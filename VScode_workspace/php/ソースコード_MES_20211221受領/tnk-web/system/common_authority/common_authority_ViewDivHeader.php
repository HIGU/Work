<?php
//////////////////////////////////////////////////////////////////////////////
// ���� ���� �ط��ơ��֥� ���ƥʥ�  Division�Υإå���      MVC View �� //
//   ����DB = common_authority, common_auth_master, common_auth_category... //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/07/25 Created   common_authority_ViewDivHeader.php                  //
// 2006/09/06 ����̾�ν�����ǽ�ɲä�ȼ�� �������Υǥ������ɲ��ѹ�           //
//////////////////////////////////////////////////////////////////////////////
if (_TNK_DEBUG) access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=EUC-JP'>
<meta http-equiv='Content-Style-Type' content='text/css'>
<meta http-equiv='Content-Script-Type' content='text/javascript'>
<title>���¥ޥ������ι���</title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<link rel='stylesheet' href='common_authority.css' type='text/css' media='screen'>
<script type='text/javascript' src='common_authority.js'></script>
</head>
<body style='background-image:none;'
    onLoad='
        autoLoadScript();
        // CommonAuthority.checkANDexecute(document.ConditionForm, 1);
        // CommonAuthority.set_focus(document.ConditionForm.targetName, "noSelect");
    '
>
<center>
    <table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>
        <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
    <table class='winbox_field list' width='100%' border='1' cellspacing='0' cellpadding='3'>
        <tr>
            <th class='winbox' width=' 9%'>����No.</th>
            <th class='winbox' width='73%'>����̾</th>
            <th class='winbox' width=' 9%'>����</th>
            <th class='winbox' width=' 9%'>���</th>
        </tr>
    </table>
        </td></tr>
    </table> <!----------------- ���ߡ�End ------------------>
</center>
</body>
<?php echo $menu->out_alert_java(false)?>
<script type='text/javascript'>
function autoLoadScript()
{
    <?php echo $result->get('autoLoadScript')?>
}
</script>
</html>
