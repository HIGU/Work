<?php
//////////////////////////////////////////////////////////////////////////////
// �ץ���������˥塼 �ץ����θ���                   MVC Model ��   //
// Copyright (C) 2010 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2010/01/26 Created   progMaster_search_Model.php                         //
// 2010/01/27 �ץ�������Ƥȥ����Ȥθ�����郎AND���ä��Τ�OR���ѹ�     //
// 2010/06/16 �������Υ����Ƚ��ǥ��쥯�ȥ�̾�ݥץ����̾�ν���ѹ�      //
// 2010/06/21 �ǥ��쥯�ȥ��option���¤ӽ���ѹ���lower�ϻȤ��ʤ���    ��ë //
//////////////////////////////////////////////////////////////////////////////
require_once ('../../../daoInterfaceClass.php');    // TNK ������ DAO���󥿡��ե��������饹


/*****************************************************************************************
*       MVC��Model�� ���饹��� daoInterfaceClass(base class) ���쥯�饹���ĥ           *
*****************************************************************************************/
class ProgMasterSearch_Model extends daoInterfaceClass
{
    ///// Private properties
    private $where;                             // ���� SQL��WHERE��
    private $order;                             // ���� SQL��ORDER��
    private $offset;                            // ���� SQL��OFFSET��
    private $limit;                             // ���� SQL��LIMIT��
    private $sql;                               // ���� SQLʸ
    private $dir;                         // dir��<select><option>�ǡ���
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
        $this->order  = 'ORDER BY dir ASC, LOWER(p_id) ASC';
        $this->offset = 'OFFSET 0';
        $this->limit  = 'LIMIT 500';
        $this->sql    = '';
        $this->dir = '';
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
    public function getDirOptions($session)
    {
        if ($this->dir == '') {
            $this->dir = $this->getDirOptionsBody($session);
        }
        return $this->dir;
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
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// �ꥯ�����Ȥˤ��SQLʸ��WHERE�������
    protected function setWhereBody($session)
    {
        $where  = '';
        $db_flg = 0;
        if ($session->get_local('pid') != '') {
            $where .= "WHERE p_id LIKE '%{$session->get_local('pid')}%'";
        }
        if ($session->get_local('dir') != '' && $where != '') {
            $where .= " AND dir = '{$session->get_local('dir')}'";
        } elseif ($session->get_local('dir') != '') {
            $where .= "WHERE dir = '{$session->get_local('dir')}'";
        }
        if ($session->get_local('name_comm') != '' && $where != '') {
            $where .= " AND (p_name LIKE '%{$session->get_local('name_comm')}%' OR comment LIKE '%{$session->get_local('name_comm')}%')";
        } elseif ($session->get_local('name_comm') != '') {
            $where .= "WHERE (p_name LIKE '%{$session->get_local('name_comm')}%' OR comment LIKE '%{$session->get_local('name_comm')}%')";
        }
        //if ($session->get_local('name_comm') != '' && $where != '') {
        //    $where .= " AND comment LIKE '%{$session->get_local('name_comm')}%'";
        //} elseif ($session->get_local('name_comm') != '') {
        //    $where .= "WHERE comment LIKE '%{$session->get_local('name_comm')}%'";
        //}
        if ($session->get_local('db') != '' && $where != '') {
            $where .= " AND (db1 LIKE '%{$session->get_local('db')}%'";
            $db_flg = 1;
        } elseif ($session->get_local('db') != '') {
            $where .= "WHERE (db1 LIKE '%{$session->get_local('db')}%'";
            $db_flg = 1;
        }
        if ($session->get_local('db') != '' && $where != '') {
            if ($db_flg == 1) {
                $where .= " OR db2 LIKE '%{$session->get_local('db')}%'";
            } else {
                $where .= " AND (db2 LIKE '%{$session->get_local('db')}%'";
                $db_flg = 1;
            }
        }
        if ($session->get_local('db') != '' && $where != '') {
            if ($db_flg == 1) {
                $where .= " OR db3 LIKE '%{$session->get_local('db')}%'";
            } else {
                $where .= " AND (db3 LIKE '%{$session->get_local('db')}%'";
                $db_flg = 1;
            }
        }
        if ($session->get_local('db') != '' && $where != '') {
            if ($db_flg == 1) {
                $where .= " OR db4 LIKE '%{$session->get_local('db')}%'";
            } else {
                $where .= " AND (db4 LIKE '%{$session->get_local('db')}%'";
                $db_flg = 1;
            }
        }
        if ($session->get_local('db') != '' && $where != '') {
            if ($db_flg == 1) {
                $where .= " OR db5 LIKE '%{$session->get_local('db')}%'";
            } else {
                $where .= " AND (db5 LIKE '%{$session->get_local('db')}%'";
                $db_flg = 1;
            }
        }
        if ($session->get_local('db') != '' && $where != '') {
            if ($db_flg == 1) {
                $where .= " OR db6 LIKE '%{$session->get_local('db')}%'";
            } else {
                $where .= " AND (db6 LIKE '%{$session->get_local('db')}%'";
                $db_flg = 1;
            }
        }
        if ($session->get_local('db') != '' && $where != '') {
            if ($db_flg == 1) {
                $where .= " OR db7 LIKE '%{$session->get_local('db')}%'";
            } else {
                $where .= " AND (db7 LIKE '%{$session->get_local('db')}%'";
                $db_flg = 1;
            }
        }
        if ($session->get_local('db') != '' && $where != '') {
            if ($db_flg == 1) {
                $where .= " OR db8 LIKE '%{$session->get_local('db')}%'";
            } else {
                $where .= " AND (db8 LIKE '%{$session->get_local('db')}%'";
                $db_flg = 1;
            }
        }
        if ($session->get_local('db') != '' && $where != '') {
            if ($db_flg == 1) {
                $where .= " OR db9 LIKE '%{$session->get_local('db')}%'";
            } else {
                $where .= " AND (db9 LIKE '%{$session->get_local('db')}%'";
                $db_flg = 1;
            }
        }
        if ($session->get_local('db') != '' && $where != '') {
            if ($db_flg == 1) {
                $where .= " OR db10 LIKE '%{$session->get_local('db')}%'";
            } else {
                $where .= " AND (db10 LIKE '%{$session->get_local('db')}%'";
                $db_flg = 1;
            }
        }
        if ($session->get_local('db') != '' && $where != '') {
            if ($db_flg == 1) {
                $where .= " OR db11 LIKE '%{$session->get_local('db')}%'";
            } else {
                $where .= " AND (db11 LIKE '%{$session->get_local('db')}%'";
                $db_flg = 1;
            }
        }
        if ($session->get_local('db') != '' && $where != '') {
            if ($db_flg == 1) {
                $where .= " OR db12 LIKE '%{$session->get_local('db')}%'";
            } else {
                $where .= " AND (db12 LIKE '%{$session->get_local('db')}%'";
                $db_flg = 1;
            }
        }
        if ($db_flg == 1) {
            $where .= ")";
        }
        return $where;
    }
    
    ///// �����ޥ�������HTML <select> option �ν���
    protected function getDirOptionsBody($session)
    {
        $query = "SELECT DISTINCT ON (dir) dir, p_id FROM program_master ORDER BY dir ASC";
        $res = array();
        if (($rows=getResult2($query, $res)) <= 0) return '';
        $options = "\n";
        $options .= "<option value='' style='color:red;'>̤����</option>\n";
        for ($i=0; $i<$rows; $i++) {
            if ($session->get_local('dir') == $res[$i][0]) {
                $options .= "<option value='{$res[$i][0]}' selected>{$res[$i][0]}</option>\n";
            } else {
                $options .= "<option value='{$res[$i][0]}'>{$res[$i][0]}</option>\n";
            }
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
        $file_name = "list/progMaster_search_ViewListHeader-{$session->get('User_ID')}.html";
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
        $file_name = "list/progMaster_search_ViewList-{$session->get('User_ID')}.html";
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
        $file_name = "list/progMaster_search_ViewListFooter-{$session->get('User_ID')}.html";
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
                p_id                                AS �ץ����ID     -- 0
                ,
                p_name                              AS �ץ����̾     -- 1
                ,
                dir                                 AS �ǥ��쥯�ȥ�     -- 2
                ,
                comment                             AS ������         -- 3
                ,
                db1                                 AS ����DB1          -- 4
                ,
                db2                                 AS ����DB2          -- 5
                ,
                db3                                 AS ����DB3          -- 6
                ,
                db4                                 AS ����DB4          -- 7
                ,
                db5                                 AS ����DB5          -- 8
                ,
                db6                                 AS ����DB6          -- 9
                ,
                db7                                 AS ����DB7          -- 10
                ,
                db8                                 AS ����DB8          -- 11
                ,
                db9                                 AS ����DB9          -- 12
                ,
                db10                                AS ����DB10         -- 13
                ,
                db11                                AS ����DB11         -- 14
                ,
                db12                                AS ����DB12         -- 15
                ,
                last_date                           AS ��Ͽ����         -- 16
            FROM
                program_master
            {$this->where}
            {$this->order}
            {$this->offset}
            {$this->limit}
        ";
        $session->add('query', $query);
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
        //$array_lowercase = array_map('strtolower', $res);
        //array_multisort($array_lowercase, SORT_ASC, SORT_STRING, $res);
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
                    $listTable .= "    <tr onDblClick='ProgMasterSearch.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPlanNo=" . urlencode($res[$i][0]) . "&targetAssyNo=" . urlencode($res[$i][1]) . "\", 600, 235)' title='�����Ȥ���Ͽ����Ƥ��ޤ������֥륯��å��ǥ����ȤξȲ��Խ�������ޤ���' style='background-color:#e6e6e6;'>\n";
                } else {
                    $listTable .= "    <tr onDblClick='ProgMasterSearch.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPlanNo=" . urlencode($res[$i][0]) . "&targetAssyNo=" . urlencode($res[$i][1]) . "\", 600, 235)' title='���ߥ����Ȥ���Ͽ����Ƥ��ޤ��󡣥��֥륯��å��ǥ����ȤξȲ��Խ�������ޤ���'>\n";
                }
                *****/
                $listTable .= "    <tr>\n";
                // ����ɽ��
                //$listTable .= "        <font size='2'><td class='winboxb' width=' 5%' align='right' rowspan='5'>" . ($i+1) . "</td>\n";    // ���ֹ�
                // DB���ѤΤ���
                $listTable .= "        <font size='2'><td class='winboxb' width=' 5%' align='right' rowspan='3'>" . ($i+1) . "</td>\n";    // ���ֹ�
                if ($session->get_local('pid') != '') {
                    $p_id = $res[$i][0];
                    $div_id = $session->get_local('pid');
                    $p_id = ereg_replace($div_id, "<B>{$div_id}</B>", $p_id);
                    $listTable .= "    <td class='winbox' width='38%' align='left'>{$p_id}</td>\n";     // �ץ����ID
                } else {
                    $listTable .= "    <td class='winbox' width='38%' align='left'>{$res[$i][0]}</td>\n";     // �ץ����ID
                }
                if ($session->get_local('name_comm') != '') {
                    $p_name = $res[$i][1];
                    $div_name = $session->get_local('name_comm');
                    $p_name = ereg_replace($div_name, "<B>{$div_name}</B>", $p_name);
                    $listTable .= "    <td class='winbox' width='24%' align='left'>{$p_name}</td>\n";     // �ץ����̾
                } else {
                    $listTable .= "    <td class='winbox' width='24%' align='left'>{$res[$i][1]}</td>\n";     // �ǥ��쥯�ȥ�
                }
                if ($session->get_local('dir') != '') {
                    $listTable .= "    <td class='winbox' width='33%' align='left'><B>{$res[$i][2]}</B></td>\n";     // �ǥ��쥯�ȥ�
                } else {
                    $listTable .= "    <td class='winbox' width='33%' align='left'>{$res[$i][2]}</td>\n";     // �ǥ��쥯�ȥ�
                }
                $listTable .= "        </font></tr>\n";
                $listTable .= "    <tr>\n";
                $listTable .= "        <font size='2'>\n";          // �����ܤΥǡ���
                if ($session->get_local('name_comm') != '') {
                    $p_comm = $res[$i][3];
                    $div_comm = $session->get_local('name_comm');
                    $p_comm = ereg_replace($div_comm, "<B>{$div_comm}</B>", $p_comm);
                    $listTable .= "    <td class='winboxb' width='65%' align='left' rowspan='2' colspan='2'>{$p_comm}</td>\n";     // ������
                } else {
                    $listTable .= "    <td class='winboxb' width='65%' align='left' rowspan='2' colspan='2'>{$res[$i][3]}</td>\n";     // ������
                }
                // ����ɽ����
                /*****
                if ($res[$i][4] != '') {
                    $listTable .= "    <td class='winbox' width='33%' align='left'>{$res[$i][4]}</td>\n";     // �ǥ��쥯�ȥ�
                } else {
                    $listTable .= "    <td class='winbox' width='33%' align='left'>��</td>\n";     // �ǥ��쥯�ȥ�
                }
                $listTable .= "        </font></tr>\n";
                $listTable .= "    <tr>\n";
                $listTable .= "        <font size='2'>\n";          // �����ܤΥǡ���
                if ($res[$i][5] != '') {
                    $listTable .= "    <td class='winbox' width='33%' align='left'>{$res[$i][5]}</td>\n";     // �ǥ��쥯�ȥ�
                } else {
                    $listTable .= "    <td class='winbox' width='33%' align='left'>��</td>\n";     // �ǥ��쥯�ȥ�
                }
                if ($res[$i][6] != '') {
                    $listTable .= "    <td class='winbox' width='32%' align='left'>{$res[$i][6]}</td>\n";     // �ǥ��쥯�ȥ�
                } else {
                    $listTable .= "    <td class='winbox' width='32%' align='left'>��</td>\n";     // �ǥ��쥯�ȥ�
                }
                if ($res[$i][7] != '') {
                    $listTable .= "    <td class='winbox' width='30%' align='left'>{$res[$i][7]}</td>\n";     // �ǥ��쥯�ȥ�
                } else {
                    $listTable .= "    <td class='winbox' width='30%' align='left'>��</td>\n";     // �ǥ��쥯�ȥ�
                }
                $listTable .= "        </font></tr>\n";
                $listTable .= "    <tr>\n";
                $listTable .= "        <font size='2'>\n";          // �����ܤΥǡ���
                if ($res[$i][8] != '') {
                    $listTable .= "    <td class='winbox' width='33%' align='left'>{$res[$i][8]}</td>\n";     // �ǥ��쥯�ȥ�
                } else {
                    $listTable .= "    <td class='winbox' width='33%' align='left'>��</td>\n";     // �ǥ��쥯�ȥ�
                }
                if ($res[$i][9] != '') {
                    $listTable .= "    <td class='winbox' width='32%' align='left'>{$res[$i][9]}</td>\n";     // �ǥ��쥯�ȥ�
                } else {
                    $listTable .= "    <td class='winbox' width='32%' align='left'>��</td>\n";     // �ǥ��쥯�ȥ�
                }
                if ($res[$i][10] != '') {
                    $listTable .= "    <td class='winbox' width='30%' align='left'>{$res[$i][10]}</td>\n";     // �ǥ��쥯�ȥ�
                } else {
                    $listTable .= "    <td class='winbox' width='30%' align='left'>��</td>\n";     // �ǥ��쥯�ȥ�
                }
                $listTable .= "        </font></tr>\n";
                $listTable .= "    <tr>\n";
                $listTable .= "        <font size='2'>\n";          // �����ܤΥǡ���
                if ($res[$i][11] != '') {
                    $listTable .= "    <td class='winboxb' width='33%' align='left'>{$res[$i][11]}</td>\n";     // �ǥ��쥯�ȥ�
                } else {
                    $listTable .= "    <td class='winboxb' width='33%' align='left'>��</td>\n";     // �ǥ��쥯�ȥ�
                }
                if ($res[$i][12] != '') {
                    $listTable .= "    <td class='winboxb' width='32%' align='left'>{$res[$i][12]}</td>\n";     // �ǥ��쥯�ȥ�
                } else {
                    $listTable .= "    <td class='winboxb' width='32%' align='left'>��</td>\n";     // �ǥ��쥯�ȥ�
                }
                if ($res[$i][13] != '') {
                    $listTable .= "    <td class='winboxb' width='30%' align='left'>{$res[$i][13]}</td>\n";     // �ǥ��쥯�ȥ�
                } else {
                    $listTable .= "    <td class='winboxb' width='30%' align='left'>��</td>\n";     // �ǥ��쥯�ȥ�
                }
                *****/
                // DB���ѤΤ���
                $db_use = 0;
                for ($r=4; $r<16; $r++) {
                    if ($res[$i][$r] != '') {
                        $db_use = 1;
                    }
                }
                if ($db_use == 1) {
                    $db_url = '../progMaster_search_db_detail.php?db1='. $res[$i][4] .'&db2='. $res[$i][5] .'&db3='. $res[$i][6] .'&db4='. $res[$i][7] .'&db5='. $res[$i][8] .'&db6='. $res[$i][9] .'&db7='. $res[$i][10] .'&db8='. $res[$i][11] .'&db9='. $res[$i][12] .'&db10='. $res[$i][13] .'&db11='. $res[$i][14] .'&db12='. $res[$i][15] .'&key='. $session->get_local('db');
                    $listTable .= "    <td class='winbox' width='30%' align='center'><a href='". $db_url ."' onclick='ProgMasterSearch.win_open(\"". $db_url ."\", 1000, 440); return false;' title='����å��ǻ��ѣģ¤ξܺ٤�ɽ�����ޤ���'>����</a></td>\n";     // DB1
                } else {
                    $listTable .= "    <td class='winbox' width='30%' align='center'>-----</td>\n";     // DB1
                }
                $listTable .= "    </tr>\n";
                $listTable .= "    <tr>\n";
                $listTable .= "        <td class='winboxb' width='33%' align='left'>��{$res[$i][16]}��</td>\n";     // ��������
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
<link rel='stylesheet' href='../progMaster_search.css' type='text/css' media='screen'>
<style type='text/css'>
<!--
body {
    background-image:none;
}
-->
</style>
<script type='text/javascript' src='../progMaster_search.js'></script>
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
    
    
} // Class ProgMasterSearch_Model End

?>
