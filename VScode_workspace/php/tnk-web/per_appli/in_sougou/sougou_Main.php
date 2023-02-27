<?php
////////////////////////////////////////////////////////////////////////////////
// 総合届（申請）                                                             //
//                               Client interface  MVC Controller の Main 部  //
// Copyright (C) 2020-2020 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2020/11/18 Created sougou_Main.php                                         //
// 2021/02/12 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug 用
// ini_set('display_errors', '1');         // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');     // zend 1.X コンパチ php4の互換モード
session_start();                        // ini_set()の次に指定すること Script 最上行
//ob_start('ob_gzhandler');               // 出力バッファをgzip圧縮

require_once ('../../function.php');    // TNK 全共通 function
require_once ('../../MenuHeader.php');  // TNK 全共通 menu class

//class Request
require_once ('../../ControllerHTTP_Class.php');       // TNK 全共通 MVC Controller Class
require_once ('../../CalendarClass.php');              // カレンダークラス スケジュールで使用
//require_once ('../../tnk_func.php');

//class Sougou_Model
require_once ('sougou_Model.php');        // MVC の Model部
//class Sougou_Controller
require_once ('sougou_Controller.php');   // MVC の Controller部

access_log();                           // Script Name は自動取得

///// Main部 の main()定義
function main()
{
    ///// TNK 共用メニュークラスのインスタンスを作成
    $menu = new MenuHeader(-1);                     // 認証チェック -1=認証なし, 0=一般以上
    
    ////////////// サイト設定
    // $menu->set_site(INDEX_INDUST, 1);            // サイト設定なし
    ////////////// リターンアドレス設定
    // $menu->set_RetUrl(EQUIP_MENU);               // 通常は指定する必要はない
    
    //////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
    $menu->set_title('総合届（申請）');

    //////////// 呼出し元のページを維持
    $menu->set_retGET('page_keep', 'on');

    //////////// リクエストオブジェクトの取得
    $request = new Request();
    
    //////////// リザルトのインスタンス生成
    $result = new Result();
    
    //////////// セッション オブジェクトの取得
    //$session = new Session();
    
    ///// キーフィールドのリクエスト取得
    $syainbangou = $request->get('syainbangou');      // uid(社員番号)のキーフィールド

    //////////// ビジネスモデル部のインスタンス生成
    $model = new Sougou_Model($request, $syainbangou);
    
    //////////// コントローラー部のインスタンス生成
    $controller = new Sougou_Controller($request, $model);
    
    //////////// Clientへ出力[show()]
    $controller->display($menu, $request, $result, $model);
}

main();

ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
