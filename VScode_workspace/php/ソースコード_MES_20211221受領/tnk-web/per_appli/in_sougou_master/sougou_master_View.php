<?php
////////////////////////////////////////////////////////////////////////////////
// ����ϡʥޥ�������View��                                                   //
// Copyright (C) 2020-2020 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2020/11/18 Created sougou_master_View.php                                  //
// 2021/02/12 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////
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

<script language="JavaScript">
<!--
/* ����ʸ�����������ɤ��������å�(ASCII code check) */
function isDigit(str) {
    var len = str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if ((c < '0') || (c > '9')) {
            return false;
        }
    }
    return true;
}

/* ����ʸ���Υ����å��ؿ� */
function chk_entry(obj) {
    if( obj.act_id ) {
        if (obj.act_id.value.length == 0 || !isDigit(obj.act_id.value) ) {
            alert('���������ɤ��ǧ���Ʋ�������');
            obj.act_id.focus();
            obj.act_id.select();
            return false;
        }
    }

    if( obj.standards_date ) {
        var str = obj.standards_date.value.replace(/[^0-9]+/i,'');
        str = str.replace(/[^0-9]+/i,'');
        if (str.length == 0 || str.length != 8) {
            alert('��������ǧ���Ʋ�������');
            obj.standards_date.focus();
            obj.standards_date.select();
            return false;
        }

        if(str.substr(6, 2) < 1) str = str.substr(0, 6) + '01';
        if (!isDate(str)) {
            var dt = new Date(str.substr(0, 4),  str.substr(4, 2), 0);
            str = ( str.substr(0, 6) + dt.getDate() );
            if (!isDate(str)) {
                alert('������λ��꤬�����Ǥ���');
                obj.standards_date.focus();
                obj.standards_date.select();
                return false;
            }

        }
    }

    return true;
}

// ���Ϥ���������򸫤䤹���񼰤��ѹ�
function checkDate(obj) {
    var str = obj.value;
    if( !str ) return '';

    str = str.replace(/[^0-9]+/i,'');
    str = str.replace(/[^0-9]+/i,'');


    if(str.substr(6, 2) < 1) str = str.substr(0, 6) + '01';
    if (!isDate(str)) {
        var dt = new Date(str.substr(0, 4),  str.substr(4, 2), 0);
        str = ( str.substr(0, 6) + dt.getDate() );
    }
    str = str.substr(0, 4) + '-' + str.substr(4, 2) + '-' + str.substr(6, 2);
    return str;
}

// ¸�ߤ������դ������å�
function isDate( str ) {
    var arr = (str.substr(0, 4) + '/' + str.substr(4, 2) + '/' + str.substr(6, 2)).split('/');

    if (arr.length !== 3) return false;
    var date = new Date(arr[0], arr[1] - 1, arr[2]);
    if (arr[0] !== String(date.getFullYear()) || arr[1] !== ('0' + (date.getMonth() + 1)).slice(-2) || arr[2] !== ('0' + date.getDate()).slice(-2)) {
        return false;
    } else {
        return true;
    }
}

// -->
</script>

<style type="text/css">
<!--
th {
    background-color:   blue;
    color:              yellow;
    font-size:          10pt;
    font-weight:        bold;
    font-family:        monospace;
}
-->
</style>
</head>
<body scroll=no onload='document.entry_form.act_id.focus();'>
    <center>
    <?php echo $menu->out_title_border() ?>

    <br>
    <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
    <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr>
        <form name='ControlForm' action='<%=$menu->out_self(), "?id={$uniq}"%>' method='post'>
            <?php $showMenu=$request->get('showMenu'); ?>
            <td class='winbox' align='center' nowrap>
                <input type='radio' name='showMenu' value='1' id='befor' onClick='submit()' <?php if($showMenu=='1') echo 'checked' ?>><label for='befor'>��ǧ ��ϩ ��Ͽ</label>
            </td>
            <td class='winbox' nowrap>
                <input type='radio' name='showMenu' value='2' id='after' onClick='submit()' <?php if($showMenu=='2') echo 'checked' ?>><label for='after'>��̳��Ĺ������Ĺ ��Ͽ</label>
            </td>
        </form>
        </tr>
    </table>
        </td></tr>
    </table> <!----------------- ���ߡ�End ------------------>

        <form name='entry_form' action=<?php echo 'sougou_master_Main.php' . '?showMenu=' . $showMenu ?> method='post' onSubmit='return chk_entry(this)'>
            <table bgcolor='#d6d3ce' width='300' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
            <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <THEAD>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <td width='700' bgcolor='#ffffc6' align='center' colspan='15'>
                    <?php
                    if($showMenu=='1') {
                        echo "��ǧ ��ϩ ��Ͽ";
                    } else {
                        echo "��̳��Ĺ������Ĺ ��Ͽ";
                    }
                    ?>
                    </td>
                </tr>
                <tr>
                    <?php
                    $field_g = $result->get_array2('field_g');
                    for ($i=0; $i<$result->get('num_g'); $i++) {   // �ե�����ɿ�ʬ���֤�
                        echo "<th class='winbox' nowrap>$field_g[$i]</th>";
                    }
                    ?>
                    <th class='winbox' colspan='12' nowrap>�ɲá��ѹ�</th>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </TFOOT>
            <TBODY>
                <tr>
                    <?php
                    if($showMenu=='1') {
                    ?>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='act_id' value='<?php echo $field_g = $request->get('act_id') ?>' size='11' maxlength='3'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='kakarityo' value='<?php echo $field_g = $request->get('kakarityo') ?>' size='8' maxlength='6'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='katyo' value='<?php echo $field_g = $request->get('katyo') ?>' size='8' maxlength='6'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='butyo' value='<?php echo $field_g = $request->get('butyo') ?>' size='8' maxlength='6'></td>
                    <td class='winbox' align='center'><input type='checkbox' name='somukatyo' value='on' <?php if($request->get('somukatyo')=='on') echo ' checked'; ?>></td>
                    <td class='winbox' align='center'><input type='checkbox' name='kanributyo' value='on' <?php if($request->get('kanributyo')=='on') echo ' checked'; ?>></td>
                    <td class='winbox' align='center'><input type='checkbox' name='kojyotyo' value='on' <?php if($request->get('kojyotyo')=='on') echo ' checked'; ?>></td>
                    <?php
                    } else {
                    ?>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='standards_date' value='<?php echo $field_g = $request->get('standards_date') ?>' size='12' maxlength='10' OnChange='value = checkDate(this)'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='somukatyo' value='<?php echo $field_g = $request->get('somukatyo') ?>' size='8' maxlength='6'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='kanributyo' value='<?php echo $field_g = $request->get('kanributyo') ?>' size='8' maxlength='6'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='kojyotyo' value='<?php echo $field_g = $request->get('kojyotyo') ?>' size='8' maxlength='6'></td>
                    <?php
                    }
                    ?>
                    <td class='winbox' colspan='6' align='center'>
                        <input type='submit' class='entry_font' name='entry' value='�ɲ�'>
                        <input type='hidden' class='entry_font' name='wage_ym' value='<?php echo $request->get('wage_ym') ?>'>
                    </td>
                    <td class='winbox' colspan='6' align='center'>
                        <input type='submit' class='entry_font' name='del' value='���'>
                        <input type='hidden' class='entry_font' name='wage_ym' value='<?php echo $request->get('wage_ym') ?>'>
                    </td>
                </tr>
            </TBODY>
            </table>
                </td></tr>
            </table> <!----------------- ���ߡ�End ------------------>
        </form>

    </center>
    <br>
    <?php
    if ($request->get('view_flg') == '�Ȳ�') {
        if($showMenu=='1') {
            echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/sougou_master_List-{$_SESSION['User_ID']}.html' name='list' align='center' width='100%' height='70%' title='�ꥹ��'>\n";
        } else {
            echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/sougou_master_List2-{$_SESSION['User_ID']}.html' name='list' align='center' width='100%' height='70%' title='�ꥹ��'>\n";
        }
        echo "    ������ɽ�����Ƥ��ޤ���\n";
        echo "</iframe>\n";
    }
    ?>
</body>
<?php echo $menu->out_alert_java() ?>
</html>
