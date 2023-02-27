<?php
//////////////////////////////////////////////////////////////////////////////
// 部品 在庫 経歴 照会 (ＭＶＣ版)                     Client interface 部   //
//                                              MVC Controller の Main 部   //
// Copyright (C) 2004-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/12/20 Created   parts_stock_history_Main.php (parts_stock_view.php) //
// 2007/03/09 オリジナルはparts_stock_view.php でparts_stock_plan_Main.phpに//
//            合わせて完全なＭＶＣモデルでコーディングした。                //
//            変更経歴は backup/parts_stock_view.php を参照すること。       //
// 2007/03/20 parts_stock_view.phpが他のプログラムで頻繁に使用されているため//
//          parts_stock_history_Main.php→parts_stock_view.phpとで２重かする//
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
ini_set('error_reporting', E_ALL);          // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード
// ini_set('implicit_flush', '0');             // echo print で flush させない(遅くなるため) CLI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 WEB CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../../MenuHeader.php');               // TNK 全共通 menu class
require_once ('../../../tnk_func.php');                 // day_off(), date_offset() で使用
require_once ('../../../ControllerHTTP_Class.php');     // TNK 全共通 MVC Controller Class
require_once ('../../../function.php');                 // access_log()等で使用
require_once ('parts_stock_history_Controller.php');    // MVC の Controller部
require_once ('parts_stock_history_Model.php');         // MVC の Model部
access_log();                                           // Script Name は自動取得

///// Main部の main() function
function main()
{
    ///// TNK 共用メニュークラスのインスタンスを作成
    $menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
    
    ////////////// サイト設定
    $menu->set_site(INDEX_INDUST, 40);          // site_index=INDEX_INDUST(生産メニュー) site_id=40(在庫経歴)999(サイトを開く)
    ////////////// リターンアドレス設定
    // $menu->set_RetUrl(INDUST_MENU);             // 通常は指定する必要はない
    
    //////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
    $title = '部品 在庫 経歴 照会';
    $menu->set_title($title);
    //////////// 呼出先のaction名とアドレス設定
    $menu->set_action('買掛実績照会',     INDUST . 'payable/act_payable_view.php');
    // 互いに呼出し元となり無限ループを避けるため
    // $controller->CondFormExecute()メソッドで以下のアクションをセットしている
    // $menu->set_action('在庫予定照会',   INDUST . 'parts/parts_stock_plan/parts_stock_plan_Main.php');
    
    //////////// コントローラー部のインスタンス生成
    $controller = new PartsStockHistory_Controller($menu);
    
    //////////// Clientへ出力 [show()]
    $controller->display();
}
main();

ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
