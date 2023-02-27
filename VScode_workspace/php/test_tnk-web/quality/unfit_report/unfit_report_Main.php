<?php
//////////////////////////////////////////////////////////////////////////////
// 全社共有 不適合報告書の照会・メンテナンス                                //
//                             Client interface  MVC Controller の Main 部  //
// Copyright (C) 2008 Norihisa.Ohya usoumu@nitto-kohki.co.jp                //
// Changed history                                                          //
// 2008/05/30 Created   unfit_report_Main.php                               //
// 2008/08/29 masterstで本稼動開始                                          //
//////////////////////////////////////////////////////////////////////////////
// ini_set('mbstring.http_output', 'UTF-8');       // ajaxで使用する場合
// ini_set('error_reporting', E_STRICT);           // E_STRICT=2048(php5) E_ALL=2047 debug 用
ini_set('error_reporting', E_ALL);                 // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');                 // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード
ob_start('ob_gzhandler');                          // 出力バッファをgzip圧縮
session_start();                                   // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');                  // access_log()等で使用
require_once ('../../MenuHeader.php');                // TNK 全共通 menu class
require_once ('../../ControllerHTTP_Class.php');      // TNK 全共通 MVC Controller Class
require_once ('../../CalendarClass.php');             // カレンダークラス スケジュールで使用
require_once ('unfit_report_Controller.php');      // MVC の Controller部
require_once ('unfit_report_Model.php');           // MVC の Model部
access_log();                                      // Script Name は自動取得

//////////////// Main部 の main()定義
function main()
{
    //////////// TNK 共用メニュークラスのインスタンスを作成
    $menu = new MenuHeader(-1);                    // 認証チェック -1=認証なし, 0=一般以上
    
    //////////// サイト設定
    $menu->set_site(70, 71);                    // site_index=70(品質管理メニュー) site_id=71(不適合報告書 照会・作成)
    //////////// リターンアドレス設定
    // $menu->set_RetUrl(EQUIP_MENU);              // 通常は指定する必要はない
    
    //////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
    $menu->set_title('不適合報告書 照会・作成');
    
    //////////// リクエストオブジェクトの取得
    $request = new Request();
    
    //////////// リザルトのインスタンス生成
    $result = new Result();
    
    //////////// セッション オブジェクトの取得
    $session = new Session();
    
    //////////// 認証なしで登録済みの場合にリクエストでユーザーを変更できる
    if ($session->get('User_ID') == '000000') {
        if ($request->get('calUid') != '') {
            $session->add('User_ID', $request->get('calUid'));
            $menu->set_auth_chk(-1);
        }
    }
    //////////// カレントの年月日が設定されているかチェック
    if ($request->get('year') == '' || $request->get('month') == '' || $request->get('day') == '') {
        //////// 初期値(本日)を設定
        $request->add('year', date('Y')); $request->add('month', date('m')); $request->add('day', date('d'));
    }
    //////////// 一覧表示時の期間(1日間,7日間,14,28...)
    if ($request->get('listSpan') == '') {
        if ($session->get_local('listSpan') != '') {
            $request->add('listSpan', $session->get_local('listSpan'));
        } else {
            $request->add('listSpan', '0');                     // 初期値(本日のみ)
        }
    }
    $session->add_local('listSpan', $request->get('listSpan')); // セッションデータも変更
    
    //////////// ビジネスモデル部のインスタンス生成
    $model = new UnfitReport_Model($request);
    
    //////////// コントローラー部のインスタンス生成
    $controller = new UnfitReport_Controller($request, $model);
    
    //////////// Clientへ出力[show()]
    $controller->display($menu, $request, $result, $model);
}
main();

ob_end_flush();                                                 // 出力バッファをgzip圧縮 END
?>
