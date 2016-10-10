<?php namespace library;

class Cart
{
    private $field = ['id', 'spu_id', 'sku_id', 'sn', 'name', 'image', 'price', 'weight', 'score', 'number'];

    private $user_id = '';

    function __construct()
    {
        if (session('user_id')) {
            $this->user_id = session('user_id');
        } else {
            $this->user_id = cookie('user_id');
        }
    }

    /*
    添加到购物车
    */
    public function inc($spu_id, $sku_id)
    {
        //购物车
        $cart = DB::get('cart', ['number', 'id'], ['AND' => ['user_id' => $this->user_id, 'spu_id' => $spu_id, 'sku_id' => $sku_id]]);
        //产品
        $sku = DB::get('sku', ['sn', 'name', 'image', 'price', 'score', 'weight', 'stock', 'promotion_id'], ['AND' => ['show' => 1, 'id' => $sku_id, 'spu_id' => $spu_id]]);
        //秒杀促销
        $promotion = DB::get('promotion', ['rule', 's_time', 'e_time', 'limit'], ['AND' => ['status' => 1, 's_time[<=]' => NOW, 'e_time[>=]' => NOW, 'type' => 3, 'id' => $sku['promotion_id']]]);
        if ($promotion) {
            $promotion['rule'] = json_decode($promotion['rule'], true);
            //检查可购买数量
            $cart_number = DB::sum('cart', 'number', ['AND' => ['sku_id' => array_keys($promotion['rule']), 'user_id' => $this->user_id]]);
            //检查库存
            $order_id_array = DB::select('order', 'id', ['AND' => ['created_at[<=]' => $promotion['e_time'], 'created_at[>=]' => $promotion['s_time'], 'status[>]' => 1]]);
            $order_stock = DB::sum('order_detail', 'number', ['AND' => ['order_id' => $order_id_array, 'sku_id' => $sku_id]]);
            //检查购物车
            if ($cart['number'] + 1 > $promotion['rule'][$sku_id]['limit'] || $cart_number + 1 > $promotion['limit'] || $order_stock >= $promotion['rule'][$sku_id]['stock']) {
                return false;
            }
            $sku['name'] = $promotion['rule'][$sku_id]['name'];
            $sku['price'] = $promotion['rule'][$sku_id]['value'];
        }
        $this->count($this->user_id, $spu_id, $sku_id);//统计更新
        if ($cart) {
            if (($cart['number'] + 1) < $sku['stock'] && DB::update('cart', ['number[+]' => 1], ['id' => $cart['id']])) {
                return $cart['id'];
            }
        } else {
            $data = [];
            $data['user_id'] = $this->user_id;
            $data['spu_id'] = $spu_id;
            $data['sku_id'] = $sku_id;
            $data['sn'] = $sku['sn'];
            $data['name'] = $sku['name'];
            $data['image'] = $sku['image'];
            $data['price'] = $sku['price'];
            $data['weight'] = $sku['weight'];
            $data['score'] = $sku['score'];
            $data['number'] = 1;
            $data['status'] = 1;
            $data['created_at'] = NOW;
            return DB::insert('cart', $data);
        }
        return false;
    }

    /*
   商品数量-1
   */

    public function count($user_id, $spu_id, $sku_id, $number = 1)
    {
        $id = DB::get('cart_count', 'id', ['AND' => ['user_id' => $user_id, 'spu_id' => $spu_id, 'sku_id' => $sku_id]]);
        if (!$id) {
            DB::insert('cart_count', ['user_id' => $user_id, 'spu_id' => $spu_id, 'sku_id' => $sku_id, 'number' => $number, 'created_at' => NOW]);
        } else {
            DB::update('cart_count', ['number[+]' => $number], ['id' => $id]);
        }
    }

    public function dec($spu_id, $sku_id)
    {
        $cart = DB::get('cart', ['id', 'number'], ['AND' => ['user_id' => $this->user_id, 'spu_id' => $spu_id, 'sku_id' => $sku_id]]);
        if ($cart['number'] > 1) {
            if (!DB::update('cart', ['number[-]' => 1], ['id' => $cart['id']])) {
                return false;
            }
        } else {
            if (!$this->del($cart['id'])) {
                return false;
            }
        }
        return $cart['id'];
    }

    /*
    查询购物车中商品的个数
    */

    public function del($id)
    {
        return DB::delete('cart', ['id' => $id]);
    }

    /*
    查询购物车中商品的个数
    */

    public function number($id)
    {
        $item = $this->get($id);
        return $item['number'];
    }


    /*
    查询购物车中商品
    */

    public function get($id)
    {
        return DB::get('cart', $this->field, ['id' => $id]);
    }

    /*
    查询购物车中商品
    */

    public function numberAll($all = false)
    {
        if ($all) {
            return DB::sum('cart', 'number', ['user_id' => $this->user_id]);
        } else {
            return DB::sum('cart', 'number', ['AND' => ['status' => 1, 'user_id' => $this->user_id]]);
        }
    }

    /*
    购物车中商品的总金额
    */

    public function price($id)
    {
        $item = $this->get($id);
        return sprintf("%01.2f", $item['number'] * $item['price']);
    }

    /*
    购物车中商品的总金额
    */

    public function priceAll($all = false)
    {
        $price = 0.00;
        foreach ($this->getAll($all) as $v) {
            $price += $v['number'] * $v['price'];
        }
        return sprintf("%01.2f", $price);
    }

    /*
    清空购物车
    */

    public function getAll($all = false)
    {
        if ($all) {
            return DB::select('cart', $this->field, ['user_id' => $this->user_id]);
        } else {
            return DB::select('cart', $this->field, ['AND' => ['status' => 1, 'user_id' => $this->user_id]]);
        }
    }

    public function clear($all = false)
    {
        if ($all) {
            return DB::delete('cart', ['user_id' => $this->user_id]);
        } else {
            return DB::delete('cart', ['AND' => ['status' => 1, 'user_id' => $this->user_id]]);
        }
    }

    public function status($id, $status = 0)
    {
        return DB::update('cart', ['status' => $status], ['id' => $id]);
    }
}