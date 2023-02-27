<?php
//////////////////////////////////////////////////////////////////////////////
// 部品売上げの材料費(購入費)の 照会   条件選択 Form  (ベース)  MVC View 部 //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/02/15 Created   parts_material_show_ViewCondForm.php                //
// 2006/02/20 結果表示領域のクリアーボタンを追加                            //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<!-- <meta http-equiv="Refresh" content="15;URL=<?=$this->menu->out_self(), "?showMenu={$this->request->get('showMenu')}"?>"> -->
<title><?= $this->menu->out_title() ?></title>
<?= $this->menu->out_site_java() ?>
<?= $this->menu->out_css() ?>
<link rel='stylesheet' href='parts_material_show.css?id=<?= $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='parts_material_show.js?<?= $uniq ?>'></script>
</head>
<body
    onLoad='
        PartsMaterialShow.set_focus(document.ConditionForm.showDiv, "");
        setInterval("PartsMaterialShow.blink_disp(\"blink_item\")", 500);
        //setInterval("PartsMaterialShow.AjaxLoadTable(\"ListTable\")", 15000);
    '
>
<center>
<?= $this->menu->out_title_border() ?>
    
    <form name='ConditionForm' action='<?= $this->menu->out_self() ?>' method='post'
        onSubmit='return PartsMaterialShow.checkANDexecute(this)'
    >
        <!----------------- ここは 本文を表示する ------------------->
        <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='5'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>
            <tr>
                    <!--  bgcolor='#ffffc6' 薄い黄色 --> 
                <td colspan='2' align='center' class='caption_font'>
                    <span id='blink_item'>照会 条件を指定して下さい。</span>
                </td>
            </tr>
            <tr>
                <td class='winbox' align='right'>
                    部門を選択して下さい
                </td>
                <td class='winbox' align='center'>
                    <select name='showDiv' class='pt12b'>
                        <option value=' '<?php if($this->request->get('showDiv')=='')  echo('selected'); ?>>全グループ</option>
                        <option value='C'<?php if($this->request->get('showDiv')=='C') echo('selected'); ?>>カプラ全体</option>
                        <option value='L'<?php if($this->request->get('showDiv')=='L') echo('selected'); ?>>リニア全体</option>
                        <!------------------------------------
                        <option value='H'<?php if($this->request->get('showDiv')=='H') echo('selected'); ?>>カプラ標準</option>
                        <option value='S'<?php if($this->request->get('showDiv')=='S') echo('selected'); ?>>カプラ特注</option>
                        <option value='M'<?php if($this->request->get('showDiv')=='M') echo('selected'); ?>>リニア標準</option>
                        <option value='B'<?php if($this->request->get('showDiv')=='B') echo('selected'); ?>>バイモル</option>
                        <option value='T'<?php if($this->request->get('showDiv')=='T') echo('selected'); ?>>ツール</option>
                        ------------------------------------->
                    </select>
                </td>
            </tr>
            <tr>
                <td class='winbox' align='right'>
                    日付を指定して下さい(必須)
                </td>
                <td class='winbox' align='center'>
                    <input type='text' name='targetDateStr' class='pt12b' size='8' value='<?php echo $this->request->get('targetDateStr'); ?>' maxlength='8'>
                    〜
                    <input type='text' name='targetDateEnd' class='pt12b' size='8' value='<?php echo $this->request->get('targetDateEnd'); ?>' maxlength='8'>
                </td>
            </tr>
            <tr>
                <td class='winbox' align='right'>
                    部品番号の指定
                    (指定しない場合は空白)
                </td>
                <td class='winbox' align='center'>
                    <input type='text' name='targetItemNo' size='9' class='pt12b' value='<?php echo $this->request->get('targetItemNo'); ?>' maxlength='9'>
                </td>
            </tr>
            <tr>
                <td class='winbox' align='right' width='400'>
                    売上区分=
                    １：製品(完成) ２：部品(合計) ５：部品(移動) ６：部品(直納) ７：部品(売上)
                    ８：部品(振替) ９：部品(受注)
                </td>
                <td class='winbox' align='center'>
                    <select name='targetSalesSegment'>
                        <option value='2'<?php if($this->request->get('targetSalesSegment')=='2') echo('selected'); ?>>2部品</option>
                        <option value='5'<?php if($this->request->get('targetSalesSegment')=='5') echo('selected'); ?>>5移動</option>
                        <option value='6'<?php if($this->request->get('targetSalesSegment')=='6') echo('selected'); ?>>6直納</option>
                        <option value='7'<?php if($this->request->get('targetSalesSegment')=='7') echo('selected'); ?>>7売上</option>
                        <option value='8'<?php if($this->request->get('targetSalesSegment')=='8') echo('selected'); ?>>8振替</option>
                        <option value='9'<?php if($this->request->get('targetSalesSegment')=='9') echo('selected'); ?>>9受注</option>
                        <!-----------------------------
                        <option value='1'<?php if($this->request->get('targetSalesSegment')=='1') echo('selected'); ?>>1製品</option>
                        ------------------------------->
                    <select>
                </td>
            </tr>
            <tr>
                <td class='winbox' colspan='2' align='center'>
                    <input type='submit' name='exec' value='実行'>
                    &nbsp;&nbsp;
                    <input type='button' name='clear' value='クリア' onClick='PartsMaterialShow.viewClear();'>
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
<?=$this->menu->out_alert_java()?>
</html>
