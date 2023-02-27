<?php
//////////////////////////////////////////////////////////////////////////////
// 部課長用会議スケジュール照会  ガントチャート   MVC View 部               //
// Copyright (C) 2010 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2010/03/11 Created   meeting_schedule_manager_ViewGanttChart.php         //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<?php echo $menu->out_jsBaseClass() ?>
<link rel='stylesheet' href='calendar.css?<?php echo $uniq ?>' type='text/css' media='screen'>
<link rel='stylesheet' href='meeting_schedule_manager.css?id=<?= $uniq ?>' type='text/css' media='screen'>
<script type='text/javascript' src='meeting_schedule_manager.js?<?= $uniq ?>'></script>
<link rel='shortcut icon' href='/favicon.ico?=<?php echo $uniq ?>'>
</head>
<body
    onLoad='
        MeetingScheduleManager.AjaxLoadTableMsg("GanttTable", "<?php if($request->get('page_keep') != '')echo'page_keep';?>");
        MeetingScheduleManager.set_focus(document.ControlForm.Gantt, "");
    '
>
<center>
<?php echo $menu->out_title_only_border() ?>
    <table border='0' align='center'>
        <tr>
        <td valign='top'>
            <?php echo $calendar_pre->show_calendar($day_pre['year'], $day_pre['mon']);?>
        </td>
        <td valign='top'>
            <?php echo $calendar_now->show_calendar($day_now['year'], $day_now['mon'], $day_now['mday']);?>
        </td>
        <td valign='top'>
            <?php echo $calendar_nex1->show_calendar($day_nex1['year'], $day_nex1['mon']);?>
        </td>
        <td valign='top'>
            <?php echo $calendar_nex2->show_calendar($day_nex2['year'], $day_nex2['mon']);?>
        </td>
        </tr>
    </table>
    <form name='ControlForm' action='<?php echo $menu->out_self(), '?', $model->get_htmlGETparm(), "&id={$uniq}&year={$year}&month={$month}&day={$day}"?>' method='get'>
    <table bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr><td> <!----------- ダミー(デザイン用) ------------>
    <table class='winbox_field' bgcolor='e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr>
            <!--
            <td nowrap <?php if($showMenu=='Apend') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return MeetingScheduleManager.ControlFormSubmit(document.ControlForm.elements["Apend"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='Apend' id='Apend'
                <?php if($showMenu=='Apend') echo 'checked' ?>>
                <label for='Apend'>会議(打合せ)入力</label>
            </td>
            -->
            <td nowrap <?php if($showMenu=='GanttChart') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return MeetingScheduleManager.ControlFormSubmit(document.ControlForm.elements["GanttChart"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='GanttChart' id='GanttChart'
                <?php if($showMenu=='GanttChart') echo 'checked' ?>>
                <label for='GanttChart'>会議(打合せ)一覧</label>
            </td>
            <?php if (getCheckAuthority(33) || $_SESSION['User_ID'] == '300144' || $_SESSION['User_ID'] == '014737') { ?>
            <td nowrap <?php if($showMenu=='MyList') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='
                    <?php if ($_SESSION['User_ID'] == '000000') { ?>
                    alert("現在共有モードで開いていますのでマイリストは表示できません！\n\n個人モードで開けば使用できます。"); return false;
                    <?php } else { ?>
                    return MeetingScheduleManager.ControlFormSubmit(document.ControlForm.elements["MyList"], document.ControlForm);
                    <?php } ?>
                '
            >
                <input type='radio' name='showMenu' value='MyList' id='MyList'
                <?php if($showMenu=='MyList') echo 'checked' ?>>
                <label for='MyList'>マイリスト</label>
            </td>
            <?php } ?>
            <!--
            <td nowrap <?php if($showMenu=='Group') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return MeetingScheduleManager.ControlFormSubmit(document.ControlForm.elements["Group"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='Group' id='Group'
                <?php if($showMenu=='Group') echo 'checked' ?>>
                <label for='Group'>グループの編集</label>
            </td>
            <td nowrap <?php if($showMenu=='Room') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return MeetingScheduleManager.ControlFormSubmit(document.ControlForm.elements["Room"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='Room' id='Room'
                <?php if($showMenu=='Room') echo 'checked' ?>>
                <label for='Room'>会議室の編集</label>
            </td>
            -->
            <input type='hidden' name='year'  value='<?php echo $year?>'>
            <input type='hidden' name='month' value='<?php echo $month?>'>
            <input type='hidden' name='day'   value='<?php echo $day?>'>
            <input type='hidden' name='my_flg'   value="<?php echo $request->get('my_flg')?>">
            <td nowrap class='winbox' onClick='return MeetingScheduleManager.addFavoriteIcon("http://<?php echo $_SERVER['SERVER_ADDR'],$menu->out_self()?>", "<?php echo $_SESSION['User_ID']?>");' id='favi'>
                <label for='favi'>アイコン追加</label>
            </td>
        </tr>
    </table>
        </td></tr>
    </table> <!----------------- ダミーEnd ------------------>
    
    <div class='caption_font'></div>
    
    <table class='list' bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <caption>
            <table border='0' width='100%'>
                <tr>
                    <td align='right' nowrap width='80%'>
                        <?php echo $menu->out_caption()?>
                        <input onClick='submit()' type='radio' name='listSpan' value='0' id='0日'<?php if($listSpan==0)echo' checked'?>>
                            <label for='0日'<?php if($listSpan==0)echo" style='color:red;'"?>>１日間</label>
                        <input onClick='submit()' type='radio' name='listSpan' value='7' id='7日'<?php if($listSpan==7)echo' checked'?>>
                            <label for='7日'<?php if($listSpan==7)echo" style='color:red;'"?>>７日間</label>
                        <input onClick='submit()' type='radio' name='listSpan' value='14' id='14日'<?php if($listSpan==14)echo' checked'?>>
                            <label for='14日'<?php if($listSpan==14)echo" style='color:red;'"?>>14日間</label>
                        <input onClick='submit()' type='radio' name='listSpan' value='28' id='28日'<?php if($listSpan==28)echo' checked'?>>
                            <label for='28日'<?php if($listSpan==28)echo" style='color:red;'"?>>28日間</label>
                        &nbsp;&nbsp;
                    </td>
                    <td>
                        <input type='button' name='zoomButton' id='zoomButton' value='ズームで開く' onClick='MeetingScheduleManager.zoomGantt("<?php echo $menu->out_self(), "?{$pageParameter}"?>")'>
                    </td>
                    <td style='visibility: hidden;'>
                        <?php echo $pageControl?>
                    </td>
                </tr>
            </table>
        </caption>
    </table>
    </form>
    <span id='showAjax'>
    </span>
</center>
</body>
</html>
