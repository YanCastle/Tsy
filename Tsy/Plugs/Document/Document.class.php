<?php

/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2016/6/29
 * Time: 16:17
 */
class Document
{
    static protected $docs=[
        'Classes'=>[],
        'Functions'=>[],
        'Objects'=>[],
        'ObjectMap'=>[]
    ];
    function loadPDM($File){
        $JSON = \Tsy\Plugs\PowerDesigner\PowerDesigner::analysis($File);
        $Tables=[];
        foreach ($JSON['Tables'] as $k=>$table){
            $Tables[sql_prefix($k,'')]=$table;
        }
        $JSON['Tables']=$Tables;
        self::$docs['PDM']=$JSON;
        return $this;
    }
    function generateObjects($ModuleName=''){
        //得到Object的目录
        $ModuleName=$ModuleName?$ModuleName:DEFAULT_MODULE;
        $Path=implode(DIRECTORY_SEPARATOR,[APP_PATH,$ModuleName,'Object']);
        foreach (self::$docs['PDM']['Tables'] as $TableName=>$TableProperties){
            if('_link'==substr($TableName,-5)){continue;}
            $ObjectName = parse_name($TableName,1);
            $ColumnComments=[];
            $AddFieldsConfigs=$SaveFieldsConfigs=[];
            $PKColumns=[];
            $SearchColumns=array_column($TableProperties['Columns'],'Code');
            $SearchColumnsString = $SearchColumns?('\''.implode('\',\'',$SearchColumns).'\''):'';
            foreach ($TableProperties['Columns'] as $ColumnName=>$ColumnProperties){
                if($ColumnProperties['P']){
                    //主键
                    $PKColumns[$ColumnName]=$ColumnProperties;
                }
                $ColumnCommentOneLine = str_replace(["\n","\r\n"],'；',$ColumnProperties['Comment']);
                $ColumnComments[] = implode(' ',[$ColumnProperties['Name'],$ColumnProperties['Code'],$ColumnProperties['DataType'],$ColumnProperties['I']?'自增':'',$ColumnProperties['P']?'主键':'',$ColumnProperties['M']?'必填':'',$ColumnProperties['DefaultValue'],str_replace(["\n","\r\n"],';  ',$ColumnProperties['Comment'])]);
                if(!$ColumnProperties['I']){
                    //开始处理addFieldsConfig
                    $Config=[
                        'FIELD_CONFIG_DEFAULT'=>$ColumnProperties['DefaultValue']?$ColumnProperties['DefaultValue']:'null',
                        'FIELD_CONFIG_DEFAULT_FUNCTION'=>'null',
                        'FIELD_CONFIG_VALUE'=>'null',
                        'FIELD_CONFIG_VALUE_FUNCTION'=>'null',
                    ];
                    switch ($ColumnName){
                        case 'CTime':
                            $Config['FIELD_CONFIG_VALUE_FUNCTION']='time';
                            break;
                        case 'UTime':
                            $Config['FIELD_CONFIG_VALUE_FUNCTION']='time';
                            break;
                        case 'CUID':
                            $Config['FIELD_CONFIG_VALUE_FUNCTION']='session("UID")';
                            break;
                        case 'UUID':
                            $Config['FIELD_CONFIG_VALUE_FUNCTION']='session("UID")';
                            break;
                    }
                    $ConfigString=[];
                    foreach ($Config as $Title=>$Value){
                        $ConfigString[]=($Value=='null'?'//':'  ')."            self::{$Title}=>'{$Config[$Title]}',//".(strpos($Title,'DEFAULT')>0?"当 {$ColumnProperties['Name']}({$ColumnProperties['Code']}) 的值不存在时，取该值或该函数的值":"不管 {$ColumnProperties['Name']}({$ColumnProperties['Code']}) 的值是否存在，取该值或该函数的值");
                    }
                    $ConfigString=implode(",\r\n",$ConfigString);
                    $AddFieldsConfigs[]="\r\n".(count(array_unique(array_values($Config)))>1?"  ":"//")."      '{$ColumnProperties['Code']}'=>[//字段名称:{$ColumnProperties['Name']},数据类型:{$ColumnProperties['DataType']},注释:{$ColumnCommentOneLine}\r\n{$ConfigString}\r\n".(count(array_unique(array_values($Config)))>1?"  ":"//")."      ]";
                    //开始处理saveFieldsConfig
                    $SaveConfig=[
                        'FIELD_CONFIG_DEFAULT'=>'null',
                        'FIELD_CONFIG_DEFAULT_FUNCTION'=>'null',
                        'FIELD_CONFIG_VALUE'=>'null',
                        'FIELD_CONFIG_VALUE_FUNCTION'=>'null',
                    ];
                    switch ($ColumnName){
                        case 'CTime':
                            $SaveConfig['FIELD_CONFIG_VALUE_FUNCTION']='unset';
                            break;
                        case 'UTime':
                            $SaveConfig['FIELD_CONFIG_VALUE_FUNCTION']='time';
                            break;
                        case 'CUID':
                            $SaveConfig['FIELD_CONFIG_VALUE_FUNCTION']='unset';
                            break;
                        case 'UUID':
                            $SaveConfig['FIELD_CONFIG_VALUE_FUNCTION']='session("UID")';
                            break;
                        case $PKColumns[0]:
                            $SaveConfig['FIELD_CONFIG_VALUE_FUNCTION']='unset';
                            break;
                    }
                    $SaveConfigString=[];
                    foreach ($SaveConfig as $Title=>$Value){
                        $SaveConfigString[]=($Value=='null'?'//':'  ')."            self::{$Title}=>'{$SaveConfig[$Title]}',//".(strpos($Title,'DEFAULT')>0?"当 {$ColumnProperties['Name']}({$ColumnProperties['Code']}) 的值不存在时，取该值或该函数的值":"不管 {$ColumnProperties['Name']}({$ColumnProperties['Code']}) 的值是否存在，取该值或该函数的值");
                    }
                    $SaveConfigString=implode(",\r\n",$SaveConfigString);
                    $SaveFieldsConfigs[]="\r\n".(count(array_unique(array_values($SaveConfig)))>1?"  ":"//")."      '{$ColumnProperties['Code']}'=>[//字段名称:{$ColumnProperties['Name']},数据类型:{$ColumnProperties['DataType']},注释:{$ColumnCommentOneLine}\r\n{$SaveConfigString}\r\n".(count(array_unique(array_values($SaveConfig)))>1?"  ":"//")."      ]";
                }
            }
            $AllFields='\''.implode('\',\'',array_diff(array_keys($TableProperties['Columns']),array_keys($PKColumns))).'\'';
            $AddFieldsConfigsString = implode(",\r\n",$AddFieldsConfigs);
            $SaveFieldsConfigsString = implode(",\r\n",$SaveFieldsConfigs);
            $ColumnCommentsString='
     * '.implode('
     * ',$ColumnComments);
            //自动生成一对一或一对多或多对多映射配置关系
            $PropertyAndLinkConfig=[
                'Property'=>[],'Link'=>[]
            ];
            foreach (array_merge($TableProperties['FKs']['Parent'],$TableProperties['FKs']['Child']) as $FKConfig){
                if(preg_match_all('/[PC]{2}\=[1NOPL]{2,3}(\=[A-Za-z][A-Za-z0-9]+){0,}/',$FKConfig['Properties']['Comment'],$Matchs)){
                    foreach ($Matchs[0] as $Row){
                        list($Key,$Relation,$PropertyName)=explode('=',$Row);
                        $Properties=['PROPERTY'];
                        switch (substr($Relation,0,2)){
                            case '1N':
                                $Properties[]='ARRAY';
                                break;
                            case '11':
                                $Properties[]='ONE';
                                break;
                            case 'NN':
                                $Properties[]='ARRAY';
                                break;
                            case 'N1':
                                $Properties[]='ONE';
                                break;
                        }
                        if(strlen($Relation)==3){
                            if(substr($Relation,2,1)=='O')
                                $Properties[]='OBJECT';//对象化映射
                            if(substr($Relation,2,1)=='P'&&$Properties[1]=='ONE')
                                $Properties[]='PROPERTY';//支持一个属性的非对象化映射情况下的子属性
                            if(substr($Relation,2,1)=='L'){
                                //Link属性，
                                //读取Link表的外键
                                $LinkTables=[];
                                $ChildTableCode=self::delPrefix($FKConfig['ChildTableCode'],1);
                                $ChildTable = self::$docs['PDM']['Tables'][parse_name($ChildTableCode)];
                                foreach ($ChildTable['FKs']['Child'] as $ChildFKConfig){
                                    if(preg_match('/[PC]{2}\=[1NOPL]{2,3}(\=[A-Za-z][A-Za-z0-9]+){0,}/',$ChildFKConfig['Properties']['Comment'])){continue;}
                                    $LinkTablePropertyName=self::delPrefix($ChildFKConfig['ParentTableCode'],1);
                                    $LinkTableColumns=array_keys($ChildFKConfig['ParentTable']['Columns']);
                                    $LinkTableColumns='\''.implode('\',\'',$LinkTableColumns).'\'';
                                    $LinkTables[]="'{$LinkTablePropertyName}'=>[
                    self::RELATION_TABLE_COLUMN=>'{$ChildFKConfig['ParentTableColumnCode']}',
                    self::RELATION_TABLE_FIELDS=>[{$LinkTableColumns}],
                ],";
                                }
                                $LinkTableString=implode("\r\n",$LinkTables);
                                $RelationTableFields=array_keys($ChildTable['Columns']);//关联表的字段
                                $RelationTableLinkHasProperty=count($RelationTableFields)>3?'true':'false';//是否关联表中具有属性
                                $RelationTableFieldsString = '\''.implode('\',\'',$RelationTableFields).'\'';
                                if(count($RelationTableFields)>3){
                                    $RelationTableLinkHasProperty='true';
                                    $RelationTableLinkHasPropertyMemo='  ';
                                }else{
                                    $RelationTableLinkHasProperty='false';
                                    $RelationTableLinkHasPropertyMemo='//';
                                }
                                $PropertyAndLinkConfig['Link'][]="'{$PropertyName}'=>[
            self::RELATION_TABLE_NAME=>'{$ChildTableCode}',
            self::RELATION_TABLE_COLUMN=>'{$FKConfig['ParentTableColumnCode']}',
            self::RELATION_TABLE_LINK_HAS_PROPERTY=>{$RelationTableLinkHasProperty},
            self::RELATION_TABLE_FIELDS=>[{$RelationTableFieldsString}],
            self::RELATION_TABLE_LINK_TABLES=>[
                 {$LinkTableString}
            ]
        ]";
                                continue;
                            }
                        }
                        $Relationship = implode('_',$Properties);
//                        $ChildTableName=parse_name(str_replace(['{$PREFIX}','prefix_'],'',$FKConfig['ChildTableCode']),1);
                        $ChildTableName=parse_name(sql_prefix($FKConfig['ChildTableCode'],''),1);
                        if(substr($Key,0,1)=='P'&&$FKConfig['Type']=='Parent'){
                            $PropertyName=$PropertyName?$PropertyName:parse_name(sql_prefix($FKConfig['ChildTableCode'],''),1);
                            $PropertyAndLinkConfig['Property'][]="'{$PropertyName}'=>[//{$FKConfig['ParentTable']['Columns'][parse_name(sql_prefix($FKConfig['ParentTableColumnCode'],''),1)]['Name']}  {$FKConfig['ChildTable']['Name']}  属性
            self::RELATION_TABLE_NAME=>'{$ChildTableName}',//属性关联表
            self::RELATION_TABLE_COLUMN=>'{$FKConfig['ChildTableColumnCode']}',//关联表中的关联字段
            self::RELATION_MAIN_COLUMN=>'{$FKConfig['ParentTableColumnCode']}',//主笔中的关联字段
            self::RELATION_TABLE_PROPERTY=>self::{$Relationship},            
        ],";
                        }
                        if(substr($Key,0,1)=='C'&&$FKConfig['Type']=='Child'){
                            $PropertyName=$PropertyName?$PropertyName:parse_name(sql_prefix($FKConfig['ParentTableCode'],''),1);
                            $PropertyAndLinkConfig['Property'][]="'{$PropertyName}'=>[//{$FKConfig['ChildTable']['Columns'][parse_name(sql_prefix($FKConfig['ChildTableColumnCode'],''),1)]['Name']}  {$FKConfig['ParentTable']['Name']}  属性
            self::RELATION_TABLE_NAME=>'{$ChildTableName}',//属性关联表
            self::RELATION_TABLE_COLUMN=>'{$FKConfig['ParentTableColumnCode']}',//关联表中的关联字段
            self::RELATION_MAIN_COLUMN=>'{$FKConfig['ChildTableColumnCode']}',//主笔中的关联字段
            self::RELATION_TABLE_PROPERTY=>self::{$Relationship},            
        ],";
                        }
                    }
                }
            }
            $PropertiesConfigString = implode("\r\n        ",$PropertyAndLinkConfig['Property']);
            $LinksConfigString = implode("\r\n        ",$PropertyAndLinkConfig['Link']);
            $FileContent="<?php
namespace {$ModuleName}\\Object;

use Tsy\\Library\\Object;
/**
 * {$TableProperties['Name']}
 * {$TableProperties['Comment']}
 * @package {$ModuleName}\\Object
 */
class {$ObjectName}Object extends Object
{
    /**
{$ColumnCommentsString}
     */
    /**
     * @var string
     */
    protected \$main='{$ObjectName}';
    protected \$pk='{$TableProperties['PK']}';
    public \$addFields=[{$AllFields}];//允许添加的字段，如果数组最后一个元素值为true则表示排除
    public \$saveFields=[{$AllFields}];//允许修改的字段，如果数组最后一个元素值为true则表示排除
    public \$addFieldsConfig=[
    {$AddFieldsConfigsString}
    ];
    public \$saveFieldsConfig=[
    {$SaveFieldsConfigsString}
    ];
    protected \$property=[
        {$PropertiesConfigString}
    ];
    protected \$link=[
       {$LinksConfigString}
    ];
    protected \$searchFields=[{$SearchColumnsString}];
    protected \$searchTable='{$ObjectName}';
    protected \$searchWFieldsConf=[
        '{$ObjectName}'=>'{$ObjectName}',        
    ];
    protected \$searchWFieldsGroup=[
        '{$ObjectName}'=>[{$SearchColumnsString}],
    ];
}";
            file_put_contents($Path.DIRECTORY_SEPARATOR.$ObjectName.'Object.class.php',$FileContent);
        }
        return $this;
    }
    function generateModels($ModuleName=''){
        $ModuleName=$ModuleName?$ModuleName:DEFAULT_MODULE;
        $Path=implode(DIRECTORY_SEPARATOR,[APP_PATH,$ModuleName,'Model']);
        foreach (self::$docs['PDM']['Tables'] as $TableName=>$TableProperties){
            $ObjectName = parse_name($TableName,1);
            $AllFields='\''.implode('\',\'',array_keys($TableProperties['Columns'])).'\'';
            $ColumnComments=[];
            $ModelMap=[];
            foreach ($TableProperties['Columns'] as $ColumnName=>$ColumnProperties){
                $ColumnComments[] = implode(' ',[$ColumnProperties['Name'],$ColumnProperties['Code'],$ColumnProperties['DataType'],$ColumnProperties['I']?'自增':'',$ColumnProperties['P']?'主键':'',$ColumnProperties['M']?'必填':'',$ColumnProperties['DefaultValue'],str_replace(["\n","\r\n"],';  ',$ColumnProperties['Comment'])]);
                $ModelMap[]='\''.strtolower($ColumnProperties['Code']).'\'=>\''.$ColumnProperties['Code'].'\'';
            }
            $ModelMapString = implode(',',$ModelMap);
            $ColumnCommentsString='
     * '.implode('
     * ',$ColumnComments);

            $FileContent="<?php
namespace {$ModuleName}\\Model;

use Tsy\\Library\\Model;
/**
 * {$TableProperties['Name']}
 * {$TableProperties['Comment']}
 * @package {$ModuleName}\\Object
 */
class {$ObjectName}Model extends Model
{
    /**
{$ColumnCommentsString}
     */
    /**
     * @var string
     */
     protected \$_map=[{$ModelMapString}];
}";
            file_put_contents($Path.DIRECTORY_SEPARATOR.$ObjectName.'Model.class.php',$FileContent);
        }
        return $this;
    }
    function generateControllers($ModuleName=''){
        $ModuleName=$ModuleName?$ModuleName:DEFAULT_MODULE;
        $Path=implode(DIRECTORY_SEPARATOR,[APP_PATH,$ModuleName,'Controller']);
        foreach (self::$docs['PDM']['Tables'] as $TableName=>$TableProperties){
            $ObjectName = parse_name($TableName,1);
            $AllFields='\''.implode('\',\'',array_keys($TableProperties['Columns'])).'\'';
            $ColumnComments=[];
            foreach ($TableProperties['Columns'] as $ColumnName=>$ColumnProperties){
                $ColumnComments[] = implode(' ',[$ColumnProperties['Name'],$ColumnProperties['Code'],$ColumnProperties['DataType'],$ColumnProperties['I']?'自增':'',$ColumnProperties['P']?'主键':'',$ColumnProperties['M']?'必填':'',$ColumnProperties['DefaultValue'],str_replace(["\n","\r\n"],';  ',$ColumnProperties['Comment'])]);
            }
            $ColumnCommentsString='
     * '.implode('
     * ',$ColumnComments);
            $FileContent="<?php
namespace {$ModuleName}\\Controller;

use Tsy\\Library\\Controller;
/**
 * {$TableProperties['Name']}
 * {$TableProperties['Comment']}
 * @package {$ModuleName}\\Object
 */
class {$ObjectName}Controller extends Controller
{
    /**
{$ColumnCommentsString}
     */
    /**
     * @var string
     */
}";
            file_put_contents($Path.DIRECTORY_SEPARATOR.$ObjectName.'Controller.class.php',$FileContent);
        }
        return $this;
    }
    /**
     * 获取文档信息
     * 这儿是描述信息
     * @login true 需要登录
     * @param $Class
     * @author castle<castle@tansuyun.cn>
     * @return $this
     * @link http://www.baidu.com?
     *
     */
    function getDoc($name='',$MethodsAccess=['public']){
//        self::$docs=[
//            'Classes'=>[
//                '完整类名'=>[
//                    'memo'=>'类说明',
//                    'type'=>'',//这个类是什么类型，控制器？Model？Object？其他？
//                    'properties'=>[
//                        '属性名称'=>[
//                            'name'=>'属性名称',
//                            'zh'=>'中文名称',
//                            'access'=>'public/protected/private',//三选一
//                            'memo'=>'属性备注',
//                            'type'=>'属性类型'
//                        ]
//                    ],
//                    'methods'=>[
//                        '方法名称'=>[
//                            'params'=>[ //参数列表
//                                '参数名称'=>[
//                                    'name'=>'参数名称',
//                                    'zh'=>'中文名称',
//                                    'memo'=>'参数备注',
//                                    'type'=>'参数类型',
//                                    'default'=>'参数默认值'
//                                ]
//                            ],
//                            'login'=>true,//是否需要登录
//                            'name'=>'方法名称',
//                            'zh'=>'方法中文名',
//                            'access'=>'访问性',
//                            'memo'=>'注释',
//                            'author'=>'作者信息',
//                            'link'=>'帮助信息链接地址',
//                            'return'=>[//返回类型
//
//                            ]
//                        ]
//                    ]
//                ]
//            ],
//            'Functions'=>[
//                '函数名称'=>[
//                    'params'=>[ //参数列表
//                        '参数名称'=>[
//                            'name'=>'参数名称',
//                            'zh'=>'中文名称',
//                            'memo'=>'参数备注',
//                            'type'=>'参数类型',
//                            'default'=>'参数默认值'
//                        ]
//                    ],
//                    'name'=>'方法名称',
//                    'zh'=>'方法中文名',
//                    'memo'=>'注释',
//                    'author'=>'作者信息',
//                    'link'=>'帮助信息链接地址',
//                    'return'=>[//返回类型
//
//                    ]
//                ]
//            ]
//        ];
        if(is_string($name)){
            if(is_object($name)){
                $this->parseClass($name,$MethodsAccess);
            }elseif(class_exists($name)){
                $class = \Tsy\Tsy::instance($name);
                $this->parseClass($class,$MethodsAccess);
            }elseif(function_exists($name)){

            }elseif(is_dir($name)){
//                each_dir() 遍历循环

            }elseif(is_file($name)){

            }else{
//                return $this;
            }
        }elseif(is_object($name)){
            $this->parseClass($name,$MethodsAccess);
        }
        return $this;
    }
    function parseClass($class,$MethodsAccess){
//        判断类是否是Controller/Object/Model中的一种，如果是则调用对应类型的解析方法
        $RefClass = new ReflectionClass($class);
        $ClassType = '';
        if(isset(self::$docs['Classes'][$RefClass->getName()])){
            return $this;
        }
        foreach (['Tsy\Library\Object','Tsy\Library\Model','Tsy\Library\Controller'] as $InsideClass){
            if($RefClass->isSubclassOf($InsideClass)){
                $ClassType = str_replace('Tsy\Library\\','',$InsideClass);
            }
        }
        switch ($ClassType){
            case 'Object':
                $this->parseObject($RefClass,$MethodsAccess);
                break;
            case 'Controller':
                $this->parseController($RefClass,$MethodsAccess);
                break;
            case 'Model':
                $this->parseModel($RefClass,$MethodsAccess);
                break;
            default:
                self::$docs['Classes'][$RefClass->getName()]=array_merge([
                    'memo'=>'',
                    'zh'=>'',
                    'name'=>'',
                    'type'=>'',//这个类是什么类型，控制器？Model？Object？其他？
                    'properties'=>[],
                    'methods'=>[]
                ],$this->parseDocComment($RefClass->getDocComment(),null,$RefClass));
//        foreach ($RefClass->getProperties() as $property){
//
//        }
                //开始解析方法注释
                $methods = [];
                foreach ($RefClass->getMethods() as $method){
//            $method->isPrivate() or $access =
                    $access = 'public';
                    if($method->isPrivate()){$access='private';}
                    if($method->isProtected()){$access='protected';}
                    if($method->isPublic()){$access='public';}
//            限定输出的方法范围
                    if(!in_array($access,$MethodsAccess)){continue;}
                    $methods[$method->getName()]=array_merge([
                        'name'=>$method->getName(),'access'=>$access,'static'=>$method->isStatic()
                    ],$this->parseDocComment($method->getDocComment(),$method));
                }
                self::$docs['Classes'][$RefClass->getName()]['methods']=$methods;
                break;
        }
        return $this;
    }
    protected function parseDocComment($Comment,$Method=null,ReflectionClass $class=null){
        $Comment = str_replace(['/**','*/'," * "],'' ,$Comment);
        $Comment = str_replace("\r\n","\n" ,$Comment );
        $Comment = trim($Comment,"\n");
        $Comment = explode("\n",$Comment );
        $data=['memo'=>[],'params'=>[]];
        $matched=false;
        foreach ($Comment as $line=>$content){
            $content = trim($content);
            if(preg_match('/^@[a-z]+[ .]+/',$content,$match)){
//                这是标签内容
                $fields = explode(' ',$content );
                $key = str_replace('@','' ,$fields[0] );
                if(in_array($key,['package','link','author','version','access','login'] )){
                    $data[$key]=$fields[1];
                }else{
                    $tmpFields = array_diff($fields,['']);
//                    $
                    $fields=[];
                    foreach ($tmpFields as $field){
                        $fields[]=$field;
                    }
                    switch ($key){
                        case 'param':
                            $count = count($fields);
                            if($count>=5){
                                $param=[
                                    'type'=>$fields[1],
                                    'name'=>$fields[2],
                                    'zh'=>$fields[3],
                                    'memo'=>substr($content,strpos($content, $fields[4]))
                                ];
                            }elseif($count==4){
                                $param=[
                                    'type'=>$fields[1],
                                    'name'=>$fields[2],
                                    'zh'=>$fields[3],
                                    'memo'=>''
                                ];
                            }elseif($count==3){
                                if(substr($fields[1],0,1 )=='$'){
                                    //变量名称
                                    $param=[
                                        'type'=>'',
                                        'name'=>$fields[1],
                                        'zh'=>$fields[2],
                                        'memo'=>''
                                    ];
                                }else{
                                    //变量类型
                                    $param=[
                                        'type'=>$fields[1],
                                        'name'=>$fields[2],
                                        'zh'=>$fields[2],
                                        'memo'=>''
                                    ];
                                }
                            }elseif($count==2){
                                $param=[
                                    'type'=>'',
                                    'name'=>$fields[1],
                                    'zh'=>$fields[1],
                                    'memo'=>''
                                ];
                            }else{
                                $param=[];
                            }
                            $data['params'][]=$param;
                            break;
                        case 'example':

                            break;
                        case 'return':
                            unset($fields[0]);
                            $data['return']=implode(' ',$fields );
                            break;
                    }
                }
            }elseif($content){
                if($line===0){
                    $data['zh']=trim($content);
                }elseif($line>0&&!$matched){
                    $data['memo'][]=trim($content);
                }
            }
        }
        if($data['memo']){
            $data['memo'] = implode("\r\n",$data['memo'] );
        }else{
            unset($data['memo']);
        }
        if($data['params']){
            $data['params'] = array_key_set($data['params'],'name');
        }
        //开始做反射检测
        if($Method instanceof ReflectionMethod){
            $data['name']=$Method->getName();
            foreach ($Method->getParameters() as $parameter){
                $name = '$'.$parameter->getName();
                $param=[
                    'name'=>$name,
                    'must'=>!$parameter->isOptional(),
                    'default'=>$parameter->isOptional()?$parameter->getDefaultValue():'',
                    'pos'=>$parameter->getPosition()
                ];
                $data['params'][$name] = isset($data['params'][$name])?(array_merge($data['params'][$name],$param)):array_merge([
                    'type'=>'',
                    'name'=>$name,
                    'zh'=>$name,
                    'memo'=>''
                ], $param);
            }
        }elseif($Method instanceof ReflectionFunction){
            $data['name']=$Method->getName();
            foreach ($Method->getParameters() as $parameter){
                $name = '$'.$parameter->getName();
                $param=[
                    'name'=>$name,
                    'must'=>!$parameter->isOptional(),
                    'default'=>$parameter->isOptional()?$parameter->getDefaultValue():'',
                    'pos'=>$parameter->getPosition()
                ];
                $data['params'][$name] = isset($data['params'][$name])?(array_merge($data['params'][$name],$param)):array_merge([
                    'type'=>'',
                    'name'=>$name,
                    'zh'=>$name,
                    'memo'=>''
                ], $param);
            }
        }else{

        }
        if($class instanceof ReflectionClass){
            $data['name']=$class->getName();
            $data['namespace']=$class->getNamespaceName();
            
        }
        return $data;
    }
    function parseModel(ReflectionClass $RefClass,array $MethodsAccess){

    }
    /**
     * 解析对象文档
     * @param $RefClass
     * @param $MethodsAccess
     */
    function parseObject(ReflectionClass $RefClass,array $MethodsAccess){
        $ClassName = $RefClass->getName();
        self::$docs['Classes'][$ClassName]=array_merge([
            'memo'=>'',
            'zh'=>'',
            'name'=>'',
            'type'=>'Object',//这个类是什么类型，控制器？Model？Object？其他？
            'Properties'=>[],
            'methods'=>[],
            'Object'=>[],
            'Comment'=>$RefClass->getDocComment()
        ],$this->parseDocComment($RefClass->getDocComment(),null,$RefClass));
        $Class = $RefClass->newInstance();
//        读取属性渲染对象化配置
        $Properties = $RefClass->getProperties();
        $Object=[];
        $ObjectColumns=[];
        $ObjectSetting=[];
        foreach ($Properties as $Property){
            switch ($Property->getName()){
                case 'main':
//                    主表
//                    读取fields属性，检查是否有值
                    $ObjectSetting['main']=$TableName=parse_name($Class->main,1);
                    $Fields=[];
                    $Values = $Class->fields;
                    if(is_string($Values)&&$Values){
                        $Fields=explode(',',$Fields);
                    }elseif($Values&&is_array($Values)){
                        if(true === ($LastValue = array_shift($Values))){
//                            取差集

                        }else{
                            array_push($Values,$LastValue);
                        }

                    }else{
                        //TODO DB Fields需要优化
                        $Table = isset(self::$docs['PDM']['Tables'][parse_name($TableName)])?self::$docs['PDM']['Tables'][parse_name($TableName)]:[];
                        $Fields = isset($Table['Columns'])?array_keys($Table['Columns']):M($TableName)->getDbFields();
                    }
                    //生成数据对象
                    $Object=array_merge($Object,array_fill_keys($Fields,1));
                    foreach (self::$docs['PDM']['Tables'][parse_name($TableName)]['Columns'] as $ColumnName=>$column){
                        if(in_array($ColumnName,$Fields))
                            $ObjectColumns[$ColumnName]=$column;
                    }
                    break;
                case 'pk':
                    $ObjectSetting['pk']=$Class->pk;
//                    主键
                    break;
                case 'property':
//                    一对一、一对多属性
                    $ObjectSetting['property']=$ObjectProperties=$Class->property;

                    foreach ($ObjectProperties as $PropertyName=>$ObjectProperty){
                        if(isset($ObjectProperty[Tsy\Library\Object::RELATION_TABLE_NAME])){
                            //表映射
                            $TableName=$ObjectProperty[Tsy\Library\Object::RELATION_TABLE_NAME];
                            $Fields=[];
                            $Values = isset($ObjectProperty[Tsy\Library\Object::RELATION_TABLE_FIELDS])?$ObjectProperty[Tsy\Library\Object::RELATION_TABLE_FIELDS]:'';
                            if(is_string($Values)&&$Values){
                                $Fields=explode(',',$Fields);
                            }elseif($Values&&is_array($Values)){
                                if(true === ($LastValue = array_pop($Values))){
//                            取差集
                                    $Fields=array_diff(array_keys(self::$docs['PDM']['Tables'][parse_name($TableName)]['Columns']),$Values);
                                }else{
                                    array_push($Values,$LastValue);
                                }

                            }else{
                                //TODO DB Fields需要优化
                                $Fields = isset(self::$docs['PDM']['Tables'][parse_name($TableName)])?array_column(self::$docs['PDM']['Tables'][parse_name($TableName)]['Columns'],'Code'):M($TableName)->getDbFields();
                            }
                            //生成数据对象
                            $ColumnPrifix='';
                            if($ObjectProperty[Tsy\Library\Object::RELATION_TABLE_PROPERTY]==Tsy\Library\Object::PROPERTY_ONE)
                                $Object=array_merge($Object,array_fill_keys($Fields,1));
                            elseif($ObjectProperty[Tsy\Library\Object::RELATION_TABLE_PROPERTY]==Tsy\Library\Object::PROPERTY_ONE_OBJECT){
//                                处理一对一对象化信息
                                $Object[$PropertyName]=array_fill_keys($Fields,1);
                                $ColumnPrifix=$PropertyName;
                            }else{
                                $ColumnPrifix=$PropertyName;
                                $Object[$PropertyName]=array_fill_keys($Fields,1);
                            }
                            foreach (self::$docs['PDM']['Tables'][parse_name($TableName)]['Columns'] as $ColumnName=>$column){
                                if(in_array($ColumnName,$Fields))
                                    $ObjectColumns[($ColumnPrifix?($ColumnPrifix.'.'):'').$ColumnName]=$column;
                            }
                        }else
                        if(isset($ObjectProperty[Tsy\Library\Object::RELATION_OBJECT_NAME])){
                            //对象映射
//                            $TableName=$ObjectProperty[Tsy\Library\Object::RELATION_OBJECT_NAME];
                            self::$docs['ObjectMap'][$ObjectProperty[Tsy\Library\Object::RELATION_OBJECT_NAME]] = $ObjectProperty;
                        }
                    }
                    break;
                case 'link':
//                    多对多关联
                    $ObjectSetting['link']=$Links = $Class->link;
                    foreach ($Links as $PropertyName=>$PropertyConfig){
                        $Fields=[];
                        if($PropertyConfig[\Tsy\Library\Object::RELATION_TABLE_LINK_HAS_PROPERTY]){
                            //关联表带属性，需要把相关关联表字段带入到输出结果中
                            foreach (self::$docs['PDM']['Tables'][parse_name($PropertyConfig[\Tsy\Library\Object::RELATION_TABLE_NAME])]['Columns'] as $ColumnName=>$column){
                                $ObjectColumns[($PropertyName?($PropertyName.'.'):'').$ColumnName]=$column;
                                $Fields[]=$ColumnName;
                            }
                        }else{

                        }
                        //循环处理
                        foreach ($PropertyConfig[\Tsy\Library\Object::RELATION_TABLE_LINK_TABLES] as $TableName=>$Config){
//                            读取字段
                            foreach (self::$docs['PDM']['Tables'][parse_name($TableName)]['Columns'] as $ColumnName=>$column){
                                $ObjectColumns[$PropertyName.'.'.$ColumnName]=$column;
                                $Fields[]=$ColumnName;
                            }
                        }
                        $Object[$PropertyName]=array_fill_keys($Fields,1);
                    }
                    break;
                case 'searchFields':
//                    限定Keyword搜索的
                    $ObjectSetting['searchFields']=$Class->searchFields;
                    break;
                case 'searchTable':
//                    限定Keyword的搜索表
                    $ObjectSetting['searchTable']=$Class->searchTable;
                    break;
                case 'searchWFieldsConf':
//                    设定分组精确搜索的表配置
                    $ObjectSetting['searchWFieldsConf']=$Class->searchWFieldsConf;
                    break;
                case 'searchWFieldsGroup':
//                    设定分组精确搜索的字段配置
                    $ObjectSetting['searchWFieldsGroup']=$Class->searchWFieldsGroup;
                    break;
                case 'allow_add':
//                    是否允许添加
                    $ObjectSetting['allow_add']=$Class->allow_add;
                    break;
                case 'allow_save':
                    $ObjectSetting['allow_save']=$Class->allow_save;
                    break;
                case 'allow_del':
                    $ObjectSetting['allow_del']=$Class->allow_del;
                    break;
                case 'is_dic':
//                    是否字典表
                    $ObjectSetting['is_dic']=$Class->is_dic;
                    break;
                case 'map':break;
                default:break;
            }
        }
        $Result = [
            'Object'=>$Object,
            'ObjectName'=>self::$docs['PDM']['Tables'][parse_name($ObjectSetting['main'])]['Name'],
            'ObjectJSON'=>json_format($Object),
            'ObjectSetting'=>$ObjectSetting,
            'ObjectColumns'=>$ObjectColumns
        ];
        self::$docs['Classes'][$ClassName]=array_merge(self::$docs['Classes'][$ClassName],$Result);
        self::$docs['Objects'][$ClassName]=$Result;
//        开始处理对象操作方法
        $methods=[];
        $ObjectZhName=self::$docs['PDM']['Tables'][strtolower($Class->main)]['Name'];
//        preg_replace('//','',$ObjectZhName);
//        TODO 更换替换逻辑
        $ObjectZhName=str_replace(['字典表'],'',$ObjectZhName);
        foreach ($RefClass->getMethods() as $reflectionMethod){
            $MethodName = $reflectionMethod->getName();
            switch ($MethodName){
                case 'add':
                    if($Class->allow_add){
//                        当 文件名 为框架Object时表示没有本地编写的
//                        $file= $reflectionMethod->getFileName();
                        if('Object.class.php'==array_pop(explode('\\',$reflectionMethod->getFileName()))){
                            //使用框架的add方法，补全文档参数信息
                            $Comment = "{$ObjectZhName}  添加\r\n";
                            //Field字段名称，Design字段配置
                            foreach (self::parseFieldsConfig($Class->main,$Class->addFields) as $Field=>$Design){
                                if($Field==$Class->pk)continue;
                                $Comment .= "@param {$Design['DataType']} {$Design['Code']} {$Design['Name']} {$Design['Comment']}\r\n";
                            }
                            $Comment .= "@memo 无\r\n";
                            $Comment .= "@return bool|{$Class->main}\r\n";
                        }else{
                            //使用自定义的add方法，读取自定义的参数信息
                            $Comment = $reflectionMethod->getDocComment();
                        }
                        $methods['add']=array_merge([
                            'name'=>'add','access'=>'public','static'=>false,'Comment'=>$Comment
                        ],$this->parseDocComment($Comment));
                    }
                    break;
                case 'del':
                    if($Class->allow_addel){
                        $PKConfig = self::parseFieldsConfig($Class->main,$Class->pk)[$Class->pk];
                        $methods['del']=array_merge([
                            'name'=>'del','access'=>'public','static'=>false,'Comment'=>$Comment
                        ],$this->parseDocComment("{$ObjectZhName}  删除\r\n@param int \${$Class->pk} {$PKConfig['Name']} {$PKConfig['Comment']}"));
                    }
                    break;
                case 'save':
                    if($Class->allow_save){
                        if('Object.class.php'==array_pop(explode('\\',$reflectionMethod->getFileName()))){
                            //使用框架的add方法，补全文档参数信息
                            $Comment = "{$ObjectZhName} 保存\r\n";
                            //Field字段名称，Design字段配置
                            foreach (self::parseFieldsConfig($Class->main,$Class->saveFields) as $Field=>$Design){
                                if($Field==$Class->pk)continue;
                                $Comment .= "@param {$Design['DataType']} {$Design['Code']} {$Design['Name']} {$Design['Comment']}\r\n";
                            }
                            $Comment .= "@memo 无\r\n";
                            $Comment .= "@return bool|{$Class->main}\r\n";
                        }else{
                            //使用自定义的add方法，读取自定义的参数信息
                            $Comment = $reflectionMethod->getDocComment();
                        }
                        $methods['save']=array_merge([
                            'name'=>'save','access'=>'public','static'=>false,'Comment'=>$Comment
                        ],$this->parseDocComment($Comment));
                    }
                    break;
                case 'get':
                    $PKConfig = self::parseFieldsConfig($Class->main,$Class->pk)[$Class->pk];
                    $methods['get']=array_merge([
                        'name'=>'get','access'=>'public','static'=>false,'Comment'=>$Comment
                    ],$this->parseDocComment("获取一个 {$ObjectZhName} 对象\r\n@param int \${$Class->pk} {$PKConfig['Name']} {$PKConfig['Comment']}\r\n@param array $Properties 限定取哪些属性 \r\n@return Object"));
                    break;
                case 'gets':
                    $PKConfig = self::parseFieldsConfig($Class->main,$Class->pk)[$Class->pk];
                    $methods['gets']=array_merge([
                        'name'=>'gets','access'=>'public','static'=>false,'Comment'=>$Comment
                    ],$this->parseDocComment("获取 {$ObjectZhName} 对象列表\r\n@param int \${$Class->pk}s {$PKConfig['Name']} {$PKConfig['Comment']}\r\n@param array \$Properties 限定取哪些属性 \r\n@return Object"));
                    break;
                case 'search':
                    $Comment = "按条件搜索 {$ObjectZhName} 对象信息\r\n";
                    $Memo = '';
                    if($Class->searchFields){
                        $Fields = is_array($Class->searchFields)?implode(',',$Fields):$Class->searchFields;
                        $Comment .= "@param string \$Keyword 模糊查询关键字 允许以下字段在{$Class->searchTable}中参与查询:{$Fields}\r\n";
                    }
                    //W参数注释生成
                    if($Class->searchWFieldsConf&&$Class->searchWFieldsGroup){
                        $Comment .= "@param array \$W 精确查找条件 允许备注中的字段参与精确查询\r\n";
                        $Memo.="以下是W参数的限定描述：\r\n";
                        foreach ($Class->searchWFieldsGroup as $GroupName=>$GroupFields){
                            $Memo .= "允许{$Class->addFieldsConfig[$GroupName]}中的".implode(',',$GroupFields)."参与查询\r\n";
                        }
                    }
                    $Comment.="@param int \$P 页码\r\n@param int \$N 每页数量\r\n @param string|array \$Sort 排序字段(暂不支持)\r\n{$Memo}";
                    $methods['search']=array_merge([
                        'name'=>'search','access'=>'public','static'=>false,'Comment'=>$Comment
                    ],$this->parseDocComment($Comment));
                    break;
                default:
                    $access = 'public';
                    if($reflectionMethod->isPrivate()){$access='private';}
                    if($reflectionMethod->isProtected()){$access='protected';}
                    if($reflectionMethod->isPublic()){$access='public';}
//            限定输出的方法范围
                    if(!in_array($access,$MethodsAccess)){continue;}
                    //过滤以下划线开头的操作方法
                    if('_'==substr($MethodName,0,1)){continue;}
                    if($MethodName=='getAll'&&$Class->is_dic===false){continue;}
                    $methods[$MethodName]=array_merge([
                        'name'=>$MethodName,'access'=>$access,'static'=>$reflectionMethod->isStatic(),'Comment'=>$reflectionMethod->getDocComment()
                    ],$this->parseDocComment($reflectionMethod->getDocComment(),$reflectionMethod));
                    break;
            }
        }
        self::$docs['Classes'][$ClassName]['methods']=$methods;
    }

    /**
     *
     * @param ReflectionClass $RefClass
     * @param array $MethodsAccess
     */
    function parseController(ReflectionClass $RefClass,array $MethodsAccess){
        $ClassName = $RefClass->getName();
        self::$docs['Classes'][$ClassName]=array_merge([
            'memo'=>'',
            'zh'=>'',
            'name'=>'',
            'type'=>'Object',//这个类是什么类型，控制器？Model？Object？其他？
            'Properties'=>[],
            'methods'=>[],
            'Object'=>[]
        ],$this->parseDocComment($RefClass->getDocComment(),null,$RefClass));
//        $Class = $RefClass->newInstance();
        self::$docs['Classes'][$RefClass->getName()]=array_merge([
            'memo'=>'',
            'zh'=>'',
            'name'=>'',
            'type'=>'',//这个类是什么类型，控制器？Model？Object？其他？
            'properties'=>[],
            'methods'=>[]
        ],$this->parseDocComment($RefClass->getDocComment(),null,$RefClass));
//        foreach ($RefClass->getProperties() as $property){
//
//        }
        //开始解析方法注释
        $methods = [];
        foreach ($RefClass->getMethods() as $method){
//            $method->isPrivate() or $access =
            $access = 'public';
            if($method->isPrivate()){$access='private';}
            if($method->isProtected()){$access='protected';}
            if($method->isPublic()){$access='public';}
//            限定输出的方法范围
            if(!in_array($access,$MethodsAccess)){continue;}
            $methods[$method->getName()]=array_merge([
                'name'=>$method->getName(),'access'=>$access,'static'=>$method->isStatic()
            ],$this->parseDocComment($method->getDocComment(),$method));
        }
        self::$docs['Classes'][$RefClass->getName()]['methods']=$methods;
    }
    /**
     * @login true
     *
     */
    function renderMD($outputFile='',$templateFile=''){
        $View = new \Tsy\Library\View();
        $View->assign(self::$docs);
        $View->assign('line',"\r\n");
        $content = $View->fetch(is_file($templateFile)?$templateFile:(__DIR__.DIRECTORY_SEPARATOR.'Template'.DIRECTORY_SEPARATOR.'render.html'));
        $content = sql_prefix($content,'');
        if($outputFile){
            file_put_contents($outputFile,$content);
        }
        return $content;
    }
    function renderHTML(){}
    function renderDOC(){}
    function renderUML(){}
    function renderXLS(){}
    function renderCSV(){}
    static function parseFieldsConfig($TableName,$Config){
        $Columns=[];
        if(isset(self::$docs['PDM']['Tables'][parse_name($TableName)])){
            $Columns = self::$docs['PDM']['Tables'][parse_name($TableName)]['Columns'];
        }
        $Fields=[];
        if(is_string($Config)&&$Config){
            $Fields=explode(',',$Config);
        }elseif(is_array($Config)){
            if(true === end($Config)){
                array_pop($Config);
                $Fields = array_diff(array_keys($Columns),$Config);
            }elseif($Config){
                $Fields=$Config;
            }else{
                $Fields=array_keys($Columns);
            }
        }else{
            $Fields=array_keys($Columns);
        }
        $Result=[];
        foreach ($Fields as $field){
            $Result[$field]=$Columns[$field];
        }
        return $Result;
    }

    /**
     * 发布代码，压缩代码并删除注释。
     * @param null $dir
     */
    function publish($dir=null){
//        $RootPath =
//        if(null==$dir){
//            $dir=dirname(APP_PATH).DIRECTORY_SEPARATOR.'Publish';
//        }
//        each_dir(dirname(APP_PATH),function($path)use($dir){
//            if(preg_match('/\\'.DIRECTORY_SEPARATOR.'\./',$path)){
//                return ;
//            }
//            $a=$path;
//        },function($path)use($dir){
//            if(preg_match('/\\'.DIRECTORY_SEPARATOR.'\./',$path)){
//                return ;
//            }
//            $a=$path;
//        });
    }
    static function delPrefix($tableName,$Type=0){
        return parse_name(sql_prefix($tableName,''),$Type);
    }

    /**
     * 自动根据Object中的方法填充到Controller中去
     */
    function autoFinishControllerByObject($Objects=[]){
        if(!is_array($Objects)){
            $Objects=[$Objects];
        }
        //循环遍历
        foreach ($Objects as $object){
            $this->getDoc($object);
        }
        foreach (self::$docs['Classes'] as $Class=>$Info){
            if($Info['type']=='Object'){
                $ControllerName = str_replace('Object','Controller',$Info['name']);
                if(!isset(self::$docs['Classes'][$ControllerName])){
                    $this->getDoc($ControllerName);
                }
                $Controller = self::$docs['Classes'][$ControllerName];
                $str=[];
                foreach (array_diff(array_keys($Info['methods']),array_keys($Controller['methods'])) as $function){
//                    生成字符串
                    $ParamStr=$VerStr=[];

                    foreach ($Info['methods'][$function]['params'] as $ParamName=>$Param){
                        $ParamStr[]="{$Param['name']}".($Param['must']?'':"='{$Param['default']}'");
                        $VerStr[]="{$Param['name']}";
                    }
                    $ParamStr = implode(',',$ParamStr);
                    $VerStr = implode(',',$VerStr);
                    $str[]="    {$Info['methods'][$function]['Comment']}
    function {$function}({$ParamStr}){
        return \$this->Object->{$function}({$VerStr});
    }";
                }
                $str = implode("\r\n",$str);
                //寻找文件路径
                $ControllerPath = APP_PATH.DIRECTORY_SEPARATOR.str_replace("\\",DIRECTORY_SEPARATOR,$ControllerName).'.class.php';
                if(file_exists($ControllerPath)){
                    $content = file_get_contents($ControllerPath);
                    $ControllerClass = new $ControllerName();
                    $Reflection = new ReflectionClass($ControllerClass);
                    $line = $Reflection->getEndLine();
//                    $content = explode("\r\n",$content);
                    $content = preg_replace('/}$/',"{$str}\r\n}",$content);
                    file_put_contents($ControllerPath,$content);
                    //把line行后的全部单独保存，然后删除掉，拆分成两个数组
//                    list($before,$after) = array_chunk($content,$line);
//                    $content = implode("\r\n",array_merge($before,$str,$after));
                }
            }
        }
    }
}