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
 * Future.class.php
 * @author fang
 * @date 2015-11-5
 */
class Future implements FutureIntf {
	protected $callback;
	public function __construct($callback) {
		$this->callback = $callback;
	}
	public function run(Async &$promise,$content) {
		$cb = $this->callback;
		return $cb ( $promise ,$content);
	}
}