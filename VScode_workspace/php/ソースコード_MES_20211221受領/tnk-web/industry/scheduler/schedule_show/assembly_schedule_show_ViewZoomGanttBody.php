<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ω�����ײ�ɽ(AS/400��)�������塼��Ȳ� ����ȥ�������ʸ   MVC View ��  //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/06/16 Created   assembly_schedule_show_ViewZoomGanttBody.php        //
// 2006/11/09 zoomGanttReload()��Ʊ�����ä�30�ä�setInterval(30000)������ //
//////////////////////////////////////////////////////////////////////////////
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title>����ȥ�������ʸ</title>
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
    <input id='zoomBody' type='image' src='<?php echo 'zoom/AssemblyScheduleZoomGanttBody-' . $_SESSION['User_ID'] . '.png'?>' alt='��Ω�ײ襹�����塼��' border='0'>
</center>
</body>
</html>
