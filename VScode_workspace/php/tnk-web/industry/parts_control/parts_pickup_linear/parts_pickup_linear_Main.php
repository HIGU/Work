<?php
//////////////////////////////////////////////////////////////////////////////
// 資材管理の部品出庫 着手・完了時間 集計用  Client interface 部            //
//                                              MVC Controller の Main 部   //
// Copyright (C) 2005-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/09/12 Created   parts_pickup_linear_Main.php                        //
// 2005/09/28 引当構成表のリンクを出庫着手一覧表に追加 クリックでジャンプ   //
// 2005/12/08 current_menu を セッションオブジェクトに登録                  //
// 2005/12/10 E_STRICT でエラーメッセージが出ないので E_ALL へ変更          //
//            文法エラーは E_ALL で 実行時の詳細は E_STRICT で行う。        //
// 2006/06/06 parts_pickup_time → parts_pickup_linear へ変更しリニア版作成 //
//            ASP(JSP)タグを廃止して phpの推奨タグへ変更                    //
// 2007/03/24 material/allo_conf_parts_view.php →                          //
//                           parts/allocate_config/allo_conf_parts_Main.php //
//////////////////////////////////////////////////////////////////////////////
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
require_once ('parts_pickup_linear_Controller.php');    // MVC の Controller部
require_once ('parts_pickup_linear_Model.php');         // MVC の Model部
access_log();                               // Script Name は自動取得

///// Main部の main() function
function main()
{
    ///// TNK 共用メニュークラスのインスタンスを作成
    $menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
    
    ////////////// サイト設定
    $menu->set_site(INDEX_INDUST, 18);          // site_index=INDEX_INDUST(生産メニュー) site_id=18(リニア資材部品出庫メニュー)999(サイトを開く)
    ////////////// リターンアドレス設定
    // $menu->set_RetUrl(EQUIP_MENU);              // 通常は指定する必要はない
    
    //////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
    $menu->set_title('リニア資材専用 部品 出庫 メニュー');
    //////////// 呼出先のaction名とアドレス設定
    // $menu->set_action('引当構成表',   INDUST . 'material/allo_conf_parts_view.php');
    $menu->set_action('引当構成表',   INDUST . 'parts/allocate_config/allo_conf_parts_Main.php');
    
    //////////// リクエストオブジェクトの取得
    $request = new Request();
    //////////// リザルトのインスタンス生成
    $result = new Result();
    
    //////////// セッション オブジェクトの取得 (2005/12/08 Add)
    ///// メニュー切替用 current_menu のデータチェック ＆ 設定 (ModelとControllerで使用する)
    $session = new Session();
    $current_menu = $request->get('current_menu');
    if ($current_menu == '') {
        if ($session->get_local('current_menu') == '') {
            $current_menu = 'list';         // 指定がない場合は一覧表を表示(特に初回)
        } else {
            $current_menu = $session->get_local('current_menu');
        }
    }
    if ($current_menu == 'TimeEdit') {      // 時間の修正画面はセッションに保存しない
        $session->add_local('current_menu', 'EndList');
    } else {
        $session->add_local('current_menu', $current_menu);
    }
    $request->add('current_menu', $current_menu);
    
    //////////// ビジネスモデル部のインスタンス生成
    $model = new PartsPickupLinear_Model($request);
    
    //////////// コントローラー部のインスタンス生成
    $controller = new PartsPickupLinear_Controller($menu, $request, $result, $model);
    
    //////////// Clientへ出力[show()]
    $controller->display($menu, $request, $result, $model);
}
main();

ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
