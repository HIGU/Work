<?php
////////////////////////////////////////////////////////////////////////////////
// 定時間外作業申告（照会）                                                   //
//                                                    MVC View 部 リスト表示  //
// Copyright (C) 2021-2021 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2021/10/20 Created over_time_work_report_ViewInquiry.php                   //
// 2021/11/01 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////

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
<script type='text/javascript' language='JavaScript' src='over_time_work_report.js'></script>

</head>

<body onLoad='InitQuiry()'>

<center>
<?= $menu->out_title_border() ?>

<!-- ＰＤＦファイルを開く-->
    <div class='pt10' align='center'>
    <BR>※操作方法が分からない場合、<a href="download_file.php/定時間外作業申告_照会_マニュアル_New.pdf">定時間外作業申告（照会）マニュアル</a>を参考にして下さい。<BR>
    </div>
<!-- TEST Start.-->
    <?php if($debug) { ?>
    <div class='pt9' align='left'><font color='red'>※※※ ここから、テストの為表示  ※※※</font></div>
    ※現在のUID：<?php echo $login_uid; ?>　【テスト 切替】
    ALL：
    <input type='button' style='<?php if($login_uid=="017361") echo "background-color:yellow"; ?>' value='017361' onClick='CangeUID(this.value, "form_quiry");'>　
    複数課：
    <input type='button' style='<?php if($login_uid=="012394") echo "background-color:yellow"; ?>' value='012394' onClick='CangeUID(this.value, "form_quiry");'>　
    <input type='button' style='<?php if($login_uid=="017850") echo "background-color:yellow"; ?>' value='017850' onClick='CangeUID(this.value, "form_quiry");'>　
    <input type='button' style='<?php if($login_uid=="012980") echo "background-color:yellow"; ?>' value='012980' onClick='CangeUID(this.value, "form_quiry");'>　
    <input type='button' style='<?php if($login_uid=="016713") echo "background-color:yellow"; ?>' value='016713' onClick='CangeUID(this.value, "form_quiry");'>
    <BR><BR>
    各課：
    <input type='button' style='<?php if($login_uid=="300055") echo "background-color:yellow"; ?>' value='300055' onClick='CangeUID(this.value, "form_quiry");'>　
    <input type='button' style='<?php if($login_uid=="300349") echo "background-color:yellow"; ?>' value='300349' onClick='CangeUID(this.value, "form_quiry");'>　
    <input type='button' style='<?php if($login_uid=="300098") echo "background-color:yellow"; ?>' value='300098' onClick='CangeUID(this.value, "form_quiry");'>　
    <input type='button' style='<?php if($login_uid=="014524") echo "background-color:yellow"; ?>' value='014524' onClick='CangeUID(this.value, "form_quiry");'>　
    <input type='button' style='<?php if($login_uid=="018040") echo "background-color:yellow"; ?>' value='018040' onClick='CangeUID(this.value, "form_quiry");'>　
    <input type='button' style='<?php if($login_uid=="015202") echo "background-color:yellow"; ?>' value='015202' onClick='CangeUID(this.value, "form_quiry");'>　
    <input type='button' style='<?php if($login_uid=="016080") echo "background-color:yellow"; ?>' value='016080' onClick='CangeUID(this.value, "form_quiry");'>　
    <input type='button' style='<?php if($login_uid=="017507") echo "background-color:yellow"; ?>' value='017507' onClick='CangeUID(this.value, "form_quiry");'>　
    <input type='button' style='<?php if($login_uid=="017728") echo "background-color:yellow"; ?>' value='017728' onClick='CangeUID(this.value, "form_quiry");'>　
    <BR><div class='pt9' align='left'><font color='red'>※※※ ここまで、テストの為表示  ※※※</font></div>
    <?php } ?>
<!-- TEST End. -->
    <BR>
<form name='form_quiry' method='post' action='<?php echo $menu->out_self() ?>' onSubmit='return;'>
<!-- TEST Start.-->
    <input type='hidden' name='login_uid' value="<?php echo $login_uid; ?>">
<!-- TEST End. -->
    <input type='hidden' name='showMenu' id='id_showMenu' value='Quiry'>
    <input type='hidden' name='date_check' value=' checked'>
    <input type='hidden' name='deploy_check' value=' checked'>
    <input type='hidden' name='name_check' value=' checked'>
    <input type='hidden' name='z_contents_check' value=' checked'>
    <input type='hidden' name='z_state_check' value=' checked'>
    <input type='hidden' name='j_contents_check' value=' checked'>
    <input type='hidden' name='j_state_check' value=' checked'>
    <input type='hidden' name='remarks_check' value=' checked'>

    <table class='pt10' border="1" cellspacing="0">
    <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table width='100%' class='winbox_field' bgcolor='#D8D8D8' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr>
                <td class='winbox' style='background-color:yellow; color:blue;' colspan='2' align='center'>
                    <div class='caption_font'><?php echo $menu->out_caption(), "\n"?></div>
                </td>
            </tr>

        <!-- 早出 or 通常・休出の選択 -->
            <tr>
                <td nowrap align='center'>早出 or 通常・休出の選択</td>
                <td align='center'>
                    【
                    <select name='ddlist_v_type' onChange=''>
                        <option value='0' <?php echo $v_early; ?>>早出</option>
                        <option value='1' <?php echo $v_normal; ?>>通常・休出</option>
                    </select>
                    】
                </td>
            </tr>

        <!-- 作業日の指定 -->
            <tr>
                <td nowrap align='center'>作業日の指定</td>
                <td nowrap align='center'>
                    <input type='radio' name='days_radio' id='id_s_day' value='1' <?php if($d_radio==1) echo " checked"; ?> onclick='DaysSelect(this)'><label for='id_s_day'>単日</label>
                    <!-- 会社カレンダーの休日情報を、javascriptの変数へセットしておく。-->
                    <script> var holiday = '<?php echo $holiday; ?>';  SetHoliday(holiday);</script>
                    <select name='ddlist_year' id='id_year' onclick='WorkDateCopy()'>
                        <?php $model->getSelectOptionDate($def_y-1, $def_y+1, $def_y); ?>
                    </select>年
                    <select name='ddlist_month' id='id_month' onclick='WorkDateCopy()'>
                        <?php $model->getSelectOptionDate(1, 12, $def_m); ?>
                    </select>月
                    <select name='ddlist_day' id='id_day' onclick='WorkDateCopy()'>
                        <?php $model->getSelectOptionDate(1, 31, $def_d); ?>
                    </select>日
                    <font id='id_w_youbi'>（　）</font><BR>
                    <input type='hidden' name='w_date' id='id_w_date' value="<?php echo $request->get('w_date'); ?>">
                    <font id='id_range' <?php if($d_radio==1) echo " disabled"; ?>>～</font><BR>
                    <input type='radio' name='days_radio' id='id_e_day' value='2' <?php if($d_radio==2) echo " checked"; ?> onclick='DaysSelect(this)'><label for='id_e_day'>連日</label>
                    <font id='id_e_day_area' <?php if($d_radio==1) echo " disabled"; ?>>
                    <select name='ddlist_year2' id='id_year2' onclick='WorkDateCopy2()' <?php if($d_radio==1) echo " disabled"; ?>>
                        <?php $model->getSelectOptionDate($def_y2-1, $def_y2+1, $def_y2); ?>
                    </select>年
                    <select name='ddlist_month2' id='id_month2' onclick='WorkDateCopy2()' <?php if($d_radio==1) echo " disabled"; ?>>
                        <?php $model->getSelectOptionDate(1, 12, $def_m2); ?>
                    </select>月
                    <select name='ddlist_day2' id='id_day2' onclick='WorkDateCopy2()' <?php if($d_radio==1) echo " disabled"; ?>>
                        <?php $model->getSelectOptionDate(1, 31, $def_d2); ?>
                    </select>日
                    <font id='id_w_youbi2'>（　）</font>
                    </font>
                    <input type='hidden' name='w_date2' id='id_w_date2' value="<?php echo $request->get('w_date2'); ?>">
                </td>
            </tr>
            
        <!-- 部署の指定 -->
            <tr>
                <td nowrap align='center'>部署の選択</td>
                <td nowrap align='center' style='border:groove'>
                    <select name="ddlist_bumon">
                        <?php $model->setSelectOptionBumon($request); ?>
                    </select>
                </td>
            </tr>
            
        <!-- 社員番号の指定 -->
            <tr>
                <?php if(getCheckAuthority(63) || $model->IsKatyou() || $model->IsButyou() ) { ?> <!-- 63:社員番号入力可能（工場長、管理部、総務課）-->
                    <td align='center'>申請者（社員No.）の指定</td>
                    <td nowrap align='center'>
                        社員番号：<input type="text" size="8" maxlength="6" name="s_no" value="<?php echo $request->get('s_no') ?>" onkeyup="value=InputCheck(this);">
                    </td>
                <?php } else { ?>
                    <td align='center'>申請者（社員No.）</td>
                    <td nowrap align='center'>
                        <input type='hidden' name='s_no' value='<?php echo $login_uid; ?>'>
                        <p class='pt10'>※権限がない為、ログイン時の社員番号固定。</p>
                        <?php echo '社員番号：' . $login_uid; ?>
                    </td>
                <?php } ?>
            </tr>
            
        <!-- その他条件 -->
            <tr align='center'>
                <td nowrap colspan='1'>
                    その他条件
                </td>
                <td nowrap colspan='1'>
                    <input type='radio' name='mode_radio' id='1' <?php if($m_radio==1) echo " checked"; ?> onClick='' value='1'><label for='1'>指定なし</label>
                    <input type='radio' name='mode_radio' id='2' <?php if($m_radio==2) echo " checked"; ?> onClick='' value='2'><label for='2'>結果未入力</label>
                    <input type='radio' name='mode_radio' id='3' <?php if($m_radio==3) echo " checked"; ?> onClick='' value='3'><label for='3'>結果入力済み</label>
                    <input type='radio' name='mode_radio' id='4' <?php if($m_radio==4) echo " checked"; ?> onClick='' value='4'><label for='4'>承認待ち</label>
                </td>
            </tr>
            
        <!-- エラー条件 -->
            <tr align='center'>
                <td nowrap colspan='1'>
                    エラー条件
                </td>
                <td nowrap colspan='1'>
                    <input type='checkbox' name='err_check0' id='c0' <?php if($e_check0) echo " checked" ?> ><label for='c0'>なし</label>
                    <input type='checkbox' name='err_check1' id='c1' <?php if($e_check1) echo " checked" ?> ><label for='c1'>退勤時間</label>
                    <input type='checkbox' name='err_check2' id='c2' <?php if($e_check2) echo " checked" ?> ><label for='c2'>実作業前</label>
                    <input type='checkbox' name='err_check3' id='c3' <?php if($e_check3) echo " checked" ?> ><label for='c3'>規程時間外</label>
                </td>
            </tr>
            
            <tr align='center'>
                <td nowrap colspan='2'>
                <input type='submit' name='quiry_exec'  value='実行' onClick='return QuiryExec();'>　
                <input type='button' name='quiry_reset' value='リセット' onClick='location.replace("<?php echo $menu->out_self(), '?showMenu=Quiry' ?>");'>&emsp;
                </td>
            </tr>
            
        </table>
    </td></tr>
    </table> <!----------------- ダミーEnd --------------------->
</form>
<BR>※ ＩＳＯ事務局は、総務課に含まれています。<BR>　
</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
