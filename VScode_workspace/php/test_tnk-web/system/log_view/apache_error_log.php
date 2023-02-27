<?php
//////////// apache error_log データの取得 データ量に制限あり
$apache = '/usr/local/apache2/logs/error_log';
$apacheSave = '/tmp/save_apache_error.log';
if (isset($_REQUEST['History']) && file_exists($apacheSave)) {
    $apache_error_log = `/bin/cat $apacheSave`;
    if ($apache_error_log == '') {
        $apache_error_log = 'History is empty.';
    }
} elseif (isset($_REQUEST['History'])) {
    touch($apacheSave);
    $apache_error_log = 'There is not history file -> create file'; // 初期化
} elseif (file_exists($apache)) {
    $apache_error_log = `/bin/cat $apache`;
    if ($apache_error_log == '') {
        $apache_error_log = 'There is nothing.';
    }
} else {
    // apacheはリード権限しかないので何もせずに初期化だけ
    $apache_error_log = 'There is not file.';       // 初期化
}

?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
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
