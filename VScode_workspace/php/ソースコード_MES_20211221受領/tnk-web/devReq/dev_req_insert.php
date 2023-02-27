<?php
//////////////////////////////////////////////////////////////////////////////
// プログラム開発依頼書 登録フォーム                                        //
// Copyright (C) 2002-2010 Kazuhiro.kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2002/02/12 Created   dev_req_insert.php                                  //
// 2002/08/09 register_globals = Off 対応                                   //
// 2002/11/18 メールの送信先を安倍副部長から手塚副部長へ変更                //
// 2003/01/31 依頼書の送信者が開発者の場合にメールの送り先変更              //
// 2003/12/12 defineされた定数でディレクトリとメニュー名を使用する          //
// 2004/02/24 メール本文の文字コードを EUC-JP から SJIS へ nkf -EsLw で変換 //
// 2005/05/17 nkf -EjLw → nkf -Ej 文字化け対応 php4では問題なかったがphp5で//
//            上記を更にmail()へ変更(From: Reply-To: を追加 Return-Path: NG)//
//               〃     mb_send_mail()へ(手動でエンコードしなくてもＯＫ)    //
// 2005/06/08 mb_send_mailで複数のヘッダーを区切るのに\r\nを使用する        //
// 2010/01/26 メール送信先を手塚さんから大谷に変更                     大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
session_start();                            // ini_set()の次に指定すること Script 最上行
ob_start();  //Warning: Cannot add header の対策のため追加。2002/01/21
require_once ('../function.php');
// include("../define.php");
// session_register("s_dev_touroku");
$sysmsg = $_SESSION['s_sysmsg'];
$_SESSION['s_sysmsg'] = NULL;
access_log();                               // Script Name は自動取得
// $_SESSION['dev_req_insert'] = date('H:i');
if (!isset($_SESSION['User_ID'])||!isset($_SESSION['Password'])||!isset($_SESSION['Auth'])) {
    $_SESSION['s_sysmsg'] = '認証されていないか認証期限が切れました。Login し直して下さい。';
    header('Location: http:' . WEB_HOST . 'index1.php');
    exit();
}
$s_dev_iraibusho  = $_SESSION['s_dev_iraibusho'];
$s_dev_iraisya    = $_SESSION['s_dev_iraisya'];
$s_dev_mokuteki   = $_SESSION['s_dev_mokuteki'];
$s_dev_naiyou     = $_SESSION['s_dev_naiyou'];
$s_dev_yosoukouka = $_SESSION['s_dev_yosoukouka'];
$s_dev_bikou      = $_SESSION['s_dev_bikou'];

$query = "select name from user_detailes where uid='" . $_SESSION['s_dev_iraisya'] . "'";
$res_name = array();
$rows_name = getResult($query,$res_name);
$iraisya = $res_name[0][0];

$query = "select section_name from section_master where sid=" . $_SESSION['s_dev_iraibusho'];
$res_section = array();
$rows_name = getResult($query,$res_section);
$iraibusho = $res_section[0][0];

$bangou_qry = "select 番号 from dev_req";
$res_bangou=array();
if ($rows_bangou=getResult($bangou_qry,$res_bangou)) {
    $bangou = $rows_bangou + 1;
    $iraibi = date('Y-m-d');
    $insert_qry  = "insert into dev_req (番号, 依頼日, 依頼部署, 依頼者, 目的, 内容, 完了日";
    $ins_qry_add = ") values($bangou, '$iraibi', " . $_SESSION["s_dev_iraibusho"] . ", '" . $_SESSION["s_dev_iraisya"] 
        . "', '" . $_SESSION["s_dev_mokuteki"] . "', '" . $_SESSION["s_dev_naiyou"] . "', '1970-01-01'";
    if ($_SESSION["s_dev_yosoukouka"] != '') {
        $insert_qry  .= ", 予想効果";
        $ins_qry_add .= ", " . $_SESSION['s_dev_yosoukouka'];
    }
    if ($_SESSION['s_dev_bikou'] != '') {
        $insert_qry  .= ", 備考";
        $ins_qry_add .= ", '" . $_SESSION['s_dev_bikou'] . "'";
    }
    $ins_qry_add .= ")";
    $insert_qry .= $ins_qry_add;
    if (funcConnect()) {
        execQuery('begin');
        if (execQuery($insert_qry) >=0 ) {
            execQuery('commit');
            disConnectDB();
            $_SESSION['s_dev_touroku'] = $bangou;
                            // 管理者にメールを送る
            `echo "受付番号： $bangou" > /tmp/dev_req_submit`;
            `echo >> /tmp/dev_req_submit`;
            `echo "依 頼 日： $iraibi" >> /tmp/dev_req_submit`;
            `echo >> /tmp/dev_req_submit`;
            `echo "依頼部署： $s_dev_iraibusho:$iraibusho" >> /tmp/dev_req_submit`;
            `echo >> /tmp/dev_req_submit`;
            `echo "依 頼 者： $s_dev_iraisya:$iraisya" >> /tmp/dev_req_submit`;
            `echo >> /tmp/dev_req_submit`;
            `echo "目    的： $s_dev_mokuteki" >> /tmp/dev_req_submit`;
            `echo >> /tmp/dev_req_submit`;
            `echo "内    容： " >> /tmp/dev_req_submit`;
            `echo "$s_dev_naiyou" >> /tmp/dev_req_submit`;
            `echo >> /tmp/dev_req_submit`;
            `echo "予想効果： $s_dev_yosoukouka" >> /tmp/dev_req_submit`;
            `echo >> /tmp/dev_req_submit`;
            `echo "備    考： $s_dev_bikou" >> /tmp/dev_req_submit`;
            `echo >> /tmp/dev_req_submit`;
            `echo >> /tmp/dev_req_submit`;
            `echo "注意：このメールはＷｅｂサーバーから自動で送られたものです。" >> /tmp/dev_req_submit`;
            `echo "      絶対に返信しないで下さい。返信するとエラーになります。" >> /tmp/dev_req_submit`;
            /***** 2005/05/17 ADD START *****/
            $to_addres = 'tnksys@nitto-kohki.co.jp';
            $subject = "プログラム開発依頼 $bangou $iraibi $iraisya";
            $message = `/bin/cat /tmp/dev_req_submit`;
            // $message = mb_convert_encoding($message, 'JIS', 'EUC-JP');       // EUC-JPをJISへ変換
            // $add_head = mb_convert_encoding("From: tnksys@nitto-kohki.co.jp\r\nReply-To: tnksys@nitto-kohki.co.jp", 'JIS', 'EUC-JP');       // EUC-JPをJISへ変換
            $add_head = "From: tnksys@nitto-kohki.co.jp\r\nReply-To: tnksys@nitto-kohki.co.jp";
            /***** 2005/05/17 ADD END *****/
            if ($_SESSION['User_ID'] == '010561') {
                // mail($to_addres, $subject, $message, $add_head);
                mb_send_mail($to_addres, $subject, $message, $add_head);
                // `/bin/cat /tmp/dev_req_submit | /usr/bin/nkf -Ej | /bin/mail -s 'プログラム開発依頼 $bangou $iraibi $iraisya' tnksys@nitto-kohki.co.jp `;
            } else {
                $to_addres .= ',norihisa_ooya@nitto-kohki.co.jp';
                // mail($to_addres, $subject, $message, $add_head);
                mb_send_mail($to_addres, $subject, $message, $add_head);
                // `/bin/cat /tmp/dev_req_submit | /usr/bin/nkf -Ej | /bin/mail -s 'プログラム開発依頼 $bangou $iraibi $iraisya' tnksys@nitto-kohki.co.jp , ytetsuka@nitto-kohki.co.jp `;
            }
                            // メール送信終了
            header('Location: ' . H_WEB_HOST . DEV . 'dev_req_submit_dsp.php');
            exit();
        } else {
            execQuery('rollback');
            disConnectDB();
            $_SESSION['s_sysmsg'] = '依頼書の送信に失敗しました。<br>管理者に連絡して下さい。';
            header('Location: ' . H_WEB_HOST . DEV_MENU);
            exit();
        }
    }
} else {
    $_SESSION['s_sysmsg'] = '受付番号取得に失敗しました。<br>管理者に連絡して下さい。';
    header('Location: ' . H_WEB_HOST . DEV_MENU);
    exit();
}
ob_end_flush();  //Warning: Cannot add header の対策のため追加。2002/01/21
?>
