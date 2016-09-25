<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/11
 * Time: 23:09
 */

namespace Application\Controller;

use Application\Object\ClientObject;
use Tsy\Library\Controller;
use Tsy\Library\Storage;
use Tsy\Plugs\WebQQ\WebQQ;

class IndexController extends Controller
{
    function index(){
        //如果这儿是return的字符串，则会作为错误信息返回
        //如果return的数组则是有效消息
//        $WebQQ = new WebQQ(RUNTIME_PATH.DIRECTORY_SEPARATOR.'qr.png',490523604);
//        if(!$WebQQ->autoLogin()){
//            $WebQQ->downQrcode();
//            $NickName = $WebQQ->login();
//            while (!$NickName){
//                $NickName = $WebQQ->login();
//            }
//        }
//        $WebQQ->init();
//        while (true){
//            $value=$WebQQ->poll();
////            sleep(2);
//            echo json_encode($value,JSON_UNESCAPED_UNICODE);
//        }
//        $o = O('Client');
        
//        $Client = new ClientObject();
//        return $Client->search('8',[
////            'SN'=>['EQ','B9C63246BEBEBA233E9C0F08D56AA0C8'],
//            'Printer.PrinterID'=>26,
//            'Printer.FullName'=>['LIKE',"%4725%"],
//            'Client.PrinterClientID'=>4,
//            'Client.SN'=>['LIKE',"%B9C6%"]
//        ]);
//        Storage::connect('UCloud');
//        each_dir('I:\UCloud\MDEditor',null,function($path){
//            Storage::put(str_replace(['I:\UCloud\MDEditor\\','\\'],['','/'],$path),file_get_contents($path));
//            echo $path;
//        });
        M('user')->find();
//        Storage::put('index.html','haha');
    }
    /**
     * 空操作
     * @param string $Action 方法名称
     * @param array|string $Data 数据
     */
    function _empty($Action,$Data){}
    function sleep(){
        sleep(10);
        return 'out';
    }
    function check(){
        return 'sds';
    }
}