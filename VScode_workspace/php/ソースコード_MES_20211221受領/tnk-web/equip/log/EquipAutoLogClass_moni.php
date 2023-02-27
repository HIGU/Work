<?php
//////////////////////////////////////////////////////////////////////////////
// ������Ư������ ��ư���ǡ��������� Class                                //
// Copyright (C) 2005-2021 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/02/17 Created   EquipAutoLogClass.php                               //
//                               equip_auto_log2data_ftp.php ����ΰܹ�     //
// 2005/06/22 equip_mac_state_log ʪ������ȥ����꡼�����å����ȹ礻����  //
//            �ǡ�������¸�ѥơ��֥���ɲäˤ����å��ѹ�                //
//            equip_mac_state_log2�Ͻ���Ʊ�� ʪ���������¸����(���ȸߴ�) //
// 2005/10/07 Netmoni��FTP��ΰ���ե����뤬���󲿤餫�Υȥ�֥�Ǻ���Ǥ���//
//            �˻ĤäƤ����Զ��ο�ʿŸ����FTP���å�������temp����ɲ�   //
// 2006/03/03 �ؼ��ֹ椬�Ѥ�ä����ν�����߾����ѹ�(�μ¤�0�ˤ���SQLʸ)//
// 2007/06/26 ent_time��end_time���������equip_log_state, equip_log_workCnt//
//            methodʬ�� factoryWhere, macNoWhere ���С���ǥХå��Ѥ��ɲ�//
// 2007/06/27 $currentFullPathName���ѹ��������ʬ���ξ���FTP�ˤ��ɲá�   //
//          �嵭�Υ����Х��ѿ������,ftp_close()���˳���³���Ȥ˥���Ͽ//
//            �����󥿡��ޥ���������ߤ�����ޥ������μ����ɲ�              //
// 2007/06/28 checkStopTime()�᥽�åɤ��ɲä�����ߤ�ͭ����̵��������å�   //
//            getLogicalState()��getPhysicalState()�᥽�åɤǾ��ּ��������� //
// 2007/06/29 equip_index2�ΰ�����/:��ȴ���Ƥ����Τ�to_char()�ǽ�����     //
// 2007/06/30 set_factory()set_macNo()�������̤Υ�å����������ɲ�        //
// 2007/07/01 equip_ftpConnectRetry()�˼¹�����msg ftp_connect/ftp_login@ADD//
// 2007/07/02 /home/fws/{$mac_no}-bcd1/8��2�ս�ǥ��顼�ˤʤ롣@����log�ɲ� //
//            getRotaryStateRetry()�᥽�åɤ��ɲä����ͻҤ򸫤�             //
//            getFTP_Retry()�᥽�å�(���֤ȥ����󥿡�����)���ɲä��ͻҤ򸫤�//
// 2007/07/03 FTP�κ���³�����ϣ���ޤǥ�ȥ饤����˥��å��ɲ�           //
//            getRotaryStateHistory()���ɲä��ƶ���Ū�˼�ư��ž�ˤ��Ƥ����� //
//            �����꡼�����å��Ǽ�ư��̵�ͤ���Ƚ�ǡ�getRotaryState()�μ���//
//            �ϸ��ߤνꡢ�ǿ��ǡ����ΤߤʤΤǰ�Ĥε����ǰ��Τߤμ¹Ԥ�  //
// 2007/07/04 �����󥿡��ν���ߤ�getSQLworkCntInsert()��1�ܲ�����ʣ�����å�//
//   Ver 1.00 ���λ����ǥǥХå���λ���顼̵��                              //
// 2007/07/06 �����꡼�����å���������¸�������᥽�åɼ����ˤ��ʲ��ѹ�  //
//   Ver 1.10 getRotaryState()��getRotaryStateCurrent��getRotaryStateHistory//
// 2007/07/09 equip_log_state_rotaryHistory_write()���ԥ����å��������ѹ�   //
//   Ver 1.11 ���Ԥ� continue �� ������߸塢return ����                  //
//            �嵭�Ƕ��ԤϤʤ��Τ�equip_log_state_rotaryHistoryDebug()�ɲ�  //
// 2007/07/10 setRotaryStateCurrentBCD()�����꡼�����å��κǿ�BCD ��������//
//   Ver 1.12 �᥽�å��ɲ� getRotaryStateCurrentBCD()�������᥽�åɤ��ɲ� //
// 2007/07/11 2006/06/12��ʪ�����椬�����Ǥ��ȵ���ξ����ȵ���Ȥ��ѹ���//
//   Ver 1.13 ����Ƥ������� equip_state_check()���ɲ�                      //
// 2007/10/05 �̿����顼�Ȼפ���㳲�ǥ쥳���ɤ�̵�����֤ˤʤä�����      //
//   Ver 1.14 if (isset($preState)) �Υ����å����ɲ�                        //
// 2018/01/18 FWS1�λ������ե������¤���ѹ��ʿ�����FWS�Υƥ���        ��ë //
// 2018/11/15 FWS2�����ؽ�����FWS2�Ǹ�����PGM����                      ��ë //
// 2018/11/19 FWS2�����ش�λ��FWS1��2�ǥե�����������㤦�Τ����      ��ë //
// 2018/12/25 Insert���˥��顼��ȯ������١��ƥ���                     ��ë //
//            Ʊ�����֤˰㤦state��ȯ�������顼�ʰ١��б�                   //
// 2020/03/13 $ftp_no�Ǹ�������kv�Ѥ��ɲá��ե�����������ǧ          ��ë //
// 2021/08/02 �ƥ��Ȥǲù�������ˤ��Ƥߤ롣                           ��ë //
// 2021/08/23 �ù�������ˤ����Τ򸵤��ᤷ����                         ��ë //
// 2021/08/24 �ǡ����ʤ��λ����ǿ�����ǤϤʤ�������λ��֤����     ��ë //
// 2021/08/26 equip_work_log2�ǥ�������ʣ����ǡ����ν񤭹��ߥ��顼��       //
//            �������ʤ��褦�˽���                                     ��ë //
// 2021/10/12 �����ߥǡ�����ƥ���Ū�ˤ��٤��ݴɤ���褦�ѹ�         ��ë //
//            �ƥǡ����򹩾����ʬ�����ݴɤ���褦�ѹ�(7SUS��6�Τ�)    ��ë //
// 2021/10/27 cnt�񤭹��߻�cnt�����ξ��Ͽ�����ȴ���Ф�������ȿ��Ȥ���褦//
//            �ѹ�                                                     ��ë //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', '0');             // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI��
//$currentFullPathName = realpath(dirname(__FILE__));
require_once (realpath(dirname(__FILE__)) . "/../equip_function.php");

if (class_exists('EquipAutoLog')) {
    return;
}
define('EAL_VERSION', '1.14');

/****************************************************************************
*                       base class ���쥯�饹�����                         *
****************************************************************************/
///// namespace Common {} �ϸ��߻��Ѥ��ʤ� �����㡧Common::ComTableMnt �� $obj = new Common::ComTableMnt;
///// Common::$sum_page, Common::set_page_rec() (̾������::��ƥ��)
class EquipAutoLog
{
    ///// Private properties
    private $log_name;                  // ���Υ��饹���̤Υ��ե�����̾
    private $str_time;                  // ��ư�������γ��ϻ���(000000)��ʬ�� ����
    private $end_time;                  // ��ư�������ν�λ����(235959)��ʬ�� �ޤ�
    private $interface = array();       // (����)���󥿡��ե������ֹ� csv_flg����
                                        // 1=netmoni4, 2=fws1, 3=fws2, 4=fws3, ... 101=netmoni4+fws1(ʣ��)
    private $ftp_ip = array();          // (����)���󥿡��ե�������IP���ɥ쥹
    private $ftp_host = array();        // (����)���󥿡��ե������Υۥ���̾ �ƻ�����(��̾)
    private $ftp_user = array();        // (����)FTP�桼����̾
    private $ftp_pass = array();        // (����)FTP�ѥ����
    private $ftp_stream = array();      // (����)FTP���ȥ꡼��
    // debug�Ѥ˸��ꤹ����
    private $factoryWhere = '';         // �����ʬ�Ǹ��ꤹ����
    private $macNoWhere = '';           // �����ֹ�Ǹ��ꤹ����
    private $mac_no = '';               // �����Ȥε����ֹ�
    private $ftp_no = '';               // ��³���륤�󥿡��ե�������ftp_no
    private $rotaryState = '';          // �����ȵ����Υ����꡼�����å��ξ���
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ///// Constructer ����� (php5�ذܹԻ��� __construct() ���ѹ�ͽ��)
    public function __construct($str_time = '00:55:00', $end_time = '23:59:00', $log_name = '/tmp/EquipAutoLogClassMoni.log')
    {
        ///// ���ꤵ�줿���ե�����̾�Υ����å� & ��Ͽ
        $this->equip_log_openChk($log_name);
        ///// �ѥ�᡼���λ��֥����å��ȥץ�ѥƥ��ؤ���Ͽ
        if ($this->equip_chkErrorTime($str_time, $end_time) == FALSE) {
            $msg = "���ϻ��֡�{$str_time} ��λ���֡�{$end_time} ��Error�Ǥ�����λ���ޤ���";
            $this->equip_log_writer($msg);      // ���饹�ⶦ��loger�ν����
            $this->equip_exit();                // ���饹�ⶦ�ѽ�λ�᥽�å�
        } else {
            $msg = "���ϻ��֡�{$str_time} ��λ���֡�{$end_time} ������Ǽ�ư��ư�������ν���������ޤ�����";
            $this->equip_log_writer($msg);
        }
    }
    ///// ���debug�Ѥι����ʬ������ ����᥽�å�
    public function set_factory($factory='')
    {
        if ($factory == '') {
            $this->factoryWhere = '';
            $this->equip_log_writer("�����ʬ�� ���� �����ꤷ�ޤ�����");
            return true;
        } else if ($this->checkGroupMaster($factory, $group_name)) {
            $this->factoryWhere = "AND factory = '{$factory}'";
            $this->equip_log_writer("�����ʬ�� {$group_name} �����ꤷ�ޤ�����");
            return true;
        } else {
            $this->equip_log_writer("�����ʬ {$factory} �ϥޥ������ˤʤ���̵���Ǥ���");
            return false;
        }
    }
    ///// ���debug�Ѥε����ֹ������ ����᥽�å�
    public function set_macNo($mac_no='')
    {
        if ($mac_no == '') {
            $this->macNoWhere = '';
            $this->equip_log_writer("�����ֹ�� ���� �����ꤷ�ޤ�����");
            return true;
        } else if ($this->checkMachineMaster($mac_no, $mac_name)) {
            $this->macNoWhere = "AND mac_no = {$mac_no}";
            $this->equip_log_writer("�����ֹ�� {$mac_no} {$mac_name} �����ꤷ�ޤ�����");
            return true;
        } else {
            $this->equip_log_writer("�����ֹ� {$mac_no} �ϥޥ������ˤʤ���̵���Ǥ���");
            return false;
        }
    }
    ////////// �������μ¹�(����������)
    public function equip_logExec_once()
    {
        ///// ��������⤫�Υ����å�
        if (date('His') < $this->str_time) {
            $this->equip_log_writer('������ֳ��Ǥ��ΤǼ¹Ԥ���ߤ��ޤ���');
            return FALSE;
        }
        if (date('His') > $this->end_time) {
            $this->equip_log_writer('������ֳ��Ǥ��ΤǼ¹Ԥ���ߤ��ޤ���');
            return FALSE;
        }
        ///// �����󥿡��ե�������FTP���ͥ�������Ω
        $this->equip_all_ftpConnect();
        ///// �оݥ��󥿡��ե�������FTP ���ȥ꡼��Υ����å� & ������
        $num = count($this->interface);
        for ($i=0; $i<$num; $i++) {
            if (!($this->ftp_stream[$i])) {
                ///// ����³�ν���
                $this->equip_ftpConnectRetry($i);
                if (!($this->ftp_stream[$i])) $this->equip_ftpConnectRetry($i); // ����ޤǥ�ȥ饤����
            } else {
                ///// ���ߤΥ��ȥ꡼�बͭ���������å�
                /*
                if (!ftp_systype($this->ftp_stream[$i])) {
                    $this->equip_log_writer("{$this->ftp_host[$i]}����FTP��³�����Ǥ���ޤ���������³���ޤ���");
                    ///// ����³�ν���
                    $this->equip_ftpConnectRetry($i);
                }
                */
            }
            ///// ������ ����
            if ($this->ftp_stream[$i]) {
                // �ƥ��Ȥǲù�������ˤ��Ƥߤ�
                $this->equip_log_workCnt($this->interface[$i], $this->ftp_stream[$i]);
                ///// ������ �����ξ��� ����
                $this->equip_log_state($this->interface[$i], $this->ftp_stream[$i]);
            }
        }
        ///// FTP���ͥ������򥯥���
        $this->equip_ftp_close();
        return TRUE;
    }
    ////////// �������μ¹�(����������)
    public function equip_logExec_once_moni()
    {
        ///// ��������⤫�Υ����å�
        if (date('His') < $this->str_time) {
            $this->equip_log_writer('������ֳ��Ǥ��ΤǼ¹Ԥ���ߤ��ޤ���');
            return FALSE;
        }
        if (date('His') > $this->end_time) {
            $this->equip_log_writer('������ֳ��Ǥ��ΤǼ¹Ԥ���ߤ��ޤ���');
            return FALSE;
        }
        ///// �����󥿡��ե�������FTP���ͥ�������Ω
        $this->equip_all_ftpConnect();
        ///// �оݥ��󥿡��ե�������FTP ���ȥ꡼��Υ����å� & ������
        $num = count($this->interface);
        for ($i=0; $i<$num; $i++) {
            if (!($this->ftp_stream[$i])) {
                ///// ����³�ν���
                $this->equip_ftpConnectRetry($i);
                if (!($this->ftp_stream[$i])) $this->equip_ftpConnectRetry($i); // ����ޤǥ�ȥ饤����
            } else {
                ///// ���ߤΥ��ȥ꡼�बͭ���������å�
                /*
                if (!ftp_systype($this->ftp_stream[$i])) {
                    $this->equip_log_writer("{$this->ftp_host[$i]}����FTP��³�����Ǥ���ޤ���������³���ޤ���");
                    ///// ����³�ν���
                    $this->equip_ftpConnectRetry($i);
                }
                */
            }
            ///// ������ ����
            if ($this->ftp_stream[$i]) {
                // �ƥ��Ȥǲù�������ˤ��Ƥߤ�
                $this->equip_log_workCnt_moni($this->interface[$i], $this->ftp_stream[$i]);
                ///// ������ �����ξ��� ����
                $this->equip_log_state($this->interface[$i], $this->ftp_stream[$i]);
            }
        }
        ///// FTP���ͥ������򥯥���
        $this->equip_ftp_close();
        return TRUE;
    }
    ///// ���饹�ⶦ�ѥ�����ߥ᥽�å�
    public function equip_log_writer($msg)
    {
        $msg = date('Y-m-d H:i:s ') . "{$msg}\n";
        if ( ($fp_log = fopen($this->log_name, 'a')) ) {
            fwrite($fp_log, $msg);
        } else {
            ///// ���٤����ƻ�Ԥ���
            sleep(3);
            if ( ($fp_log = fopen($this->log_name, 'a')) ) {
                fwrite($fp_log, $msg);
            }
        }
        fclose($fp_log);
        return;
    }
    ///// ���饹���� ��λ�᥽�å�
    public function equip_exit()
    {
        ///// FTP ���ͥ������ν�λ����
        $this->equip_ftp_close();
        $this->factoryWhere = '';
        $this->macNoWhere   = '';  
        $this->ftp_no       = '';  
        $this->equip_log_writer('��ư��������λ���ޤ���');
        ///// ���󥹥��󥹲�����������ץ�¦�ǽ�λ�������뤿��ʲ��ϥ����ȥ�����
        // exit();
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    /*************************** Set & Check methods ************************/
    ///// ���ϤȽ�λ�λ��֥����å��ȥץ�ѥƥ��ؤ���Ͽ
    protected function equip_chkErrorTime($str_time, $end_time)
    {
        ////////// ���֥ե����ޥåȤ�������Ѵ�(:)������(�ʤ���в��⤷�ʤ�)
        $str_time = str_replace(':', '', $str_time);
        $end_time = str_replace(':', '', $end_time);
        ////////// ���֤����ͤǤ��뤫�����å�
        if (!is_numeric($str_time)) return FALSE;
        if (!is_numeric($end_time)) return FALSE;
        ////////// ���֤�����Ǥ��뤫�����å�
        if (strlen($str_time) != 6) return FALSE;
        if (strlen($end_time) != 6) return FALSE;
        ////////// ���֤����ʬ���ä�ʬ����
        $str_hour   = substr($str_time, 0, 2);
        $str_minute = substr($str_time, 2, 2);
        $str_second = substr($str_time, 4, 2);
        $end_hour   = substr($end_time, 0, 2);
        $end_minute = substr($end_time, 2, 2);
        $end_second = substr($end_time, 4, 2);
        ////////// ����ʬ���ä�Ŭ�������å�
        if ($str_hour   < 0 || $str_hour   > 23) return FALSE;  // 23:59:59�μ���00:00:00�Ȼ��ꤹ��
        if ($str_minute < 0 || $str_minute > 59) return FALSE;
        if ($str_second < 0 || $str_second > 59) return FALSE;
        if ($end_hour   < 0 || $end_hour   > 23) return FALSE;
        if ($end_minute < 0 || $end_minute > 59) return FALSE;
        if ($end_second < 0 || $end_second > 59) return FALSE;
        ////////// ���֤Υ����å�
        $str_timestamp = mktime($str_hour, $str_minute, $str_second, date('m'), date('d'), date('Y'));
        $end_timestamp = mktime($end_hour, $end_minute, $end_second, date('m'), date('d'), date('Y'));
        if ( ($end_timestamp - $str_timestamp) < 300 && ($end_timestamp - $str_timestamp) > 0 ) {
            $msg = "���ϻ��֡�{$str_time} �� ��λ���֡�{$end_time} �δֳ֤Ϻ��㣵ʬ�ʾ�����ꤷ�Ʋ�������";
            $this->equip_log_writer($msg);      // ���饹�ⶦ��loger�ν����
            $this->equip_exit();                // ���饹�ⶦ�ѽ�λ�᥽�å�
        }                           // �嵭���ޥ��ʥ��ξ��ϼ�������end_time�ǽ�λ�Ȥʤ�
        ////////// �ץ�ѥƥ�����Ͽ
        $this->str_time = $str_time;
        $this->end_time = $end_time;
        ////////// ���괰λ ���
        return TRUE;
    }
    ///// �����ʬ(���롼��)�ޥ������Υ����å�
    protected function checkGroupMaster($factory, &$name)
    {
        $query = "
            SELECT group_name FROM equip_group_master WHERE group_no = {$factory}
        ";
        if (getUniResult($query, $name) > 0) {
            return true;
        } else {
            $name = '';
            return false;
        }
    }
    ///// �����ޥ������Υ����å�
    protected function checkMachineMaster($mac_no, &$name)
    {
        $query = "
            SELECT mac_name FROM equip_machine_master2 WHERE mac_no = {$mac_no}
        ";
        if (getUniResult($query, $name) > 0) {
            return true;
        } else {
            $name = '';
            return false;
        }
    }
    
    /*************************** Procedure Type methods ************************/
    ///// �ƥ��󥿡��ե�������FTP�Υ��ͥ��������������᥽�å�
    protected function equip_all_ftpConnect()
    {
        ///// �ƥ��󥿡��ե�������FTP��³�Ѥξ�������
        $res = array();
        if ( ($res = $this->equip_ftpInfo()) ) {
            ///// �ƥ��󥿡��ե�������FTP�Υ��ͥ�������������󤹤�
            $i = 0; $this->ftp_stream = array();
            foreach ($res as $r) {
                $interface = $r[0];
                $ftp_host  = $r[1];
                $ftp_ip    = $r[2];
                $ftp_user  = $r[3];
                $ftp_pass  = $r[4];
                $this->ftp_stream[$i] = $this->equip_ftpConnect($ftp_host, $ftp_ip, $ftp_user, $ftp_pass);
                $i++;
            }
        } else {
            $this->equip_log_writer('�оݥ��󥿡��ե�����������ޤ���');  // ���饹�ⶦ��loger�ν����
        }
        return;
    }
    ///// �ƥ��󥿡��ե�������FTP��³�Ѥξ��������������Ǥ�����硢�����������֤�
    protected function equip_ftpInfo()
    {
        ///// �ץ�ѥƥ��ν����
        $this->interface = array();
        $this->ftp_host  = array();
        $this->ftp_ip    = array();
        $this->ftp_user  = array();
        $this->ftp_pass  = array();
        ///// �ơ��֥������
        $query = "
            SELECT
                interface, host, ip_address, ftp_user, ftp_pass
            FROM
                equip_interface_master
            LEFT OUTER JOIN
                equip_machine_master2 ON (interface = csv_flg)
            WHERE
                ftp_active IS TRUE {$this->factoryWhere} {$this->macNoWhere}
            GROUP BY
                interface, host, ip_address, ftp_user, ftp_pass
            ORDER BY
                interface ASC
        ";
        $res = array();
        if ( ($rows=getResult2($query, $res)) < 0) {
            $this->equip_log_writer('DB���饤�󥿡��ե������ξ�������˼��Ԥ��ޤ�����');
            $this->equip_exit();
        } else {
            if ($rows == 0) return FALSE;
        }
        for ($r=0; $r<$rows; $r++) {
            $this->interface[$r] = $res[$r][0];
            $this->ftp_host[$r]  = $res[$r][1];
            $this->ftp_ip[$r]    = $res[$r][2];
            $this->ftp_user[$r]  = $res[$r][3];
            $this->ftp_pass[$r]  = $res[$r][4];
        }
        return $res;
    }
    ///// �ƥ��󥿡��ե�������FTP��³  FTP���ȥ꡼����֤�(FALSE�ξ�����³���Ƥ��ʤ�)
    protected function equip_ftpConnect($ftp_host = '', $ftp_ip = '', $ftp_user = '', $ftp_pass = '')
    {
        ////////// FTP CONNECT
        if ( !($ftp_stream = @ftp_connect($ftp_ip)) ) {
            $this->equip_log_writer("{$ftp_host}��FTP����³�˼��Ԥ��ޤ�����");
            return FALSE;
        } else {
            $this->equip_log_writer("{$ftp_host}��FTP����³���ޤ�����");
            ////////// FTP LOGIN
            if (!@ftp_login($ftp_stream, $ftp_user, $ftp_pass)) {
                $this->equip_log_writer("{$ftp_host}��FTP��login�˼��Ԥ��ޤ�����");
                ftp_close($ftp_stream);     // ���ν��������Ф�ɬ�� (�����)
                return FALSE;
            }
            $this->equip_log_writer("{$ftp_host}��FTP��login���ޤ�����");
        }
        return $ftp_stream;
    }
    ///// �ƥ��󥿡��ե�������FTP��³ �ƻ�� ���� (����������Υ���ǥå���)
    protected function equip_ftpConnectRetry($i)
    {
        // $this->equip_log_writer("{$this->ftp_host[$i]}��FTP����³�ڤ�login��ƻ�Ԥ��ޤ���");
        $this->ftp_stream[$i] = $this->equip_ftpConnect($this->ftp_host[$i], $this->ftp_ip[$i], $this->ftp_user[$i], $this->ftp_pass[$i]);
        if (!($this->ftp_stream[$i])) {
            $this->equip_log_writer("{$this->ftp_host[$i]}��FTP����³�ڤ�login�κƻ�ԤǼ��Ԥ��ޤ�����");
        } else {
            $this->equip_log_writer("{$this->ftp_host[$i]}��FTP����³�ڤ�login�κƻ�Ԥ��������ޤ�����");
        }
    }
    
    /******************************* Out methods ****************************/
    ///// ���ե����륪���ץ�Υ����å� & ��Ͽ methods
    protected function equip_log_openChk($log_name)
    {
        if ( !($fp_log = fopen($log_name, 'a')) ) {
            echo "���ե����롧{$log_name} �򥪡��ץ�Ǥ��ޤ���\n";
            exit;   // ������λ
        } else {
            fclose($fp_log);
            $this->log_name = $log_name;
        }
        return;
    }
    ///// FTP�Υ��ͥ������ ��λ�᥽�å�
    protected function equip_ftp_close()
    {
        foreach ($this->ftp_stream as $key => $ftp_stream) {
            if ($ftp_stream) {
                if (ftp_close($ftp_stream)) {
                    $this->equip_log_writer("{$this->ftp_host[$key]}��FTP�����Ǥ��ޤ�����");
                } else {
                    $this->equip_log_writer("{$this->ftp_host[$key]}��FTP�����Ǥ˼��Ԥ��ޤ�����");
                }
            } else {
                $this->equip_log_writer("{$this->ftp_host[$key]}��FTP����³����Ƥ��ޤ���");
            }
        }
        $this->ftp_stream = array();    // ��������ƽ�λ
        $this->ftp_ip     = array();
        $this->ftp_host   = array();
        $this->ftp_user   = array();
        $this->ftp_pass   = array();
        $this->interface  = array();
        return;
    }
    
    ///// ������ �����ξ��� �����᥽�å�
    protected function equip_log_state($interface, $ftp_con)
    {
        ///// ��ư��˴ط��ʤ� �����ޥ������ƻ뤹�뵡���ֹ����� (ʪ�����֤�24���ִƻ뤹�뤿��)
        $query = "
            SELECT
                mac_no, csv_flg
            FROM
                equip_machine_master2
            WHERE
                csv_flg = {$interface}
                AND
                survey = 'Y'
                {$this->factoryWhere} {$this->macNoWhere}
        ";
            // $interface == Netmoni=1 fwserver1=2 fwserver2=3 fwserver3=4 fwserver4=5 ... , Net&fws1=101
        $res_key = array();
        if ( ($rows_key = getResult($query, $res_key)) < 1) {
            return;
        }
        for ($i=0; $i<$rows_key; $i++) {                        // ���쥳���ɤ��Ľ���
            ///// insert �� �ѿ� �����
            $mac_no   = $res_key[$i]['mac_no'];
            $csv_flg  = $res_key[$i]['csv_flg'];
            $this->equip_log_state_rotary($mac_no, $csv_flg, $ftp_con);
            $this->equip_log_state_body($mac_no, $csv_flg, $ftp_con);
        }
    }
    ///// ������ �����꡼�����å��ξ��� ���� �᥽�å�
    protected function equip_log_state_rotary($mac_no, $csv_flg, $ftp_con)
    {
        $query = "
            SELECT
                interface
            FROM
                equip_machine_interface
            WHERE
                mac_no = {$mac_no}
        ";
        $ftp_no = 0;
        $this->ftp_no = 0;
        getUniResult($query, $ftp_no);
        $this->ftp_no = $ftp_no;
        /*
        if ($ftp_no == 2) {
            $fws_rotary_log = "/MMC/Plc_Work/{$mac_no}_bcd_state.log";
            $fws_rotary_tmp = "/MMC/Plc_Work/{$mac_no}_bcd_state.tmp";
            $local_rotary = "/home/fws/{$mac_no}_bcd_state.log";
        } else {
            $fws_rotary_log = "/home/fws/usr/{$mac_no}_bcd_state.log";
            $fws_rotary_tmp = "/home/fws/usr/{$mac_no}_bcd_state.tmp";
            $local_rotary = "/home/fws/{$mac_no}_bcd_state.log";
        }
        */
        // FWS2�����ء���򤳤���������ؤ���
        if ($ftp_no == 2) {
            $fws_rotary_log = "/MMC/Plc_Work/{$mac_no}_bcd_state.log";
            $fws_rotary_tmp = "/MMC/Plc_Work/{$mac_no}_bcd_state.tmp";
            $local_rotary = "/home/fws/{$mac_no}_bcd_state.log";
        } elseif ($ftp_no == 3) {
            $fws_rotary_log = "/0_CARD/Plc_Work/{$mac_no}_bcd_state.log";
            $fws_rotary_tmp = "/0_CARD/Plc_Work/{$mac_no}_bcd_state.tmp";
            $local_rotary = "/home/fws/{$mac_no}_bcd_state.log";
        } elseif ($ftp_no == 4) {
            $fws_rotary_log = "/0_CARD/Plc_Work/{$mac_no}_bcd_state.log";
            $fws_rotary_tmp = "/0_CARD/Plc_Work/{$mac_no}_bcd_state.tmp";
            $local_rotary = "/home/fws/{$mac_no}_bcd_state.log";
        } elseif ($ftp_no == 7 || $ftp_no == 8) {
            $fws_rotary_log = "/0_CARD/Plc_Work/{$mac_no}_bcd_state.log";
            $fws_rotary_tmp = "/0_CARD/Plc_Work/{$mac_no}_bcd_state.tmp";
            $local_rotary = "/home/fws/{$mac_no}_bcd_state.log";
        } else {
            $fws_rotary_log = "/home/fws/usr/{$mac_no}_bcd_state.log";
            $fws_rotary_tmp = "/home/fws/usr/{$mac_no}_bcd_state.tmp";
            $local_rotary = "/home/fws/{$mac_no}_bcd_state.log";
        }
        ///// ���� FTP Download ����
        $this->equip_FTP_Download($fws_rotary_log, $fws_rotary_tmp, $local_rotary, $ftp_con, $mac_no);
        ///// Rotary Log File Check        �����꡼�����å�������
        // �ƥ��� �ƥ��Ȳ������else¦�Τ߻Ĥ�
        if ($mac_no=='1346' || $mac_no=='1347' || $mac_no=='1348' || $mac_no=='1349' || $mac_no=='1364' || $mac_no=='1365' || $mac_no=='1366' || $mac_no=='1367' || $mac_no=='1368' || $mac_no=='1369' || $mac_no=='1372' || $mac_no=='1373' || $mac_no=='1374') {
            $timestamp = time();
            $rotary_temp = "/home/fws/��7���쿿�/{$mac_no}_bcd_state" . $timestamp . ".tmp" ;     // Rename �ѥǥ��쥯�ȥꡦ�ե�����̾����
        } elseif ($mac_no=='1224' || $mac_no=='1228' || $mac_no=='1258' || $mac_no=='1225' || $mac_no=='1226' || $mac_no=='1227' || $mac_no=='1228' || $mac_no=='1229' || $mac_no=='1230' || $mac_no=='1233' || $mac_no=='1234' || $mac_no=='1235' || $mac_no=='1257' || $mac_no=='1259') {
            $timestamp = time();
            $rotary_temp = "/home/fws/��7����SUS/{$mac_no}_bcd_state" . $timestamp . ".tmp" ;     // Rename �ѥǥ��쥯�ȥꡦ�ե�����̾����
        } elseif ($mac_no=='2101' || $mac_no=='2103' || $mac_no=='2106' || $mac_no=='2901' || $mac_no=='2902' || $mac_no=='2903' || $mac_no=='6000' || $mac_no=='6001' || $mac_no=='6002' || $mac_no=='6003' || $mac_no=='6004' || $mac_no=='6005') {
            $timestamp = time();
            $rotary_temp = "/home/fws/��6����/{$mac_no}_bcd_state" . $timestamp . ".tmp" ;     // Rename �ѥǥ��쥯�ȥꡦ�ե�����̾����
        } else {
            $rotary_temp = "/home/fws/{$mac_no}_bcd_state.tmp";     // Rename �ѥե�����̾����
        }
        
        if (file_exists($local_rotary)) {                       // Rotary Log File �������
            if (rename($local_rotary, $rotary_temp)) {
                $this->equip_log_state_rotaryHistory_write($mac_no, $csv_flg, $rotary_temp);
            } else {
                $msg = "�����꡼�����å��� rename({$local_rotary}) �˼���";
                $this->equip_log_writer($msg);
            }
        } else {
            ///// �����꡼�����å�������ǡ�����̵�����Ϻǿ��ǡ����Τ߼���
            $this->equip_log_state_rotaryCurrent_write($mac_no, $csv_flg, $ftp_con);
        }
        ///// State Log ������λ
    }
    ///// ������ ���� FTP Download ���� �᥽�å�
    protected function equip_FTP_Download($fws_log, $fws_tmp, $local_log, $ftp_con, $mac_no)
    {
        $query = "
            SELECT
                interface
            FROM
                equip_machine_interface
            WHERE
                mac_no = {$mac_no}
        ";
        $ftp_no = 0;
        getUniResult($query, $ftp_no);
        if ($ftp_no == 2) {
            // �ǡ��������ե������local��fws¦������
            $stop_local = "/home/fws/write_protect.mes";
            $stop_fws = "/MMC/Plc_Work/write_protect.mes";
            //chmod($stop_local, 0666);
            $fp = fopen($stop_local, 'a');
            flock($fp, LOCK_EX);
            ftruncate($fp,0);
            flock($fp, LOCK_UN);
            $date_time = date('H:i:s');
            fwrite($fp, "{$date_time}\n");
            fclose($fp);
            //chmod($stop_local, 0666);
            // ��FWS�������
            // ftp_size�������ʤ��Τǥե�����¸�ߤΥ����å�������ʤ�
            // ���Τ��ᥨ�顼���Ǥ��Ф�³���Ƥ��ޤ�
            
            // ���ץ����
            // Stop�ե������ž�����ǡ����������ߤޤä���˥��ԡ����ƺ��
            
            if (@ftp_put($ftp_con, $stop_fws, $stop_local, FTP_ASCII) == false) {
                // stop�ե�����ž�����顼�ΰ١����⤷�ʤ�
            } else {
                // 2�åǥ��쥤 3�ä�1��ե�������ǧ�����
                sleep(2);
                if (@ftp_get($ftp_con, $local_log, $fws_log, FTP_ASCII) == false) {
                    // �ե����뤬̵���ä���ǽ���⤢��
                    //$this->equip_log_writer("{$fws_tmp}��FTP��Download�˼��Ԥ��ޤ�����");
                    $this->getFTP_Retry($ftp_con, $local_log, $fws_log, $mac_no);
                } else if (@ftp_delete($ftp_con, $fws_log) == false) {
                    // ��ե�����κ�����Լ��Ի����ä˲��⤷�Ƥʤ��Τ�
                    // ����ʤ�����
                } else {
                    // FTP Download OK
                }
                ftp_delete($ftp_con, $stop_fws);    // ���ȥåץե�����Ϻ��
            }
            
            // FTP��ΰ���ե�����κ��
            //if (file_exists($fws_tmp) == true) {
            /* ���ץ����
            if (ftp_size($ftp_con, $fws_tmp) != -1) {
               //$this->equip_log_writer("�ƥ���1 {$fws_log}");
               ftp_delete($ftp_con, $fws_tmp);
            }
            /////////// FTP��Υե������¸�ߥ����å�
            //if (file_exists($fws_log) == false) {
            if (ftp_size($ftp_con, $fws_log) == -1) {
                //$this->equip_log_writer("�ƥ���2 {$fws_log}");
                // �ե����뤬̵���Τǲ��⤷�ʤ�
            } else if (ftp_rename($ftp_con, $fws_log, $fws_tmp) == false) {
                $this->equip_log_writer("FTP rename() ���� {$fws_log}");
                if (file_exists($local_log)) unlink($local_log);
            } else if (@ftp_get($ftp_con, $local_log, $fws_tmp, FTP_ASCII) == false) {
                $this->equip_log_writer("{$fws_tmp}��FTP��Download�˼��Ԥ��ޤ�����");
                $this->getFTP_Retry($ftp_con, $local_log, $fws_tmp, $mac_no);
            } else {
                // FTP Download OK
                ftp_delete($ftp_con, $fws_tmp);  // ��ե�����Ϻ��
            }
            */
        // FWS2������ �����Ȳ��
        } elseif ($ftp_no == 3) {
            // �ǡ��������ե������local��fws¦������
            $stop_local = "/home/fws/write_protect.mes";
            $stop_fws = "/0_CARD/Plc_Work/write_protect.mes";
            //chmod($stop_local, 0666);
            $fp = fopen($stop_local, 'a');
            flock($fp, LOCK_EX);
            ftruncate($fp,0);
            flock($fp, LOCK_UN);
            $date_time = date('H:i:s');
            fwrite($fp, "{$date_time}\n");
            fclose($fp);
            //chmod($stop_local, 0666);
            // ��FWS�������
            // ftp_size�������ʤ��Τǥե�����¸�ߤΥ����å�������ʤ�
            // ���Τ��ᥨ�顼���Ǥ��Ф�³���Ƥ��ޤ�
            
            // ���ץ����
            // Stop�ե������ž�����ǡ����������ߤޤä���˥��ԡ����ƺ��
            
            if (@ftp_put($ftp_con, $stop_fws, $stop_local, FTP_ASCII) == false) {
                // stop�ե�����ž�����顼�ΰ١����⤷�ʤ�
            } else {
                // 2�åǥ��쥤 3�ä�1��ե�������ǧ�����
                sleep(2);
                if (@ftp_get($ftp_con, $local_log, $fws_log, FTP_ASCII) == false) {
                    // �ե����뤬̵���ä���ǽ���⤢��
                    //$this->equip_log_writer("{$fws_tmp}��FTP��Download�˼��Ԥ��ޤ�����");
                    $this->getFTP_Retry($ftp_con, $local_log, $fws_log, $mac_no);
                } else if (@ftp_delete($ftp_con, $fws_log) == false) {
                    // ��ե�����κ�����Լ��Ի����ä˲��⤷�Ƥʤ��Τ�
                    // ����ʤ�����
                } else {
                    // FTP Download OK
                }
                ftp_delete($ftp_con, $stop_fws);    // ���ȥåץե�����Ϻ��
            }
        // FWS3������ �����Ȳ��
        } elseif ($ftp_no == 4) {
            // �ǡ��������ե������local��fws¦������
            $stop_local = "/home/fws/write_protect.mes";
            $stop_fws = "/0_CARD/Plc_Work/write_protect.mes";
            //chmod($stop_local, 0666);
            $fp = fopen($stop_local, 'a');
            flock($fp, LOCK_EX);
            ftruncate($fp,0);
            flock($fp, LOCK_UN);
            $date_time = date('H:i:s');
            fwrite($fp, "{$date_time}\n");
            fclose($fp);
            //chmod($stop_local, 0666);
            // ��FWS�������
            // ftp_size�������ʤ��Τǥե�����¸�ߤΥ����å�������ʤ�
            // ���Τ��ᥨ�顼���Ǥ��Ф�³���Ƥ��ޤ�
            
            // ���ץ����
            // Stop�ե������ž�����ǡ����������ߤޤä���˥��ԡ����ƺ��
            
            if (@ftp_put($ftp_con, $stop_fws, $stop_local, FTP_ASCII) == false) {
                // stop�ե�����ž�����顼�ΰ١����⤷�ʤ�
            } else {
                // 2�åǥ��쥤 3�ä�1��ե�������ǧ�����
                sleep(2);
                if (@ftp_get($ftp_con, $local_log, $fws_log, FTP_ASCII) == false) {
                    // �ե����뤬̵���ä���ǽ���⤢��
                    //$this->equip_log_writer("{$fws_tmp}��FTP��Download�˼��Ԥ��ޤ�����");
                    $this->getFTP_Retry($ftp_con, $local_log, $fws_log, $mac_no);
                } else if (@ftp_delete($ftp_con, $fws_log) == false) {
                    // ��ե�����κ�����Լ��Ի����ä˲��⤷�Ƥʤ��Τ�
                    // ����ʤ�����
                } else {
                    // FTP Download OK
                }
                ftp_delete($ftp_con, $stop_fws);    // ���ȥåץե�����Ϻ��
            }
        } elseif ($ftp_no ==7 || $ftp_no == 8) {
            // �ǡ��������ե������local��fws¦������
            $stop_local = "/home/fws/write_protect.mes";
            $stop_fws = "/0_CARD/Plc_Work/write_protect.mes";
            //chmod($stop_local, 0666);
            $fp = fopen($stop_local, 'a');
            flock($fp, LOCK_EX);
            ftruncate($fp,0);
            flock($fp, LOCK_UN);
            $date_time = date('H:i:s');
            fwrite($fp, "{$date_time}\n");
            fclose($fp);
            //chmod($stop_local, 0666);
            // ��FWS�������
            // ftp_size�������ʤ��Τǥե�����¸�ߤΥ����å�������ʤ�
            // ���Τ��ᥨ�顼���Ǥ��Ф�³���Ƥ��ޤ�
            
            // ���ץ����
            // Stop�ե������ž�����ǡ����������ߤޤä���˥��ԡ����ƺ��
            
            if (@ftp_put($ftp_con, $stop_fws, $stop_local, FTP_ASCII) == false) {
                // stop�ե�����ž�����顼�ΰ١����⤷�ʤ�
            } else {
                // 2�åǥ��쥤 3�ä�1��ե�������ǧ�����
                sleep(2);
                if (@ftp_get($ftp_con, $local_log, $fws_log, FTP_ASCII) == false) {
                    // �ե����뤬̵���ä���ǽ���⤢��
                    //$this->equip_log_writer("{$fws_tmp}��FTP��Download�˼��Ԥ��ޤ�����");
                    $this->getFTP_Retry($ftp_con, $local_log, $fws_log, $mac_no);
                } else if (@ftp_delete($ftp_con, $fws_log) == false) {
                    // ��ե�����κ�����Լ��Ի����ä˲��⤷�Ƥʤ��Τ�
                    // ����ʤ�����
                } else {
                    // FTP Download OK
                }
                ftp_delete($ftp_con, $stop_fws);    // ���ȥåץե�����Ϻ��
            }
        } else {
            // FTP��ΰ���ե������¸�ߥ����å�(����ȥ�֥�Ǻ���Ǥ����ĤäƤ�������б�)
            if (ftp_size($ftp_con, $fws_tmp) != -1) {
                ftp_delete($ftp_con, $fws_tmp);
            }
            /////////// FTP��Υե������¸�ߥ����å�
            if (ftp_size($ftp_con, $fws_log) == -1) {
                // �ե����뤬̵���Τǲ��⤷�ʤ�
            } else if (ftp_rename($ftp_con, $fws_log, $fws_tmp) == false) {
                $this->equip_log_writer("FTP rename() ���� {$fws_log}");
                if (file_exists($local_log)) unlink($local_log);
            } else if (@ftp_get($ftp_con, $local_log, $fws_tmp, FTP_ASCII) == false) {
                $this->equip_log_writer("{$fws_tmp}��FTP��Download�˼��Ԥ��ޤ�����");
                $this->getFTP_Retry($ftp_con, $local_log, $fws_tmp, $mac_no);
            } else {
                // FTP Download OK
                ftp_delete($ftp_con, $fws_tmp);  // ��ե�����Ϻ��
            }
        }
    }
    ///// ������ ������ΰ���ե������BCD�黻 ���� �᥽�å�
    protected function equip_log_state_rotaryHistory_write($mac_no, $csv_flg, $rotary_temp)
    {
        $fp = fopen($rotary_temp, 'r');
        // $bcd1 = 0; $bcd2 = 0; $bcd4 = 0; $bcd8 = 0;
        $this->getRotaryStateCurrentBCD($mac_no, $bcd1, $bcd2, $bcd4, $bcd8);
        $i = 0;
        while ( ($data = fgetcsv($fp, '50', ',')) !== false) {
            if (!$data[0]) {
                $this->equip_log_writer("{$mac_no}�Υ����꡼�����å����������ߤ϶��Ԥ����뤿����ߤ��ޤ���");
                return; // continue;    // ���ԥ����å�
            }
            $date_time = "{$data[0]} {$data[1]}";
            switch ($data[2]) {
            case 'bcd1':
                if ($data[3] == 'on') {
                    $bcd1 = 1;
                    $this->setRotaryStateCurrentBCD($mac_no, 'BCD1', 1, $date_time);
                } else {
                    $bcd1 = 0;
                    $this->setRotaryStateCurrentBCD($mac_no, 'BCD1', 0, $date_time);
                }
                break;
            case 'bcd2':
                if ($data[3] == 'on') {
                    $bcd2 = 2;
                    $this->setRotaryStateCurrentBCD($mac_no, 'BCD2', 2, $date_time);
                } else {
                    $bcd2 = 0;
                    $this->setRotaryStateCurrentBCD($mac_no, 'BCD2', 0, $date_time);
                }
                break;
            case 'bcd4':
                if ($data[3] == 'on') {
                    $bcd4 = 4;
                    $this->setRotaryStateCurrentBCD($mac_no, 'BCD4', 4, $date_time);
                } else {
                    $bcd4 = 0;
                    $this->setRotaryStateCurrentBCD($mac_no, 'BCD4', 0, $date_time);
                }
                break;
            case 'bcd8':
                if ($data[3] == 'on') {
                    $bcd8 = 8;
                    $this->setRotaryStateCurrentBCD($mac_no, 'BCD8', 8, $date_time);
                } else {
                    $bcd8 = 0;
                    $this->setRotaryStateCurrentBCD($mac_no, 'BCD8', 0, $date_time);
                }
                break;
            }
            $state = $bcd1 + $bcd2 + $bcd4 + $bcd8;
            if ($i > 0) {
                $query = "SELECT (TIMESTAMP '{$data[0]} {$data[1]}' - TIMESTAMP '{$preData[0]} {$preData[1]}') > INTERVAL '3 second' AS check_flg";
                getUniResult($query, $check_flg);
                if ($check_flg == 't') {
                    // ����ǤΣ��ä�Ķ�������������� �����
                    $this->equip_log_state_rotaryHistory_write_body($mac_no, $preState, $preData, $csv_flg);
                }
            }
            $this->equip_log_state_rotaryHistoryDebug($mac_no, $data, $state, $i);  // �ǥХå���
            $preData = $data;
            $preState = $state;
            $i++;
        }
        // file end �Ǥ�����ǡ��� �����
        if (isset($preState)) { // 2007/10/05 �̿����顼�Ȼפ���㳲�ǥ쥳���ɤ�̵�����֤ˤʤä���������å����ɲ�
            $this->equip_log_state_rotaryHistory_write_body($mac_no, $preState, $preData, $csv_flg);
        } else {
            $msg = "�����꡼�����å�������ե�����˥쥳���ɤ�̵����{$rotary_temp}\n";
            $this->equip_log_writer($msg);
        }
        fclose($fp);
        
        // �ƥ��� �ƥ��Ȳ������else¦�Τ߻Ĥ�
        if ($mac_no=='1224' || $mac_no=='1228' || $mac_no=='1258' || $mac_no=='1225' || $mac_no=='1226' || $mac_no=='1227' || $mac_no=='1228' || $mac_no=='1229' || $mac_no=='1230' || $mac_no=='1233' || $mac_no=='1234' || $mac_no=='1235' || $mac_no=='1257' || $mac_no=='1259' || $mac_no=='2101' || $mac_no=='2103' || $mac_no=='2106' || $mac_no=='2901' || $mac_no=='2902' || $mac_no=='2903' || $mac_no=='6000' || $mac_no=='6001' || $mac_no=='6002' || $mac_no=='6003' || $mac_no=='6004' || $mac_no=='6005' || $mac_no=='1346' || $mac_no=='1347' || $mac_no=='1348' || $mac_no=='1349' || $mac_no=='1364' || $mac_no=='1365' || $mac_no=='1366' || $mac_no=='1367' || $mac_no=='1368' || $mac_no=='1369' || $mac_no=='1372' || $mac_no=='1373' || $mac_no=='1374') {
        } else {
        unlink($rotary_temp);        // ����ե�����κ��
        }
    }
    ///// ������ �����꡼�����å��������������ΥǥХå��ǡ��� ���� �᥽�å�
    protected function equip_log_state_rotaryHistoryDebug($mac_no, $data, $state, $i)
    {
        $currentDir = realpath(dirname(__FILE__));
        $out_file = "{$currentDir}/rotary_BCD_debug.txt";
        $fp = fopen($out_file, 'a');
        $state_name = equip_machine_state($mac_no, $state, $txt_color, $bg_color);
        if ($i == 0) $mac_no = "\n{$mac_no}";   // �ǡ������ڤ��ܤ����Τˤ���
        if ($data[3] == 'on') {
            fwrite($fp, "{$mac_no},{$data[0]},{$data[1]},{$data[2]},{$data[3]} ,{$state},{$state_name}\n");
        } else {
            fwrite($fp, "{$mac_no},{$data[0]},{$data[1]},{$data[2]},{$data[3]},{$state},{$state_name}\n");
        }
        fclose($fp);
        chmod($out_file, 0666);
    }
    ///// ������ �����꡼�����å����������� ���� �᥽�å�
    protected function equip_log_state_rotaryHistory_write_body($mac_no, $state, $data, $csv_flg)
    {
        $state_name = equip_machine_state($mac_no, $state, $txt_color, $bg_color);
        $time_temp = $data[0] . " " . $data[1];     // timestamp�����ѿ����� ���
        //if ($this->equip_log_require($mac_no, $state, $time_temp, 1)) {
            $sql = "
                INSERT INTO equip_mac_state_log3 (mac_no, state, date_time, state_name, state_type)
                VALUES
                    ({$mac_no}, {$state}, '{$data[0]} {$data[1]}', '$state_name', $csv_flg)
            ";
            if (query_affected($sql) <= 0) {
                $msg = "�����꡼�����å������� insert error{$sql}\n";
                $this->equip_log_writer($msg);
            }
        //}
    }
    ///// ������ �����꡼�����å��κǿ����֤Υ����å�������� ���� �᥽�å�
    protected function equip_log_state_rotaryCurrent_write($mac_no, $csv_flg, $ftp_con)
    {
        $state = $this->getRotaryStateCurrent($ftp_con, $mac_no);
        $date_time = date('Y-m-d H:i:s');
        if ($state == $this->getRotaryStateHistory($mac_no, $date_time)) return ;
        $state_name = equip_machine_state($mac_no, $state, $txt_color, $bg_color);
        //if ($this->equip_log_require($mac_no, $state, $date_time, 1)) {
            $sql = "
                INSERT INTO equip_mac_state_log3 (mac_no, state, date_time, state_name, state_type)
                VALUES
                    ({$mac_no}, {$state}, '{$date_time}', '$state_name', $csv_flg)
            ";
            if (query_affected($sql) <= 0) {
                $msg = "�����꡼�����å��κǿ��ǡ���������� insert error{$sql}\n";
                $this->equip_log_writer($msg);
            } else {
                $this->equip_log_writer("�����꡼�����å�������ǽ��ȥ����ȥǡ�����̷�⤷�Ƥ��뤿�����ߤޤ���{$sql}");
            }
        //}
    }
    ///// ������ �����ξ��� (ʪ���ǡ����������ǡ���) ���� ������ �᥽�å�
    protected function equip_log_state_body($mac_no, $csv_flg, $ftp_con)
    {
        $query = "
            SELECT
                interface
            FROM
                equip_machine_interface
            WHERE
                mac_no = {$mac_no}
        ";
        $ftp_no = 0;
        getUniResult($query, $ftp_no);
        /*
        if ($ftp_no == 2) {
            $fws_state_log = "/MMC/Plc_Work/{$mac_no}_work_state.log";
            $fws_state_tmp = "/MMC/Plc_Work/{$mac_no}_work_state.tmp";
            $local_state = "/home/fws/{$mac_no}_work_state.log";
        } else {
            $fws_state_log = "/home/fws/usr/{$mac_no}_work_state.log";
            $fws_state_tmp = "/home/fws/usr/{$mac_no}_work_state.tmp";
            $local_state = "/home/fws/{$mac_no}_work_state.log";
        }
        */
        // FWS2 ���ؽ����� ���PGM������
        if ($ftp_no == 2) {
            $fws_state_log = "/MMC/Plc_Work/{$mac_no}_work_state.log";
            $fws_state_tmp = "/MMC/Plc_Work/{$mac_no}_work_state.tmp";
            $local_state = "/home/fws/{$mac_no}_work_state.log";
        } elseif ($ftp_no == 3) {
            $fws_state_log = "/0_CARD/Plc_Work/{$mac_no}_work_state.log";
            $fws_state_tmp = "/0_CARD/Plc_Work/{$mac_no}_work_state.tmp";
            $local_state = "/home/fws/{$mac_no}_work_state.log";
        } elseif ($ftp_no == 4) {
            $fws_state_log = "/0_CARD/Plc_Work/{$mac_no}_work_state.log";
            $fws_state_tmp = "/0_CARD/Plc_Work/{$mac_no}_work_state.tmp";
            $local_state = "/home/fws/{$mac_no}_work_state.log";
        } elseif ($ftp_no == 7 || $ftp_no == 8) {
            $fws_state_log = "/0_CARD/Plc_Work/{$mac_no}_work_state.log";
            $fws_state_tmp = "/0_CARD/Plc_Work/{$mac_no}_work_state.tmp";
            $local_state = "/home/fws/{$mac_no}_work_state.log";
        } else {
            $fws_state_log = "/home/fws/usr/{$mac_no}_work_state.log";
            $fws_state_tmp = "/home/fws/usr/{$mac_no}_work_state.tmp";
            $local_state = "/home/fws/{$mac_no}_work_state.log";
        }
        ///// ���� FTP Download ����
        $this->equip_FTP_Download($fws_state_log, $fws_state_tmp, $local_state, $ftp_con, $mac_no);
        ///// State Log File Check        ���ߤξ���(��ž�桦�����)����������� �������Ÿ�OFF�����ͽ��
        // �ƥ��� 1225����������ΤȤ��Ƽ��� �ƥ��Ȳ������else¦�Τ߻Ĥ�
        if ($mac_no=='1346' || $mac_no=='1347' || $mac_no=='1348' || $mac_no=='1349' || $mac_no=='1364' || $mac_no=='1365' || $mac_no=='1366' || $mac_no=='1367' || $mac_no=='1368' || $mac_no=='1369' || $mac_no=='1372' || $mac_no=='1373' || $mac_no=='1374') {
            $timestamp = time();
            $state_temp = "/home/fws/��7���쿿�/{$mac_no}_work_state" . $timestamp . ".tmp" ;     // Rename �ѥǥ��쥯�ȥꡦ�ե�����̾����
        } elseif ($mac_no=='1224' || $mac_no=='1228' || $mac_no=='1258' || $mac_no=='1225' || $mac_no=='1226' || $mac_no=='1227' || $mac_no=='1228' || $mac_no=='1229' || $mac_no=='1230' || $mac_no=='1233' || $mac_no=='1234' || $mac_no=='1235' || $mac_no=='1257' || $mac_no=='1259') {
            $timestamp = time();
            $state_temp = "/home/fws/��7����SUS/{$mac_no}_work_state" . $timestamp . ".tmp" ;     // Rename �ѥǥ��쥯�ȥꡦ�ե�����̾����
        } elseif ($mac_no=='2101' || $mac_no=='2103' || $mac_no=='2106' || $mac_no=='2901' || $mac_no=='2902' || $mac_no=='2903' || $mac_no=='6000' || $mac_no=='6001' || $mac_no=='6002' || $mac_no=='6003' || $mac_no=='6004' || $mac_no=='6005') {
            $timestamp = time();
            $state_temp = "/home/fws/��6����/{$mac_no}_work_state" . $timestamp . ".tmp" ;     // Rename �ѥǥ��쥯�ȥꡦ�ե�����̾����
        } else {
            $state_temp = "/home/fws/{$mac_no}_work_state.tmp";     // Rename �ѥǥ��쥯�ȥꡦ�ե�����̾����
        }
        if (file_exists($local_state)) {                         // State Log File �������
            if (rename($local_state, $state_temp)) {
                $this->equip_log_state_ftp_write($mac_no, $csv_flg, $state_temp, $ftp_con);
            } else {
                $msg = "���ơ������ե������ rename({$local_state}) �˼���";
                $this->equip_log_writer($msg);
            }
        } else {
            ///// State Log file ���ʤ����ϥ����꡼�����å��Τߥ����å�����
            // ʪ�������DB������
            $this->equip_log_state_db_write($mac_no, $csv_flg, $ftp_con);
        }
        ///// State Log ������λ
    }
    ///// ʪ�����֥ե������FTP��������� �ƻ�� �����᥽�å�
    protected function getFTP_Retry($ftp_con, $local_file, $fws_file, $mac_no)
    {
        if (@ftp_get($ftp_con, $local_file, $fws_file, FTP_ASCII)) {
            $this->equip_log_writer("{$fws_file}��Download�κƻ�Ԥ��������ޤ�����");
            ftp_delete($ftp_con, $fws_file);  // ��ե�����Ϻ��
            return true;
        } else {
            $query = "
                SELECT
                interface
                FROM
                equip_machine_interface
                WHERE
                mac_no = {$mac_no}
            ";
            $ftp_no = 0;
            getUniResult($query, $ftp_no);
            if ($ftp_no == 2) {
            } elseif ($ftp_no == 3) {
            } elseif ($ftp_no == 4) {
            } elseif ($ftp_no ==7 || $ftp_no ==8) {
            } else {
                $this->equip_log_writer("{$fws_file}��Download�κƻ�ԤǼ��Ԥ��ޤ�����");
            }
            if (file_exists($local_file)) {
                unlink($local_file);
            }
            return false;
        }
    }
    
    ///// ������ ���֥ǡ����������� ���� �᥽�å�
    protected function equip_log_state_ftp_write($mac_no, $csv_flg, $state_temp, $ftp_con)
    {
        $fp   = fopen($state_temp, 'r');
        $row  = 0;                                  // ���쥳����
        $data = array();                            // ǯ����,����,�ù���
        while ($data[$row] = fgetcsv($fp, 50, ',')) {
            if ($data[$row][0] == '') continue;     // ��Ƭ�ե�����ɤǥ쥳���ɥ����å�
            $row++;
        }
        for ($j=0; $j<$row; $j++) {         // Status File �˥쥳���ɤ�����о��֤�����������
            if ($data[$j][2] == 'auto') {   // �ե����뤫��ʪ�������ֹ�����
                $state_p = 1;               // ��ž��(��ư��ž)
            } elseif ($data[$j][2] == 'stop') {
                $state_p = 3;               // �����
            } elseif ($data[$j][2] == 'on') {
                $state_p = 3;               // �Ÿ�ON�ξ���Default�ͤ������=3
            } else {
                $state_p = 0;               // �Ÿ�OFF "off"
            }

            $date_time = $data[$j][0] . " " . $data[$j][1];     // timestamp�����ѿ����� ���

            // ���顼���Ф�Τǥƥ��� ��ǤϤʤ������ߤκǿ����֤�ǿ����֤Ȥ����ݴ�
            //$date_time = date('Ymd His');   // ���ߤλ��֤�Ȥ� PostgreSQL��TIMESTAMP�����ѹ�
            
            $state_r = $this->getRotaryStateHistory($mac_no, $date_time);// �����꡼�����å������򤫤�ǡ�������
            $state = $this->equip_state_check($state_p, $state_r);  // ʪ�����ֿ���ȥ����å��ξ��֤�Ŭ���ͤ�����å�
            $state_name = equip_machine_state($mac_no, $state, $txt_color, $bg_color);
            ///// ʪ������ȥ����꡼�����å��Ǥξ��ֽ����
            // ����Υǡ����Ȱ㤨�н����
            if ($this->equip_log_require($mac_no, $state, $date_time, 1)) {
                $query = "
                    INSERT INTO
                        equip_mac_state_log
                        (mac_no, state, date_time, state_name, state_type)
                    VALUES
                        ($mac_no, $state, '$date_time', '$state_name', $csv_flg)
                ";
                if (query_affected($query) <= 0) {
                    $msg = "insert error{$query}\n date_time:{$date_time} mac_no:{$mac_no} state:{$state_p} j={$j}";
                    $this->equip_log_writer($msg);
                }
            }
            ///// ʪ������Τߤξ��ֽ����(���ȸߴ�������)
            // ����Υǡ����Ȱ㤨�н����
            if ($this->equip_log_require($mac_no, $state_p, $date_time, 2)) {
                $state_name = equip_machine_state($mac_no, $state_p, $txt_color, $bg_color);
                $query = "
                    INSERT INTO
                        equip_mac_state_log2
                        (mac_no, state, date_time, state_name, state_type)
                    VALUES
                        ($mac_no, $state_p, '$date_time', '$state_name', $csv_flg)
                ";
                if (query_affected($query) <= 0) {
                    $msg = "insert error{$query}\n date_time:{$date_time} mac_no:{$mac_no} state:{$state_p} j={$j}";
                    $this->equip_log_writer($msg);
                }
            }
        }
        fclose($fp);
        // �ƥ��� 1225����������ΤȤ��Ƽ��� �ƥ��Ȳ������else¦�Τ߻Ĥ�
        if ($mac_no=='1224' || $mac_no=='1228' || $mac_no=='1258' || $mac_no=='1225' || $mac_no=='1226' || $mac_no=='1227' || $mac_no=='1228' || $mac_no=='1229' || $mac_no=='1230' || $mac_no=='1233' || $mac_no=='1234' || $mac_no=='1235' || $mac_no=='1257' || $mac_no=='1259' || $mac_no=='2101' || $mac_no=='2103' || $mac_no=='2106' || $mac_no=='2901' || $mac_no=='2902' || $mac_no=='2903' || $mac_no=='6000' || $mac_no=='6001' || $mac_no=='6002' || $mac_no=='6003' || $mac_no=='6004' || $mac_no=='6005' || $mac_no=='1346' || $mac_no=='1347' || $mac_no=='1348' || $mac_no=='1349' || $mac_no=='1364' || $mac_no=='1365' || $mac_no=='1366' || $mac_no=='1367' || $mac_no=='1368' || $mac_no=='1369' || $mac_no=='1372' || $mac_no=='1373' || $mac_no=='1374') {
        } else {
            unlink($state_temp);        // ����ե�����κ��
        }
    }
    
    ///// ������ ���֥ǡ����ʤ�DB���ʪ��������� ����� ���� �᥽�å�
    protected function equip_log_state_db_write($mac_no, $csv_flg, $ftp_con)
    {
        ///// State Log file ���ʤ����ϥ����꡼�����å��Τߥ����å�����
        // ʪ�������DB������
        $state_p = $this->getPhysicalState($mac_no);
        
        $date_time = date('Ymd His');   // ���ߤλ��֤�Ȥ� PostgreSQL��TIMESTAMP�����ѹ�
        $state_r = $this->getRotaryStateHistory($mac_no, $date_time);// �����꡼�����å������򤫤�ǡ�������
        $state = $this->equip_state_check($state_p, $state_r);  // ʪ�����ֿ���ȥ����å��ξ��֤�Ŭ���ͤ�����å�
        $state_name = equip_machine_state($mac_no, $state, $txt_color, $bg_color);
        ///// ʪ������ȥ����꡼�����å��Ǥξ��ֽ����
        // ����Υǡ����Ȱ㤨�н����
        if ($this->equip_log_require($mac_no, $state, $date_time, 1)) {
            $query = "
                INSERT INTO
                    equip_mac_state_log
                    (mac_no, state, date_time, state_name, state_type)
                VALUES
                    ($mac_no, $state, '$date_time', '$state_name', $csv_flg)
            ";
            if (query_affected($query) <= 0) {
                $msg = "insert error{$query}\n date_time:{$date_time} mac_no:{$mac_no} state:{$state_p} j={$j}";
                $this->equip_log_writer($msg);
            }
        }
    }
    
    ///// ���μ��� �ù����μ��� �����᥽�å�
    protected function equip_log_workCnt($interface, $ftp_con)
    {
        ////////// ��ư��ε������إå����ե����뤫����� & �����ޥ��������鵡��̾�����
        $query = "
            SELECT
                mac_no, siji_no, koutei, parts_no --2007/06/26�����ȥ�����, plan_cnt, mac_name, csv_flg
            FROM
                equip_work_log2_header
            LEFT OUTER JOIN
                equip_machine_master2
                USING (mac_no)
            WHERE
                work_flg is TRUE
                AND
                csv_flg = {$interface}
                AND
                survey = 'Y'
                {$this->factoryWhere} {$this->macNoWhere}
        ";
            // $interface == Netmoni=1 fwserver1=2 fwserver2=3 fwserver3=4 fwserver4=5 ... , Net&fws1=101
        $res_key = array();
        if ( ($rows_key = getResult($query, $res_key)) >= 1) {      // �쥳���ɤ����ʾ夢���
            for ($i=0; $i<$rows_key; $i++) {                        // ���쥳���ɤ��Ľ���
                $this->equip_log_workCnt_body($res_key[$i], $ftp_con);
            }
        }
    }
    
    ///// ���μ��� �ù����μ��� �����᥽�å�
    protected function equip_log_workCnt_moni($interface, $ftp_con)
    {
        ////////// ��ư��ε������إå����ե����뤫����� & �����ޥ��������鵡��̾�����
        $query = "
            SELECT
                mac_no, plan_no, koutei, parts_no --2007/06/26�����ȥ�����, plan_cnt, mac_name, csv_flg
            FROM
                equip_work_log2_header_moni
            LEFT OUTER JOIN
                equip_machine_master2
                USING (mac_no)
            WHERE
                work_flg is TRUE
                AND
                csv_flg = {$interface}
                AND
                survey = 'Y'
                {$this->factoryWhere} {$this->macNoWhere}
        ";
            // $interface == Netmoni=1 fwserver1=2 fwserver2=3 fwserver3=4 fwserver4=5 ... , Net&fws1=101
        $res_key = array();
        if ( ($rows_key = getResult($query, $res_key)) >= 1) {      // �쥳���ɤ����ʾ夢���
            for ($i=0; $i<$rows_key; $i++) {                        // ���쥳���ɤ��Ľ���
                $this->equip_log_workCnt_body_moni($res_key[$i], $ftp_con);
            }
        }
    }
    
    ///// ���μ��� �ù����μ��� ������ �����᥽�å�
    protected function equip_log_workCnt_body($res_key, $ftp_con)
    {
        ///// insert �� �ѿ� �����
        $mac_no   = $res_key['mac_no'];
        $siji_no  = $res_key['siji_no'];
        $koutei   = $res_key['koutei'];
        ///// �ե�����̾����
        $query = "
            SELECT
                interface
            FROM
                equip_machine_interface
            WHERE
                mac_no = {$mac_no}
        ";
        $ftp_no = 0;
        getUniResult($query, $ftp_no);
        /*
        if ($ftp_no == 2) {
            $fws_cnt_log = "/MMC/Plc_Work/{$mac_no}_work_cnt.log";
            $fws_cnt_tmp = "/MMC/Plc_Work/{$mac_no}_work_cnt.tmp";
            $local_cnt = "/home/fws/{$mac_no}_work_cnt.log";
            $cnt_temp  = "/home/fws/{$mac_no}_work_cnt.tmp";    // Rename�� ����ե�����̾����
        } else {
            $fws_cnt_log = "/home/fws/usr/{$mac_no}_work_cnt.log";
            $fws_cnt_tmp = "/home/fws/usr/{$mac_no}_work_cnt.tmp";
            $local_cnt = "/home/fws/{$mac_no}_work_cnt.log";
            $cnt_temp  = "/home/fws/{$mac_no}_work_cnt.tmp";    // Rename�� ����ե�����̾����
        }
        */
        // FWS2 ���ؽ��� ���PGM�������ؤ���
        if ($ftp_no == 2) {
            $fws_cnt_log = "/MMC/Plc_Work/{$mac_no}_work_cnt.log";
            $fws_cnt_tmp = "/MMC/Plc_Work/{$mac_no}_work_cnt.tmp";
            $local_cnt = "/home/fws/{$mac_no}_work_cnt.log";
            $cnt_temp  = "/home/fws/{$mac_no}_work_cnt.tmp";    // Rename�� ����ե�����̾����
        } elseif ($ftp_no == 3) {
            $fws_cnt_log = "/0_CARD/Plc_Work/{$mac_no}_work_cnt.log";
            $fws_cnt_tmp = "/0_CARD/Plc_Work/{$mac_no}_work_cnt.tmp";
            $local_cnt = "/home/fws/{$mac_no}_work_cnt.log";
            $cnt_temp  = "/home/fws/{$mac_no}_work_cnt.tmp";    // Rename�� ����ե�����̾����
        } elseif ($ftp_no == 4) {
            $fws_cnt_log = "/0_CARD/Plc_Work/{$mac_no}_work_cnt.log";
            $fws_cnt_tmp = "/0_CARD/Plc_Work/{$mac_no}_work_cnt.tmp";
            $local_cnt = "/home/fws/{$mac_no}_work_cnt.log";
            $cnt_temp  = "/home/fws/{$mac_no}_work_cnt.tmp";    // Rename�� ����ե�����̾����
        } elseif ($ftp_no == 7 || $ftp_no == 8) {
            $fws_cnt_log = "/0_CARD/Plc_Work/{$mac_no}_work_cnt.log";
            $fws_cnt_tmp = "/0_CARD/Plc_Work/{$mac_no}_work_cnt.tmp";
            $local_cnt = "/home/fws/{$mac_no}_work_cnt.log";
            $cnt_temp  = "/home/fws/{$mac_no}_work_cnt.tmp";    // Rename�� ����ե�����̾����
        } else {
            $fws_cnt_log = "/home/fws/usr/{$mac_no}_work_cnt.log";
            $fws_cnt_tmp = "/home/fws/usr/{$mac_no}_work_cnt.tmp";
            $local_cnt = "/home/fws/{$mac_no}_work_cnt.log";
            $cnt_temp  = "/home/fws/{$mac_no}_work_cnt.tmp";    // Rename�� ����ե�����̾����
        }
        ///// ���� FTP Download ����
        $this->equip_FTP_Download($fws_cnt_log, $fws_cnt_tmp, $local_cnt, $ftp_con, $mac_no);
        ///// Counter File Check        ���ߤβù��������������
        // �ƥ��� �ƥ��Ȳ������else¦�Τ߻Ĥ�
        if ($mac_no=='1346' || $mac_no=='1347' || $mac_no=='1348' || $mac_no=='1349' || $mac_no=='1364' || $mac_no=='1365' || $mac_no=='1366' || $mac_no=='1367' || $mac_no=='1368' || $mac_no=='1369' || $mac_no=='1372' || $mac_no=='1373' || $mac_no=='1374') {
            $timestamp = time();
            $cnt_temp  = "/home/fws/��7���쿿�/{$mac_no}_work_cnt" . $timestamp . ".tmp" ;     // Rename �ѥǥ��쥯�ȥꡦ�ե�����̾����
        } elseif ($mac_no=='1224' || $mac_no=='1228' || $mac_no=='1258' || $mac_no=='1225' || $mac_no=='1226' || $mac_no=='1227' || $mac_no=='1228' || $mac_no=='1229' || $mac_no=='1230' || $mac_no=='1233' || $mac_no=='1234' || $mac_no=='1235' || $mac_no=='1257' || $mac_no=='1259') {
            $timestamp = time();
            $cnt_temp  = "/home/fws/��7����SUS/{$mac_no}_work_cnt" . $timestamp . ".tmp" ;     // Rename �ѥǥ��쥯�ȥꡦ�ե�����̾����
        } else {
            $cnt_temp  = "/home/fws/{$mac_no}_work_cnt.tmp";    // Rename�� ����ե�����̾����
        }
        if (file_exists($local_cnt)) {                       // Counter File �������
            if (rename($local_cnt, $cnt_temp)) {
                $this->equip_log_workCnt_ftp_write($res_key, $cnt_temp);
            } else {
                $this->equip_log_writer("�����󥿡��ե������ rename({$local_cnt}) �˼���");
            }
        } else {                    // Counter File ���ʤ��ΤǾ��֤Τ߽����
            ///// ���֥ǡ����ϻ���������DB�����Ф���
            $this->equip_log_workCnt_db_write($res_key);
        }
    }
    
    ///// ���μ��� �ù����μ��� ������ �����᥽�å�
    protected function equip_log_workCnt_body_moni($res_key, $ftp_con)
    {
        ///// insert �� �ѿ� �����
        $mac_no   = $res_key['mac_no'];
        $plan_no  = $res_key['plan_no'];
        $koutei   = $res_key['koutei'];
        ///// �ե�����̾����
        $query = "
            SELECT
                interface
            FROM
                equip_machine_interface
            WHERE
                mac_no = {$mac_no}
        ";
        $ftp_no = 0;
        getUniResult($query, $ftp_no);
        /*
        if ($ftp_no == 2) {
            $fws_cnt_log = "/MMC/Plc_Work/{$mac_no}_work_cnt.log";
            $fws_cnt_tmp = "/MMC/Plc_Work/{$mac_no}_work_cnt.tmp";
            $local_cnt = "/home/fws/{$mac_no}_work_cnt.log";
            $cnt_temp  = "/home/fws/{$mac_no}_work_cnt.tmp";    // Rename�� ����ե�����̾����
        } else {
            $fws_cnt_log = "/home/fws/usr/{$mac_no}_work_cnt.log";
            $fws_cnt_tmp = "/home/fws/usr/{$mac_no}_work_cnt.tmp";
            $local_cnt = "/home/fws/{$mac_no}_work_cnt.log";
            $cnt_temp  = "/home/fws/{$mac_no}_work_cnt.tmp";    // Rename�� ����ե�����̾����
        }
        */
        // FWS2 ���ؽ��� ���PGM�������ؤ���
        if ($ftp_no == 2) {
            $fws_cnt_log = "/MMC/Plc_Work/{$mac_no}_work_cnt.log";
            $fws_cnt_tmp = "/MMC/Plc_Work/{$mac_no}_work_cnt.tmp";
            $local_cnt = "/home/fws/{$mac_no}_work_cnt.log";
            $cnt_temp  = "/home/fws/{$mac_no}_work_cnt.tmp";    // Rename�� ����ե�����̾����
        } elseif ($ftp_no == 3) {
            $fws_cnt_log = "/0_CARD/Plc_Work/{$mac_no}_work_cnt.log";
            $fws_cnt_tmp = "/0_CARD/Plc_Work/{$mac_no}_work_cnt.tmp";
            $local_cnt = "/home/fws/{$mac_no}_work_cnt.log";
            $cnt_temp  = "/home/fws/{$mac_no}_work_cnt.tmp";    // Rename�� ����ե�����̾����
        } elseif ($ftp_no == 4) {
            $fws_cnt_log = "/0_CARD/Plc_Work/{$mac_no}_work_cnt.log";
            $fws_cnt_tmp = "/0_CARD/Plc_Work/{$mac_no}_work_cnt.tmp";
            $local_cnt = "/home/fws/{$mac_no}_work_cnt.log";
            $cnt_temp  = "/home/fws/{$mac_no}_work_cnt.tmp";    // Rename�� ����ե�����̾����
        } elseif ($ftp_no == 7 || $ftp_no == 8) {
            $fws_cnt_log = "/0_CARD/Plc_Work/{$mac_no}_work_cnt.log";
            $fws_cnt_tmp = "/0_CARD/Plc_Work/{$mac_no}_work_cnt.tmp";
            $local_cnt = "/home/fws/{$mac_no}_work_cnt.log";
            $cnt_temp  = "/home/fws/{$mac_no}_work_cnt.tmp";    // Rename�� ����ե�����̾����
        } else {
            $fws_cnt_log = "/home/fws/usr/{$mac_no}_work_cnt.log";
            $fws_cnt_tmp = "/home/fws/usr/{$mac_no}_work_cnt.tmp";
            $local_cnt = "/home/fws/{$mac_no}_work_cnt.log";
            $cnt_temp  = "/home/fws/{$mac_no}_work_cnt.tmp";    // Rename�� ����ե�����̾����
        }
        ///// ���� FTP Download ����
        $this->equip_FTP_Download($fws_cnt_log, $fws_cnt_tmp, $local_cnt, $ftp_con, $mac_no);
        // �ƥ��� �ƥ��Ȳ������else¦�Τ߻Ĥ�
        if ($mac_no=='2101' || $mac_no=='2103' || $mac_no=='2106' || $mac_no=='2901' || $mac_no=='2902' || $mac_no=='2903' || $mac_no=='6000' || $mac_no=='6001' || $mac_no=='6002' || $mac_no=='6003' || $mac_no=='6004' || $mac_no=='6005') {
            $timestamp = time();
            $cnt_temp  = "/home/fws/��6����/{$mac_no}_work_cnt" . $timestamp . ".tmp" ;     // Rename �ѥǥ��쥯�ȥꡦ�ե�����̾����
        } else {
            $cnt_temp  = "/home/fws/{$mac_no}_work_cnt.tmp";    // Rename�� ����ե�����̾����
        }
        
        ///// Counter File Check        ���ߤβù��������������
        if (file_exists($local_cnt)) {                       // Counter File �������
            if (rename($local_cnt, $cnt_temp)) {
                $this->equip_log_workCnt_ftp_write_moni($res_key, $cnt_temp);
            } else {
                $this->equip_log_writer("�����󥿡��ե������ rename({$local_cnt}) �˼���");
            }
        } else {                    // Counter File ���ʤ��ΤǾ��֤Τ߽����
            ///// ���֥ǡ����ϻ���������DB�����Ф���
            $this->equip_log_workCnt_db_write_moni($res_key);
        }
    }
    ///// ���μ��� �ù����ν���� FTP��˥ǡ�������ξ�� �����᥽�å�
    protected function equip_log_workCnt_ftp_write($res_key, $cnt_temp)
    {
        ///// insert �� �ѿ� �����
        $mac_no   = $res_key['mac_no'];
        $siji_no  = $res_key['siji_no'];
        $koutei   = $res_key['koutei'];
        $parts_no = $res_key['parts_no'];
        ///// �����󥿡��ޥ������ɹ���
        $cntMulti = $this->getCounterMaster($mac_no, $parts_no); // Counter Multiple
        ///// ��͡��ष�������󥿡��ե�����
        $fp = fopen ($cnt_temp,'r');
        $row  = 0;                                  // ���쥳����
        $data = array();                            // ǯ����,����,�ù���
        while ($data[$row] = fgetcsv ($fp, 50, ',')) {
            if ($data[$row][0] == '') continue;     // ��Ƭ�ե�����ɤǥ쥳���ɥ����å�
            $row++;
        }
        if ($row >= 1) {            // Counter File �˥쥳���ɤ�����о��֤Ȳù��������
            ///// ���ߤΥǡ����١����κǿ��쥳���ɤ������
            $query = "
                SELECT mac_state, work_cnt FROM equip_work_log2
                WHERE
                    equip_index(mac_no, siji_no, koutei, date_time) < '{$mac_no}{$siji_no}{$koutei}99999999999999'
                    AND
                    equip_index(mac_no, siji_no, koutei, date_time) > '{$mac_no}{$siji_no}{$koutei}00000000000000'
                ORDER BY equip_index(mac_no, siji_no, koutei, date_time) DESC LIMIT 1
            ";
            $res = array();
            $multi_t_num = 0;
            $multi_t_c   = 0;
            if ( ($rows = getResult($query, $res)) >= 1) {  // ����βù����˥ץ饹���ƽ����
                for ($j=0; $j<$row; $j++) {
                    ///// ���֥ǡ����ϻ���������DB�����Ф���
                    $state = $this->getLogicalState($mac_no, $data[$j][0], $data[$j][1]);
                    if ($data[$j][2] == 'cnt') {
                        $work_cnt  = $res[0]['work_cnt'] + (($j + 1 - $multi_t_c) * $cntMulti) + $multi_t_num;  // Counter UP
                    } else {
                        $cntMulti_t = str_replace('cnt','',$data[$j][2]) * 1;
                        $multi_t_num = $multi_t_num + $cntMulti_t;
                        $multi_t_c   = $multi_t_c + 1;
                        $work_cnt  = $res[0]['work_cnt'] + (($j + 1 - $multi_t_c) * $cntMulti) + $multi_t_num;  // Counter UP
                    }
                    $date_time = $data[$j][0] . ' ' . $data[$j][1];     // PostgreSQL��TIMESTAMP�����ѹ�
                    $query = $this->getSQLworkCntInsert($mac_no, $date_time, $state, $work_cnt, $siji_no, $koutei);
                    if (query_affected($query) <= 0) {
                        $this->equip_log_writer("���� error{$query}");
                    }
                }
            } else {                    // �ǡ����١��������Τ���̵���˽����
                for ($j=0; $j<$row; $j++) {
                    ///// ���֥ǡ����ϻ���������DB�����Ф���
                    $state = $this->getLogicalState($mac_no, $data[$j][0], $data[$j][1]);
                    if ($data[$j][2] == 'cnt') {
                        $work_cnt  =  (($j + 1) * $cntMulti - $multi_t_c) + $multi_t_num;   // ���ξ��Ϥ������㤦
                    } else {
                        $cntMulti_t = str_replace('cnt','',$data[$j][2]) * 1;
                        $multi_t_num = $multi_t_num + $cntMulti_t;
                        $multi_t_c   = $multi_t_c + 1;
                        $work_cnt  =  (($j + 1) * $cntMulti - $multi_t_c) + $multi_t_num;   // ���ξ��Ϥ������㤦
                    }
                    $date_time = $data[$j][0] . ' ' . $data[$j][1];     // PostgreSQL��TIMESTAMP�����ѹ�
                    ///// ���ξ��ϲ��Υǡ����������ǽ�����⤤����ʲ���ɬ��
                    $query = $this->getSQLworkCntInsert($mac_no, $date_time, $state, $work_cnt, $siji_no, $koutei);
                    if (query_affected($query) <= 0) {
                        $this->equip_log_writer("��󹹿� error{$query}");
                    } else {
                        ///// ���μ��� ���βù����ν���ߤ˥إå����ȥ�������������å����ƹ���
                        $msg = '���ǡ�����������Ӥ�';
                        $this->equip_log_workCnt_header_write($res_key, $date_time, $msg);
                    }
                }
            }
            // Counter File ������Τ˥쥳���ɤ��ʤ����ݤϣ�ǯ�֤μ��Ӥǣ��٤�̵���Τǡ��б����å��Ͼ�ά����
        }
        // �ƥ��� �ƥ��Ȳ������else¦�Τ߻Ĥ�
        if ($mac_no=='1224' || $mac_no=='1228' || $mac_no=='1258' || $mac_no=='1225' || $mac_no=='1226' || $mac_no=='1227' || $mac_no=='1228' || $mac_no=='1229' || $mac_no=='1230' || $mac_no=='1233' || $mac_no=='1234' || $mac_no=='1235' || $mac_no=='1257' || $mac_no=='1259' || $mac_no=='2101' || $mac_no=='2103' || $mac_no=='2106' || $mac_no=='2901' || $mac_no=='2902' || $mac_no=='2903' || $mac_no=='6000' || $mac_no=='6001' || $mac_no=='6002' || $mac_no=='6003' || $mac_no=='6004' || $mac_no=='6005' || $mac_no=='1346' || $mac_no=='1347' || $mac_no=='1348' || $mac_no=='1349' || $mac_no=='1364' || $mac_no=='1365' || $mac_no=='1366' || $mac_no=='1367' || $mac_no=='1368' || $mac_no=='1369' || $mac_no=='1372' || $mac_no=='1373' || $mac_no=='1374') {
        } else {
        unlink($cnt_temp);      // ����ե�����κ��
        }
    }
    ///// ���μ��� �ù����ν���� FTP��˥ǡ�������ξ�� �����᥽�å�
    protected function equip_log_workCnt_ftp_write_moni($res_key, $cnt_temp)
    {
        ///// insert �� �ѿ� �����
        $mac_no   = $res_key['mac_no'];
        $plan_no  = $res_key['plan_no'];
        $koutei   = $res_key['koutei'];
        $parts_no = $res_key['parts_no'];
        ///// �����󥿡��ޥ������ɹ���
        $cntMulti = $this->getCounterMaster($mac_no, $parts_no); // Counter Multiple
        ///// ��͡��ष�������󥿡��ե�����
        $fp = fopen ($cnt_temp,'r');
        $row  = 0;                                  // ���쥳����
        $data = array();                            // ǯ����,����,�ù���
        while ($data[$row] = fgetcsv ($fp, 50, ',')) {
            if ($data[$row][0] == '') continue;     // ��Ƭ�ե�����ɤǥ쥳���ɥ����å�
            $row++;
        }
        if ($row >= 1) {            // Counter File �˥쥳���ɤ�����о��֤Ȳù��������
            ///// ���ߤΥǡ����١����κǿ��쥳���ɤ������
            $query = "
                SELECT mac_state, work_cnt FROM equip_work_log2_moni
                WHERE
                    equip_index_moni(mac_no, plan_no, koutei, date_time) < '{$mac_no}{$plan_no}{$koutei}99999999999999'
                    AND
                    equip_index_moni(mac_no, plan_no, koutei, date_time) > '{$mac_no}{$plan_no}{$koutei}00000000000000'
                ORDER BY equip_index_moni(mac_no, plan_no, koutei, date_time) DESC LIMIT 1
            ";
            $res = array();
            $multi_t_num = 0;
            $multi_t_c   = 0;
            if ( ($rows = getResult($query, $res)) >= 1) {  // ����βù����˥ץ饹���ƽ����
                for ($j=0; $j<$row; $j++) {
                    ///// ���֥ǡ����ϻ���������DB�����Ф���
                    $state = $this->getLogicalState($mac_no, $data[$j][0], $data[$j][1]);
                    if ($data[$j][2] == 'cnt') {
                        $work_cnt  = $res[0]['work_cnt'] + (($j + 1 - $multi_t_c) * $cntMulti) + $multi_t_num;  // Counter UP
                    } else {
                        $cntMulti_t = str_replace('cnt','',$data[$j][2]) * 1;
                        $multi_t_num = $multi_t_num + $cntMulti_t;
                        $multi_t_c   = $multi_t_c + 1;
                        $work_cnt  = $res[0]['work_cnt'] + (($j + 1 - $multi_t_c) * $cntMulti) + $multi_t_num;  // Counter UP
                    }
                    $date_time = $data[$j][0] . ' ' . $data[$j][1];     // PostgreSQL��TIMESTAMP�����ѹ�
                    $query = $this->getSQLworkCntInsert_moni($mac_no, $date_time, $state, $work_cnt, $plan_no, $koutei);
                    if (query_affected($query) <= 0) {
                        $this->equip_log_writer("���� error{$query}");
                    }
                }
            } else {                    // �ǡ����١��������Τ���̵���˽����
                for ($j=0; $j<$row; $j++) {
                    ///// ���֥ǡ����ϻ���������DB�����Ф���
                    $state = $this->getLogicalState($mac_no, $data[$j][0], $data[$j][1]);
                    if ($data[$j][2] == 'cnt') {
                        $work_cnt  =  (($j + 1 - $multi_t_c) * $cntMulti) + $multi_t_num;   // ���ξ��Ϥ������㤦
                    } else {
                        $cntMulti_t = str_replace('cnt','',$data[$j][2]) * 1;
                        $multi_t_num = $multi_t_num + $cntMulti_t;
                        $multi_t_c   = $multi_t_c + 1;
                        $work_cnt  =  (($j + 1 - $multi_t_c) * $cntMulti) + $multi_t_num;   // ���ξ��Ϥ������㤦
                    }
                    $date_time = $data[$j][0] . ' ' . $data[$j][1];     // PostgreSQL��TIMESTAMP�����ѹ�
                    ///// ���ξ��ϲ��Υǡ����������ǽ�����⤤����ʲ���ɬ��
                    $query = $this->getSQLworkCntInsert_moni($mac_no, $date_time, $state, $work_cnt, $plan_no, $koutei);
                    if (query_affected($query) <= 0) {
                        $this->equip_log_writer("��󹹿� error{$query}");
                    } else {
                        ///// ���μ��� ���βù����ν���ߤ˥إå����ȥ�������������å����ƹ���
                        $msg = '���ǡ�����������Ӥ�';
                        $this->equip_log_workCnt_header_write_moni($res_key, $date_time, $msg);
                    }
                }
            }
            // Counter File ������Τ˥쥳���ɤ��ʤ����ݤϣ�ǯ�֤μ��Ӥǣ��٤�̵���Τǡ��б����å��Ͼ�ά����
        }
        if ($mac_no=='2101' || $mac_no=='2103' || $mac_no=='2106' || $mac_no=='2901' || $mac_no=='2902' || $mac_no=='2903' || $mac_no=='6000' || $mac_no=='6001' || $mac_no=='6002' || $mac_no=='6003' || $mac_no=='6004' || $mac_no=='6005') {
        } else {
        unlink($cnt_temp);      // ����ե�����κ��
        }
    }
    ///// �ù����ν���� SQLʸ������ FTP��˥ǡ�������ξ�� �����᥽�å�
    protected function getSQLworkCntInsert($mac_no, $date_time, $state, $work_cnt, $siji_no, $koutei)
    {
        if ( ($state == 1) || ($state == 8) || ($state == 5) ) {    // ��ư��ž��̵�ͱ�ž�����ʼ���ΤϤ��ʤΤǥ����å�����ɬ�פʤ�����
            $mac_state = $state;
        } else {
            // Counter���ʤ�Ǥ���ΤǼ�ư����̵�ͤ˶���Ū�����ꤹ��
            if ($this->getRotaryStateHistory($mac_no, $date_time) == 8) {
                $mac_state = 8;
            } else {
                $mac_state = 1;
            }
        }
        // ��ʣ�����å�
        $query = "
            SELECT work_cnt FROM equip_work_log2 WHERE date_time='{$date_time}' AND mac_no={$mac_no} AND mac_state={$mac_state}
        ";
        if (getUniResult($query, $check) < 1) {
            $sql = "
                INSERT INTO
                    equip_work_log2
                (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                VALUES($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)
            ";
        } else {
            $sql = "
                UPDATE equip_work_log2 SET work_cnt={$work_cnt}
                WHERE date_time='{$date_time}' AND mac_no={$mac_no} AND mac_state={$mac_state}
            ";
            $this->equip_log_writer("��������ʣ���Ƥ��뤿��UPDATE���ޤ���{$query}{$sql}");
        }
        return $sql;
    }
    
    ///// �ù����ν���� SQLʸ������ FTP��˥ǡ�������ξ�� �����᥽�å�
    protected function getSQLworkCntInsert_moni($mac_no, $date_time, $state, $work_cnt, $plan_no, $koutei)
    {
        if ( ($state == 1) || ($state == 8) || ($state == 5) ) {    // ��ư��ž��̵�ͱ�ž�����ʼ���ΤϤ��ʤΤǥ����å�����ɬ�פʤ�����
            $mac_state = $state;
        } else {
            // Counter���ʤ�Ǥ���ΤǼ�ư����̵�ͤ˶���Ū�����ꤹ��
            if ($this->getRotaryStateHistory($mac_no, $date_time) == 8) {
                $mac_state = 8;
            } else {
                $mac_state = 1;
            }
        }
        // ��ʣ�����å�
        $query = "
            SELECT work_cnt FROM equip_work_log2_moni WHERE date_time='{$date_time}' AND mac_no={$mac_no} AND mac_state={$mac_state}
        ";
        if (getUniResult($query, $check) < 1) {
            $sql = "
                INSERT INTO
                    equip_work_log2_moni
                (mac_no, date_time, mac_state, work_cnt, plan_no, koutei)
                VALUES($mac_no, '$date_time', $mac_state, $work_cnt, '$plan_no', $koutei)
            ";
        } else {
            $sql = "
                UPDATE equip_work_log2_moni SET work_cnt={$work_cnt}
                WHERE date_time='{$date_time}' AND mac_no={$mac_no} AND mac_state={$mac_state}
            ";
            $this->equip_log_writer("��������ʣ���Ƥ��뤿��UPDATE���ޤ���{$query}{$sql}");
        }
        return $sql;
    }
    ///// ���μ��� �ù����ν���� FTP��˥ǡ����ʤ��ξ�� �����᥽�å�
    protected function equip_log_workCnt_db_write($res_key)
    {
        ///// insert �� �ѿ� �����
        $mac_no   = $res_key['mac_no'];
        $siji_no  = $res_key['siji_no'];
        $koutei   = $res_key['koutei'];
        $date_t1  = date('Ymd');
        $date_t2  = date('His');
        ///// ���֥ǡ����ϻ���������DB�����Ф���
        $state = $this->getLogicalState($mac_no, $date_t1, $date_t2);
        ///// ���֤ϻ���������DB�����Ф���
        $date_time = $this->getLogicalTime($mac_no, $date_t1, $date_t2);
        ///// ���ߤΥǡ����١����κǿ��쥳���ɤ������
        $query = "
            SELECT mac_state, work_cnt, date_time FROM equip_work_log2
            WHERE
                equip_index(mac_no, siji_no, koutei, date_time) < '{$mac_no}{$siji_no}{$koutei}99999999999999'
                AND
                equip_index(mac_no, siji_no, koutei, date_time) > '{$mac_no}{$siji_no}{$koutei}00000000000000'
            ORDER BY equip_index(mac_no, siji_no, koutei, date_time) DESC LIMIT 1
        ";
        $res = array();
        if ($date_time != '') {
            if ( ($rows = getResult($query, $res)) >= 1) {  // �쥳���ɤ�����о��֤�����å�����
                ///// ��ߤ�����ޥ�����������å����ƾ��֤��㤨�н����
                if ($this->checkStopTime($res[0]['mac_state'], $res[0]['date_time'], $state, $res_key, $date_time)) {
                    $work_cnt  = $res[0]['work_cnt'];       // ����βù����򤽤Τޤ޻Ȥ�
                    //$date_time  = date('Ymd His');          // ���ߤλ��֤�Ȥ� PostgreSQL��TIMESTAMP�����ѹ�
                    $mac_state = $state;
                    // ��ʣ�����å�
                    $query = "
                        SELECT work_cnt FROM equip_work_log2 WHERE date_time='{$date_time}' AND mac_no={$mac_no} AND mac_state={$mac_state}
                    ";
                    if (getUniResult($query, $check) < 1) {
                        $query = "
                            INSERT INTO
                                equip_work_log2
                            (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                            VALUES($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)
                        ";
                        if (query_affected($query) <= 0) {
                            $this->equip_log_writer("�����Ѳ�����insert error{$query}");
                        }
                    }
                }
            } else {        // ���Τ���̵���˽����
                $work_cnt  = 0;             // ���ξ��ϣ�
                //$date_time = date('Ymd His');       // ���ߤλ��֤�Ȥ� PostgreSQL��TIMESTAMP�����ѹ�
                $mac_state = $state;
                $query = "
                    SELECT work_cnt FROM equip_work_log2 WHERE date_time='{$date_time}' AND mac_no={$mac_no} AND mac_state={$mac_state}
                ";
                $res = array();
                if (getUniResult($query, $check) < 1) {
                    $query = "
                        INSERT INTO
                            equip_work_log2
                        (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                        VALUES($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)
                    ";
                    if (query_affected($query) <= 0) {
                        $this->equip_log_writer("1���ξ����Ѳ�����insert error{$query}");
                    } else {
                        ///// ���μ��� ���βù����ν���ߤ˥إå����ȥ�������������å����ƹ���
                        $msg = 'CounterFile���ʤ����ν��ǡ�����������Ӥ�';
                        $this->equip_log_workCnt_header_write($res_key, $date_time, $msg);
                    }
                } else {
                    $query = "
                        UPDATE
                            equip_work_log2
                        SET work_cnt={$work_cnt}, siji_no={$siji_no}, koutei={$koutei}
                        WHERE
                        mac_no={$mac_no} and date_time='{$date_time}' and mac_state={$mac_state}
                    ";
                    if (query_affected($query) <= 0) {
                        $this->equip_log_writer("���ξ����Ѳ�����update error{$query}");
                    } else {
                        ///// ���μ��� ���βù����ν���ߤ˥إå����ȥ�������������å����ƹ���
                        $msg = 'CounterFile���ʤ����ν��ǡ�����������Ӥ�';
                        $this->equip_log_workCnt_header_write($res_key, $date_time, $msg);
                    }
                }
            }
        }
    }
    
    ///// ���μ��� �ù����ν���� FTP��˥ǡ����ʤ��ξ�� �����᥽�å�
    protected function equip_log_workCnt_db_write_moni($res_key)
    {
        ///// insert �� �ѿ� �����
        $mac_no   = $res_key['mac_no'];
        $plan_no  = $res_key['plan_no'];
        $koutei   = $res_key['koutei'];
        $date_t1  = date('Ymd');
        $date_t2  = date('His');
        ///// ���֥ǡ����ϻ���������DB�����Ф���
        $state = $this->getLogicalState($mac_no, $date_t1, $date_t2);
        ///// ���֤ϻ���������DB�����Ф���
        $date_time = $this->getLogicalTime($mac_no, $date_t1, $date_t2);
        ///// ���ߤΥǡ����١����κǿ��쥳���ɤ������
        $query = "
            SELECT mac_state, work_cnt, date_time FROM equip_work_log2_moni
            WHERE
                equip_index_moni(mac_no, plan_no, koutei, date_time) < '{$mac_no}{$plan_no}{$koutei}99999999999999'
                AND
                equip_index_moni(mac_no, plan_no, koutei, date_time) > '{$mac_no}{$plan_no}{$koutei}00000000000000'
            ORDER BY equip_index_moni(mac_no, plan_no, koutei, date_time) DESC LIMIT 1
        ";
        $res = array();
        if ($date_time != '') {
            if ( ($rows = getResult($query, $res)) >= 1) {  // �쥳���ɤ�����о��֤�����å�����
                ///// ��ߤ�����ޥ�����������å����ƾ��֤��㤨�н����
                if ($this->checkStopTime($res[0]['mac_state'], $res[0]['date_time'], $state, $res_key, $date_time)) {
                    $work_cnt  = $res[0]['work_cnt'];       // ����βù����򤽤Τޤ޻Ȥ�
                    //$date_time  = date('Ymd His');          // ���ߤλ��֤�Ȥ� PostgreSQL��TIMESTAMP�����ѹ�
                    $mac_state = $state;
                    // ��ʣ�����å�
                    $query = "
                        SELECT work_cnt FROM equip_work_log2_moni WHERE date_time='{$date_time}' AND mac_no={$mac_no} AND mac_state={$mac_state}
                    ";
                    if (getUniResult($query, $check) < 1) {
                        $query = "
                           INSERT INTO
                                equip_work_log2_moni
                            (mac_no, date_time, mac_state, work_cnt, plan_no, koutei)
                            VALUES($mac_no, '$date_time', $mac_state, $work_cnt, '$plan_no', $koutei)
                        ";
                        if (query_affected($query) <= 0) {
                            $this->equip_log_writer("�����Ѳ�����insert error{$query}");
                        }
                    }
                }
            } else {        // ���Τ���̵���˽����
                $work_cnt  = 0;             // ���ξ��ϣ�
                //$date_time = date('Ymd His');       // ���ߤλ��֤�Ȥ� PostgreSQL��TIMESTAMP�����ѹ�
                $mac_state = $state;
                $query = "
                    INSERT INTO
                        equip_work_log2_moni
                    (mac_no, date_time, mac_state, work_cnt, plan_no, koutei)
                    VALUES($mac_no, '$date_time', $mac_state, $work_cnt, '$plan_no', $koutei)
                ";
                if (query_affected($query) <= 0) {
                    $this->equip_log_writer("2���ξ����Ѳ�����insert error{$query}");
                } else {
                    ///// ���μ��� ���βù����ν���ߤ˥إå����ȥ�������������å����ƹ���
                    $msg = 'CounterFile���ʤ����ν��ǡ�����������Ӥ�';
                    $this->equip_log_workCnt_header_write_moni($res_key, $date_time, $msg);
                }
            }
        }
    }
    ///// ���μ��� ���βù����ν���ߤ˥إå����ȥ�������������å����ƹ��� �����᥽�å�
    protected function equip_log_workCnt_header_write($res_key, $date_time, $msg)
    {
        ///// UPDATE �� �ѿ� �����
        $mac_no   = $res_key['mac_no'];
        $siji_no  = $res_key['siji_no'];
        $koutei   = $res_key['koutei'];
        $query = "
            SELECT
                str_timestamp
            FROM
                equip_work_log2_header
            WHERE
                mac_no={$mac_no} AND siji_no={$siji_no} AND koutei={$koutei}
        ";
        if (getUniResult($query, $str_timestamp) > 0) {
            $query = "
                SELECT
                    CASE
                        WHEN CAST('$date_time' AS TIMESTAMP) < CAST('$str_timestamp' AS TIMESTAMP)
                        THEN 1
                        ELSE 0
                    END
            ";
            if (getUniResult($query, $check_time) > 0 && $check_time == 1) {
                $query = "
                    UPDATE
                        equip_work_log2_header
                    SET
                        str_timestamp='{$date_time}'
                    WHERE
                    mac_no={$mac_no} AND siji_no={$siji_no} AND koutei={$koutei}
                ";
                if (query_affected($query) <= 0) {
                    $this->equip_log_writer("{$msg}Header��UPDATE error{$query}");
                }
            }
        }
    }
    
    ///// ���μ��� ���βù����ν���ߤ˥إå����ȥ�������������å����ƹ��� �����᥽�å�
    protected function equip_log_workCnt_header_write_moni($res_key, $date_time, $msg)
    {
        ///// UPDATE �� �ѿ� �����
        $mac_no   = $res_key['mac_no'];
        $plan_no  = $res_key['plan_no'];
        $koutei   = $res_key['koutei'];
        $query = "
            SELECT
                str_timestamp
            FROM
                equip_work_log2_header_moni
            WHERE
                mac_no={$mac_no} AND plan_no='{$plan_no}' AND koutei={$koutei}
        ";
        if (getUniResult($query, $str_timestamp) > 0) {
            $query = "
                SELECT
                    CASE
                        WHEN CAST('$date_time' AS TIMESTAMP) < CAST('$str_timestamp' AS TIMESTAMP)
                        THEN 1
                        ELSE 0
                    END
            ";
            if (getUniResult($query, $check_time) > 0 && $check_time == 1) {
                $query = "
                    UPDATE
                        equip_work_log2_header_moni
                    SET
                        str_timestamp='{$date_time}'
                    WHERE
                    mac_no={$mac_no} AND plan_no='{$plan_no}' AND koutei={$koutei}
                ";
                if (query_affected($query) <= 0) {
                    $this->equip_log_writer("{$msg}Header��UPDATE error{$query}");
                }
            }
        }
    }
    ///// �����꡼�����å��Υǡ��������᥽�å�
    protected function getRotaryStateCurrent($ftp_con, $mac_no)
    {
        ///// ���˼¹Ԥ��Ƥ��뤫
        if ($this->mac_no == $mac_no) {
            return $this->rotaryState;
        }
        ///// �ե�����̾������
        $query = "
            SELECT
                interface
            FROM
                equip_machine_interface
            WHERE
                mac_no = {$mac_no}
        ";
        $ftp_no = 0;
        getUniResult($query, $ftp_no);
        /*
        if ($ftp_no == 2) {
            $fws_bcd1 = "/MMC/Plc_Work/{$mac_no}-bcd1";
            $fws_bcd2 = "/MMC/Plc_Work/{$mac_no}-bcd2";
            $fws_bcd4 = "/MMC/Plc_Work/{$mac_no}-bcd4";
            $fws_bcd8 = "/MMC/Plc_Work/{$mac_no}-bcd8";
            $local_bcd1 = "/home/fws/{$mac_no}-bcd1";
            $local_bcd2 = "/home/fws/{$mac_no}-bcd2";
            $local_bcd4 = "/home/fws/{$mac_no}-bcd4";
            $local_bcd8 = "/home/fws/{$mac_no}-bcd8";
        } else {
            $fws_bcd1 = "/home/fws/usr/{$mac_no}-bcd1";
            $fws_bcd2 = "/home/fws/usr/{$mac_no}-bcd2";
            $fws_bcd4 = "/home/fws/usr/{$mac_no}-bcd4";
            $fws_bcd8 = "/home/fws/usr/{$mac_no}-bcd8";
            $local_bcd1 = "/home/fws/{$mac_no}-bcd1";
            $local_bcd2 = "/home/fws/{$mac_no}-bcd2";
            $local_bcd4 = "/home/fws/{$mac_no}-bcd4";
            $local_bcd8 = "/home/fws/{$mac_no}-bcd8";
        }
        */
        // FWS2���ؽ��� ���PGM�������ؤ�
        if ($ftp_no == 2) {
            $fws_bcd1 = "/MMC/Plc_Work/{$mac_no}-bcd1";
            $fws_bcd2 = "/MMC/Plc_Work/{$mac_no}-bcd2";
            $fws_bcd4 = "/MMC/Plc_Work/{$mac_no}-bcd4";
            $fws_bcd8 = "/MMC/Plc_Work/{$mac_no}-bcd8";
            $local_bcd1 = "/home/fws/{$mac_no}-bcd1";
            $local_bcd2 = "/home/fws/{$mac_no}-bcd2";
            $local_bcd4 = "/home/fws/{$mac_no}-bcd4";
            $local_bcd8 = "/home/fws/{$mac_no}-bcd8";
        } elseif ($ftp_no == 3) {
            $fws_bcd1 = "/0_CARD/Plc_Work/{$mac_no}-bcd1";
            $fws_bcd2 = "/0_CARD/Plc_Work/{$mac_no}-bcd2";
            $fws_bcd4 = "/0_CARD/Plc_Work/{$mac_no}-bcd4";
            $fws_bcd8 = "/0_CARD/Plc_Work/{$mac_no}-bcd8";
            $local_bcd1 = "/home/fws/{$mac_no}-bcd1";
            $local_bcd2 = "/home/fws/{$mac_no}-bcd2";
            $local_bcd4 = "/home/fws/{$mac_no}-bcd4";
            $local_bcd8 = "/home/fws/{$mac_no}-bcd8";
        } elseif ($ftp_no == 4) {
            $fws_bcd1 = "/0_CARD/Plc_Work/{$mac_no}-bcd1";
            $fws_bcd2 = "/0_CARD/Plc_Work/{$mac_no}-bcd2";
            $fws_bcd4 = "/0_CARD/Plc_Work/{$mac_no}-bcd4";
            $fws_bcd8 = "/0_CARD/Plc_Work/{$mac_no}-bcd8";
            $local_bcd1 = "/home/fws/{$mac_no}-bcd1";
            $local_bcd2 = "/home/fws/{$mac_no}-bcd2";
            $local_bcd4 = "/home/fws/{$mac_no}-bcd4";
            $local_bcd8 = "/home/fws/{$mac_no}-bcd8";
        } elseif ($ftp_no ==7 || $ftp_no ==8) {
            $fws_bcd1 = "/0_CARD/Plc_Work/{$mac_no}-bcd1";
            $fws_bcd2 = "/0_CARD/Plc_Work/{$mac_no}-bcd2";
            $fws_bcd4 = "/0_CARD/Plc_Work/{$mac_no}-bcd4";
            $fws_bcd8 = "/0_CARD/Plc_Work/{$mac_no}-bcd8";
            $local_bcd1 = "/home/fws/{$mac_no}-bcd1";
            $local_bcd2 = "/home/fws/{$mac_no}-bcd2";
            $local_bcd4 = "/home/fws/{$mac_no}-bcd4";
            $local_bcd8 = "/home/fws/{$mac_no}-bcd8";
        } else {
            $fws_bcd1 = "/home/fws/usr/{$mac_no}-bcd1";
            $fws_bcd2 = "/home/fws/usr/{$mac_no}-bcd2";
            $fws_bcd4 = "/home/fws/usr/{$mac_no}-bcd4";
            $fws_bcd8 = "/home/fws/usr/{$mac_no}-bcd8";
            $local_bcd1 = "/home/fws/{$mac_no}-bcd1";
            $local_bcd2 = "/home/fws/{$mac_no}-bcd2";
            $local_bcd4 = "/home/fws/{$mac_no}-bcd4";
            $local_bcd8 = "/home/fws/{$mac_no}-bcd8";
        }
        $this->getRotaryStateBody($ftp_con, $mac_no, $fws_bcd1, $local_bcd1);
        $this->getRotaryStateBody($ftp_con, $mac_no, $fws_bcd2, $local_bcd2);
        $this->getRotaryStateBody($ftp_con, $mac_no, $fws_bcd4, $local_bcd4);
        $this->getRotaryStateBody($ftp_con, $mac_no, $fws_bcd8, $local_bcd8);
        
        ///// State File Check BCD�黻  ���ߤξ��֤����
        $state_bcd = 0;                                     // �����
        if (file_exists($local_bcd1)) {
            $state_bcd += 1;
            $this->setRotaryStateCurrentBCD($mac_no, 'BCD1', 1);
        } else {
            $this->setRotaryStateCurrentBCD($mac_no, 'BCD1', 0);
        }
        if (file_exists($local_bcd2)) {
            $state_bcd += 2;
            $this->setRotaryStateCurrentBCD($mac_no, 'BCD2', 2);
        } else {
            $this->setRotaryStateCurrentBCD($mac_no, 'BCD2', 0);
        }
        if (file_exists($local_bcd4)) {
            $state_bcd += 4;
            $this->setRotaryStateCurrentBCD($mac_no, 'BCD4', 4);
        } else {
            $this->setRotaryStateCurrentBCD($mac_no, 'BCD4', 0);
        }
        if (file_exists($local_bcd8)) {
            $state_bcd += 8;
            $this->setRotaryStateCurrentBCD($mac_no, 'BCD8', 8);
        } else {
            $this->setRotaryStateCurrentBCD($mac_no, 'BCD8', 0);
        }
        ///// ���֤��ƽФ��������б�
        $this->mac_no = $mac_no;
        $this->rotaryState = $state_bcd;
        return $state_bcd;
    }
    ///// �����꡼�����å��Υǡ������� ���� �᥽�å�
    protected function getRotaryStateBody($ftp_con, $mac_no, $fws_file, $local_file)
    {
        ///// �ե�����̾������
        $query = "
            SELECT
                interface
            FROM
                equip_machine_interface
            WHERE
                mac_no = {$mac_no}
        ";
        $ftp_no = 0;
        getUniResult($query, $ftp_no);
        if ($ftp_no == 2) {
            if (!@ftp_get($ftp_con, $local_file, $fws_file, FTP_ASCII)) {
                if (file_exists($local_file)) {   // ��ե����뤬����к��
                    unlink($local_file);
                }
            }
        // FWS2 ���ؽ��� �����Ȳ������PGM�ɲ�
        } elseif ($ftp_no == 3) {
            if (!@ftp_get($ftp_con, $local_file, $fws_file, FTP_ASCII)) {
                if (file_exists($local_file)) {   // ��ե����뤬����к��
                    unlink($local_file);
                }
            }
        // FWS2 ���ؽ��� �����Ȳ������PGM�ɲ�
        } elseif ($ftp_no == 4) {
            if (!@ftp_get($ftp_con, $local_file, $fws_file, FTP_ASCII)) {
                if (file_exists($local_file)) {   // ��ե����뤬����к��
                    unlink($local_file);
                }
            }
        } elseif ($ftp_no ==7 || $ftp_no ==8) {
            if (!@ftp_get($ftp_con, $local_file, $fws_file, FTP_ASCII)) {
                if (file_exists($local_file)) {   // ��ե����뤬����к��
                    unlink($local_file);
                }
            }
        } else {
            /////////// FTP��Υե������¸�ߥ����å�
            if (ftp_size($ftp_con, $fws_file) != -1) {
                /////////// FTP Download
                if (!@ftp_get($ftp_con, $local_file, $fws_file, FTP_ASCII)) {
                    $this->equip_log_writer("{$fws_file}��FTP��Download�˼��Ԥ��ޤ�����");
                    $this->getRotaryStateRetry($ftp_con, $mac_no, $fws_file, $local_file);
                }
            } else {
                if (file_exists($local_file)) {   // ��ե����뤬����к��
                    unlink($local_file);
                }
            }
        }
    }
    ///// �����꡼�����å��Υǡ������� �ƻ�� �᥽�å�
    protected function getRotaryStateRetry($ftp_con, $mac_no, $fws_file, $local_file)
    {
        // $this->equip_log_writer("{$fws_file}��FTP��Download��ƻ�Ԥ��ޤ���");
        if (!@ftp_get($ftp_con, $local_file, $fws_file, FTP_ASCII)) {
            $query = "
                SELECT
                interface
                FROM
                equip_machine_interface
                WHERE
                mac_no = {$mac_no}
            ";
            $ftp_no = 0;
            getUniResult($query, $ftp_no);
            if ($ftp_no == 2) {
            } elseif ($ftp_no == 3) {
            } elseif ($ftp_no == 4) {
            } elseif ($ftp_no == 7 || $ftp_no == 8) {
            } else {
                $this->equip_log_writer("{$fws_file}��FTP��Download�κƻ�ԤǼ��Ԥ��ޤ�����");
            }   
        } else {
            $this->equip_log_writer("{$fws_file}��FTP��Download�κƻ�Ԥ��������ޤ�����");
        }
    }
    
    ///// �����꡼�����å��κǿ��ǡ�������᥽�å�
    protected function setRotaryStateCurrentBCD($mac_no, $bcd, $state, $date_time='')
    {
        $bcd = strtoupper($bcd);
        if ($date_time == '') {
            $date_time = date('Y-m-d H:i:s');
            $msg = '�����å���';
        } else {
            $msg = '�������߻�';
        }
        $query = "SELECT state FROM equip_mac_state_bcd WHERE mac_no={$mac_no} AND bcd='{$bcd}'";
        if (getUniResult($query, $res) < 1) {
            $sql = "
                INSERT INTO equip_mac_state_bcd (mac_no, bcd, state, date_time)
                VALUES({$mac_no}, '{$bcd}', {$state}, '{$date_time}')
            ";
            if (query_affected($sql) <= 0) {
                $this->equip_log_writer("{$mac_no} {$msg}�Υ�����BCD��insert error{$sql}");
            } else {
                $this->equip_log_writer("{$mac_no} {$msg}�˥����� {$bcd} �� {$state} �����ꤷ�ޤ�����");
            }
        } else {
            if ($state != $res) {
                $sql = "
                    UPDATE equip_mac_state_bcd SET state={$state}, date_time='{$date_time}'
                    WHERE mac_no={$mac_no} AND bcd='{$bcd}'
                ";
                if (query_affected($sql) <= 0) {
                    $this->equip_log_writer("{$mac_no} {$msg}�Υ�����BCD��update error{$sql}");
                } else {
                    $this->equip_log_writer("{$mac_no} {$msg}�˥����� {$bcd} �� {$state} ���ѹ����ޤ�����");
                }
            }
        }
    }
    ///// �����꡼�����å� ��BCD�θ��ߥǡ��������᥽�å�
    protected function getRotaryStateCurrentBCD($mac_no, &$bcd1, &$bcd2, &$bcd4, &$bcd8)
    {
        $query = "SELECT state FROM equip_mac_state_bcd WHERE mac_no={$mac_no} ORDER BY mac_no ASC, bcd ASC";
        if (getResult2($query, $res) < 1) {
            $bcd1 = 0; $bcd2 = 0; $bcd4 = 0; $bcd8 = 0;
            $this->equip_log_writer("{$mac_no} �Υ�����BCD�Υǡ�����̵������ 0 �ǽ�������ޤ�����");
        } else {
            $bcd1 = $res[0][0]; $bcd2 = $res[1][0]; $bcd4 = $res[2][0]; $bcd8 = $res[3][0];
        }
    }
    
    ///// �����꡼�����å�������ǡ��������᥽�å�
    protected function getRotaryStateHistory($mac_no, $date_time)
    {
        $query = "
            SELECT
                state
            FROM
                equip_mac_state_log3
            WHERE
                mac_no = {$mac_no} AND date_time <= TIMESTAMP '{$date_time}'
                AND
                mac_no = {$mac_no} AND date_time >= TIMESTAMP '2000-10-01 08:30:00'
            ORDER BY
                mac_no DESC, date_time DESC
            LIMIT 1
        ";
        if (getUniResult($query, $state_r) < 1) {
            // ���ϥ����꡼�����å����󤵤�Ƥ��ʤ����Ἣư��ž�ȸ��ʤ�
            $state_r = 1;
        }
        return $state_r;
    }
    
    /////// ʪ������ǥ����꡼�����å���Ŭ����Ƚ�Ǥ��������ֹ���֤�
    /////// �ʲ��ϥϡ��ɿ��椬���Τ˽��Ϥ���Ƥ����������Ȥ���
    protected function equip_state_check($state_p, $state_bcd)
    {
        if ($state_p == 1) {            // ��ž��(ʪ������)
            switch ($state_bcd) {
            case (1):                   // ��ư��ž
                return(1);
                break;
            case (4):                   // �ȵ���
                return(4);
                break;
            case (5):                   // �ʼ���
                return(5);
                break;
            case (8):                   // ̵�ͱ�ž
                return(8);
                break;
            default:                    // ����¾�ϼ�ư��ž
                return(1);
            }
        } elseif ($state_p == 3) {      // �����(ʪ������)
            switch ($state_bcd) {
            case (3):                   // �����
                return(3);
                break;
            case (2):                   // ���顼��(ʪ�����椬̵����������꡼�����å��Ǽ��)
                return(2);
                break;
            case (4):                   // �ȵ��� 2007/07/11 (���2006/06/12�ɲä����Τ�����Ƥ���)
                return(4);
                break;
            case (5):                   // �ʼ���
                return(5);
                break;
            case (6):                   // �ξ㽤��
                return(6);
                break;
            case (7):                   // �϶��
                return(7);
                break;
            case (9):                   // ����
                return(9);
                break;
            default:                    // ����¾�������
                return(3);
            }
        } elseif ($state_p == 2) {      // ���顼��(ʪ������)���ߤޤ�����Ϥʤ�(ͽ��)
            switch ($state_bcd) {
            case (2):                   // ���顼��
                return(2);
                break;
            case (5):                   // �ʼ���
                return(5);
                break;
            case (6):                   // �ξ㽤��
                return(6);
                break;
            case (7):                   // �϶��
                return(7);
                break;
            default:
                return(3);
            }
        } else {                        // �Ÿ�OFF(ʪ������)
            switch ($state_bcd) {
            case (5):                   // �ʼ���
                return(5);
                break;
            case (6):                   // �ξ㽤��
                return(6);
                break;
            default:                    // ����¾���Ÿ�OFF
                return(0);
            }
        }
    }
    ///// ����Υǡ����Ȱ㤦�������å� equip_mac_state_log equip_mac_state_log2 ���о�
    protected function equip_log_require($mac_no, $state, $date_time, $flg)
    {
        if ($flg == 1) {
            if ($this->getLogicalSame($mac_no, $state, $date_time)) {
                return FALSE;
            }
            $state_pre = $this->getLogicalState($mac_no);
            if ($state_pre == $state) {
                return FALSE;
            } else {
                $time_pre = $this->getLogicalTime($mac_no);
                if ($time_pre == $date_time) {
                    return FALSE;
                } else {
                    return TRUE;
                }
                return TRUE;
            }
        } elseif ($flg == 2) {
            if ($this->getPhysicalSame($mac_no, $state, $date_time)) {
                return FALSE;
            }
            $state_pre = $this->getPhysicalState($mac_no);
            if ($state_pre == $state) {
                return FALSE;
            } else {
                $time_pre = $this->getPhysicalTime($mac_no);
                if ($time_pre == $date_time) {
                    return FALSE;
                } else {
                    return TRUE;
                }
                return TRUE;
            }
        } else {
            return FALSE;
        }
    }
    ///// �����󥿡��ޥ������μ��� �����󥿡���Ψ���֤�
    protected function getCounterMaster($mac_no, $parts_no)
    {
        $query = "
            SELECT count FROM equip_count_master WHERE mac_no={$mac_no} AND parts_no='{$parts_no}'
        ";
        if (getUniResult($query, $count) > 0) {
            return $count;
        }
        $query = "
            SELECT count FROM equip_count_master WHERE mac_no={$mac_no} AND parts_no='000000000'
        ";
        if (getUniResult($query, $count) > 0) {
            return $count;
        } else {
            return 1;
        }
    }
    ///// ��ߤ�����ޥ������μ��� ��ߤ�Ƚ�Ǥ����ÿ����֤�
    protected function getStopMaster($mac_no, $parts_no)
    {
        $query = "
            SELECT stop FROM equip_stop_master WHERE mac_no={$mac_no} AND parts_no='{$parts_no}'
        ";
        if (getUniResult($query, $stop) > 0) {
            return $stop;
        }
        $query = "
            SELECT stop FROM equip_stop_master WHERE mac_no={$mac_no} AND parts_no='000000000'
        ";
        if (getUniResult($query, $stop) > 0) {
            return $stop;
        } else {
            return 1;
        }
    }
    ///// ����ξ��֤Ȱ㤨��true ��äƤ��Ƥ����=3�λ��ϥޥ������Υ����å���Ԥ�
    protected function checkStopTime($db_state, $date_time, $state, $res_key, $in_time)
    {
        if ($db_state == $state) {
            return false;   // ���֤����ޤʤ�
        } elseif ($state != 3 && $db_state != $state) {
            return true;    // ���֤�����
        }
        $mac_no   = $res_key['mac_no'];
        $parts_no = $res_key['parts_no'];
        $stop = $this->getStopMaster($mac_no, $parts_no);
        $query = "
            SELECT (TIMESTAMP '{$in_time}' - TIMESTAMP '{$date_time}') >= INTERVAL '{$stop} second' AS stop_flg
        ";
        $check = 't';
        getUniResult($query, $check);
        if ($check == 't') return true; else return false;
    }
    ///// ������ʪ�����֥ǡ�����DB������ �᥽�å�
    protected function getPhysicalState($mac_no, $date='99999999', $time='999999')
    {
        $date = str_replace('-', '', $date);
        $time = str_replace(':', '', $time);
        // ʪ�������DB������
        $query = "
            SELECT
                state
            FROM
                equip_mac_state_log2
            WHERE
                equip_index2(mac_no, date_time) <= to_char({$mac_no}{$date}{$time}, 'FM99999999/99/99 99:99:99')
                AND
                equip_index2(mac_no, date_time) >= to_char({$mac_no}00000000000000, 'FM99999999/99/99 99:99:99')
            ORDER BY
                equip_index2(mac_no, date_time) DESC
            LIMIT 1
        ";
        if (getUniResult($query, $state_p) < 1) {
            // ���Τ����Ÿ�OFF�Ȥߤʤ�
            $state_p = 0;
        }
        return $state_p;
    }
    ///// ������ʪ�����֥ǡ�����DB������ �᥽�å�
    protected function getPhysicalTime($mac_no, $date='99999999', $time='999999')
    {
        $date = str_replace('-', '', $date);
        $time = str_replace(':', '', $time);
        // ʪ�������DB������
        $query = "
            SELECT
                date_time
            FROM
                equip_mac_state_log2
            WHERE
                equip_index2(mac_no, date_time) <= to_char({$mac_no}{$date}{$time}, 'FM99999999/99/99 99:99:99')
                AND
                equip_index2(mac_no, date_time) >= to_char({$mac_no}00000000000000, 'FM99999999/99/99 99:99:99')
            ORDER BY
                equip_index2(mac_no, date_time) DESC
            LIMIT 1
        ";
        if (getUniResult($query, $state_p) < 1) {
            // ���Τ����Ÿ�OFF�Ȥߤʤ�
            $state_p = 0;
        }
        return $state_p;
    }
    ///// ������ʪ�����֥ǡ�����DB������ �᥽�å�
    protected function getPhysicalSame($mac_no, $state, $date_time)
    {
        // ʪ�������DB������
        $query = "
            SELECT
                mac_no
            FROM
                equip_mac_state_log2
            WHERE
                mac_no = {$mac_no}
                AND
                state = {$state}
                AND
                date_time = TIMESTAMP '{$date_time}'
        ";
        if (getUniResult($query, $mac) < 1) {
            // ���Τ����Ÿ�OFF�Ȥߤʤ�
            return FALSE;
        }
        return TRUE;
    }
    ///// �������֥ǡ�������� (ʪ������ȥ����꡼�����å���Ŭ�������å���Ԥä��ǡ������оݤȤ���)
    protected function getLogicalState($mac_no, $date='99999999', $time='999999')
    {
        $date = str_replace('-', '', $date);
        $time = str_replace(':', '', $time);
        $query = "
            SELECT
                state
            FROM
                equip_mac_state_log
            WHERE
                equip_index2(mac_no, date_time) <= to_char({$mac_no}{$date}{$time}, 'FM99999999/99/99 99:99:99')
                AND
                equip_index2(mac_no, date_time) >= to_char({$mac_no}00000000000000, 'FM99999999/99/99 99:99:99')
            ORDER BY
                equip_index2(mac_no, date_time) DESC
            LIMIT 1
        ";
        if (getUniResult($query, $state) < 1) {
            $state = 0;     // ���֥ǡ�����̵���Τ�̵�����Ÿ�off=0
        }
        return $state;
    }
    ///// �������֥ǡ�������� (ʪ������ȥ����꡼�����å���Ŭ�������å���Ԥä��ǡ������оݤȤ���)
    protected function getLogicalTime($mac_no, $date='99999999', $time='999999')
    {
        $date = str_replace('-', '', $date);
        $time = str_replace(':', '', $time);
        $query = "
            SELECT
                date_time
            FROM
                equip_mac_state_log
            WHERE
                equip_index2(mac_no, date_time) <= to_char({$mac_no}{$date}{$time}, 'FM99999999/99/99 99:99:99')
                AND
                equip_index2(mac_no, date_time) >= to_char({$mac_no}00000000000000, 'FM99999999/99/99 99:99:99')
            ORDER BY
                equip_index2(mac_no, date_time) DESC
            LIMIT 1
        ";
        if (getUniResult($query, $state) < 1) {
            $state = 0;     // ���֥ǡ�����̵���Τ�̵�����Ÿ�off=0
        }
        return $state;
    }
    ///// ������ʪ�����֥ǡ�����DB������ �᥽�å�
    protected function getLogicalSame($mac_no, $state, $date_time)
    {
        // ʪ�������DB������
        $query = "
            SELECT
                mac_no
            FROM
                equip_mac_state_log
            WHERE
                mac_no = {$mac_no}
                AND
                state = {$state}
                AND
                date_time = TIMESTAMP '{$date_time}'
        ";
        if (getUniResult($query, $mac) < 1) {
            // ���Τ����Ÿ�OFF�Ȥߤʤ�
            return FALSE;
        }
        return TRUE;
    }
} // class EquipAutoLog End

?>
