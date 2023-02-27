<?php
//////////////////////////////////////////////////////////////////////////////
// AS/400 OBJ/SRC/File 照会・登録・変更 処理                                //
// Copyright (C) 2002-2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2002/12/10 Created  system_as400_file.php                                //
//                      Excel で管理していた物を Tnk Web System へ          //
// 2003/02/23 date("Y/m/d H:m:s") → H:i:s のミス修正                       //
// 2003/02/26 body に onLoad を追加し初期入力個所に focus() させた          //
//    フィールド数の取得方法変更 getResult2() を使用し $num = count();      //
// 2003/03/05 $filename → $_POST['file_name'] のミスを訂正 176行目         //
// 2003/05/01 一覧の分類フィールドをcenterへ 分類=2 プログラム を追加       //
// 2003/07/11 全体のデザインを Windows 風に変更 セルが浮いてるように        //
// 2003/11/05 検索ボタンが押された時は order by file_name ASC を追加        //
//            同上  (PAGE+20)を追加し検索結果の一覧表を多く表示させる       //
// 2003/12/03 一覧表(降順)と入力順(昇順)の時に次頁・前頁にも反映させた      //
// 2004/05/12 サイトメニュー表示・非表示 ボタン追加 menu_OnOff($script)追加 //
// 2004/05/21 page_keep時のas_offsetが offsetになっていたのを修正           //
// 2004/10/13 DB用途に<pre>を試したが既存のデータが対応していないため断念   //
// 2005/02/23 SQL文作成後の $file_name = stripslashes($file_name);を追加    //
//            MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2007/02/07 overflowの制御をPAGE→Postデータ3種類に変更1頁行数を12→20へ  //
// 2007/02/23 addslashes(), stripslashes()を全て外して初期化時の一度だけに  //
// 2007/06/25 as400_file_view を追加 SQLキーワードを大文字に修正(慣例に遵守)//
// 2007/10/19 ショートカットを標準タグへ。E_ALL → E_ALL | E_STRICT へ      //
//             onKeyUp='baseJS.keyInUpper(this);' を必要なフォームへ追加    //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('magic_quotes_gpc', '0');           // PHP_INI_PERDIR 2 php.ini, .htaccess または httpd.confで設定可能なエントリ
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 WEB CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../function.php');           // define.php と pgsql.php を require_once している
require_once ('../MenuHeader.php');         // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(3);                  // 認証チェック0=一般以上 3=admini以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(99, 31);                    // site_index=99(システムメニュー) site_id=60(AS400 file)
////////////// リターンアドレス設定
// $menu->set_RetUrl(SYS_MENU);                // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('AS/400 Object Source File Reference');
//////////// 表題の設定
$menu->set_caption('AS/400 Object & Source & File 一覧');
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('test_template',   SYS . 'log_view/php_error_log.php');

///// POST 変数の初期化
if (isset($_POST['as_sel'])) {
    $as_sel = $_POST['as_sel'];     // POST データで初期化
} else {
    $as_sel = '';                   // 初期化
}
    ///// 以下は php.ini で magic_quotes_gpc=on になっているための初期化 2007/02/23 変更
    ///// pg_escape_string()も付加したがPostgreSQL 8.2.3でstandard_conforming_strings = onにしているため外した。
if (isset($_POST['file_name'])) {
    $file_name = stripslashes($_POST['file_name']); // POST データで初期化
} else {
    $file_name = '';                    // 初期化
}
if (isset($_POST['obj_lib'])) {
    $obj_lib = stripslashes($_POST['obj_lib']);     // POST データで初期化
} else {
    $obj_lib = '';                      // 初期化
}
if (isset($_POST['src_lib'])) {
    $src_lib = stripslashes($_POST['src_lib']);     // POST データで初期化
} else {
    $src_lib = '';                      // 初期化
}
if (isset($_POST['file_note'])) {
    $file_note = stripslashes($_POST['file_note']); // POST データで初期化
} else {
    $file_note = '';                    // 初期化
}

//////////// 一頁の行数
define('PAGE', '20');

//////////// 一覧表の並び順設定
if (isset($_POST['search']) || ($as_sel != '')) {
    $_SESSION['as400_view'] = '追加順';
    $_POST['view'] = 'view';                        // 表示させる
} elseif ( isset($_POST['view']) ) {
    if ($_POST['view'] == '入力順') {
        $_SESSION['as400_view'] = '入力順';
        $_POST['view'] = 'view';                    // 表示させる
    }
    if ($_POST['view'] == '一覧表') {
        $_SESSION['as400_view'] = '追加順';
        $_POST['view'] = 'view';                    // 表示させる
    }
}
if ( !(isset($_SESSION['as400_view'])) ) {
    $_SESSION['as400_view'] = '追加順';             // default 設定
                                                    // 表示させない
}

//////////// 登録レコード数 取得
$table_name = getTableName();
if ($_SESSION['as400_view'] == '入力順') {
    $query = "SELECT count(*) FROM {$table_name}";
} else {
    $query = "SELECT count(*) FROM {$table_name} WHERE last_date IS NOT NULL";
}
$res = array();
if(($rows=getResult($query,$res))>=1){
    $maxrows = $res[0][0];
}

//////////// ページオフセット設定
if ( isset($_POST['forward']) ) {
    $_SESSION['as_offset'] += PAGE;
    if ($_SESSION['as_offset'] >= $maxrows) {
        $_SESSION['as_offset'] = ($maxrows - 1);
    }
} elseif ( isset($_POST['backward']) ) {
    $_SESSION['as_offset'] -= PAGE;
    if ($_SESSION['as_offset'] < 0) {
        $_SESSION['as_offset'] = 0;
    }
} elseif ( isset($_GET['page_keep']) ) {                // 現在のページを維持する GETに注意
    $offset = $_SESSION['as_offset'];
} else {
    // if(!isset($_SESSION['as_offset']))     // 次頁・前頁 以外は初期値に戻す。（他の方法はsystem_menu等の初期メニューで unset($_SESSION['as_offset'])する。
    $_SESSION['as_offset'] = 0;
}
$offset = $_SESSION['as_offset'];

/////////// メンテナンスのレコード追加(登録) as_sel=登録
if($as_sel == "登録"){
    $res_sel = array();
    $query = "SELECT file_name,obj_lib,src_lib,file_note,category FROM as400_file WHERE file_name='"
        . $file_name . "' and obj_lib='" . $obj_lib . "'";
    $rows_sel = getResult($query,$res_sel);
    if($rows_sel <= 0){
        if($_POST['category'] == ""){         ////// category = 分類は NULL を許可
            $query = "insert into as400_file (file_name,obj_lib,src_lib,file_note) values ('"
                . $file_name . "','" . $obj_lib . "','" . $src_lib . "','" . $file_note . "')";
        }else{
            $query = "insert into as400_file (file_name,obj_lib,src_lib,file_note,category) values ('"
                . $file_name . "','" . $obj_lib . "','" . $src_lib . "','" . $file_note . "'," . $_POST['category'] . ")";
        }
        if(query_affected($query) >= 1)     /////// 更新専用クエリーで登録
            $_SESSION['s_sysmsg'] = "<font color='yellow'>[file_name = " . $file_name . "] [obj_lib = " . $obj_lib . "]を登録しました!</font>";
        else
            $_SESSION['s_sysmsg'] = "[file_name = " . $file_name . "] [obj_lib = " . $obj_lib . "]を登録 ERROR";
    }else
        $_SESSION['s_sysmsg'] = "[file_name = " . $file_name . "] [obj_lib = " . $obj_lib . "]は 既に登録されています!";
}
/////////// メンテナンスのレコード削除 as_sel=削除
if($as_sel == "削除"){
    $res_sel = array();
    $query = "SELECT file_name,obj_lib,src_lib,file_note,category FROM as400_file WHERE file_name='"
        . $_SESSION['as_file_name'] . "' and obj_lib='" . $_SESSION['as_obj_lib'] . "'";
    $rows_sel = getResult($query,$res_sel);
    if($rows_sel >= 1){
        $query = "delete FROM as400_file WHERE file_name='" . $_SESSION['as_file_name'] . "' and obj_lib='" . $_SESSION['as_obj_lib'] . "'";
        if(($del_rows = query_affected($query)) >= 1)     /////// 更新専用クエリーで登録
            $_SESSION['s_sysmsg'] = "<font color='yellow'>[file_name = " . $_SESSION['as_file_name'] . "] [obj_lib = " . $_SESSION['as_obj_lib'] . "] のキーで $del_rows:レコード削除しました!</font>";
        else
            $_SESSION['s_sysmsg'] = "[file_name = " . $_SESSION['as_file_name'] . "] [obj_lib = " . $_SESSION['as_obj_lib'] . "] のキーでコード:$del_rows:Error";
    }else
        $_SESSION['s_sysmsg'] = "[file_name = " . $_SESSION['as_file_name'] . "] [obj_lib = " . $_SESSION['as_obj_lib'] . "] のキーでレコードが見つからない!";
    /* セッション変数の削除 */
    unset($_SESSION['as_file_name']);
    unset($_SESSION['as_obj_lib']);
}
/////////// メンテナンスのレコード変更 as_sel=変更
if($as_sel == "変更"){
    $res_sel = array();
    $query = "SELECT file_name,obj_lib,src_lib,file_note,category FROM as400_file WHERE file_name='"
        . $_SESSION['as_file_name'] . "' and obj_lib='" . $_SESSION['as_obj_lib'] . "'";
    $rows_sel = getResult($query,$res_sel);
    if($rows_sel >= 1){
        if($_POST['category'] != ""){
            $query = "update as400_file set file_name='" . $file_name . "',obj_lib='" . $obj_lib
                . "',src_lib='" . $src_lib . "',file_note='" . $file_note . "',category='" . $_POST['category']
                . "' WHERE file_name='" . $_SESSION['as_file_name'] . "' and obj_lib='" . $_SESSION['as_obj_lib'] . "'";
        }else{
            $query = "update as400_file set file_name='" . $file_name . "',obj_lib='" . $obj_lib
                . "',src_lib='" . $src_lib . "',file_note='" . $file_note . "',category=NULL"
                . " WHERE file_name='" . $_SESSION['as_file_name'] . "' and obj_lib='" . $_SESSION['as_obj_lib'] . "'";
        }
        if(($chg_rows = query_affected($query)) >= 1)     /////// 更新専用クエリーで登録
            $_SESSION['s_sysmsg'] = "<font color='yellow'>[file_name = " . $_SESSION['as_file_name'] . "] [obj_lib = " . $_SESSION['as_obj_lib'] . "] のキーで $chg_rows:レコード変更しました!</font>";
        else
            $_SESSION['s_sysmsg'] = "[file_name = " . $_SESSION['as_file_name'] . "] [obj_lib = " . $_SESSION['as_obj_lib'] . "] のキーでコード:$chg_rows:Error";
    }else
        $_SESSION['s_sysmsg'] = "[file_name = " . $_SESSION['as_file_name'] . "] [obj_lib = " . $_SESSION['as_obj_lib'] . "] のキーでレコードが見つからない!";
    /* セッション変数の削除 */
    unset($_SESSION['as_file_name']);
    unset($_SESSION['as_obj_lib']);
}

//////////// 一覧表のデータ取得
$res = array();
if ($_SESSION['as400_view'] == '追加順') {
    if ($file_name != '') {
        ///// SQLのLIKE文でsearchするため最後が\の場合はエスケープの意味で\を追加
        if (substr($file_name, -1, 1) == "\\") {
            $file_temp = $file_name . "\\";
        } else {
            $file_temp = $file_name;
        }
        if ($file_note != '') {         ////// name と note が両方指定されている場合
            $query = "
                SELECT file_name, obj_lib, src_lib, file_note, category FROM {$table_name}
                WHERE file_name LIKE '{$file_temp}%' and file_note LIKE '%{$file_note}%'
                ORDER BY file_name ASC LIMIT 
            ". (PAGE+20);
        } else {                        /////// name だけ指定している場合
            $query = "SELECT file_name,obj_lib,src_lib,file_note,category FROM {$table_name} WHERE file_name like '"
                . $file_temp . "%' order by file_name ASC limit ". (PAGE+20);
        }
    } elseif ($file_note != "") {       /////// note だけ指定している場合
        $query = "SELECT file_name,obj_lib,src_lib,file_note,category FROM {$table_name} WHERE file_note like '%"
            . $file_note . "%' order by file_name ASC limit ". (PAGE+20);
    } else {                            /////// name も note も指定されていない場合は 一覧表と同じようだが条件が違う
        $query = "SELECT file_name,obj_lib,src_lib,file_note,category FROM {$table_name} WHERE last_date IS NOT NULL order by last_date DESC offset $offset limit ".PAGE;
    }
    if (($rows = getResult2($query,$res)) > 0) {        // 連想配列を使わない フィールド数取得のため getResultWithField($query, $field, $result) を使う方法もある $num = count($field);
        $num = count($res[0]);      // フィールド数 取得
    } else {
        $num = 0;                       // 0 で初期化
        // phpinfo(INFO_CONFIGURATION | INFO_VARIABLES);
    }
    // $_POST['view'] = '一覧表';   // 並び順設定へ移動
} else {                                 //////// 一覧表示（入力順）
    $query = "SELECT file_name,obj_lib,src_lib,file_note,category FROM {$table_name} offset $offset limit ".PAGE;
    if (($rows = getResult2($query,$res)) > 0) {        // 連想配列を使わない フィールド数取得のため getResultWithField($query, $field, $result) を使う方法もある $num = count($field);
        $num = count($res[0]);      // フィールド数 取得
    } else {
        $num = 0;                       // 0 で初期化
    }
}

/////////// メンテナンスのためのレコード選択 as_sel=select
if($as_sel == "select"){
    $res_sel = array();
    $query = "SELECT file_name,obj_lib,src_lib,file_note,category FROM {$table_name} WHERE file_name='"
        . $file_name . "' and obj_lib='" . $obj_lib . "'";
    if ( ($rows_sel=getResult2($query,$res_sel)) > 0) {
        $file_name = $res_sel[0][0];
        $obj_lib   = $res_sel[0][1];
        $src_lib   = $res_sel[0][2];
        $file_note = $res_sel[0][3];
        $category  = $res_sel[0][4];
    } else {
        $file_name = '';
        $obj_lib   = '';
        $src_lib   = '';
        $file_note = '';
        $category  = '';
        $_SESSION['s_sysmsg'] = 'データ取得でエラーが発生しました。';
        // phpinfo(INFO_CONFIGURATION | INFO_VARIABLES);
        phpinfo(INFO_VARIABLES);
    }
    /* オリジナル キーフィールドを保存 */
    $_SESSION['as_file_name'] = $file_name;
    $_SESSION['as_obj_lib']   = $obj_lib;
}

////////// オーバーフロー制御
if (isset($rows)) {
    if(isset($_POST['view']) || isset($_POST['forward']) || isset($_POST['backward'])) {
        $overflow = "style='overflow-y:scroll;'";
    } else {
        $overflow = "style='overflow-y:hidden;'";
    }
}
function getTableName()
{
    if ($_SESSION['User_ID'] == '010561') {
        return 'as400_file';
    } else {
        return 'as400_file_view';
    }
}

///////////// HTML Header を出力してブラウザーのキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?php echo $menu->out_title()?></title>
<?php echo $menu->out_site_java()?>
<?php echo $menu->out_css()?>

<script language='JavaScript'>
/* 入力文字が数字かどうかチェック */
function isDigit(str){
    var len=str.length;
    var c;
    for(i=0;i<len;i++){
        c=str.charAt(i);
        if("0">c||c>"9")
            return false;
        }
    return true;
}

/*  アルファベットを大文字変換  */
function file_name_up(obj){
    obj.file_name.value = obj.file_name.value.toUpperCase();
}

/*  未入力フィールドのチェック(必須項目)  */
function edit_chk(obj){
    if(!obj.file_name.value.length){
        alert("[File Name]の入力欄が空白です。");
        obj.file_name.focus();
        return false;
    }
    obj.file_name.value = obj.file_name.value.toUpperCase();
    if(!obj.obj_lib.value.length){
        alert("[OBJ LIB]の入力欄が空白です。");
        obj.obj_lib.focus();
        return false;
    }
    obj.obj_lib.value = obj.obj_lib.value.toUpperCase();
    if(!obj.src_lib.value.length){
        alert("[SRC LIB]の入力欄が空白です。");
        obj.src_lib.focus();
        return false;
    }
    obj.src_lib.value = obj.src_lib.value.toUpperCase();
    if(!obj.file_note.value.length){
        alert("[DB 用途]の入力欄が空白です。");
        obj.file_note.focus();
        return false;
    }
    if(obj.category.value.length){
        if(!isDigit(obj.category.value)){
            alert("[分類]の入力欄に数字以外のデータがあります!");
            obj.category.focus();
            obj.category.select();
            return false;
        }
    }
    return true;
}
// -->
</script>
<style type="text/css">
<!--
th {
    background-color:   yellow;
    color:              blue;
    font-size:              11pt;
    border-style:           solid;
    border-width:           1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
}
td.gb {
    background-color:   #d6d3ce;
    color:              black;
}
.pt12b {
    font-size:      12pt;
    font-weight:    bold;
}
.pt9 {
    font-size:      9pt;
    font-weight:    normal;
}
td {
    font-size: 11pt;
    border-style:           solid;
    border-width:           1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
}
.white {
    color: white;
}
.y_b {
    background-color:   yellow;
    color:              blue;
}
.r_b {
    background-color:   red;
    color:              black;
}
.b_w {
    background-color:   blue;
    color:              white;
}
-->
</style>
</head>
<body onLoad='document.ini_form.file_name.focus()' <?php echo $overflow?>>
    <center>
<?php echo $menu->out_title_border()?>
        
        <table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr>
                <form name='ini_form' method='post' action='<?php echo $menu->out_self()?>' onSubmit='return file_name_up(this)'>
                    <td align='left' nowrap>
                        検索条件を入力して下さい。
                        Object or File
                        <input type='text' name='file_name' size='10' maxlength='8' value='<?php echo $file_name ?>' onKeyUp='baseJS.keyInUpper(this);'>
                        データベース用途
                        <input type='text' name='file_note' size='50' maxlength='40' value='<?php echo $file_note ?>'>
                        <input type='submit' name='search' value='検索' >
                    </td>
                </form>
            </tr>
            <tr>
                <td>
                    <table class='winbox_field' width='100%' align='center' border='1' cellspacing='0' cellpadding='2'>
                        <tr align='center'>
                            <form method='post' action='system_as400_file.php'>
                                <td>
                                    <input type='submit' name='view' value='一覧表' >
                                </td>
                                <td>
                                    <input type='submit' name='view' value='入力順' >
                                </td>
                                <td>
                                    [Object or File] と [データベース用途] を両方指定した場合は and 検索になります。
                                </td>
                                <td>
                                    <input type='submit' name='as_sel' value='追加' >
                                </td>
                            </form>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
    <?php
        if($as_sel == "select"){
            echo "<hr>\n";
            echo "<table width='100%' class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='1' cellpadding='3'>\n";
            echo "  <caption class='pt12b'>AS/400 Object & Source & File メンテナンス \n";
            echo "  </caption>\n";
            echo("  <th nowrap class='b_w'>File Name</th><th nowrap class='b_w'>OBJ LIB</th><th nowrap class='b_w'>SRC LIB</th><th nowrap class='b_w'>DB 用途</th><th nowrap class='b_w'>分類</th><th nowrap class='b_w'>---</th><th nowrap class='b_w'>---</th>\n");
            echo("  </tr>\n");
            echo "  <form method='post' action='system_as400_file.php' onSubmit='return edit_chk(this)'>\n";
            echo("      <td><input type='text' name='file_name' size='11' maxlength='8' value='$file_name' onKeyUp='baseJS.keyInUpper(this);'></td>\n");
            echo("      <td><input type='text' name='obj_lib' size='12' maxlength='10' value='$obj_lib' onKeyUp='baseJS.keyInUpper(this);'></td>\n");
            echo("      <td><input type='text' name='src_lib' size='12' maxlength='10' value='$src_lib' onKeyUp='baseJS.keyInUpper(this);'></td>\n");
            echo("      <td><input type='text' name='file_note' size='80' maxlength='256' value='$file_note'></td>\n");
            echo("      <td><input type='text' name='category' size='5' maxlength='5' value='$category'></td>\n");
            echo("      <td align='center'><input type='submit' name='as_sel' value='変更'></td>\n");
            echo("      <td align='center'><input type='submit' name='as_sel' value='削除'></td>\n");
            echo "  </form>\n";
            echo("  </tr>\n");
            echo("</table>\n");
        }
        if($as_sel == "追加"){
            echo "<hr>\n";
            echo "<table width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='1' cellpadding='3'>\n";
            echo "  <caption class='pt12b'>AS/400 Object & Source & File メンテナンス \n";
            echo "  </caption>\n";
            echo("  <th nowrap class='b_w'>File Name</th><th nowrap class='b_w'>OBJ LIB</th><th nowrap class='b_w'>SRC LIB</th><th nowrap class='b_w'>DB 用途</th><th nowrap class='b_w'>分類</th><th nowrap class='b_w'>追加</th>\n");
            echo("  </tr>\n");
            echo "  <form method='post' action='system_as400_file.php' onSubmit='return edit_chk(this)'>\n";
            echo("      <td><input type='text' name='file_name' size='11' maxlength='8' value='' onKeyUp='baseJS.keyInUpper(this);'></td>\n");
            echo("      <td><input type='text' name='obj_lib' size='12' maxlength='10' value='' onKeyUp='baseJS.keyInUpper(this);'></td>\n");
            echo("      <td><input type='text' name='src_lib' size='12' maxlength='10' value='' onKeyUp='baseJS.keyInUpper(this);'></td>\n");
            echo("      <td><input type='text' name='file_note' size='80' maxlength='256' value=''></td>\n");
            echo("      <td><input type='text' name='category' size='5' maxlength='5' value=''></td>\n");
            echo("      <td align='center'><input type='submit' name='as_sel' value='登録'></td>\n");
            echo "  </form>\n";
            echo("  </tr>\n");
            echo("</table>\n");
        }
        if(isset($_POST['view']) || isset($_POST['forward']) || isset($_POST['backward'])){
            echo "<hr>\n";
            echo "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
            echo "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
            echo "<table width='100%' class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
            echo "  <form method='post' action='system_as400_file.php'>\n";
            echo "  <caption>\n";
            echo "      <font class='pt12b'>", $menu->out_caption(), "</font>\n";
            echo "      <input type='submit' name='backward' value='前頁'>\n";
            echo "      <input type='submit' name='forward' value='次頁'>\n";
            echo "      <font class='pt9'>  分類=2 はプログラム</font>\n";
            echo "  </caption>\n";
            echo "  </form>\n";
            print(" <th nowrap class='y_b'>No</th><th nowrap class='y_b'>File Name</th><th nowrap class='y_b'>OBJ LIB</th><th nowrap class='y_b'>SRC LIB</th><th nowrap class='y_b'>DB 用途</th><th nowrap class='y_b'>分類</th>\n");
            for($r=0;$r<$rows;$r++){
                print("<tr>\n");
                echo "  <form method='post' action='system_as400_file.php'>\n";
                print(" <td align='center'><input type='submit' name='copy' value='" . ($r + $offset + 1) . "'></td>\n");
                echo "      <input type='hidden' name='as_sel' value='select'>\n";
                echo "      <input type='hidden' name='file_name' value='" . $res[$r][0] . "'>\n";
                echo "      <input type='hidden' name='obj_lib' value='" . $res[$r][1] . "'>\n";
                echo "  </form>\n";
                for($n=0;$n<$num;$n++){
                    if($res[$r][$n] == "")
                        echo("<td nowrap align='center'>---</td>\n");
                    else
                        if ($n == 3) {          // DB 用途
                            echo("<td width='100%' align='left'>" . $res[$r][$n] . "</td>\n");
                        } elseif ($n == 4) {    // 分類
                            echo("<td width='40' align='center'>" . $res[$r][$n] . "</td>\n");
                        } else {
                            echo("<td nowrap width='60' align='left'>" . $res[$r][$n] . "</td>\n");
                        }
                }
                print("</tr>\n");
            }
            echo "</table>\n";
            echo "    </td></tr>\n";
            echo "</table> <!----------------- ダミーEnd ------------------>\n";
        }
    ?>
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();     // 出力バッファをgzip圧縮 END
?>
