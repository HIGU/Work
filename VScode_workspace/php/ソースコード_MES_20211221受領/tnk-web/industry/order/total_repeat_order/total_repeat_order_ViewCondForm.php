<?php
//////////////////////////////////////////////////////////////////////////////
// ��ԡ�������ȯ��ν��� �Ȳ�                 ������� Form    MVC View �� //
// Copyright (C) 2007-2008 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/12/19 Created   total_repeat_order_ViewCondForm.php                 //
// 2007/12/20 targetLimit��<option>���̤��ѹ�                               //
// 2008/07/30 targetLimit��<option>���̤��ѹ�                          ��ë //
//////////////////////////////////////////////////////////////////////////////
if (_TNK_DEBUG) access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=EUC-JP'>
<meta http-equiv='Content-Style-Type' content='text/css'>
<meta http-equiv='Content-Script-Type' content='text/javascript'>
<!-- <meta http-equiv='Refresh' content='15;URL=<?php echo $menu->out_self(), "?showMenu={$request->get('showMenu')}"?>'> -->
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<link rel='stylesheet' href='total_repeat_order.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='total_repeat_order.js?<?php echo $uniq ?>'></script>
</head>
<body style='overflow-y:hidden;'
    onLoad='
        <?php if ($request->get('AutoStart') != '') echo 'TotalRepeatOrder.checkANDexecute(document.ConditionForm, 1)'; ?>
    '
>
<center>
<?php echo $menu->out_title_border() ?>
    
    <form name='ConditionForm' id='ConditionForm' action='<?php echo $menu->out_self() ?>' method='post'
        onSubmit='return false;'
    >
        <!----------------- ������ ��ʸ��ɽ������ ------------------->
        <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='1'>
            <tr>
                    <!--  bgcolor='#ffffc6' �������� --> 
                <td align='center' class='winbox caption_color pt12b'>
                    <span id='blink_item'>�о�ǯ��</span>
                </td>
                <td class='winbox' align='center'>
                    <select id='targetDateYM' class='pt14b'>
                        <!-- <option value='200608' selected>2006ǯ08��</option> -->
                        <option value=''>��������</option>
                        <?php echo $model->getTargetDateYMvalues($request) ?>
                    </select>
                    ����
                    <input type='text' name='targetDateStr' size='8' class='pt12b' value='<?php echo $request->get('targetDateStr'); ?>' maxlength='8'>
                    ��
                    <input type='text' name='targetDateEnd' size='8' class='pt12b' value='<?php echo $request->get('targetDateEnd'); ?>' maxlength='8'>
                </td>
                <td align='center' class='winbox caption_color pt12b'>
                    <span id='blink_item2'>ɽ�����</span>
                </td>
                <td class='winbox' align='center'>
                    <select id='targetLimit' class='pt12b'>
                        <option value='50' <?php if ($request->get('targetLimit')  ==   50) echo ' selected'?>>��������</option>
                        <option value='200'<?php if ($request->get('targetLimit')  ==  200) echo ' selected'?>>��������</option>
                        <option value='500'<?php if ($request->get('targetLimit')  ==  500) echo ' selected'?>>��������</option>
                        <option value='1000'<?php if ($request->get('targetLimit') == 1000) echo ' selected'?>>��������</option>
                        <option value='3000'<?php if ($request->get('targetLimit') == 3000) echo ' selected'?>>��������</option>
                        <option value='20000'<?php if ($request->get('targetLimit') == 20000) echo ' selected'?>>�� �� ��</option>
                    </select>
                </td>
                <td class='winbox' align='center'>
                    <input type='submit' class='regular' name='exec1' value='�¹�' title='����å�����С����β���ɽ�����ޤ���'>
                    <input type='button' class='regular' id='showWin' value='����' title='����å�����С��̥�����ɥ���ɽ�����ޤ���'>
                    <input type='button' class='regular' id='clear' value='���ꥢ' title='����դ�ꥹ�Ȥ򥯥ꥢ���ޤ���'>
                </td>
            </tr>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
    </form>
    <br>
    <div id='showAjax'>
    </div>
</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
