<?php

namespace tourcms\tworiver;

class TwoRiverApi
{
    /*
     * 接口列表
     */
    public static function map()
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
    public static function all()
    {
        return array_keys(self::map());
    }

    /**
     * api列表
     */
    public static function listOfApi()
    {
        $all = self::map();
        unset($all['submit']);
        unset($all['query']);
        unset($all['refundCharges']);
        unset($all['refund']);
        unset($all['msg']);
        unset($all['changeSign']);
        unset($all['changeNoticeUrl']);
        unset($all['amt']);

        return $all;
    }

    /**
     * 获取接口名称
     */
    public static function getApiName($api)
    {
        $apiMap = self::map();

        return isset($apiMap[$api]) ? $apiMap[$api] : '未知接口';
    }

    /**
     * 航线时段.
     * @return string[]
     */
    public static function linePeriods(){
        return [
            0 => '全天',
            1 => '日游',
            2 => '夜游',
        ];
    }

}
