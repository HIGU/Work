<?php
//////////////////////////////////////////////////////////////////////////////
// 協力工場別注残リストの照会 条件選択フォーム                              //
// Copyright (C) 2005-2015 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/04/26 Created   vendor_order_list_form.php                          //
// 2005/04/30 選択方式と直接入力方式を選べる機能追加 *ブランク処理がポイント//
// 2006/08/31 注残リストから検査依頼の予約が出来るように機能実装(注文書のみ)//
//            分納伝票で注文データが2重に見えてしまうため検査依頼用として   //
//            別ボタンで実装するようにした。                                //
// 2011/05/25 注残リストをCSVで出力できるようにした。                  大谷 //
// 2015/10/19 製品グループにT=ツールを追加。                           大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');        // define.php と pgsql.php を require_once している
// require_once ('../../tnk_func.php');        // TNK に依存する部分の関数を require_once している
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(30, 51);                    // site_index=30(生産メニュー) site_id=999(未定)
////////////// リターンアドレス設定
// $menu->set_RetUrl(INDUST_MENU);             // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('協力工場別 注残リスト (条件選択)');
//////////// 表題の設定
$menu->set_caption('下記の必要な条件を入力又は選択して下さい。');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('注残リスト',   INDUST . 'vendor/vendor_order_list.php');
$menu->set_action('注残リスト2',  INDUST . 'vendor/vendor_order_list-2line.php');
$menu->set_action('注残リスト3',  INDUST . 'vendor/vendor_order_list_inspection.php');
$menu->set_action('注残リスト4',  INDUST . 'vendor/vendor_order_list-2line_inspection.php');
$menu->set_action('CSV出力',   INDUST . 'vendor/vendor_order_csv.php');

//////////// JavaScript Stylesheet File を必ず読み込ませる
$uniq = uniqid('target');

/////////////// 受け渡し変数の初期化
// あえてセッションに保存していない
$vendor = '';
$div    = '';
$plan_cond = '';

//////// 協力工場名及びコードの取得 (ベンダー名があり住所が登録されているもの)資本金から住所へ変更
$query = "select vendor, substr(trim(name),1,10) from vendor_master where trim(name) != '' and trim(address1) != '' order by vendor ASC";
$res = array();
if (($rows = getResult2($query, $res)) < 1) {
    $_SESSION['s_sysmsg'] = "発注先マスターを取得できません！";
}

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

<!--    ファイル指定の場合 -->
<script language='JavaScript' src='./vendor_order_list_form.js?<?= $uniq ?>'></script>

<script language="JavaScript">
<!--
// -->
</script>

<!-- スタイルシートのファイル指定をコメント HTMLタグ コメントは入れ子に出来ない事に注意
<link rel='stylesheet' href='<?= MENU_FORM . '?' . $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
.pt12b {
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
}
th {
    background-color:   blue;
    color:              yellow;
    font-size:          10pt;
    font-weight:        bold;
    font-family:        monospace;
}
td {
    font-size:      12pt;
    font-weight:    bold;
    /* font-family:    monospace; */
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
-->
</style>
</head>
<body onLoad='set_focus();' style='overflow-y:hidden;'>
    <center>
<?= $menu->out_title_border() ?>
        <form name='vendor_form'
            action='JavaScript:win_open("<?=$menu->out_action('注残リスト')?>", document.vendor_form.vendor.value)'
            method='post' onSubmit='return chk_vendor_order_list_form(this)'
        >
            <!----------------- ここは 本文を表示する ------------------->
            <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table class='winbox_field' width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                        <!--  bgcolor='#ffffc6' 薄い黄色 --> 
                    <td class='winbox' colspan='3' align='center' style='background-color:blue; color:white;'>
                        <span class='caption_font'><?= $menu->out_caption(), "\n" ?></span>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='center' style='background-color:#ffffc6; color:blue;'>
                        項目
                    </td>
                    <td class='winbox' align='center' style='background-color:#ffffc6; color:blue;'>
                        選択指定
                    </td>
                    <td class='winbox' align='center' style='background-color:#ffffc6; color:blue;'>
                        直接指定
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        発注先名で選択<br>
                        又は<br>
                        発注先コードを指定
                    </td>
                    <td class='winbox' align='center'>
                        <select name='vendor2' size='10' class='pt12b'
                            onClick='vendor_copy()'
                            onChange='vendor_copy()'
                            onDblClick='if (chk_vendor_order_list_form(document.vendor_form)==true) document.vendor_form.submit();'
                        >
                        <?php
                            for ($i=0; $i<$rows; $i++) {
                                echo "<option value='{$res[$i][0]}'>\n";
                                echo "{$res[$i][0]} {$res[$i][1]}\n";
                                echo "</option>\n";
                            }
                        ?>
                        </select>
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='vendor' class='pt12b' size='6' value='<?= $vendor ?>' maxlength='5' onChange='vendor_copy2()'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        製品グループ<br>
                        ブランク＝全て
                        <!-- (C=カプラ L=リニア T=ツール SC=C特注 CS=C標準) -->
                    </td>
                    <td class='winbox' align='center'>
                        <select name='div2' size='5' class='pt12b'
                            onClick='div_copy()'
                            onChange='div_copy()'
                            onDblClick='if (chk_vendor_order_list_form(document.vendor_form)==true) document.vendor_form.submit();'
                        >
                            <option <?php if ($div=='C') echo 'selected'?>  value= 'C'>カプラ</option>
                            <option <?php if ($div=='SC') echo 'selected'?> value='SC'>Ｃ特注</option>
                            <option <?php if ($div=='CS') echo 'selected'?> value='CS'>Ｃ標準</option>
                            <option <?php if ($div=='L') echo 'selected'?>  value= 'L'>リニア</option>
                            <option <?php if ($div=='L') echo 'selected'?>  value= 'T'>ツール</option>
                            <option <?php if ($div==' ') echo 'selected'?>  value= '' >全　て</option>
                        </select>
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='div' class='pt12b' size='2' value='<?= $div ?>' maxlength='2' style='text-align:center;' onChange='div_copy2()'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        発注状況区分<br>
                        ブランク＝全て
                        <!-- (ブランク=全て O=注文書発行済 R=内示中 P=予定) -->
                    </td>
                    <td class='winbox' align='center'>
                        <select name='plan_cond2' size='4' class='pt12b'
                            onClick='plan_cond_copy()'
                            onChange='plan_cond_copy()'
                            onDblClick='if (chk_vendor_order_list_form(document.vendor_form)==true) document.vendor_form.submit();'
                        >
                            <option <?php if ($plan_cond==' ') echo 'selected'?> value='' >全　て</option>
                            <option <?php if ($plan_cond=='P') echo 'selected'?> value='P'>予　定</option>
                            <option <?php if ($plan_cond=='R') echo 'selected'?> value='R'>内示中</option>
                            <option <?php if ($plan_cond=='O') echo 'selected'?> value='O'>注文書</option>
                        </select>
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='plan_cond' class='pt12b' size='2' value='<?= $plan_cond ?>' maxlength='1' style='text-align:center;' onChange='plan_cond_copy2()'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' colspan='3' align='center'>
                        <input type='submit' name='list_view' value='１段表示'>
                        <input type='button' name='list_view2' value='２段表示'
                            onClick='win_open2("<?=$menu->out_action('注残リスト2')?>", document.vendor_form.vendor.value)'>
                        <input type='button' name='list_inspection' value='１段依頼用'
                            onClick='win_open2("<?=$menu->out_action('注残リスト3')?>", document.vendor_form.vendor.value)'>
                        <input type='button' name='list_inspection2' value='２段依頼用'
                            onClick='win_open2("<?=$menu->out_action('注残リスト4')?>", document.vendor_form.vendor.value)'>
                        <input type='button' name='csv_output' value='CSV出力'
                            onClick='csv_output2("<?=$menu->out_action('CSV出力')?>", document.vendor_form.vendor.value)'>
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ダミーEnd ------------------>
        </form>
    </center>
</body>
<?= $menu->out_alert_java() ?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
