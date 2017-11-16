<?php
/**
 * 奥道易通短信业务
 * User: EdwinLee
 * Date: 2017/11/14/014
 * Time: 20:41
 */

namespace Numa\Aodao;


use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class Sms
{
    protected $app_id;
    protected $secret;

    //验证码缓存前缀
    const CACHE_KEY_PREFIX = "aodao_code_cache";

    public function __construct($app_id, $secret)
    {
        $this->app_id = $app_id;
        $this->secret = $secret;
    }

    /**
     * 验证类型短信
     *
     * @param $mobile
     * @param content 内容（必须包含$code）
     * @param task 任务名称
     * @return array|mixed
     * @throws NumaException
     */
    public function code($mobile, $content, $task = '')
    {
        if ($content == '') {
            throw new NumaException('内容不能为空');
        }
        //判断content中是否含有$code信息
        if (!strpos($content, '${code}')) {
            throw new NumaException('内容中必须包含${code}');
        }
        //随机生成验证码（验证码位数）
        $code = $this->_createVeryCode();
        //保存验证码
        $this->_cacheVeryCode($mobile, $code);
        $datas = [];
        $datas['mobile'] = $mobile;
        $datas['typeid'] = Type::SMS_VERYCODE;
        $datas['content'] = str_replace('${code}', $code, $content);
        $datas['name'] = $task;
        $result = $this->_send($datas);
        return $result;
    }

    /**
     * 语音短信
     *
     * @param $mobile
     * @param string $inform_con 签名
     * @param string $task 任务名称
     * @return array|mixed
     * @throws NumaException
     */
    public function vcode($mobile, $inform_con, $task = '')
    {
        if ($inform_con == '') {
            throw new NumaException('签名不能为空');
        }
        //随机生成验证码（验证码位数）
        $code = $this->_createVeryCode();
        //保存验证码
        $this->_cacheVeryCode($mobile, $code);
        $datas = [];
        $datas['mobile'] = $mobile;
        $datas['typeid'] = Type::SMS_VOICENOTICE;
        $datas['content'] = $code;
        $datas['inform_con'] = $inform_con;
        $datas['name'] = $task;
        $result = $this->_send($datas);
        return $result;
    }

    /**
     * 验证码是否正确
     *
     * @param $mobile
     * @param $code
     * @return bool
     */
    public function checkCode($mobile, $code)
    {
        $cache_type = config('aodao.code_cache', 'file');
        $key = self::CACHE_KEY_PREFIX . "_" . $mobile;
        $cache_code = Cache::store($cache_type)->get($key);
        if (is_null($cache_code)) {
            return false;
        } else {
            if (Hash::check($code, $cache_code)) {
                return true;
            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * 生成验证码及语音验证码
     *
     * @return string
     */
    private function _createVeryCode()
    {
        //随机生成验证码（验证码位数）
        $code_length = config('aodao.code_len', 4);
        $code = Common::random_srt($code_length, true);
        return $code;
    }

    /**
     * 保存验证码及语音验证码
     *
     * @param $mobile 手机号码
     * @param $code 验证码
     */
    private function _cacheVeryCode($mobile, $code)
    {
        $code_expire = config('aodao.code_expire', 5);
        $cache_type = config('aodao.code_cache', 'file');
        $key = self::CACHE_KEY_PREFIX . "_" . $mobile;
        Cache::store($cache_type)->put($key, Hash::make($code), $code_expire);
    }

    /**
     * 单条会员通知短信
     *
     * @param $mobile 手机号码
     * @param $content 内容
     * @param $task 任务名称
     * @param $sendtime 定时发送
     * @return array|mixed
     * @throws NumaException
     */
    public function notice($mobile, $content, $task = '', $sendtime = 0)
    {
        if ($content == "") {
            throw new NumaException('发送内容不能为空');
        }
        $datas = [];
        $datas['mobile'] = $mobile;
        $datas['typeid'] = Type::SMS_NOTICE;
        $datas['content'] = $content;
        $datas['name'] = $task;
        if ($sendtime > 0) {
            $datas['sendtime'] = $sendtime;
        }
        $result = $this->_send($datas);
        return $result;
    }

    /**
     * 会员相同内容群发
     *
     * @param $mobiles
     * @param $content
     * @param string $task
     * @return array|mixed
     * @throws NumaException
     */
    public function noticeBatch($mobiles, $content, $task = '', $sendtime = 0)
    {
        if (!is_array($mobiles)) {
            throw new NumaException('请传入数组');
        }
        //去除重复
        $mobiles = array_unique($mobiles);
        if (count($mobiles) > 1000) {
            throw new NumaException('每次最多传入1000条数据');
        }
        if ($content == "") {
            throw new NumaException('发送内容不能为空');
        }
        $mobiles = implode(",", $mobiles);
        $datas = [];
        $datas['mobile'] = $mobiles;
        $datas['typeid'] = TYPE::SMS_NOTICE;
        $datas['content'] = $content;
        if ($task != "") {
            $datas['name'] = $task;
        }
        if ($sendtime) {
            $datas['sendtime'] = $sendtime;
        }
        $result = $this->_send($datas);
        return $result;
    }

    /**
     * 会员短信群发（早9晚8）
     *
     * 非即时性发送
     * @param $mobiles
     * @param $content
     * @param string $task
     * @param int $sendtime
     */
    public function batch($mobiles, $content, $task = '', $sendtime = 0)
    {
        if (!is_array($mobiles)) {
            throw new NumaException('请传入数组');
        }
        //去除重复
        $mobiles = array_unique($mobiles);
        if (count($mobiles) > 1000) {
            throw new NumaException('每次最多传入1000条数据');
        }
        if ($content == "") {
            throw new NumaException('发送内容不能为空');
        }
        $mobiles = implode(",", $mobiles);
        $datas = [];
        $datas['mobile'] = $mobiles;
        $datas['typeid'] = TYPE::SMS_BATCHSEND;
        $datas['content'] = $content;
        if ($task != "") {
            $datas['name'] = $task;
        }
        if ($sendtime) {
            $datas['sendtime'] = $sendtime;
        }
        $result = $this->_send($datas);
        return $result;
    }

    /**
     * 短信发送处理
     *
     * @param array $datas
     * @param int $debug
     * @return array|mixed
     */
    private function _send($datas = [], $debug = 0)
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
                    $res = @json_decode($res, TRUE);
                    if ($debug) {
                        if ($res['data'] == '10021') {
                            return array("error" => 0, "message" => "接口正常");
                        } else {
                            $result['error'] = 1;
                            $result['message'] = "接口异常";
                            return $result;
                        }
                    } else {
                        if (isset($res['ret_code']) && $res['ret_code'] == 0) {
                            if (isset($res['data']['err_code']) && $res['data']['err_code'] == '10001') {
                                $result['error'] = 0;
                                $result['message'] = "接口成功";
                                $result['data'] = $res['data'];
                            } else {
                                $result['error'] = 1;
                                $result['message'] = Error::info($res['data']['err_code']);
                            }
                            return $result;
                        } else {
                            $result['error'] = 1;
                            $result['message'] = isset($res['msg']) ? $res['msg'] : '接口失败';
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
        $param_a = Aodao::PARAM_A;
        if ($param_a == '') {
            $param_a = 'we7cms';
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
        if (isset($datas["name"]) && $datas['name'] != '') {
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
        $agv = array(
            'appid' => $appid,
            'appkey' => $secret,
            'mobile' => trim($datas['mobile']),
            'time' => $t,
            'token' => $token,
            'sendtime' => $sendtime,
            'sign' => Common::createSign($token, $appid, $secret, $t),
            'inform_con' => isset($datas['inform_con']) ? $datas['inform_con'] : '',//签名内容
            'content' => $datas['content'],//短信内容
            'typeid' => $datas['typeid'],
            'name' => $name,
        );
        return $agv;
    }
}