<?php
/**
 * 奥道易通充值业务
 * User: EdwinLee
 * Date: 2017/11/14/014
 * Time: 20:46
 */

namespace Numa\Aodao;


use Illuminate\Support\Facades\Cache;
use Mockery\Exception;

class Recharge
{
    protected $app_id;
    protected $secret;
    //奥道充值业务类型缓存前缀
    const CACHE_KEY_PREFIX = "aodao_recharge_types";

    public function __construct($app_id, $secret)
    {
        $this->app_id = $app_id;
        $this->secret = $secret;
    }

    /**
     * 查询业务套餐包
     *
     * @param string $type [flow,oil,fare,certify]
     * @param $cache 是否缓存
     * @return array|mixed
     * @throws NumaException
     */
    public function packages($type = 'flow', $cache = true)
    {
        $datas = ["cklist" => 1];
        switch ($type) {
            case 'flow'://流量
                $datas['typeid'] = Type::RECHARGE_FLOW;
                $business_type = Type::BUSINESS_LIU;
                break;
            case 'oil'://油卡
                $datas['typeid'] = Type::RECHARGE_OIL;
                $business_type = Type::BUSINESS_OIL;
                break;
            case 'fare'://手机话费
                $datas['typeid'] = Type::RECHARGE_FARE;
                $business_type = Type::BUSINESS_TECH;
                break;
            case 'certify'://手机三元素实名认证
                $datas['typeid'] = Type::RECHARGE_CERTIFY;
                $business_type = Type::BUSINESS_TECH;
                break;
            default:
                throw new NumaException('业务类型不存在');
        }
        $packages = null;
        //如果缓存先从缓存获取数据
        if ($cache) {
            $packages = json_decode($this->_getPackagesFromCache($type), true);
        }
        //如果缓存中数据为空则从接口获取
        if (is_null($packages)) {
            $result = $this->_send($business_type, $datas, $cache);
            if ($result['error'] == 0) {
                $packages = $result['data'];
                return $packages;
            } else {
                return $result;
            }
        }
        return $packages;
    }

    /**
     * 从缓存获取套餐包
     *
     * @param $business_type
     * @return mixed
     */
    private function _getPackagesFromCache($business_type)
    {
        $cache_type = config('aodao.package_cache', 'file');
        $key = self::CACHE_KEY_PREFIX . "_" . $business_type;
        return Cache::store($cache_type)->get($key);
    }

    /**
     * 流量充值接口
     *
     * @param $recharge_id 充值套餐ID
     * @param $mobile
     * @param $order_no 任务名称或自定义订单编号
     * @return array|mixed
     * @throws NumaException
     */
    public function flow($recharge_id, $mobile, $order_no)
    {
        if ($mobile == '') {
            throw new NumaException('手机号码不能为空');
        }
        if (empty($recharge_id)) {
            throw new NumaException('请选择流量套餐');
        }
        if (empty($task)) {
            throw new NumaException('充值订单号不能为空');
        }
        $datas = [];
        $datas['mobile'] = $mobile;
        $datas['recharge_id'] = $recharge_id;
        $datas['typeid'] = Type::RECHARGE_FLOW;
        if ($order_no != "") {
            $datas['name'] = $order_no;
        }
        return $this->_send(Type::BUSINESS_LIU, $datas);
    }

    /**
     * 话费充值接口
     *
     * @param $recharge_id 充值套餐ID
     * @param $mobile
     * @param $order_no 任务名称或自定义订单编号
     * @return array|mixed
     * @throws NumaException
     */
    public function fare($recharge_id, $mobile, $order_no)
    {
        if ($mobile == '') {
            throw new NumaException('手机号码不能为空');
        }
        if (empty($recharge_id)) {
            throw new NumaException('请选择话费套餐');
        }
        if (empty($task)) {
            throw new NumaException('充值订单号不能为空');
        }
        $datas = [];
        $datas['mobile'] = $mobile;
        $datas['recharge_id'] = $recharge_id;
        $datas['typeid'] = Type::RECHARGE_FARE;
        if ($order_no != "") {
            $datas['name'] = $order_no;
        }
        return $this->_send(Type::BUSINESS_TECH, $datas);
    }

    /**
     * 油卡充值接口
     *
     * @param $recharge_id 充值套餐ID
     * @param $mobile 油卡绑定的手机号
     * @param $cardid 油卡编号
     * @param $order_no 任务名称或自定义订单编号
     * @return array|mixed
     * @throws NumaException
     */
    public function oil($recharge_id, $mobile, $cardid, $order_no)
    {
        if ($mobile == '') {
            throw new NumaException('加油卡绑定的手机号码不能为空');
        }
        if (empty($cardid)) {
            throw new NumaException('加油卡卡号不能为空');
        }
        if (empty($recharge_id)) {
            throw new NumaException('请选择流量套餐');
        }
        if (empty($task)) {
            throw new NumaException('充值订单号不能为空');
        }
        $datas = [];
        $datas['mobile'] = $mobile;
        $datas['recharge_id'] = $recharge_id;
        $datas['cardid'] = $cardid;
        $datas['typeid'] = Type::RECHARGE_OIL;
        if ($order_no != "") {
            $datas['name'] = $order_no;
        }
        return $this->_send(Type::BUSINESS_OIL, $datas);
    }

    /**
     * 缓存业务套餐信息
     *
     * @param $mobile 手机号码
     * @param $code 验证码
     */
    private function _cachePackages($business_type, $data)
    {
        $code_expire = config('aodao.package_expire', 24 * 60);
        $cache_type = config('aodao.package_cache', 'file');
        $key = self::CACHE_KEY_PREFIX . "_" . $business_type;
        Cache::store($cache_type)->put($key, json_encode($data), $code_expire);
    }

    /**
     * 充值业务处理
     *
     * @param $business_type 业务类型
     * @param array $datas
     * @param boolean $cache 缓存
     * @return array|mixed
     */
    private function _send($business_type, $datas = [], $cache = false)
    {
        $result = ['error' => 0, "message" => "成功", "data" => []];
        if (empty($datas)) {
            $result['error'] = 1;
            $result['message'] = "缺少参数";
            return $result;
        } else {
            if (is_array($datas)) {
                try {
                    $agv = $this->_createArguments($datas);
                    $post_datas = [
                        'a' => $business_type,
                        'agv' => json_encode($agv)
                    ];
                    $headers[] = "Content-Type: application/x-www-form-urlencoded";

                    $res = Common::http_post(Aodao::SEND_URL, $post_datas, $headers);
                    $res = @json_decode($res, TRUE);
                    if (isset($res['ret_code']) && $res['ret_code'] == 0) {
                        $result['error'] = 0;
                        $result['message'] = "接口成功";
                        $result['data'] = isset($res['data']) ? $res['data'] : '';
                        if (isset($datas['cklist']) && isset($res['data']) && $cache) {
                            $this->_cachePackages($business_type, $res['data']);
                        }
                        $result['time'] = isset($res['time']) ? $res['time'] : time();
                        return $result;
                    } else {
                        $result['error'] = 1;
                        $result['code'] = isset($res['ret_code']) ? $res['ret_code'] : -1;
                        $result['message'] = isset($res['redirect']) ? $res['redirect'] : '接口失败';
                        return $result;
                    }
                } catch (NumaException $exception) {
                    $result['error'] = 1;
                    $result['code'] = -1;
                    $result['message'] = $exception->getMessage();
                    return $result;
                }
            } else {
                $result['error'] = 1;
                $result['code'] = -1;
                $result['message'] = "非合法数组";
                return $result;
            }
        }
    }

    /**
     * 生成参数信息
     *
     * @param $datas
     * @param int $query 1充值业务0查询业务
     * @return array
     */
    private function _createArguments($datas)
    {
        $appid = $this->app_id;
        $secret = $this->secret;
        $token = Common::random_srt(8);
        $t = time();
        $recharge = isset($datas['cklist']) ? 0 : 1;
        $agv = array(
            'appid' => $appid,
            'appkey' => $secret,
            'time' => $t,
            'token' => $token,
            'sign' => Common::createSign($token, $appid, $secret, $t),
            'typeid' => $datas['typeid'],
        );
        if ($recharge == 1) {
            $name = $datas['name'] . "_" . date('YmdHis', $t) . '_' . $token;
            if (isset($datas["sendtime"])) {
                $sendtime = $datas["sendtime"];
            } else {
                $sendtime = $t;
            }
            $mobile = trim($datas['mobile']);
            $recharge_id = $datas['recharge_id'];
            $recharge_ck = base64_encode($recharge_id . "_" . $mobile . "_" . $t);
            $extra_agvs = [
                'sendtime' => $sendtime,
                'name' => $name,
                'recharge_ck' => $recharge_ck,
                'mobile' => $mobile,
            ];
            if (isset($datas['cardid'])) {
                $extra_agvs['cardid'] = $datas['cardid'];
            }
        } else {
            $extra_agvs = [
                'cklist' => 1,
            ];
        }
        $agv = array_merge($agv, $extra_agvs);
        return $agv;
    }
}