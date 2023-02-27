<?php
//////////////////////////////////////////////////////////////////////////////
// 刻印管理システム 検索メニュー            条件選択 Form       MVC View 部 //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/11/14 Created   punchMark_search_ViewCondForm.php                   //
// 2007/11/15 検索ボタンが押されたら部品番号の入力欄へ移動を追加            //
// 2007/11/19 th.cond クラス(CSS)を追加                                     //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=EUC-JP'>
<meta http-equiv='Content-Style-Type' content='text/css'>
<meta http-equiv='Content-Script-Type' content='text/javascript'>
<!-- <meta http-equiv='Refresh' content='15;URL=<?php echo $menu->out_self(), "?showMenu={$request->get('showMenu')}"?>'> -->
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<link rel='stylesheet' href='punchMark_search.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='punchMark_search.js?<?php echo $uniq ?>'></script>
</head>
<body style='overflow-y:hidden;'
    onLoad='
        PunchMarkSearch.set_focus(document.ConditionForm.parts_no, "noSelect");
        // PunchMarkSearch.intervalID = setInterval("PunchMarkSearch.blink_disp(\"blink_item\")", 1300);
        <?php if ($request->get('AutoStart') != '') echo 'PunchMarkSearch.checkANDexecute(document.ConditionForm, 1)'; ?>
    '
>
<center>
<?php echo $menu->out_title_border() ?>
    
    <form name='ConditionForm' action='<?php echo $menu->out_self() ?>' method='post'
        onSubmit='return PunchMarkSearch.checkANDexecute(this, 1)'
    >
        <!----------------- ここは 本文を表示する ------------------->
        <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='1'>
            <tr>
                <th class='winbox cond' nowrap>部品番号</th>
                <th class='winbox cond' nowrap>刻印コード</th>
                <th class='winbox cond' nowrap>棚　番</th>
                <th class='winbox cond' nowrap>刻印内容</th>
                <th class='winbox cond' nowrap>形　状</th>
                <th class='winbox cond' nowrap>客　先</th>
                <th class='winbox cond' nowrap>サイズ</th>
                <th class='winbox cond' nowrap>製作状況</th>
                <td class='winbox cond' align='center' rowspan='4'>
                    <input type='submit' class='pt11b' name='search' value='検索' onClick='PunchMarkSearch.set_focus(document.ConditionForm.parts_no, "noSelect");'>
                    <br>
                    <input type='button' class='pt11b' name='winSearch' value='開く' onClick='PunchMarkSearch.checkANDexecute(document.ConditionForm, 2);'>
                </td>
            </tr>
            <tr>
                <td class='winbox' align='center'><input type='text' name='parts_no' value='<?php echo $session->get_local('parts_no') ?>' size='10' maxlength='9' onKeyUp='baseJS.keyInUpper(this);'></td>
                <td class='winbox' align='center'><input type='text' name='punchMark_code' value='<?php echo $session->get_local('punchMark_code') ?>' size='6' maxlength='6'></td>
                <td class='winbox' align='center'><input type='text' name='shelf_no' value='<?php echo $session->get_local('shelf_no') ?>' size='6' maxlength='6'></td>
                <td class='winbox' align='center'><textarea name='mark' rows='3' cols='10' class='pt12b'><?php echo $session->get_local('mark') ?></textarea></td>
                <td class='winbox' align='center'>
                    <select name='shape_code' class='pt11b' size='1'>
                        <?php echo $model->getShapeCodeOptions($session) ?>
                    </select>
                </td>
                <td class='winbox' align='center'><input type='text' name='user_code' value='<?php echo $session->get_local('user_code') ?>' size='6' maxlength='5'></td>
                <td class='winbox' align='center'>
                    <select name='size_code' class='pt11b' size='1'>
                        <?php echo $model->getSizeCodeOptions($session) ?>
                    </select>
                </td>
                <td class='winbox' align='center'>
                    <select name='make_flg' class='pt11b' size='1'>
                        <?php echo $model->getMakeFlgOptions($session) ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th class='winbox cond' nowrap colspan='2'>部品マスター備考</th>
                <th class='winbox cond' nowrap colspan='2'>刻印マスター備考</th>
                <th class='winbox cond' nowrap colspan='2'>形状マスター備考</th>
                <th class='winbox cond' nowrap colspan='2'>サイズマスター備考</th>
            </tr>
            <tr>
                <td class='winbox' align='center' colspan='2'><input type='text' name='note_parts' value='<?php echo $session->get_local('note_parts') ?>' size='25' maxlength='30'></td>
                <td class='winbox' align='center' colspan='2'><input type='text' name='note_mark'  value='<?php echo $session->get_local('note_mark') ?>'  size='25' maxlength='30'></td>
                <td class='winbox' align='center' colspan='2'><input type='text' name='note_shape' value='<?php echo $session->get_local('note_shape') ?>' size='25' maxlength='30'></td>
                <td class='winbox' align='center' colspan='2'><input type='text' name='note_size'  value='<?php echo $session->get_local('note_size') ?>'  size='25' maxlength='30'></td>
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
