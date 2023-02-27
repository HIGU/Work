<?php
//////////////////////////////////////////////////////////////////////////////
// 全社共有 打合せ(会議)スケジュール表の照会・メンテナンス                  //
//                                                  MVC View 部   追加      //
// Copyright (C) 2005-2021 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/11/02 Created   meeting_schedule_ViewApend.php                      //
// 2005/11/21 出席者のグループ指定の追加                                    //
// 2005/11/22 Edit(編集)時に対象日付にすり替える(期間指定一覧に対応)        //
//            メール再送信と上書(登録)をセルで区切り罫線を付け分類した      //
// 2005/11/29 参加者へのメール案内 → 出席者へのメール案内 へ名称変更       //
// 2006/05/09 自分のスケジュールのみ表示(マイリスト)機能を追加              //
// 2006/06/19 ApendとEdit兼用のため hiddenのshowMenu value='変数'>へ変更    //
// 2006/06/20 カレンダー部の</tr>が抜けているのを修正                       //
// 2009/12/17 照会・印刷画面用のテスト                                 大谷 //
// 2011/07/04 サマータイム対応により開始時間を5時からに変更            大谷 //
// 2015/06/19 計画有給の照会を追加                                     大谷 //
// 2015/06/25 計画有給の照会を共通権限に変更（53）                     大谷 //
// 2018/06/18 時間を0～23に変更                                        大谷 //
// 2019/03/15 冷温水機稼働状況、社用車、不在者のメニューを追加         大谷 //
// 2021/06/09 今日へ戻るリンクを追加                                   大谷 //
// 2021/06/10 カレンダーの年月移動を追加。過去2年未来1年               大谷 //
// 2021/11/29 出席者の先頭に、ログインユーザーを表示                   和氣 //
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
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo  $menu->out_title() ?></title>
<?php echo  $menu->out_site_java() ?>
<?php echo  $menu->out_css() ?>
<link rel='stylesheet' href='calendar.css?<?php echo  $uniq ?>' type='text/css' media='screen'>
<link rel='stylesheet' href='meeting_schedule.css?<?php echo  $uniq ?>' type='text/css' media='screen'>
<script type='text/javascript' language='JavaScript' src='meeting_schedule.js?=<?php echo  $uniq ?>'></script>
</head>
<body
    onLoad='
        MeetingSchedule.set_focus(document.apend_form.subject, "NOTselect");
        MeetingSchedule.attenCopy(document.apend_form.elements["atten[]"]);
        MeetingSchedule.strTimeCopy();
        MeetingSchedule.endTimeCopy();
    '
>
<center>
<?php echo  $menu->out_title_only_border() ?>
    
    <table border='0' align='center'>
        <tr>
        <a href='<?php echo $url_para ?>' style='text-decoration:none;' class='current'><font size='2'><B>今日</B></font></a>
        <select name='ind_ym' class='pt11b' onChange='location.href=value;'>
            <?php
            $ym     = date("Ym");
            $ym     = $ym + 100;
            $ym_cnt = 0;
            while(1) {
                $ym_year    = substr($ym,0,4);
                $ym_mon     = substr($ym,4,2);
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
    
    <table bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr><td> <!----------- ダミー(デザイン用) ------------>
    <table class='winbox_field' bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr>
        <form name='ControlForm' action='<?php echo $menu->out_self(), '?', $model->get_htmlGETparm(), "&id={$uniq}"?>' method='get'>
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
            <?php
            //if ($_SESSION['User_ID'] == '300144') {
            ?>
            <td nowrap <?php if($showMenu=='Car') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return MeetingSchedule.ControlFormSubmit(document.ControlForm.elements["Car"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='Car' id='Car'
                <?php if($showMenu=='Car') echo 'checked' ?>>
                <label for='Car'>社用車の編集</label>
            </td>
            <?php
            //}
            ?>
            <input type='hidden' name='year'  value='<?php echo $year?>'>
            <input type='hidden' name='month' value='<?php echo $month?>'>
            <input type='hidden' name='day'   value='<?php echo $day?>'>
            <td nowrap class='winbox' onClick='return MeetingSchedule.addFavoriteIcon("http://<?php echo $_SERVER['SERVER_ADDR'],$menu->out_self()?>", "<?php echo $_SESSION['User_ID']?>");' id='favi'>
                <label for='favi'>アイコン追加</label>
            </td>
        </form>
        </tr>
    </table>
        </td></tr>
    </table> <!----------------- ダミーEnd ------------------>
    
    <div class='caption_font'></div>
    
    <table class='list' bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <caption>
            <?php echo $menu->out_caption(), "\n"?>
        </caption>
        <tr><td> <!-- ダミー -->
    <table class='winbox_field' bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <form name='apend_form' action='<?php echo $menu->out_self(), '?', $model->get_htmlGETparm(), "&id={$uniq}"?>'
            method='post' onSubmit='return MeetingSchedule.apend_formCheck(this)'
        >
            <input type='hidden' name='showMenu' value='<?php echo $showMenu?>'>
        <tr>
            <th class='winbox' nowrap>
                件名<br>及び 特記事項
            </th>
            <td class='winbox' width='320' colspan='2'>
                <textarea name='subject' cols='66' rows=3 wrap='virtual'><?php echo $subject?></textarea>
            </td>
        </tr>
        <tr>
            <th class='winbox' nowrap>
                開始 終了
            </th>
            <?php
                if ($showMenu == 'Edit') {
                    $tmpYear  = $year;  $year  = $result->get('editYear');
                    $tmpMonth = $month; $month = $result->get('editMonth');
                    $tmpDay   = $day;   $day   = $result->get('editDay');
                }
            ?>
            <td class='winbox' nowrap colspan='2'>
                <select name='yearReg' size='1'>
                    <?php for ($i=($year-1); $i<=($year+3); $i++) { ?>
                    <?php $data = sprintf('%04d', $i); ?>
                    <option value='<?php echo $data?>'<?php if($year==$data)echo' selected' ?>><?php echo $data?></option>
                    <?php } ?>
                </select>
                年
                <select name='monthReg' size='1'>
                    <?php for ($i=1; $i<=12; $i++) { ?>
                    <?php $data = sprintf('%02d', $i); ?>
                    <option value='<?php echo $data?>'<?php if($month==$data)echo' selected' ?>><?php echo $data?></option>
                    <?php } ?>
                </select>
                月
                <select name='dayReg' size='1'>
                    <?php for ($i=1; $i<=31; $i++) { ?>
                    <?php $data = sprintf('%02d', $i); ?>
                    <option value='<?php echo $data?>'<?php if($day==$data)echo' selected' ?>><?php echo $data?></option>
                    <?php } ?>
                </select>
                日
                &nbsp;
                &nbsp;
                <select name='str_hour' size='1'
                    onClick ='MeetingSchedule.strTimeCopy();'
                    onChange='MeetingSchedule.strTimeCopy();'
                >
                    <?php for ($i=0; $i<=23; $i++) { ?>
                    <?php $data = sprintf('%02d', $i); ?>
                    <option value='<?php echo $data?>'<?php if($str_hour==$data)echo' selected' ?>><?php echo $data?></option>
                    <?php } ?>
                </select>
                時
                <select name='str_minute' size='1'
                    onClick ='MeetingSchedule.strTimeCopy();'
                    onChange='MeetingSchedule.strTimeCopy();'
                >
                    <?php for ($i=0; $i<=55; $i+=5) { ?>
                    <?php $data = sprintf('%02d', $i); ?>
                    <option value='<?php echo $data?>'<?php if($str_minute==$data)echo' selected' ?>><?php echo $data?></option>
                    <?php } ?>
                </select>
                分
                <input type='hidden' name='str_time' value='<?php echo $str_time?>' size='8' maxlength='8'
                    style='ime-mode:disabled;' class='pt12b' onChange='this.value=this.value.toUpperCase()'
                >
                ～
                <select name='end_hour' size='1'
                    onClick ='MeetingSchedule.endTimeCopy();'
                    onChange='MeetingSchedule.endTimeCopy();'
                >
                    <?php for ($i=0; $i<=23; $i++) { ?>
                    <?php $data = sprintf('%02d', $i); ?>
                    <option value='<?php echo $data?>'<?php if($end_hour==$data)echo' selected' ?>><?php echo $data?></option>
                    <?php } ?>
                </select>
                時
                <select name='end_minute' size='1'
                    onClick ='MeetingSchedule.endTimeCopy();'
                    onChange='MeetingSchedule.endTimeCopy();'
                >
                    <?php for ($i=0; $i<=55; $i+=5) { ?>
                    <?php $data = sprintf('%02d', $i); ?>
                    <option value='<?php echo $data?>'<?php if($end_minute==$data)echo' selected' ?>><?php echo $data?></option>
                    <?php } ?>
                </select>
                分
                &nbsp;&nbsp;
                <input type='hidden' name='end_time' value='<?php echo $end_time?>' size='8' maxlength='8'
                    style='ime-mode:disabled;' class='pt12b' onChange='this.value=this.value.toUpperCase()'
                >
            </td>
            <?php
                if ($showMenu == 'Edit') {
                     $year  = $tmpYear;
                     $month = $tmpMonth;
                     $day   = $tmpDay;
                }
                $oneself=0;
            ?>
        </tr>
        <tr>
            <th class='winbox' nowrap>
                主催者
            </th>
            <td class='winbox' nowrap colspan='2'>
                <select name='userID_name' size='1'
                    onClick ='MeetingSchedule.sponsorNameCopy();'
                    onChange='MeetingSchedule.sponsorNameCopy();'
                >
                    <option value=''>選択指定</option>
                    <?php for ($i=0; $i<$user_cnt; $i++) {?>
                    <option value='<?php echo $userID_name[$i][0]?>'<?php if($userID_name[$i][0]==$sponsor){echo' selected'; $oneself=$i;} ?>><?php echo $userID_name[$i][1]?></option>
                    <?php } ?>
                </select>
                <input type='text' name='sponsor' value='<?php echo $sponsor?>' size='7' maxlength='6'
                    style='ime-mode:disabled; background-color:#e6e6e6;' class='pt12b'
                    readonly onChange='this.value=this.value.toUpperCase()'
                >
            </td>
        </tr>
        <tr>
            <th class='winbox' nowrap rowspan='2'>
                出席者
            </th>
            <td class='winbox' nowrap colspan='2'>
                <select name='group_name' size='1'
                    onClick ='//MeetingSchedule.groupMemberCopy(document.apend_form.group_name, document.apend_form.elements["atten[]"]);'
                    onChange='MeetingSchedule.groupMemberCopy(document.apend_form.group_name, document.apend_form.elements["atten[]"]);'
                >
                    <option value=''>選択指定</option>
                    <?php for ($i=0; $i<$JSgroup; $i++) {?>
                    <option value='<?php echo $i?>'><?php echo $JSgroup_name[$i]?></option>
                    <?php } ?>
                </select>
                グループで指定する場合は、こちらを選んで下さい。
            </td>
        </tr>
        <tr>
            <td class='winbox' nowrap valign='top'>
                <select name='atten[]' size='5' multiple
                    onClick ='MeetingSchedule.attenCopy(this);'
                    onChange='MeetingSchedule.attenCopy(this);'
                >
                    <?php if( $_SESSION['User_ID'] != '000000' ) { ?>
                        <option value='<?php echo $userID_name[$oneself][0]?>'<?php echo @$userID_name[$oneself][2]?>><?php echo $userID_name[$oneself][1]?></option><!-- ログインユーザーを先頭に表示 -->
                    <?php } ?>
                    <?php for ($i=0; $i<$user_cnt; $i++) {?>
                    
                        <?php if( $_SESSION['User_ID'] != '000000' ) { ?>
                            <?php if( $oneself == $i ) continue; // ログインユーザーは、先頭に表示している為、ここではスキップ ?>
                        <?php } ?>

                    <option value='<?php echo $userID_name[$i][0]?>'<?php echo @$userID_name[$i][2]?>><?php echo $userID_name[$i][1]?></option>
                    <?php } ?>
                </select>
            </td>
            <td class='winbox' valign='middle'>
            
                <?php if( $_SESSION['User_ID'] != '000000' ) { ?>
                    <b><font size='2' color='red'>※ ログインユーザーは、先頭に表示されます。</font></b><BR>
                <?php } ?>
                
                Ctrl Key 又は Sift Key を押しながらクリックすれば複数選択できます。
                <textarea name='attenView' cols='51' rows=3 wrap='virtual' style='background-color:#e6e6e6;' readonly></textarea>
            </td>
        </tr>
        <tr>
            <th class='winbox' nowrap>
                メール送信
            </th>
            <td class='winbox' nowrap colspan='2'>
                <input type='radio' name='mail' value='t' id='yes'<?php if($mail=='t')echo' checked' ?>><label for='yes'>Yes</label>
                <input type='radio' name='mail' value='f' id='no'<?php if($mail!='t')echo' checked' ?>><label for='no'>No</label>
                &nbsp;&nbsp;出席者へのメール案内
            </td>
        </tr>
        <tr>
            <th class='winbox' nowrap>
                場所
            </th>
            <td class='winbox' nowrap colspan='2'>
                <select name='room_no' size='1'>
                    <?php for ($i=0; $i<$rowsRoom; $i++) {?>
                    <option value='<?php echo $resRoom[$i][0]?>'<?php if($resRoom[$i][0]==$room_no)echo' selected' ?>><?php echo $resRoom[$i][1]?></option>
                    <?php } ?>
                </select>
                <!--
                <input type='hidden' name='room_no' value='<?php echo $room_no?>' size='6' maxlength='6'
                    class='pt12b' onClick="this.value = this.value + ' '; this.select();"
                >
                -->
            </td>
        </tr>
        <?php
        //if ($_SESSION['User_ID'] == '300144') {
        ?>
        <tr>
            <th class='winbox' nowrap>
                社用車
            </th>
            <td class='winbox' nowrap colspan='2'>
                <select name='car_no' size='1'>
                    <?php for ($i=0; $i<$rowsCar; $i++) {?>
                    <option value='<?php echo $resCar[$i][0]?>'<?php if($resCar[$i][0]==$car_no)echo' selected' ?>><?php echo $resCar[$i][1]?></option>
                    <?php } ?>
                </select>
            </td>
        </tr>
        <?php
        //} else {
        ?>
        <!--
        <input type='hidden' name='car_no' value='1'>
        -->
        <?php
        //}
        ?>
        
        <tr>
                <?php if ($showMenu == 'Edit') { ?>
            <td class='winbox' align='right' nowrap colspan='2'>
                メール再送信
                <input type='radio' name='reSend' value='t' id='sendYes'><label for='sendYes'>Yes</label>
                <input type='radio' name='reSend' value='f' id='sendNo' checked><label for='sendNo'>No</label>
                <input type='submit' name='<?php echo $showMenu?>' value='上書' class='fc_blue'>
            </td>
            <td class='winbox' align='left' nowrap>
                <?php } else { ?>
            <td class='winbox' align='center' nowrap colspan='3'>
                <input type='submit' name='<?php echo $showMenu?>' value='登録' class='fc_blue'>
                <?php } ?>
                &nbsp; &nbsp;
                <input type='button' name='Cancel' value='取消'
                    onClick='location.replace("<?php echo $menu->out_self(), "?year={$year}&month={$month}&day={$day}&showMenu=List&", $model->get_htmlGETparm(), "&id={$uniq}"?>");'
                >
                <?php if ($showMenu == 'Edit') { ?>
                &nbsp; &nbsp; &nbsp;
                <input type='submit' name='Delete' value='削除' class='fc_red'
                    onClick='return confirm("削除すると元へは戻せません！\n\n宜しいですか？");'
                >
                &nbsp;
                <input type='submit' name='Apend' value='コピー保存' class='fc_green'
                    onClick='return confirm("元のスケジュールは変更せず\n\n新しく追加します。\n\n宜しいですか？");'
                >
                <?php } ?>
            </td>
            <input type='hidden' name='<?php echo $showMenu?>' value='dummy'>
            <input type='hidden' name='serial_no' value='<?php echo $serial_no?>'>
            <input type='hidden' name='year'  value='<?php echo $year?>'>
            <input type='hidden' name='month' value='<?php echo $month?>'>
            <input type='hidden' name='day'   value='<?php echo $day?>'>
        </tr>
        </form>
    </table>
        </td></tr> <!-- ダミー -->
    </table>
</center>
</body>
<?php echo $menu->out_alert_java()?>
<?php
if ($JSgroup) {
echo "<script type='text/javascript'>\n";
echo "var Ggroup_member = new Array({$JSgroup});\n";
for ($r=0; $r<$JSgroup; $r++) {
    $script = "    Ggroup_member[{$r}] = new Array(";
    $cnt = count($JSgroup_member[$r]);
    for ($i=0; $i<$cnt; $i++) {
        if ($i == 0) {
            $script .= "'{$JSgroup_member[$r][$i]}'";
        } else {
            $script .= ", '{$JSgroup_member[$r][$i]}'";
        }
    }
    $script .= ");\n";
    echo $script;
}
echo "</script>\n";
}
?>
</html>
