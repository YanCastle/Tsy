<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/13
 * Time: 21:11
 */

function out($data){
    return json_encode([
        'm'=>$_POST['_mid'],
        'd'=>is_array($data)?$data:['err'=>$data],
        'i'=>$_POST['_i'],
        'c'=>'m',
        's'=>session('[id]'),
        'UID'=>session('UID')
    ]);
}
function dispatch($data){
    $d = json_decode($data,true);
    session('[id]',isset($d['s'])?$d['s']:str_replace('.','',uniqid()));
    return [
        'i'=>isset($d['i'])?$d['i']:'Empty/_empty',
        'd'=>isset($d['d'])?$d['d']:[],
        't'=>isset($d['t'])?$d['t']:uniqid()
    ];
}
function bridge($data){
    return json_encode([
        't'=>$data['t'],
        'm'=>$_POST['_mid'],
        'c'=>'t'
    ]);
}