<?php
//////////////////////////////////////////////////////////////////////////////
// �Ұ��ޥ������Υ᡼�륢�ɥ쥹 �Ȳ񡦥��ƥʥ�                          //
//                                                       MVC Controller ��  //
// Copyright (C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/11/15 Created   mailAddress_Controller.php                          //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);       // E_STRICT=2048(php5) E_ALL=2047 debug ��

/****************************************************************************
*                       base class ���쥯�饹�����                         *
****************************************************************************/
///// namespace Controller {} �ϸ��߻��Ѥ��ʤ� �����㡧Controller::equipController �� $obj = new Controller::equipController;
///// Common::$sum_page, Common::set_page_rec() (̾������::��ƥ��)
class mailAddress_Controller
{
    ///// Private properties
    private $showMenu;                  // ��˥塼����
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ///// Constructer ����� {php5 �ܹԤ� __construct() ���ѹ�} {�ǥ��ȥ饯��__destruct()}
    public function __construct($menu, $request, $result, $model)
    {
        //////////// POST Data �ν����������
        ///// ��˥塼������ �ꥯ������ �ǡ�������
        $showMenu = $request->get('showMenu');
        if ($showMenu == '') $showMenu = 'Mail';            // ���꤬�ʤ����ϰ���ɽ��ɽ��(�ä˽��)
        $condition = $request->get('confition');            // ����ɽ�ξ�����
        
        ///// �����ե������ �ꥯ������ �ǡ�������
        $uid        = $request->get('uid');                 // �Ұ��ֹ�
        $mailaddr   = $request->get('mailaddr');            // E_Mail ���ɥ쥹
        
        ///// ��Ͽ������������� �¹Իؼ��ꥯ������
        $mailEdit   = $request->get('mailEdit');            // ���ɥ쥹����Ͽ���ѹ�
        $mailOmit   = $request->get('mailOmit');            // ���ɥ쥹�κ��
        $mailActive = $request->get('mailActive');          // ���ɥ쥹��ͭ����̵��(�ȥ���)
        
        ///// ��Ͽ���Խ� �ǡ����Υꥯ�����ȼ���
        $user_name  = $request->get('user_name');           // �Ұ��λ�̾
        $active     = $request->get('active');              // ͭ����̵��
        
        ////////// MVC �� Model ���� �¹������å�����
        if ($mailEdit != '') {                              // ���ɥ쥹����Ͽ���ѹ�
            if ($model->mail_edit($uid, $mailaddr)) {
                // ��Ͽ�Ǥ����Τ�uid, mailaddr��<input>�ǡ�����ä�
                $request->add('uid', '');
                $request->add('name', '');
                $request->add('mailaddr', '');
            }
        } elseif ($mailOmit != '') {                        // ���ɥ쥹�κ��
            $response = $model->mail_omit($uid, $mailaddr);
            $request->add('uid', '');                       // ������ϥ��ԡ���ɬ�פʤ�
            $request->add('name', '');
            $request->add('mailaddr', '');
        } elseif ($mailActive != '') {                      // ���ɥ쥹��ͭ����̵��(�ȥ���)
            if ($model->mail_activeSwitch($uid, $mailaddr)) {
                $request->add('uid', '');
                $request->add('name', '');
                $request->add('mailaddr', '');
            }
        }
        
        $this->showMenu = $showMenu;
        
    }
    
    ///// MVC View���ν���
    public function display($menu, $request, $result, $model)
    {
        //////////// �֥饦�����Υ���å����к���
        $uniq = $menu->set_useNotCache('meeting');
        
        ///// ��˥塼���� �ꥯ������ �ǡ�������
        $showMenu   = $this->showMenu;                      // __construct()���ѹ����줿�������åȥ�˥塼�����
        $condition = $request->get('condition');            // ����ɽ�ξ�����
        
        ///// �����ե������ �ꥯ������ �ǡ�������
        $uid        = $request->get('uid');                 // �Ұ��ֹ�
        $mailaddr   = $request->get('mailaddr');            // E_Mail ���ɥ쥹
        
        ///// ��Ͽ���Խ� �ǡ����Υꥯ�����ȼ���
        $mailCopy   = $request->get('mailCopy');            // ���ɥ쥹���Խ��ǡ������ԡ�
        $name       = $request->get('name');                // ���ɥ쥹���Խ����γ�ǧ�� ��̾
        
        ////////// MVC �� Model���� View�����Ϥ��ǡ�������
        switch ($showMenu) {
        case 'Mail':                                        // ���ɥ쥹�� ��Ͽ ����ɽ ɽ��
            $rows = $model->getViewMailList($result);
            $res  = $result->get_array();
            $pageControl = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            break;
        }
        
        ////////// HTML Header ����Ϥ��ƥ���å��������
        $menu->out_html_header();
        
        ////////// MVC �� View ���ν���
        switch ($showMenu) {
        case 'Mail':                                        // ���ɥ쥹�� ����ɽ ɽ��
            if ($mailCopy == 'go') {
                $focus    = 'mailaddr';
                $readonly = "readonly style='background-color:#e6e6e6;'";
            } else {
                $focus    = 'uid';
                $readonly = '';
            }
            require_once ('mailAddress_View.php');
            break;
        default:                // �ꥯ�����ȥǡ����˥��顼�ξ��Ͻ���ͤΰ�����ɽ��
            require_once ('mailAddress_View.php');
        }
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
} // End off Class mailAddress_Controller

?>
