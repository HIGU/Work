<?php
//////////////////////////////////////////////////////////////////////////////
// 社員メニューの社員名簿・教育訓練記録 印刷の Branch (分岐)処理            //
// Copyright(C) 2004-2019 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2004/02/20 Created   print_emp_branch.php                                //
// 2007/10/15 E_ALL → E_ALL | E_STRICT  その他                             //
// 2010/06/16 暫定的に大渕さん（970268）が印刷できるように変更         大谷 //
// 2018/04/20 前期分のみの教育・異動経歴を追加                         大谷 //
// 2019/09/17 有給管理台帳の年度と部門の受け渡しを追加                 大谷 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting',E_ALL);       // E_ALL='2047' debug 用
// ini_set('display_errors','1');          // Error 表示 ON debug 用 リリース後コメント
ob_start('ob_gzhandler');               // 出力バッファをgzip圧縮
session_start();                        // ini_set()の次に指定すること Script 最上行
require_once ('../../function.php');
require_once ('../../tnk_func.php');
access_log();                           // Script Name は自動取得

////// 呼出元の保存
$_SESSION['url_referer'] = H_WEB_HOST . EMP_MENU;           // 呼出もとのURLをセッションに保存
// $_SESSION['act_referer'] = $_SERVER['HTTP_REFERER'];     // 呼出もとのURLをセッションに保存
$url_referer = $_SESSION['url_referer'];

////////////// 認証チェック
if (!isset($_SESSION["User_ID"]) || !isset($_SESSION["Password"]) || !isset($_SESSION["Auth"])) {
// if (account_group_check2() == FALSE) {
// if (account_group_check() == FALSE) {
    if ($_SESSION['User_ID'] != '970268') {
        $_SESSION['s_sysmsg'] = "あなたは許可されていません。<br>管理者に連絡して下さい。";
        header('Location: ' . $url_referer);
        // header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }
}

////////// 対象スクリプトの取得
if (isset($_POST['emp_name'])) {
    $emp_name = $_POST['emp_name'];
} elseif (isset($_GET['emp_name'])) {
    $emp_name = $_GET['emp_name'];
    // $_SESSION['s_sysmsg'] = $_GET['emp_name'];   // Debug用
} else {
    $emp_name = '';
}

////////// 台帳対象年度の取得
if (isset($_POST['yukyulist'])) {
    $list_year = $_POST['yukyulist'];
    $_SESSION['yukyulist'] = $_POST['yukyulist'];
} elseif (isset($_GET['yukyulist'])) {
    $list_year = $_GET['yukyulist'];
    $_SESSION['yukyulist'] = $_GET['yukyulist'];
    // $_SESSION['s_sysmsg'] = $_GET['emp_name'];   // Debug用
} else {
    $ym    =  date("Ym");
    $tmp   = $ym - 200003;
    $tmp   = $tmp / 100;
    $ki    = ceil($tmp);
    $nk_ki = $ki + 44;
    $yyyy = substr($ym, 0,4);
    $mm   = substr($ym, 4,2);

    // 年度計算
    if ($mm < 4) {              // 1〜3月の場合
        $business_year = $yyyy - 1;
    } else {
        $business_year = $yyyy;
    }
    $list_year = $business_year;
    $_SESSION['yukyulist'] = $list_year;
}
///////// 対象部門の取得
if (isset($_POST['fivesection'])) {
    $fivesection = $_POST['fivesection'];
    $_SESSION['fivesection'] = $_POST['fivesection'];
} elseif (isset($_GET['fivesection'])) {
    $fivesection = $_GET['fivesection'];
    $_SESSION['fivesection'] = $_GET['fivesection'];
    // $_SESSION['s_sysmsg'] = $_GET['emp_name'];   // Debug用
} else {
    $fivesection = '-1';
    $_SESSION['fivesection'] = $fivesection;
}
////////// 対照スクリプトへ分岐
switch ($emp_name) {
    
case '社員名簿(部署別)明朝'     :
case 'print_emp_section_ja'     :
    $script_name = EMP . 'print/print_emp_section_ja.php';
    ////////////// サイトメニュー設定
    $_SESSION['site_index'] =  3;       // 社員メニュー = 3 最後のメニューは 99 を使用
    $_SESSION['site_id']    =  1;       // 社員名簿(部署別)明朝 = 1  下位メニュー無し (0 <=) 下位メニューの表示のみ = 999
    break;
    
case '社員名簿(部署別)ゴシック' :
case 'print_emp_section_mbfpdf' :
    $script_name = EMP . 'print/print_emp_section_mbfpdf.php';
    ////////////// サイトメニュー設定
    $_SESSION['site_index'] =  3;       // 社員メニュー = 3 最後のメニューは 99 を使用
    $_SESSION['site_id']    =  2;       // 社員名簿(部署別)明朝 = 1  下位メニュー無し (0 <=) 下位メニューの表示のみ = 999
    break;
    
case '社員名簿(職位別)明朝'     :
case 'print_emp_position_ja'    :
    $script_name = EMP . 'print/print_emp_position_ja.php';
    ////////////// サイトメニュー設定
    $_SESSION['site_index'] =  3;       // 社員メニュー = 3 最後のメニューは 99 を使用
    $_SESSION['site_id']    =  3;       // 社員名簿(部署別)明朝 = 1  下位メニュー無し (0 <=) 下位メニューの表示のみ = 999
    break;
    
case '社員名簿(職位別)ゴシック' :
case 'print_emp_position_mbfpdf':
    $script_name = EMP . 'print/print_emp_position_mbfpdf.php';
    ////////////// サイトメニュー設定
    $_SESSION['site_index'] =  3;       // 社員メニュー = 3 最後のメニューは 99 を使用
    $_SESSION['site_id']    =  4;       // 社員名簿(部署別)明朝 = 1  下位メニュー無し (0 <=) 下位メニューの表示のみ = 999
    break;
    
case '教育・移動履歴' :
case 'print_emp_history_mbfpdf':
    $script_name = EMP . 'print/print_emp_history_mbfpdf.php';
    ////////////// サイトメニュー設定
    $_SESSION['site_index'] =  3;       // 社員メニュー = 3 最後のメニューは 99 を使用
    $_SESSION['site_id']    =  5;       // 社員名簿(部署別)明朝 = 1  下位メニュー無し (0 <=) 下位メニューの表示のみ = 999
    break;

case '前期教育・移動履歴' :
case 'print_emp_history_z_mbfpdf':
    $script_name = EMP . 'print/print_emp_history_z_mbfpdf.php';
    ////////////// サイトメニュー設定
    $_SESSION['site_index'] =  3;       // 社員メニュー = 3 最後のメニューは 99 を使用
    $_SESSION['site_id']    =  6;       // 社員名簿(部署別)明朝 = 1  下位メニュー無し (0 <=) 下位メニューの表示のみ = 999
    break;

case '有給管理台帳' :
case 'print_yukyu_five_list':
    $script_name = EMP . 'print/print_yukyu_five_list.php?yukyulist=' . $list_year . '&fivesection=' . $fivesection;
    ////////////// サイトメニュー設定
    $_SESSION['site_index'] =  3;       // 社員メニュー = 3 最後のメニューは 99 を使用
    //$_SESSION['site_id']    =  5;       // 社員名簿(部署別)明朝 = 1  下位メニュー無し (0 <=) 下位メニューの表示のみ = 999
    break;

    
default:
    $script_name = EMP_MENU;           // 呼出もとへ帰る
    $url_name    = $url_referer;       // 呼出もとのURL 別メニューから呼び出された時の対応
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title>社員メニュー印刷 分岐処理</title>
<script type='text/javascript' language='JavaScript'>
<!--
    parent.menu_site.location = '<?php echo H_WEB_HOST . SITE_MENU ?>';
// -->
</script>

<style type='text/css'>
<!--
body {
    margin:     20%;
    font-size:  24pt;
}
-->
</style>
</head>
<body>
    <center>
        印刷イメージ(PDF)生成中です。<br>
        お待ち下さい。<br>
        <img src='../../img/tnk-turbine.gif' width=68 height=72>
    </center>

    <script type='text/javascript' language='JavaScript'>
    <!--
    <?php
        if (isset($url_name)) {
            echo "location = '$url_name'";
        } else {
            echo "location = '" . H_WEB_HOST . "$script_name'";
        }
    ?>
    // -->
    </script>
</body>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
