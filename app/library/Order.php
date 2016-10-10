<?php namespace library;

class Order
{
    /*
     * status：-1删除，0取消，1待付款，2待发货，3待收货，4待评价，5已完成
     * from: 默认前台，1后台
    */
    protected $WeiXin;

    public function __construct()
    {
        $this->WeiXin = api('library\WeiXin');
    }

    //修改状态
    public function changeStatus($from = '', $order_id = 0, $status = 0, $user_id = '')
    {
        $original_status = DB::get('order', 'status', ['id' => $order_id]);
        if ($from) {
            $original_action = Html::int2str($original_status, [-1 => '从删除', 0 => '从取消', 1 => '从待付款', 2 => '从待发货', 3 => '从待收货', 4 => '从待评价', 5 => '从已完成']);
            $after_action = Html::int2str($status, [-1 => '修改为删除', 0 => '修改为取消', 1 => '修改为待付款', 2 => '修改为待发货', 3 => '修改为待收货', 4 => '修改为待评价', 5 => '修改为已完成']);
            $action = $original_action . $after_action;
            $remark = '后台-' . DB::get('user', 'username', ['id' => $user_id]) . $action;
        } else {
            $action = Html::int2str($status, [-1 => '删除', 0 => '取消', 1 => '下单', 2 => '支付', 4 => '确认收货', 5 => '评价']);
            $remark = '前台-' . DB::get('user', 'username', ['id' => $user_id]) . $action;
        }
        $result = DB::update('order', ['status' => $status], ['id' => $order_id]);
        if ($result) {
            if ($status == -1 || $status == 0) {
                if ($original_status != -1 && $original_status != 0) {
                    $skus = DB::select('order_detail', ['sku_id', 'number'], ['order_id' => $order_id]);
                    foreach ($skus as $k => $v) {
                        DB::update('sku', ['stock[+]' => $v['number']], ['id' => $v['sku_id']]);
                    }
                }
            }
            $this->log($order_id, $user_id, $action, '成功', $remark);
            response(lang('ModifySuccess'), 1);
        } else {
            $this->log($order_id, $user_id, $action, '失败', $remark);
            response(lang('ModifyFail'), 1);
        };
    }

    //发货
    public function delivery($order_id , $value = '' , $user_id = '')
    {
        //赠送福利(优惠券、葡萄币)
        $err = '';
        $skus = DB::select('order_detail',['id','sku_id','spu_id','name','number'],['order_id' => $order_id]);
        $this->checkStock($skus);
        foreach($skus as $k=>$v){
            if($v['out']==1){
                $err .= $v['name'].',';
            }
        }
        if($err){
            response($err.lang('LowStocks'), 1);
        }
        $result = DB::update('order', ['delivery_no' => $value], ['id' => $order_id]);
        if ($result) {
            //赠送福利(优惠券、葡萄币)
            $coupon_number = $this->giveWelfare($order_id);
            //发货模板消息
            $this->deliveryMsg($order_id,$coupon_number);
            //修改状态
            $this->changeStatus(1 , $order_id , 3 ,$user_id);
        } else {
            response(lang('ModifyFail'), 1);
        }
    }

    //校验库存
    public function checkStock(&$item)
    {
        $out = false;
        foreach ($item as $k => $v) {
            $stock = DB::get('sku', 'stock', ['AND' => ['show' => 1, 'id' => $v['sku_id'], 'spu_id' => $v['spu_id']]]);
            if (($v['number']) > $stock) {
                $item[$k]['out'] = true;
                $out = true;
            }
        }
        return $out;
    }

    //赠送福利(优惠券、葡萄币)
    public function giveWelfare($order_id){
        $order = DB::get('order', ['user_id' ,'get_coupon_category_id' , 'get_score' ], ['id' => $order_id]);
        $order_detail = DB::select('order_detail', ['id' , 'sku_id' , 'score' , 'number' , 'get_coupon_category_id'], ['order_id' => $order_id]);
        //订单促销-满额送券
        if($order['get_coupon_category_id']){
            $get_coupon_id[] = $this->getCoupon($order['get_coupon_category_id'],$order['user_id'],1,$order_id);
        }
        //订单促销-满额送葡萄币
        if($order['get_score']!=0){
            //发送获得葡萄币模板消息
            $this->giveScoreMsg($order['get_score'],$order['user_id'], 1 ,$order_id);
        }
        //赠送产品葡萄币
        $sku_score = 0;
        foreach($order_detail as $k=>$v){
            $sku_score += $v['score']*$v['number'];
            if($v['get_coupon_category_id']){
                $get_coupon_id[] =$this->getCoupon($order['get_coupon_category_id'],$order['user_id'],'',$v['id']);
            }
        }
        if($sku_score){
            //发送获得葡萄币模板消息
            $this->giveScoreMsg($sku_score,$order['user_id']);
        }
        return count($get_coupon_id);
    }

    //随机抽取优惠券并修改优惠券状态
    public function getCoupon($get_coupon_category_id , $user_id , $type='' , $id )
    {
        $get_coupon_id = DB::get('coupon', 'id' , ['AND' =>['coupon_category_id' => $get_coupon_category_id , 'status' => 0 ], 'ORDER' => 'id DESC']);
        DB::update('coupon', ['user_id' => $user_id ,'get_at' => time() ,'status' => 1], ['id' => $get_coupon_id]);
        if($type){//修改订单get_coupon_id
            DB::update('order', ['get_coupon_id' => $get_coupon_id], ['id' => $id]);
        }else{//修改订单order_detail中产品get_coupon_id
            DB::update('order_detail', ['get_coupon_id' => $get_coupon_id], ['id' => $id]);
        }
        return $get_coupon_id;
    }

    //支付成功模板消息
    public function payMsg($order_id)
    {
        $order = DB::get('order', ['user_id' , 'trade_no' , 'real_price' ,'payed_at' ,'address_province' ,'address_city' ,'address_region' ,'address_address'], ['id' => $order_id]);
        $tel = DB::get('user', 'tel', ['id' => $order['user_id']]);
        $sku_id = DB::select('order_detail', 'sku_id', ['order_id' => $order_id]);
        $sku_name = DB::get('sku', 'name', ['id' => $sku_id[0]]);
        $content = $sku_name;
        if(count($sku_id)>=2){
            $content .= "等多件产品";
        }
        $open_id = DB::get('user_oauth', 'open_id', ['user_id' => $order['user_id']]);
        $this->WeiXin->postTplMsg(
            $open_id,
            'ss-F05xbd7jVXHGX0r9-GjGJ2cgzFTxbs2zv1-j_NvQ',
            'http://www.putaosexy.com/user/order/show/'.$order_id,
            [
                'first'=>array('value'=>"您好，您的订单在" . date('Y-m-d H:i', $order['payed_at']) . "已支付成功！"),
                'keyword1'=>array('value'=>$content),
                'keyword2'=>array('value'=>$order['trade_no']),
                'keyword3'=>array('value'=>'￥'.$order['real_price']),
                'remark'=>array('value'=>'感谢您的光临~如有问题可拨打：400-7723326'),
            ]
        );
        //发送给管理员,永文/铖中/wj
        $open_ids = ['oX7PUvlWAfEDIVoozizrZFKvJZws','oX7PUvtCIvftBnu0Z-ClNzdYEny8','oX7PUvllIZn6B7n-MGiXxYJw5ELw'];
        foreach($open_ids as $v){
            $this->WeiXin->postTplMsg(
                $v,
                'kjGjyxSyqtUOiBfYvk7WDz92z7JWQxFA_Es9fOk_03c',
                '',
                [
                    'first' => array('value' => '您好，'.$tel.'的订单已支付成功！'),
                    'keyword1' => array('value' => $order['trade_no'] . '（' . $content . '）'),
                    'keyword2' => array('value' => date('Y-m-d H:i', $order['payed_at'])),
                    'keyword3' => array('value' => '￥' . $order['real_price']),
                    'remark' => array('value' => '地址：'.$order['address_province'].'-'.$order['address_city'].'-'.$order['address_region'].'-'.$order['address_address'].'。'),
                ]
            );
        }
    }

    //发货模板消息
    public function deliveryMsg($order_id,$coupon_number=''){
        $order = DB::get('order', ['user_id' , 'trade_no' , 'delivery_express' , 'delivery_no' , 'address_address'], ['id' => $order_id]);
        $sku_id = DB::select('order_detail', 'sku_id', ['order_id' => $order_id]);
        $sku_name = DB::get('sku', 'name', ['id' => $sku_id[0]]);
        $open_id = DB::get('user_oauth', 'open_id', ['user_id' => $order['user_id']]);
        $msg = "亲爱的，您的订单(".$order['trade_no'].")葡萄超市已发货，";
        if($coupon_number){
            $msg .= "并获得".$coupon_number."张优惠券，";
        }
        $msg .= "请耐心等候!";
        $content = $sku_name;
        if(count($sku_id)>=2){
            $content .= "等多件产品";
        }
        $this->WeiXin->postTplMsg(
            $open_id,
            'wsUUyajocTLKhCPe08EFrufqmF6fB1dbJ1UZqLflBG8',
            'http://www.putaosexy.com/user/order/show/'.$order_id,
            [
                'first'=>array('value'=>$msg),
                'keyword1'=>array('value'=>$content),
                'keyword2'=>array('value'=>$order['delivery_express']),
                'keyword3'=>array('value'=>$order['delivery_no']),
                'keyword4'=>array('value'=>$order['address_address']),
                'remark'=>array('value'=>'领券后购买，更能省钱哦，避孕套0利润，全网最低价，别忘了分享给好友哦~'),
            ]
        );
    }

    //赠送葡萄币模板消息
    public function giveScoreMsg($score , $user_id , $type = '' , $order_id = ''){
        if($type){//订单促销赠送葡萄币
            $trade_no = DB::get('order', 'trade_no', ['id' => $order_id]);
            $msg = "恭喜您！您在葡萄网购买的订单(".$trade_no.")获得了".$score."葡萄币！";
            $remark = "订单促销赠送葡萄币";
        }else{//产品赠送葡萄币
            $msg = "恭喜您！您在葡萄网购买的产品共获得了".$score."葡萄币！";
            $remark = "产品赠送葡萄币";
        }
        //修改用户葡萄币
        $this->changeScore($user_id,$score);
        //添加葡萄币记录
        $this->scoreLog($user_id,$score,$remark);
        $tel = DB::get('user', 'tel', ['id' => $user_id]);
        $open_id = DB::get('user_oauth', 'open_id', ['user_id' => $user_id]);
        $this->WeiXin->postTplMsg(
            $open_id,
            'wCqloMVAiLzrKfK196uwbNH2CAzJUVan2e-MU6tYTHg',
            'http://www.putaosexy.com/user/score',
            [
                'first'=>array('value'=>$msg),
                'keyword1'=>array('value'=>$tel),
                'keyword2'=>array('value'=>$tel),
                'keyword3'=>array('value'=>$score),
                'keyword4'=>array('value'=>date('Y-m-d H:i',NOW)),
                'remark'=>array('value'=>'积分=葡萄币。葡萄币可以10:1兑换优惠券，100:1兑换现金。我们即将推出葡萄币商城，使用葡萄币可兑换礼品。您可通过签到、购买产品获得葡萄币哦~'),
            ]
        );
    }

    //优惠卡即将过期提醒
    public function upToDateCoupon($user_id , $coupon_id)
    {
        $tel = DB::get('user', 'tel', ['id' => $user_id]);
        $coupon_category = DB::get('coupon', ["[>]coupon_category" => ["coupon_category_id" => "id"],] , ['coupon_category.name' , 'coupon_category.e_time'], ['coupon.id' => $coupon_id]);
        $open_id = DB::get('user_oauth', 'open_id', ['user_id' => $user_id]);
        $this->WeiXin->postTplMsg(
            $open_id,
            'U-iFMxj5xNg5y2YoPe2CrajRlqYpFcmWwoDGd4MWt98',
            'http://www.putaosexy.com/user/coupon',
            [
                'first'=>array('value'=>"尊敬的会员".$tel."，您的".$coupon_category['name']."元优惠券马上就要过期啦~"),
                'keyword1'=>array('value'=>$coupon_id),
                'keyword2'=>array('value'=>date('Y-m-d H:i',$coupon_category['e_time'])),
                'remark'=>array('value'=>'优惠券可以在订单结算时直接抵扣，请尽快使用哦~如有问题，请致电：400-7723326'),
            ]
        );
    }

    //新增用户模板消息
    public function newUser($user_id)
    {
        $user = DB::get('user', ['tel','created_at'], ['id' => $user_id]);
        $this->WeiXin->postTplMsg(
            'oX7PUvlWAfEDIVoozizrZFKvJZws',
            'BdnYGFLdBah3q53rlArQ9_J7ovi7JaXXUQXAuASGxW4',
            '',
            array(
                'first'=>array('value'=>"太棒啦，又有新用户注册啦！继续加油！"),
                'keyword1'=>array('value'=>$user['tel']),
                'keyword2'=>array('value'=>date('Y-m-d H:i',$user['created_at'])),
                'remark'=>array('value'=>'2016年你需要快速成长！公司需要快速发展！黄金时间就这两年，你看着办，还不赶快努力！！！'),
            )
        );
    }

    //修改用户葡萄币
    public function changeScore($user_id , $score)
    {
        DB::update('user', ['score[+]' => $score], ['id' => $user_id]);
    }

    //记录用户葡萄币操作日志
    public function scoreLog($user_id , $score , $remark)
    {
        $data['user_id'] = $user_id;
        $data['score'] = $score;
        $data['remark'] = $remark;
        $data['created_at'] = NOW;
        DB::insert('user_score', $data);
    }

    //记录订单操作日志
    public function log($order_id, $user_id, $action, $result, $remark)
    {
        $data['order_id'] = $order_id;
        $data['user_id'] = $user_id;
        $data['action'] = $action;
        $data['result'] = $result;
        $data['remark'] = $remark;
        $data['created_at'] = NOW;
        DB::insert('order_log', $data);
    }

    //添加订单催付消息队列
    public function addSms($order_id, $user_id)
    {
        $user = DB::get('user', ['username','tel'], ['id' => $user_id]);
        $data['order_id'] = $order_id;
        $data['user_id'] = $user_id;
        $data['name'] = $user['username'];
        $data['tel'] = $user['tel'];
        $data['status'] = 0;
        $data['created_at'] = NOW;
        DB::insert('order_sms', $data);
    }

    //删除订单催付消息队列
    public function delSms($order_id, $user_id)
    {
        DB::delete('order_sms', ['AND'=>['order_id' => $order_id , 'user_id' => $user_id]]);
    }
}