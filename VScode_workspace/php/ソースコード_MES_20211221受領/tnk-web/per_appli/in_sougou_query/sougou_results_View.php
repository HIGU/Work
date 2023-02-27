<?php
////////////////////////////////////////////////////////////////////////////////
// ����ϡʾȲ��                                                             //
//                                                    MVC View �� �ꥹ��ɽ��  //
// Copyright (C) 2020-2020 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2020/11/18 Created sougou_results_View.php                                 //
// 2021/02/12 Release.                                                        //
// 2021/04/21 �ҳ�����νи���������Ԥϡ����ڤμҰ������ɤ��༡�ɲ�          //
////////////////////////////////////////////////////////////////////////////////

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
<script type='text/javascript' language='JavaScript' src='sougou_query.js'></script>

</head>
<body>
<center>

<?php
    $Y = date('Y');
    $m = date('m');
    $d = date('d');
    $limit = 25;    // ����������

    if( $d < $limit ) {
        if($m == 1) {
            $Y -= 1;
            $m = 13;
        }
        $del_day = sprintf("%04s-%02s-10", $Y, ($m-1));   // ����10������������Բ�
    } else {
        $del_day = sprintf("%04s-%02s-10", $Y, $m);   // �����10������������Բ�
    }

    $res = array(); 
    $indx = $model->getIndx();
    $rows = $model->getRows();
    $res = $model->getRes();

    // [���]�ܥ���ǡ���ä����˥ǡ���������Ϥ���POST�ǡ������å�
    $menu->set_retPOST('rep', 'rep');
    $menu->set_retPOST('c0', $request->get('c0'));
    $menu->set_retPOST('si_s_date', $request->get('si_s_date'));
    $menu->set_retPOST('si_e_date', $request->get('si_e_date'));
    $menu->set_retPOST('syainbangou', $request->get('syainbangou'));
    $menu->set_retPOST('c1', $request->get('c1'));
    $menu->set_retPOST('str_date', $request->get('str_date'));
    $menu->set_retPOST('end_date', $request->get('end_date'));
    $menu->set_retPOST('c2', $request->get('c2'));
    $menu->set_retPOST('ddlist', $request->get('ddlist'));
    $menu->set_retPOST('ddlist_bumon', $request->get('ddlist_bumon'));
    $menu->set_retPOST('r4', $request->get('r4'));
    $menu->set_retPOST('r5', $request->get('r5'));
    $menu->set_retPOST('r6', $request->get('r6'));
    $menu->set_retPOST('r7', $request->get('r7'));
    $menu->set_retPOST('r8', $request->get('r8'));
    $menu->set_retPOST('r9', $request->get('r9'));
?>
<?= $menu->out_title_border() ?>

<?php $showMenu = 'List' ?>
<form name='form_results' method='post' action='<?php echo $menu->out_self(),"?showMenu=" . $showMenu ?>' onSubmit='return true'>

<input type='hidden' name='rows' value=<?php echo $rows; ?>>
<input type='hidden' name='indx' value=<?php echo $indx; ?>>

<?php for( $r=0; $r<$rows; $r++ ) { ?>
    <?php $posname = sprintf("res-%s[]", $r); ?>
    <?php for( $i=0; $i<$indx; $i++ ) { ?>
        <input type='hidden' name='<?php echo $posname; ?>' value='<?php echo $res[$r][$i]; ?>'>
    <?php } ?>
<?php } ?>

<input type='hidden' name='rep' value='rep'>
<input type='hidden' name='c0' value='<?php echo $request->get('c0'); ?>'>
<input type='hidden' name='si_s_date' value='<?php echo $request->get('si_s_date'); ?>'>
<input type='hidden' name='si_e_date' value='<?php echo $request->get('si_e_date'); ?>'>
<input type='hidden' name='syainbangou' value='<?php echo $request->get('syainbangou'); ?>'>
<input type='hidden' name='c1' value='<?php echo $request->get('c1'); ?>'>
<input type='hidden' name='str_date' value='<?php echo $request->get('str_date'); ?>'>
<input type='hidden' name='end_date' value='<?php echo $request->get('end_date'); ?>'>
<input type='hidden' name='c2' value='<?php echo $request->get('c2'); ?>'>
<input type='hidden' name='ddlist' value='<?php echo $request->get('ddlist'); ?>'>
<input type='hidden' name='ddlist_bumon' value='<?php echo $request->get('ddlist_bumon'); ?>'>
<input type='hidden' name='r4' value='<?php echo $request->get('r4'); ?>'>
<input type='hidden' name='r5' value='<?php echo $request->get('r5'); ?>'>
<input type='hidden' name='r6' value='<?php echo $request->get('r6'); ?>'>
<input type='hidden' name='r7' value='<?php echo $request->get('r7'); ?>'>
<input type='hidden' name='r8' value='<?php echo $request->get('r8'); ?>'>
<input type='hidden' name='r9' value='<?php echo $request->get('r9'); ?>'>

<?php if( $model->getRows() == 0) { ?>
    <br>�������˰��פ�������ϤϤ���ޤ���<br>
<?php } else  if($request->get('c2') == '') { ?>
    <br>�������˰��פ�������� �� <?php echo $rows . " ���"?><br>
<?php if(getCheckAuthority(66)) { ?> <!-- 66:��ò�ǽ����̳�ݡ�-->
    <p class='pt9' style="text-align: right"><?php echo "��{$limit}���˳�������Ѥߤΰ١�{$del_day}�����μ�äϤǤ��ޤ���<BR>�ҳ�����νи���������Ԥϡ��طʿ����ġ�"; ?></p>
<?php } ?>

    <table class='pt10' border="1" cellspacing="0">
    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
<table width='100%' class='pt10' bgcolor='#D8D8D8' align='center' border='1' cellspacing='0' cellpadding='3'>
    <tr style='background-color:yellow; color:blue;'>
<?php if( getCheckAuthority(66)) { ?> <!-- 66:��ò�ǽ����̳�ݡ�-->
        <td align='center'>����</td>
<?php } ?>
        <td align='center'>������</td>
        <?php if(getCheckAuthority(63) || $model->IsBukatyou()) { ?> <!-- 63:�Ұ��ֹ����ϲ�ǽ����̳�ݡ�-->
        <td align='center'>������</td>
        <?php } ?>
        <td align='center'>����</td>
        <td align='center'>����</td>
        <td align='center'>����</td>
        <td align='center'>Ϣ����</td>
<?php
if( date('Ymd') > '20210630' ) {    // ����������ԲĤ��б�
        echo "<td align='center'>Suica</td>";
        $suica_view = 'on';
} else {
        echo "<td align='center'>�����</td>";
        $suica_view = '';
}
?>
        <td align='center'>���ż�</td>
        <td align='center'>���</td>
        <td align='center'>��ǧ����</td>
        <td align='center'>������ͳ</td>
<?php if(getCheckAuthority(66)) { ?> <!-- 66:��ò�ǽ����̳�ݡ�-->
        <td align='center'>���</td>
<?php } ?>
    </tr>

<?php
    for ( $r=0; $r<$rows; $r++) {
        $date                   = $res[$r][0];          // ������
        if( $r==0 || $res[$r-1][1] != $res[$r][1]) {
            $uid                    = $res[$r][1];      // �����ԼҰ��ֹ�
        } else {
            $uid                    = "��";             // �����ԼҰ��ֹ�
        }
        $start_date             = trim($res[$r][2]);    // ������
        $start_time             = $res[$r][3];          // ���ϻ���
        $end_date               = trim($res[$r][4]);    // ��λ��
        $end_time               = $res[$r][5];          // ��λ����
        $content                = trim($res[$r][6]);    // ����
        $yukyu                  = $res[$r][7];          // ���ƾܺ١�ͭ�ٷϡ�
        $ticket01               = trim($res[$r][8]);    // �������̵ͭ
        $ticket02               = trim($res[$r][9]);    // �õ޷���̵ͭ
        $special                = trim($res[$r][10]);   // ���ƾܺ١����̵ٲˡ�
        if( $special == '��ĤA' ) {
            $special = "��Ĥ���ܿͤ��뺧 5��(������1��)";
        } else if ( $special == '��ĤB' ) {
            $special = "��Ĥ�����졦�۶��ԡ��Ҥ���˴ 5��";
        } else if ( $special == '��ĤC' ) {
            $special = "��Ĥ���۶��Ԥ����졢�ܿͤ������졢����λ�˴ 3��";
        }
        $others                 = $res[$r][11];         // ���� or �������� or ����¾
        $place                  = $res[$r][12];         // ��ƻ�ܸ�
        $purpose                = $res[$r][13];         // ��Ū
        $ticket01_set           = trim($res[$r][14]);   // �������ɬ�׿�
        $ticket02_set           = trim($res[$r][15]);   // �õ޷���ɬ�׿�
        $doukousya              = trim($res[$r][16]);   // Ʊ�Լ�
        $remarks                = $res[$r][17];         // ����
        $contact                = trim($res[$r][18]);   // Ϣ����
        $contact_other          = trim($res[$r][19]);   // Ϣ����ʤ���¾��
        $contact_tel            = $res[$r][20];         // Ϣ�����TEL��
        $received_phone         = $res[$r][21];         // ���żԤ�̵ͭ
        $received_phone_date    = $res[$r][22];         // ��������
        $received_phone_name    = $res[$r][23];         // ���ż�̾
        $hurry                  = $res[$r][24];         // ��ޤ�̵ͭ
        $ticket                 = $res[$r][25];         // ��������õ޷���̵ͭ
        $admit_status           = trim($res[$r][26]);   // ��ǧ����
        $amano_input            = trim($res[$r][27]);   // ���Ͼ���
        if( $admit_status == 'END') {
            $admit_status = '��λ';
        } else if( $admit_status == 'DENY' ) {
            $admit_status = '��ǧ';
        } else if( $admit_status == 'CANCEL' ) {
            $admit_status = '���';
        }
        $amano_input            = $res[$r][27];         // ���ޥ����Ͼ���

        // �ҳ�����νи���������� �༡�ɲ�
        // 020826:
        $view_style="";
        if( $res[$r][1] == '020826' ) {
            $view_style='background-color:RoyalBlue; color:White;';
        }
?>
    <?php echo "<tr style='{$view_style}'>"; ?>
<!-- ���Ͼ��� -->
<?php if( getCheckAuthority(66)) { ?> <!-- 66:��ò�ǽ����̳�ݡ�-->
    <?php if( $amano_input == 't' ) { ?>
            <td nowrap align='center'>��</td>
    <?php } else { ?>
            <td nowrap><input type="checkbox" name=<?php echo "amano" . $r; ?> onClick=SetVal(this);></td>
    <?php } ?>
<?php } ?>

<!-- ������ -->
        <td nowrap><?php echo substr($res[$r][0], 0 ,10); ?></td>
<!-- ������ -->
        <?php if(getCheckAuthority(63) || $model->IsBukatyou()) { ?> <!-- 63:�Ұ��ֹ����ϲ�ǽ����̳�ݡ�-->
        <td nowrap>
            <?php echo $uid; ?>
            <br>
            <?php echo $model->getSyainName($uid); ?>
        </td>
        <?php } ?>
<!-- ���� -->
        <td nowrap>
            <?php
                DayDisplay($start_date, $model);
                if($start_date != $end_date) {
                    echo " �� ";
                    DayDisplay($end_date, $model);
                }
            ?>
        <br>
            <?php
//                echo '��' . $start_time . " �� " . $end_time;
                echo '��';
                if( $start_time ) echo $start_time;
                if( $start_time && $end_time ) echo " �� ";
                if( $end_time ) echo $end_time;
            ?>
        </td>
<!-- ���� -->
        <td nowrap>
            <?php
            if($content == "ID�������̤�˺�����Сˡ� ���¾�ǧ˺��ʻĶȿ���ϳ���") {
                echo "ID�������̤�˺�����С�<br>";
                echo "�������¾�ǧ˺��ʻĶȿ���ϳ���<br>";
            } else {
                echo $content . "<br>";
            }
            ?>
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
<?php
} else {
?>
    <?php if( $ticket01 != "����" && $ticket01 != NULL ) { ?>
    <br>
            &emsp;&emsp;��ַ��ʻ�ȡ����Եܴ֡�&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;
            <?php echo $ticket01; ?>
            <?php if( $ticket01 != "����" ) echo $ticket01_set . "���å�"; ?>
    <?php } ?>
    <?php if( $ticket02 != "����" && $ticket01 != NULL ) { ?>
    <br>
            &emsp;&emsp;�������õ޷���ͳ�ʡ���ַ��ʱ��Եܡ�����֡�
            <?php echo $ticket02; ?>
            <?php if( $ticket02 != "����" ) echo $ticket02_set . "���å�"; ?>
    <?php } ?>
<?php
}
?>
    <?php if( $doukousya != '---' ) { ?>
    <br>
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
        } else {
            ; // echo "����ʳ�";
        }
?>
        </td>
<!-- ���� -->
        <td nowrap><?php echo $remarks; ?></td>
<!-- Ϣ���� -->
        <td nowrap>
            <?php echo $contact; ?>
            <?php if( $contact == "����¾" ) echo "(" . $contact_other . ")"; ?>
            <?php if( $contact == "����¾" || $contact == "��ĥ��") echo "<br> TEL:" . $contact_tel; ?>
        </td>
<!-- ����� -->
        <td nowrap align='center'>
            <?php if( $ticket == 't' ) { ?>
                <?php echo "ɬ��"; ?>
            <?php } else if( $ticket == 'f' ) { ?>
                <?php echo "����"; ?>
            <?php } else { ?>
                <?php echo "---"; ?>
            <?php } ?>
        </td>
<!-- ���ż� -->
        <td nowrap>
            <?php if( $received_phone != '' ) { ?>
                <?php echo "����������" . $received_phone_date; ?>
                <br>
                <?php echo "�� �� �ԡ�" . $received_phone_name; ?>
            <?php } else { ?>
                <?php echo "---"; ?>
            <?php } ?>
        </td>
<!-- ��� -->
        <td nowrap>
            <?php if( $hurry != '' ) { ?>
                <?php echo $hurry; ?>
            <?php } else { ?>
                <?php echo "---"; ?>
            <?php } ?>
        </td>
<!-- ��ǧ -->
        <td nowrap>
            <?php echo $admit_status; ?>
            <br>
            <?php echo $model->getSyainName($admit_status); ?>
        </td>
<!-- ��ͳ -->
        <td nowrap>
            <?php echo $model->getAdmitStopReason($res[$r][0], $res[$r][1], $admit_status); ?>
        </td>
<!-- ��� -->
<?php if(getCheckAuthority(66)) { ?> <!-- 66:��ò�ǽ����̳�ݡ�-->
    <?php if( str_replace('-', '', $start_date) > str_replace('-', '', $del_day) ) { ?>
        <td>
    <?php } else { ?>
        <td disabled=true>
    <?php } ?>
            <input type="checkbox" name=<?php echo $r; ?> id=<?php echo $r; ?> value="CANCEL" <?php if( $admit_status == '���' ) echo ' disabled' ?>>
        </td>
<?php } ?>
    </tr>

<?php } /* for() End */ ?>

    </table>
    </td></tr>
</table> <!----------------- ���ߡ�End --------------------->

<?php } else { ?> <!--- �ʲ��ϡ��Ժ߼ԥꥹ��ɽ�� --->
    <br>�������˰��פ�������ϰ����� <?php echo $rows . " ���"?>
    <p class='pt9' style="text-align: right"><?php echo "���ҳ�����νи���������Ԥϡ��طʿ����ġ�"; ?></p>

    <table class='pt10' border="1" cellspacing="0">
    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
<table width='100%' class='pt10' bgcolor='#D8D8D8' align='center' border='1' cellspacing='0' cellpadding='3'>
    <tr style='background-color:yellow; color:blue;'>
        <td align='center'>��°������</td>
        <td align='center'>��°̾</td>
        <td align='center'>�Ŀͥ�����</td>
        <td align='center'>��̾</td>
        <td align='center'>������</td>
        <td align='center'>��λ��</td>
        <td align='center'>����</td>
        <td align='center'>���ϻ���</td>
        <td align='center'>��λ����</td>
<!-- �ǽ���ǧ�Ǹ��뤿�ᡢ��ǧ��������ʤ� -->
    </tr>

<?php
    for ( $r=0; $r<$rows; $r++) {
        if( $r == 0 ) { // ��Ƭ�ԤΤ�
            $syozokucode        = trim($res[$r][0]);    // ��°������
            $syozoku            = trim($res[$r][1]);    // ��°
            $kojincode          = trim($res[$r][2]);    // �Ŀͥ�����
            $name               = trim($res[$r][3]);    // ̾��
        } else {        // 2���ܰʹߡ����ԤȰ㤦�ʤ�������Ʊ���ʤ�����
            if( trim($res[$r-1][0]) != trim($res[$r][0]) ) {
                $syozokucode    = trim($res[$r][0]);    // ��°������
                if( trim($res[$r-1][1]) != trim($res[$r][1]) ) {
                    $syozoku        = trim($res[$r][1]);    // ��°
                } else {
                    $syozoku        = "��";                 // ��°
                }
            } else {
                $syozokucode    = "��";                 // ��°������
                $syozoku        = "��";                 // ��°
            }
            if( trim($res[$r-1][2]) != trim($res[$r][2]) ) {
                $kojincode      = trim($res[$r][2]);    // �Ŀͥ�����
                $name           = trim($res[$r][3]);    // ̾��
            } else {
                $kojincode      = "��";                 // �Ŀͥ�����
                $name           = "��";                 // ̾��
            }
        }
        $str_date               = trim($res[$r][4]);    // ������
        $end_date               = trim($res[$r][5]);    // ��λ��
        $content                = trim($res[$r][6]);    // ����
        $str_time               = trim($res[$r][7]);    // ���ϻ���
        if( $str_time == '' ) $str_time = '--:--';
        $end_time               = trim($res[$r][8]);    // ��λ����
        if( $end_time == '' ) $end_time = '--:--';

        // �ҳ�����νи���������� �༡�ɲ�
        // 020826:
        $view_style="";
        if( $res[$r][2] == '020826' ) {
            $view_style='background-color:RoyalBlue; color:White;';
        }
?>
    <?php echo "<tr style='{$view_style}'>"; ?>
<!-- ��°������ -->
        <td align='right'><?php echo $syozokucode; ?></td>
<!-- ��°̾ -->
        <td nowrap><?php echo $syozoku; ?></td>
<!-- �Ŀͥ����� -->
        <td align='right'><?php echo $kojincode; ?></td>
<!-- ��̾ -->
        <td nowrap><?php echo $name; ?></td>
<!-- ������ -->
        <td nowrap align='center'>
            <?php
                DayDisplay($str_date, $model);
            ?>
        </td>
<!-- ��λ�� (��������Ʊ���ʤ����) -->
        <td nowrap align='center'>
            <?php
                if($str_date != $end_date) {
                    DayDisplay($end_date, $model);
                } else {
                    echo "��";
                }
            ?>
        </td>
<!-- ���� -->
        <td nowrap><?php echo $content; ?></td>
<!-- ���ϻ��� -->
        <td><?php echo $str_time; ?></td>
<!-- ��λ���� -->
        <td><?php echo $end_time; ?></td>
    </tr>

<?php } /* for() End */ ?>

    </table>
    </td></tr>
</table> <!----------------- ���ߡ�End --------------------->

<?php } /* if() End */ ?>

    <br>
<?php if( $model->getRows() != 0 && $request->get('c2') == '' && getCheckAuthority(66)) { ?> <!-- 66:��ò�ǽ����̳�ݡ�-->
    <input type="submit" value="���� �� ����" name="amano" onClick='return AmanoRun(<?php echo $rows ?>)'>��
    <input type='hidden' name='amano_run' value='false'>
<?php } ?>

    <input type="submit" value="�����������" name="submit">

<?php if($model->getRows() != 0 && $request->get('c2') == '' && getCheckAuthority(66)) { ?> <!-- 66:��ò�ǽ����̳�ݡ�-->
    ��<input type="submit" value="��ü¹�" name="cancel" onClick='return CancelRun(<?php echo $rows ?>)'>
    <input type='hidden' name='cancel_run' value='false'>
<?php } ?>
</form>

</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
