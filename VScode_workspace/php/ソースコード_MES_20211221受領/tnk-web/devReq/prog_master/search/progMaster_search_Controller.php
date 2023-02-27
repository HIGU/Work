<?php
//////////////////////////////////////////////////////////////////////////////
// �ץ���������˥塼 �ץ����θ���               MVC Controller ��  //
// Copyright (C) 2010 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2010/01/26 Created   progMaster_search_Controller.php                    //
//////////////////////////////////////////////////////////////////////////////

/****************************************************************************
*                       base class ���쥯�饹�����                         *
****************************************************************************/
///// namespace Controller {} �ϸ��߻��Ѥ��ʤ� �����㡧Controller::prefix_Controller �� $obj = new Controller::prefix_Controller;
///// Common::$sum_page, Common::set_page_rec() (̾������::��ƥ��)
class ProgMasterSearch_Controller
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
        
        //////////// �ӥ��ͥ���ǥ����Υ��󥹥��󥹤��������ץ�ѥƥ�����Ͽ
        $this->model = new ProgMasterSearch_Model();
    }
    
    ///// MVC Control�� �¹ԥ��å����ؤν���
    public function execute()
    {
        //////////// �ꥯ�����ȡ����å�������ν��������
        ///// ��˥塼������ showMenu ���Υǡ��������å� �� ���� (Model�ǻ��Ѥ���)
        $this->Init($this->request, $this->session);
        ///// �ꥯ�����ȤΥ�����������
        switch ($this->request->get('Action')) {
        case 'Search':                                      // �����¹�
            $this->model->setWhere($this->session);
            $this->model->setSQL($this->session);
            break;
        case 'Sort':                                        // �����ȼ¹�
            $this->model->setWhere($this->session);
            $this->model->setOrder($this->session);
            $this->model->setSQL($this->session);
            break;
        case 'SortClear':                                   // �����Ȥβ��
            $this->session->add_local('targetSortItem', '');
            $this->request->add('targetSortItem', '');
            break;
        case 'CommentSave':                                 // �����Ȥ���¸
            $this->model->setComment($this->request, $this->result, $this->session);
            break;
        }
        // $this->model->setWhere($this->session);
        // $this->model->setOrder($this->session);
        // $this->model->setOffset($this->session);
        // $this->model->setLimit($this->session);
        // $this->model->setSQL($this->session);
    }
    
    ///// MVC Control�� View���ؤν���
    public function display()
    {
        //////////// �֥饦�����Υ���å����к���
        $uniq = $this->menu->set_useNotCache('progMasterSearch');
        
        ////////// HTML Header ����Ϥ��ƥ���å��������
        $this->menu->out_html_header();
        
        ////////// MVC �� Model���� View�ѥǡ������� �� View�ν���
        switch ($this->request->get('showMenu')) {
        case 'CondForm':                                    // �������ե�����
            $this->CondFormExecute($this->menu, $this->session, $this->model, $this->request, $uniq);
            break;
        case 'List':                                        // Ajax�� �ꥹ��ɽ��
        case 'ListWin':                                     // Ajax�� �̥�����ɥ���Listɽ��
            // $rows = $this->model->getViewListTable($this->request, $this->result);
            // $res  = $this->result->get_array();
            $this->model->outViewListHTML($this->session, $this->menu);
            $this->ViewListExecute($this->menu, $this->session, $this->model, $this->request, $uniq);
            break;
        case 'Comment':                                     // �̥�����ɥ��ǥ����ȤξȲ��Խ�
            $this->model->getComment($this->request, $this->result);
            require_once ('progMaster_search_ViewEditComment.php');
            break;
        }
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// �ꥯ�����ȡ����å�������ν��������
    protected function Init($request, $session)
    {
        ///// ��˥塼������ showMenu ���Υǡ��������å� �� ����
        // PageKeep�ν���
        $this->InitPageKeep($request, $session);
        // showMenu�ν���
        $this->InitShowMenu($request);
        
        // �����ֹ�ν���
        $this->InitTargetProgId($request, $session);
        // ��������ɤν���
        $this->InitTargetProgMaster_code($request, $session);
        // ê�֤ν���
        $this->InitTargetShelf_no($request, $session);
        // ������Ƥν���
        $this->InitTargetMark($request, $session);
        // ���������ɤν���
        $this->InitTargetDir($request, $session);
        // ���襳���ɤν���
        $this->InitTargetUser_code($request, $session);
        // �����������ɤν���
        $this->InitTargetSize_code($request, $session);
        // ��������ν���
        $this->InitTargetMake_flg($request, $session);
        // ���ʥޥ��������ͤν���
        $this->InitTargetNote_parts($request, $session);
        // ����ޥ��������ͤν���
        $this->InitTargetNote_mark($request, $session);
        // �����ޥ��������ͤν���
        $this->InitTargetNote_shape($request, $session);
        // �������ޥ��������ͤν���
        $this->InitTargetNote_size($request, $session);
        
        //////////// �������ƤΥ��顼�������
        $this->errorCheck($request, $session);
    }
    
    ////////// ���顼�����������ƥ��顼�λ���Ŭ�ڤʥ쥹�ݥ󥹤��֤�
    protected function errorCheck($request, $session)
    {
        if ($this->error != 0) {
            // $request->add('showMenu', 'CondForm');
            $session->add('s_sysmsg', $this->errorMsg);
        }
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
            $request->add('showMenu', 'List');  // �ڡ������椬ɬ�פʤΤǶ���Ū��List�ˤ���
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
        if ($request->get('showMenu') == '') {
            $request->add('showMenu', 'CondForm');  // ���꤬�ʤ�����Condition Form (�������)
        }
    }
    
    ///// �����ֹ�μ����������
    private function InitTargetProgId($request, $session)
    {
        if ($request->get('pid') == '') {
            if ($session->get_local('pid') == '') {
                return;     // ���̵꤬�����ϲ��⤷�ʤ�
            } else {
                // �ꥯ�����Ȥ��ʤ��ƥڡ��������ʳ��ʤ饻�å����򥯥ꥢ��
                if ($request->get('page_keep') == '') $session->add_local('pid', '');
            }
        } else {
            // ���������ɻ�
            $request->add('pid', mb_convert_kana($request->get('pid'), 'a'));
            $session->add_local('pid', $request->get('pid'));
        }
        // ���顼�����å���ɬ�פʥꥯ�����ȤϤ����˵���
    }
    
    ///// ��������ɤμ����������
    private function InitTargetProgMaster_code($request, $session)
    {
        if ($request->get('db') == '') {
            if ($session->get_local('db') == '') {
                return;     // ���̵꤬�����ϲ��⤷�ʤ�
            } else {
                // �ꥯ�����Ȥ��ʤ��ƥڡ��������ʳ��ʤ饻�å����򥯥ꥢ��
                if ($request->get('page_keep') == '') $session->add_local('db', '');
            }
        } else {
            // ���������ɻ�
            $request->add('db', mb_convert_kana($request->get('db'), 'a'));
            $session->add_local('db', $request->get('db'));
        }
        // ���顼�����å���ɬ�פʥꥯ�����ȤϤ����˵���
    }
    
    ///// ê�֤μ����������
    private function InitTargetShelf_no($request, $session)
    {
        if ($request->get('name_comm') == '') {
            if ($session->get_local('name_comm') == '') {
                return;     // ���̵꤬�����ϲ��⤷�ʤ�
            } else {
                // �ꥯ�����Ȥ��ʤ��ƥڡ��������ʳ��ʤ饻�å����򥯥ꥢ��
                if ($request->get('page_keep') == '') $session->add_local('name_comm', '');
            }
        } else {
            // ���������ɻ�
            //$request->add('name_comm', mb_convert_kana($request->get('name_comm'), 'a'));
            $session->add_local('name_comm', mb_convert_encoding($request->get('name_comm'), 'EUC-JP', 'auto'));
            //$session->add_local('name_comm', $request->get('name_comm'));
        }
        // ���顼�����å���ɬ�פʥꥯ�����ȤϤ����˵���
    }
    
    ///// ������Ƥμ����������
    private function InitTargetMark($request, $session)
    {
        if ($request->get('mark') == '') {
            if ($session->get_local('mark') == '') {
                return;     // ���̵꤬�����ϲ��⤷�ʤ�
            } else {
                // �ꥯ�����Ȥ��ʤ��ƥڡ��������ʳ��ʤ饻�å����򥯥ꥢ��
                if ($request->get('page_keep') == '') $session->add_local('mark', '');
            }
        } else {
            // ���������ɻߤ򥳥��ȥ����� ��ޡ��������Ѥ����Ϥ���ͽ��
            // $request->add('mark', mb_convert_kana($request->get('mark'), 'a'));
            ///// Ajax��GET�᥽�åɤ� SJIS �� EUC-JP  POST�᥽�åɤ� UTF-8 �� EUC-JP ���Ѵ�
            $session->add_local('mark', mb_convert_encoding($request->get('mark'), 'EUC-JP', 'auto'));
        }
        // ���顼�����å���ɬ�פʥꥯ�����ȤϤ����˵���
    }
    
    ///// ���������ɤμ����������
    private function InitTargetDir($request, $session)
    {
        if ($request->get('dir') == '') {
            if ($session->get_local('dir') == '') {
                return;     // ���̵꤬�����ϲ��⤷�ʤ�
            } else {
                // �ꥯ�����Ȥ��ʤ��ƥڡ��������ʳ��ʤ饻�å����򥯥ꥢ��
                if ($request->get('page_keep') == '') $session->add_local('dir', '');
            }
        } else {
            // ���������ɻ� <select>�����Ϥ��Ƥ��뤿�ᥳ���ȥ�����
            // $request->add('dir', mb_convert_kana($request->get('dir'), 'a'));
            $session->add_local('dir', $request->get('dir'));
        }
        // ���顼�����å���ɬ�פʥꥯ�����ȤϤ����˵���
    }
    
    ///// ���襳���ɤμ����������
    private function InitTargetUser_code($request, $session)
    {
        if ($request->get('user_code') == '') {
            if ($session->get_local('user_code') == '') {
                return;     // ���̵꤬�����ϲ��⤷�ʤ�
            } else {
                // �ꥯ�����Ȥ��ʤ��ƥڡ��������ʳ��ʤ饻�å����򥯥ꥢ��
                if ($request->get('page_keep') == '') $session->add_local('user_code', '');
            }
        } else {
            // ���������ɻ�
            $request->add('user_code', mb_convert_kana($request->get('user_code'), 'a'));
            $session->add_local('user_code', $request->get('user_code'));
        }
        // ���顼�����å���ɬ�פʥꥯ�����ȤϤ����˵���
    }
    
    ///// �����������ɤμ����������
    private function InitTargetSize_code($request, $session)
    {
        if ($request->get('size_code') == '') {
            if ($session->get_local('size_code') == '') {
                return;     // ���̵꤬�����ϲ��⤷�ʤ�
            } else {
                // �ꥯ�����Ȥ��ʤ��ƥڡ��������ʳ��ʤ饻�å����򥯥ꥢ��
                if ($request->get('page_keep') == '') $session->add_local('size_code', '');
            }
        } else {
            // ���������ɻ� <select>�����Ϥ��Ƥ��뤿�ᥳ���ȥ�����
            // $request->add('size_code', mb_convert_kana($request->get('size_code'), 'a'));
            $session->add_local('size_code', $request->get('size_code'));
        }
        // ���顼�����å���ɬ�פʥꥯ�����ȤϤ����˵���
    }
    
    ///// ��������μ����������
    private function InitTargetMake_flg($request, $session)
    {
        if ($request->get('make_flg') == '') {
            if ($session->get_local('make_flg') == '') {
                return;     // ���̵꤬�����ϲ��⤷�ʤ�
            } else {
                // �ꥯ�����Ȥ��ʤ��ƥڡ��������ʳ��ʤ饻�å����򥯥ꥢ��
                if ($request->get('page_keep') == '') $session->add_local('make_flg', '');
            }
        } else {
            // ���������ɻ� <select>�����Ϥ��Ƥ��뤿�ᥳ���ȥ�����
            // $request->add('make_flg', mb_convert_kana($request->get('make_flg'), 'a'));
            $session->add_local('make_flg', $request->get('make_flg'));
        }
        // ���顼�����å���ɬ�פʥꥯ�����ȤϤ����˵���
    }
    
    ///// ���ʥޥ��������ͤμ����������
    private function InitTargetNote_parts($request, $session)
    {
        if ($request->get('note_parts') == '') {
            if ($session->get_local('note_parts') == '') {
                return;     // ���̵꤬�����ϲ��⤷�ʤ�
            } else {
                // �ꥯ�����Ȥ��ʤ��ƥڡ��������ʳ��ʤ饻�å����򥯥ꥢ��
                if ($request->get('page_keep') == '') $session->add_local('note_parts', '');
            }
        } else {
            ///// Ajax��GET�᥽�åɤ� SJIS �� EUC-JP  POST�᥽�åɤ� UTF-8 �� EUC-JP ���Ѵ�
            $session->add_local('note_parts', mb_convert_encoding($request->get('note_parts'), 'EUC-JP', 'auto'));
        }
        // ���顼�����å���ɬ�פʥꥯ�����ȤϤ����˵���
    }
    
    ///// ����ޥ��������ͤμ����������
    private function InitTargetNote_mark($request, $session)
    {
        if ($request->get('note_mark') == '') {
            if ($session->get_local('note_mark') == '') {
                return;     // ���̵꤬�����ϲ��⤷�ʤ�
            } else {
                // �ꥯ�����Ȥ��ʤ��ƥڡ��������ʳ��ʤ饻�å����򥯥ꥢ��
                if ($request->get('page_keep') == '') $session->add_local('note_mark', '');
            }
        } else {
            ///// Ajax��GET�᥽�åɤ� SJIS �� EUC-JP  POST�᥽�åɤ� UTF-8 �� EUC-JP ���Ѵ�
            $session->add_local('note_mark', mb_convert_encoding($request->get('note_mark'), 'EUC-JP', 'auto'));
        }
        // ���顼�����å���ɬ�פʥꥯ�����ȤϤ����˵���
    }
    
    ///// �����ޥ��������ͤμ����������
    private function InitTargetNote_shape($request, $session)
    {
        if ($request->get('note_shape') == '') {
            if ($session->get_local('note_shape') == '') {
                return;     // ���̵꤬�����ϲ��⤷�ʤ�
            } else {
                // �ꥯ�����Ȥ��ʤ��ƥڡ��������ʳ��ʤ饻�å����򥯥ꥢ��
                if ($request->get('page_keep') == '') $session->add_local('note_shape', '');
            }
        } else {
            ///// Ajax��GET�᥽�åɤ� SJIS �� EUC-JP  POST�᥽�åɤ� UTF-8 �� EUC-JP ���Ѵ�
            $session->add_local('note_shape', mb_convert_encoding($request->get('note_shape'), 'EUC-JP', 'auto'));
        }
        // ���顼�����å���ɬ�פʥꥯ�����ȤϤ����˵���
    }
    
    ///// �������ޥ��������ͤμ����������
    private function InitTargetNote_size($request, $session)
    {
        if ($request->get('note_size') == '') {
            if ($session->get_local('note_size') == '') {
                return;     // ���̵꤬�����ϲ��⤷�ʤ�
            } else {
                // �ꥯ�����Ȥ��ʤ��ƥڡ��������ʳ��ʤ饻�å����򥯥ꥢ��
                if ($request->get('page_keep') == '') $session->add_local('note_size', '');
            }
        } else {
            ///// Ajax��GET�᥽�åɤ� SJIS �� EUC-JP  POST�᥽�åɤ� UTF-8 �� EUC-JP ���Ѵ�
            $session->add_local('note_size', mb_convert_encoding($request->get('note_size'), 'EUC-JP', 'auto'));
        }
        // ���顼�����å���ɬ�פʥꥯ�����ȤϤ����˵���
    }
    
    
    /***** display()�� Private methods ���� *****/
    ///// �������ե������ɽ��
    private function CondFormExecute($menu, $session, $model, $request, $uniq)
    {
        require_once ('progMaster_search_ViewCondForm.php');
        return true;
    }
    
    /***** display()�� Private methods ���� *****/
    ///// ��ʬ�Υ�����ɥ���Ajaxɽ�����̥�����ɥ���ɽ��������
    private function ViewListExecute($menu, $session, $model, $request, $uniq)
    {
        switch ($request->get('showMenu')) {
        case 'List':
            require_once ('progMaster_search_ViewListAjax.php');
            break;
        case 'ListWin':
        default:
            require_once ('progMaster_search_ViewListWin.php');
        }
        return true;
    }
    
} // class ProgMasterSearch_Controller End

?>
