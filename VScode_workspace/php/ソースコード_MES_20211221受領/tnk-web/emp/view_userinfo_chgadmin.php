<?php
//////////////////////////////////////////////////////////////////////////////
// 社員情報管理の include file 所属・教育・資格・退職 の処理フォーム        //
// Copyright(C) 2001-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2001/07/07 Created  view_userinfo_chgadmin.php                           //
// 2002/08/07 register_globals = Off 対応 & セッション管理                  //
// 2002/11/25 所属の変更に ＊=現在使用されていない部署を追加                //
// 2005/01/17 access_log の変更と view_file_name(__FILE__) の追加           //
// 2006/01/06 移動履歴の select文で ut.oid, を削除                          //
// 2006/01/10 教育履歴の   〃       ur.oid  取得資格の uc.oid を削除        //
// 2006/01/11 移動履歴の削除でoid→trans_dateへ $res[$r][sid]→['sid']へ変更//
// 2006/02/13 教育履歴の削除で oid → rid, begin_date, end_date へ変更      //
// 2006/02/14 資格履歴の削除で oid → acq_date, cid へ変更                  //
// 2007/03/07 移動を異動に訂正 大谷                                         //
//////////////////////////////////////////////////////////////////////////////
// access_log("view_userinfo_chgadmin.php");       // Script Name 手動設定
access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));
echo view_file_name(__FILE__);
if ( isset($_POST['userid']) ) {
    $userid = $_POST['userid'];
    $sect   = $_POST['sect'];
    $recv   = $_POST['recv'];
    $capa   = $_POST['capa'];
    $retire = $_POST['retire'];
} else {
    $userid = $_SESSION['userid'];
    $sect   = $_SESSION['sect'];
    $recv   = $_SESSION['recv'];
    $capa   = $_SESSION['capa'];
    $retire = $_SESSION['retire'];
}
unset($_SESSION['userid']);
unset($_SESSION['sect']);
unset($_SESSION['recv']);
unset($_SESSION['capa']);
unset($_SESSION['retire']);

$query="select ud.name,ud.kana,sm.section_name,pm.position_name from user_detailes ud,section_master sm,position_master pm" . 
    " where ud.uid='" . $userid . "' and ud.sid=sm.sid and ud.pid=pm.pid";
$res=array();
getResult($query,$res);
$name=$res[0]['name'];
$kana=$res[0]['kana'];
$section_name=$res[0]['section_name'];
if ( isset($sect) ) {
?>
<table width="100%">
    <tr><td colspan=2 width="100%" bgcolor="#ff6600" align="center">
        <font color="#ffffff">ユーザーの所属変更</font></td>
    </tr>

    <form method="post" action="add_usertransfer.php">
    <tr><td colspan=2 width="100%" valign="top">
        <table width="100%">
            <tr><td width="15%">社員No</td>
                <td><?php echo($userid); ?></td>
            </tr>
            <tr><td width="15%">名前</td>
                <td><font size=1><?php echo($kana); ?></font><br><?php echo($name); ?></td>
            </tr>
            <tr><td width="15%">現在の所属</td>
                <td><?php echo($section_name); ?></td>
            </tr>
            <tr><td colspan=2><hr></td></tr>
        </table></td>
    </tr>
    
    <tr>
    <td colspan=1 width="40%" valign="top">
        <table width="100%">    
            <tr><td>新しい所属名を選択してください。<br>＊=現在使用されていない部署</td></tr>
            <tr><td><select name="section">
<?php
        $query="select * from section_master order by sid asc";
        $res=array();
        if($rows=getResult($query,$res)){
            for($i=0;$i<$rows;$i++){
                if($res[$i]['sflg'] == 0)
                    echo("<option value='" . $res[$i]['sid'] . "'>*" . $res[$i]['section_name'] . "\n");
                else
                    echo("<option value='" . $res[$i]['sid'] . "'>" . $res[$i]['section_name'] . "\n");
            }
        }
?>
            </select></td></tr>
            <tr><td>異動日を入力してください。</td></tr>
            <tr><td><input type="text" name="trans_date_1" size=4 maxlength=4 value=<?php echo(substr(date("Y-m-d"),0,4)); ?>><font size=+1> - </font><input type="text" name="trans_date_2" size=2 maxlength=2 value=<?php echo(substr(date("Y-m-d"),5,2)); ?>><font size=+1> - </font><input type="text" name="trans_date_3" size=2 maxlength=2 value=<?php echo(substr(date("Y-m-d"),8,2)); ?>></td></tr>
            <tr><td align="right">
                    <input type="submit" value="追加">
                    <input type="hidden" name="userid" value="<?php echo($userid); ?>">
                    <input type="hidden" name="name" value="<?php echo($name); ?>">
                    <input type="hidden" name="section_name" value="<?php echo($section_name); ?>">
    </form>
            <form method="post" action="emp_menu.php?func=<?php echo(FUNC_CHGUSERINFO); ?>">
                <p><input type="submit" name="inf" value="戻る"></p>
                <input type="hidden" name="userid" value="<?php echo($userid); ?>">
                <input type="hidden" name="name" value="<?php echo($name); ?>">
                <input type="hidden" name="section_name" value="<?php echo($section_name); ?>">
                </td><br></tr>
        </table>
        
    </td>
            </form>
    <td colspan=1 width="60%" valign="top">
        <table width="100%">
            <tr><td colspan=3>異動履歴</td></tr>
<?php
        $query="select ut.trans_date,ut.section_name,ut.sid from user_transfer ut" . 
            " where ut.uid='" . $userid . "' order by ut.trans_date";
        $res=array();
        $rows=getResult($query,$res);
        for($r=0;$r<$rows;$r++){
            $trans_date = trim($res[$r]['trans_date']);
            $section_name = trim($res[$r]['section_name']);
            $sid = $res[$r]['sid'];
            // $oid=$res[$r]['oid'];
            echo("<form method='post' action='del_usertransfer.php'><tr><td><br></td>\n");
            echo("<td width='30%'>" . $trans_date . "</td>\n");
            echo("<td><input type='text' size=32 maxlength=64 name='section_name' value='$section_name'</td>\n");
            echo("<td align='right'><input type='submit' name='chg' value='名称変更'></td>\n");
            echo("<td><input type='submit' name='del' value='削除'></td></tr>\n");
            echo("<tr><td><input type='hidden' name='userid' value='" . $userid . "'></td>\n");
            echo("<td><input type='hidden' name='sid' value=$sid></td>\n");
            // echo("<td><input type='hidden' name='oid' value=$oid></td></tr>\n");
            echo("<td><input type='hidden' name='trans_date' value=$trans_date></td></tr>\n");
            echo("</form>\n");
        }
?>
        </table>
    </td>
    </tr>
</table>
<?php
    } elseif ( isset($recv) ) {
?>
<table width="100%">
    <tr><td colspan=2 width="100%" bgcolor="#ff6600" align="center">
        <font color="#ffffff">ユーザーの教育</font></td>
    </tr>

    <form method="post" action="add_userreceive.php">
    <tr><td colspan=2 width="100%" valign="top">
        <table width="100%">
            <tr><td width="15%">社員No</td>
                <td><font><?php echo($userid); ?></font></td>
            </tr>
            <tr><td width="15%">名前</td>
                <td><font size=1><?php echo($kana); ?></font><br><?php echo($name); ?></td>
            </tr>
            <tr><td width="15%">所属</td>
                <td><font><?php echo($section_name); ?></font></td>
            </tr>
            <tr><td colspan=2><hr></td></tr>
        </table></td>
    </tr>
    
    <tr>
    <td colspan=1 width="40%" valign="top">
        <table width="100%">
            <tr><td>受講した教育名を選択してください。</td></tr>
            <tr><td><select name="receive">
<?php
        $query="select * from receive_master order by rid asc";
        $res=array();
        if($rows=getResult($query,$res)){
            for($i=0;$i<$rows;$i++)
                echo("<option value=" . $res[$i][rid] . ">" . $res[$i][receive_name] . "\n");
    }
?>
            </select></td></tr>

            <input type="hidden" name="userid" value="<?php echo($userid); ?>">
            <tr><td>開始日を入力してください。</td></tr>
            <tr><td><input type="text" name="begin_date_1" size=4 maxlength=4 value=<?php echo(substr(date("Y-m-d"),0,4)); ?>><font size=+1> - </font><input type="text" name="begin_date_2" size=2 maxlength=2 value=<?php echo(substr(date("Y-m-d"),5,2)); ?>><font size=+1> - </font><input type="text" name="begin_date_3" size=2 maxlength=2 value=<?php echo(substr(date("Y-m-d"),8,2)); ?>></td></tr>
            <tr><td>終了日を入力してください。</td></tr>
            <tr><td><input type="text" name="end_date_1" size=4 maxlength=4 value=<?php echo(substr(date("Y-m-d"),0,4)); ?>><font size=+1> - </font><input type="text" name="end_date_2" size=2 maxlength=2 value=<?php echo(substr(date("Y-m-d"),5,2)); ?>><font size=+1> - </font><input type="text" name="end_date_3" size=2 maxlength=2 value=<?php echo(substr(date("Y-m-d"),8,2)); ?>></td></tr>
            <tr><td align="right">
                <input type="submit" value="追加">
        </form>
            <form method="post" action="emp_menu.php?func=<?php echo(FUNC_CHGUSERINFO); ?>">
                <p><input type="submit" name="inf" value="戻る"></p>
                <input type="hidden" name="userid" value="<?php echo($userid); ?>">
                <input type="hidden" name="name" value="<?php echo($name); ?>">
                <input type="hidden" name="section_name" value="<?php echo($section_name); ?>">
            </form>
            </td>
            <td><br></td></tr>
        </table>
    </td>
    
    <td colspan=1 width="60%" valign="top">
        <table width="100%">
            <tr><td colspan=3>教育履歴</td></tr>
<?php
        $query = "
            SELECT ur.begin_date,ur.end_date, ur.rid, rm.receive_name
            FROM user_receive ur, receive_master rm
            WHERE ur.uid='{$userid}' and ur.rid=rm.rid ORDER BY ur.begin_date ASC
        ";
        $res  = array();
        $rows = getResult($query, $res);
        for ($r=0; $r<$rows; $r++) {
            $begin_date   = $res[$r]['begin_date'];
            $end_date     = $res[$r]['end_date'];
            $receive_name = $res[$r]['receive_name'];
            $rid          = $res[$r]['rid'];
            echo("<form method='post' action='del_userreceive.php'><tr><td><br></td>\n");
            echo("<td width='50%'>" . $begin_date . "〜" . $end_date . "</td>\n");
            echo("<td width='50%'>" . $receive_name . "</td>\n");
            echo("<td width='20%'><input type='submit' value='削除'></td></tr>\n");
            echo("<tr><td><input type='hidden' name='userid' value='" . $userid . "'></td>\n");
            echo "<td><input type='hidden' name='rid' value='{$rid}'></td></tr>\n";
            echo "<td><input type='hidden' name='begin_date' value='{$begin_date}'></td></tr>\n";
            echo "<td><input type='hidden' name='end_date' value='{$end_date}'></td></tr>\n";
            echo("</form>\n");
        }
?>
        </table>
    </td>
    </tr>
</table>
<?php
    } elseif ( isset($capa) ) {
?>
<table width="100%">
    <tr><td colspan=2 width="100%" bgcolor="#ff6600" align="center">
        <font color="#ffffff">ユーザーの資格変更</font></td>
    </tr>

    <form method="post" action="add_usercapacity.php">
    <tr><td colspan=2 width="100%" valign="top">
        <table width="100%">
            <tr><td width="15%">社員No</td>
                <td><font><?php echo($userid); ?></font></td>
            </tr>
            <tr><td width="15%">名前</td>
                <td><font size=1><?php echo($kana); ?></font><br><?php echo($name); ?></td>
            </tr>
            <tr><td width="15%">所属</td>
                <td><font><?php echo($section_name); ?></font></td>
            </tr>
            <tr><td colspan=2><hr></td></tr>
        </table></td>
    </tr>
    
    <tr>
    <td colspan=1 width="40%" valign="top">
        <table width="100%">    
            <tr><td>新しい資格名を選択してください。</td></tr>
            <tr><td><select name="capacity">
<?php
        $query="select * from capacity_master order by cid asc";
        $res=array();
        if($rows=getResult($query,$res)){
            for($i=0;$i<$rows;$i++)
                echo("<option value=" . $res[$i][cid] . ">" . $res[$i][capacity_name] . "\n");
    }
?>
            </select></td></tr>

            <tr><td>取得日を入力してください。</td></tr>
            <tr><td><input type="text" name="acq_date_1" size=4 maxlength=4 value=<?php echo(substr(date("Y-m-d"),0,4)); ?>><font size=+1> - </font><input type="text" name="acq_date_2"size=2 maxlength=2 value=<?php echo(substr(date("Y-m-d"),5,2)); ?>><font size=+1> - </font><input type="text" name="acq_date_3" size=2 maxlength=2 value=<?php echo(substr(date("Y-m-d"),8,2)); ?>></td></tr>
            <tr><td align="right">
                <input type="submit" value="追加">
        <input type="hidden" name="userid" value="<?php echo($userid); ?>">
    </form>
            <form method="post" action="emp_menu.php?func=<?php echo(FUNC_CHGUSERINFO); ?>">
                <p><input type="submit" name="inf" value="戻る"></p>
                <input type="hidden" name="userid" value="<?php echo($userid); ?>">
                <input type="hidden" name="name" value="<?php echo($name); ?>">
                <input type="hidden" name="section_name" value="<?php echo($section_name); ?>">
            </form>
            </td><td><br></td></tr>
        </table>
    </td>

    <td colspan=1 width="60%" valign="top">
        <table width="100%">
            <tr><td colspan=3>取得資格</td></tr>
<?php
        $query = "
            SELECT uc.acq_date, uc.cid, cm.capacity_name FROM user_capacity uc, capacity_master cm
            WHERE uc.uid='{$userid}' and uc.cid=cm.cid ORDER BY uc.acq_date ASC
        ";
        $res  = array();
        $rows = getResult($query, $res);
        for ($r=0; $r<$rows; $r++) {
            $acq_date      = $res[$r]['acq_date'];
            $capacity_name = $res[$r]['capacity_name'];
            $cid           = $res[$r]['cid'];
            echo "<form method='post' action='del_usercapacity.php'><tr><td><br></td>\n";
            echo "<td width='50%'>{$acq_date}</td>\n";
            echo "<td width='50%'>{$capacity_name}</td>\n";
            echo "<td width='20%'><input type='submit' value='削除'></td></tr>\n";
            echo "<tr><td><input type='hidden' name='userid' value='{$userid}'></td>\n";
            echo "<td><input type='hidden' name='cid' value='{$cid}'></td></tr>\n";
            echo "<td><input type='hidden' name='acq_date' value='{$acq_date}'></td></tr>\n";
            echo "</form>\n";
        }
?>
        </table>
    </td>
    </tr>
</table>
<?php
    } elseif ( isset($retire) ) {
?>
<table width="100%">
    <tr><td colspan=2 width="100%" bgcolor="#ff6600" align="center">
        <font color="#ffffff">ユーザーの退職情報</font></td>
    </tr>

    <form method="post" action="add_userretire.php">
    <tr><td colspan=2 width="100%" valign="top">
        <table width="100%">
            <tr><td width="15%">社員No</td>
                <td><font><?php echo($userid); ?></font></td>
            </tr>
            <tr><td width="15%">名前</td>
                <td><font size=1><?php echo($kana); ?></font><br><?php echo($name); ?></td>
            </tr>
            <tr><td width="15%">所属</td>
                <td><font><?php echo($section_name); ?></font></td>
            </tr>
            <tr><td colspan=2><hr></td></tr>
        </table></td>
    </tr>
    
    <tr>
    <td colspan=1 width="40%" valign="top">
        <table> 
            <tr><td>退職日を入力してください。</td></tr>
            <tr><td><input type="text" name="retire_date_1" size=4 maxlength=4 value=<?php echo(substr(date("Y-m-d"),0,4)); ?>><font size=+1> - </font><input type="text" name="retire_date_2" size=2 maxlength=2 value=<?php echo(substr(date("Y-m-d"),5,2)); ?>><font size=+1> - </font><input type="text" name="retire_date_3" size=2 maxlength=2 value=<?php echo(substr(date("Y-m-d"),8,2)); ?>></td></tr>
            <tr><td>退職理由を入力してください。</td></tr>
            <tr><td><input type="text" name="retire_info" size=64 maxlength=64></td></tr>
            <tr><td align="right">
                <input type="submit" value="登録">
        <input type="hidden" name="userid" value="<?php echo($userid); ?>">
    </form>
            <form method="post" action="emp_menu.php?func=<?php echo(FUNC_CHGUSERINFO); ?>">
                <p><input type="submit" name="inf" value="戻る"></p>
                <input type="hidden" name="userid" value="<?php echo($userid); ?>">
                <input type="hidden" name="name" value="<?php echo($name); ?>">
                <input type="hidden" name="section_name" value="<?php echo($section_name); ?>">
            </form>
            </td><td><br></td></tr>
        </table>

    </td>
    <td colspan=2 width="60%" valign="top">
    <table width="100%">
            <tr><td colspan=3></td></tr>
<?php
            $query="select retire_info,retire_date from user_detailes where uid='" . $userid . "'";
            $res=array();
            $rows=getResult($query,$res);
            $retire_info=$res[0][retire_info];
            $retire_date=$res[0][retire_date];
            echo("<form method='post' action='del_userretire.php'><tr><td><br></td>\n");
            echo("<td width='50%'>" . $retire_date . "</td>\n");
            echo("<td width='50%'>" . $retire_info . "</td>\n");
            echo("<td width='20%'><input type='submit' value='取り消し'></td></tr>\n");
            echo("<tr><td><input type='hidden' name='userid' value='" . $userid . "'></td>\n");
?>
            </form>
        </table>
    </td>
    </tr>
</table>
<?php
    } else {
        $_SESSION['s_sysmsg'] = "不正な画面呼出が発生しました。<br>ユーザー認証が行われていない可能性があります。";
        header("Location: http:" . WEB_HOST . "index.php");
        exit();
    }
?>
