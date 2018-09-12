<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Mail;

use Piwik\Piwik;
use Piwik\ReportRenderer;

class EmailStyles
{
    const REPORT_TITLE_TEXT_SIZE = 24;
    const REPORT_TABLE_HEADER_TEXT_SIZE = 11;
    const REPORT_TABLE_ROW_TEXT_SIZE = '13px';
    const REPORT_BACK_TO_TOP_TEXT_SIZE = 9;

    /**
     * @var string
     */
    public $reportFontFamily = ReportRenderer::DEFAULT_REPORT_FONT_FAMILY;

    /**
     * @var string
     */
    public $reportTitleTextColor;

    /**
     * @var int
     */
    public $reportTitleTextSize = self::REPORT_TITLE_TEXT_SIZE;

    /**
     * @var string
     */
    public $reportTextColor;

    /**
     * @var string
     */
    public $tableHeaderBgColor;

    /**
     * @var string
     */
    public $tableHeaderTextColor;

    /**
     * @var string
     */
    public $tableCellBorderColor;

    /**
     * @var string
     */
    public $tableBgColor;

    /**
     * @var string
     */
    public $reportTableHeaderTextWeight = ReportRenderer::TABLE_HEADER_TEXT_WEIGHT;

    /**
     * @var int
     */
    public $reportTableHeaderTextSize = self::REPORT_TABLE_HEADER_TEXT_SIZE;

    /**
     * @var string
     */
    public $reportTableHeaderTextTransform = ReportRenderer::TABLE_HEADER_TEXT_TRANSFORM;

    /**
     * @var string
     */
    public $reportTableRowTextSize = self::REPORT_TABLE_ROW_TEXT_SIZE;

    /**
     * @var int
     */
    public $reportBackToTopTextSize = self::REPORT_BACK_TO_TOP_TEXT_SIZE;

    /**
     * @var string
     */
    public $brandNameLong;

    /**
     * @var string
     */
    public $paragraphStyle = 'font-size:15px;line-height:24px;margin:0 0 16px;';

    /**
     * @var string
     */
    public $footerLinkColor = '#439fe0';

    /**
     * @var string
     */
    public $footerParagraphSyyles = 'text-align:center;font-size:13px; color:#666; padding:30px;';

    public function __construct()
    {
        $this->reportTitleTextColor = self::rgbToHex(ReportRenderer::REPORT_TITLE_TEXT_COLOR);
        $this->reportTextColor = self::rgbToHex(ReportRenderer::REPORT_TEXT_COLOR);
        $this->tableHeaderBgColor = self::rgbToHex(ReportRenderer::TABLE_HEADER_BG_COLOR);
        $this->tableHeaderTextColor = self::rgbToHex(ReportRenderer::TABLE_HEADER_TEXT_COLOR);
        $this->tableCellBorderColor = self::rgbToHex(ReportRenderer::TABLE_CELL_BORDER_COLOR);
        $this->tableBgColor = self::rgbToHex(ReportRenderer::TABLE_BG_COLOR);

        $this->brandNameLong = 'Matomo, ' . Piwik::translate('General_OpenSourceWebAnalytics');
    }

    private static function rgbToHex($rgbValues)
    {
        list($r, $g, $b) = explode(',', $rgbValues);
        return '#' . dechex($r) . dechex($g) . dechex($b);
    }

    public static function get()
    {
        $result = new self();

        /**
         * @ignore
         */
        Piwik::postEvent('Email.configureEmailStyle', [$result]);

        return $result;
    }
}