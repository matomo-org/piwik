<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\View;

use Piwik\Date;
use Piwik\Piwik;
use Piwik\Plugins\API\API;
use Piwik\Plugins\CoreAdminHome\CustomLogo;
use Piwik\Plugins\Morpheus\Emails as MorpheusEmails;
use Piwik\Scheduler\Schedule\Schedule;
use Piwik\SettingsPiwik;
use Piwik\Site;
use Piwik\View;

class HtmlReportEmailHeaderView extends View
{
    const TEMPLATE_FILE = '@CoreHome/ReportRenderer/_htmlReportHeader';

    private static $reportFrequencyTranslationByPeriod = [
        Schedule::PERIOD_DAY   => 'General_DailyReport',
        Schedule::PERIOD_WEEK  => 'General_WeeklyReport',
        Schedule::PERIOD_MONTH => 'General_MonthlyReport',
        Schedule::PERIOD_YEAR  => 'General_YearlyReport',
        Schedule::PERIOD_RANGE => 'General_RangeReports',
    ];

    public function __construct($reportTitle, $prettyDate, $description, $reportMetadata, $segment, $idSite, $period)
    {
        parent::__construct(self::TEMPLATE_FILE);

        self::assignCommonParameters($this);

        $periods = self::getPeriodToFrequencyAsAdjective();
        $this->assign("frequency", $periods[$period]);
        $this->assign("reportTitle", $reportTitle);
        $this->assign("prettyDate", $prettyDate);
        $this->assign("description", $description);
        $this->assign("reportMetadata", $reportMetadata);
        $this->assign("websiteName", Site::getNameFor($idSite));
        $this->assign("idSite", $idSite);
        $this->assign("period", $period);

        $customLogo = new CustomLogo();
        $this->assign("isCustomLogo", $customLogo->isEnabled() && CustomLogo::hasUserLogo());
        $this->assign("logoHeader", $customLogo->getHeaderLogoUrl($pathOnly = false));

        $date = Date::now()->setTimezone(Site::getTimezoneFor($idSite))->toString();
        $this->assign("date", $date);

        // segment
        $displaySegment = ($segment != null);
        $this->assign("displaySegment", $displaySegment);
        if ($displaySegment) {
            $this->assign("segmentName", $segment['name']);
        }
    }

    public static function assignCommonParameters(View $view)
    {
        $parameters = self::getEmailStyleParameters();

        $parameters['currentPath'] = SettingsPiwik::getPiwikUrl();
        $parameters['logoHeader'] = API::getInstance()->getHeaderLogoUrl();

        foreach ($parameters as $name => $value) {
            $view->assign($name, $value);
        }
    }

    private static function getPeriodToFrequencyAsAdjective()
    {
        return array_map(['\Piwik\Piwik', 'translate'], self::$reportFrequencyTranslationByPeriod);
    }

    private static function getEmailStyleParameters()
    {
        $vars = MorpheusEmails::getDefaultEmailStyles();

        /**
         * Use this event to change the colors and fonts used in HTML email reports by setting properties in $vars.
         *
         * The following properties are supported:
         * - **themeFontFamilyBase**
         * - **themeColorBrand**
         * - **themeColorBrandContrast**
         * - **themeColorText**
         * - **themeColorTextLight**
         * - **themeColorTextLighter**
         * - **themeColorTextContrast**
         * - **themeColorLink**
         * - **themeColorBaseSeries**
         * - **themeColorHeadlineAlternative**
         * - **themeColorHeaderBackground**
         * - **themeColorHeaderText**
         * - **brandNameLong**
         *
         * These variables have the same meaning as their LESS variable counterparts (the LESS variables in a theme w/ the "@theme-...-..." format).
         *
         * @param string[] &$vars
         * @internal
         */
        Piwik::postEvent('Emails.setThemeVariables', [&$vars]);

        return $vars;
    }
}
