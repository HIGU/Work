<?php
//////////////////////////////////////////////////////////////////////////////
// ���Ƚ���ν��� ��� �Ȳ�                                  MVC Model ��   //
// Copyright (C) 2008-2020     Norihisa.Ohya usoumu@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2008/09/22 Created   working_hours_report_Model.php                      //
// 2009/03/26 �ٶ�ɽ�����б�                                                //
// 2017/03/28 �ǿ�������Ĺ���б�                                            //
// 2017/05/08 �Ͱ���ɽ�����ѹ�(��Ĺ�ʾ���̳���б�)                          //
// 2017/06/02 ����Ĺ���� �ܳʲ�ư                                           //
// 2017/06/07 ���ɡ�sid=19�ˤϽ��Ȼ��֤��㤦����Ķȥ����å����б�          //
// 2017/06/12 ���ֵ٤�ɽ�����ְ�äƤ����Τǽ���                            //
//            �������������ʳ��ǡ������ξ�����ʤ��ǡ��Ժ���ͳ���ʤ����  //
//            �����ˤʤ�褦�ѹ�                                            //
// 2017/06/13 �ĶȻ��֤Υ����å��˿���ĶȤ��̣                            //
// 2017/06/22 ���顼�ΤߤξȲ���ɲ�                                        //
//            Ǥ�ո����ȼҰ����ɽ�����顼�Ƚж�MC�λ��ֵ٥��顼����      //
// 2017/06/29 �����̾Ȳ���б��ʹ���Ĺ�����                                //
// 2017/07/12 ���ֵٻ��֤ȷ��������ɽ��(���̽��פΤ� �����Ų�Ĺ��������)   //
// 2017/07/27 �ѡ��ȱ�Ĺ����̤�ǹ�⥨�顼ɽ��                              //
// 2017/08/02 ��ȴ���б��ΰ١�working_hours_report_data_new��DB���ѹ�       //
//            24���ܤ˳���MC���ɲ�                                          //
// 2017/09/13 ���칩������(sid=95)����������͡�����                        //
// 2018/03/30 �����򵻽Ѳݡ����Ĥ����L��Ω�ݤ��ѹ�                     //
// 2018/09/26 �¤ӽ�˥����ɥơ��֥�η��������ɤ��ɲ�                      //
// 2019/02/01 4/1�οͻ���ưʬ���ɲ�                                         //
// 2020/04/01 4/1�οͻ���ưʬ���ɲ�                                         //
//////////////////////////////////////////////////////////////////////////////
//�ǽ�Ū�ˤ� \\Fs1\��̳������\�ͻ��ط�\���� ���׻�������������.xls ����
// ini_set('error_reporting', E_STRICT);               // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');                     // Error ɽ�� ON debug �� ��꡼���女����

require_once ('../../daoInterfaceClass.php');          // TNK ������ DAO���󥿡��ե��������饹


/*****************************************************************************************
*       MVC��Model�� ���饹��� daoInterfaceClass(base class) ���쥯�饹���ĥ           *
*****************************************************************************************/
class WorkingHoursReport_Model extends daoInterfaceClass
{
    ////// Private properties
    private $where;                                    // ���� SQL��WHERE��
    private $last_avail_pcs;                           // �ǽ�ͭ����(�ǽ�ͽ��߸˿�)
    
    ////// public properties
    // public  $graph;                                 // GanttChart�Υ��󥹥���
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////// Constructer ����� (php5�ذܹԻ��� __construct() ���ѹ�ͽ��) (�ǥ��ȥ饯��__destruct())
    public function __construct($request)
    {
        // ����WHERE�������
        switch ($request->get('showMenu')) {
        case 'List':
        case 'ListWin':
            // $this->where = $this->SetInitWhere($request);
            // break;
        case 'CondForm':
        case 'WaitMsg':
        default:
            $this->where = '';
        }
    }
    
    ////// �о�ǯ���HTML <select> option �ν���
    public function getTargetDateYMvalues($request)
    {
        // �����
        $option   = "\n";
        $yyyymm   = date('Ym'); $yyyy = date('Y'); $mm = date('m');
        if ($request->get('targetDateYM') == $yyyymm) {
            $option .= "<option value='{$yyyymm}' selected>{$yyyy}ǯ{$mm}��</option>\n";
        } else {
            $option .= "<option value='{$yyyymm}'>{$yyyy}ǯ{$mm}��</option>\n";
        }
        while (1) {
            $mm--;
            if ($mm < 1) {
                $mm = 12; $yyyy -= 1;
            }
            $mm = sprintf('%02d', $mm);
            $yyyymm = $yyyy . $mm;
            if ($request->get('targetDateYM') == $yyyymm) {
                $option .= "<option value='{$yyyymm}' selected>{$yyyy}ǯ{$mm}��</option>\n";
            } else {
                $option .= "<option value='{$yyyymm}'>{$yyyy}ǯ{$mm}��</option>\n";
            }
            if ($yyyymm <= 201604)
                break;
        }
        return $option;
    }
    
    ////// �о������HTML <select> option �ν���
    public function getTargetSectionvalues($request)
    {
        // �����
        $option = "\n";
        // ��������
        if (getCheckAuthority(28)) {
            //$query="select * from section_master where sflg=1 and sid<>99 and sid<>90 and sid<>80 and sid<>38 and sid<>31 and sid<>17 and sid<>9 and sid<>8 order by sid asc";
            $query="select * from section_master where sflg=1 and sid<>90 and sid<>95 and sid<>80 and sid<>31 order by sid asc";
            $res=array();
            if($rows=getResult($query,$res)){
                for($i=0;$i<$rows;$i++){
                    //if (trim($res[$i]['section_name']) != '��¤��') {
                        //if (trim($res[$i]['section_name']) != '������') {
                            //if (trim($res[$i]['section_name']) != '������') {
                                //if (trim($res[$i]['section_name']) != '�������칩��') {
                                    if($request->get('targetSection') == $res[$i]["sid"]) {
                                        $option .= "<option value='{$res[$i]['sid']}' selected>" . mb_substr(trim($res[$i]['section_name']), -10). "</option>\n";
                                    } else {
                                        $option .= "<option value='{$res[$i]['sid']}'>" . mb_substr(trim($res[$i]['section_name']), -10) . "</option>\n";
                                    }
                                //}
                            //}
                        //}
                    //}
                }
                if($request->get('targetSection') == '-4') {
                    $option .= "<option value='-4' selected>�Ķ�</option>\n";
                } else {
                    $option .= "<option value='-4'>�Ķ�</option>\n";
                }
                //if ($_SESSION['User_ID'] == '300144') {
                if($request->get('targetSection') == '-5') {
                    $option .= "<option value='-5' selected>���顼�Τ�</option>\n";
                } else {
                    $option .= "<option value='-5'>���顼�Τ�</option>\n";
                }
                //}
            }
        } else if(getCheckAuthority(29)) {    // ����Ĺ��������Ĺ�����Ƥ�����Ǥ���
            //$query="select * from section_master where sflg=1 and sid<>99 and sid<>90 and sid<>80 and sid<>38 and sid<>31 and sid<>17 and sid<>9 and sid<>8 order by sid asc";
            $query="select * from section_master where sflg=1 and sid<>90 and sid<>95 and sid<>80 and sid<>31 order by sid asc";
            $res=array();
            if($rows=getResult($query,$res)){
                for($i=0;$i<$rows;$i++){
                    //if (trim($res[$i]['section_name']) != '��¤��') {                    // ��¤���ݤ�����ΰ�
                        //if (trim($res[$i]['section_name']) != '������') {                // �Ƴ�ǧ����˰�ư������
                            //if (trim($res[$i]['section_name']) != '������') {            // ��Ω���ݤ�ɽ���ΰ�
                                //if (trim($res[$i]['section_name']) != '�������칩��') {  // ��Ĺ�Τ�
                                    if($request->get('targetSection') == $res[$i]["sid"]) {
                                        $option .= "<option value='{$res[$i]['sid']}' selected>" . mb_substr(trim($res[$i]['section_name']), -10). "</option>\n";
                                    } else {
                                        $option .= "<option value='{$res[$i]['sid']}'>" . mb_substr(trim($res[$i]['section_name']), -10) . "</option>\n";
                                    }
                                //}
                            //}
                        //}
                    //}
                }
            }
        } else if(getCheckAuthority(42)) {    // �������ϵ������Τ߱����Ǥ���
            //$query="select * from section_master where sflg=1 and sid<>99 and sid<>90 and sid<>80 and sid<>38 and sid<>31 and sid<>17 and sid<>9 and sid<>8 order by sid asc";
            $sid_where="(sid='38' or sid='18' or sid='4')";
            $query="select * from section_master where sflg=1 and {$sid_where} order by sid asc";
            $res=array();
            if($rows=getResult($query,$res)){
                for($i=0;$i<$rows;$i++){
                    if($request->get('targetSection') == $res[$i]["sid"]) {
                        $option .= "<option value='{$res[$i]['sid']}' selected>" . mb_substr(trim($res[$i]['section_name']), -10). "</option>\n";
                    } else {
                        $option .= "<option value='{$res[$i]['sid']}'>" . mb_substr(trim($res[$i]['section_name']), -10) . "</option>\n";
                    }
                }
            }
        } else if(getCheckAuthority(43)) {    // ���������������Τ߱����Ǥ���
            //$query="select * from section_master where sflg=1 and sid<>99 and sid<>90 and sid<>80 and sid<>38 and sid<>31 and sid<>17 and sid<>9 and sid<>8 order by sid asc";
            $sid_where="(sid='8' or sid='32' or sid='2' or sid='3')";
            $query="select * from section_master where sflg=1 and {$sid_where} order by sid asc";
            $res=array();
            if($rows=getResult($query,$res)){
                for($i=0;$i<$rows;$i++){
                    if($request->get('targetSection') == $res[$i]["sid"]) {
                        $option .= "<option value='{$res[$i]['sid']}' selected>" . mb_substr(trim($res[$i]['section_name']), -10). "</option>\n";
                    } else {
                        $option .= "<option value='{$res[$i]['sid']}'>" . mb_substr(trim($res[$i]['section_name']), -10) . "</option>\n";
                    }
                }
            }
        } else if(getCheckAuthority(55)) {    // ��¤������¤���Τ߱����Ǥ���
            //$query="select * from section_master where sflg=1 and sid<>99 and sid<>90 and sid<>80 and sid<>38 and sid<>31 and sid<>17 and sid<>9 and sid<>8 order by sid asc";
            $sid_where="(sid='17' or sid='34' or sid='35')";
            $query="select * from section_master where sflg=1 and {$sid_where} order by sid asc";
            $res=array();
            if($rows=getResult($query,$res)){
                for($i=0;$i<$rows;$i++){
                    if($request->get('targetSection') == $res[$i]["sid"]) {
                        $option .= "<option value='{$res[$i]['sid']}' selected>" . mb_substr(trim($res[$i]['section_name']), -10). "</option>\n";
                    } else {
                        $option .= "<option value='{$res[$i]['sid']}'>" . mb_substr(trim($res[$i]['section_name']), -10) . "</option>\n";
                    }
                }
            }
        } else {
        // ������Τ߾Ȳ� �Ʋݤβ�Ĺ�μҰ��ֹ�������
            if ($_SESSION['User_ID'] == '300349') {    // ���ʴ�����   ¼���Ĺ����
                $sid=19;    
            } else if ($_SESSION['User_ID'] == '012980' || $_SESSION['User_ID'] == '300098') {    // �ʾڲ�   ������Ĺ���� �����Ĺ����
                $sid=18;
            } else if ($_SESSION['User_ID'] == '018040') {    // ��¤���� �����Ų�Ĺ
                $sid=34;
            } else if ($_SESSION['User_ID'] == '015202') {    // ��¤���� �ⶶ��Ĺ
                $sid=35;
            } else if ($_SESSION['User_ID'] == '016713' || $_SESSION['User_ID'] == '016080') {    // ���ɲ�   �滳��Ĺ���� ������Ĺ
                $sid=32;
            } else if ($_SESSION['User_ID'] == '017850' || $_SESSION['User_ID'] == '300055') {    // ��̳��   ������Ĺ���� ����Ĺ����
                $sid=5;
            } else if ($_SESSION['User_ID'] == '017728') {    // ��˥���Ω��  ���Ĳ�Ĺ
                $sid=3;
            } else if ($_SESSION['User_ID'] == '017507') {    // ���ץ���Ω�� ������Ĺ
                $sid=2;
            } else if ($_SESSION['User_ID'] == '014524') {    // ���Ѳ� �����Ĺ
                $sid=4;
            }
            $query="select * from section_master where sflg=1 and sid={$sid} order by sid asc";
            $res=array();
            if($rows=getResult($query,$res)){
                $option .= "<option value='{$res[0]['sid']}' selected>" . trim($res[0]['section_name']). "</option>\n";
            }
        }
        return $option;
    }
    // �����ǧ������ ����̾�μ���
    public function getTargetSectionConfirm()
    {
        // �����
        $res=array();
        $section_name=array();
        $section_count = 0;
        // ��������
        if (getCheckAuthority(28)) {
            $query="select * from section_master where sflg=1 and sid<>99 and sid<>90 and sid<>95 and sid<>80 and sid<>38 and sid<>31 and sid<>17 and sid<>9 and sid<>8 order by sid asc";
            if($rows=getResult($query,$res)){
                for($i=0;$i<$rows;$i++){
                    if (trim($res[$i]['section_name']) != '��¤��') {
                        if (trim($res[$i]['section_name']) != '������') {
                            if (trim($res[$i]['section_name']) != '������') {
                                if (trim($res[$i]['section_name']) != '�������칩��') {
                                    $section_name[$section_count][0] = $res[$i]['section_name'];
                                    $section_name[$section_count][1] = $res[$i]['sid'];
                                    $section_count += 1;
                                }
                            }
                        }
                    }
                }
                //$section_name[$section_count][0] = '���鿦�ʾ�';
                //$section_name[$section_count][1] = -3;
            }
        } else {
            // ������Τ߾Ȳ� �Ʋݤβ�Ĺ�μҰ��ֹ�������
            if ($_SESSION['User_ID'] == '014524') {           // ���Ѳ�   �����Ĺ
                $sid=4;
            } else if ($_SESSION['User_ID'] == '300349') {    // ���ʴ�����   ¼���Ĺ����
                $sid=19;    
            } else if ($_SESSION['User_ID'] == '012980' || $_SESSION['User_ID'] == '300098') {    // �ʾڲ�   ������Ĺ���� �����Ĺ����
                $sid=18;
            } else if ($_SESSION['User_ID'] == '018040') {    // ��¤���� �����Ų�Ĺ
                $sid=34;
            } else if ($_SESSION['User_ID'] == '015202') {    // ��¤���� �ⶶ��Ĺ
                $sid=35;
            } else if ($_SESSION['User_ID'] == '016713' || $_SESSION['User_ID'] == '016080') {    // ���ɲ�   �滳��Ĺ���� ������Ĺ����
                $sid=32;
            } else if ($_SESSION['User_ID'] == '017850' || $_SESSION['User_ID'] == '300055') {    // ��̳��   ������Ĺ���� ����Ĺ����
                $sid=5;
            } else if ($_SESSION['User_ID'] == '017728') {    // ��˥���Ω�� ���Ĳ�Ĺ
                $sid=3;
            } else if ($_SESSION['User_ID'] == '017507') {    // ���ץ���Ω�� ������Ĺ
                $sid=2;
            }
            $query="select * from section_master where sflg=1 and sid={$sid} order by sid asc";
            if($rows=getResult($query,$res)){
                $section_name[$section_count][0] = $res[0]['section_name'];
                $section_name[$section_count][1] = $res[0]['sid'];
            }
        }
        return $section_name;
    }
    ////// �������Ƥ�HTML <select> option �ν���
    public function getTargetConfirmvalues($request)
    {
        // �����
        $option = "\n";
        $uid              = $request->get('uid');
        $working_date     = $request->get('str_date'); 
        $query = sprintf("SELECT confirm_flg FROM working_hours_report_confirm WHERE uid='%s' AND working_date=%d", $uid, $working_date);
        $res_chk = array();
        if ( getResult($query, $res_chk) > 0 ) {    // ��Ͽ���� <option>����
            if ($res_chk[0] == 1) {
                $option .= "<option value='1' selected>����ʤ�</option>\n";
                $option .= "<option value='2'>�Ͻ���к�</option>\n";
                $option .= "<option value='3'>�Ͻа����</option>\n";
            } elseif ($res_chk[0] == 2) {
                $option .= "<option value='1'>����ʤ�</option>\n";
                $option .= "<option value='2' selected>�Ͻ���к�</option>\n";
                $option .= "<option value='3'>�Ͻа����</option>\n";
            } elseif ($res_chk[0] == 3) {
                $option .= "<option value='1'>����ʤ�</option>\n";
                $option .= "<option value='2'>�Ͻ���к�</option>\n";
                $option .= "<option value='3' selected>�Ͻа����</option>\n";
            }
        } else {                                    // ��Ͽ�ʤ�<option>̤����
            $option .= "<option value='1' selected>����ʤ�</option>\n";
            $option .= "<option value='2'>�Ͻ���к�</option>\n";
            $option .= "<option value='3'>�Ͻа����</option>\n";
        }
        return $option;
    }
    ////// MVC �� Model ���η�� ɽ���ѤΥǡ�������
    ////// List��    �ǡ��������� ����ɽ
    public function outViewListHTML($request, $menu, $check_flg)
    {
        /***** �إå���������� *****/
        /*****************
        // �����HTML�����������
        $headHTML  = $this->getViewHTMLconst('header');
        // ��������HTML�����������
        $headHTML .= $this->getViewHTMLheader($request);
        // �����HTML�����������
        $headHTML .= $this->getViewHTMLconst('footer');
        // HTML�ե��������
        $file_name = "list/working_hours_report_ViewListHeader-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $headHTML);
        fclose($handle);
        chmod($file_name, 0666);                                         // file������rw�⡼�ɤˤ���
        *****************/
        
                /***** ��ʸ����� *****/
        // �����HTML�����������
        $listHTML  = $this->getViewHTMLconst('header');
        // ��������HTML�����������
        $listHTML .= $this->getViewHTMLbody($request, $menu, $check_flg);        // ����ɽ��
        // �����HTML�����������
        $listHTML .= $this->getViewHTMLconst('footer');
        // HTML�ե��������
        $file_name = "list/working_hours_report_ViewList-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);                                         // file������rw�⡼�ɤˤ���
        
        /***** �եå���������� *****/
        // �����HTML�����������
        $footHTML  = $this->getViewHTMLconst('header');
        // ��������HTML�����������
        $footHTML .= $this->getViewHTMLfooter($request);
        // �����HTML�����������
        $footHTML .= $this->getViewHTMLconst('footer');
        // HTML�ե��������
        $file_name = "list/working_hours_report_ViewListFooter-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $footHTML);
        fclose($handle);
        chmod($file_name, 0666);                                         // file������rw�⡼�ɤˤ���
        return ;
    }
    public function outViewCorrectListHTML($request, $menu, $endflg)
    {    
        // �����HTML�����������
        $listHTML  = $this->getViewHTMLconst('header');
        // ��������HTML�����������
        $listHTML .= $this->getViewCorrectHTMLbody($request, $menu, $endflg);        // ����ɽ��
        // �����HTML�����������
        $listHTML .= $this->getViewHTMLconst('footer');
        // HTML�ե��������
        $file_name = "list/working_hours_report_ViewCorrectList-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);                                         // file������rw�⡼�ɤˤ���
        return ;
    }
    public function outViewConfirmListHTML($request, $menu)
    {    
        // �����HTML�����������
        $listHTML  = $this->getViewHTMLconst('header');
        // ��������HTML�����������
        $listHTML .= $this->getViewConfirmHTMLbody($request, $menu);        // ����ɽ��
        // �����HTML�����������
        $listHTML .= $this->getViewHTMLconst('footer');
        // HTML�ե��������
        $file_name = "list/working_hours_report_ViewConfirmList-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);                                         // file������rw�⡼�ɤˤ���
        return ;
    }
    
    public function outViewMailListHTML($request, $menu)
    {    
        // �����HTML�����������
        $listHTML  = $this->getViewHTMLconst('header');
        // ��������HTML�����������
        $listHTML .= $this->getViewMailHTMLbody($request, $menu);        // ����ɽ��
        // �����HTML�����������
        $listHTML .= $this->getViewHTMLconst('footer');
        // HTML�ե��������
        $file_name = "list/working_hours_report_ViewConfirmList-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);                                         // file������rw�⡼�ɤˤ���
        return ;
    }
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////// �ꥯ�����Ȥˤ��SQLʸ�δ���WHERE�������
    protected function SetInitWhere($request)
    {
        // ���ȥ����ɥץ������㡼�η���
        // SELECT * FROM assembly_schedule_time_line($request->get('targetDateStr'), $request->get('targetDateEnd'), '$request->get('targetLine')')
        if ($request->get('showMenu') == 'Graph') {
            $where = "{$request->get('targetDateStr')}, {$request->get('targetDateEnd')}, '{$request->get('targetLine')}'";
        } else {
            $where = "{$request->get('targetDateList')}, {$request->get('targetDateList')}, '{$request->get('targetLine')}'";
        }
        return $where;
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ///// List��   ���Ƚ���Ȳ�����٥ǡ�������
    private function getViewHTMLbody($request, $menu, $chek_flg)
    {
        $uid = array();                                                     // �Ұ��ֹ�
        $res = array();
        $s_name = array();                                                  // ����̾
        // �����
        $listTable  = '';
        if ($request->get('uid') != '') {                                   // �Ұ��ֹ椬���Ϥ���Ƥ���м���
            $uid[0]     = $request->get('uid');
            $s_name[0]  = $this->getSectionNameOne($uid[0]);                // �Ұ�No.�������̾�����
        } else {
            $query = $this->getSectionUser($request->get('targetSection'), $request->get('targetPosition'), $request->get('targetDateStr'), $request->get('targetDateEnd')); // �����������°�Ұ������
            if ($rows=getResult($query,$res)) {
                for ($i=0; $i<$rows; $i++) {
                    $uid[$i]   = $res[$i]['uid'];
                    $sid_t[$i] = $res[$i]['sid'];
                }
                // ���Ϥ��ʤ��Τǥ����Ȳ�
                /*
                if ($request->get('targetSection') == 4) {                  // ��������°�Ұ����б�
                    $uid[$i] = '000817';                                    // 000817=������ ���Ӥ���
                    $res[$i]['sid'] = 9;                                    // sid�˴��������ɲ�
                    $rows = $rows + 1;                                      // sid�ɲäΰ�$rows�⣱�ɲ�
                }
                */
                $s_name = $this->getSectionName($rows,$res);                // ������������祳���ɤ������̾�����
            } else {
                $uid    = '------';
                $s_name ='----------';
            }
        }
        $uid_num = count($uid);
        for ($t=0; $t<$uid_num; $t++) {                                     // �и��ԤμҰ��ֹ���Ѵ�(TimePro�ǡ���)
            if ($uid[$t] == '014737') {                                     // �и��Ԥ��ɲ��ѹ�����в����ᤷ��Ʊ�����ѹ�
                $uid[$t] = '914737';                                        // 014737=��̳�� �񤵤�
            } else if ($uid[$t] == '020273') {                              // 020273=���Ѳ� ��ƣ����
                $uid[$t] = '920273';
            }
        }
        $today_ym   = date('Ymd');
        $listTable .= "<CENTER>\n";
        $listTable .= "<font size='4'><B>���ȡ�����<B></font>\n";
        $listTable .= "<HR width='300' color='black' noshade>\n";
        $listTable .= "</CENTER>\n";
        for ($t=0; $t<$uid_num; $t++) {
            if (substr($uid[$t], 0, 3) == '990') {                          // �����Ȱ��Ͻ���
                continue;
            }
            if ($request->get('targetSection') == '-4') {   // �Ķ�ͭ��
                $working_data = array();
                $working_data = $this->getTimeProDataOver($request, $uid, $sid_t, $t);      // ������ץ�ǡ����μ������Ѵ�
                if ($uid[$t] == '914737') {                                     // �и��ԤμҰ��ֹ���ᤷ
                    $uid[$t] = '014737';
                } else if ($uid[$t] == '920273') {
                    $uid[$t] = '020273';
                }
                $work_num    = $request->get('work_num');
                $howork_num  = $request->get('howork_num');
                if ($working_data) {
                    $listTable .= "<BR><CENTER>\n";
                    $listTable .= "<U><font size='2'>�Ұ�No.��". $uid[$t] ."����".  $this->getUserName($uid[$t]) ."������°��". $s_name[$t] ."�����������֡���". format_date($request->get('targetDateStr')) ."������". format_date($request->get('targetDateEnd')) ."</U>\n";
                    $listTable .= "</CENTER>\n";
                    $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
                    $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
                    $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='2'>\n";
                    if ($request->get('rows') <= 0) {
                        $listTable .= "    <tr>\n";
                        $listTable .= "        <td colspan='11' width='100%' align='center' class='winbox'>�ǡ���������ޤ���</td>\n";
                        $listTable .= "    </tr>\n";
                        $listTable .= "</table>\n";
                        $listTable .= "    </td></tr>\n";
                        $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
                    } else {
                        if ($request->get('formal') == 'details') {                 // �ꥹ�ȷ����Υ����å�
                            $listTable .= "    <tr>\n";                                 // ���٥ǡ�����ɽ��
                            $listTable .= "        <th class='winbox'>����</th>\n";
                            $listTable .= "        <th class='winbox'>����</th>\n";
                            $listTable .= "        <th class='winbox'>����<BR>���</th>\n";
                            $listTable .= "        <th class='winbox'>�Ժ�</th>\n";
                            $listTable .= "        <th class='winbox'>�ж�<BR>����</th>\n";
                            $listTable .= "        <th class='winbox'>���<BR>����</th>\n";
                            $listTable .= "        <th class='winbox'>����<BR>����</th>\n";
                            $listTable .= "        <th class='winbox'>��Ĺ<BR>����</th>\n";
                            $listTable .= "        <th class='winbox'>���<BR>�Ķ�</th>\n";
                            $listTable .= "        <th class='winbox'>����<BR>�Ķ�</th>\n";
                            $listTable .= "        <th class='winbox'>�ٽ�<BR>����</th>\n";
                            $listTable .= "        <th class='winbox'>�ٽ�<BR>�Ķ�</th>\n";
                            $listTable .= "        <th class='winbox'>�ٽ�<BR>����</th>\n";
                            $listTable .= "        <th class='winbox'>ˡ��<BR>����</th>\n";
                            $listTable .= "        <th class='winbox'>ˡ��<BR>�Ķ�</th>\n";
                            $listTable .= "        <th class='winbox'>�ٹ�<BR>����</th>\n";
                            //$listTable .= "        <th class='winbox'>����<BR>��ǧ</th>\n";
                            $listTable .= "    </tr>\n";
                            for ($r=0; $r<$request->get('rows'); $r++) {                                // �쥳���ɿ�ʬ���֤�
                                $listTable .= "<tr>\n";
                                for ($i=0; $i<$request->get('num'); $i++) {
                                    switch ($i) {
                                        case 1:                                         // ����
                                            if ($working_data[$r][23] == '��') {
                                                if ($working_data[$r][3] == '����') {
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap><font color='red'>". substr($working_data[$r][$i], 4, 2) . "/". substr($working_data[$r][$i], 6, 2) ."</font></td>\n";
                                                    break;
                                                } elseif ($working_data[$r][3] == 'ˡ��') {
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap><font color='red'>". substr($working_data[$r][$i], 4, 2) . "/". substr($working_data[$r][$i], 6, 2) ."</font></td>\n";
                                                    break;
                                                } elseif ($working_data[$r][3] == '�ٶ�') {
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap><font color='blue'>". substr($working_data[$r][$i], 4, 2) . "/". substr($working_data[$r][$i], 6, 2) ."</font></td>\n";
                                                    break;
                                                } else {
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". substr($working_data[$r][$i], 4, 2) . "/". substr($working_data[$r][$i], 6, 2) ."</td>\n";
                                                    break;
                                                }
                                            } else {
                                                if ($working_data[$r][3] == '����') {
                                                    $listTable .= "<td class='winbox' align='right' nowrap><font color='red'>". substr($working_data[$r][$i], 4, 2) . "/". substr($working_data[$r][$i], 6, 2) ."</font></td>\n";
                                                    break;
                                                } elseif ($working_data[$r][3] == 'ˡ��') {
                                                    $listTable .= "<td class='winbox' align='right' nowrap><font color='red'>". substr($working_data[$r][$i], 4, 2) . "/". substr($working_data[$r][$i], 6, 2) ."</font></td>\n";
                                                    break;
                                                } elseif ($working_data[$r][3] == '�ٶ�') {
                                                    $listTable .= "<td class='winbox' align='right' nowrap><font color='blue'>". substr($working_data[$r][$i], 4, 2) . "/". substr($working_data[$r][$i], 6, 2) ."</font></td>\n";
                                                    break;
                                                } else {
                                                    $listTable .= "<td class='winbox' align='right' nowrap>". substr($working_data[$r][$i], 4, 2) . "/". substr($working_data[$r][$i], 6, 2) ."</td>\n";
                                                    break;
                                                }
                                            }
                                        case 2:                                         // ����
                                            if ($working_data[$r][23] == '��') {
                                                if ($working_data[$r][3] == '����') {
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap><font color='red'>". $working_data[$r][$i] ."</font></td>\n";
                                                    break;
                                                } elseif ($working_data[$r][3] == 'ˡ��') {
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap><font color='red'>". $working_data[$r][$i] ."</font></td>\n";
                                                    break;
                                                } elseif ($working_data[$r][3] == '�ٶ�') {
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap><font color='blue'>". $working_data[$r][$i] ."</font></td>\n";
                                                    break;
                                                } else {
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                    break;
                                                }
                                            } else {
                                                if ($working_data[$r][3] == '����') {
                                                    $listTable .= "<td class='winbox' align='right' nowrap><font color='red'>". $working_data[$r][$i] ."</font></td>\n";
                                                    break;
                                                } elseif ($working_data[$r][3] == 'ˡ��') {
                                                    $listTable .= "<td class='winbox' align='right' nowrap><font color='red'>". $working_data[$r][$i] ."</font></td>\n";
                                                    break;
                                                } elseif ($working_data[$r][3] == '�ٶ�') {
                                                    $listTable .= "<td class='winbox' align='right' nowrap><font color='blue'>". $working_data[$r][$i] ."</font></td>\n";
                                                    break;
                                                } else {
                                                    $listTable .= "<td class='winbox' align='right' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                    break;
                                                }
                                            }
                                        case 3:                                         // ��������
                                            if ($working_data[$r][23] == '��') {
                                                if ($working_data[$r][$i] == '����') {
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap><font color='red'>". $working_data[$r][$i] ."</font></td>\n";
                                                    break;
                                                } elseif ($working_data[$r][$i] == 'ˡ��') {
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap><font color='red'>". $working_data[$r][$i] ."</font></td>\n";
                                                    break;
                                                } elseif ($working_data[$r][$i] == '�ٶ�') {
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap><font color='blue'>". $working_data[$r][$i] ."</font></td>\n";
                                                    break;
                                                } else {
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                    break;
                                                }
                                            } else {
                                                if ($working_data[$r][$i] == '����') {
                                                    $listTable .= "<td class='winbox' align='right' nowrap><font color='red'>". $working_data[$r][$i] ."</font></td>\n";
                                                    break;
                                                } elseif ($working_data[$r][$i] == 'ˡ��') {
                                                    $listTable .= "<td class='winbox' align='right' nowrap><font color='red'>". $working_data[$r][$i] ."</font></td>\n";
                                                    break;
                                                } elseif ($working_data[$r][$i] == '�ٶ�') {
                                                    $listTable .= "<td class='winbox' align='right' nowrap><font color='blue'>". $working_data[$r][$i] ."</font></td>\n";
                                                    break;
                                                } else {
                                                    $listTable .= "<td class='winbox' align='right' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                    break;
                                                }
                                            }
                                        case 4:                                         // �Ժ���ͳ
                                            if ($working_data[$r][3] == '����') {
                                                if ($working_data[$r][23] == '��') {
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                    break;
                                                } else {
                                                    $listTable .= "<td class='winbox' align='right' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                    break;
                                                }
                                            } elseif ($working_data[$r][3] == 'ˡ��') {
                                                if ($working_data[$r][23] == '��') {
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                    break;
                                                } else {
                                                    $listTable .= "<td class='winbox' align='right' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                    break;
                                                }
                                            } elseif ($working_data[$r][3] == '�ٶ�') {
                                                if ($working_data[$r][23] == '��') {
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                    break;
                                                } else {
                                                    $listTable .= "<td class='winbox' align='right' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                    break;
                                                }
                                            } else {
                                                if ($working_data[$r][5] == '0000') {
                                                        if ($working_data[$r][6] == '0000') {
                                                            if ($working_data[$r][$i] == '��') {
                                                                $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                                break;
                                                            } else {
                                                                $listTable .= "<td class='winbox' align='right' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                                break;
                                                            }
                                                        } else {
                                                            $listTable .= "<td class='winbox' align='right' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                            break;
                                                        }
                                                } else {
                                                    $listTable .= "<td class='winbox' align='right' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                    break;
                                                }
                                            }
                                            /*
                                            if ($working_data[$r][23] == '��') {
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                break;
                                            } else {
                                                $listTable .= "<td class='winbox' align='right' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                break;
                                            }
                                            */
                                        case 5:                                         // �жл���
                                            if ($working_data[$r][23] == '��') {
                                                if ($working_data[$r][$i] == '0000') {
                                                        if ($working_data[$r][6] == '0000') {
                                                            $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                                        } else {
                                                            $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>----</td>\n";
                                                        }
                                                } else {
                                                    $work_num += 1;                         // �жл��郎�ǹ蘆��Ƥ���нж������ܣ�
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". substr($working_data[$r][$i], 0, 2) .":". substr($working_data[$r][$i], 2, 2) ."</td>\n";
                                                }
                                                break;
                                            } else {
                                                if ($working_data[$r][$i] == '0000') {
                                                        if ($working_data[$r][6] == '0000') {
                                                            $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                                        } else {
                                                            $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>----</td>\n";
                                                        }
                                                } else {
                                                    $work_num += 1;                         // �жл��郎�ǹ蘆��Ƥ���нж������ܣ�
                                                    $listTable .= "<td class='winbox' align='right' nowrap>". substr($working_data[$r][$i], 0, 2) .":". substr($working_data[$r][$i], 2, 2) ."</td>\n";
                                                }
                                                break;
                                            }
                                        case 6:                                         // ��л���
                                            if ($working_data[$r][23] == '��') {
                                                if ($working_data[$r][$i] == '0000') {
                                                        if ($working_data[$r][5] == '0000') {
                                                            $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                                        } else {
                                                            $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>----</td>\n";
                                                        }
                                                } else {
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". substr($working_data[$r][$i], 0, 2) .":". substr($working_data[$r][$i], 2, 2) ."</td>\n";
                                                }
                                                break;
                                            } else {
                                                if ($working_data[$r][$i] == '0000') {
                                                        if ($working_data[$r][5] == '0000') {
                                                            $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                                        } else {
                                                            $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>----</td>\n";
                                                        }
                                                } else {
                                                    $listTable .= "<td class='winbox' align='right' nowrap>". substr($working_data[$r][$i], 0, 2) .":". substr($working_data[$r][$i], 2, 2) ."</td>\n";
                                                }
                                                break;
                                            }
                                        case 7:                                         // �������
                                            if ($working_data[$r][23] == '��') {
                                                if ($working_data[$r][$i] == '000000') {
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                                } else {
                                                    $hour = floor($working_data[$r][$i] / 60);
                                                    $minutes = $working_data[$r][$i] % 60;
                                                    if($minutes == 0) {
                                                        $minutes = '00';
                                                    } else if($minutes < 10) {
                                                        $minutes = '0' . $minutes;
                                                    }
                                                    //$minutes = $working_data[$r][$i]%60;
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                                }
                                                break;
                                            } else {
                                                if ($working_data[$r][$i] == '000000') {
                                                        $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                                } else {
                                                    $hour = floor($working_data[$r][$i] / 60);
                                                    $minutes = $working_data[$r][$i] % 60;
                                                    if($minutes == 0) {
                                                        $minutes = '00';
                                                    } else if($minutes < 10) {
                                                        $minutes = '0' . $minutes;
                                                    }
                                                    //$minutes = $working_data[$r][$i]%60;
                                                    $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                }
                                                break;
                                            }
                                        case 8:                                         // ��Ĺ����
                                            if ($working_data[$r][23] == '��') {
                                                if ($working_data[$r][$i] == '000000') {
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                                } else {
                                                    $hour = floor($working_data[$r][$i] / 60);
                                                    $minutes = $working_data[$r][$i]%60;
                                                    if($hour == 0) {
                                                        $hour = '0';
                                                    }
                                                    if($minutes == 0) {
                                                        $minutes = '00';
                                                    } else if($minutes < 10) {
                                                        $minutes = '0' . $minutes;
                                                    }
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                                }
                                                break;
                                            } else {
                                                $query = "SELECT * FROM user_detailes WHERE uid='{$uid[$t]}' AND pid=5";
                                                $res_chk = array();
                                                if ( getResult($query, $res_chk) > 0 ) {    // �ѡ��� ��Ĺ�����å�
                                                    if ($working_data[$r][6] >= '1645') {   // ��Ĺ30ʬ�ʾ� �����å�
                                                        if ($working_data[$r][$i] == '000000') {
                                                            $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>----</td>\n";
                                                        } else {
                                                           $t_temp = $working_data[$r][$i];
                                                            // ��Ĺ���ַ׻��ʼ»��֡�
                                                            $deteObj = new DateTime($working_data[$r][6]);
                                                            $ceil_num = floor(sprintf('%d', $deteObj->format('i')) / 15) * 15;
                                                            $ceil_hour = $deteObj->format('H');
                                                            $have = $ceil_hour.sprintf( '%02d', $ceil_num);
                                                            $end = '2017-05-17 ' . substr($have, 0, 2) . ":" . substr($have, 2, 2) . ":00";
                                                            $startSec = strtotime('2017-05-17 16:15:00');
                                                            $endSec   = strtotime($end);
                                                                
                                                            $r_temp = ($endSec - $startSec)/60;
                                                            
                                                            if($r_temp>=60) {   // ��Ĺ�ϣ����֤ޤǤʤΤ�Ĵ��
                                                                $r_temp = 60;
                                                            } elseif($r_temp>=30) {
                                                                $r_temp = 30;
                                                            }
                                                            $hour_r = floor($r_temp / 60);
                                                            $minutes_r = $r_temp%60;
                                                            if($hour_r == 0) {
                                                                $hour_r = '0';
                                                            }
                                                            if($minutes_r == 0) {
                                                                $minutes_r = '00';
                                                            } else if($minutes_r < 10) {
                                                                $minutes_r = '0' . $minutes_r;
                                                            }
                                                            
                                                            // ��Ĺ���ַ׻��ʿ���ʬ��
                                                            $hour = floor($t_temp / 60);
                                                            $minutes = $t_temp%60;
                                                            if($hour == 0) {
                                                                $hour = '0';
                                                            }
                                                            if($minutes == 0) {
                                                                $minutes = '00';
                                                            } else if($minutes < 10) {
                                                                $minutes = '0' . $minutes;
                                                            }
                                                            if ($hour_r == $hour) {
                                                            //if ($hour_r == $hour_s) {
                                                                if ($minutes_r == $minutes) {
                                                                //if ($minutes_r == $minutes_s) {
                                                                    $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                                    //$listTable .= "<td class='winbox' align='right' nowrap>". $hour_r .":". $minutes_r ."</td>\n";
                                                                } else {
                                                                    $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>". $hour .":". $minutes ."</td>\n";
                                                                    //$listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                                    //$listTable .= "<td class='winbox' align='right' nowrap>". $hour_r .":". $minutes_r ."</td>\n";
                                                                }
                                                            } else {
                                                                    //$listTable .= "<td class='winbox' align='right' nowrap>". $hour_r .":". $minutes_r ."</td>\n";
                                                                    //$listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                                    $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>". $hour .":". $minutes ."</td>\n";
                                                            }
                                                        }
                                                    } else {                                // ��Ĺ̵�� �����å�̵��
                                                        if ($working_data[$r][$i] == '000000') {
                                                            $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                                        } else {
                                                            $hour = floor($working_data[$r][$i] / 60);
                                                            $minutes = $working_data[$r][$i]%60;
                                                            if($hour == 0) {
                                                                $hour = '0';
                                                            }
                                                            if($minutes == 0) {
                                                                $minutes = '00';
                                                            } else if($minutes < 10) {
                                                                $minutes = '0' . $minutes;
                                                            }
                                                            $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                        }
                                                    }
                                                } else {                                    // �ѡ��Ȱʳ� ��Ĺ�����å�̵��
                                                    if ($working_data[$r][$i] == '000000') {
                                                        $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                                    } else {
                                                        $hour = floor($working_data[$r][$i] / 60);
                                                        $minutes = $working_data[$r][$i]%60;
                                                        if($hour == 0) {
                                                            $hour = '0';
                                                        }
                                                        if($minutes == 0) {
                                                            $minutes = '00';
                                                        } else if($minutes < 10) {
                                                            $minutes = '0' . $minutes;
                                                        }
                                                        $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                    }
                                                }
                                                break;
                                            }
                                        case 9:                                         // ��лĶ�
                                            if ($working_data[$r][23] == '��') {
                                                $t_temp = $working_data[$r][$i] + $working_data[$r][$i+1];
                                                $s_temp = $t_temp + $working_data[$r][$i+2];    // ����ĶȲ�̣
                                                $query = "SELECT * FROM user_detailes WHERE uid='{$uid[$t]}' AND (class LIKE '8%' OR class LIKE '9%' OR class LIKE '10%' OR class LIKE '11%' OR class LIKE '12%')";
                                                $res_chk = array();
                                                if ( getResult($query, $res_chk) > 0 ) {    // 8�鿦�ʾ� �Ķȥ����å�̵��
                                                    if ($working_data[$r][4] == '�Ҳ�' || $working_data[$r][4] == 'Ǥ��') {
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>---</td>\n";
                                                    } elseif ($t_temp == '000000') {
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                                    } else {
                                                        $hour = floor($t_temp / 60);
                                                        $minutes = $t_temp%60;
                                                        if($hour == 0) {
                                                            $hour = '0';
                                                        }
                                                        if($minutes == 0) {
                                                            $minutes = '00';
                                                        } else if($minutes < 10) {
                                                            $minutes = '0' . $minutes;
                                                        }
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                                    }
                                                    break;
                                                } elseif($sid_t[$t] == 19) {
                                                    if ($working_data[$r][6] >= '1830') {
                                                        if ($working_data[$r][4] == '�Ҳ�' || $working_data[$r][4] == 'Ǥ��') {
                                                            $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>---</td>\n";
                                                        } elseif ($t_temp == '000000') {
                                                            $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>----</td>\n";
                                                        } else {
                                                            // �ĶȻ��ַ׻��ʼ»��֡�
                                                            $deteObj = new DateTime($working_data[$r][6]);
                                                            $ceil_num = floor(sprintf('%d', $deteObj->format('i')) / 30) * 30;
                                                            $ceil_hour = $deteObj->format('H');
                                                            $have = $ceil_hour.sprintf( '%02d', $ceil_num);
                                                            $end = '2017-05-17 ' . substr($have, 0, 2) . ":" . substr($have, 2, 2) . ":00";
                                                            $startSec = strtotime('2017-05-17 18:00:00');
                                                            $endSec   = strtotime($end);
                                                            
                                                            $r_temp = ($endSec - $startSec)/60;
                                                            
                                                            $hour_r = floor($r_temp / 60);
                                                            $minutes_r = $r_temp%60;
                                                            if($hour_r == 0) {
                                                                $hour_r = '0';
                                                            }
                                                            if($minutes_r == 0) {
                                                                $minutes_r = '00';
                                                            } else if($minutes_r < 10) {
                                                                $minutes_r = '0' . $minutes_r;
                                                            }
                                                            
                                                            // �ĶȻ��ַ׻��ʿ���ʬ��
                                                            $hour = floor($t_temp / 60);
                                                            $minutes = $t_temp%60;
                                                            if($hour == 0) {
                                                                $hour = '0';
                                                            }
                                                            if($minutes == 0) {
                                                                $minutes = '00';
                                                            } else if($minutes < 10) {
                                                                $minutes = '0' . $minutes;
                                                            }
                                                            
                                                            // ����ĶȲ�̣
                                                            $hour_s = floor($s_temp / 60);
                                                            $minutes_s = $s_temp%60;
                                                            if($hour_s == 0) {
                                                                $hour_s = '0';
                                                            }
                                                            if($minutes_s == 0) {
                                                                $minutes_s = '00';
                                                            } else if($minutes_s < 10) {
                                                                $minutes_s = '0' . $minutes_s;
                                                            }
                                                            
                                                            //if ($hour_r == $hour) {
                                                            if ($hour_r == $hour_s) {
                                                                //if ($minutes_r == $minutes) {
                                                                if ($minutes_r == $minutes_s) {
                                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                                                } else {
                                                                    $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>". $hour .":". $minutes ."</td>\n";
                                                                }
                                                            } else {
                                                                    $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>". $hour .":". $minutes ."</td>\n";
                                                            }
                                                        }
                                                        break;
                                                    } else {
                                                        if ($working_data[$r][4] == '�Ҳ�' || $working_data[$r][4] == 'Ǥ��') {
                                                            $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>---</td>\n";
                                                        } elseif ($t_temp == '000000') {
                                                            $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                                        } else {
                                                            $hour = floor($t_temp / 60);
                                                            $minutes = $t_temp%60;
                                                            if($hour == 0) {
                                                                $hour = '0';
                                                            }
                                                            if($minutes == 0) {
                                                                $minutes = '00';
                                                            } else if($minutes < 10) {
                                                                $minutes = '0' . $minutes;
                                                            }
                                                            $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                                        }
                                                        break;
                                                    }
                                                } else {
                                                    if ($working_data[$r][6] >= '1800') {
                                                        if ($working_data[$r][4] == '�Ҳ�' || $working_data[$r][4] == 'Ǥ��') {
                                                            $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>---</td>\n";
                                                        } elseif ($t_temp == '000000') {
                                                            $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>----</td>\n";
                                                        } else {
                                                            // �ĶȻ��ַ׻��ʼ»��֡�
                                                            $deteObj = new DateTime($working_data[$r][6]);
                                                            $ceil_num = floor(sprintf('%d', $deteObj->format('i')) / 30) * 30;
                                                            $ceil_hour = $deteObj->format('H');
                                                            $have = $ceil_hour.sprintf( '%02d', $ceil_num);
                                                            $end = '2017-05-17 ' . substr($have, 0, 2) . ":" . substr($have, 2, 2) . ":00";
                                                            $startSec = strtotime('2017-05-17 17:30:00');
                                                            $endSec   = strtotime($end);
                                                            
                                                            $r_temp = ($endSec - $startSec)/60;
                                                            
                                                            $hour_r = floor($r_temp / 60);
                                                            $minutes_r = $r_temp%60;
                                                            if($hour_r == 0) {
                                                                $hour_r = '0';
                                                            }
                                                            if($minutes_r == 0) {
                                                                $minutes_r = '00';
                                                            } else if($minutes_r < 10) {
                                                                $minutes_r = '0' . $minutes_r;
                                                            }
                                                            
                                                            // �ĶȻ��ַ׻��ʿ���ʬ��
                                                            $hour = floor($t_temp / 60);
                                                            $minutes = $t_temp%60;
                                                            if($hour == 0) {
                                                                $hour = '0';
                                                            }
                                                            if($minutes == 0) {
                                                                $minutes = '00';
                                                            } else if($minutes < 10) {
                                                                $minutes = '0' . $minutes;
                                                            }
                                                            // ����ĶȲ�̣
                                                            $hour_s = floor($s_temp / 60);
                                                            $minutes_s = $s_temp%60;
                                                            if($hour_s == 0) {
                                                                $hour_s = '0';
                                                            }
                                                            if($minutes_s == 0) {
                                                                $minutes_s = '00';
                                                            } else if($minutes_s < 10) {
                                                                $minutes_s = '0' . $minutes_s;
                                                            }
                                                            //if ($hour_r == $hour) {
                                                            if ($hour_r == $hour_s) {
                                                                //if ($minutes_r == $minutes) {
                                                                if ($minutes_r == $minutes_s) {
                                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                                                } else {
                                                                    $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>". $hour .":". $minutes ."</td>\n";
                                                                }
                                                            } else {
                                                                    $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>". $hour .":". $minutes ."</td>\n";
                                                            }
                                                        }
                                                        break;
                                                    } else {
                                                        if ($working_data[$r][4] == '�Ҳ�' || $working_data[$r][4] == 'Ǥ��') {
                                                            $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>---</td>\n";
                                                        } elseif ($t_temp == '000000') {
                                                            $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                                        } else {
                                                            $hour = floor($t_temp / 60);
                                                            $minutes = $t_temp%60;
                                                            if($hour == 0) {
                                                                $hour = '0';
                                                            }
                                                            if($minutes == 0) {
                                                                $minutes = '00';
                                                            } else if($minutes < 10) {
                                                                $minutes = '0' . $minutes;
                                                            }
                                                            $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                                        }
                                                        break;
                                                    }
                                                }
                                            } else {
                                                $t_temp = $working_data[$r][$i] + $working_data[$r][$i+1];
                                                $s_temp = $t_temp + $working_data[$r][$i+2];    // ����ĶȲ�̣
                                                $query = "SELECT * FROM user_detailes WHERE uid='{$uid[$t]}' AND (class LIKE '8%' OR class LIKE '9%' OR class LIKE '10%' OR class LIKE '11%' OR class LIKE '12%')";
                                                $res_chk = array();
                                                if ( getResult($query, $res_chk) > 0 ) {    // 8�鿦�ʾ� �Ķȥ����å�̵��
                                                    if ($working_data[$r][4] == '�Ҳ�' || $working_data[$r][4] == 'Ǥ��') {
                                                        $listTable .= "<td class='winbox' align='right' nowrap>---</td>\n";
                                                    } elseif ($t_temp == '000000') {
                                                        $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                                    } else {
                                                        $hour = floor($t_temp / 60);
                                                        $minutes = $t_temp%60;
                                                        if($hour == 0) {
                                                            $hour = '0';
                                                        }
                                                        if($minutes == 0) {
                                                            $minutes = '00';
                                                        } else if($minutes < 10) {
                                                            $minutes = '0' . $minutes;
                                                        }
                                                        $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                    }
                                                    break;
                                                } elseif($sid_t[$t] == 19) {
                                                    if ($working_data[$r][6] >= '1830') {
                                                        if ($working_data[$r][4] == '�Ҳ�' || $working_data[$r][4] == 'Ǥ��') {
                                                            $listTable .= "<td class='winbox' align='right' nowrap>---</td>\n";
                                                        } elseif ($t_temp == '000000') {
                                                            $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>----</td>\n";
                                                        } else {
                                                            // �ĶȻ��ַ׻��ʼ»��֡�
                                                            $deteObj = new DateTime($working_data[$r][6]);
                                                            $ceil_num = floor(sprintf('%d', $deteObj->format('i')) / 30) * 30;
                                                            $ceil_hour = $deteObj->format('H');
                                                            $have = $ceil_hour.sprintf( '%02d', $ceil_num);
                                                            $end = '2017-05-17 ' . substr($have, 0, 2) . ":" . substr($have, 2, 2) . ":00";
                                                            $startSec = strtotime('2017-05-17 18:00:00');
                                                            $endSec   = strtotime($end);
                                                            
                                                            $r_temp = ($endSec - $startSec)/60;
                                                            
                                                            $hour_r = floor($r_temp / 60);
                                                            $minutes_r = $r_temp%60;
                                                            if($hour_r == 0) {
                                                                $hour_r = '0';
                                                            }
                                                            if($minutes_r == 0) {
                                                                $minutes_r = '00';
                                                            } else if($minutes_r < 10) {
                                                                $minutes_r = '0' . $minutes_r;
                                                            }
                                                                
                                                            // �ĶȻ��ַ׻��ʿ���ʬ��
                                                            $hour = floor($t_temp / 60);
                                                            $minutes = $t_temp%60;
                                                            if($hour == 0) {
                                                                $hour = '0';
                                                            }
                                                            if($minutes == 0) {
                                                                $minutes = '00';
                                                            } else if($minutes < 10) {
                                                                $minutes = '0' . $minutes;
                                                            }
                                                            
                                                            // ����ĶȲ�̣
                                                            $hour_s = floor($s_temp / 60);
                                                            $minutes_s = $s_temp%60;
                                                            if($hour_s == 0) {
                                                                $hour_s = '0';
                                                            }
                                                            if($minutes_s == 0) {
                                                                $minutes_s = '00';
                                                            } else if($minutes_s < 10) {
                                                                $minutes_s = '0' . $minutes_s;
                                                            }
                                                            
                                                            //if ($hour_r == $hour) {
                                                            if ($hour_r == $hour_s) {
                                                                //if ($minutes_r == $minutes) {
                                                                if ($minutes_r == $minutes_s) {
                                                                    $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                                } else {
                                                                    $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>". $hour .":". $minutes ."</td>\n";
                                                                }
                                                            } else {
                                                                    $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>". $hour .":". $minutes ."</td>\n";
                                                            }
                                                        }
                                                        break;
                                                    } else {
                                                        if ($working_data[$r][4] == '�Ҳ�' || $working_data[$r][4] == 'Ǥ��') {
                                                            $listTable .= "<td class='winbox' align='right' nowrap>---</td>\n";
                                                        } elseif ($t_temp == '000000') {
                                                            $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                                        } else {
                                                            $hour = floor($t_temp / 60);
                                                            $minutes = $t_temp%60;
                                                            if($hour == 0) {
                                                                $hour = '0';
                                                            }
                                                            if($minutes == 0) {
                                                                $minutes = '00';
                                                            } else if($minutes < 10) {
                                                                $minutes = '0' . $minutes;
                                                            }
                                                                $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                        }
                                                        break;
                                                    }
                                                } else {
                                                    if ($working_data[$r][6] >= '1800') {
                                                        if ($working_data[$r][4] == '�Ҳ�' || $working_data[$r][4] == 'Ǥ��') {
                                                            $listTable .= "<td class='winbox' align='right' nowrap>---</td>\n";
                                                        } elseif ($t_temp == '000000') {
                                                            $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>----</td>\n";
                                                        } else {
                                                            // �ĶȻ��ַ׻��ʼ»��֡�
                                                            $deteObj = new DateTime($working_data[$r][6]);
                                                            $ceil_num = floor(sprintf('%d', $deteObj->format('i')) / 30) * 30;
                                                            $ceil_hour = $deteObj->format('H');
                                                            $have = $ceil_hour.sprintf( '%02d', $ceil_num);
                                                            if ($have >= '0000') {
                                                                $end = '2017-05-17 ' . substr($have, 0, 2) . ":" . substr($have, 2, 2) . ":00";
                                                            } else {
                                                                $end = '2017-05-18 ' . substr($have, 0, 2) . ":" . substr($have, 2, 2) . ":00";
                                                            }
                                                            $startSec = strtotime('2017-05-17 17:30:00');
                                                            $endSec   = strtotime($end);
                                                            
                                                            $r_temp = ($endSec - $startSec)/60;
                                                            
                                                            $hour_r = floor($r_temp / 60);
                                                            $minutes_r = $r_temp%60;
                                                            if($hour_r == 0) {
                                                                $hour_r = '0';
                                                            }
                                                            if($minutes_r == 0) {
                                                                $minutes_r = '00';
                                                            } else if($minutes_r < 10) {
                                                                $minutes_r = '0' . $minutes_r;
                                                            }
                                                                
                                                            // �ĶȻ��ַ׻��ʿ���ʬ��
                                                            $hour = floor($t_temp / 60);
                                                            $minutes = $t_temp%60;
                                                            if($hour == 0) {
                                                                $hour = '0';
                                                            }
                                                            if($minutes == 0) {
                                                                $minutes = '00';
                                                            } else if($minutes < 10) {
                                                                $minutes = '0' . $minutes;
                                                            }
                                                            // ����ĶȲ�̣
                                                            $hour_s = floor($s_temp / 60);
                                                            $minutes_s = $s_temp%60;
                                                            if($hour_s == 0) {
                                                                $hour_s = '0';
                                                            }
                                                            if($minutes_s == 0) {
                                                                $minutes_s = '00';
                                                            } else if($minutes_s < 10) {
                                                                $minutes_s = '0' . $minutes_s;
                                                            }
                                                            
                                                            //if ($hour_r == $hour) {
                                                            if ($hour_r == $hour_s) {
                                                                //if ($minutes_r == $minutes) {
                                                                if ($minutes_r == $minutes_s) {
                                                                    $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                                } else {
                                                                    $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>". $hour .":". $minutes ."</td>\n";
                                                                }
                                                            } else {
                                                                    $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>". $hour .":". $minutes ."</td>\n";
                                                            }
                                                        }
                                                        break;
                                                    } else {
                                                        if ($working_data[$r][4] == '�Ҳ�' || $working_data[$r][4] == 'Ǥ��') {
                                                            $listTable .= "<td class='winbox' align='right' nowrap>---</td>\n";
                                                        } elseif ($t_temp == '000000') {
                                                            $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                                        } else {
                                                            $hour = floor($t_temp / 60);
                                                            $minutes = $t_temp%60;
                                                            if($hour == 0) {
                                                                $hour = '0';
                                                            }
                                                            if($minutes == 0) {
                                                                $minutes = '00';
                                                            } else if($minutes < 10) {
                                                                $minutes = '0' . $minutes;
                                                            }
                                                                $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                        }
                                                        break;
                                                    }
                                                }
                                            }
                                        case 11:                                        // ����Ķ�
                                            if ($working_data[$r][23] == '��') {
                                                if ($working_data[$r][$i] == '000000') {
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                                } else {
                                                    $hour = floor($working_data[$r][$i] / 60);
                                                    $minutes = $working_data[$r][$i]%60;
                                                    if($hour == 0) {
                                                        $hour = '0';
                                                    }
                                                    if($minutes == 0) {
                                                        $minutes = '00';
                                                    } else if($minutes < 10) {
                                                        $minutes = '0' . $minutes;
                                                    }
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                                }
                                                break;
                                            } else {
                                                if ($working_data[$r][$i] == '000000') {
                                                        $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                                } else {
                                                    $hour = floor($working_data[$r][$i] / 60);
                                                    $minutes = $working_data[$r][$i]%60;
                                                    if($hour == 0) {
                                                        $hour = '0';
                                                    }
                                                    if($minutes == 0) {
                                                        $minutes = '00';
                                                    } else if($minutes < 10) {
                                                        $minutes = '0' . $minutes;
                                                    }
                                                    $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                }
                                                break;
                                            }
                                        case 12:                                        // �ٽл���
                                            if ($working_data[$r][23] == '��') {
                                                if ($working_data[$r][$i] == '000000') {
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                                } else {
                                                    $howork_num += 1;                       // �������ǹ郎����еٽ������ܣ�
                                                    $hour = floor($working_data[$r][$i] / 60);
                                                    $minutes = $working_data[$r][$i]%60;
                                                    if($hour == 0) {
                                                        $hour = '0';
                                                    }
                                                    if($minutes == 0) {
                                                        $minutes = '00';
                                                    } else if($minutes < 10) {
                                                        $minutes = '0' . $minutes;
                                                    }
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                                }
                                                break;
                                            } else {
                                                if ($working_data[$r][$i] == '000000') {
                                                        $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                                } else {
                                                    $howork_num += 1;                       // �������ǹ郎����еٽ������ܣ�
                                                    $hour = floor($working_data[$r][$i] / 60);
                                                    $minutes = $working_data[$r][$i]%60;
                                                    if($hour == 0) {
                                                        $hour = '0';
                                                    }
                                                    if($minutes == 0) {
                                                        $minutes = '00';
                                                    } else if($minutes < 10) {
                                                        $minutes = '0' . $minutes;
                                                    }
                                                    $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                }
                                                break;
                                            }
                                        case 13:                                        // �ٽлĶ�
                                            if ($working_data[$r][23] == '��') {
                                                if ($working_data[$r][$i] == '000000') {
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                                } else {
                                                    $hour = floor($working_data[$r][$i] / 60);
                                                    $minutes = $working_data[$r][$i]%60;
                                                    if($hour == 0) {
                                                        $hour = '0';
                                                    }
                                                    if($minutes == 0) {
                                                        $minutes = '00';
                                                    } else if($minutes < 10) {
                                                        $minutes = '0' . $minutes;
                                                    }
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                                }
                                                break;
                                            } else {
                                                if ($working_data[$r][$i] == '000000') {
                                                        $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                                } else {
                                                    $hour = floor($working_data[$r][$i] / 60);
                                                    $minutes = $working_data[$r][$i]%60;
                                                    if($hour == 0) {
                                                        $hour = '0';
                                                    }
                                                    if($minutes == 0) {
                                                        $minutes = '00';
                                                    } else if($minutes < 10) {
                                                        $minutes = '0' . $minutes;
                                                    }
                                                    $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                }
                                                break;
                                            }
                                        case 14:                                        // �ٽп���
                                            if ($working_data[$r][23] == '��') {
                                                if ($working_data[$r][$i] == '000000') {
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                                } else {
                                                    $hour = floor($working_data[$r][$i] / 60);
                                                    $minutes = $working_data[$r][$i]%60;
                                                    if($hour == 0) {
                                                        $hour = '0';
                                                    }
                                                    if($minutes == 0) {
                                                        $minutes = '00';
                                                    } else if($minutes < 10) {
                                                        $minutes = '0' . $minutes;
                                                    }
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                                }
                                                break;
                                            } else {
                                            }
                                        case 15:                                        // ˡ�����
                                            if ($working_data[$r][23] == '��') {
                                                if ($working_data[$r][$i] == '000000') {
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                                } else {
                                                    $hour = floor($working_data[$r][$i] / 60);
                                                    $minutes = $working_data[$r][$i]%60;
                                                    $howork_num += 1;                       // ˡ����֤��ǹ郎����еٽ������ܣ�
                                                    if($hour == 0) {
                                                        $hour = '0';
                                                    }
                                                    if($minutes == 0) {
                                                        $minutes = '00';
                                                    } else if($minutes < 10) {
                                                        $minutes = '0' . $minutes;
                                                    }
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                                }
                                                break;
                                            } else {
                                                if ($working_data[$r][$i] == '000000') {
                                                        $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                                } else {
                                                    $hour = floor($working_data[$r][$i] / 60);
                                                    $minutes = $working_data[$r][$i]%60;
                                                    $howork_num += 1;                       // ˡ����֤��ǹ郎����еٽ������ܣ�
                                                    if($hour == 0) {
                                                        $hour = '0';
                                                    }
                                                    if($minutes == 0) {
                                                        $minutes = '00';
                                                    } else if($minutes < 10) {
                                                        $minutes = '0' . $minutes;
                                                    }
                                                    $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                }
                                                break;
                                            }
                                        case 16:                                        // ˡ��Ķ�
                                            if ($working_data[$r][23] == '��') {
                                                if ($working_data[$r][$i] == '000000') {
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                                } else {
                                                    $hour = floor($working_data[$r][$i] / 60);
                                                    $minutes = $working_data[$r][$i]%60;
                                                    if($hour == 0) {
                                                        $hour = '0';
                                                    }
                                                    if($minutes == 0) {
                                                        $minutes = '00';
                                                    } else if($minutes < 10) {
                                                        $minutes = '0' . $minutes;
                                                    }
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                                }
                                                break;
                                            } else {
                                                if ($working_data[$r][$i] == '000000') {
                                                        $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                                } else {
                                                    $hour = floor($working_data[$r][$i] / 60);
                                                    $minutes = $working_data[$r][$i]%60;
                                                    if($hour == 0) {
                                                        $hour = '0';
                                                    }
                                                    if($minutes == 0) {
                                                        $minutes = '00';
                                                    } else if($minutes < 10) {
                                                        $minutes = '0' . $minutes;
                                                    }
                                                    $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                }
                                                break;
                                            }
                                        case 17:                                        // �ٹ�����
                                            if ($working_data[$r][23] == '��') {
                                                if ($working_data[$r][$i] == '000000') {
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                                } else {
                                                    $hour = floor($working_data[$r][$i] / 60);
                                                    $minutes = $working_data[$r][$i]%60;
                                                    if($hour == 0) {
                                                        $hour = '0';
                                                    }
                                                    if($minutes == 0) {
                                                        $minutes = '00';
                                                    } else if($minutes < 10) {
                                                        $minutes = '0' . $minutes;
                                                    }
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                                }
                                                break;
                                            } else {
                                                if ($working_data[$r][$i] == '000000') {
                                                        $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                                } else {
                                                    $hour = floor($working_data[$r][$i] / 60);
                                                    $minutes = $working_data[$r][$i]%60;
                                                    if($hour == 0) {
                                                        $hour = '0';
                                                    }
                                                    if($minutes == 0) {
                                                        $minutes = '00';
                                                    } else if($minutes < 10) {
                                                        $minutes = '0' . $minutes;
                                                    }
                                                    $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                }
                                                break;
                                            }
                                        case 23:                                        // �����ǧ
                                            /*
                                            if ($working_data[$r][$i] == '��') {
                                                $listTable .= "<td class='winbox' align='center' bgcolor='white' nowrap>" . $working_data[$r][$i] ."</td>\n";
                                            } else {
                                                $listTable .= "<td class='winbox' align='center' nowrap><font color='red'>". $working_data[$r][$i] ."</font></td>\n";
                                            }
                                            */
                                            break;
                                        default:                                          
                                            break;
                                    }
                                }
                                
                                $listTable .= "    </tr>\n";
                            }
                        } else {
                            for ($r=0; $r<$request->get('rows'); $r++) {                                        // �쥳���ɿ�ʬ���֤�
                                for ($i=0; $i<$request->get('num'); $i++) {
                                    switch ($i) {
                                        case 5:
                                            if ($working_data[$r][$i] != '0000') {
                                                $work_num += 1;                                 // �жл��郎�ǹ蘆��Ƥ���нж������ܣ�
                                            }
                                            break;
                                        case 12:
                                            if ($working_data[$r][$i] != '000000') {
                                                $howork_num += 1;                               // �ٽл��֤�����еٽ������ܣ�
                                            }
                                            break;
                                        case 15:
                                            if ($working_data[$r][$i] != '000000') {
                                                $howork_num += 1;                               // ˡ����֤�����еٽ������ܣ�
                                            }
                                            break;
                                        default:
                                            break;
                                    }
                                }
                            }
                        }
                        // ���ץǡ�����ʬ����
                        $listTable = $this->getViewHTMLTotal($request, $listTable, $working_data, $work_num, $howork_num, $uid[$t], $chek_flg);
                    }
                }
            } else {        // �ĶȰʳ�
                $working_data = array();
                $working_data = $this->getTimeProData($request, $uid, $t);      // ������ץ�ǡ����μ������Ѵ�
                if ($uid[$t] == '914737') {                                     // �и��ԤμҰ��ֹ���ᤷ
                    $uid[$t] = '014737';
                } else if ($uid[$t] == '920273') {
                    $uid[$t] = '020273';
                }
                $work_num    = $request->get('work_num');
                $howork_num  = $request->get('howork_num');
                if ($request->get('targetSection') == '-5') {   // ���顼�Τ�ɽ���ξ��
                    if ($this->getErrorCheck($request, $uid, $t, $working_data, $sid_t)) {
                        continue;                                                   // ���顼�����å��ˤ�����ʤ�������Ф�
                    }
                }
                $listTable .= "<BR><CENTER>\n";
                $listTable .= "<U><font size='2'>�Ұ�No.��". $uid[$t] ."����".  $this->getUserName($uid[$t]) ."������°��". $s_name[$t] ."�����������֡���". format_date($request->get('targetDateStr')) ."������". format_date($request->get('targetDateEnd')) ."</U>\n";
                $listTable .= "</CENTER>\n";
                $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
                $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
                $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='2'>\n";
                if ($request->get('rows') <= 0) {
                    $listTable .= "    <tr>\n";
                    $listTable .= "        <td colspan='11' width='100%' align='center' class='winbox'>�ǡ���������ޤ���</td>\n";
                    $listTable .= "    </tr>\n";
                    $listTable .= "</table>\n";
                    $listTable .= "    </td></tr>\n";
                    $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
                } else {
                    if ($request->get('formal') == 'details') {                 // �ꥹ�ȷ����Υ����å�
                        $listTable .= "    <tr>\n";                                 // ���٥ǡ�����ɽ��
                        $listTable .= "        <th class='winbox'>����</th>\n";
                        $listTable .= "        <th class='winbox'>����</th>\n";
                        $listTable .= "        <th class='winbox'>����<BR>���</th>\n";
                        $listTable .= "        <th class='winbox'>�Ժ�</th>\n";
                        $listTable .= "        <th class='winbox'>�ж�<BR>����</th>\n";
                        $listTable .= "        <th class='winbox'>���<BR>����</th>\n";
                        $listTable .= "        <th class='winbox'>����<BR>����</th>\n";
                        $listTable .= "        <th class='winbox'>��Ĺ<BR>����</th>\n";
                        $listTable .= "        <th class='winbox'>���<BR>�Ķ�</th>\n";
                        $listTable .= "        <th class='winbox'>����<BR>�Ķ�</th>\n";
                        $listTable .= "        <th class='winbox'>�ٽ�<BR>����</th>\n";
                        $listTable .= "        <th class='winbox'>�ٽ�<BR>�Ķ�</th>\n";
                        $listTable .= "        <th class='winbox'>�ٽ�<BR>����</th>\n";
                        $listTable .= "        <th class='winbox'>ˡ��<BR>����</th>\n";
                        $listTable .= "        <th class='winbox'>ˡ��<BR>�Ķ�</th>\n";
                        $listTable .= "        <th class='winbox'>�ٹ�<BR>����</th>\n";
                        //$listTable .= "        <th class='winbox'>����<BR>��ǧ</th>\n";
                        $listTable .= "    </tr>\n";
                        for ($r=0; $r<$request->get('rows'); $r++) {        // �쥳���ɿ�ʬ���֤�
                            if ($request->get('targetSection') == '-5') {   // ���顼�Τ�ɽ���ξ��
                                $error_flg = '';                                // ���顼�ե饰�����
                                if ($working_data[$r][1] != $today_ym) {        // �����ʳ�
                                    if ($working_data[$r][5] == '0000') {           // �ж��ǹ�ʤ�
                                        if ($working_data[$r][6] == '0000') {       // ����ǹ�ʤ�
                                            if ($working_data[$r][3] == '����' || $working_data[$r][3] == 'ˡ��' || $working_data[$r][3] == '�ٶ�') {
                                                continue;                           // �����Ǥ����ɽ�����ʤ�
                                            } else {                                // �����Ǥ�̵����
                                                if ($working_data[$r][4] == '��') { // �Ժ���ͳ������
                                                } else {
                                                    continue;                       // ����Ǥʤ����ɽ�����ʤ�
                                                }
                                            }
                                        } else {    // �ж��ǹ郎�ʤ�����Ф�����Х��顼�ΰ١�ɽ��
                                            
                                        }
                                    } else {        // �ж��ǹ濫��
                                        if ($working_data[$r][6] == '0000') {
                                                    // �ж��ǹ濫��ǡ�����ǹ�ʤ��ϥ��顼�ΰ١�ɽ��
                                        } else {    // �ж��ǹ濫��ǡ�����ǹ濫��ξ��ϱ�Ĺ�Ķȥ����å�
                                            // ��Ĺ�����å�
                                            $query = "SELECT * FROM user_detailes WHERE uid='{$uid[$t]}' AND pid=5";
                                            $res_chk = array();
                                            if ( getResult($query, $res_chk) > 0 ) {    // �ѡ��� ��Ĺ�����å�
                                                if ($working_data[$r][6] >= '1645') {   // ��Ĺ30ʬ�ʾ� �����å�
                                                    if ($working_data[$r][8] == '000000') {    // ��Ĺ�ǹ�ʤ��ʤΤǥ��顼ɽ��
                                                        $error_flg = '1';
                                                    } else {
                                                        $t_temp = $working_data[$r][8];
                                                        // ��Ĺ���ַ׻��ʼ»��֡�
                                                        $deteObj = new DateTime($working_data[$r][6]);
                                                        $ceil_num = floor(sprintf('%d', $deteObj->format('i')) / 15) * 15;
                                                        $ceil_hour = $deteObj->format('H');
                                                        $have = $ceil_hour.sprintf( '%02d', $ceil_num);
                                                        $end = '2017-05-17 ' . substr($have, 0, 2) . ":" . substr($have, 2, 2) . ":00";
                                                        $startSec = strtotime('2017-05-17 16:15:00');
                                                        $endSec   = strtotime($end);
                                                        
                                                        $r_temp = ($endSec - $startSec)/60;
                                                        
                                                        if($r_temp>=60) {   // ��Ĺ�ϣ����֤ޤǤʤΤ�Ĵ��
                                                            $r_temp = 60;
                                                        } elseif($r_temp>=30) {
                                                            $r_temp = 30;
                                                        }
                                                        $hour_r = floor($r_temp / 60);
                                                        $minutes_r = $r_temp%60;
                                                        if($hour_r == 0) {
                                                            $hour_r = '0';
                                                        }
                                                        if($minutes_r == 0) {
                                                            $minutes_r = '00';
                                                        } else if($minutes_r < 10) {
                                                            $minutes_r = '0' . $minutes_r;
                                                        }
                                                        
                                                        // ��Ĺ���ַ׻��ʿ���ʬ��
                                                        $hour = floor($t_temp / 60);
                                                        $minutes = $t_temp%60;
                                                        if($hour == 0) {
                                                            $hour = '0';
                                                        }
                                                        if($minutes == 0) {
                                                            $minutes = '00';
                                                        } else if($minutes < 10) {
                                                            $minutes = '0' . $minutes;
                                                        }
                                                        if ($hour_r == $hour) {
                                                            if ($minutes_r == $minutes) {   // ���֤�ʬ������ ���顼�ʤ��ʤΤ���ɽ��
                                                                //continue;
                                                            } else {    // ʬ���԰��פʤΤǥ��顼ɽ��
                                                                $error_flg = '1';
                                                            }
                                                        } else {    // ���֤��԰��פʤΤǥ��顼ɽ��
                                                            $error_flg = '1';
                                                        }
                                                    }
                                                } else {                                // ��Ĺ̵�� �����å�̵��
                                                    //continue;
                                                }
                                            } else {                                    // �ѡ��Ȱʳ� ��Ĺ�����å�̵��
                                                //continue;
                                            }
                                            // �Ķȥ����å�
                                            $t_temp = $working_data[$r][9] + $working_data[$r][10];
                                            $s_temp = $t_temp + $working_data[$r][11];    // ����ĶȲ�̣
                                            $query = "SELECT * FROM user_detailes WHERE uid='{$uid[$t]}' AND (class LIKE '8%' OR class LIKE '9%' OR class LIKE '10%' OR class LIKE '11%' OR class LIKE '12%')";
                                            $res_chk = array();
                                            if ( getResult($query, $res_chk) > 0 ) {    // 8�鿦�ʾ� �Ķȥ����å�̵�� ��ɽ��
                                                continue;
                                            } elseif($sid_t[$t] == 19) {                // ���ɤξ��
                                                if ($working_data[$r][6] >= '1830') {
                                                    if ($working_data[$r][4] == '�Ҳ�' || $working_data[$r][4] == 'Ǥ��') {
                                                        if ($error_flg == '') {             // ��Ĺ���顼OFF�ξ�����ɽ��
                                                            continue;                       // 18:30�ʹߤǤ�Ұ����Ǥ�ո�������ɽ��
                                                        }
                                                    } elseif ($t_temp == '000000') {    
                                                        // 18:30�ʹߤǻĶȤ��ǹ郎�ʤ����ɽ��
                                                    } else {
                                                        // �ĶȻ��ַ׻��ʼ»��֡�
                                                        $deteObj = new DateTime($working_data[$r][6]);
                                                        $ceil_num = floor(sprintf('%d', $deteObj->format('i')) / 30) * 30;
                                                        $ceil_hour = $deteObj->format('H');
                                                        $have = $ceil_hour.sprintf( '%02d', $ceil_num);
                                                        $end = '2017-05-17 ' . substr($have, 0, 2) . ":" . substr($have, 2, 2) . ":00";
                                                        $startSec = strtotime('2017-05-17 18:00:00');
                                                        $endSec   = strtotime($end);
                                                        
                                                        $r_temp = ($endSec - $startSec)/60;
                                                        
                                                        $hour_r = floor($r_temp / 60);
                                                        $minutes_r = $r_temp%60;
                                                        if($hour_r == 0) {
                                                            $hour_r = '0';
                                                        }
                                                        if($minutes_r == 0) {
                                                            $minutes_r = '00';
                                                        } else if($minutes_r < 10) {
                                                            $minutes_r = '0' . $minutes_r;
                                                        }
                                                        
                                                        // �ĶȻ��ַ׻��ʿ���ʬ��
                                                        $hour = floor($t_temp / 60);
                                                        $minutes = $t_temp%60;
                                                        if($hour == 0) {
                                                            $hour = '0';
                                                        }
                                                        if($minutes == 0) {
                                                            $minutes = '00';
                                                        } else if($minutes < 10) {
                                                            $minutes = '0' . $minutes;
                                                        }
                                                        // ����ĶȲ�̣
                                                        $hour_s = floor($s_temp / 60);
                                                        $minutes_s = $s_temp%60;
                                                        if($hour_s == 0) {
                                                            $hour_s = '0';
                                                        }
                                                        if($minutes_s == 0) {
                                                            $minutes_s = '00';
                                                        } else if($minutes_s < 10) {
                                                            $minutes_s = '0' . $minutes_s;
                                                        }
                                                        if ($hour_r == $hour_s) {
                                                            if ($minutes_r == $minutes_s) {
                                                                if ($error_flg == '') {             // ��Ĺ���顼OFF�ξ�����ɽ��
                                                                    continue;               // ����ȼ»ĶȻ��֤����פ��Ƥ���Х��顼�ǤϤʤ��Τ���ɽ��
                                                                }
                                                            } else {
                                                                // ���֤�ʬ�����ʤ��Τǥ��顼�ΰ١�ɽ��
                                                            }
                                                        } else {
                                                            // ���֤����ʤ��Τǥ��顼�ΰ١�ɽ��
                                                        }
                                                    }
                                                } else {
                                                    if ($error_flg == '') {             // ��Ĺ���顼OFF�ξ�����ɽ��
                                                        continue;                           // 18:30���ϻĶȤǤϤʤ��١���ɽ��
                                                    }
                                                }
                                            } else {                                    // ���ɰʳ��ΰ��̼Ұ�
                                                if ($working_data[$r][6] >= '1800') {
                                                    if ($working_data[$r][4] == '�Ҳ�' || $working_data[$r][4] == 'Ǥ��') {
                                                        if ($error_flg == '') {             // ��Ĺ���顼OFF�ξ�����ɽ��
                                                            continue;                       // 18:00�ʹߤǤ�Ұ����Ǥ�ո�������ɽ��
                                                        }
                                                    } elseif ($t_temp == '000000') {    
                                                        // 18:00�ʹߤǻĶȤ��ǹ郎�ʤ����ɽ��
                                                    } else {
                                                        // �ĶȻ��ַ׻��ʼ»��֡�
                                                        $deteObj = new DateTime($working_data[$r][6]);
                                                        $ceil_num = floor(sprintf('%d', $deteObj->format('i')) / 30) * 30;
                                                        $ceil_hour = $deteObj->format('H');
                                                        $have = $ceil_hour.sprintf( '%02d', $ceil_num);
                                                        $end = '2017-05-17 ' . substr($have, 0, 2) . ":" . substr($have, 2, 2) . ":00";
                                                        $startSec = strtotime('2017-05-17 17:30:00');
                                                        $endSec   = strtotime($end);
                                                        
                                                        $r_temp = ($endSec - $startSec)/60;
                                                        
                                                        $hour_r = floor($r_temp / 60);
                                                        $minutes_r = $r_temp%60;
                                                        if($hour_r == 0) {
                                                            $hour_r = '0';
                                                        }
                                                        if($minutes_r == 0) {
                                                            $minutes_r = '00';
                                                        } else if($minutes_r < 10) {
                                                            $minutes_r = '0' . $minutes_r;
                                                        }
                                                        
                                                        // �ĶȻ��ַ׻��ʿ���ʬ��
                                                        $hour = floor($t_temp / 60);
                                                        $minutes = $t_temp%60;
                                                        if($hour == 0) {
                                                            $hour = '0';
                                                        }
                                                        if($minutes == 0) {
                                                            $minutes = '00';
                                                        } else if($minutes < 10) {
                                                            $minutes = '0' . $minutes;
                                                        }
                                                        // ����ĶȲ�̣
                                                        $hour_s = floor($s_temp / 60);
                                                        $minutes_s = $s_temp%60;
                                                        if($hour_s == 0) {
                                                            $hour_s = '0';
                                                        }
                                                        if($minutes_s == 0) {
                                                            $minutes_s = '00';
                                                        } else if($minutes_s < 10) {
                                                            $minutes_s = '0' . $minutes_s;
                                                        }
                                                        if ($hour_r == $hour_s) {
                                                            if ($minutes_r == $minutes_s) {
                                                                if ($error_flg == '') {             // ��Ĺ���顼OFF�ξ�����ɽ��
                                                                    continue;               // ����ȼ»ĶȻ��֤����פ��Ƥ���Х��顼�ǤϤʤ��Τ���ɽ��
                                                                }
                                                            } else {
                                                                // ���֤�ʬ�����ʤ��Τǥ��顼�ΰ١�ɽ��
                                                            }
                                                        } else {
                                                            // ���֤����ʤ��Τǥ��顼�ΰ١�ɽ��
                                                        }
                                                    }
                                                } else {
                                                    if ($error_flg == '') {             // ��Ĺ���顼OFF�ξ�����ɽ��
                                                        continue;                           // 18:00���ϻĶȤǤϤʤ��١���ɽ��
                                                    }
                                                }
                                            }
                                        }
                                    }
                                } else {                    // �����ξ��
                                    if ($working_data[$r][5] == '0000') {           // �ж��ǹ�ʤ�
                                        if ($working_data[$r][6] == '0000') {       // ����ǹ�ʤ�
                                            if ($working_data[$r][3] == '����' || $working_data[$r][3] == 'ˡ��' || $working_data[$r][3] == '�ٶ�') {
                                                continue;                           // �����Ǥ����ɽ�����ʤ�
                                            } else {                                // �����Ǥ�̵����
                                                if ($working_data[$r][4] == '��') { // �Ժ���ͳ������
                                                } else {
                                                    continue;                       // ����Ǥʤ����ɽ�����ʤ�
                                                }
                                            }
                                        } else {    // �ж��ǹ郎�ʤ�����Ф�����Х��顼�ΰ١�ɽ��
                                            
                                        }
                                    } else {                                        // �ж��ǹ濫��
                                        if ($working_data[$r][6] == '0000') {       // ����ǹ�ʤ�
                                            continue;                               // �����Ͻ�����ǹ���ޤ����Ф�
                                        } else {    // �ж��ǹ郎���ꡢ��Ф⤹�Ǥˤ���Х��顼�����å�
                                            // ��Ĺ�����å�
                                            $query = "SELECT * FROM user_detailes WHERE uid='{$uid[$t]}' AND pid=5";
                                            $res_chk = array();
                                            if ( getResult($query, $res_chk) > 0 ) {    // �ѡ��� ��Ĺ�����å�
                                                if ($working_data[$r][6] >= '1645') {   // ��Ĺ30ʬ�ʾ� �����å�
                                                    if ($working_data[$r][8] == '000000') {    // ��Ĺ�ǹ�ʤ��ʤΤǥ��顼ɽ��
                                                        $error_flg = '1';
                                                    } else {
                                                        $t_temp = $working_data[$r][8];
                                                        // ��Ĺ���ַ׻��ʼ»��֡�
                                                        $deteObj = new DateTime($working_data[$r][6]);
                                                        $ceil_num = floor(sprintf('%d', $deteObj->format('i')) / 15) * 15;
                                                        $ceil_hour = $deteObj->format('H');
                                                        $have = $ceil_hour.sprintf( '%02d', $ceil_num);
                                                        $end = '2017-05-17 ' . substr($have, 0, 2) . ":" . substr($have, 2, 2) . ":00";
                                                        $startSec = strtotime('2017-05-17 16:15:00');
                                                        $endSec   = strtotime($end);
                                                        
                                                        $r_temp = ($endSec - $startSec)/60;
                                                        
                                                        if($r_temp>=60) {   // ��Ĺ�ϣ����֤ޤǤʤΤ�Ĵ��
                                                            $r_temp = 60;
                                                        } elseif($r_temp>=30) {
                                                            $r_temp = 30;
                                                        }
                                                        $hour_r = floor($r_temp / 60);
                                                        $minutes_r = $r_temp%60;
                                                        if($hour_r == 0) {
                                                            $hour_r = '0';
                                                        }
                                                        if($minutes_r == 0) {
                                                            $minutes_r = '00';
                                                        } else if($minutes_r < 10) {
                                                            $minutes_r = '0' . $minutes_r;
                                                        }
                                                        
                                                        // ��Ĺ���ַ׻��ʿ���ʬ��
                                                        $hour = floor($t_temp / 60);
                                                        $minutes = $t_temp%60;
                                                        if($hour == 0) {
                                                            $hour = '0';
                                                        }
                                                        if($minutes == 0) {
                                                            $minutes = '00';
                                                        } else if($minutes < 10) {
                                                            $minutes = '0' . $minutes;
                                                        }
                                                        if ($hour_r == $hour) {
                                                            if ($minutes_r == $minutes) {   // ���֤�ʬ������ ���顼�ʤ��ʤΤ���ɽ��
                                                                //continue;
                                                            } else {    // ʬ���԰��פʤΤǥ��顼ɽ��
                                                                $error_flg = '1';
                                                            }
                                                        } else {    // ���֤��԰��פʤΤǥ��顼ɽ��
                                                            $error_flg = '1';
                                                        }
                                                    }
                                                } else {                                // ��Ĺ̵�� �����å�̵��
                                                    //continue;
                                                }
                                            } else {                                    // �ѡ��Ȱʳ� ��Ĺ�����å�̵��
                                                //continue;
                                            }
                                            // �Ķȥ����å�
                                            $t_temp = $working_data[$r][9] + $working_data[$r][10];
                                            $s_temp = $t_temp + $working_data[$r][11];    // ����ĶȲ�̣
                                            $query = "SELECT * FROM user_detailes WHERE uid='{$uid[$t]}' AND (class LIKE '8%' OR class LIKE '9%' OR class LIKE '10%' OR class LIKE '11%' OR class LIKE '12%')";
                                            $res_chk = array();
                                            if ( getResult($query, $res_chk) > 0 ) {    // 8�鿦�ʾ� �Ķȥ����å�̵�� ��ɽ��
                                                continue;
                                            } elseif($sid_t[$t] == 19) {                // ���ɤξ��
                                                if ($working_data[$r][6] >= '1830') {
                                                    if ($working_data[$r][4] == '�Ҳ�' || $working_data[$r][4] == 'Ǥ��') {
                                                        if ($error_flg == '') {             // ��Ĺ���顼OFF�ξ�����ɽ��
                                                            continue;                       // 18:30�ʹߤǤ�Ұ����Ǥ�ո�������ɽ��
                                                        }
                                                    } elseif ($t_temp == '000000') {    
                                                        // 18:30�ʹߤǻĶȤ��ǹ郎�ʤ����ɽ��
                                                    } else {
                                                        // �ĶȻ��ַ׻��ʼ»��֡�
                                                        $deteObj = new DateTime($working_data[$r][6]);
                                                        $ceil_num = floor(sprintf('%d', $deteObj->format('i')) / 30) * 30;
                                                        $ceil_hour = $deteObj->format('H');
                                                        $have = $ceil_hour.sprintf( '%02d', $ceil_num);
                                                        $end = '2017-05-17 ' . substr($have, 0, 2) . ":" . substr($have, 2, 2) . ":00";
                                                        $startSec = strtotime('2017-05-17 18:00:00');
                                                        $endSec   = strtotime($end);
                                                        
                                                        $r_temp = ($endSec - $startSec)/60;
                                                        
                                                        $hour_r = floor($r_temp / 60);
                                                        $minutes_r = $r_temp%60;
                                                        if($hour_r == 0) {
                                                            $hour_r = '0';
                                                        }
                                                        if($minutes_r == 0) {
                                                            $minutes_r = '00';
                                                        } else if($minutes_r < 10) {
                                                            $minutes_r = '0' . $minutes_r;
                                                        }
                                                        
                                                        // �ĶȻ��ַ׻��ʿ���ʬ��
                                                        $hour = floor($t_temp / 60);
                                                        $minutes = $t_temp%60;
                                                        if($hour == 0) {
                                                            $hour = '0';
                                                        }
                                                        if($minutes == 0) {
                                                            $minutes = '00';
                                                        } else if($minutes < 10) {
                                                            $minutes = '0' . $minutes;
                                                        }
                                                        // ����ĶȲ�̣
                                                        $hour_s = floor($s_temp / 60);
                                                        $minutes_s = $s_temp%60;
                                                        if($hour_s == 0) {
                                                            $hour_s = '0';
                                                        }
                                                        if($minutes_s == 0) {
                                                            $minutes_s = '00';
                                                        } else if($minutes_s < 10) {
                                                            $minutes_s = '0' . $minutes_s;
                                                        }
                                                        if ($hour_r == $hour_s) {
                                                            if ($minutes_r == $minutes_s) {
                                                                if ($error_flg == '') {             // ��Ĺ���顼OFF�ξ�����ɽ��
                                                                    continue;               // ����ȼ»ĶȻ��֤����פ��Ƥ���Х��顼�ǤϤʤ��Τ���ɽ��
                                                                }
                                                            } else {
                                                                // ���֤�ʬ�����ʤ��Τǥ��顼�ΰ١�ɽ��
                                                            }
                                                        } else {
                                                            // ���֤����ʤ��Τǥ��顼�ΰ١�ɽ��
                                                        }
                                                    }
                                                } else {
                                                    if ($error_flg == '') {             // ��Ĺ���顼OFF�ξ�����ɽ��
                                                        continue;                           // 18:30���ϻĶȤǤϤʤ��١���ɽ��
                                                    }
                                                }
                                            } else {                                    // ���ɰʳ��ΰ��̼Ұ�
                                                if ($working_data[$r][6] >= '1800') {
                                                    if ($working_data[$r][4] == '�Ҳ�' || $working_data[$r][4] == 'Ǥ��') {
                                                        if ($error_flg == '') {             // ��Ĺ���顼OFF�ξ�����ɽ��
                                                            continue;                       // 18:00�ʹߤǤ�Ұ����Ǥ�ո�������ɽ��
                                                        }
                                                    } elseif ($t_temp == '000000') {    
                                                        // 18:00�ʹߤǻĶȤ��ǹ郎�ʤ����ɽ��
                                                    } else {
                                                        // �ĶȻ��ַ׻��ʼ»��֡�
                                                        $deteObj = new DateTime($working_data[$r][6]);
                                                        $ceil_num = floor(sprintf('%d', $deteObj->format('i')) / 30) * 30;
                                                        $ceil_hour = $deteObj->format('H');
                                                        $have = $ceil_hour.sprintf( '%02d', $ceil_num);
                                                        $end = '2017-05-17 ' . substr($have, 0, 2) . ":" . substr($have, 2, 2) . ":00";
                                                        $startSec = strtotime('2017-05-17 17:30:00');
                                                        $endSec   = strtotime($end);
                                                        
                                                        $r_temp = ($endSec - $startSec)/60;
                                                        
                                                        $hour_r = floor($r_temp / 60);
                                                        $minutes_r = $r_temp%60;
                                                        if($hour_r == 0) {
                                                            $hour_r = '0';
                                                        }
                                                        if($minutes_r == 0) {
                                                            $minutes_r = '00';
                                                        } else if($minutes_r < 10) {
                                                            $minutes_r = '0' . $minutes_r;
                                                        }
                                                        
                                                        // �ĶȻ��ַ׻��ʿ���ʬ��
                                                        $hour = floor($t_temp / 60);
                                                        $minutes = $t_temp%60;
                                                        if($hour == 0) {
                                                            $hour = '0';
                                                        }
                                                        if($minutes == 0) {
                                                            $minutes = '00';
                                                        } else if($minutes < 10) {
                                                            $minutes = '0' . $minutes;
                                                        }
                                                        // ����ĶȲ�̣
                                                        $hour_s = floor($s_temp / 60);
                                                        $minutes_s = $s_temp%60;
                                                        if($hour_s == 0) {
                                                            $hour_s = '0';
                                                        }
                                                        if($minutes_s == 0) {
                                                            $minutes_s = '00';
                                                        } else if($minutes_s < 10) {
                                                            $minutes_s = '0' . $minutes_s;
                                                        }
                                                        if ($hour_r == $hour_s) {
                                                            if ($minutes_r == $minutes_s) {
                                                                if ($error_flg == '') {             // ��Ĺ���顼OFF�ξ�����ɽ��
                                                                    continue;               // ����ȼ»ĶȻ��֤����פ��Ƥ���Х��顼�ǤϤʤ��Τ���ɽ��
                                                                }
                                                            } else {
                                                                // ���֤�ʬ�����ʤ��Τǥ��顼�ΰ١�ɽ��
                                                            }
                                                        } else {
                                                            // ���֤����ʤ��Τǥ��顼�ΰ١�ɽ��
                                                        }
                                                    }
                                                } else {
                                                    if ($error_flg == '') {             // ��Ĺ���顼OFF�ξ�����ɽ��
                                                        continue;                           // 18:00���ϻĶȤǤϤʤ��١���ɽ��
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            // ���顼�Τ�ɽ���ʳ�
                            $listTable .= "<tr>\n";
                            for ($i=0; $i<$request->get('num'); $i++) {
                                switch ($i) {
                                    case 1:                                         // ����
                                        if ($working_data[$r][23] == '��') {
                                            if ($working_data[$r][3] == '����') {
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap><font color='red'>". substr($working_data[$r][$i], 4, 2) . "/". substr($working_data[$r][$i], 6, 2) ."</font></td>\n";
                                                break;
                                            } elseif ($working_data[$r][3] == 'ˡ��') {
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap><font color='red'>". substr($working_data[$r][$i], 4, 2) . "/". substr($working_data[$r][$i], 6, 2) ."</font></td>\n";
                                                break;
                                            } elseif ($working_data[$r][3] == '�ٶ�') {
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap><font color='blue'>". substr($working_data[$r][$i], 4, 2) . "/". substr($working_data[$r][$i], 6, 2) ."</font></td>\n";
                                                break;
                                            } else {
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". substr($working_data[$r][$i], 4, 2) . "/". substr($working_data[$r][$i], 6, 2) ."</td>\n";
                                                break;
                                            }
                                        } else {
                                            if ($working_data[$r][3] == '����') {
                                                $listTable .= "<td class='winbox' align='right' nowrap><font color='red'>". substr($working_data[$r][$i], 4, 2) . "/". substr($working_data[$r][$i], 6, 2) ."</font></td>\n";
                                                break;
                                            } elseif ($working_data[$r][3] == 'ˡ��') {
                                                $listTable .= "<td class='winbox' align='right' nowrap><font color='red'>". substr($working_data[$r][$i], 4, 2) . "/". substr($working_data[$r][$i], 6, 2) ."</font></td>\n";
                                                break;
                                            } elseif ($working_data[$r][3] == '�ٶ�') {
                                                $listTable .= "<td class='winbox' align='right' nowrap><font color='blue'>". substr($working_data[$r][$i], 4, 2) . "/". substr($working_data[$r][$i], 6, 2) ."</font></td>\n";
                                                break;
                                            } else {
                                                $listTable .= "<td class='winbox' align='right' nowrap>". substr($working_data[$r][$i], 4, 2) . "/". substr($working_data[$r][$i], 6, 2) ."</td>\n";
                                                break;
                                            }
                                        }
                                    case 2:                                         // ����
                                        if ($working_data[$r][23] == '��') {
                                            if ($working_data[$r][3] == '����') {
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap><font color='red'>". $working_data[$r][$i] ."</font></td>\n";
                                                break;
                                            } elseif ($working_data[$r][3] == 'ˡ��') {
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap><font color='red'>". $working_data[$r][$i] ."</font></td>\n";
                                                break;
                                            } elseif ($working_data[$r][3] == '�ٶ�') {
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap><font color='blue'>". $working_data[$r][$i] ."</font></td>\n";
                                                break;
                                            } else {
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                break;
                                            }
                                        } else {
                                            if ($working_data[$r][3] == '����') {
                                                $listTable .= "<td class='winbox' align='right' nowrap><font color='red'>". $working_data[$r][$i] ."</font></td>\n";
                                                break;
                                            } elseif ($working_data[$r][3] == 'ˡ��') {
                                                $listTable .= "<td class='winbox' align='right' nowrap><font color='red'>". $working_data[$r][$i] ."</font></td>\n";
                                                break;
                                            } elseif ($working_data[$r][3] == '�ٶ�') {
                                                $listTable .= "<td class='winbox' align='right' nowrap><font color='blue'>". $working_data[$r][$i] ."</font></td>\n";
                                                break;
                                            } else {
                                                $listTable .= "<td class='winbox' align='right' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                break;
                                            }
                                        }
                                    case 3:                                         // ��������
                                        if ($working_data[$r][23] == '��') {
                                            if ($working_data[$r][$i] == '����') {
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap><font color='red'>". $working_data[$r][$i] ."</font></td>\n";
                                                break;
                                            } elseif ($working_data[$r][$i] == 'ˡ��') {
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap><font color='red'>". $working_data[$r][$i] ."</font></td>\n";
                                                break;
                                            } elseif ($working_data[$r][$i] == '�ٶ�') {
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap><font color='blue'>". $working_data[$r][$i] ."</font></td>\n";
                                                break;
                                            } else {
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                break;
                                            }
                                        } else {
                                            if ($working_data[$r][$i] == '����') {
                                                $listTable .= "<td class='winbox' align='right' nowrap><font color='red'>". $working_data[$r][$i] ."</font></td>\n";
                                                break;
                                            } elseif ($working_data[$r][$i] == 'ˡ��') {
                                                $listTable .= "<td class='winbox' align='right' nowrap><font color='red'>". $working_data[$r][$i] ."</font></td>\n";
                                                break;
                                            } elseif ($working_data[$r][$i] == '�ٶ�') {
                                                $listTable .= "<td class='winbox' align='right' nowrap><font color='blue'>". $working_data[$r][$i] ."</font></td>\n";
                                                break;
                                            } else {
                                                $listTable .= "<td class='winbox' align='right' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                break;
                                            }
                                        }
                                    case 4:                                         // �Ժ���ͳ
                                        if ($working_data[$r][3] == '����') {
                                            if ($working_data[$r][23] == '��') {
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                break;
                                            } else {
                                                $listTable .= "<td class='winbox' align='right' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                break;
                                            }
                                        } elseif ($working_data[$r][3] == 'ˡ��') {
                                            if ($working_data[$r][23] == '��') {
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                break;
                                            } else {
                                                $listTable .= "<td class='winbox' align='right' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                break;
                                            }
                                        } elseif ($working_data[$r][3] == '�ٶ�') {
                                            if ($working_data[$r][23] == '��') {
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                break;
                                            } else {
                                                $listTable .= "<td class='winbox' align='right' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                break;
                                            }
                                        } else {
                                            if ($working_data[$r][5] == '0000') {
                                                    if ($working_data[$r][6] == '0000') {
                                                        if ($working_data[$r][$i] == '��') {
                                                            $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                            break;
                                                        } else {
                                                            $listTable .= "<td class='winbox' align='right' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                            break;
                                                        }
                                                    } else {
                                                        $listTable .= "<td class='winbox' align='right' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                        break;
                                                    }
                                            } else {
                                                $listTable .= "<td class='winbox' align='right' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                break;
                                            }
                                        }
                                        /*
                                        if ($working_data[$r][23] == '��') {
                                            $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $working_data[$r][$i] ."</td>\n";
                                            break;
                                        } else {
                                            $listTable .= "<td class='winbox' align='right' nowrap>". $working_data[$r][$i] ."</td>\n";
                                            break;
                                        }
                                        */
                                    case 5:                                         // �жл���
                                        if ($working_data[$r][23] == '��') {
                                            if ($working_data[$r][$i] == '0000') {
                                                    if ($working_data[$r][6] == '0000') {
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                                    } else {
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>----</td>\n";
                                                    }
                                            } else {
                                                $work_num += 1;                         // �жл��郎�ǹ蘆��Ƥ���нж������ܣ�
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". substr($working_data[$r][$i], 0, 2) .":". substr($working_data[$r][$i], 2, 2) ."</td>\n";
                                            }
                                            break;
                                        } else {
                                            if ($working_data[$r][$i] == '0000') {
                                                    if ($working_data[$r][6] == '0000') {
                                                        $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                                    } else {
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>----</td>\n";
                                                    }
                                            } else {
                                                $work_num += 1;                         // �жл��郎�ǹ蘆��Ƥ���нж������ܣ�
                                                $listTable .= "<td class='winbox' align='right' nowrap>". substr($working_data[$r][$i], 0, 2) .":". substr($working_data[$r][$i], 2, 2) ."</td>\n";
                                            }
                                            break;
                                        }
                                    case 6:                                         // ��л���
                                        if ($working_data[$r][23] == '��') {
                                            if ($working_data[$r][$i] == '0000') {
                                                    if ($working_data[$r][5] == '0000') {
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                                    } else {
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>----</td>\n";
                                                    }
                                            } else {
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". substr($working_data[$r][$i], 0, 2) .":". substr($working_data[$r][$i], 2, 2) ."</td>\n";
                                            }
                                            break;
                                        } else {
                                            if ($working_data[$r][$i] == '0000') {
                                                    if ($working_data[$r][5] == '0000') {
                                                        $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                                    } else {
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>----</td>\n";
                                                    }
                                            } else {
                                                $listTable .= "<td class='winbox' align='right' nowrap>". substr($working_data[$r][$i], 0, 2) .":". substr($working_data[$r][$i], 2, 2) ."</td>\n";
                                            }
                                            break;
                                        }
                                    case 7:                                         // �������
                                        if ($working_data[$r][23] == '��') {
                                            if ($working_data[$r][$i] == '000000') {
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                            } else {
                                                $hour = floor($working_data[$r][$i] / 60);
                                                $minutes = $working_data[$r][$i] % 60;
                                                if($minutes == 0) {
                                                    $minutes = '00';
                                                } else if($minutes < 10) {
                                                    $minutes = '0' . $minutes;
                                                }
                                                //$minutes = $working_data[$r][$i]%60;
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                            }
                                            break;
                                        } else {
                                            if ($working_data[$r][$i] == '000000') {
                                                    $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                            } else {
                                                $hour = floor($working_data[$r][$i] / 60);
                                                $minutes = $working_data[$r][$i] % 60;
                                                if($minutes == 0) {
                                                    $minutes = '00';
                                                } else if($minutes < 10) {
                                                    $minutes = '0' . $minutes;
                                                }
                                                //$minutes = $working_data[$r][$i]%60;
                                                $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                            }
                                            break;
                                        }
                                    case 8:                                         // ��Ĺ����
                                        if ($working_data[$r][23] == '��') {
                                            if ($working_data[$r][$i] == '000000') {
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                            } else {
                                                $hour = floor($working_data[$r][$i] / 60);
                                                $minutes = $working_data[$r][$i]%60;
                                                if($hour == 0) {
                                                    $hour = '0';
                                                }
                                                if($minutes == 0) {
                                                    $minutes = '00';
                                                } else if($minutes < 10) {
                                                    $minutes = '0' . $minutes;
                                                }
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                            }
                                            break;
                                        } else {
                                            $query = "SELECT * FROM user_detailes WHERE uid='{$uid[$t]}' AND pid=5";
                                            $res_chk = array();
                                            if ( getResult($query, $res_chk) > 0 ) {    // �ѡ��� ��Ĺ�����å�
                                                if ($working_data[$r][6] >= '1645') {   // ��Ĺ30ʬ�ʾ� �����å�
                                                    if ($working_data[$r][$i] == '000000') {
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>----</td>\n";
                                                    } else {
                                                        $t_temp = $working_data[$r][$i];
                                                        // ��Ĺ���ַ׻��ʼ»��֡�
                                                        $deteObj = new DateTime($working_data[$r][6]);
                                                        $ceil_num = floor(sprintf('%d', $deteObj->format('i')) / 15) * 15;
                                                        $ceil_hour = $deteObj->format('H');
                                                        $have = $ceil_hour.sprintf( '%02d', $ceil_num);
                                                        $end = '2017-05-17 ' . substr($have, 0, 2) . ":" . substr($have, 2, 2) . ":00";
                                                        $startSec = strtotime('2017-05-17 16:15:00');
                                                        $endSec   = strtotime($end);
                                                        
                                                        $r_temp = ($endSec - $startSec)/60;
                                                        
                                                        if($r_temp>=60) {   // ��Ĺ�ϣ����֤ޤǤʤΤ�Ĵ��
                                                            $r_temp = 60;
                                                        } elseif($r_temp>=30) {
                                                            $r_temp = 30;
                                                        }
                                                        $hour_r = floor($r_temp / 60);
                                                        $minutes_r = $r_temp%60;
                                                        if($hour_r == 0) {
                                                            $hour_r = '0';
                                                        }
                                                        if($minutes_r == 0) {
                                                            $minutes_r = '00';
                                                        } else if($minutes_r < 10) {
                                                            $minutes_r = '0' . $minutes_r;
                                                        }
                                                        
                                                        // ��Ĺ���ַ׻��ʿ���ʬ��
                                                        $hour = floor($t_temp / 60);
                                                        $minutes = $t_temp%60;
                                                        if($hour == 0) {
                                                            $hour = '0';
                                                        }
                                                        if($minutes == 0) {
                                                            $minutes = '00';
                                                        } else if($minutes < 10) {
                                                            $minutes = '0' . $minutes;
                                                        }
                                                        if ($hour_r == $hour) {
                                                        //if ($hour_r == $hour_s) {
                                                            if ($minutes_r == $minutes) {
                                                            //if ($minutes_r == $minutes_s) {
                                                                $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                                //$listTable .= "<td class='winbox' align='right' nowrap>". $hour_r .":". $minutes_r ."</td>\n";
                                                            } else {
                                                                $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>". $hour .":". $minutes ."</td>\n";
                                                                //$listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                                //$listTable .= "<td class='winbox' align='right' nowrap>". $hour_r .":". $minutes_r ."</td>\n";
                                                            }
                                                        } else {
                                                                //$listTable .= "<td class='winbox' align='right' nowrap>". $hour_r .":". $minutes_r ."</td>\n";
                                                                //$listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                                $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>". $hour .":". $minutes ."</td>\n";
                                                        }
                                                    }
                                                } else {                                // ��Ĺ̵�� �����å�̵��
                                                    if ($working_data[$r][$i] == '000000') {
                                                        $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                                    } else {
                                                        $hour = floor($working_data[$r][$i] / 60);
                                                        $minutes = $working_data[$r][$i]%60;
                                                        if($hour == 0) {
                                                            $hour = '0';
                                                        }
                                                        if($minutes == 0) {
                                                            $minutes = '00';
                                                        } else if($minutes < 10) {
                                                            $minutes = '0' . $minutes;
                                                        }
                                                        $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                    }
                                                }
                                            } else {                                    // �ѡ��Ȱʳ� ��Ĺ�����å�̵��
                                                if ($working_data[$r][$i] == '000000') {
                                                    $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                                } else {
                                                    $hour = floor($working_data[$r][$i] / 60);
                                                    $minutes = $working_data[$r][$i]%60;
                                                    if($hour == 0) {
                                                        $hour = '0';
                                                    }
                                                    if($minutes == 0) {
                                                        $minutes = '00';
                                                    } else if($minutes < 10) {
                                                        $minutes = '0' . $minutes;
                                                    }
                                                    $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                }
                                            }
                                            break;
                                        }
                                    case 9:                                         // ��лĶ�
                                        if ($working_data[$r][23] == '��') {
                                            $t_temp = $working_data[$r][$i] + $working_data[$r][$i+1];
                                            $s_temp = $t_temp + $working_data[$r][$i+2];    // ����ĶȲ�̣
                                            $query = "SELECT * FROM user_detailes WHERE uid='{$uid[$t]}' AND (class LIKE '8%' OR class LIKE '9%' OR class LIKE '10%' OR class LIKE '11%' OR class LIKE '12%')";
                                            $res_chk = array();
                                            if ( getResult($query, $res_chk) > 0 ) {    // 8�鿦�ʾ� �Ķȥ����å�̵��
                                                if ($working_data[$r][4] == '�Ҳ�' || $working_data[$r][4] == 'Ǥ��') {
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>---</td>\n";
                                                } elseif ($t_temp == '000000') {
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                                } else {
                                                    $hour = floor($t_temp / 60);
                                                    $minutes = $t_temp%60;
                                                    if($hour == 0) {
                                                        $hour = '0';
                                                    }
                                                    if($minutes == 0) {
                                                        $minutes = '00';
                                                    } else if($minutes < 10) {
                                                        $minutes = '0' . $minutes;
                                                    }
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                                }
                                                break;
                                            } elseif($sid_t[$t] == 19) {
                                                if ($working_data[$r][6] >= '1830') {
                                                    if ($working_data[$r][4] == '�Ҳ�' || $working_data[$r][4] == 'Ǥ��') {
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>---</td>\n";
                                                    } elseif ($t_temp == '000000') {
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>----</td>\n";
                                                    } else {
                                                        // �ĶȻ��ַ׻��ʼ»��֡�
                                                        $deteObj = new DateTime($working_data[$r][6]);
                                                        $ceil_num = floor(sprintf('%d', $deteObj->format('i')) / 30) * 30;
                                                        $ceil_hour = $deteObj->format('H');
                                                        $have = $ceil_hour.sprintf( '%02d', $ceil_num);
                                                        $end = '2017-05-17 ' . substr($have, 0, 2) . ":" . substr($have, 2, 2) . ":00";
                                                        $startSec = strtotime('2017-05-17 18:00:00');
                                                        $endSec   = strtotime($end);
                                                        
                                                        $r_temp = ($endSec - $startSec)/60;
                                                        
                                                        $hour_r = floor($r_temp / 60);
                                                        $minutes_r = $r_temp%60;
                                                        if($hour_r == 0) {
                                                            $hour_r = '0';
                                                        }
                                                        if($minutes_r == 0) {
                                                            $minutes_r = '00';
                                                        } else if($minutes_r < 10) {
                                                            $minutes_r = '0' . $minutes_r;
                                                        }
                                                        
                                                        // �ĶȻ��ַ׻��ʿ���ʬ��
                                                        $hour = floor($t_temp / 60);
                                                        $minutes = $t_temp%60;
                                                        if($hour == 0) {
                                                            $hour = '0';
                                                        }
                                                        if($minutes == 0) {
                                                            $minutes = '00';
                                                        } else if($minutes < 10) {
                                                            $minutes = '0' . $minutes;
                                                        }
                                                        // ����ĶȲ�̣
                                                        $hour_s = floor($s_temp / 60);
                                                        $minutes_s = $s_temp%60;
                                                        if($hour_s == 0) {
                                                            $hour_s = '0';
                                                        }
                                                        if($minutes_s == 0) {
                                                            $minutes_s = '00';
                                                        } else if($minutes_s < 10) {
                                                            $minutes_s = '0' . $minutes_s;
                                                        }
                                                        //if ($hour_r == $hour) {
                                                        if ($hour_r == $hour_s) {
                                                            //if ($minutes_r == $minutes) {
                                                            if ($minutes_r == $minutes_s) {
                                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                                            } else {
                                                                $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>". $hour .":". $minutes ."</td>\n";
                                                            }
                                                        } else {
                                                                $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>". $hour .":". $minutes ."</td>\n";
                                                        }
                                                    }
                                                    break;
                                                } else {
                                                    if ($working_data[$r][4] == '�Ҳ�' || $working_data[$r][4] == 'Ǥ��') {
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>---</td>\n";
                                                    } elseif ($t_temp == '000000') {
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                                    } else {
                                                        $hour = floor($t_temp / 60);
                                                        $minutes = $t_temp%60;
                                                        if($hour == 0) {
                                                            $hour = '0';
                                                        }
                                                        if($minutes == 0) {
                                                            $minutes = '00';
                                                        } else if($minutes < 10) {
                                                            $minutes = '0' . $minutes;
                                                        }
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                                    }
                                                    break;
                                                }
                                            } else {
                                                if ($working_data[$r][6] >= '1800') {
                                                    if ($working_data[$r][4] == '�Ҳ�' || $working_data[$r][4] == 'Ǥ��') {
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>---</td>\n";
                                                    } elseif ($t_temp == '000000') {
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>----</td>\n";
                                                    } else {
                                                        // �ĶȻ��ַ׻��ʼ»��֡�
                                                        $deteObj = new DateTime($working_data[$r][6]);
                                                        $ceil_num = floor(sprintf('%d', $deteObj->format('i')) / 30) * 30;
                                                        $ceil_hour = $deteObj->format('H');
                                                        $have = $ceil_hour.sprintf( '%02d', $ceil_num);
                                                        if ($have >= '0000') {
                                                             $end = '2017-05-17 ' . substr($have, 0, 2) . ":" . substr($have, 2, 2) . ":00";
                                                        } else {
                                                            $end = '2017-05-18 ' . substr($have, 0, 2) . ":" . substr($have, 2, 2) . ":00";
                                                        }
                                                        $startSec = strtotime('2017-05-17 17:30:00');
                                                        $endSec   = strtotime($end);
                                                        
                                                        $r_temp = ($endSec - $startSec)/60;
                                                        
                                                        $hour_r = floor($r_temp / 60);
                                                        $minutes_r = $r_temp%60;
                                                        if($hour_r == 0) {
                                                            $hour_r = '0';
                                                        }
                                                        if($minutes_r == 0) {
                                                            $minutes_r = '00';
                                                        } else if($minutes_r < 10) {
                                                            $minutes_r = '0' . $minutes_r;
                                                        }
                                                        
                                                        // �ĶȻ��ַ׻��ʿ���ʬ��
                                                        $hour = floor($t_temp / 60);
                                                        $minutes = $t_temp%60;
                                                        if($hour == 0) {
                                                            $hour = '0';
                                                        }
                                                        if($minutes == 0) {
                                                            $minutes = '00';
                                                        } else if($minutes < 10) {
                                                            $minutes = '0' . $minutes;
                                                        }
                                                        // ����ĶȲ�̣
                                                        $hour_s = floor($s_temp / 60);
                                                        $minutes_s = $s_temp%60;
                                                        if($hour_s == 0) {
                                                            $hour_s = '0';
                                                        }
                                                        if($minutes_s == 0) {
                                                            $minutes_s = '00';
                                                        } else if($minutes_s < 10) {
                                                            $minutes_s = '0' . $minutes_s;
                                                        }
                                                        //if ($hour_r == $hour) {
                                                        if ($hour_r == $hour_s) {
                                                            //if ($minutes_r == $minutes) {
                                                            if ($minutes_r == $minutes_s) {
                                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                                            } else {
                                                                $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>". $hour .":". $minutes ."</td>\n";
                                                            }
                                                        } else {
                                                                $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>". $hour .":". $minutes ."</td>\n";
                                                        }
                                                    }
                                                    break;
                                                } else {
                                                    if ($working_data[$r][4] == '�Ҳ�' || $working_data[$r][4] == 'Ǥ��') {
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>---</td>\n";
                                                    } elseif ($t_temp == '000000') {
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                                    } else {
                                                        $hour = floor($t_temp / 60);
                                                        $minutes = $t_temp%60;
                                                        if($hour == 0) {
                                                            $hour = '0';
                                                        }
                                                        if($minutes == 0) {
                                                            $minutes = '00';
                                                        } else if($minutes < 10) {
                                                            $minutes = '0' . $minutes;
                                                        }
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                                    }
                                                    break;
                                                }
                                            }
                                        } else {
                                            $t_temp = $working_data[$r][$i] + $working_data[$r][$i+1];
                                            $s_temp = $t_temp + $working_data[$r][$i+2];    // ����ĶȲ�̣
                                            $query = "SELECT * FROM user_detailes WHERE uid='{$uid[$t]}' AND (class LIKE '8%' OR class LIKE '9%' OR class LIKE '10%' OR class LIKE '11%' OR class LIKE '12%')";
                                            $res_chk = array();
                                            if ( getResult($query, $res_chk) > 0 ) {    // 8�鿦�ʾ� �Ķȥ����å�̵��
                                                if ($working_data[$r][4] == '�Ҳ�' || $working_data[$r][4] == 'Ǥ��') {
                                                    $listTable .= "<td class='winbox' align='right' nowrap>---</td>\n";
                                                } elseif ($t_temp == '000000') {
                                                    $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                                } else {
                                                    $hour = floor($t_temp / 60);
                                                    $minutes = $t_temp%60;
                                                    if($hour == 0) {
                                                        $hour = '0';
                                                    }
                                                    if($minutes == 0) {
                                                        $minutes = '00';
                                                    } else if($minutes < 10) {
                                                        $minutes = '0' . $minutes;
                                                    }
                                                    $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                }
                                                break;
                                            } elseif($sid_t[$t] == 19) {
                                                if ($working_data[$r][6] >= '1830') {
                                                    if ($working_data[$r][4] == '�Ҳ�' || $working_data[$r][4] == 'Ǥ��') {
                                                        $listTable .= "<td class='winbox' align='right' nowrap>---</td>\n";
                                                    } else if ($t_temp == '000000') {
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>----</td>\n";
                                                    } else {
                                                        // �ĶȻ��ַ׻��ʼ»��֡�
                                                        $deteObj = new DateTime($working_data[$r][6]);
                                                        $ceil_num = floor(sprintf('%d', $deteObj->format('i')) / 30) * 30;
                                                        $ceil_hour = $deteObj->format('H');
                                                        $have = $ceil_hour.sprintf( '%02d', $ceil_num);
                                                        $end = '2017-05-17 ' . substr($have, 0, 2) . ":" . substr($have, 2, 2) . ":00";
                                                        $startSec = strtotime('2017-05-17 18:00:00');
                                                        $endSec   = strtotime($end);
                                                        
                                                        $r_temp = ($endSec - $startSec)/60;
                                                        
                                                        $hour_r = floor($r_temp / 60);
                                                        $minutes_r = $r_temp%60;
                                                        if($hour_r == 0) {
                                                            $hour_r = '0';
                                                        }
                                                        if($minutes_r == 0) {
                                                            $minutes_r = '00';
                                                        } else if($minutes_r < 10) {
                                                            $minutes_r = '0' . $minutes_r;
                                                        }
                                                            
                                                        // �ĶȻ��ַ׻��ʿ���ʬ��
                                                        $hour = floor($t_temp / 60);
                                                        $minutes = $t_temp%60;
                                                        if($hour == 0) {
                                                            $hour = '0';
                                                        }
                                                        if($minutes == 0) {
                                                            $minutes = '00';
                                                        } else if($minutes < 10) {
                                                            $minutes = '0' . $minutes;
                                                        }
                                                        // ����ĶȲ�̣
                                                        $hour_s = floor($s_temp / 60);
                                                        $minutes_s = $s_temp%60;
                                                        if($hour_s == 0) {
                                                            $hour_s = '0';
                                                        }
                                                        if($minutes_s == 0) {
                                                            $minutes_s = '00';
                                                        } else if($minutes_s < 10) {
                                                            $minutes_s = '0' . $minutes_s;
                                                        }
                                                        //if ($hour_r == $hour) {
                                                        if ($hour_r == $hour_s) {
                                                            //if ($minutes_r == $minutes) {
                                                            if ($minutes_r == $minutes_s) {
                                                                $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                            } else {
                                                                $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>". $hour .":". $minutes ."</td>\n";
                                                            }
                                                        } else {
                                                                $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>". $hour .":". $minutes ."</td>\n";
                                                        }
                                                    }
                                                    break;
                                                } else {
                                                    if ($working_data[$r][4] == '�Ҳ�' || $working_data[$r][4] == 'Ǥ��') {
                                                        $listTable .= "<td class='winbox' align='right' nowrap>---</td>\n";
                                                    } elseif ($t_temp == '000000') {
                                                        $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                                    } else {
                                                        $hour = floor($t_temp / 60);
                                                        $minutes = $t_temp%60;
                                                        if($hour == 0) {
                                                            $hour = '0';
                                                        }
                                                        if($minutes == 0) {
                                                            $minutes = '00';
                                                        } else if($minutes < 10) {
                                                            $minutes = '0' . $minutes;
                                                        }
                                                            $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                    }
                                                    break;
                                                }
                                            } else {
                                                if ($working_data[$r][6] >= '1800') {
                                                    if ($working_data[$r][4] == '�Ҳ�' || $working_data[$r][4] == 'Ǥ��') {
                                                        $listTable .= "<td class='winbox' align='right' nowrap>---</td>\n";
                                                    } else if ($t_temp == '000000') {
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>----</td>\n";
                                                    } else {
                                                        // �ĶȻ��ַ׻��ʼ»��֡�
                                                        $deteObj = new DateTime($working_data[$r][6]);
                                                        $ceil_num = floor(sprintf('%d', $deteObj->format('i')) / 30) * 30;
                                                        $ceil_hour = $deteObj->format('H');
                                                        $have = $ceil_hour.sprintf( '%02d', $ceil_num);
                                                        if ($have >= '0000') {
                                                             $end = '2017-05-17 ' . substr($have, 0, 2) . ":" . substr($have, 2, 2) . ":00";
                                                        } else {
                                                            $end = '2017-05-18 ' . substr($have, 0, 2) . ":" . substr($have, 2, 2) . ":00";
                                                        }
                                                        $startSec = strtotime('2017-05-17 17:30:00');
                                                        $endSec   = strtotime($end);
                                                        
                                                        $r_temp = ($endSec - $startSec)/60;
                                                        
                                                        $hour_r = floor($r_temp / 60);
                                                        $minutes_r = $r_temp%60;
                                                        if($hour_r == 0) {
                                                            $hour_r = '0';
                                                        }
                                                        if($minutes_r == 0) {
                                                            $minutes_r = '00';
                                                        } else if($minutes_r < 10) {
                                                            $minutes_r = '0' . $minutes_r;
                                                        }
                                                            
                                                        // �ĶȻ��ַ׻��ʿ���ʬ��
                                                        $hour = floor($t_temp / 60);
                                                        $minutes = $t_temp%60;
                                                        if($hour == 0) {
                                                            $hour = '0';
                                                        }
                                                        if($minutes == 0) {
                                                            $minutes = '00';
                                                        } else if($minutes < 10) {
                                                            $minutes = '0' . $minutes;
                                                        }
                                                        // ����ĶȲ�̣
                                                        $hour_s = floor($s_temp / 60);
                                                        $minutes_s = $s_temp%60;
                                                        if($hour_s == 0) {
                                                            $hour_s = '0';
                                                        }
                                                        if($minutes_s == 0) {
                                                            $minutes_s = '00';
                                                        } else if($minutes_s < 10) {
                                                            $minutes_s = '0' . $minutes_s;
                                                        }
                                                        //if ($hour_r == $hour) {
                                                        if ($hour_r == $hour_s) {
                                                            //if ($minutes_r == $minutes) {
                                                            if ($minutes_r == $minutes_s) {
                                                                $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                            } else {
                                                                $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>". $hour .":". $minutes ."</td>\n";
                                                            }
                                                        } else {
                                                                $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>". $hour .":". $minutes ."</td>\n";
                                                        }
                                                    }
                                                    break;
                                                } else {
                                                    if ($working_data[$r][4] == '�Ҳ�' || $working_data[$r][4] == 'Ǥ��') {
                                                        $listTable .= "<td class='winbox' align='right' nowrap>---</td>\n";
                                                    } elseif ($t_temp == '000000') {
                                                        $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                                    } else {
                                                        $hour = floor($t_temp / 60);
                                                        $minutes = $t_temp%60;
                                                        if($hour == 0) {
                                                            $hour = '0';
                                                        }
                                                        if($minutes == 0) {
                                                            $minutes = '00';
                                                        } else if($minutes < 10) {
                                                            $minutes = '0' . $minutes;
                                                        }
                                                            $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                    }
                                                    break;
                                                }
                                            }
                                        }
                                    case 11:                                        // ����Ķ�
                                        if ($working_data[$r][23] == '��') {
                                            if ($working_data[$r][$i] == '000000') {
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                            } else {
                                                $hour = floor($working_data[$r][$i] / 60);
                                                $minutes = $working_data[$r][$i]%60;
                                                if($hour == 0) {
                                                    $hour = '0';
                                                }
                                                if($minutes == 0) {
                                                    $minutes = '00';
                                                } else if($minutes < 10) {
                                                    $minutes = '0' . $minutes;
                                                }
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                            }
                                            break;
                                        } else {
                                            if ($working_data[$r][$i] == '000000') {
                                                    $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                            } else {
                                                $hour = floor($working_data[$r][$i] / 60);
                                                $minutes = $working_data[$r][$i]%60;
                                                if($hour == 0) {
                                                    $hour = '0';
                                                }
                                                if($minutes == 0) {
                                                    $minutes = '00';
                                                } else if($minutes < 10) {
                                                    $minutes = '0' . $minutes;
                                                }
                                                $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                            }
                                            break;
                                        }
                                    case 12:                                        // �ٽл���
                                        if ($working_data[$r][23] == '��') {
                                            if ($working_data[$r][$i] == '000000') {
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                            } else {
                                                $howork_num += 1;                       // �������ǹ郎����еٽ������ܣ�
                                                $hour = floor($working_data[$r][$i] / 60);
                                                $minutes = $working_data[$r][$i]%60;
                                                if($hour == 0) {
                                                    $hour = '0';
                                                }
                                                if($minutes == 0) {
                                                    $minutes = '00';
                                                } else if($minutes < 10) {
                                                    $minutes = '0' . $minutes;
                                                }
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                            }
                                            break;
                                        } else {
                                            if ($working_data[$r][$i] == '000000') {
                                                    $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                            } else {
                                                $howork_num += 1;                       // �������ǹ郎����еٽ������ܣ�
                                                $hour = floor($working_data[$r][$i] / 60);
                                                $minutes = $working_data[$r][$i]%60;
                                                if($hour == 0) {
                                                    $hour = '0';
                                                }
                                                if($minutes == 0) {
                                                    $minutes = '00';
                                                } else if($minutes < 10) {
                                                    $minutes = '0' . $minutes;
                                                }
                                                $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                            }
                                            break;
                                        }
                                    case 13:                                        // �ٽлĶ�
                                        if ($working_data[$r][23] == '��') {
                                            if ($working_data[$r][$i] == '000000') {
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                            } else {
                                                $hour = floor($working_data[$r][$i] / 60);
                                                $minutes = $working_data[$r][$i]%60;
                                                if($hour == 0) {
                                                    $hour = '0';
                                                }
                                                if($minutes == 0) {
                                                    $minutes = '00';
                                                } else if($minutes < 10) {
                                                    $minutes = '0' . $minutes;
                                                }
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                            }
                                            break;
                                        } else {
                                            if ($working_data[$r][$i] == '000000') {
                                                    $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                            } else {
                                                $hour = floor($working_data[$r][$i] / 60);
                                                $minutes = $working_data[$r][$i]%60;
                                                if($hour == 0) {
                                                    $hour = '0';
                                                }
                                                if($minutes == 0) {
                                                    $minutes = '00';
                                                } else if($minutes < 10) {
                                                    $minutes = '0' . $minutes;
                                                }
                                                $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                            }
                                            break;
                                        }
                                    case 14:                                        // �ٽп���
                                        if ($working_data[$r][23] == '��') {
                                            if ($working_data[$r][$i] == '000000') {
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                            } else {
                                                $hour = floor($working_data[$r][$i] / 60);
                                                $minutes = $working_data[$r][$i]%60;
                                                if($hour == 0) {
                                                    $hour = '0';
                                                }
                                                if($minutes == 0) {
                                                    $minutes = '00';
                                                } else if($minutes < 10) {
                                                    $minutes = '0' . $minutes;
                                                }
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                            }
                                            break;
                                        } else {
                                        }
                                    case 15:                                        // ˡ�����
                                        if ($working_data[$r][23] == '��') {
                                            if ($working_data[$r][$i] == '000000') {
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                            } else {
                                                $hour = floor($working_data[$r][$i] / 60);
                                                $minutes = $working_data[$r][$i]%60;
                                                $howork_num += 1;                       // ˡ����֤��ǹ郎����еٽ������ܣ�
                                                if($hour == 0) {
                                                    $hour = '0';
                                                }
                                                if($minutes == 0) {
                                                    $minutes = '00';
                                                } else if($minutes < 10) {
                                                    $minutes = '0' . $minutes;
                                                }
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                            }
                                            break;
                                        } else {
                                            if ($working_data[$r][$i] == '000000') {
                                                    $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                            } else {
                                                $hour = floor($working_data[$r][$i] / 60);
                                                $minutes = $working_data[$r][$i]%60;
                                                $howork_num += 1;                       // ˡ����֤��ǹ郎����еٽ������ܣ�
                                                if($hour == 0) {
                                                    $hour = '0';
                                                }
                                                if($minutes == 0) {
                                                    $minutes = '00';
                                                } else if($minutes < 10) {
                                                    $minutes = '0' . $minutes;
                                                }
                                                $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                            }
                                            break;
                                        }
                                    case 16:                                        // ˡ��Ķ�
                                        if ($working_data[$r][23] == '��') {
                                            if ($working_data[$r][$i] == '000000') {
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                            } else {
                                                $hour = floor($working_data[$r][$i] / 60);
                                                $minutes = $working_data[$r][$i]%60;
                                                if($hour == 0) {
                                                    $hour = '0';
                                                }
                                                if($minutes == 0) {
                                                    $minutes = '00';
                                                } else if($minutes < 10) {
                                                    $minutes = '0' . $minutes;
                                                }
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                            }
                                            break;
                                        } else {
                                            if ($working_data[$r][$i] == '000000') {
                                                    $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                            } else {
                                                $hour = floor($working_data[$r][$i] / 60);
                                                $minutes = $working_data[$r][$i]%60;
                                                if($hour == 0) {
                                                    $hour = '0';
                                                }
                                                if($minutes == 0) {
                                                    $minutes = '00';
                                                } else if($minutes < 10) {
                                                    $minutes = '0' . $minutes;
                                                }
                                                $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                            }
                                            break;
                                        }
                                    case 17:                                        // �ٹ�����
                                        if ($working_data[$r][23] == '��') {
                                            if ($working_data[$r][$i] == '000000') {
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                            } else {
                                                $hour = floor($working_data[$r][$i] / 60);
                                                $minutes = $working_data[$r][$i]%60;
                                                if($hour == 0) {
                                                    $hour = '0';
                                                }
                                                if($minutes == 0) {
                                                    $minutes = '00';
                                                } else if($minutes < 10) {
                                                    $minutes = '0' . $minutes;
                                                }
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                            }
                                            break;
                                        } else {
                                            if ($working_data[$r][$i] == '000000') {
                                                    $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                            } else {
                                                $hour = floor($working_data[$r][$i] / 60);
                                                $minutes = $working_data[$r][$i]%60;
                                                if($hour == 0) {
                                                    $hour = '0';
                                                }
                                                if($minutes == 0) {
                                                    $minutes = '00';
                                                } else if($minutes < 10) {
                                                    $minutes = '0' . $minutes;
                                                }
                                                $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                            }
                                            break;
                                        }
                                    case 23:                                        // �����ǧ
                                        /*
                                        if ($working_data[$r][$i] == '��') {
                                            $listTable .= "<td class='winbox' align='center' bgcolor='white' nowrap>" . $working_data[$r][$i] ."</td>\n";
                                        } else {
                                            $listTable .= "<td class='winbox' align='center' nowrap><font color='red'>". $working_data[$r][$i] ."</font></td>\n";
                                        }
                                        */
                                        break;
                                    default:                                          
                                        break;
                                }
                            }
                            
                            $listTable .= "    </tr>\n";
                        }
                    } else {
                        for ($r=0; $r<$request->get('rows'); $r++) {                                        // �쥳���ɿ�ʬ���֤�
                            for ($i=0; $i<$request->get('num'); $i++) {
                                switch ($i) {
                                    case 5:
                                        if ($working_data[$r][$i] != '0000') {
                                            $work_num += 1;                                 // �жл��郎�ǹ蘆��Ƥ���нж������ܣ�
                                        }
                                        break;
                                    case 12:
                                        if ($working_data[$r][$i] != '000000') {
                                            $howork_num += 1;                               // �ٽл��֤�����еٽ������ܣ�
                                        }
                                        break;
                                    case 15:
                                        if ($working_data[$r][$i] != '000000') {
                                            $howork_num += 1;                               // ˡ����֤�����еٽ������ܣ�
                                        }
                                        break;
                                    default:
                                        break;
                                }
                            }
                        }
                    }
                    // ���ץǡ�����ʬ����
                    $listTable = $this->getViewHTMLTotal($request, $listTable, $working_data, $work_num, $howork_num, $uid[$t], $chek_flg);
                }
            }
        }
        $listTable .= "
                        <form name='CorrectForm'  method='post' target='_parent'
                           onSubmit='return WorkingHoursReport.checkANDexecute(this, 1)'
                        >
        \n";
        $listTable .= "</form>\n";
        /*
        if ($request->get('rows') <= 0) {
        } else {
            $num = $request->get('rows') - 1;                                           // �ǡ������ʤ����դ���ꤷ�Ƥ�
            $str_date = $working_data[0][1];                                            // �ǡ���������ǽ����ޤǤ�
            $end_date = $working_data[$num][1];                                         // ���ꤹ��褦���б�
            if (!getCheckAuthority(29)) {                                               // ��Ĺ�����ʾ�ϾȲ�Τ�
                if ($_SESSION['User_ID'] != '970227') {                                 // �ݻ֤���ϾȲ�Τ�
                    if ($_SESSION['User_ID'] == '010472') {                             // ��̳��Ĺ ���в�Ĺ
                        if ($request->get('targetSection') == 5) {                      // ��̳�ݤϳ���Ǥ���
                            if ($request->get('formal') == 'details') {                 // �ꥹ�ȷ����Υ����å�
                                $listTable .= "
                                                <form name='CorrectForm'  method='post'  target='_parent' 
                                                    onSubmit='return WorkingHoursReport.checkANDexecute(this, 1)'
                                                >
                                \n";
                                $listTable .= "    <CENTER>\n";
                                $listTable .= "        <input type='button' name='correct1' value='����������Ͽ' onClick='WorkingHoursReport.checkANDexecute(document.CorrectForm, 3);' title='����å�����С����β���ɽ�����ޤ���'>\n";
                                $listTable .= "        <input type='button' name='correct2' value='�����ǧ' onClick='WorkingHoursReport.Confirmexecute(" . $request->get('targetSection') . ", " . $str_date . ", " . $end_date . ", " . $request->get('targetSection') . ");' title='����å�����С��̥�����ɥ���ɽ�����ޤ���'>\n";
                                $listTable .= "    </CENTER>\n";
                                $listTable .= "</form>\n";
                        
                            }
                        } else if ($request->get('targetSection') == 31) {              // �и��Ԥϳ���Ǥ���
                            if ($request->get('formal') == 'details') {                 // �ꥹ�ȷ����Υ����å�
                                $listTable .= "
                                                <form name='CorrectForm'  method='post' target='_parent'
                                                    onSubmit='return WorkingHoursReport.checkANDexecute(this, 1)'
                                                >
                                \n";
                                $listTable .= "    <CENTER>\n";
                                $listTable .= "        <input type='button' name='correct1' value='����������Ͽ' onClick='WorkingHoursReport.checkANDexecute(document.CorrectForm, 3);' title='����å�����С����β���ɽ�����ޤ���'>\n";
                                $listTable .= "        <input type='button' name='correct2' value='�����ǧ' onClick='WorkingHoursReport.Confirmexecute(" . $request->get('targetSection') . ", " . $str_date . ", " . $end_date . ", " . $request->get('targetSection') . ");' title='����å�����С��̥�����ɥ���ɽ�����ޤ���'>\n";
                                $listTable .= "    </CENTER>\n";
                                $listTable .= "</form>\n";
                        
                            }
                        } else if ($request->get('targetSection') == (-3)) {              // 8�鿦�ʾ�ϳ���Ǥ���
                            if ($request->get('formal') == 'details') {                 // �ꥹ�ȷ����Υ����å�
                                $listTable .= "
                                                <form name='CorrectForm'  method='post' target='_parent'
                                                    onSubmit='return WorkingHoursReport.checkANDexecute(this, 1)'
                                                >
                                \n";
                                $listTable .= "    <CENTER>\n";
                                $listTable .= "        <input type='button' name='correct1' value='����������Ͽ' onClick='WorkingHoursReport.checkANDexecute(document.CorrectForm, 3);' title='����å�����С����β���ɽ�����ޤ���'>\n";
                                $listTable .= "        <input type='button' name='correct2' value='�����ǧ' onClick='WorkingHoursReport.Confirmexecute(" . $request->get('targetSection') . ", " . $str_date . ", " . $end_date . ", " . $request->get('targetSection') . ");' title='����å�����С��̥�����ɥ���ɽ�����ޤ���'>\n";
                                $listTable .= "    </CENTER>\n";
                                $listTable .= "</form>\n";
                        
                            }
                        }
                    } else {
                        if ($request->get('formal') == 'details') {                 // �ꥹ�ȷ����Υ����å�
                            $listTable .= "
                                            <form name='CorrectForm'  method='post' target='_parent'
                                                onSubmit='return WorkingHoursReport.checkANDexecute(this, 1)'
                                            >
                            \n";
                            $listTable .= "    <CENTER>\n";
                            $listTable .= "        <input type='button' name='correct1' value='����������Ͽ' onClick='WorkingHoursReport.checkANDexecute(document.CorrectForm, 3);' title='����å�����С����β���ɽ�����ޤ���'>\n";
                            $listTable .= "        <input type='button' name='correct2' value='�����ǧ' onClick='WorkingHoursReport.Confirmexecute(" . $request->get('targetSection') . ", " . $str_date . ", " . $end_date . ", " . $request->get('targetSection') . ");' title='����å�����С��̥�����ɥ���ɽ�����ޤ���'>\n";
                            $listTable .= "    </CENTER>\n";
                            $listTable .= "</form>\n";
                        }
                    }
                }
            }
        }
        */
        // return mb_convert_encoding($listTable, 'UTF-8');
        $request->add('check_flg', 'n');
        return $listTable;
    }
    ////// List��   ���Ƚ���Ȳ�ν��ץǡ�����ʬ����
    private function getViewHTMLTotal($request, $listTable, $working_data, $work_num, $howork_num, $uid, $check_flg)
    {
        $fixed_time    = 0;
        $fixed_hour    = 0;
        $fixed_min     = 0;
        $extend_time   = 0;
        $extend_hour   = 0;
        $extend_min    = 0;
        $overtime      = 0;
        $over_hour     = 0;
        $over_min      = 0;
        $midnight_over = 0;
        $mid_hour      = 0;
        $mid_min       = 0;
        $holiday_time  = 0;
        $hotime_hour   = 0;
        $hotime_min    = 0;
        $holiday_over  = 0;
        $hoover_hour   = 0;
        $hoover_min    = 0;
        $holiday_mid   = 0;
        $homid_hour    = 0;
        $homid_min     = 0;
        $legal_time    = 0;
        $legal_hour    = 0;
        $legal_min     = 0;
        $legal_over    = 0;
        $leover_hour   = 0;
        $leover_min    = 0;
        $late_time     = 0;
        $late_hour     = 0;
        $late_min      = 0;
        for ($r=0; $r<$request->get('rows'); $r++) {                            // �ƻ��֤ν���
            $fixed_time    += $working_data[$r][7];                             // �������
            $extend_time   += $working_data[$r][8];                             // ��Ĺ����
            $overtime      += $working_data[$r][9] + $working_data[$r][10];     // ��лĶȻ���
            $midnight_over += $working_data[$r][11];                            // ����ĶȻ���
            $holiday_time  += $working_data[$r][12];                            // �ٽл���
            $holiday_over  += $working_data[$r][13];                            // �ٽлĶ�
            $holiday_mid   += $working_data[$r][14];                            // �ٽп���
            $legal_time    += $working_data[$r][15];                            // ˡ�����
            $legal_over    += $working_data[$r][16];                            // ˡ��Ķ�
            $late_time     += $working_data[$r][17];                            // �ٹ�����
        }
        $fixed_hour  = floor($fixed_time / 60);                                 // ������֤λ�����ʬ�׻�
        $fixed_min   = $fixed_time%60;                                          // ������֤�ʬ����ʬ�׻�
        $extend_hour = floor($extend_time / 60);                                // ��Ĺ���֤λ�����ʬ�׻�
        $extend_min  = $extend_time%60;                                         // ��Ĺ���֤�ʬ����ʬ�׻�
        $over_hour   = floor($overtime / 60);                                   // ��лĶȻ��֤λ�����ʬ�׻�
        $over_min    = $overtime%60;                                            // ��лĶȤ�ʬ����ʬ�׻�
        $mid_hour    = floor($midnight_over / 60);                              // ����ĶȻ��֤λ�����ʬ�׻�
        $mid_min     = $midnight_over%60;                                       // ����ĶȻ��֤�ʬ����ʬ�׻�
        $hotime_hour = floor($holiday_time / 60);                               // �ٽл��֤λ�����ʬ�׻�
        $hotime_min  = $holiday_time%60;                                        // �ٽл��֤�ʬ����ʬ�׻�
        $hoover_hour = floor($holiday_over / 60);                               // �ٽлĶȻ��֤λ�����ʬ�׻�
        $hoover_min  = $holiday_over%60;                                        // �ٽлĶȻ��֤�ʬ����ʬ�׻�
        $homid_hour  = floor($holiday_mid / 60);                                // �ٽп�����֤λ�����ʬ�׻�
        $homid_min   = $holiday_mid%60;                                         // �ٽп�����֤�ʬ����ʬ�׻�
        $legal_hour  = floor($legal_time / 60);                                 // ˡ����֤λ�����ʬ�׻�
        $legal_min   = $legal_time%60;                                          // ˡ����֤�ʬ����ʬ�׻�
        $leover_hour = floor($legal_over / 60);                                 // ˡ��ĶȻ��֤λ�����ʬ�׻�
        $leover_min  = $legal_over%60;                                          // ˡ��ĶȻ��֤�ʬ����ʬ�׻�
        $late_hour   = floor($late_time / 60);                                  // �ٹ�������֤λ�����ʬ�׻�
        $late_min    = $late_time%60;                                           // �ٹ�������֤�ʬ����ʬ�׻�
        $listTable .= "    <tr>\n";                                             // ���ץǡ�����ɽ��
        $listTable .= "        <th class='winbox'>��</th>\n";
        $listTable .= "        <th class='winbox'>��</th>\n";
        $listTable .= "        <th class='winbox'>��</th>\n";
        $listTable .= "        <th class='winbox'>��</th>\n";
        $listTable .= "        <th class='winbox'>��</th>\n";
        $listTable .= "        <th class='winbox'>��</th>\n";
        $listTable .= "        <th class='winbox'>�������</th>\n";
        $listTable .= "        <th class='winbox'>��Ĺ����</th>\n";
        $listTable .= "        <th class='winbox'>��лĶ�</th>\n";
        $listTable .= "        <th class='winbox'>����Ķ�</th>\n";
        $listTable .= "        <th class='winbox'>�ٽл���</th>\n";
        $listTable .= "        <th class='winbox'>�ٽлĶ�</th>\n";
        $listTable .= "        <th class='winbox'>�ٽп���</th>\n";
        $listTable .= "        <th class='winbox'>ˡ�����</th>\n";
        $listTable .= "        <th class='winbox'>ˡ��Ķ�</th>\n";
        $listTable .= "        <th class='winbox'>�ٹ�����</th>\n";
        //$listTable .= "        <th class='winbox'>��</th>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td class='winbox'>��</td>\n";
        $listTable .= "        <td class='winbox'>��</td>\n";
        $listTable .= "        <td class='winbox'>��</td>\n";
        $listTable .= "        <td class='winbox'>��</td>\n";
        $listTable .= "        <td class='winbox'>��</td>\n";
        $listTable .= "        <td class='winbox'>��</td>\n";
        if ($fixed_min == 0) {                                      // ������ֽ���ɽ��
            $listTable .= "        <td class='winbox' align='right' nowrap>". $fixed_hour .":". $fixed_min ."0</td>\n";
        } else if ($fixed_min < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $fixed_hour .":0". $fixed_min ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $fixed_hour .":". $fixed_min ."</td>\n";
        }
        if ($extend_min == 0) {                                     // ��Ĺ���ֽ���ɽ��
            $listTable .= "        <td class='winbox' align='right' nowrap>". $extend_hour .":". $extend_min ."0</td>\n";
        } else if ($extend_min < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $extend_hour .":0". $extend_min ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $extend_hour .":". $extend_min ."</td>\n";
        }
        if ($over_min == 0) {                                       // ��лĶȻ��ֽ���ɽ��
            $listTable .= "        <td class='winbox' align='right' nowrap>". $over_hour .":". $over_min ."0</td>\n";
        } else if ($over_min < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $over_hour .":0". $over_min ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $over_hour .":". $over_min ."</td>\n";
        }
        if ($mid_min == 0) {                                        // ����ĶȽ���ɽ��
            $listTable .= "        <td class='winbox' align='right' nowrap>". $mid_hour .":". $mid_min ."0</td>\n";
        } else if ($mid_min < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $mid_hour .":0". $mid_min ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $mid_hour .":". $mid_min ."</td>\n";
        }
        if ($hotime_min == 0) {                                     // �ٽл��ֽ���ɽ��
            $listTable .= "        <td class='winbox' align='right' nowrap>". $hotime_hour .":". $hotime_min ."0</td>\n";
        } else if ($hotime_min < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $hotime_hour .":0". $hotime_min ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $hotime_hour .":". $hotime_min ."</td>\n";
        }
        if ($hoover_min == 0) {                                     // �ٽлĶȽ���ɽ��
            $listTable .= "        <td class='winbox' align='right' nowrap>". $hoover_hour .":". $hoover_min ."0</td>\n";
        } else if ($hoover_min < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $hoover_hour .":0". $hoover_min ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $hoover_hour .":". $hoover_min ."</td>\n";
        }
        if ($homid_min == 0) {                                      // �ٽп��뽸��ɽ��
            $listTable .= "        <td class='winbox' align='right' nowrap>". $homid_hour .":". $homid_min ."0</td>\n";
        } else if ($homid_min < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $homid_hour .":0". $homid_min ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $homid_hour .":". $homid_min ."</td>\n";
        }
        if ($legal_min == 0) {                                      // ˡ����ֽ���ɽ��
            $listTable .= "        <td class='winbox' align='right' nowrap>". $legal_hour .":". $legal_min ."0</td>\n";
        } else if ($legal_min < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $legal_hour .":0". $legal_min ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $legal_hour .":". $legal_min ."</td>\n";
        }
        if ($leover_min == 0) {                                     // ˡ��ĶȽ���ɽ��
            $listTable .= "        <td class='winbox' align='right' nowrap>". $leover_hour .":". $leover_min ."0</td>\n";
        } else if ($leover_min < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $leover_hour .":0". $leover_min ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $leover_hour .":". $leover_min ."</td>\n";
        }
        if ($late_min == 0) {                                       // �ٹ�������ֽ���ɽ��
            $listTable .= "        <td class='winbox' align='right' nowrap>". $late_hour .":". $late_min ."0</td>\n";
        } else if ($late_min < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $late_hour .":0". $late_min ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $late_hour .":". $late_min ."</td>\n";
        }
        //$listTable .= "        <td class='winbox'>��</td>\n";
        $listTable .= "</table>\n";                                 // �����ǡ�����ɽ��
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        $listTable .= "</center>\n";
        $listTable .= "<table width='40%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='2'>\n";
        if ($request->get('rows') <= 0) {
            $listTable .= "    <tr>\n";
            $listTable .= "        <td colspan='4' width='100%' align='center' class='winbox'>�ǡ���������ޤ���</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        } else {
            $listTable .= "    <tr>\n";
            $listTable .= "        <th class='winbox'>��������</th>\n";
            $listTable .= "        <th class='winbox'>�ж�����</th>\n";
            $listTable .= "        <th class='winbox'>�ٽ�����</th>\n";
            $listTable .= "        <th class='winbox'>ǯ������</th>\n";
            $listTable .= "        <th class='winbox'>���ֵٻ���</th>\n";
            $listTable .= "        <th class='winbox'>�������</th>\n";
            $listTable .= "        <th class='winbox'>�ٶ�����</th>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";        
            $listTable .= "        <td class='winbox'><div align='right'>". number_format($request->get('fixed_num'), 2) ."</div></td>\n";    // ��������ɽ��
            $work_num = $work_num - $howork_num - $request->get('hohalf_num');      // �ж������η׻� ���ǹ���֤Τ�������)-(�ٽ�����)-(ͭ������)
            if ($work_num < 0) {
                $work_num = 0;
            }
            $listTable .= "        <td class='winbox'><div align='right'>". number_format($work_num, 2) ."</div></td>\n";     // �ж�����ɽ��
            $listTable .= "        <td class='winbox'><div align='right'>". number_format($howork_num, 2) ."</div></td>\n";   // �ٽ�����ɽ��
            if ($request->get('paidho_num') == 0) {                                 // ͭ������ɽ��
                $listTable .= "        <td class='winbox'<div align='right'>0.00</div></td>\n";
            } else {
                $listTable .= "        <td class='winbox'><div align='right'>". number_format($request->get('paidho_num'), 2) ."</div></td>\n";
            }
            if ($request->get('hotime_num') == 0) {                                 // ���ֵٻ���ɽ��
                $listTable .= "        <td class='winbox'<div align='right'>0.00</div></td>\n";
            } else {
                $listTable .= "        <td class='winbox'><div align='right'>". number_format($request->get('hotime_num'), 2) ."</div></td>\n";
            }
            if ($request->get('noholy_num') == 0) {                                 // �������ɽ��
                $listTable .= "        <td class='winbox'<div align='right'>0.00</div></td>\n";
            } else {
                $listTable .= "        <td class='winbox'><div align='right'>". number_format($request->get('noholy_num'), 2) ."</div></td>\n";
            }
            $listTable .= "        <td class='winbox'><div align='right'>". number_format($request->get('closure_num'), 2) ."</div></td>\n";   // �ٽ�����ɽ��
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
            if ($request->get('rows') <= 0) {
            } else {
                if ($check_flg == 'n') {
                    
                } else {
                    $num = $request->get('rows') - 1;                                           // �ǡ������ʤ����դ���ꤷ�Ƥ�
                    $str_date = $working_data[0][1];                                            // �ǡ���������ǽ����ޤǤ�
                    $end_date = $working_data[$num][1];                                         // ���ꤹ��褦���б�
                    $request->add('str_date', $str_date);
                    $request->add('end_date', $end_date);
                    if (!getCheckAuthority(29)) {                                               // ��Ĺ�����ʾ�ϾȲ�Τ�
                        if ($_SESSION['User_ID'] != '970227') {                                 // �ݻ֤���ϾȲ�Τ�
                            if ($_SESSION['User_ID'] == '010472') {                             // ��̳��Ĺ ���в�Ĺ
                                if ($request->get('targetSection') == 5) {                      // ��̳�ݤϳ���Ǥ���
                                    if ($request->get('formal') == 'details') {                 // �ꥹ�ȷ����Υ����å�
                                        $listTable .= "
                                                    <form name='CorrectForm". $uid ."'  method='post' target='_parent'
                                                        onSubmit='return WorkingHoursReport.checkANDexecute(this, 1)'
                                                    >
                                        \n";
                                        $listTable .= "    <CENTER>\n";
                                        $listTable .= "        <select name='ConfirmFlg". $uid ."' class='pt12b' onchange='WorkingHoursReport.getSelected(" . $uid . ")'>\n";
                                        $listTable .= "            <option value='1'>����ʤ�</option>\n";
                                        $listTable .= "            <option value='2'>�Ͻ���к�</option>\n";
                                        $listTable .= "            <option value='3'>�Ͻа����</option>\n";
                                        $listTable .= "        </select>\n";
                                        $tnk_uid    = 'tnk' . $uid;
                                        $listTable .= "        <input type='button' name='correct2' value='�����ǧ' onClick='WorkingHoursReport.Confirmoneexecute(\"" . $tnk_uid . "\", " . $str_date . ", " . $end_date . ", " . $request->get('targetSection') . ", " . $request->get('targetSection') . ");' title='����å�����С��̥�����ɥ���ɽ�����ޤ���'>\n";
                                        $listTable .= "    </CENTER>\n";
                                        $listTable .= "</form>\n";
                                    }
                                } else if ($request->get('targetSection') == 31) {              // �и��Ԥϳ���Ǥ���
                                    if ($request->get('formal') == 'details') {                 // �ꥹ�ȷ����Υ����å�
                                        $listTable .= "
                                                    <form name='CorrectForm". $uid ."'  method='post' target='_parent'
                                                        onSubmit='return WorkingHoursReport.checkANDexecute(this, 1)'
                                                    >
                                        \n";
                                        $listTable .= "    <CENTER>\n";
                                        $listTable .= "        <select name='ConfirmFlg". $uid ."' class='pt12b' onchange='WorkingHoursReport.getSelected(" . $uid . ")'>\n";
                                        $listTable .= "            <option value='1'>����ʤ�</option>\n";
                                        $listTable .= "            <option value='2'>�Ͻ���к�</option>\n";
                                        $listTable .= "            <option value='3'>�Ͻа����</option>\n";
                                        $listTable .= "        </select>\n";
                                        $tnk_uid    = 'tnk' . $uid;
                                        $listTable .= "        <input type='button' name='correct2' value='�����ǧ' onClick='WorkingHoursReport.Confirmoneexecute(\"" . $tnk_uid . "\", " . $str_date . ", " . $end_date . ", " . $request->get('targetSection') . ", " . $request->get('targetSection') . ");' title='����å�����С��̥�����ɥ���ɽ�����ޤ���'>\n";
                                        $listTable .= "    </CENTER>\n";
                                        $listTable .= "</form>\n";
                                
                                    }
                                } else if ($request->get('targetSection') == (-3)) {              // 8�鿦�ʾ�ϳ���Ǥ���
                                    if ($request->get('formal') == 'details') {                 // �ꥹ�ȷ����Υ����å�
                                        $listTable .= "
                                                    <form name='CorrectForm". $uid ."'  method='post' target='_parent'
                                                        onSubmit='return WorkingHoursReport.checkANDexecute(this, 1)'
                                                    >
                                        \n";
                                        $listTable .= "    <CENTER>\n";
                                        $listTable .= "        <select name='ConfirmFlg". $uid ."' class='pt12b' onchange='WorkingHoursReport.getSelected(" . $uid . ")'>\n";
                                        $query = sprintf("SELECT confirm_flg FROM working_hours_report_confirm WHERE uid='%s' AND working_date=%d", $uid, $str_date);
                                        $res_chk = array();
                                        if ( getResult($query, $res_chk) > 0 ) {    // ��Ͽ���� <option>����
                                            if ($res_chk[0] == 1) {
                                                $listTable .= "            <option value='1' selected>����ʤ�</option>\n";
                                                $listTable .= "            <option value='2'>�Ͻ���к�</option>\n";
                                                $listTable .= "            <option value='3'>�Ͻа����</option>\n";
                                            } elseif ($res_chk[0] == 2) {
                                                $listTable .= "            <option value='1'>����ʤ�</option>\n";
                                                $listTable .= "            <option value='2' selected>�Ͻ���к�</option>\n";
                                                $listTable .= "            <option value='3'>�Ͻа����</option>\n";
                                            } elseif ($res_chk[0] == 3) {
                                                $listTable .= "            <option value='1'>����ʤ�</option>\n";
                                                $listTable .= "            <option value='2'>�Ͻ���к�</option>\n";
                                                $listTable .= "            <option value='3' selected>�Ͻа����</option>\n";
                                            }
                                        } else {                                    // ��Ͽ�ʤ�<option>̤����
                                            $listTable .= "            <option value='1' selected>����ʤ�</option>\n";
                                            $listTable .= "            <option value='2'>�Ͻ���к�</option>\n";
                                            $listTable .= "            <option value='3'>�Ͻа����</option>\n";
                                        }
                                        $listTable .= "        </select>\n";
                                        $tnk_uid    = 'tnk' . $uid;
                                        $listTable .= "        <input type='button' name='correct2' value='�����ǧ' onClick='WorkingHoursReport.Confirmoneexecute(\"" . $tnk_uid . "\", " . $str_date . ", " . $end_date . ", " . $request->get('targetSection') . ", " . $request->get('targetSection') . ");' title='����å�����С��̥�����ɥ���ɽ�����ޤ���'>\n";
                                        $listTable .= "    </CENTER>\n";
                                        $listTable .= "</form>\n";
                                    }
                                }
                            } else {
                                if ($request->get('formal') == 'details') {                 // �ꥹ�ȷ����Υ����å�
                                    $listTable .= "
                                                    <form name='CorrectForm". $uid ."'  method='post' target='_parent'
                                                        onSubmit='return WorkingHoursReport.checkANDexecute(this, 1)'
                                                    >
                                    \n";
                                    $listTable .= "    <CENTER>\n";
                                    $listTable .= "        <select name='ConfirmFlg". $uid ."' class='pt12b' onchange='WorkingHoursReport.getSelected(" . $uid . ")'>\n";
                                    $query = sprintf("SELECT confirm_flg FROM working_hours_report_confirm WHERE uid='%s' AND working_date=%d", $uid, $str_date);
                                    $res_chk = array();
                                    if ( getResult($query, $res_chk) > 0 ) {    // ��Ͽ���� <option>����
                                        if ($res_chk[0][0] == 1) {
                                            $listTable .= "            <option value='1' selected>����ʤ�</option>\n";
                                            $listTable .= "            <option value='2'>�Ͻ���к�</option>\n";
                                            $listTable .= "            <option value='3'>�Ͻа����</option>\n";
                                        } elseif ($res_chk[0][0] == 2) {
                                            $listTable .= "            <option value='1'>����ʤ�</option>\n";
                                            $listTable .= "            <option value='2' selected>�Ͻ���к�</option>\n";
                                            $listTable .= "            <option value='3'>�Ͻа����</option>\n";
                                        } elseif ($res_chk[0][0] == 3) {
                                            $listTable .= "            <option value='1'>����ʤ�</option>\n";
                                            $listTable .= "            <option value='2'>�Ͻ���к�</option>\n";
                                            $listTable .= "            <option value='3' selected>�Ͻа����</option>\n";
                                        } else {
                                            $listTable .= "            <option value='1' selected>����ʤ�</option>\n";
                                            $listTable .= "            <option value='2'>�Ͻ���к�</option>\n";
                                            $listTable .= "            <option value='3'>�Ͻа����</option>\n";
                                        }
                                    } else {                                    // ��Ͽ�ʤ�<option>̤����
                                        $listTable .= "            <option value='1' selected>����ʤ�</option>\n";
                                        $listTable .= "            <option value='2'>�Ͻ���к�</option>\n";
                                        $listTable .= "            <option value='3'>�Ͻа����</option>\n";
                                    }
                                    $listTable .= "        </select>\n";
                                    $tnk_uid    = 'tnk' . $uid;
                                    $listTable .= "        <input type='button' name='correct2' value='�����ǧ' onClick='WorkingHoursReport.Confirmoneexecute(\"" . $tnk_uid . "\", " . $str_date . ", " . $end_date . ", " . $request->get('targetSection') . ", " . $request->get('targetSection') . ");' title='����å�����С��̥�����ɥ���ɽ�����ޤ���'>\n";
                                    $listTable .= "    </CENTER>\n";
                                    $listTable .= "</form>\n";
                                }
                            }
                        }
                    }
                }
            }
            $listTable .= "<BR>\n";
        }
        if ($uid == '014737') {                                 // �и��ԤμҰ��ֹ���Ѵ�
             $uid = '914737';                                   // 014737=��̳�� �񤵤�
        } else if ($uid == '020206') {                          // 020206=���Ѳ� ��������
             $uid = '920206';
        }
        if ($uid != '914737') {                                 // ���פη׻��ʽи��ԤϽ�����
            if ($uid != '920206') {
                if (substr($uid, 0, 1) == '9') {
                    $total_fixed_time_p    = $request->get('total_fixed_time_p') + $fixed_time;       // ������ַ�
                    $request->add('total_fixed_time_p', $total_fixed_time_p);
                    $total_extend_time_p   = $request->get('total_extend_time_p') + $extend_time;     // ��Ĺ���ַ�
                    $request->add('total_extend_time_p', $total_extend_time_p);
                    $total_overtime_p      = $request->get('total_overtime_p') + $overtime;           // ��лĶȻ��ַ�
                    $request->add('total_overtime_p', $total_overtime_p);
                    $total_midnight_over_p = $request->get('total_midnight_over_p') + $midnight_over; // ��лĶȻ��ַ�
                    $request->add('total_midnight_over_p', $total_midnight_over_p);
                    $total_holiday_time_p  = $request->get('total_holiday_time_p') + $holiday_time;   // �ٽл��ַ�
                    $request->add('total_holiday_time_p', $total_holiday_time_p);
                    $total_holiday_over_p  = $request->get('total_holiday_over_p') + $holiday_over;   // �ٽлĶȷ�
                    $request->add('total_holiday_over_p', $total_holiday_over_p);
                    $total_holiday_mid_p   = $request->get('total_holiday_mid_p') + $holiday_mid;     // �ٽп����
                    $request->add('total_holiday_mid_p', $total_holiday_mid_p);
                    $total_legal_time_p    = $request->get('total_legal_time_p') + $legal_time;       // ˡ����ַ�
                    $request->add('total_legal_time_p', $total_legal_time_p);
                    $total_legal_over_p    = $request->get('total_legal_over_p') + $legal_over;       // ˡ��Ķȷ�
                    $request->add('total_legal_over_p', $total_legal_over_p);
                    $total_late_time_p     = $request->get('total_late_time_p') + $late_time;         // �ٹ������
                    $request->add('total_late_time_p', $total_late_time_p);
                } else {
                    $total_fixed_time_s    = $request->get('total_fixed_time_s') + $fixed_time;       // ������ַ�
                    $request->add('total_fixed_time_s', $total_fixed_time_s);
                    $total_extend_time_s   = $request->get('total_extend_time_s') + $extend_time;     // ��Ĺ���ַ�
                    $request->add('total_extend_time_s', $total_extend_time_s);
                    $total_overtime_s      = $request->get('total_overtime_s') + $overtime;           // ��лĶȻ��ַ�
                    $request->add('total_overtime_s', $total_overtime_s);
                    $total_midnight_over_s = $request->get('total_midnight_over_s') + $midnight_over; // ��лĶȻ��ַ�
                    $request->add('total_midnight_over_s', $total_midnight_over_s);
                    $total_holiday_time_s  = $request->get('total_holiday_time_s') + $holiday_time;   // �ٽл��ַ�
                    $request->add('total_holiday_time_s', $total_holiday_time_s);
                    $total_holiday_over_s  = $request->get('total_holiday_over_s') + $holiday_over;   // �ٽлĶȷ�
                    $request->add('total_holiday_over_s', $total_holiday_over_s);
                    $total_holiday_mid_s   = $request->get('total_holiday_mid_s') + $holiday_mid;     // �ٽп����
                    $request->add('total_holiday_mid_s', $total_holiday_mid_s);
                    $total_legal_time_s    = $request->get('total_legal_time_s') + $legal_time;       // ˡ����ַ�
                    $request->add('total_legal_time_s', $total_legal_time_s);
                    $total_legal_over_s    = $request->get('total_legal_over_s') + $legal_over;       // ˡ��Ķȷ�
                    $request->add('total_legal_over_s', $total_legal_over_s);
                    $total_late_time_s     = $request->get('total_late_time_s') + $late_time;         // �ٹ������
                    $request->add('total_late_time_s', $total_late_time_s);
                }
                $total_fixed_time    = $request->get('total_fixed_time_p') + $request->get('total_fixed_time_s');       // ������ַ�
                $request->add('total_fixed_time', $total_fixed_time);
                $total_extend_time   = $request->get('total_extend_time_p') + $request->get('total_extend_time_s');     // ��Ĺ���ַ�
                $request->add('total_extend_time', $total_extend_time);
                $total_overtime      = $request->get('total_overtime_p') + $request->get('total_overtime_s');           // ��лĶȻ��ַ�
                $request->add('total_overtime', $total_overtime);
                $total_midnight_over = $request->get('total_midnight_over_p') + $request->get('total_midnight_over_s'); // ��лĶȻ��ַ�
                $request->add('total_midnight_over', $total_midnight_over);
                $total_holiday_time  = $request->get('total_holiday_time_p') + $request->get('total_holiday_time_s');   // �ٽл��ַ�
                $request->add('total_holiday_time', $total_holiday_time);
                $total_holiday_over  = $request->get('total_holiday_over_p') + $request->get('total_holiday_over_s');   // �ٽлĶȷ�
                $request->add('total_holiday_over', $total_holiday_over);
                $total_holiday_mid   = $request->get('total_holiday_mid_p') + $request->get('total_holiday_mid_s');     // �ٽп����
                $request->add('total_holiday_mid', $total_holiday_mid);
                $total_legal_time    = $request->get('total_legal_time_p') + $request->get('total_legal_time_s');       // ˡ����ַ�
                $request->add('total_legal_time', $total_legal_time);
                $total_legal_over    = $request->get('total_legal_over_p') + $request->get('total_legal_over_s');       // ˡ��Ķȷ�
                $request->add('total_legal_over', $total_legal_over);
                $total_late_time     = $request->get('total_late_time_p') + $request->get('total_late_time_s');         // �ٹ������
                $request->add('total_late_time', $total_late_time);
            }
        }
        return $listTable;
    }
    
    ///// ��°����ID�������̾�����
    private function getSectionName($rows,$res)
    {
        $s_name = array();
        $res_n  = array();
        for ($i=0; $i<$rows; $i++) {
            $query="select section_name -- 00
                    from section_master
                    where sid={$res[$i]['sid']}
            ";
            if ($rows_n=getResult($query,$res_n)) {
                $s_name[$i] = $res_n[0][0];
            } else {
                $s_name[$i] = '----------';
            }
            $res_n  = array();
        }
        return $s_name;
    }
    
    ///// �Ұ��ֹ椬���ꤵ�줿��������̾�μ���
    private function getSectionNameOne($uid)
    {
        
        $query="select sid   -- 00
                from user_detailes
                where uid='{$uid}'
        ";
        $res = array();
        if ($rows=getResult($query,$res)) {
            $sid = $res[0][0];
        } else {
            $s_name = '----------';
            return $s_name;
        }
        $query="select section_name -- 00
                from section_master
                where sid={$sid}
        ";
        if ($rows=getResult($query,$res)) {
            $s_name = $res[0][0];
            return $s_name;
        } else {
            $s_name = '----------';
            return $s_name;
        }
    }
    
    ///// ����ID�����°�Ұ��ֹ�����
    private function getSectionUser($sid, $position, $sdate, $edate)
    {
        if ($position == '') {              // ���٤Ƥο���
            if ($sid == (-2)) {                    // ���ƤμҰ�No.����(���鿦�ʾ�Ͻ���)
                 $query="select u.uid -- 00
                             ,u.class -- 01
                             ,u.sid   -- 02
                             ,c.act_id -- 03
                         from user_detailes as u
                         left outer join cd_table as c
                         on u.uid=c.uid
                         where (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15' and u.sid<>95
                         ORDER BY u.sid, c.act_id, u.uid
                ";
                /* 8�鿦 ������
                $query="select uid -- 00
                             ,class -- 01
                             ,sid   -- 02
                         from user_detailes
                         where (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                         AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                         and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                         ORDER BY sid, uid
                ";
                */
            } else if ($sid == (-3)) {              // ���鿦�ʾ�μҰ��ֹ�����
                $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where (u.class LIKE '8%' OR u.class LIKE '9%' OR u.class LIKE '10%' 
                        OR u.class LIKE '11%' OR u.class LIKE '12%') 
                        and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15' and u.sid<>95
                        ORDER BY c.act_id, u.uid
                ";
            } else if ($sid == (-4)) {              // �Ķ�ͭ��Τ� 8�鿦�ʾ�Ͻ���
                $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where (u.class NOT LIKE '8%' AND u.class NOT LIKE '9%' AND u.class NOT LIKE '10%' 
                        AND u.class NOT LIKE '11%' AND u.class NOT LIKE '12%' OR u.class IS NULL) AND (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15' and u.sid<>95
                        ORDER BY u.sid, c.act_id, u.uid
                ";
            } else if ($sid == (-5)) {              // ���顼�Τߡʼ�����-2�����Τ�Ʊ����
                 $query="select u.uid -- 00
                             ,u.class -- 01
                             ,u.sid   -- 02
                             ,c.act_id -- 03
                         from user_detailes as u
                         left outer join cd_table as c
                         on u.uid=c.uid
                         where (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15' and u.sid<>95
                         ORDER BY u.sid, c.act_id, u.uid
                ";
                /* 8�鿦 ������
                $query="select uid -- 00
                             ,class -- 01
                             ,sid   -- 02
                         from user_detailes
                         where (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                         AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                         and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                         ORDER BY sid, uid
                ";
                */
            } else {                               // ���򤵤줿����ID����°�Ұ��ֹ������ʣ��鿦�ʾ�Ͻ�����
                if ($sid == 36) {                  // ������ ��Ω���ݤˤ�sid=8���������ɲ�(��̳����ʬ�ΰ�)
                    $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where u.sid={$sid} 
                        and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15'
                        ORDER BY c.act_id, u.uid
                    ";
                    /* 8�鿦������
                    $query="select uid -- 00
                            ,class -- 01
                            ,sid   -- 02
                        from user_detailes
                        where (sid={$sid} OR sid=8) and (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                        AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                        and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                        ORDER BY uid
                    ";
                    */
                } else if ($sid == 5) {           //��̳�ݤ�ISO�Ƚи��Ԥ�ޤ� �ʤ���Ĺ�Ͻ������뤳��
                        $query="select u.uid -- 00
                            ,u.class  -- 01
                            ,u.sid    -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where (u.sid={$sid} OR u.sid=30 OR u.sid=31) 
                        and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15'
                        ORDER BY c.act_id, u.uid
                        ";
                        /*  ��Ĺ�ʾ�ޤ���
                        $query="select uid -- 00
                            ,class -- 01
                            ,sid   -- 02
                        from user_detailes
                        where (sid={$sid} OR sid=8 OR sid=9 OR sid=17 OR sid=31 OR sid=38 OR sid=99) 
                        and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000' and uid != '010367'
                        ORDER BY uid
                        ";
                        */
                        /* 8�鿦������
                        $query="select uid -- 00
                            ,class -- 01
                            ,sid   -- 02
                        from user_detailes
                        where (sid={$sid} OR sid=17) and (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                        AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                        and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                        ORDER BY uid
                        ";
                        */
                } else if ($sid == 34) {           // ��¤����¤���ݤˤ�sid=17��¤�����ɲ�(��̳��̾ʬ�ΰ�)
                        $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where u.sid={$sid} and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15'
                        ORDER BY c.act_id, u.uid
                        ";
                        /* 8�鿦������ ������¤�������äƤ���
                        $query="select uid -- 00
                            ,class -- 01
                            ,sid   -- 02
                        from user_detailes
                        where (sid={$sid} OR sid=17) and (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                        AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                        and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                        ORDER BY uid
                        ";
                        */
                } else {
                        $query="select u.uid -- 00
                                ,u.class -- 01
                                ,u.sid   -- 02
                                ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where u.sid={$sid} 
                        and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15' and u.sid<>95
                        ORDER BY c.act_id, u.uid
                    ";
                    /* 8�鿦������
                    $query="select uid -- 00
                                ,class -- 01
                                ,sid   -- 02
                        from user_detailes
                        where sid={$sid} and (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                        AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                        and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                        ORDER BY uid
                    */
                }
            }
        } elseif ($position == '1') {       // �Ұ��ʥѡ��ȡ��ѡ��ȥ����åա�����������Ұ��ʳ���
            if ($sid == (-2)) {                    // ���ƤμҰ�No.����(���鿦�ʾ�Ͻ���)
                 $query="select u.uid -- 00
                             ,u.class -- 01
                             ,u.sid   -- 02
                             ,c.act_id -- 03
                         from user_detailes as u
                         left outer join cd_table as c
                         on u.uid=c.uid
                         where (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15' and u.pid != '5' and u.pid != '6' and u.pid != '8' and u.pid != '9' and u.sid<>95
                         ORDER BY u.sid, c.act_id, u.uid
                ";
                //  and pid != '5' �ѡ��� and pid != '6' �ѡ��ȥ����å� and pid != '8' ���� and pid != '9' ����Ұ�
                /* 8�鿦 ������
                $query="select uid -- 00
                             ,class -- 01
                             ,sid   -- 02
                         from user_detailes
                         where (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                         AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                         and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                         ORDER BY sid, uid
                ";
                */
            } else if ($sid == (-3)) {              // ���鿦�ʾ�μҰ��ֹ�����
                $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where (u.class LIKE '8%' OR u.class LIKE '9%' OR u.class LIKE '10%' 
                        OR u.class LIKE '11%' OR u.class LIKE '12%') 
                        and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15' and u.pid != '5' and u.pid != '6' and u.pid != '8' and u.pid != '9' and u.sid<>95
                        ORDER BY c.act_id, u.uid
                ";
            } else if ($sid == (-4)) {              // �Ķ�ͭ��Τ� 8�鿦�ʾ�Ͻ���
                $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where (u.class NOT LIKE '8%' AND u.class NOT LIKE '9%' AND u.class NOT LIKE '10%' 
                        AND u.class NOT LIKE '11%' AND u.class NOT LIKE '12%' OR u.class IS NULL) AND (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15' and u.pid != '5' and u.pid != '6' and u.pid != '8' and u.pid != '9' and u.sid<>95
                        ORDER BY u.sid, c.act_id, u.uid
                ";
            } else if ($sid == (-5)) {              // ���顼�Τߡʼ�����-2�����Τ�Ʊ����
                 $query="select u.uid -- 00
                             ,u.class -- 01
                             ,u.sid   -- 02
                             ,c.act_id -- 03
                         from user_detailes as u
                         left outer join cd_table as c
                         on u.uid=c.uid
                         where (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15' and u.pid != '5' and u.pid != '6' and u.pid != '8' and u.pid != '9' and u.sid<>95
                         ORDER BY u.sid, c.act_id, u.uid
                ";
                /* 8�鿦 ������
                $query="select uid -- 00
                             ,class -- 01
                             ,sid   -- 02
                         from user_detailes
                         where (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                         AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                         and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                         ORDER BY sid, uid
                ";
                */
            } else {                               // ���򤵤줿����ID����°�Ұ��ֹ������ʣ��鿦�ʾ�Ͻ�����
                if ($sid == 36) {                  // ������ ��Ω���ݤˤ�sid=8���������ɲ�(��̳����ʬ�ΰ�)
                    $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where u.sid={$sid} 
                        and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15' and u.pid != '5' and u.pid != '6' and u.pid != '8' and u.pid != '9'
                        ORDER BY c.act_id, u.uid
                    ";
                    /* 8�鿦������
                    $query="select uid -- 00
                            ,class -- 01
                            ,sid   -- 02
                        from user_detailes
                        where (sid={$sid} OR sid=8) and (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                        AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                        and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                        ORDER BY uid
                    ";
                    */
                } else if ($sid == 5) {           //��̳�ݤ�ISO�Ƚи��Ԥ�ޤ� �ʤ���Ĺ�Ͻ������뤳��
                        $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where (u.sid={$sid} OR u.sid=30 OR u.sid=31) 
                        and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15' and u.pid != '5' and u.pid != '6' and u.pid != '8' and u.pid != '9'
                        ORDER BY c.act_id, u.uid
                        ";
                        /*  ��Ĺ�ʾ�ޤ���
                        $query="select uid -- 00
                            ,class -- 01
                            ,sid   -- 02
                        from user_detailes
                        where (sid={$sid} OR sid=8 OR sid=9 OR sid=17 OR sid=31 OR sid=38 OR sid=99) 
                        and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000' and uid != '010367'
                        ORDER BY uid
                        ";
                        */
                        /* 8�鿦������
                        $query="select uid -- 00
                            ,class -- 01
                            ,sid   -- 02
                        from user_detailes
                        where (sid={$sid} OR sid=17) and (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                        AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                        and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                        ORDER BY uid
                        ";
                        */
                } else if ($sid == 34) {           // ��¤����¤���ݤˤ�sid=17��¤�����ɲ�(��̳��̾ʬ�ΰ�)
                        $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where u.sid={$sid} and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15' and u.pid != '5' and u.pid != '6' and u.pid != '8' and u.pid != '9'
                        ORDER BY c.act_id, u.uid
                        ";
                        /* 8�鿦������ ������¤�������äƤ���
                        $query="select uid -- 00
                            ,class -- 01
                            ,sid   -- 02
                        from user_detailes
                        where (sid={$sid} OR sid=17) and (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                        AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                        and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                        ORDER BY uid
                        ";
                        */
                } else {
                        $query="select u.uid -- 00
                                ,u.class -- 01
                                ,u.sid   -- 02
                                ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where u.sid={$sid} 
                        and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15' and u.pid != '5' and u.pid != '6' and u.pid != '8' and u.pid != '9' and u.sid<>95
                        ORDER BY c.act_id, u.uid
                    ";
                    /* 8�鿦������
                    $query="select uid -- 00
                                ,class -- 01
                                ,sid   -- 02
                        from user_detailes
                        where sid={$sid} and (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                        AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                        and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                        ORDER BY uid
                    */
                }
            }
        } elseif ($position == '2') {       // �ѡ��ȡ��ѡ��ȥ����åդΤ�
            if ($sid == (-2)) {                    // ���ƤμҰ�No.����(���鿦�ʾ�Ͻ���)
                 $query="select u.uid -- 00
                             ,u.class -- 01
                             ,u.sid   -- 02
                             ,c.act_id -- 03
                         from user_detailes as u
                         left outer join cd_table as c
                         on u.uid=c.uid
                         where (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15'  and (u.pid = '5' or u.pid = '6') and u.sid<>95
                         ORDER BY u.sid, c.act_id, u.uid
                ";
                //  and pid != '5' �ѡ��� and pid != '6' �ѡ��ȥ����å� and pid != '8' ���� and pid != '9' ����Ұ�
                /* 8�鿦 ������
                $query="select uid -- 00
                             ,class -- 01
                             ,sid   -- 02
                         from user_detailes
                         where (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                         AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                         and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                         ORDER BY sid, uid
                ";
                */
            } else if ($sid == (-3)) {              // ���鿦�ʾ�μҰ��ֹ�����
                $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where (u.class LIKE '8%' OR u.class LIKE '9%' OR u.class LIKE '10%' 
                        OR u.class LIKE '11%' OR u.class LIKE '12%') 
                        and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15'  and (u.pid = '5' or u.pid = '6') and u.sid<>95
                        ORDER BY c.act_id, u.uid
                ";
            } else if ($sid == (-4)) {              // �Ķ�ͭ��Τ� 8�鿦�ʾ�Ͻ���
                $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where (u.class NOT LIKE '8%' AND u.class NOT LIKE '9%' AND u.class NOT LIKE '10%' 
                        AND u.class NOT LIKE '11%' AND u.class NOT LIKE '12%' OR u.class IS NULL) AND (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15'  and (u.pid = '5' or u.pid = '6') and u.sid<>95
                        ORDER BY u.sid, c.act_id, u.uid
                ";
            } else if ($sid == (-5)) {              // ���顼�Τߡʼ�����-2�����Τ�Ʊ����
                 $query="select u.uid -- 00
                             ,u.class -- 01
                             ,u.sid   -- 02
                             ,c.act_id -- 03
                         from user_detailes as u
                         left outer join cd_table as c
                         on u.uid=c.uid
                         where (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15'  and (u.pid = '5' or u.pid = '6') and u.sid<>95
                         ORDER BY u.sid, c.act_id, u.uid
                ";
                /* 8�鿦 ������
                $query="select uid -- 00
                             ,class -- 01
                             ,sid   -- 02
                         from user_detailes
                         where (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                         AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                         and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                         ORDER BY sid, uid
                ";
                */
            } else {                               // ���򤵤줿����ID����°�Ұ��ֹ������ʣ��鿦�ʾ�Ͻ�����
                if ($sid == 36) {                  // ������ ��Ω���ݤˤ�sid=8���������ɲ�(��̳����ʬ�ΰ�)
                    $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where u.sid={$sid} 
                        and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15'  and (u.pid = '5' or u.pid = '6')
                        ORDER BY c.act_id, u.uid
                    ";
                    /* 8�鿦������
                    $query="select uid -- 00
                            ,class -- 01
                            ,sid   -- 02
                        from user_detailes
                        where (sid={$sid} OR sid=8) and (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                        AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                        and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                        ORDER BY uid
                    ";
                    */
                } else if ($sid == 5) {           //��̳�ݤ�ISO�Ƚи��Ԥ�ޤ� �ʤ���Ĺ�Ͻ������뤳��
                        $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where (u.sid={$sid} OR u.sid=30 OR u.sid=31) 
                        and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15' and (u.pid = '5' or u.pid = '6')
                        ORDER BY c.act_id, u.uid
                        ";
                        /*  ��Ĺ�ʾ�ޤ���
                        $query="select uid -- 00
                            ,class -- 01
                            ,sid   -- 02
                        from user_detailes
                        where (sid={$sid} OR sid=8 OR sid=9 OR sid=17 OR sid=31 OR sid=38 OR sid=99) 
                        and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000' and uid != '010367'
                        ORDER BY uid
                        ";
                        */
                        /* 8�鿦������
                        $query="select uid -- 00
                            ,class -- 01
                            ,sid   -- 02
                        from user_detailes
                        where (sid={$sid} OR sid=17) and (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                        AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                        and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                        ORDER BY uid
                        ";
                        */
                } else if ($sid == 34) {           // ��¤����¤���ݤˤ�sid=17��¤�����ɲ�(��̳��̾ʬ�ΰ�)
                        $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where u.sid={$sid} and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15' and (u.pid = '5' or u.pid = '6')
                        ORDER BY c.act_id, u.uid
                        ";
                        /* 8�鿦������ ������¤�������äƤ���
                        $query="select uid -- 00
                            ,class -- 01
                            ,sid   -- 02
                        from user_detailes
                        where (sid={$sid} OR sid=17) and (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                        AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                        and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                        ORDER BY uid
                        ";
                        */
                } else {
                        $query="select u.uid -- 00
                                ,u.class -- 01
                                ,u.sid   -- 02
                                ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where u.sid={$sid} 
                        and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15' and (u.pid = '5' or u.pid = '6') and u.sid<>95
                        ORDER BY c.act_id, u.uid
                    ";
                    /* 8�鿦������
                    $query="select uid -- 00
                                ,class -- 01
                                ,sid   -- 02
                        from user_detailes
                        where sid={$sid} and (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                        AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                        and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                        ORDER BY uid
                    */
                }
            }
        } elseif ($position == '3') {       // ���󡦤���¾
            if ($sid == (-2)) {                    // ���ƤμҰ�No.����(���鿦�ʾ�Ͻ���)
                 $query="select u.uid -- 00
                             ,u.class -- 01
                             ,u.sid   -- 02
                             ,c.act_id -- 03
                         from user_detailes as u
                         left outer join cd_table as c
                         on u.uid=c.uid
                         where (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15'  and (u.pid = '8' or u.pid = '9') and u.sid<>95
                         ORDER BY u.sid, c.act_id, u.uid
                ";
                //  and pid != '5' �ѡ��� and pid != '6' �ѡ��ȥ����å� and pid != '8' ���� and pid != '9' ����Ұ�
                /* 8�鿦 ������
                $query="select uid -- 00
                             ,class -- 01
                             ,sid   -- 02
                         from user_detailes
                         where (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                         AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                         and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                         ORDER BY sid, uid
                ";
                */
            } else if ($sid == (-3)) {              // ���鿦�ʾ�μҰ��ֹ�����
                $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where (u.class LIKE '8%' OR u.class LIKE '9%' OR u.class LIKE '10%' 
                        OR u.class LIKE '11%' OR u.class LIKE '12%') 
                        and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15'  and (u.pid = '8' or u.pid = '9') and u.sid<>95
                        ORDER BY c.act_id, u.uid
                ";
            } else if ($sid == (-4)) {              // �Ķ�ͭ��Τ� 8�鿦�ʾ�Ͻ���
                $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where (u.class NOT LIKE '8%' AND u.class NOT LIKE '9%' AND u.class NOT LIKE '10%' 
                        AND u.class NOT LIKE '11%' AND u.class NOT LIKE '12%' OR u.class IS NULL) AND (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15'  and (u.pid = '8' or u.pid = '9') and u.sid<>95
                        ORDER BY u.sid, c.act_id, u.uid
                ";
            } else if ($sid == (-5)) {              // ���顼�Τߡʼ�����-2�����Τ�Ʊ����
                 $query="select u.uid -- 00
                             ,u.class -- 01
                             ,u.sid   -- 02
                             ,c.act_id -- 03
                         from user_detailes as u
                         left outer join cd_table as c
                         on u.uid=c.uid
                         where (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15'  and (u.pid = '8' or u.pid = '9') and u.sid<>95
                         ORDER BY u.sid, c.act_id, u.uid
                ";
                /* 8�鿦 ������
                $query="select uid -- 00
                             ,class -- 01
                             ,sid   -- 02
                         from user_detailes
                         where (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                         AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                         and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                         ORDER BY sid, uid
                ";
                */
            } else {                               // ���򤵤줿����ID����°�Ұ��ֹ������ʣ��鿦�ʾ�Ͻ�����
                if ($sid == 36) {                  // ������ ��Ω���ݤˤ�sid=8���������ɲ�(��̳����ʬ�ΰ�)
                    $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where u.sid={$sid} 
                        and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15'  and (u.pid = '8' or u.pid = '9')
                        ORDER BY c.act_id, u.uid
                    ";
                    /* 8�鿦������
                    $query="select uid -- 00
                            ,class -- 01
                            ,sid   -- 02
                        from user_detailes
                        where (sid={$sid} OR sid=8) and (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                        AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                        and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                        ORDER BY uid
                    ";
                    */
                } else if ($sid == 5) {           //��̳�ݤ�ISO�Ƚи��Ԥ�ޤ� �ʤ���Ĺ�Ͻ������뤳��
                        $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where (u.sid={$sid} OR u.sid=30 OR u.sid=31) 
                        and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15' and (u.pid = '8' or u.pid = '9')
                        ORDER BY c.act_id, u.uid
                        ";
                        /*  ��Ĺ�ʾ�ޤ���
                        $query="select uid -- 00
                            ,class -- 01
                            ,sid   -- 02
                        from user_detailes
                        where (sid={$sid} OR sid=8 OR sid=9 OR sid=17 OR sid=31 OR sid=38 OR sid=99) 
                        and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000' and uid != '010367'
                        ORDER BY uid
                        ";
                        */
                        /* 8�鿦������
                        $query="select uid -- 00
                            ,class -- 01
                            ,sid   -- 02
                        from user_detailes
                        where (sid={$sid} OR sid=17) and (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                        AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                        and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                        ORDER BY uid
                        ";
                        */
                } else if ($sid == 34) {           // ��¤����¤���ݤˤ�sid=17��¤�����ɲ�(��̳��̾ʬ�ΰ�)
                        $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where u.sid={$sid} and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15' and (u.pid = '8' or u.pid = '9')
                        ORDER BY c.act_id, u.uid
                        ";
                        /* 8�鿦������ ������¤�������äƤ���
                        $query="select uid -- 00
                            ,class -- 01
                            ,sid   -- 02
                        from user_detailes
                        where (sid={$sid} OR sid=17) and (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                        AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                        and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                        ORDER BY uid
                        ";
                        */
                } else {
                        $query="select u.uid -- 00
                                ,u.class -- 01
                                ,u.sid   -- 02
                                ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where u.sid={$sid} 
                        and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15' and (u.pid = '8' or u.pid = '9') and u.sid<>95
                        ORDER BY c.act_id, u.uid
                    ";
                    /* 8�鿦������
                    $query="select uid -- 00
                                ,class -- 01
                                ,sid   -- 02
                        from user_detailes
                        where sid={$sid} and (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                        AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                        and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                        ORDER BY uid
                    */
                }
            }
        } elseif ($position == '4') {       // ��Ĺ�����ʾ�
            if ($sid == (-2)) {                    // ���ƤμҰ�No.����(���鿦�ʾ�Ͻ���)
                 $query="select u.uid -- 00
                             ,u.class -- 01
                             ,u.sid   -- 02
                             ,c.act_id -- 03
                         from user_detailes as u
                         left outer join cd_table as c
                         on u.uid=c.uid
                         where (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15'  and (u.pid = '46' or u.pid = '47' or u.pid = '50' or u.pid = '60' or u.pid = '70' or u.pid = '95' or u.pid = '110') and u.sid<>95
                         ORDER BY u.sid, c.act_id, u.uid
                ";
                //  and pid != '5' �ѡ��� and pid != '6' �ѡ��ȥ����å� and pid != '8' ���� and pid != '9' ����Ұ�
                /* 8�鿦 ������
                $query="select uid -- 00
                             ,class -- 01
                             ,sid   -- 02
                         from user_detailes
                         where (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                         AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                         and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                         ORDER BY sid, uid
                ";
                */
            } else if ($sid == (-3)) {              // ���鿦�ʾ�μҰ��ֹ�����
                $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where (u.class LIKE '8%' OR u.class LIKE '9%' OR u.class LIKE '10%' 
                        OR u.class LIKE '11%' OR u.class LIKE '12%') 
                        and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15'  and (u.pid = '46' or u.pid = '47' or u.pid = '50' or u.pid = '60' or u.pid = '70' or u.pid = '95' or u.pid = '110') and u.sid<>95
                        ORDER BY c.act_id, u.uid
                ";
            } else if ($sid == (-4)) {              // �Ķ�ͭ��Τ� 8�鿦�ʾ�Ͻ���
                $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where (u.class NOT LIKE '8%' AND u.class NOT LIKE '9%' AND u.class NOT LIKE '10%' 
                        AND u.class NOT LIKE '11%' AND u.class NOT LIKE '12%' OR u.class IS NULL) AND (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15'  and (u.pid = '46' or u.pid = '47' or u.pid = '50' or u.pid = '60' or u.pid = '70' or u.pid = '95' or u.pid = '110') and u.sid<>95
                        ORDER BY u.sid, c.act_id, u.uid
                ";
            } else if ($sid == (-5)) {              // ���顼�Τߡʼ�����-2�����Τ�Ʊ����
                 $query="select u.uid -- 00
                             ,u.class -- 01
                             ,u.sid   -- 02
                             ,c.act_id -- 03
                         from user_detailes as u
                         left outer join cd_table as c
                         on u.uid=c.uid
                         where (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15'  and (u.pid = '46' or u.pid = '47' or u.pid = '50' or u.pid = '60' or u.pid = '70' or u.pid = '95' or u.pid = '110') and u.sid<>95
                         ORDER BY u.sid, c.act_id, u.uid
                ";
                /* 8�鿦 ������
                $query="select uid -- 00
                             ,class -- 01
                             ,sid   -- 02
                         from user_detailes
                         where (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                         AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                         and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                         ORDER BY sid, uid
                ";
                */
            } else {                               // ���򤵤줿����ID����°�Ұ��ֹ������ʣ��鿦�ʾ�Ͻ�����
                if ($sid == 36) {                  // ������ ��Ω���ݤˤ�sid=8���������ɲ�(��̳����ʬ�ΰ�)
                    $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where u.sid={$sid} 
                        and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15'  and (u.pid = '46' or u.pid = '47' or u.pid = '50' or u.pid = '60' or u.pid = '70' or u.pid = '95' or u.pid = '110')
                        ORDER BY c.act_id, u.uid
                    ";
                    /* 8�鿦������
                    $query="select uid -- 00
                            ,class -- 01
                            ,sid   -- 02
                        from user_detailes
                        where (sid={$sid} OR sid=8) and (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                        AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                        and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                        ORDER BY uid
                    ";
                    */
                } else if ($sid == 5) {           //��̳�ݤ�ISO�Ƚи��Ԥ�ޤ� �ʤ���Ĺ�Ͻ������뤳��
                        $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where (u.sid={$sid} OR u.sid=30 OR u.sid=31) 
                        and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15' and (u.pid = '46' or u.pid = '47' or u.pid = '50' or u.pid = '60' or u.pid = '70' or u.pid = '95' or u.pid = '110')
                        ORDER BY c.act_id, u.uid
                        ";
                        /*  ��Ĺ�ʾ�ޤ���
                        $query="select uid -- 00
                            ,class -- 01
                            ,sid   -- 02
                        from user_detailes
                        where (sid={$sid} OR sid=8 OR sid=9 OR sid=17 OR sid=31 OR sid=38 OR sid=99) 
                        and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000' and uid != '010367'
                        ORDER BY uid
                        ";
                        */
                        /* 8�鿦������
                        $query="select uid -- 00
                            ,class -- 01
                            ,sid   -- 02
                        from user_detailes
                        where (sid={$sid} OR sid=17) and (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                        AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                        and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                        ORDER BY uid
                        ";
                        */
                } else if ($sid == 34) {           // ��¤����¤���ݤˤ�sid=17��¤�����ɲ�(��̳��̾ʬ�ΰ�)
                        $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where u.sid={$sid} and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15' and (u.pid = '46' or u.pid = '47' or u.pid = '50' or u.pid = '60' or u.pid = '70' or u.pid = '95' or u.pid = '110')
                        ORDER BY c.act_id, u.uid
                        ";
                        /* 8�鿦������ ������¤�������äƤ���
                        $query="select uid -- 00
                            ,class -- 01
                            ,sid   -- 02
                        from user_detailes
                        where (sid={$sid} OR sid=17) and (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                        AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                        and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                        ORDER BY uid
                        ";
                        */
                } else {
                        $query="select u.uid -- 00
                                ,u.class -- 01
                                ,u.sid   -- 02
                                ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where u.sid={$sid} 
                        and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15' and (u.pid = '46' or u.pid = '47' or u.pid = '50' or u.pid = '60' or u.pid = '70' or u.pid = '95' or u.pid = '110') and u.sid<>95
                        ORDER BY c.act_id, u.uid
                    ";
                    /* 8�鿦������
                    $query="select uid -- 00
                                ,class -- 01
                                ,sid   -- 02
                        from user_detailes
                        where sid={$sid} and (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                        AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                        and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                        ORDER BY uid
                    */
                }
            }
        }
        return $query;
    }
    ///// ������ץ�ǡ����μ������Ѵ�
    private function getErrorCheck($request, $uid, $t, $working_data, $sid_t)
    {
        $today_ym   = date('Ymd');
        for ($r=0; $r<$request->get('rows'); $r++) {        // �쥳���ɿ�ʬ���֤�
            $error_flg = '';                                // ���顼�ե饰�����
            if ($working_data[$r][1] != $today_ym) {        // �����ʳ�
                if ($working_data[$r][5] == '0000') {           // �ж��ǹ�ʤ�
                    if ($working_data[$r][6] == '0000') {       // ����ǹ�ʤ�
                        if ($working_data[$r][3] == '����' || $working_data[$r][3] == 'ˡ��' || $working_data[$r][3] == '�ٶ�') {
                            continue;                           // �����Ǥ����ɽ�����ʤ�
                        } else {                                // �����Ǥ�̵����
                            if ($working_data[$r][4] == '��') { // �Ժ���ͳ������ξ��ϥ��顼�ʤΤ�ɽ��
                                return false;
                            } else {
                                continue;                       // ����Ǥʤ����ɽ�����ʤ�
                            }
                        }
                    } else {    // �ж��ǹ郎�ʤ�����Ф�����Х��顼�ΰ١�ɽ��
                        return false;
                    }
                } else {        // �ж��ǹ濫��
                    if ($working_data[$r][6] == '0000') {
                        return false;   // �ж��ǹ濫��ǡ�����ǹ�ʤ��ϥ��顼�ΰ١�ɽ��
                    } else {    // �ж��ǹ濫��ǡ�����ǹ濫��ξ��ϱ�Ĺ�Ķȥ����å�
                        // ��Ĺ�����å�
                        $query = "SELECT * FROM user_detailes WHERE uid='{$uid[$t]}' AND pid=5";
                        $res_chk = array();
                        if ( getResult($query, $res_chk) > 0 ) {    // �ѡ��� ��Ĺ�����å�
                            if ($working_data[$r][6] >= '1645') {   // ��Ĺ30ʬ�ʾ� �����å�
                                if ($working_data[$r][8] == '000000') {    // ��Ĺ�ǹ�ʤ��ʤΤǥ��顼ɽ��
                                    return false;   // �ж��ǹ濫��ǡ�����ǹ�ʤ��ϥ��顼�ΰ١�ɽ��
                                } else {
                                    $t_temp = $working_data[$r][8];
                                    // ��Ĺ���ַ׻��ʼ»��֡�
                                    $deteObj = new DateTime($working_data[$r][6]);
                                    $ceil_num = floor(sprintf('%d', $deteObj->format('i')) / 15) * 15;
                                    $ceil_hour = $deteObj->format('H');
                                    $have = $ceil_hour.sprintf( '%02d', $ceil_num);
                                    $end = '2017-05-17 ' . substr($have, 0, 2) . ":" . substr($have, 2, 2) . ":00";
                                    $startSec = strtotime('2017-05-17 16:15:00');
                                    $endSec   = strtotime($end);
                                    
                                    $r_temp = ($endSec - $startSec)/60;
                                    
                                    if($r_temp>=60) {   // ��Ĺ�ϣ����֤ޤǤʤΤ�Ĵ��
                                        $r_temp = 60;
                                    } elseif($r_temp>=30) {
                                        $r_temp = 30;
                                    }
                                    $hour_r = floor($r_temp / 60);
                                    $minutes_r = $r_temp%60;
                                    if($hour_r == 0) {
                                        $hour_r = '0';
                                    }
                                    if($minutes_r == 0) {
                                        $minutes_r = '00';
                                    } else if($minutes_r < 10) {
                                        $minutes_r = '0' . $minutes_r;
                                    }
                                    
                                    // ��Ĺ���ַ׻��ʿ���ʬ��
                                    $hour = floor($t_temp / 60);
                                    $minutes = $t_temp%60;
                                    if($hour == 0) {
                                        $hour = '0';
                                    }
                                    if($minutes == 0) {
                                        $minutes = '00';
                                    } else if($minutes < 10) {
                                        $minutes = '0' . $minutes;
                                    }
                                    if ($hour_r == $hour) {
                                        if ($minutes_r == $minutes) {   // ���֤�ʬ������ ���顼�ʤ��ʤΤ���ɽ��
                                            //continue;
                                        } else {    // ʬ���԰��פʤΤǥ��顼ɽ��
                                            return false;   // �ж��ǹ濫��ǡ�����ǹ�ʤ��ϥ��顼�ΰ١�ɽ��
                                        }
                                    } else {    // ���֤��԰��פʤΤǥ��顼ɽ��
                                        return false;   // �ж��ǹ濫��ǡ�����ǹ�ʤ��ϥ��顼�ΰ١�ɽ��
                                    }
                                }
                            } else {                                // ��Ĺ̵�� �����å�̵��
                                //continue;
                            }
                        } else {                                    // �ѡ��Ȱʳ� ��Ĺ�����å�̵��
                            //continue;
                        }
                        // �Ķȥ����å�
                        $t_temp = $working_data[$r][9] + $working_data[$r][10];
                        $s_temp = $t_temp + $working_data[$r][11];    // ����ĶȲ�̣
                        $query = "SELECT * FROM user_detailes WHERE uid='{$uid[$t]}' AND (class LIKE '8%' OR class LIKE '9%' OR class LIKE '10%' OR class LIKE '11%' OR class LIKE '12%')";
                        $res_chk = array();
                        if ( getResult($query, $res_chk) > 0 ) {    // 8�鿦�ʾ� �Ķȥ����å�̵�� ��ɽ��
                            continue;
                        } elseif($sid_t[$t] == 19) {                // ���ɤξ��
                            if ($working_data[$r][6] >= '1830') {
                                if ($working_data[$r][4] == '�Ҳ�' || $working_data[$r][4] == 'Ǥ��') {
                                    continue;                       // 18:30�ʹߤǤ�Ұ����Ǥ�ո�������ɽ��
                                } elseif ($t_temp == '000000') {    
                                    return false;                   // 18:30�ʹߤǻĶȤ��ǹ郎�ʤ����ɽ��
                                } else {
                                    // �ĶȻ��ַ׻��ʼ»��֡�
                                    $deteObj = new DateTime($working_data[$r][6]);
                                    $ceil_num = floor(sprintf('%d', $deteObj->format('i')) / 30) * 30;
                                    $ceil_hour = $deteObj->format('H');
                                    $have = $ceil_hour.sprintf( '%02d', $ceil_num);
                                    $end = '2017-05-17 ' . substr($have, 0, 2) . ":" . substr($have, 2, 2) . ":00";
                                    $startSec = strtotime('2017-05-17 18:00:00');
                                    $endSec   = strtotime($end);
                                    
                                    $r_temp = ($endSec - $startSec)/60;
                                    
                                    $hour_r = floor($r_temp / 60);
                                    $minutes_r = $r_temp%60;
                                    if($hour_r == 0) {
                                        $hour_r = '0';
                                    }
                                    if($minutes_r == 0) {
                                        $minutes_r = '00';
                                    } else if($minutes_r < 10) {
                                        $minutes_r = '0' . $minutes_r;
                                    }
                                    
                                    // �ĶȻ��ַ׻��ʿ���ʬ��
                                    $hour = floor($t_temp / 60);
                                    $minutes = $t_temp%60;
                                    if($hour == 0) {
                                        $hour = '0';
                                    }
                                    if($minutes == 0) {
                                        $minutes = '00';
                                    } else if($minutes < 10) {
                                        $minutes = '0' . $minutes;
                                    }
                                    // ����ĶȲ�̣
                                    $hour_s = floor($s_temp / 60);
                                    $minutes_s = $s_temp%60;
                                    if($hour_s == 0) {
                                        $hour_s = '0';
                                    }
                                    if($minutes_s == 0) {
                                        $minutes_s = '00';
                                    } else if($minutes_s < 10) {
                                        $minutes_s = '0' . $minutes_s;
                                    }
                                    if ($hour_r == $hour_s) {
                                        if ($minutes_r == $minutes_s) {
                                            continue;               // ����ȼ»ĶȻ��֤����פ��Ƥ���Х��顼�ǤϤʤ��Τ���ɽ��
                                        } else {
                                           return false;            // ���֤�ʬ�����ʤ��Τǥ��顼�ΰ١�ɽ��
                                        }
                                    } else {
                                        return false;               // ���֤����ʤ��Τǥ��顼�ΰ١�ɽ��
                                    }
                                }
                            } else {
                                continue;                           // 18:30���ϻĶȤǤϤʤ��١���ɽ��
                            }
                        } else {                                    // ���ɰʳ��ΰ��̼Ұ�
                            if ($working_data[$r][6] >= '1800') {
                                if ($working_data[$r][4] == '�Ҳ�' || $working_data[$r][4] == 'Ǥ��') {
                                    continue;                       // 18:00�ʹߤǤ�Ұ����Ǥ�ո�������ɽ��
                                } elseif ($t_temp == '000000') {    
                                    return false;                   // 18:00�ʹߤǻĶȤ��ǹ郎�ʤ����ɽ��
                                } else {
                                    // �ĶȻ��ַ׻��ʼ»��֡�
                                    $deteObj = new DateTime($working_data[$r][6]);
                                    $ceil_num = floor(sprintf('%d', $deteObj->format('i')) / 30) * 30;
                                    $ceil_hour = $deteObj->format('H');
                                    $have = $ceil_hour.sprintf( '%02d', $ceil_num);
                                    $end = '2017-05-17 ' . substr($have, 0, 2) . ":" . substr($have, 2, 2) . ":00";
                                    $startSec = strtotime('2017-05-17 17:30:00');
                                    $endSec   = strtotime($end);
                                    
                                    $r_temp = ($endSec - $startSec)/60;
                                    
                                    $hour_r = floor($r_temp / 60);
                                    $minutes_r = $r_temp%60;
                                    if($hour_r == 0) {
                                        $hour_r = '0';
                                    }
                                    if($minutes_r == 0) {
                                        $minutes_r = '00';
                                    } else if($minutes_r < 10) {
                                        $minutes_r = '0' . $minutes_r;
                                    }
                                    
                                    // �ĶȻ��ַ׻��ʿ���ʬ��
                                    $hour = floor($t_temp / 60);
                                    $minutes = $t_temp%60;
                                    if($hour == 0) {
                                        $hour = '0';
                                    }
                                    if($minutes == 0) {
                                        $minutes = '00';
                                    } else if($minutes < 10) {
                                        $minutes = '0' . $minutes;
                                    }
                                    // ����ĶȲ�̣
                                    $hour_s = floor($s_temp / 60);
                                    $minutes_s = $s_temp%60;
                                    if($hour_s == 0) {
                                        $hour_s = '0';
                                    }
                                    if($minutes_s == 0) {
                                        $minutes_s = '00';
                                    } else if($minutes_s < 10) {
                                        $minutes_s = '0' . $minutes_s;
                                    }
                                    if ($hour_r == $hour_s) {
                                        if ($minutes_r == $minutes_s) {
                                            continue;               // ����ȼ»ĶȻ��֤����פ��Ƥ���Х��顼�ǤϤʤ��Τ���ɽ��
                                        } else {
                                            return false;           // ���֤�ʬ�����ʤ��Τǥ��顼�ΰ١�ɽ��
                                        }
                                    } else {
                                        return false;               // ���֤����ʤ��Τǥ��顼�ΰ١�ɽ��
                                    }
                                }
                            } else {
                                continue;                           // 18:00���ϻĶȤǤϤʤ��١���ɽ��
                            }
                        }
                    }
                }
            } else {                    // �����ξ��
                if ($working_data[$r][5] == '0000') {           // �ж��ǹ�ʤ�
                    if ($working_data[$r][6] == '0000') {       // ����ǹ�ʤ�
                        if ($working_data[$r][3] == '����' || $working_data[$r][3] == 'ˡ��' || $working_data[$r][3] == '�ٶ�') {
                            continue;                           // �����Ǥ����ɽ�����ʤ�
                        } else {                                // �����Ǥ�̵����
                            if ($working_data[$r][4] == '��') { // �Ժ���ͳ������
                                return false;
                            } else {
                                continue;                       // ����Ǥʤ����ɽ�����ʤ�
                            }
                        }
                    } else {    // �ж��ǹ郎�ʤ�����Ф�����Х��顼�ΰ١�ɽ��
                        return false;
                    }
                } else {                                        // �ж��ǹ濫��
                    if ($working_data[$r][6] == '0000') {       // ����ǹ�ʤ�
                        continue;                               // �����Ͻ�����ǹ���ޤ����Ф�
                    } else {    // �ж��ǹ郎���ꡢ��Ф⤹�Ǥˤ���Х��顼�����å�
                        // ��Ĺ�����å�
                        $query = "SELECT * FROM user_detailes WHERE uid='{$uid[$t]}' AND pid=5";
                        $res_chk = array();
                        if ( getResult($query, $res_chk) > 0 ) {    // �ѡ��� ��Ĺ�����å�
                            if ($working_data[$r][6] >= '1645') {   // ��Ĺ30ʬ�ʾ� �����å�
                                if ($working_data[$r][8] == '000000') {    // ��Ĺ�ǹ�ʤ��ʤΤǥ��顼ɽ��
                                    return false;   // �ж��ǹ濫��ǡ�����ǹ�ʤ��ϥ��顼�ΰ١�ɽ��
                                } else {
                                    $t_temp = $working_data[$r][8];
                                    // ��Ĺ���ַ׻��ʼ»��֡�
                                    $deteObj = new DateTime($working_data[$r][6]);
                                    $ceil_num = floor(sprintf('%d', $deteObj->format('i')) / 15) * 15;
                                    $ceil_hour = $deteObj->format('H');
                                    $have = $ceil_hour.sprintf( '%02d', $ceil_num);
                                    $end = '2017-05-17 ' . substr($have, 0, 2) . ":" . substr($have, 2, 2) . ":00";
                                    $startSec = strtotime('2017-05-17 16:15:00');
                                    $endSec   = strtotime($end);
                                    
                                    $r_temp = ($endSec - $startSec)/60;
                                    
                                    if($r_temp>=60) {   // ��Ĺ�ϣ����֤ޤǤʤΤ�Ĵ��
                                        $r_temp = 60;
                                    } elseif($r_temp>=30) {
                                        $r_temp = 30;
                                    }
                                    $hour_r = floor($r_temp / 60);
                                    $minutes_r = $r_temp%60;
                                    if($hour_r == 0) {
                                        $hour_r = '0';
                                    }
                                    if($minutes_r == 0) {
                                        $minutes_r = '00';
                                    } else if($minutes_r < 10) {
                                        $minutes_r = '0' . $minutes_r;
                                    }
                                    
                                    // ��Ĺ���ַ׻��ʿ���ʬ��
                                    $hour = floor($t_temp / 60);
                                    $minutes = $t_temp%60;
                                    if($hour == 0) {
                                        $hour = '0';
                                    }
                                    if($minutes == 0) {
                                        $minutes = '00';
                                    } else if($minutes < 10) {
                                        $minutes = '0' . $minutes;
                                    }
                                    if ($hour_r == $hour) {
                                        if ($minutes_r == $minutes) {   // ���֤�ʬ������ ���顼�ʤ��ʤΤ���ɽ��
                                            //continue;
                                        } else {    // ʬ���԰��פʤΤǥ��顼ɽ��
                                            return false;   // �ж��ǹ濫��ǡ�����ǹ�ʤ��ϥ��顼�ΰ١�ɽ��
                                        }
                                    } else {    // ���֤��԰��פʤΤǥ��顼ɽ��
                                        return false;   // �ж��ǹ濫��ǡ�����ǹ�ʤ��ϥ��顼�ΰ١�ɽ��
                                    }
                                }
                            } else {                                // ��Ĺ̵�� �����å�̵��
                                //continue;
                            }
                        } else {                                    // �ѡ��Ȱʳ� ��Ĺ�����å�̵��
                            //continue;
                        }
                        // �Ķȥ����å�
                        $t_temp = $working_data[$r][9] + $working_data[$r][10];
                        $s_temp = $t_temp + $working_data[$r][11];    // ����ĶȲ�̣
                        $query = "SELECT * FROM user_detailes WHERE uid='{$uid[$t]}' AND (class LIKE '8%' OR class LIKE '9%' OR class LIKE '10%' OR class LIKE '11%' OR class LIKE '12%')";
                        $res_chk = array();
                        if ( getResult($query, $res_chk) > 0 ) {    // 8�鿦�ʾ� �Ķȥ����å�̵�� ��ɽ��
                            continue;
                        } elseif($sid_t[$t] == 19) {                // ���ɤξ��
                            if ($working_data[$r][6] >= '1830') {
                                if ($working_data[$r][4] == '�Ҳ�' || $working_data[$r][4] == 'Ǥ��') {
                                    continue;                       // 18:30�ʹߤǤ�Ұ����Ǥ�ո�������ɽ��
                                } elseif ($t_temp == '000000') {    
                                    return false;                   // 18:30�ʹߤǻĶȤ��ǹ郎�ʤ����ɽ��
                                } else {
                                    // �ĶȻ��ַ׻��ʼ»��֡�
                                    $deteObj = new DateTime($working_data[$r][6]);
                                    $ceil_num = floor(sprintf('%d', $deteObj->format('i')) / 30) * 30;
                                    $ceil_hour = $deteObj->format('H');
                                    $have = $ceil_hour.sprintf( '%02d', $ceil_num);
                                    $end = '2017-05-17 ' . substr($have, 0, 2) . ":" . substr($have, 2, 2) . ":00";
                                    $startSec = strtotime('2017-05-17 18:00:00');
                                    $endSec   = strtotime($end);
                                    
                                    $r_temp = ($endSec - $startSec)/60;
                                    
                                    $hour_r = floor($r_temp / 60);
                                    $minutes_r = $r_temp%60;
                                    if($hour_r == 0) {
                                        $hour_r = '0';
                                    }
                                    if($minutes_r == 0) {
                                        $minutes_r = '00';
                                    } else if($minutes_r < 10) {
                                        $minutes_r = '0' . $minutes_r;
                                    }
                                    
                                    // �ĶȻ��ַ׻��ʿ���ʬ��
                                    $hour = floor($t_temp / 60);
                                    $minutes = $t_temp%60;
                                    if($hour == 0) {
                                        $hour = '0';
                                    }
                                    if($minutes == 0) {
                                        $minutes = '00';
                                    } else if($minutes < 10) {
                                        $minutes = '0' . $minutes;
                                    }
                                    // ����ĶȲ�̣
                                    $hour_s = floor($s_temp / 60);
                                    $minutes_s = $s_temp%60;
                                    if($hour_s == 0) {
                                        $hour_s = '0';
                                    }
                                    if($minutes_s == 0) {
                                        $minutes_s = '00';
                                    } else if($minutes_s < 10) {
                                        $minutes_s = '0' . $minutes_s;
                                    }
                                    if ($hour_r == $hour_s) {
                                        if ($minutes_r == $minutes_s) {
                                            continue;               // ����ȼ»ĶȻ��֤����פ��Ƥ���Х��顼�ǤϤʤ��Τ���ɽ��
                                        } else {
                                            return false;           // ���֤�ʬ�����ʤ��Τǥ��顼�ΰ١�ɽ��
                                        }
                                    } else {
                                        return false;               // ���֤����ʤ��Τǥ��顼�ΰ١�ɽ��
                                    }
                                }
                            } else {
                                continue;                           // 18:30���ϻĶȤǤϤʤ��١���ɽ��
                            }
                        } else {                                    // ���ɰʳ��ΰ��̼Ұ�
                            if ($working_data[$r][6] >= '1800') {
                                if ($working_data[$r][4] == '�Ҳ�' || $working_data[$r][4] == 'Ǥ��') {
                                    continue;                       // 18:00�ʹߤǤ�Ұ����Ǥ�ո�������ɽ��
                                } elseif ($t_temp == '000000') {    
                                    return false;                   // 18:00�ʹߤǻĶȤ��ǹ郎�ʤ����ɽ��
                                } else {
                                    // �ĶȻ��ַ׻��ʼ»��֡�
                                    $deteObj = new DateTime($working_data[$r][6]);
                                    $ceil_num = floor(sprintf('%d', $deteObj->format('i')) / 30) * 30;
                                    $ceil_hour = $deteObj->format('H');
                                    $have = $ceil_hour.sprintf( '%02d', $ceil_num);
                                    $end = '2017-05-17 ' . substr($have, 0, 2) . ":" . substr($have, 2, 2) . ":00";
                                    $startSec = strtotime('2017-05-17 17:30:00');
                                    $endSec   = strtotime($end);
                                    
                                    $r_temp = ($endSec - $startSec)/60;
                                    
                                    $hour_r = floor($r_temp / 60);
                                    $minutes_r = $r_temp%60;
                                    if($hour_r == 0) {
                                        $hour_r = '0';
                                    }
                                    if($minutes_r == 0) {
                                        $minutes_r = '00';
                                    } else if($minutes_r < 10) {
                                        $minutes_r = '0' . $minutes_r;
                                    }
                                    
                                    // �ĶȻ��ַ׻��ʿ���ʬ��
                                    $hour = floor($t_temp / 60);
                                    $minutes = $t_temp%60;
                                    if($hour == 0) {
                                        $hour = '0';
                                    }
                                    if($minutes == 0) {
                                        $minutes = '00';
                                    } else if($minutes < 10) {
                                        $minutes = '0' . $minutes;
                                    }
                                    // ����ĶȲ�̣
                                    $hour_s = floor($s_temp / 60);
                                    $minutes_s = $s_temp%60;
                                    if($hour_s == 0) {
                                        $hour_s = '0';
                                    }
                                    if($minutes_s == 0) {
                                        $minutes_s = '00';
                                    } else if($minutes_s < 10) {
                                        $minutes_s = '0' . $minutes_s;
                                    }
                                    if ($hour_r == $hour_s) {
                                        if ($minutes_r == $minutes_s) {
                                            continue;               // ����ȼ»ĶȻ��֤����פ��Ƥ���Х��顼�ǤϤʤ��Τ���ɽ��
                                        } else {
                                            return false;           // ���֤�ʬ�����ʤ��Τǥ��顼�ΰ١�ɽ��
                                        }
                                    } else {
                                        return false;               // ���֤����ʤ��Τǥ��顼�ΰ١�ɽ��
                                    }
                                }
                            } else {
                                continue;                           // 18:00���ϻĶȤǤϤʤ��١���ɽ��
                            }
                        }
                    }
                }
            }
        }
        return true;
    }
    ///// ������ץ�ǡ����μ������Ѵ�
    private function getTimeProData($request, $uid, $t)
    {
        $query = $this->getWorkingData($request, $uid[$t]);             // TimePro�ǡ����μ���
        $working_data = array();
        $field = array();
        $fixed_num = 0;
        $paidho_num = 0;
        $work_num = 0;
        $howork_num = 0;
        $hohalf_num = 0;
        $closure_num = 0;
        $hotime_num  = 0;       // ���ֵٻ��֥������
        $noholy_num  = 0;       // ��������������
        if (($rows = getResultWithField2($query, $field, $working_data)) <= 0) {
            $num = 0;
        } else {
            $num = count($field) + 1;
        }
        for ($r=0; $r<$rows; $r++) {                                    
            // TimePro�ǡ������Ѵ���������
            switch ($working_data[$r][2]) {
                case 0:
                    $working_data[$r][2] = '��';
                    break;
                case 1:
                    $working_data[$r][2] = '��';
                    break;
                case 2:
                    $working_data[$r][2] = '��';
                    break;
                case 3:
                    $working_data[$r][2] = '��';
                    break;
                case 4:
                    $working_data[$r][2] = '��';
                    break;
                case 5:
                    $working_data[$r][2] = '��';
                    break;
                case 6:
                    $working_data[$r][2] = '��';
                    break;
            }
            // TimePro�ǡ������Ѵ��ʥ���������
            switch ($working_data[$r][3]) {
                case 1:
                    $working_data[$r][3] = '____';
                    $fixed_num += 1;
                    break;
                case 2:
                    $working_data[$r][3] = '����';
                    break;
                case 3:
                    $working_data[$r][3] = 'ˡ��';
                    break;
                case 5:
                    $working_data[$r][3] = '�ٶ�';
                    $closure_num += 1;
                    break;    
            }
            // TimePro�ǡ������Ѵ����Ժ���ͳ��
            switch ($working_data[$r][4]) {
                case 11:
                    $working_data[$r][4] = 'ͭ��';
                    $paidho_num += 1;                                   // ͭ�������Υ�����ȡܣ�
                    break;
                case 12:
                    $working_data[$r][4] = '���';
                    $noholy_num  += 1;                                  // ��������Υ�����ȡܣ�
                    break;
                case 13:
                    $working_data[$r][4] = '̵��';
                    break;
                case 14:
                    $working_data[$r][4] = '��ĥ';
                    $work_num += 1;                                     // ��ĥ�ξ��Ͻж������ܣ�
                    break;
                case 15:
                    $working_data[$r][4] = '����';                      // ���ܤʤ�
                    break;
                case 16:
                    $working_data[$r][4] = '�õ�';                      // �ж������ϥ�����Ȥ��ʤ�
                    break;
                case 17:
                    $working_data[$r][4] = '�Ļ�';                      // ����̵��
                    break;
                case 18:
                    $working_data[$r][4] = 'Ĥ��';                      // ����̵��
                    break;
                case 19:
                    $working_data[$r][4] = '����';                      // �ж������ϥ�����Ȥ��ʤ�
                    break;
                case 20:
                    $working_data[$r][4] = '���';
                    break;
                case 21:
                    $working_data[$r][4] = '����';
                    break;
                case 22:
                    $working_data[$r][4] = '�ٿ�';
                    break;
                case 23:
                    $working_data[$r][4] = 'ϫ��';                      // ����̵��
                    break;
                default:
                    if ($working_data[$r][18] == 41) {
                        $working_data[$r][4] = 'ȾAM';
                        $paidho_num += 0.5;                             // �ж������Υ�����ȡܣ�������Ⱦ�١�
                        $hohalf_num += 0.5;                             // ͭ�������Υ�����ȡܣ�������Ⱦ�١�
                    } else if ($working_data[$r][23] == 42) {
                        $working_data[$r][4] = 'ȾPM';             
                        $paidho_num += 0.5;                             // �ж������Υ�����ȡܣ�������Ⱦ�١�
                        $hohalf_num += 0.5;                             // ͭ�������Υ�����ȡܣ�������Ⱦ�١�
                    } else if ($working_data[$r][18] == 62) {
                        if ($working_data[$r][23] == 62) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ3H';
                                $hotime_num  += 3;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = 'ǯ4H';
                                $hotime_num  += 4;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = 'ǯ5H';
                                $hotime_num  += 5;
                            } else if ($working_data[$r][24] == 67) {
                                $working_data[$r][4] = 'ǯ6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 68) {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 69) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ2H';
                                $hotime_num  += 2;
                            }
                        } else if ($working_data[$r][23] == 65) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ4H';
                                $hotime_num  += 4;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = 'ǯ5H';
                                $hotime_num  += 5;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = 'ǯ6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 67) {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 68) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ3H';
                                $hotime_num  += 3;
                            }
                        } else if ($working_data[$r][23] == 66) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ5H';
                                $hotime_num  += 5;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = 'ǯ6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 67) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ4H';
                                $hotime_num  += 4;
                            }
                        } else if ($working_data[$r][23] == 67) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ5H';
                                $hotime_num  += 5;
                            }
                        } else if ($working_data[$r][23] == 68) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ6H';
                                $hotime_num  += 6;
                            }
                        } else if ($working_data[$r][23] == 69) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            }
                        } else if ($working_data[$r][23] == 70) {
                            $working_data[$r][4] = 'ǯ8H';
                            $hotime_num  += 8;
                        } else {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ2H';
                                $hotime_num  += 2;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = 'ǯ3H';
                                $hotime_num  += 3;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = 'ǯ4H';
                                $hotime_num  += 4;
                            } else if ($working_data[$r][24] == 67) {
                                $working_data[$r][4] = 'ǯ5H';
                                $hotime_num  += 5;
                            } else if ($working_data[$r][24] == 68) {
                                $working_data[$r][4] = 'ǯ6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 69) {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 70) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ1H';
                                $hotime_num  += 1;
                            }
                        }
                    } else if ($working_data[$r][18] == 65) {
                        if ($working_data[$r][23] == 62) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ4H';
                                $hotime_num  += 4;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = 'ǯ5H';
                                $hotime_num  += 5;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = 'ǯ6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 67) {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 68) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ3H';
                                $hotime_num  += 3;
                            }
                        } else if ($working_data[$r][23] == 65) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ5H';
                                $hotime_num  += 5;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = 'ǯ6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 67) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ4H';
                                $hotime_num  += 4;
                            }
                        } else if ($working_data[$r][23] == 66) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ5H';
                                $hotime_num  += 5;
                            }
                        } else if ($working_data[$r][23] == 67) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ6H';
                                $hotime_num  += 6;
                            }
                        } else if ($working_data[$r][23] == 68) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            }
                        } else if ($working_data[$r][23] == 69) {
                            $working_data[$r][4] = 'ǯ8H';
                            $hotime_num  += 8;
                        } else {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ3H';
                                $hotime_num  += 3;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = 'ǯ4H';
                                $hotime_num  += 4;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = 'ǯ5H';
                                $hotime_num  += 5;
                            } else if ($working_data[$r][24] == 67) {
                                $working_data[$r][4] = 'ǯ6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 68) {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 69) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ2H';
                                $hotime_num  += 2;
                            }
                        }
                    } else if ($working_data[$r][18] == 66) {
                        if ($working_data[$r][23] == 62) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ5H';
                                $hotime_num  += 5;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = 'ǯ6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 67) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ4H';
                                $hotime_num  += 4;
                            }
                        } else if ($working_data[$r][23] == 65) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ5H';
                                $hotime_num  += 5;
                            }
                        } else if ($working_data[$r][23] == 66) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ6H';
                                $hotime_num  += 6;
                            }
                        } else if ($working_data[$r][23] == 67) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            }
                        } else if ($working_data[$r][23] == 68) {
                            $working_data[$r][4] = 'ǯ8H';
                            $hotime_num  += 8;
                        } else {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ4H';
                                $hotime_num  += 4;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = 'ǯ5H';
                                $hotime_num  += 5;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = 'ǯ6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 67) {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 68) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ3H';
                                $hotime_num  += 3;
                            }
                        }
                    } else if ($working_data[$r][18] == 67) {
                        if ($working_data[$r][23] == 62) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ5H';
                                $hotime_num  += 5;
                            }
                        } else if ($working_data[$r][23] == 65) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ6H';
                                $hotime_num  += 6;
                            }
                        } else if ($working_data[$r][23] == 66) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            }
                        } else if ($working_data[$r][23] == 67) {
                            $working_data[$r][4] = 'ǯ8H';
                            $hotime_num  += 8;
                        } else {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ5H';
                                $hotime_num  += 5;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = 'ǯ6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 67) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ4H';
                                $hotime_num  += 4;
                            }
                        }
                    } else if ($working_data[$r][18] == 68) {
                        if ($working_data[$r][23] == 62) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ6H';
                                $hotime_num  += 6;
                            }
                        } else if ($working_data[$r][23] == 65) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            }
                        } else if ($working_data[$r][23] == 66) {
                            $working_data[$r][4] = 'ǯ8H';
                            $hotime_num  += 8;
                        } else {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ5H';
                                $hotime_num  += 5;
                            }
                        }
                    } else if ($working_data[$r][18] == 69) {
                        if ($working_data[$r][23] == 62) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            }
                        } else if ($working_data[$r][23] == 65) {
                            $working_data[$r][4] = 'ǯ8H';
                            $hotime_num  += 8;
                        } else {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ6H';
                                $hotime_num  += 6;
                            }
                        }
                    } else if ($working_data[$r][18] == 70) {
                        if ($working_data[$r][23] == 62) {
                            $working_data[$r][4] = 'ǯ8H';
                            $hotime_num  += 8;
                        } else {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            }
                        }
                    } else if ($working_data[$r][23] == 62) {
                        if ($working_data[$r][24] == 62) {
                            $working_data[$r][4] = 'ǯ2H';
                            $hotime_num  += 2;
                        } else if ($working_data[$r][24] == 65) {
                            $working_data[$r][4] = 'ǯ3H';
                            $hotime_num  += 3;
                        } else if ($working_data[$r][24] == 66) {
                            $working_data[$r][4] = 'ǯ4H';
                            $hotime_num  += 4;
                        } else if ($working_data[$r][24] == 67) {
                            $working_data[$r][4] = 'ǯ5H';
                            $hotime_num  += 5;
                        } else if ($working_data[$r][24] == 68) {
                            $working_data[$r][4] = 'ǯ6H';
                            $hotime_num  += 6;
                        } else if ($working_data[$r][24] == 69) {
                            $working_data[$r][4] = 'ǯ7H';
                            $hotime_num  += 7;
                        } else if ($working_data[$r][24] == 70) {
                            $working_data[$r][4] = 'ǯ8H';
                            $hotime_num  += 8;
                        } else {
                            $working_data[$r][4] = 'ǯ1H';
                            $hotime_num  += 1;
                        }
                    } else if ($working_data[$r][23] == 65) {
                        if ($working_data[$r][24] == 62) {
                            $working_data[$r][4] = 'ǯ3H';
                            $hotime_num  += 3;
                        } else if ($working_data[$r][24] == 65) {
                            $working_data[$r][4] = 'ǯ4H';
                            $hotime_num  += 4;
                        } else if ($working_data[$r][24] == 66) {
                            $working_data[$r][4] = 'ǯ5H';
                            $hotime_num  += 5;
                        } else if ($working_data[$r][24] == 67) {
                            $working_data[$r][4] = 'ǯ6H';
                            $hotime_num  += 6;
                        } else if ($working_data[$r][24] == 68) {
                            $working_data[$r][4] = 'ǯ7H';
                            $hotime_num  += 7;
                        } else if ($working_data[$r][24] == 69) {
                            $working_data[$r][4] = 'ǯ8H';
                            $hotime_num  += 8;
                        } else {
                            $working_data[$r][4] = 'ǯ2H';
                            $hotime_num  += 2;
                        }
                    } else if ($working_data[$r][23] == 66) {
                        if ($working_data[$r][24] == 62) {
                            $working_data[$r][4] = 'ǯ4H';
                            $hotime_num  += 4;
                        } else if ($working_data[$r][24] == 65) {
                            $working_data[$r][4] = 'ǯ5H';
                            $hotime_num  += 5;
                        } else if ($working_data[$r][24] == 66) {
                            $working_data[$r][4] = 'ǯ6H';
                            $hotime_num  += 6;
                        } else if ($working_data[$r][24] == 67) {
                            $working_data[$r][4] = 'ǯ7H';
                            $hotime_num  += 7;
                        } else if ($working_data[$r][24] == 68) {
                            $working_data[$r][4] = 'ǯ8H';
                            $hotime_num  += 8;
                        } else {
                            $working_data[$r][4] = 'ǯ3H';
                            $hotime_num  += 3;
                        }
                    } else if ($working_data[$r][23] == 67) {
                        if ($working_data[$r][24] == 62) {
                            $working_data[$r][4] = 'ǯ5H';
                            $hotime_num  += 5;
                        } else if ($working_data[$r][24] == 65) {
                            $working_data[$r][4] = 'ǯ6H';
                            $hotime_num  += 6;
                        } else if ($working_data[$r][24] == 66) {
                            $working_data[$r][4] = 'ǯ7H';
                            $hotime_num  += 7;
                        } else if ($working_data[$r][24] == 67) {
                            $working_data[$r][4] = 'ǯ8H';
                            $hotime_num  += 8;
                        } else {
                            $working_data[$r][4] = 'ǯ4H';
                            $hotime_num  += 4;
                        }
                    } else if ($working_data[$r][23] == 68) {
                        if ($working_data[$r][24] == 62) {
                            $working_data[$r][4] = 'ǯ6H';
                            $hotime_num  += 6;
                        } else if ($working_data[$r][24] == 65) {
                            $working_data[$r][4] = 'ǯ7H';
                            $hotime_num  += 7;
                        } else if ($working_data[$r][24] == 66) {
                            $working_data[$r][4] = 'ǯ8H';
                            $hotime_num  += 8;
                        } else {
                            $working_data[$r][4] = 'ǯ5H';
                            $hotime_num  += 5;
                        }
                    } else if ($working_data[$r][23] == 69) {
                        if ($working_data[$r][24] == 62) {
                            $working_data[$r][4] = 'ǯ7H';
                            $hotime_num  += 7;
                        } else if ($working_data[$r][24] == 65) {
                            $working_data[$r][4] = 'ǯ8H';
                            $hotime_num  += 8;
                        } else {
                            $working_data[$r][4] = 'ǯ6H';
                            $hotime_num  += 6;
                        }
                    } else if ($working_data[$r][23] == 70) {
                        if ($working_data[$r][24] == 62) {
                            $working_data[$r][4] = 'ǯ8H';
                            $hotime_num  += 8;
                        } else {
                            $working_data[$r][4] = 'ǯ7H';
                            $hotime_num  += 7;
                        }
                    } else if ($working_data[$r][23] == 58) {
                        $working_data[$r][4] = '�Ҳ�';
                    } else if ($working_data[$r][23] == 59) {
                        $working_data[$r][4] = 'Ǥ��';
                    } else if ($working_data[$r][24] == 62) {
                        $working_data[$r][4] = 'ǯ1H';
                        $hotime_num  += 1;
                    } else if ($working_data[$r][24] == 65) {
                        $working_data[$r][4] = 'ǯ2H';
                        $hotime_num  += 2;
                    } else if ($working_data[$r][24] == 66) {
                        $working_data[$r][4] = 'ǯ3H';
                        $hotime_num  += 3;
                    } else if ($working_data[$r][24] == 67) {
                        $working_data[$r][4] = 'ǯ4H';
                        $hotime_num  += 4;
                    } else if ($working_data[$r][24] == 68) {
                        $working_data[$r][4] = 'ǯ5H';
                        $hotime_num  += 5;
                    } else if ($working_data[$r][24] == 69) {
                        $working_data[$r][4] = 'ǯ6H';
                        $hotime_num  += 6;
                    } else if ($working_data[$r][24] == 70) {
                        $working_data[$r][4] = 'ǯ7H';
                        $hotime_num  += 7;
                    } else {
                        $working_data[$r][4] = '��';
                    }
                    break;
            }
            // �����ǧ�ե饰�μ���
            if ($uid[$t] == '914737') {                                     // �и��ԤμҰ��ֹ���ᤷ
                $uid[$t] = '014737';
            } else if ($uid[$t] == '920206') {
                $uid[$t] = '020206';
            }
            $confirm = $this->getConfirmData($uid[$t], $working_data[$r][1]);
            $working_data[$r][23] = $confirm;
        }
        $request->add('fixed_num', $fixed_num);
        $request->add('paidho_num', $paidho_num);
        $request->add('work_num', $work_num);
        $request->add('howork_num', $howork_num);
        $request->add('hohalf_num', $hohalf_num);
        $request->add('closure_num', $closure_num);
        $request->add('hotime_num', $hotime_num);
        $request->add('noholy_num', $noholy_num);
        $request->add('rows', $rows);
        $request->add('num', $num);
        return $working_data;
    }
    
    ///// ������ץ�ǡ����μ������Ѵ�
    private function getTimeProDataOver($request, $uid, $sid_t, $t)
    {
        $query = $this->getWorkingDataOver($request, $uid[$t], $sid_t[$t]);             // TimePro�ǡ����μ���
        $working_data = array();
        $field = array();
        $fixed_num = 0;
        $paidho_num = 0;
        $work_num = 0;
        $howork_num = 0;
        $hohalf_num = 0;
        $closure_num = 0;
        $hotime_num  = 0;
        $noholy_num  = 0;
        if (($rows = getResultWithField2($query, $field, $working_data)) <= 0) {
            $num = 0;
        } else {
            $num = count($field) + 1;
        }
        for ($r=0; $r<$rows; $r++) {                                    
            // TimePro�ǡ������Ѵ���������
            switch ($working_data[$r][2]) {
                case 0:
                    $working_data[$r][2] = '��';
                    break;
                case 1:
                    $working_data[$r][2] = '��';
                    break;
                case 2:
                    $working_data[$r][2] = '��';
                    break;
                case 3:
                    $working_data[$r][2] = '��';
                    break;
                case 4:
                    $working_data[$r][2] = '��';
                    break;
                case 5:
                    $working_data[$r][2] = '��';
                    break;
                case 6:
                    $working_data[$r][2] = '��';
                    break;
            }
            // TimePro�ǡ������Ѵ��ʥ���������
            switch ($working_data[$r][3]) {
                case 1:
                    $working_data[$r][3] = '____';
                    $fixed_num += 1;
                    break;
                case 2:
                    $working_data[$r][3] = '����';
                    break;
                case 3:
                    $working_data[$r][3] = 'ˡ��';
                    break;
                case 5:
                    $working_data[$r][3] = '�ٶ�';
                    $closure_num += 1;
                    break;    
            }
            // TimePro�ǡ������Ѵ����Ժ���ͳ��
            switch ($working_data[$r][4]) {
                case 11:
                    $working_data[$r][4] = 'ͭ��';
                    $paidho_num += 1;                                   // ͭ�������Υ�����ȡܣ�
                    break;
                case 12:
                    $working_data[$r][4] = '���';
                    $noholy_num  += 1;                                  // ��������Υ�����ȡܣ�
                    break;
                case 13:
                    $working_data[$r][4] = '̵��';
                    break;
                case 14:
                    $working_data[$r][4] = '��ĥ';
                    $work_num += 1;                                     // ��ĥ�ξ��Ͻж������ܣ�
                    break;
                case 15:
                    $working_data[$r][4] = '����';                      // ���ܤʤ�
                    break;
                case 16:
                    $working_data[$r][4] = '�õ�';                      // �ж������ϥ�����Ȥ��ʤ�
                    break;
                case 17:
                    $working_data[$r][4] = '�Ļ�';                      // ����̵��
                    break;
                case 18:
                    $working_data[$r][4] = 'Ĥ��';                      // ����̵��
                    break;
                case 19:
                    $working_data[$r][4] = '����';                      // �ж������ϥ�����Ȥ��ʤ�
                    break;
                case 20:
                    $working_data[$r][4] = '���';
                    break;
                case 21:
                    $working_data[$r][4] = '����';
                    break;
                case 22:
                    $working_data[$r][4] = '�ٿ�';
                    break;
                case 23:
                    $working_data[$r][4] = 'ϫ��';                      // ����̵��
                    break;
                default:
                    if ($working_data[$r][18] == 41) {
                        $working_data[$r][4] = 'ȾAM';
                        $paidho_num += 0.5;                             // �ж������Υ�����ȡܣ�������Ⱦ�١�
                        $hohalf_num += 0.5;                             // ͭ�������Υ�����ȡܣ�������Ⱦ�١�
                    } else if ($working_data[$r][23] == 42) {
                        $working_data[$r][4] = 'ȾPM';             
                        $paidho_num += 0.5;                             // �ж������Υ�����ȡܣ�������Ⱦ�١�
                        $hohalf_num += 0.5;                             // ͭ�������Υ�����ȡܣ�������Ⱦ�١�
                    } else if ($working_data[$r][18] == 62) {
                        if ($working_data[$r][23] == 62) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ3H';
                                $hotime_num  += 3;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = 'ǯ4H';
                                $hotime_num  += 4;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = 'ǯ5H';
                                $hotime_num  += 5;
                            } else if ($working_data[$r][24] == 67) {
                                $working_data[$r][4] = 'ǯ6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 68) {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 69) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ2H';
                                $hotime_num  += 2;
                            }
                        } else if ($working_data[$r][23] == 65) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ4H';
                                $hotime_num  += 4;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = 'ǯ5H';
                                $hotime_num  += 5;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = 'ǯ6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 67) {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 68) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ3H';
                                $hotime_num  += 3;
                            }
                        } else if ($working_data[$r][23] == 66) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ5H';
                                $hotime_num  += 5;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = 'ǯ6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 67) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ4H';
                                $hotime_num  += 4;
                            }
                        } else if ($working_data[$r][23] == 67) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ5H';
                                $hotime_num  += 5;
                            }
                        } else if ($working_data[$r][23] == 68) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ6H';
                                $hotime_num  += 6;
                            }
                        } else if ($working_data[$r][23] == 69) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            }
                        } else if ($working_data[$r][23] == 70) {
                            $working_data[$r][4] = 'ǯ8H';
                            $hotime_num  += 8;
                        } else {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ2H';
                                $hotime_num  += 2;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = 'ǯ3H';
                                $hotime_num  += 3;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = 'ǯ4H';
                                $hotime_num  += 4;
                            } else if ($working_data[$r][24] == 67) {
                                $working_data[$r][4] = 'ǯ5H';
                                $hotime_num  += 5;
                            } else if ($working_data[$r][24] == 68) {
                                $working_data[$r][4] = 'ǯ6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 69) {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 70) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ1H';
                                $hotime_num  += 1;
                            }
                        }
                    } else if ($working_data[$r][18] == 65) {
                        if ($working_data[$r][23] == 62) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ4H';
                                $hotime_num  += 4;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = 'ǯ5H';
                                $hotime_num  += 5;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = 'ǯ6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 67) {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 68) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ3H';
                                $hotime_num  += 3;
                            }
                        } else if ($working_data[$r][23] == 65) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ5H';
                                $hotime_num  += 5;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = 'ǯ6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 67) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ4H';
                                $hotime_num  += 4;
                            }
                        } else if ($working_data[$r][23] == 66) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ5H';
                                $hotime_num  += 5;
                            }
                        } else if ($working_data[$r][23] == 67) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ6H';
                                $hotime_num  += 6;
                            }
                        } else if ($working_data[$r][23] == 68) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            }
                        } else if ($working_data[$r][23] == 69) {
                            $working_data[$r][4] = 'ǯ8H';
                            $hotime_num  += 8;
                        } else {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ3H';
                                $hotime_num  += 3;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = 'ǯ4H';
                                $hotime_num  += 4;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = 'ǯ5H';
                                $hotime_num  += 5;
                            } else if ($working_data[$r][24] == 67) {
                                $working_data[$r][4] = 'ǯ6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 68) {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 69) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ2H';
                                $hotime_num  += 2;
                            }
                        }
                    } else if ($working_data[$r][18] == 66) {
                        if ($working_data[$r][23] == 62) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ5H';
                                $hotime_num  += 5;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = 'ǯ6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 67) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ4H';
                                $hotime_num  += 4;
                            }
                        } else if ($working_data[$r][23] == 65) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ5H';
                                $hotime_num  += 5;
                            }
                        } else if ($working_data[$r][23] == 66) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ6H';
                                $hotime_num  += 6;
                            }
                        } else if ($working_data[$r][23] == 67) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            }
                        } else if ($working_data[$r][23] == 68) {
                            $working_data[$r][4] = 'ǯ8H';
                            $hotime_num  += 8;
                        } else {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ4H';
                                $hotime_num  += 4;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = 'ǯ5H';
                                $hotime_num  += 5;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = 'ǯ6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 67) {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 68) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ3H';
                                $hotime_num  += 3;
                            }
                        }
                    } else if ($working_data[$r][18] == 67) {
                        if ($working_data[$r][23] == 62) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ5H';
                                $hotime_num  += 5;
                            }
                        } else if ($working_data[$r][23] == 65) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ6H';
                                $hotime_num  += 6;
                            }
                        } else if ($working_data[$r][23] == 66) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            }
                        } else if ($working_data[$r][23] == 67) {
                            $working_data[$r][4] = 'ǯ8H';
                            $hotime_num  += 8;
                        } else {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ5H';
                                $hotime_num  += 5;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = 'ǯ6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 67) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ4H';
                                $hotime_num  += 4;
                            }
                        }
                    } else if ($working_data[$r][18] == 68) {
                        if ($working_data[$r][23] == 62) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ6H';
                                $hotime_num  += 6;
                            }
                        } else if ($working_data[$r][23] == 65) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            }
                        } else if ($working_data[$r][23] == 66) {
                            $working_data[$r][4] = 'ǯ8H';
                            $hotime_num  += 8;
                        } else {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ5H';
                                $hotime_num  += 5;
                            }
                        }
                    } else if ($working_data[$r][18] == 69) {
                        if ($working_data[$r][23] == 62) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            }
                        } else if ($working_data[$r][23] == 65) {
                            $working_data[$r][4] = 'ǯ8H';
                            $hotime_num  += 8;
                        } else {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ6H';
                                $hotime_num  += 6;
                            }
                        }
                    } else if ($working_data[$r][18] == 70) {
                        if ($working_data[$r][23] == 62) {
                            $working_data[$r][4] = 'ǯ8H';
                            $hotime_num  += 8;
                        } else {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = 'ǯ8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = 'ǯ7H';
                                $hotime_num  += 7;
                            }
                        }
                    } else if ($working_data[$r][23] == 62) {
                        if ($working_data[$r][24] == 62) {
                            $working_data[$r][4] = 'ǯ2H';
                            $hotime_num  += 2;
                        } else if ($working_data[$r][24] == 65) {
                            $working_data[$r][4] = 'ǯ3H';
                            $hotime_num  += 3;
                        } else if ($working_data[$r][24] == 66) {
                            $working_data[$r][4] = 'ǯ4H';
                            $hotime_num  += 4;
                        } else if ($working_data[$r][24] == 67) {
                            $working_data[$r][4] = 'ǯ5H';
                            $hotime_num  += 5;
                        } else if ($working_data[$r][24] == 68) {
                            $working_data[$r][4] = 'ǯ6H';
                            $hotime_num  += 6;
                        } else if ($working_data[$r][24] == 69) {
                            $working_data[$r][4] = 'ǯ7H';
                            $hotime_num  += 7;
                        } else if ($working_data[$r][24] == 70) {
                            $working_data[$r][4] = 'ǯ8H';
                            $hotime_num  += 8;
                        } else {
                            $working_data[$r][4] = 'ǯ1H';
                            $hotime_num  += 1;
                        }
                    } else if ($working_data[$r][23] == 65) {
                        if ($working_data[$r][24] == 62) {
                            $working_data[$r][4] = 'ǯ3H';
                            $hotime_num  += 3;
                        } else if ($working_data[$r][24] == 65) {
                            $working_data[$r][4] = 'ǯ4H';
                            $hotime_num  += 4;
                        } else if ($working_data[$r][24] == 66) {
                            $working_data[$r][4] = 'ǯ5H';
                            $hotime_num  += 5;
                        } else if ($working_data[$r][24] == 67) {
                            $working_data[$r][4] = 'ǯ6H';
                            $hotime_num  += 6;
                        } else if ($working_data[$r][24] == 68) {
                            $working_data[$r][4] = 'ǯ7H';
                            $hotime_num  += 7;
                        } else if ($working_data[$r][24] == 69) {
                            $working_data[$r][4] = 'ǯ8H';
                            $hotime_num  += 8;
                        } else {
                            $working_data[$r][4] = 'ǯ2H';
                            $hotime_num  += 2;
                        }
                    } else if ($working_data[$r][23] == 66) {
                        if ($working_data[$r][24] == 62) {
                            $working_data[$r][4] = 'ǯ4H';
                            $hotime_num  += 4;
                        } else if ($working_data[$r][24] == 65) {
                            $working_data[$r][4] = 'ǯ5H';
                            $hotime_num  += 5;
                        } else if ($working_data[$r][24] == 66) {
                            $working_data[$r][4] = 'ǯ6H';
                            $hotime_num  += 6;
                        } else if ($working_data[$r][24] == 67) {
                            $working_data[$r][4] = 'ǯ7H';
                            $hotime_num  += 7;
                        } else if ($working_data[$r][24] == 68) {
                            $working_data[$r][4] = 'ǯ8H';
                            $hotime_num  += 8;
                        } else {
                            $working_data[$r][4] = 'ǯ3H';
                            $hotime_num  += 3;
                        }
                    } else if ($working_data[$r][23] == 67) {
                        if ($working_data[$r][24] == 62) {
                            $working_data[$r][4] = 'ǯ5H';
                            $hotime_num  += 5;
                        } else if ($working_data[$r][24] == 65) {
                            $working_data[$r][4] = 'ǯ6H';
                            $hotime_num  += 6;
                        } else if ($working_data[$r][24] == 66) {
                            $working_data[$r][4] = 'ǯ7H';
                            $hotime_num  += 7;
                        } else if ($working_data[$r][24] == 67) {
                            $working_data[$r][4] = 'ǯ8H';
                            $hotime_num  += 8;
                        } else {
                            $working_data[$r][4] = 'ǯ4H';
                            $hotime_num  += 4;
                        }
                    } else if ($working_data[$r][23] == 68) {
                        if ($working_data[$r][24] == 62) {
                            $working_data[$r][4] = 'ǯ6H';
                            $hotime_num  += 6;
                        } else if ($working_data[$r][24] == 65) {
                            $working_data[$r][4] = 'ǯ7H';
                            $hotime_num  += 7;
                        } else if ($working_data[$r][24] == 66) {
                            $working_data[$r][4] = 'ǯ8H';
                            $hotime_num  += 8;
                        } else {
                            $working_data[$r][4] = 'ǯ5H';
                            $hotime_num  += 5;
                        }
                    } else if ($working_data[$r][23] == 69) {
                        if ($working_data[$r][24] == 62) {
                            $working_data[$r][4] = 'ǯ7H';
                            $hotime_num  += 7;
                        } else if ($working_data[$r][24] == 65) {
                            $working_data[$r][4] = 'ǯ8H';
                            $hotime_num  += 8;
                        } else {
                            $working_data[$r][4] = 'ǯ6H';
                            $hotime_num  += 6;
                        }
                    } else if ($working_data[$r][23] == 70) {
                        if ($working_data[$r][24] == 62) {
                            $working_data[$r][4] = 'ǯ8H';
                            $hotime_num  += 8;
                        } else {
                            $working_data[$r][4] = 'ǯ7H';
                            $hotime_num  += 7;
                        }
                    } else if ($working_data[$r][23] == 58) {
                        $working_data[$r][4] = '�Ҳ�';
                    } else if ($working_data[$r][23] == 59) {
                        $working_data[$r][4] = 'Ǥ��';
                    } else if ($working_data[$r][24] == 62) {
                        $working_data[$r][4] = 'ǯ1H';
                        $hotime_num  += 1;
                    } else if ($working_data[$r][24] == 65) {
                        $working_data[$r][4] = 'ǯ2H';
                        $hotime_num  += 2;
                    } else if ($working_data[$r][24] == 66) {
                        $working_data[$r][4] = 'ǯ3H';
                        $hotime_num  += 3;
                    } else if ($working_data[$r][24] == 67) {
                        $working_data[$r][4] = 'ǯ4H';
                        $hotime_num  += 4;
                    } else if ($working_data[$r][24] == 68) {
                        $working_data[$r][4] = 'ǯ5H';
                        $hotime_num  += 5;
                    } else if ($working_data[$r][24] == 69) {
                        $working_data[$r][4] = 'ǯ6H';
                        $hotime_num  += 6;
                    } else if ($working_data[$r][24] == 70) {
                        $working_data[$r][4] = 'ǯ7H';
                        $hotime_num  += 7;
                    } else {
                        $working_data[$r][4] = '��';
                    }
                    break;
            }
            // �����ǧ�ե饰�μ���
            if ($uid[$t] == '914737') {                                     // �и��ԤμҰ��ֹ���ᤷ
                $uid[$t] = '014737';
            } else if ($uid[$t] == '920206') {
                $uid[$t] = '020206';
            }
            $confirm = $this->getConfirmData($uid[$t], $working_data[$r][1]);
            $working_data[$r][23] = $confirm;
        }
        $request->add('fixed_num', $fixed_num);
        $request->add('paidho_num', $paidho_num);
        $request->add('work_num', $work_num);
        $request->add('howork_num', $howork_num);
        $request->add('hohalf_num', $hohalf_num);
        $request->add('closure_num', $closure_num);
        $request->add('hotime_num', $hotime_num);
        $request->add('noholy_num', $noholy_num);
        $request->add('rows', $rows);
        $request->add('num', $num);
        return $working_data;
    }
    ///// ������ץ�ǡ����μ������Ѵ�
    private function getTimeProDataErr($request, $uid, $t)
    {
        $query = $this->getWorkingData($request, $uid[$t]);             // TimePro�ǡ����μ���
        $working_data = array();
        $field = array();
        $fixed_num = 0;
        $paidho_num = 0;
        $work_num = 0;
        $howork_num = 0;
        $hohalf_num = 0;
        $closure_num = 0;
        $hotime_num  = 0;
        $noholy_num  = 0;
        if (($rows = getResultWithField2($query, $field, $working_data)) <= 0) {
            $num = 0;
        } else {
            $num = count($field) + 1;
        }
        for ($r=0; $r<$rows; $r++) {                                    
            // TimePro�ǡ������Ѵ���������
            switch ($working_data[$r][2]) {
                case 0:
                    $working_data[$r][2] = '��';
                    break;
                case 1:
                    $working_data[$r][2] = '��';
                    break;
                case 2:
                    $working_data[$r][2] = '��';
                    break;
                case 3:
                    $working_data[$r][2] = '��';
                    break;
                case 4:
                    $working_data[$r][2] = '��';
                    break;
                case 5:
                    $working_data[$r][2] = '��';
                    break;
                case 6:
                    $working_data[$r][2] = '��';
                    break;
            }
            // TimePro�ǡ������Ѵ��ʥ���������
            switch ($working_data[$r][3]) {
                case 1:
                    $working_data[$r][3] = '____';
                    $fixed_num += 1;
                    break;
                case 2:
                    $working_data[$r][3] = '����';
                    break;
                case 3:
                    $working_data[$r][3] = 'ˡ��';
                    break;
                case 5:
                    $working_data[$r][3] = '�ٶ�';
                    $closure_num += 1;
                    break;    
            }
            // TimePro�ǡ������Ѵ����Ժ���ͳ��
            switch ($working_data[$r][4]) {
                case 11:
                    $working_data[$r][4] = 'ͭ��';
                    $paidho_num += 1;                                   // ͭ�������Υ�����ȡܣ�
                    break;
                case 12:
                    $working_data[$r][4] = '���';
                    $noholy_num  += 1;                                  // ��������Υ�����ȡܣ�
                    break;
                case 13:
                    $working_data[$r][4] = '̵��';
                    break;
                case 14:
                    $working_data[$r][4] = '��ĥ';
                    $work_num += 1;                                     // ��ĥ�ξ��Ͻж������ܣ�
                    break;
                case 15:
                    $working_data[$r][4] = '����';                      // ���ܤʤ�
                    break;
                case 16:
                    $working_data[$r][4] = '�õ�';                      // �ж������ϥ�����Ȥ��ʤ�
                    break;
                case 17:
                    $working_data[$r][4] = '�Ļ�';                      // ����̵��
                    break;
                case 18:
                    $working_data[$r][4] = 'Ĥ��';                      // ����̵��
                    break;
                case 19:
                    $working_data[$r][4] = '����';                      // �ж������ϥ�����Ȥ��ʤ�
                    break;
                case 20:
                    $working_data[$r][4] = '���';
                    break;
                case 21:
                    $working_data[$r][4] = '����';
                    break;
                case 22:
                    $working_data[$r][4] = '�ٿ�';
                    break;
                case 23:
                    $working_data[$r][4] = 'ϫ��';                      // ����̵��
                    break;
                default:
                    if ($working_data[$r][18] == 41) {
                        $working_data[$r][4] = 'Ⱦ��';
                        $paidho_num += 0.5;                             // �ж������Υ�����ȡܣ�������Ⱦ�١�
                        $hohalf_num += 0.5;                             // ͭ�������Υ�����ȡܣ�������Ⱦ�١�
                    } else if ($working_data[$r][18] == 42) {
                        $working_data[$r][4] = 'Ⱦ��';             
                        $paidho_num += 0.5;                             // �ж������Υ�����ȡܣ�������Ⱦ�١�
                        $hohalf_num += 0.5;                             // ͭ�������Υ�����ȡܣ�������Ⱦ�١�
                    } else {
                        $working_data[$r][4] = '��';
                    }
                    break;
            }
            // �����ǧ�ե饰�μ���
            if ($uid[$t] == '914737') {                                     // �и��ԤμҰ��ֹ���ᤷ
                $uid[$t] = '014737';
            } else if ($uid[$t] == '920206') {
                $uid[$t] = '020206';
            }
            $confirm = $this->getConfirmData($uid[$t], $working_data[$r][1]);
            $working_data[$r][23] = $confirm;
        }
        $request->add('fixed_num', $fixed_num);
        $request->add('paidho_num', $paidho_num);
        $request->add('work_num', $work_num);
        $request->add('howork_num', $howork_num);
        $request->add('hohalf_num', $hohalf_num);
        $request->add('closure_num', $closure_num);
        $request->add('hotime_num', $hotime_num);
        $request->add('noholy_num', $noholy_num);
        $request->add('rows', $rows);
        $request->add('num', $num);
        return $working_data;
    }
    
    ///// ���ա��о�����(�Ұ��ֹ�) ���齢�ȥǡ����μ���
    private function getWorkingData($request, $uid)
    {
        /*
        $query = "SELECT substr(timepro, 3, 6) AS �Ұ��ֹ�    -- 00
                      ,substr(timepro, 17, 8)  AS ǯ��        -- 01
                      ,substr(timepro, 25, 2)  AS ����        -- 02
                      ,substr(timepro, 27, 2)  AS ������    -- 03
                      ,substr(timepro, 173, 2) AS �Ժ���ͳ    -- 04
                      ,substr(timepro, 33, 4)  AS �жл���    -- 05
                      ,substr(timepro, 41, 4)  AS ��л���    -- 06
                      ,substr(timepro, 79, 6)  AS �������    -- 07
                      ,substr(timepro, 97, 6)  AS ��Ĺ����    -- 08
                      ,substr(timepro, 85, 6)  AS ��л���    -- 09
                      ,substr(timepro, 91, 6)  AS �ĶȻ���    -- 10
                      ,substr(timepro, 109, 6) AS ����Ķ�    -- 11
                      ,substr(timepro, 115, 6) AS �ٽл���    -- 12
                      ,substr(timepro, 121, 6) AS �ٽлĶ�    -- 13
                      ,substr(timepro, 127, 6) AS �ٽп���    -- 14
                      ,substr(timepro, 155, 6) AS ˡ�����    -- 15
                      ,substr(timepro, 161, 6) AS ˡ��Ķ�    -- 16
                      ,substr(timepro, 133, 6) AS �������    -- 17
                      ,substr(timepro, 37, 2)  AS �жУͣ�    -- 18
                      ,substr(timepro, 103, 6) AS �������    -- 19
                      ,substr(timepro, 167, 6) AS ˡ�꿼��    -- 20
                      ,substr(timepro, 139, 6) AS ���ѳ���    -- 21
                      ,substr(timepro, 175, 1) AS ���׶�ʬ    -- 22
                      ,substr(timepro, 45, 2)  AS ��Уͣ�    -- 23
                   FROM timepro_daily_data 
                   WHERE substr(timepro, 3, 6)='{$uid}'
                   AND substr(timepro, 17, 8) >= {$request->get('targetDateStr')} AND substr(timepro, 17, 8) <= {$request->get('targetDateEnd')} 
                   ORDER BY �Ұ��ֹ� , ǯ��;
        ";
        */
        $query = "SELECT uid AS �Ұ��ֹ�    -- 00
                      ,working_date  AS ǯ��        -- 01
                      ,working_day  AS ����        -- 02
                      ,calendar  AS ������    -- 03
                      ,absence AS �Ժ���ͳ    -- 04
                      ,str_time  AS �жл���    -- 05
                      ,end_time        AS ��л���    -- 06
                      ,fixed_time  AS �������    -- 07
                      ,extend_time  AS ��Ĺ����    -- 08
                      ,earlytime  AS ��л���    -- 09
                      ,overtime  AS �ĶȻ���    -- 10
                      ,midnight_over AS ����Ķ�    -- 11
                      ,holiday_time AS �ٽл���    -- 12
                      ,holiday_over AS �ٽлĶ�    -- 13
                      ,holiday_mid AS �ٽп���    -- 14
                      ,legal_time AS ˡ�����    -- 15
                      ,legal_over AS ˡ��Ķ�    -- 16
                      ,late_time AS �������    -- 17
                      ,str_mc  AS �жУͣ�    -- 18
                      ,early_mid AS �������    -- 19
                      ,legal_mid AS ˡ�꿼��    -- 20
                      ,private_out AS ���ѳ���    -- 21
                      ,total_div AS ���׶�ʬ    -- 22
                      ,end_mc  AS ��Уͣ�    -- 23
                      ,out_mc  AS ���Уͣ�    -- 24
                   FROM working_hours_report_data_new
                   WHERE uid = '{$uid}'
                   AND working_date >= {$request->get('targetDateStr')} AND working_date <= {$request->get('targetDateEnd')} 
                   ORDER BY �Ұ��ֹ� , ǯ��;
        ";
        return $query;
    }
    
    ///// ���ա��о�����(�Ұ��ֹ�) ���齢�ȥǡ����μ��� �Ķ�ͭ��Τ�
    private function getWorkingDataOver($request, $uid, $sid)
    {
        if ($sid == 19) {
            $query = "SELECT uid AS �Ұ��ֹ�    -- 00
                          ,working_date  AS ǯ��        -- 01
                          ,working_day  AS ����        -- 02
                          ,calendar  AS ������    -- 03
                          ,absence AS �Ժ���ͳ    -- 04
                          ,str_time  AS �жл���    -- 05
                          ,end_time        AS ��л���    -- 06
                          ,fixed_time  AS �������    -- 07
                          ,extend_time  AS ��Ĺ����    -- 08
                          ,earlytime  AS ��л���    -- 09
                          ,overtime  AS �ĶȻ���    -- 10
                          ,midnight_over AS ����Ķ�    -- 11
                          ,holiday_time AS �ٽл���    -- 12
                          ,holiday_over AS �ٽлĶ�    -- 13
                          ,holiday_mid AS �ٽп���    -- 14
                          ,legal_time AS ˡ�����    -- 15
                          ,legal_over AS ˡ��Ķ�    -- 16
                          ,late_time AS �������    -- 17
                          ,str_mc  AS �жУͣ�    -- 18
                          ,early_mid AS �������    -- 19
                          ,legal_mid AS ˡ�꿼��    -- 20
                          ,private_out AS ���ѳ���    -- 21
                          ,total_div AS ���׶�ʬ    -- 22
                          ,end_mc  AS ��Уͣ�    -- 23
                          ,out_mc  AS ���Уͣ�    -- 24
                       FROM working_hours_report_data_new
                       WHERE uid = '{$uid}'
                       AND working_date >= {$request->get('targetDateStr')} AND working_date <= {$request->get('targetDateEnd')} 
                       AND end_mc <> '58' AND end_mc <> '59'
                       AND (((extend_time <> '000000' OR earlytime <> '000000' OR overtime <> '000000' OR midnight_over <> '000000' OR holiday_time <> '000000'
                       OR holiday_over <> '000000' OR holiday_mid <> '000000' OR legal_time <> '000000' OR legal_over <> '000000'
                       OR early_mid <> '000000' OR legal_mid <> '000000')) OR (TO_NUMBER(end_time, '000000') >= 1830))
                       ORDER BY �Ұ��ֹ� , ǯ��;
            ";
        } else {
            $query = "SELECT uid AS �Ұ��ֹ�    -- 00
                          ,working_date  AS ǯ��        -- 01
                          ,working_day  AS ����        -- 02
                          ,calendar  AS ������    -- 03
                          ,absence AS �Ժ���ͳ    -- 04
                          ,str_time  AS �жл���    -- 05
                          ,end_time        AS ��л���    -- 06
                          ,fixed_time  AS �������    -- 07
                          ,extend_time  AS ��Ĺ����    -- 08
                          ,earlytime  AS ��л���    -- 09
                          ,overtime  AS �ĶȻ���    -- 10
                          ,midnight_over AS ����Ķ�    -- 11
                          ,holiday_time AS �ٽл���    -- 12
                          ,holiday_over AS �ٽлĶ�    -- 13
                          ,holiday_mid AS �ٽп���    -- 14
                          ,legal_time AS ˡ�����    -- 15
                          ,legal_over AS ˡ��Ķ�    -- 16
                          ,late_time AS �������    -- 17
                          ,str_mc  AS �жУͣ�    -- 18
                          ,early_mid AS �������    -- 19
                          ,legal_mid AS ˡ�꿼��    -- 20
                          ,private_out AS ���ѳ���    -- 21
                          ,total_div AS ���׶�ʬ    -- 22
                          ,end_mc  AS ��Уͣ�    -- 23
                          ,out_mc  AS ���Уͣ�    -- 24
                       FROM working_hours_report_data_new
                       WHERE uid = '{$uid}'
                       AND working_date >= {$request->get('targetDateStr')} AND working_date <= {$request->get('targetDateEnd')} 
                       AND end_mc <> '58' AND end_mc <> '59'
                       AND (((extend_time <> '000000' OR earlytime <> '000000' OR overtime <> '000000' OR midnight_over <> '000000' OR holiday_time <> '000000'
                       OR holiday_over <> '000000' OR holiday_mid <> '000000' OR legal_time <> '000000' OR legal_over <> '000000'
                       OR early_mid <> '000000' OR legal_mid <> '000000')) OR (TO_NUMBER(end_time, '000000') >= 1800))
                       ORDER BY �Ұ��ֹ� , ǯ��;
            ";
        }
        return $query;
    }
    ///// �Ұ��ֹ桦��������꽵��γ�ǧ�����
    private function getConfirmData($uid, $working_date)
    {
    $query="SELECT confirm   -- 00
            FROM working_hours_report_confirm
            WHERE uid='{$uid}' AND working_date={$working_date}
        ";
        $res = array();
        $confirm = '';
        if ($this->getResult2($query, $res) < 1) {
            $confirm = '̤';
            return $confirm;
        } else {
            if ($res[0][0] == 't') {
                $confirm = '��';
            } else {
                $confirm = '̤';
            }
            return $confirm;
        }
    }
    
    ///// �Ұ�̾�����
    private function getUserName($uid)
    {
        $query = "
            SELECT trim(name)
            FROM
                user_detailes
            LEFT OUTER JOIN
                user_master USING(uid)
            WHERE
                uid = '{$uid}'
        ";
        $res = array();                        // �����
        if ($this->getResult2($query, $res) < 1) {
            $user_name = '̤��Ͽ';
            return $user_name;
        } else {
            $user_name = $res[0][0];
            return $user_name;
        }
    }
    
    ///// List��   ���Ƚ���Ȳ���������ư����ǡ�������
    private function getViewCorrectHTMLbody($request, $menu, $endflg)
    {
        // �����
        $listTable  = '';
        $query = $this->getSectionUser($request->get('targetSection'), $request->get('targetPosition'), $request->get('targetDateStr')); // �����������°�Ұ������
        if ($rows=getResult($query,$res)) {
            for ($i=0; $i<$rows; $i++) {
                $uid[$i]   = $res[$i]['uid'];
            }
            // ���Ϥ��ʤ��Τǥ����Ȳ�
            /*
            if ($request->get('targetSection') == 4) {                  // ��������°�Ұ����б�
                $uid[$i] = '000817';                                    // 000817=������ ���Ӥ���
                $res[$i]['sid'] = 9;                                    // sid�˴��������ɲ�
                $rows = $rows + 1;                                      // sid�ɲäΰ�$rows�⣱�ɲ�
            }
            */
            $s_name = $this->getSectionName($rows,$res);                // ������������祳���ɤ������̾�����
        } else {
            $uid    = '------';
            $s_name ='----------';
        }
        $uid_num = count($uid);
        for ($t=0; $t<$uid_num; $t++) {                                     // �и��ԤμҰ��ֹ���Ѵ�(TimePro�ǡ���)
            if ($uid[$t] == '014737') {                                     // �и��Ԥ��ɲ��ѹ�����в����ᤷ��Ʊ�����ѹ�
                $uid[$t] = '914737';                                        // 014737=��̳�� �񤵤�
            } else if ($uid[$t] == '020273') {                              // 020273=���Ѳ� ��ƣ����
                $uid[$t] = '920273';
            }
        }
        //if ($endflg == 't') {                                // �����Ѱ������������ư�����Ƚ��
        //    $correct_data = $this->getCorrectEndData();    // �����Ѱ����μ���
        //} else {
        //$correct_data = $this->getCorrectData($request, $uid);       // �������ư����μ���
        //}
        //$rows = count($correct_data);
        //if ($correct_data != '') {
        //    for ($i=0; $i<$rows; $i++) {
        //        $user_name[$i] = $this->getUserName($correct_data[$i][0]);    // �Ұ��ֹ���Ұ�̾�����
        //    }
        //}
        $correct_data = array();
        $crr_num      = 0;
        $str_date = $request->get('targetDateStr');
        $end_date = $request->get('targetDateEnd');
        for ($t=0; $t<$uid_num; $t++) {
            $query = sprintf("SELECT * FROM working_hours_report_confirm WHERE uid='%s' AND working_date=%d AND working_enddate=%d AND confirm_flg > 1", $uid[$t], $str_date, $end_date);
            $res_chk = array();
            if ( getResult($query, $res_chk) > 0 ) {    // ��Ͽ���� UPDATE ����
                $correct_data[$crr_num] = $res_chk[0];
                $crr_num = $crr_num + 1;
            } else {                                    // ��Ͽ̵�� �ʤˤ⤷�ʤ�
                
            }
        }
        $rows = count($correct_data);
        if ($correct_data != '') {
            for ($i=0; $i<$rows; $i++) {
                $user_name[$i] = $this->getUserName($correct_data[$i][0]);    // �Ұ��ֹ���Ұ�̾�����
            }
        }
        $listTable .= "<center>\n";
        $listTable .= "    <form name='CorrectForm' method='post' target='_parent'>\n";
        $listTable .= "        <table bgcolor='#d6d3ce' width='150' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "        <THEAD>\n";
        $listTable .= "            <!-- �ơ��֥� �إå�����ɽ�� -->\n";
        $listTable .= "            <tr>\n";
        $listTable .= "                <th class='winbox' nowrap width='10'>No</th>        <!-- �ԥʥ�С���ɽ�� -->\n";
        $listTable .= "                <th class='winbox' nowrap>�Ұ��ֹ�</th>\n";
        $listTable .= "                <th class='winbox' nowrap>�Ұ�̾</th>\n";
        $listTable .= "                <th class='winbox' nowrap>����ǯ����</th>\n";
        $listTable .= "                <th class='winbox' nowrap>��λǯ����</th>\n";
        $listTable .= "                <th class='winbox' nowrap>��ǧ����</th>\n";
        $listTable .= "            </tr>\n";
        $listTable .= "        </THEAD>\n";
        $listTable .= "        <TFOOT>\n";
        $listTable .= "            <!-- ���ߤϥեå����ϲ���ʤ� -->\n";
        $listTable .= "        </TFOOT>\n";
        $listTable .= "        <TBODY>\n";
        if ($correct_data == '') {
            $listTable .= "        <tr>\n";
            $listTable .= "            <td class='winbox' colspan='6' nowrap align='center'><div class='pt9'>�������Ƥ���Ͽ������ޤ���</div></td>\n";
            $listTable .= "        </tr>\n";
        } else {
            for ($r=0; $r<$rows; $r++) {
                $listTable .= "    <tr>\n";
                $listTable .= "        <td class='winbox' nowrap align='right'>\n";
                $cnum = $r + 1;
                $listTable .= "        ". $cnum ."\n";
                $listTable .= "        </td>\n";
                $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'>". $correct_data[$r][0] ."</div></td>\n";
                $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'>". $user_name[$r] ."</div></td>\n";
                $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'>". $correct_data[$r][1] ."</div></td>\n";
                $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'>". $correct_data[$r][2] ."</div></td>\n";
                if ($correct_data[$r][4] == 2) {
                    $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'>�Ͻ���к�</div></td>\n";
                } else {
                    $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'>�Ͻа����</div></td>\n";
                }
                
                /*
                if ($endflg == 't') {
                    $listTable .= "        <td class='winbox' nowrap align='left'><input type='button' name='exec3' value='�������' onClick='WorkingHoursReport.Correctexecute(". $correct_data[$r][0] .", ". $correct_data[$r][1] .", 1);' title='����å�����С������Ѥ���ä��ޤ���'></td>\n";
                } else {
                    $listTable .= "        <td class='winbox' nowrap align='left'><input type='button' name='exec3' value='������' onClick='WorkingHoursReport.Correctexecute(". $correct_data[$r][0] .", ". $correct_data[$r][1] .", 2);' title='����å�����С������Ѥˤ��ޤ���'></td>\n";
                }
                */
            }
            $listTable .= "        </tr>\n";
        }
        $listTable .= "        </TBODY>\n";
        $listTable .= "        </table>\n";
        $listTable .= "            </td></tr>\n";
        $listTable .= "        </table> <!----------------- ���ߡ�End ------------------>\n";
        $listTable .= "    </form>\n";
        $listTable .= "</center>\n";
        // return mb_convert_encoding($listTable, 'UTF-8');
        return $listTable;
    }
    // �������ƤΤ��������Ѱʳ��Υǡ��������
    private function getCorrectData ($request)
    {
        $query = "
            SELECT  uid                AS �Ұ��ֹ�     -- 0
                ,   working_date       AS ����ǯ����   -- 1
                ,   correct_contents   AS ��������     -- 2
            FROM
                working_hours_report_confirm
            WHERE 
                correct = 'f'
            ORDER BY
                working_date
        ";
        $res = array();
        if ($rows=$this->getResult2($query, $res) < 1) {
            $res = '';
            return $res;
        } else {
            return $res;
        }
    }
    // �����Ѥߥǡ����μ���
    private function getCorrectEndData ()
    {
        $query = "
            SELECT  uid                AS �Ұ��ֹ�     -- 0
                ,   working_date       AS ����ǯ����   -- 1
                ,   correct_contents   AS ��������     -- 2
            FROM
                working_hours_report_correct
            WHERE 
                correct = 't'
            ORDER BY
                working_date
        ";
        $res = array();
        if ($rows=$this->getResult2($query, $res) < 1) {
            $res = '';
            return $res;
        } else {
            return $res;
        }
    }
    ///// �����δ�λ�����
    public function setCorrectData($request)
    {
        $uid              = $request->get('user_id');
        $uid              = sprintf('%06d', $uid);
        $working_date     = $request->get('date');
        $query = sprintf("SELECT * FROM working_hours_report_correct WHERE uid='%s' AND working_date=%d", $uid, $working_date);
        $res_chk = array();
        if ( getResult($query, $res_chk) > 0 ) {    // ��Ͽ���� UPDATE ����
            if ($request->get('CancelFlg') == 'n') {
                $query = sprintf("UPDATE working_hours_report_correct SET correct=TRUE, last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE uid='%s' AND working_date='%s'", $_SESSION['User_ID'], $uid, $working_date);
                if (query_affected($query) <= 0) {
                    $_SESSION['s_sysmsg'] .= "�Ұ��ֹ桧{$uid}  ����ǯ������{$working_date}�������Ѥ˽���ޤ���";      // .= �����
                    $msg_flg = 'alert';
                    return false;
                } else {
                    $_SESSION['s_sysmsg'] .= "�Ұ��ֹ桧{$uid}  ����ǯ������{$working_date}�������Ѥˤ��ޤ�����"; // .= �����
                    return true;
                }
            } else {
                $query = sprintf("UPDATE working_hours_report_correct SET correct=FALSE, last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE uid='%s' AND working_date='%s'", $_SESSION['User_ID'], $uid, $working_date);
                if (query_affected($query) <= 0) {
                    $_SESSION['s_sysmsg'] .= "�Ұ��ֹ桧{$uid}  ����ǯ������{$working_date}��̤�����˽���ޤ���";      // .= �����
                    $msg_flg = 'alert';
                    return false;
                } else {
                    $_SESSION['s_sysmsg'] .= "�Ұ��ֹ桧{$uid}  ����ǯ������{$working_date}��̤�����ˤ��ޤ�����"; // .= �����
                    return true;
                }
            }
        } else {                                    // ��Ͽ�ʤ����顼
            $_SESSION['s_sysmsg'] .= "�Ұ��ֹ桧{$uid}  ����ǯ������{$working_date}�������Ѥ˽���ޤ���2";      // .= �����
            $msg_flg = 'alert';
            return false;
        }
    }
    ///// List��   ���Ƚ���Ȳ�ν����ǧ�����ǡ�������
    private function getViewConfirmHTMLbody($request, $menu)
    {
        // �����
        $request->add('check_flg', 'y');
        $listTable  = '';
        $section_name = $this->getTargetSectionConfirm();            // �����ǧ������ ����̾�μ���
        $rows = count($section_name);
        $yyyymm   = date('Ym'); $yyyy = date('Y'); $mm = date('m');  // �إå���ɽ����ǯ��׻�
        if ($mm == 1) {
            $yyyy_b   = $yyyy - 1;
            $mm_b     = 12;
            $yyyymm_b = $yyyy_b . $mm_b;
        } else {
            $yyyy_b   = $yyyy;
            $mm_b     = $mm - 1;
            if ($mm_b == 1) {
                $mm_b = '01';
            }
            if ($mm_b == 2) {
                $mm_b = '02';
            }
            if ($mm_b == 3) {
                $mm_b = '03';
            }
            if ($mm_b == 4) {
                $mm_b = '04';
            }
            if ($mm_b == 5) {
                $mm_b = '05';
            }
            if ($mm_b == 6) {
                $mm_b = '06';
            }
            if ($mm_b == 7) {
                $mm_b = '07';
            }
            if ($mm_b == 8) {
                $mm_b = '08';
            }
            if ($mm_b == 9) {
                $mm_b = '09';
            }
            $yyyymm_b = $yyyy . $mm_b;
        }
        $listTable .= "<center>\n";
        $listTable .= "    <form name='CorrectForm' method='post' target='_parent'>\n";
        $listTable .= "        <table bgcolor='#d6d3ce' width='150' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "        <THEAD>\n";
        $listTable .= "            <!-- �ơ��֥� �إå�����ɽ�� -->\n";
        $listTable .= "            <tr>\n";
        $listTable .= "                <th class='winbox' nowrap>����</th>\n";
        $listTable .= "                <th class='winbox' nowrap>����</th>\n";
        $listTable .= "                <th class='winbox' nowrap>". $yyyy_b . "ǯ". $mm_b ."��</th>\n";
        $listTable .= "                <th class='winbox' nowrap>". $yyyy . "ǯ". $mm ."��</th>\n";
        $listTable .= "            </tr>\n";
        $listTable .= "        </THEAD>\n";
        $listTable .= "        <TFOOT>\n";
        $listTable .= "            <!-- ���ߤϥեå����ϲ���ʤ� -->\n";
        $listTable .= "        </TFOOT>\n";
        $listTable .= "        <TBODY>\n";
        for ($r=0; $r<$rows; $r++) {
            $section_confirm = $this->getSectionConfirm($section_name[$r][1], $request); // �����ǧ�ǡ����μ���
            $listTable .= "    <tr>\n";
            $listTable .= "        <th class='winbox' rowspan='3' nowrap>". $section_name[$r][0] ."</th>\n";
            $listTable .= "        <th class='winbox' nowrap>������������</th>\n";
            if ($section_confirm[0] == '̤') {
                $targetDateStr = $yyyymm_b . '01';
                $targetDateEnd = $yyyymm_b . '10';
                $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B><a href='../working_hours_report_Main.php?AutoStart=y&targetSection=". $section_name[$r][1] ."&targetDateStr=". $targetDateStr ."&targetDateEnd=". $targetDateEnd ."&check_flg=y' target='_parent' style='text-decoration:none;color:red;'>". $section_confirm[0] ."</a></B></div></td>\n";
            } else if ($section_confirm[0] == '��') {
                $targetDateStr = $yyyymm_b . '01';
                $targetDateEnd = $yyyymm_b . '10';
                $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B><a href='../working_hours_report_Main.php?AutoStart=y&targetSection=". $section_name[$r][1] ."&targetDateStr=". $targetDateStr ."&targetDateEnd=". $targetDateEnd ."&check_flg=y' target='_parent' style='text-decoration:none;'>". $section_confirm[0] ."</a></B></div></td>\n";
            } else {
                $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B>". $section_confirm[0] ."</B></div></td>\n";
            }
            if ($section_confirm[1] == '̤') {
                $targetDateStr = $request->get('yyyymm') . '01';
                $targetDateEnd = $request->get('yyyymm') . '10';
                $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B><a href='../working_hours_report_Main.php?AutoStart=y&targetSection=". $section_name[$r][1] ."&targetDateStr=". $targetDateStr ."&targetDateEnd=". $targetDateEnd ."&check_flg=y' target='_parent' style='text-decoration:none;color:red;'>". $section_confirm[1] ."</a></B></div></td>\n";
            } else if ($section_confirm[1] == '��') {
                $targetDateStr = $request->get('yyyymm') . '01';
                $targetDateEnd = $request->get('yyyymm') . '10';
                $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B><a href='../working_hours_report_Main.php?AutoStart=y&targetSection=". $section_name[$r][1] ."&targetDateStr=". $targetDateStr ."&targetDateEnd=". $targetDateEnd ."&check_flg=y' target='_parent' style='text-decoration:none;'>". $section_confirm[1] ."</a></B></div></td>\n";
            } else {
                $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B>". $section_confirm[1] ."</B></div></td>\n";
            }
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <th class='winbox' nowrap>��������������</th>\n";
            if ($section_confirm[2] == '̤') {
                $targetDateStr = $yyyymm_b . '11';
                $targetDateEnd = $yyyymm_b . '20';
                $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B><a href='../working_hours_report_Main.php?AutoStart=y&targetSection=". $section_name[$r][1] ."&targetDateStr=". $targetDateStr ."&targetDateEnd=". $targetDateEnd ."&check_flg=y' target='_parent' style='text-decoration:none;color:red;'>". $section_confirm[2] ."</a></B></div></td>\n";
            } else if ($section_confirm[2] == '��') {
                $targetDateStr = $yyyymm_b . '11';
                $targetDateEnd = $yyyymm_b . '20';
                $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B><a href='../working_hours_report_Main.php?AutoStart=y&targetSection=". $section_name[$r][1] ."&targetDateStr=". $targetDateStr ."&targetDateEnd=". $targetDateEnd ."&check_flg=y' target='_parent' style='text-decoration:none;'>". $section_confirm[2] ."</a></B></div></td>\n";
            } else {
                $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B>". $section_confirm[2] ."</B></div></td>\n";
            }
            if ($section_confirm[3] == '̤') {
                $targetDateStr = $request->get('yyyymm') . '11';
                $targetDateEnd = $request->get('yyyymm') . '20';
                $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B><a href='../working_hours_report_Main.php?AutoStart=y&targetSection=". $section_name[$r][1] ."&targetDateStr=". $targetDateStr ."&targetDateEnd=". $targetDateEnd ."&check_flg=y' target='_parent' style='text-decoration:none;color:red;'>". $section_confirm[3] ."</a></B></div></td>\n";
            } else if ($section_confirm[3] == '��') {
                $targetDateStr = $request->get('yyyymm') . '11';
                $targetDateEnd = $request->get('yyyymm') . '20';
                $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B><a href='../working_hours_report_Main.php?AutoStart=y&targetSection=". $section_name[$r][1] ."&targetDateStr=". $targetDateStr ."&targetDateEnd=". $targetDateEnd ."&check_flg=y' target='_parent' style='text-decoration:none;'>". $section_confirm[3] ."</a></B></div></td>\n";
            } else {
                $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B>". $section_confirm[3] ."</B></div></td>\n";
            }
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <th class='winbox' nowrap>������������</th>\n";
            if ($section_confirm[4] == '̤') {
                $targetDateStr = $yyyymm_b . '21';
                $targetDateEnd = $yyyymm_b . $request->get('last_day_b');
                $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B><a href='../working_hours_report_Main.php?AutoStart=y&targetSection=". $section_name[$r][1] ."&targetDateStr=". $targetDateStr ."&targetDateEnd=". $targetDateEnd ."&check_flg=y' target='_parent' style='text-decoration:none;color:red;'>". $section_confirm[4] ."</a></B></div></td>\n";
            } else if ($section_confirm[4] == '��') {
                $targetDateStr = $yyyymm_b . '21';
                $targetDateEnd = $yyyymm_b . $request->get('last_day_b');
                $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B><a href='../working_hours_report_Main.php?AutoStart=y&targetSection=". $section_name[$r][1] ."&targetDateStr=". $targetDateStr ."&targetDateEnd=". $targetDateEnd ."&check_flg=y' target='_parent' style='text-decoration:none;'>". $section_confirm[4] ."</a></B></div></td>\n";
            } else {
                $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B>". $section_confirm[4] ."</B></div></td>\n";
            }
            if ($section_confirm[5] == '̤') {
                $targetDateStr = $request->get('yyyymm') . '21';
                $targetDateEnd = $request->get('yyyymm') . $request->get('last_day');
                $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B><a href='../working_hours_report_Main.php?AutoStart=y&targetSection=". $section_name[$r][1] ."&targetDateStr=". $targetDateStr ."&targetDateEnd=". $targetDateEnd ."&check_flg=y' target='_parent' style='text-decoration:none;color:red;'>". $section_confirm[5] ."</a></B></div></td>\n";
            } else if ($section_confirm[5] == '��') {
                $targetDateStr = $request->get('yyyymm') . '21';
                $targetDateEnd = $request->get('yyyymm') . $request->get('last_day');
                $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B><a href='../working_hours_report_Main.php?AutoStart=y&targetSection=". $section_name[$r][1] ."&targetDateStr=". $targetDateStr ."&targetDateEnd=". $targetDateEnd ."&check_flg=y' target='_parent' style='text-decoration:none;'>". $section_confirm[5] ."</a></B></div></td>\n";
            } else {
                $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B>". $section_confirm[5] ."</B></div></td>\n";
            }
        }
        $listTable .= "        </tr>\n";
        $listTable .= "        </TBODY>\n";
        $listTable .= "        </table>\n";
        $listTable .= "            </td></tr>\n";
        $listTable .= "        </table> <!----------------- ���ߡ�End ------------------>\n";
        $listTable .= "    </form>\n";
        $listTable .= "</center>\n";
        // return mb_convert_encoding($listTable, 'UTF-8');
        return $listTable;
    }
    
    ///// List��   ���Ƚ���Ȳ�ν����ǧ�����ǡ�������
    private function getViewMailHTMLbody($request, $menu)
    {
        // �����
        $listTable  = '';
        $yyyymm   = date('Ym'); $yyyy = date('Y'); $mm = date('m');  // �إå���ɽ����ǯ��׻�
        if ($mm == 1) {
            $yyyy_b   = $yyyy - 1;
            $mm_b     = 12;
            $yyyymm_b = $yyyy_b . $mm_b;
        } else {
            $yyyy_b   = $yyyy;
            $mm_b     = $mm - 1;
            if ($mm_b == 1) {
                $mm_b = '01';
            }
            if ($mm_b == 2) {
                $mm_b = '02';
            }
            if ($mm_b == 3) {
                $mm_b = '03';
            }
            if ($mm_b == 4) {
                $mm_b = '04';
            }
            if ($mm_b == 5) {
                $mm_b = '05';
            }
            if ($mm_b == 6) {
                $mm_b = '06';
            }
            if ($mm_b == 7) {
                $mm_b = '07';
            }
            if ($mm_b == 8) {
                $mm_b = '08';
            }
            if ($mm_b == 9) {
                $mm_b = '09';
            }
            $yyyymm_b = $yyyy . $mm_b;
        }
        $test_str = 0;
        $test_end = 0;
        
        if ($request->get('targetDateStr') != '') {
            $test_str = $request->get('targetDateStr');
            $test_end = $request->get('targetDateEnd');
        } else {
            $test_str = 1;
            $test_end = 1;
        }
        $listTable .= "<center>\n";
        $listTable .= "    <form name='CorrectForm' method='post' target='_parent'>\n";
        $listTable .= "        <table bgcolor='#d6d3ce' width='150' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "        <THEAD>\n";
        $listTable .= "            <!-- �ơ��֥� �إå�����ɽ�� -->\n";
        $listTable .= "            <tr>\n";
        $listTable .= "                <th class='winbox' nowrap>����</th>\n";
        $listTable .= "                <th class='winbox' nowrap>����</th>\n";
        $listTable .= "                <th class='winbox' nowrap>". $yyyy_b . "ǯ". $mm_b ."��</th>\n";
        $listTable .= "                <th class='winbox' nowrap>". $yyyy . "ǯ". $mm ."��</th>\n";
        $listTable .= "            </tr>\n";
        $listTable .= "        </THEAD>\n";
        $listTable .= "        <TFOOT>\n";
        $listTable .= "            <!-- ���ߤϥեå����ϲ���ʤ� -->\n";
        $listTable .= "        </TFOOT>\n";
        $listTable .= "        <TBODY>\n";
        $section_confirm = $this->getMailConfirm($request); // �᡼��ǡ����μ���
        $listTable .= "    <tr>\n";
        $listTable .= "        <th class='winbox' rowspan='3' nowrap>". $test_str . "�᡼������". $test_end . "</th>\n";
        $listTable .= "        <th class='winbox' nowrap>������������</th>\n";
        if ($section_confirm[0] == '̤') {
            $targetDateStr = $yyyymm_b . '01';
            $targetDateEnd = $yyyymm_b . '10';
            $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B><a href='../working_hours_report_Main.php?MailStart=y&targetDateStr=". $targetDateStr ."&targetDateEnd=". $targetDateEnd ."&check_flg=y' target='_parent' style='text-decoration:none;color:red;'>". $section_confirm[0] ."</a></B></div></td>\n";
        } else if ($section_confirm[0] == '��') {
            $targetDateStr = $yyyymm_b . '01';
            $targetDateEnd = $yyyymm_b . '10';
            $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B>��</B></div></td>\n";
        } else {
            $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B>". $section_confirm[0] ."</B></div></td>\n";
        }
        if ($section_confirm[1] == '̤') {
            $targetDateStr = $request->get('yyyymm') . '01';
            $targetDateEnd = $request->get('yyyymm') . '10';
            $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B><a href='../working_hours_report_Main.php?MailStart=y&targetDateStr=". $targetDateStr ."&targetDateEnd=". $targetDateEnd ."&check_flg=y' target='_parent' style='text-decoration:none;color:red;'>". $section_confirm[1] ."</a></B></div></td>\n";
        } else if ($section_confirm[1] == '��') {
            $targetDateStr = $request->get('yyyymm') . '01';
            $targetDateEnd = $request->get('yyyymm') . '10';
            $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B>��</B></div></td>\n";
        } else {
            $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B>". $section_confirm[1] ."</B></div></td>\n";
        }
        $listTable .= "    </tr>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <th class='winbox' nowrap>��������������</th>\n";
        if ($section_confirm[2] == '̤') {
            $targetDateStr = $yyyymm_b . '11';
            $targetDateEnd = $yyyymm_b . '20';
            $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B><a href='../working_hours_report_Main.php?MailStart=y&targetDateStr=". $targetDateStr ."&targetDateEnd=". $targetDateEnd ."&check_flg=y' target='_parent' style='text-decoration:none;color:red;'>". $section_confirm[2] ."</a></B></div></td>\n";
        } else if ($section_confirm[2] == '��') {
            $targetDateStr = $yyyymm_b . '11';
            $targetDateEnd = $yyyymm_b . '20';
            $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B>��</B></div></td>\n";
        } else {
            $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B>". $section_confirm[2] ."</B></div></td>\n";
        }
        if ($section_confirm[3] == '̤') {
            $targetDateStr = $request->get('yyyymm') . '11';
            $targetDateEnd = $request->get('yyyymm') . '20';
            $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B><a href='../working_hours_report_Main.php?MailStart=y&targetDateStr=". $targetDateStr ."&targetDateEnd=". $targetDateEnd ."&check_flg=y' target='_parent' style='text-decoration:none;color:red;'>". $section_confirm[3] ."</a></B></div></td>\n";
        } else if ($section_confirm[3] == '��') {
            $targetDateStr = $request->get('yyyymm') . '11';
            $targetDateEnd = $request->get('yyyymm') . '20';
            $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B>��</B></div></td>\n";
        } else {
            $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B>". $section_confirm[3] ."</B></div></td>\n";
        }
        $listTable .= "    </tr>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <th class='winbox' nowrap>������������</th>\n";
        if ($section_confirm[4] == '̤') {
            $targetDateStr = $yyyymm_b . '21';
            $targetDateEnd = $yyyymm_b . $request->get('last_day_b');
            $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B><a href='../working_hours_report_Main.php?MailStart=y&targetDateStr=". $targetDateStr ."&targetDateEnd=". $targetDateEnd ."&check_flg=y' target='_parent' style='text-decoration:none;color:red;'>". $section_confirm[4] ."</a></B></div></td>\n";
        } else if ($section_confirm[4] == '��') {
            $targetDateStr = $yyyymm_b . '21';
            $targetDateEnd = $yyyymm_b . $request->get('last_day_b');
            $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B>��</B></div></td>\n";
        } else {
            $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B>". $section_confirm[4] ."</B></div></td>\n";
        }
        if ($section_confirm[5] == '̤') {
            $targetDateStr = $request->get('yyyymm') . '21';
            $targetDateEnd = $request->get('yyyymm') . $request->get('last_day');
            $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B><a href='../working_hours_report_Main.php?MailStart=y&targetDateStr=". $targetDateStr ."&targetDateEnd=". $targetDateEnd ."&check_flg=y' target='_parent' style='text-decoration:none;color:red;'>". $section_confirm[5] ."</a></B></div></td>\n";
        } else if ($section_confirm[5] == '��') {
            $targetDateStr = $request->get('yyyymm') . '21';
            $targetDateEnd = $request->get('yyyymm') . $request->get('last_day');
            $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B>��</B></div></td>\n";
        } else {
            $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B>". $section_confirm[5] ."</B></div></td>\n";
        }
        $listTable .= "        </tr>\n";
        $listTable .= "        </TBODY>\n";
        $listTable .= "        </table>\n";
        $listTable .= "            </td></tr>\n";
        $listTable .= "        </table> <!----------------- ���ߡ�End ------------------>\n";
        $listTable .= "    </form>\n";
        $listTable .= "</center>\n";
        // return mb_convert_encoding($listTable, 'UTF-8');
        return $listTable;
    }
    
    ///// ����ID��������ν����ǧ���������
    private function getSectionConfirm($sid, $request)
    {
        // ���շ׻�
        $yyyymm   = date('Ym'); $yyyy = date('Y'); $mm = date('m');
        if ($mm == 1) {
            $yyyy_b   = $yyyy - 1;
            $mm_b     = 12;
            $yyyymm_b = $yyyy_b . $mm_b;
        } else {
            $yyyy_b   = $yyyy;
            $mm_b     = $mm - 1;
            $yyyymm_b = $yyyy . $mm_b;
        }
        if ($mm == 1) {
            $mm = '01';
        }
        if ($mm == 2) {
            $mm = '02';
        }
        if ($mm == 3) {
            $mm = '03';
        }
        if ($mm == 4) {
            $mm = '04';
        }
        if ($mm == 5) {
            $mm = '05';
        }
        if ($mm == 6) {
            $mm = '06';
        }
        if ($mm == 7) {
            $mm = '07';
        }
        if ($mm == 8) {
            $mm = '08';
        }
        if ($mm == 9) {
            $mm = '09';
        }
        if ($mm_b == 1) {
            $mm_b = '01';
        }
        if ($mm_b == 2) {
            $mm_b = '02';
        }
        if ($mm_b == 3) {
            $mm_b = '03';
        }
        if ($mm_b == 4) {
            $mm_b = '04';
        }
        if ($mm_b == 5) {
            $mm_b = '05';
        }        
        if ($mm_b == 6) {
            $mm_b = '06';
        }        
        if ($mm_b == 7) {
            $mm_b = '07';
        }        
        if ($mm_b == 8) {
            $mm_b = '08';
        }        
        if ($mm_b == 9) {
            $mm_b = '09';
        }        
        $last_day   = date("t", mktime(0, 0, 0, $mm, 1, $yyyy));         // �����ǽ����μ���(����)
        $day_num1   = 10;                                                // ������������������(����)
        $str_day1   = $yyyy . $mm . '01';                                // �������������γ�����(����)
        $end_day1   = $yyyy . $mm . '10';                                // �������������κǽ���(����)
        $day_num2   = 10;                                                // ��������������������(����)
        $str_day2   = $yyyy . $mm . '11';                                // ���������������γ�����(����)
        $end_day2   = $yyyy . $mm . '20';                                // ���������������κǽ���(����)
        $day_num3   = $last_day - 20;                                    // ���������ǽ���������(����)
        $str_day3   = $yyyy . $mm . '21';                                // ���������ǽ����γ�����(����)
        $end_day3   = $yyyy . $mm . $last_day;                           // ���������ǽ����κǽ���(����)
        $last_day_b = date("t", mktime(0, 0, 0, $mm_b, 1, $yyyy_b));     // �����ǽ����μ���(����)
        $day_num1_b = 10;                                                // ������������������(����)
        $str_day1_b = $yyyy_b . $mm_b . '01';                            // �������������γ�����(����)
        $end_day1_b = $yyyy_b . $mm_b . '10';                            // �������������κǽ���(����)
        $day_num2_b = 10;                                                // ��������������������(����)
        $str_day2_b = $yyyy_b . $mm_b . '11';                            // ���������������γ�����(����)
        $end_day2_b = $yyyy_b . $mm_b . '20';                            // ���������������κǽ���(����)
        $day_num3_b = $last_day_b - 20;                                  // ���������ǽ���������(����)
        $str_day3_b = $yyyy_b . $mm_b . '21';                            // ���������ǽ����γ�����(����)
        $end_day3_b = $yyyy_b . $mm_b . $last_day_b;                     // ���������ǽ����κǽ���(����)
        $str_day = array($str_day1_b, $str_day1, $str_day2_b, $str_day2, $str_day3_b, $str_day3);
        $end_day = array($end_day1_b, $end_day1, $end_day2_b, $end_day2, $end_day3_b, $end_day3);
        $day_num = array($day_num1_b, $day_num1, $day_num2_b, $day_num2, $day_num3_b, $day_num3);
        $section_confirm = array();                                // ��ǧ��Ͽ��̤����
        for ($i=0; $i<6; $i++) {
            $query = $this->getSectionUser($sid, $request->get('targetPosition'), $str_day[$i]);    // ����ɣ���ν�°�ݰ������
            $res = array();
            if ($this->getResult($query, $res) < 1) {              // ��°��ï�⤤�ʤ����---ɽ��
                $section_confirm[0] = '---';                       // �������������γ�ǧ(����)
                $section_confirm[1] = '---';                       // �������������γ�ǧ(����)
                $section_confirm[2] = '---';                       // ���������������γ�ǧ(����)
                $section_confirm[3] = '---';                       // ���������������γ�ǧ(����)
                $section_confirm[4] = '---';                       // ���������ǽ����γ�ǧ(����)
                $section_confirm[5] = '---';                       // ���������ǽ����γ�ǧ(����)
            } else {
                $rows = count($res);
                // �����ǧ
                for ($r=0; $r<$rows; $r++) {
                    $query="SELECT COUNT(*)
                            FROM working_hours_report_confirm
                            WHERE uid='{$res[$r][0]}' AND working_date>='$str_day[$i]' AND working_date<='$end_day[$i]'
                    ";
                    $res_c = array();
                    $this->getResult2($query, $res_c);
                    if($res_c[0][0]!=$day_num[$i]) {    // ��ǧ��Ͽ�θĿ����������
                        $section_confirm[$i] = '̤';    // ��ͤǤ�̤����οͤ������̤
                        break;
                    } else {
                        $section_confirm[$i] = '��';
                    }
                }
                
            }
        }
        $request->add('yyyymm', $yyyymm);                // ����
        $request->add('last_day', $last_day);            // ����ǽ���
        $request->add('yyyymm_b', $yyyymm_b);            // ����
        $request->add('last_day_b', $last_day_b);        // ����ǽ���
        return $section_confirm;
    }
    
    public function sendChkMail($request)
    {
        ///// �ѥ�᡼������ʬ��
        $str_date   = $request->get('targetDateStr');
        $end_date   = $request->get('targetDateEnd');
        
        $str_year   = substr($str_date, 0, 4);
        $str_month  = substr($str_date, 4, 2);
        $str_day    = substr($str_date, 6, 2);
        $end_year   = substr($end_date, 0, 4);
        $end_month  = substr($end_date, 4, 2);
        $end_day    = substr($end_date, 6, 2);
        
        $subject      = '�����ǧ����η�';             // �᡼���̾
        $sponsor_addr = 'usoumu@nitto-kohki.co.jp';     // ���������ɥ쥹
        //$atten      = $request->get('atten');         // ������(attendance) (����)
        $atten      = array();
        $atten      = array('017850','009580','012980','017728','018040','015202','016713','011045','014834');          // ������(attendance) (����)
        $atten_num  = count($atten);            // �����Կ�
        // �����Ԥ�̾������ (�������Ĥ���������)
        $this->getAttendanceName($atten, $atten_name, $flag);
        // �����ԤΥ᡼�륢�ɥ쥹�μ����ȥ᡼������
        for ($i=0; $i<$atten_num; $i++) {
            if ($flag[$i] == 'NG') continue;
            // �����ԤΥ᡼�륢�ɥ쥹����
            if ( !($atten_addr=$this->getAttendanceAddr($atten[$i])) ) {
                continue;
            }
            $to_addres = $atten_addr;
            //$message  = "���ΰ���� {$sponsor_name} ���󤬽��ʼԤ˥᡼������Ф�����ˤ��������������줿��ΤǤ���\n\n";
            //$message .= "{$subject}\n\n";
            $message  = "����Ĺ�ư�\n\n";
            $message .= "{$str_year}ǯ {$str_month}�� {$str_day}�� �� {$end_year}ǯ {$end_month}�� {$end_day}����\n\n";
            $message .= "����Ϥ����Ϥ���λ���ޤ����Τǡ�����γ�ǧ���������ꤤ�פ��ޤ���\n\n";
            $message .= "��̳��\n\n";
            $add_head = "From: {$sponsor_addr}\r\nReply-To: {$sponsor_addr}";
            $attenSubject = '���衧 ' . $atten_name[$i] . ' �͡� ' . $subject;
            if (mb_send_mail($to_addres, $subject, $message, $add_head)) {
                // �᡼�������������¸
                $this->setAttendanceMailHistory($str_date, $end_date);
            }
        }
        return true;
    }
    
    ////////// �����Ԥ�̾������
    private function getAttendanceName($atten, &$atten_name, &$flag)
    {
        $atten_num = count($atten);
        $atten_name = array();
        $flag = array();
        for ($i=0; $i<$atten_num; $i++) {
            $query = "
                SELECT trim(name) FROM user_detailes WHERE uid = '{$atten[$i]}' AND retire_date IS NULL AND sid != 31
            ";
            $atten_name[$i] = '';
            if ($this->getUniResult($query, $atten_name[$i]) < 1) {
                $_SESSION['s_sysmsg'] .= "�᡼�����ǽ��ʼԤ�̾�������Ĥ���ޤ��� [ {$atten[$i]} ]";
                $flag[$i] = 'NG';
            } else {
                $flag[$i] = 'OK';
            }
        }
    }
    
    ////////// �����ԤΥ᡼�륢�ɥ쥹����
    private function getAttendanceAddr($atten)
    {
        $query = "
            SELECT trim(mailaddr) FROM user_master WHERE uid = '{$atten}'
        ";
        $atten_addr = '';
        if ($this->getUniResult($query, $atten_addr) < 1) {
            $_SESSION['s_sysmsg'] .= "�᡼�����ǽ��ʼԤΥ᡼�륢�ɥ쥹�����Ĥ���ޤ��� [ {$atten} ]";
        }
        return $atten_addr;
    }
    
    ////////// �᡼�������������¸
    private function setAttendanceMailHistory($str_date, $end_date)
    {
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SESSION['User_ID'];
        $sql = "
                INSERT INTO working_hours_report_mail
                (working_date, working_enddate, confirm, last_date, last_user)
                VALUES
                ($str_date, $end_date, TRUE, '$last_date', $last_user)
                ;
               ";
        query_affected($sql);
    }
    
    ///// ����ID��������ν����ǧ���������
    private function getMailConfirm($request)
    {
        // ���շ׻�
        $yyyymm   = date('Ym'); $yyyy = date('Y'); $mm = date('m');
        if ($mm == 1) {
            $yyyy_b   = $yyyy - 1;
            $mm_b     = 12;
            $yyyymm_b = $yyyy_b . $mm_b;
        } else {
            $yyyy_b   = $yyyy;
            $mm_b     = $mm - 1;
            $yyyymm_b = $yyyy . $mm_b;
        }
        if ($mm == 1) {
            $mm = '01';
        }
        if ($mm == 2) {
            $mm = '02';
        }
        if ($mm == 3) {
            $mm = '03';
        }
        if ($mm == 4) {
            $mm = '04';
        }
        if ($mm == 5) {
            $mm = '05';
        }
        if ($mm == 6) {
            $mm = '06';
        }
        if ($mm == 7) {
            $mm = '07';
        }
        if ($mm == 8) {
            $mm = '08';
        }
        if ($mm == 9) {
            $mm = '09';
        }
        if ($mm_b == 1) {
            $mm_b = '01';
        }
        if ($mm_b == 2) {
            $mm_b = '02';
        }
        if ($mm_b == 3) {
            $mm_b = '03';
        }
        if ($mm_b == 4) {
            $mm_b = '04';
        }
        if ($mm_b == 5) {
            $mm_b = '05';
        }        
        if ($mm_b == 6) {
            $mm_b = '06';
        }        
        if ($mm_b == 7) {
            $mm_b = '07';
        }        
        if ($mm_b == 8) {
            $mm_b = '08';
        }        
        if ($mm_b == 9) {
            $mm_b = '09';
        }        
        $last_day   = date("t", mktime(0, 0, 0, $mm, 1, $yyyy));         // �����ǽ����μ���(����)
        $day_num1   = 10;                                                // ������������������(����)
        $str_day1   = $yyyy . $mm . '01';                                // �������������γ�����(����)
        $end_day1   = $yyyy . $mm . '10';                                // �������������κǽ���(����)
        $day_num2   = 10;                                                // ��������������������(����)
        $str_day2   = $yyyy . $mm . '11';                                // ���������������γ�����(����)
        $end_day2   = $yyyy . $mm . '20';                                // ���������������κǽ���(����)
        $day_num3   = $last_day - 20;                                    // ���������ǽ���������(����)
        $str_day3   = $yyyy . $mm . '21';                                // ���������ǽ����γ�����(����)
        $end_day3   = $yyyy . $mm . $last_day;                           // ���������ǽ����κǽ���(����)
        $last_day_b = date("t", mktime(0, 0, 0, $mm_b, 1, $yyyy_b));     // �����ǽ����μ���(����)
        $day_num1_b = 10;                                                // ������������������(����)
        $str_day1_b = $yyyy_b . $mm_b . '01';                            // �������������γ�����(����)
        $end_day1_b = $yyyy_b . $mm_b . '10';                            // �������������κǽ���(����)
        $day_num2_b = 10;                                                // ��������������������(����)
        $str_day2_b = $yyyy_b . $mm_b . '11';                            // ���������������γ�����(����)
        $end_day2_b = $yyyy_b . $mm_b . '20';                            // ���������������κǽ���(����)
        $day_num3_b = $last_day_b - 20;                                  // ���������ǽ���������(����)
        $str_day3_b = $yyyy_b . $mm_b . '21';                            // ���������ǽ����γ�����(����)
        $end_day3_b = $yyyy_b . $mm_b . $last_day_b;                     // ���������ǽ����κǽ���(����)
        $str_day = array($str_day1_b, $str_day1, $str_day2_b, $str_day2, $str_day3_b, $str_day3);
        $end_day = array($end_day1_b, $end_day1, $end_day2_b, $end_day2, $end_day3_b, $end_day3);
        $day_num = array($day_num1_b, $day_num1, $day_num2_b, $day_num2, $day_num3_b, $day_num3);
        $section_confirm = array();                                // ��ǧ��Ͽ��̤����
        for ($i=0; $i<6; $i++) {
            $query="SELECT confirm
                        FROM working_hours_report_mail
                        WHERE working_date>='$str_day[$i]' AND working_enddate<='$end_day[$i]'
                    ";
            $res_c = array();
            //$this->getResult2($query, $res_c);
            if ( $this->getResult2($query, $res_c) > 0 ) {    // ��Ͽ���� �᡼�����������å�
                if($res_c[0][0] == 't') {    // �᡼���������򤬤���кѤ�
                    $section_confirm[$i] = '��';
                } else {
                    $section_confirm[$i] = '̤';
                }
            } else {                                    // ��Ͽ̵�� �᡼��̤����
                $section_confirm[$i] = '̤';
            }
        }
        $request->add('yyyymm', $yyyymm);                // ����
        $request->add('last_day', $last_day);            // ����ǽ���
        $request->add('yyyymm_b', $yyyymm_b);            // ����
        $request->add('last_day_b', $last_day_b);        // ����ǽ���
        return $section_confirm;
    }
    
    // ����γ�ǧ��Ͽ
    public function setConfirmData($request)
    {
        $sid        = $request->get('section_id');
        $str_date   = $request->get('str_date');
        $end_date   = $request->get('end_date');
        $format_str = format_date($str_date);
        $format_end = format_date($end_date);
        $str_ym     = substr($str_date, 0, 6);
        $end_ym     = substr($end_date, 0, 6);
        if ($str_ym != $end_ym) {
            $_SESSION['s_sysmsg'] .= "�����Ʊ�����ǳ�ǧ���Ƥ����������� ��ǧ�оݡ�{$format_str} �� {$format_end}";      // .= �����
            $msg_flg = 'alert';
            return false;
        }
        $str_day    = substr($str_date, 6, 2);
        $end_day    = substr($end_date, 6, 2);
        $date_num   = $end_day - $str_day + 1;
        $query = $this->getSectionUser($sid, $request->get('targetPosition'), $str_date);             // �����������°�Ұ������
        if ($rows=getResult($query,$res)) {
            for ($i=0; $i<$rows; $i++) {
                $uid[$i] = $res[$i]['uid'];
            }
            // �����ߤ��ʤ��Τǥ����Ȳ�
            /*
            if ($request->get('targetSection') == 4) {                  // ��������°�Ұ����б�
                $uid[$i] = '000817';                                    // 000817=������ ���Ӥ���
                $res[$i]['sid'] = 9;                                    // sid�˴��������ɲ�
                $rows = $rows + 1;                                      // sid�ɲäΰ�$rows�⣱�ɲ�
            }
            */
            $s_name = $this->getSectionName($rows,$res);  // ������������祳���ɤ������̾�����
        } else {
            $_SESSION['s_sysmsg'] .= "{$format_str} �� {$format_end}�ν����ǧ��Ͽ�˼��Ԥ��ޤ�����";      // .= �����
            $msg_flg = 'alert';
            return false;
        }
        $sql = '';
        $working_date = $str_date;
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SESSION['User_ID'];
        for ($r=0; $r<$rows; $r++) {
            $working_date = $str_date;
            for ($i=0; $i<$date_num; $i++) {
                $query = sprintf("SELECT * FROM working_hours_report_confirm WHERE uid='%s' AND working_date=%d", $uid[$r], $working_date);
                $res_chk = array();
                if ( getResult($query, $res_chk) > 0 ) {    // ��Ͽ���� UPDATE ����
                    $sql .= "
                        UPDATE working_hours_report_confirm SET
                        confirm=TRUE, last_date='$last_date', last_user=$last_user
                        WHERE uid={$uid[$r]} AND working_date={$working_date}
                        ;
                    ";
                } else {                                    // ��Ͽ�ʤ� INSERT ����
                    $sql .= "
                        INSERT INTO working_hours_report_confirm
                        (uid, working_date, confirm, last_date, last_user)
                        VALUES
                        ('{$uid[$r]}', $working_date, TRUE, '$last_date', $last_user)
                        ;
                    ";
                }
                $working_date = $working_date + 1;
            }
        }
        if (query_affected($sql) <= 0) {
            $_SESSION['s_sysmsg'] .= "{$format_str} �� {$format_end}�ν����ǧ��Ͽ���ԡ�";      // .= �����
            $msg_flg = 'alert';
            return false;
        } else {
            $_SESSION['s_sysmsg'] .= "{$format_str} �� {$format_end}�ν����ǧ��Ͽ������"; // .= �����
            return true;
        }
    }
    
    // ����γ�ǧ��Ͽ(�Ŀ͡�
    public function setConfirmOneData($request)
    {
        $uid         = $request->get('uid');
        $str_date    = $request->get('str_date');
        $end_date    = $request->get('end_date');
        $confirm_flg = $request->get('confirm_flg');
        $format_str = format_date($str_date);
        $format_end = format_date($end_date);
        $str_ym     = substr($str_date, 0, 6);
        $end_ym     = substr($end_date, 0, 6);
        if ($str_ym != $end_ym) {
            $_SESSION['s_sysmsg'] .= "�����Ʊ�����ǳ�ǧ���Ƥ����������� ��ǧ�оݡ�{$format_str} �� {$format_end}";      // .= �����
            $msg_flg = 'alert';
            return false;
        }
        $str_day    = substr($str_date, 6, 2);
        $end_day    = substr($end_date, 6, 2);
        $date_num   = $end_day - $str_day + 1;
        $sql = '';
        $working_date = $str_date;
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SESSION['User_ID'];
        $working_date = $str_date;
        for ($i=0; $i<$date_num; $i++) {
            $query = sprintf("SELECT * FROM working_hours_report_confirm WHERE uid='%s' AND working_date=%d", $uid, $working_date);
            $res_chk = array();
            if ( getResult($query, $res_chk) > 0 ) {    // ��Ͽ���� UPDATE ����
                $sql .= "
                    UPDATE working_hours_report_confirm SET
                    confirm=TRUE, confirm_flg=$confirm_flg, last_date='{$last_date}', last_user=$last_user
                    WHERE uid='{$uid}' AND working_date={$working_date}
                    ;
                ";
            } else {                                    // ��Ͽ�ʤ� INSERT ����
                $sql .= "
                    INSERT INTO working_hours_report_confirm
                    (uid, working_date, working_enddate, confirm, confirm_flg, last_date, last_user)
                    VALUES
                    ('{$uid}', $working_date, $end_date, TRUE, $confirm_flg, '$last_date', $last_user)
                    ;
                ";
            }
            $working_date = $working_date + 1;
        }
        if (query_affected($sql) <= 0) {
            $_SESSION['s_sysmsg'] .= "{$format_str} �� {$format_end}�ν����ǧ��Ͽ���ԡ�";      // .= �����
            $msg_flg = 'alert';
            return false;
        } else {
            $_SESSION['s_sysmsg'] .= "{$format_str} �� {$format_end}�ν����ǧ��Ͽ������"; // .= �����
            return true;
        }
    }
    ////// List��   ����ɽ�� �إå����������
    private function getViewHTMLheader($request)
    {
        // �����ȥ��SQL�Υ��ȥ����ɥץ������㡼�������
        $query = "SELECT parts_stock_title('{$request->get('targetPartsNo')}')";
        $title = '';
        $this->getUniResult($query, $title);
        if (!$title) {                        // �쥳���ɤ�̵������NULL�쥳���ɤ��֤뤿���ѿ������Ƥǥ����å�����
            $title = '�����ƥ�ޥ�����̤��Ͽ��';
        }
        // �����
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <th class='winbox' colspan='11'>{$title}</th>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <th class='winbox' width=' 5%'>No</th>\n";
        $listTable .= "        <th class='winbox' width=' 8%'>������</th>\n";
        $listTable .= "        <th class='winbox' width=' 8%'>�»���</th>\n";
        $listTable .= "        <th class='winbox' width='10%'>�ײ��ֹ�</th>\n";
        $listTable .= "        <th class='winbox' width='12%'>�����ֹ�</th>\n";
        $listTable .= "        <th class='winbox' width='18%'>�����ʡ�̾</th>\n";
        $listTable .= "        <th class='winbox' width=' 9%'>������</th>\n";
        $listTable .= "        <th class='winbox' width=' 9%'>ȯ���</th>\n";
        $listTable .= "        <th class='winbox' width=' 9%'>ͭ����</th>\n";
        $listTable .= "        <th class='winbox' width=' 4%'>CK</th>\n";
        $listTable .= "        <th class='winbox' width=' 8%'>����</th>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        return $listTable;
    }
    
    ////// List��   ����ɽ�� �եå����������
    private function getViewHTMLfooter($request)
    {
        // ���
        $t_fixed_hour  = floor($request->get('total_fixed_time') / 60);       // ������֤λ�����ʬ�׻�
        $t_fixed_min   = $request->get('total_fixed_time')%60;                // ������֤�ʬ����ʬ�׻�
        $t_extend_hour = floor($request->get('total_extend_time') / 60);      // ��Ĺ���֤λ�����ʬ�׻�
        $t_extend_min  = $request->get('total_extend_time')%60;               // ��Ĺ���֤�ʬ����ʬ�׻�
        $t_over_hour   = floor($request->get('total_overtime') / 60);         // ��лĶȻ��֤λ�����ʬ�׻�
        $t_over_min    = $request->get('total_overtime')%60;                  // ��лĶȤ�ʬ����ʬ�׻�
        $t_mid_hour    = floor($request->get('total_midnight_over') / 60);    // ����ĶȻ��֤λ�����ʬ�׻�
        $t_mid_min     = $request->get('total_midnight_over')%60;             // ����ĶȻ��֤�ʬ����ʬ�׻�
        $t_hotime_hour = floor($request->get('total_holiday_time') / 60);     // �ٽл��֤λ�����ʬ�׻�
        $t_hotime_min  = $request->get('total_holiday_time')%60;              // �ٽл��֤�ʬ����ʬ�׻�
        $t_hoover_hour = floor($request->get('total_holiday_over') / 60);     // �ٽлĶȻ��֤λ�����ʬ�׻�
        $t_hoover_min  = $request->get('total_holiday_over')%60;              // �ٽлĶȻ��֤�ʬ����ʬ�׻�
        $t_homid_hour  = floor($request->get('total_holiday_mid') / 60);      // �ٽп�����֤λ�����ʬ�׻�
        $t_homid_min   = $request->get('total_holiday_mid')%60;               // �ٽп�����֤�ʬ����ʬ�׻�
        $t_legal_hour  = floor($request->get('total_legal_time') / 60);       // ˡ����֤λ�����ʬ�׻�
        $t_legal_min   = $request->get('total_legal_time')%60;                // ˡ����֤�ʬ����ʬ�׻�
        $t_leover_hour = floor($request->get('total_legal_over') / 60);       // ˡ��ĶȻ��֤λ�����ʬ�׻�
        $t_leover_min  = $request->get('total_legal_over')%60;                // ˡ��ĶȻ��֤�ʬ����ʬ�׻�
        $t_late_hour   = floor($request->get('total_late_time') / 60);        // �ٹ�������֤λ�����ʬ�׻�
        $t_late_min    = $request->get('total_late_time')%60;                 // �ٹ�������֤�ʬ����ʬ�׻�
        // �Ұ�
        $t_fixed_hour_s  = floor($request->get('total_fixed_time_s') / 60);       // ������֤λ�����ʬ�׻�
        $t_fixed_min_s   = $request->get('total_fixed_time_s')%60;                // ������֤�ʬ����ʬ�׻�
        $t_extend_hour_s = floor($request->get('total_extend_time_s') / 60);      // ��Ĺ���֤λ�����ʬ�׻�
        $t_extend_min_s  = $request->get('total_extend_time_s')%60;               // ��Ĺ���֤�ʬ����ʬ�׻�
        $t_over_hour_s   = floor($request->get('total_overtime_s') / 60);         // ��лĶȻ��֤λ�����ʬ�׻�
        $t_over_min_s    = $request->get('total_overtime_s')%60;                  // ��лĶȤ�ʬ����ʬ�׻�
        $t_mid_hour_s    = floor($request->get('total_midnight_over_s') / 60);    // ����ĶȻ��֤λ�����ʬ�׻�
        $t_mid_min_s     = $request->get('total_midnight_over_s')%60;             // ����ĶȻ��֤�ʬ����ʬ�׻�
        $t_hotime_hour_s = floor($request->get('total_holiday_time_s') / 60);     // �ٽл��֤λ�����ʬ�׻�
        $t_hotime_min_s  = $request->get('total_holiday_time_s')%60;              // �ٽл��֤�ʬ����ʬ�׻�
        $t_hoover_hour_s = floor($request->get('total_holiday_over_s') / 60);     // �ٽлĶȻ��֤λ�����ʬ�׻�
        $t_hoover_min_s  = $request->get('total_holiday_over_s')%60;              // �ٽлĶȻ��֤�ʬ����ʬ�׻�
        $t_homid_hour_s  = floor($request->get('total_holiday_mid_s') / 60);      // �ٽп�����֤λ�����ʬ�׻�
        $t_homid_min_s   = $request->get('total_holiday_mid_s')%60;               // �ٽп�����֤�ʬ����ʬ�׻�
        $t_legal_hour_s  = floor($request->get('total_legal_time_s') / 60);       // ˡ����֤λ�����ʬ�׻�
        $t_legal_min_s   = $request->get('total_legal_time_s')%60;                // ˡ����֤�ʬ����ʬ�׻�
        $t_leover_hour_s = floor($request->get('total_legal_over_s') / 60);       // ˡ��ĶȻ��֤λ�����ʬ�׻�
        $t_leover_min_s  = $request->get('total_legal_over_s')%60;                // ˡ��ĶȻ��֤�ʬ����ʬ�׻�
        $t_late_hour_s   = floor($request->get('total_late_time_s') / 60);        // �ٹ�������֤λ�����ʬ�׻�
        $t_late_min_s    = $request->get('total_late_time_s')%60;                 // �ٹ�������֤�ʬ����ʬ�׻�
        // �ѡ���
        $t_fixed_hour_p  = floor($request->get('total_fixed_time_p') / 60);       // ������֤λ�����ʬ�׻�
        $t_fixed_min_p   = $request->get('total_fixed_time_p')%60;                // ������֤�ʬ����ʬ�׻�
        $t_extend_hour_p = floor($request->get('total_extend_time_p') / 60);      // ��Ĺ���֤λ�����ʬ�׻�
        $t_extend_min_p  = $request->get('total_extend_time_p')%60;               // ��Ĺ���֤�ʬ����ʬ�׻�
        $t_over_hour_p   = floor($request->get('total_overtime_p') / 60);         // ��лĶȻ��֤λ�����ʬ�׻�
        $t_over_min_p    = $request->get('total_overtime_p')%60;                  // ��лĶȤ�ʬ����ʬ�׻�
        $t_mid_hour_p    = floor($request->get('total_midnight_over_p') / 60);    // ����ĶȻ��֤λ�����ʬ�׻�
        $t_mid_min_p     = $request->get('total_midnight_over_p')%60;             // ����ĶȻ��֤�ʬ����ʬ�׻�
        $t_hotime_hour_p = floor($request->get('total_holiday_time_p') / 60);     // �ٽл��֤λ�����ʬ�׻�
        $t_hotime_min_p  = $request->get('total_holiday_time_p')%60;              // �ٽл��֤�ʬ����ʬ�׻�
        $t_hoover_hour_p = floor($request->get('total_holiday_over_p') / 60);     // �ٽлĶȻ��֤λ�����ʬ�׻�
        $t_hoover_min_p  = $request->get('total_holiday_over_p')%60;              // �ٽлĶȻ��֤�ʬ����ʬ�׻�
        $t_homid_hour_p  = floor($request->get('total_holiday_mid_p') / 60);      // �ٽп�����֤λ�����ʬ�׻�
        $t_homid_min_p   = $request->get('total_holiday_mid_p')%60;               // �ٽп�����֤�ʬ����ʬ�׻�
        $t_legal_hour_p  = floor($request->get('total_legal_time_p') / 60);       // ˡ����֤λ�����ʬ�׻�
        $t_legal_min_p   = $request->get('total_legal_time_p')%60;                // ˡ����֤�ʬ����ʬ�׻�
        $t_leover_hour_p = floor($request->get('total_legal_over_p') / 60);       // ˡ��ĶȻ��֤λ�����ʬ�׻�
        $t_leover_min_p  = $request->get('total_legal_over_p')%60;                // ˡ��ĶȻ��֤�ʬ����ʬ�׻�
        $t_late_hour_p   = floor($request->get('total_late_time_p') / 60);        // �ٹ�������֤λ�����ʬ�׻�
        $t_late_min_p    = $request->get('total_late_time_p')%60;                 // �ٹ�������֤�ʬ����ʬ�׻�
        // �Ұ�
        // �����
        $listTable = '';
        $listTable .= "<BR><CENTER>\n";
        $listTable .= "<B><U><font size='2'>�����������֡���". format_date($request->get('targetDateStr')) ."������". format_date($request->get('targetDateEnd')) ."�����סʽи��ԤϽ�����</U></B>\n";
        $listTable .= "</CENTER>\n";
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";                                             // ���ץǡ�����ɽ��
        $listTable .= "        <th class='winbox'>��</th>\n";
        $listTable .= "        <th class='winbox'>��</th>\n";
        $listTable .= "        <th class='winbox'>��</th>\n";
        $listTable .= "        <th class='winbox'>��</th>\n";
        $listTable .= "        <th class='winbox'>��</th>\n";
        $listTable .= "        <th class='winbox'>��</th>\n";
        $listTable .= "        <th class='winbox'>�������</th>\n";
        $listTable .= "        <th class='winbox'>��Ĺ����</th>\n";
        $listTable .= "        <th class='winbox'>��лĶ�</th>\n";
        $listTable .= "        <th class='winbox'>����Ķ�</th>\n";
        $listTable .= "        <th class='winbox'>�ٽл���</th>\n";
        $listTable .= "        <th class='winbox'>�ٽлĶ�</th>\n";
        $listTable .= "        <th class='winbox'>�ٽп���</th>\n";
        $listTable .= "        <th class='winbox'>ˡ�����</th>\n";
        $listTable .= "        <th class='winbox'>ˡ��Ķ�</th>\n";
        $listTable .= "        <th class='winbox'>�ٹ�����</th>\n";
        $listTable .= "        <th class='winbox'>��</th>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td class='winbox'>��</td>\n";
        $listTable .= "        <td class='winbox'>��</td>\n";
        $listTable .= "        <td class='winbox'>��</td>\n";
        $listTable .= "        <td class='winbox'>��</td>\n";
        $listTable .= "        <td class='winbox'>��</td>\n";
        $listTable .= "        <td class='winbox'>�ҡ���</td>\n";
        if ($t_fixed_min_s == 0) {                                      // ������ֽ���ɽ��
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_fixed_hour_s .":". $t_fixed_min_s ."0</td>\n";
        } else if ($t_fixed_min_s < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_fixed_hour_s .":0". $t_fixed_min_s ."</td>\n";
        } else {                                                             
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_fixed_hour_s .":". $t_fixed_min_s ."</td>\n";
        }
        if ($t_extend_min_s == 0) {                                     // ��Ĺ���ֽ���ɽ��
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_extend_hour_s .":". $t_extend_min_s ."0</td>\n";
        } else if ($t_extend_min_s < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_extend_hour_s .":0". $t_extend_min_s ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_extend_hour_s .":". $t_extend_min_s ."</td>\n";
        }
        if ($t_over_min_s == 0) {                                       // ��лĶȻ��ֽ���ɽ��
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_over_hour_s .":". $t_over_min_s ."0</td>\n";
        } else if ($t_over_min_s < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_over_hour_s .":0". $t_over_min_s ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_over_hour_s .":". $t_over_min_s ."</td>\n";
        }
        if ($t_mid_min_s == 0) {                                        // ����ĶȽ���ɽ��
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_mid_hour_s .":". $t_mid_min_s ."0</td>\n";
        } else if ($t_mid_min_s < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_mid_hour_s .":0". $t_mid_min_s ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_mid_hour_s .":". $t_mid_min_s ."</td>\n";
        }
        if ($t_hotime_min_s == 0) {                                     // �ٽл��ֽ���ɽ��
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_hotime_hour_s .":". $t_hotime_min_s ."0</td>\n";
        } else if ($t_hotime_min_s < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_hotime_hour_s .":0". $t_hotime_min_s ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_hotime_hour_s .":". $t_hotime_min_s ."</td>\n";
        }
        if ($t_hoover_min_s == 0) {                                     // �ٽлĶȽ���ɽ��
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_hoover_hour_s .":". $t_hoover_min_s ."0</td>\n";
        } else if ($t_hoover_min_s < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_hoover_hour_s .":0". $t_hoover_min_s ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_hoover_hour_s .":". $t_hoover_min_s ."</td>\n";
        }
        if ($t_homid_min_s == 0) {                                      // �ٽп��뽸��ɽ��
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_homid_hour_s .":". $t_homid_min_s ."0</td>\n";
        } else if ($t_homid_min_s < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_homid_hour_s .":0". $t_homid_min_s ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_homid_hour_s .":". $t_homid_min_s ."</td>\n";
        }
        if ($t_legal_min_s == 0) {                                      // ˡ����ֽ���ɽ��
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_legal_hour_s .":". $t_legal_min_s ."0</td>\n";
        } else if ($t_legal_min_s < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_legal_hour_s .":0". $t_legal_min_s ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_legal_hour_s .":". $t_legal_min_s ."</td>\n";
        }
        if ($t_leover_min_s == 0) {                                     // ˡ��ĶȽ���ɽ��
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_leover_hour_s .":". $t_leover_min_s ."0</td>\n";
        } else if ($t_leover_min_s < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_leover_hour_s .":0". $t_leover_min_s ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_leover_hour_s .":". $t_leover_min_s ."</td>\n";
        }
        if ($t_late_min_s == 0) {                                       // �ٹ�������ֽ���ɽ��
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_late_hour_s .":". $t_late_min_s ."0</td>\n";
        } else if ($t_late_min_s < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_late_hour_s .":0". $t_late_min_s ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_late_hour_s .":". $t_late_min_s ."</td>\n";
        }
        $listTable .= "        <td class='winbox'>��</td>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td class='winbox'>��</td>\n";
        $listTable .= "        <td class='winbox'>��</td>\n";
        $listTable .= "        <td class='winbox'>��</td>\n";
        $listTable .= "        <td class='winbox'>��</td>\n";
        $listTable .= "        <td class='winbox'>��</td>\n";
        $listTable .= "        <td class='winbox'>�ѡ���</td>\n";
        if ($t_fixed_min_p == 0) {                                      // ������ֽ���ɽ��
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_fixed_hour_p .":". $t_fixed_min_p ."0</td>\n";
        } else if ($t_fixed_min_p < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_fixed_hour_p .":0". $t_fixed_min_p ."</td>\n";
        } else {                                                             
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_fixed_hour_p .":". $t_fixed_min_p ."</td>\n";
        }
        if ($t_extend_min_p == 0) {                                     // ��Ĺ���ֽ���ɽ��
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_extend_hour_p .":". $t_extend_min_p ."0</td>\n";
        } else if ($t_extend_min_p < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_extend_hour_p .":0". $t_extend_min_p ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_extend_hour_p .":". $t_extend_min_p ."</td>\n";
        }
        if ($t_over_min_p == 0) {                                       // ��лĶȻ��ֽ���ɽ��
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_over_hour_p .":". $t_over_min_p ."0</td>\n";
        } else if ($t_over_min_p < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_over_hour_p .":0". $t_over_min_p ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_over_hour_p .":". $t_over_min_p ."</td>\n";
        }
        if ($t_mid_min_p == 0) {                                        // ����ĶȽ���ɽ��
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_mid_hour_p .":". $t_mid_min_p ."0</td>\n";
        } else if ($t_mid_min_p < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_mid_hour_p .":0". $t_mid_min_p ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_mid_hour_p .":". $t_mid_min_p ."</td>\n";
        }
        if ($t_hotime_min_p == 0) {                                     // �ٽл��ֽ���ɽ��
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_hotime_hour_p .":". $t_hotime_min_p ."0</td>\n";
        } else if ($t_hotime_min_p < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_hotime_hour_p .":0". $t_hotime_min_p ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_hotime_hour_p .":". $t_hotime_min_p ."</td>\n";
        }
        if ($t_hoover_min_p == 0) {                                     // �ٽлĶȽ���ɽ��
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_hoover_hour_p .":". $t_hoover_min_p ."0</td>\n";
        } else if ($t_hoover_min_p < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_hoover_hour_p .":0". $t_hoover_min_p ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_hoover_hour_p .":". $t_hoover_min_p ."</td>\n";
        }
        if ($t_homid_min_p == 0) {                                      // �ٽп��뽸��ɽ��
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_homid_hour_p .":". $t_homid_min_p ."0</td>\n";
        } else if ($t_homid_min_p < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_homid_hour_p .":0". $t_homid_min_p ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_homid_hour_p .":". $t_homid_min_p ."</td>\n";
        }
        if ($t_legal_min_p == 0) {                                      // ˡ����ֽ���ɽ��
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_legal_hour_p .":". $t_legal_min_p ."0</td>\n";
        } else if ($t_legal_min_p < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_legal_hour_p .":0". $t_legal_min_p ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_legal_hour_p .":". $t_legal_min_p ."</td>\n";
        }
        if ($t_leover_min_p == 0) {                                     // ˡ��ĶȽ���ɽ��
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_leover_hour_p .":". $t_leover_min_p ."0</td>\n";
        } else if ($t_leover_min_p < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_leover_hour_p .":0". $t_leover_min_p ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_leover_hour_p .":". $t_leover_min_p ."</td>\n";
        }
        if ($t_late_min_p == 0) {                                       // �ٹ�������ֽ���ɽ��
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_late_hour_p .":". $t_late_min_p ."0</td>\n";
        } else if ($t_late_min_p < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_late_hour_p .":0". $t_late_min_p ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_late_hour_p .":". $t_late_min_p ."</td>\n";
        }
        $listTable .= "        <td class='winbox'>��</td>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td class='winbox'>��</td>\n";
        $listTable .= "        <td class='winbox'>��</td>\n";
        $listTable .= "        <td class='winbox'>��</td>\n";
        $listTable .= "        <td class='winbox'>��</td>\n";
        $listTable .= "        <td class='winbox'>��</td>\n";
        $listTable .= "        <td class='winbox'>�硡��</td>\n";
        if ($t_fixed_min == 0) {                                      // ������ֽ���ɽ��
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_fixed_hour .":". $t_fixed_min ."0</td>\n";
        } else if ($t_fixed_min < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_fixed_hour .":0". $t_fixed_min ."</td>\n";
        } else {                                                             
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_fixed_hour .":". $t_fixed_min ."</td>\n";
        }
        if ($t_extend_min == 0) {                                     // ��Ĺ���ֽ���ɽ��
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_extend_hour .":". $t_extend_min ."0</td>\n";
        } else if ($t_extend_min < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_extend_hour .":0". $t_extend_min ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_extend_hour .":". $t_extend_min ."</td>\n";
        }
        if ($t_over_min == 0) {                                       // ��лĶȻ��ֽ���ɽ��
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_over_hour .":". $t_over_min ."0</td>\n";
        } else if ($t_over_min < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_over_hour .":0". $t_over_min ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_over_hour .":". $t_over_min ."</td>\n";
        }
        if ($t_mid_min == 0) {                                        // ����ĶȽ���ɽ��
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_mid_hour .":". $t_mid_min ."0</td>\n";
        } else if ($t_mid_min < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_mid_hour .":0". $t_mid_min ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_mid_hour .":". $t_mid_min ."</td>\n";
        }
        if ($t_hotime_min == 0) {                                     // �ٽл��ֽ���ɽ��
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_hotime_hour .":". $t_hotime_min ."0</td>\n";
        } else if ($t_hotime_min < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_hotime_hour .":0". $t_hotime_min ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_hotime_hour .":". $t_hotime_min ."</td>\n";
        }
        if ($t_hoover_min == 0) {                                     // �ٽлĶȽ���ɽ��
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_hoover_hour .":". $t_hoover_min ."0</td>\n";
        } else if ($t_hoover_min < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_hoover_hour .":0". $t_hoover_min ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_hoover_hour .":". $t_hoover_min ."</td>\n";
        }
        if ($t_homid_min == 0) {                                      // �ٽп��뽸��ɽ��
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_homid_hour .":". $t_homid_min ."0</td>\n";
        } else if ($t_homid_min < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_homid_hour .":0". $t_homid_min ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_homid_hour .":". $t_homid_min ."</td>\n";
        }
        if ($t_legal_min == 0) {                                      // ˡ����ֽ���ɽ��
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_legal_hour .":". $t_legal_min ."0</td>\n";
        } else if ($t_legal_min < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_legal_hour .":0". $t_legal_min ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_legal_hour .":". $t_legal_min ."</td>\n";
        }
        if ($t_leover_min == 0) {                                     // ˡ��ĶȽ���ɽ��
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_leover_hour .":". $t_leover_min ."0</td>\n";
        } else if ($t_leover_min < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_leover_hour .":0". $t_leover_min ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_leover_hour .":". $t_leover_min ."</td>\n";
        }
        if ($t_late_min == 0) {                                       // �ٹ�������ֽ���ɽ��
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_late_hour .":". $t_late_min ."0</td>\n";
        } else if ($t_late_min < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_late_hour .":0". $t_late_min ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_late_hour .":". $t_late_min ."</td>\n";
        }
        $listTable .= "        <td class='winbox'>��</td>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        return $listTable;
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
<title>���Ƚ���Ȳ�</title>
<script type='text/javascript' src='/base_class.js'></script>
<link rel='stylesheet' href='/menu_form.css' type='text/css' media='screen'>
<link rel='stylesheet' href='../working_hours_report.css' type='text/css' media='screen'>
<style type='text/css'>
<!--
body {
    background-image:none;
}
-->
</style>
<script type='text/javascript' src='../working_hours_report.js'></script>
</head>
<body>  <!--  -->
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
    
} // Class WorkingHoursReport_Model End

?>
