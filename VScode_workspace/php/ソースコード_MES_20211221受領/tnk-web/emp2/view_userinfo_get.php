<?php
//////////////////////////////////////////////////////////////////////////////
// �Ұ���������� include file ���Ȱ�������Ͽ�ե�����                       //
// Copyright (C) 2001-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// �ѹ�����                                                                 //
// 2001/07/07 Created   view_userinfo_get.php                               //
// 2002/08/07 register_globals = Off �б�                                   //
// 2002/08/19 �������������б� �����ǡ�����Ĥ�                           //
// 2005/01/17 access_log ���ѹ��� view_file_name(__FILE__) ���ɲ�           //
//////////////////////////////////////////////////////////////////////////////
// access_log("view_userinfo_get.php");        // Script Name ��ư����
access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));
echo view_file_name(__FILE__);
if(file_exists(INS_IMG . $_SESSION["User_ID"])){
    if(!isset($_POST['img_file']))
        unlink(INS_IMG . $_SESSION["User_ID"]);
}
?>

<form enctype="multipart/form-data" method="post" action="emp_menu.php?func=<?php echo(FUNC_CONFNEWUSER) ?>" onSubmit="return chkUserInfo(this)">
    <input type='hidden' name='func' value='<?php echo(FUNC_CONFNEWUSER) ?>'>
<table width="100%">
    <tr><td colspan=2 bgcolor="#003e7c" align="center">
        <font color="#ffffff">���Ȱ�(�桼����)�ο�����Ͽ</font></td></tr>

    <tr><td width="15%">����</td>
        <td><select name="authority">
<?php
    $query="select * from authority_master order by aid asc";
    $res=array();
    if($rows=getResult($query,$res)){
        for($i=0;$i<$rows;$i++){
            if($res[$i]['aid'] == $_POST['authority'])
                echo("<option value=" . $res[$i]['aid'] . " selected>" . $res[$i]['authority_name'] . "</option>\n");
            else
                echo("<option value=" . $res[$i]['aid'] . ">" . $res[$i]['authority_name'] . "</option>\n");
        }
    }
?>
        </select></td></tr>

    <tr><td width="15%">�Ұ�No.*</td>
        <td><input type="text" name="userid" value='<?php echo($_POST['userid']) ?>' size=12 maxlength=6></td>
    </tr>
    <tr>
        <td width="15%">��̾*</td>
    </tr>
    <tr>
        <td align="right">��</td><td><input type="text" name="name_1" value='<?php echo($_POST['name_1']) ?>' size=24 maxlength=8>̾<input type="text" name="name_2" value='<?php echo($_POST['name_2']) ?>' size=24 maxlength=8></td>
    </tr>
    <tr>
        <td width="15%">�եꥬ��*</td>
    </tr>
    <tr>
        <td align="right">��</td><td><input type="text" name="kana_1" value='<?php echo($_POST['kana_1']) ?>' size=24 maxlength=16>̾<input type="text" name="kana_2" value='<?php echo($_POST['kana_2']) ?>' size=24 maxlength=16></td>
    </tr>
    <tr>
        <td width="15%">���ڥ�*</td>
    </tr>
    <tr>
        <td align="right">̾</td><td><input type="text" name="spell_1" value='<?php echo($_POST['spell_1']) ?>' size=24 maxlength=16>��<input type="text" name="spell_2" value='<?php echo($_POST['spell_2']) ?>' size=24 maxlength=16></td>
    </tr>
    <tr><td width="15%">��°</td>
        <td><select name="section">
<?php
    $query="select * from section_master where sflg=1 order by sid asc";
    $res=array();
    if($rows=getResult($query,$res)){
        for($i=0;$i<$rows;$i++){
            if($res[$i]['sid'] == $_POST['section'])
                echo("<option value=" . $res[$i]["sid"] . " selected>" . $res[$i]['section_name'] . "</option>\n");
            else
                echo("<option value=" . $res[$i]["sid"] . ">" . $res[$i]['section_name'] . "</option>\n");
        }
    }
?>
        </select>
        </td>
    </tr>
    <tr><td width="15%">����</td>
        <td><select name="position">
<?php
    $query="select * from position_master where pflg=1 order by pid asc";
    $res=array();
    if($rows=getResult($query,$res)){
        for($i=0;$i<$rows;$i++){
            if($res[$i]['pid'] == $_POST['position'])
                echo("<option value=" . $res[$i]['pid'] . " selected>" . $res[$i]['position_name'] . "</option>\n");
            else
                echo("<option value=" . $res[$i]['pid'] . ">" . $res[$i]['position_name'] . "</option>\n");
        }
    }
?>
        </select>
        </td>
    </tr>
    <tr><td width="15%">����</td>
        <td><input type="text" name="class" value='<?php echo($_POST['class']) ?>' size=12 maxlength=8></td></tr>

    <tr><td width="15%">͹���ֹ�*</td>
        <td><input type="text" name="zipcode_1" value='<?php echo($_POST['zipcode_1']) ?>' size=3 maxlength=3>
            <font size=+1> - </font><input type="text" name="zipcode_2" value='<?php echo($_POST['zipcode_2']) ?>' size=4 maxlength=4></td>
    </tr>
    <tr><td width="15%">����*</td>
<!-- �ѹ��Ľ� 2001/11/29 ��������-->
        <td><textarea rows=2 cols=64 name="address" wrap="virtual"><?php echo($_POST['address']) ?></textarea></td>
<!-- �����ޤ� -->
    </tr>
    <tr><td width="15%">�����ֹ�*</td>
        <td><input type="text" name="tel" value='<?php echo($_POST['tel']) ?>' size=16 maxlength=13></td>
    </tr>
    <tr><td width="15%">��ǯ����*</td>
        <td><input type="text" name="birthday_1" value='<?php echo($_POST['birthday_1']) ?>' size=4 maxlength=4><font size=+1> - </font><input type="text" name="birthday_2" value='<?php echo($_POST['birthday_2']) ?>' size=2 maxlength=2><font size=+1> - </font><input type="text" name="birthday_3" value='<?php echo($_POST['birthday_3']) ?>' size=2 maxlength=2></td>
    </tr>
    <tr><td width="15%">����ǯ����*</td>
        <td><input type="text" name="entrydate_1" value='<?php echo($_POST['entrydate_1']) ?>' size=4 maxlength=4><font size=+1> - </font><input type="text" name="entrydate_2" value='<?php echo($_POST['entrydate_2']) ?>' size=2 maxlength=2><font size=+1> - </font><input type="text" name="entrydate_3" value='<?php echo($_POST['entrydate_3']) ?>' size=2 maxlength=2></td>
    </tr>
    <tr><td width="15%">���ݸ�</td>
        <td><br></td>
    </tr>
    <tr><td width="15%" align="right">������</td>
        <td><input type="text" name="helthins_date_1" value='<?php echo($_POST['helthins_date_1']) ?>' size=4 maxlength=4><font size=+1> - </font><input type="text" name="helthins_date_2" value='<?php echo($_POST['helthins_date_2']) ?>' size=2 maxlength=2><font size=+1> - </font><input type="text" name="helthins_date_3" value='<?php echo($_POST['helthins_date_3']) ?>' size=2 maxlength=2></td>
    </tr>
    <tr><td width="15%" align="right">����/�ֹ�</td>
        <td><input type="text" name="helthins_no_1" value='<?php echo($_POST['helthins_no_1']) ?>' size=6 maxlength=4><font size=+1> / </font><input type="text" name="helthins_no_2" value='<?php echo($_POST['helthins_no_2']) ?>' size=5 maxlength=3></td>
    </tr>
    <tr><td width="15%">����ǯ��</td>
        <td><br></td>
    </tr>
    <tr><td width="15%" align="right">������</td>
        <td><input type="text" name="welperins_date_1" value='<?php echo($_POST['welperins_date_1']) ?>' size=4 maxlength=4><font size=+1> - </font><input type="text" name="welperins_date_2" value='<?php echo($_POST['welperins_date_2']) ?>' size=2 maxlength=2><font size=+1> - </font><input type="text" name="welperins_date_3" value='<?php echo($_POST['welperins_date_3']) ?>' size=2 maxlength=2></td>
    </tr>
    <tr><td width="15%" align="right">����/�ֹ�</td>
        <td><input type="text" name="welperins_no_1" value='<?php echo($_POST['welperins_no_1']) ?>' size=6 maxlength=4><font size=+1> / </font><input type="text" name="welperins_no_2" value='<?php echo($_POST['welperins_no_2']) ?>' size=8 maxlength=6></td>
    </tr>
    <tr><td width="15%">�����ݸ�</td>
        <td><br></td>
    </tr>
    <tr><td width="15%" align="right">������</td>
        <td><input type="text" name="unemploy_date_1" value='<?php echo($_POST['unemploy_date_1']) ?>' size=4 maxlength=4><font size=+1> - </font><input type="text" name="unemploy_date_2" value='<?php echo($_POST['unemploy_date_2']) ?>' size=2 maxlength=2><font size=+1> - </font><input type="text" name="unemploy_date_3" value='<?php echo($_POST['unemploy_date_3']) ?>' size=2 maxlength=2></td>
    </tr>
    <tr><td width="15%" align="right">����/�ֹ�</td>
        <td><input type="text" name="unemploy_no_1" value='<?php echo($_POST['unemploy_no_1']) ?>' size=6 maxlength=4><font size=+1> / </font><input type="text" name="unemploy_no_2" value='<?php echo($_POST['unemploy_no_2']) ?>' size=10 maxlength=8></td>
    <tr><td width="15%">�õ�����</td>
<!-- �ѹ��Ľ� 2001/11/29 ��������-->
        <td><textarea rows=2 cols=64 name="info" wrap="virtual"><?php echo($_POST['info']) ?></textarea></td>
<!-- �����ޤ�-->
    </tr>
    <tr><td width="15%">�����ǡ���</td>
        <td><input type="hidden" name="MAX_FILE_SIZE" value=100000>
            <input type="file" name="photo" size=80 maxlength=256>
            <input type="hidden" name="img_file"></td></tr>
    <tr><td colspan=2><hr></td>
    </tr>
    <tr><td colspan=2>�嵭�����Ƥ���Ͽ��Ԥ��ޤ� [��Ͽ��ǧ] �򲡲����Ƥ���������
                <br><br><input type="submit" value="��Ͽ��ǧ"></td>
    </tr>
    <tr><td colspan=2><hr></td>
    </tr>
    <tr><td bgcolor="#ffff00" align="center">
        <p><font color="#000000">��ջ���</font></p>
        </td><td><br></td>
    </tr>
    <tr><td colspan=2>
        <ol>
        <li>���Ϲ��ܤ�&quot;*&quot;���դ��Ƥ�����ܤ�ɬ�ܤǤ������������Ϥ�ԤäƤ���������
        <li>���͡������Ⱦ�Ѥˤ����Ϥ��Ƥ���������
            <br>������ʿ��̾���������ʤ����Ϥ�����Τ����ѤȤ��Ƥ���������
        <li>����ѥ���ɡ��ɥᥤ����᡼�륢�ɥ쥹��
            <br>��Ͽ��ǧ��Ԥ����Ȥˤ�ꥷ���ƥ���Ϳ�����ޤ���
        <li>������
            <ul>
            <li>��̾:���� ��Ϻ
            <li>�եꥬ��:�˥åȥ� ����
            <li>���ڥ�:taro nitto
            <li>�����ֹ�:028-682-8851
            <li>ǯ����:1970-04-01
            </ul>
        </ol>
        </td>
    </tr>
</table>
</form>
