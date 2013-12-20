<?php
/**
 * 
 * 压测worker
 * @author walkor <worker-man@qq.com>
 */
require_once WORKERMAN_ROOT_DIR . 'man/Core/SocketWorker.php';
class EchoWorker extends Man\Core\SocketWorker
{
    public function dealInput($recv_str)
    {
        return 0; 
    }

    public function dealProcess($recv_str)
    {
        $this->sendToClient($recv_str);
    }
}
