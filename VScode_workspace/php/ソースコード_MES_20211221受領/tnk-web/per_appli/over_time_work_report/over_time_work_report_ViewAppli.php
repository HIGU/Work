<?php
////////////////////////////////////////////////////////////////////////////////
// ����ֳ���ȿ���ʿ�����                                                   //
//                                                    MVC View �� �ꥹ��ɽ��  //
// Copyright (C) 2021-2021 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2021/10/20 Created over_time_work_report_ViewAppli.php                     //
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

<body onLoad='Init()'>

<center>
<?= $menu->out_title_border() ?>

<!-- �Уģƥե�����򳫤� -->
    <div class='pt10' align='center'>
    <BR>�������ˡ��ʬ����ʤ���硢<a href="download_file.php/����ֳ���ȿ���_����_�ޥ˥奢��.pdf">����ֳ���ȿ�������ϡ˥ޥ˥奢��</a> �򻲹ͤˤ��Ʋ�������<BR>
    </div>
<!-- TEST Start.-->
    <?php if($debug) { ?>
    <div class='pt9' align='left'><font color='red'>������ �������顢�ƥ��Ȥΰ�ɽ��  ������</font></div>
    �����ߤ�UID��<?php echo $login_uid; ?>���ڥƥ��� ���ء�
    ALL��
    <input type='button' style='<?php if($login_uid=="011061") echo "background-color:yellow"; ?>' value='011061' onClick='CangeUID(this.value, "form_appli");'>��
    ʣ���ݡ�
    <input type='button' style='<?php if($login_uid=="012394") echo "background-color:yellow"; ?>' value='012394' onClick='CangeUID(this.value, "form_appli");'>��
    <input type='button' style='<?php if($login_uid=="017850") echo "background-color:yellow"; ?>' value='017850' onClick='CangeUID(this.value, "form_appli");'>��
    <input type='button' style='<?php if($login_uid=="012980") echo "background-color:yellow"; ?>' value='012980' onClick='CangeUID(this.value, "form_appli");'>��
    <input type='button' style='<?php if($login_uid=="016713") echo "background-color:yellow"; ?>' value='016713' onClick='CangeUID(this.value, "form_appli");'>
    <BR><BR>
    �Ʋݡ�
    <input type='button' style='<?php if($login_uid=="300055") echo "background-color:yellow"; ?>' value='300055' onClick='CangeUID(this.value, "form_appli");'>��
    <input type='button' style='<?php if($login_uid=="300349") echo "background-color:yellow"; ?>' value='300349' onClick='CangeUID(this.value, "form_appli");'>��
    <input type='button' style='<?php if($login_uid=="300098") echo "background-color:yellow"; ?>' value='300098' onClick='CangeUID(this.value, "form_appli");'>��
    <input type='button' style='<?php if($login_uid=="014524") echo "background-color:yellow"; ?>' value='014524' onClick='CangeUID(this.value, "form_appli");'>��
    <input type='button' style='<?php if($login_uid=="018040") echo "background-color:yellow"; ?>' value='018040' onClick='CangeUID(this.value, "form_appli");'>��
    <input type='button' style='<?php if($login_uid=="015202") echo "background-color:yellow"; ?>' value='015202' onClick='CangeUID(this.value, "form_appli");'>��
    <input type='button' style='<?php if($login_uid=="016080") echo "background-color:yellow"; ?>' value='016080' onClick='CangeUID(this.value, "form_appli");'>��
    <input type='button' style='<?php if($login_uid=="017507") echo "background-color:yellow"; ?>' value='017507' onClick='CangeUID(this.value, "form_appli");'>��
    <input type='button' style='<?php if($login_uid=="017728") echo "background-color:yellow"; ?>' value='017728' onClick='CangeUID(this.value, "form_appli");'>��
    <BR><div class='pt9' align='left'><font color='red'>������ �����ޤǡ��ƥ��Ȥΰ�ɽ��  ������</font></div>
    <?php } ?>
<!-- TEST End. -->
    <BR>
<form name='form_appli' method='post' action='<?php echo $menu->out_self() ?>' onSubmit='return true;'>
<!-- TEST Start.-->
    <input type='hidden' name='login_uid' value="<?php echo $login_uid; ?>">
<!-- TEST End. -->
    <input type='hidden' name='showMenu' id='id_showMenu' value='Appli'>
    <input type='hidden' name='list_view' id='id_list_view' value='<?php echo $list_view; ?>'>
    <input type='hidden' name='appli' id='id_appli' value=''>
    
    <table class='pt10' border="1" cellspacing="0">
    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table width='100%' class='winbox_field' bgcolor='#D8D8D8' align='center' border='1' cellspacing='0' cellpadding='3'>
            <!-- ����ץ���� -->
            <tr>
                <td class='winbox' style='background-color:yellow; color:blue;' colspan='3' align='center'>
                    <div class='caption_font'><?php echo $menu->out_caption(), "\n"?></div>
                </td>
            </tr>

            <!-- ��ҥ��������ε��������javascript���ѿ��إ��åȤ��Ƥ�����-->
            <script> var holiday = '<?php echo $holiday; ?>';  SetHoliday(holiday);</script>

            <tr>
                <td nowrap>
                    <input type='button' name='before' id='id_before' value='��' onClick='setNextDate(this)'>
                    <?php
                    echo "���������";
                    if( $list_view != 'on' ) {
                        echo "<select name='ddlist_year' id='id_year' onclick='WorkDateCopy()'>";
                        $model->getSelectOptionDate($def_y-1, $def_y+1, $def_y);
                        echo "</select>ǯ";
                        echo "<select name='ddlist_month' id='id_month' onclick='WorkDateCopy()'>";
                        $model->getSelectOptionDate(1, 12, $def_m);
                        echo "</select>��";
                        echo "<select name='ddlist_day' id='id_day' onclick='WorkDateCopy()'>";
                        $model->getSelectOptionDate(1, 31, $def_d);
                        echo "</select>��";
                    } else {
                        echo $def_y . "ǯ��" . $def_m . "�" . $def_d . "��";
                        echo "<input type='hidden' name='ddlist_year' id='id_year' value='$def_y'>";
                        echo "<input type='hidden' name='ddlist_month' id='id_month' value='$def_m'>";
                        echo "<input type='hidden' name='ddlist_day' id='id_day' value='$def_d'>";
                    }
                    ?>
                    <font id='id_w_youbi'>(��)</font>
                    <input type='hidden' name='w_date' id='id_w_date' value="<?php echo $date; ?>">
                    <input type='button' name='after' id='id_after' value='��' onClick='setNextDate(this)'>
                </td>
                <td nowrap>
                    <?php
                    echo "������̾��";
                    if( $list_view != 'on' ) {
                        echo "<select name='ddlist_bumon' onChange='DDBumon()'>";
                            $model->setSelectOptionBumon($request);
                        echo "</select>";
                    } else {
                        echo $bumon;
                        echo "<input type='hidden' name='ddlist_bumon' value='$bumon'>";
                    }
                    ?>
                </td>
                <td nowrap align='center'>
                    <?php
                    if( $list_view != 'on' ) {
                        echo "<input type='button' name='read' id='id_read' value='�ɤ߹���' onClick='SetViewON()'>";
                    } else {
                        echo "<input type='button' name='cancel' id='id_cancel' value='����󥻥�' onClick='SetViewOFF()'>";
                    }
                    ?>
                </td>
            </tr>

            <tr>
                <td nowrap colspan='3'>
                    <p class='pt9'>
                    ���Ķ���ͳ����Ӽ»ܶ�̳���ƤϾܤ�����������������ϲ�Ĺ��ǧ��������ǡ�<font color='red'>���3����</font>������Ĺ��ͳ��̳�ݤޤ���Ф��Ʋ�������
                    </p>
                </td>
            </tr>

            <tr>
                <td nowrap colspan='2'>
                    <p class='pt10'>
                    ��ʿ���Υѡ��ȡ�����Ұ��� 17��15 �ޤǤ�<font color='red'>��Ĺ</font>�Ϥ��٤ơ���Ĺ��ǧ<BR>
                    ��17��30 �ʹߤ�<font color='red'>�Ķ�</font>�˴ؤ��Ƥϰʲ��δ���Ŭ��<BR>
                    ��� Ĺ ��ǧ�䡡��С��� 1���֤ޤǤλĶ�<BR>
                    ���� Ĺ ��ǧ�䡡��С��� 1���֤�Ķ����Ķȡ�<��Ĺ��������><BR>
                    �㹩��Ĺ��ǧ�䡡�塢�� �ĶȤ���ӵ����жС� ��<��Ĺ����Ĺ��������><BR>
                    </p>
                </td>
                <td nowrap align='center'>
                    <?php
                    if( $list_view != 'on' ) {
                        echo "<input type='button' value='�С�Ͽ' disabled='true'>";
                    } else {
                        echo "<input type='submit' name='commit' id='id_commit' value='�С�Ͽ' onClick='return IsUpDate();'>";
                    }
                    ?>
                </td>
            </tr>

            <?php if( $list_view == 'on' ) { ?>
            <tr>
                <td nowrap class='pt10' colspan='3'>
                �����Ƥ��ѹ������Ȥ���[��Ͽ]�ܥ���򥯥�å����ʤ����ѹ����Ƥ�<font style='color:red;'>��¸����ޤ���!!</font><BR>
                �ڻ��������Υ�ߥåȡۺ������<font style='color:red;'><?php echo $time_limit; ?></font>�ޤǡ������ä����ǽ��<font style='color:red;'><?php echo $time_limit; ?></font>�ʹߤϻĶȷ�����Τ߲�ǽ��<BR>
                ����Ф��̾�Ķ�ξ����<font style='color:red;'>ξ���Ԥ����</font>���̾�ĶȤλ��֤���ꤷ����л��֤���ͳ�����ơˤ����Ϥ��뤳�ȡ�<BR>
                �ڻĶȤ��ʤ��ä��Ȥ��ۻĶȷ�����γ��ϤȽ�λ�λ��֤�<font style='color:red;'>Ʊ���ˤ�����Ͽ���Ʋ�������</font><BR>
                <!-- �ޤꤿ����Ÿ���ܥ��� -->
                <div onclick="obj=document.getElementById('menu1').style; obj.display=(obj.display=='none')?'block':'none'; obj2=document.getElementById('id_menu');obj2.innerHTML=(obj.display=='none')?'���ƥܥ���������ʥ���å���Ÿ����':'���ƥܥ���������ʥ���å��ǽ̾���';">
                <a class='pt10b' id='id_menu' style="cursor:pointer;">���ƥܥ���������ʥ���å���Ÿ����</a>
                </div>
                <!--// �ޤꤿ����Ÿ���ܥ��� -->
                <!-- ������������ޤꤿ���� -->
                <div id="menu1" style="display:none;clear:both;font-size:10pt;font-weight:normal;">
                �ھ��֤γƥܥ���������<BR>
                ��[�ݡ�]�����֤���ͳ�����ơˤ��������롣<BR>
                �������������Ķȷ�����¦(������������)�ʤ�[���]���ڤ��ؤ�곫�ϤȽ�λ��Ʊ���֤򥻥åȤ��롣<BR>
                ��[��λ]�����ä����̤����ܤ��롣<BR>
                ��[����]�����ä����̤����ܤ��롣<BR>
                ��[��ǧ]���ܥ���θ��̡��äˤʤ���<font style='color:red;'>����ǧ���Ƥ����������١���Ͽ���Ʋ�������</font><BR>
                ��[���]�����֤����Ƥ��������롣<BR>
                �ڤ���¾�Υܥ���������<BR>
                ��[���ԡ��¹�]��������������(1��)�����ǡ����������������(ʣ����)������إ��ԡ����롣<BR>
                ��[��]�������ˤ�������å��ܥå����Υ����å��������դ��롢�ޤ��ϳ�����<BR>
                ��[->]�����������Υǡ�����Ķȷ�����إ��ԡ����롣��[all]��[->]�����Ƽ¹ԡ�<BR>
                <!--������ʬ���ޤꤿ���ޤ졢Ÿ���ܥ���򥯥�å����뤳�Ȥ�Ÿ�����ޤ���-->
                </div>
                <!--// �����ޤǤ��ޤꤿ���� -->
                </td>
            </tr>
            <?php } ?>
        </table>
    </td></tr> <!----------------- ���ߡ�End --------------------->
    </table>

<!-- �������鲼�ϡ������ꥹ�Ȥ�ɽ��������ʬ -->
<?php 
if( $list_view != 'on' ) {
    echo "<BR><div>2021ǯ12��10���ʶ��17��00 �� ���٥ǡ�����ꥻ�åȤ��ޤ���<BR><BR>���ꥻ�åȸ�ϡ�12��13���ʷ�ˤ�����Ѥ򳫻Ϥ��Ʋ�������</div>"
    ;   // ����ɽ�����ʤ���
} else {
    if( $view_data ) {  // �ɹ��ߥǡ�����¸���񤭹���������Ӥ��뤿��
        $fiels = count($field);
        echo "<input type='hidden' name='fiels' id='id_fiels' value='$fiels'>";
        for( $r=0; $r<$rows; $r++ ) {
            for( $f=0; $f<$fiels; $f++ ) {
                echo "<input type='hidden' name='res{$r}_{$f}' id='id_res{$r}_{$f}' value='{$res[$r][$f]}'>";
            }
        }
    }
?>
    <?php if( !$limit_over ) {  // ������ǽ���� ?>
    <font class='pt10'>���Ķȷ�������ϡ�<font style='color:red;'>�������<b>17��15</b>�ʹߤ�ɽ����</font>���������ξ�ǧ���Ѥ�Ǥ�������ϲ�ǽ��</font>
    <?php } else { ?>
    <font class='pt10'>���Ķȷ�����ϡ���ж����θ�����ˤϺѤޤ���褦�������ޤ��礦��</font>
    <?php } ?>
    <input type='hidden' name='rows' id='id_rows' value='<?php echo $rows ?>'>
    <input type='hidden' name='v_data' id='id_v_data' value='<?php echo $view_data ?>'>
    <BR>
    <table class='pt10' border="1" cellspacing="0">
    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table width='100%' class='winbox_field' bgcolor='#D8D8D8' align='center' border='1' cellspacing='0' cellpadding='3'>
<!-- ����ץ���� -->
            <?php if( $model->IsHoliday($date) ) { ?>
            <tr>
                <td class='winbox' style='background-color:red; color:white;' colspan='12' align='center'>
                    <div class='caption_font'>�ڵ����жС�</div>
                </td>
            </tr>
                <?php $style_color = 'color:red;'   // �ٽФϡ�ʸ�������� ?>
            <?php } else { ?>
                <?php $style_color = 'color:black;' // �̾�ϡ�ʸ������� ?>
            <?php } ?>

            <tr>
                <td class='winbox' style='background-color:yellow; color:blue;' colspan='6' align='center'>
                    <div class='caption_font'>��������</div>
                </td>
                <?php if( !$limit_over ) {  // ������ǽ���� ?>
                <td nowrap align='center' colspan='5' rowspan='2'>������֥��å�<BR><font class='pt10'>���ܥ��󥯥�å��ǻ��֥��åȲ�ǽ</font></td>
                <?php } ?>
                <?php if( $limit_over ) {  // ������ǽ���֤�᤮�Ƥ��� ?>
                <td class='winbox' style='background-color:yellow; color:blue;' align='center'>
                    <div class='caption_font'>copy</div>
                </td>
                <td class='winbox' style='background-color:yellow; color:blue;' colspan='4' align='center'>
                    <div class='caption_font'>�Ķȷ�����</div>
                </td>
                <?php } ?>
            </tr>

<!-- �оݼ԰���ɽ�� -->
            <tr>
                <td nowrap align='center'>��</td>
                <td nowrap align='center'><input type='button' name='exec' id='id_exec' value='���ԡ��¹�' onClick='RadioToCheck(<?php echo $rows; ?>)'></td>
                <td nowrap align='center'><input type='button' name='all_check' id='id_all_check' value='��,' onClick='AllCheck(this, <?php echo $rows; ?>)'></td>
                <td nowrap align='center'>����</td>
                <td nowrap align='center'>ͽ�����</td>
                <td nowrap align='center'>�Ķȼ»���ͳ</td>
                <?php if( $limit_over ) {   // ������ǽ���֤�᤮�Ƥ��� ?>
                <td nowrap align='center'><input type='button' name='all_copy' id='id_all_copy' value='all' onClick='YoteiToJissekiAll(<?php echo $rows; ?>)'></td>
                <td nowrap align='center'>����</td>
                <td nowrap align='center'>�ºݺ�Ȼ���</td>
                <td nowrap align='center'>�»ܶ�̳����</td>
                <?php } ?>
            </tr>
            <input type='hidden' name='cancel_uid' id='id_cancel_uid' value=''>
            <input type='hidden' name='cancel_uno' id='id_cancel_uno' value=''>
            <input type='hidden' name='type' id='id_type' value=''>

            <?php $comment = array('',''); // [0]��Ĺ�����ȡ�[1]��Ĺ������ ?>
            <?php for( $n=0; $n<$rows; $n++ ) { ?>
                <?php
                if( ! $limit_over ) {    // ������17:15���ޤ�
                    $yo_disa = '';          // ͭ��
                    $ji_disa = ' disabled'; // ̵���ˤ���
                } else {
                    $yo_disa = ' disabled'; // ̵���ˤ���
                    $ji_disa = '';          // ͭ��
                }
                $status = $model->getApplStatus('yo', $view_data, $res, $n);    // ͽ��ξ��ּ���
                $status_yo = $status;
                ?>
            <tr>
            <!-- �������� -->
                <!-- ���ԡ��� -->
                <td nowrap align='center'><input type='radio' name='radioNo' id='id_radio<?php echo $n; ?>' value='' onclick='RadioCheck(this, <?php echo $n; ?>)'></td>

                <!-- ��̾ -->
                <td nowrap>
                    <?php
                    if($view_data) {
                        $uno = $res[$n][2];
                        $uid = $res[$n][3]; // ����ֳ���ȿ������Ͽ�ǡ���
                        if( $comment[0] == "" && $res[$n][14] != "" ) $comment[0] = $res[$n][14];
                        if( $comment[1] == "" && $res[$n][15] != "" ) $comment[1] = $res[$n][15];
                    } else {
                        $uno = 0;
                        $uid = $res[$n][0]; // ��°����ΰ����ǡ���
                    }
                    echo $name = trim($model->getName($uid));
                    echo "<input type='hidden' name='uid$n' id='id_uid$n' value='$uid'>";
                    echo "<input type='hidden' name='simei$n' id='id_simei$n' value='$name'>";
                    ?>
                </td>

                <!-- ���ԡ��� -->
                <td nowrap align='center'>
                    <?php
                    if( $status == '�ݡ�' ) {
                        echo "<input type='checkbox' name='check$n' id='id_check$n' onclick='CheckFlag(this)'>";
                    } else {
                        echo "<input type='checkbox' name='check$n' id='id_check$n' onclick='CheckFlag(this)' disabled>";
                    }
                    ?>
                </td>

                <?php
                if($view_data && $res[$n][4]) {
                    $def_s_h = $res[$n][4]; $def_s_m = $res[$n][5]; // ���� �� ʬ ���å�
                    $def_e_h = $res[$n][6]; $def_e_m = $res[$n][7]; // ��λ �� ʬ ���å�
                    $content = $res[$n][8]; // ���� ���å�
                } else {
                    $def_s_h = -1; $def_s_m = -1;
                    $def_e_h = -1; $def_e_m = -1;
                    $content = '';
                }
                ?>

                <!-- �������� -->
                <td nowrap align='center'>
                    <?php
                    echo "<input type='button' id='1' $yo_disa value='$status' onClick='return ReportEdit(this, $n, $uid, $uno);'>";
                    if( $status != '�ݡ�' && $status != '��ǧ' ) {   // ������ְʳ�
                        $yo_disa = ' disabled'; // ̵���ˤ���
                    }
                    ?>
                </td>

                <!-- ͽ����� -->
                <td nowrap align='center'>
                    <select style='<?php echo $style_color; ?>' name='ddlist_y_s_h<?php echo $n; ?>' id='id_y_s_h<?php echo $n; ?>' <?php echo $yo_disa; ?>>
                        <?php $model->setSelectOptionTime(0, 23, $def_s_h); ?>
                    </select>
                    :
                    <select style='<?php echo $style_color; ?>' name='ddlist_y_s_m<?php echo $n; ?>' id='id_y_s_m<?php echo $n; ?>' <?php echo $yo_disa; ?>>
                        <?php $model->setSelectOptionTime(0, 59, $def_s_m); ?>
                    </select>
                    ��
                    <select style='<?php echo $style_color; ?>' name='ddlist_y_e_h<?php echo $n; ?>' id='id_y_e_h<?php echo $n; ?>' <?php echo $yo_disa; ?>>
                        <?php $model->setSelectOptionTime(0, 23, $def_e_h); ?>
                    </select>
                    :
                    <select style='<?php echo $style_color; ?>' name='ddlist_y_e_m<?php echo $n; ?>' id='id_y_e_m<?php echo $n; ?>' <?php echo $yo_disa; ?>>
                        <?php $model->setSelectOptionTime(0, 59, $def_e_m); ?>
                    </select>
                </td>

                <!-- �Ķȼ»���ͳ -->
                <td nowrap><input type='text' style='<?php echo $style_color; ?>' size='30' maxlength='64' name='z_j_r<?php echo $n; ?>' id='id_z_j_r<?php echo $n; ?>' value='<?php echo $content; ?>' <?php echo $yo_disa; ?>></td>

                <!-- [��Ĺ][�Ķ�]�ܥ��� -->
                <?php
                if( !$limit_over ) {// ������ǽ����
                    if( $model->IsHoliday($date) ) {// �����ж�
                        echo "<td><input type='button' id='20' $yo_disa value='������' onClick='setFixedTime(this, $n);'></td>";
                        echo "<td><input type='button' id='21' $yo_disa value='������' onClick='setFixedTime(this, $n);'></td>";
                        echo "<td><input type='button' id='22' $yo_disa value='������' onClick='setFixedTime(this, $n);'></td>";
                        echo "<td><input type='button' id='23' $yo_disa value='�Уͣ�' onClick='setFixedTime(this, $n);'></td>";
                        echo "<td><input type='button' id='24' $yo_disa value='�Уͣ�' onClick='setFixedTime(this, $n);'></td>";
                    } else {// ʿ��
                        echo "<td><input type='button' id='10' $yo_disa value='�䡡Ĺ' onClick='setFixedTime(this, $n);'></td>";
                        echo "<td><input type='button' id='11' $yo_disa value='��ģ�' onClick='setFixedTime(this, $n);'></td>";
                        echo "<td><input type='button' id='12' $yo_disa value='��ģ�' onClick='setFixedTime(this, $n);'></td>";
                        echo "<td><input type='button' id='13' $yo_disa value='�ģ���' onClick='setFixedTime(this, $n);'></td>";
                        echo "<td><input type='button' id='14' $yo_disa value='�ģ���' onClick='setFixedTime(this, $n);'></td>";
                    }
                }
                ?>

            <!-- �Ķȷ����� -->
                <?php if( $limit_over ) {   // ������ǽ���֤�᤮�Ƥ��� ?>
                <?
                if( $model->IsNoAdmit('yo', $date, $uid) ) {    // �����������ޤ�̤��ǧ�ξ�硢��̤����Ϥ����ʤ���
                    $ji_disa = ' disabled'; // ̵���ˤ���
                }
                $status = $model->getApplStatus('ji', $view_data, $res, $n);    // ���Ӥξ��ּ���
                ?>
                <!-- ���ԡ� -->
                <td nowrap align='center'><input type='button' name='copy<?php echo $n; ?>' id='id_copy<?php echo $n; ?>' value='->' onClick='YoteiToJisseki(this.id,<?php echo $n; ?>)' <?php if($status!='�ݡ�' || $ji_disa) echo ' disabled';?>></td>

                <!-- ��������� -->
                <td nowrap align='center'>
                    <?php
                    echo "<input type='button' id='2_$n' $ji_disa value='$status' onClick='return ReportEdit(this, $n, $uid, $uno);'>";
/**/
                    if( $status != '�ݡ�' && $status != '��ǧ' ) {   // ������ְʳ�
                        $ji_disa = ' disabled'; // ̵���ˤ���
                    }
/**/
                    ?>
                </td>

                <!-- �Ķȷ�� -->
                <?php
                if($view_data && $res[$n][16]) {
                    $def_s_h = $res[$n][16]; $def_s_m = $res[$n][17]; // ���� �� ʬ ���å�
                    $def_e_h = $res[$n][18]; $def_e_m = $res[$n][19]; // ��λ �� ʬ ���å�
                    $content = $res[$n][20]; $bikou = $res[$n][21]; // ���� ���� ���å�
                } else {
                    $def_s_h = -1; $def_s_m = -1;
                    $def_e_h = -1; $def_e_m = -1;
                    $content = ''; $bikou = '';
                }
                ?>

                <!-- �ºݺ�Ȼ��� -->
                <?php
                if( $status_yo == "��ǧ" || $status_yo == "����" || ($status_yo == "�ݡ�" && $view_data && $res[$n][9] ) ) {
                    $status_yo = $model->getAdmitStatus($res[$n][9], $res[$n][10]);
                ?>
                <td nowrap align='center'>�������� <?php echo $status_yo; ?></td>
                <?php
                } else {
                ?>
                <td nowrap align='center'>
                    <select style='<?php echo $style_color; ?>' name='ddlist_j_s_h<?php echo $n; ?>' id='id_j_s_h<?php echo $n; ?>' <?php echo $ji_disa; ?>>
                        <?php $model->setSelectOptionTime(0, 23, $def_s_h); ?>
                    </select>
                    :
                    <select style='<?php echo $style_color; ?>' name='ddlist_j_s_m<?php echo $n; ?>' id='id_j_s_m<?php echo $n; ?>' <?php echo $ji_disa; ?>>
                        <?php $model->setSelectOptionTime(0, 59, $def_s_m); ?>
                    </select>
                    ��
                    <select style='<?php echo $style_color; ?>' name='ddlist_j_e_h<?php echo $n; ?>' id='id_j_e_h<?php echo $n; ?>' <?php echo $ji_disa; ?>>
                        <?php $model->setSelectOptionTime(0, 23, $def_e_h); ?>
                    </select>
                    :
                    <select style='<?php echo $style_color; ?>' name='ddlist_j_e_m<?php echo $n; ?>' id='id_j_e_m<?php echo $n; ?>' <?php echo $ji_disa; ?>>
                        <?php $model->setSelectOptionTime(0, 59, $def_e_m); ?>
                    </select>
                </td>
                <?php
                }
                ?>
                <!-- �»ܶ�̳���� -->
                <td nowrap><input style='<?php echo $style_color; ?>' type='text' size='30' maxlength='64' name='j_g_n<?php echo $n; ?>' id='id_j_g_n<?php echo $n; ?>' value='<?php echo $content; ?>'  <?php echo $ji_disa; ?>></td>

                <?php } // if($limit_over) End. ?>
            </tr>
            <?php
            } // for( $n=0; $n<$rows; $n++ ) End.
            ?>
            <tr><!-- �ɲù� -->
                <td nowrap align='center' colspan='3'><!-- ���ԡ��� --><!-- ��̾ --><!-- ���ԡ��� -->
                    �Ұ��ֹ桧<input type='text' size='8' maxlength='6' name='add_uid' id='id_add_uid'>
                </td>
                <td class='pt10' colspan='3'><!-- ���� --><!-- ͽ����� --><!-- �Ķȼ»���ͳ -->
                    <input type='submit' name='add_row' value='�ɲ�' onClick='return AppliAdd();'>
                    ��̾�����ʤ��͡��Ұ��ֹ�����Ϥ�[�ɲ�]�򥯥�å���
                </td>
                <?php if( $limit_over ) {  // ������ǽ���֤�᤮�Ƥ��� ?>
                <td>��</td><!-- ���ԡ� -->
                <td>��</td><!-- ���� -->
                <td>��</td><!-- �ºݺ�Ȼ��� -->
                <td>��</td><!-- �»ܶ�̳���� -->
                <?php } ?>
            </tr>
            <tr><!-- ��Ĺ ������ -->
                <td nowrap align='center' colspan='3'><!-- ���ԡ��� --><!-- ��̾ --><!-- ���ԡ��� -->
                    ��Ĺ ������
                </td>
                <td nowrap align='center' colspan='3'><!-- ���� --><!-- ͽ����� --><!-- �Ķȼ»���ͳ -->
                    <textarea name='comment_ka' id='id_comment_ka' rows='2' cols='50' style='<?php echo $style_color; ?>' value='<?php echo $comment[0]; ?>'><?php echo $comment[0]; ?></textarea>
                    <input type='submit' name='comme_ka' value='����' onClick='return UpComment();'>
                </td>
                <?php if( $limit_over ) {  // ������ǽ���֤�᤮�Ƥ��� ?>
                <td>��</td><!-- ���ԡ� -->
                <td>��</td><!-- ���� -->
                <td>��</td><!-- �ºݺ�Ȼ��� -->
                <td>��</td><!-- �»ܶ�̳���� -->
                <?php } ?>
            </tr>
            <tr><!-- ��Ĺ ������ -->
                <td nowrap align='center' colspan='3'><!-- ���ԡ��� --><!-- ��̾ --><!-- ���ԡ��� -->
                    ��Ĺ ������
                </td>
                <td nowrap align='center' colspan='3'><!-- ���� --><!-- ͽ����� --><!-- �Ķȼ»���ͳ -->
                    <textarea name='comment_bu' id='id_comment_bu' rows='2' cols='50' style='<?php echo $style_color; ?>' value='<?php echo $comment[1]; ?>'><?php echo $comment[1]; ?></textarea>
                    <input type='submit' name='comme_bu' value='����' onClick='return UpComment();'>
                </td>
                <?php if( $limit_over ) {  // ������ǽ���֤�᤮�Ƥ��� ?>
                <td>��</td><!-- ���ԡ� -->
                <td>��</td><!-- ���� -->
                <td>��</td><!-- �ºݺ�Ȼ��� -->
                <td>��</td><!-- �»ܶ�̳���� -->
                <?php } ?>
            </tr>
            <tr><!-- ���ʡ�[��Ͽ]�ܥ������ΰ� -->
                <td nowrap align='center' colspan='11'>
                    <?php echo "<b>��������� <input type='submit' align='center' name='commit' id='id_commit' value='�С�Ͽ' onClick='return IsUpDate();'> �Ǥ���Ͽ��ǽ��</b>"; ?>
                </td>
            </tr>
        </table>
    </tr></td> <!----------- ���ߡ�(�ǥ�������) ------------>
    </table>
    <BR>
<!-- TEST Start.-->
    <?php if($debug) { ?>
    <table class='pt10' border="1" cellspacing="0">
    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table width='100%' class='winbox_field' bgcolor='#D8D8D8' align='center' border='1' cellspacing='0' cellpadding='3'>
        <!-- ����ץ���� -->
            <tr>
                <td class='winbox' style='background-color:red; color:white;' colspan='12' align='center'>
                    <div class='caption_font'>��Ω�ײ����</div>
                </td>
            </tr>
        <!-- �����ǡ�������Ω�����ײ褫��ǡ����������������̾��ɽ���� -->
            <?php $max=12; $field=7; $ar12 = array("16S-A", "200-40SM", "20PM", "2S-304-NPT-E", "30PM", "350-6S", "3P-304-P", "3S-A", "3TSF-HP", "50SN", "6S-V-A", "CC225SH"); ?>
            <?php for($i=0; $i<$max; $i++) { ?>
                <?php if( $i % $field == 0 ) { ?>
            <tr nowrap>
                <?php } ?>
                <td>
                <input type='button' id='<?php echo "pl_$i"; ?>' value='��' onClick='PlanCopy(this, "<?php echo $ar12[$i]; ?>");'>
                <label for='<?php echo "pl_$i"; ?>'> <?php echo $ar12[$i]; ?></label>
                </td>
                <?php if( $i % $field == ($field-1) ) { ?>
            </tr>
                <?php } ?>
            <?php } ?>
            <?php
/**/
            for( ; ($i % $field) != 0; $i++) {
                echo "<td>��</td>";
            }
            echo "</tr>";
/**/
            ?>
            
        </table>
    </tr></td> <!----------- ���ߡ�(�ǥ�������) ------------>
    </table>
    <?php } ?>
<!-- TEST End.-->

    <?php //echo "<input type='submit' align='center' name='commit' id='id_commit' value='�С�Ͽ' onClick='return IsUpDate();'><b> ��������[��Ͽ]�ܥ����Ʊ����</b><BR>��"; ?>
<?php 
}   // if( $list_view != 'on' ) End.
?>

</form>


</center>
</body>
<BR><BR><?php echo $menu->out_alert_java(); ?>
</html>
