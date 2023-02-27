<?php
//////////////////////////////////////////////////////////////////////////////
// php のエラーログ表示・手動クリア                                         //
// Copyright(C) 2020-2020  Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// http://masterst/system/log_view/php_log_view_clear_hand.phpを実行        //
// Changed history                                                          //
// 2020/09/15 Created  php_log_view_clear_hand.php                          //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);           // E_STRICT=2048(php5) E_ALL=2047 debug 用
ini_set('error_reporting', E_ALL);              // E_STRICT=2048(php5) E_ALL=2047 debug 用
ini_set('display_errors', '1');                 // Error 表示 ON debug 用 リリース後コメント
session_start();                                // ini_set()の次に指定すること Script 最上行
ob_start('ob_gzhandler');                       // 出力バッファをgzip圧縮

require_once ('../../function.php');            // TNK 全共通 function
require_once ('../../MenuHeader.php');          // TNK 全共通 menu class
require_once ('../../ControllerHTTP_Class.php');// TNK 全共通 MVC Controller Class
access_log();                                   // Script Name 自動設定

function main()
{
    ///// TNK 共用メニュークラスのインスタンスを作成
    if ($_SESSION['User_ID'] == '300161') {     // 斎藤千尋さんの場合はテスト環境があるので一般ユーザーで
        $menu = new MenuHeader(0);              // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
    } else {
        $menu = new MenuHeader(3);              // 認証チェック3=admin以上 戻り先=TOP_MENU タイトル未設定
    }
    
    ////////////// サイト設定
    $menu->set_site(99, 41);                    // site_index=99(システム管理メニュー) site_id=41(ログチェック)
    ////////////// リターンアドレス設定
    // $menu->set_RetUrl(SYS_MENU);
    //////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
    $menu->set_title('Administrator php apache log check');
    //////////// 表題の設定
    $menu->set_caption('php apache log view');
    //////////// 呼出先のaction名とアドレス設定
    // $menu->set_action('action_name', SYS. 'script_name.php');
}

    //////////// php_error clear ボタンが押された時
        `/bin/cat /tmp/php_error >> /tmp/save_php_error.log`;
        `> /tmp/php_error`;
    //////////// apache error clear ボタンが押された時
        `/bin/cat /usr/local/apache2/logs/error_log >> /tmp/save_apache_error.log`;
        `> /usr/local/apache2/logs/error_log`;
    //////////// access_log clear ボタンが押された時
        `/bin/cat /usr/local/apache2/logs/access_log >> /tmp/save_access_log`;
        `> /usr/local/apache2/logs/access_log`;
    //////////// php ログデータの有り無し取得
    $php = '/tmp/php_error';
    if (file_exists($php)) {
        $php_error_log = `/bin/cat $php`;
        if ($php_error_log == '') {
            $php_flg = false;
        } else {
            $php_flg = true;
        }
    } else {
        $php_flg = false;
    }


?>
