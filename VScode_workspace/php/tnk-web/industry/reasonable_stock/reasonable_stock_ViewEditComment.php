<?php
//////////////////////////////////////////////////////////////////////////////
// 適正在庫数の照会 直近三年間の出荷数÷３×２         コメント MVC View 部 //
// Copyright (C) 2008 Norihisa.Ohya usoumu@nitto-kohki.co.jp                //
// Changed history                                                          //
// 2008/06/17 Created   reasonable_stock_ViewEditComment.php                //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title>長期滞留部品のコメント照会・編集</title>
<?php echo $this->menu->out_site_java() ?>
<?php echo $this->menu->out_css() ?>
<link rel='stylesheet' href='reasonable_stock.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='reasonable_stock.js?<?php echo $uniq ?>'></script>
</head>
<body style='overflow:hidden; background-color:#e6e6e6;'
    onLoad='
        ReasonableStock.set_focus(document.CommentForm.clear, "noSelect");
    '
>
<center>
    <form name='CommentForm' action='<?php echo $this->menu->out_self() ?>?showMenu=CommentSave&targetPartsNo=<?php echo urlencode($this->request->get('targetPartsNo'))?>' method='post'
        onSubmit='//return ReasonableStock.CommentCheckANDexecute(this)'
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
