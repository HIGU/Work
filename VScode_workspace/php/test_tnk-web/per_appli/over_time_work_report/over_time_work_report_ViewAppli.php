<?php
////////////////////////////////////////////////////////////////////////////////
// 定時間外作業申告（申請）                                                   //
//                                                    MVC View 部 リスト表示  //
// Copyright (C) 2021-2021 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2021/10/20 Created over_time_work_report_ViewAppli.php                     //
// 2021/11/01 Release.                                                        //
// 2022/03/14 [退勤時間]表示の追加                                            //
// 2022/03/23 [延長及び、残業無し]チェックボックスの追加                      //
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
<script type='text/javascript' language='JavaScript' src='UsefulFunctions.js'></script>

<?php if($debug) { ?>
<div id='test'></div>
<script> CreatNumberList('test', 'ddl_num', 1, 12, 8); TEST(); </script>
<?php } ?>

</head>

<body onLoad='Init()'>

<center>
<?= $menu->out_title_border() ?>

    <BR><div class='pt9' align='right'>運用開始日：2021年12月13日（月）</div>
<!-- ＰＤＦファイルを開く -->
    <div class='pt10' align='center'>
    <BR>※操作方法が分からない場合、<a href="download_file.php/定時間外作業申告_入力_マニュアル_New.pdf">定時間外作業申告（入力）マニュアル</a> を参考にして下さい。<BR>
    </div>
<!-- TEST Start.-->
    <?php if($debug) { ?>
    <div class='pt9' align='left'><font color='red'>※※※ ここから、テストの為表示  ※※※</font></div>
    ※現在のUID：<?php echo $login_uid; ?>　【テスト 切替】
    ALL：
    <input type='button' style='<?php if($login_uid=="017361") echo "background-color:yellow"; ?>' value='017361' onClick='CangeUID(this.value, "form_appli");'>　
    複数課：
    <input type='button' style='<?php if($login_uid=="012394") echo "background-color:yellow"; ?>' value='012394' onClick='CangeUID(this.value, "form_appli");'>　
    <input type='button' style='<?php if($login_uid=="017850") echo "background-color:yellow"; ?>' value='017850' onClick='CangeUID(this.value, "form_appli");'>　
    <input type='button' style='<?php if($login_uid=="012980") echo "background-color:yellow"; ?>' value='012980' onClick='CangeUID(this.value, "form_appli");'>　
    <input type='button' style='<?php if($login_uid=="016713") echo "background-color:yellow"; ?>' value='016713' onClick='CangeUID(this.value, "form_appli");'>
    <BR><BR>
    各課：
    <input type='button' style='<?php if($login_uid=="300055") echo "background-color:yellow"; ?>' value='300055' onClick='CangeUID(this.value, "form_appli");'>　
    <input type='button' style='<?php if($login_uid=="017370") echo "background-color:yellow"; ?>' value='017370' onClick='CangeUID(this.value, "form_appli");'>　
    <input type='button' style='<?php if($login_uid=="300098") echo "background-color:yellow"; ?>' value='300098' onClick='CangeUID(this.value, "form_appli");'>　
    <input type='button' style='<?php if($login_uid=="014524") echo "background-color:yellow"; ?>' value='014524' onClick='CangeUID(this.value, "form_appli");'>　
    <input type='button' style='<?php if($login_uid=="018040") echo "background-color:yellow"; ?>' value='018040' onClick='CangeUID(this.value, "form_appli");'>　
    <input type='button' style='<?php if($login_uid=="015202") echo "background-color:yellow"; ?>' value='015202' onClick='CangeUID(this.value, "form_appli");'>　
    <input type='button' style='<?php if($login_uid=="016080") echo "background-color:yellow"; ?>' value='016080' onClick='CangeUID(this.value, "form_appli");'>　
    <input type='button' style='<?php if($login_uid=="017507") echo "background-color:yellow"; ?>' value='017507' onClick='CangeUID(this.value, "form_appli");'>　
    <input type='button' style='<?php if($login_uid=="017728") echo "background-color:yellow"; ?>' value='017728' onClick='CangeUID(this.value, "form_appli");'>　
    <BR><div class='pt9' align='left'><font color='red'>※※※ ここまで、テストの為表示  ※※※</font></div>
    <?php } ?>
<!-- TEST End. -->
    <BR>
<form name='form_appli' method='post' action='<?php echo $menu->out_self() ?>' onSubmit='return true;'>
<!-- TEST Start.-->
    <input type='hidden' name='login_uid' value="<?php echo $login_uid; ?>">
<!-- TEST End. -->
    <input type='hidden' name='showMenu' id='id_showMenu' value='Appli'>
    <input type='hidden' name='list_view' id='id_list_view' value='<?php echo $list_view; ?>'>
    <input type='hidden' name='appli' id='id_appli' value=''>
    
    <table class='pt10' border="1" cellspacing="0">
    <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table width='100%' class='winbox_field' bgcolor='#D8D8D8' align='center' border='1' cellspacing='0' cellpadding='3'>
            <!-- キャプション -->
            <tr>
                <td class='winbox' style='background-color:yellow; color:blue;' colspan='3' align='center'>
                    <div class='caption_font'><?php echo $menu->out_caption(), "\n"?></div>
                </td>
            </tr>

            <!-- 会社カレンダーの休日情報を、javascriptの変数へセットしておく。-->
            <script> var holiday = '<?php echo $holiday; ?>';  SetHoliday(holiday);</script>

            <tr>
                <td nowrap align='center'>
                    <input type='button' name='before' id='id_before' value='＜' onClick='setNextDate(this)'>
                    <?php
                    echo "　作業日：";
                    if( $list_view != 'on' ) {
                        echo "<select name='ddlist_year' id='id_year' onclick='WorkDateCopy()'>";
                        $model->getSelectOptionDate($def_y-1, $def_y+1, $def_y);
                        echo "</select>年";
                        echo "<select name='ddlist_month' id='id_month' onclick='WorkDateCopy()'>";
                        $model->getSelectOptionDate(1, 12, $def_m);
                        echo "</select>月";
                        echo "<select name='ddlist_day' id='id_day' onclick='WorkDateCopy()'>";
                        $model->getSelectOptionDate(1, 31, $def_d);
                        echo "</select>日";
                    } else {
                        echo $def_y . "年　" . $def_m . "月　" . $def_d . "日";
                        echo "<input type='hidden' name='ddlist_year' id='id_year' value='$def_y'>";
                        echo "<input type='hidden' name='ddlist_month' id='id_month' value='$def_m'>";
                        echo "<input type='hidden' name='ddlist_day' id='id_day' value='$def_d'>";
                    }
                    ?>
                    <font id='id_w_youbi'>(　)</font>
                    <input type='hidden' name='w_date' id='id_w_date' value="<?php echo $date; ?>">
                    <input type='button' name='after' id='id_after' value='＞' onClick='setNextDate(this)'>
                    <BR>【
                    <?php
                    if( $list_view != 'on' ) {
                        echo "<select name='ddlist_v_type' onChange=''>";
                        echo "  <option value='0' $v_early>早出</option>";
                        echo "  <option value='1' $v_normal>通常・休出</option>";
                        echo "</select>";
                    } else {
                        if( $v_early )  echo "早出";
                        if( $v_normal ) echo "通常・休出";
                        echo "<input type='hidden' name='ddlist_v_type' value='$v_type'>";
                    }
                    ?>
                    】
                </td>
                <td nowrap>
                    <?php
                    echo "　部署名：";
                    if( $list_view != 'on' ) {
                        echo "<select name='ddlist_bumon' onChange='DDBumon()'>";
                            $model->setSelectOptionBumon($request);
                        echo "</select>";
                    } else {
                        echo $bumon;
                        echo "<input type='hidden' name='ddlist_bumon' value='$bumon'>";
                    }
                    ?>
                </td>
                <td nowrap align='center'>
                    <?php
                    if( $list_view != 'on' ) {
                        echo "<input type='button' name='read' id='id_read' value='読み込み' onClick='SetViewON(this)'>";
                    } else {
                        echo "<input type='button' name='cancel' id='id_cancel' value='キャンセル' onClick='SetViewOFF()'>";
                    }
                    ?>
                </td>
            </tr>

            <tr>
                <td nowrap colspan='3'>
                    <p class='pt9'>
                    ・残業理由および実施業務内容は詳しく記入し、記入後は課長承認を得た上で、<font color='red'>午後3時迄</font>に部門長経由総務課まで提出して下さい。
                    </p>
                </td>
            </tr>

            <tr>
                <td nowrap colspan='2'>
                    <p class='pt10'>
                    ※平日のパート・契約社員等 17：15 までの<font color='red'>延長</font>はすべて、課長承認<BR>
                    　17：30 以降の<font color='red'>残業</font>に関しては以下の基準を適用<BR>
                    ≪課 長 承認≫　月、火、木 1時間までの残業<BR>
                    ≪部 長 承認≫　月、火、木 1時間を超える残業　<課長コメント要><BR>
                    ≪工場長承認≫　水、金 残業および休日出勤　 　<課長・部長コメント要><BR>
                    </p>
                </td>
                <td nowrap align='center'>
                    <?php
                    if( $list_view != 'on' ) {
                        echo "<input type='button' value='登　録' disabled='true'>";
                    } else {
                        echo "<input type='submit' name='commit' id='id_commit' value='登　録' onClick='return IsUpDate();'>";
                    }
                    ?>
                </td>
            </tr>

            <?php if( $list_view == 'on' ) { ?>
            <tr>
                <td nowrap class='pt10' colspan='3'>
                【内容を変更したとき】[登録]ボタンをクリックしないと変更内容は<font style='color:red;'>保存されません!!</font><BR>
                【事前申請のリミット】作業日の<font style='color:red;'><?php echo $time_limit; ?></font>まで。※取り消しも可能。<font style='color:red;'><?php echo $time_limit; ?></font>以降は残業結果報告のみ可能。<BR>
                【早出と通常残業両方】<font style='color:red;'>両方行う場合</font>、通常残業の時間を指定し、早出時間は理由（内容）へ入力すること。<BR>
                【残業しなかったとき】残業結果報告の開始と終了の時間を<font style='color:red;'>同じにして登録して下さい。</font><BR>
                <!-- 折りたたみ展開ボタン -->
                <div onclick="obj=document.getElementById('menu1').style; obj.display=(obj.display=='none')?'block':'none'; obj2=document.getElementById('id_menu');obj2.innerHTML=(obj.display=='none')?'▼各ボタンの説明（クリックで展開）':'▲各ボタンの説明（クリックで縮小）';">
                <a class='pt10b' id='id_menu' style="cursor:pointer;">▼各ボタンの説明（クリックで展開）</a>
                </div>
                <!--// 折りたたみ展開ボタン -->
                <!-- ここから先を折りたたむ -->
                <div id="menu1" style="display:none;clear:both;font-size:10pt;font-weight:normal;">
                【状態の各ボタン説明】<BR>
                　[－－]：時間と理由（内容）を初期化する。<BR>
                　　　　　※残業結果報告側(事前申請あり)なら[中止]へ切り替わり開始と終了に同時間をセットする。<BR>
                　[完了]：取り消し画面へ遷移する。<BR>
                　[途中]：取り消し画面へ遷移する。<BR>
                　[否認]：ボタンの効果、特になし。<font style='color:red;'>※否認内容を訂正し再度、登録して下さい。</font><BR>
                　[中止]：時間と内容を初期化する。<BR>
                【その他のボタン説明】<BR>
                　[コピー実行]：元の列より選択(1つ)したデータを、先の列より選択(複数可)した所へコピーする。<BR>
                　[先]：先の列にあるチェックボックスのチェックを全て付ける、または外す。<BR>
                　[->]：事前申請のデータを残業結果報告へコピーする。（[all]：[->]を全て実行）<BR>
                <!--この部分が折りたたまれ、展開ボタンをクリックすることで展開します。-->
                </div>
                <!--// ここまでを折りたたむ -->
                </td>
            </tr>
            <?php } ?>
        </table>
    </td></tr> <!----------------- ダミーEnd --------------------->
    </table>

<!-- ここから下は、一覧リストを表示する部分 -->
<?php if( $list_view != 'on' ) { ?>
    <BR>
    <?php //if($debug) { ?>
    <!-- 読み込み前は、事前申請なし規程時間外打刻者リストを表示 -->
    <?php if( $model->ViewNotAppliEarly() > 0 ) echo "<BR>"; ?>
    <?php if( $model->ViewNotAppli() > 0 ) echo "<BR>"; ?>
    <?php //} ?>
    <!-- 読み込み前は、未入力者リストを表示 -->
    <?php if( $model->ViewNotReportedList(0) > 0 ) echo "<BR>"; ?>
    <?php if( $model->ViewNotReportedList(1) > 0 ) echo "<BR>"; ?>
<?php } else { ?>
    <?php
    if( $view_data ) {  // 読込みデータ保存、書き込み前に比較するため
        $fiels = count($field);
        echo "<input type='hidden' name='fiels' id='id_fiels' value='$fiels'>";
        for( $r=0; $r<$rows; $r++ ) {
            for( $f=0; $f<$fiels; $f++ ) {
                echo "<input type='hidden' name='res{$r}_{$f}' id='id_res{$r}_{$f}' value='{$res[$r][$f]}'>";
            }
        }
    }
    ?>
    <?php if( !$limit_over ) {  // 申請可能時間 ?>
    <font class='pt10'>※残業結果報告欄は、<font style='color:red;'>作業日の<b>17：15</b>以降に表示。</font>事前申請の承認が済んでいれば入力可能。</font>
    <?php } else { ?>
    <font class='pt10'>※残業結果報告は、翌出勤日の午前中には済ませるよう心がけましょう。</font>
    <BR><BR>【事前申請したが延長・残業しなかった場合は、<font style='color:red;'><b>延長及び残業なし</b></font>に、チェックを入れて登録すること。】
    <?php } ?>
    <input type='hidden' name='rows' id='id_rows' value='<?php echo $rows ?>'>
    <input type='hidden' name='v_data' id='id_v_data' value='<?php echo $view_data ?>'>
    <table class='pt10' border="1" cellspacing="0">
    <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table width='100%' class='winbox_field' bgcolor='#D8D8D8' align='center' border='1' cellspacing='0' cellpadding='3'>
<!-- キャプション -->
            <?php if( $model->IsHoliday($date) ) { ?>
            <tr>
                <td class='winbox' style='background-color:red; color:white;' colspan='12' align='center'>
                    <div class='caption_font'>【休日出勤】</div>
                </td>
            </tr>
                <?php $style_color = 'color:red;'   // 休出は、文字色を赤 ?>
            <?php } else { ?>
                <?php $style_color = 'color:black;' // 通常は、文字色を黒 ?>
            <?php } ?>

            <tr>
                <td class='winbox' style='background-color:yellow; color:blue;' colspan='6' align='center'>
                    <div class='caption_font'>事前申請</div>
                </td>
                <?php if( !$limit_over ) {  // 申請可能時間 ?>
                    <?php if( $v_early ) { ?>
                    <?php } else { ?>
                <td nowrap align='center' colspan='5' rowspan='2'>固定時間セット<BR><font class='pt10'>※ボタンクリックで時間セット可能</font></td>
                    <?php } ?>
                <?php } ?>
                <?php if( $limit_over ) {  // 申請可能時間を過ぎている ?>
                <td class='winbox' style='background-color:yellow; color:blue;' align='center'>
                    <div class='caption_font'>copy</div>
                </td>
                <td class='winbox' style='background-color:yellow; color:blue;' colspan='6' align='center'>
                    <div class='caption_font'>残業結果報告</div>
                </td>
                <?php } ?>
            </tr>

<!-- 対象者一覧表示 -->
            <tr>
                <td nowrap align='center'>元</td>
                <td nowrap align='center'><input type='button' name='exec' id='id_exec' value='コピー実行' onClick='RadioToCheck(<?php echo $rows; ?>)'></td>
                <td nowrap align='center'><input type='button' name='all_check' id='id_all_check' value='先,' onClick='AllCheck(this, <?php echo $rows; ?>)'></td>
                <td nowrap align='center'>状態</td>
                <td nowrap align='center'>予定時間</td>
                <td nowrap align='center'>残業実施理由</td>
                <?php if( $limit_over ) {   // 申請可能時間を過ぎている ?>
                <td nowrap align='center'><input type='button' name='all_copy' id='id_all_copy' value='all' onClick='YoteiToJissekiAll(<?php echo $rows; ?>)'></td>
                <td nowrap align='center'>状態</td>
                <td nowrap align='center'><?php if( $v_early ) echo "出勤時間"; else echo "退勤時間"; ?></td>
                <td nowrap align='center'>延長及び<BR>残業なし</td>
                <td nowrap align='center'>実際作業時間</td>
                <td nowrap align='center'>実施業務内容</td>
                <?php } ?>
            </tr>
            <input type='hidden' name='cancel_uid' id='id_cancel_uid' value=''>
            <input type='hidden' name='cancel_uno' id='id_cancel_uno' value=''>
            <input type='hidden' name='type' id='id_type' value=''>

            <?php $comment = array('',''); // [0]課長コメント、[1]部長コメント ?>
            <?php for( $n=0; $n<$rows; $n++ ) { ?>
                <?php
                if( ! $limit_over ) {    // 当日の17:15よりまえ
                    $yo_disa = '';          // 有効
                    $ji_disa = ' disabled'; // 無効にする
                } else {
                    $yo_disa = ' disabled'; // 無効にする
                    $ji_disa = '';          // 有効
                }
                $status = $model->getApplStatus('yo', $view_data, $res, $n);    // 予定の状態取得
                $status_yo = $status;
                ?>
            <tr>
            <!-- 事前申請 -->
                <!-- コピー元 -->
                <td nowrap align='center'><input type='radio' name='radioNo' id='id_radio<?php echo $n; ?>' value='' onclick='RadioCheck(this, <?php echo $n; ?>)'></td>

                <?php
                $id_error = $id_error_style = "";
                if( $view_data && $date < date('Ymd') && ($res[$n][4] || $res[$n][16]) ) {
                    $date_2 = $def_y . "-" . $def_m . "-" . $def_d;
                    if( $model->getWorkingStrTime($res[$n][3], $date_2) == "0000" ) $id_error  = "[出勤] ";   // 出勤時間
                    if( $model->getWorkingEndTime($res[$n][3], $date_2) == "0000" ) $id_error .= "[退勤] ";   // 退勤時間
                    if( $id_error ) {
                        $id_error .= "IDカードを通していません。※総合届を提出して下さい。";
                        $id_error_style = "style='background-color:red; color:white;'";
                    }
                }
                ?>
                <!-- 氏名 -->
                <td nowrap title='<?php echo $id_error; ?>' <?php echo $id_error_style;?> onclick='MsgView("<?php echo $id_error;?>");'>
                    <?php
                    if($view_data) {
                        $uno = $res[$n][2];
                        $uid = $res[$n][3]; // 定時間外作業申告の登録データ
                        if( $comment[0] == "" && $res[$n][14] != "" ) $comment[0] = $res[$n][14];
                        if( $comment[1] == "" && $res[$n][15] != "" ) $comment[1] = $res[$n][15];
                    } else {
                        $uno = 0;
                        $uid = $res[$n][0]; // 所属部門の一覧データ
                    }
                    echo $name = trim($model->getName($uid));
                    echo "<input type='hidden' name='uid$n' id='id_uid$n' value='$uid'>";
                    echo "<input type='hidden' name='simei$n' id='id_simei$n' value='$name'>";
                    ?>
                </td>

                <!-- コピー先 -->
                <td nowrap align='center'>
                    <?php
                    if( $status == '－－' ) {
                        echo "<input type='checkbox' name='check$n' id='id_check$n' onclick='CheckFlag(this)'>";
                    } else {
                        echo "<input type='checkbox' name='check$n' id='id_check$n' onclick='CheckFlag(this)' disabled>";
                    }
                    ?>
                </td>

                <?php
                if($view_data && $res[$n][4]) {
                    $def_s_h = $res[$n][4]; $def_s_m = $res[$n][5]; // 開始 時 分 セット
                    $def_e_h = $res[$n][6]; $def_e_m = $res[$n][7]; // 終了 時 分 セット
                    $content = $res[$n][8]; // 内容 セット
                } else {
                    $def_s_h = -1; $def_s_m = -1;
                    $def_e_h = -1; $def_e_m = -1;
                    $content = '';
                }
                ?>

                <!-- 申請状態 -->
                <td nowrap align='center'>
                    <?php
                    echo "<input type='button' id='1' $yo_disa value='$status' onClick='return ReportEdit(this, $n, $uid, $uno);'>";
                    if( $status != '－－' && $status != '否認' ) {   // 初期状態以外
                        $yo_disa = ' disabled'; // 無効にする
                    }
                    ?>
                </td>

                <!-- 予定時間 -->
                <td nowrap align='center'>
                    <select style='<?php echo $style_color; ?>' name='ddlist_y_s_h<?php echo $n; ?>' id='id_y_s_h<?php echo $n; ?>' <?php echo $yo_disa; ?>>
                        <?php $model->setSelectOptionTime(0, $hor_max, $def_s_h); ?>
                    </select>
                    :
                    <select style='<?php echo $style_color; ?>' name='ddlist_y_s_m<?php echo $n; ?>' id='id_y_s_m<?php echo $n; ?>' <?php echo $yo_disa; ?>>
                        <?php $model->setSelectOptionTime(0, 59, $def_s_m); ?>
                    </select>
                    ～
                    <select style='<?php echo $style_color; ?>' name='ddlist_y_e_h<?php echo $n; ?>' id='id_y_e_h<?php echo $n; ?>' <?php echo $yo_disa; ?>>
                        <?php $model->setSelectOptionTime(0, $hor_max, $def_e_h); ?>
                    </select>
                    :
                    <select style='<?php echo $style_color; ?>' name='ddlist_y_e_m<?php echo $n; ?>' id='id_y_e_m<?php echo $n; ?>' <?php echo $yo_disa; ?>>
                        <?php $model->setSelectOptionTime(0, 59, $def_e_m); ?>
                    </select>
                </td>

                <!-- 残業実施理由 -->
                <td nowrap><input type='text' style='<?php echo $style_color; ?>' size='30' maxlength='64' name='z_j_r<?php echo $n; ?>' id='id_z_j_r<?php echo $n; ?>' value='<?php echo $content; ?>' <?php echo $yo_disa; ?>></td>

                <!-- [延長][残業]ボタン -->
                <?php
                if( !$limit_over ) {// 申請可能時間
                    if( $v_early ) {
                        ; // 特になし
                    } else {
                        if( $model->IsHoliday($date) ) {// 休日出勤
                            echo "<td><input type='button' id='20' $yo_disa value='Ａ　Ｍ' onClick='setFixedTime(this, $n);'></td>";
                            echo "<td><input type='button' id='21' $yo_disa value='終日１' onClick='setFixedTime(this, $n);'></td>";
                            echo "<td><input type='button' id='22' $yo_disa value='終日２' onClick='setFixedTime(this, $n);'></td>";
                            echo "<td><input type='button' id='23' $yo_disa value='ＰＭ１' onClick='setFixedTime(this, $n);'></td>";
                            echo "<td><input type='button' id='24' $yo_disa value='ＰＭ２' onClick='setFixedTime(this, $n);'></td>";
                        } else {// 平日
                            echo "<td><input type='button' id='10' $yo_disa value='延　長' onClick='setFixedTime(this, $n);'></td>";
                            echo "<td><input type='button' id='11' $yo_disa value='延残１' onClick='setFixedTime(this, $n);'></td>";
                            echo "<td><input type='button' id='12' $yo_disa value='延残２' onClick='setFixedTime(this, $n);'></td>";
                            echo "<td><input type='button' id='13' $yo_disa value='残１ｈ' onClick='setFixedTime(this, $n);'></td>";
                            echo "<td><input type='button' id='14' $yo_disa value='残２ｈ' onClick='setFixedTime(this, $n);'></td>";
                        }
                    }
                }
                ?>

            <!-- 残業結果報告 -->
                <?php if( $limit_over ) {   // 申請可能時間を過ぎている ?>
                <?php
                if( $model->IsNoAdmit('yo', $date, $uid) ) {    // 事前申請がまだ未承認の場合、結果を入力させない。
                    $ji_disa = ' disabled'; // 無効にする
                }
                $status = $model->getApplStatus('ji', $view_data, $res, $n);    // 実績の状態取得
                ?>
                <!-- コピー -->
                <td nowrap align='center'><input type='button' name='copy<?php echo $n; ?>' id='id_copy<?php echo $n; ?>' value='->' onClick='YoteiToJisseki(this.id,<?php echo $n; ?>)' <?php if($status!='－－' || $ji_disa) echo ' disabled';?>></td>

                <!-- 結果報告状態 -->
                <td nowrap align='center'>
                    <?php
                    echo "<input type='button' id='2_$n' $ji_disa value='$status' onClick='return ReportEdit(this, $n, $uid, $uno);'>";
/**/
                    if( $status != '－－' && $status != '否認' ) {   // 初期状態以外
                        $ji_disa = ' disabled'; // 無効にする
                    }
/**/
                    ?>
                </td>

                <!-- 残業結果 -->
                <?php
                if($view_data && $res[$n][16]) {
                    $def_s_h = $res[$n][16]; $def_s_m = $res[$n][17]; // 開始 時 分 セット
                    $def_e_h = $res[$n][18]; $def_e_m = $res[$n][19]; // 終了 時 分 セット
                    $content = $res[$n][20]; $bikou = $res[$n][21]; // 内容 備考 セット
                } else {
                    $def_s_h = -1; $def_s_m = -1;
                    $def_e_h = -1; $def_e_m = -1;
                    $content = ''; $bikou = '';
                }
                ?>

                <!-- 退勤時間 -->
                <?php
                if( $v_early ) {
                    $hit_time = $model->getWorkingStrTime($uid, $date); // 出勤打刻時間取得
                    $limit_time = $model->getWorkTime($uid, "s"); // 定時出社時刻取得
                    if( $uid == '300349' && $model->getWorkClass($date, $uid) == '01' ) {
                        $limit_time = "08:30";
                    }
                } else {
                    $hit_time = $model->getWorkingEndTime($uid, $date); // 退勤打刻時間取得
                    $limit_time = $model->getWorkTime($uid, "e"); // 定時退社時刻取得
                    if( $uid == '300349' && $model->getWorkClass($date, $uid) == '01' ) {
                        $limit_time = "17:15";
                    }
                }
                $hh = substr($limit_time,0,2);
                $mm = substr($limit_time,3,2);
                $b_style = "";
                if( $v_early ) {
                    $min = $model->StrTimeCheck($uid, $limit_time, $hit_time);
                    $time_title = "";
                    if( $min > 0 ) {
                        $time_title = "title='始業開始 {$min} 分前に出勤打刻しています。'";
                        if( $ji_disa != ' disabled' ) {
                            if( $def_s_h == -1 ) $b_style = "style='background-color:yellow'";
                        }
                    }
                    $hit_time++; $hit_time--;
                    $time_click = "setStrTime({$n}, {$hh}, {$mm}, {$hit_time})";
                    $hit_time = sprintf("%04s", $hit_time);
                } else {
                    $min = $model->EndTimeCheck($uid, $limit_time, $hit_time);
                    $time_title = "";
                    if( $min > 0 ) {
                        $time_title = "title='定時退社時間より {$min} 分超過しています。'";
                        if( $ji_disa != ' disabled' ) {
                            if( $def_s_h == -1 ) $b_style = "style='background-color:yellow'";
                        }
                    }
                    $time_click = "setEndTime({$n}, {$hh}, {$mm}, {$hit_time})";
                }
                ?>
                <input type='hidden' id='id_limit_hh<?php echo $n;?>' value='<?php echo $hh;?>'>
                <input type='hidden' id='id_limit_mm<?php echo $n;?>' value='<?php echo $mm;?>'>

                <td nowrap align='center' <?php echo $time_title; ?>>
                    <button type='button' onClick='<?php echo $time_click; ?>' <?php echo $ji_disa . $b_style; ?>>
                        <?php echo substr_replace($hit_time, ":", 2, 0); ?>
                    </button>
                </td>

                <!-- 残業無し -->
                <td nowrap align='center'>
                <?php if($def_s_h!=-1&&($def_s_h==$def_e_h && $def_s_m==$def_e_m)) $zan_check = " checked"; else $zan_check=""; ?>
                <?php echo "<input type='checkbox' name='zan_$n' id='id_zan_$n' onclick='return ZanCheck($n, $uid, $hh, $mm);' $ji_disa $zan_check>"; ?>
                </td>

                <!-- 実際作業時間 -->
                <?php
                if( $status_yo == "否認" || $status_yo == "途中" || ($status_yo == "－－" && $view_data && $res[$n][9] ) ) {
                    $status_yo = $model->getAdmitStatus($res[$n][9], $res[$n][10]);
                ?>
                <td nowrap align='center'>事前申請 <?php echo $status_yo; ?></td>
                <?php
                } else {
                ?>
                <td nowrap align='center'>
                    <select style='<?php echo $style_color; ?>' name='ddlist_j_s_h<?php echo $n; ?>' id='id_j_s_h<?php echo $n; ?>' <?php echo $ji_disa; ?>>
                        <?php $model->setSelectOptionTime(0, $hor_max, $def_s_h); ?>
                    </select>
                    :
                    <select style='<?php echo $style_color; ?>' name='ddlist_j_s_m<?php echo $n; ?>' id='id_j_s_m<?php echo $n; ?>' <?php echo $ji_disa; ?>>
                        <?php $model->setSelectOptionTime(0, 59, $def_s_m); ?>
                    </select>
                    ～
                    <select style='<?php echo $style_color; ?>' name='ddlist_j_e_h<?php echo $n; ?>' id='id_j_e_h<?php echo $n; ?>' <?php echo $ji_disa; ?>>
                        <?php $model->setSelectOptionTime(0, $hor_max, $def_e_h); ?>
                    </select>
                    :
                    <select style='<?php echo $style_color; ?>' name='ddlist_j_e_m<?php echo $n; ?>' id='id_j_e_m<?php echo $n; ?>' <?php echo $ji_disa; ?>>
                        <?php $model->setSelectOptionTime(0, 59, $def_e_m); ?>
                    </select>
                </td>
                <?php
                }
                ?>
                <!-- 実施業務内容 -->
                <td nowrap><input style='<?php echo $style_color; ?>' type='text' size='30' maxlength='64' name='j_g_n<?php echo $n; ?>' id='id_j_g_n<?php echo $n; ?>' value='<?php echo $content; ?>'  <?php echo $ji_disa; ?>></td>

                <?php } // if($limit_over) End. ?>
            </tr>
            <?php
            } // for( $n=0; $n<$rows; $n++ ) End.
            ?>
            <tr><!-- 追加行 -->
                <td nowrap align='center' colspan='3'><!-- コピー元 --><!-- 氏名 --><!-- コピー先 -->
                    社員番号：<input type='text' size='8' maxlength='6' name='add_uid' id='id_add_uid'>
                </td>
                <td class='pt10' colspan='3'><!-- 状態 --><!-- 予定時間 --><!-- 残業実施理由 -->
                    <input type='submit' name='add_row' value='追加' onClick='return AppliAdd();'>
                    ※名前がない人、社員番号を入力し[追加]をクリック。
                </td>
                <?php if( $limit_over ) {  // 申請可能時間を過ぎている ?>
                <td>　</td><!-- コピー -->
                <td>　</td><!-- 状態 -->
                <td>　</td><!-- 退勤時間 -->
                <td>　</td><!-- 残業無し -->
                <td>　</td><!-- 実際作業時間 -->
                <td>　</td><!-- 実施業務内容 -->
                <?php } ?>
            </tr>
            <tr><!-- 課長 コメント -->
                <td nowrap align='center' colspan='3'><!-- コピー元 --><!-- 氏名 --><!-- コピー先 -->
                    課長 コメント
                </td>
                <td nowrap align='center' colspan='3'><!-- 状態 --><!-- 予定時間 --><!-- 残業実施理由 -->
                    <textarea name='comment_ka' id='id_comment_ka' rows='2' cols='50' style='<?php echo $style_color; ?>' value='<?php echo $comment[0]; ?>'><?php echo $comment[0]; ?></textarea>
                    <input type='submit' name='comme_ka' value='更新' onClick='return UpComment();'>
                </td>
                <?php if( $limit_over ) {  // 申請可能時間を過ぎている ?>
                <td>　</td><!-- コピー -->
                <td>　</td><!-- 状態 -->
                <td>　</td><!-- 退勤時間 -->
                <td>　</td><!-- 残業無し -->
                <td>　</td><!-- 実際作業時間 -->
                <td>　</td><!-- 実施業務内容 -->
                <?php } ?>
            </tr>
            <tr><!-- 部長 コメント -->
                <td nowrap align='center' colspan='3'><!-- コピー元 --><!-- 氏名 --><!-- コピー先 -->
                    部長 コメント
                </td>
                <td nowrap align='center' colspan='3'><!-- 状態 --><!-- 予定時間 --><!-- 残業実施理由 -->
                    <textarea name='comment_bu' id='id_comment_bu' rows='2' cols='50' style='<?php echo $style_color; ?>' value='<?php echo $comment[1]; ?>'><?php echo $comment[1]; ?></textarea>
                    <input type='submit' name='comme_bu' value='更新' onClick='return UpComment();'>
                </td>
                <?php if( $limit_over ) {  // 申請可能時間を過ぎている ?>
                <td>　</td><!-- コピー -->
                <td>　</td><!-- 状態 -->
                <td>　</td><!-- 退勤時間 -->
                <td>　</td><!-- 残業無し -->
                <td>　</td><!-- 実際作業時間 -->
                <td>　</td><!-- 実施業務内容 -->
                <?php } ?>
            </tr>
            <tr><!-- 下段、[登録]ボタン用領域 -->
                <td nowrap align='center' colspan='12'>
    <?php if( $limit_over ) {  // 残業結果報告表示時 ?>
    <div>【事前申請したが延長・残業しなかった場合は、<font style='color:red;'><b>延長及び残業なし</b></font>に、チェックを入れて登録すること。】</div><BR>
    <?php } ?>
                    <?php echo "<b>※こちらの <input type='submit' align='center' name='commit' id='id_commit' value='登　録' onClick='return IsUpDate();'> でも登録可能。</b>"; ?>
                </td>
            </tr>
        </table>
    </tr></td> <!----------- ダミー(デザイン用) ------------>
    </table>
    <BR>

    <?php
    // 以下は、カプラ・リニア組立課のみ表示されます。(2021.12.16 現在)
    $max = $model->getKumiDayPlan($bumon, $date, $plan_res);
    if( $max > 0 ) {
        $field = 5; // 表示列数
        if( $bumon == "リニア組立課" ) $field = 4; // 表示列数
    ?>
        ※以下の製品名をクリックすると、コピーされるます。入力したい所にカーソルを移動し「貼り付け」して下さい。
    <table class='pt10' border="1" cellspacing="0">
    <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table width='100%' class='winbox_field' bgcolor='#D8D8D8' align='center' border='1' cellspacing='0' cellpadding='3'>
        <!-- キャプション -->
            <tr>
                <td class='winbox' style='background-color:red; color:white;' colspan='12' align='center'>
                    <div class='caption_font'>組立計画一覧（<?php echo date('m', strtotime($date)) ?>月）</div>
                </td>
            </tr>
        <!-- 一覧データ（組立日程計画からデータを取得し、製品名を表示） -->
            <?php for($i=0; $i<$max; $i++) { ?>
                <?php if( $i % $field == 0 ) { ?>
            <tr nowrap>
                <?php } ?>
                <td nowrap>
                <input type='button' id='<?php echo "pl_$i"; ?>' value='　' onClick='PlanCopy(this, "<?php echo $plan_res[$i][0]; ?>");'>
                <label for='<?php echo "pl_$i"; ?>'> <?php echo $plan_res[$i][0]; ?></label>
                </td>
                <?php if( $i % $field == ($field-1) ) { ?>
            </tr>
                <?php } ?>
            <?php } ?>
            <?php for( ; ($i % $field) != 0; $i++) { ?>
                <td>　</td>
            <?php } ?>
            </tr>
            
        </table>
    </tr></td> <!----------- ダミー(デザイン用) ------------>
    </table>
    <?php } // if( $max > 0 ) End. ?>

    <?php //echo "<input type='submit' align='center' name='commit' id='id_commit' value='登　録' onClick='return IsUpDate();'><b> ※上部の[登録]ボタンと同じ。</b><BR>　"; ?>
<?php 
}   // if( $list_view != 'on' ) End.
?>

</form>


</center>
</body>
<BR><BR><?php echo $menu->out_alert_java(); ?>
</html>
