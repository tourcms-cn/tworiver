<?php

class Api
{
    /**
     * 接口列表
     */
    public function map()
    {
        return [
            'ship' => '船型',
            'type' => '船票类型',
            'passenger' => '订票人类型',
            'wharf' => '码头',
            'from' => '客源地',
            'line' => '航线',
            'price' => '价格',
            'dispatch' => '调度航班',
            'submit' => '下单',
            'query' => '订单查询',
            'refundCharges' => '退票手续费',
            'refund' => '退票',
            'msg' => '消息查询',
            'changeSign' => '修改签名',
            'changeNoticeUrl' => '修改通知地址',
            'card' => '证件类型',
            'amt' => '账户资金查询',
        ];
    }

    /**
     * api列表
     */
    public function apiList()
    {
        return array_keys($this->map());
    }

    /**
     * 获取接口名称
     */
    public function getApiName($api)
    {
        $apiMap = $this->map();

        return isset($apiMap[$api]) ? $apiMap[$api] : '未知接口';
    }

}
