<?php
////////////////////////////////////////////////////////////////////////////////
// ����ֳ���ȿ���                                                           //
//                                                         MVC Controller ��  //
// Copyright (C) 2021-2021 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2021/10/20 Created over_time_work_report_Controller.php                    //
// 2021/11/01 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);       // E_STRICT=2048(php5) E_ALL=2047 debug ��

/****************************************************************************
*                       base class ���쥯�饹�����                         *
****************************************************************************/
///// namespace Controller {} �ϸ��߻��Ѥ��ʤ� �����㡧Controller::equipController �� $obj = new Controller::equipController;
///// Common::$sum_page, Common::set_page_rec() (̾������::��ƥ��)
class over_time_work_report_Controller
{
    ///// Private properties

    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ///// Constructer ����� {php5 �ܹԤ� __construct() ���ѹ�} {�ǥ��ȥ饯��__destruct()}
    public function __construct($request, $model)
    {
        //////////// POST Data �ν����������
        ///// ��˥塼������ �ꥯ������ �ǡ�������
        if ($request->get('showMenu') == '') {
            $request->add('showMenu', 'Appli');              // ���꤬�ʤ����ϰ���ɽ��ɽ��(�ä˽��)
        }
    }
    
    ///// MVC View���ν���
    public function display($menu, $request, $result, $model)
    {
        $debug = $request->get('debug');   // �ǥХå��ե饰����

        //////////// �֥饦�����Υ���å����к���
        $uniq = $menu->set_useNotCache('over_time_work_report');

        ///// ��˥塼���� �ꥯ������ �ǡ�������
        $showMenu   = $request->get('showMenu');            // �������åȥ�˥塼�����

        $pageControll = $model->out_pageCtlOpt_HTML($menu->out_self());

        ////////// MVC �� Model���� View�����Ϥ��ǡ�������
        // ������桼����UID
        $login_uid = $model->getUID();
        
        // ����������������
        $date = $request->get('w_date');        // �������ռ���
        $bumon = $request->get('ddlist_bumon'); // �����������
        
        // ��������åȡʾȲ��ñ����
        if( $request->get('ddlist_year') == "" ) {
            $def_y = date('Y'); $def_m = date('m'); $def_d = date('d'); // ����͡�������
        } else {
            $def_y = $request->get('ddlist_year');  // ���򤵤줿 ǯ
            $def_m = $request->get('ddlist_month'); // ���򤵤줿 ��
            $def_d = $request->get('ddlist_day');   // ���򤵤줿 ��
        }

        // ��ҥ��������ε�������������
        $holiday = json_encode($model->getHolidayRang($def_y-1,$def_y+1));

        switch ($showMenu) {
            case 'Appli':   // ���������ϡ˲���
                if( $model->ReportCreate($request) ) {  // DB�δ��ܥǡ������� OK �ʤ�
                    $model->AppliUp($request);      // ���������Ͽ����
                    $model->AppliAdd($request);     // ����Ԥ��ɲý���
                    $model->UpComment($request);    // �����ȹ�������
                }
                $cancel_uid = $request->get('cancel_uid');
                if( $cancel_uid ) {   // [��ü¹�]�ܥ��󤬲����줿��
                    $model->Cancel($request);
                    $cancel_name = $model->getName($cancel_uid);
                    $_SESSION['s_sysmsg'] .= "$cancel_name �� �� ���ä���λ���ޤ�����";
                }
                $list_view = $request->get('list_view');  // 'on' or NULL
                if( $list_view != "on" ) {
                    // ����ץ����
                    $menu->set_caption('�����������̾������塢[�ɹ���]�򥯥�å����Ʋ�������');
                } else {
                    // ����ץ����
                    $menu->set_caption('�������� �ޤ��ϡ��Ķȷ��������Ͽ���Ʋ�������');
                    // ɽ���ѥǡ�������
                    $rows = $model->getViewData($date, $bumon, $field, $res);
                    if( $rows <= 0 ) {
                        $rows = $model->GetNameList($bumon, $res);          // ��Ͽ���ʤ���С���������λ�̾���������
                        $rows = $model->NameListCheck($date, $res, $rows);  // ¾�������Ͽ�����ä�����Ƥ�
                        $view_data = false; // ɽ���ǡ������� NG.
                    } else {
                        $view_data = true;  // ɽ���ǡ������� OK.
                    }
                    $now_dt  = new DateTime();                      // ��������
//                    $now_dt  = new DateTime("20211008");            // TEST ��������
                    $time_limit = '17:15';                          // ��ò�ǽ����
//                    $time_limit = '12:15';                          // TEST ��ò�ǽ����
                    $work_dt = new DateTime("$date $time_limit");   // �������17:15
                    $limit_over = false;    // ����������ǽ
                    if( $now_dt > $work_dt ) {
                        $limit_over = true;
                    }
                }
                break;
            case 'Cancel':  // �����ʼ�á˲���
                $list_view   = $request->get('list_view');      // 'on' or NULL
                $cancel_uid  = $request->get('cancel_uid');     // ���ä��оݼ�UID
                $cancel_uno  = $request->get('cancel_uno');     // ���ä��оݼ��ֹ�
                $type        = $request->get('type');           // type = 'yo' or 'ji'
                $cancel_name = $model->getName($cancel_uid);    // ���ä��оݼ�̾
                // ����ץ����
                $menu->set_caption('���ä���ͳ�����Ϥ�[��� �¹�]�򥯥�å����Ʋ�������');
                break;
            case 'Judge':   // Ƚ��ʾ�ǧ�˲���
                if( $request->get('admit') ) {
                    $model->AdmitUp($request);  // ��ǧ����
                }
                // ����ץ����
                $menu->set_caption('����ֳ���ȿ���ꥹ��');
                // ��������(1) or ���������Ժ�̤��ǧ(2) or �Ķȷ�����(3)
                if( !($select = $request->get('select_radio')) ) $select = 1;   // ����� ��������(1)
                
                if( $select==1 || $select==2 ) {
                    $column = "yo_ad_"; // ��������
                } else {
                    $column = "ji_ad_"; // �Ķȷ�����
                }
                
                $rows   = 0;  // �����
                $pos_na = $model->getPostsName();   // 'ka' or 'bu' or 'ko'
                $pos_no = $model->getPostsNo();     // 1 or 2 or 3
                $where0 = $column . "rt!='-1'"; // xx_ad_xx!=-1
                $where  = $where0;
                
                // ��Ĺ����Ĺ���Ժ߼ԥ����å�
                $absence_ka = $absence_bu = false;  // �Ժ߼ԥե饰
                if( $pos_no > 1 ) { // ��Ĺ������Ĺ�ξ��
                    $deploy_rows = $model->getDeployAbsence($deploy_res, $absence_ka, $absence_bu);
                    if( $absence_bu || $absence_ka ) {  // ��Ĺ or ��Ĺ �Ժ߼Ԥ���
                        $where = "yo_ad_rt!='-1'";
                        // �Ժ�̤��ǧ �������ɽ������ǡ�������
                        $rows = $model->GetUnapproved($deploy_res, $deploy_rows, $where, $res);
                        if( $rows < 0 ) $absence_bu = $absence_ka = false;
                    }
                }
                if( $select==2 && !$absence_bu && !$absence_ka ) $select=1; // �Ժ�̤��ǧ ����Ǥ��Ժ߼Ԥʤ��ʤ飱���ѹ�
                
                if( $select==1 || $select==3 ) {    // ̤��ǧ �����
                    if( $pos_na ) { // ��ǧ�� 'ka' or 'bu' or 'ko'
                        $where  = $where0;                          // xx_ad_xx!=-1
                        $where1 = $column . $pos_na . "='m'";       // xx_ad_xx='m'
                        $where2 = $model->getWhereDeploy();         // (deploy='xxx' OR deploy='xxx')
                        $where3 = $column . "st=" . ($pos_no-1);    // xx_ad_st=(x-1)
                        $where .= " AND " . $where1 . " AND " . $where2 . " AND " . $where3;   // xx_ad_xx='m' AND (deploy='xxx��' OR deploy='xxx��') AND xx_ad_st=(x-1)
                        $rows = $model->GetDateDeploy($where, $res); // ̤��ǧ�Τ������դ���������
                    }
                }
                break;
            case 'Quiry':   // �Ȳ�ʸ����˲���
                // ����ץ����
                $menu->set_caption('�Ȳ�������򤷤ơ�[�¹�]�򥯥�å����Ʋ�������');
                // ñ��(1) or Ϣ��(3)
                if( !($d_radio = $request->get('days_radio')) ) $d_radio = 1; // ����� ñ��(1)
                // Ϣ����ǯ�����򥻥å�
                if( $request->get('ddlist_year2') == "" ) {
                    $def_y2 = date('Y'); $def_m2 = date('m'); $def_d2 = date('d');
                } else {
                    $def_y2 = $request->get('ddlist_year2');    // ���򤵤줿 ǯ
                    $def_m2 = $request->get('ddlist_month2');   // ���򤵤줿 ��
                    $def_d2 = $request->get('ddlist_day2');     // ���򤵤줿 ��
                }
                if( !($m_radio = $request->get('mode_radio')) ) $m_radio = 1; // ����� ����ʤ�(1)
                $e_check0 = $request->get('err_check0');
                $e_check1 = $request->get('err_check1');
                $e_check2 = $request->get('err_check2');
                $e_check3 = $request->get('err_check3');
                break;
            case 'Results':   // �Ȳ�ʷ�̡� ����
                $menu->set_RetUrl(PER_APPLI . "over_time_work_report/over_time_work_report_Main.php"); // �̾�ϻ��ꤹ��ɬ�פϤʤ�
                
                // [���]�ܥ���ǡ���ä����˥ǡ���������Ϥ���POST�ǡ������å�
                $menu->set_retPOST('login_uid', $request->get('login_uid'));    // TEST��
                $menu->set_retPOST('showMenu', 'Quiry');
                $menu->set_retPOST('days_radio', $request->get('days_radio'));
                $menu->set_retPOST('ddlist_year', $request->get('ddlist_year'));
                $menu->set_retPOST('ddlist_month', $request->get('ddlist_month'));
                $menu->set_retPOST('ddlist_day', $request->get('ddlist_day'));
                $menu->set_retPOST('ddlist_year2', $request->get('ddlist_year2'));
                $menu->set_retPOST('ddlist_month2', $request->get('ddlist_month2'));
                $menu->set_retPOST('ddlist_day2', $request->get('ddlist_day2'));
                $menu->set_retPOST('ddlist_bumon', $request->get('ddlist_bumon'));
                $menu->set_retPOST('s_no', $request->get('s_no'));
                $menu->set_retPOST('mode_radio', $request->get('mode_radio'));
                $menu->set_retPOST('err_check0', $request->get('err_check0'));
                $menu->set_retPOST('err_check1', $request->get('err_check1'));
                $menu->set_retPOST('err_check2', $request->get('err_check2'));
                $menu->set_retPOST('err_check3', $request->get('err_check3'));
                
                // ɽ���ǡ�������
                $rows = $model->getResultsView($request, $res);
                break;
            default:        // �ꥯ�����ȥǡ����˥��顼�ξ��Ͻ���ͤΰ�����ɽ��
                break;
        }
        
        ////////// HTML Header ����Ϥ��ƥ���å��������
        $menu->out_html_header();
        
        ////////// MVC �� View ���ν���
        switch ($showMenu) {
            case 'Appli':   // ���������ϡ˲���
                require_once ('over_time_work_report_ViewAppli.php');
                break;
            case 'Cancel':  // �����ʼ�á˲���
                require_once ('over_time_work_report_ViewCancel.php');
                break;
            case 'Judge':   // Ƚ��ʾ�ǧ�˲���
                require_once ('over_time_work_report_ViewJudge.php');
                break;
            case 'Quiry':   // �Ȳ�ʸ����˲���
                require_once ('over_time_work_report_ViewInquiry.php');
                break;
            case 'Results':   // �Ȳ�ʷ�̡� ����
                require_once ('over_time_work_report_ViewResults.php');
                break;
            default:        // �ꥯ�����ȥǡ����˥��顼�ξ��Ͻ���ͤΰ�����ɽ��
                require_once ('over_time_work_report_ViewAppli.php');
                break;
        }
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/

    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/

} // End off Class over_time_work_report_Controller

?>
