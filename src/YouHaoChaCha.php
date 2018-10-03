<?php
/**
 * 优号查查对外API接口
 */

namespace Numa\Aodao;

class YouHaoChaCha
{
    protected $app_id;
    protected $app_secret;
    protected $url;
    const SEND_URL = "https://yhcc.alinuma.com/wapp/";

    public function __construct($config = [])
    {
        $this->app_id = $config['yhcc_appid'];
        $this->app_secret = $config['yhcc_appsecret'];
        $url = $config['yhcc_url'];
        if ($url == '') {
            $url = self::SEND_URL;
        }
        $this->url = $url;
    }

    /**
     * 账号信息
     *
     * @param $account_no 账号
     * @return array|mixed
     * @throws NumaException
     */
    public function info($account_no)
    {
        if ($account_no == '') {
            throw new NumaException('账号不能为空');
        }
        $datas = [];
        $datas['no'] = $account_no;
        $result = $this->_send('account/info', $datas);
        return $result;
    }

    /**
     * 获取账号对他人评价记录
     *
     * @param $account_no 账号
     * @param $page =1
     * @param $size =10
     * @return array|mixed
     * @throws NumaException
     */
    public function rate($account_no, $page = 1, $size = 10)
    {
        if ($account_no == '') {
            throw new NumaException('账号不能为空');
        }
        $datas = [];
        $datas['no'] = $account_no;
        $datas['page'] = $page;
        $datas['size'] = $size;
        $result = $this->_send('account/rate', $datas);
        return $result;
    }

    /**
     * 接口发送处理
     *
     * @param array $datas
     * @param int $debug
     * @return array|mixed
     */
    private function _send($path, $datas = [], $debug = 0)
    {
        $result = ['error' => 0, "message" => "成功", "data" => []];
        if (empty($datas)) {
            $result['error'] = 1;
            $result['message'] = "缺少参数";
            return $result;
        } else {
            if (is_array($datas)) {
                try {
                    $url = $this->url . $path;
                    $data = $this->_createArguments($datas, $debug);
                    $headers[] = "Content-Type: application/x-www-form-urlencoded";
                    $res = Common::http_post($url, $data, $headers);
                    $res = @json_decode($res, TRUE);
                    return $res;
                } catch (NumaException $exception) {
                    $result['code'] = 0;
                    $result['message'] = $exception->getMessage();
                    return $result;
                }
            } else {
                $result['code'] = 0;
                $result['message'] = "非合法数组";
                return $result;
            }
        }
    }

    /**
     * 生成参数信息
     *
     * @param $datas
     * @param int $debug
     * @return array
     */
    private function _createArguments($datas)
    {
        $appid = $this->app_id;
        $secret = $this->app_secret;
        $t = time();
        $data = array(
            'appid' => $appid,
            'appkey' => $secret,
            'timestamp' => $t
        );
        $data = array_merge($data, $datas);
        $sign = $this->_createSignature($data);
        $data['sign'] = $sign;
        return $data;
    }

    /**
     * 根据请求数据计算签名
     *
     * @param $datas
     * @param array $extra_datas
     * @return mixed
     */
    private function _createSignature($datas, $extra_datas = [])
    {
        //获取加密项目
        $datas = array_merge($datas, $extra_datas);
        //去除签名
        unset($datas['sign']);
        //按照键值排序
        ksort($datas);
        //获取签名字符串
        $_str_signatrue = "";
        foreach ($datas as $k => $v) {
            $_str_signatrue .= $k . "=" . $v;
        }
        //加密方法
        $sign_method = isset($datas['sign_method']) ? $datas['sign_method'] : 'md5';
        $sign = $sign_method($_str_signatrue);
        return $sign;
    }
}