<?php
//////////////////////////////////////////////////////////////////////////////
// 設備 管理 メニュー2 (新版)                                               //
// Copyright (C) 2002-2018 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2021/03/24 Created   equip_menu.php --> equip_menu_moni.php              //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
session_start();                            // ini_set()の次に指定すること Script 最上行
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮

require_once ('equip_function.php');        // 設備関係専用 (内部でfunction.phpを呼出している)
require_once ('../tnk_func.php');           // menu_bar() で使用
require_once ('../MenuHeader.php');         // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=''TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(40, 999);                   // site_index=40(設備メニュー2) site_id=999(siteを開く)
////////////// リターンアドレス設定
// $menu->set_RetUrl(TOP_MENU);
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
// $menu->set_title('設 備 全工場 メニュー');
if (isset($_REQUEST['factory'])) {
    switch ($_REQUEST['factory']) {
    case 1:
        $_SESSION['factory'] = '1';         // 設備 １工場メニュー
        $menu->set_title('設 備 １工場 メニュー');
        break;
    case 2:
        $_SESSION['factory'] = '2';         // 設備 １工場メニュー
        $menu->set_title('設 備 ２工場 メニュー');
        break;
    case 3:
        $_SESSION['factory'] = '3';         // 設備 １工場メニュー
        $menu->set_title('設 備 ３工場 メニュー');
        break;
    case 4:
        $_SESSION['factory'] = '4';         // 設備 ４工場メニュー
        $menu->set_title('設 備 ４工場 メニュー');
        break;
    case 5:
        $_SESSION['factory'] = '5';         // 設備 １工場メニュー
        $menu->set_title('設 備 ５工場 メニュー');
        break;
    case 6:
        $_SESSION['factory'] = '6';         // 設備 １工場メニュー
        $menu->set_title('設 備 ６工場 メニュー');
        break;
    case 7:
        $_SESSION['factory'] = '7';         // 設備 ７工場(真鍮)メニュー
        $menu->set_title('設 備 ７工場(真鍮) メニュー');
        break;
    case 8:
        $_SESSION['factory'] = '8';         // 設備 ７工場(SUS)メニュー
        $menu->set_title('設 備 ７工場(SUS) メニュー');
        break;
    default:
        $_SESSION['factory'] = '';          // 設備 全工場メニュー
        $menu->set_title('設 備 全工場 メニュー');
        break;
    }
} else {
    if (isset($_SESSION['factory'])) {
        $factory = $_SESSION['factory'];
    } else {
        $factory = '';
        $_SESSION['factory'] = $factory;
    }
    switch ($factory) {
    case 1:
        $menu->set_title('設 備 １工場 メニュー');
        break;
    case 2:
        $menu->set_title('設 備 ２工場 メニュー');
        break;
    case 3:
        $menu->set_title('設 備 ３工場 メニュー');
        break;
    case 4:
        $menu->set_title('設 備 ４工場 メニュー');
        break;
    case 5:
        $menu->set_title('設 備 ５工場 メニュー');
        break;
    case 6:
        $menu->set_title('設 備 ６工場 メニュー');
        break;
    case 7:
        $menu->set_title('設 備 ７工場(真鍮) メニュー');
        break;
    case 8:
        $menu->set_title('設 備 ７工場(SUS) メニュー');
        break;
    default:
        $menu->set_title('設 備 全工場 メニュー');
        break;
    }
}
if (isset($_REQUEST['factory_select'])) {
    // 工場のリクエストが無ければPOP UP WINを表示して選択フォームへ
    $menu->set_title('設 備 全工場 メニュー リクエストなし');
}

//////////// 表題の設定
$menu->set_caption('設備 稼動管理 メニュー');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('運転状況',           EQUIP2 . 'work/equip_working_disp.php');
$menu->set_action('現在グラフ',         EQUIP2 . 'work/equip_working_disp.php');
$menu->set_action('加工実績',           EQUIP2 . 'hist/equip_jisseki_select_moni.php');
$menu->set_action('運転日報',           EQUIP2 . 'daily_report_moni/EquipMenu.php');
$menu->set_action('現在稼動表',         EQUIP2 . 'work/equip_work_chart.php');
$menu->set_action('運転グラフ',         EQUIP2 . 'work/equip_work_graph.php');
//$menu->set_action('加工指示'  ,         EQUIP2 . 'work_mnt/equip_workMnt_Main.php');
$menu->set_action('加工指示'  ,         EQUIP2 . 'monitoring/monitoring_Main.php');
// $menu->set_action('予定保守',           EQUIP2 . 'equip_plan_mnt.php');
$menu->set_action('日報グラフ',         EQUIP2 . 'daily_report/equip_report_graph.php');
$menu->set_action('スケジュール',       EQUIP2 . 'plan/equip_plan_graph.php');
$menu->set_action('運転中一覧',         EQUIP2 . 'work/equip_work_moni.php?view="一覧"');
$menu->set_action('運転状況マップ',     EQUIP2 . 'work/equip_work_map.php');
// $menu->set_action('機械マスター',       EQUIP2 . 'master/equip_mac_mst_mnt.php');
$menu->set_action('機械マスター',       EQUIP2 . 'master/equip_macMasterMnt_Main.php');
$menu->set_action('インターフェース',   EQUIP2 . 'master/equip_interfaceMaster_Main.php');
$menu->set_action('カウンター',         EQUIP2 . 'master/equip_counterMaster_Main.php');
$menu->set_action('停止の定義',         EQUIP2 . 'master/equip_stopMaster_Main.php');
$menu->set_action('機械のinterface',    EQUIP2 . 'master/equip_machineInterface_Main.php');
$menu->set_action('グループマスター',   EQUIP2 . 'master/equip_groupMaster_Main.php');
// $menu->set_action('グラフ作成', EQUIP2 . 'equip_graph_create_all.php');
$menu->set_action('月報グラフ',         EQUIP2 . 'hist/equip_MonthReport_graph.php');

//////////////// 各アンカーに変数でセットする 関数コールのオーバーヘッドを１回で済ませるため
$uniq = uniqid('menu');

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

<!-- スタイルシートのファイル指定をコメント HTMLタグ コメントは入れ子に出来ない事に注意 -->
<link rel='stylesheet' href='equip_menu.css?<?php echo $uniq ?>' type='text/css' media='screen'>

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
<?php echo $menu->out_title_border() ?>
        
        <table width='100%'>
            <tr>
                <td align='center'><img src='<?php echo IMG ?>t_nitto_logo2.gif' width=348 height=83></td>
            </tr>
            <tr>
                <td align='center' class='caption_font'>
                    <?php echo $menu->out_caption(), "\n" ?>
                </td>
            </tr>
        </table>
        
        <br>
        
        <table width='70%' border='0'> <!-- widthで間隔を調整 -->
        <tr>
            <!-- /////////////// left view ////////////// -->
        <td align='center' valign='top'>
            <table border='0'>
<!--
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('運転状況'), '?status=chart' ?>'>
                        <td align='center'>
                            <input type='image' alt='現在 運転 状況 表形式(明細)の機械を選択するフォーム' border=0
                            src='<?php echo menu_bar("menu_tmp/menu_item_working_disp.png","現在運転 表形式 機械選択",11,0)."?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('現在グラフ'), '?status=graph' ?>'>
                        <td align='center'>
                            <input type='image' alt='現在 運転 状況 グラフの機械を選択するフォーム' border=0
                            src='<?php echo menu_bar("menu_tmp/menu_item_working_graph.png","現在運転 グラフ 機械選択",11,0)."?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
-->                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('加工実績') ?>'>
                        <td align='center'>
                            <input type='image' alt='加工実績照会(グラフ・集計表)' border=0
                            src='<?php echo menu_bar("menu_tmp/menu_item_equip_jisseki.png","加工実績 (グラフ・集計表)",11,0)."?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
<?php
    //if( $_SESSION['User_ID'] == '300667' || $_SESSION['User_ID'] == '300144') {
?>
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('運転日報') ?>'>
                        <td align='center'>
                            <?php if ($_SESSION['factory'] == '') {?>
                            <img src='<?= IMG ?>menu_item.gif' alt='全工場モードでは機械運転日報は使用できません！各工場を選択して下さい。' border='0'>
                            <?php } else { ?>
                            <input type='image' alt='機械運転日報 管理システム' border=0
                            src='<?php echo menu_bar("menu_tmp/menu_item_ope_daily_report.png"," 機 械 運 転 日 報",14)."?$uniq" ?>'>
                            <?php } ?>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
<?php
//}
?>
<!--
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('日報グラフ') ?>'>
                        <td align='center'>
                            <input type='image' alt='運転日報に対応したグラフを表示します。' border=0
                            src='<?php echo menu_bar("menu_tmp/menu_item_ope_report_graph.png"," 運 転 日 報 グラフ",14)."?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('現在稼動表') ?>'>
                        <td align='center'>
                            <input type='image' alt='現在稼動している機械の運転状況表示' border=0
                            src='<?php echo menu_bar("menu_tmp/menu_item_work_chart.png","現在運転状況 表形式", 14, 0)."?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('運転グラフ') ?>'>
                        <td align='center'>
                            <input type='image' alt='現在稼動している機械の運転状況をグラフ表示します。' border=0
                            src='<?php echo menu_bar("menu_tmp/menu_item_work_graph.png","現在運転状況 グラフ",14, 0)."?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('スケジュール') ?>'>
                        <td align='center'>
                            <input type='image' alt='スケジューラーの照会及びメンテナンス' border=0
                            src='<?php echo menu_bar("menu_tmp/menu_item_plan_mnt.png"," スケジューラー保守",14, 0)."?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('月報グラフ') ?>'>
                        <td align='center'>
                            <input type='image' alt='月次ベースの稼動時間グラフを表示します。' border=0
                            src='<?php echo menu_bar("menu_tmp/menu_item_MonthReport_graph.png","月次稼動時間 グラフ",14)."?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
            <!--
                <tr>
                    <form method='post' action='<?php echo EQUIP_MENU2 ?>'>
                        <td align='center'>
                            <input type='image' name='post' alt='空のアイテム' border=0 src='<?php echo IMG ?>menu_item.gif'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
            -->
                
            </table>
        </td>
            <!-- /////////////// right view ////////////// -->
        <td align='center' valign='top'>
            <table border='0'>
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('加工指示') ?>'> <!-- <?php echo IMG ?>menu_item_equip_seizou.gif -->
                        <td align='center'>
                            <input type='image' alt='加工指示入力・編集' border=0
                            src='<?php echo menu_bar("menu_tmp/menu_item_working_mnt.png"," 加工指示入力・編集",14)."?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('運転中一覧') ?>'>
                        <td align='center'>
                            <input type='image' alt='設備稼働管理システムに登録されている機械設備の全一覧を表示します。' border=0
                            src='<?php echo menu_bar("menu_tmp/menu_item_work_all.png","現在運転中の一覧表示",14)."?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>

<!--
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('運転状況マップ') ?>'>
                        <td align='center'>
                            <input type='image' name='post' alt='工場別マップ(レイアウト)式で稼動状況を表示します。' border=0
                            src='<?php echo menu_bar("menu_tmp/menu_item_equip_map4.png","工場別レイアウト表示",14, 0)."?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                    <!--
                    <form method='post' action='<?php echo $menu->out_action('グラフ作成') ?>'>
                        <td align='center'>
                            <input type='image' alt='加工指示書 別のグラフ一括作成' border=0
                            src='<?php echo menu_bar("menu_tmp/menu_item_equip_graph_create_all.png","指示別グラフ一括作成",14)."?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                    --><!--
                </tr>
-->
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('機械マスター') ?>'> <!-- <?php echo IMG ?>menu_item_equip_mac_mst.gif -->
                        <td align='center'>
                            <input type='image' alt='設備・機械マスター保守' border=0
                            src='<?php echo menu_bar("menu_tmp/menu_item_mac_mst_mnt.png"," 機械マスターの保守", 14, 0)."?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('インターフェース') ?>'> <!-- <?php echo IMG ?>menu_item_equip_mac_mst.gif -->
                        <td align='center'>
                            <input type='image' alt='設備・機械インターフェース マスター保守' border=0
                            src='<?php echo menu_bar("menu_tmp/menu_item_interfaceMaster.png","  インターフェース", 14, 0)."?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('カウンター') ?>'>
                        <td align='center'>
                            <input type='image' alt='設備・機械 カウンター マスター保守' border=0
                            src='<?php echo menu_bar("menu_tmp/menu_item_counterMaster.png"," カウンター マスター", 14, 0)."?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('停止の定義') ?>'>
                        <td align='center'>
                            <input type='image' alt='設備・機械 停止の定義 マスター保守' border=0
                            src='<?php echo menu_bar("menu_tmp/menu_item_stopMaster.png"," 停止の定義 マスター", 14, 0)."?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('機械のinterface') ?>'>
                        <td align='center'>
                            <input type='image' alt='機械毎のインターフェース マスター照会及び保守' border=0
                            src='<?php echo menu_bar("menu_tmp/menu_item_machineInterface.png","機械とインターフェース", 13, 0)."?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
<!--                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('グループマスター') ?>'>
                        <td align='center'>
                            <input type='image' alt='工場区分(グループ) マスター照会及び保守' border=0
                            src='<?php echo menu_bar("menu_tmp/menu_item_groupMaster.png"," 工場区分(グループ)", 14, 0)."?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
            <!--
                <tr>
                    <form method='post' action='<?php echo EQUIP_MENU2 ?>'>
                        <td align='center'>
                            <input type='image' name='post' alt='空のアイテム' border=0 src='<?php echo IMG ?>menu_item.gif'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
            -->
                
            <!--
                <tr>
                    <form method='post' action='/test/test_gantt_graph.php'>
                        <td align='center'>
                            <input type='image' name='post' alt='今後の開発予定表' border=0 src='<?php echo IMG ?>menu_item.gif'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
            -->
                
            </table>
        </td>
        </tr>
        </table>
    </center>
</body>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_alert_java() ?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
