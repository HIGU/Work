<?php
//////////////////////////////////////////////////////////////////////////////
// ������ ��¤����ڤ��δ���ξȲ�          ������� Form       MVC View �� //
// Copyright (C) 2007-2008 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/10/13 Created   act_summary_ViewCondForm.php                        //
// 2007/11/09 ���祳���ɤ�ɽ�� �ɲä�ȼ�� <div id='showAjax'>������<br>��� //
// 2008/06/11 �����������祳���ɤ��Ԥ���褦���ѹ�                 ��ë //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=EUC-JP'>
<meta http-equiv='Content-Style-Type' content='text/css'>
<meta http-equiv='Content-Script-Type' content='text/javascript'>
<!-- <meta http-equiv='Refresh' content='15;URL=<?php echo $menu->out_self(), "?showMenu={$request->get('showMenu')}"?>'> -->
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<link rel='stylesheet' href='act_summary.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='act_summary.js?<?php echo $uniq ?>'></script>
</head>
<body style='overflow-y:hidden;'
    onLoad='
        ActSummary.set_focus(document.ConditionForm.targetDateYM, "noSelect");
        // ActSummary.intervalID = setInterval("ActSummary.blink_disp(\"blink_item\")", 1300);
        <?php if ($request->get('AutoStart') != '') echo 'ActSummary.checkANDexecute(document.ConditionForm, 1)'; ?>
    '
>
<center>
<?php echo $menu->out_title_border() ?>
    
    <form name='ConditionForm' action='<?php echo $menu->out_self() ?>' method='post'
        onSubmit='return ActSummary.checkANDexecute(this, 1)'
    >
        <!----------------- ������ ��ʸ��ɽ������ ------------------->
        <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='1'>
            <tr>
                    <!--  bgcolor='#ffffc6' �������� --> 
                <td align='center' class='winbox caption_color' nowrap>
                    <span id='blink_item'>����ǯ��</span>
                </td>
                <td class='winbox' align='center'>
                    <select name='targetDateYM' class='pt14b' onChange='//ActSummary.dateCreate(document.ConditionForm)'>
                        <option value='ǯ������' style='color:red;' selected>ǯ������</option>
                        <?php echo $model->getTargetDateYMvalues($session) ?>
                    </select>
                </td>
                <td align='center' class='winbox caption_color' nowrap>
                    <span id='act_code'>���祳����</span>
                </td>
                <td class='winbox' align='center'>
                    <input type='text' name='act_id' size='4' maxlength='3' onChange='ActSummary.selectId(act_id.value)'>
                </td>
                <td align='center' class='winbox caption_color' nowrap>
                    <span id='blink_item'>��������</span>
                </td>
                <td class='winbox' align='center'>
                    <select name='targetAct_id' class='pt14b' onChange='//ActSummary.dateCreate(document.ConditionForm)'>
                        <option value='��������' style='color:red;' selected>��������</option>
                        <?php echo $model->getTargetAct_idValues($session) ?>
                    </select>
                </td>
                <td class='winbox' align='center'>
                    <input type='button' name='exec1' value='�¹�' onClick='ActSummary.checkANDexecute(document.ConditionForm, 1);' title='����å�����С����β���ɽ�����ޤ���'>
                    &nbsp;
                    <input type='button' name='exec2' value='����' onClick='ActSummary.checkANDexecute(document.ConditionForm, 2);' title='����å�����С��̥�����ɥ���ɽ�����ޤ���'>
                    &nbsp;
                    <input type='button' name='clear' value='���ꥢ' onClick='ActSummary.viewClear();'>
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
<?php echo $menu->out_alert_java()?>
</html>
