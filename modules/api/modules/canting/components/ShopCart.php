<?php

namespace api\modules\canting\components;

use ArrayIterator;
use IteratorAggregate;
use yii\base\Object;
use yii\di\Instance;
use yii\redis\Connection;

class ShopCart extends Object implements IteratorAggregate
{
    private $_redis;
    private $_key;

    public $duration;

    public function __construct($key, $redis = 'redis', $duration = 7200, $config = [])
    {
        $this->_key = 'rms_shop_cart_'.$key;
        $this->_redis = Instance::ensure($redis,Connection::className());
        $this->duration = $duration;
        parent::__construct($config);
    }

    public function generateField($id , $attrValue)
    {
        if($attrValue) {
            ksort($attrValue);
        }
        return md5($id . serialize($attrValue));
    }

    public function addField($id, $user_id, $name, $price, $qty, $attrValue = [])
    {
        $field = $this->generateField($id, $attrValue);

        $value = [
            'id' => $id,
            'user_id'=>$user_id,
            'name' => $name,
            'price' => $price,
            'qty' => $qty,
            'attrValue' => $attrValue,
            'total' => $qty * $price
        ];

        $this->_redis->executeCommand('EXPIRE',[
            $this->_key,
            $this->duration
        ]);

        return (boolean)$this->_redis->executeCommand('HSETNX',[
            $this->_key,
            $field,
            json_encode($value)
        ]);
    }

    public function editField($field, $qty)
    {
       $value = $this->getField($field);
       if($value === ''){
           return false;
       }
       if($qty <= 0){
            //数量小于等于0 移除
            return $this->removeField($field);
       }
       $this->_redis->executeCommand('EXPIRE',[
            $this->_key,
            $this->duration
       ]);

       $value['qty'] = $qty;
       $value['total'] =  $qty * $value['price'];
       return (boolean)$this->_redis->executeCommand('HSET',[
           $this->_key,
           $field,
           json_encode($value)
       ]);
    }

    public function removeField($field)
    {
        return (boolean)$this->_redis->executeCommand('HDEL',[
            $this->_key,
            $field
        ]);
    }

    public function removeAll()
    {
        return (boolean)$this->_redis->executeCommand('DEL',[
            $this->_key
        ]);
    }

    public function all()
    {
        $keys = $this->_redis->executeCommand('HKEYS',[
            $this->_key
        ]);
        $values = $this->_redis->executeCommand('HVALS',[
            $this->_key
        ]);
        foreach ($values as &$value){
            $value = json_decode($value , true);
        }
        return array_combine($keys, $values);
    }

    public function getField($field)
    {
        $data = $this->_redis->executeCommand('HGET',[
            $this->_key,
            $field
        ]);
        return $data === false || $data === null ? '' : json_decode($data,true);
    }

    public function hasField($field)
    {
        return (boolean)$this->_redis->executeCommand('HEXISTS',[
            $this->_key,
            $field
        ]);
    }

    public function count()
    {
        /*return $this->_redis->executeCommand('HLEN',[
            $this->_key,
        ]);*/
        $total = 0;
        foreach ($this->getIterator() as $item){
            $total += $item['qty'];
        }
        return $total;
    }

    public function totalPrice()
    {
        $totalPrice  = 0 ;
        foreach ($this->getIterator() as $item){
            $totalPrice += $item['total'];
        }
        return $totalPrice;
    }

    public function getKey()
    {
        return $this->_key;
    }

    public function getIterator()
    {
        // TODO: Implement getIterator() method.
        return new ArrayIterator($this->all());
    }

    public function formatAttrValue()
    {
        foreach ($this->getIterator() as &$item){
            if($item['attrValue'] && is_array($item['attrValue'])){
                foreach ($item['attrValue'] as $attr){
                    if($attr['name'] == '尺寸'){
                        $item['specs'] = $attr;
                    }else{
                        $item['attrs'] = $attr;
                    }
                }
            }
        }
        return $this;
    }
}