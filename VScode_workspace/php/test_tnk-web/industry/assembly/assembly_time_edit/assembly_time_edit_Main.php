<?php
//////////////////////////////////////////////////////////////////////////////
// 組立の作業管理実績データ 編集  Client interface 部                       //
//                                              MVC Controller の Main 部   //
// Copyright (C) 2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/12/07 Created   assembly_time_edit_Main.php                         //
// 2007/03/24 material/allo_conf_parts_view.php →                          //
//                           parts/allocate_config/allo_conf_parts_Main.php //
// 2007/09/20 E_ALL | E_STRICT へ変更                                       //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);
// ini_set('error_reporting', E_ALL);          // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード
// ini_set('implicit_flush', '0');             // echo print で flush させない(遅くなるため) CLI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 WEB CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../../MenuHeader.php');               // TNK 全共通 menu class
require_once ('../../../ControllerHTTP_Class.php');     // TNK 全共通 MVC Controller Class
require_once ('../../../function.php');                 // access_log()等で使用
require_once ('assembly_time_edit_Controller.php');     // MVC の Controller部
require_once ('assembly_time_edit_Model.php');          // MVC の Model部
access_log();                               // Script Name は自動取得

///// Main部の main() function
function main()
{
    ///// TNK 共用メニュークラスのインスタンスを作成
    $menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
    
    ////////////// サイト設定
    $menu->set_site(INDEX_INDUST, 4);           // site_index=INDEX_INDUST(生産メニュー) site_id=4(組立実績編集)999(サイトを開く)
    ////////////// リターンアドレス設定
    // $menu->set_RetUrl(EQUIP_MENU);              // 通常は指定する必要はない
    
    //////////// リクエストオブジェクトの取得
    $request = new Request();
    
    //////////// リザルトのインスタンス生成
    $result = new Result();
    
    //////////// セッション オブジェクトの取得 (2005/10/21 Add)
    ///// メニュー切替用 showGroup のデータチェック ＆ 設定 (ModelとControllerで使用する)
    $session = new Session();
    $showGroup = $request->get('showGroup');
    if ($showGroup == '') {
        if ($session->get_local('showGroup') == '') {
            $showGroup = '0';               // 指定がない場合は未選択状態にする
        } else {
            $showGroup = $session->get_local('showGroup');
        }
    }
    $session->add_local('showGroup', $showGroup);
    $request->add('showGroup', $showGroup);
    
    //////////// ビジネスモデル部のインスタンス生成
    $model = new AssemblyTimeEdit_Model($request);
    
    //////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
    $title = '組立 作業 実績 照会・編集 メニュー';
    $menu->set_title($title);
    //////////// 呼出先のaction名とアドレス設定
    // $menu->set_action('引当構成表',   INDUST . 'material/allo_conf_parts_view.php');
    $menu->set_action('引当構成表',   INDUST . 'parts/allocate_config/allo_conf_parts_Main.php');
    
    //////////// コントローラー部のインスタンス生成
    $controller = new AssemblyTimeEdit_Controller($request, $model, $result, $session);
    
    //////////// Clientへ出力[show()]
    $controller->display($menu, $request, $result, $model, $session);
}
main();

ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
