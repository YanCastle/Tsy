<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 7/28/16
 * Time: 3:53 PM
 */
$array = [
    'SalesAmount',
    'SalesCost',
    'SalesProfit',
    'OrderType2Sum',
    'OrderType3Sum',
    'OrderType0Sum',
    'OrderType1Sum',
    'OrderSum',
    'GoodsNewSum',
    'TraderNewSum',
    'EarlyReceivables',
    'FinalReceivalbes',
    'Received',
    'EarlyPayable',
    'FinalPayable',
    'Payed',
];
$str  = '';
foreach ($array as $item){
    $str.=("SUM({$item}) AS $item");
}
echo $str;