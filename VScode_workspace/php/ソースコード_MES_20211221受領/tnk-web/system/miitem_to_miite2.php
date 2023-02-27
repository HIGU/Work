<?php
//////////////////////////////////////////////////////////////////////////
// miitem �� miitem2 �إǡ�������С��� ���ѥ��ʤ�Ⱦ�ѥ��ʤ�            //
// 2003/12/22 Copyright(C) 2003 K.Kobayashi tnksys@nitto-kohki.co.jp    //
// �ѹ�����                                                             //
// 2003/12/22 �������� miitem_to_miitem2.php                            //
//              table�߷פ�����ѹ� regdate���ɲ� without time zoen��� //
//////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);       // E_ALL='2047' debug ��
// ini_set('display_errors','1');          // Error ɽ�� ON debug �� ��꡼���女����
ini_set('max_execution_time', 1200);    // ����¹Ի���=20ʬ
session_start();                    // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
// ob_start('ob_gzhandler');               // ���ϥХåե���gzip����

require_once ('/home/www/html/tnk-web/function.php');
require_once ('/home/www/html/tnk-web/tnk_func.php');
// access_log();                       // Script Name �ϼ�ư����



// �����ƥ�ޥ����� ��ñ�̽��� �������
$file_name = "/home/www/html/weekly/Q#MIITEM.CSV";
$file_temp = "/home/www/html/weekly/Q#MIITEM.tmp";
$file_write = "/home/www/html/weekly/Q#MIITEM.txt";
if (file_exists($file_name)) {            // �ե������¸�ߥ����å�
    $fp = fopen($file_name,"r");
    $fpw = fopen($file_temp,"a");
    while (1) {
        $data=fgets($fp,200);
        $data = mb_convert_encoding($data, "EUC-JP", "SJIS");       // SJIS��EUC-JP���Ѵ�
        $data = mb_convert_kana($data, 'ka', 'EUC-JP');     // ���ѥ��ʤ�Ⱦ�ѥ��ʤ��Ѵ�
        fwrite($fpw,$data);
        $c++;
        if (feof($fp)) {
            $c--;
            break;
        }
    }
    fclose($fp);
    fclose($fpw);
    $fp = fopen($file_temp,"r");
    $fpw = fopen($file_write,"a");
    $str_flg = 0;       // ʸ����ե�������⤫�ɤ����Υե饰
    while (FALSE!==($data = fgetc($fp)) ) {           // ��ʸ�� �ɹ�
        switch ($data) {
        case '"':
            if ($str_flg == 0) {
                $str_flg = 1;       // ʸ������ե�����ɤ˥��å�
            } else {
                $str_flg = 0;       // ʸ���󳰥ե�����ɤ˥��å�
            }
            break;
        case ',':
            if ($str_flg == 0)           // ʸ���󳰤� ',' ����ޤʤ饿�֤��ѹ�
                fwrite($fpw,"\t");
            else
                fwrite($fpw,$data); // ʸ������� ',' ����ޤʤ餽�Τޤ޽񤭹���
            break;
        case "\r":
            fwrite($fpw,"\t\\N\t\\N\r");      // last_date last_user ʬ�� \t \N �ˤ��ƽ����
            $str_flg = 0;   // CR �򸡽Ф�����ʸ����ե饰��ꥻ�å�(������"���к�)
            break;
        default:
            fwrite($fpw,$data);
            break;
        }
    }
    fclose($fp);
    fclose($fpw);
    unlink($file_name);     // ����ե�������� CSV
    unlink($file_temp);     // ����ե�������� tmp
}

// �����ƥ�ޥ����� ��ñ�̽���
$file_name = "/home/www/html/weekly/Q#MIITEM.txt";
if (file_exists($file_name)) {            // �ե������¸�ߥ����å�
    $rowcsv = 0;        // read file record number
    $row_in = 0;        // insert record number �����������˥�����ȥ��å�
    $row_up = 0;        // update record number   ��
    $miitem_ng_flg = FALSE;      // �ģ½���ߣΣǥե饰
    if ( ($fp = fopen($file_name, 'r')) ) {
        /////////// begin �ȥ�󥶥�����󳫻�
        if ( !($con = db_connect()) ) {
            $msg .= "�ǡ����١�������³�Ǥ��ޤ���<br>";
        } else {
            query_affected_trans($con, 'begin');
            while ($data = fgetcsv($fp, 200, "\t")) {
                // $num = count($data);     // CSV File �� field ��
                $rowcsv++;
                $data[1] = addslashes($data[1]);    // "'"�����ǡ����ˤ������\�ǥ��������פ���
                // ���ߤ�ɬ�פʤ� $data[1] = trim($data[1]);          // ����̾������Υ��ڡ�������
                ///////// ��Ͽ�ѤߤΥ����å�
                $query_chk = sprintf("select mipn from miitem where mipn='%s'", $data[0]);
                if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
                    ///// ��Ͽ�ʤ� insert ����
                    $query = sprintf("insert into miitem (mipn, midsc, mzist, mepnt, madat)
                            values('%s','%s','%s','%s',%d)", $data[0],$data[1],$data[2],$data[3],$data[4]);
                    if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                        $msg .= "miitem insert error rec No.=$rowcsv <br>";
                        $miitem_ng_flg = TRUE;
                        break;          // NG �Τ���ȴ����
                    } else {
                        $row_in++;      // insert ����
                    }
                } else {
                    ///// ��Ͽ���� update ����
                    $query = sprintf("update miitem set mipn='%s', midsc='%s', mzist='%s', mepnt='%s', madat=%d
                            where mipn='%s'", $data[0], $data[1], $data[2], $data[3], $data[4], $data[0]);
                    if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                        $msg .= "miitem update error rec No.=$rowcsv <br>";
                        $miitem_ng_flg = TRUE;
                        break;          // NG �Τ���ȴ����
                    } else {
                        $row_up++;      // update ����
                    }
                }
            }
        }
        /////////// commit �ȥ�󥶥������λ
        if ($miitem_ng_flg) {
            query_affected_trans($con, "rollback");     // transaction rollback
        } else {
            query_affected_trans($con, "commit");       // ����ߴ�λ
        }
    } else {
        $msg .= "Q#MIITEM.txt�򥪡��ץ����ޤ���<br>";
    }
    /**********
      ///////////////////////////////////////////////// stderr(2)����stdout(1)�� 2>&1
    $result2 = `/usr/local/pgsql/bin/psql TnkSQL < /home/www/html/weekly/qmiitem 2>&1`;
    unlink($file_name);
    ***********/
    fclose($fp);
    if ( !($miitem_ng_flg) ) {
        unlink($file_name);     // ����ե�������� txt
    }
    $flag2 = 1;
}



// ���ʻų�(�����ºݤȴ���ɸ��)���ޥ꡼�ե����� �ºݶ�ۤ�Ĥ��ि��Υե�����Ȥ��ƻ��� �������
$file_name = "/home/www/html/monthly/Q#SGKSIKP.CSV";
$file_temp = "/home/www/html/monthly/Q#SGKSIKP.tmp";
$file_write = "/home/www/html/monthly/Q#SGKSIKP.txt";
if (file_exists($file_name)) {            // �ե������¸�ߥ����å�
    $fp = fopen($file_name,"r");
    $fpw = fopen($file_temp,"a");
    while (1) {
        $data=fgets($fp,200);
        $data = mb_convert_encoding($data, "EUC-JP", "SJIS");       // SJIS��EUC-JP���Ѵ�
        //  Ⱦ�ѥ��ʥǡ����ʤ� $data_KV = mb_convert_kana($data);           // Ⱦ�ѥ��ʤ����ѥ��ʤ��Ѵ�
        fwrite($fpw,$data);
        $d++;
        if (feof($fp)) {
            $d--;
            break;
        }
    }
    fclose($fp);
    fclose($fpw);
    $fp = fopen($file_temp,"r");
    $fpw = fopen($file_write,"a");
    while (FALSE!==($data = fgetc($fp)) ) {           // ��ʸ�� �ɹ�
        switch ($data) {
        case '"':
            break;
        case ',':
            fwrite($fpw,"\t");
            break;
        case "\r":
            fwrite($fpw,"\t\\N\t\\N\r");      // last_date last_user ʬ�� \t \N �ˤ��ƽ����
            break;
        default:
            fwrite($fpw,$data);
            break;
        }
    }
    fclose($fp);
    fclose($fpw);
    unlink($file_name);     // ����ե�������� CSV
    unlink($file_temp);     // ����ե�������� tmp
}

// ���ʻų�(�����ºݤȴ���ɸ��)���ޥ꡼�ե�����  ��ñ�̽���
$file_name = "/home/www/html/monthly/Q#SGKSIKP.txt";
if (file_exists($file_name)) {            // �ե������¸�ߥ����å�
    //////////////////////////////////////////////////// stderr(2)����stdout(1)�� 2>&1
    $result3 = `/usr/local/pgsql/bin/psql TnkSQL < /home/www/html/monthly/sgksikp 2>&1`;
    unlink($file_name);     // ����ե�������� txt
    $flag3 = 1;
}



// ϫ̳�񡦷��񥵥ޥ꡼�ե����� download �������
$file_name = "/home/www/html/monthly/AAYLAWL2.CSV";
$file_temp = "/home/www/html/monthly/AAYLAWL2.tmp";
$file_write = "/home/www/html/monthly/aaylawl2.txt";
if (file_exists($file_name)) {            // �ե������¸�ߥ����å�
    $fp = fopen($file_name,"r");
    $fpw = fopen($file_temp,"a");
    while (1) {
        $data=fgets($fp,200);
        $data = mb_convert_encoding($data, "EUC-JP", "SJIS");       // SJIS��EUC-JP���Ѵ�
        //  Ⱦ�ѥ��ʥǡ����ʤ� $data_KV = mb_convert_kana($data);           // Ⱦ�ѥ��ʤ����ѥ��ʤ��Ѵ�
        fwrite($fpw,$data);
        $e++;
        if (feof($fp)) {
            $e--;
            break;
        }
    }
    fclose($fp);
    fclose($fpw);
    $fp = fopen($file_temp,"r");
    $fpw = fopen($file_write,"a");
    while (FALSE!==($data = fgetc($fp)) ) {           // ��ʸ�� �ɹ�
        switch ($data) {
        case '"':
            break;
        case ',':
            fwrite($fpw,"\t");
            break;
        case "\r":
            fwrite($fpw,"\t\\N\t\\N\r");      // last_date last_user ʬ�� \t \N �ˤ��ƽ����
            break;
        default:
            fwrite($fpw,$data);
            break;
        }
    }
    fclose($fp);
    fclose($fpw);
    unlink($file_name);     // ����ե�������� CSV
    unlink($file_temp);     // ����ե�������� tmp
}

// ϫ̳�񡦷��񥵥ޥ꡼�ե�����  ��ñ�̽��� �ܺ��
$file_name = "/home/www/html/monthly/aaylawl2.txt";
if (file_exists($file_name)) {            // �ե������¸�ߥ����å�
    //////////////////////////////////////////////////////////////////// stderr(2)����stdout(1)�� 2>&1
    $result4 = `/usr/local/pgsql/bin/psql TnkSQL < /home/www/html/monthly/act_summary 2>&1`;
    unlink($file_name);     // ����ե�������� txt
    $flag4 = 1;
}



// ��å��������֤�
if ($flag1==1) {
    $msg .= "���ǡ������ɲä��ޤ�����<br>";
    $msg .= $b . "��<br>";
    $msg .= $result1 . "<br>";
} else {
    $msg .= "���ǡ������ɲåǡ���������ޤ���<br><br>";
}
if ($flag2==1) {
    $msg .= "�����ƥ�ޥ���������<br>";
    $msg .= "insert $row_in ��<br>";
    $msg .= "update $row_up ��<br>";
    $msg .= "CSV_file $rowcsv ��<br>";
    $msg .= "Original $c ��<br><br>";
    // $msg .= $c . "��<br>";
    // $msg .= $result2 . "<br>";
} else {
    $msg .= "�����ƥ�ޥ��������ɲåǡ���������ޤ���<br><br>";
}
if ($flag3==1) {
    $msg .= "���ʻųݥ��ޥ꡼�ե�������ɲä��ޤ�����<br>";
    $msg .= $d . "��<br>";
    $msg .= $result3 . "<br>";
} else {
    $msg .= "SGKSIKP���ɲåǡ���������ޤ���<br><br>";
}
if ($flag4==1) {
    $msg .= "ϫ̳�񡦷��񥵥ޥ꡼�ե�������ɲä��ޤ�����<br>";
    $msg .= $e . "��<br>";
    $msg .= $result4;
} else {
    $msg .= "AAYLAWL2.txt���ɲåǡ���������ޤ���<br>";
}
$_SESSION["s_sysmsg"] = $msg;
header("Location: http:" . WEB_HOST . "system/system_menu.php");


ob_end_flush();  //Warning: Cannot add header ���к��Τ����ɲá�

?>

