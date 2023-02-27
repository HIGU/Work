<?php
//////////////////////////////////////////////////////////////////////////////
// 機械賃率計算表 手作業(刻印)・機械運転時間を入力し賃率を自動算出          //
// Copyright (C) 2002-2021      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2002/09/23 Created   machine_labor_rate_mnt.php                          //
// 2002/10/09 機械賃率を小数点以下２桁が０でも必ず表示させる(照会)          //
// 2003/02/26 body に onLoad を追加し初期入力個所に focus() させた          //
// 2003/09/08 自由設定(ランダム)時のSQL文の条件に不具合あり (>=)→(=)       //
// 2003/10/08 前期実績賃率の SQL文に offset 1 を追加し前決算時の賃率へ      //
// 2003/12/18 なぜか単月処理時の POST データが送信されない不具合に対応      //
// 2004/10/28 user_check()functionを追加し編集出来るユーザーを限定          //
// 2005/10/27 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2005/11/05 単月処理が２重登録される現象が出たためMenuHeaderを最後へ移動  //
// 2007/02/05 account_group_check()で登録できるユーザーの確認を追加         //
// 2007/09/25 変数の初期化を追加                                            //
// 2010/06/03 前期実績賃率のSQL文を訂正                                大谷 //
// 2014/04/11 製造２課の管理経費の配賦を追加                           大谷 //
// 2016/05/23 製造２課追加の際に配賦がおかしくなっていたのを訂正       大谷 //
// 2016/06/09 割合によって配賦差額が発生。→差額は最大の割合部門へ     大谷 //
// 2018/06/05 単月処理時、前期データ取得でエラーの為、修正             大谷 //
// 2021/04/06 2103にリース料の調整 リース資産が527でマイナスの為       大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL & ~E_NOTICE);  // E_ALL='2047' debug 用
// ini_set('display_errors', '1');          // Error 表示 ON debug 用 リリース後コメント
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../function.php');           // define.php と pgsql.php を require_once している
require_once ('../tnk_func.php');           // TNK に依存する部分の関数を require_once している
require_once ('../MenuHeader.php');         // TNK 全共通 menu class


///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
    // 実際の認証はprofit_loss_submit.phpで行っているaccount_group_check()を使用

////////////// サイト設定
$menu->set_site(10, 3);                     // site_index=10(損益メニュー) site_id=7(機械賃率の照会・登録)
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('機械賃率計算表の作成・照会');

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

<script type='text/javascript' language='JavaScript' src='machine_labor_rate_mnt.js'></script>

<style type='text/css'>
<!--
body {
    font-size:9.0pt;
    margin:0%;
}
th {
    font-size:11.0pt;
}
td {
    font-size:9.0pt;
}
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
select          {background-color:teal;
                color:white;}
textarea        {background-color:black;
                color:white;}
input.sousin    {background-color:red;}
input.text      {background-color:black;
                color:white;}
.right          {text-align:right;}
.center         {text-align:center;}
.left           {text-align:left;}
.pt10           {font-size:10pt;}
.pt10b          {font-size:10pt;
                font:bold;}
.pt11           {font-size:11pt;}
.pt11b          {font-size:11pt;
                font:bold;}
.pt12b          {font-size:12pt;
                font:bold;}
.fc_red         {color:red;}
.fc_blue        {color:blue;}
.margin1        {margin:1%;}
-->
</style>
</head>
<body style='overflow-y:hidden;' onLoad='document.ini_form.rate_ym.focus()'>
    <center>
<?= $menu->out_title_border() ?>
        <div class='pt10'>作成する場合はシステム管理メニューの月次処理で製造経費の対象月の取込みを行った後、実行する。</div>
        <table bgcolor='#d6d3ce' cellspacing='0' cellpadding='3' border='1'>
            <form name='ini_form' action='<?=$menu->out_self()?>' method='post' onSubmit='return ym_chk(this)'>
                <tr>
                    <td colspan='2' align='right' valign='middle' class='pt11'>
                        対象年月を指定して下さい。例：200204 (2002年04月)
                        <input type='text' name='rate_ym' size='7' value='<?php echo $rate_ym ?>' maxlength='6'>
                    </td>
                    <td align='left'>
                        <input class='pt11b' type='submit' name='tangetu' value='単月処理'>
                    </td>
                </tr>
            </form>
            <form action='machine_labor_rate_mnt.php' method='post' onSubmit='return kessan_chk(this)'>
                <tr>
                    <td align='left' class='pt11'>
                        対象年月 範囲を指定して下さい。
                        <input type='text' name='str_ym' size='7' value='<?php echo($_SESSION['str_ym']); ?>' maxlength='6'>
                        ～
                        <input type='text' name='end_ym' size='7' value='<?php echo($_SESSION['end_ym']); ?>' maxlength='6'>
                    </td>
                    <td align='left' class='pt11'>
                        <label for='1'>中間</label><input type='radio' name='span' value='1' id='1'<?php if($_SESSION['span']==1)echo ' checked' ?>>
                        <label for='2'>期末</label><input type='radio' name='span' value='2' id='2'<?php if($_SESSION['span']==2)echo ' checked' ?>>
                        <label for='3'>自由</label><input type='radio' name='span' value='3' id='3'<?php if($_SESSION['span']==3)echo ' checked' ?>>
                    </td>
                    <td align='left'>
                        <input class='pt11b' type='submit' name='kessan' value='決算処理'>
                    </td>
                </tr>
            </form>
        </table>
    <?php
    if(isset($_POST['insert'])){
        echo "<hr>\n";
        if (user_check($uid)) {
            echo "<br><font class='pt12b fc_blue'>登録しました。</font>\n";
        } else {
            echo "<br><font class='pt12b fc_red'>登録出来ませんでした。</font>\n";
        }
    }
    else if(isset($_POST['tangetu']) || isset($_POST['kessan']) || isset($_POST['check'])){
    ?>
        <hr>
        <table bgcolor='#d6d3ce' cellspacing='0' cellpadding='3' border='1'>
            <caption class='pt12b'>機械賃率計算表</caption>
            <?php
            if($register == "登録")
                echo "<th colspan='3' class='fc_red'>$register</th>\n";
            else
                echo "<th colspan='3' class='fc_blue'>$register</th>\n";
            for($i=0;$i<$rows_act;$i++){
                echo "<th>" . $b_name[$i] . "(" . $act_id[$i] . ")</th>\n";
            }
            ?>
            <th nowrap>製造合計</th>
            <tr>
                <td rowspan='10' width='10'>直接部門費</td>
                <td rowspan='4' width='10'>直接費</td>
                <td nowrap>減価償却費</td>
                <?php
                for($i=0;$i<$rows_act;$i++){
                    if($depre[$i] == "")
                        echo "<td nowrap align='right'>0</td>\n";
                    else
                        echo "<td nowrap align='right'>900</td>\n";
                }
                echo "<td nowrap align='right'>900</td>\n";
                ?>
            </tr>
            <tr>
                <td>リース料</td>
                <?php
                for($i=0;$i<$rows_act;$i++){
                    if($lease[$i] == "")
                        echo "<td nowrap align='right'>0</td>\n";
                    else
                        echo "<td nowrap align='right'>900</td>\n";
                }
                echo "<td nowrap align='right'>900</td>\n";
                ?>
            </tr>
            <tr>
                <td>修繕費</td>
                <?php
                for($i=0;$i<$rows_act;$i++){
                    if($repair[$i] == "")
                        echo "<td nowrap align='right'>0</td>\n";
                    else
                        echo "<td nowrap align='right'>900</td>\n";
                }
                echo "<td nowrap align='right'>900</td>\n";
                ?>
            </tr>
            <tr>
                <td>工場消耗品費</td>
                <?php
                for($i=0;$i<$rows_act;$i++){
                    if($w_cost[$i] == "")
                        echo "<td nowrap align='right'>0</td>\n";
                    else
                        echo "<td nowrap align='right'>900</td>\n";
                }
                echo "<td nowrap align='right'>900</td>\n";
                ?>
            </tr>
            <tr>
                <td colspan='2' align='center'>小計</td>
                <?php
                for($i=0;$i<$rows_act;$i++){
                    if($s1_sum[$i] == "")
                        echo "<td nowrap align='right'>0</td>\n";
                    else
                        echo "<td nowrap align='right'>900</td>\n";
                }
                echo "<td nowrap align='right'>900</td>\n";
                ?>
            </tr>
            <tr>
                <td rowspan='4' width='10'>間接費</td>
                <td>人件費</td>
                <?php
                for($i=0;$i<$rows_act;$i++){
                    if($p_cost[$i] == "")
                        echo "<td nowrap align='right'>0</td>\n";
                    else
                        echo "<td nowrap align='right'>900</td>\n";
                }
                echo "<td nowrap align='right'>900</td>\n";
                ?>
            </tr>
            <tr>
                <td>電力料</td>
                <?php
                for($i=0;$i<$rows_act;$i++){
                    if($e_cost[$i] == "")
                        echo "<td nowrap align='right'>0</td>\n";
                    else
                        echo "<td nowrap align='right'>900</td>\n";
                }
                echo "<td nowrap align='right'>900</td>\n";
                ?>
            </tr>
            <tr>
                <td>その他</td>
                <?php
                for($i=0;$i<$rows_act;$i++){
                    if($other[$i] == "")
                        echo "<td nowrap align='right'>0</td>\n";
                    else
                        echo "<td nowrap align='right'>900</td>\n";
                }
                echo "<td nowrap align='right'>900</td>\n";
                ?>
            </tr>
            <tr>
                <td>管理経費配賦</td>
                <?php
                for($i=0;$i<$rows_act;$i++){
                    if($m_cost_all[$i] == "")
                        echo "<td nowrap align='right'>0</td>\n";
                    else
                        echo "<td nowrap align='right'>900</td>\n";
                }
                echo "<td nowrap align='right'>900</td>\n";
                ?>
            </tr>
            <tr>
                <td colspan='2' align='center'>小計</td>
                <?php
                for($i=0;$i<$rows_act;$i++){
                    if($s2_sum[$i] == "")
                        echo "<td nowrap align='right'>0</td>\n";
                    else
                        echo "<td nowrap align='right'>900</td>\n";
                }
                echo "<td nowrap align='right'>900</td>\n";
                ?>
            </tr>
            <tr>
                <td colspan='3' align='center'>中計</td>
                <?php
                for($i=0;$i<=$rows_act;$i++){
                    if($m_sum[$i] == "")
                        echo "<td nowrap align='right'>0</td>\n";
                    else
                        echo "<td nowrap align='right'>900</td>\n";
                }
                ?>
            </tr>
            <?php
            if((!isset($_POST['check'])) && $register <> "照会")
                echo "<form action='machine_labor_rate_mnt.php' method='post'>\n";
            ?>
            <tr>
                <td colspan='3' align='center'>手作業経費を除く</td>
                <?php
                if(isset($_POST['check']) || $register == "照会"){
                    $h_cost_sum = 0;    // 初期化
                    for($i=0;$i<$rows_act;$i++){
                        if($_SESSION['h_cost'][$i] > 0)
                            echo "<td nowrap align='right'>△900</td>\n";
                        else
                            echo "<td nowrap align='right'>900</td>\n";
                        $h_cost_sum += $_SESSION['h_cost'][$i];
                    }
                    if($h_cost_sum > 0)
                        echo "<td nowrap align='right'>△900</td>\n";
                    else
                        echo "<td nowrap align='right'>900</td>\n";
                }else{
                    for($i=0;$i<$rows_act;$i++){
                        echo "<td nowrap align='right'><input type='text' class='right' name='h_cost[]' size='9' value='" . $_SESSION['h_cost'][$i] . "' maxlength='8'></td>\n";
                    }
                    echo "<td nowrap align='center'><input type='submit' name='check' value='確認'></td>\n";
                }
                ?>
            </tr>
            <tr>
                <td colspan='3' align='center'>合計</td>
                <?php
                for($i=0;$i<=$rows_act;$i++){
                    if($t_sum[$i] == "")
                        echo "<td nowrap align='right'>0</td>\n";
                    else
                        echo "<td nowrap align='right'>900</td>\n";
                }
                ?>
            </tr>
            <tr>
                <td colspan='3' align='center'>所属人員</td>
                <?php
                for($i=0;$i<$rows_act;$i++){
                    if($man[$i] == "")
                        echo "<td nowrap align='right'>0</td>\n";
                    else
                        echo "<td nowrap align='right'>900</td>\n";
                }
                echo "<td nowrap align='right'>900</td>\n";
                ?>
            </tr>
            <tr>
                <td colspan='3' align='center'>段取含む機械運転時間</td>
                <?php
                if(isset($_POST['check']) || $register == "照会"){
                    for($i=0;$i<$rows_act;$i++){
                        echo "<td nowrap align='right'>900</td>\n";
                    }
                    echo "<td nowrap align='right'>900</td>\n";
                }else{
                    for($i=0;$i<$rows_act;$i++){
                        echo "<td nowrap align='right'><input type='text' class='right' name='ope_time[]' size='9' value='" . $_SESSION['ope_time'][$i] . "' maxlength='8'></td>\n";
                    }
                    echo "<td nowrap align='center'><input type='submit' name='check' value='確認'></td>\n";
                }
                ?>
            </tr>
            <?php
            if((!isset($_POST['check'])) && $register <> "照会")
                echo "</form>\n";
            ?>
            <tr>
                <td colspan='3' align='center' class='fc_red'>直接経費 機械賃率</td>
                <?php
                for($i=0;$i<$rows_act;$i++){
                    if($_SESSION['ope_time'][$i] > 0)
                        echo "<td nowrap align='right' class='fc_red'>" . $labor_rate[$i] . "</td>\n";
                    else
                        echo "<td nowrap align='right'>---</td>\n";
                }
                if($labor_rate[$i] > 0)
                    echo "<td nowrap align='right' class='fc_red'>" . $labor_rate[$i] . "</td>\n";
                else
                    echo "<td nowrap align='right'>---</td>\n";
                ?>
            </tr>
            <tr>
                <td colspan='3' align='center'>標　準　賃　率</td>
                <?php
                for($i=0;$i<=$rows_act;$i++){
                    echo "<td nowrap align='right'>---</td>\n";
                }
                ?>
            </tr>
            <tr>
                <td colspan='<?php echo (4+$rows_act) ?>' align='center' bgcolor='white'>前　　期　　実　　績</td>
            </tr>
            <tr>
                <td colspan='3' align='center'>直接経費</td>
                <?php
                for($i=0;$i<$rows_act;$i++){
                    if($pre_t_cost[$i] > 0)
                        echo "<td nowrap align='right'>900</td>\n";
                    else
                        echo "<td nowrap align='right'>---</td>\n";
                }
                echo "<td nowrap align='right'>---</td>\n";
                ?>
            </tr>
            <tr>
                <td colspan='3' align='center' nowrap>段取含む機械運転時間</td>
                <?php
                for($i=0;$i<$rows_act;$i++){
                    if($pre_ope_time[$i] > 0)
                        echo "<td nowrap align='right'>900</td>\n";
                    else
                        echo "<td nowrap align='right'>---</td>\n";
                }
                echo "<td nowrap align='right'>---</td>\n";
                ?>
            </tr>
            <tr>
                <td colspan='3' align='center'>直接経費 機械賃率A</td>
                <?php
                for($i=0;$i<$rows_act;$i++){
                    if($pre_rate[$i] > 0)
                        echo "<td nowrap align='right'>" . $pre_rate[$i] . "</td>\n";
                    else
                        echo "<td nowrap align='right'>---</td>\n";
                }
                echo "<td nowrap align='right'>---</td>\n";
                ?>
            </tr>
        </table>
    <?php
    }
    if(isset($_POST['check'])){
        echo "<form action='machine_labor_rate_mnt.php' method='post'>\n";
        echo "<td nowrap align='center'><input type='submit' name='insert' value='登録' class='fc_red'></td>\n";
        echo "</form>\n";
    }
    ?>
    </center>
</body>
</html>
