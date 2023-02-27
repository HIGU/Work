<?php
//////////////////////////////////////////////////////////////////////////////
// 設備・機械マスター の 照会 ＆ メンテナンス                               //
//              MVC Controller の Main 部                                   //
// Copyright (C) 2002-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/08/19 Created   equip_macMasterMnt_Main.php                         //
// 2002/08/08 register_globals = Off 対応                                   //
// 2003/06/17 servey(監視フラグ) Y/N が変更できない不具合を修正 及び        //
//              各入力フォームをプルダウン式に変更                          //
// 2003/06/19 $uniq = uniqid('script')を追加して JavaScript Fileを必ず読む  //
// 2004/03/04 新版テーブル equip_machine_master2 への対応                   //
// 2004/07/12 Netmoni & FWS 方式を統一 スイッチ方式 そのため Net&FWS方式追加//
//            CSV 出力設定等を 監視方式へ 項目名変更                        //
// 2005/02/14 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2005/06/24 ディレクトリ変更 equip/ → equip/master/                      //
// 2005/06/28 MVCのController部へ変更  equip_macMasterMnt_Controller.php    //
// 2005/08/18 ページ制御データをComTableMntClassへ移行してカプセル化        //
// 2005/08/19 ControllerをClass化しMain Controller を新設                   //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード
// ini_set('implicit_flush', '1');             // echo print で flush させない(遅くなるため) CLI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 WEB CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../equip_function.php');             // 設備関係の共通 function
require_once ('../../MenuHeader.php');              // TNK 全共通 menu class
require_once ('../EquipControllerHTTP.php');        // 設備関係に拡張した MVC Controller Class
require_once ('equip_macMasterMnt_Controller.php'); // MVC の Controller部
require_once ('equip_macMasterMnt_Model.php');      // MVC の モデル部
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(40, 25);                    // site_index=40(設備メニュー) site_id=25(機械マスター)
////////////// リターンアドレス設定
// $menu->set_RetUrl(EQUIP_MENU);              // 通常は指定する必要はない

//////////// リクエストオブジェクトの取得
$request = new Request();
//////////// リザルトのインスタンス生成
$result = new Result();
//////////// セッションのインスタンス生成
$session = new equipSession();

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title("機械マスター メンテナンス&nbsp;&nbsp;{$session->getFactName()}");
//////////// 表題の設定

//////////// ビジネスモデル部のインスタンス生成
$model = new EquipMacMstMnt_Model($session->get('factory'), $request);

//////////// コントローラー部のインスタンス生成
$controller = new EquipMacMstMnt_Controller($menu, $request, $result, $model, $session);

//////////// 画面出力
// $controller->display();

ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
