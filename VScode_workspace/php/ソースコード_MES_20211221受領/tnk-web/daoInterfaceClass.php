<?php
//////////////////////////////////////////////////////////////////////////////
// DAO (Data Access Object) PostgreSQL DB ���󥿡��ե������μ���            //
// Copyright (C) 2005-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/07/20 Created   daoPsqlClass.php                                    //
// 2005/07/22 file̾�� daoPsqlClass.php �� daoInterfaceClass.php            //
//   Ver1.00                        RDBMS���ѹ��򤳤Υ��饹�ǵۼ����뤿��   //
// 2006/07/13 ���� ���̸��¥����å��᥽�å� getCheckAuthority(ID, Division) //
//   Ver1.10    ID=����ID(�Ұ��ֹ��IP���ɥ쥹��), Division=���¶�ʬ ���ɲ� //
// 2006/10/04 �嵭��getCheckAuthority()�᥽�åɤ�������å��ѹ�           //
//   Ver1.11  ini_set('error_reporting', E_ALL) �򥳥��� �ƽи������ꤹ�� //
// 2006/10/05 getCheckAuthority($id, $division) ��                          //
//   Ver1.12           getCheckAuthority($division, $id='') $id�ϥ��ץ���� //
// 2007/01/16 getCheckAuthority()��category=4(���¥�٥�)��ǧ�ڤ��ɲ�       //
//   Ver----    DAO_VERSION ���ѹ���̵��                                    //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��

require_once ('define.php');                // DB Connection �ǡ������ɹ���
require_once ('daoInterface.php');          // DAO Interface ���ɹ���

if (class_exists('daoInterfaceClass')) {
    return;
}
if (DAO_VERSION !== '1.12') {
    return;
}

/****************************************************************************
*                       base class ���쥯�饹�����                         *
****************************************************************************/
///// namespace Common {} �ϸ��߻��Ѥ��ʤ� �����㡧Common::ComTableMnt �� $obj = new Common::ComTableMnt;
///// Common::$sum_page, Common::set_page_rec() (̾������::��ƥ��)
class daoInterfaceClass implements daoInterface
{
    /////////////////////////////////////////////////////////////////////////
    /*        DB ���ͥ������ return=���ͥ������꥽���� FASE(error)      */
    /////////////////////////////////////////////////////////////////////////
    public function connectDB()
    {
        if (DB_HOST == 'local') {
            $connstr = 'dbname='.DB_NAME.' user='.DB_USER.' password='.DB_PASSWD;
        } else {
            $connstr = 'host='.DB_HOST.' port='.DB_PORT.' dbname='.DB_NAME.' user='.DB_USER.' password='.DB_PASSWD;
        }
        return pg_pConnect($connstr);   // ��³Ū��³
    }
    
    /////////////////////////////////////////////////////////////////////////
    /* ��ˡ����ʥǡ������Ф� return=TRUE/FALSE                          */
    /////////////////////////////////////////////////////////////////////////
    public function getUniResult($sql, &$result)
    {
        if ($conn = $this->connectDB()) {                   // ��³Ū��³
            if (($res = pg_query($conn, $sql)) !== FALSE) {
                if (($rows = pg_num_rows($res)) > 0) {      // �쥳���ɤ����뤫
                    $result = pg_fetch_result($res, 0, 0);  // �ǡ������å� row=0����, field=0��
                    return TRUE;                            // ��������
                }
                return FALSE;   // �����ͤʤ� $rows = 0 �� php5.0.4�Ǥ� -1���֤� -1=pg_num_rows error
            }
            return FALSE;   // pg_query error
        }
        return FALSE;   // ��³����
    }
    
    /////////////////////////////////////////////////////////////////////////
    /* ɽ�����ʥǡ������Ф�$result[$r][$f](����index�Τ�) return=rows    */
    /////////////////////////////////////////////////////////////////////////
    public function getResult2($sql, &$result)
    {
        if ($conn = $this->connectDB()) {                   // ��³Ū��³
            $result = array();      // �����
            if (($resource = pg_query($conn, $sql)) !== FALSE) {
                if (($rows = pg_num_rows($resource)) > 0) {         // �쥳���ɤ����뤫
                    for ($r=0; $r<$rows; $r++) {
                        $result[$r] = pg_fetch_row($resource, $r);
                    }
                }
                return $rows;   // 0�쥳���ɰʾ������ -1=pg_num_rows error
            }
            return -2;  // pg_query error
        }
        return -3;  // ��³�Ǥ��ʤ�
    }
    
    /////////////////////////////////////////////////////////////////////////
    /* ɽ�����ʥǡ���(���ͥ���ǥå���+Ϣ������)���Ф� return=rows       */
    /////////////////////////////////////////////////////////////////////////
    /********** �ߴ����Τ���˻Ĥ� **********/
    public function getResult($sql, &$result)
    {
        if ($conn = $this->connectDB()) {                   // ��³Ū��³
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
    
    /////////////////////////////////////////////////////////////////////////
    /*          �ȥ�󥶥�������� SQL�¹� return=������ <0(error)         */
    /////////////////////////////////////////////////////////////////////////
    public function query_affected_trans($connect, $sql)
    {
        if (($res = pg_query($connect, $sql)) !== FALSE) {   // �����Ǥ�������
            return pg_affected_rows($res);  // return = 0 �����оݤʤ����ϼ���
        } else {
            return -1;  // pg_query error
        }
    }
    
    //////////////////////////////////////////////////////////////////////////
    /*          ������åȹ����� SQL�¹� return=������ <0(error)            */
    //////////////////////////////////////////////////////////////////////////
    public function query_affected($sql)
    {
        if ($conn = $this->connectDB()) {                   // ��³Ū��³
            if (($res = pg_query($conn, $sql)) !== FALSE) { // �����Ǥ�������
                return pg_affected_rows($res);              // return = 0 �����оݤʤ����ϼ���
            }
            return -1;  // pg_query error
        }
        return -2;  // ��³�Ǥ��ʤ�
    }
    
    /////////////////////////////////////////////////////////////////////////
    /* �ȥ�󥶥�������� Unique �Ȳ����Ѥ�����ͤϰ��                    */
    /*      return=1(�����ͤ���) 0(�����ͤʤ�) <0(error)                   */
    /////////////////////////////////////////////////////////////////////////
    public function getUniResTrs($connect, $query, &$result)
    {
        if (($resource = pg_query($connect, $query)) !== FALSE) {
            if (($rows = pg_num_rows($resource)) > 0) {      // �쥳���ɤ����뤫
                $result = pg_fetch_result($resource, 0, 0);  // �ǡ������å� row=0����, field=0��
                return 1;   // ��������
            }
            return $rows;   // 0=�����ͤʤ� �� php5.0.4�Ǥ� -1���֤�
        }
        return -2;  // pg_query error
    }
    
    /////////////////////////////////////////////////////////////////////////
    /* �ȥ�󥶥�������� ɽ�����ǡ������Ф�$result[$r][$f](����index)   */
    /*      return= rows>0(�����ͤ���) 0(�����ͤʤ�) <0(error)             */
    /////////////////////////////////////////////////////////////////////////
    public function getResultTrs($connect, $query, &$result)
    {
        $result = array();      // �����
        if (($resource = pg_query($connect, $query)) !== FALSE) {
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
    
    //////////////////////////////////////////////////////////////////////////
    /* �ȥ�󥶥�������� ɽ�����ǡ������Ф�$result[$r][$f](����index)    */
    /* + field̾������ return= rows>0(�����ͤ���) 0(�����ͤʤ�) <0(error)   */
    /* 0�쥳���ɤǤ�ե�����ɤ�����Ф��� 0 record�б�                     */
    //////////////////////////////////////////////////////////////////////////
    public function getResWithFieldTrs($connect, $query, &$field, &$result)
    {
        $field = array(); $result = array();    // �����
        if (($resource = pg_query($connect, $query)) !== FALSE) {
            if (($rows = pg_num_rows($resource)) >= 0) {    // �쥳����0�Ǥ�field�����Ȥ�����>=0�ˤ��Ƥ���
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
    
    //////////////////////////////////////////////////////////////////////////
    /* �����ƥ�����ѤΣģ½������� ���顼��å������ϲ��̤Τ� ������å�   */
    /* + field̾������ return= rows>0(�����ͤ���) 0(�����ͤʤ�) <0(error)   */
    /* 0�쥳���ɤǤ�ե�����ɤ�����Ф��� 0 record�б�                     */
    //////////////////////////////////////////////////////////////////////////
    public function getResultWithField($query, &$field, &$result)
    {
        if ($connect = $this->connectDB()) {    // ��³Ū��³
            $field = array(); $result = array();    // �����
            if (($resource = @pg_query($connect, $query)) !== FALSE) {  // @ �ǥ��顼��å������޻�
                if (($rows = pg_num_rows($resource)) >= 0) {    // �쥳����0�Ǥ�field�����Ȥ�����>=0�ˤ��Ƥ���
                    $fields = pg_num_fields($resource);                 // field ���򥻥å� ���顼����-1���֤�
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
    
    //////////////////////////////////////////////////////////////////////////
    /*  �����ѣģ½��� php_error���� ������å��� ����index��Ϣ��index      */
    /* + field̾������ return= rows>0(�����ͤ���) 0(�����ͤʤ�) <0(error)   */
    /* 0�쥳���ɤǤ�ե�����ɤ�����Ф��� 0 record�б�                     */
    //////////////////////////////////////////////////////////////////////////
    public function getResultWithField2($query, &$field, &$result)
    {
        if ($connect = $this->connectDB()) {    // ��³Ū��³
            $field = array(); $result = array();    // �����
            if (($resource = pg_query($connect, $query)) !== FALSE) {   // ���顼��php_error��
                if (($rows = pg_num_rows($resource)) >= 0) {    // �쥳����0�Ǥ�field�����Ȥ�����>=0�ˤ��Ƥ���
                    $fields = pg_num_fields($resource);                 // field ���򥻥å� ���顼����-1���֤�
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
    
    //////////////////////////////////////////////////////////////////////////
    /* �����ѣģ½��� php_error���� ������å��� $result�Ͽ���index�Τ�     */
    /* + field̾������ return= rows>0(�����ͤ���) 0(�����ͤʤ�) <0(error)   */
    /* 0�쥳���ɤǤ�ե�����ɤ�����Ф��� 0 record�б�                     */
    //////////////////////////////////////////////////////////////////////////
    public function getResultWithField3($query, &$field, &$result)
    {
        if ($connect = $this->connectDB()) {    // ��³Ū��³
            $field = array(); $result = array();    // �����
            if (($resource = pg_query($connect, $query)) !== FALSE) {   // ���顼��php_error��
                if (($rows = pg_num_rows($resource)) >= 0) {    // �쥳����0�Ǥ�field�����Ȥ�����>=0�ˤ��Ƥ���
                    $fields = pg_num_fields($resource);                 // field ���򥻥å� ���顼����-1���֤�
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
    
    //////////////////////////////////////////////////////////////////////////
    /* ���̸���ͭ��̵�������᥽�å� ID���Ф��Ƹ��¤Τ���ʤ�                */
    /* id=string��, division�ϸ��¶�ʬ1�����֤˥��󥯥����integer��      */
    /* ����ͤ� bool�� true=���¤���, false=����̵��                        */
    /*          public method   getCheckAuthority($id, $division)           */
    /* $id = �����å��о�ID(���¼��̤ˤ��ưŪ) text                        */
    /* $division = ���¼��� integer 1=�Ұ��ֹ�, 2=IP���ɥ쥹, 3=����        */
    /* return boolean                                                       */
    /* Ver1.12 �ѥ�᡼�����򸢸�No.($division)�Τߤ�$id�ϥ��ץ����(����¾)*/
    /*          ����No.�Υ��С�����Ͽ����Ƥ���category�ˤ����礻������ */
    /* 2007/01/16 getCheckAuthority()��category=4(���¥�٥�)��ǧ�ڤ��ɲ�   */
    //////////////////////////////////////////////////////////////////////////
    public function getCheckAuthority($division, $id='')
    {
        if ( ($division < 1) || ($division > 32000) ) return false;
        if (!isset($_SESSION['User_ID'])) return false;
        $con = $this->connectDB();
        $this->query_affected_trans($con, 'BEGIN');
        $query = "
            SELECT category FROM common_authority LEFT OUTER JOIN common_auth_category USING(id)
            WHERE division={$division} GROUP BY category ORDER BY category ASC
        ";
        $res = array();
        $rows = $this->getResultTrs($con, $query, $res);
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
                $this->getUniResTrs($con, $query, $act_id);
                $query = "SELECT regdate FROM common_authority WHERE division={$division} AND id='{$act_id}'";
                break;
            case 4:     // ���¥�٥����礻 (0=����, 1=���, 2=���, 3=���ɥߥ�)
                $query = "SELECT aid FROM user_master WHERE uid='{$_SESSION['User_ID']}'";
                $aid = -1;      // �����
                $this->getUniResTrs($con, $query, $aid);                    // ���¥�٥�ʤΤ�<=(�ʲ�)�����
                $query = "SELECT regdate FROM common_authority WHERE division={$division} AND id<='{$aid}'";
                break;
            default:    // ����¾�ϻ���ID����礻
                $id = addslashes($id);  // ',",\,NULL �Υ��������� �����pg_escape_string()����Ѥ�������PostgreSQL�˰�¸���뤿���򤱤���
                $query = "SELECT regdate FROM common_authority WHERE division={$division} AND id='{$id}'";
            }
            if ($this->getUniResTrs($con, $query, $regdate) > 0) {
                $this->query_affected_trans($con, 'COMMIT');
                return true;
            }
        }
        $this->query_affected_trans($con, 'COMMIT');
        return false;
    }
    
} // Class daoInterfaceClass End

?>
