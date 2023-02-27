<?php
//////////////////////////////////////////////////////////////////////////////
// 会社の基本カレンダー メンテナンス                            MVC View 部 //
//   使用DB = company_holiday, company_business_hours, company_absent_time  //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/06/20 Created   companyCalendar_ViewCondForm.php                    //
// 2006/07/11 ControllerにExecute()メソッドを追加しActionとshowMenuの明確化 //
// 2006/10/04 CSSクラス menuButton, pageButton をメニューボタンに追加       //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE html>
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
<link rel='stylesheet' href='companyCalendar.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<link rel='shortcut icon' href='/favicon.ico?=<?php echo $uniq ?>'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='companyCalendar.js?<?php echo $uniq ?>'></script>
</head>
<body style='overflow-x:hidden;'
    onLoad='
        autoLoadScript();
        CompanyCalendar.checkANDexecute(document.ConditionForm, 1);
        //CompanyCalendar.set_focus(document.ConditionForm.targetDateY, "noSelect");
    '
>
<center>
<?php echo $menu->out_title_border() ?>
    
    <form name='ConditionForm' action='<?php echo $menu->out_self() ?>' method='post'
        onSubmit='return CompanyCalendar.checkANDexecute(this, 1)'
    >
        <!----------------- ここは 本文を表示する ------------------->
        <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='1'>
            <tr>
                <td class='winbox' align='center' nowrap>
                    <input type='button' class='menuButton' name='BDSwitch' value='営/休切替'<?php if($request->get('targetCalendar')=='BDSwitch') echo ' style="color:blue;"'?>
                        onClick='CompanyCalendar.setTargetCalendar(document.ConditionForm, 1, "BDSwitch")'
                    >
                    <input type='button' class='menuButton' name='Comment' value='コメント編集'<?php if($request->get('targetCalendar')=='Comment') echo ' style="color:blue;"'?>
                        onClick='CompanyCalendar.setTargetCalendar(document.ConditionForm, 1, "Comment")'
                    >
                    <input type='button' class='menuButton' name='SetTime' value='詳細編集へ'<?php if($request->get('targetCalendar')=='SetTime') echo ' style="color:blue;"'?>
                        onClick='CompanyCalendar.setTargetCalendar(document.ConditionForm, 1, "SetTime")'
                    >
                    <input type='hidden' name='targetCalendar' value='<?php echo $request->get('targetCalendar')?>'>
                </td>
                <td class='winbox' align='center'>
                    <input type='button' class='pageButton' name='previoustYear' value='←前期へ' title='クリックすれば、前年度のカレンダーを表示します。'
                        onClick='CompanyCalendar.dateCreate(document.ConditionForm, 1, -1);'
                    >
                </td>
                    <!--  bgcolor='#ffffc6' 薄い黄色 --> 
                <td align='center' class='winbox caption_color pt12b' nowrap>
                    <span id='blink_item'>対象期</span>
                </td>
                <td class='winbox' align='center'>
                    <select name='targetDateY' class='pt14b' onChange='document.ConditionForm.SetTime.value="詳細編集へ"; CompanyCalendar.checkANDexecute(document.ConditionForm, 1)'>
                        <!-- <option value='2006' selected>第０７期</option> -->
                        <!-- <option value='' selected>期選択</option> -->
                        <?php echo $model->getTargetDateYvalues($request) ?>
                    </select>
                    <input type='hidden' name='targetDateStr' value=''>
                    <input type='hidden' name='targetDateEnd' value=''>
                </td>
                <td class='winbox' align='center'>
                    <input type='button' class='pageButton' name='nextYear' value='次期へ→' title='クリックすれば、次年度のカレンダーを表示します。'
                        onClick='CompanyCalendar.dateCreate(document.ConditionForm, 1, +1);'
                    >
                </td>
                <td class='winbox' align='center'>
                    <input type='button' class='pageButton' name='Format' value='初期化' onClick='CompanyCalendar.initFormat(document.ConditionForm, 1);'
                        title='クリックすれば、カレンダーの営業日・休日を初期化します。' style='color:red;'
                    >
                    <input type='hidden' name='targetFormat' value=''>
                </td>
            </tr>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
    </form>
    <div id='showAjax'>
        <?php //echo $model->showCalendar($request, $calendar, $menu) ?>
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
