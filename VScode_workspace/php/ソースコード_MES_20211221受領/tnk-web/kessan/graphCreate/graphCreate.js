//////////////////////////////////////////////////////////////////////////////
// ��Ω����Ͽ�����ȼ��ӹ�������� �Ȳ�         MVC View��(JavaScript���饹) //
// Copyright (C) 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2007/10/08 Created    graphCreate.js                                     //
// 2007/10/15 checkbox �� checked = false ����submit����ʤ��������        //
//////////////////////////////////////////////////////////////////////////////

///// �����Х��ѿ� _GDEBUG �ν���ͤ򥻥å�(��꡼������false�˥��åȤ���)
var _GDEBUG = false;

/****************************************************************************
/*             graphCreate class base_class �γ�ĥ���饹�����              *
/****************************************************************************
class graphCreate extends base_class
*/
///// �����ѡ����饹�ηѾ�
graphCreate.prototype = new base_class();    // base_class �ηѾ�
///// Constructer �����
function graphCreate()
{
    /***********************************************************************
    *                           Private properties                         *
    ***********************************************************************/
    // this.properties = false;                         // �ץ�ѥƥ����ν����
    this.blink_flag = 1;                                // blink_disp()�᥽�å���ǻ��Ѥ���
    this.blink_msg  = "";                               // ��
    this.parameter  = "";                               // Ajax�̿����Υѥ�᡼����
    
    /************************************************************************
    *                           Public methods                              *
    ************************************************************************/
    /***** �ѥ�᡼�����ǻ��ꤵ�줿���֥������ȤΥ�����Ȥ˥ե������������� *****/
    graphCreate.prototype.set_focus = function (obj, status)
    {
        if (obj) {
            obj.focus();
            if (status == "select") obj.select();
        }
        // document.body.focus();   // F2/F12������ͭ���������б�
        // document.mhForm.backwardStack.focus();  // �嵭��IE�ΤߤΤ��ᡢ��������ѹ���NN�б�
        // document.form_name.element_name.focus();      // ������ϥե����ब������ϥ����Ȥ򳰤�
        // document.form_name.element_name.select();
    }
    
    /***** ����ɽ���᥽�å� *****/
    /***** blink_flg Private property �������0.5��������� *****/
    /***** <body onLoad='setInterval("obj.blink_disp(\"caption\")", 500)'> *****/
    graphCreate.prototype.blink_disp = function (id_name)
    {
        if (this.blink_flag == 1) {
            this.blink_msg = document.getElementById(id_name).innerHTML;
            document.getElementById(id_name).innerHTML = "&nbsp;";
            this.blink_flag = 2;
        } else {
            document.getElementById(id_name).innerHTML = this.blink_msg;
            this.blink_flag = 1;
        }
    }
    
    /***** ���֥������Ȥ��ͤ���ʸ���Ѵ����� *****/
    graphCreate.prototype.obj_upper = function (obj)
    {
        obj.value = obj.value.toUpperCase();
        return true;
    }
    
    /***** ������礭���Υ��֥�����ɥ��������ɽ������ *****/
    /***** Windows XP SP2 �Ǥϥ������ƥ��ηٹ𤬽Ф�  *****/
    graphCreate.prototype.win_open = function (url, w, h)
    {
        if (!w) w = 800;     // �����
        if (!h) h = 600;     // �����
        var left = (screen.availWidth  - w) / 2;
        var top  = (screen.availHeight - h) / 2;
        w -= 10; h -= 30;   // ��Ĵ����ɬ��
        window.open(url, 'view_win', 'width='+w+',height='+h+',scrollbars=yes,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
    }
    
    /***** ���֥�����ɥ�¦��Window��Active�����å���Ԥ� *****/
    /***** <body onLoad="setInterval('instanceObj.winActiveChk(targetFocusObj)',50)">*****/
    graphCreate.prototype.winActiveChk = function (obj)
    {
        if (document.all) {     // IE�ʤ�
            if (document.hasFocus() == false) {     // IE5.5�ʾ�ǻȤ���
                if (obj) {  // ���ꤵ��Ƥ��뤫���ϡ�¸�ߤ��뤫��
                    obj.focus();
                } else {
                    window.focus();
                }
                return;
            }
            return;
        } else {                // NN �ʤ�ȥ�ꥭ�å�
            if (obj) {
                obj.focus();
            }
            window.focus();
            return;
        }
    }
    
    /***** ������礭���Υ⡼�������������ɽ������ *****/
    /***** IE ���ѤʤΤ� Windows XP SP2 �Ǥϥ������ƥ��ηٹ𤬽Ф� *****/
    /***** ����������ǥꥯ�����Ȥ�Ф����ϥե졼����ڤäƹԤ� *****/
    graphCreate.prototype.win_show = function (url, w, h)
    {
        if (!w) w = 800;     // �����
        if (!h) h = 600;     // �����
        showModalDialog(url, 'show_win', "dialogWidth:" + w + "px;dialogHeight:" + h + "px");
    }
    
    /***** ǯ���dataxFlg(���ѥե饰)��on/off��prot2��ǯ������� *****/
    graphCreate.prototype.checkboxAction = function (obj)
    {
        if (obj.checked) {
            document.ConditionForm.yyyymm2.disabled = true;
            // value �� selectedIndex �Ϥɤ���Ǥ�ϣ�
            // document.ConditionForm.yyyymm2.value = document.ConditionForm.yyyymm1.value;
            document.ConditionForm.yyyymm2.selectedIndex = document.ConditionForm.yyyymm1.selectedIndex;
        } else {
            document.ConditionForm.yyyymm2.disabled = false;
        }
    }
    
    /***** prot1���ѹ���������ǯ���dataxFlg(���ѥե饰)��on/off��prot2��ǯ������� *****/
    graphCreate.prototype.prot1Action = function ()
    {
        if (document.ConditionForm.dataxFlg.checked) {
            // value �� selectedIndex �Ϥɤ���Ǥ�ϣ�
            // document.ConditionForm.yyyymm2.value = document.ConditionForm.yyyymm1.value;
            document.ConditionForm.yyyymm2.selectedIndex = document.ConditionForm.yyyymm1.selectedIndex;
        }
    }
    
    /***** ConditionForm �����ϥ����å��᥽�å�(����դι���) *****/
    graphCreate.prototype.checkConditionForm = function (obj)
    {
        // obj.targetPlanNo.value = obj.targetPlanNo.value.toUpperCase();
        if (obj.g1plot1.value == "̤����") {
            alert("����գ��Υץ�åȣ���ɬ�����ꤷ�Ʋ�������");
            obj.g1plot1.focus();
            return false;
        }
        if (obj.g2plot2.value != "̤����" && obj.g2plot1.value == "̤����") {
            alert("����գ��Υץ�åȣ�����˻��ꤷ�Ʋ�������");
            obj.g2plot1.focus();
            return false;
        }
        if (obj.g3plot2.value != "̤����" && obj.g3plot1.value == "̤����") {
            alert("����գ��Υץ�åȣ�����˻��ꤷ�Ʋ�������");
            obj.g3plot1.focus();
            return false;
        }
        if (!this.checkSubItem(obj)) {
            return false;
        }
        if (obj.dataxFlg.checked) {
            obj.dataxFlg.value = "on";
        } else {
            obj.dataxFlg.checked = true;    // checked = false ����submit����ʤ��������
            obj.dataxFlg.value = "off";
        }
        obj.yyyymm2.disabled = false;
        // this.parameter  = "&g1plot1=" + obj.g1plot1.value;
        // this.parameter += "&g1plot2=" + obj.g1plot2.value;
        // this.parameter += "&g2plot1=" + obj.g2plot1.value;
        // this.parameter += "&g2plot2=" + obj.g2plot2.value;
        // this.parameter += "&g3plot1=" + obj.g3plot1.value;
        // this.parameter += "&g3plot2=" + obj.g3plot2.value;
        return true;
    }
    
    /***** checkConditionForm �����ϥ����å����֥᥽�å� *****/
    graphCreate.prototype.checkSubItem = function (obj)
    {
        if (obj.g1plot1.value == "--�ʲ�������--" || obj.g1plot1.value == "--�ʲ��ϥ��ץ�--" || obj.g1plot1.value == "--�ʲ��ϥ�˥�--" || obj.g1plot1.value == "--�ʲ���Cɸ��--" || obj.g1plot1.value == "--�ʲ���C����--" || obj.g1plot1.value == "--�ʲ���Cɸ��--" || obj.g1plot1.value == "--�ʲ���L����--" || obj.g1plot1.value == "--�ʲ��ώʎގ��ӎ�--") {
            alert("����գ��Υץ�åȣ� [ " + obj.g1plot1.value + " ] �ϥ��������ι��ܤǤϤ���ޤ���");
            obj.g1plot1.focus();
            return false;
        }
        if (obj.g1plot2.value == "--�ʲ�������--" || obj.g1plot2.value == "--�ʲ��ϥ��ץ�--" || obj.g1plot2.value == "--�ʲ��ϥ�˥�--" || obj.g1plot2.value == "--�ʲ���Cɸ��--" || obj.g1plot2.value == "--�ʲ���C����--" || obj.g1plot2.value == "--�ʲ���Cɸ��--" || obj.g1plot2.value == "--�ʲ���L����--" || obj.g1plot2.value == "--�ʲ��ώʎގ��ӎ�--") {
            alert("����գ��Υץ�åȣ� [ " + obj.g1plot2.value + " ] �ϥ��������ι��ܤǤϤ���ޤ���");
            obj.g1plot2.focus();
            return false;
        }
        if (obj.g2plot1.value == "--�ʲ�������--" || obj.g2plot1.value == "--�ʲ��ϥ��ץ�--" || obj.g2plot1.value == "--�ʲ��ϥ�˥�--" || obj.g2plot1.value == "--�ʲ���Cɸ��--" || obj.g2plot1.value == "--�ʲ���C����--" || obj.g2plot1.value == "--�ʲ���Cɸ��--" || obj.g2plot1.value == "--�ʲ���L����--" || obj.g2plot1.value == "--�ʲ��ώʎގ��ӎ�--") {
            alert("����գ��Υץ�åȣ� [ " + obj.g2plot1.value + " ] �ϥ��������ι��ܤǤϤ���ޤ���");
            obj.g2plot1.focus();
            return false;
        }
        if (obj.g2plot2.value == "--�ʲ�������--" || obj.g2plot2.value == "--�ʲ��ϥ��ץ�--" || obj.g2plot2.value == "--�ʲ��ϥ�˥�--" || obj.g2plot2.value == "--�ʲ���Cɸ��--" || obj.g2plot2.value == "--�ʲ���C����--" || obj.g2plot2.value == "--�ʲ���Cɸ��--" || obj.g2plot2.value == "--�ʲ���L����--" || obj.g2plot2.value == "--�ʲ��ώʎގ��ӎ�--") {
            alert("����գ��Υץ�åȣ� [ " + obj.g2plot2.value + " ] �ϥ��������ι��ܤǤϤ���ޤ���");
            obj.g2plot2.focus();
            return false;
        }
        if (obj.g3plot1.value == "--�ʲ�������--" || obj.g3plot1.value == "--�ʲ��ϥ��ץ�--" || obj.g3plot1.value == "--�ʲ��ϥ�˥�--" || obj.g3plot1.value == "--�ʲ���Cɸ��--" || obj.g3plot1.value == "--�ʲ���C����--" || obj.g3plot1.value == "--�ʲ���Cɸ��--" || obj.g3plot1.value == "--�ʲ���L����--" || obj.g3plot1.value == "--�ʲ��ώʎގ��ӎ�--") {
            alert("����գ��Υץ�åȣ� [ " + obj.g3plot1.value + " ] �ϥ��������ι��ܤǤϤ���ޤ���");
            obj.g3plot1.focus();
            return false;
        }
        if (obj.g3plot2.value == "--�ʲ�������--" || obj.g3plot2.value == "--�ʲ��ϥ��ץ�--" || obj.g3plot2.value == "--�ʲ��ϥ�˥�--" || obj.g3plot2.value == "--�ʲ���Cɸ��--" || obj.g3plot2.value == "--�ʲ���C����--" || obj.g3plot2.value == "--�ʲ���Cɸ��--" || obj.g3plot2.value == "--�ʲ���L����--" || obj.g3plot2.value == "--�ʲ��ώʎގ��ӎ�--") {
            alert("����գ��Υץ�åȣ� [ " + obj.g3plot2.value + " ] �ϥ��������ι��ܤǤϤ���ޤ���");
            obj.g3plot2.focus();
            return false;
        }
        return true;
    }
    
    /***** ConditionForm �����ϥ����å��򤷤�Ajax�¹� *****/
    graphCreate.prototype.checkANDexecute = function (obj)
    {
        if (this.checkConditionForm(obj)) {
            this.AjaxLoadTable("ListTable", "showAjax");
        }
        return false;   // �ºݤ�submit�Ϥ����ʤ�
    }
    
    /***** ��Ͽ��������Ͽ�ֹ楯��å��ˤ�빩�����پȲ� Ajax�¹� *****/
    graphCreate.prototype.processExecute = function (assy_no, reg_no)
    {
        this.parameter  = "&targetAssyNo=" + assy_no + "&targetRegNo=" + reg_no;
        this.AjaxLoadTable("ProcessTable", "showAjax2");
    }
    
    /***** ���̹�����桼�����˰��´�̵��ɽ��������Ajax�ѥ���ɥ᥽�å� *****/
    // onReadyStateChange���٥�Ȥ�Ȥäƽ�������λ���Ƥ��ʤ�����WaitMessage����ϡ�
    // parameter : ListTable=���ɽ��, WaitMsg=������Ǥ������Ԥ���������
    graphCreate.prototype.AjaxLoadTable = function (showMenu, location)
    {
        if (!location) location = "showAjax";   // Default�ͤ�����
        var parm = "?";
        parm += "showMenu=" + showMenu // table�Τ����
        parm += this.parameter;
        /***
        parm += "&CTM_selectPage="      + document.ControlForm.CTM_selectPage.value;
        parm += "&CTM_prePage="         + document.ControlForm.CTM_prePage.value;
        parm += "&CTM_pageRec="         + document.ControlForm.CTM_pageRec.value;
        ***/
        try {
            var xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        } catch (e) {
            try {
                var xmlhttp = new XMLHttpRequest();
            } catch (e) {
                alert("�����ѤΥ֥饦������̤�б��Ǥ���\n\n" + e);
            }
        }
        xmlhttp.onreadystatechange = function () {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                document.getElementById(location).innerHTML = xmlhttp.responseText;
            } else {
                // onReadyStateChange���٥�Ȥ�Ȥäƽ�������λ���Ƥ��ʤ�����WaitMessage����ϡ�
                document.getElementById(location).innerHTML = "<br><table width='100%' border='0'><tr><td align='center' style='font-size:20pt; font-weight:bold;'>������Ǥ������Ԥ���������<br><img src='/img/tnk-turbine.gif' width='68' height='72'></td></tr></table>";
            }
        }
        try {
            xmlhttp.open("GET", "graphCreate_Main.php"+parm);
            xmlhttp.send(null);
        } catch (e) {
            alert(url + "\n\n�򥪡��ץ����ޤ���\n\n" + e);
        }
    }
    
    /***** ���ɽ���ΰ�Υ��ꥢ���᥽�å� *****/
    graphCreate.prototype.viewClear = function ()
    {
        document.getElementById("showAjax").innerHTML = "";
    }
    
    /***** �᥽�åɼ����ˤ��WaitMessageɽ�� *****/
    graphCreate.prototype.WaitMessage = function ()
    {
        var WaitMsg = "<br><table width='100%' border='0'><tr><td align='center' style='font-size:20pt; font-weight:bold;'>������Ǥ������Ԥ���������<br><img src='/img/tnk-turbine.gif' width='68' height='72'></td></tr></table>";
        document.getElementById("showAjax").innerHTML = WaitMsg;
    }
    
    return this;    // Object Return
    
}   /* class graphCreate END  */


///// ���󥹥��󥹤�����
var GraphCreate = new graphCreate();

