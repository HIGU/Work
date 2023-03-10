<?php
//////////////////////////////////////////////////////////////////////////////
// 共通 権限 関係テーブル メンテナンス  Divisionのヘッダー      MVC View 部 //
//   使用DB = common_authority, common_auth_master, common_auth_category... //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/07/25 Created   common_authority_ViewDivHeader.php                  //
// 2006/09/06 権限名の修正機能追加に伴い 修正部のデザイン追加変更           //
//////////////////////////////////////////////////////////////////////////////
if (_TNK_DEBUG) access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=EUC-JP'>
<meta http-equiv='Content-Style-Type' content='text/css'>
<meta http-equiv='Content-Script-Type' content='text/javascript'>
<title>権限マスターの項目</title>
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
        <tr><td> <!----------- ダミー(デザイン用) ------------>
    <table class='winbox_field list' width='100%' border='1' cellspacing='0' cellpadding='3'>
        <tr>
            <th class='winbox' width=' 9%'>権限No.</th>
            <th class='winbox' width='73%'>権限名</th>
            <th class='winbox' width=' 9%'>修正</th>
            <th class='winbox' width=' 9%'>削除</th>
        </tr>
    </table>
        </td></tr>
    </table> <!----------------- ダミーEnd ------------------>
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
