<?php
//////////////////////////////////////////////////////////////////////////////
// 設備・機械のグループ(工場)区分 マスター 照会＆メンテナンス               //
//              MVC Controller の Main 部                                   //
// Copyright (C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/08/19 Created   equip_groupMaster_Main.php                          //
//            group → group_no へ PostgreSQLでは予約語で使用できないため   //
// 2005/08/18 ページ制御データをComTableMntClassへ移行してカプセル化        //
// 2005/08/19 ControllerをClass化しMain Controller を新設                   //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード
// ini_set('implicit_flush', '0');             // echo print で flush させない(遅くなるため) CLI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 WEB CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../MenuHeader.php');              // TNK 全共通 menu class
require_once ('../../ControllerHTTP_Class.php');    // TNK 全共通 MVC Controller Class
require_once ('../equip_function.php');             // access_log(), getFactory(), equipAuthUser()等で使用
require_once ('equip_groupMaster_Controller.php');  // MVC の Controller部
require_once ('equip_groupMaster_Model.php');       // MVC の Model部
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(40, 30);                    // site_index=40(設備メニュー) site_id=30(グループ工場区分)
////////////// リターンアドレス設定
// $menu->set_RetUrl(EQUIP_MENU);              // 通常は指定する必要はない

/////////// 工場区分と工場名を取得する
// $fact_name = getFactory($factoryList);

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('工場区分 マスターの照会・編集');

//////////// リクエストオブジェクトの取得
$request = new Request();
//////////// リザルトのインスタンス生成
$result = new Result();

//////////// ビジネスモデル部のインスタンス生成
$model = new EquipGroupMaster_Model($request);

//////////// コントローラー部のインスタンス生成
$controller = new EquipGroupMaster_Controller($menu, $request, $result, $model);

//////////// 画面出力
// $controller->display();

ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
