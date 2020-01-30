<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\System;

use Piwik\CliMulti\CliPhp;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Development;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\Monolog\Handler\FailureLogMessageDetector;
use Piwik\Tests\Framework\Fixture;
use Psr\Log\LoggerInterface;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Piwik\Tests\Framework\TestCase\ConsoleCommandTestCase;

class TestCommandWithWarning extends ConsoleCommand
{
    public function configure()
    {
        parent::configure();

        $this->setName('test-command-with-warning');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        StaticContainer::get(LoggerInterface::class)->warning('warn');
    }
}

class TestCommandWithError extends ConsoleCommand
{
    public function configure()
    {
        parent::configure();

        $this->setName('test-command-with-error');
        $this->addOption('no-error', null, InputOption::VALUE_NONE);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('no-error')) {
            StaticContainer::get(LoggerInterface::class)->error('error');
        }
    }
}

class TestCommandWithFatalError extends ConsoleCommand
{
    public function configure()
    {
        parent::configure();

        $this->setName('test-command-with-fatal-error');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            \Piwik\ErrorHandler::pushFatalErrorBreadcrumb(static::class);

            $this->executeImpl($input, $output);
        } finally {
            \Piwik\ErrorHandler::popFatalErrorBreadcrumb();
        }
    }

    public function executeImpl(InputInterface $input, OutputInterface $output)
    {
        try {
            \Piwik\ErrorHandler::pushFatalErrorBreadcrumb(static::class, []);

            $val = "";
            while (true) {
                $val .= str_repeat("*", 1024 * 1024 * 1024);
            }
        } finally {
            \Piwik\ErrorHandler::popFatalErrorBreadcrumb();
        }
    }
}

class TestCommandWithException extends ConsoleCommand
{
    public function configure()
    {
        parent::configure();

        $this->setName('test-command-with-exception');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        throw new \Exception('test error');
    }
}

class ConsoleTest extends ConsoleCommandTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->application->addCommands([
            new TestCommandWithWarning(),
            new TestCommandWithError(),
        ]);

        StaticContainer::get(FailureLogMessageDetector::class)->reset();
    }

    public function test_Console_ReturnsCorrectExitCode_IfCommandEmitsWarning()
    {
        $exitCode = $this->applicationTester->run([
            'command' => 'test-command-with-warning',
        ]);
        $this->assertEquals(1, $exitCode);
    }

    public function test_Console_ReturnsCorrectExitCode_IfCommandEmitsError()
    {
        $exitCode = $this->applicationTester->run([
            'command' => 'test-command-with-error',
        ]);
        $this->assertEquals(1, $exitCode);
    }

    public function test_Console_ReturnsCorrectExitCode_IfCommandDoesNotEmitAnything()
    {
        $exitCode = $this->applicationTester->run([
            'command' => 'test-command-with-error',
            '--no-error' => true,
        ]);
        $this->assertEquals(0, $exitCode);
    }

    public function test_Console_handlesFatalErrorsCorrectly()
    {
        $command = Fixture::getCliCommandBase();
        $command .= ' test-command-with-fatal-error';
        $command .= ' 2>&1';

        $output = shell_exec($command);
        $output = $this->normalizeOutput($output);

        $expected = <<<END
#!/usr/bin/env php

Fatal error: Allowed memory size of 2147483648 bytes exhausted (tried to allocate 1073741856 bytes) in /tests/PHPUnit/System/ConsoleTest.php on line 85
*** IN SAFEMODE ***
Matomo encountered an error: Allowed memory size of 2147483648 bytes exhausted (tried to allocate 1073741856 bytes) (which lead to: Error: array (
  'type' => 1,
  'message' => 'Allowed memory size of 2147483648 bytes exhausted (tried to allocate 1073741856 bytes)',
  'file' => '/tests/PHPUnit/System/ConsoleTest.php',
  'line' => 85,
  'backtrace' => ' on /tests/PHPUnit/System/ConsoleTest.php(85)
#0 /tests/PHPUnit/System/ConsoleTest.php(72): Piwik\\\\Tests\\\\System\\\\TestCommandWithFatalError->executeImpl()
#1 /vendor/symfony/console/Symfony/Component/Console/Command/Command.php(257): Piwik\\\\Tests\\\\System\\\\TestCommandWithFatalError->execute()
',
))
END;
        $this->assertEquals($expected, $output);
    }

    public function test_Console_handlesExceptionsCorrectly()
    {
        $command = Fixture::getCliCommandBase();
        $command .= ' test-command-with-exception';
        $command .= ' 2>&1';

        $output = shell_exec($command);
        $output = $this->normalizeOutput($output);

        $expected = <<<END
#!/usr/bin/env php
*** IN SAFEMODE ***


               
  [Exception]  
  test error   
               


test-command-with-exception



END;
        $this->assertEquals($expected, $output);
    }

    public static function provideContainerConfigBeforeClass()
    {
        return [
            'log.handlers' => [\DI\get(FailureLogMessageDetector::class)],
            LoggerInterface::class => \DI\object(Logger::class)
                ->constructor('piwik', \DI\get('log.handlers'), \DI\get('log.processors')),

            'observers.global' => \DI\add([
                ['Console.filterCommands', function (&$commands) {
                    $commands[] = TestCommandWithFatalError::class;
                    $commands[] = TestCommandWithException::class;
                }],

                ['Request.dispatch', function ($module, $action) {
                    if ($module === 'CorePluginsAdmin' && $action === 'safemode') {
                        print "*** IN SAFEMODE ***\n"; // will appear in output
                    }
                }],
            ]),
        ];
    }

    private function normalizeOutput($output)
    {
        $output = str_replace(PIWIK_INCLUDE_PATH, '', $output);
        return $output;
    }
}