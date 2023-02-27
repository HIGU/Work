<?php
//////////////////////////////////////////////////////////////////////////////
// 納入予定金額の照会  Headerフレーム                                       //
// Copyright (C) 2009-2010   Norihisa.Ohya  norihisa_ooya@nitto-kohki.co.jp //
// Changed history                                                          //
// 2009/11/09 Created  /order/order_schedule_Header.phpより/order_moneyへ   //
//            流用                                                          //
// 2010/05/26 タイトルが違うので修正                                        //
//////////////////////////////////////////////////////////////////////////////
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
if (isset($_REQUEST['div'])) {
    $div = $_REQUEST['div'];                // 事業部
    $_SESSION['div'] = $_REQUEST['div'];    // セッションに保存
} else {
    if (isset($_SESSION['div'])) {
        $div = $_SESSION['div'];            // Default(セッションから)
    } else {
        $div = 'C';                         // 初期値(カプラ)あまり意味は無い
    }
}
if (isset($_REQUEST['miken'])) {
    $select = 'miken';                      // 未検収リスト
    $_SESSION['select'] = 'miken';          // セッションに保存
} elseif (isset($_REQUEST['insEnd'])) {
    $select = 'insEnd';                     // 検収済リスト
    $_SESSION['select'] = 'insEnd';         // セッションに保存
} elseif (isset($_REQUEST['graph'])) {
    $select = 'graph';                      // 納入予定グラフ
    $_SESSION['select'] = 'graph';          // セッションに保存
} elseif (isset($_REQUEST['list'])) {
    $select = 'list';                      // 納入予定集計
    $_SESSION['select'] = 'list';          // セッションに保存
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
    $menu->set_title('納入予定金額 日計グラフ');
} elseif ($select == 'list') {
    $menu->set_title('納入予定金額 集計票');
} else {
    $menu->set_title('検査仕掛 明細表 照会');
}
//////////// 表題の設定
$menu->set_caption('照会内容選択');

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
<body <?php if ($select=='miken' || $select=='insEnd') echo "onLoad='set_focus()'";?>>
    <center>
<?php if($_SESSION['User_ID'] != '00000A') { if ($select == 'graph') echo $menu->out_title_border(); else echo $menu->out_title_border(1); } else echo $menu->out_title_only_border(); ?>
        
        <!----------------- 見出しを表示 ------------------------>
        <table bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='1'>
            <tr><td> <!-- ダミー(デザイン用) -->
        <table class='winbox_field' width='100%' border='0' cellspacing='0' cellpadding='1'>
            <tr class='sub_font'>
                <!--
                <td class='winbox'>
                    <input style='font-size:10pt; font-weight:bold; color:blue;' type='submit' name='order_help' value='説明' onClick='win_open("order_help.php")'>
                </td>
                -->
                <td class='winbox'>
                    <form action='<?php echo $menu->out_parent() ?>' method='get' target='_parent'>
                        <input style='font-size:10pt; color:black; width:60px;' type='submit' name='reload' value='画面更新'>
                    </form>
                </td>
                <td class='winbox' align='center' width='100'>
                    <form name='div_form' method='get' action='<?php echo $menu->out_parent() ?>' target='_parent'>
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
                        <?php } elseif ($select == 'graph') { ?>
                        <input type='hidden' name='list' value='GO'>
                        <?php } ?>
                    </form>
                </td>
                <td class='winbox'>
                    <form action='<?php echo $menu->out_parent() ?>' method='get' target='_parent'>
                        <?php if ($select == 'graph') { ?>
                        <input style='font-size:11pt; font-weight:bold; color:blue; width:160px;' type='submit' name='graph' value='納入予定グラフ(当月)'>
                        <?php } else { ?>
                        <input style='font-size:11pt; font-weight:bold; color:black; width:160px;' type='submit' name='graph' value='納入予定グラフ(当月)'>
                        <?php } ?>
                        <input type='hidden' name='div' value='<?php echo $div?>'>
                    </form>
                </td>
                <td class='winbox'>
                    <form action='<?php echo $menu->out_parent() ?>' method='get' target='_parent'>
                        <?php if ($select == 'list') { ?>
                        <input style='font-size:11pt; font-weight:bold; color:blue; width:160px;' type='submit' name='list' value='納入予定集計'>
                        <?php } else { ?>
                        <input style='font-size:11pt; font-weight:bold; color:black; width:160px;' type='submit' name='list' value='納入予定集計'>
                        <?php } ?>
                        <input type='hidden' name='div' value='<?php echo $div?>'>
                    </form>
                </td>
                <!--
                <td class='winbox'>
                    <form action='<?php echo $menu->out_parent() ?>' method='get' target='_parent'>
                        <?php if ($select == 'miken') { ?>
                        <input style='font-size:11pt; font-weight:bold; color:blue; width:115px;' type='submit' name='miken' value='検査仕掛リスト'>
                        <?php } else { ?>
                        <input style='font-size:11pt; font-weight:bold; color:black; width:115px;' type='submit' name='miken' value='検査仕掛リスト'>
                        <?php } ?>
                        <input type='hidden' name='div' value='<?php echo $div?>'>
                    </form>
                </td>
                <?php if ($select == 'miken') { ?>
                <td class='winbox'>
                    <form action='order_schedule_List.php' method='get' target='List' name='form_parts' onSubmit='return parts_upper(this)'>
                        <input type='text' name='parts_no' onKeyUp='baseJS.keyInUpper(this);' size='9' maxlength='9' value='' style='text-align:left; font-size:12pt; font-weight:bold;'>
                    </form>
                </td>
                <?php } ?>
                <td class='winbox'>
                    <form action='<?php echo $menu->out_parent() ?>' method='get' target='_parent'>
                        <?php if ($select == 'insEnd') { ?>
                        <input style='font-size:11pt; font-weight:bold; color:blue; width:105px;' type='submit' name='insEnd' value='検査済リスト'>
                        <?php } else { ?>
                        <input style='font-size:11pt; font-weight:bold; color:black; width:105px;' type='submit' name='insEnd' value='検査済リスト'>
                        <?php } ?>
                        <input type='hidden' name='div' value='<?php echo $div?>'>
                    </form>
                </td>
                <?php if ($select == 'insEnd') { ?>
                <td class='winbox'>
                    <form action='order_schedule_List.php' method='get' target='List' name='form_parts' onSubmit='return parts_upper(this)'>
                        <input type='text' name='parts_no' onKeyUp='baseJS.keyInUpper(this);' size='9' maxlength='9' value='' style='text-align:left; font-size:12pt; font-weight:bold;'>
                    </form>
                </td>
                <?php } ?>
                <td class='winbox'>
                    <form action='inspection_recourse.php' method='get' target='_parent'>
                        <?php if ($select == 'inspc') { ?>
                        <input style='font-size:11pt; font-weight:bold; color:blue; width:115px;' type='submit' name='inspc' value='検査依頼リスト'>
                        <?php } else { ?>
                        <input style='font-size:11pt; font-weight:bold; color:black; width:115px;' type='submit' name='inspc' value='検査依頼リスト'>
                        <?php } ?>
                        <input type='hidden' name='div' value='<?php echo $div?>'>
                    </form>
                </td>
                <td class='winbox'>
                    <form action='inspectingList.php' method='get' target='_parent'>
                        <?php if ($select == 'inspecting') { ?>
                        <input style='font-size:11pt; font-weight:bold; color:blue; width:105px;' type='submit' name='inspc' value='検査中リスト'>
                        <?php } else { ?>
                        <input style='font-size:11pt; font-weight:bold; color:black; width:105px;' type='submit' name='inspc' value='検査中リスト'>
                        <?php } ?>
                        <input type='hidden' name='div' value='<?php echo $div?>'>
                    </form>
                </td>
                -->
            </tr>
        </table>
            </td></tr>
        </table> <!-- ダミーEnd -->
        
        <!-- <hr color='797979'> -->
        
        <?php if ($select == 'miken' || $select == 'insEnd') { ?>
        <table class='item' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='2'>
           <tr><td> <!-- ダミー(デザイン用) -->
        <table class='winbox_field' width=100% align='center'  border='1' cellspacing='0' cellpadding='1'>
            <th class='winbox' width='30' nowrap>No</th>
            <th class='winbox' width='98' nowrap colspan='2' style='font-size:10pt;'>検査開始終了</th>
            <th class='winbox' width='70' nowrap>受付日</th>
            <th class='winbox' width='55' nowrap style='font-size:9.5pt;'>受付No</th>
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
        <?php } ?>
    </center>
</body>
</html>
<?php echo $menu->out_alert_java()?>
<?php ob_end_flush(); // 出力バッファをgzip圧縮 END ?>
