<?php
//////////////////////////////////////////////////////////////////////////////
// ������Ư������ ��ž���� �б��� Graph Create Class Report                 //
// Copyright (C) 2004-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/08/23 Created   EquipGraphClass_report.php                          //
//            jpGraph-1.16 base created  modify jpgraph_equip.php           //
//            function SetTextLabelInterval($aStep)                         //
//                                  $graph->xaxis->SetTextLabelInterval(2); //
//            function SetTextTickInterval($aStep,$aStart)                  //
//                                 $graph->xaxis->SetTextTickInterval(1,2); //
//            �ǽ�Ū��SetTextTickInterval(Step��, Start����)����Ѥ���      //
//            EquipGraphClass_report.php  based create.                     //
// 2004/08/23 jpgraph.php -> jpgraph_equip.php ���ѹ�����Y����(-)ɽ�����   //
// 2004/08/26 ľ����work_cnt��max_work_cnt�����($max_work_cnt < $res[0][1])//
//    Ver1.01 yaxis_min()��Ϳ����ǡ�������ꤹ��褦���ѹ�                 //
// 2004/08/30 2�ڡ����ܹԤ�offset�������ɲ�(�Х�����)                       //
//    Ver1.02 $xdata += ($this->xtime * ($this->graph_page - 1))            //
//            ����End�ȥ����End����Ӥ���max_work_cnt����ꤹ��褦���ѹ�  //
// 2004/09/01 Ver1.03 out_graph()�˥��ץ����ǥ��ޥ꡼��̤����ൡǽ�ɲ� //
// 2005/05/20 db_connect() �� funcConnect() ���ѹ� pgsql.php������Τ���    //
// 2005/08/30 php5 �ذܹ�  Ver1.04 (=& new �� = new, var �� private)        //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// require_once ('../function.php');
require_once ('equip_function.php');
require_once ('/home/www/html/jpgraph_equip.php');
require_once ('/home/www/html/jpgraph_line.php');

if (class_exists('EquipGraphReport')) {
    return;
}
define('EGR_VERSION', '1.04');

/****************************************************************************
*                       base class ���쥯�饹�����                         *
****************************************************************************/
class EquipGraphReport
{
    ///// Private properties
    private $mac_no;                    // �����ֹ�
    private $str_timestamp;             // ��������(Header) 'YYYY/MM/DD HH24:MI:SS'
    private $end_timestamp;             // ��λ����(Header OR CURRENT_TIMESTAMP)
    private $xtimeArrVal;               // X���λ��֤򥻥åȤǤ����ͤ�����̾�Τ�key�Ȥ���Ϣ������
    private $xtime;                     // X���λ����ϰ�(6/12/24/48...hr)
    private $graph_strTime;             // �����ϰϤγ������� 'YYYY/MM/DD HH24:MI:SS'
    private $graph_endTime;             // �����ϰϤν�λ����
    private $width;                     // ����դ���(pixel)
    private $height;                    // ����դι⤵(pixel)
    private $title;                     // ����դΥ����ȥ�̾
    private $sampling;                  // �����ϰ���Υ���ץ�󥰿�
    private $sample_time;               // ����ץ륿����(ʬ)�ֳ�
    private $multiply;                  // X����(����) scale ��Ψ
    private $yaxis_min_data;            // Y���κǾ���
    private $xdata;                     // X��������ǡ���1����
    private $ydata;                     // Y��������ǡ���2����
    private $graph_page;                // ����դ�ɽ������ڡ����ֹ�(�¥ڡ����� 1.2.3...)
    private $forward;                   // ���ڡ����Υե饰
    private $backward;                  // ���ڡ����Υե饰
    private $rui_state;                 // state ����߷פ���¸���� ����
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ///// Constructer ����� (php5�ذܹԻ��� __construct() ���ѹ�ͽ��)
    function EquipGraphReport($mac_no, $date='', $sampling=180)
    {
        if (!isset($_SESSION)) {                    // ���å����γ��ϥ����å�
            session_start();                        // Notice ���򤱤����Ƭ��@
        }
        if ($this->set_condition($date, $mac_no) == false) {
            $addr_status = "ErrorEquipGraphPage.php?status=2&date=$date&mac_no=$mac_no";
            header('Location: ' . H_WEB_HOST . ERROR . $addr_status);
            exit();
        }
        $this->set_xtimeArrVal();               // X���Υ��åȤǤ�������ν����
        $this->xtime = 24;                      // ����� Default=24hr (����١���)
        $this->set_graph_page();                // ����� Default=0 ɽ���ڡ�����offset�ڡ�����(-1/0/1/2/3...)
        $this->set_graphWH();                   // �����
        $this->set_title();                     // �����
        $this->set_sampling($sampling);         // �����
    }
    
    /*************************** Set & Check methods ************************/
    // �����ֹ桦�ؼ��ֹ桦�����ֹ�Υ����å�������ڤӳ��Ͻ�λ�����μ���
    function set_condition($date, $mac_no)
    {
        if ($mac_no  == '') return false;
        if ($date == '') {
            $date = date('Y/m/d', mktime() - 86400);    // �����˥��å�
        }
        $query = " select to_char(CAST('{$date} 08:30:00' AS TIMESTAMP), 'YYYY/MM/DD HH24:MI:SS') AS str_timestamp
                        , CASE
                            WHEN (CAST('{$date} 08:30:00' AS TIMESTAMP) + interval '24 hour') > CURRENT_TIMESTAMP THEN
                                to_char(CURRENT_TIMESTAMP, 'YYYY/MM/DD HH24:MI:SS')
                            ELSE
                                to_char(CAST('{$date} 08:30:00' AS TIMESTAMP) + interval '24 hour', 'YYYY/MM/DD HH24:MI:SS')
                          END
                            AS end_timestamp
        ";
        $res = array();
        if (getResult($query, $res) <= 0) {
            return false;
        } else {
            $this->str_timestamp = $res[0]['str_timestamp'];
            $this->end_timestamp = $res[0]['end_timestamp'];
        }
        $this->mac_no = $mac_no;
        return true;
    }
    // X��(���ּ�)������
    function set_xtime($xtime=24)
    {
        $unset = true;
        foreach ($this->xtimeArrVal as $time) {
            if ($xtime <= $time) {
                $xtime = $time;
                $unset = false;
                break;
            }
        }
        if ($unset) {
            $xtime = 24;   // ���Ĥ���ʤ�����Default��
        }
        $this->xtime = $xtime;
        $this->set_graph_page();        // xtime���ѹ��ˤ�������
        $this->set_sample_time();       // ����ץ�󥰴ֳ֤�����
        $this->set_multiply();          // X��(����scale)����Ψ����
    }
    // ����դ�ɽ���ڡ���������
    function set_graph_page($page_offset=0)
    {
        if (isset($_SESSION['equip_graph_page'])) {
            $this->graph_page = $_SESSION['equip_graph_page'];   // ɽ���ڡ����ν����
        } else {
            $_SESSION['equip_graph_page'] = 1;
            $this->graph_page = $_SESSION['equip_graph_page'];
        }
        $this->graph_page += ($page_offset);
        if ($this->graph_page <= 0) {
            $this->graph_page = 1;
        }
        $_SESSION['equip_graph_page'] = $this->graph_page;
        $this->set_graph_strTime($this->graph_page);
    }
    // ����դβ������⤵������
    function set_graphWH($width=670, $height=350)
    {
        $this->width  = $width;
        $this->height = $height;
    }
    // ����դΥ����ȥ�̾������
    function set_title($title='������ ���� ��ž���󥰥��')
    {
        //////////////// �����ޥ��������鵡��̾�����
        $query = "select trim(mac_name) as mac_name from equip_machine_master2 where mac_no={$this->mac_no} limit 1";
        if (getUniResult($query, $mac_name) <= 0) {
            $mac_name = '��';   // error���ϵ���̾��֥��
        }
        $this->title  = "{$this->mac_no}��{$mac_name}��{$title}";
    }
    // ���λ����ϰ���ǤΥ���ץ�󥰿�������
    function set_sampling($sampling=180)
    {
        if ($sampling <= 180) {
            $sampling = 180;
        } elseif ($sampling <= 360) {
            $sampling = 360;
        } else {
            $sampling = 180;
        }
        $this->sampling = $sampling;    // ����ץ�󥰿�������
        $this->set_sample_time();       // ����ץ�󥰴ֳ֤�����
        $this->set_multiply();          // X��(����scale)����Ψ����
    }
    
    /******************************* Out methods ****************************/
    // ����դ�ɽ���ڡ�������ȥ������
    function out_page_ctl($flg='backward')
    {
        if ($flg == 'backward') {
            return $this->backward;
        } elseif($flg == 'forward') {
            return $this->forward;
        } else {
            return false;
        }
    }
    // ����ճ��������ν���
    function out_graph_strTime()
    {
        return $this->graph_strTime;
    }
    // ����ս�λ�����ν���
    function out_graph_endTime()
    {
        return $this->graph_endTime;
    }
    // ������ϰϤγ��������ν��� (format��)  ���Ͻ�=����(strDate, strTime, endDate, endTime)
    function out_graph_timestamp()
    {
        $strDate = substr($this->graph_strTime,  0, 10);
        $strTime = substr($this->graph_strTime, 11,  8);
        $endDate = substr($this->graph_endTime,  0, 10);
        $endTime = substr($this->graph_endTime, 11,  8);
        return array('strDate' => "$strDate", 'strTime' => "$strTime", 'endDate' => "$endDate", 'endTime' => "$endTime");
    }
    // ��å����Τγ��������ν��� (format��)  ���Ͻ�=����(strDate, strTime, endDate, endTime)
    function out_lot_timestamp()
    {
        $strDate = substr($this->str_timestamp,  0, 10);
        $strTime = substr($this->str_timestamp, 11,  8);
        $endDate = substr($this->end_timestamp,  0, 10);
        $endTime = substr($this->end_timestamp, 11,  8);
        return array('strDate' => "$strDate", 'strTime' => "$strTime", 'endDate' => "$endDate", 'endTime' => "$endTime");
    }
    ////////// ����դ�������ƽ���
    function out_graph($graph_name='', $summary='no')
    {
        $this->generate_data();
        if ($summary == 'yes') {
            $this->height += 20;                            // ���ޥ꡼��٥뤬ɬ�פʤΤǹ⤵��+20
            $m_b = 80;                                      // Margin-bottom=80
        } else {
            $m_b = 60;                                      // Margin-bottom=60 ���ޥ꡼̵���ξ��
        }
        $graph = new Graph($this->width, $this->height, 'auto');   // ����դ��礭�� X/Y
        $graph->img->SetMargin(60, 20, 20, $m_b);                   // ����հ��֤Υޡ����� �����岼
        $graph->SetScale('textlin');                        // X / Y LinearX LinearY (�̾��textlin TextX LinearY)
        $graph->SetShadow(); 
        $graph->yscale->SetGrace(10);                       // Set 10% grace. ;͵��������
        $graph->yaxis->SetColor('blue');
        $graph->yaxis->SetWeight(2);
        $graph->yaxis->scale->ticks->SupressFirst();        // Y���κǽ�Υ����٥��ɽ�����ʤ�
        $graph->yscale->SetAutoMin($this->yaxis_min_data);  // Y���Υ������Ȥ��ѹ�
        $graph->xaxis->SetPos('min');                       // X���Υץ�åȥ��ꥢ����ֲ���
        $graph->xaxis->SetTickLabels($this->xdata);
        $graph->xaxis->SetFont(FF_FONT1);
        $graph->xaxis->SetLabelAngle(0);                    // 90 �� 0
        // $graph->xaxis->SetTextLabelInterval($this->sampling/$this->xtime);     // ���ƥå�
        $graph->xaxis->SetTextTickInterval($this->sampling / $this->xtime * $this->multiply, 0);
                                                            // ���ƥå�, ��������
        $plot = array();
        for ($i=0; $i<=R_STAT_MAX_NO; $i++) {               // 0��11
            if ($this->rui_state[$i] <= 0) {
                continue;         // state type ������ѻ��֤�������Ƥ�����ϡ�����ʪ�������������
            }
            equip_machine_state($this->mac_no, $i, $bg_color, $txt_color);
            $plot[$i] = new LinePlot($this->ydata[$i]);
            $plot[$i]->SetFillColor($bg_color);
            $plot[$i]->SetFillFromYMin($this->yaxis_min_data);  // Y���κǾ��ͤ��ѹ�
            $plot[$i]->SetColor($bg_color);
            $plot[$i]->SetCenter();
            $plot[$i]->SetStepStyle();
            $graph->Add($plot[$i]);
        }
        $graph->title->SetFont(FF_GOTHIC, FS_NORMAL, 12);
        $graph->title->Set(mb_convert_encoding($this->title, 'UTF-8'));
        $graph->subtitle->SetFont(FF_GOTHIC, FS_NORMAL, 10);
        $graph->subtitle->Set(mb_convert_encoding("{$this->graph_strTime} �� {$this->graph_endTime}  {$this->graph_page}�ڡ���", 'UTF-8'));
        $graph->subtitle->SetAlign('right');
        $graph->xaxis->title->SetFont(FF_GOTHIC, FS_NORMAL, 9);
        $graph->xaxis->title->Set(mb_convert_encoding('����(H)', 'UTF-8'));
        $graph->yaxis->title->SetFont(FF_GOTHIC, FS_NORMAL, 9);
        $graph->yaxis->title->Set(mb_convert_encoding('������', 'UTF-8'));
        $graph->yaxis->title->SetMargin(20, 0, 0, 0);
        ///// ���ޥ꡼��̤򥰥�դ˽����
        $g_x = 50;      // �����X���ν������
        if ($summary == 'yes') {
            for ($i=0; $i<=R_STAT_MAX_NO; $i++) {
                if ($this->rui_state[$i] <= 0) {
                    continue;
                }
                $name = equip_machine_state($this->mac_no, $i, $bg_color, $txt_color);
                $name .=  "\n" . number_format($this->rui_state[$i]/60, 2) . '(' . number_format($this->rui_state[$i]) . ')';
                $g_txt = new Text(mb_convert_encoding($name, 'UTF-8'), $g_x, $this->height - 50);
                $g_txt->SetFont(FF_GOTHIC, FS_NORMAL, 9);
                $g_txt->SetBox($bg_color, $bg_color, 'gray4');  // bg-color, border-color, shadow-color
                $g_txt->SetColor($txt_color);
                $g_txt->ParagraphAlign('center');
                $graph->AddText($g_txt);
                $g_x += ($g_txt->GetWidth($graph->img) + 25);   // ���Τ����TextBox���������
            }
        }
        if ($graph_name == '') {
            $graph_name = ('graph/equip' . session_id() . '.png');
        }
        $graph->Stroke($graph_name);
    }
    ////////// state ������Ѥ��줿����ɽ�ν���
    function out_state_summary()
    {
        $out = '';
        $out .= "        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='1'>\n";
        $out .= "            <tr><td> <!-- ���ߡ�(�ǥ�������) -->\n";
        $out .= "        <table width=100% bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='2'>\n";
        $out .= "            <th width='100'>&nbsp;</th>\n";
        for ($i=0; $i<=R_STAT_MAX_NO; $i++) {
            if ($this->rui_state[$i] <= 0) {
                continue;
            }
            $name = equip_machine_state($this->mac_no, $i, $bg_color, $txt_color);
            $out .= "            <th bgcolor='$bg_color' width='100' style='font-size:11pt'><font color='$txt_color'>" . $name . "</font></th>\n";
        }
        $out .= "            <tr>\n";
        $out .= "                <td align='center' style='font-size:11pt'>���ѻ���H (M)</td>\n";
        for ($i=0; $i<=R_STAT_MAX_NO; $i++) {
            if ($this->rui_state[$i] <= 0) {
                continue;
            }
            $name = equip_machine_state($this->mac_no, $i, $bg_color, $txt_color);
            $out .= "            <td align='center' style='font-size:12pt'>" . number_format($this->rui_state[$i]/60, 2) . ' (' . number_format($this->rui_state[$i]) . ")</td>\n";
        }
        $out .= "            </tr>\n";
        $out .= "        </table>\n";
        $out .= "            </td></tr>\n";
        $out .= "        </table> <!-- ���ߡ�End -->\n";
        return $out;
    }
    ////////// ����դ�X�� ���֥��������ͤ�HTML��select->option �Τߤ����
    function out_select_xtime($xtime)
    {
        $option = "\n";     // ���줬�ߥ� ������ɽ���򸫤䤹������
        foreach ($this->xtimeArrVal as $name => $time) {
            if ($xtime == $time) {
                if (mb_strlen($name) <= 3) {
                    $option .= "                            <option value='{$time}' selected>&nbsp;{$name}</option>\n";
                } else {
                    $option .= "                            <option value='{$time}' selected>{$name}</option>\n";
                }
            } else {
                if (mb_strlen($name) <= 3) {
                    $option .= "                            <option value='{$time}'>&nbsp;{$name}</option>\n";
                } else {
                    $option .= "                            <option value='{$time}'>{$name}</option>\n";
                }
            }
        }
        return $option;
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    // X���Υ������륻�åȤǤ������������
    function set_xtimeArrVal()
    {
        for ($i=2; $i<=24; $i+=2) {                 // 2���֡�24����
            $this->xtimeArrVal["{$i}����"] = $i;
        }
        /************   // �����EquipGraphClass�ξ��
        for ($i=48; $i<=1440; $i+=24) {             // 2���֡�60����
            $day = ($i / 24);
            $this->xtimeArrVal["{$day}����"] = $i;
        }
        ************/
    }
    // ����ղ����볫�ϻ��֤Ƚ�λ���֤�����
    function set_graph_strTime($graph_page=1)
    {
        if ($graph_page <= 1) {
            $this->backward = false;
        } else {
            $this->backward = true;
        }
        $xtime = ($this->xtime * ($graph_page - 1) );
        $query = "select to_char( (CAST('{$this->str_timestamp}' AS TIMESTAMP) + interval '$xtime hour'), 'YYYY/MM/DD HH24:MI:SS')";
        getUniResult($query, $strTime);
        $this->graph_strTime = $strTime;
        $this->set_graph_endTime($this->graph_strTime, $this->xtime);
    }
    // ����ղ����뽪λ���֤�����
    function set_graph_endTime($strTime, $xtime)
    {
        $query = "select to_char( (CAST('{$strTime}' AS TIMESTAMP) + interval '$xtime hour'), 'YYYY/MM/DD HH24:MI:SS')";
        getUniResult($query, $endTime);
        $this->graph_endTime = $endTime;
        if ($this->end_timestamp > $this->graph_endTime) {
            $this->forward = true;
        } else {
            $this->forward = false;
        }
    }
    // ����դΥ���ץ�󥰴ֳ֤�����
    function set_sample_time()
    {
        $this->sample_time = (($this->xtime * 60) / $this->sampling);   // �� (12*60)/180=4ʬ�ֳ�(default)
    }
    // ����դ�X��(����)�� scale�� ��Ψ����
    function set_multiply()
    {
        if ($this->sampling <= 180) {   // ����ץ��������� 180�λ�
            if ($this->sample_time <= 4) {
                $this->multiply = 1;
            } else {
                $this->multiply = ($this->sample_time / 4);           // scale ����Ψ����
            }
        } else {                        // ���ߤ� 360�β����٤λ�
            if ($this->sample_time <= 2) {
                $this->multiply = 1;
            } else {
                $this->multiply = ($this->sample_time / 2);           // scale ����Ψ����
            }
        }
    }
    ////////////////////////////////////////////////////////////////////////////
    // ������ѥǡ������� X����Y��(����12����)                                //
    ////////////////////////////////////////////////////////////////////////////
    function generate_data()
    {
        /////////// begin �ȥ�󥶥�����󳫻�
        if ($con = funcConnect()) {
            query_affected_trans($con, 'begin');
        }
        if ($this->end_timestamp > $this->graph_endTime) {
            $graph_end = $this->graph_endTime;
        } else {
            $graph_end = $this->end_timestamp;
        }
        ////////// �����ϰ���˥ǡ��������뤫�����å�
        $query = " select work_cnt
                    from
                        equip_work_log2
                    where
                        equip_index2(mac_no, date_time) >= '{$this->mac_no}{$this->graph_strTime}'
                        and
                        equip_index2(mac_no, date_time) <= '{$this->mac_no}{$graph_end}'
                    offset 0 limit 1
        ";
        if (getUniResTrs($con, $query, $max_work_cnt) <= 0) {
            $empty = true;
        } else {
            $empty = false;
        }
        ////////// �����ϰ���Ǥκ������������������yaxis_min_data�����ꤹ��
        if ($empty == false) {
            // �����ϰϤ˥ǡ�����������ν���
            $query = " select max(work_cnt)
                        from
                            equip_work_log2
                        where
                            equip_index2(mac_no, date_time) >= '{$this->mac_no}{$this->graph_strTime}'
                            and
                            equip_index2(mac_no, date_time) <= '{$this->mac_no}{$graph_end}'
            ";
            getUniResTrs($con, $query, $max_work_cnt);
        } else {
            // �����ϰϤ˥ǡ������ʤ����ν���
            $query = " select work_cnt
                        from
                            equip_work_log2
                        where
                            equip_index2(mac_no, date_time) < '{$this->mac_no}{$this->graph_strTime}'
                        order by
                            equip_index2(mac_no, date_time) DESC
                        offset 0 limit 1
            ";
            if (getUniResTrs($con, $query, $max_work_cnt) <= 0) {
                $max_work_cnt = 0;
            }
        }
        $this->yaxis_min_data = yaxis_min($max_work_cnt);     // work_counter �κ���ù������饰��դκǾ��ͤ򻻽�
        ////////// ���ǡ����μ��� (����graph_strTime�ʲ��Υǡ�����1�����)
        $query = " select mac_state
                        , work_cnt
                        , siji_no
                        , koutei
                    from
                        equip_work_log2
                    where
                        equip_index2(mac_no, date_time) <= '{$this->mac_no}{$this->graph_strTime}'
                    order by
                        equip_index2(mac_no, date_time) DESC
                    offset 0 limit 1
        ";
        $this->xdata = array(0 => 0);      // ���ѻ���(hr)
        $this->ydata = array();
        $rui_time = array();
        $res = array();
        $r = 0;
        if (getResultTrs($con, $query, $res) <= 0) {
            // ľ���Υǡ���(���ǡ����˻���)���ʤ���� (���ε���)
            $query_exc = " select mac_state
                                , work_cnt
                                , siji_no
                                , koutei
                            from
                                equip_work_log2
                            where
                                equip_index2(mac_no, date_time) > '{$this->mac_no}{$this->graph_strTime}'
                                and
                                equip_index2(mac_no, date_time) <= '{$this->mac_no}{$graph_end}'
                            order by
                                equip_index2(mac_no, date_time) ASC
                            offset 0 limit 1
            ";
            if (getResultTrs($con, $query_exc, $res) <= 0) {
                // ����Ǥ�̵�������Ÿ�OFF ������0
                $res[0][0] = 0;
                $res[0][1] = 0;
                $res[0][2] = 0;
                $res[0][3] = 0;
            }
        }
        if ($max_work_cnt < $res[0][1]) {
            // ľ����work_cnt�������礭�����max_work_cnt�˥��åȤ��ʤ���
            $this->yaxis_min_data = yaxis_min($res[0][1]); // work_counter �κ���ù������饰��դκǾ��ͤ򻻽�
        }
        for ($i=0; $i<=R_STAT_MAX_NO; $i++) {
            if ($res[0][0] == $i) {
                $this->ydata[$i][$r] = $res[0][1];         // ���֤����פ���ʪ��work_cnt�򥻥å�
            } else {
                $this->ydata[$i][$r] = $this->yaxis_min_data;    // �㤦��ΤϺǾ��ͤ򥻥åȤ��롣
            }
        }
        $this->xdata[$r] = $this->xdata_offset($con, 0);    // ����ʬ
        
        $r++;
        $this->xdata[$r] = round((@$rui_time[$r-1] + round($this->sample_time / 60, 6)), 0);  // ʬ����֤��Ѵ�
        $this->xdata[$r] = $this->xdata_offset($con, $this->xdata[$r]);
        @$rui_time[$r] = (@$rui_time[$r-1] + round($this->sample_time / 60, 6));  // ʬ����֤��Ѵ�
        $query_now = "select to_char( (CAST('{$this->graph_strTime}' AS TIMESTAMP) + interval '{$this->sample_time} minute'), 'YYYY/MM/DD HH24:MI:SS')";
        getUniResTrs($con, $query_now, $now_time);
        ////////// 2���ܰʹߤϥ롼�פǽ���
        $mac_state = $res[0][0];
        $work_cnt  = $res[0][1];
        $siji_no   = $res[0][2];
        $koutei    = $res[0][3];
        while (1) {
            $query = " select mac_state
                            , work_cnt
                            , siji_no
                            , koutei
                        from
                            equip_work_log2
                        where
                            equip_index2(mac_no, date_time) >= '{$this->mac_no}{$this->graph_strTime}'
                            and
                            equip_index2(mac_no, date_time) <= '{$this->mac_no}{$now_time}'
                        order by
                            equip_index2(mac_no, date_time) DESC
                        offset 0 limit 1
            ";
            getResultTrs($con, $query, $res);
            if (isset($res[0][0])) $mac_state = $res[0][0]; // �ǡ�����̵���������Υǡ�����Ȥ�
            if (isset($res[0][1])) $work_cnt  = $res[0][1];
            if (isset($res[0][2])) $siji_no   = $res[0][2];
            if (isset($res[0][3])) $koutei    = $res[0][3];
            for ($i=0; $i<=R_STAT_MAX_NO; $i++) {
                if ($mac_state == $i) {
                    // �إå����Ǵ�λ���Υ����å�
                    $query_chk = "select to_char(end_timestamp, 'YYYY/MM/DD HH24:MI:SS') from equip_work_log2_header where mac_no={$this->mac_no} and siji_no={$siji_no} and koutei={$koutei}";
                    getUniResTrs($con, $query_chk, $end_timestamp);
                    if ( ($end_timestamp != '') && ($now_time > $end_timestamp) ) {
                        $this->ydata[$i][$r] = $this->yaxis_min_data;    // ��λ����Ƥ���ʪ�ϺǾ��ͤ򥻥åȤ��롣
                    } else {
                        $this->ydata[$i][$r] = $work_cnt;         // ���֤����פ���ʪ��work_cnt�򥻥å�
                    }
                } else {
                    $this->ydata[$i][$r] = $this->yaxis_min_data;    // �㤦��ΤϺǾ��ͤ򥻥åȤ��롣
                }
            }
            $r++;
            $this->xdata[$r] = round((@$rui_time[$r-1] + round($this->sample_time / 60, 6)), 0);  // ʬ����֤��Ѵ�
            $this->xdata[$r] = $this->xdata_offset($con, $this->xdata[$r]);
            @$rui_time[$r] = (@$rui_time[$r-1] + round($this->sample_time / 60, 6));  // ʬ����֤��Ѵ�
            $query = "select to_char( (CAST('{$now_time}' AS TIMESTAMP) + interval '{$this->sample_time} minute'), 'YYYY/MM/DD HH24:MI:SS')";
            getUniResTrs($con, $query, $now_time);
            $date = substr($now_time, 0, 10);
            $time = substr($now_time, 11, 8);
            ////////// ��λ����(���ϸ��߻���)������դ��ϰϽ�λ�ǥ֥쥤��   (ʸ�������ӤʤΤ����)
            if ( ($now_time > $this->end_timestamp) || ($now_time > $this->graph_endTime) ) {
                break;
            }
        }
        ////////// ���߻����᤮�Ƥ⥰����ϰϤ��ĤäƤ�����˽��� (�֥�󥯥ǡ���������)
        while ($now_time <= $this->graph_endTime) {
            $this->xdata[$r] = round((@$rui_time[$r-1] + round($this->sample_time / 60, 6)), 0);  // ʬ����֤��Ѵ�
            $this->xdata[$r] = $this->xdata_offset($con, $this->xdata[$r]);
            @$rui_time[$r] = (@$rui_time[$r-1] + round($this->sample_time / 60, 6));  // ʬ����֤��Ѵ�
            for ($i=0; $i<=R_STAT_MAX_NO; $i++) {
                $this->ydata[$i][$r] = $this->yaxis_min_data;   // �����ʤ��ΤǺǾ��ͤΤߥ��åȤ��롣
            }
            $r++;
            $query = "select to_char( (CAST('{$now_time}' AS TIMESTAMP) + interval '{$this->sample_time} minute'), 'YYYY/MM/DD HH24:MI:SS')";
            getUniResTrs($con, $query, $now_time);
            if ($now_time > $this->graph_endTime) {
                break;
            }
        }
        /////////// �ȥ�󥶥������λ
        query_affected_trans($con, 'commit');
        ///// status ��ν��פ򤷤ƽ�λ
        $this->state_summary();
    }
    ////////////////////////////////////////////////////////////////////////////
    ////////// status ��ν��ץǡ�������                                      //
    ////////////////////////////////////////////////////////////////////////////
    function state_summary()
    {
        $this->rui_state = array();
        for ($r=0; $r<$this->sampling; $r++) {      // �ƾ���������ѻ��֤򻻽�
            for ($i=0; $i<=R_STAT_MAX_NO; $i++) {
                if ($this->ydata[$i][$r] > $this->yaxis_min_data) {  // work_cnt�����åȤ���Ƥ���л��֤����Ѥ���
                    @$this->rui_state[$i] += $this->sample_time;     // ��������Ƥ��ʤ��Τ�Ƭ��@
                } else {
                    @$this->rui_state[$i] += 0;
                }
            }
        }
    }
    ////////////////////////////////////////////////////////////////////////////
    ////////// X���Υ�٥��������֤˹�碌�륪�ե��åȽ���                  //
    ////////////////////////////////////////////////////////////////////////////
    function xdata_offset($con, $xdata)
    {
        if ($this->graph_page > 1) {
            $xdata += ($this->xtime * ($this->graph_page - 1));     // 2�ڡ����ܹԤ�offset����
        }
        switch ($xdata) {
        CASE (0):
            return '08:30';
            break;
        CASE (1):
            return '09:30';
            break;
        CASE (2):
            return '10:30';
            break;
        CASE (3):
            return '11:30';
            break;
        CASE (4):
            return '12:30';
            break;
        CASE (5):
            return '13:30';
            break;
        CASE (6):
            return '14:30';
            break;
        CASE (7):
            return '15:30';
            break;
        CASE (8):
            return '16:30';
            break;
        CASE (9):
            return '17:30';
            break;
        CASE (10):
            return '18:30';
            break;
        CASE (11):
            return '19:30';
            break;
        CASE (12):
            return '20:30';
            break;
        CASE (13):
            return '21:30';
            break;
        CASE (14):
            return '22:30';
            break;
        CASE (15):
            return '23:30';
            break;
        CASE (16):
            return '00:30';
            break;
        CASE (17):
            return '01:30';
            break;
        CASE (18):
            return '02:30';
            break;
        CASE (19):
            return '03:30';
            break;
        CASE (20):
            return '04:30';
            break;
        CASE (21):
            return '05:30';
            break;
        CASE (22):
            return '06:30';
            break;
        CASE (23):
            return '07:30';
            break;
        CASE (24):
            return '08:30';
            break;
        default:
            return '';
            break;
        }
        /*****************************
        if ( $xdata == 24) {
            return '08:30';                     // 24���ָ�ϲ���SQL�ǥ��顼�ˤʤ뤿���ƥ����֤�
        }
        for ($i=0; $i<24; $i++) {
            if ($i == $xdata) {
                $query = "select to_char( (CAST('{$xdata}:00' AS TIME) + interval '8:30 hour'), 'HH24:MI')";
                getUniResTrs($con, $query, $new_xdata);
                return $new_xdata;
            } else {
                return $xdata;                  // �ϰϳ����������Ǥʤ�����ͤ򤽤Τޤ��֤�
            }
        }
        *****************************/
    }
    
} // class EquipGraph End

?>
