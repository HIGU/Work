<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ω�δ������������ӹ�������Ͽ���������   ������� Form   MVC View �� //
// Copyright (C) 2006-2013 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/03/09 Created   assembly_time_compare_ViewCondForm.php              //
// 2006/03/13 ���ʶ�ʬ������Ȥ��� targetDivision ���ɲ�                    //
// 2006/05/10 ���ȡ���ư������������ �̤˾Ȳ񥪥ץ������ɲ�           //
// 2007/09/03 php�Υ��硼�ȥ��åȥ�����ɸ�ॿ��(�侩��)���ѹ�               //
//               �����ֹ�����Ǥ���褦���ɲ�(��������󤫤����)       //
// 2013/01/29 �Х�������Υݥ�פ��ѹ� ɽ���Τߥǡ����ϥХ����Τޤ� ��ë//
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<!-- <meta http-equiv="Refresh" content="15;URL=<?php echo $this->menu->out_self(), "?showMenu={$this->request->get('showMenu')}"?>"> -->
<title><?php echo $this->menu->out_title() ?></title>
<?php echo $this->menu->out_site_java() ?>
<?php echo $this->menu->out_css() ?>
<link rel='stylesheet' href='assembly_time_compare.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='assembly_time_compare.js?<?php echo $uniq ?>'></script>
</head>
<body style='overflow-y:hidden;'
    onLoad='
        AssemblyTimeCompare.set_focus(document.ConditionForm.targetDateStr, "select");
        setInterval("AssemblyTimeCompare.blink_disp(\"blink_item\")", 500);
        <?php if ($this->request->get('targetPlanNo') != '') echo "AssemblyTimeCompare.checkANDexecute(document.ConditionForm);\n"; ?>
        <?php if ($this->request->get('showMenu') == 'Both') echo "AssemblyTimeCompare.checkANDexecute(document.ConditionForm);\n"; ?>
    '
>
<center>
<?php echo $this->menu->out_title_border() ?>
    
    <form name='ConditionForm' action='<?php echo $this->menu->out_self() ?>' method='post'
        onSubmit='return AssemblyTimeCompare.checkANDexecute(this)'
    >
        <!----------------- ������ ��ʸ��ɽ������ ------------------->
        <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='1'>
            <tr>
                    <!--  bgcolor='#ffffc6' �������� --> 
                <td colspan='7' width='760' align='center' class='winbox caption_color'>
                    <span id='blink_item'>���������ϰϤ���ꤷ�Ʋ�������</span>
                </td>
            </tr>
            <tr>
                <td class='winbox' align='right'>
                    ������
                </td>
                <td class='winbox' align='center'>
                    <input type='text' name='targetDateStr' size='8' class='pt14b' value='<?php echo $this->request->get('targetDateStr'); ?>' maxlength='8'>
                ��
                    <input type='text' name='targetDateEnd' size='8' class='pt14b' value='<?php echo $this->request->get('targetDateEnd'); ?>' maxlength='8'>
                </td>
                <td class='winbox' align='right' title='�����ֹ�Ϥ狼����ʬ���������С�������ʬ�˹��פ����Τ򸡺����ޤ���'>
                    �����ֹ�
                </td>
                <td class='winbox' align='center' title='�����ֹ�Ϥ狼����ʬ���������С�������ʬ�˹��פ����Τ򸡺����ޤ���'>
                    <input type='text' name='targetAssyNo' size='10' class='pt14b' value='<?php echo $this->request->get('targetAssyNo'); ?>' maxlength='9'
                        onKeyUp='baseJS.keyInUpper(this);'
                        title='�����ֹ�Ϥ狼����ʬ���������С�������ʬ�˹��פ����Τ򸡺����ޤ���'
                    >
                </td>
                <td class='winbox' align='center'>
                    <select name='targetDivision' onChange='AssemblyTimeCompare.checkANDexecute(ConditionForm)'>
                        <option value='AL'<?php if($this->request->get('targetDivision')=='AL')echo ' selected'?>>��������</option>
                        <option value='CA'<?php if($this->request->get('targetDivision')=='CA')echo ' selected'?>>������</option>
                        <option value='CH'<?php if($this->request->get('targetDivision')=='CH')echo ' selected'?>>��ɸ��</option>
                        <option value='CS'<?php if($this->request->get('targetDivision')=='CS')echo ' selected'?>>������</option>
                        <option value='LA'<?php if($this->request->get('targetDivision')=='LA')echo ' selected'?>>������</option>
                        <option value='LH'<?php if($this->request->get('targetDivision')=='LH')echo ' selected'?>>��˥�</option>
                        <option value='LB'<?php if($this->request->get('targetDivision')=='LB')echo ' selected'?>>���Υݥ��</option>
                    </select>
                </td>
                <td class='winbox' align='center'>
                    <select name='targetProcess' onChange='AssemblyTimeCompare.checkANDexecute(ConditionForm)'>
                        <option value='H'<?php if($this->request->get('targetProcess')=='H')echo ' selected'?>>���ȹ���</option>
                        <option value='M'<?php if($this->request->get('targetProcess')=='M')echo ' selected'?>>��ư������</option>
                        <option value='G'<?php if($this->request->get('targetProcess')=='G')echo ' selected'?>>��������</option>
                        <option value='A'<?php if($this->request->get('targetProcess')=='A')echo ' selected'?>>�����ι���</option>
                    </select>
                </td>
                <td class='winbox' align='center'>
                    <input type='submit' name='exec' value='�¹�'>
                    &nbsp;&nbsp;
                    <input type='button' name='clear' value='���ꥢ' onClick='AssemblyTimeCompare.viewClear();'>
                </td>
            </tr>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
    </form>
    
    <div id='showAjax'>
    </div>
</center>
</body>
<?php echo $this->menu->out_alert_java()?>
</html>
