<?php
//////////////////////////////////////////////////////////////////////////////
// 社内規程メニュー 内部統制関連  company regulation                        //
// Copyright (C) 2010-2015 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2010/06/15 Created  regulation_inter_menu.php                            //
// 2010/07/06 内部統制システムの基本方針を改定（2010/06/22付）              //
// 2012/10/25 内部統制規定を改定（2011/11/22付）                            //
//            内部通報規定・別紙１を改定（2012/09/03付）                    //
// 2012/10/25 コンプライアンス規定を改定（2013/04/01付）                    //
//            内部通報規定を改定（2013/04/01付）                            //
//            内部通報規定・別紙１を改定（2013/04/01付）                    //
// 2013/07/03 内部統制規定を改定（2013/04/01付）                            //
//            以前は内部にあった内部統制システムの状況(組織)を別に表示      //
// 2013/09/25 内部通報規程・別紙１を改定（2013/09/01付）                    //
// 2013/10/21 内部通報規程・別紙１を改定（2013/10/01付）                    //
// 2014/01/23 コンプライアンス規程と内部通報規程がNKと共用規程となり        //
//            通常規程に組み込んだのでこちらからは削除                      //
// 2015/03/31 内部統制規程を基本規程へ移動(4/1から)                         //
// 2015/08/07 安全保障輸出管理規程、安全保障輸出管理規程細則、別添を        //
//            2015/08/03付へ更新                                            //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');           // define.php と pgsql.php を require_once している
// require_once ('../tnk_func.php');           // TNK に依存する部分の関数を require_once している
require_once ('../../MenuHeader.php');         // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
if (isset($_SESSION['REGU_Auth'])) {
    $menu = new MenuHeader(-1);             // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
} else {
    $menu = new MenuHeader(0);              // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
}

////////////// サイト設定
$menu->set_site(INDEX_REGU, 0);            // site_index=INDEX_REGU(社内規程メニュー) site_id=0(なし)
////////////// リターンアドレス設定(絶対指定する場合)
$menu->set_RetUrl('../regulation_menu.php');                // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('社内規程 照会 メニュー 内部統制関連規定');
//////////// 表題の設定
$menu->set_caption('以下の規程類は Acrobat Reader 5 以上で閲覧出来ます。');
$uniq = 'ID=' . uniqid('regu');

$today = date('Ymd');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>
<script type='text/javascript' language='JavaScript' src='regulation_inter.js?id=<?= $uniq ?>'></script>
<link rel='stylesheet' href='regulation_inter.css?id=<?= $uniq ?>' type='text/css' media='screen'>
</head>
<body onLoad='Regu.set_focus(document.getElementById("start", ""))' style='overflow:hidden;'>
    <center>
<?= $menu->out_title_border() ?>
    <div class='pt12b'><?php echo $menu->out_caption()?></div>
    <div class='pt12b'>&nbsp;</div>
    <table class='layout'>
    <tr class='layout'>
        <td class='layout' id='start'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("internal_control_kihon10.06.22.pdf", "")'
                onMouseover="status='内部統制システムの基本方針を表示します。';return true;"
                onMouseout="status=''"
                title='内部統制システムの基本方針を表示します。'
            >内部統制システムの基本方針</a>
        </td>
        <?php
        //if ($_SESSION['User_ID'] == '300144') {
        if ($today >= 20150401) {
        ?>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("internal_control_yushutsu15.08.03.pdf", "")'
                onMouseover="status='安全保障輸出管理規程を表示します。';return true;"
                onMouseout="status=''"
                title='安全保障輸出管理規程を表示します。'
            >安全保障輸出管理規程</a>
        </td>
        <?php
        } else {
        ?>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("internal_control_kitei13.04.01.pdf", "")'
                onMouseover="status='内部統制規程を表示します。';return true;"
                onMouseout="status=''"
                title='内部統制規程を表示します。'
            >内部統制規程</a>
            &nbsp;
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("internal_control_kitei_jyokyo13.04.01.pdf", "")'
                onMouseover="status='内部統制システムの状況（組織）を表示します。';return true;"
                onMouseout="status=''"
                title='内部統制システムの状況（組織）を表示します。'
            >状況(組織)</a>
        </td>
        <?php
        }
        ?>
    </tr>
    <?php
    //if ($_SESSION['User_ID'] == '300144') {
    if ($today >= 20150401) {
    ?>
    <?php
    } else {
    ?>
    <tr class='layout'>
        <td class='layout' colspan='2'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("internal_control_yushutsu07.04.01.pdf", "")'
                onMouseover="status='安全保障輸出管理規程を表示します。';return true;"
                onMouseout="status=''"
                title='安全保障輸出管理規程を表示します。'
            >安全保障輸出管理規程</a>
        </td>
    </tr>
    <?php
    }
    if ($today >= 20150803) {
    ?>
    <tr class='layout'>
        <td class='layout' colspan='2' style='border-bottom:none;'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("internal_control_yushutsu_sai15.08.03.pdf", "")'
                onMouseover="status='安全保障輸出管理規程細則を表示します。';return true;"
                onMouseout="status=''"
                title='安全保障輸出管理規程細則を表示します。'
            >安全保障輸出管理規程細則</a>
            &nbsp;
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("internal_control_yushutsu_sai_beshi1-12_15.08.03.pdf", "")'
                onMouseover="status='安全保障輸出管理規程細則の別添１〜１２を表示します。';return true;"
                onMouseout="status=''"
                title='安全保障輸出管理規程細則の別添１〜１２を表示します。'
            >別添１〜１２</a>
        </td>
    </tr>
    <?php
    } else {
    ?>
    <tr class='layout'>
        <td class='layout' colspan='2' style='border-bottom:none;'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("internal_control_yushutsu_sai07.04.01.pdf", "")'
                onMouseover="status='安全保障輸出管理規程細則を表示します。';return true;"
                onMouseout="status=''"
                title='安全保障輸出管理規程細則を表示します。'
            >安全保障輸出管理規程細則</a>
            &nbsp;
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("internal_control_yushutsu_sai_beshi1_07.04.12.pdf", "")'
                onMouseover="status='安全保障輸出管理規程細則の別添１を表示します。';return true;"
                onMouseout="status=''"
                title='安全保障輸出管理規程細則の別添１を表示します。'
            >別添１</a>
            &nbsp;
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("internal_control_yushutsu_sai_beshi2_07.04.12.pdf", "")'
                onMouseover="status='安全保障輸出管理規程細則の別添２を表示します。';return true;"
                onMouseout="status=''"
                title='安全保障輸出管理規程細則の別添２を表示します。'
            >別添２</a>
            &nbsp;
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("internal_control_yushutsu_sai_beshi3_07.04.20.pdf", "")'
                onMouseover="status='安全保障輸出管理規程細則の別添３を表示します。';return true;"
                onMouseout="status=''"
                title='安全保障輸出管理規程細則の別添３を表示します。'
            >別添３</a>
            &nbsp;
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("internal_control_yushutsu_sai_beshi4_07.04.12.pdf", "")'
                onMouseover="status='安全保障輸出管理規程細則の別添４を表示します。';return true;"
                onMouseout="status=''"
                title='安全保障輸出管理規程細則の別添４を表示します。'
            >別添４</a>
            &nbsp;
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("internal_control_yushutsu_sai_beshi5_07.04.12.pdf", "")'
                onMouseover="status='安全保障輸出管理規程細則の別添５を表示します。';return true;"
                onMouseout="status=''"
                title='安全保障輸出管理規程細則の別添５を表示します。'
            >別添５</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout' colspan='2' style='border-top:none;'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("internal_control_yushutsu_sai_beshi6_07.04.12.pdf", "")'
                onMouseover="status='安全保障輸出管理規程細則の別添６を表示します。';return true;"
                onMouseout="status=''"
                title='安全保障輸出管理規程細則の別添６を表示します。'
            >別添６</a>
            &nbsp;
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("internal_control_yushutsu_sai_beshi7_07.04.12.pdf", "")'
                onMouseover="status='安全保障輸出管理規程細則の別添７を表示します。';return true;"
                onMouseout="status=''"
                title='安全保障輸出管理規程細則の別添７を表示します。'
            >別添７</a>
            &nbsp;
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("internal_control_yushutsu_sai_beshi8_07.04.12.pdf", "")'
                onMouseover="status='安全保障輸出管理規程細則の別添８を表示します。';return true;"
                onMouseout="status=''"
                title='安全保障輸出管理規程細則の別添８を表示します。'
            >別添８</a>
            &nbsp;
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("internal_control_yushutsu_sai_beshi9_07.04.12.pdf", "")'
                onMouseover="status='安全保障輸出管理規程細則の別添９を表示します。';return true;"
                onMouseout="status=''"
                title='安全保障輸出管理規程細則の別添９を表示します。'
            >別添９</a>
            &nbsp;
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("internal_control_yushutsu_sai_beshi10_07.04.13.pdf", "")'
                onMouseover="status='安全保障輸出管理規程細則の別添１０を表示します。';return true;"
                onMouseout="status=''"
                title='安全保障輸出管理規程細則の別添１０を表示します。'
            >別添１０</a>
            &nbsp;
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("internal_control_yushutsu_sai_beshi11_07.04.13.pdf", "")'
                onMouseover="status='安全保障輸出管理規程細則の別添１１を表示します。';return true;"
                onMouseout="status=''"
                title='安全保障輸出管理規程細則の別添１１を表示します。'
            >別添１１</a>
            &nbsp;
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("internal_control_yushutsu_sai_beshi12_07.04.13.pdf", "")'
                onMouseover="status='安全保障輸出管理規程細則の別添１２を表示します。';return true;"
                onMouseout="status=''"
                title='安全保障輸出管理規程細則の別添１２を表示します。'
            >別添１２</a>
        </td>
    </tr>
    <?php
    }
    ?>
    </table>
    </center>
</body>
<?=$menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
