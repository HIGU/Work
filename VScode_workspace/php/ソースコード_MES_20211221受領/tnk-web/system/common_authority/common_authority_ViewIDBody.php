<?php
//////////////////////////////////////////////////////////////////////////////
// 共通 権限 関係テーブル メンテナンス  IDのボディ              MVC View 部 //
//   使用DB = common_authority, common_auth_master, common_auth_category... //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/07/25 Created   common_authority_ViewIDBody.php                     //
//////////////////////////////////////////////////////////////////////////////
access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=EUC-JP'>
<meta http-equiv='Content-Style-Type' content='text/css'>
<meta http-equiv='Content-Script-Type' content='text/javascript'>
<title>権限メンバーの本文</title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<link rel='stylesheet' href='common_authority.css' type='text/css' media='screen'>
<script type='text/javascript' src='common_authority.js'></script>
</head>
<body style='background-image:none; background-color:#cecece;'
    onLoad='
        autoLoadScript();
        parent.CommonAuthority.setEventListeners("submit", "addIDForm");
        parent.CommonAuthority.setEventListeners("click", "addID");
        parent.CommonAuthority.setEventListeners("blur", "targetID");
        // parent.CommonAuthority.setEventListeners("change", "targetCategory");
        // CommonAuthority.set_focus(document.ConditionForm.targetName, "noSelect");
    '
>
<center>
    <table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>
        <tr><td> <!----------- ダミー(デザイン用) ------------>
    <table class='winbox_field list' width='100%' border='1' cellspacing='0' cellpadding='3'>
        <?php if ($rows <= 0) { ?>
        <tr>
            <td class='winbox center' width='100%'>データがありません。</td>
        </tr>
        <?php } else { ?>
            <?php for ($i=0; $i<$rows; $i++) { ?>
        <tr>
            <td class='winbox right'  width='10%'><?php echo ($i + 1) ?></td>
            <td class='winbox left'   width='30%'><?php echo $res[$i][0] ?></td>
            <td class='winbox left'   width='20%'><?php echo $res[$i][2] ?></td>
            <td class='winbox left'   width='30%'><?php echo $res[$i][1] ?></td>
            <td class='winbox center' width='10%'>
                <input type='button' name='IDDelete' value='削除' class='delButton'
                    onClick='parent.CommonAuthority.deleteID("<?php echo $res[$i][0] ?>", <?php echo $res[$i][4] ?>);'
                >
            </td>
        </tr>
            <?php } ?>
        <?php } ?>
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
