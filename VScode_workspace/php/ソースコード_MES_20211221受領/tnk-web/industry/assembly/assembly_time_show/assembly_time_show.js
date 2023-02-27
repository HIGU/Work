//////////////////////////////////////////////////////////////////////////////
// ��Ω����Ͽ�����ȼ��ӹ�������� �Ȳ�         MVC View��(JavaScript���饹) //
// Copyright (C) 2006-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/03/01 Created    assembly_time_show.js                              //
// 2006/05/19 ��Ͽ�����Τߤ�ɽ����ǽ���ɲ� regOnly                          //
// 2006/05/20 winActiveChk()�᥽�åɤ�NN���ϡ����ʤΥե�������obj.focus() //
//            �����Ǥ�Active�ˤʤ�ʤ�����window.focus()��ɬ���Ǹ�������  //
// 2007/06/17 regOnly�ξ��� usedTime, workerCount��hidden°�����ɲ�       //
//////////////////////////////////////////////////////////////////////////////

///// �����Х��ѿ� _GDEBUG �ν���ͤ򥻥å�(��꡼������false�˥��åȤ���)
var _GDEBUG = false;

/****************************************************************************
/*     assembly_time_show class base_class �γ�ĥ���饹�����              *
/****************************************************************************
class assembly_time_show extends base_class
*/
///// �����ѡ����饹�ηѾ�
assembly_time_show.prototype = new base_class();    // base_class �ηѾ�
///// Constructer �����
function assembly_time_show()
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
    assembly_time_show.prototype.set_focus = function (obj, status)
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
    assembly_time_show.prototype.blink_disp = function (id_name)
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
    assembly_time_show.prototype.obj_upper = function (obj)
    {
        obj.value = obj.value.toUpperCase();
        return true;
    }
    
    /***** ������礭���Υ��֥�����ɥ��������ɽ������ *****/
    /***** Windows XP SP2 �Ǥϥ������ƥ��ηٹ𤬽Ф�  *****/
    assembly_time_show.prototype.win_open = function (url, w, h)
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
    assembly_time_show.prototype.winActiveChk = function (obj)
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
    assembly_time_show.prototype.win_show = function (url, w, h)
    {
        if (!w) w = 800;     // �����
        if (!h) h = 600;     // �����
        showModalDialog(url, 'show_win', "dialogWidth:" + w + "px;dialogHeight:" + h + "px");
    }
    
    /***** ConditionForm �����ϥ����å��᥽�å�(����������λ���������ֹ�) *****/
    assembly_time_show.prototype.checkConditionForm = function (obj)
    {
        obj.targetPlanNo.value = obj.targetPlanNo.value.toUpperCase();
        if (!obj.targetPlanNo.value) {
            alert("�ײ��ֹ椬���Ϥ���Ƥ��ޤ���");
            obj.targetPlanNo.focus();
            obj.targetPlanNo.select();
            return false;
        }
        if (obj.targetPlanNo.value.length != 8) {
            alert("�ײ��ֹ�η���ϣ���Ǥ���");
            obj.targetPlanNo.focus();
            obj.targetPlanNo.select();
            return false;
        }
        this.parameter  = "&targetPlanNo=" + obj.targetPlanNo.value;
        this.parameter += "&regOnly=" + obj.regOnly.value;
        this.parameter += "&usedTime=" + obj.usedTime.value;
        this.parameter += "&workerCount=" + obj.workerCount.value;
        return true;
    }
    
    /***** ConditionForm �����ϥ����å��򤷤�Ajax�¹� *****/
    assembly_time_show.prototype.checkANDexecute = function (obj)
    {
        if (this.checkConditionForm(obj)) {
            this.AjaxLoadTable("ListTable", "showAjax");
        }
        return false;   // �ºݤ�submit�Ϥ����ʤ�
    }
    
    /***** ��Ͽ��������Ͽ�ֹ楯��å��ˤ�빩�����پȲ� Ajax�¹� *****/
    assembly_time_show.prototype.processExecute = function (assy_no, reg_no)
    {
        this.parameter  = "&targetAssyNo=" + assy_no + "&targetRegNo=" + reg_no;
        this.AjaxLoadTable("ProcessTable", "showAjax2");
    }
    
    /***** ���̹�����桼�����˰��´�̵��ɽ��������Ajax�ѥ���ɥ᥽�å� *****/
    // onReadyStateChange���٥�Ȥ�Ȥäƽ�������λ���Ƥ��ʤ�����WaitMessage����ϡ�
    // parameter : ListTable=���ɽ��, WaitMsg=������Ǥ������Ԥ���������
    assembly_time_show.prototype.AjaxLoadTable = function (showMenu, location)
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
            xmlhttp.open("GET", "assembly_time_show_Main.php"+parm);
            xmlhttp.send(null);
        } catch (e) {
            alert(url + "\n\n�򥪡��ץ����ޤ���\n\n" + e);
        }
    }
    
    /***** ���ɽ���ΰ�Υ��ꥢ���᥽�å� *****/
    assembly_time_show.prototype.viewClear = function ()
    {
        document.getElementById("showAjax").innerHTML = "";
    }
    
    /***** �᥽�åɼ����ˤ��WaitMessageɽ�� *****/
    assembly_time_show.prototype.WaitMessage = function ()
    {
        var WaitMsg = "<br><table width='100%' border='0'><tr><td align='center' style='font-size:20pt; font-weight:bold;'>������Ǥ������Ԥ���������<br><img src='/img/tnk-turbine.gif' width='68' height='72'></td></tr></table>";
        document.getElementById("showAjax").innerHTML = WaitMsg;
    }
    
    return this;    // Object Return
    
}   /* class assembly_time_show END  */


///// ���󥹥��󥹤�����
var AssemblyTimeShow = new assembly_time_show();

