<?php
////////////////////////////////////////////////////////////////////////////////
// 総合届（承認）                                                             //
//                                                    MVC View 部 リスト表示  //
// Copyright (C) 2020-2020 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2020/11/18 Created sougou_admit_EditView.php                               //
//            申請画面（sougou_ViewList.php）も必要に応じ同時修正             //
// 2021/02/12 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////
function SelectOptionDate($start, $end, $def)
{
    for ($i = $start; $i <= $end ; $i++) {
        if ($i == $def) {
            echo "<option value='" . sprintf("%02d", $i) . "' selected>" . $i . "</option>";
        } else {
            echo "<option value='" . sprintf("%02d", $i) . "'>" . $i . "</option>";
        }
    }
}

function SelectOptionTime($start, $end, $def)
{

    for ($i = $start; $i <= $end ; $i++) {
        if ($i == $def) {
            echo "<option value='" . sprintf("%02s",$i) . "' selected>" . $i . "</option>";
        } else {
            if( $end == 23 ) {
                echo "<option value='" . sprintf("%02s",$i) . "'>" . $i . "</option>";
            }
            if( $end == 59 ) {
                if( $i == 0 || $i%5 == 0 ) {
                    echo "<option value='" . sprintf("%02s",$i) . "'>" . $i . "</option>";
                }
            }
        }
    }
}

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
$menu->set_caption('下記の必要な条件を入力又は選択して下さい。');

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
<script type='text/javascript' language='JavaScript' src='../in_sougou/sougou.js'></script>

</head>
<body onLoad='AdmitEdit()'>
<center>

<?php $menu->set_retPOST('edit_no', $request->get('edit_no')); ?>

<?= $menu->out_title_border() ?>

<?php
    $res = array(); 
    $res = $model->getRes();

    $date                   = $res[0][0];
    $uid                    = $res[0][1];
    $start_date             = $res[0][2];
    $start_time             = $res[0][3];
    if( ! $start_time ) {
        $start_time = "08:30";
    }
    $end_date               = $res[0][4];
    $end_time               = $res[0][5];
    if( ! $end_time ) {
        $end_time = "17:15";
    }
    $content                = trim($res[0][6]);
    $yukyu                  = trim($res[0][7]);
    $ticket01               = trim($res[0][8]);
    $ticket02               = trim($res[0][9]);
    $special                = trim($res[0][10]);
    $others                 = trim($res[0][11]);
    $place                  = trim($res[0][12]);
    $purpose                = trim($res[0][13]);
    $ticket01_set           = trim($res[0][14]);
    $ticket02_set           = trim($res[0][15]);
    $doukousya              = trim($res[0][16]);
    if( $doukousya == '---') $doukousya = '';
    $remarks                = trim($res[0][17]);
    if( $remarks == '---') $remarks = '';
    $contact                = trim($res[0][18]);
    $contact_other          = trim($res[0][19]);
    $contact_tel            = trim($res[0][20]);
    $received_phone         = trim($res[0][21]);
    $received_phone_date    = trim($res[0][22]);
    $received_phone_name    = trim($res[0][23]);
    $hurry                  = trim($res[0][24]);
    $ticket                 = $res[0][25];
    $admit_status           = trim($res[0][26]);
    $amano_input            = $res[0][27];

    $suica_view             = $request->get('suica_view');  // 回数券使用不可の対応 Suica表示
?>
    <br>
    <table class='pt10' border="1" cellspacing="0">
    <tr><td> <!----------- ダミー(デザイン用) ------------>
    <table width='100%' class='winbox_field' bgcolor='#D8D8D8' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                        <!--  bgcolor='#ffffc6' 薄い黄色 --> 
                    <td class='winbox' style='background-color:yellow; color:blue;' colspan='2' align='center'>
                        <div class='caption_font'><?php echo $menu->out_caption(), "\n"?></div>
                    </td>
                </tr>
    <tr>
        <td align='center'>申請日</td>
        <td>
        &ensp;
            <?php
                DayDisplay(substr($date, 0, 10), $model);
                echo substr($date, 10, 6);
            ?>
            <?php
/* 個人情報の観点から表示しない方が良い。 */
            $yukyudata = $model->getYukyu();
            if( $yukyudata[0][4] == 0 ) {
//                echo "【有給情報：<font color='red'>現在、メンテナンス中です</font>。】";
                ?>
                <script>alert("※開始・終了時間の自動設定が正しく機能していません。\n\n　申請の際は、開始・終了時間をよく確認して下さい。");</script>
                <?php
                $twork = 8; $swork = 3; $ework = 5;
                $kyuka_jisseki = 0;
                $kyuka_yotei_1 = 0;
                $kyuka_yotei_2 = 0;
            } else {
//                echo "【有給残：<font color='red'>{$yukyudata[0][1]}</font>/{$yukyudata[0][0]}日】【半休：<font color='red'>{$yukyudata[0][2]}</font>/12回　時間休：<font color='red'>{$yukyudata[0][3]}</font>/{$yukyudata[0][4]}時間】";
//                echo "<BR><font class='pt10' color='red'>　　　　　　　　　　　　　　　　　　　　　　※有給残は、申請日よりあとの予定分も含まれています。</font>";
                $twork = $yukyudata[0][4] / 5;    // 就業時間 8 or 7
                if( $uid == '300349' && $request->get('act_id') == '670' ) {
                    $swork = 2; // 9:15 開始の人（商管：村上）
                } else {
                    $swork = 3; // 8:30 開始の人
                }
                $ework = $twork - $swork; // 
                $kyuka_jisseki = $model->KeikakuCnt();
                $kyuka_yotei_1 = $model->YoteiKyuka( $uid, substr($date, 0, 10), $start_date, $end_date, true);
                $kyuka_yotei_2 = $model->YoteiKyuka( $uid, substr($date, 0, 10), $start_date, $end_date, false);
            }
/**/
            ?>
            <input type='hidden' name='k_jisseki' id='id_k_jisseki' value='<?php echo $kyuka_jisseki; ?>'>
            <input type='hidden' name='k_yotei_1' id='id_k_yotei_1' value='<?php echo $kyuka_yotei_1; ?>'>
            <input type='hidden' name='k_yotei_2' id='id_k_yotei_2' value='<?php echo $kyuka_yotei_2; ?>'>

            <input type='hidden' name='t_work' id='id_t_work' value='<?php echo $twork; ?>'>
            <input type='hidden' name='s_work' id='id_s_work' value='<?php echo $swork; ?>'>
            <input type='hidden' name='e_work' id='id_e_work' value='<?php echo $ework; ?>'>
            <script>SetDefTime();</script>
        </td>
    </tr>

    <tr>
        <td align='center'>申請者</td>
        <td align='center'>
            <?php echo $model->getSyozoku($uid); ?>
            &emsp;
            <?php echo "社員番号：" . $uid; ?>
            &emsp;
            <?php echo '氏名：'. $model->getSyainName($uid); ?>
        </td>
    </tr>

<form name='form_edit' method='post' action='<?php echo $menu->out_self(); ?>' onSubmit='return allcheck()'>

    <input type='hidden' name='edit_no' value="<?php echo $request->get('edit_no'); ?>">
    <input type='hidden' name='sin_date' value='<?php echo $date; ?>'>
    <input type='hidden' name='sin_year' id='sin_year' value='<?php echo substr($date, 0, 4); ?>'>
    <input type='hidden' name='sin_month' id='sin_month' value='<?php echo substr($date, 5, 2); ?>'>
    <input type='hidden' name='sin_day' id='sin_day' value='<?php echo substr($date, 8, 2); ?>'>
    <input type='hidden' name='sin_hour' id='sin_hour' value='<?php echo substr($date, 11, 2); ?>'>
    <input type='hidden' name='sin_minute' id='sin_minute' value='<?php echo substr($date, 14, 2); ?>'>
    <input type='hidden' name='syain_no' value='<?php echo $uid; ?>'>

    <tr>
        <td align='center'>期&ensp;間</td>
        <td align='center'>
            <?php $year = substr($start_date, 0, 4) ?>
            <?php $month = substr($start_date, 5, 2) ?>
            <?php $day = substr($start_date, 8, 2) ?>
            <!-- 会社カレンダーの休日情報を取得し、javascriptの変数へセットしておく。-->
            <?php $holiday = json_encode($model->getHolidayRang($year-1,$year+1)); ?>
            <script> var holiday = '<?php echo $holiday; ?>';  SetHoliday(holiday);</script>
            <!-- -->
            <input type="checkbox" name="c0" id="0" value="1日" <?php if($start_date == $end_date) echo " checked" ?> onclick="OneDay(this.checked);"><label for="0">1日</label>
            <select name="ddlist" id="id_syear" onclick="StartDateCopy()">
                <?php SelectOptionDate($year-1, $year+1, $year); ?>
            </select>年
            <select name="ddlist" id="id_smonth" onclick="StartDateCopy()">
                <?php SelectOptionDate(1, 12, $month); ?>
            </select>月
            <select name="ddlist" id="id_sday" onclick="StartDateCopy()">
                <?php SelectOptionDate(1, 31, $day); ?>
            </select>日
            <font id='id_s_youbi'></font>
            <input type='hidden' name='str_date' value='<?php echo $start_date; ?>'>

            <?php $year = substr($end_date, 0, 4) ?>
            <?php $month = substr($end_date, 5, 2) ?>
            <?php $day = substr($end_date, 8, 2) ?>
            <font id='id_1000' > ～&ensp;
            <select name="ddlist" id="id_eyear" onclick="EndDateCopy()">
                <?php SelectOptionDate($year-1, $year+1, $year); ?>
            </select>年
            <select name="ddlist" id="id_emonth" onclick="EndDateCopy()">
                <?php SelectOptionDate(1, 12, $month); ?>
            </select>月
            <select name="ddlist" id="id_eday" onclick="EndDateCopy()">
                <?php SelectOptionDate(1, 31, $day); ?>
            </select>日
            <font id='id_e_youbi'></font>
            <input type='hidden' name='end_date' value='<?php echo $end_date; ?>'>
            </font>
        <br><br>
            <?php $hh = substr($start_time, 0, 2); ?>
            <?php $mm = substr($start_time, 3, 2); ?>
            <font id='id_start_time_area'>
            <input type="radio" name="r0" id="001"><label for="001">開始</label>
            <select name="ddlist" id="id_shh" onblur="StartTimeCopy()">
                <?php SelectOptionTime(0, 23, $hh); ?>
            </select>時
            <select name="ddlist" id="id_smm" onblur="StartTimeCopy()">
                <?php SelectOptionTime(0, 59, $mm); ?>
            </select>分
            <input type='hidden' name='str_time' value='<?php echo $start_time; ?>'>
            </font>
            <font id='id_time_area'>
            ～
            </font>
            <?php $hh = substr($end_time, 0, 2) ?>
            <?php $mm = substr($end_time, 3, 2) ?>
            <font id='id_end_time_area'>
            <input type="radio" name="r0" id="002"><label for="002">終了</label>
            <select name="ddlist" id="id_ehh" onblur="EndTimeCopy()">
                <?php SelectOptionTime(0, 23, $hh); ?>
            </select>時
            <select name="ddlist" id="id_emm" onblur="EndTimeCopy()">
                <?php SelectOptionTime(0, 59, $mm); ?>
            </select>分
            <input type='hidden' name='end_time' value='<?php echo $end_time; ?>'>
            </font>

            <font id='id_time_sum_area'>
            <label for="001">開始</label> or <label for="002">終了</label>より<input type="text" size="2" maxlength="2" name="sum_hour" id="id_sum_hour" onkeyup="value = value.replace(/[^0-9]/,'');">時間
            <input type="button" value="計算" name="sum" id="id_sum" onClick='TimeCalculation()'>
            </font>
        </td>
    </tr>

    <tr><td align='center'>内&ensp;容</td>
        <td>
        <input type="radio" name="r1" id="101" onClick="syousai();" value="有給休暇" <?php if($content=="有給休暇") echo " checked"; ?>><label for="101">有給休暇</label>
        <input type="radio" name="r1" id="102" onClick="syousai();" value="AM半日有給休暇" <?php if($content=="AM半日有給休暇") echo " checked"; ?>><label for="102">AM半日有給休暇</label>
        <input type="radio" name="r1" id="103" onClick="syousai();" value="PM半日有給休暇" <?php if($content=="PM半日有給休暇") echo " checked"; ?>><label for="103">PM半日有給休暇</label>
        <input type="radio" name="r1" id="104" onClick="syousai();" value="時間単位有給休暇" <?php if($content=="時間単位有給休暇") echo " checked"; ?>><label for="104">時間単位有給休暇</label>
        <input type="radio" name="r1" id="105" onClick="syousai();" value="欠勤" <?php if($content=="欠勤") echo " checked"; ?>><label for="105">欠勤</label>
        <input type="radio" name="r1" id="106" onClick="syousai();" value="遅刻早退" <?php if($content=="遅刻早退") echo " checked"; ?>><label for="106">遅刻早退</label>
            <table class='pt10' border="1" cellspacing="1" align='center' id='1000'>
            <caption></caption>
            <tr><td>
            <input type="radio" name="r2" id="201" value="通院（本人）" <?php if($yukyu=="通院（本人）") echo " checked"; ?>><label for="201">通院（本人）</label>
            <input type="radio" name="r2" id="202" value="体調不良（本人）" <?php if($yukyu=="体調不良（本人）") echo " checked"; ?>><label for="202">体調不良（本人）</label>
            <input type="radio" name="r2" id="203" value="学校行事" <?php if($yukyu=="学校行事") echo " checked"; ?>><label for="203">学校行事</label>
            <input type="radio" name="r2" id="204" value="公共機関" <?php if($yukyu=="公共機関") echo " checked"; ?>><label for="204">公共機関</label>
            <input type="radio" name="r2" id="205" value="私事都合" <?php if($yukyu=="私事都合") echo " checked"; ?>><label for="205">私事都合</label>
            <br>
            <input type="radio" name="r2" id="206" value="通院（家族）" <?php if($yukyu=="通院（家族）") echo " checked"; ?>><label for="206">通院（家族）</label>
            <input type="radio" name="r2" id="207" value="体調不良（家族）" <?php if($yukyu=="体調不良（家族）") echo " checked"; ?>><label for="207">体調不良（家族）</label>
            <input type="radio" name="r2" id="208" value="冠婚葬祭" <?php if($yukyu=="冠婚葬祭") echo " checked"; ?>><label for="208">冠婚葬祭</label>
            <input type="radio" name="r2" id="209" value="計画有休" onClick="Iskeikaku();" <?php if($yukyu=="計画有休") echo " checked"; ?>><label for="209" id="keikaku">計画有休</label>
            <input type="radio" name="r2" id="210" value="特別計画" <?php if($yukyu=="特別計画") echo " checked"; ?>><label for="210" id="tokukei">特別計画</label>
            </td></tr>
            </table>
            <br>

        <!-- 折りたたみ展開ボタン -->
        <div onclick="obj=document.getElementById('menu1').style; obj.display=(obj.display=='none')?'block':'none';">
        <a class='pt12b' style="cursor:pointer;">▼ 出張関連（クリックで展開）</a>
        </div>
        <!--// 折りたたみ展開ボタン -->

        <!-- ここから先を折りたたむ -->
        <div id="menu1" style="display:none;clear:both;font-size:12pt;font-weight:normal;">

        <!--この部分が折りたたまれ、展開ボタンをクリックすることで展開します。-->
        <input type="radio" name="r1" id="107" onClick="syousai();" value="出張（日帰り）" <?php if($content=="出張（日帰り）") echo " checked"; ?>><label for="107">出張（日帰り）</label>
        &emsp;&emsp;&ensp;
        <input type="radio" name="r1" id="108" onClick="syousai();" value="出張（宿泊）" <?php if($content=="出張（宿泊）") echo " checked"; ?>><label for="108">出張（宿泊）</label>
        <br>
        <input type="radio" name="r1" id="109" onClick="syousai();" value="直行" <?php if($content=="直行") echo " checked"; ?>><label for="109">直行</label>
        &emsp; &emsp; &emsp; &emsp; &nbsp; &thinsp;
        <input type="radio" name="r1" id="110" onClick="syousai();" value="直帰" <?php if($content=="直帰") echo " checked"; ?>><label for="110">直帰</label>
        &emsp; &emsp; &emsp; &emsp; &nbsp; &thinsp;
        <input type="radio" name="r1" id="111" onClick="syousai();" value="直行/直帰" <?php if($content=="直行/直帰") echo " checked"; ?>><label for="111">直行/直帰</label>
        <p class='pt10' align='center' id='2000'>
        &emsp;
<?php
if( $suica_view == 'on' ) {    // 回数券使用不可の対応
?>
            行先：<input type="text" size="46" maxlength="24" name="ikisaki" value='<?php echo $others; ?>' onchange="value = SpecialText(this)">
            都道府県：<input type="text" size="18" maxlength="10" name="todouhuken" value='<?php echo $place; ?>'>
            <br><br>目的：<input type="text" size="78" maxlength="32" name="mokuteki" value='<?php echo $purpose; ?>'>
<?php
} else {
?>
            行先：<input type="text" size="24" maxlength="24" name="ikisaki" value='<?php echo $others; ?>'>
            都道府県：<input type="text" size="10" maxlength="10" name="todouhuken" value='<?php echo $place; ?>'>
            目的：<input type="text" size="24" maxlength="24" name="mokuteki" value='<?php echo $purpose; ?>'>
<?php
}
?>
        </p>
        <p class='pt9' align='center' id='2500'>
<?php
if( $suica_view == 'on' ) {    // 回数券使用不可の対応
?>
            ※ Suica を利用しますか？&emsp;&emsp;&emsp;&emsp;
            <input type="radio" name="r3" id="301" onClick="suica();" value="不要" <?php if($ticket01=="不要") echo " checked"; ?>><label for="301">しない</label>
            <input type="radio" name="r3" id="302" onClick="suica();" value="往復" <?php if($ticket01=="往復") echo " checked"; ?>><label for="302">する</label>
        <br>
            （利用可能区間であれば、利用前に総務課から Suica を利用者へ貸出）
        <br><br>
            同行者：<input type="text" size="80" maxlength="160" name="doukou" value='<?php echo $doukousya; ?>'></textarea>
        <br><br>
            <input type='hidden' name='n_suica' id='id_suica'>
            <input type='hidden' name='r4'>
            <input type='hidden' name='setto1'>
            <input type='hidden' name='setto2'>
        </p>
<?php
} else {
?>
<!-- 回数券が使用できなくなったら、コメントアウト -->
<!-- -->
            ※出張等で回数券を使用する場合は、以下をご確認下さい。
        <br><br>
            乗車券（氏家～宇都宮間）&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;
            <input type="radio" name="r3" id="301" onClick="setto()" value="不要" <?php if($ticket01=="不要") echo " checked"; ?>><label for="301">不要</label>
            <input type="radio" name="r3" id="302" onClick="setto()" value="往復" <?php if($ticket01=="往復") echo " checked"; ?>><label for="302">往復</label>
            <input type="radio" name="r3" id="303" onClick="setto()" value="片道" <?php if($ticket01=="片道") echo " checked"; ?>><label for="303">片道</label>
            <input type="text" size="2" maxlength="2" name="setto1" value='<?php echo $ticket01_set; ?>' onkeyup="value = value.replace(/[^0-9]/,'');">セット
        <br>
            新幹線特急券自由席・乗車券（宇都宮～東京間）
            <input type="radio" name="r4" id="401" onClick="setto()" value="不要" <?php if($ticket02=="不要") echo " checked"; ?>><label for="401">不要</label>
            <input type="radio" name="r4" id="402" onClick="setto()" value="往復" <?php if($ticket02=="往復") echo " checked"; ?>><label for="402">往復</label>
            <input type="radio" name="r4" id="403" onClick="setto()" value="片道" <?php if($ticket02=="片道") echo " checked"; ?>><label for="403">片道</label>
            <input type="text" size="2" maxlength="2" name="setto2" value='<?php echo $ticket02_set; ?>' onkeyup="value = value.replace(/[^0-9]/,'');">セット
        <br>
<!-- 回数券が使用できなくなったら、以下をダミーで使用すればエラーは発生しない。 -->
<!--
            <input type='hidden' name='r3'> <input type='hidden' name='r4'>
            <input type='hidden' name='setto1'> <input type='hidden' name='setto2'>
<!-- -->
            同行者：<input type="text" size="80" maxlength="160" name="doukou" value='<?php echo $doukousya; ?>'></textarea>
        <br><br>※出張等で切符購入する場合は、別途 <a color="red" id="2550">切符購入依頼書</a> を提出して下さい。</p>
<?php
}
?>
        </div>
        <!--// ここまでを折りたたむ -->

        <!-- 折りたたみ展開ボタン -->
        <div onclick="obj=document.getElementById('menu2').style; obj.display=(obj.display=='none')?'block':'none';">
        <a class='pt12b' style="cursor:pointer;">▼ 特別休暇関連（クリックで展開）</a>
        </div>
        <!--// 折りたたみ展開ボタン -->

        <!-- ここから先を折りたたむ -->
        <div id="menu2" style="display:none;clear:both;font-size:12pt;font-weight:normal;">

        <!--この部分が折りたたまれ、展開ボタンをクリックすることで展開します。-->
        <input type="radio" name="r1" id="112" onClick="syousai();" value="特別休暇" <?php if($content=="特別休暇") echo " checked"; ?>><label for="112">特別休暇</label>
            <table class='pt10' border="1" cellspacing="1" align='center' id='3000'>
            <caption></caption>
            <tr><td>
            <input type="radio" name="r5" id="501" onClick="toku()" value="慶弔A" <?php if($special=="慶弔A") echo " checked"; ?>><label for="501">慶弔：本人が結婚 5日(在籍中1回)</label>
            <br>
            <input type="radio" name="r5" id="502" onClick="toku()" value="慶弔B" <?php if($special=="慶弔B") echo " checked"; ?>><label for="502">慶弔：父母・配偶者・子が死亡 5日</label>
            <br>
            <input type="radio" name="r5" id="503" onClick="toku()" value="慶弔C" <?php if($special=="慶弔C") echo " checked"; ?>><label for="503">慶弔：配偶者の父母、本人の祖父母、兄弟の死亡 3日</label>
            <br>
            <input type="radio" name="r5" id="504" onClick="toku()" value="公民権の行使" <?php if($special=="公民権の行使") echo " checked"; ?>><label for="504">公民権の行使</label>
            <input type="radio" name="r5" id="505" onClick="toku()" value="勤続満30年" <?php if($special=="勤続満30年") echo " checked"; ?>><label for="505">勤続満30年 5日</label>
            <input type="radio" name="r5" id="506" onClick="toku()" value="その他" <?php if($special=="その他") echo " checked"; ?>><label for="506">その他：<input type="text" name="tokubetu_sonota" value='<?php echo $others; ?>'></label>
            </td></tr>
            </table>
        </div>
        <!--// ここまでを折りたたむ -->

        <br>
        <input type="radio" name="r1" id="113" onClick="syousai();" value="振替休日" <?php if($content=="振替休日") echo " checked"; ?>><label for="113">振替休日（ 月 日出勤分→<input type="text" size="30" name="hurikae" value='<?php echo $others; ?>'>）</label>
        &emsp;&emsp;&emsp;&ensp;
        <input type="radio" name="r1" id="114" onClick="syousai();" value="生理休暇" <?php if($content=="生理休暇") echo " checked"; ?>><label for="114">生理休暇</label>
        <br>
        <input type="radio" name="r1" id="115" onClick="syousai();" value="IDカード通し忘れ（出勤）" <?php if($content=="IDカード通し忘れ（出勤）") echo " checked"; ?>><label for="115">IDカード通し忘れ（出勤）</label>
        &emsp;&emsp;
        <input type="radio" name="r1" id="116" onClick="syousai();" value="IDカード通し忘れ（退勤）" <?php if($content=="IDカード通し忘れ（退勤）") echo " checked"; ?>><label for="116">IDカード通し忘れ（退勤）</label>
        <br>
        <input type="radio" name="r1" id="117" onClick="syousai();" value="時限承認忘れ（残業申告漏れ）" <?php if($content=="時限承認忘れ（残業申告漏れ）") echo " checked"; ?>><label for="117">時限承認忘れ（残業申告漏れ）</label>
        <br>
        <input type="radio" name="r1" id="118" onClick="syousai();" value="IDカード通し忘れ（退勤）＋ 時限承認忘れ（残業申告漏れ）" <?php if($content=="IDカード通し忘れ（退勤）＋ 時限承認忘れ（残業申告漏れ）") echo " checked"; ?>><label for="118">IDカード通し忘れ（退勤）＋ 時限承認忘れ（残業申告漏れ）</label>
        <input type="radio" name="r1" id="119" onClick="syousai();" value="その他" <?php if($content=="その他") echo " checked"; ?>><label for="119">その他：<input type="text" name="syousai_sonota" value='<?php echo $others; ?>'></label>
        </td>
    </tr>

    <input type='hidden' name='content_no' id='id_content_no' value='-1'>

    <tr>
        <td align='center'>備&ensp;考</td>
        <td><input type="text" size="100" maxlength="40" name="bikoutext" value='<?php echo $remarks; ?>'> ※最大40字</td>
    </tr>

    <tr id='id_renraku'>
        <td align='center'>連絡先</td>
        <td>
            <input type="radio" name="r6" id="601" onclick="telno();" value="携帯" <?php if($contact=="携帯") echo " checked"; ?>><label for="601">携帯</label>
            <input type="radio" name="r6" id="602" onclick="telno();" value="自宅" <?php if($contact=="自宅") echo " checked"; ?>><label for="602">自宅</label>
            <input type="radio" name="r6" id="603" onclick="telno();" value="出張先" <?php if($contact=="出張先") echo " checked"; ?>><label for="603">出張先</label>
            <input type="radio" name="r6" id="604" onclick="telno();" value="その他" <?php if($contact=="その他") echo " checked"; ?>><label for="604">その他：<input type="text" size="6" maxlength="6" name="tel_sonota" value='<?php echo $contact_other; ?>'></label>
            <font id='id_tel_no'>TEL</font><input type="text" name="tel_no" maxlength="13" onkeyup="value = value.replace(/[^0-9,-]+/i,'');" value='<?php echo $contact_tel; ?>'>
        </td>
    </tr>

<!-- -->
    <tr id='id_jyuden'>
        <td align='center'>
            ※受電者
        </td>
        <td>
            <?php if($received_phone_date) $year = substr($received_phone_date, 0, 4); else $year = substr($start_date, 0, 4); ?>
            <?php if($received_phone_date) $month = substr($received_phone_date, 5, 2); else $month = substr($start_date, 5, 2); ?>
            <?php if($received_phone_date) $day = substr($received_phone_date, 8, 2); else $day = substr($start_date, 8, 2); ?>
            <?php if($received_phone_date) $hh = substr($received_phone_date, 11, 2); else $hh = 8; ?>
            <?php if($received_phone_date) $mm = substr($received_phone_date, 14, 2); else $mm = 30; ?>
            応対日時：
                <select name="ddlist_jyu" id="id_jyear" onclick="JyuDateCopy()">
                    <?php SelectOptionDate($year-1, $year, $year); ?>
                </select>年
                <select name="ddlist_jyu" id="id_jmonth" onclick="JyuDateCopy()">
                    <?php SelectOptionDate(1, 12, $month); ?>
                </select>月
                <select name="ddlist_jyu" id="id_jday" onclick="JyuDateCopy()">
                    <?php SelectOptionDate(1, 31, $day); ?>
                </select>日
                <font id='id_j_youbi'></font>&ensp;
                <select name="ddlist_jyu" id="id_jhh" onblur="JyuDateCopy()">
                    <?php SelectOptionTime(0, 23, $hh); ?>
                </select>時
                <select name="ddlist_jyu" id="id_jmm" onblur="JyuDateCopy()">
                    <?php SelectOptionTime(0, 59, $mm); ?>
                </select>分
                <input type='hidden' name='jyu_date' value=''>

            応対者：<input type="text" size="16" maxlength="8" name="outai" value='<?php echo $received_phone_name; ?>' onMouseover="Coment.style.visibility='visible'" onMouseout="Coment.style.visibility='hidden'" title="">
                <div id="Coment" style="color:#000000; background:#e7e7e7; font-size='9pt'; position:absolute; top:; left:; width:150; padding:5; visibility:hidden; filter:alpha(opacity='80');">
                    [社員番号] or [名前]
                </div>

        </td>
    </tr>
<!-- -->

    </table>
    </td></tr>
    </table> <!----------------- ダミーEnd --------------------->

    <p align='center'>
        <input type='hidden' name='sougou_update' value='off'>
        <input type="checkbox" name="c2" id="idc2" value="至急" <?php if($hurry=="至急") echo " checked"; ?>><label for="idc2" id="idc2l" >至急</label>
        <input type="submit" value="更新" name="submit" onClick='SougouUpdate()'>
        <input type="button" value="キャンセル" name="cancel" onClick='location.replace("<?php echo $menu->out_self(), '?edit_no=' . $request->get('edit_no') ?>");'>
    </p>
</form>

    <BR>　
</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
