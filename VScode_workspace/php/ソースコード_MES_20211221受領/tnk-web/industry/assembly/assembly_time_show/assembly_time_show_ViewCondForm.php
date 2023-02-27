<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ω����Ͽ�����ȼ��ӹ�������� �Ȳ�         ������� Form    MVC View �� //
// Copyright (C) 2006-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/03/01 Created   assembly_time_show_ViewCondForm.php                 //
// 2006/03/03 targetPlanNo���ꥯ�����Ȥ���Ƥ������ϼ�ư�Ǽ¹Ԥ���        //
// 2006/03/12 noMenu�Υꥯ�����Ȥ����ä����ϥ����ȥ�ܡ�������ɽ�����ʤ�  //
// 2006/05/19 regOnly�Υꥯ�����Ȥ����ä�������Ͽ�����Τ�ɽ������         //
// 2006/05/28 �Ĥ���ܥ���regOnly�λ����ä��Τ� noMenu �����ѹ�           //
// 2007/06/17 regOnly�ξ��� usedTime, workerCount��hidden°�����ɲ�       //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<!-- <meta http-equiv="Refresh" content="15;URL=<?=$this->menu->out_self(), "?showMenu={$this->request->get('showMenu')}"?>"> -->
<title><?= $this->menu->out_title() ?></title>
<?= $this->menu->out_site_java() ?>
<?= $this->menu->out_css() ?>
<link rel='stylesheet' href='assembly_time_show.css?id=<?= $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='assembly_time_show.js?<?= $uniq ?>'></script>
</head>
<body
    onLoad='
        <?php if ($this->request->get('regOnly')) { ?>
        AssemblyTimeShow.set_focus(document.getElementById("closeButton"), "noSelect");
        setInterval("AssemblyTimeShow.winActiveChk(document.getElementById(\"closeButton\"))",50);
        <?php } else { ?>
        AssemblyTimeShow.set_focus(document.ConditionForm.targetPlanNo, "select");
        setInterval("AssemblyTimeShow.blink_disp(\"blink_item\")", 500);
        <?php } ?>
        <?php if ($this->request->get('targetPlanNo') != '') echo 'AssemblyTimeShow.checkANDexecute(document.ConditionForm)'; ?>
    '
>
<center>
<?php if (!$this->request->get('noMenu')) {?>
<?php echo $this->menu->out_title_border() ?>
<?php }?>
    
    <form name='ConditionForm' action='<?= $this->menu->out_self() ?>' method='post'
        onSubmit='return AssemblyTimeShow.checkANDexecute(this)'
    >
        <input type='hidden' name='usedTime' value='<?php echo $this->request->get('usedTime'); ?>'>
        <input type='hidden' name='workerCount' value='<?php echo $this->request->get('workerCount'); ?>'>
    <?php if ($this->request->get('regOnly')) {?>
        <input type='hidden' name='targetPlanNo' value='<?php echo $this->request->get('targetPlanNo'); ?>'>
        <input type='hidden' name='regOnly' value='yes'>
    <?php } else { ?>
        <input type='hidden' name='regOnly' value='no'>
        <!----------------- ������ ��ʸ��ɽ������ ------------------->
        <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='5'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>
            <tr>
                    <!--  bgcolor='#ffffc6' �������� --> 
                <td colspan='2' width='350' align='center' class='winbox caption_color'>
                    <span id='blink_item'>�ײ��ֹ����ꤷ�Ʋ�������</span>
                </td>
            </tr>
            <tr>
                <td class='winbox' align='right'>
                    �ײ��ֹ�λ���
                </td>
                <td class='winbox' align='center'>
                    <input type='text' name='targetPlanNo' size='10' class='pt14b' value='<?php echo $this->request->get('targetPlanNo'); ?>' maxlength='8'>
                </td>
            </tr>
            <tr>
                <td class='winbox' colspan='2' align='center'>
                    <input type='submit' name='exec' value='�¹�'>
                    &nbsp;&nbsp;
                    <input type='button' name='clear' value='���ꥢ' onClick='AssemblyTimeShow.viewClear();'>
                </td>
            </tr>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
    <?php } ?>
    </form>
    <div id='showAjax'>
    </div>
    <?php if ($this->request->get('noMenu')) { ?>
        <div align='center'>
            <input type='button' id='closeButton' value='&nbsp;�Ĥ���&nbsp;' onClick='window.close()'>
        </div>
    <?php } ?>
</center>
</body>
<?=$this->menu->out_alert_java()?>
</html>
