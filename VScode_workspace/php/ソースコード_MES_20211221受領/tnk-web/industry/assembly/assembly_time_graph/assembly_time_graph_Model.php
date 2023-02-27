<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ω�Υ饤���̹��� �Ƽ殺���                             MVC Model ��   //
// Copyright (C) 2006-2020 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/05/12 Created   assembly_time_graph_Model.php                       //
// 2006/05/19 getTargetSupportTimeValues() �ν���ͤ��ɲ� $defaultTime      //
// 2006/06/15 ���٤������٤ȹ������٤˥��å���ʬ����(List��DetaileList) //
// 2006/09/10 getTargetDateYMvalues($request)�᥽�åɤ�������б����ѹ�   //
// 2006/09/15 ����դγ���������λ���Υ��ե��åȽ����ɲ�(����ñ�̤�������)  //
//            getGraphData()�᥽�åɤ򥿥��ॹ����ײ�����ޤ�������б�    //
// 2006/09/17 ����������λ�������Ǥʤ�����(ξ��)���ɲ� (���ϡ����Ρ���λ)   //
// 2006/09/27 ����ɽ���� ORDER BY chaku, plan_no ASC ���ɲ�                 //
// 2006/09/27 ����ե�����(�����׻���ˡ)�Υ��ץ����(���������׻�)�ɲ�    //
// 2006/09/28 getGraphData()�᥽�å���� day_off()��day_off_line()���ѹ�    //
// 2006/09/29 chaku, plan_no ASC �� chaku ASC, kanryou ASC, plan_no ASC ��  //
// 2006/11/02 getViewHTMLgraph()�᥽�åɤ˥���դ���Ψ(��������)���� ���ɲ� //
// 2006/11/04 $option = '\n'; �� $option = "\n"; �����ץߥ��������ս�     //
// 2006/11/06 ʣ���饤�������б� ���С� lineArray, targetLine, titleLine//
// 2007/01/16 �����Υ����ɽ��ON/OFF�ɲ� pastDataView,getGraphData()�ѹ�//
// 2007/02/02 �������٥ꥹ�Ȥ�ƹ��ֹ�����ˤ���getViewHTMLdetaileTable �ѹ�//
// 2007/06/20 ����ι�������դ���Ū��ɽ���Τ����ɲ� getTargetLineValues()//
// 2007/11/08 �������٤ι����������align='center'��align='left'���ѹ�      //
// 2016/06/30 4������ޤ�ɽ�����ѹ�                                    ��ë //
// 2020/06/25 ��׹������ɲ�                                           ��ë //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��

require_once ('../../../daoInterfaceClass.php');    // TNK ������ DAO���󥿡��ե��������饹


/*****************************************************************************************
*       MVC��Model�� ���饹��� daoInterfaceClass(base class) ���쥯�饹���ĥ           *
*****************************************************************************************/
class AssemblyTimeGraph_Model extends daoInterfaceClass
{
    ///// Private properties
    private $where;                             // ���� SQL��WHERE��(����ϥ��ȥ����ɥץ������㡼�ѤΥѥ�᡼����)
    private $lineArray;                         // ʣ���饤���SQL ARRAY[] ������
    private $kousuSumList = 0;                  // ����դ�����ɽ�����ι�׹�����Ǽ
    private $targetLine = '';                   // ��ɽ�饤��
    private $titleLine = '';                    // ʣ���饤��Υ���ե����ȥ���б�
    private $pastDataView = false;              // �����ޤǤι����⥰��ղ� ����(true)/���ʤ�(false)
    
    ///// public properties
    // public  $graph;                             // GanttChart�Υ��󥹥���
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer ����� (php5�ذܹԻ��� __construct() ���ѹ�ͽ��) (�ǥ��ȥ饯��__destruct())
    public function __construct($request)
    {
        /////�ꥯ�����Ȥˤ������
        if ($request->get('targetPastData') == 1) $this->pastDataView = true;
        ///// ����WHERE�������
        switch ($request->get('showMenu')) {
        case 'Graph':
        case 'List':
        case 'DetaileList':
            $this->where = $this->SetInitWhere($request);
            $this->targetLine = $this->setTargetLine($request);
            $this->titleLine = $this->setTitleLine($request);
            break;
        case 'CondForm':
        case 'WaitMsg':
        default:
            $this->where = '';
        }
    }
    
    ///// ʣ����������󤫤���ɽ�饤�������
    public function setTargetLine($request)
    {
        $targetLineArray = $request->get('targetLine');
        return $targetLineArray[0];
    }
    
    ///// ʣ����������󤫤饰����ѤΥ饤��̾�Τ�����
    public function setTitleLine($request)
    {
        $array = $request->get('targetLine');
        $title = '';
        for ($i=0; $i<count($array); $i++) {
            $title .= $array[$i] . ' ';
        }
        return $title;
    }
    
    ///// �о�ǯ���HTML <select> option �ν���
    public function getTargetDateYMvalues($request)
    {
        // �����
        $option = "\n";
        $yyyy = date('Y'); $mm = date('m');
        // $yyyymm = date('Ym');    // �������ä��Τ򲼵��Τ褦���������ѹ�
        // �������ѹ� $mm-1=0����ǯ��1���Ʊ�� 2006/09/10
        $yyyy = date('Y', mktime(0, 0, 0, $mm-1, 1, $yyyy)); $mm = date('m', mktime(0, 0, 0, $mm-1, 1, $yyyy));
        $yyyymm = $yyyy . $mm ;
        for ($i=0; $i<=5; $i++) {   // ��������ޤ�(�����ʤΤ�5)
            if ($request->get('targetDateYM') == $yyyymm) {
                $option .= "<option value='{$yyyymm}' selected>{$yyyy}ǯ{$mm}��</option>\n";
            } else {
                $option .= "<option value='{$yyyymm}'>{$yyyy}ǯ{$mm}��</option>\n";
            }
            $mm++;
            if ($mm > 12) {
                $mm = 1; $yyyy += 1;
            }
            $mm = sprintf('%02d', $mm);
            $yyyymm = $yyyy . $mm;
        }
        return $option;
    }
    
    ///// �о� ��Ω�饤���HTML <select> option �ν���
    public function getTargetLineValues($request)
    {
        // ����ǯ����Υ饤���ֹ����������
        $rows = $this->getViewLineList($request, $arrayLineNo);
        // �����
        $option = "\n";
        for ($i=0; $i<$rows; $i++) {
            // if ($request->get('targetLine') == $arrayLineNo[$i]) {   // 2006/11/06 ñ�Ȼ��꤫��ʣ��������б�
            if (array_search($arrayLineNo[$i], $request->get('targetLine')) !== false) {
                $option .= "<option value='{$arrayLineNo[$i]}' selected>" . mb_convert_kana($arrayLineNo[$i], 'RN') . "</option>\n";
            } else {
                $option .= "<option value='{$arrayLineNo[$i]}'>" . mb_convert_kana($arrayLineNo[$i], 'RN') . "</option>\n";
            }
        }
        $option .= "<option value='3LG1'>���̣ǣ�</option>\n";  // 2007/06/20 ����ι�������դ���Ū��ɽ���Τ����ɲ�
        return $option;
    }
    
    ///// ��Ω�饤��Σ��ͤλ�������HTML <select> option �ν���
    public function getTargetSupportTimeValues($request)
    {
        // �����
        $option = "\n";
        for ($i=(440-60); $i<=620; $i+=5) {  // 440ʬ����5ʬ��ߤ�3���ֻĶȤޤ�(�ѡ��Ȥξ��Τ���-60�ɲ�)
            if ($request->get('targetSupportTime') == '') $defaultTime = 440; else $defaultTime = $request->get('targetSupportTime');
            if ($defaultTime == $i) {
                $option .= "<option value='{$i}' selected>" . mb_convert_kana($i, 'N') . "</option>\n";
            } else {
                $option .= "<option value='{$i}'>" . mb_convert_kana($i, 'N') . "</option>\n";
            }
        }
        return $option;
    }
    
    ///// Graph��    ����պ����ؼ�
    public function outViewGraphHTML($request, $menu)
    {
        // �����HTML�����������
        $listHTML  = $this->getViewHTMLconst('header');
        // ��������HTML�����������
        $listHTML .= $this->getViewHTMLgraph($request, $menu);
        // �����HTML�����������
        $listHTML .= $this->getViewHTMLconst('footer');
        // HTML�ե��������
        $file_name = "graph/assembly_time_graph_ViewGraph-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
        return ;
    }
    
    ////////// MVC �� Model ���η�� ɽ���ѤΥǡ�������
    ///// List��    ����å����줿����եǡ��������� ����ɽ
    public function outViewListHTML($request, $menu)
    {
        // �����HTML�����������
        $listHTML  = $this->getViewHTMLconst('header');
        // ��������HTML�����������
        $listHTML .= $this->getViewHTMLtable($request, $menu);
        // �����HTML�����������
        $listHTML .= $this->getViewHTMLconst('footer');
        // HTML�ե��������
        $file_name = "list/assembly_time_graph_ViewList-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
                /***** �եå���������� *****/
        // �����HTML�����������
        $footHTML  = $this->getViewHTMLconst('header');
        // ��������HTML�����������
        $footHTML .= $this->getViewHTMLfooter($request);
        // �����HTML�����������
        $footHTML .= $this->getViewHTMLconst('footer');
        // HTML�ե��������
        $file_name = "list/assembly_time_graph_ViewListFooter-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $footHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
        return ;
    }
    
    ///// List��    ����å����줿����եǡ����� �������� ����ɽ
    public function outViewDetaileListHTML($request, $menu)
    {
        // �����HTML�����������
        $listHTML  = $this->getViewHTMLconst('header');
        // ��������HTML�����������
        $listHTML .= $this->getViewHTMLdetaileTable($request, $menu);
        // �����HTML�����������
        $listHTML .= $this->getViewHTMLconst('footer');
        // HTML�ե��������
        $file_name = "list/assembly_time_graph_ViewList-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
                /***** �եå���������� *****/
        // �����HTML�����������
        $footHTML  = $this->getViewHTMLconst('header');
        // ��������HTML�����������
        $footHTML .= $this->getViewHTMLfooter($request);
        // �����HTML�����������
        $footHTML .= $this->getViewHTMLconst('footer');
        // HTML�ե��������
        $file_name = "list/assembly_time_graph_ViewListFooter-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $footHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
        return ;
    }
    
    ///// ���ʤΥ����Ȥ���¸
    public function commentSave($request)
    {
        // �����ȤΥѥ�᡼���������å�(���Ƥϥ����å��Ѥ�)
        // if ($request->get('comment') == '') return;  // �����Ԥ��Ⱥ���Ǥ��ʤ�
        if ($request->get('targetPlanNo') == '') return;
        if ($request->get('targetAssyNo') == '') return;
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        $query = "SELECT comment FROM assembly_time_plan_comment WHERE plan_no='{$request->get('targetPlanNo')}'";
        if (getUniResult($query, $comment) < 1) {
            $sql = "
                INSERT INTO assembly_time_plan_comment (assy_no, plan_no, comment, last_date, last_host)
                values ('{$request->get('targetAssyNo')}', '{$request->get('targetPlanNo')}', '{$request->get('comment')}', '{$last_date}', '{$last_host}')
            ";
            if ($this->query_affected($sql) <= 0) {
                $_SESSION['s_sysmsg'] = "�����Ȥ���¸������ޤ���Ǥ�����������ô���Ԥ�Ϣ���Ʋ�������";
            }
        } else {
            $sql = "
                UPDATE assembly_time_plan_comment SET comment='{$request->get('comment')}',
                last_date='{$last_date}', last_host='{$last_host}'
                WHERE plan_no='{$request->get('targetPlanNo')}'
            ";
            if ($this->query_affected($sql) <= 0) {
                $_SESSION['s_sysmsg'] = "�����Ȥ���¸������ޤ���Ǥ�����������ô���Ԥ�Ϣ���Ʋ�������";
            }
        }
        return ;
    }
    
    ///// ���ʤΥ����Ȥ����
    public function getComment($request, $result)
    {
        // �����ȤΥѥ�᡼���������å�(���Ƥϥ����å��Ѥ�)
        if ($request->get('targetAssyNo') == '') return '';
        $query = "
            SELECT  comment  ,
                    trim(substr(midsc, 1, 20))
            FROM miitem LEFT OUTER JOIN
            assembly_time_plan_comment ON(mipn=assy_no)
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
    ////////// �ꥯ�����Ȥˤ��SQLʸ�δ���WHERE�������
    protected function SetInitWhere($request)
    {
        // ���ȥ����ɥץ������㡼�η���
        // SELECT * FROM assembly_schedule_time_line($request->get('targetDateStr'), $request->get('targetDateEnd'), '$request->get('targetLine')')
        $array = $request->get('targetLine');
        for ($i=0; $i<count($array); $i++) {
            if ($i == 0) $lineArray = "'{$array[$i]}'";
            $lineArray .= ", '{$array[$i]}'";
        }
        $this->lineArray = $lineArray;  // �ץ�ѥƥ�������Ͽ
        if ($request->get('showMenu') == 'Graph') {
            $where = "{$request->get('targetDateStr')}, {$request->get('targetDateEnd')}, ARRAY[{$this->lineArray}]";
        } else {
            $where = "{$request->get('targetDateList')}, {$request->get('targetDateList')}, ARRAY[{$this->lineArray}]";
        }
        return $where;
    }
    
    ///// List��    ��Ω�饤�� $this->where(���)��Ǥ� ����ɽ (�ڡ�������ȥ���ʤ�)
    protected function getViewLineList($request, &$arrayLineNo)
    {
        $query = "
            SELECT
                line_no            AS �饤���ֹ�           -- 00
            FROM
                assembly_schedule
            WHERE
                kanryou >= {$request->get('targetDateYM')}01 AND kanryou <= {$request->get('targetDateYM')}31 AND assy_site = '01111' AND parts_no != '999999999' AND p_kubun = 'F'
                AND trim(line_no) != ''
            GROUP BY
                line_no
            ORDER BY
                line_no ASC
        ";
        $res = array();
        $arrayLineNo = array();
        if ( ($rows=$this->getResult2($query, $res)) < 1 ) {
            $_SESSION['s_sysmsg'] = '�饤���ֹ����Ͽ������ޤ���';
        }
        for ($i=0; $i<$rows; $i++) {
            $arrayLineNo[$i] = $res[$i][0];
        }
        return $rows;
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
    ///// Graph��   ��Ω�Υ饤���̹��������
    private function getViewHTMLgraph($request, $menu)
    {
        $rows = $this->getGraphData($request, $graphDataX, $graphDataY, $graphDataY_kousu);
        if ($rows <= 0) {
            // �����
            $listTable = '';
            $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
            $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
            $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td colspan='11' width='100%' align='center' class='winbox'>��Ω�����ײ�ǡ���������ޤ���</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
            return $listTable;
        }
        require_once ('../../../../jpgraph.php');
        require_once ('../../../../jpgraph_bar.php');
        ///// ����դ���Ψ(��������)����
        $width  = (int)(830 * $request->get('targetScale'));
        $height = (int)(500 * $request->get('targetScale'));
        $graph = new Graph($width, $height);               // ����դ��礭�� X/Y
        $graph->SetScale('textlin'); 
        $graph->img->SetMargin(55, 30, 40, 80);     // ����հ��֤Υޡ����� �����岼
        $graph->SetShadow(); 
        $graph->title->SetFont(FF_GOTHIC, FS_NORMAL, 16);
        $graph->title->Set(mb_convert_encoding("{$this->titleLine}�饤������׹��������", 'UTF-8')); 
        $graph->yaxis->title->SetFont(FF_GOTHIC, FS_NORMAL, 12);
        $graph->yaxis->title->Set(mb_convert_encoding('�Ϳ�', 'UTF-8'));
        $graph->yaxis->title->SetMargin(10, 0, 0, 0);
        // Setup X-scale 
        $graph->xaxis->SetTickLabels($graphDataX);  // ��������
        $graph->xaxis->SetFont(FF_GOTHIC, FS_NORMAL, 12);
        $graph->xaxis->SetLabelAngle(60); 
        // Create the bar plots 
        $bplot = new BarPlot($graphDataY); 
        $bplot->SetWidth(0.6);
        // Setup color for gradient fill style 
        $bplot->SetFillGradient('darkgreen', 'lightsteelblue', GRAD_CENTER);
        // Set color for the frame of each bar
        $bplot->SetColor('navy');
        $bplot->value->SetFormat('%0.1f');          // �������̥ե����ޥå�
        $bplot->value->Show();                      // ����ɽ��
        // Set CSIMTarget
        $targ = array();
        $alts = array();
        $total_kousu = 0;
        for ($i=0; $i<$rows; $i++) {
            $targ[$i] = "JavaScript:AssemblyTimeGraph.win_open('{$menu->out_self()}?showMenu=List&targetDateList={$graphDataX[$i]}&noMenu=yes', 950, 600)";
            $alts[$i] = "��׹����� {$graphDataY_kousu[$i]}ʬ�Ǥ���";
            $total_kousu = $total_kousu + $graphDataY_kousu[$i];
        }
        $graph->title->Set(mb_convert_encoding("{$this->titleLine}�饤������׹�������� ��׹�����{$total_kousu}", 'UTF-8')); 
        $bplot->SetCSIMTargets($targ, $alts); 
        $graph->Add($bplot);
        $graph_name = "graph/assembly_time_graph_{$_SESSION['User_ID']}.png";
        $graph->Stroke($graph_name);
        chmod($graph_name, 0666);                   // file������rw�⡼�ɤˤ���
        
        $listTable = "\n";
        $listTable .= "<table width='100%' border='0'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td align='left'>\n";
        $listTable .= "            <input class='pageButton' type='button' name='offsetStr1' value='��������' onClick='parent.AssemblyTimeGraph.parameter=\"&targetOffsetStr=-1\"; parent.AssemblyTimeGraph.AjaxLoadTable(\"Graph\", \"showAjax\");'>\n";
        $listTable .= "            ����\n";
        $listTable .= "            <input class='pageButton' type='button' name='offsetStr2' value='�����墪' onClick='parent.AssemblyTimeGraph.parameter=\"&targetOffsetStr=+1\"; parent.AssemblyTimeGraph.AjaxLoadTable(\"Graph\", \"showAjax\");'>\n";
        $listTable .= "        </td>\n";
        $listTable .= "        <td align='center'>\n";
        $listTable .= "            <input class='pageButton' type='button' name='offsetStr3' value='<<������' onClick='parent.AssemblyTimeGraph.parameter=\"&targetOffsetStr=-1\"; parent.AssemblyTimeGraph.parameter+=\"&targetOffsetEnd=-1\"; parent.AssemblyTimeGraph.AjaxLoadTable(\"Graph\", \"showAjax\");'>\n";
        $listTable .= "            ����\n";
        $listTable .= "            <input class='pageButton' type='button' name='offsetEnd3' value='������>>' onClick='parent.AssemblyTimeGraph.parameter=\"&targetOffsetStr=+1\"; parent.AssemblyTimeGraph.parameter+=\"&targetOffsetEnd=+1\"; parent.AssemblyTimeGraph.AjaxLoadTable(\"Graph\", \"showAjax\");'>\n";
        $listTable .= "        </td>\n";
        $listTable .= "        <td align='right'>\n";
        $listTable .= "            <input class='pageButton' type='button' name='offsetEnd1' value='��������' onClick='parent.AssemblyTimeGraph.parameter=\"&targetOffsetEnd=-1\"; parent.AssemblyTimeGraph.AjaxLoadTable(\"Graph\", \"showAjax\");'>\n";
        $listTable .= "            ��λ\n";
        $listTable .= "            <input class='pageButton' type='button' name='offsetEnd2' value='�����墪' onClick='parent.AssemblyTimeGraph.parameter=\"&targetOffsetEnd=+1\"; parent.AssemblyTimeGraph.AjaxLoadTable(\"Graph\", \"showAjax\");'>\n";
        $listTable .= "        </td>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td align='center' colspan='3'>\n";
        $listTable .= "            {$graph->GetHTMLImageMap('kousu_map')}\n";
        $listTable .= "            <img src='assembly_time_graph_{$_SESSION['User_ID']}.png" . '?id=' . time() . "' ismap usemap='#kousu_map' alt='�饤���� ���� �����' border='0'>\n";
        $listTable .= "        </td>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        // return mb_convert_encoding($listTable, 'UTF-8');
        return $listTable;
    }
    
    
    ///// Get��   ����饤��������� ��׹����ǡ��������
    private function getGraphData($request, &$graphDataX, &$graphDataY, &$graphDataY_kousu)
    {
        $rows = 0;
        $graphDataX = array();
        $graphDataY = array();
        $strTimeStamp = mktime(0, 0, 0, substr($request->get('targetDateStr'), 4, 2), substr($request->get('targetDateStr'), 6, 2), substr($request->get('targetDateStr'), 0, 4));
        $endTimeStamp = mktime(0, 0, 0, substr($request->get('targetDateEnd'), 4, 2), substr($request->get('targetDateEnd'), 6, 2), substr($request->get('targetDateEnd'), 0, 4));
        for ($i=$strTimeStamp; $i<=$endTimeStamp; $i+=86400) {
            if (day_off_line($i, $this->targetLine)) {
                continue;
            }
            $date = date('Ymd', $i);
            if ($request->get('targetGraphType') == 'avr') {
                $query = "SELECT sum(kousu_sum) FROM assembly_schedule_time_lineArray_average({$date}, {$date}, ARRAY[{$this->lineArray}])";
            } else {
                $query = "SELECT sum(kousu_sum) FROM assembly_schedule_time_lineArray({$date}, {$date}, ARRAY[{$this->lineArray}])";
            }
            $kousu = 0;
            if ($this->pastDataView || $date >= date('Ymd')) {
                $this->getUniResult($query, $kousu);
            }
            $graphDataX[] = substr($date, 2, 2) . '/' . substr($date, 4, 2) . '/' . substr($date, 6, 2);
            $graphDataY[] = Uround($kousu / $request->get('targetSupportTime'), 1);
            $graphDataY_kousu[] = Uround($kousu, 1);
            $rows++;
        }
        return $rows;
    }
    
    ///// List��   ��Ω�Υ饤���̹�������դ� ���٥ǡ�������
    private function getViewHTMLtable($request, $menu)
    {
        $query = $this->getQueryStatement($request);
        // �����
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) < 1 ) {
            $listTable .= "    <tr>\n";
            $listTable .= "        <td colspan='11' width='100%' align='center' class='winbox'>��Ω�����ײ�ǡ���������ޤ���</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        } else {
            for ($i=0; $i<$rows; $i++) {
                // if ($res[$i][10] != '') {   // �����Ȥ�����п����Ѥ���
                //     $listTable .= "    <tr onDblClick='AssemblyTimeGraph.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPlanNo=" . urlencode($res[$i][0]) . "&targetAssyNo=" . urlencode($res[$i][1]) . "\", 600, 235)' title='�����Ȥ���Ͽ����Ƥ��ޤ������֥륯��å��ǥ����ȤξȲ��Խ�������ޤ���' style='background-color:#e6e6e6;'>\n";
                // } else {
                //     $listTable .= "    <tr onDblClick='AssemblyTimeGraph.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPlanNo=" . urlencode($res[$i][0]) . "&targetAssyNo=" . urlencode($res[$i][1]) . "\", 600, 235)' title='���ߥ����Ȥ���Ͽ����Ƥ��ޤ��󡣥��֥륯��å��ǥ����ȤξȲ��Խ�������ޤ���'>\n";
                // }
                $listTable .= "    <tr>\n";
                $listTable .= "        <td class='winbox' width=' 3%' align='right' >" . ($i+1) . "</td>\n";                    // ���ֹ�
                $listTable .= "        <td class='winbox' width='10%' align='center'>{$res[$i][0]}</td>\n";                     // �ײ��ֹ�
                $listTable .= "        <td class='winbox' width='13%' align='center'>{$res[$i][1]}</td>\n";                     // �����ֹ�
                $listTable .= "        <td class='winbox' width='22%' align='left'>" . mb_convert_kana($res[$i][2], 'k') . "</td>\n";   // ����̾
                $listTable .= "        <td class='winbox' width=' 8%' align='right' >" . number_format($res[$i][3]) . "</td>\n";// �ײ�Ŀ�
                $listTable .= "        <td class='winbox' width='12%' align='center'>{$res[$i][4]}</td>\n";                     // �����
                $listTable .= "        <td class='winbox' width='12%' align='center'>{$res[$i][5]}</td>\n";                     // ��λ��
                $listTable .= "        <td class='winbox' width='10%' align='center'>{$res[$i][6]}</td>\n";                     // ����
                $listTable .= "        <td class='winbox' width='10%' align='right' >{$res[$i][7]}</td>\n";                     // ��׹���
                $listTable .= "    </tr>\n";
                $this->kousuSumList += $res[$i][7];
            }
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        }
        // return mb_convert_encoding($listTable, 'UTF-8');
        return $listTable;
    }
    
    ///// List��   ��Ω�Υ饤���̹�������դ� ���� ���٥ǡ�������
    private function getViewHTMLdetaileTable($request, $menu)
    {
        $query = $this->getQueryStatement2($request);
        // �����
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) < 1 ) {
            $listTable .= "    <tr>\n";
            $listTable .= "        <td colspan='11' width='100%' align='center' class='winbox'>��Ω�����ײ�ǡ���������ޤ���</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        } else {
            $res[-1][0] = '';   // ���ߡ��Ѥ˽����
            $j          = 0;    // �ײ��ֹ�ñ�̤ι��ֹ�(�ƹ��ֹ�)
            for ($i=0; $i<$rows; $i++) {
                // if ($res[$i][10] != '') {   // �����Ȥ�����п����Ѥ���
                //     $listTable .= "    <tr onDblClick='AssemblyTimeGraph.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPlanNo=" . urlencode($res[$i][0]) . "&targetAssyNo=" . urlencode($res[$i][1]) . "\", 600, 235)' title='�����Ȥ���Ͽ����Ƥ��ޤ������֥륯��å��ǥ����ȤξȲ��Խ�������ޤ���' style='background-color:#e6e6e6;'>\n";
                // } else {
                //     $listTable .= "    <tr onDblClick='AssemblyTimeGraph.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPlanNo=" . urlencode($res[$i][0]) . "&targetAssyNo=" . urlencode($res[$i][1]) . "\", 600, 235)' title='���ߥ����Ȥ���Ͽ����Ƥ��ޤ��󡣥��֥륯��å��ǥ����ȤξȲ��Խ�������ޤ���'>\n";
                // }
                $listTable .= "    <tr>\n";
                if ($res[$i][0] == $res[$i-1][0]) { // �ƹ��ֹ桦�Ʒײ衦�����ʡ�������̾ ����ɽ���������ѹ�
                    $listTable .= "        <td class='winbox' width=' 3%' align='right' >&nbsp;</td>\n";                    // ���ֹ�ʤ�
                    $listTable .= "        <td class='winbox' width='10%' align='center'>&nbsp;</td>\n";                    // �ײ��ֹ�ʤ�
                    $listTable .= "        <td class='winbox' width='13%' align='center'>&nbsp;</td>\n";                    // �����ֹ�ʤ�
                    $listTable .= "        <td class='winbox' width='13%' align='left'>&nbsp;</td>\n";                      // ����̾�ʤ�
                    $listTable .= "        <td class='winbox' width=' 8%' align='right' >&nbsp;</td>\n";                    // �ײ�Ŀ��ʤ�
                    $listTable .= "        <td class='winbox' width='12%' align='center'>&nbsp;</td>\n";                    // ������ʤ�
                    $listTable .= "        <td class='winbox' width='12%' align='center'>&nbsp;</td>\n";                    // ��λ���ʤ�
                    $listTable .= "        <td class='winbox' width='10%' align='center'>&nbsp;</td>\n";                    // ���֤ʤ�
                } else {
                    $j++;   // �ƹ��ֹ�
                    $listTable .= "        <td class='winbox' width=' 3%' align='right' >" . $j . "</td>\n";                    // ���ֹ�
                    $listTable .= "        <td class='winbox' width='10%' align='center'>{$res[$i][0]}</td>\n";                 // �ײ��ֹ�
                    $listTable .= "        <td class='winbox' width='13%' align='center'>{$res[$i][1]}</td>\n";                 // �����ֹ�
                    $listTable .= "        <td class='winbox' width='13%' align='left'>" . mb_convert_kana($res[$i][2], 'k') . "</td>\n";   // ����̾
                    $listTable .= "        <td class='winbox' width=' 8%' align='right' >" . number_format($res[$i][3]) . "</td>\n";// �ײ�Ŀ�
                    $listTable .= "        <td class='winbox' width='12%' align='center'>{$res[$i][4]}</td>\n";                 // �����
                    $listTable .= "        <td class='winbox' width='12%' align='center'>{$res[$i][5]}</td>\n";                 // ��λ��
                    $listTable .= "        <td class='winbox' width='10%' align='center'>{$res[$i][6]}</td>\n";                 // ����
                }
                $listTable .= "        <td class='winbox' width=' 6%' align='right' >{$res[$i][7]}</td>\n";                     // �����ֹ�
                $listTable .= "        <td class='winbox' width=' 6%' align='left'  >{$res[$i][8]}</td>\n";                     // ��������
                $listTable .= "        <td class='winbox' width=' 7%' align='right' >{$res[$i][9]}</td>\n";                     // ��׹���
                $listTable .= "    </tr>\n";
                $this->kousuSumList += $res[$i][9];
            }
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        }
        // return mb_convert_encoding($listTable, 'UTF-8');
        return $listTable;
    }
    
    ///// List��   ��Ω�Υ饤���̹�������դ� ���٥ǡ��� �եå����������
    private function getViewHTMLfooter($request)
    {
        // �����
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        if ($request->get('showMenu') == 'List') {
            $listTable .= "        <td class='winbox' width='90%' align='right'>���׹���(ʬ)</td>\n";
            $listTable .= "        <td class='winbox' width='10%' align='right'>" . number_format($this->kousuSumList, 1) . "</td>\n";
        } else {
            $listTable .= "        <td class='winbox' width='93%' align='right'>���׹���(ʬ)</td>\n";
            $listTable .= "        <td class='winbox' width=' 7%' align='right'>" . number_format($this->kousuSumList, 1) . "</td>\n";
        }
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        return $listTable;
    }
    
    ///// List��   �饤���� ��Ω�����ײ� ���� ����ɽ
    private function getQueryStatement($request)
    {
        $query = "
            SELECT   plan_no        AS �ײ��ֹ�         -- 00
                    ,assy_no        AS �����ֹ�         -- 01
                    ,substr(midsc, 1, 16)
                                    AS ����̾           -- 02
                    ,zan_pcs        AS �ײ�Ŀ�         -- 03
                    ,to_char(chaku, 'FM0000/00/00')
                                    AS �����           -- 04
                    ,to_char(kanryou, 'FM0000/00/00')
                                    AS ��λ��           -- 05
                    ,CASE
                        WHEN trim(note15) = '' THEN '&nbsp;'
                        ELSE substr(note15, 1, 8)
                     END            AS ����             -- 06
                    ,sum(Uround(kousu_sum, 1))
                                    AS ��׹���         -- 07
            FROM
        ";
        if ($request->get('targetGraphType') == 'avr') {
            $query .= "    assembly_schedule_time_lineArray_average({$this->where})";
        } else {
            $query .= "    assembly_schedule_time_lineArray({$this->where})";
        }
        $query .= "
            LEFT OUTER JOIN
                miitem ON (assy_no=mipn)
            -- LEFT OUTER JOIN
            --     assembly_schedule_time_line_comment USING (plan_no, line_no)
            GROUP BY
                plan_no, assy_no, midsc, zan_pcs, chaku, kanryou, note15
            ORDER BY
                chaku ASC, kanryou ASC, plan_no ASC
        ";
        return $query;
    }
    
    ///// List��   �饤���� ��Ω�����ײ� �������� ���� ����ɽ
    private function getQueryStatement2($request)
    {
        $query = "
            SELECT   plan_no        AS �ײ��ֹ�         -- 00
                    ,assy_no        AS �����ֹ�         -- 01
                    ,substr(midsc, 1, 16)
                                    AS ����̾           -- 02
                    ,zan_pcs        AS �ײ�Ŀ�         -- 03
                    ,to_char(chaku, 'FM0000/00/00')
                                    AS �����           -- 04
                    ,to_char(kanryou, 'FM0000/00/00')
                                    AS ��λ��           -- 05
                    ,CASE
                        WHEN trim(note15) = '' THEN '&nbsp;'
                        ELSE substr(note15, 1, 8)
                     END            AS ����             -- 06
                    ,pro_no         AS �����ֹ�         -- 07
                    ,pro_mark       AS ��������         -- 08
                    ,Uround(kousu_sum, 1)
                                    AS ��׹���         -- 09
            FROM
        ";
        if ($request->get('targetGraphType') == 'avr') {
            $query .= "    assembly_schedule_time_lineArray_average({$this->where})";
        } else {
            $query .= "    assembly_schedule_time_lineArray({$this->where})";
        }
        $query .= "
            LEFT OUTER JOIN
                miitem ON (assy_no=mipn)
            -- LEFT OUTER JOIN
            --     assembly_schedule_time_line_comment USING (plan_no, line_no)
            ORDER BY
                chaku ASC, kanryou ASC, plan_no ASC, pro_no ASC
        ";
        return $query;
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
<title>��Ω�Υ饤���̹��������</title>
<script type='text/javascript' src='/base_class.js'></script>
<link rel='stylesheet' href='/menu_form.css' type='text/css' media='screen'>
<link rel='stylesheet' href='../assembly_time_graph.css' type='text/css' media='screen'>
<style type='text/css'>
<!--
body {
    background-image:none;
}
-->
</style>
<script type='text/javascript' src='../assembly_time_graph.js'></script>
</head>
<body style='background-color:#d6d3ce;'>
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
    
} // Class AssemblyTimeGraph_Model End

?>
