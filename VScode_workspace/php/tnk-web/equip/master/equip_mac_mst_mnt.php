<?php
//////////////////////////////////////////////////////////////////////////////
// 設備・機械マスター のメンテナンス                                        //
// Copyright (C) 2002-2018 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2002/03/13 Created   equip_mac_mst_mnt.php                               //
// 2002/08/08 register_globals = Off 対応                                   //
// 2003/06/17 servey(監視フラグ) Y/N が変更できない不具合を修正 及び        //
//              各入力フォームをプルダウン式に変更                          //
// 2003/06/19 $uniq = uniqid('script')を追加して JavaScript Fileを必ず読む  //
// 2004/03/04 新版テーブル equip_machine_master2 への対応                   //
// 2004/07/12 Netmoni & FWS 方式を統一 スイッチ方式 そのため Net&FWS方式追加//
//            CSV 出力設定等を 監視方式へ 項目名変更                        //
// 2005/02/14 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2005/06/24 ディレクトリ変更 equip/ → equip/master/                      //
// 2018/05/18 ７工場を追加。コード４の登録を強制的に7に変更            大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 WEB CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
require_once ('../equip_function.php');
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(40, 25);                    // site_index=40(設備メニュー) site_id=25(機械マスター)
////////////// リターンアドレス設定
// $menu->set_RetUrl(EQUIP_MENU);              // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('機械マスター メンテナンス');
//////////// 表題の設定
$menu->set_caption('作業区分を選択して下さい');
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('test_template',   SYS . 'log_view/php_error_log.php');

//////////// <JavaScript File を必ず読み込ませるようにする｡
$uniq = uniqid('script');

//////////// POST Data の初期化＆設定
if (!isset($_POST['current_menu'])) {
    $current_menu = 'working';
} else {
    $current_menu = $_POST['current_menu'];
}
if (isset($_POST['mac_no'])) {
    $mac_no = $_POST['mac_no'];
} else {
    $mac_no = '';
}
if (isset($_POST['mac_name'])) {
    $mac_name = $_POST['mac_name'];
} else {
    $mac_name = '';
}
if (isset($_POST['maker_name'])) {
    $maker_name = $_POST['maker_name'];
} else {
    $maker_name = '';
}
if (isset($_POST['maker'])) {
    $maker = $_POST['maker'];
} else {
    $maker = '';
}
if (isset($_POST['factory'])) {
    $factory = $_POST['factory'];
} else {
    $factory = '';
}
if (isset($_POST['survey'])) {
    $survey = $_POST['survey'];
} else {
    $survey = '';
}
if (isset($_POST['csv_flg'])) {
    $csv_flg = $_POST['csv_flg'];
} else {
    $csv_flg = '';
}
if (isset($_POST['sagyouku'])) {
    $sagyouku = $_POST['sagyouku'];
} else {
    $sagyouku = '';
}
if (isset($_POST['denryoku'])) {
    $denryoku = $_POST['denryoku'];
} else {
    $denryoku = '';
}
if (isset($_POST['keisuu'])) {
    $keisuu = $_POST['keisuu'];
} else {
    $keisuu = '';
}
/********* 修正用 *********/
if (isset($_POST['pmac_no'])) {
    $pmac_no = $_POST['pmac_no'];
} else {
    $pmac_no = '';
}

//////////////// 登録・修正・削除の POST 変数を ローカル変数に登録
if (isset($_POST['apend'])) {
    $apend = $_POST['apend'];
} else {
    $apend = '';
}
if (isset($_POST['edit'])) {
    $edit = $_POST['edit'];
} else {
    $edit = '';
}
if (isset($_POST['delete'])) {
    $delete = $_POST['delete'];
} else {
    $delete = '';
}

/////////////////////////////////////////////////// マスター登録 (追加)
if ($apend == '登録') {
    if (equipAuthUser('FNC_MASTER')) {
        $query = "select mac_no from equip_machine_master2 where mac_no={$mac_no} limit 1";
        $res = array();
        if ( ($rows=getResult($query,$res)) >= 1) {  // 既に登録済みのチェック
            $_SESSION['s_sysmsg'] = "機械番号:{$mac_no} は既に登録されています";
        } else {
            add_equip_mac_mst($mac_no,$mac_name,$maker_name,$maker,$factory,$survey,$csv_flg,$sagyouku,$denryoku,$keisuu);
        }
    } else {
        $_SESSION['s_sysmsg'] = '設備管理のマスター編集権限がありません！';
    }
}

//////////////////////////////////////////////////// マスター 修正
if ($edit != '') {
    if (equipAuthUser('FNC_MASTER')) {
        $query     = "select mac_no from equip_machine_master2 where mac_no={$pmac_no}";
        // $query_chk = "select mac_no from equip_machine_master2 where mac_no={$mac_no}";
        $res = array();
        if ( ($rows=getResult($query,$res)) >= 1) {  // equip_machine_master より登録されているかチェック
            chg_equip_mac_mst($pmac_no,$mac_no,$mac_name,$maker_name,$maker,$factory,$survey,$csv_flg,$sagyouku,$denryoku,$keisuu);
        } else {
            $_SESSION['s_sysmsg'] = "機械番号:{$pmac_no} は他の人に変更されました";
        }
    } else {
        $_SESSION['s_sysmsg'] = '設備管理のマスター編集権限がありません！';
    }
}

////////////////////////////////////////////////// マスターの完全削除
if ($delete != '') {
    if (equipAuthUser('FNC_MASTER')) {
        del_equip_mac_mst($mac_no);
    } else {
        $_SESSION['s_sysmsg'] = '設備管理のマスター編集権限がありません！';
    }
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
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>

<style type='text/css'>
<!--
textarea {
    background-color:   black;
    color:              white;
}
select {
    background-color:   teal;
    color:              white;
}
.center {
    text-align:         center;
}
.right {
    text-align:         right;
}
.left {
    text-align:         left;
}
.fc_yellow {
    color:              yellow;
    background-color:   blue;
}
.fc_red {
    color:              red;
    background-color:   blue;
}
th {
    font-size:          9pt;
    font-weight:        bold;
    color:              black;
    background-color:   lightblue;
}
.s_radio {
    color:              white;
    background-color:   blue;
    font-size:          11pt;
    font-weight:        bold;
}
.n_radio {
    font-size:          11pt;
}
-->
</style>
<script language='JavaScript'>
<!--
/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus() {
    // document.body.focus();   // F2/F12キーを有効化する対応
    // document.mhForm.backwardStack.focus();  // 上記はIEのみのためNN対応
}
// -->
</script>
<script language='JavaScript' src='../equipment.js?<?php echo $uniq ?>'></script>
</head>
<body onLoad='set_focus()'>
    <center>
<?= $menu->out_title_border() ?>

<table width='100%' border='0' cellspacing='0' cellpadding='0'>
    <tr><td>
        <form action='<?=$menu->out_self()?>' method='post'>
        <!--
            <table width='100%' height'10' border='0' cellspacing='0' cellpadding='0'>
                <tr><td align='center'>
                    <img src='../img/tnk-turbine_small.gif'>
                </td></tr>
            </table>
        -->
            <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td class='winbox' align='center' nowrap>
                        <span class='caption_font'><?= $menu->out_caption(), "\n" ?></span>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='center' nowrap>
                        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                            <tr align='center'>
                                <td class='winbox' nowrap>
                                    <span <?php if($current_menu=='apend') echo "class='s_radio'"; else echo "class='n_radio'" ?>>
                                        <input type='radio' name='current_menu' value='apend' id='apend' onClick='submit()'
                                        <?php if($current_menu=='apend') echo 'checked' ?>>
                                        <label for='apend'>マスター追加
                                    </span>
                                </td>
                                <td class='winbox' nowrap>
                                    <span <?php if($current_menu=='edit') echo "class='s_radio'"; else echo "class='n_radio'" ?>>
                                        <input type='radio' name='current_menu' value='edit' id='edit' onClick='submit()'
                                        <?php if($current_menu=='edit') echo 'checked' ?>>
                                        <label for='edit'>マスター修正
                                    </span>
                                </td>
                                <td class='winbox' nowrap>
                                    <span <?php if($current_menu=='delete') echo "class='s_radio'"; else echo "class='n_radio'" ?>>
                                        <input type='radio' name='current_menu' value='delete' id='del' onClick='submit()'
                                        <?php if($current_menu=='delete') echo 'checked' ?>>
                                        <label for='del'>マスター削除
                                    </span>
                                    </td>
                                <td class='winbox' nowrap>
                                    <span <?php if($current_menu=='list') echo "class='s_radio'"; else echo "class='n_radio'" ?>>
                                        <input type='radio' name='current_menu' value='list' id='work' onClick='submit()'
                                        <?php if($current_menu=='list') echo 'checked' ?>>
                                        <label for='work'>マスター一覧
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ダミーEnd ------------------>
            <!-- <div align='center'><input type='submit' name='select_submit' value='実行' ></div> -->
        </form>
        <?php
        switch ($current_menu) {
        case 'apend':       // マスター登録(追加)
            echo "<br>\n";
            echo "<form action='", $menu->out_self(), "' method='post' onSubmit='return chk_equip_mac_mst_mnt(this)'>\n";
            echo "<table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='5'>\n";
            echo "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
            print("<table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n");
            print(" <tr>\n");
            print("     <th class='winbox' width='40'>1</th>\n");
            print("     <td class='winbox' align='left' nowrap>\n");
            print("         機械番号\n");
            print("         <input type='text' name='mac_no' size='5' value='$mac_no' maxlength='4'>\n");
            print("     </td>\n");
            print(" </tr>\n");
            print(" <tr>\n");
            print("     <th class='winbox'>2</th>\n");
            print("     <td class='winbox' align='left' nowrap>\n");
            print("         機械名称\n");
            print("         <input type='text' name='mac_name' size='24' value='$mac_name' maxlength='20'>\n");
            print("     </td>\n");
            print(" </tr>\n");
            print(" <tr>\n");
            print("     <th class='winbox'>3</th>\n");
            print("     <td class='winbox' align='left' nowrap>\n");
            print("         メーカー型式\n");
            print("         <input type='text' name='maker_name' size='24' value='$maker_name' maxlength='20'>\n");
            print("     </td>\n");
            print(" </tr>\n");
            print(" <tr>\n");
            print("     <th class='winbox'>4</th>\n");
            print("     <td class='winbox' align='left' nowrap>\n");
            print("         メーカー\n");
            print("         <input type='text' name='maker' size='24' value='$maker' maxlength='20'>\n");
            print("     </td>\n");
            print(" </tr>\n");
            print(" <tr>\n");
            print("     <th class='winbox'>5</th>\n");
            print("     <td class='winbox' align='left' nowrap>\n");
            print("         工場区分\n");
            // print("         <input type='text' name='factory' size='1' value='$factory' maxlength='1' class='center'>\n");
            print("         <select name='factory'>\n");
            print("             <option value='1'>１工場</option>\n");
            print("             <option value='2'>２工場</option>\n");
            print("             <option value='3'>３工場</option>\n");
            print("             <option value='4'>４工場</option>\n");
            print("             <option value='5'>５工場</option>\n");
            print("             <option value='6'>６工場</option>\n");
            print("             <option value='7' selected>７工場</option>\n");
            print("         </select>\n");
            print("     </td>\n");
            print(" </tr>\n");
            print(" <tr>\n");
            print("     <th class='winbox'>6</th>\n");
            print("     <td class='winbox' align='left' nowrap>\n");
            print("         監視設定\n");
            // print("         <input type='text' name='survey' size='1' value='$survey' maxlength='1' class='center'>\n");
            print("         <select name='survey'>\n");
            print("             <option value='Y'>監視する</option>\n");
            print("             <option value='N'>監視しない</option>\n");
            print("         </select>\n");
            print("     </td>\n");
            print(" </tr>\n");
            print(" <tr>\n");
            print("     <th class='winbox'>7</th>\n");
            print("     <td class='winbox' align='left' nowrap>\n");
            print("         監視方式 設定\n");
            // print("         <input type='text' name='csv_flg' size='1' value='$csv_flg' maxlength='1' class='center'>\n");
            print("         <select name='csv_flg'>\n");
            print("             <option value='0'>出力しない</option>\n");
            print("             <option value='1'>出力Netmoni</option>\n");
            print("             <option value='2'>出力 FWS1</option>\n");
            print("             <option value='3'>出力 FWS2</option>\n");
            print("             <option value='4'>出力 FWS3</option>\n");
            print("             <option value='5'>出力 FWS4</option>\n");
            print("             <option value='6'>出力 FWS5</option>\n");
            print("             <option value='7'>出力 FWS6</option>\n");
            print("             <option value='8'>出力 FWS7</option>\n");
            print("             <option value='9'>出力 FWS8</option>\n");
            print("             <option value='10'>出力 FWS9</option>\n");
            print("             <option value='11'>出力 FWS10</option>\n");
            print("             <option value='12'>出力 FWS11</option>\n");
            print("             <option value='101'>出力 Net&FWS</option>\n");
            print("             <option value='201'>出力その他</option>\n");
            print("         </select>\n");
            print("     </td>\n");
            print(" </tr>\n");
            print(" <tr>\n");
            print("     <th class='winbox'>8</th>\n");
            print("     <td class='winbox' align='left' nowrap>\n");
            print("         作業区 101 401 501等\n");
            print("         <input type='text' name='sagyouku' size='3' value='$sagyouku' maxlength='3'>\n");
            print("     </td>\n");
            print(" </tr>\n");
            print(" <tr>\n");
            print("     <th class='winbox'>9</th>\n");
            print("     <td class='winbox' align='left' nowrap>\n");
            print("         使用電力(KW)\n");
            print("         <input type='text' name='denryoku' size='8' value='$denryoku' maxlength='7' class='right'>\n");
            print("     </td>\n");
            print(" </tr>\n");
            print(" <tr>\n");
            print("     <th class='winbox'>10</th>\n");
            print("     <td class='winbox' align='left' nowrap>\n");
            print("         電力係数\n");
            print("         <input type='text' name='keisuu' size='4' value='$keisuu' maxlength='4' class='right'>\n");
            print("     </td>\n");
            print(" </tr>\n");
            echo " </table>\n";
            echo "    </td></tr>\n";
            print("</table>\n");
            print("<div align='center'><input type='submit' name='apend' value='登録'></div>\n");
            print("</form>\n");
            break;
        case "edit":        // マスター 修正
            $query = "select mac_no,mac_name,maker_name,maker,factory,survey,csv_flg,sagyouku,denryoku,keisuu 
                    from equip_machine_master2 order by mac_no";
            $res = array();
            if(($rows=getResult($query,$res))>=1){      // equip_machine_master よりを取得
                echo "<table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
                // print("<caption>マスターの修正</caption>\n");
                echo "<tr><td>\n";  // ダミー
                echo "<table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
                print(" <th class='winbox' width='40'><span class='fc_yellow'>修正</span></th><th class='winbox' width='80'>機械番号</th><th class='winbox' width='80'>機械名称</th><th class='winbox' width='80'>メーカー型式</th>
                    <th class='winbox' width='80'>メーカー名</th><th class='winbox' width='80'>工場区分</th><th class='winbox' nowrap>監視</th><th class='winbox' nowrap>監視方式</th><th class='winbox' nowrap>作業区</th><th class='winbox' nowrap>使用電力</th><th class='winbox' nowrap>電力係数</th>\n");
                for($r=0;$r<$rows;$r++){
                    echo "<form action='", $menu->out_self(), "' method='post' onSubmit='return chk_equip_mac_mst_mnt(this)'>\n";
                    print("<input type='hidden' name='pmac_no' value='" . $res[$r][0] . "'>\n");
                    print("<tr>\n");
                    $num = $r+1;
                    print("<td class='winbox' align='center'><input type='submit' name='edit' value='$num'></td>\n");
                    print("<td class='winbox' align='center' nowrap><input type='text' name='mac_no' size='4' value='" . rtrim($res[$r][0]) . "' maxlength='4' class='center'></td>\n");
                    print("<td class='winbox' align='center' nowrap><input type='text' name='mac_name' size='24' value='" . rtrim($res[$r][1]) . "' maxlength='20' class='left'></td>\n");
                    print("<td class='winbox' align='center' nowrap><input type='text' name='maker_name' size='24' value='" . rtrim($res[$r][2]) . "' maxlength='20' class='left'></td>\n");
                    print("<td class='winbox' align='center' nowrap><input type='text' name='maker' size='24' value='" . rtrim($res[$r][3]) . "' maxlength='20' class='left'></td>\n");
                    // print("<td align='center' nowrap><input type='text' name='factory' size='1' value='" . $res[$r][4] . "' maxlength='1' class='center'></td>\n");
                    print("<td class='winbox' align='center' nowrap><select name='factory'>\n");
                    for ($i=1; $i<=6; $i++) {
                        if ($res[$r][4] == $i) {
                            printf("    <option value='%s' selected>%s工場</option>\n", $i, $i);
                        } else {
                            printf("    <option value='%s'>%s工場</option>\n", $i, $i);
                        }
                    }
                    print("</select></td>\n");
                    // print("<td align='center' nowrap><input type='text' name='survey' size='1' value='" . $res[$r][5] . "' maxlength='1' class='center'></td>\n");
                    echo "<td class='winbox' align='center' nowrap><select name='survey'>\n";
                    if ($res[$r][5] == 'Y') {
                        echo "    <option value='Y' selected>監視中</option>\n";
                        echo "    <option value='N'>非監視</option>\n";
                    } else {
                        echo "    <option value='Y'>監視中</option>\n";
                        echo "    <option value='N' selected>非監視</option>\n";
                    }
                    echo "</select></td>\n";
                    // print("<td align='center' nowrap><input type='text' name='csv_flg' size='1' value='" . $res[$r][6] . "' maxlength='1' class='center'></td>\n");
                    echo "<td class='winbox' align='center' nowrap><select name='csv_flg'>\n";
                    echo "    <option value='0' "; if ($res[$r][6]==0)echo 'selected'; echo ">出力Off</option>\n";
                    echo "    <option value='1' "; if ($res[$r][6]==1)echo 'selected'; echo ">Netmoni</option>\n";
                    echo "    <option value='2' "; if ($res[$r][6]==2)echo 'selected'; echo ">FWS1</option>\n";
                    echo "    <option value='3' "; if ($res[$r][6]==3)echo 'selected'; echo ">FWS2</option>\n";
                    echo "    <option value='4' "; if ($res[$r][6]==4)echo 'selected'; echo ">FWS3</option>\n";
                    echo "    <option value='5' "; if ($res[$r][6]==5)echo 'selected'; echo ">FWS4</option>\n";
                    echo "    <option value='6' "; if ($res[$r][6]==6)echo 'selected'; echo ">FWS5</option>\n";
                    echo "    <option value='7' "; if ($res[$r][6]==7)echo 'selected'; echo ">FWS6</option>\n";
                    echo "    <option value='8' "; if ($res[$r][6]==8)echo 'selected'; echo ">FWS7</option>\n";
                    echo "    <option value='9' "; if ($res[$r][6]==9)echo 'selected'; echo ">FWS8</option>\n";
                    echo "    <option value='10' "; if ($res[$r][6]==10)echo 'selected'; echo ">FWS9</option>\n";
                    echo "    <option value='11' "; if ($res[$r][6]==11)echo 'selected'; echo ">FWS10</option>\n";
                    echo "    <option value='12' "; if ($res[$r][6]==12)echo 'selected'; echo ">FWS11</option>\n";
                    echo "    <option value='101' "; if ($res[$r][6]==101)echo 'selected'; echo ">Net&FWS</option>\n";
                    echo "    <option value='201' "; if ($res[$r][6]==201)echo 'selected'; echo ">その他</option>\n";
                    echo "</select></td>\n";
                    print("<td class='winbox' align='center' nowrap><input type='text' name='sagyouku' size='3' value='" . $res[$r][7] . "' maxlength='3' class='center'></td>\n");
                    print("<td class='winbox' align='center' nowrap><input type='text' name='denryoku' size='8' value='" . $res[$r][8] . "' maxlength='7' class='right'></td>\n");
                    print("<td class='winbox' align='center' nowrap><input type='text' name='keisuu' size='4' value='" . $res[$r][9] . "' maxlength='4' class='right'></td>\n");
                    print("</tr>\n");
                    print("</form>\n");
                }
                echo "</table>\n";
                echo "    </td></tr>\n";  // ダミー
                print("</table>\n");
            }
            break;
        case "delete":      // マスターの削除
            $query = "select mac_no,mac_name,maker_name,maker,factory,survey,csv_flg,sagyouku,denryoku,keisuu 
                    from equip_machine_master2 order by mac_no";
            $res = array();
            if ( ($rows=getResult($query,$res)) >= 1) {      // equip_machine_master より取得
                echo "<table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
                print("<caption>マスターの削除(完全削除のため注意)</caption>\n");
                echo "<tr><td>\n";  // ダミー
                echo "<table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
                print(" <th class='winbox' width='40'><span class='fc_red'>削除</span></th><th class='winbox' width='80'>機械番号</th><th class='winbox' width='80'>機械名称</th><th class='winbox' width='80'>メーカー型式</th>
                    <th class='winbox' width='80'>メーカー名</th><th class='winbox' width='80'>工場区分</th><th class='winbox' nowrap>監視</th><th class='winbox' nowrap>監視方式</th><th class='winbox' nowrap>作業区</th><th class='winbox' nowrap>使用電力</th><th class='winbox' nowrap>電力係数</th>\n");
                for ($r=0; $r<$rows; $r++) {
                    echo "<form action='", $menu->out_self(), "' method='post'>\n";
                    print("<input type='hidden' name='mac_no' value='" . $res[$r][0] . "'>\n");
                    print("<tr>\n");
                    print(" <td class='winbox' align='center'><input type='submit' name='delete' value='" . ($r + 1) . "' onClick='return chk_del_equip_mac_mst()'></td>\n");
                    print(" <td class='winbox' align='center' nowrap>" . $res[$r][0] . "</td>\n");
                    print(" <td class='winbox' align='left' nowrap>" . $res[$r][1] . "</td>\n");
                    print(" <td class='winbox' align='left' nowrap>" . $res[$r][2] . "</td>\n");
                    print(" <td class='winbox' align='left' nowrap>" . $res[$r][3] . "</td>\n");
                    print(" <td class='winbox' align='center' nowrap>" . $res[$r][4] . "工場</td>\n");
                    ////////////////// 監視する／しない
                    if ($res[$r][5] == 'Y') {
                        print(" <td class='winbox' align='center' nowrap>監視</td>\n");
                    } else {
                        print(" <td class='winbox' align='center' nowrap>非監視</td>\n");
                    }
                    ////////////////// CSV 出力
                    if ($res[$r][6] == '0') {               // CSV出力 Off
                        print(" <td class='winbox' align='center' nowrap>出力Off</td>\n");
                    } elseif ($res[$r][6] == '1') {         // CSV出力 Netmoni
                        print(" <td class='winbox' align='center' nowrap>Netmoni方式</td>\n");
                    } elseif ($res[$r][6] == '2') {         // CSV出力 FWS1方式
                        print(" <td class='winbox' align='center' nowrap>FWS1 方式</td>\n");
                    } elseif ($res[$r][6] == '3') {         // CSV出力 FWS2方式
                        print(" <td class='winbox' align='center' nowrap>FWS2 方式</td>\n");
                    } elseif ($res[$r][6] == '4') {
                        print(" <td class='winbox' align='center' nowrap>FWS3 方式</td>\n");
                    } elseif ($res[$r][6] == '5') {
                        print(" <td class='winbox' align='center' nowrap>FWS4 方式</td>\n");
                    } elseif ($res[$r][6] == '6') {
                        print(" <td class='winbox' align='center' nowrap>FWS5 方式</td>\n");
                    } elseif ($res[$r][6] == '7') {
                        print(" <td class='winbox' align='center' nowrap>FWS6 方式</td>\n");
                    } elseif ($res[$r][6] == '8') {
                        print(" <td class='winbox' align='center' nowrap>FWS7 方式</td>\n");
                    } elseif ($res[$r][6] == '9') {
                        print(" <td class='winbox' align='center' nowrap>FWS8 方式</td>\n");
                    } elseif ($res[$r][6] == '10') {
                        print(" <td class='winbox' align='center' nowrap>FWS9 方式</td>\n");
                    } elseif ($res[$r][6] == '11') {
                        print(" <td class='winbox' align='center' nowrap>FWS10 方式</td>\n");
                    } elseif ($res[$r][6] == '12') {
                        print(" <td class='winbox' align='center' nowrap>FWS11 方式</td>\n");
                    } elseif ($res[$r][6] == '101') {      // Netmoni & FWS方式
                        print(" <td class='winbox' align='center' nowrap>Net&FWS方式</td>\n");
                    } else {                                // その他
                        print(" <td class='winbox' align='center' nowrap>その他</td>\n");
                    }
                    print(" <td class='winbox' align='center' nowrap>" . $res[$r][7] . "</td>\n");     // 作業区
                    if($res[$r][8] == "") {
                        print(" <td class='winbox' align='center' nowrap>-</td>\n");
                    } else {
                        print(" <td class='winbox' align='right' nowrap>" . $res[$r][8] . "</td>\n");
                    }
                    if ($res[$r][9] == "") {
                        print(" <td class='winbox' align='center' nowrap>-</td>\n");
                    } else {
                        print(" <td class='winbox' align='right' nowrap>" . $res[$r][9] . "</td>\n");
                    }
                    print("</tr>\n");
                    print("</form>\n");
                }
                echo "</table>\n";
                echo "    </td></tr>\n";  // ダミー
                print("</table>\n");
            }
            break;
        default:            // 最新の設備･機械マスター 表示
            $query = "select mac_no,mac_name,maker_name,maker,factory,survey,csv_flg,sagyouku,denryoku,keisuu 
                    from equip_machine_master2 order by mac_no";
            $res = array();
            if ( ($rows=getResult($query,$res)) >= 1) {      // equip_machine_master よりを取得
                echo "<table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
                // print("<caption>マスター 一覧</caption>\n");
                echo "<tr><td>\n";  // ダミー
                echo "<table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
                print(" <th class='winbox' nowrap>機械番号</th><th class='winbox' width='80'>機械名称</th><th class='winbox' nowrap>メーカー型式</th>
                    <th class='winbox' nowrap>メーカー名</th><th class='winbox' nowrap>工場区分</th><th class='winbox' nowrap>監視</th>
                    <th class='winbox' nowrap>監視方式</th><th class='winbox' nowrap>作業区</th><th class='winbox' nowrap>使用電力</th>
                    <th class='winbox' nowrap>電力係数</th>\n");
                for ($r=0; $r<$rows; $r++) {
                    print("<tr>\n");
                    print(" <td class='winbox' align='center' nowrap>" . $res[$r][0] . "</td>\n");
                    print(" <td class='winbox' align='left' nowrap>" . $res[$r][1] . "</td>\n");
                    print(" <td class='winbox' align='left' nowrap>" . $res[$r][2] . "</td>\n");
                    print(" <td class='winbox' align='left' nowrap>" . $res[$r][3] . "</td>\n");
                    print(" <td class='winbox' align='center' nowrap>" . $res[$r][4] . "工場</td>\n");
                    ////////////////// 監視する／しない
                    if ($res[$r][5] == 'Y') {
                        print(" <td class='winbox' align='center' nowrap>監視</td>\n");
                    } else {
                        print(" <td class='winbox' align='center' nowrap>非監視</td>\n");
                    }
                    ////////////////// CSV 出力
                    if ($res[$r][6] == '0') {               // CSV出力 Off
                        print(" <td class='winbox' align='center' nowrap>出力Off</td>\n");
                    } elseif ($res[$r][6] == '1') {         // CSV出力 Netmoni
                        print(" <td class='winbox' align='center' nowrap>Netmoni方式</td>\n");
                    } elseif ($res[$r][6] == '2') {         // CSV出力 FWS1方式
                        print(" <td class='winbox' align='center' nowrap>FWS1 方式</td>\n");
                    } elseif ($res[$r][6] == '3') {         // CSV出力 FWS2方式
                        print(" <td class='winbox' align='center' nowrap>FWS2 方式</td>\n");
                    } elseif ($res[$r][6] == '4') {
                        print(" <td class='winbox' align='center' nowrap>FWS3 方式</td>\n");
                    } elseif ($res[$r][6] == '5') {
                        print(" <td class='winbox' align='center' nowrap>FWS4 方式</td>\n");
                    } elseif ($res[$r][6] == '6') {
                        print(" <td class='winbox' align='center' nowrap>FWS5 方式</td>\n");
                    } elseif ($res[$r][6] == '7') {
                        print(" <td class='winbox' align='center' nowrap>FWS6 方式</td>\n");
                    } elseif ($res[$r][6] == '8') {
                        print(" <td class='winbox' align='center' nowrap>FWS7 方式</td>\n");
                    } elseif ($res[$r][6] == '9') {
                        print(" <td class='winbox' align='center' nowrap>FWS8 方式</td>\n");
                    } elseif ($res[$r][6] == '10') {
                        print(" <td class='winbox' align='center' nowrap>FWS9 方式</td>\n");
                    } elseif ($res[$r][6] == '11') {
                        print(" <td class='winbox' align='center' nowrap>FWS10 方式</td>\n");
                    } elseif ($res[$r][6] == '12') {
                        print(" <td class='winbox' align='center' nowrap>FWS11 方式</td>\n");
                    } elseif ($res[$r][6] == '101') {      // Netmoni & FWS方式
                        print(" <td class='winbox' align='center' nowrap>Net&FWS方式</td>\n");
                    } else {                                // その他
                        print(" <td class='winbox' align='center' nowrap>その他</td>\n");
                    }
                    ////////////////// 作業区
                    print(" <td class='winbox' align='center' nowrap>" . $res[$r][7] . "</td>\n");
                    if ($res[$r][8] == "") {
                        print(" <td class='winbox' align='center' nowrap>-</td>\n");
                    } else {
                        print(" <td class='winbox' align='right' nowrap>" . $res[$r][8] . "</td>\n");
                    }
                    if ($res[$r][9] == "") {
                        print(" <td class='winbox' align='center' nowrap>-</td>\n");
                    } else {
                        print(" <td class='winbox' align='right' nowrap>" . $res[$r][9] . "</td>\n");
                    }
                    print("</tr>\n");
                }
                echo "</table>\n";
                echo "    </td></tr>\n";  // ダミー
                print("</table>\n");
            }
        }
        ?>
    </td></tr>
</center>
</table>
</body>
<?=$menu->out_alert_java()?>
</html>
<?php
ob_end_flush();  //Warning: Cannot add header の対策のため追加。
?>
