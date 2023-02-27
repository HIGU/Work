<?php
//////////////////////////////////////////////////////////////////////////////
// 組立指示メニューの 着手・完了時間 集計用  MVC View 部                    //
//                                 組立グループ(作業区) 登録・編集・一覧表  //
// Copyright (C) 2005-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/10/17 Created   assembly_process_time_ViewGroup.php                 //
// 2005/10/24 style='ime-mode:active;' はgroup_nameで使用したが使いにくい削 //
// 2005/11/23 ControlFormSubmit()メソッド 二重Submit対策で追加              //
// 2006/04/07 </label> が抜けていた４箇所を修正                             //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE html>
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
<body onLoad='AssemblyProcessTime.set_focus(document.group_form.<?=$focus?>), "noSelect"'>
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
    
    <div></div>
    
    <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='1'>
        <caption>組立グループ(作業区) の 登録</caption>
        <tr><td> <!-- ダミー -->
    <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <form name='group_form' action='<?=$menu->out_self(), "?id={$uniq}"?>' method='post' onSubmit='return AssemblyProcessTime.group_formCheck(this)'>
        <input type='hidden' name='showMenu' value='group'>
        <th class='winbox pt12b' nowrap>
            グループ番号
        </th>
        <th class='winbox pt12b' nowrap>
            グループ(作業区)名称
        </th>
        <th class='winbox pt12b' nowrap>
            事業部
        </th>
        <th class='winbox pt12b' nowrap>
            製品グループ
        </th>
        <th class='winbox pt12b' nowrap>
            &nbsp;
        </th>
        <tr>
            <td class='winbox' nowrap align='center'>
                <input type='text' name='Ggroup_no' value='<?=$Ggroup_no?>' size='3' maxlength='3'
                    style='ime-mode:disabled;' class='pt12b' onChange='this.value=this.value.toUpperCase()'
                    <?=$readonly?>
                >
            </td>
            <td class='winbox' nowrap align='center'>
                <input type='text' name='group_name' value='<?=$group_name?>' size='18' maxlength='10'
                    class='pt12b'
                >
            </td>
            <td class='winbox' nowrap align='center'>
                <select name='div' size='1'>
                    <option value='C'<?php if ($div=='C') echo ' selected'?>>カプラ</option>
                    <option value='L'<?php if ($div=='L') echo ' selected'?>>リニア</option>
                </select>
            </td>
            <td class='winbox' nowrap align='center'>
                <select name='product' size='1'>
                    <option value='C'<?php if ($product=='C') echo ' selected'?>>カプラ標準</option>
                    <option value='S'<?php if ($product=='S') echo ' selected'?>>カプラ特注</option>
                    <option value='L'<?php if ($product=='L') echo ' selected'?>>リニア製品</option>
                    <option value='B'<?php if ($product=='B') echo ' selected'?>>バイモル</option>
                </select>
                <input type='hidden' name='active' value='t'>
            </td>
            <td class='winbox' nowrap align='center'>
                <input type='submit' name='groupEdit' value='登録' class='pt12b'>
            </td>
        </tr>
        </form>
    </table>
        </td></tr> <!-- ダミー -->
    </table>
    <?php if ($rows >= 1) { ?>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <caption>組立グループ(作業区) 登録 一覧</caption>
            <tr><td> <!-- ダミー -->
        <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <th class='winbox'>&nbsp;</th>
            <th class='winbox'>&nbsp;</th>
            <th class='winbox'>&nbsp;</th>
            <th class='winbox' nowrap>グループ番号</th>
            <th class='winbox' nowrap>グループ(作業区)名称</th>
            <th class='winbox' nowrap>事業部</th>
            <th class='winbox' nowrap>製品区分</th>
                <!-- <th class='winbox' nowrap>登録日時</th> -->
            <th class='winbox' nowrap>有効・無効</th>
            <th class='winbox' nowrap>有効・無効の切替</th>
            <th class='winbox' nowrap>現在のグループ</th>
        <?php for ($r=0; $r<$rows; $r++) { ?>
            <tr<?php if ($res[$r][5] == '無効') echo " style='color:gray;'"?>>
            <!-- No. -->
            <td class='winbox' align='right' nowrap><?=$r + 1 + $model->get_offset()?></td>
            <!-- 削除 -->
            <td class='winbox pt12b' align='center' nowrap>
                <a
                href='<?=$menu->out_self(), "?Ggroup_no={$res[$r][0]}&showMenu=group&groupOmit=go&group_name=", urlencode($res[$r][1]), '&', $model->get_htmlGETparm(), "&id={$uniq}"?>'
                style='text-decoration:none;'
                onMouseover="status='組立(グループ)作業区の削除を行います。実績がある場合は絶対に削除せず無効にして下さい。';return true;"
                onMouseout="status=''"
                title='組立(グループ)作業区の削除を行います。実績がある場合は絶対に削除せず無効にして下さい。'
                onClick='return confirm("実績データが一度もなければ削除しても問題ありませんが\n\nある場合は削除せず無効にして下さい。\n\n削除します宜しいですか？")'
                >
                    削除
                </a>
            </td>
            <!-- 変更 -->
            <td class='winbox pt12b' align='center' nowrap>
                <a
                href='<?=$menu->out_self(), "?Ggroup_no={$res[$r][0]}&showMenu=group&groupCopy=go&group_name=", urlencode($res[$r][1]), "&div={$res[$r][6]}&product={$res[$r][7]}&", $model->get_htmlGETparm(), "&id={$uniq}"?>'
                style='text-decoration:none;'
                onMouseover="status='登録内容の編集を行います。変更が無ければ実行しても登録されません。';return true;"
                onMouseout="status=''"
                title='登録内容の編集を行います。変更が無ければ実行しても登録されません。'
                >
                    変更
                </a>
            </td>
            <!-- グループ番号 -->
            <td class='winbox pt12b' align='right' nowrap><?=$res[$r][0]?>&nbsp;&nbsp;</td>
            <!-- グループ(作業区)名称 -->
            <td class='winbox pt12b' align='left' nowrap><?=$res[$r][1]?></td>
            <!-- 事業部 -->
            <td class='winbox pt12b' align='left' nowrap><?=$res[$r][2]?></td>
            <!-- 製品区分 -->
            <td class='winbox pt12b' align='left' nowrap><?=$res[$r][3]?></td>
            <!-- 登録日時 -->
                <!-- <td class='winbox pt12b' align='center' nowrap><?=$res[$r][4]?></td> -->
            <!-- 有効・無効 -->
            <td class='winbox pt12b' align='center' nowrap><?=$res[$r][5]?></td>
            <!-- 有効・無効の切替 -->
            <td class='winbox pt12b' align='center' nowrap>
                <?php if ($res[$r][0] == $group_no) { ?>
                できません
                <?php } else { ?>
                <a
                href='<?=$menu->out_self(), "?Ggroup_no={$res[$r][0]}&showMenu=group&groupActive=go&group_name=", urlencode($res[$r][1]), '&', $model->get_htmlGETparm(), "&id={$uniq}"?>'
                style='text-decoration:none;'
                onMouseover="status='組立(グループ)作業区の有効・無効を切替えます。';return true;"
                onMouseout="status=''"
                title='組立(グループ)作業区の有効・無効を切替えます。'
                >
                    <?php if ($res[$r][5] == '有効') { ?>
                    無効にする
                    <?php } else { ?>
                    有効にする
                    <?php } ?>
                </a>
                <?php } ?>
            </td>
            <!-- 現在のグループ -->
            <td class='winbox pt11' align='center' nowrap>
                <?php if ($res[$r][5] == '無効') { ?>
                できません
                <?php } else { ?>
                <a
                href='javascript:AssemblyProcessTime.groupChange("<?=$res[$r][0]?>", "<?=$menu->out_self(), "?showMenu=group&", $model->get_htmlGETparm(), "&id={$uniq}"?>")'
                style='text-decoration:none;'
                onMouseover="status='このパソコンの組立(グループ)作業区の設定をこのグループへ切替えます。';return true;"
                onMouseout="status=''"
                title='このパソコンの組立(グループ)作業区の設定をこのグループへ切替えます。'
                >
                    <?php if ($res[$r][0] == $group_no) { ?>
                    <span class='pt12b' style='color:red;'>◎</span>
                    <?php } else { ?>
                    このグループにする
                    <?php } ?>
                </a>
                <?php } ?>
            </td>
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
