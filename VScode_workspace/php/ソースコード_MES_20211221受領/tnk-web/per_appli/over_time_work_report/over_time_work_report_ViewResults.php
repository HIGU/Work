<?php
////////////////////////////////////////////////////////////////////////////////
// ����ֳ���ȿ���ʾȲ�˸������ɽ��                                       //
//                                                    MVC View �� �ꥹ��ɽ��  //
// Copyright (C) 2021-2021 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2021/10/20 Created over_time_work_report_ViewResults.php                   //
//            �ҳ�����νи���������Ԥϡ����ڤμҰ������ɤ��༡�ɲ�          //
// 2021/11/01 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////
$menu->out_html_header();
$counter = 0;       // ɽ����������󥿡��ʽ���͡�0��
$date_view = false; // ����ɽ���ե饰�ʽ���͡�false��
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
<body>
<center>

<?= $menu->out_title_border() ?>

<form name='form_results' method='post' action='<?php echo $menu->out_self() ?>' onSubmit='return true'>
<!-- TEST Start.-->
    <input type='hidden' name='login_uid' value="<?php echo $login_uid; ?>">
<!-- TEST End. -->
    <input type='hidden' name='showMenu' value='Quiry'>
    <input type='hidden' name='days_radio' value='<?php echo $request->get("days_radio"); ?>'>
    <input type='hidden' name='ddlist_year' value='<?php echo $request->get("ddlist_year"); ?>'>
    <input type='hidden' name='ddlist_month' value='<?php echo $request->get("ddlist_month"); ?>'>
    <input type='hidden' name='ddlist_day' value='<?php echo $request->get("ddlist_day"); ?>'>
    <input type='hidden' name='ddlist_year2' value='<?php echo $request->get("ddlist_year2"); ?>'>
    <input type='hidden' name='ddlist_month2' value='<?php echo $request->get("ddlist_month2"); ?>'>
    <input type='hidden' name='ddlist_day2' value='<?php echo $request->get("ddlist_day2"); ?>'>
    <input type='hidden' name='ddlist_bumon' value='<?php echo $request->get("ddlist_bumon"); ?>'>
    <input type='hidden' name='s_no' value='<?php echo $request->get("s_no"); ?>'>
    <input type='hidden' name='mode_radio' value='<?php echo $request->get("mode_radio"); ?>'>
    <input type='hidden' name='err_check0' value='<?php echo $request->get("err_check0"); ?>'>
    <input type='hidden' name='err_check1' value='<?php echo $request->get("err_check1"); ?>'>
    <input type='hidden' name='err_check2' value='<?php echo $request->get("err_check2"); ?>'>
    <input type='hidden' name='err_check3' value='<?php echo $request->get("err_check3"); ?>'>
    
    <BR>
    <div id='id_title'>�������˰��פ�������ֳ���ȿ���Ϥ���ޤ���</div>
    
    <table class='pt10' border="1" cellspacing="0">
    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table width='100%' class='pt10' bgcolor='#D8D8D8' align='center' border='1' cellspacing='0' cellpadding='3'>

<!-- ���Ф��� -->
        <tr style='background-color:yellow; color:blue;'>
            <td nowrap align='center'>�����</td>
            <td nowrap align='center'>����̾</td>
            <td nowrap align='center'>�ᡡ̾</td>
            <td nowrap align='center'>ͽ�����</td>
            <td nowrap align='center'>�Ķȼ»���ͳ</td>
            <td nowrap align='center'>����(����)</td>
            <td nowrap align='center'>�жл���</td>
            <td nowrap align='center'>��л���</td>
            <td nowrap align='center'>�ºݺ�Ȼ���</td>
            <td nowrap align='center'>�»ܶ�̳����</td>
            <td nowrap align='center'>����(���)</td>
<!-- ���� --
            <td align='center'>����</td>
<!--  -->
        </tr>

        <?php for ( $r=0; $r<$rows; $r++) { ?>
            <?php
            $s_t_err = $e_t_err = $s_t_a_err = $s_t_b_err = $j_t_b_err = $j_t_a_err = false; // ���顼�ե饰�����
            if( $r != 0 && $res[$r-1][0] == $res[$r][0] && $date_view ) {
                $date = "��";                                       // �����
            } else {
                $date = $model->getTargetDateDay($res[$r][0], 'no');// �����
                $date_view = false; // ����ɽ���ե饰�ꥻ�å�
            }
            if( $date == "��" && $res[$r-1][1] == $res[$r][1] ) {
                $deploy = "��";         // ����̾
            } else {
                $deploy = $res[$r][1];  // ����̾
            }
            $uid    = $res[$r][3]; // �Ұ��ֹ�
            $y_time = "<font style='background-color:red; color:white;'>�� �� �� ��</font>";    // ͽ�����
            if( $res[$r][4] ) {
                $y_time = "{$res[$r][4]}:{$res[$r][5]}��{$res[$r][6]}:{$res[$r][7]}";   // ͽ�����
            }
            $y_cont = $res[$r][8];  // �Ķȼ»���ͳ
            if( ! $y_cont ) $y_cont = "��";
            $y_stat = $model->getAdmitStatus($res[$r][9], $res[$r][10]);    // �������� ��ǧ ����
            $s_time = $model->getWorkingStrTime($uid, $res[$r][0]);         // �жл���
            $e_time = $model->getWorkingEndTime($uid, $res[$r][0]);         // ��л���
/**
if( $uid=='300667' ) { $s_time = '0800';
//$res[$r][16]=$res[$r][17]=$res[$r][18]=$res[$r][19]='01';
}
/**
if( $uid=='300667' ) { $e_time = '1725'; } else 
if( $uid=='300551' ) { $e_time = '1833'; } else 
if( $uid=='300632' ) { $e_time = '1925'; }
/**/
            $j_time = "��";         // �ºݺ�Ȼ���
            if( $res[$r][16] ) {    // ������λ������Ϥ���
                $j_time    = "{$res[$r][16]}:{$res[$r][17]}��{$res[$r][18]}:{$res[$r][19]}";   // �ºݺ�Ȼ���
                $early_dt  = new DateTime("0830");// ��л���
                $work_time = $res[$r][18] . $res[$r][19];
                $work_e_dt = new DateTime("$work_time");// �ºݺ�Ȼ��֡ʽ�λ��
                if( $early_dt >= $work_e_dt ) { // ��н���
                    if( $s_time != "0000" && $s_time != "----" ) {  // �жл��� �ǡ�������
                        $str_dt    = new DateTime("$s_time");// �жл���
                        $work_time = $res[$r][16] . $res[$r][17];
                        $work_s_dt = new DateTime("$work_time");// �ºݺ�Ȼ��֡ʳ��ϡ�
                        if( $str_dt > $work_e_dt ) {// �ºݺ�Ȼ��֡ʽ�λ�ˤ���˽ж�
                            $j_time = "<font style='background-color:red; color:white;'>$j_time</font>";// �����𥨥顼
                            $s_t_a_err = true;
                        } else {
                            if( $str_dt > $work_s_dt) {// �ºݺ�Ȼ��֡ʳ��ϡˤ���˽ж�
                                $j_time = "<font style='background-color:yellow; color:blue;'>$j_time</font>";// �����𥨥顼
                                $s_t_b_err = true;
                            }
                        }
                    }
                } else { // �̾�ĶȽ���
                    if( $e_time != "0000" && $e_time != "----" ) {  // ��л��� �ǡ�������
                        $end_dt = new DateTime("$e_time");  // ��л���
//                        $work_e_dt->modify('-30 minute');   // �ºݺ�Ȼ��֡ʽ�λ30ʬ����
//                        if( $end_dt <= $work_e_dt) {        // ��λ30ʬ�������
                        if( $end_dt < $work_e_dt) {        // ��λ�������
                            $j_time = "<font style='background-color:yellow; color:blue;'>$j_time</font>";  // �����𥨥顼
                            $j_t_b_err = true;
                        } else {
//                            $work_e_dt->modify('60 minute');// �ºݺ�Ȼ��֡ʽ�λ30ʬ���
                            $work_e_dt->modify('30 minute');// �ºݺ�Ȼ��֡ʽ�λ30ʬ���
                            if( $end_dt >= $work_e_dt ) {   // ��λ30ʬ������
                                $j_time = "<font style='background-color:red; color:white;'>$j_time</font>";// �����𥨥顼
                                $j_t_a_err = true;
                            }
                        }
                    }
                }
            }
            $s_time = substr_replace($s_time, ":", 2, 0);   // �жл���
            if($s_time == "00:00" && $res[$r][0] != date('Y-m-d') ) {
                $s_time = "<font style='background-color:yellow; color:blue;'>$s_time</font>";
                $s_t_err = true;
            }
            $e_time = substr_replace($e_time, ":", 2, 0);   // ��л���
            if($e_time == "00:00" && $res[$r][0] != date('Y-m-d') ) {
                $e_time = "<font style='background-color:yellow; color:blue;'>$e_time</font>";
                $e_t_err = true;
            }
            $j_cont = $res[$r][20];  // �»ܶ�̳����
            if( $res[$r][16] && $res[$r][16]==$res[$r][18] && $res[$r][17]==$res[$r][19] ) {
                $j_time = "<font style='background-color:red; color:white;'>�Ķ� ����󥻥�</font>"; // �ºݺ�Ȼ���
//                $j_time = "{$res[$r][16]}:{$res[$r][17]}��{$res[$r][18]}:{$res[$r][19]}";   // �ºݺ�Ȼ���
//                $j_cont = "<font style='background-color:red; color:white;'>�Ķ� ����󥻥�</font>";
                $j_t_b_err = $j_t_a_err = $s_t_b_err = $s_t_a_err = false;  // ���顼���
                if( $s_t_err && $e_t_err ) $s_t_err = $e_t_err = false;     // ���顼���
            }
            if( ! $j_cont ) $j_cont = "��";
            $j_rema = $res[$r][21];  // ����
            if( ! $j_rema ) $j_rema = "��";
            $j_stat = $model->getAdmitStatus($res[$r][22], $res[$r][23]); // ���� ��ǧ ����
            
            if( $request->get("err_check0") || $request->get("err_check1") || $request->get("err_check2") || $request->get("err_check3") ) {
                if( $request->get("err_check1") && ($e_t_err || $s_t_err) ) { // ��� or �ж� ���Ƥʤ�
                    $counter++;   // ok
                } else {
                    if( $request->get("err_check2") && ($j_t_b_err || $s_t_b_err) ) {   // �������������
                        $counter++;   // ok
                    } else {
                        if( $request->get("err_check3") && ($j_t_a_err || $s_t_a_err) ) {   // 30ʬ�ۤ�
                            $counter++;   // ok
                        } else {
                            if( $request->get("err_check0") && !$s_t_err && !$e_t_err && !$j_t_b_err && !$j_t_a_err && !$s_t_b_err && !$s_t_a_err) {
                                $counter++; // ok
                            } else {
                                continue;   // ɽ�����ʤ�
                            }
                        }
                    }
                }
            } else {
                $counter++;   // ok
            }
            $date_view = true;  // ����ɽ���ե饰ON
            
            // �ҳ�����νи���������� �༡�ɲ�
            // 020826:�ʼ��ݾڲ� ����
            $view_style="";
            if( $uid == '020826' ) {
                $view_style="style='background-color:RoyalBlue; color:White;'";
            }
            ?>
            <tr <?php echo "$view_style"; ?> >
<!-- ����� -->
                <td nowrap><?php echo $date; ?></td>
<!-- ����̾ -->
                <td nowrap><?php echo $deploy; ?></td>
<!-- ��  ̾ -->
                <td nowrap><?php echo $model->getName($uid); ?></td>
<!-- ͽ����� -->
                <td nowrap><?php echo $y_time; ?></td>
<!-- �Ķȼ»���ͳ -->
                <td nowrap><?php echo $y_cont; ?></td>
<!-- �������� ���� -->
                <td nowrap align='center'><?php echo $y_stat; ?></td>
<!-- �жл��� -->
                <td nowrap align='center'><?php echo $s_time; ?></td>
<!-- ��л��� -->
                <td nowrap align='center'><?php echo $e_time; ?></td>
<!-- �ºݺ�Ȼ��� -->
                <td nowrap align='center'><?php echo $j_time; ?></td>
<!-- �»ܶ�̳���� -->
                <td nowrap><?php echo $j_cont; ?></td>
<!-- �Ķȷ����� ���� -->
                <td nowrap align='center'><?php echo $j_stat; ?></td>
<!-- ���� --
                <td nowrap><?php echo $j_rema; ?></td>
<!--  -->
            </tr>
        <?php } /* for() End. */ ?>
        
        <script>
            var obj = document.getElementById('id_title');
            if( <?php echo $counter; ?> > 0 ) {
                obj.innerHTML="�������˰��פ�������ֳ���ȿ��𤬤���ޤ���<?php echo '�� ' . $counter . ' ���'; ?>";
            }
        </script>
        
        </table>
        </td></tr>
    </table> <!----------------- ���ߡ�End --------------------->
    <br>
    <input type="submit" value="�����������" name="submit">
    <br>��

</form>

</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
