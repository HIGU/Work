<?php
//////////////////////////////////////////////////////////////////////////////
// ���������λ��֡�����ν��׎�ʬ�� ��� �Ȳ�    ɽ��            MVC View��  //
// Copyright (C) 2006-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/08/09 Created   acceptance_inspection_analyze_ViewListWin.php       //
//            height='38��'35'(NN7.1�к�), frameborder='0' ���ɲ�           //
// 2006/11/30 Header Footer ξ������Ѥ����ͤ��ѹ�                          //
// 2007/09/05 if ($request->get('targetDateStr') < '20070901')�����ɲ�    //
//////////////////////////////////////////////////////////////////////////////
if (_TNK_DEBUG) access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));
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
<link rel='stylesheet' href='acceptance_inspection_analyze.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<!-- <script type='text/javascript' src='acceptance_inspection_analyze.js?<?php echo $uniq ?>'></script> -->
</head>
<!-- background-color:#d6d3ce; -->
<body style='overflow-y:hidden;'
    onLoad='
        document.getElementById("closeID").focus();
    '
>
<center>
<?php
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/acceptance_inspection_analyze_ViewListHeader-{$_SESSION['User_ID']}.html?{$uniq}' name='header' align='center' width='100%' height='35' title='����'>\n";
echo "    ���ܤ�ɽ�����Ƥ��ޤ���\n";
echo "</iframe>\n";
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/acceptance_inspection_analyze_ViewList-{$_SESSION['User_ID']}.html?{$uniq}' name='list' align='center' width='100%' height='75%' title='����'>\n";
echo "    ������ɽ�����Ƥ��ޤ���\n";
echo "</iframe>\n";
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/acceptance_inspection_analyze_ViewListFooter-{$_SESSION['User_ID']}.html?{$uniq}' name='footer' align='center' width='100%' height='35' title='�եå���'>\n";
echo "    �եå�����ɽ�����Ƥ��ޤ���\n";
echo "</iframe>\n";
if ($request->get('targetDateStr') < '20070901') {
    echo "<div>��ա������Ϲ�θ����Ƥ��ޤ���</div>\n";
}
?>
<div align='center'><input type='button' name='closeButton' id='closeID' value='&nbsp;&nbsp;OK&nbsp;&nbsp;' onClick='window.close();'></div>
</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
