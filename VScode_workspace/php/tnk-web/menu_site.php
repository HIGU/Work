<?php
//////////////////////////////////////////////////////////////////////////////
// 栃木日東工器 サイト メニュー (フレームの左側に表示を想定)                //
// Copyright (C) 2002-2021 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2002/08/01 Created  menu_site.php                                        //
// 2002/08/26 セッション管理を追加 & register_globals = Off 対応            //
// 2002/09/21 メニューアイコンを 32X32 → 22X22 へ変更 文字サイズ11ptへ     //
//              文字数を短縮 設備稼働管理の長さが最適 800X600の画面で       //
// 2002/09/24 ネスケ対策のため<body> に link='white' vling='white' 追加     //
// 2002/12/27 ブラウザーのキャッシュ対策のため uniqid() を追加              //
//            <a href='/system/phpinfo.php?". uniqid("menu") ."'            //
// 2003/01/23 <a href='*****.php?". uniqid("menu") ."'  →  ?$uniq へ       //
// 2003/02/27 文字サイズをブラウザーで変更できなくした[システムメッセージ]  //
// 2003/05/13 設備稼働管理に 加工 実績 照会 を追加                          //
// 2003/05/15 大分類配賦率保守を大分類 項目保守へ変更 旧部門コード保守削除  //
// 2003/06/30 開発用テンプレートファイルの表示をメニューに追加              //
// 2003/08/25 style sheet に a:hover { background-color:blue; } 追加        //
// 2003/10/17 月次・中間・決算の中にサービス割合処理メニューを追加          //
// 2003/10/31 売上メニューにＣ特注標準グラフを追加 site_index=1 site_id=9   //
// 2003/11/15 font style 11pt → 9pt へ <a> の部分を小さくした。            //
// 2003/11/18 font style sysmsg_body を 8.7pt → 7.2pt へ 小さくした。      //
// 2003/11/29 <img width='22' height='22'>のサイズ指定削除'16'へ変更と高速化//
// 2003/12/01 site_indexが一致した場合に<td bgcolor='blue'>img</td>を追加   //
// 2003/12/02 site_icon1_on.gif?1→site_icon1_on.gif?v=2 へ変更(最新読込)   //
//            (off.gif) site_icon1_on.gif?$uniq にすると IE6で不具合がでた。//
// 2003/12/11 システムメッセージのfontを 8.7pt → 7.9pt へ変更              //
// 2003/12/12 defineされた定数でディレクトリとメニューを使用して管理する    //
//            ob_start('ob_gzhandler') を追加   終了(ログアウト)→(logout)  //
// 2004/02/13 ?$uniq → ?id=$uniq へ変更 社員メニューに社員名簿印刷を追加   //
//            ". TOP_MENU ." の連結を ", TOP_MENU, "へ echoの()を外す必要有 //
// 2004/04/28 ?id=$uniq → ?$uniq ($uniq='id=menu42498dds')変数に埋め込んだ //
//            定義済みのSIDを使用してクッキー許可の条件判断ロジックを追加   //
// 2004/07/09 旧の設備メニューを権限レベルで隠す 新機械運転日報を追加       //
// 2005/01/13 MenuHeaderで共通キー割当てに伴いparent.application.focus()追加//
// 2005/01/14 上記を取消 初期入力位置にset_focus()している場合に不具合が出る//
// 2005/01/18 リテラルから SITE_ICON_ON  SITE_ICON_OFF にdefine定義へ切替   //
// 2005/02/25 CSS関係a.currentを追加し<a>を擬似クラスで統一active red->gold //
// 2005/09/02 target='application→<a href='logout.php' target='_parent'>へ //
// 2005/09/13 規程メニューを追加  ＆  session_register('s_sysmsg')を撤去    //
// 2006/06/23 会社の基本カレンダーメンテナンスをシステムメニューに追加      //
// 2006/07/15 上記のカレンダーはTOPへ変更リンクの見残す。共通権限編集を追加 //
// 2006/08/30 掲示板 ４種類をトップメニューに追加 $uniqのIDは付加してはNG   //
// 2006/09/29 売上照会メニューを sales/ → sales/details/ へ移動            //
// 2007/02/20 parts_stock_plan_Main.php → parts_stock_plan_form.php へ変更 //
// 2007/03/07 移動履歴を異動履歴に訂正大谷 部品在庫経歴ディレクトリ変更 小林//
// 2007/03/24 sales_miken allo_conf_parts のディレクトリ(プログラム)変更    //
// 2007/03/27 設備メニューをINDEX_EQUIP へ変更  旧設備メニューコメントを削除//
// 2007/03/29 全工場モードでは機械運転日報は表示しない                      //
// 2007/05/23 売上メニューに生産メニューにある売上未検収照会を追加          //
// 2007/10/05 組立賃率のフォルダ構成変更ooyaを削除                     大谷 //
// 2007/10/07 損益関係のグラフ作成メニューを損益・売上メニューに追加   小林 //
// 2007/10/09 site_indexがdefine化されていない部分を修正               小林 //
// 2007/10/28 部門 別製造経費の照会を経理メニューに追加                小林 //
// 2008/08/29 INDEX_QUALITY 品質メニュー 追加                          大谷 //
// 2008/09/25 社員メニューに就業週報追加                               大谷 //
// 2010/03/11 部課長スケジュールを追加                                      //
//            予測原価率分析を追加(テスト300144のみ)                   大谷 //
// 2010/05/19 販管費の照会追加により、メニュー整理                     大谷 //
// 2010/06/21 内線表をtestからtelフォルダへ移動                        大谷 //
// 2010/10/05 INDEX_ASSET 資材管理メニュー 追加                        大谷 //
// 2011/11/21 売上予定照会(18)を売上メニューに追加                     大谷 //
// 2011/11/22 生産メニューに納期遅れ部品の照会を追加                   大谷 //
// 2013/01/28 バイモルを液体ポンプへ変更 表示のみデータはバイモルのまま 大谷//
// 2018/04/20 前期分のみの教育・異動経歴を追加                         大谷 //
// 2018/08/29 生産メニューと売上メニューの未検収照会を分離             大谷 //
// 2021/06/22 設備メニューを組立と加工設備でメニュー分けfactory=6      大谷 //
//            今後は組立のみ別にする。ただASの切替で加工設備は              //
//            製造Noがキーになる可能性あり（指示Noがなくなる）         大谷 //
// 2021/07/07 品質メニューを品質・環境メニューへ変更                   和氣 //
//            品質・環境メニューへ[部署別コピー用紙使用量]を追加            //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

// require_once ('function.php');
require_once ('define.php');                // 全共通 define定義
require_once ('./function.php');

////////////// システムメッセージの初期化
if ( !(isset($_SESSION['s_sysmsg'])) ) {
    $_SESSION['s_sysmsg'] = '';             // 初回の場合は登録
}
$sysmsg = $_SESSION['s_sysmsg'];
$_SESSION['s_sysmsg'] = '';                 // NULL → '' へ変更 2003/11/17

if (isset($_REQUEST['factory'])) {
    $_SESSION['factory'] = $_REQUEST['factory'];
    $factory = $_SESSION['factory'];
} elseif(isset($_SESSION['factory'])) {
    $factory = $_SESSION['factory'];
} else {
    $factory = '';
    $_SESSION['factory'] = $factory;
}
//////////////// 各アンカーに変数でセットする 関数コールのオーバーヘッドを１回で済ませるため
if (SID == '') {
    $uniq = 'id=' . uniqid('menu'); // クッキーが許可されている場合はユニークなIDを生成
} else {
    $uniq = strip_tags(SID);        // クッキーが許可されていない場合はセッションIDを登録
}                                   // XSSに関する攻撃を防止するため strip_tags()を使用

$uid   = $_SESSION['User_ID'];
$query = "SELECT sid FROM user_detailes WHERE uid='$uid'";
$res   = array();
if( getResult($query,$res) <= 0 ) {
    $sid   = "";
} else {
    $sid   = $res[0][0];
}

header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');               // 日付が過去
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');  // 常に修正されている
header('Cache-Control: no-store, no-cache, must-revalidate');   // HTTP/1.1
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');                                     // HTTP/1.0
?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
<meta http-equiv='Content-Style-Type' content='text/css'>
<meta http-equiv='Content-Script-type' content='text/javascript'>
<title>TNK Site Menu</title>
<style type='text/css'>
<!--
body {
    margin:             0%;
}
form {
    margin:             0%;
}
table {
    margin:             0%;
}
.yellow {
    color:              yellow;
    text-decoreation:   none;
}
.none_ {
    text-decoreation:   none;
}
.sysmsg_title {
    font-size:          7.9pt;
    color:              white;
}
.sysmsg_body {
    font-size:          7.2pt;
    color:              #ff0000;
}
a {
    font-size:          12; /* 9pt = 12px */
    color:              white;
}
a.current {
    color:              yellow;
}
a:hover {
    background-color:   blue;
}
a:active {
    background-color:   gold;
    color:              black;
}
-->
</style>
</head>

<body bgcolor='#000000' text='#ffffff' background='<?php echo IMG?>wallpaper_b1.gif' link='white' vlink='white'>
<div id='Layer1'><img alt='TNK Site Menu' width='100%' border='0' src='<?php echo IMG?>silver_line2.gif'></div>
<table border='0'>
    <?php
////////////////////////////////////////////////// index=0 トップメニュー
    if ($_SESSION['site_index'] == INDEX_TOP) {
        echo "<tr>\n";
        echo "<td bgcolor='blue'><a href='", TOP_MENU, "?$uniq' target='application' onMouseover=\"status='栃木日東工器 全体のメニューを表示します。';return true;\" onMouseout=\"status=''\"><img alt='Top Menu' border='0' src='", SITE_ICON_ON, "'></a></td>\n";
        echo "<td nowrap><a href='", TOP_MENU, "?$uniq' target='application' style='text-decoration:none;' class='current' onMouseover=\"status='栃木日東工器 全体のメニューを表示します。';return true;\" onMouseout=\"status=''\" title='栃木日東工器 全体のメニューを表示します。'>トップメニュー</a></td>\n";
        echo "</tr>\n";
        if ($sid != '95') {
        if ($_SESSION['site_id'] > 0) {
            if ($_SESSION['site_id'] == 1) {  // テスト的にTNK内線表の表示を行う
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='tel/tnk_tel.php?$uniq' target='application' style='text-decoration:none;' class='current'>ＴＮＫ内線表</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='tel/tnk_tel.php?$uniq' target='application' style='text-decoration:none;'>ＴＮＫ内線表</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 2) {  // 全社共有 会議(打合せ)スケジュール表
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='meeting/meeting_schedule_Main.php?$uniq' target='_blank' style='text-decoration:none;' class='current'>会議一覧</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='meeting/meeting_schedule_Main.php?$uniq' target='_blank' style='text-decoration:none;'>会議一覧</a></td>\n";
                echo "</tr>\n";
            }
            if (getCheckAuthority(34)) {
            //if ($_SESSION['User_ID'] == '300144') {
                if ($_SESSION['site_id'] == 8) {  // 部課長用 会議(打合せ)スケジュール表
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='meeting/meeting_manager/meeting_schedule_manager_Main.php?$uniq' target='_blank' style='text-decoration:none;' class='current'>部課長スケジュール</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='meeting/meeting_manager/meeting_schedule_manager_Main.php?$uniq' target='_blank' style='text-decoration:none;'>部課長スケジュール</a></td>\n";
                    echo "</tr>\n";
                }
            }
            if ($_SESSION['site_id'] == 3) {  // 会社の基本カレンダー照会・編集(権限が必要)メニュー
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SYS, "calendar/companyCalendar_Main.php?$uniq' target='application' style='text-decoration:none;' class='current'>会社カレンダー</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SYS, "calendar/companyCalendar_Main.php?$uniq' target='application' style='text-decoration:none;'>会社カレンダー</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 4) {  // 総務課 掲示板
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='bbs/soumu/bbs.php' target='_blank' style='text-decoration:none;' class='current'>総務課 掲示板</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='bbs/soumu/bbs.php' target='_blank' style='text-decoration:none;'>総務課 掲示板</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 5) {  // カプラ特注課 掲示板
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='bbs/toku/bbs.php' target='_blank' style='text-decoration:none;' class='current'>特注課 掲示板</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='bbs/toku/bbs.php' target='_blank' style='text-decoration:none;'>特注課 掲示板</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 6) {  // 生産管理課 掲示板
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='bbs/seikan/bbs.php' target='_blank' style='text-decoration:none;' class='current'>生管課 掲示板</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='bbs/seikan/bbs.php' target='_blank' style='text-decoration:none;'>生管課 掲示板</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 7) {  // 技術課(加工技術) 掲示板
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='bbs/gijyutu/bbs.php' target='_blank' style='text-decoration:none;' class='current'>技術課 掲示板</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='bbs/gijyutu/bbs.php' target='_blank' style='text-decoration:none;'>技術課 掲示板</a></td>\n";
                echo "</tr>\n";
            }
        }
        }
    } else {
        echo "<tr>\n";
        echo "<td><a href='", TOP_MENU, "?$uniq' target='application' onMouseover=\"status='栃木日東工器 全体のメニューを表示します。';return true;\" onMouseout=\"status=''\"><img alt='Top Menu' border='0' src='", SITE_ICON_OFF, "'></a></td>\n";
        echo "<td nowrap><a href='", TOP_MENU, "?$uniq' target='application' style='text-decoration:none;' onMouseover=\"status='栃木日東工器 全体のメニューを表示します。';return true;\" onMouseout=\"status=''\" title='栃木日東工器 全体のメニューを表示します。'>トップメニュー</a></td>\n";
        echo "</tr>\n";
    }
    
////////////////////////////////////////////////// index=30 生産メニュー
    if ($_SESSION['site_index'] == INDEX_INDUST) {
        echo "<tr>\n";
        echo "<td bgcolor='blue'><a href='", INDUST_MENU, "?$uniq' target='application' onMouseover=\"status='生産 関係 処理メニューを表示します。';return true;\" onMouseout=\"status=''\"><img alt='生産 関係 処理メニュー' border='0' src='", SITE_ICON_ON, "'></a></td>\n";
        echo "<td nowrap><a href='", INDUST_MENU, "?$uniq' target='application' style='text-decoration:none;' class='current' onMouseover=\"status='生産 関係 処理メニューを表示します。';return true;\" onMouseout=\"status=''\" title='生産 関係 処理メニューを表示します。'>生産メニュー</a></td>\n";
        echo "</tr>\n";
        if ($sid != '95') {
        // 17=資材部品出庫集計, 18=リニア資材出庫メニュー, 27=指定検収日で指定保管場所の一覧照
        // 28=組立ラインのカレンダー, 29=受入検査集計
        if ($_SESSION['site_id'] > 0) {
            if ($_SESSION['site_id'] == 16) {  // 部品在庫予定照会
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "parts/parts_stock_plan/parts_stock_plan_form.php?$uniq' target='application' style='text-decoration:none;' class='current'>部品在庫予定</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "parts/parts_stock_plan/parts_stock_plan_form.php?$uniq' target='application' style='text-decoration:none;'>部品在庫予定</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 40) {  // 部品在庫経歴照会
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "parts/parts_stock_history/parts_stock_form.php?$uniq' target='application' style='text-decoration:none;' class='current'>部品在庫経歴</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "parts/parts_stock_history/parts_stock_form.php?$uniq' target='application' style='text-decoration:none;'>部品在庫経歴</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 50) {  // 納入予定と検査仕掛及び検査依頼リスト
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "order/order_schedule.php?$uniq' target='application' style='text-decoration:none;' class='current'>納入・検査仕掛</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "order/order_schedule.php?$uniq' target='application' style='text-decoration:none;'>納入・検査仕掛</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 51) {  // 協力工場別注残リスト
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "vendor/vendor_order_list_form.php?$uniq' target='application' style='text-decoration:none;' class='current'>注残リスト</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "vendor/vendor_order_list_form.php?$uniq' target='application' style='text-decoration:none;'>注残リスト</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 25) {  // 仕切単価と総材料費の比較表 照会
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "material/sales_material_comp_form.php?$uniq' target='application' style='text-decoration:none;' class='current'>仕切と総材料</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "material/sales_material_comp_form.php?$uniq' target='application' style='text-decoration:none;'>仕切と総材料</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 23) {  // 総材料費の照会(ASSY番号)
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "material/materialCost_view_assy.php?$uniq' target='application' style='text-decoration:none;' class='current'>総材料費ASSY</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "material/materialCost_view_assy.php?$uniq' target='application' style='text-decoration:none;'>総材料費ASSY</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 20) {  // 総材料費の照会(計画番号)
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "material/materialCost_view_plan.php?$uniq' target='application' style='text-decoration:none;' class='current'>総材料費 計画</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "material/materialCost_view_plan.php?$uniq' target='application' style='text-decoration:none;'>総材料費 計画</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 24) {  // 総材料 未登録 照会
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "ind_branch.php?ind_name=materialCost_unregist_view&$uniq' target='application' style='text-decoration:none;' class='current'>総材料未登録</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "ind_branch.php?ind_name=materialCost_unregist_view&$uniq' target='application' style='text-decoration:none;'>総材料未登録</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 19) {  // 総材料費と売上高の比率表
                echo "<tr>\n";
                // echo "<td></td>\n<td nowrap><a href='", INDUST, "ind_branch.php?ind_name=materialCost_sales_comp&$uniq' target='application' style='text-decoration:none;' class='current'>総材料費と売上</a></td>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "material/materialCost_sales_comp.php?$uniq' target='application' style='text-decoration:none;' class='current'>総材料費と売上</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                // echo "<td></td>\n<td nowrap><a href='", INDUST, "ind_branch.php?ind_name=materialCost_sales_comp&$uniq' target='application' style='text-decoration:none;'>総材料費と売上</a></td>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "material/materialCost_sales_comp.php?$uniq' target='application' style='text-decoration:none;'>総材料費と売上</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 21) {  // 総材料費の登録
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "material/materialCost_entry_plan.php?$uniq' target='application' style='text-decoration:none;' class='current'>総材料費登録</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "material/materialCost_entry_plan.php?$uniq' target='application' style='text-decoration:none;'>総材料費登録</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 30) {  // 製品売上 未検収 明細 照会
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "sales_miken/sales_miken_Main.php?$uniq' target='application' style='text-decoration:none;' class='current'>売上未検収</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "sales_miken/sales_miken_Main.php?$uniq' target='application' style='text-decoration:none;'>売上未検収</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 13) {  // Ａ伝情報の照会
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "ind_branch.php?ind_name=aden_master_view&$uniq' target='application' style='text-decoration:none;' class='current'>Ａ伝情報照会</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "ind_branch.php?ind_name=aden_master_view&$uniq' target='application' style='text-decoration:none;'>Ａ伝情報照会</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 10) {  // 買掛実績の照会
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "payable/act_payable_form.php?$uniq' target='application' style='text-decoration:none;' class='current'>買掛実績照会</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "payable/act_payable_form.php?$uniq' target='application' style='text-decoration:none;'>買掛実績照会</a></td>\n";
                echo "</tr>\n";
            }
            /*********************
            if ($_SESSION['site_id'] == 11) {  // 支給票の照会
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "ind_branch.php?ind_name=act_miprov_view&$uniq' target='application' style='text-decoration:none;' class='current'>支給実績照会</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "ind_branch.php?ind_name=act_miprov_view&$uniq' target='application' style='text-decoration:none;'>支給実績照会</a></td>\n";
                echo "</tr>\n";
            }
            *********************/
            if ($_SESSION['site_id'] == 14) {  // 単価経歴の照会
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "parts/parts_cost_form.php?$uniq' target='application' style='text-decoration:none;' class='current'>単価経歴照会</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "parts/parts_cost_form.php?$uniq' target='application' style='text-decoration:none;'>単価経歴照会</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 26) {  // 引当部品構成表の照会
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "parts/allocate_config/allo_conf_parts_form.php?$uniq' target='application' style='text-decoration:none;' class='current' onMouseover=\"status='組立計画の引当部品の構成表を照会します。';return true;\" onMouseout=\"status=''\">引当部品照会</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "parts/allocate_config/allo_conf_parts_form.php?$uniq' target='application' style='text-decoration:none;' onMouseover=\"status='組立計画の引当部品の構成表を照会します。';return true;\" onMouseout=\"status=''\">引当部品照会</a></td>\n";
                echo "</tr>\n";
            }
            /*****
            if ($_SESSION['site_id'] == 12) {  // 発注計画ファイルの照会
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "order/order_plan_view.php?$uniq' target='application' style='text-decoration:none;' class='current' onMouseover=\"status='発注計画データの照会をします。';return true;\" onMouseout=\"status=''\">発注計画照会</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "order/order_plan_view.php?$uniq' target='application' style='text-decoration:none;' onMouseover=\"status='発注計画データの照会をします。';return true;\" onMouseout=\"status=''\">発注計画照会</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 22) {  // 発注先マスターの照会
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "ind_branch.php?ind_name=vendor_master_view&$uniq' target='application' style='text-decoration:none;' class='current' onMouseover=\"status='発注先マスターの照会・編集を行います。';return true;\" onMouseout=\"status=''\">発注先の照会</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "ind_branch.php?ind_name=vendor_master_view&$uniq' target='application' style='text-decoration:none; onMouseover=\"status='発注先マスターの照会・編集を行います。';return true;\" onMouseout=\"status=''\"'>発注先の照会</a></td>\n";
                echo "</tr>\n";
            }
            *****/
            if ($_SESSION['site_id'] == 1) {    // 部品・製品のアイテムマスターを照会・編集
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "master/parts_item/parts_item_Main.php?$uniq' target='application' style='text-decoration:none;' class='current' onMouseover=\"status='部品・製品のアイテムマスターを照会・編集を行います。';return true;\" onMouseout=\"status=''\">アイテムマスター</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "master/parts_item/parts_item_Main.php?$uniq' target='application' style='text-decoration:none;' onMouseover=\"status='部品・製品のアイテムマスターを照会・編集します。';return true;\" onMouseout=\"status=''\">アイテムマスター</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 2) {    // 資材部品出庫メニュー
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "parts_control/parts_pickup_time_Main.php?$uniq' target='application' style='text-decoration:none;' class='current' onMouseover=\"status='資材の部品出庫時間集計用に着手・完了時間を入力します。';return true;\" onMouseout=\"status=''\">部品出庫メニュー</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "parts_control/parts_pickup_time_Main.php?$uniq' target='application' style='text-decoration:none;' onMouseover=\"status='資材の部品出庫時間集計用に着手・完了時間を入力します。';return true;\" onMouseout=\"status=''\">部品出庫メニュー</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 3) {    // 組立指示メニュー(工数集計等)
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "assembly/assembly_process/assembly_process_time_Main.php?$uniq' target='application' style='text-decoration:none;' class='current' onMouseover=\"status='組立工程の管理及び工数集計用に着手・完了時間を入力します。';return true;\" onMouseout=\"status=''\">組立指示メニュー</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "assembly/assembly_process/assembly_process_time_Main.php?$uniq' target='application' style='text-decoration:none;' onMouseover=\"status='組立工程の管理及び工数集計用に着手・完了時間を入力します。';return true;\" onMouseout=\"status=''\">組立指示メニュー</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 4) {    // 組立実績編集(工数集計等)
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "assembly/assembly_time_edit/assembly_time_edit_Main.php?$uniq' target='application' style='text-decoration:none;' class='current' onMouseover=\"status='組立工程の管理及び工数集計用に着手・完了時間を入力します。';return true;\" onMouseout=\"status=''\">組立実績編集</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "assembly/assembly_time_edit/assembly_time_edit_Main.php?$uniq' target='application' style='text-decoration:none;' onMouseover=\"status='組立工程の管理及び工数集計用に着手・完了時間を入力します。';return true;\" onMouseout=\"status=''\">組立実績編集</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 5) {    // データサム バーコード作成
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "ind_branch.php?ind_name=datasum_barcode&$uniq' target='application' style='text-decoration:none;' class='current' onMouseover=\"status='データサムのバーコードカードを作成・印刷します。';return true;\" onMouseout=\"status=''\">バーコード作成</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "ind_branch.php?ind_name=datasum_barcode&$uniq' target='application' style='text-decoration:none;' onMouseover=\"status='データサムのバーコードカードを作成・印刷します。';return true;\" onMouseout=\"status=''\">バーコード作成</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 6) {    // 組立状況照会
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "assembly/assembly_process_show/assembly_process_show_Main.php?$uniq' target='application' style='text-decoration:none;' class='current' onMouseover=\"status='組立工程の着手・完了状況を照会します。';return true;\" onMouseout=\"status=''\">組立状況照会</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "assembly/assembly_process_show/assembly_process_show_Main.php?$uniq' target='application' style='text-decoration:none;' onMouseover=\"status='組立工程の着手・完了状況を照会します。';return true;\" onMouseout=\"status=''\">組立状況照会</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 7) {    // 組立日程照会
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "scheduler/schedule_show/assembly_schedule_show_Main.php?$uniq' target='application' style='text-decoration:none;' class='current' onMouseover=\"status='各ライン毎の組立日程計画の照会をします。';return true;\" onMouseout=\"status=''\">組立日程照会</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "scheduler/schedule_show/assembly_schedule_show_Main.php?$uniq' target='application' style='text-decoration:none;' onMouseover=\"status='各ライン毎の組立日程計画の照会をします。';return true;\" onMouseout=\"status=''\">組立日程照会</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 8) {    // 実績工数照会(登録工数と比較)
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "assembly/assembly_time_show/assembly_time_show_Main.php?$uniq' target='application' style='text-decoration:none;' class='current' onMouseover=\"status='各ライン毎の組立日程計画の照会をします。';return true;\" onMouseout=\"status=''\">実績工数照会</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "assembly/assembly_time_show/assembly_time_show_Main.php?$uniq' target='application' style='text-decoration:none;' onMouseover=\"status='各ライン毎の組立日程計画の照会をします。';return true;\" onMouseout=\"status=''\">実績工数照会</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 9) {    // 組立完成一覧より実績工数と登録工数 比較
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "assembly/assembly_time_compare/assembly_time_compare_Main.php?$uniq' target='application' style='text-decoration:none;' class='current' onMouseover=\"status='組立完成一覧より実績工数と登録工数 比較をします。';return true;\" onMouseout=\"status=''\">完成一覧工数</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "assembly/assembly_time_compare/assembly_time_compare_Main.php?$uniq' target='application' style='text-decoration:none;' onMouseover=\"status='組立完成一覧より実績工数と登録工数 比較をします。';return true;\" onMouseout=\"status=''\">完成一覧工数</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 52) {    // 納期遅れ部品の照会
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "order/delivery_late/delivery_late_form.php?$uniq' target='application' style='text-decoration:none;' class='current' onMouseover=\"status='納期遅れ部品の照会をします。';return true;\" onMouseout=\"status=''\">納期遅れ照会</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "order/delivery_late/delivery_late_form.php?$uniq' target='application' style='text-decoration:none;' onMouseover=\"status='納期遅れ部品の照会をします。';return true;\" onMouseout=\"status=''\">納期遅れ照会</a></td>\n";
                echo "</tr>\n";
            }
        }
        }
    } else {
        echo "<tr>\n";
        echo "<td><a href='", INDUST_MENU, "?$uniq' target='application' onMouseover=\"status='生産 関係 処理メニューを表示します。';return true;\" onMouseout=\"status=''\"><img alt='生産 関係 処理 メニュー' border='0' src='", SITE_ICON_OFF, "'></a></td>\n";
        echo "<td nowrap><a href='", INDUST_MENU, "?$uniq' target='application' style='text-decoration:none;' onMouseover=\"status='生産 関係 処理メニューを表示します。';return true;\" onMouseout=\"status=''\" title='生産 関係 処理メニューを表示します。'>生産メニュー</a></td>\n";
        echo "</tr>\n";
    }
    if ($sid != '95') {
////////////////////////////////////////////////// index=1 売上メニュー
    if ($_SESSION['site_index'] == INDEX_SALES) {
        echo "<tr>\n";
        echo "<td bgcolor='blue'><a href='", SALES_MENU, "?$uniq' target='application' onMouseover=\"status='売上メニューを表示します。';return true;\" onMouseout=\"status=''\"><img alt='売上メニュー' border='0' src='", SITE_ICON_ON, "'></a></td>\n";
        echo "<td nowrap><a href='", SALES_MENU, "?$uniq' target='application' style='text-decoration:none;' class='current' onMouseover=\"status='売上メニューを表示します。';return true;\" onMouseout=\"status=''\" title='売上メニューを表示します。'>売上メニュー</a></td>\n";
        echo "</tr>\n";
        if ($_SESSION['site_id'] > 0) {
            if ($_SESSION['site_id'] == 11) {  // 売上実績照会 new version 売上明細照会
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SALES, "details/sales_form.php?$uniq' target='application' style='text-decoration:none;' class='current'>売上明細照会</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SALES, "details/sales_form.php?$uniq' target='application' style='text-decoration:none;'>売上明細照会</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 18) {  // 売上予定照会
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SALES, "sales_plan/sales_plan_form.php?$uniq' target='application' style='text-decoration:none;' class='current'>売上予定照会</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SALES, "sales_plan/sales_plan_form.php?$uniq' target='application' style='text-decoration:none;'>売上予定照会</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 90) {  // 製品売上 未検収 明細 照会
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SALES, "sales_miken/sales_miken_Main.php?$uniq' target='application' style='text-decoration:none;' class='current'>売上未検収</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SALES, "sales_miken/sales_miken_Main.php?$uniq' target='application' style='text-decoration:none;'>売上未検収</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 13) {  // 売上条件別合計表 特注カプラ専用
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SALES, "custom/sales_custom_form.php?$uniq' target='application' style='text-decoration:none;' class='current'>特注条件別売上</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SALES, "custom/sales_custom_form.php?$uniq' target='application' style='text-decoration:none;'>特注条件別売上</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 14) {  // 売上原価率分析(総材料費使用)
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SALES, "sales_material/sales_standard_form.php?$uniq' target='application' style='text-decoration:none;' class='current'>原価率分析</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SALES, "sales_material/sales_standard_form.php?$uniq' target='application' style='text-decoration:none;'>原価率分析</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['User_ID'] == '300144') {
            if ($_SESSION['site_id'] == 17) {  // 予測売上原価率分析(総材料費・組立日程計画使用)
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SALES, "sales_material_pre/sales_standard_pre_form.php?$uniq' target='application' style='text-decoration:none;' class='current'>予測原価率分析</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SALES, "sales_material_pre/sales_standard_pre_form.php?$uniq' target='application' style='text-decoration:none;'>予測原価率分析</a></td>\n";
                echo "</tr>\n";
            }
            }
            if ($_SESSION['site_id'] == 12) {  // 売上と総材料費の比較表
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SALES, "materialCost_sales_comp.php?$uniq' target='application' style='text-decoration:none;' class='current'>売上と総材料費</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SALES, "materialCost_sales_comp.php?$uniq' target='application' style='text-decoration:none;'>売上と総材料費</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 15) {  // 売上の材料費の比較表(損益計算書と比較) 現在では製品売上の材料費照会へ
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SALES, "materialCost_sales_comp2.php?$uniq' target='application' style='text-decoration:none;' class='current'>製品売上材料費</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SALES, "materialCost_sales_comp2.php?$uniq' target='application' style='text-decoration:none;'>製品売上材料費</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 16) {  // 部品売上の材料費
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SALES, "parts_material/parts_material_show_Main.php?$uniq' target='application' style='text-decoration:none;' class='current'>部品売上材料費</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SALES, "parts_material/parts_material_show_Main.php?$uniq' target='application' style='text-decoration:none;'>部品売上材料費</a></td>\n";
                echo "</tr>\n";
            }
            /****************************************************************************
            if ($_SESSION['site_id'] == 1) {  // 売上実績照会
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SALES, "uriage.php?$uniq' target='application' style='text-decoration:none;' class='current'>売上実績照会</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SALES, "uriage.php?$uniq' target='application' style='text-decoration:none;'>売上実績照会</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 2) {  // 指定利益率以下の売上実績照会
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SALES, "uriage_temp.php?$uniq' target='application' style='text-decoration:none;' class='current'>指定利益率以下</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SALES, "uriage_temp.php?$uniq' target='application' style='text-decoration:none;'>指定利益率以下</a></td>\n";
                echo "</tr>\n";
            }
            ****************************************************************************/
            if ($_SESSION['site_id'] == 3) {  // 売上日計グラフ 年月指定
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SALES, "uriage_graph_daily_select.php?$uniq' target='application' style='text-decoration:none;' class='current'>売上日計グラフ</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SALES, "uriage_graph_daily_select.php?$uniq' target='application' style='text-decoration:none;'>売上日計グラフ</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 4) {  // 売上月計グラフ
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='/processing_msg.php?{$uniq}&script=", SALES, "uriage_graph_all_tuki.php' target='application' style='text-decoration:none;' class='current'>売上月計グラフ</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='/processing_msg.php?{$uniq}&script=", SALES, "uriage_graph_all_tuki.php' target='application' style='text-decoration:none;'>売上月計グラフ</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 5) {  // 製品・部品の売上グラフ
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='/processing_msg.php?{$uniq}&script=", SALES, "view_all_hiritu.php' target='application' style='text-decoration:none;' class='current'>製品部品グラフ</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='/processing_msg.php?{$uniq}&script=", SALES, "view_all_hiritu.php' target='application' style='text-decoration:none;'>製品部品グラフ</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 6) {  // カプラ・リニアの売上グラフ
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='/processing_msg.php?{$uniq}&script=", SALES, "view_cl_graph.php' target='application' style='text-decoration:none;' class='current'>カプラリニアグラフ</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='/processing_msg.php?{$uniq}&script=", SALES, "view_cl_graph.php' target='application' style='text-decoration:none;'>カプラリニアグラフ</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 9) {  // カプラ標準品・特注品の売上グラフ
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='/processing_msg.php?{$uniq}&script=", SALES, "uriage_graph_sp_std.php' target='application' style='text-decoration:none;' class='current'>Ｃ特注標準グラフ</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='/processing_msg.php?{$uniq}&script=", SALES, "uriage_graph_sp_std.php' target='application' style='text-decoration:none;'>Ｃ特注標準グラフ</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 7) {  // カプラ標準品・特注品の実際原価と売上金額グラフ
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='/processing_msg.php?{$uniq}&script=", SALES, "uriage_graph_sp_std_jissai.php' target='application' style='text-decoration:none;' class='current'>Ｃ実際原価グラフ</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='/processing_msg.php?{$uniq}&script=", SALES, "uriage_graph_sp_std_jissai.php' target='application' style='text-decoration:none;'>Ｃ実際原価グラフ</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 8) {  // 月次損益照会
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SALES, "profit_loss_query_menu.php?$uniq' target='application' style='text-decoration:none;' class='current'>月次損益照会</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SALES, "profit_loss_query_menu.php?$uniq' target='application' style='text-decoration:none;'>月次損益照会</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 99) { // 損益 グラフ作成メニュー
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", PL, "graphCreate/graphCreate_Form.php?$uniq' target='application' style='text-decoration:none;' class='current'>グラフ作成メニュー</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", PL, "graphCreate/graphCreate_Form.php?$uniq' target='application' style='text-decoration:none;'>グラフ作成メニュー</a></td>\n";
                echo "</tr>\n";
            }
        }
    } else {
        echo "<tr>\n";
        echo "<td><a href='", SALES_MENU, "?$uniq' target='application' onMouseover=\"status='売上メニューを表示します。';return true;\" onMouseout=\"status=''\"><img alt='売上メニュー' border='0' src='", SITE_ICON_OFF, "'></a></td>\n";
        echo "<td nowrap><a href='", SALES_MENU, "?$uniq' target='application' style='text-decoration:none;' onMouseover=\"status='売上メニューを表示します。';return true;\" onMouseout=\"status=''\" title='売上メニューを表示します。'>売上メニュー</a></td>\n";
        echo "</tr>\n";
    }
////////////////////////////////////////////////// index=40 設備稼働管理
    if ($_SESSION['site_index'] == INDEX_EQUIP) {
        echo "<tr>\n";
        echo "<td bgcolor='blue'><a href='", EQUIP2, 'equip_factory_select.php', "?$uniq' target='application' onMouseover=\"status='設備メニューを表示します。';return true;\" onMouseout=\"status=''\"><img alt='設備稼働管理メニュー' border='0' src='", SITE_ICON_ON, "'></a></td>\n";
        echo "<td nowrap><a href='", EQUIP2, 'equip_factory_select.php', "?$uniq' target='application' style='text-decoration:none;' class='current' onMouseover=\"status='設備メニューを表示します。';return true;\" onMouseout=\"status=''\" title='設備メニューを表示します。'>設備メニュー</a></td>\n";
        echo "</tr>\n";
        if ($_SESSION['site_id'] > 0) {
            if ($factory == '6') {
                if ($_SESSION['site_id'] == 23) {  // 機械 運転 指示 2
                  echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "monitoring/monitoring_Main.php?$uniq' target='application' style='text-decoration:none;' class='current'>加工指示</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "monitoring/monitoring_Main.php?$uniq' target='application' style='text-decoration:none;'>加工指示</a></td>\n";
                    echo "</tr>\n";
                }
                /*
                if ($_SESSION['site_id'] == 10) {  // 設備･機械 運転状況 表示2
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "work/equip_working_disp.php?$uniq&status=chart' target='application' style='text-decoration:none;' class='current'>運転状況</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "work/equip_working_disp.php?$uniq&status=chart' target='application' style='text-decoration:none;'>運転状況</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 11) {  // 設備･機械 現在グラフ 表示2
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "work/equip_working_disp.php?$uniq&status=graph' target='application' style='text-decoration:none;' class='current'>運転グラフ</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "work/equip_working_disp.php?$uniq&status=graph' target='application' style='text-decoration:none;'>運転グラフ</a></td>\n";
                    echo "</tr>\n";
                }
                */
                if ($_SESSION['site_id'] == 6) {    // 加工実績照会2
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "hist/equip_jisseki_select_moni.php?$uniq' target='application' style='text-decoration:none;' class='current'>加工実績</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "hist/equip_jisseki_select_moni.php?$uniq' target='application' style='text-decoration:none;'>加工実績</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['factory'] != '') {   // 全工場モードでは表示しない
                    if ($_SESSION['site_id'] == 7) {    // 機械運転日報2
                        echo "<tr>\n";
                        echo "<td></td>\n<td nowrap><a href='", EQUIP2, "daily_report_moni/EquipMenu.php?$uniq' target='application' style='text-decoration:none;' class='current'>運転日報</a></td>\n";
                        echo "</tr>\n";
                    } else {
                        echo "<tr>\n";
                        echo "<td></td>\n<td nowrap><a href='", EQUIP2, "daily_report_moni/EquipMenu.php?$uniq' target='application' style='text-decoration:none;'>運転日報</a></td>\n";
                        echo "</tr>\n";
                    }
                }
                /*
                if ($_SESSION['site_id'] == 8) {    // スケジューラーの照会及びメンテ
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "plan/equip_plan_graph.php?$uniq' target='application' style='text-decoration:none;' class='current'>スケジュール</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "plan/equip_plan_graph.php?$uniq' target='application' style='text-decoration:none;'>スケジュール</a></td>\n";
                    echo "</tr>\n";
                }
                */
                if ($_SESSION['site_id'] == 9) {    // 現在運転中の一覧表
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "work/equip_work_moni.php?$uniq' target='application' style='text-decoration:none;' class='current'>運転中一覧</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "work/equip_work_moni.php?$uniq' target='application' style='text-decoration:none;'>運転中一覧</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 12) {   // 運転状況マップ(レイアウト)表示
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "work/equip_work_monimap.php?$uniq' target='application' style='text-decoration:none;' class='current'>レイアウト</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "work/equip_work_monimap.php?$uniq' target='application' style='text-decoration:none;'>レイアウト</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 25) {  // 設備機械のマスター保守2
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "master/equip_macMasterMnt_Main.php?$uniq' target='application' style='text-decoration:none;' class='current'>機械マスター</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "master/equip_macMasterMnt_Main.php?$uniq' target='application' style='text-decoration:none;'>機械マスター</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 26) {  // 設備機械のインターフェース マスター保守
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "master/equip_interfaceMaster_Main.php?$uniq' target='application' style='text-decoration:none;' class='current'>インターフェース</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "master/equip_interfaceMaster_Main.php?$uniq' target='application' style='text-decoration:none;'>インターフェース</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 27) {  // 設備機械のカウンター マスター保守
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "master/equip_counterMaster_Main.php?$uniq' target='application' style='text-decoration:none;' class='current'>カウントマスター</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "master/equip_counterMaster_Main.php?$uniq' target='application' style='text-decoration:none;'>カウントマスター</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 28) {  // 設備機械の停止の定義 マスター保守
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "master/equip_stopMaster_Main.php?$uniq' target='application' style='text-decoration:none;' class='current'>停止定義マスター</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "master/equip_stopMaster_Main.php?$uniq' target='application' style='text-decoration:none;'>停止定義マスター</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 29) {  // 設備機械の機械の使用インターフェース マスター保守
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "master/equip_machineInterface_Main.php?$uniq' target='application' style='text-decoration:none;' class='current'>機械とインターフェース</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "master/equip_machineInterface_Main.php?$uniq' target='application' style='text-decoration:none;'>機械とインターフェース</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 30) {  // 設備機械の工場区分(グループ) マスター保守
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "master/equip_groupMaster_Main.php?$uniq' target='application' style='text-decoration:none;' class='current'>工場区分マスター</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "master/equip_groupMaster_Main.php?$uniq' target='application' style='text-decoration:none;'>工場区分マスター</a></td>\n";
                    echo "</tr>\n";
                }
                /*
                if ($_SESSION['site_id'] == 96) {  // スポットで fwserver1 の稼動状況 Check用
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS1.php' target='application' style='text-decoration:none;' class='current'>FwServer1状況</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS1.php' target='application' style='text-decoration:none;'>FwServer1状況</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 90) {  // スポットで fwserver2 の稼動状況 Check用
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS2.php' target='application' style='text-decoration:none;' class='current'>FwServer2状況</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS2.php' target='application' style='text-decoration:none;'>FwServer2状況</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 91) {  // スポットで fwserver3 の稼動状況 Check用
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS3.php' target='application' style='text-decoration:none;' class='current'>FwServer3状況</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS3.php' target='application' style='text-decoration:none;'>FwServer3状況</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 92) {  // スポットで fwserver4 の稼動状況 Check用
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS4.php' target='application' style='text-decoration:none;' class='current'>FwServer4状況</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS4.php' target='application' style='text-decoration:none;'>FwServer4状況</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 93) {  // スポットで fwserver5 の稼動状況 Check用
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS5.php' target='application' style='text-decoration:none;' class='current'>FwServer5状況</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS5.php' target='application' style='text-decoration:none;'>FwServer5状況</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 94) {  // スポットで fwserver6 の稼動状況 Check用
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS6.php' target='application' style='text-decoration:none;' class='current'>FwServer6状況</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS6.php' target='application' style='text-decoration:none;'>FwServer6状況</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 95) {  // スポットで fwserver7 の稼動状況 Check用
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS7.php' target='application' style='text-decoration:none;' class='current'>FwServer7状況</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS7.php' target='application' style='text-decoration:none;'>FwServer7状況</a></td>\n";
                    echo "</tr>\n";
                }
                */
            } else {
                if ($_SESSION['site_id'] == 23) {  // 機械 運転 指示 2
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "work_mnt/equip_workMnt_Main.php?$uniq' target='application' style='text-decoration:none;' class='current'>加工指示</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "work_mnt/equip_workMnt_Main.php?$uniq' target='application' style='text-decoration:none;'>加工指示</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 10) {  // 設備･機械 運転状況 表示2
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "work/equip_working_disp.php?$uniq&status=chart' target='application' style='text-decoration:none;' class='current'>運転状況</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "work/equip_working_disp.php?$uniq&status=chart' target='application' style='text-decoration:none;'>運転状況</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 11) {  // 設備･機械 現在グラフ 表示2
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "work/equip_working_disp.php?$uniq&status=graph' target='application' style='text-decoration:none;' class='current'>運転グラフ</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "work/equip_working_disp.php?$uniq&status=graph' target='application' style='text-decoration:none;'>運転グラフ</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 6) {    // 加工実績照会2
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "hist/equip_jisseki_select.php?$uniq' target='application' style='text-decoration:none;' class='current'>加工実績</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "hist/equip_jisseki_select.php?$uniq' target='application' style='text-decoration:none;'>加工実績</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['factory'] != '') {   // 全工場モードでは表示しない
                    if ($_SESSION['site_id'] == 7) {    // 機械運転日報2
                        echo "<tr>\n";
                        echo "<td></td>\n<td nowrap><a href='", EQUIP2, "daily_report/EquipMenu.php?$uniq' target='application' style='text-decoration:none;' class='current'>運転日報</a></td>\n";
                        echo "</tr>\n";
                    } else {
                        echo "<tr>\n";
                        echo "<td></td>\n<td nowrap><a href='", EQUIP2, "daily_report/EquipMenu.php?$uniq' target='application' style='text-decoration:none;'>運転日報</a></td>\n";
                        echo "</tr>\n";
                    }
                }
                /*
                if ($_SESSION['site_id'] == 8) {    // スケジューラーの照会及びメンテ
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "plan/equip_plan_graph.php?$uniq' target='application' style='text-decoration:none;' class='current'>スケジュール</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "plan/equip_plan_graph.php?$uniq' target='application' style='text-decoration:none;'>スケジュール</a></td>\n";
                    echo "</tr>\n";
                }
                */
                if ($_SESSION['site_id'] == 9) {    // 現在運転中の一覧表
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "work/equip_work_all.php?$uniq' target='application' style='text-decoration:none;' class='current'>運転中一覧</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "work/equip_work_all.php?$uniq' target='application' style='text-decoration:none;'>運転中一覧</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 12) {   // 運転状況マップ(レイアウト)表示
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "work/equip_work_map.php?$uniq' target='application' style='text-decoration:none;' class='current'>レイアウト</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "work/equip_work_map.php?$uniq' target='application' style='text-decoration:none;'>レイアウト</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 25) {  // 設備機械のマスター保守2
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "master/equip_macMasterMnt_Main.php?$uniq' target='application' style='text-decoration:none;' class='current'>機械マスター</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "master/equip_macMasterMnt_Main.php?$uniq' target='application' style='text-decoration:none;'>機械マスター</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 26) {  // 設備機械のインターフェース マスター保守
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "master/equip_interfaceMaster_Main.php?$uniq' target='application' style='text-decoration:none;' class='current'>インターフェース</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "master/equip_interfaceMaster_Main.php?$uniq' target='application' style='text-decoration:none;'>インターフェース</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 27) {  // 設備機械のカウンター マスター保守
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "master/equip_counterMaster_Main.php?$uniq' target='application' style='text-decoration:none;' class='current'>カウントマスター</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "master/equip_counterMaster_Main.php?$uniq' target='application' style='text-decoration:none;'>カウントマスター</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 28) {  // 設備機械の停止の定義 マスター保守
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "master/equip_stopMaster_Main.php?$uniq' target='application' style='text-decoration:none;' class='current'>停止定義マスター</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "master/equip_stopMaster_Main.php?$uniq' target='application' style='text-decoration:none;'>停止定義マスター</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 29) {  // 設備機械の機械の使用インターフェース マスター保守
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "master/equip_machineInterface_Main.php?$uniq' target='application' style='text-decoration:none;' class='current'>機械とインターフェース</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "master/equip_machineInterface_Main.php?$uniq' target='application' style='text-decoration:none;'>機械とインターフェース</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 30) {  // 設備機械の工場区分(グループ) マスター保守
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "master/equip_groupMaster_Main.php?$uniq' target='application' style='text-decoration:none;' class='current'>工場区分マスター</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "master/equip_groupMaster_Main.php?$uniq' target='application' style='text-decoration:none;'>工場区分マスター</a></td>\n";
                    echo "</tr>\n";
                }
                /*
                if ($_SESSION['site_id'] == 96) {  // スポットで fwserver1 の稼動状況 Check用
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS1.php' target='application' style='text-decoration:none;' class='current'>FwServer1状況</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS1.php' target='application' style='text-decoration:none;'>FwServer1状況</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 90) {  // スポットで fwserver2 の稼動状況 Check用
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS2.php' target='application' style='text-decoration:none;' class='current'>FwServer2状況</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS2.php' target='application' style='text-decoration:none;'>FwServer2状況</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 91) {  // スポットで fwserver3 の稼動状況 Check用
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS3.php' target='application' style='text-decoration:none;' class='current'>FwServer3状況</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS3.php' target='application' style='text-decoration:none;'>FwServer3状況</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 92) {  // スポットで fwserver4 の稼動状況 Check用
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS4.php' target='application' style='text-decoration:none;' class='current'>FwServer4状況</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS4.php' target='application' style='text-decoration:none;'>FwServer4状況</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 93) {  // スポットで fwserver5 の稼動状況 Check用
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS5.php' target='application' style='text-decoration:none;' class='current'>FwServer5状況</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS5.php' target='application' style='text-decoration:none;'>FwServer5状況</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 94) {  // スポットで fwserver6 の稼動状況 Check用
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS6.php' target='application' style='text-decoration:none;' class='current'>FwServer6状況</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS6.php' target='application' style='text-decoration:none;'>FwServer6状況</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 95) {  // スポットで fwserver7 の稼動状況 Check用
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS7.php' target='application' style='text-decoration:none;' class='current'>FwServer7状況</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS7.php' target='application' style='text-decoration:none;'>FwServer7状況</a></td>\n";
                    echo "</tr>\n";
                }
                */
            }
        }
    } else {
        echo "<tr>\n";
        echo "<td><a href='", EQUIP2, 'equip_factory_select.php', "?$uniq' target='application' onMouseover=\"status='設備メニューを表示します。';return true;\" onMouseout=\"status=''\"><img alt='設備稼働管理メニュー' border='0' src='", SITE_ICON_OFF, "'></a></td>\n";
        echo "<td nowrap><a href='", EQUIP2, 'equip_factory_select.php', "?$uniq' target='application' style='text-decoration:none;' onMouseover=\"status='設備メニューを表示します。';return true;\" onMouseout=\"status=''\" title='設備メニューを表示します。'>設備メニュー</a></td>\n";
        echo "</tr>\n";
    }
////////////////////////////////////////////////// index=3 社員情報管理 → 社員メニューへ
    if ($_SESSION['site_index'] == INDEX_EMP) {
        echo "<tr>\n";
        echo "<td bgcolor='blue'><a href='", EMP_MENU, "?$uniq' target='application' onMouseover=\"status='社員メニューを表示します。';return true;\" onMouseout=\"status=''\"><img alt='社員情報管理メニュー' border='0' src='", SITE_ICON_ON, "'></a></td>\n";
        echo "<td nowrap><a href='", EMP_MENU, "?$uniq' target='application' style='text-decoration:none;' class='current' onMouseover=\"status='社員メニューを表示します。';return true;\" onMouseout=\"status=''\" title='社員メニューを表示します。'>社員メニュー</a></td>\n";
        echo "</tr>\n";
        if ($_SESSION['site_id'] > 0) {
            if ($_SESSION['site_id'] == 1) {    // 社員名簿(部署別)明朝 PDF出力(印刷) ja版
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", EMP, "print/print_emp_branch.php?emp_name=print_emp_section_ja&$uniq' target='application' style='text-decoration:none;' class='current'>名簿(部署)明朝</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", EMP, "print/print_emp_branch.php?emp_name=print_emp_section_ja&$uniq' target='application' style='text-decoration:none;'>名簿(部署)明朝</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 2) {    // 社員名簿(部署別)ゴシック PDF出力(印刷) MBFPDF版
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", EMP, "print/print_emp_branch.php?emp_name=print_emp_section_mbfpdf&$uniq' target='application' style='text-decoration:none;' class='current'>名簿(部署)ゴシック</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", EMP, "print/print_emp_branch.php?emp_name=print_emp_section_mbfpdf&$uniq' target='application' style='text-decoration:none;'>名簿(部署)ゴシック</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 3) {    // 社員名簿(職位別)明朝 PDF出力(印刷) ja版
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", EMP, "print/print_emp_branch.php?emp_name=print_emp_position_ja&$uniq' target='application' style='text-decoration:none;' class='current'>名簿(職位)明朝</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", EMP, "print/print_emp_branch.php?emp_name=print_emp_position_ja&$uniq' target='application' style='text-decoration:none;'>名簿(職位)明朝</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 4) {    // 社員名簿(職位別)ゴシック PDF出力(印刷) MBFPDF版
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", EMP, "print/print_emp_branch.php?emp_name=print_emp_position_mbfpdf&$uniq' target='application' style='text-decoration:none;' class='current'>名簿(職位)ゴシック</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", EMP, "print/print_emp_branch.php?emp_name=print_emp_position_mbfpdf&$uniq' target='application' style='text-decoration:none;'>名簿(職位)ゴシック</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 5) {    // 社員の教育・資格・移動経歴一覧 ゴシック PDF出力(印刷) MBFPDF版
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", EMP, "print/print_emp_branch.php?emp_name=print_emp_history_mbfpdf&$uniq' target='application' style='text-decoration:none;' class='current'>教育・異動履歴</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", EMP, "print/print_emp_branch.php?emp_name=print_emp_history_mbfpdf&$uniq' target='application' style='text-decoration:none;'>教育・異動履歴</a></td>\n";
                echo "</tr>\n";
            }
            /*
            if ($_SESSION['site_id'] == 6) {    // 社員の教育・資格・移動経歴一覧 ゴシック PDF出力(印刷) MBFPDF版 前期分
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", EMP, "print/print_emp_branch.php?emp_name=print_emp_history_z_mbfpdf&$uniq' target='application' style='text-decoration:none;' class='current'>前期教育・異動履歴</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", EMP, "print/print_emp_branch.php?emp_name=print_emp_history_z_mbfpdf&$uniq' target='application' style='text-decoration:none;'>前期教育・異動履歴</a></td>\n";
                echo "</tr>\n";
            }
            */
            if (getCheckAuthority(27)) {
                if ($_SESSION['site_id'] == 7) {    // 従業員の就業週報照会画面
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EMP, "working_hours_report/working_hours_report_Main.php' target='application' style='text-decoration:none;' class='current'>就業週報照会</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EMP, "working_hours_report/working_hours_report_Main.php' target='application' style='text-decoration:none;'>就業週報照会</a></td>\n";
                    echo "</tr>\n";
                }
            }
        }
    } else {
        echo "<tr>\n";
        echo "<td><a href='", EMP_MENU, "?$uniq' target='application' onMouseover=\"status='社員メニューを表示します。';return true;\" onMouseout=\"status=''\"><img alt='社員情報管理メニュー' border='0' src='", SITE_ICON_OFF, "'></a></td>\n";
        echo "<td nowrap><a href='", EMP_MENU, "?$uniq' target='application' style='text-decoration:none;' onMouseover=\"status='社員メニューを表示します。';return true;\" onMouseout=\"status=''\" title='社員メニューを表示します。'>社員メニュー</a></td>\n";
        echo "</tr>\n";
    }
////////////////////////////////////////////////// index=INDEX_REGU 社内規程メニュー
    if ($_SESSION['site_index'] == INDEX_REGU) {
        echo "<tr>\n";
        echo "<td bgcolor='blue'><a href='", REGU_MENU, "?$uniq' target='application' onMouseover=\"status='社内規程メニューを表示します。';return true;\" onMouseout=\"status=''\"><img alt='社内規程メニュー' border='0' src='", SITE_ICON_ON, "'></a></td>\n";
        echo "<td nowrap><a href='", REGU_MENU, "?$uniq' target='application' style='text-decoration:none;' class='current' onMouseover=\"status='社内規程メニューを表示します。';return true;\" onMouseout=\"status=''\" title='社内規程メニューを表示します。'>規程メニュー</a></td>\n";
        echo "</tr>\n";
    } else {
        echo "<tr>\n";
        echo "<td><a href='", REGU_MENU, "?$uniq' target='application' onMouseover=\"status='社内規程メニューを表示します。';return true;\" onMouseout=\"status=''\"><img alt='社内規程メニュー' border='0' src='", SITE_ICON_OFF, "'></a></td>\n";
        echo "<td nowrap><a href='", REGU_MENU, "?$uniq' target='application' style='text-decoration:none;' onMouseover=\"status='社内規程メニューを表示します。';return true;\" onMouseout=\"status=''\" title='社内規程メニューを表示します。'>規程メニュー</a></td>\n";
        echo "</tr>\n";
    }
////////////////////////////////////////////////// index=INDEX_QUALITY 品質・環境メニュー
    if ($_SESSION['site_index'] == INDEX_QUALITY) {
        echo "<tr>\n";
        echo "<td bgcolor='blue'><a href='", QUALITY_MENU, "?$uniq' target='application' onMouseover=\"status='品質・環境メニューを表示します。';return true;\" onMouseout=\"status=''\"><img alt='品質・環境メニュー' border='0' src='", SITE_ICON_ON, "'></a></td>\n";
        echo "<td nowrap><a href='", QUALITY_MENU, "?$uniq' target='application' style='text-decoration:none;' class='current' onMouseover=\"status='品質・環境メニューを表示します。';return true;\" onMouseout=\"status=''\" title='品質・環境メニューを表示します。'>品質・環境メニュー</a></td>\n";
        echo "</tr>\n";
        if ($_SESSION['site_id'] > 0) {
            if ($_SESSION['site_id'] == 71) {  // 不適合報告書 照会・作成
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", QUALITY, "unfit_report/unfit_report_Main.php?$uniq' target='application' style='text-decoration:none;' class='current'>不適合報告書 照会・作成</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", QUALITY, "unfit_report/unfit_report_Main.php?$uniq' target='application' style='text-decoration:none;'>不適合報告書 照会・作成</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 72) {  // 部署別コピー用紙使用量
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", QUALITY, "copy_pepar/copy_pepar.php?$uniq' target='application' style='text-decoration:none;' class='current'>部署別コピー用紙使用量</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", QUALITY, "copy_pepar/copy_pepar.php?$uniq' target='application' style='text-decoration:none;'>部署別コピー用紙使用量</a></td>\n";
                echo "</tr>\n";
            }
        }
    } else {
        echo "<tr>\n";
        echo "<td><a href='", QUALITY_MENU, "?$uniq' target='application' onMouseover=\"status='品質・環境メニューを表示します。';return true;\" onMouseout=\"status=''\"><img alt='品質・環境メニュー' border='0' src='", SITE_ICON_OFF, "'></a></td>\n";
        echo "<td nowrap><a href='", QUALITY_MENU, "?$uniq' target='application' style='text-decoration:none;' onMouseover=\"status='品質・環境メニューを表示します。';return true;\" onMouseout=\"status=''\" title='品質・環境メニューを表示します。'>品質・環境メニュー</a></td>\n";
        echo "</tr>\n";
    }
    ////////////////////////////////////////////////// index=INDEX_ASSET 資産管理メニュー
    if ($_SESSION['site_index'] == INDEX_ASSET) {
        echo "<tr>\n";
        echo "<td bgcolor='blue'><a href='", ASSET_MENU, "?$uniq' target='application' onMouseover=\"status='品質メニューを表示します。';return true;\" onMouseout=\"status=''\"><img alt='資産管理メニュー' border='0' src='", SITE_ICON_ON, "'></a></td>\n";
        echo "<td nowrap><a href='", ASSET_MENU, "?$uniq' target='application' style='text-decoration:none;' class='current' onMouseover=\"status='品質メニューを表示します。';return true;\" onMouseout=\"status=''\" title='資産管理メニューを表示します。'>資産管理メニュー</a></td>\n";
        echo "</tr>\n";
        if ($_SESSION['site_id'] > 0) {
            if ($_SESSION['site_id'] == 81) {  // 少額資産管理
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ASSET, "smallsum_assets_menu.php?$uniq' target='application' style='text-decoration:none;' class='current'>少額資産管理</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ASSET, "smallsum_assets_menu.php?$uniq' target='application' style='text-decoration:none;'>少額資産管理</a></td>\n";
                echo "</tr>\n";
            }
        }
        if ($_SESSION['site_id'] > 0) {
            if ($_SESSION['site_id'] == 82) {  // 圧造工具管理
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ASSET, "press_tool_menu.php?$uniq' target='application' style='text-decoration:none;' class='current'>圧造工具管理</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ASSET, "press_tool_menu.php?$uniq' target='application' style='text-decoration:none;'>圧造工具管理</a></td>\n";
                echo "</tr>\n";
            }
        }
    } else {
        echo "<tr>\n";
        echo "<td><a href='", ASSET_MENU, "?$uniq' target='application' onMouseover=\"status='品質メニューを表示します。';return true;\" onMouseout=\"status=''\"><img alt='資産管理メニュー' border='0' src='", SITE_ICON_OFF, "'></a></td>\n";
        echo "<td nowrap><a href='", ASSET_MENU, "?$uniq' target='application' style='text-decoration:none;' onMouseover=\"status='品質メニューを表示します。';return true;\" onMouseout=\"status=''\" title='資産管理メニューを表示します。'>資産管理メニュー</a></td>\n";
        echo "</tr>\n";
    }
////////////////////////////////////////////////// index=10 月次・中間・決算処理 → 損益メニュー
    if ($_SESSION['site_index'] == INDEX_PL) {
        echo "<tr>\n";
        echo "<td bgcolor='blue'><a href='", PL_MENU, "?$uniq' target='application' onMouseover=\"status='月次及び決算の損益資料を作成・照会します。';return true;\" onMouseout=\"status=''\"><img alt='損益関係(月次・中間・決算) 処理メニュー' border='0' src='", SITE_ICON_ON, "'></a></td>\n";
        echo "<td nowrap><a href='", PL_MENU, "?$uniq' target='application' style='text-decoration:none;' class='current' onMouseover=\"status='月次及び決算の損益資料を作成・照会します。';return true;\" onMouseout=\"status=''\" title='月次及び決算の損益資料を作成・照会します。'>損益メニュー</a></td>\n";
        echo "</tr>\n";
        if ($_SESSION['site_id'] > 0) {
            if ($_SESSION['site_id'] == 13) { // 月次損益 照会メニューへ
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", PL, "profit_loss_query_menu.php?$uniq' target='application' style='text-decoration:none;' class='current'>損益照会メニュー</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", PL, "profit_loss_query_menu.php?$uniq' target='application' style='text-decoration:none;'>損益照会メニュー</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 7) {  // 月次損益 作成 処理メニューへ
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", PL, "profit_loss_select.php?$uniq' target='application' style='text-decoration:none;' class='current'>損益作成メニュー</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", PL, "profit_loss_select.php?$uniq' target='application' style='text-decoration:none;'>損益作成メニュー</a></td>\n";
                echo "</tr>\n";
            }
/*** 旧タイプ コメント
            if ($_SESSION['site_id'] == 1) {  // 経理部門コード・配賦率の保守 (旧タイプ)
                echo "<tr>\n";
                echo "<td bgcolor='blue'></td>\n<td nowrap><a href='", PL, "act_table_mnt.php?$uniq' target='application' style='text-decoration:none;' class='current'>部門コード保守旧</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", PL, "act_table_mnt.php?$uniq' target='application' style='text-decoration:none;'>部門コード保守旧</a></td>\n";
                echo "</tr>\n";
            }
***/
            if ($_SESSION['site_id'] == 10) { // 経理部門コード保守 (新タイプ)
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", PL, "act_table_mnt_new.php?$uniq' target='application' style='text-decoration:none;' class='current'>部門コード保守</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", PL, "act_table_mnt_new.php?$uniq' target='application' style='text-decoration:none;'>部門コード保守</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 11) { // 大分類 項目保守 cate_allocation category_item
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", PL, "category_mnt.php?$uniq' target='application' style='text-decoration:none;' class='current'>大分類 項目保守</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", PL, "category_mnt.php?$uniq' target='application' style='text-decoration:none;'>大分類 項目保守</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 12) { // 小分類配賦率マスター保守 act_allocation allocation_item
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", PL, "allocation_mnt.php?$uniq' target='application' style='text-decoration:none;' class='current'>小分類配賦率保守</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", PL, "allocation_mnt.php?$uniq' target='application' style='text-decoration:none;'>小分類配賦率保守</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 2) {  // 経理・組織・人事コードテーブル保守 & サービス割合 応援 人件費の配賦保守
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", PL, "cd_table_mnt.php?$uniq' target='application' style='text-decoration:none;' class='current'>ｺｰﾄﾞﾃｰﾌﾞﾙ保守</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", PL, "cd_table_mnt.php?$uniq' target='application' style='text-decoration:none;'>ｺｰﾄﾞﾃｰﾌﾞﾙ保守</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 3) {  // 機械賃率計算表の作成・保守
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", PL, "machine_labor_rate_mnt.php?$uniq' target='application' style='text-decoration:none;' class='current'>機械賃率計算表</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", PL, "machine_labor_rate_mnt.php?$uniq' target='application' style='text-decoration:none;'>機械賃率計算表</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 4) {  // 組立自動機賃率・作業員賃率 作成・照会
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", PL, "wage_rate/wage_rate_menu.php?$uniq' target='application' style='text-decoration:none;' class='current'>組立賃率計算表</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", PL, "wage_rate/wage_rate_menu.php?$uniq' target='application' style='text-decoration:none;'>組立賃率計算表</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 5) {  // 直接部門へのサービス割合表の入力
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", PL, "service/service_percentage_menu.php?$uniq' target='application' style='text-decoration:none;' class='current'>サービス割合入力</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", PL, "service/service_percentage_menu.php?$uniq' target='application' style='text-decoration:none;'>サービス割合入力</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 6) {  // 作業応援月報の入力
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", PL, "pl_menu.php?$uniq' target='application' style='text-decoration:none;' class='current'>応援月報の入力</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", PL, "pl_menu.php?$uniq' target='application' style='text-decoration:none;'>応援月報の入力</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 14) { // 損益 グラフ作成メニュー
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", PL, "graphCreate/graphCreate_Form.php?$uniq' target='application' style='text-decoration:none;' class='current'>グラフ作成メニュー</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", PL, "graphCreate/graphCreate_Form.php?$uniq' target='application' style='text-decoration:none;'>グラフ作成メニュー</a></td>\n";
                echo "</tr>\n";
            }
        }
    } else {
        echo "<tr>\n";
        echo "<td><a href='", PL_MENU, "?$uniq' target='application' onMouseover=\"status='月次及び決算の損益資料を作成・照会します。';return true;\" onMouseout=\"status=''\"><img alt='月次・中間・決算の損益関係 処理メニュー' border='0' src='", SITE_ICON_OFF, "'></a></td>\n";
        echo "<td nowrap><a href='", PL_MENU, "?$uniq' target='application' style='text-decoration:none;' onMouseover=\"status='月次及び決算の損益資料を作成・照会します。';return true;\" onMouseout=\"status=''\" title='月次及び決算の損益資料を作成・照会します。'>損益メニュー</a></td>\n";
        echo "</tr>\n";
    }
////////////////////////////////////////////////// index=20 経理メニュー
    if ($_SESSION['site_index'] == INDEX_ACT) {
        echo "<tr>\n";
        echo "<td bgcolor='blue'><a href='", ACT_MENU, "?$uniq' target='application' onMouseover=\"status='経理の日報・月次処理を行います。';return true;\" onMouseout=\"status=''\"><img alt='経理の日報・月次 関係処理メニュー' border='0' src='", SITE_ICON_ON, "'></a></td>\n";
        echo "<td nowrap><a href='", ACT_MENU, "?$uniq' target='application' style='text-decoration:none;' class='current' onMouseover=\"status='経理の日報・月次処理を行います。';return true;\" onMouseout=\"status=''\" title='経理の日報・月次処理を行います。'>経理メニュー</a></td>\n";
        echo "</tr>\n";
        if ($_SESSION['site_id'] > 0) {
            if ($_SESSION['site_id'] == 10) {  // 買掛金のチェックリスト
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ACT, "act_branch.php?act_name=act_payable_view&$uniq' target='application' style='text-decoration:none;' class='current'>買掛金チェック</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ACT, "act_branch.php?act_name=act_payable_view&$uniq' target='application' style='text-decoration:none;'>買掛金チェック</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 11) {  // 支給票のチェックリスト
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ACT, "act_branch.php?act_name=act_miprov_view&$uniq' target='application' style='text-decoration:none;' class='current'>支給票チェック</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ACT, "act_branch.php?act_name=act_miprov_view&$uniq' target='application' style='text-decoration:none;'>支給票チェック</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 12) {  // 発注計画ファイルのチェックリスト
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ACT, "act_branch.php?act_name=order_plan_view&$uniq' target='application' style='text-decoration:none;' class='current'>発注計画チェック</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ACT, "act_branch.php?act_name=order_plan_view&$uniq' target='application' style='text-decoration:none;'>発注計画チェック</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 13) {  // Ａ伝情報のチェックリスト
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ACT, "act_branch.php?act_name=aden_master_view&$uniq' target='application' style='text-decoration:none;' class='current'>Ａ伝情報チェック</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ACT, "act_branch.php?act_name=aden_master_view&$uniq' target='application' style='text-decoration:none;'>Ａ伝情報チェック</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 21) {  // 棚卸データのチェックリスト
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ACT, "act_branch.php?act_name=inventory_month_view&$uniq' target='application' style='text-decoration:none;' class='current'>棚卸データチェック</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ACT, "act_branch.php?act_name=inventory_month_view&$uniq' target='application' style='text-decoration:none;'>棚卸データチェック</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 22) {  // 発注先マスターのチェックリスト
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ACT, "act_branch.php?act_name=vendor_master_view&$uniq' target='application' style='text-decoration:none;' class='current'>発注先のチェック</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ACT, "act_branch.php?act_name=vendor_master_view&$uniq' target='application' style='text-decoration:none;'>発注先のチェック</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 37) {  // 月次の無償支給品のチェックリスト
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ACT, "act_branch.php?act_name=provide_month_view&$uniq' target='application' style='text-decoration:none;' class='current'>無償支給品リスト</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ACT, "act_branch.php?act_name=provide_month_view&$uniq' target='application' style='text-decoration:none;'>無償支給品リスト</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 35) {  // 月次のカプラ 棚卸金額の照会
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ACT, "act_branch.php?act_name=inventory_month_c_view&$uniq' target='application' style='text-decoration:none;' class='current'>カプラ棚卸金額</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ACT, "act_branch.php?act_name=inventory_month_c_view&$uniq' target='application' style='text-decoration:none;'>カプラ棚卸金額</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 36) {  // 月次のリニア 棚卸金額の照会
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ACT, "act_branch.php?act_name=inventory_month_l_view&$uniq' target='application' style='text-decoration:none;' class='current'>リニア棚卸金額</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ACT, "act_branch.php?act_name=inventory_month_l_view&$uniq' target='application' style='text-decoration:none;'>リニア棚卸金額</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 32) {  // 月次のカプラ特注 棚卸金額の照会
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ACT, "act_branch.php?act_name=inventory_month_ctoku_view&$uniq' target='application' style='text-decoration:none;' class='current'>Ｃ特注棚卸金額</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ACT, "act_branch.php?act_name=inventory_month_ctoku_view&$uniq' target='application' style='text-decoration:none;'>Ｃ特注棚卸金額</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 34) {  // 月次の液体ポンプ 棚卸金額の照会
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ACT, "act_branch.php?act_name=inventory_month_bimor_view&$uniq' target='application' style='text-decoration:none;' class='current'>液体ポンプ棚卸金額</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ACT, "act_branch.php?act_name=inventory_month_bimor_view&$uniq' target='application' style='text-decoration:none;'>液体ポンプ棚卸金額</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 31) {  // 月次の仕入金額の照会
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ACT, "act_branch.php?act_name=act_purchase_view&$uniq' target='application' style='text-decoration:none;' class='current'>仕入金額の照会</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ACT, "act_branch.php?act_name=act_purchase_view&$uniq' target='application' style='text-decoration:none;'>仕入金額の照会</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 14) {  // 部門 別製造経費の照会
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ACT, "act_summary/act_summary_Main.php?$uniq' target='application' style='text-decoration:none;' class='current'>部門別経費照会</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ACT, "act_summary/act_summary_Main.php?$uniq' target='application' style='text-decoration:none;'>部門別経費照会</a></td>\n";
                echo "</tr>\n";
            }
        }
    } else {
        echo "<tr>\n";
        echo "<td><a href='", ACT_MENU, "?$uniq' target='application' onMouseover=\"status='経理の日報・月次処理を行います。';return true;\" onMouseout=\"status=''\"><img alt='経理の日報・月次 関係処理メニュー' border='0' src='", SITE_ICON_OFF, "'></a></td>\n";
        echo "<td nowrap><a href='", ACT_MENU, "?$uniq' target='application' style='text-decoration:none;' onMouseover=\"status='経理の日報・月次処理を行います。';return true;\" onMouseout=\"status=''\" title='経理の日報・月次処理を行います。'>経理メニュー</a></td>\n";
        echo "</tr>\n";
    }
////////////////////////////////////////////////// index=4 プログラム開発依頼書メニュー
    if ($_SESSION['site_index'] == INDEX_DEV) {
        echo "<tr>\n";
        echo "<td bgcolor='blue'><a href='", DEV_MENU, "?$uniq' target='application'><img alt='プログラム開発依頼書メニュー' border='0' src='", SITE_ICON_ON, "'></a></td>\n";
        echo "<td nowrap><a href='", DEV_MENU, "?$uniq' target='application' style='text-decoration:none;' class='current'>開発メニュー</a></td>\n";
        echo "</tr>\n";
        if ($_SESSION['site_id'] > 0) {
            if ($_SESSION['site_id'] == 1) {  // プログラム開発依頼書 状況照会
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", DEV, "dev_req_select.php?$uniq' target='application' style='text-decoration:none;' class='current'>開発依頼書照会</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", DEV, "dev_req_select.php?$uniq' target='application' style='text-decoration:none;'>開発依頼書照会</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 2) {  // プログラム開発依頼書 作成・送信
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", DEV, "dev_req_submit.php?$uniq' target='application' style='text-decoration:none;' class='current'>依頼書作成送信</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", DEV, "dev_req_submit.php?$uniq' target='application' style='text-decoration:none;'>依頼書作成送信</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 3) {  // 開発件数・工数グラフ
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='/processing_msg.php?{$uniq}&script=", DEV, "dev_req_graph_jisseki.php' target='application' style='text-decoration:none;' class='current'>開発件数工数グラフ</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='/processing_msg.php?{$uniq}&script=", DEV, "dev_req_graph_jisseki.php' target='application' style='text-decoration:none;'>開発件数工数グラフ</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 4) {  // 開発受付・完了・未完了グラフ
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='/processing_msg.php?{$uniq}&script=", DEV, "dev_req_graph2.php' target='application' style='text-decoration:none;' class='current'>受付 完了 未完了</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='/processing_msg.php?{$uniq}&script=", DEV, "dev_req_graph2.php' target='application' style='text-decoration:none;'>受付 完了 未完了</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 20) { // フォームのカラーチェック
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", DEV, "color_check_input.php?$uniq' target='application' style='text-decoration:none;' class='current'>カラーチェック</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", DEV, "color_check_input.php?$uniq' target='application' style='text-decoration:none;'>カラーチェック</a></td>\n";
                echo "</tr>\n";
            }
        }
    } else {
        echo "<tr>\n";
        echo "<td><a href='", DEV_MENU, "?$uniq' target='application'><img alt='プログラム開発依頼書メニュー' border='0' src='", SITE_ICON_OFF, "'></a></td>\n";
        echo "<td nowrap><a href='", DEV_MENU, "?$uniq' target='application' style='text-decoration:none;'>開発メニュー</a></td>\n";
        echo "</tr>\n";
    }
////////////////////////////////////////////////// index=99 システム管理メニュー
    if ($_SESSION['site_index'] == INDEX_SYS) {
        echo "<tr>\n";
        echo "<td bgcolor='blue'><a href='", SYS_MENU, "?$uniq' target='application' onMouseover=\"status='このメニューはシステム管理担当者のみ使用できます。';return true;\" onMouseout=\"status=''\"><img alt='システム管理メニュー' border='0' src='", SITE_ICON_ON, "'></a></td>\n";
        echo "<td nowrap><a href='", SYS_MENU, "?$uniq' target='application' style='text-decoration:none;' class='current' onMouseover=\"status='このメニューはシステム管理担当者のみ使用できます。';return true;\" onMouseout=\"status=''\" title='このメニューはシステム管理担当者のみ使用できます。'>管理メニュー</a></td>\n";
        echo "</tr>\n";
        if ($_SESSION['site_id'] > 0) {
            if ($_SESSION['site_id'] == 10) { // 日報処理
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SYS, "system_daily.php?$uniq' target='application' style='text-decoration:none;' class='current'>日報処理</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SYS, "system_daily.php?$uniq' target='application' style='text-decoration:none;'>日報処理</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 11) { // 月次処理
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SYS, "system_getuji_select.php?$uniq' target='application' style='text-decoration:none;' class='current'>月次処理</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SYS, "system_getuji_select.php?$uniq' target='application' style='text-decoration:none;'>月次処理</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 20) { // フォームのカラーチェック
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SYS, "color_check_input.php?$uniq' target='application' style='text-decoration:none;' class='current'>カラーチェック</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SYS, "color_check_input.php?$uniq' target='application' style='text-decoration:none;'>カラーチェック</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 30) { // データベース処理(現在はログのチェックに使用)
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SYS, "database/system_db.php?$uniq' target='application' style='text-decoration:none;' class='current'>ＤＢ処理</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SYS, "database/system_db.php?$uniq' target='application' style='text-decoration:none;'>ＤＢ処理</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 31) { // AS/400 Object Source File Reference ファイル照会・メンテ
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SYS, "system_as400_file.php?$uniq' target='application' style='text-decoration:none;' class='current'>AS/400検索</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SYS, "system_as400_file.php?$uniq' target='application' style='text-decoration:none;'>AS/400検索</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 41) { // phpのログ表示・クリア
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SYS, "log_view/php_log_view_clear.php?$uniq' target='application' style='text-decoration:none;' class='current'>log_view</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SYS, "log_view/php_log_view_clear.php?$uniq' target='application' style='text-decoration:none;'>log_view</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 50) { // フリーメモリチェック(おまけ的なもの)
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SYS, "top-free/free_chk.php?$uniq' target='application' style='text-decoration:none;' class='current'>フリーメモリ</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SYS, "top-free/free_chk.php?$uniq' target='application' style='text-decoration:none;'>フリーメモリ</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 52) { // top System status view
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SYS, "top-free/top_chk.php?$uniq' target='application' style='text-decoration:none;' class='current'>System status</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SYS, "top-free/top_chk.php?$uniq' target='application' style='text-decoration:none;'>System status</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 51) { // phpinfo ＰＨＰの詳細情報照会
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SYS, "phpinfo/phpinfoMain.php?$uniq' target='application' style='text-decoration:none;' class='current'>システム情報</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SYS, "phpinfo/phpinfoMain.php?$uniq' target='application' style='text-decoration:none;'>システム情報</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 60) { // 開発用 Template ファイルの実行
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SYS, "templateSample/template.php?$uniq' target='application' style='text-decoration:none;' class='current'>開発template</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SYS, "templateSample/template.php?$uniq' target='application' style='text-decoration:none;'>開発template</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 70) { // 会社の基本カレンダー メンテナンス (現在はリンクのみで70は使用されていない)
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SYS, "calendar/companyCalendar_Main.php?$uniq' target='application' style='text-decoration:none;' class='current'>基本カレンダー</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SYS, "calendar/companyCalendar_Main.php?$uniq' target='application' style='text-decoration:none;'>基本カレンダー</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 71) { // 共通 権限 テーブル メンテナンス
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SYS, "common_authority/common_authority_Main.php?$uniq' target='application' style='text-decoration:none;' class='current'>共通 権限 編集</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SYS, "common_authority/common_authority_Main.php?$uniq' target='application' style='text-decoration:none;'>共通 権限 編集</a></td>\n";
                echo "</tr>\n";
            }
        }
    } else {
        echo "<tr>\n";
        echo "<td><a href='", SYS_MENU, "?$uniq' target='application' onMouseover=\"status='このメニューはシステム管理担当者のみ使用できます。';return true;\" onMouseout=\"status=''\"><img alt='システム管理メニュー' border='0' src='", SITE_ICON_OFF, "'></a></td>\n";
        echo "<td nowrap><a href='", SYS_MENU, "?$uniq' target='application' style='text-decoration:none;' onMouseover=\"status='このメニューはシステム管理担当者のみ使用できます。';return true;\" onMouseout=\"status=''\" title='このメニューはシステム管理担当者のみ使用できます。'>管理メニュー</a></td>\n";
        echo "</tr>\n";
    }
    }
////////////////////////////////////////////////// index=999 ログアウト処理
    if ($_SESSION['site_index'] == INDEX_LOGOUT) {
        echo "<tr>\n";
        echo "<td bgcolor='blue'><a href='", ROOT, "logout.php?$uniq' target='_parent'><img alt='終了(ログアウト)' border='0' src='", SITE_ICON_ON, "'></a></td>\n";
        echo "<td nowrap><a href='", ROOT, "logout.php?$uniq' target='_parent' style='text-decoration:none;' class='current'>終了(logout)</a></td>\n";
        echo "</tr>\n";
    } else {
        echo "<tr>\n";
        echo "<td><a href='", ROOT, "logout.php?$uniq' target='_parent' onMouseover=\"status='終了処理を行います。';return true;\" onMouseout=\"status=''\"><img alt='終了(ログアウト)' border='0' src='", SITE_ICON_OFF, "'></a></td>\n";
        echo "<td nowrap><a href='", ROOT, "logout.php?$uniq' target='_parent' style='text-decoration:none;' onMouseover=\"status='終了処理を行います。';return true;\" onMouseout=\"status=''\" title='終了処理を行います。'>終了(logout)</a></td>\n";
        echo "</tr>\n";
    }
    ?>
</table>
<div id='Layer2'><img alt='TNK Site Menu' width='100%' border='0' src='<?php echo IMG?>silver_line2.gif'></div>
<br><span class='sysmsg_title'>[システムメッセージ]</span><br>
<span class='sysmsg_body'><?php echo $sysmsg ?></span>
<div id='Layer3'><img alt='TNK Site Menu' width='100%' border='0' src='<?php echo IMG?>silver_line1-2.gif'></div>
<!-- <hr> -->
</body>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
