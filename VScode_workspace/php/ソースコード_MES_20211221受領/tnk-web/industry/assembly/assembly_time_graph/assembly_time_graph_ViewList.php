<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ω�Υ饤���̹��� �Ƽ殺���    ���٥ꥹ��ɽ��(Ajax)        MVC View��  //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/05/16 Created   assembly_time_graph_ViewList.php                    //
//            height='38��'35'(NN7.1�к�), frameborder='0' ���ɲ�           //
// 2006/06/15 ���٤������٤ȹ������٤˥��å���ʬ����(List��DetaileList) //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $request->get('targetDateList') . '�Υ���� �ǡ��� ���� ɽ��' ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<link rel='stylesheet' href='assembly_time_graph.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='assembly_time_graph.js?<?php echo $uniq ?>'></script>
</head>
<!-- background-color:#d6d3ce; -->
<body style='overflow-y:hidden;'
    onLoad='
        AssemblyTimeGraph.set_focus(document.getElementById("closeID"), "noSelect");
    '
>
<center>
<?php
if ($request->get('showMenu') == 'List') {
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='assembly_time_graph_ViewHeader.html?{$uniq}' name='header' align='center' width='100%' height='33' title='����'>\n";
} else {
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='assembly_time_graph_ViewDetaileHeader.html?{$uniq}' name='header' align='center' width='100%' height='50' title='����'>\n";
}
echo "    ɽ�ι��ܤ�ɽ�����Ƥ��ޤ���\n";
echo "</iframe>\n";
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/assembly_time_graph_ViewList-{$_SESSION['User_ID']}.html?{$uniq}' name='list' align='center' width='100%' height='80%' title='�饤���̹�������'>\n";
echo "    �饤���̹���������ɽ�����Ƥ��ޤ���\n";
echo "</iframe>\n";
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/assembly_time_graph_ViewListFooter-{$_SESSION['User_ID']}.html?{$uniq}' name='footer' align='center' width='100%' height='35' title='���׹���'>\n";
echo "    �եå���(���׹���)��ɽ�����Ƥ��ޤ���\n";
echo "</iframe>\n";
?>
<div align='center'>
    <input type='button' name='closeButton' id='closeID' value='&nbsp;&nbsp;OK&nbsp;&nbsp;' onClick='window.close();'>
    <?php if ($request->get('showMenu') == 'List') {?>
    &nbsp;
    <input type='button' name='detaileList' value='��������' onClick='AssemblyTimeGraph.win_open("<?php echo $menu->out_self()?>?showMenu=DetaileList&targetDateList=<?php echo $request->get('targetDateList')?>&noMenu=yes", 950, 600)'>
    <?php } ?>
</div>
</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
