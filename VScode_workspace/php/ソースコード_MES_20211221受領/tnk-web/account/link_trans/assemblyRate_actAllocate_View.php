<?php
//////////////////////////////////////////////////////////////////////////////
// ����������Ψ �Ȳ� View�� assemblyRate_actAllocate_View.php               //
// Copyright (C) 2007-2011 Norihisa.Ooya usoumu@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2007/12/06 Created  assemblyRate_actAllocate_View.php                    //
// 2007/12/29 ����ͤΥե���������軻�����ν�λǯ����ѹ�                  //
// 2011/06/22 format_date�Ϥ�tnk_func�˰�ư�Τ��ᤳ�������               //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<script type='text/javascript' src='assemblyRate_actAllocate.js'></script>
<link rel='stylesheet' href='assemblyRate_actAllocate.css' type='text/css' media='screen'>
</head>
<body onLoad='document.kessan_form.end_ym.focus()' scroll=no>
    <center>
    <?php echo $menu->out_title_border()?>    <!-- �������ɽ�� -->
        <table bgcolor='#d6d3ce' cellspacing='0' cellpadding='3' border='1'>
            <form name='ini_form' action='<?php echo $menu->out_self() ?>' method='post' onSubmit='return ym_chk_tangetu(this)'>
                <tr>    
                    <td colspan='2' align='right' valign='middle' class='pt11' nowrap>
                        �о�ǯ����ϰϡˤ���ꤷ�Ʋ��������㡧200604 (2006ǯ04��)
                        <input type='text' name='tan_str_ym' size='7' value='<?php echo $request->get('tan_str_ym') ?>' maxlength='6'>
                        ��
                        <input type='text' name='tan_end_ym' size='7' value='<?php echo $request->get('tan_end_ym') ?>' maxlength='6'>
                    </td>
                    <td align='center'>
                        <input class='pt11b' type='submit' name='tangetu' value='��ͳ�׻�'>
                    </td>
                    <td align='center'>
                        <input type="button" name="print" value="����" onclick="framePrint()">
                    </td>
                </tr>
            </form>
            <form name='kessan_form' action='<?php echo $menu->out_self() ?>' method='post' onSubmit='return ym_chk_kessan(this)'>
                <tr>
                    <td align='left' class='pt11' nowrap>
                        �о�ǯ�����ꤷ�Ʋ�������
                        <input type='text' name='str_ym' size='7' value='<?php echo $request->get('str_ym') ?>' readonly class='readonly'>
                        ��
                        <input type='text' name='end_ym' id='end_ym' size='7' value='<?php echo $request->get('end_ym') ?>' maxlength='6' onkeyup='start_ym()'>
                    </td>
                    <td align='center'>
                        <input class='pt11b' type='submit' name='kessan' value='�軻����'>
                    </td>
                    <?php
                    if ($request->get('tangetu') != '') {    // ñ��ξ��Ͼ�˾Ȳ�
                    ?>    
                        <td align='center' class='pt11bb' nowrap>
                        �Ȳ�
                        </td>
                    <?php
                    } else if ($request->get('kessan') != '') {    // �軻����Ψ����Ͽ����Ƥ�����ϾȲ�
                        if($request->get('input') != '') {
                            $rate_register = "�Ȳ�";
                            $request->add('rate_register', $rate_register);
                        }
                        if ($request->get('rate_register') == "�Ȳ�") {
                            if (getCheckAuthority(22)) {    //ǧ�ڥ����å�
                    ?>    
                                <td align='center' class='pt11bb' nowrap>
                                �Ȳ�
                                </td>
                                <td align='center' class='pt11bb' nowrap>
                                    <input class='pt11b' type='submit' name='delete' value='������'>
                                </td>
                            <?php
                            }
                        } else if ($request->get('rate_register') == "��Ͽ") {    // �軻����Ψ����Ͽ����Ƥ��ʤ�������Ͽ��ǧ����
                            if (getCheckAuthority(22)) {    //ǧ�ڥ����å�
                        ?>
                                <td align='center' class='pt11br' nowrap>
                                ��ǧ
                                </td>
                                <td align='center' class='pt11bb' nowrap>
                                    <input class='pt11b' type='submit' name='input' value='��Ͽ'>
                                    <input type='hidden' name='kessan' value='kessan'>
                                    <input type='hidden' name='c_indirect_cost' value='<?php echo $result->get('c_indirect_cost') ?>'>
                                    <input type='hidden' name='c_suppli_section_cost' value='<?php echo $result->get('c_suppli_section_cost') ?>'>
                                    <input type='hidden' name='l_indirect_cost' value='<?php echo $result->get('l_indirect_cost') ?>'>
                                    <input type='hidden' name='l_suppli_section_cost' value='<?php echo $result->get('l_suppli_section_cost') ?>'>
                                </td>
                        <?php
                            }
                        }
                    }
                    ?>
                </tr>
            </form>
        </table>
    </center>
    <br>
    <?php
    if ($request->get('view_flg') == '�Ȳ�') {
        echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/assemblyRate_actAllocate_List-{$_SESSION['User_ID']}.html' name='list' align='center' width='100%' height='80%' title='�ꥹ��'>\n";
        echo "    ������ɽ�����Ƥ��ޤ���\n";
        echo "</iframe>\n";
    }
    ?>
</body>
<?php echo $menu->out_alert_java() ?>
</html>

