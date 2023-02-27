<?php
//////////////////////////////////////////////////////////////////////////////
// ������Ư���� ��ž����κ����ޥ������ݼ� �Ȳ����     Client interface �� //
//           MaterialEntry.php����ƽ�  ��Ͽ���ƾȲ�    MVC View �� List �� //
// Copyright (C) 2004-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/07/01 Created   MaterialView.php                                    //
// 2006/06/09 access_log() �б�  ob_start()��session_start()�ϸƽи�������  //
//            style='width:200px; height:25px;' ���ɲ�                      //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
ini_set('error_reporting', E_ALL);          // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����

require_once ('../../../function.php');     // access_log()���ǻ���
access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));
// access_log();                               // Script Name �ϼ�ư����

// �����ɤ���̾�Τ��Ѵ�
if ($Materials['Type'] == 'B')  $TYPE = '�С���';
else                            $TYPE = '���Ǻ�';

SetHttpHeader();
?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
<html>
<head>
<?php require_once ('../com/PageHeader.php'); ?>
<LINK rel="stylesheet" href="../com/css.css" type="text/css">
<Script Language='JavaScript'>
function init() {
}
function doSubmit() {
    document.MainForm.submit();
}
<?php if ($_REQUEST['RetUrl'] != '') { ?>
function doBack() {
    location.href = '<?=$_REQUEST['RetUrl']?>';
}
<?php } ?>
</Script>
</head>
<body>
    <center>
        <table border="1">
            <!-- ���������� -->
            <tr>
                <td class="HED" style="width:100px;">
                    ����������
                </td>
                <td style='width:200px; height:25px;'>
                    <?=outHtml($Materials['Code'])?>
                </td>
            </tr>
            <!-- ����̾�� -->
            <tr>
                <td class="HED" style="width:100px;">
                    ����̾��
                </td>
                <td style='width:200px; height:25px;'>
                    <?=outHtml($Materials['Name'])?>
                </td>
            </tr>
            <!-- �С��� or ���Ǻ� -->
            <tr>
                <td class="HED" style="width:100px;">
                    ������
                </td>
                <td style='width:200px; height:25px;'>
                    <?=outHtml($TYPE)?>
                </td>
            </tr>
            <!-- ��� -->
            <tr>
                <td class="HED" style="width:100px;">
                    ���ʺ��
                </td>
                <td style='width:200px; height:25px;'>
                    <?=outHtml($Materials['Style'])?>
                </td>
            </tr>
            <!-- �н��� -->
            <tr>
                <td class="HED" style="width:100px;">
                    ñ�̽���
                </td>
                <td align='right' style='width:200px; height:25px;'>
                    <?=outHtml(sprintf ('%.04f',$Materials['Weight']))?> Kg/m
                </td>
            </tr>
            <!-- ɸ��Ĺ�� -->
            <tr>
                <td class="HED" style="width:100px;">
                    ɸ��Ĺ��
                </td>
                <td align='right' style='width:200px; height:25px;'>
                    <?=outHtml(sprintf ('%.04f',$Materials['Length']))?> ��
                </td>
            </tr>
        </table>
        <br>
        <?php if ($_REQUEST['RetUrl'] != '') { ?>
        <input type="button" value="�ᡡ��" style="width:80;" onClick="doBack()">
        <?php } ?>
        <?php if ($Message != '') { ?>
            <br><br><br><font color='#ff0000'><b><?=$Message?></b></font><br>
        <?php } ?>
        
    </center>
</form>
</body>
</html>
