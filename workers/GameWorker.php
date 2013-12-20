<?php
/**
 * 
 * 处理具体逻辑
 * 
 * @author walkor <worker-man@qq.com>
 * 
 */
require_once WORKERMAN_ROOT_DIR . 'man/Core/SocketWorker.php';
require_once WORKERMAN_ROOT_DIR . 'applications/Game/Protocols/GameBuffer.php';
require_once WORKERMAN_ROOT_DIR . 'applications/Game/Event.php';
require_once WORKERMAN_ROOT_DIR . 'applications/Game/User.php';

class GameWorker extends Man\Core\SocketWorker
{
    protected $data = array();
    public function dealInput($recv_str)
    {
        return GameBuffer::input($recv_str, $this->data); 
    }

    public function dealProcess($recv_str)
    {
        if(!isset(GameBuffer::$cmdMap[$this->data['cmd']]) || !isset(GameBuffer::$scmdMap[$this->data['sub_cmd']]))
        {
            $this->notice('cmd err ' . serialize($this->data) );
            return;
        }
        $class = GameBuffer::$cmdMap[$this->data['cmd']];
        $method = GameBuffer::$scmdMap[$this->data['sub_cmd']];
        if(!method_exists($class, $method))
        {
            if($class == 'System')
            {
                switch($this->data['sub_cmd'])
                {
                    case GameBuffer::SCMD_ON_CONNECT:
                        call_user_func_array(array('Event', 'onConnect'), array('udp://'.$this->getRemoteIp().':'.$this->data['to_uid'], $this->data['from_uid'], $this->data['body']));
                        return;
                    case GameBuffer::SCMD_ON_CLOSE:
                        call_user_func_array(array('Event', 'onClose'), array('udp://'.$this->getRemoteIp().':'.$this->data['to_uid'], $this->data['from_uid']));
                        return; 
                }
            }
            $this->notice("cmd err $class::$method not exists");
            return;
        }
        call_user_func_array(array($class, $method),  array($this->data));
    }
}
