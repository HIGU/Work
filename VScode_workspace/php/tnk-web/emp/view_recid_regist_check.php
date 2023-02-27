<?php
//////////////////////////////////////////////////////////////////////////////
// 社員情報管理の include file 教育・訓練の一括登録 確認フォーム            //
// Copyright(C) 2007 Norihisa.Ohya usoumu@nitto-kohki.co.jp                 //
// Changed history                                                          //
// 2007/02/07 Created  view_recid_regist_check.php                          //
// 2007/02/15 POSTをREQUESTに置き換え                                       //
//            重複がある場合登録ボタンが押せなくなるように変更              //
// 2007/06/18 社員番号未入力の場合は重複のチェックをしないように変更        //
// 2007/07/06 教育名を表示する為$receive_nameをpostするよう変更 大谷        //
//////////////////////////////////////////////////////////////////////////////
//  access_log("view_recid_regist_check.php");        // Script Name 手動設定
access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));
echo view_file_name(__FILE__);

$rows = count($_REQUEST['uid']);
$_SESSION['receive'] = $_REQUEST['receive'];
$begin_date = $_REQUEST['begin_date'];
$end_date = $_REQUEST['end_date'];
for ($r=0; $r<$rows; $r++) {
    $uid[$r] = $_REQUEST['uid'][$r];
    if (!$uid[$r]) continue;
}
$uname = array();
$chk_name = array();
for ($r=0; $r<$rows; $r++) {
    if ($uid[$r] == "") {
        $uname[$r] = '社員番号未入力';
        $chk_name[$r] = 2;
    } else {
        $query = sprintf("select name from user_detailes where uid='%s'", $uid[$r]);
        $res_name = array();
        $rows_name = getResult($query,$res_name);
        if (!getResult($query, $res_name) > 0) {   //////// 登録なし
            $uname[$r] = '社員番号登録なし';
            $chk_name[$r] = 3;
        } else {
            $query = sprintf("select name, retire_date from user_detailes where uid='%s'", $uid[$r]);
            $res_name = array();
            $rows_name = getResult($query,$res_name);
            if ($res_name[0]['retire_date'] == ""){
                $uname[$r] = $res_name[0]['name'];
                $chk_name[$r] = 0;
            } else {
                $uname[$r] = $res_name[0]['name'] . "退社済み";
                $chk_name[$r] = 1;
            }
        }
    }
}
$double_check = array();
for ($r=0; $r<$rows; $r++) {    //重複のチェック
    $id_count = 0;
    $chk_id = $uid[$r];
    if ($chk_name[$r] == 2) {   //社員番号未入力の場合は重複チェックはしない
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
              <td width="100%" bgcolor="#ff6600" align="center"><font color="#ffffff">教育登録</font></td>
            </tr>
          </table>

            <table width="100%">
              <tr>
                <td width="10%">教育名</td>
<?php
        $query="select receive_name from receive_master where rid=" . $_SESSION['receive'];
        $res=array();
        if(getResult($query,$res))
            $receive_name=$res[0]['receive_name'];
            echo("<td>$receive_name</td>");
?>
              </tr>
              <tr>
                <td width="10%">受講日</td>
<?php
                echo("<td>$begin_date ～ $end_date</td>");
?>
              </tr>
              <tr>
                <td width="10%" nowrap>受講人数</td>
                <td><?php echo($_REQUEST['entry_num']); ?>人</td>
              </tr>
            </table>
            <hr>
            <table width="100%">
<?php
    if($_REQUEST['entry_num'] >= 10){
              echo("<form method='post' action='emp_menu.php?func=" . FUNC_RECIDREGIST . "'>\n");
              echo("<tr><td colspan='2' align='right'><input type='submit' value='戻る' name='back'></td></tr>\n");
?>
                <tr>
                    <td><input type='hidden' size='8' maxlength='6' name='uid[]' value='<?php echo $uid[$r] ?>'></td>
                    <td><input type="hidden" name="begin_date" value="<?php echo($begin_date); ?>"></td>
                    <td><input type="hidden" name="end_date" value=<?php echo($end_date); ?>></td>
                    <td><input type="hidden" name="receive" value=<?php echo($_SESSION['receive']); ?>></td>  
                    <td><input type="hidden" name="receive_name" value=<?php echo($receive_name); ?>></td>             
                </tr>
<?php
              echo("</form>\n");
    }
?>            
          </table>
          <table width="50%" align='center' border='1' cellspacing='0' cellpadding='3'>
          <form method="post" action="add_receiveentry.php">
                  <!-- ここから取得人数分まわす -->
<?php
    if ($d_check == 1) {
        for($r=0;$r<$_REQUEST['entry_num'];$r++){
            $num=$r+1;
            echo("<tr>\n");
            echo("<td width='3%'>$num</td>\n");
            ?>
            <td align='left' nowrap>社員No. <?php echo($uid[$r]); ?>
                <input type='hidden' name='uid[]' value='<?php echo $uid[$r] ?>'>
                <input type='hidden' name='uname[]' value='<?php echo $uname[$r] ?>'>
            </td>
            <?php
            if ($double_check[$r] == 1) {
                echo "<td align='left' nowrap><font color='red'><B>", $uname[$r], "</B></font></td>\n";
                echo "<td align='left' nowrap><font color='red'><B>社員番号が二重に入力されています！</B></font></td>\n";
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
            <td align='left' nowrap>社員No. <?php echo($uid[$r]); ?>
                <input type='hidden' name='uid[]' value='<?php echo $uid[$r] ?>'>
                <input type='hidden' name='uname[]' value='<?php echo $uname[$r] ?>'>
                <input type="hidden" name="receive_name" value=<?php echo($receive_name); ?>>
            </td>
            <?php
            if ($chk_name[$r] > 0) {
                echo "<td align='left' nowrap><font color='#787878'><B>", $uname[$r], "</B></font></td>\n";
            } else {
                echo "<td align='left' nowrap><B>", $uname[$r], "</B></td>\n";
            }
            echo("</tr>\n");
        }
    }
?>              
              <!-- ここまで -->
              </table>
              <table width="100%">
              <tr>
              <?php
              if ($d_check == 1) {
              ?>
                <td colspan="2" align="right"><input type="submit" value="登録" disabled></td>
              <?php
              } else {
              ?>
                <td colspan="2" align="right"><input type="submit" value="登録"></td>
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
                <td><input type="hidden" name="begin_date" value="<?php echo($begin_date); ?>"></td>
                <td><input type="hidden" name="end_date" value=<?php echo($end_date); ?>></td>
                <td><input type="hidden" name="receive" value=<?php echo($_SESSION['receive']); ?>></td>
                <td><input type="hidden" name="receive_name" value=<?php echo($receive_name); ?>></td>               
              </tr>
              </form>   
<?php
              echo("<form method='post' action='emp_menu.php?func=" . FUNC_RECIDREGIST . "'>\n");
              echo("<tr><td colspan='2' align='right'><input type='submit' value='戻る' name='back'></td></tr>\n");
?>
                <tr>
                <?php
                    for($r=0;$r<$_REQUEST['entry_num'];$r++){
                ?>
                        <td><input type='hidden' name='uid[]' value='<?php echo($uid[$r]); ?>'></td>
                <?php
                    }
                ?>
                    <td><input type="hidden" name="begin_date" value="<?php echo($begin_date); ?>"></td>
                    <td><input type="hidden" name="end_date" value=<?php echo($end_date); ?>></td>
                    <td><input type="hidden" name="receive" value=<?php echo($_SESSION['receive']); ?>></td>
                    <td><input type="hidden" name="entry_num" value=<?php echo($_REQUEST['entry_num']); ?>></td>               
                    <td><input type="hidden" name="receive_name" value=<?php echo($receive_name); ?>></td>
                </tr>
<?php
              echo("</form>\n");
?>
            </table>
      </table>
