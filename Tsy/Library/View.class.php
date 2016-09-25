<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 16-6-21
 * Time: 下午10:40
 */

namespace Tsy\Library;


use Tsy\Tsy;

class View
{
    /**
     * 模板输出变量
     * @var array tVar
     * @access protected
     */
    protected $tVar     =   array();

    /**
     * 模板主题
     * @var string theme
     * @access protected
     */
    protected $theme    =   '';

    /**
     * 模板变量赋值
     * @access public
     * @param mixed $name
     * @param mixed $value
     */
    public function assign($name,$value=''){
        if(is_array($name)) {
            $this->tVar   =  array_merge($this->tVar,$name);
        }else {
            $this->tVar[$name] = $value;
        }
    }

    /**
     * 取得模板变量的值
     * @access public
     * @param string $name
     * @return mixed
     */
    public function get($name=''){
        if('' === $name) {
            return $this->tVar;
        }
        return isset($this->tVar[$name])?$this->tVar[$name]:false;
    }

    /**
     * 加载模板和页面输出 可以返回输出内容
     * @access public
     * @param string $templateFile 模板文件名
     * @param string $charset 模板输出字符集
     * @param string $contentType 输出类型
     * @param string $content 模板输出内容
     * @param string $prefix 模板缓存前缀
     * @return mixed
     */
    public function display($templateFile='',$charset='',$contentType='',$content='',$prefix='') {
        // 解析并获取模板内容
        $content = $this->fetch($templateFile,$content,$prefix);
        // 输出模板内容
        $this->render($content,$charset,$contentType);
    }

    /**
     * 输出内容文本可以包括Html
     * @access private
     * @param string $content 输出内容
     * @param string $charset 模板输出字符集
     * @param string $contentType 输出类型
     * @return mixed
     */
    private function render($content,$charset='',$contentType=''){
        if(empty($charset))  $charset = C('DEFAULT_CHARSET');
        if(empty($contentType)) $contentType = C('TMPL_CONTENT_TYPE');
        echo $content;
    }

    /**
     * 解析和获取模板内容 用于输出
     * @access public
     * @param string $templateFile 模板文件名
     * @param string $content 模板输出内容
     * @param string $prefix 模板缓存前缀
     * @return string
     */
    public function fetch($templateFile='',$content='',$prefix='') {
        if(empty($content)) {
            $templateFile   =   $this->parseTemplateFile($templateFile);
            // 模板文件不存在直接返回
            if(!is_file($templateFile)) E(L('_TEMPLATE_NOT_EXIST_').':'.$templateFile);
        }else{
            defined('THEME_PATH') or    define('THEME_PATH', $this->getThemePath());
        }
        // 页面缓存
        ob_start();
        ob_implicit_flush(0);
        if('php' == strtolower(C('TMPL_ENGINE_TYPE'))) { // 使用PHP原生模板
            $_content   =   $content;
            // 模板阵列变量分解成为独立变量
            extract($this->tVar, EXTR_OVERWRITE);
            // 直接载入PHP模板
            empty($_content)?include $templateFile:eval('?>'.$_content);
        }else{
            // 视图解析标签
            $params = array('var'=>$this->tVar,'file'=>$templateFile,'content'=>$content,'prefix'=>$prefix);
        }
        $this->generaTemplate($params);
        // 获取并清空缓存
        $content = ob_get_clean();
        // 输出模板文件
        return $content;
    }

    /**
     * 自动定位模板文件
     * @access protected
     * @param string $template 模板文件规则
     * @return string
     */
    public function parseTemplateFile($template='') {
        if(is_file($template)) {
            return $template;
        }
        $depr       =   DIRECTORY_SEPARATOR;
        $template   =   str_replace(':', $depr, $template);

        // 获取当前模块
        $module   =  current_MCA('M');
        if(strpos($template,'@')){ // 跨模块调用模版文件
            list($module,$template)  =   explode('@',$template);
        }
        // 获取当前主题的模版路径
//        defined('THEME_PATH') or    define('THEME_PATH', $this->getThemePath($module));
        $ThemePath = defined('THEME_PATH')?THEME_PATH:$this->getThemePath($module);
//        $ThemePath .= DIRECTORY_SEPARATOR;
        // 分析模板文件规则
        if('' == $template) {
            // 如果模板文件名为空 按照默认规则定位
            $template = current_MCA('C') . $depr . current_MCA('A');
        }elseif(false === strpos($template, $depr)){
            $template = current_MCA('C') . $depr . $template;
        }
        $file   =   $ThemePath.$template.C('TMPL_TEMPLATE_SUFFIX');
        if(C('TMPL_LOAD_DEFAULTTHEME') && $ThemePath != C('DEFAULT_THEME') && !is_file($file)){
            // 找不到当前主题模板的时候定位默认主题中的模板
            $file   =   dirname($ThemePath).DIRECTORY_SEPARATOR.C('DEFAULT_THEME').DIRECTORY_SEPARATOR.$template.C('TMPL_TEMPLATE_SUFFIX');
        }
        return $file;
    }

    /**
     * 获取当前的模板路径
     * @access protected
     * @param  string $module 模块名
     * @return string
     */
    protected function getThemePath($module=''){
        $module or $module = current_MCA('M');
        // 获取当前主题名称
        $theme = $this->getTemplateTheme();
        // 获取当前主题的模版路径
        $tmplPath   =   C('VIEW_PATH'); // 模块设置独立的视图目录
        if(!$tmplPath){
            // 定义TMPL_PATH 则改变全局的视图目录到模块之外
            $tmplPath   =   defined('TMPL_PATH')? TMPL_PATH.DIRECTORY_SEPARATOR.$module.DIRECTORY_SEPARATOR : APP_PATH.DIRECTORY_SEPARATOR.$module.DIRECTORY_SEPARATOR.C('DEFAULT_V_LAYER').DIRECTORY_SEPARATOR;
        }
        return $tmplPath.$theme;
    }

    /**
     * 设置当前输出的模板主题
     * @access public
     * @param  mixed $theme 主题名称
     * @return View
     */
    public function theme($theme){
        $this->theme = $theme;
        return $this;
    }

    /**
     * 获取当前的模板主题
     * @access private
     * @return string
     */
    private function getTemplateTheme() {
        if($this->theme) { // 指定模板主题
            $theme = $this->theme;
        }else{
            /* 获取模板主题名称 */
            $theme =  C('DEFAULT_THEME');
            if(C('TMPL_DETECT_THEME')) {// 自动侦测模板主题
                $t = C('VAR_TEMPLATE');
                if (isset($_GET[$t])){
                    $theme = $_GET[$t];
                }elseif(cookie('think_template')){
                    $theme = cookie('think_template');
                }
                if(!in_array($theme,explode(',',C('THEME_LIST')))){
                    $theme =  C('DEFAULT_THEME');
                }
                cookie('think_template',$theme,864000);
            }
        }
        defined('THEME_NAME') || define('THEME_NAME',   $theme);                  // 当前模板主题名称
        return $theme?$theme . DIRECTORY_SEPARATOR:'';
    }

    public function generaTemplate(&$_data){
        $engine             =   strtolower(C('TMPL_ENGINE_TYPE'));
        $_content           =   empty($_data['content'])?$_data['file']:$_data['content'];
        $_data['prefix']    =   !empty($_data['prefix'])?$_data['prefix']:C('TMPL_CACHE_PREFIX');
        if('think'==$engine){ // 采用Think模板引擎
            if((!empty($_data['content']) && $this->checkContentCache($_data['content'],$_data['prefix']))
                ||  $this->checkCache($_data['file'],$_data['prefix'])) { // 缓存有效
                //载入模版缓存文件
                Storage::load(C('CACHE_PATH').$_data['prefix'].md5($_content).C('TMPL_CACHFILE_SUFFIX'),$_data['var']);
            }else{
                $tpl = Tsy::instance('Tsy\\Library\\Template');
                // 编译并加载模板文件
                $tpl->fetch($_content,$_data['var'],$_data['prefix']);
            }
        }else{
            // 调用第三方模板引擎解析和输出
            if(strpos($engine,'\\')){
                $class  =   $engine;
            }else{
                $class   =  'Tsy\\Library\\Template\\Driver\\'.ucwords($engine);
            }
            if(class_exists($class)) {
                $tpl   =  new $class;
                $tpl->fetch($_content,$_data['var']);
            }else {  // 类没有定义
                E(L('_NOT_SUPPORT_').': ' . $class);
            }
        }
    }

    /**
     * 检查缓存文件是否有效
     * 如果无效则需要重新编译
     * @access public
     * @param string $tmplTemplateFile  模板文件名
     * @return boolean
     */
    protected function checkCache($tmplTemplateFile,$prefix='') {
        if (!C('TMPL_CACHE_ON')) // 优先对配置设定检测
            return false;
        $tmplCacheFile = C('CACHE_PATH').$prefix.md5($tmplTemplateFile).C('TMPL_CACHFILE_SUFFIX');
        if(!Storage::has($tmplCacheFile)){
            return false;
        }elseif (filemtime($tmplTemplateFile) > Storage::get($tmplCacheFile,'mtime')) {
            // 模板文件如果有更新则缓存需要更新
            return false;
        }elseif (C('TMPL_CACHE_TIME') != 0 && time() > Storage::get($tmplCacheFile,'mtime')+C('TMPL_CACHE_TIME')) {
            // 缓存是否在有效期
            return false;
        }
        // 开启布局模板
        if(C('LAYOUT_ON')) {
            $layoutFile  =  THEME_PATH.C('LAYOUT_NAME').C('TMPL_TEMPLATE_SUFFIX');
            if(filemtime($layoutFile) > Storage::get($tmplCacheFile,'mtime')) {
                return false;
            }
        }
        // 缓存有效
        return true;
    }

    /**
     * 检查缓存内容是否有效
     * 如果无效则需要重新编译
     * @access public
     * @param string $tmplContent  模板内容
     * @return boolean
     */
    protected function checkContentCache($tmplContent,$prefix='') {
        if(Storage::has(C('CACHE_PATH').$prefix.md5($tmplContent).C('TMPL_CACHFILE_SUFFIX'))){
            return true;
        }else{
            return false;
        }
    }
    public function writeHtmlCache($content) {
        //2014-11-28 修改 如果有HTTP 4xx 3xx 5xx 头部，禁止存储
        //2014-12-1 修改 对注入的网址 防止生成，例如 /game/lst/SortType/hot/-e8-90-8c-e5-85-94-e7-88-b1-e6-b6-88-e9-99-a4/-e8-bf-9b-e5-87-bb-e7-9a-84-e9-83-a8-e8-90-bd/-e9-a3-8e-e4-ba-91-e5-a4-a9-e4-b8-8b/index.shtml
        if (C('HTML_CACHE_ON') && defined('HTML_FILE_NAME')
            && !preg_match('/Status.*[345]{1}\d{2}/i', implode(' ', headers_list()))
            && !preg_match('/(-[a-z0-9]{2}){3,}/i',HTML_FILE_NAME)) {
            //静态文件写入
            Storage::put(HTML_FILE_NAME, $content, 'html');
        }
    }
}