<?php
//////////////////////////////////////////////////////////////////////////////
// ���Ҷ�ͭ �ǹ礻(���)�������塼��ɽ�ξȲ񡦥��ƥʥ�                  //
//                                                  MVC View ��   �ɲ�      //
// Copyright (C) 2005-2019 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/11/02 Created   meeting_schedule_ViewApend.php                      //
// 2005/11/21 ���ʼԤΥ��롼�׻�����ɲ�                                    //
// 2005/11/22 Edit(�Խ�)�����о����դˤ����ؤ���(���ֻ���������б�)        //
//            �᡼��������Ⱦ��(��Ͽ)�򥻥�Ƕ��ڤ�������դ�ʬ�ष��      //
// 2005/11/29 ���üԤؤΥ᡼����� �� ���ʼԤؤΥ᡼����� ��̾���ѹ�       //
// 2006/05/09 ��ʬ�Υ������塼��Τ�ɽ��(�ޥ��ꥹ��)��ǽ���ɲ�              //
// 2006/06/19 Apend��Edit���ѤΤ��� hidden��showMenu value='�ѿ�'>���ѹ�    //
// 2006/06/20 ������������</tr>��ȴ���Ƥ���Τ���                       //
// 2009/12/17 �Ȳ񡦰��������ѤΥƥ���                                 ��ë //
// 2011/07/04 ���ޡ��������б��ˤ�곫�ϻ��֤�5��������ѹ�            ��ë //
// 2015/06/19 �ײ�ͭ��ξȲ���ɲ�                                     ��ë //
// 2015/06/25 �ײ�ͭ��ξȲ���̸��¤��ѹ���53��                     ��ë //
// 2018/06/18 ���֤�0��23���ѹ�                                        ��ë //
// 2019/03/15 �䲹�嵡��Ư���������Ѽ֡��Ժ߼ԤΥ�˥塼���ɲ�         ��ë //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo  $menu->out_title() ?></title>
<?php echo  $menu->out_site_java() ?>
<?php echo  $menu->out_css() ?>
<link rel='stylesheet' href='calendar.css?<?php echo  $uniq ?>' type='text/css' media='screen'>
<link rel='stylesheet' href='meeting_schedule.css?<?php echo  $uniq ?>' type='text/css' media='screen'>
<script type='text/javascript' language='JavaScript' src='meeting_schedule.js?=<?php echo  $uniq ?>'></script>
</head>
<body
    onLoad='
        MeetingSchedule.set_focus(document.apend_form.subject, "NOTselect");
        MeetingSchedule.attenCopy(document.apend_form.elements["atten[]"]);
        MeetingSchedule.strTimeCopy();
        MeetingSchedule.endTimeCopy();
    '
>
<center>
<?php echo  $menu->out_title_only_border() ?>
    
    <div class='caption_font'></div>
    
    <table class='list' bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <caption>
            <?php echo $menu->out_caption(), "\n"?>
        </caption>
        <tr><td> <!-- ���ߡ� -->
    <table class='winbox_field' bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <form name='apend_form' action='<?php echo $menu->out_self(), '?', $model->get_htmlGETparm(), "&id={$uniq}"?>'
            method='post' onSubmit='return MeetingSchedule.apend_formCheck(this)'
        >
            <input type='hidden' name='showMenu' value='<?php echo $showMenu?>'>
        <tr>
            <th class='winbox' nowrap>
                ��̾<br>�ڤ� �õ�����
            </th>
            <td class='winbox' width='320' colspan='2'>
                <textarea name='subject' cols='66' rows=3 wrap='virtual'><?php echo $subject?></textarea>
            </td>
        </tr>
        <tr>
            <th class='winbox' nowrap>
                ���� ��λ
            </th>
            <?php
                if ($showMenu == 'Edit') {
                    $tmpYear  = $year;  $year  = $result->get('editYear');
                    $tmpMonth = $month; $month = $result->get('editMonth');
                    $tmpDay   = $day;   $day   = $result->get('editDay');
                }
            ?>
            <td class='winbox' nowrap colspan='2'>
                <select name='yearReg' size='1'>
                    <?php for ($i=($year-1); $i<=($year+3); $i++) { ?>
                    <?php $data = sprintf('%04d', $i); ?>
                    <option value='<?php echo $data?>'<?php if($year==$data)echo' selected' ?>><?php echo $data?></option>
                    <?php } ?>
                </select>
                ǯ
                <select name='monthReg' size='1'>
                    <?php for ($i=1; $i<=12; $i++) { ?>
                    <?php $data = sprintf('%02d', $i); ?>
                    <option value='<?php echo $data?>'<?php if($month==$data)echo' selected' ?>><?php echo $data?></option>
                    <?php } ?>
                </select>
                ��
                <select name='dayReg' size='1'>
                    <?php for ($i=1; $i<=31; $i++) { ?>
                    <?php $data = sprintf('%02d', $i); ?>
                    <option value='<?php echo $data?>'<?php if($day==$data)echo' selected' ?>><?php echo $data?></option>
                    <?php } ?>
                </select>
                ��
                &nbsp;
                &nbsp;
                <select name='str_hour' size='1'
                    onClick ='MeetingSchedule.strTimeCopy();'
                    onChange='MeetingSchedule.strTimeCopy();'
                >
                    <?php for ($i=0; $i<=23; $i++) { ?>
                    <?php $data = sprintf('%02d', $i); ?>
                    <option value='<?php echo $data?>'<?php if($str_hour==$data)echo' selected' ?>><?php echo $data?></option>
                    <?php } ?>
                </select>
                ��
                <select name='str_minute' size='1'
                    onClick ='MeetingSchedule.strTimeCopy();'
                    onChange='MeetingSchedule.strTimeCopy();'
                >
                    <?php for ($i=0; $i<=55; $i+=5) { ?>
                    <?php $data = sprintf('%02d', $i); ?>
                    <option value='<?php echo $data?>'<?php if($str_minute==$data)echo' selected' ?>><?php echo $data?></option>
                    <?php } ?>
                </select>
                ʬ
                <input type='hidden' name='str_time' value='<?php echo $str_time?>' size='8' maxlength='8'
                    style='ime-mode:disabled;' class='pt12b' onChange='this.value=this.value.toUpperCase()'
                >
                ��
                <select name='end_hour' size='1'
                    onClick ='MeetingSchedule.endTimeCopy();'
                    onChange='MeetingSchedule.endTimeCopy();'
                >
                    <?php for ($i=0; $i<=23; $i++) { ?>
                    <?php $data = sprintf('%02d', $i); ?>
                    <option value='<?php echo $data?>'<?php if($end_hour==$data)echo' selected' ?>><?php echo $data?></option>
                    <?php } ?>
                </select>
                ��
                <select name='end_minute' size='1'
                    onClick ='MeetingSchedule.endTimeCopy();'
                    onChange='MeetingSchedule.endTimeCopy();'
                >
                    <?php for ($i=0; $i<=55; $i+=5) { ?>
                    <?php $data = sprintf('%02d', $i); ?>
                    <option value='<?php echo $data?>'<?php if($end_minute==$data)echo' selected' ?>><?php echo $data?></option>
                    <?php } ?>
                </select>
                ʬ
                &nbsp;&nbsp;
                <input type='hidden' name='end_time' value='<?php echo $end_time?>' size='8' maxlength='8'
                    style='ime-mode:disabled;' class='pt12b' onChange='this.value=this.value.toUpperCase()'
                >
            </td>
            <?php
                if ($showMenu == 'Edit') {
                     $year  = $tmpYear;
                     $month = $tmpMonth;
                     $day   = $tmpDay;
                }
            ?>
        </tr>
        <tr>
            <th class='winbox' nowrap>
                ��ż�
            </th>
            <td class='winbox' nowrap colspan='2'>
                <select name='userID_name' size='1'
                    onClick ='MeetingSchedule.sponsorNameCopy();'
                    onChange='MeetingSchedule.sponsorNameCopy();'
                >
                    <option value=''>�������</option>
                    <?php for ($i=0; $i<$user_cnt; $i++) {?>
                    <option value='<?php echo $userID_name[$i][0]?>'<?php if($userID_name[$i][0]==$sponsor)echo' selected' ?>><?php echo $userID_name[$i][1]?></option>
                    <?php } ?>
                </select>
                <input type='text' name='sponsor' value='<?php echo $sponsor?>' size='7' maxlength='6'
                    style='ime-mode:disabled; background-color:#e6e6e6;' class='pt12b'
                    readonly onChange='this.value=this.value.toUpperCase()'
                >
            </td>
        </tr>
        <tr>
            <th class='winbox' nowrap rowspan='2'>
                ���ʼ�
            </th>
            <td class='winbox' nowrap colspan='2'>
                <select name='group_name' size='1'
                    onClick ='//MeetingSchedule.groupMemberCopy(document.apend_form.group_name, document.apend_form.elements["atten[]"]);'
                    onChange='MeetingSchedule.groupMemberCopy(document.apend_form.group_name, document.apend_form.elements["atten[]"]);'
                >
                    <option value=''>�������</option>
                    <?php for ($i=0; $i<$JSgroup; $i++) {?>
                    <option value='<?php echo $i?>'><?php echo $JSgroup_name[$i]?></option>
                    <?php } ?>
                </select>
                ���롼�פǻ��ꤹ����ϡ������������ǲ�������
            </td>
        </tr>
        <tr>
            <td class='winbox' nowrap valign='top'>
                <select name='atten[]' size='5' multiple
                    onClick ='MeetingSchedule.attenCopy(this);'
                    onChange='MeetingSchedule.attenCopy(this);'
                >
                    <?php for ($i=0; $i<$user_cnt; $i++) {?>
                    <option value='<?php echo $userID_name[$i][0]?>'<?php echo @$userID_name[$i][2]?>><?php echo $userID_name[$i][1]?></option>
                    <?php } ?>
                </select>
            </td>
            <td class='winbox' valign='middle'>
                Ctrl Key ���� Sift Key �򲡤��ʤ��饯��å������ʣ������Ǥ��ޤ���
                <textarea name='attenView' cols='51' rows=3 wrap='virtual' style='background-color:#e6e6e6;' readonly></textarea>
            </td>
        </tr>
        <tr>
            <th class='winbox' nowrap>
                �᡼������
            </th>
            <td class='winbox' nowrap colspan='2'>
                <input type='radio' name='mail' value='t' id='yes'<?php if($mail=='t')echo' checked' ?>><label for='yes'>Yes</label>
                <input type='radio' name='mail' value='f' id='no'<?php if($mail!='t')echo' checked' ?>><label for='no'>No</label>
                &nbsp;&nbsp;���ʼԤؤΥ᡼�����
            </td>
        </tr>
        <tr>
            <th class='winbox' nowrap>
                ���
            </th>
            <td class='winbox' nowrap colspan='2'>
                <select name='room_no' size='1'>
                    <?php for ($i=0; $i<$rowsRoom; $i++) {?>
                    <option value='<?php echo $resRoom[$i][0]?>'<?php if($resRoom[$i][0]==$room_no)echo' selected' ?>><?php echo $resRoom[$i][1]?></option>
                    <?php } ?>
                </select>
                <!--
                <input type='hidden' name='room_no' value='<?php echo $room_no?>' size='6' maxlength='6'
                    class='pt12b' onClick="this.value = this.value + ' '; this.select();"
                >
                -->
            </td>
        </tr>
        <?php
        //if ($_SESSION['User_ID'] == '300144') {
        ?>
        <tr>
            <th class='winbox' nowrap>
                ���Ѽ�
            </th>
            <td class='winbox' nowrap colspan='2'>
                <select name='car_no' size='1'>
                    <?php for ($i=0; $i<$rowsCar; $i++) {?>
                    <option value='<?php echo $resCar[$i][0]?>'<?php if($resCar[$i][0]==$car_no)echo' selected' ?>><?php echo $resCar[$i][1]?></option>
                    <?php } ?>
                </select>
            </td>
        </tr>
        <?php
        //} else {
        ?>
        <!--
        <input type='hidden' name='car_no' value='1'>
        -->
        <?php
        //}
        ?>
        
        <tr>
                <?php if ($showMenu == 'Edit') { ?>
            <td class='winbox' align='right' nowrap colspan='2'>
                �᡼�������
                <input type='radio' name='reSend' value='t' id='sendYes'><label for='sendYes'>Yes</label>
                <input type='radio' name='reSend' value='f' id='sendNo' checked><label for='sendNo'>No</label>
                <input type='submit' name='<?php echo $showMenu?>' value='���' class='fc_blue'>
            </td>
            <td class='winbox' align='left' nowrap>
                <?php } else { ?>
            <td class='winbox' align='center' nowrap colspan='3'>
                <input type='submit' name='<?php echo $showMenu?>' value='��Ͽ' class='fc_blue'>
                <?php } ?>
                &nbsp; &nbsp;
                <input type='button' name='Cancel' value='���'
                    onClick='location.replace("<?php echo $menu->out_self(), "?year={$year}&month={$month}&day={$day}&showMenu=List&only=yes&", $model->get_htmlGETparm(), "&id={$uniq}"?>");'
                >
                <?php if ($showMenu == 'Edit') { ?>
                &nbsp; &nbsp; &nbsp;
                <input type='submit' name='Delete' value='���' class='fc_red'
                    onClick='return confirm("�������ȸ��ؤ��᤻�ޤ���\n\n�������Ǥ�����");'
                >
                &nbsp;
                <input type='submit' name='Apend' value='���ԡ���¸' class='fc_green'
                    onClick='return confirm("���Υ������塼����ѹ�����\n\n�������ɲä��ޤ���\n\n�������Ǥ�����");'
                >
                <?php } ?>
            </td>
            <input type='hidden' name='<?php echo $showMenu?>' value='dummy'>
            <input type='hidden' name='serial_no' value='<?php echo $serial_no?>'>
            <input type='hidden' name='year'  value='<?php echo $year?>'>
            <input type='hidden' name='month' value='<?php echo $month?>'>
            <input type='hidden' name='day'   value='<?php echo $day?>'>
            <input type='hidden' name='only'   value='yes'>
        </tr>
        </form>
    </table>
        </td></tr> <!-- ���ߡ� -->
    </table>
</center>
</body>
<?php echo $menu->out_alert_java()?>
<?php
if ($JSgroup) {
echo "<script type='text/javascript'>\n";
echo "var Ggroup_member = new Array({$JSgroup});\n";
for ($r=0; $r<$JSgroup; $r++) {
    $script = "    Ggroup_member[{$r}] = new Array(";
    $cnt = count($JSgroup_member[$r]);
    for ($i=0; $i<$cnt; $i++) {
        if ($i == 0) {
            $script .= "'{$JSgroup_member[$r][$i]}'";
        } else {
            $script .= ", '{$JSgroup_member[$r][$i]}'";
        }
    }
    $script .= ");\n";
    echo $script;
}
echo "</script>\n";
}
?>
</html>
