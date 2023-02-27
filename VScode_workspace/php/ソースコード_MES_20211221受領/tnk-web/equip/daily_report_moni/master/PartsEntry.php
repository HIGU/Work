<?php 
//////////////////////////////////////////////////////////////////////////////
// ������Ư���� ��ž��������ʥޥ������ݼ�  �ƥե�����  Client interface �� //
//     �Խ�(PartsEntryPage)���Ȳ�(PartsView)�Ѥ�ƽФ�  MVC View �� List �� //
// Copyright (C) 2004-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/07/01 Created   PartsEntry.php                                      //
// 2006/06/09 access_log()�б� equip_parts�ơ��֥��ѹ��������ֹ�ȵ���̾�ɲ�//
// 2006/06/12 $ProcCode�ǽ�����ʬ��if else if �� switch () ���ѹ�           //
//            getMacNoSelectData($mac_no), getMachineName($mac_no) ���ɲ�   //
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

// �����ԥ⡼�ɤμ���
$AdminUser = AdminUser( FNC_MASTER );
// ��å������Υ��ꥢ
$Message = '';
$CheckMaster = false;

// ���������ɤμ���
$ProcCode = @$_REQUEST['ProcCode'];
if (!isset($_REQUEST['ProcCode'])) $ProcCode = 'EDIT';

// ��Ǽ
$Parts = Array();
$Parts['MacNo']         = trim(@$_REQUEST['MacNo']);
$Parts['MacName']       = trim(@$_REQUEST['MacName']);  // Undefined index ���򤱤뤿����ɲ�
$Parts['Code']          = strtoupper(trim(@$_REQUEST['Code']));
$Parts['Name']          = trim(@$_REQUEST['Name']);
$Parts['Zai']           = trim(@$_REQUEST['Zai']);
$Parts['Size']          = trim(@$_REQUEST['Size']);
$Parts['UseItem']       = trim(@$_REQUEST['UseItem']);
$Parts['Abandonment']   = trim(@$_REQUEST['Abandonment']);

// �����ο���ʬ��
switch ($ProcCode) {
case 'EDIT':
    $EDIT_MODE = 'INSERT';
    // ���������ɤ������Ǥ���Ȥ��Ͻ����⡼��
    if ($Parts['Code'] != '') {
        // �ǡ������ɤ߹���
        ReadData();
        // �����⡼�ɥ��å�
        $EDIT_MODE = 'UPDATE';
    }
    // Entry����ɽ��
    require_once('PartsEntryPage.php');
    break;
case 'WRITE':
    $CheckMaster = $_REQUEST['CheckMaster'];
    // �������ƤΥ����å�
    $EDIT_MODE = @$_REQUEST['EDIT_MODE'];
    if (!EntryDataCheck()) {
        // ���顼������Τ����ϲ��̤����
        require_once('PartsEntryPage.php');
    } else {
        // �ǡ�������¸
        SaveData();
        // �ǡ����κ��ɹ�
        ReadData();
        // ɽ������
        $Message = '��Ͽ���ޤ�����';
        // ɽ���ڡ����˰�ư
        require_once('PartsView.php');
    }
    break;
case 'CHECK_MASTER':
    $EDIT_MODE = $_REQUEST['EDIT_MODE'];
    EntryDataCheck();
    $Message = '';
    $Parts['UseItem'] = '';
    if ($Parts['Code'] != '') {
        // ���ͥ������μ���
        $con = getConnection();     // �ʲ��Ϻǿ��κ��������ɤˤ��뤿�� ORDER BY delivery DESC ���ɲ� 2006/06/12
        $rs = pg_query ($con , "SELECT material FROM equip_work_inst_header WHERE parts_no='" . pg_escape_string ($Parts['Code']) . "' ORDER BY delivery DESC");
        if ($row = pg_fetch_array ($rs)) {
            $Parts['UseItem'] = $row['material'];
            $CheckMaster = true;
        }
    }
    // ���ϲ��̤����
    require_once('PartsEntryPage.php');
    break;
case 'CHECK_MAC_MASTER':
    $EDIT_MODE = $_REQUEST['EDIT_MODE'];
    $Message = '';
    $Parts['MacName'] = getMachineName($Parts['MacNo']);
    // ���ϲ��̤����
    require_once('PartsEntryPage.php');
    break;
case 'DELETE':
    // �ǡ����κ��
    DeleteData();
    // ������쥯��
    header("Location: ".@$_REQUEST['RetUrl']);
    break;
case 'VIEW':
    // �ǡ������ɤ߹���
    ReadData();
    // ɽ���ڡ����˰�ư
    require_once('PartsView.php');
    break;
default:
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
    global $Message,$Parts,$EDIT_MODE;
    
    // ���ͥ������μ���
    $con = getConnection();
    // ���ʥ�����
    if ($Parts['Code'] == '') {
        $Message .= '�����ֹ椬̤���ϤǤ���\n\n';
    } else {
        // ��¸�Υ����ƥ�ޥ���¸�ߥ����å�
        $sql = "SELECT mipn, midsc, mzist FROM miitem WHERE mipn='" . pg_escape_string ($Parts['Code'])."'";
        $rs = pg_query ($con , $sql);
        if ($row = pg_fetch_array ($rs)) {
            // �ޥ�����¸�ߤ���Τ��ͤ��Ǽ
            $Parts['Name'] = $row['midsc'];
            $Parts['Zai']  = $row['mzist'];
        } else {
            // �ޥ��������å����顼
            $Message .= "�����ֹ� [{$Parts['Code']}] �ϥޥ�������Ͽ����Ƥ��ޤ���\\n\\n";
            $Parts['Name'] = '';
            $Parts['Zai']  = '';
        }
        // ��ʣ��Ͽ�Υ����å�
        if ($EDIT_MODE == 'INSERT') {
            $sql = "SELECT item_code FROM equip_parts WHERE item_code='" .$Parts['Code']."' AND mac_no={$Parts['MacNo']}";
            $rs = pg_query ($con , $sql);
            if ($row = pg_fetch_array ($rs)) {
                $Message .= "�����ֹ� [{$Parts['MacNo']}] �� �����ֹ� [{$Parts['Code']}] �Ϥ��Ǥ���Ͽ����Ƥ��ޤ���\\n\\n";
            }
        }
    }
    
    // ��ˡ
    if ($Parts['Size'] == '') {
        $Message .= '��ˡ��̤���ϤǤ���\n\n';
    } else {
        if (!is_numeric($Parts['Size'])) {
            $Message .= '��ˡ�Ͽ��ͤ����Ϥ��Ʋ�������\n\n';
        } else {
            if ($Parts['Size'] <= 0) {
                $Message .= '��ˡ�ϣ��ʲ��Ǥ���Ͽ�Ǥ��ޤ���\n\n';
            }
        }
    }
    // ���Ѻ���
    if ($Parts['UseItem'] == '') {
        $Message .= '���Ѻ�����̤���ϤǤ���\n\n';
    }
    
    // �˺ॵ����
    if ($Parts['Abandonment'] == '') {
        $Message .= '�˺ॵ������̤���ϤǤ���\n\n';
    } else {
        if (!is_numeric($Parts['Abandonment'])) {
            $Message .= '�˺ॵ�����Ͽ��ͤ����Ϥ��Ʋ�������\n\n';
        } else {
            if ($Parts['Abandonment'] < 0) {
                $Message .= '�˺�ϣ�̤���Ǥ���Ͽ�Ǥ��ޤ���\n\n';
            }
        }
    }
    
    if ($Message == '') return true;
    else                return false;
}
// --------------------------------------------------
// �ǡ�������¸
// --------------------------------------------------
function SaveData()
{
    global $Parts;
    
    // ���ͥ������μ���
    $con = getConnection();
    pg_query ($con , 'BEGIN');
    
    if (@$_REQUEST['EDIT_MODE'] == 'UPDATE') {
        // �����⡼�ɤλ��ϰ��پä�
        $sql = "DELETE FROM equip_parts WHERE item_code='" . pg_escape_string ($Parts['Code']) . "' AND mac_no={$Parts['MacNo']}";
        if (!pg_query ($con , $sql)) {
            pg_query ($con , 'ROLLBACK');
            $SYSTEM_MESSAGE = "�ǡ����١����ι����˼��Ԥ��ޤ���\n$sql";
            require_once ('../com/' . ERROR_PAGE);
            exit();
        }
    }
    
    // �ǡ�����¸
    $sql = "INSERT INTO equip_parts(mac_no, item_code, size, use_item, abandonment, last_user) values ( "
         .       pg_escape_string ($Parts['MacNo'])        . " ,"
         . "'" . pg_escape_string ($Parts['Code'])         . "',"
         . "'" . pg_escape_string ($Parts['Size'])         . "',"
         . "'" . pg_escape_string ($Parts['UseItem'])      . "',"
         . "'" . pg_escape_string ($Parts['Abandonment'])  . "',"
         . "'" . pg_escape_string ($_SESSION['User_ID'])   . "'"
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
// �ǡ������ɤ߹��� 
// --------------------------------------------------
function ReadData()
{
    global $Parts;
    
    // ���ͥ������μ���
    $con = getConnection();
    
    $sql = "
        SELECT
            to_char(equip_parts.mac_no, 'FM0000')
                                    AS mac_no           ,
            CASE
                WHEN equip_parts.mac_no = 0 THEN '���ѥǡ���(�����)'
                ELSE mac_master.mac_name
            END                     AS mac_name         ,
            equip_parts.item_code   AS item_code        ,
            miitem.midsc            AS item_name        ,
            miitem.mzist            AS zai              ,
            equip_parts.size        AS size             ,
            equip_parts.use_item    AS use_item         ,
            equip_parts.abandonment AS abandonment
        FROM
            equip_parts
        LEFT OUTER JOIN miitem ON (equip_parts.item_code = miitem.mipn)
        LEFT OUTER JOIN equip_machine_master2 AS mac_master USING (mac_no)
        WHERE
            equip_parts.item_code = '" . pg_escape_string ($Parts['Code']) . "'
            AND equip_parts.mac_no = " . pg_escape_string ($Parts['MacNo']) . " 
    ";

    $rs = pg_query ($con , $sql);
    
    if ($row = pg_fetch_array ($rs)) {
        // �ͤγ�Ǽ
        $Parts['MacNo']   = $row['mac_no'];
        $Parts['MacName'] = $row['mac_name'];
        $Parts['Code']    = $row['item_code'];
        $Parts['Name']    = $row['item_name'];
        $Parts['Zai']     = $row['zai'];
        $Parts['Size']    = $row['size'];
        $Parts['UseItem'] = $row['use_item'];
        $Parts['Abandonment']  = $row['abandonment'];
    } else {
        // �����ƥ२�顼
        $SYSTEM_MESSAGE = "�ǡ����μ����˼��Ԥ��ޤ�����\n$sql";
        require_once ('../com/'.ERROR_PAGE);
        exit();
    }
   
}
// --------------------------------------------------
// �ǡ����κ��      
// --------------------------------------------------
function DeleteData()
{
    global $Parts;
    
    // ���ͥ������μ���
    $con = getConnection();
    pg_query ($con , 'BEGIN');
    
    $sql = "DELETE FROM equip_parts WHERE item_code='" . pg_escape_string ($Parts['Code']) . "' AND mac_no={$Parts['MacNo']}";
    echo($sql);
    if (!pg_query ($con , $sql)) {
        // �����ƥ२�顼
        pg_query ($con , 'ROLLBACK');
        $SYSTEM_MESSAGE = "�ǡ����١����ι����˼��Ԥ��ޤ���\n$sql";
        require_once ('../com/' . ERROR_PAGE);
        exit();
    }
    
    pg_query ($con , 'COMMIT');
}
// --------------------------------------------------
// �����ֹ��select�ǡ�������
// --------------------------------------------------
function getMacNoSelectData($mac_no)
{
    if (isset($_SESSION['factory'])) $factory = $_SESSION['factory']; else $factory = '';
    if ($factory != '') {
        $query = "
            SELECT to_char(mac_no, 'FM0000'), substr(mac_name, 1, 10) FROM equip_machine_master2 WHERE survey = 'Y' AND factory = '{$factory}'
        ";
    } else {
        $query = "
            SELECT to_char(mac_no, 'FM0000'), substr(mac_name, 1, 10) FROM equip_machine_master2 WHERE survey = 'Y'
        ";
    }
    // �����
    $option = "\n";
    $res = array();
    $rows = getResult2($query, $res);
    if ($mac_no == '0000') {
        $option .= "<option value='0000' selected>0000 ���ѥǡ���</option>\n";
    } else {
        $option .= "<option value='0000'>0000 ���ѥǡ���</option>\n";
    }
    for ($i=0; $i<$rows; $i++) {
        if ($mac_no == $res[$i][0]) {
            $option .= "<option value='{$res[$i][0]}' selected>{$res[$i][0]} {$res[$i][1]}</option>\n";
        } else {
            $option .= "<option value='{$res[$i][0]}'>{$res[$i][0]} {$res[$i][1]}</option>\n";
        }
    }
    return $option;
}
// --------------------------------------------------
// �����ֹ��select�ǡ�������
// --------------------------------------------------
function getMachineName($mac_no)
{
    if ($mac_no == '0000') return '���ѥǡ���(�����)';
    $query = "
        SELECT mac_name FROM equip_machine_master2 WHERE mac_no = {$mac_no}
    ";
    $mac_name = '';
    getUniResult($query, $mac_name);
    return $mac_name;
}

ob_end_flush();
