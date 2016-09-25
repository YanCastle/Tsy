<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2016/07/27
 * Time: 9:40
 */

namespace Tsy\Library;


trait Report
{
    /**
     * 限制那些字段允许被Fields包含
     * @var array
     */
    protected $Fields=[
//        '字段名称'=>[
//            '规则1','规则2'
//        ],
//        'UID'=>[
//            'SUM','MAX','MIN'
//        ]
    ];
    /**
     * 限制哪些字段允许被Where包含
     * @var array
     */
    protected $Where=[];
    /**
     * 限制允许哪些字段参与分组
     * @var array
     */
    protected $Group=[];
    /**
     * 限制哪些字段允许排序
     * @var array
     */
    protected $Order=[];
    
    function report($Fields,$Where=false,$Group=false,$Order=false,$KeyField=false,$KeyOrder=0){
        if(!is_array($Fields)||!$Fields){
            trigger_error('_ERROR_FIELDS_PARAM_');
            return false;
        }
        if(is_array($Where)&&!$Where){
            trigger_error('_ERROR_WHERE_PARAM_');
            return false;
        }
        if(is_array($Group)&&!$Group){
            trigger_error('_ERROR_GROUP_PARAM_');
            return false;
        }
        if(is_array($Order)&&!$Order){
            trigger_error('_ERROR_ORDER_PARAM_');
            return false;
        }
        //过滤字段表
        foreach ($Fields as $k=>$Field){
            if(is_numeric($k)){
                //直接字段
                if(!isset($this->Fields[$Field])||preg_match('/[A-Za-z]+ [A-Za-z]+/',$Field)){
                    trigger_error('_ERROR_FIELD_PARAM_:'.$Field);
                    return false;
                }
            }elseif(!is_numeric($k)){
                //函数字段

            }
        }
    }
}