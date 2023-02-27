<?php
////////////////////////////////////////////////////////////////////////////////
// 定時間外作業申告（照会）検索結果表示                                       //
//                                                    MVC View 部 リスト表示  //
// Copyright (C) 2021-2021 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2021/10/20 Created over_time_work_report_ViewResults.php                   //
//            社外からの出向受け入れ者は、栃木の社員コードを逐次追加          //
// 2021/11/01 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////
$menu->out_html_header();
$counter = 0;       // 表示件数カウンター（初期値：0）
$date_view = false; // 日付表示フラグ（初期値：false）
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<?php echo $menu->out_jsBaseClass() ?>

<link rel='stylesheet' href='../per_appli.css' type='text/css' media='screen'>
<script type='text/javascript' language='JavaScript' src='over_time_work_report.js'></script>

</head>
<body>
<center>

<?= $menu->out_title_border() ?>

<form name='form_results' method='post' action='<?php echo $menu->out_self() ?>' onSubmit='return true'>
<!-- TEST Start.-->
    <input type='hidden' name='login_uid' value="<?php echo $login_uid; ?>">
<!-- TEST End. -->
    <input type='hidden' name='showMenu' value='Quiry'>
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
    
    <BR>
    <div id='id_title'>検索条件に一致する定時間外作業申告はありません。</div>
    
    <table class='pt10' border="1" cellspacing="0">
    <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table width='100%' class='pt10' bgcolor='#D8D8D8' align='center' border='1' cellspacing='0' cellpadding='3'>

<!-- 見出し行 -->
        <tr style='background-color:yellow; color:blue;'>
            <td nowrap align='center'>作業日</td>
            <td nowrap align='center'>部署名</td>
            <td nowrap align='center'>氏　名</td>
            <td nowrap align='center'>予定時間</td>
            <td nowrap align='center'>残業実施理由</td>
            <td nowrap align='center'>状況(事前)</td>
            <td nowrap align='center'>出勤時間</td>
            <td nowrap align='center'>退勤時間</td>
            <td nowrap align='center'>実際作業時間</td>
            <td nowrap align='center'>実施業務内容</td>
            <td nowrap align='center'>状況(結果)</td>
<!-- 備考 --
            <td align='center'>備考</td>
<!--  -->
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
            if( $date == "　" && $res[$r-1][1] == $res[$r][1] ) {
                $deploy = "　";         // 部署名
            } else {
                $deploy = $res[$r][1];  // 部署名
            }
            $uid    = $res[$r][3]; // 社員番号
            $y_time = "<font style='background-color:red; color:white;'>事 後 申 請</font>";    // 予定時間
            if( $res[$r][4] ) {
                $y_time = "{$res[$r][4]}:{$res[$r][5]}〜{$res[$r][6]}:{$res[$r][7]}";   // 予定時間
            }
            $y_cont = $res[$r][8];  // 残業実施理由
            if( ! $y_cont ) $y_cont = "　";
            $y_stat = $model->getAdmitStatus($res[$r][9], $res[$r][10]);    // 事前申請 承認 状態
            $s_time = $model->getWorkingStrTime($uid, $res[$r][0]);         // 出勤時間
            $e_time = $model->getWorkingEndTime($uid, $res[$r][0]);         // 退勤時間
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
                $j_time    = "{$res[$r][16]}:{$res[$r][17]}〜{$res[$r][18]}:{$res[$r][19]}";   // 実際作業時間
                $early_dt  = new DateTime("0830");// 早出時間
                $work_time = $res[$r][18] . $res[$r][19];
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
//                        $work_e_dt->modify('-30 minute');   // 実際作業時間（終了30分前）
//                        if( $end_dt <= $work_e_dt) {        // 終了30分前に退勤
                        if( $end_dt < $work_e_dt) {        // 終了前に退勤
                            $j_time = "<font style='background-color:yellow; color:blue;'>$j_time</font>";  // 結果報告エラー
                            $j_t_b_err = true;
                        } else {
//                            $work_e_dt->modify('60 minute');// 実際作業時間（終了30分後）
                            $work_e_dt->modify('30 minute');// 実際作業時間（終了30分後）
                            if( $end_dt >= $work_e_dt ) {   // 終了30分後に退勤
                                $j_time = "<font style='background-color:red; color:white;'>$j_time</font>";// 結果報告エラー
                                $j_t_a_err = true;
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
//                $j_time = "{$res[$r][16]}:{$res[$r][17]}〜{$res[$r][18]}:{$res[$r][19]}";   // 実際作業時間
//                $j_cont = "<font style='background-color:red; color:white;'>残業 キャンセル</font>";
                $j_t_b_err = $j_t_a_err = $s_t_b_err = $s_t_a_err = false;  // エラー解除
                if( $s_t_err && $e_t_err ) $s_t_err = $e_t_err = false;     // エラー解除
            }
            if( ! $j_cont ) $j_cont = "　";
            $j_rema = $res[$r][21];  // 備考
            if( ! $j_rema ) $j_rema = "　";
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
            ?>
            <tr <?php echo "$view_style"; ?> >
<!-- 作業日 -->
                <td nowrap><?php echo $date; ?></td>
<!-- 部署名 -->
                <td nowrap><?php echo $deploy; ?></td>
<!-- 氏  名 -->
                <td nowrap><?php echo $model->getName($uid); ?></td>
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
<!-- 実際作業時間 -->
                <td nowrap align='center'><?php echo $j_time; ?></td>
<!-- 実施業務内容 -->
                <td nowrap><?php echo $j_cont; ?></td>
<!-- 残業結果報告 状態 -->
                <td nowrap align='center'><?php echo $j_stat; ?></td>
<!-- 備考 --
                <td nowrap><?php echo $j_rema; ?></td>
<!--  -->
            </tr>
        <?php } /* for() End. */ ?>
        
        <script>
            var obj = document.getElementById('id_title');
            if( <?php echo $counter; ?> > 0 ) {
                obj.innerHTML="検索条件に一致する定時間外作業申告があります。<?php echo '【 ' . $counter . ' 件】'; ?>";
            }
        </script>
        
        </table>
        </td></tr>
    </table> <!----------------- ダミーEnd --------------------->
    <br>
    <input type="submit" value="検索条件へ戻る" name="submit">
    <br>　

</form>

</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
