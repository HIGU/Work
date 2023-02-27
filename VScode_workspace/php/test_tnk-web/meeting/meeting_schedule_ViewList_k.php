<?php
//////////////////////////////////////////////////////////////////////////////
// 全社共有 打合せ(会議)スケジュール表の照会・メンテナンス                  //
//                                          MVC View 部     リスト表示      //
// Copyright (C) 2005-2021 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/11/02 Created   meeting_schedule_ViewList.php                       //
// 2005/11/24 addFavoriteIcon(url,uid)お気に入りにアイコン追加メソッドを追加//
// 2006/05/09 自分のスケジュールのみ表示(マイリスト)機能を追加              //
// 2006/06/20 カレンダー部の</tr>が抜けているのを修正                       //
// 2008/09/01 出席者の表示を折りたたんで表示できるように修正                //
//                                          (マイリストのみ)           大谷 //
// 2009/12/17 照会・印刷用画面(Print)テスト                            大谷 //
// 2015/06/19 計画有給の照会を追加                                     大谷 //
// 2015/06/25 計画有給の照会を共通権限に変更（53）                     大谷 //
// 2019/01/23 冷温水機稼働状況をテスト的に表示(総務課員のみ 56)        大谷 //
// 2019/03/13 不在者の表示を暫定追加（自分のみ）                       大谷 //
// 2019/03/15 冷温水機稼働状況、社用車、不在者のメニューを追加         大谷 //
// 2019/11/01 冷温水機状態変更を制限したときのエラーに対応             大谷 //
// 2019/12/13 画面遷移で冷温水機の稼動状況が変化してしまうのを修正     大谷 //
// 2020/01/06 上記修正でon_changeがエラーになるのに対応                大谷 //
// 2020/08/19 冷温水機稼動状況で変更権限が無い場合に照会ボタン表示を        //
//            しないように変更                                         大谷 //
// 2020/08/19 PIのスケジュールを表示するとエラーになるのでコメント化   大谷 //
// 2020/09/11 通達発効状況照会ウインドウ表示を追加                     大谷 //
// 2020/11/27 営繕状況照会ウインドウ表示を追加                         大谷 //
// 2021/06/09 今日へ戻るリンクを追加                                   大谷 //
// 2021/06/10 カレンダーの年月移動を追加。過去2年未来1年               大谷 //
//////////////////////////////////////////////////////////////////////////////
///// カレンダー移動用の年月が設定されているかチェック
if ($request->get('ind_ym') == '') {
    // 初期値(本日)を設定
    $ind_ym = date('Ym');
} elseif (isset($_SESSION['ind_ym'])) {
    $ind_ym = $_SESSION['ind_ym'];
} else {
    $ind_ym = $request->get('ind_ym');
}

$today = date('Ymd');
$last_host = $_SERVER['REMOTE_ADDR'] . ' ' . $_SESSION['User_ID'];
if ( isset($_SESSION['OnOff']) ) {
    $OnOff = $_SESSION['OnOff'];
} elseif ( isset($_POST['OnOff']) ) {
    $OnOff = $_POST['OnOff'];
} elseif ( isset($_GET['OnOff']) ) {
    $OnOff = $_GET['OnOff'];
    if($OnOff == "") {
        $OnOff = 0; // エラー防止
    }
    // 変更ボタンが押されたときは変更
    if($_GET['on_change']=='変更') {
        // 冷温水機状態変更時のチェック
        $query_con = "
                SELECT OnOff FROM meeting_conditioning_history WHERE to_char(regdate, 'YYYYMMDD')={$today} ORDER BY regdate DESC limit 1
            ";
        $res_con = array();
        if (getResult2($query_con, $res_con) <= 0) {
            // 本日の登録が無ければ無条件で変更された状態をチェック
            $query_in = sprintf("INSERT INTO meeting_conditioning_history
                            (OnOff, last_host)
                            VALUES ({$OnOff},'{$last_host}')");
            query_affected($query_in);
        } else {
            // 本日の登録がある場合、最新状態と変更があれば登録する
            if($OnOff == $res_con[0][0]) {
                
            } else {
                $query_in = sprintf("INSERT INTO meeting_conditioning_history
                            (OnOff, last_host)
                            VALUES ({$OnOff},'{$last_host}')");
                query_affected($query_in);
            }
        }
    }
} else {
    $OnOff = 0;
}

// 当日の最新冷温水機稼働状況を取得
$query_con = "
            SELECT OnOff FROM meeting_conditioning_history WHERE to_char(regdate, 'YYYYMMDD')={$today} ORDER BY regdate DESC limit 1
        ";
$res_con = array();
if (getResult2($query_con, $res_con) <= 0) {
    $OnOff = 0;
} else {
    $OnOff = $res_con[0][0];
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
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<?php echo $menu->out_jsBaseClass() ?>
<link rel='stylesheet' href='calendar.css?<?php echo $uniq ?>' type='text/css' media='screen'>
<link rel='stylesheet' href='meeting_schedule.css?<?php echo $uniq ?>' type='text/css' media='screen'>
<link rel='stylesheet' href='meeting_schedule_radio.css?<?php echo $uniq ?>' type='text/css' media='screen'>
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
    
    <form name='ControlForm' action='<?php echo $menu->out_self(), '?', $model->get_htmlGETparm(), "&id={$uniq}"?>' method='get'>
    <table bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr><td> <!----------- ダミー(デザイン用) ------------>
    <table class='winbox_field' bgcolor='e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr>
            <!--
            <td nowrap <?php if($showMenu=='Apend') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return MeetingSchedule.ControlFormSubmit(document.ControlForm.elements["Apend"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='Apend' id='Apend'
                <?php if($showMenu=='Apend') echo 'checked' ?>>
                <label for='Apend'>会議(打合せ)入力</label>
            </td>
            -->
            <td nowrap <?php if($showMenu=='List') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return MeetingSchedule.ControlFormSubmit(document.ControlForm.elements["List"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='List' id='List'
                <?php if($showMenu=='List') echo 'checked' ?>>
                <label for='List'>会議(打合せ)一覧</label>
            </td>
            <td nowrap <?php if($showMenu=='Over') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return MeetingSchedule.ControlFormSubmit(document.ControlForm.elements["Over"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='Over' id='Over'
                <?php if($showMenu=='Over') echo 'checked' ?>>
                <label for='Over'>残業予定</label>
            </td>
            <!--
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
            -->
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
            <!--
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
            -->
            <input type='hidden' name='year'  value='<?php echo $year?>'>
            <input type='hidden' name='month' value='<?php echo $month?>'>
            <input type='hidden' name='day'   value='<?php echo $day?>'>
            <!--
            <td nowrap class='winbox' onClick='return MeetingSchedule.addFavoriteIcon("http://<?php echo $_SERVER['SERVER_ADDR'],$menu->out_self()?>", "<?php echo $_SESSION['User_ID']?>");' id='favi'>
                <label for='favi'>アイコン追加</label>
            </td>
            -->
        </tr>
    </table>
        </td></tr>
    </table> <!----------------- ダミーEnd ------------------>
    
    <?php
    if ($showMenu=='List' || $showMenu=='MyList') {
    ?>
    <div class='caption_font'></div>
    
    <table class='list' bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <caption>
            <table border='0' width='100%'>
                <tr>
                    <td align='right' nowrap width='60%'>
                        <!--
                        <?php
                        if (getCheckAuthority(57)) {
                        //if ($_SESSION['User_ID'] == '300144') {
                        ?>
                        <input type='button' name='exec2' value='不在者' style='width:54px;' onClick='MeetingSchedule.checkANDexecute(2);' title='別ウィンドウで表示します。'>
                            <?php
                            if( $_SESSION['User_ID'] == '300144' || $_SESSION['User_ID'] == '300667' || $_SESSION['User_ID'] == '011061' ) { // || $_SESSION['User_ID'] == '011061'
                            ?>
                            <!--
                            <input type='button' name='exec3' value='PI' style='width:54px;' onClick='MeetingSchedule.checkANDexecute(5);' title='別ウィンドウで表示します。'>
                            -->
                            <?php
                            }
                        } else {
                        ?>
                        <?php echo $menu->out_caption()?>
                        <?php
                        }
                        ?>
                        <script>setSelectDate(<?php echo $request->get('year'); ?>,<?php echo $request->get('month'); ?>,<?php echo $request->get('day'); ?>);</script>
                        <input type='button' name='exec4' value='会議室' style='width:54px;' onClick='MeetingSchedule.checkANDexecute(8);' title='別ウィンドウで表示します。'>
                        -->
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
                    <td align='center' nowrap width='40%'>
                        <?php echo $pageControl?>
                    </td>
                </tr>
            </table>
        </caption>
        <caption>
            <table border='0' width='50%'>
            <tr>
                    <td align='right' nowrap width='50%'>
                <?php
                if (getCheckAuthority(67)) {
                ?>
                <input type='button' name='notification' value='営繕' style='width:54px;' onClick='MeetingSchedule.checkANDexecute(7);' title='別ウィンドウで表示します。'>
                <?php
                }
                ?>
                <?php
                if (getCheckAuthority(62)) {
                ?>
                <input type='button' name='notification' value='通達' style='width:54px;' onClick='MeetingSchedule.checkANDexecute(6);' title='別ウィンドウで表示します。'>
                <?php
                }
                ?>
    <?php
    if (getCheckAuthority(56)) {
    if ($showMenu=='List') {
    ?>
                <!--
                        <B>
                        <?php echo date('Y年n月j日', strtotime($today)); ?>
                        冷温水機稼働状況
                        </B>
                        <form name='OnOff' action='<?php echo $menu->out_self(), '?', $model->get_htmlGETparm(), "&id={$uniq}"?>' method='get'>
                        <?php
                        if ($_SESSION['User_ID'] == '300144') {
                        ?>
                        <input type='radio' name='OnOff' value='1' id='on'<?php if($OnOff==1)echo' checked'?>>
                            <label for='on'<?php if($OnOff==1)echo" style='color:lime;font-weight:bold;'"?>>稼動中</label>
                        <input type='radio' name='OnOff' value='0' id='off'<?php if($OnOff==0)echo' checked'?>>
                            <label for='off'<?php if($OnOff==0)echo" style='color:red;font-weight:bold;'"?>>停止中</label>
                        <?php
                        } else {
                        ?>
                        <input type='radio' name='OnOff' value='1' id='on'<?php if($OnOff==1)echo' checked'?>>
                            <label for='on'<?php if($OnOff==1)echo" style='color:lime;font-weight:bold;'"?>>稼動中</label>
                        <input type='radio' name='OnOff' value='0' id='off'<?php if($OnOff==0)echo' checked'?>>
                            <label for='off'<?php if($OnOff==0)echo" style='color:red;font-weight:bold;'"?>>停止中</label>
                        <?php
                        }
                        ?>
                        &nbsp;
                        <input type='hidden' name='on_change' value='照会'>
                        <input type='submit' name='on_change' value='変更'>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        </form>
                        -->
    <?php
    }
    } else {
    if ($showMenu=='List') {
    ?>
                        <!--
                        <B>
                        <?php echo date('Y年n月j日', strtotime($today)); ?>
                        冷温水機稼働状況
                        </B>
                        <form name='OnOff' action='<?php echo $menu->out_self(), '?', $model->get_htmlGETparm(), "&id={$uniq}"?>' method='get'>
                        <input type='radio' name='OnOff' value='' id='on'<?php if($OnOff==1)echo' checked'?><?php if($OnOff==0)echo' disabled'?>>
                            <label for='on'<?php if($OnOff==1)echo" style='color:lime;font-weight:bold;'"?>>稼動中</label>
                        <input type='radio' name='OnOff' value='' id='off'<?php if($OnOff==0)echo' checked'?><?php if($OnOff==1)echo' disabled'?>>
                            <label for='off'<?php if($OnOff==0)echo" style='color:red;font-weight:bold;'"?>>停止中</label>
                        <input type='hidden' name='on_change' value='照会'>
                        <!--
                        <input type='submit' name='on_change' value='照会'>
                        -->
                        </form>
    <?php
    }
    }
    ?>
                    </td>
                </tr>
            </table>
        </caption>
        <tr><td> <!-- ダミー -->
    <table class='winbox_field' bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <th class='winbox' width='20'>&nbsp;</th>
        <th class='winbox' width='30'>&nbsp;</th>
        <th class='winbox' width='320'>件名 及び 特記事項</th>
        <th class='winbox' nowrap>開始日時</th>
        <th class='winbox' nowrap>終了日時</th>
        <th class='winbox' width='70'>主催者</th>
        <th class='winbox' width='70'>出席者</th>
        <th class='winbox' nowrap>場　所</th>
        <?php
        if($showMenu=='List') {
        //if ($_SESSION['User_ID'] == '300144') {
        ?>
        <th class='winbox' width='70' nowrap>社用<BR>車</th>
        <?php
        //}
        }
        ?>
        <th class='winbox' width='70'>更新日</th>
    <?php if ($rows >= 1) { ?>
        <?php for ($r=0; $r<$rows; $r++) { ?>
            <?php if ($res[$r][8] == '有効') { ?>
            <tr>
            <?php } else { ?>
            <tr style='color:gray;'>
            <?php } ?>
            <td class='winbox' align='right' nowrap><?php echo $r + 1 + $model->get_offset()?></td>
            <td class='winbox' align='center' nowrap>
                <!--
                <a href='<?php echo $menu->out_self(), "?serial_no={$res[$r][0]}&year={$year}&month={$month}&day={$day}&showMenu=Edit&", $model->get_htmlGETparm(), "&id={$uniq}"?>'
                    style='text-decoration:none;'>編集
                </a>
                -->
                　
            </td>
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
            
            <?php
            if($showMenu=='List') {
            //if ($_SESSION['User_ID'] == '300144') {
            if ($res[$r][16]=='') {
                $res[$r][16] = '未使用';
            }
            ?>
            
            <td class='winbox' align='left'><?php echo $res[$r][16]?></td>
            <?php
            //}
            }
            ?>
            <!-- 更新日 (表示は更新日だが中身は変更日) -->
            <td class='winbox' align='center' onDblclick='alert("初回 登録日は\n\n[ <?php echo $res[$r][9]?> ]\n\nです。");'>
                <?php echo $res[$r][10]?>
            </td>
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
    <?php
    } else {    // 以下は、会議室別一覧の表示
    ?>
    テスト中<BR>
    <table class='list' bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <caption>
        </caption>
        <tr><td> <!-- ダミー -->
    <table class='winbox_field' bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <th class='winbox' nowrap>　</th>
    <?php
/*
    $rows = $model->getActiveRoomList($result);
    $res  = $result->get_array();
    $cnt  = 0;  // 使用する部屋数
    $s_hour = 8; $s_minute = 30;    // 開始( 8：30)就業時間
    $e_hour = 17; $e_minute = 15;   // 終了(17：15)就業時間

    // データ作成（行数：$r_idx）
    $r_idx = 1; // 初期値セット（0：は、空）
    for( $h=$s_hour; $h<=$e_hour; $h++ ) {
        if( $h==$s_hour ) $m=$s_minute; else $m=0;
        if( $h==$e_hour ) $max=$e_minute; else $max=59;
        for( ; $m<=$max; $m++ ) {
            if( ($m % 5) != 0 ) continue;
            $t = sprintf("%02d:%02d", $h, $m);
//            if( $m==0 || $m==30 )           // 30分単位
                $room_info[$r_idx][0] = $t; // 時間セット
//            else
//                $room_info[$r_idx][0] = ''; // 空セット
            $r_idx++;   // セット後、カウントアップ
        }
    }

    // データ作成（列数：$f_idx）
    $f_idx = 1; // 初期値セット（0：は、空）
    for( $r=0; $r<$rows; $r++ ){
        if( !strstr($res[$r][1], "会議室") && !strstr($res[$r][1], "応接室") ) continue;
        $room = str_replace("事務所棟", "", $res[$r][1]);
        echo "<th class='winbox' nowrap>{$room}</th>";
        $room_name[0][$cnt] = $res[$r][1];  // 使用部屋名(正式名称)
        $cnt++; // 使用部屋数
        $room_info[0][$f_idx] = $room;
        $f_idx++;
    }

    // 予定 あり なし の セット 処理 // 通常の表示データ取得して、部屋名ごとの使用時間をチェック
    // あり なし のテーブル作成してそれを表示する？
    // 1：件名 2：開始日時 3：終了日時 4：部屋
    $rows = $model->getViewList($result);
    $res  = $result->get_array();
    // 部屋数ループ 表示データループ 部屋と比較 開始と終了時間取得
    for($r=0; $r<$cnt; $r++) {
        for($c=0; $c<$rows; $c++) {
            if($room_name[0][$r] == $res[$c][4]) {
                echo substr($res[$c][2], 9, 5) . "～" . substr($res[$c][3], 9, 5) . "：" . $res[$c][1] . "<BR>";
                $s_h = substr($res[$c][2], 9, 2) * 60; $s_m = substr($res[$c][2], 12, 2);
                $e_h = substr($res[$c][3], 9, 2) * 60; $e_m = substr($res[$c][3], 12, 2);
                $s_t = ($s_h + $s_m - 510) / 5;
                $e_t = ($e_h + $e_m - 510) / 5;
                for( $n=$s_t; $n<$e_t; $n++) {
                    $room_info[$n+1][$r+1] = "×予定あり";
                }
            }
        }
    }
/**
    // データ作成（空へ〇をセット）
    for( $r=1; $r<$r_idx; $r++ ) {
        for( $f=1; $f<$f_idx; $f++ ) {
            if( empty($room_info[$r][$f]) ) $room_info[$r][$f] = "〇";    // 〇
        }
    }
/**
    // データ表示
    $nowtime = 0;
    for( $r=1; $r<$r_idx; $r++ ) {
        $w = sprintf("%04d%02d%02d%02d%02d00", $request->get('year'), $request->get('month'), $request->get('day'), substr($room_info[$r][0],0,2), substr($room_info[$r][0],3,2));
        if( date('YmdHis') > $w ) {
            echo "<tr class='winbox' style='background-color:#e6e6e6; color:DarkGray;'>";
        } else {
            if($nowtime == 0){
                $nowtime = 1;
                echo "<tr class='winbox' style='background-color:yellow; color:blue;'>";
            } else {
                echo "<tr class='winbox'>";
            }
        }
        $mi = substr($room_info[$r][0], 3, 2);
        if( $mi != "00" && $mi != "30" ) {
            if($nowtime == 1){
                $nowtime = 2;
            } else {
                $room_info[$r][0] = "　"; // 30分単位以外の時間は表示しない
//                $room_info[$r][0] = ""; // 30分単位以外の時間は表示しない
            }
        } else {
            if($nowtime == 1){
                $nowtime = 2;
            }
        }

        for( $f=0; $f<$f_idx; $f++ ) {
            if( $room_info[$r][$f] == "〇" ) {
                echo "<td class='winbox' align='center'>";
            } else {
                echo "<td class='winbox'>";
            }
            echo "{$room_info[$r][$f]}</td>";
        }

        echo "</tr>";
    }

    for( $f=0; $f<$f_idx; $f++ ) {
        echo "<th class='winbox' nowrap>　</th>";
    }
/**/
/**
    for( $h=$s_hour; $h<=$e_hour; $h++ ) {
        if( $h==$s_hour ) $m=$s_minute; else $m=0;
        if( $h==$e_hour ) $max=$e_minute; else $max=59;
        for( ; $m<=$max; $m++ ) {
            if( ($m % 5) != 0 ) continue;
//            $w = sprintf("02d%02d", $h, $m);
            $w = sprintf("%04d%02d%02d%02d%02d", $request->get('year'), $request->get('month'), $request->get('day'), $h, $m);
//            if( date('Hi') >= $w ) {
            if( date('YmdHi') >= $w ) {
                echo "<tr bgcolor='white' >";
            } else
                echo "<tr>";

            $t = sprintf("%02d:%02d", $h, $m);
            if( $m==0 || $m==30 )
                echo "<td>{$t}</td>";   // 30分単位で表示
            else
                echo "<td>　</td>";

            for( $r=0; $r<$cnt; $r++ ){
                echo "<td>　</td>";
            }
            echo "</tr>";
        }
    }
/**/
    ?>
    </table>
        </td></tr> <!-- ダミー -->
    </table>
    <?php
    }
    ?>
    </form>
</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
