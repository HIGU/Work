<?php
//////////////////////////////////////////////////////////////////////////////
// �Ұ���������� include file ��ʤΰ����Ͽ �ե�����                      //
// Copyright(C) 2001-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2001/07/07 Created  view_capid_regist.php                                //
// 2002/08/07 register_globals = Off �б� & ���å�������                  //
// 2005/01/17 access_log ���ѹ��� view_file_name(__FILE__) ���ɲ�           //
// 2007/02/09 ��Ͽ����view_capid_regist_check.php�ǳ�ǧ���̤� ��ë          //
// 2007/02/15 POST��REQUEST���ѹ�   ��ë                                    //
//////////////////////////////////////////////////////////////////////////////
//  access_log("view_capid_regist.php");        // Script Name ��ư����
access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));
echo view_file_name(__FILE__);

$rows = count($_REQUEST['uid']);
$_SESSION['capacity'] = $_REQUEST['capacity'];
$acq_date = $_REQUEST['acq_date'];
for ($r=0; $r<$rows; $r++) {
    $uid[$r] = $_REQUEST['uid'][$r];
    if (!$uid[$r]) continue;
}
?>
<table width="100%">
            <tr>
              <td width="100%" bgcolor="#ff6600" align="center"><font color="#ffffff">�����Ͽ</font></td>
            </tr>
          </table>

            <table width="100%">
              <tr>
                <td width="10%">���̾</td>
<?php
        $query="select capacity_name from capacity_master where cid=" . $_REQUEST['capacity'];
        $res=array();
        if(getResult($query,$res))
            $capacity_name=$res[0]['capacity_name'];
            echo("<td>$capacity_name</td>");
?>
              </tr>
              <tr>
                <td width="10%">������</td>
<?php
    if ($acq_date == '') {
        $acq_date = $_REQUEST['begin_date_1'] . "-" . $_REQUEST['begin_date_2'] . "-" . $_REQUEST['begin_date_3'];
    }
                echo("<td>$acq_date</td>");
?>
              </tr>
              <tr>
                <td width="10%">�����Ϳ�</td>
                <td><?php echo($_REQUEST['entry_num']); ?>��</td>
              </tr>
            </table>
            <hr>
            <table width="100%">
<?php
    if($_REQUEST['entry_num'] >= 10){
              echo("<form method='post' action='emp_menu.php?func=" . FUNC_CHGCAPACITY . "'>\n");
              echo("<tr><td colspan='2' align='right'><input type='submit' value='���' name='back'></td></tr>\n");
              echo("</form>\n");
    }
?>            
          <form method="post" action="emp_menu.php?func=<?php echo(FUNC_CAPIDREGISTCHK) ?>">
              <!-- ����������Ͽ�Ϳ�ʬ�롼�� -->
<?php
        for($r=0;$r<$_REQUEST['entry_num'];$r++){
              $num=$r+1;
              echo("<tr>\n"); 
              echo("<td width='3%'>$num</td>\n");
?>
              <td align='left'>�Ұ�No. <input type='text' size='8' maxlength='6' name='uid[]' value='<?php echo $uid[$r] ?>'></td>
<?php
              echo("</tr>\n");
        }
?>                 
              <!-- �����ޤ� -->
              <tr>
                <td colspan="2" align="right"><input type="submit" value="��Ͽ"></td>
              </tr>
              <tr>
                <td><input type="hidden" name="acq_date" value="<?php echo($acq_date); ?>"></td>
                <td><input type="hidden" name="capacity" value=<?php echo($_REQUEST['capacity']); ?>></td>
                <td><input type="hidden" name="entry_num" value=<?php echo($_REQUEST['entry_num']); ?>></td>             
            </tr>
            </form>
<?php
              echo("<form method='post' action='emp_menu.php?func=" . FUNC_CHGCAPACITY . "'>\n");
              echo("<tr><td colspan='2' align='right'><input type='submit' value='���' name='back'></td></tr>\n");
              echo("</form>\n");
?>
          </table>
      </table>
