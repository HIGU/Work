<?php
//////////////////////////////////////////////////////////////////////////////
// 組立指示メニューの 着手・完了時間 集計用  MVC View 部                    //
//                                              組立着手 指示 入力(登録)    //
// Copyright (C) 2005-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/10/18 Created   assembly_process_time_ViewApend.php                 //
// 2005/10/24 style='ime-mode:disabled;' 誤ってIMEキーのONに対応のため追加  //
// 2005/11/23 ControlFormSubmit()メソッド 二重Submit対策で追加              //
// 2005/11/30 計画数を計画残へ変更。それに伴いダブルクリックで明細照会追加  //
// 2006/04/07 </label> が抜けていた４箇所を修正                             //
// 2006/05/19 計画入力時、登録工数表示機能追加 $model->outViewKousu($menu)  //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<link rel='stylesheet' href='assembly_process_time.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='assembly_process_time.js?<?php echo $uniq ?>'></script>
</head>
<body onLoad='
    AssemblyProcessTime.set_focus(document.start_form.plan_no, "select")
    <?php echo $model->outViewKousu($menu) ?>
'>
<center>
<?php echo $menu->out_title_border() ?>
    
    <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='1'>
        <tr><td> <!----------- ダミー(デザイン用) ------------>
    <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr>
        <form name='ControlForm' action='<?php echo $menu->out_self(), "?id={$uniq}"?>' method='post'>
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
                <?php echo $pageControl?>
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
            <td class='winbox pt12b' align='right' nowrap><?php echo $r + 1 + $model->get_offset()?></td>
            <!-- 取消 -->
            <td class='winbox pt12b' align='center' nowrap>
                <a
                href='<?php echo $menu->out_self(), "?user_id={$userRes[$r][0]}&showMenu=apend&deleteUser=go&", $model->get_htmlGETparm(), "&id={$uniq}"?>'
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
            <td class='winbox pt12b' align='center' nowrap><?php echo $userRes[$r][0]?></td>
            <!-- 作業者 -->
            <td class='winbox pt12b' align='left' nowrap><?php echo $userRes[$r][1]?></td>
            <!-- 組立着手日時 -->
            <td class='winbox pt12b' align='center' nowrap><?php echo $userRes[$r][2]?></td>
            </tr>
        <?php } ?>
        </table>
            </td></tr> <!-- ダミー -->
        </table>
    <?php } ?>
    
    <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <caption>組立着手 計画番号 指示 入力</caption>
        <tr><td> <!-- ダミー -->
    <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <form name='start_form' action='<?php echo $menu->out_self(), "?id={$uniq}"?>' method='post' onSubmit='return AssemblyProcessTime.start_formCheck(this)'>
            <input type='hidden' name='showMenu' value='apend'>
        <tr>
            <td class='winbox pt12b' nowrap>
                計画番号
            </td>
            <td class='winbox' nowrap>
                <input type='text' name='plan_no' value='<?php echo $plan_no?>' size='10' maxlength='8'
                    style='ime-mode:disabled;' class='pt12b' onChange='this.value=this.value.toUpperCase()'
                >
                <input type='hidden' name='userEnd' value='dummy'>
                <input type='hidden' name='apendPlan' value='dummy'>
            </td>
            <td class='winbox' nowrap>
                <input type='submit' name='apendPlan' value='登録' class='pt12b'>
            </td>
        </tr>
        </form>
    </table>
        </td></tr> <!-- ダミー -->
    </table>
    
    <?php if ($planRows >= 1) { ?>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <caption>組立着手 指示 一覧</caption>
            <tr><td> <!-- ダミー -->
        <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <th class='winbox'>&nbsp;</th>
            <th class='winbox'>&nbsp;</th>
            <th class='winbox'>&nbsp;</th>
            <th class='winbox' nowrap>計画番号</th>
            <th class='winbox' nowrap>製品番号</th>
            <th class='winbox' nowrap>製　品　名</th>
            <th class='winbox' nowrap>計画残</th>
            <th class='winbox' nowrap>社員番号</th>
            <th class='winbox' nowrap>作業者</th>
            <th class='winbox' nowrap>組立着手</th>
        <?php for ($r=0; $r<$planRows; $r++) { ?>
            <tr>
            <!-- No. -->
            <td class='winbox pt12b' align='right' nowrap><?php echo $r + 1 + $model->get_offset()?></td>
            <!-- 取消 -->
            <td class='winbox pt12b' align='center' nowrap>
                <a
                href='<?php echo $menu->out_self(), "?serial_no={$planRes[$r][7]}&showMenu=apend&deletePlan=go&plan_no={$planRes[$r][0]}&userEnd=go&", $model->get_htmlGETparm(), "&id={$uniq}"?>'
                style='text-decoration:none;'
                onClick='return confirm("着手の取消をします宜しいですか？")'
                onMouseover="status='部品組立 着手の取消を行います。';return true;"
                onMouseout="status=''"
                title='部品組立 着手の取消を行います。'
                >
                    取消
                </a>
            </td>
            <!-- 組立完了 -->
            <td class='winbox pt12b' align='center' nowrap>
                <a
                href='<?php echo $menu->out_self(), "?serial_no={$planRes[$r][7]}&showMenu=apend&assyEnd=go&plan_no={$planRes[$r][0]}&userEnd=go&", $model->get_htmlGETparm(), "&id={$uniq}"?>'
                style='text-decoration:none;'
                onMouseover="status='部品組立の完了入力を行います。';return true;"
                onMouseout="status=''"
                title='部品組立の完了入力を行います。'
                >
                    完了
                </a>
            </td>
            <!-- 計画番号 -->
            <td class='winbox pt12b' align='right' nowrap><?php echo $planRes[$r][0]?></td>
            <!-- 製品番号 -->
            <td class='winbox pt12b' align='left' nowrap><?php echo $planRes[$r][1]?></td>
            <!-- 製品名 -->
            <td class='winbox pt12b' align='left' nowrap><?php echo $planRes[$r][2]?></td>
            <!-- 計画残 -->
            <td class='winbox pt12b' align='right' nowrap onDblClick='alert("計画残／計画数は\n\n<?php echo $planRes[$r][3]?>／<?php echo $planRes[$r][8]?>\n\nです。")'>
                <?php echo $planRes[$r][3]?>
            </td>
            <!-- 社員番号 -->
            <td class='winbox pt12b' align='center' nowrap><?php echo $planRes[$r][4]?></td>
            <!-- 作業者 -->
            <td class='winbox pt12b' align='left' nowrap><?php echo $planRes[$r][5]?></td>
            <!-- 組立着手日時 -->
            <td class='winbox pt12b' align='center' nowrap><?php echo $planRes[$r][6]?></td>
            </tr>
        <?php } ?>
        </table>
            </td></tr> <!-- ダミー -->
        </table>
        <table width='100%' border='0' cellspacing='0' cellpadding='10'>
            <form name='end_form' action='<?php echo $menu->out_self(), "?id={$uniq}"?>' method='post'>
            <tr align='center'>
                <td>
                    <input type='hidden' name='showMenu' value='apend'>
                    <input type='submit' name='apendEnd' value='入力終了' class='pt12b'>
                </td>
            </tr>
            </form>
        </table>
    <?php } ?>
</center>
</body>
<?php if ($_SESSION['s_sysmsg'] != '登録がありません！') { ?>
<?php echo $menu->out_alert_java()?>
<?php } ?>
</html>
