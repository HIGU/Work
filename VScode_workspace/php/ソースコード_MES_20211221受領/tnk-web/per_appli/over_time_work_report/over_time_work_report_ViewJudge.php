<?php
////////////////////////////////////////////////////////////////////////////////
// ����ֳ���ȿ���ʾ�ǧ��                                                   //
//                                                    MVC View �� �ꥹ��ɽ��  //
// Copyright (C) 2021-2021 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2021/10/20 Created over_time_work_report_ViewJudge.php                     //
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

<body onLoad=''>

<center>
    <?= $menu->out_title_border() ?>

<!-- �Уģƥե�����򳫤� -->
    <div class='pt10' align='center'>
    <BR>�������ˡ��ʬ����ʤ���硢<a href="download_file.php/����ֳ���ȿ���_��ǧ_�ޥ˥奢��.pdf">����ֳ���ȿ���ʾ�ǧ�˥ޥ˥奢��</a> �򻲹ͤˤ��Ʋ�������<BR>
    </div>
<!-- TEST Start.-->
    <?php if($debug) { ?>
    <div class='pt9' align='left'><font color='red'>������ �������顢�ƥ��Ȥΰ�ɽ��  ������</font></div>
    ���ڥƥ��ȡ���ǧ�ԡ����ء� ����Ĺ��
    <input type='button' style='<?php if($login_uid=="011061") echo "background-color:yellow"; ?>' value='011061' onClick='CangeUID(this.value, "form_judge");'>��
    ������Ĺ��
    <input type='button' style='<?php if($login_uid=="012394") echo "background-color:yellow"; ?>' value='012394' onClick='CangeUID(this.value, "form_judge");'>��
    ��Ĺ��
    <input type='button' style='<?php if($login_uid=="017850") echo "background-color:yellow"; ?>' value='017850' onClick='CangeUID(this.value, "form_judge");'>��
    <input type='button' style='<?php if($login_uid=="012980") echo "background-color:yellow"; ?>' value='012980' onClick='CangeUID(this.value, "form_judge");'>��
    <input type='button' style='<?php if($login_uid=="016713") echo "background-color:yellow"; ?>' value='016713' onClick='CangeUID(this.value, "form_judge");'>
    <BR><BR>
    ��Ĺ��
    <input type='button' style='<?php if($login_uid=="300055") echo "background-color:yellow"; ?>' value='300055' onClick='CangeUID(this.value, "form_judge");'>��
    <input type='button' style='<?php if($login_uid=="300349") echo "background-color:yellow"; ?>' value='300349' onClick='CangeUID(this.value, "form_judge");'>��
    <input type='button' style='<?php if($login_uid=="300098") echo "background-color:yellow"; ?>' value='300098' onClick='CangeUID(this.value, "form_judge");'>��
    <input type='button' style='<?php if($login_uid=="014524") echo "background-color:yellow"; ?>' value='014524' onClick='CangeUID(this.value, "form_judge");'>��
    <input type='button' style='<?php if($login_uid=="018040") echo "background-color:yellow"; ?>' value='018040' onClick='CangeUID(this.value, "form_judge");'>��
    <input type='button' style='<?php if($login_uid=="015202") echo "background-color:yellow"; ?>' value='015202' onClick='CangeUID(this.value, "form_judge");'>��
    <input type='button' style='<?php if($login_uid=="016080") echo "background-color:yellow"; ?>' value='016080' onClick='CangeUID(this.value, "form_judge");'>��
    <input type='button' style='<?php if($login_uid=="017507") echo "background-color:yellow"; ?>' value='017507' onClick='CangeUID(this.value, "form_judge");'>��
    <input type='button' style='<?php if($login_uid=="017728") echo "background-color:yellow"; ?>' value='017728' onClick='CangeUID(this.value, "form_judge");'>��
    <BR><div class='pt9' align='left'><font color='red'>������ �����ޤǡ��ƥ��Ȥΰ�ɽ��  ������</font></div>
    <?php } ?>
<!-- TEST End. -->
    <BR>
<form name='form_judge' method='post' action="<?php echo $menu->out_self() . '?showMenu=Judge' ?>" onSubmit='return ;'>
<!-- TEST Start.-->
    <input type='hidden' name='login_uid' value="<?php echo $login_uid; ?>">
<!-- TEST End. -->
    <input type='hidden' name='admit' id='id_admit' value=''>

    <table class='pt10' border="1" cellspacing="0">
    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table width='100%' class='winbox_field' bgcolor='#D8D8D8' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr> <!-- ����ץ���� -->
                <td nowrap class='winbox' style='background-color:yellow; color:blue;' colspan='1' align='center'>
                    <div class='caption_font'>�� �� �� ��</div>
                </td>
                <td nowrap class='winbox' style='background-color:yellow; color:blue;' colspan='1' align='center'>
                    <div class='caption_font'>�Ķȷ�����</div>
                </td>
            </tr>

            <tr align='center'> <!-- ������� -->
                <td nowrap>
                    <input type='radio' name='select_radio' id='1' <?php if($select==1) echo " checked"; ?> onClick='AdmitDispSwitch();' value='1'><label for='1'>̤��ǧ</label>��
<!-- -->
                    <?php if( ($pos_no == 3) && $absence_ka && $absence_bu ) { ?>
                    <input type='radio' name='select_radio' id='2' <?php if($select==2) echo " checked"; ?> onClick='AdmitDispSwitch();' value='2'><label for='2'>��Ĺ����Ĺ�Ժ�̤��ǧ</label>
                    <?php } else if( ($pos_no == 2) && $absence_ka ) { ?>
                    <input type='radio' name='select_radio' id='2' <?php if($select==2) echo " checked"; ?> onClick='AdmitDispSwitch();' value='2'><label for='2'>��Ĺ�Ժ�̤��ǧ</label>
                    <?php } else if( $absence_bu ) { ?>
                    <input type='radio' name='select_radio' id='2' <?php if($select==2) echo " checked"; ?> onClick='AdmitDispSwitch();' value='2'><label for='2'>��Ĺ�Ժ�̤��ǧ</label>
                    <?php } ?>
<!-- -->
                </td>
<!-- --
                <td>
                    <input type='radio' name='select_radio' id='2' <?php if($select==2) echo " checked"; ?> onClick='AdmitDispSwitch();' value='2'><label for='2'>��Ǥ��̤��ǧ</label>
                </td>
<!-- -->
                <td nowrap>
                    <input type='radio' name='select_radio' id='3' <?php if($select==3) echo " checked"; ?> onClick='AdmitDispSwitch();' value='3'><label for='3'>̤��ǧ</label><BR>
                </td>
<!-- --
                <td>
                    <input type='radio' name='select_radio' id='4' <?php if($select==4) echo " checked"; ?> onClick='AdmitDispSwitch();' value='4'><label for='4'>��Ĺ̤��ǧ</label>
                </td>
<!-- -->
            </tr>
        </table>
    </td></tr>
    </table> <!----------------- ���ߡ�End --------------------->

<!-- �ơ����ա�����˻��ֳ���ȿ����ɽ������ -->
    <input type='hidden' name='select' id='id_select' value='<?php echo $select;?>'>
    <input type='hidden' name='column' id='id_column' value='<?php echo $column;?>'>
    <input type='hidden' name='posts' id='id_posts' value='<?php echo $pos_na;?>'>
    <input type='hidden' name='rows_max' id='id_rows_max' value='<?php echo $rows;?>'>

    <?php for($i=0; $i<$rows; $i++) { ?>
        <?php
        $date   = $res[$i][0];  // �����
        $deploy = $res[$i][1];  // ����̾
        $now    = date('Ymd');  // ����ǯ����
        if( $pos_no > 1 ) { // ��Ĺ������Ĺ�ξ�� �Ժ߼ԥ����å�
            switch ($pos_no) {
                case 3:   // ����Ĺ�ʤ���Ĺ�ʲ�Ĺ��ޤ�ˤνжг�ǧ
                    $absence_bu = $model->IsAbsence($now, $model->getButyouUID($deploy));
                case 2:   // ��Ĺ�ʤ��Ĺ�νжг�ǧ
                    $absence_ka = $model->IsAbsence($now, $model->getKatyouUID($deploy));
                    break;
            }
        }
        $where4 = "date='$date' AND deploy='$deploy'";
        // ������桼������̤��ǧ�ꥹ�Ȥ����
        // date='xxxx-xx-xx' AND deploy='xxx��' AND xx_ad_xx='m' ...
//        $where5 = $where4 . " AND " . $where . " AND " . "(yo_ad_rt!='-1' OR yo_ad_rt IS NULL)";
        $where5 = $where4 . " AND " . $where;
        if( ($rows_2 = $model->GetReport($where5, $res_2)) <=0 ) continue;

        /* ��ǧ���������̤��ǧ�ԤΥ롼�ȡ� -------------------------------> */
        $def_flag = '----';
        $ad_info  = array($def_flag, $def_flag, $def_flag, $def_flag, $def_flag, $def_flag);
        
        for( $t=0; $t<$rows_2; $t++ ) {
            if( $ad_info[0] == $def_flag && $res_2[$t][11] != "" ) $ad_info[0] = $model->GetAdmitInfo($res_2[$t][11]);
            if( $ad_info[1] == $def_flag && $res_2[$t][12] != "" ) $ad_info[1] = $model->GetAdmitInfo($res_2[$t][12]);
            if( $ad_info[2] == $def_flag && $res_2[$t][13] != "" ) $ad_info[2] = $model->GetAdmitInfo($res_2[$t][13]);
            if( $ad_info[3] == $def_flag && $res_2[$t][24] != "" ) $ad_info[3] = $model->GetAdmitInfo($res_2[$t][24]);
            if( $ad_info[4] == $def_flag && $res_2[$t][25] != "" ) $ad_info[4] = $model->GetAdmitInfo($res_2[$t][25]);
            if( $ad_info[5] == $def_flag && $res_2[$t][26] != "" ) $ad_info[5] = $model->GetAdmitInfo($res_2[$t][26]);
            
            if( $select==2 && $absence_bu ) $ad_info[1] = "<font style='color:red;'>�Ժ�</font>"; // ��Ĺ�Ժ�
            if( $select==2 && $absence_ka ) $ad_info[0] = "<font style='color:red;'>�Ժ�</font>"; // ��Ĺ�Ժ�
        }
        /* <---------------------------------------------------------------- */

        // ���˾�ǧ���Ƥ���ͤ�ɽ������١������ѹ������٥ǡ�����������롣
        $where5 = $where4 . " AND " . $where0;
        if( ($rows_2 = $model->GetReport($where5, $res_2)) <=0 ) continue;

        // ����Ĺ�����ȼ���
        $comment = array('','');
        for( $t=0; $t<$rows_2; $t++ ) {
            if( $comment[0] == '' && $res_2[$t][14] != "" ) $comment[0] = $res_2[$t][14];
            if( $comment[1] == '' && $res_2[$t][15] != "" ) $comment[1] = $res_2[$t][15];
        }

        $holiday = $model->IsHoliday($date);
        if( $holiday ) {
            $caption_color   = 'background-color:red; color:white;';    // �ٽФϡ��ط� �֡�ʸ�� ��
            $font_main_color = 'color:red;'; // �ٽФϡ�ʸ��������
        } else {
            $caption_color   = 'background-color:yellow; color:blue;';  // �̾�ϡ��ط� ����ʸ�� ��
            $font_main_color = 'color:black;'; // �̾�ϡ�ʸ�������
        }
        $capcion = '�������' . $model->getTargetDateDay($date, 'on') . ' ����̾��' . $deploy;
        $menu->set_caption($capcion);
        ?>
        <input type='hidden' name='w_date<?php echo $i; ?>' value='<?php echo $date;?>'>
        <input type='hidden' name='deploy<?php echo $i; ?>' value='<?php echo $deploy;?>'>

        <BR>
        <table class='pt10' border="1" cellspacing="0">
        <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
            <table width='100%' class='winbox_field' bgcolor='#D8D8D8' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr> <!-- ����ץ���� -->
                    <td class='winbox' style='<?php echo $caption_color; ?>' colspan='8' align='center'>
                        <div class='caption_font'><?php echo $menu->out_caption(), "\n"?></div>
                    </td>
                </tr>

                <tr> <!-- ����ץ���� -->
                    <td nowrap align='center' colspan='4'>��������</td>
                    <td nowrap align='center' colspan='4'>�Ķȷ�����</td>
                </tr>

                <tr> <!-- �ƹ���̾ -->
                    <td nowrap align='center'>�ᡡ̾</td>
                    <td nowrap align='center'>ͽ�����</td>
                    <td nowrap align='center'>�Ķȼ»���ͳ</td>
                    <td nowrap align='center'>����</td>
                    
                    <td nowrap align='center'>�ºݺ�Ȼ���</td>
                    <td nowrap align='center'>�»ܶ�̳����</td>
                    <td nowrap align='center'>����</td>
                </tr>

                <input type='hidden' name='rows<?php echo $i; ?>' value='<?php echo $rows_2;?>'>
                <?php for($r=0; $r<$rows_2; $r++) { ?>
                    <?php
                    $font_style = $font_main_color; // ɸ�५�顼
                    if( $select == 1 || $select == 2 ) {
                        $index = 10;    // yo_ad_st
                        $s_time = "{$res_2[$r][4]}:{$res_2[$r][5]}";
                        $e_time = "{$res_2[$r][6]}:{$res_2[$r][7]}";
                    } else {
                        $index = 23;    // ji_ad_st
                        $s_time = "{$res_2[$r][16]}:{$res_2[$r][17]}";
                        $e_time = "{$res_2[$r][18]}:{$res_2[$r][19]}";
                    }
                    // ��ǧ�Ԥ˴ط��ʤ������ϡ����졼������
                    $select_disa = ''; // ��ǧ�������ͭ����''���ػߡ�' disabled'��
                    if( $select != 2 ) {
                        if( $res_2[$r][$index] != ($pos_no-1) || $res_2[$r][$index + $pos_no] != 'm' ) {
                            $font_style = 'color:DarkGray;';
                        } else {
                            echo "<input type='hidden' name='up{$i}_{$r}'  value='on'>";

                            switch ($pos_no) {
                                case 3:     // ��ǧ�� ����Ĺ�ʤ�
                                    if( $comment[1] ) break; // ��Ĺ�����Ȥ���
                                    if( ! $model->IsButyouUID($res_2[$r][3]) ) break; // ������ ��Ĺ �ʳ�
                                    if( $holiday || (strtotime($e_time) - strtotime($s_time)) > 3600 ) $select_disa = ' disabled'; // ��ǧ �ػ�
                                    break;
                                case 2:     // ��ǧ�� ��Ĺ�ʤ�
                                    if( $comment[0] ) break; // ��Ĺ�����Ȥ���
                                    if( ! $model->IsKatyouUID($res_2[$r][3]) ) break; // ������ ��Ĺ �ʳ�
                                    if( $holiday || (strtotime($e_time) - strtotime($s_time)) > 3600 ) $select_disa = ' disabled'; // ��ǧ �ػ�
                                    break;
                                default:    // 
                                    break;
                            }

                        }
                    } else {    // �Ժ�̤��ǧ�����
                        //  �롼�� �� ���� ���� ���� == ��ǧ�Ԥ��� || (��Ĺ�Ժ� ���� ��Ĺ��ǧ == 'm')
                        if( $res_2[$r][$index-1] > $res_2[$r][$index] && $res_2[$r][$index] == ($pos_no-2)  || ($absence_ka && $res_2[$r][$index+1] == 'm')) {
                            switch ($pos_no) {
                                case 3:   // ����Ĺ�ʤ���Ĺ�ʲ�Ĺ��ޤ�ˤνжг�ǧ
                                    if( $absence_bu ) {
                                        if( $res_2[$r][$index+2] != 's' ) {
                                            echo "<input type='hidden' name='absence_bu{$i}_{$r}'  value='on'>";
                                        }
                                        if( $absence_ka && $res_2[$r][$index+1] != '' && $res_2[$r][$index+1] != 's') {
                                            echo "<input type='hidden' name='absence_ka{$i}_{$r}'  value='on'>";
                                        }
                                    }
                                    break;
                                case 2:   // ��Ĺ�ʤ��Ĺ�νжг�ǧ
                                    if( $absence_ka && $res_2[$r][$index+1] != 's' ) {
                                        echo "<input type='hidden' name='absence_ka{$i}_{$r}'  value='on'>";
                                    }
                                    break;
                            }
                            echo "<input type='hidden' name='up{$i}_{$r}' value='on'>";
                        } else {
                            $font_style = 'color:DarkGray;';
                        }
                    }
                    $uid = $res_2[$r][3];
                    $yo_root = $res_2[$r][9];
                    $ji_root = $res_2[$r][22];
                    ?>
                    <input type='hidden' name='uid<?php echo $i . '_' . $r; ?>'  value='<?php echo $uid;?>'>
                    <input type='hidden' name='yo_root<?php echo $i . '_' . $r; ?>' value='<?php echo $yo_root;?>'>
                    <input type='hidden' name='ji_root<?php echo $i . '_' . $r; ?>' value='<?php echo $ji_root;?>'>
                    <tr style='<?php echo $font_style; ?>'><!-- ���ֳ���ȼԾ��� -->
                        <td nowrap><?php echo $model->getName($uid); ?></td>
                        <?php if( $res_2[$r][4] ) { ?>
                            <td nowrap align='center'><?php echo $res_2[$r][4] . ':' . $res_2[$r][5] . '��' . $res_2[$r][6] . ':' . $res_2[$r][7] ?></td>
                            <td nowrap align='center'><?php echo $res_2[$r][8] ?></td>
                            <td nowrap align='center'><?php echo $model->getAdmitStatus($yo_root, $res_2[$r][10]); ?></td>
                        <?php } else { ?>
                            <?php if($font_style == $font_main_color) { ?>
                            <td nowrap align='center' style='background-color:red; color:white;'>�� �� �� ��</td>
                            <?php } else { ?>
                            <td nowrap align='center' style='<?php $font_style ?>'>�� �� �� ��</td>
                            <?php } ?>
                            <td nowrap align='center'>--------</td> <!-- ���� -->
                            <td nowrap align='center'>----</td> <!-- ���� -->
                        <?php } ?>
                        <?php if( $res_2[$r][16] ) { ?>
                            <?php if($res_2[$r][16]==$res_2[$r][18] && $res_2[$r][17]==$res_2[$r][19]) { ?>
                                <?php if($font_style == $font_main_color) { ?>
                                <td nowrap align='center' style='background-color:red; color:white;'>�Ķ� ����󥻥�</td>
                                <?php } else { ?>
                                <td nowrap align='center' style='<?php $font_style ?>'>�Ķ� ����󥻥�</td>
                                <?php } ?>
                            <?php } else { ?>
                                <td nowrap align='center'><?php echo $res_2[$r][16] . ':' . $res_2[$r][17] . '��' . $res_2[$r][18] . ':' . $res_2[$r][19] ?></td>
                            <?php } ?>
                                <td nowrap align='center'><?php if($res_2[$r][20]) echo $res_2[$r][20]; else echo "��"; ?></td>
                            <td nowrap align='center'><?php echo $model->getAdmitStatus($ji_root, $res_2[$r][23]); ?></td>
                        <?php } else { ?>
                            <td nowrap align='center'>��</td> <!-- ���� -->
                            <td nowrap align='center'>��</td> <!-- ���� -->
                            <td nowrap align='center'>��</td> <!-- ���� -->
                        <?php } ?>
                    </tr>
                <?php } ?>

                <tr> <!-- ��������ǧ(ͽ��)�������ȡ���ǧ(����) -->
                    <td nowrap colspan='2'>
                        <p class='pt9'>
                        ���Ĺ��ǧ��<BR>
                        ����С��� 1���֤ޤǤλĶ�<BR>
                        ����Ĺ��ǧ�䡡<��Ĺ��������><BR>
                        ����С��� 1���֤�Ķ����Ķ�<BR>
                        �㹩��Ĺ��ǧ�䡡<��Ĺ����Ĺ��������><BR>
                        ���塢�� �ĶȤ���ӵ����ж�<BR>
                        </p>
                    </td>

                    <td colspan='2' align='center'>
                        <?php if( $select == 1 || $select == 2 ) { ?>
                        <?php if( $select_disa ) { ?>
                        <font size=2>��<?php if($pos_no==2) echo "��Ĺ"; else echo "��Ĺ"; ?>�����ȡ�̤�۾�ǧ�Բ�</font><BR>
                        <input type='radio' name='radio_yo<?php echo $i; ?>' id='id_a_radio<?php echo $i; ?>' onClick="AdmitSelect(this, 's', <?php echo $i; ?>);" value="" <?php echo $select_disa; ?>><font style='color:DarkGray;'>��ǧ</font>
                        <?php } else { ?>
                        <input type='radio' name='radio_yo<?php echo $i; ?>' id='id_a_radio<?php echo $i; ?>' onClick="AdmitSelect(this, 's', <?php echo $i; ?>);" value=""><label for='id_a_radio<?php echo $i; ?>'>��ǧ</label>
                        <?php } ?>
                        <input type='radio' name='radio_yo<?php echo $i; ?>' id='id_b_radio<?php echo $i; ?>' onClick="AdmitSelect(this, 'h', <?php echo $i; ?>);" value=""><label for='id_b_radio<?php echo $i; ?>'>��ǧ</label>
                        <BR><div align='right'>
                        <textarea name='yo_ng_comme<?php echo $i; ?>' id='id_yo_ng_comme<?php echo $i; ?>' rows='2' cols='22' value='' disabled>��ͳ��</textarea>
                        </div>
                        <?php } ?>
                        <table class='pt10' border="1" cellspacing="0" align='right'>
                        <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
                            <table width='100%' class='winbox_field' bgcolor='#D8D8D8' align='center' border='1' cellspacing='0' cellpadding='3'>
                                <tr>
                                    <td nowrap align='center'>����Ĺ</td>
                                    <td nowrap align='center'>����Ĺ</td>
                                    <td nowrap align='center'>�ݡ�Ĺ</td>
                                </tr>
                                <tr>
                                    <td align='center'><?php echo $ad_info[2]; ?></td>
                                    <td align='center'><?php echo $ad_info[1]; ?></td>
                                    <td align='center'><?php echo $ad_info[0]; ?></td>
                                </tr>
                            </table>
                        </td></tr>
                        </table> <!----------------- ���ߡ�End --------------------->
                    </td>

                    <td class='pt9' valign='top'>
                        <?php
                        echo "��Ĺ ������ ���ĶȤ�ɬ������ܺ٤�<BR>";
                        if( $pos_no == 1 && ($ad_info[1] == '̤' || $ad_info[4] == '̤') ) {
                            $readonly="";
                            $style = "style='$font_main_color'";
                        } else {
                            $readonly = "readonly";
                            $style = "style='$font_main_color background-color:#D8D8D8;'";
                        }
                        echo "<textarea name='comment_ka$i' id='id_comment_ka$i' rows='3' cols='30' $style value='$comment[0]' $readonly>$comment[0]</textarea>";

                        echo "<BR>��Ĺ ������ ���ĶȤ�ɬ������ܺ٤�<BR>";
                        if( $pos_no == 2 && ($ad_info[2] == '̤' || $ad_info[5] == '̤' ) ) {
                            $readonly="";
                            $style = "style='$font_main_color'";
                        } else {
                            $readonly = "readonly";
                            $style = "style='$font_main_color background-color:#D8D8D8;'";
                        }
                        echo "<textarea name='comment_bu$i' id='id_comment_bu$i' rows='3' cols='30' $style value='$comment[1]' $readonly>$comment[1]</textarea>";
                        ?>
                    </td>

                    <td colspan='3' align='center'>
                        <?php if( $select == 3 ) { ?>
                        <?php if( $select_disa ) { ?>
                        <font size=2>��<?php if($pos_no==2) echo "��Ĺ"; else echo "��Ĺ"; ?>�����ȡ�̤�۾�ǧ�Բ�</font><BR>
                        <input type='radio' name='radio_ji<?php echo $i; ?>' id='id_c_radio<?php echo $i; ?>' onClick="AdmitSelect(this, 's', <?php echo $i; ?>);" value="" <?php echo $select_disa; ?>><font style='color:DarkGray;'>��ǧ</font>
                        <?php } else { ?>
                        <input type='radio' name='radio_ji<?php echo $i; ?>' id='id_c_radio<?php echo $i; ?>' onClick="AdmitSelect(this, 's', <?php echo $i; ?>);" value=""><label for='id_c_radio<?php echo $i; ?>'>��ǧ</label>
                        <?php } ?>
                        <input type='radio' name='radio_ji<?php echo $i; ?>' id='id_d_radio<?php echo $i; ?>' onClick="AdmitSelect(this, 'h', <?php echo $i; ?>);" value=""><label for='id_d_radio<?php echo $i; ?>'>��ǧ</label>
                        <BR><div align='right'>
                        <textarea name='ji_ng_comme<?php echo $i; ?>' id='id_ji_ng_comme<?php echo $i; ?>' rows='2' cols='22' value='' disabled>��ͳ��</textarea>
                        </div>
                        <?php } else { echo "<BR>"; } ?>
                        <table class='pt9' border="1" cellspacing="0" align='right'>
                        <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
                            <table width='100%' class='winbox_field' bgcolor='#D8D8D8' align='center' border='1' cellspacing='0' cellpadding='3'>
                                <tr>
                                    <td nowrap align='center'>����Ĺ</td>
                                    <td nowrap align='center'>����Ĺ</td>
                                    <td nowrap align='center'>�ݡ�Ĺ</td>
                                </tr>
                                <tr>
                                    <td align='center'><?php echo $ad_info[5]; ?></td>
                                    <td align='center'><?php echo $ad_info[4]; ?></td>
                                    <td align='center'><?php echo $ad_info[3]; ?></td>
                                </tr>
                            </table>
                        </td></tr>
                        </table> <!----------------- ���ߡ�End --------------------->
                    </td>

                </tr>
            </table>
        </td></tr>
        </table> <!----------------- ���ߡ�End --------------------->
    <?php } // for($i=0; $i<$rows; $i++) End. ?>

<!--  --><BR>
    <?php if( $rows <= 0 || $rows_2 <= 0 ) { ?>
        ̤��ǧ�Υǡ����Ϥ���ޤ���
    <?php } else { ?>
        <input type='button' name='admit_all' id='' value='��ǧ�������' onClick='AdmitAllSelect(this, <?php echo $rows ?>);'>��
        <input type='submit' name='admit_ok'  id='' value='�¹�' onClick='return AdmitExec();'>��
        <input type='button' name='admit_no'  id='' value='����󥻥�' onClick='location.replace("<?php echo $menu->out_self(), '?showMenu=Judge'; ?>");'>
        <BR>��
    <?php } ?>
</form>

</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
