<?php
//////////////////////////////////////////////////////////////////////////////
// �ץ����ޥ������Υ��ƥʥ� MVC Controller��                        //
// Copyright (C) 2010 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2010/01/26 Created   progMaster_input_Controller.php                     //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��

/****************************************************************************
*                       base class ���쥯�饹�����                         *
****************************************************************************/
///// namespace Controller {} �ϸ��߻��Ѥ��ʤ� �����㡧Controller::equipController �� $obj = new Controller::equipController;
///// Common::$sum_page, Common::set_page_rec() (̾������::��ƥ��)
class ProgMaster_Controller
{
    ///// Private properties
    private $current_menu;                  // ��˥塼����
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ///// Constructer ����� {php5 �ܹԤ� __construct() ���ѹ�} {�ǥ��ȥ饯��__destruct()}
    public function __construct($menu, $request, $result, $model)
    {
        //////////// POST Data �ν����������
        ///// ��˥塼�����ѥǡ�������
        $current_menu = $request->get('current_menu');
        if ($current_menu == '') $current_menu = 'list'; // ���꤬�ʤ����ϰ���ɽ��ɽ��(�ä˽��)
        
        ///// ɽ���ѥե������ �ǡ�������
        $pid   = $request->get('pid');        // mipn (�����ֹ�)
        $pname = $request->get('pname');      // midsc(̾��)
        $pdir  = $request->get('pdir');       // mzist(���)
        $pcomment= $request->get('pcomment');     // mepnt(�Ƶ���)
        $db1 = $request->get('db1');      // madat(AS��Ͽ��)
        $db2 = $request->get('db2');      // madat(AS��Ͽ��)
        $db3 = $request->get('db3');      // madat(AS��Ͽ��)
        $db4 = $request->get('db4');      // madat(AS��Ͽ��)
        $db5 = $request->get('db5');      // madat(AS��Ͽ��)
        $db6 = $request->get('db6');      // madat(AS��Ͽ��)
        $db7 = $request->get('db7');      // madat(AS��Ͽ��)
        $db8 = $request->get('db8');      // madat(AS��Ͽ��)
        $db9 = $request->get('db9');      // madat(AS��Ͽ��)
        $db10 = $request->get('db10');      // madat(AS��Ͽ��)
        $db11 = $request->get('db11');      // madat(AS��Ͽ��)
        $db12 = $request->get('db12');      // madat(AS��Ͽ��)
        
        // ���Ϥ��줿����DB�����˵ͤ�����
        if ($db1 == '') {
            if ($db2 != '') {
                $db1 = $db2;
                $db2 = '';
            } elseif ($db3 != '') {
                $db1 = $db3;
                $db3 = '';
            } elseif ($db4 != '') {
                $db1 = $db4;
                $db4 = '';
            } elseif ($db5 != '') {
                $db1 = $db5;
                $db5 = '';
            } elseif ($db6 != '') {
                $db1 = $db6;
                $db6 = '';
            } elseif ($db7 != '') {
                $db1 = $db7;
                $db7 = '';
            } elseif ($db8 != '') {
                $db1 = $db8;
                $db8 = '';
            } elseif ($db9 != '') {
                $db1 = $db9;
                $db9 = '';
            } elseif ($db10 != '') {
                $db1 = $db10;
                $db10 = '';
            } elseif ($db11 != '') {
                $db1 = $db11;
                $db11 = '';
            } elseif ($db12 != '') {
                $db1 = $db12;
                $db12 = '';
            }
        }
        if ($db2 == '') {
            if ($db3 != '') {
                $db2 = $db3;
                $db3 = '';
            } elseif ($db4 != '') {
                $db2 = $db4;
                $db4 = '';
            } elseif ($db5 != '') {
                $db2 = $db5;
                $db5 = '';
            } elseif ($db6 != '') {
                $db2 = $db6;
                $db6 = '';
            } elseif ($db7 != '') {
                $db2 = $db7;
                $db7 = '';
            } elseif ($db8 != '') {
                $db2 = $db8;
                $db8 = '';
            } elseif ($db9 != '') {
                $db2 = $db9;
                $db9 = '';
            } elseif ($db10 != '') {
                $db2 = $db10;
                $db10 = '';
            } elseif ($db11 != '') {
                $db2 = $db11;
                $db11 = '';
            } elseif ($db12 != '') {
                $db2 = $db12;
                $db12 = '';
            }
        }
        if ($db3 == '') {
            if ($db4 != '') {
                $db3 = $db4;
                $db4 = '';
            } elseif ($db5 != '') {
                $db3 = $db5;
                $db5 = '';
            } elseif ($db6 != '') {
                $db3 = $db6;
                $db6 = '';
            } elseif ($db7 != '') {
                $db3 = $db7;
                $db7 = '';
            } elseif ($db8 != '') {
                $db3 = $db8;
                $db8 = '';
            } elseif ($db9 != '') {
                $db3 = $db9;
                $db9 = '';
            } elseif ($db10 != '') {
                $db3 = $db10;
                $db10 = '';
            } elseif ($db11 != '') {
                $db3 = $db11;
                $db11 = '';
            } elseif ($db12 != '') {
                $db3 = $db12;
                $db12 = '';
            }
        }
        if ($db4 == '') {
            if ($db5 != '') {
                $db4 = $db5;
                $db5 = '';
            } elseif ($db6 != '') {
                $db4 = $db6;
                $db6 = '';
            } elseif ($db7 != '') {
                $db4 = $db7;
                $db7 = '';
            } elseif ($db8 != '') {
                $db4 = $db8;
                $db8 = '';
            } elseif ($db9 != '') {
                $db4 = $db9;
                $db9 = '';
            } elseif ($db10 != '') {
                $db4 = $db10;
                $db10 = '';
            } elseif ($db11 != '') {
                $db4 = $db11;
                $db11 = '';
            } elseif ($db12 != '') {
                $db4 = $db12;
                $db12 = '';
            }
        }
        if ($db5 == '') {
            if ($db6 != '') {
                $db5 = $db6;
                $db6 = '';
            } elseif ($db7 != '') {
                $db5 = $db7;
                $db7 = '';
            } elseif ($db8 != '') {
                $db5 = $db8;
                $db8 = '';
            } elseif ($db9 != '') {
                $db5 = $db9;
                $db9 = '';
            } elseif ($db10 != '') {
                $db5 = $db10;
                $db10 = '';
            } elseif ($db11 != '') {
                $db5 = $db11;
                $db11 = '';
            } elseif ($db12 != '') {
                $db5 = $db12;
                $db12 = '';
            }
        }
        if ($db6 == '') {
            if ($db7 != '') {
                $db6 = $db7;
                $db7 = '';
            } elseif ($db8 != '') {
                $db6 = $db8;
                $db8 = '';
            } elseif ($db9 != '') {
                $db6 = $db9;
                $db9 = '';
            } elseif ($db10 != '') {
                $db6 = $db10;
                $db10 = '';
            } elseif ($db11 != '') {
                $db6 = $db11;
                $db11 = '';
            } elseif ($db12 != '') {
                $db6 = $db12;
                $db12 = '';
            }
        }
        if ($db7 == '') {
            if ($db8 != '') {
                $db7 = $db8;
                $db8 = '';
            } elseif ($db9 != '') {
                $db7 = $db9;
                $db9 = '';
            } elseif ($db10 != '') {
                $db7 = $db10;
                $db10 = '';
            } elseif ($db11 != '') {
                $db7 = $db11;
                $db11 = '';
            } elseif ($db12 != '') {
                $db7 = $db12;
                $db12 = '';
            }
        }
        if ($db8 == '') {
            if ($db9 != '') {
                $db8 = $db9;
                $db9 = '';
            } elseif ($db10 != '') {
                $db8 = $db10;
                $db10 = '';
            } elseif ($db11 != '') {
                $db8 = $db11;
                $db11 = '';
            } elseif ($db12 != '') {
                $db8 = $db12;
                $db12 = '';
            }
        }
        if ($db9 == '') {
            if ($db10 != '') {
                $db9 = $db10;
                $db10 = '';
            } elseif ($db11 != '') {
                $db9 = $db11;
                $db11 = '';
            } elseif ($db12 != '') {
                $db9 = $db12;
                $db12 = '';
            }
        }
        if ($db10 == '') {
            if ($db11 != '') {
                $db10 = $db11;
                $db11 = '';
            } elseif ($db12 != '') {
                $db10 = $db12;
                $db12 = '';
            }
        }
        if ($db11 == '') {
            if ($db12 != '') {
                $db11 = $db12;
                $db12 = '';
            }
        }
        ////////// ��ǧ�ե�����Ǽ�ä������줿���Υꥯ�����ȼ���
        $cancel_apend  = $request->get('cancel_apend');
        $cancel_del    = $request->get('cancel_del');
        $cancel_edit   = $request->get('cancel_edit');
        
        /********* ������ *********/
        $prePid = $request->get('prePid');
        $preDir = $request->get('preDir');
        
        ////////// ��ǧ�ե�������Ϥ��ǡ�������
        $confirm_apend  = $request->get('confirm_apend');
        $confirm_edit   = $request->get('confirm_edit');
        $confirm_delete = $request->get('confirm_delete');
        if ($confirm_apend != '') {
            $current_menu = 'confirm_apend';
        } elseif ($confirm_edit != '') {
            $current_menu = 'confirm_edit';
        } elseif ($confirm_delete != '') {
            $current_menu = 'confirm_delete';
        }
        
        ////////// ��ǧ�ե�����Ǽ�ä������줿���Υ��ơ��������������˥塼����
        if ($cancel_apend != '') {
            $current_menu = 'apend';
        } elseif ($cancel_edit != '') {
            $current_menu = 'edit';
        } elseif ($cancel_del != '') {
            $current_menu = 'edit';
        }
        
        //////////////// ��Ͽ������������� POST �ѿ��� �������ѿ�����Ͽ
        $apend  = $request->get('apend');
        $edit   = $request->get('edit');
        $delete = $request->get('delete');
        
        ////////// MVC �� Model ���� �¹������å�����
        if ($apend != '') {         ////////// �ޥ������ɲ�
            $response = $model->table_add($pid, $pname, $pdir, $pcomment, $db1, $db2, $db3, $db4, $db5, $db6, $db7, $db8, $db9, $db10, $db11, $db12);
            if (!$response) $current_menu = 'apend';    // ��Ͽ����ʤ��ä��Τ��ɲò��̤ˤ���
        } elseif ($edit != '') {    ////////// �ޥ����� �ѹ�
            $response = $model->table_change($prePid, $pid, $pname, $pdir, $preDir, $pcomment, $db1, $db2, $db3, $db4, $db5, $db6, $db7, $db8, $db9, $db10, $db11, $db12);
            if (!$response) {
                $current_menu = 'edit';                 // �ѹ�����ʤ��ä��Τ��Խ����̤ˤ���
                $cancel_edit  = '���';                 // �ѹ����Υǡ�����ɽ��
            }
        } elseif ($delete != '') {  ////////// �ޥ������������
            $response = $model->table_delete($pid, $pdir);
            if (!$response) $current_menu = 'edit';     // �������ʤ��ä��Τ��Խ����̤ˤ���
        }
        
        $this->current_menu = $current_menu;
        
        ////////// �ꥯ�����ȥǡ����ΰ������ѹ������ΤǺ���Ͽ
        $request->add('cancel_apend', $cancel_apend);
        $request->add('cancel_del',   $cancel_del);
        $request->add('cancel_edit',  $cancel_edit);
    }
    
    ///// MVC View���ν���
    public function display($menu, $request, $result, $model)
    {
        //////////// �֥饦�����Υ���å����к���
        $uniq = $menu->set_useNotCache('item');
        
        ///// ������إ��֥������ȥ��ԡ�(HTML�������ѿ���)
        $current_menu = $this->current_menu;
        
        ///// ɽ���ѥե������ �ǡ�������
        $pid   = $request->get('pid');      // mipn (�����ֹ�)
        $pid   = str_replace('���㡼��', '#', $pid);
        $pname = $request->get('pname');    // midsc(̾��)
        $pdir  = $request->get('pdir');     // mzist(���)
        $pcomment= $request->get('pcomment');   // mepnt(�Ƶ���)
        $db1 = $request->get('db1');    // madat(AS��Ͽ��)
        $db2 = $request->get('db2');    // madat(AS��Ͽ��)
        $db3 = $request->get('db3');    // madat(AS��Ͽ��)
        $db4 = $request->get('db4');    // madat(AS��Ͽ��)
        $db5 = $request->get('db5');    // madat(AS��Ͽ��)
        $db6 = $request->get('db6');    // madat(AS��Ͽ��)
        $db7 = $request->get('db7');    // madat(AS��Ͽ��)
        $db8 = $request->get('db8');    // madat(AS��Ͽ��)
        $db9 = $request->get('db9');    // madat(AS��Ͽ��)
        $db10 = $request->get('db10');    // madat(AS��Ͽ��)
        $db11 = $request->get('db11');    // madat(AS��Ͽ��)
        $db12 = $request->get('db12');    // madat(AS��Ͽ��)
        /********* ������ *********/
        $prePid = $request->get('prePid');  // �ѹ����������ֹ�
        $preDir = $request->get('preDir');  // �ѹ����������ֹ�
        
        ///// �����ե�����ɤΥꥯ�����ȼ���
        $pidKey   = $request->get('pidKey');      // mipn(�����ֹ�)�Υ����ե������
        
        ////////// MVC �� Model���� View�����Ϥ��ǡ�������
        switch ($current_menu) {
        case 'list':            // �����ƥ� ����ɽ ɽ��
        case 'table':           // �����ƥ� ����ɽ �Υơ��֥����Τ�ɽ��(Ajax��)
            if ($pidKey == '') {
                // �����ե�����ɤ����ꤵ��Ƥ��ʤ�(���)�Τ����ϥե�����Τ�
                $rows = 0; $res = array();
            } else {
                $rows = $model->getViewDataList($result);
                $res  = $result->get_array();
            }
            break;
        case 'edit':            // �ޥ���������
        case 'confirm_delete':  // ����γ�ǧ
            if ($prePid == '') $prePid = $pid;   // �����ֹ椬���ꤵ��Ƥ��ʤ����Ͻ���Ƚ�ꤷ��pid����������
            if ($preDir == '') $preDir = $pdir;  // �����ֹ椬���ꤵ��Ƥ��ʤ����Ͻ���Ƚ�ꤷ��pid����������
            if ($request->get('cancel_edit') == '') {     // ��ǧ�ե�����μ�äλ������Υǡ����򤽤Τޤ޻Ȥ�
                $rows = $model->getViewDataEdit($pid, $pdir, $result);
                $pname = $result->get_once('pname');
                $pcomment= $result->get_once('pcomment');
                $db1 = $result->get_once('db1');
                $db2 = $result->get_once('db2');
                $db3 = $result->get_once('db3');
                $db4 = $result->get_once('db4');
                $db5 = $result->get_once('db5');
                $db6 = $result->get_once('db6');
                $db7 = $result->get_once('db7');
                $db8 = $result->get_once('db8');
                $db9 = $result->get_once('db9');
                $db10 = $result->get_once('db10');
                $db11 = $result->get_once('db11');
                $db12 = $result->get_once('db12');
            }
            break;
        }
        
        ////////// HTML Header ����Ϥ��ƥ���å�����������
        $menu->out_html_header();
        
        ////////// MVC �� View ���ν���
        switch ($current_menu) {
        case 'list':            // ����ɽ ɽ��
            // $pageControll = $model->out_pageControll_HTML($menu->out_self()."?id={$uniq}");
            // $model->set_page_rec(20);     // 1�ǤΥ쥳���ɿ�
            $pageControll = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            require_once ('progMaster_input_ViewList.php');
            break;
        case 'table':           // �и� ����ɽ �Υơ��֥����Τ�ɽ��(Ajax��)
            require_once ('progMaster_input_ViewTable.php');
            break;
        case 'apend':           // �ޥ������ɲ�
            require_once ('progMaster_input_ViewApend.php');
            break;
        case 'edit':            // �ޥ���������
            require_once ('progMaster_input_ViewEdit.php');
            break;
        case 'confirm_apend':   // ��Ͽ�γ�ǧ
        case 'confirm_edit':    // �ѹ��γ�ǧ
        case 'confirm_delete':  // ����γ�ǧ
            require_once ('progMaster_input_ViewConfirm.php');
            break;
        default:                // �ꥯ�����ȥǡ����˥��顼
            require_once ('progMaster_input_ViewList.php');
        }
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
}

?>
