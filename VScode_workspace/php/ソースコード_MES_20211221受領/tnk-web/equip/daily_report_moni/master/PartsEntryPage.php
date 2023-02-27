<?php
//////////////////////////////////////////////////////////////////////////////
// ������Ư���� ��ž��������ʥޥ������ݼ� ��Ͽ����     Client interface �� //
//                PartsEntry.php����ƽ�  �Խ�������    MVC View �� List �� //
// Copyright (C) 2004-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/07/01 Created   PartsEntryPage.php                                  //
// 2006/06/09 access_log() �б�  ob_start()��session_start()�ϸƽи�������  //
// 2006/06/12 �����ֹ桦����̾���ɲ� doMacMasterCheck()���ɲ�               //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
ini_set('error_reporting', E_ALL);          // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����

require_once ('../../../function.php');     // access_log()���ǻ���
access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));
// access_log();                               // Script Name �ϼ�ư����

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
function doMasterCheck() {
    document.MainForm.ProcCode.value = 'CHECK_MASTER';
    document.MainForm.submit();
}
function doMacMasterCheck() {
    document.MainForm.ProcCode.value = 'CHECK_MAC_MASTER';
    document.MainForm.submit();
}
<?php if (isset($_REQUEST['RetUrl'])) { ?>
function doBack() {
    location.href = '<?=$_REQUEST['RetUrl']?>';
}
<?php } ?>
</Script>
</head>
<body onLoad='init()'>
<form name='MainForm' action='PartsEntry.php' method='post'>
<input type='hidden' name='ProcCode' value=''>
<input type='hidden' name='EDIT_MODE' value='<?=$EDIT_MODE?>'>
<input type='hidden' name='CheckMaster' value='<?=$CheckMaster?>'>
<input type='hidden' name='RetUrl' value='<?=outHtml(@$_REQUEST['RetUrl'])?>'>
    <center>
        <table border='1'>
            <!-- �����ֹ� -->
            <tr>
                <td class='HED' style='width:100px;'>
                    �����ֹ�
                </td>
                <td align='center'>
                    <?php if ($EDIT_MODE == 'INSERT') { ?>
                    <select name='MacNo' class='pt14b' onChange='doMacMasterCheck()'>
                        <?php echo getMacNoSelectData($Parts['MacNo']) ?>
                    </select>
                    <?php } else { ?>
                        <?=outHtml($Parts['MacNo'])?>
                        <input type='hidden' name='MacNo' value='<?=outHtml($Parts['MacNo'])?>'>
                    <?php } ?>
                </td>
            </tr>
            <!-- ����̾ -->
            <tr>
                <td class='HED' style='width:100px;'>
                    ����̾
                </td>
                <td>
                    <?=outHtml($Parts['MacName'])?>
                </td>
            </tr>
            <!-- �����ֹ� -->
            <tr>
                <td class='HED' style='width:100px;'>
                    �����ֹ�
                </td>
                <td align='center'>
                    <?php if ($EDIT_MODE == 'INSERT') { ?>
                    <input type='button' value='����' onClick='SearchItem(Code,Name,Zai)'>
                    <input type='text' name='Code' size='9' class='CODE' value='<?=outHtml($Parts['Code'])?>'>
                    <input type='button' value='��' onClick='doMasterCheck()'>
                    <?php } else { ?>
                        <?=outHtml($Parts['Code'])?>
                        <input type='hidden' name='Code' value='<?=outHtml($Parts['Code'])?>'>
                    <?php } ?>
                </td>
            </tr>
            <!-- ����̾�� -->
            <tr>
                <td class='HED' style='width:100px;'>
                    ����̾��
                </td>
                <td>
                    <input type='text' name='Name' size='30' class='READONLY' value='<?=outHtml($Parts['Name'])?>' readonly>
                </td>
            </tr>
            <!-- ��� -->
            <tr>
                <td class='HED' style='width:100px;'>
                    ���ʺ��
                </td>
                <td align='center'>
                    <input type='text' name='Zai' size='30' class='READONLY' value='<?=outHtml($Parts['Zai'])?>' readonly>
                </td>
            </tr>
            <!-- ��ˡ -->
            <tr>
                <td class='HED' style='width:100px;'>
                    ��ˡ
                </td>
                <td>
                    <input type='text' name='Size' size='8' class='NUM' value='<?=outHtml($Parts['Size'])?>'> mm
                </td>
            </tr>
            <!-- ���Ѥ������ -->
            <tr>
                <td class='HED' style='width:100px;'>
                    ���Ѻ���
                </td>
                <td>
                    <?php if ($CheckMaster == false) { ?>
                        <?php // �쥿���פ�false�ξ��Τ�����ܥ����ɽ�������Ƥ��� ?>
                    <?php } ?>
                    <input type='button' value='����' onClick='SearchMaterials(UseItem)'>
                    <input type='text' name='UseItem' size='7' CLASS='READONLY' value='<?=outHtml($Parts['UseItem'])?>' readonly>
                </td>
            </tr>
            <!-- ����̾�� -->
            <tr>
                <td class='HED' style='width:100px;'>
                    �˺ॵ����
                </td>
                <td>
                    <input type='text' name='Abandonment' size='9' class='NUM'value='<?=outHtml($Parts['Abandonment'])?>'> mm
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
