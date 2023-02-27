<?php
////////////////////////////////////////////////////////////////////////////////
// ����ϡʿ�����                                                             //
//                                                    MVC View �� �ꥹ��ɽ��  //
// Copyright (C) 2020-2020 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2020/11/18 Created sougou_ViewList.php                                     //
//            ��ǧ���Խ����̡�sougou_admit_EditView.php�ˤ�ɬ�פ˱���Ʊ������ //
// 2021/02/12 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////

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

$menu->out_html_header();

if( $request->get('showMenu') == 'Re') {    // �ƿ���
    if( !$model->GetReViewData($request) ) {        // �������������
        ?>
        <script>alert("��ǧ���줿��������μ����˼��Ԥ��ޤ�����"); window.open("about:blank","_self").close();</script>
        <?php
    }
    if( !$model->IsReApplPossible($request) ) {
        ?>
        <script>alert("���ˡ��ƿ����ѤߤǤ���"); window.open("about:blank","_self").close();</script>
        <?php
    }
    if( ! $model->IsDelPossible($request) ) {
        ?>
        <script>alert("���ˡ���úѤߤǤ���");window.open("about:blank","_self").close()</script>
        <?php         
    }
}

if( !$model->IsSyain() ) {
    $menu->set_caption('�Ұ��ֹ�����Ϥ��Ʋ�������');
    $dis = " disabled";
} else {
    $menu->set_caption('������ɬ�פʾ��������������򤷤Ʋ�������');
    $dis = "";
}

    $res = array(); 
    $indx = $model->getIndx();
    $rows = $model->getRows();
    $res = $model->getRes();

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
        $remarks                = $request->get('bikoutext');       // ����
        $contact                = $request->get('r6');              // Ϣ����ʥ饸����
        $contact_other          = $request->get('tel_sonota');      // Ϣ����ʤ���¾��
        $contact_tel            = $request->get('tel_no');          // Ϣ�����TEL��

        $hurry                  = $request->get('c2');              // ��ޡʥ����å���

?>
<input type='hidden' name='rows' value=<?php echo $rows; ?>>
<input type='hidden' name='indx' value=<?php echo $indx; ?>>

<?php for( $r=0; $r<$rows; $r++ ) { ?>
    <?php $posname = sprintf("res-%s[]", $r); ?>
    <?php for( $i=0; $i<$indx; $i++ ) { ?>
        <input type='hidden' name='<?php echo $posname; ?>' value='<?php echo trim($res[$r][$i]); ?>'>
    <?php } ?>
<?php } ?>

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

<?php if( $model->IsKeiyaku($syainbangou) ) { ?>
    <input type='hidden' name='keiyaku' id='id_keiyaku'>
<?php } ?>

<?php if($request->get('check_flag')=="replay") { ?>
<body onLoad='ReDisp()'>
<?php } else if( $request->get('showMenu') == 'Re') { ?>
<body onLoad='ReInit()'>
<?php } else { ?>
<body onLoad='Init(); setInterval("blink_disp(\"blink_item\")", 1000);'>
<?php } ?>

<center>
<?php if( $request->get("reappl") ) { ?>
<?php } else { ?>
    <?= $menu->out_title_border() ?>
<?php } ?>
<!-- �Уģƥե�����򳫤�-->
    <div class='pt10' align='center'>
    <br>��������ˡ��ʬ����ʤ���硢<a href="download_file.php/����ϡʿ�����.pdf">������</a> �򻲹ͤ˿������Ʋ�������<font color='red'>����)</font>IE�ʳ��Υ֥饦���Ǥ�������ư��ޤ���<br><br>
<?php if( date('Ymd') > '20211001' ) {    // ����������ԲĤ��б� ?>
    <font color='red'>�ڤ��Τ餻��</font><font id='blink_item'>[10/14 ����]</font>��<font color='red'>���̷ײ�ͭ��</font>���������ݤϡ֡�ͭ��ٲˡע��֡����̷ײ�פ����򤷤Ʋ�������<br><br>
<?php } else { ?>
    <font color='red'>�ڤ��Τ餻��</font><font id='blink_item'>[ 7/ 1 ����]</font>��������ѻߤ�ȼ����Suica ���ѹ��Ȥʤ�ޤ����ʿ����㡧P.12 ������<br><br>
<?php } ?>
    </div>
<!-- -->
    <table class='pt10' border="1" cellspacing="0">
    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table width='100%' class='winbox_field' bgcolor='#D8D8D8' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr>
                    <!--  bgcolor='#ffffc6' �������� --> 
                <td class='winbox' style='background-color:yellow; color:blue;' colspan='2' align='center'>
                    <div class='caption_font'><?php echo $menu->out_caption(), "\n"?></div>
                </td>
            </tr>
<!-- ������ -->
    <tr>
        <td nowrap align='center'>������</td>
        <td>
        &ensp;
            <script>sinseibi();</script>
            <?php
/* �Ŀ;���δ�������ɽ�����ʤ������ɤ��� */
            $yukyudata = $model->getYukyu();
            if( $model->IsSyain() && $yukyudata[0][4] == 0 ) {
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
                if( $model->IsSyain() ) {
//                    echo "<BR><font class='pt10' color='red'>����������������������������������������������ͭ��Ĥϡ���������ꤢ�Ȥ�ͽ��ʬ��ޤޤ�Ƥ��ޤ���</font>";
                }
                $twork = $yukyudata[0][4] / 5;    // ���Ȼ��� 8 or 7
                if( $request->get('syainbangou') == '300349' && $request->get('act_id') == '670') {
                    $swork = 2; // 9:15 ���Ϥο͡ʾ��ɡ�¼���
                } else {
                    $swork = 3; // 8:30 ���Ϥο�
                }
                $ework = $twork - $swork; // 
                $kyuka_jisseki = $model->KeikakuCnt() - $model->GetSpecialPlans(date('Ymd'));
                $kyuka_yotei_1 = $model->YoteiKyuka( date('Ymd'), true);
                $kyuka_yotei_2 = $model->YoteiKyuka( date('Ymd'), false);
            }

if( $request->get('syainbangou') == '300667' ) {
//echo "TEST : " . $kyuka_jisseki . " : ". $kyuka_yotei_1 . " : ". $kyuka_yotei_2;
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

    <form name="sinseisya" method="post" action='<?php echo $menu->out_self(), '?', $model->get_htmlGETparm() ?>'>
<!-- ������ -->
        <tr>
            <td nowrap align='center'>������</td>
            <td align='center'>
                <?php echo $request->get('syozoku'); ?>
                &emsp;
            <?php if( !$model->IsSyain() ) { ?>
                �Ұ��ֹ桧<input type="text" size="8" maxlength="6" name="syainbangou" onkeyup="value = value.replace(/[^0-9]+/i,'');" onchange="submit()">
            <?php } else { ?>
                <?php echo "�Ұ��ֹ桧" . $request->get('syainbangou'); ?>
            <?php } ?>
                &emsp;

                <?php echo '��̾��'.$request->get('simei'); ?>
            </td>
        </tr>
    </form>

    <?php $showMenu = 'Check' ?>
    <form name='form_sougou' method='post' action='<?php echo $menu->out_self(),"?showMenu=" . $showMenu ?>' onSubmit='return allcheck()'>

        <input type='hidden' name='sin_date' value='<?php echo $date; ?>'>
        <input type='hidden' name='sin_year' id='sin_year'>
        <input type='hidden' name='sin_month' id='sin_month'>
        <input type='hidden' name='sin_day' id='sin_day'>
        <input type='hidden' name='sin_hour' id='sin_hour'>
        <input type='hidden' name='sin_minute' id='sin_minute'>
        <input type='hidden' name='syain_no' value='<?php echo $request->get('syainbangou'); ?>'>
        <input type='hidden' name='approval' value='<?php echo $model->IsApproval(); ?>'>

<?php
if( date('Ymd') > '20210630' ) {    // ����������ԲĤ��б�
        echo "<input type='hidden' name='suica_view' value='on'>";
        $suica_view = 'on';
} else {
        $suica_view = '';
}
?>

<!-- ���� -->
    <?php if( !$model->IsSyain() || !$model->IsApproval() ) { ?>
        <tr disabled=true>
    <?php } else { ?>
        <tr>
    <?php } ?>
        <td nowrap align='center'>��&ensp;��</td>
        <!-- ��ҥ��������ε���������������javascript���ѿ��إ��åȤ��Ƥ�����-->
        <?php $holiday = json_encode($model->getHolidayRang(date('Y')-1,date('Y')+1)); ?>
        <script> var holiday = '<?php echo $holiday; ?>';  SetHoliday(holiday);</script>
        <!-- -->
        <td align='center'>
            <input type="checkbox" name="c0" id="0" value="1��" <?php if($start_date == $end_date) echo " checked" ?> onclick="OneDay(this.checked);" <?php echo $dis; ?>><label for="0">1��</label>
                <?php
                    if( $start_date ) {
                        $def_y = substr($start_date, 0, 4);
                        $def_m = substr($start_date, 4, 2);
                        $def_d = substr($start_date, -2, 2);
                    } else {
                        $def_y = date('Y');
                        $def_m = date('m');
                        $def_d = date('d');
                    }
                ?>
            <select name="ddlist" id="id_syear" onclick="StartDateCopy()" <?php echo $dis; ?>>
                <?php SelectOptionDate(date('Y')-1, date('Y')+1, $def_y); ?>
            </select>ǯ
            <select name="ddlist" id="id_smonth" onclick="StartDateCopy()" <?php echo $dis; ?>>
                <?php SelectOptionDate(1, 12, $def_m); ?>
            </select>��
            <select name="ddlist" id="id_sday" onclick="StartDateCopy()" <?php echo $dis; ?>>
                <?php SelectOptionDate(1, 31, $def_d); ?>
            </select>��
            <font id='id_s_youbi'></font>
            <input type='hidden' name='str_date' value='<?php echo $str_date; ?>'>

            <font id='id_1000' > ��&ensp;
                <?php
                    if( $end_date ) {
                        $def_y = substr($end_date, 0, 4);
                        $def_m = substr($end_date, 4, 2);
                        $def_d = substr($end_date, -2, 2);
                    } else {
                        $def_y = date('Y');
                        $def_m = date('m');
                        $def_d = date('d');
                    }
                ?>
            <select name="ddlist" id="id_eyear" onclick="EndDateCopy()" <?php echo $dis; ?>>
                <?php SelectOptionDate(date('Y')-1, date('Y')+1, $def_y); ?>
            </select>ǯ
            <select name="ddlist" id="id_emonth" onclick="EndDateCopy()" <?php echo $dis; ?>>
                <?php SelectOptionDate(1, 12, $def_m); ?>
            </select>��
            <select name="ddlist" id="id_eday" onclick="EndDateCopy()" <?php echo $dis; ?>>
                <?php SelectOptionDate(1, 31, $def_d); ?>
            </select>��
            <font id='id_e_youbi'></font>
            <input type='hidden' name='end_date' value='<?php echo $end_date; ?>'>
            </font>
        <br><br>
                <?php
                    if( $start_time ) {
                        $def_y = substr($start_time, 0, 2);
                        $def_m = substr($start_time, -2, 2);
                    } else {
                        $def_y = 8;
                        $def_m = 30;
                    }
                ?>
            <font id='id_start_time_area'>
            <input type="radio" name="r0" id="001" <?php echo $dis; ?>><label for="001">����</label>
            <select name="ddlist" id="id_shh" onblur="StartTimeCopy()" <?php echo $dis; ?>>
                <?php SelectOptionTime(0, 23, $def_y); ?>
            </select>��
            <select name="ddlist" id="id_smm" onblur="StartTimeCopy()" <?php echo $dis; ?>>
                <?php SelectOptionTime(0, 59, $def_m); ?>
            </select>ʬ
            <input type='hidden' name='str_time' value='<?php echo $str_time; ?>'>
            </font>
            <font id='id_time_area'>
            ��
            </font>
                <?php
                    if( $end_time ) {
                        $def_y = substr($end_time, 0, 2);
                        $def_m = substr($end_time, -2, 2);
                    } else {
                        $def_y = 17;
                        $def_m = 15;
                    }
                ?>
            <font id='id_end_time_area'>
            <input type="radio" name="r0" id="002" <?php echo $dis; ?>><label for="002">��λ</label>
            <select name="ddlist" id="id_ehh" onblur="EndTimeCopy()" <?php echo $dis; ?>>
                <?php SelectOptionTime(0, 23, $def_y); ?>
            </select>��
            <select name="ddlist" id="id_emm" onblur="EndTimeCopy()" <?php echo $dis; ?>>
                <?php SelectOptionTime(0, 59, $def_m); ?>
            </select>ʬ
            <input type='hidden' name='end_time' value='<?php echo $end_time; ?>'>
            </font>

            <font id='id_time_sum_area'>
            <label for="001">����</label> or <label for="002">��λ</label>���<input type="text" size="2" maxlength="2" name="sum_hour" id="id_sum_hour" onkeyup="value = value.replace(/[^0-9]/,'');" <?php echo $dis; ?>>����
            <input type="button" value="�׻�" name="sum" id="id_sum" onClick='TimeCalculation()' <?php echo $dis; ?>>
            </font>
        </td>
    </tr>

<!-- ���� -->
    <?php if( !$model->IsSyain() || !$model->IsApproval() ) { ?>
        <tr disabled=true>
    <?php } else { ?>
        <tr>
    <?php } ?>
    <td nowrap align='center'>��&ensp;��</td>
    <td>
        <input type="radio" name="r1" id="101" onClick="syousai();" value="ͭ��ٲ�" <?php if($content=="ͭ��ٲ�") echo " checked"; ?>><label for="101">ͭ��ٲ�</label>
        <input type="radio" name="r1" id="102" onClick="syousai();" value="AMȾ��ͭ��ٲ�" <?php if($content=="AMȾ��ͭ��ٲ�") echo " checked"; ?>><label for="102">AMȾ��ͭ��ٲ�</label>
        <input type="radio" name="r1" id="103" onClick="syousai();" value="PMȾ��ͭ��ٲ�" <?php if($content=="PMȾ��ͭ��ٲ�") echo " checked"; ?>><label for="103">PMȾ��ͭ��ٲ�</label>
        <input type="radio" name="r1" id="104" onClick="syousai();" value="����ñ��ͭ��ٲ�" <?php if($content=="����ñ��ͭ��ٲ�") echo " checked"; ?>><label for="104">����ñ��ͭ��ٲ�</label>
        <input type="radio" name="r1" id="105" onClick="syousai();" value="���" <?php if($content=="���") echo " checked"; ?>><label for="105">���</label>
        <input type="radio" name="r1" id="106" onClick="syousai();" value="�ٹ�����" <?php if($content=="�ٹ�����") echo " checked"; ?>><label for="106">�ٹ�����</label>
            <table class='pt10' border="1" cellspacing="1" align='center' id="1000">
            <caption></caption>
            <!-- ���ƾܺ� -->
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
        <div onclick="obj=document.getElementById('menu1').style; obj.display=(obj.display=='none')?'block':'none'; obj2=document.getElementById('id_menu1');obj2.innerHTML=(obj.display=='none')?'�� ��ĥ��Ϣ�ʥ���å���Ÿ����':'�� ��ĥ��Ϣ�ʥ���å��ǽ̾���';">
        <a class='pt12b' id='id_menu1' style="cursor:pointer;">�� ��ĥ��Ϣ�ʥ���å���Ÿ����</a>
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
        <p class='pt10' align='center' id="2000">
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
            ���衧<input type="text" size="24" maxlength="24" name="ikisaki" value='<?php echo $others; ?>' onchange="value = SpecialText(this)">
            ��ƻ�ܸ���<input type="text" size="10" maxlength="10" name="todouhuken" value='<?php echo $place; ?>'>
            ��Ū��<input type="text" size="24" maxlength="24" name="mokuteki" value='<?php echo $purpose; ?>'>
<?php
}
?>
        </p>
        <p class='pt9' align='center' id="2500">
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
<!-- -->
<!-- ����������ѤǤ��ʤ��ʤä��顢�ʲ�����ߡ��ǻ��Ѥ���Х��顼��ȯ�����ʤ��� -->
<!--
            ����ĥ���� suica ����Ѥ�����ϡ��ʲ��򤴳�ǧ��������
            <input type='hidden' name='r3'> <input type='hidden' name='r4'>
            <input type='hidden' name='setto1'> <input type='hidden' name='setto2'>
<!-- -->
            Ʊ�Լԡ�<input type="text" size="80" maxlength="160" name="doukou" value='<?php echo $doukousya; ?>'></textarea>
<!-- ����������ѤǤ��ʤ��ʤä��顢�����ȥ����� -->
<!-- -->
        <br><br>����ĥ�����������������ϡ����� <a color="red" id="2550">������������</a> ����Ф��Ʋ�������</p>
<!-- -->
<?php
}
?>
        </div>
        <!--// �����ޤǤ��ޤꤿ���� -->

        <!-- �ޤꤿ����Ÿ���ܥ��� -->
        <div onclick="obj=document.getElementById('menu2').style; obj.display=(obj.display=='none')?'block':'none'; obj2=document.getElementById('id_menu2');obj2.innerHTML=(obj.display=='none')?'�� ���̵ٲ˴�Ϣ�ʥ���å���Ÿ����':'�� ���̵ٲ˴�Ϣ�ʥ���å��ǽ̾���';">
        <a class='pt12b' id='id_menu2' style="cursor:pointer;">�� ���̵ٲ˴�Ϣ�ʥ���å���Ÿ����</a><font color='red' size='3'>���說�����ܼ�Ϥ�����򥯥�å�</font>
        </div>
        <!--// �ޤꤿ����Ÿ���ܥ��� -->

        <!-- ������������ޤꤿ���� -->
        <div id="menu2" style="display:none;clear:both;font-size:12pt;font-weight:normal;">

        <!--������ʬ���ޤꤿ���ޤ졢Ÿ���ܥ���򥯥�å����뤳�Ȥ�Ÿ�����ޤ���-->
        <input type="radio" name="r1" id="112" onClick="syousai();" value="���̵ٲ�" <?php if($content=="���̵ٲ�") echo " checked"; ?>><label for="112">���̵ٲ�</label>
            <table class='pt10' border="1" cellspacing="1" align='center' id="3000">
            <caption></caption>
            <!-- ���ƾܺ� -->
            <tr><td>
            <input type="radio" name="r5" id="501" onClick="toku()" value="��ĤA" <?php if($special=="��ĤA") echo " checked"; ?>><label for="501">��Ĥ���ܿͤ��뺧 5��(������1��)</label>
            <br>
            <input type="radio" name="r5" id="502" onClick="toku()" value="��ĤB" <?php if($special=="��ĤB") echo " checked"; ?>><label for="502">��Ĥ�����졦�۶��ԡ��Ҥ���˴ 5��</label>
            <br>
            <input type="radio" name="r5" id="503" onClick="toku()" value="��ĤC" <?php if($special=="��ĤC") echo " checked"; ?>><label for="503">��Ĥ���۶��Ԥ����졢�ܿͤ������졢����λ�˴ 3��</label><label for="506"><font color='red' onclick='vaccine()'>�����說�����ܼ�</font></label>
            <br>
            <input type="radio" name="r5" id="504" onClick="toku()" value="��̱���ιԻ�" <?php if($special=="��̱���ιԻ�") echo " checked"; ?>><label for="504">��̱���ιԻ�</label>
            <input type="radio" name="r5" id="505" onClick="toku()" value="��³��30ǯ" <?php if($special=="��³��30ǯ") echo " checked"; ?>><label for="505">��³��30ǯ 5��</label>
            <input type="radio" name="r5" id="506" onClick="toku()" value="����¾" <?php if($special=="����¾") echo " checked"; ?>><label for="506">����¾��<input type="text" size="30" maxlength="60" name="tokubetu_sonota" value='<?php echo $others; ?>'></label>
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

<!-- ���� -->
    <?php if( !$model->IsSyain() || !$model->IsApproval() ) { ?>
        <tr disabled=true>
    <?php } else { ?>
        <tr>
    <?php } ?>
        <td nowrap align='center'>��&ensp;��</td>
        <?php
        $ua = getenv('HTTP_USER_AGENT');
        if(strstr($ua, 'Trident') || strstr($ua, 'MSIE')) { // Microsoft Internet Explorer
        ?>
        <td><input type="text" size="100" maxlength="40" name="bikoutext" value='<?php echo $remarks; ?>'> ������40��</td>
        <?php } else { ?>
        <td><input type="text" size="80" maxlength="40" name="bikoutext" value='<?php echo $remarks; ?>'> ������40��</td>
        <?php } ?>
    </tr>

<!-- Ϣ���� -->
    <?php if( !$model->IsSyain() || !$model->IsApproval() ) { ?>
        <tr id='id_renraku' disabled=true>
    <?php } else { ?>
        <tr id='id_renraku'>
    <?php } ?>
        <td nowrap align='center'>Ϣ����</td>
        <td>
            <input type="radio" name="r6" id="601" onclick="telno();" value="����" <?php if($contact=="����") echo " checked"; ?>><label for="601">����</label>
            <input type="radio" name="r6" id="602" onclick="telno();" value="����" <?php if($contact=="����") echo " checked"; ?>><label for="602">����</label>
            <input type="radio" name="r6" id="603" onclick="telno();" value="��ĥ��" <?php if($contact=="��ĥ��") echo " checked"; ?>><label for="603">��ĥ��</label>
            <input type="radio" name="r6" id="604" onclick="telno();" value="����¾" <?php if($contact=="����¾") echo " checked"; ?>><label for="604">����¾��<input type="text" size="8" maxlength="8" name="tel_sonota" value='<?php echo $contact_other; ?>'></label>
            <font id='id_tel_no'>TEL</font><input type="text" name="tel_no" maxlength="13" onkeyup="value = value.replace(/[^0-9,-]+/i,'');" value='<?php echo $contact_tel; ?>'>
        </td>
    </tr>

<!-- ���ż� -->
    <input type='hidden' name='jyu_date' value='<?php echo $request->get("jyu_date"); ?>'>
    <input type='hidden' name='outai' value='<?php echo $request->get("outai"); ?>'>
<!--
    <?php if( !$model->IsSyain() || !$model->IsApproval() ) { ?>
        <tr id='id_jyuden' disabled=true>
    <?php } else { ?>
        <tr id='id_jyuden'>
    <?php } ?>
        <td nowrap align='center'>�����ż�</td>
        <td>
            Ϣ�������������
                <select name="ddlist_jyu" id="id_jyear" onblur="JyuDateCopy()">
                    <?php SelectOptionDate(date('Y')-1, date('Y')+1, date('Y')); ?>
                </select>ǯ
                <select name="ddlist_jyu" id="id_jmonth" onblur="JyuDateCopy()">
                    <?php SelectOptionDate(1, 12, date('m')); ?>
                </select>��
                <select name="ddlist_jyu" id="id_jday" onblur="JyuDateCopy()">
                    <?php SelectOptionDate(1, 31, date('d')); ?>
                </select>��
                <select name="ddlist_jyu" id="id_jhh" onblur="JyuDateCopy()">
                    <?php SelectOptionTime(0, 23, 8); ?>
                </select>��
                <select name="ddlist_jyu" id="id_jmm" onblur="JyuDateCopy()">
                    <?php SelectOptionTime(0, 59, 30); ?>
                </select>ʬ
                <input type='hidden' name='jyu_date' value=''>

            ���мԡ�<input type="text" size="16" maxlength="8" name="outai">
        </td>
    </tr>
<!-- -->

        </table>
    </td></tr>
    </table> <!----------------- ���ߡ�End --------------------->

    <br>��ǧ�롼�ȡ�<?php if($model->IsSyain()) echo $model->getApproval(); ?><br>

    <p align='center'>
        <input type="checkbox" name="c2" id="idc2" value="���" <?php if($hurry=="���") echo " checked"; ?>><label for="idc2" id="idc2l" >���</label>
        <input type="submit" value="��ǧ���̤�" name="submit" onClick='return IsAMandTimeVacation()'>��
<?php if( $request->get("reappl") ) { ?>
        <input type="button" value="[��]�Ĥ���" name="close" onClick='window.open("about:blank","_self").close()'>
<?php } else { ?>
        <input type="button" value="����󥻥�" name="cancel" onClick='location.replace("<?php echo $menu->out_self(), '?', $model->get_htmlGETparm() ?>");'>
<?php } ?>
    </p>
        <input type='hidden' name='reappl' value='<?php echo $request->get("reappl"); ?>'>
        <input type='hidden' name='deny_uid' value='<?php echo $request->get("deny_uid"); ?>'>
        <input type='hidden' name='previous_date' value='<?php echo $request->get("previous_date"); ?>'>
    </form>

</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
