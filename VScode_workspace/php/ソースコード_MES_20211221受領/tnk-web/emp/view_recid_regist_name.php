<?php
//////////////////////////////////////////////////////////////////////////////
// �Ұ���������� include file ���顦�����ΰ����Ͽ �ե�����                //
// Copyright(C) 2001-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2001/07/07 Created  view_recid_regist.php                                //
// 2002/08/07 register_globals = Off �б� & ���å�������                  //
// 2005/01/17 access_log ���ѹ��� view_file_name(__FILE__) ���ɲ�           //
//////////////////////////////////////////////////////////////////////////////
//  access_log("view_recid_regist.php");        // Script Name ��ư����
access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));
echo view_file_name(__FILE__);
?>
<table width="100%">
            <tr>
              <td width="100%" bgcolor="#ff6600" align="center"><font color="#ffffff">������Ͽ</font></td>
            </tr>
          </table>

            <table width="100%">
              <tr>
                <td width="10%">����̾</td>
<?php
        $query="select receive_name from receive_master where rid=" . $_POST['receive'];
        $res=array();
        if(getResult($query,$res))
            $receive_name=$res[0]['receive_name'];
            echo("<td>$receive_name</td>");
?>
              </tr>
              <tr>
                <td width="10%">������</td>
<?php
    $begin_date = $_POST['begin_date_1'] . "-" . $_POST['begin_date_2'] . "-" . $_POST['begin_date_3'];
    $end_date   = $_POST['end_date_1'] . "-" . $_POST['end_date_2'] . "-" . $_POST['end_date_3'];
                echo("<td>$begin_date �� $end_date</td>");
?>
              </tr>
              <tr>
                <td width="10%" nowrap>���ֿͿ�</td>
                <td><?php echo($_POST['entry_num']); ?>��</td>
              </tr>
            </table>
            <hr>
            <table width="100%">
<?php
    if($_POST['entry_num'] >= 10){
              echo("<form method='post' action='emp_menu.php?func=" . FUNC_CHGRECEIVE . "'>\n");
              echo("<tr><td colspan='2' align='right'><input type='submit' value='���' name='back'></td></tr>\n");
              echo("</form>\n");
    }
?>            
          <form method="post" action="add_receiveentry.php">
                  <!-- ������������Ϳ�ʬ�ޤ魯 -->
<?php
        for($r=0;$r<$_POST['entry_num'];$r++){
            $num=$r+1;
            echo("<tr>\n"); 
            echo("<td width='3%'>$num</td>\n");
            echo("<td align='left'>�Ұ�No. <input type='text' size='8' maxlength='6' name='uid[]'></td>\n");
            echo("</tr>\n");
        }
?>              
              <!-- �����ޤ� -->
              <tr>
                <td colspan="2" align="right"><input type="submit" value="��Ͽ"></td>
              </tr>
              <tr>
                <td><input type="hidden" name="begin_date" value="<?php echo($begin_date); ?>"></td>
                <td><input type="hidden" name="end_date" value=<?php echo($end_date); ?>></td>
                <td><input type="hidden" name="receive" value=<?php echo($_POST['receive']); ?>></td>               
              </tr>
              </form>   
<?php
              echo("<form method='post' action='emp_menu.php?func=" . FUNC_CHGRECEIVE . "'>\n");
              echo("<tr><td colspan='2' align='right'><input type='submit' value='���' name='back'></td></tr>\n");
              echo("</form>\n");
?>
            </table>
      </table>
