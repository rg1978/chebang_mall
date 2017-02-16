<?php
/**
 * 订阅物流单号查询事件,解决物流查询量过大问题
 *
 * 事件任务说明：订阅物流单号到快递鸟
 * 异步事件任务
 */
class syslogistics_events_listeners_kdnsubscribe {

    public $ReqURL = 'http://api.kdniao.cc/Ebusiness/EbusinessOrderHandle.aspx';


    public function __construct()
    {
        //快递鸟配置数据
        $hqepayParams = app::get('syslogistics')->getConf('syslogistics.order.hqepay');
        //电商ID
        $this->EBusinessID =  !empty($hqepayParams['id']) ? $hqepayParams['id'] : '1226825';
        //AppKey,电商加密私钥，快递鸟提供，注意保管，不要泄漏
        $this->AppKey = !empty($hqepayParams['appkey']) ? $hqepayParams['appkey'] : '9326bc57-8964-4f59-88fe-b5ced1dfd66a';
    }

    /**
     * 订阅物流单号查询事件
     * Json方式  物流信息订阅
     *
     * @param array $tradeData 订单信息，这里无用
     * @param array $shipData 物流单号等数据
     */
    public function handle($tradeData, $shipData)
    {
        // 每次只推送一单
        $params = array(
            'Code' => $shipData['corp_code'],
            'Item' => array(
                array(
                    'No' => $shipData['logi_no'],
                    'Bk' => 'shopex',
                ),
            ),
        );
        $requestData = json_encode($params);
        $datas = array(
            'EBusinessID' => $this->EBusinessID,
            'RequestType' => '1005',
            'RequestData' => urlencode($requestData) ,
            'DataType' => '2',
        );
        $datas['DataSign'] = $this->encrypt($requestData, $this->AppKey);
        $result = client::post($this->ReqURL, ['query' => $datas])->json();

        return true;
    }

    /**
     * 电商Sign签名生成
     * @param data 内容   
     * @param appkey Appkey
     * @return DataSign签名
     */
    function encrypt($data, $appkey) {
        return urlencode(base64_encode(md5($data.$appkey)));
    }

}
