<?php 
ini_set('error_reporting', E_ALL);

require_once ('../com/define.php');
require_once ('../com/function.php');
require_once ('../com/PageControl.php');

// ���ͥ������μ���
$con = getConnection();

///// 2007/08/08 ���å����γ��ϥ����å����ɲ� Notice �б� k.kobayashi  2007/09/27 ')'��ȴ������
if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_REQUEST['MaxRows'])) {
    $sql = 'select count(*) as num from miitem';
    $rs = pg_query ($con , $sql);
    $row = pg_fetch_array($rs);
    $PageCtl['RowsNum']     = $row['num'];
} else {
    $PageCtl['RowsNum']     = $_REQUEST['MaxRows'];
}
/** �ڡ�������ȥ��� */

$PageCtl['ViewPage']    = @$_REQUEST['ViewPage'];
$PageCtl['ListNum']     = 10;


$sql = 'select mipn,midsc,mzist from miitem order by mipn offset ' . getStartRecNum($PageCtl['ViewPage'],$PageCtl['ListNum']) . ' limit 10';
$rs = pg_query ($con , $sql);


# $PageCtl['StartRecNum'] = getStartRecNum($PageCtl['ViewPage'],$PageCtl['ListNum']);
# $PageCtl['EndRecNum']   = getEndRecNum($PageCtl['ViewPage'],$PageCtl['ListNum'],$PageCtl['RowsNum']);

ob_start('ob_gzhandler');
?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
<html>
<head>
<?php require_once ('../com/PageHeader.php'); ?>
<SCRIPT language='JavaScript' SRC='../com/popup.js'></SCRIPT>
<script language="JavaScript">
function doSelect(code,name,zai){
    if (navigator.userAgent.indexOf('MSIE') > -1) {
        parent.returnValue = new Array(code,name,zai);
    } else {
        window.opener.NnRetValue = new Array(code,name,zai);
        window.opener.NnReturn();
    }
    window.close();
}
function MovePage(page) {
    var NextPage = page;
    if (page == 0) {
        var idx = document.MainForm.SelectPage.selectedIndex;
        NextPage = document.MainForm.SelectPage.options[idx].text;
    }
    
    document.MovePageForm.ViewPage.value = NextPage;
    document.MovePageForm.submit();
}
</script>
</head>
<body>
<form name='MovePageForm' action='Item.php' method='post'>
<input type='hidden' name='ProcCode' value='VIEW'>
<input type='hidden' name='ViewPage' value=''>
<input type='hidden' name='MaxRows' value='<?=$PageCtl['RowsNum']?>'>
</form>
<form name='MainForm'>
    <center>
<?php if (pg_num_rows ($rs) == 0){ ?>
<table border='0' class='LAYOUT' width='100%' height='100%'>
    <tr class='LAYOUT'>
        <td class='LAYOUT' align='center'>
            �����ǡ�����¸�ߤ��ޤ���
        </td>
    </tr>
</table>
<?php } else { ?>
        <table border='1'>
            <tr>
                <td class='HED'>
                </td>
                <td class='HED'>
                    �����ֹ�
                </td>
                <td class='HED'>
                    ����̾��
                </td>
                <td class='HED'>
                    ���ʺ��
                </td>
            </tr>
<?php
        for ($i=0;$i<10;$i++) {
            $row = pg_fetch_array ($rs); 
?>
            <tr>
                <td>
                    <input type='button' value='����' onClick='doSelect("<?=outHtml($row['mipn'])?>","<?=outHtml($row['midsc'])?>","<?=outHtml($row['mzist'])?>")'>
                <td nowrap>
                    <?=outHtml($row['mipn'])?>
                </td>
                <td nowrap>
                    <?=outHtml($row['midsc'],20)?>
                </td>
                <td nowrap>
                    <?=outHtml($row['mzist'],20)?>
                </td>
            </tr>
            <?php } ?>
        </table>
<?= getPageControlHtml($PageCtl['ViewPage'],$PageCtl['RowsNum'],$PageCtl['ListNum']) ?>
    </center>
</form>
<?php } ?>
</body>
</html>
