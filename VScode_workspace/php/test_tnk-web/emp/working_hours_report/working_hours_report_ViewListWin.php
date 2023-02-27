<?php
//////////////////////////////////////////////////////////////////////////////
// 就業週報の集計 結果 照会    表示                             MVC View部  //
// Copyright (C) 2008 - 2017 Norihisa.Ohya usoumu@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2008/09/22 Created   working_hours_report_ViewListWin.php                //
// 2017/06/02 部課長説明 本格稼動                                           //
// 2017/06/29 エラー箇所等を訂正                                            //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<link rel='stylesheet' href='working_hours_report.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='working_hours_report.js?<?php echo $uniq ?>'></script>
</head>
<!-- background-color:#d6d3ce; -->
<body style='overflow-y:hidden;'
    onLoad='
        WorkingHoursReport.set_focus(document.getElementById("closeID"), "noSelect");
    '
>
<center>
<?php
// echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/working_hours_report_ViewListHeader-{$_SESSION['User_ID']}.html?{$uniq}' name='header' align='center' width='100%' height='60' title='項目'>\n";
// echo "    項目を表示しています。\n";
// echo "</iframe>\n";
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/working_hours_report_ViewList-{$_SESSION['User_ID']}.html?{$uniq}' name='list' align='center' width='100%' height='80%' title='一覧'>\n";
echo "    一覧を表示しています。\n";
echo "</iframe>\n";
// echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/working_hours_report_ViewListFooter-{$_SESSION['User_ID']}.html?{$uniq}' name='footer' align='center' width='100%' height='35' title='フッター'>\n";
// echo "    フッターを表示しています。\n";
// echo "</iframe>\n";
?>
<div align='center'><input type='button' name='closeButton' id='closeID' value='&nbsp;&nbsp;OK&nbsp;&nbsp;' onClick='window.close();'></div>
</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
