<?php

namespace tourcms\tworiver;

use GuzzleHttp\Client;

class TwoRiverTicket
{
    protected $api_url = '';
    protected $mch_id = '';
    protected $key = '';

    function __construct($api_url, $mch_id, $key)
    {
        $this->api_url = $api_url;
        $this->mch_id = $mch_id;
        $this->key = $key;
    }

    /**
     * 1.5 船型接口
     *
     * @return void
     */
    public function ship()
    {
        return TwoRiverTicket::requestData('ticket.cmm.ship.category');
    }

    /**
     * 1.6 船票类型接口
     * @return void
     */
    public function type()
    {
        return TwoRiverTicket::requestData('ticket.cmm.tick.type');
    }

    /**
     * 1.7 订票人类型接口
     *
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function passenger()
    {
        return TwoRiverTicket::requestData('ticket.cmm.passenger.type');
    }

    /**
     * 1.8 码头接口
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function wharf()
    {
        return TwoRiverTicket::requestData('ticket.cmm.wharf');
    }

    /**
     * 1.9 客源地接口
     *
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function from()
    {
        return TwoRiverTicket::requestData('ticket.cmm.from');
    }

    /**
     * 1.10 航线接口
     *
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function line($period = 0)
    {
        $allPeriod = TwoRiverApi::linePeriods();
        $period = $allPeriod[$period] ?? '全天';

        return TwoRiverTicket::requestData('ticket.cmm.line', ['period' => $period]);
    }

    /**
     * 1.11 价格接口
     *
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function price()
    {
        return TwoRiverTicket::requestData('ticket.cmm.price');
    }

    /**
     * 1.12 调度航班接口
     *
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function dispatch($request_data = [])
    {
        return TwoRiverTicket::requestData('ticket.cmm.dispatch.info', $request_data);
    }

    /**
     * 1.13 下单接口
     *
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function submit($request_data)
    {
        return TwoRiverTicket::requestData('ticket.cmm.order.sub', $request_data);
    }

    /**
     * 1.14 订单查询接口
     *
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function query($request_data = [])
    {
        return TwoRiverTicket::requestData('ticket.cmm.order.info', $request_data);
    }

    /**
     * 1.15 退票手续费接口
     *
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function refundCharges($request_data)
    {
        return TwoRiverTicket::requestData('ticket.cmm.refund.charges', $request_data);
    }

    /**
     * 1.16 退票接口
     *
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function refund($request_data)
    {
        return TwoRiverTicket::requestData('ticket.cmm.order.refund', $request_data);
    }

    /**
     * 1.18 消息查询接口
     *
     * @return false|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function msg($post_date)
    {
        return TwoRiverTicket::requestData('ticket.cmm.msg.info', ['post_date' => $post_date]);
    }

    /**
     * 1.19 修改签名
     *
     * @return void
     */
    public function changeSign($ncryption_key)
    {
        return TwoRiverTicket::requestData('ticket.cmm.cust.key', ['ncryption_key' => $ncryption_key]);
    }

    /**
     * 1.20 修改通知地址
     * @return void
     */
    public function changeNoticeUrl($notify_url)
    {
        return TwoRiverTicket::requestData('ticket.cmm.cust.notice', ['notify_url' => $notify_url]);
    }

    /**
     * 1.21 证件类型
     *
     * @return false|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function card()
    {
        return TwoRiverTicket::requestData('ticket.cmm.card.type');
    }

    /**
     * 1.22 账户资金查询
     *
     * @return false|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function amt()
    {
        return TwoRiverTicket::requestData('ticket.cmm.cust.amt');
    }

    /**
     * 获取接口数据.
     *
     * @param $service
     * @param $data
     * @return false|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function requestData($service, $data = array())
    {
        $req_data = [
            "mch_id" => $this->mch_id,
            "nonce_str" => str_replace('-', '', uuid()),
            "service" => $service,
            "time" => date('YmdHis000'),
        ];
        if ($data) {
            $req_data['data'] = json_encode($data);
        } else {
            $req_data['data'] = json_encode(['date' => date('Ymd')]);
        }

        $signStr = $this->signData($req_data, $this->key);

        $req_data['sign'] = $signStr;

        $client = new Client([
            'timeout' => 5.0,
            'headers' => [
                'Content-Type' => 'application/json:charset=utf-8',
            ],
        ]);
        $response = $client->request(
            'POST',
            $this->api_url,
            [
                \GuzzleHttp\RequestOptions::JSON => $req_data,
            ]
        );

        if ($response->getStatusCode() == '200') {
            $data = json_decode($response->getBody()->getContents());
            if ($data->code != '0000') {
                return $data->description;
            }

            if ($data->data) {
                return json_decode($data->data, true);
            } else {
                return $data->data;
            }

        }

        return false;
    }

    /**
     * 签名
     * @param $data
     * @param $key
     * @return array|string
     */
    public function signData($data, $key)
    {
        ksort($data);

        $stringA = '';
        foreach ($data as $k => $v) {
            if ($k == 'sign' || empty($v)) {
                continue;
            }

            $stringA .= "{$k}={$v}&";
        }
        $stringSignTemp = $stringA . "key=" . $key;
        $md5 = md5($stringSignTemp);

        return strtoupper($md5);
    }
}
