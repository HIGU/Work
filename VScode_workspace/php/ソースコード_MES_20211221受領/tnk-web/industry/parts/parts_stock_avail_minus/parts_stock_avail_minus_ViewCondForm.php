<?php
//////////////////////////////////////////////////////////////////////////////
// ���� �߸ˡ�ͭ�����ѿ�(ͽ��߸˿�)�ޥ��ʥ��ꥹ�ȾȲ�         MVC View ��  //
// Copyright (C) 2007 - 2013 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2007/08/02 Created   parts_stock_avail_minus_ViewCondForm.php            //
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
<link rel='stylesheet' href='parts_stock_avail_minus.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='parts_stock_avail_minus.js?<?php echo $uniq ?>'></script>
</head>
<body style='overflow-y:hidden;'
    onLoad='
        // PartsStockAvailMinus.set_focus(document.ConditionForm.searchPartsNo, "select");
        PartsStockAvailMinus.set_focus(document.ConditionForm.targetDivision, "noSelect");
        // setInterval("PartsStockAvailMinus.blink_disp(\"blink_item\")", 500);
        <?php if ($request->get('showMenu') == 'Both') echo "PartsStockAvailMinus.checkANDexecute(document.ConditionForm);\n"; ?>
    '
>
<center>
<?php echo $menu->out_title_border() ?>
    
    <form name='ConditionForm' action='<?php echo $menu->out_self() ?>' method='post'
        onSubmit='return PartsStockAvailMinus.checkANDexecute(this)'
    >
        <!----------------- ������ ��ʸ��ɽ������ ------------------->
        <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='1'>
            <tr>
                <td width='20%' align='center' class='winbox caption_color' nowrap
title='
���ʥ��롼�פ����򤷤������Ǹ�����¹Ԥ��ޤ���
���ץ�ϸ��ߤΤȤ������Τ�������ޤ���
'
                >
                    <span id='blink_item'>���ʥ��롼��</span>
                </td>
                <td width='10%' class='winbox' align='center' nowrap>
                    <select name='targetDivision' onChange='PartsStockAvailMinus.checkANDexecute(document.ConditionForm)'>
                        <option value=''  <?php if($request->get('targetDivision')==''  )echo ' selected'?>>����ǲ�����</option>
                        <option value='AL'<?php if($request->get('targetDivision')=='AL')echo ' selected'?>>�����롼��</option>
                        <option value='CA'<?php if($request->get('targetDivision')=='CA')echo ' selected'?>>���ץ�����</option>
                        <option value='CH'<?php if($request->get('targetDivision')=='CH')echo ' selected'?>>���ץ�ɸ��</option>
                        <option value='CS'<?php if($request->get('targetDivision')=='CS')echo ' selected'?>>���ץ�����</option>
                        <option value='LA'<?php if($request->get('targetDivision')=='LA')echo ' selected'?>>��˥�����</option>
                        <option value='LL'<?php if($request->get('targetDivision')=='LL')echo ' selected'?>>��˥��Τ�</option>
                        <option value='LB'<?php if($request->get('targetDivision')=='LB')echo ' selected'?>>���Υݥ��</option>
                    </select>
                </td>
                <td width='20%' align='center' class='winbox caption_color' nowrap
title='
��ͭ�����ꤷ�����ϡ������ʾ�Τ�Τ�ɽ�����ޤ���
���ꤷ�ʤ���������ɽ�����ޤ���
'
                >
                    <span id='blink_item'>�ޥ��ʥ����</span>
                </td>
                <td width='10%' class='winbox' align='center' nowrap>
                    <select name='targetMinusItem'>
                        <option value='1'<?php if($request->get('targetMinusItem')=='1')echo ' selected'?>>����</option>
                        <option value='2'<?php if($request->get('targetMinusItem')=='2')echo ' selected'?>>���ߺ߸�</option>
                        <option value='3'<?php if($request->get('targetMinusItem')=='3')echo ' selected'?>>����߸�</option>
                        <option value='4'<?php if($request->get('targetMinusItem')=='4')echo ' selected'?>>�ǽ��߸�</option>
                    </select>
                </td>
                <td width='20%' align='center' class='winbox caption_color' nowrap>
                    �����ֹ�
                </td>
                <td width='10%' align='center' class='winbox'>
                    <input type='text' name='searchPartsNo' value='<?php echo $request->get('searchPartsNo') ?>' size='9' maxlength='9'
                        class='pt12b' onKeyUp='PartsStockAvailMinus.keyInUpper(this);'
title='
�����ֹ�λ�����ˡ

CP012 �� Ƭ���� CP012 �˹��פ���������

0354 �� ����� 0354 �˹��פ���������

#3   �� �Ǹ夬 #3   �˹��פ���������

-6   �� �Ǹ夬 -6   �˹��פ���������
'
                    >
                </td>
                <td width='10%' class='winbox' align='center' nowrap>
                    <input type='submit' name='exec' value='�¹�'>
                    <input type='button' name='clear' value='���ꥢ' onClick='PartsStockAvailMinus.viewClear();'>
                    <input type='button' name='sclear' value='�����Ȳ��'class='cancelButton' onClick='PartsStockAvailMinus.sortClear(document.ConditionForm)'>
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
