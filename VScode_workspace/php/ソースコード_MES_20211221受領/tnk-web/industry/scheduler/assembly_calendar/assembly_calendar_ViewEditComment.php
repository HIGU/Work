<?php
//////////////////////////////////////////////////////////////////////////////
// 組立ラインのカレンダー メンテナンス  コメント照会・編集      MVC View 部 //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/06/24 Created   assembly_calendar_ViewEditComment.php               //
//            NN7.1の対応でwindow.close()→setTimeout("window.close()", 400)//
//            400=親のリロード時間に依存する(カレンダーリロードで200はNG)   //
//            onUnload='parentReload();'を行いたいがNN7.1がNGなのでコメント //
//            onUnload='if (document.all) parentReload();'で対応            //
//            submit時にG_reloadFlg=false;にしてリロードで失う親子関係を維持//
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title>組立ラインカレンダーのコメント照会・編集</title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<link rel='stylesheet' href='assembly_calendar.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='assembly_calendar.js?<?php echo $uniq ?>'></script>
<script type='text/javascript'>
var G_reloadFlg = true;
function parentReload()
{
    // window.opener.location.replace('<?php echo $menu->out_self(), "?id={$uniq}"?>');
    if (!window.opener.parent.AssemblyCalendar) return; //でもIEならOK NN7.1ではNG対応でonUnloadにifを追加(try catchでもOK)
    window.opener.parent.AssemblyCalendar.AjaxLoadUrl
    ("<?php echo "{$menu->out_self()}?showMenu=Calendar&targetCalendar=Comment&year={$request->get('year')}&month={$request->get('month')}&day={$request->get('day')}&id={$uniq}" ?>");
}
</script>
</head>
<body style='overflow:hidden; background-color:#e6e6e6;'
    onLoad='
        setInterval("AssemblyCalendar.winActiveChk()", 30);
        AssemblyCalendar.set_focus(document.CommentForm.note, "noSelect");
        // AssemblyCalendar.set_focus(document.CommentForm.clear, "noSelect");
    '
    onUnload='if (document.all) if (G_reloadFlg) parentReload(); // IEなら'
>
<center>
    <form name='CommentForm' action='<?php echo "{$menu->out_self()}?Action=CommentSave&showMenu=EditComment&year={$request->get('year')}&month={$request->get('month')}&day={$request->get('day')}&id={$uniq}"?>' method='post'
        onSubmit='G_reloadFlg=false;'
    >
        <div class='pt14b'><?php echo $result->get('title') ?></div>
        <!-- <textarea name='note' cols='50' rows='5' wrap='virtual' style='background-color:floralwhite;'><?php echo $result->get('note')?></textarea> -->
        <input type='text' name='note' size='40' maxlength='50' value='<?php echo $result->get('note')?>'
            title='停止日にした理由・稼働日にした理由等を入力します。' style='height:70px;' class='pt14b'
        >
                    <!-- style='background-color:#e6e6e6;' readonly -->
        <div style='position:relative; top:6px;'>
            <input type='button' name='close' value='登録' style='color:blue;' onClick='G_reloadFlg=false; document.CommentForm.submit();'>
                &nbsp;&nbsp;
            <input type='button' name='clear' value='閉じる' onClick='parentReload(); setTimeout("window.close()", 400);'>
        </div>
    </form>
    
    <div id='showAjax'>
    </div>
</center>
</body>
<?php echo $menu->out_alert_java(false)?>
</html>
