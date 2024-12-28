<?php

namespace tourcms\tworiver;

use Exception;

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
     */
    public function passenger()
    {
        return TwoRiverTicket::requestData('ticket.cmm.passenger.type');
    }

    /**
     * 1.8 码头接口
     * @return void
     *
     */
    public function wharf()
    {
        return TwoRiverTicket::requestData('ticket.cmm.wharf');
    }

    /**
     * 1.9 客源地接口
     *
     * @return void
     */
    public function from()
    {
        return TwoRiverTicket::requestData('ticket.cmm.from');
    }

    /**
     * 1.10 航线接口
     *
     * @return void
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
     */
    public function price()
    {
        return TwoRiverTicket::requestData('ticket.cmm.price');
    }

    /**
     * 1.12 调度航班接口
     *
     * @return void
     */
    public function dispatch($request_data = [])
    {
        return TwoRiverTicket::requestData('ticket.cmm.dispatch.info', $request_data);
    }

    /**
     * 1.13 下单接口
     *
     * @return void
     */
    public function submit($request_data)
    {
        return TwoRiverTicket::requestData('ticket.cmm.order.sub', $request_data);
    }

    /**
     * 1.14 订单查询接口
     *
     * @return void
     */
    public function query($request_data = [])
    {
        return TwoRiverTicket::requestData('ticket.cmm.order.info', $request_data);
    }

    /**
     * 1.15 退票手续费接口
     *
     * @return void
     */
    public function refundCharges($request_data)
    {
        return TwoRiverTicket::requestData('ticket.cmm.refund.charges', $request_data);
    }

    /**
     * 1.16 退票接口
     *
     * @return void
     */
    public function refund($request_data)
    {
        return TwoRiverTicket::requestData('ticket.cmm.order.refund', $request_data);
    }

    /**
     * 1.18 消息查询接口
     *
     * @return false|mixed
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
     */
    public function card()
    {
        return TwoRiverTicket::requestData('ticket.cmm.card.type');
    }

    /**
     * 1.22 账户资金查询
     *
     * @return false|mixed
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
     */
    /**
     * 获取接口数据.
     *
     * @param $service
     * @param $data
     * @return false|mixed
     */
    public function requestData($service, $data = array())
    {
        try {
            $nonce_str = bin2hex(random_bytes(16));
            $time = self::getMillisecondTimestamp();
            $req_data = [
                "mch_id" => $this->mch_id,
                "nonce_str" => $nonce_str,
                "service" => $service,
                "time" => $time,
            ];

            // 处理 data 参数
            if (!empty($data)) {
                $req_data['data'] = json_encode($data);
            } else {
                $req_data['data'] = json_encode(['date' => date('Ymd')]);
            }

            // 签名
            $signStr = $this->signData($req_data, $this->key);
            $req_data['sign'] = $signStr;

            // JSON 编码请求数据
            $json_data = json_encode($req_data);
            if ($json_data === false) {
                throw new Exception('JSON encode failed for request data.');
            }

            // 初始化 cURL 会话
            $ch = curl_init($this->api_url);

            // 设置 cURL 选项
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($json_data),
            ]);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);

            // 执行 cURL 请求
            $response = curl_exec($ch);

            // 检查是否有错误发生
            if (curl_errno($ch)) {
                throw new Exception('cURL request failed: ' . curl_error($ch));
            }

            // 关闭 cURL 会话
            curl_close($ch);

            // 解码响应数据
            $resp_data = json_decode($response, true);
            if ($resp_data === null && json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('JSON decode failed for response data.');
            }

            // 业务异常判断
            if (isset($resp_data['code'])) {
                switch ($resp_data['code']) {
                    case '0000':
                        // 成功
                        break;
                    case '1000':
                        throw new Exception('校验错误');
//                    case '2000':
//                        throw new Exception('系统异常');
                    case '1001':
                        throw new Exception('参数错误');
                    default:
                        throw new Exception('未知错误代码: ' . $resp_data['code']);
                }
            } else {
                throw new Exception('响应数据中缺少 code 字段');
            }

            // 解码响应数据中的 data 字段
            if ($resp_data['data'] != null) {
                return json_decode($resp_data['data'], true);
            }

            return [];
        } catch (Exception $e) {
            // 记录错误日志
//            error_log('Request failed: ' . $e->getMessage());
            // 可以根据需要抛出异常或返回错误信息
            throw $e;
        }
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

    /**
     * 生成带毫秒的时间戳
     * @return string
     */
    private function getMillisecondTimestamp()
    {
        list($usec, $sec) = explode(" ", microtime());
        $usec = substr($usec, 2, 3); // 取毫秒部分
        return date('YmdHis', $sec) . $usec;
    }
}
