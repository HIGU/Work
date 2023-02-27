<?php
//////////////////////////////////////////////////////////////////////////////
// 経費内訳の分析用グラフ作成メニュー  グラフの生成・表示                   //
// Copyright (C) 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2007/10/05 Created   graphCreate_Main.php                                //
// 2007/10/07 グラフの値表示・非表示追加。Y軸１個(共用)・２個(別々)を追加   //
// 2007/10/13 X軸の年月をprot1とprot2別々に設定できるオプションを追加       //
// 2007/11/06 損益グラフ作成メニューを経費内訳グラフ作成メニューへ改造      //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 WEB CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');        // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');        // TNK に依存する部分の関数を require_once している
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
require_once ('../../ControllerHTTP_Class.php');// TNK 全共通 MVC Controller Class
require_once ('graphCreate_Function.php');  // グラフ作成メニュー共用関数
access_log();                               // Script Name は自動取得

////////////// main スタート
function main()
{
    ///// TNK 共用メニュークラスのインスタンスを作成
    $menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
    
    ////////////// サイト設定
    $menu->set_site(INDEX_ACT, 15);              // site_index=(経理メニュー) site_id=15(経費グラフ)999(未定)
    ////////////// リターンアドレス設定
    // $menu->set_RetUrl(SALES_MENU);              // 通常は指定する必要はない
    //////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
    $menu->set_title('経費内訳 分析用 グラフ');
    //////////// 呼出先のaction名とアドレス設定
    // $menu->set_action('日計グラフ',   SALES . 'uriage_graph_all_niti.php');
    
    //////////// リクエストのインスタンスを生成
    $request = new Request();
    //////////// セッションのインスタンスを生成
    $session = new Session();
    //////////// リザルトのインスタンスを生成
    $result = new Result();
    
    //////////// メインコントローラーの実行
    mainController($menu, $request, $session);
    
    //////////// 呼出し元へデータ戻し
    setReturnData($menu, $session);
    
    //////////// グラフ作成
    graphCreate($session, $result);
    
    //////////// 前月・次月のページ制御 データ設定
    setPageData($session->get_local('yyyymm1'), 'yyyymm1', $result);
    setPageData($session->get_local('yyyymm2'), 'yyyymm2', $result);
    
    //////////// ブラウザーのキャッシュ対策用
    $uniq = $menu->set_useNotCache('graphCreate');
    ///////////// HTML Header を出力してブラウザーのキャッシュを制御
    $menu->out_html_header();
    //////////// グラフ表示
    require_once ('graphCreate_View.php');
}
main();
ob_end_flush();                 // 出力バッファをgzip圧縮 END
exit();
?>
