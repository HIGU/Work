<?php
//////////////////////////////////////////////////////////////////////////
//  経理部門コード・配賦率の保守                                        //
//  2002/09/17   Copyright(C) K.Kobayashi k_kobayashi@tnk.co.jp         //
//  変更経歴                                                            //
//  2002/09/17 新規作成                                                 //
//  2002/09/20 サイトメニューに追加                                     //
//  2002/09/28 配賦率テーブルを act_allocation & allocation_item に変更 //
//  2002/10/05 現在は使用していない act_table_mnt_new.php に変更        //
//  2003/02/26 body に onLoad を追加し初期入力個所に focus() させた     //
//////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);   // E_ALL='2047' debug 用
// ini_set('display_errors','1');      // Error 表示 ON debug 用 リリース後コメント
session_start();                    // ini_set()の次に指定すること Script 最上行
require_once ("../function.php");
access_log();       // Script Name は自動取得
$_SESSION["site_index"] = 10;       // 最後のメニューにするため 10 を使用
$_SESSION["site_id"] = 1;       // とりあえず下位メニュー無し (0 < であり)
if ( !isset($_SESSION["User_ID"]) || !isset($_SESSION["Password"]) || !isset($_SESSION["Auth"]) ) {
// if($_SESSION["Auth"] <= 2){
    $_SESSION["s_sysmsg"] = "認証されていないか認証期限が切れています｡<br>認証からやり直して下さい｡";
    header("Location: http:" . WEB_HOST . "index.php");
    exit();
}
if (!isset($_POST['act_sel'])) {        // 設定されていない時の初期化
    $_POST['act_sel'] = "";
}
if (!isset($_POST['act_id'])) {         // 設定されていない時の初期化
    $_POST['act_id'] = "";
}
$today = date("Y-m-d");
$query = "select count(*) from act_table";
$res = array();
if ( ($rows=getResult($query,$res)) >= 1) {
    $maxrows = $res[0][0];
}
define("PAGE","4");
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
///////////////////////////////////////// act_add 追加 処理
if ( isset($_POST['act_add']) ) {
    $query = "insert into act_table values (";
    $query .= $_POST['act_id'] . ",'" . $_POST['act_name'] . "','" . $_POST['s_name'] . "','$today',NULL,'t',";
    if ($_POST['s_g_exp'] == "")
        $query .= "NULL,";
    else
        $query .= $_POST['s_g_exp'] . ",";
    if($_POST['c_exp'] == "")
        $query .= "NULL,";
    else
        $query .= $_POST['c_exp'] . ",";
    if($_POST['l_exp'] == "")
        $query .= "NULL,";
    else
        $query .= $_POST['l_exp'] . ",";
    if($_POST['shoukan'] == "")
        $query .= "NULL,";
    else
        $query .= $_POST['shoukan'] . ",";
    if($_POST['c_assy'] == "")
        $query .= "NULL,";
    else
        $query .= $_POST['c_assy'] . ",";
    if($_POST['s_toku'] == "")
        $query .= "NULL,";
    else
        $query .= $_POST['s_toku'] . ",";
    if($_POST['s_1_nc'] == "")
        $query .= "NULL,";
    else
        $query .= $_POST['s_1_nc'] . ",";
    if($_POST['s_1_6'] == "")
        $query .= "NULL,";
    else
        $query .= $_POST['s_1_6'] . ",";
    if($_POST['s_4_nc'] == "")
        $query .= "NULL,";
    else
        $query .= $_POST['s_4_nc'] . ",";
    if($_POST['s_5_pf'] == "")
        $query .= "NULL,";
    else
        $query .= $_POST['s_5_pf'] . ",";
    if($_POST['s_5_2'] == "")
        $query .= "NULL,";
    else
        $query .= $_POST['s_5_2'] . ",";
    if($_POST['shape'] == "")
        $query .= "NULL)";
    else
        $query .= $_POST['shape'] . ")";
    
    $res = array();
    if(($rows=getResult($query,$res))>=0)
        $_SESSION['s_sysmsg'] = $_POST['act_id'] . " : " . $_POST['act_name'] . "を登録しました。";
    else
        $_SESSION['s_sysmsg'] = $res[0][0];
    // ----------------------------------- offset 値をSET
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
    // ----------------------------------- offset 値 END
}
///////////////////////////////////////// act_chg 変更 処理
if ( isset($_POST['act_chg']) ) {
    $query = "update act_table set act_name='" . $_POST['act_name'] . "', s_name='" . $_POST['s_name'] . "',date_chg='$today', s_g_exp=";
    if($_POST['s_g_exp'] == "")
        $query .= "NULL, c_exp=";
    else
        $query .= $_POST['s_g_exp'] . ", c_exp=";
    if($_POST['c_exp'] == "")
        $query .= "NULL, l_exp=";
    else
        $query .= $_POST['c_exp'] . ", l_exp=";
    if($_POST['l_exp'] == "")
        $query .= "NULL, shoukan=";
    else
        $query .= $_POST['l_exp'] . ", shoukan=";
    if($_POST['shoukan'] == "")
        $query .= "NULL, c_assy=";
    else
        $query .= $_POST['shoukan'] . ", c_assy=";
    if($_POST['c_assy'] == "")
        $query .= "NULL, s_toku=";
    else
        $query .= $_POST['c_assy'] . ", s_toku=";
    if($_POST['s_toku'] == "")
        $query .= "NULL, s_1_nc=";
    else
        $query .= $_POST['s_toku'] . ", s_1_nc=";
    if($_POST['s_1_nc'] == "")
        $query .= "NULL, s_1_6=";
    else
        $query .= $_POST['s_1_nc'] . ", s_1_6=";
    if($_POST['s_1_6'] == "")
        $query .= "NULL, s_4_nc=";
    else
        $query .= $_POST['s_1_6'] . ", s_4_nc=";
    if($_POST['s_4_nc'] == "")
        $query .= "NULL, s_5_pf=";
    else
        $query .= $_POST['s_4_nc'] . ", s_5_pf=";
    if($_POST['s_5_pf'] == "")
        $query .= "NULL, s_5_2=";
    else
        $query .= $_POST['s_5_pf'] . ", s_5_2=";
    if($_POST['s_5_2'] == "")
        $query .= "NULL, shape=";
    else
        $query .= $_POST['s_5_2'] . ", shape=";
    if($_POST['shape'] == "")
        $query .= "NULL ";
    else
        $query .= $_POST['shape'] . " ";
    
    $query .= "where act_id=" . $_POST['act_id'];
    $res = array();
    if ( ($rows=getResult($query,$res)) >= 0)
        $_SESSION['s_sysmsg'] = $_POST['act_id'] . " : " . $_POST['act_name'] . "を変更しました。";
    else
        $_SESSION['s_sysmsg'] = $res[0][0];
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
}
///////////////////////////////////////// act_del 変更 処理
if ( isset($_POST['act_del']) ) {
    $query = "delete from act_table where act_id=";
    $query .= $_POST['act_id'] ;
    $res=array();
    if(($rows=getResult($query,$res))>=0)
        $_SESSION['s_sysmsg'] = $_POST['act_id'] . "を削除しました。";
    else
        $_SESSION['s_sysmsg'] = $res[0][0];
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
*/      // ----------------------------------- offset 値 END
}
///////////////////////////////////////// act_flg 変更 処理(直接・間接部門)
if ( isset($_POST['act_flg']) ) {
    $res = array();
    if ($_POST['act_flg'] == "直接部門") {
        $query = "update act_table set act_flg='f' where act_id=" . $_POST['act_id'];
        if ( ($rows=getResult($query,$res)) >= 0) {
            $_SESSION['s_sysmsg'] = $_POST['act_id'] . "を間接部門に変更しました｡";
        } else {
            $_SESSION['s_sysmsg'] = $res[0][0];
        }
    } else {
        $query = "update act_table set act_flg='t' where act_id=" . $_POST['act_id'];
        if ( ($rows=getResult($query,$res)) >= 0) {
            $_SESSION['s_sysmsg'] = $_POST['act_id'] . "を直接部門に変更しました｡";
        } else {
            $_SESSION['s_sysmsg'] = $res[0][0];
        }
    }
}
///////////////////////////////////////// rate_flg 変更 処理(機械賃率対象外(0)or(NULL) 対象(1) 管理配賦(2))
if ( isset($_POST['rate_flg']) ) {
    $res = array();
    if ($_POST['rate_flg'] == "機械賃率除外") {
        $query = "update act_table set rate_flg='1' where act_id=" . $_POST['act_id'];
        if ( ($rows=getResult($query,$res)) >= 0) {
            $_SESSION['s_sysmsg'] = $_POST['act_id'] . "を機械賃率対象に変更しました｡";
        } else {
            $_SESSION['s_sysmsg'] = $res[0][0];
        }
    } elseif ($_POST['rate_flg'] == "機械賃率対象") {
        $query = "update act_table set rate_flg='2' where act_id=" . $_POST['act_id'];
        if ( ($rows=getResult($query,$res)) >= 0) {
            $_SESSION['s_sysmsg'] = $_POST['act_id'] . "を賃率管理配賦に変更しました｡";
        } else {
            $_SESSION['s_sysmsg'] = $res[0][0];
        }
    } else {
        $query = "update act_table set rate_flg='0' where act_id=" . $_POST['act_id'];
        if ( ($rows=getResult($query,$res)) >= 0) {
            $_SESSION['s_sysmsg'] = $_POST['act_id'] . "を機械賃率除外に変更しました｡";
        } else {
            $_SESSION['s_sysmsg'] = $res[0][0];
        }
    }
}
?>

<!DOCTYPE html>
<HTML>
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset=UTF-8">
<META http-equiv="Content-Style-Type" content="text/css">
<TITLE>経理部門コード・配賦率の保守</TITLE>
<script language='JavaScript'>
    <!--
    parent.menu_site.location = 'http:<?php echo(WEB_HOST) ?>menu_site.php';
    // -->
</script>
<script language='JavaScript' src='act_table_mnt.js'>
</script>
<style type="text/css">
    <!--
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
    .r_w    {background-color:red; color:white;}
    .b_w    {background-color:blue; color:white;}
    .fsp    {font-size:8pt;}
    .fmp    {font-size:10pt;}
    .flp    {font-size:12pt;}
    .fllbp  {font-size:16pt;font-weight:bold;}
    input.blue      {color:blue;}
    input.red       {color:red;}
    -->
</style>
</HEAD>
<BODY class='margin0' onLoad='document.select_form.act_id.focus()'>
    <center>
        <table width=100% border='1' cellspacing='1' cellpadding='1'>
            <tr>
                <form method='post' action='kessan_menu.php'>
                    <td width='60' bgcolor='blue' align='center' valign='center'>
                        <input class='pt12b' type='submit' name='return' value='戻る'>
                    </td>
                </form>
                <td bgcolor='#d6d3ce' align='center' class='fllbp'>
                    経理部門コード・配賦率のメンテナンス
                </td>
                <td bgcolor='#d6d3ce' align='center' width='80' nowrap>
                    <?php echo $today ?>
                </td>
            </tr>
        </table>
        <form name='select_form' method='post' action='act_table_mnt.php' onSubmit='return act_id_chk(this)'>
            <table bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
                <tr>
                    <td align='left' nowrap>
                        区分を選択して部門コードを入力し実行ボタンを押して下さい｡<br>
                        <table width='100%' align='center' border='1' cellspacing='0' cellpadding='2'>
                            <tr align='center'>
                                <td nowrap <?php if($_POST['act_sel']=="add") echo "class='b_w'" ?>><input type='radio' name='act_sel' value='add' id='add'
                                    <?php if($_POST['act_sel']=="add") echo(" checked") ?>><label for='add'>追加</label>
                                </td>
                                <td nowrap <?php if($_POST['act_sel']=="chg") echo "class='b_w'" ?>><input type='radio' name='act_sel' value='chg' id='chg'
                                    <?php if($_POST['act_sel']=="chg") echo(" checked") ?>><label for='chg'>変更</label>
                                </td>
                                <td nowrap <?php if($_POST['act_sel']=="del") echo "class='b_w'" ?>><input type='radio' name='act_sel' value='del' id='del'
                                    <?php if($_POST['act_sel']=="del") echo(" checked") ?>><label for='del'>削除</label>
                                </td>
                        </table>
                        <div align='center'>
                            部門コード<input type='text' name='act_id' size='7' maxlength='6' value='<?php echo $_POST['act_id'] ?>'>
                            <input type='submit' name='edit' value='実行' >
                        </div>
                    </td>
                </tr>
            </table>
        </form>
    <?php
        if($_POST['act_sel'] == "add"){
            $query = "select act_id from act_table where act_id=" . $_POST['act_id'];
            $res = array();
            if(($rows=getResult($query,$res))>=1){      // 登録済みのチェック
                echo "<table bgcolor='#d6d3ce' align='center' cellspacing='2' cellpadding='3' border='1'>\n";
                echo "  <tr>\n";
                echo "      <td class='r_b' nowrap>\n";
                echo "          部門コードが既に登録済みです｡変更又は削除を実行して下さい｡\n";
                echo "      </td>\n";
                echo "  </tr>\n";
                echo "</table>\n";
            }else{
                echo "<form method='post' action='act_table_mnt.php' onSubmit='return act_chk(this)'>\n";
                echo "<table bgcolor='#d6d3ce' border='1' cellspacing='1' cellpadding='2'>\n";
                echo "  <th nowrap>コード</th><th nowrap>部 門 名</th><th nowrap>短縮名</th><th nowrap>カプラ</th><th nowrap>リニア</th>
                        <th nowrap>販管費</th><th nowrap>商管</th><th colspan='5' align='left'>大分類</th>\n";
                echo "  <tr align='center'>\n";
                echo "      <td>" . $_POST['act_id'] . "</td>\n";
                echo "          <input type='hidden' name='act_id' value='" . $_POST['act_id'] . "'>\n";
                echo "      <td><input type='text' name='act_name' size='23' maxlength='20'></td>\n";
                echo "      <td><input type='text' name='s_name' size='9' maxlength='8'></td>\n";
                echo "      <td><input type='text' name='c_exp' size='4' maxlength='3'></td>\n";
                echo "      <td><input type='text' name='l_exp' size='4' maxlength='3'></td>\n";
                echo "      <td><input type='text' name='s_g_exp' size='4' maxlength='3'></td>\n";
                echo "      <td><input type='text' name='shoukan' size='4' maxlength='3'></td>\n";
                echo "      <td colspan='5' align='left'>全体で100%になるように</td>\n";
                echo "  </tr>\n";
                echo "  <th colspan='3' align='right'>カプラの内訳 配賦率</th><th nowrap>Ｃ組立</th><th nowrap>製造特</th><th nowrap>製1-NC</th>
                        <th nowrap>製1-6</th><th nowrap>製4-NC</th><th nowrap>製5-PF</th><th nowrap>製5-2</th><th nowrap>成型機</th><th class='b_w'>登録</th>\n";
                echo "  <tr align='center'>\n";
                echo "      <td colspan='3' align='right'>全体で100%になるように</td>\n";
                echo "      <td><input type='text' name='c_assy' size='4' maxlength='3'></td>\n";
                echo "      <td><input type='text' name='s_toku' size='4' maxlength='3'></td>\n";
                echo "      <td><input type='text' name='s_1_nc' size='4' maxlength='3'></td>\n";
                echo "      <td><input type='text' name='s_1_6' size='4' maxlength='3'></td>\n";
                echo "      <td><input type='text' name='s_4_nc' size='4' maxlength='3'></td>\n";
                echo "      <td><input type='text' name='s_5_pf' size='4' maxlength='3'></td>\n";
                echo "      <td><input type='text' name='s_5_2' size='4' maxlength='3'></td>\n";
                echo "      <td><input type='text' name='shape' size='4' maxlength='3'></td>\n";
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
                echo "<form method='post' action='act_table_mnt.php' onSubmit='return act_chk(this)'>\n";
                echo "<table bgcolor='#d6d3ce' border='1' cellspacing='1' cellpadding='2'>\n";
                echo "  <th nowrap>コード</th><th nowrap>部 門 名</th><th nowrap>短縮名</th><th nowrap>カプラ</th><th nowrap>リニア</th>
                        <th nowrap>販管費</th><th nowrap>商管</th><th colspan='5' align='left'>大分類</th>\n";
                echo "  <tr align='center'>\n";
                echo "      <td>" . $_POST['act_id'] . "</td>\n";
                echo "          <input type='hidden' name='act_id' value='" . $_POST['act_id'] . "'>\n";
                echo "      <td><input type='text' name='act_name' size='23' maxlength='20' value='" . $res[0]['act_name'] . "'></td>\n";
                echo "      <td><input type='text' name='s_name' size='9' maxlength='8' value='" . $res[0]['s_name'] . "'></td>\n";
                echo "      <td><input type='text' name='c_exp' size='4' maxlength='3' value='" . $res[0]['c_exp'] . "'></td>\n";
                echo "      <td><input type='text' name='l_exp' size='4' maxlength='3' value='" . $res[0]['l_exp'] . "'></td>\n";
                echo "      <td><input type='text' name='s_g_exp' size='4' maxlength='3' value='" . $res[0]['s_g_exp'] . "'></td>\n";
                echo "      <td><input type='text' name='shoukan' size='4' maxlength='3' value='" . $res[0]['shoukan'] . "'></td>\n";
                echo "      <td colspan='5' align='left'>全体で100%になるように</td>\n";
                echo "  </tr>\n";
                echo "  <th colspan='3' align='right'>カプラの内訳 配賦率</th><th nowrap>Ｃ組立</th><th nowrap>製造特</th><th nowrap>製1-NC</th>
                        <th nowrap>製1-6</th><th nowrap>製4-NC</th><th nowrap>製5-PF</th><th nowrap>製5-2</th><th nowrap>成型機</th><th class='b_w'>変更</th>\n";
                echo "  <tr align='center'>\n";
                echo "      <td colspan='3' align='right'>全体で100%になるように</td>\n";
                echo "      <td><input type='text' name='c_assy' size='4' maxlength='3' value='" . $res[0]['c_assy'] . "'></td>\n";
                echo "      <td><input type='text' name='s_toku' size='4' maxlength='3' value='" . $res[0]['s_toku'] . "'></td>\n";
                echo "      <td><input type='text' name='s_1_nc' size='4' maxlength='3' value='" . $res[0]['s_1_nc'] . "'></td>\n";
                echo "      <td><input type='text' name='s_1_6' size='4' maxlength='3' value='" . $res[0]['s_1_6'] . "'></td>\n";
                echo "      <td><input type='text' name='s_4_nc' size='4' maxlength='3' value='" . $res[0]['s_4_nc'] . "'></td>\n";
                echo "      <td><input type='text' name='s_5_pf' size='4' maxlength='3' value='" . $res[0]['s_5_pf'] . "'></td>\n";
                echo "      <td><input type='text' name='s_5_2' size='4' maxlength='3' value='" . $res[0]['s_5_2'] . "'></td>\n";
                echo "      <td><input type='text' name='shape' size='4' maxlength='3' value='" . $res[0]['shape'] . "'></td>\n";
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
                echo "<form method='post' action='act_table_mnt.php'>\n";
                echo "<table bgcolor='#d6d3ce' border='1' cellspacing='1' cellpadding='2'>\n";
                echo "  <th nowrap>コード</th><th nowrap>部 門 名</th><th nowrap>短縮名</th><th nowrap>カプラ</th><th nowrap>リニア</th>
                        <th nowrap>販管費</th><th nowrap>商管</th><th colspan='5' align='left'>大分類</th>\n";
                echo "  <tr align='center'>\n";
                echo "      <td>" . $_POST['act_id'] . "</td>\n";
                echo "          <input type='hidden' name='act_id' value='" . $_POST['act_id'] . "'>\n";
                echo "      <td><input type='text' name='act_name' size='23' maxlength='20' value='" . $res[0]['act_name'] . "'></td>\n";
                echo "      <td><input type='text' name='s_name' size='9' maxlength='8' value='" . $res[0]['s_name'] . "'></td>\n";
                echo "      <td><input type='text' name='c_exp' size='4' maxlength='3' value='" . $res[0]['c_exp'] . "'></td>\n";
                echo "      <td><input type='text' name='l_exp' size='4' maxlength='3' value='" . $res[0]['l_exp'] . "'></td>\n";
                echo "      <td><input type='text' name='s_g_exp' size='4' maxlength='3' value='" . $res[0]['s_g_exp'] . "'></td>\n";
                echo "      <td><input type='text' name='shoukan' size='4' maxlength='3' value='" . $res[0]['shoukan'] . "'></td>\n";
                echo "      <td colspan='5' align='left'>全体で100%になるように</td>\n";
                echo "  </tr>\n";
                echo "  <th colspan='3' align='right'>カプラの内訳 配賦率</th><th nowrap>Ｃ組立</th><th nowrap>製造特</th><th nowrap>製1-NC</th>
                        <th nowrap>製1-6</th><th nowrap>製4-NC</th><th nowrap>製5-PF</th><th nowrap>製5-2</th><th nowrap>成型機</th><th class='r_w'>削除</th>\n";
                echo "  <tr align='center'>\n";
                echo "      <td colspan='3' align='right'>全体で100%になるように</td>\n";
                echo "      <td><input type='text' name='c_assy' size='4' maxlength='3' value='" . $res[0]['c_assy'] . "'></td>\n";
                echo "      <td><input type='text' name='s_toku' size='4' maxlength='3' value='" . $res[0]['s_toku'] . "'></td>\n";
                echo "      <td><input type='text' name='s_1_nc' size='4' maxlength='3' value='" . $res[0]['s_1_nc'] . "'></td>\n";
                echo "      <td><input type='text' name='s_1_6' size='4' maxlength='3' value='" . $res[0]['s_1_6'] . "'></td>\n";
                echo "      <td><input type='text' name='s_4_nc' size='4' maxlength='3' value='" . $res[0]['s_4_nc'] . "'></td>\n";
                echo "      <td><input type='text' name='s_5_pf' size='4' maxlength='3' value='" . $res[0]['s_5_pf'] . "'></td>\n";
                echo "      <td><input type='text' name='s_5_2' size='4' maxlength='3' value='" . $res[0]['s_5_2'] . "'></td>\n";
                echo "      <td><input type='text' name='shape' size='4' maxlength='3' value='" . $res[0]['shape'] . "'></td>\n";
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
        $query = "select act_id,act_name,s_name,c_exp,l_exp,s_g_exp,shoukan,c_assy,s_toku,s_1_nc,s_1_6,s_4_nc,s_5_pf,s_5_2,shape ";
        $query .= "from act_table order by act_id ASC offset $offset limit " . PAGE;
        $res = array();
        if(($rows=getResult($query,$res))>=1){      // 
            echo "<hr>\n";
            echo "<table bgcolor='#d6d3ce' align='center' cellspacing='1' cellpadding='3' border='1'>\n";
            echo "  <form method='post' action='act_table_mnt.php'>\n";
            echo "  <caption>経理部門コード・配布率一覧\n";
            echo "      <input type='submit' name='backward' value='前頁'>\n";
            echo "      <input type='submit' name='forward' value='次頁'>\n";
            echo "  </caption>\n";
            echo "  </form>\n";
            $num = count($res[0]);
            for($r=0;$r<$rows;$r++){
                echo "  <th nowrap class='y_b'>No</th><th nowrap class='y_b'>コード</th><th nowrap class='y_b'>部 門 名</th><th nowrap class='y_b'>短縮名</th><th nowrap class='y_b'>カプラ</th><th nowrap class='y_b'>リニア</th>
                        <th nowrap class='y_b'>販管費</th><th nowrap class='y_b'>商管</th><th colspan='5' align='center'>-</th>\n";
                print("<tr>\n");
                echo "  <form method='post' action='act_table_mnt.php'>\n";
                print(" <td align='center'><input type='submit' name='copy' value='" . ($r + $offset + 1) . "'></td>\n");
                echo "      <input type='hidden' name='act_sel' value='chg'>\n";
                echo "      <input type='hidden' name='act_id' value='" . $res[$r][0] . "'>\n";
                echo "  </form>\n";
                for($n=0;$n<7;$n++){
                    if($res[$r][$n] == "")
                        echo("<td nowrap align='center'>---</td>\n");
                    else
                        if($n >= 1 && $n <= 2)
                            echo("<td nowrap align='left' class='fmp'>" . $res[$r][$n] . "</td>\n");
                        else
                            echo("<td nowrap align='center' class='flp'>" . $res[$r][$n] . "</td>\n");
                }
                echo "<td colspan='3' align='center'>-</td>\n";
                print("</tr>\n");
                echo "  <form method='post' action='act_table_mnt.php'>\n";
                $query = "select act_flg from act_table where act_id=" . $res[$r]['act_id'];
                $res_flg = array();
                if(($rows_flg=getResult($query,$res_flg))>=1){      // 直接・間接部門取得
                    if($res_flg[0]['act_flg'] == 't')
                        echo "  <th colspan='3'><input type='submit' name='act_flg' value='直接部門' class='blue'></th>\n";
                    else
                        echo "  <th colspan='3'><input type='submit' name='act_flg' value='間接部門' class='red'></th>\n";
                }
                echo "<input type='hidden' name='act_id' value='" . $res[$r]['act_id'] . "'>\n";
                echo "  </form>\n";
                echo "<th nowrap class='y_b'>Ｃ組立</th><th nowrap class='y_b'>製造特</th><th nowrap class='y_b'>製1-NC</th>
                        <th nowrap class='y_b'>製1-6</th><th nowrap class='y_b'>製4-NC</th><th nowrap class='y_b'>製5-PF</th><th nowrap class='y_b'>製5-2</th><th nowrap class='y_b'>成型機</th>\n";
                print("<tr>\n");
                for($n=7;$n<15;$n++){
                    if($n == 7){
                        echo "  <form method='post' action='act_table_mnt.php'>\n";
                        $query = "select rate_flg from act_table where act_id=" . $res[$r]['act_id'];
                        $res_rate = array();
                        if(($rows_rate=getResult($query,$res_rate))>=1){    // 機械賃率除外・対象・賃率管理配賦の取得
                            if($res_rate[0]['rate_flg'] == '1')
                                echo "  <td colspan='3' align='center'><input type='submit' name='rate_flg' value='機械賃率対象' class='blue'></td>\n";
                            else if($res_rate[0]['rate_flg'] == '2')
                                echo "  <td colspan='3' align='center'><input type='submit' name='rate_flg' value='賃率管理配賦' class='red'></td>\n";
                            else
                                echo "  <td colspan='3' align='center'><input type='submit' name='rate_flg' value='機械賃率除外'></td>\n";
                        }
                        echo "<input type='hidden' name='act_id' value='" . $res[$r]['act_id'] . "'>\n";
                        echo "  </form>\n";
                    }
                    if($res[$r][$n] == "")
                        echo("<td nowrap align='center'>---</td>\n");
                    else
                        echo("<td nowrap align='center' class='flp'>" . $res[$r][$n] . "</td>\n");
                }
                print("</tr>\n");
            }
            print("</table>\n");
        }
    ?>
    </center>
</BODY>
</HTML>
