<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ҥδ��ܥ������� ���ƥʥ�  �ĶȻ��֡��٤߻��֤��Խ�  MVC View �� //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/06/29 Created   companyCalendar_ViewTimeEdit.php                    //
// 2006/06/30 �ƽи����� window.opener.parent.CompanyCalendar.AjaxLoadUrl //
//            NN7.1���б���window.close()��setTimeout("window.close()", 200)//
//            200=�ƤΥ���ɻ��֤˰�¸����                                //
// 2006/07/05 onUnload='parentReload();'��Ԥ�������NN7.1��NG�ʤΤǥ����� //
//            onUnload='if (document.all) parentReload();'���б�            //
//            submit����G_reloadFlg=false;�ˤ��ƥ���ɤǼ����ƻҴط���ݻ�//
// 2006/07/07 ľ��Υǡ������ԡ��ܥ�����ɲ�                                //
// 2006/07/11 Controller��Execute()�᥽�åɤ��ɲä�Action��showMenu�����β� //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title>��Ҵ��ܥ��������ξܺ��Խ�</title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<link rel='stylesheet' href='companyCalendar.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='companyCalendar.js?<?php echo $uniq ?>'></script>
<script type='text/javascript'>
var G_reloadFlg = true;
function parentReload()
{
    if (!window.opener.parent.CompanyCalendar) return; //�Ǥ�IE�ʤ�OK NN7.1�Ǥ�NG�б���onUnload��if���ɲ�(try catch�Ǥ�OK)
    window.opener.parent.CompanyCalendar.AjaxLoadUrl
    ("<?php echo "{$menu->out_self()}?showMenu=List&year={$request->get('year')}&month={$request->get('month')}&id={$uniq}" ?>");
}
</script>
</head>
<body style='overflow-x:hidden; background-color:#e6e6e6;'
    onLoad='
        setInterval("CompanyCalendar.winActiveChk()", 30);
        // CompanyCalendar.set_focus(document.BusinessHourEditForm.bh_note, "noSelect");
        CompanyCalendar.set_focus(document.CalendarCommentForm.clear, "noSelect");
    '
    onUnload='if (document.all) if (G_reloadFlg) parentReload(); // IE�ʤ�'
>
<center>
    <table width='100%' bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
    <table width='100%' class='winbox_field list' bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr class='pt14b'>
            <th class='winbox' colspan='5' align='center' nowrap>
                <?php echo $request->get('year') ?>ǯ<?php echo $request->get('month') ?>��<?php echo $request->get('day')?>��
                �Ķ������ٶ��������� �ڤ� �����Ȥ��Խ�
            </th>
        </tr>
        <tr>
            <td class='winbox' nowrap>
                �ġ�������
            </td>
            <form name='CalendarBDForm' action='<?php echo $menu->out_self(), "?Action=bdDetailSave&showMenu=TimeEdit&year={$request->get('year')}&month={$request->get('month')}&day={$request->get('day')}&id={$uniq}"?>' method='post'
                onSubmit='G_reloadFlg=false;'
            >
            <td class='winbox' nowrap align='center'>
                <?php if ($result->get('bd_flg') == 't') { ?>
                <input type='submit' name='bd_flg' value='�Ķ���' style='color:black;'>
                <?php } else { ?>
                <input type='submit' name='bd_flg' value='�ٶ���' style='color:gray;'>
                <?php } ?>
            </td>
            </form>
            <td class='winbox' nowrap>
                ������
            </td>
            <form name='CalendarCommentForm' action='<?php echo $menu->out_self(), "?Action=bdCommentSave&showMenu=TimeEdit&year={$request->get('year')}&month={$request->get('month')}&day={$request->get('day')}&id={$uniq}"?>' method='post'
                onSubmit='G_reloadFlg = false;'
            >
            <td class='winbox' nowrap>
                <input type='text' name='note' size='40' maxlength='50' value='<?php echo $result->get('bd_note')?>'
                    title='ɬ�פ�����бĶ����������˴ؤ��ƤΥ����Ȥ����Ϥ��ޤ���' style='height:30px;' class='pt14b'
                >
            </td>
            <td class='winbox' nowrap>
                <input type='submit' name='bdSave' value='��Ͽ' style='color:blue;'>
                <input type='button' name='clear' value='�Ĥ���' onClick='parentReload(); setTimeout("window.close()", 200);'>
            </td>
            </form>
        </tr>
    </table>
        </td></tr>
    </table> <!----------------- ���ߡ�End ------------------>
    
    <br>
    
    <form name='BusinessHourEditForm' action='<?php echo $menu->out_self(), "?Action=TimeSave&showMenu=TimeEdit&year={$request->get('year')}&month={$request->get('month')}&day={$request->get('day')}&id={$uniq}"?>' method='post'
        onSubmit='G_reloadFlg=false; return CompanyCalendar.checkTimeValue(this.hours.value, this);'
    >
    <table width='100%' bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
    <table width='100%' class='winbox_field list' bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr class='pt14b'>
            <th class='winbox' colspan='6' align='center' nowrap>
                <?php echo $request->get('year') ?>ǯ<?php echo $request->get('month') ?>��<?php echo $request->get('day')?>��
                �ĶȻ��֤��Խ�
            </th>
        </tr>
        <tr>
            <td class='winbox' nowrap>
                �ĶȻ���
            </td>
            <td class='winbox' nowrap align='right'>
                ����
                <select name='str_hour' class='pt14b'
                    onChange='CompanyCalendar.setTimeValue(document.BusinessHourEditForm, document.BusinessHourEditForm.hours);'
                >
                    <?php echo $model->getHourValues($result->get('str_hour'))?>
                </select>
                ��
                <select name='str_minute' class='pt14b'
                    onChange='CompanyCalendar.setTimeValue(document.BusinessHourEditForm, document.BusinessHourEditForm.hours);'
                >
                    <?php echo $model->getMinuteValues($result->get('str_minute'))?>
                </select>
                ʬ
                <input type='hidden' name='old_str_time' value='<?php echo $result->get('str_hour')?>:<?php echo $result->get('str_minute')?>'>
            </td>
            <td class='winbox' nowrap>
                ��
            </td>
            <td class='winbox' nowrap>
                ��λ
                <select name='end_hour' class='pt14b'
                    onChange='CompanyCalendar.setTimeValue(document.BusinessHourEditForm, document.BusinessHourEditForm.hours);'
                >
                    <?php echo $model->getHourValues($result->get('end_hour'))?>
                </select>
                ��
                <select name='end_minute' class='pt14b'
                    onChange='CompanyCalendar.setTimeValue(document.BusinessHourEditForm, document.BusinessHourEditForm.hours);'
                >
                    <?php echo $model->getMinuteValues($result->get('end_minute'))?>
                </select>
                ʬ
                <input type='hidden' name='old_end_time' value='<?php echo $result->get('end_hour')?>:<?php echo $result->get('end_minute')?>'>
            </td>
            <td class='winbox' nowrap colspan='2'>
                <input type='text' name='hours' style='text-align:right; border:1px solid #e6e6e6; background-color:#e6e6e6;' value='<?php echo $result->get('hours')?>' size='4' class='pt14b' readonly>
                ʬ
                <input type='hidden' name='old_hours' value='<?php echo $result->get('hours')?>'>
            </td>
        </tr>
        <tr>
            <td class='winbox' nowrap>
                ������
            </td>
            <td class='winbox' colspan='4' nowrap>
                <input type='text' name='bh_note' size='40' maxlength='50' value='<?php echo $result->get('bh_note')?>'
                    title='ɬ�פ�����бĶȻ��֤˴ؤ��ƤΥ����Ȥ����Ϥ��ޤ���' style='height:30px;' class='pt14b'
                >
                <input type='hidden' name='old_bh_note' value='<?php echo $result->get('bh_note')?>'>
            </td>
            <td class='winbox' nowrap>
                <input type='submit' name='bdSave' value='��Ͽ' style='color:blue;'>
                <input type='submit' name='bdDelete' value='���' style='color:red;' onClick='return confirm("�������ȸ��ˤ��᤻�ޤ���\n\n\n�������Ǥ�����");'>
                <input type='button' name='clear' value='�Ĥ���' onClick='parentReload(); setTimeout("window.close()", 200);'>
            </td>
        </tr>
    </table>
        </td></tr>
    </table> <!----------------- ���ߡ�End ------------------>
        <div style='position:relative; top:6px;'>
        </div>
    </form>
    
    <br>
    
    <table width='100%' bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
    <table width='100%' class='winbox_field list' bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr class='pt14b'>
            <th class='winbox' colspan='7' align='center' nowrap>
                <?php echo $request->get('year') ?>ǯ<?php echo $request->get('month') ?>��<?php echo $request->get('day')?>��
                �ٷƻ��֤��Խ�
            </th>
        </tr>
        <?php $rows = $result->get('array_rows'); $res = $result->get_array(); ?>
        <?php for ($i=0; $i<$rows; $i++) { ?>
        <form name='AbsentTimeEditForm<?php echo $i?>' action='<?php echo $menu->out_self(), "?Action=TimeSave&showMenu=TimeEdit&year={$request->get('year')}&month={$request->get('month')}&day={$request->get('day')}&id={$uniq}"?>' method='post'
            onSubmit='G_reloadFlg=false;  return CompanyCalendar.checkTimeValue(this.absent_time.value, this);'
        >
            <tr>
                <td class='winbox' nowrap rowspan='2'>
                    <?php echo ($i + 1) ?>
                </td>
                <td class='winbox' nowrap>
                    �ٷƻ���
                </td>
                <td class='winbox' nowrap align='right'>
                    ����
                    <select name='str_hour' class='pt14b'
                        onChange='CompanyCalendar.setTimeValue(document.AbsentTimeEditForm<?php echo $i?>, document.AbsentTimeEditForm<?php echo $i?>.absent_time);'
                    >
                        <?php echo $model->getHourValues($res[$i]['str_hour'])?>
                    </select>
                    ��
                    <select name='str_minute' class='pt14b'
                        onChange='CompanyCalendar.setTimeValue(document.AbsentTimeEditForm<?php echo $i?>, document.AbsentTimeEditForm<?php echo $i?>.absent_time);'
                    >
                        <?php echo $model->getMinuteValues($res[$i]['str_minute'])?>
                    </select>
                    ʬ
                    <input type='hidden' name='old_str_time' value='<?php echo $res[$i]['str_hour']?>:<?php echo $res[$i]['str_minute']?>'>
                </td>
                <td class='winbox' nowrap>
                    ��
                </td>
                <td class='winbox' nowrap>
                    ��λ
                    <select name='end_hour' class='pt14b'
                        onChange='CompanyCalendar.setTimeValue(document.AbsentTimeEditForm<?php echo $i?>, document.AbsentTimeEditForm<?php echo $i?>.absent_time);'
                    >
                        <?php echo $model->getHourValues($res[$i]['end_hour'])?>
                    </select>
                    ��
                    <select name='end_minute' class='pt14b'
                        onChange='CompanyCalendar.setTimeValue(document.AbsentTimeEditForm<?php echo $i?>, document.AbsentTimeEditForm<?php echo $i?>.absent_time);'
                    >
                        <?php echo $model->getMinuteValues($res[$i]['end_minute'])?>
                    </select>
                    ʬ
                    <input type='hidden' name='old_end_time' value='<?php echo $res[$i]['end_hour']?>:<?php echo $res[$i]['end_minute']?>'>
                </td>
                <td class='winbox' nowrap>
                    <input type='text' name='absent_time' style='text-align:right; border:1px solid #e6e6e6; background-color:#e6e6e6;' value='<?php echo $res[$i]['absent_time']?>' size='4' class='pt14b' readonly>
                    <input type='hidden' name='old_absent_time' value='<?php echo $res[$i]['absent_time']?>'>
                    ʬ
                </td>
                <td class='winbox' nowrap rowspan='2' align='center'>
                    <input type='submit' name='atSave' value='��Ͽ' style='color:blue;'><br>
                    <input type='submit' name='atDelete' value='���' onClick='return confirm("�������ȸ��ˤ��᤻�ޤ���\n\n\n�������Ǥ�����");'
                        style='color:red; position:relative; top:6px;'
                    >
                </td>
            </tr>
            <tr>
                <td class='winbox' nowrap>
                    ������
                </td>
                <td class='winbox' colspan='4' nowrap>
                    <input type='text' name='absent_note' size='40' maxlength='50' value='<?php echo $res[$i]['absent_note']?>'
                        title='ɬ�פ�����еٷƻ��֤˴ؤ��ƤΥ����Ȥ����Ϥ��ޤ���' style='height:30px;' class='pt14b'
                    >
                    <input type='hidden' name='old_absent_note' value='<?php echo $res[$i]['absent_note']?>'>
                </td>
            </tr>
        </form>
        <?php } ?>
        <form name='AbsentTimeEditForm<?php echo $i?>' action='<?php echo $menu->out_self(), "?Action=TimeSave&showMenu=TimeEdit&year={$request->get('year')}&month={$request->get('month')}&day={$request->get('day')}&id={$uniq}"?>' method='post'
            onSubmit='G_reloadFlg=false;  return CompanyCalendar.checkTimeValue(this.absent_time.value, this);'
        >
            <tr>
                <td class='winbox' nowrap rowspan='2'>
                    <?php echo ($i + 1) ?>
                </td>
                <td class='winbox' nowrap>
                    �ٷƻ���
                </td>
                <td class='winbox' nowrap align='right'>
                    ����
                    <select name='str_hour' class='pt14b'
                        onChange='CompanyCalendar.setTimeValue(document.AbsentTimeEditForm<?php echo $i?>, document.AbsentTimeEditForm<?php echo $i?>.absent_time);'
                    >
                        <?php echo $model->getHourValues(-1)?>
                    </select>
                    ��
                    <select name='str_minute' class='pt14b'
                        onChange='CompanyCalendar.setTimeValue(document.AbsentTimeEditForm<?php echo $i?>, document.AbsentTimeEditForm<?php echo $i?>.absent_time);'
                    >
                        <?php echo $model->getMinuteValues(-1)?>
                    </select>
                    ʬ
                </td>
                <td class='winbox' nowrap>
                    ��
                </td>
                <td class='winbox' nowrap>
                    ��λ
                    <select name='end_hour' class='pt14b'
                        onChange='CompanyCalendar.setTimeValue(document.AbsentTimeEditForm<?php echo $i?>, document.AbsentTimeEditForm<?php echo $i?>.absent_time);'
                    >
                        <?php echo $model->getHourValues(-1)?>
                    </select>
                    ��
                    <select name='end_minute' class='pt14b'
                        onChange='CompanyCalendar.setTimeValue(document.AbsentTimeEditForm<?php echo $i?>, document.AbsentTimeEditForm<?php echo $i?>.absent_time);'
                    >
                        <?php echo $model->getMinuteValues(-1)?>
                    </select>
                    ʬ
                </td>
                <td class='winbox' nowrap>
                    <input type='text' name='absent_time' style='text-align:right; border:1px solid #e6e6e6; background-color:#e6e6e6;' value='' size='4' class='pt14b' readonly>
                    ʬ
                </td>
                <td class='winbox' nowrap rowspan='2' align='center'>
                    <input type='submit' name='atSave' value='�ɲ�' style='color:blue;'><br>
                    <input type='button' name='clear' value='�Ĥ���' onClick='parentReload(); setTimeout("window.close()", 200);'
                        style='position:relative; top:6px;'
                    >
                </td>
            </tr>
            <tr>
                <td class='winbox' nowrap>
                    ������
                </td>
                <td class='winbox' colspan='4' nowrap>
                    <input type='text' name='absent_note' size='40' maxlength='50' value=''
                        title='ɬ�פ�����еٷƻ��֤˴ؤ��ƤΥ����Ȥ����Ϥ��ޤ���' style='height:30px;' class='pt14b'
                    >
                </td>
            </tr>
        </form>
    </table>
        </td></tr>
    </table> <!----------------- ���ߡ�End ------------------>
    <?php if ($result->get('str_hour') == '') { ?>
    <div>
        <form name='DataInheritanceForm' action='<?php echo $menu->out_self(), "?Action=TimeCopy&showMenu=TimeEdit&year={$request->get('year')}&month={$request->get('month')}&day={$request->get('day')}&id={$uniq}"?>' method='post'
            onSubmit='G_reloadFlg=false;'
        >
            <input type='submit' name='DataCopy' value='ľ��Υǡ������ԡ�' title='�������ľ��Υǡ����򥳥ԡ����ޤ���'
                style='position:relative; top:6px;'
            >
        </form>
    </div>
    <?php } ?>
</center>
</body>
<?php echo $menu->out_alert_java(false)?>
</html>
