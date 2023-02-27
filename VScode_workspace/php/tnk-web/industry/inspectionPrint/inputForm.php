<?php
//////////////////////////////////////////////////////////////////////////////
// カプラ特注の完成品検査成績書 印刷  計画番号のバーコード入力フォーム      //
// テンプレートエンジンはsimplate, クライアント印刷はPXDoc を使用           //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/12/06 Created  inputForm.php                                        //
// 2007/12/07 製品名・製品番号等の確認画面を出力するように追加              //
// 2007/12/10 resetForm()を追加し、submit()後にフォームを初期状態にする     //
// 2007/12/28 本体材質とゴム材質を修正できるように機能追加。それに伴い確認  //
//            ボタンを追加しsubmitを制御できるように変更。前回印刷も追加    //
// 2007/12/29 履歴に保存に計画番号も追加 $result->get('prePlanNo')          //
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
<?php echo $menu->out_jsBaseClass() ?>

<!-- JavaScriptのファイル指定をbodyの最後にする。 HTMLタグのコメントは入れ子に出来ない事に注意  -->
<script type='text/javascript' src='/pxd/checkPXD.js?<?php echo $uniq ?>'></script>

<!-- スタイルシートのファイル指定をコメント HTMLタグのコメントは入れ子に出来ない事に注意  -->
<link rel='stylesheet' href='inspectionPrint.css?<?php echo $uniq ?>' type='text/css' media='screen'>

<link rel='shortcut icon' href='/favicon.ico?=<?php echo $uniq ?>'>

<style type='text/css'><!-- --></style>
</head>

<script type='text/javascript'>
function formSubmit(flg)
{
    if (flg == 1) {
        inputForm.showMenu.value = "preView";
    } else if (flg == 2) {
        inputForm.showMenu.value = "execPrint";
    }
    inputForm.submit();
    resetForm();
}
function resetForm()
{
    setTimeout("inputForm.showMenu.value = '';", 500);
    // document.inputForm.targetPlanNo.focus();
    // document.inputForm.targetPlanNo.select();
}
function checkTemplateFile(obj)
{
    if (obj.svgFile.value) {
        return true;
    } else {
        alert('SVG(スケーラーブル・ベクター・グラフィックス)ファイルが指定されていません！');
        return false;
    }
}
</script>
<body style='overflow-y:hidden;'
    onLoad='
        <?php if ($result->get('assyNo') != '') {?>
        document.inputForm.targetMaterial.focus();
        // document.inputForm.targetMaterial.select();
        <?php } else { ?>
        document.inputForm.targetPlanNo.focus();
        document.inputForm.targetPlanNo.select();
        <?php }?>
    '
>
    <center>
<?php echo $menu->out_title_border() ?>
        <br>
        <!--------------- ここから本文の表を表示する -------------------->
        <table width='60%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <form name='inputForm' method='post' action='<?php echo $menu->out_self() ?>'>
            <tr>
                <th class='winbox' nowrap width='20%'>No.</th>
                <th class='winbox' nowrap width='40%'>項　目</th>
                <th class='winbox' nowrap width='40%'>入力欄</th>
            </tr>
            <tr>
                <td class='winbox' nowrap width='20%' align='right'>1</td>
                <td class='winbox' nowrap width='40%' align='center'>計画番号</td>
                <td class='winbox' nowrap width='40%' align='center' style='font-size:1.0em; font-weight:bold;'>
                    <?php if ($result->get('assyNo') != '') {?>
                    <input type='hidden' name='targetPlanNo' value='<?php echo $request->get('targetPlanNo')?>'>
                    <?php echo $request->get('targetPlanNo')?>
                    <?php } else { ?>
                    <input type='text' name='targetPlanNo' style='font-size:1.0em; font-weight:bold;' value='<?php echo $request->get('targetPlanNo')?>' size='9' maxlength='8' onKeyUp='baseJS.keyInUpper(this);'>
                    <?php }?>
                </td>
            </tr>
            <?php if ($result->get('assyNo') != '') {?>
            <tr>
                <td class='winbox' nowrap width='20%' align='right'>2</td>
                <td class='winbox' nowrap width='40%' align='center'>製品番号</td>
                <td class='winbox' nowrap width='40%' align='center'><?php echo $result->get('assyNo')?></td>
            </tr>
            <tr>
                <td class='winbox' nowrap width='20%' align='right'>3</td>
                <td class='winbox' nowrap width='40%' align='center'>製 品 名</td>
                <td class='winbox' nowrap width='40%' align='center'><?php echo $result->get('assyName')?></td>
            </tr>
            <tr>
                <td class='winbox' nowrap width='20%' align='right'>4</td>
                <td class='winbox' nowrap width='40%' align='center'>計 画 数</td>
                <td class='winbox' nowrap width='40%' align='center'><?php echo $result->get('plan')?></td>
            </tr>
            <tr>
                <td class='winbox' nowrap width='20%' align='right'>5</td>
                <td class='winbox' nowrap width='40%' align='center'>本体材質</td>
                <td class='winbox' nowrap width='40%' align='center'>
                    <input type='text' name='targetMaterial' style='font-size:1.0em; font-weight:bold;' value='<?php echo $result->get('material')?>' size='11' maxlength='10' onKeyUp='baseJS.keyInUpper(this);'>
                </td>
            </tr>
            <tr>
                <td class='winbox' nowrap width='20%' align='right'>6</td>
                <td class='winbox' nowrap width='40%' align='center'>ゴム材質</td>
                <td class='winbox' nowrap width='40%' align='center'>
                    <input type='text' name='targetMaterial2' style='font-size:1.0em; font-weight:bold;' value='<?php echo $result->get('material2')?>' size='11' maxlength='10' onKeyUp='baseJS.keyInUpper(this);'>
                </td>
            </tr>
            <tr>
                <td class='winbox' nowrap width='20%' align='right'>7</td>
                <td class='winbox' nowrap width='40%' align='center'>工事番号</td>
                <td class='winbox' nowrap width='40%' align='center'><?php echo $result->get('scNo')?></td>
            </tr>
            <tr>
                <td class='winbox' nowrap width='20%' align='right'>8</td>
                <td class='winbox' nowrap width='40%' align='center'>要領書No</td>
                <td class='winbox' nowrap width='40%' align='center'><?php echo $result->get('cdNo')?></td>
            </tr>
            <tr>
                <td class='winbox' nowrap width='20%' align='right'>9</td>
                <td class='winbox' nowrap width='40%' align='center'>ユーザー</td>
                <td class='winbox' nowrap width='40%' align='center'><?php echo $result->get('userName')?></td>
            </tr>
            <tr style='color:blue;'>
                <td class='winbox' nowrap width='20%' align='right'>10</td>
                <td class='winbox' nowrap width='40%' align='center'>前回印刷</td>
                <td class='winbox' nowrap width='40%' align='center'>
                    <?php echo $result->get('prePrintDate')?>
                    <br>
                    <?php echo $result->get('prePlanNo') ?>
                </td>
            </tr>
            <?php }?>
            <tr>
                <td class='winbox' nowrap colspan='3' align='center'>
                    <?php if ($result->get('assyNo') != '') {?>
                    <input type='hidden' name='showMenu' value=''>
                    <!-- <input type='hidden' name='DEBUG' value='yes'> -->
                    <input type='button' name='preView' style='width:110px;' value='印刷プレビュー' onClick='formSubmit(1);'>
                    &nbsp;
                    <input type='button' name='execPrint' value='印刷' onClick='formSubmit(2);'>
                    &nbsp;
                    <input type='submit' name='Rturn' value='戻る' onClick='document.inputForm.targetPlanNo.value=""; location.replace("<?php echo $menu->out_self()?>")'>
                    <?php } else { ?>
                    <input type='submit' name='Confirm' value='確認'>
                    <?php }?>
                </td>
            </tr>
            </form>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
    </center>
</body>
<?php echo $menu->out_alert_java(false)?>
</html>
