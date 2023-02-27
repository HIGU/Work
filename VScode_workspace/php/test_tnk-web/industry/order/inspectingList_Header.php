<?php
//////////////////////////////////////////////////////////////////////////////
// 受入検査中のリスト 照会 及び 中断指示メンテナンス        Headerフレーム  //
// Copyright (C) 2007-2017 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/01/17 Created  inspectingList_Header.php                            //
// 2007/01/18 HELP 機能を追加                                               //
// 2007/01/22 out_title_border() → out_title_border(1) へ変更              //
// 2007/09/29 検査済リストを追加 E_ALL → E_ALL | E_STRICTへ                //
// 2017/07/27 集荷納期グラフをメニューに追加                                //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../../function.php');        // TNK 全共通 function
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader();                   // 認証チェック0=一般以上 戻り先=セッションより タイトル未設定

////////////// サイト設定
$menu->set_site(30, 50);                    // site_index=30(生産メニュー) site_id=50(納入・検査仕掛)999(サイトを開く)
////////////// target設定
// $menu->set_target('application');           // フレーム版の戻り先はtarget属性が必須
$menu->set_target('_parent');               // フレーム版の戻り先はtarget属性が必須

///////// パラメーターチェックと設定
$div = $_SESSION['div'];                    // Default(セッションから)
$select = 'inspecting';                     // 検査中リスト

/////////// 画面解像度の取得
if ($_SESSION['site_view'] == 'on') {
    $display = 'normal';
} else {
    $display = 'wide';
}

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('受入検査中リスト照会 及び 中断処理');
//////////// 表題の設定
$menu->set_caption('照会内容選択');

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_css() ?>
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
    left:    20px;
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
<?php if($_SESSION['User_ID'] != '00000A') echo $menu->out_title_border(1); else echo $menu->out_title_only_border(); ?>
        
        <!----------------- 見出しを表示 ------------------------>
        <table bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='1'>
            <tr><td> <!-- ダミー(デザイン用) -->
        <table class='winbox_field' width='100%' border='0' cellspacing='0' cellpadding='1'>
            <tr class='sub_font'>
                <td class='winbox'>
                    <input style='font-size:10pt; font-weight:bold; color:blue;' type='submit' name='inspectingList_help' value='説明' onClick='win_open("inspectingList_help.php")'>
                </td>
                <td class='winbox'>
                    <form action='<?= $menu->out_parent() ?>' method='get' target='_parent'>
                        <input style='font-size:10pt; color:black; width:60px;' type='submit' name='reload' value='画面更新'>
                    </form>
                </td>
                <td class='winbox' align='center' width='100'>
                    <form name='div_form' method='get' action='<?= $menu->out_parent() ?>' target='_parent'>
                        <select name='div' class='ret_font' onChange='document.div_form.submit()'>
                            <option value='C' <?php if($div=='C') echo 'selected'; ?>>カプラ</option>
                            <option value='SC' <?php if($div=='SC') echo 'selected'; ?>>Ｃ特注</option>
                            <option value='CS' <?php if($div=='CS') echo 'selected'; ?>>Ｃ標準</option>
                            <option value='L' <?php if($div=='L') echo 'selected'; ?>>リニア</option>
                            <option value='T' <?php if($div=='T') echo 'selected'; ?>>ツール</option>
                            <option value='F' <?php if($div=='F') echo 'selected'; ?>>ＦＡ</option>
                            <option value='A' <?php if($div=='A') echo 'selected'; ?>>全て</option>
                            <option value='N' <?php if($div=='N') echo 'selected'; ?>>ＮＫ</option>
                            <option value='NKB' <?php if($div=='NKB') echo 'selected'; ?>>ＮＫＢ</option>
                        </select>
                        <?php if ($select == 'miken') { ?>
                        <input type='hidden' name='miken' value='GO'>
                        <?php } elseif ($select == 'insEnd') { ?>
                        <input type='hidden' name='insEnd' value='GO'>
                        <?php } elseif ($select == 'graph') { ?>
                        <input type='hidden' name='graph' value='GO'>
                        <?php } elseif ($select == 'sgraph') { ?>
                        <input type='hidden' name='sgraph' value='GO'>
                        <?php } ?>
                    </form>
                </td>
                <td class='winbox'>
                    <form action='order_schedule.php' method='get' target='_parent'>
                        <?php if ($select == 'graph') { ?>
                        <input style='font-size:11pt; font-weight:bold; color:blue; width:115px;' type='submit' name='graph' value='納入予定グラフ'>
                        <?php } else { ?>
                        <input style='font-size:11pt; font-weight:bold; color:black; width:115px;' type='submit' name='graph' value='納入予定グラフ'>
                        <?php } ?>
                        <input type='hidden' name='div' value='<?=$div?>'>
                    </form>
                </td>
                <td class='winbox'>
                    <form action='order_schedule.php' method='get' target='_parent'>
                        <?php if ($select == 'sgraph') { ?>
                        <input style='font-size:11pt; font-weight:bold; color:blue; width:115px;' type='submit' name='sgraph' value='集荷納期グラフ'>
                        <?php } else { ?>
                        <input style='font-size:11pt; font-weight:bold; color:black; width:115px;' type='submit' name='sgraph' value='集荷納期グラフ'>
                        <?php } ?>
                        <input type='hidden' name='div' value='<?=$div?>'>
                    </form>
                </td>
                <td class='winbox'>
                    <form action='order_schedule.php' method='get' target='_parent'>
                        <?php if ($select == 'miken') { ?>
                        <input style='font-size:11pt; font-weight:bold; color:blue; width:115px;' type='submit' name='miken' value='検査仕掛リスト'>
                        <?php } else { ?>
                        <input style='font-size:11pt; font-weight:bold; color:black; width:115px;' type='submit' name='miken' value='検査仕掛リスト'>
                        <?php } ?>
                        <input type='hidden' name='div' value='<?=$div?>'>
                    </form>
                </td>
                <td class='winbox'>
                    <form action='order_schedule.php' method='get' target='_parent'>
                        <?php if ($select == 'insEnd') { ?>
                        <input style='font-size:11pt; font-weight:bold; color:blue; width:105px;' type='submit' name='insEnd' value='検査済リスト'>
                        <?php } else { ?>
                        <input style='font-size:11pt; font-weight:bold; color:black; width:105px;' type='submit' name='insEnd' value='検査済リスト'>
                        <?php } ?>
                        <input type='hidden' name='div' value='<?php echo $div?>'>
                    </form>
                </td>
                <td class='winbox'>
                    <form action='inspection_recourse.php' method='get' target='_parent'>
                        <?php if ($select == 'inspc') { ?>
                        <input style='font-size:11pt; font-weight:bold; color:blue; width:115px;' type='submit' name='inspc' value='検査依頼リスト'>
                        <?php } else { ?>
                        <input style='font-size:11pt; font-weight:bold; color:black; width:115px;' type='submit' name='inspc' value='検査依頼リスト'>
                        <?php } ?>
                        <input type='hidden' name='div' value='<?=$div?>'>
                    </form>
                </td>
                <td class='winbox'>
                    <form action='<?= $menu->out_parent() ?>' method='get' target='_parent'>
                        <?php if ($select == 'inspecting') { ?>
                        <input style='font-size:11pt; font-weight:bold; color:blue; width:105px;' type='submit' name='inspc' value='検査中リスト'>
                        <?php } else { ?>
                        <input style='font-size:11pt; font-weight:bold; color:black; width:105px;' type='submit' name='inspc' value='検査中リスト'>
                        <?php } ?>
                        <input type='hidden' name='div' value='<?=$div?>'>
                    </form>
                </td>
                <!--
                <?php if ($select == 'inspecting') { ?>
                <td class='winbox'>
                    <form action='inspectingList_List.php' method='get' target='List' name='form_parts' onSubmit='return parts_upper(this)'>
                        <input type='text' name='vendor_no' onKeyUp='baseJS.keyInUpper(this);' size='9' maxlength='9' value='' style='text-align:left; font-size:12pt; font-weight:bold;'>
                    </form>
                </td>
                <?php } ?>
                -->
            </tr>
        </table>
            </td></tr>
        </table> <!-- ダミーEnd -->
        
        <!-- <hr color='797979'> -->
        
        <table class='item' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='2'>
           <tr><td> <!-- ダミー(デザイン用) -->
        <table class='winbox_field' width=100% align='center'  border='1' cellspacing='0' cellpadding='1'>
            <th class='winbox' width='30' nowrap>No</th>
            <th class='winbox' width='98' nowrap colspan='2' style='font-size:10pt;'>検査開始終了</th>
            <th class='winbox' width='70' nowrap>受付日</th>
            <!-- <th class='winbox' width='55' nowrap style='font-size:9.5pt;'>受付No</th> -->
            <th class='winbox' width='55' nowrap style='font-size:9.5pt;'>処理</th>
            <th class='winbox' width='90' nowrap>部品番号</th>
            <th class='winbox' width='150' nowrap>部品名</th>
            <th class='winbox' width='90' nowrap style='font-size:10pt;'>材質/親機種</th>
            <th class='winbox' width='70' nowrap>受付数</th>
            <th class='winbox' width='35' nowrap style='font-size:9.5pt;'>工程</th>
            <th class='winbox' width='130' nowrap>納入先</th>
            <?php if ($display == 'wide') { ?>
            <th class='winbox' width='80' nowrap>工事番号</th>
            <th class='winbox' width='80' nowrap>発行連番</th>
            <th class='winbox' width='70' nowrap>製造番号</th>
            <th class='winbox' width='130' nowrap>次工程</th>
            <?php } ?>
        </table> <!----- ダミー End ----->
            </td></tr>
        </table>
    </center>
</body>
</html>
<?= $menu->out_alert_java()?>
<?php ob_end_flush(); // 出力バッファをgzip圧縮 END ?>
