<?php
//////////////////////////////////////////////////////////////////////////////
// 組立日程計画表(AS/400版)スケジュール 照会  Client interface 部           //
//                                              MVC Controller の Main 部   //
// Copyright (C) 2006-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/01/23 Created   assembly_schedule_show_Main.php                     //
// 2006/03/03 out_action に実績(登録)工数照会を追加                         //
// 2007/02/13 php-5.2.1でMemory limit is now enabled by default.になったので//
//            memory_limit = '128M' をini_set()に追加                       //
// 2007/03/24 material/allo_conf_parts_view.php →                          //
//                           parts/allocate_config/allo_conf_parts_Main.php //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
ini_set('error_reporting', E_ALL);          // E_STRICT=2048(php5) E_ALL=2047 debug 用
ini_set('memory_limit', '128M');            // ガントチャート用に使用メモリーを増やす
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
require_once ('assembly_schedule_show_Controller.php'); // MVC の Controller部
require_once ('assembly_schedule_show_Model.php');      // MVC の Model部
require_once ('../../../../jpgraph-4.4.1/src/jpgraph.php');               // Common Graph class
require_once ('../../../../jpgraph-4.4.1/src/jpgraph_gantt.php');         // GanttChart Graph class
access_log();                               // Script Name は自動取得

///// Main部の main() function
function main()
{
    ///// TNK 共用メニュークラスのインスタンスを作成
    $menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
    
    ////////////// サイト設定
    $menu->set_site(INDEX_INDUST, 7);           // site_index=INDEX_INDUST(生産メニュー) site_id=7(日程表照会)999(サイトを開く)
    ////////////// リターンアドレス設定
    // $menu->set_RetUrl(EQUIP_MENU);              // 通常は指定する必要はない
    
    //////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
    $title = '組立 日程 計画表 照会 メニュー';
    $menu->set_title($title);
    //////////// 呼出先のaction名とアドレス設定
    // $menu->set_action('引当構成表',   INDUST . 'material/allo_conf_parts_view.php');
    $menu->set_action('引当構成表',   INDUST . 'parts/allocate_config/allo_conf_parts_Main.php');
    $menu->set_action('実績工数照会',   INDUST . 'assembly/assembly_time_show/assembly_time_show_Main.php');
    
    //////////// コントローラー部のインスタンス生成
    $controller = new AssemblyScheduleShow_Controller($menu);
    
    //////////// Clientへ出力 [show()]
    $controller->display();
}
main();

ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
