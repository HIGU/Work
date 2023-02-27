<?php
//////////////////////////////////////////////////////////////////////////////
// 総材料費の登録  計画番号の入力・確認 form                                //
// Copyright (C) 2003-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2003/12/15 Created   metarialCost_entry_plan.php                         //
// 2004/05/12 サイトメニュー表示・非表示 ボタン追加 menu_OnOff($script)追加 //
// 2005/02/08 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2005/03/17 alert_java()を使用のため entry_form.plan.select()を復活       //
// 2007/06/12 プログラム変更による呼出先変更materialCost_entry_main.php 大谷//
// 2007/09/28 暫定で計画番号'Z'のものはmiitemをチェックしないロジックを追加 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');        // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');        // TNK に依存する部分の関数を require_once している
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(30, 21);                    // site_index=30(生産メニュー) site_id=21(総材料費の登録 計画番号)
////////////// リターンアドレス設定
// $menu->set_RetUrl(INDUST_MENU);             // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('総 材 料 費 の 登 録 (計画番号)');
//////////// 表題の設定
$menu->set_caption('計画番号を入力');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('総材料費登録',   INDUST . 'material/material_entry/materialCost_entry_main.php');

//////////// JavaScript Stylesheet File を必ず読み込ませる
$uniq = uniqid('target');

////////////// 自分のポストデータをチェック
if (isset($_POST['plan']) && substr($_POST['plan'], 0, 1) == 'Z' ) {
    $plan = $_POST['plan'];
    $query = "
        SELECT assy_no, midsc FROM material_cost_header LEFT OUTER JOIN miitem ON (mipn=assy_no) WHERE plan_no = '{$_POST['plan']}'
    ";
    if (getResult2($query, $res) <= 0) {
        $_SESSION['s_sysmsg'] = "{$plan}：では登録されていません！";
        $parts_no  = '&nbsp;';
        $assy_name = "<font color='red'>未 登 録</font>";
        $kansei    = '&nbsp;';
        $kouji_no  = '&nbsp;';
    } else {
        $parts_no  = $res[0][0];
        $assy_name = $res[0][1];
        $kansei    = '&nbsp;';
        $kouji_no  = '&nbsp;';
        $_SESSION['plan_no']  = $plan;       // 計画番号の確定(entryが押されたらこれで処理)
        $_SESSION['assy_no']  = $parts_no;
    } 
} elseif (isset($_POST['plan'])) {
    $plan = $_POST['plan'];
    $query = "select parts_no, midsc, kansei, note15
                from
                    assembly_schedule
                left outer join
                    miitem
                on (parts_no=mipn)
                where plan_no='{$plan}'";
    $res = array();
    if (getResult($query, $res) <= 0) {
        $_SESSION['s_sysmsg'] = "{$plan}：では登録されていません！";
        $parts_no  = '&nbsp;';
        $assy_name = "<font color='red'>未 登 録</font>";
        $kansei    = '&nbsp;';
        $kouji_no  = '&nbsp;';
    } else {
        $parts_no  = $res[0][0];
        $assy_name = $res[0][1];
        $kansei    = $res[0][2];
        $kouji_no  = $res[0][3];
        $_SESSION['plan_no']  = $plan;       // 計画番号の確定(entryが押されたらこれで処理)
        $_SESSION['assy_no']  = $parts_no;
    }
} else {
    $plan = '';
}

////////////// 登録ボタンが押された(entryボタン)
if (isset($_POST['entry'])) {
    header('Location: ' . H_WEB_HOST . $menu->out_action('総材料費登録'));  // 構成部品の登録へ
    exit();
}

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java()?>
<?php echo $menu->out_css()?>

<!--    ファイル指定の場合
<script language='JavaScript' src='template.js?<?php echo $uniq ?>'></script>
-->

<script language="JavaScript">
<!--
/* 入力文字が数字かどうかチェック */
function isDigit(str) {
    var len=str.length;
    var c;
    for (i=1; i<len; i++) {
        c = str.charAt(i);
        if ((c < "0") || (c > "9")) {
            return true;
        }
    }
    return false;
}

function chk_plan_entry(obj) {
    obj.plan.value = obj.plan.value.toUpperCase();
    if (obj.plan.value.length != 0) {
        if (obj.plan.value.length != 8) {
            alert("計画番号の桁数は８桁です。");
            obj.plan.focus();
            obj.plan.select();
            return false;
        } else {
            return true;
        }
    }
    alert('計画番号が入力されていません！');
    obj.plan.focus();
    obj.plan.select();
    return false;
}

/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus() {
    document.entry_form.plan.focus();      // 初期入力フォームがある場合はコメントを外す
    document.entry_form.plan.select();
}
// -->
</script>

<!-- スタイルシートのファイル指定をコメント HTMLタグ コメントは入れ子に出来ない事に注意
<link rel='stylesheet' href='template.css?<?php echo $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
.pt11b {
    font-size:      11pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pt12b {
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
}
.plan_font {
    font-size:      13pt;
    font-weight:    bold;
    text-align:     left;
    font-family:    monospace;
}
.entry_font {
    font-size:      11pt;
    font-weight:    bold;
    color:          red;
}
.winbox {
    border-style:           solid;
    border-width:           1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
    /* background-color:#d6d3ce; */
}
.winbox_field {
    border-style:           solid;
    border-width:           1px;
    border-top-color:       #bdaa90;
    border-left-color:      #bdaa90;
    border-right-color:     #FFFFFF;
    border-bottom-color:    #FFFFFF;
    /* background-color:#d6d3ce; */
}
-->
</style>
</head>
<body onLoad='set_focus()' style='overflow:hidden;'>
    <center>
<?php echo $menu->out_title_border()?>
        
        <br>
        
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <form name='entry_form' method='post' action='<?php echo $menu->out_self() ?>' onSubmit='return chk_plan_entry(this)'>
                <tr>
                    <td class='winbox' nowrap align='center'>
                        <div class='caption_font'><?php echo $menu->out_caption() ?></div>
                    </td>
                    <td class='winbox' width='300' nowrap align='center'>
                        <input class='plan_font' type='text' name='plan' value='<?php echo $plan ?>' size='8' maxlength='8'>
                        <input class='pt11b' type='submit' name='conf' value='確認'>
                    </td>
                </tr>
                <?php if ($plan != '') { ?>
                <tr>
                    <td class='winbox' nowrap align='center'>
                        <div class='caption_font'>製品番号</div>
                    </td>
                    <td class='winbox' nowrap align='left'>
                        <div class='pt12b'><?php echo $parts_no ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap align='center'>
                        <div class='caption_font'>製 品 名</div>
                    </td>
                    <td class='winbox' width='300' nowrap align='left'>
                        <div class='pt12b'><?php echo $assy_name ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap align='center'>
                        <div class='caption_font'>完 成 数</div>
                    </td>
                    <td class='winbox' nowrap align='left'>
                        <div class='pt12b'><?php echo $kansei ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap align='center'>
                        <div class='caption_font'>工事番号</div>
                    </td>
                    <td class='winbox' nowrap align='left'>
                        <div class='pt12b'><?php echo $kouji_no ?></div>
                    </td>
                </tr>
                    <?php if ($parts_no != '' && $parts_no != '&nbsp;') { ?>
                    <tr>
                        <td class='winbox' colspan='2' nowrap align='center'>
                            <input class='entry_font' type='submit' name='entry' value='登録'>
                        </td>
                    </tr>
                    <?php } ?>
                <?php } ?>
            </form>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
