<?php
/**
 * User: bc8 /bc8web@126.com
 * Date: 2019/3/14 0014 - 下午 20:13
 * 启动文件
 */

use \Workerman\Worker;

require 'vendor/autoload.php';
require 'utils.php';

date_default_timezone_set('PRC'); // 设置中国时区

$worker = new Worker('websocket://0.0.0.0:1234'); // 实例
$worker->count = 1; // 开启一个进程
$worker->onWorkerStart = function ($worker) { // 当进程启动的时候

    \Workerman\Lib\Timer::add(7, function () {
        $ctime = date("ymd", time());
        if (isTradeTime()) { // 判断是否在股票交易时间
            if (!file_exists($ctime)) {
                file_put_contents($ctime, "[]"); // 创建以当前日期的数据文件
            }
            $ctime_data = json_decode(file_get_contents($ctime)); // 获取目前数据
            $str = curl_request('http://qt.gtimg.cn/q=s_sh000001'); // 请求新的数据
            $tmp = explode("=", $str); // v_s_sh000001="1~上证指数~000001~3016.48~25.79~0.86~322061989~32535943~~";
            $data = explode("~", $tmp[1]);
            $store_str = $data[5] + "~" + $data[3] + "~" + $data[7]; //  涨幅%~当前价~成交额 构造发送到前端的字符串
            array_push($ctime_data, $store_str);
            file_put_contents($ctime, json_encode($ctime_data));
        }
    });

    $worker->onConnect = function ($connection) { // 当客户端连接进来之后
        $ctime = date("ymd", time());
        \Workerman\Lib\Timer::add(7, function () use ($connection, $ctime) {
            if (file_exists($ctime)) {
                $connection->send(file_get_contents($ctime));
            }
        });
    };
    
    $worker->onClose = function ($connection) {
        \Workerman\Lib\Timer::del($connection->worker->timerId);
    }
};

Worker::runAll(); // 启动进程
