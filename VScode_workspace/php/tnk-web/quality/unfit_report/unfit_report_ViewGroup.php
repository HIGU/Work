<?php
//////////////////////////////////////////////////////////////////////////////
// 全社共有 不適合報告書のグループ宛先(報告先)の編集                        //
//                                         MVC View 部  グループリスト表示  //
// Copyright (C) 2008 Norihisa.Ohya usoumu@nitto-kohki.co.jp                //
// Changed history                                                          //
// 2008/05/30 Created   unfit_report_ViewGroup.php                          //
// 2008/08/29 masterstで本稼動開始                                          //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo  $menu->out_title() ?></title>
<?php echo  $menu->out_site_java() ?>
<?php echo  $menu->out_css() ?>
<?php echo  $menu->out_jsBaseClass() ?>
<link rel='stylesheet' href='calendar.css?<?php echo  $uniq ?>' type='text/css' media='screen'>
<link rel='stylesheet' href='unfit_report.css?<?php echo  $uniq ?>' type='text/css' media='screen'>
<script type='text/javascript' src='unfit_report.js?=<?php echo  $uniq ?>'></script>
</head>
<body
    onLoad='
        UnfitReport.set_focus(document.group_form.<?php echo $focus?>, "noSelect");
        <?php if ($groupCopy) { ?>
        UnfitReport.attenCopy2(document.group_form.elements["atten[]"], document.group_form.attenView);
        <?php } ?>
    '
>
<center>
<?php echo  $menu->out_title_border() ?>
    <!--
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
    -->
    <form name='ControlForm' action='<?php echo $menu->out_self(), '?', $model->get_htmlGETparm(), "&id={$uniq}"?>' method='get'>
    <table bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr><td> <!----------- ダミー(デザイン用) ------------>
    <table class='winbox_field' bgcolor='e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr>
            <td nowrap <?php if($showMenu=='Apend') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return UnfitReport.ControlFormSubmit(document.ControlForm.elements["Apend"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='Apend' id='Apend'
                <?php if($showMenu=='Apend') echo 'checked' ?>>
                <label for='Apend'>報告書入力</label>
            </td>
            <td nowrap <?php if($showMenu=='IncompleteList') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return UnfitReport.ControlFormSubmit(document.ControlForm.elements["IncompleteList"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='IncompleteList' id='IncompleteList'
                <?php if($showMenu=='IncompleteList') echo 'checked' ?>>
                <label for='IncompleteList'>対策未完了一覧</label>
            </td>
            <td nowrap <?php if($showMenu=='CompleteList') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return UnfitReport.ControlFormSubmit(document.ControlForm.elements["CompleteList"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='CompleteList' id='CompleteList'
                <?php if($showMenu=='CompleteList') echo 'checked' ?>>
                <label for='CompleteList'>対策完了一覧</label>
            </td>
            <td nowrap <?php if($showMenu=='FollowList') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return UnfitReport.ControlFormSubmit(document.ControlForm.elements["FollowList"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='FollowList' id='FollowList'
                <?php if($showMenu=='FollowList') echo 'checked' ?>>
                <label for='FollowList'>フォローアップ完了一覧</label>
            </td>
            <td nowrap <?php if($showMenu=='Group') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return UnfitReport.ControlFormSubmit(document.ControlForm.elements["Group"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='Group' id='Group'
                <?php if($showMenu=='Group') echo 'checked' ?>>
                <label for='Group'>グループの編集</label>
            </td>
            <input type='hidden' name='year'  value='<?php echo $year?>'>
            <input type='hidden' name='month' value='<?php echo $month?>'>
            <input type='hidden' name='day'   value='<?php echo $day?>'>
            <!-----------------
            <td nowrap class='winbox' onClick='return UnfitReport.addFavoriteIcon("http://<?php echo $_SERVER['SERVER_ADDR'],$menu->out_self()?>", "<?php echo $_SESSION['User_ID']?>");' id='favi'>
                <label for='favi'>アイコン追加</label>
            </td>
            ------------------>
        </tr>
    </table>
        </td></tr>
    </table> <!----------------- ダミーEnd ------------------>
    </form>
    
    <table class='list' bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <caption>報告先グループの編集<BR><span style='color:red;'>※ 会議スケジュールのグループと共有なので、削除・変更する際は注意してください。</span></caption>
        <tr><td> <!-- ダミー -->
    <table class='winbox_field' bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <form name='group_form' action='<?php echo $menu->out_self(), '?', $model->get_htmlGETparm(), "&id={$uniq}"?>'
            method='post' onSubmit='return UnfitReport.group_formCheck(this)'
        >
        <tr>
            <th class='winbox' nowrap>
                グループ番号
            </th>
            <td class='winbox' colspan='2' nowrap>
                <input type='text' name='group_no2' value='<?php echo  $group_no ?>' size='3' maxlength='3'
                    style='ime-mode:disabled;'
                    onChange='this.value=this.value.toUpperCase()'
                <?php echo $readonly?>
                >
            </td>
        </tr>
        <tr>
            <th class='winbox' nowrap>
                グループ名
            </th>
            <td class='winbox' colspan='2' nowrap>
                <input type='text' name='group_name' value='<?php echo $group_name?>' size='40' maxlength='80' class='pt12b'>
            </td>
        </tr>
        <tr>
            <th class='winbox' nowrap>
                報告先
            </th>
            <td class='winbox' nowrap valign='top'>
                <select name='atten[]' size='5' multiple
                    onClick ='UnfitReport.attenCopy2(this, document.group_form.attenView);'
                    onChange='UnfitReport.attenCopy2(this, document.group_form.attenView);'
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
                個人・共有
            </th>
            <td class='winbox' colspan='2' nowrap>
                <?php if (!$groupCopy) { ?>
                <input type='radio' name='owner' value='<?php if($_SESSION['User_ID']!='000000')echo $_SESSION['User_ID'] ?>' id='yes'<?php if($_SESSION['User_ID']=='000000')echo' disabled' ?>><label for='yes'>個人用</label>
                <input type='radio' name='owner' value='000000' id='no'<?php if($_SESSION['User_ID']=='000000')echo' checked' ?>><label for='no'>共有用</label>
                <?php } else { ?>
                <input type='radio' name='owner' value='<?php echo $_SESSION['User_ID']?>' id='yes'<?php if($owner!='000000')echo' checked' ?>><label for='yes'>個人用</label>
                <input type='radio' name='owner' value='000000' id='no'<?php if($owner=='000000')echo' checked' ?>><label for='no'>共有用</label>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <td class='winbox' align='center' colspan='3' nowrap>
                <input type='submit' name='groupEdit' value='登録' class='pt12b'>
            </td>
        </tr>
            <input type='hidden' name='showMenu' value='Group'>
            <input type='hidden' name='year'  value='<?php echo $year?>'>
            <input type='hidden' name='month' value='<?php echo $month?>'>
            <input type='hidden' name='day'   value='<?php echo $day?>'>
        </form>
    </table>
        </td></tr> <!-- ダミー -->
    </table>
    
    <table class='list' bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <caption>
            <table border='0' width='100%'>
                <tr>
                    <td align='right' nowrap width='60%'>
                        報告先グループの一覧 &nbsp;&nbsp;
                    </td>
                    <td align='center' nowrap width='40%'>
                        <form name='pageForm' action='<?php echo $menu->out_self(), "?id={$uniq}"?>' method='post'>
                        <?php echo $pageControl?>
                        <input type='hidden' name='showMenu' value='Group'>
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
        <th class='winbox' nowrap>グループ番号</th>
        <th class='winbox' nowrap>報告先グループ名</th>
        <th class='winbox' width='70'>報告先</th>
        <th class='winbox' nowrap>個人・共有</th>
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
                <?php if ($res[$r][2] == '000000' || $res[$r][2] == $_SESSION['User_ID']) { ?>
                <a href='<?php echo $menu->out_self(), "?group_no2={$res[$r][0]}&showMenu=Group&groupOmit=go&group_name=", urlencode($res[$r][1]), '&owner=', urlencode($res[$r][2]), "&year={$year}&month={$month}&day={$day}&", $model->get_htmlGETparm(), "&id={$uniq}"?>'
                    style='text-decoration:none;'
                    onClick='return confirm("本当に必要なければ削除してかまいませんが\n\n後で使用する可能性がある場合は無効にして下さい。\n\n削除します宜しいですか？")'
                >
                    削除
                </a>
                <?php } else { ?>
                    削除
                <?php } ?>
            </td>
            <td class='winbox' align='center' nowrap>
                <?php if ($res[$r][2] == '000000' || $res[$r][2] == $_SESSION['User_ID']) { ?>
                <a href='<?php echo $menu->out_self(), "?group_no2={$res[$r][0]}&showMenu=Group&groupCopy=go&group_name=", urlencode($res[$r][1]), '&owner=', urlencode($res[$r][2]), "&year={$year}&month={$month}&day={$day}&", $model->get_htmlGETparm(), "&id={$uniq}"?>'
                    style='text-decoration:none;'>
                    編集
                </a>
                <?php } else { ?>
                    編集
                <?php } ?>
            </td>
            <!-- グループ番号 -->
            <td class='winbox' align='right'><?php echo $res[$r][0]?></td>
            <!-- グループ名 -->
            <td class='winbox' align='left'><?php echo $res[$r][1]?></td>
            <!-- 報告先 -->
            <td class='winbox' align='left' nowrap>
                <?php 
                for ($i=0; $i<$rowsAtten[$r]; $i++) {
                    echo "{$resAtten[$r][$i][0]}<br>";
                }
                ?>
            </td>
            <!-- 個人・共用 -->
            <td class='winbox' align='center'
                <?php if ($res[$r][2] != '000000') { ?>
                onDblclick='alert("[ <?php echo $res[$r][6]?> ] さん 個人のものです。");'
                <?php } ?>
            >
                <?php if ($res[$r][2] == '000000') echo '共有'; else echo '個人';?>
            </td>
            <!-- 有効／無効 -->
            <td class='winbox' align='center' nowrap>
                <a href='<?php echo $menu->out_self(), "?group_no2={$res[$r][0]}&showMenu=Group&groupActive=go&group_name=", urlencode($res[$r][1]), '&owner=', urlencode($res[$r][2]), "&year={$year}&month={$month}&day={$day}&", $model->get_htmlGETparm(), "&id={$uniq}"?>'
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
        <tr><td class='winbox' align='center' colspan='9'>
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
