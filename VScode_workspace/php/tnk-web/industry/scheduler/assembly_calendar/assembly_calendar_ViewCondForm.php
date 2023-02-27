<?php
//////////////////////////////////////////////////////////////////////////////
// 組立ラインのカレンダー メンテナンス                          MVC View 部 //
//   使用DB = company_holiday, company_business_hours, company_absent_time  //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/06/20 Created   assembly_calendar_ViewCondForm.php                  //
// 2006/09/29 CSSクラス menuButton, pageButton をメニューボタンに追加       //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
<meta http-equiv='Content-Style-Type' content='text/css'>
<meta http-equiv='Content-Script-Type' content='text/javascript'>
<!-- <meta http-equiv='Refresh' content='15;URL=<?php echo $menu->out_self(), "?showMenu={$request->get('showMenu')}"?>'> -->
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<link rel='stylesheet' href='calendar.css?<?php echo $uniq ?>' type='text/css' media='screen'>
<link rel='stylesheet' href='assembly_calendar.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<link rel='shortcut icon' href='/favicon.ico?=<?php echo $uniq ?>'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='assembly_calendar.js?<?php echo $uniq ?>'></script>
</head>
<body style='overflow-x:hidden;'
    onLoad='
        autoLoadScript();
        AssemblyCalendar.checkANDexecute(document.ConditionForm, 1);
        //AssemblyCalendar.set_focus(document.ConditionForm.targetDateY, "noSelect");
    '
>
<center>
<?php echo $menu->out_title_border() ?>
    
    <form name='ConditionForm' action='<?php echo $menu->out_self() ?>' method='post'
        onSubmit='return AssemblyCalendar.checkANDexecute(this, 1)'
    >
        <!----------------- ここは 本文を表示する ------------------->
        <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='1'>
            <tr>
                <td class='winbox' align='center'>
                    <select name='targetLine' class='pt14b' onChange='AssemblyCalendar.checkANDexecute(document.ConditionForm, 1)'>
                        <option value='0000'<?php if ($request->get('targetLine') == '0000') echo ' selected'?>>共通</option>
                        <option value='2MH1'<?php if ($request->get('targetLine') == '2MH1') echo ' selected'?>>2MH1</option>
                        <option value='2OH1'<?php if ($request->get('targetLine') == '2OH1') echo ' selected'?>>2OH1</option>
                        <option value='2OC1'<?php if ($request->get('targetLine') == '2OC1') echo ' selected'?>>2OC1</option>
                        <option value='2GA1'<?php if ($request->get('targetLine') == '2GA1') echo ' selected'?>>2GA1</option>
                        <option value='2GA2'<?php if ($request->get('targetLine') == '2GA2') echo ' selected'?>>2GA2</option>
                        <option value='2HA1'<?php if ($request->get('targetLine') == '2HA1') echo ' selected'?>>2HA1</option>
                        <option value='2HA3'<?php if ($request->get('targetLine') == '2HA3') echo ' selected'?>>2HA3</option>
                        <option value='2HY1'<?php if ($request->get('targetLine') == '2HY1') echo ' selected'?>>2HY1</option>
                        <option value='2MA1'<?php if ($request->get('targetLine') == '2MA1') echo ' selected'?>>2MA1</option>
                        <option value='2MA2'<?php if ($request->get('targetLine') == '2MA2') echo ' selected'?>>2MA2</option>
                        <option value='2MP1'<?php if ($request->get('targetLine') == '2MP1') echo ' selected'?>>2MP1</option>
                        <option value='2MP2'<?php if ($request->get('targetLine') == '2MP2') echo ' selected'?>>2MP2</option>
                        <option value='2PK1'<?php if ($request->get('targetLine') == '2PK1') echo ' selected'?>>2PK1</option>
                        <option value='22A2'<?php if ($request->get('targetLine') == '22A2') echo ' selected'?>>22A2</option>
                        <option value='4AP1'<?php if ($request->get('targetLine') == '4AP1') echo ' selected'?>>4AP1</option>
                        <option value='4AS1'<?php if ($request->get('targetLine') == '4AS1') echo ' selected'?>>4AS1</option>
                        <option value='4AT1'<?php if ($request->get('targetLine') == '4AT1') echo ' selected'?>>4AT1</option>
                        <option value='3LC2'<?php if ($request->get('targetLine') == '3LC2') echo ' selected'?>>3LC2</option>
                        <option value='3LC3'<?php if ($request->get('targetLine') == '3LC3') echo ' selected'?>>3LC3</option>
                        <option value='3LH1'<?php if ($request->get('targetLine') == '3LH1') echo ' selected'?>>3LH1</option>
                        <option value='3LH2'<?php if ($request->get('targetLine') == '3LH2') echo ' selected'?>>3LH2</option>
                        <option value='3LO1'<?php if ($request->get('targetLine') == '3LO1') echo ' selected'?>>3LO1</option>
                    </select>
                </td>
                <td class='winbox' align='center' nowrap>
                    <input type='button' class='menuButton' name='BDSwitch' value='稼/停切替'<?php if($request->get('targetCalendar')=='BDSwitch') echo ' style="color:blue;"'?>
                        onClick='AssemblyCalendar.setTargetCalendar(document.ConditionForm, 1, "BDSwitch")'
                    >
                    <input type='button' class='menuButton' name='Comment' value='コメント編集'<?php if($request->get('targetCalendar')=='Comment') echo ' style="color:blue;"'?>
                        onClick='AssemblyCalendar.setTargetCalendar(document.ConditionForm, 1, "Comment")'
                    >
                    <input type='button' class='menuButton' name='SetTime' value='詳細編集へ'<?php if($request->get('targetCalendar')=='SetTime') echo ' style="color:blue;"'?>
                        onClick='AssemblyCalendar.setTargetCalendar(document.ConditionForm, 1, "SetTime")'
                    >
                    <input type='hidden' name='targetCalendar' value='<?php echo $request->get('targetCalendar')?>'>
                </td>
                <td class='winbox' align='center'>
                    <input type='button' class='pageButton' name='previoustYear' value='←前期へ' title='クリックすれば、前年度のカレンダーを表示します。'
                        onClick='AssemblyCalendar.dateCreate(document.ConditionForm, 1, -1);'
                    >
                </td>
                    <!--  bgcolor='#ffffc6' 薄い黄色 --> 
                <td align='center' class='winbox caption_color pt12b' nowrap>
                    <span id='blink_item'>対象期</span>
                </td>
                <td class='winbox' align='center'>
                    <select name='targetDateY' class='pt14b' onChange='document.ConditionForm.SetTime.value="詳細編集へ"; AssemblyCalendar.checkANDexecute(document.ConditionForm, 1)'>
                        <!-- <option value='2006' selected>第０７期</option> -->
                        <!-- <option value='' selected>期選択</option> -->
                        <?php echo $model->getTargetDateYvalues($request) ?>
                    </select>
                    <input type='hidden' name='targetDateStr' value=''>
                    <input type='hidden' name='targetDateEnd' value=''>
                </td>
                <td class='winbox' align='center'>
                    <input type='button' class='pageButton' name='nextYear' value='次期へ→' title='クリックすれば、次年度のカレンダーを表示します。'
                        onClick='AssemblyCalendar.dateCreate(document.ConditionForm, 1, +1);'
                    >
                </td>
                <td class='winbox' align='center'>
                    <input type='button' class='pageButton' name='Format' value='初期化' onClick='AssemblyCalendar.initFormat(document.ConditionForm, 1);'
                        title='クリックすれば、カレンダーの稼働日・停止日・時間を初期化します。' style='color:red;'
                    >
                    <input type='hidden' name='targetFormat' value=''>
                </td>
            </tr>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
    </form>
    <div id='showAjax'>
    </div>
</center>
</body>
<?php echo $menu->out_alert_java(false)?>
<script type='text/javascript'>
function autoLoadScript()
{
    <?php echo $result->get('autoLoadScript')?>
}
</script>
</html>
