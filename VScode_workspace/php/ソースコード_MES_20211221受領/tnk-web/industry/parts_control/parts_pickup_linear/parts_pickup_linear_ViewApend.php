<?php
//////////////////////////////////////////////////////////////////////////////
// �����������ʽи� ��ꡦ��λ���� ������  MVC View ��                    //
//                                              �и���� �ؼ� ����(��Ͽ)    //
// Copyright (C) 2005-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/09/22 Created   parts_pickup_linear_ViewApend.php                   //
// 2005/09/30 set_focus()�᥽�åɤ�status Parameter �ɲ�                    //
// 2005/10/07 user_id�λ��꤬�������ControlForm��hidden��user_id�򥻥å� //
// 2005/10/24 style='ime-mode:disabled;' ��ä�IME������ON���б��Τ����ɲ�  //
// 2005/11/23 ControlFormSubmit()�᥽�å� ���Submit�к����ɲ�              //
// 2006/04/07 </label> ��ȴ���Ƥ������ս����                             //
// 2006/06/06 parts_pickup_time �� parts_pickup_linear ���ѹ�����˥��Ǻ��� //
//            ASP(JSP)�������ѻߤ��� php�ο侩�������ѹ�                    //
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
<link rel='stylesheet' href='parts_pickup_linear.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='parts_pickup_linear.js?<?php echo $uniq ?>'></script>
</head>
<body onLoad='PartsPickupLinear.set_focus(document.start_form.plan_no, "select")'>
<center>
<?php echo $menu->out_title_border() ?>
    
    <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
    <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr>
        <form name='ControlForm' action='<?php echo $menu->out_self(), "?id={$uniq}"?>' method='post'>
            <td nowrap <?php if($current_menu=='apend') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return PartsPickupLinear.ControlFormSubmit(document.ControlForm.elements["apend"], document.ControlForm);'
            >
                <input type='radio' name='current_menu' value='apend' id='apend'
                <?php if($current_menu=='apend') echo 'checked' ?>>
                <label for='apend'>�и��������</label>
            </td>
            <td nowrap <?php if($current_menu=='list') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return PartsPickupLinear.ControlFormSubmit(document.ControlForm.elements["list"], document.ControlForm);'
            >
                <input type='radio' name='current_menu' value='list' id='list'
                <?php if($current_menu=='list') echo 'checked' ?>>
                <label for='list'>�и�������</label>
            </td>
            <td nowrap <?php if($current_menu=='EndList') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return PartsPickupLinear.ControlFormSubmit(document.ControlForm.elements["EndList"], document.ControlForm);'
            >
                <input type='radio' name='current_menu' value='EndList' id='EndList'
                <?php if($current_menu=='EndList') echo 'checked' ?>>
                <label for='EndList'>�и˴�λ����</label>
            </td>
            <td nowrap class='winbox'>
                <?php echo $pageControl?>
                <?php if ($user_id != '') echo "<input type='hidden' name='user_id' value='$user_id'>\n"?>
            </td>
            <td nowrap <?php if($current_menu=='user') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return PartsPickupLinear.ControlFormSubmit(document.ControlForm.elements["user"], document.ControlForm);'
            >
                <input type='radio' name='current_menu' value='user' id='user'
                <?php if($current_menu=='user') echo 'checked' ?>>
                <label for='user'>��ȼ���Ͽ</label>
            </td>
        </form>
        </tr>
    </table>
        </td></tr>
    </table> <!----------------- ���ߡ�End ------------------>
    
    <div></div>
    
    <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <caption>�и���� ��ȼ� �ؼ�</caption>
        <tr><td> <!-- ���ߡ� -->
    <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='10'>
    <?php $tr = 0; ?>
    <?php for ($i=0; $i<$userRows; $i++) { ?>
        <?php if ($tr == 0) {?>
        <tr>
        <?php } ?>
            <td class='winbox' align='center' nowrap>
                <input type='button' name='user_name' value='<?php echo $userRes[$i][1]?>' class='pt12b'
                    onClick='location.replace("<?php echo $menu->out_self(), "?user_id={$userRes[$i][0]}&current_menu=apend&", $model->get_htmlGETparm(), "&id={$uniq}"?>")'
                    <?php if ($userRes[$i][0] == $user_id) echo 'style=color:red;';?>
                >
            </td>
            <?php $tr++ ?>
        <?php if ($tr >= 5) {?>
        </tr>
        <?php } ?>
        <?php if ($tr >= 5) $tr = 0;?>
    <?php } ?>
    <?php if ($tr != 0) echo "</tr>\n";?>
    </table>
        </td></tr> <!-- ���ߡ� -->
    </table>
    
    <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <caption>�и���� �ײ��ֹ� �ؼ� ����</caption>
        <tr><td> <!-- ���ߡ� -->
    <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <form name='start_form' action='<?php echo $menu->out_self(), "?id={$uniq}"?>' method='post' onSubmit='return PartsPickupLinear.start_formCheck(this)'>
            <input type='hidden' name='current_menu' value='apend'>
        <tr>
            <td class='winbox pt12b' nowrap>
                �ײ��ֹ�
            </td>
            <td class='winbox' nowrap>
                <input type='text' name='plan_no' value='<?php echo $plan_no?>' size='10' maxlength='8'
                    style='ime-mode:disabled;' class='pt12b' onChange='this.value=this.value.toUpperCase()'
                >
                <input type='hidden' name='user_id' value='<?php echo $user_id?>'>
                <input type='hidden' name='apend' value='�¹�'>
            </td>
            <td class='winbox' nowrap>
                <input type='submit' name='apend' value='��Ͽ' class='pt12b'>
            </td>
        </tr>
        </form>
    </table>
        </td></tr> <!-- ���ߡ� -->
    </table>
    
    <?php if ($rows >= 1) { ?>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <caption>�и���� �ؼ� ����</caption>
            <tr><td> <!-- ���ߡ� -->
        <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <th class='winbox'>&nbsp;</th>
            <th class='winbox'>&nbsp;</th>
            <th class='winbox'>&nbsp;</th>
            <th class='winbox' nowrap>�ײ��ֹ�</th>
            <th class='winbox' nowrap>�����ֹ�</th>
            <th class='winbox' nowrap>�����ʡ�̾</th>
            <th class='winbox' nowrap>�ײ��</th>
            <th class='winbox' nowrap>�Ұ��ֹ�</th>
            <th class='winbox' nowrap>��ȼ�</th>
            <th class='winbox' nowrap>�и����</th>
        <?php for ($r=0; $r<$rows; $r++) { ?>
            <tr>
            <!-- No. -->
            <td class='winbox pt12b' align='right' nowrap><?php echo $r + 1 + $model->get_offset()?></td>
            <!-- ��� -->
            <td class='winbox pt12b' align='center' nowrap>
                <a
                href='<?php echo $menu->out_self(), "?serial_no={$res[$r][7]}&current_menu=apend&delete=go&plan_no={$res[$r][0]}&user_id={$res[$r][4]}&", $model->get_htmlGETparm(), "&id={$uniq}"?>'
                style='text-decoration:none;'
                onClick='return confirm("���μ�ä򤷤ޤ��������Ǥ�����")'
                onMouseover="status='���ʽи� ���μ�ä�Ԥ��ޤ���';return true;"
                onMouseout="status=''"
                title='���ʽи� ���μ�ä�Ԥ��ޤ���'
                >
                    ���
                </a>
            </td>
            <!-- �и˴�λ -->
            <td class='winbox pt12b' align='center' nowrap>
                <a
                href='<?php echo $menu->out_self(), "?serial_no={$res[$r][7]}&current_menu=apend&editEnd=go&user_id={$res[$r][4]}&plan_no={$res[$r][0]}&", $model->get_htmlGETparm(), "&id={$uniq}"?>'
                style='text-decoration:none;'
                onMouseover="status='���ʽиˤδ�λ���Ϥ�Ԥ��ޤ���';return true;"
                onMouseout="status=''"
                title='���ʽиˤδ�λ���Ϥ�Ԥ��ޤ���'
                >
                    ��λ
                </a>
            </td>
            <!-- �ײ��ֹ� -->
            <td class='winbox pt12b' align='right' nowrap><?php echo $res[$r][0]?></td>
            <!-- �����ֹ� -->
            <td class='winbox pt12b' align='left' nowrap><?php echo $res[$r][1]?></td>
            <!-- ����̾ -->
            <td class='winbox pt12b' align='left' nowrap><?php echo $res[$r][2]?></td>
            <!-- �ײ�� -->
            <td class='winbox pt12b' align='right' nowrap><?php echo $res[$r][3]?></td>
            <!-- �Ұ��ֹ� -->
            <td class='winbox pt12b' align='center' nowrap><?php echo $res[$r][4]?></td>
            <!-- ��ȼ� -->
            <td class='winbox pt12b' align='left' nowrap><?php echo $res[$r][5]?></td>
            <!-- �и�������� -->
            <td class='winbox pt12b' align='center' nowrap><?php echo $res[$r][6]?></td>
            </tr>
        <?php } ?>
        </table>
            </td></tr> <!-- ���ߡ� -->
        </table>
    <?php } ?>
</center>
</body>
<?php if ($_SESSION['s_sysmsg'] != '��Ͽ������ޤ���') { ?>
<?php echo $menu->out_alert_java()?>
<?php } ?>
</html>
