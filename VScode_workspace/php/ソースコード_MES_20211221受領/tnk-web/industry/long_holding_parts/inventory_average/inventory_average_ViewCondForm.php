<?php
//////////////////////////////////////////////////////////////////////////////
// ���߸����� �����ܤη�ʿ�ѽи˿�����ͭ������Ȳ�           MVC View ��  //
// Copyright (C) 2007 - 2013 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2007/06/08 Created   inventory_average_ViewCondForm.php                  //
// 2007/06/10 �����ȹ��ܥ���å����Υѥ�᡼���Ϥ��Τ��� CTM_viewPage ���ɲ�//
// 2007/06/11 �װ��ޥ������Խ��ܥ�����ɲâ�inventory_average.js ���ѹ�     //
// 2007/07/11 �����ֹ�(searchPartsNo)��LIKE�����ɲá����åץإ���ɲ�       //
// 2007/07/23 ��ͭ��λ�����ɲ�(�ե��륿����ǽ)                            //
// 2013/01/29 �Х�������Υݥ�פ��ѹ� ɽ���Τߥǡ����ϥХ����Τޤ� ��ë//
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<!-- <meta http-equiv="Refresh" content="15;URL=<?php echo $menu->out_self(), "?showMenu={$request->get('showMenu')}"?>"> -->
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<link rel='stylesheet' href='inventory_average.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='inventory_average.js?<?php echo $uniq ?>'></script>
<form name='ControlForm'>
    <input type='hidden' name='CTM_selectPage' value='<?php echo $request->get('CTM_selectPage')?>'>
    <input type='hidden' name='CTM_prePage'    value='<?php echo $request->get('CTM_prePage')?>'>
    <input type='hidden' name='CTM_pageRec'    value='<?php echo $request->get('CTM_pageRec')?>'>
    <input type='hidden' name='CTM_back'       value='<?php echo $request->get('CTM_back')?>'>
    <input type='hidden' name='CTM_next'       value='<?php echo $request->get('CTM_next')?>'>
    <input type='hidden' name='CTM_viewPage'   value='<?php echo $request->get('CTM_viewPage')?>'>
</form>
</head>
<body style='overflow-y:hidden;'
    onLoad='
        InventoryAverage.set_focus(document.ConditionForm.searchPartsNo, "select");
        // InventoryAverage.set_focus(document.ConditionForm.targetDivision, "noSelect");
        // setInterval("InventoryAverage.blink_disp(\"blink_item\")", 500);
        <?php if ($request->get('showMenu') == 'Both') echo "InventoryAverage.checkANDexecute(document.ConditionForm);\n"; ?>
    '
>
<center>
<?php echo $menu->out_title_border() ?>
    
    <form name='ConditionForm' action='<?php echo $menu->out_self() ?>' method='post'
        onSubmit='return InventoryAverage.checkANDexecute(this)'
    >
        <!----------------- ������ ��ʸ��ɽ������ ------------------->
        <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='1'>
            <tr>
                <td width='20%' align='center' class='winbox caption_color' nowrap>
                    �����ֹ�
                </td>
                <td width='10%' align='center' class='winbox'>
                    <input type='text' name='searchPartsNo' value='<?php echo $request->get('searchPartsNo') ?>' size='10' maxlength='9'
                        class='pt12b' onKeyUp='InventoryAverage.keyInUpper(this);'
title='
�����ֹ�λ�����ˡ

CP012 �� Ƭ���� CP012 �˹��פ���������

0354 �� ����� 0354 �˹��פ���������

#3   �� �Ǹ夬 #3   �˹��פ���������

-6   �� �Ǹ夬 -6   �˹��פ���������
'
                    >
                </td>
                <td width='20%' align='center' class='winbox caption_color' nowrap
title='
���ʥ��롼�פ����򤷤������Ǹ�����¹Ԥ��ޤ���
���ץ�ϸ��ߤΤȤ������Τ�������ޤ���
'
                >
                    <span id='blink_item'>���ʥ��롼��</span>
                </td>
                <td width='10%' class='winbox' align='center' nowrap>
                    <select name='targetDivision' onChange='InventoryAverage.checkANDexecute(ConditionForm)'>
                        <option value='AL'<?php if($request->get('targetDivision')=='AL')echo ' selected'?>>�����롼��</option>
                        <option value='CA'<?php if($request->get('targetDivision')=='CA')echo ' selected'?>>���ץ�����</option>
                   <!-- <option value='CH'<?php if($request->get('targetDivision')=='CH')echo ' selected'?>>���ץ�ɸ��</option> -->
                   <!-- <option value='CS'<?php if($request->get('targetDivision')=='CS')echo ' selected'?>>���ץ�����</option> -->
                        <option value='LA'<?php if($request->get('targetDivision')=='LA')echo ' selected'?>>��˥�����</option>
                        <option value='LH'<?php if($request->get('targetDivision')=='LH')echo ' selected'?>>��˥��Τ�</option>
                        <option value='LB'<?php if($request->get('targetDivision')=='LB')echo ' selected'?>>���Υݥ��</option>
                        <option value='OT'<?php if($request->get('targetDivision')=='OT')echo ' selected'?>>����¾</option>
                    </select>
                </td>
                <td width='20%' align='center' class='winbox caption_color' nowrap
title='
��ͭ�����ꤷ�����ϡ������ʾ�Τ�Τ�ɽ�����ޤ���
���ꤷ�ʤ���������ɽ�����ޤ���
'
                >
                    <span id='blink_item'>��ͭ��</span>
                </td>
                <td width='10%' class='winbox' align='center' nowrap>
                    <select name='targetHoldMonth'>
                        <option value='0'<?php if($request->get('targetHoldMonth')=='0')echo ' selected'?>>̤����</option>
                        <option value='2'<?php if($request->get('targetHoldMonth')=='1')echo ' selected'?>>������</option>
                        <option value='4'<?php if($request->get('targetHoldMonth')=='4')echo ' selected'?>>������</option>
                        <option value='6'<?php if($request->get('targetHoldMonth')=='6')echo ' selected'?>>������</option>
                        <option value='8'<?php if($request->get('targetHoldMonth')=='8')echo ' selected'?>>������</option>
                        <option value='10'<?php if($request->get('targetHoldMonth')=='10')echo ' selected'?>>��������</option>
                        <option value='12'<?php if($request->get('targetHoldMonth')=='12')echo ' selected'?>>��������</option>
                        <option value='14'<?php if($request->get('targetHoldMonth')=='14')echo ' selected'?>>��������</option>
                        <option value='16'<?php if($request->get('targetHoldMonth')=='16')echo ' selected'?>>��������</option>
                        <option value='18'<?php if($request->get('targetHoldMonth')=='18')echo ' selected'?>>��������</option>
                        <option value='20'<?php if($request->get('targetHoldMonth')=='20')echo ' selected'?>>��������</option>
                        <option value='22'<?php if($request->get('targetHoldMonth')=='22')echo ' selected'?>>��������</option>
                        <option value='24'<?php if($request->get('targetHoldMonth')=='24')echo ' selected'?>>��������</option>
                        <option value='26'<?php if($request->get('targetHoldMonth')=='26')echo ' selected'?>>��������</option>
                        <option value='28'<?php if($request->get('targetHoldMonth')=='28')echo ' selected'?>>��������</option>
                        <option value='30'<?php if($request->get('targetHoldMonth')=='30')echo ' selected'?>>��������</option>
                        <option value='32'<?php if($request->get('targetHoldMonth')=='32')echo ' selected'?>>��������</option>
                        <option value='34'<?php if($request->get('targetHoldMonth')=='34')echo ' selected'?>>��������</option>
                        <option value='36'<?php if($request->get('targetHoldMonth')=='36')echo ' selected'?>>��������</option>
                        <option value='38'<?php if($request->get('targetHoldMonth')=='38')echo ' selected'?>>��������</option>
                        <option value='40'<?php if($request->get('targetHoldMonth')=='40')echo ' selected'?>>��������</option>
                        <option value='42'<?php if($request->get('targetHoldMonth')=='42')echo ' selected'?>>��������</option>
                        <option value='44'<?php if($request->get('targetHoldMonth')=='44')echo ' selected'?>>��������</option>
                        <option value='46'<?php if($request->get('targetHoldMonth')=='46')echo ' selected'?>>��������</option>
                        <option value='48'<?php if($request->get('targetHoldMonth')=='48')echo ' selected'?>>��������</option>
                        <option value='50'<?php if($request->get('targetHoldMonth')=='50')echo ' selected'?>>��������</option>
                        <option value='52'<?php if($request->get('targetHoldMonth')=='52')echo ' selected'?>>��������</option>
                        <option value='54'<?php if($request->get('targetHoldMonth')=='54')echo ' selected'?>>��������</option>
                        <option value='56'<?php if($request->get('targetHoldMonth')=='56')echo ' selected'?>>��������</option>
                        <option value='58'<?php if($request->get('targetHoldMonth')=='58')echo ' selected'?>>��������</option>
                        <option value='60'<?php if($request->get('targetHoldMonth')=='60')echo ' selected'?>>��������</option>
                        <option value='62'<?php if($request->get('targetHoldMonth')=='62')echo ' selected'?>>��������</option>
                        <option value='64'<?php if($request->get('targetHoldMonth')=='64')echo ' selected'?>>��������</option>
                        <option value='66'<?php if($request->get('targetHoldMonth')=='66')echo ' selected'?>>��������</option>
                        <option value='68'<?php if($request->get('targetHoldMonth')=='68')echo ' selected'?>>��������</option>
                        <option value='70'<?php if($request->get('targetHoldMonth')=='70')echo ' selected'?>>��������</option>
                        <option value='72'<?php if($request->get('targetHoldMonth')=='72')echo ' selected'?>>��������</option>
                        <option value='74'<?php if($request->get('targetHoldMonth')=='74')echo ' selected'?>>��������</option>
                        <option value='76'<?php if($request->get('targetHoldMonth')=='76')echo ' selected'?>>��������</option>
                        <option value='78'<?php if($request->get('targetHoldMonth')=='78')echo ' selected'?>>��������</option>
                        <option value='80'<?php if($request->get('targetHoldMonth')=='80')echo ' selected'?>>��������</option>
                        <option value='82'<?php if($request->get('targetHoldMonth')=='82')echo ' selected'?>>��������</option>
                        <option value='84'<?php if($request->get('targetHoldMonth')=='84')echo ' selected'?>>��������</option>
                        <option value='86'<?php if($request->get('targetHoldMonth')=='86')echo ' selected'?>>��������</option>
                        <option value='88'<?php if($request->get('targetHoldMonth')=='88')echo ' selected'?>>��������</option>
                        <option value='90'<?php if($request->get('targetHoldMonth')=='90')echo ' selected'?>>��������</option>
                        <option value='92'<?php if($request->get('targetHoldMonth')=='92')echo ' selected'?>>��������</option>
                        <option value='94'<?php if($request->get('targetHoldMonth')=='94')echo ' selected'?>>��������</option>
                        <option value='96'<?php if($request->get('targetHoldMonth')=='96')echo ' selected'?>>��������</option>
                        <option value='98'<?php if($request->get('targetHoldMonth')=='98')echo ' selected'?>>��������</option>
                        <option value='100'<?php if($request->get('targetHoldMonth')=='100')echo ' selected'?>>����������</option>
                        <option value='999'<?php if($request->get('targetHoldMonth')=='999')echo ' selected'?>>����������</option>
                    </select>
                    �ʾ�
                </td>
                <td width='10%' class='winbox' align='center' nowrap>
                    <input type='submit' name='exec' value='�¹�'>
                    <input type='button' name='clear' value='���ꥢ' onClick='InventoryAverage.viewClear();'>
                </td>
            </tr>
        </table>
            </td>
            <td>
        <table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='1'>
            <tr>
                <td width='90%' class='winbox caption_color' align='center' nowrap>
                    <span id='blink_item'>�װ��ޥ�����</span>
                </td>
                <td width='10%' class='winbox' align='center' nowrap>
                    <input type='button' name='factorMnt' value='�Խ�' onclick='InventoryAverage.AjaxLoadTable("FactorMnt", "showAjax")'>
                </td>
            </tr>
        </table>
            </td>
            </tr>
        </table> <!----------------- ���ߡ�End ------------------>
    </form>
    
    <div id='showAjax'>
    </div>
</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
