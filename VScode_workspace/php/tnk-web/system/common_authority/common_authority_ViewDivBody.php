<?php
//////////////////////////////////////////////////////////////////////////////
// 共通 権限 関係テーブル メンテナンス  Divisionのボディ        MVC View 部 //
//   使用DB = common_authority, common_auth_master, common_auth_category... //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/07/25 Created   common_authority_ViewDivBody.php                    //
// 2006/09/06 権限名の修正機能追加に伴い 修正・取消部のデザイン追加変更     //
//////////////////////////////////////////////////////////////////////////////
access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
<meta http-equiv='Content-Style-Type' content='text/css'>
<meta http-equiv='Content-Script-Type' content='text/javascript'>
<title>権限マスターの本文</title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<link rel='stylesheet' href='common_authority.css' type='text/css' media='screen'>
<script type='text/javascript' src='common_authority.js'></script>
</head>
<body style='background-image:none; background-color:#cecece;'
    onLoad='
        autoLoadScript();
        parent.CommonAuthority.setEventListeners("submit", "addDivisionForm");
        parent.CommonAuthority.setEventListeners("click", "addDivision");
        <?php if ($request->get('targetEditDiv') != '') { ?>
        CommonAuthority.set_focus(document.getElementById("editDivText"), "select");
        <?php } ?>
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
        <tr onMouseOver='this.className="mouseOver";' onMouseOut='this.className="";' onClick='parent.CommonAuthority.listID(<?php echo $res[$i][0] ?>);'>
            <td class='winbox right'  width=' 9%'><?php echo $res[$i][0] ?></td>
            <?php if ($request->get('targetEditDiv') == $res[$i][0]) { ?>
            <td class='winbox left'   width='73%'>
                <input type='text' id='editDivText' size='72' maxlength='100' value='<?php echo $res[$i][1] ?>'>
                <input type='button' name='DivUpCancel' value='取消' class='cancelButton'
                    onClick='parent.CommonAuthority.AjaxLoadUrl("<?php echo $menu->out_self(), '?Action=ListDivision&showMenu=ListDivision'?>", "showAjax1");'
            </td>
            <td class='winbox center' width=' 9%'>
                <input type='button' name='DivUpdate' value='登録' class='updateButton'
                    onClick='parent.CommonAuthority.updateDivision(<?php echo $res[$i][0] ?>, document.getElementById("editDivText").value);'
                >
            </td>
            <?php } else { ?>
            <td class='winbox left'   width='73%'><?php echo $res[$i][1] ?></td>
            <td class='winbox center' width=' 9%'>
                <input type='button' name='DivEdit' value='修正' class='editButton'
                    onClick='parent.CommonAuthority.editDivision(<?php echo $res[$i][0] ?>, "<?php echo $res[$i][1] ?>");'
                >
            </td>
            <?php } ?>
            <td class='winbox center' width=' 9%'>
                <input type='button' name='DivDelete' value='削除' class='delButton'
                    onClick='parent.CommonAuthority.deleteDivision(<?php echo $res[$i][0] ?>, "<?php echo $res[$i][1] ?>");'
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
