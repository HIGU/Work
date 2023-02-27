<?php
//////////////////////////////////////////////////////////////////////////////
// ���ʬ�����칩�� ̤����ʬ���������ɤ��Ƥ���ǡ�����Ȳ񤹤�          //
// Copyright (C) 2004-2015 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2018/08/29 Created  sales_miken_view.php                                 //
//                     ������˥塼���饳�ԡ���������ξ��ľ������           //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../function.php');                // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../tnk_func.php');                // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../MenuHeader.php');              // TNK ������ menu class
require_once ('../../ControllerHTTP_Class.php');    // TNK ������ MVC Controller Class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(1, 90);          // site_index=30(������˥塼) site_id=30(NK̤��������)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(INDUST_MENU);             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('���칩�� ���ʴ���Ǽ��ʬ ̤���� ����');
//////////// ɽ�������
$menu->set_caption('��Ω����ʬ ̤��������ɽ');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('��������ɽ��ɽ��',   INDUST . 'material/allo_conf_parts_view.php');
// $menu->set_action('����������Ͽ',     INDUST . 'material/materialCost_entry.php');
//////////// �꥿���󥢥ɥ쥹�ؤ�GET�ǡ��������å�
$menu->set_retGET('page_keep', 'on');

//////////// JavaScript Stylesheet File ��ɬ���ɤ߹��ޤ���
$uniq = $menu->set_useNotCache('miken');
//////////// �ƥ����ȥե����뤫�����٤μ����ڤӹ�ץ쥳���ɿ�����(�оݥơ��֥�κ������ڡ�������˻���)
$file_orign    = '../..' . SYS . 'backup/W#TIUKSL.TXT';
$res           = array();
$total_price   = 0;
$total_price_c = 0;
$total_price_l = 0;
$total_price_t = 0;
$total_num     = 0;
$total_num_c   = 0;
$total_num_l   = 0;
$total_num_t   = 0;
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    $fp = fopen($file_orign, 'r');
    $rec = 0;       // �쥳���ɭ�
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 130, '_');     // �¥쥳���ɤ�103�Х��ȤʤΤǤ���ä�;͵��ǥ�ߥ���'_'�����
        if (feof($fp)) {
            break;
        }
        $num  = count($data);       // �ե�����ɿ��μ���
        if ($num != 14) {   // AS¦�κ���쥳���ɤ� php-4.3.5��0�֤� php-4.3.6��1���֤����ͤˤʤä���fgetcsv�λ����ѹ��ˤ��
           continue;
        }
        for ($f=0; $f<$num; $f++) {
            $res[$rec][$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJIS��EUC-JP���Ѵ�
            $res[$rec][$f] = addslashes($res[$rec][$f]);    // "'"�����ǡ����ˤ������\�ǥ��������פ���
            // $data_KV[$f] = mb_convert_kana($data[$f]);   // Ⱦ�ѥ��ʤ����ѥ��ʤ��Ѵ�
        }
        if($res[$rec][5] !='C8385407') {
            $query = sprintf("select midsc from miitem where mipn='%s' limit 1", $res[$rec][3]);
            getUniResult($query, $res[$rec][4]);       // ����̾�μ��� (���ʥ����ɤ��񤭤���)
            /******** ����������Ͽ�Ѥߤι����ɲ� *********/
            $sql = "
                SELECT plan_no FROM material_cost_header WHERE plan_no='{$res[$rec][5]}'
            ";
            if (getUniResult($sql, $temp) <= 0) {
                $res[$rec][13] = '��Ͽ';
                $sql_c = "
                    SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE assy_no = '{$res[$rec][3]}' ORDER BY assy_no DESC, regdate DESC LIMIT 1
                ";
                if (($rows_c = getResultWithField3($sql_c, $field_c, $res_c)) <= 0) {
                } else {
                }
            } else {
                $res[$rec][13] = '��Ͽ��';
                $sql_c = "
                    SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE plan_no='{$res[$rec][5]}' AND assy_no = '{$res[$rec][3]}' ORDER BY assy_no DESC, regdate DESC LIMIT 1
                ";
                if (($rows_c = getResultWithField3($sql_c, $field_c, $res_c)) <= 0) {
                } else {
                }
            }
            /******** ����ɸ��ι����ɲ� *********/
            $sql2 = "
                SELECT substr(note15, 1, 2) FROM assembly_schedule WHERE plan_no='{$res[$rec][5]}'
            ";
            $sc = '';
            getUniResult($sql2, $sc);
            if ($sc == 'SC') {
                $res[$rec][15] = '����';
            } else {
                $res[$rec][15] = 'ɸ��';
            }
            /******** ����ñ�������ǡ����ˤʤ����ξ�񤭽��� *********/
            if ($res[$rec][12] == 0) {                                  // ���ǡ����˻��ڤ����뤫�ɤ���
                $res[$rec][14] = '1';
                $sql = "
                    SELECT price FROM sales_price_nk WHERE parts_no='{$res[$rec][3]}'
                ";
                if (getUniResult($sql, $sales_price) <= 0) {            // �ǿ����ڤ���Ͽ����Ƥ��뤫
                    $sql = "
                        SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE plan_no='{$res[$rec][5]}' AND assy_no = '{$res[$rec][3]}' ORDER BY assy_no DESC, regdate DESC LIMIT 1
                    ";
                    if (getUniResult($sql, $sales_price) <= 0) {        // �ײ�����������Ͽ����Ƥ��뤫
                        $sql_c = "
                            SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE assy_no = '{$res[$rec][3]}' ORDER BY assy_no DESC, regdate DESC LIMIT 1
                        ";
                        if (getUniResult($sql, $sales_price) <= 0) {    // ���ʤ����������Ͽ����Ƥ��뤫
                            $res[$rec][12] = 0;
                        } else {
                            if ($res[$rec][15] == '����') {
                                $res[$rec][12] = round(($sales_price * 1.27), 2);   // ����ΤȤ�����Ψ��
                            } else {
                                $res[$rec][12] = round(($sales_price * 1.13), 2);
                            }
                        }
                    } else {
                        if ($res[$rec][15] == '����') {
                            $res[$rec][12] = round(($sales_price * 1.27), 2);       // ����ΤȤ�����Ψ��
                        } else {
                            $res[$rec][12] = round(($sales_price * 1.13), 2);
                        }
                    }
                } else {
                    $res[$rec][12] = $sales_price;
                }
            } else {
                $res[$rec][14] = '0';
            }
            /******** ���� �׻� *********/
            $res[$rec][16] = round(($res[$rec][11] * $res[$rec][12]), 0);
            $total_price  += $res[$rec][16];
            $total_num    += 1;
            if ($res[$rec][0] == 'C') {
                $total_price_c += $res[$rec][16];
                $total_num_c   += 1;
            } elseif ($res[$rec][0] == 'L') {
                $total_price_l += $res[$rec][16];
                $total_num_l   += 1;
            } else {
                $total_price_t += $res[$rec][16];
                $total_num_t   += 1;
            }
            $rec++;
        }
    }
    $maxrows = $rec;
    $rec    -= 1;
    $rows    = $maxrows;    // ����Ϲ�ץ쥳���ɿ���ɽ���ѥ쥳���ɿ���Ʊ��
    $field   = array(0=>'������', 1=>'������', 3=>'�����ֹ�', 4=>'����̾', 5=>'�ײ��ֹ�', 11=>'������', 12=>'����ñ��');
} else {
    header("Location: $url_referer");                   // ľ���θƽи������
    $_SESSION['s_sysmsg'] .= '̤�������٤Υե����뤬����ޤ���';  // .= ��å��������ɲä���
    exit();
}
$f_total_price   = number_format($total_price, 0);
$f_total_price_c = number_format($total_price_c, 0);
$f_total_price_l = number_format($total_price_l, 0);
$f_total_price_t = number_format($total_price_t, 0);
// ����������
$f_total_num   = number_format($total_num, 0);
$f_total_num_c = number_format($total_num_c, 0);
$f_total_num_l = number_format($total_num_l, 0);
$f_total_num_t = number_format($total_num_t, 0);
//$menu->set_caption2("<u>���ץ�̤�������={$f_total_price_c}����˥�̤�������={$f_total_price_l}���ġ���̤�������={$f_total_price_t}�����̤�������={$f_total_price}<u>");
$menu->set_caption2("<u>���ץ�̤�������={$f_total_num_c}����={$f_total_price_c}�ߡ���˥�̤�������={$f_total_num_l}����={$f_total_price_l}�ߡ����̤�������={$f_total_num}����={$f_total_price}��<u>");

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
<link rel='stylesheet' href='sales_miken.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<!-- <script type='text/javascript' src='sales_miken.js?<?php echo $uniq ?>'></script> -->
</head>
<!-- background-color:#d6d3ce; -->
<body style='overflow-y:hidden;'>
<center>
<?=$menu->out_title_border()?>
        
        <!----------------- ������ ���� ���� �Υե����� ---------------->
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <tr>
                <td align='center' class='caption_font'>
                    <?= $menu->out_caption(), "\n" ?>
                    ��
                    
                </td>
            </tr>
        </table>
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <tr>
                <td nowrap align='center' class='caption_font'>
                    <?php echo $menu->out_caption2(), "\n" ?>
                </td>
            </tr>
        </table>
<?php
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='sales_miken_ViewHeader.html?{$uniq}' name='header' align='center' width='98%' height='32' title='����'>\n";
echo "    ���ܤ�ɽ�����Ƥ��ޤ���\n";
echo "</iframe>\n";
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='sales_miken_ViewBody.php?", $_SERVER['QUERY_STRING'], "&{$uniq}#last' name='list' align='center' width='98%' height='81%' title='����'>\n";
echo "    ������ɽ�����Ƥ��ޤ���\n";
echo "</iframe>\n";
?>
</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
