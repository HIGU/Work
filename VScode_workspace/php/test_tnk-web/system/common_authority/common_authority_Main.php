<?php
//////////////////////////////////////////////////////////////////////////////
// 共通 権限 関係テーブル メンテナンス                Client interface 部   //
//                                              MVC Controller の Main 部   //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/07/15 Created   common_authority_Main.php                           //
// 2006/08/03 デバッグモードを追加 _TNK_DEBUG 現在はログの保存モード        //
//////////////////////////////////////////////////////////////////////////////
define('_TNK_DEBUG', false);                // デバッグ時はtrue
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
ini_set('error_reporting', E_ALL);          // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード
// ini_set('implicit_flush', '0');             // echo print で flush させない(遅くなるため) CLI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 WEB CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');                    // access_log()等で使用
if (_TNK_DEBUG) access_log();                           // Script Name は自動取得

require_once ('../../MenuHeader.php');                  // TNK 全共通 menu class
// require_once ('../../tnk_func.php');                    // day_off(), date_offset() で使用
require_once ('../../ControllerHTTP_Class.php');        // TNK 全共通 MVC Controller Class
require_once ('common_authority_Controller.php');       // MVC の Controller部
require_once ('common_authority_Model.php');            // MVC の Model部

///// Main部の main() function
function main()
{
    ///// TNK 共用メニュークラスのインスタンスを作成
    $menu = new MenuHeader(3);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
    
    ////////////// サイト設定
    $menu->set_site(INDEX_SYS, 71);              // site_index=INDEX_SYS(システムメニュー) site_id=71(共通権限編集)999(サイトを開く)
    ////////////// リターンアドレス設定
    // $menu->set_RetUrl(INDUST_MENU);             // 通常は指定する必要はない
    
    //////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
    $title = '共通 権限 メンテナンス';
    $menu->set_title($title);
    //////////// 呼出先のaction名とアドレス設定
    // $menu->set_action('引当構成表',   INDUST . 'material/allo_conf_parts_view.php');
    
    //////////// コントローラー部のインスタンス生成
    $controller = new CommonAuthority_Controller($menu);
    
    //////////// Clientからのリクエスト処理
    $controller->Execute();
    //////////// Clientへ出力 [show()]
    $controller->display();
}
main();

ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>