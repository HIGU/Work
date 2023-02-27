<?php
//////////////////////////////////////////////////////////////////////////////
// Ａ伝情報ファイルの照会用 条件指定フォーム  更新元 UKWLIB/W#MIADIM        //
// Copyright (C) 2004-2015 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/10/21 Created   aden_master_view_form.php                           //
// 2004/10/22 ＳＣ工番と製品番号assy_no(parts_no)で検索できる機能追加       //
// 2004/10/23 上記の変更で３種類の検索が出来るため見やすくタイトルを動的変更//
//            ダブルクリックで実行する機能を追加 onDblClick='submit()'      //
// 2005/01/18 キーメッセージ追加 栃木日東工器のロゴを右下に追加 background  //
// 2005/01/24 繰返しのタブをonKeyDown='tab_chk()'→隠しボタンのonFocus()へ  //
// 2011/01/11 SC工番の入力桁を8桁から9桁へ変更                         大谷 //
// 2015/02/06 A伝未回答のみの抽出を追加                                大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);           // E_ALL='2047' debug 用
// ini_set('display_errors','1');              // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');        // define.php と pgsql.php を require_once している
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(30, 13);                    // 生産=20 Ａ伝=13

////////////// リターンアドレス設定
// $menu->set_RetUrl(INDUST_MENU);          // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('Ａ 伝 情 報 の 照 会 フォーム');
//////////// 表題の設定
$menu->set_caption('ＳＣ工番を入力してEnterキーを押して下さい。');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('A伝情報の照会',   INDUST . 'Aden/aden_master_view.php');

///// セッション情報のパラメーター取得
if (isset($_SESSION['sc_no'])) {
    $sc_no = $_SESSION['sc_no'];
} else {
    $sc_no = '';
}
if (isset($_SESSION['aden_no'])) {
    $aden_no = $_SESSION['aden_no'];
} else {
    $aden_no = '';
}
if (isset($_SESSION['aden_assy_no'])) {
    $aden_assy_no = $_SESSION['aden_assy_no'];
} else {
    $aden_assy_no = '';
}

//////////// JavaScript Stylesheet File 等のcache防止
$uniq = uniqid('target');

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>

<!--    ファイル指定の場合
<script language='JavaScript' src='template.js?<?= $uniq ?>'>
</script>
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
/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus() {
    document.sc_form.sc_no.focus();
    document.sc_form.sc_no.select();
}
function assy_upper(obj) {
    obj.aden_assy_no.value = obj.aden_assy_no.value.toUpperCase();
    return true;
}
function sc_upper(obj) {
    obj.sc_no.value = obj.sc_no.value.toUpperCase();
    return true;
}
function title_chg(name) {
    if (document.all) {                         // IE4-
        document.all.sc.style.backgroundColor   = '#d6d3ce';
        document.all.sc.style.color             = 'blue';
        document.all.aden.style.backgroundColor = '#d6d3ce';
        document.all.aden.style.color           = 'blue';
        document.all.assy.style.backgroundColor = '#d6d3ce';
        document.all.assy.style.color           = 'blue';
    } else if (document.getElementById) {       // NN6- NN7.1- (IE5.5も使用可)
        document.getElementById('sc').style.backgroundColor   = '#d6d3ce';
        document.getElementById('sc').style.color             = 'blue';
        document.getElementById('aden').style.backgroundColor = '#d6d3ce';
        document.getElementById('aden').style.color           = 'blue';
        document.getElementById('assy').style.backgroundColor = '#d6d3ce';
        document.getElementById('assy').style.color           = 'blue';
    } else {
        return;
    }
    switch (name) {
    case 'aden_assy_no':
        if (document.all) {
            document.all.assy.style.backgroundColor = 'darkblue';
            document.all.assy.style.color           = 'white';
        } else {
            document.getElementById('assy').style.backgroundColor = 'darkblue';
            document.getElementById('assy').style.color           = 'white';
        }
        break;
    case 'aden_no':
        if (document.all) {
            document.all.aden.style.backgroundColor = 'darkblue';
            document.all.aden.style.color           = 'white';
        } else {
            document.getElementById('aden').style.backgroundColor = 'darkblue';
            document.getElementById('aden').style.color           = 'white';
        }
        break;
    case 'sc_no':
        if (document.all) {
            document.all.sc.style.backgroundColor = 'darkblue';
            document.all.sc.style.color           = 'white';
        } else {
            document.getElementById('sc').style.backgroundColor = 'darkblue';
            document.getElementById('sc').style.color           = 'white';
        }
        break;
    default:
        return;
    }
    return;
}
function tab_chk() {
    if (event.keyCode == 9) {       // tab
        document.all.note.focus();
        return;
    }
    //  onKeyDown='tab_chk()'
    /***********
    if (event.keyCode == 16) {      // shift
        document.aden_form.aden_no.focus();
        return;
    }
    ***********/
    return;
}
// -->
</script>

<!-- スタイルシートのファイル指定をコメント HTMLタグ コメントは入れ子に出来ない事に注意
<link rel='stylesheet' href='template.css?<?= $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
.pt9 {
    font-size:      9pt;
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
}
.pt12b {
    font-size:      12pt;
    font-weight:    bold;
}
th {
    background-color:yellow;
    color:          blue;
    font-size:      10pt;
    font-weight:    bold;
    font-family:    monospace;
}
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #999999;
    border-bottom-color:    #999999;
    background-color:#d6d3ce;
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
.note {
    border: 2px solid #0A0;
}
body {
    background-image:url(<?= IMG ?>t_nitto_logo4.png);
    background-repeat:no-repeat;
    background-attachment:fixed;
    background-position:right bottom;
}
-->
</style>
</head>
<body onLoad='set_focus()'>
    <center>
<?= $menu->out_title_border() ?>
        <table bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' align='center' cellspacing="0" cellpadding="3" border='1'>
            <form name='sc_form' method='get' action='<?= $menu->out_action('A伝情報の照会'), "?id=$uniq" ?>' onSubmit='return sc_upper(this)'>
                <tr>
                    <td class='winbox' nowrap align='center' style='font-size:12pt; font-weight:bold; color:blue; font-family:monospace;' id='sc'>
                        <?= $menu->out_caption() . "\n" ?>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap align='center' style='font-size:12pt; font-weight:bold; font-family:monospace;'>
                        例１:SC410181 例２:SC410* 例３:*410181 例４:*4101*
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap align='center' style='font-size:11pt;'>
                        入力せずに「Enter」を押せば最新のＳＣ工番順に全て照会
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='center'>
                        <input class='pt12b' type='text' name='sc_no' size='10' maxlength='9' value='<?= $sc_no ?>' style='text-align:center;' onFocus='title_chg(this.name)' onDblClick='submit()' tabindex='2'>
                    </td>
                </tr>
            </form>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <br>
        
        <table bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' align='center' cellspacing="0" cellpadding="3" border='1'>
            <form name='aden_form' method='get' action='<?= $menu->out_action('A伝情報の照会'), "?id=$uniq" ?>'>
                <tr>
                    <td class='winbox' nowrap align='center' style='font-size:12pt; font-weight:bold; color:blue; font-family:monospace;' id='aden'>
                        Ａ伝番号を入力してEnterキーを押して下さい。
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap align='center' style='font-size:12pt; font-weight:bold; font-family:monospace;'>
                        例１:795404 例２:7953* 例３:*95367 例４:*9536*
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap align='center' style='font-size:11pt;'>
                        入力せずに「Enter」を押せば最新のA伝番号順に全て照会
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='center'>
                        <input class='pt12b' type='text' name='aden_no' size='7' maxlength='6' value='<?= $aden_no ?>' style='text-align:center;' onFocus='title_chg(this.name)' onDblClick='submit()' tabindex='3'>
                    </td>
                </tr>
            </form>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <br>
        
        <table bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' align='center' cellspacing="0" cellpadding="3" border='1'>
            <form name='assy_form' method='get' action='<?= $menu->out_action('A伝情報の照会'), "?id=$uniq" ?>' onSubmit='return assy_upper(this)'>
                <tr>
                    <td class='winbox' nowrap align='center' style='font-size:12pt; font-weight:bold; color:blue; font-family:monospace;' id='assy'>
                        製品・部品番号を入力してEnterキーを押して下さい。
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap align='center' style='font-size:12pt; font-weight:bold; font-family:monospace;'>
                        例１:CB21655-0 例２:CB2165* 例３:*21655-0 例４:*21655*
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap align='center' style='font-size:11pt;'>
                        入力せずに「Enter」を押せば製品・部品番号順に全て照会
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='center'>
                        <input class='pt12b' type='text' name='aden_assy_no' size='10' maxlength='9' value='<?= $aden_assy_no ?>' style='text-align:center;' onFocus='title_chg(this.name)' onDblClick='submit()' tabindex='4'>
                    </td>
                </tr>
            </form>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <br>
        
        
        <table bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' align='center' cellspacing="0" cellpadding="3" border='1'>
            <form name='assy_form' method='submit' action='<?= $menu->out_action('A伝情報の照会'), "?id=$uniq" ?>'>
                <tr>
                    <td class='winbox' width='500' nowrap align='center'>
                        <input class='pt11b' type='submit' name='aden_mikan' value='A伝未回答の照会(SC工番有)'>
                    </td>
                </tr>
            </form>
            <form name='assy_form' method='submit' action='<?= $menu->out_action('A伝情報の照会'), "?id=$uniq" ?>'>
                <tr>
                    <td class='winbox' width='500' nowrap align='center'>
                        <input class='pt11b' type='submit' name='aden_mikanc' value='A伝未回答の照会(カプラ SC工番無)'>
                    </td>
                </tr>
            </form>
            <form name='assy_form' method='submit' action='<?= $menu->out_action('A伝情報の照会'), "?id=$uniq" ?>'>
                <tr>
                    <td class='winbox' width='500' nowrap align='center'>
                        <input class='pt11b' type='submit' name='aden_mikanl' value='A伝未回答の照会(リニア)'>
                    </td>
                </tr>
            </form>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        
        
        <hr>
        <br>
        
        <table class='note'>
            <tr><td align='center' class='pt11b' tabindex='1' id='note'>上記の各入力欄でダブルクリックすればEnterキーと同じく実行します。</td></tr>
        </table>
        <br>
        <table class='note'>
            <tr><td align='center' class='pt11b'>ＴＡＢキーで入力欄を移動できます。また、F2キーで前の画面に戻ります。</td></tr>
        </table>
    </center>
</body>
<input type='button' name='none' value='' tabindex='5' onFocus='set_focus()' style='font-size:1pt;'>
</html>
