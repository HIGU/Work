<?php
////////////////////////////////////////////////////////////////////////////////
// ����ϡʿ�����                                                             //
//                                                    MVC View �� ��ǧɽ��    //
// Copyright (C) 2020-2020 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2020/11/18 Created sougou_admit_ViewCheck.php                              //
// 2021/02/12 Release.                                                        //
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
<script type='text/javascript' language='JavaScript' src='sougou.js'></script>

</head>
<?php
$sinseiNG = false;
$naiyou= $request->get('r1');
if( $naiyou == "ͭ��ٲ�" || $naiyou == "AMȾ��ͭ��ٲ�" || $naiyou == "PMȾ��ͭ��ٲ�"
 || $naiyou == "����ñ��ͭ��ٲ�" || $naiyou == "���" || $naiyou == "�ٹ�����"
 || $naiyou == "���̵ٲ�" || $naiyou == "���ص���" || $naiyou == "�����ٲ�" )
{
    if( $model->IsHoliday($request->get("str_date")) ) $sinseiNG = true;
    if( $model->IsHoliday($request->get("end_date")) ) $sinseiNG = true;
}
?>
<body onLoad='CheckDisp(<?php echo $sinseiNG ?>)'>
<center>

<div>
<br>�ںǽ���ǧ��<br><br>
</div>

<?php $showMenu = 'List'; ?>
<form name='form_check' method='post' action='<?php echo $menu->out_self(),"?showMenu=" . $showMenu ?>' onSubmit='return true'>

<input type='hidden' name='check_flag' ?>
<input type='hidden' name='sin_date' value='<?php echo $request->get("sin_date"); ?>'>
<input type='hidden' name='sin_year' value='<?php echo $request->get("sin_year"); ?>'>
<input type='hidden' name='sin_month' value='<?php echo $request->get("sin_month"); ?>'>
<input type='hidden' name='sin_day' value='<?php echo $request->get("sin_day"); ?>'>
<input type='hidden' name='sin_hour' value='<?php echo $request->get("sin_hour"); ?>'>
<input type='hidden' name='sin_minute' value='<?php echo $request->get("sin_minute"); ?>'>

<input type='hidden' name='syain_no' value='<?php echo $request->get("syain_no"); ?>'>
<input type='hidden' name='syainbangou' value='<?php echo $request->get("syain_no"); ?>'>
<input type='hidden' name='str_date' value='<?php echo $request->get("str_date"); ?>'>
<input type='hidden' name='str_time' value='<?php echo $request->get("str_time"); ?>'>
<input type='hidden' name='end_date' value='<?php echo $request->get("end_date"); ?>'>
<input type='hidden' name='end_time' value='<?php echo $request->get("end_time"); ?>'>
<input type='hidden' name='r1' value='<?php echo $request->get('r1'); ?>'>
<input type='hidden' name='r2' value='<?php echo $request->get('r2'); ?>'>
<input type='hidden' name='r3' value='<?php echo $request->get('r3'); ?>'>
<input type='hidden' name='r4' value='<?php echo $request->get('r4'); ?>'>
<input type='hidden' name='r5' value='<?php echo $request->get('r5'); ?>'>
<input type='hidden' name='ikisaki' value='<?php echo $request->get("ikisaki"); ?>'>
<input type='hidden' name='tokubetu_sonota' value='<?php echo $request->get("tokubetu_sonota"); ?>'>
<input type='hidden' name='hurikae' value='<?php echo $request->get("hurikae"); ?>'>
<input type='hidden' name='syousai_sonota' value='<?php echo $request->get("syousai_sonota"); ?>'>
<input type='hidden' name='todouhuken' value='<?php echo $request->get("todouhuken"); ?>'>
<input type='hidden' name='mokuteki' value='<?php echo $request->get("mokuteki"); ?>'>
<input type='hidden' name='setto1' value='<?php echo $request->get("setto1"); ?>'>
<input type='hidden' name='setto2' value='<?php echo $request->get("setto2"); ?>'>
<input type='hidden' name='doukou' value='<?php echo $request->get("doukou"); ?>'>
<input type='hidden' name='bikoutext' value='<?php echo $request->get("bikoutext"); ?>'>
<input type='hidden' name='r6' value='<?php echo $request->get("r6"); ?>'>
<input type='hidden' name='tel_sonota' value='<?php echo $request->get("tel_sonota"); ?>'>
<input type='hidden' name='tel_no' value='<?php echo $request->get("tel_no"); ?>'>
<input type='hidden' name='jyu_date' value='<?php echo $request->get("jyu_date"); ?>'>
<input type='hidden' name='outai' value='<?php echo $request->get("outai"); ?>'>
<input type='hidden' name='c2' value='<?php echo $request->get("c2"); ?>'>

<input type='hidden' name='reappl' value='<?php echo $request->get("reappl"); ?>'>
<input type='hidden' name='deny_uid' value='<?php echo $request->get("deny_uid"); ?>'>
<input type='hidden' name='previous_date' value='<?php echo $request->get("previous_date"); ?>'>

<?php
        $date                   = $request->get("sin_date");        // ����ǯ����
        $uid                    = $request->get("syain_no");        // ������ �Ұ��ֹ�
        $start_date             = $request->get("str_date");        // ���� ���� ����
        $start_time             = $request->get("str_time");        // ���� ���� ����
        $end_date               = $request->get("end_date");        // ���� ��λ ����
        $end_time               = $request->get("end_time");        // ���� ��λ ����
        $content                = $request->get('r1');              // ���ơʥ饸��1��
        $yukyu                  = $request->get('r2');              // ���ơʥ饸��2��ͭ���Ϣ
        $ticket01               = $request->get('r3');              // ���ơʥ饸��3�˾�ַ�
        $ticket02               = $request->get('r4');              // ���ơʥ饸��4�˿�����������
        $special                = $request->get('r5');              // ���ơʥ饸��5�����̴�Ϣ
        if( $special == '��ĤA' ) {
            $special = "��Ĥ���ܿͤ��뺧 5��(������1��)";
        } else if ( $special == '��ĤB' ) {
            $special = "��Ĥ�����졦�۶��ԡ��Ҥ���˴ 5��";
        } else if ( $special == '��ĤC' ) {
            $special = "��Ĥ���۶��Ԥ����졢�ܿͤ������졢����λ�˴ 3��";
        }
        $others                 = $request->get('ikisaki');         // ���ơ�ʸ����1�˹��衦���ص���������¾
        if( $others == '' )
            $others             = $request->get('tokubetu_sonota'); // ���ơ�ʸ����1�˹��衦���ص���������¾
        if( $others == '' )
            $others             = $request->get('hurikae');         // ���ơ�ʸ����1�˹��衦���ص���������¾
        if( $others == '' )
            $others             = $request->get('syousai_sonota');  // ���ơ�ʸ����1�˹��衦���ص���������¾

        $place                  = $request->get('todouhuken');      // ���ơ�ʸ����2����ƻ�ܸ�
        $purpose                = $request->get('mokuteki');        // ���ơ�ʸ����3����Ū
        $ticket01_set           = $request->get('setto1');          // ���ơ�ʸ����4�˾�ַ����åȿ�
        $ticket02_set           = $request->get('setto2');          // ���ơ�ʸ����5�˿��������åȿ�
        $doukousya              = $request->get('doukou');          // ���ơ�ʸ����6��Ʊ�Լ�
        if( $doukousya == '' )
            $doukousya             = '---';                         // ���ơ�ʸ����6��Ʊ�Լ�
        $remarks                = $request->get('bikoutext');       // ����
        if( $remarks == '' )
            $remarks             = '---';                           // ����

        $contact                = $request->get('r6');              // Ϣ����ʥ饸����
        if( $contact == '' )
            $contact             = '---';                           // Ϣ����ʥ饸����
        $contact_other          = $request->get('tel_sonota');      // Ϣ����ʤ���¾��
        $contact_tel            = $request->get('tel_no');          // Ϣ�����TEL��

        $hurry                  = $request->get('c2');              // ��ޡʥ����å���

        $suica_view             = $request->get('suica_view');      // ����������ԲĤ��б� Suicaɽ��
?>
    <table class='pt10' border="1" cellspacing="0">
        <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table width='100%' class='winbox_field' bgcolor='#D8D8D8' align='center' border='1' cellspacing='0' cellpadding='3'>

    <?php if( trim($hurry) == "���" ) { ?>
        <caption class='pt12b' style='background-color:#FF0040; color:white;'> >>>>>����ޡ�<<<<< </caption>
    <?php } else { ?>
        <caption style='background-color:yellow; color:blue;'>�����</caption>
    <?php } ?>
    <tr>
        <td align='center'>������</td>
        <td>
            &ensp;
            <?php
                $w_date = substr($date, 0, 10);
                DayDisplay($w_date, $model);
            ?>

        </td>
    </tr>

    <tr>
        <td align='center'>������</td>
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
                $w_date = substr($start_date, 0, 4) . '-' . substr($start_date, 4, 2) . '-' . substr($start_date, 6, 2);
                DayDisplay($w_date, $model);
                if($start_date != $end_date) {
                    echo " �� ";
                    $end_date = substr($end_date, 0, 4) . '-' . substr($end_date, 4, 2) . '-' . substr($end_date, 6, 2);
                    DayDisplay($end_date, $model);
                }
            ?>
            &emsp;
            <?php
                if( $start_time ) echo $start_time;
                if( $start_time && $end_time ) echo " �� ";
                if( $end_time ) echo $end_time;
            ?>
        </td>
    </tr>

    <tr>
        <td align='center'>��&ensp;��</td>
        <td>
            &ensp;
            <?php echo $content; ?>
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
                <br>&emsp;&emsp;&emsp;<font color="red">�� Suica ���Ѥ��롣</font>
<?php
} else {
?>
        <?php if( $ticket01 != "����" && $ticket01 != "�Բ�" && $ticket01 != NULL) { ?>
        <br>
                &emsp;&emsp;��ַ��ʻ�ȡ����Եܴ֡�&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;
                <?php echo $ticket01; ?>
                <?php if( $ticket01 != "����" ) echo $ticket01_set . "���å�"; ?>
        <?php } ?>
        <?php if( $ticket02 != "����" && $ticket02 != "�Բ�" && $ticket02 != NULL ) { ?>
        <br>
                &emsp;&emsp;�������õ޷���ͳ�ʡ���ַ��ʱ��Եܡ�����֡�
                <?php echo $ticket02; ?>
                <?php if( $ticket02 != "����" ) echo $ticket02_set . "���å�"; ?>
        <?php } ?>
<?php
}
?>
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
            } else {
                ; // echo "����ʳ�";
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

        </table>
        </td></tr>
    </table> <!----------------- ���ߡ�End --------------------->
<br>

    <input type="submit" value="����" name="submit" onClick='SetCheckFlag(this.value)' disabled>&emsp;
    <input type="submit" value="���" name="submit" onClick='SetCheckFlag(this.value)' >&emsp;
<?php if( $request->get("reappl") ) { ?>
    <input type="button" value="[��]�Ĥ���" name="close" onClick='window.open("about:blank","_self").close()'>
<?php } else { ?>
    <input type="button" value="����󥻥�" name="reset" onClick='location.replace("<?php echo $menu->out_self(), '?', $model->get_htmlGETparm() ?>");'>
<?php } ?>
</form>
</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
