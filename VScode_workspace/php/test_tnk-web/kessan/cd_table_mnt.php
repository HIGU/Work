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

$cd_sel = "del";

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
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
                                <td class='winbox caption_font' nowrap <?php if($cd_sel=="add") echo "class='y_b'" ?>><input type='radio' name='cd_sel' value='add' id='add'
                                    <?php if($cd_sel=="add") echo(" checked") ?>><label for='add'>追加</label>
                                </td>
                                <td class='winbox caption_font' nowrap <?php if($cd_sel=="chg") echo "class='y_b'" ?>><input type='radio' name='cd_sel' value='chg' id='chg'
                                    <?php if($cd_sel=="chg") echo(" checked") ?>><label for='chg'>変更</label>
                                </td>
                                <td class='winbox caption_font' nowrap <?php if($cd_sel=="del") echo "class='y_b'" ?>><input type='radio' name='cd_sel' value='del' id='del'
                                    <?php if($cd_sel=="del") echo(" checked") ?>><label for='del'>削除</label>
                                </td>
                            </tr>
                        </table>
                        <div align='center'>
                            社員番号<input type='text' name='uid' size='7' maxlength='6' value='<?php echo 12345 ?>'>
                            <input type='submit' name='edit' value='実行' >
                        </div>
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ダミーEnd ------------------>
        </form>
    <?php
        if($cd_sel == "add"){
            echo "<form method='post' action='cd_table_mnt.php'>\n";
            echo "<table bgcolor='#d6d3ce'  cellspacing='0' cellpadding='3' border='1'>\n";
            echo "<tr><td> <!----------- ダミー(デザイン用) ------------>\n";
            echo "<table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
            // echo "<table bgcolor='#d6d3ce' border='1' cellspacing='1' cellpadding='2'>\n";
            echo "  <th class='winbox'>社員No</th><th class='winbox'>氏名</th><th class='winbox'>経理</th><th class='winbox'>組織</th><th class='winbox'>人事</th>\n";
            echo "  <tr align='center'>\n";
            echo "      <td class='winbox'>test</td>\n";
            echo "      <td class='winbox'>dummy</td>\n";
            echo "          <input type='hidden' name='uid' value='test'>\n";
            echo "          <input type='hidden' name='name' value='dummy'>\n";
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
        if($cd_sel == "chg"){
            echo "<form method='post' action='cd_table_mnt.php'>\n";
            echo "<table bgcolor='#d6d3ce'  cellspacing='0' cellpadding='3' border='1'>\n";
            echo "<tr><td> <!----------- ダミー(デザイン用) ------------>\n";
            echo "<table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
            // echo "<table bgcolor='#d6d3ce' border='1' cellspacing='1' cellpadding='2'>\n";
            echo "  <th class='winbox'>社員No</th><th class='winbox'>氏名</th><th class='winbox'>経理</th><th class='winbox'>組織</th><th class='winbox'>人事</th><th class='winbox' align='center'>変更</th>\n";
            echo "  <tr align='center'>\n";
            echo "      <td class='winbox'>test</td>\n";
            echo "      <td class='winbox'>dummy</td>\n";
            echo "          <input type='hidden' name='uid' value='test'>\n";
            echo "          <input type='hidden' name='name' value='dummy'>\n";
            echo "      <td class='winbox'><input type='text' name='act_id' size='6' maxlength='5' value='800'></td>\n";
            echo "      <td class='winbox'><input type='text' name='orga_id' size='7' maxlength='6' value='800'></td>\n";
            echo "      <td class='winbox'><input type='text' name='pers_id' size='6' maxlength='5' value='800'></td>\n";
            echo "      <td class='winbox'><input type='submit' name='cd_chg' value='実行' >\n";
            echo "  </tr>\n";
            echo "</table>\n";
            echo "    </td></tr>\n";
            echo "</table> <!----------------- ダミーEnd ------------------>\n";
            echo "</form>\n";
        }
        if($cd_sel == "del"){
            $name = $res_name[0][0];
            echo "<form method='post' action='cd_table_mnt.php'>\n";
            echo "<table bgcolor='#d6d3ce'  cellspacing='0' cellpadding='3' border='1'>\n";
            echo "<tr><td> <!----------- ダミー(デザイン用) ------------>\n";
            echo "<table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
            // echo "<table bgcolor='#d6d3ce' border='1' cellspacing='1' cellpadding='2'>\n";
            echo "  <th class='winbox'>社員No</th><th class='winbox'>氏名</th><th class='winbox'>経理</th><th class='winbox'>組織</th><th class='winbox'>人事</th><th align='center' class='r_b winbox'>削除</th>\n";
            echo "  <tr align='center'>\n";
            echo "      <td class='winbox'>test</td>\n";
            echo "      <td class='winbox'>dummy</td>\n";
            echo "          <input type='hidden' name='uid' value='test'>\n";
            echo "          <input type='hidden' name='name' value='dummy'>\n";
            echo "      <td class='winbox'>800</td>\n";
            echo "      <td class='winbox'>800</td>\n";
            echo "      <td class='winbox'>800</td>\n";
            echo "      <td class='winbox'><input type='submit' name='cd_del' value='実行' >\n";
            echo "  </tr>\n";
            echo "</table>\n";
            echo "    </td></tr>\n";
            echo "</table> <!----------------- ダミーEnd ------------------>\n";
            echo "</form>\n";
        }
        // view 削除の必要な一覧表示
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

        $offset = 0;
        $res = array(
            [0, 1, 2, 3, 4, 5, 6, 7],
            [0, 1, 2, 3, 4, 5, 6, 7],
            [0, 1, 2, 3, 4, 5, 6, 7],
            [0, 1, 2, 3, 4, 5, 6, 7],
            [0, 1, 2, 3, 4, 5, 6, 7],
        );
        $rows = count($res);
        $num = count($res[0]);
        for($r=0;$r<$rows;$r++){
            print("<tr>\n");
            echo "  <form method='post' action='cd_table_mnt.php'>\n";
            print(" <td class='winbox' align='center'><input type='submit' name='copy' value='" . ($r + 1) . "'></td>\n");
            echo "      <input type='hidden' name='cd_sel' value='del'>\n";
            echo "      <input type='hidden' name='uid' value='test'>\n";
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
            echo "      <input type='hidden' name='uid' value='test'>\n";
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

        $res = array(
            [0, 1, 2, 3, 4, 5, 6, 7, 8],
            [0, 1, 2, 3, 4, 5, 6, 7, 8],
            [0, 1, 2, 3, 4, 5, 6, 7, 8],
            [0, 1, 2, 3, 4, 5, 6, 7, 8],
            [0, 1, 2, 3, 4, 5, 6, 7, 8],
        );
        $rows = count($res);
        $num = count($res[0]);
        for($r=0;$r<$rows;$r++){
            print("<tr>\n");
            echo "  <form method='post' action='cd_table_mnt.php'>\n";
            print(" <td class='winbox' align='center'><input type='submit' name='copy' value='" . ($r + $offset + 1) . "'></td>\n");
            echo "      <input type='hidden' name='cd_sel' value='chg'>\n";
            echo "      <input type='hidden' name='uid' value='test'>\n";
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
?>
    </center>
</body>
</html>
<?php

?>

