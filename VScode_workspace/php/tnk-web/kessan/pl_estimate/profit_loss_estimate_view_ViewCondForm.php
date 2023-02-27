<?php
//////////////////////////////////////////////////////////////////////////////
// 損益予測の集計・分析 結果 照会(照会のみ)  条件選択 Form      MVC View 部 //
// Copyright (C) 2011-     Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2011/07/19 Created   profit_loss_estimate_view_ViewCondForm.php          //
// 2011/08/02 印刷ボタンのテスト                                            //
// 2011/08/04 単位と桁数を追加                                              //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
<meta http-equiv='Content-Style-Type' content='text/css'>
<meta http-equiv='Content-Script-Type' content='text/javascript'>
<!-- <meta http-equiv='Refresh' content='15;URL=<?php echo $menu->out_self(), "?showMenu={$request->get('showMenu')}"?>'> -->
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<link rel='stylesheet' href='profit_loss_estimate.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='profit_loss_estimate_view.js?<?php echo $uniq ?>'>
</script>
<script type='text/javascript' language='JavaScript'>
<!--
function YMDCreate(val) {
    
}
function framePrint() {
    //list.focus();
    print();
}
function PrintPreview()
{
    list.focus();
    if(window.ActiveXObject == null || document.body.insertAdjacentHTML == null) return;
    var sWebBrowserCode = '<object width="0" height="0" classid="CLSID:8856F961-340A-11D0-A96B-00C04FD705A2"></object>'; 
    document.body.insertAdjacentHTML('beforeEnd', sWebBrowserCode);
    var objWebBrowser = document.body.lastChild;
    if(objWebBrowser == null) return;
    objWebBrowser.ExecWB(7, 1);
    document.body.removeChild(objWebBrowser);
}
//-->

</script>
<style media=print>
<!--
/*ブラウザのみ表示*/
.dspOnly {
    display:none;
}
.footer {
    display:none;
}
// -->
</style>
</head>
<body style='overflow-y:hidden;'
    onLoad='
        ProfitLossEstimateView.set_focus(document.ConditionForm.targetDateStr, "noSelect");
        // ProfitLossEstimateView.intervalID = setInterval("ProfitLossEstimateView.blink_disp(\"blink_item\")", 1300);
        <?php if ($request->get('AutoStart') != '') echo 'ProfitLossEstimateView.checkANDexecute(document.ConditionForm, 1)'; ?>
    '
>
<center>
<?php echo $menu->out_title_border() ?>
    
    <form name='ConditionForm' action='<?php echo $menu->out_self() ?>' method='post'
        onSubmit='return ProfitLossEstimateView.checkANDexecute(this, 1)'
    >
        <!----------------- ここは 本文を表示する ------------------->
        <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='1'>
            <tr>
                    <!--  bgcolor='#ffffc6' 薄い黄色 --> 
                <td align='center' class='winbox caption_color'>
                    <span id='blink_item'>対象年月</span>
                </td>
                <td class='winbox' align='center'>
                    <select name='targetDateYM' class='pt14b' onChange='ProfitLossEstimateView.checkANDReload(document.ConditionForm, 1);'>
                        <!-- <option value='200605' selected>2005年05月</option> -->
                        <option value='<?php echo date('Ym') ?>' selected>年月選択</option>
                        <?php echo $model->getTargetDateYMvalues($request) ?>
                    </select>
                </td>
                <td align='center' class='winbox caption_color'>
                    <span id='blink_item'>作成日付</span>
                </td>
                <td class='winbox' align='center'>
                    <select name='targetDateYMD' class='pt14b'>
                        <!-- <option value='200605' selected>2005年05月</option> -->
                        <option value='<?php echo date('Ymd') ?>' selected>日付選択</option>
                        <?php echo $model->getTargetDateYMDvalues($request) ?>
                    </select>
                </td>
                <td class='winbox' align='center'>
                    単位
                    <select name='keihi_tani' class='pt12b'>
                        <?php
                            if (!$request->get('keihi_tani')) {
                                $tani = $request->get('keihi_tani');
                            } else {
                                $tani = 1000;           // 初期値 表示単位 千円
                            }
                            if ($tani == 1000)
                                echo "<option value='1000' selected>　千円</option>\n";
                            else
                                echo "<option value='1000'>　千円</option>\n";
                            if ($tani == 1)
                                echo "<option value='1' selected>　　円</option>\n";
                            else
                                echo "<option value='1'>　　円</option>\n";
                            if ($tani == 1000000)
                                echo "<option value='1000000' selected>百万円</option>\n";
                            else
                                echo "<option value='1000000'>百万円</option>\n";
                            if ($tani == 10000)
                                echo "<option value='10000' selected>　万円</option>\n";
                            else
                                echo "<option value='10000'>　万円</option>\n";
                            if($tani == 100000)
                                echo "<option value='100000' selected>十万円</option>\n";
                            else
                                echo "<option value='100000'>十万円</option>\n";
                        ?>
                        </select>
                        少数桁
                        <select name='keihi_keta' class='pt12b'>
                        <?php
                            if (!$request->get('keihi_keta')) {
                                $keta = $request->get('keihi_keta');
                            } else {
                                $keta = 0;              // 初期値 小数点以下桁数
                            }
                            if ($keta == 0)
                                echo "<option value='0' selected>０桁</option>\n";
                            else
                                echo "<option value='0'>０桁</option>\n";
                            if ($keta == 3)
                                echo "<option value='3' selected>３桁</option>\n";
                            else
                                echo "<option value='3'>３桁</option>\n";
                            if ($keta == 6)
                                echo "<option value='6' selected>６桁</option>\n";
                            else
                                echo "<option value='6'>６桁</option>\n";
                            if ($keta == 1)
                                echo "<option value='1' selected>１桁</option>\n";
                            else
                                echo "<option value='1'>１桁</option>\n";
                            if ($keta == 2)
                                echo "<option value='2' selected>２桁</option>\n";
                            else
                                echo "<option value='2'>２桁</option>\n";
                            if ($keta == 4)
                                echo "<option value='4' selected>４桁</option>\n";
                            else
                                echo "<option value='4'>４桁</option>\n";
                            if ($keta == 5)
                                echo "<option value='5' selected>５桁</option>\n";
                            else
                                echo "<option value='5'>５桁</option>\n";
                        ?>
                        </select>
                </td>
                <td class='winbox' align='center'>
                    <input type='button' name='exec1' value='実行' onClick='ProfitLossEstimateView.checkANDexecute(document.ConditionForm, 1);' title='クリックすれば、この下に表示します。'>
                    &nbsp;
                    <input type='button' name='clear' value='クリア' onClick='ProfitLossEstimateView.viewClear();'>
                </td>
            </tr>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
    </form>
    <br>
    <div id='showAjax'>
    </div>
</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
