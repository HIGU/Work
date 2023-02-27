<?php
//////////////////////////////////////////////////////////////////////////////
// 総材料費の登録  Assy番号(親番号)のform                                   //
// Copyright(C) 2003-2020 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// 変更経歴                                                                 //
// 2003/12/15 新規作成  metarialCost_entry_assy.php                         //
// 2004/05/12 サイトメニュー表示・非表示 ボタン追加 menu_OnOff($script)追加 //
// 2007/09/14 最新総材料費の登録用に 計画番号の頭２桁に 'ZZ' で連番登録     //
// 2007/09/18 assy_no LIKE 'ZZ%' → plan_no LIKE 'ZZ%' ミス訂正             //
// 2007/10/04 最新登録用 計画番号を表示追加                                 //
// 2008/03/12 最新登録の同時入力を可能にする為、計画番号の命名規則を変更    //
//            'Z'+AssyNo.の先頭１字+AssyNoの-前の数字部分+連番              //
//            （'LA70356-0'なら'ZL703560'となる→2回目の登録はZL703561）    //
//            枝版違いの製品の場合は同時入力付加(旧製品になるので問題       //
//            はないかと思います。)                                    大谷 //
// 2020/03/03 9の後は0になるように変更（桁あふれの為）                 大谷 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);// E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');       // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);    // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');        // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');        // TNK に依存する部分の関数を require_once している
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(30, 999);                   // site_index=30(生産メニュー) site_id=21(総材料費の登録 計画番号)
////////////// リターンアドレス設定
// $menu->set_RetUrl(INDUST_MENU);             // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('総 材 料 費 の 登 録 (製品番号)');
//////////// 表題の設定
$menu->set_caption('製品番号を入力');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('総材料費登録',   INDUST . 'material/material_entry/materialCost_entry_main.php');

//////////// JavaScript Stylesheet File を必ず読み込ませる
$uniq = uniqid('target');

////////////// 自分のポストデータをチェック
if (isset($_REQUEST['assy'])) {
    $assy = $_REQUEST['assy'];
    $query = "SELECT midsc FROM miitem WHERE mipn='{$assy}'";
    $new_plan = substr($assy, 0, 1) . substr($assy, 2, 5);  // 最新の場合の計画番号は'Z'+AssyNo.の先頭１字+AssyNoの-前の数字部分
    $check_nplan = 'Z' . $new_plan;                         // 同じ製品番号の計画が登録されているかのチェック用
    if (getUniResult($query, $assy_name) <= 0) {
        $_SESSION['s_sysmsg'] = "{$assy}：では登録されていません！";
        $assy_name = "<font color='red'>未 登 録</font>";
        $assy = '';
    } else {
        $query = "SELECT plan_no FROM material_cost_header WHERE plan_no LIKE '{$check_nplan}%' ORDER BY last_date DESC LIMIT 1";
        if (getUniResult($query, $plan) > 0) {
            if (substr($plan, 7, 1) == 9) {
                $temp_plan = 0;
            } else {
                $temp_plan = substr($plan, 7, 1) + 1;
            }
            $plan = 'Z' . $new_plan . $temp_plan;    // 最後の計画番号にインクリメント
            $_SESSION['plan_no'] = $plan;
        } else {
            $_SESSION['plan_no'] = 'Z' . $new_plan . 0;  // 初回
        }
        $_SESSION['assy_no']  = $assy;
        $menu->set_retGET('assy', $assy);
    }
} else {
    $assy = '';
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
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java()?>
<?php echo $menu->out_css()?>

<!--    ファイル指定の場合
<script type='text/javascript language='JavaScript' src='template.js?<?php echo $uniq ?>'>
</script>
-->

<script type='text/javascript' language="JavaScript">
<!--
function chk_assy_entry(obj) {
    obj.assy.value = obj.assy.value.toUpperCase();
    if (obj.assy.value.length != 0) {
        if (obj.assy.value.length != 9) {
            alert("Ａｓｓｙ番号の桁数は９桁です。");
            obj.assy.focus();
            obj.assy.select();
            return false;
        } else {
            return true;
        }
    }
    alert('Ａｓｓｙ番号が入力されていません！');
    obj.assy.focus();
    obj.assy.select();
    return false;
}

/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus(){
    document.entry_form.assy.focus();      // 初期入力フォームがある場合はコメントを外す
    // document.entry_form.assy.select();
}
// -->
</script>

<!-- スタイルシートのファイル指定をコメント HTMLタグ コメントは入れ子に出来ない事に注意 -->
<link rel='stylesheet' href='material.css?<?php echo $uniq ?>' type='text/css' media='screen'>

<style type="text/css">
<!--
-->
</style>
</head>
<body onLoad='set_focus()'>
    <center>
<?php echo $menu->out_title_border()?>
        
        <br>
        
        <table bgcolor='#d6d3ce' width='350' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <form name='entry_form' method='post' action='<?php echo $menu->out_self() ?>' onSubmit='return chk_assy_entry(this)'>
                <tr>
                    <td nowrap align='center' class='caption_font'>
                        <?php echo $menu->out_caption() . "\n" ?>
                    </td>
                </tr>
                <tr>
                    <td nowrap align='center'>
                        <input type='text' class='assy_font' name='assy' value='<?php echo $assy ?>' size='9' maxlength='9' onKeyUp='baseJS.keyInUpper(this);'>
                    </td>
                </tr>
                <?php if ($assy == '') { ?>
                <tr>
                    <td nowrap align='center' class='caption_font'>
                        <input class='pt11b' type='submit' name='conf' value='確認'>
                    </td>
                </tr>
                <?php } else { ?>
                <tr>
                    <td nowrap align='center' class='pt12b'>
                        <?php echo $assy_name ?>
                    </td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt12b'>
                        最新登録用 計画番号：<?php echo $_SESSION['plan_no'] ?>
                    </td>
                </tr>
                
                <tr>
                    <td nowrap align='center' class='caption_font'>
                        <input class='pt11b' type='submit' name='entry' value='登録'>
                    </td>
                </tr>
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
