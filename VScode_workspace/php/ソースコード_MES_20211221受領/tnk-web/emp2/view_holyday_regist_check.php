<?php
//////////////////////////////////////////////////////////////////////////////
// �Ұ���������� include file �ײ�ͭ�����Ͽ �ե�����                      //
// Copyright(C) 2015 - 2015 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp   //
// Changed history                                                          //
// 2015/06/18 Created  view_holyday_regist_check.php                        //
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
$uname = array();
$chk_name = array();
for ($r=0; $r<$rows; $r++) {
    if ($uid[$r] == "") {
        $uname[$r] = '�Ұ��ֹ�̤����';
        $chk_name[$r] = 2;
    } else {
        $query = sprintf("select name from user_detailes where uid='%s'", $uid[$r]);
        $res_name = array();
        $rows_name = getResult($query,$res_name);
        if (!getResult($query, $res_name) > 0) {   //////// ��Ͽ�ʤ�
            $uname[$r] = '�Ұ��ֹ���Ͽ�ʤ�';
            $chk_name[$r] = 3;
        } else {
            $query = sprintf("select name, retire_date from user_detailes where uid='%s'", $uid[$r]);
            $res_name = array();
            $rows_name = getResult($query,$res_name);
            if ($res_name[0]['retire_date'] == ""){
                $uname[$r] = $res_name[0]['name'];
                $chk_name[$r] = 0;
            } else {
                $uname[$r] = $res_name[0]['name'] . "��ҺѤ�";
                $chk_name[$r] = 1;
            }
        }
    }
}
$double_check = array();
for ($r=0; $r<$rows; $r++) {    //��ʣ�Υ����å�
    $id_count = 0;
    $chk_id = $uid[$r];
    if ($chk_name[$r] == 2) {   //�Ұ��ֹ�̤���Ϥξ��Ͻ�ʣ�����å��Ϥ��ʤ�
        $double_check[$i] = 0;
        $double_check[$r] = 0;
    } else {
        if ($double_check[$r] == '') {
            for ($i=$r+1; $i<$rows; $i++) {
                if ($chk_id == $uid[$i]) {
                $id_count++;
                }
                if ($id_count > 0) {
                    $double_check[$i] = 1;
                    $double_check[$r] = 1;
                    $id_count = 0;
                }
            } 
         } else if ($double_check[$r] == 0) {
            $double_check[$i] = 0;
            $double_check[$r] = 0;
         }
    }
}

for ($r=0; $r<$rows; $r++) {
    if ($double_check[$r] == 1) {
        $d_check = 1;
    }
}
?>
<table width="100%">
            <tr>
              <td width="100%" bgcolor="#ff6600" align="center"><font color="#ffffff">�����Ͽ</font></td>
            </tr>
          </table>

            <table width="100%">
              <tr>
                <td width="10%">������</td>
                <td><?php echo($acq_date); ?></td>
              </tr>
              <tr>
                <td width="10%">�����Ϳ�</td>
                <td><?php echo($_REQUEST['entry_num']); ?>��</td>
              </tr>
            </table>
            <hr>
            <table width="50%" align='center' border='1' cellspacing='0' cellpadding='3'>
<?php
    if($_REQUEST['entry_num'] >= 10){
              echo("<form method='post' action='emp_menu.php?func=" . FUNC_HOLYDAYREGIST . "'>\n");
              echo("<tr><td colspan='2' align='right'><input type='submit' value='���' name='back'></td></tr>\n");
?>
                <tr>
                    <td><input type='hidden' size='8' maxlength='6' name='uid[]' value='<?php echo $uid[$r] ?>'></td>
                    <td><input type="hidden" name="acq_date" value="<?php echo($acq_date); ?>"></td>
                </tr>
<?php
                echo("</form>\n");
    }
?>            
          <form method="post" action="add_holydayentry.php">
              <!-- ����������Ͽ�Ϳ�ʬ�롼�� -->
<?php
    if ($d_check == 1) {
        for($r=0;$r<$_REQUEST['entry_num'];$r++){
            $num=$r+1;
            echo("<tr>\n");
            echo("<td width='3%'>$num</td>\n");
            ?>
            <td align='left' nowrap>�Ұ�No. <?php echo($uid[$r]); ?>
                <input type='hidden' name='uid[]' value='<?php echo $uid[$r] ?>'>
                <input type='hidden' name='uname[]' value='<?php echo $uname[$r] ?>'>
            </td>
            <?php
            if ($double_check[$r] == 1) {
                echo "<td align='left' nowrap><font color='red'><B>", $uname[$r], "</B></font></td>\n";
                echo "<td align='left' nowrap><font color='red'><B>�Ұ��ֹ椬��Ť����Ϥ���Ƥ��ޤ���</B></font></td>\n";
            } else {
                if ($chk_name[$r] > 0) {
                    echo "<td align='left' nowrap><font color='#787878'><B>", $uname[$r], "</B></font></td>\n";
                } else {
                    echo "<td align='left' nowrap><B>", $uname[$r], "</B></td>\n";
                }
            }
            echo("</tr>\n");
        }
    } else {
        for($r=0;$r<$_REQUEST['entry_num'];$r++){
              $num=$r+1;
              echo("<tr>\n"); 
              echo("<td width='3%'>$num</td>\n");
             ?>
            <td align='left'>�Ұ�No. <?php echo($uid[$r]); ?>
                <input type='hidden' name='uid[]' value='<?php echo $uid[$r] ?>'>
                <input type='hidden' name='uname[]' value='<?php echo $uname[$r] ?>'>
            </td>
            <?php
            if ($chk_name[$r] > 0) {
                echo "<td align='left'><font color='#787878'><B>", $uname[$r], "</B></font></td>\n";
            } else {
                echo "<td align='left'><B>", $uname[$r], "</B></td>\n";
            }
            echo("</tr>\n");
        }
    }
?>                 
              <!-- �����ޤ� -->
              </table>
              <table width="100%">
              <tr>
              <?php
              if ($d_check == 1) {
              ?>
                <td colspan="2" align="right"><input type="submit" value="��Ͽ" disabled></td>
              <?php
              } else {
              ?>
                <td colspan="2" align="right"><input type="submit" value="��Ͽ"></td>
              <?php
              }
              ?>
              </tr>
              <tr>
                <?php
                  for($r=0;$r<$_REQUEST['entry_num'];$r++){
              ?>
                      <td><input type='hidden' name='chk_name[]' value='<?php echo $chk_name[$r] ?>'></td>
              <?php
                  }
              ?>
                <td><input type="hidden" name="acq_date" value="<?php echo($acq_date); ?>"></td>             
              </tr>
            </form>
<?php
              echo("<form method='post' action='emp_menu.php?func=" . FUNC_HOLYDAYREGIST . "'>\n");
              echo("<tr><td colspan='2' align='right'><input type='submit' value='���' name='back'></td></tr>\n");
              ?>
                <tr>
                <?php
                    for($r=0;$r<$_REQUEST['entry_num'];$r++){
                ?>
                        <td><input type='hidden' name='uid[]' value='<?php echo($uid[$r]); ?>'></td>
                <?php
                    }
                ?>
                    <td><input type="hidden" name="acq_date" value="<?php echo($acq_date); ?>"></td>
                    <td><input type="hidden" name="entry_num" value=<?php echo($_REQUEST['entry_num']); ?>></td>
                </tr>
<?php
                echo("</form>\n");
?>
          </table>
      </table>
