<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ω�κ�ȴ��� ��ꡦ���ӥǡ��� �Ȳ�   ����������      MVC View ��     //
//                                      ��Ω���Ӱ���ɽ �������ɲäΥ���� //
// Copyright (C) 2006-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/01/19 Created   assembly_process_show_ViewStartList.php             //
// 2006/01/20 <meta �� Refresh 15�ä��ɲ�  Ajax�Τ����<span id='showAjax'> //
//            onLoad='setInterval("AssemblyProcessShow.AjaxLoadStart()"��   //
//            �ɲä������� <meta Refresh�ˤ�����ɤ򥳥��ȥ�����      //
// 2006/01/24 $pageParameter �θ��ID=���� ��λ������$pageParameter���� //
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
<!-- <meta http-equiv="Refresh" content="15;URL=<?php echo $menu->out_self(), "?showMenu={$request->get('showMenu')}&{$pageParameter}"?>"> -->
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<link rel='stylesheet' href='assembly_process_show.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='assembly_process_show.js?<?php echo $uniq ?>'></script>
</head>
<body onLoad='setInterval("AssemblyProcessShow.AjaxLoadStart()", 15000)'>
<center>
<?php echo $menu->out_title_border() ?>
    
    <?php if ($rowsGroup <= 0) { ?>
    <div>&nbsp;</div>
    <div class='pt12b'>��Ω���롼�פ���Ͽ������ޤ��������Ω���롼�פ���Ͽ��ԤäƲ�������</div>
    <?php } else { ?>
    <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='1'>
        <!-- <caption>��Ω���� ��ȥ��롼�פ�����</caption> -->
        <tr>
            <td class='winbox' align='center' nowrap>
                <input type='button' name='group_name' value='����' class='pt12b bg'
                    onClick='location.replace("<?php echo $menu->out_self(), "?showGroup=0&showMenu={$request->get('showMenu')}&id={$uniq}"?>")'
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
                <input type='button' name='group_name' value='<?php echo $resGroup[$i][1]?>' class='pt12b bg'
                    onClick='location.replace("<?php echo $menu->out_self(), "?showGroup={$resGroup[$i][0]}&showMenu={$request->get('showMenu')}&id={$uniq}"?>")'
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
        <form name='ControlForm' action='<?php echo $menu->out_self(), "?showMenu={$request->get('showMenu')}&id={$uniq}"?>' method='post'>
            <table border='0' width='100%'>
                <tr>
                    <td align='center' nowrap width='10%'>
                        <input type='button' name='List' value='������' class='pt12b bg' style='color:red;'
                        onClick='location.replace("<?php echo $menu->out_self(), "?showMenu=StartList&{$pageParameter}"?>")'
                    </td>
                    <td align='center' nowrap width='10%'>
                        <input type='button' name='List' value='��λ����' class='pt12b bg'
                        onClick='location.replace("<?php echo $menu->out_self(), "?showMenu=EndList&id={$uniq}"?>")'
                    </td>
                    <td align='center' nowrap width='40%'>
                        <span class='caption_font'>��Ω��� ����</span>
                    </td>
                    <td align='center' nowrap width='40%'>
                        <?php echo $pageControl?>
                    </td>
                </tr>
            </table>
        </form>
        </caption>
        <tr><td> <!-- ���ߡ� #e6e6e6 -->
    <span id='showAjax'>
        <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <th class='winbox'>&nbsp;</th>
            <th class='winbox pt12b' width='80' nowrap>�ײ��ֹ�</th>
            <th class='winbox pt12b' width='80' nowrap>�����ֹ�</th>
            <th class='winbox pt12b' width='180' nowrap>�����ʡ�̾</th>
            <th class='winbox pt12b' width='80' nowrap>�ײ��</th>
            <th class='winbox pt12b' width='80' nowrap>�Ұ��ֹ�</th>
            <th class='winbox pt12b' width='80' nowrap>��ȼ�</th>
            <th class='winbox pt12b' width='120' nowrap>��Ω���</th>
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
                    <?php echo $res[$r][0]?>
                </a>
            </td>
            <!-- �����ֹ� -->
            <td class='winbox pt12b' align='left' nowrap><?php echo $res[$r][1]?></td>
            <!-- ����̾ -->
            <td class='winbox pt12b' align='left' nowrap><?php echo mb_convert_kana($res[$r][2], 'k')?></td>
            <!-- �ײ�Ŀ� -->
            <td class='winbox pt12b' align='right' nowrap onDblClick='alert("�ײ�ġ��ײ����\n\n<?php echo $res[$r][3]?>��<?php echo $res[$r][13]?>\n\n�Ǥ���")'>
                <?php echo $res[$r][3]?>
            </td>
            <!-- �Ұ��ֹ� -->
            <td class='winbox pt12b' align='center' nowrap><?php echo $res[$r][4]?></td>
            <!-- ��ȼ� -->
            <td class='winbox pt12b' align='left' nowrap onDblClick='alert("�Ұ��ֹ�\n\n <?php echo $res[$r][4]?>")'>
                <?php echo $res[$r][5]?>
            </td>
            <!-- ��Ω������� -->
            <td class='winbox pt12b' align='center' nowrap onDblClick='alert("���ϻ��֤ξܺ�\n\n<?php echo $res[$r][10]?>")'>
                <?php echo $res[$r][6]?>
            </td>
            </tr>
        <?php } ?>
        </table>
    </span>
        </td></tr> <!-- ���ߡ� -->
    </table>
</center>
</body>
<?php if ($_SESSION['s_sysmsg'] != '��Ͽ������ޤ���') { ?>
<?php echo $menu->out_alert_java()?>
<?php } ?>
</html>
