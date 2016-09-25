<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2015/12/03
 * Time: 21:59
 */

namespace Tsy\Plugs\Build;

use Tsy\Plugs\Db\Db;
use Tsy\Plugs\Verify\Verify;

class Build
{
    public $Model;
    public $Db;
    public $ModulePath = '';
    public $ModuleName='';
    function __construct($ModulePath=''){
        $this->Model=M();
        $this->Db=new Db();
        $this->ModulePath=$ModulePath?$ModulePath:APP_PATH.DIRECTORY_SEPARATOR.process_queue('controller','get')[0];
    }

    /**
     * 自动构建数据库缓存
     */
    function buildDbConfig(){
        $DB_PREFIX = C('DB_PREFIX');
        $dbConf = "";
        foreach($this->Db->getColumns() as $table=>$columns){
            $tableName = str_replace($DB_PREFIX,'',$table);
            $columnConf = "";
            foreach($columns as $column){
                $filedConf = "";
                foreach($column as $k=>$v){
                    $filedConf.="'{$k}'=>'{$v}',\r\n";
                }
                $columnConf.=
"   '{$column['field']}'=>[
    $filedConf
    ],\r\n";
            }
            $dbConf.=
"'{$tableName}'=>[
$columnConf
],\r\n";
        }
        $db_php = "<?php\r\nreturn [{$dbConf}];";
        $db_php_path = $this->ModulePath.'Config/db.php';
        if(!is_dir(dirname($db_php_path))){
            @mkdir(dirname($db_php_path),0777,true);
        }
        file_put_contents($db_php_path,$db_php);
        file_put_contents($db_php_path,php_strip_whitespace($db_php_path));
    }

    /**
     * 自动构建控制层缓存
     */
    function buildControllerConfig(){
        $db_php_path = $this->ModulePath.'Config/db.php';
        $dbConf = include $db_php_path;
        $ControllerConf = [];
        $ModuleName = $this->ModuleName;//模块名称
        foreach($dbConf as $table=>$columns){
            $ControllerName = '';
            foreach(explode('_',$table) as $tb){
                $ControllerName.=ucwords($tb);
            }
            if(!isset($ControllerConf[$table])){
                $ControllerConf[$ControllerName]=[
                'get'=>[],
                'gets'=>[],
                'save'=>[],
                'del'=>[],
                'search'=>[
                        'keyword'=>[FALSE,Verify::$V_STRING],
                        'W'=>[FALSE,Verify::$V_ARRAY],
                        'P'=>[FALSE,Verify::$V_INT],
                        'N'=>[FALSE,Verify::$V_INT],
                    ],
                '_fields'=>$columns,
                '_search'=>[],
                '_save'=>[]
                ];
            }
            foreach($columns as $fields){
                if($fields['extra']=="auto_increment"||$fields['key']=="PRI"){
                    $PKConf = array_merge([TRUE],explode(' ',trim(str_replace(['(',')'],' ',$fields['type']))));
                    $ControllerConf[$ControllerName]['get']=[$fields['field']=>$PKConf];
                    $ControllerConf[$ControllerName]['gets']=[$fields['field']=>$PKConf];
                    $ControllerConf[$ControllerName]['save']=[$fields['field']=>$PKConf,'Params'=>[TRUE,Verify::$V_ARRAY=>[]]];
                    $ControllerConf[$ControllerName]['del']=[$fields['field']=>$PKConf];
                    $ControllerConf[$ControllerName]['_pki']=$fields['field'];
                }else{
                    $ControllerConf[$ControllerName]['save']['Params'][Verify::$V_ARRAY][]=$fields;
                }
                if($fields['extra']!="auto_increment"){
                    $AddConf = array_merge([$fields['null']!="NO"],explode(' ',trim(str_replace(['(',')'],' ',$fields['type']))));
                    $ControllerConf[$ControllerName]['add'][$fields['field']]=$AddConf;
                }
                if(in_array(substr($fields['type'],0,4),['char','text'])){
                    //搜索时所使用的字符串字段
                    $ControllerConf[$ControllerName]['_search'][]=$fields['field'];
                }
            }
            $ControllerFileContent="<?php
namespace {$ModuleName}\\Controller;
use {$ModuleName}\\Object\\{$ControllerName}Object;
use Tsy\\Library\\Controller;
class {$ControllerName}Controller extends Controller {}";
            $path = implode(DIRECTORY_SEPARATOR,[$this->ModulePath,'Controller',$ControllerName.'Controller.class.php']);
            if(!file_exists($path))
                file_put_contents($path,$ControllerFileContent);
        }
        $ConfStr = var_export($ControllerConf,true);
        $ConfStr = str_replace(['array (',')'],['[',']'],$ConfStr);
        $ConfPath = $this->ModulePath.'/Config/controller.php';
        file_put_contents($ConfPath,"<?php \r\nreturn $ConfStr;");
        //file_put_contents($ConfPath,php_strip_whitespace($ConfPath));
    }

    /**
     * 自动构建 Model 配置和缓存
     */
    function buildModelConfig(){
        $db_php_path = $this->ModulePath.'Config/db.php';
        $dbConf = include $db_php_path;
        $ModelConfig = [];
        $ModuleName  = $this->ModuleName;
        foreach($dbConf as $table=>$columns){
            if(!isset($ModelConfig[$table])){$ModelConfig[$table]=[];}
            $ModelName = '';
            foreach(explode('_',$table) as $tb){
                $ModelName.=ucwords($tb);
            }
            foreach($columns as $field){
                if($field['extra']=="auto_increment"||$field['key']=="PRI"){
                    $ModelConfig[$ModelName]['_pk']=explode(' ',trim(str_replace(['(',')'],' ',$field['type'])));
                }
                $ModelConfig[$ModelName][$field['field']]=explode(' ',trim(str_replace(['(',')'],' ',$field['type'])));
            }
            $ModelFileContent = "<?php
namespace {$ModuleName}\\Model;
use Tsy\\Library\\Model;
class {$ModelName}Model extends  Model{}";
            $ModelFilePath = $this->ModulePath."Model/{$ModelName}Model.class.php";
            if(!is_dir(dirname($ModelFilePath))){
                @mkdir(dirname($ModelFilePath));
            }
            if(!file_exists($ModelFilePath))
                file_put_contents($ModelFilePath,$ModelFileContent);
        }
        $ModelConfig = var_export($ModelConfig,true);
        $ModelConfig = str_replace(['array (',')'],['[',']'],$ModelConfig);
        $ModelPath = $this->ModulePath.'Config/model.php';
        file_put_contents($ModelPath,"<?php \r\nreturn $ModelConfig;");
        file_put_contents($ModelPath,php_strip_whitespace($ModelPath));
    }
    function buildObjectConfig(){
        $db_php_path = $this->ModulePath.'Config/db.php';
        $dbConf = include $db_php_path;
        $ObjectConfig = [];
        $ModuleName  = $this->ModuleName;
        foreach($dbConf as $table=>$columns){
            if(!isset($ObjectConfig[$table])){$ObjectConfig[$table]=[];}
            $ObjectName = '';
            foreach(explode('_',$table) as $tb){
                $ObjectName.=ucwords($tb);
            }
            $pk='';
            foreach($columns as $field){
                if($field['extra']=="auto_increment"||$field['key']=="PRI"){
                    $pk=$field['field'];
                    $ObjectConfig[$ObjectName]['_pk']=explode(' ',trim(str_replace(['(',')'],' ',$field['type'])));
                }
                $ObjectConfig[$ObjectName][$field['field']]=explode(' ',trim(str_replace(['(',')'],' ',$field['type'])));
            }
            $ObjectFileContent = "<?php
namespace {$this->ModuleName}\\Object;
use Tsy\\Library\\Object;
class {$ObjectName}Object extends  Object{
    protected \$main='{$ObjectName}';
    protected \$pk='{$pk}';
    protected \$link=[];
    protected \$property=[];
}";
            $ObjectFilePath = $this->ModulePath."Object/{$ObjectName}Object.class.php";
            if(!is_dir(dirname($ObjectFilePath))){
                @mkdir(dirname($ObjectFilePath));
            }
            if(!file_exists($ObjectFilePath))
                file_put_contents($ObjectFilePath,$ObjectFileContent);
        }
        $ObjectConfig = var_export($ObjectConfig,true);
        $ObjectConfig = str_replace(['array (',')'],['[',']'],$ObjectConfig);
        $ObjectPath = $this->ModulePath.'Config/object.php';
        file_put_contents($ObjectPath,"<?php \r\nreturn $ObjectConfig;");
        file_put_contents($ObjectPath,php_strip_whitespace($ObjectPath));
    }
}