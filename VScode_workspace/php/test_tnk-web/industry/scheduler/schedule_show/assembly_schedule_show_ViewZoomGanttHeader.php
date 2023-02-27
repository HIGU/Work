<?php
//////////////////////////////////////////////////////////////////////////////
// 組立日程計画表(AS/400版)スケジュール照会 ガントズーム見出し MVC View 部  //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/06/16 Created   assembly_schedule_show_ViewZoomGanttHeader.php      //
// 2006/11/09 zoomGanttReload()と同期を取って30秒でsetInterval(30000)させる //
//////////////////////////////////////////////////////////////////////////////
session_start();                            // ini_set()の次に指定すること Script 最上行
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title>ガントズーム見出し</title>
<link rel='stylesheet' href='assembly_schedule_show.css' type='text/css' media='screen'>
<style type='text/css'>
body {
    margin:        0%;
    background-image:none;
}
form {
    margin:        0%;
}
</style>
<!-- <script type='text/javascript' src='assembly_schedule_show.js'></script> -->
</head>
<body
    onLoad='
        setInterval("document.getElementById(\"zoomHeader\").setAttribute(\"src\", \"<?php echo 'zoom/AssemblyScheduleZoomGanttHeader-' . $_SESSION['User_ID'] . '.png'?>\")", 30000);
    '
>
<center>
    <img id='zoomHeader' src='<?php echo 'zoom/AssemblyScheduleZoomGanttHeader-' . $_SESSION['User_ID'] . '.png'?>' alt='スケジュール見出し' border='0'>
</center>
</body>
</html>
