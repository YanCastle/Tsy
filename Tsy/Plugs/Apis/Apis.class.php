<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2016/2/14
 * Time: 9:21
 */

namespace Tsy\Plugs\Apis;


class Apis
{
    /**
     * 查询四川车辆违章情况
     * @param string $CarNumber 车牌号
     * @param number $LastSexVIN 车架号后六位
     * @return bool|array
     */
    function SiChuanWeiZhang($CarNumber,$LastSexVIN){
        $rs = json_decode(file_get_contents("http://www.xmxing.net/panda_api_new/anonymous_car_illegal_list.php?phone=21838194&pass=1471f23c0513cd19b5993b94763aaff94e935c119ff865296ce5e643758fb3f4&hpzl=02&hphm={$CarNumber}&cjh={$LastSexVIN}"),true);
        if(is_array($rs)&&isset($rs['state'])){
            //查询成功
            if($rs['state']){
                return $rs['data'];
            }else{
                $this->error('为空');
                return false;
            }
//            return $rs['state']?$rs['data']:[];
        }
        $this->error('查询失败');
        return false;
    }
}