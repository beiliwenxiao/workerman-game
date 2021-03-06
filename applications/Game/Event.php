<?php
/**
 * 
 * 
 * @author walkor <worker-man@qq.com>
 * 
 */

require_once WORKERMAN_ROOT_DIR . 'applications/Game/Gateway.php';

class Event
{
   /**
    * 当有用户连接时，会触发该方法
    */
   public static function onConnect($message)
   {
       // 通过message验证用户，并获得uid
       $uid = self::checkUser($message);
       // 不合法踢掉
       if(!$uid)
       {
           // 返回失败
           return GateWay::kickCurrentUser('登录失败');
       }
       
       // [这步是必须的]合法，记录uid到gateway通信地址的映射
       GateWay::storeUid($uid);
       
       // [这步是必须的]发送数据包到address对应的gateway，确认connection成功
       GateWay::notifyConnectionSuccess($uid);
       
       // 向当前用户发送uid
       GateWay::sendToCurrentUid(json_encode(array('uid'=>$uid))."\n");
       
       // 广播所有用户，xxx connected
       GateWay::sendToAll(json_encode(array('from_uid'=>'SYSTEM', 'message'=>"$uid come \n", 'to_uid'=>'all'))."\n");
   }
   
   /**
    * 当用户断开连接时触发的方法
    * @param 和该用户gateway通信的地址 $address
    * @param integer $uid 用户id 
    */
   public static function onClose($uid)
   {
       // [这步是必须的]删除这个用户的gateway通信地址
       GateWay::deleteUidAddress($uid);
       
       // 广播 xxx 退出了
       GateWay::sendToAll(json_encode(array('from_uid'=>'SYSTEM', 'message'=>"$uid logout\n", 'to_uid'=>'all'))."\n");
   }
   
   /**
    * 有消息时
    * @param int $uid
    * @param string $message
    */
   public static function onMessage($uid, $message)
   {
        $message_data = json_decode($message, true);
        
        // 向所有人发送
        if($message_data['to_uid'] == 'all')
        {
            return GateWay::sendToAll($message);
        }
        // 向某个人发送
        else
        {
            return GateWay::sendToUid($message_data['to_uid'], $message);
        }
   }
   
   
   /**
    * 用户第一次链接时，根据用户传递的消息（一般是用户名 密码）返回当前uid，这里只是返回了时间戳相关的一个数字
    * @param string $message
    * @return number
    */
   protected static function checkUser($message)
   {
       return substr(strval(microtime(true)), 3, 10)*100;
   }
   
}
