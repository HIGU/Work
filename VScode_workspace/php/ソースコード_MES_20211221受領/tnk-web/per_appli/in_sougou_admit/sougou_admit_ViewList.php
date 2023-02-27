<?php
////////////////////////////////////////////////////////////////////////////////
// ����ϡʾ�ǧ��                                                             //
//                                                    MVC View �� �ꥹ��ɽ��  //
// Copyright (C) 2020-2020 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2020/11/18 Created sougou_admit_ViewList.php                               //
// 2021/02/12 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////

////////////// �꥿���󥢥ɥ쥹����
$menu->set_RetUrl(PER_APPLI_MENU);                // �̾�ϻ��ꤹ��ɬ�פϤʤ�

// ǯ�����Υɥ�åץ�����ꥹ�Ⱥ���
function SelectOptionDate($start, $end, $def)
{
    for ($i = $start; $i <= $end ; $i++) {
        if ($i == $def) {
            echo "<option value='" . sprintf("%02d", $i) . "' selected>" . $i . "</option>";
        } else {
            echo "<option value='" . sprintf("%02d", $i) . "'>" . $i . "</option>";
        }
    }
}

// ����Υɥ�åץ�����ꥹ�Ⱥ���
function SelectOptionTime($start, $end, $def)
{

    for ($i = $start; $i <= $end ; $i++) {
        if ($i == $def) {
            echo "<option value='" . sprintf("%02s",$i) . "' selected>" . $i . "</option>";
        } else {
            if( $end == 23 ) {
                echo "<option value='" . sprintf("%02s",$i) . "'>" . $i . "</option>";
            }
            if( $end == 59 ) {
                if( $i == 0 || $i%5 == 0 ) {
                    echo "<option value='" . sprintf("%02s",$i) . "'>" . $i . "</option>";
                }
            }
        }
    }
}

// ������ɽ��
function DayDisplay($target_date, $model)
{
    $week = array(' (��)',' (��)',' (��)',' (��)',' (��)',' (��)',' (��)');

    $day_no = date('w', strtotime($target_date));
    if( $day_no == 0 ) {            // �������ʿ����֡�
        echo $target_date . "<font color='red'>$week[$day_no]</font>";
    } else if( $day_no == 6 ) {     // �������ʿ����ġ�
        echo $target_date . "<font color='blue'>$week[$day_no]</font>";
    } else if( $model->IsHoliday($target_date) ) {  // ��ҥ������������ʿ����֡�
        echo $target_date . "<font color='red'>$week[$day_no]</font>";
    } else {
        echo $target_date . $week[$day_no];         // ����¾ ʿ�� �Ķ����ʿ����ǥե���ȹ���
    }
}

// �����ꥹ�Ⱥ���
function SelectOptionAgent($model)
{
    $no_list = $model->getAgentList();
    if( !$no_list ) {
        echo "<option value='' selected>--------</option>";
        return;
    }
    $max = count($no_list);
    for ($i = 0; $i < $max ; $i++) {
        if ($i == 0) {
            echo "<option value='" . $no_list[$i][0] . "' selected>" . $model->getSyainName($no_list[$i][0]) . "</option>";
        } else {
            echo "<option value='" . $no_list[$i][0] . "'>" . $model->getSyainName($no_list[$i][0]) . "</option>";
        }
    }
}

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
<script type='text/javascript' language='JavaScript' src='sougou_admit.js'></script>

</head>

<body onLoad="Init(<?php echo $request->get('edit_no'); ?>)">
<center>
<?= $menu->out_title_border() ?>

<!-- ��ǧ�Ԥ����� ɽ�� --
<?php if( $model->getUid()=='300144' && ($rows_uid=$model->getAdmitUID($res_uid)) > 0 ) { ?>
<form name='form_send' method='post' action='<?php echo $menu->out_self(); ?>' onSubmit='return true;'>
    <input type='hidden' name='send_uid' id='id_send_uid' value=''>
</form>
    <table class='pt10' border="1" cellspacing="0">
    <tr><td>
        <table width='100%' class='winbox_field' bgcolor='#D8D8D8' align='center' border='1' cellspacing='0' cellpadding='3'>
            <caption style='background-color:DarkCyan; color:White;'><div class='caption_font'>��ǧ�Ԥ�����</div></caption>
            <tr style='background-color:yellow; color:blue;'>
                <td align='center'>�� ǧ ��</td>
                <td align='center'>���</td>
                <td align='center'>�̡���</td>
            </tr>
            <?php for($u=0; $u<$rows_uid; $u++) { ?>
                <?php $send_uid = $res_uid[$u][0]; ?>
            <tr>
                <td align='center'><?php echo $model->getSyainName($send_uid); ?></td>
                <td align='center'><?php echo $model->getAdmitCnt($send_uid); ?> ��</td>
                <td align='center'>
                    <?php if($model->getUid() != $send_uid ) { ?>
                        <?php echo "<input type='hidden' id='id_w_uid$u' value='$send_uid'>"; ?>
                        <input type='button' value='����' onClick='SetSendInfo(<?php echo $u; ?>)'>
                    <?php } ?>
                </td>
            </tr>
            <?php } ?>
        </table>
    </tr></td>
    </table>
<?php } ?>
<!-- ��ǧ�Ԥ����� ɽ�� -->

<?php $menu->set_caption('������ɬ�פʾ��������������򤷤Ʋ�������'); ?>

<?php if( $model->IsAdmit() == 0) { ?>
    <br>���ߡ�̤��ǧ������ϤϤ���ޤ���<br><br>
    ���̾̤��ǧ������Ϥ������硢<font color='red'>����10:00</font> �� <font color='red'>���12:45</font>�� <font color='red'>���15:00</font> �ηף����Τ餻�᡼����ۿ����Ƥ��ޤ���<br><br>
    ������<font color='red'>�ڻ�ޡ�</font>�ξ�硢������ �ޤ��� ��Ǥ�����ξ�ǧ�� ���Τ餻�᡼�뤬�ۿ������褦�ˤʤäƤ��ޤ���
<?php } else { ?>
<!-- �Уģƥե�����򳫤�-->
    <div class='pt10' align='center'>
    <br>����ǧ���̤λ�����ˡ��ʬ����ʤ����ϡ�<a href="download_file.php/����ϡʾ�ǧ��.pdf">����ϡʾ�ǧ��</a>�Ρڲ��̤������ۤ򤴳�ǧ����������<br><br>
    </div>
<!-- -->
    <br>̤��ǧ������ϰ���<br>
<?php
    $res = array(); 
    $indx = $model->getIndx();
    $rows = $model->getRows();
    $res = $model->getRes();
?>
<form name='form_admit' method='post' action='<?php echo $menu->out_self(); ?>'>
<input type='hidden' name='rows' value=<?php echo $rows; ?>>
<input type='hidden' name='indx' value=<?php echo $indx; ?>>

<input type='hidden' name='EditFlag'>

<?php for( $r=0; $r<$rows; $r++ ) { ?>
    <?php $posname = sprintf("res-%s[]", $r); ?>
    <?php for( $i=0; $i<$indx; $i++ ) { ?>
        <input type='hidden' name='<?php echo $posname; ?>' value='<?php echo $res[$r][$i]; ?>'>
    <?php } ?>
<?php } ?>

<?php
if( date('Ymd') > '20210630' ) {    // ����������ԲĤ��б�
    echo "<input type='hidden' name='suica_view' value='on'>";
    $suica_view = 'on';
} else {
    $suica_view = '';
}
?>

<?php
    for ( $r=0; $r<$rows; $r++) {
        $date                   = $res[$r][0];
        $uid                    = $res[$r][1];
        $start_date             = trim($res[$r][2]);
        $start_time             = $res[$r][3];
        $end_date               = trim($res[$r][4]);
        $end_time               = $res[$r][5];
        $content                = trim($res[$r][6]);
        $yukyu                  = $res[$r][7];
        $ticket01               = trim($res[$r][8]);
        $ticket02               = trim($res[$r][9]);
        $special                = trim($res[$r][10]);
        if( $special == '��ĤA' ) {
            $special = "��Ĥ���ܿͤ��뺧 5��(������1��)";
        } else if ( $special == '��ĤB' ) {
            $special = "��Ĥ�����졦�۶��ԡ��Ҥ���˴ 5��";
        } else if ( $special == '��ĤC' ) {
            $special = "��Ĥ���۶��Ԥ����졢�ܿͤ������졢����λ�˴ 3��";
        }
        $others                 = $res[$r][11];
        $place                  = $res[$r][12];
        $purpose                = $res[$r][13];
        $ticket01_set           = trim($res[$r][14]);
        $ticket02_set           = trim($res[$r][15]);
        $doukousya              = trim($res[$r][16]);
        $remarks                = $res[$r][17];
        $contact                = trim($res[$r][18]);
        $contact_other          = trim($res[$r][19]);
        $contact_tel            = $res[$r][20];
        $received_phone         = $res[$r][21];
        $received_phone_date    = $res[$r][22];
        $received_phone_name    = trim($res[$r][23]);
        $hurry                  = $res[$r][24];
        $ticket                 = $res[$r][25];
        $admit_status           = trim($res[$r][26]);
        $amano_input            = $res[$r][27];

        $reappl = $model->IsReAppl($date, $uid);
?>
    <table width='734' class='pt10' border="1" cellspacing="0">
        <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table width='100%' class='winbox_field' bgcolor='#D8D8D8' align='center' border='1' cellspacing='0' cellpadding='3'>

    <?php if( $reappl ) { ?>
        <caption class='pt12b' style='background-color:blue; color:white;'>
        <input type="submit" class='pt11b' style='background-color:blue; color:white; border:none' id=<?php echo 'id_title' . $r; ?> value="�ƿ���" name=<?php echo "edit" . $r; ?> onClick='return EditRun(<?php echo $r; ?>);'>
        </caption>
    <?php } else if( trim($hurry) == "���" ) { ?>
        <caption class='pt12b' style='background-color:#FF0040; color:white;'>
        >>>>>
        <input type="submit" class='pt11b' style='background-color:#FF0040; color:white; border:none' id=<?php echo 'id_title' . $r; ?> value="���" name=<?php echo "edit" . $r; ?> onClick='return EditRun(<?php echo $r; ?>);'>
        <<<<<
        </caption>
    <?php } else { ?>
        <caption style='background-color:yellow; color:blue;'>
        <input type="submit" class='pt11b' style='background-color:yellow; color:blue; border:none' id=<?php echo 'id_title' . $r; ?> value="�����" name=<?php echo "edit" . $r; ?> onClick='return EditRun(<?php echo $r; ?>);'>
        </caption>
    <?php } ?>
    <tr>
        <td align='center'>������</td>
        <td>
            &ensp;
            <?php
                DayDisplay(substr($res[$r][0], 0, 10), $model);
                echo substr($res[$r][0], 10, 6);
            ?>

        </td>
    </tr>

    <tr>
        <td nowrap align='center'>������</td>
        <td>
            &ensp;
            <?php echo $model->getSyozoku($uid); ?>
            &emsp;
            <?php echo "�Ұ��ֹ桧" . $uid; ?>
            &emsp;
            <?php echo '��̾��'. $model->getSyainName($uid); ?>
        </td>
    </tr>

    <tr>
        <td align='center'>��&ensp;��</td>
        <td>
            &ensp;
            <?php
                DayDisplay($start_date, $model);
                if($start_date != $end_date) {
                    echo " �� ";
                    DayDisplay($end_date, $model);
                }
            ?>
            &emsp;
            <?php
//                echo $start_time . " �� " . $end_time;
                if( $start_time ) echo $start_time;
                if( $start_time && $end_time ) echo " �� ";
                if( $end_time ) echo $end_time;
            ?>
        </td>
    </tr>

    <?php $jyuden_skip = false; ?>
    <tr>
        <td align='center'>��&ensp;��</td>
        <td>
            &ensp;
            <?php echo $res[$r][6]; ?>
        <br>
<?php
            if( $content == "ͭ��ٲ�" || $content == "AMȾ��ͭ��ٲ�" || $content == "PMȾ��ͭ��ٲ�"
                || $content == "����ñ��ͭ��ٲ�" || $content == "���" || $content == "�ٹ�����" ) {
?>
                &emsp;&emsp;<?php echo $yukyu; ?>
<?php
            } else if( $content == "��ĥ���������" || $content == "��ĥ�ʽ����"
                || $content == "ľ��" || $content == "ľ��" || $content == "ľ��/ľ��" ) {
?>
                &emsp;&emsp;<?php echo "���衧" . $others; ?>
                &emsp;<?php echo "��ƻ�ܸ���" . $place; ?><br>&ensp;
                &emsp;<?php echo "��Ū��" . $purpose; ?>
<?php
if( $suica_view == 'on' && $ticket01_set == 1) {   // ����������ԲĤ��б�
?>
                <br>&emsp;&emsp;&emsp;<font color="red">�� Suica �����Ѥ��ޤ���</font>
<?php
} else {
?>
        <?php if( $ticket01 != "����" && $ticket01 != "�Բ�" && $ticket01 != NULL) { ?>
        <br>
                &emsp;&emsp;��ַ��ʻ�ȡ����Եܴ֡�&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;
                <?php echo $ticket01; ?>
                <?php if( $ticket01 != "����" ) echo $ticket01_set . "���å�"; ?>
        <?php } ?>
<?php
}
?>
        <?php if( $ticket02 != "����" && $ticket02 != "�Բ�" && $ticket02 != NULL ) { ?>
        <br>
                &emsp;&emsp;�������õ޷���ͳ�ʡ���ַ��ʱ��Եܡ�����֡�
                <?php echo $ticket02; ?>
                <?php if( $ticket02 != "����" ) echo $ticket02_set . "���å�"; ?>
        <?php } ?>
        <br>
        <?php if( $doukousya != '---' ) { ?>
                &emsp;&emsp;<?php echo "Ʊ�Լԡ�" . $doukousya; ?>
        <?php } ?>
<?php
            } else if( $content == "���̵ٲ�" ) {
?>
                &emsp;&emsp;<?php echo $special; ?>
                <?php if( $special == "����¾" ) echo "�� " . $others; ?>
<?php
            } else if( $content == "���ص���" || $content == "����¾" ) {
?>
                &emsp;&emsp;<?php echo $others; ?>
<?php
            } else if( $content == "�����ٲ�" ) {
                ; // �����ٲ�
            } else {
                $jyuden_skip = true; // ID�����ɷ�
            }
?>
        </td>
    </tr>

    <tr>
        <td align='center'>��&ensp;��</td>
        <td>
            &ensp;
            <?php echo $remarks; ?>
        </td>
    </tr>

    <tr>
        <td align='center'>Ϣ����</td>
        <td>
            &ensp;
            <?php echo $contact; ?>
            <?php if( $contact == "����¾" ) echo "(" . $contact_other . ")"; ?>
            <?php if( $contact == "����¾" || $contact == "��ĥ��") echo "TEL:" . $contact_tel; ?>
        </td>
    </tr>

    <?php $jyuden = false; ?>
    <?php if( $received_phone != '' ) { ?>
        <script>Zigo(<?php echo $r; ?>);</script>
        <tr>
            <td align='center'>���ż�</td>
            <td>
                    &ensp;
                    <?php
                        echo "����������";
                        DayDisplay(substr($received_phone_date,0,10), $model);
                        echo substr($received_phone_date, 10, 6);
                        if( is_numeric($received_phone_name) ) {
                            echo " ���мԡ�" . $model->getSyainName($received_phone_name);
                        } else {
                            echo " ���мԡ�" . $received_phone_name;
                        }
                        $jyuden = true;
                    ?>
            </td>
        </tr>
    <?php } else { ?>
        <?php
        if( $reappl ) {
            $previous_date = $model->GetPreviousDate($date, $uid);
            if( $previous_date == "" ) {
                $previous_date = $date;
            }
            $sin_dt = new DateTime($previous_date);             // ����������
        } else {
            $sin_dt = new DateTime($date);                      // ��������
        }
        $str_dt = new DateTime("{$start_date} {$start_time}");  // �о�����(����)
        $end_dt = new DateTime("{$end_date} {$end_time}");      // �о�����(��λ)
        // 2021.09.06 �������֤�곫�ϻ���($str_dt)�����ʤ���żԤ�ɬ�פȤ��롣�褦���ѹ�
        // �� 2021.09.27 �мҸ塢ͭ�ټ������˼��żԤ�ɽ�����ʤ��褦 $end_dt
        ?>
        <?php if( $sin_dt >= $str_dt && $sin_dt >= $end_dt && !$jyuden_skip ) { ?>
        <script>ZigoOutai(<?php echo $r; ?>);</script>
        <tr>
            <td align='center' style='color:Red;'>���ż�</td>
            <td nowrap>
                    &emsp;����������
                    <select name=<?php echo "ddlist_ye" . $r ?> id="id_ye" onblur="JyuDateCopy(<?php echo $r; ?>)">
                        <?php SelectOptionDate($str_dt->format('Y')-1, $str_dt->format('Y'), $str_dt->format('Y')); ?>
                    </select>ǯ
                    <select name=<?php echo "ddlist_mo" . $r ?> id="id_mo" onblur="JyuDateCopy(<?php echo $r; ?>)">
                        <?php SelectOptionDate(1, 12, $str_dt->format('m')); ?>
                    </select>��
                    <select name=<?php echo "ddlist_da" . $r ?>  id="id_da" onblur="JyuDateCopy(<?php echo $r; ?>)">
                        <?php SelectOptionDate(1, 31, $str_dt->format('d')); ?>
                    </select>��
                    <select name=<?php echo "ddlist_ho" . $r ?> id="id_ho" onblur="JyuDateCopy(<?php echo $r; ?>)">
                        <?php SelectOptionTime(0, 23, 8); ?>
                    </select>��
                    <select name=<?php echo "ddlist_mi" . $r ?> id="id_mi" onblur="JyuDateCopy(<?php echo $r; ?>)">
                        <?php SelectOptionTime(0, 59, 30); ?>
                    </select>ʬ
                    <input type='hidden' name=<?php echo "jyu_date" . $r ?> value=''>

                    ���мԡ�<input type="text" size="17" maxlength="8" name=<?php echo "outai" . $r ?> onMouseover=<?php echo "Coment".$r.".style.visibility='visible'" ?> onMouseout=<?php echo "Coment".$r.".style.visibility='hidden'" ?> onkeydown='OutaiEnter(<?php echo $r; ?>)' title="">

                    <div id=<?php echo "Coment" . $r; ?> style="color:#000000; background:#e7e7e7; font-size='9pt'; position:absolute; top:; left:; width:150; padding:5; visibility:hidden; filter:alpha(opacity='80');">
                        [�Ұ��ֹ�] or [̾��]
                    </div>

                    <input type="submit" value="��Ͽ" name=<?php echo "received_phone_register" . $r ?> onClick='return ReceivedPhoneRegister(<?php echo $r; ?>)'>
                    <input type='hidden' name=<?php echo "jyu_register" . $r ?> value=''>
            </td>
        </tr>
        <?php } else { ?>
            <?php $jyuden = true; ?>
        <?php } ?>
    <?php } ?>
<!--
    <?php if( $received_phone != '' ) { ?>
    <tr>
        <td align='center'>�����ż�</td>
        <td>
                <?php echo "Ϣ�������������" . $received_phone_date; ?>
                <?php echo "���мԡ�" . $received_phone_name; ?>
        </td>
    </tr>
    <?php } ?>
<!-- -->
    <?php $model->getAdmit($request, $date, $uid); ?>

    <table width='100%' class='winbox_field' bgcolor='#D8D8D8' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='pt10' border="1" cellspacing="0">

        <table class='pt10' border="1" cellspacing="1" align='center' style='color:Green;'>
        <caption>�� ǧ �� ��</caption>
        <tr align='center'>
<!-- -->
            <td>������</td>
            <td></td>
<!-- 
            <td><?php echo $model->getSyainName($uid); ?></td>
<!-- -->
<!-- 
            <td>�� ̾</td>
<!-- -->
            <td>��ǧ��</td>
            <td></td>
<!-- -->
            <td><?php echo $model->getSyainName($request->get('kakarityo')); ?></td>
            <td><?php echo $model->getSyainName($request->get('katyo')); ?></td>
            <td><?php echo $model->getSyainName($request->get('butyo')); ?></td>
            <td><?php if($request->get('somukatyo')!='------') echo "��̳��Ĺ"; else echo "------"; ?></td>
            <td><?php if($request->get('kanributyo')!='------') echo "������Ĺ"; else echo "------"; ?></td>
            <td><?php if($request->get('kojyotyo')!='------') echo "�� �� Ĺ"; else echo "------"; ?></td>
        </tr>
        <tr align='center'>
<!-- -->
            <td>��</td>
            <td></td>
<!-- 
            <td><?php echo substr($res[$r][0], 0 ,10); ?></td>
<!-- -->
<!-- 
            <td>�� ��</td>
<!-- -->
            <td>��ǧ��</td>
            <td></td>
<!-- -->
<?php
            $kakar_date = $request->get('kakarityo_date');
            $katyo_date = $request->get('katyo_date');
            $butyo_date = $request->get('butyo_date');
            $soumu_date = $request->get('somukatyo_date');
            $kanri_date = $request->get('kanributyo_date');
            $kojyo_date = $request->get('kojyotyo_date');
?>
<!-- -->
            <td><?php if($kakar_date=='------') echo $kakar_date; else echo "<font color='Red'>$kakar_date</font>"; ?></td>
            <td><?php if($katyo_date=='------') echo $katyo_date; else echo "<font color='Red'>$katyo_date</font>"; ?></td>
            <td><?php if($butyo_date=='------') echo $butyo_date; else echo "<font color='Red'>$butyo_date</font>"; ?></td>
            <td><?php if($soumu_date=='------') echo $soumu_date; else echo "<font color='Red'>$soumu_date</font>"; ?></td>
            <td><?php if($kanri_date=='------') echo $kanri_date; else echo "<font color='Red'>$kanri_date</font>"; ?></td>
            <td><?php if($kojyo_date=='------') echo $kojyo_date; else echo "<font color='Red'>$kojyo_date</font>"; ?></td>
<!--
            <td><?php echo $request->get('katyo_date'); ?></td>
            <td><?php echo $request->get('katyo_date'); ?></td>
            <td><?php echo $request->get('butyo_date'); ?></td>
            <td><?php echo $request->get('somukatyo_date'); ?></td>
            <td><?php echo $request->get('kanributyo_date'); ?></td>
            <td><?php echo $request->get('kojyotyo_date'); ?></td>
<!-- -->
        </tr>
        <tr align='center'>
            <td><input type="checkbox" name=<?php echo 70000+$r; ?> onClick='setNgMail(<?php echo $r; ?>, 0);' checked disabled></td>
            <td></td>
            <td>��ǧ�᡼��</td>
            <td></td>
            <td><input type="checkbox" name=<?php echo 70000+$r; if($kakar_date=='------' ) echo " disabled"; else echo " checked"; ?> onClick='setNgMail(<?php echo $r; ?>, 1);'></td>
            <td><input type="checkbox" name=<?php echo 70000+$r; if($katyo_date=='------' ) echo " disabled"; else echo " checked"; ?> onClick='setNgMail(<?php echo $r; ?>, 2);'></td>
            <td><input type="checkbox" name=<?php echo 70000+$r; if($butyo_date=='------' ) echo " disabled"; else echo " checked"; ?> onClick='setNgMail(<?php echo $r; ?>, 3);'></td>
            <td><input type="checkbox" name=<?php echo 70000+$r; if($soumu_date=='------' ) echo " disabled" ?> onClick='setNgMail(<?php echo $r; ?>, 4);'></td>
            <td><input type="checkbox" name=<?php echo 70000+$r; if($kanri_date=='------' ) echo " disabled" ?> onClick='setNgMail(<?php echo $r; ?>, 5);'></td>
            <td><input type="checkbox" name=<?php echo 70000+$r; if($kojyo_date=='------' ) echo " disabled" ?> onClick='setNgMail(<?php echo $r; ?>, 6);'></td>
        </tr>
            <!-- ��ǧ�᡼�� �����ե饰 -->
            <input type='hidden' name=<?php echo 70000+$r . "_sinsei"; ?> value=true>
            <input type='hidden' name=<?php echo 70000+$r . "_kakari"; ?> value=<?php if($kakar_date!='------' ) echo "true"; ?>>
            <input type='hidden' name=<?php echo 70000+$r . "_katyo"; ?>  value=<?php if($katyo_date!='------' ) echo "true"; ?>>
            <input type='hidden' name=<?php echo 70000+$r . "_butyo"; ?>  value=<?php if($butyo_date!='------' ) echo "true"; ?>>
            <input type='hidden' name=<?php echo 70000+$r . "_soumu"; ?>>
            <input type='hidden' name=<?php echo 70000+$r . "_kanri"; ?>>
            <input type='hidden' name=<?php echo 70000+$r . "_kojyo"; ?>>
        </table>

        <p align='center'>
            <input type='hidden' name=<?php echo 90000+$r; ?>>
<!--
            <input type="submit" value="����" name=<?php echo "edit" . $r; ?> onClick='EditRun(<?php echo $r; ?>);'>
<!--/**/-->
            <?php if( ($model->IsKatyou($model->getUid()) || $model->IsButyou($model->getUid()) ) && !$jyuden ) { ?>
<!-- -->
                <font color='DarkGray' >
                <input type="radio" name=<?php echo $r; ?> id=<?php echo 1000+$r; ?>  disabled>��ǧ
                </font>
            <?php } else { ?>
                <input type="radio" name=<?php echo $r; ?> id=<?php echo 1000+$r; ?> onclick="AdmitSelect(this, <?php echo $r; ?>);" value="��ǧ"><label for=<?php echo 1000+$r; ?>>��ǧ</label>
            <?php } ?>
                <input type="radio" name=<?php echo $r; ?> id=<?php echo 5000+$r; ?> onclick="DenySelect(this, <?php echo $r; ?>);" value="��ǧ"><label for=<?php echo 5000+$r; ?>>��ǧ</label>
                <font color='DarkGray' id=<?php echo 55000+$r; ?>>����ͳ��</font><input type="text" size="30"  maxlength="40" name=<?php echo 10000+$r; ?> id=<?php echo 10000+$r; ?> value="" disabled onkeydown='ReasonEnter(<?php echo $r; ?>)'>
<!--/**/-->
<!--
            <input type="radio" name=<?php echo $r; ?> id=<?php echo 1000+$r; ?> onclick="DenyReason(<?php echo $r; ?>);" value="��ǧ"><label for=<?php echo 1000+$r; ?>>��ǧ</label>
            <input type="radio" name=<?php echo $r; ?> id=<?php echo 5000+$r; ?> onclick="DenyReason(<?php echo $r; ?>);" value="��ǧ"><label for=<?php echo 5000+$r; ?>>��ǧ</label>
            <font color='DarkGray' id=<?php echo 55000+$r; ?>>����ǧ��ͳ��</font><input type="text" name=<?php echo 10000+$r; ?> id=<?php echo 10000+$r; ?> value="" disabled>
<!-- -->
        </p>
        <input type='hidden' name=<?php echo 15000+$r; ?>>
        <input type='hidden' name=<?php echo 20000+$r; ?>>

        </table>
        </td></tr>
    </table> <!----------------- ���ߡ�End --------------------->

        </table>
        </td></tr>
    </table> <!----------------- ���ߡ�End --------------------->
<br>

<?php } /* for() */ ?>

    <input type='hidden' name="edit_no" ?>

    <input type="button" value="��ǧ�������" name="bulk_selection" onClick="BulkSelection(this, <?php echo $rows; ?>);" >&emsp;&emsp;
<?php /* if( $model->getUid() == '300144' || $model->getUid() == '300055' ) { */ ?>
    <input type="checkbox" name="next" id="id_next" onClick='SetValue(this);'><label for='id_next'>���ξ�ǧ�Ԥ�����</label>��
<?php /* } */ ?>
    <input type="submit" value="����" name="submit" onClick='return onAdmit(<?php echo $rows; ?>)' >&emsp;
    <input type="button" value="�ꥻ�å�" name="reset" onClick='location.replace("<?php echo $menu->out_self(), '?', $model->get_htmlGETparm() ?>");'>
<!--
    <?php if($request->get('c_agent') == '' && ($model->IsKatyou($model->getUid()) || $model->IsButyou($model->getUid())) ) { ?>
    <br><br>���������� �ƥ����� ����������<br><br>
    <input type="checkbox" name="c_agent" id="id_agent" value="" onClick='AgentCheck(this)'><label for="id_agent">������ǧ</label>
    <font id="agent_select">
    <select id="ddlist">
        <?php SelectOptionAgent($model); ?>
    </select> ���Ƥ�����Ϥ�
    <input type="submit" value="ɽ��" name="agent">
    </font>
    <?php } ?>
<!-- -->
</form>

<?php } /* if()*/ ?>

</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
