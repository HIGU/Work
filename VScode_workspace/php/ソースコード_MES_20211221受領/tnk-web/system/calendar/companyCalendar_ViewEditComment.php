<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ҥδ��ܥ������� ���ƥʥ�  �����ȾȲ��Խ�        MVC View �� //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/06/24 Created   companyCalendar_ViewEditComment.php                 //
//            NN7.1���б���window.close()��setTimeout("window.close()", 400)//
//            400=�ƤΥ���ɻ��֤˰�¸����(������������ɤ�200��NG)   //
// 2006/07/05 onUnload='parentReload();'��Ԥ�������NN7.1��NG�ʤΤǥ����� //
//            onUnload='if (document.all) parentReload();'���б�            //
//            submit����G_reloadFlg=false;�ˤ��ƥ���ɤǼ����ƻҴط���ݻ�//
// 2006/07/11 Controller��Execute()�᥽�åɤ��ɲä�Action��showMenu�����β� //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title>��Ҵ��ܥ��������Υ����ȾȲ��Խ�</title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<link rel='stylesheet' href='companyCalendar.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='companyCalendar.js?<?php echo $uniq ?>'></script>
<script type='text/javascript'>
var G_reloadFlg = true;
function parentReload()
{
    // window.opener.location.replace('<?php echo $menu->out_self(), "?id={$uniq}"?>');
    if (!window.opener.parent.CompanyCalendar) return; //�Ǥ�IE�ʤ�OK NN7.1�Ǥ�NG�б���onUnload��if���ɲ�(try catch�Ǥ�OK)
    window.opener.parent.CompanyCalendar.AjaxLoadUrl
    ("<?php echo "{$menu->out_self()}?showMenu=Calendar&targetCalendar=Comment&year={$request->get('year')}&month={$request->get('month')}&day={$request->get('day')}&id={$uniq}" ?>");
}
</script>
</head>
<body style='overflow:hidden; background-color:#e6e6e6;'
    onLoad='
        setInterval("CompanyCalendar.winActiveChk()", 30);
        CompanyCalendar.set_focus(document.CommentForm.note, "noSelect");
        // CompanyCalendar.set_focus(document.CommentForm.clear, "noSelect");
    '
    onUnload='if (document.all) if (G_reloadFlg) parentReload(); // IE�ʤ�'
>
<center>
    <form name='CommentForm' action='<?php echo "{$menu->out_self()}?Action=CommentSave&showMenu=EditComment&year={$request->get('year')}&month={$request->get('month')}&day={$request->get('day')}&id={$uniq}"?>' method='post'
        onSubmit='G_reloadFlg=false;'
    >
        <div class='pt14b'><?php echo $result->get('title') ?></div>
        <!-- <textarea name='note' cols='50' rows='5' wrap='virtual' style='background-color:floralwhite;'><?php echo $result->get('note')?></textarea> -->
        <input type='text' name='note' size='40' maxlength='50' value='<?php echo $result->get('note')?>'
            title='�ٲˤ���������������ơ������ˤ�����ͳ���Ķ����ˤ�����ͳ�������Ϥ��ޤ���' style='height:70px;' class='pt14b'
        >
                    <!-- style='background-color:#e6e6e6;' readonly -->
        <div style='position:relative; top:6px;'>
            <input type='button' name='close' value='��Ͽ' style='color:blue;' onClick='G_reloadFlg=false; document.CommentForm.submit();'>
                &nbsp;&nbsp;
            <input type='button' name='clear' value='�Ĥ���' onClick='parentReload(); setTimeout("window.close()", 400);'>
        </div>
    </form>
    
    <div id='showAjax'>
    </div>
</center>
</body>
<?php echo $menu->out_alert_java(false)?>
</html>
