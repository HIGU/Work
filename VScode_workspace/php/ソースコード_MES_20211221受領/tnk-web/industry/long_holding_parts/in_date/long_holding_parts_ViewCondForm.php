<?php
//////////////////////////////////////////////////////////////////////////////
// Ĺ����α���ʤξȲ� �ǽ�����������Ǹ��ߺ߸ˤ�����ʪ         MVC View ��  //
// Copyright (C) 2006-2019 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/04/03 Created   long_holding_parts_ViewCondForm.php                 //
// 2006/04/06 ����иˤ��ϰϵڤӲ��(ʪ��ư��)�ξ�索�ץ��������        //
// 2013/01/29 �Х�������Υݥ�פ��ѹ� ɽ���Τߥǡ����ϥХ����Τޤ� ��ë//
// 2019/01/28 �ġ�����ɲá��Х���롦ɸ��򥳥��Ȳ�                 ��ë //
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
<link rel='stylesheet' href='long_holding_parts.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='long_holding_parts.js?<?php echo $uniq ?>'></script>
</head>
<body style='overflow-y:hidden;'
    onLoad='
        LongHoldingParts.set_focus(document.ConditionForm.exec, "noSelect");
        setInterval("LongHoldingParts.blink_disp(\"blink_item\")", 500);
        <?php if ($this->request->get('showMenu') == 'Both') echo "LongHoldingParts.checkANDexecute(document.ConditionForm);\n"; ?>
    '
>
<center>
<?php echo $this->menu->out_title_border() ?>
    
    <form name='ConditionForm' action='<?php echo $this->menu->out_self() ?>' method='post'
        onSubmit='return LongHoldingParts.checkANDexecute(this)'
    >
        <!----------------- ������ ��ʸ��ɽ������ ------------------->
        <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='1'>
            <tr>
                    <!--  bgcolor='#ffffc6' �������� --> 
                <td colspan='4' width='800' align='center' class='winbox caption_color'>
                    <span id='blink_item'>�ǽ������������ʥ��롼�פ���ꤷ�Ʋ�������</span>
                </td>
            </tr>
            <tr>
                <td class='winbox' align='center' nowrap>
                    �ǽ�������<br>
                    <select name='targetDate' onChange='LongHoldingParts.checkANDexecute(ConditionForm)'>
                        <?php echo $this->model->getTargetDateView($this->request) ?>
                    </select>
                    ��
                    <select name='targetDateSpan' onChange='LongHoldingParts.checkANDexecute(ConditionForm)'>
                        <?php echo $this->model->getTargetDateSpanView($this->request) ?>
                    </select>
                </td>
                <td class='winbox' align='center' nowrap>
                    ���ʥ��롼��<br>
                    <select name='targetDivision' onChange='LongHoldingParts.checkANDexecute(ConditionForm)'>
                        <option value='AL'<?php if($this->request->get('targetDivision')=='AL')echo ' selected'?>>�����롼��</option>
                        <option value='CA'<?php if($this->request->get('targetDivision')=='CA')echo ' selected'?>>���ץ�����</option>
                        <option value='CH'<?php if($this->request->get('targetDivision')=='CH')echo ' selected'?>>���ץ�ɸ��</option>
                        <option value='CS'<?php if($this->request->get('targetDivision')=='CS')echo ' selected'?>>���ץ�����</option>
                        <option value='LA'<?php if($this->request->get('targetDivision')=='LA')echo ' selected'?>>��˥�����</option>
                        <!--
                        <option value='LH'<?php if($this->request->get('targetDivision')=='LH')echo ' selected'?>>��˥��Τ�</option>
                        <option value='LB'<?php if($this->request->get('targetDivision')=='LB')echo ' selected'?>>���Υݥ��</option>
                        -->
                        <option value='TA'<?php if($this->request->get('targetDivision')=='TA')echo ' selected'?>>�ġ���</option>
                        <option value='OT'<?php if($this->request->get('targetDivision')=='OT')echo ' selected'?>>����¾����</option>
                    </select>
                </td>
                <td class='winbox' align='center' nowrap>
                    <input type='checkbox' name='targetOutFlg' id='OutFlg' value='on'<?php if($this->request->get('targetOutFlg')=='on')echo ' checked'?>>
                    <label for='OutFlg'>�иˤ����ߤ���</label>
                    <br>
                    <select name='targetOutDate'>
                        <?php echo $this->model->getTargetOutDateView($this->request) ?>
                    </select>
                    �ޤǤ�
                    <select name='targetOutCount'>
                        <option value='0'<?php if($this->request->get('targetOutCount')=='0')echo ' selected'?>>����ޤ�</option>
                        <option value='1'<?php if($this->request->get('targetOutCount')=='1')echo ' selected'?>>����ޤ�</option>
                        <option value='2'<?php if($this->request->get('targetOutCount')=='2')echo ' selected'?>>����ޤ�</option>
                    </select>
                </td>
                <td class='winbox' align='center' nowrap>
                    <input type='submit' name='exec' value='�¹�'>
                    &nbsp;&nbsp;
                    <input type='button' name='clear' value='���ꥢ' onClick='LongHoldingParts.viewClear();'>
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
