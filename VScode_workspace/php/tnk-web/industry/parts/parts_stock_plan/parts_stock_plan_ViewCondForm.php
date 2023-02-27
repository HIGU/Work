<?php
//////////////////////////////////////////////////////////////////////////////
// 部品 在庫 予定 照会 (引当･発注状況照会)  条件選択 Form       MVC View 部 //
// Copyright (C) 2006-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/05/25 Created   parts_stock_plan_ViewCondForm.php                   //
// 2006/06/02 大文字変換用のイベントハンドラーonKeyUpを追加                 //
// 2007/02/08 在庫経歴から呼出された時のアクションと在庫経歴呼出しを追加    //
// 2007/02/21 Window表示のnoMenu(経歴と予定の往復に制限無し)対応            //
// 2007/03/13 在庫経歴照会時のパラメーターをリクエストにより条件分け追加    //
// 2007/05/22 最低必要日の照会を追加 requireDateのリクエストダイレクト処理  //
// 2007/06/22 noMenuをAjaxへ渡すためhidden属性でフォーム部品追加            //
// 2007/07/27 部品番号を変えて在庫経歴照会をクリックする場合に変えた部品を  //
//            反映させるため<a href='直接URL' → 'javascript...'で対応      //
// 2007/10/19 noMenu時にkeyInUpper()が使えないため                          //
//            単体版のwindowKeyCheckMethod.jsを作成しkeyInUpper()を切替使用 //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<!-- <meta http-equiv="Refresh" content="15;URL=<?php echo $menu->out_self(), "?showMenu={$request->get('showMenu')}"?>"> -->
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<link rel='stylesheet' href='parts_stock_plan.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='parts_stock_plan.js?<?php echo $uniq ?>'></script>
</head>
<body style='overflow-y:hidden;'
    onLoad='
        PartsStockPlan.set_focus(document.ConditionForm.targetPartsNo, "select");
        // PartsStockPlan.intervalID = setInterval("PartsStockPlan.blink_disp(\"blink_item\")", 1300);
        <?php if ($request->get('targetPartsNo') != '' && $request->get('requireDate') != '') { echo 'PartsStockPlan.checkANDexecute(document.ConditionForm, 3)'; ?>
        <?php } elseif ($request->get('targetPartsNo') != '') { echo 'PartsStockPlan.checkANDexecute(document.ConditionForm, 1)'; }?>
    '
>
<center>
<?php if ($request->get('noMenu')) { ?>
<script type='text/javascript' src='/windowKeyCheckMethod.js?<?php echo $uniq ?>'></script>
<?php } else { ?>
<?php echo $menu->out_title_border() ?>
<?php } ?>
    
    <form name='ConditionForm' action='<?php echo $menu->out_self() ?>' method='post'
        onSubmit='return PartsStockPlan.checkANDexecute(this, 1)'
    >
        <!----------------- ここは 本文を表示する ------------------->
        <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='1'>
            <tr>
                    <!--  bgcolor='#ffffc6' 薄い黄色 --> 
                <td colspan='7' align='center' class='winbox caption_color'>
                    <span id='blink_item'>部品番号</span>
                </td>
                <td class='winbox' align='center'>
                    <input type='text' name='targetPartsNo' size='9' class='pt12b' value='<?php echo $request->get('targetPartsNo'); ?>' maxlength='9'
                        <?php if ($request->get('noMenu')) { ?>
                        onKeyUp='keyInUpper(this);'
                        <?php } else { ?>
                        onKeyUp='PartsStockPlan.keyInUpper(this);'
                        <?php } ?>
                    >
                </td>
                <td class='winbox' align='center'>
                    <input type='button' name='exec1' value='実行' onClick='PartsStockPlan.checkANDexecute(document.ConditionForm, 1);' title='クリックすれば、この下に表示します。'>
                    &nbsp;
                    <input type='button' name='exec2' value='開く' style='width:54px;' onClick='PartsStockPlan.checkANDexecute(document.ConditionForm, 2);' title='別ウィンドウで表示します。'>
                    &nbsp;
                    <input type='button' name='clear' value='クリア' style='width:54px;' onClick='PartsStockPlan.viewClear();'>
                    &nbsp;
                    <input type='button' name='exec3' value='必要日' style='width:54px;' onClick='PartsStockPlan.checkANDexecute(document.ConditionForm, 3);' title='この下に発注を除いた引当のみにし必要日を表示します。'>
                    &nbsp;
                    <input type='button' name='exec3' value='必開く' style='width:54px;' onClick='PartsStockPlan.checkANDexecute(document.ConditionForm, 4);' title='別ウィンドウに発注を除いた引当のみにし必要日を表示します。'>
                </td>
                <?php
                if ($stockViewFlg && $request->get('targetPartsNo')) {
                    echo "<td class='winbox' align='center'>\n";
                    if ($request->get('noMenu') && $request->get('material')) {
                        echo "&nbsp&nbsp<a href='javascript:location.replace(\"", $menu->out_action('在庫経歴照会'), "?parts_no=\" + escape(document.ConditionForm.targetPartsNo.value) + \"&material=1&noMenu=yes\")' style='text-decoration:none;'>在庫経歴照会</a>\n";
                    } elseif ($request->get('noMenu')) {
                        echo "&nbsp&nbsp<a href='javascript:location.replace(\"", $menu->out_action('在庫経歴照会'), "?parts_no=\" + escape(document.ConditionForm.targetPartsNo.value) + \"&noMenu=yes\")' style='text-decoration:none;'>在庫経歴照会</a>\n";
                    } elseif ($request->get('material')) {
                        echo "&nbsp&nbsp<a href='javascript:location.replace(\"", $menu->out_action('在庫経歴照会'), "?parts_no=\" + escape(document.ConditionForm.targetPartsNo.value) + \"&material=1\")' style='text-decoration:none;'>在庫経歴照会</a>\n";
                    } elseif ($request->get('aden_flg')) {
                        //echo "&nbsp&nbsp<a href='javascript:location.replace(\"", $menu->out_action('在庫経歴照会'), "?parts_no=\" + escape(document.ConditionForm.targetPartsNo.value) + \"&sc_no=", $request->get('sc_no'), "&aden_flg=1\")' style='text-decoration:none;'>在庫経歴照会</a>\n";
                    } else {
                        echo "&nbsp&nbsp<a href='javascript:location.replace(\"", $menu->out_action('在庫経歴照会'), "?parts_no=\" + escape(document.ConditionForm.targetPartsNo.value) )' style='text-decoration:none;'>在庫経歴照会</a>\n";
                    }
                    echo "</td>\n";
                }
                ?>
            </tr>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        <input type='hidden' name='noMenu' value='<?php echo $request->get('noMenu')?>'>
    </form>
    <div id='showAjax'>
    </div>
</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
