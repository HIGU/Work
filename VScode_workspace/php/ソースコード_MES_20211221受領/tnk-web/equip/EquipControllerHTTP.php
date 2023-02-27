<?php
//////////////////////////////////////////////////////////////////////////////
// ������Ư���� MVC �� Controller ����ɬ�פʥ��饹 HTTP ��                  //
// Copyright (C) 2005-2018 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/07/28 Created   EquipControllerHTTP.php                             //
//            Request Class �� Result Class �Ϥ��Τޤ�Sssion Class���ĥ����//
// 2018/05/18 ��������ɲá������ɣ�����Ͽ����Ū��7���ѹ�            ��ë //
// 2018/12/25 �������﫤�SUS��ʬΥ���塹�ΰ١�                      ��ë //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
require_once ('/home/www/html/tnk-web/ControllerHTTP_Class.php');   // ����HTTP�ѥ���ȥ��륯�饹


/***********
if (SESSION_VERSION != '1.00') {
    $_SESSION['s_sysmsg'] = '���å���󥯥饹�ΥС�����󤬰㤤�ޤ���';
    return;
}
***********/
/****************************************************************************
*                       sub class ��ĥ���饹�����                          *
****************************************************************************/
class equipSession extends Session
{
    ///// Private properties
    private $factory;
    private $factory_name;
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ///// Constructer ����� (php5�ذܹԻ��� __construct() ���ѹ�ͽ��) (�ǥ��ȥ饯��__destruct())
    public function __construct()
    {
        $this->factory = $this->get('factory');
        $this->factory_name = $this->getFactoryName($this->factory);
    }
    /*************************** Get methods ************************/
    public function getFactoryName($factory)
    {
        switch ($factory) {
        case 1:
            $fact_name = '������';
            break;
        case 2:
            $fact_name = '������';
            break;
        case 4:
            $fact_name = '������';
            break;
        case 5:
            $fact_name = '������';
            break;
        case 6:
            $fact_name = '������';
            break;
        case 7:
            $fact_name = '������(���)';
            break;
        case 8:
            $fact_name = '������(SUS)';
            break;
        default:
            $fact_name = '������';
            break;
        }
        return $fact_name;
    }
    public function getFactory()
    {
        return $this->factory;
    }
    public function getFactName()
    {
        return $this->factory_name;
    }
} // Class Session End

?>
