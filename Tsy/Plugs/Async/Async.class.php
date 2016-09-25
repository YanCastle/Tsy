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
 * Async.class.php
 * @author fang
 * @date 2015-11-5
 */
class Async {
	public $context = null;		//AsyncContext
	protected $future;				//FutureIntf
	protected $lastAsync = null;	//Async
	protected $nextAsync = null;	//Async
	protected function __construct($future) {
		$this->future = $future;
	}
	static public function create($sth) {
		if (is_callable ( $sth )) {
			$future = new Future ( $sth );
			return new self ( $future );
		} elseif ($sth instanceof FutureIntf) {
			return new self ( $sth );
		} elseif ($sth instanceof Async) {
			return $sth;
		} elseif (is_array($sth)) {
			return AsyncGroup::create($sth);
		} else {
			throw new \Exception ( 'error sth type' );
		}
	}

	public function then($sth) {
		if (is_callable ( $sth )) {
			$future = new Future ( $sth );
			$nextAsync = new self ( $future );
			$this->nextAsync = $nextAsync;
			$nextAsync->lastAsync = $this;
			return $nextAsync;
		} elseif ($sth instanceof FutureIntf) {
			$nextAsync = new self ( $sth );
			$this->nextAsync = $nextAsync;
			$nextAsync->lastAsync = $this;
			return $nextAsync;
		} elseif ($sth instanceof Async) {
			// 拿到的sth一定是尾promise，把头promise挂上主promise
			$headAsync = $sth->getHeadAsync ();
			$this->nextAsync = $headAsync;
			$headAsync->lastAsync = $this;
			return $sth;
		} elseif (is_array($sth)){
			$nextAsync = AsyncGroup::create($sth);
			$this->nextAsync = $nextAsync;
			$nextAsync->lastAsync = $this;
			return $nextAsync;
		}else {
			throw new Exception ( 'error sth type' );
		}
	}

	// 找到第一个promise然后执行
	public function start($context) {
		$headAsync = $this->getHeadAsync ();
		$headAsync->run ( $context );
		unset($headAsync);
	}

	protected $accepted = false;
	// 成功后执行
	public function accept($ret = null) {
		if($this->accepted)return;	//仅执行一次
		$this->accepted = true;
		
		if ($this->nextAsync !== null) {
			if(is_array($ret)){
				$this->context->merge($ret);
			}
			$this->nextAsync->run ( $this->context );
			unset($this->nextAsync);
		}
	}

	//设置上下文数据
	public function set($key, $val){
		return $this->context->set($key, $val);
	}

	//获取上下文数据
	public function get($key){
		return $this->context->get($key);
	}

	//获取全部上下文数据
	public function getData(){
		return $this->context->getAll();
	}

	// 失败后执行
	public function reject() {
	}

	// /////////////////////////////////////////////

	// 取得第一个promise
	/**
	 * @return Async
	 */
	protected function getHeadAsync() {
		for($i = $this; $i->lastAsync != null; $i = $i->lastAsync)
			;
		return $i;
	}
	protected function run(AsyncContext $context) {
		$this->context = $context;
		if($this->future instanceof FutureIntf){
			$ret = $this->future->run ( $this, $context );
			unset($this->future);

			// 如果返回值是个promise，那么把后续的promise链条挂载到这个promise后面，然后继续执行
			if ($ret instanceof Async) {
				$ret->nextAsync = $this->nextAsync;
				if ($this->nextAsync) {
					$this->nextAsync->lastAsync = $ret;
				}
				$ret->start ( $context );
			}
		}
	}
}
//class_alias('', );