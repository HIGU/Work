<?php
//////////////////////////////////////////////////////////////////////////////
// サービス割合 部門別 入力 独自Templateを使用                              //
// Copyright(C) 2003-2012      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp  //
// Changed history                                                          //
// 2003/10/17 Created   service_percentage_input.php                        //
// 2003/10/21 直接部門(グループ)をマスター化しロジック上から取込む          //
// 2003/10/22 HTML 関係をテンプレート風にし条件分岐で include_once する     //
//     JavaScript等から直接呼ばれた場合HTTP_REFERERにはmenu_frameが入る     //
// 2003/10/27 service_percent_historyに intextフィールドを追加し保存        //
// 2003/11/05 月次確定済みチェックのロジックを追加                          //
// 2003/11/12 内作・外作間接費の表示色変更 order_noによる表示順に変更       //
//            div(事業部)section(部門別)order_no(表示順)note(備考)を追加    //
// 2004/04/19 (部門毎の)一括入力方式だったのを個人毎にも入力出来るように変更//
//                          前期の実績表示をグレーに変更                    //
// 2007/01/24 MenuHeaderクラス対応                                          //
// 2012/10/06 決算時に前月コピーが効かないのを修正                     大谷 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
ini_set('error_reporting',E_ALL);           // E_ALL='2047' debug 用
ini_set('display_errors','1');              // Error 表示 ON debug 用 
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');
require_once ('../../tnk_func.php');
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(10,  5);                    // site_index=10(損益メニュー) site_id=5(サービス割合メニュー)

$menu_title = "$view_ym サービス割合 $section_name 部門 入力";

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title($menu_title);

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();

//if (isset($_POST['check'])) {           // 確認用の実行ボタンが押された時
//    include_once ('templates/service_percentage_check.templ.html');
//} elseif (isset($_POST['repair'])) {    // 修正ボタンが押された時
//    include_once ('templates/service_percentage_input.templ.html');
//} else {                                // 初期入力フォーム
include_once ('templates/service_percentage_input.templ.html');
//}
?>

<?php ob_end_flush(); // 出力バッファをgzip圧縮 END ?>
