<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2015/11/24
 * Time: 22:39
 */
$Excel = new \Tsy\Excel\Excel();
//读取某个Excel文件的内容
$data = $Excel->read('a.xls');
//写入到某个excel文件
$Excel->write('b.xlsx',$data);
//读取template.xlsx 并按照里面配置的模板渲染 支持 竖向渲染，支持两级渲染
$Excel->template('template.xlsx','save.xls',[
    'A'=>'15',
    'B'=>'fwew',
    'C'=>['A'=>'awfawf'],
    'D'=>[
        ['LN'=>1,'SiteName'=>'SiteNfame1'],
        ['LN'=>2,'SiteName'=>'SiteNaame1'],
        ['LN'=>3,'SiteName'=>'SiteNfwame1'],
        ['LN'=>4,'SiteName'=>'SiteNafwme1'],
        ['LN'=>5,'SiteName'=>'SiteNafwme1'],
        ['LN'=>6,'SiteName'=>'SiteNafawefme1'],
    ],
    'F'=>[
        ['LN'=>1,'SiteName'=>'SiteNfame1'],
        ['LN'=>2,'SiteName'=>'SiteNaame1'],
        ['LN'=>3,'SiteName'=>'SiteNfwame1'],
        ['LN'=>4,'SiteName'=>'SiteNafwme1'],
        ['LN'=>5,'SiteName'=>'SiteNafwme1'],
        ['LN'=>6,'SiteName'=>'SiteNafawefme1'],
    ]
]);


