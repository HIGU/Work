<?php 
//////////////////////////////////////////////////////////////////////////////
// 組立設備稼働管理システムの機械運転日報 日報検索フォーム                  //
// Copyright (C) 2021-2021 norihisa_ooya@nitto-kohki.co.jp                  //
// Original by yamagishi@matehan.co.jp                                      //
// Changed history                                                          //
// 2021/03/26 Created  ReportSearch.php                                     //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);
ob_start('ob_gzhandler');

require_once ('../../../function.php');     // TNK 全共通 function
require_once ('../../../MenuHeader.php');   // TNK 全共通 menu class
require_once ('../com/define.php');
require_once ('../com/function.php');
require_once ('../com/mu_date.php');

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証レベル=0, リターンアドレス, タイトルの指定なし
access_log();                               // Script Name は自動取得

///// フレーム版はターゲット設定が必要
$menu->set_target('_parent');               // リターン時の戻り先は親

//////////// タイトルの設定
if (isset($_SESSION['factory'])) $factory = $_SESSION['factory']; else $factory = '';
switch ($factory) {
case 1:
    $title = '機械運転日報 １工場';
    break;
case 2:
    $title = '機械運転日報 ２工場';
    break;
case 3:
    $title = '機械運転日報 ３工場';
    break;
case 4:
    $title = '機械運転日報 ４工場';
    break;
case 5:
    $title = '機械運転日報 ５工場';
    break;
case 6:
    $title = '機械運転日報 ６工場';
    break;
case 7:
    $title = '機械運転日報 ７工場(真鍮)';
    break;
case 8:
    $title = '機械運転日報 ７工場(SUS)';
    break;
default:
    $title = '機械運転日報 全工場';
    break;
}
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title($title);

// 管理者モード
$AdminUser = AdminUser( FNC_REPORT );

// デフォルト開始日付は日報確定されていない一番古い日～
$con = getConnection();

$query = "select min(work_date) as work_date
            from
                equip_work_report_moni r
            left outer join
                equip_machine_master2 e
                on r.mac_no=e.mac_no
          where
            decision_flg!=1
";
if ($factory != '') {
    $query .= "and e.factory={$factory}";
}
$rs = pg_query($con, $query);
if ($row = pg_fetch_array ($rs)) {
    $FromDate = $row['work_date'];
} else {
    $FromDate = date('Ymd', time());
}

// 日付の分割
$FromYear  = mu_Date::toString($FromDate,'Y');
$FromMonth = mu_Date::toString($FromDate,'m');
$FromDay   = mu_Date::toString($FromDate,'d');

/**********************************
$ToYear  = date('Y', time());
$ToMonth = date('m', time());
$ToDay   = date('d', time());
**********************************/

/////////// 速度アップのため初期値は同月の最終日 2005/02/25 ADD
$ToYear  = $FromYear;
$ToMonth = $FromMonth;
$ToDay   = mu_Date::toString(mu_Date::getLastDate("$FromYear$FromMonth$FromDay", '30'), 'd');

// 共通ヘッダの出力
SetHttpHeader();
?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
<html>
<head>
<?php require_once ('../com/PageHeaderOnly.php'); ?>
<script type='text/javascript' language='JavaScript'>
<!--
    var CONTEXT_PATH = '<?=CONTEXT_PATH?>';
<?php if ($AdminUser) { ?>
function NewEdit() {
    document.MainForm.ProcCode.value = 'EDIT';
    document.MainForm.action = 'ReportEntry.php';
    document.MainForm.submit();
}
<?php } ?>
<?php if (@$_REQUEST['RetUrl'] != '') { ?>
function doBack() {
    document.MainForm.action = '<?=@$_REQUEST['RetUrl']?>';
    document.MainForm.target = '_parent';
    document.MainForm.submit();
}
<?php } ?>
function ViewList() {
    if (!dateCheck()) return false;
    document.MainForm.ProcCode.value = 'VIEW';
    document.MainForm.action = 'ReportList.php';
    document.MainForm.submit();
    return false;
}
function dateCheck() {
    // 開始年
    if (document.MainForm.FromYear.value.length < 4) {
        document.MainForm.FromYear.focus(); 
        alert("開始 年を４桁入力して下さい。");
        return false;
    }
    
    // 開始月
    if (document.MainForm.FromMonth.value.length < 2) document.MainForm.FromMonth.focus();
    if (document.MainForm.FromMonth.value.length == 0) {
        alert("開始 月が入力されていません。");
        return false;
    }
    if (document.MainForm.FromMonth.value.length == 1) document.MainForm.FromMonth.value = "0" + document.MainForm.FromMonth.value; 
    
    // 開始日
    if (document.MainForm.FromDay.value.length < 2) document.MainForm.FromDay.focus();
    if (document.MainForm.FromDay.value.length == 0) {
        alert("開始 日が入力されていません。");
        return false;
    }
    if (document.MainForm.FromDay.value.length == 1) document.MainForm.FromDay.value = "0" + document.MainForm.FromDay.value; 
    
    // 終了年
    if (document.MainForm.ToYear.value.length < 4) {
        document.MainForm.ToYear.focus(); 
        alert("終了 年を４桁入力して下さい。");
        return false;
    }
    
    // 終了月
    if (document.MainForm.ToMonth.value.length < 2) document.MainForm.ToMonth.focus();
    if (document.MainForm.ToMonth.value.length == 0) {
        alert("終了 月が入力されていません。");
        return false;
    }
    if (document.MainForm.ToMonth.value.length == 1) document.MainForm.ToMonth.value = "0" + document.MainForm.ToMonth.value; 
    
    // 終了日
    if (document.MainForm.ToDay.value.length < 2) document.MainForm.ToDay.focus();
    if (document.MainForm.ToDay.value.length == 0) {
        alert("終了 日が入力されていません。");
        return false;
    }
    if (document.MainForm.ToDay.value.length == 1) document.MainForm.ToDay.value = "0" + document.MainForm.ToDay.value; 
    
    return true;
}
function win_open(url) {
    var w = 800;
    var h = 600;
    var left = (screen.availWidth  - w) / 2;
    var top  = (screen.availHeight - h) / 2;
    window.open(url, 'help_win', 'width='+w+',height='+h+',resizable=yes,scrollbars=yes,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
}
/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus() {
    // document.body.focus();   // F2/F12キーを有効化する対応
    // document.mhForm.backwardStack.focus();  // 上記はIEのみのためNN対応
    document.MainForm.FromMonth.focus();
}
// -->
</script>
<script type='text/javascript' language='JavaScript' src='<?=SEARCH_JS?>'></script>
<?=$menu->out_css()?>
<LINK rel='stylesheet' href='<?=CONTEXT_PATH?>com/cssConversion.css' type='text/css'>
</head>
<body onLoad='set_focus()' style='overflow:hidden;'>
<?=$menu->out_title_border()?>
<form name='MainForm' method='post' target='ListFream' onSubmit='return ViewList()'>
<input type='hidden' name='ProcCode' value=''>
<input type='hidden' name='EDIT_MODE' value='INSERT'>
<input type='hidden' name='EntryErrorLevel' value='0'>
<!-- <Div class='TITLE'><?=$title?></Div> -->
<br>
    <center>
        <table border='1' class='Conversion'>
            <tr class='Conversion'>
                <td style='width:100;' class='HED Conversion'>運転日</td>
                <td style='width:300;' class='Conversion'>
                    <input type='text' name='FromYear'  value='<?=$FromYear?>' size='4' maxlength='4' class='NUM'>/<input type='text' name='FromMonth' value='<?=$FromMonth?>' size='2' maxlength='2' class='NUM'>/<input type='text' name='FromDay' value='<?=$FromDay?>' size='2' maxlength='2' class='NUM'> ～
                    <input type='text' name='ToYear'    value='<?=$ToYear?>' size='4' maxlength='4' class='NUM'>/<input type='text' name='ToMonth'   value='<?=$ToMonth?>' size='2' maxlength='2' class='NUM'>/<input type='text' name='ToDay' value='<?=$ToDay?>' size='2' maxlength='2' class='NUM'> 
                </td>
                <td style='width:100;' class='HED Conversion'>機械No.</td>
                <td style='width:100;' class='Conversion' align='center'>
                    <input type='text' name='MacNo' size='6' maxlength='6' class='NUM'>
                </td>
            </tr>
            <tr class='Conversion'>
                <td style='width:100;' class='HED Conversion'>日報確定</td>
                <td style='width:300;' class='Conversion'>
                    <input type='radio' name='Decision' value='Z' ID='DecisionA' checked><label for='DecisionA'>すべて</label>
                    <input type='radio' name='Decision' value='0' ID='DecisionB'><label for='DecisionB'>未確定</label>
                    <input type='radio' name='Decision' value='1' ID='DecisionC'><label for='DecisionC'>確定済</label>
                </td>
                <td style='width:100;' class='HED Conversion'>表示行</td>
                <td style='width:100;' class='Conversion' align='center'>
                    <select name='ListNum'><?=SelectPageListNumOptions()?></select>
                </td>
            </tr>
            <tr class='Conversion'>
                <td style='width:100;' class='HED Conversion'>備考</td>
                <td style='width:500;' colspan='3' class='Conversion'>
                    <input type='radio' name='Remark' value='Z' ID='RemarkA' checked><label for='RemarkA'>すべて</label>
                    <input type='radio' name='Remark' value='1A' ID='RemarkB'><label for='RemarkB'>あり(すべて)</label>
                    <input type='radio' name='Remark' value='16' ID='RemarkC'><label for='RemarkC'>あり(修理のみ)</label>
                    <input type='radio' name='Remark' value='1N' ID='RemarkD'><label for='RemarkD'>あり(修理以外)</label>
                    <input type='radio' name='Remark' value='0' ID='RemarkE'><label for='RemarkE'>なし</label>
                </td>
            </tr>
        </table>
        <br>
        <input style='font-size:10pt; font-weight:bold; color:blue;' type='button' name='work_mnt_help' value='HELP' onClick='win_open("../help/ReportList_help.html")'>
        <input type='submit' value='一覧表示' style='width:80;'>
        <!--
        <?php if ($AdminUser) { ?>
        <input type='button' value='新規登録' style='width:80;' onClick='NewEdit()'>
        <?php } ?>
        -->
        <?php if (@$_REQUEST['RetUrl'] != '') { ?>
        <input type='button' value='戻　る' style='width:80;' onClick='doBack()'>
        <?php } ?>
    </center>
</form>
</body>
</html>
