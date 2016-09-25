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
 * AsyncGroup.class.php
 * 用于并行执行Async，自己发起，自己接收，承担了map + reduce两个工作
 * @author fang
 * @date 2015-11-5
 */
class AsyncGroup extends Async {
	protected $subAsyncArray = array();
	protected function __construct() {}
	
	static public function create($sthGroup) {
		if(!is_array($sthGroup)){
			throw new \Exception('asset is_array($sthGroup)');
		}
		
		$promiseGroup = new self();
		
		foreach($sthGroup as $sth){
			$promise = Async::create($sth);
			$promise->nextAsync = $promiseGroup;
			$promiseGroup->subAsyncArray[] = $promise;
		}
		
		return $promiseGroup;
	}

	// /////////////////////////////////////////////

	protected $phase = 0;	//执行阶段 0:map 1:reduce
	protected $runCount = 0;
	protected function run(AsyncContext $context) {
		if($this->phase == 0){
			$this->context = $context;
			$this->phase = 1;
			$this->runCount = count($this->subAsyncArray);
			foreach($this->subAsyncArray as $subAsync){
				$subAsync->start ( $this->context );
				unset($subAsync);
			}
			unset($this->subAsyncArray);
		}else{
			$this->context->merge($context);
			$this->runCount --;
			if($this->runCount == 0){//都执行完了
				$this->accept();
			}
		}
	}
}