<?php
/**
 *
 */
use GuzzleHttp\Exception\ClientException;

class syslogistics_data_tracker {

    public $hqepayApiUrl = 'http://api.kdniao.cc/Ebusiness/EbusinessOrderHandle.aspx';

    /**
     * @brief 从快递鸟提供的的物流跟踪API，获取物流轨迹
     *
     * @param string $LogisticCode 物流单号
     * @param string $ShipperCode  快递公司编号
     *
     * @return array
     */
    public function pullFromHqepay($LogisticCode, $ShipperCode)
    {
        //请求类型 1002表示查询订单轨迹
        $RequestType = 1002;

        //快递鸟配置数据
        $hqepayParams = app::get('syslogistics')->getConf('syslogistics.order.hqepay');

        //电商ID
        $EBusinessID =  !empty($hqepayParams['id']) ? $hqepayParams['id'] : '1226825';

        //AppKey
        $appkey = !empty($hqepayParams['appkey']) ? $hqepayParams['appkey'] : '9326bc57-8964-4f59-88fe-b5ced1dfd66a';

        //参数内容
        //$content = "<Content><OrderCode></OrderCode><ShipperCode>{$ShipperCode}</ShipperCode><LogisticCode>{$LogisticCode}</LogisticCode></Content>";

        $content = "{'OrderCode':'', 'ShipperCode':'{$ShipperCode}', 'LogisticCode':{$LogisticCode}}";

        //签名
        $DataSign = $this->__hqepayEncrypt($content,$appkey);

        # 返回数据类型: 1-xml,2-json
        $DataType = 2;

        $post = array(
            'RequestType' => $RequestType,
            'EBusinessID' => $EBusinessID,
            'RequestData' => urlencode($content),
            'DataSign' => urlencode($DataSign),
            'DataType' => $DataType,
        );

        try
        {
            $responseData = client::post($this->hqepayApiUrl, ['body' => $post])->json();
        }
        catch (Exception $e )
        {
            $responseData = [];
        }

        if( $responseData['Success'] === true )
        {
            $traces = array();
            foreach( $responseData['Traces'] as $key=>$value)
            {
                $traces[$key]['AcceptTime'] = $value['AcceptTime'];
                $traces[$key]['AcceptStation'] = strip_tags($value['AcceptStation']);
            }
            return $traces;
        }
        elseif (isset($responseData['Reason']))
        {
            throw new \LogicException($responseData['Reason']);
        }
        else
        {
            throw new \LogicException(app::get('syslogistics')->_('查询失败，请到快递公司官网查询'));
        }
    }

    private function __hqepayEncrypt($content, $appkey)
    {
        return base64_encode(md5($content.$appkey));
    }
}

