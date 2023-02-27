<?php
////////////////////////////////////////////////////////////////////////////////
// 総合届（申請）                                                             //
//                                                    MVC View 部 確認表示    //
// Copyright (C) 2020-2020 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2020/11/18 Created sougou_admit_ViewCheck.php                              //
// 2021/02/12 Release.                                                        //
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
<script type='text/javascript' language='JavaScript' src='sougou.js'></script>

</head>
<?php
$sinseiNG = false;
$naiyou= $request->get('r1');
if( $naiyou == "有給休暇" || $naiyou == "AM半日有給休暇" || $naiyou == "PM半日有給休暇"
 || $naiyou == "時間単位有給休暇" || $naiyou == "欠勤" || $naiyou == "遅刻早退"
 || $naiyou == "特別休暇" || $naiyou == "振替休日" || $naiyou == "生理休暇" )
{
    if( $model->IsHoliday($request->get("str_date")) ) $sinseiNG = true;
    if( $model->IsHoliday($request->get("end_date")) ) $sinseiNG = true;
}
?>
<body onLoad='CheckDisp(<?php echo $sinseiNG ?>)'>
<center>

<div>
<br>【最終確認】<br><br>
</div>

<?php $showMenu = 'List'; ?>
<form name='form_check' method='post' action='<?php echo $menu->out_self(),"?showMenu=" . $showMenu ?>' onSubmit='return true'>

<input type='hidden' name='check_flag' ?>
<input type='hidden' name='sin_date' value='<?php echo $request->get("sin_date"); ?>'>
<input type='hidden' name='sin_year' value='<?php echo $request->get("sin_year"); ?>'>
<input type='hidden' name='sin_month' value='<?php echo $request->get("sin_month"); ?>'>
<input type='hidden' name='sin_day' value='<?php echo $request->get("sin_day"); ?>'>
<input type='hidden' name='sin_hour' value='<?php echo $request->get("sin_hour"); ?>'>
<input type='hidden' name='sin_minute' value='<?php echo $request->get("sin_minute"); ?>'>

<input type='hidden' name='syain_no' value='<?php echo $request->get("syain_no"); ?>'>
<input type='hidden' name='syainbangou' value='<?php echo $request->get("syain_no"); ?>'>
<input type='hidden' name='str_date' value='<?php echo $request->get("str_date"); ?>'>
<input type='hidden' name='str_time' value='<?php echo $request->get("str_time"); ?>'>
<input type='hidden' name='end_date' value='<?php echo $request->get("end_date"); ?>'>
<input type='hidden' name='end_time' value='<?php echo $request->get("end_time"); ?>'>
<input type='hidden' name='r1' value='<?php echo $request->get('r1'); ?>'>
<input type='hidden' name='r2' value='<?php echo $request->get('r2'); ?>'>
<input type='hidden' name='r3' value='<?php echo $request->get('r3'); ?>'>
<input type='hidden' name='r4' value='<?php echo $request->get('r4'); ?>'>
<input type='hidden' name='r5' value='<?php echo $request->get('r5'); ?>'>
<input type='hidden' name='ikisaki' value='<?php echo $request->get("ikisaki"); ?>'>
<input type='hidden' name='tokubetu_sonota' value='<?php echo $request->get("tokubetu_sonota"); ?>'>
<input type='hidden' name='hurikae' value='<?php echo $request->get("hurikae"); ?>'>
<input type='hidden' name='syousai_sonota' value='<?php echo $request->get("syousai_sonota"); ?>'>
<input type='hidden' name='todouhuken' value='<?php echo $request->get("todouhuken"); ?>'>
<input type='hidden' name='mokuteki' value='<?php echo $request->get("mokuteki"); ?>'>
<input type='hidden' name='setto1' value='<?php echo $request->get("setto1"); ?>'>
<input type='hidden' name='setto2' value='<?php echo $request->get("setto2"); ?>'>
<input type='hidden' name='doukou' value='<?php echo $request->get("doukou"); ?>'>
<input type='hidden' name='bikoutext' value='<?php echo $request->get("bikoutext"); ?>'>
<input type='hidden' name='r6' value='<?php echo $request->get("r6"); ?>'>
<input type='hidden' name='tel_sonota' value='<?php echo $request->get("tel_sonota"); ?>'>
<input type='hidden' name='tel_no' value='<?php echo $request->get("tel_no"); ?>'>
<input type='hidden' name='jyu_date' value='<?php echo $request->get("jyu_date"); ?>'>
<input type='hidden' name='outai' value='<?php echo $request->get("outai"); ?>'>
<input type='hidden' name='c2' value='<?php echo $request->get("c2"); ?>'>

<input type='hidden' name='reappl' value='<?php echo $request->get("reappl"); ?>'>
<input type='hidden' name='deny_uid' value='<?php echo $request->get("deny_uid"); ?>'>
<input type='hidden' name='previous_date' value='<?php echo $request->get("previous_date"); ?>'>

<?php
        $date                   = $request->get("sin_date");        // 申請年月日
        $uid                    = $request->get("syain_no");        // 申請者 社員番号
        $start_date             = $request->get("str_date");        // 期間 開始 日付
        $start_time             = $request->get("str_time");        // 期間 開始 時間
        $end_date               = $request->get("end_date");        // 期間 終了 日付
        $end_time               = $request->get("end_time");        // 期間 終了 時間
        $content                = $request->get('r1');              // 内容（ラジオ1）
        $yukyu                  = $request->get('r2');              // 内容（ラジオ2）有給関連
        $ticket01               = $request->get('r3');              // 内容（ラジオ3）乗車券
        $ticket02               = $request->get('r4');              // 内容（ラジオ4）新幹線：不要
        $special                = $request->get('r5');              // 内容（ラジオ5）特別関連
        if( $special == '慶弔A' ) {
            $special = "慶弔：本人が結婚 5日(在籍中1回)";
        } else if ( $special == '慶弔B' ) {
            $special = "慶弔：父母・配偶者・子が死亡 5日";
        } else if ( $special == '慶弔C' ) {
            $special = "慶弔：配偶者の父母、本人の祖父母、兄弟の死亡 3日";
        }
        $others                 = $request->get('ikisaki');         // 内容（文字列1）行先・振替休日・その他
        if( $others == '' )
            $others             = $request->get('tokubetu_sonota'); // 内容（文字列1）行先・振替休日・その他
        if( $others == '' )
            $others             = $request->get('hurikae');         // 内容（文字列1）行先・振替休日・その他
        if( $others == '' )
            $others             = $request->get('syousai_sonota');  // 内容（文字列1）行先・振替休日・その他

        $place                  = $request->get('todouhuken');      // 内容（文字列2）都道府県
        $purpose                = $request->get('mokuteki');        // 内容（文字列3）目的
        $ticket01_set           = $request->get('setto1');          // 内容（文字列4）乗車券セット数
        $ticket02_set           = $request->get('setto2');          // 内容（文字列5）新幹線セット数
        $doukousya              = $request->get('doukou');          // 内容（文字列6）同行者
        if( $doukousya == '' )
            $doukousya             = '---';                         // 内容（文字列6）同行者
        $remarks                = $request->get('bikoutext');       // 備考
        if( $remarks == '' )
            $remarks             = '---';                           // 備考

        $contact                = $request->get('r6');              // 連絡先（ラジオ）
        if( $contact == '' )
            $contact             = '---';                           // 連絡先（ラジオ）
        $contact_other          = $request->get('tel_sonota');      // 連絡先（その他）
        $contact_tel            = $request->get('tel_no');          // 連絡先（TEL）

        $hurry                  = $request->get('c2');              // 至急（チェック）

        $suica_view             = $request->get('suica_view');      // 回数券使用不可の対応 Suica表示
?>
    <table class='pt10' border="1" cellspacing="0">
        <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table width='100%' class='winbox_field' bgcolor='#D8D8D8' align='center' border='1' cellspacing='0' cellpadding='3'>

    <?php if( trim($hurry) == "至急" ) { ?>
        <caption class='pt12b' style='background-color:#FF0040; color:white;'> >>>>>　至急　<<<<< </caption>
    <?php } else { ?>
        <caption style='background-color:yellow; color:blue;'>総合届</caption>
    <?php } ?>
    <tr>
        <td align='center'>申請日</td>
        <td>
            &ensp;
            <?php
                $w_date = substr($date, 0, 10);
                DayDisplay($w_date, $model);
            ?>

        </td>
    </tr>

    <tr>
        <td align='center'>申請者</td>
        <td>
            &ensp;
            <?php echo $model->getSyozoku($uid); ?>
            &emsp;
            <?php echo "社員番号：" . $uid; ?>
            &emsp;
            <?php echo '氏名：'. $model->getSyainName($uid); ?>
        </td>
    </tr>

    <tr>
        <td align='center'>期&ensp;間</td>
        <td>
            &ensp;
            <?php
                $w_date = substr($start_date, 0, 4) . '-' . substr($start_date, 4, 2) . '-' . substr($start_date, 6, 2);
                DayDisplay($w_date, $model);
                if($start_date != $end_date) {
                    echo " ～ ";
                    $end_date = substr($end_date, 0, 4) . '-' . substr($end_date, 4, 2) . '-' . substr($end_date, 6, 2);
                    DayDisplay($end_date, $model);
                }
            ?>
            &emsp;
            <?php
                if( $start_time ) echo $start_time;
                if( $start_time && $end_time ) echo " ～ ";
                if( $end_time ) echo $end_time;
            ?>
        </td>
    </tr>

    <tr>
        <td align='center'>内&ensp;容</td>
        <td>
            &ensp;
            <?php echo $content; ?>
        <br>
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
                <br>&emsp;&emsp;&emsp;<font color="red">※ Suica 利用する。</font>
<?php
} else {
?>
        <?php if( $ticket01 != "不要" && $ticket01 != "不可" && $ticket01 != NULL) { ?>
        <br>
                &emsp;&emsp;乗車券（氏家～宇都宮間）&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;
                <?php echo $ticket01; ?>
                <?php if( $ticket01 != "不要" ) echo $ticket01_set . "セット"; ?>
        <?php } ?>
        <?php if( $ticket02 != "不要" && $ticket02 != "不可" && $ticket02 != NULL ) { ?>
        <br>
                &emsp;&emsp;新幹線特急券自由席・乗車券（宇都宮～東京間）
                <?php echo $ticket02; ?>
                <?php if( $ticket02 != "不要" ) echo $ticket02_set . "セット"; ?>
        <?php } ?>
<?php
}
?>
        <br>
        <?php if( $doukousya != '---' ) { ?>
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
    </tr>

    <tr>
        <td align='center'>備&ensp;考</td>
        <td>
            &ensp;
            <?php echo $remarks; ?>
        </td>
    </tr>

    <tr>
        <td align='center'>連絡先</td>
        <td>
            &ensp;
            <?php echo $contact; ?>
            <?php if( $contact == "その他" ) echo "(" . $contact_other . ")"; ?>
            <?php if( $contact == "その他" || $contact == "出張先") echo "TEL:" . $contact_tel; ?>
        </td>
    </tr>

        </table>
        </td></tr>
    </table> <!----------------- ダミーEnd --------------------->
<br>

    <input type="submit" value="送信" name="submit" onClick='SetCheckFlag(this.value)' disabled>&emsp;
    <input type="submit" value="戻る" name="submit" onClick='SetCheckFlag(this.value)' >&emsp;
<?php if( $request->get("reappl") ) { ?>
    <input type="button" value="[×]閉じる" name="close" onClick='window.open("about:blank","_self").close()'>
<?php } else { ?>
    <input type="button" value="キャンセル" name="reset" onClick='location.replace("<?php echo $menu->out_self(), '?', $model->get_htmlGETparm() ?>");'>
<?php } ?>
</form>
</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
