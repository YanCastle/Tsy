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
 * Date: 6/24/16
 * Time: 10:41 PM
 */

/**
 * 文件监控，需要基于Inotify扩展
 * Class Inotify
 */
class Inotify
{
    protected $inotify;
    protected $watched=[];
    protected $wd=[];
    function __construct()
    {
        $this->inotify = inotify_init();
    }

    function watch($Dir){
        $Dir=realpath($Dir);
        if(is_dir($Dir)||is_file($Dir)){
            if(!in_array($Dir, $this->wd)){
                $this->watched[]=$Dir;
                $this->wd[]=$Dir;
                inotify_add_watch($this->inotify,$Dir,IN_MODIFY|IN_CREATE|IN_DELETE|IN_MOVED_FROM|IN_MOVED_TO|IN_DELETE_SELF|IN_MOVE_SELF|IN_MOVE);
                if(is_dir($Dir)){
                    $this->each_dir($Dir,[$this,'eachDir']);
                }
            }
            return true;
        }
        return false;
    }
    function eachDir($path){
        if(!in_array($path, $this->wd)&&is_dir($path)) {
            inotify_add_watch($this->inotify, $path, IN_MODIFY | IN_CREATE | IN_DELETE | IN_MOVED_FROM | IN_MOVED_TO | IN_DELETE_SELF | IN_MOVE_SELF | IN_MOVE);
            $this->wd[] = $path;
        }
    }

    /**
     * 目录遍历
     * @param string $dir
     * @param callable|null $dir_callback
     * @param callable|null $file_callback
     */
    function each_dir(string $dir,callable $dir_callback=null,callable $file_callback=null){
        if(is_dir($dir)){
            foreach (scandir($dir) as $path){
                if(!in_array($path, ['.','..'])){
                    $path = $dir.DIRECTORY_SEPARATOR.$path;
                    if(is_dir($path)){
                        if(is_callable($dir_callback)){
                            call_user_func($dir_callback,$path);
                        }
                        $this->each_dir($path, $dir_callback, $file_callback);
                    }else{
                        if(is_callable($file_callback)){
                            call_user_func($file_callback,$path);
                        }
                    }
                }
            }
        }
    }

    /**
     * @param $Dir
     */
    function unwatch($Dir){
        if(in_array($Dir,$this->watched )){
            inotify_rm_watch($this->inotify,$Dir );
        }
    }

    /**
     * 开始监控
     * @param callable $function
     */
    function start(callable $function){
        swoole_event_add($this->inotify,function($fd)use($function){
            $events = inotify_read($fd);
            if($events){
                foreach ($events as $event){
                    //TODO 检测变更类型，回调用户函数
                    $path = $this->wd[$event['wd']-1].DIRECTORY_SEPARATOR.$event['name'];
                    if(is_dir($path)&&!in_array($path,$this->wd)){
                        $this->watch($path);
                    }
                    call_user_func_array($function, [$path,$event['mask']]);
                    //echo var_export($event);
                }
            }
        });
    }
    function stop(){}
}
