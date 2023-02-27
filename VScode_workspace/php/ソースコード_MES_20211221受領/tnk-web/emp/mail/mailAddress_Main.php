<?php
//////////////////////////////////////////////////////////////////////////////
// 社員マスターのメールアドレス 照会・メンテナンス                          //
//                             Client interface  MVC Controller の Main 部  //
// Copyright (C) 2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/11/15 Created   mailAddress_Main.php                                //
// 2007/06/15 view_file_name(__FILE__)を追加 (タイトル上部のアドレス)       //
//////////////////////////////////////////////////////////////////////////////
// ini_set('mbstring.http_output', 'UTF-8');           // ajaxで使用する場合
// ini_set('error_reporting', E_STRICT);               // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('error_reporting', E_ALL);                  // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');                     // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');        // zend 1.X コンパチ php4の互換モード
// ob_start('ob_gzhandler');                           // 出力バッファをgzip圧縮
// session_start();                                    // ini_set()の次に指定すること Script 最上行

// require_once ('../function.php');                // access_log()等で使用
require_once ('../MenuHeader.php');                 // TNK 全共通 menu class
require_once ('../ControllerHTTP_Class.php');       // TNK 全共通 MVC Controller Class
// require_once ('../CalendarClass.php');           // カレンダークラス スケジュールで使用
require_once ('mail/mailAddress_Controller.php');   // MVC の Controller部
require_once ('mail/mailAddress_Model.php');        // MVC の Model部
// access_log();                                       // Script Name は自動取得
access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));
echo view_file_name(__FILE__);

///// Main部 の main()定義
function main()
{
    ///// TNK 共用メニュークラスのインスタンスを作成
    $menu = new MenuHeader(0);                     // 認証チェック -1=認証なし, 0=一般 1=課長 2=部長以上 3=アドミニ
    // 照会は一般以上でOK 編集は2以上
    
    ////////////// サイト設定
    // $menu->set_site(INDEX_INDUST, 1);            // サイト設定なし
    ////////////// リターンアドレス設定
    // $menu->set_RetUrl(EQUIP_MENU);               // 通常は指定する必要はない
    
    //////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
    // $menu->set_title('社員マスターのメールアドレス照会・編集');
    
    //////////// リクエストオブジェクトの取得
    $request = new Request();
    if ($request->get('condition') == '') $request->add('condition', 'genzai');
    
    //////////// リザルトのインスタンス生成
    $result = new Result();
    
    //////////// ビジネスモデル部のインスタンス生成
    $model = new mailAddress_Model($request);
    
    //////////// コントローラー部のインスタンス生成
    $controller = new mailAddress_Controller($menu, $request, $result, $model);
    
    //////////// Clientへ出力[show()]
    $controller->display($menu, $request, $result, $model);
}
main();

// ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
