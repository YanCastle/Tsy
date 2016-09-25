<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2016/07/28
 * Time: 10:50
 */

namespace Tsy\Library\Msg;


use Tsy\Library\View;

class Msg implements MsgIFace
{
    protected $handles=[];
    /**
     * @var MsgIFace $current
     */
    protected $current;
    protected $current_method='';
    /**
     * @var View $view
     */
    protected $view;
    public function __construct($config=[])
    {
//        $this->view=new View();
    }
    function load($Method,$Config=[]){
        if(!isset($this->handles[$Method])){
            $this->handles[$Method]=[];
        }
        if($StaticConfit = C('MSG')){
            if(isset($StaticConfit[strtoupper($Method)])){
                $Config = array_merge($StaticConfit[$Method],$Config);
            }
        }
        $md5 = md5(serialize($Config));
        if(!isset($this->handles[$Method][$md5])){
            $Class = 'Tsy\\Library\\Msg\\Driver\\'.$Method;
            if(class_exists($Class)){
                $this->current=new $Class($Config);
                $this->current_method=$Method;
                $this->handles[$Method][$md5]=$this->current;
                return true;
            }
            return '找不到:'.$Method.'类';
        }
        return true;
    }
    /**
     * 使用内容发送
     * @param $To
     * @param $Content
     * @return mixed
     */
    function send($To, $Content)
    {
        // TODO: Implement send() method.
        $this->current->send($To,$Content);
    }

    /**
     * 使用服务商模板发送
     * @param $To
     * @param $Params
     * @param $TemplateID
     * @return mixed
     */
    function RemoteTemplateSend($To, $Params, $TemplateID)
    {
        // TODO: Implement RemoteTemplateSend() method.
        return $this->current->RemoteTemplateSend($To,$Params,$TemplateID);
    }

    /**
     * 使用本地模板发送
     * @param $To
     * @param $Params
     * @param $Template
     * @return mixed
     */
    function LocalTemplateSend($To, $Params, $Content)
    {
        if(is_string($Content)){
            $MSG_TEMPLATE_DIR=C('MSG_TEMPLATE_DIR').DIRECTORY_SEPARATOR.$this->current_method.DIRECTORY_SEPARATOR.$Content.'.html';
            if(is_file($Content)){
                $Content = file_get_contents($Content);
            }elseif($MSG_TEMPLATE_DIR){
                $Content = file_get_contents($MSG_TEMPLATE_DIR);
            }else{
                
            }
            $View = new View();
            $View->assign($Params);
            $Content = $View->fetch('',$Content);
            return $this->current->LocalTemplateSend($To,$Params,$Content);
        }elseif(is_array($Content)){
            L(E('_NOT_SUPPORT_FOR_ARRAY_'));
            return false;
        }else{
            return false;
        }
    }

    /**
     * 接受内容
     * @return mixed
     */
    function receive()
    {
        // TODO: Implement receive() method.
    }
}