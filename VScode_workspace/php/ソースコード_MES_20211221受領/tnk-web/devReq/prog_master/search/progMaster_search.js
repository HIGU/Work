//////////////////////////////////////////////////////////////////////////////
// �ץ���������˥塼 �ץ����θ���     MVC View��(JavaScript���饹) //
// Copyright (C) 2010 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2010/01/26 Created   progMaster_search.js                                //
//////////////////////////////////////////////////////////////////////////////

///// �����Х��ѿ� _GDEBUG �ν���ͤ򥻥å�(��꡼������false�˥��åȤ���)
var _GDEBUG = false;

/****************************************************************************
/*           progMaster_search class base_class �γ�ĥ���饹�����           *
/****************************************************************************
class progMaster_search extends base_class
*/
///// �����ѡ����饹�ηѾ�
progMaster_search.prototype = new base_class();    // base_class �ηѾ�
///// Constructer �����
function progMaster_search()
{
    /***********************************************************************
    *                           Private properties                         *
    ***********************************************************************/
    // this.properties = false;                         // �ץ�ѥƥ����ν����
    this.blink_flag = 1;                                // blink_disp()�᥽�å���ǻ��Ѥ���
    this.blink_msg  = "�����ֹ�";                       //     ��      , checkANDexecute(), viewClear()
    this.intervalID;                                    // �����Ѥ�intervalID
    this.blink_id_name;                                 // �����оݤ� ID̾ ID='???'
    this.parameter  = "";                               // Ajax�̿����Υѥ�᡼����
    
    /************************************************************************
    *                           Public methods                              *
    ************************************************************************/
    /***** �ѥ�᡼�����ǻ��ꤵ�줿���֥������ȤΥ�����Ȥ˥ե������������� *****/
    progMaster_search.prototype.set_focus = function (obj, status)
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
    progMaster_search.prototype.blink_disp = function (id_name)
    {
        this.blink_id_name = id_name;
        if (this.blink_flag == 1) {
            // ����ͤ�ץ�ѥƥ��ǻ��ꤷ������ʲ��򥳥���
            // this.blink_msg = document.getElementById(id_name).innerHTML;
            document.getElementById(id_name).innerHTML = "&nbsp;";
            this.blink_flag = 2;
        } else {
            document.getElementById(id_name).innerHTML = this.blink_msg;
            this.blink_flag = 1;
        }
    }
    
    /***** ���ǥ��ȥåץ᥽�å� *****/
    progMaster_search.prototype.stop_blink = function ()
    {
        document.getElementById(this.blink_id_name).innerHTML = this.blink_msg;
        clearInterval(this.intervalID);
    }
    
    /***** ���֥������Ȥ��ͤ���ʸ���Ѵ����� *****/
    progMaster_search.prototype.obj_upper = function (obj)
    {
        obj.value = obj.value.toUpperCase();
        return true;
    }
    
    /***** ������礭���Υ��֥�����ɥ��������ɽ������ *****/
    /***** Windows XP SP2 �Ǥϥ������ƥ��ηٹ𤬽Ф�  *****/
    progMaster_search.prototype.win_open = function (url, w, h)
    {
        if (!w) w = 800;     // �����
        if (!h) h = 600;     // �����
        var left = (screen.availWidth  - w) / 2;
        var top  = (screen.availHeight - h) / 2;
        w -= 10; h -= 30;   // ��Ĵ����ɬ��
        window.open(url, '', 'width='+w+',height='+h+',resizable=yes,scrollbars=yes,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
    }
    
    /***** ���֥�����ɥ�¦��Window��Active�����å���Ԥ� *****/
    /***** <body onLoad="setInterval('templ.winActiveChk()',100)">*****/
    progMaster_search.prototype.winActiveChk = function ()
    {
        if (document.all) {     // IE�ʤ�
            if (document.hasFocus() == false) {     // IE5.5�ʾ�ǻȤ���
                window.focus();
                return;
            }
            return;
        } else {                // NN �ʤ�ȥ�ꥭ�å�
            window.focus();
            return;
        }
    }
    
    /***** ������礭���Υ⡼�������������ɽ������ *****/
    /***** IE ���ѤʤΤ� Windows XP SP2 �Ǥϥ������ƥ��ηٹ𤬽Ф� *****/
    /***** ����������ǥꥯ�����Ȥ�Ф����ϥե졼����ڤäƹԤ� *****/
    progMaster_search.prototype.win_show = function (url, w, h)
    {
        if (!w) w = 800;     // �����
        if (!h) h = 600;     // �����
        showModalDialog(url, 'show_win', "dialogWidth:" + w + "px;dialogHeight:" + h + "px");
    }
    
    /***** �ꥹ�ȥܥå��������򤷤����դγ��ϡ���λ���դ򥻥åȤ��� *****/
    progMaster_search.prototype.dateCreate = function (obj)
    {
        if (!obj) return;     // parameter�Υ����å�
        obj.targetDateStr.value = obj.targetDateYM.value + "01";
        var yyyy = obj.targetDateStr.value.substr(0, 4);
        var mm   = obj.targetDateStr.value.substr(4, 2);
        if (mm == 12) {
            yyyy = (yyyy - 0 + 1);      // ʸ�������ͤ��Ѵ����뤿�� - 0���Ƥ��롣
            mm = 0;
        }
        var dateEnd = new Date(yyyy, mm, 1, 0, 0, 0)    // ��������ե��֥������Ȥ����
        dateEnd.setTime(dateEnd.getTime() - 1000);      // �������ˤ����������ˤ���
        yyyy = dateEnd.getYear();
        mm   = dateEnd.getMonth() + 1;
        var dd = dateEnd.getDate();
        if (yyyy < 2000) { yyyy += 1900; }
        if (mm < 10) { mm = "0" + mm; }
        if (dd < 10) { dd = "0" + dd; }
        obj.targetDateEnd.value = (yyyy + "" + mm + dd);
        return;
    }
    
    /***** ConditionForm �����ϥ����å��᥽�å� *****/
    progMaster_search.prototype.checkConditionForm = function (obj)
    {
        /************
        if (!obj.targetPartsNo.value.match(/^[A-Z0-9]{7}[-#]{1}[A-Z0-9]{1}$/)) {
            alert("�����ֹ椬�ְ�äƤ��ޤ���");
            obj.targetPartsNo.focus();
            obj.targetPartsNo.select();
            return false;
        }
        ************/
        var value = "";
        for (var i=0; i<obj.elements.length; i++) {
            if (obj.elements[i].value == "") continue;      // �֥�󥯤��оݤ��鳰��
            value += ("&" + obj.elements[i].name + "=" + obj.elements[i].value);
        }
        // this.parameter = value.substring(1);    // �ǽ��&����
        this.parameter = value;
        
        return true;
    }
    
    /***** ConditionForm �����ϥ����å��򤷤�Ajax�¹� *****/
    progMaster_search.prototype.checkANDexecute = function (obj, flg)
    {
        var result = true;
        if (this.checkConditionForm(obj)) {
            if (flg == 1) {
                resuslt = this.AjaxLoadTable("List", "showAjax");
            } else {
                this.AjaxLoadTable("ListWin", "showAjax");
            }
            // ���ǤΥ�å��������ѹ�����
            // this.blink_msg = "�����ֹ�";
            // this.stop_blink();
        }
        if (result) {
            return false;   // �ºݤ�submit�Ϥ����ʤ�
        } else {
            return true;    // Ajax���ԤʤΤ�submit������
        }
    }
    
    /***** ���̹�����桼�����˰��´�̵��ɽ��������Ajax�ѥ���ɥ᥽�å� *****/
    // onReadyStateChange���٥�Ȥ�Ȥäƽ�������λ���Ƥ��ʤ�����WaitMessage����ϡ�
    // parameter : ListTable=���ɽ��, WaitMsg=������Ǥ������Ԥ���������
    progMaster_search.prototype.AjaxLoadTable = function (showMenu, location)
    {
        if (!location) location = "showAjax";   // Default�ͤ�����
        var parm = "?";
        parm += "Action=Search&showMenu=" + showMenu  // iframe�Τ����
        parm += this.parameter;
        if (showMenu == "ListWin") {    // �̥�����ɥ���ɽ��
            this.win_open("progMaster_search_Main.php"+parm, 900, 700);
            return true;
        }
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
                return false;
                alert("�����ѤΥ֥饦������̤�б��Ǥ���\n\n" + e);
            }
        }
        xmlhttp.onreadystatechange = function () {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                document.getElementById(location).innerHTML = xmlhttp.responseText;
            } else if (xmlhttp.readyState == 4 && xmlhttp.status == 404) {
                alert("�ǡ����μ����˼��Ԥ��ޤ���������ô���Ԥ�Ϣ���Ʋ�������");
            } else {
                // onReadyStateChange���٥�Ȥ�Ȥäƽ�������λ���Ƥ��ʤ�����WaitMessage����ϡ�
                document.getElementById(location).innerHTML = "<br><table width='100%' border='0'><tr><td align='center' style='font-size:20pt; font-weight:bold;'>������Ǥ������Ԥ���������<br><img src='/img/tnk-turbine.gif' width='68' height='72'></td></tr></table>";
            }
        }
        try {
            /***** GET�᥽�åɤ���Ѥ�����
            xmlhttp.open("GET", "progMaster_search_Main.php" + parm, true);  // true=Ajax������ ��Ʊ���̿���Ԥ������ꤷ�ʤ����true
            xmlhttp.setRequestHeader("If-Modified-Since", "Thu, 01 Jun 1970 00:00:00 GMT"); // IE�Υ���å����б�
            xmlhttp.send("");   // null �� "" Linux �� Konqueror/3.3 �ǥ��顼�б�
            *****/
            xmlhttp.open("POST", "progMaster_search_Main.php", true);
            xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xmlhttp.send(parm.substr(1));
        } catch (e) {
            return false;
            alert(url + "\n\n�򥪡��ץ����ޤ���\n\n" + e);
        }
        return true;
    }
    
    /***** ���ɽ���ΰ�Υ��ꥢ���᥽�å� *****/
    progMaster_search.prototype.viewClear = function ()
    {
        document.getElementById("showAjax").innerHTML = "";
        // ���ǤΥ�å������������֤��᤹
        // this.blink_msg = "�����ֹ�";
        // document.getElementById(this.blink_id_name).innerHTML = this.blink_msg;
    }
    
    /***** �᥽�åɼ����ˤ��WaitMessageɽ�� *****/
    progMaster_search.prototype.WaitMessage = function ()
    {
        var WaitMsg = "<br><table width='100%' border='0'><tr><td align='center' style='font-size:20pt; font-weight:bold;'>������Ǥ������Ԥ���������<br><img src='/img/tnk-turbine.gif' width='68' height='72'></td></tr></table>";
        document.getElementById("showAjax").innerHTML = WaitMsg;
    }
    
    return this;    // Object Return
    
}   /* class progMaster_search END  */


///// ���󥹥��󥹤�����
var ProgMasterSearch = new progMaster_search();

