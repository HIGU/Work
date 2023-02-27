<?php
//////////////////////////////////////////////////////////////////////////////
// 栃木日東工器 トップ メニュー (TOP MENU)                                  //
// Copyright(C) 2001-2015 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2001/07/07 Created   menu.php                                            //
// 2002/08/07 セッション管理を追加 & register_globals = Off 対応            //
// 2003/02/14 TNK TOP MENU のフォントを style で指定に変更                  //
//                              ブラウザーによる変更が出来ない様にした      //
// 2003/11/17 経理日報処理メニューを追加  動的メニューアイコンに変更        //
// 2003/12/10 top_font→caption_fontへ $menu_caption(TNK TOP MENU)追加      //
// 2003/12/12 defineされた定数でディレクトリとメニューを使用して管理する    //
//            ob_start('ob_gzhandler') を追加                               //
// 2003/12/22 PL_MENUにあった menuOFFを TOP_MENUにも追加                    //
// 2004/02/13 index1.php→index.phpへ変更(index1はauthenticateに変更のため) //
// 2004/06/10 view_user($_SESSION['User_ID']) をメニューヘッダーの下に追加  //
// 2004/07/06 設備メニュー 呼出先を 設備メニュー2 へ変更                    //
// 2004/07/26 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2004/09/27 設備メニューを EQUIP_MENU2→'equip_factory_select.php'へ変更  //
// 2004/12/25 style='overflow-y:hidden;' を追加                             //
// 2005/01/14 F2/F12キーを有効化する対応のため document.body.focus()を追加  //
// 2005/08/02 各メニュー間の<br>レイアウトを<div>&nbsp;</div>へ変更NN対応   //
// 2005/08/20 $menu->set_RetUrl()をコメント MenuHeader でロジック対応のため //
// 2005/09/02 logout.php の場合は target='application → target='_parent'へ //
// 2005/09/12 環境情報リセットのbaseJS.EnvInfoReset()を右下隅に追加         //
// 2006/07/12 会社の基本カレンダー照会・編集メニューのset_action()追加      //
// 2007/08/23 スクロール型TNKのマップを搭載(ALPS MAPPING K.K)               //
// 2008/08/29 品質メニューQUALITY_MENU 追加                            大谷 //
// 2010/04/09 ALPS MAPPING K.K サービス終了の為、マップ表示をコメント  大谷 //
// 2010/10/05 資産管理メニュー ASSET_MENU 追加                         大谷 //
// 2013/11/11 届出申請書メニュー PER_APPLI_MENU(人事)                       //
//                               ACT_APPLI_MENU(経理) 追加             大谷 //
// 2014/09/29 届出申請書メニューを社内と社外へ変更                     大谷 //
// 2015/02/17 届出申請書メニューを社内と社外区分をなくした             大谷 //
// 2021/07/07 品質メニューを品質・環境メニューへ変更                   和氣 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 WEB CGI版
session_start();                            // ini_set()の次に指定すること Script 最上行
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮

require_once ('function.php');              // 全共用 define.php と pgsql.php を require_once している
require_once ('tnk_func.php');              // menu_bar() カレンダーで使用
require_once ('MenuHeader.php');            // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(0, 999);                    // site_index=0(TOPメニュー) site_id=999(siteを開く)
////////////// リターンアドレス設定
// $menu->set_RetUrl(TOP_MENU);                // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('トップ メニュー');
//////////// 表題の設定
$menu->set_caption('TNK TOP MENU');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('indust_menu' , INDUST_MENU);
$menu->set_action('sales_menu'  , SALES_MENU);
$menu->set_action('equip_menu2' , EQUIP2 . 'equip_factory_select.php');
$menu->set_action('emp_menu'    , EMP_MENU);
// $menu->set_action('genka_menu' , TOP_MENU);  // 原価メニューを作成中
$menu->set_action('pl_menu'     , PL_MENU);
$menu->set_action('act_menu'    , ACT_MENU);
$menu->set_action('dev_menu'    , DEV_MENU);
$menu->set_action('sys_menu'    , SYS_MENU);
$menu->set_action('社内規程'    , REGU_MENU);
$menu->set_action('quality_menu', QUALITY_MENU);
$menu->set_action('asset_menu', ASSET_MENU);
$menu->set_action('per_appli_menu', PER_APPLI_MENU);
$menu->set_action('act_appli_menu', ACT_APPLI_MENU);
// 現在はダミーでセット
$menu->set_action('会社カレンダー'   , SYS . 'calendar/companyCalendar_Main.php');

//////////// ブラウザーのキャッシュ対策用
$uniq = $menu->set_useNotCache('target');

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();

$uid   = $_SESSION['User_ID'];
$query = "SELECT sid FROM user_detailes WHERE uid='$uid'";
$res   = array();
getResult($query,$res);
$sid   = $res[0][0];

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

<style type="text/css">
<!--
/** font-weight: normal;        **/
/** font-weight: 400;    と同じ **/
/** font-weight: bold;          **/
/** font-weight: 700;    と同じ **/
/**         100〜900まで100刻み **/
.caption_font {
    font-size:   12pt;
    font-family: serif;
    font-weight: bold;
}
.OnOff_font {
    font-size:     8.5pt;
    font-family:   monospace;
}
-->
</style>
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
<body style='overflow-y:hidden;' onLoad='set_focus()'>
    <center>
<?= $menu->out_title_border() ?>
        
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <tr>
                <td align='center'><img src='<?= IMG ?>t_nitto_logo2.gif' width=348 height=83></td>
            </tr>
            <tr>
                <td align='center' class='caption_font'>
                    <?= $menu->out_caption() ?>
                </td>
            </tr>
        </table>
        
        <table width='70%' border='0'> <!-- widthで間隔を調整 -->
        <tr>
            <!-- /////////////// left view ////////////// -->
        <td align='center' valign='top'>
            <table border='0'>
                <tr>
                    <form method='post' action='<?= $menu->out_action('indust_menu') ?>'>
                        <td align='center'>
                            <input type='image' alt='生産 関係 処理メニュー' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_industry_menu.png', '  生 産 メニュー', 14) . "?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                <?php
                if ($sid != '95') {
                ?>
                <tr>
                    <form method='post' action='<?= $menu->out_action('sales_menu') ?>'>
                        <td align="center">
                            <!-- <input type='image' value='売上関係照会メニュー' border=0 src='<?php echo IMG ?>menu_item_urimenu.gif'> -->
                            <input type='image' alt='売上 関係 照会メニュー' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_sales_menu.png', '  売 上 メニュー', 14) . "?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?= $menu->out_action('equip_menu2') ?>'>
                        <td align="center">
                            <!-- <input type='image' alt='設備管理' border=0 src='<?php echo IMG ?>menu_item_equipment.gif'> -->
                            <input type='image' alt='設備・機械 稼動管理システム 全体メニュー' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_equip_menu.png', '  設 備 メニュー', 14, 0) . "?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?= $menu->out_action('emp_menu') ?>'>
                        <td align="center">
                            <!-- <input type='image' value='人事(社員情報管理)' border=0 src='<?php echo IMG ?>menu_item_employ.gif'> -->
                            <input type='image' alt='社員の教育・訓練・研修・講習会 等の記録照会' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_emp_menu.png', '  社 員 メニュー', 14) . "?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?=$menu->out_action('社内規程') ?>'>
                        <td align='center'>
                            <input type='image' alt='社内規程 照会 メニュー' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_regulation_menu.png', '  規 程 メニュー', 14) . "?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                <tr>
                    <form method='post' action='<?=$menu->out_action('per_appli_menu') ?>'>
                        <td align='center'>
                            <input type='image' alt='届出・申請書 メニュー(社内)' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_per_appli_menu.png', '  届出・申請書 メニュー', 10) . "?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                <tr>
                    <form method='post' action='<?=$menu->out_action('quality_menu') ?>'>
                        <td align='center'>
                            <input type='image' alt='品質・環境 メニュー' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_quality_environment_menu.png', '  品 質・環境 メニュー', 12) . "?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
            <!--
                <tr>
                    <form method='post' action='<?php echo TOP_MENU ?>'>
                        <td align='center'>
                            <input type='image' alt='原価 計算 処理メニュー' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_costAct_menu.png', '  原 価 メニュー', 14) . "?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
            -->
            <?php
            }
            ?>
            </table>
        </td>
            <!-- /////////////// right view ////////////// -->
        <td align='center' valign='top'>
            <table border='0'>
                <?php
                if ($sid != '95') {
                ?>
                <tr>
                    <form method='post' action='<?= $menu->out_action('pl_menu') ?>'>
                        <td align="center">
                            <!-- <input type='image' alt='月次・中間・決算処理メニュー' border=0 src='<?php echo IMG ?>menu_item_kessan_menu.gif'> -->
                            <input type='image' alt='損益の照会・作成 処理メニュー' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_pl_menu.png', '  損 益 メニュー', 14) . "?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                <tr>
                    <form method='post' action='<?= $menu->out_action('act_menu') ?>'>
                        <td align="center">
                            <input type='image' alt='経理 関係 処理メニュー' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_act_menu.png', '  経 理 メニュー', 14, 0) . "?$uniq" ?>'>
                            <!-- <input type='image' value='空のアイテム' border=0 src='<?php echo IMG ?>menu_item.gif'> -->
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?= $menu->out_action('asset_menu') ?>'>
                        <td align="center">
                            <input type='image' alt='資産管理 処理メニュー' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_asset_menu.png', '  資産管理 メニュー', 14) . "?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?= $menu->out_action('dev_menu') ?>'>
                        <td align="center">
                            <!-- <input type='image' value='プログラム開発' border=0 src='<?php echo IMG ?>menu_item_develop.gif'> -->
                            <input type='image' alt='プログラム開発依頼書 送信・照会' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_dev_req_menu.png', '  開 発 メニュー', 14) . "?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?= $menu->out_action('sys_menu') ?>'>
                        <td align="center">
                            <!-- <input type='image' value='システム管理' border=0 src='<?php echo IMG ?>menu_item_edp.gif'> -->
                            <input type='image' alt='システム管理者用 処理メニュー' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_system_menu.png', '  管 理 メニュー', 14) . "?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                <?php if (getCheckAuthority(38)) { ?>
                <tr>
                    <form method='post' action='<?= $menu->out_action('act_appli_menu') ?>'>
                        <td align="center">
                            <input type='image' alt='届出・申請書メニュー(社外)' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_act_appli_menu.png', '  届出・申請書 メニュー(社外)', 10) . "?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                <?php } else { ?>
                <tr>
                    <td align='center'>
                        <input type='image' name='post' alt='空のアイテム' border=0 src='<?php echo IMG ?>menu_item.gif'>
                        <div>&nbsp;</div>
                    </td>
                </tr>
                <?php } ?>
                <?php
                }
                ?>
                <tr>
                    <form method='post' action='<?= ROOT, 'logout.php' ?>' target='_parent'>
                        <td align="center">
                            <!-- <input type='image' value='空のアイテム' border=0 src='<?php echo IMG ?>menu_item.gif'> -->
                            <input type='image' alt='個人のセッションを終了します。' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_logout.png', '  終 了 (ログアウト)', 14) . "?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
            </table>
        </td>
        </tr>
        </table>
        <!-- ALPS MAP サービス終了の為、コメント化 -->
        <!--<script type='text/javascript' src='/test/ALPS_MAPPING/scrollmap.js'></script> -->
        <!-- <script type='text/javascript' src='http://slide.alpslab.jp/scrollmap.js'></script> -->
        <!--<div class='alpslab-slide'> -->
        <!--    scale:70000 36/42/22.299,139/58/5.726 -->
        <!--    <a href='http://base.alpslab.jp/?s=25000;p=36/42/22.299,139/58/5.726' target='_blank'><img src='http://clip.alpslab.jp/bin/map?pos=36/42/22.299,139/58/5.726&scale=25000'></a> -->
        <!--</div> -->
        <span style='position:absolute; bottom:1px; right:1px;'>
            <input type='button' name='envReset' value='環境リセット' onClick='baseJS.EnvInfoReset();'
                onMouseover='status="Windowの位置や大きさ及び開いているか等の情報を初期状態に戻します。"; return true;'
                onMouseout='status=""'
                title='Windowの位置や大きさ及び開いているか等の情報を初期状態に戻します。'
            >
        </span>
    </center>
</body>
<?=$menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
