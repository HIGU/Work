<?php
//////////////////////////////////////////////////////////////////////////////
// ���� �߸� ͽ�� �Ȳ� (������ȯ������Ȳ�)  ������� Form       MVC View �� //
// Copyright (C) 2006-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/05/25 Created   parts_stock_plan_ViewCondForm.php                   //
// 2006/06/02 ��ʸ���Ѵ��ѤΥ��٥�ȥϥ�ɥ顼onKeyUp���ɲ�                 //
// 2007/02/08 �߸˷��򤫤�ƽФ��줿���Υ��������Ⱥ߸˷���ƽФ����ɲ�    //
// 2007/02/21 Windowɽ����noMenu(�����ͽ��α���������̵��)�б�            //
// 2007/03/13 �߸˷���Ȳ���Υѥ�᡼������ꥯ�����Ȥˤ����ʬ���ɲ�    //
// 2007/05/22 ����ɬ�����ξȲ���ɲ� requireDate�Υꥯ�����ȥ����쥯�Ƚ���  //
// 2007/06/22 noMenu��Ajax���Ϥ�����hidden°���ǥե����������ɲ�            //
// 2007/07/27 �����ֹ���Ѥ��ƺ߸˷���Ȳ�򥯥�å���������Ѥ������ʤ�  //
//            ȿ�Ǥ����뤿��<a href='ľ��URL' �� 'javascript...'���б�      //
// 2007/10/19 noMenu����keyInUpper()���Ȥ��ʤ�����                          //
//            ñ���Ǥ�windowKeyCheckMethod.js�������keyInUpper()�����ػ��� //
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
<link rel='stylesheet' href='parts_stock_plan.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='parts_stock_plan.js?<?php echo $uniq ?>'></script>
</head>
<body style='overflow-y:hidden;'
    onLoad='
        PartsStockPlan.set_focus(document.ConditionForm.targetPartsNo, "select");
        // PartsStockPlan.intervalID = setInterval("PartsStockPlan.blink_disp(\"blink_item\")", 1300);
        <?php if ($request->get('targetPartsNo') != '' && $request->get('requireDate') != '') { echo 'PartsStockPlan.checkANDexecute(document.ConditionForm, 3)'; ?>
        <?php } elseif ($request->get('targetPartsNo') != '') { echo 'PartsStockPlan.checkANDexecute(document.ConditionForm, 1)'; }?>
    '
>
<center>
<?php if ($request->get('noMenu')) { ?>
<script type='text/javascript' src='/windowKeyCheckMethod.js?<?php echo $uniq ?>'></script>
<?php } else { ?>
<?php echo $menu->out_title_border() ?>
<?php } ?>
    
    <form name='ConditionForm' action='<?php echo $menu->out_self() ?>' method='post'
        onSubmit='return PartsStockPlan.checkANDexecute(this, 1)'
    >
        <!----------------- ������ ��ʸ��ɽ������ ------------------->
        <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='1'>
            <tr>
                    <!--  bgcolor='#ffffc6' �������� --> 
                <td colspan='7' align='center' class='winbox caption_color'>
                    <span id='blink_item'>�����ֹ�</span>
                </td>
                <td class='winbox' align='center'>
                    <input type='text' name='targetPartsNo' size='9' class='pt12b' value='<?php echo $request->get('targetPartsNo'); ?>' maxlength='9'
                        <?php if ($request->get('noMenu')) { ?>
                        onKeyUp='keyInUpper(this);'
                        <?php } else { ?>
                        onKeyUp='PartsStockPlan.keyInUpper(this);'
                        <?php } ?>
                    >
                </td>
                <td class='winbox' align='center'>
                    <input type='button' name='exec1' value='�¹�' onClick='PartsStockPlan.checkANDexecute(document.ConditionForm, 1);' title='����å�����С����β���ɽ�����ޤ���'>
                    &nbsp;
                    <input type='button' name='exec2' value='����' style='width:54px;' onClick='PartsStockPlan.checkANDexecute(document.ConditionForm, 2);' title='�̥�����ɥ���ɽ�����ޤ���'>
                    &nbsp;
                    <input type='button' name='clear' value='���ꥢ' style='width:54px;' onClick='PartsStockPlan.viewClear();'>
                    &nbsp;
                    <input type='button' name='exec3' value='ɬ����' style='width:54px;' onClick='PartsStockPlan.checkANDexecute(document.ConditionForm, 3);' title='���β���ȯ�������������Τߤˤ�ɬ������ɽ�����ޤ���'>
                    &nbsp;
                    <input type='button' name='exec3' value='ɬ����' style='width:54px;' onClick='PartsStockPlan.checkANDexecute(document.ConditionForm, 4);' title='�̥�����ɥ���ȯ�������������Τߤˤ�ɬ������ɽ�����ޤ���'>
                </td>
                <?php
                if ($stockViewFlg && $request->get('targetPartsNo')) {
                    echo "<td class='winbox' align='center'>\n";
                    if ($request->get('noMenu') && $request->get('material')) {
                        echo "&nbsp&nbsp<a href='javascript:location.replace(\"", $menu->out_action('�߸˷���Ȳ�'), "?parts_no=\" + escape(document.ConditionForm.targetPartsNo.value) + \"&material=1&noMenu=yes\")' style='text-decoration:none;'>�߸˷���Ȳ�</a>\n";
                    } elseif ($request->get('noMenu')) {
                        echo "&nbsp&nbsp<a href='javascript:location.replace(\"", $menu->out_action('�߸˷���Ȳ�'), "?parts_no=\" + escape(document.ConditionForm.targetPartsNo.value) + \"&noMenu=yes\")' style='text-decoration:none;'>�߸˷���Ȳ�</a>\n";
                    } elseif ($request->get('material')) {
                        echo "&nbsp&nbsp<a href='javascript:location.replace(\"", $menu->out_action('�߸˷���Ȳ�'), "?parts_no=\" + escape(document.ConditionForm.targetPartsNo.value) + \"&material=1\")' style='text-decoration:none;'>�߸˷���Ȳ�</a>\n";
                    } elseif ($request->get('aden_flg')) {
                        //echo "&nbsp&nbsp<a href='javascript:location.replace(\"", $menu->out_action('�߸˷���Ȳ�'), "?parts_no=\" + escape(document.ConditionForm.targetPartsNo.value) + \"&sc_no=", $request->get('sc_no'), "&aden_flg=1\")' style='text-decoration:none;'>�߸˷���Ȳ�</a>\n";
                    } else {
                        echo "&nbsp&nbsp<a href='javascript:location.replace(\"", $menu->out_action('�߸˷���Ȳ�'), "?parts_no=\" + escape(document.ConditionForm.targetPartsNo.value) )' style='text-decoration:none;'>�߸˷���Ȳ�</a>\n";
                    }
                    echo "</td>\n";
                }
                ?>
            </tr>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        <input type='hidden' name='noMenu' value='<?php echo $request->get('noMenu')?>'>
    </form>
    <div id='showAjax'>
    </div>
</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
