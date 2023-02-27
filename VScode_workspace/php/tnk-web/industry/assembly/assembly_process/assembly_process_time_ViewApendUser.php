<?php
//////////////////////////////////////////////////////////////////////////////
// 組立指示メニューの 着手・完了時間 集計用  MVC View 部                    //
//                                             組立着手 作業者 指示(ボタン) //
// Copyright (C) 2005-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/10/18 Created   assembly_process_time_ViewApendUserID.php           //
// 2005/10/24 style='ime-mode:disabled;' 誤ってIMEキーのONに対応のため追加  //
// 2005/11/23 ControlFormSubmit()メソッド 二重Submit対策で追加              //
// 2005/12/01 作業者が入力されていなければ計画番号へのボタンを表示しない    //
// 2006/04/07 </label> が抜けていた４箇所を修正                             //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>
<link rel='stylesheet' href='assembly_process_time.css?id=<?= $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='assembly_process_time.js?<?= $uniq ?>'></script>
</head>
<body onLoad='AssemblyProcessTime.set_focus(document.user_form.user_id, "select")'>
<center>
<?= $menu->out_title_border() ?>
    
    <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='1'>
        <tr><td> <!----------- ダミー(デザイン用) ------------>
    <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr>
        <form name='ControlForm' action='<?=$menu->out_self(), "?id={$uniq}"?>' method='post'>
            <td nowrap <?php if($showMenu=='apend') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return AssemblyProcessTime.ControlFormSubmit(document.ControlForm.elements["apend"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='apend' id='apend'
                <?php if($showMenu=='apend') echo 'checked' ?>>
                <label for='apend'>組立着手入力</label>
            </td>
            <td nowrap <?php if($showMenu=='StartList') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return AssemblyProcessTime.ControlFormSubmit(document.ControlForm.elements["StartList"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='StartList' id='StartList'
                <?php if($showMenu=='StartList') echo 'checked' ?>>
                <label for='StartList'>組立着手一覧</label>
            </td>
            <td nowrap <?php if($showMenu=='EndList') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return AssemblyProcessTime.ControlFormSubmit(document.ControlForm.elements["EndList"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='EndList' id='EndList'
                <?php if($showMenu=='EndList') echo 'checked' ?>>
                <label for='EndList'>組立完了一覧</label>
            </td>
            <td nowrap class='winbox'>
                <?=$pageControl?>
            </td>
            <td nowrap <?php if($showMenu=='group') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return AssemblyProcessTime.ControlFormSubmit(document.ControlForm.elements["group"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='group' id='group'
                <?php if($showMenu=='group') echo 'checked' ?>>
                <label for='group'>グループ編集</label>
            </td>
        </form>
        </tr>
    </table>
        </td></tr>
    </table> <!----------------- ダミーEnd ------------------>
    
    <div class='caption_font'></div>
    
    <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='1'>
        <caption>組立着手 作業者 指示</caption>
        <tr><td> <!-- ダミー -->
    <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <form name='user_form' action='<?=$menu->out_self(), "?id={$uniq}"?>' method='post' onSubmit='return AssemblyProcessTime.user_formCheck(this)'>
            <input type='hidden' name='showMenu' value='apend'>
        <tr>
            <td class='winbox pt12b' nowrap>
                社員番号
            </td>
            <td class='winbox' nowrap>
                <input type='text' name='user_id' value='<?=$user_id?>' size='8' maxlength='6'
                    style='ime-mode:disabled;' class='pt12b' onChange='this.value=this.value.toUpperCase()'
                >
                <input type='hidden' name='apendUser' value='dummy'>
            </td>
            <td class='winbox' nowrap>
                <input type='submit' name='apendUser' value='登録' class='pt12b'>
            </td>
            <?php if ($userRows >= 1) { ?>
            <td class='winbox' nowrap>
                <input type='button' name='userEnd' value='計画番号へ' class='pt12b'
                    onClick='location.replace("<?=$menu->out_self(), "?showMenu=apend&userEnd=go&", $model->get_htmlGETparm(), "&id={$uniq}"?>")'
                >
            </td>
            <?php } ?>
        </tr>
        </form>
    </table>
        </td></tr> <!-- ダミー -->
    </table>
    
    <?php if ($userRows >= 1) { ?>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <caption>組立着手 作業者 指示 一覧</caption>
            <tr><td> <!-- ダミー -->
        <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <th class='winbox'>&nbsp;</th>
            <th class='winbox'>&nbsp;</th>
            <th class='winbox' nowrap>社員番号</th>
            <th class='winbox' nowrap>作業者</th>
            <th class='winbox' nowrap>組立着手</th>
        <?php for ($r=0; $r<$userRows; $r++) { ?>
            <tr>
            <!-- No. -->
            <td class='winbox pt12b' align='right' nowrap><?=$r + 1 + $model->get_offset()?></td>
            <!-- 取消 -->
            <td class='winbox pt12b' align='center' nowrap>
                <a
                href='<?=$menu->out_self(), "?user_id={$userRes[$r][0]}&showMenu=apend&deleteUser=go&", $model->get_htmlGETparm(), "&id={$uniq}"?>'
                style='text-decoration:none;'
                onClick='return confirm("作業者の着手の取消をします宜しいですか？")'
                onMouseover="status='組立 作業者の 着手の取消を行います。';return true;"
                onMouseout="status=''"
                title='組立 作業者の 着手の取消を行います。'
                >
                    取消
                </a>
            </td>
            <!-- 社員番号 -->
            <td class='winbox pt12b' align='center' nowrap><?=$userRes[$r][0]?></td>
            <!-- 作業者 -->
            <td class='winbox pt12b' align='left' nowrap><?=$userRes[$r][1]?></td>
            <!-- 組立着手日時 -->
            <td class='winbox pt12b' align='center' nowrap><?=$userRes[$r][2]?></td>
            </tr>
        <?php } ?>
        </table>
            </td></tr> <!-- ダミー -->
        </table>
    <?php } ?>
</center>
</body>
<?php if ($_SESSION['s_sysmsg'] != '登録がありません！') { ?>
<?=$menu->out_alert_java()?>
<?php } ?>
</html>
