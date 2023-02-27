<?php
//////////////////////////////////////////////////////////////////////////////
// 納入予定グラフ・検査仕掛明細の照会(検査の仕事量把握)  Headerフレーム     //
// Copyright (C) 2004-2017 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2021/07/07 Created  order_schedule_Header.php -> copy_pepar_Header.php   //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../../function.php');        // TNK 全共通 function
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
require_once ('copy_pepar_function.php');   // copy_pepar 関係の共通 function
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader();                   // 認証チェック0=一般以上 戻り先=セッションより タイトル未設定

////////////// サイト設定
$menu->set_site(70, 72);                   // site_index=70(品質・環境メニュー) site_id=72(部署別コピー用紙使用量)
////////////// target設定
// $menu->set_target('application');           // フレーム版の戻り先はtarget属性が必須
$menu->set_target('_parent');               // フレーム版の戻り先はtarget属性が必須

///////// パラメーターチェックと設定
if (isset($_REQUEST['tnk_ki'])) {
    $div = $_REQUEST['tnk_ki'];                // 事業部
    $_SESSION['tnk_ki'] = $_REQUEST['tnk_ki'];    // セッションに保存
} else {
    if (isset($_SESSION['tnk_ki'])) {
        $div = $_SESSION['tnk_ki'];            // Default(セッションから)
    } else {
        $div = getTnkKi();                         // 初期値(カプラ)あまり意味は無い
    }
}
if (isset($_REQUEST['input_mode'])) {
    $select = 'input_mode';                      // 未検収リスト
    $_SESSION['select'] = 'input_mode';          // セッションに保存
} elseif (isset($_REQUEST['graph'])) {
    $select = 'graph';                      // 納入予定グラフ
    $_SESSION['select'] = 'graph';          // セッションに保存
} else {
    if (isset($_SESSION['select'])) {
        $select = $_SESSION['select'];      // Default(セッションから)
    } else {
        $select = 'graph';                  // 初期値(納入予定グラフ)あまり意味は無い
    }
}

/////////// 画面解像度の取得
if ($_SESSION['site_view'] == 'on') {
    $display = 'normal';
} else {
    $display = 'wide';
}

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
if ($select == 'graph') {
    $menu->set_title('部署別コピー用紙使用量比較グラフ');
} else {
    $menu->set_title('部署別コピー用紙使用量');
}
//////////// 表題の設定
$menu->set_caption('照会内容選択');

// 現在の期を取得
$ki = getTnkKi();

// 期が変わった際、レコードがいつもないと、期のリストに反映されないので、1行は自動的に作成する。
if( !isTnkKi($ki) ) {
    insertRecord($ki, 0);
}

// 登録されているテーブルの期一覧を取得
$ki_rows = getTableKi($ki_tbl); //追加後、再度読み込む

// カラム取得
$column_row = getColumn($column);

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
<?php echo $menu->out_css() ?>
<style type='text/css'>
<!--
select {
    background-color:   teal;
    color:              white;
}
.sub_font {
    font-size:      11.5pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pick_font {
    font-size:      9.5pt;
    font-weight:    bold;
    font-family: monospace;
}
th {
    font-size:      11.5pt;
    font-weight:    bold;
    font-family:    monospace;
    color:              blue;
    background-color:   yellow;
}
.item {
    position: absolute;
    top:    90px;
    left:   20px;
}
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #999999;
    border-bottom-color:    #999999;
    /* background-color:#d6d3ce; */
}
.winbox_field {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #999999;
    border-left-color:      #999999;
    border-right-color:     #FFFFFF;
    border-bottom-color:    #FFFFFF;
    background-color:#d6d3ce;
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
    document.form_parts.parts_no.focus();
    document.form_parts.parts_no.select();
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
<body>
    <center>
<?php 
    if($_SESSION['User_ID'] != '00000A') {
        if ($select == 'graph') {
            echo $menu->out_title_border();
        } else {
            echo $menu->out_title_border(1);
        } 
    } else {
        echo $menu->out_title_only_border();
    }
?>
        
        <!----------------- 見出しを表示 ------------------------>
        <table bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='1'>
            <tr><td> <!-- ダミー(デザイン用) -->
        <table class='winbox_field' width='100%' border='0' cellspacing='0' cellpadding='1'>
            <tr class='sub_font'>
                <td class='winbox' align='center' width='100'> <!-- [期]選択ドロップリスト-->
                    <form name='div_form' method='get' action='<?php echo $menu->out_parent() ?>' target='_parent'>
                        <select name='tnk_ki' class='ret_font' onChange='document.div_form.submit()'>
                        <?php
                        for( $k=0; $k<$ki_rows; $k++){
                            if( $div == $ki_tbl[$k][0] ) {
                                echo "<option value='{$ki_tbl[$k][0]}' selected>{$ki_tbl[$k][0]}期</option>";
                            } else {
                                echo "<option value='{$ki_tbl[$k][0]}'>{$ki_tbl[$k][0]}期</option>";
                            }
                        }
                        ?>
                        </select>
                        <?php if ($select == 'input_mode') { ?>
                        <input type='hidden' name='input_mode' value='GO'>
                        <?php } elseif ($select == 'graph') { ?>
                        <input type='hidden' name='graph' value='GO'>
                        <?php } ?>
                    </form>
                </td>
                <td class='winbox'> <!-- [グラフ]ボタン-->
                    <form action='<?php echo $menu->out_parent() ?>' method='get' target='_parent'>
                        <?php if ($select == 'graph') { ?>
                        <input style='font-size:11pt; font-weight:bold; color:blue; width:115px;' type='submit' name='graph' value='グラフ'>
                        <?php } else { ?>
                        <input style='font-size:11pt; font-weight:bold; color:black; width:115px;' type='submit' name='graph' value='グラフ'>
                        <?php } ?>
                        <input type='hidden' name='tnk_ki' value='<?php echo $div?>'>
                    </form>
                </td>
                <?php
                if (getCheckAuthority(69)) { // 69：総務課員の社員番号（管理部と総務課）
                ?>
                <td class='winbox'> <!-- [入力]ボタン-->
                    <form action='<?php echo $menu->out_parent() ?>' method='get' target='_parent'>
                        <?php if ($select == 'input_mode') { ?>
                        <input style='font-size:11pt; font-weight:bold; color:blue; width:115px;' type='submit' name='input_mode' value='入力'>
                        <?php } else { ?>
                        <input style='font-size:11pt; font-weight:bold; color:black; width:115px;' type='submit' name='input_mode' value='入力'>
                        <?php } ?>
                        <input type='hidden' name='tnk_ki' value='<?php echo $div?>'>
                    </form>
                </td>
                <?php
                }
                ?>
            </tr>
        </table>
            </td></tr>
        </table> <!-- ダミーEnd -->
        <!-- <hr color='797979'> -->
        
        <?php if ($select == 'input_mode') { ?>
        <table class='item' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='2'>
           <tr><td> <!-- ダミー(デザイン用) -->
        <table class='winbox_field' width=100% align='center'  border='1' cellspacing='0' cellpadding='1'>
            <?php
            echo "<th class='winbox' width='88' nowrap>{$column[0][0]}</th>";   // 部署
            for( $c=1; $c<$column_row; $c++ ) {
                echo "<th class='winbox' width='61' nowrap>{$column[$c][0]}</th>";  // 各月
            }
            ?>
        </table> <!----- ダミー End ----->
            </td></tr>
        </table>
        <?php } ?>
    </center>
</body>
</html>
<?php echo $menu->out_alert_java()?>
<?php ob_end_flush(); // 出力バッファをgzip圧縮 END ?>
