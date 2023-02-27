<?php
////////////////////////////////////////////////////////////////////////////////
// ����ϡʾ�ǧ��                                                             //
//                                                    MVC View �� �ꥹ��ɽ��  //
// Copyright (C) 2020-2020 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2020/11/18 Created sougou_admit_EditView.php                               //
//            �������̡�sougou_ViewList.php�ˤ�ɬ�פ˱���Ʊ������             //
// 2021/02/12 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////
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

$menu->out_html_header();
$menu->set_caption('������ɬ�פʾ��������������򤷤Ʋ�������');

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
<script type='text/javascript' language='JavaScript' src='../in_sougou/sougou.js'></script>

</head>
<body onLoad='AdmitEdit()'>
<center>

<?php $menu->set_retPOST('edit_no', $request->get('edit_no')); ?>

<?= $menu->out_title_border() ?>

<?php
    $res = array(); 
    $res = $model->getRes();

    $date                   = $res[0][0];
    $uid                    = $res[0][1];
    $start_date             = $res[0][2];
    $start_time             = $res[0][3];
    if( ! $start_time ) {
        $start_time = "08:30";
    }
    $end_date               = $res[0][4];
    $end_time               = $res[0][5];
    if( ! $end_time ) {
        $end_time = "17:15";
    }
    $content                = trim($res[0][6]);
    $yukyu                  = trim($res[0][7]);
    $ticket01               = trim($res[0][8]);
    $ticket02               = trim($res[0][9]);
    $special                = trim($res[0][10]);
    $others                 = trim($res[0][11]);
    $place                  = trim($res[0][12]);
    $purpose                = trim($res[0][13]);
    $ticket01_set           = trim($res[0][14]);
    $ticket02_set           = trim($res[0][15]);
    $doukousya              = trim($res[0][16]);
    if( $doukousya == '---') $doukousya = '';
    $remarks                = trim($res[0][17]);
    if( $remarks == '---') $remarks = '';
    $contact                = trim($res[0][18]);
    $contact_other          = trim($res[0][19]);
    $contact_tel            = trim($res[0][20]);
    $received_phone         = trim($res[0][21]);
    $received_phone_date    = trim($res[0][22]);
    $received_phone_name    = trim($res[0][23]);
    $hurry                  = trim($res[0][24]);
    $ticket                 = $res[0][25];
    $admit_status           = trim($res[0][26]);
    $amano_input            = $res[0][27];

    $suica_view             = $request->get('suica_view');  // ����������ԲĤ��б� Suicaɽ��
?>
    <br>
    <table class='pt10' border="1" cellspacing="0">
    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
    <table width='100%' class='winbox_field' bgcolor='#D8D8D8' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                        <!--  bgcolor='#ffffc6' �������� --> 
                    <td class='winbox' style='background-color:yellow; color:blue;' colspan='2' align='center'>
                        <div class='caption_font'><?php echo $menu->out_caption(), "\n"?></div>
                    </td>
                </tr>
    <tr>
        <td align='center'>������</td>
        <td>
        &ensp;
            <?php
                DayDisplay(substr($date, 0, 10), $model);
                echo substr($date, 10, 6);
            ?>
            <?php
/* �Ŀ;���δ�������ɽ�����ʤ������ɤ��� */
            $yukyudata = $model->getYukyu();
            if( $yukyudata[0][4] == 0 ) {
//                echo "��ͭ�����<font color='red'>���ߡ����ƥʥ���Ǥ�</font>����";
                ?>
                <script>alert("�����ϡ���λ���֤μ�ư���꤬��������ǽ���Ƥ��ޤ���\n\n�������κݤϡ����ϡ���λ���֤�褯��ǧ���Ʋ�������");</script>
                <?php
                $twork = 8; $swork = 3; $ework = 5;
                $kyuka_jisseki = 0;
                $kyuka_yotei_1 = 0;
                $kyuka_yotei_2 = 0;
            } else {
//                echo "��ͭ��ġ�<font color='red'>{$yukyudata[0][1]}</font>/{$yukyudata[0][0]}���ۡ�Ⱦ�١�<font color='red'>{$yukyudata[0][2]}</font>/12�󡡻��ֵ١�<font color='red'>{$yukyudata[0][3]}</font>/{$yukyudata[0][4]}���֡�";
//                echo "<BR><font class='pt10' color='red'>����������������������������������������������ͭ��Ĥϡ���������ꤢ�Ȥ�ͽ��ʬ��ޤޤ�Ƥ��ޤ���</font>";
                $twork = $yukyudata[0][4] / 5;    // ���Ȼ��� 8 or 7
                if( $uid == '300349' && $request->get('act_id') == '670' ) {
                    $swork = 2; // 9:15 ���Ϥο͡ʾ��ɡ�¼���
                } else {
                    $swork = 3; // 8:30 ���Ϥο�
                }
                $ework = $twork - $swork; // 
                $kyuka_jisseki = $model->KeikakuCnt();
                $kyuka_yotei_1 = $model->YoteiKyuka( $uid, substr($date, 0, 10), $start_date, $end_date, true);
                $kyuka_yotei_2 = $model->YoteiKyuka( $uid, substr($date, 0, 10), $start_date, $end_date, false);
            }
/**/
            ?>
            <input type='hidden' name='k_jisseki' id='id_k_jisseki' value='<?php echo $kyuka_jisseki; ?>'>
            <input type='hidden' name='k_yotei_1' id='id_k_yotei_1' value='<?php echo $kyuka_yotei_1; ?>'>
            <input type='hidden' name='k_yotei_2' id='id_k_yotei_2' value='<?php echo $kyuka_yotei_2; ?>'>

            <input type='hidden' name='t_work' id='id_t_work' value='<?php echo $twork; ?>'>
            <input type='hidden' name='s_work' id='id_s_work' value='<?php echo $swork; ?>'>
            <input type='hidden' name='e_work' id='id_e_work' value='<?php echo $ework; ?>'>
            <script>SetDefTime();</script>
        </td>
    </tr>

    <tr>
        <td align='center'>������</td>
        <td align='center'>
            <?php echo $model->getSyozoku($uid); ?>
            &emsp;
            <?php echo "�Ұ��ֹ桧" . $uid; ?>
            &emsp;
            <?php echo '��̾��'. $model->getSyainName($uid); ?>
        </td>
    </tr>

<form name='form_edit' method='post' action='<?php echo $menu->out_self(); ?>' onSubmit='return allcheck()'>

    <input type='hidden' name='edit_no' value="<?php echo $request->get('edit_no'); ?>">
    <input type='hidden' name='sin_date' value='<?php echo $date; ?>'>
    <input type='hidden' name='sin_year' id='sin_year' value='<?php echo substr($date, 0, 4); ?>'>
    <input type='hidden' name='sin_month' id='sin_month' value='<?php echo substr($date, 5, 2); ?>'>
    <input type='hidden' name='sin_day' id='sin_day' value='<?php echo substr($date, 8, 2); ?>'>
    <input type='hidden' name='sin_hour' id='sin_hour' value='<?php echo substr($date, 11, 2); ?>'>
    <input type='hidden' name='sin_minute' id='sin_minute' value='<?php echo substr($date, 14, 2); ?>'>
    <input type='hidden' name='syain_no' value='<?php echo $uid; ?>'>

    <tr>
        <td align='center'>��&ensp;��</td>
        <td align='center'>
            <?php $year = substr($start_date, 0, 4) ?>
            <?php $month = substr($start_date, 5, 2) ?>
            <?php $day = substr($start_date, 8, 2) ?>
            <!-- ��ҥ��������ε���������������javascript���ѿ��إ��åȤ��Ƥ�����-->
            <?php $holiday = json_encode($model->getHolidayRang($year-1,$year+1)); ?>
            <script> var holiday = '<?php echo $holiday; ?>';  SetHoliday(holiday);</script>
            <!-- -->
            <input type="checkbox" name="c0" id="0" value="1��" <?php if($start_date == $end_date) echo " checked" ?> onclick="OneDay(this.checked);"><label for="0">1��</label>
            <select name="ddlist" id="id_syear" onclick="StartDateCopy()">
                <?php SelectOptionDate($year-1, $year+1, $year); ?>
            </select>ǯ
            <select name="ddlist" id="id_smonth" onclick="StartDateCopy()">
                <?php SelectOptionDate(1, 12, $month); ?>
            </select>��
            <select name="ddlist" id="id_sday" onclick="StartDateCopy()">
                <?php SelectOptionDate(1, 31, $day); ?>
            </select>��
            <font id='id_s_youbi'></font>
            <input type='hidden' name='str_date' value='<?php echo $start_date; ?>'>

            <?php $year = substr($end_date, 0, 4) ?>
            <?php $month = substr($end_date, 5, 2) ?>
            <?php $day = substr($end_date, 8, 2) ?>
            <font id='id_1000' > ��&ensp;
            <select name="ddlist" id="id_eyear" onclick="EndDateCopy()">
                <?php SelectOptionDate($year-1, $year+1, $year); ?>
            </select>ǯ
            <select name="ddlist" id="id_emonth" onclick="EndDateCopy()">
                <?php SelectOptionDate(1, 12, $month); ?>
            </select>��
            <select name="ddlist" id="id_eday" onclick="EndDateCopy()">
                <?php SelectOptionDate(1, 31, $day); ?>
            </select>��
            <font id='id_e_youbi'></font>
            <input type='hidden' name='end_date' value='<?php echo $end_date; ?>'>
            </font>
        <br><br>
            <?php $hh = substr($start_time, 0, 2); ?>
            <?php $mm = substr($start_time, 3, 2); ?>
            <font id='id_start_time_area'>
            <input type="radio" name="r0" id="001"><label for="001">����</label>
            <select name="ddlist" id="id_shh" onblur="StartTimeCopy()">
                <?php SelectOptionTime(0, 23, $hh); ?>
            </select>��
            <select name="ddlist" id="id_smm" onblur="StartTimeCopy()">
                <?php SelectOptionTime(0, 59, $mm); ?>
            </select>ʬ
            <input type='hidden' name='str_time' value='<?php echo $start_time; ?>'>
            </font>
            <font id='id_time_area'>
            ��
            </font>
            <?php $hh = substr($end_time, 0, 2) ?>
            <?php $mm = substr($end_time, 3, 2) ?>
            <font id='id_end_time_area'>
            <input type="radio" name="r0" id="002"><label for="002">��λ</label>
            <select name="ddlist" id="id_ehh" onblur="EndTimeCopy()">
                <?php SelectOptionTime(0, 23, $hh); ?>
            </select>��
            <select name="ddlist" id="id_emm" onblur="EndTimeCopy()">
                <?php SelectOptionTime(0, 59, $mm); ?>
            </select>ʬ
            <input type='hidden' name='end_time' value='<?php echo $end_time; ?>'>
            </font>

            <font id='id_time_sum_area'>
            <label for="001">����</label> or <label for="002">��λ</label>���<input type="text" size="2" maxlength="2" name="sum_hour" id="id_sum_hour" onkeyup="value = value.replace(/[^0-9]/,'');">����
            <input type="button" value="�׻�" name="sum" id="id_sum" onClick='TimeCalculation()'>
            </font>
        </td>
    </tr>

    <tr><td align='center'>��&ensp;��</td>
        <td>
        <input type="radio" name="r1" id="101" onClick="syousai();" value="ͭ��ٲ�" <?php if($content=="ͭ��ٲ�") echo " checked"; ?>><label for="101">ͭ��ٲ�</label>
        <input type="radio" name="r1" id="102" onClick="syousai();" value="AMȾ��ͭ��ٲ�" <?php if($content=="AMȾ��ͭ��ٲ�") echo " checked"; ?>><label for="102">AMȾ��ͭ��ٲ�</label>
        <input type="radio" name="r1" id="103" onClick="syousai();" value="PMȾ��ͭ��ٲ�" <?php if($content=="PMȾ��ͭ��ٲ�") echo " checked"; ?>><label for="103">PMȾ��ͭ��ٲ�</label>
        <input type="radio" name="r1" id="104" onClick="syousai();" value="����ñ��ͭ��ٲ�" <?php if($content=="����ñ��ͭ��ٲ�") echo " checked"; ?>><label for="104">����ñ��ͭ��ٲ�</label>
        <input type="radio" name="r1" id="105" onClick="syousai();" value="���" <?php if($content=="���") echo " checked"; ?>><label for="105">���</label>
        <input type="radio" name="r1" id="106" onClick="syousai();" value="�ٹ�����" <?php if($content=="�ٹ�����") echo " checked"; ?>><label for="106">�ٹ�����</label>
            <table class='pt10' border="1" cellspacing="1" align='center' id='1000'>
            <caption></caption>
            <tr><td>
            <input type="radio" name="r2" id="201" value="�̱����ܿ͡�" <?php if($yukyu=="�̱����ܿ͡�") echo " checked"; ?>><label for="201">�̱����ܿ͡�</label>
            <input type="radio" name="r2" id="202" value="��Ĵ���ɡ��ܿ͡�" <?php if($yukyu=="��Ĵ���ɡ��ܿ͡�") echo " checked"; ?>><label for="202">��Ĵ���ɡ��ܿ͡�</label>
            <input type="radio" name="r2" id="203" value="�ع��Ի�" <?php if($yukyu=="�ع��Ի�") echo " checked"; ?>><label for="203">�ع��Ի�</label>
            <input type="radio" name="r2" id="204" value="��������" <?php if($yukyu=="��������") echo " checked"; ?>><label for="204">��������</label>
            <input type="radio" name="r2" id="205" value="����Թ�" <?php if($yukyu=="����Թ�") echo " checked"; ?>><label for="205">����Թ�</label>
            <br>
            <input type="radio" name="r2" id="206" value="�̱��ʲ�²��" <?php if($yukyu=="�̱��ʲ�²��") echo " checked"; ?>><label for="206">�̱��ʲ�²��</label>
            <input type="radio" name="r2" id="207" value="��Ĵ���ɡʲ�²��" <?php if($yukyu=="��Ĵ���ɡʲ�²��") echo " checked"; ?>><label for="207">��Ĵ���ɡʲ�²��</label>
            <input type="radio" name="r2" id="208" value="�������" <?php if($yukyu=="�������") echo " checked"; ?>><label for="208">�������</label>
            <input type="radio" name="r2" id="209" value="�ײ�ͭ��" onClick="Iskeikaku();" <?php if($yukyu=="�ײ�ͭ��") echo " checked"; ?>><label for="209" id="keikaku">�ײ�ͭ��</label>
            <input type="radio" name="r2" id="210" value="���̷ײ�" <?php if($yukyu=="���̷ײ�") echo " checked"; ?>><label for="210" id="tokukei">���̷ײ�</label>
            </td></tr>
            </table>
            <br>

        <!-- �ޤꤿ����Ÿ���ܥ��� -->
        <div onclick="obj=document.getElementById('menu1').style; obj.display=(obj.display=='none')?'block':'none';">
        <a class='pt12b' style="cursor:pointer;">�� ��ĥ��Ϣ�ʥ���å���Ÿ����</a>
        </div>
        <!--// �ޤꤿ����Ÿ���ܥ��� -->

        <!-- ������������ޤꤿ���� -->
        <div id="menu1" style="display:none;clear:both;font-size:12pt;font-weight:normal;">

        <!--������ʬ���ޤꤿ���ޤ졢Ÿ���ܥ���򥯥�å����뤳�Ȥ�Ÿ�����ޤ���-->
        <input type="radio" name="r1" id="107" onClick="syousai();" value="��ĥ���������" <?php if($content=="��ĥ���������") echo " checked"; ?>><label for="107">��ĥ���������</label>
        &emsp;&emsp;&ensp;
        <input type="radio" name="r1" id="108" onClick="syousai();" value="��ĥ�ʽ����" <?php if($content=="��ĥ�ʽ����") echo " checked"; ?>><label for="108">��ĥ�ʽ����</label>
        <br>
        <input type="radio" name="r1" id="109" onClick="syousai();" value="ľ��" <?php if($content=="ľ��") echo " checked"; ?>><label for="109">ľ��</label>
        &emsp; &emsp; &emsp; &emsp; &nbsp; &thinsp;
        <input type="radio" name="r1" id="110" onClick="syousai();" value="ľ��" <?php if($content=="ľ��") echo " checked"; ?>><label for="110">ľ��</label>
        &emsp; &emsp; &emsp; &emsp; &nbsp; &thinsp;
        <input type="radio" name="r1" id="111" onClick="syousai();" value="ľ��/ľ��" <?php if($content=="ľ��/ľ��") echo " checked"; ?>><label for="111">ľ��/ľ��</label>
        <p class='pt10' align='center' id='2000'>
        &emsp;
<?php
if( $suica_view == 'on' ) {    // ����������ԲĤ��б�
?>
            ���衧<input type="text" size="46" maxlength="24" name="ikisaki" value='<?php echo $others; ?>' onchange="value = SpecialText(this)">
            ��ƻ�ܸ���<input type="text" size="18" maxlength="10" name="todouhuken" value='<?php echo $place; ?>'>
            <br><br>��Ū��<input type="text" size="78" maxlength="32" name="mokuteki" value='<?php echo $purpose; ?>'>
<?php
} else {
?>
            ���衧<input type="text" size="24" maxlength="24" name="ikisaki" value='<?php echo $others; ?>'>
            ��ƻ�ܸ���<input type="text" size="10" maxlength="10" name="todouhuken" value='<?php echo $place; ?>'>
            ��Ū��<input type="text" size="24" maxlength="24" name="mokuteki" value='<?php echo $purpose; ?>'>
<?php
}
?>
        </p>
        <p class='pt9' align='center' id='2500'>
<?php
if( $suica_view == 'on' ) {    // ����������ԲĤ��б�
?>
            �� Suica �����Ѥ��ޤ�����&emsp;&emsp;&emsp;&emsp;
            <input type="radio" name="r3" id="301" onClick="suica();" value="����" <?php if($ticket01=="����") echo " checked"; ?>><label for="301">���ʤ�</label>
            <input type="radio" name="r3" id="302" onClick="suica();" value="����" <?php if($ticket01=="����") echo " checked"; ?>><label for="302">����</label>
        <br><br>
            Ʊ�Լԡ�<input type="text" size="80" maxlength="160" name="doukou" value='<?php echo $doukousya; ?>'></textarea>
        <br><br>
            <input type='hidden' name='n_suica' id='id_suica'>
            <input type='hidden' name='r4'>
            <input type='hidden' name='setto1'>
            <input type='hidden' name='setto2'>
        </p>
<?php
} else {
?>
<!-- ����������ѤǤ��ʤ��ʤä��顢�����ȥ����� -->
<!-- -->
            ����ĥ���ǲ��������Ѥ�����ϡ��ʲ��򤴳�ǧ��������
        <br><br>
            ��ַ��ʻ�ȡ����Եܴ֡�&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;
            <input type="radio" name="r3" id="301" onClick="setto()" value="����" <?php if($ticket01=="����") echo " checked"; ?>><label for="301">����</label>
            <input type="radio" name="r3" id="302" onClick="setto()" value="����" <?php if($ticket01=="����") echo " checked"; ?>><label for="302">����</label>
            <input type="radio" name="r3" id="303" onClick="setto()" value="��ƻ" <?php if($ticket01=="��ƻ") echo " checked"; ?>><label for="303">��ƻ</label>
            <input type="text" size="2" maxlength="2" name="setto1" value='<?php echo $ticket01_set; ?>' onkeyup="value = value.replace(/[^0-9]/,'');">���å�
        <br>
            �������õ޷���ͳ�ʡ���ַ��ʱ��Եܡ�����֡�
            <input type="radio" name="r4" id="401" onClick="setto()" value="����" <?php if($ticket02=="����") echo " checked"; ?>><label for="401">����</label>
            <input type="radio" name="r4" id="402" onClick="setto()" value="����" <?php if($ticket02=="����") echo " checked"; ?>><label for="402">����</label>
            <input type="radio" name="r4" id="403" onClick="setto()" value="��ƻ" <?php if($ticket02=="��ƻ") echo " checked"; ?>><label for="403">��ƻ</label>
            <input type="text" size="2" maxlength="2" name="setto2" value='<?php echo $ticket02_set; ?>' onkeyup="value = value.replace(/[^0-9]/,'');">���å�
        <br>
<!-- ����������ѤǤ��ʤ��ʤä��顢�ʲ�����ߡ��ǻ��Ѥ���Х��顼��ȯ�����ʤ��� -->
<!--
            <input type='hidden' name='r3'> <input type='hidden' name='r4'>
            <input type='hidden' name='setto1'> <input type='hidden' name='setto2'>
<!-- -->
            Ʊ�Լԡ�<input type="text" size="80" maxlength="160" name="doukou" value='<?php echo $doukousya; ?>'></textarea>
        <br><br>����ĥ�����������������ϡ����� <a color="red" id="2550">������������</a> ����Ф��Ʋ�������</p>
<?php
}
?>
        </div>
        <!--// �����ޤǤ��ޤꤿ���� -->

        <!-- �ޤꤿ����Ÿ���ܥ��� -->
        <div onclick="obj=document.getElementById('menu2').style; obj.display=(obj.display=='none')?'block':'none';">
        <a class='pt12b' style="cursor:pointer;">�� ���̵ٲ˴�Ϣ�ʥ���å���Ÿ����</a>
        </div>
        <!--// �ޤꤿ����Ÿ���ܥ��� -->

        <!-- ������������ޤꤿ���� -->
        <div id="menu2" style="display:none;clear:both;font-size:12pt;font-weight:normal;">

        <!--������ʬ���ޤꤿ���ޤ졢Ÿ���ܥ���򥯥�å����뤳�Ȥ�Ÿ�����ޤ���-->
        <input type="radio" name="r1" id="112" onClick="syousai();" value="���̵ٲ�" <?php if($content=="���̵ٲ�") echo " checked"; ?>><label for="112">���̵ٲ�</label>
            <table class='pt10' border="1" cellspacing="1" align='center' id='3000'>
            <caption></caption>
            <tr><td>
            <input type="radio" name="r5" id="501" onClick="toku()" value="��ĤA" <?php if($special=="��ĤA") echo " checked"; ?>><label for="501">��Ĥ���ܿͤ��뺧 5��(������1��)</label>
            <br>
            <input type="radio" name="r5" id="502" onClick="toku()" value="��ĤB" <?php if($special=="��ĤB") echo " checked"; ?>><label for="502">��Ĥ�����졦�۶��ԡ��Ҥ���˴ 5��</label>
            <br>
            <input type="radio" name="r5" id="503" onClick="toku()" value="��ĤC" <?php if($special=="��ĤC") echo " checked"; ?>><label for="503">��Ĥ���۶��Ԥ����졢�ܿͤ������졢����λ�˴ 3��</label>
            <br>
            <input type="radio" name="r5" id="504" onClick="toku()" value="��̱���ιԻ�" <?php if($special=="��̱���ιԻ�") echo " checked"; ?>><label for="504">��̱���ιԻ�</label>
            <input type="radio" name="r5" id="505" onClick="toku()" value="��³��30ǯ" <?php if($special=="��³��30ǯ") echo " checked"; ?>><label for="505">��³��30ǯ 5��</label>
            <input type="radio" name="r5" id="506" onClick="toku()" value="����¾" <?php if($special=="����¾") echo " checked"; ?>><label for="506">����¾��<input type="text" name="tokubetu_sonota" value='<?php echo $others; ?>'></label>
            </td></tr>
            </table>
        </div>
        <!--// �����ޤǤ��ޤꤿ���� -->

        <br>
        <input type="radio" name="r1" id="113" onClick="syousai();" value="���ص���" <?php if($content=="���ص���") echo " checked"; ?>><label for="113">���ص����� �� ���ж�ʬ��<input type="text" size="30" name="hurikae" value='<?php echo $others; ?>'>��</label>
        &emsp;&emsp;&emsp;&ensp;
        <input type="radio" name="r1" id="114" onClick="syousai();" value="�����ٲ�" <?php if($content=="�����ٲ�") echo " checked"; ?>><label for="114">�����ٲ�</label>
        <br>
        <input type="radio" name="r1" id="115" onClick="syousai();" value="ID�������̤�˺��ʽжС�" <?php if($content=="ID�������̤�˺��ʽжС�") echo " checked"; ?>><label for="115">ID�������̤�˺��ʽжС�</label>
        &emsp;&emsp;
        <input type="radio" name="r1" id="116" onClick="syousai();" value="ID�������̤�˺�����С�" <?php if($content=="ID�������̤�˺�����С�") echo " checked"; ?>><label for="116">ID�������̤�˺�����С�</label>
        <br>
        <input type="radio" name="r1" id="117" onClick="syousai();" value="���¾�ǧ˺��ʻĶȿ���ϳ���" <?php if($content=="���¾�ǧ˺��ʻĶȿ���ϳ���") echo " checked"; ?>><label for="117">���¾�ǧ˺��ʻĶȿ���ϳ���</label>
        <br>
        <input type="radio" name="r1" id="118" onClick="syousai();" value="ID�������̤�˺�����Сˡ� ���¾�ǧ˺��ʻĶȿ���ϳ���" <?php if($content=="ID�������̤�˺�����Сˡ� ���¾�ǧ˺��ʻĶȿ���ϳ���") echo " checked"; ?>><label for="118">ID�������̤�˺�����Сˡ� ���¾�ǧ˺��ʻĶȿ���ϳ���</label>
        <input type="radio" name="r1" id="119" onClick="syousai();" value="����¾" <?php if($content=="����¾") echo " checked"; ?>><label for="119">����¾��<input type="text" name="syousai_sonota" value='<?php echo $others; ?>'></label>
        </td>
    </tr>

    <input type='hidden' name='content_no' id='id_content_no' value='-1'>

    <tr>
        <td align='center'>��&ensp;��</td>
        <td><input type="text" size="100" maxlength="40" name="bikoutext" value='<?php echo $remarks; ?>'> ������40��</td>
    </tr>

    <tr id='id_renraku'>
        <td align='center'>Ϣ����</td>
        <td>
            <input type="radio" name="r6" id="601" onclick="telno();" value="����" <?php if($contact=="����") echo " checked"; ?>><label for="601">����</label>
            <input type="radio" name="r6" id="602" onclick="telno();" value="����" <?php if($contact=="����") echo " checked"; ?>><label for="602">����</label>
            <input type="radio" name="r6" id="603" onclick="telno();" value="��ĥ��" <?php if($contact=="��ĥ��") echo " checked"; ?>><label for="603">��ĥ��</label>
            <input type="radio" name="r6" id="604" onclick="telno();" value="����¾" <?php if($contact=="����¾") echo " checked"; ?>><label for="604">����¾��<input type="text" size="6" maxlength="6" name="tel_sonota" value='<?php echo $contact_other; ?>'></label>
            <font id='id_tel_no'>TEL</font><input type="text" name="tel_no" maxlength="13" onkeyup="value = value.replace(/[^0-9,-]+/i,'');" value='<?php echo $contact_tel; ?>'>
        </td>
    </tr>

<!-- -->
    <tr id='id_jyuden'>
        <td align='center'>
            �����ż�
        </td>
        <td>
            <?php if($received_phone_date) $year = substr($received_phone_date, 0, 4); else $year = substr($date, 0, 4); ?>
            <?php if($received_phone_date) $month = substr($received_phone_date, 5, 2); else $month = substr($date, 5, 2); ?>
            <?php if($received_phone_date) $day = substr($received_phone_date, 8, 2); else $day = substr($date, 8, 2); ?>
            <?php if($received_phone_date) $hh = substr($received_phone_date, 11, 2); else $hh = 8; ?>
            <?php if($received_phone_date) $mm = substr($received_phone_date, 14, 2); else $mm = 30; ?>
            ����������
                <select name="ddlist_jyu" id="id_jyear" onclick="JyuDateCopy()">
                    <?php SelectOptionDate($year-1, $year, $year); ?>
                </select>ǯ
                <select name="ddlist_jyu" id="id_jmonth" onclick="JyuDateCopy()">
                    <?php SelectOptionDate(1, 12, $month); ?>
                </select>��
                <select name="ddlist_jyu" id="id_jday" onclick="JyuDateCopy()">
                    <?php SelectOptionDate(1, 31, $day); ?>
                </select>��
                <font id='id_j_youbi'></font>&ensp;
                <select name="ddlist_jyu" id="id_jhh" onblur="JyuDateCopy()">
                    <?php SelectOptionTime(0, 23, $hh); ?>
                </select>��
                <select name="ddlist_jyu" id="id_jmm" onblur="JyuDateCopy()">
                    <?php SelectOptionTime(0, 59, $mm); ?>
                </select>ʬ
                <input type='hidden' name='jyu_date' value=''>

            ���мԡ�<input type="text" size="16" maxlength="8" name="outai" value='<?php echo $received_phone_name; ?>' onMouseover="Coment.style.visibility='visible'" onMouseout="Coment.style.visibility='hidden'" title="">
                <div id="Coment" style="color:#000000; background:#e7e7e7; font-size='9pt'; position:absolute; top:; left:; width:150; padding:5; visibility:hidden; filter:alpha(opacity='80');">
                    [�Ұ��ֹ�] or [̾��]
                </div>

        </td>
    </tr>
<!-- -->

    </table>
    </td></tr>
    </table> <!----------------- ���ߡ�End --------------------->

    <p align='center'>
        <input type='hidden' name='sougou_update' value='off'>
        <input type="checkbox" name="c2" id="idc2" value="���" <?php if($hurry=="���") echo " checked"; ?>><label for="idc2" id="idc2l" >���</label>
        <input type="submit" value="����" name="submit" onClick='SougouUpdate()'>
        <input type="button" value="����󥻥�" name="cancel" onClick='location.replace("<?php echo $menu->out_self(), '?edit_no=' . $request->get('edit_no') ?>");'>
    </p>
</form>


</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
