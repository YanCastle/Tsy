<?php
/**
 * Created by PhpStorm.
 * User=> Castle
 * Date: 2016/05/16
 * Time: 11:56
 */

namespace Tsy\Plugs\PowerDesigner;

//use Tsy\Plugs\PowerDesigner\Pdm;

class PowerDesigner
{
    /**
     * 入口函数，获取Controller，Model，Object参数
     * @return array
     */
    function get($FilePath){
        $Pdm=new Pdm();
        $Pdm->load($FilePath);
        $Tables=$Pdm->json['Tables'];
        $ModelAndController=$this->modelGet($Tables);
        $Data=[];
        foreach($Tables as $Name=>$Table){
            foreach($Table['Columns'] as $Column ){
                if(true==$Column['P']){
                    $pk=$Column['Code'];
                }
            }
            $Comment=$this->getComment($Table['Comment']);
            $main=str_replace(' ','',ucwords(str_replace('_',' ',substr($Table['Code'],9))));
//            不能用序列号和json，冒号读取不出来
            $Data[$main.'Object']=['main'=>$main,'pk'=>$pk,'property'=>$Comment['property'],'link'=>$Comment['link']];
        }
        return ['Controller'=>$ModelAndController['Controller'],'Model'=>$ModelAndController['Model'],'Object'=>$Data];
    }

    /**
     * comment的解析
     * @param $Comment
     * @return array
     */
    function getComment($Comment){
        $LAndP=explode('|',$Comment);
        $Links=explode(',',$LAndP[1]);
        $Propertys=explode(',',$LAndP[0]);
        unset($Propertys[0]);
        unset($Links[0]);
        $P=[];
        foreach($Propertys as $Property){
            $Tables=explode('&amp;',$Property);
            $P[$Tables[0]]=[
                'self::RELATION_TABLE_NAME'=>$Tables[1],
                'self::RELATION_TABLE_COLUMN'=>$Tables[2],
                'self::RELATION_TABLE_PROPERTY'=>'self::PROPERTY_'.$Tables[3]
            ];
        }
        unset($Tables);
        $L=[];
        foreach($Links as $Link){
            $LinkTables=explode('#',$Link);
            $Tables=explode('&amp;',$LinkTables[0]);
            $L[$Tables[0]]=[
                'self::RELATION_TABLE_NAME'=>$Tables[1],
                'self::RELATION_TABLE_COLUMN'=>$Tables[2],
                'self::RELATION_TABLE_LINK_HAS_PROPERTY'=>$Tables[3],
            ];
            unset($LinkTables[0]);
            foreach($LinkTables as $LinkTable){
                $LinkMsgs=explode('&amp;',$LinkTable);
                $L[$Tables[0]]['self::RELATION_TABLE_LINK_TABLES'][$LinkMsgs[0]]=['self::RELATION_TABLE_COLUMN'=>$LinkMsgs[1]];
            }
        }
        $Data=['property'=>$P,'link'=>$L];
        return $Data;
    }

    /**
     * 获取model生成参数,还有Controller的名称
     */
    function modelGet($Tables){
        $ModelName=[];
        $ControllerName=[];
        foreach($Tables as $Table){
            //表明转化为model名称
            $Name=str_replace('_',' ',substr($Table['Code'],9));
            $ModelName[]=str_replace(' ','',ucwords($Name.' model')).'.class.php';
            $ControllerName[]=str_replace(' ','',ucwords($Name.' Controller')).'.class.php';
        }
        return ['Model'=>$ModelName,'Controller'=>$ControllerName];
    }
    static function analysis($File){
        if(!is_file($File)){
            return false;
        }
        $Pdm = new Pdm();
        if($Pdm->load($File)){
            return $Pdm->json;
        }
        return false;
    }

    /**
     * 获取前端需要的JSON结构
     * @param $File
     * @return mixed
     */
    static function getObjectJson($File,$OutPut,$ModuleName){
        $JSON = self::analysis($File);
        $Obj = [];
        foreach ($JSON['Tables'] as $TableName=>$Table){
            $Columns=[];
            $I='';
            foreach ($Table['Columns'] as $Column){
                if($Column['I']){
                    $I=$Column['Code'];
                }
                $Columns[]=[
                        "Name"=> $Column['Name'],
                        "Code"=> $Column['Code'],
                        "Comment"=> $Column['Comment'],
                        "DataType"=> $Column['DataType'],
                        "Length"=> [
                                "11"
                            ],
                        "Must"=> $Column['M'],
                        "Default"=> $Column['DefaultValue']?'':$Column['DefaultValue'],
                        "Editable"=> false,
                        "Hidden"=> false,
                        "GetBy"=> false,
                        "SearchBy"=> false,
                        "RegExp"=> ""
                ];
            }
            $Obj[]=[
                "Name"=> $Table['Name'],
                "Code"=> $Table['Code'],
                "Comment"=> $Table['Comment'],
                "I"=> $I,
                'ModuleName'=>$ModuleName,
                "Columns"=>$Columns,
            ];
        }
//        $Obj['obj']=$Obj;
        file_put_contents($OutPut,str_replace(['{$PREFIX}','prefix_'],'',json_encode(['obj'=>$Obj],JSON_UNESCAPED_UNICODE)));
    }
}