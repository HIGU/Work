<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ω�������������ӹ�������Ͽ��������� �����ȾȲ��Խ�  MVC View �� //
// Copyright (C) 2006-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/05/01 Created   assembly_time_compare_ViewEditComment.php           //
// 2006/05/08 �����ȤξȲ��Խ��ѥơ��֥�Υ����������ֹ梪�ײ��ֹ���ѹ�//
// 2007/06/12 parentReload()���ɲä������ȹ������Ͽƥǡ����ι����б�      //
//            <textarea>��wrap='virtual' �� 'hard' ���ѹ�                   //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title>��Ω�������� ������ӤΥ����ȾȲ��Խ�</title>
<?php echo $this->menu->out_site_java() ?>
<?php echo $this->menu->out_css() ?>
<link rel='stylesheet' href='assembly_time_compare_edit.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='assembly_time_compare_edit.js?<?php echo $uniq ?>'></script>
<script type='text/javascript'>
var G_reloadFlg = true;
function parentReload()
{
    if (!window.opener.parent.AssemblyTimeCompare) return; //IE�ʤ�OK NN7.1�Ǥ�NG�б���onUnload��if���ɲ�(try catch�Ǥ�OK)
    window.opener.parent.AssemblyTimeCompare.checkANDexecute(window.opener.parent.document.ConditionForm);
}
</script>
</head>
<body style='overflow:hidden; background-color:#e6e6e6;'
    onLoad='
        AssemblyTimeCompare.set_focus(document.CommentForm.clear, "noSelect");
        <?php echo $this->result->get('AutoClose') ?>
    '
    onUnload='if (document.all) if (G_reloadFlg) parentReload(); // IE�ʤ�'
>
<center>
    <form name='CommentForm' action='<?php echo $this->menu->out_self() ?>?showMenu=CommentSave&targetPlanNo=<?php echo urlencode($this->request->get('targetPlanNo'))?>&targetAssyNo=<?php echo urlencode($this->request->get('targetAssyNo'))?>' method='post'
        onSubmit='G_reloadFlg=false;'
    >
        <div class='pt14b'><?php echo $this->result->get('title') ?></div>
        <textarea name='comment' class='pt12b' cols='63' rows='8' wrap='hard' style='background-color:floralwhite;'><?php echo $this->result->get('comment')?></textarea>
                    <!-- style='background-color:#e6e6e6;' readonly -->
        <div>
            <input type='submit' name='save' value='��Ͽ' style='position:relative; top:6px;'>
                &nbsp;&nbsp;
            <input type='button' name='clear' value='���' onClick='G_reloadFlg=false; window.close();' style='position:relative; top:6px;'>
        </div>
    </form>
    
    <div id='showAjax'>
    </div>
</center>
</body>
<?php echo $this->menu->out_alert_java(false)?>
</html>
