# numa_aodao
奥道易通短信接口laravel扩展包

开发中。。。暂不对外开放

#短信接口使用

获取短信服务对象 $sms = Aodao::sms();

##1验证码短信
###1.1发送验证码短信
方法名：code（mobile,content,task）<br/>
#####说明：
1、mobile 手机号码（必填）<br/>
2、content 发送内容（必填）<br/>
3、task 任务名（选填）<br/>
**发送内容中必须包含${code}验证码占位符**<br/>
**发送内容中必须包含签名，签名使用【】加在末尾**
###1.2发送语音验证码
方法名：vcode(mobile,inform_con)<br/>
#####说明
1、mobile 手机号码 必填<br/>
2、inform_con 签名 必填<br/>