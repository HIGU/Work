<?php
//////////////////////////////////////////////////////////////////////////////
// 特注カプラ冶具・作業注意点照会 メイン attention_point_Main.php           //
// Copyright (C) 2013-2013 Norihisa.Ooya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2013/01/31 Created  attention_point_Main.php                             //
// 2013/02/06 ファイル表示等を完成させテスト運用                            //
// 2013/04/26 添付ファイルのエラーを解除                                    //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
session_start();                                 // ini_set()の次に指定すること Script 最上行
require_once ('../../MenuHeader.php');           // TNK 全共通 menu class
require_once ('../../function.php');             // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');             // TNK に依存する部分の関数を require_once している
require_once ('../../ControllerHTTP_Class.php'); // TNK 全共通 MVC Controller Class
access_log();                                    // Script Name は自動取得

/////////////// 受け渡し変数の保管
if (isset($_POST['assy_no'])) {
    $_SESSION['assy_no'] = $_POST['assy_no'];                 // 製品番号をセッションに保存
}
if ( isset($_SESSION['assy_no']) ) {
    $assy_no = $_SESSION['assy_no'];
} else {
    $assy_no = '';
}

main();

function main()
{
    ///// TNK 共用メニュークラスのインスタンスを作成
    $menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
    //////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
    $menu->set_title('特注カプラ冶具・作業注意点の照会');
    //////////// 戻先へのGETデータ設定
    $menu->set_retGET('page_keep', 'On');    
  
    $request = new Request;
    $result  = new Result;
    
    $request->add('assy_no', $_SESSION['assy_no']);
    
    ////////////// リターンアドレス設定
    //$menu->set_RetUrl($_SESSION['various_referer'] . '?wage_ym=' . $request->get('wage_ym'));             // 通常は指定する必要はない
    ////////////// リターンアドレス設定
    $menu->set_RetUrl('http:' . WEB_HOST . 'industry/custom_attention/attention_point_form.php');             // 通常は指定する必要はない
    
    get_point_master($result, $request);                          // 各種データの取得
    
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
    require_once ('attention_point_List.php');

    ob_end_flush();
}

////////////// 処理の分岐を行う
function request_check($request, $result, $menu)
{
    $ok = true;
    if ($request->get('temp_file') != '') $ok = attention_point_open($request, $result);
    if ($ok) {
        ////// データの初期化
        $request->add('del', '');
        $request->add('entry', '');
        $request->add('number', '');
        $request->add('group_no', '');
        $request->add('group_name', '');
        get_point_master($result, $request);    // 各種データの取得
    }
}

////////////// 表示用(一覧表)の不適合処置連絡書データをSQLで取得
function get_point_master ($result, $request)
{
    $assy_no = $request->get('assy_no');
    $query_g = "
        SELECT  to_char(last_date, 'YYYY/MM/DD')    AS 登録日時          -- 0
            ,   point_name                          AS 作業内容          -- 1
            ,   point_note                          AS 備考              -- 2
            ,   file_name                           AS ファイル名        -- 3
        FROM
            attention_point_details
        WHERE assy_no = '{$assy_no}'
        ORDER BY
            point_name
    ";

    $res_g = array();
    if (($rows_g = getResultWithField2($query_g, $field_g, $res_g)) <= 0) {
        $_SESSION['s_sysmsg'] .= "注意点の登録がありません！";
        $field_g[0]   = "登録日時";
        $field_g[1]   = "作業内容";
        $field_g[2]   = "備考";
        $field_g[3]   = "ファイル名";
        $result->add_array2('res_g', '');
        $result->add_array2('field_g', $field_g);
        $result->add('num_g', 4);
        $result->add('rows_g', '');
    } else {
        $num_g = count($field_g);
        $result->add_array2('res_g', $res_g);
        $result->add_array2('field_g', $field_g);
        $result->add('num_g', 4);
        $result->add('rows_g', $rows_g);
    }
    $query = "
            SELECT  midsc          AS 製品名                 -- 0
            FROM
                miitem
            WHERE mipn = '{$assy_no}'
        ";
    
    $res = array();
    if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
        $assy_name = '　';
    } else {
        $assy_name = $res[0][0];
    }
    $result->add('assy_name', $assy_name);
}

////////////// ファイル名のリンクが押された時
function attention_point_open($request, $result)
{
    $temp_file = mb_convert_encoding($request->get('temp_file'), 'UTF-8','SJIS');
    $temp_file = "files/" . $temp_file;
    $j_file = basename(mb_convert_encoding($temp_file, 'SJIS', 'UTF-8'));
    $request->add('temp_file', $temp_file);
    $request->add('j_file', $j_file);
    
    $excelfile = $j_file;
    header('Content-Disposition: attachment; filename="' . basename($excelfile) . '"');
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Transfer-Encoding: binary');
    header('Content-Length: ' . filesize($temp_file));
    ob_clean();
    flush();
    readfile($temp_file);
    exit();

    /*
    ob_start();
    $handle = @fopen($temp_file, 'rb');
    clearstatcache();
    $filesize = filesize($temp_file);
    header("Content-Length: $filesize");
    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=\"$j_file\"");
    while (!feof($handle)) {
        $buffer = fread($handle, 1024);
        echo $buffer;
        ob_flush();
        flush();
    }
    fclose($handle);
    */
}
////////////// 不適合処置連絡書照会画面のHTMLの作成
function getViewHTMLbody($request, $menu, $result)
{
    $listTable = '';
    $listTable .= "<!DOCTYPE HTML>\n";
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
    $listTable .= "    <form name='entry_form' action='attention_point_Main.php' method='post' enctype='multipart/form-data' target='_parent'>\n";
    $listTable .= "        <table bgcolor='#d6d3ce' width='150' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "            <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
    $listTable .= "        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "        <THEAD>\n";
    $listTable .= "            <!-- テーブル ヘッダーの表示 -->\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <td width='100%' bgcolor='#ffffc6' align='center' colspan='5' nowrap>\n";
    $listTable .= "                <B>作業注意点一覧</B>\n";
    $listTable .= "                </td>\n";
    $listTable .= "            </tr>\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <td width='100%' bgcolor='#ffffc6' align='center' colspan='5' nowrap>\n";
    $listTable .= "                <B>" . $request->get('assy_no') . "　" . $result->get('assy_name') . "</B>\n";
    $listTable .= "                </td>\n";
    $listTable .= "            </tr>\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <td width='100%' bgcolor='#ffffc6' align='center' colspan='5' nowrap>\n";
    $listTable .= "                <B>" . $request->get('temp_file') ."</B>\n";
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
                case 0:                                 // 登録日時
                    $listTable .= "<td class='winbox' nowrap align='center'><div class='pt9'>". $res_g[$r][$i] ."</div></td>\n";
                break;
                case 1:                                 // 作業内容
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". $res_g[$r][$i] ."</div></td>\n";
                break;
                case 2:                                 // 備考
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". $res_g[$r][$i] ."</div></td>\n";
                break;
                case 3:                                 // ファイル名
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>\n";
                    $listTable .= "     <a href='../attention_point_Main.php?temp_file=". $res_g[$r][$i] ."' target='_parent' style='text-decoration:none;'>\n";
                    $listTable .= "         ". $res_g[$r][$i] ."\n";
                    $listTable .= "     </a>\n";
                    $listTable .= "</div></td>\n";
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
    $file_name = "list/attention_point_List-{$_SESSION['User_ID']}.html";
    $handle = fopen($file_name, 'w');
    fwrite($handle, $listHTML);
    fclose($handle);
    chmod($file_name, 0666);    // fileを全てrwモードにする
}
