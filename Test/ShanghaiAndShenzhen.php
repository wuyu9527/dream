<?php
    /**
     * Created by PhpStorm.
     * User: whx
     * Date: 2016/7/8
     * Time: 14:07
     */
    header('Content-type:text/html;charset=utf-8');
    //配置您申请的appkey
    $appkey = "9123f07be3da07bdf7b6e126f33ed608";
    //************1.沪深股市************
    $url = "http://web.juhe.cn:8080/finance/stock/hs";
    $params = array(
        "gid" => "sh600012",//股票编号，上海股市以sh开头，深圳股市以sz开头如：sh601009
        "key" => $appkey,//APP Key
    );
    $paramstring = http_build_query($params);
    $content = juhecurl($url, $paramstring);
    $result = json_decode($content, true);
    if ($result) {
        if ($result['error_code'] == '0') {
            print_r($result['result'][0]['dapandata']['name']);
        } else {
            echo $result['error_code'] . ":" . $result['reason'];
        }
    } else {
        echo "请求失败";
    }
    //**************************************************
    /**
     * 请求接口返回内容
     *
     * @param  string $url    [请求的URL地址]
     * @param  string $params [请求的参数]
     * @param  int    $ipost  [是否采用POST形式]
     *
     * @return  string
     */
    function juhecurl($url, $params = false, $ispost = 0) {

        $httpInfo = array();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'JuheData');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        if ($ispost) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_URL, $url);
        } else {
            if ($params) {
                curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
            } else {
                curl_setopt($ch, CURLOPT_URL, $url);
            }
        }
        $response = curl_exec($ch);
        if ($response === false) {
            //echo "cURL Error: " . curl_error($ch);
            return false;
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
        curl_close($ch);

        return $response;
    }