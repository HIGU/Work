<?php
//////////////////////////////////////////////////////////////////////////////
// ������ ��¤����ڤ��δ���ξȲ�     Windowɽ��               MVC View��  //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/10/13 Created   act_summary_ViewListWin.php                         //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<link rel='stylesheet' href='act_summary.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='act_summary.js?<?php echo $uniq ?>'></script>
</head>
<!-- background-color:#d6d3ce; -->
<body style='overflow-y:hidden;'
    onLoad='
        ActSummary.set_focus(document.getElementById("closeID"), "noSelect");
    '
>
<center>
    <div class='pt12b'><?php echo $model->getTitleDateValues($session)?></div>
<?php
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='act_summary_ViewListHeader.html?{$uniq}' name='header' align='center' width='100%' height='35' title='�إå���'>\n";
echo "    ���ܤ�ɽ�����Ƥ��ޤ���\n";
echo "</iframe>\n";
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/act_summary_ViewList-{$_SESSION['User_ID']}.html?{$uniq}' name='list' align='center' width='100%' height='85%' title='�ܥǥ���'>\n";
echo "    ������ɽ�����Ƥ��ޤ���\n";
echo "</iframe>\n";
// echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/act_summary_ViewListFooter-{$_SESSION['User_ID']}.html?{$uniq}' name='footer' align='center' width='100%' height='35' title='�եå���'>\n";
// echo "    �եå�����ɽ�����Ƥ��ޤ���\n";
// echo "</iframe>\n";
?>
<div align='center'><input type='button' name='closeButton' id='closeID' value='&nbsp;&nbsp;OK&nbsp;&nbsp;' onClick='window.close();'></div>
</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
