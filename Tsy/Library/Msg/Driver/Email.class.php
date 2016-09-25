<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2016/07/28
 * Time: 11:10
 */

namespace Tsy\Library\Msg\Driver;


use Tsy\Library\Msg\MsgIFace;
use Tsy\Plugs\PHPMailer\PHPMailer;

class Email implements MsgIFace
{
    protected $handler;
    protected $config=[
        'Title'=>'Please Config The Email Title',
        'Subject'=>'Please Config The Email Title',
        'From'=>'castle@tansuyun.cn',
        'FromName'=>'Castle'
    ];
    public function __construct($config=[])
    {
        $this->handler = new PHPMailer();
        $this->handler->isSMTP();
        $this->handler->Port=$config['PORT'];
        $this->handler->Host=$config['HOST'];
        $this->handler->SMTPAuth=true;
        $this->handler->Username=$config['USERNAME'];
        $this->handler->Password=$config['PASSWORD'];
        $this->config = array_merge($this->config,$config);
//        $this->handler->setFrom($from[0],$from[1]);
//        $this->handler->addAddress($to[0],$to[1]);
//        $this->handler->Subject=$title;
//        $this->handler->msgHTML($content);
//        if($this->handler->send()){
//            return true;
//        }else{
//            L($this->handler->ErrorInfo);
//            return false;
//        }
    }

    /**
     * @param $To
     * @param $Content
     * @param array ...$
     */
    function send($To, $Content)
    {
        // TODO: Implement send() method.
        if(is_string($To)){
            $To=[$To,$To];
        }
        if(count($To)!=2){
            return false;
        }
        $this->handler->addAddress($To[0],$To[1]);
        if(!$this->handler->Subject){
            $this->handler->Subject = $this->config['Subject'];
        }
        if('root@localhost'==$this->handler->From){
            $this->handler->From = $this->config['From'];
        }
        if('Root User'==$this->handler->FromName){
            $this->handler->FromName = $this->config['FromName'];
        }
        $this->handler->msgHTML($Content);
        if($this->handler->send()){
            return true;
        }else{
            L($this->handler->ErrorInfo);
            return false;
        }
    }

    function RemoteTemplateSend($To, $Params, $TemplateID)
    {
        return false;
    }

    function LocalTemplateSend($To,$Params,$Content)
    {
        $Title = isset($Params['Title'])?$Params['Title']:$this->config['Title'];
        $From = isset($Params['From'])?$Params['From']:$this->config['From'];
        if(is_string($From)){
            $From=[$From,$From];
        }
        if(count($From)!=2){
            return false;
        }
//        $To = isset($Params['To'])?$Params['To']:$this->config['To'];

        $this->handler->setFrom($From[0],$From[1]);
        $this->handler->Subject=$Title;
//        $this->handler->msgHTML($Content);
        return $this->send($To,$Content);
    }

    function receive()
    {
        // TODO: Implement receive() method.
    }
}