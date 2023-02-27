<?php
//////////////////////////////////////////////////////////////////////////////
// 社員情報管理の include file 計画有給の登録 フォーム                      //
// Copyright(C) 2015-2015 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// Changed history                                                          //
// 2015/06/18 Created  view_holyday_regist.php                              //
//////////////////////////////////////////////////////////////////////////////
//  access_log("view_capid_regist.php");        // Script Name 手動設定
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
              <td width="100%" bgcolor="#ff6600" align="center"><font color="#ffffff">計画有給登録</font></td>
            </tr>
          </table>

            <table width="100%">
              <tr>
                <td width="10%">取得日</td>
<?php
    if ($acq_date == '') {
        $acq_date = $_REQUEST['begin_date_1'] . "-" . $_REQUEST['begin_date_2'] . "-" . $_REQUEST['begin_date_3'];
    }
                echo("<td>$acq_date</td>");
?>
              </tr>
              <tr>
                <td width="10%">取得人数</td>
                <td><?php echo($_REQUEST['entry_num']); ?>人</td>
              </tr>
            </table>
            <hr>
            <table width="100%">
<?php
    if($_REQUEST['entry_num'] >= 10){
              echo("<form method='post' action='emp_menu.php?func=" . FUNC_ADDPHOLYDAY . "'>\n");
              echo("<tr><td colspan='2' align='right'><input type='submit' value='戻る' name='back'></td></tr>\n");
              echo("</form>\n");
    }
?>            
          <form method="post" action="emp_menu.php?func=<?php echo(FUNC_HOLYDAYREGISTCHK) ?>">
              <!-- ここから登録人数分ループ -->
<?php
        for($r=0;$r<$_REQUEST['entry_num'];$r++){
              $num=$r+1;
              echo("<tr>\n"); 
              echo("<td width='3%'>$num</td>\n");
?>
              <td align='left'>社員No. <input type='text' size='8' maxlength='6' name='uid[]' value='<?php echo $uid[$r] ?>'></td>
<?php
              echo("</tr>\n");
        }
?>                 
              <!-- ここまで -->
              <tr>
                <td colspan="2" align="right"><input type="submit" value="登録"></td>
              </tr>
              <tr>
                <td><input type="hidden" name="acq_date" value="<?php echo($acq_date); ?>"></td>
                <td><input type="hidden" name="entry_num" value=<?php echo($_REQUEST['entry_num']); ?>></td>             
            </tr>
            </form>
<?php
              echo("<form method='post' action='emp_menu.php?func=" . FUNC_ADDPHOLYDAY . "'>\n");
              echo("<tr><td colspan='2' align='right'><input type='submit' value='戻る' name='back'></td></tr>\n");
              echo("</form>\n");
?>
          </table>
      </table>
