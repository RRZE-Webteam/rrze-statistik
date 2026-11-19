<?php

namespace RRZE\Statistik;

defined('ABSPATH') || exit;

/**
 * Collects Translations in one place.
 */
class Language
{
    /**
     * Returns the twelve translated month abbreviations in calendar order.
     *
     * @return string[] Month labels for January through December.
     */
    public static function getMonthDesc()
    {
        return array(
            __('Jan', 'rrze-statistik'),
            __('Feb', 'rrze-statistik'),
            __('Mar', 'rrze-statistik'),
            __('Apr', 'rrze-statistik'),
            __('May', 'rrze-statistik'),
            __('Jun', 'rrze-statistik'),
            __('Jul', 'rrze-statistik'),
            __('Aug', 'rrze-statistik'),
            __('Sep', 'rrze-statistik'),
            __('Oct', 'rrze-statistik'),
            __('Nov', 'rrze-statistik'),
            __('Dec', 'rrze-statistik'),
        );
    }

    /**
     * Returns the monthly chart axis labels.
     *
     * @return string[] Translated month abbreviations.
     */
    public static function getAbscissa()
    {
        $abscissa_desc = self::getMonthDesc();
        return $abscissa_desc;
    }

    /**
     * Returns the translated attribution for the statistics provider.
     *
     * @return string Source label.
     */
    public static function getSource()
    {
        $source_text = __('Source: Statistiken.rrze.fau.de', 'rrze-statistik');
        return $source_text;
    }

    /**
     * Returns translated chart titles, units and tooltip descriptions by metric.
     *
     * @return array<string,array<string,string>> Metric metadata.
     */
    public static function getLanguagePackage()
    {
        $output = array(
            'visits' => array(
                'ordinate_desc' => __('Visits', 'rrze-statistik'),
                'headline_chart' => __('Visits over time', 'rrze-statistik'),
                'tooltip_desc' => __(' Visits / Month', 'rrze-statistik'),
            ),
            'hits' => array(
                'ordinate_desc' => __('Hits', 'rrze-statistik'),
                'headline_chart' => __('Hits over time', 'rrze-statistik'),
                'tooltip_desc' => __(' Hits / Month', 'rrze-statistik'),
            ),
            'hosts' => array(
                'ordinate_desc' => __('Hosts', 'rrze-statistik'),
                'headline_chart' => __('Hosts over time', 'rrze-statistik'),
                'tooltip_desc' => __(' Hosts / Month', 'rrze-statistik'),
            ),
            'files' => array(
                'ordinate_desc' => __('Files', 'rrze-statistik'),
                'headline_chart' => __('Files over time', 'rrze-statistik'),
                'tooltip_desc' => __(' Files / Month', 'rrze-statistik'),
            ),
            'kbytes' => array(
                'ordinate_desc' => __('Kbytes', 'rrze-statistik'),
                'headline_chart' => __('Kbytes over time', 'rrze-statistik'),
                'tooltip_desc' => __(' Kbytes / Month', 'rrze-statistik'),
            ),
        );
        return $output;
    }

    /**
     * Returns a dashboard widget title for a metric or the popular-pages table.
     *
     * @param string $container Widget metric identifier.
     * @return string Translated widget title.
     */
    public static function dashboardWidgetTitle($container){
        switch ($container) {
            case 'visits':
                return __('Site visits over time', 'rrze-statistik');
            case 'hits':
                return __('Hits over time', 'rrze-statistik');
            case 'hosts':
                return __('Hosts over time', 'rrze-statistik');
            case 'files':
                return __('Files over time', 'rrze-statistik');
            case 'kbytes':
                return __('Kbytes over time', 'rrze-statistik');
            case 'urls':
                return __('Popular sites and files', 'rrze-statistik');

            default:
                return __('Unknown container', 'rrze-statistik');
        }
    }

    /**
     * Describes the selected chart type and the meaning of its metric.
     *
     * @param string $type Metric identifier.
     * @param string|null $displayType Explicit chart type, or the legacy site default.
     * @return string|null Translated description, or null for an unknown metric.
     */
    public static function getLinechartDescription($type, $displayType = null)
    {
        $options = get_option('rrze_statistik_widget');
        $display_type = $displayType ?? ($options['display_type'] ?? '');

        switch ($display_type){
            case 'bar':
                $display_type_description = __('Bar chart', 'rrze-statistik');
                break;
            case 'spline':
                $display_type_description = __('Line chart', 'rrze-statistik');
                break;
            case 'areaspline':
                $display_type_description = __('Area chart', 'rrze-statistik');
                break;
            case 'column':
                $display_type_description = __('Column chart', 'rrze-statistik');
                break;
            
            default: 
                $display_type_description = __('Chart', 'rrze-statistik');
                break;
        }

        switch ($type) {
            case 'visits':
                /* translators: %s: Selected chart type. */
                return sprintf(__('%s displaying monthly site visits. Visits are not a count of unique people.', 'rrze-statistik'), $display_type_description);
            case 'hits':
                return sprintf(__('%s displaying the number of hits over several months. A hit is a request for a file such as a web page, image, javascript, or CSS hosted on a web server. A single visitor can generate plenty of hits.', 'rrze-statistik'), $display_type_description);
            case 'hosts':
                return sprintf(__('%s displaying the number of hosts over several months. A host is a computer or server opening your website. Larger online services are often only displayed as a single host if they share one server.', 'rrze-statistik'), $display_type_description);
            case 'files':
                return sprintf(__('%s displaying the number of successful loaded files from your domain. This includes all documents, css-files, scripts and media-files.', 'rrze-statistik'), $display_type_description);
            case 'kbytes':
                return sprintf(__('%s displaying the number of transferred Data in kBytes over several months.', 'rrze-statistik'), $display_type_description);
        }
        return;
    }

    /**
     * Returns the translated title of the monthly chart axis.
     *
     * @return string Axis title.
     */
    public static function getAbscissaTitle()
    {
        return __('Time', 'rrze-statistik');
    }

    /**
     * Describes the three calendar years represented by the chart.
     *
     * @return string Translated calendar-range description.
     */
    public static function getAccessibilityAbscissa()
    {
        $year = (int) wp_date('Y');
        return sprintf(
            /* translators: 1: First calendar year, 2: Last calendar year. */
            __('Monthly values from January %1$d to December %2$d', 'rrze-statistik'),
            $year - 2,
            $year
        );
    }

    /**
     * Returns the existing clipboard action label.
     *
     * @return string Translated button label.
     */
    public static function getCSVButtonText()
    {
        return __('Copy CSV to clipboard', 'rrze-statistik');
    }

    /**
     * Provides compact, localized labels for the dashboard tabs.
     * @return array<string,string> Labels in display order.
     */
    public static function getDashboardViews()
    {
        return [
            'visits' => __('Visits', 'rrze-statistik'),
            'hits' => __('Requests', 'rrze-statistik'),
            'hosts' => __('Hosts', 'rrze-statistik'),
            'files' => __('Files', 'rrze-statistik'),
            'kbytes' => __('Transfer', 'rrze-statistik'),
            'urls' => __('Popular pages', 'rrze-statistik'),
        ];
    }

    /**
     * Provides the native chart-type selector's labels and allowlisted values.
     * @return array<string,string> Chart type labels.
     */
    public static function getDisplayTypes()
    {
        return [
            'spline' => __('Line chart', 'rrze-statistik'),
            'areaspline' => __('Area chart', 'rrze-statistik'),
            'column' => __('Column chart', 'rrze-statistik'),
            'bar' => __('Bar chart', 'rrze-statistik'),
        ];
    }

    /**
     * Returns translated labels shared by chart controls, tables and exports.
     *
     * Named placeholders are replaced by the browser after the values are known.
     *
     * @return array<string,string> Labels keyed by the JavaScript model contract.
     */
    public static function getChartLabels()
    {
        return [
            'month' => __('Month', 'rrze-statistik'),
            'year' => __('Year', 'rrze-statistik'),
            'chooseYear' => __('Choose a year', 'rrze-statistik'),
            'chooseMonth' => __('Choose a month', 'rrze-statistik'),
            'visibleYears' => __('Visible years', 'rrze-statistik'),
            'showTable' => __('Show data table', 'rrze-statistik'),
            /* translators: {title} is the chart title; preserve the placeholder. */
            'tableCaption' => __('{title}: monthly values', 'rrze-statistik'),
            'noData' => __('No data available', 'rrze-statistik'),
            'emptyChart' => __('No monthly data are available for this period.', 'rrze-statistik'),
            'renderError' => __('The chart could not be displayed. The data table and CSV export remain available.', 'rrze-statistik'),
            'loading' => __('Loading statistics…', 'rrze-statistik'),
            'copyCsv' => self::getCSVButtonText(),
            'downloadCsv' => __('Download CSV', 'rrze-statistik'),
            'downloadSvg' => __('Download SVG', 'rrze-statistik'),
            'downloadPng' => __('Download PNG', 'rrze-statistik'),
            'copied' => __('CSV copied to clipboard.', 'rrze-statistik'),
            'copyError' => __('The CSV could not be copied. Please use the CSV download instead.', 'rrze-statistik'),
            'exportError' => __('The export could not be created. Please try again.', 'rrze-statistik'),
            'exporting' => __('Preparing export…', 'rrze-statistik'),
            'exported' => __('The download is ready.', 'rrze-statistik'),
            'lastVisibleYear' => __('At least one year must remain visible.', 'rrze-statistik'),
            /* translators: Preserve {range}, {min}, {minDate}, {max} and {maxDate}. */
            'summary' => __('{range}. Minimum: {min} ({minDate}). Maximum: {max} ({maxDate}).', 'rrze-statistik'),
            /* translators: {first} and {last} are calendar years; preserve the placeholders. */
            'range' => __('{first}–{last}', 'rrze-statistik'),
            'minimum' => __('Minimum', 'rrze-statistik'),
            'maximum' => __('Maximum', 'rrze-statistik'),
            'navigationHelp' => __('Choose a year and use the month slider to explore the values.', 'rrze-statistik'),
            'missingValue' => __('No data available', 'rrze-statistik'),
            'source' => __('Source', 'rrze-statistik'),
            'preferencesError' => __('Your selection could not be saved. It remains active until this page is reloaded.', 'rrze-statistik'),
            'preferencesSaving' => __('Saving selection…', 'rrze-statistik'),
            'preferencesSaved' => __('Selection saved.', 'rrze-statistik'),
        ];
    }
}
