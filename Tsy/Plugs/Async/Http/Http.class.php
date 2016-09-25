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
 * Time: 6:35 PM
 */

namespace Tsy\Plugs\Async\Http;


class Http
{
    public $url;
    public $cookie=[];
    public $response_header=[];
    public $request_header=[];
    /**
     * @var \swoole_client $client
     */
    private $client;
    private $UserID;
    private $clients=[];
}