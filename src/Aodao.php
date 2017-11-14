<?php
/**
 * 奥道接口
 */

namespace Numa\Aodao;

class Aodao
{
    protected $app_id;
    protected $app_secret;

    const SEND_URL = "https://sms.aodao.com.cn/api/api.php";
    //免审短信通道
    const PARAM_A = "alinuma";
    //短信实例
    protected $sms = null;
    //充值实例
    protected $recharge = null;

    public function __construct($config = [])
    {
        $this->app_id = $config['app_id'];
        $this->app_secret = $config['secret'];
    }

    /**
     * 如果访问属性不存在时调用方法
     *
     * @param $name
     * @return mixed
     * @throws NumaException
     */
    public function __call($name)
    {
        $className = ucfirst($name);
        if ($this->$name != null) {
            return $this->$name;
        } else {
            if (class_exists($className)) {
                $this->$name = new $className($this->app_id,$this->secrect);
            } else {
                throw new NumaException("类不存在");
            }
            return $this->$name;
        }
    }
}