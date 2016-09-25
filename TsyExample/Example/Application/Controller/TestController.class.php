<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/13
 * Time: 21:23
 */

namespace Application\Controller;


use Tsy\Library\Controller;

class TestController extends Controller
{
    function test(){
        return [
            's'=>3
        ];
    }
}