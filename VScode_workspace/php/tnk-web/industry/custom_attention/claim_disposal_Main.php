<?php
//////////////////////////////////////////////////////////////////////////////
// 不適合処置連絡書照会 メイン claim_disposal_Main.php                      //
// Copyright (C) 2013-2016 Norihisa.Ooya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2013/01/24 Created  claim_disposal_Main.php                              //
// 2013/01/30 不適合処置と注意点をメニューで分割した                        //
// 2013/05/09 前方一致検索へ切替の為、変更を行った。                        //
// 2016/12/09 他メニュー（組立指示メニュー）からの呼び出しに対して          //
//            リターンがうまくいかないので仕掛け(various_referer)を設定 大谷//
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
session_start();                                 // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');             // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');             // TNK に依存する部分の関数を require_once している
require_once ('../../MenuHeader.php');           // TNK 全共通 menu class
require_once ('../../ControllerHTTP_Class.php'); // TNK 全共通 MVC Controller Class
access_log();                                    // Script Name は自動取得

/////////////// 受け渡し変数の保管
if (isset($_POST['assy_no'])) {
    $_SESSION['assy_no'] = $_POST['assy_no'];                 // 製品番号をセッションに保存
} elseif (isset($_REQUEST['assy_no'])) {
    $_SESSION['assy_no'] = $_REQUEST['assy_no'];                 // 製品番号をセッションに保存
}
if ( isset($_SESSION['assy_no']) ) {
    $assy_no = $_SESSION['assy_no'];
} else {
    $assy_no = '';
}
if (isset($_POST['various_referer'])) {
    $_SESSION['various_referer'] = $_POST['various_referer']; // リターンアドレスのフラグをセッションに保存
} elseif (isset($_REQUEST['various_referer'])) {
    $_SESSION['various_referer'] = $_REQUEST['various_referer']; // リターンアドレスのフラグをセッションに保存
}

main();

function main()
{
    ///// TNK 共用メニュークラスのインスタンスを作成
    $menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
    //////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
    $menu->set_title('不適合処置連絡書の照会');
    //////////// 戻先へのGETデータ設定
    $menu->set_retGET('page_keep', 'On');    
  
    $request = new Request;
    $result  = new Result;
    
    $request->add('assy_no', $_SESSION['assy_no']);
    
    ////////////// リターンアドレス設定
    //$menu->set_RetUrl($_SESSION['various_referer'] . '?wage_ym=' . $request->get('wage_ym'));             // 通常は指定する必要はない
    ////////////// リターンアドレス設定 他のプログラムからの呼び出しと区別する為
    if ($_SESSION['various_referer'] == 'form') {
        $menu->set_RetUrl('http:' . WEB_HOST . 'industry/custom_attention/claim_disposal_form.php');             // 通常は指定する必要はない
    }
    $_SESSION['various_referer'] == 'off';
    
    get_claim_master($result, $request);                          // 各種データの取得
    
    request_check($request, $result, $menu);           // 処理の分岐チェック
    
    outViewListHTML($request, $menu, $result);    // HTML作成
    
    display($menu, $request, $result);          // 画面表示
}

////////////// 画面表示
function display($menu, $request, $result)
{       
    ////////// ブラウザーのキャッシュ対策用
    $uniq = 'id=' . $menu->set_useNotCache('target');
    
    ////////// メッセージ出力フラグ
    $msg_flg = 'site';

    ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
    
    ////////// HTML Header を出力してキャッシュを制御
    $menu->out_html_header();
 
    ////////// Viewの処理
    require_once ('claim_disposal_List.php');

    ob_end_flush(); 
}

////////////// 処理の分岐を行う
function request_check($request, $result, $menu)
{
    $ok = true;
    if ($ok) {
        ////// データの初期化
        $request->add('del', '');
        $request->add('entry', '');
        $request->add('number', '');
        $request->add('group_no', '');
        $request->add('group_name', '');
        get_claim_master($result, $request);    // 各種データの取得
    }
}

////////////// 表示用(一覧表)の不適合処置連絡書データをSQLで取得
function get_claim_master ($result, $request)
{
    $assy_no = $request->get('assy_no');
    $query_g = "
        SELECT  assy_no                 AS 製品番号     -- 0
            ,   midsc                   AS 製品名       -- 1
            ,   publish_date            AS 発行日       -- 2
            ,   publish_no              AS 発行番号     -- 3
            ,   claim_name              AS 件名         -- 4
        FROM
            claim_disposal_details
        LEFT OUTER JOIN
            miitem
        ON assy_no = mipn
        WHERE assy_no LIKE '{$assy_no}%'
        ORDER BY
            mipn,publish_date
    ";

    $res_g = array();
    if (($rows_g = getResultWithField2($query_g, $field_g, $res_g)) <= 0) {
        $_SESSION['s_sysmsg'] = "不適合処置連絡書の登録がありません！";
        $field_g[0]   = "製品番号";
        $field_g[1]   = "製品名";
        $field_g[2]   = "発行日";
        $field_g[3]   = "発行番号";
        $field_g[4]   = "件名";
        $result->add_array2('res_g', '');
        $result->add_array2('field_g', $field_g);
        $result->add('num_g', 5);
        $result->add('rows_g', '');
    } else {
        $num_g = count($field_g);
        $result->add_array2('res_g', $res_g);
        $result->add_array2('field_g', $field_g);
        $result->add('num_g', $num_g);
        $result->add('rows_g', $rows_g);
    }
}

////////////// 不適合処置連絡書照会画面のHTMLの作成
function getViewHTMLbody($request, $menu, $result)
{
    $listTable = '';
    $listTable .= "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>\n";
    $listTable .= "<html>\n";
    $listTable .= "<head>\n";
    $listTable .= "<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>\n";
    $listTable .= "<meta http-equiv='Content-Style-Type' content='text/css'>\n";
    $listTable .= "<meta http-equiv='Content-Script-Type' content='text/javascript'>\n";
    $listTable .= "<style type='text/css'>\n";
    $listTable .= "<!--\n";
    $listTable .= "th {\n";
    $listTable .= "    background-color:   blue;\n";
    $listTable .= "    color:              yellow;\n";
    $listTable .= "    font-size:          10pt;\n";
    $listTable .= "    font-weight:        bold;\n";
    $listTable .= "    font-family:        monospace;\n";
    $listTable .= "}\n";
    $listTable .= "a:hover {\n";
    $listTable .= "    background-color:   blue;\n";
    $listTable .= "    color:              white;\n";
    $listTable .= "}\n";
    $listTable .= "    a:active {\n";
    $listTable .= "    background-color:   gold;\n";
    $listTable .= "    color:              black;\n";
    $listTable .= "}\n";
    $listTable .= "a {\n";
    $listTable .= "    color:   blue;\n";
    $listTable .= "}\n";
    $listTable .= "-->\n";
    $listTable .= "</style>\n";
    $listTable .= "</head>\n";
    $listTable .= "<body>\n";
    $listTable .= "<center>\n";
    $listTable .= "    <form name='entry_form' action='clame_disposal_Main.php' method='post' target='_parent'>\n";
    $listTable .= "        <table bgcolor='#d6d3ce' width='150' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "            <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
    $listTable .= "        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "        <THEAD>\n";
    $listTable .= "            <!-- テーブル ヘッダーの表示 -->\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <td width='100%' bgcolor='#ffffc6' align='center' colspan='6' nowrap>\n";
    $listTable .= "                <B>不適合処置連絡書一覧</B>\n";
    $listTable .= "                </td>\n";
    $listTable .= "            </tr>\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <th class='winbox' nowrap width='15'>No</th>        <!-- 行ナンバーの表示 -->\n";
    $field_g = $result->get_array2('field_g');
    for ($i=0; $i<$result->get('num_g'); $i++) {        // フィールド数分繰返し\n";
        $listTable .= "            <th class='winbox' nowrap>". $field_g[$i] ."</th>\n";
    }
    $listTable .= "            </tr>\n";
    $listTable .= "        </THEAD>\n";
    $listTable .= "        <TFOOT>\n";
    $listTable .= "            <!-- 現在はフッターは何もない -->\n";
    $listTable .= "        </TFOOT>\n";
    $listTable .= "        <TBODY>\n";
    for ($r=0; $r<$result->get('rows_g'); $r++) {
        $listTable .= "        <tr>\n";
        $listTable .= "            <td class='winbox' nowrap align='right'>    <!-- 削除変更用に入力欄にコピー  -->\n";
        $cnum = $r + 1;
        $listTable .= "                ". $cnum ."\n";
        $listTable .= "            </td>\n";
        $res_g = $result->get_array2('res_g');
        for ($i=0; $i<$result->get('num_g'); $i++) {    // レコード数分繰返し
            switch ($i) {
                case 0:                                 // 製品番号
                    if ($res_g[$r][$i] == '') {
                        $res_g[$r][$i] = '　';
                    }
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". $res_g[$r][$i] ."</div></td>\n";
                break;
                case 1:                                 // 製品名
                    if ($res_g[$r][$i] == '') {
                        $res_g[$r][$i] = '　';
                    }
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". $res_g[$r][$i] ."</div></td>\n";
                break;
                case 2:                                 // 発行日
                    $listTable .= "<td class='winbox' nowrap align='center'><div class='pt9'>". format_date($res_g[$r][$i]) ."</div></td>\n";
                break;
                case 3:                                 // 発行番号
                    $listTable .= "<td class='winbox' nowrap align='center'><div class='pt9'>\n";
                    $listTable .= "    <a href='../claim_disposal_View.php?assy_no=". $request->get('assy_no') ."&c_assy_no=". $res_g[$r][0] ."&publish_no=". $res_g[$r][$i] ."' target='_parent' style='text-decoration:none;'>\n";
                    $listTable .= "     ". $res_g[$r][$i] ."\n"; 
                    $listTable .= "</div></td>\n";
                break;
                case 4:                                 // 件名
                    if ($res_g[$r][$i] == '') {
                        $res_g[$r][$i] = '　';
                    }
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". $res_g[$r][$i] ."</div></td>\n";
                break;
                default:
                break;
            }
        }
        $listTable .= "        </tr>\n";
    }
    $listTable .= "        </TBODY>\n";
    $listTable .= "        </table>\n";
    $listTable .= "            </td></tr>\n";
    $listTable .= "        </table> <!----------------- ダミーEnd ------------------>\n";
    $listTable .= "    </form>\n";
    $listTable .= "</center>\n";
    $listTable .= "</body>\n";
    $listTable .= "</html>\n";
    return $listTable;
}

////////////// 不適合処置連絡書照会画面のHTMLを出力
function outViewListHTML($request, $menu, $result)
{
    $listHTML = getViewHTMLbody($request, $menu, $result);
    $request->add('view_flg', '照会');
    ////////// HTMLファイル出力
    $file_name = "list/claim_disposal_List-{$_SESSION['User_ID']}.html";
    $handle = fopen($file_name, 'w');
    fwrite($handle, $listHTML);
    fclose($handle);
    chmod($file_name, 0666);    // fileを全てrwモードにする
}
