<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
/**
 * 将主要数据序列化保存在cookie中，取数据的时候api请求item获取数据信息。
 * 现在保存字段如下：sku_id，quantity，checked
 * 因为有购物车id，这里打算用sku_id于页面上的cart_id对应。
 *
 */
class topm_cart_offline
{

    private $__key = 'offlineCart';


    private function __store($params)
    {
        kernel::single('base_session')->start();
        $_SESSION[$this->__key] = $params;
        kernel::single('base_session')->close();
      //$value = serialize($params);
      //setcookie($this->__key, $value);
      //$_COOKIE[$this->__key] = $value;
        return true;
    }

    private function __fetch()
    {
        kernel::single('base_session')->start();
        $params = $_SESSION[$this->__key];
      //$value = $_COOKIE[$this->__key];
      //$params = unserialize($value);
        return $params;
    }

    public function addCart($sku_id, $quantity)
    {
        $sku = ['sku_id' => $sku_id, 'quantity' => $quantity, 'is_checked' => 0];


        $cart = $this->__fetch();

        if($cart[$sku_id]['quantity'] > 0)
        {
            $cart[$sku_id]['quantity'] += $quantity;
        }
        else
        {
            $cart[$sku_id] = $sku;
        }

        $this->__store($cart);


        return true;
    }

    public function updateCart($sku_id, $quantity, $checked = 0)
    {
        $sku = ['sku_id' => $sku_id, 'quantity' => $quantity, 'is_checked' => $checked];
        $cart = $this->__fetch();
        $cart[$sku_id] = $sku;
        $this->__store($cart);

        return true;
    }

    public function removeCart($sku_id)
    {
        $sku_ids = split(',',$sku_id);
        $cart = $this->__fetch();
        foreach($sku_ids as $skuId)
            unset($cart[$skuId]);
        $this->__store($cart);
        return true;
    }

    public function cleanCart()
    {
        $this->__store([]);
        return true;
    }

    public function getCart()
    {
        $cart = $this->__fetch();
        return $cart;
    }

    public function getCartInfo($countAll = false)
    {
        $cart = $this->getCart();
        if(count($cart) == 0)
        {
            return [];
        }
        $cartInfo = kernel::single('topm_cart_skuInfo')->genCartInfo($cart, $countAll);
        return $cartInfo;
    }

    //kernel::single('topm_cart_offline')->getCartCount(kernel::single('topm_cart_offline')->getCart());
    public function getCartCount( $cart = null )
    {
        //如果传入了购物车数据，将不会再取一次数据，但是这个购物车数据为$this->getCart()获取的购物车
        if( $cart != null )
        {
            $number  = 0;
            $variety = count($cart);
            foreach($cart as $cartObj)
            {
                $number += $cartObj['quantity'];
            }
        }
        else
        {
            $cart = $this->getCartInfo( true );

            $number = $cart['totalCart']['number'];
            $variety = $cart['totalCart']['variety'];
        }

        return ['number'=>$number, 'variety'=>$variety];
    }

  //public function getCartCount( $cart = null )
  //{
  //    if($cart == null)
  //        $cart = $this->getCart();
  //    $number  = 0;
  //    $variety = count($cart);
  //    foreach($cart as $cartObj)
  //    {
  //        $number += $cartObj['quantity'];
  //    }
  //    return ['number'=>$number, 'variety'=>$variety];
  //}

}
