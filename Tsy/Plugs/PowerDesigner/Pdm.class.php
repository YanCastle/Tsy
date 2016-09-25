<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2016/03/09
 * Time: 21:53
 */

namespace Tsy\Plugs\PowerDesigner;


class Pdm
{
    const FK='FK';
    const TABLE='TABLE';
    const COLUMN='COLUMN';
    const DOMAIN='DOMAIN';
    private $pq;
    private $Domain;
    public $json=[];
    private $IDMap=[];
    function load($file){
        if(file_exists($file)) {
            $xml = file_get_contents($file);
            $xml = str_replace(':', '', $xml);
            $xml = str_replace('Column.Mandatory', 'ColumnMandatory', $xml);
            vendor('phpQuery.phpQuery');
            $this->pq=\phpQuery::newDocument($xml);
            $this->getDomains();
            $ProjectInfo = $this->getProject();
            $TableInfo = $this->getTables();
            $json['Project'] = $ProjectInfo;
            $json['Tables'] = $TableInfo;
            $json['Domains'] = $this->Domain;
            $json['ForeignKeys']=$this->getForeignKeys();
            foreach ($json['ForeignKeys'] as $ID=>$FK){
                $ParentTableCode=$this->IDMap[$FK['ParentTableID']]['Code'];
                $ChildTableCode=$this->IDMap[$FK['ChildTableID']]['Code'];
                $ParentColumnCode=$this->IDMap[$FK['ParentColumnID']]['Code'];
                $ChildColumnCode=$this->IDMap[$FK['ChildColumnID']]['Code'];
                $FKProperty=[
                    'ParentTable'=>$json['Tables'][$ParentTableCode],
                    'ParentTableCode'=>$ParentTableCode,
                    'ParentTableColumnCode'=>$ParentColumnCode,
                    'ChildTable'=>$json['Tables'][$ChildTableCode],
                    'ChildTableCode'=>$ChildTableCode,
                    'ChildTableColumnCode'=>$ChildColumnCode,
                    'Properties'=>$FK
                ];
                $json['Tables'][$ParentTableCode]['FKs']['Parent'][]=array_merge($FKProperty,['Type'=>'Parent']);
                $json['Tables'][$ChildTableCode]['FKs']['Child'][]=array_merge($FKProperty,['Type'=>'Child']);
            }
            $this->json=$json;
            return true;
        }
        return false;
    }

    /**
     * 获取项目信息
     * @return array|bool
     */
    public function getProject(){
        $ProjectInfo=[
            'Name'=>pq('oModel aName:first')->html(),
            'Comment'=>pq('oModel aComment:first')->html(),
        ];
        return $ProjectInfo['Name']?$ProjectInfo:false;
    }

    /**
     * 获取表信息
     * @return array
     */
    public function getTables(){
        $Tables=[];
        foreach(pq('cTables oTable') as $oTable){
            $Table=[
                'ID'=>pq($oTable)->attr('Id'),
                'Name'=>pq($oTable)->find('aName:first')->html(),
                'Code'=>pq($oTable)->find('aCode:first')->html(),
                'Comment'=>pq($oTable)->find('aComment:first')->html(),
                'FKs'=>[
                    'Parent'=>[],//此表为父表时
                    'Child'=>[]//此表为子表时
                ]
            ];
            $Columns=[];
            $PK = pq($oTable)->find('cPrimaryKey oKey')->attr('Ref');
            $PK = pq($oTable)->find("[Id={$PK}]")->find('oColumn')->attr('Ref');
            $PKColumn='';
            foreach(pq($oTable)->find('oColumn') as $oColumn){
                $ColumnID=pq($oColumn)->attr('Id');
                if(null===$ColumnID){
                    $html = pq($oColumn)->html();
                    continue;
                }
                $Code = pq($oColumn)->find('aCode')->html();
                $Column=[
                    'Name'=>pq($oColumn)->find('aName')->html(),
                    'Code'=>$Code,
                    'Comment'=>pq($oColumn)->find('aComment')->html(),
                    'DataType'=>pq($oColumn)->find('aDataType')->html(),
                    'I'=>pq($oColumn)->find('aIdentity')->html()==1,
                    'M'=>pq($oColumn)->find('aColumnMandatory')->html(),
                    'ID'=>$ColumnID,
                    'P'=>$PK?$PK==$ColumnID:0,
                    'DomainID'=>pq($oColumn)->find('cDomain oPhysicalDomain')->attr('Ref'),
                    'DefaultValue'=>pq($oColumn)->find('aDefaultValue')->html(),
                ];
                $Column['DefaultValue']=$Column['DefaultValue']==false?"":$Column['DefaultValue'];
                $Columns[$Code]=$Column;
                if($PK&&$PK==$ColumnID){
                    $PKColumn=$Code;
                }
                $this->IDMap[$ColumnID]=array_merge($Column,['Type'=>self::COLUMN]);
            }
            $Table['Columns']=$Columns;
            $Table['PK']=$PKColumn;
            $Tables[$Table['Code']]=$Table;
            $this->IDMap[$Table['ID']]=array_merge($Table,['Type'=>self::TABLE]);
        }
        return $Tables;
    }

    /**
     * 获取
     */
    public function getDomains(){
        foreach(pq('cDomains oPhysicalDomain') as $oPhysicalDomain){
            $Domain = [
                'ID'=>pq($oPhysicalDomain)->attr('Id'),
                'Name'=>pq($oPhysicalDomain)->find('aName:first')->html(),
                'Code'=>pq($oPhysicalDomain)->find('aCode:first')->html(),
                'Comment'=>pq($oPhysicalDomain)->find('aComment:first')->html(),
                'DataType'=>pq($oPhysicalDomain)->find('aDataType:first')->html(),
            ];
            $this->Domain[$Domain['ID']]=$Domain;
            $this->IDMap[$Domain['ID']]=array_merge($Domain,['Type'=>self::DOMAIN]);
        }
    }
    public function getForeignKeys(){
        $References=[];
        foreach(pq('cReferences oReference') as $oReference){
            $ID=pq($oReference)->attr('Id');
            $Reference = [
                'ID'=>$ID,
                'Name'=>pq($oReference)->find('aName:first')->html(),
                'Code'=>pq($oReference)->find('aCode:first')->html(),
                'Comment'=>pq($oReference)->find('aComment:first')->html(),
                'ParentTableID'=>pq($oReference)->find('cParentTable oTable')->attr('Ref'),
                'ChildTableID'=>pq($oReference)->find('cChildTable oTable')->attr('Ref'),
                'ParentColumnID'=>pq($oReference)->find('cJoins oReferenceJoin cObject1 oColumn')->attr('Ref'),
                'ChildColumnID'=>pq($oReference)->find('cJoins oReferenceJoin cObject2 oColumn')->attr('Ref'),
            ];
            $References[]=$Reference;
            $this->IDMap[$ID]=array_merge($Reference,['Type'=>self::FK]);
//            $this->Reference[$Domain['ID']]=$Domain;
        }
        return $References;
//        $a=1;
    }
}