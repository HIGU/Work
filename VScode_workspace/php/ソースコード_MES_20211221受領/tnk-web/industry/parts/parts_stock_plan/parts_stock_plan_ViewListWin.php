<?php
//////////////////////////////////////////////////////////////////////////////
// ���� �߸� ͽ�� �Ȳ� (������ȯ������Ȳ�)  ɽ��                MVC View��  //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/05/24 Created   parts_stock_plan_ViewListWin.php                    //
//            height='38��'35'(NN7.1�к�), frameborder='0' ���ɲ�           //
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
<link rel='stylesheet' href='parts_stock_plan.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='parts_stock_plan.js?<?php echo $uniq ?>'></script>
</head>
<!-- background-color:#d6d3ce; -->
<body style='overflow-y:hidden;'
    onLoad='
        PartsStockPlan.set_focus(document.getElementById("closeID"), "noSelect");
    '
>
<center>
<?php
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/parts_stock_plan_ViewListHeader-{$_SESSION['User_ID']}.html?{$uniq}' name='header' align='center' width='100%' height='60' title='����'>\n";
echo "    ���ܤ�ɽ�����Ƥ��ޤ���\n";
echo "</iframe>\n";
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/parts_stock_plan_ViewList-{$_SESSION['User_ID']}.html?{$uniq}' name='list' align='center' width='100%' height='83%' title='����'>\n";
echo "    ������ɽ�����Ƥ��ޤ���\n";
echo "</iframe>\n";
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/parts_stock_plan_ViewListFooter-{$_SESSION['User_ID']}.html?{$uniq}' name='footer' align='center' width='100%' height='35' title='�եå���'>\n";
echo "    �եå�����ɽ�����Ƥ��ޤ���\n";
echo "</iframe>\n";
?>
<div align='center'><input type='button' name='closeButton' id='closeID' value='&nbsp;&nbsp;OK&nbsp;&nbsp;' onClick='window.close();'></div>
</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
