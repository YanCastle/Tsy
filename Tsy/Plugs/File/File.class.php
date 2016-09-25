<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 5/31/16
 * Time: 4:52 PM
 */

namespace Tsy\Plugs;


class File
{
    static function eachDir(){}

    /**
     * 删除文件
     */
    static function rm(){}

    /**
     * 移动文件，改名
     */
    static function mv(){}

    /**
     * 写入文件，自带目录存在性检测
     * @param string $path 文件目录
     * @param string $content 写入内容
     * @param bool|false $append 是否追加写入
     */
    static function write($path,$content,$append=false){
        if(!is_dir($path)&&!self::mk_dir($path)){
            return false;
        }
        if($append){
            $fp = fopen($path,'w+');
            $writes = fwrite($fp,$content);
            fclose($fp);
            return !!$writes;
        }else{
            return !!file_put_contents($path,$content);
        }
    }
    static function mk_dir($path){
        if(is_dir($path)){return true;}
        if(is_file($path)){return false;}
        return mkdir($path,0777,true);
    }
    /**
     * 上传文件
     * @link http://document.thinkphp.cn/manual_3_2.html#upload
     */
    static function upload($TPUploadConfig=[]){
//        foreach(explode(',','mimes,maxSize,exts,autoSub,subName,rootPath,savePath,saveName,saveExt,replace,hash,callback,driver,driverConfig') as $config){
//            if(!isset($TPUploadConfig[$config])){
//                $c = C('UPLOAD_'.strtoupper($config),null,'');
//                if($c){
//                    $TPUploadConfig[$config]=$c;
//                }
//            }
//        }
//        $Upload = new Upload($TPUploadConfig);
//        $infos = $Upload->upload();
//        if($infos){
//            $Model = M('Upload');
//            $Rs = [];
//            foreach($infos as $info){
//                $data = [
//                    'FileName'=>$info['name'],
//                    'Extension'=>$info['ext'],
//                    'MIME'=>$info['type'],
//                    'Size'=>$info['size'],
//                    'SaveName'=>$info['savename'],
//                    'SavePath'=>$info['savepath'],
//                    'FileMd5'=>$info['md5'],
//                    'UploadTime'=>time(),
//                    'UploaderUID'=>session('UID')
//                ];
//                $UploadID = $Model->add($data);
//                if($UploadID){
//                    $data['UploadID']=$UploadID;
//                    $Rs[]=$data;
//                }else{
//                    return false;
//                }
//            }
//            return $Rs;
//        }else{
//            //失败
//            return $Upload->getError();
//        }
    }

    /**
     * 下载文件
     */
    static function download($file){}

    /**
     * 罗列文件
     */
    static function ls($dir){}

    /**
     * zip压缩
     */
    static function zip(){}

    /**
     * zip解压
     */
    static function unzip(){}
}