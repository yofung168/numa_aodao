<?php
/**
 * 常用方法类
 *
 * User: Administrator
 * Date: 2017/11/14/014
 * Time: 23:18
 */

namespace Numa\Aodao;


class Common
{
    /**
     * 生成签名
     *
     * @param $token
     * @param $appid
     * @param $secret
     * @param $time
     * @return string
     */
    public static function createSign($token, $appid, $secret, $time)
    {
        return sha1(md5($token . $time . $secret . $appid));
    }

    /**
     * 生成随机token
     *
     * @param $length
     * @param bool $numeric
     * @return string
     */
    public static function random_srt($length, $numeric = FALSE)
    {
        $seed = base_convert(md5(microtime() . $_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
        $seed = $numeric ? (str_replace('0', '', $seed) . '012340567890') : ($seed . 'zZ' . strtoupper($seed));
        if ($numeric) {
            $hash = '';
        } else {
            $hash = chr(rand(1, 26) + rand(0, 1) * 32 + 64);
            $length--;
        }
        $max = strlen($seed) - 1;
        for ($i = 0; $i < $length; $i++) {
            $hash .= $seed{mt_rand(0, $max)};
        }
        return $hash;
    }

    /**
     * httpPost请求
     *
     * @param string $api_url
     * @param array $param
     * @param array $headers
     * @param int $timeout
     * @return bool|mixed
     */
    public static function http_post($api_url = '', $param = array(), $headers = array(), $timeout = 5)
    {
        $host = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_REFERER, $host);   //构造来路
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        $is_https = parse_url($api_url);
        if ($is_https['scheme'] == 'https') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        }
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
        if (!empty($headers)) {
            //          curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        $res = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if ($error) {
            throw new NumaException('请求失败:' . $error);
        }
        return $res;
    }
}