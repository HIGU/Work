<?php
//////////////////////////////////////////////////////////////////////////////
// ���ѥ��᡼��(�̿�)ɽ���� �ȣԣͣ����� Window Active Check �б�           //
// Copyright (C) 2004-2004 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// HTML��TITLE(�����ȥ�)̾���ѹ����ƻ��Ѥ���                                //
// Changed history                                                          //
// 2004/09/30 Created  photo_view.php                                       //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../../function.php');        // define.php �� pgsql.php �� require_once ���Ƥ���
access_log();                               // Script Name �ϼ�ư����

///// GET/POST�Υ����å�&����
if (isset($_REQUEST['img_src'])) {
    $img_src = $_REQUEST['img_src'];
} else {
    $img_src = '';
}
if (isset($_REQUEST['img_alt'])) {
    $img_alt = $_REQUEST['img_alt'];
} else {
    $img_alt = '';
}

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");               // ���դ����
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");  // ��˽�������Ƥ���
header("Cache-Control: no-store, no-cache, must-revalidate");   // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");                                     // HTTP/1.0
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title>�����̿�����ɽ��</title>
<script language='JavaScript'>
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
}
</script>
</head>
<body style='margin:0%;' onLoad="setInterval('winActiveChk()',100)">
    <center>
        <input type='image' src='<?=$img_src?>' alt='<?=$img_alt?>' onClick='window.close()'>
    </center>
</body>
</html>
<?php ob_end_flush(); // ���ϥХåե���gzip���� END ?>
