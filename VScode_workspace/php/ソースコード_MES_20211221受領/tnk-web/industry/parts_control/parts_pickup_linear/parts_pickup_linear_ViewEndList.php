<?php
//////////////////////////////////////////////////////////////////////////////
// �����������ʽи� ��ꡦ��λ���� ������  MVC View ��                    //
//                                              �и˴�λ����ɽ              //
// Copyright (C) 2005-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/09/25 Created   parts_pickup_linear_ViewEndList.php                 //
// 2005/09/27 ����̾�����ѥ��ʤλ� ��·�������뤿��mb_convert_kana�Ƿ��碌//
//            ����ɽ��ηײ��ֹ�ʹߤ�pt12b�������ɸ�ॵ�������� �Ϥ߽Ф�  //
// 2005/11/23 ControlFormSubmit()�᥽�å� ���Submit�к����ɲ�              //
// 2005/12/08 ��λ�μ�ä������ޤǤ˸��ꡣphp6�Ѥ�ASP/JSP������php���Ѥ��ѹ�//
// 2005/12/10 ��ꡦ��λ���֤ν����ѥ��å����ɲ� �Ȱ������ƹ���ɽ�Υ�� //
// 2006/04/07 </label> ��ȴ���Ƥ������ս����                             //
// 2006/06/06 parts_pickup_time �� parts_pickup_linear ���ѹ�����˥��Ǻ��� //
//            ���硼�ȥ��åȥ������ѻߤ��� php�ο侩�������ѹ�              //
//////////////////////////////////////////////////////////////////////////////
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
<link rel='stylesheet' href='parts_pickup_linear.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='parts_pickup_linear.js?<?php echo $uniq ?>'></script>
</head>
<body>
<center>
<?php echo $menu->out_title_border() ?>
    
    <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
    <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr>
        <form name='ControlForm' action='<?php echo $menu->out_self(), "?id={$uniq}"?>' method='post'>
            <td nowrap <?php if($current_menu=='apend') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return PartsPickupLinear.ControlFormSubmit(document.ControlForm.elements["apend"], document.ControlForm);'
            >
                <input type='radio' name='current_menu' value='apend' id='apend'
                <?php if($current_menu=='apend') echo 'checked' ?>>
                <label for='apend'>�и��������</label>
            </td>
            <td nowrap <?php if($current_menu=='list') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return PartsPickupLinear.ControlFormSubmit(document.ControlForm.elements["list"], document.ControlForm);'
            >
                <input type='radio' name='current_menu' value='list' id='list'
                <?php if($current_menu=='list') echo 'checked' ?>>
                <label for='list'>�и�������</label>
            </td>
            <td nowrap <?php if($current_menu=='EndList') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return PartsPickupLinear.ControlFormSubmit(document.ControlForm.elements["EndList"], document.ControlForm);'
            >
                <input type='radio' name='current_menu' value='EndList' id='EndList'
                <?php if($current_menu=='EndList') echo 'checked' ?>>
                <label for='EndList'>�и˴�λ����</label>
            </td>
            <td nowrap class='winbox'>
                <?php echo $pageControl?>
            </td>
            <td nowrap <?php if($current_menu=='user') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return PartsPickupLinear.ControlFormSubmit(document.ControlForm.elements["user"], document.ControlForm);'
            >
                <input type='radio' name='current_menu' value='user' id='user'
                <?php if($current_menu=='user') echo 'checked' ?>>
                <label for='user'>��ȼ���Ͽ</label>
            </td>
        </form>
        </tr>
    </table>
        </td></tr>
    </table> <!----------------- ���ߡ�End ------------------>
    <?php if ($rows >= 1) { ?>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <caption>�и˴�λ ���� &nbsp; &nbsp; &nbsp; (���֤������֥륯��å���������������Ǥ��ޤ���)</caption>
            <tr><td> <!-- ���ߡ� -->
        <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <th class='winbox'>&nbsp;</th>
            <th class='winbox'>&nbsp;</th>
            <th class='winbox' nowrap>�ײ��ֹ�</th>
            <th class='winbox' nowrap>�����ֹ�</th>
            <th class='winbox' nowrap>�����ʡ�̾</th>
            <th class='winbox' nowrap>�ײ��</th>
            <!-- <th class='winbox' nowrap>�Ұ��ֹ�</th> -->
            <th class='winbox' nowrap>��ȼ�</th>
            <th class='winbox' nowrap>�и����</th>
            <th class='winbox' nowrap>�и˴�λ</th>
            <th class='winbox' nowrap>����(ʬ)</th>
        <?php for ($r=0; $r<$rows; $r++) { ?>
            <tr>
            <!-- No. -->
            <td class='winbox pt12b' align='right' nowrap><?php echo $r + 1 + $model->get_offset()?></td>
            <!-- �и˴�λ�μ�� -->
            <td class='winbox pt12b' align='center' nowrap>
                <?php if ($res[$r][10] == '���ͭ��') { ?>
                <a
                href='<?php echo $menu->out_self(), "?serial_no={$res[$r][8]}&current_menu=EndList&editCancel=go&plan_no={$res[$r][0]}&user_id={$res[$r][4]}&", $model->get_htmlGETparm(), "&id={$uniq}"?>'
                style='text-decoration:none;'
                onClick='return confirm("��λ�μ�ä򤷤ޤ��������Ǥ�����")'
                onMouseover="status='���ʽи� ��λ�μ�ä�Ԥ��ޤ���';return true;"
                onMouseout="status=''"
                title='���ʽи� ��λ�μ�ä�Ԥ��ޤ���'
                >
                    ���
                </a>
                <?php } else { ?>
                    ����
                <?php } ?>
            </td>
            <!-- �ײ��ֹ� -->
            <td class='winbox pt12b' align='right' nowrap>
                <a
                href='<?php echo $menu->out_action('��������ɽ'), '?plan_no=', urlencode($res[$r][0]), "&id={$uniq}"?>'
                style='text-decoration:none;'
                onMouseover="status='���ηײ��ֹ�ΰ������ʹ���ɽ�˥����פ��ޤ���';return true;"
                onMouseout="status=''"
                title='���ηײ��ֹ�ΰ������ʹ���ɽ�˥����פ��ޤ���'
                >
                    <?php echo $res[$r][0]?>
                </a>
            </td>
            <!-- �����ֹ� -->
            <td class='winbox' align='left' nowrap><?php echo $res[$r][1]?></td>
            <!-- ����̾ -->
            <td class='winbox' align='left' nowrap><?php echo mb_convert_kana($res[$r][2], 'k')?></td>
            <!-- �ײ�� -->
            <td class='winbox' align='right' nowrap><?php echo $res[$r][3]?></td>
            <!-- �Ұ��ֹ� -->
            <!-- <td class='winbox' align='center' nowrap><?php echo $res[$r][4]?></td> -->
            <!-- ��ȼ� -->
            <td class='winbox' align='left' nowrap><?php echo $res[$r][5]?></td>
            <!-- �и�������� -->
            <td class='winbox' align='center' nowrap onDblClick='location.replace("<?php echo $menu->out_self(), "?current_menu=TimeEdit&serial_no={$res[$r][8]}&{$pageParm}"?>");'>
                <?php echo $res[$r][6]?>
            </td>
            <!-- �и˴�λ���� -->
            <td class='winbox' align='center' nowrap onDblClick='location.replace("<?php echo $menu->out_self(), "?current_menu=TimeEdit&serial_no={$res[$r][8]}&{$pageParm}"?>");'>
                <?php echo $res[$r][7]?>
            </td>
            <!-- �и˹���(ʬ) -->
            <td class='winbox' align='right' nowrap><?php echo $res[$r][9]?></td>
            </tr>
        <?php } ?>
        </table>
            </td></tr> <!-- ���ߡ� -->
        </table>
    <?php } ?>
</center>
</body>
<?php if ($_SESSION['s_sysmsg'] != '��Ͽ������ޤ���') { ?>
<?php echo $menu->out_alert_java()?>
<?php } ?>
</html>
