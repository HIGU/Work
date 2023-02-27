<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ω�ؼ���˥塼�� ��ꡦ��λ���� ������  MVC View ��                    //
//                                                      ��Ω���Ӥ��Խ�����  //
// Copyright (C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/12/13 Created   assembly_time_edit_ViewEdit.php                     //
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
<link rel='stylesheet' href='assembly_time_edit.css?id=<?= $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='assembly_time_edit.js?<?= $uniq ?>'></script>
</head>
<body>
<center>
<?= $menu->out_title_border() ?>
    
    <?php if ($rowsGroup <= 0) { ?>
    <div>&nbsp;</div>
    <div class='pt12b'>��Ω���롼�פ���Ͽ������ޤ��������Ω���롼�פ���Ͽ��ԤäƲ�������</div>
    <?php } else { ?>
    <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='1'>
        <!-- <caption>��Ω���� ��ȥ��롼�פ�����</caption> -->
        <tr><td> <!-- ���ߡ� -->
    <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
    <?php $tr = 0; $column = 6; ?>
    <?php for ($i=0; $i<$rowsGroup; $i++) { ?>
        <?php if ($tr == 0) {?>
        <tr>
        <?php } ?>
            <td class='winbox' align='center' nowrap>
                <input type='button' name='group_name' value='<?=$resGroup[$i][1]?>' class='pt12b bg'
                    onClick='location.replace("<?=$menu->out_self(), "?showGroup={$resGroup[$i][0]}&showMenu=List&id={$uniq}"?>")'
                    <?php if ($resGroup[$i][0] == $request->get('showGroup')) echo 'style=color:red;';?>
                >
            </td>
            <?php $tr++ ?>
        <?php if ($tr >= $column) {?>
        </tr>
        <?php } ?>
        <?php if ($tr >= $column) $tr = 0;?>
    <?php } ?>
    <?php
    if ($tr != 0) {
        while ($tr < $column) {
            echo "            <td class='winbox'>&nbsp;</td>\n";
            $tr++;
        }
        echo "        </tr>\n";
    }
    ?>
    </table>
        </td></tr> <!-- ���ߡ� -->
    </table>
    <?php } ?>
    
    <?php if ($rows >= 1) { ?>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <caption>��Ω���� (��ꡦ��λ����)�ν���</caption>
            <tr><td> <!-- ���ߡ� -->
        <table class='winbox_field' bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='8'>
        <form name='Apend_form' action='<?=$menu->out_self(), "?showMenu=ConfirmEdit&{$pageParameter}"?>' method='post' onSubmit='return AssemblyTimeEdit.checkInputForm(this);'>
            <input type='hidden' name='serial_no' value='<?=$request->get('serial_no')?>'>
            <tr>
                <!-- No. -->
                <td class='winbox pt14b' align='center' nowrap>1</td>
                <th class='winbox' width='100'>����̾</th>
                <!-- ����̾ -->
                <td class='winbox pt14b' align='center' nowrap><?=mb_convert_kana($request->get('assy_name'), 'k')?></td>
                <input type='hidden' name='assy_name' value='<?=$request->get('assy_name')?>'>
            </tr>
            <tr>
                <td class='winbox pt14b' align='center' nowrap>2</td>
                <th class='winbox' nowrap>�����ֹ�</th>
                <!-- �����ֹ� -->
                <td class='winbox pt14b' align='center' nowrap><?=$request->get('assy_no')?></td>
                <input type='hidden' name='assy_no' value='<?=$request->get('assy_no')?>'>
            </tr>
            <tr>
                <td class='winbox pt14b' align='center' width='30'>3</td>
                <th class='winbox' nowrap>�ײ��ֹ�</th>
                <!-- �ײ��ֹ� -->
                <td class='winbox pt14b' align='center' nowrap>
                    <input type='text' name='plan_no' value='<?=$request->get('plan_no')?>' size='10' maxlength='8'
                        style='ime-mode:disabled;' class='pt14b' onChange='this.value=this.value.toUpperCase()'
                    >
                </td>
                <!-- <input type='hidden' name='plan_no' value='<?=$request->get('plan_no')?>'> -->
            </tr>
            <tr>
                <td class='winbox pt14b' align='center' nowrap>4</td>
                <th class='winbox' nowrap>�ײ��</th>
                <!-- �ײ�� -->
                <td class='winbox pt14b' align='center' nowrap><?=$request->get('plan')?></td>
                <input type='hidden' name='plan' value='<?=$request->get('plan')?>'>
            </tr>
            <tr>
                <td class='winbox pt14b' align='center' nowrap>5</td>
                <th class='winbox' nowrap>�Ұ��ֹ�</th>
                <!-- �Ұ��ֹ� -->
                <td class='winbox pt14b' align='center' nowrap>
                    <input type='text' name='user_id' value='<?=$request->get('user_id')?>' size='8' maxlength='6'
                        style='ime-mode:disabled;' class='pt14b' onChange='this.value=this.value.toUpperCase()'
                    >
                </td>
                <!-- <input type='hidden' name='user_id' value='<?=$request->get('user_id')?>'> -->
            </tr>
            <tr>
                <td class='winbox pt14b' align='center' nowrap>5</td>
                <th class='winbox' nowrap>��ȼ�</th>
                <!-- ��ȼ� -->
                <td class='winbox pt14b' align='center' nowrap><?=$request->get('user_name')?></td>
                <input type='hidden' name='user_name' value='<?=$request->get('user_name')?>'>
            </tr>
            <tr class='TimeEdit'>
                <td class='winbox pt14b' align='center' nowrap>6</td>
                <th class='winbox' nowrap>��Ω���</th>
                <!-- ��Ω������� -->
                <td class='winbox pt14b' align='center' nowrap>
                    <select name='str_year' size='1'>
                        <?php for ($i=($request->get('str_year')-1); $i<=($request->get('str_year')+3); $i++) { ?>
                        <?php $data = sprintf('%04d', $i); ?>
                        <option value='<?=$data?>'<?php if($request->get('str_year')==$data)echo' selected' ?>><?=$data?></option>
                        <?php } ?>
                    </select>
                    ǯ
                    <select name='str_month' size='1'>
                        <?php for ($i=1; $i<=12; $i++) { ?>
                        <?php $data = sprintf('%02d', $i); ?>
                        <option value='<?=$data?>'<?php if($request->get('str_month')==$data)echo' selected' ?>><?=$data?></option>
                        <?php } ?>
                    </select>
                    ��
                    <select name='str_day' size='1'>
                        <?php for ($i=1; $i<=31; $i++) { ?>
                        <?php $data = sprintf('%02d', $i); ?>
                        <option value='<?=$data?>'<?php if($request->get('str_day')==$data)echo' selected' ?>><?=$data?></option>
                        <?php } ?>
                    </select>
                    ��
                    <select name='str_hour' size='1'>
                        <?php for ($i=0; $i<=23; $i++) { ?>
                        <?php $data = sprintf('%02d', $i); ?>
                        <option value='<?=$data?>'<?php if($request->get('str_hour')==$data)echo' selected' ?>><?=$data?></option>
                        <?php } ?>
                    </select>
                    ��
                    <select name='str_minute' size='1'>
                        <?php for ($i=0; $i<=59; $i++) { ?>
                        <?php $data = sprintf('%02d', $i); ?>
                        <option value='<?=$data?>'<?php if($request->get('str_minute')==$data)echo' selected' ?>><?=$data?></option>
                        <?php } ?>
                    </select>
                    ʬ
                </td>
            </tr>
            <tr class='TimeEdit'>
                <td class='winbox pt14b' align='center' nowrap>7</td>
                <th class='winbox' nowrap>��Ω��λ</th>
                <!-- ��Ω��λ���� -->
                <td class='winbox pt14b' align='center' nowrap>
                    <select name='end_year' size='1'>
                        <?php for ($i=($request->get('end_year')-1); $i<=($request->get('end_year')+3); $i++) { ?>
                        <?php $data = sprintf('%04d', $i); ?>
                        <option value='<?=$data?>'<?php if($request->get('end_year')==$data)echo' selected' ?>><?=$data?></option>
                        <?php } ?>
                    </select>
                    ǯ
                    <select name='end_month' size='1'>
                        <?php for ($i=1; $i<=12; $i++) { ?>
                        <?php $data = sprintf('%02d', $i); ?>
                        <option value='<?=$data?>'<?php if($request->get('end_month')==$data)echo' selected' ?>><?=$data?></option>
                        <?php } ?>
                    </select>
                    ��
                    <select name='end_day' size='1'>
                        <?php for ($i=1; $i<=31; $i++) { ?>
                        <?php $data = sprintf('%02d', $i); ?>
                        <option value='<?=$data?>'<?php if($request->get('end_day')==$data)echo' selected' ?>><?=$data?></option>
                        <?php } ?>
                    </select>
                    ��
                    <select name='end_hour' size='1'>
                        <?php for ($i=0; $i<=23; $i++) { ?>
                        <?php $data = sprintf('%02d', $i); ?>
                        <option value='<?=$data?>'<?php if($request->get('end_hour')==$data)echo' selected' ?>><?=$data?></option>
                        <?php } ?>
                    </select>
                    ��
                    <select name='end_minute' size='1'>
                        <?php for ($i=0; $i<=59; $i++) { ?>
                        <?php $data = sprintf('%02d', $i); ?>
                        <option value='<?=$data?>'<?php if($request->get('end_minute')==$data)echo' selected' ?>><?=$data?></option>
                        <?php } ?>
                    </select>
                    ʬ
                </td>
            </tr>
            <tr>
                <td class='winbox pt14b' align='center' nowrap>8</td>
                <th class='winbox' nowrap>����(ʬ)</th>
                <!-- ��׹���(ʬ) -->
                <td class='winbox pt14b' align='center' nowrap><?=$request->get('assy_time')?> ʬ</td>
                <input type='hidden' name='assy_time' value='<?=$request->get('assy_time')?>'>
            </tr>
            <tr>
                <td class='winbox pg12b' align='center' colspan='3'>
                    <input type='hidden' name='ConfirmEdit' value='Dummy'>
                    <input type='submit' name='ConfirmEdit' value='������ǧ' class='pt12b' style='color:blue;'>
                    &nbsp; &nbsp;
                    <input type='button' name='Cancel' value='���' class='pt12b' onClick='location.replace("<?=$menu->out_self(), "?showMenu=List&{$pageParameter}"?>")'>
                </td>
            </tr>
        </form>
        </table>
            </td></tr> <!-- ���ߡ� -->
        </table>
    <?php } ?>
    <?php if($rowsDupli > 0) { ?>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <caption>Ʊ����Ȥηײ褬�ʲ��ˤ���ޤ���</caption>
            <tr><td> <!-- ���ߡ� -->
        <table class='winbox_field' bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
            <th class='winbox' nowrap>�ײ��ֹ�</th>
            <th class='winbox' nowrap>�����ֹ�</th>
            <th class='winbox' nowrap>�����ʡ�̾</th>
            <th class='winbox' nowrap>�ײ��</th>
            <th class='winbox' nowrap>��׷ײ�</th>
            <th class='winbox' nowrap>��ȼ�</th>
            <th class='winbox' nowrap>��Ω���</th>
            <th class='winbox' nowrap>��λ(����)</th>
            <th class='winbox' nowrap>������(ʬ)</th>
        <?php for ($r=0; $r<$rowsDupli; $r++) { ?>
            <tr>
            <!-- �ײ��ֹ� -->
            <td class='winbox pt12b' align='right' nowrap><?=$resDupli[$r][0]?></td>
            <!-- �����ֹ� -->
            <td class='winbox' align='left' nowrap><?=$resDupli[$r][1]?></td>
            <!-- ����̾ -->
            <td class='winbox' align='left' nowrap><?=mb_convert_kana($resDupli[$r][2], 'k')?></td>
            <!-- �ײ�Ŀ� -->
            <td class='winbox' align='right' nowrap onDblClick='alert("�ײ�ġ��ײ����\n\n<?=$resDupli[$r][3]?>��<?=$resDupli[$r][13]?>\n\n�Ǥ���")'>
                <?=$resDupli[$r][3]?>
            </td>
            <!-- ��׷ײ� -->
            <td class='winbox' align='right' nowrap><?=$resDupli[$r][9]?></td>
            <!-- ��ȼ�̾ -->
            <td class='winbox' align='left' nowrap onDblClick='alert("�Ұ��ֹ�\n\n <?=$resDupli[$r][4]?>")'>
                <?=$resDupli[$r][5]?>
            </td>
            <!-- ��Ω������� -->
            <td class='winbox' align='center' nowrap onDblClick='alert("���ϻ��֤ξܺ�\n\n<?=$resDupli[$r][10]?>")'>
                <?=$resDupli[$r][6]?>
            </td>
            <!-- ��Ω��λ���� -->
            <td class='winbox' align='center' nowrap onDblClick='alert("��λ(����)���֤ξܺ�\n\n<?=$resDupli[$r][11]?>")'>
                <?=$resDupli[$r][7]?>
            </td>
            <!-- ������(ʬ) -->
            <td class='winbox' align='right' nowrap onDblClick='alert("���Ĥ�����ι���\n\n<?=$resDupli[$r][12]?> ʬ/��")'>
                <?=$resDupli[$r][8]?>
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
