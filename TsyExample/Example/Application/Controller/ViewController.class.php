<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2016/6/29
 * Time: 19:30
 */

namespace Application\Controller;


use Tsy\Library\Controller;

/**
 * 视图测试类
 * Class ViewController
 * @package Application\Controller
 */
class ViewController extends Controller
{
    /**
     * 视图测试方法
     */
    function view(){
        $Document = new \Document();
        $Document->getDoc('Application\Controller\ViewController');
        echo $Document->renderMD();
    }
}