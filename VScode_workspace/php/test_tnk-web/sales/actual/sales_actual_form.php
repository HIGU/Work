<?php
//////////////////////////////////////////////////////////////////////////////
// 栃木日東工器 売上 実績 照会 条件選択フォーム                             //
// Copyright(C) 2020-2020 Ryota.Waki tnksys@nitto-kohki.co.jp               //
// Changed history                                                          //
// 2020/12/17 Created   sales_form.php → sales_actual_form.php             //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);              // E_ALL='2047' debug 用
// ini_set('display_errors', '1');              // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1'); // zend 1.X コンパチ php4の互換モード
// ini_set('implicit_flush', 'off');            // echo print で flush させない(遅くなるため) CLI版
// ini_set('max_execution_time', 1200);         // 最大実行時間=20分 WEB CGI版
ob_start('ob_gzhandler');                       // 出力バッファをgzip圧縮
session_start();                                // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');
require_once ('../../tnk_func.php');
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site( 1, 11);                    // site_index=01(売上メニュー) site_id=11(売上実績明細)
////////////// リターンアドレス設定
// $menu->set_RetUrl(SYS_MENU);                // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('売 上 実 績 条 件 設 定');
//////////// 表題の設定
$menu->set_caption('下記の必要な条件を入力又は選択して下さい。');
//////////// 呼出先のaction名とアドレス設定
if( $_SESSION['User_ID'] == "300667" ) {
//$menu->set_action('売上実績',   SALES . 'actual/sales_actual_set_plan_test.php');    // 売上予定をＤＢに保存する
$menu->set_action('売上実績',   SALES . 'actual/sales_actual_view.php');
} else {
$menu->set_action('売上実績',   SALES . 'actual/sales_actual_view.php');
}
//$menu->set_action('売上実績',   SALES . 'actual/sales_actual_set_plan.php');    // 売上予定をＤＢに保存する

//////////// ブラウザーのキャッシュ対策用
$uniq = $menu->set_useNotCache('target');

/////////////// 受け渡し変数の初期化
if ( isset($_SESSION['s_div']) ) {
    $div = $_SESSION['s_div'];
} else {
    $div = '';
}

if ( isset($_SESSION['s_target_ym']) ) {
    $target_ym = $_SESSION['s_target_ym'];
} else {
    $target_ym = '';
}

///// 対象年月のHTML <select> option の出力
function getTarget_ymValues($target_ym)
{
    $query = "
                SELECT
                        DISTINCT target_ym
                FROM (
                        SELECT
                                SUBSTRING(kanryou, 0, 7) AS target_ym
                        FROM (
                                SELECT
                                        DISTINCT kanryou
                                FROM
                                        month_first_sales_plan
                              ) AS a -- 完了予定日の重複削除
                      ) AS b -- 完了予定日の年月のみを抽出
                ORDER BY
                            target_ym DESC
    ";

    $res = array();
    $rows = getResult2($query, $res);

    // 初期化
    $option = "";
    $temp_ym = "";
    for ($i=0; $i<$rows; $i++) {
        $next_ym = substr($res[$i][0],0,4) . '/' . substr($res[$i][0],4,2);
        if( $temp_ym == $next_ym ) continue;
        $temp_ym = $next_ym;
        $option .= "<option value='{$temp_ym}'";
        if( $target_ym == $temp_ym ) {
            $option .= " selected>{$temp_ym}</option>\n";
        } else {
            $option .= ">{$temp_ym}</option>\n";
        }
    }
    return $option;
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
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java()?>
<?php echo $menu->out_css()?>
<?php echo $menu->out_jsBaseClass() ?>
<script type='text/javascript' src='./sales_actual_form.js?<?php echo $uniq ?>'>
</script>

<script type='text/javascript' language='JavaScript'>
<!--
/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus(){
//    document.form_name.element_name.focus();      // 初期入力フォームがある場合はコメントを外す
//    document.form_name.element_name.select();
}
// -->
</script>

<!-- スタイルシートのファイル指定をコメント HTMLタグ コメントは入れ子に出来ない事に注意
<link rel='stylesheet' href='<?php echo MENU_FORM . '?' . $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
.pt8 {
    font-size:      8pt;
    font-weight:    normal;
    font-family:    monospace;
}
.pt9 {
    font-size:      9pt;
    font-weight:    normal;
    font-family:    monospace;
}
.pt10 {
    font-size:      10pt;
    font-weight:    normal;
    font-family:    monospace;
}
.pt10b {
    font-size:      10pt;
    font-weight:    bold;
    font-family:    monospace;
}
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
th {
    background-color:   yellow;
    color:              blue;
    font-size:          10pt;
    font-weight:        bold;
    font-family:        monospace;
}
td {
    font-size: 10pt;
}
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
    /* background-color:#d6d3ce; */
}
.winbox_field {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #bdaa90;
    border-left-color:      #bdaa90;
    border-right-color:     #FFFFFF;
    border-bottom-color:    #FFFFFF;
    /* background-color:#d6d3ce; */
}
a:hover {
    background-color:   blue;
    color:              white;
}
a {
    color:   blue;
}
body {
    background-image:url(<?php echo IMG ?>t_nitto_logo4.png);
    background-repeat:no-repeat;
    background-attachment:fixed;
    background-position:right bottom;
}
-->
</style>
</head>
</style>
<body onLoad='document.uri_form.div.focus();' style='overflow:hidden;'>
    <center>
<?php echo $menu->out_title_border()?>
        
        <form name='uri_form' action='<?php echo $menu->out_action('売上実績') ?>' method='post' onSubmit='return true;'>
            <!----------------- ここは 本文を表示する ------------------->
            <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='5'>
                <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table class='winbox_field' width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                        <!--  bgcolor='#ffffc6' 薄い黄色 --> 
                    <td class='winbox' style='background-color:yellow; color:blue;' colspan='2' align='center'>
                        <div class='caption_font'><?php echo $menu->out_caption(), "\n"?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        製品グループを選択して下さい。
                    </td>
                    <td class='winbox' align='center'>
                        <select name="div">
                            <option value="S"<?php if($div=="S") echo("selected"); ?>>カプラ特注</option>
                            <option value="D"<?php if($div=="D") echo("selected"); ?>>カプラ標準</option>
                            <option value="L"<?php if($div=="L") echo("selected"); ?>>リニア全体</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        年月を選択して下さい。
                    </td>
                    <td class='winbox' align='center'>
                        <select name="target_ym">
                            <?php echo getTarget_ymValues($target_ym) ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' colspan='2' align='center'>
                        <input type="submit" name="照会" value="実行" >
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ダミーEnd ------------------>
            <font class='pt9'><br>※ 2021年１月より月初予定の自動保存を開始。<br><br>2021年８月の月初予定自動保存に失敗!![リニア全体]のみ修正実施</font>
        </form>
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
