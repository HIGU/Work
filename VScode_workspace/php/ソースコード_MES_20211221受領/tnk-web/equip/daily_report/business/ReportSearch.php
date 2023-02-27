<?php 
//////////////////////////////////////////////////////////////////////////////
// ������Ư���������ƥ�ε�����ž���� ���󸡺��ե�����                      //
// Copyright (C) 2004-2021 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Original by yamagishi@matehan.co.jp                                      //
// Changed history                                                          //
// 2004/07/15 Created  ReportSearch.php                                     //
// 2004/09/27 �����̱�ž������б� ���å�����ѿ��ˤ�빩���ʬ�б�         //
// 2005/02/02 decision_flg=0 �� decision_flg!=1 �ؽ����Ѥ�=2�б��Τ����ѹ�  //
// 2005/02/25 ®�٥��åפΤ��᱿ž�����ϰϤ�default��From��Ʊ��κǽ�����   //
// 2006/04/13 �إ�ץ�����ɥ��Υ������ѹ����ǽ�� resizable=yes            //
// 2006/04/17 ����CSS��cssConversion.css ���ѹ�                             //
// 2007/04/04 JavaScript��dateCheck()�ؿ����ɲá�����ե��������򳫻Ϸ��   //
// 2018/05/18 ��������ɲá������ɣ�����Ͽ����Ū��7���ѹ�            ��ë //
// 2021/03/11 ���ͤθ������ɲ�                                         ��ë //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);
ob_start('ob_gzhandler');

require_once ('../../../function.php');     // TNK ������ function
require_once ('../../../MenuHeader.php');   // TNK ������ menu class
require_once ('../com/define.php');
require_once ('../com/function.php');
require_once ('../com/mu_date.php');

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ�٥�=0, �꥿���󥢥ɥ쥹, �����ȥ�λ���ʤ�
access_log();                               // Script Name �ϼ�ư����

///// �ե졼���Ǥϥ������å����꤬ɬ��
$menu->set_target('_parent');               // �꥿������������Ͽ�

//////////// �����ȥ������
if (isset($_SESSION['factory'])) $factory = $_SESSION['factory']; else $factory = '';
switch ($factory) {
case 1:
    $title = '������ž���� ������';
    break;
case 2:
    $title = '������ž���� ������';
    break;
case 3:
    $title = '������ž���� ������';
    break;
case 4:
    $title = '������ž���� ������';
    break;
case 5:
    $title = '������ž���� ������';
    break;
case 6:
    $title = '������ž���� ������';
    break;
case 7:
    $title = '������ž���� ������(���)';
    break;
case 8:
    $title = '������ž���� ������(SUS)';
    break;
default:
    $title = '������ž���� ������';
    break;
}
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title($title);

// �����ԥ⡼��
$AdminUser = AdminUser( FNC_REPORT );

// �ǥե���ȳ������դ�������ꤵ��Ƥ��ʤ����ָŤ�����
$con = getConnection();

$query = "select min(work_date) as work_date
            from
                equip_work_report r
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

// ���դ�ʬ��
$FromYear  = mu_Date::toString($FromDate,'Y');
$FromMonth = mu_Date::toString($FromDate,'m');
$FromDay   = mu_Date::toString($FromDate,'d');

/**********************************
$ToYear  = date('Y', time());
$ToMonth = date('m', time());
$ToDay   = date('d', time());
**********************************/

/////////// ®�٥��åפΤ������ͤ�Ʊ��κǽ��� 2005/02/25 ADD
$ToYear  = $FromYear;
$ToMonth = $FromMonth;
$ToDay   = mu_Date::toString(mu_Date::getLastDate("$FromYear$FromMonth$FromDay", '30'), 'd');

// ���̥إå��ν���
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
    // ����ǯ
    if (document.MainForm.FromYear.value.length < 4) {
        document.MainForm.FromYear.focus(); 
        alert("���� ǯ�򣴷����Ϥ��Ʋ�������");
        return false;
    }
    
    // ���Ϸ�
    if (document.MainForm.FromMonth.value.length < 2) document.MainForm.FromMonth.focus();
    if (document.MainForm.FromMonth.value.length == 0) {
        alert("���� ����Ϥ���Ƥ��ޤ���");
        return false;
    }
    if (document.MainForm.FromMonth.value.length == 1) document.MainForm.FromMonth.value = "0" + document.MainForm.FromMonth.value; 
    
    // ������
    if (document.MainForm.FromDay.value.length < 2) document.MainForm.FromDay.focus();
    if (document.MainForm.FromDay.value.length == 0) {
        alert("���� �������Ϥ���Ƥ��ޤ���");
        return false;
    }
    if (document.MainForm.FromDay.value.length == 1) document.MainForm.FromDay.value = "0" + document.MainForm.FromDay.value; 
    
    // ��λǯ
    if (document.MainForm.ToYear.value.length < 4) {
        document.MainForm.ToYear.focus(); 
        alert("��λ ǯ�򣴷����Ϥ��Ʋ�������");
        return false;
    }
    
    // ��λ��
    if (document.MainForm.ToMonth.value.length < 2) document.MainForm.ToMonth.focus();
    if (document.MainForm.ToMonth.value.length == 0) {
        alert("��λ ����Ϥ���Ƥ��ޤ���");
        return false;
    }
    if (document.MainForm.ToMonth.value.length == 1) document.MainForm.ToMonth.value = "0" + document.MainForm.ToMonth.value; 
    
    // ��λ��
    if (document.MainForm.ToDay.value.length < 2) document.MainForm.ToDay.focus();
    if (document.MainForm.ToDay.value.length == 0) {
        alert("��λ �������Ϥ���Ƥ��ޤ���");
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
/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus() {
    // document.body.focus();   // F2/F12������ͭ���������б�
    // document.mhForm.backwardStack.focus();  // �嵭��IE�ΤߤΤ���NN�б�
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
                <td style='width:100;' class='HED Conversion'>��ž��</td>
                <td style='width:300;' class='Conversion'>
                    <input type='text' name='FromYear'  value='<?=$FromYear?>' size='4' maxlength='4' class='NUM'>/<input type='text' name='FromMonth' value='<?=$FromMonth?>' size='2' maxlength='2' class='NUM'>/<input type='text' name='FromDay' value='<?=$FromDay?>' size='2' maxlength='2' class='NUM'> ��
                    <input type='text' name='ToYear'    value='<?=$ToYear?>' size='4' maxlength='4' class='NUM'>/<input type='text' name='ToMonth'   value='<?=$ToMonth?>' size='2' maxlength='2' class='NUM'>/<input type='text' name='ToDay' value='<?=$ToDay?>' size='2' maxlength='2' class='NUM'> 
                </td>
                <td style='width:100;' class='HED Conversion'>����No.</td>
                <td style='width:100;' class='Conversion' align='center'>
                    <input type='text' name='MacNo' size='6' maxlength='6' class='NUM'>
                </td>
            </tr>
            <tr class='Conversion'>
                <td style='width:100;' class='HED Conversion'>�������</td>
                <td style='width:300;' class='Conversion'>
                    <input type='radio' name='Decision' value='Z' ID='DecisionA' checked><label for='DecisionA'>���٤�</label>
                    <input type='radio' name='Decision' value='0' ID='DecisionB'><label for='DecisionB'>̤����</label>
                    <input type='radio' name='Decision' value='1' ID='DecisionC'><label for='DecisionC'>�����</label>
                </td>
                <td style='width:100;' class='HED Conversion'>ɽ����</td>
                <td style='width:100;' class='Conversion' align='center'>
                    <select name='ListNum'><?=SelectPageListNumOptions()?></select>
                </td>
            </tr>
            <tr class='Conversion'>
                <td style='width:100;' class='HED Conversion'>����</td>
                <td style='width:500;' colspan='3' class='Conversion'>
                    <input type='radio' name='Remark' value='Z' ID='RemarkA' checked><label for='RemarkA'>���٤�</label>
                    <input type='radio' name='Remark' value='1A' ID='RemarkB'><label for='RemarkB'>����(���٤�)</label>
                    <input type='radio' name='Remark' value='16' ID='RemarkC'><label for='RemarkC'>����(�����Τ�)</label>
                    <input type='radio' name='Remark' value='1N' ID='RemarkD'><label for='RemarkD'>����(�����ʳ�)</label>
                    <input type='radio' name='Remark' value='0' ID='RemarkE'><label for='RemarkE'>�ʤ�</label>
                </td>
            </tr>
        </table>
        <br>
        <input style='font-size:10pt; font-weight:bold; color:blue;' type='button' name='work_mnt_help' value='HELP' onClick='win_open("../help/ReportList_help.html")'>
        <input type='submit' value='����ɽ��' style='width:80;'>
        <?php if ($AdminUser) { ?>
        <input type='button' value='������Ͽ' style='width:80;' onClick='NewEdit()'>
        <?php } ?>
        <?php if (@$_REQUEST['RetUrl'] != '') { ?>
        <input type='button' value='�ᡡ��' style='width:80;' onClick='doBack()'>
        <?php } ?>
    </center>
</form>
</body>
</html>
