<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 5/19/16
 * Time: 7:09 PM
 */
/**
 * 通过SMTP发送邮件
 * @param $from
 * @param $to
 * @param $title
 * @param $content
 * @param $config
 * @return bool
 * @throws \Tsy\Plugs\PHPMailer\phpmailerException
 */
function smtp_mail($from,$to,$title,$content,$config){
//    smtp_mail(['safe@tansuyun.com','安全中心'],['490523604@qq.com','castle'],'系统报121警','系统1212报警',[
//        'Username'=>'SMTP帐号',
//        'Password'=>'SMTP密码',
//        'Host'=>'smtp.mxhichina.com',
//        'Port'=>25
//    ]);
    $Mailer = new Tsy\Plugs\PHPMailer\PHPMailer();
    $Mailer->isSMTP();
    $Mailer->Port=$config['Port'];
    $Mailer->Host=$config['Host'];
    $Mailer->SMTPAuth=true;
    $Mailer->Username=$config['Username'];
    $Mailer->Password=$config['Password'];
    $Mailer->setFrom($from[0],$from[1]);
    $Mailer->addAddress($to[0],$to[1]);
    $Mailer->Subject=$title;
    $Mailer->msgHTML($content);
    if($Mailer->send()){
        return true;
    }else{
        L($Mailer->ErrorInfo);
        return false;
    }
}