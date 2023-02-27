<?php
//////////////////////////////////////////////////////////////////////////////
// 全社共有 会議(打合せ)スケジュール表の会議室のメンテナンス                //
//                                          MVC View 部  社用車リスト表示   //
// Copyright (C) 2019-2019 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2019/01/23 Created   meeting_schedule_ViewCar.php                       //
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
<link rel='stylesheet' href='calendar.css?<?php echo  $uniq ?>' type='text/css' media='screen'>
<link rel='stylesheet' href='meeting_schedule.css?<?php echo  $uniq ?>' type='text/css' media='screen'>
<script type='text/javascript' src='meeting_schedule.js?=<?php echo $uniq ?>'></script>
</head>
<body onLoad='MeetingSchedule.set_focus(document.car_form.<?php echo $focus?>, "noSelect")'>
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
    
    <form name='ControlForm' action='<?php echo $menu->out_self(), '?', $model->get_htmlGETparm(), "&id={$uniq}"?>' method='get'>
    <table bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr><td> <!----------- ダミー(デザイン用) ------------>
    <table class='winbox_field' bgcolor='e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr>
            <td nowrap <?php if($showMenu=='Apend') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return MeetingSchedule.ControlFormSubmit(document.ControlForm.elements["Apend"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='Apend' id='Apend'
                <?php if($showMenu=='Apend') echo 'checked' ?>>
                <label for='Apend'>会議(打合せ)入力</label>
            </td>
            <td nowrap <?php if($showMenu=='List') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return MeetingSchedule.ControlFormSubmit(document.ControlForm.elements["List"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='List' id='List'
                <?php if($showMenu=='List') echo 'checked' ?>>
                <label for='List'>会議(打合せ)一覧</label>
            </td>
            <td nowrap <?php if($showMenu=='MyList') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='
                    <?php if ($_SESSION['User_ID'] == '000000') { ?>
                    alert("現在共有モードで開いていますのでマイリストは表示できません！\n\n個人モードで開けば使用できます。"); return false;
                    <?php } else { ?>
                    return MeetingSchedule.ControlFormSubmit(document.ControlForm.elements["MyList"], document.ControlForm);
                    <?php } ?>
                '
            >
                <input type='radio' name='showMenu' value='MyList' id='MyList'
                <?php if($showMenu=='MyList') echo 'checked' ?>>
                <label for='MyList'>マイリスト</label>
            </td>
            <?php
            if (getCheckAuthority(68)) {
            ?>
            <td nowrap <?php if($showMenu=='Absence') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return MeetingSchedule.ControlFormSubmit(document.ControlForm.elements["Absence"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='Absence' id='Absence'
                <?php if($showMenu=='Absence') echo 'checked' ?>>
                <label for='Absence'>不在予定</label>
            </td>
            <?php
            }
            ?>
            <?php
            if (getCheckAuthority(53)) {
            ?>
            <td nowrap <?php if($showMenu=='Holyday') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return MeetingSchedule.ControlFormSubmit(document.ControlForm.elements["Holyday"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='Holyday' id='Holyday'
                <?php if($showMenu=='Holyday') echo 'checked' ?>>
                <label for='Holyday'>計画有給</label>
            </td>
            <?php
            }
            ?>
            <td nowrap <?php if($showMenu=='Group') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return MeetingSchedule.ControlFormSubmit(document.ControlForm.elements["Group"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='Group' id='Group'
                <?php if($showMenu=='Group') echo 'checked' ?>>
                <label for='Group'>グループの編集</label>
            </td>
            <td nowrap <?php if($showMenu=='Room') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return MeetingSchedule.ControlFormSubmit(document.ControlForm.elements["Room"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='Room' id='Room'
                <?php if($showMenu=='Room') echo 'checked' ?>>
                <label for='Room'>会議室の編集</label>
            </td>
            <td nowrap <?php if($showMenu=='Car') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return MeetingSchedule.ControlFormSubmit(document.ControlForm.elements["Car"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='Car' id='Car'
                <?php if($showMenu=='Car') echo 'checked' ?>>
                <label for='Car'>社用車の編集</label>
            </td>
            <input type='hidden' name='year'  value='<?php echo $year?>'>
            <input type='hidden' name='month' value='<?php echo $month?>'>
            <input type='hidden' name='day'   value='<?php echo $day?>'>
            <td nowrap class='winbox' onClick='return MeetingSchedule.addFavoriteIcon("http://<?php echo $_SERVER['SERVER_ADDR'],$menu->out_self()?>", "<?php echo $_SESSION['User_ID']?>");' id='favi'>
                <label for='favi'>アイコン追加</label>
            </td>
        </tr>
    </table>
        </td></tr>
    </table> <!----------------- ダミーEnd ------------------>
    </form>
    
    <table class='list' bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <caption>社用車のメンテナンス</caption>
        <tr><td> <!-- ダミー -->
    <table class='winbox_field' bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <form name='car_form' action='<?php echo $menu->out_self(), '?', $model->get_htmlGETparm(), "&id={$uniq}"?>'
            method='post' onSubmit='return MeetingSchedule.car_formCheck(this)'
        >
        <tr>
            <td class='winbox' nowrap>
                社用車の番号
            </td>
            <td class='winbox' nowrap>
                <input type='text' name='car_no' value='<?php echo $car_no?>' size='6' maxlength='5'
                    style='ime-mode:disabled;'
                    onChange='this.value=this.value.toUpperCase()'
                <?php echo $readonly?>
                >
            </td>
            <td class='winbox' nowrap>
                社 用 車 名
            </td>
            <td class='winbox' nowrap>
                <input type='text' name='car_name' value='<?php echo $car_name?>' size='16' maxlength='16' class='pt12b'>
            </td>
            <td class='winbox' nowrap>
                重複チェック
            </td>
            <td class='winbox' nowrap>
                <input type='radio' name='car_dup' value='t' id='yes'<?php if($car_dup=='する')echo' checked' ?>><label for='yes'>Yes</label>
                <input type='radio' name='car_dup' value='f' id='no'<?php if($car_dup=='しない')echo' checked' ?>><label for='no'>No</label>
            </td>
            <td class='winbox' nowrap>
                <input type='submit' name='carEdit' value='登録' class='pt12b'>
            </td>
            <input type='hidden' name='showMenu' value='Car'>
            <input type='hidden' name='year'  value='<?php echo $year?>'>
            <input type='hidden' name='month' value='<?php echo $month?>'>
            <input type='hidden' name='day'   value='<?php echo $day?>'>
        </tr>
        </form>
    </table>
        </td></tr> <!-- ダミー -->
    </table>
    
    <table class='list' bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <caption>
            <table border='0' width='100%'>
                <tr>
                    <td align='right' nowrap width='60%'>
                        社用車の一覧 &nbsp;&nbsp;
                    </td>
                    <td align='center' nowrap width='40%'>
                        <form name='pageForm' action='<?php echo $menu->out_self(), "?id={$uniq}"?>' method='post'>
                        <?php echo $pageControl?>
                        <input type='hidden' name='showMenu' value='Car'>
                        <input type='hidden' name='year'  value='<?php echo $year?>'>
                        <input type='hidden' name='month' value='<?php echo $month?>'>
                        <input type='hidden' name='day'   value='<?php echo $day?>'>
                        </form>
                    </td>
                </tr>
            </table>
        </caption>
        <tr><td> <!-- ダミー -->
    <table class='winbox_field' bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <th class='winbox' width='30'>&nbsp;</th>
        <th class='winbox' width='40'>&nbsp;</th>
        <th class='winbox' width='40'>&nbsp;</th>
        <th class='winbox' nowrap>社用車番号</th>
        <th class='winbox' nowrap>社 用 車 名</th>
        <th class='winbox' nowrap>重複チェック</th>
        <th class='winbox' nowrap>有効／無効</th>
        <th class='winbox' nowrap>更新日</th>
    <?php if ($rows >= 1) { ?>
        <?php for ($r=0; $r<$rows; $r++) { ?>
            <?php if ($res[$r][3] == '有効') { ?>
            <tr>
            <?php } else { ?>
            <tr style='color:gray;'>
            <?php } ?>
            <td class='winbox' align='right' nowrap><?php echo $r + 1 + $model->get_offset()?></td>
            <td class='winbox' align='center' nowrap>
                <a href='<?php echo $menu->out_self(), "?car_no={$res[$r][0]}&showMenu=Car&carOmit=go&car_name=", urlencode($res[$r][1]), '&car_dup=', urlencode($res[$r][2]), "&year={$year}&month={$month}&day={$day}&", $model->get_htmlGETparm(), "&id={$uniq}"?>'
                    style='text-decoration:none;'
                    onClick='return confirm("過去に使われていなければ削除しても問題ありませんが\n\nある場合は削除せず無効にして下さい。\n\n削除します宜しいですか？")'
                >
                    削除
                </a>
            </td>
            <td class='winbox' align='center' nowrap>
                <a href='<?php echo $menu->out_self(), "?car_no={$res[$r][0]}&showMenu=Car&carCopy=go&car_name=", urlencode($res[$r][1]), '&car_dup=', urlencode($res[$r][2]), "&year={$year}&month={$month}&day={$day}&", $model->get_htmlGETparm(), "&id={$uniq}"?>'
                    style='text-decoration:none;'>編集
                </a>
            </td>
            <!-- 社用車番号 -->
            <td class='winbox' align='right'><?php echo $res[$r][0]?></td>
            <!-- 社用車名 -->
            <td class='winbox' align='left'><?php echo $res[$r][1]?></td>
            <!-- 重複チェック -->
            <td class='winbox' align='center'><?php echo $res[$r][2]?></td>
            <!-- 有効／無効 -->
            <td class='winbox' align='center' nowrap>
                <a href='<?php echo $menu->out_self(), "?car_no={$res[$r][0]}&showMenu=Car&carActive=go&car_name=", urlencode($res[$r][1]), '&car_dup=', urlencode($res[$r][2]), "&year={$year}&month={$month}&day={$day}&", $model->get_htmlGETparm(), "&id={$uniq}"?>'
                    style='text-decoration:none;'>
                <?php echo $res[$r][3]?>
            </td>
            <!-- 更新日 (表示は更新日だが中身は変更日) -->
            <td class='winbox' align='center' onDblclick='alert("初回 登録日は\n\n[ <?php echo $res[$r][4]?> ]\n\nです。");'>
                <?php echo $res[$r][5]?>
            </td>
            </tr>
        <?php } ?>
    <?php } else { ?>
        <tr><td class='winbox' align='center' colspan='8'>
            登録がありません。
        </td></tr>
    <?php } ?>
    </table>
        </td></tr> <!-- ダミー -->
    </table>
</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
