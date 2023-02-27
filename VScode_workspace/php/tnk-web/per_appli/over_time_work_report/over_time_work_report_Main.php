<?php
////////////////////////////////////////////////////////////////////////////////
// 定時間外作業申告                                                           //
//                               Client interface  MVC Controller の Main 部  //
// Copyright (C) 2021-2021 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2021/10/20 Created over_time_work_report_Main.php                          //
// 2021/11/01 Release.                                                        //
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

//class over_time_work_report_Model
require_once ('over_time_work_report_Model.php');        // MVC の Model部
//class over_time_work_report_Controller
require_once ('over_time_work_report_Controller.php');   // MVC の Controller部

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
    $menu->set_title('定時間外作業申告');
    
    //////////// 呼出し元のページを維持
    $menu->set_retGET('page_keep', 'on');
    
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
    
    //////////// ビジネスモデル部のインスタンス生成
    if( $session->get('User_ID') == '300667' ) {
//    if( $session->get('User_ID') == '300667' || $session->get('User_ID') == '300144') {
//        $request->add('debug', 'on');   // デバッグON ※リリース時は、コメント化
//    if(getCheckAuthority(69) ) { //  <!-- 69:総務課員の社員番号（管理部と総務課）-->
        $array_agent = array("MSIE","Chrome","Firefox","Opera","Safari");
        $h_agent = $_SERVER['HTTP_USER_AGENT'];
        $agent;
        for($i=0; $i<5; $i++){
            if(strlen(strpos($h_agent,$array_agent[$i]))>0){
                $agent = $array_agent[$i];
                break;
            }
        }
        switch($agent) {
            case "MSIE":
                $h_agent = substr($h_agent,strpos($h_agent,$agent),strlen($h_agent));
                $h_agent = substr($h_agent,0,strpos($h_agent,";"));
                break;
            case "Chrome":
                $h_agent = substr($h_agent,strpos($h_agent,$agent),strlen($h_agent));
                $h_agent = substr($h_agent,0,strpos($h_agent," "));
                break;
            case "Firefox":
                $h_agent = substr($h_agent,strpos($h_agent,$agent),strlen($h_agent));
                break;
            case "Safari":
                $h_agent = substr($h_agent,strpos($h_agent,"Version/")+strlen("Version/"),strlen($h_agent));
                $h_agent = $agent." ".substr($h_agent,0,strpos($h_agent," ".$agent));
                break;
            case "Opera":
                $h_agent = substr($h_agent,strpos($h_agent,"Version/")+strlen("Version/"),strlen($h_agent));
                $h_agent = $agent." ".$h_agent;
                break;
            default:
        }
        //ブラウザ名とバージョンの表示 echo $h_agent
        if( $h_agent != "MSIE 7.0") {
            $request->add('debug', 'on');   // デバッグON ※リリース時は、コメント化
        }
        if( ($login_uid = $request->get('login_uid')) != "" ) {
            $model = new over_time_work_report_Model($request, $login_uid);
//            $model->TEST();   // 事前申請のお知らせ   デモ ※リリース時は、コメント化
//            $model->TEST2();  // 報告未入力のお知らせ デモ ※リリース時は、コメント化
//            $model->TEST3();  // 【早出】未報告者をメールする デモ ※リリース時は、コメント化
//            $model->TEST4();  // 【通常】未報告者をメールする デモ ※リリース時は、コメント化
//            $model->TEST5();  // 【早出】事前申請のお知らせ   デモ ※リリース時は、コメント化
//            $model->TEST6();  // 【早出】報告未入力のお知らせ デモ ※リリース時は、コメント化
//            $model->TEST7();  // デモ ※リリース時は、コメント化
        } else {
//            $model = new over_time_work_report_Model($request, '970392');   // 指定可能
            $model = new over_time_work_report_Model($request, $session->get('User_ID'));
//            $model = new over_time_work_report_Model($request, '011061');   // 工場長
//            $model = new over_time_work_report_Model($request, '017850');   // 管理部
//            $model = new over_time_work_report_Model($request, '007528');   // ISO(総務)
//            $model = new over_time_work_report_Model($request, '300055');   // 総務
//            $model = new over_time_work_report_Model($request, '300349');   // 商品管理
//            $model = new over_time_work_report_Model($request, '012980');   // 技術部
//            $model = new over_time_work_report_Model($request, '300098');   // 品質保証
//            $model = new over_time_work_report_Model($request, '300209');   // 技術
//            $model = new over_time_work_report_Model($request, '012394');   // 副工場長（生産部へ変換）
//            $model = new over_time_work_report_Model($request, '010537');   // 製造１
//            $model = new over_time_work_report_Model($request, '300233');   // 製造２
//            $model = new over_time_work_report_Model($request, '016713');   // 生産部
//            $model = new over_time_work_report_Model($request, '300152');   // 生産管理 計画・購買
//            $model = new over_time_work_report_Model($request, '016951');   // 生産管理 資材
//            $model = new over_time_work_report_Model($request, '300331');   // カプラ 標準MA
//            $model = new over_time_work_report_Model($request, '300659');   // カプラ 標準HA
//            $model = new over_time_work_report_Model($request, '015989');   // カプラ 特注
//            $model = new over_time_work_report_Model($request, '017728');   // リニア
        }
    } else {
        $model = new over_time_work_report_Model($request, $session->get('User_ID'));
    }
    
    //////////// コントローラー部のインスタンス生成
    $controller = new over_time_work_report_Controller($request, $model);
    
    //////////// Clientへ出力[show()]
    $controller->display($menu, $request, $result, $model);
}

main();

ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
