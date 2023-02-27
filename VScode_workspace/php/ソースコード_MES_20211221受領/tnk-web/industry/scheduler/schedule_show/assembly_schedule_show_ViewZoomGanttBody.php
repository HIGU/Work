<?php
//////////////////////////////////////////////////////////////////////////////
// 組立日程計画表(AS/400版)スケジュール照会 ガントズーム本文   MVC View 部  //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/06/16 Created   assembly_schedule_show_ViewZoomGanttBody.php        //
// 2006/11/09 zoomGanttReload()と同期を取って30秒でsetInterval(30000)させる //
//////////////////////////////////////////////////////////////////////////////
session_start();                            // ini_set()の次に指定すること Script 最上行
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title>ガントズーム本文</title>
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
        setInterval("document.getElementById(\"zoomBody\").setAttribute(\"src\", \"<?php echo 'zoom/AssemblyScheduleZoomGanttBody-' . $_SESSION['User_ID'] . '.png'?>\")", 30000);
    '
>
<center>
    <input id='zoomBody' type='image' src='<?php echo 'zoom/AssemblyScheduleZoomGanttBody-' . $_SESSION['User_ID'] . '.png'?>' alt='組立計画スケジュール' border='0'>
</center>
</body>
</html>
