<?php
//////////////////////////////////////////////////////////////////////////////
// �Ұ���������� include file �桼����������ѹ��γ�ǧ�ե�����             //
// Copyright(C) 2001-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2001/07/07 Created  view_userinfo_chgconf.php                            //
// 2002/08/07 register_globals = Off �б� & ���å�������                  //
// 2003/01/31 ����ǯ���׻�����ɽ�����뵡ǽ���ɲ� & �����ե�����λ���     //
//            ��̵�����˸��������Фʤ�bug���� if($_POST['photoid'])       //
// 2003/06/09 Administrator���¤Ǥʤ��ȥѥ���ɤ�***�ˤʤ�褦���ѹ�      //
// 2005/01/17 access_log ���ѹ��� view_file_name(__FILE__) ���ɲ�           //
//////////////////////////////////////////////////////////////////////////////
//    access_log("view_userinfo_chgconf.php");        // Script Name ��ư����
access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));
echo view_file_name(__FILE__);
    $query="select * from user_master where uid='" .$_POST['userid'] . "'";
    $res=array();
    $rows=getResult($query,$res);
    if(!$rows || $res[0]['uid']==$_POST['oldid']){
//      $mailaddr=$res[0]['mailaddr'];
//      $passwd=$res[0]['passwd'];
?>
        <form method="post" action="chg_userinfo.php">
        <table width="100%">
            <tr><td colspan=2 bgcolor="#003e7c" align="center">
                <font color="#ffffff">�������Ƥγ�ǧ</font></td></tr>

            <tr><td width="15%">����</td>
                <td>
<?php
        $query="select authority_name from authority_master where aid=" . $_POST['authority'];
        $res=array();
        if(getResult($query,$res))
            echo($res[0]['authority_name']);
        echo("<input type='hidden' name='authority' value=" . $_POST['authority'] . ">");
?>
                </td>
            </tr>
            <tr><td width="15%">�Ұ�No.</td>
                <td>
<?php 
        echo($_POST['userid']); 
        echo("<input type='hidden' name='userid' value='" . $_POST['userid'] . "'>");
?>
                </td>
            </tr>
            <tr><td width="15%">�ѥ����</td>
                <td>
<?php
        if ($_SESSION["User_ID"] == AUTH_LEVEL3) {
            echo $_POST['passwd'];
        } else {
            echo str_repeat('*', strlen($_POST['passwd']));
        }
        echo("<input type='hidden' name='passwd' value='" . $_POST['passwd'] . "'>");
?>
                </td>
            </tr>
            <tr><td width="15%">�᡼�륢�ɥ쥹</td>
                <td>
<?php
        echo($_POST['mailaddr']);
        echo("<input type='hidden' name='mailaddr' value='" . $_POST['mailaddr'] . "'>");
?>
                </td>
            </tr>

            <tr><td width="15%">��̾</td>
                <td>
<?php
        $name = $_POST['name_1'] . " " . $_POST['name_2'];
        echo($name);
        echo("<input type='hidden' name='name' value='" . $name . "'>");
?>
                </td>
            </tr>
            <tr><td width="15%">�եꥬ��</td>
                <td>
<?php
        $kana = $_POST['kana_1'] . " " . $_POST['kana_2'];
        echo($kana);
        echo("<input type='hidden' name='kana' value='" . $kana . "'>");
?>
                </td>
            </tr>
            <tr><td width="15%">���ڥ�</td>
                <td>
<?php
        $spell = $_POST['spell_1'] . " " . $_POST['spell_2'];
        echo($spell);
        echo("<input type='hidden' name='spell' value='" . $spell . "'>");
?>
                </td>
            </tr>
            <tr><td width="15%">��°</td>
                <td>
<?php
        $query="select section_name from section_master where sid=" . $_POST['section'];
        $res=array();
        if(getResult($query,$res))
            echo($res[0]['section_name']);
        echo("<input type='hidden' name='section' value=" . $_POST['section'] . ">");
?>
                </td>
            </tr>
            <tr><td width="15%">����</td>
                <td>
<?php
        $query="select position_name from position_master where pid=" . $_POST['position'];
        $res=array();
        if(getResult($query,$res))
            echo($res[0]['position_name']);
            echo("<input type='hidden' name='position' value=" . $_POST['position'] . ">");
?>
                </td>
            </tr>
            <tr><td width="15%">����</td>
                <td>
<?php
        echo($_POST['class']);
        echo("<input type='hidden' name='class' value='" . $_POST['class'] . "'>");
?>
                </td>
            </tr>
            <tr><td width="15%">͹���ֹ�</td>
                <td>
<?php
        $zipcode = $_POST['zipcode_1'] . $_POST['zipcode_2'];
        echo($_POST['zipcode_1'] . "-" . $_POST['zipcode_2']);
        echo("<input type='hidden' name='zipcode' value='" . $zipcode . "'>");
?>
                </td>
            </tr>
            <tr><td width="15%">����</td>
                <td>
<!-- �ѹ��Ľ� 2001/11/29 �������� -->
<?php
        echo($_POST['address']);
        echo("<input type='hidden' name='address' value='" . $_POST['address'] . "'>");
?>
<!-- �����ޤ� -->
                </td>
            </tr>

            <tr><td width="15%">�����ֹ�</td>
                <td>
<?php
        echo($_POST['tel']);
        echo("<input type='hidden' name='tel' value='" . $_POST['tel'] . "'>");
?>
                </td>
            </tr>
            <tr><td width="15%">��ǯ����</td>
                <td>
<?php
        $birthday = $_POST['birthday_1'] . "-" . $_POST['birthday_2'] . "-" . $_POST['birthday_3'];
        echo($birthday);
            /********* 2003/01/31 ����ǯ���׻�����ɽ�����뎡��ǽ�ɲ� *********/
        $birth_f = sprintf("%d/%d/%d", $_POST['birthday_1'], $_POST['birthday_2'], $_POST['birthday_3']);
        $res_age = array();
        $query_age = sprintf("select extract(years from age('%s'::TIMESTAMP)) as years, extract(mons from age('%s'::TIMESTAMP)) as mons, extract(days from age('%s'::TIMESTAMP)) as days",$birth_f,$birth_f,$birth_f);
        if (($rows_age=getResult($query_age,$res_age)) > 0)
            printf("������ǯ��<font color='red'><b>��%s�С�%s���%s��</b></font>", $res_age[0]['years'], $res_age[0]['mons'], $res_age[0]['days']);
            /********* 2003/01/31 END *********/
        echo("<input type='hidden' name='birthday' value='" . $birthday . "'>");
?>
                </td>
            </tr>
            <tr><td width="15%">����ǯ����</td>
                <td>
<?php
        $entrydate = $_POST['entrydate_1'] . "-" . $_POST['entrydate_2'] . "-" . $_POST['entrydate_3']; 
        echo($entrydate);
        echo("<input type='hidden' name='entrydate' value='" . $entrydate . "'>");
?>
                </td>
            </tr>
            <tr><td width="15%">���ݸ�</td>
                <td><br></td>
            </tr>
            <tr><td width="15%" align="right">������</td>
                <td>
<?php
        if ($_POST['helthins_date_1']==""){
            $helthins_date="";
        }else{
            $helthins_date = $_POST['helthins_date_1'] . "-" . $_POST['helthins_date_2'] . "-" . $_POST['helthins_date_3'];
        }
        echo($helthins_date);
        echo("<input type='hidden' name='helthins_date' value='" . $helthins_date . "'>");
?>
                </td>
            </tr>
            <tr><td width="15%" align="right">����/�ֹ�</td>
                <td>
<?php
        if ($_POST['helthins_no_1']==""){
            $helthins_no="";
        }else{
            $helthins_no = $_POST['helthins_no_1'] . "/" . $_POST['helthins_no_2'];
        }
        echo($helthins_no);
        echo("<input type='hidden' name='helthins_no' value='" . $helthins_no . "'>");
?>
                </td>
            </tr>
            <tr><td width="15%">����ǯ��</td>
                <td><br></td>
            </tr>
            <tr><td width="15%" align="right">������</td>
                <td>
<?php
        if ($_POST['welperins_date_1'] == ""){
            $welperins_date = "";
        }else{
            $welperins_date = $_POST['welperins_date_1'] . "-" . $_POST['welperins_date_2'] . "-" . $_POST['welperins_date_3'];
        }
        echo($welperins_date);
        echo("<input type='hidden' name='welperins_date' value='" . $welperins_date . "'>");
?>
                </td>
            </tr>
            <tr><td width="15%" align="right">����/�ֹ�</td>
                <td>
<?php
        if ($_POST['welperins_no_1']==""){
            $welperins_no = "";
        }else{
            $welperins_no = $_POST['welperins_no_1'] . "/" . $_POST['welperins_no_2'];
        }
        echo($welperins_no);
        echo("<input type='hidden' name='welperins_no' value='" . $welperins_no . "'>");
?>
                </td>
            </tr>
            <tr><td width="15%">�����ݸ�</td>
                <td><br></td>
            </tr>
            <tr><td width="15%" align="right">������</td>
                <td>
<?php
        if ($_POST['unemploy_date_1'] == ""){
            $unemploy_date = "";
        }else{
            $unemploy_date = $_POST['unemploy_date_1'] . "-" . $_POST['unemploy_date_2'] . "-" . $_POST['unemploy_date_3'];
        }
        echo($unemploy_date);
        echo("<input type='hidden' name='unemploy_date' value='" . $unemploy_date . "'>");
?>
                </td>
            </tr>
            <tr><td width="15%" align="right">����/�ֹ�</td>
                <td>
<?php
        if ($_POST['unemploy_no_1']==""){
            $unemploy_no = "";
        }else{
            $unemploy_no = $_POST['unemploy_no_1'] . "/" . $_POST['unemploy_no_2'];
        }
        echo($unemploy_no);
        echo("<input type='hidden' name='unemploy_no' value='" . $unemploy_no . "'>");
?>
                </td>
            </tr>
            <tr><td width="15%">�õ�����</td>
                <td>
<!-- �ѹ��Ľ� 2001/11/29 �������� -->
<?php
        echo($_POST['info']);
        echo("<input type='hidden' name='info' value='" . $_POST['info'] . "'>");
?>
<!-- �����ޤ� -->
                </td>
            </tr>
            <tr><td width="15%">�����ǡ���</td>
                <td>
<?php
        $img_file = StripSlashes($_POST['img_file']);
        echo($img_file);
?>
                </td>
            </tr>
            <tr><td width="15%"><br></td>
                <td>
<?php
        if($_FILES['photo']['tmp_name'] != ""){
            $photo = StripSlashes($_FILES['photo']['tmp_name']);
            $file = INS_IMG . $_SESSION["User_ID"];
            copy($_FILES['photo']['tmp_name'], $file);
            echo("<img src='$file?" . uniqid(abcdef) . "' align='left' alt='�������᡼��' width=256 height=384 border=0>\n");
            //$photo=StripSlashes($photo);
//          copy($_FILES['photo']['tmp_name'],INS_IMG . $_SESSION['User_ID']);
//          $fullpath = "file:///" . "$img_file";
//          echo("<img src='$fullpath?" . uniqid(abcdef) . "' align='left' alt='�������᡼��' width=256 height=384 border=0>");
            echo("<input type='hidden' name='photo' value=1>");
        }else{
            if($_POST['photoid']){
                $photoid = $_POST['photoid'];
                $file    = IND . $_POST['userid'] . '.gif';
                getObject($photoid, $file);
                echo("<img src='$file?new' width=256 height=384 border=0></td>");
            }
        }
        echo("<input type='hidden' name='photoid' value=$photoid>");
?>
                </td>
            </tr>

            <tr><td colspan=2><hr></td>
            </tr>

            <tr><td colspan=2>�嵭�����ƤǤ������� [��Ͽ�¹�] �򲡲����Ƥ���������
                <br>ɬ�פʤ�� [���] �ˤ����Ϥ���ٹԤäƤ���������
                    <br><br><input type="submit" value="��Ͽ�¹�">
                </td>
            </tr>
        </table>
    </form>
    <form method="post" action="emp_menu.php?func=<?php echo(FUNC_CHGUSERINFO) ?>">
        <input type="hidden" name="inf" value="������ѹ�">

        <input type='hidden' name='authority' value='<?php echo($_POST["authority"]) ?>'>
        <input type='hidden' name='userid' value='<?php echo($_POST["userid"]) ?>'>
        <input type='hidden' name='name_1' value='<?php echo($_POST["name_1"]) ?>'>
        <input type='hidden' name='name_2' value='<?php echo($_POST["name_2"]) ?>'>
        <input type='hidden' name='kana_1' value='<?php echo($_POST["kana_1"]) ?>'>
        <input type='hidden' name='kana_2' value='<?php echo($_POST["kana_2"]) ?>'>
        <input type='hidden' name='spell_1' value='<?php echo($_POST["spell_1"]) ?>'>
        <input type='hidden' name='spell_2' value='<?php echo($_POST["spell_2"]) ?>'>
        <input type='hidden' name='section' value='<?php echo($_POST["section"]) ?>'>
        <input type='hidden' name='position' value='<?php echo($_POST["position"]) ?>'>
        <input type='hidden' name='class' value='<?php echo($_POST["class"]) ?>'>
        <input type='hidden' name='zipcode_1' value='<?php echo($_POST["zipcode_1"]) ?>'>
        <input type='hidden' name='zipcode_2' value='<?php echo($_POST["zipcode_2"]) ?>'>
        <input type='hidden' name='address' value='<?php echo($_POST["address"]) ?>'>
        <input type='hidden' name='tel' value='<?php echo($_POST["tel"]) ?>'>
        <input type='hidden' name='birthday_1' value='<?php echo($_POST["birthday_1"]) ?>'>
        <input type='hidden' name='birthday_2' value='<?php echo($_POST["birthday_2"]) ?>'>
        <input type='hidden' name='birthday_3' value='<?php echo($_POST["birthday_3"]) ?>'>
        <input type='hidden' name='entrydate_1' value='<?php echo($_POST["entrydate_1"]) ?>'>
        <input type='hidden' name='entrydate_2' value='<?php echo($_POST["entrydate_2"]) ?>'>
        <input type='hidden' name='entrydate_3' value='<?php echo($_POST["entrydate_3"]) ?>'>
        <input type='hidden' name='helthins_date_1' value='<?php echo($_POST["helthins_date_1"]) ?>'>
        <input type='hidden' name='helthins_date_2' value='<?php echo($_POST["helthins_date_2"]) ?>'>
        <input type='hidden' name='helthins_date_3' value='<?php echo($_POST["helthins_date_3"]) ?>'>
        <input type='hidden' name='helthins_no_1' value='<?php echo($_POST["helthins_no_1"]) ?>'>
        <input type='hidden' name='helthins_no_2' value='<?php echo($_POST["helthins_no_2"]) ?>'>
        <input type='hidden' name='welperins_date_1' value='<?php echo($_POST["welperins_date_1"]) ?>'>
        <input type='hidden' name='welperins_date_2' value='<?php echo($_POST["welperins_date_2"]) ?>'>
        <input type='hidden' name='welperins_date_3' value='<?php echo($_POST["welperins_date_3"]) ?>'>
        <input type='hidden' name='welperins_no_1' value='<?php echo($_POST["welperins_no_1"]) ?>'>
        <input type='hidden' name='welperins_no_2' value='<?php echo($_POST["welperins_no_2"]) ?>'>
        <input type='hidden' name='unemploy_date_1' value='<?php echo($_POST["unemploy_date_1"]) ?>'>
        <input type='hidden' name='unemploy_date_2' value='<?php echo($_POST["unemploy_date_2"]) ?>'>
        <input type='hidden' name='unemploy_date_3' value='<?php echo($_POST["unemploy_date_3"]) ?>'>
        <input type='hidden' name='unemploy_no_1' value='<?php echo($_POST["unemploy_no_1"]) ?>'>
        <input type='hidden' name='unemploy_no_2' value='<?php echo($_POST["unemploy_no_2"]) ?>'>
        <input type='hidden' name='info' value='<?php echo($_POST["info"]) ?>'>
        <input type='hidden' name='img_file' value='<?php echo($_POST["img_file"]) ?>'>
                        <p><input type="submit" value="���" ></p></td>
    </form>
<?php
    }else{
?>
        <table width="100%">
            <tr><td colspan=2 bgcolor="#003e7c" align="center">
                <font color="#ffffff">�������Ƥγ�ǧ</font></td>
            </tr>
        </table>
<script language="javascript">
    alert("�Ұ�No��¾�Υ桼�����Ƚ�ʣ���Ƥ��ޤ��������Ԥˤ��䤤��碌����������");
</script>
<?php
    }
?>
