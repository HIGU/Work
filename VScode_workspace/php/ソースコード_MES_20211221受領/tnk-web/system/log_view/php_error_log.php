<?php
//////////// php_error ���ǡ����μ��� �ǡ����̤����¤���
$php = '/tmp/php_error';
$phpSave = '/tmp/save_php_error.log';
if (isset($_REQUEST['History']) && file_exists($phpSave)) {
    $php_error_log = `/bin/cat $phpSave`;
    if ($php_error_log == '') {
        $php_error_log = 'History is empty.';
    }
} elseif (isset($_REQUEST['History'])) {
    touch($phpSave);
    $php_error_log = 'There is not history file -> create file';        // �����
} elseif (file_exists($php)) {
    $php_error_log = `/bin/cat $php`;
    if ($php_error_log == '') {
        $php_error_log = 'There is nothing.';
    }
} else {
    // $php_error_log = `/bin/touch /tmp/php_error`;     // �ʤ�������ȤΤʤ��ե��������
    touch($php);                    // �ʤ�������ȤΤʤ��ե��������
    chmod($php, 0600);              // �⡼������
    $php_error_log = 'There is not file -> create file';        // �����
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<style type="text/css">
<!--
pre {
    color:          blue;
    background-color: #c8c8c8;
    font-size:      10pt;
    /* font-weight:    bold; */
    font-family:    monospace;
    /* text-decoration:underline; */
}
-->
</style>
</head>
<body style='margin:0%;'>
<pre>
<?= "$php_error_log \n" ?>
</pre>
</body>
</html>
