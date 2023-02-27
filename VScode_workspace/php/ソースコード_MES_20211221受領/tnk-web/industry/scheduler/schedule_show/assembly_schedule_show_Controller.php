<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ω�����ײ�ɽ(AS/400��)�������塼�� �Ȳ�         MVC Controller ��      //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/01/23 Created   assembly_schedule_show_Controller.php               //
// 2006/01/26 InitPageKeep()�᥽�åɤ��ɲä����������å��������˹ԥޡ�����//
// 2006/02/03 Init()�Υǥե�����ͤ��ѹ�(��λ����ꡦ�Τߢ��ޤǡ�List��Chart//
//            model->getViewGanttChart()�ΰ�����$this->menu���ɲ�           //
// 2006/02/08 Ajax�ξ��ϥ��å�������¸���ʤ����� || �� && �˽���      //
//            $allo_parts_url = $this->menu->out_action('��������ɽ'); �ɲ� //
// 2006/03/03 InitTargetCompleteFlag()�᥽�åɤ��ɲ� (����ʬ������ɽ��ɽ��) //
// 2006/03/09 ����ͤ��ѹ� �Ķ���������������last_day(), ���������λ��     //
// 2006/06/16 ����ȥ��㡼�ȤΤߤ��̥�����ɥ��ǳ�����ǽ���ɲ� ZoomGantt    //
// 2006/06/22 ������ǳ�����pageParameter�ɲ�                               //
// 2006/10/19 InitLineMethod()���ɲ� targetLineMethod��ʣ���饤���б�       //
// 2006/11/01 ɽ����Ψ�λ��� targetScale ���ɲ�                             //
// 2006/11/09 showMenu��ZoomGanttAjax���ɲä�zoom���̤ǥ���ɤ򥹥ࡼ���� //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��

/****************************************************************************
*                       base class ���쥯�饹�����                         *
****************************************************************************/
///// namespace Controller {} �ϸ��߻��Ѥ��ʤ� �����㡧Controller::equipController �� $obj = new Controller::equipController;
///// Common::$sum_page, Common::set_page_rec() (̾������::��ƥ��)
class AssemblyScheduleShow_Controller
{
    ///// Private properties
    private $menu;                              // TNK ���ѥ�˥塼���饹�Υ��󥹥���
    private $request;                           // HTTP Controller���Υꥯ������ ���󥹥���
    private $result;                            // HTTP Controller���Υꥶ���   ���󥹥���
    private $session;                           // HTTP Controller���Υ��å���� ���󥹥���
    private $model;                             // �ӥ��ͥ���ǥ����Υ��󥹥���
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ///// Constructer ����� {php5 �ܹԤ� __construct() ���ѹ�} {�ǥ��ȥ饯��__destruct()}
    public function __construct($menu)
    {
        ///// MenuHeader ���饹�Υ��󥹥��󥹤� properties ����Ͽ
        if (is_object($menu)) {
            $this->menu = $menu;
        } else {
            exit();
        }
        //////////// �ꥯ�����ȤΥ��󥹥��󥹤���Ͽ
        $this->request = new Request();
        
        //////////// �ꥶ��ȤΥ��󥹥��󥹤���Ͽ
        $this->result = new Result();
        
        //////////// ���å����Υ��󥹥��󥹤���Ͽ
        $this->session = new Session();
        
        //////////// �ꥯ�����ȡ����å�������ν��������
        ///// ��˥塼������ showMenu��showLine �Υǡ��������å� �� ���� (Model�ǻ��Ѥ���)
        ///// targetDate �μ����Ƚ����
        $this->Init();
        
        //////////// �ӥ��ͥ���ǥ����Υ��󥹥��󥹤��������ץ�ѥƥ�����Ͽ
        $this->model = new AssemblyScheduleShow_Model($this->request);
        $this->session->add_local('viewPage', $this->model->get_viewPage());
        $this->session->add_local('pageRec' , $this->model->get_pageRec());
    }
    
    ///// MVC View���ν���
    public function display()
    {
        //////////// �֥饦�����Υ���å����к���
        $uniq = $this->menu->set_useNotCache('processShow');
        
        ////////// HTML Header ����Ϥ��ƥ���å��������
        $this->menu->out_html_header();
        
        ////////// MVC �� Model���� View�ѥǡ������� �� View�ν���
        $rowsLine = $this->model->getViewLineList($this->result);
        $resLine  = $this->result->get_array();
        switch ($this->request->get('showMenu')) {
        case 'PlanList':                                    // ��Ω�����ײ�ɽ ɽ��
            $rows = $this->model->getViewPlanList($this->request, $this->result);
            $res  = $this->result->get_array();
            // $pageControl = $this->model->out_pageControll_HTML($this->menu->out_self()."?id={$uniq}");
            $pageControl = $this->model->out_pageCtlOpt_HTML($this->menu->out_self()."?id={$uniq}");
            $pageParameter = $this->model->get_htmlGETparm() ."&id={$uniq}";
            require_once ('assembly_schedule_show_ViewPlanList.php');
            break;
        case 'ListTable':                                   // �嵭��Ajax�� ɽ��
            $rows = $this->model->getViewPlanList($this->request, $this->result);
            $res  = $this->result->get_array();
            // $pageControl = $this->model->out_pageControll_HTML($this->menu->out_self()."?id={$uniq}");
            $pageControl = $this->model->out_pageCtlOpt_HTML($this->menu->out_self()."?id={$uniq}");
            $pageParameter = $this->model->get_htmlGETparm() ."&id={$uniq}";
            $allo_parts_url = $this->menu->out_action('��������ɽ');
            require_once ('assembly_schedule_show_ViewListTable.php');
            break;
        case 'GanttChart':                                  // �ײ�Υ���ȥ��㡼�� ɽ��
                // $rows = $this->model->getViewGanttChart($this->request, $this->result, $this->menu);
                // $res  = $this->result->get_array();
            // �ǥǡ��������Τ���嵭������˰ʲ�����ߡ��ǻ��Ѥ���(List�����ʤΤǹ�®)
            // $rows = $this->model->getViewPlanList($this->request, $this->result);
            // $res  = $this->result->get_array();
            $pageControl = $this->model->out_pageCtlOpt_HTML($this->menu->out_self()."?id={$uniq}");
            $pageParameter = $this->model->get_htmlGETparm() ."&id={$uniq}";
                // require_once ('assembly_schedule_show_ViewGanttChart.php');
            require_once ('assembly_schedule_show_ViewGanttChartAjax.php'); // ���ߤ�Ajax�б���
            break;
        case 'GanttTable':                                  // �嵭��Ajax�� ɽ��
            $rows = $this->model->getViewGanttChart($this->request, $this->result, $this->menu);
            $res  = $this->result->get_array();
            $pageControl = $this->model->out_pageCtlOpt_HTML($this->menu->out_self()."?id={$uniq}");
            $pageParameter = $this->model->get_htmlGETparm() ."&id={$uniq}";
            require_once ('assembly_schedule_show_ViewGanttTable.php');
            break;
        case 'ZoomGantt':                                  // ����ȥ��㡼�ȤΤߤ��̥�����ɥ��˥���饤��ե졼��� ɽ��
            $rows = $this->model->getViewZoomGantt($this->request, $this->result, $this->menu);
            $pageParameter = $this->model->get_htmlGETparm() ."&id={$uniq}";
            require_once ('assembly_schedule_show_ViewZoomGantt.php');
            // �嵭�������� _ViewZoomGanttHeader.php �� _ViewZoomGanttBody.php �򥤥�饤��ǸƽФ���
            break;
        case 'ZoomGanttAjax':                              // �嵭��Ajax�������
            $rows = $this->model->getViewZoomGantt($this->request, $this->result, $this->menu);
            // $pageParameter = $this->model->get_htmlGETparm() ."&id={$uniq}";
            break;
        }
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// �ꥯ�����ȡ����å�������ν��������
    protected function Init()
    {
        ///// ��˥塼������ showMenu��showLine �Υǡ��������å� �� ����
        // showMenu�ν���
        $this->InitShowMenu();
        // showLine�ν���
        $this->InitShowLine();
        // targetLineMethod�ν���
        $this->InitLineMethod();
        // targetDate�ν���
        $this->InitTargetDate();
        // targetDateSpan�ν���
        $this->InitTargetDateSpan();
        // targetDateItem�ν���
        $this->InitTargetDateItem();
        // targetCompleteFlag�ν���
        $this->InitTargetCompleteFlag();
        // targetSeiKubun�ν���
        $this->InitTargetSeiKubun();
        // targetDept�ν���
        $this->InitTargetDept();
        // targetScale�ν���
        $this->InitTargetScale();
        // PageKeep�ν���
        $this->InitPageKeep();
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ////////// �ꥯ�����ȡ����å�������ν��������
    ///// ��˥塼������ showMenu��showLine �Υǡ��������å� �� ����
    // showMenu�ν���
    private function InitShowMenu()
    {
        $showMenu = $this->request->get('showMenu');
        if ($showMenu == '') {
            if ($this->session->get_local('showMenu') == '') {
                $showMenu = 'GanttChart';         // ���꤬�ʤ����ϥ���ȥ��㡼�� PlanList=�����ײ����
            } else {
                $showMenu = $this->session->get_local('showMenu');
            }
        }
        // Ajax�ξ��ϥ��å�������¸���ʤ�
        if ($showMenu != 'ListTable' && $showMenu != 'GanttTable' && $showMenu != 'ZoomGantt' && $showMenu != 'ZoomGanttAjax') {
            $this->session->add_local('showMenu', $showMenu);
        }
        $this->request->add('showMenu', $showMenu);
    }
    
    // showLine�ν���
    private function InitShowLine()
    {
        $showLine = $this->request->get('showLine');
        if ($showLine == '') {
            if ($this->session->get_local('showLine') == '') {
                $showLine = '';                // ���꤬�ʤ��������ƤΥ饤�����ꤷ����Τȸ��ʤ�
            } else {
                $showLine = $this->session->get_local('showLine');
            }
        }
        if ($showLine == '0') $showLine = ''; // 0 �����Τ��̣���롣
        $this->session->add_local('showLine', $showLine);
        $this->request->add('showLine', $showLine);
    }
    
    // targetLineMethod�ν���
    private function InitLineMethod()
    {
        $LineMethod = $this->request->get('targetLineMethod');
        if ($LineMethod == '') {
            if ($this->session->get_local('targetLineMethod') == '') {
                $LineMethod = '1';              // ���꤬�ʤ�����1=���̻���Ȥ��롣2=ʣ������
            } else {
                $LineMethod = $this->session->get_local('targetLineMethod');
            }
        }
        if ($LineMethod == '1') {
            $this->session->add_local('arrayLine', array());        // �����
        } else {
            // ʣ���饤��arrayLine�ν���
            $arrayLine = $this->session->get_local('arrayLine');
            if ( ($key=array_search($this->request->get('showLine'), $arrayLine)) === false) {
                $arrayLine[] = $this->request->get('showLine');
            } else {
                // unset ($arrayLine[$key]);   // ����Ʊ���饤�󤬻��ꤵ�줿���ϥȥ��������Ǻ������������ư����ɤ���Ѥ��Ƥ��뤿�����ʤ�
            }
            $this->session->add_local('arrayLine', $arrayLine);     // ��¸
            $this->request->add('arrayLine', $arrayLine);
        }
        $this->session->add_local('targetLineMethod', $LineMethod);
        $this->request->add('targetLineMethod', $LineMethod);
    }
    
    ///// ����ǯ�����μ����������
    // targetDate�ν���
    private function InitTargetDate()
    {
        $targetDate = $this->request->get('targetDate');
        if ($targetDate == '') {
            if ($this->session->get_local('targetDate') == '') {
                // $targetDate = workingDayOffset('+0');   // ���꤬�ʤ����ϱĶ���������
                $targetDate = date('Ym') . last_day();      // ���꤬�ʤ�����������
            } else {
                $targetDate = $this->session->get_local('targetDate');
            }
        }
        $this->session->add_local('targetDate', $targetDate);
        $this->request->add('targetDate', $targetDate);
    }
    
    ///// ����ǯ�������ϰ� �����������
    // targetDateSpan�ν���
    private function InitTargetDateSpan()
    {
        $targetDateSpan = $this->request->get('targetDateSpan');
        if ($targetDateSpan == '') {
            if ($this->session->get_local('targetDateSpan') == '') {
                $targetDateSpan = '1';   // ���꤬�ʤ����ϻ������ޤ� (�������Τ�=0)
            } else {
                $targetDateSpan = $this->session->get_local('targetDateSpan');
            }
        }
        $this->session->add_local('targetDateSpan', $targetDateSpan);
        $this->request->add('targetDateSpan', $targetDateSpan);
    }
    
    ///// ����ǯ��������λ��������������������μ����������
    // targetDateItem�ν���
    private function InitTargetDateItem()
    {
        $targetDateItem = $this->request->get('targetDateItem');
        if ($targetDateItem == '') {
            if ($this->session->get_local('targetDateItem') == '') {
                $targetDateItem = 'kanryou';   // ���꤬�ʤ���������� (kanryou, chaku, syuka)
            } else {
                $targetDateItem = $this->session->get_local('targetDateItem');
            }
        }
        $this->session->add_local('targetDateItem', $targetDateItem);
        $this->request->add('targetDateItem', $targetDateItem);
    }
    
    ///// ����ʬ��������̤����ʬ���������μ����������
    // targetCompleteFlag�ν���
    private function InitTargetCompleteFlag()
    {
        $targetCompleteFlag = $this->request->get('targetCompleteFlag');
        if ($targetCompleteFlag == '') {
            if ($this->session->get_local('targetCompleteFlag') == '') {
                $targetCompleteFlag = 'no';   // ���꤬�ʤ�����̤����ʬ (yes=complete, no=incomplete)
            } else {
                $targetCompleteFlag = $this->session->get_local('targetCompleteFlag');
            }
        }
        $this->session->add_local('targetCompleteFlag', $targetCompleteFlag);
        $this->request->add('targetCompleteFlag', $targetCompleteFlag);
    }
    
    ///// ���� ���� ��ʬ�μ����������
    // targetSeiKubun�ν���
    private function InitTargetSeiKubun()
    {
        $targetSeiKubun = $this->request->get('targetSeiKubun');
        if ($targetSeiKubun == '') {
            if ($this->session->get_local('targetSeiKubun') == '') {
                $targetSeiKubun = '0';   // ���꤬�ʤ�����0 (0=����, 1=����, 2=L�ۥ襦, 3=C����, 4=L�ԥ��ȥ�)
            } else {
                $targetSeiKubun = $this->session->get_local('targetSeiKubun');
            }
        }
        $this->session->add_local('targetSeiKubun', $targetSeiKubun);
        $this->request->add('targetSeiKubun', $targetSeiKubun);
    }
    
    ///// ���� ���� �������μ����������
    // targetDept�ν���
    private function InitTargetDept()
    {
        $targetDept = $this->request->get('targetDept');
        if ($targetDept == '') {
            if ($this->session->get_local('targetDept') == '') {
                $targetDept = '0';   // ���꤬�ʤ�����0 (0=����, C=���ץ�, L=��˥�)
            } else {
                $targetDept = $this->session->get_local('targetDept');
            }
        }
        $this->session->add_local('targetDept', $targetDept);
        $this->request->add('targetDept', $targetDept);
    }
    
    ///// �����६��ȥ��㡼�Ȥ���Ψ����
    // targetScale�ν���
    private function InitTargetScale()
    {
        $targetScale = $this->request->get('targetScale');
        if ($targetScale == '') {
            if ($this->session->get_local('targetScale') == '') {
                $targetScale = '1.0';   // ���꤬�ʤ�����1.0��ɽ��
            } else {
                $targetScale = $this->session->get_local('targetScale');
            }
        }
        if ($targetScale < 0.3) $targetScale = '0.3';
        if ($targetScale > 1.7) $targetScale = '1.7';
        $this->session->add_local('targetScale', $targetScale);
        $this->request->add('targetScale', $targetScale);
    }
    
    ///// �ײ��ֹ�ǰ�����������ɽ��Ȳ񤷤���������ͤ�����å�
    // page_keep���������material_plan_no �ڤӥڡ�������ν���
    private function InitPageKeep()
    {
        if ($this->request->get('page_keep') != '') {
            // ����å������ײ��ֹ�ιԤ˥ޡ�������
            if ($this->session->get('material_plan_no') != '') {
                $this->request->add('material_plan_no', $this->session->get('material_plan_no'));
            }
            // �ڡ��������� (�ƽФ������Υڡ������᤹)
            if ($this->session->get_local('viewPage') != '') {
                $this->request->add('CTM_viewPage', $this->session->get_local('viewPage'));
            }
            if ($this->session->get_local('pageRec') != '') {
                $this->request->add('CTM_pageRec', $this->session->get_local('pageRec'));
            }
        }
    }
    
} // class AssemblyScheduleShow_Controller End

?>
