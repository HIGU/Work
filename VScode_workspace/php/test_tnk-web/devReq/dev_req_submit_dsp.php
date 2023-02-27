<?php
//////////////////////////////////////////////////////////////////////////
// プログラム開発依頼書 送信結果フォーム                                //
// 2002/02/12 Copyright(C)2002-2003 Kobayashi tnksys@nitto-kohki.co.jp  //
// 変更経歴                                                             //
// 2002/08/09   register_globals = Off 対応                             //
// 2003/12/12 defineされた定数でディレクトリとメニュー名を使用する      //
//////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting',E_ALL);   // E_ALL='2047' debug 用
// ini_set('display_errors','1');      // Error 表示 ON debug 用 リリース後コメント
session_start();                    // ini_set()の次に指定すること Script 最上行
require_once ("../function.php");
// require("../define.php");
require_once ("../tnk_func.php");
$sysmsg = $_SESSION["s_sysmsg"];
$_SESSION["s_sysmsg"] = NULL;
access_log();                       // Script Name は自動取得
// $_SESSION["dev_req_submit_dsp"] = date("H:i");
if ( !isset($_SESSION["User_ID"]) || !isset($_SESSION["Password"]) || !isset($_SESSION["Auth"]) ) {
    $_SESSION["s_sysmsg"] = "認証されていないか認証期限が切れました。Login し直して下さい。";
    header("Location: http:" . WEB_HOST . "index1.php");
    exit();
}

?>
<!DOCTYPE html>
<HTML>
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset=UTF-8">
<META http-equiv="Content-Style-Type" content="text/css">
<TITLE>TNK 開発依頼書 送信 完了</TITLE>
<style type="text/css">
<!--
select      {background-color:teal; color:white;}
textarea        {background-color:black; color:white;}
input.sousin    {background-color:red;}
input.text      {background-color:black; color:white;}
.pt         {font-size:11pt;}
-->
</style>
</HEAD>
<BODY>
<table width=100%>
    <tr><td bgcolor="#003e7c" align="center">
        <font color="#ffffff" size="4">開発依頼書 送信 完了</font>
    </td></tr>
</table>
<table width=100%>
    <hr color="navy">
</table>
<table width=100%>
    <tr>
    <form action='<?php echo DEV_MENU ?>' method='post'>
        <td align='center'><input type="submit" name="dev_chk_submit" value="戻る" ></td>
    </form>
    </tr>
</table>
<table width='100%' cellspacing='0' cellpadding='2' border='1' bgcolor='#e6e6fa'>
        <tr>
            <td align='center' width='20'>①</td>
            <td align='left' width='80'>依頼№</td>
            <td align='left'>
                <?php echo "<font color='red'><font size='6'><b>" . $_SESSION["s_dev_touroku"] 
                . "</b></font>番で送信されました。照会画面で番号を入力して確認して下さい。</font>\n" ?>
            </td>
        </tr>
        <tr>
            <td align='center' width='20'>②</td>
            <td align='left'>依頼日</td>
            <td align='left'>
                <?php $iraibi=date("Y-m-d");echo $iraibi; ?>
            </td>
        </tr>
        <tr>
            <td align='center' width='20'>③</td>
            <td align='left'>依頼部署</td>
            <td align="left">
                <?php
                    $query_section = "select * from section_master where sid = " . $_SESSION["s_dev_iraibusho"];
                    $res_section=array();
                    if($rows_section=getResult($query_section,$res_section))
                        print(rtrim($res_section[0][section_name]));
                    else
                        print($_SESSION["s_dev_iraibusho"]);
                ?>
            </td>
        </tr>
        <tr>
            <td align='center' width='20'>④</td>
            <td align='left'>依頼者</td>
            <td align="left">
                依頼者の社員№
                <?php
                    print $_SESSION["s_dev_iraisya"] . "\n";
                    $query_user = "select name from user_detailes where uid='" . $_SESSION["s_dev_iraisya"] . "'";
                    $res_user=array();
                    if($rows_user=getResult($query_user,$res_user))
                        print("<font size='3'>" . rtrim($res_user[0][name]) . "</font></td>\n");
                    else
                        print("--------\n");
                ?>
            </td>
        </tr>
        <tr>
            <td align='center' width='20'>⑤</td>
            <td align='left' width='80'>目的又はタイトル</td>
            <td align='left'>
                <?php
                    print $_SESSION["s_dev_mokuteki"] . "\n";
                ?>
            </td>
        </tr>
        <tr>
            <td align='center' width='20'>⑥</td>
            <td align='left' width='80'>内  容</td>
            <td align='left'>
                <?php
                    print $_SESSION["s_dev_naiyou"] . "\n";
                ?>
            </td>
        </tr>
        <tr>
            <td align='center' width='20'>⑦</td>
            <td align='left' nowrap>予想効果</td>
            <td align='left'>
                <?php
                    if($_SESSION["s_dev_yosoukouka"] == "")
                        print("-----\n");
                    else
                        print $_SESSION["s_dev_yosoukouka"] . " 分／年\n";
                ?>
            </td>
        </tr>
        <tr>
            <td align='center' width='20'>⑧</td>
            <td align='left'>計算式又は備考</td>
            <td align='left'>
                <?php
                    if($_SESSION["s_dev_bikou"] == "")
                        print("-----\n");
                    else
                        print($_SESSION["s_dev_bikou"] . "\n");
                ?>
            </td>
        </tr>
    </form>
</table>
<table width=100%>
    <form action='<?php echo DEV_MENU ?>' method='post'>
        <tr><td align='center'><input type="submit" name="dev_chk_submit" value="戻る" ></td></tr>
    </form>
</table>
</BODY>
</HTML>
