<?php

return [
    //开启调试模式使用调试账号
    "debug_appid" => "100104",
    "debug_secret" => "fxMvLAEETKa4",
    //奥道易通应用ID
    "appid" => env("NUMA_AODAO_APPID", ""),
    //奥道易通应用密钥
    "secret" => env("NUMA_AODAO_SECRET", ""),
    //验证码短信位数
    "code_len" => env('NUMA_AODAO_CODE_LEN', 4),
    //验证码保存时长，默认5分钟
    "code_expire" => env('NUMA_AODAO_CODE_EXPIRE', 5),
    //验证码保存方式,默认文件形式
    "code_cache" => env('NUMA_AODAO_CODE_CACHE', 'file'),
    //充值业务套餐保存时长，默认24*60分钟
    "package_expire" => env('NUMA_AODAO_PACKAGE_EXPIRE', 1440),
    //验证码保存方式,默认文件形式
    "package_cache" => env('NUMA_AODAO_PACKAGE_CACHE', 'file'),
    //优号查查接口地址
    "yhcc_url"=>env('NUMA_AODAO_YHCC_URL','https://yhcc.alinuma.com/wapp/'),
    //优号查查APPid
    "yhcc_appid"=>env('NUMA_AODAO_YHCC_APPID',''),
    //优号查查APPsecret
    "yhcc_appsecret"=>env('NUMA_AODAO_YHCC_APPSECRET',''),
];