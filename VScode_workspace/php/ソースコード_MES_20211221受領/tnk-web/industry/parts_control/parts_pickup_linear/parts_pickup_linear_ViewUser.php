<?php
//////////////////////////////////////////////////////////////////////////////
// �����������ʽи� ��ꡦ��λ���� ������  MVC View ��                    //
//                                           �и�ô���� ��Ͽ���Խ�������ɽ  //
// Copyright (C) 2005-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/09/26 Created   parts_pickup_linear_ViewUser.php                    //
// 2005/09/30 set_focus()�᥽�åɤ�status Parameter �ɲ�                    //
// 2005/10/04 �и˺�ȼԤ���Ͽ�ơ��֥��ͭ����̵�����ɲ�  ȼ���᥽�å��ɲ�  //
// 2005/10/24 style='ime-mode:disabled;' ��ä�IME������ON���б��Τ����ɲ�  //
// 2005/11/12 onLoad=***.set_focus( ���Ĥ���̤ΰ��֤��ְ�äƤ���Τ���  //
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
<body onLoad='PartsPickupLinear.set_focus(document.user_form.<?php echo $focus?>, "noSelect")'>
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
        <caption>�и� ��ȼ� �� ��Ͽ</caption>
        <tr><td> <!-- ���ߡ� -->
    <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <form name='user_form' action='<?php echo $menu->out_self(), "?id={$uniq}"?>' method='post' onSubmit='return PartsPickupLinear.user_formCheck(this)'>
            <input type='hidden' name='current_menu' value='user'>
        <tr>
            <td class='winbox pt12b' nowrap>
                ��ȼԤμҰ��ֹ�
            </td>
            <td class='winbox' nowrap>
                <input type='text' name='user_id' value='<?php echo $user_id?>' size='10' maxlength='6'
                    style='ime-mode:disabled;' class='pt12b'
                    onChange='this.value=this.value.toUpperCase()'
                <?php echo $readonly?>
                >
            </td>
            <td class='winbox pt12b' nowrap>
                ��̾
            </td>
            <td class='winbox' nowrap>
                <input type='text' name='user_name' value='<?php echo $user_name?>' size='16' maxlength='8' class='pt12b'>
            </td>
            <td class='winbox' nowrap>
                <input type='submit' name='userEdit' value='��Ͽ' class='pt12b'>
            </td>
        </tr>
        </form>
    </table>
        </td></tr> <!-- ���ߡ� -->
    </table>
    <?php if ($rows >= 1) { ?>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <caption>�и� ��ȼ� ��Ͽ ����</caption>
            <tr><td> <!-- ���ߡ� -->
        <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <th class='winbox'>&nbsp;</th>
            <th class='winbox'>&nbsp;</th>
            <th class='winbox'>&nbsp;</th>
            <th class='winbox' nowrap>�Ұ��ֹ�</th>
            <th class='winbox' nowrap>�ᡡ̾</th>
            <th class='winbox' nowrap>��Ͽ����</th>
            <th class='winbox' nowrap>ͭ����̵��</th>
            <th class='winbox' nowrap>ͭ����̵��������</th>
        <?php for ($r=0; $r<$rows; $r++) { ?>
            <tr<?php if ($res[$r][3] == '̵��') echo " style='color:gray;'"?>>
            <!-- No. -->
            <td class='winbox' align='right' nowrap><?php echo $r + 1 + $model->get_offset()?></td>
            <!-- ��� -->
            <td class='winbox pt12b' align='center' nowrap>
                <a
                href='<?php echo $menu->out_self(), "?user_id={$res[$r][0]}&current_menu=user&userOmit=go&user_name=", urlencode($res[$r][1]), '&', $model->get_htmlGETparm(), "&id={$uniq}"?>'
                style='text-decoration:none;'
                onClick='return confirm("���ӥǡ��������٤�ʤ���к�����Ƥ����ꤢ��ޤ���\n\n������Ϻ������̵���ˤ��Ʋ�������\n\n������ޤ��������Ǥ�����")'
                >
                    ���
                </a>
            </td>
            <!-- �ѹ� -->
            <td class='winbox pt12b' align='center' nowrap>
                <a
                href='<?php echo $menu->out_self(), "?user_id={$res[$r][0]}&current_menu=user&userCopy=go&user_name=", urlencode($res[$r][1]), '&', $model->get_htmlGETparm(), "&id={$uniq}"?>'
                style='text-decoration:none;'
                >
                    �ѹ�
                </a>
            </td>
            <!-- �Ұ��ֹ� -->
            <td class='winbox pt12b' align='right' nowrap><?php echo $res[$r][0]?></td>
            <!-- ��̾ -->
            <td class='winbox pt12b' align='left' nowrap><?php echo $res[$r][1]?></td>
            <!-- ��Ͽ���� -->
            <td class='winbox pt12b' align='left' nowrap><?php echo $res[$r][2]?></td>
            <!-- ͭ����̵�� -->
            <td class='winbox pt12b' align='center' nowrap><?php echo $res[$r][3]?></td>
            <!-- ͭ����̵�������� -->
            <td class='winbox pt12b' align='center' nowrap>
                <a
                href='<?php echo $menu->out_self(), "?user_id={$res[$r][0]}&current_menu=user&userActive=go&user_name=", urlencode($res[$r][1]), '&', $model->get_htmlGETparm(), "&id={$uniq}"?>'
                style='text-decoration:none;'
                >
                    <?php if ($res[$r][3] == 'ͭ��') { ?>
                    ̵���ˤ���
                    <?php } else { ?>
                    ͭ���ˤ���
                    <?php } ?>
                </a>
            </td>
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
