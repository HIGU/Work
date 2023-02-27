<?php
//////////////////////////////////////////////////////////////////////////////
// 経理部門コード・配賦率の保守 act_table.php → act_table_new.php          //
// Copyright (C) 2002-2009 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2002/09/17 Created  act_table_mnt_new.php                                //
// 2002/09/20 サイトメニューに追加                                          //
// 2002/09/28 配賦率テーブルを act_allocation & allocation_item に変更      //
//            そのためプログラムを大幅に変更 ファイル名変更(表題)           //
// 2002/11/26 損益グループの配賦率照会がほぼ完成 1項目クエリー              //
//                         全体で100％のチェック未完成？                    //
// 2003/02/26 body に onLoad を追加し初期入力個所に focus() させた          //
// 2003/05/15 新組織体系に対応 所属コードを追加 大分類項目保守でメンテ      //
// 2004/10/28 user_check()functionを追加し編集出来るユーザーを限定          //
// 2005/10/27 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2007/04/17 権限に300144を追加 大谷                                       //
// 2009/03/12 権限に014737を追加 桝                                         //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('error_reporting',E_ALL);           // E_ALL='2047' debug 用
// ini_set('display_errors','1');              // Error 表示 ON debug 用 リリース後コメント
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../function.php');           // define.php と pgsql.php を require_once している
require_once ('../tnk_func.php');           // TNK に依存する部分の関数を require_once している
require_once ('../MenuHeader.php');         // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
    // 実際の認証はprofit_loss_submit.phpで行っているaccount_group_check()を使用

////////////// サイト設定
$menu->set_site(INDEX_PL, 10);                    // site_index=INDEX_PL(損益メニュー) site_id=10(部門コード)
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('経理部門コードのメンテナンス');

/////////// ユーザーのチェック
$uid = $_SESSION['User_ID'];            // ユーザー
function user_check($uid)
{
    switch ($uid) {
    case '017850':      // 上野
    case '300055':      // 斎藤
    case '300101':      // 大谷
    case '300144':      // 大谷
    case '010561':      // 小林
    case '014737':      // 桝
        return TRUE;
        break;
    default:
        $query = "select trim(name) from user_detailes where uid = '{$uid}' limit 1";
        if (getUniResult($query, $name) <= 0) $name = '';
        $_SESSION['s_sysmsg'] = "社員番号：{$uid}：{$name}さんでは経理部門コードの保守は出来ません！ 管理担当者へ連絡して下さい。";
        return FALSE;
    }
}

if (!isset($_POST['act_sel'])) {        // 設定されていない時の初期化
    $_POST['act_sel'] = "";
}
if (!isset($_POST['act_id'])) {        // 設定されていない時の初期化
    $_POST['act_id'] = "";
}
$today = date("Y-m-d");
$query = "select count(*) from act_table";
$res = array();
if ( ($rows=getResult($query,$res)) >= 1) {
    $maxrows = $res[0][0];
}
define("PAGE","15");
if ( isset($_POST['forward']) ) {
    $_SESSION['act_offset'] += PAGE;
    if ($_SESSION['act_offset'] >= $maxrows) {
        $_SESSION['act_offset'] = ($maxrows - 1);
    }
} elseif (isset($_POST['backward'])) {
    $_SESSION['act_offset'] -= PAGE;
    if ($_SESSION['act_offset'] < 0) {
        $_SESSION['act_offset'] = 0;
    }
} else {
    if ( !isset($_SESSION['act_offset']) ) {
        $_SESSION['act_offset'] = 0;
    }
}
$offset = $_SESSION['act_offset'];
///////////////////////////////////////// act_add 追加 処理(act_table に項目の追加のみ)
while (isset($_POST['act_add'])) {
    if (!user_check($uid)) break;
    $query = "insert into act_table (act_id, act_name, s_name, date_add, date_chg, act_flg) values (";
    $query .= $_POST['act_id'] . ",'" . $_POST['act_name'] . "','" . $_POST['s_name'] . "','$today',NULL,'t')";
    $res = array();
    if ( ($rows=getResult($query,$res)) >= 0) {
        $_SESSION['s_sysmsg'] = $_POST['act_id'] . " : " . $_POST['act_name'] . "を登録しました。";
    } else {
        $_SESSION['s_sysmsg'] = $res[0][0];
    }
    // ----------------------------------- offset 値をSET
    $query = "select act_id from act_table order by act_id ASC";
    $res = array();
    if ( ($rows=getResult($query,$res)) >= 1) {
        for ($i=0; $i<$rows; $i++) {
            if ($res[$i][0] == $_POST['act_id']) {
                $_SESSION['act_offset'] = $i;
                $offset = $i;
            }
        }
    }
    // ----------------------------------- offset 値 END
    break;
}
///////////////////////////////////////// act_chg 変更 処理
while ( isset($_POST['act_chg']) ) {
    if (!user_check($uid)) break;
    $query = "update act_table set act_name='" . $_POST['act_name'] . "', s_name='" . $_POST['s_name'] . "',date_chg='$today', s_g_exp=";
    if ($_POST['s_g_exp'] == "") {
        $query .= "NULL, c_exp=";
    } else {
        $query .= $_POST['s_g_exp'] . ", c_exp=";
    }
    if ($_POST['c_exp'] == "") {
        $query .= "NULL, l_exp=";
    } else {
        $query .= $_POST['c_exp'] . ", l_exp=";
    }
    if ($_POST['l_exp'] == "") {
        $query .= "NULL, shoukan=";
    } else {
        $query .= $_POST['l_exp'] . ", shoukan=";
    }
    if ($_POST['shoukan'] == "") {
        $query .= "NULL, c_assy=";
    } else {
        $query .= $_POST['shoukan'] . ", c_assy=";
    }
    if ($_POST['c_assy'] == "") {
        $query .= "NULL, s_toku=";
    } else {
        $query .= $_POST['c_assy'] . ", s_toku=";
    }
    if ($_POST['s_toku'] == "") {
        $query .= "NULL, s_1_nc=";
    } else {
        $query .= $_POST['s_toku'] . ", s_1_nc=";
    }
    if ($_POST['s_1_nc'] == "") {
        $query .= "NULL, s_1_6=";
    } else {
        $query .= $_POST['s_1_nc'] . ", s_1_6=";
    }
    if ($_POST['s_1_6'] == "") {
        $query .= "NULL, s_4_nc=";
    } else {
        $query .= $_POST['s_1_6'] . ", s_4_nc=";
    }
    if ($_POST['s_4_nc'] == "") {
        $query .= "NULL, s_5_pf=";
    } else {
        $query .= $_POST['s_4_nc'] . ", s_5_pf=";
    }
    if ($_POST['s_5_pf'] == "") {
        $query .= "NULL, s_5_2=";
    } else {
        $query .= $_POST['s_5_pf'] . ", s_5_2=";
    }
    if ($_POST['s_5_2'] == "") {
        $query .= "NULL, shape=";
    } else {
        $query .= $_POST['s_5_2'] . ", shape=";
    }
    if ($_POST['shape'] == "") {
        $query .= "NULL ";
    } else {
        $query .= $_POST['shape'] . " ";
    }
    $query .= "where act_id=" . $_POST['act_id'];
    $res = array();
    if ( ($rows=getResult($query,$res)) >=0 ) {
        $_SESSION['s_sysmsg'] = $_POST['act_id'] . " : " . $_POST['act_name'] . "を変更しました。";
    } else {
        $_SESSION['s_sysmsg'] = $res[0][0];
    }
    // ----------------------------------- offset 値をSET
    $query = "select act_id from act_table order by act_id ASC";
    $res = array();
    if ( ($rows=getResult($query,$res)) >= 1) {
        for ($i=0; $i<$rows; $i++) {
            if ($res[$i][0] == $_POST['act_id']) {
                $_SESSION['act_offset'] = $i;
                $offset = $i;
            }
        }
    }
    // ----------------------------------- offset 値 END
    break;
}
///////////////////////////////////////// act_del 変更 処理
while ( isset($_POST['act_del']) ) {
    if (!user_check($uid)) break;
    $query = "delete from act_table where act_id=";
    $query .= $_POST['act_id'] ;
    $res=array();
    if ( ($rows=getResult($query,$res)) >= 0) {
        $_SESSION['s_sysmsg'] = $_POST['act_id'] . "を削除しました。";
    } else {
        $_SESSION['s_sysmsg'] = $res[0][0];
    }
/*  // ----------------------------------- offset 値をSET
    $query = "select act_id from act_table order by act_id ASC";
    $res = array();
    if(($rows=getResult($query,$res)) >= 1){
        for($i=0;$i<$rows;$i++){
            if($res[$i][0] == $_POST['act_id']){
                $_SESSION['act_offset'] = $i;
                $offset = $i;
            }
        }
    }
*/  // ----------------------------------- offset 値 END
    break;
}
///////////////////////////////////////// act_flg 変更 処理(直接・間接部門・販管費) 基本
while ( isset($_POST['act_flg']) ) {
    if (!user_check($uid)) break;
    $res = array();
    if ($_POST['act_flg'] == "直接部門") {
        $query = "update act_table set act_flg='f' where act_id=" . $_POST['act_id'];
        if ( ($rows=getResult($query,$res)) >= 0) {
            $_SESSION['s_sysmsg'] = $_POST['act_id'] . "を間接部門に変更しました。";
        } else {
            $_SESSION['s_sysmsg'] = $res[0][0];
        }
    } elseif ($_POST['act_flg'] == "間接部門") {
        $query = "update act_table set act_flg='h' where act_id=" . $_POST['act_id'];
        if ( ($rows=getResult($query,$res)) >= 0) {
            $_SESSION['s_sysmsg'] = $_POST['act_id'] . "を販管部門に変更しました。";
        } else {
            $_SESSION['s_sysmsg'] = $res[0][0];
        }
    } else { /////////////////////// 販管費部門
        $query = "update act_table set act_flg='t' where act_id=" . $_POST['act_id'];
        if ( ($rows=getResult($query,$res)) >= 0) {
            $_SESSION['s_sysmsg'] = $_POST['act_id'] . "を直接部門に変更しました。";
        } else {
            $_SESSION['s_sysmsg'] = $res[0][0];
        }
    }
    break;
}
///////////////////////////////////////// rate_flg 変更 処理(機械賃率対象外(0)or(NULL) 対象(1) 管理配賦(2))
while ( isset($_POST['rate_flg']) ) {
    if (!user_check($uid)) break;
    $res = array();
    if ($_POST['rate_flg'] == "機械賃率除外") {
        $query = "update act_table set rate_flg='1' where act_id=" . $_POST['act_id'];
        if ( ($rows=getResult($query,$res)) >= 0) {
            $_SESSION['s_sysmsg'] = $_POST['act_id'] . "を機械賃率対象に変更しました。";
        } else {
            $_SESSION['s_sysmsg'] = $res[0][0];
        }
    } elseif ($_POST['rate_flg'] == "機械賃率対象") {
        $query = "update act_table set rate_flg='2' where act_id=" . $_POST['act_id'];
        if ( ($rows=getResult($query,$res)) >= 0) {
            $_SESSION['s_sysmsg'] = $_POST['act_id'] . "を賃率管理配賦に変更しました。";
        } else {
            $_SESSION['s_sysmsg'] = $res[0][0];
        }
    } else {
        $query = "update act_table set rate_flg='0' where act_id=" . $_POST['act_id'];
        if ( ($rows=getResult($query,$res)) >= 0) {
            $_SESSION['s_sysmsg'] = $_POST['act_id'] . "を機械賃率除外に変更しました。";
        } else {
            $_SESSION['s_sysmsg'] = $res[0][0];
        }
    }
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
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>
<?= $menu->out_jsBaseClass() ?>

<script type='text/javascript' language='JavaScript' src='act_table_mnt.js'></script>
<style type='text/css'>
    <!--
    select{
        background-color:teal;
        color:white;
    }
    textarea{
        background-color:black;
        color:white;
    }
    input.sousin{
        background-color:red;
    }
    input.text{
        background-color:black;
        color:white;
    }
    .pt11{
        font-size:11pt;
    }
    .margin1{
        margin:1%;
    }
    .margin0{
        margin:0%;
    }
    .pt12b{
        font:bold 12pt;
    }
    .y_b{
        background-color:yellow;
        color:blue;
    }
    .r_b{
        background-color:red;
        color:black;
    }
    .r_w{
        background-color:red;
        color:white;
    }
    .b_w{
        background-color:blue;
        color:white;
    }
    .fsp{
        font-size:8pt;
    }
    .fmp{
        font-size:10pt;
    }
    .flp{
        font-size:12pt;
    }
    .fllbp{
        font-size:16pt;
        font-weight:bold;
    }
    .fmp-n{
        background-color:yellow;
        color:blue;
        font-size:10pt;
        font-weight:normal;
    }
    input.blue{
        color:blue;
    }
    input.red{
        color:red;
    }
    input.green{
        color:green;
    }
    -->
</style>
</head>
<body onLoad='document.select_form.act_id.focus()'>
    <center>
<?= $menu->out_title_border() ?>
        <form name='select_form' method='post' action='<?=$menu->out_self()?>' onSubmit='return act_id_chk(this)'>
            <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td class='winbox' align='left' nowrap>
                        区分を選択して部門コードを入力し実行ボタンを押して下さい。<br>
                        <table class='winbox_field' width='100%' align='center' border='1' cellspacing='0' cellpadding='2'>
                            <tr align='center'>
                                <td class='winbox' nowrap <?php if($_POST['act_sel']=='add') echo "class='b_w'" ?>>
                                    <input type='radio' name='act_sel' value='add' id='add'
                                        <?php if($_POST['act_sel']=='add') echo 'checked' ?>
                                    >
                                    <label for='add'>追加</label>
                                </td>
                                <td class='winbox' nowrap <?php if($_POST['act_sel']=='chg') echo "class='b_w'" ?>>
                                    <input type='radio' name='act_sel' value='chg' id='chg'
                                        <?php if($_POST['act_sel']=='chg') echo'checked' ?>
                                    >
                                    <label for='chg'>変更</label>
                                </td>
                                <td class='winbox' nowrap <?php if($_POST['act_sel']=='del') echo "class='b_w'" ?>>
                                    <input type='radio' name='act_sel' value='del' id='del'
                                    <?php if($_POST['act_sel']=='del') echo 'checked' ?>
                                    >
                                    <label for='del'>削除</label>
                                </td>
                        </table>
                        <div align='center'>
                            部門コード<input type='text' name='act_id' size='7' maxlength='6' value='<?php echo $_POST['act_id'] ?>'>
                            <input type='submit' name='edit' value='実行' >
                        </div>
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ダミーEnd ------------------>
        </form>
    <?php
        if($_POST['act_sel'] == 'add'){
            $query = "select act_id from act_table where act_id=" . $_POST['act_id'];
            $res = array();
            if(($rows=getResult($query,$res))>=1){      // 登録済みのチェック
                echo "<table bgcolor='#d6d3ce' align='center' cellspacing='2' cellpadding='3' border='1'>\n";
                echo "  <tr>\n";
                echo "      <td class='r_b' nowrap>\n";
                echo "          部門コードが既に登録済みです。変更又は削除を実行して下さい。\n";
                echo "      </td>\n";
                echo "  </tr>\n";
                echo "</table>\n";
            }else{
                echo "<form method='post' action='act_table_mnt_new.php' onSubmit='return act_add_chk(this)'>\n";
                echo "<table bgcolor='#d6d3ce' border='1' cellspacing='1' cellpadding='2'>\n";
                echo "  <th nowrap>コード</th><th nowrap>部 門 名</th><th nowrap>短縮名</th><th class='b_w'>登録</th>\n";
                echo "  <tr align='center'>\n";
                echo "      <td>" . $_POST['act_id'] . "</td>\n";
                echo "          <input type='hidden' name='act_id' value='" . $_POST['act_id'] . "'>\n";
                echo "      <td><input type='text' name='act_name' size='23' maxlength='20'></td>\n";
                echo "      <td><input type='text' name='s_name' size='9' maxlength='8'></td>\n";
                echo "      <td><input type='submit' name='act_add' value='実行' >\n";
                echo "  </tr>\n";
                echo "</table>\n";
                echo "</form>\n";
            }
        }
        if($_POST['act_sel'] == "chg"){
            $query = "select act_id,act_name,s_name,c_exp,l_exp,s_g_exp,shoukan,c_assy,s_toku,s_1_nc,s_1_6,s_4_nc,s_5_pf,s_5_2,shape from act_table where act_id=" . $_POST['act_id'];
            $res = array();
            if(($rows=getResult($query,$res))>=1){      // 変更対象データ検索
                echo "<form method='post' action='act_table_mnt_new.php' onSubmit='return act_chk_name(this)'>\n";
                echo "<table bgcolor='#d6d3ce' border='1' cellspacing='1' cellpadding='2'>\n";
                echo "  <th nowrap>コード</th><th nowrap>部 門 名</th><th nowrap>短縮名</th><th class='b_w'>変更</th>\n";
                echo "  <tr align='center'>\n";
                echo "      <td>" . $_POST['act_id'] . "</td>\n";
                echo "          <input type='hidden' name='act_id' value='" . $_POST['act_id'] . "'>\n";
                echo "      <td><input type='text' name='act_name' size='23' maxlength='20' value='" . trim($res[0]['act_name']) . "'></td>\n";
                echo "      <td><input type='text' name='s_name' size='9' maxlength='8' value='" . trim($res[0]['s_name']) . "'></td>\n";
                echo "      <td><input type='submit' name='act_chg' value='実行' >\n";
                echo "  </tr>\n";
                echo "</table>\n";
                echo "</form>\n";
            }else{
                echo "<table bgcolor='#d6d3ce' align='center' cellspacing='2' cellpadding='3' border='1'>\n";
                echo "  <tr>\n";
                echo "      <td class='r_b' nowrap>\n";
                echo "          部門コードが登録されていません。 先に追加を実行して下さい。\n";
                echo "      </td>\n";
                echo "  </tr>\n";
                echo "</table>\n";
            }
        }
        if($_POST['act_sel'] == "del"){
            $query = "select * from act_table where act_id=" . $_POST['act_id'];
            $res = array();
            if(($rows=getResult($query,$res))>=1){      // 変更対象データ検索
                echo "<form method='post' action='act_table_mnt_new.php'>\n";
                echo "<table bgcolor='#d6d3ce' border='1' cellspacing='1' cellpadding='2'>\n";
                echo "  <th nowrap>コード</th><th nowrap>部 門 名</th><th nowrap>短縮名</th><th class='r_w'>削除</th>\n";
                echo "  <tr align='center'>\n";
                echo "      <td>" . $_POST['act_id'] . "</td>\n";
                echo "          <input type='hidden' name='act_id' value='" . $_POST['act_id'] . "'>\n";
                echo "      <td><input type='text' name='act_name' size='23' maxlength='20' value='" . $res[0]['act_name'] . "'></td>\n";
                echo "      <td><input type='text' name='s_name' size='9' maxlength='8' value='" . $res[0]['s_name'] . "'></td>\n";
                echo "      <td><input type='submit' name='act_del' value='実行' >\n";
                echo "  </tr>\n";
                echo "</table>\n";
                echo "</form>\n";
            }else{
                echo "<table bgcolor='#d6d3ce' align='center' cellspacing='2' cellpadding='3' border='1'>\n";
                echo "  <tr>\n";
                echo "      <td class='r_b' nowrap>\n";
                echo "          部門コードがマスターに登録されていません。\n";
                echo "      </td>\n";
                echo "  </tr>\n";
                echo "</table>\n";
            }
        }
        // view 一覧表示
        $query = "select act_id,act_name,s_name,act_flg,rate_flg ";
        $query .= "from act_table left outer join cate_allocation on(act_id=dest_id and cate_id=0) order by cate_rate ASC offset $offset limit " . PAGE;
        $res = array();
        if(($rows=getResult($query,$res))>=1){ ///////////// 部門コードマスターの取得
//          echo "<hr>\n";
            echo "<table bgcolor='#d6d3ce' align='center' cellspacing='1' cellpadding='3' border='1'>\n";
            echo "  <form action='act_table_mnt_new.php' method='post'>\n";
            echo "  <caption>経理部門コード・配賦率一覧\n";
            echo "      <input type='submit' name='backward' value='前頁'>\n";
            echo "      <input type='submit' name='forward' value='次頁'>\n";
            echo "  </caption>\n";
            echo "  </form>\n";
            ////////////////////////////////////////////// 大分類の配賦率 category_item cate_allocation
            $query = "select cate_item,cate_id from category_item where cate_id<=100 order by cate_group";
            $res_cate = array();
            $rows_cate=getResult($query,$res_cate);
                /***** フィールド名設定 *****/
            echo "  <th nowrap class='fmp-n'>No</th><th nowrap class='fmp-n'>コード</th><th nowrap class='fmp-n'>部 門 名</th><th nowrap class='fmp-n'>短縮名</th>\n";
            for($i=0;$i<$rows_cate;$i++){
                echo "<th nowrap class='fmp-n'>" . $res_cate[$i]['cate_item'] . "</th>\n";
            }
            echo "<th colspan='2' align='center' class='fmp-n'>直接/間接/販管費/その他</th>\n";
                /***** フィールド名 End *****/
            for($r=0;$r<$rows;$r++){
                print("<tr>\n");
                echo "  <form method='post' action='act_table_mnt_new.php'>\n";
                print(" <td align='center'><input type='submit' name='copy' value='" . ($r + $offset + 1) . "'></td>\n");
                echo "      <input type='hidden' name='act_sel' value='chg'>\n";
                echo "      <input type='hidden' name='act_id' value='" . $res[$r]['act_id'] . "'>\n";
                echo("<td nowrap align='left' class='flp'>" . $res[$r]['act_id'] . "</td>\n");
                echo("<td nowrap align='left' class='fmp'>" . $res[$r]['act_name'] . "</td>\n");
                echo("<td nowrap align='left' class='fmp'>" . $res[$r]['s_name'] . "</td>\n");
                for($i=0;$i<$rows_cate;$i++){
                    //////////////////////////// １項目クエリー ユニークな配賦率の取得
                    $query = "select cate_rate from cate_allocation where dest_id=" . $res[$r]['act_id'] . "and cate_id=" . $res_cate[$i]['cate_id'];
                    $res_cate_allo = array();
                    if (($rows_cate_allo = getResult($query,$res_cate_allo)) < 1)
                        echo("<td nowrap align='center'>---</td>\n");
                    elseif ($res_cate_allo[0]['cate_rate'] == "")
                        echo("<td nowrap align='center'>---</td>\n");
                    else
                        echo("<td nowrap align='right' class='flp'>" . $res_cate_allo[0]['cate_rate'] . "</td>\n");
                }
                echo "  </form>\n";
                echo "  <form method='post' action='act_table_mnt_new.php'>\n";
                if($res[$r]['act_flg'] == 't')
                    echo "  <td><input type='submit' name='act_flg' value='直接部門' class='blue'></td>\n";
                else if($res[$r]['act_flg'] == 'f')
                    echo "  <td><input type='submit' name='act_flg' value='間接部門' class='red'></td>\n";
                else
                    echo "  <td><input type='submit' name='act_flg' value='販管部門' class='green'></td>\n";
                if($res[$r]['rate_flg'] == '1')
                    echo "  <td align='center'><input type='submit' name='rate_flg' value='機械賃率対象' class='blue'></td>\n";
                else if($res[$r]['rate_flg'] == '2')
                    echo "  <td align='center'><input type='submit' name='rate_flg' value='賃率管理配賦' class='red'></td>\n";
                else
                    echo "  <td align='center'><input type='submit' name='rate_flg' value='機械賃率除外'></td>\n";
                echo "      <input type='hidden' name='act_id' value='" . $res[$r]['act_id'] . "'>\n";
                echo "  </form>\n";
                print("</tr>\n");
            }
            print("</table>\n");
            //////////////////////////////////////////////// 小分類の各配賦率 allocation_item act_allocation
            for($r=0;$r<$rows;$r++){ ////////////// PAGE 数 分回す
                $query = "select allo_item,allo_id from allocation_item order by allo_id ASC";
                $res_allo = array();
                if(($rows_allo=getResult($query,$res_allo))>=1){ ///////////// 配布項目の取得
                    for($i=0;$i<$rows_allo;$i++){
                        $query = "select dest_id,allo_rate from act_allocation where allo_id="  . $res_allo[$i]['allo_id'] . 
                            " and orign_id=" . $res[$r]['act_id'] . " order by dest_id ASC";
                        $res_act = array();
                        if(($rows_act=getResult($query,$res_act))>=1){ //////// 配布先・配布率の取得
                            echo "<hr>\n";
                            echo "<table bgcolor='#d6d3ce' align='center' cellspacing='1' cellpadding='3' border='1'>\n";
                            echo "  <form action='act_table_mnt_new.php' method='post'>\n";
                            echo "  <caption>" . $res_allo[$i]['allo_item'] . "\n";
                            echo "      <input type='submit' name='backward' value='前頁'>\n";
                            echo "      <input type='submit' name='forward' value='次頁'>\n";
                            echo "  </caption>\n";
                            echo "  </form>\n";
                            echo "  <th nowrap class='fmp-n'>コード</th><th nowrap class='fmp-n'>部 門 名</th><th nowrap class='fmp-n'>短縮名</th>\n";
                            for($j=0;$j<$rows_act;$j++){
                                $query = "select s_name from act_table where act_id=" . $res_act[$j]['dest_id'] . " limit 1\n";
                                $res_name = array();
                                if(($rows_name=getResult($query,$res_name))>=1){ //////// 配布先名称の取得
                                    echo "<th nowrap class='y_b'>" . $res_name[0]['s_name'] . "</th>\n";
                                }
                            }
                            echo "<tr>\n";
                            echo("<td nowrap align='left' class='flp'>" . $res[$r]['act_id'] . "</td>\n");
                            echo("<td nowrap align='left' class='fmp'>" . $res[$r]['act_name'] . "</td>\n");
                            echo("<td nowrap align='left' class='fmp'>" . $res[$r]['s_name'] . "</td>\n");
                            for($j=0;$j<$rows_act;$j++){
                                if($res_act[$j]['allo_rate'] == "")
                                    echo("<td nowrap align='center'>---</td>\n");
                                else
                                    echo("<td nowrap align='right' class='flp'>" . $res_act[$j]['allo_rate'] . "</td>\n");
                            }
                            echo "</tr>\n";
                            echo "</table>\n";
                        }
                    }
                }
            }
        }
    ?>
    </center>
</body>
</html>
