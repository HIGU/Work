<?php
//////////////////////////////////////////////////////////////////////////////
// ���Ƚ���ν��� ��� �Ȳ�        ������� Form                MVC View �� //
// Copyright (C) 2008-2017 Norihisa.Ohya usoumu@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2008/09/22 Created   working_hours_report_ViewCondForm.php               //
// 2017/05/08 8�鿦�Τ�����򳰤���                                         //
// 2017/06/02 ����Ĺ���� �ܳʲ�ư                                           //
// 2017/06/21 ����Ĺ��������Ĺ�⤹�٤�ɽ�����б�                            //
// 2017/06/28 ��������ɽ�����б����������Τߤξ��ñ������(̤����)        //
// 2017/06/29 �����̾Ȳ���б��ʹ���Ĺ�����                                //
// 2017/07/12 ��ʬ�ʳ���Java���顼ȯ���ΰ١�����                            //
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
<link rel='stylesheet' href='working_hours_report.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='working_hours_report.js?<?php echo $uniq ?>'></script>
<script type="text/javascript" src="jkl-calendar_sp1.1.js" charset="Shift_JIS"></script>
<script type="text/javascript" src="HolidayChk.js" charset="Shift_JIS"></script>
<script language="JavaScript">
    var cal1      = new JKL.Calendar('cal_1','ConditionForm','targetDateStr');
    cal1.min_date = new Date( "2016/04/01" );
    var cal2      = new JKL.Calendar('cal_2','ConditionForm','targetDateEnd');
    cal2.min_date = new Date( "2016/04/01" );
    var cdview    = new JKL.Calendar('cd_view','','');
    var OnMenu = 'out';
    <!--
    document.onmousedown = function(e){
        if(OnMenu != 'over'){
            //document.getElementById('cal_1').style.visibility = 'hidden';
            //document.getElementById('cal_2').style.visibility = 'hidden';
            cal1.hide();
            cal2.hide();
            OnMenu = 'over';
        }
    }
    // -->
</script>
</head>
<body style='overflow-y:hidden;'
    onLoad='
        WorkingHoursReport.set_focus(document.ConditionForm.targetDateStr, "noSelect");
        // WorkingHoursReport.intervalID = setInterval("WorkingHoursReport.blink_disp(\"blink_item\")", 1300);
        <?php if ($request->get('AutoStart') != '') echo 'WorkingHoursReport.checkANDexecute(document.ConditionForm, 10)'; ?>
        <?php if ($request->get('MailStart') != '') echo 'WorkingHoursReport.checkANDexecute(document.ConditionForm, 11)'; ?>
        <?php 
        if ($request->get('MailStart') != '') {
            if ($model->sendChkMail($request)) {
                $_SESSION['s_sysmsg'] = '�᡼����������ޤ�����';
            } else {
                $_SESSION['s_sysmsg'] = '�᡼�������Ǥ��ޤ���Ǥ�����';
            }
        } 
        ?>
        <?php if ($request->get('CorrectFlg') == 'y') echo 'WorkingHoursReport.checkANDexecute(document.ConditionForm, 4)'; ?>
        <?php if ($request->get('ConfirmFlg') == 'y') echo "WorkingHoursReport.ConfirmFlgexecute(document.ConditionForm, 6, {$request->get('targetSection')})"; ?>
    '
    onChange='cdview.hide_nocd();'
>
<div id='cd_view'>
<center>
<?php echo $menu->out_title_border() ?>
    <?php 
    $request->add('CorrectFlg', '');
    $request->add('ConfirmFlg', '');
    ?>
    <form name='ConditionForm' action='<?php echo $menu->out_self() ?>' method='post'
        onSubmit='return WorkingHoursReport.checkANDexecute(this, 1)'
    >
        <!----------------- ������ ��ʸ��ɽ������ ------------------->
        <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='1'>
            <tr>
                    <!--  bgcolor='#ffffc6' �������� --> 
                <td align='left' class='winbox caption_color' nowrap>
                    <span id='blink_item'>�о�ǯ��</span>
                </td>
                <td class='winbox' align='left' nowrap colspan='3'>
                    <select name='targetDateYM' class='pt14b' onChange='WorkingHoursReport.dateCreate(document.ConditionForm); cal1.getFormValue(); cal2.getFormValue();'>
                        <!-- <option value='200605' selected>2006ǯ05��</option> -->
                        <option value='<?php echo date('Ym') ?>' selected>��������</option>
                        <?php echo $model->getTargetDateYMvalues($request) ?>
                    </select>
                    ����
                    <input type='text' id='str_date' name='targetDateStr' size='8' class='pt12b' value='<?php echo $request->get('targetDateStr'); ?>' maxlength='8' onChange='cal1.getFormValue(); cal1.hide();'>
                    <div id='cal_1' style='float:left' onMouseover="OnMenu = 'over'" onMouseout ="OnMenu = 'out'"></div>
                    <a href="javascript:cal2.hide(); cal1.write();" onMouseover="OnMenu = 'over'" onMouseout ="OnMenu = 'out'"><img src="calendar.png" style="border:none;"></a>
                    ��
                    <input type='text' id='end_date' name='targetDateEnd' size='8' class='pt12b' value='<?php echo $request->get('targetDateEnd'); ?>' maxlength='8' onChange='cal2.getFormValue(); cal2.hide();'>
                    <div id='cal_2' style='float:left' onMouseover="OnMenu = 'over'" onMouseout ="OnMenu = 'out'"></div>
                    <a href="javascript:cal1.hide(); cal2.write();" onMouseover="OnMenu = 'over'" onMouseout ="OnMenu = 'out'"><img src="calendar.png" style="border:none;"></a>
                </td>
                <td class='winbox' align='left' nowrap>
                    <div class='pt12b'><input type='radio' name='formal' value='details' <?php if ($request->get('formal') != 'total') echo 'checked'; ?>>����
                    <input type='radio' name='formal' value='total' <?php if ($request->get('formal') == 'total') echo 'checked'; ?>>����</div>
                </td>
                <!--
                <?php
                if (!getCheckAuthority(29)) {                    // ǧ�ڥ����å�
                    if (!getCheckAuthority(42)) {                    // ǧ�ڥ����å�
                        if (!getCheckAuthority(43)) {                    // ǧ�ڥ����å�
                            if (!getCheckAuthority(55)) {                    // ǧ�ڥ����å�
                ?>
                <td class='winbox' align='center'>
                    <input type='button' name='exec2' value='�����ǧ' onClick='WorkingHoursReport.checkANDexecute(document.ConditionForm, 8);' title='����å�����С����β���ɽ�����ޤ���'>
                </td>
                <?php
                            }
                        }
                    }
                }
                if (getCheckAuthority(28)) {                    // ǧ�ڥ����å�
                ?>
                <td class='winbox' align='center'>
                    <input type='button' name='exec2' value='��������' onClick='WorkingHoursReport.checkANDexecute(document.ConditionForm, 4);' title='����å�����С����β���ɽ�����ޤ���'>
                </td>
                <?php
                }
                //} else {
                ?>
                -->
                <!--
                <td class='winbox' align='center'>
                    <input type='button' name='exec2' value='�����������' onClick='WorkingHoursReport.checkANDexecute(document.ConditionForm, 9);' title='����å�����С����β���ɽ�����ޤ���'>
                </td>
                -->
                <?php
                //}
                ?>
            </tr>
            <tr>
                    <!--  bgcolor='#ffffc6' �������� --> 
                <td align='left' class='winbox caption_color'>
                    <span id='blink_item'>�о�����</span>
                </td>
                <td class='winbox' align='left'>
                    <select name='targetSection' class='pt14b'>
                        <option value='' selected>��������</option>
                        <?php 
                            if (getCheckAuthority(28)) {
                        ?>
                        <option <?php if ($request->get('targetSection') == '-2') echo 'selected '; ?>value='-2'>����
                        <?php
                            }
                        ?>
                        <?php 
                            if (getCheckAuthority(29)) {
                        ?>
                        <option <?php if ($request->get('targetSection') == '-2') echo 'selected '; ?>value='-2'>����
                        <?php
                            }
                        ?>
                        <?php echo $model->getTargetSectionvalues($request) ?>
                        <?php 
                            if (getCheckAuthority(28)) {
                        ?>
                        <!-- <option <?php if ($request->get('targetSection') == '-3') echo 'selected '; ?>value='-3'>���鿦�ʾ� -->
                        <?php
                            }
                        ?>
                    </select>
                    <!--
                    ���ϼҰ�No.
                    <input type='text' name='uid' size='8' class='pt12b' value='<?php echo $request->get('uid'); ?>' maxlength='6'>
                    -->
                </td>
                <td align='left' class='winbox caption_color'>
                    <span id='blink_item'>�оݿ���</span>
                </td>
                <td class='winbox' align='left'>
                    <select name='targetPosition' class='pt14b'>
                        <option value='' selected>���٤�</option>
                        <option value='1' <?php if ($request->get('targetPosition') == '1') echo 'selected '; ?>>�Ұ�</option>
                        <option value='2' <?php if ($request->get('targetPosition') == '2') echo 'selected '; ?>>�ѡ���</option>
                        <option value='3' <?php if ($request->get('targetPosition') == '3') echo 'selected '; ?>>���󡦤���¾</option>
                        <option value='4' <?php if ($request->get('targetPosition') == '4') echo 'selected '; ?>>��Ĺ�����ʾ�</option>
                    </select>
                </td>
                <?php
                $use_uid = $_SESSION['User_ID'];
                ?>
                <input type='hidden' name='use_uid' value='<?php echo $use_uid; ?>'>
                <td rowspan='2' class='winbox' align='center' nowrap>
                    <input type='button' name='exec1' value='�¹�' onClick='WorkingHoursReport.checkANDexecute(document.ConditionForm, 1);' title='����å�����С����β���ɽ�����ޤ���'>
                    &nbsp;
                    <input type='button' name='clear' value='���ꥢ' onClick='WorkingHoursReport.viewClear();'>
                </td>
                <!--
                <td class='winbox' align='center'>
                ����������
                </td>
                <?php
                if (getCheckAuthority(28)) {                    // ǧ�ڥ����å�
                ?>
                <td class='winbox' align='center'>
                    <input type='button' name='exec2' value='�᡼������' onClick='WorkingHoursReport.checkANDexecute(document.ConditionForm, 11);' title='����å�����С����β���ɽ�����ޤ���'>
                </td>
                <?php
                }
                ?>
                -->
            </tr>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
    </form>
    <br>
    <div id='showAjax'>
    </div>
</center>
</div>
</body>
<?php echo $menu->out_alert_java()?>
</html>
