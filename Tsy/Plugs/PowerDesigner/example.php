<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/21
 * Time: 16:41
 */
$Power=new \Tsy\Plugs\PowerDesigner\PowerDesigner();
$Params=$Power->get('C:\Users\ghost\Desktop\1.pdm');
return $Params;
//get函数中传入pdm文件路径
//params中有Controller。Model和Object的参数信息