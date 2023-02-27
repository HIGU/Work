<?php 
//////////////////////////////////////////////////////////////////////////////
// ������Ư���������ƥ�κ����ޥ������ݼ�               Client interface �� //
//                                                  MVC View �� List ��     //
// Copyright (C) 2004-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/07/01 Created   MaterialsSearch.php                                 //
// 2006/04/12 access_log() �б�                                             //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
ini_set('error_reporting', E_ALL);          // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../../function.php');     // access_log()���ǻ���
require_once ('../com/define.php');
require_once ('../com/function.php');
require_once ('../com/PageControl.php');
access_log();                               // Script Name �ϼ�ư����

// �����ԥ⡼��
$AdminUser = AdminUser( FNC_MASTER );

// �ѥ�᡼���γ�Ǽ
setParameter();

if ($Parameter['ProcCode'] != '') {
    
    // ���ͥ������μ���
    $con = getConnection();
    // sql������
    $sql = MakeSql();
    // ��������
    $rs = pg_query ($con , $sql);
    
    /** �ڡ�������ȥ��� */
    $PageCtl['ViewPage']    = $Parameter['ViewPage'];
    $PageCtl['ListNum']     = $Parameter['ListNum'];
    $PageCtl['RowsNum']     = pg_num_rows ($rs);
    $PageCtl['StartRecNum'] = getStartRecNum($PageCtl['ViewPage'],$PageCtl['ListNum']);
    $PageCtl['EndRecNum']   = getEndRecNum($PageCtl['ViewPage'],$PageCtl['ListNum'],$PageCtl['RowsNum']);
}
// --------------------------------------------------
// �ѥ�᡼���γ�Ǽ         
// --------------------------------------------------
function setParameter()
{
    global $Parameter;
    
    $Parameter['ProcCode']       = @$_REQUEST['ProcCode'];
    $Parameter['ViewPage']       = @$_REQUEST['ViewPage'];
    $Parameter['ListNum']        = @$_REQUEST['ListNum'];
    $Parameter['FromCode']       = @$_REQUEST['FromCode'];
    $Parameter['ToCode']         = @$_REQUEST['ToCode'];
    $Parameter['Type']           = @$_REQUEST['Type'];
    
}
// --------------------------------------------------
// ���������ѤΣӣѣ�����
// --------------------------------------------------
function MakeSql()
{
    global $Parameter;

    $FirstParam = true;
    $sql = 'select mtcode,mtname,type,style,weight,length from equip_materials where 0=0 ';
    
    // where������
    if ($Parameter['FromCode'] != '') {
        $sql .= " and mtcode >='".pg_escape_string($Parameter['FromCode'])."'";
    }
    if ($Parameter['ToCode'] != '') {
        $sql .= " and mtcode <='".pg_escape_string($Parameter['ToCode'])."'";
    }
    if ($Parameter['Type'] != 'A') {
        $sql .= " and type ='".pg_escape_string($Parameter['Type'])."'";
    }
    
    $sql .= ' order by mtcode';
    
    return $sql;
}

// ���̥إå��ν���
SetHttpHeader();
?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
<html>
<head>
<?php require_once ('../com/PageHeader.php'); ?>
<script language='JavaScript'>
function doView(code) {
    document.MainForm.ProcCode.value = 'VIEW';
    document.MainForm.EDIT_MODE.value = '';
    document.MainForm.Code.value = code;
    document.MainForm.action = 'MaterialsEntry.php';
    document.MainForm.submit();
}
function doEdit(code) {
    document.MainForm.ProcCode.value = 'EDIT';
    document.MainForm.EDIT_MODE.value = 'UPDATE';
    document.MainForm.Code.value = code;
    document.MainForm.action = 'MaterialsEntry.php';
    document.MainForm.submit();
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
<?php if ($Parameter['ProcCode'] == '') { ?>
<table border='0' class='LAYOUT' width='100%' height='100%'>
    <tr class='LAYOUT'>
        <td class='LAYOUT' align='center'>
            ��о������Ϥ��Ʋ�����
        </td>
    </tr>
</table>
<?php } else if ($PageCtl['RowsNum'] == 0) { ?>
<table border='0' class='LAYOUT' width='100%' height='100%'>
    <tr class='LAYOUT'>
        <td class='LAYOUT' align='center'>
            �����ǡ�����¸�ߤ��ޤ���
        </td>
    </tr>
</table>
<?php } else { ?>
<form name='MovePageForm' action='MaterialsList.php' method='post'>
<input type='hidden' name='ProcCode' value='VIEW'>
<input type='hidden' name='FromCode' value='<?=outHtml($Parameter['FromCode'])?>'>
<input type='hidden' name='ToCode' value='<?=outHtml($Parameter['ToCode'])?>'>
<input type='hidden' name='Type' value='<?=outHtml($Parameter['Type'])?>'>
<input type='hidden' name='ListNum' value='<?=outHtml($Parameter['ListNum'])?>'>
<input type='hidden' name='ViewPage' value=''>
</form>

<form name='MainForm' method='post'>
<input type='hidden' name='RetUrl' value='MaterialsList.php?ProcCode=VIEW&FromCode=<?=outHtml($Parameter['FromCode'])?>&ToCode=<?=outHtml($Parameter['ToCode'])?>&Type=<?=outHtml($Parameter['Type'])?>&ViewPage=<?=$PageCtl['ViewPage']?>&ListNum=<?=$PageCtl['ListNum']?>'>
<input type='hidden' name='ProcCode' value=''>
<input type='hidden' name='EDIT_MODE' value=''>
<input type='hidden' name='Code' value=''>
    <center>
        <table border='1'>
            <tr>
                <td class='HED'>
                </td>
                <td class='HED'>
                    ����������
                </td>
                <td class='HED'>
                    ����̾��
                </td>
                <td class='HED'>
                    ������
                </td>
                <td class='HED'>
                    ���ʺ��
                </td>
                <td class='HED'>
                    ñ�̽���
                </td>
                <td class='HED'>
                    ɸ��Ĺ��
                </td>
            </tr>
<?php
        for ($i=$PageCtl['StartRecNum'];$i<=$PageCtl['EndRecNum'];$i++) {
            $row = pg_fetch_array ($rs,$i); 
?>
            <tr>
                <td>
                    <input type='button' value='ɽ��' onClick="doView('<?=outHtml($row['mtcode'])?>')"><?php if ($AdminUser) { ?><input type='button' value='����' onClick="doEdit('<?=outHtml($row['mtcode'])?>')"><?php } ?>
                <td nowrap>
                    <?=outHtml($row['mtcode'])?>
                </td>
                <td nowrap>
                    <?=outHtml($row['mtname'])?>
                </td>
                <td nowrap>
                    <?php if ($row['type'] == 'B') echo('�С���'); ?>
                    <?php if ($row['type'] == 'C') echo('���Ǻ�'); ?>
                </td>
                <td nowrap>
                    <?=outHtml($row['style'])?>
                </td>
                <td align='right' nowrap>
                    <?=outHtml(sprintf ('%.04f',$row['weight']))?>
                </td>
                <td align='right' nowrap>
                    <?=outHtml(sprintf('%.04f',$row['length']))?>
                </td>
            </tr>
    <?php } ?>
        </table>
        <br>
<?= getPageControlHtml($PageCtl['ViewPage'],$PageCtl['RowsNum'],$PageCtl['ListNum']) ?>
    </center>
</form>
<?php } ?>
</body>
</html>
<?php ob_end_flush(); ?>
