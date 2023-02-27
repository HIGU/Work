<?php
//////////////////////////////////////////////////////////////////////////////////
// TNK Web System ���� �ģ¥��󥿡��ե�����������¾ ���Ѵؿ�                    //
// function.php                                                                 //
// Copyright (C) 2001-2007 Kazuhiro.Kobayashi all rights reserved.              //
//                                      2001/10/15  tnksys@nitto-kohki.co.jp    //
// Changed history                                                              //
// 2001/10/15 Created  function.php                                             //
// 2002/09/02 Tnk Web site access_log function �ɲ�                             //
// 2002/12/09 posgreSQL �Υ쥳���ɹ��� �� �ؿ��ɲ� query_affected               //
// 2002/12/10 query_affected_trans �ȥ�󥶥�������Ǥ��ɲ�                     //
// 2003/02/12 ���ꥯ���꡼�μ¹� getResult2() ���ɲ� ���ꥸ�ʥ��ʪ�����       //
//            ����ǥå������ä��Τǿ��ͥ���ǥå����Τߤˤ��� foreach()�ǻ���  //
// 2003/02/14 Unique Data �ξȲ����ѥ����꡼ getUniResult() ���ɲ�              //
// 2003/04/21 authorityUser() �˥ƥ����Ѥ�ǧ�ڥ��å��ɲäȰ�������            //
// 2003/05/01 getResultWithField()�򥷥��ƥ�����ѤΣģ½������Ѥ��ѹ�          //
// 2003/05/31 getUniResTrs() �ȥ�󥶥��������ǤξȲ���˻��� $connect��    //
// 2003/06/16 getResultWithField2()������ѤȤ����ɲ� php_error �˽���(�̾�)    //
// 2003/06/25 PHP-4.3.3RC1��session_start()������ Notice �б�                 //
// 2003/07/03 CLI�Ǥ�Function��ͭ���뤿��require_once()�����л�����ѹ�       //
// 2003/07/15 getResultWithField3()�����Ѥ�getRowdata2(Ϣ������ʤ�)���ɲ�      //
// 2003/10/22 getResultTrs()�ȥ�󥶥�������Ǥ����(����������˷�̤��Ǽ)    //
// 2003/10/23 getResWithFieldTrs()�ե������̾�ղäΥȥ�󥶥�������Ǥ��ɲ�    //
// 2003/12/19 �Ұ���˥塼�Ѥ� function �� emp_function.php �ذ�ư              //
// 2003/12/19 DB�� access_log �� access_log2 ���ѹ� TIMESTAMP(6)�����          //
// 2004/05/12 menu_OnOff() �ե졼���˥塼��On/Off(ɽ������ɽ��)�ؿ��ɲ�       //
// 2004/06/10 ��˥塼�إå����β���User_ID��̾����ɽ���Ѵؿ��ɲ� view_user()   //
// 2004/07/17 MenuHeader.php ���饹��require�ɲâ��Σ�(���ꤷ�ƤϤ����ʤ�)      //
// 2005/01/17 view_file_name(__FILE__)�δؿ����ɲ� require����include��file̾   //
//            admin �ξ��substr($file, strlen($_SERVER['DOCUMENT_ROOT']), -4)  //
//            getResult()getResult2()��execQuery��Ǥ�error��-1���֤��褦���ѹ� //
// 2005/05/20 db_connect() �� funcConnect() ���ѹ� pgsql.php������Τ���        //
// 2005/07/06 �嵭�򶦤�connectDB()��ƽФ������ꥢ�����롣DAO(pgsql.php)��ͽ�� //
// 2005/07/20 daoPsqlClass ������������� DB�ط���function��DAO�˹�碌��       //
// 2005/11/24 authorityUser()��������ޤ�Ƥ��������ѥѥ���ɤ�Ź沽       //
// 2006/09/28 access_log()�˥��å����γ��ϥ����å����ɲ�(OLD�Ǥ⤢�ä�������) //
// 2006/10/04 getCheckAuthority() ����(����)���¥����å�function���ɲ�          //
// 2006/10/05 �嵭�θ��¥����å����ѹ� getCheckAuthority($division, $id='')     //
// 2007/01/16 getCheckAuthority()��category=4(���¥�٥�)��ǧ�ڤ��ɲ�           //
// 2007/04/23 function uriIndexCheck() ���ɲáʾܤ����ϴؿ�������򻲾ȡ�       //
//////////////////////////////////////////////////////////////////////////////////

require_once ('/home/www/html/tnk-web/pgsql.php');
require_once ('/home/www/html/tnk-web/define.php');
// require_once ('/home/www/html/tnk-web/MenuHeader.php');

/* ��³�ѥ��󥿡��ե����� */
function funcConnect()
{
    return connectDB(DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASSWD);
}

/* DAO(pgsql.php)��³�ѥ��󥿡��ե����� �� funcConnect�ؤΥ��󥿡��ե������� �쥹����ץȤΤ���˻Ĥ� */
/* �Ϥ��Ǥ��ä����ºݤˤϤ��������Ѥ�������ʬ����פ�̾���Τ�����Ȥ�Ʊ���ˤ������ꥢ���Ȥ��� */
function db_connect()
{
    // $conn_str = 'host='.DB_HOST.' port='.DB_PORT.' dbname='.DB_NAME.' user='.DB_USER.' password='.DB_PASSWD;
    // return pg_pConnect($conn_str);   // ��³Ū��³
    // return funcConnect();
    return connectDB(DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASSWD);
}

/* �쥳���ɹ������ѥ����꡼�μ¹�(�����쥳���ɿ����֤�)�ȥ�󥶥�������� */
function query_affected_trans($connect, $query)
{
    if (($res = pg_query($connect, $query)) != FALSE) {  // �����Ǥ�������
        return pg_affected_rows($res);              // return = 0 ��������
    } else {
        return -1;                                  // return = -1 DB ��³����
    }
}

/* ���ꥯ���꡼�μ¹� Unique (�Ȳ����Ѥ�����ͤϰ��) �ȥ�󥶥�������� */
function getUniResTrs($connect, $query, &$result)
{
    if (($resource = pg_query($connect, $query)) !== FALSE) {
        if (($rows = pg_num_rows($resource)) > 0) {      // �쥳���ɤ����뤫
            $result = pg_fetch_result($resource, 0, 0);  // �ǡ������å� row=0����, field=0��
            return 1;   // ��������
        }
        return $rows;   // 0=�����ͤʤ� -1=���顼
    }
    return -2;  // pg_query error
}

/* ���ꥯ���꡼�μ¹� $result �Ͽ��ͥ���ǥå����Τ� �ȥ�󥶥�������� (getUniResTrs�β���) */
function getResultTrs($connect, $query, &$result)
{
    $result = array();      // �����
    if (($resource = pg_query($connect, $query)) !== FALSE) {
        if (($rows = pg_num_rows($resource)) > 0) {         // �쥳���ɤ����뤫
            for ($r=0; $r<$rows; $r++) {
                $result[$r] = pg_fetch_row($resource, $r);
            }
            return $rows;   // ��������(�쥳���ɿ�)
        }
        return $rows;   // 0=�����ͤʤ� -1=���顼
    }
    return -2;  // pg_query error
}

/* ���ꥯ���꡼�μ¹� �ե������̾�������� $result �Ͽ��ͥ���ǥå����Τ� �ȥ�󥶥�������� */
function getResWithFieldTrs($connect, $query, &$field, &$result)
{
    $field = array(); $result = array();    // �����
    if (($resource = pg_query($connect, $query)) !== FALSE) {
        if (($rows = pg_num_rows($resource)) > 0) {         // �쥳���ɤ����뤫
            $fields = pg_num_fields($resource);             // field ���򥻥å� ���顼����-1���֤�
            for ($f=0; $f<$fields; $f++) {
                $field[$f] = pg_field_name($resource, $f);  // �ե������̾����
            }
            for ($r=0; $r<$rows; $r++) {
                $result[$r] = pg_fetch_row($resource, $r);
            }
            return $rows;                           // ��������(�쥳���ɿ�)
        }
        return $rows;   // 0=�����ͤʤ� -1=���顼
    }
    return -2;  // pg_query error
}


/* ���ꥯ���꡼�μ¹� Unique (�Ȳ����Ѥ�����ͤϰ��) */
function getUniResult($query, &$result)
{
    if ($conn = funcConnect()) {                        // ��³Ū��³
        if (($res = pg_query($conn, $query)) !== FALSE) {
            if (($rows = pg_num_rows($res)) > 0) {      // �쥳���ɤ����뤫
                $result = pg_fetch_result($res, 0, 0);  // �ǡ������å� row=0����, field=0��
                return 1;                               // ��������
            }
            return $rows;   // �����ͤʤ� $rows = 0 �� php5.0.4�Ǥ� -1���֤�
        }
        return -2;  // pg_query error
    }
    return -3;  // ��³�Ǥ��ʤ�
}

/* �쥳���ɹ������ѥ����꡼�μ¹�(�����쥳���ɿ����֤�) */
function query_affected($query)
{
    if ( $conn = funcConnect() ) {          // ��³Ū��³
        $res = pg_query($conn, $query);
        return pg_affected_rows($res);      // return = 0 ��������
    } else {
        return -1;                          // return = -1 DB ��³����
    }
}

/* ���ꥯ���꡼�μ¹� ���ͥ���ǥå��� + �ե������̾��Ϣ�ۥ���ǥå�������ť���ǥå��� 2003/02/12�Υ����� */
function getResult($sql, &$result)
{
    if ($conn=funcConnect()) {
        $result = array();      // �����
        if (($resource = pg_query($conn, $sql)) !== FALSE) {
            if (($rows = pg_num_rows($resource)) > 0) {         // �쥳���ɤ����뤫
                for ($i=0; $i<$rows; $i++) {
                    $result[$i] = pg_fetch_array($resource, $i);
                }
            }
            return $rows;   // 0�쥳���ɰʾ������ -1=pg_num_rows error
        }
        return -2;  // pg_query error
    }
    return -3;  // ��³�Ǥ��ʤ�
}

/* ���ꥯ���꡼�μ¹� $result �Ͽ��ͥ���ǥå����Τ� foreach() �ǻ��� 2003/02/12 */
function getResult2($sql, &$result)
{
    if ($conn=funcConnect()) {
        $result = array();      // �����
        if (($resource = pg_query($conn, $sql)) !== FALSE) {
            if (($rows = pg_num_rows($resource)) > 0) {         // �쥳���ɤ����뤫
                for ($r=0; $r<$rows; $r++) {
                    $result[$r] = pg_fetch_row($resource, $r);
                }
                return $rows;   // ��������(�쥳���ɿ�)
            }
            return $rows;   // 0=�����ͤʤ� �� php5.0.4�Ǥ� -1���֤� -1=���顼
        }
        return -2;  // pg_query error
    }
    return -3;              // ��³�Ǥ��ʤ�
}
    /* �ʲ��� getResult2() �λ�����Ǥ� */
/******************************************************************
if (($rows=getResult2($query, $res)) > 0) {
    $r = 0;
    $c = 0;
    foreach ($res as $res2) {
        foreach ($res2 as $value) {
            printf("r=%d:c=%d::%d <br>\n", $r, $c, $value);
            $c++;
        }
        $r++;
        $c = 0;
    }
}
******************************************************************/


/***** �����ƥ�����ѤΣģ½������� ���顼��å������ϲ��̤Τ� *****/
function getResultWithField($query, &$field, &$result)
{
    if ($connect=funcConnect()) {
        $field = array(); $result = array();    // �����
        if (($resource = @pg_query($connect, $query)) !== FALSE) {  // @ �ǥ��顼��å������޻�
            if (($rows = pg_num_rows($resource)) >= 0) {    // �쥳����0�Ǥ�field�����Ȥ�����>=0�ˤ��Ƥ���
                $fields = pg_num_fields($resource);             // field ���򥻥å� ���顼����-1���֤�
                for ($f=0; $f<$fields; $f++) {
                    $field[$f] = pg_field_name($resource, $f);      // �ե������̾����
                }
                for ($r=0; $r<$rows; $r++) {
                    $result[$r] = pg_fetch_array($resource, $r);    // ���͡�Ϣ�ۥ���ǥå���
                }
                return $rows;                           // ��������(�쥳���ɿ�)
            }
            return $rows;   // 0=�����ͤʤ� -1=���顼
        }
        echo "<tr><td>\n";
        if (isset($php_errormsg)) {
            echo "<div style='color: #ff1e00;'>{$php_errormsg}</div>\n";      // �֥饦��������
        } else {
            echo "<div style='color: #ff1e00;'>php.ini �� track_errors = Off �� On �ˤ��Ʋ�������</div>\n";      // �֥饦��������
        }
        echo "</td></tr>\n";
        return -2;  // pg_query error
    }
    return -3;  // ��³�Ǥ��ʤ�
}

/***** �����ѣģ½��� ���顼��å������� php_error �˽��� *****/
function getResultWithField2($query, &$field, &$result)
{
    if ($connect=funcConnect()) {
        $field = array(); $result = array();    // �����
        if (($resource = pg_query($connect, $query)) !== FALSE) {   // ���顼��php_error��
            if (($rows = pg_num_rows($resource)) >= 0) {    // �쥳����0�Ǥ�field�����Ȥ�����>=0�ˤ��Ƥ���
                $fields = pg_num_fields($resource);             // field ���򥻥å� ���顼����-1���֤�
                for ($f=0; $f<$fields; $f++) {
                    $field[$f] = pg_field_name($resource, $f);      // �ե������̾����
                }
                for ($r=0; $r<$rows; $r++) {
                    $result[$r] = pg_fetch_array($resource, $r);    // ���͡�Ϣ�ۥ���ǥå���
                }
                return $rows;                           // ��������(�쥳���ɿ�)
            }
            return $rows;   // 0=�����ͤʤ� -1=���顼
        }
        return -2;  // pg_query error
    }
    return -3;  // ��³�Ǥ��ʤ�
}

/***** �����ѣģ½��� ���顼��å������� php_error �˽��� *****/
///////// $result �Ͽ��ͥ���ǥå����Τ�
function getResultWithField3($query, &$field, &$result)
{
    if ($connect=funcConnect()) {
        $field = array(); $result = array();    // �����
        if (($resource = pg_query($connect, $query)) !== FALSE) {   // ���顼��php_error��
            if (($rows = pg_num_rows($resource)) >= 0) {    // �쥳����0�Ǥ�field�����Ȥ�����>=0�ˤ��Ƥ���
                $fields = pg_num_fields($resource);             // field ���򥻥å� ���顼����-1���֤�
                for ($f=0; $f<$fields; $f++) {
                    $field[$f] = pg_field_name($resource, $f);      // �ե������̾����
                }
                for ($r=0; $r<$rows; $r++) {
                    $result[$r] = pg_fetch_row($resource, $r);    // ���ͥ���ǥå����Τ�
                }
                return $rows;                           // ��������(�쥳���ɿ�)
            }
            return $rows;   // 0=�����ͤʤ� -1=���顼
        }
        return -2;  // pg_query error
    }
    return -3;  // ��³�Ǥ��ʤ�
}


/* �桼����ǧ�� */
function authorityUser($userid, $passwd, &$authority)
{
    if (strlen($passwd) != 32) $passwd = md5($passwd);
    /*** 2003/04/21 �ƥ����Ѥ��ɲ� ***/
    if (($userid >= 0) && ($userid <= 9) && ($passwd == 'efd9d4fee1dc7684f7699bd7e7f11f67')) {
        $authority = $userid;
        return true;
    }
    /*** 2003/04/21 End ***/
    if ( funcConnect() ) {
        $query = "select retire_date from user_detailes where uid='$userid'";
        if ( execQuery($query) ) {
            $rowdata = array();
            if ( getRowdata(0,$rowdata) ) {
                if ($rowdata['retire_date'] != NULL) {
                    disConnectDB();
                    return false;
                }
            }
        }
        $query = "select * from user_master where uid='$userid' and passwd='$passwd'";
        if ( execQuery($query) ) {
            $rowdata = array();
            if ( getRowdata(0,$rowdata) ) {
                $authority = $rowdata['aid'];
                disConnectDB();             // disConnectDB �� return true �� { } ��˰�ư 2003/04/21
                return true;
            }
        }
    }
    disConnectDB();
    return false;
}


//////////////////////////////////////////////////////////////////////////////
// Tnk Web Site ��Υ���������                                            //
// Copyright(C) 2002-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2002/04/02 Created access_log() function                                 //
// 2002/09/02 ���å������� & register global off �б�                     //
// 2002/09/10 time_log �� �ޥ������äޤ��ݻ�����褦���ѹ�maicrotime()      //
// 2002/09/28 $script_name �� ����Ĺ�����Ȥ��ư����褦���ѹ�                //
// 2003/06/25 PHP-4.3.3RC1��session_start()������ Notice �б�             //
// 2003/12/19 DB�� access_log �� access_log2 ���ѹ� TIMESTAMP(6)�����      //
// 2006/09/28 ���å����γ��ϥ����å����ɲ�(OLD�Ǥˤ⤢�ä������ɤ������) //
//////////////////////////////////////////////////////////////////////////////
function access_log()
{
    if (!isset($_SESSION)) {                    // ���å����γ��ϥ����å�
        session_start();                        // Notice ���򤱤����Ƭ��@(�嵭��ifʬ�����뤿��ɬ�פʤ�)
    }
    $addr_log = $_SERVER['REMOTE_ADDR'];
    $host_log = gethostbyaddr($addr_log);
    // $date_log = date('Y-m-d');
    // $time_log = date('H:i:s:');             // 2002/09/10 �Ǹ��':'���ɲ�
    // $time_log .= substr(microtime(),2,2);   // 2002/09/10 �ޥ������ä��ɲ�
    
    if (func_num_args() >= 1) {
        if (func_get_arg(0) == '') {    // NULL �� '' ���ѹ�
            $file_log = $_SERVER['SCRIPT_NAME'];
        } else {
            $file_log = func_get_arg(0);
        }
    } else {
        $file_log = $_SERVER['SCRIPT_NAME'];
    }
    $con = funcConnect();
    if ($con) {
        execQuery('begin');
        if (isset($_SESSION['User_ID'])) {
            $query = "insert into access_log2 (ip_addr, host, uid, script)
                    values('{$addr_log}', '{$host_log}', '{$_SESSION['User_ID']}', '{$file_log}')";
        } else {
            $query = "insert into access_log2 (ip_addr, host, uid, script)
                    values('{$addr_log}', '{$host_log}', NULL, '{$file_log}')";
        }
        if (execQuery($query) >= 0) {
            execQuery('commit');
            disConnectDB();
        } else {
            execQuery('rollback');
            disConnectDB();
        }
    }
}


//////////////////////////////////////////////////////////////////////////////
// �ե졼���˥塼�� On/Off(ɽ������ɽ��)�ؿ�                              //
//////////////////////////////////////////////////////////////////////////////
function menu_OnOff($script)
{
    /***** �����ȥ�˥塼 On / Off *****/
    if ($_SESSION['site_view'] == 'on') {
        $site_view = 'MenuOFF';
    } else {
        $site_view = 'MenuON';
    }
                                                             // ret_border �ϳƥ�˥塼�ǻ��Ѥ��Ƥ���
    return "
        <td width='40' align='center' valign='center' class='ret_border'>
            <input style='font-size:8.5pt; font-family:monospace;' type='submit' name='site' value='{$site_view}'
            onClick=\"top.location.href = '/menu_frame_OnOff.php?name={$script}';\">
        </td>
    ";
    // �ҥե졼���б��Τ��� parent.location.href �� top.location.href ���ѹ�
    /*********************
    return "<td width='40' bgcolor='#d6d3ce' align='center' valign='center' class='ret_border'>
            <input style='font-size:8.5pt; font-family:monospace;' type='submit' name='site' value='{$site_view}'
            onClick=\"parent.location.href = '/menu_frame_OnOff.php?name={$script}';\">
            </td>\n";
    *********************/
}


//////////////////////////////////////////////////////////////////////////////
// �إå����Υ�˥塼�С��β��˥桼�����ɣġ��桼����̾��ɽ��               //
//////////////////////////////////////////////////////////////////////////////
function view_user($u_id)
{
    switch ($u_id) {
    case 0:
    case 1:
    case 2:
    case 3:
    case 4:
    case 5:
        $auth = (int)$u_id;     // ���������Ѵ�
        $name = "Auth{$auth}";  // ʸ�����Ϣ��
        break;
    default:
        $query = "SELECT trim(name) FROM user_detailes WHERE uid='{$u_id}'";
        if (getUniResult($query, $name) <= 0) {
            $name = 'check'; // ̤��Ͽ���ϥ��顼�ʤ�
        }
    }
    return "
        <table width='100%' cellspacing='0' cellpadding='0' border='0'>
            <td align='right' style='font-size:10pt; font-weight:normal;'>
                {$u_id} {$name}
            </td>
        </table>
    ";
}

//////////////////////////////////////////////////////////////////////////////
// title_boder �β��� include file��ɽ��(basename�Τ�)                      //
// ������ˡ��require����include�ե���������view_file_name(__FILE__)����� //
//////////////////////////////////////////////////////////////////////////////
function view_file_name($file = '')
{
    if ($file != '') {
        if ($_SESSION['Auth'] <= 2) {
            $name = basename($file, '.php');    // ���桼�����ʲ�
        } else {
            $name = substr($file, strlen($_SERVER['DOCUMENT_ROOT']), -4);  // admin�ʤ�
        }
    } else {
        $name = '';
    }
    return "
        <table width='100%' cellspacing='0' cellpadding='0' border='0'>
            <td align='right' style='font-size:10pt; font-weight:normal;'>
                {$name}
            </td>
        </table>
    ";
}


//////////////////////////////////////////////////////////////////////////////
// ����(����)���¥����å� �ե��󥯥å����  �Խ��������γ�ǧ�Ѥ˻��Ѥ���    //
// Copyright(C) 2006-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2006/10/04 Created function getCheckAuthority($id, $division)            //
//            $id = �����å��о�ID(���¼��̤ˤ��ưŪ) text                 //
//            $division = ���¼��� integer 1=�Ұ��ֹ�, 2=IP���ɥ쥹, 3=���� //
//            return boolean                                                //
// 2006/10/05 �ѥ�᡼�����򸢸�No.($division)�Τߤ�$id�ϥ��ץ����(����¾) //
//            ����No.�Υ��С�����Ͽ����Ƥ���category�ˤ����礻������   //
// 2007/01/16 getCheckAuthority()��category=4(���¥�٥�)��ǧ�ڤ��ɲ�           //
//////////////////////////////////////////////////////////////////////////////
function getCheckAuthority($division, $id='')
{
    if ( ($division < 1) || ($division > 32000) ) return false;
    if (!isset($_SESSION['User_ID'])) return false;
    $con = db_connect();
    query_affected_trans($con, 'BEGIN');
    $query = "
        SELECT category FROM common_authority LEFT OUTER JOIN common_auth_category USING(id)
        WHERE division={$division} GROUP BY category ORDER BY category ASC
    ";
    $res = array();
    $rows = getResultTrs($con, $query, $res);
    for ($i=0; $i<$rows; $i++) {
        switch ($res[$i][0]) {
        case 1:     // �Ұ��ֹ����礻
            $query = "SELECT regdate FROM common_authority WHERE division={$division} AND id='{$_SESSION['User_ID']}'";
            break;
        case 2:     // IP���ɥ쥹����礻
            $query = "SELECT regdate FROM common_authority WHERE division={$division} AND id='{$_SERVER['REMOTE_ADDR']}'";
            break;
        case 3:     // ���祳���ɤ���礻
            $query = "SELECT act_id FROM cd_table WHERE uid='{$_SESSION['User_ID']}'";
            $act_id = 0;    // �����
            getUniResTrs($con, $query, $act_id);
            $query = "SELECT regdate FROM common_authority WHERE division={$division} AND id='{$act_id}'";
            break;
        case 4:     // ���¥�٥����礻 (0=����, 1=���, 2=���, 3=���ɥߥ�)
            $query = "SELECT aid FROM user_master WHERE uid='{$_SESSION['User_ID']}'";
            $aid = -1;      // �����
            getUniResTrs($con, $query, $aid);                           // ���¥�٥�ʤΤ�<=(�ʲ�)�����
            $query = "SELECT regdate FROM common_authority WHERE division={$division} AND id<='{$aid}'";
            break;
        default:    // ����¾�ϻ���ID����礻
            $id = addslashes($id);  // ',",\,NULL �Υ��������� �����pg_escape_string()����Ѥ�������PostgreSQL�˰�¸���뤿���򤱤���
            $query = "SELECT regdate FROM common_authority WHERE division={$division} AND id='{$id}'";
        }
        if (getUniResTrs($con, $query, $regdate) > 0) {
            query_affected_trans($con, 'COMMIT');
            return true;
        }
    }
    query_affected_trans($con, 'COMMIT');
    return false;
}


//////////////////////////////////////////////////////////////////////////////
// 2007/04/23 URI�˻��ꤵ�줿�ե����뤬index.php�������å����ơ������Ǥ����//
// �ե�����̾���ά���� URI�ǥ�����쥯�Ȥ����롣����ʳ��ϲ��⤷�ʤ���     //
//////////////////////////////////////////////////////////////////////////////
function uriIndexCheck()
{
    if (basename($_SERVER['SCRIPT_NAME']) != 'index.php') return;
    if (basename($_SERVER['REQUEST_URI']) == basename($_SERVER['SCRIPT_NAME'])) {
        $uri = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['REQUEST_URI']);
        header('Location: ' . H_WEB_HOST . $uri);
        exit();
    }
    /*********** �ʲ��ϼ�ʬ���Ȥ�ľ�ܻ��ꤵ�줿��� ���ޤ����ӤϤʤ��Ȼפ���
    if (basename(__FILE__) != 'index.php') return;
    if (basename($_SERVER['REQUEST_URI']) == basename(__FILE__)) {
        $uri = str_replace(basename(__FILE__), '', $_SERVER['REQUEST_URI']);
        header('Location: ' . H_WEB_HOST . $uri);
        exit();
    }
    ***********/
}

?>
