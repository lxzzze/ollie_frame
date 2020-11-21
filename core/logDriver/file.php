<?php


namespace core\logDriver;


class file
{
    public function info($message,$path)
    {
        if (is_array($message)){
            $message = json_encode($message);
        }else if (is_object($message)){
            $message = serialize($message);
        }

        error_log('['.date('y-m-d h:m:s').'][info]'.$message.PHP_EOL,3,$path);
    }

}