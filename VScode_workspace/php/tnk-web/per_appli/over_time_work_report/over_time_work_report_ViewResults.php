<?php
////////////////////////////////////////////////////////////////////////////////
// 定時間外作業申告（照会）検索結果表示                                       //
//                                                    MVC View 部 リスト表示  //
// Copyright (C) 2021-2021 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2021/10/20 Created over_time_work_report_ViewResults.php                   //
//            社外からの出向受け入れ者は、栃木の社員コードを逐次追加          //
// 2021/11/01 Release.                                                        //
// 2021/12/17 部課長コメントがある場合、ツールチップにて表示を追加            //
// 2022/03/14 [時間外申請]の表示を追加                                        //
////////////////////////////////////////////////////////////////////////////////
$menu->out_html_header();
$counter = 0;       // 表示件数カウンター（初期値：0）
$date_view = false; // 日付表示フラグ（初期値：false）
$deploy_bak= "";
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

<link rel='stylesheet' href='../per_appli.css' type='text/css' media='screen'>
<script type='text/javascript' language='JavaScript' src='over_time_work_report.js'></script>

</head>
<body onLoad='InitResults();'>
<center>

<?= $menu->out_title_border() ?>

<form name='form_results' method='post' action='<?php echo $menu->out_self() ?>' onSubmit='return true'>
<!-- TEST Start.-->
    <input type='hidden' name='login_uid' value="<?php echo $login_uid; ?>">
<!-- TEST End. -->
    <input type='hidden' name='showMenu' value='Quiry' id='id_showMenu' >
    <input type='hidden' name='ddlist_v_type' value='<?php echo $request->get("ddlist_v_type"); ?>'>
    <input type='hidden' name='days_radio' value='<?php echo $request->get("days_radio"); ?>'>
    <input type='hidden' name='ddlist_year' value='<?php echo $request->get("ddlist_year"); ?>'>
    <input type='hidden' name='ddlist_month' value='<?php echo $request->get("ddlist_month"); ?>'>
    <input type='hidden' name='ddlist_day' value='<?php echo $request->get("ddlist_day"); ?>'>
    <input type='hidden' name='ddlist_year2' value='<?php echo $request->get("ddlist_year2"); ?>'>
    <input type='hidden' name='ddlist_month2' value='<?php echo $request->get("ddlist_month2"); ?>'>
    <input type='hidden' name='ddlist_day2' value='<?php echo $request->get("ddlist_day2"); ?>'>
    <input type='hidden' name='ddlist_bumon' value='<?php echo $request->get("ddlist_bumon"); ?>'>
    <input type='hidden' name='s_no' value='<?php echo $request->get("s_no"); ?>'>
    <input type='hidden' name='mode_radio' value='<?php echo $request->get("mode_radio"); ?>'>
    <input type='hidden' name='err_check0' value='<?php echo $request->get("err_check0"); ?>'>
    <input type='hidden' name='err_check1' value='<?php echo $request->get("err_check1"); ?>'>
    <input type='hidden' name='err_check2' value='<?php echo $request->get("err_check2"); ?>'>
    <input type='hidden' name='err_check3' value='<?php echo $request->get("err_check3"); ?>'>
    <input type='hidden' name='rows' id='id_rows' value='<?php echo $rows; ?>'>
    
    <div class='pt10'><BR>チェックを外すと非表示：
    <input type="checkbox" id="date_check"   name="date_check" onclick="checkbox_cell(this,'date_display')" <?php if($request->get("date_check")) echo " checked"; ?>>作業日
    <input type="checkbox" id="deploy_check" name="deploy_check" onclick="checkbox_cell(this,'deploy_display')" <?php if($request->get("deploy_check")) echo " checked"; ?>>部署
<!--
    <input type="checkbox" id="name_check"   name="name_check" onclick="checkbox_cell(this,'name_display')" <?php if($request->get("name_check")) echo " checked"; ?>>名前
-->
    <input type="checkbox" id="z_contents_check" name="z_contents_check" onclick="checkbox_cell(this,'z_contents_display')" <?php if($request->get("z_contents_check")) echo " checked"; ?>>残業実施理由
    <input type="checkbox" id="z_state_check"    name="z_state_check" onclick="checkbox_cell(this,'z_state_display')" <?php if($request->get("z_state_check")) echo " checked"; ?>>状況（事前）
    <input type="checkbox" id="j_contents_check" name="j_contents_check" onclick="checkbox_cell(this,'j_contents_display')" <?php if($request->get("j_contents_check")) echo " checked"; ?>>実施業務内容
    <input type="checkbox" id="j_state_check"    name="j_state_check" onclick="checkbox_cell(this,'j_state_display')" <?php if($request->get("j_state_check")) echo " checked"; ?>>状況（結果）
    <input type="checkbox" id="remarks_check"    name="remarks_check" onclick="checkbox_cell(this,'remarks_display')" <?php if($request->get("remarks_check")) echo " checked"; ?>>備考
    </div>
    
    <BR>
    <div id='id_title'>検索条件に一致する定時間外作業申告はありません。</div>
    
    <table class='pt10' border="1" cellspacing="0">
    <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table width='100%' class='pt10' bgcolor='#D8D8D8' align='center' border='1' cellspacing='0' cellpadding='3'>

<!-- 見出し行 -->
        <tr style='background-color:yellow; color:blue;'>
            <th nowrap align='center' id='date_display'>作業日</th>
            <th nowrap align='center' id='deploy_display'>部署名</th>
            <th nowrap align='center' id='name_display'>氏　名</th>
            <th nowrap align='center'>予定<BR>時間</th>
            <th nowrap align='center' id='z_contents_display'>残業実施<BR>理由</th>
            <th nowrap align='center' id='z_state_display'>状況<BR>(事前)</th>
            <th nowrap align='center'>出勤<BR>時間</th>
            <th nowrap align='center'>退勤<BR>時間</th>
            <?php if($v_type==0) { ?>
            <th nowrap align='center'>早出<BR>申請</th>
            <?php } else { ?>
            <th nowrap align='center'>延長<BR>申請</th>
            <th nowrap align='center'>時間外<BR>申請</th>
            <?php } ?>
            <th nowrap align='center' id='j_time_display'>実際作業<BR>時間</th>
            <th nowrap align='center'>端数<BR>(分)</th>
            <th nowrap align='center' id='j_contents_display'>実施業務<BR>内容</th>
            <th nowrap align='center' id='j_state_display'>状況<BR>(結果)</th>
            <th nowrap align='center' id='remarks_display'>備考</th>
<?php if( getCheckAuthority(66)) { ?> <!-- 66:修正可能（総務課）-->
<!--
            <th nowrap align='center'>修正</th>
-->
<?php } ?>
    <!-- 予備 -->
            <th nowrap align='center'>　</th>
        </tr>

        <?php for ( $r=0; $r<$rows; $r++) { ?>
            <?php
            $s_t_err = $e_t_err = $s_t_a_err = $s_t_b_err = $j_t_b_err = $j_t_a_err = false; // エラーフラグ初期化
            if( $r != 0 && $res[$r-1][0] == $res[$r][0] && $date_view ) {
                $date = "　";                                       // 作業日
            } else {
                $date = $model->getTargetDateDay($res[$r][0], 'no');// 作業日
                $date_view = false; // 日付表示フラグリセット
            }
/**
            if( $date == "　" && $res[$r-1][1] == $res[$r][1] ) {
                $deploy = "　";         // 部署名
            } else {
                $deploy = $res[$r][1];  // 部署名
            }
/**/
            $uid    = $res[$r][3]; // 社員番号
            $y_time = "<font style='background-color:red; color:white;'>事 後 申 請</font>";    // 予定時間
            if( $res[$r][4] ) {
                $y_time = "{$res[$r][4]}:{$res[$r][5]}～{$res[$r][6]}:{$res[$r][7]}";   // 予定時間
            }
            $y_cont = $res[$r][8];  // 残業実施理由
            if( ! $y_cont ) $y_cont = "　";
            $y_stat = $model->getAdmitStatus($res[$r][9], $res[$r][10]);    // 事前申請 承認 状態
            $s_time = $model->getWorkingStrTime($uid, $res[$r][0]);         // 出勤時間
            $e_time = $model->getWorkingEndTime($uid, $res[$r][0]);         // 退勤時間

            $fraction = 0;// 端数
            if($v_type==0) { // 早出
                $early_time = $model->getWorkingEarlyTime($uid, $res[$r][0]) + 0; // 早出申請
                $early_view_time = "-.-";
                if( $early_time > 0 ) {
                    $early_view_time = $early_time/60;
                    $j_str = strtotime("{$res[$r][16]}:{$res[$r][17]} +{$early_time} minute");
                    $j_end = strtotime("{$res[$r][18]}:{$res[$r][19]}");
                    if( $j_str != "" && $j_end != "" ) {
                        if( $j_str <= $j_end ) {
                            $fraction = date('i', ($j_end-$j_str)) + 0; // プラス
                        } else {
                            $fraction -= date('i', ($j_str-$j_end)) + 0; // マイナス
                        }
                    }
                }
            } else { // 通常
                $extend_time = $model->getWorkingExtendTime($uid, $res[$r][0]) + 0; // 延長申請
                $extend_view_time = "-.-";
                if( $extend_time > 0 ) $extend_view_time = $extend_time/60;
                $j_str = strtotime("{$res[$r][16]}:{$res[$r][17]}");
                $j_end = strtotime("{$res[$r][18]}:{$res[$r][19]}");
                if( $e_time == "----" || $e_time == "0000" || $e_time < "1800" || ($j_str==$j_end) ) {
                    $o_time = "-.-";
                } else {
                    $o_time = $model->getWorkingOverTime($uid, $res[$r][0]); // 時間外申請
                    $o_time = $o_time/60;
                }
                if( $o_time>0 || $extend_time>0 ) {
                    if( $o_time>0 ) {
                        $tmp = $o_time*60;
                        if( $j_str <= strtotime("17:30") ) {
                            $j_str = "17:30";
                        } else {
                            $j_str = "18:15";
                        }
                        $j_str = strtotime("{$j_str} +{$tmp} minute");
                    } else if( $extend_time>0 ) {
                        if( $j_end > strtotime("17:30") ) $extend_time += 15; // 休憩分加算
                        $j_str = strtotime("{$res[$r][16]}:{$res[$r][17]} +{$extend_time} minute");
//                        echo date('Y-m-d H:i',$j_str) . " / " . date('Y-m-d H:i',$j_end);
//                        echo $j_str . " / " . $j_end;
//                        echo date('i', ($j_str-$j_end)) + 0;
                    }
//                    echo date('Y-m-d H:i',$j_str);
                    if( $j_str != "" && $j_end != "" ) {
                        if( $j_str <= $j_end ) {
                            $fraction = date('i', ($j_end-$j_str)) + 0; // プラス
                        } else {
                            $fraction -= date('i', ($j_str-$j_end)) + 0; // マイナス
                        }
                    }
                } else if( $o_time==0 ) { // 時間外申請がないとき
                    $fraction = ($j_end-$j_str)/60 + 0; // プラス
                }
            }
/**
if( $uid=='300667' ) { $s_time = '0800';
//$res[$r][16]=$res[$r][17]=$res[$r][18]=$res[$r][19]='01';
}
/**
if( $uid=='300667' ) { $e_time = '1725'; } else 
if( $uid=='300551' ) { $e_time = '1833'; } else 
if( $uid=='300632' ) { $e_time = '1925'; }
/**/
            $j_time = "　";         // 実際作業時間
            if( $res[$r][16] ) {    // 結果報告の時間入力あり
                $j_time    = "{$res[$r][16]}:{$res[$r][17]}～{$res[$r][18]}:{$res[$r][19]}";   // 実際作業時間
                $early_dt  = new DateTime("0830");// 早出時間
                if($v_type==0) {
                    $work_time = $res[$r][16] . $res[$r][17];
                } else {
                    $work_time = $res[$r][18] . $res[$r][19];
                }
                $work_e_dt = new DateTime("$work_time");// 実際作業時間（終了）
                if( $early_dt >= $work_e_dt ) { // 早出処理
                    if( $s_time != "0000" && $s_time != "----" ) {  // 出勤時間 データあり
                        $str_dt    = new DateTime("$s_time");// 出勤時間
                        $work_time = $res[$r][16] . $res[$r][17];
                        $work_s_dt = new DateTime("$work_time");// 実際作業時間（開始）
                        if( $str_dt > $work_e_dt ) {// 実際作業時間（終了）より後に出勤
                            $j_time = "<font style='background-color:red; color:white;'>$j_time</font>";// 結果報告エラー
                            $s_t_a_err = true;
                        } else {
                            if( $str_dt > $work_s_dt) {// 実際作業時間（開始）より後に出勤
                                $j_time = "<font style='background-color:yellow; color:blue;'>$j_time</font>";// 結果報告エラー
                                $s_t_b_err = true;
                            }
                        }
                    }
                } else { // 通常残業処理
                    if( $e_time != "0000" && $e_time != "----" ) {  // 退勤時間 データあり
                        $end_dt = new DateTime("$e_time");  // 退勤時間
                        if( $end_dt < $work_e_dt) {        // 終了前に退勤
                            $j_time = "<font style='background-color:yellow; color:blue;'>$j_time</font>";  // 結果報告エラー
                            $j_t_b_err = true;
                        } else {
                            if( $work_time=="1715" ) $work_e_dt->modify('15 minute');// 17:15～17:30 は休憩の為スキップ
                            $dummy = substr($res[$r][0], 0,4) . substr($res[$r][0], 5,2) . substr($res[$r][0], 8,2);
                            if($dummy >= "20220411") {
                                $work_e_dt->modify('10 minute');// 実際作業時間（終了10分後）
                                if( $end_dt > $work_e_dt ) {   // 終了10分後に退勤
                                    $j_time = "<font style='background-color:red; color:white;'>$j_time</font>";// 結果報告エラー
                                    $j_t_a_err = true;
                                }
                            } else {
                                $work_e_dt->modify('30 minute');// 実際作業時間（終了30分後）
                                if( $end_dt >= $work_e_dt ) {   // 終了30分後に退勤
                                    $j_time = "<font style='background-color:red; color:white;'>$j_time</font>";// 結果報告エラー
                                    $j_t_a_err = true;
                                }
                            }
                        }
                    }
                }
            }
            $s_time = substr_replace($s_time, ":", 2, 0);   // 出勤時間
            if($s_time == "00:00" && $res[$r][0] != date('Y-m-d') ) {
                $s_time = "<font style='background-color:yellow; color:blue;'>$s_time</font>";
                $s_t_err = true;
            }
            $e_time = substr_replace($e_time, ":", 2, 0);   // 退勤時間
            if($e_time == "00:00" && $res[$r][0] != date('Y-m-d') ) {
                $e_time = "<font style='background-color:yellow; color:blue;'>$e_time</font>";
                $e_t_err = true;
            }
            $j_cont = $res[$r][20];  // 実施業務内容
            if( $res[$r][16] && $res[$r][16]==$res[$r][18] && $res[$r][17]==$res[$r][19] ) {
                $j_time = "<font style='background-color:red; color:white;'>残業 キャンセル</font>"; // 実際作業時間
//                $j_time = "{$res[$r][16]}:{$res[$r][17]}～{$res[$r][18]}:{$res[$r][19]}";   // 実際作業時間
//                $j_cont = "<font style='background-color:red; color:white;'>残業 キャンセル</font>";
                $j_t_b_err = $j_t_a_err = $s_t_b_err = $s_t_a_err = false;  // エラー解除
                if( $s_t_err && $e_t_err ) $s_t_err = $e_t_err = false;     // エラー解除
            }
            if( ! $j_cont ) $j_cont = "　";
            $j_rema = $res[$r][21];  // 備考
            if( ! $j_rema ) $j_rema = "-----";
            $j_stat = $model->getAdmitStatus($res[$r][22], $res[$r][23]); // 実績 承認 状態
            
            if( $request->get("err_check0") || $request->get("err_check1") || $request->get("err_check2") || $request->get("err_check3") ) {
                if( $request->get("err_check1") && ($e_t_err || $s_t_err) ) { // 退勤 or 出勤 してない
                    $counter++;   // ok
                } else {
                    if( $request->get("err_check2") && ($j_t_b_err || $s_t_b_err) ) {   // 報告時間前に退勤
                        $counter++;   // ok
                    } else {
                        if( $request->get("err_check3") && ($j_t_a_err || $s_t_a_err) ) {   // 30分越え
                            $counter++;   // ok
                        } else {
                            if( $request->get("err_check0") && !$s_t_err && !$e_t_err && !$j_t_b_err && !$j_t_a_err && !$s_t_b_err && !$s_t_a_err) {
                                $counter++; // ok
                            } else {
                                continue;   // 表示しない
                            }
                        }
                    }
                }
            } else {
                $counter++;   // ok
            }
            $date_view = true;  // 日付表示フラグON
            
            // 社外からの出向受け入れ者 逐次追加
            // 020826:品質保証課 高木
            $view_style="";
            if( $uid == '020826' ) {
                $view_style="style='background-color:RoyalBlue; color:White;'";
            }
            
            // 部課長コメントがある場合、ツールチップにて表示
            $comment = "";
            if( $res[$r][14] ) $comment  = "課長コメント：\n　{$res[$r][14]}";
            if( $comment )     $comment .= "\n";
            if( $res[$r][15] ) $comment .= "部長コメント：\n　{$res[$r][15]}";
            
            // 表示部署判別
            if( $date == "　" && $deploy_bak == $res[$r][1] ) {
                $deploy = "　";         // 部署名
            } else {
                $deploy = $res[$r][1];  // 部署名
                $deploy_bak = $deploy;
            }
            ?>
            <tr <?php echo "$view_style"; ?> title='<?php echo $comment; ?>'>
<!-- 作業日 -->
                <td nowrap><?php echo $date; ?></td>
                <input type='hidden' name='date<?php echo $r; ?>' value='<?php echo $res[$r][0]; ?>'>
<!-- 部署名 -->
                <td nowrap><?php echo $deploy; ?></td>
<!-- 氏  名 -->
                <td nowrap><?php echo $model->getName($uid); ?></td>
                <input type='hidden' name='uid<?php echo $r; ?>' value='<?php echo $uid; ?>'>
<!-- 予定時間 -->
                <td nowrap><?php echo $y_time; ?></td>
<!-- 残業実施理由 -->
                <td nowrap><?php echo $y_cont; ?></td>
<!-- 事前申請 状態 -->
                <td nowrap align='center'><?php echo $y_stat; ?></td>
<!-- 出勤時間 -->
                <td nowrap align='center'><?php echo $s_time; ?></td>
<!-- 退勤時間 -->
                <td nowrap align='center'><?php echo $e_time; ?></td>
<?php if($v_type==0) { ?>
<!-- 早出時間 -->
                <td nowrap align='center' <?php if($early_time=="0") echo "style='color: DarkGray;'"; ?>><?php if($early_time>0) echo number_format($early_view_time, 1); else echo "-.-"; ?> h</td>
<?php } else { ?>
<!-- 延長時間 -->
                <td nowrap align='center' <?php if($extend_time=="0") echo "style='color: DarkGray;'"; ?>><?php if($extend_time>0) echo number_format($extend_view_time, 1); else echo "-.-"; ?> h</td>
<!-- 時間外申請 -->
                <?php if($o_time=="0") $style="background-color:Yellow; color:Blue;"; else if($o_time=="-.-") $style="color: DarkGray;"; else $style=""; ?>
                <td nowrap align='center' style='<?php echo $style; ?>'><?php if($o_time>0) echo number_format($o_time, 1); else echo $o_time; ?> h</td>
<?php } ?>
<!-- 実際作業時間 -->
                <td nowrap align='center'><?php echo $j_time; ?></td>
<!-- 端数 -->
                <?php if($fraction=="0") $style="color: DarkGray;"; else if($fraction>"0") $style="background-color:Yellow; color:Blue;"; else $style="background-color:red; color:white;"; ?>
                <td nowrap align='center' style='<?php echo $style; ?>'><?php echo $fraction; ?> m</td>
<!-- 実施業務内容 -->
                <td nowrap><?php echo $j_cont; ?></td>
<!-- 残業結果報告 状態 -->
                <td nowrap align='center'><?php echo $j_stat; ?></td>
<!-- 備考 -->
                <td nowrap><?php echo $j_rema; ?></td>
<!-- 修正 -->
<?php if( getCheckAuthority(66)) { ?> <!-- 66:修正可能（総務課）-->
<!--
                <td nowrap><button class='pt9' onClick='return false;' <?php if($j_stat!="承認 済") echo " disabled"; ?>>修正</button></td>
-->
<?php } ?>
<!-- 予備 -->
                <td nowrap>　</td>
            </tr>
        <?php } /* for() End. */ ?>
        
        <script>
            var obj = document.getElementById('id_title');
            if( <?php echo $counter; ?> > 0 ) {
                obj.innerHTML="検索条件に一致する定時間外作業申告があります。<?php echo '【 ' . $counter . ' 件】'; ?><BR><font size='2' style='color:red;'>※部課長コメントがあれば、カーソルを置いておくことで表示されます。</font>";
            }
        </script>
        
        </table>
        </td></tr>
    </table> <!----------------- ダミーEnd --------------------->
    <br>
    <input type="submit" value="検索条件へ戻る" name="submit">　
    <button id='j_time_edit_str' onClick='return TimeEditStr();'>実際作業時間の修正</button>　
    <input type="submit" id='j_time_edit_end' value="修正を確定" onClick='return TimeEditEnd();' disabled>
    <input type='hidden' id='id_time_edit' name='time_edit' value=''>
    <br>　

</form>

</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
