<?php
//////////////////////////////////////////////////////////////////////////////
// Ǽ��ͽ�ꥰ��ա������ų����٤ξȲ�(�����λŻ����İ�)  �إ��ɽ��         //
// Copyright (C) 2004-2004 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/11/01 Created  order_help.php                                       //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../../function.php');        // TNK ������ function
access_log();                               // Script Name �ϼ�ư����
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title>�����ų� �Ȳ���̣ȣţ̣�</title>
<script language='JavaScript'>
<!--
function winActiveChk() {
    if (document.all) {     // IE�ʤ�
        if (document.hasFocus() == false) {     // IE5.5�ʾ�ǻȤ���
            window.focus();
            return;
        }
        return;
    } else {                // NN �ʤ�ȥ�ꥭ�å�
        window.focus();
        return;
    }
    // <input type='button' value='TEST' onClick="window.opener.location.reload()">
    // parent.Header.�ؿ�̾() or ���֥�������;
}
// -->
</script>
</head>
<body style='margin:0%;' onLoad="setInterval('winActiveChk()',100)">
    <center>
        <?php
        if ($_SESSION['select'] == 'miken') {
            echo "        <input type='image' alt='�����ų� �Ȳ���̣ȣţ̣�' border='0' src='order_help.png' onClick='window.close()'>\n";
        } else {
            echo "        <input type='image' alt='Ǽ��ͽ�ꥰ��դβ��̣ȣţ̣�' border='0' src='order_graph_help.png' onClick='window.close()'>\n";
        }
        ?>
    </center>
</body>
</html>
<?php ob_end_flush(); // ���ϥХåե���gzip���� END ?>
