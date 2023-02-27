<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ω����Ͽ�����ȼ��ӹ�������� �Ȳ�       MVC Model ��                   //
// Copyright (C) 2006-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/03/01 Created   assembly_time_show_Model.php                        //
// 2006/03/03 �������٤�Ajax�ѾȲ� getViewProcessTable()�᥽�åɤ��ɲ�      //
// 2006/03/05 �ǡ������ʤ�����ɽ�������� getPlanData()�᥽�åɤ��ɲ�      //
// 2006/03/06 �����ط��ι�פ򽸷פ���ʬ������ɽ��(ɽ�򸫤䤹������)        //
//            ���ӹ��������٤򤤤��ʤ�Ф�����ɽ������ɽ���ܥ�������DHTML //
// 2006/03/09 str_time ASC, end_time ASC �� str_time ASC, ��λ���� ASC      //
// 2006/03/13 getViewJissekiTable()�ι�������ѡ�����ơ������ѹ�           //
// 2006/05/02 getViewRegisterTable()��������åȤˤ���׹���ɽ���ɲ�      //
// 2006/05/05 �嵭�Υ᥽�åɤ����������ʲ��ξ����б�                    //
// 2006/05/10 getViewRegisterTable()����ȡ���ư���������ʬ��            //
// 2006/05/12 �����ʼ蹩������Ͽ���ФƤ������� SQL���å����ɲ�            //
// 2006/05/19 ��Ͽ�����Τߤ�ɽ����ǽ���ɲ� regOnly �ײ���Ǥι�׹������ɲ� //
// 2006/05/28 �嵭�򹹤˲��ɤ�getViewRegisterTable()��ײ�Ŀ��б����ѹ�    //
// 2007/06/17 ��Ω��λͽ���������ɲäΤ���getViewRegisterTable()�᥽�åɤ�  //
//            usedTime, workerCount ���ɲá�getPlanEndTime()�᥽�åɤ��ɲ�  //
// 2007/06/20 DB�ץ������㡼overlaps_time_diff()���ɲá��٤߻����б���    //
//            �嵭�ǲ�Ư���ֳ��βû��ϻĶȻ��֤˻ؼ����������å����ݥ����//
// 2007/06/22 ���˾嵭php���å��򥹥ȥ����ɥץ������㡼�ذܹ�           //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��

require_once ('../../../daoInterfaceClass.php');    // TNK ������ DAO���󥿡��ե��������饹


/*****************************************************************************************
*       MVC��Model�� ���饹��� daoInterfaceClass(base class) ���쥯�饹���ĥ           *
*****************************************************************************************/
class AssemblyTimeShow_Model extends daoInterfaceClass
{
    ///// Private properties
    private $where;                             // ���� SQL��WHERE��
    
    ///// public properties
    // public  $graph;                             // GanttChart�Υ��󥹥���
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer ����� (php5�ذܹԻ��� __construct() ���ѹ�ͽ��) (�ǥ��ȥ饯��__destruct())
    public function __construct($request)
    {
        ///// ����WHERE�������
        switch ($request->get('showMenu')) {
        case 'ListTable':
            $this->where = $this->SetInitWhere($request);
            break;
        case 'CondForm':
        case 'WaitMsg':
        default:
            $this->where = '';
        }
    }
    
    ////////// MVC �� Model ���η�� ɽ���ѤΥǡ�������
    ///// List��    ���ӹ������� �� ��Ͽ�����إå��� ����ɽ
    public function getViewListTable($request)
    {
        $listTable = '';    // �����
        if ($request->get('regOnly') == 'no') {
            $listTable .= $this->getViewJissekiTable($request);
        }
        $listTable .= $this->getViewRegisterTable($request);
        /*** �ǥХå���
        $handle = fopen('debug-assembly_time_show.html', 'w');
        fwrite($handle, $listTable);
        fclose($handle);
        ***/
        return $listTable;
    }
    
    ///// List��    ��Ͽ�������� ����ɽ
    public function getViewProcessTable($request)
    {
        $query = "
            SELECT pro_no       AS �����ֹ�     -- 00
                ,pro_mark       AS ��������     -- 01
                ,line_no        AS �饤���ֹ�   -- 02
                ,assy_time      AS ��Ͽ����     -- 03
                ,Uround(setup_time / std_lot, 3)
                                AS �ʼ蹩��     -- 04
                ,setup_time     AS �ʼ����     -- 05
                ,man_count      AS ��ȿͿ�     -- 06
                ,assy_time + Uround(setup_time / std_lot, 3)
                                AS ��׹���     -- 07
                ,CASE
                    WHEN pro_seg = '1' THEN '����'
                    WHEN pro_seg = '2' THEN '��ư��'
                    WHEN pro_seg = '3' THEN '������'
                    ELSE pro_seg
                 END            AS ������ʬ     -- 08
            FROM
                assembly_standard_time
            LEFT OUTER JOIN
                assembly_time_header USING(assy_no, reg_no)
            LEFT OUTER JOIN
                assembly_process_master USING(pro_mark)
            WHERE
                assy_no='{$request->get('targetAssyNo')}' AND reg_no={$request->get('targetRegNo')}
            ORDER BY
                assy_no ASC, reg_no ASC, pro_no ASC
        ";
        $listTable = '';
        $listTable .= "<table width='700' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <caption><span style='color:red;'>��������</span>����Ͽ�ֹ桧{$request->get('targetRegNo')}</caption>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <th class='winbox' align='center'>�����ֹ�</th>\n";
        $listTable .= "        <th class='winbox' align='center'>��������</th>\n";
        $listTable .= "        <th class='winbox' align='center'>������ʬ</th>\n";
        $listTable .= "        <th class='winbox' align='center'>�饤���ֹ�</th>\n";
        $listTable .= "        <th class='winbox' align='center'>����(ʬ)</th>\n";
        $listTable .= "        <th class='winbox' align='center'>�ʼ蹩��</th>\n";
        $listTable .= "        <th class='winbox' align='center'>��׹���</th>\n";
        $listTable .= "        <th class='winbox' align='center'>�ʼ����</th>\n";
        $listTable .= "        <th class='winbox' align='center'>��ȿͿ�</th>\n";
        $listTable .= "    </tr>\n";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) < 1 ) {
            $listTable .= "    <tr>\n";
            $listTable .= "        <td width='700' colspan='9' align='center' class='winbox'>�������٥ǡ���������ޤ���</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        } else {
            $kousu    = 0;
            $dan_kosu = 0;
            $sum_kosu = 0;
            $sum_dan  = 0;
            $sum_man  = 0;
            for ($i=0; $i<$rows; $i++) {
                $listTable .= "    <tr>\n";
                $listTable .= "        <td class='winbox' align='right' >{$res[$i][0]}</td>\n";
                $listTable .= "        <td class='winbox' align='center'>{$res[$i][1]}</td>\n";
                $listTable .= "        <td class='winbox' align='center'>{$res[$i][8]}</td>\n";
                $listTable .= "        <td class='winbox' align='center'>{$res[$i][2]}</td>\n";
                $listTable .= "        <td class='winbox' align='right' >{$res[$i][3]}</td>\n";
                $listTable .= "        <td class='winbox' align='right' >{$res[$i][4]}</td>\n";
                $listTable .= "        <td class='winbox' align='right' >" . number_format($res[$i][7], 3) . "</td>\n";
                $listTable .= "        <td class='winbox' align='right' >{$res[$i][5]}</td>\n";
                $listTable .= "        <td class='winbox' align='right' >{$res[$i][6]}</td>\n";
                $listTable .= "    </tr>\n";
                $kousu    += $res[$i][3];
                $dan_kosu += $res[$i][4];
                $sum_kosu += $res[$i][7];
                $sum_dan  += $res[$i][5];
                $sum_man  += $res[$i][6];
            }
            $listTable .= "    <tr>\n";
            $listTable .= "        <td class='winbox' align='right' colspan='4'>�硡��</td>\n";
            $listTable .= "        <td class='winbox' align='right'>" . number_format($kousu, 3)    . "</td>\n";
            $listTable .= "        <td class='winbox' align='right'>" . number_format($dan_kosu, 3) . "</td>\n";
            $listTable .= "        <td class='winbox' align='right' style='color:red;'>" . number_format($sum_kosu, 3) . "</td>\n";
            $listTable .= "        <td class='winbox' align='right'>" . number_format($sum_dan, 3)  . "</td>\n";
            $listTable .= "        <td class='winbox' align='right'>" . number_format($sum_man, 2)  . "</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        }
        return mb_convert_encoding($listTable, 'UTF-8');
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// �ꥯ�����Ȥˤ��SQLʸ�δ���WHERE�������
    protected function SetInitWhere($request)
    {
        $where = '';    // �����
        ///// �ײ��ֹ�λ���
        $where .= "WHERE plan_no = '{$request->get('targetPlanNo')}'";
        return $where;
    }
    
    ///// �ײ��ֹ椫�������ֹ桦����̾���ײ���������������
    protected function getPlanData($request, &$res)
    {
        // �ײ��ֹ椫�������ֹ�μ���(���ӥǡ�����̵�������б�)
        $query = "SELECT parts_no       AS �����ֹ�     -- 00
                        ,substr(midsc, 1, 20)
                                        AS ����̾       -- 01
                        ,plan-cut_plan  AS �ײ��       -- 02
                        ,kansei         AS ������       -- 03
                    FROM assembly_schedule
                    LEFT OUTER JOIN
                        miitem ON (parts_no=mipn)
                    WHERE plan_no='{$request->get('targetPlanNo')}'
        ";
        $res = array();
        if ($this->getResult2($query, $res) > 0) {
            $res['assy_no']   = $res[0][0];
            $res['assy_name'] = $res[0][1];
            $res['keikaku']   = $res[0][2];
            $res['kansei']    = $res[0][3];
            return true;
        } else {
            $res['assy_no']   = '';
            $res['assy_name'] = '';
            $res['keikaku']   = '';
            $res['kansei']    = '';
            return false;
        }
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ///// List��    ���ӹ������� ����ɽ
    private function getViewJissekiTable($request)
    {
        $query = "SELECT plan_no        AS �ײ��ֹ�     -- 00
                        ,parts_no       AS �����ֹ�     -- 01
                        ,substr(midsc, 1, 20)
                                        AS ����̾       -- 02
                        ,plan_pcs       AS �ײ�Ŀ�     -- 03
                        ,user_id        AS �Ұ��ֹ�     -- 04
                        ,CASE
                            WHEN to_number(user_id, '999999') >= 777001 AND to_number(user_id, '999999') <= 777999
                            THEN '�����' || substr(user_id, 4, 3)
                            ELSE trim(assyuser.name)
                         END            AS ��ȼ�       -- 05
                        ,to_char(str_time, 'YY/MM/DD HH24:MI:SS')
                                        AS ��������     -- 06
                        ,CASE
                            WHEN to_char(end_time, 'YY/MM/DD HH24:MI:SS') = '70/01/01 00:00:00'
                            THEN '̤��λ'
                            ELSE to_char(end_time, 'YY/MM/DD HH24:MI:SS')
                         END            AS ��λ����     -- 07
                        ,CASE
                            WHEN assy_time IS NULL
                            THEN 0
                            ELSE assy_time
                         END            AS ��׹���     -- 08
                        -----------------------------�ꥹ�ȤϾ嵭�ޤ�
                        ,group_name     AS ���롼��̾   -- 09
                        ,serial_no      AS Ϣ��         -- 10
                        ,to_char(str_time, 'YY/MM/DD HH24:MI:SS')
                                        AS ���Ͼܺ�     -- 11
                        ,to_char(end_time, 'YY/MM/DD HH24:MI:SS')
                                        AS ��λ�ܺ�     -- 12
                        ,CASE
                            WHEN plan_pcs > 0 AND assy_time IS NOT NULL
                            THEN Uround(assy_time / plan_pcs, 3)
                            ELSE 0
                         END            AS ����         -- 13
                        ,plan-cut_plan  AS �ײ��       -- 14
                        ,kansei         AS ������       -- 15
                        ,CASE
                            WHEN end_time = '1970-01-01 00:00:00'
                            THEN CURRENT_TIMESTAMP(0)
                            ELSE end_time
                         END            AS ��λ����     -- 16
                    FROM
                        assembly_process_time
                    LEFT OUTER JOIN
                        assembly_schedule USING(plan_no)
                    LEFT OUTER JOIN
                        miitem ON (parts_no=mipn)
                    LEFT OUTER JOIN
                        user_detailes   AS assyuser ON (user_id=uid)
                    LEFT OUTER JOIN
                        assembly_process_group USING(group_no)
                    {$this->where}
                    ORDER BY
                        str_time ASC, ��λ���� ASC
        ";
        // �����ֹ桦����̾���ײ�����������μ���
        $this->getPlanData($request, $res);
        $listTable = '';
        $listTable .= "<table width='870' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <caption><span style='color:blue;'>���ӹ���</span>�������ֹ桧{$res['assy_no']}������̾��{$res['assy_name']}���ײ����" . number_format($res['keikaku']) . "����������" . number_format($res['kansei']) . "</caption>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <th class='winbox' width='13.00%' align='center'>���롼��̾</th>\n";
        $listTable .= "        <th class='winbox' width=' 8.85%' align='center'>��Ω��</th>\n";
        $listTable .= "        <th class='winbox' width=' 9.29%' align='center'>�Ұ��ֹ�</th>\n";
        $listTable .= "        <th class='winbox' width='12.52%' align='center'>�� �� �� ̾</th>\n";
        $listTable .= "        <th class='winbox' width='18.08%' align='center'>��Ω���</th>\n";
        $listTable .= "        <th class='winbox' width='18.08%' align='center'>��λ(����)</th>\n";
        $listTable .= "        <th class='winbox' width='11.06%' align='center'>������(ʬ)</th>\n";
        $listTable .= "        <th class='winbox' width=' 9.12%' align='center'>����(ʬ)</th>\n";
        $listTable .= "    </tr>\n";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) < 1 ) {
            $listTable .= "    <tr>\n";
            $listTable .= "        <td colspan='8' width='870' align='center' class='winbox'>���ӥǡ���������ޤ���</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        } else {
            $sokosu = 0;
            $kosu   = 0;
            for ($i=0; $i<$rows; $i++) {
                $listTable .= "    <tr>\n";
                $listTable .= "        <td class='winbox' align='right' ><span id='group{$i}'   ></span></td>\n";
                $listTable .= "        <td class='winbox' align='right' ><span id='indust{$i}'  ></span></td>\n";
                $listTable .= "        <td class='winbox' align='center'><span id='emp_no{$i}'  ></span></td>\n";
                $listTable .= "        <td class='winbox' align='left'  ><span id='emp_name{$i}'></span></td>\n";
                $listTable .= "        <td class='winbox' align='center'><span id='chaku{$i}'   ></span></td>\n";
                $listTable .= "        <td class='winbox' align='center'><span id='kanryo{$i}'  ></span></td>\n";
                $listTable .= "        <td class='winbox' align='right' ><span id='sokousu{$i}' ></span></td>\n";
                $listTable .= "        <td class='winbox' align='right' ><span id='kousu{$i}'   ></span></td>\n";
                $listTable .= "    </tr>\n";
                $sokosu += $res[$i][8];
                $kosu   += $res[$i][13];
            }
            $listTable .= "    <tr>\n";
            $listTable .= "        <td class='winbox' align='center' colspan='2'><input type='button' name='meisai' value='����ɽ��'\n";
            $listTable .= "            onClick=\"\n";
            for ($i=0; $i<$rows; $i++) {
                $listTable .= "                document.getElementById('group{$i}').innerHTML=   '{$res[$i][9]}';\n";
                $listTable .= "                document.getElementById('indust{$i}').innerHTML=  '" . number_format($res[$i][3]) . "';\n";
                $listTable .= "                document.getElementById('emp_no{$i}').innerHTML=  '{$res[$i][4]}';\n";
                $listTable .= "                document.getElementById('emp_name{$i}').innerHTML='{$res[$i][5]}';\n";
                $listTable .= "                document.getElementById('chaku{$i}').innerHTML=   '{$res[$i][6]}';\n";
                $listTable .= "                document.getElementById('kanryo{$i}').innerHTML=  '{$res[$i][7]}';\n";
                $listTable .= "                document.getElementById('sokousu{$i}').innerHTML= '{$res[$i][8]}';\n";
                $listTable .= "                document.getElementById('kousu{$i}').innerHTML=   '{$res[$i][13]}';\n";
            }
            $listTable .= "            \"\n";
            $listTable .= "            style='color:blue;'>\n";
            $listTable .= "        <input type='button' name='noDisp' value='��ɽ��'\n";
            $listTable .= "            onClick=\"\n";
            for ($i=0; $i<$rows; $i++) {
                $listTable .= "                document.getElementById('group{$i}').innerHTML=   '';\n";
                $listTable .= "                document.getElementById('indust{$i}').innerHTML=  '';\n";
                $listTable .= "                document.getElementById('emp_no{$i}').innerHTML=  '';\n";
                $listTable .= "                document.getElementById('emp_name{$i}').innerHTML='';\n";
                $listTable .= "                document.getElementById('chaku{$i}').innerHTML=   '';\n";
                $listTable .= "                document.getElementById('kanryo{$i}').innerHTML=  '';\n";
                $listTable .= "                document.getElementById('sokousu{$i}').innerHTML= '';\n";
                $listTable .= "                document.getElementById('kousu{$i}').innerHTML=   '';\n";
            }
            $listTable .= "            \"\n";
            $listTable .= "            style='color:black;'>\n";
            $listTable .= "        </td>\n";
            $listTable .= "        <td class='winbox' align='right' colspan='4'>���</td>\n";
            $listTable .= "        <td class='winbox' align='right'>" . number_format($sokosu, 3) . "</td>\n";
            $listTable .= "        <td class='winbox' align='right' style='color:blue;'>" . number_format($kosu, 3) . "</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <span id='jissekiMei'>\n";
            $listTable .= "    </span>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        }
        return mb_convert_encoding($listTable, 'UTF-8');
    }
    
    ///// List��    ��Ͽ���� ����ɽ
    private function getViewRegisterTable($request)
    {
        // �ײ��ֹ椫�������ֹ�μ���(���ӥǡ�����̵�������б�)
        // �����ֹ桦����̾���ײ�����������μ���
        $this->getPlanData($request, $res);
        // ��Ͽ�����μ���
        $assy_no    = $res['assy_no'];
        $assy_name  = $res['assy_name'];
        $keikaku    = $res['keikaku'];
        $kansei     = $res['kansei'];
        $kei_zan    = $keikaku - $kansei;
        $query = $this->getQueryStatement($assy_no, $kansei, $kei_zan);
        // �����
        $listTable = '';
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) > 0 ) {
            if ($request->get('regOnly') == 'yes') {
                $all_time = Uround($kei_zan * ($res[0][4]+$res[0][14]), 3);
                $need_time = Uround($all_time - $request->get('usedTime'), 3);  // �Ĥ깩��
                // ɬ�׹������鴰λͽ�����������
                $end_date_time = $this->getPlanEndTime($request, $need_time, $str_date_time);
                $need_time = number_format($need_time, 3);
                $listTable .= "<table width='870' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>\n";
                $listTable .= "    <caption><span style='color:blue;'>ͽ��</span>���ײ��ֹ桧{$request->get('targetPlanNo')}�������ֹ桧{$assy_no}������̾��{$assy_name}���ײ����" . number_format($keikaku) . "����������" . number_format($kansei) . "</caption>\n";
                $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
                $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
                $listTable .= "    <tr>\n";
                $listTable .= "        <td class='winbox' align='center' rowspan='1' style='color:blue;'>��Ωͽ����Ǥι�׼��ȹ���</td>\n";
                $listTable .= "        <th class='winbox' align='center'>�ײ�Ŀ�</th>\n";
                $listTable .= "        <th class='winbox' align='center'>���ȹ��</th>\n";
                $listTable .= "        <th class='winbox' align='center'>��ư�����</th>\n";
                $listTable .= "        <th class='winbox' align='center'>��������&nbsp;&nbsp;</th>\n";
                $listTable .= "        <th class='winbox' align='center'>��׹���</th>\n";
                $listTable .= "    </tr>\n";
                $listTable .= "    <tr>\n";
                $listTable .= "        <td class='winbox' align='center' style='font-size:0.95em; color:blue; background-color:#ceffce;'>�ײ�� {$kei_zan} �� (���ȹ��� ". number_format($res[0][4], 3) . " �� �ʼ蹩�� " . number_format($res[0][14], 3) . ")</td>\n";// ��åȹ�׹���
                $listTable .= "        <td class='winbox' align='right' >{$kei_zan}</td>\n";    // �ײ�Ŀ�  #ffffc6(��������) #ceffce(������ɥ��꡼��)
                $listTable .= "        <td class='winbox' align='right' style='color:blue; background-color:#ceffce;'>" . number_format($kei_zan * ($res[0][4]+$res[0][14]), 3) . "</td>\n";  // ���ȹ���(�ײ��)
                $listTable .= "        <td class='winbox' align='right' style='color:black;'>" . number_format($kei_zan * ($res[0][6]+$res[0][15]), 3) . "</td>\n";  // ��ư������(�ײ��)
                $listTable .= "        <td class='winbox' align='right' style='color:black;'>" . number_format($kei_zan * ($res[0][8]+$res[0][16]), 3) . "</td>\n";  // ������(�ײ��)
                $listTable .= "        <td class='winbox' align='right' style='color:black;'>" . number_format($kei_zan * ($res[0][4]+$res[0][14]+$res[0][6]+$res[0][15]+$res[0][8]+$res[0][16]), 3) . "</td>\n"; // ��׹���(�ײ��)
                $listTable .= "    </tr>\n";
                $listTable .= "    <tr>\n";                                             // �ƥ����� �ײ��ֹ� C4290968
                $listTable .= "        <td class='winbox' align='center' rowspan='1' style='color:blue;'>��Ω ��λ ͽ����������(����)</td>\n";
                $listTable .= "        <th class='winbox' align='center' colspan='2'>���ߤޤǤλ��ѹ���</th>\n";
                $listTable .= "        <th class='winbox' align='center' colspan='3'>�������Ǥ�ɬ�׹����Ⱥ�ȼԿ�</th>\n";
                $listTable .= "    </tr>\n";
                $listTable .= "    <tr>\n";
                $listTable .= "        <td class='winbox' align='center' style='font-size:1.1em; color:blue; background-color:#ceffce;'>{$end_date_time}��<span style='font-size:0.8em;'>({$str_date_time})</span></td>\n";
                $listTable .= "        <td class='winbox' align='right' colspan='2'>" . number_format($request->get('usedTime'), 3) . "ʬ</td>\n";
                $listTable .= "        <td class='winbox' align='right' colspan='3'>������{$need_time}ʬ���ȼԡ�{$request->get('workerCount')}��</td>\n";
                $listTable .= "    </tr>\n";
                $listTable .= "</table>\n";
                $listTable .= "    </td></tr>\n";
                $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
            }
        }
        $listTable .= '<br>';
        $listTable .= "<table width='870' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <caption><span style='color:red;'>��Ͽ����</span>�������ֹ桧{$assy_no}������̾��{$assy_name}���ײ����" . number_format($keikaku) . "����������" . number_format($kansei) . "</caption>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <th class='winbox' align='center'>&nbsp;</th>\n";
        $listTable .= "        <th class='winbox' align='center'>��Ͽ�ֹ�</th>\n";
        $listTable .= "        <th class='winbox' align='center'>ɸ��и˻���</th>\n";
        $listTable .= "        <th class='winbox' align='center'>ɸ���å�</th>\n";
        $listTable .= "        <th class='winbox' align='center'>���ʼ����</th>\n";
        $listTable .= "        <th class='winbox' align='center'>�ߡ��ꡡ��</th>\n";
        $listTable .= "        <th class='winbox' align='center'>���ȹ��</th>\n";
        $listTable .= "        <th class='winbox' align='center'>��ư�����</th>\n";
        $listTable .= "        <th class='winbox' align='center'>��������&nbsp;&nbsp;</th>\n";
        $listTable .= "        <th class='winbox' align='center'>��׹���</th>\n";
        $listTable .= "    </tr>\n";
        if ($rows < 1) {
            $listTable .= "    <tr>\n";
            $listTable .= "        <td colspan='10' width='870' align='center' class='winbox'>��������Ͽ����Ƥޤ���</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        } else {
            if ($kansei > 0) {
                $listTable .= "    <tr>\n";
                $listTable .= "        <td class='winbox' colspan='3' align='right' >������å�</td>\n";
                $listTable .= "        <td class='winbox' align='right' >{$kansei}</td>\n";                         // ������å�(������)
                $listTable .= "        <td class='winbox' colspan='2' align='right'>������åȤǤι���</td>\n";     // �ƹ���
                $listTable .= "        <td class='winbox' align='right' style='color:yellow;'>" . number_format($res[0][4]+$res[0][10], 3) . "</td>\n"; // ���ȹ���(������å�)
                $listTable .= "        <td class='winbox' align='right' style='color:yellow;'>" . number_format($res[0][6]+$res[0][11], 3) . "</td>\n"; // ��ư������(������å�)
                $listTable .= "        <td class='winbox' align='right' style='color:yellow;'>" . number_format($res[0][8]+$res[0][12], 3) . "</td>\n"; // ������(������åȤϴط��ʤ�����Ͽ�����뤿��)
                $listTable .= "        <td class='winbox' align='right' style='color:yellow;'>" . number_format($res[0][4]+$res[0][10]+$res[0][6]+$res[0][11]+$res[0][8]+$res[0][12], 3) . "</td>\n"; // ��׹���(������å�)
                $listTable .= "    </tr>\n";
            }
            for ($i=0; $i<$rows; $i++) {
                $listTable .= "    <tr>\n";
                $listTable .= "        <td class='winbox' align='center'>\n";
                $listTable .= "            <input type='button' name='process' value='����' onClick='AssemblyTimeShow.processExecute(\"{$assy_no}\", \"{$res[$i][0]}\")' style='color:red;'>\n";
                $listTable .= "        </td>\n";
                $listTable .= "        <td class='winbox' align='right' >{$res[$i][0]}</td>\n";                         // ��Ͽ�ֹ�
                $listTable .= "        <td class='winbox' align='right' >" . number_format($res[$i][9], 3) . "</td>\n"; // ɸ����и˻���
                $listTable .= "        <td class='winbox' align='right' >" . number_format($res[$i][1]) . "</td>\n";    // ɸ���å�
                $listTable .= "        <td class='winbox' align='right' >{$res[$i][2]}</td>\n";                         // ���ʼ����
                $listTable .= "        <td class='winbox' align='center'>{$res[$i][3]}</td>\n";                         // ������
                $listTable .= "        <td class='winbox' align='right' >" . number_format($res[$i][4]+$res[$i][5], 3) . "</td>\n"; // ���ȹ�� (�������ʼ�)
                $listTable .= "        <td class='winbox' align='right' >" . number_format($res[$i][6]+$res[$i][7], 3) . "</td>\n"; // ��ư����� (�������ʼ�)
                $listTable .= "        <td class='winbox' align='right' >" . number_format($res[$i][8]+$res[$i][13], 3) . "</td>\n";    // ������ (�������ʼ�)
                $listTable .= "        <td class='winbox' align='right' style='color:red;'>" . number_format($res[$i][4]+$res[$i][5]+$res[$i][6]+$res[$i][7]+$res[$i][8]+$res[$i][13], 3) . "</td>\n"; // ��׹���
                $listTable .= "    </tr>\n";
            }
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
            $listTable .= "<div id='showAjax2'>\n";
            $listTable .= "</div>\n";
        }
        return mb_convert_encoding($listTable, 'UTF-8');
    }
    
    ///// List��   ����ɽ��SQL���ơ��ȥ��ȼ���
    private function getQueryStatement($assy_no, $kansei, $kei_zan)
    {
        if ($kansei <= 0) {
            $kansei = 1;    // �ʲ��Ƿ׻����뤿�᥼������
        }
        if ($kei_zan <= 0) {
            $kei_zan = 1;   // �ʲ��Ƿ׻����뤿�᥼������
        }
        $query = "
            SELECT to_char(reg_no, '0000000')
                              AS ��Ͽ�ֹ�     -- 00
              ,std_lot        AS ɸ���å�   -- 01
              ,(   SELECT sum(setup_time)
               FROM assembly_standard_time AS mei
               LEFT OUTER JOIN assembly_process_master AS master USING(pro_mark)
               WHERE mei.assy_no = head.assy_no AND mei.reg_no = head.reg_no
              )               AS ���ʼ����   -- 02
              ,to_char(setdate, '0000/00/00')
                              AS ������       -- 03
              ,(   SELECT sum(assy_time)
               FROM assembly_standard_time AS mei
               LEFT OUTER JOIN assembly_process_master AS master USING(pro_mark)
               WHERE mei.assy_no = head.assy_no AND mei.reg_no = head.reg_no AND master.pro_seg = '1'
              )               AS ���ȹ���   -- 04
              ,(   SELECT Uround(sum(setup_time) / head.std_lot, 3)
               FROM assembly_standard_time AS mei
               LEFT OUTER JOIN assembly_process_master AS master USING(pro_mark)
               WHERE mei.assy_no = head.assy_no AND mei.reg_no = head.reg_no AND master.pro_seg = '1'
              )               AS ���ʼ蹩��   -- 05
              ,(   SELECT sum(assy_time)
               FROM assembly_standard_time AS mei
               LEFT OUTER JOIN assembly_process_master AS master USING(pro_mark)
               WHERE mei.assy_no = head.assy_no AND mei.reg_no = head.reg_no AND master.pro_seg = '2'
              )               AS ��ư������   -- 06
              ,(   SELECT Uround(sum(setup_time) / head.std_lot, 3)
               FROM assembly_standard_time AS mei
               LEFT OUTER JOIN assembly_process_master AS master USING(pro_mark)
               WHERE mei.assy_no = head.assy_no AND mei.reg_no = head.reg_no AND master.pro_seg = '2'
              )               AS ���ʼ蹩��   -- 07
              ,(   SELECT sum(assy_time)
               FROM assembly_standard_time AS mei
               LEFT OUTER JOIN assembly_process_master AS master USING(pro_mark)
               WHERE mei.assy_no = head.assy_no AND mei.reg_no = head.reg_no AND master.pro_seg = '3'
              )               AS ������     -- 08
              ,pick_time      AS ɸ��и˻��� -- 09
              ---------------------------------------------------------- ������å�(������)�ˤ���ʼ蹩��
              ,(   SELECT Uround(sum(setup_time) / {$kansei}, 3)
               FROM assembly_standard_time AS mei
               LEFT OUTER JOIN assembly_process_master AS master USING(pro_mark)
               WHERE mei.assy_no = head.assy_no AND mei.reg_no = head.reg_no AND master.pro_seg = '1'
              )               AS ���ʼ蹩������   -- 10
              ,(   SELECT Uround(sum(setup_time) / {$kansei}, 3)
               FROM assembly_standard_time AS mei
               LEFT OUTER JOIN assembly_process_master AS master USING(pro_mark)
               WHERE mei.assy_no = head.assy_no AND mei.reg_no = head.reg_no AND master.pro_seg = '2'
              )               AS ���ʼ蹩������   -- 11
              ,(   SELECT Uround(sum(setup_time) / {$kansei}, 3)
               FROM assembly_standard_time AS mei
               LEFT OUTER JOIN assembly_process_master AS master USING(pro_mark)
               WHERE mei.assy_no = head.assy_no AND mei.reg_no = head.reg_no AND master.pro_seg = '3'
              )               AS ���ʼ蹩������   -- 12
              ------------------------------------------ ��Ͽ�����γ����ʼ蹩�� ��������夫���ɲä�������
              ,(   SELECT Uround(sum(setup_time) / head.std_lot, 3)
               FROM assembly_standard_time AS mei
               LEFT OUTER JOIN assembly_process_master AS master USING(pro_mark)
               WHERE mei.assy_no = head.assy_no AND mei.reg_no = head.reg_no AND master.pro_seg = '3'
              )               AS ���ʼ蹩��       -- 13
              ---------------------------------------------------------- �ײ�Ŀ��ˤ���ʼ蹩��
              ,(   SELECT Uround(sum(setup_time) / {$kei_zan}, 3)
               FROM assembly_standard_time AS mei
               LEFT OUTER JOIN assembly_process_master AS master USING(pro_mark)
               WHERE mei.assy_no = head.assy_no AND mei.reg_no = head.reg_no AND master.pro_seg = '1'
              )               AS ���ʼ蹩������   -- 14
              ,(   SELECT Uround(sum(setup_time) / {$kei_zan}, 3)
               FROM assembly_standard_time AS mei
               LEFT OUTER JOIN assembly_process_master AS master USING(pro_mark)
               WHERE mei.assy_no = head.assy_no AND mei.reg_no = head.reg_no AND master.pro_seg = '2'
              )               AS ���ʼ蹩������   -- 15
              ,(   SELECT Uround(sum(setup_time) / {$kei_zan}, 3)
               FROM assembly_standard_time AS mei
               LEFT OUTER JOIN assembly_process_master AS master USING(pro_mark)
               WHERE mei.assy_no = head.assy_no AND mei.reg_no = head.reg_no AND master.pro_seg = '3'
              )               AS ���ʼ蹩������   -- 16
          FROM assembly_time_header AS head
          WHERE assy_no='{$assy_no}'
          ORDER BY
              setdate DESC, reg_no DESC
          LIMIT 5
        ";
        return $query;
    }
    
    ///// ɬ�׹���(�Ĥ깩��)���齪λͽ�����������
    ///// ���� OVERLAPS �νҸ����Ѥ���SQLʸ���ѹ�ͽ�� SELECT (TIME '083000', TIME '171500') OVERLAPS (TIME '171000', TIME '171000')
    private function getPlanEndTime($request, $need_time, &$str_date_time)
    {
        // ɬ�׹����μ���
        $requireTime = Uround($need_time / $request->get('workerCount'), 3);
        // ����(ʬ)��INTERVAL�����Ѵ�
        $query = "
            SELECT INTERVAL '{$requireTime} minute'
        ";
        $this->getUniResult($query, $requireTime);
        // �������������
        $query = "
            SELECT to_char(now() , 'YYYY/MM/DD HH24:MI:SS')
        ";
        $this->getUniResult($query, $now);
        $str_date_time = $now;
        $i = 0;
        while (1) {
                // ������������β��ν�λ���֤����
            $query = "
                SELECT to_char(TIMESTAMP '{$now}' + INTERVAL '{$requireTime}', 'YYYY/MM/DD HH24:MI:SS')
            ";
            $this->getUniResult($query, $tempEndTime);
            // �٤߻��֤βû�
                // �����Υȥ���ٷ�
            if ($i == 0) {
                $str_overTime = date('Y/m/d 103000');   // �����Υȥ���ٷ�
                $end_overTime = date('Y/m/d 103500');   // (workingDayOffset(0)����Ѥ��ʤ��Τϵ����ж��б�)
            } else {
                $str_overTime = workingDayOffset($i) . ' 103000';   // �����Ư���Υȥ���ٷ�
                $end_overTime = workingDayOffset($i) . ' 103500';
            }
            $query = "
                SELECT overlaps_time_diff(TIMESTAMP '{$now}', TIMESTAMP '{$tempEndTime}',
                TIMESTAMP '{$str_overTime}', TIMESTAMP '{$end_overTime}') + INTERVAL '{$requireTime}'
            ";
            $this->getUniResult($query, $requireTime);
                // ������������β��ν�λ���֤����
            $query = "
                SELECT to_char(TIMESTAMP '{$now}' + INTERVAL '{$requireTime}', 'YYYY/MM/DD HH24:MI:SS')
            ";
            $this->getUniResult($query, $tempEndTime);
                // ��٤�
            if ($i == 0) {
                $str_overTime = date('Y/m/d 120000');   // ��������٤�
                $end_overTime = date('Y/m/d 124500');   // (workingDayOffset(0)����Ѥ��ʤ��Τϵ����ж��б�)
            } else {
                $str_overTime = workingDayOffset($i) . ' 120000';   // �����Ư������٤�
                $end_overTime = workingDayOffset($i) . ' 124500';
            }
            $query = "
                SELECT overlaps_time_diff(TIMESTAMP '{$now}', TIMESTAMP '{$tempEndTime}',
                TIMESTAMP '{$str_overTime}', TIMESTAMP '{$end_overTime}') + INTERVAL '{$requireTime}'
            ";
            $this->getUniResult($query, $requireTime);
                // ������������β��ν�λ���֤����
            $query = "
                SELECT to_char(TIMESTAMP '{$now}' + INTERVAL '{$requireTime}', 'YYYY/MM/DD HH24:MI:SS')
            ";
            $this->getUniResult($query, $tempEndTime);
                // �����ٷ�
            if ($i == 0) {
                $str_overTime = date('Y/m/d 150000');   // �����Σ����ٷ�
                $end_overTime = date('Y/m/d 151000');   // (workingDayOffset(0)����Ѥ��ʤ��Τϵ����ж��б�)
            } else {
                $str_overTime = workingDayOffset($i) . ' 150000';   // �����Ư���Σ����ٷ�
                $end_overTime = workingDayOffset($i) . ' 151000';
            }
            $query = "
                SELECT overlaps_time_diff(TIMESTAMP '{$now}', TIMESTAMP '{$tempEndTime}',
                TIMESTAMP '{$str_overTime}', TIMESTAMP '{$end_overTime}') + INTERVAL '{$requireTime}'
            ";
            $this->getUniResult($query, $requireTime);
                // ������������β��ν�λ���֤����
            $query = "
                SELECT to_char(TIMESTAMP '{$now}' + INTERVAL '{$requireTime}', 'YYYY/MM/DD HH24:MI:SS')
            ";
            $this->getUniResult($query, $tempEndTime);
            // ��Ư���ֳ��βû�
            if ($i == 0) {
                $str_overTime = date('Y/m/d 171500');   // �����λĶȻ���(workingDayOffset(0)����Ѥ��ʤ��Τϵ����ж��б�)
            } else {
                $str_overTime = workingDayOffset($i) . ' 171500';   // �����Ư���λĶȻ���
            }
            $end_overTime = workingDayOffset($i+1) . ' 083000';     // ����Ư���γ��ϻ��֤�����
            $query = "
                SELECT overlaps_time_diff(TIMESTAMP '{$now}', TIMESTAMP '{$tempEndTime}',
                TIMESTAMP '{$str_overTime}', TIMESTAMP '{$end_overTime}') + INTERVAL '{$requireTime}'
            ";
            $this->getUniResult($query, $requireTime);
            // ��Ư���ֳ��ȵ٤߻��֤��θ������λ���֤����
            $query = "
                SELECT to_char(TIMESTAMP '{$now}' + INTERVAL '{$requireTime}', 'YYYY/MM/DD HH24:MI:SS')
            ";
            $this->getUniResult($query, $planEndTime);
            // ��λ���֤������Ƥ��뤫�����å�
            $query = "
                SELECT (TIMESTAMP '{$tempEndTime}') < (TIMESTAMP '{$planEndTime}')
            ";
            $this->getUniResult($query, $end_check);
            if ($end_check == 't') {    // ɬ�׹����������Ƥ���з��֤�
                $i++;
                continue;
            } else {
                break;
            }
        }
        return $planEndTime;
    }
    
} // Class AssemblyTimeShow_Model End

?>
