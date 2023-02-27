<?php
//////////////////////////////////////////////////////////////////////////////
// ���ץ�����δ����ʸ������ӽ� ����  �ײ��ֹ�ΥС����������ϥե�����      //
// �ƥ�ץ졼�ȥ��󥸥��simplate, ���饤����Ȱ�����PXDoc �����           //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/12/06 Created  inputForm.php                                        //
// 2007/12/07 ����̾�������ֹ����γ�ǧ���̤���Ϥ���褦���ɲ�              //
// 2007/12/10 resetForm()���ɲä���submit()��˥ե�����������֤ˤ���     //
// 2007/12/28 ���κ���ȥ����������Ǥ���褦�˵�ǽ�ɲá������ȼ����ǧ  //
//            �ܥ�����ɲä�submit������Ǥ���褦���ѹ�������������ɲ�    //
// 2007/12/29 �������¸�˷ײ��ֹ���ɲ� $result->get('prePlanNo')          //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<?php echo $menu->out_jsBaseClass() ?>

<!-- JavaScript�Υե���������body�κǸ�ˤ��롣 HTML�����Υ����Ȥ�����Ҥ˽���ʤ��������  -->
<script type='text/javascript' src='/pxd/checkPXD.js?<?php echo $uniq ?>'></script>

<!-- �������륷���ȤΥե��������򥳥��� HTML�����Υ����Ȥ�����Ҥ˽���ʤ��������  -->
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
        alert('SVG(�������顼�֥롦�٥�����������ե��å���)�ե����뤬���ꤵ��Ƥ��ޤ���');
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
        <!--------------- ����������ʸ��ɽ��ɽ������ -------------------->
        <table width='60%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <form name='inputForm' method='post' action='<?php echo $menu->out_self() ?>'>
            <tr>
                <th class='winbox' nowrap width='20%'>No.</th>
                <th class='winbox' nowrap width='40%'>�ࡡ��</th>
                <th class='winbox' nowrap width='40%'>������</th>
            </tr>
            <tr>
                <td class='winbox' nowrap width='20%' align='right'>1</td>
                <td class='winbox' nowrap width='40%' align='center'>�ײ��ֹ�</td>
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
                <td class='winbox' nowrap width='40%' align='center'>�����ֹ�</td>
                <td class='winbox' nowrap width='40%' align='center'><?php echo $result->get('assyNo')?></td>
            </tr>
            <tr>
                <td class='winbox' nowrap width='20%' align='right'>3</td>
                <td class='winbox' nowrap width='40%' align='center'>�� �� ̾</td>
                <td class='winbox' nowrap width='40%' align='center'><?php echo $result->get('assyName')?></td>
            </tr>
            <tr>
                <td class='winbox' nowrap width='20%' align='right'>4</td>
                <td class='winbox' nowrap width='40%' align='center'>�� �� ��</td>
                <td class='winbox' nowrap width='40%' align='center'><?php echo $result->get('plan')?></td>
            </tr>
            <tr>
                <td class='winbox' nowrap width='20%' align='right'>5</td>
                <td class='winbox' nowrap width='40%' align='center'>���κ��</td>
                <td class='winbox' nowrap width='40%' align='center'>
                    <input type='text' name='targetMaterial' style='font-size:1.0em; font-weight:bold;' value='<?php echo $result->get('material')?>' size='11' maxlength='10' onKeyUp='baseJS.keyInUpper(this);'>
                </td>
            </tr>
            <tr>
                <td class='winbox' nowrap width='20%' align='right'>6</td>
                <td class='winbox' nowrap width='40%' align='center'>������</td>
                <td class='winbox' nowrap width='40%' align='center'>
                    <input type='text' name='targetMaterial2' style='font-size:1.0em; font-weight:bold;' value='<?php echo $result->get('material2')?>' size='11' maxlength='10' onKeyUp='baseJS.keyInUpper(this);'>
                </td>
            </tr>
            <tr>
                <td class='winbox' nowrap width='20%' align='right'>7</td>
                <td class='winbox' nowrap width='40%' align='center'>�����ֹ�</td>
                <td class='winbox' nowrap width='40%' align='center'><?php echo $result->get('scNo')?></td>
            </tr>
            <tr>
                <td class='winbox' nowrap width='20%' align='right'>8</td>
                <td class='winbox' nowrap width='40%' align='center'>���ν�No</td>
                <td class='winbox' nowrap width='40%' align='center'><?php echo $result->get('cdNo')?></td>
            </tr>
            <tr>
                <td class='winbox' nowrap width='20%' align='right'>9</td>
                <td class='winbox' nowrap width='40%' align='center'>�桼����</td>
                <td class='winbox' nowrap width='40%' align='center'><?php echo $result->get('userName')?></td>
            </tr>
            <tr style='color:blue;'>
                <td class='winbox' nowrap width='20%' align='right'>10</td>
                <td class='winbox' nowrap width='40%' align='center'>�������</td>
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
                    <input type='button' name='preView' style='width:110px;' value='�����ץ�ӥ塼' onClick='formSubmit(1);'>
                    &nbsp;
                    <input type='button' name='execPrint' value='����' onClick='formSubmit(2);'>
                    &nbsp;
                    <input type='submit' name='Rturn' value='���' onClick='document.inputForm.targetPlanNo.value=""; location.replace("<?php echo $menu->out_self()?>")'>
                    <?php } else { ?>
                    <input type='submit' name='Confirm' value='��ǧ'>
                    <?php }?>
                </td>
            </tr>
            </form>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
    </center>
</body>
<?php echo $menu->out_alert_java(false)?>
</html>
