<?php
////////////////////////////////////////////////////////////////////////////////
// 総合届（承認）                                                             //
//                                                    MVC View 部 リスト表示  //
// Copyright (C) 2020-2020 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2020/11/18 Created sougou_admit_ViewList.php                               //
// 2021/02/12 Release.                                                        //
// 2022/01/27 受電者の必要ない時、応対者："不要"登録でスキップするよう変更    //
// 2022/02/16 有休残情報の表示を追加 ※承認完了分は加算される                 //
////////////////////////////////////////////////////////////////////////////////

////////////// リターンアドレス設定
$menu->set_RetUrl(PER_APPLI_MENU);                // 通常は指定する必要はない

// 年月日のドロップダウンリスト作成
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

// 時刻のドロップダウンリスト作成
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

// 代理リスト作成
function SelectOptionAgent($model)
{
    $no_list = $model->getAgentList();
    if( !$no_list ) {
        echo "<option value='' selected>--------</option>";
        return;
    }
    $max = count($no_list);
    for ($i = 0; $i < $max ; $i++) {
        if ($i == 0) {
            echo "<option value='" . $no_list[$i][0] . "' selected>" . $model->getSyainName($no_list[$i][0]) . "</option>";
        } else {
            echo "<option value='" . $no_list[$i][0] . "'>" . $model->getSyainName($no_list[$i][0]) . "</option>";
        }
    }
}

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
<script type='text/javascript' language='JavaScript' src='sougou_admit.js'></script>

</head>

<body onLoad="Init(<?php echo $request->get('edit_no'); ?>)">
<center>
<?= $menu->out_title_border() ?>

<!-- 承認待ち情報 表示 --
<?php if( $model->getUid()=='300144' && ($rows_uid=$model->getAdmitUID($res_uid)) > 0 ) { ?>
<form name='form_send' method='post' action='<?php echo $menu->out_self(); ?>' onSubmit='return true;'>
    <input type='hidden' name='send_uid' id='id_send_uid' value=''>
</form>
    <table class='pt10' border="1" cellspacing="0">
    <tr><td>
        <table width='100%' class='winbox_field' bgcolor='#D8D8D8' align='center' border='1' cellspacing='0' cellpadding='3'>
            <caption style='background-color:DarkCyan; color:White;'><div class='caption_font'>承認待ち情報</div></caption>
            <tr style='background-color:yellow; color:blue;'>
                <td align='center'>承 認 者</td>
                <td align='center'>件　数</td>
                <td align='center'>通　知</td>
            </tr>
            <?php for($u=0; $u<$rows_uid; $u++) { ?>
                <?php $send_uid = $res_uid[$u][0]; ?>
            <tr>
                <td align='center'><?php echo $model->getSyainName($send_uid); ?></td>
                <td align='center'><?php echo $model->getAdmitCnt($send_uid); ?> 件</td>
                <td align='center'>
                    <?php if($model->getUid() != $send_uid ) { ?>
                        <?php echo "<input type='hidden' id='id_w_uid$u' value='$send_uid'>"; ?>
                        <input type='button' value='送信' onClick='SetSendInfo(<?php echo $u; ?>)'>
                    <?php } ?>
                </td>
            </tr>
            <?php } ?>
        </table>
    </tr></td>
    </table>
<?php } ?>
<!-- 承認待ち情報 表示 -->

<?php $menu->set_caption('下記の必要な条件を入力又は選択して下さい。'); ?>

<?php if( $model->IsAdmit() == 0) { ?>
    <br>現在、未承認の総合届はありません。<br><br>
    ※通常：未承認の総合届がある場合、<font color='red'>午前10:00</font> と <font color='red'>午後12:45</font>と <font color='red'>午後15:00</font> の計３回お知らせメールを配信しています。<br><br>
    ただし<font color='red'>【至急】</font>の場合、申請後 または 前任の方の承認後 お知らせメールが配信されるようになっています。
<?php } else { ?>
<!-- ＰＤＦファイルを開く-->
    <div class='pt10' align='center'>
    <br>※承認画面の使用方法が分からない場合は、<a href="download_file.php/総合届（承認）.pdf">総合届（承認）</a>の【画面の説明】をご確認ください。<br><br>
    </div>
<!-- -->
    <br>未承認の総合届一覧<br>
<?php
    $res = array(); 
    $indx = $model->getIndx();
    $rows = $model->getRows();
    $res = $model->getRes();
?>
<form name='form_admit' method='post' action='<?php echo $menu->out_self(); ?>'>
<input type='hidden' name='rows' value=<?php echo $rows; ?>>
<input type='hidden' name='indx' value=<?php echo $indx; ?>>

<input type='hidden' name='EditFlag'>

<?php for( $r=0; $r<$rows; $r++ ) { ?>
    <?php $posname = sprintf("res-%s[]", $r); ?>
    <?php for( $i=0; $i<$indx; $i++ ) { ?>
        <input type='hidden' name='<?php echo $posname; ?>' value='<?php echo $res[$r][$i]; ?>'>
    <?php } ?>
<?php } ?>

<?php
if( date('Ymd') > '20210630' ) {    // 回数券使用不可の対応
    echo "<input type='hidden' name='suica_view' value='on'>";
    $suica_view = 'on';
} else {
    $suica_view = '';
}
?>

<?php
    for ( $r=0; $r<$rows; $r++) {
        $date                   = $res[$r][0];
        $uid                    = $res[$r][1];
        $start_date             = trim($res[$r][2]);
        $start_time             = $res[$r][3];
        $end_date               = trim($res[$r][4]);
        $end_time               = $res[$r][5];
        $content                = trim($res[$r][6]);
        $yukyu                  = $res[$r][7];
        $ticket01               = trim($res[$r][8]);
        $ticket02               = trim($res[$r][9]);
        $special                = trim($res[$r][10]);
        if( $special == '慶弔A' ) {
            $special = "慶弔：本人が結婚 5日(在籍中1回)";
        } else if ( $special == '慶弔B' ) {
            $special = "慶弔：父母・配偶者・子が死亡 5日";
        } else if ( $special == '慶弔C' ) {
            $special = "慶弔：配偶者の父母、本人の祖父母、兄弟の死亡 3日";
        }
        $others                 = $res[$r][11];
        $place                  = $res[$r][12];
        $purpose                = $res[$r][13];
        $ticket01_set           = trim($res[$r][14]);
        $ticket02_set           = trim($res[$r][15]);
        $doukousya              = trim($res[$r][16]);
        $remarks                = $res[$r][17];
        $contact                = trim($res[$r][18]);
        $contact_other          = trim($res[$r][19]);
        $contact_tel            = $res[$r][20];
        $received_phone         = $res[$r][21];
        $received_phone_date    = $res[$r][22];
        $received_phone_name    = trim($res[$r][23]);
        $hurry                  = $res[$r][24];
        $ticket                 = $res[$r][25];
        $admit_status           = trim($res[$r][26]);
        $amano_input            = $res[$r][27];

        $reappl = $model->IsReAppl($date, $uid);
?>
    <table width='734' class='pt10' border="1" cellspacing="0">
        <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table width='100%' class='winbox_field' bgcolor='#D8D8D8' align='center' border='1' cellspacing='0' cellpadding='3'>

    <?php if( $reappl ) { ?>
        <caption class='pt12b' style='background-color:blue; color:white;'>
        <input type="submit" class='pt11b' style='background-color:blue; color:white; border:none' id=<?php echo 'id_title' . $r; ?> value="再申請" name=<?php echo "edit" . $r; ?> onClick='return EditRun(<?php echo $r; ?>);'>
        </caption>
    <?php } else if( trim($hurry) == "至急" ) { ?>
        <caption class='pt12b' style='background-color:#FF0040; color:white;'>
        >>>>>
        <input type="submit" class='pt11b' style='background-color:#FF0040; color:white; border:none' id=<?php echo 'id_title' . $r; ?> value="至急" name=<?php echo "edit" . $r; ?> onClick='return EditRun(<?php echo $r; ?>);'>
        <<<<<
        </caption>
    <?php } else { ?>
        <caption style='background-color:yellow; color:blue;'>
        <input type="submit" class='pt11b' style='background-color:yellow; color:blue; border:none' id=<?php echo 'id_title' . $r; ?> value="総合届" name=<?php echo "edit" . $r; ?> onClick='return EditRun(<?php echo $r; ?>);'>
        </caption>
    <?php } ?>
    <tr>
        <td align='center'>申請日</td>
        <td>
            &ensp;
            <?php
                DayDisplay(substr($res[$r][0], 0, 10), $model);
                echo substr($res[$r][0], 10, 6);
            ?>

        </td>
    </tr>

    <tr>
        <td nowrap align='center'>申請者</td>
        <td>
            &ensp;
            <?php echo $model->getSyozoku($uid); ?>
            &emsp;
            <?php echo "社員番号：" . $uid; ?>
            &emsp;
            <?php echo '氏名：'. $model->getSyainName($uid); ?>
            
            <div class='pt10' align='right'>
            <?php
            // 
            $model->setYukyu($uid);
            $model->setYotei(substr($res[$r][0], 0, 10), $uid);
            $yukyudata = $model->getYukyu();
            if( $yukyudata[0][4] == 0 ) {
                echo "【有給情報：<font color='red'>現在、メンテナンス中です</font>】";
            } else {
                echo "【有給残：<font color='red'>{$yukyudata[0][1]}</font>/{$yukyudata[0][0]}日】";
                echo "【半休：<font color='red'>{$yukyudata[0][2]}</font>/20回　時間休：<font color='red'>{$yukyudata[0][3]}</font>/{$yukyudata[0][4]}時間】※承認待ち除く";
            }
            ?>
            </div>

        </td>
    </tr>

    <tr>
        <td align='center'>期&ensp;間</td>
        <td>
            &ensp;
            <?php
                DayDisplay($start_date, $model);
                if($start_date != $end_date) {
                    echo " ～ ";
                    DayDisplay($end_date, $model);
                }
            ?>
            &emsp;
            <?php
//                echo $start_time . " ～ " . $end_time;
                if( $start_time ) echo $start_time;
                if( $start_time && $end_time ) echo " ～ ";
                if( $end_time ) echo $end_time;
            ?>
        </td>
    </tr>

    <?php $jyuden_skip = false; ?>
    <tr>
        <td align='center'>内&ensp;容</td>
        <td>
            &ensp;
            <?php echo $res[$r][6]; ?>
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
                <br>&emsp;&emsp;&emsp;<font color="red">※ Suica を利用します。</font>
<?php
} else {
?>
        <?php if( $ticket01 != "不要" && $ticket01 != "不可" && $ticket01 != NULL) { ?>
        <br>
                &emsp;&emsp;乗車券（氏家～宇都宮間）&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;
                <?php echo $ticket01; ?>
                <?php if( $ticket01 != "不要" ) echo $ticket01_set . "セット"; ?>
        <?php } ?>
<?php
}
?>
        <?php if( $ticket02 != "不要" && $ticket02 != "不可" && $ticket02 != NULL ) { ?>
        <br>
                &emsp;&emsp;新幹線特急券自由席・乗車券（宇都宮～東京間）
                <?php echo $ticket02; ?>
                <?php if( $ticket02 != "不要" ) echo $ticket02_set . "セット"; ?>
        <?php } ?>
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
            } else if( $content == "生理休暇" ) {
                ; // 生理休暇
            } else {
                $jyuden_skip = true; // IDカード系
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

    <?php $jyuden = false; ?>
    <?php if( $received_phone != '' ) { ?>
        <?php if( $received_phone_name == "不要" ) { ?>
        <?php     $jyuden = true; ?>
        <?php } else { ?>
        <script>Zigo(<?php echo $r; ?>);</script>
        <tr>
            <td align='center'>受電者</td>
            <td>
                    &ensp;
                    <?php
                        echo "応対日時：";
                        DayDisplay(substr($received_phone_date,0,10), $model);
                        echo substr($received_phone_date, 10, 6);
                        if( is_numeric($received_phone_name) ) {
                            echo " 応対者：" . $model->getSyainName($received_phone_name);
                        } else {
                            echo " 応対者：" . $received_phone_name;
                        }
                        $jyuden = true;
                    ?>
            </td>
        </tr>
        <?php } ?>
    <?php } else { ?>
        <?php
        if( $reappl ) {
            $previous_date = $model->GetPreviousDate($date, $uid);
            if( $previous_date == "" ) {
                $previous_date = $date;
            }
            $sin_dt = new DateTime($previous_date);             // 前申請日時
        } else {
            $sin_dt = new DateTime($date);                      // 申請日時
        }
        $str_dt = new DateTime("{$start_date} {$start_time}");  // 対象日時(開始)
        $end_dt = new DateTime("{$end_date} {$end_time}");      // 対象日時(終了)
        // 2021.09.06 申請時間より開始時間($str_dt)が前なら受電者を必要とする。ように変更
        // → 2021.09.27 出社後、有休取得時に受電者を表示しないよう $end_dt
        // → 2022.06.14 出社後、有休以外は受電者を表示するよう
        ?>
        <?php if( $content != "有給休暇" ) $end_dt = $sin_dt; // 2022.06.14 有休以外受電者表示する為 Add. ?>
        <?php if( $sin_dt >= $str_dt && $sin_dt >= $end_dt && !$jyuden_skip ) { ?>
        <script>ZigoOutai(<?php echo $r; ?>);</script>
        <tr title="受電者が必要ない場合、&#13;&#10;応対者を 不要 で登録して下さい。">
            <td align='center' style='color:Red;'>受電者</td>
            <td nowrap>
                    &emsp;応対日時：
                    <select name=<?php echo "ddlist_ye" . $r ?> id="id_ye" onblur="JyuDateCopy(<?php echo $r; ?>)">
                        <?php SelectOptionDate($str_dt->format('Y')-1, $str_dt->format('Y'), $str_dt->format('Y')); ?>
                    </select>年
                    <select name=<?php echo "ddlist_mo" . $r ?> id="id_mo" onblur="JyuDateCopy(<?php echo $r; ?>)">
                        <?php SelectOptionDate(1, 12, $str_dt->format('m')); ?>
                    </select>月
                    <select name=<?php echo "ddlist_da" . $r ?>  id="id_da" onblur="JyuDateCopy(<?php echo $r; ?>)">
                        <?php SelectOptionDate(1, 31, $str_dt->format('d')); ?>
                    </select>日
                    <select name=<?php echo "ddlist_ho" . $r ?> id="id_ho" onblur="JyuDateCopy(<?php echo $r; ?>)">
                        <?php SelectOptionTime(0, 23, 8); ?>
                    </select>時
                    <select name=<?php echo "ddlist_mi" . $r ?> id="id_mi" onblur="JyuDateCopy(<?php echo $r; ?>)">
                        <?php SelectOptionTime(0, 59, 30); ?>
                    </select>分
                    <input type='hidden' name=<?php echo "jyu_date" . $r ?> value=''>

                    応対者：<input type="text" size="17" maxlength="8" name=<?php echo "outai" . $r ?> onMouseover=<?php echo "Coment".$r.".style.visibility='visible'" ?> onMouseout=<?php echo "Coment".$r.".style.visibility='hidden'" ?> onkeydown='OutaiEnter(<?php echo $r; ?>)'>

                    <div id=<?php echo "Coment" . $r; ?> style="color:#000000; background:#e7e7e7; font-size='9pt'; position:absolute; top:; left:; height:70; width:80; padding:5; visibility:hidden; filter:alpha(opacity='80');">
                        [社員番号]<BR>　　or<BR>　[名前]<BR>　　or <BR>　<font style='color:Red;'> 不要</font>
                    </div>

                    <input type="submit" value="登録" name=<?php echo "received_phone_register" . $r ?> onClick='return ReceivedPhoneRegister(<?php echo $r; ?>)'>
                    <input type='hidden' name=<?php echo "jyu_register" . $r ?> value=''>
            </td>
        </tr>
        <?php } else { ?>
            <?php $jyuden = true; ?>
        <?php } ?>
    <?php } ?>
<!--
    <?php if( $received_phone != '' ) { ?>
    <tr>
        <td align='center'>※受電者</td>
        <td>
                <?php echo "連絡受けた日時：" . $received_phone_date; ?>
                <?php echo "応対者：" . $received_phone_name; ?>
        </td>
    </tr>
    <?php } ?>
<!-- -->
    <?php $model->getAdmit($request, $date, $uid); ?>

    <table width='100%' class='winbox_field' bgcolor='#D8D8D8' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='pt10' border="1" cellspacing="0">

        <table class='pt10' border="1" cellspacing="1" align='center' style='color:Green;'>
        <caption>承 認 状 況</caption>
        <tr align='center'>
<!-- -->
            <td>申請者</td>
            <td></td>
<!-- 
            <td><?php echo $model->getSyainName($uid); ?></td>
<!-- -->
<!-- 
            <td>氏 名</td>
<!-- -->
            <td>承認者</td>
            <td></td>
<!-- -->
            <td><?php echo $model->getSyainName($request->get('kakarityo')); ?></td>
            <td><?php echo $model->getSyainName($request->get('katyo')); ?></td>
            <td><?php echo $model->getSyainName($request->get('butyo')); ?></td>
            <td><?php if($request->get('somukatyo')!='------') echo "総務課長"; else echo "------"; ?></td>
            <td><?php if($request->get('kanributyo')!='------') echo "管理部長"; else echo "------"; ?></td>
            <td><?php if($request->get('kojyotyo')!='------') echo "工 場 長"; else echo "------"; ?></td>
        </tr>
        <tr align='center'>
<!-- -->
            <td>　</td>
            <td></td>
<!-- 
            <td><?php echo substr($res[$r][0], 0 ,10); ?></td>
<!-- -->
<!-- 
            <td>日 付</td>
<!-- -->
            <td>承認日</td>
            <td></td>
<!-- -->
<?php
            $kakar_date = $request->get('kakarityo_date');
            $katyo_date = $request->get('katyo_date');
            $butyo_date = $request->get('butyo_date');
            $soumu_date = $request->get('somukatyo_date');
            $kanri_date = $request->get('kanributyo_date');
            $kojyo_date = $request->get('kojyotyo_date');
?>
<!-- -->
            <td><?php if($kakar_date=='------') echo $kakar_date; else echo "<font color='Red'>$kakar_date</font>"; ?></td>
            <td><?php if($katyo_date=='------') echo $katyo_date; else echo "<font color='Red'>$katyo_date</font>"; ?></td>
            <td><?php if($butyo_date=='------') echo $butyo_date; else echo "<font color='Red'>$butyo_date</font>"; ?></td>
            <td><?php if($soumu_date=='------') echo $soumu_date; else echo "<font color='Red'>$soumu_date</font>"; ?></td>
            <td><?php if($kanri_date=='------') echo $kanri_date; else echo "<font color='Red'>$kanri_date</font>"; ?></td>
            <td><?php if($kojyo_date=='------') echo $kojyo_date; else echo "<font color='Red'>$kojyo_date</font>"; ?></td>
<!--
            <td><?php echo $request->get('katyo_date'); ?></td>
            <td><?php echo $request->get('katyo_date'); ?></td>
            <td><?php echo $request->get('butyo_date'); ?></td>
            <td><?php echo $request->get('somukatyo_date'); ?></td>
            <td><?php echo $request->get('kanributyo_date'); ?></td>
            <td><?php echo $request->get('kojyotyo_date'); ?></td>
<!-- -->
        </tr>
        <tr align='center'>
            <td><input type="checkbox" name=<?php echo 70000+$r; ?> onClick='setNgMail(<?php echo $r; ?>, 0);' checked disabled></td>
            <td></td>
            <td>否認メール</td>
            <td></td>
            <td><input type="checkbox" name=<?php echo 70000+$r; if($kakar_date=='------' ) echo " disabled"; else echo " checked"; ?> onClick='setNgMail(<?php echo $r; ?>, 1);'></td>
            <td><input type="checkbox" name=<?php echo 70000+$r; if($katyo_date=='------' ) echo " disabled"; else echo " checked"; ?> onClick='setNgMail(<?php echo $r; ?>, 2);'></td>
            <td><input type="checkbox" name=<?php echo 70000+$r; if($butyo_date=='------' ) echo " disabled"; else echo " checked"; ?> onClick='setNgMail(<?php echo $r; ?>, 3);'></td>
            <td><input type="checkbox" name=<?php echo 70000+$r; if($soumu_date=='------' ) echo " disabled" ?> onClick='setNgMail(<?php echo $r; ?>, 4);'></td>
            <td><input type="checkbox" name=<?php echo 70000+$r; if($kanri_date=='------' ) echo " disabled" ?> onClick='setNgMail(<?php echo $r; ?>, 5);'></td>
            <td><input type="checkbox" name=<?php echo 70000+$r; if($kojyo_date=='------' ) echo " disabled" ?> onClick='setNgMail(<?php echo $r; ?>, 6);'></td>
        </tr>
            <!-- 否認メール 隠しフラグ -->
            <input type='hidden' name=<?php echo 70000+$r . "_sinsei"; ?> value=true>
            <input type='hidden' name=<?php echo 70000+$r . "_kakari"; ?> value=<?php if($kakar_date!='------' ) echo "true"; ?>>
            <input type='hidden' name=<?php echo 70000+$r . "_katyo"; ?>  value=<?php if($katyo_date!='------' ) echo "true"; ?>>
            <input type='hidden' name=<?php echo 70000+$r . "_butyo"; ?>  value=<?php if($butyo_date!='------' ) echo "true"; ?>>
            <input type='hidden' name=<?php echo 70000+$r . "_soumu"; ?>>
            <input type='hidden' name=<?php echo 70000+$r . "_kanri"; ?>>
            <input type='hidden' name=<?php echo 70000+$r . "_kojyo"; ?>>
        </table>

        <p align='center'>
            <input type='hidden' name=<?php echo 90000+$r; ?>>
<!--
            <input type="submit" value="修正" name=<?php echo "edit" . $r; ?> onClick='EditRun(<?php echo $r; ?>);'>
<!--/**/-->
            <?php if( ($model->IsKatyou($model->getUid()) || $model->IsButyou($model->getUid()) ) && !$jyuden ) { ?>
<!-- -->
                <font color='DarkGray' >
                <input type="radio" name=<?php echo $r; ?> id=<?php echo 1000+$r; ?>  disabled>承認
                </font>
            <?php } else { ?>
                <input type="radio" name=<?php echo $r; ?> id=<?php echo 1000+$r; ?> onclick="AdmitSelect(this, <?php echo $r; ?>);" value="承認"><label for=<?php echo 1000+$r; ?>>承認</label>
            <?php } ?>
                <input type="radio" name=<?php echo $r; ?> id=<?php echo 5000+$r; ?> onclick="DenySelect(this, <?php echo $r; ?>);" value="否認"><label for=<?php echo 5000+$r; ?>>否認</label>
                <font color='DarkGray' id=<?php echo 55000+$r; ?>>※理由：</font><input type="text" size="30"  maxlength="40" name=<?php echo 10000+$r; ?> id=<?php echo 10000+$r; ?> value="" disabled onkeydown='ReasonEnter(<?php echo $r; ?>)'>
<!--/**/-->
<!--
            <input type="radio" name=<?php echo $r; ?> id=<?php echo 1000+$r; ?> onclick="DenyReason(<?php echo $r; ?>);" value="承認"><label for=<?php echo 1000+$r; ?>>承認</label>
            <input type="radio" name=<?php echo $r; ?> id=<?php echo 5000+$r; ?> onclick="DenyReason(<?php echo $r; ?>);" value="否認"><label for=<?php echo 5000+$r; ?>>否認</label>
            <font color='DarkGray' id=<?php echo 55000+$r; ?>>※否認理由：</font><input type="text" name=<?php echo 10000+$r; ?> id=<?php echo 10000+$r; ?> value="" disabled>
<!-- -->
        </p>
        <input type='hidden' name=<?php echo 15000+$r; ?>>
        <input type='hidden' name=<?php echo 20000+$r; ?>>

        </table>
        </td></tr>
    </table> <!----------------- ダミーEnd --------------------->

        </table>
        </td></tr>
    </table> <!----------------- ダミーEnd --------------------->
<br>

<?php } /* for() */ ?>

    <input type='hidden' name="edit_no" ?>

    <input type="button" value="承認一括選択" name="bulk_selection" onClick="BulkSelection(this, <?php echo $rows; ?>);" >&emsp;&emsp;
<?php /* if( $model->getUid() == '300144' || $model->getUid() == '300055' ) { */ ?>
    <input type="checkbox" name="next" id="id_next" onClick='SetValue(this);'><label for='id_next'>次の承認者へ通知</label>　
<?php /* } */ ?>
    <input type="submit" value="確定" name="submit" onClick='return onAdmit(<?php echo $rows; ?>)' >&emsp;
    <input type="button" value="リセット" name="reset" onClick='location.replace("<?php echo $menu->out_self(), '?', $model->get_htmlGETparm() ?>");'>
    <BR>　
<!--
    <?php if($request->get('c_agent') == '' && ($model->IsKatyou($model->getUid()) || $model->IsButyou($model->getUid())) ) { ?>
    <br><br>↓↓↓↓↓ テスト中 ↓↓↓↓↓<br><br>
    <input type="checkbox" name="c_agent" id="id_agent" value="" onClick='AgentCheck(this)'><label for="id_agent">代理承認</label>
    <font id="agent_select">
    <select id="ddlist">
        <?php SelectOptionAgent($model); ?>
    </select> 宛ての総合届を
    <input type="submit" value="表示" name="agent">
    </font>
    <?php } ?>
<!-- -->
</form>

<?php } /* if()*/ ?>

</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
