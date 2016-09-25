<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/12
 * Time: 15:30
 */

namespace Tsy\Library;

/**
 * 驱动模式下的Session处理
 * Class Session
 * @package Tsy\Library
 */
class Session implements \SessionHandlerInterface
{
    public function close(){}
    public function create_sid(){}
    public function destroy($session_id ){}
    public function gc($maxlifetime ){}
    public function open($save_path ,  $session_name ){}
    public function read($session_id ){}
    public function write($session_id ,  $session_data ){}
}