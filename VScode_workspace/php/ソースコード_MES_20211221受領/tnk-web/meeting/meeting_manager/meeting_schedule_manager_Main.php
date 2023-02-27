<?php
//////////////////////////////////////////////////////////////////////////////
// 部課長用会議スケジュール照会               Client interface 部           //
//                                              MVC Controller の Main 部   //
// Copyright (C) 2010 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2010/03/11 Created   meeting_schedule_manager_Main.php                   //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
ini_set('error_reporting', E_ALL);          // E_STRICT=2048(php5) E_ALL=2047 debug 用
ini_set('memory_limit', '128M');            // ガントチャート用に使用メモリーを増やす
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード
// ini_set('implicit_flush', '0');             // echo print で flush させない(遅くなるため) CLI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 WEB CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../MenuHeader.php');               // TNK 全共通 menu class
require_once ('../../tnk_func.php');                 // day_off(), date_offset() で使用
require_once ('../../ControllerHTTP_Class.php');     // TNK 全共通 MVC Controller Class
require_once ('../../function.php');                 // access_log()等で使用
require_once ('meeting_schedule_manager_Controller.php'); // MVC の Controller部
require_once ('meeting_schedule_manager_Model.php');      // MVC の Model部
require_once ('../../../jpgraph.php');               // Common Graph class
require_once ('../../../jpgraph_gantt_hour.php');         // GanttChart Graph class
access_log();                               // Script Name は自動取得

//// Main部 の main()定義
function main()
{
    ///// TNK 共用メニュークラスのインスタンスを作成
    $menu = new MenuHeader(-1);                     // 認証チェック -1=認証なし, 0=一般以上
    
    ////////////// サイト設定
    // $menu->set_site(INDEX_INDUST, 1);            // サイト設定なし
    ////////////// リターンアドレス設定
    // $menu->set_RetUrl(EQUIP_MENU);               // 通常は指定する必要はない
    
    //////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
    $menu->set_title('部課長用 会議(打合せ)のスケジュール表 照会');
    
    //////////// リクエストオブジェクトの取得
    $request = new Request();
    
    //////////// リザルトのインスタンス生成
    $result = new Result();
    
    //////////// セッション オブジェクトの取得
    $session = new Session();
    
    // 認証なしで登録済みの場合にリクエストでユーザーを変更できる
    if ($session->get('User_ID') == '000000') {
        if ($request->get('calUid') == '') {
            $_SESSION["s_sysmsg"] = "あなたは、許可されたユーザーではありません。";
            header('Location: ' . H_WEB_HOST);
            exit();
        }
    }
    if ($session->get('User_ID') == '000000') {
        if ($request->get('calUid') != '') {
            $session->add('User_ID', $request->get('calUid'));
            $menu->set_auth_chk(-1);
        }
    }
    ///// カレントの年月日が設定されているかチェック
    if ($request->get('year') == '' || $request->get('month') == '' || $request->get('day') == '') {
        // 初期値(本日)を設定
        $request->add('year', date('Y')); $request->add('month', date('m')); $request->add('day', date('d'));
    }
    $yy_temp = str_pad($request->get('year'), 4, '0', STR_PAD_LEFT);
    $mm_temp = str_pad($request->get('month'), 2, '0', STR_PAD_LEFT);
    $dd_temp = str_pad($request->get('day'), 2, '0', STR_PAD_LEFT);
    $request->add('year', $yy_temp); $request->add('month', $mm_temp); $request->add('day', $dd_temp);
    ///// 一覧表示時の期間(1日間,7日間,14,28...)
    if ($request->get('listSpan') == '') {
        if ($session->get_local('listSpan') != '') {
            $request->add('listSpan', $session->get_local('listSpan'));
        } else {
            $request->add('listSpan', '0');             // 初期値(本日のみ)
        }
    }
    $session->add_local('listSpan', $request->get('listSpan')); // セッションデータも変更
    //////////// ビジネスモデル部のインスタンス生成
    $model = new MeetingSchedule_Model($request);
    
    //////////// コントローラー部のインスタンス生成
    $controller = new MeetingSchedule_Controller($request, $model);
    
    //////////// Clientへ出力[show()]
    $controller->display($menu, $request, $result, $model);
}
main();

ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
