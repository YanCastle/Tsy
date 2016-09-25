<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/11
 * Time: 23:11
 */

namespace Tsy\Library;
use Tsy\Library\View;

/**
 * Class Controller
 * 请尽量不要在Controller中直接调用Model
 * @package Tsy\Library
 */
class Controller
{
    protected $className='';
    protected $swoole;
    public $Controller=[];
    public $PRIKey="";
    public $Params=[];
    public $__CLASS__='';
    protected $ModuleName;
    protected $ControllerName;
    protected $MethodName;
    protected $ObjectVarName;
    /**
     * @var Object $Object
     */
    public $Object;
    protected $MC;
    /**
     * @var View View
     */
    protected $view;
    function __construct()
    {
        $this->__CLASS__ = get_class($this);
        $this->MC = explode('\\\\',str_replace(['Controller','Object','Model'],'' ,$this->__CLASS__ ) );
        $ObjectName = str_replace('Controller','Object',$this->__CLASS__);
        if(class_exists($ObjectName)&&$ObjectName!=$this->__CLASS__){
            list($this->ModuleName,$this->ControllerName)=explode('\\\\',str_replace('Controller','' ,$this->__CLASS__));
            $this->Object=new $ObjectName();
//            $this-> = $this->Object;
            $this->PRIKey = $this->Object->pk;
//            $this->ObjectVarName=$ObjectVarName;
        }
        $this->className = $this->getControllerName();
        if(file_exists(APP_PATH.DIRECTORY_SEPARATOR.$this->ModuleName.'Config/controller.php'))
            $this->Controller=include APP_PATH.DIRECTORY_SEPARATOR.$this->ModuleName.'/Config/controller.php';
        if(isset($this->Controller[$this->ControllerName])&&
            isset($this->Controller[$this->ControllerName][$this->MethodName])) {
            $this->PRIKey = $this->Controller[$this->ControllerName]['_pki'];
//            $this->Params=$this->Controller[$this->ControllerName][$this->MethodName];
        }
        $this->view = new View();
    }

    /**
     * 模板显示 调用内置的模板引擎显示方法，
     * @access protected
     * @param string $templateFile 指定要调用的模板文件
     * 默认为空 由系统自动定位模板文件
     * @param string $charset 输出编码
     * @param string $contentType 输出类型
     * @param string $content 输出内容
     * @param string $prefix 模板缓存前缀
     * @return void
     */
    protected function display($templateFile='',$charset='',$contentType='',$content='',$prefix='') {
        $this->view->display($templateFile,$charset,$contentType,$content,$prefix);
    }

    /**
     * 输出内容文本可以包括Html 并支持内容解析
     * @access protected
     * @param string $content 输出内容
     * @param string $charset 模板输出字符集
     * @param string $contentType 输出类型
     * @param string $prefix 模板缓存前缀
     * @return mixed
     */
    protected function show($content,$charset='',$contentType='',$prefix='') {
        $this->view->display('',$charset,$contentType,$content,$prefix);
    }

    /**
     *  获取输出页面内容
     * 调用内置的模板引擎fetch方法，
     * @access protected
     * @param string $templateFile 指定要调用的模板文件
     * 默认为空 由系统自动定位模板文件
     * @param string $content 模板输出内容
     * @param string $prefix 模板缓存前缀*
     * @return string
     */
    protected function fetch($templateFile='',$content='',$prefix='') {
        return $this->view->fetch($templateFile,$content,$prefix);
    }

    /**
     *  创建静态页面
     * @access protected
     * @htmlfile 生成的静态文件名称
     * @htmlpath 生成的静态文件路径
     * @param string $templateFile 指定要调用的模板文件
     * 默认为空 由系统自动定位模板文件
     * @return string
     */
    protected function buildHtml($htmlfile='',$htmlpath='',$templateFile='') {
        $content    =   $this->fetch($templateFile);
        $htmlpath   =   !empty($htmlpath)?$htmlpath:HTML_PATH;
        $htmlfile   =   $htmlpath.$htmlfile.C('HTML_FILE_SUFFIX');
        Storage::put($htmlfile,$content,'html');
        return $content;
    }

    /**
     * 模板主题设置
     * @access protected
     * @param string $theme 模版主题
     * @return Action
     */
    protected function theme($theme){
        $this->view->theme($theme);
        return $this;
    }

    /**
     * 模板变量赋值
     * @access protected
     * @param mixed $name 要显示的模板变量
     * @param mixed $value 变量的值
     * @return Action
     */
    protected function assign($name,$value='') {
        $this->view->assign($name,$value);
        return $this;
    }

    function __call($name, $arguments)
    {
        $Object = $this->className.'Object';
        if($this->Object){
            if(method_exists($ObjectClass,$name)){
                return call_user_func_array($ObjectClass,$arguments);
            }
        }else
        if(class_exists($Object)){
            $ObjectClass = new $Object();
            if(method_exists($ObjectClass,$name)){
                return call_user_func_array($ObjectClass,$arguments);
            }
        }else{
            return '不存在的方法:'.$name;
        }
    }
    protected function getControllerName(){
        return substr($this->__CLASS__,0,strlen($this->__CLASS__)-10);
    }
    function set_swoole($swoole){
        $this->swoole=$swoole;
    }
    protected function send($UID,$data){
        //TODO 需要建立UID跟fd的连接信息，如果不是在swoole模式下还需要放到队列中去
    }
    function _empty($Action,$Data){
        $Object = $this->className;
        return class_exists($this->className)?controller($this->className.'/'.$Action,$Data,'','Object'):"{$this->className}/{$Action}方法不存在";
    }
    function get($ID=[]){
        if(!$ID){
            !isset($_POST[$this->PRIKey]) or $ID = $_POST[$this->PRIKey];
        }
        if($ID){
            $ClassName=$this->ControllerName;
            if(property_exists($this,'Object')){
                $objs=$this->Object->get($ID);
                return $objs;
            }
        }
        return [];
    }

    /**
     * 获取对象的列表
     * @param array $IDs 参数为各自对象的主键 此处不做限制
     * @param array $Properties 限定取出属性范围
     * @return array|bool
     */
    function gets($IDs=[]){
        $ObjectClass = str_replace('Controller','Object',$this->__CLASS__);
        if(class_exists($ObjectClass)){
            if($this->Object->is_dic){
                return array_values($this->Object->getAll());
            }elseif ($IDs){
                return array_values($this->Object->gets($_POST[$this->PRIKey.'s']));
            }elseif($this->PRIKey&&isset($_POST[$this->PRIKey.'s'])){
                return array_values($this->Object->gets($_POST[$this->PRIKey.'s']));
            }
        }
        if($this->PRIKey){
            $Model = D($this->ControllerName);
            if(isset($_REQUEST[$this->PRIKey.'s'])&&is_array($_REQUEST[$this->PRIKey.'s'])){
                $IDs = $Model->where([$this->PRIKey=>['in',$_REQUEST[$this->PRIKey.'s']]])->page($P,$N)->order($Sort)->getField($this->PRIKey,true);
            }else{
                $IDs = $Model->page($P,$N)->order($Sort)->getField($this->PRIKey,true);
            }
            return $IDs!==false?array_values($Model->obj($IDs)):FALSE;
        }else{
            return FALSE;
        }
    }
    function save(array $Params){
        $ObjectClass = str_replace('Controller','Object',$this->__CLASS__);
        if(class_exists($ObjectClass)){
            $Class = new $ObjectClass();
            if(method_exists($Class,'save')){
                return invokeClass($Class,'save' ,$_POST );
            }
        }
        if($this->PRIKey&&isset($_REQUEST[$this->PRIKey])&&is_numeric($_REQUEST[$this->PRIKey])){
            $Model = D($this->ControllerName);
            return $Model->where([$this->PRIKey=>$_REQUEST[$this->PRIKey]])->save($Params);
        }else{return FALSE;}
    }
    function del(){
        if($this->PRIKey&&isset($_REQUEST[$this->PRIKey])&&(is_numeric($_REQUEST[$this->PRIKey])||is_array($_REQUEST[$this->PRIKey]))){
            $IDs=[];
            if(is_array($_REQUEST[$this->PRIKey])){
                foreach($_REQUEST[$this->PRIKey] as $ID){
                    if(is_numeric($ID)){
                        $IDs[]=$ID;
                    }else{
                        return false;
                    }
                }
            }elseif(is_numeric($_REQUEST[$this->PRIKey])){
                $IDs=[$_REQUEST[$this->PRIKey]];
            }
            if($this->Object->allow_del){
                return !!$this->Object->del($IDs);
            }
            trigger_error('_ERROR_DENY_DEL_');
            return false;
        }else{return FALSE;}
    }
    function search($Keyword='',$W=[],$P=1,$N=20,$Sort=[],$Properties=false){
        if($this->Object instanceof Object){
//            $OBVN = $this->ObjectVarName;
//            $obj=$this->$OBVN;
//            if((is_string($Keyword)&&strlen($Keyword)>0)||(is_array($W)&&count($W)>0))
                return $this->Object->search($Keyword,$W,$Sort,$P,$N,$Properties);
//            elseif($Keyword===''&&$W===[]){
//                return
//            }
        }
//        $where = [];
//        if($keyword&&isset($this->Controller[$this->ControllerName]['_search'])){
//            foreach($this->Controller[$this->ControllerName]['_search'] as $column){
//                $where[$column]=['like',"%{$keyword}%"];db5757c0ed18381_store_combine
//            }
//        }
//        $Relation=[];//关系处理规则定义
//        if(is_array($W)&&count($W)){
//            foreach($W as $k=>$v){
//                if(substr($k,0,1)=='_'){
//                    //这是特殊处理字段
//                }else{
//                    $Router = explode('.',$k);
//                    if(count($Router)==2){
//                        //初始化关联查询数组
//                        if(!isset($Relation[$Router[0]])){$Relation[$Router[0]]=[];}
//                        $Relation[$Router[0]][$Router[1]]=$v;
//                    }else{
//                        $where[$k]=$v;
//                    }
//                }
//            }
//        }
//        //检测关联查询
//        $IDs=[];
//        if(count($Relation)){
//            foreach($Relation as $ModelName=>$ModelWhere){
//                if($ModelName&&$ModelWhere){
//                    $Rs = D($ModelName)->where($ModelWhere)->order($Sort)->getField($this->PRIKey,TRUE);
//                    if($Rs){
//                        if(strtoupper($W['_logic'])=='AND'){
//                            $IDs=array_intersect($IDs,$Rs);
//                        }else{
//                            $IDs=array_merge($IDs,$Rs);
//                        }
//                    }
//                }
//            }
//        }
//        if($where){
//            $IDs = D($this->ControllerName)->where($where)->order($Sort)->getField($this->PRIKey,TRUE);
//        }
//        $IDs=array_unique($IDs);
//        //开始分页处理
//        if(isset($W['_logic'])&&$where){
//            $Rs = D($this->ControllerName)->order($Sort)->where($where)->getField($this->PRIKey,TRUE);
//            if(strtoupper($W['_logic'])=='AND'){
//                $IDs = array_intersect($IDs,$Rs);
//            }else{
//                $IDs = array_merge($IDs,$Rs);
//            }
//        }
//        $IDs=array_unique($IDs);
////	    分页控制
//        $PRIKeyIDs = [];
//        for($i=$P-1;$i<($P*$N-1);$i++){
//            if($IDs[$i])
//                $PRIKeyIDs[]=$IDs[$i];
//        }
//        if(false!==$IDs){
//            return [
//                'L'=>array_values(D($this->ControllerName)->obj($PRIKeyIDs)),
//                'P'=>$P,
//                'N'=>$N,
//                'T'=>count($IDs)
//            ];
//        }else{
//            return FALSE;
//        }
    }
    function add($data=false){
        if(!$data)$data=$_POST;
        if($this->Object instanceof Object){
            return invokeClass($this->Object,'add',$data);
        }
        $ID = D($this->ControllerName)->add($_POST);
        return $ID?array_values(D($this->ControllerName)->obj([$ID]))[0]:false;
    }
}