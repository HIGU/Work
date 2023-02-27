<?php
////////////////////////////////////////////////////////////////////////////////
// ����ϡʾȲ��                                                             //
//                                                    MVC View �� �ꥹ��ɽ��  //
// Copyright (C) 2020-2020 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2020/11/18 Created sougou_query_ViewList.php                               //
// 2021/02/12 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////

// �����ǽ������򥻥å�
function SelectOptionBumon($model, $request)
{
    $bumonname = array("����ʤ� �ʤ��٤ơ�", "�������칩��", "�ɣӣϻ�̳��", "������", "������ (������)", "������ ��̳��", "������ ���ʴ�����", "������", "������ (������)", "������ �ʼ��ݾڲ�", "������ ���Ѳ�", "��¤��", "��¤�� (��¤��)", "��¤�� ��¤����", "��¤�� ��¤����", "������", "������ (������)", "������ ����������", "������ ���ץ���Ω��", "������ ��˥���Ω��");

    $max = count($bumonname);
    for( $i = 0; $i < $max ; $i++ ) {
        if( $model->IsDisp($i) ) {
            if( $request->get('ddlist_bumon') == $bumonname[$i] ) {
                echo "<option value='{$bumonname[$i]}' selected>{$bumonname[$i]}</option>";
            } else {
                echo "<option value='{$bumonname[$i]}'>{$bumonname[$i]}</option>";
            }
        }
    }
}

$menu->out_html_header();
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

<link rel='stylesheet' href='../per_appli.css' type='text/css' media='screen'>
<script type='text/javascript' language='JavaScript' src='sougou_query.js'></script>

</head>

<?php if($request->get('rep')!='rep' ) { ?>
<body onLoad='Init()'>
<?php } else { ?>
<body onLoad='Rep()'>
<?php } ?>

<center>
<?= $menu->out_title_border() ?>

    <br>
    <table class='pt10' border="1" cellspacing="0">
    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table width='100%' class='winbox_field' bgcolor='#D8D8D8' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                        <!--  bgcolor='#ffffc6' �������� --> 
                    <td class='winbox' style='background-color:yellow; color:blue;' colspan='2' align='center'>
                        <div class='caption_font'><?php echo $menu->out_caption(), "\n"?></div>
                    </td>
                </tr>

<form name='form_query' method='post' action='<?php echo $menu->out_self(),"?showMenu=Results" ?>' onSubmit='return InputAllCheck()'>
    <tr>
        <td align='center'>����� �������λ����YYYYMMDD��</td>
        <td align='center'>
            <input type="checkbox" name="c0" id="001" value="����" <?php if($request->get('c0')=='����') echo ' checked'; ?> onClick='OneDay(this)'><label for="001">����</label>
            <input type="text" size="10" maxlength="8" id="001-1" name="si_s_date" value='<?php echo $request->get('si_s_date') ?>' onkeyup="value=InputCheck(this);" onblur='checkDate(this)'>
            <font id='001-0'>��<font>
            <input type="text" size="10" maxlength="8" id="001-2" name="si_e_date" value='<?php echo $request->get('si_e_date') ?>' onkeyup="value=InputCheck(this);" onblur='checkDate(this)'>
        </td>
    </tr>

<!-- �������ͽ�� -->
    <?php if($model->IsMaster() || $model->IsBukatyou()) { ?>
    <tr>
        <td align='center'>���������</td>
        <td style='border:groove' align='center'>
            <select name="ddlist_bumon">
                <?php SelectOptionBumon($model, $request); ?>
            </select>
        </td>
    </tr>
    <?php } ?>
<!-- -->

    <?php if(getCheckAuthority(63)) { ?> <!-- 63:�Ұ��ֹ����ϲ�ǽ�ʹ���Ĺ������������̳�ݡ�-->
    <tr>
        <td align='center'>�ѡ��Ȱʳ� ���ϡ��ѡ��ȤΤ�</td>
        <td align='center'>
            <input type="radio" name="r5" id="501" value="����ʤ�" <?php if($request->get('r5')=='����ʤ�') echo ' checked'; ?> ><label for="501">����ʤ�</label>
            <input type="radio" name="r5" id="502" value="�ѡ��Ȱʳ�" <?php if($request->get('r5')=='�ѡ��Ȱʳ�') echo ' checked'; ?> ><label for="502">�ѡ��Ȱʳ�</label>
            <input type="radio" name="r5" id="503" value="�ѡ��ȤΤ�" <?php if($request->get('r5')=='�ѡ��ȤΤ�') echo ' checked'; ?> ><label for="503">�ѡ��ȤΤ�</label>
        </td>
    </tr>
    <?php } ?>

    <tr>
        <?php if(getCheckAuthority(63) || $model->IsBukatyou()) { ?> <!-- 63:�Ұ��ֹ����ϲ�ǽ�ʹ���Ĺ������������̳�ݡ�-->
            <td align='center'>�����ԡʼҰ�No.�ˤλ���</td>
            <td align='center'>
                �Ұ��ֹ桧<input type="text" size="8" maxlength="6" name="syainbangou" value='<?php echo $request->get('syainbangou') ?>' onkeyup="value=InputCheck(this);">
            </td>
        <?php } else { ?>
            <td align='center'>�����ԡʼҰ�No.��</td>
            <td align='center'>
                <input type='hidden' name='syainbangou' value='<?php echo $model->getUid(); ?>'>
                <p class='pt10'>�����¤��ʤ��١���������μҰ��ֹ���ꡣ</p>
                <?php echo '�Ұ��ֹ桧' . $model->getUid(); ?>
            </td>
        <?php } ?>
    </tr>

    <tr>
        <td align='center'>�о����λ����YYYYMMDD��</td>
        <td align='center'>
            <input type="checkbox" name="c1" id="101" value="����" <?php if($request->get('c1')=='����') echo ' checked'; ?> onClick='OneDay(this)'><label for="101">����</label>

            <input type="text" size="10" maxlength="8" id="101-1" name="str_date" value='<?php echo $request->get('str_date') ?>' onkeyup="value=InputCheck(this);" onblur='checkDate(this)'>
            <font id='101-0'>��<font>
            <input type="text" size="10" maxlength="8" id="101-2" name="end_date" value='<?php echo $request->get('end_date') ?>' onkeyup="value=InputCheck(this);" onblur='checkDate(this)'>
        </td>
    </tr>

    <?php if(getCheckAuthority(63)) { ?> <!-- 63:�Ұ��ֹ����ϲ�ǽ����̳�ݡ�-->
    <tr>
        <td align='center'><font style='color:red;'>�������å������ʲ��ξ���̵�뤵��ޤ���</font></td>
        <td align='center'>
            <input type="checkbox" name="c2" id="201" value="�Ժ߼ԥꥹ��" <?php if($request->get('c2')=='�Ժ߼ԥꥹ��') echo ' checked'; ?> onClick='huzaisya(this.checked)'><label for="201">�Ժ߼ԥꥹ��</label>
        </td>
    </tr>
    <?php } ?>

    <tr>
        <td align='center'>���Ƥ�����</td>
        <td style='border:groove' align='center'>
            <select name="ddlist">
                <option value="����ʤ�" <?php if($request->get('ddlist')=='����ʤ�') echo ' selected'; ?> >����ʤ�</option>
                <option value="ͭ��ٲ�" <?php if($request->get('ddlist')=='ͭ��ٲ�') echo ' selected'; ?> >ͭ��ٲ�</option>
                <option value="AMȾ��ͭ��ٲ�" <?php if($request->get('ddlist')=='AMȾ��ͭ��ٲ�') echo ' selected'; ?> >AMȾ��ͭ��ٲ�</option>
                <option value="PMȾ��ͭ��ٲ�" <?php if($request->get('ddlist')=='PMȾ��ͭ��ٲ�') echo ' selected'; ?> >PMȾ��ͭ��ٲ�</option>
                <option value="����ñ��ͭ��ٲ�" <?php if($request->get('ddlist')=='����ñ��ͭ��ٲ�') echo ' selected'; ?> >����ñ��ͭ��ٲ�</option>
                <option value="���" <?php if($request->get('ddlist')=='���') echo ' selected'; ?> >���</option>
                <option value="�ٹ�����" <?php if($request->get('ddlist')=='�ٹ�����') echo ' selected'; ?> >�ٹ�����</option>
                <option value="��ĥ���������" <?php if($request->get('ddlist')=='��ĥ���������') echo ' selected'; ?> >��ĥ���������</option>
                <option value="��ĥ�ʽ����" <?php if($request->get('ddlist')=='��ĥ�ʽ����') echo ' selected'; ?> >��ĥ�ʽ����</option>
                <option value="ľ��" <?php if($request->get('ddlist')=='ľ��') echo ' selected'; ?> >ľ��</option>
                <option value="ľ��" <?php if($request->get('ddlist')=='ľ��') echo ' selected'; ?> >ľ��</option>
                <option value="ľ��/ľ��" <?php if($request->get('ddlist')=='ľ��/ľ��') echo ' selected'; ?> >ľ��/ľ��</option>
                <option value="���̵ٲ�" <?php if($request->get('ddlist')=='���̵ٲ�') echo ' selected'; ?> >���̵ٲ�</option>
                <option value="���ص���" <?php if($request->get('ddlist')=='���ص���') echo ' selected'; ?> >���ص���</option>
                <option value="�����ٲ�" <?php if($request->get('ddlist')=='�����ٲ�') echo ' selected'; ?> >�����ٲ�</option>
                <option value="ID�������̤�˺��ʽжС�" <?php if($request->get('ddlist')=='ID�������̤�˺��ʽжС�') echo ' selected'; ?> >ID�������̤�˺��ʽжС�</option>
                <option value="ID�������̤�˺�����С�" <?php if($request->get('ddlist')=='ID�������̤�˺�����С�') echo ' selected'; ?> >ID�������̤�˺�����С�</option>
                <option value="���¾�ǧ˺��ʻĶȿ���ϳ���" <?php if($request->get('ddlist')=='���¾�ǧ˺��ʻĶȿ���ϳ���') echo ' selected'; ?> >���¾�ǧ˺��ʻĶȿ���ϳ���</option>
                <option value="ID�������̤�˺�����Сˡ� ���¾�ǧ˺��ʻĶȿ���ϳ���" <?php if($request->get('ddlist')=='ID�������̤�˺�����Сˡ� ���¾�ǧ˺��ʻĶȿ���ϳ���') echo ' selected'; ?> >ID�������̤�˺�����Сˡ� ���¾�ǧ˺��ʻĶȿ���ϳ���</option>
                <option value="����¾" <?php if($request->get('ddlist')=='����¾') echo ' selected'; ?> >����¾</option>
            </select>
        </td>
    </tr>

    <tr>
<?php
if( date('Ymd') > '20210630' ) {    // ����������ԲĤ��б�
        echo "<td align='center'>Suica ��̵ͭ</td>";
} else {
        echo "<td align='center'>�������̵ͭ</td>";
}
?>
        
        <td align='center' id='6000'>
            <input type="radio" name="r6" id="601" value="����ʤ�" <?php if($request->get('r6')=='����ʤ�') echo ' checked'; ?> ><label for="601">����ʤ�</label>
            <input type="radio" name="r6" id="602" value="t" <?php if($request->get('r6')=='t') echo ' checked'; ?> ><label for="602">����</label>
            <input type="radio" name="r6" id="603" value="f" <?php if($request->get('r6')=='f') echo ' checked'; ?> ><label for="603">�ʤ�</label>
        </td>
    </tr>

    <tr>
        <td align='center'>���żԤ�̵ͭ</td>
        <td align='center' id='7000'>
            <input type="radio" name="r7" id="701" value="����ʤ�" <?php if($request->get('r7')=='����ʤ�') echo ' checked'; ?> ><label for="701">����ʤ�</label>
            <input type="radio" name="r7" id="702" value="���ż�" <?php if($request->get('r7')=='���ż�') echo ' checked'; ?> ><label for="702">����</label>
            <input type="radio" name="r7" id="703" value="�ʤ�" <?php if($request->get('r7')=='�ʤ�') echo ' checked'; ?> ><label for="703">�ʤ�</label>
        </td>
    </tr>

    <tr>
        <td align='center'>��ޤ�̵ͭ</td>
        <td align='center' id='8000'>
            <input type="radio" name="r8" id="801" value="����ʤ�" <?php if($request->get('r8')=='����ʤ�') echo ' checked'; ?> ><label for="801">����ʤ�</label>
            <input type="radio" name="r8" id="802" value="���" <?php if($request->get('r8')=='���') echo ' checked'; ?> ><label for="802">���</label>
            <input type="radio" name="r8" id="803" value="�̾�" <?php if($request->get('r8')=='�̾�') echo ' checked'; ?> ><label for="803">�̾�</label>
        </td>
    </tr>

    <tr>
        <td align='center'>��ǧ����</td>
        <td align='center' id='9000'>
            <input type="radio" name="r9" id="901" value="����ʤ�" <?php if($request->get('r9')=='����ʤ�') echo ' checked'; ?> ><label for="901">����ʤ�</label>
            <input type="radio" name="r9" id="902" value="END" <?php if($request->get('r9')=='END') echo ' checked'; ?> ><label for="902">��λ</label>
            <input type="radio" name="r9" id="903" value="����" <?php if($request->get('r9')=='����') echo ' checked'; ?> ><label for="903">����</label>
            <input type="radio" name="r9" id="904" value="DENY" <?php if($request->get('r9')=='DENY') echo ' checked'; ?> ><label for="904">��ǧ</label>
            <input type="radio" name="r9" id="905" value="CANCEL" <?php if($request->get('r9')=='CANCEL') echo ' checked'; ?> ><label for="905">���</label>
        </td>
    </tr>

        </table>
    </td></tr>
    </table> <!----------------- ���ߡ�End --------------------->

    <p align='center'>
        ������������������������������������
        <input type="submit" value="�¹�" name="submit">&emsp;
        <input type="button" value="�ꥻ�å�" name="reset" onClick='location.replace("<?php echo $menu->out_self(), '?', $model->get_htmlGETparm() ?>");'>&emsp;
<!-- �Уģƥե�����򳫤�-->
        <font class='pt10' align='center'>
        ��<a href="download_file.php/����ϡʾȲ��.pdf">����ϡʾȲ��</a>�β���������
        </font>
<!-- -->
    </p>
</form>

<?php
if( $model->getUid() == '300667' ) {
echo "<div class='pt9'>�� ������βݤ���ư������硢<font style='color:red;'>�����̤�actID����</font>�ؿ���������ɬ�פ��ꡣ</div>";
}
?>

</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
