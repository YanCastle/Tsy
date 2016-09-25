<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2016/08/05
 * Time: 10:39
 */

namespace Tsy\Library\Define;


class FieldConfig
{
    /**
     * 电话号码
     */
    const TYPE_PHONE_NUMBER='0';
    /**
     * 邮件地址
     */
    const TYPE_EMAIL_ADDRESS='1';
    /**
     * URL
     */
    const TYPE_URL='2';
    protected $option=[];

    /**
     * 添加正则表达式规则
     * @param string $Pattern
     * @return FieldConfig
     */
    function preg(string $Pattern,$Msg=''){
        return $this->_option('Preg',$Pattern,$Msg);
    }

    /**
     * 设置默认值来源
     * @param $Rule
     * @param string $Msg
     * @return FieldConfig
     */
    function default($Rule,$Msg=''){
        return $this->_option('Default',$Rule,$Msg);
    }

    /**
     * 设定默认错误提示
     * @param $Msg
     * @return FieldConfig
     */
    function error($Msg){
        return $this->_option('ErrMsg',$Msg);
    }

    /**
     * 限定指定类型
     * @param string $Type
     * @return FieldConfig
     */
    function type(string $Type,$Msg=''){
        return $this->_option('Type',$Type,$Msg);
    }

    /**
     * 判断是否是某个属性
     * @param string $Type 用&表示与，用|表示或
     * @param string $Msg
     * @return FieldConfig
     */
    function insteadOf(string $Type,$Msg=''){
        return $this->_option('InsteadOf',$Type,$Msg);
    }

    /**
     * 验证数据
     * @param $data
     */
    function verify(&$data){
        if(!is_scalar($data)&&isset($this->option['Default'])){
            $data = $this->_default();
        }
    }
    private function _default(){
        $Rule= $this->option['Default'];
        if(is_callable($Rule)){
            return call_user_func($Rule);
        }elseif(is_string($Rule)){
            //暂时只支持字符串直接返回做默认值
            return $Rule;
        }elseif(is_array($Rule)){

        }else{

        }
        return null;
    }
    /**
     * 私有，设置属性
     * @param $Option
     * @param $Value
     * @return $this
     */
    private function _option($Option,$Value,$Msg=''){
        if($Value===null){
            unset($this->option[$Option]);
        }else{
            $this->option[$Option]=$Value;
        }
        return $this;
    }
}