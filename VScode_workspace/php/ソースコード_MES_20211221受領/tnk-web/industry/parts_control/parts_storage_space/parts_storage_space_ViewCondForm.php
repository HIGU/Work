<?php
//////////////////////////////////////////////////////////////////////////////
// ���긡�����ǻ����ݴɾ��ΰ���(NKB������)�Ȳ�  ������� Form MVC View �� //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/06/21 Created   parts_storage_space_ViewCondForm.php                //
//////////////////////////////////////////////////////////////////////////////
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
<link rel='stylesheet' href='parts_storage_space.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<link rel='shortcut icon' href='/favicon.ico?=<?php echo $uniq ?>'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='parts_storage_space.js?<?php echo $uniq ?>'></script>
</head>
<body style='overflow-y:hidden;'
    onLoad='
        PartsStorageSpace.set_focus(document.ConditionForm.targetDateStr, "noSelect");
        <?php if ($request->get('AutoStart') != '') echo 'PartsStorageSpace.checkANDexecute(document.ConditionForm, 1)'; ?>
    '
>
<center>
<?php echo $menu->out_title_border() ?>
    
    <form name='ConditionForm' action='<?php echo $menu->out_self() ?>' method='post'
        onSubmit='return PartsStorageSpace.checkANDexecute(this, 1)'
    >
        <!----------------- ������ ��ʸ��ɽ������ ------------------->
        <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='1'>
            <tr>
                    <!--  bgcolor='#ffffc6' ��������  #ceffce ������ɥ��꡼�� --> 
                <td align='center' class='winbox caption_color'>
                    <span id='blink_item'>�о�ǯ����</span>
                </td>
                <td class='winbox' align='center'>
                    <select name='targetDate' class='pt14b' onChange='PartsStorageSpace.dateCreate(document.ConditionForm)'>
                        <!-- <option value='20060621' selected>2006ǯ06��21��</option> -->
                        <option value='<?php echo workingDayOffset('-0') ?>' selected>��������</option>
                        <?php echo $model->getTargetDateValues($request) ?>
                    </select>
                    ����
                    <input type='text' name='targetDateStr' size='8' class='pt12b' value='<?php echo $request->get('targetDateStr'); ?>' maxlength='8'>
                    ��
                    <input type='text' name='targetDateEnd' size='8' class='pt12b' value='<?php echo $request->get('targetDateEnd'); ?>' maxlength='8'>
                </td>
                <td align='center' class='winbox caption_color'>
                    ���˾��
                </td>
                <td class='winbox' align='center'>
                    <select name='targetLocate' class='pt14b'>
                        <option value='14'<?php if($request->get('targetLocate') == 14) echo ' selected'?>>�Σˣ�</option>
                        <option value='30'<?php if($request->get('targetLocate') == 30) echo ' selected'?>>���</option>
                        <option value='33'<?php if($request->get('targetLocate') == 33) echo ' selected'?>>��Ω</option>
                        <option value='52'<?php if($request->get('targetLocate') == 52) echo ' selected'?>>���</option>
                        <option value='40'<?php if($request->get('targetLocate') == 40) echo ' selected'?>>����</option>
                        <option value='91'<?php if($request->get('targetLocate') == 91) echo ' selected'?>>����</option>
                    </select>
                </td>
                <td class='winbox' align='center'>
                    <input type='button' name='exec1' value='�¹�' onClick='PartsStorageSpace.checkANDexecute(document.ConditionForm, 1);' title='����å�����С����β���ɽ�����ޤ���'>
                    &nbsp;
                    <input type='button' name='exec2' value='����' onClick='PartsStorageSpace.checkANDexecute(document.ConditionForm, 2);' title='����å�����С��̥�����ɥ���ɽ�����ޤ���'>
                    &nbsp;
                    <input type='button' name='clear' value='���ꥢ' onClick='PartsStorageSpace.viewClear();'>
                </td>
            </tr>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
    </form>
    <!-- <br> -->
    <div id='showAjax'>
    </div>
</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
