<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ω�Υ饤���̹��� �Ƽ殺���                         MVC Controller ��  //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/05/12 Created   assembly_time_graph_Controller.php                  //
// 2006/06/15 ���٤������٤ȹ������٤˥��å���ʬ����(List��DetaileList) //
// 2006/09/15 ����դγ���������λ���Υ��ե��åȽ����ɲ�(����ñ�̤�������)  //
// 2006/09/27 ����ե�����(�����׻���ˡ)�Υ��ץ����(���������׻�)�ɲ�    //
// 2006/09/28 tagetOffsetStr/End�᥽�å���� day_off()��day_off_line()���ѹ�//
// 2006/11/02 ����ղ�������Ψ������ɲ� targetScale                        //
// 2006/11/06 ʣ���饤�������б����뤿�� targetLine �� targetLineArray�ɲ�//
// 2006/11/08 TargetLine()��TargetDateYM()�ν�����礬�դ��ä��Τ���      //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
require_once ('../../../tnk_func.php');     // workingDayOffset(-1), day_off(), date_offset() �ǻ���

/****************************************************************************
*                       base class ���쥯�饹�����                         *
****************************************************************************/
///// namespace Controller {} �ϸ��߻��Ѥ��ʤ� �����㡧Controller::equipController �� $obj = new Controller::equipController;
///// Common::$sum_page, Common::set_page_rec() (̾������::��ƥ��)
class AssemblyTimeGraph_Controller
{
    ///// Private properties
    private $menu;                              // TNK ���ѥ�˥塼���饹�Υ��󥹥���
    private $request;                           // HTTP Controller���Υꥯ������ ���󥹥���
    private $result;                            // HTTP Controller���Υꥶ���   ���󥹥���
    private $session;                           // HTTP Controller���Υ��å���� ���󥹥���
    private $model;                             // �ӥ��ͥ���ǥ����Υ��󥹥���
    private $error = 0;                         // ���顼���������ϥե饰
    private $errorMsg = '';                     // ���顼��å�����
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
        ///// ��˥塼������ showMenu ���Υǡ��������å� �� ���� (Model�ǻ��Ѥ���)
        $this->Init();
        
        //////////// �ӥ��ͥ���ǥ����Υ��󥹥��󥹤��������ץ�ѥƥ�����Ͽ
        $this->model = new AssemblyTimeGraph_Model($this->request);
    }
    
    ///// MVC View���ν���
    public function display()
    {
        //////////// �֥饦�����Υ���å����к���
        $uniq = $this->menu->set_useNotCache('assyTimeComp');
        
        ////////// HTML Header ����Ϥ��ƥ���å��������
        $this->menu->out_html_header();
        
        ////////// MVC �� Model���� View�ѥǡ������� �� View�ν���
        switch ($this->request->get('showMenu')) {
        case 'CondForm':                                    // �������ե�����
            $this->CondFormExecute($this->menu, $this->request, $this->model, $uniq);
            break;
        case 'Graph':                                       // Ajax�� �����ɽ��
            $this->model->outViewGraphHTML($this->request, $this->menu);
            require_once ('assembly_time_graph_ViewGraph.php');
            break;
        case 'List':                                        // Ajax�� �̥�����ɥ���Listɽ��
            // $rows = $this->model->getViewListTable($this->request, $this->result);
            // $res  = $this->result->get_array();
            $this->model->outViewListHTML($this->request, $this->menu);
            $this->ViewListExecute($this->menu, $this->request, $this->model, $uniq);
            break;
        case 'DetaileList':                                 // Ajax�� �̥�����ɥ������� Listɽ��
            $this->model->outViewDetaileListHTML($this->request, $this->menu);
            $this->ViewListExecute($this->menu, $this->request, $this->model, $uniq);
            break;
        case 'CommentSave':                                 // �����Ȥ���¸ �饤���ֹ��ǯ����������
            $this->model->commentSave($this->request);
        case 'Comment':                                     // �̥�����ɥ��ǥ����ȤξȲ��Խ�
            $this->model->getComment($this->request, $this->result);
            require_once ('assembly_time_graph_ViewEditComment.php');
            break;
        }
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// �ꥯ�����ȡ����å�������ν��������
    protected function Init()
    {
        ///// ��˥塼������ showMenu ���Υǡ��������å� �� ����
        // PageKeep�ν���
        $this->InitPageKeep($this->request, $this->session);
        
        // showMenu�ν���
        $this->InitShowMenu($this->request);
        
        // targetLine�ν���     ���:DateYM�����˹Ԥ���
        $this->InitTargetLine($this->request, $this->session);
        
        // targetDateYM�ν���
        $this->InitTargetDateYM($this->request, $this->session);
        
        // targetSupportTime�ν���
        $this->InitTargetSupportTime($this->request, $this->session);
        
        // targetGraphType�ν���
        $this->InitTargetGraphType($this->request, $this->session);
        
        // targetProcess�ν���
        $this->InitTargetProcess($this->request, $this->session);
        
        // targetPlanNo�ν��� �����ȤξȲ��Խ���
        $this->InitTargetPlanNo($this->request);
        
        // targetDateList�ν���
        $this->InitTargetDateList($this->request);
        
        // targetScale�ν���
        $this->InitTargetScale($this->request, $this->session);
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ////////// �ꥯ�����ȡ����å�������ν��������
    ///// ������Ȳ񤷤���������ͤ�����å�
    // page_keep���������rec_no �ڤӥڡ�������ν���
    private function InitPageKeep($request, $session)
    {
        if ($request->get('page_keep') != '') {
            $request->add('showMenu', 'List');  // �����Graph���١��������ڡ��������List����ɬ��
            // ����å������Ԥ˥ޡ�������
            if ($session->get_local('rec_no') != '') {
                $request->add('rec_no', $session->get_local('rec_no'));
            }
            // �ڡ��������� (�ƽФ������Υڡ������᤹)
            if ($session->get_local('viewPage') != '') {
                $request->add('CTM_viewPage', $session->get_local('viewPage'));
            }
            if ($session->get_local('pageRec') != '') {
                $request->add('CTM_pageRec', $session->get_local('pageRec'));
            }
        }
    }
    
    ///// ��˥塼������ showMenu �Υǡ��������å� �� ����
    // showMenu�ν���
    private function InitShowMenu($request)
    {
        $showMenu = $request->get('showMenu');
        if ($showMenu == '') {
            $showMenu = 'CondForm';         // ���꤬�ʤ�����Condition Form (�������)
        }
        $request->add('showMenu', $showMenu);
    }
    
    ///// �о�ǯ��μ����������
    // targetDateYM�ν���
    private function InitTargetDateYM($request, $session)
    {
        $targetDateYM = $request->get('targetDateYM');
        if ($targetDateYM == '') {
            if ($session->get_local('targetDateYM') == '') {
                $targetDateYM = date('Ym');      // ���꤬�ʤ���������
            } else {
                $targetDateYM = $session->get_local('targetDateYM');
            }
        } else {
            ///// targetDateYM�λ��꤬���ä����ϥ��ե��å��ͤ���������
            $session->add_local('targetOffsetStr', '');
            $session->add_local('targetOffsetEnd', '');
        }
        $session->add_local('targetDateYM', $targetDateYM);
        $request->add('targetDateYM', $targetDateYM);
        // targetDateStr �� targetDateEnd ��Ÿ��
        $request->add('targetDateStr', $targetDateYM . '01');   //����ǯ��Σ���
        $YYYY = substr($targetDateYM, 0, 4);                    // �ꥯ������ǯ
        $MM   = substr($targetDateYM, 4, 2);                    // �ꥯ�����ȷ�
        $request->add('targetDateEnd', $targetDateYM . last_day($YYYY, $MM));   // ����ǯ��κǽ���
        
        if ($request->get('targetOffsetStr') != '') {
            $session->add_local('targetOffsetStr', $request->get('targetOffsetStr')+$session->get_local('targetOffsetStr'));
        }
        if ($request->get('targetOffsetEnd') != '') {
            $session->add_local('targetOffsetEnd', $request->get('targetOffsetEnd')+$session->get_local('targetOffsetEnd'));
        }
        $this->targetOffsetStr($request, $session->get_local('targetOffsetStr'));
        $this->targetOffsetEnd($request, $session->get_local('targetOffsetEnd'));
    }
    
    ///// ��Ω�饤��μ����������      2006/11/06 ������ѹ�
    // targetLine�ν���
    private function InitTargetLine($request, $session)
    {
        $targetLine = $request->get('targetLine');
        if (!is_array($targetLine)) {
            if (!is_array($session->get_local('targetLine'))) { // ����Ƚ�Ǥ�����Υ����å��ǹԤ�
                $targetLine = array();                      // ����ν����
            } else {
                $targetLine = $session->get_local('targetLine');
            }
        }
        $session->add_local('targetLine', $targetLine);
        $request->add('targetLine', $targetLine);
    }
    
    ///// �������μ����������
    // targetSupportTime�ν���
    private function InitTargetSupportTime($request, $session)
    {
        $targetSupportTime = $request->get('targetSupportTime');
        if ($targetSupportTime == '') {
            if ($session->get_local('targetSupportTime') == '') {
                $targetSupportTime = '440';             // ���꤬�ʤ����ϣ�����ʿ��440ʬ
            } else {
                $targetSupportTime = $session->get_local('targetSupportTime');
            }
        }
        $session->add_local('targetSupportTime', $targetSupportTime);
        $request->add('targetSupportTime', $targetSupportTime);
    }
    
    ///// �����׻��Υ���ե����פμ����������
    // targetGraphType�ν���
    private function InitTargetGraphType($request, $session)
    {
        $targetGraphType = $request->get('targetGraphType');
        if ($targetGraphType == '') {
            if ($session->get_local('targetGraphType') == '') {
                $targetGraphType = 'avr';             // ���꤬�ʤ����������(ʿ��)�����
            } else {
                $targetGraphType = $session->get_local('targetGraphType');
            }
        }
        $session->add_local('targetGraphType', $targetGraphType);
        $request->add('targetGraphType', $targetGraphType);
    }
    
    ///// ������ʬ�μ����������  ���ߤν�ϻ��Ѥ��ʤ���
    // targetProcess�ν���
    private function InitTargetProcess($request, $session)
    {
        $targetProcess = $request->get('targetProcess');
        if ($targetProcess == '') {
            if ($session->get_local('targetProcess') == '') {
                $targetProcess = 'H';                       // ���꤬�ʤ����ϼ��ȹ���
            } else {
                $targetProcess = $session->get_local('targetProcess');
            }
        }
        $session->add_local('targetProcess', $targetProcess);
        $request->add('targetProcess', $targetProcess);
    }
    
    ///// �ײ��ֹ���Υ����ȤξȲ��Խ��� �ײ��ֹ�ѥ�᡼�� ����������
    // targetPlanNo�ν���
    private function InitTargetPlanNo($request)
    {
        if ($request->get('targetPlanNo') == '') {
            return true;          // ���꤬�ʤ����ϲ��⤷�ʤ���
        }
        if (!is_numeric(substr($request->get('targetPlanNo'), 1, 7))) {
            $this->error = 1;
            $this->errorMsg = '�����ֹ�Σ��夫�飸��ޤǤϿ��������Ϥ��Ʋ�������';
            return false;
        }
        if (strlen($request->get('targetPlanNo')) != 8) {
            $this->error = 1;
            $this->errorMsg = '�����ֹ�ϣ���Ǥ���';
            return false;
        }
        return true;
    }
    
    ///// ����դΥС��򥯥�å�����List���ѥ�᡼�� ����������
    // targetDateList�ν���
    private function InitTargetDateList($request)
    {
        if ($request->get('targetDateList') == '') {
            return true;          // ���꤬�ʤ����ϲ��⤷�ʤ���
        }
        if (strlen($request->get('targetDateList')) != 8) {
            $this->error = 1;
            $this->errorMsg = '����ǯ�����ϣ���Ǥ���';
            return false;
        }
        if ($request->get('showMenu') == 'List') {  // DetaileList ���� �̾�����դǤ��� 20060517
            // 06/05/17 �� 20060517 ���Ѵ�
            $request->add('targetDateList', '20' . substr($request->get('targetDateList'), 0, 2) . substr($request->get('targetDateList'), 3, 2) . substr($request->get('targetDateList'), 6, 2));
        }
        return true;
    }
    
    ///// ����ղ�������Ψ�λ���
    // targetScale�ν���
    private function InitTargetScale($request, $session)
    {
        $targetScale = $request->get('targetScale');
        if ($targetScale == '') {
            if ($session->get_local('targetScale') == '') {
                $targetScale = '1.0';                  // ���꤬�ʤ�����100%
            } else {
                $targetScale = $session->get_local('targetScale');
            }
        }
        $session->add_local('targetScale', $targetScale);
        $request->add('targetScale', $targetScale);
    }
    
    ///// �������Υ��ե��åȽ���
    private function targetOffsetStr($request, $offset)
    {
        $dateStr = $request->get('targetDateStr');
        $yyyy = substr($dateStr, 0, 4);
        $mm   = substr($dateStr, 4, 2);
        $dd   = substr($dateStr, 6, 2);
        $targetLineArray = $request->get('targetLine');
        // $dateStr = date('Ymd', mktime(0, 0, 0, $mm, $dd+($offset), $yyyy)); //������ȵ�����Ƚ�꤬����ʤ�
        $i = 0;
        if ($offset > 0) {
            while ($offset != 0) {
                $i++;
                if (day_off_line(mktime(0, 0, 0, $mm, $dd+($i), $yyyy), $targetLineArray[0])) continue;
                $offset--;
            }
        } elseif ($offset < 0) {
            while ($offset != 0) {
                $i--;
                if (day_off_line(mktime(0, 0, 0, $mm, $dd+($i), $yyyy), $targetLineArray[0])) continue;
                $offset++;
            }
        }
        $dateStr = date('Ymd', mktime(0, 0, 0, $mm, $dd+($i), $yyyy));
        $request->add('targetDateStr', $dateStr);
    }
    
    ///// ��λ���Υ��ե��åȽ���
    private function targetOffsetEnd($request, $offset)
    {
        $dateEnd = $request->get('targetDateEnd');
        $yyyy = substr($dateEnd, 0, 4);
        $mm   = substr($dateEnd, 4, 2);
        $dd   = substr($dateEnd, 6, 2);
        $targetLineArray = $request->get('targetLine');
        // $dateEnd = date('Ymd', mktime(0, 0, 0, $mm, $dd+($offset), $yyyy)); //������ȵ�����Ƚ�꤬����ʤ�
        $i = 0;
        if ($offset > 0) {
            while ($offset != 0) {
                $i++;
                if (day_off_line(mktime(0, 0, 0, $mm, $dd+($i), $yyyy), $targetLineArray[0])) continue;
                $offset--;
            }
        } elseif ($offset < 0) {
            while ($offset != 0) {
                $i--;
                if (day_off_line(mktime(0, 0, 0, $mm, $dd+($i), $yyyy), $targetLineArray[0])) continue;
                $offset++;
            }
        }
        $dateEnd = date('Ymd', mktime(0, 0, 0, $mm, $dd+($i), $yyyy));
        $request->add('targetDateEnd', $dateEnd);
    }
    
    
    /***** display()�� Private methods ���� *****/
    ///// �������ե������ɽ��
    private function CondFormExecute($menu, $request, $model, $uniq)
    {
        require_once ('assembly_time_graph_ViewCondForm.php');
        return true;
    }
    
    /***** display()�� Private methods ���� *****/
    ///// ����դθ��̥С������٤�ɽ��
    private function ViewListExecute($menu, $request, $model, $uniq)
    {
        require_once ('assembly_time_graph_ViewList.php');
        return true;
    }
    
} // class AssemblyTimeGraph_Controller End

?>
