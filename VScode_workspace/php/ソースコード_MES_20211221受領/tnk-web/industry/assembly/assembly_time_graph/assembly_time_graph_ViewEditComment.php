<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ω�Υ饤���̹��� �Ƽ殺��� �����ȾȲ��Խ�             MVC View �� //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/05/12 Created   assembly_time_graph_ViewEditComment.php             //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title>��Ω��������դΥ����ȾȲ��Խ�</title>
<?php echo $this->menu->out_site_java() ?>
<?php echo $this->menu->out_css() ?>
<link rel='stylesheet' href='assembly_time_graph.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='assembly_time_graph.js?<?php echo $uniq ?>'></script>
</head>
<body style='overflow:hidden; background-color:#e6e6e6;'
    onLoad='
        AssemblyTimeGraph.set_focus(document.CommentForm.clear, "noSelect");
    '
>
<center>
    <form name='CommentForm' action='<?php echo $this->menu->out_self() ?>?showMenu=CommentSave&targetPlanNo=<?php echo urlencode($this->request->get('targetPlanNo'))?>&targetAssyNo=<?php echo urlencode($this->request->get('targetAssyNo'))?>' method='post'
        onSubmit='//return AssemblyTimeGraph.CommentCheckANDexecute(this)'
    >
        <div class='pt14b'><?php echo $this->result->get('title') ?></div>
        <textarea name='comment' cols='80' rows='10' wrap='virtual' style='background-color:floralwhite;'><?php echo $this->result->get('comment')?></textarea>
                    <!-- style='background-color:#e6e6e6;' readonly -->
        <div>
            <input type='button' name='close' value='��¸��λ' onClick='document.CommentForm.submit(); window.close()' style='position:relative; top:6px;'>
                &nbsp;&nbsp;
            <input type='submit' name='save' value='��¸' style='position:relative; top:6px;'>
                &nbsp;&nbsp;
            <input type='button' name='clear' value='�Ĥ���' onClick='window.close();' style='position:relative; top:6px;'>
        </div>
    </form>
    
    <div id='showAjax'>
    </div>
</center>
</body>
<?php echo $this->menu->out_alert_java()?>
</html>
