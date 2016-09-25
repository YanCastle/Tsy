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
 * ResponseFuture.class.php
 * @author fang
 * @date 2015-11-5
 */
class ResponseFuture implements FutureIntf {
	protected $response;
	
	public function __construct($response){
		$this->response = $response;
	}
	
	public function run(Async &$promise,$content) {
		$data = json_encode($promise->getData());
		$this->response->end($data);
		//echo "Mem: ",\memory_get_usage() / 1024,"k \n";
		$promise->accept ();
	}
}