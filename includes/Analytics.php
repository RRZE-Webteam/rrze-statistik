<?php

namespace RRZE\Statistik;

defined('ABSPATH') || exit;

/**
 * Gets processed statistics and initiates their dashboard presentation.
 */
class Analytics
{

    public static function getDate()
    {
        $raw_date = date("Ym");
        $new_date = date("Ym", strtotime('-1 month', strtotime($raw_date)));
        return $new_date;
    }

    /**
     * Determine which url should be used and return the string
     *
     * @param string $type
     * @return string
     */
    public static function retrieveSiteUrl($type)
    {
        $debug = Helper::isDebug();
        if ($debug === false) {
            $remove_char = ["https://", "http://", "/"];
            $url = str_replace($remove_char, "", get_site_url());
        } else {
            $url = "www.wp.rrze.fau.de";
        }

        if ($type === 'webalizer.hist') {
            $output = 'https://statistiken.rrze.fau.de/webauftritte/logs/' . $url . '/webalizer.hist';
        } else if ($type === 'logs') {
            $output = 'https://statistiken.rrze.fau.de/webauftritte/logs/' . $url;
        } else {
            $output = 'https://statistiken.rrze.fau.de/webauftritte/logs/' . $url . '/url_' . self::getDate() . '.tab';
        }
        return $output;
    }

    /**
     * Creates chart markup while preserving the existing public metric interface.
     *
     * @param string $container Supported metric identifier and chart element ID.
     * @return string Escaped chart markup or a data-availability notice.
     */
    public function getLinechart($container)
    {
        $remove_char = ["https://", "http://", "/"];
        $site = str_replace($remove_char, "", get_site_url());
        $ready_check = Data::processLinechartDataset(self::retrieveSiteUrl('webalizer.hist'));
        if ($ready_check === false) {
            return '<p>' . esc_html(sprintf(
                /* translators: %1$s: Website hostname. */
                __('It might take a few days until personal statistics for your website ( %1$s ) are displayed within your dashboard.', 'rrze-statistik'),
                $site
            )) . '</p>';
        } else {
            return Charts::lineplot($container);
        };
    }

    /**
     * Renders a ranked resource table inside a native, keyboard-operable disclosure.
     * Full resource paths remain visible; only the homepage gets a descriptive label.
     * @param array $array Source rows containing request counts and resource paths.
     * @param string|int $array_key_1 Request-count key.
     * @param string|int $array_key_2 Resource-path key.
     * @param string $head_desc_1 Localized count-column heading.
     * @param string $head_desc_2 Localized category and resource-column heading.
     * @param bool $expanded Whether the category is initially open.
     * @return string Escaped disclosure and semantic table, or an empty string without valid rows.
     */
    public static function getTwoDimensionalHtmlTable($array, $array_key_1, $array_key_2, $head_desc_1, $head_desc_2, $expanded = true)
    {
        if (!is_array($array)) return '';
        $rows = [];
        foreach ($array as $value) {
            if (!is_array($value) || !isset($value[$array_key_1], $value[$array_key_2])
                || !is_scalar($value[$array_key_1]) || !is_string($value[$array_key_2])) continue;
            $hits = filter_var($value[$array_key_1], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
            $path = trim($value[$array_key_2]);
            if ($hits === false || $path === '') continue;
            $rows[] = ['hits' => $hits, 'path' => $path, 'order' => count($rows)];
        }
        if (!$rows) return '';
        /**
         * Sort by requests while preserving source order for ties on PHP 7.4 too.
         * @param array $first First normalized resource.
         * @param array $second Second normalized resource.
         * @return int Relative sort order.
         */
        $compare = static function ($first, $second) {
            return ($second['hits'] <=> $first['hits']) ?: ($first['order'] <=> $second['order']);
        };
        usort($rows, $compare);
        $topLabel = sprintf(
            /* translators: %s: Number of resources in the ranking. */
            __('Top %s', 'rrze-statistik'), number_format_i18n(count($rows))
        );
        $html = '<details class="rrze-statistik-ranking"' . ($expanded ? ' open' : '') . '>'
            . '<summary><span class="rrze-statistik-ranking-title">' . esc_html($head_desc_2) . '</span>'
            . '<span class="rrze-statistik-ranking-count">' . esc_html($topLabel) . '</span></summary>'
            . '<div class="rrze-statistik-table"><table><caption>' . esc_html($head_desc_2) . '</caption>'
            . '<colgroup><col><col class="rrze-statistik-hits-column"></colgroup>'
            . '<thead><tr><th scope="col">' . esc_html($head_desc_2) . '</th>'
            . '<th scope="col" class="rrze-statistik-hits">' . esc_html($head_desc_1) . '</th></tr></thead><tbody>';
        foreach ($rows as $index => $row) {
            $url = esc_url($row['path'], ['http', 'https']);
            $label = $row['path'] === '/' ? __('Home page', 'rrze-statistik') : $row['path'];
            $resource = $url !== '' ? '<a href="' . $url . '">' . esc_html($label) . '</a>' : '<span>' . esc_html($label) . '</span>';
            $rankLabel = sprintf(
                /* translators: %s: Position in the resource ranking. */
                __('Rank %s', 'rrze-statistik'), number_format_i18n($index + 1)
            );
            $html .= '<tr><th scope="row"><div class="rrze-statistik-resource">'
                . '<span class="rrze-statistik-rank"><span class="rrze-statistik-ranking-sr">'
                . esc_html($rankLabel) . '</span><span aria-hidden="true">' . esc_html($index + 1) . '</span></span>'
                . '<span class="rrze-statistik-resource-path">' . $resource . '</span></div></th>'
                . '<td class="rrze-statistik-hits"><span class="rrze-statistik-hit-count">'
                . esc_html(number_format_i18n($row['hits'])) . '</span></td></tr>';
        }
        return $html . '</tbody></table></div></details>';
    }

    /**
     * Renders cached resource rankings, initially expanding the sites category.
     *
     * @return string Escaped ranking disclosures or a data-availability notice.
     */
    public static function getUrlDatasetTable()
    {
        $data = get_transient('rrze_statistik_data_url');
        $output = '';
        $categories = [__('Sites', 'rrze-statistik'), __('Media', 'rrze-statistik'), __('Documents', 'rrze-statistik')];
        if (is_array($data)) {
            foreach ($categories as $index => $label) {
                $output .= self::getTwoDimensionalHtmlTable(
                    $data[$index] ?? [], 0, 1, __('Requests', 'rrze-statistik'), $label, $index === 0
                );
            }
        }
        if ($output === '') {
            return '<p>' . esc_html__('It might take a few weeks until the summary is displayed on your dashboard.', 'rrze-statistik') . '</p>';
        }
        return $output;
    }

    /**
     * Checks if the new array's date is newer than the reference date. Checks the last-1 value against each other to ignore incomplete datasets.
     *
     * @param array $array
     * @param array $arrayRef
     * @return boolean
     */
    public static function isDateNewer($array, $arrayRef)
    {
        if (is_array($array) && is_array($arrayRef)) {
            $offset = count($array) - 2;
            $date = $array[$offset]['year'] . sprintf("%02d", $array[$offset]['month']);
            $dateRef = $arrayRef[count($arrayRef) - 1]['year'] . sprintf("%02d", $arrayRef[count($arrayRef) - 1]['month']);
            if ((int)$date > (int)$dateRef) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
