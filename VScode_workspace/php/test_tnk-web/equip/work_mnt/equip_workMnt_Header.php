<?php
//////////////////////////////////////////////////////////////////////////////
// 機械稼動管理システムの 加工指示(指示メンテナンス)  フレームヘッダー定義  //
// Copyright (C) 2004-2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2004/07/27 Created  equip_workMnt_Header.php                             //
// 2004/08/08 フレーム版の戻り先をapplication→_parentに変更(FRAME無し対応) //
// 2004/11/16 工場別に対応 $factory($_SESSION['factory'])                   //
// 2004/12/09 HELP表示を追加                                                //
// 2005/06/24 F2/F12キーで戻るための対応で JavaScriptの set_focus()を追加   //
// 2007/03/27 set_site()メソッドをINDEX_EQUIPへ変更 equipment_selectの初期値//
// 2007/09/18 E_ALL | E_STRICT へ変更                                       //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../../function.php');                // TNK 全共通 function
require_once ('../EquipControllerHTTP.php');        // TNK 全共通 MVC Controller Class
require_once ('../../MenuHeader.php');              // TNK 全共通 menu class
access_log();                                       // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader();                   // 認証チェック0=一般以上 戻り先=セッションより タイトル未設定

///// 設備専用セッションクラスのインスタンスを作成
$equipSession = new equipSession();

$request = new Request();

////////////// サイト設定
$menu->set_site(INDEX_EQUIP, 23);           // site_index=40(設備メニュー) site_id=23(指示メンテナンス)
////////////// target設定
// $menu->set_target('application');           // フレーム版の戻り先はtarget属性が必須
$menu->set_target('_parent');               // フレーム版の戻り先はtarget属性が必須

/////////// 工場区分を取得する
$factory = $equipSession->getFactory();
$fact_name = $equipSession->getFactoryName($factory);

/////////// 運転指示メニューの選択設定
$equipment_select = $request->get('equipment_select');

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title("機械稼動管理 指示メンテナンス&nbsp;&nbsp;{$fact_name}");
//////////// 表題の設定
$menu->set_caption('作業区分を選択して下さい');

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
<?= $menu->out_css() ?>
<?= $menu->out_site_java() ?>
<style type='text/css'>
<!--
.item {
    position: absolute;
    top:    90px;
    left:   90px;
}
.s_radio {
    color:              white;
    background-color:   blue;
    font-weight:        bold;
    /***
    ***/
    font-size: 11pt;
}
.u_radio {
    font-size: 11pt;
}
radio {
    outline: 0px none black;
}
label {
    outline: 0px none black;
}
-->
</style>
<script type='text/javascript'>
<!--
function radio_select(num) {
    // document.radioForm.elements[num].checked = true;
    if (document.getElementById) {                                      // IE5.5-, NN6- NN7.1-
        document.getElementById('radio0').className = 'u_radio';
        document.getElementById('radio1').className = 'u_radio';
        document.getElementById('radio2').className = 'u_radio';
        document.getElementById('radio3').className = 'u_radio';
        document.getElementById('radio4').className = 'u_radio';
        document.getElementById('radio5').className = 'u_radio';
        document.getElementById('radio6').className = 'u_radio';
        if (num == 0) {
            document.getElementById('radio0').className = 's_radio';
        } else if (num == 1) {
            document.getElementById('radio1').className = 's_radio';
        } else if (num == 2) {
            document.getElementById('radio2').className = 's_radio';
        } else if (num == 3) {
            document.getElementById('radio3').className = 's_radio';
        } else if (num == 4) {
            document.getElementById('radio4').className = 's_radio';
        } else if (num == 5) {
            document.getElementById('radio5').className = 's_radio';
        } else if (num == 6) {
            document.getElementById('radio6').className = 's_radio';
        }
    } else if (document.all) {                                          // IE4-
        document.all['radio0'].className = 'u_radio';
        document.all['radio1'].className = 'u_radio';
        document.all['radio2'].className = 'u_radio';
        document.all['radio3'].className = 'u_radio';
        document.all['radio4'].className = 'u_radio';
        document.all['radio5'].className = 'u_radio';
        document.all['radio6'].className = 'u_radio';
        if (num == 0) {
            document.all['radio0'].className = 's_radio';
        } else if (num == 1) {
            document.all['radio1'].className = 's_radio';
        } else if (num == 2) {
            document.all['radio2'].className = 's_radio';
        } else if (num == 3) {
            document.all['radio3'].className = 's_radio';
        } else if (num == 4) {
            document.all['radio4'].className = 's_radio';
        } else if (num == 5) {
            document.all['radio5'].className = 's_radio';
        } else if (num == 6) {
            document.all['radio6'].className = 's_radio';
        }
    }
    document.radioForm.submit();
    // document.body.focus();   // outline で対応出来なかった
    document.mhForm.backwardStack.focus();  // 上記はIEのみのためNN対応
}
function win_open(url) {
    var w = 800;
    var h = 600;
    var left = (screen.availWidth  - w) / 2;
    var top  = (screen.availHeight - h) / 2;
    window.open(url, 'help_win', 'width='+w+',height='+h+',scrollbars=yes,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
}
/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus() {
    // document.body.focus();   // F2/F12キーを有効化する対応
    document.mhForm.backwardStack.focus();  // 上記はIEのみのためNN対応
}
// -->
</script>
</head>
<body onLoad='set_focus()'>
    <center>
<?= $menu->out_title_border() ?>
        
        <table bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>
            <tr>
                <td align='center' nowrap class='caption_font'>
                    <?= $menu->out_caption() ?>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <input style='font-size:10pt; font-weight:bold; color:blue;' type='button' name='work_mnt_help' value='HELP' onClick='win_open("help/Mnt_top_help.html")'>
                    <br>
                    <table width='100%' bordercolordark='white' bordercolorlight='#bdaa90' align='center' border='1' cellspacing='0' cellpadding='3'>
                        <form name='radioForm' action='equip_workMnt_List.php' method='post' target='List'>
                            <tr align='center'>
                                <td nowrap id='radio0' class='<?php if ($equipment_select == 'init_data_input') echo 's_radio'; else echo 'u_radio'; ?>'>
                                    <input type='radio' name='equipment_select' value='init_data_input' id='input' onClick='radio_select(0)'<?php if ($equipment_select == 'init_data_input') echo ' checked'?>>
                                    <label for='input'>運転開始
                                </td>
                                <td nowrap id='radio1' class='<?php if ($equipment_select == 'init_data_end') echo 's_radio'; else echo 'u_radio'; ?>'>
                                    <input type='radio' name='equipment_select' value='init_data_end' id='end' onClick='radio_select(1)'<?php if ($equipment_select == 'init_data_end') echo ' checked'?>>
                                    <label for='end'>加工完了
                                </td>
                                <td nowrap id='radio2' class='<?php if ($equipment_select == 'init_data_cut') echo 's_radio'; else echo 'u_radio'; ?>'>
                                    <input type='radio' name='equipment_select' value='init_data_cut' id='cut' onClick='radio_select(2)'<?php if ($equipment_select == 'init_data_cut') echo ' checked'?>>
                                    <label for='cut'>運転中断
                                </td>
                                <td nowrap id='radio3' class='<?php if ($equipment_select == 'break_data') echo 's_radio'; else echo 'u_radio'; ?>'>
                                    <input type='radio' name='equipment_select' value='break_data' id='break' onClick='radio_select(3)'<?php if ($equipment_select == 'break_data') echo ' checked'?>>
                                    <label for='break'>中断計画
                                </td>
                                <td nowrap id='radio4' class='<?php if ($equipment_select == 'init_data_edit') echo 's_radio'; else echo 'u_radio'; ?>'>
                                    <input type='radio' name='equipment_select' value='init_data_edit' id='edit' onClick='radio_select(4)'<?php if ($equipment_select == 'init_data_edit') echo ' checked'?>>
                                    <label for='edit'>指示変更
                                </td>
                                <td nowrap id='radio5' class='<?php if ($equipment_select == 'plan_data') echo 's_radio'; else echo 'u_radio'; ?>'>
                                    <input type='radio' name='equipment_select' value='plan_data' id='plan' onClick='radio_select(5)'<?php if ($equipment_select == 'plan_data') echo ' checked'?>>
                                    <label for='plan'>予定計画
                                </td>
                                <td nowrap id='radio6' class='<?php if ($equipment_select == '') echo 's_radio'; else echo 'u_radio'; ?>'>
                                    <input type='radio' name='equipment_select' value='working' id='working' onClick='radio_select(6)'<?php if ($equipment_select == '') echo ' checked'?>>
                                    <label for='working'>現加工中
                                </td>
                            </tr>
                            <tr align='center' class='u_radio'>
                                <td nowrap>(データ入力)</td><td>　</td><td>　</td><td nowrap>(データ削除)</td><td nowrap>(データ修正)</td></td><td>　</td><td>　</td>
                                <input type='hidden' name='select_submit' value='実行'>
                            </tr>
                        </form>
                    </table>
                </td>
            </tr>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <!-- <hr color='797979'> -->
        
    </center>
</body>
<script type='text/javascript'>
<!--
/***** default 設定 
if (document.getElementById) {                                      // IE5.5-, NN6- NN7.1-
    document.getElementById('radio6').className = 's_radio';
} else if (document.all) {                                          // IE4-
    document.all['radio6'].className = 's_radio';
}
*****/
// -->
</script>
</html>
<?php ob_end_flush(); // 出力バッファをgzip圧縮 END ?>
