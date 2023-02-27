<?php
//////////////////////////////////////////////////////////////////////////////
// 間接費配賦率 照会 main部 assemblyRate_actAllocate_Main.php               //
//                          (旧 indirect_cost_allocate.php)                 //
// Copyright (C) 2007-2014 Norihisa.Ooya usoumu@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2007/12/06 Created  assemblyRate_reference_Main.php                      //
// 2007/12/13 余分なfontタグの削除 コメントの位置調整                       //
// 2007/12/29 日付の初期値の設定を追加                                      //
//            前画面に戻る時決算処理の対象年月か前画面で選択した日付を返す  //
//            ように変更                                                    //
// 2008/01/10 表示部の余分なタグの削除                                      //
// 2008/05/09 表示項目・サイズの微調整                                      //
// 2009/04/10 新しくリニア修理部門（559）を追加                             //
// 2010/02/04 製造経費の取り込みとサービス割合の製造経費の配賦を行わないと  //
//            処理が出来ないように変更                                      //
// 2010/03/03 上の条件が少しおかしかったので調整                            //
//            期年月の表示を調整。substrの後に+1-1して数字にして0を消す     //
// 2010/12/09 税務調査指摘により、リニア修理(559)を削除 2010/12～           //
// 2011/06/22 format_date系をtnk_funcに移動のためこちらを削除               //
// 2014/04/11 2014/04より組織変更の為、各部を調整                           //
//////////////////////////////////////////////////////////////////////////////
//ini_set('error_reporting', E_ALL || E_STRICT);
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
session_start();                                 // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');             // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');             // TNK に依存する部分の関数を require_once している
require_once ('../../MenuHeader.php');           // TNK 全共通 menu class
require_once ('../../ControllerHTTP_Class.php'); // TNK 全共通 MVC Controller Class

main();

function main()
{
    ////////// TNK 共用メニュークラスのインスタンスを作成
    $menu = new MenuHeader(0);                       // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
       
    ////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
    $menu->set_title('間接費配賦率の照会');
    
    $request = new Request;
    $result  = new Result;

    //request_check($request, $result, $menu);         // 処理の分岐チェック

    $request->add('view_flg', '照会');

    ////////// HTMLファイル出力
    display($menu, $request, $result);               // 画面表示
}

////////////// 画面表示
function display($menu, $request, $result)
{       
    ////////// ブラウザーのキャッシュ対策用
    $uniq = 'id=' . $menu->set_useNotCache('target');
    
    ////////// メッセージ出力フラグ
    $msg_flg = 'site';

    ob_start('ob_gzhandler');                        // 出力バッファをgzip圧縮
    
    ////////// HTML Header を出力してキャッシュを制御
    $menu->out_html_header();

    ////////// Viewの処理
    require_once ('assemblyRate_actAllocate_View.php');

    ob_end_flush(); 
}
