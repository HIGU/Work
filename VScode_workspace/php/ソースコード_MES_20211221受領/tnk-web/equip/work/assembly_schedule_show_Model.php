<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ω�����ײ�ɽ(AS/400��)�������塼�� �Ȳ�         MVC Model ��           //
// Copyright (C) 2006-2014 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/01/23 Created   assembly_schedule_show_Model.php                    //
// 2006/02/03 getViewGanttChart($request, $result, $menu)����$menu���ɲ�    //
//            title ����å��ǰ�������ɽ�ξȲ���ɲä�������                //
// 2006/02/04 chmod($this->graph_name, 0666);���ɲ�                         //
// 2006/02/06 �������ʤΥ����å�����ä����˥ޡ������Ȥ����ֿ��ˤ���    //
//            getViewGanttChart()�ץ�ѥƥ����˾嵭��ǽ���ɲ�               //
// 2006/02/07 GanttChart�˽и�Ψ���ɲ� getPickingRatio()�᥽�åɤ��ɲ�      //
//            �ǽ�Ū�ˤ�SubQuery�Ǽ¸� formatPickingRatio()�᥽�å��ɲ�     //
// 2006/02/08 �������ޤǤλ�����餫��������ޤ� �� ����1������������ޤ�   //
//            �и�Ψ�׻���SQLʸ�� division by zero �б����ѹ� (3�ս�)       //
//            GanttChart�Ǵ�λ������������оݷ�Ȱ㤦���caption��ɽ��     //
//            Ajax�Τ����ʸ���������Ѵ��᥽�å� setActivityCSIM() ���ɲ�   //
// 2006/02/24 �����ѥ�˥������֤ˤ�ꣲ����ɽ�������ɲ�                  //
// 2006/03/02 �嵭��ǥե���Ȥ�Ʊ��������ɽ�����ѹ�(����β����٤��Ѥ���)  //
// 2006/03/03 ���ӡ���Ͽ �����Ȳ��Ѥ�setActivityCSIMreal()�᥽�åɤ��ɲ�    //
//            �����Ѥ�����ɽ��Ȳ�Ǥ���褦�˵�ǽ�ɲ�                      //
// 2006/03/05 $graph->img->SetMargin(15,17,10,15)���ɲ� 25��10���ѹ�����    //
// 2006/03/15 ��Ͽ������ɽ���ɲ� �� ���ӹ�������Ͽ������Windowɽ�����ѹ�    //
// 2006/03/17 assembly_time_sum(sche.parts_no, sche.chaku)��sche.kanryou    //
// 2006/05/12 �и�Ψ�򾮿������̤ޤ�getViewPlanList,formatPickingRatio �ѹ� //
// 2006/06/16 ����ȥ��㡼�ȤΤߤ��̥�����ɥ��ǳ���getViewZoomGantt()���ɲ�//
// 2006/06/19 ����եե�����κǽ����������������60�÷вᤷ�Ƥ���й�������//
// 2006/06/22 �嵭�ν����Τ���˥ե�����̾��__construct �ǽ����            //
// 2006/07/08 ����ȥ��㡼�Ȥνĥ����������0��-1��  ɽ���Կ���100�ԤޤǤ�  //
// 2006/07/26 getViewZoomGantt()�᥽�åɤ� libpng error: ������å����ɲ� //
// 2006/10/19 �ꥯ������targetLineMethod(1=��������,2=ʣ������)�ɲäˤ��   //
//            �����ȥ饤���ֹ�ɽ���᥽�å��ɲ� setLineWhere()�᥽�åɤ��ɲ� //
// 2006/11/01 getViewZoomGantt()�᥽�åɤ���Ψ������ɲ� targetScale        //
// 2006/11/07 �饤���ʣ��������˥���ե����ȥ��ʣ��ɽ��getLineTitle()�ɲ�//
// 2007/02/01 �и�Ψ�ξ�������1�墪2��� Uround(3����)formatPickingRatio�ѹ�//
// 2007/08/21 getViewZoomGantt()��copy���Ի���sleep(2)���ɲ�libpng���顼����//
// 2013/05/20 �����ײ����ɽ�����˥ǡ������������or�����ѹ����줿��Τ�    //
//            ��λ�����֤�����٤δؿ����ɲ� plan_add_check()          ��ë //
// 2013/05/23 ����ȥ��㡼��¦��Ʊ���褦�˿����Ѥ���褦���ѹ�         ��ë //
// 2014/05/23 plan_add_check()��ʬ�� �ɲä�plan_add_check()����ɽ��         //
//            �ѹ���plan_chage_check()����ɽ�����ѹ�(���ץ���Ω����)   ��ë //
// 2015/05/20 �����б��ΰ١�������T���ɲ�                              ��ë //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��

require_once ('../../../ComTableMntClass.php');    // TNK ������ �ơ��֥����&�ڡ�������Class


/*****************************************************************************************
* ��Ω�����ײ�ɽ(AS/400��)�������塼�� �Ȳ��� MVC��Model���� base class ���쥯�饹����� *
*****************************************************************************************/
class AssemblyScheduleShow_Model extends ComTableMnt
{
    ///// Private properties
    private $where;                             // ���� SQL��WHERE��
    private $whereNoLine;                       // ���� SQL��WHERE��(Line�������)
    private $GraphName;
                                                // GanttChart�Υե�����̾
    
    ///// public properties
    public  $graph;                             // GanttChart�Υ��󥹥���
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer ����� (php5�ذܹԻ��� __construct() ���ѹ�ͽ��) (�ǥ��ȥ饯��__destruct())
    public function __construct($request)
    {
        ///// �ץ�ѥƥ����ν����
        $this->GraphName = "graph/AssemblyScheduleGanttChart-{$_SESSION['User_ID']}.png";
        
        switch ($request->get('showMenu')) {
        case 'GanttChart':
            // ����WHERE�������
            $where = $this->InitWherePlanList($request);
            if ($request->get('CTM_pageRec') > 100) {
                $request->add('CTM_pageRec', 100);
                $_SESSION['s_sysmsg'] = '����ȥ��㡼�ȤǤϣ������ԤޤǤǤ���\n\n�������Ԥ�Ĵ�����ޤ�����';
            }
            break;
        case 'PlanList':
        default:
            // ����WHERE�������
            $where = $this->InitWherePlanList($request);
        }
        $sql_sum = "
            SELECT count(*) FROM assembly_schedule $where
        ";
        ///// SQLʸ��WHERE���Properties����Ͽ
        $this->where  = $where;
        ///// log file �λ���
        $log_file = 'assembly_schedule_show.log';
        ///// 1�ڡ����Υ쥳���ɿ���ǥե�����ͤ�20��15���ѹ�
        if ($_SERVER['REMOTE_ADDR'] != '10.1.3.67') {
            $pageRec = 15;
        } else {
            $pageRec = 15;  // scheduler-1(�����˥����ѡ�23��15���ѹ�(2006/03/02)
        }
        ///// Constructer ���������� ���쥯�饹�� Constructer���¹Ԥ���ʤ�
        ///// ����Class��Constructer�ϥץ���ޡ�����Ǥ�ǸƽФ�
        parent::__construct($sql_sum, $request, $log_file, $pageRec);
    }
    
    ////////// MVC �� Model ���η�� ɽ���ѤΥǡ�������
    ///// List��    ��Ω�����ײ� ����ɽ
    public function getViewPlanList($request, $result)
    {
        switch ($request->get('targetDateItem')) {
        case 'chaku':
            $order = 'chaku ASC, parts_no ASC';
            break;
        case 'syuka':
            $order = 'syuka ASC, parts_no ASC';
            break;
        case 'kanryou':
        default:
            $order = 'kanryou ASC, parts_no ASC';
        }
        $query = "
            SELECT
                 plan_no        AS �ײ��ֹ�         -- 00
                ,parts_no       AS �����ֹ�         -- 01
                ,substr(midsc, 1, 20)
                                AS ����̾           -- 02
                ,plan - cut_plan - kansei
                                AS �ײ�Ŀ�         -- 03
                ,syuka          AS ������           -- 04
                ,chaku          AS �����           -- 05
                ,kanryou        AS ��λ��           -- 06
                ,CASE
                    WHEN trim(note15) = '' THEN '&nbsp;'
                    ELSE note15
                 END            AS ����             -- 07
                -----------------------------�ꥹ�ȤϾ嵭�ޤ�
                ,plan           AS �ײ��           -- 08
                ,cut_plan       AS ���ڤ��         -- 09
                ,kansei         AS ������           -- 10
                ,(
                    SELECT 
                        CASE
                            WHEN sum(allo_qt) = 0 THEN 0    -- division by zero �б�
                            ELSE
                            Uround(
                                CAST(sum(sum_qt) AS numeric(11, 2)) / CAST(sum(allo_qt) AS numeric(11, 2)) * 100, 2
                            )
                        END
                    FROM allocated_parts WHERE plan_no=sche.plan_no AND assy_no=sche.parts_no
                 )              AS �и�Ψ           -- 11
                ,(SELECT assembly_time_sum(sche.parts_no, sche.kanryou))
                                AS ��Ͽ����         -- 12
            FROM
                assembly_schedule AS sche
            LEFT OUTER JOIN
                miitem ON (parts_no=mipn)
            {$this->where}
            ORDER BY
                {$order}
        ";
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) < 1 ) {
            $_SESSION['s_sysmsg'] = '��Ͽ������ޤ���';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// Get��    ����ȥ��㡼�ȤΥ饤�󥿥��ȥ�ɽ���ѥǡ�������
    public function getLineTitle($request)
    {
        if ($request->get('targetLineMethod') == '1') {
            // ���̻���
            if ($request->get('showLine')) $showLine = $request->get('showLine').' '; else $showLine = '���� ';
        } else {
            // ʣ������
            $arrayLine = $request->get('arrayLine');
            $showLine = '';
            for ($i=0; $i<count($arrayLine); $i++) {
                $showLine .= $arrayLine[$i] . ' ';
            }
        }
        return $showLine;
    }
    
    ///// Get��    ����ȥ��㡼��ɽ���ѥǡ�������
    public function getViewGanttChart($request, $result, $menu)
    {
        // �оݥǡ��������(PlanList�Υǡ�����Ȥ�)
        $rows = $this->getViewPlanList($request, $result);
        if ($rows <= 0) return $rows;   // �ǡ�����̵�����Chart�Ϻ��ʤ�
        // GanttChart�κ���
        $res = $result->get_array();
        
        $graph = new GanttGraph(990, -1, 'auto');   // -1=��ư, 0=�Ǥ������̵���ä�
        $graph->SetShadow();
        $graph->img->SetMargin(15, 17, 10, 15);     // default�� 25��10 ���ѹ�
        // ������ѥ����ȥ����
        $showLine = $this->getLineTitle($request);
        if ($request->get('targetSeiKubun') == '1')
            $sei_kubun = 'ɸ����';
        elseif ($request->get('targetSeiKubun') == '3')
            $sei_kubun = '������';
        else
            $sei_kubun = '����';
        // Add title and subtitle
        $graph->title->Set(mb_convert_encoding("��Ω�����ײ� (����ȥ��㡼��)  ����饤��{$showLine}���ʶ�ʬ��{$sei_kubun}", 'UTF-8'));
        $graph->title->SetFont(FF_GOTHIC, FS_NORMAL, 14);
            // $graph->subtitle->SetFont(FF_GOTHIC, FS_NORMAL, 10);
            // $graph->subtitle->Set(mb_convert_encoding("����饤��{$showLine} ���ʶ�ʬ��{$sei_kubun}", 'UTF-8'));
        // Show day, week and month scale
        $graph->ShowHeaders(GANTT_HDAY | GANTT_HWEEK | GANTT_HMONTH);
        // $graph->ShowHeaders(GANTT_HDAY | GANTT_HMONTH);
        // 1.5 line spacing to make more room
        $graph->SetVMarginFactor(1.0);      // ���μ��Ӥ�ɽ����ȼ�� 2.5��1.0 ��
        // Setup some nonstandard colors
        $graph->SetMarginColor('lightgreen@0.8');
        $graph->SetBox(true, 'yellow:0.6', 2);
        $graph->SetFrame(true, 'darkgreen', 4);
        $graph->scale->divider->SetColor('yellow:0.6');
        $graph->scale->dividerh->SetColor('yellow:0.6');
        // ����̾������
        //$graph->scale->tableTitle->Set(mb_convert_encoding("\n�ײ�No. ����No. ����\n�������ʡ���̾", 'UTF-8'));
        //$graph->scale->tableTitle->SetFont(FF_GOTHIC, FS_NORMAL, 12);
        //$graph->scale->SetTableTitleBackground('darkgreen@0.6');
        //$graph->scale->tableTitle->Show(true);
        $item1 = mb_convert_encoding("���� �ɲ�:�� �ѹ�:��\n�ײ�No. ����No. ����\n�������ʡ���̾", 'UTF-8');
        $item2 = mb_convert_encoding("\n�и�Ψ\n������", 'UTF-8');
        $graph->scale->actinfo->SetColTitles(array($item1, $item2));
        $graph->scale->actinfo->SetFont(FF_GOTHIC, FS_NORMAL, 12);
        $graph->scale->actinfo->vgrid->SetColor('gray');
        $graph->scale->actinfo->SetBackgroundColor('darkgreen@0.6');
        $graph->scale->actinfo->SetColor('darkgray');
        
        // ��󥸤���ꤹ�� �������Ƚ�λ��������
        $year  = substr($request->get('targetDate'), 0, 4);
        $month = substr($request->get('targetDate'), 4, 2);
        $lastDay = last_day($year, $month);
        $graph->scale->SetRange($year.$month.'01', $year.$month.$lastDay);
        // Make the day scale
        $graph->scale->day->SetStyle(DAYSTYLE_SHORTDATE4);
        $graph->scale->day->SetFont(FF_GOTHIC, FS_NORMAL, 12);
        // Instead of week number show the date for the first day in the week
        // on the week scale
        $graph->scale->week->SetStyle(WEEKSTYLE_FIRSTDAY2);
        // Make the week scale font smaller than the default
        // $graph->scale->week->SetFont(FF_FONT0);  // ����ϥ��ꥸ�ʥ�
        $graph->scale->week->SetFont(FF_GOTHIC, FS_NORMAL, 12);
        // Use the short name of the month together with a 2 digit year
        // on the month scale
        $graph->scale->month->SetStyle(MONTHSTYLE_SHORTNAMEYEAR4);
        $graph->scale->month->SetFont(FF_GOTHIC, FS_NORMAL, 14);
        $graph->scale->month->SetFontColor('white');
        $graph->scale->month->SetBackgroundColor('blue');
        // ɽ�����դκǾ��ͤ����
        $graph->scale->AdjustStartEndDay();     // �ޥ˥奢���̵���᥽�åɡ��ץ�ѥƥ������
        $viewStartDate = date('Ymd', $graph->scale->iStartDate);
        // CSIM�ѥǡ��������
        $targ = array();
        $alts = array();
        $num = 0;   // ����հ��֤�����(�����ɲäˤ��)
        for ($r=0; $r<$rows; $r++) {
            $plan_no  = $res[$r][0];    // �ײ��ֹ�
            $assy_no  = $res[$r][1];    // �����ֹ�
            $assy_name= $res[$r][2];    // ����̾
            $plan_zan = $res[$r][3];    // �ײ�Ŀ�
            $syuka    = $res[$r][4];    // ������
            $chaku    = $res[$r][5];    // �����
            $kanryou  = $res[$r][6];    // ��λ��
            $bikou    = $res[$r][7];    // ����
            $plan_pcs = $res[$r][8];    // �ײ��
            $cut_pcs  = $res[$r][9];    // ���ڤ��
            $end_pcs  = $res[$r][10];   // ������
            $ritu     = $res[$r][11];   // �и�Ψ
            $kousu    = $res[$r][12];   // ��Ͽ����
            $assy_name = mb_convert_kana($assy_name, 'k');
            $item  = mb_convert_encoding("{$plan_no} {$assy_no} {$plan_zan}\n{$assy_name}", 'UTF-8');
                                    // ($row, $title, $startdate, $enddate)
            // �и�Ψ����� %�դ�ʸ������֤�
                    // $ritu = $this->getPickingRatio($plan_no, $assy_no);
            $ritu = $this->formatPickingRatio($ritu);
            // �������ͤ�Ĵ�٤�
            if ($kousu) $kousu = (' ' . $kousu); else $kousu = '̤��Ͽ';
            // �и�Ψ����Ͽ������Ϣ�뤵����
            $ritu_kousu = mb_convert_encoding(" {$ritu}\n{$kousu}", 'UTF-8');
            // ��λ�����㤦���ϥ���ץ�����ɽ��
            if (substr($kanryou, 4, 2) != $month && substr($chaku, 4, 2) != $month) {
                $activity[$num] = new GanttBar($num, array($item, $ritu_kousu), $viewStartDate, $viewStartDate);
                if ($this->plan_add_check($plan_no)) {
                    $activity[$num]->title->SetColor('red');
                } elseif ($this->plan_change_check($plan_no)) {
                    $activity[$num]->title->SetColor('blue');
                }
                $activity[$num]->caption->Set(mb_convert_encoding("{$chaku}��{$kanryou}", 'UTF-8'));
                $activity[$num]->caption->SetFont(FF_GOTHIC, FS_NORMAL, 12);
                $activity[$num]->caption->SetColor('blue');
                $activity[$num]->SetPattern(BAND_RDIAG, 'white');
                $activity[$num]->SetFillColor('red');
            } else {
                $activity[$num] = new GanttBar($num, array($item, $ritu_kousu), $chaku, $kanryou);
                // Yellow diagonal line pattern on a red background
                if ($this->plan_add_check($plan_no)) {
                    $activity[$num]->title->SetColor('red');
                } elseif ($this->plan_change_check($plan_no)) {
                    $activity[$num]->title->SetColor('blue');
                }
                $activity[$num]->SetPattern(BAND_RDIAG, 'yellow');
                $activity[$num]->SetFillColor('blue');
                $activity[$num]->SetShadow(true, 'black');
            }
            // $activity[$num]->title->Align('right', 'center');  // �������Ȳ��̤������SetAlign��Ʊ��
            $activity[$num]->title->SetFont(FF_GOTHIC, FS_NORMAL, 10);
            // CSIM �ǡ�������
            $this->setActivityCSIM($activity[$num], $request, $menu, $res[$r]);
            $graph->Add($activity[$num]);
            
            $num++;     // ���μ���ɽ���Τ��ᥤ�󥯥����
            if ($this->plan_chaku_check($plan_no, $strDate, $endDate)) {
                if ($request->get('targetCompleteFlag') == 'no') {
                    $item2 = mb_convert_encoding("����������", 'UTF-8');
                } else {
                    $item2 = mb_convert_encoding("������������", 'UTF-8');
                }
                $activity[$num] = new GanttBar($num, array($item2, '  -'), $strDate, $endDate);
                $activity[$num]->title->SetColor('teal');
            } else {
                if ($request->get('targetCompleteFlag') == 'no') {
                    $item2 = mb_convert_encoding("������̤���", 'UTF-8');
                } else {
                    $item2 = mb_convert_encoding("������̤����", 'UTF-8');
                }
                $activity[$num] = new GanttBar($num, array($item2, '  -'), $strDate, $endDate);
                $activity[$num]->title->SetColor('gray');
            }
            $activity[$num]->title->SetFont(FF_GOTHIC, FS_NORMAL, 12);
            $activity[$num]->SetPattern(BAND_RDIAG, 'yellow');
            $activity[$num]->SetFillColor('teal');
            $activity[$num]->SetShadow(true, 'black');
            // CSIM �ǡ�������
            // $this->setActivityCSIMreal($activity[$num], $request, $menu, $res[$r]);
            $this->setActivityCSIMrealWin($activity[$num], $request, $menu, $res[$r]);
            $graph->Add($activity[$num]);
            
            $num++;
        }
        // ��Ҥε��������������
        $j = 0; $vline = array();   // �����
        for ($i=(-5); $i<37; $i++) {
            $timestamp = mktime(0, 0, 0, $month, $i, $year);
            if (date('w',$timestamp) == 0) continue;    // ������
            if (date('w',$timestamp) == 6) continue;    // ������
            if (day_off($timestamp)) {  // ��Ҥε٤ߤ�����å�
                $vline[$j] = new GanttVLine(date('Ymd', $timestamp));
                $vline[$j]->SetDayOffset(0.5);
                $graph->Add($vline[$j]);
                $j++;
            }
        }
        $graph->Stroke($this->GraphName);
        chmod($this->GraphName, 0666);     // file������rw�⡼�ɤˤ���
        $this->graph = $graph;              // View�ǻ��Ѥ��뤿��graph���֥������Ȥ���¸
        return $rows;
    }
    
    ///// Get��    GanttChart�Υե�����̾���֤�
    public function getGraphName()
    {
        return $this->GraphName;
    }
    
    ///// Get��    GanttChart��إå������ȥܥǥ�����ʬ�䤷�ƥꥶ��Ȥ˥ե�����̾���֤�
    public function getViewZoomGantt($request, $result, $menu)
    {
        // ����եե�����κǽ���������������ƹ������뤫���ꤹ��
        // clearstatcache();   // �ե����륹�ơ������Υ���å���򥯥ꥢ
        if ( (mktime() - filemtime($this->GraphName)) > 60) {   // ����եե����뤬�������줿�Τ�60�����ʤ�
            $this->getViewGanttChart($request, $result, $menu); // ��������
        }
        // ���ꥸ�ʥ�Υ���եե����뤫�饳�ԡ����������[gd-png:  fatal libpng error: IDAT: CRC error]����� 2006/07/26 ADD
        $header_height  = 87;                   // �����������θ��Ф��ι⤵
        $scale = $request->get('targetScale');  // ����������������Ψ����
        $tempGraphName = $this->GraphName . session_id() . '.png';
        while (!copy($this->GraphName, $tempGraphName)) {
            sleep(2);   // ����եե�����򥳥ԡ��Ǥ��ʤ���У��ä��餷�ƥȥ饤
        }
        $src_id = imagecreatefrompng($tempGraphName);
        $src_x  = imagesx($src_id);
        $src_y  = imagesy($src_id);
        $src_header_y   = $header_height;
        $src_body_y     = $src_y - $header_height;
        $dst_header_x   = $src_x * $scale;
        $dst_header_y   = $header_height * $scale;
        $dst_body_x     = $src_x * $scale;
        $dst_body_y     = ($src_y - $header_height) * $scale;
        $dst_header_id  = imagecreatetruecolor($dst_header_x, $dst_header_y);
        $dst_body_id    = imagecreatetruecolor($dst_body_x, $dst_body_y);
        imagecopyresampled($dst_header_id, $src_id, 0, 0, 0, 0, $dst_header_x, $dst_header_y, $src_x, $src_header_y);
        imagecopyresampled($dst_body_id, $src_id, 0, 0, 0, $header_height, $dst_body_x, $dst_body_y, $src_x, $src_body_y);
        $dst_header_file = ('zoom/AssemblyScheduleZoomGanttHeader-' . $_SESSION['User_ID'] . '.png');
        $dst_body_file   = ('zoom/AssemblyScheduleZoomGanttBody-' . $_SESSION['User_ID'] . '.png');
        ImagePng ($dst_header_id, $dst_header_file);
        ImagePng ($dst_body_id, $dst_body_file);
        chmod($dst_header_file, 0666);      // file������rw�⡼�ɤˤ���
        chmod($dst_body_file, 0666);        // file������rw�⡼�ɤˤ���
        ImageDestroy ($dst_header_id);
        ImageDestroy ($dst_body_id);
        ImageDestroy ($src_id);
        if (file_exists($tempGraphName)) {  // 2007/08/21 ����ɲ�
            unlink($tempGraphName); // 2006/07/26 ADD
        }
        $result->add('zoomGanttHeader', $dst_header_file);
        $result->add('zoomGanttbody', $dst_body_file);
        return;
    }
    
    ///// List��    ��Ω�饤�� $this->where(���)��Ǥ� ����ɽ (�ڡ�������ȥ���ʤ�)
    public function getViewLineList(&$result)
    {
        $query = "
            SELECT
                line_no            AS �饤���ֹ�           -- 00
            FROM
                assembly_schedule
            {$this->whereNoLine}
                AND trim(line_no) != ''
            GROUP BY
                line_no
            ORDER BY
                line_no ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_ListNotPageControl($query, $res)) < 1 ) {
            $_SESSION['s_sysmsg'] = '��Ͽ������ޤ���';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// ��Ω�����ײ�ɽ���о������������1��������դ��֤�
    // HTML��form����������select��˥塼��ɽ��
    public function getDateSpanHTML($targetDate)
    {
        $year = substr($targetDate, 0, 4); $mon = substr($targetDate, 4, 2); $day = substr($targetDate, 6, 2);
        $HtmlSource  = "\n";
        $HtmlSource .= "<select name='targetDate' onChange='submit()' style='text-align:right; font-size:12pt; font-weight:bold;'>\n";
        for ($i=(-31); $i<=31; $i++) {
            $timestamp = mktime(0, 0, 0, $mon, ($day + $i), $year);
            if (!day_off($timestamp)) {
                // �Ķ����ʤ�
                $date = date('Ymd', $timestamp);
                if ($targetDate == $date) {
                    $HtmlSource .= "<option value='{$date}' style='color:white;background-color:red;' selected>{$date}</option>\n";
                } else {
                    $HtmlSource .= "<option value='{$date}'>{$date}</option>\n";
                }
            }
        }
        $HtmlSource .= "</select>\n";
        return $HtmlSource;
    }
    
    ///// �и�Ψ����롣�ʤ�٤�assy_no����ꤷ���ۤ����ɤ�(���ְ㤤��Ʊ���ײ��ֹ椬�����礬����)
    public function getPickingRatio($plan_no, $assy_no=false)
    {
        $query = "
            SELECT
                CASE
                    WHEN sum(allo_qt) = 0 THEN 0    -- division by zero �б�
                    ELSE
                    Uround(
                        CAST(sum(sum_qt) AS numeric(11, 2)) / CAST(sum(allo_qt) AS numeric(11, 2)) * 100, 2
                    )
                END
            FROM allocated_parts
        ";
        if (!$assy_no) {
            $query .= " WHERE plan_no='{$plan_no}' AND assy_no='{$assy_no}'";
        } else {
            $query .= " WHERE plan_no='{$plan_no}'";
        }
        $ritu = '  0';
        $this->getUniResult($query, $ritu);     // �ǡ����������$ritu���ͤ�����
        switch (strlen($ritu)) {
            case 1: $ritu = '  ' . $ritu . '%'; break;
            case 2: $ritu = ' ' . $ritu . '%'; break;
            case 3: $ritu = $ritu . '%'; break;
        }
        return $ritu;
    }
    
    ///// �и�Ψ�Υǡ�������դǷ����·���� �ե����ޥåȥ᥽�å�
    public function formatPickingRatio($ritu)
    {
        // �и�Ψ��0.0(����������)�ˤ������� 123 �� 345 ���ѹ�
        // �и�Ψ��0.00(����������)�ˤ������� 345 �� 456 ���ѹ� 2007/02/01 (99.96 �� 100.0�ˤʤäƤ��ޤ�����)
        switch (strlen($ritu)) {
            case 4: $ritu = '  ' . $ritu . '%'; break;
            case 5: $ritu = ' ' . $ritu . '%'; break;
            case 6: $ritu = $ritu . '%'; break;
            default: $ritu = '  0%'; break;
        }
        return $ritu;
    }
    
    ///// Edit Confirm_delete 1�쥳����ʬ�ξܺ٥ǡ�����
    public function getViewDataEdit($request)
    {
        $query = "
            SELECT
                 plan_no        AS �ײ��ֹ�         -- 00
                ,parts_no       AS �����ֹ�         -- 01
                ,substr(midsc, 1, 20)
                                AS ����̾           -- 02
                ,plan - cut_plan - kansei
                                AS �ײ�Ŀ�         -- 03
                ,syuka          AS ������           -- 04
                ,chaku          AS �����           -- 05
                ,kanryou        AS ��λ��           -- 06
                ,CASE
                    WHEN trim(note15) = '' THEN '&nbsp;'
                    ELSE note15
                 END            AS ����             -- 07
                -----------------------------�ꥹ�ȤϾ嵭�ޤ�
                ,plan           AS �ײ��           -- 08
                ,cut_plan       AS ���ڤ��         -- 09
                ,kansei         AS ������           -- 10
                ,(
                    SELECT
                        CASE
                            WHEN sum(allo_qt) = 0 THEN 0    -- division by zero �б�
                            ELSE
                            Uround(
                                CAST(sum(sum_qt) AS numeric(11, 2)) / CAST(sum(allo_qt) AS numeric(11, 2)) * 100, 2
                            )
                        END
                    FROM allocated_parts WHERE plan_no=sche.plan_no AND assy_no=sche.parts_no
                 )              AS �и�Ψ           -- 11
            FROM
                assembly_schedule AS sche
            LEFT OUTER JOIN
                miitem ON (parts_no=mipn)
            WHERE
                plan_no = '{$request->get('plan_no')}'
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) >= 1) {
            $request->add('plan_no',    $res[0][0]);
            $request->add('assy_no',    $res[0][1]);
            $request->add('assy_name',  $res[0][2]);
            $request->add('plan_zan',   $res[0][3]);
            $request->add('syuka',      $res[0][4]);
            $request->add('chaku',      $res[0][5]);
            $request->add('kanryou',    $res[0][6]);
            $request->add('bikou',      $res[0][7]);
            $request->add('plan_pcs',   $res[0][8]);
            $request->add('cut_pcs',    $res[0][9]);
            $request->add('end_pcs',    $res[0][10]);
            $request->add('pick_ratio', $res[0][11]);
        }
        return $rows;
    }
    
    ///// �饤��̾��ɽ���᥽�å�
    public function showLineNameButton($request, $menu, $rowsLine, $resLine, $pageParameter, $uniq)
    {
        $tr = 0; $column = 10;
        $arrayLine = $request->get('arrayLine');
        for ($i=(-1); $i<$rowsLine; $i++) {
            if ($tr == 0) {
                echo "<tr>\n";
            }
            echo "<td class='winbox' align='center' nowrap>\n";
            if ($i == (-1)) {
                echo "<input type='button' name='showLine' value='����' class='pt12b bg'\n";
                echo "    onClick='AssemblyScheduleShow.setLineMethod(\"1\"); AssemblyScheduleShow.targetLineExecute(\"{$menu->out_self()}?showLine=0&showMenu={$request->get('showMenu')}&{$pageParameter}&id={$uniq}\")'\n";
                // ���̻���Τ�
                if ($request->get('showLine') == '') echo "    style='color:red;'\n";
                echo ">\n";
            } else {
                echo "<input type='button' name='showLine' value='{$resLine[$i][0]}' class='pt12b bg'\n";
                echo "    onClick='AssemblyScheduleShow.targetLineExecute(\"{$menu->out_self()}?showLine={$resLine[$i][0]}&showMenu={$request->get('showMenu')}&{$pageParameter}&id={$uniq}\")'\n";
                if ($request->get('targetLineMethod') == '1') {
                    // ���̻���
                    if ($resLine[$i][0] == $request->get('showLine')) echo "    style='color:red;'\n";
                } else {
                    // ʣ������
                    if (array_search($resLine[$i][0], $arrayLine) !== false)
                        echo "    style='color:blue;'\n";
                }
                echo ">\n";
            }
            echo "</td>\n";
            $tr++;
            if ($tr >= $column) {
                echo "</tr>\n";
                $tr = 0;
            }
        }
        if ($tr != 0) {
            while ($tr < $column) {
                echo "    <td class='winbox' width='55'>&nbsp;</td>\n";
                $tr++;
            }
            echo "</tr>\n";
        }
    }
    // �����ɲ÷ײ��Ƚ��������ɲä��줿��Τ�TRUE��
    public function plan_add_check($plan_no)
    {
        $cstr_date = date('Ym') . '01';
        $cend_date = date('Ym') . '99';
        $query = "
            SELECT
                 plan_no        AS �ײ��ֹ�         -- 00
                ,kanryou        AS ��λ��           -- 01
                ,rep_date       AS ������           -- 02
                ,crt_date       AS ������           -- 03
            FROM
                assembly_schedule
            WHERE
                plan_no = '{$plan_no}'
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) >= 1) {
            if ($cstr_date <= $res[0][1] && $cend_date >= $res[0][1]) {
                if ($cstr_date <= $res[0][3] && $cend_date >= $res[0][3]) {
                    return true;
                }
            }
        }
        return false;
    }
    // �����ɲ÷ײ��Ƚ��������ѹ����줿��Τ�TRUE��
    public function plan_change_check($plan_no)
    {
        $cstr_date = date('Ym') . '01';
        $cend_date = date('Ym') . '99';
        $query = "
            SELECT
                 plan_no        AS �ײ��ֹ�         -- 00
                ,kanryou        AS ��λ��           -- 01
                ,rep_date       AS ������           -- 02
                ,crt_date       AS ������           -- 03
            FROM
                assembly_schedule
            WHERE
                plan_no = '{$plan_no}'
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) >= 1) {
            if ($cstr_date <= $res[0][1] && $cend_date >= $res[0][1]) {
                if ($cstr_date <= $res[0][2] && $cend_date >= $res[0][2]) {
                    return true;
                }
            }
        }
        return false;
    }
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// ��Ω�ؼ���˥塼���Խ����¥����å��᥽�å�(���ѥ᥽�å�)
    protected function assemblyAuthUser()
    {
        $LoginUser = $_SESSION['User_ID'];
        $query = "select act_id from cd_table where uid='$LoginUser'";
        if (getUniResult($query, $sid) > 0) {
            switch ($sid) {             // �Ұ��ν�°�������祳���ɤǥ����å�
            case 500:                   // ������ (2005/12/15�ɲ�)
            case 176:
            case 522:
            case 523:
            case 525:
                return true;            // ���ץ���Ω(�������)
            case 551:
            case 175:
            case 560:
            case 537:
            case 534:
                return true;            // ��˥���Ω(��ࡦ���������)
            default:
                if ($_SESSION['Auth'] >= 3) { // �ƥ�����
                    return true;
                }
                return false;
            }
        } else {
            return false;
        }
    }
    
    ////////// �ꥯ�����Ȥˤ��SQLʸ�δ���WHERE�������
    protected function InitWherePlanList($request)
    {
        ///// ���������ϰ�
        switch ($request->get('targetDateSpan')) {
        case '0':   // �������Τ�
            if ($request->get('targetDateItem') == 'chaku') {
                $where = "WHERE chaku = {$request->get('targetDate')} AND assy_site = '01111' AND parts_no != '999999999' AND p_kubun = 'F'";
            } elseif ($request->get('targetDateItem') == 'syuka') {
                $where = "WHERE syuka = {$request->get('targetDate')} AND assy_site = '01111' AND parts_no != '999999999' AND p_kubun = 'F'";
            } else {
                $where = "WHERE kanryou = {$request->get('targetDate')} AND assy_site = '01111' AND parts_no != '999999999' AND p_kubun = 'F'";
            }
            break;
        case '1':   // ��餫��������ޤ� �� ����1������������ޤ�
        default :
            ///// ̤����ʬ��ɽ��=����η�餫��, ����ʬ��ɽ����=����η�餫��
            if ($request->get('targetCompleteFlag') == 'no') {
                // �����1��������
                // �ʲ���  date('Ymd', mktime(0, 0, 0, ��-1, 1��, ǯ)) �������1�������ꤷ�Ƥ��� mktime()�μ�ư������ǽ������
                $strDate = date('Ymd', mktime(0, 0, 0, substr($request->get('targetDate'), 4, 2) - 1, 1, substr($request->get('targetDate'), 0, 4)));
            } else {
                // ��������
                $strDate = substr($request->get('targetDate'), 0, 4) . substr($request->get('targetDate'), 4, 2) . '01';
            }
            if ($request->get('targetDateItem') == 'chaku') {
                $where = "WHERE chaku >= {$strDate} AND chaku <= {$request->get('targetDate')} AND assy_site = '01111' AND parts_no != '999999999' AND p_kubun = 'F'";
            } elseif ($request->get('targetDateItem') == 'syuka') {
                $where = "WHERE syuka >= {$strDate} AND syuka <= {$request->get('targetDate')} AND assy_site = '01111' AND parts_no != '999999999' AND p_kubun = 'F'";
            } else {
                $where = "WHERE kanryou >= {$strDate} AND kanryou <= {$request->get('targetDate')} AND assy_site = '01111' AND parts_no != '999999999' AND p_kubun = 'F'";
            }
        }
        ///// ̤����ʬ������ʬ������ ����   2006/10/19 'AND �� ' AND ���ڡ�����ȴ���Ƥ����Τ���
        if ($request->get('targetCompleteFlag') == 'no') {
             $where .= ' AND (plan - cut_plan - kansei) > 0';
        } else {
             $where .= ' AND (plan - cut_plan) = kansei AND kansei > 0';
        }
        ///// ���ʶ�ʬ�λ���
        switch ($request->get('targetSeiKubun')) {
        case '1':   // ����
            $where .= " AND sei_kubun = '1'";
            break;
        case '2':   // L�ۥ襦
            $where .= " AND sei_kubun = '2'";
            break;
        case '3':   // C����
            $where .= " AND sei_kubun = '3'";
            break;
        case '4':   // L�ԥ��ȥ�
            $where .= " AND sei_kubun = '4'";
            break;
        case '0':   // ����(�ǥե����)
        default :
        }
        ///// ���ʻ������λ���
        if ($request->get('targetDept') == 'C') {
            $where .= " AND dept = 'C'";
        } elseif ($request->get('targetDept') == 'L') {
            $where .= " AND dept = 'L'";
        } elseif ($request->get('targetDept') == 'T') {
            $where .= " AND dept = 'T'";
        }
        ///// ����WHERE���ץ�ѥƥ�����Ͽ
        $this->whereNoLine = $where;
        ///// �饤���ֹ�λ���
        $where .= $this->setLineWhere($request);
        return $where;
    }
    
    ///// �饤��λ�����ˡ�ڤӥ饤�������WHERE��򥻥å�
    protected function setLineWhere($request)
    {
        if ($request->get('targetLineMethod') == '1') {
            // ���̻���
            if ($request->get('showLine') != '') {
                return " AND line_no = '{$request->get('showLine')}'";
            } else {
                return '';
            }
        } else {
            // ʣ������
            $arrayLine = $request->get('arrayLine');
            $i = 0;
            foreach ($arrayLine as $value) {
                if ($i == 0) {
                    $where = ' AND (';
                } else {
                    $where .= ' OR ';
                }
                $where .= "line_no='{$value}'";
                $i++;
            }
            if (isset($where)) {
                $where .= ')';
            } else { 
                $where = " AND line_no=''";
            }
            return $where;
        }
    }
    
    ///// Set��  Activity �� CSIM���� showMenu�����Ƥ�ʸ�������ɤ�����
    protected function setActivityCSIM($activity, $request, $menu, $res)
    {
        $plan_no  = $res[0];    // �ײ��ֹ�
        $assy_no  = $res[1];    // �����ֹ�
        $assy_name= $res[2];    // ����̾
        $plan_zan = $res[3];    // �ײ�Ŀ�
        $syuka    = $res[4];    // ������
        $chaku    = $res[5];    // �����
        $kanryou  = $res[6];    // ��λ��
        $bikou    = $res[7];    // ����
        $plan_pcs = $res[8];    // �ײ��
        $cut_pcs  = $res[9];    // ���ڤ��
        $end_pcs  = $res[10];   // ������
        $ritu     = $res[11];   // �и�Ψ
        $targ1 = "JavaScript:alert('�ײ��ֹ桧{$plan_no}\\n\\n�����ֹ桧{$assy_no}\\n\\n����̾�Ρ�{$assy_name}\\n\\n�ײ����{$plan_pcs}\\n\\n���ڿ���{$cut_pcs}\\n\\n��������{$end_pcs}\\n\\n�ײ�ġ�{$plan_zan}\\n\\n��������{$syuka}\\n\\n�������{$chaku}\\n\\n��λ����{$kanryou}\\n\\n���� �͡�{$bikou}')";
        $alts1 = "�ײ��ֹ桧{$plan_no}�������ֹ桧{$assy_no}���ײ�ġ�{$plan_zan}������̾�Ρ�{$assy_name}";
        $targ2 = "{$menu->out_action('��������ɽ')}?plan_no=".urlencode($plan_no)."&material=1&id={$menu->out_useNotCache()}";
        $alts2 = '���ηײ��ֹ�ΰ������ʹ���ɽ��ɽ�����ޤ���';
        if ($request->get('showMenu') == 'GanttTable') {
            $targ1 = mb_convert_encoding($targ1, 'UTF-8', 'EUC-JP');
            $alts1 = mb_convert_encoding($alts1, 'UTF-8', 'EUC-JP');
            $targ2 = mb_convert_encoding($targ2, 'UTF-8', 'EUC-JP');
            $alts2 = mb_convert_encoding($alts2, 'UTF-8', 'EUC-JP');
        }
        $activity->SetCSIMTarget($targ1, $alts1);
        $activity->title->SetCSIMTarget($targ2, $alts2);
        if ($request->get('material_plan_no') == $plan_no) {
            $activity->title->SetColor('red');  // �ޡ�������
        }
    }
    
    ///// Set��  ���ӥ��㡼���� Activity �� CSIM���� showMenu�����Ƥ�ʸ�������ɤ�����
    protected function setActivityCSIMreal($activity, $request, $menu, $res)
    {
        $plan_no  = $res[0];    // �ײ��ֹ�
        $assy_name= $res[2];    // ����̾
        $targ1 = "{$menu->out_action('���ӹ����Ȳ�')}?showMenu=CondForm&targetPlanNo=" . urlencode($plan_no);
        $alts1 = "����̾��{$assy_name}������Ͽ�����ȼ��ӹ�����Ȳ񤷤ޤ���";
        if ($request->get('showMenu') == 'GanttTable') {
            $targ1 = mb_convert_encoding($targ1, 'UTF-8', 'EUC-JP');
            $alts1 = mb_convert_encoding($alts1, 'UTF-8', 'EUC-JP');
        }
        $activity->SetCSIMTarget($targ1, $alts1);
        $activity->title->SetCSIMTarget($targ1, $alts1);
    }
    
    ///// Set��  ���ӥ��㡼���� Activity �� CSIM���� showMenu�����Ƥ�ʸ�������ɤ����� Window��
    protected function setActivityCSIMrealWin($activity, $request, $menu, $res)
    {
        $plan_no  = $res[0];    // �ײ��ֹ�
        $assy_name= $res[2];    // ����̾
        $targ1 = "javascript:AssemblyScheduleShow.win_open('{$menu->out_action('���ӹ����Ȳ�')}?targetPlanNo=" . urlencode($plan_no) . "&noMenu=yes', 900, 600)";
        $alts1 = "����̾��{$assy_name}������Ͽ�����ȼ��ӹ�����Ȳ񤷤ޤ���";
        if ($request->get('showMenu') == 'GanttTable') {
            $targ1 = mb_convert_encoding($targ1, 'UTF-8', 'EUC-JP');
            $alts1 = mb_convert_encoding($alts1, 'UTF-8', 'EUC-JP');
        }
        $activity->SetCSIMTarget($targ1, $alts1);
        $activity->title->SetCSIMTarget($targ1, $alts1);
    }
    
    ///// ���ꤵ�줿�ײ��ֹ���������������å����Ƥ���кǾ��ͤȺ����ͤ򥻥åȤʤ����19700101�򥻥å�
    protected function plan_chaku_check($plan_no, &$strDate, &$endDate)
    {
        $query = "
            SELECT to_char(str_time, 'YYYYMMDD') FROM assembly_process_time WHERE plan_no='{$plan_no}'
            ORDER BY str_time ASC LIMIT 1
        ";
        $strDate = '19700101';
        $endDate = '19700101';
        if ($this->getUniResult($query, $strDate) > 0) {
            $query = "
                SELECT to_char(str_time, 'YYYYMMDD') FROM assembly_process_time WHERE plan_no='{$plan_no}'
                ORDER BY str_time DESC LIMIT 1
            ";
            $this->getUniResult($query, $endDate);
        } else {
            return false;
        }
        return true;
    }
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ////////// ��Ω���Ӥ��ɲü¹�
    private function ApendExecute($request)
    {
        // ������ last_date last_host ����Ͽ�����������
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        
        $insert_sql = "
            INSERT INTO assembly_process_time
            (group_no, plan_no, user_id, str_time, end_time, plan_all_pcs, plan_pcs, assy_time, last_date, last_host)
            values
            ({$request->get('showGroup')}, '{$request->get('plan_no')}', '{$request->get('user_id')}', '{$request->get('str_time')}', '{$request->get('end_time')}'
            , {$request->get('plan')}, {$request->get('plan')}, {$request->get('assy_time')}, '{$last_date}', '{$last_host}')
        ";
        return $this->execute_Insert($insert_sql);
    }
    
    ////////// ��Ω���Ӥκ���¹�
    private function DeleteExecute($request)
    {
        // ������ last_date last_host ����Ͽ�����������
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        
        // Ʊ����ȷײ�ʬ�Τ����ɬ�פʥǡ�������˻Ĥ�
        $query = "
            SELECT str_time, end_time, user_id FROM assembly_process_time WHERE serial_no={$request->get('serial_no')}
        ";
        $res = array();
        if ($this->getResult2($query, $res) > 0) {
            $request->add('str_time', $res[0][0]);
            $request->add('end_time', $res[0][1]);
            $request->add('user_id',  $res[0][2]);
        } else {
            return false;
        }
        $save_sql = "
            SELECT * FROM assembly_process_time WHERE serial_no={$request->get('serial_no')}
        ";
        $delete_sql = "
            DELETE FROM assembly_process_time
            WHERE serial_no={$request->get('serial_no')}
        ";
        return $this->execute_Delete($delete_sql, $save_sql);
    }
    
    ////////// ��Ω���Ӥν����¹�
    private function EditExecute($request, $session)
    {
        // ������ last_date last_host ����Ͽ�����������
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        $save_sql = "
            SELECT * FROM assembly_process_time WHERE serial_no={$request->get('serial_no')}
        ";
        // �ǽ��ñ�Ȥ��ѹ���¹�
        $update_sql = "
            UPDATE assembly_process_time SET
                plan_no='{$request->get('plan_no')}', user_id='{$request->get('user_id')}',
                str_time='{$request->get('str_time')}', end_time='{$request->get('end_time')}',
                plan_all_pcs={$request->get('plan')}, plan_pcs={$request->get('plan')},
                assy_time={$request->get('assy_time')}, last_date='{$last_date}', last_host='{$last_host}'
            WHERE
                serial_no={$request->get('serial_no')}
        ";
        if (!$this->execute_Update($update_sql, $save_sql)) {
            return false;
        }
        // Ʊ����ȷײ褬¸�ߤ��뤫�����å�
        $query = "
            SELECT serial_no FROM assembly_process_time
            WHERE
                (str_time<='{$session->get_local('pre_str_time')}' AND group_no={$request->get('showGroup')} AND user_id='{$session->get_local('pre_user_id')}')
                AND
                (end_time>='{$session->get_local('pre_end_time')}' AND group_no={$request->get('showGroup')} AND user_id='{$session->get_local('pre_user_id')}')
        ";
        $rows = $this->getResult2($query, $res);
        // �Ұ��ֹ������å���Ʊ����ȷײ�ν�����ʬ��������
        if ($session->get_local('pre_user_id') == $request->get('user_id') && $rows > 0) {
            // �Ұ��ֹ椬Ʊ���ʤΤ�Ʊ����ȷײ��str_time��end_time���ѹ�
            $update_sql = "
                UPDATE assembly_process_time SET
                    str_time='{$request->get('str_time')}', end_time='{$request->get('end_time')}',
                    last_date='{$last_date}', last_host='{$last_host}'
                WHERE
                (str_time<='{$session->get_local('pre_str_time')}' AND group_no={$request->get('showGroup')} AND user_id='{$session->get_local('pre_user_id')}')
                AND
                (end_time>='{$session->get_local('pre_end_time')}' AND group_no={$request->get('showGroup')} AND user_id='{$session->get_local('pre_user_id')}')
            ";
            return $this->execute_Update($update_sql, $save_sql);
        } else {
            // �Ұ��ֹ椬�Ѥ�ä�����ñ�Ȥȸ��ʤ���Ʊ����ȷײ�������ѹ��Ϥ��ʤ�
            // ����Ʊ����ȷײ褬¸�ߤ��ʤ�(�ǽ�ϥȥ�󥶥������ǹԤäƤ�����Ʊ����ȷײ褬¸�ߤ��ʤ�����ñ�Ȥι���������ʤ��ʤ뤿����̤ˤ���)
        }
        return true;
    }
    
} // Class AssemblyScheduleShow_Model End

?>
