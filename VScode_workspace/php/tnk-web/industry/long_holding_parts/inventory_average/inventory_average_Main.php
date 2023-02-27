<?php
//////////////////////////////////////////////////////////////////////////////
// 資材在庫部品 全品種の月平均出庫数・保有月数等照会    Client interface 部 //
//                                                   MVC Controller Main 部 //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/05/23 Created   inventory_average_Main.php                          //
// 2007/06/09 最新単価のクリックで単価登録照会機能を追加                    //
// 2007/06/11 $controller->Execute() を追加                                 //
// 2007/06/12 デバッグモードを追加 _TNK_DEBUG 現在はログの保存モード        //
// 2007/06/14 要因マスターの編集。コメント・要因の登録編集完了 照会→分析へ //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
ini_set('error_reporting', E_ALL);          // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード
// ini_set('implicit_flush', '0');             // echo print で flush させない(遅くなるため) CLI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 WEB CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

define('_TNK_DEBUG', false);                // デバッグ時はtrue

require_once ('../../../MenuHeader.php');               // TNK 全共通 menu class
require_once ('../../../tnk_func.php');                 // day_off(), date_offset() で使用
require_once ('../../../ControllerHTTP_Class.php');     // TNK 全共通 MVC Controller Class
require_once ('../../../function.php');                 // access_log()等で使用
require_once ('inventory_average_Controller.php');      // MVC の Controller部
require_once ('inventory_average_Model.php');           // MVC の Model部
access_log();                                           // Script Name は自動取得

///// Main部の main() function
function main()
{
    ///// TNK 共用メニュークラスのインスタンスを作成
    $menu = new MenuHeader(0);                          // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
    
    ////////////// サイト設定
    $menu->set_site(INDEX_INDUST, 15);                  // site_index=INDEX_INDUST(生産メニュー) site_id=15(長期滞留部品)999(サイトを開く)
    ////////////// リターンアドレス設定
    // $menu->set_RetUrl(EQUIP_MENU);                   // 通常は指定する必要はない
    
    //////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
    $title = '資材部品 在庫金額・月平均出庫数・保有月数の要因分析';
    $menu->set_title($title);
    //////////// 呼出先のaction名とアドレス設定
    $menu->set_action('在庫経歴',       INDUST . 'parts/parts_stock_history/parts_stock_history_Main.php');
    $menu->set_action('単価登録照会',   INDUST . 'parts/parts_cost_view.php');
    
    //////////// コントローラー部のインスタンス生成
    $controller = new InventoryAverage_Controller($menu);
    
    //////////// Clientからのリクエスト処理
    $controller->Execute();
    //////////// Clientへ出力 [show()]
    $controller->display();
}
main();

ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
