<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ω�κ�ȴ������ӥǡ��� �Խ�             MVC View ��                    //
//                                      ��Ω���Ӱ���ɽ �������ɲäΥ���� //
// Copyright (C) 2005-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/12/13 Created   assembly_time_edit_ViewList.php                     //
// 2006/11/28 group_name�ܥ����&{$pageParameter}�ѥ�᡼�������ɲ�         //
// 2007/09/13 ��Ω���� ������caption������� php�Υ��硼�Ȥ�ɸ�ॿ����    //
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
<link rel='stylesheet' href='assembly_time_edit.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='assembly_time_edit.js?<?php echo $uniq ?>'></script>
</head>
<body>
<center>
<?php echo $menu->out_title_border() ?>
    
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
                <input type='button' name='group_name' value='<?php echo $resGroup[$i][1]?>' class='pt12b bg'
                    onClick='location.replace("<?php echo $menu->out_self(), "?showGroup={$resGroup[$i][0]}&showMenu=List&{$pageParameter}&id={$uniq}"?>")'
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
        <form name='ControlForm' action='<?php echo $menu->out_self(), "?showMenu=List&id={$uniq}"?>' method='post'>
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <tr>
                <td align='center' nowrap width='20%'>
                    <input type='button' name='apendForm' value='�ɲ�' class='pt12b bg' style='color:blue;'
                    onClick='location.replace("<?php echo $menu->out_self(), "?showMenu=Apend&{$pageParameter}"?>")'
                </td>
                <td align='center' nowrap width='40%'>
                    <span class='caption_font'>��Ω���� ����</span>
                </td>
                <td align='center' nowrap width='40%'>
                    <?php echo $pageControl?>
                </td>
            </tr>
        </table>
        </form>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!-- ���ߡ� -->
        <table class='winbox_field' bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
            <th class='winbox'>&nbsp;</th>
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
            <tr>
            <!-- No. -->
            <td class='winbox pt12b' align='right' nowrap><?php echo $r + 1 + $model->get_offset()?></td>
            <!-- ��Ω���Ӥν��� -->
            <td class='winbox pt12b' align='center' nowrap>
                <a
                href='<?php echo $menu->out_self(), "?serial_no={$res[$r][9]}&showMenu=Edit&user_id={$res[$r][4]}&", $pageParameter?>'
                style='text-decoration:none;'
                onMouseover="status='���ӥǡ����ν�����Ԥ��ޤ���';return true;"
                onMouseout="status=''"
                title='���ӥǡ����ν�����Ԥ��ޤ���'
                >
                    ����
                </a>
                <!-- onClick='return confirm("���ӥǡ������Խ���Ԥ��ޤ���\n\n�����������ǽ�Ǥ���\n\n�������Ǥ�����")' -->
            </td>
            <!-- ��Ω���Ӥκ�� -->
            <td class='winbox pt10' align='center' nowrap>
                <a
                href='<?php echo $menu->out_self(), "?serial_no={$res[$r][9]}&showMenu=ConfirmDelete&ConfirmDelete=go&user_id={$res[$r][4]}&", $pageParameter?>'
                style='text-decoration:none;'
                onMouseover="status='���ӥǡ����κ����Ԥ��ޤ���';return true;"
                onMouseout="status=''"
                title='���ӥǡ����κ����Ԥ��ޤ���'
                >
                    ���
                </a>
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
            <!-- �ײ�Ŀ� -->
            <td class='winbox' align='right' nowrap onDblClick='alert("�ײ�ġ��ײ����\n\n<?php echo $res[$r][3]?>��<?php echo $res[$r][13]?>\n\n�Ǥ���")'>
                <?php echo $res[$r][3]?>
            </td>
                <!-- �Ұ��ֹ� -->
                <!-- <td class='winbox' align='center' nowrap><?php echo $res[$r][4]?></td> -->
            <!-- ��ȼ� -->
            <td class='winbox' align='left' nowrap onDblClick='alert("�Ұ��ֹ�\n\n <?php echo $res[$r][4]?>")'>
                <?php echo $res[$r][5]?>
            </td>
            <!-- ��Ω������� -->
            <td class='winbox' align='center' nowrap onDblClick='alert("���ϻ��֤ξܺ�\n\n<?php echo $res[$r][10]?>")'>
                <?php echo $res[$r][6]?>
            </td>
            <!-- ��Ω��λ���� -->
            <td class='winbox' align='center' nowrap onDblClick='alert("��λ(����)���֤ξܺ�\n\n<?php echo $res[$r][11]?>")'>
                <?php echo $res[$r][7]?>
            </td>
            <!-- ��Ω����(ʬ) -->
            <td class='winbox' align='right' nowrap onDblClick='alert("���Ĥ�����ι���\n\n<?php echo $res[$r][12]?> ʬ/��")'>
                <?php echo $res[$r][8]?>
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
<?php echo $menu->out_alert_java()?>
<?php } ?>
</html>
