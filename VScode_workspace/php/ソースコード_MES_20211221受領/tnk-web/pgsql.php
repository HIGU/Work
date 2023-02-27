<?php
//////////////////////////////////////////////////////////////////////////////
// �ǡ����١�����³�� funcotion file PostgreSQL                             //
// Copyright (C) 2001-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2001/07/07 Created  pgsql.php                                            //
// 2001/10/01 pg_Connect() pg_pConnect()���ѹ� (��³Ū��³)                 //
//            ��̣������Τ�?���䤬�Ĥ�pg_close()��pg_pconnect�ˤϺ��Ѥ��ʤ�//
// 2002/12/09 pg_FreeResult() �� pg_Free_Result()���ѹ�                     //
//            pg_Exec()       �� pg_query() ���ѹ�                          //
//            pg_NumRows()    �� pg_num_rows() ���ѹ�                       //
//                                �嵭������ PHP 4.2.0 �ʾ���ѹ�           //
// 2002/12/11 $query=StripSlashes($query) �򥳥��� \�Υ��������н�        //
// 2003/02/12 pg_Fetch_Array getRowdata()�򤽤Τޤޤˤ���(�ߴ����Τ���)     //
//            pg_Fetch_row ���Ѥ��� getRowdata2() ���ɲ�                    //
//                       ���ͥ���ǥå����Τߤ� foreach()�˻���             //
// 2003/05/01 �����ƥ�����Σģ½����Ѥ�execQuery2()�򿷵�����              //
// 2004/01/07 unexpected EOF on client connection�к��Τ��� disConnectDB��  //
//            pg_free_result()�򥳥��Ȥˤ�����                            //
// 2005/05/20 connectDB()�˥�����(Unix�����å�)��³�ε�ǽ���ɲõڤӸ�ľ�� //
//////////////////////////////////////////////////////////////////////////////
define('NOTCONNECT',    '1');
define('EMPTYRESULT',   '2');
define('FAILEDCONNECT', '3');
define('FAILEDQUERY',   '4');

$gConnect   = 0;
$gResult    = 0;
$gLastError = 0;

/* �ǡ����١�������³  */
function connectDB($host, $port, $name, $user, $passwd) {
    global $gConnect;
    global $gResult;
    global $gLastError;

    $gConnect = $gResult = 0;
    if (DB_HOST == 'local') {
        $connstr = 'dbname='.DB_NAME.' user='.DB_USER.' password='.DB_PASSWD;
    } else {
        $connstr = 'host='.DB_HOST.' port='.DB_PORT.' dbname='.DB_NAME.' user='.DB_USER.' password='.DB_PASSWD;
    }
    if ($conn=pg_pConnect($connstr)) {  // ��³Ū��³���ѹ�
    // if ($conn=pg_Connect($connstr)) {
        // pg_set_client_encoding($conn, 'EUC-JP');
        $gConnect = $conn;
        return $conn;
    }
    $gLastError = FAILEDCONNECT;
    return $conn;   // FALSE
}

/* �ǡ����١���������  */
function disConnectDB(){
    global $gConnect;
    global $gResult;
    if($gResult){
        // pg_Free_Result($gResult);
        $gResult=0;
    }
    pg_Close($gConnect);
    $gConnect=0;
}

/* �����꡼��¹� */
function execQuery($query)
{
    global $gConnect;
    global $gResult;
    global $gLastError;
    $gResult = 0;
//  $query = StripSlashes($query);
    if ($gConnect) {
        $res = pg_query($gConnect, $query);
        if ($res) {
            $gResult = $res;
            return pg_Num_Rows($res);
        }
        $gLastError = FAILEDQUERY;
    } else {
        $gLastError = NOTCONNECT;
        // echo "$query \n";   // ��³���顼�Ǹƽи��ǥ����å����Ƥ��뤿���̣���ʤ�
    }
    return -1;
}

/* �����꡼��¹� �����ƥ�����ѤΣģ½������� */
/* @pg_query �ˤ��� $php_errormsg �ǥ��顼��֥饦�����˽��� */
function execQuery2($query)
{
    global $gConnect;
    global $gResult;
    global $gLastError;
    $gResult = 0;
//  $query=StripSlashes($query);
    if ($gConnect) {
        $res = @pg_query($gConnect, $query);    // @ �ǥ��顼��å������޻�
        if ($res) {
            $gResult = $res;
            return pg_Num_Rows($res);
        } else {
            echo "<tr><td>\n";
            if (isset($php_errormsg)) {
                echo "<font color='#ff1e00'>" . $php_errormsg . "</font><br>\n";      // �֥饦��������
            } else {
                echo "<font color='#ff1e00'>php.ini �� track_errors = Off �� On �ˤ��Ʋ�������</font><br>\n";      // �֥饦��������
            }
            echo "</td></tr>\n";
        }
        $gLastError = FAILEDQUERY;
    } else {
        $gLastError = NOTCONNECT;
    }
    return -1;
}

/* ����쥳���ɤ��ͤ��֤� ���ͥ���ǥå��� + �ե������̾������Ϣ�ۥ���ǥå��� 2003/02/12 */
function getRowdata($row, &$rowdata)
{
    global $gConnect;
    global $gResult;
    global $gLastError;
    if($gResult){
        $rowdata = pg_Fetch_Array($gResult, $row);
        return count($rowdata);
    }
    $gLastError = EMPTYRESULT;
    return 0;
}

/* ����쥳���ɤ��ͤ��֤� ���ͥ���ǥå����Τ� foreach �˻��� 2003/02/12 */
function getRowdata2($row, &$rowdata)
{
    global $gConnect;
    global $gResult;
    global $gLastError;
    if ($gResult) {
        $rowdata = pg_Fetch_row($gResult, $row);
        return count($rowdata);
    }
    $gLastError = EMPTYRESULT;
    return 0;
}

/* �Ǹ��ȯ���������顼��ʸ����Ȥ����֤� */
function getLastError(){
    global $gLastError;
    switch($gLastError){
    case NOTCONNECT:    $str="�ǡ����١�������³����Ƥ��ޤ���";    break;
    case EMPTYRESULT:   $str="�䤤��碌�η�̤�����ޤ���";        break;
    case FAILEDCONNECT: $str="�ǡ����١�������³�˼��Ԥ��ޤ���";    break;
    case FAILEDQUERY:   $str="�ǡ����١����ؤ��䤤��碌�˼��Ԥ��ޤ���";break;
    default:        $str="�������Ƥ��ʤ����顼�Ǥ�";
    }
    $gLastError=0;
    return $str;
}

/* �ե������̾��������� */
function getFieldsName(&$res_array){
    global $gConnect;
    global $gResult;
    if($gConnect){
        $fields=pg_NumFields($gResult);
        for($i=0;$i<$fields;$i++)
            $res_array[$i]=pg_FieldName($gResult,$i);
        return $fields;
    }
    return 0;
}
?>
