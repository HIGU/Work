<?php
//////////////////////////////////////////////////////////////////////////////
// 従業員の各種コードテーブルの保守 経理・組織・人事コード                  //
// Copyright (C) 2002-2008 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2002/09/12 Created   cd_table_mnt.php                                    //
// 2002/09/20 サイトメニューに追加                                          //
// 2003/02/26 body に onLoad を追加し初期入力個所に focus() させた          //
// 2003/04/24 追加の必要な一覧と削除が必要な一覧 ロジックを追加             //
// 2004/04/20 社員番号のチェック＆補正ロジックを JavaScriptで追加           //
// 2004/10/28 user_check()functionを追加し編集出来るユーザーを限定          //
// 2005/10/13 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2006/06/27 追加が必要な一覧表の条件に u.uid NOT LIKE '99%' を追加        //
// 2008/09/17 認証をgetCheckAuthority()に変更                               //
//            26：コードテーブルの保守が行える社員番号                 大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../function.php');           // 前共通ファンクッション
require_once ('../MenuHeader.php');         // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(10, 2);                     // site_index=10(損益メニュー) site_id=2(コードテーブル)
////////////// リターンアドレス設定(絶対指定する場合)
// $menu->set_RetUrl(PL_MENU);                 // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('コードテーブルのメンテナンス');
//////////// 表題の設定
$menu->set_caption('区分を選択し社員番号を入力し実行ボタンを押して下さい。');
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('test_template',   SYS . 'log_view/php_error_log.php');

/////////// 登録・変更 ユーザーのチェック
$uid = $_SESSION['User_ID'];            // ユーザー
function user_check($uid)
{
    if(getCheckAuthority(26)) {
        return TRUE;
    } else {
        $query = "SELECT trim(name) FROM user_detailes WHERE uid = '{$uid}' LIMIT 1";
        if (getUniResult($query, $name) <= 0) $name = '';
        $_SESSION['s_sysmsg'] = "社員番号：{$uid}：{$name}さんではコードテーブルの保守は出来ません！ 管理担当者へ連絡して下さい。";
        return FALSE;
    }
}

///// Controller 部
if (isset($_POST['uid'])) {     // editでNG IEでテキストフィールド(uid)でenterした時にuidしかPOSTされない為
    if (!isset($_POST['cd_sel'])) {
        $_SESSION['s_sysmsg'] = '追加・変更・削除の区分を選択して下さい！';
    }
}
if (!isset($_POST['cd_sel'])) {
    $_POST['cd_sel'] = '';          // セットされていない時の初期化
}
if (!isset($_POST['uid'])) {        // セットされていない時の初期化
    $_POST['uid'] = '';
}

///// Model 部
$today = date('Y-m-d');
$query = 'SELECT count(*) FROM cd_table';
$res = array();
if (($rows=getResult($query,$res))>=1) {
    $maxrows = $res[0][0];
}
define('PAGE', '15');
if (isset($_POST['forward'])) {
    $_SESSION['cd_offset'] += PAGE;
    if ($_SESSION['cd_offset'] >= $maxrows)
        $_SESSION['cd_offset'] = ($maxrows - 1);
} elseif (isset($_POST['backward'])) {
    $_SESSION['cd_offset'] -= PAGE;
    if($_SESSION['cd_offset'] < 0)
        $_SESSION['cd_offset'] = 0;
} else {
    if (!isset($_SESSION['cd_offset']))
        $_SESSION['cd_offset'] = 0;
}
$offset = $_SESSION['cd_offset'];
///////////////////////////////////////// cd_add 追加 処理
while (isset($_POST['cd_add'])) {
    if (!user_check($uid)) break;
    $query = "INSERT INTO cd_table VALUES ('";
    $query .= $_POST['uid'] . "',";
    if ($_POST['act_id'] == "")
        $query .= "NULL,";
    else
        $query .= $_POST['act_id'] . ",";
    if ($_POST['orga_id'] == "")
        $query .= "NULL,";
    else
        $query .= $_POST['orga_id'] . ",";
    if($_POST['pers_id'] == "")
        $query .= "NULL,";
    else
        $query .= $_POST['pers_id'] . ",";
    $query .= "'$today',NULL,'t')";
    $res=array();
    if (($rows=getResult($query,$res))>=0)
        $_SESSION['s_sysmsg'] = $_POST['name'] . "を登録しました。";
    else
        $_SESSION['s_sysmsg'] = $res[0][0];
    // ----------------------------------- offset 値をSET
    $query = "SELECT uid FROM cd_table AS c LEFT OUTER JOIN user_detailes AS u USING(uid) ORDER BY act_id ASC, pid DESC, uid ASC";
    $res = array();
    if(($rows=getResult($query,$res)) >= 1){
        for($i=0;$i<$rows;$i++){
            if($res[$i][0] == $_POST['uid']){
                $_SESSION['cd_offset'] = $i;
                $offset = $i;
            }
        }
    }
    // ----------------------------------- offset 値 END
    break;
}
///////////////////////////////////////// cd_chg 変更 処理
while (isset($_POST['cd_chg'])) {
    if (!user_check($uid)) break;
    $query = "UPDATE cd_table SET act_id=";
    if($_POST['act_id'] == "")
        $query .= "NULL, orga_id=";
    else
        $query .= $_POST['act_id'] . ", orga_id=";
    if($_POST['orga_id'] == "")
        $query .= "NULL, pers_id=";
    else
        $query .= $_POST['orga_id'] . ", pers_id=";
    if($_POST['pers_id'] == "")
        $query .= "NULL, date_chg=";
    else
        $query .= $_POST['pers_id'] . ", date_chg=";
    $query .= "'$today' where uid='";
    $query .= $_POST['uid'] . "'";
    $res=array();
    if(($rows=getResult($query,$res))>=0)
        $_SESSION['s_sysmsg'] = $_POST['name'] . "を変更しました。";
    else
        $_SESSION['s_sysmsg'] = $res[0][0];
    // ----------------------------------- offset 値をSET
    $query = "SELECT uid FROM cd_table AS c LEFT OUTER JOIN user_detailes AS u USING(uid) ORDER BY act_id ASC, pid DESC, uid ASC";
    $res = array();
    if(($rows=getResult($query,$res)) >= 1){
        for($i=0;$i<$rows;$i++){
            if($res[$i][0] == $_POST['uid']){
                $_SESSION['cd_offset'] = $i;
                $offset = $i;
            }
        }
    }
    // ----------------------------------- offset 値 END
    break;
}
///////////////////////////////////////// cd_del 変更 処理
while (isset($_POST['cd_del'])) {
    if (!user_check($uid)) break;
    $query = "DELETE FROM cd_table WHERE uid='";
    $query .= $_POST['uid'] . "'";
    $res=array();
    if ( ($rows=getResult($query,$res)) >= 0) {
        $_SESSION['s_sysmsg'] = $_POST['name'] . "を削除しました。";
    } else {
        $_SESSION['s_sysmsg'] = $res[0][0];
    }
/*  // ----------------------------------- offset 値をSET
    $query = "SELECT uid FROM cd_table AS c LEFT OUTER JOIN user_detailes AS u USING(uid) ORDER BY act_id ASC, pid DESC, uid ASC";
    $res = array();
    if ( ($rows=getResult($query,$res)) >= 1) {
        for ($i=0; $i<$rows; $i++) {
            if ($res[$i][0] == $_POST['uid']) {
                $_SESSION['cd_offset'] = $i;
                $offset = $i;
            }
        }
    }
*/  // ----------------------------------- offset 値 END
    break;
}

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>
<?= $menu->out_jsBaseClass() ?>

<script type='text/javascript' language='JavaScript'>
<!--
/* 入力文字が数字かどうかチェック */
/* false=数字でない  ture=数字である */
function isDigit(str) {
    var len = str.length;
    var c;
    for (i=0; i<len; i++){
        c = str.charAt(i);
        if ( ("0" > c) || ("9" < c) ) {
            return false;
        }
    }
    return true;
}
/* 社員番号のチェックと補正 */
function uid_chk(obj) {
    if (!obj.uid.value.length) {
        alert("[社員番号]の入力欄が空白です。");
        obj.uid.focus();
        return false;
    }
    if (!isDigit(obj.uid.value)) {
        alert("[社員番号]が数字でありません。");
        obj.uid.focus();
        return false;
    }
    if (obj.uid.value.length != 6) {
        switch (obj.uid.value.length) {
        case 1:
            obj.uid.value = ('00000' + obj.uid.value);
            break;
        case 2:
            obj.uid.value = ('0000' + obj.uid.value);
            break;
        case 3:
            obj.uid.value = ('000' + obj.uid.value);
            break;
        case 4:
            obj.uid.value = ('00' + obj.uid.value);
            break;
        case 5:
            obj.uid.value = ('0' + obj.uid.value);
            break;
        }
    }
    return true;
}
// -->
</script>
<style type="text/css">
<!--
.title-font {
    font:bold 13.5pt;
    font-family: monospace;
    border-top:1.0pt solid windowtext;
    border-right:none;
    border-bottom:1.0pt solid windowtext;
    border-left:0.5pt solid windowtext;
}
.today-font {
    font-size: 10.5pt;
    font-family: monospace;
    border-top:1.0pt solid windowtext;
    border-right:1.0pt solid windowtext;
    border-bottom:1.0pt solid windowtext;
    border-left:0.5pt solid windowtext;
}
form {
    margin: 0%;
}
select      {background-color:teal; color:white;}
textarea        {background-color:black; color:white;}
input.sousin    {background-color:red;}
input.text      {background-color:black; color:white;}
.pt11           {font-size:11pt;}
.margin1        {margin:1%;}
.margin0        {margin:0%;}
.pt12b          {font:bold 12pt;}
.y_b    {background-color:yellow; color:blue;}
.r_b    {background-color:red; color:black;}
.b_w    {background-color:blue; color:white;}
-->
</style>
</head>
<body class='margin0' onLoad='document.select_form.uid.focus()'>
    <center>
<?= $menu->out_title_border() ?>
        <form name='select_form' method='post' action='cd_table_mnt.php' onSubmit='return uid_chk(this)'>
            <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td class='winbox caption_font' align='left' nowrap>
                        <?php $menu->out_caption() . "\n" ?>
                        <table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                            <tr align='center'>
                                <td class='winbox caption_font' nowrap <?php if($_POST['cd_sel']=="add") echo "class='y_b'" ?>><input type='radio' name='cd_sel' value='add' id='add'
                                    <?php if($_POST['cd_sel']=="add") echo(" checked") ?>><label for='add'>追加</label>
                                </td>
                                <td class='winbox caption_font' nowrap <?php if($_POST['cd_sel']=="chg") echo "class='y_b'" ?>><input type='radio' name='cd_sel' value='chg' id='chg'
                                    <?php if($_POST['cd_sel']=="chg") echo(" checked") ?>><label for='chg'>変更</label>
                                </td>
                                <td class='winbox caption_font' nowrap <?php if($_POST['cd_sel']=="del") echo "class='y_b'" ?>><input type='radio' name='cd_sel' value='del' id='del'
                                    <?php if($_POST['cd_sel']=="del") echo(" checked") ?>><label for='del'>削除</label>
                                </td>
                            </tr>
                        </table>
                        <div align='center'>
                            社員番号<input type='text' name='uid' size='7' maxlength='6' value='<?php echo $_POST['uid'] ?>'>
                            <input type='submit' name='edit' value='実行' >
                        </div>
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ダミーEnd ------------------>
        </form>
    <?php
        if($_POST['cd_sel'] == "add"){
            $query = "SELECT name FROM user_detailes WHERE uid='" . $_POST['uid'] . "'";
            $res=array();
            if (($rows=getResult($query,$res))>=1){      // 社員マスターのチェック
                $name = $res[0][0];
                $query = "SELECT uid FROM cd_table WHERE uid='" . $_POST['uid'] . "'";
                $res=array();
                if (($rows=getResult($query,$res))>=1){      // 登録済みのチェック
                    echo "<table bgcolor='#d6d3ce'  cellspacing='0' cellpadding='3' border='1'>\n";
                    echo "<tr><td> <!----------- ダミー(デザイン用) ------------>\n";
                    echo "<table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
                    // echo "<table bgcolor='#d6d3ce' align='center' cellspacing='2' cellpadding='3' border='1'>\n";
                    echo "  <tr>\n";
                    echo "      <td class='r_b winbox' nowrap>\n";
                    echo "          コードテーブルに既に登録済みです。変更又は削除を実行して下さい。\n";
                    echo "      </td>\n";
                    echo "  </tr>\n";
                    echo "</table>\n";
                    echo "    </td></tr>\n";
                    echo "</table> <!----------------- ダミーEnd ------------------>\n";
                } else {
                    echo "<form method='post' action='cd_table_mnt.php'>\n";
                    echo "<table bgcolor='#d6d3ce'  cellspacing='0' cellpadding='3' border='1'>\n";
                    echo "<tr><td> <!----------- ダミー(デザイン用) ------------>\n";
                    echo "<table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
                    // echo "<table bgcolor='#d6d3ce' border='1' cellspacing='1' cellpadding='2'>\n";
                    echo "  <th class='winbox'>社員No</th><th class='winbox'>氏名</th><th class='winbox'>経理</th><th class='winbox'>組織</th><th class='winbox'>人事</th>\n";
                    echo "  <tr align='center'>\n";
                    echo "      <td class='winbox'>" . $_POST['uid'] . "</td>\n";
                    echo "      <td class='winbox'>" . $name . "</td>\n";
                    echo "          <input type='hidden' name='uid' value='" . $_POST['uid'] . "'>\n";
                    echo "          <input type='hidden' name='name' value='" . $name . "'>\n";
                    echo "      <td class='winbox'><input type='text' name='act_id' size='6' maxlength='5'></td>\n";
                    echo "      <td class='winbox'><input type='text' name='orga_id' size='7' maxlength='6'></td>\n";
                    echo "      <td class='winbox'><input type='text' name='pers_id' size='6' maxlength='5'></td>\n";
                    echo "      <td class='winbox'><input type='submit' name='cd_add' value='実行' >\n";
                    echo "  </tr>\n";
                    echo "</table>\n";
                    echo "    </td></tr>\n";
                    echo "</table> <!----------------- ダミーEnd ------------------>\n";
                    echo "</form>\n";
                }
            }else{
                echo "<table bgcolor='#d6d3ce' align='center' cellspacing='2' cellpadding='3' border='1'>\n";
                echo "  <tr>\n";
                echo "      <td class='r_b winbox' nowrap>\n";
                echo "          社員番号が登録されていません。\n";
                echo "      </td>\n";
                echo "  </tr>\n";
                echo "</table>\n";
            }
        }
        if($_POST['cd_sel'] == "chg"){
            $query = "SELECT name FROM user_detailes WHERE uid='" . $_POST['uid'] . "'";
            $res=array();
            if(($rows=getResult($query,$res))>=1){      // 氏名の検索
                $name = $res[0][0];
                $query = "SELECT act_id,orga_id,pers_id FROM cd_table WHERE uid='" . $_POST['uid'] . "'";
                $res = array();
                if(($rows=getResult($query,$res))>=1){      // 変更対象データ検索
                    echo "<form method='post' action='cd_table_mnt.php'>\n";
                    echo "<table bgcolor='#d6d3ce'  cellspacing='0' cellpadding='3' border='1'>\n";
                    echo "<tr><td> <!----------- ダミー(デザイン用) ------------>\n";
                    echo "<table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
                    // echo "<table bgcolor='#d6d3ce' border='1' cellspacing='1' cellpadding='2'>\n";
                    echo "  <th class='winbox'>社員No</th><th class='winbox'>氏名</th><th class='winbox'>経理</th><th class='winbox'>組織</th><th class='winbox'>人事</th><th class='winbox' align='center'>変更</th>\n";
                    echo "  <tr align='center'>\n";
                    echo "      <td class='winbox'>" . $_POST['uid'] . "</td>\n";
                    echo "      <td class='winbox'>" . $name . "</td>\n";
                    echo "          <input type='hidden' name='uid' value='" . $_POST['uid'] . "'>\n";
                    echo "          <input type='hidden' name='name' value='" . $name . "'>\n";
                    echo "      <td class='winbox'><input type='text' name='act_id' size='6' maxlength='5' value='" . $res[0]['act_id'] . "'></td>\n";
                    echo "      <td class='winbox'><input type='text' name='orga_id' size='7' maxlength='6' value='" . $res[0]['orga_id'] . "'></td>\n";
                    echo "      <td class='winbox'><input type='text' name='pers_id' size='6' maxlength='5' value='" . $res[0]['pers_id'] . "'></td>\n";
                    echo "      <td class='winbox'><input type='submit' name='cd_chg' value='実行' >\n";
                    echo "  </tr>\n";
                    echo "</table>\n";
                    echo "    </td></tr>\n";
                    echo "</table> <!----------------- ダミーEnd ------------------>\n";
                    echo "</form>\n";
                }else{
                    echo "<table bgcolor='#d6d3ce' align='center' cellspacing='2' cellpadding='3' border='1'>\n";
                    echo "  <tr>\n";
                    echo "      <td class='r_b winbox' nowrap>\n";
                    echo "          社員番号がコードテーブルに登録されていません。 先に追加を実行して下さい。\n";
                    echo "      </td>\n";
                    echo "  </tr>\n";
                    echo "</table>\n";
                }
            }else{
                echo "<table bgcolor='#d6d3ce' align='center' cellspacing='2' cellpadding='3' border='1'>\n";
                echo "  <tr>\n";
                echo "      <td class='r_b winbox' nowrap>\n";
                echo "          社員番号が従業員マスターに登録されていません。\n";
                echo "      </td>\n";
                echo "  </tr>\n";
                echo "</table>\n";
            }
        }
        if($_POST['cd_sel'] == "del"){
            $query = "SELECT act_id,orga_id,pers_id FROM cd_table WHERE uid='" . $_POST['uid'] . "'";
            $res=array();
            if(($rows=getResult($query,$res))>=1){      // 氏名の検索
                $query = "SELECT name FROM user_detailes WHERE uid='" . $_POST['uid'] . "'";
                $res_name = array();
                if(($rows=getResult($query,$res_name))>=1){     // 変更対象データ検索
                    $name = $res_name[0][0];
                    echo "<form method='post' action='cd_table_mnt.php'>\n";
                    echo "<table bgcolor='#d6d3ce'  cellspacing='0' cellpadding='3' border='1'>\n";
                    echo "<tr><td> <!----------- ダミー(デザイン用) ------------>\n";
                    echo "<table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
                    // echo "<table bgcolor='#d6d3ce' border='1' cellspacing='1' cellpadding='2'>\n";
                    echo "  <th class='winbox'>社員No</th><th class='winbox'>氏名</th><th class='winbox'>経理</th><th class='winbox'>組織</th><th class='winbox'>人事</th><th align='center' class='r_b winbox'>削除</th>\n";
                    echo "  <tr align='center'>\n";
                    echo "      <td class='winbox'>" . $_POST['uid'] . "</td>\n";
                    echo "      <td class='winbox'>" . $name . "</td>\n";
                    echo "          <input type='hidden' name='uid' value='" . $_POST['uid'] . "'>\n";
                    echo "          <input type='hidden' name='name' value='" . $name . "'>\n";
                    echo "      <td class='winbox'>" . $res[0]['act_id'] . "</td>\n";
                    echo "      <td class='winbox'>" . $res[0]['orga_id'] . "</td>\n";
                    echo "      <td class='winbox'>" . $res[0]['pers_id'] . "</td>\n";
                    echo "      <td class='winbox'><input type='submit' name='cd_del' value='実行' >\n";
                    echo "  </tr>\n";
                    echo "</table>\n";
                    echo "    </td></tr>\n";
                    echo "</table> <!----------------- ダミーEnd ------------------>\n";
                    echo "</form>\n";
                }else{
                    echo "<table bgcolor='#d6d3ce' align='center' cellspacing='2' cellpadding='3' border='1'>\n";
                    echo "  <tr>\n";
                    echo "      <td class='r_b winbox' nowrap>\n";
                    echo "          社員番号が従業員マスターに登録されていません。\n";
                    echo "      </td>\n";
                    echo "  </tr>\n";
                    echo "</table>\n";
                }
            }else{
                echo "<table bgcolor='#d6d3ce' align='center' cellspacing='2' cellpadding='3' border='1'>\n";
                echo "  <tr>\n";
                echo "      <td class='r_b winbox' nowrap>\n";
                echo "          社員番号がコードテーブルに登録されていません。\n";
                echo "      </td>\n";
                echo "  </tr>\n";
                echo "</table>\n";
            }
        }
        // view 削除の必要な一覧表示
        $query = "SELECT c.uid, u.name, c.act_id, c.orga_id, c.pers_id, c.date_add, c.date_chg, c.cd_flg 
            FROM cd_table AS c LEFT JOIN user_detailes AS u USING(uid) 
            WHERE retire_date IS NOT NULL OR u.sid = 31";
        $res=array();
        if (($rows=getResult2($query,$res)) >= 1) {     // foreach用のquery実行
            echo "<hr>\n";
            echo "<table bgcolor='#d6d3ce'  cellspacing='0' cellpadding='3' border='1'>\n";
            echo "<tr><td> <!----------- ダミー(デザイン用) ------------>\n";
            echo "<table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
            // echo "<table bgcolor='#d6d3ce' align='center' cellspacing='1' cellpadding='3' border='1'>\n";
            echo "  <form method='post' action='cd_table_mnt.php'>\n";
            echo "  <caption>コードテーブルから削除しなければならない一覧\n";
            echo "  </caption>\n";
            echo "  </form>\n";
            print(" <th nowrap class='r_b'>No</th><th nowrap class='r_b'>社員No</th><th nowrap class='r_b'>氏名</th><th nowrap class='r_b'>経理</th><th nowrap class='r_b'>組織</th><th nowrap class='r_b'>人事</th><th nowrap class='r_b'>登録日</th><th nowrap class='r_b'>変更日</th><th nowrap class='r_b'>有効</th>\n");
            $num = count($res[0]);
            for($r=0;$r<$rows;$r++){
                print("<tr>\n");
                echo "  <form method='post' action='cd_table_mnt.php'>\n";
                print(" <td class='winbox' align='center'><input type='submit' name='copy' value='" . ($r + 1) . "'></td>\n");
                echo "      <input type='hidden' name='cd_sel' value='del'>\n";
                echo "      <input type='hidden' name='uid' value='" . $res[$r][0] . "'>\n";
                echo "  </form>\n";
                for ($n=0; $n<$num; $n++) {
                    if ($res[$r][$n] == "")
                        echo("<td class='winbox' nowrap align='center'>---</td>\n");
                    else
                        echo("<td class='winbox' nowrap align='center'>" . $res[$r][$n] . "</td>\n");
                }
                echo "</tr>\n";
            }
            echo "</table>\n";
            echo "    </td></tr>\n";
            echo "</table> <!----------------- ダミーEnd ------------------>\n";
        }

        // view 追加の必要な一覧表示
        $query = "
            SELECT u.uid, u.name, c.act_id, c.orga_id, c.pers_id, c.date_add, c.date_chg, c.cd_flg 
            FROM user_detailes AS u LEFT OUTER JOIN cd_table AS c USING(uid) 
            WHERE c.uid IS NULL AND retire_date IS NULL AND u.sid != 31 AND u.pid != 120 AND u.uid NOT LIKE '99%'
        ";
        $res=array();
        if (($rows=getResult2($query,$res)) >= 1) {     // foreach用のquery実行
            echo "<hr>\n";
            echo "<table bgcolor='#d6d3ce'  cellspacing='0' cellpadding='3' border='1'>\n";
            echo "<tr><td> <!----------- ダミー(デザイン用) ------------>\n";
            echo "<table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
            // echo "<table bgcolor='#d6d3ce' align='center' cellspacing='1' cellpadding='3' border='1'>\n";
            echo "  <form method='post' action='cd_table_mnt.php'>\n";
            echo "  <caption>コードテーブルに追加しなければならない一覧\n";
            echo "  </caption>\n";
            echo "  </form>\n";
            echo "  <th nowrap class='y_b winbox'>No</th><th nowrap class='y_b winbox'>社員No</th><th nowrap class='y_b winbox'>氏名</th><th nowrap class='y_b winbox'>経理</th><th nowrap class='y_b winbox'>組織</th><th nowrap class='y_b winbox'>人事</th><th nowrap class='y_b winbox'>登録日</th><th nowrap class='y_b winbox'>変更日</th><th nowrap class='y_b winbox'>有効</th>\n";
            $num = count($res[0]);
            for ($r=0; $r<$rows; $r++) {
                echo "<tr>\n";
                echo "  <form method='post' action='cd_table_mnt.php'>\n";
                echo " <td class='winbox' align='center'><input type='submit' name='copy' value='" . ($r + 1) . "'></td>\n";
                echo "      <input type='hidden' name='cd_sel' value='add'>\n";
                echo "      <input type='hidden' name='uid' value='" . $res[$r][0] . "'>\n";
                echo "  </form>\n";
                for ($n=0; $n<$num; $n++) {
                    if ($res[$r][$n] == "")
                        echo "<td class='winbox' nowrap align='center'>---</td>\n";
                    else
                        echo "<td class='winbox' nowrap align='center'>" . $res[$r][$n] . "</td>\n";
                }
                echo "</tr>\n";
            }
            echo "</table>\n";
            echo "    </td></tr>\n";
            echo "</table> <!----------------- ダミーEnd ------------------>\n";
        }

        // view 一覧表示
        $query = "SELECT c.uid, u.name, c.act_id, a.s_name, c.orga_id, c.pers_id, c.date_add, c.date_chg, c.cd_flg 
            FROM cd_table AS c LEFT JOIN act_table AS a USING(act_id) LEFT OUTER JOIN user_detailes AS u 
            USING(uid) ORDER BY u.sid ASC, c.act_id ASC, pid DESC, uid ASC OFFSET $offset LIMIT " . PAGE;
        $res=array();
        if (($rows=getResult2($query,$res)) >= 1) {     // 
            echo "<hr>\n";
            echo "<table bgcolor='#d6d3ce'  cellspacing='0' cellpadding='3' border='1'>\n";
            echo "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
            echo "<table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
            // echo "<table bgcolor='#d6d3ce' align='center' cellspacing='1' cellpadding='3' border='1'>\n";
            echo "  <form method='post' action='cd_table_mnt.php'>\n";
            echo "  <caption>社員番号・経理・組織・人事コードテーブル一覧\n";
            echo "      <input type='submit' name='backward' value='前頁'>\n";
            echo "      <input type='submit' name='forward' value='次頁'>\n";
            echo "  </caption>\n";
            echo "  </form>\n";
            print(" <th nowrap class='b_w winbox'>No</th><th nowrap class='b_w winbox'>社員No</th><th nowrap class='b_w winbox'>氏名</th><th nowrap class='b_w winbox'>経理</th><th nowrap class='b_w winbox'>経理部門名</th><th nowrap class='b_w winbox'>組織</th><th nowrap class='b_w winbox'>人事</th><th nowrap class='b_w winbox'>登録日</th><th nowrap class='b_w winbox'>変更日</th><th nowrap class='b_w winbox'>有効</th>\n");
            $num = count($res[0]);
            for($r=0;$r<$rows;$r++){
                print("<tr>\n");
                echo "  <form method='post' action='cd_table_mnt.php'>\n";
                print(" <td class='winbox' align='center'><input type='submit' name='copy' value='" . ($r + $offset + 1) . "'></td>\n");
                echo "      <input type='hidden' name='cd_sel' value='chg'>\n";
                echo "      <input type='hidden' name='uid' value='" . $res[$r][0] . "'>\n";
                echo "  </form>\n";
                for ($n=0; $n<$num; $n++) {
                    if ($res[$r][$n] == "")
                        echo("<td class='winbox' nowrap align='center'>---</td>\n");
                    else
                        echo("<td class='winbox' nowrap align='center'>" . $res[$r][$n] . "</td>\n");
                }
                echo "</tr>\n";
            }
            echo "</table>\n";
            echo "    </td></tr>\n";
            echo "</table> <!----------------- ダミーEnd ------------------>\n";
        }
    ?>
    </center>
</body>
</html>
<?php
    // 追加が必要なリスト
    ///// 社員データにあって経理コードテーブルに無い者のリスト (除く退職者と出向者)
    $query = "SELECT u.uid, u.name FROM user_detailes AS u LEFT OUTER JOIN cd_table AS c USING(uid) 
            WHERE c.uid IS NULL AND retire_date IS NULL AND u.sid != 31";
    // 削除が必要なリスト
    ///// 経理コードテーブルにあって社員データでは退職又は出向している者のリスト
    $query = "SELECT c.uid, u.name FROM cd_table AS c LEFT JOIN user_detailes AS u USING(uid) 
            WHERE retire_date IS NOT NULL OR u.sid = 31";
?>

