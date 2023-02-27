<?php
//////////////////////////////////////////////////////////////////////////////
// ������������ƥ� ������˥塼                             MVC Model ��   //
// Copyright (C) 2007-2008 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/11/14 Created   punchMark_search_Model.php                          //
// 2007/11/15 ��������ֿ�ɽ��                                              //
// 2008/09/03 BODY��ɽ����Ĵ��                                         ��ë //
//////////////////////////////////////////////////////////////////////////////
require_once ('../../../daoInterfaceClass.php');    // TNK ������ DAO���󥿡��ե��������饹


/*****************************************************************************************
*       MVC��Model�� ���饹��� daoInterfaceClass(base class) ���쥯�饹���ĥ           *
*****************************************************************************************/
class PunchMarkSearch_Model extends daoInterfaceClass
{
    ///// Private properties
    private $where;                             // ���� SQL��WHERE��
    private $order;                             // ���� SQL��ORDER��
    private $offset;                            // ���� SQL��OFFSET��
    private $limit;                             // ���� SQL��LIMIT��
    private $sql;                               // ���� SQLʸ
    private $shapeCode;                         // shape_code��<select><option>�ǡ���
    private $sizeCode;                          // size_code��<select><option>�ǡ���
    private $makeFlg;                           // make_flg��<select><option>�ǡ���
    
    ///// public properties
    // public  $graph;                             // GanttChart�Υ��󥹥���
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer ����� (php5�ذܹԻ��� __construct() ���ѹ�ͽ��) (�ǥ��ȥ饯��__destruct())
    public function __construct()
    {
        ///// Properties �ν����
        $this->where  = '';
        $this->order  = '';
        $this->offset = 'OFFSET 0';
        $this->limit  = 'LIMIT 500';
        $this->sql    = '';
        $this->shapeCode = '';
        $this->sizeCode  = '';
        $this->makeFlg   = '';
    }
    
    ///// SQL��WHERE�������
    public function setWhere($session)
    {
        $this->where = $this->setWhereBody($session);
    }
    
    ///// SQLʸ������
    public function setSQL($session)
    {
        $this->sql = $this->setSQLbody($session);
    }
    
    ///// �����ޥ�������HTML <select> option �ν���
    public function getShapeCodeOptions($session)
    {
        if ($this->shapeCode == '') {
            $this->shapeCode = $this->getShapeCodeOptionsBody($session);
        }
        return $this->shapeCode;
    }
    
    ///// �������ޥ�������HTML <select> option �ν���
    public function getSizeCodeOptions($session)
    {
        if ($this->sizeCode == '') {
            $this->sizeCode = $this->getSizeCodeOptionsBody($session);
        }
        return $this->sizeCode;
    }
    
    ///// ���������HTML <select> option �ν���
    public function getMakeFlgOptions($session)
    {
        if ($this->makeFlg == '') {
            $this->makeFlg = $this->getMakeFlgOptionsBody($session);
        }
        return $this->makeFlg;
    }
    
    ////////// MVC �� Model ���η�� ɽ���ѤΥǡ�������
    ///// List��    �ǡ��������� ����ɽ
    public function outViewListHTML($session, $menu)
    {
                /***** �إå���������� *****/
        /*****************
        $this->outViewHTMLheader($session);
        *****************/
        
                /***** ��ʸ����� *****/
        $this->outViewHTMLbody($session, $menu);
        
                /***** �եå���������� *****/
        /************************
        $this->outViewHTMLfooter($session);
        ************************/
        return ;
    }
    
    ///// ����Υ����Ȥ���¸
    public function commentSave($request)
    {
        // �����ȤΥѥ�᡼���������å�(���Ƥϥ����å��Ѥ�)
        // if ($request->get('comment') == '') return;  // �����Ԥ��Ⱥ���Ǥ��ʤ�
        if ($request->get('targetPlanNo') == '') return;
        if ($request->get('targetAssyNo') == '') return;
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        $query = "SELECT comment FROM punchMark_search_comment WHERE plan_no='{$request->get('targetPlanNo')}'";
        if ($this->getUniResult($query, $comment) < 1) {
            $sql = "
                INSERT INTO punchMark_search_comment (assy_no, plan_no, comment, last_date, last_host)
                values ('{$request->get('targetAssyNo')}', '{$request->get('targetPlanNo')}', '{$request->get('comment')}', '{$last_date}', '{$last_host}')
            ";
            if ($this->query_affected($sql) <= 0) {
                $_SESSION['s_sysmsg'] = "�����Ȥ���¸������ޤ���Ǥ�����������ô���Ԥ�Ϣ���Ʋ�������";
            }
        } else {
            $sql = "
                UPDATE punchMark_search_comment SET comment='{$request->get('comment')}',
                last_date='{$last_date}', last_host='{$last_host}'
                WHERE plan_no='{$request->get('targetPlanNo')}'
            ";
            if ($this->query_affected($sql) <= 0) {
                $_SESSION['s_sysmsg'] = "�����Ȥ���¸������ޤ���Ǥ�����������ô���Ԥ�Ϣ���Ʋ�������";
            }
        }
        return ;
    }
    
    ///// ����Υ����Ȥ����
    public function getComment($request, $result)
    {
        // �����ȤΥѥ�᡼���������å�(���Ƥϥ����å��Ѥ�)
        if ($request->get('targetAssyNo') == '') return '';
        $query = "
            SELECT  comment  ,
                    trim(substr(midsc, 1, 20))
            FROM miitem LEFT OUTER JOIN
            punchMark_search_comment ON(mipn=assy_no)
            WHERE mipn='{$request->get('targetAssyNo')}'
        ";
        $res = array();
        if ($this->getResult2($query, $res) > 0) {
            $result->add('comment', $res[0][0]);
            $result->add('assy_name', $res[0][1]);
            $result->add('title', "{$request->get('targetPlanNo')}��{$request->get('targetAssyNo')}��{$res[0][1]}");
            return true;
        } else {
            return false;
        }
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// �ꥯ�����Ȥˤ��SQLʸ��WHERE�������
    protected function setWhereBody($session)
    {
        $where = '';
        if ($session->get_local('parts_no') != '') {
            $where .= "WHERE parts_no LIKE '%{$session->get_local('parts_no')}%'";
        }
        if ($session->get_local('punchMark_code') != '' && $where != '') {
            $where .= " AND punchMark_code LIKE '%{$session->get_local('punchMark_code')}%'";
        } elseif ($session->get_local('punchMark_code') != '') {
            $where .= "WHERE punchMark_code LIKE '%{$session->get_local('punchMark_code')}%'";
        }
        if ($session->get_local('shelf_no') != '' && $where != '') {
            $where .= " AND shelf_no LIKE '%{$session->get_local('shelf_no')}%'";
        } elseif ($session->get_local('shelf_no') != '') {
            $where .= "WHERE shelf_no LIKE '%{$session->get_local('shelf_no')}%'";
        }
        if ($session->get_local('mark') != '' && $where != '') {
            $where .= " AND mark LIKE '%{$session->get_local('mark')}%'";
        } elseif ($session->get_local('mark') != '') {
            $where .= "WHERE mark LIKE '%{$session->get_local('mark')}%'";
        }
        if ($session->get_local('shape_code') != '' && $where != '') {
            $where .= " AND shape_code LIKE '%{$session->get_local('shape_code')}%'";
        } elseif ($session->get_local('shape_code') != '') {
            $where .= "WHERE shape_code LIKE '%{$session->get_local('shape_code')}%'";
        }
        if ($session->get_local('size_code') != '' && $where != '') {
            $where .= " AND size_code LIKE '%{$session->get_local('size_code')}%'";
        } elseif ($session->get_local('size_code') != '') {
            $where .= "WHERE size_code LIKE '%{$session->get_local('size_code')}%'";
        }
        if ($session->get_local('user_code') != '' && $where != '') {
            $where .= " AND user_code LIKE '%{$session->get_local('user_code')}%'";
        } elseif ($session->get_local('user_code') != '') {
            $where .= "WHERE user_code LIKE '%{$session->get_local('user_code')}%'";
        }
        if ($session->get_local('make_flg') != '' && $where != '') {
            $where .= " AND make_flg = '{$session->get_local('make_flg')}'";
        } elseif ($session->get_local('make_flg') != '') {
            $where .= "WHERE make_flg = '{$session->get_local('make_flg')}'";
        }
        if ($session->get_local('note_parts') != '' && $where != '') {
            $where .= " AND parts.note LIKE '%{$session->get_local('note_parts')}%'";
        } elseif ($session->get_local('note_parts') != '') {
            $where .= "WHERE parts.note LIKE '%{$session->get_local('note_parts')}%'";
        }
        if ($session->get_local('note_mark') != '' && $where != '') {
            $where .= " AND mark.note LIKE '%{$session->get_local('note_mark')}%'";
        } elseif ($session->get_local('note_mark') != '') {
            $where .= "WHERE mark.note LIKE '%{$session->get_local('note_mark')}%'";
        }
        if ($session->get_local('note_shape') != '' && $where != '') {
            $where .= " AND shape.note LIKE '%{$session->get_local('note_shape')}%'";
        } elseif ($session->get_local('note_shape') != '') {
            $where .= "WHERE shape.note LIKE '%{$session->get_local('note_shape')}%'";
        }
        if ($session->get_local('note_size') != '' && $where != '') {
            $where .= " AND size.note LIKE '%{$session->get_local('note_size')}%'";
        } elseif ($session->get_local('note_size') != '') {
            $where .= "WHERE size.note LIKE '%{$session->get_local('note_size')}%'";
        }
        return $where;
    }
    
    ///// �����ޥ�������HTML <select> option �ν���
    protected function getShapeCodeOptionsBody($session)
    {
        $query = "SELECT shape_code, shape_name FROM punchMark_shape_master ORDER BY shape_code ASC";
        $res = array();
        if (($rows=getResult2($query, $res)) <= 0) return '';
        $options = "\n";
        $options .= "<option value='' style='color:red;'>̤����</option>\n";
        for ($i=0; $i<$rows; $i++) {
            if ($session->get_local('shape_code') == $res[$i][0]) {
                $options .= "<option value='{$res[$i][0]}' selected>{$res[$i][1]}</option>\n";
            } else {
                $options .= "<option value='{$res[$i][0]}'>{$res[$i][1]}</option>\n";
            }
        }
        return $options;
    }
    
    ///// �������ޥ�������HTML <select> option �ν���
    protected function getSizeCodeOptionsBody($session)
    {
        $query = "SELECT size_code, size_name FROM punchMark_size_master ORDER BY size_code ASC";
        $res = array();
        if (($rows=getResult2($query, $res)) <= 0) return '';
        $options = "\n";
        $options .= "<option value='' style='color:red;'>̤����</option>\n";
        for ($i=0; $i<$rows; $i++) {
            if ($session->get_local('size_code') == $res[$i][0]) {
                $options .= "<option value='{$res[$i][0]}' selected>{$res[$i][1]}</option>\n";
            } else {
                $options .= "<option value='{$res[$i][0]}'>{$res[$i][1]}</option>\n";
            }
        }
        return $options;
    }
    
    ///// ���������HTML <select> option �ν���
    protected function getMakeFlgOptionsBody($session)
    {
        $options = "\n";
        $options .= "<option value='' style='color:red;'>̤����</option>\n";
        if ($session->get_local('make_flg') == 'f') {
            $options .= "<option value='f' selected>�����</option>\n";
        } else {
            $options .= "<option value='f'>�����</option>\n";
        }
        if ($session->get_local('make_flg') == 't') {
            $options .= "<option value='t' selected>������</option>\n";
        } else {
            $options .= "<option value='t'>������</option>\n";
        }
        return $options;
    }
    
    ///// ���٤ν��� �إå�����
    protected function outViewHTMLheader($session)
    {
        // �����HTML�����������
        $headHTML  = $this->getViewHTMLconst('header');
        // ��������HTML�����������
        $headHTML .= $this->getViewHTMLheader();
        // �����HTML�����������
        $headHTML .= $this->getViewHTMLconst('footer');
        // HTML�ե��������
        $file_name = "list/punchMark_search_ViewListHeader-{$session->get('User_ID')}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $headHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
    }
    
    ///// ���٤ν��� �ܥǥ�����
    protected function outViewHTMLbody($session, $menu)
    {
        // �����HTML�����������
        $listHTML  = $this->getViewHTMLconst('header');
        // ��������HTML�����������
        $listHTML .= $this->getViewHTMLbody($session, $menu);
        // �����HTML�����������
        $listHTML .= $this->getViewHTMLconst('footer');
        // HTML�ե��������
        $file_name = "list/punchMark_search_ViewList-{$session->get('User_ID')}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
    }
    
    ///// ���٤ν��� �եå�����
    protected function outViewHTMLfooter($session)
    {
        // �����HTML�����������
        $footHTML  = $this->getViewHTMLconst('header');
        // ��������HTML�����������
        $footHTML .= $this->getViewHTMLfooter();
        // �����HTML�����������
        $footHTML .= $this->getViewHTMLconst('footer');
        // HTML�ե��������
        $file_name = "list/punchMark_search_ViewListFooter-{$session->get('User_ID')}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $footHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ///// List��   ����ɽ��SQL���ơ��ȥ�������
    // ���ʥޥ�����������ޥ������������ޥ��������������ޥ������� SQL
    private function setSQLbody($session)
    {
        $query = "
            SELECT
                parts.parts_no                      AS �����ֹ�     -- 0
                ,
                substr(midsc, 1, 10)                AS ����̾       -- 1
                ,
                parts.punchMark_code                AS ���������   -- 2
                ,
                shelf_no                            AS ê��         -- 3
                ,
                mark                                AS �������     -- 4
                ,
                shape_name                          AS ����̾       -- 5
                ,
                -- COALESCE(user_code, '&nbsp;') -- ��������֤�NULL�Ǥʤ��ǽ���ͤ��֤�
                CASE
                    WHEN user_code = '' THEN '&nbsp;'
                    ELSE user_code
                END                                 AS ���襳����   -- 6
                ,
                size_name                           AS ������̾     -- 7
                ,
                CASE WHEN make_flg IS TRUE THEN '������'
                     ELSE '�����'
                END                                 AS  �������    -- 8
                ,
                CASE
                    WHEN parts.note = '' THEN '&nbsp;'
                    ELSE parts.note
                END                                 AS note_parts   -- 9
                ,
                CASE
                    WHEN mark.note = '' THEN '&nbsp;'
                    ELSE mark.note
                END                                 AS note_mark    --10
                ,
                CASE
                    WHEN shape.note = '' THEN '&nbsp;'
                    ELSE shape.note
                END                                 AS note_shape   --11
                ,
                CASE
                    WHEN size.note = '' THEN '&nbsp;'
                    ELSE size.note
                END                                 AS note_size    --12
            FROM
                punchMark_parts_master  AS parts
            LEFT OUTER JOIN
                miitem ON (parts_no = mipn)
            LEFT OUTER JOIN
                punchMark_master        AS mark  USING (punchmark_code)
            LEFT OUTER JOIN
                punchMark_shape_master  AS shape USING (shape_code)
            LEFT OUTER JOIN
                punchMark_size_master   AS size  USING (size_code)
            {$this->where}
            {$this->order}
            {$this->offset}
            {$this->limit}
        ";
        return $query;
    }
    
    ///// List��   ������̤� ���٥ǡ�������
    private function getViewHTMLbody($session, $menu)
    {
        if ($this->sql == '') exit();
        $res = array();
        if ( ($rows=$this->getResult2($this->sql, $res)) <= 0) {
            // $session->add('s_sysmsg', '�оݥǡ���������ޤ���');
        }
        
        // �����
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        if ($rows <= 0) {
            $listTable .= "    <tr>\n";
            $listTable .= "        <td width='100%' align='center' class='winbox'>�оݥǡ���������ޤ���</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        } else {
            for ($i=0; $i<$rows; $i++) {
                $tmpMark = str_replace("\n", '<br>', $res[$i][4]);
                $tmpMark = str_replace("\r", '', $tmpMark);
                /*****
                if ($res[$i][10] != '') {   // �����Ȥ�����п����Ѥ���
                    $listTable .= "    <tr onDblClick='PunchMarkSearch.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPlanNo=" . urlencode($res[$i][0]) . "&targetAssyNo=" . urlencode($res[$i][1]) . "\", 600, 235)' title='�����Ȥ���Ͽ����Ƥ��ޤ������֥륯��å��ǥ����ȤξȲ��Խ�������ޤ���' style='background-color:#e6e6e6;'>\n";
                } else {
                    $listTable .= "    <tr onDblClick='PunchMarkSearch.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPlanNo=" . urlencode($res[$i][0]) . "&targetAssyNo=" . urlencode($res[$i][1]) . "\", 600, 235)' title='���ߥ����Ȥ���Ͽ����Ƥ��ޤ��󡣥��֥륯��å��ǥ����ȤξȲ��Խ�������ޤ���'>\n";
                }
                *****/
                $listTable .= "    <tr>\n";
                $listTable .= "        <font size='2'><td class='winbox' width=' 5%' align='right' >" . ($i+1) . "</td>\n";    // ���ֹ�
                // $listTable .= "        <td class='winbox' width=' 8%' align='right' >\n";
                // $listTable .= "            <a href='javascript:win_open(\"{$menu->out_self()}?Action=ListDetails&showMenu=ListWin&targetUid={$res[$i][0]}\");'>����</a>\n";
                // $listTable .= "        </td>\n"; // ���٥���å���
                $listTable .= "        <td class='winbox' width='10%' align='center'>{$res[$i][0]}</td>\n";     // �����ֹ�
                $listTable .= "        <td class='winbox' width='16%' align='left'  >{$res[$i][1]}</td>\n";     // ����̾
                $listTable .= "        <td class='winbox' width=' 7%' align='center'>{$res[$i][2]}</td>\n";     // ���������
                $listTable .= "        <td class='winbox' width=' 7%' align='center'>{$res[$i][3]}</td>\n";     // ê��
                $listTable .= "        <td class='winbox' width='23%' align='center'>{$tmpMark}   </td>\n";     // �������
                $listTable .= "        <td class='winbox' width=' 6%' align='center'>{$res[$i][5]}</td>\n";     // ����̾
                $listTable .= "        <td class='winbox' width='10%' align='center'>{$res[$i][6]}</td>\n";     // ����
                $listTable .= "        <td class='winbox' width=' 6%' align='center'>{$res[$i][7]}</td>\n";     // ������̾
                if ($res[$i][8] == '������') {
                    $listTable .= "        <td class='winbox' width='10%' align='center' style='color:red;'>{$res[$i][8]}</td>\n";// �������
                } else {
                    $listTable .= "        <td class='winbox' width='10%' align='center'>{$res[$i][8]}</td>\n";     // �������
                }
                $listTable .= "    </font></tr>\n";
                $listTable .= "    <tr>\n";
                $listTable .= "        <font size='2'><td class='winbox' width=' 5%' align='left' colspan='1'>&nbsp;</td>\n";          // �����ܤΥǡ���
                $listTable .= "        <td class='winbox' width='26%' align='left' colspan='2'>{$res[$i][9]}</td>\n";   // ���ʥޥ���������
                $listTable .= "        <td class='winbox' width='37%' align='left' colspan='3'>{$res[$i][10]}</td>\n";  // ����ޥ���������
                $listTable .= "        <td class='winbox' width='16%' align='left' colspan='2'>{$res[$i][11]}</td>\n";  // �����ޥ���������
                $listTable .= "        <td class='winbox' width='16%' align='left' colspan='2'>{$res[$i][12]}</td>\n";  // �������ޥ���������
                $listTable .= "    </font></tr>\n";
            }
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        }
        // return mb_convert_encoding($listTable, 'UTF-8');
        return $listTable;
    }
    
    ///// List��   ����ɽ�� �إå����������
    private function getViewHTMLheader()
    {
        // �����
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <th class='winbox' colspan='11'>�������</th>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <th class='winbox' width=' 5%'>No</th>\n";
        $listTable .= "        <th class='winbox' width='11%'>�����ֹ�</th>\n";
        $listTable .= "        <th class='winbox' width='18%'>����̾</th>\n";
        $listTable .= "        <th class='winbox' width='10%'>���������</th>\n";
        $listTable .= "        <th class='winbox' width=' 8%'>ê����</th>\n";
        $listTable .= "        <th class='winbox' width='10%'>�������</th>\n";
        $listTable .= "        <th class='winbox' width=' 6%'>������</th>\n";
        $listTable .= "        <th class='winbox' width='13%'>�ҡ���</th>\n";
        $listTable .= "        <th class='winbox' width=' 6%'>������</th>\n";
        $listTable .= "        <th class='winbox' width='13%'>�������</th>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <th class='winbox' width=' 5%' colspan='1'>&nbsp;</th>\n";
        $listTable .= "        <th class='winbox' width='29%' colspan='2'>���ʥޥ���������</th>\n";
        $listTable .= "        <th class='winbox' width='28%' colspan='3'>����ޥ���������</th>\n";
        $listTable .= "        <th class='winbox' width='19%' colspan='2'>�����ޥ���������</th>\n";
        $listTable .= "        <th class='winbox' width='19%' colspan='2'>�������ޥ���������</th>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        return $listTable;
    }
    
    ///// List��   ����ɽ�� �եå����������
    private function getViewHTMLfooter()
    {
        // �����
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td class='winbox' width='79%' align='right'>��׷��</td>\n";
        $listTable .= "        <td class='winbox' width=' 9%' align='right'>{$this->sumCount}</td>\n";
        $listTable .= "        <td class='winbox' width='12%' align='right'>&nbsp;</td>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        return $listTable;
    }
    
    ///// �����List��    HTML�ե��������
    private function getViewHTMLconst($status)
    {
        if ($status == 'header') {
            $listHTML = 
"
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=EUC-JP'>
<meta http-equiv='Content-Style-Type' content='text/css'>
<meta http-equiv='Content-Script-Type' content='text/javascript'>
<title>������������ƥม�����</title>
<script type='text/javascript' src='/base_class.js'></script>
<link rel='stylesheet' href='/menu_form.css' type='text/css' media='screen'>
<link rel='stylesheet' href='../punchMark_search.css' type='text/css' media='screen'>
<style type='text/css'>
<!--
body {
    background-image:none;
}
-->
</style>
<script type='text/javascript' src='../punchMark_search.js'></script>
</head>
<body style='background-color:#d6d3ce;'>  <!--  -->
<center>
";
        } elseif ($status == 'footer') {
            $listHTML = 
"
</center>
</body>
</html>
";
        } else {
            $listHTML = '';
        }
        return $listHTML;
    }
    
    
} // Class PunchMarkSearch_Model End

?>
