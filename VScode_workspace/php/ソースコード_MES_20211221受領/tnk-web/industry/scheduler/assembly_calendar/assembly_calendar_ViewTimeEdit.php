<?php
//////////////////////////////////////////////////////////////////////////////
// 組立ラインのカレンダーメンテナンス 稼働時間・停止時間の編集  MVC View 部 //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/07/11 Created   assembly_calendar_ViewTimeEdit.php                  //
// 2006/09/29 営／休切替 → 稼／停切替 , 休業日 → 停止日 へ変更            //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title>組立ラインカレンダーの詳細編集</title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<link rel='stylesheet' href='assembly_calendar.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='assembly_calendar.js?<?php echo $uniq ?>'></script>
<script type='text/javascript'>
var G_reloadFlg = true;
function parentReload()
{
    if (!window.opener.parent.AssemblyCalendar) return; //でもIEならOK NN7.1ではNG対応でonUnloadにifを追加(try catchでもOK)
    window.opener.parent.AssemblyCalendar.AjaxLoadUrl
    ("<?php echo "{$menu->out_self()}?showMenu=List&year={$request->get('year')}&month={$request->get('month')}&id={$uniq}" ?>");
}
</script>
</head>
<body style='overflow-x:hidden; background-color:#e6e6e6;'
    onLoad='
        setInterval("AssemblyCalendar.winActiveChk()", 30);
        // AssemblyCalendar.set_focus(document.BusinessHourEditForm.bh_note, "noSelect");
        AssemblyCalendar.set_focus(document.CalendarCommentForm.clear, "noSelect");
    '
    onUnload='if (document.all) if (G_reloadFlg) parentReload(); // IEなら'
>
<center>
    <table width='100%' bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr><td> <!----------- ダミー(デザイン用) ------------>
    <table width='100%' class='winbox_field list' bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr class='pt14b'>
            <th class='winbox' colspan='5' align='center' nowrap>
                <?php echo $request->get('year') ?>年<?php echo $request->get('month') ?>月<?php echo $request->get('day')?>日
                稼働日／停止日の切替 及び コメントの編集
            </th>
        </tr>
        <tr>
            <td class='winbox' nowrap>
                稼／停切替
            </td>
            <form name='CalendarBDForm' action='<?php echo $menu->out_self(), "?Action=bdDetailSave&showMenu=TimeEdit&year={$request->get('year')}&month={$request->get('month')}&day={$request->get('day')}&id={$uniq}"?>' method='post'
                onSubmit='G_reloadFlg=false;'
            >
            <td class='winbox' nowrap align='center'>
                <?php if ($result->get('bd_flg') == 't') { ?>
                <input type='submit' name='bd_flg' value='稼働日' style='color:black;'>
                <?php } else { ?>
                <input type='submit' name='bd_flg' value='停止日' style='color:gray;'>
                <?php } ?>
            </td>
            </form>
            <td class='winbox' nowrap>
                コメント
            </td>
            <form name='CalendarCommentForm' action='<?php echo $menu->out_self(), "?Action=bdCommentSave&showMenu=TimeEdit&year={$request->get('year')}&month={$request->get('month')}&day={$request->get('day')}&id={$uniq}"?>' method='post'
                onSubmit='G_reloadFlg = false;'
            >
            <td class='winbox' nowrap>
                <input type='text' name='note' size='40' maxlength='50' value='<?php echo $result->get('bd_note')?>'
                    title='必要があれば稼働日／停止日に関してのコメントを入力します。' style='height:30px;' class='pt14b'
                >
            </td>
            <td class='winbox' nowrap>
                <input type='submit' name='bdSave' value='登録' style='color:blue;'>
                <input type='button' name='clear' value='閉じる' onClick='parentReload(); setTimeout("window.close()", 200);'>
            </td>
            </form>
        </tr>
    </table>
        </td></tr>
    </table> <!----------------- ダミーEnd ------------------>
    
    <br>
    
    <form name='BusinessHourEditForm' action='<?php echo $menu->out_self(), "?Action=TimeSave&showMenu=TimeEdit&year={$request->get('year')}&month={$request->get('month')}&day={$request->get('day')}&id={$uniq}"?>' method='post'
        onSubmit='G_reloadFlg=false; return AssemblyCalendar.checkTimeValue(this.hours.value, this);'
    >
    <table width='100%' bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr><td> <!----------- ダミー(デザイン用) ------------>
    <table width='100%' class='winbox_field list' bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr class='pt14b'>
            <th class='winbox' colspan='6' align='center' nowrap>
                <?php echo $request->get('year') ?>年<?php echo $request->get('month') ?>月<?php echo $request->get('day')?>日
                稼働時間の編集
            </th>
        </tr>
        <tr>
            <td class='winbox' nowrap>
                稼働時間
            </td>
            <td class='winbox' nowrap align='right'>
                開始
                <select name='str_hour' class='pt14b'
                    onChange='AssemblyCalendar.setTimeValue(document.BusinessHourEditForm, document.BusinessHourEditForm.hours);'
                >
                    <?php echo $model->getHourValues($result->get('str_hour'))?>
                </select>
                時
                <select name='str_minute' class='pt14b'
                    onChange='AssemblyCalendar.setTimeValue(document.BusinessHourEditForm, document.BusinessHourEditForm.hours);'
                >
                    <?php echo $model->getMinuteValues($result->get('str_minute'))?>
                </select>
                分
                <input type='hidden' name='old_str_time' value='<?php echo $result->get('str_hour')?>:<?php echo $result->get('str_minute')?>'>
            </td>
            <td class='winbox' nowrap>
                〜
            </td>
            <td class='winbox' nowrap>
                終了
                <select name='end_hour' class='pt14b'
                    onChange='AssemblyCalendar.setTimeValue(document.BusinessHourEditForm, document.BusinessHourEditForm.hours);'
                >
                    <?php echo $model->getHourValues($result->get('end_hour'))?>
                </select>
                時
                <select name='end_minute' class='pt14b'
                    onChange='AssemblyCalendar.setTimeValue(document.BusinessHourEditForm, document.BusinessHourEditForm.hours);'
                >
                    <?php echo $model->getMinuteValues($result->get('end_minute'))?>
                </select>
                分
                <input type='hidden' name='old_end_time' value='<?php echo $result->get('end_hour')?>:<?php echo $result->get('end_minute')?>'>
            </td>
            <td class='winbox' nowrap colspan='2'>
                <input type='text' name='hours' style='text-align:right; border:1px solid #e6e6e6; background-color:#e6e6e6;' value='<?php echo $result->get('hours')?>' size='4' class='pt14b' readonly>
                分
                <input type='hidden' name='old_hours' value='<?php echo $result->get('hours')?>'>
            </td>
        </tr>
        <tr>
            <td class='winbox' nowrap>
                コメント
            </td>
            <td class='winbox' colspan='4' nowrap>
                <input type='text' name='bh_note' size='40' maxlength='50' value='<?php echo $result->get('bh_note')?>'
                    title='必要があれば稼働時間に関してのコメントを入力します。' style='height:30px;' class='pt14b'
                >
                <input type='hidden' name='old_bh_note' value='<?php echo $result->get('bh_note')?>'>
            </td>
            <td class='winbox' nowrap>
                <input type='submit' name='bdSave' value='登録' style='color:blue;'>
                <input type='submit' name='bdDelete' value='削除' style='color:red;' onClick='return confirm("削除すると元には戻せません。\n\n\n宜しいですか？");'>
                <input type='button' name='clear' value='閉じる' onClick='parentReload(); setTimeout("window.close()", 200);'>
            </td>
        </tr>
    </table>
        </td></tr>
    </table> <!----------------- ダミーEnd ------------------>
        <div style='position:relative; top:6px;'>
        </div>
    </form>
    
    <br>
    
    <table width='100%' bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr><td> <!----------- ダミー(デザイン用) ------------>
    <table width='100%' class='winbox_field list' bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr class='pt14b'>
            <th class='winbox' colspan='7' align='center' nowrap>
                <?php echo $request->get('year') ?>年<?php echo $request->get('month') ?>月<?php echo $request->get('day')?>日
                停止時間の編集
            </th>
        </tr>
        <?php $rows = $result->get('array_rows'); $res = $result->get_array(); ?>
        <?php for ($i=0; $i<$rows; $i++) { ?>
        <form name='AbsentTimeEditForm<?php echo $i?>' action='<?php echo $menu->out_self(), "?Action=TimeSave&showMenu=TimeEdit&year={$request->get('year')}&month={$request->get('month')}&day={$request->get('day')}&id={$uniq}"?>' method='post'
            onSubmit='G_reloadFlg=false;  return AssemblyCalendar.checkTimeValue(this.absent_time.value, this);'
        >
            <tr>
                <td class='winbox' nowrap rowspan='2'>
                    <?php echo ($i + 1) ?>
                </td>
                <td class='winbox' nowrap>
                    停止時間
                </td>
                <td class='winbox' nowrap align='right'>
                    開始
                    <select name='str_hour' class='pt14b'
                        onChange='AssemblyCalendar.setTimeValue(document.AbsentTimeEditForm<?php echo $i?>, document.AbsentTimeEditForm<?php echo $i?>.absent_time);'
                    >
                        <?php echo $model->getHourValues($res[$i]['str_hour'])?>
                    </select>
                    時
                    <select name='str_minute' class='pt14b'
                        onChange='AssemblyCalendar.setTimeValue(document.AbsentTimeEditForm<?php echo $i?>, document.AbsentTimeEditForm<?php echo $i?>.absent_time);'
                    >
                        <?php echo $model->getMinuteValues($res[$i]['str_minute'])?>
                    </select>
                    分
                    <input type='hidden' name='old_str_time' value='<?php echo $res[$i]['str_hour']?>:<?php echo $res[$i]['str_minute']?>'>
                </td>
                <td class='winbox' nowrap>
                    〜
                </td>
                <td class='winbox' nowrap>
                    終了
                    <select name='end_hour' class='pt14b'
                        onChange='AssemblyCalendar.setTimeValue(document.AbsentTimeEditForm<?php echo $i?>, document.AbsentTimeEditForm<?php echo $i?>.absent_time);'
                    >
                        <?php echo $model->getHourValues($res[$i]['end_hour'])?>
                    </select>
                    時
                    <select name='end_minute' class='pt14b'
                        onChange='AssemblyCalendar.setTimeValue(document.AbsentTimeEditForm<?php echo $i?>, document.AbsentTimeEditForm<?php echo $i?>.absent_time);'
                    >
                        <?php echo $model->getMinuteValues($res[$i]['end_minute'])?>
                    </select>
                    分
                    <input type='hidden' name='old_end_time' value='<?php echo $res[$i]['end_hour']?>:<?php echo $res[$i]['end_minute']?>'>
                </td>
                <td class='winbox' nowrap>
                    <input type='text' name='absent_time' style='text-align:right; border:1px solid #e6e6e6; background-color:#e6e6e6;' value='<?php echo $res[$i]['absent_time']?>' size='4' class='pt14b' readonly>
                    <input type='hidden' name='old_absent_time' value='<?php echo $res[$i]['absent_time']?>'>
                    分
                </td>
                <td class='winbox' nowrap rowspan='2' align='center'>
                    <input type='submit' name='atSave' value='登録' style='color:blue;'><br>
                    <input type='submit' name='atDelete' value='削除' onClick='return confirm("削除すると元には戻せません。\n\n\n宜しいですか？");'
                        style='color:red; position:relative; top:6px;'
                    >
                </td>
            </tr>
            <tr>
                <td class='winbox' nowrap>
                    コメント
                </td>
                <td class='winbox' colspan='4' nowrap>
                    <input type='text' name='absent_note' size='40' maxlength='50' value='<?php echo $res[$i]['absent_note']?>'
                        title='必要があれば停止時間に関してのコメントを入力します。' style='height:30px;' class='pt14b'
                    >
                    <input type='hidden' name='old_absent_note' value='<?php echo $res[$i]['absent_note']?>'>
                </td>
            </tr>
        </form>
        <?php } ?>
        <form name='AbsentTimeEditForm<?php echo $i?>' action='<?php echo $menu->out_self(), "?Action=TimeSave&showMenu=TimeEdit&year={$request->get('year')}&month={$request->get('month')}&day={$request->get('day')}&id={$uniq}"?>' method='post'
            onSubmit='G_reloadFlg=false;  return AssemblyCalendar.checkTimeValue(this.absent_time.value, this);'
        >
            <tr>
                <td class='winbox' nowrap rowspan='2'>
                    <?php echo ($i + 1) ?>
                </td>
                <td class='winbox' nowrap>
                    停止時間
                </td>
                <td class='winbox' nowrap align='right'>
                    開始
                    <select name='str_hour' class='pt14b'
                        onChange='AssemblyCalendar.setTimeValue(document.AbsentTimeEditForm<?php echo $i?>, document.AbsentTimeEditForm<?php echo $i?>.absent_time);'
                    >
                        <?php echo $model->getHourValues(-1)?>
                    </select>
                    時
                    <select name='str_minute' class='pt14b'
                        onChange='AssemblyCalendar.setTimeValue(document.AbsentTimeEditForm<?php echo $i?>, document.AbsentTimeEditForm<?php echo $i?>.absent_time);'
                    >
                        <?php echo $model->getMinuteValues(-1)?>
                    </select>
                    分
                </td>
                <td class='winbox' nowrap>
                    〜
                </td>
                <td class='winbox' nowrap>
                    終了
                    <select name='end_hour' class='pt14b'
                        onChange='AssemblyCalendar.setTimeValue(document.AbsentTimeEditForm<?php echo $i?>, document.AbsentTimeEditForm<?php echo $i?>.absent_time);'
                    >
                        <?php echo $model->getHourValues(-1)?>
                    </select>
                    時
                    <select name='end_minute' class='pt14b'
                        onChange='AssemblyCalendar.setTimeValue(document.AbsentTimeEditForm<?php echo $i?>, document.AbsentTimeEditForm<?php echo $i?>.absent_time);'
                    >
                        <?php echo $model->getMinuteValues(-1)?>
                    </select>
                    分
                </td>
                <td class='winbox' nowrap>
                    <input type='text' name='absent_time' style='text-align:right; border:1px solid #e6e6e6; background-color:#e6e6e6;' value='' size='4' class='pt14b' readonly>
                    分
                </td>
                <td class='winbox' nowrap rowspan='2' align='center'>
                    <input type='submit' name='atSave' value='追加' style='color:blue;'><br>
                    <input type='button' name='clear' value='閉じる' onClick='parentReload(); setTimeout("window.close()", 200);'
                        style='position:relative; top:6px;'
                    >
                </td>
            </tr>
            <tr>
                <td class='winbox' nowrap>
                    コメント
                </td>
                <td class='winbox' colspan='4' nowrap>
                    <input type='text' name='absent_note' size='40' maxlength='50' value=''
                        title='必要があれば停止時間に関してのコメントを入力します。' style='height:30px;' class='pt14b'
                    >
                </td>
            </tr>
        </form>
    </table>
        </td></tr>
    </table> <!----------------- ダミーEnd ------------------>
    <?php if ($result->get('str_hour') == '') { ?>
    <div>
        <form name='DataInheritanceForm' action='<?php echo $menu->out_self(), "?Action=TimeCopy&showMenu=TimeEdit&year={$request->get('year')}&month={$request->get('month')}&day={$request->get('day')}&id={$uniq}"?>' method='post'
            onSubmit='G_reloadFlg=false;'
        >
            <input type='submit' name='DataCopy' value='直近のデータコピー' title='過去から一番直近のデータをコピーします。'
                style='position:relative; top:6px;'
            >
        </form>
    </div>
    <?php } ?>
</center>
</body>
<?php echo $menu->out_alert_java(false)?>
</html>
