<?php

namespace tourcms\tworiver;

use Exception;

class TwoRiverTicket
{
    protected $api_url = '';
    protected $mch_id = '';
    protected $key = '';
    protected $logPath = '';

    function __construct($api_url, $mch_id, $key, $logPath = '')
    {
        $this->api_url = $api_url;
        $this->mch_id = $mch_id;
        $this->key = $key;
        $this->logPath = $logPath;
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
                $this->error_log(curl_error($ch));
                throw new Exception('cURL request failed: ' . curl_error($ch));
            }

            // 关闭 cURL 会话
            curl_close($ch);

            // 解码响应数据
            $resp_data = json_decode($response, true);

            if ($resp_data === null && json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('JSON decode failed for response data.');
            }

            $return_data = [
                'status' => false,
                'msg' => '',
                'data' => []
            ];
            if (isset($resp_data['code']) && $resp_data['code'] == '0000') {
                $return_data['status'] = true;
                $return_data['data'] = is_null($resp_data['data']) ? [] : json_decode($resp_data['data'], true);
            } else {
                $return_data['msg'] = $resp_data['desicription'] ?? '未知错误';// 业务异常判断
                $this->error_log($req_data);
                $this->error_log($response);
            }

            return $return_data;

        } catch (Exception $e) {
            // 记录错误日志
            $this->error_log('返回错误：' . $e->getMessage());
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

    /**
     * 记录错误日志
     *
     * 当需要记录程序中的错误信息时使用此方法它将错误信息格式化后写入到日志文件中
     * 如果未指定日志文件路径，则使用默认路径
     *
     * @param mixed $msg 日志消息内容
     * @param int $msg_type 日志消息类型，默认为3，表示将消息记录到系统日志以及指定的日志文件
     */
    private function error_log($msg, $msg_type = 3)
    {
        // 确保日志路径已初始化
        if (empty($this->logPath)) {
            $this->logPath = __DIR__ . '/logs/tworiver_' . date('Ymd') . '.log';
        }

        error($this->logPath);

        try {
            // 确保日志文件存在且可写
            if (!file_exists($this->logPath)) {
                file_put_contents($this->logPath, "", FILE_APPEND);
            }

            // 增加日期信息，并在消息后换行，以便于日志的阅读和管理
            $formattedMsg = "\n" . date('Y-m-d H:i:s') . "\n";

            // 使用纯文本格式记录错误信息
            if (is_array($msg) || is_object($msg)) {
                $formattedMsg .= json_encode($msg, JSON_UNESCAPED_UNICODE) . PHP_EOL;
            } else {
                $formattedMsg .= $msg . PHP_EOL;
            }

            // 使用PHP的error_log函数将格式化后的消息记录到指定的日志文件中
            error_log($formattedMsg, $msg_type, $this->logPath);

        } catch (Exception $e) {
            // 记录异常信息，防止日志记录失败影响程序运行
            fwrite(STDERR, "Failed to write log: " . $e->getMessage() . PHP_EOL);
        }
    }

}
