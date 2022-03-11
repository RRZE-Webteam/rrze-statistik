<?php

namespace RRZE\Statistik;

defined('ABSPATH') || exit;

/**
 * Collects and processes data
 */
class Experimente
{
    public static function getDummyData()
    {

        $dataThisMonth = array(
            0 => array(
                'month' => '4',
                'year' => '2020',
                'hits' => '393623',
                'files' => '351159',
                'hosts' => '7048',
                'kbytes' => '13815129',
                'month_count' => '1',
                'recorded_days' => '30',
                'pages' => '144253',
                'visits' => '22130',
            ),
            1 => array(
                'month' => '5',
                'year' => '2020',
                'hits' => '410720',
                'files' => '363278',
                'hosts' => '7135',
                'kbytes' => '13657506',
                'month_count' => '1',
                'recorded_days' => '31',
                'pages' => '161602',
                'visits' => '22063',
            ),
            2 => array(
                'month' => '6',
                'year' => '2020',
                'hits' => '449129',
                'files' => '402583',
                'hosts' => '6974',
                'kbytes' => '14991626',
                'month_count' => '1',
                'recorded_days' => '30',
                'pages' => '166987',
                'visits' => '22715',
            ),
            3 => array(
                'month' => '7',
                'year' => '2020',
                'hits' => '490811',
                'files' => '437343',
                'hosts' => '8084',
                'kbytes' => '15307601',
                'month_count' => '1',
                'recorded_days' => '31',
                'pages' => '176893',
                'visits' => '26334',
            ),
            4 => array(
                'month' => '8',
                'year' => '2020',
                'hits' => '451737',
                'files' => '405600',
                'hosts' => '6795',
                'kbytes' => '14073065',
                'month_count' => '1',
                'recorded_days' => '31',
                'pages' => '175633',
                'visits' => '23499',
            ),
            5 => array(
                'month' => '9',
                'year' => '2020',
                'hits' => '422924',
                'files' => '381220',
                'hosts' => '8395',
                'kbytes' => '13215547',
                'month_count' => '1',
                'recorded_days' => '30',
                'pages' => '147607',
                'visits' => '26357',
            ),
            6 => array(
                'month' => '10',
                'year' => '2020',
                'hits' => '466031',
                'files' => '426505',
                'hosts' => '9487',
                'kbytes' => '15664561',
                'month_count' => '1',
                'recorded_days' => '31',
                'pages' => '141012',
                'visits' => '30053',
            ),
            7 => array(
                'month' => '11',
                'year' => '2020',
                'hits' => '442089',
                'files' => '404973',
                'hosts' => '8445',
                'kbytes' => '14873601',
                'month_count' => '1',
                'recorded_days' => '30',
                'pages' => '230463',
                'visits' => '28216',
            ),
            8 => array(
                'month' => '12',
                'year' => '2020',
                'hits' => '269353',
                'files' => '235358',
                'hosts' => '7557',
                'kbytes' => '9440194',
                'month_count' => '1',
                'recorded_days' => '31',
                'pages' => '109134',
                'visits' => '23749',
            ),
            9 => array('month' => '1', 'year' => '2021', 'hits' => '311990', 'files' => '275925', 'hosts' => '8355', 'kbytes' => '10690666', 'month_count' => '1', 'recorded_days' => '31', 'pages' => '108486', 'visits' => '24114',),
            10 => array('month' => '2', 'year' => '2021', 'hits' => '297612', 'files' => '265780', 'hosts' => '8579', 'kbytes' => '10052688', 'month_count' => '1', 'recorded_days' => '28', 'pages' => '94704', 'visits' => '23246',),
            11 => array('month' => '3', 'year' => '2021', 'hits' => '346718', 'files' => '314962', 'hosts' => '10001', 'kbytes' => '14174769', 'month_count' => '1', 'recorded_days' => '31', 'pages' => '71018', 'visits' => '27374',),
            12 => array('month' => '4', 'year' => '2021', 'hits' => '494562', 'files' => '452178', 'hosts' => '10365', 'kbytes' => '16531419', 'month_count' => '1', 'recorded_days' => '30', 'pages' => '199606', 'visits' => '29623',),
            13 => array('month' => '5', 'year' => '2021', 'hits' => '692376', 'files' => '614745', 'hosts' => '9771', 'kbytes' => '15681784', 'month_count' => '1', 'recorded_days' => '31', 'pages' => '405563', 'visits' => '31173',),
            14 => array('month' => '6', 'year' => '2021', 'hits' => '339178', 'files' => '285852', 'hosts' => '9502', 'kbytes' => '15157023', 'month_count' => '1', 'recorded_days' => '30', 'pages' => '123628', 'visits' => '27462',),
            15 => array('month' => '7', 'year' => '2021', 'hits' => '332839', 'files' => '283844', 'hosts' => '9363', 'kbytes' => '13860279', 'month_count' => '1', 'recorded_days' => '31', 'pages' => '109045', 'visits' => '28308',),
            16 => array('month' => '8', 'year' => '2021', 'hits' => '374234', 'files' => '303084', 'hosts' => '9779', 'kbytes' => '15917959', 'month_count' => '1', 'recorded_days' => '31', 'pages' => '156950', 'visits' => '28898',),
            17 => array('month' => '9', 'year' => '2021', 'hits' => '386147', 'files' => '344513', 'hosts' => '12449', 'kbytes' => '18290225', 'month_count' => '1', 'recorded_days' => '30', 'pages' => '111642', 'visits' => '33271',),
            18 => array('month' => '10', 'year' => '2021', 'hits' => '406287', 'files' => '364333', 'hosts' => '12255', 'kbytes' => '19287129', 'month_count' => '1', 'recorded_days' => '31', 'pages' => '110586', 'visits' => '35681',),
            19 => array('month' => '11', 'year' => '2021', 'hits' => '288950', 'files' => '249166', 'hosts' => '9739', 'kbytes' => '14006965', 'month_count' => '1', 'recorded_days' => '30', 'pages' => '98574', 'visits' => '29108',),
            20 => array('month' => '12', 'year' => '2021', 'hits' => '977144', 'files' => '888291', 'hosts' => '8281', 'kbytes' => '80729890', 'month_count' => '1', 'recorded_days' => '31', 'pages' => '763824', 'visits' => '37679',),
            21 => array(
                'month' => '1',
                'year' => '2022',
                'hits' => '290563',
                'files' => '249191',
                'hosts' => '9553',
                'kbytes' => '13406275',
                'month_count' => '1',
                'recorded_days' => '31',
                'pages' => '98298',
                'visits' => '35784',
            ),
            22 => array(
                'month' => '2',
                'year' => '2022',
                'hits' => '310324',
                'files' => '259610',
                'hosts' => '10558',
                'kbytes' => '13132899',
                'month_count' => '1',
                'recorded_days' => '28',
                'pages' => '108775',
                'visits' => '37918',
            ),
            23 => array(
                'month' => '3',
                'year' => '2022',
                'hits' => '91534',
                'files' => '74771',
                'hosts' => '3649',
                'kbytes' => '3868183',
                'month_count' => '1',
                'recorded_days' => '11',
                'pages' => '32470',
                'visits' => '11198',
            ),
        );
        return $dataThisMonth;
    }
    public static function getDummyDataNext(){

        $dataNextMonth = array(
            0 => array(
                'month' => '5',
                'year' => '2020',
                'hits' => '410720',
                'files' => '363278',
                'hosts' => '7135',
                'kbytes' => '13657506',
                'month_count' => '1',
                'recorded_days' => '31',
                'pages' => '161602',
                'visits' => '22063',
            ),
            1 => array(
                'month' => '6',
                'year' => '2020',
                'hits' => '449129',
                'files' => '402583',
                'hosts' => '6974',
                'kbytes' => '14991626',
                'month_count' => '1',
                'recorded_days' => '30',
                'pages' => '166987',
                'visits' => '22715',
            ),
            2 => array(
                'month' => '7',
                'year' => '2020',
                'hits' => '490811',
                'files' => '437343',
                'hosts' => '8084',
                'kbytes' => '15307601',
                'month_count' => '1',
                'recorded_days' => '31',
                'pages' => '176893',
                'visits' => '26334',
            ),
            3 => array(
                'month' => '8',
                'year' => '2020',
                'hits' => '451737',
                'files' => '405600',
                'hosts' => '6795',
                'kbytes' => '14073065',
                'month_count' => '1',
                'recorded_days' => '31',
                'pages' => '175633',
                'visits' => '23499',
            ),
            4 => array(
                'month' => '9',
                'year' => '2020',
                'hits' => '422924',
                'files' => '381220',
                'hosts' => '8395',
                'kbytes' => '13215547',
                'month_count' => '1',
                'recorded_days' => '30',
                'pages' => '147607',
                'visits' => '26357',
            ),
            5 => array(
                'month' => '10',
                'year' => '2020',
                'hits' => '466031',
                'files' => '426505',
                'hosts' => '9487',
                'kbytes' => '15664561',
                'month_count' => '1',
                'recorded_days' => '31',
                'pages' => '141012',
                'visits' => '30053',
            ),
            6 => array(
                'month' => '11',
                'year' => '2020',
                'hits' => '442089',
                'files' => '404973',
                'hosts' => '8445',
                'kbytes' => '14873601',
                'month_count' => '1',
                'recorded_days' => '30',
                'pages' => '230463',
                'visits' => '28216',
            ),
            7 => array(
                'month' => '12',
                'year' => '2020',
                'hits' => '269353',
                'files' => '235358',
                'hosts' => '7557',
                'kbytes' => '9440194',
                'month_count' => '1',
                'recorded_days' => '31',
                'pages' => '109134',
                'visits' => '23749',
            ),
            8 => array('month' => '1', 'year' => '2021', 'hits' => '311990', 'files' => '275925', 'hosts' => '8355', 'kbytes' => '10690666', 'month_count' => '1', 'recorded_days' => '31', 'pages' => '108486', 'visits' => '24114',),
            9 => array('month' => '2', 'year' => '2021', 'hits' => '297612', 'files' => '265780', 'hosts' => '8579', 'kbytes' => '10052688', 'month_count' => '1', 'recorded_days' => '28', 'pages' => '94704', 'visits' => '23246',),
            10 => array('month' => '3', 'year' => '2021', 'hits' => '346718', 'files' => '314962', 'hosts' => '10001', 'kbytes' => '14174769', 'month_count' => '1', 'recorded_days' => '31', 'pages' => '71018', 'visits' => '27374',),
            11 => array('month' => '4', 'year' => '2021', 'hits' => '494562', 'files' => '452178', 'hosts' => '10365', 'kbytes' => '16531419', 'month_count' => '1', 'recorded_days' => '30', 'pages' => '199606', 'visits' => '29623',),
            12 => array('month' => '5', 'year' => '2021', 'hits' => '692376', 'files' => '614745', 'hosts' => '9771', 'kbytes' => '15681784', 'month_count' => '1', 'recorded_days' => '31', 'pages' => '405563', 'visits' => '31173',),
            13 => array('month' => '6', 'year' => '2021', 'hits' => '339178', 'files' => '285852', 'hosts' => '9502', 'kbytes' => '15157023', 'month_count' => '1', 'recorded_days' => '30', 'pages' => '123628', 'visits' => '27462',),
            14 => array('month' => '7', 'year' => '2021', 'hits' => '332839', 'files' => '283844', 'hosts' => '9363', 'kbytes' => '13860279', 'month_count' => '1', 'recorded_days' => '31', 'pages' => '109045', 'visits' => '28308',),
            15 => array('month' => '8', 'year' => '2021', 'hits' => '374234', 'files' => '303084', 'hosts' => '9779', 'kbytes' => '15917959', 'month_count' => '1', 'recorded_days' => '31', 'pages' => '156950', 'visits' => '28898',),
            16 => array('month' => '9', 'year' => '2021', 'hits' => '386147', 'files' => '344513', 'hosts' => '12449', 'kbytes' => '18290225', 'month_count' => '1', 'recorded_days' => '30', 'pages' => '111642', 'visits' => '33271',),
            17 => array('month' => '10', 'year' => '2021', 'hits' => '406287', 'files' => '364333', 'hosts' => '12255', 'kbytes' => '19287129', 'month_count' => '1', 'recorded_days' => '31', 'pages' => '110586', 'visits' => '35681',),
            18 => array('month' => '11', 'year' => '2021', 'hits' => '288950', 'files' => '249166', 'hosts' => '9739', 'kbytes' => '14006965', 'month_count' => '1', 'recorded_days' => '30', 'pages' => '98574', 'visits' => '29108',),
            19 => array('month' => '12', 'year' => '2021', 'hits' => '977144', 'files' => '888291', 'hosts' => '8281', 'kbytes' => '80729890', 'month_count' => '1', 'recorded_days' => '31', 'pages' => '763824', 'visits' => '37679',),
            20 => array(
                'month' => '1',
                'year' => '2022',
                'hits' => '290563',
                'files' => '249191',
                'hosts' => '9553',
                'kbytes' => '13406275',
                'month_count' => '1',
                'recorded_days' => '31',
                'pages' => '98298',
                'visits' => '35784',
            ),
            21 => array(
                'month' => '2',
                'year' => '2022',
                'hits' => '310324',
                'files' => '259610',
                'hosts' => '10558',
                'kbytes' => '13132899',
                'month_count' => '1',
                'recorded_days' => '28',
                'pages' => '108775',
                'visits' => '37918',
            ),
            22 => array(
                'month' => '3',
                'year' => '2022',
                'hits' => '91534',
                'files' => '74771',
                'hosts' => '3649',
                'kbytes' => '3868183',
                'month_count' => '1',
                'recorded_days' => '11',
                'pages' => '32470',
                'visits' => '11198',
            ),
            23 => array(
                'month' => '4',
                'year' => '2022',
                'hits' => '393523',
                'files' => '352159',
                'hosts' => '7038',
                'kbytes' => '15815129',
                'month_count' => '1',
                'recorded_days' => '11',
                'pages' => '144353',
                'visits' => '22230',
            ),
        );
        return $dataNextMonth;
    
    }
}
