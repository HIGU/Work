<?php
//////////////////////////////////////////////////////////////////////////////
// ���߸����� �����ܤη�ʿ�ѽи˿�����ͭ������Ȳ�           MVC View ��  //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/06/08 Created   inventory_average_ViewEditComment.php               //
// 2007/06/11 showMenu=CommentSave �� Action=CommentSave ���ѹ�             //
// 2007/06/12 <textarea>��wrap='virtual' �� 'hard' ���ѹ�                   //
// 2007/06/14 �װ��ޥ��������Խ��������ȡ��װ�����Ͽ�Խ� ��Ϣ ��λ        //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title>������� �߸���ͭ��Υ����ȾȲ��Խ�</title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<link rel='stylesheet' href='inventory_average.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='inventory_average.js?<?php echo $uniq ?>'></script>
<script type='text/javascript'>
var G_reloadFlg = true;
function parentReload()
{
    if (!window.opener.parent.InventoryAverage) return; //IE�ʤ�OK NN7.1�Ǥ�NG�б���onUnload��if���ɲ�(try catch�Ǥ�OK)
    window.opener.parent.InventoryAverage.checkANDexecute(window.opener.parent.document.ConditionForm);
}
</script>
</head>
<body style='overflow:hidden; background-color:#e6e6e6;'
    onLoad='
        InventoryAverage.set_focus(document.CommentForm.clear, "noSelect");
        <?php echo $result->get('AutoClose') ?>
    '
    onUnload='if (document.all) if (G_reloadFlg) parentReload(); // IE�ʤ�'
>
<center>
    <form name='CommentForm' action='<?php echo $menu->out_self() ?>?Action=CommentSave&showMenu=Comment&targetPartsNo=<?php echo urlencode($request->get('targetPartsNo'))?>' method='post'
        onSubmit='G_reloadFlg=false;'
    >
        <div class='pt14b'><?php echo $result->get('title') ?></div>
        <div class='pt12b'>
            �װ�����
            <select name='targetFactor' onChange='InventoryAverage.selectOptionsLink(this, document.CommentForm.Explanation)'>
                <?php echo $result->get('factorNameOptions') ?>
            </select>
            &nbsp;����
            <select name='Explanation' style='width:350px;' onChange='InventoryAverage.selectOptionsLink(this, document.CommentForm.targetFactor)'>
                <?php echo $result->get('factorExplanationOptions') ?>
            </select>
        </div>
        <textarea name='comment' class='pt12b' cols='63' rows='6' wrap='hard' style='background-color:floralwhite;'><?php echo $result->get('comment')?></textarea>
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
<?php echo $menu->out_alert_java(false)?>
</html>
