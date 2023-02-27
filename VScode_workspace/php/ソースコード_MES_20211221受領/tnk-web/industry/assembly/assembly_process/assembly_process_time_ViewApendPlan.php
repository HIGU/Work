<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ω�ؼ���˥塼�� ��ꡦ��λ���� ������  MVC View ��                    //
//                                              ��Ω��� �ؼ� ����(��Ͽ)    //
// Copyright (C) 2005-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/10/18 Created   assembly_process_time_ViewApend.php                 //
// 2005/10/24 style='ime-mode:disabled;' ��ä�IME������ON���б��Τ����ɲ�  //
// 2005/11/23 ControlFormSubmit()�᥽�å� ���Submit�к����ɲ�              //
// 2005/11/30 �ײ����ײ�Ĥ��ѹ��������ȼ�����֥륯��å������پȲ��ɲ�  //
// 2006/04/07 </label> ��ȴ���Ƥ������ս����                             //
// 2006/05/19 �ײ����ϻ�����Ͽ����ɽ����ǽ�ɲ� $model->outViewKousu($menu)  //
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
<link rel='stylesheet' href='assembly_process_time.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='assembly_process_time.js?<?php echo $uniq ?>'></script>
</head>
<body onLoad='
    AssemblyProcessTime.set_focus(document.start_form.plan_no, "select")
    <?php echo $model->outViewKousu($menu) ?>
'>
<center>
<?php echo $menu->out_title_border() ?>
    
    <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='1'>
        <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
    <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr>
        <form name='ControlForm' action='<?php echo $menu->out_self(), "?id={$uniq}"?>' method='post'>
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
                <?php echo $pageControl?>
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
    
    <div></div>
    
    <?php if ($userRows >= 1) { ?>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <caption>��Ω��� ��ȼ� �ؼ� ����</caption>
            <tr><td> <!-- ���ߡ� -->
        <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <th class='winbox'>&nbsp;</th>
            <th class='winbox'>&nbsp;</th>
            <th class='winbox' nowrap>�Ұ��ֹ�</th>
            <th class='winbox' nowrap>��ȼ�</th>
            <th class='winbox' nowrap>��Ω���</th>
        <?php for ($r=0; $r<$userRows; $r++) { ?>
            <tr>
            <!-- No. -->
            <td class='winbox pt12b' align='right' nowrap><?php echo $r + 1 + $model->get_offset()?></td>
            <!-- ��� -->
            <td class='winbox pt12b' align='center' nowrap>
                <a
                href='<?php echo $menu->out_self(), "?user_id={$userRes[$r][0]}&showMenu=apend&deleteUser=go&", $model->get_htmlGETparm(), "&id={$uniq}"?>'
                style='text-decoration:none;'
                onClick='return confirm("��ȼԤ����μ�ä򤷤ޤ��������Ǥ�����")'
                onMouseover="status='��Ω ��ȼԤ� ���μ�ä�Ԥ��ޤ���';return true;"
                onMouseout="status=''"
                title='��Ω ��ȼԤ� ���μ�ä�Ԥ��ޤ���'
                >
                    ���
                </a>
            </td>
            <!-- �Ұ��ֹ� -->
            <td class='winbox pt12b' align='center' nowrap><?php echo $userRes[$r][0]?></td>
            <!-- ��ȼ� -->
            <td class='winbox pt12b' align='left' nowrap><?php echo $userRes[$r][1]?></td>
            <!-- ��Ω������� -->
            <td class='winbox pt12b' align='center' nowrap><?php echo $userRes[$r][2]?></td>
            </tr>
        <?php } ?>
        </table>
            </td></tr> <!-- ���ߡ� -->
        </table>
    <?php } ?>
    
    <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <caption>��Ω��� �ײ��ֹ� �ؼ� ����</caption>
        <tr><td> <!-- ���ߡ� -->
    <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <form name='start_form' action='<?php echo $menu->out_self(), "?id={$uniq}"?>' method='post' onSubmit='return AssemblyProcessTime.start_formCheck(this)'>
            <input type='hidden' name='showMenu' value='apend'>
        <tr>
            <td class='winbox pt12b' nowrap>
                �ײ��ֹ�
            </td>
            <td class='winbox' nowrap>
                <input type='text' name='plan_no' value='<?php echo $plan_no?>' size='10' maxlength='8'
                    style='ime-mode:disabled;' class='pt12b' onChange='this.value=this.value.toUpperCase()'
                >
                <input type='hidden' name='userEnd' value='dummy'>
                <input type='hidden' name='apendPlan' value='dummy'>
            </td>
            <td class='winbox' nowrap>
                <input type='submit' name='apendPlan' value='��Ͽ' class='pt12b'>
            </td>
        </tr>
        </form>
    </table>
        </td></tr> <!-- ���ߡ� -->
    </table>
    
    <?php if ($planRows >= 1) { ?>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <caption>��Ω��� �ؼ� ����</caption>
            <tr><td> <!-- ���ߡ� -->
        <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <th class='winbox'>&nbsp;</th>
            <th class='winbox'>&nbsp;</th>
            <th class='winbox'>&nbsp;</th>
            <th class='winbox' nowrap>�ײ��ֹ�</th>
            <th class='winbox' nowrap>�����ֹ�</th>
            <th class='winbox' nowrap>�����ʡ�̾</th>
            <th class='winbox' nowrap>�ײ��</th>
            <th class='winbox' nowrap>�Ұ��ֹ�</th>
            <th class='winbox' nowrap>��ȼ�</th>
            <th class='winbox' nowrap>��Ω���</th>
        <?php for ($r=0; $r<$planRows; $r++) { ?>
            <tr>
            <!-- No. -->
            <td class='winbox pt12b' align='right' nowrap><?php echo $r + 1 + $model->get_offset()?></td>
            <!-- ��� -->
            <td class='winbox pt12b' align='center' nowrap>
                <a
                href='<?php echo $menu->out_self(), "?serial_no={$planRes[$r][7]}&showMenu=apend&deletePlan=go&plan_no={$planRes[$r][0]}&userEnd=go&", $model->get_htmlGETparm(), "&id={$uniq}"?>'
                style='text-decoration:none;'
                onClick='return confirm("���μ�ä򤷤ޤ��������Ǥ�����")'
                onMouseover="status='������Ω ���μ�ä�Ԥ��ޤ���';return true;"
                onMouseout="status=''"
                title='������Ω ���μ�ä�Ԥ��ޤ���'
                >
                    ���
                </a>
            </td>
            <!-- ��Ω��λ -->
            <td class='winbox pt12b' align='center' nowrap>
                <a
                href='<?php echo $menu->out_self(), "?serial_no={$planRes[$r][7]}&showMenu=apend&assyEnd=go&plan_no={$planRes[$r][0]}&userEnd=go&", $model->get_htmlGETparm(), "&id={$uniq}"?>'
                style='text-decoration:none;'
                onMouseover="status='������Ω�δ�λ���Ϥ�Ԥ��ޤ���';return true;"
                onMouseout="status=''"
                title='������Ω�δ�λ���Ϥ�Ԥ��ޤ���'
                >
                    ��λ
                </a>
            </td>
            <!-- �ײ��ֹ� -->
            <td class='winbox pt12b' align='right' nowrap><?php echo $planRes[$r][0]?></td>
            <!-- �����ֹ� -->
            <td class='winbox pt12b' align='left' nowrap><?php echo $planRes[$r][1]?></td>
            <!-- ����̾ -->
            <td class='winbox pt12b' align='left' nowrap><?php echo $planRes[$r][2]?></td>
            <!-- �ײ�� -->
            <td class='winbox pt12b' align='right' nowrap onDblClick='alert("�ײ�ġ��ײ����\n\n<?php echo $planRes[$r][3]?>��<?php echo $planRes[$r][8]?>\n\n�Ǥ���")'>
                <?php echo $planRes[$r][3]?>
            </td>
            <!-- �Ұ��ֹ� -->
            <td class='winbox pt12b' align='center' nowrap><?php echo $planRes[$r][4]?></td>
            <!-- ��ȼ� -->
            <td class='winbox pt12b' align='left' nowrap><?php echo $planRes[$r][5]?></td>
            <!-- ��Ω������� -->
            <td class='winbox pt12b' align='center' nowrap><?php echo $planRes[$r][6]?></td>
            </tr>
        <?php } ?>
        </table>
            </td></tr> <!-- ���ߡ� -->
        </table>
        <table width='100%' border='0' cellspacing='0' cellpadding='10'>
            <form name='end_form' action='<?php echo $menu->out_self(), "?id={$uniq}"?>' method='post'>
            <tr align='center'>
                <td>
                    <input type='hidden' name='showMenu' value='apend'>
                    <input type='submit' name='apendEnd' value='���Ͻ�λ' class='pt12b'>
                </td>
            </tr>
            </form>
        </table>
    <?php } ?>
</center>
</body>
<?php if ($_SESSION['s_sysmsg'] != '��Ͽ������ޤ���') { ?>
<?php echo $menu->out_alert_java()?>
<?php } ?>
</html>
