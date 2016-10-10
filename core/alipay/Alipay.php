<?php namespace core\alipay;

require_once('core/alipay/lib/alipay_core.function.php');
require_once('core/alipay/lib/alipay_md5.function.php');

use core\alipay\lib\AlipaySubmit;

/*
 * 支付宝
 * Class Alipay
 * @package core\Alipay
 */

class Alipay
{
    public function pay($data)
    {
        //构造要请求的参数数组，无需改动
        $parameter = [
            "service" => "create_direct_pay_by_user",
            "partner" => config('alipay.partner'),
            "seller_email" => config('alipay.seller_email'),
            "payment_type" => config('alipay.payment_type'),
            "notify_url" => config('alipay.notify_url'),
            "return_url" => config('alipay.return_url'),
            "out_trade_no" => $data['out_trade_no'],
            "subject" => $data['subject'],
            "total_fee" => $data['total_fee'],
            "body" => $data['body'],
            "show_url" => $data['show_url'],
            "anti_phishing_key" => '',
            "exter_invoke_ip" => '',
            "_input_charset" => config('alipay.input_charset')
        ];

        //建立请求
        $alipaySubmit = new AlipaySubmit(config('alipay'));
        echo $alipaySubmit->buildRequestForm($parameter, "get", "确认");
    }
}