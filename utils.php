<?php
/**
 * User: bc8 /bc8web@126.com
 * Date: 2019/3/15 0015 - 上午 11:16
 * 工具
 */
//参数1：访问的URL，参数2：post数据(不填则为GET)，参数3：提交的$cookies,参数4：是否返回$cookies
function curl_request($url, $post = '', $cookie = '', $returnCookie = 0)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
    curl_setopt($curl, CURLOPT_REFERER, "http://XXX");
    if ($post) {
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
    }
    if ($cookie) {
        curl_setopt($curl, CURLOPT_COOKIE, $cookie);
    }
    curl_setopt($curl, CURLOPT_HEADER, $returnCookie);
    curl_setopt($curl, CURLOPT_TIMEOUT, 10);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($curl);
    if (curl_errno($curl)) {
        return curl_error($curl);
    }
    curl_close($curl);
    if ($returnCookie) {
        list($header, $body) = explode("\r\n\r\n", $data, 2);
        preg_match_all("/Set\-Cookie:([^;]*);/", $header, $matches);
        $info['cookie'] = substr($matches[1][0], 1);
        $info['content'] = $body;
        return $info;
    } else {
        return $data;
    }
}

// 判断是否在股票交易时间 9:30-11:30  13:00-15:00
function isTradeTime()
{
    date_default_timezone_set('PRC');
    $current_time_str = (int)date('His', time()); // 当前时间 140540
    return ($current_time_str >= 93000 && $current_time_str <= 113000) || ($current_time_str >= 130000 && $current_time_str <= 150000);
}

// 判断当时日期是否是交易日
function isTradeDate()
{
    $date = date("Ymd", time());
    $res = file_get_contents("http://api.goseek.cn/Tools/holiday?date=" . $date);    //json格式，前端需要直接提供
    $res = json_decode($res, true);
    if ((int)$res['data'] == 0) return true;
    return false;
}