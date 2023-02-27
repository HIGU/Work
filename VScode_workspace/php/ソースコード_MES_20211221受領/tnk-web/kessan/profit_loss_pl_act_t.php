<?php
//////////////////////////////////////////////////////////////////////////////
// �»�״ط� � �ã̡����ʴ���������� »�׷׻���                    //
// Copyright (C) 2003-2010 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2003/02/12 Created   profit_loss_pl_act.php                              //
// 2003/02/23 date("Y/m/d H:m:s") �� H:i:s �Υߥ�����                       //
// 2003/03/04 ʸ����������֥饦�������ѹ��Ǥ��ʤ����� title_font ��        //
//            �õ�����򥫥ץ顦��˥��ʳ������ΤȤ���¾���ɲ�              //
// 2003/03/06 title_font today_font ������ �����ʲ��η��������ɲ�         //
// 2003/03/11 Location: http �� Location $url_referer ���ѹ�                //
//            ��å���������Ϥ��뤿�� site_index site_id �򥳥��Ȥˤ�    //
//            parent.menu_site.��ͭ�����ѹ�                                 //
// 2003/05/01 ����Ĺ����λؼ���ǧ�ڤ�Account_group�����̾���ѹ�           //
// 2003/08/05 $p1_c_srisoku �� $p1_l_srisoku �ˤʤäƤ����Τ���           //
// 2003/12/15 �δ���ڤ�  ���̴����� �� �� ���̴������ (���ڡ�������)    //
// 2004/05/11 ��¦�Υ����ȥ�˥塼�Υ��󡦥��� �ܥ�����ɲ�                 //
// 2005/10/26 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
// 2005/11/08 $menu->out_action('�õ���������')��<a href=���ɲ�             //
// 2006/03/07 ���� style='overflow-y:hidden;' �򤦤ä����դ������ᥳ����  //
// 2007/11/08 ����λ��������ɽ����$p1_l_swari �� $p1_c_swari ������       //
// 2009/08/17 ʪή��»��ɽ�����ɲáʻ����                             ��ë //
// 2009/08/18 ������������»��ɽ�����ɲáʻ����                   ��ë //
// 2009/08/19 ʪή���ʴ�����̾���ѹ�                                 ��ë //
// 2009/08/20 �����Ȥ��Խ�                                           ��ë //
// 2009/08/21 »�פ�Excel�ˤ��碌��200904��200906��Ĵ�������줿        ��ë //
// 2009/10/06 ���ɤ����⤬AS����Ͽ���줿�ΤǤ����б�200909���       ��ë //
//            ���ϲ��̤�Ĵ����ۤ����Ϥ����ä��ǥޥ��ʥ�����           ��ë //
// 2009/10/15 ���⡦��������ס��Ķ����ס��о����פ��������ѹ�       ��ë //
// 2009/10/29 ���ɤؤμҰ���Ϳ��ʬ�û����б�$��_allo_kin               ��ë //
// 2009/11/09 ���ɤ����Ĵ�����������������äƤ��ʤ��ä��Τǽ���   ��ë //
// 2009/11/12 ��˥���Ĵ����ۤ����ޤ������ʤ��ä��Τ���           ��ë //
// 2009/12/07 ���ץ�������������̣����褦�ѹ�                 ��ë //
// 2009/12/10 �����Ĵ��                                               ��ë //
// 2010/01/15 200912�٤�ź�Ĥ����ϫ̳���Ĵ��                         ��ë //
// 2010/01/19 200912�٤ζ�̳���������Ȥ���¾��Ĵ����1�����ᤷ��ʬ���  ��ë //
// 2010/02/01 201001�٤��Ķȳ���Ͱ���Ψ�ǺƷ׻������ͤ��֤�����     ��ë //
// 2010/02/04 201001�٤�ź�Ĥ����ϫ̳���Ĵ��                              //
//            ϫ̳������Ϥ������ꤹ��褦�ץ��������ͽ��         ��ë //
// 2010/02/08 201001�٤������ꤷ��ϫ̳����̣����褦���ѹ�           ��ë //
// 2010/03/04 201002�ٱĶȳ����פ���¾��Ĵ�����ɲá�201003�ˤ��ᤷ     ��ë //
// 2010/04/08 $p2_l_kyu_kin��2��ȴ���Ƥ�����������                     ��ë //
// 2010/04/12 ����Υ�˥��о����פǷ���ڤ꤬����Ƥ��ʤ��ä��Τ����� ��ë //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);    // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('error_reporting', E_ALL);       // E_ALL='2047' debug ��
// ini_set('display_errors', '1');          // Error ɽ�� ON debug �� ��꡼���女����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../function.php');           // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../tnk_func.php');           // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../MenuHeader.php');         // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
   // �ºݤ�ǧ�ڤ�profit_loss_submit.php�ǹԤäƤ���account_group_check()�����

///// ����������
// $menu->set_site(10, 7);                  // site_index=10(»�ץ�˥塼) site_id=7(�»��)
///// ɽ�������
$menu->set_caption('�������칩��(��)');
///// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('�õ���������',   PL . 'profit_loss_comment_put.php');

///// ������μ���
$ki = Ym_to_tnk($_SESSION['pl_ym']);
$tuki = substr($_SESSION['pl_ym'],4,2);

///// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title("�� {$ki} ����{$tuki} ���١��� �� � ���� �� �� �� » �� �� �� ��");

///// �о�����
$yyyymm = $_SESSION['pl_ym'];
///// �о�����
if (substr($yyyymm,4,2)!=01) {
    $p1_ym = $yyyymm - 1;
} else {
    $p1_ym = $yyyymm - 100;
    $p1_ym = $p1_ym + 11;
}
///// �о�������
if (substr($p1_ym,4,2)!=01) {
    $p2_ym = $p1_ym - 1;
} else {
    $p2_ym = $p1_ym - 100;
    $p2_ym = $p2_ym + 11;
}
///// ����ǯ��λ���
$yyyy = substr($yyyymm, 0,4);
$mm   = substr($yyyymm, 4,2);
if (($mm >= 1) && ($mm <= 3)) {
    $yyyy = ($yyyy - 1);
}
$str_ym = $yyyy . "04";     // ����ǯ��

///// ɽ��ñ�̤��������
if (isset($_POST['keihi_tani'])) {
    $_SESSION['keihi_tani'] = $_POST['keihi_tani'];
    $tani = $_SESSION['keihi_tani'];
} elseif (isset($_SESSION['keihi_tani'])) {
    $tani = $_SESSION['keihi_tani'];
} else {
    $tani = 1000;           // ����� ɽ��ñ�� ���
    $_SESSION['keihi_tani'] = $tani;
}
///// ɽ�� ��������� �������
if (isset($_POST['keihi_keta'])) {
    $_SESSION['keihi_keta'] = $_POST['keihi_keta'];
    $keta = $_SESSION['keihi_keta'];
} elseif (isset($_SESSION['keihi_keta'])) {
    $keta = $_SESSION['keihi_keta'];
} else {
    $keta = 0;              // ����� �������ʲ����
    $_SESSION['keihi_keta'] = $keta;
}

/********** ���� **********/
    ///// ����
if ( $yyyymm >= 200909) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��������'", $yyyymm);
    if (getUniResult($query, $b_uri) < 1) {
        $b_uri        = 0;      // ��������
        $b_uri_sagaku = 0;
    } else {
        if ($yyyymm == 201004) {
            $b_uri = $b_uri + 255240;
        }
        $b_uri_sagaku = 0;
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�������Ĵ����'", $yyyymm);
    if (getUniResult($query, $b_uri_cho) < 1) {
        // �������� Ĵ����̵���Τǲ��⤷�ʤ�
        $b_uri_sagaku = 0;
    } else {
        $b_uri        = $b_uri + $b_uri_cho;
        $b_uri_sagaku = $b_uri_cho;
    }
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��������'", $yyyymm);
    if (getUniResult($query, $b_uri) < 1) {
        $b_uri        = 0;      // ��������
        $b_uri_sagaku = 0;
    } else {
        $b_uri_sagaku = $b_uri;
    }
}
if ( $yyyymm >= 200911) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�����'", $yyyymm);
    if (getUniResult($query, $sc_uri) < 1) {
        $sc_uri        = 0;     // ��������
        $sc_uri_sagaku = 0;
        $sc_uri_temp   = 0;
    } else {
        $sc_uri_temp   = $sc_uri;
        $sc_uri_sagaku = $sc_uri;
        $sc_uri        = number_format(($sc_uri / $tani), $keta);
    }
} else{
    $sc_uri        = 0;         // ��������
    $sc_uri_sagaku = 0;
    $sc_uri_temp   = 0;
}
if ( $yyyymm >= 200909) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�����'", $yyyymm);
    if (getUniResult($query, $s_uri) < 1) {
        $s_uri        = 0;      // ��������
        $s_uri_sagaku = 0;
        $s_uri_temp   = 0;
    } else {
        $s_uri_temp = $s_uri;
        if ($yyyymm == 200906) {
            $s_uri  = $s_uri - 3100900;
        } elseif ($yyyymm == 200905) {
            $s_uri  = $s_uri + 1550450;
        } elseif ($yyyymm == 200904) {
            $s_uri  = $s_uri + 1550450;
        }
        $s_uri_sagaku = $s_uri;
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='����Ĵ����'", $yyyymm);
    if (getUniResult($query, $s_uri_cho) < 1) {
        // ��������
        $s_uri = $s_uri + $sc_uri_sagaku;       // ���ץ��������̣
        $s_uri = number_format(($s_uri / $tani), $keta);
    } else {
        $s_uri_sagaku = $s_uri_sagaku + $s_uri_cho;
        $s_uri_temp   = $s_uri_sagaku;
        $s_uri        = $s_uri_sagaku + $sc_uri_sagaku;         // ���ץ��������̣��temp�θ�ݥ�˥�����ޥ��ʥ����Ƥ��ޤ��١�
        $s_uri        = number_format(($s_uri / $tani), $keta);
    }
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�����'", $yyyymm);
    if (getUniResult($query, $s_uri) < 1) {
        $s_uri        = 0;      // ��������
        $s_uri_sagaku = 0;
        $s_uri_temp   = 0;
    } else {
        $s_uri_temp = $s_uri;
        if ($yyyymm == 200906) {
            $s_uri  = $s_uri - 3100900;
        } elseif ($yyyymm == 200905) {
            $s_uri  = $s_uri + 1550450;
        } elseif ($yyyymm == 200904) {
            $s_uri  = $s_uri + 1550450;
        }
        $s_uri_sagaku = $s_uri;
        $s_uri        = number_format(($s_uri / $tani), $keta);
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��������'", $yyyymm);
if (getUniResult($query, $all_uri) < 1) {
    $all_uri = 0;               // ��������
} else {
    if ($yyyymm == 200906) {
        $all_uri = $all_uri + $b_uri_sagaku - 3100900;
    } elseif ($yyyymm == 200905) {
        $all_uri = $all_uri + $b_uri_sagaku + 1550450;
    } elseif ($yyyymm == 200904) {
        $all_uri = $all_uri + $b_uri_sagaku + 1550450;
    } else {
        $all_uri = $all_uri + $b_uri_sagaku;
    }
    $all_uri = number_format(($all_uri / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�����'", $yyyymm);
if (getUniResult($query, $c_uri) < 1) {
    $c_uri = 0;                 // ��������
} else {
    $c_uri = $c_uri - $sc_uri_sagaku;                   // ���ץ��������̣
    $c_uri = number_format(($c_uri / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�����'", $yyyymm);
if (getUniResult($query, $l_uri) < 1) {
    $l_uri = 0 - $s_uri_temp;   // ��������
} else {
    $l_uri = $l_uri - $s_uri_temp;
    if ($yyyymm == 201004) {
        $l_uri = $l_uri - 255240;
    }
    $l_uri = number_format(($l_uri / $tani), $keta);
}
    ///// ����
if ($yyyymm >= 200910) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��������'", $p1_ym);
    if (getUniResult($query, $p1_b_uri) < 1) {
        $p1_b_uri        = 0;   // ��������
        $p1_b_uri_sagaku = 0;
    } else {
        if ($p1_ym == 201004) {
            $p1_b_uri = $p1_b_uri + 255240;
        }
        $p1_b_uri_sagaku = 0;
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�������Ĵ����'", $p1_ym);
    if (getUniResult($query, $p1_b_uri_cho) < 1) {
        // �������� Ĵ����̵���Τǲ��⤷�ʤ�
    } else {
        $p1_b_uri        = $p1_b_uri + $p1_b_uri_cho;
        $p1_b_uri_sagaku = $p1_b_uri_cho;
    }
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��������'", $p1_ym);
    if (getUniResult($query, $p1_b_uri) < 1) {
        $p1_b_uri        = 0;   // ��������
        $p1_b_uri_sagaku = 0;
    } else {
        $p1_b_uri_sagaku = $p1_b_uri;
    }
}
if ( $yyyymm >= 200911) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�����'", $p1_ym);
    if (getUniResult($query, $p1_sc_uri) < 1) {
        $p1_sc_uri        = 0;      // ��������
        $p1_sc_uri_sagaku = 0;
        $p1_sc_uri_temp   = 0;
    } else {
        $p1_sc_uri_temp   = $p1_sc_uri;
        $p1_sc_uri_sagaku = $p1_sc_uri;
        $p1_sc_uri        = number_format(($p1_sc_uri / $tani), $keta);
    }
} else{
    $p1_sc_uri        = 0;          // ��������
    $p1_sc_uri_sagaku = 0;
    $p1_sc_uri_temp   = 0;
}
if ($yyyymm >= 200910) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�����'", $p1_ym);
    if (getUniResult($query, $p1_s_uri) < 1) {
        $p1_s_uri        = 0;       // ��������
        $p1_s_uri_sagaku = 0;
        $p1_s_uri_temp   = 0;
    } else {
        $p1_s_uri_temp = $p1_s_uri;
        if ($p1_ym == 200906) {
            $p1_s_uri  = $p1_s_uri - 3100900;
        } elseif ($p1_ym == 200905) {
            $p1_s_uri  = $p1_s_uri + 1550450;
        } elseif ($p1_ym == 200904) {
            $p1_s_uri  = $p1_s_uri + 1550450;
        }
        $p1_s_uri_sagaku = $p1_s_uri;
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='����Ĵ����'", $p1_ym);
    if (getUniResult($query, $p1_s_uri_cho) < 1) {
        // ��������
        $p1_s_uri = $p1_s_uri + $p1_sc_uri_sagaku;                  // ���ץ��������̣
        $p1_s_uri = number_format(($p1_s_uri / $tani), $keta);
    } else {
        $p1_s_uri_sagaku = $p1_s_uri_sagaku + $p1_s_uri_cho;
        $p1_s_uri_temp   = $p1_s_uri_sagaku;
        $p1_s_uri        = $p1_s_uri_sagaku + $p1_sc_uri_sagaku;    // ���ץ��������̣��temp�θ�ݥ�˥�����ޥ��ʥ����Ƥ��ޤ��١�
        $p1_s_uri        = number_format(($p1_s_uri / $tani), $keta);
    }
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�����'", $p1_ym);
    if (getUniResult($query, $p1_s_uri) < 1) {
        $p1_s_uri        = 0;           // ��������
        $p1_s_uri_sagaku = 0;
        $p1_s_uri_temp   = 0;
    } else {
        $p1_s_uri_temp = $p1_s_uri;
        if ($p1_ym == 200906) {
            $p1_s_uri  = $p1_s_uri - 3100900;
        } elseif ($p1_ym == 200905) {
            $p1_s_uri  = $p1_s_uri + 1550450;
        } elseif ($p1_ym == 200904) {
            $p1_s_uri  = $p1_s_uri + 1550450;
        }
        $p1_s_uri_sagaku = $p1_s_uri;
        $p1_s_uri        = number_format(($p1_s_uri / $tani), $keta);
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��������'", $p1_ym);
if (getUniResult($query, $p1_all_uri) < 1) {
    $p1_all_uri = 0;                    // ��������
} else {
    if ($p1_ym == 200906) {
        $p1_all_uri = $p1_all_uri + $p1_b_uri_sagaku - 3100900;
    } elseif ($p1_ym == 200905) {
        $p1_all_uri = $p1_all_uri + $p1_b_uri_sagaku + 1550450;
    } elseif ($p1_ym == 200904) {
        $p1_all_uri = $p1_all_uri + $p1_b_uri_sagaku + 1550450;
    } else {
        $p1_all_uri = $p1_all_uri + $p1_b_uri_sagaku;
    }
    $p1_all_uri = number_format(($p1_all_uri / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�����'", $p1_ym);
if (getUniResult($query, $p1_c_uri) < 1) {
    $p1_c_uri = 0;                      // ��������
} else {
    $p1_c_uri = $p1_c_uri - $p1_sc_uri_sagaku;                  // ���ץ��������̣
    $p1_c_uri = number_format(($p1_c_uri / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�����'", $p1_ym);
if (getUniResult($query, $p1_l_uri) < 1) {
    $p1_l_uri = 0 - $p1_s_uri_temp;     // ��������
} else {
    $p1_l_uri = $p1_l_uri - $p1_s_uri_temp;
    if ($p1_ym == 201004) {
        $p1_l_uri = $p1_l_uri - 255240;
    }
    $p1_l_uri = number_format(($p1_l_uri / $tani), $keta);
}
    ///// ������
if ($yyyymm >= 200911) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��������'", $p2_ym);
    if (getUniResult($query, $p2_b_uri) < 1) {
        $p2_b_uri        = 0;           // ��������
        $p2_b_uri_sagaku = 0;
    } else {
        if ($p2_ym == 201004) {
            $p2_b_uri = $p2_b_uri + 255240;
        }
        $p2_b_uri_sagaku = 0;
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�������Ĵ����'", $p2_ym);
    if (getUniResult($query, $p2_b_uri_cho) < 1) {
        // �������� Ĵ����̵���Τǲ��⤷�ʤ�
    } else {
        $p2_b_uri        = $p2_b_uri + $p2_b_uri_cho;
        $p2_b_uri_sagaku = $p2_b_uri_cho;
    }
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��������'", $p2_ym);
    if (getUniResult($query, $p2_b_uri) < 1) {
        $p2_b_uri        = 0;           // ��������
        $p2_b_uri_sagaku = 0;
    } else {
        $p2_b_uri_sagaku = $p2_b_uri;
    }
}
if ( $yyyymm >= 200911) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�����'", $p2_ym);
    if (getUniResult($query, $p2_sc_uri) < 1) {
        $p2_sc_uri        = 0;          // ��������
        $p2_sc_uri_sagaku = 0;
        $p2_sc_uri_temp   = 0;
    } else {
        $p2_sc_uri_temp   = $p2_sc_uri;
        $p2_sc_uri_sagaku = $p2_sc_uri;
        $p2_sc_uri        = number_format(($p2_sc_uri / $tani), $keta);
    }
} else{
    $p2_sc_uri        = 0;              // ��������
    $p2_sc_uri_sagaku = 0;
    $p2_sc_uri_temp   = 0;
}
if ($yyyymm >= 200911) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�����'", $p2_ym);
    if (getUniResult($query, $p2_s_uri) < 1) {
        $p2_s_uri        = 0;           // ��������
        $p2_s_uri_sagaku = 0;
        $p2_s_uri_temp   = 0;
    } else {
        $p2_s_uri_temp = $p2_s_uri;
        if ($p2_ym == 200906) {
            $p2_s_uri  = $p2_s_uri - 3100900;
        } elseif ($p2_ym == 200905) {
            $p2_s_uri  = $p2_s_uri + 1550450;
        } elseif ($p2_ym == 200904) {
            $p2_s_uri  = $p2_s_uri + 1550450;
        }
        $p2_s_uri_sagaku = $p2_s_uri;
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='����Ĵ����'", $p2_ym);
    if (getUniResult($query, $p2_s_uri_cho) < 1) {
        // ��������
        $p2_s_uri = $p2_s_uri + $p2_sc_uri_sagaku;                  // ���ץ��������̣
        $p2_s_uri = number_format(($p2_s_uri / $tani), $keta);
    } else {
        $p2_s_uri_sagaku = $p2_s_uri_sagaku + $p2_s_uri_cho;
        $p2_s_uri_temp   = $p2_s_uri_sagaku;
        $p2_s_uri        = $p2_s_uri_sagaku + $p2_sc_uri_sagaku;    // ���ץ��������̣��temp�θ�ݥ�˥�����ޥ��ʥ����Ƥ��ޤ��١�
        $p2_s_uri        = number_format(($p2_s_uri / $tani), $keta);
    }
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�����'", $p2_ym);
    if (getUniResult($query, $p2_s_uri) < 1) {
        $p2_s_uri        = 0;           // ��������
        $p2_s_uri_sagaku = 0;
        $p2_s_uri_temp   = 0;
    } else {
        $p2_s_uri_temp = $p2_s_uri;
        if ($p2_ym == 200906) {
            $p2_s_uri  = $p2_s_uri - 3100900;
        } elseif ($p2_ym == 200905) {
            $p2_s_uri  = $p2_s_uri + 1550450;
        } elseif ($p2_ym == 200904) {
            $p2_s_uri  = $p2_s_uri + 1550450;
        }
        $p2_s_uri_sagaku = $p2_s_uri;
        $p2_s_uri        = number_format(($p2_s_uri / $tani), $keta);
    }
}

$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��������'", $p2_ym);
if (getUniResult($query, $p2_all_uri) < 1) {
    $p2_all_uri = 0;                    // ��������
} else {
    if ($p2_ym == 200906) {
        $p2_all_uri = $p2_all_uri + $p2_b_uri_sagaku - 3100900;
    } elseif ($p2_ym == 200905) {
        $p2_all_uri = $p2_all_uri + $p2_b_uri_sagaku + 1550450;
    } elseif ($p2_ym == 200904) {
        $p2_all_uri = $p2_all_uri + $p2_b_uri_sagaku + 1550450;
    } else {
        $p2_all_uri = $p2_all_uri + $p2_b_uri_sagaku;
    }
    $p2_all_uri = number_format(($p2_all_uri / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�����'", $p2_ym);
if (getUniResult($query, $p2_c_uri) < 1) {
    $p2_c_uri = 0;                      // ��������
} else {
    $p2_c_uri = $p2_c_uri - $p2_sc_uri_sagaku;                  // ���ץ��������̣
    $p2_c_uri = number_format(($p2_c_uri / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�����'", $p2_ym);
if (getUniResult($query, $p2_l_uri) < 1) {
    $p2_l_uri = 0 - $p2_s_uri_temp;     // ��������
} else {
    $p2_l_uri = $p2_l_uri - $p2_s_uri_temp;
    if ($p2_ym == 201004) {
        $p2_l_uri = $p2_l_uri - 255240;
    }
    $p2_l_uri = number_format(($p2_l_uri / $tani), $keta);
}
    ///// �����߷�
if($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��������'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_uri) < 1) {
        $rui_b_uri        = 0;          // ��������
        $rui_b_uri_sagaku = 0;
    } else {
        if ($yyyymm >= 201004 && $yyyymm <= 201103) {
            $rui_b_uri = $rui_b_uri + 255240;
        }
        $rui_b_uri_sagaku = 0;
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�������Ĵ����'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_uri_cho) < 1) {
        // �������� Ĵ����̵���Τǲ��⤷�ʤ�
        $rui_b_uri_sagaku = 0;
    } else {
        $rui_b_uri        = $rui_b_uri + $rui_b_uri_cho;
        $rui_b_uri_sagaku = $rui_b_uri_cho;
    }
} else if($yyyymm >= 200909 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��������'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_uri) < 1) {
        $rui_b_uri        = 0;          // ��������
        $rui_b_uri_sagaku = 0;
    } else {
        $rui_b_uri_sagaku = 0;
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�������Ĵ����'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_uri_cho) < 1) {
        // �������� Ĵ����̵���Τǲ��⤷�ʤ�
        $rui_b_uri_sagaku = 0;
    } else {
        $rui_b_uri        = $rui_b_uri + $rui_b_uri_cho;
        $rui_b_uri_sagaku = $rui_b_uri_cho + 25354300;      // 7��8��ʬ��Ĵ����9������줿ʬ���ᤷ
    }
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��������'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_uri) < 1) {
        $rui_b_uri        = 0;          // ��������
        $rui_b_uri_sagaku = 0;
    } else {
        $rui_b_uri_sagaku = $rui_b_uri;
    }
}
if ( $yyyymm >= 200911) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ�����'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_sc_uri) < 1) {
        $rui_sc_uri        = 0;         // ��������
        $rui_sc_uri_sagaku = 0;
        $rui_sc_uri_temp   = 0;
    } else {
        $rui_sc_uri_temp   = $rui_sc_uri;
        $rui_sc_uri_sagaku = $rui_sc_uri;
        $rui_sc_uri        = number_format(($rui_sc_uri / $tani), $keta);
    }
} else{
    $rui_sc_uri        = 0;             // ��������
    $rui_sc_uri_sagaku = 0;
    $rui_sc_uri_temp   = 0;
}
if ($yyyymm >= 200909) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�����'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_uri) < 1) {
        $rui_s_uri        = 0;          // ��������
        $rui_s_uri_sagaku = 0;
    } else {
        $rui_s_uri_sagaku = $rui_s_uri;
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='����Ĵ����'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_uri_cho) < 1) {
        // ��������
        $rui_s_uri = $rui_s_uri + $rui_sc_uri_sagaku;                   // ���ץ��������̣
        $rui_s_uri = number_format(($rui_s_uri / $tani), $keta);
    } else {
        $rui_s_uri_sagaku = $rui_s_uri_sagaku + $rui_s_uri_cho;
        $rui_s_uri        = $rui_s_uri_sagaku + $rui_sc_uri_sagaku;     // ���ץ��������̣��temp�θ�ݥ�˥�����ޥ��ʥ����Ƥ��ޤ��١�
        $rui_s_uri        = number_format(($rui_s_uri / $tani), $keta);
    }
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�����'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_uri) < 1) {
        $rui_s_uri        = 0;          // ��������
        $rui_s_uri_sagaku = 0;
    } else {
        if ($yyyymm == 200905) {
            $rui_s_uri = $rui_s_uri + 3100900;
        } elseif ($yyyymm == 200904) {
            $rui_s_uri = $rui_s_uri + 1550450;
        }
        $rui_s_uri_sagaku = $rui_s_uri;
        $rui_s_uri        = number_format(($rui_s_uri / $tani), $keta);
    }
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��������'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_uri) < 1) {
    $rui_all_uri = 0;                   // ��������
} else {
    if ($yyyymm == 200905) {
        $rui_all_uri = $rui_all_uri + 3100900;
    } elseif ($yyyymm == 200904) {
        $rui_all_uri = $rui_all_uri + 1550450;
    }
    $rui_all_uri = $rui_all_uri + $rui_b_uri_sagaku;
    $rui_all_uri = number_format(($rui_all_uri / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ�����'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_c_uri) < 1) {
    $rui_c_uri = 0;                     // ��������
} else {
    $rui_c_uri = $rui_c_uri - $rui_sc_uri_sagaku;                   // ���ץ��������̣
    $rui_c_uri = number_format(($rui_c_uri / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥�����'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_uri) < 1) {
    $rui_l_uri = 0 - $rui_s_uri_sagaku;     // ��������
} else {
    $rui_l_uri = $rui_l_uri - $rui_s_uri_sagaku;
    if ($yyyymm == 200905) {
        $rui_l_uri = $rui_l_uri + 3100900;
    } elseif ($yyyymm == 200904) {
        $rui_l_uri = $rui_l_uri + 1550450;
    }
    if ($yyyymm >= 201004 && $yyyymm <= 201103) {
        $rui_l_uri = $rui_l_uri - 255240;
    }
    $rui_l_uri = number_format(($rui_l_uri / $tani), $keta);
}

/********** ��������ų���ê���� **********/
    ///// ����
$p2_b_invent  = 0;
$p1_b_invent  = 0;
$b_invent     = 0;
$rui_b_invent = 0;
    ///// �������
$p2_s_invent  = 0;
$p1_s_invent  = 0;
$s_invent     = 0;
$rui_s_invent = 0;
    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���δ���ê����'", $yyyymm);
if (getUniResult($query, $all_invent) < 1) {
    $all_invent = 0;                        // ��������
} else {
    $all_invent = number_format(($all_invent / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ����ê����'", $yyyymm);
if (getUniResult($query, $c_invent) < 1) {
    $c_invent = 0;                          // ��������
} else {
    $c_invent = number_format(($c_invent / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�����ê����'", $yyyymm);
if (getUniResult($query, $l_invent) < 1) {
    $l_invent = 0;                          // ��������
} else {
    $l_invent = number_format(($l_invent / $tani), $keta);
}
    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���δ���ê����'", $p1_ym);
if (getUniResult($query, $p1_all_invent) < 1) {
    $p1_all_invent = 0;                     // ��������
} else {
    $p1_all_invent = number_format(($p1_all_invent / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ����ê����'", $p1_ym);
if (getUniResult($query, $p1_c_invent) < 1) {
    $p1_c_invent = 0;                       // ��������
} else {
    $p1_c_invent = number_format(($p1_c_invent / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�����ê����'", $p1_ym);
if (getUniResult($query, $p1_l_invent) < 1) {
    $p1_l_invent = 0;                       // ��������
} else {
    $p1_l_invent = number_format(($p1_l_invent / $tani), $keta);
}
    ///// ������
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���δ���ê����'", $p2_ym);
if (getUniResult($query, $p2_all_invent) < 1) {
    $p2_all_invent = 0;                     // ��������
} else {
    $p2_all_invent = number_format(($p2_all_invent / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ����ê����'", $p2_ym);
if (getUniResult($query, $p2_c_invent) < 1) {
    $p2_c_invent = 0;                       // ��������
} else {
    $p2_c_invent = number_format(($p2_c_invent / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�����ê����'", $p2_ym);
if (getUniResult($query, $p2_l_invent) < 1) {
    $p2_l_invent = 0;                       // ��������
} else {
    $p2_l_invent = number_format(($p2_l_invent / $tani), $keta);
}
    ///// �����߷�
    /////   ����ê������߷פ� ����ǯ��δ���ê����ˤʤ�
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���δ���ê����'", $str_ym);
if (getUniResult($query, $rui_all_invent) < 1) {
    $rui_all_invent = 0;                    // ��������
} else {
    $rui_all_invent = number_format(($rui_all_invent / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym=%d and note='���ץ����ê����'", $str_ym);
if (getUniResult($query, $rui_c_invent) < 1) {
    $rui_c_invent = 0;                      // ��������
} else {
    $rui_c_invent = number_format(($rui_c_invent / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym=%d and note='��˥�����ê����'", $str_ym);
if (getUniResult($query, $rui_l_invent) < 1) {
    $rui_l_invent = 0;                      // ��������
} else {
    $rui_l_invent = number_format(($rui_l_invent / $tani), $keta);
}

/********** ������(������) **********/
    ///// ����
$p2_b_metarial  = 0;
$p1_b_metarial  = 0;
$b_metarial     = 0;
$rui_b_metarial = 0;
    ///// ����
if ( $yyyymm >= 200911) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�������'", $yyyymm);
    if (getUniResult($query, $sc_metarial) < 1) {
        $sc_metarial        = 0;            // ��������
        $sc_metarial_sagaku = 0;
        $sc_metarial_temp   = 0;
    } else {
        $sc_metarial_temp   = $sc_metarial;
        $sc_metarial_sagaku = $sc_metarial;
        $sc_metarial        = number_format(($sc_metarial / $tani), $keta);
    }
} else{
    $sc_metarial        = 0;                // ��������
    $sc_metarial_sagaku = 0;
    $sc_metarial_temp   = 0;
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�������'", $yyyymm);
if (getUniResult($query, $s_metarial) < 1) {
    $s_metarial        = 0;                 // ��������
    $s_metarial_sagaku = 0;
} else {
    $s_metarial_sagaku = $s_metarial;
    $s_metarial        = $s_metarial + $sc_metarial_sagaku;             // ���ץ���������̣��sagaku�β� ��˥�����ޥ��ʥ����Ƥ��ޤ��١�
    $s_metarial        = number_format(($s_metarial / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���κ�����'", $yyyymm);
if (getUniResult($query, $all_metarial) < 1) {
    $all_metarial = 0;                      // ��������
} else {
    $all_metarial = number_format(($all_metarial / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ������'", $yyyymm);
if (getUniResult($query, $c_metarial) < 1) {
    $c_metarial = 0;                        // ��������
} else {
    $c_metarial = $c_metarial - $sc_metarial_sagaku;                    // ���ץ���������̣
    $c_metarial = number_format(($c_metarial / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�������'", $yyyymm);
if (getUniResult($query, $l_metarial) < 1) {
    $l_metarial = 0 - $s_metarial_sagaku;   // ��������
} else {
    $l_metarial = $l_metarial - $s_metarial_sagaku;
    $l_metarial = number_format(($l_metarial / $tani), $keta);
}
    ///// ����
if ( $yyyymm >= 200911) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�������'", $p1_ym);
    if (getUniResult($query, $p1_sc_metarial) < 1) {
        $p1_sc_metarial        = 0;         // ��������
        $p1_sc_metarial_sagaku = 0;
        $p1_sc_metarial_temp   = 0;
    } else {
        $p1_sc_metarial_temp   = $p1_sc_metarial;
        $p1_sc_metarial_sagaku = $p1_sc_metarial;
        $p1_sc_metarial        = number_format(($p1_sc_metarial / $tani), $keta);
    }
} else{
    $p1_sc_metarial        = 0;             // ��������
    $p1_sc_metarial_sagaku = 0;
    $p1_sc_metarial_temp   = 0;
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�������'", $p1_ym);
if (getUniResult($query, $p1_s_metarial) < 1) {
    $p1_s_metarial        = 0;              // ��������
    $p1_s_metarial_sagaku = 0;
} else {
    $p1_s_metarial_sagaku = $p1_s_metarial;
    $p1_s_metarial        = $p1_s_metarial + $p1_sc_metarial_sagaku;            // ���ץ���������̣��sagaku�β� ��˥�����ޥ��ʥ����Ƥ��ޤ��١�
    $p1_s_metarial        = number_format(($p1_s_metarial / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���κ�����'", $p1_ym);
if (getUniResult($query, $p1_all_metarial) < 1) {
    $p1_all_metarial = 0;                   // ��������
} else {
    $p1_all_metarial = number_format(($p1_all_metarial / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ������'", $p1_ym);
if (getUniResult($query, $p1_c_metarial) < 1) {
    $p1_c_metarial = 0;                     // ��������
} else {
    $p1_c_metarial = $p1_c_metarial - $p1_sc_metarial_sagaku;                   // ���ץ���������̣
    $p1_c_metarial = number_format(($p1_c_metarial / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�������'", $p1_ym);
if (getUniResult($query, $p1_l_metarial) < 1) {
    $p1_l_metarial = 0 - $p1_s_metarial_sagaku;     // ��������
} else {
    $p1_l_metarial = $p1_l_metarial - $p1_s_metarial_sagaku;
    $p1_l_metarial = number_format(($p1_l_metarial / $tani), $keta);
}
    ///// ������
if ( $yyyymm >= 200911) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�������'", $p2_ym);
    if (getUniResult($query, $p2_sc_metarial) < 1) {
        $p2_sc_metarial        = 0;         // ��������
        $p2_sc_metarial_sagaku = 0;
        $p2_sc_metarial_temp   = 0;
    } else {
        $p2_sc_metarial_temp   = $p2_sc_metarial;
        $p2_sc_metarial_sagaku = $p2_sc_metarial;
        $p2_sc_metarial        = number_format(($p2_sc_metarial / $tani), $keta);
    }
} else{
    $p2_sc_metarial        = 0;             // ��������
    $p2_sc_metarial_sagaku = 0;
    $p2_sc_metarial_temp   = 0;
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�������'", $p2_ym);
if (getUniResult($query, $p2_s_metarial) < 1) {
    $p2_s_metarial        = 0;              // ��������
    $p2_s_metarial_sagaku = 0;
} else {
    $p2_s_metarial_sagaku = $p2_s_metarial;
    $p2_s_metarial        = $p2_s_metarial + $p2_sc_metarial_sagaku;        // ���ץ���������̣��sagaku�β� ��˥�����ޥ��ʥ����Ƥ��ޤ��١�
    $p2_s_metarial        = number_format(($p2_s_metarial / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���κ�����'", $p2_ym);
if (getUniResult($query, $p2_all_metarial) < 1) {
    $p2_all_metarial = 0;                   // ��������
} else {
    $p2_all_metarial = number_format(($p2_all_metarial / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ������'", $p2_ym);
if (getUniResult($query, $p2_c_metarial) < 1) {
    $p2_c_metarial = 0;                     // ��������
} else {
    $p2_c_metarial = $p2_c_metarial - $p2_sc_metarial_sagaku;               // ���ץ���������̣
    $p2_c_metarial = number_format(($p2_c_metarial / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�������'", $p2_ym);
if (getUniResult($query, $p2_l_metarial) < 1) {
    $p2_l_metarial = 0 - $p2_s_metarial_sagaku;     // ��������
} else {
    $p2_l_metarial = $p2_l_metarial - $p2_s_metarial_sagaku;
    $p2_l_metarial = number_format(($p2_l_metarial / $tani), $keta);
}
    ///// �����߷�
if ( $yyyymm >= 200911) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ�������'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_sc_metarial) < 1) {
        $rui_sc_metarial        = 0;        // ��������
        $rui_sc_metarial_sagaku = 0;
        $rui_sc_metarial_temp   = 0;
    } else {
        $rui_sc_metarial_temp   = $rui_sc_metarial;
        $rui_sc_metarial_sagaku = $rui_sc_metarial;
        $rui_sc_metarial        = number_format(($rui_sc_metarial / $tani), $keta);
    }
} else{
    $rui_sc_metarial        = 0;            // ��������
    $rui_sc_metarial_sagaku = 0;
    $rui_sc_metarial_temp   = 0;
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�������'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_s_metarial) < 1) {
    $rui_s_metarial        = 0;             // ��������
    $rui_s_metarial_sagaku = 0;
} else {
    $rui_s_metarial_sagaku = $rui_s_metarial;
    $rui_s_metarial        = $rui_s_metarial + $rui_sc_metarial_sagaku;         // ���ץ���������̣��sagaku�β� ��˥�����ޥ��ʥ����Ƥ��ޤ��١�
    $rui_s_metarial        = number_format(($rui_s_metarial / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���κ�����'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_metarial) < 1) {
    $rui_all_metarial = 0;                  // ��������
} else {
    $rui_all_metarial = number_format(($rui_all_metarial / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ������'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_c_metarial) < 1) {
    $rui_c_metarial = 0;                    // ��������
} else {
    $rui_c_metarial = $rui_c_metarial - $rui_sc_metarial_sagaku;                // ���ץ���������̣
    $rui_c_metarial = number_format(($rui_c_metarial / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥�������'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_metarial) < 1) {
    $rui_l_metarial = 0 - $rui_s_metarial_sagaku;       // ��������
} else {
    $rui_l_metarial = $rui_l_metarial - $rui_s_metarial_sagaku;
    $rui_l_metarial = number_format(($rui_l_metarial / $tani), $keta);
}

/********** ϫ̳�� **********/
    ///// ����
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���Ϳ�����'", $yyyymm);
    if (getUniResult($query, $s_kyu_kei) < 1) {
        $s_kyu_kei = 0;                    // ��������
        $s_kyu_kin = 0;
    } else {
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���Ϳ����Ψ'", $yyyymm);
        if (getUniResult($query, $s_kyu_kin) < 1) {
            $s_kyu_kin = 0;
        }
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�ϫ̳��'", $yyyymm);
if (getUniResult($query, $s_roumu) < 1) {
    $s_roumu        = 0;                    // ��������
    $s_roumu_sagaku = 0;
} else {
    $s_roumu_sagaku = $s_roumu;
    if ($yyyymm == 200912) {
        $s_roumu = $s_roumu - 1409708;
    }
    if ($yyyymm >= 201001) {
        $s_roumu = $s_roumu - $s_kyu_kei + $s_kyu_kin;    // ������Ϳ���̣
        //$s_roumu = $s_roumu - 432323 + 129697;  // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    $s_roumu        = number_format(($s_roumu / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='����ϫ̳��'", $yyyymm);
if (getUniResult($query, $all_roumu) < 1) {
    $all_roumu = 0;                         // ��������
} else {
    $all_roumu = number_format(($all_roumu / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��Ϳ����Ψ'", $yyyymm);
    if (getUniResult($query, $c_kyu_kin) < 1) {
        $c_kyu_kin = 0;
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�ϫ̳��'", $yyyymm);
if (getUniResult($query, $c_roumu) < 1) {
    $c_roumu = 0;                           // ��������
} else {
    if ($yyyymm == 200912) {
        $c_roumu = $c_roumu + 1227429;
    }
    if ($yyyymm >= 201001) {
        $c_roumu = $c_roumu + $c_kyu_kin;   // ���ץ������Ϳ���̣
        //$c_roumu = $c_roumu + 151313;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    //$c_roumu = number_format(($c_roumu / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���Ϳ����Ψ'", $yyyymm);
    if (getUniResult($query, $l_kyu_kin) < 1) {
        $l_kyu_kin = 0;
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�ϫ̳��'", $yyyymm);
if (getUniResult($query, $l_roumu) < 1) {
    $l_roumu = 0 - $s_roumu_sagaku;         // ��������
} else {
    if ($yyyymm == 200912) {
        $l_roumu = $l_roumu + 182279;
    }
    if ($yyyymm >= 201001) {
        $l_roumu = $l_roumu + $l_kyu_kin;   // ��˥������Ϳ���̣
        //$l_roumu = $l_roumu + 151313;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    $l_roumu = $l_roumu - $s_roumu_sagaku;
    $l_roumu = number_format(($l_roumu / $tani), $keta);
}
// ����7��̤ʧ����Ϳʬ
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='����ϫ̳��'", $yyyymm);
if (getUniResult($query, $b_roumu_sagaku) < 1) {
    $b_roumu_sagaku = 0;                    // ��������
}
    ///// ���� ����
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=8101 and orign_id=580", $yyyymm);
if (getUniResult($query, $b_roumu) < 1) {
    $b_roumu  = 0 + $b_roumu_sagaku;        // ��������
    $b_urigen = $b_roumu;
    $b_sagaku = $b_roumu;                   // ���ץ麹�۷׻���
} else {
    $b_roumu  = $b_roumu + $b_roumu_sagaku;
    $b_urigen = $b_roumu;
    $c_roumu  = $c_roumu - $b_roumu;        // ���ץ�ϫ̳��ݾ���ϫ̳��
    $b_sagaku = $b_roumu;                   // ���ץ麹�۷׻���
    $c_roumu  = number_format(($c_roumu / $tani), $keta);
    $b_roumu  = number_format(($b_roumu / $tani), $keta);
}

    ///// ����
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���Ϳ�����'", $p1_ym);
    if (getUniResult($query, $p1_s_kyu_kei) < 1) {
        $p1_s_kyu_kei = 0;                    // ��������
        $p1_s_kyu_kin = 0;
    } else {
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���Ϳ����Ψ'", $p1_ym);
        if (getUniResult($query, $p1_s_kyu_kin) < 1) {
            $p1_s_kyu_kin = 0;
        }
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�ϫ̳��'", $p1_ym);
if (getUniResult($query, $p1_s_roumu) < 1) {
    $p1_s_roumu        = 0;                 // ��������
    $p1_s_roumu_sagaku = 0;
} else {
    $p1_s_roumu_sagaku = $p1_s_roumu;
    if ($p1_ym == 200912) {
        $p1_s_roumu = $p1_s_roumu - 1409708;
    }
    if ($p1_ym >= 201001) {
        $p1_s_roumu = $p1_s_roumu - $p1_s_kyu_kei + $p1_s_kyu_kin;    // ������Ϳ���̣
        //$p1_s_roumu = $p1_s_roumu - 432323 + 129697;    // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    $p1_s_roumu        = number_format(($p1_s_roumu / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='����ϫ̳��'", $p1_ym);
if (getUniResult($query, $p1_b_roumu_sagaku) < 1) {
    $p1_b_roumu_sagaku = 0;                 // ��������
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='����ϫ̳��'", $p1_ym);
if (getUniResult($query, $p1_all_roumu) < 1) {
    $p1_all_roumu = 0;                      // ��������
} else {
    $p1_all_roumu = number_format(($p1_all_roumu / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��Ϳ����Ψ'", $p1_ym);
    if (getUniResult($query, $p1_c_kyu_kin) < 1) {
        $p1_c_kyu_kin = 0;
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�ϫ̳��'", $p1_ym);
if (getUniResult($query, $p1_c_roumu) < 1) {
    $p1_c_roumu = 0;                        // ��������
} else {
    if ($p1_ym == 200912) {
        $p1_c_roumu = $p1_c_roumu + 1227429;
    }
    if ($p1_ym >= 201001) {
        $p1_c_roumu = $p1_c_roumu + $p1_c_kyu_kin;   // ���ץ������Ϳ���̣
        //$p1_c_roumu = $p1_c_roumu + 151313; // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    //$p1_c_roumu = number_format(($p1_c_roumu / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���Ϳ����Ψ'", $p1_ym);
    if (getUniResult($query, $p1_l_kyu_kin) < 1) {
        $p1_l_kyu_kin = 0;
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�ϫ̳��'", $p1_ym);
if (getUniResult($query, $p1_l_roumu) < 1) {
    $p1_l_roumu = 0 - $p1_s_roumu_sagaku;   // ��������
} else {
    if ($p1_ym == 200912) {
        $p1_l_roumu = $p1_l_roumu + 182279;
    }
    if ($p1_ym >= 201001) {
        $p1_l_roumu = $p1_l_roumu + $p1_l_kyu_kin;   // ��˥������Ϳ���̣
        //$p1_l_roumu = $p1_l_roumu + 151313; // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    $p1_l_roumu = $p1_l_roumu - $p1_s_roumu_sagaku;
    $p1_l_roumu = number_format(($p1_l_roumu / $tani), $keta);
}
    ///// ���� ����
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=8101 and orign_id=580", $p1_ym);
if (getUniResult($query, $p1_b_roumu) < 1) {
    $p1_b_roumu  = 0 + $p1_b_roumu_sagaku;      // ��������
    $p1_b_urigen = $p1_b_roumu;
    $p1_b_sagaku = $p1_b_roumu;                 // ���ץ麹�۷׻���
} else {
    $p1_b_roumu  = $p1_b_roumu + $p1_b_roumu_sagaku;
    $p1_b_urigen = $p1_b_roumu;
    $p1_c_roumu  = $p1_c_roumu - $p1_b_roumu;   // ���ץ�ϫ̳��ݾ���ϫ̳��
    $p1_b_sagaku = $p1_b_roumu;                 // ���ץ麹�۷׻���
    $p1_c_roumu  = number_format(($p1_c_roumu / $tani), $keta);
    $p1_b_roumu  = number_format(($p1_b_roumu / $tani), $keta);
}

    ///// ������
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���Ϳ�����'", $p2_ym);
    if (getUniResult($query, $p2_s_kyu_kei) < 1) {
        $p2_s_kyu_kei = 0;                    // ��������
        $p2_s_kyu_kin = 0;
    } else {
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���Ϳ����Ψ'", $p2_ym);
        if (getUniResult($query, $p2_s_kyu_kin) < 1) {
            $p2_s_kyu_kin = 0;
        }
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�ϫ̳��'", $p2_ym);
if (getUniResult($query, $p2_s_roumu) < 1) {
    $p2_s_roumu        = 0;                     // ��������
    $p2_s_roumu_sagaku = 0;
} else {
    $p2_s_roumu_sagaku = $p2_s_roumu;
    if ($p2_ym == 200912) {
        $p2_s_roumu = $p2_s_roumu - 1409708;
    }
    if ($p2_ym >= 201001) {
        $p2_s_roumu = $p2_s_roumu - $p2_s_kyu_kei + $p2_s_kyu_kin;    // ������Ϳ���̣
        //$p2_s_roumu = $p2_s_roumu - 432323 + 129697;    // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    $p2_s_roumu        = number_format(($p2_s_roumu / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='����ϫ̳��'", $p2_ym);
if (getUniResult($query, $p2_b_roumu_sagaku) < 1) {
    $p2_b_roumu_sagaku = 0;                     // ��������
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='����ϫ̳��'", $p2_ym);
if (getUniResult($query, $p2_all_roumu) < 1) {
    $p2_all_roumu = 0;                          // ��������
} else {
    $p2_all_roumu = number_format(($p2_all_roumu / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��Ϳ����Ψ'", $p2_ym);
    if (getUniResult($query, $p2_c_kyu_kin) < 1) {
        $p2_c_kyu_kin = 0;
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�ϫ̳��'", $p2_ym);
if (getUniResult($query, $p2_c_roumu) < 1) {
    $p2_c_roumu = 0;                            // ��������
} else {
    if ($p2_ym == 200912) {
        $p2_c_roumu = $p2_c_roumu + 1227429;
    }
    if ($p2_ym >= 201001) {
        $p2_c_roumu = $p2_c_roumu + $p2_c_kyu_kin;   // ���ץ������Ϳ���̣
        //$p2_c_roumu = $p2_c_roumu + 151313;    // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    //$p2_c_roumu = number_format(($p2_c_roumu / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���Ϳ����Ψ'", $p2_ym);
    if (getUniResult($query, $p2_l_kyu_kin) < 1) {
        $p2_l_kyu_kin = 0;
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�ϫ̳��'", $p2_ym);
if (getUniResult($query, $p2_l_roumu) < 1) {
    $p2_l_roumu = 0 - $p2_s_roumu_sagaku;       // ��������
} else {
    if ($p2_ym == 200912) {
        $p2_l_roumu = $p2_l_roumu + 182279;
    }
    if ($p2_ym >= 201001) {
        $p2_l_roumu = $p2_l_roumu + $p2_l_kyu_kin;   // ��˥������Ϳ���̣
        //$p2_l_roumu = $p2_l_roumu + 151313;     // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    $p2_l_roumu = $p2_l_roumu - $p2_s_roumu_sagaku;
    $p2_l_roumu = number_format(($p2_l_roumu / $tani), $keta);
}
    ///// ������ ����
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=8101 and orign_id=580", $p2_ym);
if (getUniResult($query, $p2_b_roumu) < 1) {
    $p2_b_roumu  = 0 + $p2_b_roumu_sagaku;      // ��������
    $p2_b_urigen = $p2_b_roumu;
    $p2_b_sagaku = $p2_b_roumu;                 // ���ץ麹�۷׻���
} else {
    $p2_b_roumu  = $p2_b_roumu + $p2_b_roumu_sagaku;
    $p2_b_urigen = $p2_b_roumu;
    $p2_c_roumu  = $p2_c_roumu - $p2_b_roumu;   // ���ץ�ϫ̳��ݾ���ϫ̳��
    $p2_b_sagaku = $p2_b_roumu;                 // ���ץ麹�۷׻���
    $p2_c_roumu  = number_format(($p2_c_roumu / $tani), $keta);
    $p2_b_roumu  = number_format(($p2_b_roumu / $tani), $keta);
}

    ///// �����߷�
if ($yyyymm >= 201001) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���Ϳ�����'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_kyu_kei) < 1) {
        $rui_s_kyu_kei = 0;                    // ��������
        $rui_s_kyu_kin = 0;
    } else {
        $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���Ϳ����Ψ'", $str_ym, $yyyymm);
        if (getUniResult($query, $rui_s_kyu_kin) < 1) {
            $rui_s_kyu_kin = 0;
        }
    }
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�ϫ̳��'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_s_roumu) < 1) {
    $rui_s_roumu        = 0;                    // ��������
    $rui_s_roumu_sagaku = 0;
} else {
    $rui_s_roumu_sagaku = $rui_s_roumu;
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_s_roumu = $rui_s_roumu - 1409708;
    }
    if ($yyyymm >= 201001) {
        $rui_s_roumu = $rui_s_roumu - $rui_s_kyu_kei + $rui_s_kyu_kin;    // ������Ϳ���̣
        //$rui_s_roumu = $rui_s_roumu - 432323 + 129697;  // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    $rui_s_roumu        = number_format(($rui_s_roumu / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='����ϫ̳��'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_b_roumu_sagaku) < 1) {
    $rui_b_roumu_sagaku = 0;                    // ��������
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='����ϫ̳��'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_roumu) < 1) {
    $rui_all_roumu = 0;                         // ��������
} else {
    $rui_all_roumu = number_format(($rui_all_roumu / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ��Ϳ����Ψ'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_kyu_kin) < 1) {
        $rui_c_kyu_kin = 0;
    }
}

$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ�ϫ̳��'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_c_roumu) < 1) {
    $rui_c_roumu = 0;                           // ��������
} else {
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_c_roumu = $rui_c_roumu + 1227429;
    }
    if ($yyyymm >= 201001) {
        $rui_c_roumu = $rui_c_roumu + $rui_c_kyu_kin;   // ���ץ������Ϳ���̣
        //$rui_c_roumu = $rui_c_roumu + 151313;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    //$rui_c_roumu = number_format(($rui_c_roumu / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥���Ϳ����Ψ'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_kyu_kin) < 1) {
        $rui_l_kyu_kin = 0;
    }
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥�ϫ̳��'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_roumu) < 1) {
    $rui_l_roumu = 0 - $rui_s_roumu_sagaku;     // ��������
} else {
    $rui_l_roumu = $rui_l_roumu - $rui_s_roumu_sagaku;
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_l_roumu = $rui_l_roumu + 182279;
    }
    if ($yyyymm >= 201001) {
        $rui_l_roumu = $rui_l_roumu + $rui_l_kyu_kin;   // ��˥������Ϳ���̣
        //$rui_l_roumu = $rui_l_roumu + 151313;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    $rui_l_roumu = number_format(($rui_l_roumu / $tani), $keta);
}
    ///// �����߷� ����
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym>=%d and pl_bs_ym<=%d and actcod>=8101 and orign_id=580", $str_ym, $yyyymm);
if (getUniResult($query, $rui_b_roumu) < 1) {
    $rui_b_roumu  = 0 + $rui_b_roumu_sagaku;        // ��������
    $rui_b_urigen = $rui_b_roumu;
} else {
    // ����7��̤ʧ����;ʬ�ɲ� �ƥ�����
    $rui_b_roumu  = $rui_b_roumu + $rui_b_roumu_sagaku;
    $rui_b_urigen = $rui_b_roumu;
    $rui_c_roumu  = $rui_c_roumu - $rui_b_roumu;    // ���ץ�ϫ̳��ݾ���ϫ̳��
    $rui_b_sagaku = $rui_b_roumu;                   // ���ץ麹�۷׻���
    $rui_c_roumu  = number_format(($rui_c_roumu / $tani), $keta);
    $rui_b_roumu  = number_format(($rui_b_roumu / $tani), $keta);
}

/********** ����(��¤����) **********/
    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���¤����'", $yyyymm);
if (getUniResult($query, $s_expense) < 1) {
    $s_expense        = 0;                          // ��������
    $s_expense_sagaku = 0;
} else {
    $s_expense_sagaku = $s_expense;
    $s_expense        = number_format(($s_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='������¤����'", $yyyymm);
if (getUniResult($query, $b_expense_sagaku) < 1) {
    $b_expense_sagaku = 0;                          // ��������
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='������¤����'", $yyyymm);
if (getUniResult($query, $all_expense) < 1) {
    $all_expense = 0;                               // ��������
} else {
    $all_expense = number_format(($all_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ���¤����'", $yyyymm);
if (getUniResult($query, $c_expense) < 1) {
    $c_expense = 0;                                 // ��������
} else {
    //$c_expense = number_format(($c_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���¤����'", $yyyymm);
if (getUniResult($query, $l_expense) < 1) {
    $l_expense = 0 - $s_expense_sagaku;             // ��������
} else {
    $l_expense = $l_expense - $s_expense_sagaku;
    $l_expense = number_format(($l_expense / $tani), $keta);
}
    ///// ���� ����
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=7501 and actcod<=8000 and orign_id=580", $yyyymm);
if (getUniResult($query, $b_expense) < 1) {
    $b_expense = 0 + $b_roumu_sagaku;               // ��������
    $b_urigen  = $b_urigen + $b_expense;
    $b_sagaku  = $b_sagaku + $b_expense;            // ���ץ麹�۷׻���
} else {
    $b_expense = $b_expense + $b_expense_sagaku;
    $b_urigen  = $b_urigen + $b_expense;
    $c_expense = $c_expense - $b_expense;           // ���ץ���¤����ݾ�����¤����
    $b_sagaku  = $b_sagaku + $b_expense;            // ���ץ麹�۷׻���
    $c_expense = number_format(($c_expense / $tani), $keta);
    $b_expense = number_format(($b_expense / $tani), $keta);
}

    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���¤����'", $p1_ym);
if (getUniResult($query, $p1_s_expense) < 1) {
    $p1_s_expense        = 0;                       // ��������
    $p1_s_expense_sagaku = 0;
} else {
    $p1_s_expense_sagaku = $p1_s_expense;
    $p1_s_expense        = number_format(($p1_s_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='������¤����'", $p1_ym);
if (getUniResult($query, $p1_b_expense_sagaku) < 1) {
    $p1_b_expense_sagaku = 0;                       // ��������
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='������¤����'", $p1_ym);
if (getUniResult($query, $p1_all_expense) < 1) {
    $p1_all_expense = 0;                            // ��������
} else {
    $p1_all_expense = number_format(($p1_all_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ���¤����'", $p1_ym);
if (getUniResult($query, $p1_c_expense) < 1) {
    $p1_c_expense = 0;                              // ��������
} else {
    //$p1_c_expense = number_format(($p1_c_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���¤����'", $p1_ym);
if (getUniResult($query, $p1_l_expense) < 1) {
    $p1_l_expense = 0 - $p1_s_expense_sagaku;       // ��������
} else {
    $p1_l_expense = $p1_l_expense - $p1_s_expense_sagaku;
    $p1_l_expense = number_format(($p1_l_expense / $tani), $keta);
}
    ///// ���� ����
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=7501 and actcod<=8000 and orign_id=580", $p1_ym);
if (getUniResult($query, $p1_b_expense) < 1) {
    $p1_b_expense = 0 + $p1_b_expense_sagaku;       // ��������
    $p1_b_urigen  = $p1_b_urigen + $p1_b_expense;
    $p1_b_sagaku  = $p1_b_sagaku + $p1_b_expense;   // ���ץ麹�۷׻���
} else {
    $p1_b_expense = $p1_b_expense + $p1_b_expense_sagaku;
    $p1_b_urigen  = $p1_b_urigen + $p1_b_expense;
    $p1_c_expense = $p1_c_expense - $p1_b_expense;  // ���ץ���¤����ݾ�����¤����
    $p1_b_sagaku  = $p1_b_sagaku + $p1_b_expense;   // ���ץ麹�۷׻���
    $p1_c_expense = number_format(($p1_c_expense / $tani), $keta);
    $p1_b_expense = number_format(($p1_b_expense / $tani), $keta);
}

    ///// ������
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���¤����'", $p2_ym);
if (getUniResult($query, $p2_s_expense) < 1) {
    $p2_s_expense        = 0;                       // ��������
    $p2_s_expense_sagaku = 0;
} else {
    $p2_s_expense_sagaku = $p2_s_expense;
    $p2_s_expense        = number_format(($p2_s_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='������¤����'", $p2_ym);
if (getUniResult($query, $p2_b_expense_sagaku) < 1) {
    $p2_b_expense_sagaku = 0;                       // ��������
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='������¤����'", $p2_ym);
if (getUniResult($query, $p2_all_expense) < 1) {
    $p2_all_expense = 0;                            // ��������
} else {
    $p2_all_expense = number_format(($p2_all_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ���¤����'", $p2_ym);
if (getUniResult($query, $p2_c_expense) < 1) {
    $p2_c_expense = 0;                              // ��������
} else {
    //$p2_c_expense = number_format(($p2_c_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���¤����'", $p2_ym);
if (getUniResult($query, $p2_l_expense) < 1) {
    $p2_l_expense = 0 - $p2_s_expense_sagaku;       // ��������
} else {
    $p2_l_expense = $p2_l_expense - $p2_s_expense_sagaku;
    $p2_l_expense = number_format(($p2_l_expense / $tani), $keta);
}
    ///// ������ ����
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=7501 and actcod<=8000 and orign_id=580", $p2_ym);
if (getUniResult($query, $p2_b_expense) < 1) {
    $p2_b_expense = 0 + $p2_b_expense_sagaku;       // ��������
    $p2_b_urigen  = $p2_b_urigen + $p2_b_expense;
    $p2_b_sagaku  = $p2_b_sagaku + $p2_b_expense;   // ���ץ麹�۷׻���
} else {
    $p2_b_expense = $p2_b_expense + $p2_b_expense_sagaku;
    $p2_b_urigen  = $p2_b_urigen + $p2_b_expense;
    $p2_c_expense = $p2_c_expense - $p2_b_expense;  // ���ץ���¤����ݾ�����¤����
    $p2_b_sagaku  = $p2_b_sagaku + $p2_b_expense;   // ���ץ麹�۷׻���
    $p2_c_expense = number_format(($p2_c_expense / $tani), $keta);
    $p2_b_expense = number_format(($p2_b_expense / $tani), $keta);
}

    ///// �����߷�
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���¤����'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_s_expense) < 1) {
    $rui_s_expense        = 0;                      // ��������
    $rui_s_expense_sagaku = 0;
} else {
    $rui_s_expense_sagaku = $rui_s_expense;
    $rui_s_expense        = number_format(($rui_s_expense / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='������¤����'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_b_expense_sagaku) < 1) {
    $rui_b_expense_sagaku = 0;                      // ��������
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='������¤����'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_expense) < 1) {
    $rui_all_expense = 0;                           // ��������
} else {
    $rui_all_expense = number_format(($rui_all_expense / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ���¤����'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_c_expense) < 1) {
    $rui_c_expense = 0;                             // ��������
} else {
    //$rui_c_expense = number_format(($rui_c_expense / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥���¤����'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_expense) < 1) {
    $rui_l_expense = 0 - $rui_s_expense_sagaku;     // ��������
} else {
    $rui_l_expense = $rui_l_expense - $rui_s_expense_sagaku;
    $rui_l_expense = number_format(($rui_l_expense / $tani), $keta);
}
    ///// �����߷� ����
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym>=%d and pl_bs_ym<=%d and actcod>=7501 and actcod<=8000 and orign_id=580", $str_ym, $yyyymm);
if (getUniResult($query, $rui_b_expense) < 1) {
    $rui_b_expense = 0 + $rui_b_expense_sagaku;     // ��������
    $rui_b_urigen  = $rui_b_urigen + $rui_b_expense;
    $rui_b_sagaku  = $rui_b_sagaku + $rui_b_expense;    // ���ץ麹�۷׻���
} else {
    $rui_b_expense = $rui_b_expense + $rui_b_expense_sagaku;
    $rui_b_urigen  = $rui_b_urigen + $rui_b_expense;
    $rui_c_expense = $rui_c_expense - $rui_b_expense;   // ���ץ���¤����ݾ�����¤����
    $rui_b_sagaku  = $rui_b_sagaku + $rui_b_expense;    // ���ץ麹�۷׻���
    $rui_c_expense = number_format(($rui_c_expense / $tani), $keta);
    $rui_b_expense = number_format(($rui_b_expense / $tani), $keta);
}

/********** ���������ų���ê���� **********/
    ///// ����
$p2_b_endinv = 0;
$p1_b_endinv = 0;
$b_endinv    = 0;
    ///// �������
$p2_s_endinv = 0;
$p1_s_endinv = 0;
$s_endinv    = 0;
    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���δ���ê����'", $yyyymm);
if (getUniResult($query, $all_endinv) < 1) {
    $all_endinv = 0;                                // ��������
} else {
    $all_endinv = ($all_endinv * (-1));             // ���ȿž
    $all_endinv = number_format(($all_endinv / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ����ê����'", $yyyymm);
if (getUniResult($query, $c_endinv) < 1) {
    $c_endinv = 0;                                  // ��������
} else {
    $c_endinv = ($c_endinv * (-1));                 // ���ȿž
    $c_endinv = number_format(($c_endinv / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�����ê����'", $yyyymm);
if (getUniResult($query, $l_endinv) < 1) {
    $l_endinv = 0;                                  // ��������
} else {
    $l_endinv = ($l_endinv * (-1));                 // ���ȿž
    $l_endinv = number_format(($l_endinv / $tani), $keta);
}
    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���δ���ê����'", $p1_ym);
if (getUniResult($query, $p1_all_endinv) < 1) {
    $p1_all_endinv = 0;                             // ��������
} else {
    $p1_all_endinv = ($p1_all_endinv * (-1));       // ���ȿž
    $p1_all_endinv = number_format(($p1_all_endinv / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ����ê����'", $p1_ym);
if (getUniResult($query, $p1_c_endinv) < 1) {
    $p1_c_endinv = 0;                               // ��������
} else {
    $p1_c_endinv = ($p1_c_endinv * (-1));           // ���ȿž
    $p1_c_endinv = number_format(($p1_c_endinv / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�����ê����'", $p1_ym);
if (getUniResult($query, $p1_l_endinv) < 1) {
    $p1_l_endinv = 0;                               // ��������
} else {
    $p1_l_endinv = ($p1_l_endinv * (-1));           // ���ȿž
    $p1_l_endinv = number_format(($p1_l_endinv / $tani), $keta);
}
    ///// ������
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���δ���ê����'", $p2_ym);
if (getUniResult($query, $p2_all_endinv) < 1) {
    $p2_all_endinv = 0;                             // ��������
} else {
    $p2_all_endinv = ($p2_all_endinv * (-1));       // ���ȿž
    $p2_all_endinv = number_format(($p2_all_endinv / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ����ê����'", $p2_ym);
if (getUniResult($query, $p2_c_endinv) < 1) {
    $p2_c_endinv = 0;                               // ��������
} else {
    $p2_c_endinv = ($p2_c_endinv * (-1));           // ���ȿž
    $p2_c_endinv = number_format(($p2_c_endinv / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�����ê����'", $p2_ym);
if (getUniResult($query, $p2_l_endinv) < 1) {
    $p2_l_endinv = 0;                               // ��������
} else {
    $p2_l_endinv = ($p2_l_endinv * (-1));           // ���ȿž
    $p2_l_endinv = number_format(($p2_l_endinv / $tani), $keta);
}
    ///// �����߷�
    ///// ����ê������߷פ������Ʊ��

/********** ��帶�� **********/
    ///// ����
    ///// �������
    $s_urigen        = $s_invent + $s_metarial_sagaku + $s_roumu_sagaku + $s_expense_sagaku + $s_endinv;
    $s_urigen_sagaku = $s_urigen;
    $s_urigen        = $s_urigen + $sc_metarial_sagaku;         // ���ץ���������̣��sagaku�β� ��˥�����ޥ��ʥ����Ƥ��ޤ��١�
    if ($yyyymm == 200912) {
        $s_urigen = $s_urigen - 1409708;
    }
    if ($yyyymm >= 201001) {
        $s_urigen = $s_urigen - $s_kyu_kei + $s_kyu_kin;    // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        //$s_urigen = $s_urigen - 432323 + 129697;    // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    $s_urigen        = number_format(($s_urigen / $tani), $keta);
    ///// CL
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='������帶��'", $yyyymm);
if (getUniResult($query, $all_urigen) < 1) {
    $all_urigen = 0;                                // ��������
} else {
    $all_urigen = number_format(($all_urigen / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ���帶��'", $yyyymm);
if (getUniResult($query, $c_urigen) < 1) {
    $c_urigen = 0;                                  // ��������
} else {
    $c_urigen = $c_urigen - $b_urigen - $sc_metarial_sagaku;    // ���ץ���������̣
    if ($yyyymm == 200912) {
        $c_urigen = $c_urigen + 1227429;
    }
    if ($yyyymm >= 201001) {
        $c_urigen = $c_urigen + $c_kyu_kin;     // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        //$c_urigen = $c_urigen + 151313;     // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    $c_urigen = number_format(($c_urigen / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���帶��'", $yyyymm);
if (getUniResult($query, $l_urigen) < 1) {
    $l_urigen = 0 - $s_urigen_sagaku;               // ��������
} else {
    $l_urigen = $l_urigen - $s_urigen_sagaku;
    if ($yyyymm == 200912) {
        $l_urigen = $l_urigen + 182279;
    }
    if ($yyyymm >= 201001) {
        $l_urigen = $l_urigen + $l_kyu_kin;     // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        //$l_urigen = $l_urigen + 151313;     // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    $l_urigen = number_format(($l_urigen / $tani), $keta);
}

    ///// ����
    ///// �������
    $p1_s_urigen        = $p1_s_invent + $p1_s_metarial_sagaku + $p1_s_roumu_sagaku + $p1_s_expense_sagaku + $p1_s_endinv;
    $p1_s_urigen_sagaku = $p1_s_urigen;
    $p1_s_urigen        = $p1_s_urigen + $p1_sc_metarial_sagaku;    // ���ץ���������̣��sagaku�β� ��˥�����ޥ��ʥ����Ƥ��ޤ��١�
    if ($p1_ym == 200912) {
        $p1_s_urigen = $p1_s_urigen - 1409708;
    }
    if ($p1_ym >= 201001) {
        $p1_s_urigen = $p1_s_urigen - $p1_s_kyu_kei + $p1_s_kyu_kin;  // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        //$p1_s_urigen = $p1_s_urigen - 432323 + 129697;  // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    $p1_s_urigen        = number_format(($p1_s_urigen / $tani), $keta);
    ///// CL
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='������帶��'", $p1_ym);
if (getUniResult($query, $p1_all_urigen) < 1) {
    $p1_all_urigen = 0;                             // ��������
} else {
    $p1_all_urigen = number_format(($p1_all_urigen / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ���帶��'", $p1_ym);
if (getUniResult($query, $p1_c_urigen) < 1) {
    $p1_c_urigen = 0;                               // ��������
} else {
    $p1_c_urigen = $p1_c_urigen - $p1_b_urigen - $p1_sc_metarial_sagaku;    // ���ץ���������̣
    if ($p1_ym == 200912) {
        $p1_c_urigen = $p1_c_urigen + 1227429;
    }
    if ($p1_ym >= 201001) {
        $p1_c_urigen = $p1_c_urigen + $p1_c_kyu_kin;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        //$p1_c_urigen = $p1_c_urigen + 151313;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    $p1_c_urigen = number_format(($p1_c_urigen / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���帶��'", $p1_ym);
if (getUniResult($query, $p1_l_urigen) < 1) {
    $p1_l_urigen = 0 - $p1_s_urigen_sagaku;         // ��������
} else {
    $p1_l_urigen = $p1_l_urigen - $p1_s_urigen_sagaku;
    if ($p1_ym == 200912) {
        $p1_l_urigen = $p1_l_urigen + 182279;
    }
    if ($p1_ym >= 201001) {
        $p1_l_urigen = $p1_l_urigen + $p1_l_kyu_kin;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        //$p1_l_urigen = $p1_l_urigen + 151313;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    $p1_l_urigen = number_format(($p1_l_urigen / $tani), $keta);
}

    ///// ������
    ///// �������
    $p2_s_urigen        = $p2_s_invent + $p2_s_metarial_sagaku + $p2_s_roumu_sagaku + $p2_s_expense_sagaku + $p2_s_endinv;
    $p2_s_urigen_sagaku = $p2_s_urigen;
    $p2_s_urigen        = $p2_s_urigen + $p2_sc_metarial_sagaku;    // ���ץ���������̣��sagaku�β� ��˥�����ޥ��ʥ����Ƥ��ޤ��١�
    if ($p2_ym == 200912) {
        $p2_s_urigen = $p2_s_urigen - 1409708;
    }
    if ($p2_ym >= 201001) {
        $p2_s_urigen = $p2_s_urigen - $p2_s_kyu_kei + $p2_s_kyu_kin;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        //$p2_s_urigen = $p2_s_urigen - 432323 + 129697;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    $p2_s_urigen        = number_format(($p2_s_urigen / $tani), $keta);
    ///// CL
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='������帶��'", $p2_ym);
if (getUniResult($query, $p2_all_urigen) < 1) {
    $p2_all_urigen = 0;                             // ��������
} else {
    $p2_all_urigen = number_format(($p2_all_urigen / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ���帶��'", $p2_ym);
if (getUniResult($query, $p2_c_urigen) < 1) {
    $p2_c_urigen = 0;                               // ��������
} else {
    $p2_c_urigen = $p2_c_urigen - $p2_b_urigen - $p2_sc_metarial_sagaku;    // ���ץ���������̣
    if ($p2_ym == 200912) {
        $p2_c_urigen = $p2_c_urigen + 1227429;
    }
    if ($p2_ym >= 201001) {
        $p2_c_urigen = $p2_c_urigen + $p2_c_kyu_kin;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        //$p2_c_urigen = $p2_c_urigen + 151313;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    $p2_c_urigen = number_format(($p2_c_urigen / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���帶��'", $p2_ym);
if (getUniResult($query, $p2_l_urigen) < 1) {
    $p2_l_urigen = 0 - $p2_s_urigen_sagaku;         // ��������
} else {
    $p2_l_urigen = $p2_l_urigen - $p2_s_urigen_sagaku;
    if ($p2_ym == 200912) {
        $p2_l_urigen = $p2_l_urigen + 182279;
    }
    if ($p2_ym >= 201001) {
        $p2_l_urigen = $p2_l_urigen + $p2_l_kyu_kin;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        //$p2_l_urigen = $p2_l_urigen + 151313;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    $p2_l_urigen = number_format(($p2_l_urigen / $tani), $keta);
}

    ///// �����߷�
    ///// �������
    $rui_s_urigen        = $rui_s_invent + $rui_s_metarial_sagaku + $rui_s_roumu_sagaku + $rui_s_expense_sagaku + $s_endinv;
    $rui_s_urigen_sagaku = $rui_s_urigen;
    $rui_s_urigen        = $rui_s_urigen + $rui_sc_metarial_sagaku; // ���ץ���������̣��sagaku�β� ��˥�����ޥ��ʥ����Ƥ��ޤ��١�
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_s_urigen = $rui_s_urigen - 1409708;
    }
    if ($yyyymm >= 201001) {
        $rui_s_urigen = $rui_s_urigen - $rui_s_kyu_kei + $rui_s_kyu_kin;    // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        //$rui_s_urigen = $rui_s_urigen - 432323 + 129697;    // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    $rui_s_urigen        = number_format(($rui_s_urigen / $tani), $keta);
    ///// CL
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='������帶��'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_urigen) < 1) {
    $rui_all_urigen = 0;                            // ��������
} else {
    $rui_all_urigen = number_format(($rui_all_urigen / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ���帶��'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_c_urigen) < 1) {
    $rui_c_urigen = 0;                              // ��������
} else {
    $rui_c_urigen = $rui_c_urigen - $rui_b_urigen - $rui_sc_metarial_sagaku;    // ���ץ���������̣
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_c_urigen = $rui_c_urigen + 1227429;
    }
    if ($yyyymm >= 201001) {
        $rui_c_urigen = $rui_c_urigen + $rui_c_kyu_kin;     // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    $rui_c_urigen = number_format(($rui_c_urigen / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥���帶��'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_urigen) < 1) {
    $rui_l_urigen = 0 - $rui_s_urigen_sagaku;       // ��������
} else {
    $rui_l_urigen = $rui_l_urigen - $rui_s_urigen_sagaku;
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_l_urigen = $rui_l_urigen + 182279;
    }
    if ($yyyymm >= 201001) {
        $rui_l_urigen = $rui_l_urigen + $rui_l_kyu_kin;     // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    $rui_l_urigen = number_format(($rui_l_urigen / $tani), $keta);
}

/********** ��������� **********/
    ///// ����
$p2_b_gross_profit  = $p2_b_uri - $p2_b_urigen;
$p2_b_uri           = number_format(($p2_b_uri / $tani), $keta);
$p2_b_invent        = number_format(($p2_b_invent / $tani), $keta);
$p2_b_metarial      = number_format(($p2_b_metarial / $tani), $keta);
$p2_b_endinv        = number_format(($p2_b_endinv / $tani), $keta);

$p1_b_gross_profit  = $p1_b_uri - $p1_b_urigen;
$p1_b_uri           = number_format(($p1_b_uri / $tani), $keta);
$p1_b_invent        = number_format(($p1_b_invent / $tani), $keta);
$p1_b_metarial      = number_format(($p1_b_metarial / $tani), $keta);
$p1_b_endinv        = number_format(($p1_b_endinv / $tani), $keta);

$b_gross_profit     = $b_uri - $b_urigen;
$b_uri              = number_format(($b_uri / $tani), $keta);
$b_invent           = number_format(($b_invent / $tani), $keta);
$b_metarial         = number_format(($b_metarial / $tani), $keta);
$b_endinv           = number_format(($b_endinv / $tani), $keta);

$rui_b_gross_profit = $rui_b_uri - $rui_b_urigen;
$rui_b_uri          = number_format(($rui_b_uri / $tani), $keta);
$rui_b_invent       = number_format(($rui_b_invent / $tani), $keta);
$rui_b_metarial     = number_format(($rui_b_metarial / $tani), $keta);
    
    ///// �������
$p2_s_gross_profit         = $p2_s_uri_sagaku - $p2_s_urigen_sagaku;
$p2_s_gross_profit_sagaku  = $p2_s_gross_profit;
$p2_s_gross_profit         = $p2_s_gross_profit + $p2_sc_uri_sagaku - $p2_sc_metarial_sagaku;    // ���ץ��������̣��sagaku�θ�ݥ�˥�����ޥ��ʥ����Ƥ��ޤ��١�
if ($p2_ym == 200912) {
    $p2_s_gross_profit = $p2_s_gross_profit + 1409708;
}
if ($p2_ym >= 201001) {
    $p2_s_gross_profit = $p2_s_gross_profit + $p2_s_kyu_kei - $p2_s_kyu_kin;  // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    //$p2_s_gross_profit = $p2_s_gross_profit + 432323 - 129697;  // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
}
$p2_s_gross_profit         = number_format(($p2_s_gross_profit / $tani), $keta);

$p1_s_gross_profit         = $p1_s_uri_sagaku - $p1_s_urigen_sagaku;
$p1_s_gross_profit_sagaku  = $p1_s_gross_profit;
$p1_s_gross_profit         = $p1_s_gross_profit + $p1_sc_uri_sagaku - $p1_sc_metarial_sagaku;    // ���ץ��������̣��sagaku�θ�ݥ�˥�����ޥ��ʥ����Ƥ��ޤ��١�
if ($p1_ym == 200912) {
    $p1_s_gross_profit = $p1_s_gross_profit + 1409708;
}
if ($p1_ym >= 201001) {
    $p1_s_gross_profit = $p1_s_gross_profit + $p1_s_kyu_kei - $p1_s_kyu_kin;  // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    //$p1_s_gross_profit = $p1_s_gross_profit + 432323 - 129697;  // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
}
$p1_s_gross_profit         = number_format(($p1_s_gross_profit / $tani), $keta);

$s_gross_profit            = $s_uri_sagaku - $s_urigen_sagaku;
$s_gross_profit_sagaku     = $s_gross_profit;
$s_gross_profit            = $s_gross_profit + $sc_uri_sagaku - $sc_metarial_sagaku;             // ���ץ��������̣��sagaku�θ�ݥ�˥�����ޥ��ʥ����Ƥ��ޤ��١�
if ($yyyymm == 200912) {
    $s_gross_profit = $s_gross_profit + 1409708;
}
if ($yyyymm >= 201001) {
    $s_gross_profit = $s_gross_profit + $s_kyu_kei - $s_kyu_kin;  // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    //$s_gross_profit = $s_gross_profit + 432323 - 129697;  // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
}
$s_gross_profit            = number_format(($s_gross_profit / $tani), $keta);

$rui_s_gross_profit        = $rui_s_uri_sagaku - $rui_s_urigen_sagaku;
$rui_s_gross_profit_sagaku = $rui_s_gross_profit;
$rui_s_gross_profit        = $rui_s_gross_profit + $rui_sc_uri_sagaku - $rui_sc_metarial_sagaku; // ���ץ��������̣��sagaku�θ�ݥ�˥�����ޥ��ʥ����Ƥ��ޤ��١�
if ($yyyymm >= 200912 && $yyyymm <= 201003) {
    $rui_s_gross_profit = $rui_s_gross_profit + 1409708;
}
if ($yyyymm >= 201001) {
    $rui_s_gross_profit = $rui_s_gross_profit + $rui_s_kyu_kei - $rui_s_kyu_kin;  // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    //$rui_s_gross_profit = $rui_s_gross_profit + 432323 - 129697;  // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
}
$rui_s_gross_profit        = number_format(($rui_s_gross_profit / $tani), $keta);

    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='����������'", $yyyymm);
if (getUniResult($query, $all_gross_profit) < 1) {
    $all_gross_profit = 0;                      // ��������
} else {
    if ($yyyymm == 200906) {
        $all_gross_profit = $all_gross_profit + $b_uri_sagaku - 3100900;
    } elseif ($yyyymm == 200905) {
        $all_gross_profit = $all_gross_profit + $b_uri_sagaku + 1550450;
    } elseif ($yyyymm == 200904) {
        $all_gross_profit = $all_gross_profit + $b_uri_sagaku + 1550450;
    } else {
        $all_gross_profit = $all_gross_profit + $b_uri_sagaku;
    }
    $all_gross_profit = number_format(($all_gross_profit / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�������'", $yyyymm);
if (getUniResult($query, $c_gross_profit) < 1) {
    $c_gross_profit = 0;                        // ��������
} else {
    $c_gross_profit = $c_gross_profit + $b_urigen - $sc_uri_sagaku + $sc_metarial_sagaku;   // ���ץ��������̣
    if ($yyyymm == 200912) {
        $c_gross_profit = $c_gross_profit - 1227429;
    }
    if ($yyyymm >= 201001) {
        $c_gross_profit = $c_gross_profit - $c_kyu_kin;     // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        //$c_gross_profit = $c_gross_profit - 151313;     // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    $b_urigen       = number_format(($b_urigen / $tani), $keta);
    $c_gross_profit = number_format(($c_gross_profit / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�������'", $yyyymm);
if (getUniResult($query, $l_gross_profit) < 1) {
    if ($yyyymm == 200906) {
        $l_gross_profit = 0 - $s_gross_profit_sagaku - 3100900;     // ��������
    } elseif ($yyyymm == 200905) {
        $l_gross_profit = 0 - $s_gross_profit_sagaku - 1550450;     // ��������
    } elseif ($yyyymm == 200904) {
        $l_gross_profit = 0 - $s_gross_profit_sagaku - 1550450;     // ��������
    } else {
        $l_gross_profit = 0 - $s_gross_profit_sagaku;               // ��������
    }
} else {
    if ($yyyymm == 200906) {
        $l_gross_profit = $l_gross_profit - $s_gross_profit_sagaku - 3100900;
    } elseif ($yyyymm == 200905) {
        $l_gross_profit = $l_gross_profit - $s_gross_profit_sagaku + 1550450;
    } elseif ($yyyymm == 200904) {
        $l_gross_profit = $l_gross_profit - $s_gross_profit_sagaku + 1550450;
    } else {
        $l_gross_profit = $l_gross_profit - $s_gross_profit_sagaku;
    }
    if ($yyyymm == 200912) {
        $l_gross_profit = $l_gross_profit - 182279;
    }
    if ($yyyymm >= 201001) {
        $l_gross_profit = $l_gross_profit - $l_kyu_kin;     // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        //$l_gross_profit = $l_gross_profit - 151313;     // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    if ($yyyymm == 201004) {
        $l_gross_profit = $l_gross_profit - 255240;
    }
    $l_gross_profit = number_format(($l_gross_profit / $tani), $keta);
}
    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='����������'", $p1_ym);
if (getUniResult($query, $p1_all_gross_profit) < 1) {
    $p1_all_gross_profit = 0;                   // ��������
} else {
    if ($p1_ym == 200906) {
        $p1_all_gross_profit = $p1_all_gross_profit + $p1_b_uri_sagaku - 3100900;
    } elseif ($p1_ym == 200905) {
        $p1_all_gross_profit = $p1_all_gross_profit + $p1_b_uri_sagaku + 1550450;
    } elseif ($p1_ym == 200904) {
        $p1_all_gross_profit = $p1_all_gross_profit + $p1_b_uri_sagaku + 1550450;
    } else {
        $p1_all_gross_profit = $p1_all_gross_profit + $p1_b_uri_sagaku;
    }
    $p1_all_gross_profit = number_format(($p1_all_gross_profit / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�������'", $p1_ym);
if (getUniResult($query, $p1_c_gross_profit) < 1) {
    $p1_c_gross_profit = 0;                     // ��������
} else {
    $p1_c_gross_profit = $p1_c_gross_profit + $p1_b_urigen - $p1_sc_uri_sagaku + $p1_sc_metarial_sagaku;    // ���ץ��������̣
    if ($p1_ym == 200912) {
        $p1_c_gross_profit = $p1_c_gross_profit - 1227429;
    }
    if ($p1_ym >= 201001) {
        $p1_c_gross_profit = $p1_c_gross_profit - $p1_c_kyu_kin;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        //$p1_c_gross_profit = $p1_c_gross_profit - 151313;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    $p1_b_urigen       = number_format(($p1_b_urigen / $tani), $keta);
    $p1_c_gross_profit = number_format(($p1_c_gross_profit / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�������'", $p1_ym);
if (getUniResult($query, $p1_l_gross_profit) < 1) {
    if ($p1_ym == 200906) {
        $p1_l_gross_profit = 0 - $p1_s_gross_profit_sagaku - 3100900;   // ��������
    } elseif ($p1_ym == 200905) {
        $p1_l_gross_profit = 0 - $p1_s_gross_profit_sagaku - 1550450;   // ��������
    } elseif ($p1_ym == 200904) {
        $p1_l_gross_profit = 0 - $p1_s_gross_profit_sagaku - 1550450;   // ��������
    } else {
        $p1_l_gross_profit = 0 - $p1_s_gross_profit_sagaku;             // ��������
    }
} else {
    if ($p1_ym == 200906) {
        $p1_l_gross_profit = $p1_l_gross_profit - $p1_s_gross_profit_sagaku - 3100900;
    } elseif ($p1_ym == 200905) {
        $p1_l_gross_profit = $p1_l_gross_profit - $p1_s_gross_profit_sagaku + 1550450;
    } elseif ($p1_ym == 200904) {
        $p1_l_gross_profit = $p1_l_gross_profit - $p1_s_gross_profit_sagaku + 1550450;
    } else {
        $p1_l_gross_profit = $p1_l_gross_profit - $p1_s_gross_profit_sagaku;
    }
    if ($p1_ym == 200912) {
        $p1_l_gross_profit = $p1_l_gross_profit - 182279;
    } 
    if ($p1_ym >= 201001) {
        $p1_l_gross_profit = $p1_l_gross_profit - $p1_l_kyu_kin;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        //$p1_l_gross_profit = $p1_l_gross_profit - 151313;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    if ($p1_ym == 201004) {
        $p1_l_gross_profit = $p1_l_gross_profit - 255240;
    }
    $p1_l_gross_profit = number_format(($p1_l_gross_profit / $tani), $keta);
}
    ///// ������
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='����������'", $p2_ym);
if (getUniResult($query, $p2_all_gross_profit) < 1) {
    $p2_all_gross_profit = 0;                   // ��������
} else {
    if ($p2_ym == 200906) {
        $p2_all_gross_profit = $p2_all_gross_profit + $p2_b_uri_sagaku - 3100900;
    } elseif ($p2_ym == 200905) {
        $p2_all_gross_profit = $p2_all_gross_profit + $p2_b_uri_sagaku + 1550450;
    } elseif ($p2_ym == 200904) {
        $p2_all_gross_profit = $p2_all_gross_profit + $p2_b_uri_sagaku + 1550450;
    } else {
        $p2_all_gross_profit = $p2_all_gross_profit + $p2_b_uri_sagaku;
    }
    $p2_all_gross_profit = number_format(($p2_all_gross_profit / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�������'", $p2_ym);
if (getUniResult($query, $p2_c_gross_profit) < 1) {
    $p2_c_gross_profit = 0;                     // ��������
} else {
    $p2_c_gross_profit = $p2_c_gross_profit + $p2_b_urigen - $p2_sc_uri_sagaku + $p2_sc_metarial_sagaku;    // ���ץ��������̣
    if ($p2_ym == 200912) {
        $p2_c_gross_profit = $p2_c_gross_profit - 1227429;
    }
    if ($p2_ym >= 201001) {
        $p2_c_gross_profit = $p2_c_gross_profit - $p2_c_kyu_kin;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        //$p2_c_gross_profit = $p2_c_gross_profit - 151313;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    $p2_b_urigen       = number_format(($p2_b_urigen / $tani), $keta);
    $p2_c_gross_profit = number_format(($p2_c_gross_profit / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�������'", $p2_ym);
if (getUniResult($query, $p2_l_gross_profit) < 1) {
    if ($p2_ym == 200906) {
        $p2_l_gross_profit = 0 - $p2_s_gross_profit_sagaku - 3100900;   // ��������
    } elseif ($p2_ym == 200905) {
        $p2_l_gross_profit = 0 - $p2_s_gross_profit_sagaku - 1550450;   // ��������
    } elseif ($p2_ym == 200904) {
        $p2_l_gross_profit = 0 - $p2_s_gross_profit_sagaku - 1550450;   // ��������
    } else {
        $p2_l_gross_profit = 0 - $p2_s_gross_profit_sagaku;             // ��������
    }
} else {
    if ($p2_ym == 200906) {
        $p2_l_gross_profit = $p2_l_gross_profit - $p2_s_gross_profit_sagaku - 3100900;
    } elseif ($p2_ym == 200905) {
        $p2_l_gross_profit = $p2_l_gross_profit - $p2_s_gross_profit_sagaku + 1550450;
    } elseif ($p2_ym == 200904) {
        $p2_l_gross_profit = $p2_l_gross_profit - $p2_s_gross_profit_sagaku + 1550450;
    } else {
        $p2_l_gross_profit = $p2_l_gross_profit - $p2_s_gross_profit_sagaku;
    }
    if ($p2_ym == 200912) {
        $p2_l_gross_profit = $p2_l_gross_profit - 182279;
    }
    if ($p2_ym >= 201001) {
        $p2_l_gross_profit = $p2_l_gross_profit - $p2_l_kyu_kin;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        //$p2_l_gross_profit = $p2_l_gross_profit - 151313;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    if ($p2_ym == 201004) {
        $p2_l_gross_profit = $p2_l_gross_profit - 255240;
    }
    $p2_l_gross_profit = number_format(($p2_l_gross_profit / $tani), $keta);
}
    ///// �����߷�
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='����������'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_gross_profit) < 1) {
    $rui_all_gross_profit = 0;                  // ��������
} else {
    if ($yyyymm == 200905) {
        $rui_all_gross_profit = $rui_all_gross_profit + 3100900;
    } elseif ($yyyymm == 200904) {
        $rui_all_gross_profit = $rui_all_gross_profit + 1550450;
    }
    $rui_all_gross_profit = $rui_all_gross_profit + $rui_b_uri_sagaku;
    $rui_all_gross_profit = number_format(($rui_all_gross_profit / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ�������'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_c_gross_profit) < 1) {
    $rui_c_gross_profit = 0;                    // ��������
} else {
    $rui_c_gross_profit = $rui_c_gross_profit + $rui_b_urigen - $rui_sc_uri_sagaku + $rui_sc_metarial_sagaku;   // ���ץ��������̣
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_c_gross_profit = $rui_c_gross_profit - 1227429;
    }
    if ($yyyymm >= 201001) {
        $rui_c_gross_profit = $rui_c_gross_profit - $rui_c_kyu_kin;     // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        //$rui_c_gross_profit = $rui_c_gross_profit - 151313;     // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    $rui_b_urigen       = number_format(($rui_b_urigen / $tani), $keta);
    $rui_c_gross_profit = number_format(($rui_c_gross_profit / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥�������'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_gross_profit) < 1) {
    $rui_l_gross_profit = 0 - $rui_s_gross_profit_sagaku;   // ��������
} else {
    $rui_l_gross_profit = $rui_l_gross_profit - $rui_s_gross_profit_sagaku;
    if ($yyyymm == 200905) {
        $rui_l_gross_profit = $rui_l_gross_profit + 3100900;
    } elseif ($yyyymm == 200904) {
        $rui_l_gross_profit = $rui_l_gross_profit + 1550450;
    }
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_l_gross_profit = $rui_l_gross_profit - 182279;
    }
    if ($yyyymm >= 201001) {
        $rui_l_gross_profit = $rui_l_gross_profit - $rui_l_kyu_kin;
        //$rui_l_gross_profit = $rui_l_gross_profit - 151313;
    }
    if ($yyyymm >= 201004 && $yyyymm <= 201103) {
        $rui_l_gross_profit = $rui_l_gross_profit - 255240;
    }
    $rui_l_gross_profit = number_format(($rui_l_gross_profit / $tani), $keta);
}

/********** �δ���οͷ��� **********/
    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��ͷ���'", $yyyymm);
if (getUniResult($query, $s_han_jin) < 1) {
    $s_han_jin        = 0;                      // ��������
    $s_han_jin_sagaku = 0;
} else {
    $s_han_jin_sagaku = $s_han_jin;
    $s_han_jin        = number_format(($s_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ɿͷ���'", $yyyymm);
if (getUniResult($query, $b_han_jin_sagaku) < 1) {
    $b_han_jin_sagaku = 0;                      // ��������
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���οͷ���'", $yyyymm);
if (getUniResult($query, $all_han_jin) < 1) {
    $all_han_jin = 0;                           // ��������
} else {
    $all_han_jin = number_format(($all_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ龦�ɼҰ���ʬ��Ϳ'", $yyyymm);
if (getUniResult($query, $c_allo_kin) < 1) {
    $c_allo_kin = 0;                            // ��������
} else {
    //$c_han_jin = number_format(($c_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�ͷ���'", $yyyymm);
if (getUniResult($query, $c_han_jin) < 1) {
    $c_han_jin = 0;                             // ��������
} else {
    $c_han_jin = $c_han_jin - $c_allo_kin;
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥����ɼҰ���ʬ��Ϳ'", $yyyymm);
if (getUniResult($query, $l_allo_kin) < 1) {
    $l_allo_kin = 0;     // ��������
} else {
    //$c_han_jin = number_format(($c_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��ͷ���'", $yyyymm);
if (getUniResult($query, $l_han_jin) < 1) {
    $l_han_jin = 0 - $s_han_jin_sagaku;         // ��������
} else {
    $l_han_jin = $l_han_jin - $s_han_jin_sagaku - $l_allo_kin;
    $l_han_jin = number_format(($l_han_jin / $tani), $keta);
}
    ///// ���� ����
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=8101 and orign_id=670", $yyyymm);
if (getUniResult($query, $b_han_jin) < 1) {
    $b_han_jin = 0 + $b_han_jin_sagaku;         // ��������
    $b_han_all = $b_han_jin;
    $b_sagaku  = $b_sagaku + $b_han_jin;        // ���ץ麹�۷׻���
} else {
    // ����7��̤ʧ����;ʬ�ɲ� �ƥ�����
    $b_han_jin = $b_han_jin + $b_han_jin_sagaku;
    $c_han_jin = $c_han_jin - $b_han_jin;
    $b_sagaku  = $b_sagaku + $b_han_jin;        // ���ץ麹�۷׻���
    $b_han_jin = $b_han_jin + $c_allo_kin + $l_allo_kin;
    $b_han_all = $b_han_jin;
    $c_han_jin = number_format(($c_han_jin / $tani), $keta);
    $b_han_jin = number_format(($b_han_jin / $tani), $keta);
}

    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��ͷ���'", $p1_ym);
if (getUniResult($query, $p1_s_han_jin) < 1) {
    $p1_s_han_jin        = 0;                   // ��������
    $p1_s_han_jin_sagaku = 0;
} else {
    $p1_s_han_jin_sagaku = $p1_s_han_jin;
    $p1_s_han_jin        = number_format(($p1_s_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ɿͷ���'", $p1_ym);
if (getUniResult($query, $p1_b_han_jin_sagaku) < 1) {
    $p1_b_han_jin_sagaku = 0;                   // ��������
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���οͷ���'", $p1_ym);
if (getUniResult($query, $p1_all_han_jin) < 1) {
    $p1_all_han_jin = 0;                        // ��������
} else {
    $p1_all_han_jin = number_format(($p1_all_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ龦�ɼҰ���ʬ��Ϳ'", $p1_ym);
if (getUniResult($query, $p1_c_allo_kin) < 1) {
    $p1_c_allo_kin = 0;                         // ��������
} else {
    //$c_han_jin = number_format(($c_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�ͷ���'", $p1_ym);
if (getUniResult($query, $p1_c_han_jin) < 1) {
    $p1_c_han_jin = 0 - $p1_c_allo_kin;         // ��������
} else {
    $p1_c_han_jin = $p1_c_han_jin - $p1_c_allo_kin;
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥����ɼҰ���ʬ��Ϳ'", $p1_ym);
if (getUniResult($query, $p1_l_allo_kin) < 1) {
    $p1_l_allo_kin = 0;                         // ��������
} else {
    //$c_han_jin = number_format(($c_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��ͷ���'", $p1_ym);
if (getUniResult($query, $p1_l_han_jin) < 1) {
    $p1_l_han_jin = 0 - $p1_s_han_jin_sagaku - $p1_l_allo_kin;      // ��������
} else {
    $p1_l_han_jin = $p1_l_han_jin - $p1_s_han_jin_sagaku - $p1_l_allo_kin;
    $p1_l_han_jin = number_format(($p1_l_han_jin / $tani), $keta);
}
    ///// ���� ����
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=8101 and orign_id=670", $p1_ym);
if (getUniResult($query, $p1_b_han_jin) < 1) {
    $p1_b_han_jin = 0 + $p1_b_han_jin_sagaku + $p1_c_allo_kin + $p1_l_allo_kin;     // ��������
    $p1_b_han_all = $p1_b_han_jin;
    $p1_b_sagaku  = $p1_b_sagaku + $p1_b_han_jin;       // ���ץ麹�۷׻���
} else {
    $p1_b_han_jin = $p1_b_han_jin + $p1_b_han_jin_sagaku;
    $p1_c_han_jin = $p1_c_han_jin - $p1_b_han_jin;
    $p1_b_sagaku  = $p1_b_sagaku + $p1_b_han_jin;       // ���ץ麹�۷׻���
    $p1_b_han_jin = $p1_b_han_jin + $p1_c_allo_kin + $p1_l_allo_kin;
    $p1_b_han_all = $p1_b_han_jin;
    $p1_c_han_jin = number_format(($p1_c_han_jin / $tani), $keta);
    $p1_b_han_jin = number_format(($p1_b_han_jin / $tani), $keta);
}

    ///// ������
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��ͷ���'", $p2_ym);
if (getUniResult($query, $p2_s_han_jin) < 1) {
    $p2_s_han_jin        = 0;                   // ��������
    $p2_s_han_jin_sagaku = 0;
} else {
    $p2_s_han_jin_sagaku = $p2_s_han_jin;
    $p2_s_han_jin        = number_format(($p2_s_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ɿͷ���'", $p2_ym);
if (getUniResult($query, $p2_b_han_jin_sagaku) < 1) {
    $p2_b_han_jin_sagaku = 0;                   // ��������
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���οͷ���'", $p2_ym);
if (getUniResult($query, $p2_all_han_jin) < 1) {
    $p2_all_han_jin = 0;                        // ��������
} else {
    $p2_all_han_jin = number_format(($p2_all_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ龦�ɼҰ���ʬ��Ϳ'", $p2_ym);
if (getUniResult($query, $p2_c_allo_kin) < 1) {
    $p2_c_allo_kin = 0;                         // ��������
} else {
    //$c_han_jin = number_format(($c_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�ͷ���'", $p2_ym);
if (getUniResult($query, $p2_c_han_jin) < 1) {
    $p2_c_han_jin = 0 - $p2_c_allo_kin;         // ��������
} else {
    $p2_c_han_jin = $p2_c_han_jin - $p2_c_allo_kin;
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥����ɼҰ���ʬ��Ϳ'", $p2_ym);
if (getUniResult($query, $p2_l_allo_kin) < 1) {
    $p2_l_allo_kin = 0;     // ��������
} else {
    //$c_han_jin = number_format(($c_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��ͷ���'", $p2_ym);
if (getUniResult($query, $p2_l_han_jin) < 1) {
    $p2_l_han_jin = 0 - $p2_s_han_jin_sagaku - $p2_l_allo_kin;      // ��������
} else {
    $p2_l_han_jin = $p2_l_han_jin - $p2_s_han_jin_sagaku - $p2_l_allo_kin;
    $p2_l_han_jin = number_format(($p2_l_han_jin / $tani), $keta);
}
    ///// ������ ����
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=8101 and orign_id=670", $p2_ym);
if (getUniResult($query, $p2_b_han_jin) < 1) {
    $p2_b_han_jin = 0 + $p2_b_han_jin_sagaku + $p2_c_allo_kin + $p2_l_allo_kin;     // ��������
    $p2_b_han_all = $p2_b_han_jin;
    $p2_b_sagaku  = $p2_b_sagaku + $p2_b_han_jin;                   // ���ץ麹�۷׻���
} else {
    $p2_b_han_jin = $p2_b_han_jin + $p2_b_han_jin_sagaku;
    $p2_c_han_jin = $p2_c_han_jin - $p2_b_han_jin;
    $p2_b_sagaku  = $p2_b_sagaku + $p2_b_han_jin;                   // ���ץ麹�۷׻���
    $p2_b_han_jin = $p2_b_han_jin + $p2_c_allo_kin + $p2_l_allo_kin;
    $p2_b_han_all = $p2_b_han_jin;
    $p2_c_han_jin = number_format(($p2_c_han_jin / $tani), $keta);
    $p2_b_han_jin = number_format(($p2_b_han_jin / $tani), $keta);
}

    ///// �����߷�
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��ͷ���'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_s_han_jin) < 1) {
    $rui_s_han_jin        = 0;                  // ��������
    $rui_s_han_jin_sagaku = 0;
} else {
    $rui_s_han_jin_sagaku = $rui_s_han_jin;
    $rui_s_han_jin        = number_format(($rui_s_han_jin / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ɿͷ���'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_b_han_jin_sagaku) < 1) {
    $rui_b_han_jin_sagaku = 0;                  // ��������
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���οͷ���'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_han_jin) < 1) {
    $rui_all_han_jin = 0;                       // ��������
} else {
    $rui_all_han_jin = number_format(($rui_all_han_jin / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ龦�ɼҰ���ʬ��Ϳ'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_c_allo_kin) < 1) {
    $rui_c_allo_kin = 0;                        // ��������
} else {
    //$c_han_jin = number_format(($c_han_jin / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ�ͷ���'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_c_han_jin) < 1) {
    $rui_c_han_jin = 0 - $rui_c_allo_kin;       // ��������
} else {
    $rui_c_han_jin = $rui_c_han_jin - $rui_c_allo_kin;
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥����ɼҰ���ʬ��Ϳ'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_allo_kin) < 1) {
    $rui_l_allo_kin = 0;     // ��������
} else {
    //$c_han_jin = number_format(($c_han_jin / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥��ͷ���'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_han_jin) < 1) {
    $rui_l_han_jin = 0 - $rui_s_han_jin_sagaku - $rui_l_allo_kin;   // ��������
} else {
    $rui_l_han_jin = $rui_l_han_jin - $rui_s_han_jin_sagaku - $rui_l_allo_kin;
    $rui_l_han_jin = number_format(($rui_l_han_jin / $tani), $keta);
}
    ///// �����߷� ����
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym>=%d and pl_bs_ym<=%d and actcod>=8101 and orign_id=670", $str_ym, $yyyymm);
if (getUniResult($query, $rui_b_han_jin) < 1) {
    $rui_b_han_jin = 0 + $rui_b_han_jin_sagaku + $rui_c_allo_kin + $rui_l_allo_kin;     // ��������
    $rui_b_han_all = $rui_b_han_jin;
    $rui_b_sagaku  = $rui_b_sagaku + $rui_b_han_jin;                // ���ץ麹�۷׻���
} else {
    // ����7��̤ʧ����;ʬ�ɲ� �ƥ�����
    $rui_b_han_jin = $rui_b_han_jin + $rui_b_han_jin_sagaku;
    $rui_c_han_jin = $rui_c_han_jin - $rui_b_han_jin;
    $rui_b_sagaku  = $rui_b_sagaku + $rui_b_han_jin;                // ���ץ麹�۷׻���
    $rui_b_han_jin = $rui_b_han_jin + $rui_c_allo_kin + $rui_l_allo_kin;
    $rui_b_han_all = $rui_b_han_jin;
    $rui_c_han_jin = number_format(($rui_c_han_jin / $tani), $keta);
    $rui_b_han_jin = number_format(($rui_b_han_jin / $tani), $keta);
}

/********** �δ���η��� **********/
    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��δ������'", $yyyymm);
if (getUniResult($query, $s_han_kei) < 1) {
    $s_han_kei        = 0;                  // ��������
    $s_han_kei_sagaku = 0;
} else {
    $s_han_kei_sagaku = $s_han_kei;
    $s_han_kei        = number_format(($s_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�����δ������'", $yyyymm);
if (getUniResult($query, $b_han_kei_sagaku) < 1) {
    $b_han_kei_sagaku = 0;                  // ��������
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���η���'", $yyyymm);
if (getUniResult($query, $all_han_kei) < 1) {
    $all_han_kei = 0;                       // ��������
} else {
    $all_han_kei = number_format(($all_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ����'", $yyyymm);
if (getUniResult($query, $c_han_kei) < 1) {
    $c_han_kei = 0;                         // ��������
} else {
    //$c_han_kei = number_format(($c_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�����'", $yyyymm);
if (getUniResult($query, $l_han_kei) < 1) {
    $l_han_kei = 0 - $s_han_kei_sagaku;     // ��������
} else {
    $l_han_kei = $l_han_kei - $s_han_kei_sagaku;
    $l_han_kei = number_format(($l_han_kei / $tani), $keta);
}
    ///// ���� ����
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=7501 and actcod<=8000 and orign_id=670", $yyyymm);
if (getUniResult($query, $b_han_kei) < 1) {
    $b_han_kei = 0 + $b_han_kei_sagaku;     // ��������
    $b_han_all = $b_han_all + $b_han_kei;
    $b_sagaku  = $b_sagaku + $b_han_kei;    // ���ץ麹�۷׻���
} else {
    $b_han_kei = $b_han_kei + $b_han_kei_sagaku;
    $b_han_all = $b_han_all + $b_han_kei;
    $c_han_kei = $c_han_kei - $b_han_kei;
    $b_sagaku  = $b_sagaku + $b_han_kei;    // ���ץ麹�۷׻���
    $c_han_kei = number_format(($c_han_kei / $tani), $keta);
    $b_han_kei = number_format(($b_han_kei / $tani), $keta);
}

    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��δ������'", $p1_ym);
if (getUniResult($query, $p1_s_han_kei) < 1) {
    $p1_s_han_kei        = 0;               // ��������
    $p1_s_han_kei_sagaku = 0;
} else {
    $p1_s_han_kei_sagaku = $p1_s_han_kei;
    $p1_s_han_kei        = number_format(($p1_s_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�����δ������'", $p1_ym);
if (getUniResult($query, $p1_b_han_kei_sagaku) < 1) {
    $p1_b_han_kei_sagaku = 0;               // ��������
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���η���'", $p1_ym);
if (getUniResult($query, $p1_all_han_kei) < 1) {
    $p1_all_han_kei = 0;                    // ��������
} else {
    $p1_all_han_kei = number_format(($p1_all_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ����'", $p1_ym);
if (getUniResult($query, $p1_c_han_kei) < 1) {
    $p1_c_han_kei = 0;                      // ��������
} else {
    //$p1_c_han_kei = number_format(($p1_c_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�����'", $p1_ym);
if (getUniResult($query, $p1_l_han_kei) < 1) {
    $p1_l_han_kei = 0 - $p1_s_han_kei_sagaku;       // ��������
} else {
    $p1_l_han_kei = $p1_l_han_kei - $p1_s_han_kei_sagaku;
    $p1_l_han_kei = number_format(($p1_l_han_kei / $tani), $keta);
}
    ///// ���� ����
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=7501 and actcod<=8000 and orign_id=670", $p1_ym);
if (getUniResult($query, $p1_b_han_kei) < 1) {
    $p1_b_han_kei = 0 + $p1_b_han_kei_sagaku;       // ��������
    $p1_b_han_all = $p1_b_han_all + $p1_b_han_kei;
    $p1_b_sagaku  = $p1_b_sagaku + $p1_b_han_kei;   // ���ץ麹�۷׻���
} else {
    $p1_b_han_kei = $p1_b_han_kei + $p1_b_han_kei_sagaku;
    $p1_b_han_all = $p1_b_han_all + $p1_b_han_kei;
    $p1_c_han_kei = $p1_c_han_kei - $p1_b_han_kei;
    $p1_b_sagaku  = $p1_b_sagaku + $p1_b_han_kei;   // ���ץ麹�۷׻���
    $p1_c_han_kei = number_format(($p1_c_han_kei / $tani), $keta);
    $p1_b_han_kei = number_format(($p1_b_han_kei / $tani), $keta);
}

    ///// ������
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��δ������'", $p2_ym);
if (getUniResult($query, $p2_s_han_kei) < 1) {
    $p2_s_han_kei        = 0;               // ��������
    $p2_s_han_kei_sagaku = 0;
} else {
    $p2_s_han_kei_sagaku = $p2_s_han_kei;
    $p2_s_han_kei        = number_format(($p2_s_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�����δ������'", $p2_ym);
if (getUniResult($query, $p2_b_han_kei_sagaku) < 1) {
    $p2_b_han_kei_sagaku = 0;               // ��������
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���η���'", $p2_ym);
if (getUniResult($query, $p2_all_han_kei) < 1) {
    $p2_all_han_kei = 0;                    // ��������
} else {
    $p2_all_han_kei = number_format(($p2_all_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ����'", $p2_ym);
if (getUniResult($query, $p2_c_han_kei) < 1) {
    $p2_c_han_kei = 0;                      // ��������
} else {
    //$p2_c_han_kei = number_format(($p2_c_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�����'", $p2_ym);
if (getUniResult($query, $p2_l_han_kei) < 1) {
    $p2_l_han_kei = 0 - $p2_s_han_kei_sagaku;       // ��������
} else {
    $p2_l_han_kei = $p2_l_han_kei - $p2_s_han_kei_sagaku;
    $p2_l_han_kei = number_format(($p2_l_han_kei / $tani), $keta);
}
    ///// ������ ����
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=7501 and actcod<=8000 and orign_id=670", $p2_ym);
if (getUniResult($query, $p2_b_han_kei) < 1) {
    $p2_b_han_kei = 0 + $p2_b_han_kei_sagaku;       // ��������
    $p2_b_han_all = $p2_b_han_all + $p2_b_han_kei;
    $p2_b_sagaku  = $p2_b_sagaku + $p2_b_han_kei;   // ���ץ麹�۷׻���
} else {
    $p2_b_han_kei = $p2_b_han_kei + $p2_b_han_kei_sagaku;
    $p2_b_han_all = $p2_b_han_all + $p2_b_han_kei;
    $p2_c_han_kei = $p2_c_han_kei - $p2_b_han_kei;
    $p2_b_sagaku  = $p2_b_sagaku + $p2_b_han_kei;   // ���ץ麹�۷׻���
    $p2_c_han_kei = number_format(($p2_c_han_kei / $tani), $keta);
    $p2_b_han_kei = number_format(($p2_b_han_kei / $tani), $keta);
}

    ///// �����߷�
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��δ������'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_s_han_kei) < 1) {
    $rui_s_han_kei        = 0;                      // ��������
    $rui_s_han_kei_sagaku = 0;
} else {
    $rui_s_han_kei_sagaku = $rui_s_han_kei;
    $rui_s_han_kei        = number_format(($rui_s_han_kei / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�����δ������'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_b_han_kei_sagaku) < 1) {
    $rui_b_han_kei_sagaku = 0;                      // ��������
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���η���'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_han_kei) < 1) {
    $rui_all_han_kei = 0;                           // ��������
} else {
    $rui_all_han_kei = number_format(($rui_all_han_kei / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ����'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_c_han_kei) < 1) {
    $rui_c_han_kei = 0;                             // ��������
} else {
    //$rui_c_han_kei = number_format(($rui_c_han_kei / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥�����'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_han_kei) < 1) {
    $rui_l_han_kei = 0 - $rui_s_han_kei_sagaku;     // ��������
} else {
    $rui_l_han_kei = $rui_l_han_kei - $rui_s_han_kei_sagaku;
    $rui_l_han_kei = number_format(($rui_l_han_kei / $tani), $keta);
}
    ///// �����߷� ����
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym>=%d and pl_bs_ym<=%d and actcod>=7501 and actcod<=8000 and orign_id=670", $str_ym, $yyyymm);
if (getUniResult($query, $rui_b_han_kei) < 1) {
    $rui_b_han_kei = 0 + $rui_b_han_kei_sagaku;         // ��������
    $rui_b_han_all = $rui_b_han_all + $rui_b_han_kei;
    $rui_b_sagaku  = $rui_b_sagaku + $rui_b_han_kei;    // ���ץ麹�۷׻���
} else {
    $rui_b_han_kei = $rui_b_han_kei + $rui_b_han_kei_sagaku;
    $rui_b_han_all = $rui_b_han_all + $rui_b_han_kei;
    $rui_c_han_kei = $rui_c_han_kei - $rui_b_han_kei;
    $rui_b_sagaku  = $rui_b_sagaku + $rui_b_han_kei;    // ���ץ麹�۷׻���
    $rui_c_han_kei = number_format(($rui_c_han_kei / $tani), $keta);
    $rui_b_han_kei = number_format(($rui_b_han_kei / $tani), $keta);
}

/********** �δ���ι�� **********/
    ///// ����
    ///// �������
    $s_han_all        = $s_han_jin_sagaku + $s_han_kei_sagaku;
    $s_han_all_sagaku = $s_han_all;
    $s_han_all        = number_format(($s_han_all / $tani), $keta);
    ///// CL
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�����δ���'", $yyyymm);
if (getUniResult($query, $all_han_all) < 1) {
    $all_han_all = 0;                           // ��������
} else {
    $all_han_all = number_format(($all_han_all / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��δ���'", $yyyymm);
if (getUniResult($query, $c_han_all) < 1) {
    $c_han_all = 0;                             // ��������
} else {
    $c_han_all = $c_han_all - $b_han_all + $c_allo_kin + $l_allo_kin - $c_allo_kin;
    $c_han_all = number_format(($c_han_all / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��δ���'", $yyyymm);
if (getUniResult($query, $l_han_all) < 1) {
    $l_han_all = 0 - $s_han_all_sagaku;         // ��������
} else {
    $l_han_all = $l_han_all - $s_han_all_sagaku - $l_allo_kin;
    $l_han_all = number_format(($l_han_all / $tani), $keta);
}

    ///// ����
    ///// �������
    $p1_s_han_all        = $p1_s_han_jin_sagaku + $p1_s_han_kei_sagaku;
    $p1_s_han_all_sagaku = $p1_s_han_all;
    $p1_s_han_all        = number_format(($p1_s_han_all / $tani), $keta);
    ///// CL
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�����δ���'", $p1_ym);
if (getUniResult($query, $p1_all_han_all) < 1) {
    $p1_all_han_all = 0;                        // ��������
} else {
    $p1_all_han_all = number_format(($p1_all_han_all / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��δ���'", $p1_ym);
if (getUniResult($query, $p1_c_han_all) < 1) {
    $p1_c_han_all = 0;                          // ��������
} else {
    $p1_c_han_all = $p1_c_han_all - $p1_b_han_all + $p1_c_allo_kin + $p1_l_allo_kin - $p1_c_allo_kin;
    $p1_c_han_all = number_format(($p1_c_han_all / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��δ���'", $p1_ym);
if (getUniResult($query, $p1_l_han_all) < 1) {
    $p1_l_han_all = 0 - $p1_s_han_all_sagaku;   // ��������
} else {
    $p1_l_han_all = $p1_l_han_all - $p1_s_han_all_sagaku - $p1_l_allo_kin;
    $p1_l_han_all = number_format(($p1_l_han_all / $tani), $keta);
}

    ///// ������
    ///// �������
    $p2_s_han_all        = $p2_s_han_jin_sagaku + $p2_s_han_kei_sagaku;
    $p2_s_han_all_sagaku = $p2_s_han_all;
    $p2_s_han_all        = number_format(($p2_s_han_all / $tani), $keta);
    ///// CL
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�����δ���'", $p2_ym);
if (getUniResult($query, $p2_all_han_all) < 1) {
    $p2_all_han_all = 0;                        // ��������
} else {
    $p2_all_han_all = number_format(($p2_all_han_all / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��δ���'", $p2_ym);
if (getUniResult($query, $p2_c_han_all) < 1) {
    $p2_c_han_all = 0;                          // ��������
} else {
    $p2_c_han_all = $p2_c_han_all - $p2_b_han_all + $p2_c_allo_kin + $p2_l_allo_kin - $p2_c_allo_kin;
    $p2_c_han_all = number_format(($p2_c_han_all / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��δ���'", $p2_ym);
if (getUniResult($query, $p2_l_han_all) < 1) {
    $p2_l_han_all = 0 - $p2_s_han_all_sagaku;   // ��������
} else {
    $p2_l_han_all = $p2_l_han_all - $p2_s_han_all_sagaku - $p2_l_allo_kin;
    $p2_l_han_all = number_format(($p2_l_han_all / $tani), $keta);
}

    ///// �����߷�
    ///// �������
    $rui_s_han_all        = $rui_s_han_jin_sagaku + $rui_s_han_kei_sagaku;
    $rui_s_han_all_sagaku = $rui_s_han_all;
    $rui_s_han_all        = number_format(($rui_s_han_all / $tani), $keta);
    ///// CL
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�����δ���'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_han_all) < 1) {
    $rui_all_han_all = 0;                       // ��������
} else {
    $rui_all_han_all = number_format(($rui_all_han_all / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ��δ���'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_c_han_all) < 1) {
    $rui_c_han_all = 0;                         // ��������
} else {
    $rui_c_han_all = $rui_c_han_all - $rui_b_han_all + $rui_c_allo_kin + $rui_l_allo_kin - $rui_c_allo_kin;
    $rui_c_han_all = number_format(($rui_c_han_all / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥��δ���'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_han_all) < 1) {
    $rui_l_han_all = 0 - $rui_s_han_all_sagaku; // ��������
} else {
    $rui_l_han_all = $rui_l_han_all - $rui_s_han_all_sagaku - $rui_l_allo_kin;
    $rui_l_han_all = number_format(($rui_l_han_all / $tani), $keta);
}

/********** �Ķ����� **********/
    ///// ����
$p2_b_ope_profit    = $p2_b_gross_profit - $p2_b_han_all;
$p2_b_han_all       = number_format(($p2_b_han_all / $tani), $keta);
$p2_b_gross_profit  = number_format(($p2_b_gross_profit / $tani), $keta);

$p1_b_ope_profit    = $p1_b_gross_profit - $p1_b_han_all;
$p1_b_han_all       = number_format(($p1_b_han_all / $tani), $keta);
$p1_b_gross_profit  = number_format(($p1_b_gross_profit / $tani), $keta);

$b_ope_profit       = $b_gross_profit - $b_han_all;
$b_han_all          = number_format(($b_han_all / $tani), $keta);
$b_gross_profit     = number_format(($b_gross_profit / $tani), $keta);

$rui_b_ope_profit   = $rui_b_gross_profit - $rui_b_han_all;
$rui_b_han_all      = number_format(($rui_b_han_all / $tani), $keta);
$rui_b_gross_profit = number_format(($rui_b_gross_profit / $tani), $keta);
    ///// �������
$p2_s_ope_profit         = $p2_s_gross_profit_sagaku - $p2_s_han_all_sagaku;
$p2_s_ope_profit_sagaku  = $p2_s_ope_profit;
$p2_s_ope_profit         = $p2_s_ope_profit + $p2_sc_uri_sagaku - $p2_sc_metarial_sagaku;       // ���ץ��������̣��sagaku�θ�ݥ�˥�����ޥ��ʥ����Ƥ��ޤ��١�
if ($p2_ym == 200912) {
    $p2_s_ope_profit = $p2_s_ope_profit + 1409708;
}
if ($p2_ym >= 201001) {
    $p2_s_ope_profit = $p2_s_ope_profit + $p2_s_kyu_kei - $p2_s_kyu_kin;  // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    //$p2_s_ope_profit = $p2_s_ope_profit + 432323 - 129697;  // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
}
$p2_s_ope_profit         = number_format(($p2_s_ope_profit / $tani), $keta);

$p1_s_ope_profit         = $p1_s_gross_profit_sagaku - $p1_s_han_all_sagaku;
$p1_s_ope_profit_sagaku  = $p1_s_ope_profit;
$p1_s_ope_profit         = $p1_s_ope_profit + $p1_sc_uri_sagaku - $p1_sc_metarial_sagaku;       // ���ץ��������̣��sagaku�θ�ݥ�˥�����ޥ��ʥ����Ƥ��ޤ��١�
if ($p1_ym == 200912) {
    $p1_s_ope_profit = $p1_s_ope_profit + 1409708;
}
if ($p1_ym >= 201001) {
    $p1_s_ope_profit = $p1_s_ope_profit + $p1_s_kyu_kei - $p1_s_kyu_kin;  // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    //$p1_s_ope_profit = $p1_s_ope_profit + 432323 - 129697;  // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
}
$p1_s_ope_profit         = number_format(($p1_s_ope_profit / $tani), $keta);

$s_ope_profit            = $s_gross_profit_sagaku - $s_han_all_sagaku;
$s_ope_profit_sagaku     = $s_ope_profit;
$s_ope_profit            = $s_ope_profit + $sc_uri_sagaku - $sc_metarial_sagaku;                // ���ץ��������̣��sagaku�θ�ݥ�˥�����ޥ��ʥ����Ƥ��ޤ��١�
if ($yyyymm == 200912) {
    $s_ope_profit = $s_ope_profit + 1409708;
}
if ($yyyymm >= 201001) {
    $s_ope_profit = $s_ope_profit + $s_kyu_kei - $s_kyu_kin;  // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    //$s_ope_profit = $s_ope_profit + 432323 - 129697;  // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
}
$s_ope_profit            = number_format(($s_ope_profit / $tani), $keta);

$rui_s_ope_profit        = $rui_s_gross_profit_sagaku - $rui_s_han_all_sagaku;
$rui_s_ope_profit_sagaku = $rui_s_ope_profit;
$rui_s_ope_profit        = $rui_s_ope_profit + $rui_sc_uri_sagaku - $rui_sc_metarial_sagaku;    // ���ץ��������̣��sagaku�θ�ݥ�˥�����ޥ��ʥ����Ƥ��ޤ��١�
if ($yyyymm >= 200912 && $yyyymm <= 201003) {
    $rui_s_ope_profit = $rui_s_ope_profit + 1409708;
}
if ($yyyymm >= 201001) {
    $rui_s_ope_profit = $rui_s_ope_profit + $rui_s_kyu_kei - $rui_s_kyu_kin;  // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    //$rui_s_ope_profit = $rui_s_ope_profit + 432323 - 129697;  // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
}
$rui_s_ope_profit        = number_format(($rui_s_ope_profit / $tani), $keta);

    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���αĶ�����'", $yyyymm);
if (getUniResult($query, $all_ope_profit) < 1) {
    $all_ope_profit = 0;                        // ��������
} else {
    if ($yyyymm == 200906) {
        $all_ope_profit = $all_ope_profit + $b_uri_sagaku - 3100900;
    } elseif ($yyyymm == 200905) {
        $all_ope_profit = $all_ope_profit + $b_uri_sagaku + 1550450;
    } elseif ($yyyymm == 200904) {
        $all_ope_profit = $all_ope_profit + $b_uri_sagaku + 1550450;
    } else {
        $all_ope_profit = $all_ope_profit + $b_uri_sagaku;
    }
    $all_ope_profit = number_format(($all_ope_profit / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķ�����'", $yyyymm);
if (getUniResult($query, $c_ope_profit) < 1) {
    $c_ope_profit = 0;                          // ��������
    $c_ope_profit_temp = 0;
} else {
    $c_ope_profit = $c_ope_profit + $b_sagaku + $c_allo_kin - $sc_uri_sagaku + $sc_metarial_sagaku; // ���ץ��������̣
    if ($yyyymm == 200912) {
        $c_ope_profit = $c_ope_profit - 1227429;
    }
    if ($yyyymm >= 201001) {
        $c_ope_profit = $c_ope_profit - $c_kyu_kin; // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        //$c_ope_profit = $c_ope_profit - 151313; // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    $c_ope_profit_temp = $c_ope_profit;
    $c_ope_profit      = number_format(($c_ope_profit / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķ�����'", $yyyymm);
if (getUniResult($query, $l_ope_profit) < 1) {
    if ($yyyymm == 200906) {
        $l_ope_profit = 0 - $s_ope_profit_sagaku - 3100900;     // ��������
    } elseif ($yyyymm == 200905) {
        $l_ope_profit = 0 - $s_ope_profit_sagaku + 1550450;     // ��������
    } elseif ($yyyymm == 200904) {
        $l_ope_profit = 0 - $s_ope_profit_sagaku + 1550450;     // ��������
    } else {
        $l_ope_profit = 0 - $s_ope_profit_sagaku;               // ��������
    }
} else {
    if ($yyyymm == 200906) {
        $l_ope_profit = $l_ope_profit - $s_ope_profit_sagaku - 3100900 + $l_allo_kin;
    } elseif ($yyyymm == 200905) {
        $l_ope_profit = $l_ope_profit - $s_ope_profit_sagaku + 1550450 + $l_allo_kin;
    } elseif ($yyyymm == 200904) {
        $l_ope_profit = $l_ope_profit - $s_ope_profit_sagaku + 1550450 + $l_allo_kin;
    } else {
        $l_ope_profit = $l_ope_profit - $s_ope_profit_sagaku + $l_allo_kin;
    }
    if ($yyyymm == 200912) {
        $l_ope_profit = $l_ope_profit - 182279;
    }
    if ($yyyymm >= 201001) {
        $l_ope_profit = $l_ope_profit - $l_kyu_kin; // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        //$l_ope_profit = $l_ope_profit - 151313; // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    if ($yyyymm == 201004) {
        $l_ope_profit = $l_ope_profit - 255240;
    }
    $l_ope_profit = number_format(($l_ope_profit / $tani), $keta);
}
    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���αĶ�����'", $p1_ym);
if (getUniResult($query, $p1_all_ope_profit) < 1) {
    $p1_all_ope_profit = 0;                     // ��������
} else {
    if ($p1_ym == 200906) {
        $p1_all_ope_profit = $p1_all_ope_profit + $p1_b_uri_sagaku - 3100900;
    } elseif ($p1_ym == 200905) {
        $p1_all_ope_profit = $p1_all_ope_profit + $p1_b_uri_sagaku + 1550450;
    } elseif ($p1_ym == 200904) {
        $p1_all_ope_profit = $p1_all_ope_profit + $p1_b_uri_sagaku + 1550450;
    } else {
        $p1_all_ope_profit = $p1_all_ope_profit + $p1_b_uri_sagaku;
    }
    $p1_all_ope_profit = number_format(($p1_all_ope_profit / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķ�����'", $p1_ym);
if (getUniResult($query, $p1_c_ope_profit) < 1) {
    $p1_c_ope_profit = 0;                       // ��������
} else {
    $p1_c_ope_profit = $p1_c_ope_profit + $p1_b_sagaku + $p1_c_allo_kin - $p1_sc_uri_sagaku + $p1_sc_metarial_sagaku; // ���ץ��������̣
    if ($p1_ym == 200912) {
        $p1_c_ope_profit = $p1_c_ope_profit - 1227429;
    }
    if ($p1_ym >= 201001) {
        $p1_c_ope_profit = $p1_c_ope_profit - $p1_c_kyu_kin;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        //$p1_c_ope_profit = $p1_c_ope_profit - 151313;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    $p1_c_ope_profit_temp = $p1_c_ope_profit;
    $p1_c_ope_profit = number_format(($p1_c_ope_profit / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķ�����'", $p1_ym);
if (getUniResult($query, $p1_l_ope_profit) < 1) {
    if ($p1_ym == 200906) {
        $p1_l_ope_profit = 0 - $p1_s_ope_profit_sagaku - 3100900;   // ��������
    } elseif ($p1_ym == 200905) {
        $p1_l_ope_profit = 0 - $p1_s_ope_profit_sagaku + 1550450;   // ��������
    } elseif ($p1_ym == 200904) {
        $p1_l_ope_profit = 0 - $p1_s_ope_profit_sagaku + 1550450;   // ��������
    } else {
        $p1_l_ope_profit = 0 - $p1_s_ope_profit_sagaku;             // ��������
    }
} else {
    if ($p1_ym == 200906) {
        $p1_l_ope_profit = $p1_l_ope_profit - $p1_s_ope_profit_sagaku - 3100900 + $p1_l_allo_kin;
    } elseif ($p1_ym == 200905) {
        $p1_l_ope_profit = $p1_l_ope_profit - $p1_s_ope_profit_sagaku + 1550450 + $p1_l_allo_kin;
    } elseif ($p1_ym == 200904) {
        $p1_l_ope_profit = $p1_l_ope_profit - $p1_s_ope_profit_sagaku + 1550450 + $p1_l_allo_kin;
    } else {
        $p1_l_ope_profit = $p1_l_ope_profit - $p1_s_ope_profit_sagaku + $p1_l_allo_kin;
    }
    if ($p1_ym == 200912) {
        $p1_l_ope_profit = $p1_l_ope_profit - 182279;
    }
    if ($p1_ym >= 201001) {
        $p1_l_ope_profit = $p1_l_ope_profit - $p1_l_kyu_kin;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        //$p1_l_ope_profit = $p1_l_ope_profit - 151313;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    if ($p1_ym == 201004) {
        $p1_l_ope_profit = $p1_l_ope_profit - 255240;
    }
    $p1_l_ope_profit = number_format(($p1_l_ope_profit / $tani), $keta);
}
    ///// ������
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���αĶ�����'", $p2_ym);
if (getUniResult($query, $p2_all_ope_profit) < 1) {
    $p2_all_ope_profit = 0;                     // ��������
} else {
    if ($p2_ym == 200906) {
        $p2_all_ope_profit = $p2_all_ope_profit + $p2_b_uri_sagaku - 3100900;
    } elseif ($p2_ym == 200905) {
        $p2_all_ope_profit = $p2_all_ope_profit + $p2_b_uri_sagaku + 1550450;
    } elseif ($p2_ym == 200904) {
        $p2_all_ope_profit = $p2_all_ope_profit + $p2_b_uri_sagaku + 1550450;
    } else {
        $p2_all_ope_profit = $p2_all_ope_profit + $p2_b_uri_sagaku;
    }
    $p2_all_ope_profit = number_format(($p2_all_ope_profit / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķ�����'", $p2_ym);
if (getUniResult($query, $p2_c_ope_profit) < 1) {
    $p2_c_ope_profit = 0;                       // ��������
} else {
    $p2_c_ope_profit = $p2_c_ope_profit + $p2_b_sagaku + $p2_c_allo_kin - $p2_sc_uri_sagaku + $p2_sc_metarial_sagaku; // ���ץ��������̣
    if ($p2_ym == 200912) {
        $p2_c_ope_profit = $p2_c_ope_profit - 1227429;
    }
    if ($p2_ym >= 201001) {
        $p2_c_ope_profit = $p2_c_ope_profit - $p2_c_kyu_kin;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        //$p2_c_ope_profit = $p2_c_ope_profit - 151313;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    $p2_c_ope_profit_temp = $p2_c_ope_profit;
    $p2_c_ope_profit = number_format(($p2_c_ope_profit / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķ�����'", $p2_ym);
if (getUniResult($query, $p2_l_ope_profit) < 1) {
    if ($p2_ym == 200906) {
        $p2_l_ope_profit = 0 - $p2_s_ope_profit_sagaku - 3100900;   // ��������
    } elseif ($p2_ym == 200905) {
        $p2_l_ope_profit = 0 - $p2_s_ope_profit_sagaku + 1550450;   // ��������
    } elseif ($p2_ym == 200904) {
        $p2_l_ope_profit = 0 - $p2_s_ope_profit_sagaku + 1550450;   // ��������
    } else {
        $p2_l_ope_profit = 0 - $p2_s_ope_profit_sagaku;             // ��������
    }
} else {
    if ($p2_ym == 200906) {
        $p2_l_ope_profit = $p2_l_ope_profit - $p2_s_ope_profit_sagaku - 3100900 + $p2_l_allo_kin;
    } elseif ($p2_ym == 200905) {
        $p2_l_ope_profit = $p2_l_ope_profit - $p2_s_ope_profit_sagaku + 1550450 + $p2_l_allo_kin;
    } elseif ($p2_ym == 200904) {
        $p2_l_ope_profit = $p2_l_ope_profit - $p2_s_ope_profit_sagaku + 1550450 + $p2_l_allo_kin;
    } else {
        $p2_l_ope_profit = $p2_l_ope_profit - $p2_s_ope_profit_sagaku + $p2_l_allo_kin;
    }
    if ($p2_ym == 200912) {
        $p2_l_ope_profit = $p2_l_ope_profit - 182279;
    }
    if ($p2_ym >= 201001) {
        $p2_l_ope_profit = $p2_l_ope_profit - $p2_l_kyu_kin;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        //$p2_l_ope_profit = $p2_l_ope_profit - 151313;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    if ($p2_ym == 201004) {
        $p2_l_ope_profit = $p2_l_ope_profit - 255240;
    }
    $p2_l_ope_profit = number_format(($p2_l_ope_profit / $tani), $keta);
}
    ///// �����߷�
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���αĶ�����'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_ope_profit) < 1) {
    $rui_all_ope_profit = 0;                    // ��������
} else {
    if ($yyyymm == 200905) {
        $rui_all_ope_profit = $rui_all_ope_profit + 3100900;
    } elseif ($yyyymm == 200904) {
        $rui_all_ope_profit = $rui_all_ope_profit + 1550450;
    }
    $rui_all_ope_profit = $rui_all_ope_profit + $rui_b_uri_sagaku;
    $rui_all_ope_profit = number_format(($rui_all_ope_profit / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ�Ķ�����'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_c_ope_profit) < 1) {
    $rui_c_ope_profit = 0;                      // ��������
} else {
    $rui_c_ope_profit = $rui_c_ope_profit + $rui_b_sagaku + $rui_c_allo_kin - $rui_sc_uri_sagaku + $rui_sc_metarial_sagaku; // ���ץ��������̣
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_c_ope_profit = $rui_c_ope_profit - 1227429;
    }
    if ($yyyymm >= 201001) {
        $rui_c_ope_profit = $rui_c_ope_profit - $rui_c_kyu_kin; // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        //$rui_c_ope_profit = $rui_c_ope_profit - 151313; // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    $rui_c_ope_profit = number_format(($rui_c_ope_profit / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥��Ķ�����'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_ope_profit) < 1) {
    $rui_l_ope_profit = 0 - $rui_s_ope_profit_sagaku;   // ��������
    $rui_l_ope_profit_temp = $rui_l_ope_profit;         // �о����׷׻���
} else {
    $rui_l_ope_profit = $rui_l_ope_profit - $rui_s_ope_profit_sagaku + $rui_l_allo_kin;
    if ($yyyymm == 200905) {
        $rui_l_ope_profit = $rui_l_ope_profit + 3100900;
    } elseif ($yyyymm == 200904) {
        $rui_l_ope_profit = $rui_l_ope_profit + 1550450;
    }
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_l_ope_profit = $rui_l_ope_profit - 182279;
    }
    if ($yyyymm >= 201001) {
        $rui_l_ope_profit = $rui_l_ope_profit - $rui_l_kyu_kin; // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        //$rui_l_ope_profit = $rui_l_ope_profit - 151313; // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    if ($yyyymm >= 201004 && $yyyymm <= 201103) {
        $rui_l_ope_profit = $rui_l_ope_profit - 255240;
    }
    $rui_l_ope_profit_temp = $rui_l_ope_profit;         // �о����׷׻���
    $rui_l_ope_profit = number_format(($rui_l_ope_profit / $tani), $keta);
}

/********** �Ķȳ����פζ�̳�������� **********/
    ///// ����
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ɶ�̳���������Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $b_gyoumu) < 1) {
        $b_gyoumu      = 0;                       // ��������
        $b_gyoumu_temp = 0;
    } else {
        if ($yyyymm == 201001) {
            $b_gyoumu = $b_gyoumu + 63096;
        }
        $b_gyoumu_temp = $b_gyoumu;
    }
} else {
    $b_gyoumu     = 0;
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���̳���������Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���̳��������'", $yyyymm);
}
if (getUniResult($query, $s_gyoumu) < 1) {
    $s_gyoumu        = 0;                       // ��������
    $s_gyoumu_sagaku = 0;
} else {
    $s_gyoumu_sagaku = $s_gyoumu;
    if ($yyyymm == 200912) {
        $s_gyoumu = $s_gyoumu - 722;
    }
    if ($yyyymm == 201001) {
        $s_gyoumu = $s_gyoumu + 29125;
    }
    $s_gyoumu        = number_format(($s_gyoumu / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ζ�̳��������'", $yyyymm);
if (getUniResult($query, $all_gyoumu) < 1) {
    $all_gyoumu = 0;                            // ��������
} else {
    if ($yyyymm == 200906) {
        $all_gyoumu = $all_gyoumu + 3100900;
    } elseif ($yyyymm == 200905) {
        $all_gyoumu = $all_gyoumu - 1550450;
    } elseif ($yyyymm == 200904) {
        $all_gyoumu = $all_gyoumu - 1550450;
    }
    if ($yyyymm == 200912) {
        $all_gyoumu = $all_gyoumu - 466000;
    }
    if ($yyyymm == 201001) {
        $all_gyoumu = $all_gyoumu + 466000;
    }
    $all_gyoumu = number_format(($all_gyoumu / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��̳���������Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��̳��������'", $yyyymm);
}
if (getUniResult($query, $c_gyoumu) < 1) {
    $c_gyoumu = 0;                              // ��������
} else {
    if ($yyyymm == 200912) {
        $c_gyoumu = $c_gyoumu - 389809;
    }
    if ($yyyymm == 201001) {
        $c_gyoumu = $c_gyoumu + 315529;
    }
    $c_gyoumu = number_format(($c_gyoumu / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���̳���������Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���̳��������'", $yyyymm);
}
if (getUniResult($query, $l_gyoumu) < 1) {
    if ($yyyymm == 200906) {
        $l_gyoumu = 0 - $s_gyoumu_sagaku + 3100900;     // ��������
    } elseif ($yyyymm == 200905) {
        $l_gyoumu = 0 - $s_gyoumu_sagaku - 1550450;     // ��������
    } elseif ($yyyymm == 200904) {
        $l_gyoumu = 0 - $s_gyoumu_sagaku - 1550450;     // ��������
    } else {
        $l_gyoumu = 0 - $s_gyoumu_sagaku;               // ��������
    }
} else {
    if ($yyyymm == 200906) {
        $l_gyoumu = $l_gyoumu - $s_gyoumu_sagaku + 3100900;
    } elseif ($yyyymm == 200905) {
        $l_gyoumu = $l_gyoumu - $s_gyoumu_sagaku - 1550450;
    } elseif ($yyyymm == 200904) {
        $l_gyoumu = $l_gyoumu - $s_gyoumu_sagaku - 1550450;
    } elseif($yyyymm < 201001) {
        $l_gyoumu = $l_gyoumu - $s_gyoumu_sagaku;
    }
    if ($yyyymm == 200912) {
        $l_gyoumu = $l_gyoumu - 75469;
    }
    if ($yyyymm == 201001) {
        $l_gyoumu = $l_gyoumu + 58250;
    }
    $l_gyoumu = number_format(($l_gyoumu / $tani), $keta);
}
    ///// ����
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ɶ�̳���������Ʒ׻�'", $p1_ym);
    if (getUniResult($query, $p1_b_gyoumu) < 1) {
        $p1_b_gyoumu      = 0;                       // ��������
        $p1_b_gyoumu_temp = 0;
    } else {
        if ($p1_ym == 201001) {
            $p1_b_gyoumu = $p1_b_gyoumu + 63096;
        }
        $p1_b_gyoumu_temp = $p1_b_gyoumu;
    }
} else {
    $p1_b_gyoumu     = 0;
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���̳���������Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���̳��������'", $p1_ym);
}
if (getUniResult($query, $p1_s_gyoumu) < 1) {
    $p1_s_gyoumu        = 0;                       // ��������
    $p1_s_gyoumu_sagaku = 0;
} else {
    $p1_s_gyoumu_sagaku = $p1_s_gyoumu;
    if ($p1_ym == 200912) {
        $p1_s_gyoumu = $p1_s_gyoumu - 722;
    }
    if ($p1_ym == 201001) {
        $p1_s_gyoumu = $p1_s_gyoumu + 29125;
    }
    $p1_s_gyoumu        = number_format(($p1_s_gyoumu / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ζ�̳��������'", $p1_ym);
if (getUniResult($query, $p1_all_gyoumu) < 1) {
    $p1_all_gyoumu = 0;                         // ��������
} else {
    if ($p1_ym == 200906) {
        $p1_all_gyoumu = $p1_all_gyoumu + 3100900;
    } elseif ($p1_ym == 200905) {
        $p1_all_gyoumu = $p1_all_gyoumu - 1550450;
    } elseif ($p1_ym == 200904) {
        $p1_all_gyoumu = $p1_all_gyoumu - 1550450;
    }
    if ($p1_ym == 200912) {
        $p1_all_gyoumu = $p1_all_gyoumu - 466000;
    }
    if ($p1_ym == 201001) {
        $p1_all_gyoumu = $p1_all_gyoumu + 466000;
    }
    $p1_all_gyoumu = number_format(($p1_all_gyoumu / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��̳���������Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��̳��������'", $p1_ym);
}
if (getUniResult($query, $p1_c_gyoumu) < 1) {
    $p1_c_gyoumu = 0;                              // ��������
} else {
    if ($p1_ym == 200912) {
        $p1_c_gyoumu = $p1_c_gyoumu - 389809;
    }
    if ($p1_ym == 201001) {
        $p1_c_gyoumu = $p1_c_gyoumu + 315529;
    }
    $p1_c_gyoumu = number_format(($p1_c_gyoumu / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���̳���������Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���̳��������'", $p1_ym);
}
if (getUniResult($query, $p1_l_gyoumu) < 1) {
    if ($p1_ym == 200906) {
        $p1_l_gyoumu = 0 - $p1_s_gyoumu_sagaku + 3100900;     // ��������
    } elseif ($p1_ym == 200905) {
        $p1_l_gyoumu = 0 - $p1_s_gyoumu_sagaku - 1550450;     // ��������
    } elseif ($p1_ym == 200904) {
        $p1_l_gyoumu = 0 - $p1_s_gyoumu_sagaku - 1550450;     // ��������
    } else {
        $p1_l_gyoumu = 0 - $p1_s_gyoumu_sagaku;               // ��������
    }
} else {
    if ($p1_ym == 200906) {
        $p1_l_gyoumu = $p1_l_gyoumu - $p1_s_gyoumu_sagaku + 3100900;
    } elseif ($p1_ym == 200905) {
        $p1_l_gyoumu = $p1_l_gyoumu - $p1_s_gyoumu_sagaku - 1550450;
    } elseif ($p1_ym == 200904) {
        $p1_l_gyoumu = $p1_l_gyoumu - $p1_s_gyoumu_sagaku - 1550450;
    } elseif($p1_ym < 201001) {
        $p1_l_gyoumu = $p1_l_gyoumu - $p1_s_gyoumu_sagaku;
    }
    if ($p1_ym == 200912) {
        $p1_l_gyoumu = $p1_l_gyoumu - 75469;
    }
    if ($p1_ym == 201001) {
        $p1_l_gyoumu = $p1_l_gyoumu + 58250;
    }
    $p1_l_gyoumu = number_format(($p1_l_gyoumu / $tani), $keta);
}
    ///// ������
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ɶ�̳���������Ʒ׻�'", $p2_ym);
    if (getUniResult($query, $p2_b_gyoumu) < 1) {
        $p2_b_gyoumu      = 0;                       // ��������
        $p2_b_gyoumu_temp = 0;
    } else {
        if ($p2_ym == 201001) {
            $p2_b_gyoumu = $p2_b_gyoumu + 63096;
        }
        $p2_b_gyoumu_temp = $p2_b_gyoumu;
    }
} else {
    $p2_b_gyoumu     = 0;
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���̳���������Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���̳��������'", $p2_ym);
}
if (getUniResult($query, $p2_s_gyoumu) < 1) {
    $p2_s_gyoumu        = 0;                       // ��������
    $p2_s_gyoumu_sagaku = 0;
} else {
    $p2_s_gyoumu_sagaku = $p2_s_gyoumu;
    if ($p2_ym == 200912) {
        $p2_s_gyoumu = $p2_s_gyoumu - 722;
    }
    if ($p2_ym == 201001) {
        $p2_s_gyoumu = $p2_s_gyoumu + 29125;
    }
    $p2_s_gyoumu        = number_format(($p2_s_gyoumu / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ζ�̳��������'", $p2_ym);
if (getUniResult($query, $p2_all_gyoumu) < 1) {
    $p2_all_gyoumu = 0;   // ��������
} else {
    if ($p2_ym == 200906) {
        $p2_all_gyoumu = $p2_all_gyoumu + 3100900;
    } elseif ($p2_ym == 200905) {
        $p2_all_gyoumu = $p2_all_gyoumu - 1550450;
    } elseif ($p2_ym == 200904) {
        $p2_all_gyoumu = $p2_all_gyoumu - 1550450;
    }
    if ($p2_ym == 200912) {
        $p2_all_gyoumu = $p2_all_gyoumu - 466000;
    }
    if ($p2_ym == 201001) {
        $p2_all_gyoumu = $p2_all_gyoumu + 466000;
    }
    $p2_all_gyoumu = number_format(($p2_all_gyoumu / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��̳���������Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��̳��������'", $p2_ym);
}
if (getUniResult($query, $p2_c_gyoumu) < 1) {
    $p2_c_gyoumu = 0;                              // ��������
} else {
    if ($p2_ym == 200912) {
        $p2_c_gyoumu = $p2_c_gyoumu - 389809;
    }
    if ($p2_ym == 201001) {
        $p2_c_gyoumu = $p2_c_gyoumu + 315529;
    }
    $p2_c_gyoumu = number_format(($p2_c_gyoumu / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���̳���������Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���̳��������'", $p2_ym);
}
if (getUniResult($query, $p2_l_gyoumu) < 1) {
    if ($p2_ym == 200906) {
        $p2_l_gyoumu = 0 - $p2_s_gyoumu_sagaku + 3100900;     // ��������
    } elseif ($p2_ym == 200905) {
        $p2_l_gyoumu = 0 - $p2_s_gyoumu_sagaku - 1550450;     // ��������
    } elseif ($p2_ym == 200904) {
        $p2_l_gyoumu = 0 - $p2_s_gyoumu_sagaku - 1550450;     // ��������
    } else {
        $p2_l_gyoumu = 0 - $p2_s_gyoumu_sagaku;               // ��������
    }
} else {
    if ($p2_ym == 200906) {
        $p2_l_gyoumu = $p2_l_gyoumu - $p2_s_gyoumu_sagaku + 3100900;
    } elseif ($p1_ym == 200905) {
        $p2_l_gyoumu = $p2_l_gyoumu - $p2_s_gyoumu_sagaku - 1550450;
    } elseif ($p1_ym == 200904) {
        $p2_l_gyoumu = $p2_l_gyoumu - $p2_s_gyoumu_sagaku - 1550450;
    } elseif($p2_ym < 201001) {
        $p2_l_gyoumu = $p2_l_gyoumu - $p2_s_gyoumu_sagaku;
    }
    if ($p2_ym == 200912) {
        $p2_l_gyoumu = $p2_l_gyoumu - 75469;
    }
    if ($p2_ym == 201001) {
        $p2_l_gyoumu = $p2_l_gyoumu + 58250;
    }
    $p2_l_gyoumu = number_format(($p2_l_gyoumu / $tani), $keta);
}
    ///// �����߷�
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ɶ�̳���������Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_gyoumu) < 1) {
        $rui_b_gyoumu = 0;                          // ��������
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $rui_b_gyoumu_a = 0;
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='���ɶ�̳���������Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_b_gyoumu_b) < 1) {
        $rui_b_gyoumu_b = 0;                          // ��������
    }
    $rui_b_gyoumu = $rui_b_gyoumu_a + $rui_b_gyoumu_b;
    $rui_b_gyoumu = $rui_b_gyoumu + 63096;
} else {
    $rui_b_gyoumu = 0;
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���̳���������Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_gyoumu) < 1) {
        $rui_s_gyoumu = 0;                          // ��������
    } else {
        //$rui_s_gyoumu_sagaku = $rui_s_gyoumu;
        $rui_s_gyoumu = number_format(($rui_s_gyoumu / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='���̳��������'");
    if (getUniResult($query, $rui_s_gyoumu_a) < 1) {
        $rui_s_gyoumu_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='���̳���������Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_s_gyoumu_b) < 1) {
        $rui_s_gyoumu_b = 0;                          // ��������
    }
    $rui_s_gyoumu = $rui_s_gyoumu_a + $rui_s_gyoumu_b;
    
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_s_gyoumu = $rui_s_gyoumu - 722;
    }
    if ($yyyymm >= 201001 && $yyyymm <= 201003) {
        $rui_s_gyoumu = $rui_s_gyoumu + 29125;
    }
    $rui_s_gyoumu_sagaku = $rui_s_gyoumu;
    $rui_s_gyoumu = number_format(($rui_s_gyoumu / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���̳��������'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_gyoumu) < 1) {
        $rui_s_gyoumu        = 0;                   // ��������
        $rui_s_gyoumu_sagaku = 0;
    } else {
        $rui_s_gyoumu_sagaku = $rui_s_gyoumu;
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_s_gyoumu = $rui_s_gyoumu - 722;
        }
        if ($yyyymm >= 201001 && $yyyymm <= 201003) {
            $rui_s_gyoumu = $rui_s_gyoumu + 29125;
        }
        $rui_s_gyoumu = number_format(($rui_s_gyoumu / $tani), $keta);
    }
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ζ�̳��������'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_gyoumu) < 1) {
    $rui_all_gyoumu = 0;                        // ��������
} else {
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_all_gyoumu = $rui_all_gyoumu - 466000;
    }
    if ($yyyymm >= 201001 && $yyyymm <= 201003) {
        $rui_all_gyoumu = $rui_all_gyoumu + 466000;
    }
    $rui_all_gyoumu = number_format(($rui_all_gyoumu / $tani), $keta);
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ��̳���������Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_gyoumu) < 1) {
        $rui_c_gyoumu = 0;                          // ��������
    } else {
        $rui_c_gyoumu = number_format(($rui_c_gyoumu / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='���ץ��̳��������'");
    if (getUniResult($query, $rui_c_gyoumu_a) < 1) {
        $rui_c_gyoumu_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='���ץ��̳���������Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_c_gyoumu_b) < 1) {
        $rui_c_gyoumu_b = 0;                          // ��������
    }
    $rui_c_gyoumu = $rui_c_gyoumu_a + $rui_c_gyoumu_b;
    
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_c_gyoumu = $rui_c_gyoumu - 389809;
    }
    if ($yyyymm >= 201001 && $yyyymm <= 201003) {
        $rui_c_gyoumu = $rui_c_gyoumu + 315529;
    }
    $rui_c_gyoumu = number_format(($rui_c_gyoumu / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ��̳��������'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_gyoumu) < 1) {
        $rui_c_gyoumu = 0;                          // ��������
    } else {
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_c_gyoumu = $rui_c_gyoumu - 389809;
        }
        if ($yyyymm >= 201001 && $yyyymm <= 201003) {
            $rui_c_gyoumu = $rui_c_gyoumu + 315529;
        }
        $rui_c_gyoumu = number_format(($rui_c_gyoumu / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥���̳���������Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_gyoumu) < 1) {
        $rui_l_gyoumu = 0;                          // ��������
    } else {
        $rui_l_gyoumu = number_format(($rui_l_gyoumu / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='��˥���̳��������'");
    if (getUniResult($query, $rui_l_gyoumu_a) < 1) {
        $rui_l_gyoumu_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='��˥���̳���������Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_l_gyoumu_b) < 1) {
        $rui_l_gyoumu_b = 0;                          // ��������
    }
    $rui_l_gyoumu = $rui_l_gyoumu_a + $rui_l_gyoumu_b;
    
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_l_gyoumu = $rui_l_gyoumu - 75469;
    }
    if ($yyyymm >= 201001 && $yyyymm <= 201003) {
        $rui_l_gyoumu = $rui_l_gyoumu + 58250;
    }
    $rui_l_gyoumu = $rui_l_gyoumu - $rui_s_gyoumu_a;
    $rui_l_gyoumu = number_format(($rui_l_gyoumu / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥���̳��������'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_gyoumu) < 1) {
        $rui_l_gyoumu = 0 - $rui_s_gyoumu_sagaku;   // ��������
    } else {
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_l_gyoumu = $rui_l_gyoumu - 75469;
        }
        if ($yyyymm >= 201001 && $yyyymm <= 201003) {
            $rui_l_gyoumu = $rui_l_gyoumu + 58250;
        }
        $rui_l_gyoumu = $rui_l_gyoumu - $rui_s_gyoumu_sagaku;
        $rui_l_gyoumu = number_format(($rui_l_gyoumu / $tani), $keta);
    }
}
/********** �Ķȳ����פλ������ **********/
    ///// ����
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ɻ�������Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $b_swari) < 1) {
        $b_swari        = 0;                        // ��������
        $b_swari_temp = 0;
    } else {
        $b_swari_temp = $b_swari;
    }
} else {
    $b_swari     = 0;
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���������Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��������'", $yyyymm);
}
if (getUniResult($query, $s_swari) < 1) {
    $s_swari        = 0;                        // ��������
    $s_swari_sagaku = 0;
} else {
    $s_swari_sagaku = $s_swari;
    $s_swari        = number_format(($s_swari / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���λ������'", $yyyymm);
if (getUniResult($query, $all_swari) < 1) {
    $all_swari = 0;                             // ��������
} else {
    $all_swari = number_format(($all_swari / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��������Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�������'", $yyyymm);
}
if (getUniResult($query, $c_swari) < 1) {
    $c_swari = 0;                               // ��������
} else {
    $c_swari = number_format(($c_swari / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���������Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��������'", $yyyymm);
}
if (getUniResult($query, $l_swari) < 1) {
    if ($yyyymm < 201001) {
        $l_swari = 0 - $s_swari_sagaku;             // ��������
    } else {
        $l_swari = 0;             // ��������
    }
} else {
    if ($yyyymm < 201001) {
        $l_swari = $l_swari - $s_swari_sagaku;
    }
    $l_swari = number_format(($l_swari / $tani), $keta);
}
    ///// ����
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ɻ�������Ʒ׻�'", $p1_ym);
    if (getUniResult($query, $p1_b_swari) < 1) {
        $p1_b_swari        = 0;                        // ��������
        $p1_b_swari_temp = 0;
    } else {
        $p1_b_swari_temp = $p1_b_swari;
    }
} else {
    $p1_b_swari     = 0;
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���������Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��������'", $p1_ym);
}
if (getUniResult($query, $p1_s_swari) < 1) {
    $p1_s_swari        = 0;                        // ��������
    $p1_s_swari_sagaku = 0;
} else {
    $p1_s_swari_sagaku = $p1_s_swari;
    $p1_s_swari        = number_format(($p1_s_swari / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���λ������'", $p1_ym);
if (getUniResult($query, $p1_all_swari) < 1) {
    $p1_all_swari = 0;                          // ��������
} else {
    $p1_all_swari = number_format(($p1_all_swari / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��������Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�������'", $p1_ym);
}
if (getUniResult($query, $p1_c_swari) < 1) {
    $p1_c_swari = 0;                               // ��������
} else {
    $p1_c_swari = number_format(($p1_c_swari / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���������Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��������'", $p1_ym);
}
if (getUniResult($query, $p1_l_swari) < 1) {
    if ($p1_ym < 201001) {
        $p1_l_swari = 0 - $p1_s_swari_sagaku;             // ��������
    } else {
        $p1_l_swari = 0;             // ��������
    }
} else {
    if ($p1_ym < 201001) {
        $p1_l_swari = $p1_l_swari - $p1_s_swari_sagaku;
    }
    $p1_l_swari = number_format(($p1_l_swari / $tani), $keta);
}
    ///// ������
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ɻ�������Ʒ׻�'", $p2_ym);
    if (getUniResult($query, $p2_b_swari) < 1) {
        $p2_b_swari        = 0;                        // ��������
        $p2_b_swari_temp = 0;
    } else {
        $p2_b_swari_temp = $p2_b_swari;
    }
} else {
    $p2_b_swari     = 0;
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���������Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��������'", $p2_ym);
}
if (getUniResult($query, $p2_s_swari) < 1) {
    $p2_s_swari        = 0;                        // ��������
    $p2_s_swari_sagaku = 0;
} else {
    $p2_s_swari_sagaku = $p2_s_swari;
    $p2_s_swari        = number_format(($p2_s_swari / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���λ������'", $p2_ym);
if (getUniResult($query, $p2_all_swari) < 1) {
    $p2_all_swari = 0;                          // ��������
} else {
    $p2_all_swari = number_format(($p2_all_swari / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��������Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�������'", $p2_ym);
}
if (getUniResult($query, $p2_c_swari) < 1) {
    $p2_c_swari = 0;                               // ��������
} else {
    $p2_c_swari = number_format(($p2_c_swari / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���������Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��������'", $p2_ym);
}
if (getUniResult($query, $p2_l_swari) < 1) {
    if ($p2_ym < 201001) {
        $p2_l_swari = 0 - $p2_s_swari_sagaku;             // ��������
    } else {
        $p2_l_swari = 0;             // ��������
    }
} else {
    if ($p2_ym < 201001) {
        $p2_l_swari = $p2_l_swari - $p2_s_swari_sagaku;
    }
    $p2_l_swari = number_format(($p2_l_swari / $tani), $keta);
}
    ///// �����߷�
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ɻ�������Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_swari) < 1) {
        $rui_b_swari = 0;                           // ��������
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $rui_b_swari_a = 0;
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='���ɻ�������Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_b_swari_b) < 1) {
        $rui_b_swari_b = 0;                          // ��������
    }
    $rui_b_swari = $rui_b_swari_a + $rui_b_swari_b;
} else {
    $rui_b_swari = 0;
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���������Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_swari) < 1) {
        $rui_s_swari = 0;                           // ��������
    } else {
        //$rui_s_swari_sagaku = $rui_s_swari;
        $rui_s_swari = number_format(($rui_s_swari / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='��������'");
    if (getUniResult($query, $rui_s_swari_a) < 1) {
        $rui_s_swari_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='���������Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_s_swari_b) < 1) {
        $rui_s_swari_b = 0;                          // ��������
    }
    $rui_s_swari = $rui_s_swari_a + $rui_s_swari_b;
    $rui_s_swari_sagaku = $rui_s_swari;
    $rui_s_swari = number_format(($rui_s_swari / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��������'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_swari) < 1) {
        $rui_s_swari        = 0;                    // ��������
        $rui_s_swari_sagaku = 0;
    } else {
        $rui_s_swari_sagaku = $rui_s_swari;
        $rui_s_swari = number_format(($rui_s_swari / $tani), $keta);
    }
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���λ������'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_swari) < 1) {
    $rui_all_swari = 0;                         // ��������
} else {
    $rui_all_swari = number_format(($rui_all_swari / $tani), $keta);
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ��������Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_swari) < 1) {
        $rui_c_swari = 0;                           // ��������
    } else {
        $rui_c_swari = number_format(($rui_c_swari / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='���ץ�������'");
    if (getUniResult($query, $rui_c_swari_a) < 1) {
        $rui_c_swari_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='���ץ��������Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_c_swari_b) < 1) {
        $rui_c_swari_b = 0;                          // ��������
    }
    $rui_c_swari = $rui_c_swari_a + $rui_c_swari_b;
    $rui_c_swari = number_format(($rui_c_swari / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ�������'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_swari) < 1) {
        $rui_c_swari = 0;                           // ��������
    } else {
        $rui_c_swari = number_format(($rui_c_swari / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥���������Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_swari) < 1) {
        $rui_l_swari = 0;                           // ��������
    } else {
        $rui_l_swari = number_format(($rui_l_swari / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='��˥��������'");
    if (getUniResult($query, $rui_l_swari_a) < 1) {
        $rui_l_swari_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='��˥���������Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_l_swari_b) < 1) {
        $rui_l_swari_b = 0;                          // ��������
    }
    $rui_l_swari = $rui_l_swari_a + $rui_l_swari_b;
    $rui_l_swari = $rui_l_swari - $rui_s_swari_a;
    $rui_l_swari = number_format(($rui_l_swari / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥��������'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_swari) < 1) {
        $rui_l_swari = 0;                           // ��������
    } else {
        $rui_l_swari = $rui_l_swari - $rui_s_swari_sagaku;
        $rui_l_swari = number_format(($rui_l_swari / $tani), $keta);
    }
}
/********** �Ķȳ����פΤ���¾ **********/
    ///// ����
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��Ķȳ����פ���¾�Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��Ķȳ����פ���¾'", $yyyymm);
}
if (getUniResult($query, $s_pother) < 1) {
    $s_pother        = 0;                       // ��������
    $s_pother_sagaku = 0;
} else {
    $s_pother_sagaku = $s_pother;
    if ($yyyymm == 200912) {
        $s_pother = $s_pother + 722;
    }
    if ($yyyymm == 201001) {
        $s_pother = $s_pother - 29125;
    }
    $s_pother        = number_format(($s_pother / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ɱĶȳ����פ���¾�Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ɱĶȳ����פ���¾'", $yyyymm);
}
if (getUniResult($query, $b_pother) < 1) {
    $b_pother = 0;                              // ��������
    $b_sagaku = $b_sagaku - $b_pother;          // ���ץ麹�۷׻���
} else {
    if ($yyyymm == 201001) {
        $b_pother = $b_pother - 63096;
    }
    $b_sagaku = $b_sagaku - $b_pother;          // ���ץ麹�۷׻���
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���αĶȳ����פ���¾'", $yyyymm);
if (getUniResult($query, $all_pother) < 1) {
    $all_pother = 0;                            // ��������
} else {
    if ($yyyymm == 200912) {
        $all_pother = $all_pother + 466000;
    }
    if ($yyyymm == 201001) {
        $all_pother = $all_pother - 466000;
    }
    if ($yyyymm == 201002) {
        $all_pother = $all_pother + 600000;
    }
    if ($yyyymm == 201003) {
        $all_pother = $all_pother - 600000;
    }
    $all_pother = number_format(($all_pother / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����פ���¾�Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����פ���¾'", $yyyymm);
}
if (getUniResult($query, $c_pother) < 1) {
    $c_pother = 0;                              // ��������
} else {
    if ($yyyymm < 201001) {
        $c_pother = $c_pother - $b_pother;
    }
    if ($yyyymm == 200912) {
        $c_pother = $c_pother + 389809;
    }
    if ($yyyymm == 201001) {
        $c_pother = $c_pother - 315529;
    }
    $c_pother = number_format(($c_pother / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķȳ����פ���¾�Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķȳ����פ���¾'", $yyyymm);
}
if (getUniResult($query, $l_pother) < 1) {
    if ($yyyymm < 201001) {
        $l_pother = 0 - $s_pother_sagaku;           // ��������
    } else {
        $l_pother = 0;           // ��������
    }
} else {
    if ($yyyymm < 201001) {
        $l_pother = $l_pother - $s_pother_sagaku;
    }
    if ($yyyymm == 200912) {
        $l_pother = $l_pother + 75469;
    }
    if ($yyyymm == 201001) {
        $l_pother = $l_pother - 58250;
    }
    $l_pother = number_format(($l_pother / $tani), $keta);
}
    ///// ����
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��Ķȳ����פ���¾�Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��Ķȳ����פ���¾'", $p1_ym);
}
if (getUniResult($query, $p1_s_pother) < 1) {
    $p1_s_pother        = 0;                       // ��������
    $p1_s_pother_sagaku = 0;
} else {
    $p1_s_pother_sagaku = $p1_s_pother;
    if ($p1_ym == 200912) {
        $p1_s_pother = $p1_s_pother + 722;
    }
    if ($p1_ym == 201001) {
        $p1_s_pother = $p1_s_pother - 29125;
    }
    $p1_s_pother        = number_format(($p1_s_pother / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ɱĶȳ����פ���¾�Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ɱĶȳ����פ���¾'", $p1_ym);
}
if (getUniResult($query, $p1_b_pother) < 1) {
    $p1_b_pother = 0;                              // ��������
    $p1_b_sagaku = $p1_b_sagaku - $p1_b_pother;          // ���ץ麹�۷׻���
} else {
    if ($p1_ym == 201001) {
        $p1_b_pother = $p1_b_pother - 63096;
    }
    $p1_b_sagaku = $p1_b_sagaku - $p1_b_pother;          // ���ץ麹�۷׻���
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���αĶȳ����פ���¾'", $p1_ym);
if (getUniResult($query, $p1_all_pother) < 1) {
    $p1_all_pother = 0;                         // ��������
} else {
    if ($p1_ym == 200912) {
        $p1_all_pother = $p1_all_pother + 466000;
    }
    if ($p1_ym == 201001) {
        $p1_all_pother = $p1_all_pother - 466000;
    }
    if ($p1_ym == 201002) {
        $p1_all_pother = $p1_all_pother + 600000;
    }
    if ($p1_ym == 201003) {
        $p1_all_pother = $p1_all_pother - 600000;
    }
    $p1_all_pother = number_format(($p1_all_pother / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����פ���¾�Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����פ���¾'", $p1_ym);
}
if (getUniResult($query, $p1_c_pother) < 1) {
    $p1_c_pother = 0;                              // ��������
} else {
    if ($p1_ym < 201001) {
        $p1_c_pother = $p1_c_pother - $p1_b_pother;
    }
    if ($p1_ym == 200912) {
        $p1_c_pother = $p1_c_pother + 389809;
    }
    if ($p1_ym == 201001) {
        $p1_c_pother = $p1_c_pother - 315529;
    }
    $p1_c_pother = number_format(($p1_c_pother / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķȳ����פ���¾�Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķȳ����פ���¾'", $p1_ym);
}
if (getUniResult($query, $p1_l_pother) < 1) {
    if ($p1_ym < 201001) {
        $p1_l_pother = 0 - $p1_s_pother_sagaku;           // ��������
    } else {
        $p1_l_pother = 0;           // ��������
    }
} else {
    if ($p1_ym < 201001) {
        $p1_l_pother = $p1_l_pother - $p1_s_pother_sagaku;
    }
    if ($p1_ym == 200912) {
        $p1_l_pother = $p1_l_pother + 75469;
    }
    if ($p1_ym == 201001) {
        $p1_l_pother = $p1_l_pother - 58250;
    }
    $p1_l_pother = number_format(($p1_l_pother / $tani), $keta);
}
    ///// ������
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��Ķȳ����פ���¾�Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��Ķȳ����פ���¾'", $p2_ym);
}
if (getUniResult($query, $p2_s_pother) < 1) {
    $p2_s_pother        = 0;                       // ��������
    $p2_s_pother_sagaku = 0;
} else {
    $p2_s_pother_sagaku = $p2_s_pother;
    if ($p2_ym == 200912) {
        $p2_s_pother = $p2_s_pother + 722;
    }
    if ($p2_ym == 201001) {
        $p2_s_pother = $p2_s_pother - 29125;
    }
    $p2_s_pother        = number_format(($p2_s_pother / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ɱĶȳ����פ���¾�Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ɱĶȳ����פ���¾'", $p2_ym);
}
if (getUniResult($query, $p2_b_pother) < 1) {
    $p2_b_pother = 0;                              // ��������
    $p2_b_sagaku = $p2_b_sagaku - $p2_b_pother;          // ���ץ麹�۷׻���
} else {
    if ($p2_ym == 201001) {
        $p2_b_pother = $p2_b_pother - 63096;
    }
    $p2_b_sagaku = $p2_b_sagaku - $p2_b_pother;          // ���ץ麹�۷׻���
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���αĶȳ����פ���¾'", $p2_ym);
if (getUniResult($query, $p2_all_pother) < 1) {
    $p2_all_pother = 0;                         // ��������
} else {
    if ($p2_ym == 200912) {
        $p2_all_pother = $p2_all_pother + 466000;
    }
    if ($p2_ym == 201001) {
        $p2_all_pother = $p2_all_pother - 466000;
    }
    if ($p2_ym == 201002) {
        $p2_all_pother = $p2_all_pother + 600000;
    }
    if ($p2_ym == 201003) {
        $p2_all_pother = $p2_all_pother - 600000;
    }
    $p2_all_pother = number_format(($p2_all_pother / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����פ���¾�Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����פ���¾'", $p2_ym);
}
if (getUniResult($query, $p2_c_pother) < 1) {
    $p2_c_pother = 0;                              // ��������
} else {
    if ($p2_ym < 201001) {
        $p2_c_pother = $p2_c_pother - $p2_b_pother;
    }
    if ($p2_ym == 200912) {
        $p2_c_pother = $p2_c_pother + 389809;
    }
    if ($p2_ym == 201001) {
        $p2_c_pother = $p2_c_pother - 315529;
    }
    $p2_c_pother = number_format(($p2_c_pother / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķȳ����פ���¾�Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķȳ����פ���¾'", $p2_ym);
}
if (getUniResult($query, $p2_l_pother) < 1) {
    if ($p2_ym < 201001) {
        $p2_l_pother = 0 - $p2_s_pother_sagaku;           // ��������
    } else {
        $p2_l_pother = 0;           // ��������
    }
} else {
    if ($p2_ym < 201001) {
        $p2_l_pother = $p2_l_pother - $p2_s_pother_sagaku;
    }
    if ($p2_ym == 200912) {
        $p2_l_pother = $p2_l_pother + 75469;
    }
    if ($p2_ym == 201001) {
        $p2_l_pother = $p2_l_pother - 58250;
    }
    $p2_l_pother = number_format(($p2_l_pother / $tani), $keta);
}
    ///// �����߷�
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ɱĶȳ����פ���¾�Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_pother) < 1) {
        $rui_b_pother = 0;                          // ��������
    } else {
        //$rui_b_pother_sagaku = $rui_b_pother;
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='���ɱĶȳ����פ���¾'");
    if (getUniResult($query, $rui_b_pother_a) < 1) {
        $rui_b_pother_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='���ɱĶȳ����פ���¾�Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_b_pother_b) < 1) {
        $rui_b_pother_b = 0;                          // ��������
    }
    $rui_b_pother = $rui_b_pother_a + $rui_b_pother_b;
    $rui_b_pother = $rui_b_pother - 63096;
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ɱĶȳ����פ���¾'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_pother) < 1) {
        $rui_b_pother = 0;                          // ��������
        $rui_b_sagaku = $rui_b_sagaku - $rui_b_pother;      // ���ץ麹�۷׻���
    } else {
        $rui_b_sagaku = $rui_b_sagaku - $rui_b_pother;      // ���ץ麹�۷׻���
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��Ķȳ����פ���¾�Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_pother) < 1) {
        $rui_s_pother = 0;                          // ��������
    } else {
        //$rui_s_pother_sagaku = $rui_s_pother;
        $rui_s_pother = number_format(($rui_s_pother / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='��Ķȳ����פ���¾'");
    if (getUniResult($query, $rui_s_pother_a) < 1) {
        $rui_s_pother_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='��Ķȳ����פ���¾�Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_s_pother_b) < 1) {
        $rui_s_pother_b = 0;                          // ��������
    }
    $rui_s_pother = $rui_s_pother_a + $rui_s_pother_b;
    
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_s_pother = $rui_s_pother + 722;
    }
    if ($yyyymm >= 201001 && $yyyymm <= 201003) {
        $rui_s_pother = $rui_s_pother - 29125;
    }
    $rui_s_pother_sagaku = $rui_s_pother;
    $rui_s_pother = number_format(($rui_s_pother / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��Ķȳ����פ���¾'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_pother) < 1) {
        $rui_s_pother        = 0;                   // ��������
        $rui_s_pother_sagaku = 0;
    } else {
        $rui_s_pother_sagaku = $rui_s_pother;
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_s_pother = $rui_s_pother + 722;
        }
        if ($yyyymm >= 201001 && $yyyymm <= 201003) {
            $rui_s_pother = $rui_s_pother - 29125;
        }
        $rui_s_pother = number_format(($rui_s_pother / $tani), $keta);
    }
}

$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���αĶȳ����פ���¾'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_pother) < 1) {
    $rui_all_pother = 0;                        // ��������
} else {
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_all_pother = $rui_all_pother + 466000;
    }
    if ($yyyymm >= 201001 && $yyyymm <= 201003) {
        $rui_all_pother = $rui_all_pother - 466000;
    }
    if ($yyyymm >= 201002 && $yyyymm <= 201003) {
        $rui_all_pother = $rui_all_pother + 600000;
    }
    if ($yyyymm == 201003) {
        $rui_all_pother = $rui_all_pother - 600000;
    }
    $rui_all_pother = number_format(($rui_all_pother / $tani), $keta);
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ�Ķȳ����פ���¾�Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_pother) < 1) {
        $rui_c_pother = 0;                          // ��������
    } else {
        $rui_c_pother = number_format(($rui_c_pother / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='���ץ�Ķȳ����פ���¾'");
    if (getUniResult($query, $rui_c_pother_a) < 1) {
        $rui_c_pother_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='���ץ�Ķȳ����פ���¾�Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_c_pother_b) < 1) {
        $rui_c_pother_b = 0;                          // ��������
    }
    $rui_c_pother = $rui_c_pother_a + $rui_c_pother_b;
    
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_c_pother = $rui_c_pother + 389809;
    }
    if ($yyyymm >= 201001 && $yyyymm <= 201003) {
        $rui_c_pother = $rui_c_pother - 315529;
    }
    $rui_c_pother = $rui_c_pother - $rui_b_pother_a;
    $rui_c_pother = number_format(($rui_c_pother / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ�Ķȳ����פ���¾'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_pother) < 1) {
        $rui_c_pother = 0;                          // ��������
    } else {
        $rui_c_pother = $rui_c_pother - $rui_b_pother;
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_c_pother = $rui_c_pother + 389809;
        }
        if ($yyyymm >= 201001 && $yyyymm <= 201003) {
            $rui_c_pother = $rui_c_pother - 315529;
        }
        $rui_c_pother = number_format(($rui_c_pother / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥��Ķȳ����פ���¾�Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_pother) < 1) {
        $rui_l_pother = 0;                          // ��������
    } else {
        $rui_l_pother = number_format(($rui_l_pother / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='��˥��Ķȳ����פ���¾'");
    if (getUniResult($query, $rui_l_pother_a) < 1) {
        $rui_l_pother_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='��˥��Ķȳ����פ���¾�Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_l_pother_b) < 1) {
        $rui_l_pother_b = 0;                          // ��������
    }
    $rui_l_pother = $rui_l_pother_a + $rui_l_pother_b;
    
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_l_pother = $rui_l_pother + 75469;
    }
    if ($yyyymm >= 201001 && $yyyymm <= 201003) {
        $rui_l_pother = $rui_l_pother - 58250;
    }
    $rui_l_pother = $rui_l_pother - $rui_s_pother_a;
    $rui_l_pother = number_format(($rui_l_pother / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥��Ķȳ����פ���¾'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_pother) < 1) {
        $rui_l_pother = 0 - $rui_s_pother_sagaku;   // ��������
    } else {
        $rui_l_pother = $rui_l_pother - $rui_s_pother_sagaku;
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_l_pother = $rui_l_pother + 75469;
        }
        if ($yyyymm >= 201001 && $yyyymm <= 201003) {
            $rui_l_pother = $rui_l_pother - 58250;
        }
        $rui_l_pother = number_format(($rui_l_pother / $tani), $keta);
    }
}
/********** �Ķȳ����פι�� **********/
    ///// ����
$p2_b_nonope_profit_sum  = $p2_b_gyoumu + $p2_b_swari + $p2_b_pother;
$p2_b_gyoumu             = number_format(($p2_b_gyoumu / $tani), $keta);
$p2_b_swari              = number_format(($p2_b_swari / $tani), $keta);
$p2_b_pother             = number_format(($p2_b_pother / $tani), $keta);

$p1_b_nonope_profit_sum  = $p1_b_gyoumu + $p1_b_swari + $p1_b_pother;
$p1_b_gyoumu             = number_format(($p1_b_gyoumu / $tani), $keta);
$p1_b_swari              = number_format(($p1_b_swari / $tani), $keta);
$p1_b_pother             = number_format(($p1_b_pother / $tani), $keta);

$b_nonope_profit_sum     = $b_gyoumu + $b_swari + $b_pother;
$b_gyoumu                = number_format(($b_gyoumu / $tani), $keta);
$b_swari                 = number_format(($b_swari / $tani), $keta);
$b_pother                = number_format(($b_pother / $tani), $keta);

$rui_b_nonope_profit_sum = $rui_b_gyoumu + $rui_b_swari + $rui_b_pother;
$rui_b_gyoumu            = number_format(($rui_b_gyoumu / $tani), $keta);
$rui_b_swari             = number_format(($rui_b_swari / $tani), $keta);
$rui_b_pother            = number_format(($rui_b_pother / $tani), $keta);
    ///// �������
$p2_s_nonope_profit_sum         = $p2_s_gyoumu_sagaku + $p2_s_swari_sagaku + $p2_s_pother_sagaku;
$p2_s_nonope_profit_sum_sagaku  = $p2_s_nonope_profit_sum;
$p2_s_nonope_profit_sum         = number_format(($p2_s_nonope_profit_sum / $tani), $keta);

$p1_s_nonope_profit_sum         = $p1_s_gyoumu_sagaku + $p1_s_swari_sagaku + $p1_s_pother_sagaku;
$p1_s_nonope_profit_sum_sagaku  = $p1_s_nonope_profit_sum;
$p1_s_nonope_profit_sum         = number_format(($p1_s_nonope_profit_sum / $tani), $keta);

$s_nonope_profit_sum            = $s_gyoumu_sagaku + $s_swari_sagaku + $s_pother_sagaku;
$s_nonope_profit_sum_sagaku     = $s_nonope_profit_sum;
$s_nonope_profit_sum            = number_format(($s_nonope_profit_sum / $tani), $keta);

$rui_s_nonope_profit_sum        = $rui_s_gyoumu_sagaku + $rui_s_swari_sagaku + $rui_s_pother_sagaku;
$rui_s_nonope_profit_sum_sagaku = $rui_s_nonope_profit_sum;
$rui_s_nonope_profit_sum        = number_format(($rui_s_nonope_profit_sum / $tani), $keta);

    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���αĶȳ����׷�'", $yyyymm);
if (getUniResult($query, $all_nonope_profit_sum) < 1) {
    $all_nonope_profit_sum = 0;                 // ��������
} else {
    if ($yyyymm == 200906) {
        $all_nonope_profit_sum = $all_nonope_profit_sum + 3100900;
    } elseif ($yyyymm == 200905) {
        $all_nonope_profit_sum = $all_nonope_profit_sum - 1550450;
    } elseif ($yyyymm == 200904) {
        $all_nonope_profit_sum = $all_nonope_profit_sum - 1550450;
    }
    if ($yyyymm == 201002) {
        $all_nonope_profit_sum = $all_nonope_profit_sum + 600000;
    }
    if ($yyyymm == 201003) {
        $all_nonope_profit_sum = $all_nonope_profit_sum - 600000;
    }
    $all_nonope_profit_sum = number_format(($all_nonope_profit_sum / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����׷׺Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����׷�'", $yyyymm);
}
if (getUniResult($query, $c_nonope_profit_sum) < 1) {
    $c_nonope_profit_sum = 0;                   // ��������
    $c_nonope_profit_sum_temp = 0;
} else {
    if ($yyyymm < 201001) {
        $c_nonope_profit_sum = $c_nonope_profit_sum - $b_nonope_profit_sum;
    }
    $c_nonope_profit_sum_temp = $c_nonope_profit_sum;
    $c_nonope_profit_sum      = number_format(($c_nonope_profit_sum / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķȳ����׷׺Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķȳ����׷�'", $yyyymm);
}
if (getUniResult($query, $l_nonope_profit_sum) < 1) {
    if ($yyyymm == 200906) {
        $l_nonope_profit_sum = 0 - $s_nonope_profit_sum_sagaku + 3100900;   // ��������
    } elseif ($yyyymm == 200905) {
        $l_nonope_profit_sum = 0 - $s_nonope_profit_sum_sagaku - 1550450;   // ��������
    } elseif ($yyyymm == 200904) {
        $l_nonope_profit_sum = 0 - $s_nonope_profit_sum_sagaku - 1550450;   // ��������
    } else {
        $l_nonope_profit_sum = 0 - $s_nonope_profit_sum_sagaku;             // ��������
    }
} else {
    if ($yyyymm == 200906) {
        $l_nonope_profit_sum = $l_nonope_profit_sum - $s_nonope_profit_sum_sagaku + 3100900;
    } elseif ($yyyymm == 200905) {
        $l_nonope_profit_sum = $l_nonope_profit_sum - $s_nonope_profit_sum_sagaku - 1550450;
    } elseif ($yyyymm == 200904) {
        $l_nonope_profit_sum = $l_nonope_profit_sum - $s_nonope_profit_sum_sagaku - 1550450;
    } elseif ($yyyymm < 201001) {
        $l_nonope_profit_sum = $l_nonope_profit_sum - $s_nonope_profit_sum_sagaku;
    }
    $l_nonope_profit_sum = number_format(($l_nonope_profit_sum / $tani), $keta);
}
    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���αĶȳ����׷�'", $p1_ym);
if (getUniResult($query, $p1_all_nonope_profit_sum) < 1) {
    $p1_all_nonope_profit_sum = 0;              // ��������
} else {
    if ($p1_ym == 200906) {
        $p1_all_nonope_profit_sum = $p1_all_nonope_profit_sum + 3100900;
    } elseif ($p1_ym == 200905) {
        $p1_all_nonope_profit_sum = $p1_all_nonope_profit_sum - 1550450;
    } elseif ($p1_ym == 200904) {
        $p1_all_nonope_profit_sum = $p1_all_nonope_profit_sum - 1550450;
    }
    if ($p1_ym == 201002) {
        $p1_all_nonope_profit_sum = $p1_all_nonope_profit_sum + 600000;
    }
    if ($p1_ym == 201003) {
        $p1_all_nonope_profit_sum = $p1_all_nonope_profit_sum - 600000;
    }
    $p1_all_nonope_profit_sum = number_format(($p1_all_nonope_profit_sum / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����׷׺Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����׷�'", $p1_ym);
}
if (getUniResult($query, $p1_c_nonope_profit_sum) < 1) {
    $p1_c_nonope_profit_sum = 0;                   // ��������
    $p1_c_nonope_profit_sum_temp = 0;
} else {
    if ($p1_ym < 201001) {
        $p1_c_nonope_profit_sum = $p1_c_nonope_profit_sum - $p1_b_nonope_profit_sum;
    }
    $p1_c_nonope_profit_sum_temp = $p1_c_nonope_profit_sum;
    $p1_c_nonope_profit_sum      = number_format(($p1_c_nonope_profit_sum / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķȳ����׷׺Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķȳ����׷�'", $p1_ym);
}
if (getUniResult($query, $p1_l_nonope_profit_sum) < 1) {
    if ($p1_ym == 200906) {
        $p1_l_nonope_profit_sum = 0 - $p1_s_nonope_profit_sum_sagaku + 3100900;   // ��������
    } elseif ($p1_ym == 200905) {
        $p1_l_nonope_profit_sum = 0 - $p1_s_nonope_profit_sum_sagaku - 1550450;   // ��������
    } elseif ($p1_ym == 200904) {
        $p1_l_nonope_profit_sum = 0 - $p1_s_nonope_profit_sum_sagaku - 1550450;   // ��������
    } else {
        $p1_l_nonope_profit_sum = 0 - $p1_s_nonope_profit_sum_sagaku;             // ��������
    }
} else {
    if ($p1_ym == 200906) {
        $p1_l_nonope_profit_sum = $p1_l_nonope_profit_sum - $p1_s_nonope_profit_sum_sagaku + 3100900;
    } elseif ($p1_ym == 200905) {
        $p1_l_nonope_profit_sum = $p1_l_nonope_profit_sum - $p1_s_nonope_profit_sum_sagaku - 1550450;
    } elseif ($p1_ym == 200904) {
        $p1_l_nonope_profit_sum = $p1_l_nonope_profit_sum - $p1_s_nonope_profit_sum_sagaku - 1550450;
    } elseif ($p1_ym < 201001) {
        $p1_l_nonope_profit_sum = $p1_l_nonope_profit_sum - $p1_s_nonope_profit_sum_sagaku;
    }
    $p1_l_nonope_profit_sum = number_format(($p1_l_nonope_profit_sum / $tani), $keta);
}
    ///// ������
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���αĶȳ����׷�'", $p2_ym);
if (getUniResult($query, $p2_all_nonope_profit_sum) < 1) {
    $p2_all_nonope_profit_sum = 0;              // ��������
} else {
    if ($p2_ym == 200906) {
        $p2_all_nonope_profit_sum = $p2_all_nonope_profit_sum + 3100900;
    } elseif ($p2_ym == 200905) {
        $p2_all_nonope_profit_sum = $p2_all_nonope_profit_sum - 1550450;
    } elseif ($p2_ym == 200904) {
        $p2_all_nonope_profit_sum = $p2_all_nonope_profit_sum - 1550450;
    }
    if ($p2_ym == 201002) {
        $p2_all_nonope_profit_sum = $p2_all_nonope_profit_sum + 600000;
    }
    if ($p2_ym == 201003) {
        $p2_all_nonope_profit_sum = $p2_all_nonope_profit_sum - 600000;
    }
    $p2_all_nonope_profit_sum = number_format(($p2_all_nonope_profit_sum / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����׷׺Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����׷�'", $p2_ym);
}
if (getUniResult($query, $p2_c_nonope_profit_sum) < 1) {
    $p2_c_nonope_profit_sum = 0;                   // ��������
    $p2_c_nonope_profit_sum_temp = 0;
} else {
    if ($p2_ym < 201001) {
        $p2_c_nonope_profit_sum = $p2_c_nonope_profit_sum - $p2_b_nonope_profit_sum;
    }
    $p2_c_nonope_profit_sum_temp = $p2_c_nonope_profit_sum;
    $p2_c_nonope_profit_sum      = number_format(($p2_c_nonope_profit_sum / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķȳ����׷׺Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķȳ����׷�'", $p2_ym);
}
if (getUniResult($query, $p2_l_nonope_profit_sum) < 1) {
    if ($p2_ym == 200906) {
        $p2_l_nonope_profit_sum = 0 - $p2_s_nonope_profit_sum_sagaku + 3100900;   // ��������
    } elseif ($p2_ym == 200905) {
        $p2_l_nonope_profit_sum = 0 - $p2_s_nonope_profit_sum_sagaku - 1550450;   // ��������
    } elseif ($p2_ym == 200904) {
        $p2_l_nonope_profit_sum = 0 - $p2_s_nonope_profit_sum_sagaku - 1550450;   // ��������
    } else {
        $p2_l_nonope_profit_sum = 0 - $p2_s_nonope_profit_sum_sagaku;             // ��������
    }
} else {
    if ($p2_ym == 200906) {
        $p2_l_nonope_profit_sum = $p2_l_nonope_profit_sum - $p2_s_nonope_profit_sum_sagaku + 3100900;
    } elseif ($p2_ym == 200905) {
        $p2_l_nonope_profit_sum = $p2_l_nonope_profit_sum - $p2_s_nonope_profit_sum_sagaku - 1550450;
    } elseif ($p2_ym == 200904) {
        $p2_l_nonope_profit_sum = $p2_l_nonope_profit_sum - $p2_s_nonope_profit_sum_sagaku - 1550450;
    } elseif ($p2_ym < 201001) {
        $p2_l_nonope_profit_sum = $p2_l_nonope_profit_sum - $p2_s_nonope_profit_sum_sagaku;
    }
    $p2_l_nonope_profit_sum = number_format(($p2_l_nonope_profit_sum / $tani), $keta);
}
    ///// �����߷�
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���αĶȳ����׷�'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_nonope_profit_sum) < 1) {
    $rui_all_nonope_profit_sum = 0;             // ��������
} else {
    if ($yyyymm >= 201002 && $yyyymm <= 201003) {
        $rui_all_nonope_profit_sum = $rui_all_nonope_profit_sum + 600000;
    }
    if ($yyyymm == 201003) {
        $rui_all_nonope_profit_sum = $rui_all_nonope_profit_sum - 600000;
    }
    $rui_all_nonope_profit_sum = number_format(($rui_all_nonope_profit_sum / $tani), $keta);
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ�Ķȳ����׷׺Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_nonope_profit_sum) < 1) {
        $rui_c_nonope_profit_sum = 0;                           // ��������
    } else {
        //$rui_c_nonope_profit_sum = $rui_c_nonope_profit_sum - $rui_b_nonope_profit_sum;
        $rui_c_nonope_profit_sum = number_format(($rui_c_nonope_profit_sum / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='���ץ�Ķȳ����׷�'");
    if (getUniResult($query, $rui_c_nonope_profit_sum_a) < 1) {
        $rui_c_nonope_profit_sum_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='���ץ�Ķȳ����׷׺Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_c_nonope_profit_sum_b) < 1) {
        $rui_c_nonope_profit_sum_b = 0;                          // ��������
    }
    $rui_c_nonope_profit_sum = $rui_c_nonope_profit_sum_a + $rui_c_nonope_profit_sum_b;
    if ($yyyymm < 201001) {
        $rui_c_nonope_profit_sum = $rui_c_nonope_profit_sum - $rui_b_nonope_profit_sum;
    }
    $rui_c_nonope_profit_sum = $rui_c_nonope_profit_sum - $rui_b_pother_a;
    $rui_c_nonope_profit_sum = number_format(($rui_c_nonope_profit_sum / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ�Ķȳ����׷�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_nonope_profit_sum) < 1) {
        $rui_c_nonope_profit_sum = 0;                           // ��������
    } else {
        $rui_c_nonope_profit_sum = $rui_c_nonope_profit_sum - $rui_b_nonope_profit_sum;
        $rui_c_nonope_profit_sum = number_format(($rui_c_nonope_profit_sum / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥��Ķȳ����׷׺Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_nonope_profit_sum) < 1) {
        $rui_l_nonope_profit_sum = 0;                           // ��������
    } else {
        //$rui_l_nonope_profit_sum      = $rui_l_nonope_profit_sum - $rui_s_nonope_profit_sum;
        $rui_l_nonope_profit_sum_temp = $rui_l_nonope_profit_sum;         // �о����׷׻���
        $rui_l_nonope_profit_sum      = number_format(($rui_l_nonope_profit_sum / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='��˥��Ķȳ����׷�'");
    if (getUniResult($query, $rui_l_nonope_profit_sum_a) < 1) {
        $rui_l_nonope_profit_sum_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='��˥��Ķȳ����׷׺Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_l_nonope_profit_sum_b) < 1) {
        $rui_l_nonope_profit_sum_b = 0;                          // ��������
    }
    $rui_l_nonope_profit_sum      = $rui_l_nonope_profit_sum_a + $rui_l_nonope_profit_sum_b;
    $rui_l_nonope_profit_sum      = $rui_l_nonope_profit_sum - $rui_s_gyoumu_a - $rui_s_swari_a - $rui_s_pother_a;
    $rui_l_nonope_profit_sum_temp = $rui_l_nonope_profit_sum;         // �о����׷׻���
    $rui_l_nonope_profit_sum      = number_format(($rui_l_nonope_profit_sum / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥��Ķȳ����׷�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_nonope_profit_sum) < 1) {
        $rui_l_nonope_profit_sum = 0 - $rui_s_nonope_profit_sum_sagaku;     // ��������
    } else {
        $rui_l_nonope_profit_sum = $rui_l_nonope_profit_sum - $rui_s_nonope_profit_sum_sagaku;
        $rui_l_nonope_profit_sum = number_format(($rui_l_nonope_profit_sum / $tani), $keta);
    }
}

/********** �Ķȳ����Ѥλ�ʧ��© **********/
    ///// ����
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ɻ�ʧ��©�Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $b_srisoku) < 1) {
        $b_srisoku        = 0;                      // ��������
        $b_srisoku_temp = 0;
    } else {
        $b_srisoku_temp = $b_srisoku;
    }
} else {
    $b_srisoku     = 0;
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ʧ��©�Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ʧ��©'", $yyyymm);
}
if (getUniResult($query, $s_srisoku) < 1) {
    $s_srisoku        = 0;                      // ��������
    $s_srisoku_sagaku = 0;
} else {
    $s_srisoku_sagaku = $s_srisoku;
    $s_srisoku        = number_format(($s_srisoku / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���λ�ʧ��©'", $yyyymm);
if (getUniResult($query, $all_srisoku) < 1) {
    $all_srisoku = 0;                           // ��������
} else {
    $all_srisoku = number_format(($all_srisoku / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��ʧ��©�Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��ʧ��©'", $yyyymm);
}
if (getUniResult($query, $c_srisoku) < 1) {
    $c_srisoku = 0;                             // ��������
} else {
    $c_srisoku = number_format(($c_srisoku / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���ʧ��©�Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���ʧ��©'", $yyyymm);
}
if (getUniResult($query, $l_srisoku) < 1) {
    $l_srisoku = 0 - $s_srisoku_sagaku;         // ��������
} else {
    if ($yyyymm < 201001) {
        $l_srisoku = $l_srisoku - $s_srisoku_sagaku;
    }
    $l_srisoku = number_format(($l_srisoku / $tani), $keta);
}
    ///// ����
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ɻ�ʧ��©�Ʒ׻�'", $p1_ym);
    if (getUniResult($query, $p1_b_srisoku) < 1) {
        $p1_b_srisoku        = 0;                      // ��������
        $p1_b_srisoku_temp = 0;
    } else {
        $p1_b_srisoku_temp = $p1_b_srisoku;
    }
} else {
    $p1_b_srisoku     = 0;
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ʧ��©�Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ʧ��©'", $p1_ym);
}
if (getUniResult($query, $p1_s_srisoku) < 1) {
    $p1_s_srisoku        = 0;                      // ��������
    $p1_s_srisoku_sagaku = 0;
} else {
    $p1_s_srisoku_sagaku = $p1_s_srisoku;
    $p1_s_srisoku        = number_format(($p1_s_srisoku / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���λ�ʧ��©'", $p1_ym);
if (getUniResult($query, $p1_all_srisoku) < 1) {
    $p1_all_srisoku = 0;                        // ��������
} else {
    $p1_all_srisoku = number_format(($p1_all_srisoku / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��ʧ��©�Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��ʧ��©'", $p1_ym);
}
if (getUniResult($query, $p1_c_srisoku) < 1) {
    $p1_c_srisoku = 0;                             // ��������
} else {
    $p1_c_srisoku = number_format(($p1_c_srisoku / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���ʧ��©�Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���ʧ��©'", $p1_ym);
}
if (getUniResult($query, $p1_l_srisoku) < 1) {
    $p1_l_srisoku = 0 - $p1_s_srisoku_sagaku;         // ��������
} else {
    if ($p1_ym < 201001) {
        $p1_l_srisoku = $p1_l_srisoku - $p1_s_srisoku_sagaku;
    }
    $p1_l_srisoku = number_format(($p1_l_srisoku / $tani), $keta);
}
    ///// ������
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ɻ�ʧ��©�Ʒ׻�'", $p2_ym);
    if (getUniResult($query, $p2_b_srisoku) < 1) {
        $p2_b_srisoku        = 0;                      // ��������
        $p2_b_srisoku_temp = 0;
    } else {
        $p2_b_srisoku_temp = $p2_b_srisoku;
    }
} else {
    $p2_b_srisoku     = 0;
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ʧ��©�Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ʧ��©'", $p2_ym);
}
if (getUniResult($query, $p2_s_srisoku) < 1) {
    $p2_s_srisoku        = 0;                      // ��������
    $p2_s_srisoku_sagaku = 0;
} else {
    $p2_s_srisoku_sagaku = $p2_s_srisoku;
    $p2_s_srisoku        = number_format(($p2_s_srisoku / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���λ�ʧ��©'", $p2_ym);
if (getUniResult($query, $p2_all_srisoku) < 1) {
    $p2_all_srisoku = 0;                        // ��������
} else {
    $p2_all_srisoku = number_format(($p2_all_srisoku / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��ʧ��©�Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��ʧ��©'", $p2_ym);
}
if (getUniResult($query, $p2_c_srisoku) < 1) {
    $p2_c_srisoku = 0;                             // ��������
} else {
    $p2_c_srisoku = number_format(($p2_c_srisoku / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���ʧ��©�Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���ʧ��©'", $p2_ym);
}
if (getUniResult($query, $p2_l_srisoku) < 1) {
    $p2_l_srisoku = 0 - $p2_s_srisoku_sagaku;         // ��������
} else {
    if ($p2_ym < 201001) {
        $p2_l_srisoku = $p2_l_srisoku - $p2_s_srisoku_sagaku;
    }
    $p2_l_srisoku = number_format(($p2_l_srisoku / $tani), $keta);
}
    ///// �����߷�
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ɻ�ʧ��©�Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_srisoku) < 1) {
        $rui_b_srisoku = 0;                           // ��������
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $rui_b_srisoku_a = 0;
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='���ɻ�ʧ��©�Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_b_srisoku_b) < 1) {
        $rui_b_srisoku_b = 0;                          // ��������
    }
    $rui_b_srisoku = $rui_b_srisoku_a + $rui_b_srisoku_b;
} else {
    $rui_b_srisoku = 0;
}

if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ʧ��©�Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_srisoku) < 1) {
        $rui_s_srisoku = 0;                           // ��������
    } else {
        $rui_s_srisoku = number_format(($rui_s_srisoku / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='���ʧ��©'");
    if (getUniResult($query, $rui_s_srisoku_a) < 1) {
        $rui_s_srisoku_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='���ʧ��©�Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_s_srisoku_b) < 1) {
        $rui_s_srisoku_b = 0;                          // ��������
    }
    $rui_s_srisoku = $rui_s_srisoku_a + $rui_s_srisoku_b;
    $rui_s_srisoku_sagaku = $rui_s_srisoku;
    $rui_s_srisoku = number_format(($rui_s_srisoku / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ʧ��©'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_srisoku) < 1) {
        $rui_s_srisoku        = 0;                  // ��������
        $rui_s_srisoku_sagaku = 0;
    } else {
        $rui_s_srisoku_sagaku = $rui_s_srisoku;
        $rui_s_srisoku = number_format(($rui_s_srisoku / $tani), $keta);
    }
}

$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���λ�ʧ��©'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_srisoku) < 1) {
    $rui_all_srisoku = 0;                       // ��������
} else {
    $rui_all_srisoku = number_format(($rui_all_srisoku / $tani), $keta);
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ��ʧ��©�Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_srisoku) < 1) {
        $rui_c_srisoku = 0;                           // ��������
    } else {
        $rui_c_srisoku = number_format(($rui_c_srisoku / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='���ץ��ʧ��©'");
    if (getUniResult($query, $rui_c_srisoku_a) < 1) {
        $rui_c_srisoku_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='���ץ��ʧ��©�Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_c_srisoku_b) < 1) {
        $rui_c_srisoku_b = 0;                          // ��������
    }
    $rui_c_srisoku = $rui_c_srisoku_a + $rui_c_srisoku_b;
    $rui_c_srisoku = number_format(($rui_c_srisoku / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ��ʧ��©'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_srisoku) < 1) {
        $rui_c_srisoku = 0;                           // ��������
    } else {
        $rui_c_srisoku = number_format(($rui_c_srisoku / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥���ʧ��©�Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_srisoku) < 1) {
        $rui_l_srisoku = 0;                           // ��������
    } else {
        $rui_l_srisoku = number_format(($rui_l_srisoku / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='��˥���ʧ��©'");
    if (getUniResult($query, $rui_l_srisoku_a) < 1) {
        $rui_l_srisoku_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='��˥���ʧ��©�Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_l_srisoku_b) < 1) {
        $rui_l_srisoku_b = 0;                          // ��������
    }
    $rui_l_srisoku = $rui_l_srisoku_a + $rui_l_srisoku_b;
    $rui_l_srisoku = $rui_l_srisoku - $rui_s_srisoku_a;
    $rui_l_srisoku = number_format(($rui_l_srisoku / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥���ʧ��©'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_srisoku) < 1) {
        $rui_l_srisoku = 0 - $rui_s_srisoku_sagaku; // ��������
    } else {
        $rui_l_srisoku = $rui_l_srisoku - $rui_s_srisoku_sagaku;
        $rui_l_srisoku = number_format(($rui_l_srisoku / $tani), $keta);
    }
}

/********** �Ķȳ����ѤΤ���¾ **********/
    ///// ����
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ɱĶȳ����Ѥ���¾�Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $b_lother) < 1) {
        $b_lother      = 0;                       // ��������
        $b_lother_temp = 0;
    } else {
        $b_lother_temp = $b_lother;
    }
} else {
    $b_lother     = 0;
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��Ķȳ����Ѥ���¾�Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��Ķȳ����Ѥ���¾'", $yyyymm);
}
if (getUniResult($query, $s_lother) < 1) {
    $s_lother        = 0;                       // ��������
    $s_lother_sagaku = 0;
} else {
    $s_lother_sagaku = $s_lother;
    $s_lother        = number_format(($s_lother / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���αĶȳ����Ѥ���¾'", $yyyymm);
if (getUniResult($query, $all_lother) < 1) {
    $all_lother = 0;                            // ��������
} else {
    $all_lother = number_format(($all_lother / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����Ѥ���¾�Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����Ѥ���¾'", $yyyymm);
}
if (getUniResult($query, $c_lother) < 1) {
    $c_lother = 0;                              // ��������
} else {
    $c_lother = number_format(($c_lother / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķȳ����Ѥ���¾�Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķȳ����Ѥ���¾'", $yyyymm);
}
if (getUniResult($query, $l_lother) < 1) {
    $l_lother = 0 - $s_lother_sagaku;           // ��������
} else {
    if ($yyyymm < 201001) {
        $l_lother = $l_lother - $s_lother_sagaku;
    }
    $l_lother = number_format(($l_lother / $tani), $keta);
}
    ///// ����
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ɱĶȳ����Ѥ���¾�Ʒ׻�'", $p1_ym);
    if (getUniResult($query, $p1_b_lother) < 1) {
        $p1_b_lother      = 0;                       // ��������
        $p1_b_lother_temp = 0;
    } else {
        $p1_b_lother_temp = $p1_b_lother;
    }
} else {
    $p1_b_lother     = 0;
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��Ķȳ����Ѥ���¾�Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��Ķȳ����Ѥ���¾'", $p1_ym);
}
if (getUniResult($query, $p1_s_lother) < 1) {
    $p1_s_lother        = 0;                       // ��������
    $p1_s_lother_sagaku = 0;
} else {
    $p1_s_lother_sagaku = $p1_s_lother;
    $p1_s_lother        = number_format(($p1_s_lother / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���αĶȳ����Ѥ���¾'", $p1_ym);
if (getUniResult($query, $p1_all_lother) < 1) {
    $p1_all_lother = 0;                         // ��������
} else {
    $p1_all_lother = number_format(($p1_all_lother / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����Ѥ���¾�Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����Ѥ���¾'", $p1_ym);
}
if (getUniResult($query, $p1_c_lother) < 1) {
    $p1_c_lother = 0;                              // ��������
} else {
    $p1_c_lother = number_format(($p1_c_lother / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķȳ����Ѥ���¾�Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķȳ����Ѥ���¾'", $p1_ym);
}
if (getUniResult($query, $p1_l_lother) < 1) {
    $p1_l_lother = 0 - $p1_s_lother_sagaku;           // ��������
} else {
    if ($p1_ym < 201001) {
        $p1_l_lother = $p1_l_lother - $p1_s_lother_sagaku;
    }
    $p1_l_lother = number_format(($p1_l_lother / $tani), $keta);
}
    ///// ������
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ɱĶȳ����Ѥ���¾�Ʒ׻�'", $p2_ym);
    if (getUniResult($query, $p2_b_lother) < 1) {
        $p2_b_lother      = 0;                       // ��������
        $p2_b_lother_temp = 0;
    } else {
        $p2_b_lother_temp = $p2_b_lother;
    }
} else {
    $p2_b_lother     = 0;
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��Ķȳ����Ѥ���¾�Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��Ķȳ����Ѥ���¾'", $p2_ym);
}
if (getUniResult($query, $p2_s_lother) < 1) {
    $p2_s_lother        = 0;                       // ��������
    $p2_s_lother_sagaku = 0;
} else {
    $p2_s_lother_sagaku = $p2_s_lother;
    $p2_s_lother        = number_format(($p2_s_lother / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���αĶȳ����Ѥ���¾'", $p2_ym);
if (getUniResult($query, $p2_all_lother) < 1) {
    $p2_all_lother = 0;                         // ��������
} else {
    $p2_all_lother = number_format(($p2_all_lother / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����Ѥ���¾�Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����Ѥ���¾'", $p2_ym);
}
if (getUniResult($query, $p2_c_lother) < 1) {
    $p2_c_lother = 0;                              // ��������
} else {
    $p2_c_lother = number_format(($p2_c_lother / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķȳ����Ѥ���¾�Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķȳ����Ѥ���¾'", $p2_ym);
}
if (getUniResult($query, $p2_l_lother) < 1) {
    $p2_l_lother = 0 - $p2_s_lother_sagaku;           // ��������
} else {
    if ($p2_ym < 201001) {
        $p2_l_lother = $p2_l_lother - $p2_s_lother_sagaku;
    }
    $p2_l_lother = number_format(($p2_l_lother / $tani), $keta);
}
    ///// �����߷�
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ɱĶȳ����Ѥ���¾�Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_lother) < 1) {
        $rui_b_lother = 0;                           // ��������
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $rui_b_lother_a = 0;
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='���ɱĶȳ����Ѥ���¾�Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_b_lother_b) < 1) {
        $rui_b_lother_b = 0;                          // ��������
    }
    $rui_b_lother = $rui_b_lother_a + $rui_b_lother_b;
} else {
    $rui_b_lother = 0;
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��Ķȳ����Ѥ���¾�Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_lother) < 1) {
        $rui_s_lother = 0;                           // ��������
    } else {
        $rui_s_lother = number_format(($rui_s_lother / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='��Ķȳ����Ѥ���¾'");
    if (getUniResult($query, $rui_s_lother_a) < 1) {
        $rui_s_lother_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='��Ķȳ����Ѥ���¾�Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_s_lother_b) < 1) {
        $rui_s_lother_b = 0;                          // ��������
    }
    $rui_s_lother = $rui_s_lother_a + $rui_s_lother_b;
    $rui_s_lother_sagaku = $rui_s_lother;
    $rui_s_lother = number_format(($rui_s_lother / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��Ķȳ����Ѥ���¾'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_lother) < 1) {
        $rui_s_lother        = 0;                   // ��������
        $rui_s_lother_sagaku = 0;
    } else {
        $rui_s_lother_sagaku = $rui_s_lother;
        $rui_s_lother        = number_format(($rui_s_lother / $tani), $keta);
    }
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���αĶȳ����Ѥ���¾'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_lother) < 1) {
    $rui_all_lother = 0;                        // ��������
} else {
    $rui_all_lother = number_format(($rui_all_lother / $tani), $keta);
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ�Ķȳ����Ѥ���¾�Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_lother) < 1) {
        $rui_c_lother = 0;                           // ��������
    } else {
        $rui_l_lother = $rui_l_lother - $rui_s_lother_sagaku;
        $rui_c_lother = number_format(($rui_c_lother / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='���ץ�Ķȳ����Ѥ���¾'");
    if (getUniResult($query, $rui_c_lother_a) < 1) {
        $rui_c_lother_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='���ץ�Ķȳ����Ѥ���¾�Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_c_lother_b) < 1) {
        $rui_c_lother_b = 0;                          // ��������
    }
    $rui_c_lother = $rui_c_lother_a + $rui_c_lother_b;
    $rui_c_lother = number_format(($rui_c_lother / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ�Ķȳ����Ѥ���¾'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_lother) < 1) {
        $rui_c_lother = 0;                           // ��������
    } else {
        $rui_c_lother = number_format(($rui_c_lother / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥��Ķȳ����Ѥ���¾�Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_lother) < 1) {
        $rui_l_lother = 0 - $rui_s_lother_sagaku;   // ��������
    } else {
        $rui_l_lother = $rui_l_lother - $rui_s_lother_sagaku;
        $rui_l_lother = number_format(($rui_l_lother / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='��˥��Ķȳ����Ѥ���¾'");
    if (getUniResult($query, $rui_l_lother_a) < 1) {
        $rui_l_lother_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='��˥��Ķȳ����Ѥ���¾�Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_l_lother_b) < 1) {
        $rui_l_lother_b = 0;                          // ��������
    }
    $rui_l_lother = $rui_l_lother_a + $rui_l_lother_b;
    $rui_l_lother = $rui_l_lother - $rui_s_lother_sagaku;
    $rui_l_lother = number_format(($rui_l_lother / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥��Ķȳ����Ѥ���¾'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_lother) < 1) {
        $rui_l_lother = 0 - $rui_s_lother_sagaku;   // ��������
    } else {
        $rui_l_lother = $rui_l_lother - $rui_s_lother_sagaku;
        $rui_l_lother = number_format(($rui_l_lother / $tani), $keta);
    }
}
/********** �Ķȳ����Ѥι�� **********/
    ///// ����
$p2_b_nonope_loss_sum  = $p2_b_srisoku + $p2_b_lother;
$p2_b_srisoku          = number_format(($p2_b_srisoku / $tani), $keta);
$p2_b_lother           = number_format(($p2_b_lother / $tani), $keta);

$p1_b_nonope_loss_sum  = $p1_b_srisoku + $p1_b_lother;
$p1_b_srisoku          = number_format(($p1_b_srisoku / $tani), $keta);
$p1_b_lother           = number_format(($p1_b_lother / $tani), $keta);

$b_nonope_loss_sum     = $b_srisoku + $p1_b_lother;
$b_srisoku             = number_format(($b_srisoku / $tani), $keta);
$b_lother              = number_format(($b_lother / $tani), $keta);

$rui_b_nonope_loss_sum = $rui_b_srisoku + $rui_b_lother;
$rui_b_srisoku         = number_format(($rui_b_srisoku / $tani), $keta);
$rui_b_lother          = number_format(($rui_b_lother / $tani), $keta);
    ///// �������
$p2_s_nonope_loss_sum         = $p2_s_srisoku_sagaku + $p2_s_lother_sagaku;
$p2_s_nonope_loss_sum_sagaku  = $p2_s_nonope_loss_sum;
$p2_s_nonope_loss_sum         = number_format(($p2_s_nonope_loss_sum / $tani), $keta);

$p1_s_nonope_loss_sum         = $p1_s_srisoku_sagaku + $p1_s_lother_sagaku;
$p1_s_nonope_loss_sum_sagaku  = $p1_s_nonope_loss_sum;
$p1_s_nonope_loss_sum         = number_format(($p1_s_nonope_loss_sum / $tani), $keta);

$s_nonope_loss_sum            = $s_srisoku_sagaku + $s_lother_sagaku;
$s_nonope_loss_sum_sagaku     = $s_nonope_loss_sum;
$s_nonope_loss_sum            = number_format(($s_nonope_loss_sum / $tani), $keta);

$rui_s_nonope_loss_sum        = $rui_s_srisoku_sagaku + $rui_s_lother_sagaku;
$rui_s_nonope_loss_sum_sagaku = $rui_s_nonope_loss_sum;
$rui_s_nonope_loss_sum        = number_format(($rui_s_nonope_loss_sum / $tani), $keta);

    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���αĶȳ����ѷ�'", $yyyymm);
if (getUniResult($query, $all_nonope_loss_sum) < 1) {
    $all_nonope_loss_sum = 0;                   // ��������
} else {
    $all_nonope_loss_sum = number_format(($all_nonope_loss_sum / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����ѷ׺Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����ѷ�'", $yyyymm);
}
if (getUniResult($query, $c_nonope_loss_sum) < 1) {
    $c_nonope_loss_sum = 0;                     // ��������
    $c_nonope_loss_sum_temp = 0;
} else {
    $c_nonope_loss_sum_temp = $c_nonope_loss_sum;
    $c_nonope_loss_sum = number_format(($c_nonope_loss_sum / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķȳ����ѷ׺Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķȳ����ѷ�'", $yyyymm);
}
if (getUniResult($query, $l_nonope_loss_sum) < 1) {
    $l_nonope_loss_sum = 0 - $s_nonope_loss_sum_sagaku;     // ��������
} else {
    if ($yyyymm < 201001) {
        $l_nonope_loss_sum = $l_nonope_loss_sum - $s_nonope_loss_sum_sagaku;
    }
    $l_nonope_loss_sum = number_format(($l_nonope_loss_sum / $tani), $keta);
}
    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���αĶȳ����ѷ�'", $p1_ym);
if (getUniResult($query, $p1_all_nonope_loss_sum) < 1) {
    $p1_all_nonope_loss_sum = 0;                // ��������
} else {
    $p1_all_nonope_loss_sum = number_format(($p1_all_nonope_loss_sum / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����ѷ׺Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����ѷ�'", $p1_ym);
}
if (getUniResult($query, $p1_c_nonope_loss_sum) < 1) {
    $p1_c_nonope_loss_sum = 0;                     // ��������
    $p1_c_nonope_loss_sum_temp = 0;
} else {
    $p1_c_nonope_loss_sum_temp = $p1_c_nonope_loss_sum;
    $p1_c_nonope_loss_sum = number_format(($p1_c_nonope_loss_sum / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķȳ����ѷ׺Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķȳ����ѷ�'", $p1_ym);
}
if (getUniResult($query, $p1_l_nonope_loss_sum) < 1) {
    $p1_l_nonope_loss_sum = 0 - $p1_s_nonope_loss_sum_sagaku;     // ��������
} else {
    if ($p1_ym < 201001) {
        $p1_l_nonope_loss_sum = $p1_l_nonope_loss_sum - $p1_s_nonope_loss_sum_sagaku;
    }
    $p1_l_nonope_loss_sum = number_format(($p1_l_nonope_loss_sum / $tani), $keta);
}
    ///// ������
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���αĶȳ����ѷ�'", $p2_ym);
if (getUniResult($query, $p2_all_nonope_loss_sum) < 1) {
    $p2_all_nonope_loss_sum = 0;                // ��������
} else {
    $p2_all_nonope_loss_sum = number_format(($p2_all_nonope_loss_sum / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����ѷ׺Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����ѷ�'", $p2_ym);
}
if (getUniResult($query, $p2_c_nonope_loss_sum) < 1) {
    $p2_c_nonope_loss_sum = 0;                     // ��������
    $p2_c_nonope_loss_sum_temp = 0;
} else {
    $p2_c_nonope_loss_sum_temp = $p2_c_nonope_loss_sum;
    $p2_c_nonope_loss_sum = number_format(($p2_c_nonope_loss_sum / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķȳ����ѷ׺Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķȳ����ѷ�'", $p2_ym);
}
if (getUniResult($query, $p2_l_nonope_loss_sum) < 1) {
    $p2_l_nonope_loss_sum = 0 - $p2_s_nonope_loss_sum_sagaku;     // ��������
} else {
    if ($p2_ym < 201001) {
        $p2_l_nonope_loss_sum = $p2_l_nonope_loss_sum - $p2_s_nonope_loss_sum_sagaku;
    }
    $p2_l_nonope_loss_sum = number_format(($p2_l_nonope_loss_sum / $tani), $keta);
}
    ///// �����߷�
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���αĶȳ����ѷ�'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_nonope_loss_sum) < 1) {
    $rui_all_nonope_loss_sum = 0;               // ��������
} else {
    $rui_all_nonope_loss_sum = number_format(($rui_all_nonope_loss_sum / $tani), $keta);
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ�Ķȳ����ѷ׺Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_nonope_loss_sum) < 1) {
        $rui_c_nonope_loss_sum = 0;                           // ��������
    } else {
        $rui_c_nonope_loss_sum = number_format(($rui_c_nonope_loss_sum / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='���ץ�Ķȳ����ѷ�'");
    if (getUniResult($query, $rui_c_nonope_loss_sum_a) < 1) {
        $rui_c_nonope_loss_sum_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='���ץ�Ķȳ����ѷ׺Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_c_nonope_loss_sum_b) < 1) {
        $rui_c_nonope_loss_sum_b = 0;                          // ��������
    }
    $rui_c_nonope_loss_sum = $rui_c_nonope_loss_sum_a + $rui_c_nonope_loss_sum_b;
    $rui_c_nonope_loss_sum = number_format(($rui_c_nonope_loss_sum / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ�Ķȳ����ѷ�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_nonope_loss_sum) < 1) {
        $rui_c_nonope_loss_sum = 0;                           // ��������
    } else {
        $rui_c_nonope_loss_sum = number_format(($rui_c_nonope_loss_sum / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥��Ķȳ����ѷ׺Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_nonope_loss_sum) < 1) {
        $rui_l_nonope_loss_sum = 0;                           // ��������
    } else {
        $rui_l_nonope_loss_sum_temp = $rui_l_nonope_loss_sum;         // �о����׷׻���
        $rui_l_nonope_loss_sum      = number_format(($rui_l_nonope_loss_sum / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='��˥��Ķȳ����ѷ�'");
    if (getUniResult($query, $rui_l_nonope_loss_sum_a) < 1) {
        $rui_l_nonope_loss_sum_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='��˥��Ķȳ����ѷ׺Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_l_nonope_loss_sum_b) < 1) {
        $rui_l_nonope_loss_sum_b = 0;                          // ��������
    }
    $rui_l_nonope_loss_sum      = $rui_l_nonope_loss_sum_a + $rui_l_nonope_loss_sum_b;
    $rui_l_nonope_loss_sum      = $rui_l_nonope_loss_sum - $rui_s_srisoku_a - $rui_s_lother_a;
    $rui_l_nonope_loss_sum_temp = $rui_l_nonope_loss_sum;         // �о����׷׻���
    $rui_l_nonope_loss_sum      = number_format(($rui_l_nonope_loss_sum / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥��Ķȳ����ѷ�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_nonope_loss_sum) < 1) {
        $rui_l_nonope_loss_sum = 0 - $rui_s_nonope_loss_sum_sagaku;     // ��������
    } else {
        $rui_l_nonope_loss_sum = $rui_l_nonope_loss_sum - $rui_s_nonope_loss_sum_sagaku;
        $rui_l_nonope_loss_sum = number_format(($rui_l_nonope_loss_sum / $tani), $keta);
    }
}

/********** �о����� **********/
    ///// ����
$p2_b_current_profit     = $p2_b_ope_profit + $p2_b_nonope_profit_sum - $p2_b_nonope_loss_sum;
$p2_b_ope_profit         = number_format(($p2_b_ope_profit / $tani), $keta);
$p2_b_nonope_profit_sum  = number_format(($p2_b_nonope_profit_sum / $tani), $keta);
$p2_b_nonope_loss_sum    = number_format(($p2_b_nonope_loss_sum / $tani), $keta);
$p2_b_current_profit     = number_format(($p2_b_current_profit / $tani), $keta);

$p1_b_current_profit     = $p1_b_ope_profit + $p1_b_nonope_profit_sum - $p1_b_nonope_loss_sum;
$p1_b_ope_profit         = number_format(($p1_b_ope_profit / $tani), $keta);
$p1_b_nonope_profit_sum  = number_format(($p1_b_nonope_profit_sum / $tani), $keta);
$p1_b_nonope_loss_sum    = number_format(($p1_b_nonope_loss_sum / $tani), $keta);
$p1_b_current_profit     = number_format(($p1_b_current_profit / $tani), $keta);

$b_current_profit        = $b_ope_profit + $b_nonope_profit_sum - $b_nonope_loss_sum;
$b_ope_profit            = number_format(($b_ope_profit / $tani), $keta);
$b_nonope_profit_sum     = number_format(($b_nonope_profit_sum / $tani), $keta);
$b_nonope_loss_sum       = number_format(($b_nonope_loss_sum / $tani), $keta);
$b_current_profit        = number_format(($b_current_profit / $tani), $keta);

$rui_b_current_profit    = $rui_b_ope_profit + $rui_b_nonope_profit_sum - $rui_b_nonope_loss_sum;
$rui_b_ope_profit        = number_format(($rui_b_ope_profit / $tani), $keta);
$rui_b_nonope_profit_sum = number_format(($rui_b_nonope_profit_sum / $tani), $keta);
$rui_b_nonope_loss_sum   = number_format(($rui_b_nonope_loss_sum / $tani), $keta);
$rui_b_current_profit    = number_format(($rui_b_current_profit / $tani), $keta);
    ///// �������
$p2_s_current_profit         = $p2_s_ope_profit_sagaku + $p2_s_nonope_profit_sum_sagaku - $p2_s_nonope_loss_sum_sagaku;
$p2_s_current_profit_sagaku  = $p2_s_current_profit;
$p2_s_current_profit         = $p2_s_current_profit + $p2_sc_uri_sagaku - $p2_sc_metarial_sagaku;      // ���ץ��������̣��sagaku�θ�ݥ�˥�����ޥ��ʥ����Ƥ��ޤ��١�
if ($p2_ym == 200912) {
    $p2_s_current_profit = $p2_s_current_profit + 1409708;
}
if ($p2_ym >= 201001) {
    $p2_s_current_profit = $p2_s_current_profit + $p2_s_kyu_kei - $p2_s_kyu_kin;  // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    //$p2_s_current_profit = $p2_s_current_profit + 432323 - 129697;  // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
}
$p2_s_current_profit         = number_format(($p2_s_current_profit / $tani), $keta);

$p1_s_current_profit         = $p1_s_ope_profit_sagaku + $p1_s_nonope_profit_sum_sagaku - $p1_s_nonope_loss_sum_sagaku;
$p1_s_current_profit_sagaku  = $p1_s_current_profit;
$p1_s_current_profit         = $p1_s_current_profit + $p1_sc_uri_sagaku - $p1_sc_metarial_sagaku;      // ���ץ��������̣��sagaku�θ�ݥ�˥�����ޥ��ʥ����Ƥ��ޤ��١�
if ($p1_ym == 200912) {
    $p1_s_current_profit = $p1_s_current_profit + 1409708;
}
if ($p1_ym >= 201001) {
    $p1_s_current_profit = $p1_s_current_profit + $p1_s_kyu_kei - $p1_s_kyu_kin;  // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    //$p1_s_current_profit = $p1_s_current_profit + 432323 - 129697;  // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
}
$p1_s_current_profit         = number_format(($p1_s_current_profit / $tani), $keta);

$s_current_profit            = $s_ope_profit_sagaku + $s_nonope_profit_sum_sagaku - $s_nonope_loss_sum_sagaku;
$s_current_profit_sagaku     = $s_current_profit;
$s_current_profit            = $s_current_profit + $sc_uri_sagaku - $sc_metarial_sagaku;      // ���ץ��������̣��sagaku�θ�ݥ�˥�����ޥ��ʥ����Ƥ��ޤ��١�
if ($yyyymm == 200912) {
    $s_current_profit = $s_current_profit + 1409708;
}
if ($yyyymm >= 201001) {
    $s_current_profit = $s_current_profit + $s_kyu_kei - $s_kyu_kin;  // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    //$s_current_profit = $s_current_profit + 432323 - 129697;  // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
}
$s_current_profit            = number_format(($s_current_profit / $tani), $keta);

$rui_s_current_profit        = $rui_s_ope_profit_sagaku + $rui_s_nonope_profit_sum_sagaku - $rui_s_nonope_loss_sum_sagaku;
$rui_s_current_profit_sagaku = $rui_s_current_profit;
$rui_s_current_profit        = $rui_s_current_profit + $rui_sc_uri_sagaku - $rui_sc_metarial_sagaku;      // ���ץ��������̣��sagaku�θ�ݥ�˥�����ޥ��ʥ����Ƥ��ޤ��١�
if ($yyyymm >= 200912 && $yyyymm <= 201003) {
    $rui_s_current_profit = $rui_s_current_profit + 1409708;
}
if ($yyyymm >= 201001) {
    $rui_s_current_profit = $rui_s_current_profit + $rui_s_kyu_kei - $rui_s_kyu_kin;  // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    //$rui_s_current_profit = $rui_s_current_profit + 432323 - 129697;  // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
}
$rui_s_current_profit        = number_format(($rui_s_current_profit / $tani), $keta);

    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ηо�����'", $yyyymm);
if (getUniResult($query, $all_current_profit) < 1) {
    $all_current_profit = 0;                // ��������
} else {
    if ($yyyymm == 201002) {
        $all_current_profit = $all_current_profit + 600000;
    }
    if ($yyyymm == 201003) {
        $all_current_profit = $all_current_profit - 600000;
    }
    $all_current_profit = $all_current_profit + $b_uri_sagaku;
    $all_current_profit = number_format(($all_current_profit / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�о����׺Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�о�����'", $yyyymm);
}
if (getUniResult($query, $c_current_profit) < 1) {
    $c_current_profit = 0;                  // ��������
} else {
    if ($yyyymm < 201001) {
        $c_current_profit = $c_current_profit + $b_sagaku + $c_allo_kin - $sc_uri_sagaku + $sc_metarial_sagaku; // ���ץ��������̣
    } else {
        $c_current_profit = $c_ope_profit_temp + $c_nonope_profit_sum_temp - $c_nonope_loss_sum_temp;
    }
    if ($yyyymm == 200912) {
        $c_current_profit = $c_current_profit - 1227429;
    }
    if ($yyyymm >= 201001) {
        //$c_current_profit = $c_current_profit - $c_kyu_kin;
        //$c_current_profit = $c_current_profit - 151313;
    }
    $c_current_profit = number_format(($c_current_profit / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��о����׺Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��о�����'", $yyyymm);
}
if (getUniResult($query, $l_current_profit) < 1) {
    $l_current_profit = 0 - $s_current_profit_sagaku;       // ��������
    $l_current_profit = number_format(($l_current_profit / $tani), $keta);
} else {
    if ($yyyymm < 201001) {
        $l_current_profit = $l_current_profit - $s_current_profit_sagaku + $l_allo_kin;
    } else {
        $l_current_profit = $l_current_profit - $s_ope_profit_sagaku + $l_allo_kin;
    }
    if ($yyyymm == 200912) {
        $l_current_profit = $l_current_profit - 182279;
    }
    if ($yyyymm >= 201001) {
        $l_current_profit = $l_current_profit - $l_kyu_kin; // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        //$l_current_profit = $l_current_profit - 151313; // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    if ($yyyymm == 201004) {
        $l_current_profit = $l_current_profit - 255240;
    }
    $l_current_profit = number_format(($l_current_profit / $tani), $keta);
}
    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ηо�����'", $p1_ym);
if (getUniResult($query, $p1_all_current_profit) < 1) {
    $p1_all_current_profit = 0;             // ��������
} else {
    if ($p1_ym == 201002) {
        $p1_all_current_profit = $p1_all_current_profit + 600000;
    }
    if ($p1_ym == 201003) {
        $p1_all_current_profit = $p1_all_current_profit - 600000;
    }
    $p1_all_current_profit = $p1_all_current_profit + $p1_b_uri_sagaku;
    $p1_all_current_profit = number_format(($p1_all_current_profit / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�о����׺Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�о�����'", $p1_ym);
}
if (getUniResult($query, $p1_c_current_profit) < 1) {
    $p1_c_current_profit = 0;                  // ��������
} else {
    if ($p1_ym < 201001) {
        $p1_c_current_profit = $p1_c_current_profit + $p1_b_sagaku + $p1_c_allo_kin - $p1_sc_uri_sagaku + $p1_sc_metarial_sagaku; // ���ץ��������̣
    } else {
        $p1_c_current_profit = $p1_c_ope_profit_temp + $p1_c_nonope_profit_sum_temp - $p1_c_nonope_loss_sum_temp;
    }
    if ($p1_ym == 200912) {
        $p1_c_current_profit = $p1_c_current_profit - 1227429;
    }
    if ($p1_ym >= 201001) {
        //$p1_c_current_profit = $p1_c_current_profit - $p1_c_kyu_kin;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        //$p1_c_current_profit = $p1_c_current_profit - 151313;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    $p1_c_current_profit = number_format(($p1_c_current_profit / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��о����׺Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��о�����'", $p1_ym);
}
if (getUniResult($query, $p1_l_current_profit) < 1) {
    $p1_l_current_profit = 0 - $p1_s_current_profit_sagaku;       // ��������
} else {
    if ($p1_ym < 201001) {
        $p1_l_current_profit = $p1_l_current_profit - $p1_s_current_profit_sagaku + $p1_l_allo_kin;
    } else {
        $p1_l_current_profit = $p1_l_current_profit - $p1_s_ope_profit_sagaku + $p1_l_allo_kin;
    }
    if ($p1_ym == 200912) {
        $p1_l_current_profit = $p1_l_current_profit - 182279;
    }
    if ($p1_ym >= 201001) {
        $p1_l_current_profit = $p1_l_current_profit - $p1_l_kyu_kin;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        //$p1_l_current_profit = $p1_l_current_profit - 151313;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    if ($p1_ym == 201004) {
        $p1_l_current_profit = $p1_l_current_profit - 255240;
    }
    $p1_l_current_profit = number_format(($p1_l_current_profit / $tani), $keta);
}
    ///// ������
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ηо�����'", $p2_ym);
if (getUniResult($query, $p2_all_current_profit) < 1) {
    $p2_all_current_profit = 0;             // ��������
} else {
    if ($p2_ym == 201002) {
        $p2_all_current_profit = $p2_all_current_profit + 600000;
    }
    if ($p2_ym == 201003) {
        $p2_all_current_profit = $p2_all_current_profit - 600000;
    }
    $p2_all_current_profit = $p2_all_current_profit + $p2_b_uri_sagaku;
    $p2_all_current_profit = number_format(($p2_all_current_profit / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�о����׺Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�о�����'", $p2_ym);
}
if (getUniResult($query, $p2_c_current_profit) < 1) {
    $p2_c_current_profit = 0;                  // ��������
} else {
    if ($p2_ym < 201001) {
        $p2_c_current_profit = $p2_c_current_profit + $p2_b_sagaku + $p2_c_allo_kin - $p2_sc_uri_sagaku + $p2_sc_metarial_sagaku; // ���ץ��������̣
    } else {
        $p2_c_current_profit = $p2_c_ope_profit_temp + $p2_c_nonope_profit_sum_temp - $p2_c_nonope_loss_sum_temp;
    }
    if ($p2_ym == 200912) {
        $p2_c_current_profit = $p2_c_current_profit - 1227429;
    }
    if ($p2_ym >= 201001) {
        //$p2_c_current_profit = $p2_c_current_profit - $p2_c_kyu_kin;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        //$p2_c_current_profit = $p2_c_current_profit - 151313;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    $p2_c_current_profit = number_format(($p2_c_current_profit / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��о����׺Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��о�����'", $p2_ym);
}
if (getUniResult($query, $p2_l_current_profit) < 1) {
    $p2_l_current_profit = 0 - $p2_s_current_profit_sagaku;       // ��������
} else {
    if ($p2_ym < 201001) {
        $p2_l_current_profit = $p2_l_current_profit - $p2_s_current_profit_sagaku + $p2_l_allo_kin;
    } else {
        $p2_l_current_profit = $p2_l_current_profit - $p2_s_ope_profit_sagaku + $p2_l_allo_kin;
    }
    if ($p2_ym == 200912) {
        $p2_l_current_profit = $p2_l_current_profit - 182279;
    }
    if ($p2_ym >= 201001) {
        $p2_l_current_profit = $p2_l_current_profit - $p2_l_kyu_kin;
        //$p2_l_current_profit = $p2_l_current_profit - 151313;
    }
    if ($p2_ym == 201004) {
        $p2_l_current_profit = $p2_l_current_profit - 255240;
    }
    $p2_l_current_profit = number_format(($p2_l_current_profit / $tani), $keta);
}
    ///// �����߷�
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ηо�����'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_current_profit) < 1) {
    $rui_all_current_profit = 0;            // ��������
} else {
    if ($yyyymm >= 201002 && $yyyymm <= 201003) {
        $rui_all_current_profit = $rui_all_current_profit + 600000;
    }
    if ($yyyymm == 201003) {
        $rui_all_current_profit = $rui_all_current_profit - 600000;
    }
    $rui_all_current_profit = $rui_all_current_profit + $rui_b_uri_sagaku;
    $rui_all_current_profit = number_format(($rui_all_current_profit / $tani), $keta);
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ�о����׺Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_current_profit) < 1) {
        $rui_c_current_profit = 0;                           // ��������
    } else {
        $rui_c_current_profit = $rui_c_current_profit + $rui_b_sagaku + $rui_c_allo_kin - $rui_sc_uri_sagaku + $rui_sc_metarial_sagaku; // ���ץ��������̣
        if ($yyyymm >= 201001) {
            $rui_c_current_profit = $rui_c_current_profit - $rui_c_kyu_kin; // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
            //$rui_c_current_profit = $rui_c_current_profit - 151313; // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        }
        $rui_c_current_profit = number_format(($rui_c_current_profit / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='���ץ�о�����'");
    if (getUniResult($query, $rui_c_current_profit_a) < 1) {
        $rui_c_current_profit_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='���ץ�о����׺Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_c_current_profit_b) < 1) {
        $rui_c_current_profit_b = 0;                          // ��������
    }
    $rui_c_current_profit = $rui_c_current_profit_a + $rui_c_current_profit_b;
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_c_current_profit = $rui_c_current_profit - 1227429;
    }
    if ($yyyymm >= 201001) {
        $rui_c_current_profit = $rui_c_current_profit - $rui_c_kyu_kin; // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        //$rui_c_current_profit = $rui_c_current_profit - 151313; // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    $rui_c_current_profit = $rui_c_current_profit + $rui_b_sagaku + $rui_c_allo_kin - $rui_sc_uri_sagaku + $rui_sc_metarial_sagaku  - $rui_b_pother_a; // ���ץ��������̣
    $rui_c_current_profit = number_format(($rui_c_current_profit / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ�о�����'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_current_profit) < 1) {
        $rui_c_current_profit = 0;                           // ��������
    } else {
        $rui_c_current_profit = $rui_c_current_profit + $rui_b_sagaku + $rui_c_allo_kin - $rui_sc_uri_sagaku + $rui_sc_metarial_sagaku; // ���ץ��������̣
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_c_current_profit = $rui_c_current_profit - 1227429;
        }
        $rui_c_current_profit = number_format(($rui_c_current_profit / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥��о����׺Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_current_profit) < 1) {
        $rui_l_current_profit = 0;                           // ��������
    } else {
        //$rui_l_current_profit = $rui_l_current_profit - $rui_s_current_profit_sagaku + $rui_l_allo_kin;
        $rui_l_current_profit = $rui_l_ope_profit_temp + $rui_l_nonope_profit_sum_temp - $rui_l_nonope_loss_sum_temp;
        $rui_l_current_profit = number_format(($rui_l_current_profit / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    //$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='��˥��о�����'");
    //if (getUniResult($query, $rui_l_current_profit_a) < 1) {
    //    $rui_l_current_profit_a = 0;                          // ��������
    //}
    //$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='��˥��о����׺Ʒ׻�'", $yyyymm);
    //if (getUniResult($query, $rui_l_current_profit_b) < 1) {
    //    $rui_l_current_profit_b = 0;                          // ��������
    //}
    //$rui_l_current_profit = $rui_l_current_profit_a + $rui_l_current_profit_b;
    //if ($yyyymm >= 200912 && $yyyymm <= 201003) {
    //    $rui_l_current_profit = $rui_l_current_profit - 182279;
    //}
    //$rui_l_current_profit = $rui_l_current_profit - $rui_s_current_profit_sagaku + $rui_l_allo_kin;
    //$rui_l_current_profit = $rui_l_current_profit - $rui_s_ope_profit_sagaku + $rui_l_allo_kin;
    
    $rui_l_current_profit = $rui_l_ope_profit_temp + $rui_l_nonope_profit_sum_temp - $rui_l_nonope_loss_sum_temp;
    $rui_l_current_profit = number_format(($rui_l_current_profit / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥��о�����'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_current_profit) < 1) {
        $rui_l_current_profit = 0 - $rui_s_current_profit_sagaku;   // ��������
    } else {
        $rui_l_current_profit = $rui_l_current_profit - $rui_s_current_profit_sagaku + $rui_l_allo_kin;
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_l_current_profit = $rui_l_current_profit - 182279;
        }
        $rui_l_current_profit = number_format(($rui_l_current_profit / $tani), $keta);
    }
}

////////// �õ�����μ���
$query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='���ץ�»�׷׻���'", $yyyymm);
if (getUniResult($query,$comment_c) <= 0) {
    $comment_c = "";
}
$query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='��˥�»�׷׻���'", $yyyymm);
if (getUniResult($query,$comment_l) <= 0) {
    $comment_l = "";
}
$query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='�������»�׷׻���'", $yyyymm);
if (getUniResult($query,$comment_s) <= 0) {
    $comment_s = "";
}

$query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='���ʴ���»�׷׻���'", $yyyymm);
if (getUniResult($query,$comment_b) <= 0) {
    $comment_b = "";
}
$query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='����»�׷׻���'", $yyyymm);
if (getUniResult($query,$comment_all) <= 0) {
    $comment_all = "";
}
$query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='����¾»�׷׻���'", $yyyymm);
if (getUniResult($query,$comment_other) <= 0) {
    $comment_other = "";
}

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>
<?= $menu->out_jsBaseClass() ?>

<style type='text/css'>
<!--
select {
    background-color:teal;
    color:white;
}
textarea {
    background-color:black;
    color:white;
}
input.sousin {
    background-color:red;
}
input.text {
    background-color:black;
    color:white;
}
.pt8 {
    font:normal 8pt;
    font-family: monospace;
}
.pt10 {
    font: normal 10pt;
    font-family: monospace;
}
.pt10b {
    font:bold 10pt;
    font-family: monospace;
}
.pt12b {
    font:bold 12pt;
    font-family: monospace;
}
.title_font {
    font:bold 13.5pt;
    font-family: monospace;
}
.today_font {
    font-size: 10.5pt;
    font-family: monospace;
}
.corporate_name {
    font:bold 10pt;
    font-family: monospace;
}
.margin0 {
    margin:0%;
}
ol {
    line-height: normal;
}
pre {
    font-size: 10.0pt;
    font-family: monospace;
}
.OnOff_font {
    font-size:     8.5pt;
    font-family:   monospace;
}
-->
</style>
</head>
<!--  style='overflow-y:hidden;' -->
<body>
    <center>
<?= $menu->out_title_border() ?>
        <table width='100%' border='1' cellspacing='1' cellpadding='1'>
            <tr>
                <td colspan='2' bgcolor='#d6d3ce' align='center' class='corporate_name'>
                    <?=$menu->out_caption(), "\n"?>
                </td>
                <form method='post' action='<?php echo $menu->out_self() ?>'>
                    <td colspan='14' bgcolor='#d6d3ce' align='right' class='pt10'>
                        ñ��
                        <select name='keihi_tani' class='pt10'>
                        <?php
                            if ($tani == 1000)
                                echo "<option value='1000' selected>�����</option>\n";
                            else
                                echo "<option value='1000'>�����</option>\n";
                            if ($tani == 1)
                                echo "<option value='1' selected>������</option>\n";
                            else
                                echo "<option value='1'>������</option>\n";
                            if ($tani == 1000000)
                                echo "<option value='1000000' selected>ɴ����</option>\n";
                            else
                                echo "<option value='1000000'>ɴ����</option>\n";
                            if ($tani == 10000)
                                echo "<option value='10000' selected>������</option>\n";
                            else
                                echo "<option value='10000'>������</option>\n";
                            if($tani == 100000)
                                echo "<option value='100000' selected>������</option>\n";
                            else
                                echo "<option value='100000'>������</option>\n";
                        ?>
                        </select>
                        ������
                        <select name='keihi_keta' class='pt10'>
                        <?php
                            if ($keta == 0)
                                echo "<option value='0' selected>����</option>\n";
                            else
                                echo "<option value='0'>����</option>\n";
                            if ($keta == 3)
                                echo "<option value='3' selected>����</option>\n";
                            else
                                echo "<option value='3'>����</option>\n";
                            if ($keta == 6)
                                echo "<option value='6' selected>����</option>\n";
                            else
                                echo "<option value='6'>����</option>\n";
                            if ($keta == 1)
                                echo "<option value='1' selected>����</option>\n";
                            else
                                echo "<option value='1'>����</option>\n";
                            if ($keta == 2)
                                echo "<option value='2' selected>����</option>\n";
                            else
                                echo "<option value='2'>����</option>\n";
                            if ($keta == 4)
                                echo "<option value='4' selected>����</option>\n";
                            else
                                echo "<option value='4'>����</option>\n";
                            if ($keta == 5)
                                echo "<option value='5' selected>����</option>\n";
                            else
                                echo "<option value='5'>����</option>\n";
                        ?>
                        </select>
                        <input class='pt10b' type='submit' name='return' value='ñ���ѹ�'>
                    </td>
                </form>
            </tr>
        </table>
    <table width='100%' bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
        <tr>
        <td>
        <table width='100%' bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <TBODY>
                <tr>
                    <td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>�ࡡ������</td>
                    <td colspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>�����ס���</td>
                    <td colspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>�ꡡ�ˡ���</td>
                    <td colspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>�������</td>
                    <td colspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>���ʴ���</td>
                    <td colspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>�硡������</td>
                    <td rowspan='2' width='400' align='left' class='pt10b' bgcolor='#ffffc6'>��¤���ܷ����δ����������</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p2_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p1_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'><?php echo $yyyymm ?></td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>�ߡ���</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p2_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p1_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'><?php echo $yyyymm ?></td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>�ߡ���</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p2_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p1_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'><?php echo $yyyymm ?></td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>�ߡ���</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p2_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p1_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'><?php echo $yyyymm ?></td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>�ߡ���</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p2_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p1_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'><?php echo $yyyymm ?></td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>�ߡ���</td>
                </tr>
                <tr>
                    <td rowspan='11' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ceffce'>�ġ��ȡ�»����</td>
                    <td colspan='2' nowrap align='center' class='pt10b' bgcolor='#ceffce'>�䡡�塡��</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_c_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_c_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $c_uri ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_c_uri ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_l_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_l_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $l_uri ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_l_uri ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_s_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_s_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $s_uri ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_s_uri ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_b_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_b_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $b_uri ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_b_uri ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_all_uri ?> </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_all_uri ?> </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $all_uri ?>    </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_all_uri ?></td>
                    <td nowrap align='left'  class='pt10' bgcolor='#d6d3ce'>�º�����</td>
                </tr>
                <tr>
                    <td rowspan='6' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ffffc6'>��帶��</td> <!-- ��帶�� -->
                    <td nowrap align='left' class='pt10b' bgcolor='white'>����������ų���ê����</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_c_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_c_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $c_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_c_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_l_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_l_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $l_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_l_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_s_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_s_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $s_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_s_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_b_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_b_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $b_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_b_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_all_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_all_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $all_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_all_invent ?></td>
                    <td nowrap align='left'  class='pt10'>��ʿ��ñ���ˤ��ê����</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>��������(������)</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_c_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_c_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $c_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_c_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_l_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_l_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $l_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_l_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_s_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_s_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $s_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_s_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_b_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_b_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $b_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_b_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_all_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_all_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $all_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_all_metarial ?></td>
                    <td nowrap align='left'  class='pt10'>��ݹ�������</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>��ϫ����̳������</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_c_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_c_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $c_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_c_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_l_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_l_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $l_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_l_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_s_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_s_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $s_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_s_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_b_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_b_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $b_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_b_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_all_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_all_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $all_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_all_roumu ?></td>
                    <td nowrap align='left'  class='pt10'>�ḁ̃����ӥ������</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>���С�����������</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_c_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_c_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $c_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_c_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_l_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_l_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $l_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_l_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_s_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_s_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $s_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_s_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_b_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_b_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $b_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_b_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_all_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_all_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $all_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_all_expense ?></td>
                    <td nowrap align='left'  class='pt10'>�ã�ľ�ܷ�������Ψ</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>�����������ų���ê����</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_c_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_c_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $c_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $c_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_l_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_l_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $l_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $l_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_s_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_s_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $s_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $s_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_b_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_b_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $b_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $b_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_all_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_all_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $all_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $all_endinv ?></td>
                    <td nowrap align='left'  class='pt10'>��ʿ��ñ���ˤ��ê����</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#ffffc6'>���䡡�塡������</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_c_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_c_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $c_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_c_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_l_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_l_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $l_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_l_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_s_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_s_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $s_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_s_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_b_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_b_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $b_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_b_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_all_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_all_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $all_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_all_urigen ?></td>
                    <td nowrap align='left'  class='pt10'>�ã�ľ�ܷ�������Ψ</td>
                </tr>
                <tr>
                    <td colspan='2' nowrap align='center' class='pt10b' bgcolor='#ceffce'>�䡡�塡��������</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_c_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_c_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $c_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_c_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_l_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_l_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $l_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_l_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_s_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_s_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $s_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_s_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_b_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_b_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $b_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_b_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_all_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_all_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $all_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_all_gross_profit ?></td>
                    <td nowrap align='left'  class='pt10' bgcolor='#d6d3ce'>��</td>  <!-- ;�� -->
                </tr>
                <tr>
                    <td rowspan='3' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ffffc6'></td> <!-- �δ��� -->
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>���͡��������</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_c_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_c_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $c_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_c_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_l_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_l_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $l_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_l_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_s_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_s_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $s_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_s_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_b_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_b_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $b_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_b_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_all_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_all_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $all_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_all_han_jin ?></td>
                    <td nowrap align='left'  class='pt10'>�ã�ľ�ܵ�����Ψ</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>���С�����������</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_c_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_c_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $c_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_c_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_l_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_l_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $l_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_l_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_s_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_s_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $s_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_s_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_b_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_b_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $b_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_b_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_all_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_all_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $all_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_all_han_kei ?></td>
                    <td nowrap align='left'  class='pt10'>�ã���ͭ�����桦�ã�ľ�ܷ�������Ψ¾</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>�δ���ڤӰ��̴������</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_c_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_c_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $c_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_c_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_l_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_l_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $l_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_l_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_s_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_s_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $s_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_s_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_b_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_b_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $b_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_b_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_all_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_all_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $all_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_all_han_all ?></td>
                    <td nowrap align='left'  class='pt10'>��</td>  <!-- ;�� -->
                </tr>
                <tr>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ceffce'>�ġ����ȡ�����������</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_c_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_c_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $c_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_c_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_l_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_l_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $l_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_l_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_s_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_s_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $s_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_s_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_b_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_b_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $b_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_b_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_all_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_all_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $all_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_all_ope_profit ?></td>
                    <td nowrap align='left'  class='pt10'>��</td>  <!-- ;�� -->
                </tr>
                <tr>
                    <td rowspan='7' align='center' valign='middle' class='pt10b' bgcolor='#ceffce'>�Ķȳ�»��</td>
                    <td rowspan='4' align='center' class='pt10' bgcolor='#ffffc6'></td> <!-- ;�� -->
                    <td nowrap align='left' class='pt10b' bgcolor='white'>����̳��������</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_c_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_c_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $c_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_c_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_l_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_l_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $l_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_l_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_s_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_s_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $s_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_s_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_b_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_b_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $b_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_b_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_all_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_all_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $all_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_all_gyoumu ?></td>
                    <?php if ($yyyymm >= 201001) { ?>
                    <td nowrap align='left'  class='pt10'>����οͰ���</td>
                    <?php } else { ?>
                    <td nowrap align='left'  class='pt10'>�������Ӥ�������</td>
                    <?php } ?>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>���š������䡡��</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_c_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_c_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $c_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_c_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_l_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_l_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $l_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_l_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_s_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_s_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $s_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_s_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_b_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_b_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $b_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_b_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_all_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_all_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $all_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_all_swari ?></td>
                    <?php if ($yyyymm >= 201001) { ?>
                    <td nowrap align='left'  class='pt10'>����οͰ���</td>
                    <?php } else { ?>
                    <td nowrap align='left'  class='pt10'>�������Ӥ�������</td>
                    <?php } ?>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>���������Ρ���¾</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_c_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_c_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $c_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_c_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_l_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_l_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $l_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_l_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_s_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_s_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $s_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_s_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_b_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_b_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $b_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_b_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_all_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_all_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $all_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_all_pother ?></td>
                    <?php if ($yyyymm >= 201001) { ?>
                    <td nowrap align='left'  class='pt10'>����οͰ���</td>
                    <?php } else { ?>
                    <td nowrap align='left'  class='pt10'>�������Ӥ�������</td>
                    <?php } ?>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#ffffc6'>���Ķȳ����� ��</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_c_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_c_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $c_nonope_profit_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_c_nonope_profit_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_l_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_l_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $l_nonope_profit_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_l_nonope_profit_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_s_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_s_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $s_nonope_profit_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_s_nonope_profit_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_b_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_b_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $b_nonope_profit_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_b_nonope_profit_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_all_nonope_profit_sum ?> </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_all_nonope_profit_sum ?> </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $all_nonope_profit_sum ?>    </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_all_nonope_profit_sum ?></td>
                    <td nowrap align='left'  class='pt10'>��</td> <!-- ;�� -->
                </tr>
                <tr>
                    <td rowspan='3' align='center' class='pt10' bgcolor='#ffffc6'></td> <!-- ;�� -->
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>���١�ʧ������©</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_c_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_c_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $c_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_c_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_l_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_l_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $l_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_l_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_s_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_s_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $s_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_s_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_b_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_b_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $b_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_b_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_all_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_all_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $all_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_all_srisoku ?></td>
                    <?php if ($yyyymm >= 201001) { ?>
                    <td nowrap align='left'  class='pt10'>����οͰ���</td>
                    <?php } else { ?>
                    <td nowrap align='left'  class='pt10'>�������Ӥ�������</td>
                    <?php } ?>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>���������Ρ���¾</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_c_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_c_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $c_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_c_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_l_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_l_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $l_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_l_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_s_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_s_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $s_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_s_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_b_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_b_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $b_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_b_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_all_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_all_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $all_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_all_lother ?></td>
                    <?php if ($yyyymm >= 201001) { ?>
                    <td nowrap align='left'  class='pt10'>����οͰ���</td>
                    <?php } else { ?>
                    <td nowrap align='left'  class='pt10'>�������Ӥ�������</td>
                    <?php } ?>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#ffffc6'>���Ķȳ����� ��</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_c_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_c_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $c_nonope_loss_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_c_nonope_loss_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_l_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_l_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $l_nonope_loss_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_l_nonope_loss_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_s_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_s_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $s_nonope_loss_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_s_nonope_loss_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_b_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_b_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $b_nonope_loss_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_b_nonope_loss_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_all_nonope_loss_sum ?> </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_all_nonope_loss_sum ?> </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $all_nonope_loss_sum ?>    </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_all_nonope_loss_sum ?></td>
                    <td nowrap align='left'  class='pt10'>��</td> <!-- ;�� -->
                </tr>
                <tr>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ceffce'>�С��������������</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_c_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_c_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $c_current_profit ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_c_current_profit ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_l_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_l_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $l_current_profit ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_l_current_profit ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_s_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_s_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $s_current_profit ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_s_current_profit ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_b_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_b_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $b_current_profit ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_b_current_profit ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_all_current_profit ?> </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_all_current_profit ?> </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $all_current_profit ?>    </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_all_current_profit ?></td>
                    <td nowrap align='left'  class='pt10'>��</td>  <!-- ;�� -->
                </tr>
            </TBODY>
        </table>
        </td>
        </tr>
        <tr>
        <td>
        <table width='100%' bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <tbody>
                <tr>
                    <td colspan='20' bgcolor='white' align='left' class='pt10b'><a href='<%=$menu->out_action('�õ���������')%>?<?php echo uniqid('menu') ?>' style='text-decoration:none; color:black;'>�������»���õ�����</a></td>
                </tr>
                <tr>
                    <td colspan='20' bgcolor='white' class='pt10'>
                        <ol>
                        <?php
                            if ($comment_c != "") {
                                echo "<li><pre>$comment_c</pre></li>\n";
                            }
                            if ($comment_l != "") {
                                echo "<li><pre>$comment_l</pre></li>\n";
                            }
                            if ($comment_s != "") {
                                echo "<li><pre>$comment_s</pre></li>\n";
                            }
                            if ($comment_b != "") {
                                echo "<li><pre>$comment_b</pre></li>\n";
                            }
                            if ($comment_all != "") {
                                echo "<li><pre>$comment_all</pre></li>\n";
                            }
                            if ($comment_other != "") {
                                echo "<li><pre>$comment_other</pre></li>\n";
                            }
                        ?>
                        </ol>
                    </td>
                </tr>
            </tbody>
        </table>
        </td>
        </tr>
    </table>
    </center>
</body>
</html>
