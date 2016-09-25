<?php
/**
 * 创建数据库、获取字段结构等数据库操作方法
 */
namespace Tsy\Plugs\Db;

use Tsy\Library\Model;

class Db{
    static $BACKUP_TYPE_FILE='file';//备份到文件
    static $BACKUP_TYPE_RETURN='content';//返回数据库内容
    public $Model;
    public $tablePrefix='';
    public $db_name;
    public $tables=[];
    public $views=[];
    function __construct($name='',$tablePrefix='',$connection='',$db_name=''){
        $this->Model=new Model($name,$tablePrefix,$connection);
        $this->tablePrefix=$tablePrefix?$tablePrefix:C('DB_PREFIX');
        $this->db_name=$db_name?$db_name:C('DB_NAME');
    }

    /**
     * 获取表
     * @param string $db_prefix
     * @return mixed
     */
    function getTableList($db_prefix='',$no_prefix=false){
        if($this->tablePrefix&&!$db_prefix){$db_prefix=$this->tablePrefix;}
        $tables = $this->Model->query('SHOW TABLES WHERE tables_in_' . $this->db_name . ' like "' . $db_prefix . '%"');
        foreach($tables as $table){
            $this->tables[]=$no_prefix?str_replace($this->tablePrefix,'',array_values($table)[0]):array_values($table)[0];
        }
        return $this->tables;
    }

    /**
     * 获取视图
     * @param string $db_prefix
     * @return mixed
     */
    function getViewList($db_prefix='',$no_prefix=false){
        if($this->tablePrefix&&!$db_prefix){$db_prefix=$this->tablePrefix;}
        $views = $this->Model->query('SHOW VIEWS WHERE tables_in_' . $this->db_name . ' like "' . $db_prefix . '%"');
        foreach($views as $view){
            $this->views[]=$no_prefix?str_replace($this->tablePrefix,'',$view["tables_in_{$this->db_name}"]):$view["tables_in_{$this->db_name}"];
        }
        return $this->views;
    }

    /**
     * 检查表或者视图是否存在
     * @param $TableName
     * @param string $db_prefix
     * @return bool
     */
    function existTable($TableName,$db_prefix=''){
        if($this->tablePrefix){$db_prefix=$this->tablePrefix;}
        $tables = $this->Model->query('SHOW TABLES WHERE tables_in_' . $this->db_name . ' = "' . $db_prefix.$TableName . '"');
        $views = $this->Model->query('SHOW VIEWS WHERE tables_in_' . $this->db_name . ' like "' . $db_prefix . '%"');
        return $tables||$views;
    }

    /**
     * 执行SQL导入文件
     * @param Model $Model
     * @param string $file
     * @param string $content
     * @param string $db_prefix
     * @return bool
     */
    static function build(Model $Model,$file='',$content='',$db_prefix=''){
        if($db_prefix==''){$db_prefix=C('DB_PREFIX');}
        if(!$Model instanceof Model){return false;}
        if($file){
            if(file_exists($file)&&is_readable($file)){
                $content = file_get_contents($file);
            }else{
                return false;
            }
        }elseif($content){

        }else{
            return false;
        }
        if($content){
            $content = preg_replace('/\/\*.+\*\/\r\n/','',$content);
            $content = sql_prefix($content,$db_prefix);
            $Sqls = explode(";",$content);
            if(is_array($Sqls)&&count($Sqls)>0){
                $Model->startTrans();
                try{
                    foreach($Sqls as $sql){
                        if($sql)
                            $Model->execute($sql);
                    }
                }catch (\Exception $e){
                    $Model->rollback();
                    return false;
                }
                $Model->commit();
                return true;
            }
        }else{
            return false;
        }
        return true;
    }
    function backup($type,$file=false,array $tables=[]){

    }

    /**
     * 获取表的字段信息
     * @param array $tables
     * @param bool $prefix
     * @return array|mixed
     */
    function getColumns($tables=[],$prefix=false,$cache=APP_DEBUG){
        $one = false;
        if(is_string($tables)){
            $tables=[$tables];
            $one=true;
        }
        if(!$tables){
            $tables=$this->getTableList();
        }else{
            if(false===$prefix){
                //不需要加前缀
                $prefix='';
            }elseif(true===$prefix){
//                从当前环境中添加前缀
                $prefix=C('DB_PREFIX');
            }elseif(is_string($prefix)){
//                设置前缀为
//                $prefix=$prefix;
            }else{
                $prefix='';
            }
        }
        $TableColumns=[];
        //是否强制刷新
        if($cache){
            foreach ($tables as $table){
                if($CacheColumns = cache('ColumnsCache'.$prefix.$table)){
                    $TableColumns[$table]=$CacheColumns;
                }else{
                    $Columns = $this->Model->query("SHOW columns From {$prefix}{$table}");
                    if($Columns){
                        $TableColumns[$table]=$Columns;
                    }
                    cache('ColumnsCache'.$prefix.$table,$Columns);
                }
            }
        }else{
            foreach($tables as $table){
                $Columns = $this->Model->query("SHOW columns From {$prefix}{$table}");
                if($Columns){
                    $TableColumns[$table]=$Columns;
                }
                cache('ColumnsCache'.$prefix.$table,$Columns);
            }
        }
        return $one?$TableColumns[$tables[0]]:$TableColumns;
    }
}