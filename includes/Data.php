<?php

namespace RRZE\Statistik;

defined('ABSPATH') || exit;

/**
 * Collects data from statistiken.rrze.fau.de
 */
class Data
{
    public static function updateData() {
        // Get the data from the API.
    }

    public static function fetchDataBody($url)
    {
        $cached_processed_value = get_transient('rrze_statistik_webalizer_hist_processed');
        if (false !== $cached_processed_value) {
            return [1, $cached_processed_value];
        } else {
            $cached = get_transient('rrze_statistik_webalizer_hist');
            var_dump($cached);
            if ($cached !== false) {
                return [0, $cached];
            } else {
                $cachable = wp_remote_get(esc_url_raw($url));
                
                $cachable_body = wp_remote_retrieve_body($cachable);
                var_dump($cachable_body);
                if(strlen($cachable_body) !== 0){
                    set_transient('rrze_statistik_webalizer_hist', $cachable_body, 120);
                }
                return [0, $cachable_body];
            }
        }
    }

    public static function fetchLast24Months($url)
    {
        /* DATA STRUCTURE */
        $keymap = array(
            'monat',
            'jahr',
            'hits',
            'files',
            'hosts',
            'kbytes',
            'anzahl_monate',
            'aufgezeichnete_tage',
            'pages',
            'visits',
        );

        /* Wenn nicht im Uninetz wird "forbidden" returnt */
        $data = self::fetchDataBody($url);
        $ready_check = $data[0];
        $data_body = $data[1];
        //var_dump(get_transient('rrze_staistik_webalizer_hist'));
        //var_dump($data_body);

        if($ready_check === 0){
            if (strpos($data_body, "Forbidden") !== false) {
                wp_localize_script('index-js', 'linechart_dataset', ['forbidden']);
                return 'forbidden';
            } else if (strlen($data_body) === 0) {
                wp_localize_script('index-js', 'linechart_dataset', ['forbidden']);
                return 'no_data';
            } else if (strpos($data_body, "could not be found on this server") !== false) {
                wp_localize_script('index-js', 'linechart_dataset', ['forbidden']);
                return 'no_data';
            } else {
                $data_trim = rtrim($data_body, " \n\r\t\v");
                $array = preg_split("/\r\n|\n|\r/", $data_trim);
                $output = [];
                foreach ($array as $value) {
                    array_push($output, array_combine($keymap, preg_split("/ /", $value)));
                }

                $cachable = $output;
                set_transient('rrze_statistik_webalizer_hist_processed', $cachable, 60 * 60 * 24);

                $reshuffled_data = array(
                    'l10n_print_after' => 'linechart_dataset = ' . json_encode($output) . ';'
                );
                wp_localize_script('index-js', 'linechart_dataset', $reshuffled_data);
                return $output;
            }
        }
        else{
            var_dump('hier');
            $reshuffled_data = array(
                'l10n_print_after' => 'linechart_dataset = ' . json_encode($data_body) . ';'
            );
            wp_localize_script('index-js', 'linechart_dataset', $reshuffled_data);
            return $data_body;
        }
    }
    
}
