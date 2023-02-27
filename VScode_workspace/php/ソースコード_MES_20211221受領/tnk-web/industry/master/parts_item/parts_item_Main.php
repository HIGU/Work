<?php
//////////////////////////////////////////////////////////////////////////////
// 生産システムの部品・製品関係のアイテムマスター  Client interface 部      //
//                                              MVC Controller の Main 部   //
// Copyright (C) 2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/09/13 Created   parts_item_Main.php                                 //
// 2005/09/15 site_id=1 をこのアイテムマスターに割当て                      //
// 2005/09/26 main()の定義追加 と display()にパラメータ(オブジェクト)追加   //
// 2007/09/07 呼出し元のページ維持(無条件)を追加                            //
//////////////////////////////////////////////////////////////////////////////
// ini_set('mbstring.http_output', 'UTF-8');
ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード
// ini_set('implicit_flush', '0');             // echo print で flush させない(遅くなるため) CLI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 WEB CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../../MenuHeader.php');           // TNK 全共通 menu class
require_once ('../../../ControllerHTTP_Class.php'); // TNK 全共通 MVC Controller Class
require_once ('../../../function.php');             // access_log()等で使用
require_once ('parts_item_Controller.php');         // MVC の Controller部
require_once ('parts_item_Model.php');              // MVC の Model部
access_log();                               // Script Name は自動取得

///// Main部 の main()定義
function main()
{
    ///// TNK 共用メニュークラスのインスタンスを作成
    $menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
    
    ////////////// サイト設定
    $menu->set_site(INDEX_INDUST, 1);           // site_index=INDEX_INDUST(生産メニュー) site_id=1(アイテムマスターに割当て)
    ////////////// リターンアドレス設定
    // $menu->set_RetUrl(EQUIP_MENU);              // 通常は指定する必要はない
    
    //////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
    $menu->set_title('部品・製品 アイテムマスターの照会・メンテナンス');
    //////////// 呼出し元のページを維持
    $menu->set_retGET('page_keep', 'on');
    
    //////////// リクエストオブジェクトの取得
    $request = new Request();
    //////////// リザルトのインスタンス生成
    $result = new Result();
    
    ///// キーフィールドのリクエスト取得
    $partsKey = $request->get('partsKey');      // mipn(部品番号)のキーフィールド
    
    //////////// ビジネスモデル部のインスタンス生成
    $model = new PartsItem_Model($request, $partsKey);
    
    //////////// コントローラー部のインスタンス生成
    $controller = new PartsItem_Controller($menu, $request, $result, $model);
    
    //////////// Clientへ出力[show()]
    $controller->display($menu, $request, $result, $model);
}
main();

ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
