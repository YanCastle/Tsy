<?php

namespace Tsy\Plugs\Verify;
class Verify{
	static $V_INT='INT';
	static $V_STRING='STRING';//字符串
	static $V_DOUBLE='DOUBLE';//双精度浮点数
	static $V_ARRAY='ARRAY';//数组
	static $V_EMAIL='EMAIL';//email
	static $V_URL='URL';//地址
	static $V_MONEY='MONEY';//金额
	static $V_IN='IN';
	static $V_BOOL='BOOL';//布尔值
	static $V_PLUS='PLUS';//正数
	static $V_NEGATIVE='NEGATIVE';//负数
	static $V_PLUS_ZERO='PLUS_ZERO';//正数
	static $V_NEGATIVE_ZERO='NEGATIVE_ZERO';//负数
	static $V_TIMESTAMP='TIMESTAMP';//时间戳
	static $V_DATE='DATE';//日期
	static $A=[];
	public $Config=[];
	public $err='';
	function __construct(){
		self::$A=[self::$V_INT,self::$V_STRING,self::$V_DOUBLE,self::$V_ARRAY,
		          self::$V_EMAIL,self::$V_URL,self::$V_MONEY,self::$V_IN,self::$V_BOOL,
		          self::$V_PLUS,self::$V_NEGATIVE,self::$V_PLUS_ZERO,self::$V_NEGATIVE_ZERO,
		          self::$V_TIMESTAMP,self::$V_DATE,];
	}
	function v($Class,$Func,array $Param,$Config=false){
		$Func=strtolower($Func);
		$Class=ucwords(strtolower($Class));
		if(!$Config){
			if(isset($this->Config[$Class])&&isset($this->Config[$Class][$Func])&&$this->Config[$Class][$Func]){
				$Config=$this->Config[$Class][$Func];
			}else{
//					引入文件
				$path = __DIR__.'/Verify/'.$Class.'.php';
				if(file_exists($path)){
					$this->Config=array_merge($this->Config,[$Class=>include $path]);
				}
				if(isset($this->Config[$Class])&&isset($this->Config[$Class][$Func])&&$this->Config[$Class][$Func]){
					$Config=$this->Config[$Class][$Func];
				}
			}
		}
		if(!$Config||!is_array($Config)){
			$this->err('配置文件缺失');
			return 0;
		}
		return $this->_v($Config,$Param);
	}
	function getConfig(){

	}
	function err($err=false){
//		if($err){
//			$this->err=$err;
//		}else{
//			return $this->err;
//		}
		L($err);
	}
	public function _v($Config,$Param){
		//要取得必要参数列表，和所有参数列表，并取得
		foreach($Config as $K=>$Rule){
			if($Rule[0]&&!isset($Param[$K])){
				$this->err(isset($Rule['E'])?$Rule['E']:'缺少参数:'.$K);
				return false;
			}
			if($Param[$K]==''){
				continue;
			}
			if(isset($Param[$K])){
				if(is_array($Rule[1])&&$Param[$K]){
					foreach($Rule[1] as $K1=>$V1){
						$Pass=false;
						if(is_numeric($K1)&&$this->_s($V1,$Param[$K],isset($Rule[2])?$Rule[2]:false)){
//							[Verify::$V_INT,Verify::$V_STRING]
							$Pass=true;
						}elseif($K1==self::$V_ARRAY&&in_array($V1,self::$A)){
							foreach($Param[$K] as $v){
								if(!$this->_s($V1,$v)){
									return false;
								}
							}
							$Pass=true;
						}elseif($K1==self::$V_ARRAY&&is_array($V1)){
//							[Verify::$V_ARRAY=>[
//								'GoodsID'=>[true,Verify::$V_INT,'E'=>'商品编号错误'],
//								'Amount'=>[true,Verify::$V_DOUBLE,'E'=>'商品数量错误'],
//								'Memo'=>[false,Verify::$V_STRING,'E'=>'商品备注错误']
//							]]
							foreach($Param[$K] as $K2=>$Arr2){
								if(is_numeric($K2)){
									if(!$this->_v($V1,$Arr2)){
										return false;
									}
								}else{
									if(!$this->_v($V1,[$K2=>$Arr2])){
										return false;
									}
								}
							}
							$Pass=true;
						}elseif($this->_s($K1,$Param[$K],$V1)){
//							[Verify::$V_STRING=>[1,30]];
//							[Verify::$V_IN=>[1,30]];
//							[Verify::$V_INT=>[1,30]];
							$Pass=true;
						}
						if(!$Pass){
							$this->err(is_array($V1)&&isset($V1['E'])?$V1['E']:(isset($Rule['E'])?$Rule['E']:'参数:'.$K.'未通过验证'));
							return false;
						}
					}
				}else{
					if(!$this->_s($Rule[1],$Param[$K],isset($Rule[2])?$Rule[2]:false)){
						$this->err(isset($Rule['E'])?$Rule['E']:"参数:{$K}未通过验证");
						return false;
					}
				}
			}elseif(isset($Rule['D'])){
				$Param[$K]=$Rule['D'];
			}
		}
		return $Param;
	}
	private function _s($Type,$Value,$Length=false){
		switch($Type){
			case self::$V_STRING:
				if(!is_string($Value)){
					return false;
				}
				if($Length!==false){
					if(is_array($Length)){
						if(count($Length)==1&&is_numeric(array_values($Length)[0])){
							if(!mstrlen($Value)==array_values($Length)[0]){
								return false;
							}
						}elseif(count($Length)==2){
							$strlen = mstrlen($Value);
							$sort = array_values($Length);
							sort($sort,SORT_NUMERIC);
							if(!($strlen>=$sort[0]&&$strlen<=$sort[1])){
								return false;
							}
						}
					}elseif(is_numeric($Length)){
						return mstrlen($Value)==$Length;
					}else{
						return false;
					}
				}
				break;
			case self::$V_DOUBLE:

				break;
			case self::$V_ARRAY:break;
			case self::$V_INT:
				if(sprintf('%d',$Value)!=$Value){
					return false;
				}
				if($Length!==false){
					if(is_array($Length)){
						if(count($Length)==1&&is_numeric(array_values($Length)[0])){
							if($Value!=array_values($Length)[0]){
								return false;
							}
						}elseif(count($Length)==2){
							$sort = array_values($Length);
							sort($sort,SORT_NUMERIC);
							if(!($Value>=$sort[0]&&$Value<=$sort[1])){
								return false;
							}
						}
					}elseif(is_numeric($Length)){
						return $Length==$Value;
					}else{
						return false;
					}
				}
				break;
			case self::$V_IN:
				if(!is_array($Length)){
					return false;
				}
				if(!in_array($Value,$Length)){
					return false;
				}
				break;
			case self::$V_MONEY:
				return sprintf('%.2f',$Value)==$Value;
				break;
			case self::$V_EMAIL:

				break;
			case self::$V_URL:

				break;
			case self::$V_BOOL:

				break;
			case self::$V_PLUS:
				return sprintf('%d',$Value)==$Value&&$Value>0;
				break;
			case self::$V_NEGATIVE:
				return sprintf('%d',$Value)==$Value&&$Value<0;
				break;
			case self::$V_PLUS_ZERO:
				return sprintf('%d',$Value)==$Value&&$Value>=0;
				break;
			case self::$V_NEGATIVE_ZERO:
				return sprintf('%d',$Value)==$Value&&$Value<=0;
				break;
			//TODO 时间戳和日期的验证
			default:
				break;
		}
		return true;
	}

	/**
	 * 检验是否是中国18位身份证号
	 * @param $IDCardNumber
	 * @return bool
	 */
	static function ChineseIDCard($IDCardNumber){
		$IDCardNumber=strtoupper($IDCardNumber);
		if(strlen($IDCardNumber)==18&&preg_match('/^\d{17}[\dX]{1}/',$IDCardNumber)){
			$Weight = [7,9,10,5,8,4,2,1,6,3,7,9,10,5,8,4,2];
			$Sum = 0;
			$Exploded = mexplode($IDCardNumber);
			for($i=0;$i<17;$i++){
				$Sum+=$Exploded[$i]*$Weight[$i];
			}
			$Mod = $Sum%11;
			$Mod = $Mod==10?'X':$Mod;
			return $Exploded[17]==$Mod;
		}
		return false;
	}

	/**
	 * 验证手机号是否符合规范
	 * @param $PhoneNumber
	 * @return int
	 */
	static function CellPhoneNumber($PhoneNumber){
		return preg_match('/^1\d{10}$/',$PhoneNumber);
	}
}