<?php
//////////////////////////////////////////////////////////////////////////////
// ������������ƥ� �Խ������˥塼        ������� Form       MVC View �� //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/11/15 Created   punchMark_editHistory_ViewCondForm.php              //
// 2007/11/19 th.cond ���饹(CSS)���ɲ�                                     //
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
<link rel='stylesheet' href='punchMark_editHistory.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='punchMark_editHistory.js?<?php echo $uniq ?>'></script>
</head>
<body style='overflow-y:hidden;'
    onLoad='
        PunchMarkEditHistory.set_focus(document.ConditionForm.targetMaster, "noSelect");
        // PunchMarkEditHistory.intervalID = setInterval("PunchMarkEditHistory.blink_disp(\"blink_item\")", 1300);
        <?php if ($request->get('AutoStart') != '') echo 'PunchMarkEditHistory.checkANDexecute(document.ConditionForm, 1)'; ?>
    '
>
<center>
<?php echo $menu->out_title_border() ?>
    
    <form name='ConditionForm' action='<?php echo $menu->out_self() ?>' method='post'
        onSubmit='return PunchMarkEditHistory.checkANDexecute(this, 1)'
    >
        <!----------------- ������ ��ʸ��ɽ������ ------------------->
        <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='1'>
            <tr>
                <th class='winbox cond' nowrap>�ޥ���������</th>
                <td class='winbox' nowrap>
                    <select name='targetMaster' class='pt11b' size='1'>
                        <?php echo $model->getMasterOptions($session) ?>
                    </select>
                </td>
                <th class='winbox cond' nowrap>������������</th>
                <td class='winbox' nowrap>
                    <select name='targetHistory' class='pt11b' size='1'>
                        <?php echo $model->getHistoryOptions($session) ?>
                    </select>
                </td>
                <td class='winbox' align='center' rowspan='4'>
                    <input type='submit' class='pt11b' name='ajaxSearch' value='�¹�'>
                    <input type='button' class='pt11b' name='winSearch' value='����' onClick='PunchMarkEditHistory.checkANDexecute(document.ConditionForm, 2)'>
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
