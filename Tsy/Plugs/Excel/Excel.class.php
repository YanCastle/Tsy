<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2015/11/24
 * Time: 19:56
 */
namespace Tsy\Plugs\Excel;

use Tsy\Plugs\Curl\Curl;

class Excel {
    function __construct(){
        if(!class_exists('\PHPExcel'));
            require dirname(__FILE__) . '/PHPExcel/PHPExcel.php';
    }

    /**
     * Excel模板渲染
     * @param $templateFile
     * @param $saveFile
     * @param $ExcelData
     */
    function template($templateFile,$saveFile,$ExcelData,$SendFile=false){
        /**
         * 取得字符串的ASCII和
         */
        function getStrAsciiAnd($str){
            $arr = str_split($str,1);
            $and=0;
            foreach($arr as $a){
                $and+=ord($a);
            }
            return $and;
        }
        //加载xls文件
        $objLoader = \PHPExcel_IOFactory::load($templateFile);
//        取得第一个sheet
        $Sheet = $objLoader->getSheet(0);
//        取得所有有值的序列
        $Collection = $Sheet->getCellCollection();
        $MaxFieldName='A';
        $RowMap=[];$ColumnMap=[];
        foreach($Collection as $co){
            preg_match('/\d+/',$co,$RowNumber);
            $RowNumber=$RowNumber[0];
            preg_match('/[A-Z]+/',$co,$ColumnNumber);
            $ColumnNumber=$ColumnNumber[0];
            if(getStrAsciiAnd($ColumnNumber)>getStrAsciiAnd($MaxFieldName)){$MaxFieldName=$ColumnNumber;}
            if(isset($RowMap[$RowNumber])){$RowMap[$RowNumber][]=$ColumnNumber;}else{$RowMap[$RowNumber]=[$ColumnNumber];}
            if(isset($ColumnMap[$ColumnNumber])){$ColumnMap[$ColumnNumber][]=$RowNumber;}else{$ColumnMap[$ColumnNumber]=[$RowNumber];}
        }
        $TodoLines = [];
        foreach($Collection as $cellIndex){
            preg_match('/\d+/',$cellIndex,$RowNumber);
            $RowNumber=$RowNumber[0];
            preg_match('/[A-Z]+/',$cellIndex,$ColumnNumber);
            $ColumnNumber=$ColumnNumber[0];
            $p = $Sheet->getCell($cellIndex)->getValue();
            if(is_object($p)){
                $p=$p->getPlainText();
            }
            //进入模板处理规则
            preg_match('/[\{\[]\$[A-Za-z\.\d\-\+\*\/]+[\}\]]/',$p,$match);
            if($match){
                preg_match('/\{\$[A-Za-z\.\-\+\*\/]+\}/',$p,$isValue);
                if($isValue){
                    //值填充处理
                    $param = trim(str_replace(['{','$','}'],'',$match[0]));
                    $Val=false;
                    $p = trim(str_replace(['{','$','}'],'',$p));
                    $d = explode('.',$param);
                    foreach($d as $t){
                        $Val=$Val?(isset($Val[$t])?$Val[$t]:false):(isset($ExcelData[$t])?$ExcelData[$t]:false);
                    }
                    $Val = str_replace($param,$Val,$p);
//                    $Sheet->getStyle($cellIndex)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $Sheet->setCellValueExplicit($cellIndex,$Val?$Val:NULL,\PHPExcel_Cell_DataType::TYPE_STRING);
                }else{
                    //行处理
                    $TodoLines[$ColumnNumber.'-'.$RowNumber]=$p;
                }
            }
        }
        $LineNumbers=[];
        $LineMap=[];
        if($TodoLines){
            //取得有多少个行需要进行 行处理
            foreach($TodoLines as $k=>$v){
                list($ColumnNumber,$RowNumber)=explode('-',$k);
//            识别有多少个行重复
                if(!in_array($RowNumber,$LineNumbers)){$LineNumbers[]=$RowNumber;}
//            生成 行-模板 映射关系
                if(!isset($LineMap[$RowNumber])){$LineMap[$RowNumber]=[];}
                $LineMap[$RowNumber][$ColumnNumber]=trim(str_replace(['[$',']'],'',$v));
            }
            //取得该行最大ColumnNumber
            $LineData=[];
            foreach($LineNumbers as $RowNumber){
//            $LineMaxColumn = max($RowMap[$RowNumber]);
                //取得行值填充参数
//            for($i=ord('A');$i<=ord($LineMaxColumn);$i++){
//
//            }
                $LineData[$RowNumber]=[];
                foreach($LineMap[$RowNumber] as $cellIndex=>$template){
                    $index = explode('.',$template);
                    $value=false;
                    //需要生成 A1=>ExcelData[FieldName] 的映射关系以便生成数据
                    if(!isset($ExcelData[$index[0]])||!is_array($ExcelData[$index[0]])){continue;}
                    $d = $ExcelData[$index[0]];
                    $LineData[$RowNumber][$cellIndex]=array_key_value($d,$index[1]);
                }
            }
            $RowAdded=0;
            foreach($LineData as $RowNumber=>$data){
                $RowCount = 0;
                //取得数据长度
                foreach($data as $da){$RowCount=count($da);break;}
                $Sheet->insertNewRowBefore($RowNumber+$RowAdded,$RowCount);
                $RowAddedNumber = $RowAdded?$RowAdded-1:0;
                foreach($data as $ColumnNumber=>$ColumnValue){
                    //循环填充数据
                    $i=0;
                    foreach($ColumnValue as $val){
//                        $Sheet->setCellValue($ColumnNumber.($RowNumber+$RowAddedNumber+$i),$val);
                        $Sheet->setCellValueExplicit($ColumnNumber.($RowNumber+$RowAddedNumber+$i),$val,\PHPExcel_Cell_DataType::TYPE_STRING);
                        $i++;
                    }
                }
                $RowAdded+=$RowCount;
            }
            $Sheet->removeRow($RowNumber+$RowAdded+$RowCount-1);
        }
        $ExcelType = explode('.',$saveFile)[1]=='xls'?'Excel5':'Excel2007';
        $objWriter = \PHPExcel_IOFactory::createWriter($objLoader,$ExcelType);
        if($SendFile){
            $Path = defined('DATA_PATH')?DATA_PATH:'./';
            $Save = $Path.''.uniqid().'.'.explode('.',$saveFile)[1];
            $objWriter->save($Save);
//            TODO 推送下载
            Curl::download($Save,$saveFile);
            unlink($Save);
        }else{
            $objWriter->save($saveFile);
        }
    }

    /**
     * 读取Excel
     * @param $file
     * @param bool|false $FirstIsField
     * @return array
     */
    function read($file,$FirstIsField=false){
        $data = [];
        $info = explode('.',$file);
        $extension = $info[count($info)-1];
        switch(strtoupper($extension)){
            case 'CSV':
//                $content=str_replace("\r\n","\n",iconv('GBK','UTF-8',file_get_contents($file)));
                return array_map(function($v){return explode(',',$v);},explode("\n",str_replace("\r\n","\n",iconv('GBK','UTF-8',file_get_contents($file)))));
                break;
            case 'XLSX':
                $objLoader = \PHPExcel_IOFactory::load($file);
                $Sheets = $objLoader->getAllSheets();
                foreach($Sheets as $Sheet){
                    $data[$Sheet->getTitle()] = $Sheet->toArray();
                }
                return $data;
                break;
            case 'XLS':
                $objLoader = \PHPExcel_IOFactory::load($file);
                $Sheets = $objLoader->getAllSheets();
                foreach($Sheets as $Sheet){
                    $data[$Sheet->getTitle()] = $Sheet->toArray();
                }
                return $data;
                break;
            default:
                return [];
                break;
        }
    }

    /**
     * 写入Excel
     * @param $file
     * @param $data
     * @return bool|string
     *
     $Excel = new Excel();
    $Excel->write('a.xlsx',[
        'Sheet1'=>[
            [1,2,3]
        ]
    ]);
     */
    function write($file,$data){
        if(!$data||!$file||!( file_exists($file)?unlink($file):true )){
            return false;
        }
        $pathinfo = pathinfo($file);
        $path = realpath($pathinfo['dirname']);
        $file=$path.DIRECTORY_SEPARATOR.$pathinfo['basename'];
//    $file = realpath($file);
        $sheet_id = 0;
        //创建excel操作对象
        $objPHPExcel = new \PHPExcel();
        //获得文件属性对象，给下文提供设置资源
        $objPHPExcel->getProperties()->setCreator("绵阳市碳素云信息技术有限责任公司")
            ->setLastModifiedBy("绵阳市碳素云信息技术有限责任公司")
//            ->setTitle("Input_Goods_message")
//            ->setSubject("主题1")
//            ->setDescription("随便一个描述了")
//            ->setKeywords("关键字 用空格分开")
            ->setCategory("分类 ");
        for($i=1;$i<count($data);$i++){
            $objPHPExcel->addSheet(new \PHPExcel_Worksheet($objPHPExcel,'sheet'.$i));
        }
        foreach($data as $sheetName => $sheetData){
            $Sheet = $objPHPExcel->setActiveSheetIndex($sheet_id);
            $Sheet->setTitle($sheetName);
            $Sheet->fromArray($sheetData);
            $sheet_id++;
        }
        try{
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, explode('.',$file)[1]=='xlsx'?'Excel2007':'Excel5');
            $objWriter->save($file);
        }catch (\Exception $e){
            return false;
        }
        return $file;
    }

    /**
     * 读取Excel文件并转化成数组
     * @param $file
     * @return array|bool
     */
    static function csvRead($file="",$content="",$charset='GBK'){
        if(!$file&&!$content){return false;}
        if(file_exists($file)&&is_readable($file)){
            $content = $content?$content:file_get_contents($file);
        }
        if($content){
            $content = str_replace("\r\n","\n",$charset=='GBK'?iconv('GBK','UTF-8',$content):$content);
            $content = trim($content,"\n");
            return array_map(function($v){
                return explode(',',$v);
            },explode("\n",$content));
        }
        return false;
    }
}