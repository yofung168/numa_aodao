<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/14/014
 * Time: 20:53
 */

namespace Numa\Aodao;


use Exception as BaseException;

class NumaException extends BaseException
{

    public function toArray()
    {
        return [
            "error" => 1,
            "message" => $this->getMessage()
        ];
    }

    public function toJson()
    {
        return json_encode($this->toArray());
    }
}