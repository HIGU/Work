<?php
//////////////////////////////////////////////////////////////////////////////
// ������������ƥ� �߽���Ģ���߽���Ͽ      ������� Form       MVC View �� //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/11/26 Created   punchMark_lendList_ViewLendRegist.php               //
// 2007/11/30 window.opener.parent.PunchMarkLendList.???????? ���ɲ�        //
// 2007/12/01 showMenu.value = "NoList" ���ѹ�                              //
// 2007/12/03 Ajax�Ѥ�Action�� LendList �� noAction ���ѹ�                  //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=EUC-JP'>
<meta http-equiv='Content-Style-Type' content='text/css'>
<meta http-equiv='Content-Script-Type' content='text/javascript'>
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<link rel='stylesheet' href='punchMark_lendList.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='punchMark_lendList.js?<?php echo $uniq ?>'></script>
</head>
<body style='overflow-y:hidden;'
    onLoad='
        PunchMarkLendList.set_focus(document.RegistForm.targetVendor, "noSelect");
    '
>
<center>
    <!----------------- ������ ��ʸ��ɽ������ ------------------->
    <table bgcolor='#d6d3ce' width='100%' height='100%' border='1' cellspacing='0' cellpadding='3'>
        <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
    <table class='winbox_field' width='100%' height='100%' border='1' cellspacing='0' cellpadding='3'>
    <form name='RegistForm' action='<?php echo $menu->out_self() ?>' method='post' target='_self'>
        <tr>
            <th class='winbox cond' nowrap colspan='3'>������߽С�����</th>
        </tr>
        <tr>
            <th class='winbox' nowrap>�߽�������</th><td class='winbox' align='center' colspan='2'><?php echo $result->get('LendDate')?></td>
        </tr>
        <tr>
            <th class='winbox' nowrap>�߽��衡��</th><td class='winbox' align='center'><input type='text' class='pt12b' name='targetVendor' value='<?php echo $session->get_local('targetVendor')?>' size='5' maxlength='5'></td><td class='winbox' align='left'><?php echo $result->get('vendorName')?></td>
        </tr>
        <tr>
            <th class='winbox' nowrap>ô���ԡ���</th><td class='winbox' align='center'><input type='text' class='pt12b' name='targetLendUser' value='<?php echo $result->get('LendUser')?>' size='6' maxlength='6'></td></td><td class='winbox' align='left'><?php echo $result->get('userName')?></td>
        </tr>
        <tr>
            <th class='winbox' nowrap>�������ʡ�</th><td class='winbox' align='center'><?php echo $session->get_local('targetPartsNo')?></td><td class='winbox' align='left'><?php echo $result->get('partsName')?></td>
        </tr>
        <tr>
            <th class='winbox' nowrap>ê���֡���</th><td class='winbox' align='center' colspan='2'><?php echo $session->get_local('targetShelfNo')?></td>
        </tr>
        <tr>
            <th class='winbox' nowrap>���������</th><td class='winbox' align='center' colspan='2'><?php echo $session->get_local('targetMarkCode')?></td>
        </tr>
        <tr>
            <th class='winbox' nowrap>������ơ�</th><td class='winbox' align='center' colspan='2'><?php echo str_replace("\r", '<br>', $result->get('Mark'))?></td>
        </tr>
        <tr>
            <th class='winbox' nowrap>���������</th><td class='winbox' align='center' colspan='2'><?php echo $result->get('Shape')?></td>
        </tr>
        <tr>
            <th class='winbox' nowrap>���������</th><td class='winbox' align='center' colspan='2'><?php echo $result->get('Size')?></td>
        </tr>
        <tr>
            <th class='winbox' nowrap>�����͡���</th><td class='winbox' align='center' colspan='2'><input type='text' name='targetNote' value='<?php echo $session->get_local('targetNote')?>' size='50' maxlength='50'></td>
        </tr>
            <input type='hidden' name='Action'   value='LendRegist'>
            <input type='hidden' name='showMenu' value='LendRegistForm'>
            <input type='hidden' name='page_keep' value='on'>
        <tr>
            <td class='winbox' align='center' colspan='3'>
                <input type='submit' class='pt12b' name='registCheck' value='��ǧ'>
                <input type='button' class='pt12b' name='registClear' value='���' onClick='window.close()'>
                <input type='button' class='pt12b' name='RegistExec'  value='�¹�'<?php echo $result->get('execFlg')?>
                onClick='
                    document.RegistForm.Action.value   = "Lend";
                    document.RegistForm.showMenu.value = "noList";
                    // document.RegistForm.target = "application";
                    document.RegistForm.submit();
                    window.opener.parent.document.ConditionForm.targetMarkCode.value = "<?php echo $session->get_local('targetMarkCode')?>";
                    window.opener.parent.document.ConditionForm.targetShelfNo.value  = "<?php echo $session->get_local('targetShelfNo')?>";
                    window.opener.parent.PunchMarkLendList.checkANDexecute(window.opener.parent.document.ConditionForm, "noAction", "LendList", "showAjax");
                    window.close();
                '
                >
            </td>
        </tr>
    </form>
    </table>
        </td></tr>
    </table> <!----------------- ���ߡ�End ------------------>
</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
