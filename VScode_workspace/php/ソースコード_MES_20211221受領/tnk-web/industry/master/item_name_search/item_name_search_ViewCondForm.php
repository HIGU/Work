<?php
//////////////////////////////////////////////////////////////////////////////
// アイテムマスターの品名による前方検索・部分検索              MVC View 部  //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/04/10 Created   item_name_search_ViewCondForm.php                   //
// 2006/05/22 材質によるマスター検索を追加 targetItemMaterial targetLimit   //
// 2006/05/23 在庫チェックオプションを追加 targetStockOption                //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<!-- <meta http-equiv="Refresh" content="15;URL=<?php echo $this->menu->out_self(), "?showMenu={$this->request->get('showMenu')}"?>"> -->
<title><?php echo $this->menu->out_title() ?></title>
<?php echo $this->menu->out_site_java() ?>
<?php echo $this->menu->out_css() ?>
<link rel='stylesheet' href='item_name_search.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='item_name_search.js?<?php echo $uniq ?>'></script>
</head>
<body style='overflow-y:hidden;'
    onLoad='
        ItemNameSearch.set_focus(document.ConditionForm.targetItemName, "noSelect");
        ItemNameSearch.intervalID = setInterval("ItemNameSearch.blink_disp(\"blink_item\")", 1300);
        <?php if ($this->request->get('showMenu') == 'Both') echo "ItemNameSearch.checkANDexecute(document.ConditionForm);\n"; ?>
    '
>
<center>
<?php echo $this->menu->out_title_border() ?>
    
    <form name='ConditionForm' action='<?php echo $this->menu->out_self() ?>' method='post'
        onSubmit='return ItemNameSearch.checkANDexecute(this)'
    >
        <!----------------- ここは 本文を表示する ------------------->
        <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='1'>
            <tr>
                    <!--  bgcolor='#ffffc6' 薄い黄色 --> 
                <td colspan='6' width='850' align='center' class='winbox caption_color'>
                    <span id='blink_item'>品名または材質に検索文字を入れてEnterキーか実行ボタンを押して下さい。</span>
                </td>
            </tr>
            <tr>
                <td class='winbox' align='center' nowrap>
                    品名(最初の何文字か一部分でもOK)<br>
                    <input type='text' name='targetItemName' size='30' maxlength='38' value='<?php echo $this->request->get('targetItemName')?>'>
                </td>
                <td class='winbox' align='center' nowrap>
                    材質(品名と同様)<br>
                    <input type='text' name='targetItemMaterial' size='10' maxlength='15' value='<?php echo $this->request->get('targetItemMaterial')?>'>
                </td>
                <td class='winbox' align='center' nowrap>
                    製品グループ<br>
                    <select name='targetDivision' onChange='//ItemNameSearch.checkANDexecute(ConditionForm)'>
                        <option value='A'<?php if($this->request->get('targetDivision')=='A')echo ' selected'?>>すべて</option>
                        <option value='C'<?php if($this->request->get('targetDivision')=='C')echo ' selected'?>>カプラ</option>
                        <option value='L'<?php if($this->request->get('targetDivision')=='L')echo ' selected'?>>リニア</option>
                        <option value='T'<?php if($this->request->get('targetDivision')=='T')echo ' selected'?>>ツール</option>
                        <option value='O'<?php if($this->request->get('targetDivision')=='O')echo ' selected'?>>その他</option>
                    </select>
                </td>
                <td class='winbox' align='center' nowrap>
                    在庫チェック<br>
                    <select name='targetStockOption' onChange='//ItemNameSearch.checkANDexecute(ConditionForm)'>
                        <option value='0'<?php if($this->request->get('targetStockOption')=='0')echo ' selected'?>>在庫無視する</option>
                        <option value='1'<?php if($this->request->get('targetStockOption')=='1')echo ' selected'?>>マスターあり</option>
                        <option value='2'<?php if($this->request->get('targetStockOption')=='2')echo ' selected'?>>在庫経歴あり</option>
                        <option value='3'<?php if($this->request->get('targetStockOption')=='3')echo ' selected'?>>現在在庫あり</option>
                    </select>
                </td>
                <td class='winbox' align='center' nowrap>
                    最大検索行数<br>
                    <select name='targetLimit' onChange='//ItemNameSearch.checkANDexecute(ConditionForm)'>
                        <option value=' 300'<?php if($this->request->get('targetLimit')== 300)echo ' selected'?>>　３００</option>
                        <option value=' 600'<?php if($this->request->get('targetLimit')== 600)echo ' selected'?>>　６００</option>
                        <option value='1000'<?php if($this->request->get('targetLimit')==1000)echo ' selected'?>>１０００</option>
                        <option value='2000'<?php if($this->request->get('targetLimit')==2000)echo ' selected'?>>２０００</option>
                        <option value='4000'<?php if($this->request->get('targetLimit')==4000)echo ' selected'?>>４０００</option>
                        <option value='8000'<?php if($this->request->get('targetLimit')==8000)echo ' selected'?>>８０００</option>
                        <option value='10000'<?php if($this->request->get('targetLimit')==8000)echo ' selected'?>>１００００</option>
                    </select>
                </td>
                <td class='winbox' align='center' nowrap>
                    <input type='submit' name='exec' value='実行'>
                    &nbsp;
                    <input type='button' name='clear' value='クリア' onClick='ItemNameSearch.viewClear();'>
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
