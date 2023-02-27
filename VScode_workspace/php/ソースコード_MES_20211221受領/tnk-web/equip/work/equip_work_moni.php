<?php
//////////////////////////////////////////////////////////////////////////////
// 組立設備稼動管理システムの 現在運転一覧 表示  フレーム定義               //
// Copyright (C) 2021-2021 norihisa_ooya@nitto-kohki.co.jp                  //
// Changed history                                                          //
// 2021/03/26 Created  equip_work_moni.php                                  //
// 2021/06/22 メニューが違うので6工場以外は表示しない様に変更          大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');        // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);     // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../../function.php');        // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');        // TNK に依存する部分の関数を require_once している
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
require_once ('../../ControllerHTTP_Class.php');    // TNK 全共通 MVC Controller Class
require_once ('../equip_function.php');             // 設備関係の共通 function
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader();                   // 認証チェックも行っている
////////////// サイト設定
$menu->set_site(40, 9);                     // site_index=40(設備メニュー) site_id=9(運転中一覧)

/////////// 工場区分と工場名を取得する
$fact_name = getFactory($factoryList);

////////////// リターンアドレス設定
//$menu->set_RetUrl(EQUIP_MENU2);              // 通常は指定する必要はない
$menu->set_RetUrl(EQUIP_MENU3);              // 通常は指定する必要はない

//////////// フレームの呼出先のアクション(frame)名とアドレス設定
$menu->set_frame('Header', EQUIP2 . 'work/equip_work_moniHeader.php');
$menu->set_frame('List'  , EQUIP2 . 'work/equip_work_moniList.php');
//////////// フレームの呼出先のアクション(frame)名とアドレス設定
$menu->set_frame('Header2', EQUIP2 . 'work/equip_work_monigraphHeader.php');
$menu->set_frame('List2'  , EQUIP2 . 'work/equip_work_monigraphList.php');
// フレーム版は $menu->set_action()ではなく$menu->set_frame()を使用する

///// GET/POSTのチェック&設定
if (isset($_REQUEST['factory'])) {
    $parm = '?factory=' . $_REQUEST['factory'];
    $factory = $_REQUEST['factory'];
    $_SESSION['factory'] = $factory;
} else {
    ///// リクエストが無ければセッションから工場区分を取得する。(通常はこのパターン)
    $factory = @$_SESSION['factory'];
    $parm = "?factory={$factory}";
}
///// GET/POSTのチェック&設定
if (isset($_REQUEST['view'])) {
    $parm = '?view=' . $_REQUEST['view'];
    $view = $_REQUEST['view'];
    $_SESSION['view'] = $view;
} elseif (isset($_SESSION['view'])) {
    ///// リクエストが無ければセッションから形式を取得する。(通常はこのパターン)
    $view = @$_SESSION['view'];
    $parm = "?view={$view}";
} else {
    $view = "一覧";
    $parm = "?view={$view}";
}
if($view == 'グラフ') {
    //////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
    $menu->set_title('現在運転中 一覧表（グラフ）');
} else {
    //////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
    $menu->set_title('現在運転中 一覧表（リスト）');
}

//////////// ブラウザーのキャッシュ対策用
$uniq = $menu->set_useNotCache('alloConf');

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<style type='text/css'>
<!--
.pt8 {
    font-size:   0.6em;
    font-weight: normal;
    font-family: monospace;
}
.pt9 {
    font-size:   0.7em;
    font-weight: normal;
    font-family: monospace;
}
.pt10 {
    font-size:   0.8em;
    font-weight: normal;
    font-family: monospace;
}
.pt10b {
    font-size:   0.8em;
    font-weight: bold;
    font-family: monospace;
}
.pt11b {
    font-size:   0.9em;
    font-weight: bold;
    font-family: monospace;
}
.pt12b {
    font-size:   1.0em;
    font-weight: bold;
    font-family: monospace;
}
.pt13b {
    font-size:   1.1em;
    font-weight: bold;
    /* font-family: monospace; */
}
.pt14b {
    font-size:   1.2em;
    font-weight: bold;
    /* font-family: monospace; */
}
select {
    background-color:   teal;
    color:              white;
}
.sub_font {
    font-size:      0.95em;
    font-weight:    bold;
    font-family:    monospace;
}
.pick_font {
    font-size:      0.75em;
    font-weight:    bold;
    font-family: monospace;
}
th {
    font-size:      0.95em;
    font-weight:    bold;
    font-family:    monospace;
    color:              blue;
    background-color:   yellow;
}
.item {
    position: absolute;
    top:    90px;
    left:    0px;
}
-->
</style>
<form name='MainForm' method='post'>
    <input type='hidden' name='select' value=''>
</form>
<script language="JavaScript">
<!--
/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus() {
    // document.mac_form.factory.focus();      // カーソルキーで工場を移動きるようにする
}
    function parts_upper(obj) {
    obj.parts_no.value = obj.parts_no.value.toUpperCase();
    return true;
}
function win_open(url) {
    var w = 800;
    var h = 600;
    var left = (screen.availWidth  - w) / 2;
    var top  = (screen.availHeight - h) / 2;
    window.open(url, 'view_win', 'width='+w+',height='+h+',scrollbars=yes,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
}
// -->
</script>
</head>
<body style='overflow-y:hidden;'>
<center>
<?php echo $menu->out_title_border() ?>
    
    <!----------------- 見出しを表示 ------------------------>
    <table bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='1'>
        <tr><td> <!-- ダミー(デザイン用) -->
    <table class='winbox_field' width='100%' border='0' cellspacing='0' cellpadding='1'>
        <tr class='sub_font'>
            <td class='winbox'>
                <input style='font-size:0.8em; font-weight:bold; color:blue;' type='submit' name='list_help' value='説明' onClick='win_open("list_help.html")'>
            </td>
            <td class='winbox' align='center' width='100'>
                <form name='mac_form' method='post' action='<?php echo $menu->out_self() ?>'>
                <select name='factory' class='ret_font' onChange='document.mac_form.submit()'>
                    <!--
                    <option value='' <?php if($factory=='') echo 'selected'; ?>>全工場</option>
                    <option value='1' <?php if($factory==1) echo 'selected'; ?>>１工場</option>
                    <option value='2' <?php if($factory==2) echo 'selected'; ?>>２工場</option>
                    <option value='4' <?php if($factory==4) echo 'selected'; ?>>４工場</option>
                    <option value='5' <?php if($factory==5) echo 'selected'; ?>>５工場</option>
                    -->
                    <option value='6' <?php if($factory==6) echo 'selected'; ?>>６工場</option>
                    <!--
                    <option value='7' <?php if($factory==7) echo 'selected'; ?>>７工場</option>
                    -->
                </select>
                </form>
            </td>
            <td class='winbox'>
                <form action='<?php echo $menu->out_self()?>' method='get' target='_self'>
                    <input style='font-size:0.8em; color:blue;' type='submit' name='reload' value='再表示'>
                        <input type='hidden' name='factory' value='<?php echo $factory?>'>
                </form>
            </td>
            <td class='winbox'>
                <form action='equip_work_monimap.php' method='get' target='_self'>
                    <input style='font-size:0.8empt; color:blue;' type='submit' name='map_view' value='レイアウト'>
                    <input type='hidden' name='factory' value='<?php echo $factory?>'>
                </form>
            </td>
            <?php
            if ($_SESSION['User_ID'] == '300144') {
                if ($view == 'グラフ') {
            ?>
            <td class='winbox'>
                <form action='<?php echo $menu->out_self()?>' method='get' target='_self'>
                    <input style='font-size:0.8empt; color:blue;' type='submit' name='view' value='一覧'>
                    <input type='hidden' name='factory' value='<?php echo $factory?>'>
                </form>
            </td>
                <?php
                } else {
                ?>
            <!--
            <td class='winbox'>
                <form action='<?php echo $menu->out_self()?>' method='get' target='_self'>
                    <input style='font-size:0.8empt; color:blue;' type='submit' name='view' value='グラフ'>
                    <input type='hidden' name='factory' value='<?php echo $factory?>'>
                </form>
            </td>
            -->
                <?php
                }
                ?>
            <?php
            }
            ?>
        </tr>
    </table>
        </td></tr>
    </table> <!-- ダミーEnd -->
    <?php
    if ($view == 'グラフ') {
    ?>
    <iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='<?php echo $menu->out_frame('Header2') . $parm ?>' name='header' align='center' width='100%' height='30' title='項目'>;
        項目を表示しています。
    </iframe>
    <iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='<?php echo $menu->out_frame('List2') . $parm ?>' name='list' align='center' width='100%' height='80%' title='一覧'>
        一覧を表示しています。
    </iframe>
    <?php
    } else {
    ?>
    <iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='<?php echo $menu->out_frame('Header') . $parm ?>' name='header' align='center' width='100%' height='30' title='項目'>;
        項目を表示しています。
    </iframe>
    <iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='<?php echo $menu->out_frame('List') . $parm ?>' name='list' align='center' width='100%' height='80%' title='一覧'>
        一覧を表示しています。
    </iframe>
    <?php
    }
    ?>
</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php ob_end_flush(); // 出力バッファをgzip圧縮 END ?>
