<?php
//////////////////////////////////////////////////////////////////////////////
// ������������ƥ� �߽���Ģ��˥塼        ������� Form       MVC View �� //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/11/16 Created   punchMark_lendList_ViewCondForm.php                 //
// 2007/11/30 �߽С��ֵѥܥ���򥳥��ȥ�����                              //
// 2007/12/03 AutoStart�ꥯ�����Ȥ�shoMenu�Υǡ�������Ѥ���褦���ѹ�      //
// 2007/12/05 checkPXD.js ���ɲä���PXD�ˤ褦������򥵥ݡ���               //
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
<link rel='stylesheet' href='punchMark_lendList.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='punchMark_lendList.js?<?php echo $uniq ?>'></script>
<script type='text/javascript' src='/pxd/checkPXD.js?<?php echo $uniq ?>'></script>
</head>
<body style='overflow-y:hidden;'
    onLoad='
        PunchMarkLendList.set_focus(document.ConditionForm.targetMaster, "noSelect");
        // PunchMarkLendList.intervalID = setInterval("PunchMarkLendList.blink_disp(\"blink_item\")", 1300);
        <?php if ($request->get('AutoStart') != '') echo "PunchMarkLendList.checkANDexecute(document.ConditionForm, \"noAction\", \"{$request->get('AutoStart')}\", \"showAjax\")"; ?>
    '
>
<center>
<?php echo $menu->out_title_border() ?>
    
    <form name='ConditionForm' action='<?php echo $menu->out_self() ?>' method='post'
        onSubmit='return PunchMarkLendList.checkANDexecute(this, "noAction", "MarkList", "showAjax")'
    >
        <!----------------- ������ ��ʸ��ɽ������ ------------------->
        <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='1'>
            <tr>
                <th class='winbox cond' nowrap>�����ֹ�</th>
                <th class='winbox cond' nowrap>���������</th>
                <th class='winbox cond' nowrap>ê������</th>
                <th class='winbox cond' nowrap>��������</th>
                <th class='winbox cond' colspan='1' nowrap>��ǽ�ܥ���</th>
            </tr>
            <tr>
                <td class='winbox' align='center'><input type='text' name='targetPartsNo' value='<?php echo $request->get('targetPartsNo') ?>' size='10' maxlength='9' onKeyUp='baseJS.keyInUpper(this);'></td>
                <td class='winbox' align='center'><input type='text' name='targetMarkCode' value='<?php echo $request->get('targetMarkCode') ?>' size='6' maxlength='6'></td>
                <td class='winbox' align='center'><input type='text' name='targetShelfNo' value='<?php echo $request->get('targetShelfNo') ?>' size='6' maxlength='6'></td>
                <td class='winbox' align='center'><input type='text' name='targetNote' value='<?php echo $request->get('targetNote') ?>' size='25' maxlength='30'></td>
                <td class='winbox' align='center' rowspan='1'>
                    <input type='submit' class='pt11b' name='ajaxSearch' value='���'> <input type='button' class='pt11b' name='ajaxLend' value='��Ģ' onClick='PunchMarkLendList.checkANDexecute(document.ConditionForm, "noAction", "LendList", "showAjax")'> <input type='button' class='pt11b' name='ajaxClear'  value='���' onClick='PunchMarkLendList.viewClear("showAjax")'>
                    <!-- <input type='button' class='pt11b' name='ajaxLend'   value='�߽�' onClick='PunchMarkLendList.checkANDexecute(document.ConditionForm, "Lend", "LendList", "showAjax")'> <input type='button' class='pt11b' name='ajaxRturn'  value='�ֵ�' onClick='PunchMarkLendList.checkANDexecute(document.ConditionForm, "Return", "LendList", "showAjax")'> -->
                    <!-- <input type='button' class='pt11b' name='winSearch' value='����' onClick='PunchMarkLendList.checkANDexecute(document.ConditionForm, "Search", "MarkListWin", "showAjax")'> -->
                </td>
            </tr>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
    </form>
    <div id='showAjax'>
    </div>
</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
