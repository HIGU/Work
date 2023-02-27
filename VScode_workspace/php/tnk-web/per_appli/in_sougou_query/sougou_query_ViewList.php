<?php
////////////////////////////////////////////////////////////////////////////////
// 総合届（照会）                                                             //
//                                                    MVC View 部 リスト表示  //
// Copyright (C) 2020-2020 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2020/11/18 Created sougou_query_ViewList.php                               //
// 2021/02/12 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////

// 選択可能な部門をセット
function SelectOptionBumon($model, $request)
{
    $bumonname = array("指定なし （すべて）", "栃木日東工器", "ＩＳＯ事務局", "管理部", "管理部 (管理部)", "管理部 総務課", "管理部 商品管理課", "技術部", "技術部 (技術部)", "技術部 品質保証課", "技術部 技術課", "製造部", "製造部 (製造部)", "製造部 製造１課", "製造部 製造２課", "生産部", "生産部 (生産部)", "生産部 生産管理課", "生産部 カプラ組立課", "生産部 リニア組立課");

    $max = count($bumonname);
    for( $i = 0; $i < $max ; $i++ ) {
        if( $model->IsDisp($i) ) {
            if( $request->get('ddlist_bumon') == $bumonname[$i] ) {
                echo "<option value='{$bumonname[$i]}' selected>{$bumonname[$i]}</option>";
            } else {
                echo "<option value='{$bumonname[$i]}'>{$bumonname[$i]}</option>";
            }
        }
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
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<?php echo $menu->out_jsBaseClass() ?>

<link rel='stylesheet' href='../per_appli.css' type='text/css' media='screen'>
<script type='text/javascript' language='JavaScript' src='sougou_query.js'></script>

</head>

<?php if($request->get('rep')!='rep' ) { ?>
<body onLoad='Init()'>
<?php } else { ?>
<body onLoad='Rep()'>
<?php } ?>

<center>
<?= $menu->out_title_border() ?>

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

<form name='form_query' method='post' action='<?php echo $menu->out_self(),"?showMenu=Results" ?>' onSubmit='return InputAllCheck()'>
    <tr>
        <td align='center'>総合届 申請日の指定（YYYYMMDD）</td>
        <td align='center'>
            <input type="checkbox" name="c0" id="001" value="１日" <?php if($request->get('c0')=='１日') echo ' checked'; ?> onClick='OneDay(this)'><label for="001">１日</label>
            <input type="text" size="10" maxlength="8" id="001-1" name="si_s_date" value='<?php echo $request->get('si_s_date') ?>' onkeyup="value=InputCheck(this);" onblur='checkDate(this)'>
            <font id='001-0'>～<font>
            <input type="text" size="10" maxlength="8" id="001-2" name="si_e_date" value='<?php echo $request->get('si_e_date') ?>' onkeyup="value=InputCheck(this);" onblur='checkDate(this)'>
        </td>
    </tr>

<!-- 部門指定予定 -->
    <?php if($model->IsMaster() || $model->IsBukatyou()) { ?>
    <tr>
        <td align='center'>部門の選択</td>
        <td style='border:groove' align='center'>
            <select name="ddlist_bumon">
                <?php SelectOptionBumon($model, $request); ?>
            </select>
        </td>
    </tr>
    <?php } ?>
<!-- -->

    <?php if(getCheckAuthority(63)) { ?> <!-- 63:社員番号入力可能（工場長、管理部、総務課）-->
    <tr>
        <td align='center'>パート以外 又は、パートのみ</td>
        <td align='center'>
            <input type="radio" name="r5" id="501" value="指定なし" <?php if($request->get('r5')=='指定なし') echo ' checked'; ?> ><label for="501">指定なし</label>
            <input type="radio" name="r5" id="502" value="パート以外" <?php if($request->get('r5')=='パート以外') echo ' checked'; ?> ><label for="502">パート以外</label>
            <input type="radio" name="r5" id="503" value="パートのみ" <?php if($request->get('r5')=='パートのみ') echo ' checked'; ?> ><label for="503">パートのみ</label>
        </td>
    </tr>
    <?php } ?>

    <tr>
        <?php if(getCheckAuthority(63) || $model->IsBukatyou()) { ?> <!-- 63:社員番号入力可能（工場長、管理部、総務課）-->
            <td align='center'>申請者（社員No.）の指定</td>
            <td align='center'>
                社員番号：<input type="text" size="8" maxlength="6" name="syainbangou" value='<?php echo $request->get('syainbangou') ?>' onkeyup="value=InputCheck(this);">
            </td>
        <?php } else { ?>
            <td align='center'>申請者（社員No.）</td>
            <td align='center'>
                <input type='hidden' name='syainbangou' value='<?php echo $model->getUid(); ?>'>
                <p class='pt10'>※権限がない為、ログイン時の社員番号固定。</p>
                <?php echo '社員番号：' . $model->getUid(); ?>
            </td>
        <?php } ?>
    </tr>

    <tr>
        <td align='center'>対象日の指定（YYYYMMDD）</td>
        <td align='center'>
            <input type="checkbox" name="c1" id="101" value="１日" <?php if($request->get('c1')=='１日') echo ' checked'; ?> onClick='OneDay(this)'><label for="101">１日</label>

            <input type="text" size="10" maxlength="8" id="101-1" name="str_date" value='<?php echo $request->get('str_date') ?>' onkeyup="value=InputCheck(this);" onblur='checkDate(this)'>
            <font id='101-0'>～<font>
            <input type="text" size="10" maxlength="8" id="101-2" name="end_date" value='<?php echo $request->get('end_date') ?>' onkeyup="value=InputCheck(this);" onblur='checkDate(this)'>
        </td>
    </tr>

    <?php if(getCheckAuthority(63)) { ?> <!-- 63:社員番号入力可能（総務課）-->
    <tr>
        <td align='center'><font style='color:red;'>※チェック時、以下の条件は無視されます。</font></td>
        <td align='center'>
            <input type="checkbox" name="c2" id="201" value="不在者リスト" <?php if($request->get('c2')=='不在者リスト') echo ' checked'; ?> onClick='huzaisya(this.checked)'><label for="201">不在者リスト</label>
        </td>
    </tr>
    <?php } ?>

    <tr>
        <td align='center'>内容の選択</td>
        <td style='border:groove' align='center'>
            <select name="ddlist">
                <option value="指定なし" <?php if($request->get('ddlist')=='指定なし') echo ' selected'; ?> >指定なし</option>
                <option value="有給休暇" <?php if($request->get('ddlist')=='有給休暇') echo ' selected'; ?> >有給休暇</option>
                <option value="AM半日有給休暇" <?php if($request->get('ddlist')=='AM半日有給休暇') echo ' selected'; ?> >AM半日有給休暇</option>
                <option value="PM半日有給休暇" <?php if($request->get('ddlist')=='PM半日有給休暇') echo ' selected'; ?> >PM半日有給休暇</option>
                <option value="時間単位有給休暇" <?php if($request->get('ddlist')=='時間単位有給休暇') echo ' selected'; ?> >時間単位有給休暇</option>
                <option value="欠勤" <?php if($request->get('ddlist')=='欠勤') echo ' selected'; ?> >欠勤</option>
                <option value="遅刻早退" <?php if($request->get('ddlist')=='遅刻早退') echo ' selected'; ?> >遅刻早退</option>
                <option value="出張（日帰り）" <?php if($request->get('ddlist')=='出張（日帰り）') echo ' selected'; ?> >出張（日帰り）</option>
                <option value="出張（宿泊）" <?php if($request->get('ddlist')=='出張（宿泊）') echo ' selected'; ?> >出張（宿泊）</option>
                <option value="直行" <?php if($request->get('ddlist')=='直行') echo ' selected'; ?> >直行</option>
                <option value="直帰" <?php if($request->get('ddlist')=='直帰') echo ' selected'; ?> >直帰</option>
                <option value="直行/直帰" <?php if($request->get('ddlist')=='直行/直帰') echo ' selected'; ?> >直行/直帰</option>
                <option value="特別休暇" <?php if($request->get('ddlist')=='特別休暇') echo ' selected'; ?> >特別休暇</option>
                <option value="振替休日" <?php if($request->get('ddlist')=='振替休日') echo ' selected'; ?> >振替休日</option>
                <option value="生理休暇" <?php if($request->get('ddlist')=='生理休暇') echo ' selected'; ?> >生理休暇</option>
                <option value="IDカード通し忘れ（出勤）" <?php if($request->get('ddlist')=='IDカード通し忘れ（出勤）') echo ' selected'; ?> >IDカード通し忘れ（出勤）</option>
                <option value="IDカード通し忘れ（退勤）" <?php if($request->get('ddlist')=='IDカード通し忘れ（退勤）') echo ' selected'; ?> >IDカード通し忘れ（退勤）</option>
                <option value="時限承認忘れ（残業申告漏れ）" <?php if($request->get('ddlist')=='時限承認忘れ（残業申告漏れ）') echo ' selected'; ?> >時限承認忘れ（残業申告漏れ）</option>
                <option value="IDカード通し忘れ（退勤）＋ 時限承認忘れ（残業申告漏れ）" <?php if($request->get('ddlist')=='IDカード通し忘れ（退勤）＋ 時限承認忘れ（残業申告漏れ）') echo ' selected'; ?> >IDカード通し忘れ（退勤）＋ 時限承認忘れ（残業申告漏れ）</option>
                <option value="その他" <?php if($request->get('ddlist')=='その他') echo ' selected'; ?> >その他</option>
            </select>
        </td>
    </tr>

    <tr>
<?php
if( date('Ymd') > '20210630' ) {    // 回数券使用不可の対応
        echo "<td align='center'>Suica の有無</td>";
} else {
        echo "<td align='center'>回数券の有無</td>";
}
?>
        
        <td align='center' id='6000'>
            <input type="radio" name="r6" id="601" value="指定なし" <?php if($request->get('r6')=='指定なし') echo ' checked'; ?> ><label for="601">指定なし</label>
            <input type="radio" name="r6" id="602" value="t" <?php if($request->get('r6')=='t') echo ' checked'; ?> ><label for="602">あり</label>
            <input type="radio" name="r6" id="603" value="f" <?php if($request->get('r6')=='f') echo ' checked'; ?> ><label for="603">なし</label>
        </td>
    </tr>

    <tr>
        <td align='center'>受電者の有無</td>
        <td align='center' id='7000'>
            <input type="radio" name="r7" id="701" value="指定なし" <?php if($request->get('r7')=='指定なし') echo ' checked'; ?> ><label for="701">指定なし</label>
            <input type="radio" name="r7" id="702" value="受電者" <?php if($request->get('r7')=='受電者') echo ' checked'; ?> ><label for="702">あり</label>
            <input type="radio" name="r7" id="703" value="なし" <?php if($request->get('r7')=='なし') echo ' checked'; ?> ><label for="703">なし</label>
        </td>
    </tr>

    <tr>
        <td align='center'>至急の有無</td>
        <td align='center' id='8000'>
            <input type="radio" name="r8" id="801" value="指定なし" <?php if($request->get('r8')=='指定なし') echo ' checked'; ?> ><label for="801">指定なし</label>
            <input type="radio" name="r8" id="802" value="至急" <?php if($request->get('r8')=='至急') echo ' checked'; ?> ><label for="802">至急</label>
            <input type="radio" name="r8" id="803" value="通常" <?php if($request->get('r8')=='通常') echo ' checked'; ?> ><label for="803">通常</label>
        </td>
    </tr>

    <tr>
        <td align='center'>承認状況</td>
        <td align='center' id='9000'>
            <input type="radio" name="r9" id="901" value="指定なし" <?php if($request->get('r9')=='指定なし') echo ' checked'; ?> ><label for="901">指定なし</label>
            <input type="radio" name="r9" id="902" value="END" <?php if($request->get('r9')=='END') echo ' checked'; ?> ><label for="902">完了</label>
            <input type="radio" name="r9" id="903" value="途中" <?php if($request->get('r9')=='途中') echo ' checked'; ?> ><label for="903">途中</label>
            <input type="radio" name="r9" id="904" value="DENY" <?php if($request->get('r9')=='DENY') echo ' checked'; ?> ><label for="904">否認</label>
            <input type="radio" name="r9" id="905" value="CANCEL" <?php if($request->get('r9')=='CANCEL') echo ' checked'; ?> ><label for="905">取消</label>
        </td>
    </tr>

        </table>
    </td></tr>
    </table> <!----------------- ダミーEnd --------------------->

    <p align='center'>
        　　　　　　　　　　　　　　　　　　
        <input type="submit" value="実行" name="submit">&emsp;
        <input type="button" value="リセット" name="reset" onClick='location.replace("<?php echo $menu->out_self(), '?', $model->get_htmlGETparm() ?>");'>&emsp;
<!-- ＰＤＦファイルを開く-->
        <font class='pt10' align='center'>
        ※<a href="download_file.php/総合届（照会）.pdf">総合届（照会）</a>の画面説明。
        </font>
<!-- -->
    </p>
</form>

<?php
if( $model->getUid() == '300667' ) {
echo "<div class='pt9'>※ 部門内の課が移動した場合、<font style='color:red;'>部門別のactID取得</font>関数を修正する必要あり。</div>";
}
?>
</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
