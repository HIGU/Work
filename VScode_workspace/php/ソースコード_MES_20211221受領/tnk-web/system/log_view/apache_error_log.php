<?php
//////////// apache error_log �ǡ����μ��� �ǡ����̤����¤���
$apache = '/usr/local/apache2/logs/error_log';
$apacheSave = '/tmp/save_apache_error.log';
if (isset($_REQUEST['History']) && file_exists($apacheSave)) {
    $apache_error_log = `/bin/cat $apacheSave`;
    if ($apache_error_log == '') {
        $apache_error_log = 'History is empty.';
    }
} elseif (isset($_REQUEST['History'])) {
    touch($apacheSave);
    $apache_error_log = 'There is not history file -> create file'; // �����
} elseif (file_exists($apache)) {
    $apache_error_log = `/bin/cat $apache`;
    if ($apache_error_log == '') {
        $apache_error_log = 'There is nothing.';
    }
} else {
    // apache�ϥ꡼�ɸ��¤����ʤ��Τǲ��⤻���˽��������
    $apache_error_log = 'There is not file.';       // �����
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
<?= "$apache_error_log \n" ?>
</pre>
</body>
</html>
