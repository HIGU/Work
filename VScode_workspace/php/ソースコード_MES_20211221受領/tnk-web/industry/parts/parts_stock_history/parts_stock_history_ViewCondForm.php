<?php
//////////////////////////////////////////////////////////////////////////////
// 部品 在庫 経歴 照会 (ＭＶＣ版)       部品 選択 Form          MVC View 部 //
// Copyright (C) 2004-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/12/20 Created parts_stock_history_ViewCondForm.php(parts_stock_view)//
// 2007/03/16 オリジナルはparts_stock_view.php でparts_stock_plan_ViewCond  //
//            Form.phpを雛形にして完全なＭＶＣモデルでコーディングした。    //
//            変更経歴は backup/parts_stock_view.php を参照すること。       //
// 2007/03/22 PartsStockHistory.呼出し時に$_SERVER['QUERY_STRING']パラ追加  //
// 2007/06/22 noMenuをAjaxへ渡すためhidden属性でフォーム部品追加            //
// 2007/07/27 部品番号を変えて在庫予定照会をクリックする場合に変えた部品を  //
//            反映させるため<a href='直接URL' → 'javascript...'で対応      //
// 2007/10/19 noMenu時にkeyInUpper()が使えないため                          //
//            単体版のwindowKeyCheckMethod.jsを作成しkeyInUpper()を切替使用 //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<!-- <meta http-equiv="Refresh" content="15;URL=<?php echo $menu->out_self(), "?showMenu={$request->get('showMenu')}"?>"> -->
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<link rel='stylesheet' href='parts_stock_history.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='parts_stock_history.js?<?php echo $uniq ?>'></script>
</head>
<body style='overflow-y:hidden;'
    onLoad='
        PartsStockHistory.set_focus(document.ConditionForm.targetPartsNo, "select");
        // PartsStockHistory.intervalID = setInterval("PartsStockHistory.blink_disp(\"blink_item\")", 1300);
        <?php if ($request->get('targetPartsNo') != '') echo "PartsStockHistory.checkANDexecute(document.ConditionForm, 1, \"{$_SERVER['QUERY_STRING']}\")\n" ?>
    '
>
<center>
<?php if (isset($_REQUEST['noMenu'])) { ?>
<script type='text/javascript' src='/windowKeyCheckMethod.js?<?php echo $uniq ?>'></script>
<?php } else { ?>
<?php echo $menu->out_title_border() ?>
<?php } ?>
    
    <form name='ConditionForm' action='<?php echo $menu->out_self() ?>' method='post'
        onSubmit='return PartsStockHistory.checkANDexecute(this, 1, "<?php echo $_SERVER['QUERY_STRING'] ?>")'
    >
        <!----------------- ここは 本文を表示する ------------------->
        <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='1'>
            <tr>
                <?php
                if ($request->get('targetPartsNo') && (!$request->get('noMenu')) && $request->get('material') ) {
                    echo "                <td class='winbox' align='center'>\n";
                    echo "                    <a href='", $menu->out_action('買掛実績照会'), "?parts_no=", urlencode($request->get('targetPartsNo')), "&material=1' style='text-decoration:none;'>買掛実績照会</a>&nbsp&nbsp\n";
                    echo "                </td>\n";
                } elseif ($request->get('targetPartsNo') && (!$request->get('noMenu')) ) { // 現在はあまり意味は無いが？
                    echo "                <td class='winbox' align='center'>\n";
                    echo "                    <a href='", $menu->out_action('買掛実績照会'), "?parts_no=", urlencode($request->get('targetPartsNo')), "' style='text-decoration:none;'>買掛実績照会</a>&nbsp&nbsp\n";
                    echo "                </td>\n";
                }
                ?>
                    <!--  bgcolor='#ffffc6' 薄い黄色 --> 
                <td colspan='7' align='center' class='winbox caption_color'>
                    <span id='blink_item'>部品番号</span>
                </td>
                <td class='winbox' align='center'>
                    <input type='text' name='targetPartsNo' size='9' class='pt12b' value='<?php echo $request->get('targetPartsNo'); ?>' maxlength='9'
                        <?php if (isset($_REQUEST['noMenu'])) { ?>
                        onKeyUp='keyInUpper(this);'
                        <?php } else { ?>
                        onKeyUp='PartsStockHistory.keyInUpper(this);'
                        <?php } ?>
                    >
                </td>
                <td class='winbox' align='center'>
                    <input type='button' name='exec1' value='実行' onClick='PartsStockHistory.checkANDexecute(document.ConditionForm, 1, "<?php echo $_SERVER['QUERY_STRING'] ?>");' title='クリックすれば、この下に表示します。'>
                    &nbsp;
                    <input type='button' name='exec2' value='開く' onClick='PartsStockHistory.checkANDexecute(document.ConditionForm, 2, "<?php echo $_SERVER['QUERY_STRING'] ?>");' title='クリックすれば、別ウィンドウで表示します。'>
                    &nbsp;
                    <input type='button' name='clear' value='クリア' onClick='PartsStockHistory.viewClear();'>
                </td>
                <?php
                if ($stockViewFlg && $request->get('targetPartsNo')) {
                    echo "<td class='winbox' align='center'>\n";
                    if ($request->get('noMenu') && $request->get('material')) {
                        echo "&nbsp&nbsp<a href='javascript:location.replace(\"", $menu->out_action('在庫予定照会'), "?targetPartsNo=\" + escape(document.ConditionForm.targetPartsNo.value) + \"&material=1&noMenu=yes\")' style='text-decoration:none;'>在庫予定照会</a>\n";
                    } elseif ($request->get('noMenu')) {
                        echo "&nbsp&nbsp<a href='javascript:location.replace(\"", $menu->out_action('在庫予定照会'), "?targetPartsNo=\" + escape(document.ConditionForm.targetPartsNo.value) + \"&noMenu=yes\")' style='text-decoration:none;'>在庫予定照会</a>\n";
                    } elseif ($request->get('material')) {
                        echo "&nbsp&nbsp<a href='javascript:location.replace(\"", $menu->out_action('在庫予定照会'), "?targetPartsNo=\" + escape(document.ConditionForm.targetPartsNo.value) + \"&material=1\")' style='text-decoration:none;'>在庫予定照会</a>\n";
                    } else {
                        echo "&nbsp&nbsp<a href='javascript:location.replace(\"", $menu->out_action('在庫予定照会'), "?targetPartsNo=\" + escape(document.ConditionForm.targetPartsNo.value) )' style='text-decoration:none;'>在庫予定照会</a>\n";
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
