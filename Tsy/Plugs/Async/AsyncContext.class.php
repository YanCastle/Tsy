<?php
/**
 * Copyright (c) 2016. Lorem ipsum dolor sit amet, consectetur adipiscing elit. 
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan. 
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna. 
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus. 
 * Vestibulum commodo. Ut rhoncus gravida arcu. 
 */

namespace Tsy\Plugs\Async;
/**
 * AsyncContext.class.php
 * @author fang
 * @date 2015-11-5
 */
class AsyncContext {
	protected $data = array ();
	public function set($k, $v) {
		$this->data [$k] = $v;
	}
	public function merge($data){
		if(is_array($data)){
			$this->data = array_merge($this->data, $data);
		}elseif($data instanceof AsyncContext){
			$this->data = array_merge($this->data, $data->data);
		}
	}
	public function get($k) {
		return $this->data [$k];
	}
	public function getAll() {
		return $this->data;
	}
}