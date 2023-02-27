<?php
//////////////////////////////////////////////////////////////////////////////
// ���� ���� �ط��ơ��֥� ���ƥʥ�                       MVC Model ��   //
// Copyright (C) 2006-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/07/26 Created   common_authority_Model.php                          //
// 2006/09/06 ����̾�ν�����ǽ�ɲä�ȼ�� Edit/UpdateDivision  �ط����ɲ�    //
// 2006/10/03 categorySelectList()�᥽�åɤ� ORDER BY category ASC ���ɲ�   //
//            getIDName(), getViewListID()�᥽�åɤ����祳����[3]���ɲ�     //
// 2006/10/04 ���С�(ID)�Υ����ȥ�� ����No.?? ��ɽ���ɲ�                 //
// 2007/01/16 category=4(���¥�٥�)���ɲ� getViewListID(), getIDName() �ѹ�//
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
if (_TNK_DEBUG) access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));

// require_once ('../../daoInterfaceClass.php');   // TNK ������ DAO���󥿡��ե��������饹
require_once ('../../ComTableMntClass.php');    // TNK ������ �ơ��֥����&�ڡ�������Class


/*****************************************************************************************
*                   MVC��Model�� ���饹��� ComTableMnt ���饹���ĥ                     *
*****************************************************************************************/
class CommonAuthority_Model extends ComTableMnt
{
    ///// Private properties
    private $where;                             // ���� SQL��WHERE��
    
    private $authDiv = 1;                       // ���Υӥ��ͥ����å��θ��¶�ʬ
    
    ///// Public properties
    // public  $graph;                             // GanttChart�Υ��󥹥���
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer ����� (php5�ذܹԻ��� __construct() ���ѹ�ͽ��) (�ǥ��ȥ饯��__destruct())
    public function __construct($request)
    {
        switch ($request->get('Action')) {
        case 'ListDivBody':     // ���¥ޥ�����
            $where  = '';
            $sql_sum = "
                SELECT count(*) FROM common_auth_master $where
            ";
            break;
        case 'ListIDBody':      // ���¥��С�
            $where  = "WHERE division={$request->get('targetDivision')}";
            $sql_sum = "
                SELECT count(*) FROM common_authority $where
            ";
            break;
        case 'AddDivision':     // ���¥ޥ��������ɲ�
            $query = "SELECT max(division)+1 FROM common_auth_master";
            $this->getUniResult($query, $div);
            if ($div == '') $div = 1;   // ���ξ��
            $request->add('targetDivision', $div);
        case 'DeleteDivision':  // ���¥ޥ������κ��
        case 'EditDivision':    // ����̾�ν���
        case 'UpdateDivision':  // ����̾�ν�����Ͽ
            $where  = "WHERE division={$request->get('targetDivision')}";
            $sql_sum = "
                SELECT count(*) FROM common_auth_master $where
            ";
            break;
        case 'AddID':           // ���¥��С����ɲ�
        case 'DeleteID':        // ���¥��С��κ��
            $where  = "WHERE id='{$request->get('targetID')}' AND division={$request->get('targetDivision')}";
            $sql_sum = "
                SELECT count(*) FROM common_authority $where
            ";
            break;
        default:
            return;
        }
        ///// SQLʸ��WHERE���Properties����Ͽ
        $this->where  = $where;
        ///// log file �λ���
        $log_file = 'common_authority.log';
        ///// ���ڡ����ιԿ� ����� ����
        $page = 200;    // ����ϥڡ�������Ϥ��ʤ�����¿�������
        ///// Constructer ���������� ���쥯�饹�� Constructer���¹Ԥ���ʤ�
        ///// ����Class��Constructer�ϥץ���ޡ�����Ǥ�ǸƽФ�
        parent::__construct($sql_sum, $request, $log_file, $page);
    }
    
    ///// ���¥ޥ������Υꥹ�Ȥ����
    public function getViewListDivision($request, &$res)
    {
        // �����
        $res = array();
        $query = "
            SELECT division, auth_name FROM common_auth_master {$this->where} ORDER BY division ASC
        ";
        return $this->execute_ListNotPageControl($query, $res);
    }
    
    ///// ���¥ޥ������λ��� ����̾�����
    public function getViewDivisionName($request)
    {
        // �����
        $authName = '';
        $query = "
            SELECT auth_name FROM common_auth_master WHERE division={$request->get('targetDivision')}
        ";
        $this->getUniResult($query, $authName);
        $authName = '����No.' . $request->get('targetDivision') . ' &nbsp; ' . $authName;
        return mb_convert_encoding($authName, 'UTF-8', 'EUC-JP');
    }
    
    ///// ���¥��С��Υꥹ�Ȥ����
    public function getViewListID($request, &$res)
    {
        // �����
        $res = array();
        $query = "
            SELECT
                    auth.id ,   -- 00
                CASE
                    WHEN category = 1 THEN (SELECT trim(name) FROM user_detailes WHERE uid=auth.id)
                    WHEN category = 3 THEN (SELECT trim(act_name) FROM act_table WHERE act_id=to_number(auth.id, '999'))
                    WHEN category = 4 THEN (SELECT trim(authority_name) FROM authority_master WHERE aid=to_number(auth.id, '999'))
                    ELSE auth.id
                END         ,   -- 01
                master.cate_name
                            ,   -- 02
                -----------------------------�ʲ��ϥꥹ�ȳ�--------------------
                cate.category,  -- 03
                auth.division   -- 04
            FROM
                common_authority AS auth
            LEFT OUTER JOIN
                common_auth_category AS cate USING(id)
            LEFT OUTER JOIN
                common_auth_category_master AS master USING(category)
            {$this->where}
            ORDER BY auth.id ASC
        ";
        $rows = $this->execute_ListNotPageControl($query, $res);
        for ($i=0; $i<$rows; $i++) {
            if ($res[$i][3] == 2) {
                $res[$i][1] = gethostbyaddr($res[$i][0]);
            }
            if ($res[$i][1] == '') $res[$i][1] = $res[$i][0];
        }
        return $rows;
    }
    
    ///// ���¥ޥ��������ɲ�
    public function addDivision($request)
    {
        // ��Ͽ�ѤߤΥ����å�
        $query = "
            SELECT division FROM common_auth_master WHERE auth_name='{$request->get('targetAuthName')}'
        ";
        if ($this->getUniResult($query, $check) > 0) {
            $_SESSION['s_sysmsg'] = "{$request->get('targetAuthName')}\\n\\n�ϴ�����Ͽ����Ƥ��ޤ���";
            return false;
        }
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        $sql = "
            INSERT INTO common_auth_master (division, auth_name, last_date, last_user)
            VALUES ('{$request->get('targetDivision')}', '{$request->get('targetAuthName')}', '{$last_date}', '{$last_user}')
        ";
        if ($this->execute_Insert($sql)) {
            $_SESSION['s_sysmsg'] = '���¥ޥ��������ɲä��ޤ�����';
        } else {
            $_SESSION['s_sysmsg'] = '���¥ޥ��������ɲä˼��Ԥ��ޤ�����';
        }
    }
    
    ///// ���¥ޥ������κ��
    public function deleteDivision($request)
    {
        // ��Ͽ�ѤߤΥ����å�
        $query = "
            SELECT id FROM common_authority WHERE division={$request->get('targetDivision')} LIMIT 1
        ";
        if ($this->getUniResult($query, $check) > 0) {
            $_SESSION['s_sysmsg'] = "����No. {$request->get('targetDivision')} �ϥ��С�����Ͽ����Ƥ��ޤ���\\n\\n��˥��С��������Ʋ�������";
            return false;
        }
        $sql = "
            DELETE FROM common_auth_master {$this->where}
        ";
        $save_sql = "
            SELECT * FROM common_auth_master {$this->where}
        ";
        if ($this->execute_Delete($sql, $save_sql)) {
            $_SESSION['s_sysmsg'] = '���¥ޥ������������ޤ�����';
        } else {
            $_SESSION['s_sysmsg'] = '���¥ޥ������κ���˼��Ԥ��ޤ�����';
        }
    }
    
    ///// ����̾�ν���
    public function editDivision($request, $result)
    {
        // ��Ͽ�ѤߤΥ����å�
        $query = "
            SELECT auth_name FROM common_auth_master {$this->where}
        ";
        if ($this->getUniResult($query, $check) <= 0) {
            $_SESSION['s_sysmsg'] = "����No. {$request->get('targetDivision')} ����Ͽ����Ƥ��ޤ���\\n\\n�ۤ��Υ桼��������˺�������Ȼפ��ޤ���";
            return false;
        } else {
            $result->add('division', $request->get('targetDivision'));
            $result->add('auth_name', $check);
        }
        return true;
    }
    
    ///// ����̾�ν�����Ͽ
    public function updateDivision($request, $result)
    {
        // ��Ͽ�ѤߤΥ����å�
        $query = "
            SELECT auth_name FROM common_auth_master {$this->where}
        ";
        if ($this->getUniResult($query, $check) <= 0) {
            $_SESSION['s_sysmsg'] = "����No. {$request->get('targetDivision')} ����Ͽ����Ƥ��ޤ���\\n\\n�ۤ��Υ桼��������˺�������Ȼפ��ޤ���";
            return false;
        } else {
            $last_date = date('Y-m-d H:i:s');
            $last_user = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
            $sql = "
                UPDATE common_auth_master SET auth_name='{$request->get('targetAuthName')}',
                    last_date='{$last_date}', last_user='{$last_user}'
                {$this->where}
            ";
            $save_sql = "
                SELECT * FROM common_auth_master {$this->where}
            ";
            if ($this->execute_Update($sql, $save_sql)) {
                $_SESSION['s_sysmsg'] = '����̾���ѹ����ޤ�����';
            } else {
                $_SESSION['s_sysmsg'] = '����̾���ѹ��˼��Ԥ��ޤ�����';
            }
        }
        return true;
    }
    
    ///// ���¥��С����ɲ�
    public function addID($request)
    {
        // ��Ͽ�ѤߤΥ����å�
        $query = "
            SELECT id FROM common_authority {$this->where}
        ";
        if ($this->getUniResult($query, $check) > 0) {
            $_SESSION['s_sysmsg'] = "{$request->get('targetID')} �ϴ�����Ͽ����Ƥ��ޤ���";
            return false;
        }
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        $query = "
            SELECT category FROM common_auth_category WHERE id='{$request->get('targetID')}'
        ";
        if ($this->getUniResult($query, $check) <= 0) {
            $sql = "
            INSERT INTO common_authority (id, division, last_date, last_user)
            VALUES ('{$request->get('targetID')}', '{$request->get('targetDivision')}', '{$last_date}', '{$last_user}')
            ;
            INSERT INTO common_auth_category (id, category, last_date, last_user)
            VALUES ('{$request->get('targetID')}', {$request->get('targetCategory')}, '{$last_date}', '{$last_user}')
            ";
        } else {
            $sql = "
            INSERT INTO common_authority (id, division, last_date, last_user)
            VALUES ('{$request->get('targetID')}', '{$request->get('targetDivision')}', '{$last_date}', '{$last_user}')
            ;
            UPDATE common_auth_category SET category={$request->get('targetCategory')}
            WHERE id='{$request->get('targetID')}'
            ";
        }
        if ($this->execute_Insert($sql)) {
            $_SESSION['s_sysmsg'] = '���¥��С����ɲä��ޤ�����';
            return true;
        } else {
            $_SESSION['s_sysmsg'] = '���¥��С����ɲä˼��Ԥ��ޤ�����';
            return false;
        }
    }
    
    ///// ���¥��С��κ��
    public function deleteID($request)
    {
        $sql = "
            DELETE FROM common_authority {$this->where}
        ";
        $save_sql = "
            SELECT * FROM common_authority {$this->where}
        ";
        if ($this->execute_Delete($sql, $save_sql)) {
            $_SESSION['s_sysmsg'] = '���¥��С��������ޤ�����';
        } else {
            $_SESSION['s_sysmsg'] = '���¥��С��κ���˼��Ԥ��ޤ�����';
        }
    }
    
    ///// ���¥��С���ID���� category ����
    public function getCategory($request)
    {
        $query = "
            SELECT category FROM common_auth_category WHERE id='{$request->get('targetID')}'
        ";
        $category = '';
        $this->getUniResult($query, $category);
        return $category;
    }
    
    ///// ���¥��С���<select>�ꥹ�Ƚ���
    public function categorySelectList($targetCategory='')
    {
        $query = "
            SELECT category, cate_name FROM common_auth_category_master ORDER BY category ASC
        ";
        $res = array();
        $rows = $this->getResult2($query, $res);
        $option = "\n";
        $option .= "<select id='targetCategory'>\n";
        if ($targetCategory == '') {
            $option .= "<option value='' selected>���򤷤Ʋ�����</option>\n";
        }
        for ($i=0; $i<$rows; $i++) {
            if ($res[$i][0] == $targetCategory) {
                $option .= "<option value='{$res[$i][0]}' selected>{$res[$i][0]}.{$res[$i][1]}</option>\n";
            } else {
                $option .= "<option value='{$res[$i][0]}'>{$res[$i][0]}.{$res[$i][1]}</option>\n";
            }
        }
        $option .= "</select>\n";
        return mb_convert_encoding($option, 'UTF-8', 'EUC-JP');
    }
    
    ///// ���¥��С��� ���� ����
    public function getIDName($request)
    {
        $idName = '';   // �����
        switch ($request->get('targetCategory')) {
        case 1:     // �Ұ��ֹ� �� �Ұ�̾ �򥻥å�
            $query = "
                SELECT trim(name) FROM user_detailes WHERE uid='{$request->get('targetID')}'
            ";
            $this->getUniResult($query, $idName);
            if ($idName == '') $idName = '̤��Ͽ';
            break;
        case 2:     // IP���ɥ쥹 �� host̾ �򥻥å�
            $idName = @gethostbyaddr($request->get('targetID'));
            if ($idName == '') $idName = 'IP���ɥ쥹�ǤϤʤ�';
            break;
        case 3:     // ���祳���� �� ����̾ �򥻥å�
            $query = "
                SELECT trim(act_name) FROM act_table WHERE act_id='{$request->get('targetID')}'
            ";
            $this->getUniResult($query, $idName);
            if ($idName == '') $idName = '̤��Ͽ';
            break;
        case 4:     // ���¥�٥� �� (0=����, 1=���, 2=���, 3=���ɥߥ�)
            $query = "
                SELECT trim(authority_name) FROM authority_master WHERE aid={$request->get('targetID')}
            ";
            $this->getUniResult($query, $idName);
            if ($idName == '') $idName = '̤��Ͽ';
            break;
        default:
            $idName = $request->get('targetID');
        }
        return mb_convert_encoding($idName, 'UTF-8', 'EUC-JP');
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    
    
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    
    
    
} // Class CommonAuthority_Model End

?>
