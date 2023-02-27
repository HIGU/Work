<?php
////////////////////////////////////////////////////////////////////////////////
// ����ֳ���ȿ���ʾȲ��                                                   //
//                                                    MVC View �� �ꥹ��ɽ��  //
// Copyright (C) 2021-2021 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2021/10/20 Created over_time_work_report_ViewInquiry.php                   //
// 2021/11/01 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////

$menu->out_html_header();

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

<link rel='stylesheet' href='../per_appli.css' type='text/css' media='screen'>
<script type='text/javascript' language='JavaScript' src='over_time_work_report.js'></script>

</head>

<body onLoad='InitQuiry()'>

<center>
<?= $menu->out_title_border() ?>

<!-- �Уģƥե�����򳫤�-->
    <div class='pt10' align='center'>
    <BR>�������ˡ��ʬ����ʤ���硢<a href="download_file.php/����ֳ���ȿ���_�Ȳ�_�ޥ˥奢��.pdf">����ֳ���ȿ���ʾȲ�˥ޥ˥奢��</a>�򻲹ͤˤ��Ʋ�������<BR>
    </div>
<!-- TEST Start.-->
    <?php if($debug) { ?>
    <div class='pt9' align='left'><font color='red'>������ �������顢�ƥ��Ȥΰ�ɽ��  ������</font></div>
    �����ߤ�UID��<?php echo $login_uid; ?>���ڥƥ��� ���ء�
    ALL��
    <input type='button' style='<?php if($login_uid=="011061") echo "background-color:yellow"; ?>' value='011061' onClick='CangeUID(this.value, "form_quiry");'>��
    ʣ���ݡ�
    <input type='button' style='<?php if($login_uid=="012394") echo "background-color:yellow"; ?>' value='012394' onClick='CangeUID(this.value, "form_quiry");'>��
    <input type='button' style='<?php if($login_uid=="017850") echo "background-color:yellow"; ?>' value='017850' onClick='CangeUID(this.value, "form_quiry");'>��
    <input type='button' style='<?php if($login_uid=="012980") echo "background-color:yellow"; ?>' value='012980' onClick='CangeUID(this.value, "form_quiry");'>��
    <input type='button' style='<?php if($login_uid=="016713") echo "background-color:yellow"; ?>' value='016713' onClick='CangeUID(this.value, "form_quiry");'>
    <BR><BR>
    �Ʋݡ�
    <input type='button' style='<?php if($login_uid=="300055") echo "background-color:yellow"; ?>' value='300055' onClick='CangeUID(this.value, "form_quiry");'>��
    <input type='button' style='<?php if($login_uid=="300349") echo "background-color:yellow"; ?>' value='300349' onClick='CangeUID(this.value, "form_quiry");'>��
    <input type='button' style='<?php if($login_uid=="300098") echo "background-color:yellow"; ?>' value='300098' onClick='CangeUID(this.value, "form_quiry");'>��
    <input type='button' style='<?php if($login_uid=="014524") echo "background-color:yellow"; ?>' value='014524' onClick='CangeUID(this.value, "form_quiry");'>��
    <input type='button' style='<?php if($login_uid=="018040") echo "background-color:yellow"; ?>' value='018040' onClick='CangeUID(this.value, "form_quiry");'>��
    <input type='button' style='<?php if($login_uid=="015202") echo "background-color:yellow"; ?>' value='015202' onClick='CangeUID(this.value, "form_quiry");'>��
    <input type='button' style='<?php if($login_uid=="016080") echo "background-color:yellow"; ?>' value='016080' onClick='CangeUID(this.value, "form_quiry");'>��
    <input type='button' style='<?php if($login_uid=="017507") echo "background-color:yellow"; ?>' value='017507' onClick='CangeUID(this.value, "form_quiry");'>��
    <input type='button' style='<?php if($login_uid=="017728") echo "background-color:yellow"; ?>' value='017728' onClick='CangeUID(this.value, "form_quiry");'>��
    <BR><div class='pt9' align='left'><font color='red'>������ �����ޤǡ��ƥ��Ȥΰ�ɽ��  ������</font></div>
    <?php } ?>
<!-- TEST End. -->
    <BR>
<form name='form_quiry' method='post' action='<?php echo $menu->out_self() ?>' onSubmit='return;'>
<!-- TEST Start.-->
    <input type='hidden' name='login_uid' value="<?php echo $login_uid; ?>">
<!-- TEST End. -->
    <input type='hidden' name='showMenu' id='id_showMenu' value='Quiry'>
    <table class='pt10' border="1" cellspacing="0">
    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table width='100%' class='winbox_field' bgcolor='#D8D8D8' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr>
                <td class='winbox' style='background-color:yellow; color:blue;' colspan='2' align='center'>
                    <div class='caption_font'><?php echo $menu->out_caption(), "\n"?></div>
                </td>
            </tr>

        <!-- ������λ��� -->
            <tr>
                <td align='center'>������λ���</td>
                <td align='center'>
                    <input type='radio' name='days_radio' id='id_s_day' value='1' <?php if($d_radio==1) echo " checked"; ?> onclick='DaysSelect(this)'><label for='id_s_day'>ñ��</label>
                    <!-- ��ҥ��������ε��������javascript���ѿ��إ��åȤ��Ƥ�����-->
                    <script> var holiday = '<?php echo $holiday; ?>';  SetHoliday(holiday);</script>
                    <select name='ddlist_year' id='id_year' onclick='WorkDateCopy()'>
                        <?php $model->getSelectOptionDate($def_y-1, $def_y+1, $def_y); ?>
                    </select>ǯ
                    <select name='ddlist_month' id='id_month' onclick='WorkDateCopy()'>
                        <?php $model->getSelectOptionDate(1, 12, $def_m); ?>
                    </select>��
                    <select name='ddlist_day' id='id_day' onclick='WorkDateCopy()'>
                        <?php $model->getSelectOptionDate(1, 31, $def_d); ?>
                    </select>��
                    <font id='id_w_youbi'>�ʡ���</font><BR>
                    <input type='hidden' name='w_date' id='id_w_date' value="<?php echo $request->get('w_date'); ?>">
                    <font id='id_range' <?php if($d_radio==1) echo " disabled"; ?>>��</font><BR>
                    <input type='radio' name='days_radio' id='id_e_day' value='2' <?php if($d_radio==2) echo " checked"; ?> onclick='DaysSelect(this)'><label for='id_e_day'>Ϣ��</label>
                    <font id='id_e_day_area' <?php if($d_radio==1) echo " disabled"; ?>>
                    <select name='ddlist_year2' id='id_year2' onclick='WorkDateCopy2()' <?php if($d_radio==1) echo " disabled"; ?>>
                        <?php $model->getSelectOptionDate($def_y2-1, $def_y2+1, $def_y2); ?>
                    </select>ǯ
                    <select name='ddlist_month2' id='id_month2' onclick='WorkDateCopy2()' <?php if($d_radio==1) echo " disabled"; ?>>
                        <?php $model->getSelectOptionDate(1, 12, $def_m2); ?>
                    </select>��
                    <select name='ddlist_day2' id='id_day2' onclick='WorkDateCopy2()' <?php if($d_radio==1) echo " disabled"; ?>>
                        <?php $model->getSelectOptionDate(1, 31, $def_d2); ?>
                    </select>��
                    <font id='id_w_youbi2'>�ʡ���</font>
                    </font>
                    <input type='hidden' name='w_date2' id='id_w_date2' value="<?php echo $request->get('w_date2'); ?>">
                </td>
            </tr>
            
        <!-- ����λ��� -->
            <tr>
                <td align='center'>���������</td>
                <td style='border:groove' align='center'>
                    <select name="ddlist_bumon">
                        <?php $model->setSelectOptionBumon($request); ?>
                    </select>
                </td>
            </tr>
            
        <!-- �Ұ��ֹ�λ��� -->
            <tr>
                <?php if(getCheckAuthority(63) || $model->IsKatyou() || $model->IsButyou() ) { ?> <!-- 63:�Ұ��ֹ����ϲ�ǽ�ʹ���Ĺ������������̳�ݡ�-->
                    <td align='center'>�����ԡʼҰ�No.�ˤλ���</td>
                    <td align='center'>
                        �Ұ��ֹ桧<input type="text" size="8" maxlength="6" name="s_no" value="<?php echo $request->get('s_no') ?>" onkeyup="value=InputCheck(this);">
                    </td>
                <?php } else { ?>
                    <td align='center'>�����ԡʼҰ�No.��</td>
                    <td align='center'>
                        <input type='hidden' name='s_no' value='<?php echo $login_uid; ?>'>
                        <p class='pt10'>�����¤��ʤ��١���������μҰ��ֹ���ꡣ</p>
                        <?php echo '�Ұ��ֹ桧' . $login_uid; ?>
                    </td>
                <?php } ?>
            </tr>
            
        <!-- ����¾��� -->
            <tr align='center'>
                <td colspan='1'>
                    ����¾���
                </td>
                <td colspan='1'>
                    <input type='radio' name='mode_radio' id='1' <?php if($m_radio==1) echo " checked"; ?> onClick='' value='1'><label for='1'>����ʤ�</label>
                    <input type='radio' name='mode_radio' id='2' <?php if($m_radio==2) echo " checked"; ?> onClick='' value='2'><label for='2'>���̤����</label>
                    <input type='radio' name='mode_radio' id='3' <?php if($m_radio==3) echo " checked"; ?> onClick='' value='3'><label for='3'>������ϺѤ�</label>
                </td>
            </tr>
            
        <!-- ���顼��� -->
            <tr align='center'>
                <td colspan='1'>
                    ���顼���
                </td>
                <td colspan='1'>
                    <input type='checkbox' name='err_check0' id='c0' <?php if($e_check0) echo " checked" ?> ><label for='c0'>�ʤ�</label>
                    <input type='checkbox' name='err_check1' id='c1' <?php if($e_check1) echo " checked" ?> ><label for='c1'>��л���</label>
                    <input type='checkbox' name='err_check2' id='c2' <?php if($e_check2) echo " checked" ?> ><label for='c2'>�º����</label>
                    <input type='checkbox' name='err_check3' id='c3' <?php if($e_check3) echo " checked" ?> ><label for='c3'>30ʬ�᤮</label>
                </td>
            </tr>
            
            <tr align='center'>
                <td colspan='2'>
                <input type='submit' name='quiry_exec'  value='�¹�' onClick='return QuiryExec();'>��
                <input type='button' name='quiry_reset' value='�ꥻ�å�' onClick='location.replace("<?php echo $menu->out_self(), '?showMenu=Quiry' ?>");'>&emsp;
                </td>
            </tr>

        </table>
    </td></tr>
    </table> <!----------------- ���ߡ�End --------------------->
</form>
<BR>�� �ɣӣϻ�̳�ɤϡ���̳�ݤ˴ޤޤ�Ƥ��ޤ���

</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
