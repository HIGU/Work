<?php

/**--------------------
 * �ǥ����������
 */

/**
 * ���Τ��طʿ�
 */
define("APP_HTML_BGCOLOR","#FFFFFF");

/**
 * �Ȥ��طʿ�
 */
define("APP_HTML_WAKUBGCOLOR","white");

/**
 * �ǥ�������ꥫ�����ޥ������������� view/template/ �ǥ��쥯�ȥ�ˤ���ե�������Խ����ޤ�
 * CSS���ҥե������ view/template/parts/header.php �Ǥ�
 */

// -----------------------

/**
 * �����ǡ�����¸�ǥ��쥯�ȥ�
 */
define("APP_DATA_DIR","data/");

/**
 * �ֿ������ǥ��쥯�ȥ�
 */
define("APP_RES_DIR","res/");

/**
 * �����ǡ����ե�����̾
 */
define("APP_DATA_FILE",APP_DATA_DIR."data.cgi");

/**
 * �ǡ���ɽ�����
 */
define("APP_DATA_VIEW_COUNT",10);

/**
 * �ǡ�����¸������
 */
define("APP_DATA_SAVE_MAX",2000);

/**
 * rss���ϥǡ������
 */
define("APP_RSS_VIEW_COUNT",10);

/**
 * javascript���ϥǡ������
 */
define("APP_JS_VIEW_COUNT",10);

/**
 * �����ֿ����˵����գ�(1:�����գФ���,0:�����գФ��ʤ�)
 */
define("APP_KIJI_UP",1);

/**
 * title������˵��Ҥ��륿���ȥ�
 */
define("APP_TITLE", "���ץ������ �Ǽ���");

/**
 * �ǡ������������ - ǧ�ڥ桼��
 * ! ɬ���ѹ����Ƥ�������
 */
$_APP_AUTH_USER = array(
'k_kobayashi' => 'bbs',
'n_ooya' => 'bbs'
);

/**--------------------------------
 * �᡼����Ƶ�ǽ����Ѥ���(0:�Ȥ�ʤ�;1:�Ȥ�)
 */
define("APP_MAIL_POST",0);

/**
 * �᡼��������ѤκݤΥ᡼�륵����̾
 */
define("APP_MAIL_HOST","mx.server.jp");

/**
 * �᡼��������ѤκݤΥ桼���ɣ�
 */
define("APP_MAIL_UID","userid");

/**
 * �᡼��������ѤκݤΥѥ����
 */
define("APP_MAIL_PASS","password");

/**
 * �᡼������ѤΥ᡼�륢�ɥ쥹
 */
define("APP_MAIL_ADDR","mail@mail.com");

// -----------------------------------

/** ---------------------
 * ����å���ڡ�����Ƭ��
 */
define("APP_PAGE_PREFIX","im");

?>