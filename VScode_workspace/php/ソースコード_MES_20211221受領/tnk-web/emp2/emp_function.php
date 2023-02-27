<?php
//////////////////////////////////////////////////////////////////////////////////
// TNK Web System 社員情報管理 専用 関数をまとめたもの                          //
// function.php                                                                 //
// Copyright (C) 2001-2015 K.Kobayashi all rights reserved.                     //
//                                      2001/10/15  tnksys@nitto-kohki.co.jp    //
// Changed history                                                              //
// 2001/10/15 Created   emp_function.php                                        //
// 2002/04/23 BEGIN: already a transaction in progress のメッセージ対策のため   //
//            for 文の後にあった execQuery('BEGIN') を 前で実行するように変更   //
//            function addReceiveentry($uid,$receive,$begin_date,$end_date)     //
// 2002/08/21 delUser() function の ローカルユーザーを削除する部分をコメント    //
//              変わりに /temp/ユーザー.gif の写真を削除しようと思ったが?       //
// 2002/08/22 addTransfer() の配列 [sid] → ['sid'] に訂正                      //
//            delTransfer() の配列 [sid] → ['sid'] に訂正                      //
//            authorityUser() 配列 [aid] → ['aid'] に訂正                      //
// 2002/09/03 authorityUser() ユーザー認証で退職者を除外するように変更          //
// 2003/04/02 getRowdata($rows-1,$res); 移動日が同じ日の場合に最終の所属にする  //
// 2003/04/21 authorityUser() にテスト用の認証ロジック追加と一部訂正            //
//            pg_lounlink($con,$oid) を一時的にコメントアウトする必要があった   //
// 2003/06/28 pg_lounlink()→pg_lo_unlink()へ変更4.2.0で名前の変更があった？    //
//             現在はラージオブジェクトを使用していないのでコメントにする。     //
// 2003/07/03 CLI版とFunctionを共有するためrequire_once()を絶対指定に変更       //
// 2003/10/22 addselect Section/Receive/Capacity/Position に登録済みのチェック  //
//                      所属    教育    資格     職位                           //
//            及び blankチェックを追加                                          //
//            教育だけ既に登録がある時は UPDATE するように変更 ミス入力の訂正   //
// 2003/11/11 getObject()の pg_lo_export($oid,$file,$con);をコメントアウト      //
// 2003/12/05 所属も既に登録がある時は未使用のチェック後UPDATEするように変更    //
// 2003/12/05 getObjectAdd()を追加 view_userinfo_user等でコメントのため         //
// 2003/12/19 function.php から 社員メニュー専用のものを こちらに移動           //
// 2005/07/21 教育の一括登録で途中の社員番号の抜けに対応 教育名のロジック訂正   //
// 2006/01/11 delTransfer() の oid → trans_date へ変更                         //
// 2006/02/13 delReceive() の oid → rid, begin_date, end_date へ変更           //
// 2006/02/14 delCapacity() の oid → acq_date, cid へ変更                      //
//            chg_Sectionname() の oid → trans_date, sid へ変更                //
// 2007/02/09 addCapaentry() 途中の社員番号の抜けに対応                    大谷 //
// 2007/10/04 表示項目の有効･無効の設定関数の$rows = count($array);をコメント   //
// 2015/06/19 計画有給の登録addHolyday()を追加                             大谷 //
//////////////////////////////////////////////////////////////////////////////////

// require_once ('/home/www/html/tnk-web/pgsql.php');
require_once ('/home/www/html/tnk-web/emp/emp_define.php');
/////// @@@@@@@@@@ 必須 条件 前もって function.php(共用)をrequire_once しておく事 @@@@@@@@@@@@@
define('CMD_STR_DEL', '/usr/bin/sudo /usr/sbin/userdel ');


/* 重複のないドメイン内メールアドレスの生成 */
function makeMailAddress($spell)
{
    $len  = strlen($spell);
    $fast = "";
    $last = "";
    $sp   = 0;
    for ($i=0; $i<$len; $i++) {
        $char = substr($spell, $i, 1);
        if ($char == " ") {
            $sp = 1;
        } else {
            if ($sp) {
                $last .=$char;
            } else {
                $fast .=$char;
            }
        }
    }

    $len = strlen($fast);
    for ($i=0; $i<$len; $i++) {
        $addr  = sprintf("%s_%s@%s",substr($fast,0,$i+1),$last,WEB_DOMAIN);
        $query = "SELECT * FROM user_master WHERE mailaddr='$addr'";
        $array = array();
        if ( !getResult($query, $array) ) {
            return $addr;
        }
    }
    return "";
}

/* 重複のないパスワードの生成 */
function makePassword()
{
    for ($ret=0; $ret<10; $ret++) {
        $pwd = "";
        srand(time());
        
        $str = "abcdefghijklmnopqrstuvwxyz";
        for ($i=0; $i<2; $i++) {
            $pwd .=substr($str,(rand() % (strlen($str))),1);
        }
        $str = "1234567890";
        for ($i=0; $i<4; $i++) {
            $pwd .=substr($str,(rand() % (strlen($str))),1);
        }
        $query = "SELECT * FROM user_master WHERE passwd='$pwd'";
        $array = array();
        if ( !getResult($query,$array) ) {
            return $pwd;
        }
    }
    return "";
}

/* ユーザーの削除(トランザクション処理) */
function delUser($userid, $oid, $user)
{
    $con = funcConnect();
    if ($con) {
        execQuery('BEGIN');
        /**************
        if ($oid) {
            pg_lo_unlink($con, $oid);
        }
        ***************/
        if (execQuery("DELETE FROM user_detailes WHERE uid='$userid'") >= 0) {
            if (execQuery("DELETE FROM user_transfer WHERE uid='$userid'") >= 0) {
                if (execQuery("DELETE FROM user_receive WHERE uid='$userid'") >=0 ) {
                    if (execQuery("DELETE FROM user_capacity WHERE uid='$userid'") >=0 ) {
                        if (execQuery("DELETE FROM user_master WHERE uid='$userid'") >=0 ) {
                            execQuery('COMMIT');
                            disConnectDB();
/* add 09/27 begin */
//                          $cmd = escapeshellcmd(CMD_STR_DEL . $user);
//                          exec($cmd);
/* end */
                            return true;
                        }
                    }
                }
            }
        }
        execQuery('ROLLBACK');
        disConnectDB();
    }
    return false;
}

/* 移動の情報修正 */
function addTransfer($userid, $section, $trans_date, $section_name)
{
    $con = funcConnect();
    if ($con) {
        execQuery('BEGIN');
        $query="INSERT INTO user_transfer VALUES('$userid','$trans_date',$section,'$section_name')";
        if (execQuery($query) >= 0) {
            $query="SELECT sid FROM user_transfer" . 
                " WHERE uid='$userid' and trans_date=(SELECT max(trans_date) FROM user_transfer WHERE uid='$userid')";
            if (($rows=execQuery($query)) >= 1) {
//            if (execQuery($query)){
                $res=array();
                getRowdata($rows-1,$res);       // 移動日が同じ日の場合に最終の所属にする
//                getRowdata(0,$res);
                $query="update user_detailes set sid=" . $res['sid'] . " WHERE uid='$userid'";
                if (execQuery($query) >= 0) {
                    execQuery('COMMIT');
                    disConnectDB();
                    return true;
                }
            }
        }
        execQuery('ROLLBACK');
        disConnectDB();
    }
    return false;
}

// function delTransfer($userid,$oid,$sid){
function delTransfer($userid, $trans_date, $sid)
{
    $con = funcConnect();
    if ($con) {
        execQuery('begin');
        $query = "DELETE FROM user_transfer WHERE uid='$userid' AND trans_date='$trans_date' AND sid=$sid";
        if (execQuery($query)>=0) {
            $query = "SELECT sid FROM user_transfer" . 
                " WHERE uid='$userid' and trans_date=(SELECT max(trans_date) FROM user_transfer WHERE uid='$userid')";
            if (execQuery($query)) {
                $res = array();
                getRowdata(0, $res);
                $query = "update user_detailes set sid=" . $res['sid'] . " WHERE uid='$userid'";
                if (execQuery($query)>=0) {
                    execQuery('commit');
                    disConnectDB();
                    return true;
                }
            }
        }
        execQuery('rollback');
        disConnectDB();
    }
    return false;
}

/* 所属名の変更 */
///// $oid → $trans_date, sid へ変更 2006/02/14
function chg_Sectionname($userid, $trans_date, $sid, $section_name)
{
    $con = funcConnect();
    if ($con) {
        execQuery('begin');
        $query = "
            UPDATE user_transfer SET section_name='{$section_name}'
            WHERE uid='{$userid}' AND trans_date='{$trans_date}' AND sid={$sid}
        ";
        if (execQuery($query) >= 0) {
            execQuery('commit');
            disConnectDB();
            return true;
        }
        execQuery('rollback');
        disConnectDB();
    }
    return false;
}

/* 教育の情報修正 */
function addReceive($userid, $receive, $begin_date, $end_date)
{
    $con = funcConnect();
    if ($con) {
        execQuery('BEGIN');
        $query="INSERT INTO user_receive VALUES('$userid', '$begin_date', '$end_date', $receive)";
        if (execQuery($query) >= 0) {
            execQuery('COMMIT');
            disConnectDB();
            return true;
        }
        execQuery('ROLLBACK');
        disConnectDB();
    }
    return false;
}

///// 教育履歴の削除で引数を oid → rid, begin_date, end_date へ変更
function delReceive($userid, $rid, $begin_date, $end_date)
{
    $con = funcConnect();
    if ($con) {
        execQuery('begin');
        $query = "DELETE FROM user_receive WHERE uid='$userid' and rid={$rid} and begin_date='{$begin_date}' and end_date='{$end_date}'";
        if (execQuery($query) >= 0) {
            execQuery('commit');
            disConnectDB();
            return true;
        }
        execQuery('rollback');
        disConnectDB();
    }
    return false;
}

/* 資格の情報修正 */
function addCapacity($userid, $capacity, $acq_date)
{
    $con = funcConnect();
    if ($con) {
        execQuery('BEGIN');
        $query = "INSERT INTO user_capacity VALUES('$userid', '$acq_date', $capacity)";
        if (execQuery($query) >= 0) {
            execQuery('COMMIT');
            disConnectDB();
            return true;
        }
        execQuery('ROLLBACK');
        disConnectDB();
    }
    return false;
}

///// $oid → $acq_date, $cid へ変更 2006/02/14
function delCapacity($userid, $acq_date, $cid)
{
    $con = funcConnect();
    if ($con) {
        execQuery('begin');
        $query = "DELETE FROM user_capacity WHERE uid='$userid' and acq_date='{$acq_date}' and cid=$cid";
        if (execQuery($query) >= 0) {
            execQuery('commit');
            disConnectDB();
            return true;
        }
        execQuery('rollback');
        disConnectDB();
    }
    return false;
}

/* 退職の情報修正 */
function addRetire($userid, $retire_info, $retire_date)
{
    $con = funcConnect();
    if ($con) {
        execQuery('BEGIN');
        $query = "UPDATE user_detailes SET retire_info='$retire_info', retire_date='$retire_date' WHERE uid='$userid'";
        if (execQuery($query) >= 0) {
            execQuery('COMMIT');
            disConnectDB();
            return true;
        }
        execQuery('ROLLBACK');
        disConnectDB();
    }
    return false;
}

function delRetire($userid)
{
    $con = funcConnect();
    if ($con) {
        execQuery('BEGIN');
        $query = "UPDATE user_detailes SET retire_info=NULL, retire_date=NULL WHERE uid='$userid'";
        if (execQuery($query) >= 0) {
            execQuery('COMMIT');
            disConnectDB();
            return true;
        }
        execQuery('ROLLBACK');
        disConnectDB();
    }
    return false;
}

function addObject($file)
{
    $oid = 0;
    $con = funcConnect();
    if ($con) {
        execQuery('begin');

        $oid  = pg_lo_create($con);
        $lobj = pg_lo_open($con, $oid, 'w');
        $fd   = fopen($file, 'r');
        $img  = fread($fd, filesize($file) );
        pg_lo_write($lobj, $img);
        fclose($fd);
        pg_lo_close($lobj);
        
        execQuery('commit');
        disConnectDB();
    }
    return $oid;
}

function delObject($oid)
{
    $con = funcConnect();
    if ($con) {
        execQuery('begin');
        /***********
        pg_lo_unlink($con, $oid);
        ***********/
        execQuery('commit');
        disConnectDB();
    }
    return;
}

function getObject($oid, $file)
{
    $con = funcConnect();
    if ($con) {
        execQuery('begin');
        /***********
        pg_lo_export($oid, $file, $con);
        ***********/
        execQuery('commit');
        disConnectDB();
    }
}

/******* 2003/12/05 追加 ******/
function getObjectAdd($oid, $file)
{
    $con = funcConnect();
    if ($con) {
        execQuery('begin');
        pg_lo_export($oid, $file, $con);
        execQuery('commit');
        disConnectDB();
    }
}

/* 所属の有効・無効設定 */
function indSection($sid, $sflg)
{
    $con = funcConnect();
    // $rows = count($array); 何のためにあるのか意味不明(過去の履歴を忘れた)
    if ($con) {
        execQuery('BEGIN');
        if ($sflg == '1') {
            $flg = 0;
        } else {
            $flg = 1;
        }
        $query = "UPDATE section_master SET sflg=$flg WHERE sid=$sid";
        if (execQuery($query) >= 0) {
            execQuery('COMMIT');
            disConnectDB();
            return true;
        }
        execQuery('ROLLBACK');
        disConnectDB();
    }
    return false;
}

/* 所属の項目追加・変更 */
function addselectSection($sid, $section_name, $sflg)
{
    if ( ($sid == "") || ($section_name == "") ) {  // blank入力チェック
        $_SESSION['s_sysmsg'] = '所属のコード又は名称が未入力です。';
        return false;
    }
    $con = funcConnect();
    if ($con) {
        execQuery('begin');
        $query = "SELECT sid FROM section_master WHERE sid=$sid";
        if (getUniResTrs($con, $query, $res) <= 0) {    // 登録済みのチェック 2003/10/22
            $query = "INSERT INTO section_master VALUES($sid, '$section_name', $sflg)";
            if (execQuery($query) >= 0) {
                execQuery('COMMIT');
                disConnectDB();
                return true;
            }
        } else {    // 登録済みの場合は UPDATE を実行 但し現在使われている所属は変更できない 2003/12/05
            $query = "SELECT uid, trim(name) FROM section_master as sec left outer join user_detailes as det using(sid) WHERE det.sid=$sid";
            $res_user = array();
            if ( ($rows=getResultTrs($con, $query, $res_user)) <= 0) {   // 既に個人に登録されているかチェック
                ///// UPDATEの場合オリジナルのsflgはそのまま
                $query = "update section_master set sid=$sid, section_name='$section_name' WHERE sid=$sid";
                if (execQuery($query) >= 0) {
                    execQuery('commit');
                    disConnectDB();
                    $_SESSION['s_sysmsg'] = "<font color='yellow'>所属名を変更しました。<br>$res → $section_name </font>";
                    return true;
                }
            } else {
                $_SESSION['s_sysmsg'] = '既に登録されています。<br>';
                for ($i=0; $i<$rows; $i++) {
                    $_SESSION['s_sysmsg'] .= "{$res_user[$i][0]} : {$res_user[$i][1]}<br>";
                }
            }
        }
        execQuery('rollback');
        disConnectDB();
    }
    return false;
}

/* 教育の有効・無効設定 */
function indReceive($rid, $rflg)
{
    $con = funcConnect();
    // $rows = count($array); 何のためにあるのか意味不明(過去の履歴を忘れた)
    if ($con) {
        execQuery('BEGIN');
        if ($rflg == '1') {
            $flg = 0;
        } else {
            $flg = 1;
        }
        $query = "UPDATE receive_master SET rflg=$flg WHERE rid=$rid";
        if (execQuery($query) >= 0) {
            execQuery('COMMIT');
            disConnectDB();
            return true;
        }
        execQuery('ROLLBACK');
        disConnectDB();
    }
    return false;
}

/* 教育の項目追加 */
function addselectReceive($rid, $receive_name, $rflg)
{
    if ( ($rid == "") || ($receive_name == "") ) {  // blank入力チェック
        $_SESSION['s_sysmsg'] = '教育のコード又は名称が未入力です。';
        return false;
    }
    $con = funcConnect();
    if ($con) {
        execQuery('begin');
        $query = "SELECT trim(receive_name) FROM receive_master WHERE rid=$rid";
        if (getUniResTrs($con, $query, $res) <= 0) {    // 登録済みのチェック 2003/10/22
            ///// 教育名の登録済みチェック 2005/07/21
            $query = "SELECT receive_name FROM receive_master WHERE rid != $rid and receive_name='{$receive_name}'";
            if (getUniResTrs($con, $query, $res) <= 0) {
                $query = "INSERT INTO receive_master VALUES($rid, '$receive_name', $rflg)";
                if (execQuery($query) >= 0) {
                    execQuery('commit');
                    disConnectDB();
                    return true;
                }
            } else {
                $_SESSION['s_sysmsg'] = "この教育名: [{$receive_name}] は既に登録されています。<br>";
            }
        } else {    // 登録済みの場合は UPDATE を実行 教育のみ 2003/10/22
            $query = "SELECT uid, trim(name) FROM user_receive left outer join user_detailes using(uid) WHERE rid=$rid";
            $res_user = array();
            if ( ($rows=getResultTrs($con, $query, $res_user)) <= 0) {   // 既に個人に登録されているかチェック
                ///// 教育名の登録済みチェック 2005/07/21
                $query = "SELECT receive_name FROM receive_master WHERE rid != $rid and receive_name='{$receive_name}'";
                if (getUniResTrs($con, $query, $res) <= 0) {
                    ///// UPDATEの場合オリジナルのrflgはそのまま
                    $query = "update receive_master set rid=$rid, receive_name='$receive_name' WHERE rid=$rid";
                    if (execQuery($query) >= 0) {
                        execQuery('commit');
                        disConnectDB();
                        $_SESSION['s_sysmsg'] = "<font color='yellow'>教育名を変更しました。<br>$res → $receive_name </font>";
                        return true;
                    }
                } else {
                    $_SESSION['s_sysmsg'] = "この教育名: [{$receive_name}] は既に登録されています。<br>";
                }
            } else {
                $_SESSION['s_sysmsg'] = 'この教育コードは既に使われています。<br>';
                for ($i=0; $i<$rows; $i++) {
                    $_SESSION['s_sysmsg'] .= "{$res_user[$i][0]} : {$res_user[$i][1]}<br>";
                }
            }
        }
        execQuery('rollback');
        disConnectDB();
    }
    return false;
}

/* 資格の有効・無効設定 */
function indCapacity($cid, $cflg)
{
    $con = funcConnect();
    // $rows = count($array); 何のためにあるのか意味不明(過去の履歴を忘れた)
    if ($con) {
        execQuery('BEGIN');
        if ($cflg == '1') {
            $flg = 0;
        } else {
            $flg = 1;
        }
        $query = "UPDATE capacity_master SET cflg=$flg WHERE cid=$cid";
        if (execQuery($query) >= 0) {
            execQuery('COMMIT');
            disConnectDB();
            return true;
        }
        execQuery('ROLLBACK');
        disConnectDB();
    }
    return false;
}

/* 資格の項目追加 */
function addselectCapacity($cid, $capacity_name, $cflg)
{
    if ( ($cid == "") || ($capacity_name == "") ) {  // blank入力チェック
        $_SESSION['s_sysmsg'] = '資格のコード又は名称が未入力です。';
        return false;
    }
    $con = funcConnect();
    if ($con) {
        execQuery('BEGIN');
        $query = "SELECT cid FROM capacity_master WHERE cid=$cid";
        if (getUniResTrs($con, $query, $res) <= 0) {    // 登録済みのチェック 2003/10/22
            $query = "INSERT INTO capacity_master VALUES($cid, '$capacity_name', $cflg)";
            if (execQuery($query) >= 0) {
                execQuery('COMMIT');
                disConnectDB();
                return true;
            }
        }
        execQuery('ROLLBACK');
        disConnectDB();
    }
    return false;
}

/* 職位の有効・無効設定 */
function indPosition($pid, $pflg)
{
    $con = funcConnect();
    // $rows = count($array); 何のためにあるのか意味不明(過去の履歴を忘れた)
    if ($con) {
        execQuery('BEGIN');
        if ($pflg == '1') {
            $flg = 0;
        } else {
            $flg = 1;
        }
        $query = "UPDATE position_master SET pflg=$flg WHERE pid=$pid";
        if (execQuery($query) >= 0) {
            execQuery('COMMIT');
            disConnectDB();
            return true;
        }
        execQuery('ROLLBACK');
        disConnectDB();
    }
    return false;
}

/* 職位の項目追加 */
function addselectPosition($pid,$position_name,$pflg)
{
    if ( ($pid == "") || ($position_name == "") ) {  // blank入力チェック
        $_SESSION['s_sysmsg'] = '職位のコード又は名称が未入力です。';
        return false;
    }
    $con = funcConnect();
    if ($con) {
        execQuery('BEGIN');
        $query = "SELECT pid FROM position_master WHERE pid=$pid";
        if (getUniResTrs($con, $query, $res) <= 0) {    // 登録済みのチェック 2003/10/22
            $query = "INSERT INTO position_master VALUES($pid, '$position_name', $pflg)";
            if (execQuery($query) >= 0) {
                execQuery('COMMIT');
                disConnectDB();
                return true;
            }
        }
        execQuery('ROLLBACK');
        disConnectDB();
    }
    return false;
}

/* 資格一括登録 */
function addCapaentry($uid, $acq_date, $capacity)
{
    $con = funcConnect();
    $rows = count($uid);
    $query = array();
    if (!$con){ return false; }
    execQuery('BEGIN');     // 2002/04/23 以下と同様の理由
    for ($r=0; $r<$rows; $r++) {
        if (!$uid[$r]) continue;    // 2007/02/09 途中の社員番号の抜けに対応
        $query[$r]="INSERT INTO user_capacity VALUES('$uid[$r]','$acq_date',$capacity)";
        if (execQuery($query[$r])<0){
            execQuery('ROLLBACK');
            disConnectDB();
            return false;
        }
    }
    execQuery('COMMIT');
    disConnectDB();
    return true;
}

/* 教育一括登録 */
function addReceiveentry($uid, $receive, $begin_date, $end_date)
{
    $con   = funcConnect();
    $rows  = count($uid);
    $query = array();
    if (!$con){ return false; }
    execQuery('begin');     // 2002/04/23 BEGIN: already a transaction in progress のメッセージ対策のため
    for ($r=0; $r<$rows; $r++) {    // for 文の後にあった execQuery('begin') を 前で実行するように変更
        if (!$uid[$r]) continue;    // 2005/07/21 途中の社員番号の抜けに対応
        $query[$r] = "INSERT INTO user_receive VALUES('{$uid[$r]}', '{$begin_date}', '{$end_date}', {$receive})";
        if (execQuery($query[$r]) < 0) {
            execQuery('rollback');
            disConnectDB();
            return false;
        }
    }
    execQuery('commit');
    disConnectDB();
    return true;
}

/* 計画有給登録 */
function addHolyday($uid, $acq_date)
{
    $con = funcConnect();
    $rows = count($uid);
    $query = array();
    if (!$con){ return false; }
    execQuery('BEGIN');     // 2002/04/23 以下と同様の理由
    for ($r=0; $r<$rows; $r++) {
        if (!$uid[$r]) continue;    // 2007/02/09 途中の社員番号の抜けに対応
        $query[$r]="SELECT uid FROM user_holyday WHERE uid='$uid[$r]' and acq_date='$acq_date'";
        if (getUniResTrs($con, $query[$r], $res) <= 0) {    // 登録済みのチェック 2003/10/22
            $query[$r]="INSERT INTO user_holyday VALUES('$uid[$r]','$acq_date')";
            if (execQuery($query[$r])<0){
                execQuery('ROLLBACK');
                disConnectDB();
                return false;
            }
        }
    }
    execQuery('COMMIT');
    disConnectDB();
    return true;
}
