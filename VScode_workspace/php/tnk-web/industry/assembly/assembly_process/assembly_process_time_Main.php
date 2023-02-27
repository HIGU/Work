<?php
//////////////////////////////////////////////////////////////////////////////
// 組立工程の作業工数 (着手・完了時間) 集計用  Client interface 部          //
//                                              MVC Controller の Main 部   //
// Copyright (C) 2005-2016 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/09/30 Created   assembly_process_time_Main.php                      //
// 2005/10/21 Session Classを使用し showMenu の初期値を取得するように変更   //
// 2005/11/18 クッキーのgroup_noは他のメニューで不具合が出るためDSgroup_noへ//
//            移行するロジックを追加。 同時修正はassembly_process_time.js   //
// 2006/05/19 set_action('登録工数照会' を追加 計画入力時に登録工数照会     //
// 2007/03/24 material/allo_conf_parts_view.php →                          //
//                           parts/allocate_config/allo_conf_parts_Main.php //
// 2007/06/18 tnk_func.php のrequire を追加 Uround()の使用のため            //
// 2016/12/09 set_action('不適合報告書' を追加 製品番号クリックで呼び出し   //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
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
require_once ('../../../tnk_func.php');                 // day_off(), date_offset() で使用
require_once ('assembly_process_time_Controller.php');  // MVC の Controller部
require_once ('assembly_process_time_Model.php');       // MVC の Model部
access_log();                               // Script Name は自動取得

///// Main部の main() function
function main()
{
    ///// TNK 共用メニュークラスのインスタンスを作成
    $menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
    
    ////////////// サイト設定
    $menu->set_site(INDEX_INDUST, 3);           // site_index=INDEX_INDUST(生産メニュー) site_id=3(組立指示メニュー)999(サイトを開く)
    ////////////// リターンアドレス設定
    // $menu->set_RetUrl(EQUIP_MENU);              // 通常は指定する必要はない
    
    //////////// リクエストオブジェクトの取得
    $request = new Request();
    if ($request->get('group_no') != '') {
        setcookie('group_no', '', time() - 3600, '/');  // 旧クッキーデータを削除(１時間前にする)
        setcookie('DSgroup_no', $request->get('group_no'), time() + 630720000, '/');  // 新DSgroup_noを20年間有効でセット
    } elseif ($request->get('DSgroup_no') == '') {
        $request->add('group_no', '1');    // 初期値を設定
    } else {
        $request->add('group_no', $request->get('DSgroup_no')); // データを移行する (最終運用形態)
    }
    
    //////////// リザルトのインスタンス生成
    $result = new Result();
    
    //////////// セッション オブジェクトの取得 (2005/10/21 Add)
    ///// メニュー切替用 showMenu のデータチェック ＆ 設定 (ModelとControllerで使用する)
    $session = new Session();
    $showMenu = $request->get('showMenu');
    if ($showMenu == '') {
        if ($session->get_local('showMenu') == '') {
            $showMenu = 'StartList';       // 指定がない場合は一覧表を表示(特に初回)
        } else {
            $showMenu = $session->get_local('showMenu');
        }
    }
    $session->add_local('showMenu', $showMenu);
    $request->add('showMenu', $showMenu);
    
    //////////// ビジネスモデル部のインスタンス生成
    $model = new AssemblyProcessTime_Model($request);
    
    //////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
    $title = "組立 指示 メニュー&nbsp;&nbsp;&nbsp;<span style='color:blue;'>" . $model->getGroupName($request->get('group_no')) . '</span>';
    $menu->set_title($title);
    //////////// 呼出先のaction名とアドレス設定
    // $menu->set_action('引当構成表',   INDUST . 'material/allo_conf_parts_view.php');
    $menu->set_action('引当構成表',   INDUST . 'parts/allocate_config/allo_conf_parts_Main.php');
    $menu->set_action('登録工数照会',   INDUST . 'assembly/assembly_time_show/assembly_time_show_Main.php');
    $menu->set_action('不適合報告書',   INDUST . 'custom_attention/claim_disposal_Main.php');
    
    //////////// コントローラー部のインスタンス生成
    $controller = new AssemblyProcessTime_Controller($menu, $request, $result, $model);
    
    //////////// Clientへ出力[show()]
    $controller->display($menu, $request, $result, $model);
}
main();

ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
