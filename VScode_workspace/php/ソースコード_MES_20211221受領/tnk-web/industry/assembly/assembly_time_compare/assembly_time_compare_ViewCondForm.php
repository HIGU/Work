<?php
//////////////////////////////////////////////////////////////////////////////
// 組立の完成一覧より実績工数と登録工数の比較   条件選択 Form   MVC View 部 //
// Copyright (C) 2006-2013 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/03/09 Created   assembly_time_compare_ViewCondForm.php              //
// 2006/03/13 製品区分の選択として targetDivision を追加                    //
// 2006/05/10 手作業・自動機・外注・全体 別に照会オプションを追加           //
// 2007/09/03 phpのショートカットタグを標準タグ(推奨値)へ変更               //
//               製品番号を指定できるように追加(高野絹江さんから依頼)       //
// 2013/01/29 バイモルを液体ポンプへ変更 表示のみデータはバイモルのまま 大谷//
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
<link rel='stylesheet' href='assembly_time_compare.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='assembly_time_compare.js?<?php echo $uniq ?>'></script>
</head>
<body style='overflow-y:hidden;'
    onLoad='
        AssemblyTimeCompare.set_focus(document.ConditionForm.targetDateStr, "select");
        setInterval("AssemblyTimeCompare.blink_disp(\"blink_item\")", 500);
        <?php if ($this->request->get('targetPlanNo') != '') echo "AssemblyTimeCompare.checkANDexecute(document.ConditionForm);\n"; ?>
        <?php if ($this->request->get('showMenu') == 'Both') echo "AssemblyTimeCompare.checkANDexecute(document.ConditionForm);\n"; ?>
    '
>
<center>
<?php echo $this->menu->out_title_border() ?>
    
    <form name='ConditionForm' action='<?php echo $this->menu->out_self() ?>' method='post'
        onSubmit='return AssemblyTimeCompare.checkANDexecute(this)'
    >
        <!----------------- ここは 本文を表示する ------------------->
        <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='1'>
            <tr>
                    <!--  bgcolor='#ffffc6' 薄い黄色 --> 
                <td colspan='7' width='760' align='center' class='winbox caption_color'>
                    <span id='blink_item'>完成日の範囲を指定して下さい。</span>
                </td>
            </tr>
            <tr>
                <td class='winbox' align='right'>
                    完成日
                </td>
                <td class='winbox' align='center'>
                    <input type='text' name='targetDateStr' size='8' class='pt14b' value='<?php echo $this->request->get('targetDateStr'); ?>' maxlength='8'>
                〜
                    <input type='text' name='targetDateEnd' size='8' class='pt14b' value='<?php echo $this->request->get('targetDateEnd'); ?>' maxlength='8'>
                </td>
                <td class='winbox' align='right' title='製品番号はわかる部分だけ入れれば、その部分に合致するものを検索します。'>
                    製品番号
                </td>
                <td class='winbox' align='center' title='製品番号はわかる部分だけ入れれば、その部分に合致するものを検索します。'>
                    <input type='text' name='targetAssyNo' size='10' class='pt14b' value='<?php echo $this->request->get('targetAssyNo'); ?>' maxlength='9'
                        onKeyUp='baseJS.keyInUpper(this);'
                        title='製品番号はわかる部分だけ入れれば、その部分に合致するものを検索します。'
                    >
                </td>
                <td class='winbox' align='center'>
                    <select name='targetDivision' onChange='AssemblyTimeCompare.checkANDexecute(ConditionForm)'>
                        <option value='AL'<?php if($this->request->get('targetDivision')=='AL')echo ' selected'?>>製品全体</option>
                        <option value='CA'<?php if($this->request->get('targetDivision')=='CA')echo ' selected'?>>Ｃ全体</option>
                        <option value='CH'<?php if($this->request->get('targetDivision')=='CH')echo ' selected'?>>Ｃ標準</option>
                        <option value='CS'<?php if($this->request->get('targetDivision')=='CS')echo ' selected'?>>Ｃ特注</option>
                        <option value='LA'<?php if($this->request->get('targetDivision')=='LA')echo ' selected'?>>Ｌ全体</option>
                        <option value='LH'<?php if($this->request->get('targetDivision')=='LH')echo ' selected'?>>リニア</option>
                        <option value='LB'<?php if($this->request->get('targetDivision')=='LB')echo ' selected'?>>液体ポンプ</option>
                    </select>
                </td>
                <td class='winbox' align='center'>
                    <select name='targetProcess' onChange='AssemblyTimeCompare.checkANDexecute(ConditionForm)'>
                        <option value='H'<?php if($this->request->get('targetProcess')=='H')echo ' selected'?>>手作業工程</option>
                        <option value='M'<?php if($this->request->get('targetProcess')=='M')echo ' selected'?>>自動機工程</option>
                        <option value='G'<?php if($this->request->get('targetProcess')=='G')echo ' selected'?>>外　注工程</option>
                        <option value='A'<?php if($this->request->get('targetProcess')=='A')echo ' selected'?>>全　体工程</option>
                    </select>
                </td>
                <td class='winbox' align='center'>
                    <input type='submit' name='exec' value='実行'>
                    &nbsp;&nbsp;
                    <input type='button' name='clear' value='クリア' onClick='AssemblyTimeCompare.viewClear();'>
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
