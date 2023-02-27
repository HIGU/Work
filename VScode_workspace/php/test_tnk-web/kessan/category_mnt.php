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

$today = date("Y-m-d");
if (isset($_POST['cate_id1'])) {
    $_SESSION['cate_id'] = $_POST['cate_id1'];
} elseif (isset($_POST['cate_id2'])) {
    $_SESSION['cate_id'] = $_POST['cate_id2'];
} elseif (isset($_POST['cate_id'])) {
    $_SESSION['cate_id'] = $_POST['cate_id'];
} else {
    //  $_SESSION['cate_id'] = "";
}

///////////////////////////////////////////////////////////// 一覧表(既存のデータ表示)
///////// 項目グループ
$res_item = array();
$cate_id1 = array(
    '1',
    '2',
    '3',
    '4',
    '5',
);
$cate_item1 = array(
    'test1',
    'test2',
    'test3',
    'test4',
    'test5',
);

$cate_id2 = array(
    '1',
    '2',
    '3',
    '4',
    '5',
);
$cate_item2 = array(
    'dummy1',
    'dummy2',
    'dummy3',
    'dummy4',
    'dummy5',
);

$cate_id_max1 = 5;
$cate_id_max2 = 5;


/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
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
                            for($i=0;$i<5;$i++){
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
                            for($i=0;$i<5;$i++){
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
        if(FALSE){ ////////////////////////////// 追加のブラウザー表示
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
                    echo "<td><input type='text' name='cate_group' value='cate_id' size='7' maxlength='6' class='right'></td>\n";
                    echo "<td><input type='submit' name='register' value='登録' ></td>\n";
                    ?>
                </tr>
            </form>
        </table>
        <?php
        }
        else if(TRUE){ ////////////////////////////// 変更・削除のブラウザー表示
        ?>
        <hr>
        <table bgcolor='#d6d3ce' cellpadding='5' border='1'>
            <form action='category_mnt.php' method='post'>
                <caption>変更・削除</caption>
                <th>分類項目名</th><th>備　考</th><th>Group</th><th><input type='submit' name='delete' value='削除' class='fc_red'></th>
                <tr>
                    <td><input type='text' name='cate_item' value='<?php echo "cate_item" ?>' size='16' maxlength='10'></td>
                    <td><input type='text' name='cate_note' value='<?php echo "cate_note" ?>' size='80' maxlength='50'></td>
                    <td><input type='text' name='cate_group' value='<?php echo "cate_group" ?>' size='7' maxlength='6' class='right'></td>
                    <td><input type='submit' name='change' value='変更' class='fc_blue'></td>
                </tr>
            </form>
        </table>
        <hr>
        <table bgcolor='#d6d3ce' cellpadding='5' border='1'>
            <form action='category_mnt.php' method='post'>
                <?php /////////////////////////////////////////////// 配布部門の追加・変更・削除
                echo "<input type='hidden' name='select' value=''>\n"; //// 再表示のため
                echo "<caption>部門のメンテナンス　<input type='submit' name='all_del' value='一括削除' class='fc_red'>\n";
                echo "<input type='submit' name='all_add' value='一括登録' class='fc_blue'></caption>\n";
                echo "<th>自部門</th><th>分類</th><th>登録・削除</th><th>配賦部門名</th><th>配賦率/コード</th><th>Group</th><th>部門/更新</th>\n";
                echo "</form>\n";

                $res_allo = array(
                    ["s_name"=>"test1", "act_id"=>"1", "orign_id"=>"1", "cate_id"=>"1", "dest_id"=>"1", "group_id"=>"1", "cate_rate"=>"dummy"],
                    ["s_name"=>"test1", "act_id"=>"2", "orign_id"=>"2", "cate_id"=>"2", "dest_id"=>"2", "group_id"=>"2", "cate_rate"=>"dummy"],
                    ["s_name"=>"test1", "act_id"=>"3", "orign_id"=>"3", "cate_id"=>"3", "dest_id"=>"3", "group_id"=>"3", "cate_rate"=>"dummy"],
                    ["s_name"=>"test1", "act_id"=>"4", "orign_id"=>"4", "cate_id"=>"4", "dest_id"=>"4", "group_id"=>"4", "cate_rate"=>"dummy"],
                    ["s_name"=>"test1", "act_id"=>"5", "orign_id"=>"5", "cate_id"=>"5", "dest_id"=>"5", "group_id"=>"5", "cate_rate"=>"dummy"]
                );

                for($a=0;$a<5;$a++){
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
                        echo "  <input type='hidden' name='select' value=''>\n"; //// 再表示のため
                    }
                    echo "</tr>\n";
                    echo "</form>\n";
                    }
                ?>
        </table>
        <?php
        }
        ?>
    </center>
</body>
</html>
