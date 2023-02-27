<?php
//////////////////////////////////////////////////////////////////////////////
// 全社共有 打合せ(会議)スケジュール表の照会・メンテナンス                  //
//                                                  MVC View 部   追加      //
// Copyright (C) 2005-2019 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
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
// 2018/06/18 時間を0〜23に変更                                        大谷 //
// 2019/03/15 冷温水機稼働状況、社用車、不在者のメニューを追加         大谷 //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
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
                〜
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
                    <option value='<?php echo $userID_name[$i][0]?>'<?php if($userID_name[$i][0]==$sponsor)echo' selected' ?>><?php echo $userID_name[$i][1]?></option>
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
                    <?php for ($i=0; $i<$user_cnt; $i++) {?>
                    <option value='<?php echo $userID_name[$i][0]?>'<?php echo @$userID_name[$i][2]?>><?php echo $userID_name[$i][1]?></option>
                    <?php } ?>
                </select>
            </td>
            <td class='winbox' valign='middle'>
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
                    onClick='location.replace("<?php echo $menu->out_self(), "?year={$year}&month={$month}&day={$day}&showMenu=List&only=yes&", $model->get_htmlGETparm(), "&id={$uniq}"?>");'
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
            <input type='hidden' name='only'   value='yes'>
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
