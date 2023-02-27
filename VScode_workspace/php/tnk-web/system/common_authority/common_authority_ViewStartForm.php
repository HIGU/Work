<?php
//////////////////////////////////////////////////////////////////////////////
// 共通 権限 関係テーブル メンテナンス                          MVC View 部 //
//   使用DB = common_authority, common_auth_master, common_auth_category... //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/07/20 Created   common_authority_ViewStartForm.php                  //
//////////////////////////////////////////////////////////////////////////////
if (_TNK_DEBUG) access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
<meta http-equiv='Content-Style-Type' content='text/css'>
<meta http-equiv='Content-Script-Type' content='text/javascript'>
<!-- <meta http-equiv='Refresh' content='15;URL=<?php echo $menu->out_self(), "?Action={$request->get('Action')}&showMenu={$request->get('showMenu')}"?>'> -->
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<link rel='stylesheet' href='common_authority.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<link rel='shortcut icon' href='/favicon.ico?=<?php echo $uniq ?>'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='common_authority.js?<?php echo $uniq ?>'></script>
</head>
<body style='overflow:hidden;'
    onLoad='
        CommonAuthority.AjaxLoadUrl("<?php echo $menu->out_self(), '?Action=ListDivision&showMenu=ListDivision'?>", "showAjax1");
        // CommonAuthority.set_focus(document.ConditionForm.targetName, "noSelect");
    '
>
<center>
<?php echo $menu->out_title_border() ?>
    
    <div id='showAjax1'>
    </div>
    <div id='showAjax2'>
    </div>
</center>
</body>
<?php echo $menu->out_alert_java(false)?>
</html>
