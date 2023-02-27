<?php
//////////////////////////////////////////////////////////////////////////////
// �����ӥ���� ������ ���� �ȼ�Template�����                              //
// Copyright(C) 2003-2012      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp  //
// Changed history                                                          //
// 2003/10/17 Created   service_percentage_input.php                        //
// 2003/10/21 ľ������(���롼��)��ޥ������������å��夫������          //
// 2003/10/22 HTML �ط���ƥ�ץ졼�����ˤ����ʬ���� include_once ����     //
//     JavaScript������ľ�ܸƤФ줿���HTTP_REFERER�ˤ�menu_frame������     //
// 2003/10/27 service_percent_history�� intext�ե�����ɤ��ɲä���¸        //
// 2003/11/05 �����Ѥߥ����å��Υ��å����ɲ�                          //
// 2003/11/12 ������������ɽ�����ѹ� order_no�ˤ��ɽ������ѹ�       //
//            div(������)section(������)order_no(ɽ����)note(����)���ɲ�    //
// 2004/04/19 (�������)��������������ä��Τ�Ŀ���ˤ����Ͻ����褦���ѹ�//
//                          �����μ���ɽ���򥰥졼���ѹ�                    //
// 2007/01/24 MenuHeader���饹�б�                                          //
// 2012/10/06 �軻��������ԡ��������ʤ��Τ���                     ��ë //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
ini_set('error_reporting',E_ALL);           // E_ALL='2047' debug ��
// ini_set('display_errors','1');              // Error ɽ�� ON debug �� 
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../function.php');
require_once ('../../tnk_func.php');
require_once ('../../MenuHeader.php');      // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(10,  5);                    // site_index=10(»�ץ�˥塼) site_id=5(�����ӥ�����˥塼)

$current_script  = $_SERVER['PHP_SELF'];        // ���߼¹���Υ�����ץ�̾����¸

//////// ��ʬ���ȤθƽФ��λ��ϥ��å�������¸���ʤ�
if ( preg_match('/service_percentage_input.php/', $_SERVER['HTTP_REFERER']) ) {
    $url_referer = $_SESSION['service_input_referer'];
// } elseif ( preg_match('/menu_frame.php/', $_SERVER['HTTP_REFERER']) ) { // ����Ͼ�����ݾڤ�̵���Τǥ�����
} elseif (isset($_GET['view'])) {
    $_SESSION['service_input_referer'] = $_SESSION['service_view_referer'];   // view����ƤФ줿�Τ�
    $url_referer = $_SESSION['service_view_referer'];                   // view����ƤФ줿�Τ�
} else {
    $_SESSION['service_input_referer'] = $_SERVER['HTTP_REFERER'];  // �ƽФ�Ȥ�URL�򥻥å�������¸
    $url_referer = $_SESSION['service_input_referer'];
}
////////////// �꥿���󥢥ɥ쥹����(���л��ꤹ����)
$menu->set_RetUrl($url_referer);        // �嵭�η�̤򥻥å�
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('test_template',   SYS . 'log_view/php_error_log.php');

///////////// �о�����μ���
if (isset($_POST['section_id'])) {
    $_SESSION['service_id']   = $_POST['section_id'];
    $_SESSION['section_name'] = $_POST['section_name'];
    $section_id   = $_POST['section_id'];
    $section_name = $_POST['section_name'];
} else {
    $section_id   = $_SESSION['service_id'];
    $section_name = $_SESSION['section_name'];
}

//////////// �о�ǯ��Υ��å����ǡ�������
if (isset($_SESSION['service_ym'])) {
    $service_ym = $_SESSION['service_ym']; 
} else {
    $service_ym = date('Ym');        // ���å����ǡ������ʤ����ν����(����)
    if (substr($service_ym,4,2) != 01) {
        $service_ym--;
    } else {
        $service_ym = $service_ym - 100;
        $service_ym = $service_ym + 11;   // ��ǯ��12��˥��å�
    }
}

//////////// ����ǡ����μ����������
if (substr($service_ym,4,2) != 01) {
    $before_ym = $service_ym - 1;
} else {
    $before_ym = $service_ym - 100;
    $before_ym = $before_ym + 11;   // ��ǯ��12��˥��å�
}
if (substr($service_ym,6,2) == '32') {
    $before_ym = substr($service_ym,0,6);
}
////////////// �����ѤߤΥ����å�
if ( file_exists("final/$service_ym") ) {
    $_SESSION["s_sysmsg"] = "{$service_ym}���ϴ��˳����������Ƥ��ޤ���";
    header("Location: $url_referer");                   // ľ���θƽи������
    exit();
}

//////////// �����ȥ�����ա���������
$today = date("Y/m/d H:i:s");
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
if (substr($service_ym,6,2) == '32') {
    $view_ym = substr($service_ym,0,6) . '�軻';
} else {
    $view_ym = $service_ym;
}
if (isset($_POST['check'])) {   // ��Ͽ�γ�ǧ
    $menu_title = "$view_ym �����ӥ���� $section_name ���� ��Ͽ��ǧ";
} else {                        // ������ϥե�����
    $menu_title = "$view_ym �����ӥ���� $section_name ���� ����";
}
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title($menu_title);

///// ��Ⱦ���� ǯ��λ���
$yyyy = substr($service_ym, 0,4);
$mm   = substr($service_ym, 4,2);
if (($mm >= 1) && ($mm <= 3)) {
    $yyyy = ($yyyy - 1);
    $zenki_ym = $yyyy . '09';     // ����ǯ��
} elseif (($mm >= 10) && ($mm <= 12)) {
    $zenki_ym = $yyyy . '09';     // ����ǯ��
} else {
    $zenki_ym = $yyyy . '03';     // ����ǯ��
}

////////// �ǡ����١����ؤ���³
if ( !($con = db_connect()) ) {
    $_SESSION['s_sysmsg'] = '�ǡ����١�������³�Ǥ��ޤ���';
    header("Location: $url_referer");                   // ľ���θƽи������
    exit();
}

//////////// �������������(�Ŀ�)��ȴ�Ф�
$query = sprintf("select act.act_id as ������, s_name as ��������, cd.uid as �Ұ��ֹ�, d.name as ̾��
        from cate_allocation left outer join act_table as act
            on dest_id=act.act_id
        left outer join cd_table as cd
            on act.act_id=cd.act_id
        left outer join user_detailes as d
            on cd.uid=d.uid
        where orign_id=0 and
            group_id=%d and
            act_flg='f'
        order by act.act_id",
        $section_id);
$res = array();
if (($rows = getResWithFieldTrs($con, $query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] = '�����������٤������Ǥ��ޤ���';
    header("Location: $url_referer");                   // ľ���θƽи������
    exit();
} else {
    $num = count($field);       // �ե�����ɿ�����
    $query = "select item, item_no, intext, div, section, order_no, note from service_item_master
              order by intext, order_no";
    $_SESSION['s_sysmsg'] = '';     // �����
    if ( ($rows_item=getResultTrs($con, $query, $res_item)) <= 0) {
        $_SESSION['s_sysmsg'] = 'ľ������Υޥ������������Ǥ��ޤ���';
        header("Location: $url_referer");                   // ľ���θƽи������
        exit();
    } else {
        for ($i=$num; $i<($rows_item+$num); $i++) {
            $field[$i]        = $res_item[$i-$num][0];      // ɽ���ѥե������
            $intext2[$i]      = $res_item[$i-$num][2];      // 2003/11/12 ��ʬ��ɽ���Τ�����ɲ�
            
            $item[$i-$num]     = $res_item[$i-$num][0];      // �ʲ�����Ͽ��
            $item_no[$i-$num]  = $res_item[$i-$num][1];
            $intext[$i-$num]   = $res_item[$i-$num][2];
            $div[$i-$num]      = $res_item[$i-$num][3];
            $section[$i-$num]  = $res_item[$i-$num][4];
            $order_no[$i-$num] = $res_item[$i-$num][5];
            $note[$i-$num]     = $res_item[$i-$num][6];
        }
        $field[$i]   = '�硡��';
        $num_p = count($field);     // �ե�����ɿ����� num_p = num+��ά
    }
}

///////////// �������Ӥ����
for ($r=0; $r<$rows; $r++) {
    $zenki[$r]['���'] = 0;
    for ($f=0; $f<$rows_item; $f++) {
        $query = sprintf("select percent from service_percent_history
                where service_ym=%d and act_id=%d and uid='%s' and item_no=%d", $zenki_ym . '32', $res[$r][0], $res[$r][2], $item_no[$f]);
        if (getUniResult($query, $res_user) > 0) {
            $zenki[$r][$f]      = ($res_user * 100);    // ����Ѵ�
            $zenki[$r]['���'] += $zenki[$r][$f];
        } else {
            $zenki[$r][$f] = 0;
        }
    }
}
///////////// ������Ӥ����
for ($r=0; $r<$rows; $r++) {
    $zengetsu[$r]['���'] = 0;
    for ($f=0; $f<$rows_item; $f++) {
        $query = sprintf("select percent from service_percent_history
                where service_ym=%d and act_id=%d and uid='%s' and item_no=%d", $before_ym, $res[$r][0], $res[$r][2], $item_no[$f]);
        if (getUniResult($query, $res_user) > 0) {
            $zengetsu[$r][$f]      = ($res_user * 100);    // ����Ѵ�
            $zengetsu[$r]['���'] += $zengetsu[$r][$f];
        } else {
            $zengetsu[$r][$f] = 0;
        }
    }
}

////////////// ��ǧ�Ѥμ¹ԥܥ��󤬲����줿��
if (isset($_POST['check'])) {
    unset($_SESSION['percent']);
    $i = 0;     // ���󥤥�ǥå���
    for ($r=0; $r<$rows; $r++) {
        $percent[$r]['���'] = 0;
        $_SESSION['percent'][$r]['���'] = 0;
        for ($f=0; $f<$rows_item; $f++) {
            if (isset($_POST['percent'][$i])) {
                if ($_POST['percent'][$i] == "") {
                    $percent[$r][$f] = '';      // 0��ɽ�������ʤ��褦�ˤ��뤿��
                } else {
                    $percent[$r][$f] = $_POST['percent'][$i];
                }
                $_SESSION['percent'][$r][$f]      = $_POST['percent'][$i];
                $_SESSION['percent'][$r]['���'] += $_POST['percent'][$i];
                $percent[$r]['���'] += $_POST['percent'][$i];
            } else {
                $percent[$r][$f] = 'Not';
            }
            $i++;
        }
        if ($percent[$r]['���'] == 100) {
            $_SESSION['percent'][$r]['��Ͽ'] = 'yes';   // ������Ͽ�б��Τ����ɲ�
        } elseif ($percent[$r]['���'] == 0) {
            $_SESSION['percent'][$r]['��Ͽ'] = 'no';    // 0=���Ϥ��Ƥ��ʤ��ȸ��ʤ���������
        } else {
            $_SESSION['s_sysmsg'] .= "{$res[$r][3]}����ι�פ�100�Ǥʤ�{$percent[$r]['���']}�Ǥ���<br>"; // ��פΥ��顼
            unset($_POST['check']);
            $_POST['repair'] = '����';
        }
    }
//////////////////// �����ܥ��󤬲����줿��
} elseif (isset($_POST['repair'])) {
    if (isset($_SESSION['percent'])) {
        for ($r=0; $r<$rows; $r++) {
            for ($f=0; $f<$rows_item; $f++) {
                $percent[$r][$f] = $_SESSION['percent'][$r][$f];
            }
            $percent[$r]['���'] = $_SESSION['percent'][$r]['���'];
        }
    } else {
        for ($r=0; $r<$rows; $r++) {
            for ($f=0; $f<$rows_item; $f++) {
                $percent[$r][$f] = '';
            }
            $percent[$r]['���'] = '��';
        }
    }
//////////////////// ����ǡ����Υ��ԡ��ܥ��󤬲����줿��
} elseif (isset($_POST['before'])) {
    for ($r=0; $r<$rows; $r++) {
        $percent[$r]['���'] = 0;
        for ($f=0; $f<$rows_item; $f++) {
            $query = sprintf("select percent from service_percent_history
                    where service_ym=%d and act_id=%d and uid='%s' and item_no=%d", $before_ym, $res[$r][0], $res[$r][2], $item_no[$f]);
            if (getUniResult($query, $res_user) > 0) {
                $percent[$r][$f]      = ($res_user * 100);    // ����Ѵ�
                $percent[$r]['���'] += $percent[$r][$f];
            } else {
                $percent[$r][$f] = 0;
            }
        }
    }
    //for ($r=0; $r<$rows; $r++) {
    //    $percent[$r]['���'] = 0;   // �����
    //    for ($f=0; $f<$rows_item; $f++) {
    //        ///// ��Ͽ�ѤߤΥ����å�
    //        $query = sprintf("select percent from service_percent_history
    //                where service_ym=%d and act_id=%d and uid='%s' and item_no=%d", $service_ym, $res[$r][0], $res[$r][2], $item_no[$f]);
    //        if (getUniResTrs($con, $query, $res_pert) > 0) {
    //            $percent[$r][$f]      = ($res_pert * 100);      // ����Ѵ�
    //            $percent[$r]['���'] += $percent[$r][$f];
    //        } else {
    //            $percent[$r][$f] = '';
    //        }
    //    }
    //}
//////////////////// ��Ͽ�ܥ��󤬲����줿��
} elseif (isset($_POST['save'])) {
    query_affected_trans($con, 'begin');    // �ȥ�󥶥������γ���
    for ($r=0; $r<$rows; $r++) {
        if ($_SESSION['percent'][$r]['��Ͽ'] == 'yes') {    // ������Ͽ�б��Τ����ɲ�
            for ($f=0; $f<$rows_item; $f++) {
                $percent[$r][$f] = ($_SESSION['percent'][$r][$f] / 100);    // ��Τ����Ѵ�
                ///// ��Ͽ�ѤߤΥ����å�
                $query = sprintf("select percent from service_percent_history
                        where service_ym=%d and act_id=%d and uid='%s' and item_no=%d", $service_ym, $res[$r][0] , $res[$r][2], $item_no[$f]);
                if (getUniResTrs($con, $query, $res_pert) <= 0) {
                    ///// ̤��Ͽ insert
                    $query = sprintf("INSERT INTO service_percent_history (service_ym, act_id, uid, item_no, intext, item, percent, div, section, order_no, note)
                             values (%d, %d, '%s', %d, %d, '%s', %1.2f, '%s', '%s', %d, '%s')",
                             $service_ym, $res[$r][0], $res[$r][2], $item_no[$f], $intext[$f], $item[$f], $percent[$r][$f],
                             $div[$f], $section[$f], $order_no[$f], $note[$f]);
                    if (query_affected_trans($con, $query) <= 0) {
                        $_SESSION['s_sysmsg'] .= "{$res[$r][3]}�������Ͽ�˼��ԡ�";
                        query_affected_trans($con, 'rollback');         // Rollback
                        header("Location: $url_referer");               // ľ���θƽи������
                        exit();
                    }
                } else {
                    ///// ��Ͽ�� update
                    $query = "UPDATE service_percent_history SET percent={$percent[$r][$f]}, item='{$item[$f]}', intext={$intext[$f]},
                              div='{$div[$f]}', section='{$section[$f]}', order_no={$order_no[$f]}, note='{$note[$f]}'
                              where service_ym=$service_ym and act_id={$res[$r][0]} and uid='{$res[$r][2]}' and item_no={$item_no[$f]}";
                    if (query_affected_trans($con, $query) <= 0) {
                        $_SESSION['s_sysmsg'] .= "{$res[$r][3]}������ѹ��˼��ԡ�";
                        query_affected_trans($con, 'rollback');         // Rollback
                        header("Location: $url_referer");               // ľ���θƽи������
                        exit();
                    }
                }
            }
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>{$res[$r][3]}�������Ͽ���ޤ�����<br></font>";
        }
    }
    query_affected_trans($con, 'commit');    // �ȥ�󥶥������ν�λ
    // $_SESSION['s_sysmsg'] .= "<font color='white'>���ƴ�λ��</font>";
    header("Location: $url_referer");               // ľ���θƽи������
    exit();
/////////////////////// ������ϥե�����
} else {
    for ($r=0; $r<$rows; $r++) {
        $percent[$r]['���'] = 0;   // �����
        for ($f=0; $f<$rows_item; $f++) {
            ///// ��Ͽ�ѤߤΥ����å�
            $query = sprintf("select percent from service_percent_history
                    where service_ym=%d and act_id=%d and uid='%s' and item_no=%d", $service_ym, $res[$r][0], $res[$r][2], $item_no[$f]);
            if (getUniResTrs($con, $query, $res_pert) > 0) {
                $percent[$r][$f]      = ($res_pert * 100);      // ����Ѵ�
                $percent[$r]['���'] += $percent[$r][$f];
            } else {
                $percent[$r][$f] = '';
            }
        }
    }
}

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();

if (isset($_POST['check'])) {           // ��ǧ�Ѥμ¹ԥܥ��󤬲����줿��
    include_once ('templates/service_percentage_check.templ.html');
} elseif (isset($_POST['repair'])) {    // �����ܥ��󤬲����줿��
    include_once ('templates/service_percentage_input.templ.html');
} else {                                // ������ϥե�����
    include_once ('templates/service_percentage_input.templ.html');
}
?>
<?php ob_end_flush(); // ���ϥХåե���gzip���� END ?>
