<?php
//////////////////////////////////////////////////////////////////////////////
// php apache のlog及びエラーログ表示・クリア ifram ファイル                //
// Copyright(C) 2004-2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp  //
// Changed history                                                          //
// 2004/04/23 Created  apache_log.php                                       //
// 2005/12/10 apacheの access_log を ローテートする事によるファイル名変更   //
// 2007/08/08 tac /tmp/access_log.tmp を追加して逆順表示とリロード追加      //
//////////////////////////////////////////////////////////////////////////////
//////////// apache access_log データの取得 データ量に制限あり
// $access = '/usr/local/apache2/logs/access_log';
$access = '/tmp/access_log.' . mktime(9, 0, 0); // UNIXタイムスタンプ(UTC)の0時0分0秒を付加
if (file_exists($access)) {
    $apache_access_log = `/usr/bin/tail -200 $access > /tmp/access_log.tmp`;
    $apache_access_log = `/usr/bin/tac /tmp/access_log.tmp`;
    if ($apache_access_log == '') {
        $apache_access_log = 'There is nothing.';
    }
} else {
    // apacheはリード権限しかないので何もせずに初期化だけ
    $apache_access_log = 'There is not file.';      // 初期化
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<!-- <meta http-equiv="Refresh" content="5;"> -->
<style type='text/css'>
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
<?= "$apache_access_log \n" ?>
</pre>
</body>
</html>
