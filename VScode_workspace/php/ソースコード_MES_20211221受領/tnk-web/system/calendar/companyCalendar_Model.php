<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ҥδ��ܥ������� ���ƥʥ�                         MVC Model ��   //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/06/21 Created   companyCalendar_Model.php                           //
// 2006/06/26 �����������饹��Ver1.10����Ѥ����Ƥ������оݤ˥����Ȥ�   //
//            �Ķ��������꤬�����褦���ѹ� (���Ǥ�OLD-version����¸)      //
//            str_replace("\r\n", CRLF���������ɲ�                        //
//            showCalendar()�᥽�åɤ� echo ���Ϥ��� return ���Ϥ��ѹ�����  //
//            Ajax�б��Τ��� UTF-8 �Ѵ��ɲ�                                 //
// 2006/07/01 CompanyCalendar.win_open()�ˤ�window̾��ɬ�����ꤹ��(�����ѹ�)//
//            ���ꤷ�ʤ�����application(window)̾������븽�ݤˤʤ�       //
// 2006/07/07 ľ��Υǡ������ԡ��ܥ�����ɲäˤ�� setTimeCopyExecute()�ɲ� //
// 2006/07/11 Controller��Execute()�᥽�åɤ��ɲä�Action��showMenu�����β� //
// 2006/07/12 getAuthority($id, $division)�᥽�åɤ��ɲä��Խ����¥����å�  //
// 2006/10/04 �Խ����� ���֤�$this->getCheckAuthority()����Ѥ���褦���ѹ� //
// 2006/10/05 getCheckAuthority($id,$division)��getCheckAuthority($division)//
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����

require_once ('../../daoInterfaceClass.php');   // TNK ������ DAO���󥿡��ե��������饹


/*****************************************************************************************
*       MVC��Model�� ���饹��� daoInterfaceClass(base class) ���쥯�饹���ĥ           *
*****************************************************************************************/
class CompanyCalendar_Model extends daoInterfaceClass
{
    ///// Private properties
    private $calendarStatus;                    // ���������Υ������������
    private $calendarMsg;                       // ���������Υ��åץإ�׵ڤӥ��ơ������С��Υ�å�����
    private $calendarUrl;                       // ������������URL���ɥ쥹
    
    private $sumBusinessHours = 0;              // ��ױĶȻ���(������)
    private $sumAbsentTime = 0;                 // ��׵ٷƻ���(������)
    private $netBusinessHours = 0;              // �¶�̳���ַ�(������)
    
    private $authDiv = 1;                       // ���Υӥ��ͥ����å��θ��¶�ʬ
    
    ///// Public properties
    // public  $graph;                             // GanttChart�Υ��󥹥���
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer ����� (php5�ذܹԻ��� __construct() ���ѹ�ͽ��) (�ǥ��ȥ饯��__destruct())
    public function __construct($request, $menu)
    {
        ///// ���������Υ������������
        switch ($request->get('targetCalendar')) {
        case 'BDSwitch':
            $this->calendarStatus = 'BDSwitch';
            $this->calendarMsg = '����å�������˵����ȱĶ��������ؤ��ޤ���';
            $this->calendarUrl = "window.parent.CompanyCalendar.AjaxLoadUrl(\"{$menu->out_self()}?Action=Change&showMenu=Calendar%s\")";
            break;
        case 'Comment':
            $this->calendarStatus = 'Comment';
            $this->calendarMsg = '����å�����ȵ����ޤ��ϱĶ����Υ������Խ�������ޤ���';
                                                    // �쥿����(location.replace)�� CommentEdit �� Comment ��ƽФ�
            $this->calendarUrl = "CompanyCalendar.win_open(\"{$menu->out_self()}?Action=Comment&showMenu=EditComment%s\", 400, 200, \"CommentWin\")";
            break;
        case 'SetTime':
            $this->calendarStatus = 'SetTime';
            $this->calendarMsg = '����å�����ȱĶȻ��֤���ӵٷƻ��֤��Խ�������ޤ���';
            $this->calendarUrl = "window.parent.CompanyCalendar.actionNameSwitch(); window.parent.CompanyCalendar.AjaxLoadUrl(\"{$menu->out_self()}?Action=TimeList&showMenu=List%s\")";
            break;
        default:
            $this->calendarStatus = '';
            $this->calendarMsg = '';
            $this->calendarUrl = '';
        }
    }
    
    ///// �о�ǯ���HTML <select> option �ν���
    public function getTargetDateYvalues($request)
    {
        // �����
        $option = "\n";
        $year = date('Y');
        $year++;
        for ($i=$year; $i>=2000; $i--) {
            $ki = $i - 2000 + 1;
            $ki = sprintf('%02d', $ki);
            $ki = mb_convert_kana($ki, 'N');
            if ($request->get('targetDateY') == $i) {
                $option .= "<option value='{$i}' selected>��{$ki}��</option>\n";
            } else {
                $option .= "<option value='{$i}'>��{$ki}��</option>\n";
            }
        }
        // $option .= "<option value='2006'>�裰����</option>\n";
        return $option;
    }
    
    ///// �оݴ��Υ��������ν���
    public function showCalendar($request, $calendar, $menu, $uniq)
    {
        // ����������ǯ�����
        $strYear  = substr($request->get('targetDateStr'), 0, 4);
        $strMonth = substr($request->get('targetDateStr'), 4, 2);
        // �����
        $table_list = "\n";     // �����
        $table_list .= "<table border='0' align='center'>\n";
        $colCount = 0;
        for ($i=0; $i<12; $i++) {
            // ���������������դ˥�󥯤����ꤹ��
            $calendar[$i]->setAllLinkYMD($strYear, $strMonth, $this->calendarUrl, $this->calendarMsg);
            if ($colCount == 0) {
                $table_list .= "    <tr>\n";
            }
            $table_list .= "    <td valign='top'>\n";
            // �������Υ����å�
            if ($request->get('targetCalendar') != 'BDSwitch' && $request->get('year') == $strYear && $request->get('month') == $strMonth) {
                $table_list .= "        {$calendar[$i]->show_calendar($strYear, $strMonth, $request->get('day'))}\n";
            } else {
                $table_list .= "        {$calendar[$i]->show_calendar($strYear, $strMonth)}\n";
            }
            $table_list .= "    </td>\n";
            if ($colCount == 3) {
                $table_list .= "    </tr>\n";
            }
            $colCount++;
            if ($colCount == 4) $colCount = 0;
            $strMonth++;
            if ($strMonth == 13) {
                $strMonth = 1;
                $strYear++;
            }
            $strYM = sprintf('%04d%02d', $strYear, $strMonth);
            if ($strYM > $request->get('targetDateEnd')) break;
        }
        $table_list .= "</table>\n";
        // return mb_convert_encoding($table_list, 'UTF-8');
        // return $table_list;
        
        // �����HTML�إå��������
        $listHTML  = $this->getViewCalendarHTMLconst('header', $uniq);
        
        // ���������ơ��֥���ղ�
        $listHTML .= $table_list;
        
        // �����HTML�եå��������
        $listHTML .= $this->getViewCalendarHTMLconst('footer', $uniq);
        
        // HTML�ե��������
        $file_name = "list/companyCalendar_ViewCalendar-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
    }
    
    ///// ��Ҥε������Ķ����ȥ�������
    public function changeHoliday($request, $result, $menu)
    {
        if (!$this->getAuthority($_SESSION['User_ID'], $this->authDiv)) {
            return false;
        }
        $query = "
            SELECT tdate FROM company_calendar WHERE
            tdate = DATE '{$request->get('year')}-{$request->get('month')}-{$request->get('day')}'
            AND bd_flg
        ";
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        if ($this->getUniResult($query, $check) < 1) {
            $sql = "
                UPDATE company_calendar SET bd_flg = TRUE, last_date = '{$last_date}', last_user = '{$last_user}'
                WHERE tdate = DATE '{$request->get('year')}-{$request->get('month')}-{$request->get('day')}'
            ";
        } else {
            $sql = "
                UPDATE company_calendar SET bd_flg = FALSE, last_date = '{$last_date}', last_user = '{$last_user}'
                WHERE tdate = DATE '{$request->get('year')}-{$request->get('month')}-{$request->get('day')}'
            ";
        }
        if ($this->query_affected($sql) <= 0) {
            $_SESSION['s_sysmsg'] = '�������Ķ��������ؤ�����ޤ���Ǥ���������ô���Ԥ�Ϣ���Ʋ�������';
        } else {
            if ($request->get('combinedEdit') == 'yes') {   // �Ķ������������ؤȥ����Ȥ�Ʊ���Խ��ξ��
                $script = "CompanyCalendar.win_open('{$menu->out_self()}?Action=Comment&showMenu=EditComment&year={$request->get('year')}&month={$request->get('month')}&day={$request->get('day')}', 400, 200);\n";
                $result->add('autoLoadScript', $script);
            }
        }
    }
    
    ///// ���������λ������դΥ����Ȥ��Խ�
    public function commentEdit($request, $result, $menu)
    {
        $script = "CompanyCalendar.win_open('{$menu->out_self()}?Action=Comment&showMenu=EditComment&year={$request->get('year')}&month={$request->get('month')}&day={$request->get('day')}', 400, 200);\n";
        $result->add('autoLoadScript', $script);
    }
    
    ///// �������Ķ����Υ����Ȥ���¸
    public function commentSave($request)
    {
        if (!$this->getAuthority($_SESSION['User_ID'], $this->authDiv)) {
            return false;
        }
        // �����ȤΥѥ�᡼���������å�(���Ƥϥ����å��Ѥ�)
        // if ($request->get('note') == '') return;  // �����Ԥ��Ⱥ���Ǥ��ʤ�
        if ($request->get('year') == '')  return '';
        if ($request->get('month') == '') return '';
        if ($request->get('day') == '')   return '';
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        $note = str_replace("\r\n", '', $request->get('note')); // CRLF������
        // �ǡ�����¸�ߥ����å�
        $query = "
            SELECT note FROM company_calendar WHERE tdate = DATE '{$request->get('year')}-{$request->get('month')}-{$request->get('day')}'
        ";
        if ($this->getUniResult($query, $old_note) <= 0) {
            return;     // �ǡ�����¾�οͤ��ѹ����줿���ģ¥��顼
        }
        if ($old_note == $note) return; // �ǡ������ѹ�����Ƥ��ʤ��Τǹ������ʤ�
        $sql = "
            UPDATE company_calendar SET note = '{$note}',
            last_date='{$last_date}', last_user='{$last_user}'
            WHERE tdate = DATE '{$request->get('year')}-{$request->get('month')}-{$request->get('day')}'
        ";
        if ($this->query_affected($sql) <= 0) {
            $_SESSION['s_sysmsg'] = '�����Ȥ���¸������ޤ���Ǥ�����������ô���Ԥ�Ϣ���Ʋ�������';
        } else {
            $_SESSION['s_sysmsg'] = '��Ͽ���ޤ�����';
        }
        return ;
    }
    
    ///// �������Ķ����Υ����Ȥ����
    public function getComment($request, $result)
    {
        // �����ȤΥѥ�᡼���������å�(���Ƥϥ����å��Ѥ�)
        if ($request->get('year') == '')  return false;
        if ($request->get('month') == '') return false;
        if ($request->get('day') == '')   return false;
        $query = "
            SELECT  note FROM company_calendar
            WHERE tdate = DATE '{$request->get('year')}-{$request->get('month')}-{$request->get('day')}'
        ";
        if ($this->getUniResult($query, $note) > 0) {
            $result->add('note', $note);
            $result->add('title', "{$request->get('year')}-{$request->get('month')}-{$request->get('day')} �ε������Ķ����Υ������Խ�");
            return true;
        } else {
            return false;
        }
    }
    
    ////////// MVC �� Model ���η�� ɽ���ѤΥǡ�������
    ///// List��    �ǡ��������� ����ɽ
    public function outViewListHTML($request, $menu, $uniq)
    {
                /***** �إå���������� *****/
        // �����HTML�����������
        $headHTML  = $this->getViewHTMLconst('header', $uniq);
        // ��������HTML�����������
        $headHTML .= $this->getViewHTMLheader($request);
        // �����HTML�����������
        $headHTML .= $this->getViewHTMLconst('footer', $uniq);
        // HTML�ե��������
        $file_name = "list/companyCalendar_ViewListHeader-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $headHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
        
                /***** ��ʸ����� *****/
        // �����HTML�����������
        $listHTML  = $this->getViewHTMLconst('header', $uniq);
        // ��������HTML�����������
        $listHTML .= $this->getViewHTMLbody($request, $menu);
        // �����HTML�����������
        $listHTML .= $this->getViewHTMLconst('footer', $uniq);
        // HTML�ե��������
        $file_name = "list/companyCalendar_ViewListBody-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
        
                /***** �եå���������� *****/
        // �����HTML�����������
        $footHTML  = $this->getViewHTMLconst('header', $uniq);
        // ��������HTML�����������
        $footHTML .= $this->getViewHTMLfooter();
        // �����HTML�����������
        $footHTML .= $this->getViewHTMLconst('footer', $uniq);
        // HTML�ե��������
        $file_name = "list/companyCalendar_ViewListFooter-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $footHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
        return ;
    }
    
    ///// �о����λ����Խ���Ԥ��������ټ���
    public function getTimeDetail($request, $result, $flg=1)
    {
        // �ѥ�᡼���������å�
        if ($request->get('year') == '') {
            $_SESSION['s_sysmsg'] = '�о�������������ޤ���Ǥ�����';
            return false;
        }
        if ($request->get('month') == '') {
            $_SESSION['s_sysmsg'] = '�о�������������ޤ���Ǥ�����';
            return false;
        }
        if ($request->get('day') == '') {
            $_SESSION['s_sysmsg'] = '�о�������������ޤ���Ǥ�����';
            return false;
        }
        if ($flg == 1) {
            return $this->getTimeDetailExecute($request, $result);
        } else {
            return $this->setTimeCopyExecute($request, $result);
        }
    }
    
    ///// �ĶȻ��֡��ٷƻ��֤��Խ��� hour <select> option �ν���
    public function getHourValues($para_hour)
    {
        // �����
        $option = "\n";
        for ($i=0; $i<=24; $i++) {  // 23 �� 24 �ˤ�����ͳ��24����Ϣ³��ư�����뤿�ᡢ24���֤�Ķ��������å���Ԥ�
            $hour = sprintf('%02d', $i);
            $mbHour = mb_convert_kana($hour, 'N');
            if ($para_hour == $i) {
                $option .= "<option value='{$hour}' selected>{$mbHour}</option>\n";
            } else {
                $option .= "<option value='{$hour}'>{$mbHour}</option>\n";
            }
        }
        // �� <option value='08'>����</option>
        return $option;
    }
    
    ///// �ĶȻ��֡��ٷƻ��֤��Խ��� minute <select> option �ν���
    public function getMinuteValues($para_minute)
    {
        // �����
        $option = "\n";
        for ($i=0; $i<=59; $i++) {
            $minute = sprintf('%02d', $i);
            $mbMinute = mb_convert_kana($minute, 'N');
            if ($para_minute == $i) {
                $option .= "<option value='{$minute}' selected>{$mbMinute}</option>\n";
            } else {
                $option .= "<option value='{$minute}'>{$mbMinute}</option>\n";
            }
        }
        // �� <option value='35'>����</option>
        return $option;
    }
    
    ///// �ĶȻ��֡��ٷƻ��֤��Խ��ǡ�����¸
    public function timeSave($request)
    {
        // �ǡ�����¸�ߥ����å�
        if ($request->get('day')   == '')       return;
        if ($request->get('str_hour')   == '')  return;
        if ($request->get('str_minute') == '')  return;
        if ($request->get('end_hour')   == '')  return;
        if ($request->get('end_minute') == '')  return;
        // �ĶȻ��֤���Ͽ��������ٷƻ��֤���Ͽ������������å�
        if ($request->get('bdSave') != '') {    // �ĶȻ��֤���Ͽ
            // �ǡ������ѹ������å�
            if ($request->get('old_str_time') == "{$request->get('str_hour')}:{$request->get('str_minute')}") {
                if ($request->get('old_end_time') == "{$request->get('end_hour')}:{$request->get('end_minute')}") {
                    if ($request->get('old_bh_note') == $request->get('bh_note')) {
                        return;
                    }
                }
            }
            // ���ϻ��֤Ƚ�λ���֤�Ŭ�������å�
            if ("{$request->get('str_hour')}{$request->get('str_minute')}" >= "{$request->get('end_hour')}{$request->get('end_minute')}") {
                $_SESSION['s_sysmsg'] = '�ĶȻ��֤�Ʊ������ž���Ƥ��ޤ���';
                return;
            }
            // ��Ͽ�¹�
            $this->bhTimeSaveExecute($request);
        } elseif ($request->get('bdDelete') != '') {    // �ĶȻ��֤κ��
            // ����¹�
            $this->bhTimeDeleteExecute($request);
        } elseif ($request->get('atSave') != '') {      // �ٷƻ��֤���Ͽ
            // �ǡ������ѹ������å�
            if ($request->get('old_str_time') == "{$request->get('str_hour')}:{$request->get('str_minute')}") {
                if ($request->get('old_end_time') == "{$request->get('end_hour')}:{$request->get('end_minute')}") {
                    if ($request->get('old_absent_note') == $request->get('absent_note')) {
                        return;
                    }
                }
            }
            // ���ϻ��֤Ƚ�λ���֤�Ŭ�������å�
            if ("{$request->get('str_hour')}{$request->get('str_minute')}" >= "{$request->get('end_hour')}{$request->get('end_minute')}") {
                $_SESSION['s_sysmsg'] = '�ٷƻ��֤�Ʊ������ž���Ƥ��ޤ���';
                return;
            }
            // ��Ͽ�¹�
            $this->atTimeSaveExecute($request);
        } elseif ($request->get('atDelete') != '') {    // �ٷƻ��֤κ��
            // ����¹�
            $this->atTimeDeleteExecute($request);
        }
        return;
    }
    
    ///// �оݴ��Υ����������������뤿���ö�������
    public function deleteCalendar($request)
    {
        if (!$this->getAuthority($_SESSION['User_ID'], $this->authDiv)) {
            return false;
        }
        // �ѥ�᡼���������å�(���Ƥϥ����å��Ѥ�)
        if ($request->get('targetDateStr') == '') {
            $_SESSION['s_sysmsg'] = '����������������ޤ���Ǥ�����';
            return false;
        }
        if ($request->get('targetDateEnd') == '') {
            $_SESSION['s_sysmsg'] = '��λ������������ޤ���Ǥ�����';
            return false;
        }
        // DB�Υ��ͥ���������
        if ($con = $this->connectDB()) {
            // �ȥ�󥶥�����󳫻�
            $this->query_affected_trans($con, 'BEGIN');
        } else {
            $_SESSION['s_sysmsg'] = 'DB�����ƥ२�顼';
            return false;
        }
        $query = "
            DELETE FROM company_calendar
            WHERE tdate >= DATE '{$request->get('targetDateStr')}01' AND tdate <= DATE '{$request->get('targetDateEnd')}31'
        ";
        if ($this->query_affected_trans($con, $query) <= 0) {   // ���������ϥǡ��������뤿�� <= �����
            $_SESSION['s_sysmsg'] = '���������ν��������ޤ���Ǥ�����������ô���Ԥ�Ϣ���Ʋ�������';
            $this->query_affected_trans($con, 'ROLLBACK');
            return false;
        } else {
            $query = "
                DELETE FROM company_business_hours
                WHERE tdate >= DATE '{$request->get('targetDateStr')}01' AND tdate <= DATE '{$request->get('targetDateEnd')}31'
            ";
            if ($this->query_affected_trans($con, $query) < 0) {
                $_SESSION['s_sysmsg'] = '�ĶȻ��֤ν��������ޤ���Ǥ�����������ô���Ԥ�Ϣ���Ʋ�������';
                $this->query_affected_trans($con, 'ROLLBACK');
                return false;
            } else {
                $query = "
                    DELETE FROM company_absent_time
                    WHERE tdate >= DATE '{$request->get('targetDateStr')}01' AND tdate <= DATE '{$request->get('targetDateEnd')}31'
                ";
                if ($this->query_affected_trans($con, $query) < 0) {
                    $_SESSION['s_sysmsg'] = '�ٷƻ��֤ν��������ޤ���Ǥ�����������ô���Ԥ�Ϣ���Ʋ�������';
                    $this->query_affected_trans($con, 'ROLLBACK');
                    return false;
                }
            }
        }
        $this->query_affected_trans($con, 'COMMIT');
        // $_SESSION['s_sysmsg'] = '�������λ���ޤ�����'; Ajax�Τ��ᥳ����
        return true;
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// �ꥯ�����Ȥˤ��SQLʸ�δ���WHERE�������
    protected function SetInitWhere($request)
    {
        // ���Υ᥽�åɤϸ��߻��Ѥ��Ƥ��ʤ�
        $year  = $request->get('year');
        $month = $request->get('month');
        if ($month == 12) {
            $endYear  = $year + 1;
            $endMonth = '01';
        } else {
            $endYear  = $year;
            $endMonth = $month + 1;
            $endMonth = sprintf('%02d', $endMonth);
        }
        $where = "
            calen.tdate >= DATE '{$year}-{$month}-01' AND calen.tdate < DATE '{$endYear}-{$endMonth}-01'
        ";
        return $where;
    }
    
    ////////// �������������٥ǡ������� �¹���
    protected function getCalendarDetail($request, $result)
    {
        ///// �������������� ����
        $query = "
            SELECT bd_flg, note FROM company_calendar WHERE tdate = DATE '{$request->get('year')}-{$request->get('month')}-{$request->get('day')}'
        ";
        if ($this->getResult2($query, $res) <= 0) {
            $_SESSION['s_sysmsg'] = '���������Υǡ���������ޤ���';
            return false;   // �����ƥ२�顼�ʤΤ�³�Ԥ��ʤ�
        }
        $result->add('bd_flg',  $res[0][0]);
        $result->add('bd_note', $res[0][1]);
        return true;
    }
    
    ////////// �����Խ��Ѥ����٥ǡ������� �¹���
    protected function getTimeDetailExecute($request, $result)
    {
        ///// �������������� ����
        if (!$this->getCalendarDetail($request, $result)) {
            return false;
        }
        ///// �ĶȻ��֤μ���
        $query = "
            SELECT
                 to_char(str_time, 'HH24')      AS str_hour
                ,to_char(str_time, 'MI')        AS str_minute
                ,to_char(end_time, 'HH24')      AS end_hour
                ,to_char(end_time, 'MI')        AS end_minute
                ,hours                          AS hours
                ,note                           AS bh_note
            FROM
                company_business_hours
            WHERE
                tdate = DATE '{$request->get('year')}-{$request->get('month')}-{$request->get('day')}'
            ORDER BY tdate DESC -- <= �� = ���ѹ�(�ºݤ���Ͽ����Ƥ���ǡ����Τߤˤ���)
            LIMIT 1
        ";
        $res = array();
        if ($this->getResult2($query, $res) <= 0) {
            // $_SESSION['s_sysmsg'] = '�ĶȻ��֤Υǡ���������ޤ���';
            // return false;    // �����Ͽ���θ���ƥ����Ȥˤ���
        } else {
            $result->add('str_hour',   $res[0][0]);
            $result->add('str_minute', $res[0][1]);
            $result->add('end_hour',   $res[0][2]);
            $result->add('end_minute', $res[0][3]);
            $result->add('hours',      $res[0][4]);
            $result->add('bh_note',    $res[0][5]);
        }
        ///// �ٷƻ��֤μ���
        $query = "
            SELECT
                 to_char(str_time, 'HH24')      AS str_hour
                ,to_char(str_time, 'MI')        AS str_minute
                ,to_char(end_time, 'HH24')      AS end_hour
                ,to_char(end_time, 'MI')        AS end_minute
                ,absent_time                    AS absent_time
                ,note                           AS absent_note
            FROM
                company_absent_time
            WHERE
                tdate = DATE '{$request->get('year')}-{$request->get('month')}-{$request->get('day')}'
            ORDER BY
                str_time ASC
        ";
        $res = array(); // �����
        if (($rows=$this->getResult($query, $res)) <= 0) {
            // ���κǽ���Ͽ�ǵٷƻ��֤μ����򤷤Ƥ������å�����
            // $_SESSION['s_sysmsg'] = '�ٷƻ��֤μ���������ޤ���Ǥ�����';
            // return false;    // �����Ͽ���θ���ƥ����Ȥˤ���
        }
        $result->add_array($res);
        $result->add('array_rows', $rows);
        return true;
    }
    
    ////////// �����Խ��Ѥβ���ľ��ǡ������ԡ���¸ �¹���
    protected function setTimeCopyExecute($request, $result)
    {
        if (!$this->getAuthority($_SESSION['User_ID'], $this->authDiv)) {
            return false;
        }
        ///// �ĶȻ��֤Υ��ԡ�
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        $sql = "
            INSERT INTO company_business_hours (tdate, str_time, end_time, hours, note, last_date, last_user)
            SELECT
                 DATE '{$request->get('year')}-{$request->get('month')}-{$request->get('day')}'
                ,str_time
                ,end_time
                ,hours
                ,note
                ,'{$last_date}'
                ,'{$last_user}'
            FROM
                company_business_hours
            WHERE
                tdate <= DATE '{$request->get('year')}-{$request->get('month')}-{$request->get('day')}'
            ORDER BY tdate DESC -- = �� <= �����(�ºݤˤ�<������)
            LIMIT 1
        ";
        if ($this->query_affected($sql) <= 0) {
            $_SESSION['s_sysmsg'] = '���ԡ�����ĶȻ��֤Υǡ���������ޤ���';
        } else {
            $_SESSION['s_sysmsg'] = '�ĶȻ��֤Υǡ����򥳥ԡ����ޤ�����';
        }
        ///// �ٷƻ��֤Υ��ԡ�
        // �оݥǡ�����ͭ��̵�������å�
        $query = "
            SELECT tdate FROM company_absent_time WHERE tdate <= DATE '{$request->get('year')}-{$request->get('month')}-{$request->get('day')}'
            ORDER BY tdate DESC
            LIMIT 1
        ";
        if ($this->getUniResult($query, $tdate) <= 0) {
            if ($_SESSION['s_sysmsg'] == '') {
                $_SESSION['s_sysmsg'] = '���ԡ�����ٷƻ��֤Υǡ���������ޤ���';
            } else {
                $_SESSION['s_sysmsg'] .= '\n\n���ԡ�����ٷƻ��֤Υǡ���������ޤ���';
            }
        } else {
            $sql = "
                INSERT INTO company_absent_time (tdate, str_time, end_time, absent_time, note, last_date, last_user)
                SELECT
                     DATE '{$request->get('year')}-{$request->get('month')}-{$request->get('day')}'
                    ,str_time
                    ,end_time
                    ,absent_time
                    ,note
                    ,'{$last_date}'
                    ,'{$last_user}'
                FROM
                    company_absent_time
                WHERE
                    tdate = '{$tdate}'
                ORDER BY
                    str_time ASC
            ";
            if ($this->query_affected($sql) <= 0) {
                if ($_SESSION['s_sysmsg'] == '') {
                    $_SESSION['s_sysmsg'] = '�ٷƻ��֤Υ��ԡ��˼��Ԥ��ޤ�����';
                } else {
                    $_SESSION['s_sysmsg'] .= '\n\n�ٷƻ��֤Υ��ԡ��˼��Ԥ��ޤ�����';
                }
            } else {
                if ($_SESSION['s_sysmsg'] == '') {
                    $_SESSION['s_sysmsg'] = '�ٷƻ��֤Υǡ����򥳥ԡ����ޤ�����';
                } else {
                    $_SESSION['s_sysmsg'] .= '\n\n�ٷƻ��֤Υǡ����򥳥ԡ����ޤ�����';
                }
            }
        }
        return true;
    }
    
    ////////// �ĶȻ��֤���¸ �¹���
    protected function bhTimeSaveExecute($request)
    {
        if (!$this->getAuthority($_SESSION['User_ID'], $this->authDiv)) {
            return false;
        }
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        // �ǡ�����¸�ߥ����å�
        $query = "
            SELECT tdate FROM company_business_hours WHERE tdate = DATE '{$request->get('year')}-{$request->get('month')}-{$request->get('day')}'
        ";
        if ($this->getUniResult($query, $check) <= 0) {
            // �ǡ���̵��insert
            $sql = "
                INSERT INTO company_business_hours (tdate, str_time, end_time, hours, note, last_date, last_user)
                VALUES (
                    DATE '{$request->get('year')}-{$request->get('month')}-{$request->get('day')}' ,
                    TIME '{$request->get('str_hour')}:{$request->get('str_minute')}:00' ,
                    TIME '{$request->get('end_hour')}:{$request->get('end_minute')}:00' ,
                    EXTRACT(HOUR FROM (TIME '{$request->get('end_hour')}:{$request->get('end_minute')}:00' - TIME '{$request->get('str_hour')}:{$request->get('str_minute')}:00')) * 60 + EXTRACT(MINUTE FROM (TIME '{$request->get('end_hour')}:{$request->get('end_minute')}:00' - TIME '{$request->get('str_hour')}:{$request->get('str_minute')}:00')) ,
                    '{$request->get('bh_note')}' ,
                    '{$last_date}' , '{$last_user}'
                )
            ";
        } else {
            // �ǡ�������update
            $sql = "
                UPDATE company_business_hours SET
                str_time = TIME '{$request->get('str_hour')}:{$request->get('str_minute')}:00' ,
                end_time = TIME '{$request->get('end_hour')}:{$request->get('end_minute')}:00' ,
                hours = EXTRACT(HOUR FROM (TIME '{$request->get('end_hour')}:{$request->get('end_minute')}:00' - TIME '{$request->get('str_hour')}:{$request->get('str_minute')}:00')) * 60 + EXTRACT(MINUTE FROM (TIME '{$request->get('end_hour')}:{$request->get('end_minute')}:00' - TIME '{$request->get('str_hour')}:{$request->get('str_minute')}:00')) ,
                note = '{$request->get('bh_note')}' ,
                last_date = '{$last_date}', last_user = '{$last_user}'
                WHERE tdate = DATE '{$request->get('year')}-{$request->get('month')}-{$request->get('day')}'
            ";
        }
        if ($this->query_affected($sql) <= 0) {
            $_SESSION['s_sysmsg'] = '�ĶȻ��֤���Ͽ�˼��Ԥ��ޤ�����������ô���Ԥ�Ϣ���Ʋ�������';
        } else {
            $_SESSION['s_sysmsg'] = '�ĶȻ��֤���Ͽ���ޤ�����';
        }
        return;
    }
    
    ////////// �ĶȻ��֤κ�� �¹���
    protected function bhTimeDeleteExecute($request)
    {
        if (!$this->getAuthority($_SESSION['User_ID'], $this->authDiv)) {
            return false;
        }
        // �ٷƻ��֤���Ͽ�����å�
        $query = "
            SELECT tdate FROM company_absent_time WHERE tdate = DATE '{$request->get('year')}-{$request->get('month')}-{$request->get('day')}'
        ";
        if ($this->getResult2($query, $check) > 0) {
            $_SESSION['s_sysmsg'] = '��˵ٷƻ��֤������Ʋ�������';
            return;
        }
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        // �ǡ�����¸�ߥ����å�
        $query = "
            SELECT tdate FROM company_business_hours WHERE tdate = DATE '{$request->get('year')}-{$request->get('month')}-{$request->get('day')}'
        ";
        if ($this->getUniResult($query, $check) <= 0) {
            // �ǡ���̵�� ��Ͽ����Ƥ��ʤ���ʬ�κ����������¾�Υ��饤����Ȥ����
            return;
        } else {
            // �ǡ�������(����������뤫��Ƥ��)
            $sql = "
                DELETE FROM company_business_hours WHERE tdate = DATE '{$request->get('year')}-{$request->get('month')}-{$request->get('day')}'
            ";
        }
        if ($this->query_affected($sql) <= 0) {
            $_SESSION['s_sysmsg'] = '�ĶȻ��֤κ���˼��Ԥ��ޤ�����������ô���Ԥ�Ϣ���Ʋ�������';
        } else {
            $_SESSION['s_sysmsg'] = '�ĶȻ��֤������ޤ�����';
        }
        return;
    }
    
    ////////// �ٷƻ��֤���¸ �¹���
    protected function atTimeSaveExecute($request)
    {
        if (!$this->getAuthority($_SESSION['User_ID'], $this->authDiv)) {
            return false;
        }
        // �ĶȻ��֤���Ͽ�����å�
        $query = "
            SELECT to_char(str_time, 'HH24MI'), to_char(end_time, 'HH24MI') FROM company_business_hours
            WHERE
                tdate = DATE '{$request->get('year')}-{$request->get('month')}-{$request->get('day')}'
        ";
        $res = array();
        if ($this->getResult2($query, $res) <= 0) {
            $_SESSION['s_sysmsg'] = '�ĶȻ��֤������Ͽ���Ʋ�������';
            return;
        }
        // �ĶȻ�����εٷƤ������å�
        if ($res[0][0] > "{$request->get('str_hour')}{$request->get('str_minute')}") {
            $_SESSION['s_sysmsg'] = '���ϻ��֤��ĶȻ��ֳ��Ǥ���';
            return;
        }
        if ($res[0][1] < "{$request->get('end_hour')}{$request->get('end_minute')}") {
            $_SESSION['s_sysmsg'] = '��λ���֤��ĶȻ��ֳ��Ǥ���';
            return;
        }
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        // �ǡ����ν�ʣ�����å�
        if (!$this->atTimeDuplicate($request)) {
            return;
        }
        // OLD VALUE �Υ����å�
        if (str_replace(':', '', $request->get('old_str_time')) == '') {
            // �ǡ���̵��insert
            $sql = "
                INSERT INTO company_absent_time (tdate, str_time, end_time, absent_time, note, last_date, last_user)
                VALUES (
                    DATE '{$request->get('year')}-{$request->get('month')}-{$request->get('day')}' ,
                    TIME '{$request->get('str_hour')}:{$request->get('str_minute')}:00' ,
                    TIME '{$request->get('end_hour')}:{$request->get('end_minute')}:00' ,
                    EXTRACT(HOUR FROM (TIME '{$request->get('end_hour')}:{$request->get('end_minute')}:00' - TIME '{$request->get('str_hour')}:{$request->get('str_minute')}:00')) * 60 + EXTRACT(MINUTE FROM (TIME '{$request->get('end_hour')}:{$request->get('end_minute')}:00' - TIME '{$request->get('str_hour')}:{$request->get('str_minute')}:00')) ,
                    '{$request->get('absent_note')}' ,
                    '{$last_date}' , '{$last_user}'
                )
            ";
        } else {
            // �ǡ�������update
            $sql = "
                UPDATE company_absent_time SET
                    str_time = TIME '{$request->get('str_hour')}:{$request->get('str_minute')}:00' ,
                    end_time = TIME '{$request->get('end_hour')}:{$request->get('end_minute')}:00' ,
                    absent_time = EXTRACT(HOUR FROM (TIME '{$request->get('end_hour')}:{$request->get('end_minute')}:00' - TIME '{$request->get('str_hour')}:{$request->get('str_minute')}:00')) * 60 + EXTRACT(MINUTE FROM (TIME '{$request->get('end_hour')}:{$request->get('end_minute')}:00' - TIME '{$request->get('str_hour')}:{$request->get('str_minute')}:00')) ,
                    note = '{$request->get('absent_note')}' ,
                    last_date = '{$last_date}', last_user = '{$last_user}'
                WHERE
                    tdate = DATE '{$request->get('year')}-{$request->get('month')}-{$request->get('day')}'
                AND
                    str_time = TIME '{$request->get('old_str_time')}:00'
                AND
                    end_time = TIME '{$request->get('old_end_time')}:00'
            ";
        }
        if ($this->query_affected($sql) <= 0) {
            $_SESSION['s_sysmsg'] = '�ٷƻ��֤���Ͽ�˼��Ԥ��ޤ�����������ô���Ԥ�Ϣ���Ʋ�������';
        } else {
            $_SESSION['s_sysmsg'] = '�ٷƻ��֤���Ͽ���ޤ�����';
        }
        return;
    }
    
    ////////// �ٷƻ��֤ν�ʣ�����å�
    protected function atTimeDuplicate($request)
    {
        // OLD VALUE �Υ����å�
        if ($request->get('old_str_time') != '') {  // �ѹ��ξ��
            $query = "
            SELECT tdate FROM company_absent_time
            WHERE
                tdate = DATE '{$request->get('year')}-{$request->get('month')}-{$request->get('day')}'
            AND
                str_time != TIME '{$request->get('old_str_time')}:00'
            AND
                end_time != TIME '{$request->get('old_end_time')}:00'
            AND
                (str_time, end_time) OVERLAPS (TIME'{$request->get('str_hour')}:{$request->get('str_minute')}:00', TIME '{$request->get('end_hour')}:{$request->get('end_minute')}:00')
            ";
        } else {    // �ɲäξ��
            $query = "
            SELECT tdate FROM company_absent_time
            WHERE
                tdate = DATE '{$request->get('year')}-{$request->get('month')}-{$request->get('day')}'
            AND
                (str_time, end_time) OVERLAPS (TIME'{$request->get('str_hour')}:{$request->get('str_minute')}:00', TIME '{$request->get('end_hour')}:{$request->get('end_minute')}:00')
            ";
        }
        if ($this->getResult2($query, $check) >= 1) {
            $_SESSION['s_sysmsg'] = '�ٷƻ��֤�¾�Ƚ�ʣ���Ƥ��ޤ���';
            return false;
        } else {
            return true;
        }
    }
    
    ////////// �ٷƻ��֤κ�� �¹���
    protected function atTimeDeleteExecute($request)
    {
        if (!$this->getAuthority($_SESSION['User_ID'], $this->authDiv)) {
            return false;
        }
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        // �ǡ�����¸�ߥ����å�
        $query = "
            SELECT tdate FROM company_absent_time WHERE tdate = DATE '{$request->get('year')}-{$request->get('month')}-{$request->get('day')}'
            AND
                str_time = TIME '{$request->get('old_str_time')}:00'
            AND
                end_time = TIME '{$request->get('old_end_time')}:00'
        ";
        if ($this->getUniResult($query, $check) <= 0) {
            // �ǡ���̵�� ��Ͽ����Ƥ��ʤ���ʬ�κ����������¾�Υ��饤����Ȥ����
            return;
        } else {
            // �ǡ�������(����������뤫��Ƥ��)
            $sql = "
                DELETE FROM company_absent_time WHERE tdate = DATE '{$request->get('year')}-{$request->get('month')}-{$request->get('day')}'
                AND
                    str_time = TIME '{$request->get('old_str_time')}:00'
                AND
                    end_time = TIME '{$request->get('old_end_time')}:00'
            ";
        }
        if ($this->query_affected($sql) <= 0) {
            $_SESSION['s_sysmsg'] = '�ٷƻ��֤κ���˼��Ԥ��ޤ�����������ô���Ԥ�Ϣ���Ʋ�������';
        } else {
            $_SESSION['s_sysmsg'] = '�ٷƻ��֤������ޤ�����';
        }
        return;
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ///// List��   ����ɽ�� �إå����������
    private function getViewHTMLheader($request)
    {
        // �����
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field list' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <th class='winbox' width='12%'>���� (����)</th>\n";
        $listTable .= "        <th class='winbox' width='15%'>�ĵ٥�����</th>\n";
        $listTable .= "        <th class='winbox' width='11%'>����</th>\n";
        $listTable .= "        <th class='winbox' width='11%'>��λ</th>\n";
        $listTable .= "        <th class='winbox' width='12%'>����(ʬ)</th>\n";
        $listTable .= "        <th class='winbox' width='15%'>�Ļ�������</th>\n";
        $listTable .= "        <th class='winbox' width='12%'>�ٷ�(ʬ)</th>\n";
        $listTable .= "        <th class='winbox' width='12%'>�»�(ʬ)</th>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        return $listTable;
    }
    
    ///// List��   ����å����줿��������������֤Υꥹ�� ���٥ǡ�������
    private function getViewHTMLbody($request, $menu)
    {
        $query = $this->getQueryStatement($request);
        
        // �����
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field list' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) < 1 ) {
            $listTable .= "    <tr>\n";
            $listTable .= "        <td width='100%' align='center' class='winbox'>�ǡ���������ޤ���</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        } else {
            $week = array('��', '��', '��', '��', '��', '��', '��');
            for ($i=0; $i<$rows; $i++) {
                $dayWeek = $week[date('w', mktime(0, 0, 0, $res[$i][12], $res[$i][13], $res[$i][11]))];
                if ($res[$i][10] == 't') {
                    $listTable .= "    <tr style='font-weight:bold;'\n";
                    $listTable .= "        onClick='CompanyCalendar.win_open(\"{$menu->out_self()}?showMenu=TimeEdit&year={$res[$i][11]}&month={$res[$i][12]}&day={$res[$i][13]}\", 800, 600, \"timeEditWin\");'\n";
                    $listTable .= "        title='{$res[$i][12]}/{$res[$i][13]}�αĶȻ��֤ȵٷƻ��֤��Խ���Ԥ��ޤ���'\n";
                    $listTable .= "        onMouseover=\"this.style.backgroundColor='#ceffce'; this.style.color='black'; this.style.cursor='hand'; \"\n";
                    $listTable .= "        onMouseout =\"this.style.backgroundColor=''; this.style.color=''; this.style.cursor='auto'; \"\n";
                    $listTable .= "    >\n";
                } else {
                    $listTable .= "    <tr style='color:white;'\n";
                    $listTable .= "        onClick='CompanyCalendar.win_open(\"{$menu->out_self()}?showMenu=TimeEdit&year={$res[$i][11]}&month={$res[$i][12]}&day={$res[$i][13]}\", 800, 600, \"timeEditWin\");'\n";
                    $listTable .= "        title='{$res[$i][12]}/{$res[$i][13]}�αĶȡ����������صڤӥ����Ȥ��Խ���Ԥ��ޤ���'\n";
                    $listTable .= "        onMouseover=\"this.style.backgroundColor='#ceffce'; this.style.color='red'; this.style.cursor='hand'; \"\n";
                    $listTable .= "        onMouseout =\"this.style.backgroundColor=''; this.style.color='white'; this.style.cursor='auto'; \"\n";
                    $listTable .= "    >\n";
                }
                $listTable .= "        <td class='winbox' width='12%' align='center'>{$res[$i][0]} ({$dayWeek})</td>\n"; // ����(����)
                $listTable .= "        <td class='winbox' width='15%' align='left'  >{$res[$i][1]}</td>\n";         // ������
                if ($res[$i][3] == '') $res[$i][3] = '&nbsp;';
                $listTable .= "        <td class='winbox' width='11%' align='center'>{$res[$i][3]}</td>\n";         // ���ϻ���
                if ($res[$i][4] == '') $res[$i][4] = '&nbsp;';
                $listTable .= "        <td class='winbox' width='11%' align='center'>{$res[$i][4]}</td>\n";         // ��λ����
                $listTable .= "        <td class='winbox' width='12%' align='right' ><span style='color:blue;'>{$res[$i][2]}</span>".number_format($res[$i][5])."</td>\n";// �ĶȻ���(ʬ)
                if ($res[$i][6] == '') $res[$i][6] = '&nbsp;';
                $listTable .= "        <td class='winbox' width='15%' align='left'  >{$res[$i][6]}</td>\n";         // �Ķȥ�����
                $listTable .= "        <td class='winbox' width='12%' align='right' ><span style='color:blue;'>{$res[$i][7]}</span>".number_format($res[$i][8])."</td>\n";// �ٷƻ��ֹ��(ʬ)
                $listTable .= "        <td class='winbox' width='12%' align='right' >".number_format($res[$i][5]-$res[$i][8])."</td>\n";// �»�(ʬ)
                $listTable .= "    </tr>\n";
                if ($res[$i][10] == 't') {
                    $this->sumBusinessHours += $res[$i][5];
                    $this->sumAbsentTime    += $res[$i][8];
                }
            }
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
            $this->netBusinessHours = ($this->sumBusinessHours - $this->sumAbsentTime);
        }
        // return mb_convert_encoding($listTable, 'UTF-8');
        return $listTable;
    }
    
    ///// List��   ����ɽ�� �եå����������
    private function getViewHTMLfooter()
    {
        // �����
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field list' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td class='winbox' width='38%' align='right'>&nbsp;</td>\n";
        $listTable .= "        <td class='winbox' width='11%' align='right'>���</td>\n";   // �ĶȻ���
        $listTable .= "        <td class='winbox' width='12%' align='right'>".number_format($this->sumBusinessHours)."</td>\n";
        $listTable .= "        <td class='winbox' width='15%' align='right'>&nbsp;</td>\n";   // �ٷƻ���
        $listTable .= "        <td class='winbox' width='12%' align='right'>".number_format($this->sumAbsentTime)."</td>\n";
        $listTable .= "        <td class='winbox' width='12%' align='right'>".number_format($this->netBusinessHours)."</td>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        return $listTable;
    }
    
    ///// List��   ����ɽ��SQL���ơ��ȥ��ȼ���
    private function getQueryStatement($request)
    {
        $year  = $request->get('year');
        $month = $request->get('month');
        if ($month == 12) {
            $endYear  = $year + 1;
            $endMonth = '01';
        } else {
            $endYear  = $year;
            $endMonth = $month + 1;
            $endMonth = sprintf('%02d', $endMonth);
        }
        $endYMD = date('Y-m-d', mktime(0, 0, 0, $endMonth, 0, $endYear));
        $query = "
            SELECT
                  to_char(tdate, 'MM/DD')
                                            AS ����         -- 00
                , CASE
                    WHEN bd_note = '' THEN '&nbsp;'
                    ELSE bd_note
                  END                       AS ������     -- 01
                , CASE
                    WHEN bh_flg THEN '��'
                    ELSE ''
                  END                       AS �ĶȻ�����Ͽ -- 02
                , to_char(bh_str_time, 'HH24:MI')
                                            AS ���ϻ���     -- 03
                , to_char(bh_end_time, 'HH24:MI')
                                            AS ��λ����     -- 04
                , bh_hours                  AS �ĶȻ���     -- 05
                , CASE
                    WHEN bh_note IS NULL THEN '&nbsp;'
                    WHEN bh_note = '' THEN '&nbsp;'
                    ELSE bh_note
                  END                       AS �Ķȥ����� -- 06
                , CASE
                    WHEN at_flg THEN '��'
                    ELSE ''
                  END                       AS �ٷƻ�����Ͽ -- 07
                , at_sum                    AS �ٷƹ�׻��� -- 08
                , at_count                  AS �ٷƲ��     -- 09
                --------------------------------------------�ʲ��ϥꥹ�ȳ�
                , bd_flg                    AS �Ķȵ���     -- 10
                , to_char(tdate, 'YYYY')
                                            AS ǯ           -- 11
                , to_char(tdate, 'MM')
                                            AS ��           -- 12
                , to_char(tdate, 'DD')
                                            AS ��           -- 13
            FROM
                company_calendar_schedule(DATE '{$year}-{$month}-01', DATE '{$endYMD}', FALSE)
            ORDER BY
                tdate ASC
        ";
        return $query;
    }
    
    ///// �����List��    HTML�ե��������
    private function getViewHTMLconst($status, $uniq)
    {
        if ($status == 'header') {
            $listHTML = 
"<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=EUC-JP'>
<meta http-equiv='Content-Style-Type' content='text/css'>
<meta http-equiv='Content-Script-Type' content='text/javascript'>
<title>�ĶȻ��֡��ٷƻ��֤��Խ�</title>
<script type='text/javascript' src='/base_class.js'?id={$uniq}></script>
<link rel='stylesheet' href='/menu_form.css?id={$uniq}' type='text/css' media='screen'>
<link rel='stylesheet' href='../companyCalendar.css?id={$uniq}' type='text/css' media='screen'>
<script type='text/javascript' src='../companyCalendar.js?id={$uniq}'></script>
<style type='text/css'>
<!--
body {
    background-image:none;
}
-->
</style>
</head>
<body style='background-color:#d6d3ce;'>  <!--  -->
<center>
";
        } elseif ($status == 'footer') {
            $listHTML = "\n";   // �����
            if ($_SESSION['s_sysmsg'] == '') {
                $listHTML .= "</center>\n";
                $listHTML .= "</body>\n";
                $listHTML .= "</html>\n";
            } else {
                $listHTML .= "</center>\n";
                $listHTML .= "</body>\n";
                $listHTML .= "<script type='text/javascript'>\n";
                $listHTML .= "    alert('{$_SESSION['s_sysmsg']}');\n";
                $listHTML .= "</script>\n";
                $listHTML .= "</html>\n";
                $_SESSION['s_sysmsg'] = '';     // ��å��������ꥢ
            }
        } else {
            $listHTML = '';
        }
        return $listHTML;
    }
    
    ///// �������� �����HTML�ե��������
    private function getViewCalendarHTMLconst($status, $uniq)
    {
        if ($status == 'header') {
            $listHTML = 
"<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=EUC-JP'>
<meta http-equiv='Content-Style-Type' content='text/css'>
<meta http-equiv='Content-Script-Type' content='text/javascript'>
<title>��Ҥδ��ܥ�������</title>
<script type='text/javascript' src='/base_class.js?id={$uniq}'></script>
<link rel='stylesheet' href='/menu_form.css?id={$uniq}' type='text/css' media='screen'>
<link rel='stylesheet' href='../calendar.css?id={$uniq}' type='text/css' media='screen'>
<link rel='stylesheet' href='../companyCalendar.css?id={$uniq}' type='text/css' media='screen'>
<script type='text/javascript' src='../companyCalendar.js?id={$uniq}'></script>
<style type='text/css'>
<!--
body {
    background-image:none;
    background-color:transparent;
}
-->
</style>
</head>
<body>
<center>
";
        } elseif ($status == 'footer') {
            $listHTML = "\n";   // �����
            if ($_SESSION['s_sysmsg'] == '') {
                $listHTML .= "</center>\n";
                $listHTML .= "</body>\n";
                $listHTML .= "</html>\n";
            } else {
                $listHTML .= "</center>\n";
                $listHTML .= "</body>\n";
                $listHTML .= "<script type='text/javascript'>\n";
                $listHTML .= "    alert('{$_SESSION['s_sysmsg']}');\n";
                $listHTML .= "</script>\n";
                $listHTML .= "</html>\n";
                $_SESSION['s_sysmsg'] = '';     // ��å��������ꥢ
            }
        } else {
            $listHTML = '';
        }
        return $listHTML;
    }
    
    ////////// �Խ����¥����å�
    private function getAuthority($id, $division)
    {
        /******************************
        switch ($id) {
        case '010561':  // ����
        case '300101':  // ��ë(��)
        case '300144':  // ��ë
        case '300071':  // ����
        case '017850':  // ����
        case '018261':  // �ڲ�
        case '300055':  // ��ƣ
        case '970223':  // ­Ω
        case '970227':  // �ݻ�
            return true;
        }
        $_SESSION['s_sysmsg'] = '�Խ����¤�����ޤ���ɬ�פʾ��ˤϡ�ô���Ԥ�Ϣ���Ʋ�������';
        return false;
        ******************************/
        
        ///// DAO ���饹������
        // ����No.�Τߤǥ����å�
        if ($this->getCheckAuthority($division)) {
            return true;    // ���¤���
        }
        // $division������¾�ʤ�ʲ��ν� (����No.�ȸ���No.���б�����ID)
        // if ($this->getCheckAuthority($division, $act_id)) {
        //     return true;
        // }
        $_SESSION['s_sysmsg'] = '�Խ����¤�����ޤ���ɬ�פʾ��ˤϡ�ô���Ԥ�Ϣ���Ʋ�������';
        return false;
    }
    
} // Class CompanyCalendar_Model End

?>
