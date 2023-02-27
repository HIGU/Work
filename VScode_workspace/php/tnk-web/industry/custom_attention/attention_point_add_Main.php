<?php
//////////////////////////////////////////////////////////////////////////////
// 特注カプラ冶具・作業注意点編集 メイン attention_point_add_Main.php       //
// Copyright (C) 2013-2013 Norihisa.Ooya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2013/01/31 Created  attention_point_add_Main.php                         //
// 2013/02/01 登録日時の取得方法をYYYY/MM/DDに変更                          //
// 2013/02/06 表示等の微調整                                                //
//////////////////////////////////////////////////////////////////////////////
//ini_set('error_reporting', E_ALL || E_STRICT);
session_start();                                 // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');             // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');             // TNK に依存する部分の関数を require_once している
require_once ('../../MenuHeader.php');           // TNK 全共通 menu class
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
    $menu->set_title('特注カプラ冶具・作業注意点の編集');
    //////////// 戻先へのGETデータ設定
    $menu->set_retGET('page_keep', 'On');    
  
    $request = new Request;
    $result  = new Result;
    
    $request->add('assy_no', $_SESSION['assy_no']);
    
    ////////////// リターンアドレス設定
    $menu->set_RetUrl('http:' . WEB_HOST . 'industry/custom_attention/attention_point_add_form.php');             // 通常は指定する必要はない
    
    get_point_master($result, $request);        // 各種データの取得
    
    request_check($request, $result, $menu);    // 処理の分岐チェック
    
    outViewListHTML($request, $menu, $result);  // HTML作成
    
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
    require_once ('attention_point_add_View.php');

    ob_end_flush(); 
}

////////////// 処理の分岐を行う
function request_check($request, $result, $menu)
{
    $ok = true;
    if ($request->get('number') != '') $ok = attention_point_copy($request, $result);
    if ($request->get('del') != '') $ok = groupMaster_del($request);
    if ($request->get('entry') != '')  $ok = attention_point_entry($request, $result);
    if ($ok) {
        ////// データの初期化
        $request->add('del', '');
        $request->add('entry', '');
        $request->add('number', '');
        $request->add('assy_name', '');
        $request->add('point_name', '');
        $request->add('point_note', '');
        $request->add('file_name', '');
        $request->add('file_data', '');
        $request->add('last_date', '');
        get_point_master($result, $request);    // 各種データの取得
    }
}

////////////// 追加・変更ロジック (合計レコード数取得前に行う)
function attention_point_entry($request, $result)
{
        $assy_no        = $request->get('assy_no');
        $point_name     = $request->get('point_name');
        $point_note     = $request->get('point_note');
        $file_name      = $_FILES["file_name"]["name"];
        $query = sprintf("SELECT * FROM attention_point_details WHERE assy_no='%s' AND point_name='%s'", $assy_no, $point_name);
        $res_chk = array();
        if ( getResult($query, $res_chk) > 0 ) {    // 登録あり 更新はできないようにする
            $_SESSION['s_sysmsg'] .= "同じ作業内容がすでに登録されています。変更する場合は一度削除してから新規登録してください。";
            $msg_flg = 'alert';
            return false;
        } else {                                    // 登録なし INSERT 新規
            //ファイル重複チェック
            if (is_uploaded_file($_FILES["file_name"]["tmp_name"])) {
                $dir      = 'files/';
                $filelist = scandir($dir);
                foreach($filelist as $file){
                    if(!is_dir($file)){
                        if($file_name==$file){
                            $_SESSION['s_sysmsg'] .="ファイル名が重複しているのでアップロードできません。";
                            $msg_flg = 'alert';
                            return false;
                        }
                    }
                }
            } else {
                $_SESSION['s_sysmsg'] .= "ファイルが選択されていません。";
                $msg_flg = 'alert';
                return false;
            }
            $query = sprintf("INSERT INTO attention_point_details (assy_no, point_name, point_note, file_name, last_date, last_user)
                              VALUES ('%s', '%s', '%s', '%s', CURRENT_TIMESTAMP, '%s')",
                                $assy_no, $point_name, $point_note, $file_name, $_SESSION['User_ID']);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "グループ番号：{$group_no} グループ名：{$group_name}の追加に失敗！";      // .= に注意
                $msg_flg = 'alert';
                return false;
            } else {
                $_SESSION['s_sysmsg'] .= "製品番号：{$assy_no} 作業内容：{$point_name}を追加しました！";    // .= に注意
                if (move_uploaded_file($_FILES['file_name']['tmp_name'], 'files/' . $_FILES['file_name']['name'])) {
                    chmod('files/' . $_FILES['file_name']['name'], 0644);
                    $_SESSION['s_sysmsg'] .= $_FILES['file_name']['name'] . "をアップロードしました。";
                } else {
                    $_SESSION['s_sysmsg'] .= "ファイルのアップロードに失敗しました。";
                }
                return true;
            }
        }
}

////////////// 削除ロジック (合計レコード数取得前に行う)
function groupMaster_del($request)
{
        $assy_no     = $request->get('assy_no');
        $point_name  = $request->get('point_name');
        $deletefiles = $request->get('file_name_cp');
        $dir         = 'files/';
        if(file_exists($dir.$deletefiles)){
            unlink($dir.$deletefiles);
        }
        $query = sprintf("DELETE FROM attention_point_details WHERE assy_no = '%s' AND point_name = '%s'", $assy_no, $point_name);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "製品番号：{$assy_no} 作業内容：{$point_name}の削除に失敗！";   // .= に注意
            $msg_flg = 'alert';
            return false;
        } else {
            $_SESSION['s_sysmsg'] .= "製品番号：{$assy_no} 作業内容：{$point_name}を削除しました！ {$deletefiles}"; // .= に注意
            return true;
        }
}
////////////// 表示用(一覧表)のグループマスターデータをSQLで取得
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

////////////// コピーのリンクが押された時
function attention_point_copy($request, $result)
{
    $res_g = $result->get_array2('res_g');
    $r = $request->get('number');
    $point_name = $res_g[$r][1];
    $point_note = $res_g[$r][2];
    $file_name  = $res_g[$r][3];
    $request->add('point_name', $point_name);
    $request->add('point_note', $point_note);
    $request->add('file_name_cp', $file_name);
}

////////////// グループマスター照会画面のHTMLの作成
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
    $listTable .= "    <form name='entry_form' action='attention_point_add_Main.php' method='post' target='_parent'>\n";
    $listTable .= "        <table bgcolor='#d6d3ce' width='150' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "            <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
    $listTable .= "        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "        <THEAD>\n";
    $listTable .= "            <!-- テーブル ヘッダーの表示 -->\n";
   $listTable .= "            <tr>\n";
    $listTable .= "                <td width='100%' bgcolor='#ffffc6' align='center' colspan='5' nowrap>\n";
    $listTable .= "                <B>特注カプラ冶具・作業注意点一覧</B>\n";
    $listTable .= "                </td>\n";
    $listTable .= "            </tr>\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <td width='100%' bgcolor='#ffffc6' align='center' colspan='5' nowrap>\n";
    $listTable .= "                <B>" . $request->get('assy_no') . "　" . $result->get('assy_name') . "</B>\n";
    $listTable .= "                </td>\n";
    $listTable .= "            </tr>\n";
     $listTable .= "            <tr>\n";
    $listTable .= "                <th class='winbox' nowrap width='10'>No</th>        <!-- 行ナンバーの表示 -->\n";
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
        $listTable .= "                <a href='../attention_point_add_Main.php?number=". $r ."' target='_parent' style='text-decoration:none;'>\n";
        $cnum = $r + 1;
        $listTable .= "                ". $cnum ."\n";
        $listTable .= "                </a>\n";
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

////////////// 賃率照会画面のHTMLを出力
function outViewListHTML($request, $menu, $result)
{
    $listHTML = getViewHTMLbody($request, $menu, $result);
    $request->add('view_flg', '照会');
    ////////// HTMLファイル出力
    $file_name = "list/attention_point_add_List-{$_SESSION['User_ID']}.html";
    $handle = fopen($file_name, 'w');
    fwrite($handle, $listHTML);
    fclose($handle);
    chmod($file_name, 0666);    // fileを全てrwモードにする
}
