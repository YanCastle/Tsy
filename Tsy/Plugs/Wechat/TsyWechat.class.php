<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2015/11/20
 * Time: 11:20
 */
namespace Wechat;

use Tsy\Library\Controller;
use Tsy\Library\Model;
use Tsy\Plugs\Db\Db;

class TsyWechat extends Controller{
    private $appid='';
    private $token='';
    private $crypt='';
    private $secret='';
    private $access_token='';
    public $MsgType='';
    public $Event='';
    public $MsgTypeID='';
    public $LID = 0;
    public $ReplyID=0;
    public $MatchID=0;
    public $wechat =0;
    public $ReplyContent="";
    public $data=[];
    public $WechatAuth ;
    private $tokenKey;
    private $ticketKey;
    public $ticket;
    private $MatchRule=[];//被匹配成功的规则
//    public $wechat = new Wechat();
    function __construct($appid='',$token='',$secret='',$crypt=''){
        parent::__construct();
        $this->appid = $appid?$appid:C('WECHAT_APPID');
        $this->token = $token?$token:C('WECHAT_TOKEN');
        $this->crypt = $crypt?$crypt:C('WECHAT_CRYPT');
        $this->secret = $secret?$secret:C('WECHAT_SECRET');
        $this->tokenKey='wechat_token_'.$this->appid;
        $this->ticketKey='wechat_ticket_'.$this->appid;
        $access_token = S($this->tokenKey);
        $ticket = S($this->ticketKey);
        $this->access_token=time()>=explode('|',$access_token)[1]?false:explode('|',$access_token)[0];
        $this->ticket=time()>=explode('|',$ticket)[1]?false:explode('|',$ticket)[0];
        if($this->access_token){
            $this->WechatAuth = new WechatAuth($this->appid, $this->secret, $this->access_token);
        } else {
            $this->WechatAuth  = new WechatAuth($this->appid, $this->secret);
            $token = $this->WechatAuth->getAccessToken();
            $this->access_token=$token['access_token'];
            S($this->tokenKey,$this->access_token.'|'.(time()+7200));
        }
        if(!$this->ticket){
            $ticket = $this->WechatAuth->getJSTicket();
            if($ticket&&$ticket['ticket']){
                $this->ticket=$ticket['ticket'];
                S($this->ticketKey,$this->ticket.'|'.(time()+7200));
            }
        }
    }
    function getJSTicket(){
        if($this->ticket){
            return $this->ticket;
        }else{
            return false;
        }
    }

    /**
     * 微信调用方法
     */
    function wechat(){
        if(isset($_GET['signature'])&&isset($_GET['echostr'])
            &&isset($_GET['timestamp'])&&isset($_GET['nonce'])){
            echo $_GET['echostr'];
            return ;
        }
        try{
            $appid = C('WECHAT_APPID'); //AppID(应用ID)
            $token = C('WECHAT_TOKEN'); //微信后台填写的TOKEN
            $crypt = C('WECHAT_CRYPT'); //消息加密KEY（EncodingAESKey）

            /* 加载微信SDK */
            $wechat = new Wechat($token, $appid, $crypt);

            /* 获取请求信息 */
            $data = $wechat->request();
            $this->wechat = $wechat;
            $this->data = $data;
            if($data && is_array($data)){
                /**
                 * 你可以在这里分析数据，决定要返回给用户什么样的信息
                 * 接受到的信息类型有10种，分别使用下面10个常量标识
                 * Wechat::MSG_TYPE_TEXT       //文本消息
                 * Wechat::MSG_TYPE_IMAGE      //图片消息
                 * Wechat::MSG_TYPE_VOICE      //音频消息
                 * Wechat::MSG_TYPE_VIDEO      //视频消息
                 * Wechat::MSG_TYPE_SHORTVIDEO //视频消息
                 * Wechat::MSG_TYPE_MUSIC      //音乐消息
                 * Wechat::MSG_TYPE_NEWS       //图文消息（推送过来的应该不存在这种类型，但是可以给用户回复该类型消息）
                 * Wechat::MSG_TYPE_LOCATION   //位置消息
                 * Wechat::MSG_TYPE_LINK       //连接消息
                 * Wechat::MSG_TYPE_EVENT      //事件消息
                 *
                 * 事件消息又分为下面五种
                 * Wechat::MSG_EVENT_SUBSCRIBE    //订阅
                 * Wechat::MSG_EVENT_UNSUBSCRIBE  //取消订阅
                 * Wechat::MSG_EVENT_SCAN         //二维码扫描
                 * Wechat::MSG_EVENT_LOCATION     //报告位置
                 * Wechat::MSG_EVENT_CLICK        //菜单点击
                 */

                //记录微信推送过来的数据
//                file_put_contents('./data.json', json_encode($data));

                /* 响应当前请求(自动回复) */
                //$wechat->response($content, $type);

                /**
                 * 响应当前请求还有以下方法可以使用
                 * 具体参数格式说明请参考文档
                 *
                 * $wechat->replyText($text); //回复文本消息
                 * $wechat->replyImage($media_id); //回复图片消息
                 * $wechat->replyVoice($media_id); //回复音频消息
                 * $wechat->replyVideo($media_id, $title, $discription); //回复视频消息
                 * $wechat->replyMusic($title, $discription, $musicurl, $hqmusicurl, $thumb_media_id); //回复音乐消息
                 * $wechat->replyNews($news, $news1, $news2, $news3); //回复多条图文消息
                 * $wechat->replyNewsOnce($title, $discription, $url, $picurl); //回复单条图文消息
                 *
                 */

                //执行Demo
//                $this->demo($wechat, $data);
                $this->msgType($data);
                $this->match($data);
                $this->log($data);

            }
        } catch(\Exception $e){
            @file_put_contents('./error.json', json_encode($e->getMessage()));
        }
    }

    /**
     * 获取系统记录的消息类型编号
     * @param $data
     */
    function msgType($data){
        $MsgTypeModel = M('WechatMsgTypeDic');
        if($data['MsgType']=='event'){
            $this->Event = $data['Event'];
            $this->MsgType='event';
            $this->MsgTypeID = $MsgTypeModel->where(['Method'=>'EVENT','MsgType'=>$data['Event']])->cache(true)->getField('MsgTypeID');
        }else{
            $this->MsgTypeID = $MsgTypeModel->where(['Method'=>'TYPE','MsgType'=>$data['MsgType']])->cache(true)->getField('MsgTypeID');
            $this->MsgType=$data['MsgType'];
        }
    }

    /**
     * 微信消息记录
     * @param $data
     */
    function log($data){
        $LogModel = M('WechatLog');
        $this->LID = $LogModel->add([
            'To'=>$data['ToUserName'],
            'From'=>$data['FromUserName'],
            'Time'=>$data['CreateTime'],
            'MsgTypeID'=>$this->MsgTypeID,
            'Content'=>$data['Event']=='LOCATION'?json_encode([
                'Latitude'=>$data['Latitude'],
                'Longitude'=>$data['Longitude'],
                'Precision'=>$data['Precision']
            ]):(isset($data['Content'])?$data['Content']:(isset($data['EventKey'])?$data['EventKey']:'')),
            'MsgID'=>$data['MsgId'],
            'MatchRuleID'=>$this->MatchRule?$this->MatchRule['ConfigID']:null,
            'Match'=>json_encode($this->MatchRule,JSON_UNESCAPED_UNICODE)
        ]);
    }

    /**
     * 匹配算法
     * @param $data
     */
    function match($data){
        $MatchModel = M('WechatMatch');
        $DefaultRule = null;
        $MatchRule = null;
        $Rules = $MatchModel->where(['MsgTypeID'=>['IN',[$this->MsgTypeID,'21']],'Open'=>1])->order('`Order`,MatchTimes DESC')->select();
        if($Rules){
            //有匹配规则的
            foreach($Rules as $Rule){
                //如果已经匹配成功则不再循环
                if($this->MatchID){break;}
                if($Rule['StartTime']>0&&$Rule['StartTime']>time()){break;}
                if($Rule['EndTime']>0&&$Rule['EndTime']<time()){break;}
                switch(strtoupper($Rule['Method'])){
                    case 'PREG':
                        preg_match($Rule['Rule'],$data['Content'],$match);
                        if($match){
                            $MatchRule=$Rule;
                        }
                        break;
                    case 'EQ':
                        if($data['Content']==$Rule['Rule']){
                            $MatchRule=$Rule;
                        }
                        break;
                    case 'FUNC':
                        $json = json_decode($Rule['Rule'],true);
                        if($json){$Rule['Rule']=$json;}
                        if(call_user_func_array($Rule['Rule'],$data)){
                            $MatchRule=$Rule;
                        }
                        break;
                    case 'EVENT':
                        if($data['EventKey']==$Rule['Rule']){
                            $MatchRule=$Rule;
                        }
                        break;
                    case 'TEMPLATE':
                        $this->assign($this->data);
                        if(trim($this->fetch('',$Rule['Rule']))=='true')$MatchRule=$Rule;
                        break;
                    case 'DEFAULT':
                        $DefaultRule=$Rule;
                        break;
                    default:
//                        time();
                        break;
                }
            }
        }else{
            //查询是否存在默认处理方式
            echo 'success';
        }
        if($DefaultRule||$MatchRule){
            $this->MatchRule = $MatchRule?$MatchRule:$DefaultRule;
            $this->matchSuccess($this->MatchRule);
        }
        if($this->ReplyID){
            //调用回复配置进行回复处理
            $this->reply($this->ReplyID);
        }else{
//            echo 'success';
        }
    }
    function reply($ReplyID){
        $Reply = M('WechatReply')->where(['ReplyID'=>$ReplyID])->find();
        if($Reply){
            $Method = strtoupper($Reply['Method']);
            switch($Method){
                case 'TEXT':
//                    文本类型的生成
                    $this->ReplyContent = $Reply['Config'];
                    break;
                case 'TEMPLATE':
//                    根据模板生成内容并输出
                   $View = new View();
                   $View->assign($this->data);
                   $this->ReplyContent = $View->fetch("",$Reply['Config']);
                    break;
                case 'FUNC':
                    if(is_callable($Reply['Config'])){
                        $this->ReplyContent = call_user_func_array($Reply['Config'],[$this->wechat,$this->data,$Reply]);
                        if(is_array($this->ReplyContent)&&isset($this->ReplyContent['MsgTypeID'])&&isset($this->ReplyContent['Content'])){
                            //如果返回内容是数组，且携带有变更回复消息类型的参数则变更
                            $Reply['MsgTypeID']=$this->ReplyContent['MsgTypeID'];
                            $this->ReplyContent=$this->ReplyContent['Content'];
                        }elseif(is_string($this->ReplyContent)){

                        }else{
                            $this->ReplyContent='';
                        }
                    }
                    break;
            }
            if($Reply['MsgTypeID']){
                $MsgTypeReply = M('WechatMsgTypeDic')->where(['MsgTypeID'=>$Reply['MsgTypeID']])->getField('ReplyMethod');
                //检查是否存在该方法，如果存在则调用并回复
                try{
                    $json = json_decode($this->ReplyContent,true);
                    if($json){
                        $this->ReplyContent=$json;
                    }
                    if($MsgTypeReply&&method_exists($this->wechat,$MsgTypeReply)){
                        if(is_array($this->ReplyContent)){
                            call($this->wechat,$MsgTypeReply,$this->ReplyContent);
                        }else{
                            $this->wechat->$MsgTypeReply($this->ReplyContent);
                        }
//                        is_array($this->ReplyContent)?call($this->wechat,$MsgTypeReply,$this->ReplyContent):$this->wechat->$MsgTypeReply($this->ReplyContent);
                    }
                }catch (Exception $e){

                }
            }else{
                echo 'success';
            }
        }
    }
    function replyLog(){}
    protected function matchSuccess(array $Rule){
        $MatchModel = M('WechatMatch');
        $MatchModel->where(['ConfigID'=>$Rule['ConfigID']])->setInc('MatchTimes');
        $this->MatchID = $Rule['ConfigID'];
        $this->ReplyID = $Rule['ReplyID'];
        if($Rule['Success']){
            if(is_callable($Rule['Success'])){
                call_user_func_array($Rule['Success'],array_merge($this->data,['Reply'=>$Rule]));
            }else{
                $this->assign(array_merge($this->data,['this'=>$this,'Wechat'=>$this->wechat,'WechatAuth'=>$this->WechatAuth]));
                $rs = $this->fetch('',$Rule['Success']);
                if(preg_match('/[\{\].+[\]\]]/',$rs)){
                    //这返回内容是一个json
                    $this->data=array_merge($this->data,json_decode($rs,true));
                }
                if($rs){
                    if(is_callable($rs)){
                        call_user_func_array($rs,array_merge($this->data,['Reply'=>$Rule]));
                    }
                }
            }
        }
    }

    /**
     * 用于自动创建数据库
     */
    function build(){
        $file = __DIR__.'/wechat_build.sql';
        Db::build(new Model(),$file,'',C('DB_PREFIX'));
    }
    /**
     * 资源文件上传方法
     * @param  string $type 上传的资源类型
     * @return string       媒体资源ID
     */
    private function upload($type,$filename,$video_description=""){
        $appid     = $this->appid;
        $appsecret = $this->secret;
        switch ($type) {
            case 'image':
                $media    = $this->WechatAuth->materialAddMaterial($filename, $type);
                break;
            case 'voice':
                $media    = $this->WechatAuth->materialAddMaterial($filename, $type);
                break;
            case 'video':
                $media       = $this->WechatAuth->materialAddMaterial($filename, $type, $video_description);
                break;
            case 'thumb':
                $media    = $this->WechatAuth->materialAddMaterial($filename, $type);
                break;
            default:
                return '';
        }

        if($media["errcode"] == 42001){ //access_token expired
            session("token", null);
            return $this->upload($type,$filename);
        }
        return $media['media_id'];
    }
    function download($media_id,$savepath){

    }

    /**
     * @param $data 微信推送出来的数据
     */
    function member($data){

    }
    function getAllMembers(){}
    function getMember($openid){

    }

    /**
     * 初始化获取所有的微信用户列表写入到表中
     * @return bool
     */
    private function initMembers(){
        $WechatMemberModel = M('WechatMember');
        $SavedOpenIDs = $WechatMemberModel->getField('OpenID',true);
        $Rs = $this->WechatAuth->userGet();
        if(!isset($Rs['total'])){return false;}
        $Total = $Rs['total'];
        $OpenIDs = $Rs['data']['openid'];
        for($i=1;$i<intval($Total/10000)+1;$i++){
            //循环获取所有的openid值
            if(!$Rs['next_openid']){break;}//当next_openid为空时表示已获取完成，不再继续循环
            $Rs = $this->WechatAuth->userGet($Rs['next_openid']);
            if(!isset($Rs['total'])){return false;}
            $OpenIDs=array_merge($OpenIDs,$Rs['data']['openid']);
        }
        //比较当前已存在的openid，去除已存在的openid，然后写入数据库
        $NeedSaveOpenIDs = array_diff($OpenIDs,$SavedOpenIDs?$SavedOpenIDs:[]);
        $SaveData = array_map(function($v){return ['OpenID'=>$v];},$NeedSaveOpenIDs);
        return !!$WechatMemberModel->addAll($SaveData);
    }
    function init(){
        $this->initMembers();
        $this->initMemberInfo();
    }
    /**
     * 获取所有的用户信息
     */
    private function initMemberInfo(){
        $WechatMemberModel = M('WechatMember');
        $NeedInfoOpenIDs = $WechatMemberModel->where('NickName IS NULL')->getField('OpenID',true);
        if(!$NeedInfoOpenIDs){$NeedInfoOpenIDs=[];}
        foreach($NeedInfoOpenIDs as $OpenID){
            $UserInfo = $this->WechatAuth->userInfo($OpenID);
            if(isset($UserInfo['errcode'])){break;}
            $UserData = [
                'NickName'=>$UserInfo['nickname'],//当遇到颜文字时无法处理错误
                'Subscribe'=>$UserInfo['subscribe'],
                'City'=>$UserInfo['city'],
                'Country'=>$UserInfo['country'],
                'Province'=>$UserInfo['province'],
                'Language'=>$UserInfo['language'],
                'HeadImgUrl'=>$UserInfo['headimgurl'],
                'SubscribeTime'=>$UserInfo['subscribe_time'],
                'UnionID'=>$UserInfo['unionid'],
                'Remark'=>$UserInfo['remark'],
                'GroupID'=>$UserInfo['groupid'],
                'Sex'=>$UserInfo['sex'],//0表示未知
            ];
            try{
                $WechatMemberModel->where(['OpenID'=>$OpenID])->save($UserData);
            }catch(\PDOException $e){

            }
        }
    }
    function getOauth2AccessToken($Code){

    }
}