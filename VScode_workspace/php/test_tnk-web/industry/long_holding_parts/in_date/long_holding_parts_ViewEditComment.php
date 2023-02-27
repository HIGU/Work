<?php
//////////////////////////////////////////////////////////////////////////////
// 長期滞留部品の照会 最終入庫日指定で現在在庫がある物 コメント MVC View 部 //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/04/07 Created   long_holding_parts_ViewEditComment.php              //
// 2006/04/10 <form>にurlencode($this->request->get('targetPartsNo'))を追加 //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title>長期滞留部品のコメント照会・編集</title>
<?php echo $this->menu->out_site_java() ?>
<?php echo $this->menu->out_css() ?>
<link rel='stylesheet' href='long_holding_parts.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='long_holding_parts.js?<?php echo $uniq ?>'></script>
</head>
<body style='overflow:hidden; background-color:#e6e6e6;'
    onLoad='
        LongHoldingParts.set_focus(document.CommentForm.clear, "noSelect");
    '
>
<center>
    <form name='CommentForm' action='<?php echo $this->menu->out_self() ?>?showMenu=CommentSave&targetPartsNo=<?php echo urlencode($this->request->get('targetPartsNo'))?>' method='post'
        onSubmit='//return LongHoldingParts.CommentCheckANDexecute(this)'
    >
        <div class='pt14b'><?php echo $this->result->get('title') ?></div>
        <textarea name='comment' cols='80' rows='10' wrap='virtual' style='background-color:floralwhite;'><?php echo $this->result->get('comment')?></textarea>
                    <!-- style='background-color:#e6e6e6;' readonly -->
        <div>
            <input type='button' name='close' value='保存終了' onClick='document.CommentForm.submit(); window.close()' style='position:relative; top:6px;'>
                &nbsp;&nbsp;
            <input type='submit' name='save' value='保存' style='position:relative; top:6px;'>
                &nbsp;&nbsp;
            <input type='button' name='clear' value='閉じる' onClick='window.close();' style='position:relative; top:6px;'>
        </div>
    </form>
    
    <div id='showAjax'>
    </div>
</center>
</body>
<?php echo $this->menu->out_alert_java()?>
</html>
