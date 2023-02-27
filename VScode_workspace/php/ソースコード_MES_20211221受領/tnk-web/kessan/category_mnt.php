<?php
//////////////////////////////////////////////////////////////////////////////
// カテゴリ(大分類項目)マスターの保守 (category_item cate_allocation)       //
// Copyright (C) 2002-2009 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2002/10/02 Created   category_mnt.php                                    //
// 2002/11/26 UPDATE の際に dest_id のみの条件指定を cate_id も追加         //
// 2003/05/15 $_SESSION['cate_id'] = ""; をコメント ロジックミス            //
//            if($_POST['cate_rate'] == "" の条件追加 ブランク時の対応      //
// 2004/10/28 user_check()functionを追加し編集出来るユーザーを限定          //
// 2005/11/02 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2007/04/18 ユーザーに300144大谷を追加 大谷                               //
// 2009/03/12 ユーザーに014737桝を追加 桝                                   //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../function.php');           // define.php と pgsql.php を require_once している
require_once ('../MenuHeader.php');         // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
    // 実際の認証はprofit_loss_submit.phpで行っているaccount_group_check()を使用

////////////// サイト設定
$menu->set_site(INDEX_PL, 11);              // site_index=INDEX_PL(損益メニュー) site_id=11(大分類配布率)
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('大分類項目マスター・部門のメンテナンス');

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
        $_SESSION['s_sysmsg'] = "社員番号：{$uid}：{$name}さんではカテゴリーマスターの保守は出来ません！ 管理担当者へ連絡して下さい。";
        return FALSE;
    }
}

$today = date("Y-m-d");
if (isset($_POST['cate_id1'])) {
    $_SESSION['cate_id'] = $_POST['cate_id1'];
} elseif (isset($_POST['cate_id2'])) {
    $_SESSION['cate_id'] = $_POST['cate_id2'];
} elseif (isset($_POST['cate_id'])) {
    $_SESSION['cate_id'] = $_POST['cate_id'];
} else {
    //  $_SESSION['cate_id'] = "";
    $current_script  = $_SERVER['PHP_SELF'];        // 現在実行中のスクリプト名を保存
    $url_referer = $_SERVER["HTTP_REFERER"];        // 呼出もとのURLを保存
    if (!eregi($current_script, $url_referer)) {    // 自分自身で呼び出していなければ
        $_SESSION['cate_id'] = "";                  // ブランクに初期化
    }
}

////////////////////////// cate_allocation の 変更 UPDATE
while ( isset($_POST['dest_update']) ) {
    if (!user_check($uid)) break;
    $query = "select dest_id from cate_allocation where dest_id=" . $_POST['dest_update'];
    $res_chg = array();
    if((getResult($query,$res_chg))==0) /////////// 先に他のユーザーに削除されたかのチェック
        $_SESSION['s_sysmsg'] = "先に他のユーザーによって削除されましたので変更出来ません!";
    else{
        if ($_POST['cate_rate'] == "") {
            $_POST['cate_rate'] = 0;
        }
        $query = "update cate_allocation set cate_rate=" . $_POST['cate_rate'] . ",";
        $query .= "group_id=" . $_POST['group_id'] . " ";
        $query .= "where dest_id=" . $_POST['dest_update'];
        $query .= " and cate_id=" . $_POST['cate_id'];
        if((getResult($query,$res_chg))>=0)
            $_SESSION['s_sysmsg'] = $_POST['dest_update'] . " : を変更しました!";
        else
            $_SESSION['s_sysmsg'] = $_POST['dest_update'] . " : を変更に失敗しました!";
    }
    break;
}
////////////////////////// cate_allocation へ 全部門 一括 登録
while ( isset($_POST['all_add']) ) {
    if (!user_check($uid)) break;
    $res_add = array();
    $query = "select act_id,s_name from act_table order by act_id ASC";
    $res = array();
    $rows_act = getResult($query,$res);
    for ($a=0; $a<$rows_act; $a++) {
        $query = "select cate_id from cate_allocation where cate_id=" . $_SESSION['cate_id'] . " and dest_id=" . $res[$a]['act_id'];
        if ( (getResult($query,$res_add)) == 0) { /////////// 登録済のチェック
            $query = "insert into cate_allocation (orign_id,cate_id,group_id,dest_id) values(";
            $query .= $_SESSION['cate_id'] . ",";
            $query .= $_SESSION['cate_id'] . ",";
            $query .= $_SESSION['cate_id'] . ",";
            $query .= $res[$a]['act_id'] . ")";
            if ( (getResult($query,$res_add)) >= 0)
                $_SESSION['s_sysmsg'] .= $res[$a]['act_id'] . "：" . $res[$a]['s_name'] . "を登録<br>";
            else
                $_SESSION['s_sysmsg'] .= $res[$a]['act_id'] . "：" . $res[$a]['s_name'] . "を登録失敗<br>";
        }
    }
    break;
}
////////////////////////// cate_allocation へ 全部門 一括 削除
while ( isset($_POST['all_del']) ) {
    if (!user_check($uid)) break;
    $query = "select cate_item from category_item where cate_id=" . $_SESSION['cate_id'];
    $res = array();
    $rows = getResult($query,$res);
    $query = "select cate_id from cate_allocation where cate_id=" . $_SESSION['cate_id'];
    $res_del = array();
    if ( (getResult($query,$res_del)) >= 1) { /////////// 削除済のチェック
        $query = "delete from cate_allocation where cate_id=" . $_SESSION['cate_id'];
        if ( (getResult($query,$res_del)) >= 0)
            $_SESSION['s_sysmsg'] = $_SESSION['cate_id'] . "：" . $res[0]['cate_item'] . "の部門全て削除しました";
        else
            $_SESSION['s_sysmsg'] = $_SESSION['cate_id'] . "：" . $res[0]['cate_item'] . "の部門 削除 失敗";
    }
    break;
}
////////////////////////// cate_allocation へ 個別 追加
while (isset($_POST['allo_add'])) {
    if (!user_check($uid)) break;
    $res_add = array();
    $query = "select cate_id from cate_allocation where cate_id=" . $_SESSION['cate_id'] . " and dest_id=" . $_POST['act_id'];
    if((getResult($query,$res_add))==0){ /////////// 登録済のチェック
        $query = "insert into cate_allocation (orign_id,cate_id,group_id,dest_id) values(";
        $query .= $_SESSION['cate_id'] . ",";
        $query .= $_SESSION['cate_id'] . ",";
        $query .= $_SESSION['cate_id'] . ",";
        $query .= $_POST['act_id'] . ")";
        if((getResult($query,$res_add))>=0)
            $_SESSION['s_sysmsg'] = $_POST['act_id'] . "を登録しました";
        else
            $_SESSION['s_sysmsg'] = $_POST['act_id'] . "を登録失敗";
    }else
        $_SESSION['s_sysmsg'] = $_POST['act_id'] . "：は他のユーザーに登録されました!";
    break;
}
////////////////////////// cate_allocation へ 個別 削除
while (isset($_POST['allo_del'])) {
    if (!user_check($uid)) break;
    $res_del = array();
    $query = "select cate_id from cate_allocation where cate_id=" . $_SESSION['cate_id'] . " and dest_id=" . $_POST['dest_id'];
    if((getResult($query,$res_del))>=1){ /////////// 登録済のチェック
        $query = "delete from cate_allocation where cate_id=" . $_SESSION['cate_id'] . " and dest_id=" . $_POST['dest_id'];
        if((getResult($query,$res_add))>=0)
            $_SESSION['s_sysmsg'] .= $_POST['dest_id'] . "を削除しました";
        else
            $_SESSION['s_sysmsg'] .= $_POST['dest_id'] . "を削除失敗";
    }else
        $_SESSION['s_sysmsg'] = $_POST['dest_id'] . "：は他のユーザーに削除されました!";
    break;
}
////////////////////////// allocation_item へ 追加
while (isset($_POST['register'])) {
    if (!user_check($uid)) break;
    $query = "select cate_id from category_item where cate_id=" . $_SESSION['cate_id'];
    $res_add = array();
    if((getResult($query,$res_add))>=1) /////////// 先に他のユーザーに登録されたかのチェック
        $_SESSION['s_sysmsg'] = "先に他のユーザーに登録されましたので やり直して下さい!";
    else{
        $query = "insert into category_item (cate_id,cate_item,cate_note,cate_group) values(";
        $query .= $_SESSION['cate_id'] . ",";
        $query .= "'" . $_POST['cate_item'] . "',";
        $query .= "'" . $_POST['cate_note'] . "',";
        $query .= $_POST['cate_group'] . ")";
        if((getResult($query,$res_add))>=0)
            $_SESSION['s_sysmsg'] = $_POST['cate_item'] . "を" . $_SESSION['cate_id'] . "番で登録しました。";
        else
            $_SESSION['s_sysmsg'] = $_POST['cate_item'] . " : を登録に失敗しました!";
    }
    break;
}
////////////////////////// allocation_item へ 変更
while (isset($_POST['change'])) {
    if (!user_check($uid)) break;
    $query = "select cate_id from category_item where cate_id=" . $_SESSION['cate_id'];
    $res_chg = array();
    if((getResult($query,$res_chg))==0) /////////// 先に他のユーザーに削除されたかのチェック
        $_SESSION['s_sysmsg'] = "先に他のユーザーによって削除されましたので変更出来ません!";
    else{
        $query = "update category_item set cate_item='" . $_POST['cate_item'] . "',";
        $query .= "cate_note='" . $_POST['cate_note'] . "',";
        $query .= "cate_group=" . $_POST['cate_group'] . " ";
        $query .= "where cate_id=" . $_SESSION['cate_id'];
        if((getResult($query,$res_chg))>=0)
            $_SESSION['s_sysmsg'] = $_SESSION['cate_id'] . "：" . $_POST['cate_item'] . " : を変更しました。";
        else
            $_SESSION['s_sysmsg'] = $_POST['cate_item'] . " : を変更に失敗しました!";
    }
    break;
}
////////////////////////// category_item へ 削除
while (isset($_POST['delete'])) {
    if (!user_check($uid)) break;
    $query = "select cate_id from category_item where cate_id=" . $_SESSION['cate_id'];
    $res_del = array();
    if((getResult($query,$res_del))==0) /////////// 先に他のユーザーに削除されたかのチェック
        $_SESSION['s_sysmsg'] = "先に他のユーザーによって削除されましたので削除出来ません!";
    else{
        $query = "delete from category_item where cate_id=" . $_SESSION['cate_id'];
        if((getResult($query,$res_del))>=0)
            $_SESSION['s_sysmsg'] = $_SESSION['cate_id'] . "：" . $_POST['cate_item'] . " : を削除しました。";
        else
            $_SESSION['s_sysmsg'] = $_POST['cate_item'] . " : を削除に失敗しました!";
    }
    break;
}
///////////////////////////////////////////////////////////// 一覧表(既存のデータ表示)
///////// 項目グループ
$query = "select cate_id,cate_item from category_item where cate_id<=100 order by cate_group ASC, cate_id ASC";
$res_item = array();
$cate_id1 = array();
$cate_item1 = array();
if($rows_item1 = getResult($query,$res_item)){
    for($i=0;$i<$rows_item1;$i++){
        $cate_id1[$i] = $res_item[$i]['cate_id'];
        $cate_item1[$i] = $res_item[$i]['cate_item'];
    }
}
///////// ユーザー設定グループ
$query = "select cate_id,cate_item from category_item where cate_id>=101 order by cate_group ASC, cate_id ASC";
$res_item = array();
$cate_id2 = array();
$cate_item2 = array();
if($rows_item2 = getResult($query,$res_item)){
    for($i=0;$i<$rows_item2;$i++){
        $cate_id2[$i] = $res_item[$i]['cate_id'];
        $cate_item2[$i] = $res_item[$i]['cate_item'];
    }
}
/////////////////////////// 項目グループの新規登録のID 生成
$query = "select max(cate_id) from category_item where cate_id<=100";
$res_max = array();
if($rows_max = getResult($query,$res_max)){
    if($res_max[0]['max'] < 100) ////////////// 〜100 までは項目グループで予約済み
        $cate_id_max1 = ($res_max[0]['max'] + 1);
    else
        $_SESSION['s_sysmsg'] = "予約済みのIDは全て使いました!管理者に連絡して下さい!";
}
/////////////////////////// ユーザー設定グループの新規登録のID 生成
$query = "select max(cate_id) from category_item";
$res_max = array();
if($rows_max = getResult($query,$res_max)){
    if($res_max[0]['max'] >= 101) ////////////// 101〜 ユーザー設定ID
        $cate_id_max2 = ($res_max[0]['max'] + 1);
    else
        $cate_id_max2 = 101;
}
////////////////////////// 追加・変更・削除の選択
if(isset($_POST['select'])){
    $query = "select cate_item,cate_note,cate_group from category_item where cate_id=" . $_SESSION['cate_id'];
    $res_chk = array();
    if($rows_chk = getResult($query,$res_chk)){
        $chg_del = 1; ////// 変更・削除 (データあり)
        $cate_id = $_SESSION['cate_id'];
        $cate_item = $res_chk[0]['cate_item'];
        $cate_note = $res_chk[0]['cate_note'];
        $cate_group = $res_chk[0]['cate_group'];
    }else
        $add = 1; ////////// 追加 (データ無し)
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

<link rel='stylesheet' href='allocation.css' type='text/css'>
</head>
<body>
    <center>
<?= $menu->out_title_border() ?>
        <table bgcolor='#d6d3ce' cellpadding='5' border='1'>
            <tr>
                <form action='category_mnt.php' method='post'>
                    <td align='center'>
                        項目グループ
                        <select name='cate_id1' class='pt11b'>
                            <?php
                            for($i=0;$i<$rows_item1;$i++){
                                if($_SESSION['cate_id'] == $cate_id1[$i])
                                    echo "<option value='" . $cate_id1[$i] . "' selected>" . $cate_item1[$i] . "</option>\n";
                                else
                                    echo "<option value='" . $cate_id1[$i] . "'>" . $cate_item1[$i] . "</option>\n";
                            }
                            if($_SESSION['cate_id'] == $cate_id_max1)
                                echo "<option value='$cate_id_max1' class='fc_red' selected>新規 追加</option>\n";
                            else
                                echo "<option value='$cate_id_max1' class='fc_red'>新規 追加</option>\n";
                            ?>
                        </select>
                    </td>
                    <td align='center'>
                        <input type='submit' name='select' value='実行' >
                    </td>
                </form>
                <form action='category_mnt.php' method='post'>
                    <td align='center'>
                        自由設定グループ
                        <select name='cate_id2' class='pt11b'>
                            <?php
                            for($i=0;$i<$rows_item2;$i++){
                                if($_SESSION['cate_id'] == $cate_id2[$i])
                                    echo "<option value='" . $cate_id2[$i] . "' selected>" . $cate_item2[$i] . "</option>\n";
                                else
                                    echo "<option value='" . $cate_id2[$i] . "'>" . $cate_item2[$i] . "</option>\n";
                            }
                            if($_SESSION['cate_id'] == $cate_id_max2)
                                echo "<option value='$cate_id_max2' class='fc_yellow' selected>新規 追加</option>\n";
                            else
                                echo "<option value='$cate_id_max2' class='fc_yellow'>新規 追加</option>\n";
                            ?>
                        </select>
                    </td>
                    <td align='center'>
                        <input type='submit' name='select' value='実行' >
                    </td>
                </form>
            </tr>
        </table>
        <?php
        if(isset($add)){ ////////////////////////////// 追加のブラウザー表示
        ?>
        <hr>
        <table bgcolor='#d6d3ce' cellpadding='5' border='1'>
            <form action='category_mnt.php' method='post'>
                <caption>新　規　登　録</caption>
                <th>分類項目名</th><th>備　考</th><th colspan='2'>グループ</th>
                <tr>
                    <td><input type='text' name='cate_item' value='' size='16' maxlength='10'></td>
                    <td><input type='text' name='cate_note' value='' size='80' maxlength='50'></td>
                    <?php
                    echo "<td><input type='text' name='cate_group' value='" . $_SESSION['cate_id'] . "' size='7' maxlength='6' class='right'></td>\n";
                    echo "<td><input type='submit' name='register' value='登録' ></td>\n";
                    ?>
                </tr>
            </form>
        </table>
        <?php
        }
        if(isset($chg_del)){ ////////////////////////////// 変更・削除のブラウザー表示
        ?>
        <hr>
        <table bgcolor='#d6d3ce' cellpadding='5' border='1'>
            <form action='category_mnt.php' method='post'>
                <caption>変更・削除</caption>
                <th>分類項目名</th><th>備　考</th><th>Group</th><th><input type='submit' name='delete' value='削除' class='fc_red'></th>
                <tr>
                    <td><input type='text' name='cate_item' value='<?php echo $cate_item ?>' size='16' maxlength='10'></td>
                    <td><input type='text' name='cate_note' value='<?php echo $cate_note ?>' size='80' maxlength='50'></td>
                    <td><input type='text' name='cate_group' value='<?php echo $cate_group ?>' size='7' maxlength='6' class='right'></td>
                    <td><input type='submit' name='change' value='変更' class='fc_blue'></td>
                </tr>
            </form>
        </table>
        <hr>
        <table bgcolor='#d6d3ce' cellpadding='5' border='1'>
            <form action='category_mnt.php' method='post'>
                <?php /////////////////////////////////////////////// 配布部門の追加・変更・削除
                echo "<input type='hidden' name='select' value='" . $_POST['select'] . "'>\n"; //// 再表示のため
                $query = "select s_name,act_id,orign_id,cate_id,dest_id,group_id,cate_rate from act_table left outer join cate_allocation on act_id=dest_id and cate_id=" . $_SESSION['cate_id'] . " order by group_id ASC, act_id ASC";    
                $res_allo = array();
                if(($rows_allo=getResult($query,$res_allo))==0){
                    echo "<div>部門が登録されていません。下のボタンで全部門を登録して必要ない部門は後から削除して下さい。</div>\n";
                    echo "<input type='submit' name='all_add' value='全部門登録'>\n";
                }else{
                    echo "<caption>部門のメンテナンス　<input type='submit' name='all_del' value='一括削除' class='fc_red'>\n";
                    echo "<input type='submit' name='all_add' value='一括登録' class='fc_blue'></caption>\n";
                    echo "<th>自部門</th><th>分類</th><th>登録・削除</th><th>配賦部門名</th><th>配賦率/コード</th><th>Group</th><th>部門/更新</th>\n";
                    echo "</form>\n";
                    for($a=0;$a<$rows_allo;$a++){
                        echo "<form action='category_mnt.php' method='post'>\n";
                        echo "<tr>\n";
                        if($res_allo[$a]['dest_id'] == ""){
                            echo "  <td align='center'>---</td>\n";
                            echo "  <td align='center'>---</td>\n";
                            echo "  <td align='center'><input type='submit' name='allo_add' value='登録' class='fc_blue'></td>\n";
                            echo "  <td>" . $res_allo[$a]['s_name'] . "</td>\n";
                            echo "  <td align='center'>---</td>\n";
                            echo "  <td align='center'>---</td>\n";
                            echo "  <td align='center'>" . $res_allo[$a]['act_id'] . "</td>\n";
                            echo "  <input type='hidden' name='act_id' value='" . $res_allo[$a]['act_id'] . "'>\n";
                            echo "  <input type='hidden' name='select' value='" . $_POST['select'] . "'>\n"; //// 再表示のため
                        }else{
                            echo "  <td align='right'>" . $res_allo[$a]['orign_id'] . "</td>\n";
                            echo "  <td align='right'>" . $res_allo[$a]['cate_id'] . "</td>\n";
                            echo "  <td align='center'><input type='submit' name='allo_del' value='削除' class='fc_red'></td>\n";
                            echo "  <td>" . $res_allo[$a]['s_name'] . "</td>\n";
                            echo "  <td align='center'><input type='text' name='cate_rate' value='" . $res_allo[$a]['cate_rate'] . "' size='5' maxlength='3' class='right'></td>\n";
                            echo "  <td align='right'><input type='text' name='group_id' value='" . $res_allo[$a]['group_id'] . "' size='5' maxlength='4' class='right'></td>\n";
                            echo "  <td align='center'><input type='submit' name='dest_update' value='" . $res_allo[$a]['dest_id'] . "' class='fc_blue'></td>\n";
                            echo "  <input type='hidden' name='dest_id' value='" . $res_allo[$a]['dest_id'] . "'>\n";
                            echo "  <input type='hidden' name='cate_id' value='" . $res_allo[$a]['cate_id'] . "'>\n";
                            echo "  <input type='hidden' name='select' value='" . $_POST['select'] . "'>\n"; //// 再表示のため
                        }
                        echo "</tr>\n";
                        echo "</form>\n";
                    }
                }
                ?>
        </table>
        <?php
        }
        ?>
    </center>
</body>
</html>
