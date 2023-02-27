<?php
////////////////////////////////////////////////////////////////////////////////
// 総合届（照会）                                                             //
//                                                    MVC View 部 リスト表示  //
// Copyright (C) 2020-2020 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2020/11/18 Created sougou_results_View.php                                 //
// 2021/02/12 Release.                                                        //
// 2021/04/21 社外からの出向受け入れ者は、栃木の社員コードを逐次追加          //
// 2022/01/27 受電者の必要ない時、応対者が"不要"なら表示しないよう変更        //
////////////////////////////////////////////////////////////////////////////////

// 曜日を表示
function DayDisplay($target_date, $model)
{
    $week = array(' (日)',' (月)',' (火)',' (水)',' (木)',' (金)',' (土)');

    $day_no = date('w', strtotime($target_date));
    if( $day_no == 0 ) {            // 日曜日（色：赤）
        echo $target_date . "<font color='red'>$week[$day_no]</font>";
    } else if( $day_no == 6 ) {     // 土曜日（色：青）
        echo $target_date . "<font color='blue'>$week[$day_no]</font>";
    } else if( $model->IsHoliday($target_date) ) {  // 会社カレンダー休日（色：赤）
        echo $target_date . "<font color='red'>$week[$day_no]</font>";
    } else {
        echo $target_date . $week[$day_no];         // その他 平日 営業日（色：デフォルト黒）
    }
}

$menu->out_html_header();
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
<link rel='stylesheet' href='sougou_results.css' type='text/css' media='screen'>
<script type='text/javascript' language='JavaScript' src='sougou_query.js'></script>

</head>
<body>
<center>

<?php
    $Y = date('Y');
    $m = date('m');
    $d = date('d');
    $limit = 25;    // 処理確定日

    if( $d < $limit ) {
        if($m == 1) {
            $Y -= 1;
            $m = 13;
        }
        $del_day = sprintf("%04s-%02s-10", $Y, ($m-1));   // 先月の10日以前が削除不可
    } else {
        $del_day = sprintf("%04s-%02s-10", $Y, $m);   // 今月の10日以前が削除不可
    }

    $res = array(); 
    $indx = $model->getIndx();
    $rows = $model->getRows();
    $res = $model->getRes();

    // [戻る]ボタンで、戻った時にデータを受け渡す為POSTデータセット
    $menu->set_retPOST('rep', 'rep');
    $menu->set_retPOST('c0', $request->get('c0'));
    $menu->set_retPOST('si_s_date', $request->get('si_s_date'));
    $menu->set_retPOST('si_e_date', $request->get('si_e_date'));
    $menu->set_retPOST('syainbangou', $request->get('syainbangou'));
    $menu->set_retPOST('c1', $request->get('c1'));
    $menu->set_retPOST('str_date', $request->get('str_date'));
    $menu->set_retPOST('end_date', $request->get('end_date'));
    $menu->set_retPOST('c2', $request->get('c2'));
    $menu->set_retPOST('ddlist', $request->get('ddlist'));
    $menu->set_retPOST('ddlist_bumon', $request->get('ddlist_bumon'));
    $menu->set_retPOST('r4', $request->get('r4'));
    $menu->set_retPOST('r5', $request->get('r5'));
    $menu->set_retPOST('r6', $request->get('r6'));
    $menu->set_retPOST('r7', $request->get('r7'));
    $menu->set_retPOST('r8', $request->get('r8'));
    $menu->set_retPOST('r9', $request->get('r9'));
?>
<!--
<header style="position: fixed; ">
-->

<?= $menu->out_title_border() ?>

<!--
</header>
-->

<?php $showMenu = 'List' ?>
<form name='form_results' method='post' action='<?php echo $menu->out_self(),"?showMenu=" . $showMenu ?>' onSubmit='return true'>

<input type='hidden' name='rows' value=<?php echo $rows; ?>>
<input type='hidden' name='indx' value=<?php echo $indx; ?>>

<?php for( $r=0; $r<$rows; $r++ ) { ?>
    <?php $posname = sprintf("res-%s[]", $r); ?>
    <?php for( $i=0; $i<$indx; $i++ ) { ?>
        <input type='hidden' name='<?php echo $posname; ?>' value='<?php echo $res[$r][$i]; ?>'>
    <?php } ?>
<?php } ?>

<input type='hidden' name='rep' value='rep'>
<input type='hidden' name='c0' value='<?php echo $request->get('c0'); ?>'>
<input type='hidden' name='si_s_date' value='<?php echo $request->get('si_s_date'); ?>'>
<input type='hidden' name='si_e_date' value='<?php echo $request->get('si_e_date'); ?>'>
<input type='hidden' name='syainbangou' value='<?php echo $request->get('syainbangou'); ?>'>
<input type='hidden' name='c1' value='<?php echo $request->get('c1'); ?>'>
<input type='hidden' name='str_date' value='<?php echo $request->get('str_date'); ?>'>
<input type='hidden' name='end_date' value='<?php echo $request->get('end_date'); ?>'>
<input type='hidden' name='c2' value='<?php echo $request->get('c2'); ?>'>
<input type='hidden' name='ddlist' value='<?php echo $request->get('ddlist'); ?>'>
<input type='hidden' name='ddlist_bumon' value='<?php echo $request->get('ddlist_bumon'); ?>'>
<input type='hidden' name='r4' value='<?php echo $request->get('r4'); ?>'>
<input type='hidden' name='r5' value='<?php echo $request->get('r5'); ?>'>
<input type='hidden' name='r6' value='<?php echo $request->get('r6'); ?>'>
<input type='hidden' name='r7' value='<?php echo $request->get('r7'); ?>'>
<input type='hidden' name='r8' value='<?php echo $request->get('r8'); ?>'>
<input type='hidden' name='r9' value='<?php echo $request->get('r9'); ?>'>

<?php if( $model->getRows() == 0) { ?>
    <br>検索条件に一致する総合届はありません。<br>
<?php } else  if($request->get('c2') == '') { ?>
    <br>検索条件に一致した総合届 【 <?php echo $rows . " 件】"?><br>
<?php if(getCheckAuthority(66)) { ?> <!-- 66:取消可能（総務課）-->
    <p class='pt9' style="text-align: right"><?php echo "※{$limit}日に確定処理済みの為、{$del_day}以前の取消はできません。<BR>社外からの出向受け入れ者は、背景色が青。"; ?></p>
<?php } ?>

    <table class='pt10' border="1" cellspacing="0">
    <tr><td> <!----------- ダミー(デザイン用) ------------>
<table width='100%' class='pt10' bgcolor='#D8D8D8' align='center' border='1' cellspacing='0' cellpadding='3'>
    <tr style='background-color:yellow; color:blue;'>
<?php if( getCheckAuthority(66)) { ?> <!-- 66:取消可能（総務課）-->
        <th align='center'>入力</th>
<?php } ?>
        <th align='center'>申請日</th>
        <?php if(getCheckAuthority(63) || $model->IsBukatyou()) { ?> <!-- 63:社員番号入力可能（総務課）-->
        <th align='center'>申請者</th>
        <?php } ?>
        <th align='center'>期間</th>
        <th align='center'>内容</th>
        <th align='center'>備考</th>
        <th align='center'>連絡先</th>
<?php
if( date('Ymd') > '20210630' ) {    // 回数券使用不可の対応
        echo "<th align='center'>Suica</th>";
        $suica_view = 'on';
} else {
        echo "<th align='center'>回数券</th>";
        $suica_view = '';
}
?>
        <th align='center'>受電者</th>
        <th align='center'>至急</th>
        <th align='center'>承認状況</th>
        <th align='center'>中断理由</th>
<?php if(getCheckAuthority(66)) { ?> <!-- 66:取消可能（総務課）-->
        <th align='center'>取消</th>
<?php } ?>
    </tr>

<?php
    for ( $r=0; $r<$rows; $r++) {
        $date                   = $res[$r][0];          // 申請日
        if( $r==0 || $res[$r-1][1] != $res[$r][1]) {
            $uid                    = $res[$r][1];      // 申請者社員番号
        } else {
            $uid                    = "　";             // 申請者社員番号
        }
        $start_date             = trim($res[$r][2]);    // 開始日
        $start_time             = $res[$r][3];          // 開始時刻
        $end_date               = trim($res[$r][4]);    // 終了日
        $end_time               = $res[$r][5];          // 終了時刻
        $content                = trim($res[$r][6]);    // 内容
        $yukyu                  = $res[$r][7];          // 内容詳細（有休系）
        $ticket01               = trim($res[$r][8]);    // 回数券の有無
        $ticket02               = trim($res[$r][9]);    // 特急券の有無
        $special                = trim($res[$r][10]);   // 内容詳細（特別休暇）
        if( $special == '慶弔A' ) {
            $special = "慶弔：本人が結婚 5日(在籍中1回)";
        } else if ( $special == '慶弔B' ) {
            $special = "慶弔：父母・配偶者・子が死亡 5日";
        } else if ( $special == '慶弔C' ) {
            $special = "慶弔：配偶者の父母、本人の祖父母、兄弟の死亡 3日";
        }
        $others                 = $res[$r][11];         // 行先 or 振替内容 or その他
        $place                  = $res[$r][12];         // 都道府県
        $purpose                = $res[$r][13];         // 目的
        $ticket01_set           = trim($res[$r][14]);   // 回数券の必要数
        $ticket02_set           = trim($res[$r][15]);   // 特急券の必要数
        $doukousya              = trim($res[$r][16]);   // 同行者
        $remarks                = $res[$r][17];         // 備考
        $contact                = trim($res[$r][18]);   // 連絡先
        $contact_other          = trim($res[$r][19]);   // 連絡先（その他）
        $contact_tel            = $res[$r][20];         // 連絡先（TEL）
        $received_phone         = $res[$r][21];         // 受電者の有無
        $received_phone_date    = $res[$r][22];         // 受電日時
        $received_phone_name    = trim($res[$r][23]);   // 受電者名
        if( $received_phone_name == "不要" ) $received_phone = "";
        $hurry                  = $res[$r][24];         // 至急の有無
        $ticket                 = $res[$r][25];         // 回数券・特急券の有無
        $admit_status           = trim($res[$r][26]);   // 承認状況
        $amano_input            = trim($res[$r][27]);   // 入力状況
        if( $admit_status == 'END') {
            $admit_status = '完了';
        } else if( $admit_status == 'DENY' ) {
            $admit_status = '否認';
        } else if( $admit_status == 'CANCEL' ) {
            $admit_status = '取消';
        }
        $amano_input            = $res[$r][27];         // アマノ入力状況

        // 社外からの出向受け入れ者 逐次追加
        // 020826:
        $view_style="";
        if( $res[$r][1] == '020826' ) {
            $view_style='background-color:RoyalBlue; color:White;';
        }
?>
    <?php echo "<tr style='{$view_style}'>"; ?>
<!-- 入力状況 -->
<?php if( getCheckAuthority(66)) { ?> <!-- 66:取消可能（総務課）-->
    <?php if( $amano_input == 't' ) { ?>
            <td nowrap align='center'>済</td>
    <?php } else { ?>
            <td nowrap><input type="checkbox" name=<?php echo "amano" . $r; ?> onClick=SetVal(this);></td>
    <?php } ?>
<?php } ?>

<!-- 申請日 -->
        <td nowrap><?php echo substr($res[$r][0], 0 ,10); ?></td>
<!-- 申請者 -->
        <?php if(getCheckAuthority(63) || $model->IsBukatyou()) { ?> <!-- 63:社員番号入力可能（総務課）-->
        <td nowrap>
            <?php echo $uid; ?>
            <br>
            <?php echo $model->getSyainName($uid); ?>
        </td>
        <?php } ?>
<!-- 期間 -->
        <td nowrap>
            <?php
                DayDisplay($start_date, $model);
                if($start_date != $end_date) {
                    echo " ～ ";
                    DayDisplay($end_date, $model);
                }
            ?>
        <br>
            <?php
//                echo '　' . $start_time . " ～ " . $end_time;
                echo '　';
                if( $start_time ) echo $start_time;
                if( $start_time && $end_time ) echo " ～ ";
                if( $end_time ) echo $end_time;
            ?>
        </td>
<!-- 内容 -->
        <td nowrap>
            <?php
            if($content == "IDカード通し忘れ（退勤）＋ 時限承認忘れ（残業申告漏れ）") {
                echo "IDカード通し忘れ（退勤）<br>";
                echo "　＆時限承認忘れ（残業申告漏れ）<br>";
            } else {
                echo $content . "<br>";
            }
            ?>
<?php
        if( $content == "有給休暇" || $content == "AM半日有給休暇" || $content == "PM半日有給休暇"
            || $content == "時間単位有給休暇" || $content == "欠勤" || $content == "遅刻早退" ) {
?>
            &emsp;&emsp;<?php echo $yukyu; ?>
<?php
        } else if( $content == "出張（日帰り）" || $content == "出張（宿泊）"
            || $content == "直行" || $content == "直帰" || $content == "直行/直帰" ) {
?>
            &emsp;&emsp;<?php echo "行先：" . $others; ?>
            &emsp;<?php echo "都道府県：" . $place; ?><br>&ensp;
            &emsp;<?php echo "目的：" . $purpose; ?>
<?php
if( $suica_view == 'on' && $ticket01_set == 1) {   // 回数券使用不可の対応
?>
<?php
} else {
?>
    <?php if( $ticket01 != "不要" && $ticket01 != NULL ) { ?>
    <br>
            &emsp;&emsp;乗車券（氏家～宇都宮間）&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;
            <?php echo $ticket01; ?>
            <?php if( $ticket01 != "不要" ) echo $ticket01_set . "セット"; ?>
    <?php } ?>
    <?php if( $ticket02 != "不要" && $ticket01 != NULL ) { ?>
    <br>
            &emsp;&emsp;新幹線特急券自由席・乗車券（宇都宮～東京間）
            <?php echo $ticket02; ?>
            <?php if( $ticket02 != "不要" ) echo $ticket02_set . "セット"; ?>
    <?php } ?>
<?php
}
?>
    <?php if( $doukousya != '---' ) { ?>
    <br>
            &emsp;&emsp;<?php echo "同行者：" . $doukousya; ?>
    <?php } ?>
<?php
        } else if( $content == "特別休暇" ) {
?>
            &emsp;&emsp;<?php echo $special; ?>
            <?php if( $special == "その他" ) echo "： " . $others; ?>
<?php
        } else if( $content == "振替休日" || $content == "その他" ) {
?>
            &emsp;&emsp;<?php echo $others; ?>
<?php
        } else {
            ; // echo "それ以外";
        }
?>
        </td>
<!-- 備考 -->
        <td nowrap><?php echo $remarks; ?></td>
<!-- 連絡先 -->
        <td nowrap>
            <?php echo $contact; ?>
            <?php if( $contact == "その他" ) echo "(" . $contact_other . ")"; ?>
            <?php if( $contact == "その他" || $contact == "出張先") echo "<br> TEL:" . $contact_tel; ?>
        </td>
<!-- 回数券 -->
        <td nowrap align='center'>
            <?php if( $ticket == 't' ) { ?>
                <?php echo "必要"; ?>
            <?php } else if( $ticket == 'f' ) { ?>
                <?php echo "不要"; ?>
            <?php } else { ?>
                <?php echo "---"; ?>
            <?php } ?>
        </td>
<!-- 受電者 -->
        <td nowrap>
            <?php if( $received_phone != '' ) { ?>
                <?php echo "応対日時：" . $received_phone_date; ?>
                <br>
                <?php echo "応 対 者：" . $received_phone_name; ?>
            <?php } else { ?>
                <?php echo "---"; ?>
            <?php } ?>
        </td>
<!-- 至急 -->
        <td nowrap>
            <?php if( $hurry != '' ) { ?>
                <?php echo $hurry; ?>
            <?php } else { ?>
                <?php echo "---"; ?>
            <?php } ?>
        </td>
<!-- 承認 -->
        <td nowrap>
            <?php echo $admit_status; ?>
            <br>
            <?php echo $model->getSyainName($admit_status); ?>
        </td>
<!-- 理由 -->
        <td nowrap>
            <?php echo $model->getAdmitStopReason($res[$r][0], $res[$r][1], $admit_status); ?>
        </td>
<!-- 取消 -->
<?php if(getCheckAuthority(66)) { ?> <!-- 66:取消可能（総務課）-->
    <?php if( str_replace('-', '', $start_date) > str_replace('-', '', $del_day) ) { ?>
        <td>
    <?php } else { ?>
        <td disabled=true>
    <?php } ?>
            <input type="checkbox" name=<?php echo $r; ?> id=<?php echo $r; ?> value="CANCEL" <?php if( $admit_status == '取消' ) echo ' disabled' ?>>
        </td>
<?php } ?>
    </tr>

<?php } /* for() End */ ?>

    </table>
    </td></tr>
</table> <!----------------- ダミーEnd --------------------->

<?php } else { ?> <!--- 以下は、不在者リスト表示 --->
    <br>検索条件に一致した総合届一覧【 <?php echo $rows . " 件】"?>
    <p class='pt9' style="text-align: right"><?php echo "※社外からの出向受け入れ者は、背景色が青。"; ?></p>

    <table class='pt10' border="1" cellspacing="0">
    <tr><td> <!----------- ダミー(デザイン用) ------------>
<table width='100%' class='pt10' bgcolor='#D8D8D8' align='center' border='1' cellspacing='0' cellpadding='3'>
    <tr style='background-color:yellow; color:blue;'>
        <td align='center'>所属コード</td>
        <td align='center'>所属名</td>
        <td align='center'>個人コード</td>
        <td align='center'>氏名</td>
        <td align='center'>開始日</td>
        <td align='center'>終了日</td>
        <td align='center'>内容</td>
        <td align='center'>開始時間</td>
        <td align='center'>終了時間</td>
<!-- 最終確認で見るため、承認状況いらない -->
    </tr>

<?php
    for ( $r=0; $r<$rows; $r++) {
        if( $r == 0 ) { // 先頭行のみ
            $syozokucode        = trim($res[$r][0]);    // 所属コード
            $syozoku            = trim($res[$r][1]);    // 所属
            $kojincode          = trim($res[$r][2]);    // 個人コード
            $name               = trim($res[$r][3]);    // 名前
        } else {        // 2行目以降、前行と違うなら代入、同じなら空欄に
            if( trim($res[$r-1][0]) != trim($res[$r][0]) ) {
                $syozokucode    = trim($res[$r][0]);    // 所属コード
                if( trim($res[$r-1][1]) != trim($res[$r][1]) ) {
                    $syozoku        = trim($res[$r][1]);    // 所属
                } else {
                    $syozoku        = "　";                 // 所属
                }
            } else {
                $syozokucode    = "　";                 // 所属コード
                $syozoku        = "　";                 // 所属
            }
            if( trim($res[$r-1][2]) != trim($res[$r][2]) ) {
                $kojincode      = trim($res[$r][2]);    // 個人コード
                $name           = trim($res[$r][3]);    // 名前
            } else {
                $kojincode      = "　";                 // 個人コード
                $name           = "　";                 // 名前
            }
        }
        $str_date               = trim($res[$r][4]);    // 開始日
        $end_date               = trim($res[$r][5]);    // 終了日
        $content                = trim($res[$r][6]);    // 内容
        $str_time               = trim($res[$r][7]);    // 開始時刻
        if( $str_time == '' ) $str_time = '--:--';
        $end_time               = trim($res[$r][8]);    // 終了時刻
        if( $end_time == '' ) $end_time = '--:--';

        // 社外からの出向受け入れ者 逐次追加
        // 020826:
        $view_style="";
        if( $res[$r][2] == '020826' ) {
            $view_style='background-color:RoyalBlue; color:White;';
        }
?>
    <?php echo "<tr style='{$view_style}'>"; ?>
<!-- 所属コード -->
        <td align='right'><?php echo $syozokucode; ?></td>
<!-- 所属名 -->
        <td nowrap><?php echo $syozoku; ?></td>
<!-- 個人コード -->
        <td align='right'><?php echo $kojincode; ?></td>
<!-- 氏名 -->
        <td nowrap><?php echo $name; ?></td>
<!-- 開始日 -->
        <td nowrap align='center'>
            <?php
                DayDisplay($str_date, $model);
            ?>
        </td>
<!-- 終了日 (開始日と同じなら空欄) -->
        <td nowrap align='center'>
            <?php
                if($str_date != $end_date) {
                    DayDisplay($end_date, $model);
                } else {
                    echo "　";
                }
            ?>
        </td>
<!-- 内容 -->
        <td nowrap><?php echo $content; ?></td>
<!-- 開始時間 -->
        <td><?php echo $str_time; ?></td>
<!-- 終了時間 -->
        <td><?php echo $end_time; ?></td>
    </tr>

<?php } /* for() End */ ?>

    </table>
    </td></tr>
</table> <!----------------- ダミーEnd --------------------->

<?php } /* if() End */ ?>

    <br>
<?php if( $model->getRows() != 0 && $request->get('c2') == '' && getCheckAuthority(66)) { ?> <!-- 66:取消可能（総務課）-->
    <input type="submit" value="入力 済 更新" name="amano" onClick='return AmanoRun(<?php echo $rows ?>)'>　
    <input type='hidden' name='amano_run' value='false'>
<?php } ?>

    <input type="submit" value="検索条件へ戻る" name="submit">

<?php if($model->getRows() != 0 && $request->get('c2') == '' && getCheckAuthority(66)) { ?> <!-- 66:取消可能（総務課）-->
    　<input type="submit" value="取消実行" name="cancel" onClick='return CancelRun(<?php echo $rows ?>)'>
    <input type='hidden' name='cancel_run' value='false'>
<?php } ?>
</form>

</center>

<a href="#" class="gotop">トップへ</a>
</body>
<?php echo $menu->out_alert_java()?>
</html>
