<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ω�ؼ���˥塼�� ��ꡦ��λ���� ������  MVC View ��                    //
//                                              ��Ω��λ����ɽ              //
// Copyright (C) 2005-2016 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/10/18 Created   assembly_process_time_ViewEndList.php               //
// 2005/10/18 ����̾�����ѥ��ʤλ� ��·�������뤿��mb_convert_kana�Ƿ��碌//
//            ����ɽ��ηײ��ֹ�ʹߤ�pt12b�������ɸ�ॵ�������� �Ϥ߽Ф�  //
// 2005/11/23 ControlFormSubmit()�᥽�å� ���Submit�к����ɲ�              //
// 2005/11/30 �ײ����ײ�Ĥ��ѹ��������ȼ�����֥륯��å������پȲ��ɲ�  //
// 2005/12/07 ��λ�μ�ä������ޤǤ˸��ꡣphp6�Ѥ�ASP/JSP������php���Ѥ��ѹ�//
// 2006/04/07 </label> ��ȴ���Ƥ������ս����                             //
// 2016/08/08 mouseOver���ɲ�                                          ��ë //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>
<link rel='stylesheet' href='assembly_process_time.css?id=<?= $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='assembly_process_time.js?<?= $uniq ?>'></script>
</head>
<body>
<center>
<?= $menu->out_title_border() ?>
    
    <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='1'>
        <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
    <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr>
        <form name='ControlForm' action='<?=$menu->out_self(), "?id={$uniq}"?>' method='post'>
            <td nowrap <?php if($showMenu=='apend') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return AssemblyProcessTime.ControlFormSubmit(document.ControlForm.elements["apend"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='apend' id='apend'
                <?php if($showMenu=='apend') echo 'checked' ?>>
                <label for='apend'>��Ω�������</label>
            </td>
            <td nowrap <?php if($showMenu=='StartList') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return AssemblyProcessTime.ControlFormSubmit(document.ControlForm.elements["StartList"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='StartList' id='StartList'
                <?php if($showMenu=='StartList') echo 'checked' ?>>
                <label for='StartList'>��Ω������</label>
            </td>
            <td nowrap <?php if($showMenu=='EndList') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return AssemblyProcessTime.ControlFormSubmit(document.ControlForm.elements["EndList"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='EndList' id='EndList'
                <?php if($showMenu=='EndList') echo 'checked' ?>>
                <label for='EndList'>��Ω��λ����</label>
            </td>
            <td nowrap class='winbox'>
                <?=$pageControl?>
            </td>
            <td nowrap <?php if($showMenu=='group') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return AssemblyProcessTime.ControlFormSubmit(document.ControlForm.elements["group"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='group' id='group'
                <?php if($showMenu=='group') echo 'checked' ?>>
                <label for='group'>���롼���Խ�</label>
            </td>
        </form>
        </tr>
    </table>
        </td></tr>
    </table> <!----------------- ���ߡ�End ------------------>
    <?php if ($rows >= 1) { ?>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <caption>��Ω��λ ����</caption>
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
            <th class='winbox' nowrap>��Ω���</th>
            <th class='winbox' nowrap>��λ(����)</th>
            <th class='winbox' nowrap>������(ʬ)</th>
        <?php for ($r=0; $r<$rows; $r++) { ?>
            <tr onMouseOver="style.background='#ceffce'" onMouseOut="style.background='#d6d3ce'">
            <!-- No. -->
            <td class='winbox pt12b' align='right' nowrap><?=$r + 1 + $model->get_offset()?></td>
            <!-- ��Ω��λ�μ�� -->
            <td class='winbox pt12b' align='center' nowrap>
                <?php if ($res[$r][14] == '���ͭ��') { ?>
                <a
                href='<?=$menu->out_self(), "?serial_no={$res[$r][9]}&showMenu=EndList&endCancel=go&plan_no={$res[$r][0]}&", $model->get_htmlGETparm(), "&id={$uniq}"?>'
                style='text-decoration:none;'
                onClick='return confirm("��λ(����)�μ�ä򤷤ޤ��������Ǥ�����")'
                onMouseover="status='��Ω ��λ(����)�μ�ä�Ԥ��ޤ���';return true;"
                onMouseout="status=''"
                title='��Ω ��λ(����)�μ�ä�Ԥ��ޤ���'
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
                href='<?=$menu->out_action('��������ɽ'), '?plan_no=', urlencode($res[$r][0]), "&id={$uniq}"?>'
                style='text-decoration:none;'
                onMouseover="status='���ηײ��ֹ�ΰ������ʹ���ɽ�˥����פ��ޤ���';return true;"
                onMouseout="status=''"
                title='���ηײ��ֹ�ΰ������ʹ���ɽ�˥����פ��ޤ���'
                >
                    <?=$res[$r][0]?>
                </a>
            </td>
                <!-- <td class='winbox' align='right' nowrap><?=$res[$r][0]?></td> -->
            <!-- �����ֹ� -->
            <td class='winbox' align='left' nowrap><?=$res[$r][1]?></td>
            <!-- ����̾ -->
            <td class='winbox' align='left' nowrap><?=mb_convert_kana($res[$r][2], 'k')?></td>
            <!-- �ײ�Ŀ� -->
            <td class='winbox' align='right' nowrap onDblClick='alert("�ײ�ġ��ײ����\n\n<?=$res[$r][3]?>��<?=$res[$r][13]?>\n\n�Ǥ���")'>
                <?=$res[$r][3]?>
            </td>
            <!-- �Ұ��ֹ� -->
            <!-- <td class='winbox' align='center' nowrap><?=$res[$r][4]?></td> -->
            <!-- ��ȼ� -->
            <td class='winbox' align='left' nowrap><?=$res[$r][5]?></td>
            <!-- ��Ω������� -->
            <td class='winbox' align='center' nowrap onDblClick='alert("���ϻ��֤ξܺ�\n\n<?=$res[$r][10]?>")'>
                <?=$res[$r][6]?>
            </td>
            <!-- ��Ω��λ���� -->
            <td class='winbox' align='center' nowrap onDblClick='alert("��λ(����)���֤ξܺ�\n\n<?=$res[$r][11]?>")'>
                <?=$res[$r][7]?>
            </td>
            <!-- ��Ω����(ʬ) -->
            <td class='winbox' align='right' nowrap onDblClick='alert("���Ĥ�����ι���\n\n<?=$res[$r][12]?> ʬ/��")'>
                <?=$res[$r][8]?>
            </td>
            </tr>
        <?php } ?>
        </table>
            </td></tr> <!-- ���ߡ� -->
        </table>
    <?php } ?>
</center>
</body>
<?php if ($_SESSION['s_sysmsg'] != '��Ͽ������ޤ���') { ?>
<?=$menu->out_alert_java()?>
<?php } ?>
</html>
