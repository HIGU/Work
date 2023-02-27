<?php
//////////////////////////////////////////////////////////////////////////////
// ���Ҷ�ͭ ���(�ǹ礻)�������塼��ɽ�β�ļ��Υ��ƥʥ�                //
//                                          MVC View ��  ��ļ��ꥹ��ɽ��   //
// Copyright (C) 2005-2015 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/11/12 Created   meeting_schedule_ViewRoom.php                       //
// 2006/05/09 ��ʬ�Υ������塼��Τ�ɽ��(�ޥ��ꥹ��)��ǽ���ɲ�              //
// 2006/06/20 ������������</tr>��ȴ���Ƥ���Τ���                       //
// 2009/12/17 �Ȳ񡦰������̥ƥ���                                     ��ë //
// 2015/06/19 �ײ�ͭ��ξȲ���ɲ�                                     ��ë //
// 2015/06/25 �ײ�ͭ��ξȲ���̸��¤��ѹ���53��                     ��ë //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<?php echo $menu->out_jsBaseClass() ?>
<link rel='stylesheet' href='calendar.css?<?php echo  $uniq ?>' type='text/css' media='screen'>
<link rel='stylesheet' href='meeting_schedule.css?<?php echo  $uniq ?>' type='text/css' media='screen'>
<script type='text/javascript' src='meeting_schedule.js?=<?php echo $uniq ?>'></script>
</head>
<body onLoad='MeetingSchedule.set_focus(document.room_form.<?php echo $focus?>, "noSelect")'>
<center>
<?php echo $menu->out_title_only_border() ?>
    
    <table border='0' align='center'>
        <tr>
        <td valign='top'>
            <?php echo $calendar_pre->show_calendar($day_pre['year'], $day_pre['mon']);?>
        </td>
        <td valign='top'>
            <?php echo $calendar_now->show_calendar($day_now['year'], $day_now['mon'], $day_now['mday']);?>
        </td>
        <td valign='top'>
            <?php echo $calendar_nex1->show_calendar($day_nex1['year'], $day_nex1['mon']);?>
        </td>
        <td valign='top'>
            <?php echo $calendar_nex2->show_calendar($day_nex2['year'], $day_nex2['mon']);?>
        </td>
        </tr>
    </table>
    
    <form name='ControlForm' action='<?php echo $menu->out_self(), '?', $model->get_htmlGETparm(), "&id={$uniq}"?>' method='get'>
    <table bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
    <table class='winbox_field' bgcolor='e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr>
            <td nowrap <?php if($showMenu=='Apend') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return MeetingSchedule.ControlFormSubmit(document.ControlForm.elements["Apend"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='Apend' id='Apend'
                <?php if($showMenu=='Apend') echo 'checked' ?>>
                <label for='Apend'>���(�ǹ礻)����</label>
            </td>
            <td nowrap <?php if($showMenu=='List') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return MeetingSchedule.ControlFormSubmit(document.ControlForm.elements["List"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='List' id='List'
                <?php if($showMenu=='List') echo 'checked' ?>>
                <label for='List'>���(�ǹ礻)����</label>
            </td>
            <td nowrap <?php if($showMenu=='MyList') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='
                    <?php if ($_SESSION['User_ID'] == '000000') { ?>
                    alert("���߶�ͭ�⡼�ɤǳ����Ƥ��ޤ��Τǥޥ��ꥹ�Ȥ�ɽ���Ǥ��ޤ���\n\n�Ŀͥ⡼�ɤǳ����л��ѤǤ��ޤ���"); return false;
                    <?php } else { ?>
                    return MeetingSchedule.ControlFormSubmit(document.ControlForm.elements["MyList"], document.ControlForm);
                    <?php } ?>
                '
            >
                <input type='radio' name='showMenu' value='MyList' id='MyList'
                <?php if($showMenu=='MyList') echo 'checked' ?>>
                <label for='MyList'>�ޥ��ꥹ��</label>
            </td>
            <?php
            if (getCheckAuthority(68)) {
            ?>
            <td nowrap <?php if($showMenu=='Absence') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return MeetingSchedule.ControlFormSubmit(document.ControlForm.elements["Absence"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='Absence' id='Absence'
                <?php if($showMenu=='Absence') echo 'checked' ?>>
                <label for='Absence'>�Ժ�ͽ��</label>
            </td>
            <?php
            }
            ?>
            <?php
            if (getCheckAuthority(53)) {
            ?>
            <td nowrap <?php if($showMenu=='Holyday') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return MeetingSchedule.ControlFormSubmit(document.ControlForm.elements["Holyday"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='Holyday' id='Holyday'
                <?php if($showMenu=='Holyday') echo 'checked' ?>>
                <label for='Holyday'>�ײ�ͭ��</label>
            </td>
            <?php
            }
            ?>
            <td nowrap <?php if($showMenu=='Group') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return MeetingSchedule.ControlFormSubmit(document.ControlForm.elements["Group"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='Group' id='Group'
                <?php if($showMenu=='Group') echo 'checked' ?>>
                <label for='Group'>���롼�פ��Խ�</label>
            </td>
            <td nowrap <?php if($showMenu=='Room') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return MeetingSchedule.ControlFormSubmit(document.ControlForm.elements["Room"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='Room' id='Room'
                <?php if($showMenu=='Room') echo 'checked' ?>>
                <label for='Room'>��ļ����Խ�</label>
            </td>
            <?php
            if ($_SESSION['User_ID'] == '300144') {
            ?>
            <td nowrap <?php if($showMenu=='Car') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return MeetingSchedule.ControlFormSubmit(document.ControlForm.elements["Car"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='Car' id='Car'
                <?php if($showMenu=='Car') echo 'checked' ?>>
                <label for='Car'>���Ѽ֤��Խ�</label>
            </td>
            <?php
            }
            ?>
            <input type='hidden' name='year'  value='<?php echo $year?>'>
            <input type='hidden' name='month' value='<?php echo $month?>'>
            <input type='hidden' name='day'   value='<?php echo $day?>'>
            <td nowrap class='winbox' onClick='return MeetingSchedule.addFavoriteIcon("http://<?php echo $_SERVER['SERVER_ADDR'],$menu->out_self()?>", "<?php echo $_SESSION['User_ID']?>");' id='favi'>
                <label for='favi'>���������ɲ�</label>
            </td>
        </tr>
    </table>
        </td></tr>
    </table> <!----------------- ���ߡ�End ------------------>
    </form>
    
    <table class='list' bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <caption>��ļ��Υ��ƥʥ�</caption>
        <tr><td> <!-- ���ߡ� -->
    <table class='winbox_field' bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <form name='room_form' action='<?php echo $menu->out_self(), '?', $model->get_htmlGETparm(), "&id={$uniq}"?>'
            method='post' onSubmit='return MeetingSchedule.room_formCheck(this)'
        >
        <tr>
            <td class='winbox' nowrap>
                ��ļ����ֹ�
            </td>
            <td class='winbox' nowrap>
                <input type='text' name='room_no' value='<?php echo $room_no?>' size='6' maxlength='5'
                    style='ime-mode:disabled;'
                    onChange='this.value=this.value.toUpperCase()'
                <?php echo $readonly?>
                >
            </td>
            <td class='winbox' nowrap>
                �� �� �� ̾
            </td>
            <td class='winbox' nowrap>
                <input type='text' name='room_name' value='<?php echo $room_name?>' size='16' maxlength='16' class='pt12b'>
            </td>
            <td class='winbox' nowrap>
                ��ʣ�����å�
            </td>
            <td class='winbox' nowrap>
                <input type='radio' name='duplicate' value='t' id='yes'<?php if($duplicate=='����')echo' checked' ?>><label for='yes'>Yes</label>
                <input type='radio' name='duplicate' value='f' id='no'<?php if($duplicate=='���ʤ�')echo' checked' ?>><label for='no'>No</label>
            </td>
            <td class='winbox' nowrap>
                <input type='submit' name='roomEdit' value='��Ͽ' class='pt12b'>
            </td>
            <input type='hidden' name='showMenu' value='Room'>
            <input type='hidden' name='year'  value='<?php echo $year?>'>
            <input type='hidden' name='month' value='<?php echo $month?>'>
            <input type='hidden' name='day'   value='<?php echo $day?>'>
        </tr>
        </form>
    </table>
        </td></tr> <!-- ���ߡ� -->
    </table>
    
    <table class='list' bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <caption>
            <table border='0' width='100%'>
                <tr>
                    <td align='right' nowrap width='60%'>
                        ��ļ��ΰ��� &nbsp;&nbsp;
                    </td>
                    <td align='center' nowrap width='40%'>
                        <form name='pageForm' action='<?php echo $menu->out_self(), "?id={$uniq}"?>' method='post'>
                        <?php echo $pageControl?>
                        <input type='hidden' name='showMenu' value='Room'>
                        <input type='hidden' name='year'  value='<?php echo $year?>'>
                        <input type='hidden' name='month' value='<?php echo $month?>'>
                        <input type='hidden' name='day'   value='<?php echo $day?>'>
                        </form>
                    </td>
                </tr>
            </table>
        </caption>
        <tr><td> <!-- ���ߡ� -->
    <table class='winbox_field' bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <th class='winbox' width='30'>&nbsp;</th>
        <th class='winbox' width='40'>&nbsp;</th>
        <th class='winbox' width='40'>&nbsp;</th>
        <th class='winbox' nowrap>��ļ��ֹ�</th>
        <th class='winbox' nowrap>�� �� �� ̾</th>
        <th class='winbox' nowrap>��ʣ�����å�</th>
        <th class='winbox' nowrap>ͭ����̵��</th>
        <th class='winbox' nowrap>������</th>
    <?php if ($rows >= 1) { ?>
        <?php for ($r=0; $r<$rows; $r++) { ?>
            <?php if ($res[$r][3] == 'ͭ��') { ?>
            <tr>
            <?php } else { ?>
            <tr style='color:gray;'>
            <?php } ?>
            <td class='winbox' align='right' nowrap><?php echo $r + 1 + $model->get_offset()?></td>
            <td class='winbox' align='center' nowrap>
                <a href='<?php echo $menu->out_self(), "?room_no={$res[$r][0]}&showMenu=Room&roomOmit=go&room_name=", urlencode($res[$r][1]), '&duplicate=', urlencode($res[$r][2]), "&year={$year}&month={$month}&day={$day}&", $model->get_htmlGETparm(), "&id={$uniq}"?>'
                    style='text-decoration:none;'
                    onClick='return confirm("���˻Ȥ��Ƥ��ʤ���к�����Ƥ����ꤢ��ޤ���\n\n������Ϻ������̵���ˤ��Ʋ�������\n\n������ޤ��������Ǥ�����")'
                >
                    ���
                </a>
            </td>
            <td class='winbox' align='center' nowrap>
                <a href='<?php echo $menu->out_self(), "?room_no={$res[$r][0]}&showMenu=Room&roomCopy=go&room_name=", urlencode($res[$r][1]), '&duplicate=', urlencode($res[$r][2]), "&year={$year}&month={$month}&day={$day}&", $model->get_htmlGETparm(), "&id={$uniq}"?>'
                    style='text-decoration:none;'>�Խ�
                </a>
            </td>
            <!-- ��ļ��ֹ� -->
            <td class='winbox' align='right'><?php echo $res[$r][0]?></td>
            <!-- ��ļ�̾ -->
            <td class='winbox' align='left'><?php echo $res[$r][1]?></td>
            <!-- ��ʣ�����å� -->
            <td class='winbox' align='center'><?php echo $res[$r][2]?></td>
            <!-- ͭ����̵�� -->
            <td class='winbox' align='center' nowrap>
                <a href='<?php echo $menu->out_self(), "?room_no={$res[$r][0]}&showMenu=Room&roomActive=go&room_name=", urlencode($res[$r][1]), '&duplicate=', urlencode($res[$r][2]), "&year={$year}&month={$month}&day={$day}&", $model->get_htmlGETparm(), "&id={$uniq}"?>'
                    style='text-decoration:none;'>
                <?php echo $res[$r][3]?>
            </td>
            <!-- ������ (ɽ���Ϲ�����������Ȥ��ѹ���) -->
            <td class='winbox' align='center' onDblclick='alert("��� ��Ͽ����\n\n[ <?php echo $res[$r][4]?> ]\n\n�Ǥ���");'>
                <?php echo $res[$r][5]?>
            </td>
            </tr>
        <?php } ?>
    <?php } else { ?>
        <tr><td class='winbox' align='center' colspan='8'>
            ��Ͽ������ޤ���
        </td></tr>
    <?php } ?>
    </table>
        </td></tr> <!-- ���ߡ� -->
    </table>
</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
