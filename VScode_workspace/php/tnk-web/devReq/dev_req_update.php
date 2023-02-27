<?php
//////////////////////////////////////////////////////////////////////////
// プログラム開発依頼書 照会&編集                                       //
// 2002/02/12 Copyright(C)2002-2003 Kobayashi tnksys@nitto-kohki.co.jp  //
// 変更経歴                                                             //
// 2002/08/09 register_globals = Off 対応                               //
// 2003/12/12 defineされた定数でディレクトリとメニュー名を使用する      //
//////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting',E_ALL);   // E_ALL='2047' debug 用
// ini_set('display_errors','1');      // Error 表示 ON debug 用 リリース後コメント
session_start();                    // ini_set()の次に指定すること Script 最上行
$_SESSION['s_rec_No'] = $_SESSION['s_dev_current_rec'];
require_once ("../function.php");
// include("../define.php");
access_log();       // Script Name は自動取得
if($_SESSION['Auth'] >= 3){
    if($_POST['yuusendo']=="") $_POST['yuusendo'] = NULL;
    if($_POST['sagyouku']=="") $_POST['sagyouku'] = NULL;
    if($_POST['sintyoku']=="") $_POST['sintyoku'] = NULL;
    if($_POST['kousuu']=="") $_POST['kousuu'] = 0;
    if($_POST['kanryou']=="") $_POST['kanryou'] = "1970-01-01";
    if($_POST['tantou']=="") $_POST['tantou'] = NULL;
    $_POST['tantou'] = ltrim($_POST['tantou']);
    $update_qry  = "update dev_req set 依頼日='" . $_POST['iraibi'] . "', 依頼部署=" . $_POST['iraibusho'] . ", 依頼者='" . $_POST['iraisya'] . "',目的='" . $_POST['mokuteki'] . "',
        内容='" . $_POST['naiyou'] . "',優先度='" . $_POST['yuusendo'] . "',作業区='". $_POST['sagyouku'] . "',進捗状況='" . $_POST['sintyoku'] . "',開発工数=" . $_POST['kousuu'] . ",完了日='" . $_POST['kanryou'] . "',
        担当者='" . $_POST['tantou'] . "' ";
    if($_POST['yosoukouka'] != "")
        $update_qry .= ",予想効果=" . $_POST['yosoukouka'] . " ";
    else
        $update_qry .= ",予想効果=NULL ";
    if($_POST['bikou'] != "")
        $update_qry .= ",備考='" . $_POST['bikou'] . "' ";
    else
        $update_qry .= ",備考=NULL ";
    $update_qry .= "where 番号=" . $_POST['update_No'];
    if(funcConnect()){
        execQuery("begin");
        if(execQuery($update_qry)>=0){
            execQuery("commit");
            disConnectDB();
            header('Location: ' . H_WEB_HOST . DEV . 'edit_dev_req.php');
            exit();
        }else{
            execQuery("rollback");
            disConnectDB();
            $_SESSION['s_sysmsg'] = "データの変更に失敗しました。<br>データベースロジックを調べて下さい。";
            header('Location: ' . H_WEB_HOST . DEV . 'edit_dev_req.php');
        }
    }
}
$_SESSION['s_sysmsg'] = "データの変更に失敗しました。<br>権限ロジックを調べて下さい。";
header('Location: ' . H_WEB_HOST . DEV . 'edit_dev_req.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset=UTF-8">
<META http-equiv="Content-Style-Type" content="text/css">
<TITLE>TNK 開発依頼書UPDATE</TITLE>
</HEAD>
<BODY>

</BODY>
</HTML>
