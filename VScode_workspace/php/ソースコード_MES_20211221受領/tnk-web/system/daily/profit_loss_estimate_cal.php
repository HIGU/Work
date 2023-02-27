#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// »��ͽ¬�μ�ư�׻�����Ͽ as400get_ftp.php�����Ǽ¹�                      //
// Copyright (C) 2011-2018 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2011/07/15 Created   profit_loss_estimate_cal.php                        //
// 2011/07/20 ���Τ���Ͽ���ɲ�                                              //
//            ������ɤϻ���Ū�˲�ǯ�֤�ʿ�Ѥ�껻��                  //
// 2011/07/21 »��ͽ¬����ư�¹Ԥ���ʤ����ᣱ���ܤ��ɲ�                    //
// 2011/07/22 daoInterfaceClass.php�����פ�                                 //
//                              ���顼ȯ��(��󥯥ߥ���)�Τ�����          //
// 2011/07/25 �ѿ�����ʸ����ʸ��������                                      //
// 2011/10/04 ��˥����⤬���ץ�˹�פ���Ƥ���ߥ�����                //
// 2011/11/22 �����ê����μ����Ǻ�����׻��˥ߥ������ä��Τ���        //
// 2018/04/17 ��˥���»�ץǡ�������˥�ɸ����Ѥ�äƤ���Τ�����          //
// 2018/09/26 ���ջ����ä�˺��Ƥ����Τǽ���                              //
// 2018/09/27 ͽ����ʬ�������ѹ��Ǵ�λ���Ƥ���⤬�ޤޤ�Ƥ����Τǽ���    //
//            �Ѥ�LIMIT���ĤäƤ����Τǽ���                                 //
//////////////////////////////////////////////////////////////////////////////
//ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
//ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��)
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');        ///// �����ѥ�������
$fpa = fopen('/tmp/nippo.log', 'a');    ///// �����ѥ��ե�����ؤν���ߤǥ����ץ�
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// ����ǡ����Ƽ����ѥ��ե�����ؤν���ߤǥ����ץ�
fwrite($fpb, "»��ͽ¬�ǡ����η׻�������\n");
fwrite($fpb, "/home/www/html/tnk-web/system/daily/profit_loss_estimate_cal.php\n");
echo "/home/www/html/tnk-web/system/daily/profit_loss_estimate_cal.php\n";

/////////// ���եǡ����μ���
$target_ym = date('Ym');
// ���ǡ�������ľ���ݤϤ����ʳ��˲��ǡ����Ǹ������������Ȥ������ؤ���
// �������κ��ľ���Ϥ��Τޤޤ�����ʤ���
//$target_ym = 201809;
$today     = date('Ymd');
//$today     = 20180926;
        
        // ����μ���
        // getQueryStatement1�������ͽ��Τ��������Ǥ��ڤꡢ������ʬ�ʳ������Ѻ�������������ꡣ
        $div   = 'C';
        $query = getQueryStatement1($target_ym, $today, $div);
        $res_t   = array();
        $field_t = array();
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $c_uri       = 0;                   // ���ץ�����
            $c_endinv    = 0;                   // ���ץ����ê���⣱
        } else {
            // �ƥǡ����ν����
            $c_uri       = 0;                   // ���ץ�����
            $c_endinv    = 0;                   // ���ץ����ê���⣱
            for ($r=0; $r<$rows_t; $r++) {
                $c_uri     += $res_t[$r][9];
                $c_endinv  -= $res_t[$r][7];
            }
        }
        // getQueryStatement17�������ޤǤ�����ʴ����Τߡˡ����Ѻ�������������ꡣ
        $query = getQueryStatement17($target_ym, $today, $div);
        $res_t   = array();
        $field_t = array();
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $c_uri       += 0;
            $c_endinv    -= 0;
        } else {
            for ($r=0; $r<$rows_t; $r++) {
                $c_uri     += $res_t[$r][9];
                $c_endinv  -= $res_t[$r][7];
            }
        }
        // getQueryStatement15����������ʶ�ʬ��2�ʾ�������6����ʬ�������ʿ�ѡ���������碌�ơ�
        $query = getQueryStatement15($target_ym, $div);
        $res_t   = array();
        $field_t = array();
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $c_uri       += 0;
            $c_endinv    -= 0;
        } else {
            $c_uri     += $res_t[0][0];
            $c_endinv  -= $res_t[0][3];
        }
        
        $div   = 'L';
        $query = getQueryStatement1($target_ym, $today, $div);
        $res_t   = array();
        $field_t = array();
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $l_uri       = 0;                   // ��˥�����
            $l_endinv    = 0;                   // ��˥�����ê���⣱
        } else {
            // �ƥǡ����ν����
            $l_uri       = 0;                   // ��˥�����
            $l_endinv    = 0;                   // ��˥�����ê���⣱
            for ($r=0; $r<$rows_t; $r++) {
                $l_uri     += $res_t[$r][9];
                $l_endinv  -= $res_t[$r][7];
            }
        }
        $query = getQueryStatement17($target_ym, $today, $div);
        $res_t   = array();
        $field_t = array();
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $l_uri       += 0;
            $l_endinv    -= 0;
        } else {
            for ($r=0; $r<$rows_t; $r++) {
                $l_uri     += $res_t[$r][9];
                $l_endinv  -= $res_t[$r][7];
            }
        }
        $query = getQueryStatement15($target_ym, $div);
        $res_t   = array();
        $field_t = array();
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $l_uri       += 0;
            $l_endinv    -= 0;
        } else {
            $l_uri     += $res_t[0][0];
            $l_endinv  -= $res_t[0][3];
        }
        
        // ����ê����μ���
        // getQueryStatement2������ê���������δ���ê����
        $res_t   = array();
        $field_t = array();
        $div   = 'C';
        $query = getQueryStatement2($target_ym, $div);
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $c_invent = 0;
        } else {
            $c_invent = -$res_t[0][0];
        }
        $res_t   = array();
        $field_t = array();
        $div   = 'L';
        $query = getQueryStatement2($target_ym, $div);
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $l_invent = 0;
        } else {
            $l_invent = -$res_t[0][0];
        }
        // ������μ���
        // getQueryStatement3����ݼ��Ӥ�����5�ʾ塢�������칩��(01111)������(00222)�����Ͻ���
        $res_t   = array();
        $field_t = array();
        $div   = 'C';
        $query = getQueryStatement3($target_ym, $div);
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $c_metarial = 0;
        } else {
            $c_metarial = $res_t[0][0];
        }
        $res_t   = array();
        $field_t = array();
        $query = getQueryStatement4($div);
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $c_metarial += 0;
        } else {
            $c_metarial += $res_t[0][0];
        }
        $res_t   = array();
        $field_t = array();
        $query = getQueryStatement5($target_ym, $today, $div);
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $c_metarial += 0;
        } else {
            $c_metarial += $res_t[0][0];
        }
        $res_t   = array();
        $field_t = array();
        $query = getQueryStatement6($target_ym, $today, $div);
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $c_metarial += 0;
        } else {
            for ($r=0; $r<$rows_t; $r++) {
                $c_metarial += $res_t[$r][2];
            }
        }
        $res_t   = array();
        $field_t = array();
        $query = getQueryStatement7($target_ym, $today, $div);
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $c_metarial += 0;
        } else {
            $c_metarial += $res_t[0][0];
        }
        $res_t   = array();
        $field_t = array();
        $query = getQueryStatement8($target_ym, $today, $div);
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $c_metarial += 0;
        } else {
            for ($r=0; $r<$rows_t; $r++) {
                $c_metarial += $res_t[$r][2];
            }
        }
        
        $res_t   = array();
        $field_t = array();
        $div   = 'L';
        $query = getQueryStatement3($target_ym, $div);
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $l_metarial = 0;
        } else {
            $l_metarial = $res_t[0][0];
        }
        $res_t   = array();
        $field_t = array();
        $query = getQueryStatement4($div);
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $l_metarial += 0;
        } else {
            $l_metarial += $res_t[0][0];
        }
        $res_t   = array();
        $field_t = array();
        $query = getQueryStatement5($target_ym, $today, $div);
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $l_metarial += 0;
        } else {
            $l_metarial += $res_t[0][0];
        }
        $res_t   = array();
        $field_t = array();
        $query = getQueryStatement6($target_ym, $today, $div);
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $l_metarial += 0;
        } else {
            for ($r=0; $r<$rows_t; $r++) {
                $l_metarial += $res_t[$r][2];
            }
        }
        $res_t   = array();
        $field_t = array();
        $query = getQueryStatement7($target_ym, $today, $div);
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $l_metarial += 0;
        } else {
            $l_metarial += $res_t[0][0];
        }
        $res_t   = array();
        $field_t = array();
        $query = getQueryStatement8($target_ym, $today, $div);
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $l_metarial += 0;
        } else {
            for ($r=0; $r<$rows_t; $r++) {
                $l_metarial += $res_t[$r][2];
            }
        }
        // ����ê����μ���
        $res_t   = array();
        $field_t = array();
        $div   = 'C';
        $query = getQueryStatement9($target_ym, $div);
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $c_endinv += 0;
        } else {
            $c_endinv += $res_t[0][0];
        }
        $res_t   = array();
        $field_t = array();
        $query = getQueryStatement10($div);
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $c_endinv += 0;
        } else {
            $c_endinv += $res_t[0][0];
        }
        $res_t   = array();
        $field_t = array();
        $query = getQueryStatement11($target_ym, $today, $div);
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $c_endinv += 0;
        } else {
            $c_endinv += $res_t[0][0];
        }
        $res_t   = array();
        $field_t = array();
        $query = getQueryStatement12($target_ym, $today, $div);
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $c_endinv += 0;
        } else {
            for ($r=0; $r<$rows_t; $r++) {
                $c_endinv += $res_t[$r][2];
            }
        }
        $res_t   = array();
        $field_t = array();
        $query = getQueryStatement13($target_ym, $today, $div);
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $c_endinv += 0;
        } else {
            $c_endinv += $res_t[0][0];
        }
        $res_t   = array();
        $field_t = array();
        $query = getQueryStatement14($target_ym, $today, $div);
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $c_endinv += 0;
        } else {
            for ($r=0; $r<$rows_t; $r++) {
                $c_endinv += $res_t[$r][2];
            }
        }
        
        $res_t   = array();
        $field_t = array();
        $div   = 'L';
        $query = getQueryStatement9($target_ym, $div);
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $l_endinv += 0;
        } else {
            $l_endinv += $res_t[0][0];
        }
        $res_t   = array();
        $field_t = array();
        $query = getQueryStatement10($div);
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $l_endinv += 0;
        } else {
            $l_endinv += $res_t[0][0];
        }
        $res_t   = array();
        $field_t = array();
        $query = getQueryStatement11($target_ym, $today, $div);
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $l_endinv += 0;
        } else {
            $l_endinv += $res_t[0][0];
        }
        $res_t   = array();
        $field_t = array();
        $query = getQueryStatement12($target_ym, $today, $div);
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $l_endinv += 0;
        } else {
            for ($r=0; $r<$rows_t; $r++) {
                $l_endinv += $res_t[$r][2];
            }
        }
        $res_t   = array();
        $field_t = array();
        $query = getQueryStatement13($target_ym, $today, $div);
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $l_endinv += 0;
        } else {
            $l_endinv += $res_t[0][0];
        }
        $res_t   = array();
        $field_t = array();
        $query = getQueryStatement14($target_ym, $today, $div);
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $l_endinv += 0;
        } else {
            for ($r=0; $r<$rows_t; $r++) {
                $l_endinv += $res_t[$r][2];
            }
        }
        
        // �Ƽ����η׻�
        $div      = 'C';
        $rate_c   = array();
        $note     = array();
        $note[0]  = '���ץ�ϫ̳��';
        $note[1]  = '���ץ���¤����';
        $note[2]  = '���ץ�ͷ���';
        $note[3]  = '���ץ����';
        $note[4]  = '���ץ��̳��������';
        $note[5]  = '���ץ�������';
        $note[6]  = '���ץ�Ķȳ����פ���¾';
        $note[7]  = '���ץ��ʧ��©';
        $note[8]  = '���ץ�Ķȳ����Ѥ���¾';
        $uri_note = '���ץ�����';
        $num = count($note);
        for ($r=0; $r<$num; $r++) {
            $res_t   = array();
            $field_t = array();
            $query = getQueryStatement16($target_ym, $note[$r]);
            if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
                $kei_tmp = 0;
            } else {
                $kei_tmp = $res_t[0][0];
            }
            $res_t   = array();
            $field_t = array();
            $query = getQueryStatement16($target_ym, $uri_note);
            if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
                $uri_tmp = 0;
            } else {
                $uri_tmp = $res_t[0][0];
            }
            if ($uri_tmp != 0) {
                $rate_c[$r] = round($kei_tmp / $uri_tmp, 4);
            } else {
                $rate_c[$r] = 0;
            }
            $kei_kin   = round($c_uri * $rate_c[$r], 0);
            if ($r == 0) {
                $c_roumu    = $kei_kin;     // ��¤����-ϫ̳��
            } elseif ($r == 1) {
                $c_expense  = $kei_kin;     // ��¤����-����
            } elseif ($r == 2) {
                $c_han_jin  = $kei_kin;     // �δ���-�ͷ���
            } elseif ($r == 3) {
                $c_han_kei  = $kei_kin;     // �δ���-����
            } elseif ($r == 4) {
                $c_gyoumu   = $kei_kin;     // ��̳��������
            } elseif ($r == 5) {
                $c_swari    = $kei_kin;     // �������
            } elseif ($r == 6) {
                $c_pother   = $kei_kin;     // �Ķȳ����פ���¾
            } elseif ($r == 7) {
                $c_srisoku  = $kei_kin;     // ��ʧ��©
            } elseif ($r == 8) {
                $c_lother   = $kei_kin;     // �Ķȳ����Ѥ���¾
            }
        }
                
        $div      = 'L';
        $rate_l   = array();
        $note     = array();
        $note[0]  = '��˥�ɸ��ϫ̳��';
        $note[1]  = '��˥�ɸ����¤����';
        $note[2]  = '��˥�ɸ��ͷ���';
        $note[3]  = '��˥�ɸ�����';
        $note[4]  = '��˥�ɸ���̳��������';
        $note[5]  = '��˥�ɸ��������';
        $note[6]  = '��˥�ɸ��Ķȳ����פ���¾';
        $note[7]  = '��˥�ɸ���ʧ��©';
        $note[8]  = '��˥�ɸ��Ķȳ����Ѥ���¾';
        $uri_note = '��˥�ɸ������';
        /*
        $note[0]  = '��˥�ϫ̳��';
        $note[1]  = '��˥���¤����';
        $note[2]  = '��˥��ͷ���';
        $note[3]  = '��˥�����';
        $note[4]  = '��˥���̳��������';
        $note[5]  = '��˥��������';
        $note[6]  = '��˥��Ķȳ����פ���¾';
        $note[7]  = '��˥���ʧ��©';
        $note[8]  = '��˥��Ķȳ����Ѥ���¾';
        $uri_note = '��˥�����';
        */
        $num = count($note);
        for ($r=0; $r<$num; $r++) {
            $res_t   = array();
            $field_t = array();
            $query = getQueryStatement16($target_ym, $note[$r]);
            if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
                $kei_tmp = 0;
            } else {
                $kei_tmp = $res_t[0][0];
            }
            $res_t   = array();
            $field_t = array();
            $query = getQueryStatement16($target_ym, $uri_note);
            if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
                $uri_tmp = 0;
            } else {
                $uri_tmp = $res_t[0][0];
            }
            if ($uri_tmp != 0) {
                $rate_l[$r] = round($kei_tmp / $uri_tmp, 4);
            } else {
                $rate_l[$r] = 0;
            }
            $kei_kin   = round($l_uri * $rate_l[$r], 0);
            if ($r == 0) {
                $l_roumu    = $kei_kin;     // ��¤����-ϫ̳��
            } elseif ($r == 1) {
                $l_expense  = $kei_kin;     // ��¤����-����
            } elseif ($r == 2) {
                $l_han_jin  = $kei_kin;     // �δ���-�ͷ���
            } elseif ($r == 3) {
                $l_han_kei  = $kei_kin;     // �δ���-����
            } elseif ($r == 4) {
                $l_gyoumu   = $kei_kin;     // ��̳��������
            } elseif ($r == 5) {
                $l_swari    = $kei_kin;     // �������
            } elseif ($r == 6) {
                $l_pother   = $kei_kin;     // �Ķȳ����פ���¾
            } elseif ($r == 7) {
                $l_srisoku  = $kei_kin;     // ��ʧ��©
            } elseif ($r == 8) {
                $l_lother   = $kei_kin;     // �Ķȳ����Ѥ���¾
            }
        }
        // ���ʴ����ʲ�ǯ�֤�ʿ�ѡ˻���
        $item_b = array();
        $item_b[0]  = '���ʴ�������';
        $item_b[1]  = '���ʴ�����������ų���ê����';
        $item_b[2]  = '���ʴ���������(������)';
        $item_b[3]  = '���ʴ���ϫ̳��';
        $item_b[4]  = '���ʴ�����¤����';
        $item_b[5]  = '���ʴ������������ų���ê����';
        $item_b[6]  = '���ʴ�����帶��';
        $item_b[7]  = '���ʴ������������';
        $item_b[8]  = '���ʴ����ͷ���';
        $item_b[9]  = '���ʴ�������';
        $item_b[10] = '���ʴ����δ���ڤӰ��̴������';
        $item_b[11] = '���ʴ����Ķ�����';
        $item_b[12] = '���ʴ�����̳��������';
        $item_b[13] = '���ʴ����������';
        $item_b[14] = '���ʴ����Ķȳ����פ���¾';
        $item_b[15] = '���ʴ����Ķȳ����׷�';
        $item_b[16] = '���ʴ�����ʧ��©';
        $item_b[17] = '���ʴ����Ķȳ����Ѥ���¾';
        $item_b[18] = '���ʴ����Ķȳ����ѷ�';
        $item_b[19] = '���ʴ����о�����';
        $num = count($item_b);
        for ($r=0; $r<$num; $r++) {
            $res_t   = array();
            $field_t = array();
            $query = getQueryStatement16($target_ym, $item_b[$r]);
            if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
                if ($r == 0) {
                    $b_uri = 0;
                } elseif ($r == 1) {
                    $b_invent = 0;
                } elseif ($r == 2) {
                    $b_metarial = 0;
                } elseif ($r == 3) {
                    $b_roumu = 0;
                } elseif ($r == 4) {
                    $b_expense = 0;
                } elseif ($r == 5) {
                    $b_endinv = 0;
                } elseif ($r == 6) {
                    $b_urigen = 0;
                } elseif ($r == 7) {
                    $b_gross_profit = 0;
                } elseif ($r == 8) {
                    $b_han_jin = 0;
                } elseif ($r == 9) {
                    $b_han_kei = 0;
                } elseif ($r == 10) {
                    $b_han_all = 0;
                } elseif ($r == 11) {
                    $b_ope_profit = 0;
                } elseif ($r == 12) {
                    $b_gyoumu = 0;
                } elseif ($r == 13) {
                    $b_swari = 0;
                } elseif ($r == 14) {
                    $b_pother = 0;
                } elseif ($r == 15) {
                    $b_nonope_profit_sum = 0;
                } elseif ($r == 16) {
                    $b_srisoku = 0;
                } elseif ($r == 17) {
                    $b_lother = 0;
                } elseif ($r == 18) {
                    $b_nonope_loss_sum = 0;
                } elseif ($r == 19) {
                    $b_current_profit = 0;
                }
            } else {
                if ($r == 0) {
                    $b_uri = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 1) {
                    $b_invent = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 2) {
                    $b_metarial = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 3) {
                    $b_roumu = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 4) {
                    $b_expense = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 5) {
                    $b_endinv = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 6) {
                    $b_urigen = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 7) {
                    $b_gross_profit = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 8) {
                    $b_han_jin = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 9) {
                    $b_han_kei = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 10) {
                    $b_han_all = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 11) {
                    $b_ope_profit = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 12) {
                    $b_gyoumu = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 13) {
                    $b_swari = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 14) {
                    $b_pother = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 15) {
                    $b_nonope_profit_sum = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 16) {
                    $b_srisoku = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 17) {
                    $b_lother = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 18) {
                    $b_nonope_loss_sum = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 19) {
                    $b_current_profit = round(($res_t[0][0] / 12), 0);
                }
            }
        }
        // �����
        $item_s = array();
        $item_s[0]  = '���������';
        $item_s[1]  = '�������������ų���ê����';
        $item_s[2]  = '�����������(������)';
        $item_s[3]  = '�����ϫ̳��';
        $item_s[4]  = '�������¤����';
        $item_s[5]  = '��������������ų���ê����';
        $item_s[6]  = '�������帶��';
        $item_s[7]  = '��������������';
        $item_s[8]  = '������ͷ���';
        $item_s[9]  = '���������';
        $item_s[10] = '������δ���ڤӰ��̴������';
        $item_s[11] = '������Ķ�����';
        $item_s[12] = '�������̳��������';
        $item_s[13] = '������������';
        $item_s[14] = '������Ķȳ����פ���¾';
        $item_s[15] = '������Ķȳ����׷�';
        $item_s[16] = '�������ʧ��©';
        $item_s[17] = '������Ķȳ����Ѥ���¾';
        $item_s[18] = '������Ķȳ����ѷ�';
        $item_s[19] = '������о�����';
        $num = count($item_s);
        for ($r=0; $r<$num; $r++) {
            $res_t   = array();
            $field_t = array();
            $query = getQueryStatement16($target_ym, $item_s[$r]);
            if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
                if ($r == 0) {
                    $s_uri = 0;
                } elseif ($r == 1) {
                    $s_invent = 0;
                } elseif ($r == 2) {
                    $s_metarial = 0;
                } elseif ($r == 3) {
                    $s_roumu = 0;
                } elseif ($r == 4) {
                    $s_expense = 0;
                } elseif ($r == 5) {
                    $s_endinv = 0;
                } elseif ($r == 6) {
                    $s_urigen = 0;
                } elseif ($r == 7) {
                    $s_gross_profit = 0;
                } elseif ($r == 8) {
                    $s_han_jin = 0;
                } elseif ($r == 9) {
                    $s_han_kei = 0;
                } elseif ($r == 10) {
                    $s_han_all = 0;
                } elseif ($r == 11) {
                    $s_ope_profit = 0;
                } elseif ($r == 12) {
                    $s_gyoumu = 0;
                } elseif ($r == 13) {
                    $s_swari = 0;
                } elseif ($r == 14) {
                    $s_pother = 0;
                } elseif ($r == 15) {
                    $s_nonope_profit_sum = 0;
                } elseif ($r == 16) {
                    $s_srisoku = 0;
                } elseif ($r == 17) {
                    $s_lother = 0;
                } elseif ($r == 18) {
                    $s_nonope_loss_sum = 0;
                } elseif ($r == 19) {
                    $s_current_profit = 0;
                }
            } else {
                if ($r == 0) {
                    $s_uri = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 1) {
                    $s_invent = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 2) {
                    $s_metarial = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 3) {
                    $s_roumu = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 4) {
                    $s_expense = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 5) {
                    $s_endinv = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 6) {
                    $s_urigen = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 7) {
                    $s_gross_profit = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 8) {
                    $s_han_jin = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 9) {
                    $s_han_kei = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 10) {
                    $s_han_all = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 11) {
                    $s_ope_profit = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 12) {
                    $s_gyoumu = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 13) {
                    $s_swari = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 14) {
                    $s_pother = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 15) {
                    $s_nonope_profit_sum = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 16) {
                    $s_srisoku = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 17) {
                    $s_lother = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 18) {
                    $s_nonope_loss_sum = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 19) {
                    $s_current_profit = round(($res_t[0][0] / 12), 0);
                }
            }
        }
        // ���ɡ���ϻ���Ū�˲�ǯ�֤�ʿ�ѤǷ׻�
        // Ψ�ϡ��׻������ѹ������Ȥ��Τ���˽���������᤹��
        $rate_s     = array();
        $rate_s[0]  = 0;
        $rate_s[1]  = 0;
        $rate_s[2]  = 0;
        $rate_s[3]  = 0;
        $rate_s[4]  = 0;
        $rate_s[5]  = 0;
        $rate_s[6]  = 0;
        $rate_s[7]  = 0;
        $rate_s[8]  = 0;
        $rate_b     = array();
        $rate_b[0]  = 0;
        $rate_b[1]  = 0;
        $rate_b[2]  = 0;
        $rate_b[3]  = 0;
        $rate_b[4]  = 0;
        $rate_b[5]  = 0;
        $rate_b[6]  = 0;
        $rate_b[7]  = 0;
        $rate_b[8]  = 0;
        
        // ����ê����η׻�
        $c_endinv = -($c_invent + $c_endinv);
        $l_endinv = -($l_invent + $l_endinv);
        // ��帶���η׻�
        $c_urigen = $c_invent + $c_metarial + $c_roumu + $c_expense + $c_endinv;
        $l_urigen = $l_invent + $l_metarial + $l_roumu + $l_expense + $l_endinv;
        $s_urigen = $s_invent + $s_metarial + $s_roumu + $s_expense + $s_endinv;
        $b_urigen = $b_invent + $b_metarial + $b_roumu + $b_expense + $b_endinv;
        // ��������פη׻�
        $c_gross_profit = $c_uri - $c_urigen;
        $l_gross_profit = $l_uri - $l_urigen;
        $s_gross_profit = $s_uri - $s_urigen;
        $b_gross_profit = $b_uri - $b_urigen;
        // �δ����פη׻�
        $c_han_all = $c_han_jin + $c_han_kei;
        $l_han_all = $l_han_jin + $l_han_kei;
        $s_han_all = $s_han_jin + $s_han_kei;
        $b_han_all = $b_han_jin + $b_han_kei;
        // �Ķ����פη׻�
        $c_ope_profit = $c_gross_profit - $c_han_all;
        $l_ope_profit = $l_gross_profit - $l_han_all;
        $s_ope_profit = $s_gross_profit - $s_han_all;
        $b_ope_profit = $b_gross_profit - $b_han_all;
        // �Ķȳ����׷פη׻�
        $c_nonope_profit_sum = $c_gyoumu + $c_swari + $c_pother;
        $l_nonope_profit_sum = $l_gyoumu + $l_swari + $l_pother;
        $s_nonope_profit_sum = $s_gyoumu + $s_swari + $s_pother;
        $b_nonope_profit_sum = $b_gyoumu + $b_swari + $b_pother;
        // �Ķȳ����ѷפη׻�
        $c_nonope_loss_sum = $c_srisoku + $c_lother;
        $l_nonope_loss_sum = $l_srisoku + $l_lother;
        $s_nonope_loss_sum = $s_srisoku + $s_lother;
        $b_nonope_loss_sum = $b_srisoku + $b_lother;
        // �о����פη׻�
        $c_current_profit = $c_ope_profit + $c_nonope_profit_sum - $c_nonope_loss_sum;
        $l_current_profit = $l_ope_profit + $l_nonope_profit_sum - $l_nonope_loss_sum;
        $s_current_profit = $s_ope_profit + $s_nonope_profit_sum - $s_nonope_loss_sum;
        $b_current_profit = $b_ope_profit + $b_nonope_profit_sum - $b_nonope_loss_sum;
        
        // �ƹ�פη׻�
        $all_uri               = $c_uri + $l_uri + $s_uri + $b_uri;                         // ������
        $all_invent            = $c_invent + $l_invent + $s_invent + $b_invent;             // ����ê������
        $all_metarial          = $c_metarial + $l_metarial + $s_metarial + $b_metarial;     // ��������
        $all_roumu             = $c_roumu + $l_roumu + $s_roumu + $b_roumu;                 // ��¤����-ϫ̳����
        $all_expense           = $c_expense + $l_expense + $s_expense + $b_expense;         // ��¤����-������
        $all_endinv            = $c_endinv + $l_endinv + $s_endinv + $b_endinv;             // ����ê������
        $all_urigen            = $c_urigen + $l_urigen + $s_urigen + $b_urigen;             // ��帶�����
        $all_gross_profit      = $c_gross_profit + $l_gross_profit + $s_gross_profit + $b_gross_profit;                     // ��������׹��
        $all_han_jin           = $c_han_jin + $l_han_jin + $s_han_jin + $b_han_jin;         // �δ���-�ͷ�����
        $all_han_kei           = $c_han_kei + $l_han_kei + $s_han_kei + $b_han_kei;         // �δ���-������
        $all_han_all           = $c_han_all + $l_han_all + $s_han_all + $b_han_all;         // �δ���� ���
        $all_ope_profit        = $c_ope_profit + $l_ope_profit + $s_ope_profit + $b_ope_profit;                             // �Ķ����׹��
        $all_gyoumu            = $c_gyoumu + $l_gyoumu + $s_gyoumu + $b_gyoumu;             // �Ķȳ�����-��̳�����������
        $all_swari             = $c_swari + $l_swari + $s_swari + $b_swari;                 // �Ķȳ�����-����������
        $all_pother            = $c_pother + $l_pother + $s_pother + $b_pother;             // �Ķȳ�����-����¾���
        $all_nonope_profit_sum = $c_nonope_profit_sum + $l_nonope_profit_sum + $s_nonope_profit_sum + $b_nonope_profit_sum; // �Ķȳ����׷� ���
        $all_srisoku           = $c_srisoku + $l_srisoku + $s_srisoku + $b_srisoku;         // �Ķȳ�����-��ʧ��©���
        $all_lother            = $c_lother + $l_lother + $s_lother + $b_lother;             // �Ķȳ�����-����¾
        $all_nonope_loss_sum   = $c_nonope_loss_sum + $l_nonope_loss_sum + $s_nonope_loss_sum + $b_nonope_loss_sum;         // �Ķȳ����ѷ� ���
        $all_current_profit    = $c_current_profit + $l_current_profit + $s_current_profit + $b_current_profit;             // �о����� ���
        
// DB��Ͽ�ѥ����ƥ������
// ���ץ�
$item_c = array();
$item_c[0]  = '���ץ�����';
$item_c[1]  = '���ץ��������ų���ê����';
$item_c[2]  = '���ץ������(������)';
$item_c[3]  = '���ץ�ϫ̳��';
$item_c[4]  = '���ץ���¤����';
$item_c[5]  = '���ץ���������ų���ê����';
$item_c[6]  = '���ץ���帶��';
$item_c[7]  = '���ץ����������';
$item_c[8]  = '���ץ�ͷ���';
$item_c[9]  = '���ץ����';
$item_c[10] = '���ץ��δ���ڤӰ��̴������';
$item_c[11] = '���ץ�Ķ�����';
$item_c[12] = '���ץ��̳��������';
$item_c[13] = '���ץ�������';
$item_c[14] = '���ץ�Ķȳ����פ���¾';
$item_c[15] = '���ץ�Ķȳ����׷�';
$item_c[16] = '���ץ��ʧ��©';
$item_c[17] = '���ץ�Ķȳ����Ѥ���¾';
$item_c[18] = '���ץ�Ķȳ����ѷ�';
$item_c[19] = '���ץ�о�����';
// ��˥�
$item_l = array();
$item_l[0]  = '��˥�����';
$item_l[1]  = '��˥���������ų���ê����';
$item_l[2]  = '��˥�������(������)';
$item_l[3]  = '��˥�ϫ̳��';
$item_l[4]  = '��˥���¤����';
$item_l[5]  = '��˥����������ų���ê����';
$item_l[6]  = '��˥���帶��';
$item_l[7]  = '��˥����������';
$item_l[8]  = '��˥��ͷ���';
$item_l[9]  = '��˥�����';
$item_l[10] = '��˥��δ���ڤӰ��̴������';
$item_l[11] = '��˥��Ķ�����';
$item_l[12] = '��˥���̳��������';
$item_l[13] = '��˥��������';
$item_l[14] = '��˥��Ķȳ����פ���¾';
$item_l[15] = '��˥��Ķȳ����׷�';
$item_l[16] = '��˥���ʧ��©';
$item_l[17] = '��˥��Ķȳ����Ѥ���¾';
$item_l[18] = '��˥��Ķȳ����ѷ�';
$item_l[19] = '��˥��о�����';
// ���ʴ���
$item_b = array();
$item_b[0]  = '���ʴ�������';
$item_b[1]  = '���ʴ�����������ų���ê����';
$item_b[2]  = '���ʴ���������(������)';
$item_b[3]  = '���ʴ���ϫ̳��';
$item_b[4]  = '���ʴ�����¤����';
$item_b[5]  = '���ʴ������������ų���ê����';
$item_b[6]  = '���ʴ�����帶��';
$item_b[7]  = '���ʴ������������';
$item_b[8]  = '���ʴ����ͷ���';
$item_b[9]  = '���ʴ�������';
$item_b[10] = '���ʴ����δ���ڤӰ��̴������';
$item_b[11] = '���ʴ����Ķ�����';
$item_b[12] = '���ʴ�����̳��������';
$item_b[13] = '���ʴ����������';
$item_b[14] = '���ʴ����Ķȳ����פ���¾';
$item_b[15] = '���ʴ����Ķȳ����׷�';
$item_b[16] = '���ʴ�����ʧ��©';
$item_b[17] = '���ʴ����Ķȳ����Ѥ���¾';
$item_b[18] = '���ʴ����Ķȳ����ѷ�';
$item_b[19] = '���ʴ����о�����';
// �����
$item_s = array();
$item_s[0]  = '���������';
$item_s[1]  = '�������������ų���ê����';
$item_s[2]  = '�����������(������)';
$item_s[3]  = '�����ϫ̳��';
$item_s[4]  = '�������¤����';
$item_s[5]  = '��������������ų���ê����';
$item_s[6]  = '�������帶��';
$item_s[7]  = '��������������';
$item_s[8]  = '������ͷ���';
$item_s[9]  = '���������';
$item_s[10] = '������δ���ڤӰ��̴������';
$item_s[11] = '������Ķ�����';
$item_s[12] = '�������̳��������';
$item_s[13] = '������������';
$item_s[14] = '������Ķȳ����פ���¾';
$item_s[15] = '������Ķȳ����׷�';
$item_s[16] = '�������ʧ��©';
$item_s[17] = '������Ķȳ����Ѥ���¾';
$item_s[18] = '������Ķȳ����ѷ�';
$item_s[19] = '������о�����';
// ����
$item_a = array();
$item_a[0]  = '��������';
$item_a[1]  = '���δ�������ų���ê����';
$item_a[2]  = '���κ�����(������)';
$item_a[3]  = '����ϫ̳��';
$item_a[4]  = '������¤����';
$item_a[5]  = '���δ��������ų���ê����';
$item_a[6]  = '������帶��';
$item_a[7]  = '�������������';
$item_a[8]  = '���οͷ���';
$item_a[9]  = '���η���';
$item_a[10] = '�����δ���ڤӰ��̴������';
$item_a[11] = '���αĶ�����';
$item_a[12] = '���ζ�̳��������';
$item_a[13] = '���λ������';
$item_a[14] = '���αĶȳ����פ���¾';
$item_a[15] = '���αĶȳ����׷�';
$item_a[16] = '���λ�ʧ��©';
$item_a[17] = '���αĶȳ����Ѥ���¾';
$item_a[18] = '���αĶȳ����ѷ�';
$item_a[19] = '���ηо�����';
// DB��Ͽ�ѥǡ���������
// ���ץ�
$pl_data_c = array();
$pl_data_c[0]  = $c_uri;
$pl_data_c[1]  = $c_invent;
$pl_data_c[2]  = $c_metarial;
$pl_data_c[3]  = $c_roumu;
$pl_data_c[4]  = $c_expense;
$pl_data_c[5]  = $c_endinv;
$pl_data_c[6]  = $c_urigen;
$pl_data_c[7]  = $c_gross_profit;
$pl_data_c[8]  = $c_han_jin;
$pl_data_c[9]  = $c_han_kei;
$pl_data_c[10] = $c_han_all;
$pl_data_c[11] = $c_ope_profit;
$pl_data_c[12] = $c_gyoumu;
$pl_data_c[13] = $c_swari;
$pl_data_c[14] = $c_pother;
$pl_data_c[15] = $c_nonope_profit_sum;
$pl_data_c[16] = $c_srisoku;
$pl_data_c[17] = $c_lother;
$pl_data_c[18] = $c_nonope_loss_sum;
$pl_data_c[19] = $c_current_profit;
// ��˥�
$pl_data_l = array();
$pl_data_l[0]  = $l_uri;
$pl_data_l[1]  = $l_invent;
$pl_data_l[2]  = $l_metarial;
$pl_data_l[3]  = $l_roumu;
$pl_data_l[4]  = $l_expense;
$pl_data_l[5]  = $l_endinv;
$pl_data_l[6]  = $l_urigen;
$pl_data_l[7]  = $l_gross_profit;
$pl_data_l[8]  = $l_han_jin;
$pl_data_l[9]  = $l_han_kei;
$pl_data_l[10] = $l_han_all;
$pl_data_l[11] = $l_ope_profit;
$pl_data_l[12] = $l_gyoumu;
$pl_data_l[13] = $l_swari;
$pl_data_l[14] = $l_pother;
$pl_data_l[15] = $l_nonope_profit_sum;
$pl_data_l[16] = $l_srisoku;
$pl_data_l[17] = $l_lother;
$pl_data_l[18] = $l_nonope_loss_sum;
$pl_data_l[19] = $l_current_profit;
// ���ʴ���
$pl_data_b = array();
$pl_data_b[0]  = $b_uri;
$pl_data_b[1]  = $b_invent;
$pl_data_b[2]  = $b_metarial;
$pl_data_b[3]  = $b_roumu;
$pl_data_b[4]  = $b_expense;
$pl_data_b[5]  = $b_endinv;
$pl_data_b[6]  = $b_urigen;
$pl_data_b[7]  = $b_gross_profit;
$pl_data_b[8]  = $b_han_jin;
$pl_data_b[9]  = $b_han_kei;
$pl_data_b[10] = $b_han_all;
$pl_data_b[11] = $b_ope_profit;
$pl_data_b[12] = $b_gyoumu;
$pl_data_b[13] = $b_swari;
$pl_data_b[14] = $b_pother;
$pl_data_b[15] = $b_nonope_profit_sum;
$pl_data_b[16] = $b_srisoku;
$pl_data_b[17] = $b_lother;
$pl_data_b[18] = $b_nonope_loss_sum;
$pl_data_b[19] = $b_current_profit;
// �����
$pl_data_s = array();
$pl_data_s[0]  = $s_uri;
$pl_data_s[1]  = $s_invent;
$pl_data_s[2]  = $s_metarial;
$pl_data_s[3]  = $s_roumu;
$pl_data_s[4]  = $s_expense;
$pl_data_s[5]  = $s_endinv;
$pl_data_s[6]  = $s_urigen;
$pl_data_s[7]  = $s_gross_profit;
$pl_data_s[8]  = $s_han_jin;
$pl_data_s[9]  = $s_han_kei;
$pl_data_s[10] = $s_han_all;
$pl_data_s[11] = $s_ope_profit;
$pl_data_s[12] = $s_gyoumu;
$pl_data_s[13] = $s_swari;
$pl_data_s[14] = $s_pother;
$pl_data_s[15] = $s_nonope_profit_sum;
$pl_data_s[16] = $s_srisoku;
$pl_data_s[17] = $s_lother;
$pl_data_s[18] = $s_nonope_loss_sum;
$pl_data_s[19] = $s_current_profit;
// ����
$pl_data_a = array();
$pl_data_a[0]  = $all_uri;
$pl_data_a[1]  = $all_invent;
$pl_data_a[2]  = $all_metarial;
$pl_data_a[3]  = $all_roumu;
$pl_data_a[4]  = $all_expense;
$pl_data_a[5]  = $all_endinv;
$pl_data_a[6]  = $all_urigen;
$pl_data_a[7]  = $all_gross_profit;
$pl_data_a[8]  = $all_han_jin;
$pl_data_a[9]  = $all_han_kei;
$pl_data_a[10] = $all_han_all;
$pl_data_a[11] = $all_ope_profit;
$pl_data_a[12] = $all_gyoumu;
$pl_data_a[13] = $all_swari;
$pl_data_a[14] = $all_pother;
$pl_data_a[15] = $all_nonope_profit_sum;
$pl_data_a[16] = $all_srisoku;
$pl_data_a[17] = $all_lother;
$pl_data_a[18] = $all_nonope_loss_sum;
$pl_data_a[19] = $all_current_profit;
$last_date = date('Y-m-d H:i:s');
$last_user = '000000';

/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    query_affected_trans($con, "begin");
} else {
    echo "$log_date »��ͽ¬ db_connect() error \n";
    fwrite($fpa,"$log_date »��ͽ¬ db_connect() error \n");
    fwrite($fpb,"$log_date »��ͽ¬ db_connect() error \n");
    exit();
}

/////////// ���ץ�ͽ¬�ǡ�����Ͽ
$up_flg = 0;
$num = count($item_c);
for ($r=0; $r<$num; $r++) {
    $query = sprintf("SELECT kin FROM act_pl_estimate WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $target_ym, $today, $item_c[$r]);
    $res = array();
    if (getResult2($query,$res) <= 0) {
        ////////// Insert Start
        if ($r == 3) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_c[$r], $rate_c[0], $item_c[$r]);
        } elseif($r == 4) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_c[$r], $rate_c[1], $item_c[$r]);
        } elseif($r == 8) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_c[$r], $rate_c[2], $item_c[$r]);
        } elseif($r == 9) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_c[$r], $rate_c[3], $item_c[$r]);
        } elseif($r == 12) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_c[$r], $rate_c[4], $item_c[$r]);
        } elseif($r == 13) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_c[$r], $rate_c[5], $item_c[$r]);
        } elseif($r == 14) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_c[$r], $rate_c[6], $item_c[$r]);
        } elseif($r == 16) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_c[$r], $rate_c[7], $item_c[$r]);
        } elseif($r == 17) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_c[$r], $rate_c[8], $item_c[$r]);
        } else {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, note) VALUES (%d, %d, %d, '%s')", $target_ym, $today, $pl_data_c[$r], $item_c[$r]);
        }
        if (query_affected_trans($con, $query) <= 0) {
            fwrite($fpa,"$log_date ���ץ�»��ͽ¬:$target_ym : $today ʬ��$item_c[$r]�ν���ߤ˼��Ԥ��ޤ���!\n");
            fwrite($fpb,"$log_date ���ץ�»��ͽ¬:$target_ym : $today ʬ��$item_c[$r]�ν���ߤ˼��Ԥ��ޤ���!\n");
            $up_flg = 1;
        }
    } else {
        ////////// UPDATE Start
        if ($r == 3) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user='000000' WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_c[$r], $rate_c[0], $target_ym, $today, $item_c[$r]);
        } elseif($r == 4) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user='000000' WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_c[$r], $rate_c[1], $target_ym, $today, $item_c[$r]);
        } elseif($r == 8) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user='000000' WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_c[$r], $rate_c[2], $target_ym, $today, $item_c[$r]);
        } elseif($r == 9) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user='000000' WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_c[$r], $rate_c[3], $target_ym, $today, $item_c[$r]);
        } elseif($r == 12) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user='000000' WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_c[$r], $rate_c[4], $target_ym, $today, $item_c[$r]);
        } elseif($r == 13) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user='000000' WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_c[$r], $rate_c[5], $target_ym, $today, $item_c[$r]);
        } elseif($r == 14) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user='000000' WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_c[$r], $rate_c[6], $target_ym, $today, $item_c[$r]);
        } elseif($r == 16) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user='000000' WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_c[$r], $rate_c[7], $target_ym, $today, $item_c[$r]);
        } elseif($r == 17) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user='000000' WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_c[$r], $rate_c[8], $target_ym, $today, $item_c[$r]);
        } else {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, last_date=CURRENT_TIMESTAMP, last_user='000000' WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_c[$r], $target_ym, $today, $item_c[$r]);
        }
        if (query_affected_trans($con, $query) <= 0) {
            fwrite($fpa,"$log_date ���ץ�»��ͽ¬:$target_ym : $today ʬ��$item_c[$r]��UPDATE�˼��Ԥ��ޤ���!\n");
            fwrite($fpb,"$log_date ���ץ�»��ͽ¬:$target_ym : $today ʬ��$item_c[$r]��UPDATE�˼��Ԥ��ޤ���!\n");
            $up_flg = 1;
        }
    }
}
if ($up_flg == 1) {
    fwrite($fpa,"$log_date ���ץ�»��ͽ¬:$target_ym : $today ʬ�ΰ�������������Ͽ����ޤ���Ǥ�����\n");
    fwrite($fpb,"$log_date ���ץ�»��ͽ¬:$target_ym : $today ʬ�ΰ�������������Ͽ����ޤ���Ǥ�����\n");
    echo "$log_date ���ץ�»��ͽ¬:$target_ym : $today ʬ�ΰ�������������Ͽ����ޤ���Ǥ�����\n";
} else {
    fwrite($fpa,"$log_date ���ץ�»��ͽ¬:$target_ym : $today ʬ��Ͽ���ޤ�����\n");
    fwrite($fpb,"$log_date ���ץ�»��ͽ¬:$target_ym : $today ʬ��Ͽ���ޤ�����\n");
    echo "$log_date ���ץ�»��ͽ¬:$target_ym : $today ʬ��Ͽ���ޤ�����\n";
}

/////////// ��˥�ͽ¬�ǡ�����Ͽ
$up_flg = 0;
$num = count($item_l);
for ($r=0; $r<$num; $r++) {
    $query = sprintf("SELECT kin FROM act_pl_estimate WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $target_ym, $today, $item_l[$r]);
    $res = array();
    if (getResult2($query,$res) <= 0) {
        ////////// Insert Start
        if ($r == 3) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_l[$r], $rate_l[0], $item_l[$r]);
        } elseif($r == 4) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_l[$r], $rate_l[1], $item_l[$r]);
        } elseif($r == 8) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_l[$r], $rate_l[2], $item_l[$r]);
        } elseif($r == 9) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_l[$r], $rate_l[3], $item_l[$r]);
        } elseif($r == 12) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_l[$r], $rate_l[4], $item_l[$r]);
        } elseif($r == 13) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_l[$r], $rate_l[5], $item_l[$r]);
        } elseif($r == 14) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_l[$r], $rate_l[6], $item_l[$r]);
        } elseif($r == 16) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_l[$r], $rate_l[7], $item_l[$r]);
        } elseif($r == 17) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_l[$r], $rate_l[8], $item_l[$r]);
        } else {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, note) VALUES (%d, %d, %d, '%s')", $target_ym, $today, $pl_data_l[$r], $item_l[$r]);
        }
        if (query_affected_trans($con, $query) <= 0) {
            fwrite($fpa,"$log_date ��˥�»��ͽ¬:$target_ym : $today ʬ��$item_l[$r]�ν���ߤ˼��Ԥ��ޤ���!\n");
            fwrite($fpb,"$log_date ��˥�»��ͽ¬:$target_ym : $today ʬ��$item_l[$r]�ν���ߤ˼��Ԥ��ޤ���!\n");
            $up_flg = 1;
        }
    } else {
        ////////// UPDATE Start
        if ($r == 3) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_l[$r], $rate_l[0], $target_ym, $today, $item_l[$r]);
        } elseif($r == 4) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_l[$r], $rate_l[1], $target_ym, $today, $item_l[$r]);
        } elseif($r == 8) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_l[$r], $rate_l[2], $target_ym, $today, $item_l[$r]);
        } elseif($r == 9) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_l[$r], $rate_l[3], $target_ym, $today, $item_l[$r]);
        } elseif($r == 12) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_l[$r], $rate_l[4], $target_ym, $today, $item_l[$r]);
        } elseif($r == 13) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_l[$r], $rate_l[5], $target_ym, $today, $item_l[$r]);
        } elseif($r == 14) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_l[$r], $rate_l[6], $target_ym, $today, $item_l[$r]);
        } elseif($r == 16) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_l[$r], $rate_l[7], $target_ym, $today, $item_l[$r]);
        } elseif($r == 17) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_l[$r], $rate_l[8], $target_ym, $today, $item_l[$r]);
        } else {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_l[$r], $target_ym, $today, $item_l[$r]);
        }
        if (query_affected_trans($con, $query) <= 0) {
            fwrite($fpa,"$log_date ��˥�»��ͽ¬:$target_ym : $today ʬ��$item_l[$r]��UPDATE�˼��Ԥ��ޤ���!\n");
            fwrite($fpb,"$log_date ��˥�»��ͽ¬:$target_ym : $today ʬ��$item_l[$r]��UPDATE�˼��Ԥ��ޤ���!\n");
            $up_flg = 1;
        }
    }
}
if ($up_flg == 1) {
    fwrite($fpa,"$log_date ��˥�»��ͽ¬:$target_ym : $today ʬ�ΰ�������������Ͽ����ޤ���Ǥ�����\n");
    fwrite($fpb,"$log_date ��˥�»��ͽ¬:$target_ym : $today ʬ�ΰ�������������Ͽ����ޤ���Ǥ�����\n");
    echo "$log_date ��˥�»��ͽ¬:$target_ym : $today ʬ�ΰ�������������Ͽ����ޤ���Ǥ�����\n";
} else {
    fwrite($fpa,"$log_date ��˥�»��ͽ¬:$target_ym : $today ʬ��Ͽ���ޤ�����\n");
    fwrite($fpb,"$log_date ��˥�»��ͽ¬:$target_ym : $today ʬ��Ͽ���ޤ�����\n");
    echo "$log_date ��˥�»��ͽ¬:$target_ym : $today ʬ��Ͽ���ޤ�����\n";
}

/////////// ���ʴ���ͽ¬�ǡ�����Ͽ
$up_flg = 0;
$num = count($item_b);
for ($r=0; $r<$num; $r++) {
    $query = sprintf("SELECT kin FROM act_pl_estimate WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $target_ym, $today, $item_b[$r]);
    $res = array();
    if (getResult2($query,$res) <= 0) {
        ////////// Insert Start
        if ($r == 3) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_b[$r], $rate_b[0], $item_b[$r]);
        } elseif($r == 4) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_b[$r], $rate_b[1], $item_b[$r]);
        } elseif($r == 8) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_b[$r], $rate_b[2], $item_b[$r]);
        } elseif($r == 9) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_b[$r], $rate_b[3], $item_b[$r]);
        } elseif($r == 12) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_b[$r], $rate_b[4], $item_b[$r]);
        } elseif($r == 13) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_b[$r], $rate_b[5], $item_b[$r]);
        } elseif($r == 14) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_b[$r], $rate_b[6], $item_b[$r]);
        } elseif($r == 16) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_b[$r], $rate_b[7], $item_b[$r]);
        } elseif($r == 17) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_b[$r], $rate_b[8], $item_b[$r]);
        } else {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, note) VALUES (%d, %d, %d, '%s')", $target_ym, $today, $pl_data_b[$r], $item_b[$r]);
        }
        if (query_affected_trans($con, $query) <= 0) {
            fwrite($fpa,"$log_date ���ʴ���»��ͽ¬:$target_ym : $today ʬ��$item_b[$r]�ν���ߤ˼��Ԥ��ޤ���!\n");
            fwrite($fpb,"$log_date ���ʴ���»��ͽ¬:$target_ym : $today ʬ��$item_b[$r]�ν���ߤ˼��Ԥ��ޤ���!\n");
            $up_flg = 1;
        }
    } else {
        ////////// UPDATE Start
        if ($r == 3) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_b[$r], $rate_b[0], $target_ym, $today, $item_b[$r]);
        } elseif($r == 4) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_b[$r], $rate_b[1], $target_ym, $today, $item_b[$r]);
        } elseif($r == 8) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_b[$r], $rate_b[2], $target_ym, $today, $item_b[$r]);
        } elseif($r == 9) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_b[$r], $rate_b[3], $target_ym, $today, $item_b[$r]);
        } elseif($r == 12) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_b[$r], $rate_b[4], $target_ym, $today, $item_b[$r]);
        } elseif($r == 13) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_b[$r], $rate_b[5], $target_ym, $today, $item_b[$r]);
        } elseif($r == 14) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_b[$r], $rate_b[6], $target_ym, $today, $item_b[$r]);
        } elseif($r == 16) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_b[$r], $rate_b[7], $target_ym, $today, $item_b[$r]);
        } elseif($r == 17) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_b[$r], $rate_b[8], $target_ym, $today, $item_b[$r]);
        } else {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_b[$r], $target_ym, $today, $item_b[$r]);
        }
        if (query_affected_trans($con, $query) <= 0) {
            fwrite($fpa,"$log_date ���ʴ���»��ͽ¬:$target_ym : $today ʬ��$item_b[$r]��UPDATE�˼��Ԥ��ޤ���!\n");
            fwrite($fpb,"$log_date ���ʴ���»��ͽ¬:$target_ym : $today ʬ��$item_b[$r]��UPDATE�˼��Ԥ��ޤ���!\n");
            $up_flg = 1;
        }
    }
}
if ($up_flg == 1) {
    fwrite($fpa,"$log_date ���ʴ���»��ͽ¬:$target_ym : $today ʬ�ΰ�������������Ͽ����ޤ���Ǥ�����\n");
    fwrite($fpb,"$log_date ���ʴ���»��ͽ¬:$target_ym : $today ʬ�ΰ�������������Ͽ����ޤ���Ǥ�����\n");
    echo "$log_date ���ʴ���»��ͽ¬:$target_ym : $today ʬ�ΰ�������������Ͽ����ޤ���Ǥ�����\n";
} else {
    fwrite($fpa,"$log_date ���ʴ���»��ͽ¬:$target_ym : $today ʬ��Ͽ���ޤ�����\n");
    fwrite($fpb,"$log_date ���ʴ���»��ͽ¬:$target_ym : $today ʬ��Ͽ���ޤ�����\n");
    echo "$log_date ���ʴ���»��ͽ¬:$target_ym : $today ʬ��Ͽ���ޤ�����\n";
}

/////////// �����ͽ¬�ǡ�����Ͽ
$up_flg = 0;
$num = count($item_s);
for ($r=0; $r<$num; $r++) {
    $query = sprintf("SELECT kin FROM act_pl_estimate WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $target_ym, $today, $item_s[$r]);
    $res = array();
    if (getResult2($query,$res) <= 0) {
        ////////// Insert Start
        if ($r == 3) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_s[$r], $rate_s[0], $item_s[$r]);
        } elseif($r == 4) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_s[$r], $rate_s[1], $item_s[$r]);
        } elseif($r == 8) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_s[$r], $rate_s[2], $item_s[$r]);
        } elseif($r == 9) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_s[$r], $rate_s[3], $item_s[$r]);
        } elseif($r == 12) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_s[$r], $rate_s[4], $item_s[$r]);
        } elseif($r == 13) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_s[$r], $rate_s[5], $item_s[$r]);
        } elseif($r == 14) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_s[$r], $rate_s[6], $item_s[$r]);
        } elseif($r == 16) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_s[$r], $rate_s[7], $item_s[$r]);
        } elseif($r == 17) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_s[$r], $rate_s[8], $item_s[$r]);
        } else {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, note) VALUES (%d, %d, %d, '%s')", $target_ym, $today, $pl_data_s[$r], $item_s[$r]);
        }
        if (query_affected_trans($con, $query) <= 0) {
            fwrite($fpa,"$log_date �����»��ͽ¬:$target_ym : $today ʬ��$item_s[$r]�ν���ߤ˼��Ԥ��ޤ���!\n");
            fwrite($fpb,"$log_date �����»��ͽ¬:$target_ym : $today ʬ��$item_s[$r]�ν���ߤ˼��Ԥ��ޤ���!\n");
            $up_flg = 1;
        }
    } else {
        ////////// UPDATE Start
        if ($r == 3) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_s[$r], $rate_s[0], $target_ym, $today, $item_s[$r]);
        } elseif($r == 4) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_s[$r], $rate_s[1], $target_ym, $today, $item_s[$r]);
        } elseif($r == 8) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_s[$r], $rate_s[2], $target_ym, $today, $item_s[$r]);
        } elseif($r == 9) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_s[$r], $rate_s[3], $target_ym, $today, $item_s[$r]);
        } elseif($r == 12) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_s[$r], $rate_s[4], $target_ym, $today, $item_s[$r]);
        } elseif($r == 13) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_s[$r], $rate_s[5], $target_ym, $today, $item_s[$r]);
        } elseif($r == 14) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_s[$r], $rate_s[6], $target_ym, $today, $item_s[$r]);
        } elseif($r == 16) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_s[$r], $rate_s[7], $target_ym, $today, $item_s[$r]);
        } elseif($r == 17) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_s[$r], $rate_s[8], $target_ym, $today, $item_s[$r]);
        } else {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_s[$r], $target_ym, $today, $item_s[$r]);
        }
        if (query_affected_trans($con, $query) <= 0) {
            fwrite($fpa,"$log_date �����»��ͽ¬:$target_ym : $today ʬ��$item_s[$r]��UPDATE�˼��Ԥ��ޤ���!\n");
            fwrite($fpb,"$log_date �����»��ͽ¬:$target_ym : $today ʬ��$item_s[$r]��UPDATE�˼��Ԥ��ޤ���!\n");
            $up_flg = 1;
        }
    }
}
if ($up_flg == 1) {
    fwrite($fpa,"$log_date �����»��ͽ¬:$target_ym : $today ʬ�ΰ�������������Ͽ����ޤ���Ǥ�����\n");
    fwrite($fpb,"$log_date �����»��ͽ¬:$target_ym : $today ʬ�ΰ�������������Ͽ����ޤ���Ǥ�����\n");
    echo "$log_date �����»��ͽ¬:$target_ym : $today ʬ�ΰ�������������Ͽ����ޤ���Ǥ�����\n";
} else {
    fwrite($fpa,"$log_date �����»��ͽ¬:$target_ym : $today ʬ��Ͽ���ޤ�����\n");
    fwrite($fpb,"$log_date �����»��ͽ¬:$target_ym : $today ʬ��Ͽ���ޤ�����\n");
    echo "$log_date �����»��ͽ¬:$target_ym : $today ʬ��Ͽ���ޤ�����\n";
}
/////////// ����ͽ¬�ǡ�����Ͽ
$up_flg = 0;
$num = count($item_a);
for ($r=0; $r<$num; $r++) {
    $query = sprintf("SELECT kin FROM act_pl_estimate WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $target_ym, $today, $item_a[$r]);
    $res = array();
    if (getResult2($query,$res) <= 0) {
        ////////// Insert Start
        $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, note) VALUES (%d, %d, %d, '%s')", $target_ym, $today, $pl_data_a[$r], $item_a[$r]);
        if (query_affected_trans($con, $query) <= 0) {
            fwrite($fpa,"$log_date ����»��ͽ¬:$target_ym : $today ʬ��$item_a[$r]�ν���ߤ˼��Ԥ��ޤ���!\n");
            fwrite($fpb,"$log_date ����»��ͽ¬:$target_ym : $today ʬ��$item_a[$r]�ν���ߤ˼��Ԥ��ޤ���!\n");
            $up_flg = 1;
        }
    } else {
        ////////// UPDATE Start
        $query = sprintf("UPDATE act_pl_estimate SET kin=%d, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_a[$r], $target_ym, $today, $item_a[$r]);
        if (query_affected_trans($con, $query) <= 0) {
            fwrite($fpa,"$log_date ����»��ͽ¬:$target_ym : $today ʬ��$item_a[$r]��UPDATE�˼��Ԥ��ޤ���!\n");
            fwrite($fpb,"$log_date ����»��ͽ¬:$target_ym : $today ʬ��$item_a[$r]��UPDATE�˼��Ԥ��ޤ���!\n");
            $up_flg = 1;
        }
    }
}
if ($up_flg == 1) {
    fwrite($fpa,"$log_date ����»��ͽ¬:$target_ym : $today ʬ�ΰ�������������Ͽ����ޤ���Ǥ�����\n");
    fwrite($fpb,"$log_date ����»��ͽ¬:$target_ym : $today ʬ�ΰ�������������Ͽ����ޤ���Ǥ�����\n");
    echo "$log_date ����»��ͽ¬:$target_ym : $today ʬ�ΰ�������������Ͽ����ޤ���Ǥ�����\n";
} else {
    fwrite($fpa,"$log_date ����»��ͽ¬:$target_ym : $today ʬ��Ͽ���ޤ�����\n");
    fwrite($fpb,"$log_date ����»��ͽ¬:$target_ym : $today ʬ��Ͽ���ޤ�����\n");
    echo "$log_date ����»��ͽ¬:$target_ym : $today ʬ��Ͽ���ޤ�����\n";
}

/////////// commit �ȥ�󥶥������λ
query_affected_trans($con, "commit");
// echo $query . "\n";
fclose($fpa);      ////// �����ѥ�����߽�λ
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// ����ǡ����Ƽ����ѥ�����߽�λ

exit();

    ///// List��   ����ɽ��SQL���ơ��ȥ��ȼ���
    // ����ȴ���ê����ΰ��������(CL����) �оݷ�����ײ���̤����ʬ�����
    // getQueryStatement1�������ͽ��Τ��������Ǥ��ڤꡢ������ʬ�ʳ������Ѻ�������������ꡣ
    function getQueryStatement1($target_ym, $today, $div)
    {
        //$str_date = $target_ym . '01';
        // 2011/08/30 ͽ¬���ٸ���ΰ� ����μ�����ˡ���ѹ�
        // ����ޤǤϡ���Ω�����ײ�Τߤ�ͽ¬���Ƥ�����
        // �����ޤǤ������ӡ������������ޤǤ���Ω�����ײ�ι绻���ѹ�
        // ̤����ʬ�γ������Ϸ��Ȥ�����λʬ�Ϸ׻������������
        $str_date  = $target_ym . '01';
        // ���ǡ�������ľ���ݤϾ嵭�������ؤ���
        //$str_date = $today;
        $end_date = $target_ym . '31';
        /*if ($div == 'C') {
            if ($target_ym < 200710) {
                $rate = 25.60;  // ���ץ�ɸ�� 2007/10/01���ʲ������
            } elseif ($target_ym < 201104) {
                $rate = 57.00;  // ���ץ�ɸ�� 2007/10/01���ʲ���ʹ�
            } else {
                $rate = 45.00;  // ���ץ�ɸ�� 2011/04/01���ʲ���ʹ�
            }
        } elseif ($div == 'L') {
            if ($target_ym < 200710) {
                $rate = 37.00;  // ��˥� 2008/10/01���ʲ������
            } elseif ($target_ym < 201104) {
                $rate = 44.00;  // ��˥� 2008/10/01���ʲ���ʹ�
            } else {
                $rate = 53.00;  // ��˥� 2011/04/01���ʲ���ʹ�
            }
        } else {
            $rate = 65.00;
        }*/
        /*$query = "SELECT  
                    a.plan_no       AS �ײ��ֹ�,
                    a.parts_no      AS �����ֹ�,
                    a.kanryou       AS ��λͽ����,
                    a.plan          AS �ײ��,
                    a.cut_plan      AS ���ڿ�,
                    a.kansei        AS ������,
                    (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$end_date} AND assy_no = a.parts_no ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                                    AS �ǿ��������,
                    CASE
                        WHEN (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$end_date} AND assy_no = a.parts_no ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL
                        THEN 
                             Uround((SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<{$end_date} AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1) * (a.plan-a.cut_plan), 0)
                        ELSE
                             Uround((SELECT sum_price FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$end_date} AND assy_no = a.parts_no ORDER BY assy_no DESC, regdate DESC LIMIT 1) * (a.plan-a.cut_plan), 0) 
                    END             AS ��������,
                    CASE
                        WHEN (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) IS NULL THEN 0
                        ELSE (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1)
                    END
                                    AS �ǿ�����ñ��,
                    CASE
                        WHEN (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) IS NULL THEN 0
                        ELSE Uround((SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) * (a.plan-a.cut_plan), 0)
                    END
                                    AS ����
                    FROM assembly_schedule AS a
                    WHERE a.kanryou<={$end_date} AND a.kanryou>={$str_date} AND a.dept='{$div}'
                    AND (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$end_date} AND assy_no = a.parts_no ORDER BY assy_no DESC, regdate DESC LIMIT 1) > 0
        ";
        */
        // 2011/08/30 ����ñ����¸�ߤ��ʤ���硢��夬�׻�����ʤ��ä���
        // ���κݤϺǿ���������1.13�ܤǻ���ñ����׻���������׻�����褦���ѹ�
        // �ޤ��ǿ��������μ�������WHEN�����оݷ����ޤǤκǿ���ȴ���Ф��Ƥ��뤬plan_no = u.�ײ��ֹ���ѹ�
        // 2011/09/05 ������Ϻ߸ˤ�����Ȥ��˴������ɲä���뤿�ᡢ1.026��ݤ��Ʒ׻�����
        if ($div == 'C') {
            $zai_rate = 1.026;
        } else {
            $zai_rate = 1.026;
        }
        $query = "SELECT
                    a.plan_no       AS �ײ��ֹ�,
                    a.parts_no      AS �����ֹ�,
                    a.kanryou       AS ��λͽ����,
                    a.plan          AS �ײ��,
                    a.cut_plan      AS ���ڿ�,
                    a.kansei        AS ������,
                    CASE
                        WHEN (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE plan_no = a.plan_no ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL
                        THEN 
                             CASE
                                 WHEN (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1) IS NULL
                                 THEN
                                     Uround((SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<{$end_date} AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1) * $zai_rate, 2)
                                 ELSE
                                     Uround((select sum_price from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1) * $zai_rate, 2)
                             END
                        ELSE
                             Uround((SELECT sum_price FROM material_cost_header WHERE plan_no = a.plan_no ORDER BY assy_no DESC, regdate DESC LIMIT 1) * $zai_rate, 2)
                    END             AS �ǿ��������,
                    CASE
                        WHEN (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE plan_no = a.plan_no ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL
                        THEN 
                             CASE
                                 WHEN (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1) IS NULL
                                 THEN
                                     Uround(Uround((SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<{$end_date} AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1) * $zai_rate, 2) * (a.plan-a.cut_plan-a.kansei), 0)
                                 ELSE
                                     Uround(Uround((select sum_price from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1) * $zai_rate, 2) * (a.plan-a.cut_plan-a.kansei), 0)
                             END
                        ELSE
                             Uround(Uround((SELECT sum_price FROM material_cost_header WHERE plan_no = a.plan_no ORDER BY assy_no DESC, regdate DESC LIMIT 1) * $zai_rate, 2) * (a.plan-a.cut_plan-a.kansei), 0) 
                    END             AS ��������,
                    CASE
                        WHEN (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) IS NULL
                        THEN
                            CASE
                                WHEN (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE plan_no = a.plan_no ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL  
                                THEN
                                    CASE
                                        WHEN (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1) IS NULL
                                        THEN
                                            Uround((SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<{$end_date} AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)*1.13, 0)
                                        ELSE
                                            Uround((select sum_price from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)*1.13, 0)
                                    END
                                ELSE
                                    Uround((SELECT sum_price FROM material_cost_header WHERE plan_no = a.plan_no ORDER BY assy_no DESC, regdate DESC LIMIT 1)*1.13, 0)
                            END

                        ELSE (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1)
                    END
                                    AS �ǿ�����ñ��,
                    CASE
                        WHEN (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) IS NULL
                        THEN 
                            CASE
                                WHEN (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE plan_no = a.plan_no ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL  
                                THEN
                                    CASE
                                        WHEN (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1) IS NULL
                                        THEN
                                            Uround(Uround((SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<{$end_date} AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)*1.13, 0) * (a.plan-a.cut_plan-a.kansei), 0)
                                        ELSE
                                            Uround(Uround((select sum_price from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)*1.13, 0) * (a.plan-a.cut_plan-a.kansei), 0)
                                    END
                                ELSE
                                    Uround(Uround((SELECT sum_price FROM material_cost_header WHERE plan_no = a.plan_no ORDER BY assy_no DESC, regdate DESC LIMIT 1)*1.13, 0)  * (a.plan-a.cut_plan-a.kansei), 0) 
                            END
                        ELSE Uround((SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) * (a.plan-a.cut_plan-a.kansei), 0)
                    END
                                    AS ����
                    FROM assembly_schedule AS a
                    WHERE a.kanryou<={$end_date} AND a.kanryou>={$str_date} AND a.dept='{$div}'
                    AND assy_site='01111' AND a.nyuuko!=30 AND p_kubun='F'
        ";
        return $query;
    }
    
    // ����ê����μ���(����δ���ê���� CL����)
    function getQueryStatement2($target_ym, $div)
    {
        if ($div == 'C') {
            $div_note = '���ץ���������ų���ê����';
        } else {
            $div_note = '��˥�ɸ����������ų���ê����';
            //$div_note = '��˥����������ų���ê����';
        }
        if (substr($target_ym,4,2)!=01) {
            $p1_ym = $target_ym - 1;
        } else {
            $p1_ym = $target_ym - 100;
            $p1_ym = $p1_ym + 11;
        }
        $query = "
            SELECT kin FROM profit_loss_pl_history
            WHERE pl_bs_ym={$p1_ym} AND note='{$div_note}'
        ";
        return $query;
    }
    
    // ������μ�����(CL����) 
    // getQueryStatement3����ݼ��Ӥ�����5�ʾ塢�������칩��(01111)������(00222)�����Ͻ���
    function getQueryStatement3($target_ym, $div)
    {
        $str_date = $target_ym . '01';
        $end_date = $target_ym . '31';
        // ���ܣ��ʾ夬���äƤ������ᣵ�ޤ��ѹ�
        /*
        $query = "
            select 
            sum(Uround(order_price * siharai,0)) 
            FROM act_payable 
            WHERE act_date>=$str_date AND act_date<=$end_date AND div='{$div}' AND vendor !='01111' AND vendor !='00222'
        ";
        */
        $query = "
            select 
            sum(Uround(order_price * siharai,0)) 
            FROM act_payable 
            WHERE act_date>=$str_date AND act_date<=$end_date AND div='{$div}' AND vendor !='01111' AND vendor !='00222' AND kamoku<=5
        ";
        return $query;
    }
    
    // ������μ�����(CL����)
    // getQueryStatement4�������ų�ʬ(̤�������)�ι�פ���� ���Ͻ���
    function getQueryStatement4($div)
    {
        $query = "
            SELECT  sum(Uround(data.order_q * data.order_price,0))
                FROM
                    order_data          AS data
                LEFT OUTER JOIN
                    acceptance_kensa    AS ken  on(data.order_seq=ken.order_seq)
                LEFT OUTER JOIN
                    order_plan          AS plan     USING (sei_no)
                WHERE
                    ken_date <= 0       -- ̤����ʬ
                    AND
                    data.sei_no > 0     -- ��¤�ѤǤ���
                    AND
                    (data.order_q - data.cut_genpin) > 0  -- ���ڤ���Ƥ��ʤ�ʪ
                    AND
                    ( (ken.end_timestamp IS NULL) OR (ken.end_timestamp >= (CURRENT_TIMESTAMP - interval '10 minute')) )
                    AND
                    uke_no > '500000' AND data.parts_no LIKE '{$div}%' and vendor !='01111' and vendor !='00222'
        ";
        return $query;
    }
    
    // ������μ�����(CL����)
    // getQueryStatement5�������ų�ʬ(Ǽ���٤�)�ι�פ���� ���Ͻ���
    function getQueryStatement5($target_ym, $today, $div)
    {
        $str_date = $target_ym - 200;
        $str_date = $str_date . '01';
        $end_date = $today;
        $query = "
            SELECT  sum(Uround(data.order_q * data.order_price,0))
                FROM
                    order_data      AS data
                LEFT OUTER JOIN
                    order_process   AS proc
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan     USING (sei_no)
                WHERE
                    proc.delivery <= {$end_date}
                    AND
                    proc.delivery >= {$str_date}
                    AND
                    uke_date <= 0       -- ̤Ǽ��ʬ
                    AND
                    ken_date <= 0       -- ̤����ʬ
                    AND
                    data.sei_no > 0     -- ��¤�ѤǤ���
                    AND
                    (data.order_q - data.cut_genpin) > 0  -- ���ڤ���Ƥ��ʤ�ʪ
                    AND
                    data.parts_no like '{$div}%' AND proc.locate != '52   ' and vendor !='01111' and vendor !='00222'
        ";
        return $query;
    }
    
    // ������μ�����(CL����)
    // getQueryStatement6�������ų�ʬ(̤Ǽ��ʬ)�ι�פ���� ���Ͻ���
    function getQueryStatement6($target_ym, $today, $div)
    {
        $end_date = $target_ym;
        $end_date = $end_date . '31';
        $str_date = $today;
        $query = "
            SELECT  substr(to_char(proc.delivery, 'FM9999-99-99'), 3, 8) AS delivery
                    , count(proc.delivery) AS cnt
                    , sum(Uround(data.order_q * data.order_price,0)) as kin
                FROM
                    order_data      AS data
                LEFT OUTER JOIN
                    order_process   AS proc
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan     USING (sei_no)
                WHERE
                    proc.delivery > $str_date
                    AND
                    proc.delivery <= $end_date
                    AND
                    uke_date <= 0       -- ̤Ǽ��ʬ
                    AND
                    ken_date <= 0       -- ̤����ʬ
                    AND
                    data.sei_no > 0     -- ��¤�ѤǤ���
                    AND
                    (data.order_q - data.cut_genpin) > 0  -- ���ڤ���Ƥ��ʤ�ʪ
                    AND
                    data.parts_no like '{$div}%' AND proc.locate != '52   ' and vendor !='01111' and vendor !='00222'
                GROUP BY
                    proc.delivery
                ORDER BY
                    proc.delivery ASC

        ";
        return $query;
    }
    
    // ������μ�����(CL����)
    // getQueryStatement7�������ų�ʬ ��������(��ʸ��̤ȯ��) ��Ǽ���٤�ʬ
    function getQueryStatement7($target_ym, $today, $div)
    {
        $str_date = $target_ym - 200;
        $str_date = $str_date . '01';
        $end_date = $today;
        $query = "
            SELECT sum(Uround(plan.order_q * proc.order_price,0))
                FROM
                    order_process   AS proc
                LEFT OUTER JOIN
                    order_data      AS data
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan
                                            using(sei_no)
                WHERE
                    proc.delivery <= {$end_date}
                    AND
                    proc.delivery >= {$str_date}
                    AND
                    proc.sei_no > 0                 -- ��¤�ѤǤ���
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '%0'       -- �鹩�������
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '_00000_'  -- ������֤�ʪ�����
                    AND
                    proc.plan_cond='R'              -- ��ʸ��ͽ��Τ��
                    AND
                    data.order_no IS NULL           -- ��ʸ�񤬼ºݤ�̵��ʪ
                    AND
                    (SELECT sub_order.order_q - sub_order.cut_siharai FROM order_process AS sub_order WHERE sub_order.sei_no=proc.sei_no AND to_char(sub_order.order_no, 'FM9999999') LIKE '%0' LIMIT 1) > 0
                                                    -- �鹩�������ڤ���Ƥ��ʤ�ʪ
                    AND
                    proc.parts_no like '{$div}%' AND proc.locate != '52   ' and vendor !='01111' and vendor !='00222'
        ";
        return $query;
    }
    
    // ������μ�����(CL����)
    // getQueryStatement7�������ų�ʬ ��������(��ʸ��̤ȯ��) ̤Ǽ��ʬ
    function getQueryStatement8($target_ym, $today, $div)
    {
        $end_date = $target_ym . '31';
        $str_date = $today;
        $query = "
            SELECT  substr(to_char(proc.delivery, 'FM9999-99-99'), 3, 8) AS delivery
                    , count(proc.delivery) AS cnt
                    , sum(Uround(plan.order_q * proc.order_price,0)) as kin
                FROM
                    order_process   AS proc
                LEFT OUTER JOIN
                    order_data      AS data
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan
                                            using(sei_no)
                WHERE
                    proc.delivery > {$str_date}
                    AND
                    proc.delivery <= {$end_date}
                    AND
                    proc.sei_no > 0                 -- ��¤�ѤǤ���
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '%0'       -- �鹩�������
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '_00000_'  -- ������֤�ʪ�����
                    AND
                    proc.plan_cond='R'              -- ��ʸ��ͽ��Τ��
                    AND
                    data.order_no IS NULL           -- ��ʸ�񤬼ºݤ�̵��ʪ
                    AND
                    (SELECT sub_order.order_q - sub_order.cut_siharai FROM order_process AS sub_order WHERE sub_order.sei_no=proc.sei_no AND to_char(sub_order.order_no, 'FM9999999') LIKE '%0' LIMIT 1) > 0
                                                    -- �鹩�������ڤ���Ƥ��ʤ�ʪ
                    AND
                    proc.parts_no like '{$div}%' AND proc.locate != '52   ' and vendor !='01111' and vendor !='00222'
                GROUP BY
                    proc.delivery
                ORDER BY
                    proc.delivery ASC

        ";
        return $query;
    }
    
    // ����ê����μ�����(CL����)   // �����ޤǤ���ݶ��
    function getQueryStatement9($target_ym, $div)
    {
        $str_date = $target_ym . '01';
        $end_date = $target_ym . '31';
        // ���ܣ��ʾ夬���äƤ������ᣵ�ޤ��ѹ�
        /*
        $query = "
            select sum(Uround(order_price * siharai,0)) 
            from act_payable 
            where act_date>={$str_date} and act_date<={$end_date} and div='{$div}' 
        ";
        */
        $query = "
            select sum(Uround(order_price * siharai,0)) 
            from act_payable 
            where act_date>={$str_date} and act_date<={$end_date} and div='{$div}' and kamoku<=5
        ";
        return $query;
    }
    
    // ����ê����μ�����(CL����) �����ų�ʬ(̤�������)�ι�פ����
    function getQueryStatement10($div)
    {
        $query = "
            SELECT  sum(Uround(data.order_q * data.order_price,0))
                FROM
                    order_data          AS data
                LEFT OUTER JOIN
                    acceptance_kensa    AS ken  on(data.order_seq=ken.order_seq)
                LEFT OUTER JOIN
                    order_plan          AS plan     USING (sei_no)
                WHERE
                    ken_date <= 0       -- ̤����ʬ
                    AND
                    data.sei_no > 0     -- ��¤�ѤǤ���
                    AND
                    (data.order_q - data.cut_genpin) > 0  -- ���ڤ���Ƥ��ʤ�ʪ
                    AND
                    ( (ken.end_timestamp IS NULL) OR (ken.end_timestamp >= (CURRENT_TIMESTAMP - interval '10 minute')) )
                    AND
                    uke_no > '500000' AND data.parts_no LIKE '{$div}%'
        ";
        return $query;
    }
    
    // ����ê����μ�����(CL����) Ǽ���٤�ʬ�ι�פ����
    function getQueryStatement11($target_ym, $today, $div)
    {
        $str_date = $target_ym - 200;
        $str_date = $str_date . '01';
        $end_date = $today - 1;
        $query = "
            SELECT sum(Uround(data.order_q * data.order_price,0))
                FROM
                    order_data      AS data
                LEFT OUTER JOIN
                    order_process   AS proc
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan     USING (sei_no)
                WHERE
                    proc.delivery <= {$end_date}
                    AND
                    proc.delivery >= {$str_date}
                    AND
                    uke_date <= 0       -- ̤Ǽ��ʬ
                    AND
                    ken_date <= 0       -- ̤����ʬ
                    AND
                    data.sei_no > 0     -- ��¤�ѤǤ���
                    AND
                    (data.order_q - data.cut_genpin) > 0  -- ���ڤ���Ƥ��ʤ�ʪ
                    AND
                    data.parts_no like '{$div}%' AND proc.locate != '52   '
        ";
        return $query;
    }
    
    // ����ê����μ�����(CL����) �����ʹߤΥ��ޥ꡼�����
    function getQueryStatement12($target_ym, $today, $div)
    {
        $end_date = $target_ym . '31';
        $str_date = $today;
        $query = "
            SELECT  substr(to_char(proc.delivery, 'FM9999-99-99'), 3, 8) AS delivery
                    , count(proc.delivery) AS cnt
                    , sum(Uround(data.order_q * data.order_price,0)) as kin
                FROM
                    order_data      AS data
                LEFT OUTER JOIN
                    order_process   AS proc
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan     USING (sei_no)
                WHERE
                    proc.delivery >= {$str_date}
                    AND
                    proc.delivery <= {$end_date}
                    AND
                    uke_date <= 0       -- ̤Ǽ��ʬ
                    AND
                    ken_date <= 0       -- ̤����ʬ
                    AND
                    data.sei_no > 0     -- ��¤�ѤǤ���
                    AND
                    (data.order_q - data.cut_genpin) > 0  -- ���ڤ���Ƥ��ʤ�ʪ
                    AND
                    data.parts_no like '{$div}%' AND proc.locate != '52   '
                GROUP BY
                    proc.delivery
                ORDER BY
                    proc.delivery ASC
        ";
        return $query;
    }
    // ����ê����μ�����(CL����) ��������(��ʸ��̤ȯ��) Ǽ���٤�ʬ�ι�פ����
    function getQueryStatement13($target_ym, $today, $div)
    {
        $str_date = $target_ym - 200;
        $str_date = $str_date . '01';
        $end_date = $today - 1;
        $query = "
            SELECT sum(Uround(plan.order_q * proc.order_price,0))
                FROM
                    order_process   AS proc
                LEFT OUTER JOIN
                    order_data      AS data
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan
                                            using(sei_no)
                WHERE
                    proc.delivery <= {$end_date}
                    AND
                    proc.delivery >= {$str_date}
                    AND
                    proc.sei_no > 0                 -- ��¤�ѤǤ���
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '%0'       -- �鹩�������
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '_00000_'  -- ������֤�ʪ�����
                    AND
                    proc.plan_cond='R'              -- ��ʸ��ͽ��Τ��
                    AND
                    data.order_no IS NULL           -- ��ʸ�񤬼ºݤ�̵��ʪ
                    AND
                    (SELECT sub_order.order_q - sub_order.cut_siharai FROM order_process AS sub_order WHERE sub_order.sei_no=proc.sei_no AND to_char(sub_order.order_no, 'FM9999999') LIKE '%0' LIMIT 1) > 0
                                                    -- �鹩�������ڤ���Ƥ��ʤ�ʪ
                    AND
                    proc.parts_no like '{$div}%' AND proc.locate != '52   '
        ";
        return $query;
    }
    // ����ê����μ�����(CL����) ��������(��ʸ��̤ȯ��) �����ʹߤΥ��ޥ꡼�����
    function getQueryStatement14($target_ym, $today, $div)
    {
        $end_date = $target_ym . '31';
        $str_date = $today;
        $query = "
            SELECT  substr(to_char(proc.delivery, 'FM9999-99-99'), 3, 8) AS delivery
                    , count(proc.delivery) AS cnt
                    , sum(Uround(plan.order_q * proc.order_price,0)) as kin
                FROM
                    order_process   AS proc
                LEFT OUTER JOIN
                    order_data      AS data
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan
                                            using(sei_no)
                WHERE
                    proc.delivery >= {$str_date}
                    AND
                    proc.delivery <= {$end_date}
                    AND
                    proc.sei_no > 0                 -- ��¤�ѤǤ���
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '%0'       -- �鹩�������
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '_00000_'  -- ������֤�ʪ�����
                    AND
                    proc.plan_cond='R'              -- ��ʸ��ͽ��Τ��
                    AND
                    data.order_no IS NULL           -- ��ʸ�񤬼ºݤ�̵��ʪ
                    AND
                    (SELECT sub_order.order_q - sub_order.cut_siharai FROM order_process AS sub_order WHERE sub_order.sei_no=proc.sei_no AND to_char(sub_order.order_no, 'FM9999999') LIKE '%0' LIMIT 1) > 0
                                                    -- �鹩�������ڤ���Ƥ��ʤ�ʪ
                    AND
                    proc.parts_no like '{$div}%' AND proc.locate != '52   '
                GROUP BY
                    proc.delivery
                ORDER BY
                    proc.delivery ASC
        ";
        return $query;
    }
    ///// ���ʡ�����¾���⡢������μ���
    // getQueryStatement15����������ʶ�ʬ��2�ʾ�������6����ʬ�������ʿ�ѡ���������碌�ơ�
    function getQueryStatement15($target_ym, $div)
    {
        $end_date = $target_ym;
        $str_date = $target_ym;
        if (substr($str_date,4,2)>=07) {
            $str_date = $str_date - 6;
            $str_date = $str_date . '01';
        } else {
            $str_date = $str_date - 100;
            $str_date = $str_date + 6;
            $str_date = $str_date . '01';
        }
        if (substr($end_date,4,2)!=01) {
            $end_date = $end_date - 1;
            $end_date = $end_date . '31';
        } else {
            $end_date = $end_date - 100;
            $end_date = $end_date + 11;
            $end_date = $end_date . '31';
        }
        $query = "
            SELECT
                Uround(sum(Uround(����*ñ��, 0)) / 6, 0)         AS ��������
                ,
                Uround(sum(Uround(����*ext_cost, 0)) / 6, 0)       AS ����������
                ,
                Uround(sum(Uround(����*int_cost, 0)) / 6, 0)      AS ���������
                ,
                Uround(sum(Uround(����*unit_cost, 0)) / 6, 0)      AS ���������
                ,
                count(*)                            AS ����
                ,
                count(*)-count(unit_cost)
                                                    AS ̤��Ͽ
            FROM
                hiuuri
            LEFT OUTER JOIN
                sales_parts_material_history ON (assyno=parts_no AND �׾���=sales_date)
            WHERE �׾��� >= {$str_date} AND �׾��� <= {$end_date}
             AND ������ = '{$div}' AND (assyno not like 'NKB%%') AND (assyno not like 'SS%%')
             AND datatype >= '2' 
        ";
        return $query;
    }
    ///// ϫ̳�񡦷����ۼ���
    function getQueryStatement16($target_ym, $note_name)
    {
        
            $end_date = $target_ym;
            $str_date = $target_ym;
            if (substr($str_date,4,2)==12) {
                $str_date = $str_date - 11;
            } else {
                $str_date = $str_date - 99;
            }
            if (substr($end_date,4,2)!=01) {
                $end_date = $end_date - 1;
            } else {
                $end_date = $end_date - 100;
                $end_date = $end_date + 11;
            }
            $query = "
                SELECT sum(kin) FROM profit_loss_pl_history
                    WHERE pl_bs_ym<={$end_date} AND pl_bs_ym>={$str_date} AND note='{$note_name}'
        ";
        return $query;
    }
    // ����ȴ���ê����ΰ��������(CL����) ������٤��
    // getQueryStatement17�������ޤǤ�����ʴ����Τߡˡ����Ѻ�������������ꡣ
    function getQueryStatement17($target_ym, $today, $div)
    {
        $str_date  = $target_ym . '01';
        $end_date  = $today;
        if (substr($end_date,6,2)!=01) {
            $end_date  = $end_date - 1;
        }
        $cost_date = $target_ym . '31';
        /*if ($div == 'C') {
            if ($target_ym < 200710) {
                $rate = 25.60;  // ���ץ�ɸ�� 2007/10/01���ʲ������
            } elseif ($target_ym < 201104) {
                $rate = 57.00;  // ���ץ�ɸ�� 2007/10/01���ʲ���ʹ�
            } else {
                $rate = 45.00;  // ���ץ�ɸ�� 2011/04/01���ʲ���ʹ�
            }
        } elseif ($div == 'L') {
            if ($target_ym < 200710) {
                $rate = 37.00;  // ��˥� 2008/10/01���ʲ������
            } elseif ($target_ym < 201104) {
                $rate = 44.00;  // ��˥� 2008/10/01���ʲ���ʹ�
            } else {
                $rate = 53.00;  // ��˥� 2011/04/01���ʲ���ʹ�
            }
        } else {
            $rate = 65.00;
        }*/
        if ($div == 'C') {
            $zai_rate = 1.026;
        } else {
            $zai_rate = 1.026;
        }
        $query = "select
                        u.�׾���        as �׾���,                  -- 0
                            CASE
                                WHEN u.datatype=1 THEN '����'
                                WHEN u.datatype=2 THEN '����'
                                WHEN u.datatype=3 THEN '����'
                                WHEN u.datatype=4 THEN 'Ĵ��'
                                WHEN u.datatype=5 THEN '��ư'
                                WHEN u.datatype=6 THEN 'ľǼ'
                                WHEN u.datatype=7 THEN '���'
                                WHEN u.datatype=8 THEN '����'
                                WHEN u.datatype=9 THEN '����'
                                ELSE u.datatype
                            END             as ��ʬ,                    -- 1
                            CASE
                                WHEN trim(u.�ײ��ֹ�)='' THEN '---'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                ELSE u.�ײ��ֹ�
                            END                     as �ײ��ֹ�,        -- 2
                            CASE
                                WHEN trim(u.assyno) = '' THEN '---'
                                ELSE u.assyno
                            END                     as �����ֹ�,        -- 3
                            CASE
                                WHEN trim(u.���˾��)='' THEN '--'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                ELSE u.���˾��
                            END                     as ����,            -- 4
                            u.����          as ����,                    -- 5
                            CASE
                                WHEN (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE plan_no = u.�ײ��ֹ� ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL
                                THEN
                                    CASE
                                        WHEN (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1) IS NULL
                                        THEN
                                            Uround((SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<{$cost_date} AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1) * $zai_rate, 2)
                                        ELSE
                                            Uround((select sum_price from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1) * $zai_rate, 2)
                                    END
                                ELSE
                                    Uround((SELECT sum_price FROM material_cost_header WHERE plan_no = u.�ײ��ֹ� ORDER BY assy_no DESC, regdate DESC LIMIT 1) * $zai_rate, 2)
                            END             AS �ǿ��������,            -- 6
                            CASE
                                WHEN (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE plan_no = u.�ײ��ֹ� ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL
                                THEN
                                    CASE
                                        WHEN (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1) IS NULL
                                        THEN
                                            Uround(Uround((SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<{$cost_date} AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1) * $zai_rate, 2) * u.����, 0)
                                        ELSE
                                            Uround(Uround((select sum_price from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1) * $zai_rate, 2) * u.����, 0)
                                    END
                                ELSE
                                    Uround(Uround((SELECT sum_price FROM material_cost_header WHERE plan_no = u.�ײ��ֹ� ORDER BY assy_no DESC, regdate DESC LIMIT 1) * $zai_rate, 2) * u.����, 0)
                            END             AS ��������,              -- 7
                            u.ñ��          as ����ñ��,                -- 8
                            Uround(u.���� * u.ñ��, 0) as ���          -- 9
                      from
                            hiuuri as u
                      left outer join
                            assembly_schedule as a
                      on u.�ײ��ֹ�=a.plan_no
                      left outer join
                            miitem as m
                      on u.assyno=m.mipn
                      left outer join
                            material_cost_header as mate
                      on u.�ײ��ֹ�=mate.plan_no
                      LEFT OUTER JOIN
                            sales_parts_material_history AS pmate
                      ON (u.assyno=pmate.parts_no AND u.�׾���=pmate.sales_date) 
                      where �׾���>={$str_date} and �׾���<={$end_date} and ������='{$div}' and datatype='1'
                      order by u.�׾���, assyno
        ";
        return $query;
    }
?>
