<?php
//////////////////////////////////////////////////////////////////////////////
// �Ұ���������� include file �ײ�ͭ�����Ͽ �ե�����                      //
// Copyright(C) 2015-2015 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// Changed history                                                          //
// 2015/06/18 Created  view_holyday_regist.php                              //
//////////////////////////////////////////////////////////////////////////////
//  access_log("view_capid_regist.php");        // Script Name ��ư����
access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));
echo view_file_name(__FILE__);

$rows = count($_REQUEST['uid']);
$acq_date = $_REQUEST['acq_date'];
for ($r=0; $r<$rows; $r++) {
    $uid[$r] = $_REQUEST['uid'][$r];
    if (!$uid[$r]) continue;
}
?>
<table width="100%">
            <tr>
              <td width="100%" bgcolor="#ff6600" align="center"><font color="#ffffff">�ײ�ͭ����Ͽ</font></td>
            </tr>
          </table>

            <table width="100%">
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
              echo("<form method='post' action='emp_menu.php?func=" . FUNC_ADDPHOLYDAY . "'>\n");
              echo("<tr><td colspan='2' align='right'><input type='submit' value='���' name='back'></td></tr>\n");
              echo("</form>\n");
    }
?>            
          <form method="post" action="emp_menu.php?func=<?php echo(FUNC_HOLYDAYREGISTCHK) ?>">
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
                <td><input type="hidden" name="entry_num" value=<?php echo($_REQUEST['entry_num']); ?>></td>             
            </tr>
            </form>
<?php
              echo("<form method='post' action='emp_menu.php?func=" . FUNC_ADDPHOLYDAY . "'>\n");
              echo("<tr><td colspan='2' align='right'><input type='submit' value='���' name='back'></td></tr>\n");
              echo("</form>\n");
?>
          </table>
      </table>
