<?php
////////////////////////////////////////////////////////////////////////////////
// 定時間外作業申告（承認）                                                   //
//                                                    MVC View 部 リスト表示  //
// Copyright (C) 2021-2021 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2021/10/20 Created over_time_work_report_ViewJudge.php                     //
// 2021/11/01 Release.                                                        //
// 2021/12/17 退勤時間の表示追加                                              //
////////////////////////////////////////////////////////////////////////////////
$menu->out_html_header();
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

<link rel='stylesheet' href='../per_appli.css' type='text/css' media='screen'>
<script type='text/javascript' language='JavaScript' src='over_time_work_report.js'></script>

</head>

<body onLoad=''>

<center>
    <?= $menu->out_title_border() ?>

<!-- ＰＤＦファイルを開く -->
    <div class='pt10' align='center'>
    <BR>※操作方法が分からない場合、<a href="download_file.php/定時間外作業申告_承認_マニュアル_New.pdf">定時間外作業申告（承認）マニュアル</a> を参考にして下さい。<BR>
    </div>
<!-- TEST Start.-->
    <?php if($debug) { ?>
    <div class='pt9' align='left'><font color='red'>※※※ ここから、テストの為表示  ※※※</font></div>
    ※【テスト　承認者　切替】 工場長：
    <input type='button' style='<?php if($login_uid=="017361") echo "background-color:yellow"; ?>' value='017361' onClick='CangeUID(this.value, "form_judge");'>　
    副工場長：
    <input type='button' style='<?php if($login_uid=="012394") echo "background-color:yellow"; ?>' value='012394' onClick='CangeUID(this.value, "form_judge");'>　
    部長：
    <input type='button' style='<?php if($login_uid=="017850") echo "background-color:yellow"; ?>' value='017850' onClick='CangeUID(this.value, "form_judge");'>　
    <input type='button' style='<?php if($login_uid=="012980") echo "background-color:yellow"; ?>' value='012980' onClick='CangeUID(this.value, "form_judge");'>　
    <input type='button' style='<?php if($login_uid=="016713") echo "background-color:yellow"; ?>' value='016713' onClick='CangeUID(this.value, "form_judge");'>
    <BR><BR>
    課長：
    <input type='button' style='<?php if($login_uid=="300055") echo "background-color:yellow"; ?>' value='300055' onClick='CangeUID(this.value, "form_judge");'>　
    <input type='button' style='<?php if($login_uid=="017370") echo "background-color:yellow"; ?>' value='017370' onClick='CangeUID(this.value, "form_judge");'>　
    <input type='button' style='<?php if($login_uid=="300098") echo "background-color:yellow"; ?>' value='300098' onClick='CangeUID(this.value, "form_judge");'>　
    <input type='button' style='<?php if($login_uid=="014524") echo "background-color:yellow"; ?>' value='014524' onClick='CangeUID(this.value, "form_judge");'>　
    <input type='button' style='<?php if($login_uid=="018040") echo "background-color:yellow"; ?>' value='018040' onClick='CangeUID(this.value, "form_judge");'>　
    <input type='button' style='<?php if($login_uid=="015202") echo "background-color:yellow"; ?>' value='015202' onClick='CangeUID(this.value, "form_judge");'>　
    <input type='button' style='<?php if($login_uid=="016080") echo "background-color:yellow"; ?>' value='016080' onClick='CangeUID(this.value, "form_judge");'>　
    <input type='button' style='<?php if($login_uid=="017507") echo "background-color:yellow"; ?>' value='017507' onClick='CangeUID(this.value, "form_judge");'>　
    <input type='button' style='<?php if($login_uid=="017728") echo "background-color:yellow"; ?>' value='017728' onClick='CangeUID(this.value, "form_judge");'>　
    <BR><div class='pt9' align='left'><font color='red'>※※※ ここまで、テストの為表示  ※※※</font></div>
    <?php } ?>
<!-- TEST End. -->
    <BR>
<form name='form_judge' method='post' action="<?php echo $menu->out_self() . '?showMenu=Judge' ?>" onSubmit='return ;'>
<!-- TEST Start.-->
    <input type='hidden' name='login_uid' value="<?php echo $login_uid; ?>">
<!-- TEST End. -->
    <input type='hidden' name='admit' id='id_admit' value=''>

    <?php if($v_early) { ?>
    <?php } else { ?>
    <table class='pt10' bgcolor='#D8D8D8' border="1" cellspacing="0">
    <tr><td> <!----------- ダミー(デザイン用) ------------>
    <div align='center'>
        ※注意）延長及び残業してない人は、「<font style='color:red'>残業 キャンセル</font>」表示になっていること。<BR>
        なっていない場合、定時間外作業申告（入力）で残業結果報告の<BR>
        延長及び残業なしに<font style='color:red'>チェック</font>を入れ[登録]するよう指導すること。<BR><BR>
        ※※※ 時間集計の際、異常となるため ※※※
    </div>
    </td></tr> <!----------- ダミー(デザイン用) ------------>
    </table>
    <BR>
    <?php } ?>

    <table class='pt10' border="1" cellspacing="0">
    <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table width='100%' class='winbox_field' bgcolor='#D8D8D8' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr> <!-- キャプション -->
                <td nowrap class='winbox' style='background-color:yellow; color:blue;' colspan='1' align='center'>
                    <div class='caption_font'>事 前 申 請</div>
                </td>
                <td nowrap class='winbox' style='background-color:yellow; color:blue;' colspan='1' align='center'>
                    <div class='caption_font'>残業結果報告</div>
                </td>
            </tr>

            <tr align='center'> <!-- 選択項目 -->
                <td nowrap>
                    <input type='radio' name='select_radio' id='1' <?php if($select==1) echo " checked"; ?> onClick='AdmitDispSwitch();' value='1'><label for='1'>未承認</label>　
<!-- -->
                    <?php if( ($pos_no == 3) && $absence_ka && $absence_bu ) { ?>
                    <input type='radio' name='select_radio' id='2' <?php if($select==2) echo " checked"; ?> onClick='AdmitDispSwitch();' value='2'><label for='2'>課長・部長不在未承認</label>
                    <?php } else if( ($pos_no == 2) && $absence_ka ) { ?>
                    <input type='radio' name='select_radio' id='2' <?php if($select==2) echo " checked"; ?> onClick='AdmitDispSwitch();' value='2'><label for='2'>課長不在未承認</label>
                    <?php } else if( $absence_bu ) { ?>
                    <input type='radio' name='select_radio' id='2' <?php if($select==2) echo " checked"; ?> onClick='AdmitDispSwitch();' value='2'><label for='2'>部長不在未承認</label>
                    <?php } ?>
<!-- -->
                </td>
<!-- --
                <td>
                    <input type='radio' name='select_radio' id='2' <?php if($select==2) echo " checked"; ?> onClick='AdmitDispSwitch();' value='2'><label for='2'>前任者未承認</label>
                </td>
<!-- -->
                <td nowrap>
                    <input type='radio' name='select_radio' id='3' <?php if($select==3) echo " checked"; ?> onClick='AdmitDispSwitch();' value='3'><label for='3'>未承認</label><BR>
                </td>
<!-- --
                <td>
                    <input type='radio' name='select_radio' id='4' <?php if($select==4) echo " checked"; ?> onClick='AdmitDispSwitch();' value='4'><label for='4'>課長未承認</label>
                </td>
<!-- -->
            </tr>

            <tr align='center'> <!-- 早出 or 通常・休出 -->
                <td colspan='2'>
                    【
                    <select name='ddlist_v_type' onChange='AdmitDispSwitch();'>
                        <option value='0' <?php echo $v_early; ?>>早出</option>
                        <option value='1' <?php echo $v_normal; ?>>通常・休出</option>
                    </select>
                    】
                </td>
            </tr>

        </table>
    </td></tr>
    </table> <!----------------- ダミーEnd --------------------->

<!-- 各（日付／部署）時間外作業申告を表示する -->
    <input type='hidden' name='select' id='id_select' value='<?php echo $select;?>'>
    <input type='hidden' name='column' id='id_column' value='<?php echo $column;?>'>
    <input type='hidden' name='posts' id='id_posts' value='<?php echo $pos_na;?>'>
    <input type='hidden' name='rows_max' id='id_rows_max' value='<?php echo $rows;?>'>

    <?php for($i=0; $i<$rows; $i++) { ?>
        <?php
        $date   = $res[$i][0];  // 作業日
        $deploy = $res[$i][1];  // 部署名
        $now    = date('Ymd');  // 今の年月日
        $c_radio = "";
        $c_label = "";
        if( $pos_no > 1 ) { // 部長、工場長の場合 不在者チェック
            switch ($pos_no) {
                case 3:   // 工場長なら部長（課長も含む）の出勤確認
                    $absence_bu = $model->IsAbsence($now, $model->getButyouUID($deploy));
                case 2:   // 部長なら課長の出勤確認
                    $absence_ka = $model->IsAbsence($now, $model->getKatyouUID($deploy));
                    break;
            }
        }
        $where4 = "date='$date' AND deploy='$deploy'";
        // ログインユーザーの未承認リストを取得
        // date='xxxx-xx-xx' AND deploy='xxx課' AND xx_ad_xx='m' ...
//        $where5 = $where4 . " AND " . $where . " AND " . "(yo_ad_rt!='-1' OR yo_ad_rt IS NULL)";
        $where5 = $where4 . " AND " . $where;
        if( ($rows_2 = $model->GetReport($where5, $res_2)) <=0 ) continue;

        /* 承認情報作成（未承認者のルート） -------------------------------> */
        $def_flag = '----';
        $ad_info  = array($def_flag, $def_flag, $def_flag, $def_flag, $def_flag, $def_flag);
        
        for( $t=0; $t<$rows_2; $t++ ) {
            if( $ad_info[0] == $def_flag && $res_2[$t][11] != "" ) $ad_info[0] = $model->GetAdmitInfo($res_2[$t][11]);
            if( $ad_info[1] == $def_flag && $res_2[$t][12] != "" ) $ad_info[1] = $model->GetAdmitInfo($res_2[$t][12]);
            if( $ad_info[2] == $def_flag && $res_2[$t][13] != "" ) $ad_info[2] = $model->GetAdmitInfo($res_2[$t][13]);
            if( $ad_info[3] == $def_flag && $res_2[$t][24] != "" ) $ad_info[3] = $model->GetAdmitInfo($res_2[$t][24]);
            if( $ad_info[4] == $def_flag && $res_2[$t][25] != "" ) $ad_info[4] = $model->GetAdmitInfo($res_2[$t][25]);
            if( $ad_info[5] == $def_flag && $res_2[$t][26] != "" ) $ad_info[5] = $model->GetAdmitInfo($res_2[$t][26]);
            
            if( $select==2 && $absence_bu ) $ad_info[1] = "<font style='color:red;'>不在</font>"; // 部長不在
            if( $select==2 && $absence_ka ) $ad_info[0] = "<font style='color:red;'>不在</font>"; // 課長不在
        }
        /* <---------------------------------------------------------------- */

        // 既に承認している人も表示する為、条件を変更し再度データを取得する。
        $where5 = $where4 . " AND " . $where0;
        if( ($rows_2 = $model->GetReport($where5, $res_2)) <=0 ) continue;

        // 部課長コメント取得
        $comment = array('','');
        for( $t=0; $t<$rows_2; $t++ ) {
            if( $comment[0] == '' && $res_2[$t][14] != "" ) $comment[0] = $res_2[$t][14];
            if( $comment[1] == '' && $res_2[$t][15] != "" ) $comment[1] = $res_2[$t][15];
        }

        $holiday = $model->IsHoliday($date);
        if( $holiday ) {
            $caption_color   = 'background-color:red; color:white;';    // 休出は、背景 赤、文字 白
            $font_main_color = 'color:red;'; // 休出は、文字色を赤
        } else {
            $caption_color   = 'background-color:yellow; color:blue;';  // 通常は、背景 黄、文字 青
            $font_main_color = 'color:black;'; // 通常は、文字色を黒
        }
        $capcion = '作業日：' . $model->getTargetDateDay($date, 'on') . ' 部署名：' . $deploy;
        $menu->set_caption($capcion);
        ?>
        <input type='hidden' name='w_date<?php echo $i; ?>' value='<?php echo $date;?>'>
        <input type='hidden' name='deploy<?php echo $i; ?>' value='<?php echo $deploy;?>'>

        <BR>
        <table class='pt10' border="1" cellspacing="0">
        <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table width='100%' class='winbox_field' bgcolor='#D8D8D8' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr> <!-- キャプション -->
                    <td class='winbox' style='<?php echo $caption_color; ?>' colspan='9' align='center'>
                        <div class='caption_font'><?php echo $menu->out_caption(), "\n"?></div>
                    </td>
                </tr>

                <tr> <!-- キャプション２ -->
                    <td nowrap align='center' colspan='4'>事前申請</td>
                    <td nowrap align='center' colspan='5'>残業結果報告</td>
                </tr>

                <tr> <!-- 各項目名 -->
                    <td nowrap align='center'>氏　名</td>
                    <td nowrap align='center'>予定時間</td>
                    <td nowrap align='center'>残業実施理由</td>
                    <td nowrap align='center'>状況</td>
                    
                    <td nowrap align='center'>実際作業時間</td>
                    <td nowrap align='center'><?php if( $v_early ) echo "出勤時間"; else echo "退勤時間"; ?></td>
                    <td nowrap align='center'>実施業務内容</td>
                    <td nowrap align='center'>備考</td>
                    <td nowrap align='center'>状況</td>
                </tr>

                <input type='hidden' name='rows<?php echo $i; ?>' value='<?php echo $rows_2;?>'>
                <?php for($r=0; $r<$rows_2; $r++) { ?>
                    <?php
                    $font_style = $font_main_color; // 標準カラー
                    if( $select == 1 || $select == 2 ) {
                        $index = 10;    // yo_ad_st
                        $s_time = "{$res_2[$r][4]}:{$res_2[$r][5]}";
                        $e_time = "{$res_2[$r][6]}:{$res_2[$r][7]}";
                    } else {
                        $index = 23;    // ji_ad_st
                        $s_time = "{$res_2[$r][16]}:{$res_2[$r][17]}";
                        $e_time = "{$res_2[$r][18]}:{$res_2[$r][19]}";
                    }
                    // 承認者に関係ない申請は、グレーアウト
                    $select_disa = ''; // 承認の選択（有効：''、禁止：' disabled'）
                    if( $select != 2 ) {
                        if( $res_2[$r][$index] != ($pos_no-1) || $res_2[$r][$index + $pos_no] != 'm' ) {
                            $font_style = 'color:DarkGray;';
                        } else {
                            echo "<input type='hidden' name='up{$i}_{$r}'  value='on'>";

                            switch ($pos_no) {
                                case 3:     // 承認者 工場長なら
                                    if( $comment[1] ) break; // 部長コメントあり
                                    if( ! $model->IsButyouUID($res_2[$r][3]) ) break; // 申請者 部長 以外
                                    if( $holiday || (strtotime($e_time) - strtotime($s_time)) > 3600 ) $select_disa = ' disabled'; // 承認 禁止
                                    break;
                                case 2:     // 承認者 部長なら
                                    if( $comment[0] ) break; // 課長コメントあり
                                    if( ! $model->IsKatyouUID($res_2[$r][3]) ) break; // 申請者 課長 以外
                                    if( $holiday || (strtotime($e_time) - strtotime($s_time)) > 3600 ) $select_disa = ' disabled'; // 承認 禁止
                                    break;
                                default:    // 
                                    break;
                            }

                        }
                    } else {    // 不在未承認選択時
                        //  ルート ＞ 現在 ＆＆ 現在 == 承認者の前 || (課長不在 ＆＆ 課長承認 == 'm')
                        if( $res_2[$r][$index-1] > $res_2[$r][$index] && $res_2[$r][$index] == ($pos_no-2)  || ($absence_ka && $res_2[$r][$index+1] == 'm')) {
                            switch ($pos_no) {
                                case 3:   // 工場長なら部長（課長も含む）の出勤確認
                                    if( $absence_bu ) {
                                        if( $res_2[$r][$index+2] != 's' ) {
                                            echo "<input type='hidden' name='absence_bu{$i}_{$r}'  value='on'>";
                                        }
                                        if( $absence_ka && $res_2[$r][$index+1] != '' && $res_2[$r][$index+1] != 's') {
                                            echo "<input type='hidden' name='absence_ka{$i}_{$r}'  value='on'>";
                                        }
                                    }
                                    break;
                                case 2:   // 部長なら課長の出勤確認
                                    if( $absence_ka && $res_2[$r][$index+1] != 's' ) {
                                        echo "<input type='hidden' name='absence_ka{$i}_{$r}'  value='on'>";
                                    }
                                    break;
                            }
                            echo "<input type='hidden' name='up{$i}_{$r}' value='on'>";
                        } else {
                            $font_style = 'color:DarkGray;';
                        }
                    }
                    $uid = $res_2[$r][3];
                    $yo_root = $res_2[$r][9];
                    $ji_root = $res_2[$r][22];
                    ?>
                    <input type='hidden' name='uid<?php echo $i . '_' . $r; ?>'  value='<?php echo $uid;?>'>
                    <input type='hidden' name='yo_root<?php echo $i . '_' . $r; ?>' value='<?php echo $yo_root;?>'>
                    <input type='hidden' name='ji_root<?php echo $i . '_' . $r; ?>' value='<?php echo $ji_root;?>'>
                    <tr style='<?php echo $font_style; ?>'><!-- 時間外作業者情報 -->
                        <td nowrap><?php echo $model->getName($uid); ?></td> <!-- 氏　名 -->
                        <?php if( $res_2[$r][4] ) { ?>
                            <td nowrap align='center'><?php echo $res_2[$r][4] . ':' . $res_2[$r][5] . '～' . $res_2[$r][6] . ':' . $res_2[$r][7] ?></td> <!-- 予定時間 -->
                            <td nowrap align='center'><?php echo $res_2[$r][8] ?></td> <!-- 残業実施理由 -->
                            <td nowrap align='center'><?php echo $model->getAdmitStatus($yo_root, $res_2[$r][10]); ?></td> <!-- 状況 -->
                        <?php } else { ?>
                            <?php if($font_style == $font_main_color) { ?>
                            <td nowrap align='center' style='background-color:red; color:white;'>事 後 報 告</td> <!-- 予定時間 -->
                            <?php } else { ?>
                            <td nowrap align='center' style='<?php $font_style ?>'>事 後 報 告</td> <!-- 予定時間 -->
                            <?php } ?>
                            <td nowrap align='center'>--------</td> <!-- 残業実施理由 -->
                            <td nowrap align='center'>----</td> <!-- 状況 -->
                        <?php } ?>
                        <?php $style_error_j = ""; ?>
                        <?php if( $res_2[$r][16] ) { ?>
                            <?php if($res_2[$r][16]==$res_2[$r][18] && $res_2[$r][17]==$res_2[$r][19]) { ?>
                                <?php if($font_style == $font_main_color) { ?>
                                <td nowrap align='center' style='background-color:red; color:white;'>残業 キャンセル</td> <!-- 実際作業時間 -->
                                <?php } else { ?>
                                <td nowrap align='center' style='<?php $font_style ?>'>残業 キャンセル</td> <!-- 実際作業時間 -->
                                <?php } ?>
                            <?php } else { ?>
                                <?php if($res_2[$r][4]==$res_2[$r][16] && $res_2[$r][5]==$res_2[$r][17] && $res_2[$r][6]==$res_2[$r][18] && $res_2[$r][7]==$res_2[$r][19]) { ?>
                                <td nowrap align='center'><?php echo $res_2[$r][16] . ':' . $res_2[$r][17] . '～' . $res_2[$r][18] . ':' . $res_2[$r][19] ?></td> <!-- 実際作業時間 -->
                                <?php } else { ?>
                                <?php
                                $style_error_j = "style='background-color:yellow; color:blue; font-weight:bold;'"; // 異常 背景 黄、文字 青
                                if( $font_style == 'color:DarkGray;' ) $style_error_j = ""; // グレーアウトの行ならエラーstyleにしない。
                                ?>
                                <td nowrap align='center' title='予定時間と違います。※備考入力が必要' <?php echo $style_error_j; ?>><?php echo $res_2[$r][16] . ':' . $res_2[$r][17] . '～' . $res_2[$r][18] . ':' . $res_2[$r][19] ?></td> <!-- 実際作業時間 -->
                                <?php } ?>
                            <?php } ?>
                            <?php
if( $v_early ) {
                            $work_time   = $res_2[$r][16] . $res_2[$r][17];
                            $work_dt     = new DateTime("$work_time");// 実際作業時間（開始）
                            $style_error = "style='background-color:yellow; color:blue; font-weight:bold;'"; // 異常 背景 黄、文字 青
                            if( $font_style == 'color:DarkGray;' ) $style_error = ""; // グレーアウトの行ならエラーstyleにしない。
                            $end_time    = $model->getWorkingStrTime($uid, $date);
                            if( $end_time == "----" ) {// タイムプロのデータ異常？
                                $err_msg = "タイムプロのデータが見つかりません。";
                            } else {
                                $err_msg = "[出勤] IDカード通していません。";
                                if( $end_time != "0000" ) {// 出勤IDカード通した
                                    if($res_2[$r][16]==$res_2[$r][18] && $res_2[$r][17]==$res_2[$r][19]) {
                                        $style_error = ""; // 異常なし
                                        $err_msg = ""; // キャンセル
                                    } else {
                                        $end_dt = new DateTime("$end_time");  // 出勤時間
                                        $err_msg = "実際作業時間より後に出勤しています。";
                                        if( $end_dt <= $work_dt ) { // 出勤<＝申告 正常
                                            $err_msg = "実際作業時間より10分以上前から出勤しています。";
                                            $work_dt->modify('-10 minute');// 実際作業時間（開始10分前）
                                            if( $end_dt > $work_dt ) {// 退勤＞申告-10分 正常
                                                $style_error = ""; // 異常なし
                                                $err_msg = "";
                                            }
                                        }
                                    }
                                }
                            }
} else {
                            $work_time   = $res_2[$r][18] . $res_2[$r][19];
                            $work_dt     = new DateTime("$work_time");// 実際作業時間（終了）
                            $style_error = "style='background-color:yellow; color:blue; font-weight:bold;'"; // 異常 背景 黄、文字 青
                            if( $font_style == 'color:DarkGray;' ) $style_error = ""; // グレーアウトの行ならエラーstyleにしない。
                            $end_time    = $model->getWorkingEndTime($uid, $date);
                            if( $end_time == "----" ) {// タイムプロのデータ異常？
                                $err_msg = "タイムプロのデータが見つかりません。";
                            } else {
                                $err_msg = "[退勤] IDカード通していません。";
                                if( $end_time != "0000" ) {// 退勤IDカード通した
                                    $end_dt = new DateTime("$end_time");  // 退勤時間
                                    $err_msg = "実際作業時間より前に退勤しています。";
                                    if( $end_dt >= $work_dt ) { // 退勤＞＝申告 正常
                                        $dummy = substr($res[$i][0], 0,4) . substr($res[$i][0], 5,2) . substr($res[$i][0], 8,2);
                                        if($dummy >= "20220411") {
                                            $err_msg = "実際作業時間より10分超えてから退勤しています。";
                                            if( $work_time == "1715" ) $work_dt->modify('15 minute');// 17:15～17:30 は休憩の為スキップ
                                            $work_dt->modify('10 minute');// 実際作業時間（終了10分後）
                                            if( $end_dt <= $work_dt ) {// 退勤＜申告＋10分 正常
                                                $style_error = ""; // 異常なし
                                                $err_msg = "";
                                            }
                                        } else {
                                            $err_msg = "実際作業時間より30分超えてから退勤しています。";
                                            if( $work_time == "1715" ) $work_dt->modify('15 minute');// 17:15～17:30 は休憩の為スキップ
                                            $work_dt->modify('30 minute');// 実際作業時間（終了30分後）
                                            if( $end_dt < $work_dt ) {// 退勤＜申告＋30分 正常
                                                $style_error = ""; // 異常なし
                                                $err_msg = "";
                                            }
                                        }
                                    }
                                }
                            }
}
                            ?>
                            <td nowrap align='center' title='<?php echo $err_msg; ?>' <?php echo $style_error?>><?php echo substr_replace($end_time, ":", 2, 0); ?></td> <!-- 退勤時間 -->
                            <td nowrap align='center'><?php if($res_2[$r][20]) echo $res_2[$r][20]; else echo "　"; ?></td> <!-- 実施業務内容 -->
                            <?php
                            $remarks = "---";   // 備考データ（初期値："---"）
                            if( $res_2[$r][21] ) $remarks = $res_2[$r][21]; // 備考データが登録されている
                            if( $style_error || $style_error_j ) {// 退勤 黄色表示あり
                                if( $pos_no != 3 && $font_style == $font_main_color ) {
                                    if( !$res_2[$r][21] ) {// 備考未入力の為、承認ボタン使用禁止
                                        $c_radio = "disabled";
                                        $c_label = "style='color:DarkGray;'";
                                        $remarks = "<textarea name='remarks{$i}_{$r}' id='id_remarks{$i}_{$r}' rows='1' cols='40' onblur='IsRemarks(this, {$i}, {$rows_2});'>{$res_2[$r][21]}</textarea>";
                                    }
                                }
//                              $remarks = "<textarea name='remarks{$i}_{$r}' id='id_remarks{$i}_{$r}' rows='1' cols='40' {$readonly} {$ro_style} onblur='IsRemarks(this, {$i}, {$rows_2});'>{$res_2[$r][21]}</textarea>";
                            }
                            if( $remarks != "---" ) {
                                $remarks = "<textarea name='remarks{$i}_{$r}' id='id_remarks{$i}_{$r}' rows='1' cols='40' onblur='IsRemarks(this, {$i}, {$rows_2});'>{$res_2[$r][21]}</textarea>";
                            }
                            ?>
                            <td nowrap><?php echo $remarks; ?></td> <!-- 備考 -->
                            <td nowrap align='center'><?php echo $model->getAdmitStatus($ji_root, $res_2[$r][23]); ?></td> <!-- 状況 -->
                        <?php } else { ?>
                            <td nowrap align='center'>　</td> <!-- 実際作業時間 -->
                            <td nowrap align='center'>　</td> <!-- 退勤時間 -->
                            <td nowrap align='center'>　</td> <!-- 実施業務内容 -->
                            <td nowrap align='center'>　</td> <!-- 備考 -->
                            <td nowrap align='center'>　</td> <!-- 状況 -->
                        <?php } ?>
                    </tr>
                <?php } ?>

                <tr> <!-- 説明・承認(予定)・コメント・承認(実績) -->
                    <td nowrap colspan='2'>
                        <p class='pt9'>
                        ≪課長承認≫<BR>
                        　月、火、木 1時間までの残業<BR>
                        ≪部長承認≫　<課長コメント要><BR>
                        　月、火、木 1時間を超える残業<BR>
                        ≪工場長承認≫　<課長・部長コメント要><BR>
                        　水、金 残業および休日出勤<BR>
                        </p>
                    </td>

                    <td colspan='2' align='center'>
                        <?php if( $select == 1 || $select == 2 ) { ?>
                        <?php if( $select_disa ) { ?>
                        <font size=2>※<?php if($pos_no==2) echo "課長"; else echo "部長"; ?>コメント【未】承認不可</font><BR>
                        <input type='radio' name='radio_yo<?php echo $i; ?>' id='id_a_radio<?php echo $i; ?>' onClick="AdmitSelect(this, 's', <?php echo $i; ?>);" value="" <?php echo $select_disa; ?>><font style='color:DarkGray;'>承認</font>
                        <?php } else { ?>
                        <input type='radio' name='radio_yo<?php echo $i; ?>' id='id_a_radio<?php echo $i; ?>' onClick="AdmitSelect(this, 's', <?php echo $i; ?>);" value=""><label for='id_a_radio<?php echo $i; ?>'>承認</label>
                        <?php } ?>
                        <input type='radio' name='radio_yo<?php echo $i; ?>' id='id_b_radio<?php echo $i; ?>' onClick="AdmitSelect(this, 'h', <?php echo $i; ?>);" value=""><label for='id_b_radio<?php echo $i; ?>'>否認</label>
                        <BR><div align='right'>
                        <textarea name='yo_ng_comme<?php echo $i; ?>' id='id_yo_ng_comme<?php echo $i; ?>' rows='2' cols='22' value='' disabled>理由：</textarea>
                        </div>
                        <?php } ?>
                        <table class='pt10' border="1" cellspacing="0" align='right'>
                        <tr><td> <!----------- ダミー(デザイン用) ------------>
                            <table width='100%' class='winbox_field' bgcolor='#D8D8D8' align='center' border='1' cellspacing='0' cellpadding='3'>
                                <tr>
                                    <td nowrap align='center'>工場長</td>
                                    <td nowrap align='center'>部　長</td>
                                    <td nowrap align='center'>課　長</td>
                                </tr>
                                <tr>
                                    <td align='center'><?php echo $ad_info[2]; ?></td>
                                    <td align='center'><?php echo $ad_info[1]; ?></td>
                                    <td align='center'><?php echo $ad_info[0]; ?></td>
                                </tr>
                            </table>
                        </td></tr>
                        </table> <!----------------- ダミーEnd --------------------->
                    </td>

                    <td class='pt9'  colspan='2' valign='top'>
                        <?php
                        echo "課長 コメント ※残業の必要性を詳細に<BR>";
                        if( $pos_no == 1 && ($ad_info[1] == '未' || $ad_info[4] == '未') ) {
                            $readonly="";
                            $style = "style='$font_main_color'";
                        } else {
                            $readonly = "readonly";
                            $style = "style='$font_main_color background-color:#D8D8D8;'";
                        }
                        echo "<textarea name='comment_ka$i' id='id_comment_ka$i' rows='3' cols='30' $style value='$comment[0]' $readonly>$comment[0]</textarea>";

                        echo "<BR>部長 コメント ※残業の必要性を詳細に<BR>";
                        if( $pos_no == 2 && ($ad_info[2] == '未' || $ad_info[5] == '未' ) ) {
                            $readonly="";
                            $style = "style='$font_main_color'";
                        } else {
                            $readonly = "readonly";
                            $style = "style='$font_main_color background-color:#D8D8D8;'";
                        }
                        echo "<textarea name='comment_bu$i' id='id_comment_bu$i' rows='3' cols='30' $style value='$comment[1]' $readonly>$comment[1]</textarea>";
                        ?>
                    </td>

                    <td colspan='3' nowrap align='center'>
                        <?php if( $select == 3 ) { ?>
                        <?php if( $select_disa ) { ?>
                        <font size=2>※<?php if($pos_no==2) echo "課長"; else echo "部長"; ?>コメント【未】承認不可</font><BR>
                        <input type='radio' name='radio_ji<?php echo $i; ?>' id='id_c_radio<?php echo $i; ?>' onClick="AdmitSelect(this, 's', <?php echo $i; ?>);" value="" <?php echo $select_disa; ?>><font style='color:DarkGray;'>承認</font>
                        <?php } else { ?>
                            <?php if( $v_early ) { ?>
                                <font id='id_rem_msg<?php echo $i; ?>' style='color:red;'><?php if( $c_radio ) echo "※実際作業時間 or 出勤時間 が黄色の備考入力を行って下さい。"; ?></font><BR>
                            <?php } else { ?>
                                <font id='id_rem_msg<?php echo $i; ?>' style='color:red;'><?php if( $c_radio ) echo "※退勤時間 黄色の備考入力を行って下さい。"; ?></font><BR>
                            <?php } ?>
                        <input type='radio' name='radio_ji<?php echo $i; ?>' id='id_c_radio<?php echo $i; ?>' onClick="AdmitSelect(this, 's', <?php echo $i; ?>);" value="" <?php echo $c_radio; ?>><label for='id_c_radio<?php echo $i; ?>' id='id_c_label<?php echo $i; ?>' <?php echo $c_label; ?>>承認</label>
                        <?php } ?>
                        <input type='radio' name='radio_ji<?php echo $i; ?>' id='id_d_radio<?php echo $i; ?>' onClick="AdmitSelect(this, 'h', <?php echo $i; ?>);" value=""><label for='id_d_radio<?php echo $i; ?>'>否認</label>
                        <BR><div align='right'>
                        <textarea name='ji_ng_comme<?php echo $i; ?>' id='id_ji_ng_comme<?php echo $i; ?>' rows='2' cols='22' value='' disabled>理由：</textarea>
                        </div>
                        <?php } else { echo "<BR>"; } ?>
                        <table class='pt9' border="1" cellspacing="0" align='right'>
                        <tr><td> <!----------- ダミー(デザイン用) ------------>
                            <table width='100%' class='winbox_field' bgcolor='#D8D8D8' align='center' border='1' cellspacing='0' cellpadding='3'>
                                <tr>
                                    <td nowrap align='center'>工場長</td>
                                    <td nowrap align='center'>部　長</td>
                                    <td nowrap align='center'>課　長</td>
                                </tr>
                                <tr>
                                    <td align='center'><?php echo $ad_info[5]; ?></td>
                                    <td align='center'><?php echo $ad_info[4]; ?></td>
                                    <td align='center'><?php echo $ad_info[3]; ?></td>
                                </tr>
                            </table>
                        </td></tr>
                        </table> <!----------------- ダミーEnd --------------------->
                    </td>

                </tr>
            </table>
        </td></tr>
        </table> <!----------------- ダミーEnd --------------------->
    <?php } // for($i=0; $i<$rows; $i++) End. ?>

<!--  --><BR>
    <?php if( $rows <= 0 || $rows_2 <= 0 ) { ?>
        未承認のデータはありません。
    <?php } else { ?>
        <?php if($select==3 && $v_normal) { ?>
        <div style='color:red'>
            ※【退勤時間】の表示を追加しました。 報告内容のチェックにご利用下さい。<BR>　
        </div>
        <?php } ?>
        <input type='button' name='admit_all' id='' value='承認一括選択' onClick='AdmitAllSelect(this, <?php echo $rows ?>);'>　
        <input type='submit' name='admit_ok'  id='' value='実行' onClick='return AdmitExec();'>　
        <input type='button' name='admit_no'  id='' value='キャンセル' onClick='location.replace("<?php echo $menu->out_self(), '?showMenu=Judge'; ?>");'>
        <BR>　
    <?php } ?>
</form>

</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
