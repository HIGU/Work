<?php
//////////////////////////////////////////////////////////////////////////////
// 設備・機械の停止の定義(ストップ) マスター 照会＆メンテナンス             //
//              MVC Controller の Main 部                                   //
// Copyright (C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/07/16 Created   equip_stopMaster_Main.php                           //
// 2005/07/28 ControllerHTTP_Class → EquipControllerHTTP へ変更            //
// 2005/08/18 ページ制御データをComTableMntClassへ移行してカプセル化        //
// 2005/08/19 ControllerをClass化しMain Controller を新設                   //
// 2005/11/01 $controller->display()メソッドのコメントを解除                //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード
// ini_set('implicit_flush', '1');             // echo print で flush させない(遅くなるため) CLI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 WEB CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../equip_function.php');             // 設備関係共用 function
require_once ('../../MenuHeader.php');              // TNK 全共通 menu class
require_once ('../EquipControllerHTTP.php');        // 設備関係に拡張した MVC Controller Class
require_once ('equip_stopMaster_Controller.php');   // MVC の Controller部
require_once ('equip_stopMaster_Model.php');        // MVC の モデル部
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(40, 28);                    // site_index=40(設備メニュー) site_id=28(停止の定義マスター)
////////////// リターンアドレス設定
// $menu->set_RetUrl(EQUIP_MENU);              // 通常は指定する必要はない

//////////// リクエストオブジェクトの取得
$request = new Request();
//////////// リザルトのインスタンス生成
$result = new Result();
//////////// セッションのインスタンス生成
$session = new equipSession();

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title("機械の停止定義 マスターの照会・編集&nbsp;&nbsp;{$session->getFactName()}");

//////////// ビジネスモデル部のインスタンス生成
$model = new EquipStopMaster_Model($session->get('factory'), $request);

//////////// コントローラー部のインスタンス生成
$controller = new EquipStopMaster_Controller($menu, $request, $result, $model, $session);

//////////// 画面出力
$controller->display($menu, $request, $result, $model, $session);

ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
