<?php
/**
 * 奥道易通短信业务
 * User: Administrator
 * Date: 2017/11/14/014
 * Time: 20:41
 */

namespace Numa\Aodao;


class Sms
{
    protected $app_id;
    protected $secret;

    public function __construct($app_id, $secret)
    {
        $this->app_id = $app_id;
        $this->secret = $secret;
    }

    /**
     * 短信发送接口
     *
     * @param array $datas
     * @param int $debug
     * @return array|mixed
     */
    public function send($datas = [], $debug = 0)
    {
        $result = ['error' => 0, "message" => "成功", "data" => []];
        if (empty($datas)) {
            $result['error'] = 1;
            $result['message'] = "缺少参数";
            return $result;
        } else {
            if (is_array($datas)) {
                try {
                    $agv = $this->_createArguments($datas, $debug);
                    $post_datas = [
                        'a' => $this->_createParamA(),
                        'agv' => json_encode($agv)
                    ];
                    $headers[] = "Content-Type: application/x-www-form-urlencoded";

                    $res = Common::http_post(Aodao::SEND_URL, $post_datas, $headers);
                    $result = @json_decode($res, TRUE);
                    if ($debug) {
                        if ($result['data'] == '10021') {
                            return array("error" => 0, "message" => "接口正常");
                        } else {
                            $result['error'] = 1;
                            $result['message'] = "接口异常";
                            return $result;
                        }
                    } else {
                        if (isset($result['ret_code']) && $result['ret_code'] == 0) {
                            if (isset($result['data']['err_code']) && $result['data']['err_code'] == '10001') {
                                $result['error'] = 0;
                                $result['message'] = "接口成功";
                                $result['data'] = $result['data'];
                            } else {
                                $result['error'] = 1;
                                $result['message'] = Error::info($result['data']['err_code']);
                            }
                            return $result;
                        } else {
                            $result['error'] = 1;
                            $result['message'] = isset($result['msg']) ? $result['msg'] : '接口失败';
                            return $result;
                        }
                    }
                } catch (NumaException $exception) {
                    $result['error'] = 1;
                    $result['message'] = $exception->getMessage();
                    return $result;
                }
            } else {
                $result['error'] = 1;
                $result['message'] = "非合法数组";
                return $result;
            }
        }
    }

    /**
     * 根据模式生成A参数
     *
     * @return string
     */
    private function _createParamA()
    {
        if (config('aodao.mode') == 1) {//免审模式
            $param_a = Aodao::PARAM_A;
        } else {
            $param_a = 'sendsms';
        }
        return $param_a;
    }

    /**
     * 生成参数信息
     *
     * @param $datas
     * @param int $debug
     * @return array
     */
    private function _createArguments($datas, $debug = 0)
    {
        $appid = $this->app_id;
        $secret = $this->secret;
        if ($debug) {
            $appid = config('aodao.debug_appid');
            $secret = config('aodao.debug_secret');
        }
        $token = Common::random_srt(8);
        $t = time();
        if (isset($datas["name"])) {
            $name = $datas['name'] . "_" . date('YmdHis', $t) . '_' . $token;
        } elseif (isset($datas["sitecode"])) {
            $name = date('YmdHis', $t) . '_' . $token . '_' . $datas['sitecode'];
        } else {
            $name = date('YmdHis', $t) . '_' . $token;
        }
        if (isset($datas["sendtime"])) {
            $sendtime = $datas["sendtime"];
        } else {
            $sendtime = $t;
        }
        if (config('aodao.mode') == 1) {//免审模式
            if (!isset($datas['inform_con'])) {
                throw new NumaException('缺少签名内容[inform_con]');
            }
            if (!isset($datas['content'])) {
                throw new NumaException('缺少短信内容[content]');
            }
            $agv = array(
                'appid' => $appid,
                'appkey' => $secret,
                'mobile' => trim($datas['mobile']),
                'time' => $t,
                'token' => $token,
                'sendtime' => $sendtime,
                'sign' => Common::createSign($token, $appid, $secret, $t),
                'inform_con' => $datas['inform_con'],//签名内容
                'content' => $datas['content'],//短信内容
                'typeid' => $datas['typeid'],
                'name' => $name,
            );
        } else {
            if (!isset($datas['inform_id'])) {
                throw new NumaException('缺少签名模板ID[inform_id]');
            }
            if (!isset($datas['template_id'])) {
                throw new NumaException('缺少内容模板ID[template_id]');
            }
            if (!isset($datas['content'])) {
                throw new NumaException('缺少模板变量值[content]');
            }
            $agv = array(
                'appid' => $appid,
                'appkey' => $secret,
                'mobile' => trim($datas['mobile']),
                'time' => $t,
                'token' => $token,
                'sendtime' => $sendtime,
                'sign' => Common::createSign($token, $appid, $secret, $t),
                'inform_type' => $datas['inform_id'],//签名模板ID
                'template_type' => $datas['template_id'],//模板内容ID
                'content_variable' => $datas['content'],//模板变量值
                'typeid' => $datas['typeid'],
                'name' => $name,
            );
        }
        return $agv;
    }
}