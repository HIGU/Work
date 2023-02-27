<?php
//////////////////////////////////////////////////////////////////////////////
// Tnk Web site ���̤Υ롼�����������(Server/DB/Directory/menu)��        //
// Copyright (C) 2001-2014 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2001/07/07 Created   define.php                                          //
// 2002/07/25 WEB_HOST �� DB_HOST ʣ�������С��Ķ����б�                    //
// 2002/08/07 �Ұ���������Υǥ��쥯�ȥ��ѹ�                                //
// 2003/04/21 AUTH_LEVEL �� AUTH_LEBEL �˥ߥ������פ��Ƥ����Τ�����         //
// 2003/04/23 FUNC_STATISTIC=21 ���Ȱ������׾����ɲ�                        //
// 2003/11/17 AS400FTP �Υ��ɥ쥹���ɲ� �ƥ��å���층������              //
// 2003/12/11 �ǥ��쥯�ȥ�����ȥ롼�ȥ�˥塼������ɲ�                    //
// 2003/12/22 SITE_INDEX��SITE_ID��������ɲ�  �Ұ������emp_define.php��   //
// 2004/03/04 CLI�Ǥ�$_SERVER['HTTP_HOST']��Index��¸�ߤ��ʤ�����check�ɲ�  //
// 2004/07/20 Error Page �ѤΥǥ��쥯�ȥ�������ɲ�                         //
// 2005/01/18 SITE_ICON �ǥ��쥯�ȥꡦ�ե����롦V=3 ̾������ɲ�            //
// 2005/07/15 INDEX_REGU �� ���⵬���˥塼 �ɲ�                           //
// 2005/08/28 JS_BASE_CLASS �� �����ȶ���JavaScript�١������饹�ե������ɲ� //
// 2005/09/23 DB�� masterDB(10.1.3.247) ���ѹ�(DB�����С�ʬΥ)              //
// 2007/03/27 define('INDEX_EQIP', 2) �� define('INDEX_EQUIP', 40) ���ѹ�   //
// 2008/08/29 INDEX_QUALITY �� �ʼ���˥塼 �ɲ�                            //
//            define('INDEX_QUALITY',  70)                             ��ë //
// 2010/03/13 AS400�ҳ��к��ƥ��ȤΤ���AS400_HOST����10.1.3.99���ѹ� ��ë //
// 2010/09/30 INDEX_ASSET �� �񻺴�����˥塼 �ɲ�                          //
//            define('INDEX_ASSET',  80)                               ��ë //
// 2012/03/10 AS400�ҳ��к��ƥ��ȤΤ���AS400_HOST����10.1.3.99���ѹ� ��ë //
// 2012/11/17 AS400�ҳ��к��ƥ��ȤΤ���AS400_HOST����10.1.3.99���ѹ� ��ë //
// 2013/05/06 AS400VerUP�Τ��ᡢAS400_HOST����10.1.3.99���ѹ� ��ë        //
// 2013/11/11 INDEX_PER_APPLI �� �ϽС��������˥塼�ʿͻ��� �ɲ�          //
//            define('INDEX_PER_APPLI',  97)                                //
//            INDEX_PER_APPLI �� �ϽС��������˥塼�ʷ����� �ɲ�          //
//            define('INDEX_PER_APPLI',  98)                           ��ë //
// 2013/11/16 AS400�ҳ��к��ƥ��ȤΤ���AS400_HOST����10.1.3.99���ѹ� ��ë //
// 2013/12/04 ASSET_MENU���ְ�äƤ����Τǽ���                         ��ë //
// 2014/08/02 ��AS���إƥ��ȤΤ���AS400_HOST����10.1.100.170���ѹ�   ��ë //
// 2014/09/19 �������ϽХ�˥塼�����¾���ѹ�                         ��ë //
// 2014/11/15 AS400�ҳ��к��ƥ��ȤΤ���AS400_HOST����10.1.2.76���ѹ� ��ë //
//////////////////////////////////////////////////////////////////////////////
    /* �����С����� */
    if (isset($_SERVER['HTTP_HOST'])) {     // CLI�Ǥ��б��Τ����ɲ�
        define('WEB_HOST',   '//' . $_SERVER['HTTP_HOST'] . '/');   // ���ѥۥ���̾
        define('H_WEB_HOST', 'http://' . $_SERVER['HTTP_HOST']);    // header()�ѥۥ���̾
        define('LH_WEB_HOST', 'Location: http://' . $_SERVER['HTTP_HOST']);// header()�ѥ���������եۥ���̾
    }
    define('WEB_DOMAIN', 'tnk.co.jp');
    if (@$_SERVER['SERVER_ADDR'] == '10.1.1.252') {         // CLI�Ǥ��б��Τ���@��error�޻�
        define('DB_HOST',    '10.1.3.247');
    } elseif (file_exists('/usr/local/masterst_cli_flg')) { // CLI�Ǥ��б��Τ����ɲ�
        define('DB_HOST',    '10.1.3.247');
    } else {
        define('DB_HOST',    '127.0.0.1');  // ��ȯ�Ѥ�backup�ѤΥޥ�����ϼ�ʬ��DB��Ȥ�
    }
    /*****
    define('DB_HOST',    '127.0.0.1');
    *****/
    define('DB_PORT',    '5432');
    define('DB_NAME',    'TnkSQL');
    define('DB_USER',    'nobody');
    define('DB_PASSWD',  '');
    define('TEMP_DIR',   '/tmp/');
    define('AS400_HOST', '10.1.1.252');           // �ҳ�����10.1.2.76���ѹ���TNK�Хå����å׵��˸���10.1.1.252
    //define('AS400_HOST', '10.1.2.76');           // �ҳ�����10.1.2.76���ѹ���TNK�Хå����å׵��˸���10.1.1.252
    //define('AS400_HOST', '10.1.1.102');           // �ڤ��ؤ��ƥ�����
    define('AS400_USER', 'FTPUSR');
    define('AS400_PASS', 'AS400FTP');
    
    ///////////////// �ǥ��쥯�ȥ�����ȥ�˥塼���
    /* apache �� home ����Υѥ� */
    define('ROOT', '/');                // ����
    
    /* �ȥåץ�˥塼 */
    define('TOP',       ROOT);                  define('TOP_MENU',      TOP.     'menu.php');
    /* �����ȥ�˥塼 */
    define('SITE',      ROOT);                  define('SITE_MENU',     SITE.    'menu_site.php');
    /* ������˥塼 */
    define('INDUST',    ROOT.'industry/');      define('INDUST_MENU',   INDUST.  'industry_menu.php');
    /* ����˥塼 */
    define('SALES',     ROOT.'sales/');         define('SALES_MENU',    SALES.   'sales_menu.php');
    /* ������˥塼 */
    define('EQUIP',     ROOT.'equipment/');     define('EQUIP_MENU',    EQUIP.   'equipment_menu.php');
    define('EQUIP2',    ROOT.'equip/');         define('EQUIP_MENU2',   EQUIP2.  'equip_menu.php');
    define('EQUIP3',    ROOT.'equip/');         define('EQUIP_MENU3',   EQUIP2.  'equip_menu_moni.php');
    /* �Ұ���˥塼 */
    define('EMP',       ROOT.'emp/');           define('EMP_MENU',      EMP.     'emp_menu.php');
    define('EMP2',      ROOT.'emp2/');          define('EMP_MENU2',     EMP2.     'emp_menu.php');
    /* »�ץ�˥塼 */
    define('PL',        ROOT.'kessan/');        define('PL_MENU',       PL.      'pl_menu.php');
    /* ������˥塼 */
    define('ACT',       ROOT.'account/');       define('ACT_MENU',      ACT.     'act_menu.php');
    /* ������˥塼 */
    define('COST',      ROOT.'costAct/');       define('COST_MENU',     COST.    'costAct_menu.php');
    /* ��ȯ��˥塼 */
    define('DEV',       ROOT.'devReq/');        define('DEV_MENU',      DEV.     'dev_req_menu.php');
    /* ������˥塼 */
    define('SYS',       ROOT.'system/');        define('SYS_MENU',      SYS.     'system_menu.php');
    /* ���⵬���˥塼 */
    define('REGU',      ROOT.'regulation/');    define('REGU_MENU',     REGU.    'regulation_menu.php');
    /* �ʼ���˥塼 */
    define('QUALITY',   ROOT.'quality/');       define('QUALITY_MENU',  QUALITY. 'quality_menu.php');
    /* �ʼ���˥塼 */
    define('ASSET',   ROOT.'asset/');           define('ASSET_MENU',  ASSET. 'assets_menu.php');
    /* �ϽС��������˥塼(�ͻ�) */
    define('PER_APPLI',     ROOT.'per_appli/'); define('PER_APPLI_MENU',  PER_APPLI. 'per_appli_menu.php');
    /* �ϽС��������˥塼(����¾) */
    define('ACT_APPLI',     ROOT.'act_appli/'); define('ACT_APPLI_MENU',  ACT_APPLI. 'act_appli_menu.php');
    
    /* Error Page �ǥ��쥯�ȥ� */
    define('ERROR',     ROOT.'error/');
    /* TEST�ǥ��쥯�ȥ� */
    define('TEST',      ROOT.'test/');
    /* ��˥塼��CSS�ե����� */
    define('MENU_FORM', ROOT.'menu_form.css');
    /* �����ȶ���JavaScript �١������饹 �ե����� */
    define('JS_BASE_CLASS', ROOT.'base_class.js');
    /* �����ե����� */
    define('IMG',       ROOT.'img/');
    /* �����ȥ�˥塼�Υ������� �ե����� */
    // define('SITE_ICON_ON',  IMG.'site_icon1_on.gif?v=2');  // �쥿����
    // define('SITE_ICON_OFF', IMG.'site_icon1_off.gif?v=2'); // �쥿����
    define('SITE_ICON_ON',  IMG.'tnk_icon_on.gif?v=3');
    define('SITE_ICON_OFF', IMG.'tnk_icon_off.gif?v=3');
    
    
    ///////////////// SITE_INDEX�����SITE_ID���
    /* SITE_INDEX */
    define('INDEX_TOP',          0);        // �ȥåץ�˥塼
    define('INDEX_SALES',        1);        // ����˥塼
    define('INDEX_EMP',          3);        // �Ұ���˥塼
    define('INDEX_DEV',          4);        // ��ȯ��˥塼
    define('INDEX_PL',          10);        // »�ץ�˥塼
    define('INDEX_ACT',         20);        // ������˥塼
    define('INDEX_INDUST',      30);        // ������˥塼
    define('INDEX_EQUIP',       40);        // ������˥塼
    define('INDEX_COST',        50);        // ������˥塼
    define('INDEX_REGU',        60);        // ���⵬���˥塼
    define('INDEX_QUALITY',     70);        // �ʼ���˥塼
    define('INDEX_ASSET',       80);        // �񻺴�����˥塼
    define('INDEX_PER_APPLI',   97);        // �ϽС��������˥塼�ʿͻ���
    define('INDEX_ACT_APPLI',   98);        // �ϽС��������˥塼�ʷ���¾��
    define('INDEX_SYS',         99);        // ������˥塼
    define('INDEX_LOGOUT',     999);        // ��λ(logout)
    /* SITE_ID */
        // �ȥåץ�˥塼
    define('ID_TOP_TEL',                  1);       // �ƥ���Ū��TNK����ɽ
        // ������˥塼
    define('ID_INDUST_MATE_VIEW',        20);       // �������ξȲ�
    define('ID_INDUST_MATE_ENTRY',       21);       // ����������Ͽ
    define('ID_INDUST_A_DEN',            13);       // ��������Ȳ�
    define('ID_INDUST_PAYABLE',          10);       // ��ݼ��ӤξȲ�
    define('ID_INDUST_PROVIDE',          11);       // �ٵ�ɽ�ξȲ�
    define('ID_INDUST_OPLAN',            12);       // ȯ��ײ�ξȲ�
    define('ID_INDUST_C_INVENT',         35);       // ���ץ�ê���ξȲ�
    define('ID_INDUST_L_INVENT',         36);       // ��˥�ê���ξȲ�
    define('ID_INDUST_VENDOR',           22);       // ȯ����ޥ������ξȲ�
    define('ID_INDUST_PURCHASE',         31);       // ������ۤξȲ�
    define('ID_INDUST_C_TOKU',           32);       // ���ץ�����ê���ξȲ�
    define('ID_INDUST_BIMOR_INV',        34);       // �Х����ê���ξȲ�
        // ����˥塼
    define('ID_SALES_VIEW',              11);       // ������پȲ�(new)
    define('ID_SALES_URIAGE',             1);       // �����ӾȲ�
    define('ID_SALES_URI_TMP',            2);       // ������پȲ�(��������Ψ�ʲ�)
    define('ID_SALES_GRAPH_DAILY',        3);       // ��奰�������
    define('ID_SALES_GRAPH_MONTHLY',      4);       // ��奰��շ��
    define('ID_SALES_GRAPH_PRO_PAT',      5);       // ��奰������ʡ����ʤ���Ψ
    define('ID_SALES_GRAPH_CL',           6);       // ��奰��� ���ץ顦��˥�
    define('ID_SALES_GRAPH_C_TOKU',       9);       // ��奰��� ���ץ顦������
    define('ID_SALES_PL_QUERY',           8);       // �»�׾Ȳ�
        // ������˥塼
    
    
?>
