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
];