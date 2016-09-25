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
 * Date: 7/1/16
 * Time: 11:21 AM
 */

namespace Tsy\Library;


class Task
{
    public $cmd;
    public $data;
    public $sid;
    function __construct($cmd,$data)
    {
        $this->cmd=$cmd;
        $this->data=$data;
        $this->sid=session('[id]');
    }
}