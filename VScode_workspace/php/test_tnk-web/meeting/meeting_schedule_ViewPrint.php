<?php
//////////////////////////////////////////////////////////////////////////////
// 全社共有 会議(打合せ)スケジュール表の照会・印刷                          //
//                                         MVC View 部  照会・印刷          //
// Copyright (C) 2009-2015 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2009/12/17 Created   meeting_schedule_ViewPrint.php                      //
// 2010/01/08 暫定表示に桝さんと川崎さんを追加。印刷へのリンクをコメント化  //
// 2015/06/19 計画有給の照会を追加                                          //
// 2015/06/25 計画有給の照会を共通権限に変更（53）                     大谷 //
//////////////////////////////////////////////////////////////////////////////
if ( isset($_SESSION['str_date']) ) {
    $str_date = $_SESSION['str_date'];
} else {
    if ( isset($_POST['str_date']) ) {
        $str_date = $_POST['str_date'];
    } else {
        $str_date = date_offset(0);
    }
}
if ( isset($_SESSION['end_date']) ) {
    $end_date = $_SESSION['end_date'];
} else {
    if ( isset($_POST['end_date']) ) {
        $end_date = $_POST['end_date'];
    } else {
        $end_date = date_offset(0);
    }
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
<?php echo  $menu->out_jsBaseClass() ?>
<link rel='stylesheet' href='calendar.css?<?php echo  $uniq ?>' type='text/css' media='screen'>
<link rel='stylesheet' href='meeting_schedule.css?<?php echo  $uniq ?>' type='text/css' media='screen'>
<script type='text/javascript' src='meeting_schedule.js?=<?php echo  $uniq ?>'></script>
</head>
<body>
<center>
<?php echo  $menu->out_title_only_border() ?>
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
                onClick='return MeetingSchedule.ControlFormSubmit(document.ControlForm.elements["Apend"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='Apend' id='Apend'
                <?php if($showMenu=='Apend') echo 'checked' ?>>
                <label for='Apend'>会議入力</label>
            </td>
            <td nowrap <?php if($showMenu=='List') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return MeetingSchedule.ControlFormSubmit(document.ControlForm.elements["List"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='List' id='List'
                <?php if($showMenu=='List') echo 'checked' ?>>
                <label for='List'>会議一覧</label>
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
            <?php
            if($_SESSION['User_ID'] == '300144' || $_SESSION['User_ID'] == '014737' || $_SESSION['User_ID'] == '300055') {
            ?>
            <td nowrap <?php if($showMenu=='Print') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return MeetingSchedule.ControlFormSubmit(document.ControlForm.elements["Print"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='Print' id='Print'
                <?php if($showMenu=='Print') echo 'checked' ?>>
                <label for='Print'>照会</label>
            </td>
            <?php
            }
            ?>
            <td nowrap <?php if($showMenu=='Group') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return MeetingSchedule.ControlFormSubmit(document.ControlForm.elements["Group"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='Group' id='Group'
                <?php if($showMenu=='Group') echo 'checked' ?>>
                <label for='Group'>グループ編集</label>
            </td>
            <td nowrap <?php if($showMenu=='Room') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return MeetingSchedule.ControlFormSubmit(document.ControlForm.elements["Room"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='Room' id='Room'
                <?php if($showMenu=='Room') echo 'checked' ?>>
                <label for='Room'>会議室編集</label>
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
    </form>
    
    <table class='list' bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <caption>スケジュールの照会・印刷</caption>
        <tr><td> <!-- ダミー -->
    <table class='winbox_field' bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <form name='print_form' action='<?php echo $menu->out_self(), '?', $model->get_htmlGETparm(), "&id={$uniq}"?>'
            method='post' onSubmit='return MeetingSchedule.print_formCheck(this)'
        >
        <tr>
            <th class='winbox' nowrap>
                場所
            </th>
            <td class='winbox' nowrap colspan='4'>
                <select name='room_no' size='1'>
                    <option value='' <?php if($room_no=='')echo' selected' ?>>指定無し</option>
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
        <tr>
            <th class='winbox' nowrap>
                日付
            </th>
            <td class='winbox' colspan='4' nowrap>
                <input type='text' name='str_date' value='<?php echo $str_date?>' size='9' maxlength='8' class='pt12b'>
            ～
                <input type='text' name='end_date' value='<?php echo $end_date?>' size='9' maxlength='8' class='pt12b'>
            </td>
        </tr>
        <tr>
            <td class='winbox' align='center' colspan='5' nowrap>
                <input type='submit' name='showprint' value='照会' class='pt12b'>
                <?php
                 if ($showprint != '') {
                ?>
                <!--
                <a href='<?php echo "meeting_schedule_Print_ja.php?room_no={$room_no}&str_date={$str_date}&end_date={$end_date}&id={$uniq}"?>'
                    style='text-decoration:none;' target='_blank'><B>印刷<B>
                </a>
                -->
                <?php
                 }
                ?>
            </td>
        </tr>
            <input type='hidden' name='showMenu' value='Print'>
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
                    <td align='center' nowrap width='40%'>
                        <form name='pageForm' action='<?php echo $menu->out_self(), "?id={$uniq}"?>' method='post'>
                        <?php echo $pageControl?>
                        <input type='hidden' name='showMenu' value='Print'>
                        <input type='hidden' name='year'  value='<?php echo $year?>'>
                        <input type='hidden' name='month' value='<?php echo $month?>'>
                        <input type='hidden' name='day'   value='<?php echo $day?>'>
                        <input type='hidden' name='room_no' value='<?php echo $room_no?>'>
                        <input type='hidden' name='str_date' value='<?php echo $str_date?>'>
                        <input type='hidden' name='end_date'   value='<?php echo $end_date?>'>
                        <input type='hidden' name='showprint' value='showprint'>
                        </form>
                    </td>
                </tr>
            </table>
        </caption>
        <tr><td> <!-- ダミー -->
    <table class='winbox_field' bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <th class='winbox' width='20'>No.</th>
        <th class='winbox' width='320'>件名 及び 特記事項</th>
        <th class='winbox' nowrap>開始日時</th>
        <th class='winbox' nowrap>終了日時</th>
        <th class='winbox' width='70'>主催者</th>
        <th class='winbox' width='70'>出席者</th>
        <th class='winbox' nowrap>場　所</th>
    <?php if ($rows >= 1) { ?>
        <?php for ($r=0; $r<$rows; $r++) { ?>
            <tr>
            <td class='winbox' align='right' nowrap><?php echo $r + 1 + $model->get_offset()?></td>
            <!-- 会議件名 -->
            <td class='winbox' align='left'><?php echo $res[$r][1]?></td>
            <!-- 開始日時 -->
            <td class='winbox' align='center'><?php echo $res[$r][2]?></td>
            <!-- 終了日時 -->
            <td class='winbox' align='center'><?php echo $res[$r][3]?></td>
            <!-- 主催者 -->
            <td class='winbox' align='center' nowrap>
            <?php
                if ($res[$r][5] == $_SESSION['User_ID']) {
                    echo "<span style='color:red;'>{$res[$r][6]}</span>\n";
                } else {
                    echo "{$res[$r][6]}\n";
                }
            ?>
            </td>
            <!-- 出席者 -->
            <td class='winbox' align='left' nowrap onDblclick='alert("メール案内は\n\n[ <?php echo $res[$r][15]?> ]\n\nに設定されています。");'>
                <?php
                if($showMenu=='MyList') {
                    // 報告先を折りたたんで表示するロジック
                    if ($atten_flg == 1) {
                        if ($res[$r][0] == $serial_no) { 
                            for ($i=0; $i<$rowsAtten[$r]; $i++) {
                                if ($resAtten[$r][$i][1] == $_SESSION['User_ID']) {
                                    echo "<span style='color:red;'>{$resAtten[$r][$i][2]}</span><br>";
                                } else {
                                    echo "{$resAtten[$r][$i][2]}<br>";
                                }
                            }
                        } else {
                            $myatten     = 0;
                            $myatten_flg = 0;
                            for ($i=0; $i<$rowsAtten[$r]; $i++) {
                                if ($resAtten[$r][$i][1] == $_SESSION['User_ID']) {
                                    $myatten     = $i;
                                    $myatten_flg = 1;
                                }
                            }
                            if ($myatten_flg == 1) {
                                echo "<span style='color:red;'>{$resAtten[$r][$myatten][2]}</span><br>";
                                if ($rowsAtten[$r] > 1) {
                                    $num = $rowsAtten[$r] - 1;
                            ?>
                            <a href='<?php echo $menu->out_self(), "?serial_no={$res[$r][0]}&atten_flg=1&year={$year}&month={$month}&day={$day}&showMenu={$showMenu}&", $model->get_htmlGETparm(), "&id={$uniq}"?>'
                            style='text-decoration:none;'>他<?php echo $num ?>名
                            </a><br>
                            <?php
                                }
                            } else {
                                echo "{$resAtten[$r][0][2]}<br>";
                                if ($rowsAtten[$r] > 1) {
                                $num = $rowsAtten[$r] - 1;
                                ?>
                                <a href='<?php echo $menu->out_self(), "?serial_no={$res[$r][0]}&atten_flg=1&year={$year}&month={$month}&day={$day}&showMenu={$showMenu}&", $model->get_htmlGETparm(), "&id={$uniq}"?>'
                                style='text-decoration:none;'>他<?php echo $num ?>名
                                </a><br>
                                <?php
                                }
                            }
                        }
                    } else {
                        $myatten     = 0;
                        $myatten_flg = 0;
                        for ($i=0; $i<$rowsAtten[$r]; $i++) {
                            if ($resAtten[$r][$i][1] == $_SESSION['User_ID']) {
                                $myatten     = $i;
                                $myatten_flg = 1;
                            }
                        }
                        if ($myatten_flg == 1) {
                            echo "<span style='color:red;'>{$resAtten[$r][$myatten][2]}</span><br>";
                            if ($rowsAtten[$r] > 1) {
                                $num = $rowsAtten[$r] - 1;
                        ?>
                        <a href='<?php echo $menu->out_self(), "?serial_no={$res[$r][0]}&atten_flg=1&year={$year}&month={$month}&day={$day}&showMenu={$showMenu}&", $model->get_htmlGETparm(), "&id={$uniq}"?>'
                        style='text-decoration:none;'>他<?php echo $num ?>名
                        </a><br>
                        <?php
                            }
                        } else {
                            echo "{$resAtten[$r][0][2]}<br>";
                            if ($rowsAtten[$r] > 1) {
                                $num = $rowsAtten[$r] - 1;
                                ?>
                                <a href='<?php echo $menu->out_self(), "?serial_no={$res[$r][0]}&atten_flg=1&year={$year}&month={$month}&day={$day}&showMenu={$showMenu}&", $model->get_htmlGETparm(), "&id={$uniq}"?>'
                                style='text-decoration:none;'>他<?php echo $num ?>名
                                </a><br>
                                <?php
                            }
                        }
                    }
                } else {
                    for ($i=0; $i<$rowsAtten[$r]; $i++) {
                        if ($resAtten[$r][$i][1] == $_SESSION['User_ID']) {
                            echo "<span style='color:red;'>{$resAtten[$r][$i][2]}</span><br>";
                        } else {
                            echo "{$resAtten[$r][$i][2]}<br>";
                        }
                    }
                    echo "\n";
                }
                    ?>
            </td>
            <!-- 場所 -->
            <td class='winbox' align='left'><?php echo $res[$r][4]?></td>
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
