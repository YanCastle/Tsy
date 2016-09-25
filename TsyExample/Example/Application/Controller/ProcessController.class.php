<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 5/7/16
 * Time: 2:58 PM
 */

namespace Application\Controller;


use Tsy\Library\Controller;

class ProcessController extends Controller
{
//    json {"to":5,"m":"ss"}
    function a($to,$m){
//        pipe_message($to,$m);
        swoole_get_process_type();
    }
}