<?php 
//////////////////////////////////////////////////////////////////////////////
// ������Ư���� ��ž����κ����ޥ������ݼ� ��Ͽ����     Client interface �� //
//                PartsEntry.php����ƽ�  �Խ�������    MVC View �� List �� //
// Copyright (C) 2004-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/07/01 Created   MaterialEntryPage.php                               //
// 2006/06/09 access_log() �б�  ob_start()��session_start()�ϸƽи�������  //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
ini_set('error_reporting', E_ALL);          // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����

require_once ('../../../function.php');     // access_log()���ǻ���
access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));
// access_log();                               // Script Name �ϼ�ư����

// �����å�������
if ($Materials['Type'] != 'C') {
    $BAR_CHECKED = ' checked ';
    $CUT_CHECKED = '';
} else {
    $BAR_CHECKED = '';
    $CUT_CHECKED = ' checked ';
}

// ���̥إå��ν���
SetHttpHeader();
?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
<html>
<head>
<?php require_once ('../com/PageHeader.php'); ?>
<SCRIPT language='JavaScript' SRC='<?=SEARCH_JS?>'></SCRIPT>
<Script Language='JavaScript'>
function init() {
<?php if ($Message != '') { ?>
    alert('<?=$Message?>');
<?php } ?>
}
function doSubmit() {
    if (confirm('��Ͽ���ޤ���������Ǥ�����')) {
        document.MainForm.ProcCode.value = 'WRITE';
        document.MainForm.submit();
    }
}
function doDelete() {
    if (confirm('������ޤ���������Ǥ�����')) {
        document.MainForm.ProcCode.value = 'DELETE';
        document.MainForm.submit();
    }
}
<?php if (isset($_REQUEST['RetUrl'])) { ?>
function doBack() {
    location.href = '<?=$_REQUEST['RetUrl']?>';
}
<?php } ?>
</Script>
</head>
<body onLoad='init()'>
<form name='MainForm' action='MaterialsEntry.php' method='post'>
<input type='hidden' name='ProcCode' value=''>
<input type='hidden' name='EDIT_MODE' value='<?=$EDIT_MODE?>'>
<input type='hidden' name='RetUrl' value='<?=outHtml(@$_REQUEST['RetUrl'])?>'>
    <center>
        <table border='1'>
            <!-- ���������� -->
            <tr>
                <td class='HED' style='width:100px;'>
                    ����������
                </td>
                <td>
                <?php if ($EDIT_MODE == 'INSERT') { ?>
                    <input type='text' name='Code' size='7' maxlength='7' class='CODE' value='<?=outHtml($Materials['Code'])?>'>
                <?php } else { ?>
                    <?=outHtml($Materials['Code'])?>
                    <input type='hidden' name='Code' value='<?=outHtml($Materials['Code'])?>'>
                <?php } ?>
                </td>
            </tr>
            <!-- ����̾�� -->
            <tr>
                <td class='HED' style='width:100px;'>
                    ����̾��
                </td>
                <td>
                    <input type='text' name='Name' size='30' maxlength='30' class='TEXT' value='<?=outHtml($Materials['Name'])?>'>
                </td>
            </tr>
            <!-- �С��� or ���Ǻ� -->
            <tr>
                <td class='HED' style='width:100px;'>
                    ������
                </td>
                <td>
                    <input type='radio' name='Type' value='B' ID='TypeB'<?=$BAR_CHECKED?>><Label for='TypeB'>�С���</Label>
                    <input type='radio' name='Type' value='C' ID='TypeC'<?=$CUT_CHECKED?>><Label for='TypeC'>���Ǻ�</Label>
                </td>
            </tr>
            <!-- ��� -->
            <tr>
                <td class='HED' style='width:100px;'>
                    ���ʺ��
                </td>
                <td>
                    <input type='text' name='Style' size='30' maxlength='30' class='TEXT' value='<?=outHtml($Materials['Style'])?>'>
                </td>
            </tr>
            <!-- �н��� -->
            <tr>
                <td class='HED' style='width:100px;'>
                    ñ�̽���
                </td>
                <td>
                    <input type='text' name='Weight' size='7' maxlength='7' class='NUM' value='<?=outHtml(sprintf ('%.04f',$Materials['Weight']))?>'> Kg/m(���Ǻ�ϣ�������νŤ�)
                </td>
            </tr>
            <!-- ɸ��Ĺ�� -->
            <tr>
                <td class='HED' style='width:100px;'>
                    ɸ��Ĺ��
                </td>
                <td>
                    <input type='text' name='Length' size='7' maxlength='7' class='NUM' value='<?=outHtml(sprintf ('%.04f',$Materials['Length']))?>'> ��
                </td>
            </tr>
        </table>
        <br>
        <input type='button' value='�С�Ͽ' style='width:80;' onClick='doSubmit()'>
        <?php if ($EDIT_MODE == 'UPDATE') { ?>
        <input type='button' value='���' style='width:80;' onClick='doDelete()'>
        <?php } ?>
        <?php if (@$_REQUEST['RetUrl'] != '') { ?>
        <input type="button" value="�ᡡ��" style="width:80;" onClick="doBack()">
        <?php } ?>
    </center>
</form>
</body>
</html>
