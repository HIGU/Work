<?php
//////////////////////////////////////////////////////////////////////////////
// 社員情報管理の include file 教育・訓練の一括登録 フォーム                //
// Copyright(C) 2001-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2001/07/07 Created  view_recid_regist.php                                //
// 2002/08/07 register_globals = Off 対応 & セッション管理                  //
// 2005/01/17 access_log の変更と view_file_name(__FILE__) の追加           //
// 2007/02/07 登録時にview_recid_regist_check.phpで確認画面へ   大谷        //
// 2007/02/15 POSTをREQUESTに変更   大谷                                    //
//////////////////////////////////////////////////////////////////////////////
//  access_log("view_recid_regist.php");        // Script Name 手動設定
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
        $query="select receive_name from receive_master where rid=" . $_REQUEST['receive'];
        $res=array();
        if(getResult($query,$res))
            $receive_name=$res[0]['receive_name'];
            echo("<td>$receive_name</td>");
?>
              </tr>
              <tr>
                <td width="10%">受講日</td>
<?php
    if ($begin_date == '') {
        $begin_date = $_REQUEST['begin_date_1'] . "-" . $_REQUEST['begin_date_2'] . "-" . $_REQUEST['begin_date_3'];
        $end_date   = $_REQUEST['end_date_1'] . "-" . $_REQUEST['end_date_2'] . "-" . $_REQUEST['end_date_3'];
    }
                echo("<td>$begin_date 〜 $end_date</td>");
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
              echo("<form method='post' action='emp_menu.php?func=" . FUNC_CHGRECEIVE . "'>\n");
              echo("<tr><td colspan='2' align='right'><input type='submit' value='戻る' name='back'></td></tr>\n");
              echo("</form>\n");
    }
?>            
          <form method="post" action="emp_menu.php?func=<?php echo(FUNC_RECIDREGISTCHK) ?>">
                  <!-- ここから取得人数分まわす -->
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
                <td><input type="hidden" name="begin_date" value="<?php echo($begin_date); ?>"></td>
                <td><input type="hidden" name="end_date" value=<?php echo($end_date); ?>></td>
                <td><input type="hidden" name="receive" value=<?php echo($_REQUEST['receive']); ?>></td>
                <td><input type="hidden" name="entry_num" value=<?php echo($_REQUEST['entry_num']); ?>></td>               
              </tr>
              </form>   
<?php
              echo("<form method='post' action='emp_menu.php?func=" . FUNC_CHGRECEIVE . "'>\n");
              echo("<tr><td colspan='2' align='right'><input type='submit' value='戻る' name='back'></td></tr>\n");
              echo("</form>\n");
?>
            </table>
      </table>
