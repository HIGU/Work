<?php
//////////////////////////////////////////////////////////////////////////////
// ���߸����� ���ʼ�η�ʿ�ѽи˿�����ͭ������Ȳ�           MVC Model �� //
// Copyright (C) 2007-2016 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/05/23 Created   inventory_average_Model.php                         //
// 2007/06/09 �ޥ�����̤��Ͽ(��ʸ��ɽ��)�Υ�å��������ɲá��ǿ�ñ���˥��//
//            mb_convert_kana��'k'��'ksa'���ѹ�(����̾�ȿƵ���)             //
// 2007/06/10 table index��(div, parts_no)���ɲä�SetInitWhere()�᥽�å��ѹ�//
// 2007/06/14 �װ��ޥ��������Խ��������ȡ��װ�����Ͽ�Խ� ��Ϣ ��λ        //
//            editFactor()�᥽�åɤ�SJIS��EUC-JP���Ѵ����ʤ���ʸ������Ĵ����//
// 2007/07/03 ��ͭ���� ORDER BY ��ͭ�� DESC, �߸˶�� DESC ���ɲ�         //
// 2007/07/11 �����ֹ�(searchPartsNo)��LIKE�����ɲá�                       //
// 2007/07/23 ��ͭ��λ�����ɲ�(�ե��륿����ǽ) targetHoldMonth            //
// 2016/06/24 CSV�����ɲäΤ���SQL��WHERE�������Ϥ���                ��ë //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��

require_once ('../../../ComTableMntClass.php');     // TNK ������ �ơ��֥����&�ڡ�������Class


/*****************************************************************************************
*       MVC��Model�� ���饹��� ComTableMnt(class) ���ѥơ��֥���ƥ��饹���ĥ        *
*****************************************************************************************/
class InventoryAverage_Model extends ComTableMnt
{
    ///// Private properties
    private $where;                             // ���� SQL��WHERE��
    private $order;                             // ���� SQL��ORDER��
    private $totalMsg;                          // �եå�����������׷������׶��
    
    ///// public properties
    // public  $graph;                             // GanttChart�Υ��󥹥���
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer ����� (php5�ذܹԻ��� __construct() ���ѹ�ͽ��) (�ǥ��ȥ饯��__destruct())
    public function __construct($request)
    {
        ///// ��ץ쥳���ɿ�����SQL����
        switch ($request->get('showMenu')) {
        case 'List':                                        // ������ʤ���ͭ�����Υꥹ��
            $this->where = $this->SetInitWhere($request);
            $this->order = $this->SetInitOrder($request);
            $sql_sum = "SELECT count(*) FROM inventory_average_summary {$this->where}";
            break;
        case 'Comment':                                     // �����ȡ��װ��ơ��֥���Խ�
            $this->where = '';
            $this->order = '';
            $sql_sum = "SELECT count(*) FROM inventory_average_comment";
            break;
        case 'FactorMnt':                                   // �װ��ޥ������ξȲ��Խ�
            $this->where = '';
            $this->order = '';
            $sql_sum = "SELECT count(*) FROM inventory_average_factor";
            break;
        case 'CondForm':
        case 'Both':
        default:
            $this->where = '';
            return;
        }
        $log_file = 'inventory_average.log';
        ///// Constructer ���������� ���쥯�饹�� Constructer���¹Ԥ���ʤ�
        ///// ����Class��Constructer�ϥץ���ޡ�����Ǥ�ǸƽФ�
        parent::__construct($sql_sum, $request, $log_file, 1000);
    }
    
    ////////// MVC �� Model ���η�� ɽ���ѤΥǡ�������
    ///// List��    ���߸����������оݤ���ͭ�����λ�����Ǥ� ����ɽ
    public function outViewListHTML($request, $menu, $pageParameter, $session)
    {
        /************************* �إå��� ***************************/
        // �����HTML�إå��������������
        $listHTML  = $this->getViewHTMLconst('header');
        // ��������HTML�ܥǥ������������
        $listHTML .= $this->getViewHTMLheader($request, $menu, $pageParameter);
        // �����HTML�եå��������������
        $listHTML .= $this->getViewHTMLconst('footer');
        // HTML�ե��������
        $file_name = "list/inventory_average_ViewListHeader-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
        /************************* �ܥǥ� ***************************/
        // �����HTML�إå��������������
        $listHTML  = $this->getViewHTMLconst('header');
        // ��������HTML�ܥǥ������������
        $listHTML .= $this->getViewHTMLbody($request, $menu, $session);
        // �����HTML�եå��������������
        $listHTML .= $this->getViewHTMLconst('footer');
        // HTML�ե��������
        $file_name = "list/inventory_average_ViewListBody-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
        /************************* �եå��� ***************************/
        // �����HTML�إå��������������
        $listHTML  = $this->getViewHTMLconst('header');
        // ��������HTML�ܥǥ������������
        $listHTML .= $this->getViewHTMLfooter();
        // �����HTML�եå��������������
        $listHTML .= $this->getViewHTMLconst('footer');
        // HTML�ե��������
        $file_name = "list/inventory_average_ViewListFooter-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
        return ;
    }
    
    ///// List��    �װ����ܥޥ������� ����ɽ
    public function outViewFactorHTML($request, $menu, $session)
    {
        /************************* �ܥǥ� ***************************/
        // �����HTML�إå��������������
        $listHTML  = $this->getViewHTMLconst('header');
        // ��������HTML�ܥǥ������������
        $listHTML .= $this->getViewFactorHTMLbody($request, $menu, $session);
        // �����HTML�եå��������������
        $listHTML .= $this->getViewHTMLconst('footer');
        // HTML�ե��������
        $file_name = "factor/inventory_average_ViewFactorBody-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
        return ;
    }
    
    ///// ���ʤΥ����Ȥ���¸
    public function commentSave($request, $result, $session)
    {
        // �����ȤΥѥ�᡼���������å�(���Ƥϥ����å��Ѥ�)
        // if ($request->get('comment') == '') return;  // �����Ԥ��Ⱥ���Ǥ��ʤ�
        if ($request->get('targetPartsNo') == '') return;
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        // ̤��������������������å�
        if ($request->get('targetFactor') == '') {
            $reg_factor = 'NULL';
        } else {
            $reg_factor = $request->get('targetFactor');
        }
        // ��������Υ֥饦�������դ���CR����ʧ��
        $comment = str_replace("\r", '', $request->get('comment'));
        $query = "SELECT comment, factor FROM inventory_average_comment WHERE parts_no='{$request->get('targetPartsNo')}'";
        if ($this->getResult($query, $res) < 1) {
            if ($comment == '' && $request->get('targetFactor') == '') {
                // �ǡ���̵��
                $result->add('AutoClose', 'G_reloadFlg=false; window.close();'); // ��Ͽ�� �ƤΥ���ɤϤ��ʤ���Window��λ
                return;
            }
            $sql = "
                INSERT INTO inventory_average_comment (parts_no, comment, factor, last_date, last_user)
                VALUES ('{$request->get('targetPartsNo')}', '{$comment}', {$reg_factor}, '{$last_date}', '{$last_user}')
            ";
            if ($this->execute_Insert($sql) <= 0) {
                $_SESSION['s_sysmsg'] = "�����ֹ桧{$request->get('targetPartsNo')}\\n\\n�װ��ڤӥ����Ȥ���Ͽ������ޤ���Ǥ�����������ô���Ԥ�Ϣ���Ʋ�������";
            } else {
                $_SESSION['s_sysmsg'] = "�����ֹ桧{$request->get('targetPartsNo')}\\n\\n�װ��ڤӥ����Ȥ���Ͽ���ޤ�����";
            }
        } else {
            $saveSQL = "SELECT * FROM inventory_average_comment WHERE parts_no='{$request->get('targetPartsNo')}'";
            if ($comment == '' && $request->get('targetFactor') == '') {
                // �����Ȥ����Ƥ��������ƹ����ξ��ϡ��¥쥳���ɤ���
                $sql = "DELETE FROM inventory_average_comment WHERE parts_no='{$request->get('targetPartsNo')}'";
                if ($this->execute_Delete($sql, $saveSQL) <= 0) {
                    $_SESSION['s_sysmsg'] = "�����ֹ桧{$request->get('targetPartsNo')}\\n\\n�װ��ڤӥ����Ȥκ��������ޤ���Ǥ�����������ô���Ԥ�Ϣ���Ʋ�������";
                } else {
                    $_SESSION['s_sysmsg'] = "�����ֹ桧{$request->get('targetPartsNo')}\\n\\n�װ��ڤӥ����Ȥ������ޤ�����";
                }
            } elseif ($res[0]['comment'] == $comment && $res[0]['factor'] == $request->get('targetFactor')) {
                // �ѹ�̵��
                $result->add('AutoClose', 'G_reloadFlg=false; window.close();'); // ��Ͽ�� �ƤΥ���ɤϤ��ʤ���Window��λ
                return;
            } else {
                $sql = "
                    UPDATE inventory_average_comment SET comment='{$comment}', factor={$reg_factor},
                    last_date='{$last_date}', last_user='{$last_user}'
                    WHERE parts_no='{$request->get('targetPartsNo')}'
                ";
                if ($this->execute_Update($sql, $saveSQL) <= 0) {
                    $_SESSION['s_sysmsg'] = "�����ֹ桧{$request->get('targetPartsNo')}\\n\\n�װ��ڤӥ����Ȥ��ѹ�������ޤ���Ǥ�����������ô���Ԥ�Ϣ���Ʋ�������";
                } else {
                    $_SESSION['s_sysmsg'] = "�����ֹ桧{$request->get('targetPartsNo')}\\n\\n�װ��ڤӥ����Ȥ��ѹ����ޤ�����";
                }
            }
        }
        $session->add('regParts', $request->get('targetPartsNo'));  // �ޡ������ڤӥ������Ѥ���Ͽ
        $result->add('AutoClose', 'window.close();'); // ��Ͽ�� Window��λ
        return;
    }
    
    ///// ���ʤΥ����Ȥ����
    public function getComment($request, $result)
    {
        // �����ȤΥѥ�᡼���������å�(���Ƥϥ����å��Ѥ�)
        if ($request->get('targetPartsNo') == '') return '';
        $query = "
            SELECT  comment  ,
                    trim(substr(midsc, 1, 20))
            FROM miitem LEFT OUTER JOIN
            inventory_average_comment ON(mipn=parts_no)
            WHERE mipn='{$request->get('targetPartsNo')}'
        ";
        $res = array();
        if ($this->getResult2($query, $res) > 0) {
            $result->add('comment', $res[0][0]);
            $result->add('parts_name', $res[0][1]);
            $result->add('title', "{$request->get('targetPartsNo')}��{$res[0][1]}");
            return true;
        } else {
            return false;
        }
    }
    
    ///// �װ��ޥ������� select options �����
    public function getFactorOptions($request, $result)
    {
        // �����ȤΥѥ�᡼���������å�(���Ƥϥ����å��Ѥ�)
        if ($request->get('targetPartsNo') == '') return '';
        $query = "
            SELECT factor FROM inventory_average_comment WHERE parts_no='{$request->get('targetPartsNo')}'
        ";
        $factor = '';
        $this->getUniResult($query, $factor);
        $query = "
            SELECT factor, factor_name, factor_explanation, active FROM inventory_average_factor ORDER BY factor ASC
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) < 1) {
            return;
        }
        $options1 = "\n";
        $options2 = "\n";
        for ($i=0; $i<$rows; $i++) {
            if ($res[$i][0] == $factor) {
                $options1 .= "<option value='{$res[$i][0]}' selected>{$res[$i][1]}</option>\n";
                $options2 .= "<option value='{$res[$i][0]}' selected>{$res[$i][2]}</option>\n";
            } elseif ($res[$i][3] == 'f') {
                continue;
            } else {
                $options1 .= "<option value='{$res[$i][0]}'>{$res[$i][1]}</option>\n";
                $options2 .= "<option value='{$res[$i][0]}'>{$res[$i][2]}</option>\n";
            }
        }
        $options1 .= "<option value='' style='color:red;'>��Ͽ���ʤ�</option>\n";
        $options2 .= "<option value='' style='color:red;'>��Ͽ���Ƥ�����Ϻ�����ޤ���</option>\n";
        $result->add('factorNameOptions', $options1);
        $result->add('factorExplanationOptions', $options2);
    }
    
    /*************** ComTableMntClass �� Out methods �򥪡��С��饤�� ****************/
    public function out_pageRecOptions_HTML($default=20)
    {
        if (!is_numeric($default)) $default = 20;
        $Options = '';
        switch ($default) {
        case    5:
        case   10:
        case   15:
        case   20:
        case   30:
        case   50:
        case  100:
        case  500:
        case 1000:
        case 2000:
        case 4000:
        case 8000:
        case 12000:
            break;
        default:
            $Options .= "<option value='{$default}' selected>{$default}��</option>";
        }
        if ($default == 5) {
            $Options .= "<option value='5' selected>5��</option>";
        } else {
            $Options .= "<option value='5'>5��</option>";
        }
        if ($default == 10) {
            $Options .= "<option value='10' selected>10��</option>";
        } else {
            $Options .= "<option value='10'>10��</option>";
        }
        if ($default == 15) {
            $Options .= "<option value='15' selected>15��</option>";
        } else {
            $Options .= "<option value='15'>15��</option>";
        }
        if ($default == 20) {
            $Options .= "<option value='20' selected>20��</option>";
        } else {
            $Options .= "<option value='20'>20��</option>";
        }
        if ($default == 30) {
            $Options .= "<option value='30' selected>30��</option>";
        } else {
            $Options .= "<option value='30'>30��</option>";
        }
        if ($default == 50) {
            $Options .= "<option value='50' selected>50��</option>";
        } else {
            $Options .= "<option value='50'>50��</option>";
        }
        if ($default == 100) {
            $Options .= "<option value='100' selected>100��</option>";
        } else {
            $Options .= "<option value='100'>100��</option>";
        }
        if ($default == 500) {
            $Options .= "<option value='500' selected>500��</option>";
        } else {
            $Options .= "<option value='500'>500��</option>";
        }
        if ($default == 1000) {
            $Options .= "<option value='1000' selected>1000��</option>";
        } else {
            $Options .= "<option value='1000'>1000��</option>";
        }
        if ($default == 2000) {
            $Options .= "<option value='2000' selected>2000��</option>";
        } else {
            $Options .= "<option value='2000'>2000��</option>";
        }
        if ($default == 4000) {
            $Options .= "<option value='4000' selected>4000��</option>";
        } else {
            $Options .= "<option value='4000'>4000��</option>";
        }
        if ($default == 8000) {
            $Options .= "<option value='8000' selected>8000��</option>";
        } else {
            $Options .= "<option value='8000'>8000��</option>";
        }
        if ($default == 12000) {
            $Options .= "<option value='12000' selected>12000��</option>";
        } else {
            $Options .= "<option value='12000'>12000��</option>";
        }
        return $Options;
    }
    
    ///// �װ��ޥ��������Խ�
    public function editFactor($request, $result, $session)
    {
        $request->add('targetFactorName', mb_convert_encoding($request->get('targetFactorName'), 'EUC-JP', 'SJIS'));
        $request->add('targetFactorExplanation', mb_convert_encoding($request->get('targetFactorExplanation'), 'EUC-JP', 'SJIS'));
        $request->add('targetFactorName', trim($request->get('targetFactorName')));
        $request->add('targetFactorExplanation', trim($request->get('targetFactorExplanation')));
        if ($request->get('targetFactorName') == '') return;
        if ($request->get('targetFactorExplanation') == '') return;
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        if ($request->get('targetFactor') == '') {
            ///// INSERT
            $query = "SELECT factor FROM inventory_average_factor WHERE factor_name='{$request->get('targetFactorName')}'";
            if ($this->getUniResult($query, $factor) > 0) {
                $_SESSION['s_sysmsg'] = "�װ����ܡ�{$request->get('targetFactorName')}\\n\\n���Ϥ��줿�װ����ܤϴ��� ��{$factor}�� �֤���Ͽ����Ƥ��ޤ���";
                $session->add('regFactor', $factor);  // �ޡ������ڤӥ������Ѥ���Ͽ
                return;
            }
            $query = "SELECT CASE WHEN max(factor) IS NULL THEN 1 ELSE max(factor)+1 END FROM inventory_average_factor";
            $factor = 0;
            $this->getUniResult($query, $factor);
            $sql = "
                INSERT INTO inventory_average_factor (factor, factor_name, factor_explanation, active, last_date, last_user)
                VALUES ({$factor}, '{$request->get('targetFactorName')}', '{$request->get('targetFactorExplanation')}',
                    TRUE, '{$last_date}', '{$last_user}')
            ";
            if ($this->execute_Insert($sql) <= 0) {
                $_SESSION['s_sysmsg'] = "�װ��ֹ桧{$factor}\\n\\n�װ��ޥ���������Ͽ������ޤ���Ǥ�����������ô���Ԥ�Ϣ���Ʋ�������";
            } else {
                $_SESSION['s_sysmsg'] = "�װ��ֹ桧{$factor}\\n\\n�װ��ޥ���������Ͽ���ޤ�����";
                $session->add('regFactor', $factor);  // �ޡ������ڤӥ������Ѥ���Ͽ
            }
        } else {
            ///// UPDATE
            $query = "SELECT factor_name, factor_explanation FROM inventory_average_factor WHERE factor={$request->get('targetFactor')}";
            if ($this->getResult2($query, $check) < 1) {
                $_SESSION['s_sysmsg'] = "�װ��ֹ桧{$request->get('targetFactor')}\\n\\n���ꤵ�줿�װ��ֹ����Ͽ����Ƥ��ޤ��󡪡�����ô���Ԥ�Ϣ���Ʋ�������";
            } elseif ($check[0][0] == $request->get('targetFactorName') && $check[0][1] == $request->get('targetFactorExplanation')) {
                // �ѹ�̵��
                return;
            } else {
                $query = "SELECT factor FROM inventory_average_factor WHERE factor_name='{$request->get('targetFactorName')}' AND factor != {$request->get('targetFactor')}";
                if ($this->getUniResult($query, $factor) > 0) {
                    $_SESSION['s_sysmsg'] = "�װ����ܡ�{$request->get('targetFactorName')}\\n\\n���Ϥ��줿�װ����ܤϴ��� ��{$factor}�� �֤���Ͽ����Ƥ��ޤ���";
                    $session->add('regFactor', $factor);  // �ޡ������ڤӥ������Ѥ���Ͽ
                    return;
                }
                $sql = "
                    UPDATE inventory_average_factor SET factor_name='{$request->get('targetFactorName')}', factor_explanation='{$request->get('targetFactorExplanation')}',
                        last_date='{$last_date}', last_user='{$last_user}'
                    WHERE factor={$request->get('targetFactor')}
                ";
                $save_sql = "SELECT * FROM inventory_average_factor WHERE factor={$request->get('targetFactor')}";
                if ($this->execute_Update($sql, $save_sql) <= 0) {
                    $_SESSION['s_sysmsg'] = "�װ��ֹ桧{$request->get('targetFactor')}\\n\\n�װ��ޥ��������ѹ�������ޤ���Ǥ�����������ô���Ԥ�Ϣ���Ʋ�������";
                } else {
                    $_SESSION['s_sysmsg'] = "�װ��ֹ桧{$request->get('targetFactor')}\\n\\n�װ��ޥ��������ѹ����ޤ�����";
                    $session->add('regFactor', $request->get('targetFactor'));  // �ޡ������ڤӥ������Ѥ���Ͽ
                }
            }
        }
    }
    
    ///// �װ��ޥ������κ��
    public function deleteFactor($request, $result, $session)
    {
        if ($request->get('targetFactor') == '') return;
        $sql = "DELETE FROM inventory_average_factor WHERE factor={$request->get('targetFactor')}";
        $save_sql = "SELECT * FROM inventory_average_factor WHERE factor={$request->get('targetFactor')}";
        if ($this->execute_Delete($sql, $save_sql) <= 0) {
            $_SESSION['s_sysmsg'] = "�װ��ֹ桧{$request->get('targetFactor')}\\n\\n�װ��ޥ������κ��������ޤ���Ǥ�����������ô���Ԥ�Ϣ���Ʋ�������";
        } else {
            $_SESSION['s_sysmsg'] = "�װ��ֹ桧{$request->get('targetFactor')}\\n\\n�װ��ޥ������������ޤ�����";
        }
    }
    
    ///// �װ��ޥ�������ͭ����̵��������
    public function activeFactor($request, $result, $session)
    {
        if ($request->get('targetFactor') == '') return;
        $query = "SELECT active FROM inventory_average_factor WHERE factor={$request->get('targetFactor')}";
        if ($this->getUniResult($query, $check) < 1) {
            $_SESSION['s_sysmsg'] = "�װ��ֹ桧{$request->get('targetFactor')}\\n\\n���ꤵ�줿�װ��ֹ����Ͽ����Ƥ��ޤ��󡪡�����ô���Ԥ�Ϣ���Ʋ�������";
        } else {
            if ($check == 't') {
                $active = 'FALSE';
                $message = '̵��';
            } else {
                $active = 'TRUE';
                $message = 'ͭ��';
            }
            $last_date = date('Y-m-d H:i:s');
            $last_user = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
            $sql = "UPDATE inventory_average_factor SET active={$active}, last_date='{$last_date}', last_user='{$last_user}' WHERE factor={$request->get('targetFactor')}";
            $save_sql = "SELECT * FROM inventory_average_factor WHERE factor={$request->get('targetFactor')}";
            if ($this->execute_Update($sql, $save_sql) <= 0) {
                $_SESSION['s_sysmsg'] = "�װ��ֹ桧{$request->get('targetFactor')}\\n\\n�װ��ޥ�������{$message}�˽���ޤ���Ǥ�����������ô���Ԥ�Ϣ���Ʋ�������";
            } else {
                $_SESSION['s_sysmsg'] = "�װ��ֹ桧{$request->get('targetFactor')}\\n\\n�װ��ޥ�������{$message}�ˤ��ޤ�����";
            }
            $session->add('regFactor', $request->get('targetFactor'));  // �ޡ������ڤӥ������Ѥ���Ͽ
        }
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// �ꥯ�����Ȥˤ��SQLʸ�δ���WHERE�������
    protected function SetInitWhere($request)
    {
        ///// ����WHERE�������
        if ($request->get('searchPartsNo') != '') {
            $where = "WHERE parts_no LIKE '%{$request->get('searchPartsNo')}%'";
        } else {
            $where = 'WHERE TRUE';
        }
        $hold = $request->get('targetHoldMonth');
        switch ($request->get('targetDivision')) {
        case 'AL':
            $where .= " AND hold_monthly_avr >= {$hold}";
            break;
        case 'CA':
            $where .= " AND hold_monthly_avr >= {$hold} AND div = 'C'";
            break;
        case 'CH':
            $where .= " AND hold_monthly_avr >= {$hold} AND div = 'C'";
            break;
        case 'CS':
            $where .= " AND hold_monthly_avr >= {$hold} AND div = 'C'";
            break;
        case 'LA':
            $where .= " AND hold_monthly_avr >= {$hold} AND div = 'L'";
            break;
        case 'LH':
            $where .= " AND hold_monthly_avr >= {$hold} AND div = 'L' AND parts_no NOT LIKE 'LR%' AND parts_no NOT LIKE 'LC%'";
            break;
        case 'LB':
            $where .= " AND hold_monthly_avr >= {$hold} AND (parts_no LIKE 'LR%' OR parts_no LIKE 'LC%')";
            break;
        case 'OT':
        default:
            $where .= " AND hold_monthly_avr >= {$hold} AND div != 'C' AND div != 'L'";
        }
        return $where;
    }
    
    ////////// �ꥯ�����Ȥˤ��SQLʸ�δ���ORDER�������
    protected function SetInitOrder($request)
    {
        ///// targetSortItem������
        switch ($request->get('targetSortItem')) {
        case 'parts':
            $order = 'ORDER BY �����ֹ� ASC';
            break;
        case 'name':
            $order = 'ORDER BY ����̾ ASC';
            break;
        case 'parent':
            $order = 'ORDER BY �Ƶ��� DESC';
            break;
        case 'price':
            $order = 'ORDER BY �ǿ�ñ�� DESC';
            break;
        case 'stock':
            $order = 'ORDER BY �����߸˿� DESC';
            break;
        case 'money':
            $order = 'ORDER BY �߸˶�� DESC';
            break;
        case 'avrpcs':
            $order = 'ORDER BY ��ʿ�ѽи˿� ASC';
            break;
        case 'month':
            $order = 'ORDER BY ��ͭ�� DESC, �߸˶�� DESC';
            break;
        case 'factor':
            $order = 'ORDER BY �װ�̾ DESC';
            break;
        default:
            $order = 'ORDER BY ��ͭ�� DESC';
        }
        return $order;
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ///// List��   ����ɽ�� �إå����� ����
    private function getViewHTMLheader($request, $menu, $pageParameter)
    {
        // �����ȹ��ܤ��������
        $item = $this->getSortItemArray($request);
        // HTML�ι�������
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <th class='winbox' width=' 5%'>No</th>\n";
        $listTable .= "        <th class='winbox' width='12%'{$item['parts']} title='�����ֹ�ǡ�����˥����Ȥ��ޤ���'>\n";
        $listTable .= "            <a href='{$menu->out_self()}?targetSortItem=parts&{$pageParameter}' target='_parent' onMouseover=\"status='�����ֹ�ǡ�����˥����Ȥ��ޤ���'; return true;\" onMouseout=\"status='';\">�����ֹ�</a></th>\n";
        $listTable .= "        <th class='winbox' width='19%'{$item['name']} title='����̾�ǡ�����˥����Ȥ��ޤ���'>\n";
        $listTable .= "            <a href='{$menu->out_self()}?targetSortItem=name&{$pageParameter}' target='_parent' onMouseover=\"status='����̾�ǡ�����˥����Ȥ��ޤ���'; return true;\" onMouseout=\"status='';\">�����ʡ�̾</a></th>\n";
        $listTable .= "        <th class='winbox' width='12%'{$item['parent']} title='�Ƶ���ǡ�����˥����Ȥ��ޤ���'>\n";
        $listTable .= "            <a href='{$menu->out_self()}?targetSortItem=parent&{$pageParameter}' target='_parent' onMouseover=\"status='�Ƶ���ǡ�����˥����Ȥ��ޤ���'; return true;\" onMouseout=\"status='';\">�Ƶ���</a></th>\n";
        $listTable .= "        <th class='winbox' width=' 9%'{$item['price']} title='�ǿ�ñ���ǡ��߽�˥����Ȥ��ޤ���'>\n";
        $listTable .= "            <a href='{$menu->out_self()}?targetSortItem=price&{$pageParameter}' target='_parent' onMouseover=\"status='�ǿ�ñ���ǡ��߽�˥����Ȥ��ޤ���'; return true;\" onMouseout=\"status='';\">�ǿ�ñ��</a></th>\n";
        $listTable .= "        <th class='winbox' width=' 9%'{$item['stock']} title='�����κ߸˿��ǡ��߽�˥����Ȥ��ޤ���'>\n";
        $listTable .= "            <a href='{$menu->out_self()}?targetSortItem=stock&{$pageParameter}' target='_parent' onMouseover=\"status='�����κ߸˿��ǡ��߽�˥����Ȥ��ޤ���'; return true;\" onMouseout=\"status='';\">�����߸�</a></th>\n";
        $listTable .= "        <th class='winbox' width='10%'{$item['money']} title='�߸˶�ۤǡ��߽�˥����Ȥ��ޤ���'>\n";
        $listTable .= "            <a href='{$menu->out_self()}?targetSortItem=money&{$pageParameter}' target='_parent' onMouseover=\"status='�߸˶�ۤǡ��߽�˥����Ȥ��ޤ���'; return true;\" onMouseout=\"status='';\">�߸˶��</a></th>\n";
        $listTable .= "        <th class='winbox' width='10%'{$item['avrpcs']} title='��ʿ�ѽи˿��ǡ��߽�˥����Ȥ��ޤ���'>\n";
        $listTable .= "            <a href='{$menu->out_self()}?targetSortItem=avrpcs&{$pageParameter}' target='_parent' onMouseover=\"status='��ʿ�ѽи˿��ǡ��߽�˥����Ȥ��ޤ���'; return true;\" onMouseout=\"status='';\">ʿ�ѽи�</a></th>\n";
        $listTable .= "        <th class='winbox' width=' 7%'{$item['month']} title='��ͭ��ǡ��߽�˥����Ȥ��ޤ���'>\n";
        $listTable .= "            <a href='{$menu->out_self()}?targetSortItem=month&{$pageParameter}' target='_parent' onMouseover=\"status='��ͭ��ǡ��߽�˥����Ȥ��ޤ���'; return true;\" onMouseout=\"status='';\">��ͭ��</a></th>\n";
        $listTable .= "        <th class='winbox' width=' 7%'{$item['factor']} title='�װ��ǡ�����˥����Ȥ��ޤ���'>\n";
        $listTable .= "            <a href='{$menu->out_self()}?targetSortItem=factor&{$pageParameter}' target='_parent' onMouseover=\"status='�װ��ǡ�����˥����Ȥ��ޤ���'; return true;\" onMouseout=\"status='';\">�װ�</a></th>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        // return mb_convert_encoding($listTable, 'UTF-8');
        return $listTable;
    }
    
    ///// List��   �������� �ܥǥ���
    private function getViewHTMLbody($request, $menu, $session)
    {
        // �����ȹ��ܤ��������
        $item = $this->getSortItemArray($request);
        $query = "
            SELECT   invent.parts_no    AS �����ֹ�         -- 00
                    , trim(substr(midsc, 1, 14))
                                        AS ����̾           -- 01
                    , CASE
                        WHEN mepnt = '' THEN '&nbsp;'
                        WHEN mepnt IS NULL THEN '&nbsp;'
                        ELSE trim(substr(mepnt, 1, 9))
                      END               AS �Ƶ���           -- 02
                    , CASE
                        WHEN latest_parts_cost(invent.parts_no) IS NULL THEN 0
                        ELSE latest_parts_cost(invent.parts_no)
                      END               AS �ǿ�ñ��         -- 03
                    , invent_pcs
                                        AS �����߸˿�       -- 04
                    , CASE
                        WHEN latest_parts_cost(invent.parts_no) IS NULL THEN 0
                        ELSE Uround(latest_parts_cost(invent.parts_no) * invent_pcs, 0)
                      END               AS �߸˶��         -- 05
                    , month_pickup_avr
                                        AS ��ʿ�ѽи˿�     -- 06
                    , hold_monthly_avr
                                        AS ��ͭ��           -- 07
                    , CASE
                        WHEN factor_name IS NULL THEN '&nbsp;'
                        ELSE factor_name
                      END               AS �װ�̾           -- 08
                    , factor_explanation
                                        AS �װ�����         -- 09
                    , comment
                                        AS ������         -- 10
                    , CASE
                        WHEN latest_parts_cost_regno(invent.parts_no) IS NULL THEN 0
                        ELSE latest_parts_cost_regno(invent.parts_no)
                      END               AS ��Ͽ�ֹ�         -- 11
                    FROM
                        inventory_average_summary AS invent
                    LEFT OUTER JOIN
                        miitem ON (invent.parts_no = mipn)
                    LEFT OUTER JOIN
                        inventory_average_comment USING (parts_no)
                    LEFT OUTER JOIN
                        inventory_average_factor USING (factor)
                    {$this->where}
                    {$this->order}
        ";
        $session->add('csv_where', $this->where);
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) < 1 ) {
            $listTable .= "    <tr>\n";
            $listTable .= "        <td width='100%' align='center' class='winbox'>�������ʤ�����ޤ���</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
            // $listTable .= "<script type='text/javascript'>\n";
            // $listTable .= "parent.document.ConditionForm.searchPartsNo.focus();\n";
            // $listTable .= "// parent.document.ConditionForm.searchPartsNo.select();\n";
            // $listTable .= "</script>\n";
        } else {
            $this->totalMsg = $this->getSumPrice($rows, $res);
            $date_low = (date('Ymd') - 100000);      // 10ǯ������(view_rec��300�����¤��Ƥ���)
            if ($session->get('regParts') != '') {
                $regParts = $session->get('regParts');
                $session->add('regParts', '');
            } else {
                $regParts = '';
            }
            for ($i=0; $i<$rows; $i++) {
                if ($regParts == $res[$i][0]) {
                    $listTable .= "    <tr onDblClick='InventoryAverage.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPartsNo=" . urlencode($res[$i][0]) . "\", 600, 235)' title='{$res[$i][10]}' style='background-color:#ffffc6;'>\n";
                    $listTable .= "        <td class='winbox' width=' 5%' align='right' ><a name='Mark' style='color:black;'>" . ($i+1 + $this->get_offset()) . "</a></td>\n";                  // ���ֹ�
                } elseif ($res[$i][10] == '') {
                    $listTable .= "    <tr onDblClick='InventoryAverage.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPartsNo=" . urlencode($res[$i][0]) . "\", 600, 235)' title='���֥륯��å��ǥ����ȤξȲ��Խ�������ޤ���'>\n";
                    $listTable .= "        <td class='winbox' width=' 5%' align='right' >" . ($i+1 + $this->get_offset()) . "</td>\n";                  // ���ֹ�
                } else {
                    $listTable .= "    <tr onDblClick='InventoryAverage.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPartsNo=" . urlencode($res[$i][0]) . "\", 600, 235)' title='{$res[$i][10]}' style='background-color:#e6e6e6;'>\n";
                    $listTable .= "        <td class='winbox' width=' 5%' align='right' >" . ($i+1 + $this->get_offset()) . "</td>\n";                  // ���ֹ�
                }
                $listTable .= "        <td class='winbox' width='12%' align='center'{$item['parts']} title='�����ֹ�򥯥�å�����к߸˷����Ȳ�Ǥ��ޤ���'\n";
                $listTable .= "            onClick='InventoryAverage.win_open(\"" . $menu->out_action('�߸˷���') . "?parts_no=" . urlencode($res[$i][0]) . "&date_low={$date_low}&view_rec=300&noMenu=yes\", 900, 680)'\n";
                $listTable .= "        ><a href='javascript:void(0);'>{$res[$i][0]}</a></td>\n";                                                        // �����ֹ�
                if ($res[$i][1] != '') {                                                                                                                // ����̾
                    $listTable .= "        <td class='winbox' width='19%' align='left'{$item['name']}>" . mb_substr(mb_convert_kana($res[$i][1], 'ksa'), 0, 15) . "</td>\n";
                } else {
                    $listTable .= "        <td class='winbox' width='19%' align='left'{$item['name']}><span style='color:red;'>�ޥ�����̤��Ͽ</span></td>\n";
                }
                $listTable .= "        <td class='winbox' width='12%' align='left'{$item['parent']}>" . mb_convert_kana($res[$i][2], 'ksa') . "</td>\n";// �Ƶ���
                if ($res[$i][3] > 0) {                                                                                                                  // �ǿ�ñ��
                    $listTable .= "        <td class='winbox' width=' 9%' align='right'{$item['price']} title='ñ���򥯥�å������ñ����Ͽ��Ȳ�Ǥ��ޤ���'\n";
                    $listTable .= "            onClick='InventoryAverage.win_open(\"" . $menu->out_action('ñ����Ͽ�Ȳ�') . "?parts_no=" . urlencode($res[$i][0]) . "& reg_no={$res[$i][11]}&noMenu=yes\", 900, 680)'\n";
                    $listTable .= "        ><a href='javascript:void(0);'>" . number_format($res[$i][3], 2) . "</a></td>\n";                            // �ǿ�ñ��
                } else {
                    $listTable .= "        <td class='winbox' width=' 9%' align='right'{$item['price']}>" . number_format($res[$i][3], 2) . "</td>\n";  // �ǿ�ñ��
                }
                $listTable .= "        <td class='winbox' width=' 9%' align='right'{$item['stock']}>" . number_format($res[$i][4]) . "</td>\n";         // �����κ߸˿�
                $listTable .= "        <td class='winbox' width='10%' align='right'{$item['money']}>" . number_format($res[$i][5]) . "</td>\n";         // �߸˶��
                $listTable .= "        <td class='winbox' width='10%' align='right'{$item['avrpcs']}>" . number_format($res[$i][6]) . "</td>\n";        // ��ʿ�ѽи˿�
                $listTable .= "        <td class='winbox' width=' 7%' align='right'{$item['month']}>" . number_format($res[$i][7], 1) . "</td>\n";      // ��ͭ��
                $listTable .= "        <td class='winbox factorFont' width=' 7%' align='left'{$item['factor']} title='{$res[$i][9]}'>{$res[$i][8]}</td>\n";        // �װ�̾
                $listTable .= "    </tr>\n";
            }
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        }
        // return mb_convert_encoding($listTable, 'UTF-8');
        return $listTable;
    }
    
    ///// List��   �������� �եå�����
    private function getViewHTMLfooter()
    {
        // �����
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td class='winbox' align='right'>{$this->totalMsg}</td>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        return $listTable;
    }
    
    ///// List��   �װ����ܥޥ����� �������� �ܥǥ���
    private function getViewFactorHTMLbody($request, $menu, $session)
    {
        // �����ȹ��ܤ��������
        $item = $this->getSortItemArray($request);
        $query = "
            SELECT    factor            AS �װ��ֹ�         -- 00
                    , factor_name       AS �װ�����         -- 01
                    , factor_explanation
                                        AS �װ�����         -- 02
                    , CASE
                        WHEN active THEN 'ͭ��'
                        ELSE '̵��'
                      END               AS ͭ��̵��         -- 03
                    , regdate           AS �����Ͽ��       -- 04
                    , last_date         AS �ǽ��ѹ���       -- 05
                    , last_user         AS �ǽ��ѹ���       -- 06
                    , (SELECT parts_no FROM inventory_average_comment WHERE factor=invent.factor LIMIT 1)
                                        AS ��������         -- 07
                    FROM
                        inventory_average_factor AS invent
                    ORDER BY factor ASC
        ";
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) < 1 ) {
            $listTable .= "    <tr>\n";
            $listTable .= "        <td width='100%' align='center' class='winbox'>̤��Ͽ�Ǥ���</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        } else {
            if ($session->get('regFactor') != '') {
                $regFactor = $session->get('regFactor');
                $session->add('regFactor', '');
            } else {
                $regFactor = '';
            }
            for ($i=0; $i<$rows; $i++) {
                if ($res[$i][3] == '̵��') $activeColor = " color:gray;"; else $activeColor = '';
                if ($regFactor == $res[$i][0]) {
                    $listTable .= "    <tr style='background-color:#ffffc6;{$activeColor}'>\n";
                    $listTable .= "        <td class='winbox' width=' 5%' align='right' ><a name='Mark' style='color:black;'>{$res[$i][0]}</a></td>\n"; // �װ��ֹ�
                } else {
                    $listTable .= "    <tr style='{$activeColor}'>\n";
                    $listTable .= "        <td class='winbox' width=' 5%' align='right'>{$res[$i][0]}</td>\n";      // �װ��ֹ�
                }
                $listTable .= "        <td class='winbox' width='11%' align='left'>{$res[$i][1]}</td>\n";           // �װ�����
                $listTable .= "        <td class='winbox' width='60%' align='left' style='font-size:0.9em;'>{$res[$i][2]}</td>\n";           // �װ�����
                $listTable .= "        <td class='winbox' width=' 8%' align='center'>\n";
                $listTable .= "            <input type='button' name='editButton' value='����' class='editButton'\n";
                $listTable .= "                onClick='parent.InventoryAverage.copyFactor(\"{$res[$i][0]}\", \"{$res[$i][1]}\", \"{$res[$i][2]}\");'\n";
                $listTable .= "            >\n";
                $listTable .= "        </td>\n";
                $listTable .= "        <td class='winbox' width=' 8%' align='center'>\n";
                if ($res[$i][3] == 'ͭ��') {
                    $listTable .= "            <input type='button' name='activeButton' value='̵��' class='updateButton'\n";
                    $listTable .= "                onClick='parent.InventoryAverage.activeFactor(\"{$res[$i][0]}\", \"{$res[$i][1]}\", \"̵��\");'\n";
                    $listTable .= "            >\n";
                } else {
                    $listTable .= "            <input type='button' name='activeButton' value='ͭ��' class='updateButton'\n";
                    $listTable .= "                onClick='parent.InventoryAverage.activeFactor(\"{$res[$i][0]}\", \"{$res[$i][1]}\", \"ͭ��\");'\n";
                    $listTable .= "            >\n";
                }
                $listTable .= "        </td>\n";
                $listTable .= "        <td class='winbox' width=' 8%' align='center'>\n";
                if ($res[$i][7] == '') {
                    $listTable .= "            <input type='button' name='delButton' value='���' class='delButton'\n";
                    $listTable .= "                onClick='parent.InventoryAverage.deleteFactor(\"{$res[$i][0]}\", \"{$res[$i][1]}\");'\n";
                    $listTable .= "            >\n";
                } else {
                    $listTable .= "            <input type='button' name='delButton' value='���' class='delButton' disabled>\n";
                }
                $listTable .= "        </td>\n";
                $listTable .= "    </tr>\n";
            }
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        }
        // return mb_convert_encoding($listTable, 'UTF-8');
        return $listTable;
    }
    
    ///// �����ȥ����ƥ��������֤�
    private function getSortItemArray($request)
    {
        // �����
        $itemArray = array('parts' => '', 'name' => '', 'parent' => '', 'price' => '', 'stock' => '',
            'money' => '', 'avrpcs' => '', 'month' => '', 'factor' => '');
        // �ꥯ�����Ȥˤ�꥽���ȹ��ܤ˿��դ�
        switch ($request->get('targetSortItem')) {
        case 'parts':
            $itemArray['parts'] = " style='background-color:#ffffc6;'";
            break;
        case 'name':
            $itemArray['name'] = " style='background-color:#ffffc6;'";
            break;
        case 'parent':
            $itemArray['parent'] = " style='background-color:#ffffc6;'";
            break;
        case 'price':
            $itemArray['price'] = " style='background-color:#ffffc6;'";
            break;
        case 'stock':
            $itemArray['stock'] = " style='background-color:#ffffc6;'";
            break;
        case 'money':
            $itemArray['money'] = " style='background-color:#ffffc6;'";
            break;
        case 'avrpcs':
            $itemArray['avrpcs'] = " style='background-color:#ffffc6;'";
            break;
        case 'month':
            $itemArray['month'] = " style='background-color:#ffffc6;'";
            break;
        case 'factor':
            $itemArray['factor'] = " style='background-color:#ffffc6;'";
            break;
        }
        return $itemArray;
    }
    
    ///// �����List��    HTML�ե��������
    private function getViewHTMLconst($status)
    {
        if ($status == 'header') {
            $listHTML = 
"
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=EUC-JP'>
<meta http-equiv='Content-Style-Type' content='text/css'>
<meta http-equiv='Content-Script-Type' content='text/javascript'>
<title>Ĺ����α����List��</title>
<script type='text/javascript' src='/base_class.js'></script>
<link rel='stylesheet' href='/menu_form.css' type='text/css' media='screen'>
<link rel='stylesheet' href='../inventory_average.css' type='text/css' media='screen'>
<style type='text/css'>
<!--
body {
    background-image:   none;
    background-color:   #d6d3ce;
}
-->
</style>
<script type='text/javascript' src='../inventory_average.js'></script>
</head>
<body
    onLoad='if (parent.document.ConditionForm.searchPartsNo) parent.document.ConditionForm.searchPartsNo.focus();'
>
<center>
";
        } elseif ($status == 'footer') {
            $listHTML = 
"
</center>
</body>
</html>
";
        } else {
            $listHTML = '';
        }
        return $listHTML;
    }
    
    ///// ��׶�ۡ������׻����ƥ�å��������֤���
    private function getSumPrice($rows, $array)
    {
        $sumPrice = 0;     // �����
        for ($i=0; $i<$rows; $i++) {
            $sumPrice += $array[$i][5];
        }
        $sumPrice = number_format($sumPrice);
        return "��׷�� �� {$rows} �� &nbsp;&nbsp;&nbsp&nbsp; ��׶�� �� {$sumPrice}";
    }
    

} // Class InventoryAverage_Model End

?>
