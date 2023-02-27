<?php
//////////////////////////////////////////////////////////////////////////////
// 月次損益関係 修理・耐久損益計算書等の特記事項(コメント)入力及び登録      //
// Copyright(C) 2016 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp          //
// Changed history                                                          //
// 2016/07/25 Created   profit_loss_comment_put_ss.php                      //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',  E_ALL);         // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../function.php');           // define.php と pgsql.php を require_once している
require_once ('../tnk_func.php');           // TNK に依存する部分の関数を require_once している
require_once ('../MenuHeader.php');         // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
// $menu->set_site(10, 7);                     // site_index=10(損益メニュー) site_id=7(月次損益)
//////////// 表題の設定
$menu->set_caption('栃木日東工器(株)');
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('抽象化名',   PL . 'address.php');

if (account_group_check() == FALSE) {
    $_SESSION['s_sysmsg'] = 'あなたは許可されていません。<br>管理者に連絡して下さい。';
    header('Location: http:' . WEB_HOST . 'menu.php');
    exit();
}

///// 期・月の取得
$ki = 22
$tuki = 11

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title("第{$ki}期　{$tuki}月度　月次損益計算書の特記事項の入力");

///// 対象当月
$yyyymm = 202211
///// 対象前月
if (substr($yyyymm,4,2)!=01) {
    $p1_ym = $yyyymm - 1;
} else {
    $p1_ym = $yyyymm - 100;
    $p1_ym = $p1_ym + 11;
}

if (!isset($_POST['touroku'])) {     // データ入力
    ////////// 登録済みならば特記事項 取得
    $query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='修理損益計算書'", $yyyymm);
    if (getUniResult($query,$comment_ss) <= 0) {
        $comment_sc = "";
    }
    $query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='耐久損益計算書'", $yyyymm);
    if (getUniResult($query,$comment_st) <= 0) {
        $comment_sl = "";
    }
    $query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='試修全体損益計算書'", $yyyymm);
    if (getUniResult($query,$comment_s) <= 0) {
        $comment_s = "";
    }
} else {
    $query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='修理損益計算書'", $yyyymm);
    if (getUniResult($query,$comment_ss) <= 0) {
        $query = sprintf("insert into act_comment_history (pl_bs_ym, item, comment) values (%d, '修理損益計算書', '%s')", $yyyymm, $_POST['comment_ss']);
        query_affected($query);
    } else {
        $query = sprintf("update act_comment_history set comment='%s' where pl_bs_ym=%d and item='修理損益計算書'", $_POST['comment_ss'], $yyyymm);
        query_affected($query);
    }
    $query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='耐久損益計算書'", $yyyymm);
    if (getUniResult($query,$comment_st) <= 0) {
        $query = sprintf("insert into act_comment_history (pl_bs_ym, item, comment) values (%d, '耐久損益計算書', '%s')", $yyyymm, $_POST['comment_st']);
        query_affected($query);
    } else {
        $query = sprintf("update act_comment_history set comment='%s' where pl_bs_ym=%d and item='耐久損益計算書'", $_POST['comment_st'], $yyyymm);
        query_affected($query);
    }
    $query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='試修全体損益計算書'", $yyyymm);
    if (getUniResult($query,$comment_s) <= 0) {
        $query = sprintf("insert into act_comment_history (pl_bs_ym, item, comment) values (%d, '試修全体損益計算書', '%s')", $yyyymm, $_POST['comment_s']);
        query_affected($query);
    } else {
        $query = sprintf("update act_comment_history set comment='%s' where pl_bs_ym=%d and item='試修全体損益計算書'", $_POST['comment_s'], $yyyymm);
        query_affected($query);
    }
    $_SESSION["s_sysmsg"] .= sprintf("<font color='yellow'>損益計算書 特記事項入力完了<br>第 %d期 %d月</font>",$ki,$tuki);
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_pl_act_ss.php");
    exit();
}

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>
<?= $menu->out_jsBaseClass() ?>

<script type='text/javascript' language='JavaScript'>
<!--
function set_focus(){
    document.comment_form.comment_sc.focus();
    // document.comment_form.comment_c.select();
}
// -->
</script>
<style type='text/css'>
<!--
select {
    background-color:teal;
    color:white;
}
textarea {
    background-color:white;
    color:black;
}
input.sousin {
    background-color:red;
}
input.text {
    background-color:black;
    color:white;
}
.pt9 {
    font-size: 9pt;
    font-family: monospace;
}
.pt11 {
    font-size: 11pt;
    font-family: monospace;
}
.pt11b {
    font:bold 11pt;
    font-family: monospace;
}
.pt12b {
    font:bold 12pt;
    font-family: monospace;
}
.title-font {
    font:bold 16.5pt;
    font-family: monospace;
}
.today-font {
    font-size: 10.5pt;
    font-family: monospace;
}
.right{
    text-align:right;
    font:bold 12pt;
    font-family: monospace;
}
.margin0 {
    margin:0%;
}
textarea {
    font-size: 10.0pt;
    font-family: monospace;
}
.save_button {
    font:bold 12pt;
    font-family: monospace;
    color:red;
}
-->
</style>
</head>
<body onLoad='set_focus();'>
    <center>
<?= $menu->out_title_border() ?>
        
        <form name='comment_form' action='profit_loss_comment_put_ss.php' method='post'>
            <table bgcolor='#d6d3ce' cellpadding='5' border='1'>
                <tr>
                    <td align='center' bgcolor='#e6e6e6' class='pt12b'>
                        修理特記事項
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#e6e6e6' class='pt12b'>
                        <textarea name='comment_ss' cols='114' rows='5' wrap='hard'><?php echo $comment_ss ?></textarea>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#e6e6e6' class='pt12b'>
                        耐久特記事項
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#e6e6e6' class='pt12b'>
                        <textarea name='comment_st' cols='114' rows='5' wrap='hard'><?php echo $comment_st ?></textarea>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#e6e6e6' class='pt12b'>
                        試験・修理全体特記事項
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#e6e6e6' class='pt12b'>
                        <textarea name='comment_s' cols='114' rows='5' wrap='hard'><?php echo $comment_s ?></textarea>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#e6e6e6'>
                        <input type='submit' name='touroku' value='保存' class='save_button'>
                    </td>
                </tr>
            </table>
        </form>
    </center>
</body>
<?=$menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
