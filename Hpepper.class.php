<?php

/**
 * Hpepper.class.php
 *
 * @since 2016/08/04
 */
class Hpepper
{

    /**
     * アクセスキー
     * @var string
     */
    private static $token = '50c71e2aa4909d2d';

    /**
     * 都道府県リストを取得
     * @return object
     */
    public static function getPref()
    {
        $uri = "https://webservice.recruit.co.jp/hotpepper/service_area/v1/";
        $acckey = self::$token;
        $format = "json";

        $url = sprintf("%s?format=%s&key=%s", $uri, $format, $acckey);
        $json = @file_get_contents($url);
        $obj = json_decode($json);
        return $obj;
    }

    /**
     * レストラン検索
     * @return object
     */
    public static function getRestaurants()
    {
        $uri = "http://webservice.recruit.co.jp/hotpepper/gourmet/v1/";
        $acckey = self::$token;
        $format = "json";

        $get = array(
            'format' => $format
            , 'key' => $acckey
            , 'count' => 10
            , 'name_any' => ''
        );
        if (!is_null(filter_input_array(INPUT_GET))) {
            $get += filter_input_array(INPUT_GET);
        }
        $url = sprintf("%s?%s", $uri, http_build_query($get));

        $json = @file_get_contents($url);
        $obj = json_decode($json);
        return $obj;
    }

    public static function pagination($total = 0)
    {
        $start = filter_input(INPUT_GET, 'start');
        if ($start == 0) {
            $start = 1;
        }

        if ($total == 0) {
            return;
        }

        $html = '<li%s><a href="?%s">%s</a></li>';

        //現在の頁
        $curPage = ceil($start / 10);
        $iStart = (0 < $curPage - 3) ? $curPage - 3 : 1;

        $pages = ceil($total / 10);
        $iMax = ($iStart + 6 > $pages) ? $pages : $iStart + 6;

        $arr = array();
        $params = filter_input_array(INPUT_GET);

        for ($i = $iStart; $i <= $iMax; $i++) {
            $params['start'] = ($i - 1) * 10 + 1;

            $class = ($params['start'] == $start) ? ' class="active"' : '';

            $query = http_build_query($params, '', '&amp;');
            $arr[] = sprintf($html, $class, $query, $i);
        }

        return implode(PHP_EOL, $arr);
    }

}