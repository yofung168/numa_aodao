<?php

return [
    //开启调试模式使用调试账号
    "debug" => env('NUMA_AODAO_DEGUG', false),
    "debug_appid" => "100104",
    "debug_secret" => "fxMvLAEETKa4",
    //奥道易通应用ID
    "appid" => env("NUMA_AODAO_APPID", ""),
    //奥道易通应用密钥
    "secret" => env("NUMA_AODAO_SECRET", ""),
    //短信接口模式1免审0非免审
    "sms_mode" => env('NUMA_AODAO_MODE', 0),
];