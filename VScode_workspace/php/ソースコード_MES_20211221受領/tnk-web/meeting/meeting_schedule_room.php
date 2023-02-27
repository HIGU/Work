<?php
//////////////////////////////////////////////////////////////////////////////
// ����� [��ļ�]�򥦥���ɥ�ɽ���ʲ�ļ������Ѿ������ǧ�Ǥ����          //
// Copyright (C) 2021-2021 Ryota.Waki ryota_waki@nitto-kohki.co.jp          //
// Changed history                                                          //
// 2021/05/14 Created   meeting_schedule_room.php                           //
// 2021/06/10 ����������󥯤��ɲ�                                   ��ë //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
ini_set('max_execution_time', 60);          // ����¹Ի���=60�� WEB CGI��
//ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
//session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../function.php');     // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../MenuHeader.php');   // TNK ������ menu class
require_once ('../ControllerHTTP_Class.php');       // TNK ������ MVC Controller Class
require_once ('meeting_schedule_Model.php');        // MVC �� Model��
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader();                   // ǧ�ڥ����å�0=���̰ʾ� �����=���å������ �����ȥ�̤����

//////////// �ꥯ�����ȥ��֥������Ȥμ���
$request = new Request();

//////////// �ꥶ��ȤΥ��󥹥�������
$result = new Result();

//////////// ���å���� ���֥������Ȥμ���
$session = new Session();

$menu->set_title('��ļ�ͽ��ɽ');     // �����ȥ������ʤ���IE�ΰ����ΥС�������ɽ���Ǥ��ʤ��Զ�礢��

///// �����Ȥ�ǯ���������ꤵ��Ƥ��뤫�����å�
if ($request->get('year') == '' || $request->get('month') == '' || $request->get('day') == '') {
    // �����(����)������
    $request->add('year', date('Y')); $request->add('month', date('m')); $request->add('day', date('d'));
}
///// ����ɽ�����δ���(1����,7����,14,28...)
if ($request->get('listSpan') == '') {
    if ($session->get_local('listSpan') != '') {
        $request->add('listSpan', $session->get_local('listSpan'));
    } else {
        $request->add('listSpan', '0');             // �����(�����Τ�)
    }
}
$session->add_local('listSpan', $request->get('listSpan')); // ���å����ǡ������ѹ�

//////////// �ӥ��ͥ���ǥ����Υ��󥹥�������
$model = new MeetingSchedule_Model($request);

// 
$year = $request->get('year');
$month = $request->get('month');
$day = $request->get('day');
$uniq = 'meeting';


$url = $menu->out_self() . "?showMenu=List&" . $model->get_htmlGETparm() . "&id={$uniq}";

$today   = date('Ymd');
$today_y = substr($today,0,4);
$today_m = substr($today,4,2);
$today_d = substr($today,6,2);

if (preg_match('/\?/', $url)) {
    $url_para = $url . "&year={$today_y}&month={$today_m}&day={$today_d}";
} else {
    $url_para = $url . "?year={$today_y}&month={$today_m}&day={$today_d}";
}

// ǯ�����Υɥ�åץ�����ꥹ�Ⱥ���
function SelectOptionDate($start, $end, $def)
{
    for ($i = $start; $i <= $end ; $i++) {
        if ($i == $def) {
            echo "<option value='" . sprintf("%02d", $i) . "' selected>" . $i . "</option>";
        } else {
            echo "<option value='" . sprintf("%02d", $i) . "'>" . $i . "</option>";
        }
    }
}

// ��Ҥε���������������壱ǯʬ��
function GetHoliday($year)
{
    if( !$year ) return "";
    $s_year = $year++;
    $e_year = $year--;

    $query = "
            SELECT  tdate           AS ����
            FROM    company_calendar
            WHERE   tdate >= '{$s_year}0101' AND tdate <= '{$e_year}1231' AND bd_flg = 'f'
        ";
    if( getResult2($query, $res) <= 0 ) {
        return "";
    }
    return $res;
}

// �ɤ����ļ�����
function WhereDaiRoom( $room_name )
{
    if( ! strstr($room_name, "���ļ�") ) return false;  // ���ļ�����

    $pos = strpos($room_name, '('); // �ɤ������ļ�����Ƚ�Ǥ���١�'('��ʸ�����֤������

    if( ! $pos ) return false;      // '('�����뤫��

    $where = substr($room_name, $pos+1, 2); // �� or �� or �� �򥻥å�

    return $where;
}

// ʣ�� ���ļ� ���ѡ�
function IsDaiRoomMulti( $room_name )
{
    if( ! strstr($room_name, "/") ) return false;         // ʣ������
    if( ! strstr($room_name, "���ļ�") ) return false;  // ���ļ�����

    return true;
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<?php echo $menu->out_jsBaseClass() ?>
<link rel='stylesheet' href='meeting_schedule.css' type='text/css' media='screen'>
<script type='text/javascript' src='meeting_schedule.js'></script>

<style>
</style>

</head>
<body onLoad='set_focus()'>
<center>

    <form name='ControlForm' action='<?php echo "./meeting_schedule_Main.php?year={$year}&month={$month}&day={$day}&showMenu=Apend&only=yes&{$model->get_htmlGETparm()}&id={$uniq}"; ?>' method='post'>

    <script>setSelectDate(<?php echo $year; ?>,<?php echo $month; ?>,<?php echo $day; ?>);</script>
    <script>var holiday = '<?php echo json_encode(GetHoliday($year)); ?>';setHoliday(holiday);</script>

    <BR>
    <input type="button" value="< ������ <" name="befor" onClick="setBeforDate(); checkANDexecute(8);">��

    <select name="ddlist" id="id_year" onChange="setDdlistDate(); checkANDexecute(8);">
        <?php SelectOptionDate(date('Y')-1, date('Y')+1, $year); ?>
    </select>ǯ
    <select name="ddlist" id="id_month" onChange="setDdlistDate(); checkANDexecute(8);">
        <?php SelectOptionDate(1, 12, $month); ?>
    </select>��
    <select name="ddlist" id="id_day" onChange="setDdlistDate(); checkANDexecute(8);">
        <?php SelectOptionDate(1, 31, $day); ?>
    </select>��
    <font id='id_week'></font>
    <script>viewWeek();</script>

    ��<input type="button" value="> ������ >" name="next" onClick="setNextDate(); checkANDexecute(8);">

    <BR>
    <a href='<?php echo $url_para ?>' style='text-decoration:none;' class='current'><font size='2'><B>����</B></font></a>
    <BR>
    <input type="button" value=">>> ��ɽ�� <<<" name="review" onClick='checkANDexecute(8);'>
    ��<input type="button" value="[��]�Ĥ���" name="close" onClick='window.parent.close();'>
    <BR>

    <?php
    $room_info[0][0] = "��";        // [��]����[��]���� ���Ѿ����ǡ�����Ǽ�ΰ�
    $s_hour =  8; $s_minute = 0;    // ����
    $e_hour = 21; $e_minute = 0;    // ��λ

    // �ԥǡ��������ʹԿ���$r_idx�˳��ϡ���λ�ޤǣ�ʬ��ߤΥǡ�������
    $r_idx = 1; // ����ͥ��åȡ�0���ϡ�����
    for( $h=$s_hour; $h<=$e_hour; $h++ ) {
        if( $h==$e_hour ) $max=$e_minute; else $max=59;
        for( $m=0; $m<=$max; $m=$m+5 ) {    // 5ʬñ��
            $t = sprintf("%02d:%02d", $h, $m);
            $room_info[$r_idx][0] = $t; // ���֥��å�
            $r_idx++;   // ���åȸ塢�Կ�������ȥ��å�
        }
    }

    // ��ļ��ξ�������
    $rows = $model->getActiveRoomList($result); // 0:����No. 1:����̾
    $res  = $result->get_array();
    $cnt  = 0;  // ���Ѥ���������
//    $th_width = 102;    // �����
    $th_width = 108;    // �����
//    $th_width = 140;    // �����

    // ��ǡ��������������$f_idx�˻�������ʬ�Υǡ�������
    $f_idx = 1; // ����ͥ��åȡ�0���ϡ�����
    for( $r=0; $r<$rows; $r++ ){
        if( !strstr($res[$r][1], "��ļ�") && !strstr($res[$r][1], "���ܼ�") ) continue;
        if( strstr($res[$r][1], "/") ) continue;   // ���ļ���ʣ�����Ѥ�Ϥ�����
        $room = str_replace("��̳����", "", $res[$r][1]);   // ����̾���ڻ�̳����ۺ��

        $pos = strpos($room, '('); // '('��ʸ�����ּ���
        if( $pos ) {
            $room = substr_replace($room, '<BR>(', $pos, 1); // '('��������Ԥ��롣
        }

        $room_info[0][$f_idx] = $room;      // ɽ����������̾(��ά̾��)
        $f_idx++;   // ���åȸ塢���������ȥ��å�
        $room_name[0][$cnt] = $res[$r][0];  // ��������No.
        $room_name[1][$cnt] = $res[$r][1];  // ��������̾(����̾��)
        $cnt++; // ����������
    }

    // ͽ���������
    $rows = $model->getViewList($result);
    $res  = $result->get_array();   // 0�����ꥢ���ֹ� 1����̾ 2���������� 3����λ���� 4������

    // ���ѥǡ���������ͽ�ꤢ����Ͼ���򥻥åȡ������Ȼ��֤�����å��������������ڳ���-��λ���֡۷�̾
    for( $r=0; $r<$cnt; $r++ ) {    // ɽ�������������롼��
        $where = WhereDaiRoom($room_name[1][$r]);           // �ɤ������ļ������� or �� or �� �����
        for( $c=0; $c<$rows; $c++ ) {   // ��������ͽ�����ʬ�롼��
            $flag = false;
            if( $where && IsDaiRoomMulti($res[$c][4]) ) {   // ͽ��ǡ�ʣ�������ļ�����ѡ�
                $flag = strstr($res[$c][4], $where);        // �����å�������Ĥ�ޤޤ�뤫��
            }

            if( $room_name[1][$r] == $res[$c][4] || $flag ) {    // ��������̾��ͽ����󤫡�
                $s_h = substr($res[$c][2], 9, 2); $s_m = substr($res[$c][2], 12, 2);    // ���ϻ���
                $e_h = substr($res[$c][3], 9, 2); $e_m = substr($res[$c][3], 12, 2);    // ��λ����
                $s_t = (($s_h*60) + $s_m - ($s_hour*60+$s_minute)) / 5;  // ���ϻ��֡�ʬ�Ѵ���ʳ��Ϥ֤�ϸ�����5ʬñ�� 
                $e_t = (($e_h*60) + $e_m - ($s_hour*60+$s_minute)) / 5;  // ��λ���֡�ʬ�Ѵ���ʳ��Ϥ֤�ϸ�����5ʬñ��
                $start_end = '��' . $s_h . ':' . $s_m . '-' . $e_h . ':' . $e_m . '��<BR>';

                $rang = $e_t - $s_t;
                $room_info[$s_t+1][$r+1] = $rang . ':' . $start_end . $res[$c][1]; // �������ڳ���-��λ���֡۷�̾
                for( $n=$s_t+1; $n<$e_t; $n++) {
                    $room_info[$n+1][$r+1] = 1; // ͽ�ꤢ��ե饰 1 �򥻥å�
                }
            }
        }
    }

    // ���ѥǡ��������ʶ��� 0 �򥻥å� �Ԥκǽ����ͽ�ꤢ��ʤ��ե饰�򥻥åȡ�
    for( $r=1; $r<$r_idx; $r++ ) {
        $room_info[$r][$f_idx] = 'off';     // ����� ͽ��ʤ� off �򥻥å�
        for( $f=1; $f<$f_idx; $f++ ) {
            if( empty($room_info[$r][$f]) ) {
                $room_info[$r][$f] = '0';   // �����ե饰 0 �򥻥å�
            } else {
                $room_info[$r][$f_idx] = 'on';    // ͽ�꤬������֤ʤ� on �򥻥å�
            }
        }
    }
    ?>
    <BR>    <!-- ��ļ����Ѿ����ơ��֥��ɽ�� -->
    <table class='list' style='height:78%; width:100%; bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <caption>
        </caption>
        <tr><td> <!-- ���ߡ� -->

        <!-- ���Ф��Ԥ򥹥�����С��դ��Υơ��֥��ɽ��(���֤�·�����) -->
        <div style='width:100%; overflow-y:scroll;'>
            <table class='winbox_field' bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
            <?php
            echo "<th style='font-size:0pt; width:48;' class='winbox' nowrap>��</th>";
            for( $f=1; $f<$f_idx; $f++ ) {
                echo "<th style='width:{$th_width};' class='winbox' nowrap>{$room_info[0][$f]}</th>";
            }
            ?>
            </table>
        </div>

        <!-- ���Ѿ����򥹥�����С��դ��Υơ��֥��ɽ�� -->
        <div style='height:94%; width:100%; overflow-y:scroll;'>
            <table class='winbox_field' bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
                <input type='hidden' name='room_no' id='id_room_no' value=''>
                <input type='hidden' name='str_hour' id='id_str_hour' value=''>
                <input type='hidden' name='str_minute' id='id_str_minute' value=''>
                <input type='hidden' name='end_hour' id='id_end_hour' value=''>
                <input type='hidden' name='end_minute' id='id_end_minute' value=''>
            <?php
            $time_flag = $time_flag2 = false;
            for( $r=1; $r<$r_idx-1; $r++ ) {
                $hu = substr($room_info[$r][0], 0, 2);
                $mi = substr($room_info[$r][0], 3, 2);

                $view_style = '';   // �Ԥ�ɽ���������� �طʡ����졼��ʸ������
                $row_time = sprintf("%04d%02d%02d%02d%02d00", $request->get('year'), $request->get('month'), $request->get('day'), $hu, $mi);
                if( date('YmdHis') > $row_time+500 ) { // ���������� �طʡ����졼��ʸ�������졼
                    $view_style .= 'background-color:#e6e6e6; color:DarkGray;';
                } else if(! $time_flag && date('d') == $request->get('day') ){    // ������Τ� �طʡ�������ʸ������
                    $view_style .= 'background-color:yellow; color:blue;';
                    $time_flag = true;  // 1�ԤΤߤΰ١������ե饰���ڤ��ؤ��롣
                }
                echo "<tr class='winbox' style='{$view_style}'>";

                // ������ʬ��ɽ��
                if( $mi == "00" || $mi == "30" ) {  // 30ʬñ�̤�ɽ���ΰ���礹��
                    $r_s = 6;       // 30ʬ��5ʬ��6ʬ 6��ʬ���
                    $view_style = '';   // �Ԥ�ɽ���������� �طʡ����졼��ʸ������
                    if( $mi == "00") {
                        $view_style = 'border-bottom-style:none; ';
                    } else {
                        $view_style = 'border-top-style:dashed; border-bottom-style:none; ';
                    }

                    if( date('YmdHis') < $row_time+3000 ) {
                        if(! $time_flag2 && date('d') == $request->get('day')){    // ������Τ� �طʡ�������ʸ������
                            $view_style .= 'background-color:yellow; color:blue;';
                            $time_flag2 = true;  // 1�ԤΤߤΰ١������ե饰���ڤ��ؤ��롣
                        }
                    }
                    echo "<td rowspan='{$r_s}' style='{$view_style}'>{$room_info[$r][0]}</td>";
                }

                // �������ξ����ɽ��
                for( $f=1; $f<$f_idx; $f++ ) {
                    if( $room_info[$r][$f] == '0' ) {           // �����Ƥ�����
                        $view_style = 'font-size: 5pt; line-height: 0; ';
                        if( $mi == "00" ) {         // 00ʬ��05ʬ �塧ɽ����������ɽ��
                            $view_style .= 'border-bottom-style:none;';
                        } else if( $mi == "30") {   // 30ʬ��35ʬ �塧������������ɽ��
                            $view_style .= 'border-top-style:dashed; border-bottom-style:none;';
                        } else {                    // ����ʳ��Υե������ �塧��ɽ����������ɽ��
                            $view_style .= 'border-top-style:none; border-bottom-style:none;';
                        }

                        // ���ߤλ����ꡢ��������������å����롣��00��30 or 30��60 ��30ʬ�֤��
                        if( $mi < 30 ) {    // 00��25
                            $s_hu = $hu; $s_max = "00"; $e_hu = $hu; $e_max = "25";
                        } else {            // 30��55
                            $s_hu = $hu; $s_max = "30"; $e_hu = $hu; $e_max = "55";
                        }
                        
                        // ���ߤλ����ꡢ���ʡ�00 or ��30�ˤ˻��Ѥ���Ƥ��ʤ��������å���
                        for( $w=$r, $w_mi=$mi; $w_mi>=$s_max; $w--, $w_mi=$w_mi-5) {
                            if( $room_info[$w][$f] != '0' ) break;
                            $s_mi = $w_mi;      // ���ϤǤ���ʬ�򥻥å�
                        }
                        
                        // ���ߤλ����ꡢ��ʡ�30 or ��60�ˤ˻��Ѥ���Ƥ��ʤ��������å���
                        for( $w=$r, $w_mi=$mi; $w_mi<=$e_max; $w++, $w_mi=$w_mi+5) {
                            if( $room_info[$w][$f] != '0' ) break;
                            $e_mi = $w_mi+5;    // ��λ�Ǥ���ʬ�򥻥å�
                        }
                        
                        if( $w_mi == 60 ) { // 60ʬ�ޤǻ��Ѥ���Ƥ���Τǡ����λ��֤��ѹ���ʬ�� 00 �򥻥å�
                            $e_hu = $hu+1;
                            $e_mi = "00";
                        }

                        echo "<td style='{$view_style} width:{$th_width};' onClick='setApendData({$s_hu},{$s_mi},{$e_hu},{$e_mi},{$room_name[0][$f-1]}); submit();'>��</td>";
                    } else if( $room_info[$r][$f] != '1' ) {    // ͽ�ꤢ��
                        $pos = strpos($room_info[$r][$f], ':'); // �����ޤǤ�ʸ��������
                        $num = preg_replace('/[^0-9]/', '', substr($room_info[$r][$f], 0, $pos));   // ��������
                        $title = substr($room_info[$r][$f], $pos+1);    // �ڳ���-��λ���֡۷�̾����
                        if( $room_info[$r][$f_idx] == 'on' ) $view_style = 'background-color:PaleTurquoise; color:White; ';

                        $title_only = substr($room_info[$r][$f], $pos+20);  // ��̾�Τ߼���

                        $where = WhereDaiRoom($room_name[1][$f-1]);         // �ɤ������ļ������� or �� or �� �����
                        for( $c=0; $c<$rows; $c++ ) {   // ��������ͽ�����ʬ�롼��
                            $flag = false;
                            if( $where && IsDaiRoomMulti($res[$c][4]) ) {   // ͽ��ǡ�ʣ�������ļ�����ѡ�
                                $flag = strstr($res[$c][4], $where);        // �����å�������Ĥ�ޤޤ�뤫��
                            }

                            if( $room_name[1][$f-1] == $res[$c][4] || $flag ) {    // ��������̾��ͽ����󤫡�
                                if( $title_only == $res[$c][1] && $hu == substr($res[$c][2], 9, 2) && $mi == substr($res[$c][2], 12, 2) ) {
                                    break;
                                }
                            }
                        }

                        if( $c != $rows ) {
                            $col = 1;   // �����������ν���ͥ��å�
                            if( $flag ) {
                                if( $f == 1 || $f == 2 ) { // ���ļ����� or ��ˤλ������ļ����� or ��ˤ�Ʊ��ͽ�꤫��
                                    if( $room_info[$r][$f] == $room_info[$r][$f+1] ) {
                                        $room_info[$r][$f+1] = 1;
                                        $col=2;
                                    }
                                }
                                if( $f == 1 && $col == 2) { // ���ļ����̡ˤλ������ļ�����ˤ�Ʊ��ͽ�꤫��
                                    if( $room_info[$r][$f] == $room_info[$r][$f+2] ) {
                                        $room_info[$r][$f+2] = 1;
                                        $col=3;
                                    }
                                }
                            }

                            $a_view_style='background-color:PaleTurquoise; ';
                            $e_date = sprintf("%04s%02s%02s", $year, $month, $day);
                            $e_time = substr($title, 8, 2) . substr($title, 11, 2);
                            if( date('YmdHi') >= ($e_date . $e_time)) { // ����ͽ��
                                $view_style = 'background-color:Silver; color:White; ';
                                $a_view_style = 'background-color:Silver; color:White; ';
                            }
                            $td_width = $th_width * $col;   // ��������ʬ�η�̾ɽ���ΰ襻�åȡ�����
                            echo "<td class='winbox' rowspan='{$num}' colspan='{$col}' style='{$view_style} width:{$td_width};'>
                                    <a href='./meeting_schedule_Main.php?serial_no={$res[$c][0]}&year={$year}&month={$month}&day={$day}&showMenu=Edit&only=yes&{$model->get_htmlGETparm()}&id={$uniq}'
                                        style='{$a_view_style}text-decoration:none;'>{$title}
                                    </a>
                                </td>";
                        }
                    }
                }
                echo "</tr>";
            }

            // �ǽ��Ԥء����Ф���Ʊ�����򥻥åȡ�ɽ���������ʤ������
            echo "<th style='font-size:0pt; width:48;' class='winbox' nowrap>��</th>";
            for( $f=1; $f<$f_idx; $f++ ) {
                echo "<th style='font-size:0pt; width:{$th_width};' class='winbox' nowrap>��</th>";
            }
            ?>
            </table>
        </div>
        </td></tr> <!-- ���ߡ� -->
    </table>

    </form>
</center>
</body>

<?php echo $menu->out_alert_java()?>
</html>
