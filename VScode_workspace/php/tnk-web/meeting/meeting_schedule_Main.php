<?php
//////////////////////////////////////////////////////////////////////////////
// 全社共有 打合せ(会議)スケジュール表の照会・メンテナンス                  //
//                             Client interface  MVC Controller の Main 部  //
// Copyright (C) 2005-2021 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/11/01 Created   meeting_schedule_Main.php                           //
// 2009/12/17 照会・印刷画面追加の為調整                               大谷 //
// 2010/03/11 部課長用スケジュール作成のためテスト変更                 大谷 //
// 2021/06/10 カレンダー移動用の年月を受け渡し                         大谷 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('mbstring.http_output', 'UTF-8');        // ajaxで使用する場合
// ini_set('error_reporting', E_STRICT);               // E_STRICT=2048(php5) E_ALL=2047 debug 用
ini_set('error_reporting', E_ALL);                  // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');                  // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');     // zend 1.X コンパチ php4の互換モード
ob_start('ob_gzhandler');                           // 出力バッファをgzip圧縮
session_start();                                    // ini_set()の次に指定すること Script 最上行

require_once ('../function.php');                   // access_log()等で使用
require_once ('../MenuHeader.php');                 // TNK 全共通 menu class
require_once ('../ControllerHTTP_Class.php');       // TNK 全共通 MVC Controller Class
require_once ('../CalendarClass.php');              // カレンダークラス スケジュールで使用
require_once ('meeting_schedule_Controller.php');   // MVC の Controller部
require_once ('meeting_schedule_Model.php');        // MVC の Model部
access_log();                                       // Script Name は自動取得

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
    $menu->set_title('全社共有 会議(打合せ)のスケジュール表 照会・編集');
    
    //////////// リクエストオブジェクトの取得
    $request = new Request();
    
    //////////// リザルトのインスタンス生成
    $result = new Result();
    
    //////////// セッション オブジェクトの取得
    $session = new Session();
    
    // 認証なしで登録済みの場合にリクエストでユーザーを変更できる
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
    ///// カレンダー移動用の年月が設定されているかチェック
    if ($request->get('ind_ym') == 99) {
        // 初期値(本日)を設定
        $request->add('ind_ym', date('Ym'));
        $_SESSION['ind_ym'] = $request->get('ind_ym');
    } elseif ($request->get('ind_ym') == '') {
        // 初期値(本日)を設定
        $request->add('ind_ym', date('Ym'));
    } else {
        $_SESSION['ind_ym'] = $request->get('ind_ym');
    }
    ///// 一覧表示時の期間(1日間,7日間,14,28...)
    if ($request->get('listSpan') == '') {
        if ($session->get_local('listSpan') != '') {
            $request->add('listSpan', $session->get_local('listSpan'));
        } else {
            $request->add('listSpan', '0');             // 初期値(本日のみ)
        }
    }
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
