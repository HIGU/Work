<?php
////////////////////////////////////////////////////////////////////////////////
// 食堂メニュー予約                                                           //
//                               Client interface  MVC Controller の Main 部  //
// Changed history                                                            //
// 2022/04/29 Created meal_appli_ViewMenuSelect.php                           //
// 2022/05/07 Release.                                                        //
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

//class meal_appli_Model
require_once ('meal_appli_Model.php');        // MVC の Model部
//class meal_appli_Controller
require_once ('meal_appli_Controller.php');   // MVC の Controller部

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
    $menu->set_title('食堂メニュー予約');
    
    //////////// 呼出し元のページを維持
    $menu->set_retGET('page_keep', 'on');
    
    //////////// リクエストオブジェクトの取得
    $request = new Request();
    
    //////////// リザルトのインスタンス生成
    $result = new Result();
    
    //////////// セッション オブジェクトの取得
    $session = new Session();
    
    //////////// ログインユーザーのUID取得
    $login_uid = $session->get('User_ID');
    
    //////////// ブラウザ名とバージョンをチェック
    if( $login_uid == '300667' ) {
//    if( $login_uid == '300667' || $login_uid == '300144') {
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
                break;
        }
        // ブラウザ名とバージョンの表示 echo $h_agent
        if( $h_agent != "MSIE 7.0") {
            $request->add('debug', 'on');   // デバッグON ※リリース時は、コメント化
        }
    }
    $debug = $request->get('debug');   // デバッグフラグ取得
    
    $menu_name[0] = array("menu1", "menu2",      "menu3");  // メニュー
    $menu_name[1] = array("定食",  "丼・カレー", "麺類");   // 表示名
    
    //////////// ビジネスモデル部のインスタンス生成
    $model = new meal_appli_Model($request, $login_uid, $menu_name);
    
    //////////// コントローラー部のインスタンス生成
    $controller = new meal_appli_Controller($request, $model);
    
    //////////// Clientへ出力[show()]
    $controller->display($menu, $request, $result, $model);
}

main();

ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
