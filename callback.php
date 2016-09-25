<?php
//定义一个trait
trait 需要复用的方法{
    function 复用方法1(){
        echo 'trait ',__METHOD__,"\r\n";
    }
    function 复用方法2(){
        echo 'trait ',__METHOD__,"\r\n";
    }
}
//定义一个父类
class 父类{
    function 方法1(){
        echo '父类 ',__METHOD__,"\r\n";
    }
    function 复用方法1(){
        echo '父类 ',__METHOD__,"\r\n";
    }
    function 复用方法3(){
        echo '父类 ',__METHOD__,"\r\n";
    }
}
//定义一个类来引用
class 类 extends 父类{
    use 需要复用的方法;
    function 复用方法1()
    {
        echo '子类 ',__METHOD__,"\r\n";
    }
    function 复用方法3():父类
    {
        echo '子类 ',__METHOD__,"\r\n";
    }
}

$实例化的类 = new 类();
$实例化的类->复用方法1();
$实例化的类->复用方法2();
$实例化的类->复用方法3();
$实例化的类->方法1();

echo '该类具有的方法有：',"\r\n";
$反射类 = new ReflectionClass($实例化的类);
foreach ($反射类->getMethods() as $method){
    echo $method->getName(),"\r\n";
}