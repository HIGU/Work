#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// Áí¹çÆÏ¾µÇ§ÂÔ¤Á¾ðÊó¥á¡¼¥ëÁ÷¿® cron.d tnk_daily ½èÍý¤Ç¼Â¹Ô                 //
// ·î¡Á¶â¤Î16¡§40»þ¤Ë¥á¡¼¥ëÁ÷¿® ¸ÄÊÌÈÇ                                      //
// SELECT DISTINCT admit_status FROM sougou_deteils                         //
//                                             WHERE admit_status ='300055' //
// 300055=Àî”³²ÝÄ¹ÂåÍý ¸½ºß¸ÄÊÌÈÇ ID»ØÄêÄÉ²Ã¤Î¾ì¹ç¤Ï or ¤Ç·Ò¤°              //
// Copyright (C) 2020-2020 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2020/09/24 Created   sougou_admit_mail.php                               //
// 2021/02/17 ËÜÊ¸¤ÎÃæ¤Ë¾µÇ§¥Ú¡¼¥¸¤Î¥¢¥É¥ì¥¹¤òÄÉ²Ã                          //
//////////////////////////////////////////////////////////////////////////////
//ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ÍÑ
//ini_set('display_errors', '1');             // Error É½¼¨ ON debug ÍÑ ¥ê¥ê¡¼¥¹¸å¥³¥á¥ó¥È
ini_set('implicit_flush', 'off');           // echo print ¤Ç flush ¤µ¤»¤Ê¤¤(ÃÙ¤¯¤Ê¤ë¤¿¤á)
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');        ///// ÆüÊóÍÑ¥í¥°¤ÎÆü»þ
$fpa = fopen('/tmp/nippo.log', 'a');    ///// ÆüÊóÍÑ¥í¥°¥Õ¥¡¥¤¥ë¤Ø¤Î½ñ¹þ¤ß¤Ç¥ª¡¼¥×¥ó
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// ÆüÊó¥Ç¡¼¥¿ºÆ¼èÆÀÍÑ¥í¥°¥Õ¥¡¥¤¥ë¤Ø¤Î½ñ¹þ¤ß¤Ç¥ª¡¼¥×¥ó
fwrite($fpb, "Áí¹çÆÏ¾µÇ§ÂÔ¤Á¾ðÊó¥á¡¼¥ëÁ÷¿®\n");
fwrite($fpb, "/home/www/html/tnk-web/system/daily/sougou_admit_mail.php\n");
echo "/home/www/html/tnk-web/system/daily/sougou_admit_mail.php\n";

if ($con = db_connect()) {
    query_affected_trans($con, "begin");
} else {
    echo "$log_date Áí¹çÆÏ¾µÇ§ÂÔ¤Á¾ðÊó¥á¡¼¥ëÁ÷¿® db_connect() error \n";
    fwrite($fpa,"$log_date Áí¹çÆÏ¾µÇ§ÂÔ¤Á¾ðÊó¥á¡¼¥ëÁ÷¿® db_connect() error \n");
    fwrite($fpb,"$log_date Áí¹çÆÏ¾µÇ§ÂÔ¤Á¾ðÊó¥á¡¼¥ëÁ÷¿® db_connect() error \n");
    exit();
}

/////////// ÆüÉÕ¥Ç¡¼¥¿¤Î¼èÆÀ
$target_ym   = date('Ym');          //201710
$b_target_ym = $target_ym - 100;    //201610
$today       = date('Ymd');         //20171012
$b_today     = $today - 10000;      //20161012

/////////// begin ¥È¥é¥ó¥¶¥¯¥·¥ç¥ó³«»Ï
$query = sprintf("SELECT DISTINCT admit_status FROM sougou_deteils WHERE admit_status ='300055'");
$res   = array();
$field = array();
if (($rows = getResultWithField3($query, $field, $res)) <= 0) {
    exit();
} else {
    $num = count($field);       // ¥Õ¥£¡¼¥ë¥É¿ô¼èÆÀ
    for ($r=0; $r<$rows; $r++) {
        $query_t = "SELECT 
                                count(admit_status) as t_ken
                          FROM sougou_deteils ";
        $search_t = "WHERE admit_status='{$res[$r][0]}'";
        $query_t = sprintf("$query_t %s", $search_t);     // SQL query Ê¸¤Î´°À®
        $res_t   = array();
        $field_t = array();
        $res_sum_t = array();
        if (getResult($query_t, $res_sum_t) <= 0) {
            exit();
        } else {
            $t_ken     = $res_sum_t[0]['t_ken'];
            $_SESSION['u_t_ken']  = $t_ken;
            if ($t_ken>0) {
                $query_m = "SELECT trim(name), trim(mailaddr)
                                FROM
                                    user_detailes
                                LEFT OUTER JOIN
                                    user_master USING(uid)
                                ";
                //$search_m = "WHERE uid='300144'";
                // ¾å¤Ï¥Æ¥¹¥ÈÍÑ ¶¯À©Åª¤Ë¼«Ê¬¤Ë¥á¡¼¥ë¤òÁ÷¤ë
                $search_m = "WHERE uid='{$res[$r][0]}'";
                $query_m = sprintf("$query_m %s", $search_m);     // SQL query Ê¸¤Î´°À®
                $res_m   = array();
                $field_m = array();
                $res_sum_m = array();
                if (getResult($query_m, $res_sum_m) <= 0) {
                    exit();
                } else {
                    $sendna = $res_sum_m[0][0];
                    $mailad = $res_sum_m[0][1];
                    $_SESSION['u_mailad']  = $mailad;
                    $to_addres = $mailad;
                    $add_head = "";
                    $attenSubject = "°¸Àè¡§ {$sendna} ÍÍ Áí¹çÆÏ¾µÇ§ÂÔ¤Á¤Î¤ªÃÎ¤é¤»";
                    $message   = "{$sendna} ÍÍ\n\n";
                    $message  .= "Áí¹çÆÏ¤Î¾µÇ§ÂÔ¤Á¤¬{$t_ken}·ï¤¢¤ê¤Þ¤¹¡£\n\n";
                    //¥Æ¥¹¥ÈÍÑ ²¼¤ËÊÑ¹¹¤¹¤ë¤³¤È
                    //$message  = "Áí¹çÆÏ¤Î¾µÇ§ÂÔ¤Á¤¬{$t_ken}·ï¤¢¤ê¤Þ¤¹¡£\n\n";
                    $message .= "Áí¹çÆÏ¤Î¾µÇ§½èÍý¤ò¤ª´ê¤¤¤·¤Þ¤¹¡£\n\n";
                    // ¾µÇ§¥Ú¡¼¥¸¤Î¥¢¥É¥ì¥¹(Uid)¤òÉ½¼¨¡¢¥¯¥ê¥Ã¥¯¤Ç¾µÇ§¥Ú¡¼¥¸¤Ø
                    $message .= "http://masterst/per_appli/in_sougou_admit/sougou_admit_Main.php?calUid=";
                    $message .= $res[$r][0];
                    if (mb_send_mail($to_addres, $attenSubject, $message, $add_head)) {
                        // ½ÐÀÊ¼Ô¤Ø¤Î¥á¡¼¥ëÁ÷¿®ÍúÎò¤òÊÝÂ¸
                        //$this->setAttendanceMailHistory($serial_no, $atten[$i]);
                    }
                    ///// Debug
                    //if ($cancel) {
                    //    if ($i == 0) mb_send_mail('tnksys@nitto-kohki.co.jp', $attenSubject, $message, $add_head);
                    //}
                }
            } else {
                
            }
        }
    }
}


/////////// commit ¥È¥é¥ó¥¶¥¯¥·¥ç¥ó½ªÎ»
query_affected_trans($con, "commit");
// echo $query . "\n";
fclose($fpa);      ////// ÆüÊóÍÑ¥í¥°½ñ¹þ¤ß½ªÎ»
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// ÆüÊó¥Ç¡¼¥¿ºÆ¼èÆÀÍÑ¥í¥°½ñ¹þ¤ß½ªÎ»

exit();

?>
