<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2016/5/21
 * Time: 23:06
 */

namespace Tsy\Plugs\Tree;


class Tree
{
    public $tableName;
    /**
     * @var \Tsy\Library\Model
     */
    public $Model;
    function __construct($tableName='')
    {
//        if(!$tableName){/
        $this->tableName=$tableName?$tableName:C('TREE_TABLE_NAME');
//        }
        $this->Model = M($this->tableName);
    }

    /**
     * 把ID插入到LID下的Sort顺序
     * @param $ID
     * @param $LID
     * @param int $Sort
     */
    function add($ID,$LID,$Sort=0){
        $this->Model->where(['LID'=>$LID])->getField('ID');
    }
    function get(){
        $lists = $this->Model->order('LID')->select();

        //相邻的两条记录的右值第一条的右值比第二条的大那么就是他的父类
        //我们用一个数组来存储上一条记录的右值，再把它和本条记录的右值比较，如果前者比后者小，说明不是父子关系，就用array_pop弹出数组，否则就保留

        //两个循环而已，没有递归
        $parent = array();
        $arr_list = array();
        foreach($lists as $item){

            if(count($parent)){
                while (count($parent) -1 > 0 && $parent[count($parent) -1]['RID'] < $item['RID']){
                    array_pop($parent);
                }
            }

            $item['depath'] = count($parent);
            $parent[]  = $item;
            $arr_list[]= $item;
        }

        //显示树状结构
        foreach($arr_list as $a)
        {
            echo str_repeat('--', $a['depath']) . $a['title'] . '<br />';
        }
    }
}