<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 6/9/16
 * Time: 11:08 PM
 */

namespace Application\Controller;


use Tsy\Library\Controller;
use Tsy\Plugs\Async\Async;
use Tsy\Plugs\Async\AsyncContext;
use Tsy\Plugs\Async\HttpClientFuture;

class AsyncController extends Controller
{
    function async(){
        Async::create(new HttpClientFuture('https://www.baidu.com/'))->then([$this,'a'])->start(new AsyncContext());
    }
    function e(&$promise,$data){
        echo 'e';
        return $promise;
    }
    function a(&$promise,$data){
        var_dump($data);
        return $promise;
    }
}