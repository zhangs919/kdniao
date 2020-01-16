<?php

namespace Laravelvip\Kdniao;


/**
 * Class singlePlane    快递鸟电子面单API
 * @package KdNiao
 * @author  TangPeng <1257390133@qq.com>
 * @Date    2017/12/15
 * @since   1.0
 * @link    http://www.kdniao.com/api-eorder    快递鸟电子面单API文档地址
 */
class Sheet{
    //快递鸟配置
    protected $config = array(
        'EBusinessID'   =>  '商户ID',
        'AppKey'        =>  'API key',
        'ReqUrl'        =>  '1'
    );
    //电子面单-物流配置
    private $logistics  = array();
    //电子面单-收件方信息
    private $receiver   = array();
    //电子面单-配送方信息
    private $sender     = array();
    //电子面单-商品信息 二维数组
    private $goods      = array();
    //会员标识
    public  $MemberID;
    //快递单号
    public  $LogisticCode;
    //通知快递员上门揽件     1：不通知 0：通知
    public $IsNotice = 0;
    //运费
    public $Cost;
    //上门取货时间段
    public $StartDate;
    public $EndDate;
    //物品总重量
    public $Weight;
    //件数/包裹数
    public $Quantity;
    //物品总体积
    public $Volume;
    //备注
    public $Remark;
    //返回电子面单模板  0：不需要 1：需要
    public $IsReturnPrintTemplate = 0;
    /**
     * singlePlane constructor.
     * @param array $config 配置商户ID、AppKey、请求URL
     */
    public function __construct( $config = array() )
    {
        if( !isset( $config['EBusinessID'] ) ) exit( '请配置商户ID' );
        if( !isset( $config['AppKey'] ) ) exit( '请配置AppKey' );
        if( isset( $config['ReqUrl'] ) ){
            if( (int)$config['ReqUrl'] == 1 ){
                $config['ReqUrl'] = 'http://api.kdniao.cc/api/Eorderservice';
            }else{
                $config['ReqUrl'] = 'http://testapi.kdniao.cc:8081/api/EOrderService';
            }
        }else{
            if( (int)$this->config['ReqUrl'] == 1 ){
                $config['ReqUrl'] = 'http://api.kdniao.cc/api/Eorderservice';
            }else{
                $config['ReqUrl'] = 'http://testapi.kdniao.cc:8081/api/EOrderService';
            }
        }
        $this->config = $config;
    }
    /**
     * @function 发送请求
     * @return array|mixed
     */
    public function request()
    {
        $validate = $this->validate();
        if( !$validate['status'] ) return $validate;
        $data = $this->get_data();
        if( version_compare('5.4', PHP_VERSION, '>' ) ) {
            $data = $this->JSON( $data );
        }else{
            $data = json_encode( $data, JSON_UNESCAPED_UNICODE );
        }
        $result = json_decode( $this->submitEOrder( $data ), true );
        return $result;
    }
    /**
     * @function 设置物流配置
     * @param $key array|string
     * @param $value
     */
    public function set_logistics( $key, $value = '' )
    {
        if( is_array( $key ) ){
            $this->logistics = array_merge( $this->logistics, $key );
        }else{
            $this->logistics[$key] = $value;
        }
    }
    /**
     * @function 设置收件方信息
     * @param $key array|string
     * @param $value
     */
    public function set_receiver( $key, $value = '' )
    {
        if( is_array( $key ) ){
            $this->receiver = array_merge( $this->receiver, $key );
        }else{
            $this->receiver[$key] = $value;
        }
    }
    /**
     * @function 设置配送方信息
     * @param $key array|string
     * @param $value
     */
    public function set_sender( $key, $value = '' )
    {
        if( is_array( $key ) ){
            $this->sender = array_merge( $this->sender, $key );
        }else{
            $this->sender[$key] = $value;
        }
    }
    /**
     * @function 设置商品信息
     * @param array $array
     * @example
     *          set_goods( array( 'GoodsName' => '商品名' ) )
     */
    public function set_goods( $array )
    {
        if( count( $array ) == count( $array, 1 ) ){
            $this->goods[] = $array;
        }else{
            $this->goods = array_merge( $this->goods, $array );
        }
    }
    /**
     * @function 提交电子面单
     * @param $requestData
     * @return string
     */
    private function submitEOrder( $requestData ){
        $data = array(
            'EBusinessID' => $this->config['EBusinessID'],
            'RequestType' => '1007',
            'RequestData' => urlencode( $requestData ) ,
            'DataType' => '2',
        );
        $data['DataSign'] = $this->encrypt( $requestData );
        $result = $this->sendPost( $data );
        return $result;
    }
    /**
     * @function 整合数据
     * @return array
     */
    private function get_data()
    {
        $data = array();
        foreach( $this->logistics as $key => $val ){
            $data[$key] = $val;
        }
        $data['Receiver'] = $this->receiver;
        $data['Sender'] = $this->sender;
        $data['Commodity'] = $this->goods;
        if( $this->MemberID )  $data['MemberID'] = $this->MemberID;
        if( $this->LogisticCode )  $data['LogisticCode'] = $this->LogisticCode;
        $data['IsNotice'] = $this->IsNotice;
        if( $this->Cost )  $data['Cost'] = $this->Cost;
        if( $this->StartDate )  $data['StartDate'] = $this->StartDate;
        if( $this->EndDate )  $data['EndDate'] = $this->EndDate;
        if( $this->Weight )  $data['Weight'] = $this->Weight;
        if( $this->Quantity )  $data['Quantity'] = $this->Quantity;
        if( $this->Volume )  $data['Volume'] = $this->Volume;
        if( $this->Remark )  $data['Remark'] = $this->Remark;
        $data['IsReturnPrintTemplate'] = $this->IsReturnPrintTemplate;
        return $data;
    }
    /**
     * @function 验证电子面单必填项
     * @return array
     */
    private function validate()
    {
        //验证物流必填项
        if( !isset( $this->logistics['ShipperCode'] ) ) return array( 'status' => false, 'key' => 'ShipperCode', 'msg' => '快递公司编码未设置' );
        if( !isset( $this->logistics['OrderCode'] ) ) return array( 'status' => false, 'key' => 'OrderCode', 'msg' => '订单编号未设置' );
        if( !isset( $this->logistics['PayType'] ) ) return array( 'status' => false, 'key' => 'PayType', 'msg' => '邮费支付方式未设置' );
        if( !isset( $this->logistics['ExpType'] ) ) return array( 'status' => false, 'key' => 'ExpType', 'msg' => '快递类型未设置' );
        //验证收件方必填项
        if( !isset( $this->receiver['Name'] ) ) return array( 'status' => false, 'key' => 'Name', 'msg' => '收件人未设置' );
        if( !isset( $this->receiver['Tel'] ) && !isset( $this->receiver['Mobile'] ) ) return array( 'status' => false, 'key' => 'Tel OR Mobile', 'msg' => '收件方电话与手机，必填一个' );
        if( !isset( $this->receiver['ProvinceName'] ) ) return array( 'status' => false, 'key' => 'ProvinceName', 'msg' => '收件省未设置' );
        if( !isset( $this->receiver['CityName'] ) ) return array( 'status' => false, 'key' => 'CityName', 'msg' => '收件市未设置' );
        if( !isset( $this->receiver['Address'] ) ) return array( 'status' => false, 'key' => 'Address', 'msg' => '收件人详细地址未设置' );
        //验证配送放必填项
        if( !isset( $this->sender ['Name'] ) ) return array( 'status' => false, 'key' => 'Name', 'msg' => '发件人未设置' );
        if( !isset( $this->sender['Tel'] ) && !isset( $this->sender['Mobile'] ) ) return array( 'status' => false, 'key' => 'Tel OR Mobile', 'msg' => '发件方电话与手机，必填一个' );
        if( !isset( $this->sender['ProvinceName'] ) ) return array( 'status' => false, 'key' => 'ProvinceName', 'msg' => '发件省未设置' );
        if( !isset( $this->sender['CityName'] ) ) return array( 'status' => false, 'key' => 'CityName', 'msg' => '发件市未设置' );
        if( !isset( $this->sender['Address'] ) ) return array( 'status' => false, 'key' => 'Address', 'msg' => '发件人详细地址未设置' );
        //验证商品必填项
        if( empty( $this->goods ) ) return array( 'status' => false, 'key' => '$this->goods( array )', 'msg' => '商品未设置' );
        foreach( $this->goods as $val ){
            if( !is_array( $val ) )  return array( 'status' => false, 'key' => 'array', 'msg' => '商品设置参数必须为数组' );
            if( !isset( $val['GoodsName'] ) ) return array( 'status' => false, 'key' => 'GoodsName', 'msg' => '商品名称未设置' );
        }
        return array( 'status' => true );
    }
    /**
     * @function 电商Sign签名生成
     * @param $data
     * @return string
     */
    private function encrypt( $data ) {
        return urlencode( base64_encode( md5($data.$this->config['AppKey'] ) ) );
    }
    /**
     * @function 发送URL请求
     * @param $data
     * @return string
     */
    private function sendPost( $data ) {
        $temps = array();
        foreach ( $data as $key => $value ) {
            $temps[] = sprintf('%s=%s', $key, $value );
        }
        $post_data = implode('&', $temps);
        $url_info = parse_url( $this->config['ReqUrl'] );
        if(empty($url_info['port']))
        {
            $url_info['port']=80;
        }
        $httpheader = "POST " . $url_info['path'] . " HTTP/1.0\r\n";
        $httpheader.= "Host:" . $url_info['host'] . "\r\n";
        $httpheader.= "Content-Type:application/x-www-form-urlencoded\r\n";
        $httpheader.= "Content-Length:" . strlen($post_data) . "\r\n";
        $httpheader.= "Connection:close\r\n\r\n";
        $httpheader.= $post_data;
        $fd = fsockopen($url_info['host'], $url_info['port']);
        fwrite($fd, $httpheader);
        $gets = "";
        $headerFlag = true;
        while (!feof($fd)) {
            if (($header = @fgets($fd)) && ($header == "\r\n" || $header == "\n")) {
                break;
            }
        }
        while (!feof($fd)) {
            $gets.= fread($fd, 128);
        }
        fclose($fd);
        return $gets;
    }
    /**
     * @function JSON转换
     * @param $array
     * @param $function
     * @param bool $apply_to_keys_also
     */
    private function arrayRecursive(&$array, $function, $apply_to_keys_also = false)
    {
        static $recursive_counter = 0;
        if (++$recursive_counter > 1000) {
            die('possible deep recursion attack');
        }
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $this->arrayRecursive($array[$key], $function, $apply_to_keys_also);
            } else {
                $array[$key] = $function($value);
            }
            if ($apply_to_keys_also && is_string($key)) {
                $new_key = $function($key);
                if ($new_key != $key) {
                    $array[$new_key] = $array[$key];
                    unset($array[$key]);
                }
            }
        }
        $recursive_counter--;
    }
    private function JSON($array) {
        $this->arrayRecursive($array, 'urlencode', true);
        $json = json_encode($array);
        return urldecode($json);
    }
}