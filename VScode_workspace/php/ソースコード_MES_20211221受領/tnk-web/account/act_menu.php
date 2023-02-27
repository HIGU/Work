<?php
//////////////////////////////////////////////////////////////////////////////
// 経理 処理 メニュー                                                       //
// Copyright (C) 2003-2018 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2003/11/17 Created   act_menu.php                                        //
//            暫定的に経理日報関係の処理を行うが恒久的には経理メニューへ    //
// 2003/11/29 経理 処理 メニュー(経理メニュmenu_sit.php)へ移行              //
// 2003/12/08 monthly_send(name)をjavaScriptで作成し<a href'**'>から送信    //
// 2003/12/12 defineされた定数でディレクトリとメニューを使用して管理する    //
//            ob_start('ob_gzhandler') を追加                               //
// 2004/02/13 index1.php→index.phpへ変更(index1はauthenticateに変更のため) //
// 2004/05/12 サイトメニュー表示・非表示 ボタン追加 menu_OnOff($script)追加 //
// 2004/06/10 view_user($_SESSION['User_ID']) をメニューヘッダーの下に追加  //
// 2004/12/25 style='overflow:hidden;' (-xy両方)を追加                      //
// 2005/01/18 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2005/05/09 発注計画データの自動更新に伴いメニューからの処理時にメッセージ//
// 2005/05/31 上記と同じ様にＡ伝情報も既に自動化されているためメッセージ追加//
// 2005/06/07 更新処理で一部しか確認ダイアログを出してないのを全てに出す    //
// 2005/08/06 客先支給品にjavaScript:monthly_send()が抜けているのを追加     //
// 2007/01/09 date_offset()がDB処理に変更になったため時間がかかる64→26へ   //
// 2007/09/07 phpのショートカットタグを標準タグ(推奨値)へ変更               //
// 2007/10/13 部門別製造経費・販管費サマリー照会を追加 E_ALL | E_STRICT へ  //
// 2007/10/22 setArrayYMD(),getArrayYMD()を作成し年月日の配列処理を高速化   //
//            date('Y/m/d', filemtime(YMD_FILE))を左下に追加。上記の確認用  //
// 2007/10/28 部門別製造経費に余分な<form>が入っていたので削除              //
// 2007/11/06 経費内訳グラフ作成メニューを追加。tableのwidth指定を変更      //
// 2009/02/24 2008/01/17が洩れていた為強制的に日付を表示した           大谷 //
// 2009/12/25 23日が営業日になった為強制的に23日を表示するように変更        //
//            変更を解除。営業日を変更する場合は始めに全社共有のカレンダーを//
//            メンテナンスしarrayYMDmenu.txtを一度削除してこのメニューを    //
//            開きなおせば再作成される。                               大谷 //
// 2010/05/19 販管費の照会追加により、メニュー整理                     大谷 //
// 2010/11/11 各セグメント別の棚卸増減比較を追加                       大谷 //
// 2013/01/29 メニュー量が増加した為サイドバーの非表示を解除           大谷 //
// 2014/12/02 2014/11/06が洩れていた為強制的に日付を表示した           大谷 //
// 2015/02/16 表示日付を60日に延長した                                 大谷 //
// 2015/05/21 機工生産に対応                                           大谷 //
// 2015/11/30 抜けがあったものを取得するため日付を強制変更             大谷 //
// 2017/10/24 連結取引総括表を追加                                     大谷 //
// 2017/11/10 特注品配賦棚卸高を追加                                   大谷 //
// 2018/05/08 ツール棚卸照会を全体に公開                               大谷 //
// 2018/10/16 強制日付変更                                             大谷 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 WEB CGI版
session_start();                            // ini_set()の次に指定すること Script 最上行
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮

require_once ('../function.php');           // define.php と pgsql.php を require_once している
require_once ('../tnk_func.php');           // TNK に依存する部分の関数を require_once している
require_once ('../MenuHeader.php');         // TNK 全共通 menu class
require_once ('../ControllerHTTP_Class.php');// TNK 全共通 MVC Controller Class
//////////// セッションのインスタンスを登録
// $session = new Session();
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(20, 999);                   // site_index=20(経理メニュー) site_id=999(サイトを開く)
////////////// リターンアドレス設定
// $menu->set_RetUrl(MENU);                 // 通常は指定する必要はない(トップメニュー)
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('経理 処理 メニュー');
//////////// 表題の設定
$menu->set_caption('経理 日報 処理 メニュー &nbsp;&nbsp;&nbsp;処理日');
//////////// 呼出先のaction名とアドレス設定
/**************** 日報処理 ****************/
$menu->set_action('買掛金の更新',       ACT . 'act_payable_get_ftp.php');
$menu->set_action('買掛金のチェック',   ACT . 'act_payable_view.php');
$menu->set_action('支給票の更新',       ACT . 'act_miprov_get_ftp.php');
$menu->set_action('支給表のチェック',   ACT . 'act_miprov_view.php');
$menu->set_action('発注計画の更新',     ACT . 'order_plan_get_ftp.php');
$menu->set_action('発注計画のチェック', ACT . 'order_plan_view.php');
$menu->set_action('Ａ伝情報の更新',     ACT . 'aden_master_update.php');
$menu->set_action('Ａ伝情報の照会',     ACT . 'aden_master_view.php');
/**************** 月次処理 ****************/
$menu->set_action('棚卸データの更新',           ACT . 'inventory/inventory_month_update.php');
$menu->set_action('棚卸データのチェック',       ACT . 'inventory/inventory_month_view.php');
$menu->set_action('客先支給品の更新',           ACT . 'provide_month_update.php');
$menu->set_action('客先支給品のチェック',       ACT . 'provide_month_view.php');
$menu->set_action('発注先マスターの更新',       ACT . 'vendor_master_update.php');
$menu->set_action('発注先マスターのチェック',   ACT . 'vendor_master_view.php');
$menu->set_action('担当者マスターの更新',       ACT . 'vendor_person_master_update.php');
$menu->set_action('担当者マスターのチェック',   ACT . 'vendor_person_master_view.php');
$menu->set_action('仕入金額の照会',             ACT . 'act_purchase_view.php');
$menu->set_action('仕入計上処理',               ACT . 'act_purchase_update.php');
$menu->set_action('カプラ棚卸金額',             ACT . 'inventory/inventory_month_c_view.php');
$menu->set_action('リニア棚卸金額',             ACT . 'inventory/inventory_month_l_view.php');
$menu->set_action('Ｃ特注棚卸金額',             ACT . 'inventory/inventory_monthly_ctoku_view.php');
// $menu->set_action('Ｃ特注棚卸前月',             ACT . 'inventory_month_ctoku_zen_view.php');
$menu->set_action('バイモル棚卸金額',           ACT . 'inventory/inventory_month_bimor_view.php');
$menu->set_action('ツール棚卸金額',           ACT . 'inventory/inventory_month_tool_view.php');
$menu->set_action('部門別棚卸金額計上処理',     ACT . 'inventory_monthly_header_update.php');
/**************** その他 照会メニュー ****************/
$menu->set_action('部門別製造経費',             ACT . 'act_summary/act_summary_Main.php');
$menu->set_action('経費内訳グラフ',             ACT . 'graphCreate/graphCreate_Form.php');
$menu->set_action('部門別販管費',               ACT . 'sga_summary/act_summary_Main.php');
$menu->set_action('総平均棚卸金額',             ACT . 'inventory/inventory_month_view_average.php');
$menu->set_action('カプラ総平均棚卸金額',       ACT . 'inventory/inventory_month_c_view_average.php');
$menu->set_action('リニア総平均棚卸金額',       ACT . 'inventory/inventory_month_l_view_average.php');
$menu->set_action('Ｃ特注総平均棚卸金額',       ACT . 'inventory/inventory_monthly_ctoku_view_average.php');
$menu->set_action('Ｃ特注総平均棚卸金額配賦',   ACT . 'inventory/inventory_monthly_ctoku_view_average_allo.php');
$menu->set_action('バイモル総平均棚卸金額',     ACT . 'inventory/inventory_month_bimor_view_average.php');
$menu->set_action('ツール総平均棚卸金額',     ACT . 'inventory/inventory_month_tool_view_average.php');
$menu->set_action('総平均棚卸金額比較',         ACT . 'inventory/inventory_month_compare.php');
$menu->set_action('カプラ総平均棚卸金額比較',         ACT . 'inventory/inventory_month_c_compare.php');
$menu->set_action('リニア総平均棚卸金額比較',         ACT . 'inventory/inventory_month_l_compare.php');
$menu->set_action('Ｃ特注総平均棚卸金額比較',         ACT . 'inventory/inventory_month_ctoku_compare.php');
$menu->set_action('バイモル総平均棚卸金額比較',         ACT . 'inventory/inventory_month_bimor_compare.php');
$menu->set_action('ツール総平均棚卸金額比較',         ACT . 'inventory/inventory_month_tool_compare.php');
$menu->set_action('連結取引総括表',             ACT . 'link_trans/link_trans_menu.php');

//////////// ブラウザーのキャッシュ対策用
$uniq = $menu->set_useNotCache('actMenu');  

//////////// 対象年月日のセッションデータ取得
if (isset($_SESSION['act_ymd'])) {
    $act_ymd = $_SESSION['act_ymd']; 
} else {
    $act_ymd = date('Ymd');        // セッションデータがない場合の初期値(当月)
}
//////////// 対象年月のセッションデータ取得
if (isset($_SESSION['act_ym'])) {
    $act_ym = $_SESSION['act_ym']; 
} else {
    $act_ym = date('Ym');        // セッションデータがない場合の初期値(当月)
}
//////////// 対象年月のセッションデータ取得
if (isset($_SESSION['actv_ym'])) {
    $actv_ym = $_SESSION['actv_ym']; 
} else {
    $actv_ym = date('Ym');        // セッションデータがない場合の初期値(当月)
}
/////////// 稼働日の年月日の配列生成
define('preDays', 60);  // 稼働日で25日分さかのぼる
define('YMD_FILE', 'arrayYMDmenu.txt');
if ( ($ymd=getArrayYMD(YMD_FILE)) === false) {
    $ymd[0] = 20210211;
    for ($i=1; $i<preDays; $i++) {
        $ymd_chk = date_offset($i);     // 営業日で$i日分前へ
        if ($ymd[$i-1] == $ymd_chk) {
            continue;                   // 前回と同じなら その前の日へ
        } else {
            $ymd[$i] = date_offset($i);
        }
    }
    setArrayYMD(YMD_FILE, $ymd);
}
////////// 稼働日の年月日の配列をファイルに保存
function setArrayYMD($file, $data)
{
    $data = serialize($data);
    $fp = fopen($file, 'w');
    fwrite($fp, $data);
    fclose($fp);
}
////////// 稼働日の年月日の配列をファイルから取得 ファイルの更新日もチェック
function getArrayYMD($file)
{
    if (!file_exists($file)) return false;
    if ( date('Ymd') != date('Ymd', filemtime($file)) ) return false;
    $fp = fopen($file, 'r');
    $data = fgets($fp);
    fclose($fp);
    return unserialize($data);
}
//$ymd[0]=20210211;
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
<?php echo $menu->out_site_java()?>
<?php echo $menu->out_css()?>

<script type='text/javascript'>
<!--
function monthly_send(script_name)
{
    document.monthly_form.action = 'act_branch.php?act_name=' + script_name;
    document.monthly_form.submit();
}
function ave_monthly_send(script_name)
{
    document.average_form.action = 'act_branch.php?act_name=' + script_name;
    document.average_form.submit();
}
// -->
</script>

<!-- スタイルシートのファイル指定をコメント HTMLタグ コメントは入れ子に出来ない事に注意 -->
<link rel='stylesheet' href='<?php echo "act_menu.css?{$uniq}" ?>' type='text/css' media='screen'>

<style type='text/css'>
<!--
select {
    background-color:   teal;
    color:              white;
}
a:hover {
    background-color:   gold;
    color:              black;
}
a {
    font-size:          0.9em;
    font-weight:        bold;
    color:              black;
}
-->
</style>
</head>
<body onLoad='document.mhForm.backwardStack.focus()'>
    <center>
<?php echo $menu->out_title_border()?>
        <!--
        <table width='100%'>
            <tr>
                <td align='center'><img src='<?php echo IMG ?>t_nitto_logo2.gif' width=348 height=83></td>
            </tr>
        </table>
        -->
        <BR>
        <form name='daily_form' action='act_branch.php' method='post'>
        <table width='516' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='5'>
            <tr><td> <!-- ダミー(デザイン用) -->
            <table width='100%' class='winbox_field' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td class='winbox' align='center' colspan='5' style='background-color:#ffffc6;'>
                    <div class='caption_font'>
                        <?php echo $menu->out_caption(), "\n"?>
                        <select name='act_ymd' class='pt11b'>
                            <?php
                            for ($i=1; $i<preDays; $i++) {
                                if ($act_ymd == $ymd[$i]) {
                                    printf("<option value='%d' selected>%s年%s月%s日</option>\n", $ymd[$i], substr($ymd[$i], 0, 4), substr($ymd[$i], 4, 2), substr($ymd[$i], 6, 2));
                                } else {
                                    printf("<option value='%d'>%s年%s月%s日</option>\n", $ymd[$i], substr($ymd[$i], 0, 4), substr($ymd[$i], 4, 2), substr($ymd[$i], 6, 2));
                                }
                            }
                            ?>
                        </select>
                    </div>
                    </td>
                </tr>
                <tr>
                    <td width='50%' class='winbox' align='center' bgcolor='#ceffce'> <!-- #ffffc6 薄い黄色 -->
                        <input class='pt10b' type='submit' name='act_name' value='買掛金の更新'
                            onClick="return confirm('買掛金の更新処理を実行します。\n\nこの処理は日報ベースの処理です。\n\nＡＳ/４００の日報は終了していますか？')"
                        >
                    </td>
                    <td width='50%' class='winbox' align='center' bgcolor='#ceffce'>
                        <input class='pt10b' type='submit' name='act_name' value='支給票の更新'
                            onClick="return confirm('支給票の更新処理を実行します。\n\nこの処理は日報ベースの処理です。\n\nＡＳ/４００の日報は終了していますか？')"
                        >
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <input class='pt10b' type='submit' name='act_name' value='買掛金のチェック'>
                        <!-- <a href='act_branch.php?act_name=act_payable_view' target='application' style='text-decoration:none;'>買掛金のチェックリスト</a> -->
                    </td> <!-- 余白 -->
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <input class='pt10b' type='submit' name='act_name' value='支給票のチェック'>
                        <!-- <a href='act_branch.php?act_name=act_miprov_view' target='application' style='text-decoration:none;'>支給票のチェックリスト</a> -->
                    </td> <!-- 余白 -->
                </tr>
                <tr>
                    <td class='winbox' align='center' bgcolor='#ceffce'> <!-- #ffffc6 薄い黄色 -->
                        <!-- <input class='pt10b' type='submit' name='act_name' value='発注計画の更新'> -->
                        <a href='act_branch.php?act_name=order_plan_update' target='application'
                            style='text-decoration:none;'
                            onClick="return confirm('発注計画データの更新処理を実行します。\n\nこの処理は現在自動化されています。\n\nそれでも実行しますか？')"
                        >
                            発注計画の更新
                        </a>
                    </td>
                    <td class='winbox' align='center' bgcolor='#ceffce'>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='Ａ伝情報の更新'> -->
                        <a href='act_branch.php?act_name=aden_master_update' target='application'
                            style='text-decoration:none;'
                            onClick="return confirm('Ａ伝情報データの更新処理を実行します。\n\nこの処理は現在自動化されています。\n\nそれでも実行しますか？')"
                        >
                            Ａ伝情報の更新
                        </a>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='発注計画のチェック'> -->
                        <a href='act_branch.php?act_name=order_plan_view' target='application' style='text-decoration:none;'>発注計画のチェックリスト</a>
                    </td> <!-- 余白 -->
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='Ａ伝情報のチェック'> -->
                        <a href='act_branch.php?act_name=aden_master_view' target='application' style='text-decoration:none;'>Ａ伝情報のチェックリスト</a>
                    </td> <!-- 余白 -->
                </tr>
            </table>
            </td></tr>
        </table> <!-- ダミーEnd -->
        </form>
        
        <br>
        
        <form name='monthly_form' action='act_branch.php' method='post'>
        <table width='516' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='5'>
            <tr><td> <!-- ダミー(デザイン用) -->
            <table width='100%' class='winbox_field' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td class='winbox' align='center' colspan='2' style='background-color:#ffffc6;'>
                    <div class='caption_font'>
                        月次ベース 処理 メニュー &nbsp;&nbsp;&nbsp;処理日
                        <select name='act_ym' class='pt11b'>
                            <?php
                            $ym = date("Ym");
                            while(1) {
                                if (substr($ym,4,2)!=01) {
                                    $ym--;
                                } else {
                                    $ym = $ym - 100;
                                    $ym = $ym + 11;
                                }
                                if ($act_ym == $ym) {
                                    printf("<option value='%d' selected>%s年%s月</option>\n",$ym,substr($ym,0,4),substr($ym,4,2));
                                    $init_flg = 0;
                                } else
                                    printf("<option value='%d'>%s年%s月</option>\n",$ym,substr($ym,0,4),substr($ym,4,2));
                                if ($ym <= 200410)
                                    break;
                            }
                            ?>
                        </select>
                    </div>
                    </td>
                </tr>
                <tr>
                    <td width='50%' class='winbox' align='center' bgcolor='#ceffce'>
                        <!-- <a href='act_branch.php?act_name=inventory_month_update' target='application' style='text-decoration:none;'>棚卸データの更新</a> -->
                        <input class='pt10b' type='submit' name='act_name' value='棚卸データの更新'>
                    </td>
                    <td width='50%' class='winbox' align='center' bgcolor='#ceffce'>
                        <!-- <a href='act_branch.php?act_name=provide_month_update' target='application' style='text-decoration:none;'>客先支給品の更新</a> -->
                        <input class='pt10b' type='submit' name='act_name' value='客先支給品の更新'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javaScript:monthly_send("inventory_month_view")' target='application' style='text-decoration:none;'>棚卸データのチェックリスト</a>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='棚卸データのチェック'> -->
                    </td>
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javaScript:monthly_send("provide_month_view")' target='application' style='text-decoration:none;'>客先支給品のチェックリスト</a>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='客先支給品のチェックリスト'> -->
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='center' bgcolor='#ceffce'>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='発注先マスター更新'> -->
                        <a href='act_branch.php?act_name=vendor_master_update' target='application'
                            style='text-decoration:none;'
                            onClick="return confirm('発注先マスターの更新処理を実行します。\n\nこの処理は月次ベースで行っています。\n\nＡＳ/４００からの転送は終了していますか？')"
                        >
                            発注先マスター更新
                        </a>
                    </td>
                    <td class='winbox' align='center' bgcolor='#ceffce'>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='仕入計上処理'> -->
                        <a href='javaScript:monthly_send("act_purchase_update")' target='application'
                            style='text-decoration:none;'
                            onClick="return confirm('仕入の計上処理を実行します。\n\nこの処理は月次ベースで必ず行います。\n\n実行しても宜しいでしょうか？')"
                        >
                            仕入 計上 処理
                        </a>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='発注先マスターチェック'> -->
                        <a href='act_branch.php?act_name=vendor_master_view' target='application' style='text-decoration:none;'>発注先マスターチェックリスト</a>
                    </td>
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javaScript:monthly_send("act_purchase_view")' target='application' style='text-decoration:none;'>仕入金額の照会</a>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='仕入金額の照会'> -->
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='center' bgcolor='#ceffce'>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='発注先マスター更新'> -->
                        <a href='act_branch.php?act_name=vendor_person_master_update' target='application'
                            style='text-decoration:none;'
                            onClick="return confirm('担当者マスターの更新処理を実行します。\n\nこの処理は月次ベースで行っています。\n\nＡＳ/４００からの転送は終了していますか？')"
                        >
                            担当者マスター更新
                        </a>
                    </td>
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='発注先マスターチェック'> -->
                        <a href='act_branch.php?act_name=vendor_person_master_view' target='application' style='text-decoration:none;'>担当者マスターチェックリスト</a>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javaScript:monthly_send("inventory_month_c_view")' target='application' style='text-decoration:none;'>カプラ棚卸金額</a>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='カプラ棚卸金額'> -->
                    </td>
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javaScript:monthly_send("inventory_month_l_view")' target='application' style='text-decoration:none;'>リニア棚卸金額</a>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='リニア棚卸金額'> -->
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javaScript:monthly_send("inventory_month_ctoku_view")' target='application' style='text-decoration:none;'>カプラ特注 棚卸金額 照会</a>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='カプラ特注棚卸金額の照会'> -->
                    </td>
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javaScript:monthly_send("inventory_month_tool_view")' target='application' style='text-decoration:none;'>ツール 棚卸金額 照会</a>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='ツール棚卸金額の照会'> -->
                    </td>
                    <!--
                    <td class='winbox' align='center' bgcolor='#d6d3ce'> 
                        <a href='javaScript:monthly_send("inventory_month_bimor_view")' target='application' style='text-decoration:none;'>液体ポンプ 棚卸金額 照会</a> -->
                        <!-- <input class='pt10b' type='submit' name='act_name' value='液体ポンプ棚卸金額の照会'> -->
                    <!--
                    </td>
                    -->
                </tr>
                <tr>
                    <td class='winbox' colspan='2' align='center' bgcolor='#ceffce'>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='部門別 棚卸金額 計上処理'> -->
                        <a href='javaScript:monthly_send("inventory_monthly_header_update")'
                            target='application' style='text-decoration:none;'
                            onClick="return confirm('部門別 棚卸金額の計上処理を実行します。\n\nこの処理は棚卸データの更新で自動的に行われます。\n\nそれでも単独で実行しますか？')"
                        >
                            部門別 棚卸金額 計上処理
                        </a>
                    </td>
                </tr>
            </table>
            </td></tr>
        </table> <!-- ダミーEnd -->
        </form>
        
        <br>
        
        <form name='average_form' action='act_branch.php' method='post'>
        <table width='516' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='5'>
            <tr><td> <!-- ダミー(デザイン用) -->
            <table width='100%' class='winbox_field' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td class='winbox' align='center' colspan='2' style='background-color:#ffffc6;'>
                    <div class='caption_font'>
                        その他 照会 メニュー &nbsp;&nbsp;&nbsp;処理日
                        <select name='actv_ym' class='pt11b'>
                            <?php
                            $ym = date("Ym");
                            while(1) {
                                if (substr($ym,4,2)!=01) {
                                    $ym--;
                                } else {
                                    $ym = $ym - 100;
                                    $ym = $ym + 11;
                                }
                                if ($actv_ym == $ym) {
                                    printf("<option value='%d' selected>%s年%s月</option>\n",$ym,substr($ym,0,4),substr($ym,4,2));
                                    $init_flg = 0;
                                } else
                                    printf("<option value='%d'>%s年%s月</option>\n",$ym,substr($ym,0,4),substr($ym,4,2));
                                if ($ym <= 200410)
                                    break;
                            }
                            ?>
                        </select>
                    </div>
                    </td>
                </tr>
                <tr>
                    <td width='50%' class='winbox' align='center' bgcolor='#d6d3ce'>
                        <?php
                        if (getCheckAuthority(35)) {
                        ?>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('部門別製造経費')?>"); return false;'
                            onMouseover="status='部門別 製造経費・販管費の照会メニューを表示します。';return true;"
                            onMouseout="status=''"
                            title='部門別 製造経費・販管費の照会メニューを表示します。'
                        >
                            部門別 製造・販管費の照会
                        <?php
                        } else {
                        ?>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('部門別製造経費')?>"); return false;'
                            onMouseover="status='部門別 製造経費の照会メニューを表示します。';return true;"
                            onMouseout="status=''"
                            title='部門別 製造経費の照会メニューを表示します。'
                        >
                            部門別 製造経費の照会
                        </a>
                        <?php 
                        }
                        ?>
                    </td>
                    <td width='50%' class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('経費内訳グラフ')?>"); return false;'
                            onMouseover="status='経費内訳の分析用グラフ作成メニューを表示します。';return true;"
                            onMouseout="status=''"
                            title='経費内訳の分析用グラフ作成メニューを表示します。'
                        >
                            経費内訳グラフ作成メニュー
                        </a>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javaScript:ave_monthly_send("inventory_month_view_average")' target='application' style='text-decoration:none;'>総平均棚卸金額</a>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='総平均棚卸金額'> -->
                    </td>
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javaScript:ave_monthly_send("link_trans")' target='application' style='text-decoration:none;'>連結取引総括表</a>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='連結取引総括表'> -->
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javaScript:ave_monthly_send("inventory_month_c_view_average")' target='application' style='text-decoration:none;'>カプラ総平均棚卸金額</a>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='カプラ総平均棚卸金額'> -->
                    </td>
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javaScript:ave_monthly_send("inventory_month_l_view_average")' target='application' style='text-decoration:none;'>リニア総平均棚卸金額</a>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='リニア総平均棚卸金額'> -->
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javaScript:ave_monthly_send("inventory_month_ctoku_view_average")' target='application' style='text-decoration:none;'>Ｃ特注総平均棚卸金額</a>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='Ｃ特注総平均棚卸金額'> -->
                    </td>
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javaScript:ave_monthly_send("inventory_month_tool_view_average")' target='application' style='text-decoration:none;'>ツール総平均棚卸金額</a>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='ツール総平均棚卸金額'> -->
                    </td>
                    <!--
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javaScript:ave_monthly_send("inventory_month_bimor_view_average")' target='application' style='text-decoration:none;'>液体ポンプ総平均棚卸金額</a> -->
                        <!-- <input class='pt10b' type='submit' name='act_name' value='液体ポンプ総平均棚卸金額'> -->
                    <!--
                    </td>
                    -->
                </tr>
                <tr>
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javaScript:ave_monthly_send("inventory_month_ctoku_view_average_allo")' target='application' style='text-decoration:none;'>Ｃ特注総平均棚卸金額配賦</a>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='Ｃ特注総平均棚卸金額配賦'> -->
                    </td>
                    <td width='50%' class='winbox' align='center' bgcolor='#d6d3ce'>
                        　
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javaScript:ave_monthly_send("inventory_month_compare")' target='application' style='text-decoration:none;'>総平均棚卸金額比較</a>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='総平均棚卸金額比較'> -->
                    </td>
                    <td width='50%' class='winbox' align='center' bgcolor='#d6d3ce'>
                        　
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javaScript:ave_monthly_send("inventory_month_c_compare")' target='application' style='text-decoration:none;'>カプラ総平均棚卸金額比較</a>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='カプラ総平均棚卸金額比較'> -->
                    </td>
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javaScript:ave_monthly_send("inventory_month_l_compare")' target='application' style='text-decoration:none;'>リニア総平均棚卸金額比較</a>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='リニア総平均棚卸金額比較'> -->
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javaScript:ave_monthly_send("inventory_month_ctoku_compare")' target='application' style='text-decoration:none;'>Ｃ特注総平均棚卸金額比較</a>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='Ｃ特注総平均棚卸金額比較'> -->
                    </td>
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javaScript:ave_monthly_send("inventory_month_tool_compare")' target='application' style='text-decoration:none;'>ツール総平均棚卸金額比較</a>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='ツール総平均棚卸金額比較'> -->
                    </td>
                    <!--
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javaScript:ave_monthly_send("inventory_month_bimor_compare")' target='application' style='text-decoration:none;'>液体ポンプ総平均棚卸金額比較</a> -->
                        <!-- <input class='pt10b' type='submit' name='act_name' value='液体ポンプ総平均棚卸金額比較'> -->
                    <!--
                    </td>
                    -->
                </tr>
<!--
                <tr>
                    <td width='50%' class='winbox' align='center' bgcolor='#ceffce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('部門別販管費')?>"); return false;'
                            onMouseover="status='部門別 製造経費・販管費の照会メニューを表示します。';return true;"
                            onMouseout="status=''"
                            title='部門別 製造経費・販管費の照会メニューを表示します。'
                        >
                            部門別 販管費の照会
                        </a>
                    </td>
                    <td width='50%' class='winbox' align='center' bgcolor='#ceffce'>
                        　
                    </td>
                </tr>
-->
            </table>
            </td></tr>
        </table> <!-- ダミーEnd -->
        </form>
        <div class='ymd'><?php echo date('Y/m/d', filemtime(YMD_FILE)) ?></div>
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
