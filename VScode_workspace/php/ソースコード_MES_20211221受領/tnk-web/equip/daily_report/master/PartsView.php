<?php
//////////////////////////////////////////////////////////////////////////////
// ������Ư���� ��ž��������ʥޥ������ݼ� �Ȳ����     Client interface �� //
//              PartsEntry.php����ƽ�  ��Ͽ���ƾȲ�    MVC View �� List �� //
// Copyright (C) 2004-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/07/01 Created   PartsView.php                                       //
// 2006/06/09 access_log() �б�  ob_start()��session_start()�ϸƽи�������  //
//            style='width:200px; height:25px;' ���ɲ�                      //
// 2006/06/10 equip_parts�ơ��֥���ѹ����������ֹ�ȵ���̾��ꥹ�Ȥ��ɲ�   //
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
<LINK rel="stylesheet" href="../com/css.css" type="text/css">
<Script Language='JavaScript'>
function init() {
}
function doSubmit() {
    document.MainForm.submit();
}
<?php if (@$_REQUEST['RetUrl'] != '') { ?>
function doBack() {
    location.href = '<?=@$_REQUEST['RetUrl']?>';
}
<?php } ?>
</Script>
</head>
<body>
    <center>
        <table border='1'>
            <!-- �����ֹ� -->
            <tr>
                <td class='HED' style='width:100px;'>
                    �����ֹ�
                </td>
                <td style='width:200px; height:25px;' align='center'>
                    <?=outHtml($Parts['MacNo'])?>
                </td>
            </tr>
            <!-- ����̾ -->
            <tr>
                <td class='HED' style='width:100px;'>
                    ����̾
                </td>
                <td style='width:200px; height:25px;'>
                    <?=outHtml($Parts['MacName'])?>
                </td>
            </tr>
            <!-- �����ֹ� -->
            <tr>
                <td class='HED' style='width:100px;'>
                    �����ֹ�
                </td>
                <td style='width:200px; height:25px;' align='center'>
                    <?=outHtml($Parts['Code'])?>
                </td>
            </tr>
            <!-- ����̾�� -->
            <tr>
                <td class='HED' style='width:100px;'>
                    ����̾��
                </td>
                <td style='width:220px; height:25px;'>
                    <?=outHtml($Parts['Name'])?>
                </td>
            </tr>
            <!-- ��� -->
            <tr>
                <td class='HED' style='width:100px;'>
                    ���ʺ��
                </td>
                <td style='width:200px; height:25px;' align='center'>
                    <?=outHtml($Parts['Zai'])?>
                </td>
            </tr>
            <!-- ��ˡ -->
            <tr>
                <td class='HED' style='width:100px;'>
                    ��ˡ
                </td>
                <td align='right' style='width:200px; height:25px;'>
                    <?=outHtml($Parts['Size'])?> mm
                </td>
            </tr>
            <!-- ���Ѥ������ -->
            <tr>
                <td class='HED' style='width:100px;'>
                    ���Ѻ���
                </td>
                <td style='width:200px; height:25px;' align='center'>
                    <?=outHtml($Parts['UseItem'])?>
                </td>
            </tr>
            <!-- ����̾�� -->
            <tr>
                <td class='HED' style='width:100px;'>
                    �˺ॵ����
                </td>
                <td align='right' style='width:200px; height:25px;'>
                    <?=outHtml($Parts['Abandonment'])?> mm
                </td>
            </tr>
        </table>
        <br>
        <?php if (@$_REQUEST['RetUrl'] != '') { ?>
        <input type="button" value="�ᡡ��" style="width:80;" onClick="doBack()">
        <?php } ?>
        <?php if ($Message != '') { ?>
            <br><br><br><font color='#ff0000'><b><?=$Message?></b></font><br>
        <?php } ?>
        
    </center>
</form>
</body>
</html>
