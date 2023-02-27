<?php
//////////////////////////////////////////////////////////////////////////////
// 適正在庫数の照会 直近三年間の出荷数÷３×２                 MVC View 部  //
// Copyright (C) 2008 Norihisa.Ohya usoumu@nitto-kohki.co.jp                //
// Changed history                                                          //
// 2008/06/17 Created   reasonable_stock_ViewCondForm.php                   //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<!-- <meta http-equiv="Refresh" content="15;URL=<?php echo $this->menu->out_self(), "?showMenu={$this->request->get('showMenu')}"?>"> -->
<title><?php echo $this->menu->out_title() ?></title>
<?php echo $this->menu->out_site_java() ?>
<?php echo $this->menu->out_css() ?>
<link rel='stylesheet' href='reasonable_stock.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='reasonable_stock.js?<?php echo $uniq ?>'></script>
</head>
<body style='overflow-y:hidden;'
    onLoad='
        ReasonableStock.set_focus(document.ConditionForm.exec, "noSelect");
        setInterval("ReasonableStock.blink_disp(\"blink_item\")", 500);
        <?php if ($this->request->get('showMenu') == 'Both') echo "ReasonableStock.checkANDexecute(document.ConditionForm);\n"; ?>
    '
>
<center>
<?php echo $this->menu->out_title_border() ?>
    
    <form name='ConditionForm' action='<?php echo $this->menu->out_self() ?>' method='post'
        onSubmit='return ReasonableStock.checkANDexecute(this)'
    >
        <!----------------- ここは 本文を表示する ------------------->
        <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='1'>
            <tr>
                    <!--  bgcolor='#ffffc6' 薄い黄色 --> 
                <td colspan='4' width='800' align='center' class='winbox caption_color'>
                    <span id='blink_item'>製品グループと在庫基準年月を指定して下さい。</span>
                </td>
            </tr>
            <tr>
                <td class='winbox' align='center' nowrap>
                    製品グループ<br>
                    <select name='targetDivision' onChange='ReasonableStock.checkANDexecute(ConditionForm)'>
                        <option value='AL'<?php if($this->request->get('targetDivision')=='AL')echo ' selected'?>>全グループ</option>
                        <option value='CA'<?php if($this->request->get('targetDivision')=='CA')echo ' selected'?>>カプラ全体</option>
                        <option value='CH'<?php if($this->request->get('targetDivision')=='CH')echo ' selected'?>>カプラ標準</option>
                        <option value='CS'<?php if($this->request->get('targetDivision')=='CS')echo ' selected'?>>カプラ特注</option>
                        <option value='LA'<?php if($this->request->get('targetDivision')=='LA')echo ' selected'?>>リニア全体</option>
                        <option value='LH'<?php if($this->request->get('targetDivision')=='LH')echo ' selected'?>>リニアのみ</option>
                        <option value='LB'<?php if($this->request->get('targetDivision')=='LB')echo ' selected'?>>バイモル</option>
                        <option value='OT'<?php if($this->request->get('targetDivision')=='OT')echo ' selected'?>>その他入庫</option>
                    </select>
                </td>
                <td class='winbox' align='center' nowrap>
                    <input type='checkbox' name='targetOutFlg' id='OutFlg' value='on'<?php if($this->request->get('targetOutFlg')=='on')echo ' checked'?>>
                    <label for='OutFlg'>前日の在庫で計算</label>
                    <br>
                    <select name='targetOutDate'>
                        <?php
                        $ym = date("Ym");
                        while(1) {
                            if (substr($ym,4,2)!=01) {
                                $ym--;
                            } else {
                                $ym = $ym - 100;
                                $ym = $ym + 11;
                            }
                            printf("<option value='%d'>%s年%s月</option>\n",$ym,substr($ym,0,4),substr($ym,4,2));
                            if ($ym <= 200804)
                                break;
                        }
                        ?>
                    </select>
                    末在庫
                </td>
                <td class='winbox' align='center' nowrap>
                    <input type='submit' name='exec' value='実行'>
                    &nbsp;&nbsp;
                    <input type='button' name='clear' value='クリア' onClick='ReasonableStock.viewClear();'>
                    <?php
                    if (getCheckAuthority(24)) {                                  //認証チェック
                    ?>
                    </form>
                    <form method='post' action='<?php echo $this->menu->out_action('適正在庫計算')?>'>
                        <input type='submit' name='calc' value='適正在庫計算'>
                    </form>
                    <?php
                    }
                    ?>
                </td>
            </tr>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
    </form>
    
    <div id='showAjax'>
    </div>
</center>
</body>
<?php echo $this->menu->out_alert_java()?>
</html>
