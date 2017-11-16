<?php
/**
 * 业务类型常量
 * User: Administrator
 * Date: 2017/11/14/014
 * Time: 21:02
 */

namespace Numa\Aodao;


class Type
{
    const SMS_VERYCODE = 1;//验证码业务
    const SMS_NOTICE = 2;//短信通知业务
    const SMS_INTERACT = 3;//营销互动（上行互动）
    const SMS_VOICENOTICE = 4;//语音通知
    const SMS_BATCHSEND = 5;//会员短信群发

    const RECHARGE_FLOW = 13;//流量钱包
    const RECHARGE_FARE = 14;//话费钱包
    const RECHARGE_OIL = 15;//油卡钱包
    const RECHARGE_CERTIFY = 16;//手机三元素实名认证

    //业务通道
    const BUSINESS_MAIL = "sendmail";//邮件业务
    const BUSINESS_OIL = "sendoil";//加油卡业务
    const BUSINESS_LIU = "sendliu";//流量业务
    const BUSINESS_TECH = "sendtach";//话费业务

}