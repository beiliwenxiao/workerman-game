<?php
/**
 * 
 * 
 * @author walkor <worker-man@qq.com>
 * 
 */

require_once WORKERMAN_ROOT_DIR . 'applications/Game/Store.php';

class Event
{
    /**
     * 当有用户连接时，会触发该方法
     * @param string $address 和该用户gateway通信的地址
     * @param integer $socket_id 该用户链接的socketid
     * @param string $sid sessionid或者说是客户端id
     */
   public static function onConnect($address, $socket_id, $sid)
   {
       // 检查sid是否合法
       $uid = self::getUidBySid($sid);
       // 不合法踢掉
       if(!$uid)
       {
           self::kickAddress($address, $socket_id);
           return;
       }
       
       // 合法，记录uid到gateway通信地址的映射
       self::storeUidAddress($uid, $address);
       
       // 发送数据包到address对应的gateway，确认connection成功
       self::notifyConnectionSuccess($address, $socket_id, $uid);
       
       /**
        * 业务的其它逻辑
        * 。。。。。。。。
        */
   }
   
   /**
    * 当用户断开连接时触发的方法
    * @param 和该用户gateway通信的地址 $address
    * @param integer $uid 用户id 
    */
   public static function onClose($address, $uid)
   {
       $buf = new Gamebuffer();
       $buf->header['cmd'] = GameBuffer::CMD_GATEWAY;
       $buf->header['sub_cmd'] = GameBuffer::SCMD_BROADCAST;
       $buf->header['from_uid'] = $uid;
       $buf->body = "logout bye!!!";
       // 广播所有人，这个用户退出了
       GameBuffer::sendToAll($buf->getBuffer());
       // 删除这个用户的gateway通信地址
       self::deleteUidAddress($uid);
   }
   
   public static function kickUid($uid)
   {
       
   }
   
   public static function kickAddress($address, $socket_id)
   {
     
   }
   
   public static function storeUidAddress($uid, $address)
   {
       Store::set($uid, $address);
   }
   
   public static function getAddressByUid($uid)
   {
       return Store::get($uid);
   }
   
   public static function deleteUidAddress($uid)
   {
       return Store::delete($uid);
   }
   
   protected static function notifyConnectionSuccess($address, $socket_id, $uid)
   {
       $buf = new GameBuffer();
       $buf->header['cmd'] = GameBuffer::CMD_GATEWAY;
       $buf->header['sub_cmd'] = GameBuffer::SCMD_CONNECT_SUCCESS;
       $buf->header['from_uid'] = $socket_id;
       $buf->header['to_uid'] = $uid;
       GameBuffer::sendToGateway($address, $buf->getBuffer());
   }
   
   protected static function getUidBySid($sid)
   {
       return $sid;
   }
}
