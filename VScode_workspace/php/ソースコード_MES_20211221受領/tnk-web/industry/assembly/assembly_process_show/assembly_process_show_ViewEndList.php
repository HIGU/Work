<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ω�κ�ȴ��� ��ꡦ���ӥǡ��� �Ȳ�   ��λ��������      MVC View ��     //
//                                      ��Ω���Ӱ���ɽ �������ɲäΥ���� //
// Copyright (C) 2006-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/01/19 Created   assembly_process_show_ViewList.php                  //
// 2006/01/20 <meta �� Refresh 15�ä��ɲ�                                   //
// 2006/01/24 $pageParameter �θ��ID=���� ��������$pageParameter���� //
// 2006/04/13 ��λ�ǡ�����̵������ if ($rows >= 1) ���� �ܥ����ä��ʤ�  //
// 2007/03/19 ʸ�������ɤ�����Τ���out_action('��������ɽ')��'AlloConfView'//
// 2007/03/26 �ѥ�᡼������material=1���ɲä���������page_keep�����롣   //
//            �ײ��ֹ楯��å����ι��ֹ���¸�������ɲ�                      //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<meta http-equiv="Refresh" content="15;URL=<?=$menu->out_self(), "?showMenu={$request->get('showMenu')}&{$pageParameter}"?>">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>
<link rel='stylesheet' href='assembly_process_show.css?id=<?= $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='assembly_process_show.js?<?= $uniq ?>'></script>
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
        <tr>
            <td class='winbox' align='center' nowrap>
                <input type='button' name='group_name' value='����' class='pt12b bg'
                    onClick='location.replace("<?=$menu->out_self(), "?showGroup=0&showMenu={$request->get('showMenu')}&id={$uniq}"?>")'
                    <?php if ($request->get('showGroup') == '') echo 'style=color:red;';?>
                >
            </td>
        <td> <!-- ���ߡ� -->
    <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
    <?php $tr = 0; $column = 6; ?>
    <?php for ($i=0; $i<$rowsGroup; $i++) { ?>
        <?php if ($tr == 0) {?>
        <tr>
        <?php } ?>
            <td class='winbox' align='center' nowrap>
                <input type='button' name='group_name' value='<?=$resGroup[$i][1]?>' class='pt12b bg'
                    onClick='location.replace("<?=$menu->out_self(), "?showGroup={$resGroup[$i][0]}&showMenu={$request->get('showMenu')}&id={$uniq}"?>")'
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
    
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <caption>
        <form name='ControlForm' action='<?=$menu->out_self(), "?showMenu={$request->get('showMenu')}&id={$uniq}"?>' method='post'>
            <table border='0' width='100%'>
                <tr>
                    <td align='center' nowrap width='10%'>
                        <input type='button' name='List' value='������' class='pt12b bg'
                        onClick='location.replace("<?=$menu->out_self(), "?showMenu=StartList&id={$uniq}"?>")'
                    </td>
                    <td align='center' nowrap width='10%'>
                        <input type='button' name='List' value='��λ����' class='pt12b bg' style='color:red;'
                        onClick='location.replace("<?=$menu->out_self(), "?showMenu=EndList&{$pageParameter}"?>")'
                    </td>
                    <td align='center' nowrap width='40%'>
                        <span class='caption_font'>��Ω��λ ����</span>
                    </td>
                    <td align='center' nowrap width='40%'>
                        <?=$pageControl?>
                    </td>
                </tr>
            </table>
        </form>
        </caption>
            <tr><td> <!-- ���ߡ� #e6e6e6 -->
        <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <th class='winbox'>&nbsp;</th>
            <th class='winbox' width='70'  nowrap>�ײ��ֹ�</th>
            <th class='winbox' width='70'  nowrap>�����ֹ�</th>
            <th class='winbox' width='180' nowrap>�����ʡ�̾</th>
            <th class='winbox' width='50'  nowrap>�ײ��</th>
            <!-- <th class='winbox' width='60' nowrap>�Ұ��ֹ�</th> -->
            <th class='winbox' width='60' nowrap>��ȼ�</th>
            <th class='winbox' width='110' nowrap>��Ω���</th>
            <th class='winbox' width='110' nowrap>��λ(����)</th>
            <th class='winbox' width='80'  nowrap>������(ʬ)</th>
        <?php for ($r=0; $r<$rows; $r++) { ?>
            <?php $recNo = ($r + 1 + $this->model->get_offset() )?>
            <?php if ($session->get_local('recNo') == $recNo) { ?>
            <tr style='background-color:#ffffc6;'>
            <?php } else { ?>
            <tr>
            <?php } ?>
            <!-- No. -->
            <td class='winbox pt12b' align='right' nowrap><?php echo $recNo ?></td>
            <!-- �ײ��ֹ� -->
            <td class='winbox pt12b' align='right' nowrap>
                <a
                href='<?php echo "JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}&{$uniq}\"); location.replace(\"", $menu->out_action('AlloConfView'), '?plan_no=', urlencode($res[$r][0]), "&material=1&id={$uniq}\");"?>'
                style='text-decoration:none;'
                onMouseover="status='���ηײ��ֹ�ΰ������ʹ���ɽ�˥����פ��ޤ���';return true;"
                onMouseout="status=''"
                title='���ηײ��ֹ�ΰ������ʹ���ɽ�˥����פ��ޤ���'
                >
                    <?=$res[$r][0]?>
                </a>
            </td>
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
            <td class='winbox' align='left' nowrap onDblClick='alert("�Ұ��ֹ�\n\n <?=$res[$r][4]?>")'>
                <?=$res[$r][5]?>
            </td>
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
</center>
</body>
<?php if ($_SESSION['s_sysmsg'] != '��Ͽ������ޤ���') { ?>
<?=$menu->out_alert_java()?>
<?php } ?>
</html>
