<?php
//////////////////////////////////////////////////////////////////////////////
// ����������׻� View��                                                    //
// Copyright (C) 2007-2011 Norihisa.Ooya usoumu@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2007/12/14 Created  assemblyRate_depreciationCal_View.php                //
// 2011/06/22 format_date�Ϥ�tnk_func�˰�ư�Τ��ᤳ�������               //
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

<style type="text/css">
<!--
th {
    background-color:   blue;
    color:              yellow;
    font-size:          10pt;
    font-weight:        bold;
    font-family:        monospace;
}
-->
</style>
</head>
<body scroll=no>
    <center>
    <?php echo $menu->out_title_border() ?>
    <br>
    <?php
    if ($request->get('view_flg') == '�Ȳ�') {
        echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/assemblyRate_depreciationCal_List-{$_SESSION['User_ID']}.html' name='list' align='center' width='100%' height='90%' title='�ꥹ��'>\n";
        echo "    ������ɽ�����Ƥ��ޤ���\n";
        echo "</iframe>\n";
    }
    ?>
</body>
<?php echo $menu->out_alert_java() ?>
</html>
