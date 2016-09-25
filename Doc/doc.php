<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 6/9/16
 * Time: 5:39 PM
 */
function create_doc(){
    $Extension = new ReflectionExtension('swoole');
    foreach ($Extension->getClasses() as $class){
        if($class->getName()=='swoole_http_client'){
            foreach ($class->getMethods() as $method){
                $doc = $method->getDocComment();
                foreach ($method->getParameters() as $parameter){
                    $p = 1;
                }
            }
        }
    }
}
create_doc();