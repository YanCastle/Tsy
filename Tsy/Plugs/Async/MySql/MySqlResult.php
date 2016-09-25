<?php
/**
 * Copyright (c) 2016. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

/**
 * Created by PhpStorm.
 * User: castle
 * Date: 6/10/16
 * Time: 1:49 PM
 */

namespace Tsy\Plugs\Async\MySql;


class MySqlResult
{
    public $Result=[];
    public $InsertID=-1;
    public $Rows=0;
    public $SQL='';
    function __construct($SQL,$Result=[],$InsertID=-1,$Rows=0)
    {
        $this->SQL=$SQL;
        $this->Result=$Result;
        $this->InsertID=$InsertID;
        $this->Rows=$Rows;
    }
}