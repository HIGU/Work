<?php
//////////////////////////////////////////////////////////////////////////////
// 組立の作業管理 着手・完了データ 照会  Client interface 部                //
//                                              MVC Controller の Main 部   //
// Copyright (C) 2006-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/01/19 Created   assembly_process_show_Main.php                      //
// 2006/01/20 showGroup showMenuのチェック・設定をControllerへ移動          //
//            上記に伴い Modelの__construct()もshowGroupの 全て=0 に対応    //
// 2007/03/19 文字コードの問題のためset_action('引当構成表')→'AlloConfView'//
// 2007/03/24 material/allo_conf_parts_view.php →                          //
//                           parts/allocate_config/allo_conf_parts_Main.php //
// 2007/03/26 計画番号クリック時の行番号保存処理を追加                      //
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
require_once ('../../../ControllerHTTP_Class.php');     // TNK 全共通 MVC Controller Class
require_once ('../../../function.php');                 // access_log()等で使用
require_once ('assembly_process_show_Controller.php');  // MVC の Controller部
require_once ('assembly_process_show_Model.php');       // MVC の Model部
access_log();                               // Script Name は自動取得

///// Main部の main() function
function main()
{
    ///// TNK 共用メニュークラスのインスタンスを作成
    $menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
    
    ////////////// サイト設定
    $menu->set_site(INDEX_INDUST, 6);           // site_index=INDEX_INDUST(生産メニュー) site_id=6(組立状況照会)999(サイトを開く)
    ////////////// リターンアドレス設定
    // $menu->set_RetUrl(EQUIP_MENU);              // 通常は指定する必要はない
    
    //////////// リクエストオブジェクトの取得
    $request = new Request();
    
    //////////// リザルトのインスタンス生成
    $result = new Result();
    
    //////////// セッション オブジェクトの取得
    ///// メニュー切替用 showGroupとshowMenu のデータチェック ＆ 設定 (ModelとControllerで使用する)
    $session = new Session();
    
    ///// 行番号保存(計画番号クリック時)のリクエスト処理
    if ( $request->get('recNo') ) {
        $session->add_local('recNo', $request->get('recNo'));
        exit();
    }
    
    //////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
    $title = '組立 作業 着手・完了 照会 メニュー';
    $menu->set_title($title);
    //////////// 呼出先のaction名とアドレス設定
    // $menu->set_action('AlloConfView',   INDUST . 'material/allo_conf_parts_view.php');
    $menu->set_action('AlloConfView',   INDUST . 'parts/allocate_config/allo_conf_parts_Main.php');
    
    //////////// コントローラー部のインスタンス生成
    $controller = new AssemblyProcessShow_Controller($request, $session);
    
    //////////// Clientへ出力[show()]
    $controller->display($menu, $request, $result, $session);
}
main();

ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
