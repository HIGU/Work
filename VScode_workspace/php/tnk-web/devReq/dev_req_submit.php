<?php
//////////////////////////////////////////////////////////////////////////////
// プログラム開発依頼書 作成フォーム                                        //
// Copyright(C) 2002-2004 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// 変更経歴                                                                 //
// 2002/02/12 新規作成 dev_req_submit.php                                   //
// 2002/08/09 register_globals = Off 対応                                   //
// 2003/12/12 defineされた定数でディレクトリとメニュー名を使用する          //
// 2004/02/24 確認用に社員番号のみ入力した時でも即社員名が出るように変更    //
// 2004/07/17 MenuHeader()クラスを新規作成しデザイン・認証等のロジック統一  //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug 用
// ini_set('display_errors', '1');         // Error 表示 ON debug 用 リリース後コメント
session_start();                        // ini_set()の次に指定すること Script 最上行
require_once ('../function.php');       // TNK 全共通 function
require_once ('../MenuHeader.php');     // TNK 全共通 menu class
require_once ('../tnk_func.php');
access_log();                           // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader();               // 認証チェックも行っている

////////////// サイト設定
$menu->set_site(4, 2);                  // site_index=4(プログラム開発) site_id=2(依頼書作成・送信)
////////////// カレントスクリプトのアドレス設定
// $menu->set_self($_SERVER['PHP_SELF']);
////////////// リターンアドレス設定
// $menu->set_RetUrl(DEV_MENU);         // リターンアドレスを自動取得に変更
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('プログラム開発依頼書 作成・送信');
//////////// 表題の設定
$menu->set_caption('開発依頼書作成 メニュー');

if (isset($_POST['iraisya'])) {
    /////// 社員の名前を取得 SQL
    $_SESSION['s_dev_iraisya']    = $_POST['iraisya'];
    $iraisya    = $_POST['iraisya'];
    $query_user = "select name from user_detailes where uid='{$iraisya}'";
    $res_user = array();
    if($rows_user=getResult($query_user,$res_user)) {
        $user_name = rtrim($res_user[0]['name']);
    } else {
        $user_name = '未登録';
    }
} else {
    $user_name = '';
    $iraisya   = '';
}

if (isset($_POST['dev_chk_submit'])) {
    $dev_chk_submit = $_POST['dev_chk_submit'];
} else {
    $dev_chk_submit = '';
}

if ($dev_chk_submit == '確認') {
    if ($_POST['mokuteki'] == '') {
        $dev_chk_submit = '';
        $_SESSION['s_sysmsg'] = '目的が未入力です！';
    }
    if ($_POST['naiyou'] == '') {
        $dev_chk_submit = '';
        $_SESSION['s_sysmsg'] = '内容が未入力です！';
    }
    // session_register('s_dev_iraibusho','s_dev_iraisya','s_dev_mokuteki','s_dev_naiyou');
    // session_register('s_dev_yosoukouka','s_dev_bikou');
    $_SESSION['s_dev_iraibusho']  = $_POST['iraibusho'];
    $_SESSION['s_dev_mokuteki']   = $_POST['mokuteki'];
    $_SESSION['s_dev_naiyou']     = $_POST['naiyou'];
    $_SESSION['s_dev_yosoukouka'] = $_POST['yosoukouka'];
    $_SESSION['s_dev_bikou']      = $_POST['bikou'];
    $iraibusho  = $_POST['iraibusho'];
    $mokuteki   = $_POST['mokuteki'];
    $naiyou     = $_POST['naiyou'];
    $yosoukouka = $_POST['yosoukouka'];
    $bikou      = $_POST['bikou'];
} elseif ($dev_chk_submit == '修正') {
    $iraibusho  = $_SESSION['s_dev_iraibusho'];
    $iraisya    = $_SESSION['s_dev_iraisya'];
    $mokuteki   = $_SESSION['s_dev_mokuteki'];
    $naiyou     = $_SESSION['s_dev_naiyou'];
    $yosoukouka = $_SESSION['s_dev_yosoukouka'];
    $bikou      = $_SESSION['s_dev_bikou'];
    // $user_name  = '';
} else {
    // $user_name  = '';
    $iraibusho = '';
    $mokuteki = '';
    $naiyou   = '';
    $yosoukouka = '';
    $bikou    = '';
}

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<style type="text/css">
<!--
select {
    background-color:teal;
    color:white;
}
textarea {
    background-color:black;
    color:white;
}
input.sousin {
    background-color:red;
}
input.text {
    background-color:black;
    color:white;
}
.pt {
    font-size:11pt;
}
-->
</style>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>
<script language="JavaScript">
<!--
/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus(){
    <?php
    if ($user_name == '') {
        echo "document.input_form.iraisya.focus();\n";
        echo "document.input_form.iraisya.select();\n";
    } elseif ($mokuteki == '') {
        echo "document.input_form.mokuteki.focus();\n";
        echo "document.input_form.mokuteki.select();\n";
    } elseif ($naiyou == '') {
        echo "document.input_form.naiyou.focus();\n";
        echo "document.input_form.naiyou.select();\n";
    }
    ?>
}
// -->
</script>
<script language="JavaScript" src="./dev_req.js?id=2">
</script>
</head>
<body onload='set_focus()'>
    <center>
<?= $menu->out_title_border() ?>
        <hr color='navy'>
        <table width=100% border='0'>
            <tr>
            <?php if($dev_chk_submit == '確認') { ?>
                <form action='dev_req_insert.php' method='post'>
                <td align='center'><input class='sousin' type='submit' name='dev_chk_submit' value='送信' ></td>
                </form>
                <form action='<?= $menu->out_self() ?>' method='post'>
                <td align='center'><input type='submit' name='dev_chk_submit' value='修正' ></td>
            <?php } else { ?>
                <form name='input_form' action='<?= $menu->out_self() ?>' method='post' onSubmit='return chk_dev_req_submit(this)'>
                <td align='center'><input type='submit' name='dev_chk_submit' value='確認' ></td>
            <?php } ?>
            </tr>
        </table>
        <table width='100%' cellspacing='0' cellpadding='2' border='1' bgcolor='#e6e6fa'>
            <tr>
                <td align='center' width='20'>①</td>
                <td align='left'>依頼No</td>
                <td align='left'>
                    依頼No(受付No)は送信時に自動で取られます。
                </td>
            </tr>
            <tr>
                <td align='center' width='20'>②</td>
                <td align='left'>依頼日</td>
                <td align='left'>
                    <?php $iraibi=date("Y-m-d");echo $iraibi; ?>
                </td>
            </tr>
            <tr>
                <td align='center' width='20'>③</td>
                <td align='left'>依頼部署</td>
                <td align="left">
                
                <?php
                if($dev_chk_submit != "確認"){
                    print("<select name='iraibusho'>\n");
                    $query_section="select * from section_master where sflg=1 order by sid asc";
                    $res_section=array();
                    if($rows_section=getResult($query_section,$res_section)){
                        for($i=0;$i<$rows_section;$i++){
                            echo("<option ");
                            if($iraibusho==$res_section[$i][0])    // なぜか sid が使えず数字の 0 にした。
                                echo("selected ");
                            echo("value='" . $res_section[$i][0] . "'>" . rtrim($res_section[$i]['section_name']) . "</option>\n");
                        }
                    }
                    print("</select>\n");
                }else{
                    $query_section="select * from section_master where sid = $iraibusho ";
                    $res_section=array();
                    if($rows_section=getResult($query_section,$res_section))
                        print(rtrim($res_section[0]['section_name']));
                    else
                        print($iraibusho);
                }
                ?>
                </td>
            </tr>
            <tr>
                <td align='center' width='20'>④</td>
                <td align='left'>依頼者</td>
                <td align="left">
                    依頼者の社員No
                    <?php
                    if ($dev_chk_submit != '確認') {
                        echo "<input class='text' type='text' name='iraisya' size='7' maxlength='6' value='", ltrim($iraisya), "'>\n";
                    } else {
                        echo "$iraisya\n";
                    }
                    if ($user_name != '') {
                        echo "<font size='3'>{$user_name}</font></td>\n";
                    } else {
                        echo "--------\n";
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td align='center' width='20'>⑤</td>
                <td align='left'>目的又はタイトル</td>
                <td align='left'>
                    <?php
                    if($dev_chk_submit != "確認"){
                        echo("<textarea class='pt' name='mokuteki' cols='50' rows='2' wrap='soft'>" . $mokuteki . "</textarea>\n");
                        echo("<font size='1'>自動で改行しますので改行キーは押さないで下さい。</font>\n");
                    }else{
                        print("$mokuteki\n");
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td align='center' width='20'>⑥</td>
                <td align='left'>内　　容</td>
                <td align='left'>
                    <?php
                    if($dev_chk_submit != "確認")
                        echo("<textarea class='pt' name='naiyou' cols='80' rows='6' wrap='soft'>" . $naiyou . "</textarea>\n");
                    else
                        print("$naiyou\n");
                    ?>
                </td>
            </tr>
            <tr>
                <td align='center' width='20'>⑦</td>
                <td align='left' nowrap>予想効果</td>
                <td align='left'>
                    <?php
                    if($dev_chk_submit != "確認"){
                        print("<input class='text' type='text' name='yosoukouka' size='11' maxlength='9' value='" . ltrim($yosoukouka) . "'>\n");
                        print("工数(分)／年　（省略可能）\n");
                    }else
                        if($yosoukouka=="")
                            print("-----\n");
                        else
                            print("$yosoukouka 分／年\n");
                    ?>
                </td>
            </tr>
            <tr>
                <td align='center' width='20'>⑧</td>
                <td align='left'>計算式又は備考</td>
                <td align='left'>
                    <?php
                    if($dev_chk_submit != "確認"){
                        echo("<textarea class='pt' name='bikou' cols='50' rows='2' wrap='soft'>" . $bikou . "</textarea>\n");
                        echo("<font size='1'>予想効果工数(分)の計算式又は説明・備考(省略可)</font>\n");
                    }else
                        if($bikou=="")
                            print("-----\n");
                        else
                            print("$bikou\n");
                    ?>
                </td>
            </tr>
            </form>
        </table>
        <table width='100%' border='0'>
            <form action='<?= $menu->out_RetUrl() ?>' method='post'>
                <tr><td align='center'><input type='submit' name='dev_chk_submit' value='戻る' ></td></tr>
            </form>
        </table>
    </center>
</body>
</html>
