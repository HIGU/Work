<?php
//////////////////////////////////////////////////////////////////////////////
// ���Ҷ�ͭ ��Ŭ������ξȲ񡦥��ƥʥ�                                //
//                                      MVC View �� �ե������å��ɲ�      //
// Copyright (C) 2008 Norihisa.Ohya usoumu@nitto-kohki.co.jp                //
// Changed history                                                          //
// 2008/05/30 Created   unfit_report_ViewFollow.php                         //
// 2008/08/29 masterst���ܲ�ư����                                          //
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
<link rel='stylesheet' href='unfit_report.css?<?php echo  $uniq ?>' type='text/css' media='screen'>
<script type='text/javascript' language='JavaScript' src='unfit_report.js?=<?php echo  $uniq ?>'></script>
</head>
<?php
if ($request->get('partsflg') != '') {
?>
    <body
        onLoad='
            UnfitReport.set_focus(document.apend_form.parts_no, "NOTselect");
        '
    >
<?php
} else if ($request->get('assyflg') != '') {
?>
    <body
        onLoad='
            UnfitReport.set_focus(document.apend_form.assy_no, "NOTselect");
        '
    >
<?php
} else {
?>
    <body
        onLoad='
            UnfitReport.set_focus(document.apend_form.follow_section, "NOTselect");
            UnfitReport.attenCopy(document.apend_form.elements["atten[]"]);
        '
    >
<?php
}
?>
<center>
<?php echo  $menu->out_title_border() ?>
    
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
    
    <table bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
    <table class='winbox_field' bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr>
        <form name='ControlForm' action='<?php echo $menu->out_self(), '?', $model->get_htmlGETparm(), "&id={$uniq}"?>' method='get'>
            <td nowrap <?php if($showMenu=='Apend') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return UnfitReport.ControlFormSubmit(document.ControlForm.elements["Apend"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='Apend' id='Apend'
                <?php if($showMenu=='Apend') echo 'checked' ?>>
                <label for='Apend'>��������</label>
            </td>
            <td nowrap <?php if($showMenu=='IncompleteList') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return UnfitReport.ControlFormSubmit(document.ControlForm.elements["IncompleteList"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='IncompleteList' id='IncompleteList'
                <?php if($showMenu=='IncompleteList') echo 'checked' ?>>
                <label for='IncompleteList'>�к�̤��λ����</label>
            </td>
            <td nowrap <?php if($showMenu=='CompleteList') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return UnfitReport.ControlFormSubmit(document.ControlForm.elements["CompleteList"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='CompleteList' id='CompleteList'
                <?php if($showMenu=='CompleteList') echo 'checked' ?>>
                <label for='CompleteListList'>�к���λ����</label>
            </td>
            <td nowrap <?php if($showMenu=='FollowList') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return UnfitReport.ControlFormSubmit(document.ControlForm.elements["FollowList"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='FollowList' id='FollowList'
                <?php if($showMenu=='FollowList') echo 'checked' ?>>
                <label for='FollowList'>�ե������å״�λ����</label>
            </td>
            <td nowrap <?php if($showMenu=='Group') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return UnfitReport.ControlFormSubmit(document.ControlForm.elements["Group"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='Group' id='Group'
                <?php if($showMenu=='Group') echo 'checked' ?>>
                <label for='Group'>���롼�פ��Խ�</label>
            </td>
            <input type='hidden' name='year'  value='<?php echo $year?>'>
            <input type='hidden' name='month' value='<?php echo $month?>'>
            <input type='hidden' name='day'   value='<?php echo $day?>'>
            <!-----------------
            <td nowrap class='winbox' onClick='return UnfitReport.addFavoriteIcon("http://<?php echo $_SERVER['SERVER_ADDR'],$menu->out_self()?>", "<?php echo $_SESSION['User_ID']?>");' id='favi'>
                <label for='favi'>���������ɲ�</label>
            </td>
            ------------------>
        </form>
        </tr>
    </table>
        </td></tr>
    </table> <!----------------- ���ߡ�End ------------------>
    
    <div class='caption_font'></div>
    
    <table class='list' bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <caption>
            <?php echo $menu->out_caption(), "\n"?>
        </caption>
        <tr><td> <!-- ���ߡ� -->
    <table class='winbox_field' bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <form name='apend_form' action='<?php echo $menu->out_self(), '?', $model->get_htmlGETparm(), "&id={$uniq}"?>'
            method='post' onSubmit='return UnfitReport.follow_formCheck(this)'
        >
            <input type='hidden' name='showMenu' value='<?php echo $showMenu?>'>
        <tr>
            <th class='winbox' colspan='6'>
                ȯ������
            </th>
        </tr>
        <tr>
            <th class='winbox' colspan='6'>
                [�ե������å�]
            </th>
        </tr>
        <tr>
            <td class='winbox' colspan='6'>
                <center>
                    <textarea name='follow_section' cols='96' rows=5 wrap='hard' onKeyUp='limitChars(this,250,5)'><?php echo $follow_section?></textarea>
                </center>
            </td>
        </tr>
        <tr>
            <th class='winbox' colspan='6'>
                �ʼ��ݾڲ�
            </th>
        </tr>
        <tr>
            <th class='winbox' colspan='6'>
                [�ե������å�]
            </th>
        </tr>
        <tr>
            <td class='winbox' colspan='6'>
                <center>
                    <textarea name='follow_quality' cols='96' rows=5 wrap='hard' onKeyUp='limitChars(this,250,5)'><?php echo $follow_quality?></textarea>
                </center>
            </td>
        </tr>
        <tr>
            <th class='winbox' colspan='6'>
                [�ո���]
            </th>
        </tr>
        <tr>
            <td class='winbox' colspan='6'>
                <center>
                    <textarea name='follow_opinion' cols='96' rows=8 wrap='hard' onKeyUp='limitChars(this,400,8)'><?php echo $follow_opinion?></textarea>
                </center>
            </td>
        </tr>
        <tr>
            <th class='winbox' nowrap  colspan='2'>
                �ե������å״�λ
            </th>
            <td class='winbox' colspan='4'>
                <input type='radio' name='follow' value='t' id='followYes'<?php if($follow=='t')echo' checked' ?>><label for='followYes'>��λ</label>
                <input type='radio' name='follow' value='f' id='followNo'<?php if($follow!='t')echo' checked' ?>><label for='followNo'>̤��λ</label>
            </td>
        </tr>
        <tr>
            <th class='winbox' nowrap>
                ������
            </th>
            <td class='winbox' nowrap colspan='5'>
                <select name='userID_name' size='1'
                    onClick ='UnfitReport.sponsorNameCopy();'
                    onChange='UnfitReport.sponsorNameCopy();'
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
                �����
            </th>
            <td class='winbox' nowrap colspan='5'>
                <select name='group_name' size='1'
                    onClick ='//UnfitReport.groupMemberCopy(document.apend_form.group_name, document.apend_form.elements["atten[]"]);'
                    onChange='UnfitReport.groupMemberCopy(document.apend_form.group_name, document.apend_form.elements["atten[]"]);'
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
            <td class='winbox' nowrap valign='top' colspan='1'>
                <select name='atten[]' size='5' multiple
                    onClick ='UnfitReport.attenCopy(this);'
                    onChange='UnfitReport.attenCopy(this);'
                >
                    <?php for ($i=0; $i<$user_cnt; $i++) {?>
                    <option value='<?php echo $userID_name[$i][0]?>'<?php echo @$userID_name[$i][2]?>><?php echo $userID_name[$i][1]?></option>
                    <?php } ?>
                </select>
            </td>
            <td class='winbox' valign='middle' colspan='4'>
                Ctrl Key ���� Sift Key �򲡤��ʤ��饯��å������ʣ������Ǥ��ޤ���
                <textarea name='attenView' cols='51' rows=3 wrap='virtual' style='background-color:#e6e6e6;' readonly></textarea>
            </td>
        </tr>
        <tr>
            <th class='winbox' nowrap>
                �᡼������
            </th>
            <td class='winbox' nowrap colspan='5'>
                <input type='radio' name='mail' value='t' id='mailYes'<?php if($mail=='t')echo' checked' ?>><label for='mailYes'>Yes</label>
                <input type='radio' name='mail' value='f' id='mailNo'<?php if($mail!='t')echo' checked' ?>><label for='mailNo'>No</label>
                &nbsp;&nbsp;�����ؤΥ᡼�����
            </td>
        </tr>
        <tr>
            <td class='winbox' align='center' nowrap colspan='6'>
                <input type='submit' name='<?php echo $showMenu?>' value='��Ͽ' class='fc_blue'>
                &nbsp; &nbsp;
                <input type='button' name='Cancel' value='���'
                    onClick='location.replace("<?php echo $menu->out_self(), "?year={$year}&month={$month}&day={$day}&showMenu=CompleteList&", $model->get_htmlGETparm(), "&id={$uniq}"?>");'
                >
            </td>
            <input type='hidden' name='<?php echo $showMenu?>' value='dummy'>
            <input type='hidden' name='serial_no' value='<?php echo $serial_no?>'>
            <input type='hidden' name='year'  value='<?php echo $year?>'>
            <input type='hidden' name='month' value='<?php echo $month?>'>
            <input type='hidden' name='day'   value='<?php echo $day?>'>
        </tr>
        <tr>
            <td class='winbox' colspan='6'>��</td>
        </tr>
        <tr>
            <td class='winbox' colspan='6'><B>������ ��Ŭ����������</B></td>
        </tr>
        <tr>
            <th class='winbox' nowrap colspan='2'>
                ��Ŭ������
            </th>
            <td class='winbox' colspan='4'>
                <input type='text' name='subject' value='<?php echo $subject?>' size='66' maxlength='32' style='background-color:#e6e6e6;' readonly>
            </td>
        </tr>
        <tr>
            <th class='winbox' nowrap>
                ����
                 (When)
            </th>
            <th class='winbox' nowrap>
                ȯ��ǯ����
            </th>
            <?php
                if ($showMenu == 'Follow') {
                    $tmpYear  = $year;  $year  = $result->get('editYear');
                    $tmpMonth = $month; $month = $result->get('editMonth');
                    $tmpDay   = $day;   $day   = $result->get('editDay');
                    $model->getTargetAssyNames($request);
                }
                if ($request->get('partsflg') != '') {
                    $tmpYear  = $year;  $year  = $request->get('yearReg');
                    $tmpMonth = $month; $month = $request->get('monthReg');
                    $tmpDay   = $day;   $day   = $request->get('dayReg');
                } 
                if ($request->get('assyflg') != '') {
                    $tmpYear  = $year;  $year  = $request->get('yearReg');
                    $tmpMonth = $month; $month = $request->get('monthReg');
                    $tmpDay   = $day;   $day   = $request->get('dayReg');
                }
            ?>
            <td class='winbox' nowrap colspan='4'>
                <input type='text' name='yearReg' value='<?php echo $year?>' size='5' maxlength='4' style='background-color:#e6e6e6;' readonly>
                ǯ
                <input type='text' name='monthReg' value='<?php echo $month?>' size='3' maxlength='2' style='background-color:#e6e6e6;' readonly>
                ��
                <input type='text' name='dayReg' value='<?php echo $day?>' size='3' maxlength='2' style='background-color:#e6e6e6;' readonly>
                ��
            </td>
            <?php
                if ($showMenu == 'Follow') {
                     $year       = $tmpYear;
                     $month      = $tmpMonth;
                     $day        = $tmpDay;
                     $assy_name  = $model->getTargetAssyNamesEdit($assy_no);
                     $parts_name = $model->getTargetPartsNamesEdit($parts_no);
                }
                if ($request->get('partsflg') != '') {
                    $year  = $tmpYear;
                    $month = $tmpMonth;
                    $day   = $tmpDay;
                    $request->add('partsflg',  '');
                } 
                if ($request->get('assyflg') != '') {
                    $year  = $tmpYear;
                    $month = $tmpMonth;
                    $day   = $tmpDay;
                    $request->add('assyflg',  '');
                }
            ?>
        </tr>
        <tr>
            <th class='winbox' nowrap>
                �ɤ���
                 (Where)
            </th>
            <th class='winbox' nowrap>
                ȯ�����
            </th>
            <td class='winbox' colspan='4'>
                <input type='text' name='place' value='<?php echo $place?>' size='42' maxlength='20'style='background-color:#e6e6e6;' readonly>
            </td>
        </tr>
        <tr>
            <th class='winbox' nowrap>
                ï��
                 (Who)
            </th>
            <th class='winbox' nowrap>
                ��Ǥ����
            </th>
            <td class='winbox' colspan='4'>
                <input type='text' name='section' value='<?php echo $section?>' size='42' maxlength='20'style='background-color:#e6e6e6;' readonly>
            </td>
        </tr>
        <tr>
            <th class='winbox' nowrap rowspan='2' colspan='1'>
                ����
                 (What)
            </th>
            <th class='winbox' nowrap  colspan='1'>
                ����̾
            </th>
            <td class='winbox' colspan='1'><B>
                <?php 
                if ($showMenu == 'Follow') {
                    echo $assy_name;
                } else {
                    echo $model->getTargetAssyNames($request);
                }
                ?>
            </B></td>
            <th class='winbox' nowrap  colspan='1'>
                ����̾
            </th>
            <td class='winbox' colspan='2'><B>
                <?php 
                if ($showMenu == 'Follow') {
                    echo $parts_name;
                } else {
                    echo $model->getTargetPartsNames($request);
                }
                ?>
            </B></td>
        </tr>
        <tr>
            <th class='winbox' nowrap  colspan='1'>
                �����ֹ�
            </th>
            <td class='winbox' colspan='1'>
                <input type='text' name='assy_no' value='<?php echo $assy_no?>' size='12' maxlength='9' style='background-color:#e6e6e6;' readonly onChange='AssyNoSubmit(assy_no)' autocomplete='off'>
                <input type='hidden' name='assyflg'  value=''>
            </td>
            <th class='winbox' nowrap  colspan='1'>
                �����ֹ�
            </th>
            <td class='winbox' colspan='2'>
                <input type='text' name='parts_no' value='<?php echo $parts_no?>' size='12' maxlength='9' style='background-color:#e6e6e6;' readonly onChange='PartsNoSubmit(parts_no)' autocomplete='off'>
                <input type='hidden' name='partsflg'  value=''>
            </td>
        </tr>
        <tr>
            <th class='winbox' nowrap rowspan='4' colspan='1'>
                �ɤΤ褦��
                 (How)
            </th>
            <th class='winbox' nowrap colspan='1'>
                ȯ������
            </th>
            <td class='winbox' colspan='4'>
                <textarea name='occur_cause' cols='65' rows=3 wrap='hard' onKeyUp='limitChars(this,100,3)' style='background-color:#e6e6e6;' readonly><?php echo $occur_cause?></textarea>
            </td>
        </tr>
        <tr>
            <th class='winbox' nowrap  colspan='1'>
                ��Ŭ�����
            </th>
            <td class='winbox' colspan='5'>
                <input type='text' name='unfit_num' value='<?php echo $unfit_num?>' size='10' maxlength='9' style='background-color:#e6e6e6;' readonly>
            </td>
        </tr>
        <tr>
            <th class='winbox' nowrap colspan='1'>
                ή�и���<br>�ݳ�ή�Ф�<br>ͭ��̵
            </th>
            <td class='winbox' colspan='4'>
                <textarea name='issue_cause' cols='65' rows=3 wrap='hard' onKeyUp='limitChars(this,100,3)' style='background-color:#e6e6e6;' readonly><?php echo $issue_cause?></textarea>
            </td>
        </tr>
        <tr>
            <th class='winbox' nowrap  colspan='1'>
                ή�п���
            </th>
            <td class='winbox' colspan='5'>
                <input type='text' name='issue_num' value='<?php echo $issue_num?>' size='10' maxlength='9' style='background-color:#e6e6e6;' readonly>
            </td>
        </tr>
        <tr>
            <th class='winbox' colspan='6'>
                ����Ŭ���ʤν��֡�
            </th>
        </tr>
        <tr>
            <td class='winbox' colspan='6'>
                <center>
                    <textarea name='unfit_dispose' cols='96' rows=3 wrap='hard' onKeyUp='limitChars(this,150,3)' style='background-color:#e6e6e6;' readonly><?php echo $unfit_dispose?></textarea>
                </center>
            </td>
        </tr>
        <tr>
            <th class='winbox' colspan='4'>
                ��ȯ�����к���
            </th>
            <th class='winbox' colspan='2'>
                �»ܹ���(�ʾڵ�����)
            </th>
        </tr>
        <tr>
            <td class='winbox' colspan='4' rowspan='2')>
                <center>
                    <textarea name='occur_measure' cols='68' rows=4 wrap='hard' onKeyUp='limitChars(this,140,4)' style='background-color:#e6e6e6;' readonly><?php echo $occur_measure?></textarea>
                </center>
            </td>
            <th class='winbox' nowrap  colspan='1'>
                ��ʿŸ��
            </th>
            <td class='winbox' colspan='1'>
                <input type='radio' name='suihei' value='t' id='yes'<?php if($suihei=='t') { echo 'checked'; } else { echo 'disabled'; } ?>><label for='yes'>ͭ</label>
                <input type='radio' name='suihei' value='f' id='no'<?php if($suihei!='t') { echo 'checked'; } else { echo 'disabled'; } ?>><label for='no'>̵</label>
            </td>
        </tr>
        <tr>
            <th class='winbox' nowrap  colspan='1'>
                ����Ÿ��
            </th>
            <td class='winbox' colspan='1'>
                <input type='radio' name='kanai' value='t' id='yes'<?php if($kanai=='t') { echo 'checked'; } else { echo 'disabled'; } ?>><label for='yes'>ͭ</label>
                <input type='radio' name='kanai' value='f' id='no'<?php if($kanai!='t') { echo 'checked'; } else { echo 'disabled'; } ?>><label for='no'>̵</label>
            </td>
        </tr>
        <tr>
            <td class='winbox' nowrap colspan='4'>
            <?php
                if ($showMenu == 'Follow') {
                    $tmpYear  = $year;  $year  = $result->get('occurYear');
                    $tmpMonth = $month; $month = $result->get('occurMonth');
                    $tmpDay   = $day;   $day   = $result->get('occurDay');
                }
            ?>
            �� �»�ͽ����
                <input type='text' name='occur_yearReg' value='<?php echo $year?>' size='5' maxlength='4' style='background-color:#e6e6e6;' readonly>
                ǯ
                <input type='text' name='occur_monthReg' value='<?php echo $month?>' size='3' maxlength='2' style='background-color:#e6e6e6;' readonly>
                ��
                <input type='text' name='occur_dayReg' value='<?php echo $day?>' size='3' maxlength='2' style='background-color:#e6e6e6;' readonly>
                �� ��
            </td>
            <?php
                if ($showMenu == 'Follow') {
                     $year  = $tmpYear;
                     $month = $tmpMonth;
                     $day   = $tmpDay;
                }
            ?>
            <th class='winbox' nowrap  colspan='1'>
                �ݳ�Ÿ��
            </th>
            <td class='winbox' colspan='1'>
                <input type='radio' name='kagai' value='t' id='yes'<?php if($kagai=='t') { echo 'checked'; } else { echo 'disabled'; } ?>><label for='yes'>ͭ</label>
                <input type='radio' name='kagai' value='f' id='no'<?php if($kagai!='t') { echo 'checked'; } else { echo 'disabled'; } ?>><label for='no'>̵</label>
            </td>
        </tr>
        <tr>
            <th class='winbox' colspan='4'>
                ��ή���к���
            </th>
            <th class='winbox' nowrap  colspan='1'>
                ɸ���Ÿ��
            </th>
            <td class='winbox' colspan='1'>
                <input type='radio' name='hyoujyun' value='t' id='yes'<?php if($hyoujyun=='t') { echo 'checked'; } else { echo 'disabled'; } ?>><label for='yes'>ͭ</label>
                <input type='radio' name='hyoujyun' value='f' id='no'<?php if($hyoujyun!='t') { echo 'checked'; } else { echo 'disabled'; } ?>><label for='no'>̵</label>
            </td>
        </tr>
        <tr>
            <td class='winbox' colspan='4' rowspan='2'>
                <center>
                    <textarea name='issue_measure' cols='68' rows=4 wrap='hard' onKeyUp='limitChars(this,140,4)' style='background-color:#e6e6e6;' readonly><?php echo $issue_measure?></textarea>
                </center>
                
            </td>
            <th class='winbox' nowrap  colspan='1'>
                ����»�
            </th>
            <td class='winbox' colspan='1'>
                <input type='radio' name='kyouiku' value='t' id='yes'<?php if($kyouiku=='t') { echo 'checked'; } else { echo 'disabled'; } ?>><label for='yes'>ͭ</label>
                <input type='radio' name='kyouiku' value='f' id='no'<?php if($kyouiku!='t') { echo 'checked'; } else { echo 'disabled'; } ?>><label for='no'>̵</label>
            </td>
        </tr>
        <tr>
            <th class='winbox' nowrap  colspan='1'>
                �����ƥ�
            </th>
            <td class='winbox' colspan='1'>
                <input type='radio' name='system' value='t' id='yes'<?php if($system=='t') { echo 'checked'; } else { echo 'disabled'; } ?>><label for='yes'>ͭ</label>
                <input type='radio' name='system' value='f' id='no'<?php if($system!='t') { echo 'checked'; } else { echo 'disabled'; } ?>><label for='no'>̵</label>
            </td>
        </tr>
        <tr>
            <td class='winbox' nowrap colspan='6'>
            <?php
                if ($showMenu == 'Follow') {
                    $tmpYear  = $year;  $year  = $result->get('issueYear');
                    $tmpMonth = $month; $month = $result->get('issueMonth');
                    $tmpDay   = $day;   $day   = $result->get('issueDay');
                }
            ?>
            �� �»�ͽ����
                <input type='text' name='issue_yearReg' value='<?php echo $year?>' size='5' maxlength='4' style='background-color:#e6e6e6;' readonly>
                ǯ
                <input type='text' name='issue_monthReg' value='<?php echo $month?>' size='3' maxlength='2' style='background-color:#e6e6e6;' readonly>
                ��
                <input type='text' name='issue_dayReg' value='<?php echo $day?>' size='3' maxlength='2' style='background-color:#e6e6e6;' readonly>
                �� ��
            </td>
            <?php
                if ($showMenu == 'Follow') {
                     $year  = $tmpYear;
                     $month = $tmpMonth;
                     $day   = $tmpDay;
                }
            ?>
        </tr>
        <tr>
            <th class='winbox' nowrap colspan='1' rowspan='1'>
            [�ե������å�ͽ��] 
            </th>
            <td class='winbox' nowrap colspan='5'>
            ( ï
                <input type='text' name='follow_who' value='<?php echo $follow_who?>' size='22' maxlength='10' style='background-color:#e6e6e6;' readonly>
                ���� ����
                <?php
                if ($showMenu == 'Follow') {
                    $tmpYear  = $year;  $year  = $result->get('issueYear');
                    $tmpMonth = $month; $month = $result->get('issueMonth');
                    $tmpDay   = $day;   $day   = $result->get('issueDay');
                }
                ?>
                <input type='text' name='follow_yearReg' value='<?php echo $year?>' size='5' maxlength='4' style='background-color:#e6e6e6;' readonly>
                ǯ
                <input type='text' name='follow_monthReg' value='<?php echo $month?>' size='3' maxlength='2' style='background-color:#e6e6e6;' readonly>
                ��
                <input type='text' name='follow_dayReg' value='<?php echo $day?>' size='3' maxlength='2' style='background-color:#e6e6e6;' readonly>
                �� ��
            </td>
            <?php
                if ($showMenu == 'Follow') {
                     $year  = $tmpYear;
                     $month = $tmpMonth;
                     $day   = $tmpDay;
                }
            ?>
        </tr>
        <tr>
            <td class='winbox' colspan='6'>
                <textarea name='follow_how' cols='70' rows=2 wrap='hard' onKeyUp='limitChars(this,72,2)' style='background-color:#e6e6e6;' readonly><?php echo $follow_how ?></textarea>
            </td>
        </tr>
        <tr>
            <th class='winbox' nowrap  colspan='1'>
                ����No.
            </th>
            <td class='winbox' colspan='5'>
                <input type='text' name='receipt_no' value='<?php echo $receipt_no?>' size='15' maxlength='15' style='background-color:#e6e6e6;' readonly>
            </td>
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
