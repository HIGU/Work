<?php
////////////////////////////////////////////////////////////////////////////////
// 総合届（申請）                                                             //
//                                                    MVC View 部 リスト表示  //
// Copyright (C) 2020-2020 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2020/11/18 Created sougou_ViewList.php                                     //
//            承認の編集画面（sougou_admit_EditView.php）も必要に応じ同時修正 //
// 2021/02/12 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////

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

$menu->out_html_header();

if( $request->get('showMenu') == 'Re') {    // 再申請
    if( !$model->GetReViewData($request) ) {        // 前回申請情報取得
        ?>
        <script>alert("否認された申請情報の取得に失敗しました。"); window.open("about:blank","_self").close();</script>
        <?php
    }
    if( !$model->IsReApplPossible($request) ) {
        ?>
        <script>alert("既に、再申請済みです。"); window.open("about:blank","_self").close();</script>
        <?php
    }
    if( ! $model->IsDelPossible($request) ) {
        ?>
        <script>alert("既に、取消済みです。");window.open("about:blank","_self").close()</script>
        <?php         
    }
}

if( !$model->IsSyain() ) {
    $menu->set_caption('社員番号を入力して下さい。');
    $dis = " disabled";
} else {
    $menu->set_caption('下記の必要な条件を入力又は選択して下さい。');
    $dis = "";
}

    $res = array(); 
    $indx = $model->getIndx();
    $rows = $model->getRows();
    $res = $model->getRes();

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
        $remarks                = $request->get('bikoutext');       // 備考
        $contact                = $request->get('r6');              // 連絡先（ラジオ）
        $contact_other          = $request->get('tel_sonota');      // 連絡先（その他）
        $contact_tel            = $request->get('tel_no');          // 連絡先（TEL）

        $hurry                  = $request->get('c2');              // 至急（チェック）

?>
<input type='hidden' name='rows' value=<?php echo $rows; ?>>
<input type='hidden' name='indx' value=<?php echo $indx; ?>>

<?php for( $r=0; $r<$rows; $r++ ) { ?>
    <?php $posname = sprintf("res-%s[]", $r); ?>
    <?php for( $i=0; $i<$indx; $i++ ) { ?>
        <input type='hidden' name='<?php echo $posname; ?>' value='<?php echo trim($res[$r][$i]); ?>'>
    <?php } ?>
<?php } ?>

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
<script type='text/javascript' language='JavaScript' src='sougou.js'></script>

</head>

<?php if( $model->IsKeiyaku($syainbangou) ) { ?>
    <input type='hidden' name='keiyaku' id='id_keiyaku'>
<?php } ?>

<?php if($request->get('check_flag')=="replay") { ?>
<body onLoad='ReDisp()'>
<?php } else if( $request->get('showMenu') == 'Re') { ?>
<body onLoad='ReInit()'>
<?php } else { ?>
<body onLoad='Init(); setInterval("blink_disp(\"blink_item\")", 1000);'>
<?php } ?>

<center>
<?php if( $request->get("reappl") ) { ?>
<?php } else { ?>
    <?= $menu->out_title_border() ?>
<?php } ?>
<!-- ＰＤＦファイルを開く-->
    <div class='pt10' align='center'>
    <br>※申請方法が分からない場合、<a href="download_file.php/総合届（申請）.pdf">申請例</a> を参考に申請して下さい。<font color='red'>※注)</font>IE以外のブラウザでは正しく動作しません。<br><br>
<?php if( date('Ymd') > '20211001' ) {    // 回数券使用不可の対応 ?>
    <font color='red'>【お知らせ】</font><font id='blink_item'>[10/14 更新]</font>・<font color='red'>特別計画有休</font>を取得する際は「●有給休暇」→「●特別計画」を選択して下さい。<br><br>
<?php } else { ?>
    <font color='red'>【お知らせ】</font><font id='blink_item'>[ 7/ 1 更新]</font>・回数券廃止に伴い、Suica へ変更となります。（申請例：P.12 更新）<br><br>
<?php } ?>
    </div>
<!-- -->
    <table class='pt10' border="1" cellspacing="0">
    <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table width='100%' class='winbox_field' bgcolor='#D8D8D8' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr>
                    <!--  bgcolor='#ffffc6' 薄い黄色 --> 
                <td class='winbox' style='background-color:yellow; color:blue;' colspan='2' align='center'>
                    <div class='caption_font'><?php echo $menu->out_caption(), "\n"?></div>
                </td>
            </tr>
<!-- 申請日 -->
    <tr>
        <td nowrap align='center'>申請日</td>
        <td>
        &ensp;
            <script>sinseibi();</script>
            <?php
/* 個人情報の観点から表示しない方が良い。 */
            $yukyudata = $model->getYukyu();
            if( $model->IsSyain() && $yukyudata[0][4] == 0 ) {
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
                if( $model->IsSyain() ) {
//                    echo "<BR><font class='pt10' color='red'>　　　　　　　　　　　　　　　　　　　　　　※有給残は、申請日よりあとの予定分も含まれています。</font>";
                }
                $twork = $yukyudata[0][4] / 5;    // 就業時間 8 or 7
                if( $request->get('syainbangou') == '300349' && $request->get('act_id') == '670') {
                    $swork = 2; // 9:15 開始の人（商管：村上）
                } else {
                    $swork = 3; // 8:30 開始の人
                }
                $ework = $twork - $swork; // 
                $kyuka_jisseki = $model->KeikakuCnt() - $model->GetSpecialPlans(date('Ymd'));
                $kyuka_yotei_1 = $model->YoteiKyuka( date('Ymd'), true);
                $kyuka_yotei_2 = $model->YoteiKyuka( date('Ymd'), false);
            }

if( $request->get('syainbangou') == '300667' ) {
//echo "TEST : " . $kyuka_jisseki . " : ". $kyuka_yotei_1 . " : ". $kyuka_yotei_2;
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

    <form name="sinseisya" method="post" action='<?php echo $menu->out_self(), '?', $model->get_htmlGETparm() ?>'>
<!-- 申請者 -->
        <tr>
            <td nowrap align='center'>申請者</td>
            <td align='center'>
                <?php echo $request->get('syozoku'); ?>
                &emsp;
            <?php if( !$model->IsSyain() ) { ?>
                社員番号：<input type="text" size="8" maxlength="6" name="syainbangou" onkeyup="value = value.replace(/[^0-9]+/i,'');" onchange="submit()">
            <?php } else { ?>
                <?php echo "社員番号：" . $request->get('syainbangou'); ?>
            <?php } ?>
                &emsp;

                <?php echo '氏名：'.$request->get('simei'); ?>
            </td>
        </tr>
    </form>

    <?php $showMenu = 'Check' ?>
    <form name='form_sougou' method='post' action='<?php echo $menu->out_self(),"?showMenu=" . $showMenu ?>' onSubmit='return allcheck()'>

        <input type='hidden' name='sin_date' value='<?php echo $date; ?>'>
        <input type='hidden' name='sin_year' id='sin_year'>
        <input type='hidden' name='sin_month' id='sin_month'>
        <input type='hidden' name='sin_day' id='sin_day'>
        <input type='hidden' name='sin_hour' id='sin_hour'>
        <input type='hidden' name='sin_minute' id='sin_minute'>
        <input type='hidden' name='syain_no' value='<?php echo $request->get('syainbangou'); ?>'>
        <input type='hidden' name='approval' value='<?php echo $model->IsApproval(); ?>'>

<?php
if( date('Ymd') > '20210630' ) {    // 回数券使用不可の対応
        echo "<input type='hidden' name='suica_view' value='on'>";
        $suica_view = 'on';
} else {
        $suica_view = '';
}
?>

<!-- 期間 -->
    <?php if( !$model->IsSyain() || !$model->IsApproval() ) { ?>
        <tr disabled=true>
    <?php } else { ?>
        <tr>
    <?php } ?>
        <td nowrap align='center'>期&ensp;間</td>
        <!-- 会社カレンダーの休日情報を取得し、javascriptの変数へセットしておく。-->
        <?php $holiday = json_encode($model->getHolidayRang(date('Y')-1,date('Y')+1)); ?>
        <script> var holiday = '<?php echo $holiday; ?>';  SetHoliday(holiday);</script>
        <!-- -->
        <td align='center'>
            <input type="checkbox" name="c0" id="0" value="1日" <?php if($start_date == $end_date) echo " checked" ?> onclick="OneDay(this.checked);" <?php echo $dis; ?>><label for="0">1日</label>
                <?php
                    if( $start_date ) {
                        $def_y = substr($start_date, 0, 4);
                        $def_m = substr($start_date, 4, 2);
                        $def_d = substr($start_date, -2, 2);
                    } else {
                        $def_y = date('Y');
                        $def_m = date('m');
                        $def_d = date('d');
                    }
                ?>
            <select name="ddlist" id="id_syear" onclick="StartDateCopy()" <?php echo $dis; ?>>
                <?php SelectOptionDate(date('Y')-1, date('Y')+1, $def_y); ?>
            </select>年
            <select name="ddlist" id="id_smonth" onclick="StartDateCopy()" <?php echo $dis; ?>>
                <?php SelectOptionDate(1, 12, $def_m); ?>
            </select>月
            <select name="ddlist" id="id_sday" onclick="StartDateCopy()" <?php echo $dis; ?>>
                <?php SelectOptionDate(1, 31, $def_d); ?>
            </select>日
            <font id='id_s_youbi'></font>
            <input type='hidden' name='str_date' value='<?php echo $str_date; ?>'>

            <font id='id_1000' > ～&ensp;
                <?php
                    if( $end_date ) {
                        $def_y = substr($end_date, 0, 4);
                        $def_m = substr($end_date, 4, 2);
                        $def_d = substr($end_date, -2, 2);
                    } else {
                        $def_y = date('Y');
                        $def_m = date('m');
                        $def_d = date('d');
                    }
                ?>
            <select name="ddlist" id="id_eyear" onclick="EndDateCopy()" <?php echo $dis; ?>>
                <?php SelectOptionDate(date('Y')-1, date('Y')+1, $def_y); ?>
            </select>年
            <select name="ddlist" id="id_emonth" onclick="EndDateCopy()" <?php echo $dis; ?>>
                <?php SelectOptionDate(1, 12, $def_m); ?>
            </select>月
            <select name="ddlist" id="id_eday" onclick="EndDateCopy()" <?php echo $dis; ?>>
                <?php SelectOptionDate(1, 31, $def_d); ?>
            </select>日
            <font id='id_e_youbi'></font>
            <input type='hidden' name='end_date' value='<?php echo $end_date; ?>'>
            </font>
        <br><br>
                <?php
                    if( $start_time ) {
                        $def_y = substr($start_time, 0, 2);
                        $def_m = substr($start_time, -2, 2);
                    } else {
                        $def_y = 8;
                        $def_m = 30;
                    }
                ?>
            <font id='id_start_time_area'>
            <input type="radio" name="r0" id="001" <?php echo $dis; ?>><label for="001">開始</label>
            <select name="ddlist" id="id_shh" onblur="StartTimeCopy()" <?php echo $dis; ?>>
                <?php SelectOptionTime(0, 23, $def_y); ?>
            </select>時
            <select name="ddlist" id="id_smm" onblur="StartTimeCopy()" <?php echo $dis; ?>>
                <?php SelectOptionTime(0, 59, $def_m); ?>
            </select>分
            <input type='hidden' name='str_time' value='<?php echo $str_time; ?>'>
            </font>
            <font id='id_time_area'>
            ～
            </font>
                <?php
                    if( $end_time ) {
                        $def_y = substr($end_time, 0, 2);
                        $def_m = substr($end_time, -2, 2);
                    } else {
                        $def_y = 17;
                        $def_m = 15;
                    }
                ?>
            <font id='id_end_time_area'>
            <input type="radio" name="r0" id="002" <?php echo $dis; ?>><label for="002">終了</label>
            <select name="ddlist" id="id_ehh" onblur="EndTimeCopy()" <?php echo $dis; ?>>
                <?php SelectOptionTime(0, 23, $def_y); ?>
            </select>時
            <select name="ddlist" id="id_emm" onblur="EndTimeCopy()" <?php echo $dis; ?>>
                <?php SelectOptionTime(0, 59, $def_m); ?>
            </select>分
            <input type='hidden' name='end_time' value='<?php echo $end_time; ?>'>
            </font>

            <font id='id_time_sum_area'>
            <label for="001">開始</label> or <label for="002">終了</label>より<input type="text" size="2" maxlength="2" name="sum_hour" id="id_sum_hour" onkeyup="value = value.replace(/[^0-9]/,'');" <?php echo $dis; ?>>時間
            <input type="button" value="計算" name="sum" id="id_sum" onClick='TimeCalculation()' <?php echo $dis; ?>>
            </font>
        </td>
    </tr>

<!-- 内容 -->
    <?php if( !$model->IsSyain() || !$model->IsApproval() ) { ?>
        <tr disabled=true>
    <?php } else { ?>
        <tr>
    <?php } ?>
    <td nowrap align='center'>内&ensp;容</td>
    <td>
        <input type="radio" name="r1" id="101" onClick="syousai();" value="有給休暇" <?php if($content=="有給休暇") echo " checked"; ?>><label for="101">有給休暇</label>
        <input type="radio" name="r1" id="102" onClick="syousai();" value="AM半日有給休暇" <?php if($content=="AM半日有給休暇") echo " checked"; ?>><label for="102">AM半日有給休暇</label>
        <input type="radio" name="r1" id="103" onClick="syousai();" value="PM半日有給休暇" <?php if($content=="PM半日有給休暇") echo " checked"; ?>><label for="103">PM半日有給休暇</label>
        <input type="radio" name="r1" id="104" onClick="syousai();" value="時間単位有給休暇" <?php if($content=="時間単位有給休暇") echo " checked"; ?>><label for="104">時間単位有給休暇</label>
        <input type="radio" name="r1" id="105" onClick="syousai();" value="欠勤" <?php if($content=="欠勤") echo " checked"; ?>><label for="105">欠勤</label>
        <input type="radio" name="r1" id="106" onClick="syousai();" value="遅刻早退" <?php if($content=="遅刻早退") echo " checked"; ?>><label for="106">遅刻早退</label>
            <table class='pt10' border="1" cellspacing="1" align='center' id="1000">
            <caption></caption>
            <!-- 内容詳細 -->
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
        <div onclick="obj=document.getElementById('menu1').style; obj.display=(obj.display=='none')?'block':'none'; obj2=document.getElementById('id_menu1');obj2.innerHTML=(obj.display=='none')?'▼ 出張関連（クリックで展開）':'▲ 出張関連（クリックで縮小）';">
        <a class='pt12b' id='id_menu1' style="cursor:pointer;">▼ 出張関連（クリックで展開）</a>
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
        <p class='pt10' align='center' id="2000">
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
            行先：<input type="text" size="24" maxlength="24" name="ikisaki" value='<?php echo $others; ?>' onchange="value = SpecialText(this)">
            都道府県：<input type="text" size="10" maxlength="10" name="todouhuken" value='<?php echo $place; ?>'>
            目的：<input type="text" size="24" maxlength="24" name="mokuteki" value='<?php echo $purpose; ?>'>
<?php
}
?>
        </p>
        <p class='pt9' align='center' id="2500">
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
<!-- -->
<!-- 回数券が使用できなくなったら、以下をダミーで使用すればエラーは発生しない。 -->
<!--
            ※出張等で suica を使用する場合は、以下をご確認下さい。
            <input type='hidden' name='r3'> <input type='hidden' name='r4'>
            <input type='hidden' name='setto1'> <input type='hidden' name='setto2'>
<!-- -->
            同行者：<input type="text" size="80" maxlength="160" name="doukou" value='<?php echo $doukousya; ?>'></textarea>
<!-- 回数券が使用できなくなったら、コメントアウト -->
<!-- -->
        <br><br>※出張等で切符購入する場合は、別途 <a color="red" id="2550">切符購入依頼書</a> を提出して下さい。</p>
<!-- -->
<?php
}
?>
        </div>
        <!--// ここまでを折りたたむ -->

        <!-- 折りたたみ展開ボタン -->
        <div onclick="obj=document.getElementById('menu2').style; obj.display=(obj.display=='none')?'block':'none'; obj2=document.getElementById('id_menu2');obj2.innerHTML=(obj.display=='none')?'▼ 特別休暇関連（クリックで展開）':'▲ 特別休暇関連（クリックで縮小）';">
        <a class='pt12b' id='id_menu2' style="cursor:pointer;">▼ 特別休暇関連（クリックで展開）</a><font color='red' size='3'>※ワクチン接種はこちらをクリック</font>
        </div>
        <!--// 折りたたみ展開ボタン -->

        <!-- ここから先を折りたたむ -->
        <div id="menu2" style="display:none;clear:both;font-size:12pt;font-weight:normal;">

        <!--この部分が折りたたまれ、展開ボタンをクリックすることで展開します。-->
        <input type="radio" name="r1" id="112" onClick="syousai();" value="特別休暇" <?php if($content=="特別休暇") echo " checked"; ?>><label for="112">特別休暇</label>
            <table class='pt10' border="1" cellspacing="1" align='center' id="3000">
            <caption></caption>
            <!-- 内容詳細 -->
            <tr><td>
            <input type="radio" name="r5" id="501" onClick="toku()" value="慶弔A" <?php if($special=="慶弔A") echo " checked"; ?>><label for="501">慶弔：本人が結婚 5日(在籍中1回)</label>
            <br>
            <input type="radio" name="r5" id="502" onClick="toku()" value="慶弔B" <?php if($special=="慶弔B") echo " checked"; ?>><label for="502">慶弔：父母・配偶者・子が死亡 5日</label>
            <br>
            <input type="radio" name="r5" id="503" onClick="toku()" value="慶弔C" <?php if($special=="慶弔C") echo " checked"; ?>><label for="503">慶弔：配偶者の父母、本人の祖父母、兄弟の死亡 3日</label><label for="506"><font color='red' onclick='vaccine()'>　↓ワクチン接種</font></label>
            <br>
            <input type="radio" name="r5" id="504" onClick="toku()" value="公民権の行使" <?php if($special=="公民権の行使") echo " checked"; ?>><label for="504">公民権の行使</label>
            <input type="radio" name="r5" id="505" onClick="toku()" value="勤続満30年" <?php if($special=="勤続満30年") echo " checked"; ?>><label for="505">勤続満30年 5日</label>
            <input type="radio" name="r5" id="506" onClick="toku()" value="その他" <?php if($special=="その他") echo " checked"; ?>><label for="506">その他：<input type="text" size="30" maxlength="60" name="tokubetu_sonota" value='<?php echo $others; ?>'></label>
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

<!-- 備考 -->
    <?php if( !$model->IsSyain() || !$model->IsApproval() ) { ?>
        <tr disabled=true>
    <?php } else { ?>
        <tr>
    <?php } ?>
        <td nowrap align='center'>備&ensp;考</td>
        <?php
        $ua = getenv('HTTP_USER_AGENT');
        if(strstr($ua, 'Trident') || strstr($ua, 'MSIE')) { // Microsoft Internet Explorer
        ?>
        <td><input type="text" size="100" maxlength="40" name="bikoutext" value='<?php echo $remarks; ?>'> ※最大40字</td>
        <?php } else { ?>
        <td><input type="text" size="80" maxlength="40" name="bikoutext" value='<?php echo $remarks; ?>'> ※最大40字</td>
        <?php } ?>
    </tr>

<!-- 連絡先 -->
    <?php if( !$model->IsSyain() || !$model->IsApproval() ) { ?>
        <tr id='id_renraku' disabled=true>
    <?php } else { ?>
        <tr id='id_renraku'>
    <?php } ?>
        <td nowrap align='center'>連絡先</td>
        <td>
            <input type="radio" name="r6" id="601" onclick="telno();" value="携帯" <?php if($contact=="携帯") echo " checked"; ?>><label for="601">携帯</label>
            <input type="radio" name="r6" id="602" onclick="telno();" value="自宅" <?php if($contact=="自宅") echo " checked"; ?>><label for="602">自宅</label>
            <input type="radio" name="r6" id="603" onclick="telno();" value="出張先" <?php if($contact=="出張先") echo " checked"; ?>><label for="603">出張先</label>
            <input type="radio" name="r6" id="604" onclick="telno();" value="その他" <?php if($contact=="その他") echo " checked"; ?>><label for="604">その他：<input type="text" size="8" maxlength="8" name="tel_sonota" value='<?php echo $contact_other; ?>'></label>
            <font id='id_tel_no'>TEL</font><input type="text" name="tel_no" maxlength="13" onkeyup="value = value.replace(/[^0-9,-]+/i,'');" value='<?php echo $contact_tel; ?>'>
        </td>
    </tr>

<!-- 受電者 -->
    <input type='hidden' name='jyu_date' value='<?php echo $request->get("jyu_date"); ?>'>
    <input type='hidden' name='outai' value='<?php echo $request->get("outai"); ?>'>
<!--
    <?php if( !$model->IsSyain() || !$model->IsApproval() ) { ?>
        <tr id='id_jyuden' disabled=true>
    <?php } else { ?>
        <tr id='id_jyuden'>
    <?php } ?>
        <td nowrap align='center'>※受電者</td>
        <td>
            連絡受けた日時：
                <select name="ddlist_jyu" id="id_jyear" onblur="JyuDateCopy()">
                    <?php SelectOptionDate(date('Y')-1, date('Y')+1, date('Y')); ?>
                </select>年
                <select name="ddlist_jyu" id="id_jmonth" onblur="JyuDateCopy()">
                    <?php SelectOptionDate(1, 12, date('m')); ?>
                </select>月
                <select name="ddlist_jyu" id="id_jday" onblur="JyuDateCopy()">
                    <?php SelectOptionDate(1, 31, date('d')); ?>
                </select>日
                <select name="ddlist_jyu" id="id_jhh" onblur="JyuDateCopy()">
                    <?php SelectOptionTime(0, 23, 8); ?>
                </select>時
                <select name="ddlist_jyu" id="id_jmm" onblur="JyuDateCopy()">
                    <?php SelectOptionTime(0, 59, 30); ?>
                </select>分
                <input type='hidden' name='jyu_date' value=''>

            応対者：<input type="text" size="16" maxlength="8" name="outai">
        </td>
    </tr>
<!-- -->

        </table>
    </td></tr>
    </table> <!----------------- ダミーEnd --------------------->

    <br>承認ルート：<?php if($model->IsSyain()) echo $model->getApproval(); ?><br>

    <p align='center'>
        <input type="checkbox" name="c2" id="idc2" value="至急" <?php if($hurry=="至急") echo " checked"; ?>><label for="idc2" id="idc2l" >至急</label>
        <input type="submit" value="確認画面へ" name="submit" onClick='return IsAMandTimeVacation()'>　
<?php if( $request->get("reappl") ) { ?>
        <input type="button" value="[×]閉じる" name="close" onClick='window.open("about:blank","_self").close()'>
<?php } else { ?>
        <input type="button" value="キャンセル" name="cancel" onClick='location.replace("<?php echo $menu->out_self(), '?', $model->get_htmlGETparm() ?>");'>
<?php } ?>
    </p>
        <input type='hidden' name='reappl' value='<?php echo $request->get("reappl"); ?>'>
        <input type='hidden' name='deny_uid' value='<?php echo $request->get("deny_uid"); ?>'>
        <input type='hidden' name='previous_date' value='<?php echo $request->get("previous_date"); ?>'>
    </form>
    <BR>　
</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
