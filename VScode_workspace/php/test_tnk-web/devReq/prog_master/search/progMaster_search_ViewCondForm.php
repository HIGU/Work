<?php
//////////////////////////////////////////////////////////////////////////////
// プログラム管理メニュー プログラムの検索  条件選択 Form       MVC View 部 //
// Copyright (C) 2010 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2010/01/26 Created   progMaster_search_ViewCondForm.php                  //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
<meta http-equiv='Content-Style-Type' content='text/css'>
<meta http-equiv='Content-Script-Type' content='text/javascript'>
<!-- <meta http-equiv='Refresh' content='15;URL=<?php echo $menu->out_self(), "?showMenu={$request->get('showMenu')}"?>'> -->
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<link rel='stylesheet' href='progMaster_search.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='progMaster_search.js?<?php echo $uniq ?>'></script>
</head>
<body style='overflow-y:hidden;'
    onLoad='
        ProgMasterSearch.set_focus(document.ConditionForm.pid, "noSelect");
        // ProgMasterSearch.intervalID = setInterval("ProgMasterSearch.blink_disp(\"blink_item\")", 1300);
        <?php if ($request->get('AutoStart') != '') echo 'ProgMasterSearch.checkANDexecute(document.ConditionForm, 1)'; ?>
    '
>
<center>
<?php echo $menu->out_title_border() ?>
    
    <form name='ConditionForm' action='<?php echo $menu->out_self() ?>' method='post'
        onSubmit='return ProgMasterSearch.checkANDexecute(this, 1)'
    >
        <!----------------- ここは 本文を表示する ------------------->
        <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='1'>
            <tr>
                <th class='winbox cond' nowrap>プログラムID</th>
                <th class='winbox cond' nowrap>フォルダ名</th>
                <td class='winbox cond' align='center' rowspan='4'>
                    <input type='submit' class='pt11b' name='search' value='検索' onClick='ProgMasterSearch.set_focus(document.ConditionForm.pid, "noSelect");'>
                    <br>
                    <input type='button' class='pt11b' name='winSearch' value='開く' onClick='ProgMasterSearch.checkANDexecute(document.ConditionForm, 2);'>
                </td>
            </tr>
            <tr>
                <td class='winbox' align='center'><input type='text' name='pid' value='<?php echo $session->get_local('pid') ?>' size='40' maxlength='38'></td>
                <td class='winbox' align='center'>
                    <select name='dir' class='pt11b' size='1'>
                        <?php echo $model->getDirOptions($session) ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th class='winbox cond' nowrap>使用DB</th>
                <th class='winbox cond' nowrap>プログラム内容</th>
            </tr>
            <tr>
                <td class='winbox' align='center'><input type='text' name='db' value='<?php echo $session->get_local('db') ?>' size='40' maxlength='38'></td>
                <td class='winbox' align='center'><input type='text' name='name_comm' value='<?php echo $session->get_local('name_comm') ?>' size='40' maxlength='38'></td>
            </tr>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
    </form>
    <div id='showAjax'>
    </div>
</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
