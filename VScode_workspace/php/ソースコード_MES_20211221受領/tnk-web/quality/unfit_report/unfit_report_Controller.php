<?php
//////////////////////////////////////////////////////////////////////////////
// ���Ҷ�ͭ ��Ŭ������ξȲ񡦥��ƥʥ�                                //
//                                                       MVC Controller ��  //
// Copyright (C) 2008 Norihisa.Ohya usoumu@nitto-kohki.co.jp                //
// Changed history                                                          //
// 2008/05/30 Created   unfit_report_Controller.php                         //
// 2008/08/29 masterst���ܲ�ư����                                          //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);       // E_STRICT=2048(php5) E_ALL=2047 debug ��

/****************************************************************************
*                       base class ���쥯�饹�����                         *
****************************************************************************/
////////////// namespace Controller {} �ϸ��߻��Ѥ��ʤ� �����㡧Controller::equipController �� $obj = new Controller::equipController;
////////////// Common::$sum_page, Common::set_page_rec() (̾������::��ƥ��)
class UnfitReport_Controller
{
    ////////// Private properties
    ////////// private $showMenu;                           // ��˥塼����
    ////////// private $listSpan;                           // ����ɽ�����δ���(1����,7����,14,28...)
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer ����� {php5 �ܹԤ� __construct() ���ѹ�} {�ǥ��ȥ饯��__destruct()}
    public function __construct($request, $model)
    {
        ////// POST Data �ν����������
        ////// ��˥塼������ �ꥯ������ �ǡ�������
        if ($request->get('showMenu') == '') {
            $request->add('showMenu', 'IncompleteList');    // ���꤬�ʤ����ϰ���ɽ��ɽ��(�ä˽��)
        }
        
        ////// ��Ͽ������������� �¹Իؼ��ꥯ������
        $apend      = $request->get('Apend');               // ��Ŭ���������Ͽ
        $partsflg   = $request->get('partsflg');            // ����̾ɽ���ΰ٤Υե饰
        $assyflg    = $request->get('assyflg');             // ����̾ɽ���ΰ٤Υե饰
        $delete     = $request->get('Delete');              // ��Ŭ������μ��(���)
        $edit       = $request->get('Edit');                // ��Ŭ��������ѹ�
        $follow     = $request->get('Follow');              // �ե������åפ�����
        ////// ���롼����
        $groupEdit  = $request->get('groupEdit');           // ���롼�פ���Ͽ���ѹ�
        $groupOmit  = $request->get('groupOmit');           // ���롼�פκ��
        $groupActive= $request->get('groupActive');         // ���롼�פ�ͭ����̵��(�ȥ���)
        
        ////// MVC �� Model ���� �¹������å�����
        ////// ��Ŭ��������Խ�
        if ($apend != '') {                                 // ��Ŭ���������Ͽ (�ɲ�)
            if ($partsflg == '' && $assyflg == '') {        // ���ʡ�����̾ɽ���ե饰��ON�Ǥʤ������ɲ�
                $this->apend($request, $model);
            }
        } elseif ($delete != '') {                          // ��Ŭ������μ�� (�������)
            $this->delete($request, $model);
        } elseif ($edit != '') {                            // ��Ŭ��������ѹ�
            if ($partsflg == '' && $assyflg == '') {        // ���ʡ�����̾ɽ���ե饰��ON�Ǥʤ������ɲ�
                $this->edit($request, $model);
            }
        } elseif ($follow != '') {                          // �ե������åפ�����
            $this->follow($request, $model);
            $request->add('showMenu', 'CompleteList');
        ////// �����Υ��롼���Խ�
        } elseif ($groupEdit != '') {                       // ���롼�פ���Ͽ���ѹ�
            $this->groupEdit($request, $model);
        } elseif ($groupOmit != '') {                       // ���롼�פκ��
            $this->groupOmit($request, $model);
        } elseif ($groupActive != '') {                     // ���롼�פ�ͭ����̵��(�ȥ���)
            $this->groupActive($request, $model);
        }
    }
    
    ////////// MVC View���ν���
    public function display($menu, $request, $result, $model)
    {
        ////// �����������饹��include
        require_once ('../../CalendarClass.php');              // �����������饹
        
        ////// ���饹�Υ��󥹥��󥹺���
        ////// calendar(��������, ����ʳ������դ�ɽ�����뤫�ɤ���) �η��ǻ��ꤷ�ޤ���
        ////// ������������0-���� ���� 6-���ˡˡ�����ʳ������դ�ɽ����0-No, 1-Yes��
        $calendar_now  = new Calendar(0, 0);
        $calendar_nex1 = new Calendar(0, 0);
        $calendar_nex2 = new Calendar(0, 0);
        $calendar_pre  = new Calendar(0, 0);

        ////// �֥饦�����Υ���å����к���
        $uniq = $menu->set_useNotCache('unfit');
        
        ////// ��˥塼���� �ꥯ������ �ǡ�������
        $showMenu   = $request->get('showMenu');            // �������åȥ�˥塼�����
        $listSpan   = $request->get('listSpan');            // ����ɽ�����δ���(1����,7����,14,28...)
        
        ////// �����ե������ �ꥯ������ �ǡ�������
        $year       = $request->get('year');                // ȯ��ǯ������ǯ����
        $month      = $request->get('month');               // ȯ��ǯ�����η��
        $day        = $request->get('day');                 // ȯ��ǯ������������
        $serial_no  = $request->get('serial_no');           // tableϢ�� �����ե������
        $atten_flg  = $request->get('atten_flg');           // �����Ÿ���ե饰
        
        ////// ��ǧ�ե�����Ǽ�ä������줿���Υꥯ�����ȼ���
        $cancel_edit   = $request->get('cancel_edit');
        
        ////// ��Ͽ���Խ� �ǡ����Υꥯ�����ȼ���
        $subject       = $request->get('subject');          // ��Ŭ������
        $occur_time    = $request->get('occur_time');       // ȯ��ǯ����
        $sponsor       = $request->get('sponsor');          // ������
        $receipt_no    = $request->get('receipt_no');       // ����No.
        $atten         = $request->get('atten');            // �����(attendance) (����) ���롼�פǤ����
        $mail          = $request->get('mail');             // �᡼������� Y/N
        $suihei        = $request->get('suihei');           // ��ʿŸ�� Y/N
        $kanai         = $request->get('kanai');            // ����Ÿ�� Y/N
        $kagai         = $request->get('kagai');            // �ݳ�Ÿ�� Y/N
        $hyoujyun      = $request->get('hyoujyun');         // ɸ���Ÿ�� Y/N
        $kyouiku       = $request->get('kyouiku');          // ����»� Y/N
        $system        = $request->get('system');           // �����ƥ� Y/N
        $measure       = $request->get('measure');          // �к��»� Y/N
        $occuryear     = $request->get('occuryear');        // ȯ�����к��»�ͽ��ǯ
        $occurmonth    = $request->get('occurmonth');       // ȯ�����к��»�ͽ���
        $occurday      = $request->get('occurday');         // ȯ�����к��»�ͽ����
        $issueyear     = $request->get('issueyear');        // ή�и��к��»�ͽ��ǯ
        $issuemonth    = $request->get('issuemonth');       // ή�и��к��»�ͽ���
        $issueday      = $request->get('issueday');         // ή�и��к��»�ͽ����
        $place         = $request->get('place');            // ȯ�����
        $section       = $request->get('section');          // ��Ǥ����
        $assy_no       = $request->get('assy_no');          // �����ֹ�
        $parts_no      = $request->get('parts_no');         // �����ֹ�
        $occur_cause   = $request->get('occur_cause');      // ȯ������
        $unfit_num     = $request->get('unfit_num');        // ��Ŭ�����
        $issue_cause   = $request->get('issue_cause');      // ή�и���
        $issue_num     = $request->get('issue_num');        // ή�п���
        $unfit_dispose = $request->get('unfit_dispose');    // ��Ŭ���ʤν���
        $occur_measure = $request->get('occur_measure');    // ȯ�����к�
        $issue_measure = $request->get('issue_measure');    // ή���к�
        $follow_who    = $request->get('follow_who');       // �ե������å� ï��
        $follow_when   = $request->get('follow_when');      // �ե������å� ����
        $follow_how    = $request->get('follow_how');       // �ե������å� �ɤΤ褦��
        ////// ���롼���Խ���
        $group_no2     = $request->get('group_no2');        // ���롼���ֹ�
        $group_no      = $group_no2;         // TEST
        $group_name    = $request->get('group_name');       // ���롼��̾
        $owner         = $request->get('owner');            // ���롼�פ�Ŀ͡���ͭ
        $groupCopy     = $request->get('groupCopy');        // ���롼�פ��Խ��ǡ������ԡ�
        
        ////// ���������ꥯ�����ȤΥ����å��ڤӼ���
        if ($year != '' && $month != '' && $day != '') {
            $day_now  = getdate(mktime(0, 0, 0, $month, $day, $year));
            $day_nex1 = getdate(mktime(0, 0, 0, $month+1, 1, $year));
            $day_nex2 = getdate(mktime(0, 0, 0, $month+2, 1, $year));
            $day_pre  = getdate(mktime(0, 0, 0, $month-1, 1, $year));
        } else {
        ////// ���������
            $day_now  = getdate();
            $day_nex1 = getdate(mktime(0, 0, 0, $day_now['mon']+1, 1, $day_now['year']));
            $day_nex2 = getdate(mktime(0, 0, 0, $day_now['mon']+2, 1, $day_now['year']));
            $day_pre  = getdate(mktime(0, 0, 0, $day_now['mon']-1, 1, $day_now['year']));
        }
        
        ////// ���������������դ��󥯤ˤ���
        if ($showMenu != 'Edit') {
            $url = $menu->out_self() . "?showMenu={$showMenu}&" . $model->get_htmlGETparm() . "&id={$uniq}";
        } else {
            $url = $menu->out_self() . "?showMenu=IncompleteList&" . $model->get_htmlGETparm() . "&id={$uniq}";
        }
        $calendar_now-> setAllLinkYMD($day_now['year'], $day_now['mon'], $url);
        $calendar_pre-> setAllLinkYMD($day_pre['year'], $day_pre['mon'], $url);
        $calendar_nex1->setAllLinkYMD($day_nex1['year'], $day_nex1['mon'], $url);
        $calendar_nex2->setAllLinkYMD($day_nex2['year'], $day_nex2['mon'], $url);
        
        ////// MVC �� Model���� View�����Ϥ��ǡ�������
        switch ($showMenu) {
        case 'FollowList':                                  // ��Ŭ������ �ե������å״�λ�ꥹ��
            $rows = $model->getViewFollowList($result);
            $res  = $result->get_array();
            // $pageControl = $model->out_pageControll_HTML($menu->out_self()."?id={$uniq}");
            $pageControl = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            // ������ʣ���ǡ��������
            $rowsAtten = array(); $resAtten = array();      // �����
            for ($i=0; $i<$rows; $i++) {
                $rowsAtten[$i] = $model->getViewAttenList($result, $res[$i][0]);
                $resAtten[$i]  = $result->get_array();
            }
            // ɽ��(����ץ����)������
            $menu->set_caption($model->get_caption('FollowList', $year, $month, $day));
            // �ǡ����ʤ��λ��Υ�å���������
            $noDataMessage = $model->get_noDataMessage($year, $month, $day);
            break;
        case 'CompleteList':                                // ��Ŭ������ �к���λ�ꥹ��
            $rows = $model->getViewCompleteList($result);
            $res  = $result->get_array();
            // $pageControl = $model->out_pageControll_HTML($menu->out_self()."?id={$uniq}");
            $pageControl = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            // ������ʣ���ǡ��������
            $rowsAtten = array(); $resAtten = array();      // �����
            for ($i=0; $i<$rows; $i++) {
                $rowsAtten[$i] = $model->getViewAttenList($result, $res[$i][0]);
                $resAtten[$i]  = $result->get_array();
            }
            // ɽ��(����ץ����)������
            $menu->set_caption($model->get_caption('CompleteList', $year, $month, $day));
            // �ǡ����ʤ��λ��Υ�å���������
            $noDataMessage = $model->get_noDataMessage($year, $month, $day);
            break;
        case 'IncompleteList':                              // ��Ŭ������ �к���λ�ꥹ��
            $rows = $model->getViewIncompleteList($result);
            $res  = $result->get_array();
            // $pageControl = $model->out_pageControll_HTML($menu->out_self()."?id={$uniq}");
            $pageControl = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            // ������ʣ���ǡ��������
            $rowsAtten = array(); $resAtten = array();      // �����
            for ($i=0; $i<$rows; $i++) {
                $rowsAtten[$i] = $model->getViewAttenList($result, $res[$i][0]);
                $resAtten[$i]  = $result->get_array();
            }
            // ɽ��(����ץ����)������
            $menu->set_caption($model->get_caption('IncompleteList', $year, $month, $day));
            // �ǡ����ʤ��λ��Υ�å���������
            $noDataMessage = $model->get_noDataMessage($year, $month, $day);
            break;
        case 'Apend':                                       // ��Ŭ����������Ϥ�ɬ�פ�User�ǡ���
            // ��Ŭ�������ɲû��κ����Ԥν�������� (�ܿͤμҰ��ֹ�)
            if ($sponsor == '') if ($_SESSION['User_ID'] != '000000') $sponsor = $_SESSION['User_ID'];
            // ������μҰ��ֹ�Ȼ�̾��������� selected ������ޤǹԤ�
            $user_cnt = $model->getViewUserName($userID_name, $atten);
            // ���롼�ץޥ�������ͭ���ʥꥹ�Ȥ���� (JavaScript���Ϥ�)
            $JSgroup = $model->getActiveGroupList($JSgroup_name, $JSgroup_member, $_SESSION['User_ID']);
            // ɽ��(����ץ����)������
            $menu->set_caption($model->get_caption('Apend', $year, $month, $day));
            break;
        case 'Edit':                                        // ��Ŭ��������ѹ� ���쥳���ɤΥǡ���
            $rows       = $model->getViewEdit($serial_no, $result);
            $subject    = $result->get_once('subject');
            $place      = $result->get_once('place');
            $section    = $result->get_once('section');
            $occur_time = $result->get_once('occur_time');
            $sponsor    = $result->get_once('sponsor');
            $receipt_no = $result->get_once('receipt_no');
            $atten_num  = $result->get_once('atten_num');
            $mail       = $result->get_once('mail');
            // ������ʣ���ǡ��������
            $rowsAtten = $model->getViewAttenList($result, $serial_no);
            $resAtten  = $result->get_array();
            // �����μҰ��ֹ�Τ����
            $atten = array();   // �����
            for ($i=0; $i<$rowsAtten; $i++) {
                $atten[$i] = $resAtten[$i][1];
            }
            // ������μҰ��ֹ�Ȼ�̾��������� selected ������ޤǹԤ�
            $user_cnt = $model->getViewUserName($userID_name, $atten);
            // ȯ�������Υǡ��������
            $rowsCause   = $model->getViewCauseList($result, $serial_no);
            $partsflg   = $request->get('partsflg');        // ����̾ɽ���ΰ٤Υե饰
            $assyflg    = $request->get('assyflg');         // ����̾ɽ���ΰ٤Υե饰
            if ($partsflg == '' && $assyflg == '') {        // ���ʡ�����̾ɽ���ե饰��ON�Ǥʤ������ɲ�
                $assy_no     = $result->get_once('assy_no');
                $parts_no    = $result->get_once('parts_no');
            } else {
                $assy_no     = $request->get('assy_no');
                $parts_no    = $request->get('parts_no');    
            }
            $occur_cause = $result->get_once('occur_cause');
            $unfit_num   = $result->get_once('unfit_num');
            $issue_cause = $result->get_once('issue_cause');
            $issue_num   = $result->get_once('issue_num');
            // �к��Υǡ��������
            $rowsMeasure   = $model->getViewMeasureList($result, $serial_no);
            $unfit_dispose = $result->get_once('unfit_dispose');
            $occur_measure = $result->get_once('occur_measure');
            $issue_measure = $result->get_once('issue_measure');
            $follow_who    = $result->get_once('follow_who');
            $follow_when   = $result->get_once('follow_when');
            $follow_how    = $result->get_once('follow_how');
            $measure       = $result->get_once('measure');
            // Ÿ���Υǡ��������
            $rowsDevelop = $model->getViewDevelopList($result, $serial_no);
            $suihei      = $result->get_once('suihei');
            $kanai       = $result->get_once('kanai');
            $kagai       = $result->get_once('kagai');
            $hyoujyun    = $result->get_once('hyoujyun');
            $kyouiku     = $result->get_once('kyouiku');
            $system      = $result->get_once('system');
            // ���롼�ץޥ�������ͭ���ʥꥹ�Ȥ���� (JavaScript���Ϥ�)
            $JSgroup = $model->getActiveGroupList($JSgroup_name, $JSgroup_member, $_SESSION['User_ID']);
            // ɽ��(����ץ����)������
            $menu->set_caption($model->get_caption('Edit', $year, $month, $day));
            break;
        case 'Follow':                                      // �ե������åפ����ϡ��ѹ�
            $rows       = $model->getViewEdit($serial_no, $result);
            $subject    = $result->get_once('subject');
            $place      = $result->get_once('place');
            $section    = $result->get_once('section');
            $occur_time = $result->get_once('occur_time');
            $receipt_no = $result->get_once('receipt_no');
            $atten_num  = $result->get_once('atten_num');
            $mail       = $result->get_once('mail');
            // ������ʣ���ǡ��������
            $rowsAtten = $model->getViewAttenList($result, $serial_no);
            $resAtten  = $result->get_array();
            // �����μҰ��ֹ�Τ����
            $atten = array();   // �����
            for ($i=0; $i<$rowsAtten; $i++) {
                $atten[$i] = $resAtten[$i][1];
            }
            // ������μҰ��ֹ�Ȼ�̾��������� selected ������ޤǹԤ�
            $user_cnt = $model->getViewUserName($userID_name, $atten);
            // ȯ�������Υǡ��������
            $rowsCause   = $model->getViewCauseList($result, $serial_no);
            $partsflg   = $request->get('partsflg');        // ����̾ɽ���ΰ٤Υե饰
            $assyflg    = $request->get('assyflg');         // ����̾ɽ���ΰ٤Υե饰
            if ($partsflg == '' && $assyflg == '') {        // ���ʡ�����̾ɽ���ե饰��ON�Ǥʤ������ɲ�
                $assy_no     = $result->get_once('assy_no');
                $parts_no    = $result->get_once('parts_no');
            } else {
                $assy_no     = $request->get('assy_no');
                $parts_no    = $request->get('parts_no');    
            }
            $occur_cause = $result->get_once('occur_cause');
            $unfit_num   = $result->get_once('unfit_num');
            $issue_cause = $result->get_once('issue_cause');
            $issue_num   = $result->get_once('issue_num');
            // �к��Υǡ��������
            $rowsMeasure   = $model->getViewMeasureList($result, $serial_no);
            $unfit_dispose = $result->get_once('unfit_dispose');
            $occur_measure = $result->get_once('occur_measure');
            $issue_measure = $result->get_once('issue_measure');
            $follow_who    = $result->get_once('follow_who');
            $follow_when   = $result->get_once('follow_when');
            $follow_how    = $result->get_once('follow_how');
            $measure       = $result->get_once('measure');
            // Ÿ���Υǡ��������
            $rowsDevelop = $model->getViewDevelopList($result, $serial_no);
            $suihei      = $result->get_once('suihei');
            $kanai       = $result->get_once('kanai');
            $kagai       = $result->get_once('kagai');
            $hyoujyun    = $result->get_once('hyoujyun');
            $kyouiku     = $result->get_once('kyouiku');
            $system      = $result->get_once('system');
            // �ե������åפΥǡ��������
            $rowsfollow     = $model->getViewFollow($result, $serial_no);
            $follow_section = $result->get_once('follow_section');
            $follow_quality = $result->get_once('follow_quality');
            $follow_opinion = $result->get_once('follow_opinion');
            $sponsor        = $result->get_once('follow_sponsor');
            $follow         = $result->get_once('follow');
            $follow_flg     = $result->get_once('follow_flg');
            // ��Ŭ�������ɲû��κ����Ԥν�������� (�ܿͤμҰ��ֹ�)
            if ($sponsor == '') if ($_SESSION['User_ID'] != '000000') $sponsor = $_SESSION['User_ID'];
            // ���롼�ץޥ�������ͭ���ʥꥹ�Ȥ���� (JavaScript���Ϥ�)
            $JSgroup = $model->getActiveGroupList($JSgroup_name, $JSgroup_member, $_SESSION['User_ID']);
            // ɽ��(����ץ����)������
            $menu->set_caption($model->get_caption('Follow', $year, $month, $day));
            break;
        case 'Group':                                       // ���롼�פ� ��Ͽ ����ɽ ɽ��
            if ($group_no != '') {                          // �Խ�������å������group_no�����åȤ���Ƥ����
                // ���롼��������ʣ���ǡ��������
                $rowsAtten = $model->getGroupAttenList($result, $group_no);
                $resAtten  = $result->get_array();
                // ���롼�������μҰ��ֹ�Τ����
                $atten = array();                           // �����
                for ($i=0; $i<$rowsAtten; $i++) {
                    $atten[$i] = $resAtten[$i][1];
                }
            }
            // ������μҰ��ֹ�Ȼ�̾��������� selected ������ޤǹԤ�
            $user_cnt = $model->getViewUserName($userID_name, $atten);
            // ���롼�ץꥹ�Ȥμ���
            $rows = $model->getViewGroupList($result);
            $res  = $result->get_array();
            $pageControl = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            // ������ʣ���ǡ��������
            $rowsAtten = array(); $resAtten = array();      // �����
            for ($i=0; $i<$rows; $i++) {
                $rowsAtten[$i] = $model->getGroupAttenList($result, $res[$i][0]);
                $resAtten[$i]  = $result->get_array();
            }
            break;
        }
        
        ////// HTML Header ����Ϥ��ƥ���å��������
        $menu->out_html_header();
        
        ////// MVC �� View ���ν���
        switch ($showMenu) {
        case 'Edit':                                        // ��Ŭ��������ѹ� ����
            require_once ('unfit_report_ViewApend.php');    // ����
            break;
        case 'Apend':                                       // ��Ŭ����������� ����
            require_once ('unfit_report_ViewApend.php');
            break;
        case 'Follow':                                      // �ե������åפ����ϡ��ѹ� ����
            require_once ('unfit_report_ViewFollow.php');
            break;
        case 'Group':                                       // ���롼�פ� ����ɽ ɽ��
            if ($groupCopy == 'go') {
                $focus    = 'group_name';
                $readonly = "readonly style='background-color:#e6e6e6;'";
            } else {
                $focus    = 'group_no';
                $readonly = '';
            }
            require_once ('unfit_report_ViewGroup.php');
            break;
        default:                                            // �ꥯ�����ȥǡ����˥��顼�ξ��Ͻ���ͤΰ�����ɽ��
            require_once ('unfit_report_ViewList.php');
        }
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// ��Ͽ(�ɲ�) ����
    protected function apend($request, $model)
    {
        $response = $model->add($request);
        if ($response) {
            $request->add('subject', '');                   // ��Ͽ�Ǥ����Τ����ϥե�����ɤ�ä�
            $request->add('occur_time', '');
            $request->add('place', '');
            $request->add('section', '');
            $request->add('assy_no', '');
            $request->add('parts_no', '');
            $request->add('occur_cause', '');
            $request->add('unfit_num', '');
            $request->add('issue_cause', '');
            $request->add('issue_num', '');
            $request->add('unfit_dispose', '');
            $request->add('occur_measure', '');
            $request->add('issue_measure', '');
            $request->add('follow_who', '');
            $request->add('follow_when', '');
            $request->add('follow_how', '');
            $request->add('measure', '');
            $request->add('sponsor', '');
            $request->add('mail', '');
            $request->add_array('atten', '1', '');
            $request->add('showMenu', 'IncompleteList');    // ��Ͽ�Ǥ����Τǰ������̤ˤ��롣
        }
    }
    protected function follow($request, $model)
    {
        $response = $model->follow($request);
        if ($response) {
            $request->add('follow_section', '');            // ��Ͽ�Ǥ����Τ����ϥե�����ɤ�ä�
            $request->add('follow_quality', '');
            $request->add('follow_opinion', '');
            $request->add('follow', '');
            $request->add('sponsor', '');
            $request->add('mail', '');
            $request->add_array('atten', '1', '');
            $request->add('showMenu', 'completeList');      // ��Ͽ�Ǥ����Τǰ������̤ˤ��롣
        }
    }
    ////////// ���(�������) ����
    protected function delete($request, $model)
    {
        ////// $serial_no  = $request->get('serial_no');        // ���ꥢ���ֹ�
        ////// $subject    = $request->get('subject');          // ��Ŭ������
        ////// $response = $model->delete($serial_no, $subject);
        $response = $model->delete($request);               // ����󥻥�Υ᡼���б���
        if ($response) {
            $request->add('showMenu', 'IncompleteList');    // ������褿�Τǰ������̤ˤ��롣
        } else {
            $request->add('showMenu', 'Edit');              // �������ʤ��ä��Τ��Խ����̤�����
        }
    }
    
    ////////// �Խ�����  ����
    protected function edit($request, $model)
    {
        if ($model->edit($request)) {
            $request->add('subject', '');                   // �ѹ��Ǥ����Τ����ϥե�����ɤ�ä�
            $request->add('occur_time', '');
            $request->add('sponsor', '');
            $request->add('mail', '');
            $request->add_array('atten', '1', '');
            $request->add('showMenu', 'IncompleteList');    // ��Ͽ�Ǥ����Τǰ������̤ˤ��롣
        } else {
            $request->add('showMenu', 'Edit');              // ��Ͽ����ʤ��ä��Τ��Խ����̤�����
        }
    }
    
    ////////// ����襰�롼�פ��Խ� ����
    protected function groupEdit($request, $model)
    {
        $group_no2  = $request->get('group_no2');           // ���롼���ֹ�
        $group_no   = $group_no2;                           // TEST
        $group_name = $request->get('group_name');          // ���롼��̾
        $atten      = $request->get('atten');               // �����(attendance) (����) ���롼�פǤ����
        $owner      = $request->get('owner');               // ���롼�פ�Ŀ͡���ͭ
        if ($model->group_edit($group_no, $group_name, $atten, $owner)) {
            // ��Ͽ�Ǥ����Τ�group_no, group_name��<input>�ǡ�����ä�
            $request->add('group_no', '');
            $request->add('group_no2', '');
            $request->add('group_name', '');
            $request->del('atten');
        }
    }
    
    ////////// ����襰�롼�פκ�� ����
    protected function groupOmit($request, $model)
    {
        $group_no2  = $request->get('group_no2');           // ���롼���ֹ�
        $group_no   = $group_no2;                           // TEST
        $group_name = $request->get('group_name');          // ���롼��̾
        $response = $model->group_omit($group_no, $group_name);
        $request->add('group_no', '');                      // ������ϥ��ԡ���ɬ�פʤ�
        $request->add('group_no2', '');
        $request->add('group_name', '');
        $request->del('atten');
    }
    
    ////////// ����襰�롼�פ�ͭ����̵�� ����
    protected function groupActive($request, $model)
    {
        $group_no2  = $request->get('group_no2');           // ���롼���ֹ�
        $group_no   = $group_no2;                           // TEST
        $group_name = $request->get('group_name');          // ���롼��̾
        if ($model->group_activeSwitch($group_no, $group_name)) {
            $request->add('group_no', '');
            $request->add('group_no2', '');
            $request->add('group_name', '');
            $request->del('atten');
        }
    }
    
} //////////// End off Class UnfitReport_Controller

?>
