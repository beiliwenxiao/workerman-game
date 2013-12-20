<?php 
/**
 * 二进制协议
 * 
 * struct BufferProtocol
 * {
 *     unsigned char     version,//版本
 *     unsigned short    series_id,//序列号 udp协议使用
 *     unsigned short    cmd,//主命令字
 *     unsigned short    sub_cmd,//子命令字
 *     int                         code,//返回码
 *     unsigned int        from_uid,//来自用户uid
 *     unsigned int        to_uid,//发往的uid
 *     unsigned int       pack_len,//包长
 *     char[pack_length-HEAD_LEN] body//包体
 * }
 * 
 * @author walkor <worker-man@qq.com>
 */

class Buffer
{
    /**
     * 版本
     * @var integer
     */
    const VERSION = 0x01;
    
    /**
     * 包头长度
     * @var integer
     */
    const HEAD_LEN = 23;
     
    /**
     * 序列号，防止串包
     * @var integer
     */
    protected static $seriesId = 0;
    
    /**
     * 协议头
     * @var array
     */
    public $header = array(
        'version'        => self::VERSION,
        'series_id'      => 0,
        'cmd'            => 0,
        'sub_cmd'     => 0,
        'code'           => 0,
        'from_uid'    => 0,
        'to_uid'         => 0,
        'pack_len'    => self::HEAD_LEN
    );
    
    /**
     * 包体
     * @var string
     */
    public $body = '';
    
    /**
     * 初始化
     * @return void
     */
    public function __construct($buffer = null)
    {
        if($buffer)
        {
            $data = self::bufferToData($buffer);
            $this->body = $data['body'];
            unset($data['body']);
            $this->header = $data;
        }
        else
        {
            if(self::$seriesId>=65535)
            {
                self::$seriesId = 0;
            }
            else
            {
                $this->header['series_id'] = self::$seriesId++;
            }
        }
    }
    
    /**
     * 判断数据包是否都到了
     * @param string $buffer
     * @return int int=0数据是完整的 int>0数据不完整，还要继续接收int字节
     */
    public static function input($buffer, &$data = null)
    {
        $len = strlen($buffer);
        if($len < self::HEAD_LEN)
        {
            return self::HEAD_LEN - $len;
        }
        
        $data = unpack("Cversion/Sseries_id/Scmd/Ssub_cmd/icode/Ifrom_uid/Ito_uid/Ipack_len", $buffer);
        if($data['pack_len'] > $len)
        {
            return $data['pack_len'] - $len;
        }
        $data['body'] = '';
        $body_len = $data['pack_len'] - self::HEAD_LEN;
        if($body_len > 0)
        {
            $data['body'] = substr($buffer, self::HEAD_LEN, $body_len);
        }
        return 0;
    }
    
    
    /**
     * 设置包体
     * @param string $body_str
     * @return void
     */
    public function setBody($body_str)
    {
        $this->body = (string) $body_str;
    }
    
    /**
     * 获取整个包的buffer
     * @param string $data
     * @return string
     */
    public function getBuffer()
    {
        $this->header['pack_len'] = self::HEAD_LEN + strlen($this->body);
        return pack("CSSSiIII", $this->header['version'],  $this->header['series_id'], $this->header['cmd'], $this->header['sub_cmd'], $this->header['code'], $this->header['from_uid'], $this->header['to_uid'], $this->header['pack_len']).$this->body;
    }
    
    /**
     * 从二进制数据转换为数组
     * @param string $buffer
     * @return array
     */    
    public static function decode($buffer)
    {
        $data = unpack("Cversion/Sseries_id/Scmd/Ssub_cmd/icode/Ifrom_uid/Ito_uid/Ipack_len", $buffer);
        $data['body'] = '';
        $body_len = $data['pack_len'] - self::HEAD_LEN;
        if($body_len > 0)
        {
            $data['body'] = substr($buffer, self::HEAD_LEN, $body_len);
        }
        return $data;
    }
    
}



