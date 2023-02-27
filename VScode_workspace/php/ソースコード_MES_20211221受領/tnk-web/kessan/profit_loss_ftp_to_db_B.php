<?php
//////////////////////////////////////////////////////////////////////////////
// ·î¼¡Â»±×´Ø·¸¤Î¥Ç¡¼¥¿ ¼«Æ°FTP Download  ²ÊÌÜÊÌÉôÌç·ÐÈñ¤Î¥Ç¡¼¥¿            //
// AS/400 ----> Web Server (PHP) TNKACT ¢ª 77 ¢ª 77 ¢ª 31 ¢ª 4              //
// 2003/01/17 Copyright(C) 2003-2004 K.Kobayashi tnksys@nitto-kohki.co.jp   //
// ÊÑ¹¹·ÐÎò                                                                 //
// 2003/01/17 ¿·µ¬ºîÀ®  profit_loss_ftp_to_db_B.php                         //
// 2003/01/24 ¥Ç¡¼¥¿¥Ù¡¼¥¹¤Ø¤Î¼è¤ê¹þ¤ß¥í¥¸¥Ã¥¯¤òÄÉ²Ã                        //
// 2003/01/27 ¥Ç¡¼¥¿¥Ù¡¼¥¹¤Ø¤Î¼è¤ê¹þ¤ß¤ò¾®Ê¬¤±¤¹¤ë¤¿¤á¥Õ¥¡¥¤¥ëÌ¾ÊÑ¹¹        //
//            £Ã£Ì·ÐÈñ¼ÂÀÓÉ½ÍÑ¤Î¥Ç¡¼¥¿¼è¤ê¹þ¤ß É½ID=B                       //
// 2003/01/28 ¥Ç¡¼¥¿¥Ù¡¼¥¹¤Î¥Õ¥£¡¼¥ë¥ÉÄÉ²Ã ÂÐ¾Ý´ü(ki=3¤Ê¤É)                 //
// 2003/02/28 ¥Ç¡¼¥¿¥Ù¡¼¥¹¤Ø¤ÎÅÐÏ¿¤ò¥È¥é¥ó¥¶¥¯¥·¥ç¥ó¤ËÊÑ¹¹                  //
// 2004/02/05 AS/400 ¤ÎÂÐ¾ÝÇ¯·î¤Î¥Á¥§¥Ã¥¯µ¡Ç½ÄÉ²Ã kin9 != $yyyymm           //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);   // E_ALL='2047' debug ÍÑ
// ini_set('display_errors','1');      // Error É½¼¨ ON debug ÍÑ ¥ê¥ê¡¼¥¹¸å¥³¥á¥ó¥È
session_start();                    // ini_set()¤Î¼¡¤Ë»ØÄê¤¹¤ë¤³¤È Script ºÇ¾å¹Ô
require_once ("../function.php");
require_once ("../tnk_func.php");
access_log();       // Script Name ¤Ï¼«Æ°¼èÆÀ
if (account_group_check() == FALSE) {
    $_SESSION["s_sysmsg"] = "¤¢¤Ê¤¿¤Ïµö²Ä¤µ¤ì¤Æ¤¤¤Þ¤»¤ó!<br>´ÉÍý¼Ô¤ËÏ¢Íí¤·¤Æ²¼¤µ¤¤!";
    header("Location: http:" . WEB_HOST . "kessan/kessan_menu.php");
    exit();
}

    ///// ÂÐ¾ÝÇ¯·î¤Î¼èÆÀ
$yyyymm = $_SESSION['pl_ym'];
    ///// ´ü¤Î¼èÆÀ
$ki = Ym_to_tnk($_SESSION['pl_ym']);

    ///// AS/400 ¤Î ¥é¥¤¥Ö¥é¥ê¤È¥Õ¥¡¥¤¥ëÌ¾ÀßÄê
$as_lib_file = "UKFLIB/WCPLBSP";
    ///// Dounload File Name ÀßÄê
$file_orign = "WCPLBSP.TXT";
    ///// Dounload file ÆâÍÆÀâÌÀ
$file_note  = "²ÊÌÜÊÌÉôÌç·ÐÈñ B";

    ///// ¥Õ¥¡¥¤¥ë¤ÎÂ¸ºß¥Á¥§¥Ã¥¯
if (file_exists($file_orign)) {
    unlink($file_orign);    // ¤¢¤ë¾ì¹ç¤Ïµì¥Õ¥¡¥¤¥ë¤Î¤¿¤áºï½ü FTP error »þ¤Ëµì¥Õ¥¡¥¤¥ë¤Ç¹¹¿·¤·¤Ê¤¤¤¿¤á
}
// ¥³¥Í¥¯¥·¥ç¥ó¤ò¼è¤ë(FTPÀÜÂ³¤Î¥ª¡¼¥×¥ó)
if ($ftp_stream = ftp_connect("10.1.1.252")) {
    if (ftp_login($ftp_stream,"FTPUSR","AS400FTP")) {
        if (ftp_get($ftp_stream, $file_orign, $as_lib_file, FTP_ASCII)) {
            $_SESSION['s_sysmsg'] .= sprintf("<font color='white'>%d %s¤Î DOWNLOAD À®¸ù</font>", $yyyymm, $file_note);
        } else {
            $_SESSION['s_sysmsg'] .= sprintf("%d %s¤Î DOWNLOAD ¼ºÇÔ<br>ftp_get_error", $yyyymm, $file_note);
        }
    } else {
        $_SESSION['s_sysmsg'] .= sprintf("%d %s¤Î DOWNLOAD ¼ºÇÔ<br>ftp_login_error", $yyyymm, $file_note);
    }
    ftp_close($ftp_stream);
} else {
    $_SESSION['s_sysmsg'] .= sprintf("%d %s¤Î DOWNLOAD ¼ºÇÔ<br>ftp_connect_error", $yyyymm, $file_note);
}

///// ·î¼¡Â»±×¥Ç¡¼¥¿ ½àÈ÷ºî¶È FTP ¥Ç¡¼¥¿¤ò¼èÆÀ
if(file_exists($file_orign)){           // ¥Õ¥¡¥¤¥ë¤ÎÂ¸ºß¥Á¥§¥Ã¥¯
    $fp = fopen($file_orign,"r");
    $t_id     = array();   // É½ID   ¥¢¥ë¥Õ¥¡¥Ù¥Ã¥È 1
    $t_row    = array();   // ¹Ô­â                  2
    $actcod = array();   // ²ÊÌÜ¥³¡¼¥É            4
    $wplkn1 = array();   // ¶â³Û1                11
    $wplkn2 = array();   // ¶â³Û2                11
    $wplkn3 = array();   // ¶â³Û3                11
    $wplkn4 = array();   // ¶â³Û4                11
    $wplkn5 = array();   // ¶â³Û5                11
    $wplkn6 = array();   // ¶â³Û6                11
    $wplkn7 = array();   // ¶â³Û7                11
    $wplkn8 = array();   // ¶â³Û8                11
    $wplkn9 = array();   // ¶â³Û9                11
    $rec = 0;       // ¥ì¥³¡¼¥É­â
    while(!feof($fp)){          // ¥Õ¥¡¥¤¥ë¤ÎEOF¥Á¥§¥Ã¥¯
        $data=fgets($fp,200);   // ¼ÂºÝ¤Ë¤Ï120 ¤ÇOK¤À¤¬Í¾Íµ¤ò»ý¤Ã¤Æ
        $data = mb_convert_encoding($data, "EUC-JP", "auto");       // auto¤òEUC-JP¤ØÊÑ´¹
        $t_id[$rec]     = substr($data,0,1);        // É½ID
        if ($t_id[$rec] != 'B')     // £Ã£Ì·ÐÈñ¥Ç¡¼¥¿¤Ç¤Ê¤±¤ì¤ÐºÆÆÉ¹þ
            continue;
        $t_row[$rec]  = substr($data,1,2);          // ¹Ô­â
        $actcod[$rec] = substr($data,3,4);          // ²ÊÌÜ¥³¡¼¥É
        $wplkn1[$rec] = substr($data,7,11)  ;       // ¶â³Û1
        $wplkn2[$rec] = substr($data,18,11) ;       // ¶â³Û2
        $wplkn3[$rec] = substr($data,29,11) ;       // ¶â³Û3
        $wplkn4[$rec] = substr($data,40,11) ;       // ¶â³Û4
        $wplkn5[$rec] = substr($data,51,11) ;       // ¶â³Û5
        $wplkn6[$rec] = substr($data,62,11) ;       // ¶â³Û6
        $wplkn7[$rec] = substr($data,73,11) ;       // ¶â³Û7
        $wplkn8[$rec] = substr($data,84,11) ;       // ¶â³Û8
        $wplkn9[$rec] = substr($data,95,11) ;       // ¶â³Û9
        $rec++;
    }
    fclose($fp);
    //////////// ÂÐ¾ÝÇ¯·î¤Î¥Á¥§¥Ã¥¯
    if ($wplkn9[0] != $yyyymm) {
        $_SESSION['s_sysmsg'] .= "AS/400¤ÎÇ¯·î¤¬°ã¤¤¤Þ¤¹<br>{$t_id[0]}{$t_row[0]}¡§{$wplkn9[0]}";
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    
    /////////// begin ¥È¥é¥ó¥¶¥¯¥·¥ç¥ó³«»Ï
    if ($con = db_connect()) {
        query_affected_trans($con, "begin");
    } else {
        $_SESSION["s_sysmsg"] .= "¥Ç¡¼¥¿¥Ù¡¼¥¹¤ËÀÜÂ³¤Ç¤­¤Þ¤»¤ó";
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    ///// ¥Ç¡¼¥¿¥Ù¡¼¥¹¤Ø¤Î¼è¤ê¹þ¤ß
    $ok_row = 0;        ///// ¼è¤ê¹þ¤ß´°Î»¥ì¥³¡¼¥É¿ô
    $res_chk = array();
    $query_chk = sprintf("select pl_bs_ym from pl_bs_summary where pl_bs_ym=%d and t_id='B'", $yyyymm);
    if (getResult($query_chk,$res_chk) <= 0) {      ///// ´ûÅÐÏ¿ºÑ¤ß¤Î¥Á¥§¥Ã¥¯
        for($i=0;$i<$rec;$i++){                     ///// ¿·µ¬ÅÐÏ¿
            $query = sprintf("insert into pl_bs_summary (pl_bs_ym,ki,t_id,t_row,actcod,kin1,kin2,kin3,kin4,kin5,kin6,kin7,kin8,kin9) 
                values(%d,%d,'%s',%d,%d,%d,%d,%d,%d,%d,%d,%d,%d,%d)",
                $yyyymm, $ki, $t_id[$i], $t_row[$i], $actcod[$i], $wplkn1[$i], $wplkn2[$i], $wplkn3[$i], 
                $wplkn4[$i], $wplkn5[$i], $wplkn6[$i], $wplkn7[$i], $wplkn8[$i], $wplkn9[$i]);
            if(query_affected_trans($con, $query) <= 0){        // ¹¹¿·ÍÑ¥¯¥¨¥ê¡¼¤Î¼Â¹Ô
                $NG_row = ($i + 1);
                $_SESSION['s_sysmsg'] .= "<br>¥Ç¡¼¥¿¥Ù¡¼¥¹¤Î¿·µ¬ÅÐÏ¿¤Ë¼ºÇÔ¤·¤Þ¤·¤¿ ­â$NG_row";
                query_affected_trans($con, "rollback");         // transaction rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            } else
                $ok_row++;
        }
        /******** debug start
        $i = 85;
            $query = sprintf("insert into pl_bs_summary (pl_bs_ym,t_id,t_row,actcod,kin1,kin2,kin3,kin4,kin5,kin6,kin7,kin8,kin9) 
                values(%d,'%s',%d,%d,%d,%d,%d,%d,%d,%d,%d,%d,%d)",
                $yyyymm, $t_id[$i], $t_row[$i], $actcod[$i], $wplkn1[$i], $wplkn2[$i], $wplkn3[$i], 
                $wplkn4[$i], $wplkn5[$i], $wplkn6[$i], $wplkn7[$i], $wplkn8[$i], $wplkn9[$i]);
        $_SESSION['s_sysmsg'] .= $query;
        *********//// debug end
    } else {                  // UPDATE
        for($i=0;$i<$rec;$i++){
            $query = sprintf("update pl_bs_summary set pl_bs_ym=%d, ki=%d, t_id='%s', t_row=%d, actcod=%d, 
                kin1=%d, kin2=%d, kin3=%d, kin4=%d, kin5=%d, kin6=%d, kin7=%d, kin8=%d, kin9=%d 
                where pl_bs_ym=%d and t_id='%s' and t_row=%d", 
                $yyyymm, $ki, $t_id[$i], $t_row[$i], $actcod[$i], $wplkn1[$i], $wplkn2[$i], $wplkn3[$i], 
                $wplkn4[$i], $wplkn5[$i], $wplkn6[$i], $wplkn7[$i], $wplkn8[$i], $wplkn9[$i], 
                $yyyymm, $t_id[$i], $t_row[$i]);
            if(query_affected_trans($con, $query) <= 0){        // ¹¹¿·ÍÑ¥¯¥¨¥ê¡¼¤Î¼Â¹Ô
                $NG_row = ($i + 1);
                $_SESSION['s_sysmsg'] .= "<br>¥Ç¡¼¥¿¥Ù¡¼¥¹¤ÎUPDATE¤Ë¼ºÇÔ¤·¤Þ¤·¤¿ ­â$NG_row";
                query_affected_trans($con, "rollback");         // transaction rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            } else 
                $ok_row++;
        }
        /******* debug start
        $i = 1;
            $query = sprintf("update pl_bs_summary set pl_bs_ym=%d, t_id='%s', t_row=%d, actcod=%d, 
                kin1=%d, kin2=%d, kin3=%d, kin4=%d, kin5=%d, kin6=%d, kin7=%d, kin8=%d, kin9=%d 
                where pl_bs_ym=%d and t_id='%s' and t_row=%d", 
                $yyyymm, $t_id[$i], $t_row[$i], $actcod[$i], $wplkn1[$i], $wplkn2[$i], $wplkn3[$i], 
                $wplkn4[$i], $wplkn5[$i], $wplkn6[$i], $wplkn7[$i], $wplkn8[$i], $wplkn9[$i], 
                $yyyymm, $t_id[$i], $t_row[$i]);
        $_SESSION['s_sysmsg'] .= $query;
        ********////// debug end
    }
    $_SESSION['s_sysmsg'] .= sprintf("<br>%d %s %d ·ï ¼è¤ê¹þ¤ß´°Î»", $yyyymm, $file_note, $ok_row);
    /////////// commit ¥È¥é¥ó¥¶¥¯¥·¥ç¥ó½ªÎ»
    query_affected_trans($con, "commit");
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<META http-equiv="Content-Style-Type" content="text/css">
<TITLE>·î¼¡Â»±× FTP Download </TITLE>
<style type="text/css">
<!--
body        {margin:20%;font-size:24pt;}
-->
</style>
</HEAD>
<BODY>
    <center>AS/400 ¤È ¥Ç¡¼¥¿¥ê¥ó¥¯ ´°Î»</center>

    <script language="JavaScript">
    <!--
        location = 'http:<?php echo(WEB_HOST) . "kessan/profit_loss_select.php" ?>';
    // -->
    </script>
</BODY>
</HTML>
