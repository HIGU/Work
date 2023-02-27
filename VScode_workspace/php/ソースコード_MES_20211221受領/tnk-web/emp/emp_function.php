<?php
//////////////////////////////////////////////////////////////////////////////////
// TNK Web System �Ұ�������� ���� �ؿ���ޤȤ᤿���                          //
// function.php                                                                 //
// Copyright (C) 2001-2015 K.Kobayashi all rights reserved.                     //
//                                      2001/10/15  tnksys@nitto-kohki.co.jp    //
// Changed history                                                              //
// 2001/10/15 Created   emp_function.php                                        //
// 2002/04/23 BEGIN: already a transaction in progress �Υ�å������к��Τ���   //
//            for ʸ�θ�ˤ��ä� execQuery('BEGIN') �� ���Ǽ¹Ԥ���褦���ѹ�   //
//            function addReceiveentry($uid,$receive,$begin_date,$end_date)     //
// 2002/08/21 delUser() function �� ������桼��������������ʬ�򥳥���    //
//              �Ѥ��� /temp/�桼����.gif �μ̿��������褦�Ȼפä���?       //
// 2002/08/22 addTransfer() ������ [sid] �� ['sid'] ������                      //
//            delTransfer() ������ [sid] �� ['sid'] ������                      //
//            authorityUser() ���� [aid] �� ['aid'] ������                      //
// 2002/09/03 authorityUser() �桼����ǧ�ڤ��࿦�Ԥ��������褦���ѹ�          //
// 2003/04/02 getRowdata($rows-1,$res); ��ư����Ʊ�����ξ��˺ǽ��ν�°�ˤ���  //
// 2003/04/21 authorityUser() �˥ƥ����Ѥ�ǧ�ڥ��å��ɲäȰ�������            //
//            pg_lounlink($con,$oid) ����Ū�˥����ȥ����Ȥ���ɬ�פ����ä�   //
// 2003/06/28 pg_lounlink()��pg_lo_unlink()���ѹ�4.2.0��̾�����ѹ������ä���    //
//             ���ߤϥ顼�����֥������Ȥ���Ѥ��Ƥ��ʤ��Τǥ����Ȥˤ��롣     //
// 2003/07/03 CLI�Ǥ�Function��ͭ���뤿��require_once()�����л�����ѹ�       //
// 2003/10/22 addselect Section/Receive/Capacity/Position ����Ͽ�ѤߤΥ����å�  //
//                      ��°    ����    ���     ����                           //
//            �ڤ� blank�����å����ɲ�                                          //
//            �������������Ͽ��������� UPDATE ����褦���ѹ� �ߥ����Ϥ�����   //
// 2003/11/11 getObject()�� pg_lo_export($oid,$file,$con);�򥳥��ȥ�����      //
// 2003/12/05 ��°�������Ͽ���������̤���ѤΥ����å���UPDATE����褦���ѹ�    //
// 2003/12/05 getObjectAdd()���ɲ� view_userinfo_user���ǥ����ȤΤ���         //
// 2003/12/19 function.php ���� �Ұ���˥塼���ѤΤ�Τ� ������˰�ư           //
// 2005/07/21 ����ΰ����Ͽ������μҰ��ֹ��ȴ�����б� ����̾�Υ��å�����   //
// 2006/01/11 delTransfer() �� oid �� trans_date ���ѹ�                         //
// 2006/02/13 delReceive() �� oid �� rid, begin_date, end_date ���ѹ�           //
// 2006/02/14 delCapacity() �� oid �� acq_date, cid ���ѹ�                      //
//            chg_Sectionname() �� oid �� trans_date, sid ���ѹ�                //
// 2007/02/09 addCapaentry() ����μҰ��ֹ��ȴ�����б�                    ��ë //
// 2007/10/04 ɽ�����ܤ�ͭ����̵��������ؿ���$rows = count($array);�򥳥���   //
// 2015/06/19 �ײ�ͭ�����ϿaddHolyday()���ɲ�                             ��ë //
//////////////////////////////////////////////////////////////////////////////////

// require_once ('/home/www/html/tnk-web/pgsql.php');
require_once ('/home/www/html/tnk-web/emp/emp_define.php');
/////// @@@@@@@@@@ ɬ�� ��� ����ä� function.php(����)��require_once ���Ƥ����� @@@@@@@@@@@@@
define('CMD_STR_DEL', '/usr/bin/sudo /usr/sbin/userdel ');


/* ��ʣ�Τʤ��ɥᥤ����᡼�륢�ɥ쥹������ */
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

/* ��ʣ�Τʤ��ѥ���ɤ����� */
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

/* �桼�����κ��(�ȥ�󥶥���������) */
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

/* ��ư�ξ����� */
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
                getRowdata($rows-1,$res);       // ��ư����Ʊ�����ξ��˺ǽ��ν�°�ˤ���
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

/* ��°̾���ѹ� */
///// $oid �� $trans_date, sid ���ѹ� 2006/02/14
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

/* ����ξ����� */
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

///// ��������κ���ǰ����� oid �� rid, begin_date, end_date ���ѹ�
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

/* ��ʤξ����� */
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

///// $oid �� $acq_date, $cid ���ѹ� 2006/02/14
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

/* �࿦�ξ����� */
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

/******* 2003/12/05 �ɲ� ******/
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

/* ��°��ͭ����̵������ */
function indSection($sid, $sflg)
{
    $con = funcConnect();
    // $rows = count($array); ���Τ���ˤ���Τ���̣����(���������˺�줿)
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

/* ��°�ι����ɲá��ѹ� */
function addselectSection($sid, $section_name, $sflg)
{
    if ( ($sid == "") || ($section_name == "") ) {  // blank���ϥ����å�
        $_SESSION['s_sysmsg'] = '��°�Υ���������̾�Τ�̤���ϤǤ���';
        return false;
    }
    $con = funcConnect();
    if ($con) {
        execQuery('begin');
        $query = "SELECT sid FROM section_master WHERE sid=$sid";
        if (getUniResTrs($con, $query, $res) <= 0) {    // ��Ͽ�ѤߤΥ����å� 2003/10/22
            $query = "INSERT INTO section_master VALUES($sid, '$section_name', $sflg)";
            if (execQuery($query) >= 0) {
                execQuery('COMMIT');
                disConnectDB();
                return true;
            }
        } else {    // ��Ͽ�Ѥߤξ��� UPDATE ��¹� â�����߻Ȥ��Ƥ����°���ѹ��Ǥ��ʤ� 2003/12/05
            $query = "SELECT uid, trim(name) FROM section_master as sec left outer join user_detailes as det using(sid) WHERE det.sid=$sid";
            $res_user = array();
            if ( ($rows=getResultTrs($con, $query, $res_user)) <= 0) {   // ���˸Ŀͤ���Ͽ����Ƥ��뤫�����å�
                ///// UPDATE�ξ�祪�ꥸ�ʥ��sflg�Ϥ��Τޤ�
                $query = "update section_master set sid=$sid, section_name='$section_name' WHERE sid=$sid";
                if (execQuery($query) >= 0) {
                    execQuery('commit');
                    disConnectDB();
                    $_SESSION['s_sysmsg'] = "<font color='yellow'>��°̾���ѹ����ޤ�����<br>$res �� $section_name </font>";
                    return true;
                }
            } else {
                $_SESSION['s_sysmsg'] = '������Ͽ����Ƥ��ޤ���<br>';
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

/* �����ͭ����̵������ */
function indReceive($rid, $rflg)
{
    $con = funcConnect();
    // $rows = count($array); ���Τ���ˤ���Τ���̣����(���������˺�줿)
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

/* ����ι����ɲ� */
function addselectReceive($rid, $receive_name, $rflg)
{
    if ( ($rid == "") || ($receive_name == "") ) {  // blank���ϥ����å�
        $_SESSION['s_sysmsg'] = '����Υ���������̾�Τ�̤���ϤǤ���';
        return false;
    }
    $con = funcConnect();
    if ($con) {
        execQuery('begin');
        $query = "SELECT trim(receive_name) FROM receive_master WHERE rid=$rid";
        if (getUniResTrs($con, $query, $res) <= 0) {    // ��Ͽ�ѤߤΥ����å� 2003/10/22
            ///// ����̾����Ͽ�Ѥߥ����å� 2005/07/21
            $query = "SELECT receive_name FROM receive_master WHERE rid != $rid and receive_name='{$receive_name}'";
            if (getUniResTrs($con, $query, $res) <= 0) {
                $query = "INSERT INTO receive_master VALUES($rid, '$receive_name', $rflg)";
                if (execQuery($query) >= 0) {
                    execQuery('commit');
                    disConnectDB();
                    return true;
                }
            } else {
                $_SESSION['s_sysmsg'] = "���ζ���̾: [{$receive_name}] �ϴ�����Ͽ����Ƥ��ޤ���<br>";
            }
        } else {    // ��Ͽ�Ѥߤξ��� UPDATE ��¹� ����Τ� 2003/10/22
            $query = "SELECT uid, trim(name) FROM user_receive left outer join user_detailes using(uid) WHERE rid=$rid";
            $res_user = array();
            if ( ($rows=getResultTrs($con, $query, $res_user)) <= 0) {   // ���˸Ŀͤ���Ͽ����Ƥ��뤫�����å�
                ///// ����̾����Ͽ�Ѥߥ����å� 2005/07/21
                $query = "SELECT receive_name FROM receive_master WHERE rid != $rid and receive_name='{$receive_name}'";
                if (getUniResTrs($con, $query, $res) <= 0) {
                    ///// UPDATE�ξ�祪�ꥸ�ʥ��rflg�Ϥ��Τޤ�
                    $query = "update receive_master set rid=$rid, receive_name='$receive_name' WHERE rid=$rid";
                    if (execQuery($query) >= 0) {
                        execQuery('commit');
                        disConnectDB();
                        $_SESSION['s_sysmsg'] = "<font color='yellow'>����̾���ѹ����ޤ�����<br>$res �� $receive_name </font>";
                        return true;
                    }
                } else {
                    $_SESSION['s_sysmsg'] = "���ζ���̾: [{$receive_name}] �ϴ�����Ͽ����Ƥ��ޤ���<br>";
                }
            } else {
                $_SESSION['s_sysmsg'] = '���ζ��饳���ɤϴ��˻Ȥ��Ƥ��ޤ���<br>';
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

/* ��ʤ�ͭ����̵������ */
function indCapacity($cid, $cflg)
{
    $con = funcConnect();
    // $rows = count($array); ���Τ���ˤ���Τ���̣����(���������˺�줿)
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

/* ��ʤι����ɲ� */
function addselectCapacity($cid, $capacity_name, $cflg)
{
    if ( ($cid == "") || ($capacity_name == "") ) {  // blank���ϥ����å�
        $_SESSION['s_sysmsg'] = '��ʤΥ���������̾�Τ�̤���ϤǤ���';
        return false;
    }
    $con = funcConnect();
    if ($con) {
        execQuery('BEGIN');
        $query = "SELECT cid FROM capacity_master WHERE cid=$cid";
        if (getUniResTrs($con, $query, $res) <= 0) {    // ��Ͽ�ѤߤΥ����å� 2003/10/22
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

/* ���̤�ͭ����̵������ */
function indPosition($pid, $pflg)
{
    $con = funcConnect();
    // $rows = count($array); ���Τ���ˤ���Τ���̣����(���������˺�줿)
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

/* ���̤ι����ɲ� */
function addselectPosition($pid,$position_name,$pflg)
{
    if ( ($pid == "") || ($position_name == "") ) {  // blank���ϥ����å�
        $_SESSION['s_sysmsg'] = '���̤Υ���������̾�Τ�̤���ϤǤ���';
        return false;
    }
    $con = funcConnect();
    if ($con) {
        execQuery('BEGIN');
        $query = "SELECT pid FROM position_master WHERE pid=$pid";
        if (getUniResTrs($con, $query, $res) <= 0) {    // ��Ͽ�ѤߤΥ����å� 2003/10/22
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

/* ��ʰ����Ͽ */
function addCapaentry($uid, $acq_date, $capacity)
{
    $con = funcConnect();
    $rows = count($uid);
    $query = array();
    if (!$con){ return false; }
    execQuery('BEGIN');     // 2002/04/23 �ʲ���Ʊ�ͤ���ͳ
    for ($r=0; $r<$rows; $r++) {
        if (!$uid[$r]) continue;    // 2007/02/09 ����μҰ��ֹ��ȴ�����б�
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

/* ��������Ͽ */
function addReceiveentry($uid, $receive, $begin_date, $end_date)
{
    $con   = funcConnect();
    $rows  = count($uid);
    $query = array();
    if (!$con){ return false; }
    execQuery('begin');     // 2002/04/23 BEGIN: already a transaction in progress �Υ�å������к��Τ���
    for ($r=0; $r<$rows; $r++) {    // for ʸ�θ�ˤ��ä� execQuery('begin') �� ���Ǽ¹Ԥ���褦���ѹ�
        if (!$uid[$r]) continue;    // 2005/07/21 ����μҰ��ֹ��ȴ�����б�
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

/* �ײ�ͭ����Ͽ */
function addHolyday($uid, $acq_date)
{
    $con = funcConnect();
    $rows = count($uid);
    $query = array();
    if (!$con){ return false; }
    execQuery('BEGIN');     // 2002/04/23 �ʲ���Ʊ�ͤ���ͳ
    for ($r=0; $r<$rows; $r++) {
        if (!$uid[$r]) continue;    // 2007/02/09 ����μҰ��ֹ��ȴ�����б�
        $query[$r]="SELECT uid FROM user_holyday WHERE uid='$uid[$r]' and acq_date='$acq_date'";
        if (getUniResTrs($con, $query[$r], $res) <= 0) {    // ��Ͽ�ѤߤΥ����å� 2003/10/22
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
