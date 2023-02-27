<?php
//////////////////////////////////////////////////////////////////////////////
// 全社共有 打合せ(会議)不在予定の照会・メンテナンス                        //
//                                          MVC View 部     リスト表示      //
// Copyright (C) 2015-2021 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2015/06/19 Created   meeting_schedule_ViewHolyday.php                    //
// 2021/06/09 今日へ戻るリンクを追加                                   大谷 //
// 2021/06/10 カレンダーの年月移動を追加。過去2年未来1年               大谷 //
//////////////////////////////////////////////////////////////////////////////
///// カレンダー移動用の年月が設定されているかチェック
if ($request->get('ind_ym') == '') {
    // 初期値(本日)を設定
    $ind_ym = date('Ym');
} else {
    $ind_ym = $request->get('ind_ym');
}

// 当日へのリンクを生成
$day_today  = getdate();

if ($showMenu != 'Edit') {
    $url = $menu->out_self() . "?showMenu={$showMenu}&" . $model->get_htmlGETparm() . "&id={$uniq}";
} else {
    $url = $menu->out_self() . "?showMenu=List&" . $model->get_htmlGETparm() . "&id={$uniq}";
}
if (preg_match('/\?/', $url)) {
    $url_para = $url . "&year={$day_today['year']}&month={$day_today['mon']}&day={$day_today['mday']}&ind_ym=99";
} else {
    $url_para = $url . "?year={$day_today['year']}&month={$day_today['mon']}&day={$day_today['mday']}&ind_ym=99";
}
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
<link rel='stylesheet' href='meeting_schedule.css?<?php echo $uniq ?>' type='text/css' media='screen'>
<script type='text/javascript' src='meeting_schedule.js?=<?php echo $uniq ?>'></script>
<link rel='shortcut icon' href='/favicon.ico?=<?php echo $uniq ?>'>
</head>
<body onLoad='set_focus()'>
<center>
<?php echo $menu->out_title_only_border() ?>
    
    <table border='0' align='center'>
        <tr>
        <a href='<?php echo $url_para ?>' style='text-decoration:none;' class='current'><font size='2'><B>今日</B></font></a>
        <select name='ind_ym' class='pt11b' onChange='location.href=value;'>
            <?php
            $ym     = date("Ym");
            $ym     = $ym + 100;
            $ym_cnt = 0;
            while(1) {
                $ym_year = substr($ym,0,4);
                $ym_mon  = substr($ym,4,2);
                $url_ind    = $url . "&year={$ym_year}&month={$ym_mon}&day={$day_now['mday']}&ind_ym={$ym}";
                if ($ind_ym == $ym) {
                    printf("<option value='%s' selected>%s年%s月</option>\n",$url_ind,substr($ym,0,4),substr($ym,4,2));
                    $init_flg = 0;
                } else
                    printf("<option value='%s'>%s年%s月</option>\n",$url_ind,substr($ym,0,4),substr($ym,4,2));
                if ($ym_cnt >= 36)
                    break;
                if (substr($ym,4,2)!=01) {
                    $ym--;
                } else {
                    $ym = $ym - 100;
                    $ym = $ym + 11;
                }
                $ym_cnt++;
            }
            ?>
        </select>
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
            <td nowrap <?php if($showMenu=='Holyday') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return MeetingSchedule.ControlFormSubmit(document.ControlForm.elements["Holyday"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='Holyday' id='Holyday'
                <?php if($showMenu=='Holyday') echo 'checked' ?>>
                <label for='Holyday'>計画有給</label>
            </td>
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
            <?php
            if ($_SESSION['User_ID'] == '300144') {
            ?>
            <td nowrap <?php if($showMenu=='Car') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return MeetingSchedule.ControlFormSubmit(document.ControlForm.elements["Car"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='Car' id='Car'
                <?php if($showMenu=='Car') echo 'checked' ?>>
                <label for='Car'>社用車の編集</label>
            </td>
            <?php
            }
            ?>
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
    
    <div class='caption_font'></div>
    
    <table class='list' bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <caption>
            <table border='0' width='100%'>
                <tr>
                    <td align='center' nowrap width='40%'>
                        <?php printf("%s年%s月%s日\n",$day_now['year'],$day_now['mon'],$day_now['mday']); ?>総合届のデータ
                    </td>
                </tr>
            </table>
        </caption>
        <tr><td> <!-- ダミー -->
    <table class='winbox_field' bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3' width='100%'>
        <th class='winbox' nowrap>No.</th>
        <th class='winbox' nowrap>承認状況</th>
        <th class='winbox' nowrap>所　属</th>
        <th class='winbox' nowrap>氏　名</th>
        <th class='winbox' nowrap>不在理由</th>
        <th class='winbox' nowrap>開始時間</th>
        <th class='winbox' nowrap>終了時間</th>
    <?php if ($rows >= 1) { ?>
        <?php for ($r=0; $r<$rows; $r++) { ?>
            <tr>
            <!-- No. -->
            <td class='winbox' align='right' nowrap><?php echo $r + 1 + $model->get_offset()?></td>
            <!-- 承認状況 -->
            <?php
            $disp = '完了';
            $disp_stile = 'background-color:SkyBlue; color:White;';
            if( trim($res[$r][0]) != 'END' ) {
                $disp = '途中';
                $disp_stile = 'background-color: Yellow ; color:Blue;';
            }
            ?>
            <td class='winbox' align='center' style='<?php echo $disp_stile; ?>'><?php echo $disp; ?></td>
            <!-- 所　属 -->
            <td class='winbox' align='left' nowrap><?php echo $res[$r][1]?></td>
            <!-- 氏　名 -->
            <td class='winbox' align='left' nowrap><?php echo $res[$r][2]?></td>
            <!-- 不在理由 -->
            <td class='winbox' align='left' nowr><?php echo $res[$r][3]?></td>
            <!-- 開始時間 -->
            <td class='winbox' align='center'><?php if( $res[$r][4] == '' ) echo "--:--"; else echo $res[$r][4]; ?></td>
            <!-- 終了時間 -->
            <td class='winbox' align='center'><?php if( $res[$r][5] == '' ) echo "--:--"; else echo $res[$r][5]; ?></td>
            </tr>
        <?php } ?>
    <?php } else { ?>
        <tr><td class='winbox' align='center' colspan='9'>
            <?php echo $noDataMessage, "\n"?>
        </td></tr>
    <?php } ?>
    </table>
        </td></tr> <!-- ダミー -->
    </table>
    </form>
</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
