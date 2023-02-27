<?php
//////////////////////////////////////////////////////////////////////////////
// 届出・申請書メニュー（社内）                                             //
// Copyright(C) 2013-2020 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// Changed history                                                          //
// 2013/11/11 Created  per_appli_menu.php                                   //
// 2014/09/18 各メニューのリンク先をフォルダに変更                          //
// 2014/09/22 captionの内容を変更                                           //
// 2020/09/25 総合届の申請・照会・承認・マスターを追加                 和氣 //
// 2021/11/10 定時間外作業申告申請・照会・承認を追加                   和氣 //
// 2022/04/10 食堂メニュー予約を追加                                   和氣 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug 用
// ini_set('display_errors', '1');         // Error 表示 ON debug 用 リリース後コメント
session_start();                        // ini_set()の次に指定すること Script 最上行
ob_start('ob_gzhandler');               // 出力バッファをgzip圧縮

require_once ('../function.php');       // TNK 全共通 function
require_once ('../MenuHeader.php');     // TNK 全共通 menu class
require_once ('../tnk_func.php');
access_log();                           // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);    // 認証レベル=0, リターンアドレス, タイトルの指定なし

////////////// サイト設定
$menu->set_site(97, 999);                // site_index=4(プログラム開発) site_id=999(子メニューあり)
////////////// リターンアドレス設定
$menu->set_RetUrl(TOP_MENU);            // 上で設定している
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('届出・申請書メニュー（社内）');
//////////// 表題の設定
$menu->set_caption('社内の各種届出用紙をダウンロード出来ます。');
//////////// 呼出先のaction名とアドレス設定
// 社内・社外別メニュー
$menu->set_action('人事に関する届出',    PER_APPLI . 'in_personnel_appli/in_personnel_appli_menu.php');
$menu->set_action('経理に関する届出',      PER_APPLI . 'in_account_appli/in_account_appli_menu.php');
$menu->set_action('総務に関する届出',  PER_APPLI . 'in_affairs_appli/in_affairs_appli_menu.php');
//$menu->set_action('営繕に関する届出',  PER_APPLI . 'in_repair_appli/in_repair_appli_menu.php');
$menu->set_action('その他の届出',  PER_APPLI . 'in_other_appli/in_other_appli_menu.php');
$menu->set_action('総合届（申請）',  PER_APPLI . 'in_sougou/sougou_Main.php');
$menu->set_action('総合届（承認）',  PER_APPLI . 'in_sougou_admit/sougou_admit_Main.php');
$menu->set_action('総合届（マスター）',  PER_APPLI . 'in_sougou_master/sougou_master_Main.php');
$menu->set_action('総合届（照会）',  PER_APPLI . 'in_sougou_query/sougou_query_Main.php');

$menu->set_action('定時間外作業申告',  PER_APPLI . 'over_time_work_report/over_time_work_report_Main.php');

$menu->set_action('食堂メニュー予約',  PER_APPLI . 'meal_appli/meal_appli_Main.php');

// 以下は旧メニュー
$menu->set_action('勤怠に関する届出',    PER_APPLI . 'service_appli/service_appli_menu.php');
$menu->set_action('給与に関する届出',      PER_APPLI . 'supply_appli/supply_appli_menu.php');
$menu->set_action('住所変更に関する届出',  PER_APPLI . 'address_appli/address_appli_menu.php');
$menu->set_action('結婚に関する届出',  PER_APPLI . 'marriage_appli/marriage_appli_menu.php');
$menu->set_action('出産に関する届出',  PER_APPLI . 'childbirth_appli/childbirth_appli_menu.php');
$menu->set_action('扶養者の増加に関する届出',  PER_APPLI . 'support_inc_appli/support_inc_appli_menu.php');
$menu->set_action('扶養者の減少に関する届出',  PER_APPLI . 'support_dec_appli/support_dec_appli_menu.php');
$menu->set_action('弔慰に関する届出',  PER_APPLI . 'condol_appli/condol_appli_menu.php');
$menu->set_action('マイカー変更に関する届出',  PER_APPLI . 'mycar_appli/mycar_appli_menu.php');
$menu->set_action('公的資格取得に関する届出',  PER_APPLI . 'capacity_appli/capacity_appli_menu.php');
$menu->set_action('教育訓練に関する届出',  PER_APPLI . 'training_appli/training_appli_menu.php');


/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?= $menu->out_title() ?></title>
<style type="text/css">
<!--
.test_font {
    font-size: 12pt;
    font-weight: bold;
    font-family: monospace;
}
-->
</style>
<?= $menu->out_css() ?>
<script type='text/javascript'>
<!--
function set_focus()
{
    // document.body.focus();   // F2/F12キーを有効化する対応
    // document.mhForm.backwardStack.focus();  // 上記はIEのみのためNN対応
}
// -->
</script>
</head>
<body style='overflow:hidden;' onLoad='set_focus()'>
    <center>
<?= $menu->out_title_border() ?>
        <table border='0'>
            <tr>
                <td>
                    <p><img src='<?php echo IMG ?>t_nitto_logo3.gif' width='348' height='83'></p>
                </td>
            </tr>
            <tr>
                <td align='center' class='caption_font'>
                    <?= $menu->out_caption() . "\n" ?>
                </td>
            </tr>
        </table>
        <table border='0'>
            <tr>
                <td align='center'>
                    <img src='<?php echo IMG ?>tnk-turbine.gif' width='68' height='72'>
                </td>
            </tr>
        </table>
        <table width='80%' border='0'>
            <!-- 社内・社外別メニュー -->
            <tr>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('人事に関する届出') ?>">
                        <input type='image' alt='人事に関する届出メニュー' border=0 src='<?php echo menu_bar("menu_tmp/in_personnel_appli.png","人事に関する届出",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('経理に関する届出') ?>">
                        <input type='image' alt='経理に関する届出メニュー' border=0 src='<?php echo menu_bar("menu_tmp/in_account_appli.png","経理に関する届出",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
            </tr>
            <tr>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('総務に関する届出') ?>">
                        <input type='image' alt='総務に関する届出メニュー' border=0 src='<?php echo menu_bar("menu_tmp/in_affairs_appli.png","総務に関する届出",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('その他の届出') ?>">
                        <input type='image' alt='その他の届出メニュー' border=0 src='<?php echo menu_bar("menu_tmp/in_other_appli.png","その他の届出",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
            </tr>
            <tr>
                <!--
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('営繕に関する届出') ?>">
                        <input type='image' alt='営繕に関する届出メニュー' border=0 src='<?php echo menu_bar("menu_tmp/in_repair_appli.png","営繕に関する届出",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                -->
                <td align='center'>
                    <form method="post" action="<?= $menu->out_action('総合届（申請）') ?>">
                        <input type='image' alt='総合届（申請）' border=0 src='<?php echo menu_bar("menu_tmp/image_sinsei.png","総合届（申請）",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <td align='center'>
                    <form method="post" action="<?= $menu->out_action('総合届（照会）') ?>">
                        <input type='image' alt='総合届（照会）' border=0 src='<?php echo menu_bar("menu_tmp/image_syoukai.png","総合届（照会）",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
            </tr>

            <tr>
                <td align='center'>
                    <?php
                    if(getCheckAuthority(64)) { // 64:承認可能
                    ?>
                    <form method="post" action="<?= $menu->out_action('総合届（承認）') ?>">
                        <input type='image' alt='総合届（承認）' border=0 src='<?php echo menu_bar("menu_tmp/image_syounin.png","総合届（承認）",13)."?".uniqid("menu") ?>'>
                    </form>
                    <?php
                    } else {
                    ?>
                        <input type='image' name='post' alt='空のアイテム' border=0 src='<?php echo IMG ?>menu_item.gif'>
                    <?php
                    }
                    ?>
                    <div>&nbsp;</div>
                </td>

                <td align='center'>
                    <?php
                    if(getCheckAuthority(65)) { // 65:マスター編集可能
                    ?>
                    <form method="post" action="<?= $menu->out_action('総合届（マスター）') ?>">
                        <input type='image' alt='総合届（マスター）' border=0 src='<?php echo menu_bar("menu_tmp/image_master.png","総合届（マスター）",13)."?".uniqid("menu") ?>'>
                    </form>
                    <?php
                    } else {
                    ?>
                        <input type='image' name='post' alt='空のアイテム' border=0 src='<?php echo IMG ?>menu_item.gif'>
                    <?php
                    }
                    ?>
                    <div>&nbsp;</div>
                </td>
            </tr>

            <tr>
                <td align='center'>
                    <form method="post" action="<?= $menu->out_action('定時間外作業申告') . '?showMenu=Appli' ?>">
                        <input type='image' alt='定時間外作業申告（入力）' border=0 src='<?php echo menu_bar("menu_tmp/over_time_input.png","定時間外作業申告（入力）",12)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <td align='center'>
                    <form method="post" action="<?= $menu->out_action('定時間外作業申告') . '?showMenu=Quiry' ?>">
                        <input type='image' alt='定時間外作業申告（照会）' border=0 src='<?php echo menu_bar("menu_tmp/over_time_quiry.png","定時間外作業申告（照会）",12)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
            </tr>

            <tr>
                <td align='center'>
                    <form method="post" action="<?= $menu->out_action('定時間外作業申告') . '?showMenu=Judge' ?>">
                        <input type='image' alt='定時間外作業申告（承認）' border=0 src='<?php echo menu_bar("menu_tmp/over_time_admit.png","定時間外作業申告（承認）",12)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <td align='center'>
                    <form method='post' action='<?php echo $menu->out_self() ?>'>
                        <input type='image' name='post' alt='空のアイテム' border=0 src='<?php echo IMG ?>menu_item.gif'>
                    </form>
                    <div>&nbsp;</div>
                </td>
            </tr>

            <tr>
                <td align='center'>
                    <form method="post" action="<?= $menu->out_action('食堂メニュー予約') ?>">
                        <input type='image' alt='食堂メニュー予約' border=0 src='<?php echo menu_bar("menu_tmp/meal_appli.png","食堂メニュー予約",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>

                <td align='center'>
                    <form method='post' action='<?php echo $menu->out_self() ?>'>
                        <input type='image' name='post' alt='空のアイテム' border=0 src='<?php echo IMG ?>menu_item.gif'>
                    </form>
                    <div>&nbsp;</div>
                </td>
            </tr>
            
            <!-- 旧メニュー 
            <tr>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('勤怠に関する届出') ?>">
                        <input type='image' alt='勤怠に関する届出メニュー' border=0 src='<?php echo menu_bar("menu_tmp/service_appli_menu.png","勤怠に関する届出",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('給与に関する届出') ?>">
                        <input type='image' alt='給与に関する届出メニュー' border=0 src='<?php echo menu_bar("menu_tmp/supply_appli_menu.png","給与に関する届出",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
            </tr>
            <tr>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('住所変更に関する届出') ?>">
                        <input type='image' alt='住所変更に関する届出メニュー' border=0 src='<?php echo menu_bar("menu_tmp/address_appli_menu.png","住所変更に関する届出",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('結婚に関する届出') ?>">
                        <input type='image' alt='結婚に関する届出メニュー' border=0 src='<?php echo menu_bar("menu_tmp/marriage_appli_menu.png","結婚に関する届出",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
            </tr>
            <tr>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('出産に関する届出') ?>">
                        <input type='image' alt='出産に関する届出メニュー' border=0 src='<?php echo menu_bar("menu_tmp/childbirth_appli_menu.png","出産に関する届出",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('扶養者の増加に関する届出') ?>">
                        <input type='image' alt='扶養者の増加に関する届出メニュー' border=0 src='<?php echo menu_bar("menu_tmp/support_inc_appli_menu.png","扶養者の増加に関する届出",11)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
            </tr>
            <tr>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('扶養者の減少に関する届出') ?>">
                        <input type='image' alt='扶養者の減少に関する届出メニュー' border=0 src='<?php echo menu_bar("menu_tmp/support_dec_appli_menu.png","扶養者の減少に関する届出",11)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('弔慰に関する届出') ?>">
                        <input type='image' alt='弔慰に関する届出メニュー' border=0 src='<?php echo menu_bar("menu_tmp/condol_appli_menu.png","弔慰に関する届出",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
            </tr>
            <tr>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('マイカー変更に関する届出') ?>">
                        <input type='image' alt='マイカー変更に関する届出メニュー' border=0 src='<?php echo menu_bar("menu_tmp/mycar_appli_menu.png","マイカー変更に関する届出",11)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('公的資格取得に関する届出') ?>">
                        <input type='image' alt='公的資格取得に関する届出メニュー' border=0 src='<?php echo menu_bar("menu_tmp/capacity_appli_menu.png","公的資格取得に関する届出",11)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
            </tr>
            <tr>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('教育訓練に関する届出') ?>">
                        <input type='image' alt='教育訓練に関する届出メニュー' border=0 src='<?php echo menu_bar("menu_tmp/training_appli_menu.png","教育訓練に関する届出",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <td align='center'>
                    <input type='image' name='post' alt='空のアイテム' border=0 src='<?php echo IMG ?>menu_item.gif'>
                </td>
            </tr>
            -->
        </table>
    </center>
</body>
<?= $menu->out_site_java() ?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
