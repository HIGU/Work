<?php 
//////////////////////////////////////////////////////////////////////////////
// ������Ư���� ��ž����κ����ޥ������ݼ�  �ƥե�����  Client interface �� //
// �Խ�(MaterialEntryPage)���Ȳ�(MaterialView)��ƽФ�  MVC View �� List �� //
// Copyright (C) 2004-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/07/01 Created   MaterialEntry.php                                   //
// 2006/06/09 access_log() �б�                                             //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
ini_set('error_reporting', E_ALL);          // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../../function.php');     // access_log()���ǻ���
require_once ('../com/define.php');
require_once ('../com/function.php');
access_log();                               // Script Name �ϼ�ư����

// ��å������Υ��ꥢ
$Message = '';

// �����ԥ⡼�ɤμ���
$AdminUser = AdminUser( FNC_MASTER );

// ���������ɤμ���
$ProcCode = $_REQUEST['ProcCode'];
if (!isset($_REQUEST['ProcCode'])) $ProcCode = 'EDIT';

// ��Ǽ
$Materials = Array();
$Materials['Code']   = trim (@$_REQUEST['Code']);
$Materials['Name']   = trim (@$_REQUEST['Name']);
$Materials['Type']   = trim (@$_REQUEST['Type']);
$Materials['Style']  = trim (@$_REQUEST['Style']);
$Materials['Weight'] = trim (@$_REQUEST['Weight']);
$Materials['Length'] = trim (@$_REQUEST['Length']);


// �����ο���ʬ��
if ($ProcCode == 'EDIT') {
    $EDIT_MODE = 'INSERT';
    // ���������ɤ������Ǥ���Ȥ��Ͻ����⡼��
    if ($Materials['Code'] != '') {
        ReadData();
        $EDIT_MODE = 'UPDATE';
    }
    // Entry����ɽ��
    require_once('MaterialsEntryPage.php');
} else if ($ProcCode == 'WRITE') {
    // �������ƤΥ����å�
    $EDIT_MODE = @$_REQUEST['EDIT_MODE'];
    if (!EntryDataCheck()) {
        // ���顼������Τ����ϲ��̤����
        require_once('MaterialsEntryPage.php');
    } else {
        // �ǡ�������¸
        SaveData();
        // ���ɹ�
        ReadData();
        // ɽ������
        $Message = '��Ͽ���ޤ�����';
        // ɽ�����̤�
        require_once('MaterialsView.php');
    }
} else if ($ProcCode == 'DELETE') {
    // ����⡼��
    DeleteData();
    // ������쥯��
    header("Location: ".@$_REQUEST['RetUrl']);
    
} else if ($ProcCode == 'VIEW') {
    // ��Ͽ���Ƥ��ɤ߹���
    ReadData();
    // ɽ�����̤�
    require_once('MaterialsView.php');
} else {
    // �����ƥ२�顼
    $SYSTEM_MESSAGE = "���������ɤ�����������ޤ���[$ProcCode]";
    require_once('../com/' . ERROR_PAGE);
    exit();
}

// --------------------------------------------------
// �������ƤΥ����å�
// --------------------------------------------------
function EntryDataCheck()
{
    global $Message,$Materials,$EDIT_MODE;
    // ����������
    if ($Materials['Code'] == '') {
        $Message .= '���������ɤ�̤���ϤǤ���\n\n';
    } else {
        // ʸ���������å�
        if (strlen($Materials['Code']) > 7) {
            $Message .= '���������ɤϣ��Х��Ȱ������Ͽ���Ʋ�������\n\n';
        } else {
            // ��ʣ��Ͽ�Υ����å�
            if ($EDIT_MODE == 'INSERT') {
                $con = getConnection();
                $sql = "select mtcode from equip_materials where mtcode='" .$Materials['Code']."'";
                $rs = pg_query ($con , $sql);
                if ($row = pg_fetch_array ($rs)) {
                    $Message .= '����������['.$Materials['Code'].']�Ϥ��Ǥ���Ͽ����Ƥ��ޤ���\n\n';
                }
            }
        }
    }
    
    // ����̾��
    if ($Materials['Name'] == '') {
        $Message .= '����̾�Τ�̤���ϤǤ���\n\n';
    } else {
        if (strlen($Materials['Name']) > 30) {
            $Message .= '����̾�Τϣ����Х��Ȱ������Ͽ���Ʋ�������\n\n';
        }
    }

    // ���
    if ($Materials['Style'] == '') {
        $Message .= '���ʺ����̤���ϤǤ���\n\n';
    } else {
        if (strlen($Materials['Style']) > 30) {
            $Message .= '���ʺ���ϣ����Х��Ȱ������Ͽ���Ʋ�������\n\n';
        }
    }
    
    // �н���
    if ($Materials['Weight'] == '') {
        $Message .= '���̤�̤���ϤǤ���\n\n';
    } else {
        if (!is_numeric($Materials['Weight'])) {
            $Message .= '���̤Ͽ��ͤ����Ϥ��Ʋ�������\n\n';
        } else {
            if ($Materials['Weight'] <= 0) {
                $Message .= '���̤ϣ��ʲ��Ǥ���Ͽ�Ǥ��ޤ���\n\n';
            } else {
                $Materials['Weight'] = sprintf ('%.04f', $Materials['Weight']);
            }
        }
    }
    // ɸ��Ĺ��
    if ($Materials['Length'] == '') {
        $Message .= 'ɸ��Ĺ����̤���ϤǤ���\n\n';
    } else {
        if (!is_numeric($Materials['Length'])) {
            $Message .= 'ɸ��Ĺ���Ͽ��ͤ����Ϥ��Ʋ�������\n\n';
        } else {
            if ($Materials['Length'] <= 0) {
                $Message .= 'ɸ��Ĺ���ϣ��ʲ��Ǥ���Ͽ�Ǥ��ޤ���\n\n';
            } else {
                $Materials['Length'] = sprintf ('%.04f', $Materials['Length']);
            }
        }
    }
    
    if ($Message == '') return true;
    else                return false;
}
// --------------------------------------------------
// ��Ͽ
// --------------------------------------------------
function SaveData()
{
    global $Materials;
    
    // ���ͥ������μ���
    $con = getConnection();
    pg_query ($con , 'BEGIN');
    
    if ($_REQUEST['EDIT_MODE'] == 'UPDATE') {
        // �����⡼�ɤλ��ϰ��پä�
        $sql = "delete from equip_materials where mtcode='" . pg_escape_string ($Materials['Code']) . "'";
        if (!pg_query ($con , $sql)) {
            pg_query ($con , 'ROLLBACK');
            $SYSTEM_MESSAGE = "�ǡ����١����ι����˼��Ԥ��ޤ���\n$sql";
            require_once ('../com/' . ERROR_PAGE);
            exit();
        }
    }
    // ��¸
    $sql = "insert into equip_materials(mtcode,mtname,type,style,weight,length,last_user) values ( "
         . "'" . pg_escape_string ($Materials['Code'])    . "',"
         . "'" . pg_escape_string ($Materials['Name'])    . "',"
         . "'" . pg_escape_string ($Materials['Type'])    . "',"
         . "'" . pg_escape_string ($Materials['Style'])   . "',"
         . "'" . pg_escape_string ($Materials['Weight'])  . "',"
         . "'" . pg_escape_string ($Materials['Length'])  . "',"
         . "'" . pg_escape_string ($_SESSION['User_ID'])  . "'"
         . " ) ";
    if (!pg_query ($con , $sql)) {
        pg_query ($con , 'ROLLBACK');
        $SYSTEM_MESSAGE = "�ǡ����١����ι����˼��Ԥ��ޤ���\n$sql";
        require_once ('../com/' . ERROR_PAGE);
        exit();
    }
    
    pg_query ($con , 'COMMIT');
   
}
// --------------------------------------------------
// ��Ͽ�ǡ������ɤ߹���
// --------------------------------------------------
function ReadData()
{
    global $Materials;
    
    // ���ͥ������μ���
    $con = getConnection();
    
    // �ǡ�������
    $sql = "select mtname,type,style,weight,length from equip_materials where mtcode='" . pg_escape_string ($Materials['Code']) . "'";
    $rs = pg_query ($con , $sql);
    if ($row = pg_fetch_array ($rs)) {
        $Materials['Name']    = $row['mtname'];
        $Materials['Type']    = $row['type'];
        $Materials['Style']   = $row['style'];
        $Materials['Weight']  = $row['weight'];
        $Materials['Length']  = $row['length'];
    } else {
        $SYSTEM_MESSAGE = "�ǡ����μ����˼��Ԥ��ޤ�����\n$sql";
        require_once ('../com/'.ERROR_PAGE);
        exit();
    }
   
}
// --------------------------------------------------
// ��Ͽ�ǡ����κ��
// --------------------------------------------------
function DeleteData()
{
    global $Materials;
    
    $con = getConnection();
    pg_query ($con , 'BEGIN');
    $sql = "delete from equip_materials where mtcode='" . pg_escape_string ($Materials['Code']) . "'";
    echo($sql);
    if (!pg_query ($con , $sql)) {
        pg_query ($con , 'ROLLBACK');
        $SYSTEM_MESSAGE = "�ǡ����١����ι����˼��Ԥ��ޤ���\n$sql";
        require_once ('../com/' . ERROR_PAGE);
        exit();
    }
    pg_query ($con , 'COMMIT');
}
ob_end_flush();
