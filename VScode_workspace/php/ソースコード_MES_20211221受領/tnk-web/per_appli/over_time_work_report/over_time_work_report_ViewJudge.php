<?php
////////////////////////////////////////////////////////////////////////////////
// 定時間外作業申告（承認）                                                   //
//                                                    MVC View 部 リスト表示  //
// Copyright (C) 2021-2021 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2021/10/20 Created over_time_work_report_ViewJudge.php                     //
// 2021/11/01 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////
$menu->out_html_header();
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

<body onLoad=''>

<center>
    <?= $menu->out_title_border() ?>

<!-- ＰＤＦファイルを開く -->
    <div class='pt10' align='center'>
    <BR>※操作方法が分からない場合、<a href="download_file.php/定時間外作業申告_承認_マニュアル.pdf">定時間外作業申告（承認）マニュアル</a> を参考にして下さい。<BR>
    </div>
<!-- TEST Start.-->
    <?php if($debug) { ?>
    <div class='pt9' align='left'><font color='red'>※※※ ここから、テストの為表示  ※※※</font></div>
    ※【テスト　承認者　切替】 工場長：
    <input type='button' style='<?php if($login_uid=="011061") echo "background-color:yellow"; ?>' value='011061' onClick='CangeUID(this.value, "form_judge");'>　
    副工場長：
    <input type='button' style='<?php if($login_uid=="012394") echo "background-color:yellow"; ?>' value='012394' onClick='CangeUID(this.value, "form_judge");'>　
    部長：
    <input type='button' style='<?php if($login_uid=="017850") echo "background-color:yellow"; ?>' value='017850' onClick='CangeUID(this.value, "form_judge");'>　
    <input type='button' style='<?php if($login_uid=="012980") echo "background-color:yellow"; ?>' value='012980' onClick='CangeUID(this.value, "form_judge");'>　
    <input type='button' style='<?php if($login_uid=="016713") echo "background-color:yellow"; ?>' value='016713' onClick='CangeUID(this.value, "form_judge");'>
    <BR><BR>
    課長：
    <input type='button' style='<?php if($login_uid=="300055") echo "background-color:yellow"; ?>' value='300055' onClick='CangeUID(this.value, "form_judge");'>　
    <input type='button' style='<?php if($login_uid=="300349") echo "background-color:yellow"; ?>' value='300349' onClick='CangeUID(this.value, "form_judge");'>　
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
                    <td class='winbox' style='<?php echo $caption_color; ?>' colspan='8' align='center'>
                        <div class='caption_font'><?php echo $menu->out_caption(), "\n"?></div>
                    </td>
                </tr>

                <tr> <!-- キャプション２ -->
                    <td nowrap align='center' colspan='4'>事前申請</td>
                    <td nowrap align='center' colspan='4'>残業結果報告</td>
                </tr>

                <tr> <!-- 各項目名 -->
                    <td nowrap align='center'>氏　名</td>
                    <td nowrap align='center'>予定時間</td>
                    <td nowrap align='center'>残業実施理由</td>
                    <td nowrap align='center'>状況</td>
                    
                    <td nowrap align='center'>実際作業時間</td>
                    <td nowrap align='center'>実施業務内容</td>
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
                        <td nowrap><?php echo $model->getName($uid); ?></td>
                        <?php if( $res_2[$r][4] ) { ?>
                            <td nowrap align='center'><?php echo $res_2[$r][4] . ':' . $res_2[$r][5] . '〜' . $res_2[$r][6] . ':' . $res_2[$r][7] ?></td>
                            <td nowrap align='center'><?php echo $res_2[$r][8] ?></td>
                            <td nowrap align='center'><?php echo $model->getAdmitStatus($yo_root, $res_2[$r][10]); ?></td>
                        <?php } else { ?>
                            <?php if($font_style == $font_main_color) { ?>
                            <td nowrap align='center' style='background-color:red; color:white;'>事 後 報 告</td>
                            <?php } else { ?>
                            <td nowrap align='center' style='<?php $font_style ?>'>事 後 報 告</td>
                            <?php } ?>
                            <td nowrap align='center'>--------</td> <!-- 内容 -->
                            <td nowrap align='center'>----</td> <!-- 状態 -->
                        <?php } ?>
                        <?php if( $res_2[$r][16] ) { ?>
                            <?php if($res_2[$r][16]==$res_2[$r][18] && $res_2[$r][17]==$res_2[$r][19]) { ?>
                                <?php if($font_style == $font_main_color) { ?>
                                <td nowrap align='center' style='background-color:red; color:white;'>残業 キャンセル</td>
                                <?php } else { ?>
                                <td nowrap align='center' style='<?php $font_style ?>'>残業 キャンセル</td>
                                <?php } ?>
                            <?php } else { ?>
                                <td nowrap align='center'><?php echo $res_2[$r][16] . ':' . $res_2[$r][17] . '〜' . $res_2[$r][18] . ':' . $res_2[$r][19] ?></td>
                            <?php } ?>
                                <td nowrap align='center'><?php if($res_2[$r][20]) echo $res_2[$r][20]; else echo "　"; ?></td>
                            <td nowrap align='center'><?php echo $model->getAdmitStatus($ji_root, $res_2[$r][23]); ?></td>
                        <?php } else { ?>
                            <td nowrap align='center'>　</td> <!-- 時間 -->
                            <td nowrap align='center'>　</td> <!-- 内容 -->
                            <td nowrap align='center'>　</td> <!-- 状態 -->
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

                    <td class='pt9' valign='top'>
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

                    <td colspan='3' align='center'>
                        <?php if( $select == 3 ) { ?>
                        <?php if( $select_disa ) { ?>
                        <font size=2>※<?php if($pos_no==2) echo "課長"; else echo "部長"; ?>コメント【未】承認不可</font><BR>
                        <input type='radio' name='radio_ji<?php echo $i; ?>' id='id_c_radio<?php echo $i; ?>' onClick="AdmitSelect(this, 's', <?php echo $i; ?>);" value="" <?php echo $select_disa; ?>><font style='color:DarkGray;'>承認</font>
                        <?php } else { ?>
                        <input type='radio' name='radio_ji<?php echo $i; ?>' id='id_c_radio<?php echo $i; ?>' onClick="AdmitSelect(this, 's', <?php echo $i; ?>);" value=""><label for='id_c_radio<?php echo $i; ?>'>承認</label>
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
