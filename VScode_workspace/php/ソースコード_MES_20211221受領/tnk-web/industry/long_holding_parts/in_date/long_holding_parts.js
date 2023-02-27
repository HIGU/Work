//////////////////////////////////////////////////////////////////////////////
// Ĺ����α���ʤξȲ� �ǽ�����������Ǻ߸ˤ��� MVC View��(JavaScript���饹) //
// Copyright (C) 2006-2019 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/04/03 Created    long_holding_parts.js                              //
// 2006/04/06 ����иˤ��ϰϵڤӲ��(ʪ��ư��)�ξ�索�ץ��������        //
//            checkbox �� checked == true �ǥ����å�(value�Ͼ�ˤ���)       //
// 2008/03/11 ����������Ĺ����Ǻǽ����������ϰϤ�1ǯ������11���������ѹ�   //
//                                                                     ��ë //
// 2011/07/28 �Ƶ�����ɲ�                                             ��ë //
// 2013/10/10 �и��ϰϤ�60�������ޤ���ФǤ���褦���ѹ�               ��ë //
// 2019/01/28 �ġ�����ɲá��Х���롦ɸ��򥳥��Ȳ�                 ��ë //
//////////////////////////////////////////////////////////////////////////////

///// �����Х��ѿ� _GDEBUG �ν���ͤ򥻥å�(��꡼������false�˥��åȤ���)
var _GDEBUG = false;

/****************************************************************************
/*     long_holding_parts class base_class �γ�ĥ���饹�����            *
/****************************************************************************
class long_holding_parts extends base_class
*/
///// �����ѡ����饹�ηѾ�
long_holding_parts.prototype = new base_class();    // base_class �ηѾ�
///// Constructer �����
function long_holding_parts()
{
    /***********************************************************************
    *                           Private properties                         *
    ***********************************************************************/
    // this.properties = false;                         // �ץ�ѥƥ����ν����
    this.blink_flag = 1;                                // blink_disp()�᥽�å���ǻ��Ѥ���
    this.blink_msg  = "�ǽ������������ʥ��롼�פ���ꤷ�Ʋ�������";
                                                        //     ��      , checkANDexecute(), viewClear()
    this.parameter  = "";                               // Ajax�̿����Υѥ�᡼����
    
    /************************************************************************
    *                           Public methods                              *
    ************************************************************************/
    /***** �ѥ�᡼�����ǻ��ꤵ�줿���֥������ȤΥ�����Ȥ˥ե������������� *****/
    long_holding_parts.prototype.set_focus = function (obj, status)
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
    long_holding_parts.prototype.blink_disp = function (id_name)
    {
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
    
    /***** ���֥������Ȥ��ͤ���ʸ���Ѵ����� *****/
    long_holding_parts.prototype.obj_upper = function (obj)
    {
        obj.value = obj.value.toUpperCase();
        return true;
    }
    
    /***** ������礭���Υ��֥�����ɥ��������ɽ������ *****/
    /***** Windows XP SP2 �Ǥϥ������ƥ��ηٹ𤬽Ф�  *****/
    long_holding_parts.prototype.win_open = function (url, w, h)
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
    long_holding_parts.prototype.winActiveChk = function ()
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
    long_holding_parts.prototype.win_show = function (url, w, h)
    {
        if (!w) w = 800;     // �����
        if (!h) h = 600;     // �����
        showModalDialog(url, 'show_win', "dialogWidth:" + w + "px;dialogHeight:" + h + "px");
    }
    
    /***** ConditionForm �����ϥ����å��᥽�å�(����������λ���������ֹ�) *****/
    long_holding_parts.prototype.checkConditionForm = function (obj)
    {
        // obj.targetDate.value = obj.targetDate.value.toUpperCase();
        if (!obj.targetDate.value) {
            alert("������ ���� ��������Ϥ���Ƥ��ޤ���");
            obj.targetDate.focus();
            // obj.targetDate.select();
            return false;
        }
        if (!this.isDigit(obj.targetDate.value)) {
            alert("������ ���� ����Ͽ��������Ϥ��Ʋ�������");
            obj.targetDate.focus();
            // obj.targetDate.select();
            return false;
        }
        if (obj.targetDate.value < 11 || obj.targetDate.value > 84) {
            alert("������ ���� �����11�����84����Ǥ���");
            obj.targetDate.focus();
            // obj.targetDate.select();
            return false;
        }
        ///// �ϰϷ��
        if (!obj.targetDateSpan.value) {
            alert("������ �ϰ� ��������Ϥ���Ƥ��ޤ���");
            obj.targetDateSpan.focus();
            // obj.targetDateSpan.select();
            return false;
        }
        if (!this.isDigit(obj.targetDateSpan.value)) {
            alert("������ �ϰ� ����Ͽ��������Ϥ��Ʋ�������");
            obj.targetDateSpan.focus();
            // obj.targetDateSpan.select();
            return false;
        }
        if (obj.targetDateSpan.value < 1 || obj.targetDateSpan.value > 120) {
            alert("������ �ϰ� �����1�����120����Ǥ���");
            obj.targetDateSpan.focus();
            // obj.targetDateSpan.select();
            return false;
        }
        ///// ����иˤ��ϰϷ��
        if (!obj.targetOutDate.value) {
            alert("����и� �ϰ� ��������Ϥ���Ƥ��ޤ���");
            obj.targetOutDate.focus();
            // obj.targetOutDate.select();
            return false;
        }
        if (!this.isDigit(obj.targetOutDate.value)) {
            alert("����и� �ϰ� ����Ͽ��������Ϥ��Ʋ�������");
            obj.targetOutDate.focus();
            // obj.targetOutDate.select();
            return false;
        }
        if (obj.targetOutDate.value < 1 || obj.targetOutDate.value > 60) {
            alert("����и� �ϰ� �����1�����60����Ǥ���");
            obj.targetOutDate.focus();
            // obj.targetOutDate.select();
            return false;
        }
        ///// ����иˤ��ϰ���Ǥβ��
        if (!obj.targetOutCount.value) {
            alert("����и� �ϰ���Ǥ� ��������Ϥ���Ƥ��ޤ���");
            obj.targetOutCount.focus();
            // obj.targetOutCount.select();
            return false;
        }
        if (!this.isDigit(obj.targetOutCount.value)) {
            alert("����и� �ϰ���Ǥ� ����Ͽ��������Ϥ��Ʋ�������");
            obj.targetOutCount.focus();
            // obj.targetOutCount.select();
            return false;
        }
        if (obj.targetOutCount.value < 0 || obj.targetOutCount.value > 2) {
            alert("����и� �ϰ���Ǥ� ����ϣ��󤫤飲��Ǥ���");
            obj.targetOutCount.focus();
            // obj.targetOutCount.select();
            return false;
        }
        switch (obj.targetDivision.value) {
        case "AL" :
        case "CA" :
        case "CH" :
        case "CS" :
        case "LA" :
        /*
        case "LH" :
        case "LB" :
        */
        case "TA" :
        case "OT" : // OTHER ����¾ ��������ʬ
            obj.exec.focus();       // obj.targetDivision �Υե��������򳰤�����
            break;
        default :
            alert("���ʶ�ʬ�������Ǥ���");
            obj.targetDivision.focus();
            return false;
        }
        this.parameter  = "&targetDate=" + obj.targetDate.value;
        this.parameter += "&targetDateSpan=" + obj.targetDateSpan.value;
        this.parameter += "&targetDivision=" + obj.targetDivision.value;
        if (obj.targetOutFlg.checked == true) {
            this.parameter += "&targetOutFlg=" + obj.targetOutFlg.value;
        } else {
            this.parameter += "&targetOutFlg=off";
        }
        this.parameter += "&targetOutDate=" + obj.targetOutDate.value;
        this.parameter += "&targetOutCount=" + obj.targetOutCount.value;
        return true;
    }
    
    /***** ConditionForm �����ϥ����å��򤷤�Ajax�¹� *****/
    long_holding_parts.prototype.checkANDexecute = function (obj)
    {
        if (this.checkConditionForm(obj)) {
            this.AjaxLoadTable("List", "showAjax");
        }
        // ���ǤΥ�å��������ѹ�����
        this.blink_msg = "�����ֹ�򥯥�å�����к߸˷����ɽ�����ޤ������ܥ���å��ǥ����Ȥ��ޤ���";
        return false;   // �ºݤ�submit�Ϥ����ʤ�
    }
    
    /***** ���̹�����桼�����˰��´�̵��ɽ��������Ajax�ѥ���ɥ᥽�å� *****/
    // onReadyStateChange���٥�Ȥ�Ȥäƽ�������λ���Ƥ��ʤ�����WaitMessage����ϡ�
    // parameter : ListTable=���ɽ��, WaitMsg=������Ǥ������Ԥ���������
    long_holding_parts.prototype.AjaxLoadTable = function (showMenu, location)
    {
        if (!location) location = "showAjax";   // Default�ͤ�����
        var parm = "?";
        parm += "showMenu=" + showMenu  // iframe�Τ����
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
            xmlhttp.open("GET", "long_holding_parts_Main.php"+parm);
            xmlhttp.send(null);
        } catch (e) {
            alert(url + "\n\n�򥪡��ץ����ޤ���\n\n" + e);
        }
    }
    
    /***** ���ɽ���ΰ�Υ��ꥢ���᥽�å� *****/
    long_holding_parts.prototype.viewClear = function ()
    {
        document.getElementById("showAjax").innerHTML = "";
        // ���ǤΥ�å������������֤��᤹
        this.blink_msg = "�ǽ������������ʥ��롼�פ���ꤷ�Ʋ�������";
    }
    
    /***** �᥽�åɼ����ˤ��WaitMessageɽ�� *****/
    long_holding_parts.prototype.WaitMessage = function ()
    {
        var WaitMsg = "<br><table width='100%' border='0'><tr><td align='center' style='font-size:20pt; font-weight:bold;'>������Ǥ������Ԥ���������<br><img src='/img/tnk-turbine.gif' width='68' height='72'></td></tr></table>";
        document.getElementById("showAjax").innerHTML = WaitMsg;
    }
    
    /***** long_holding_parts_ViewHeader.html�ѤΥ����ȹ��� ��Ĵ ɽ�� �᥽�å� *****/
    long_holding_parts.prototype.highlight = function ()
    {
        if (location.search.substr(1, 9) == "item=tana") {
            // document.getElementById("tana").style.color = "white";
            document.getElementById("tana").style.backgroundColor = "#ffffc6";
        } else if (location.search.substr(1, 10) == "item=parts") {
            // document.getElementById("parts").style.color = "white";
            document.getElementById("parts").style.backgroundColor = "#ffffc6";
        } else if (location.search.substr(1, 9) == "item=name") {
            // document.getElementById("name").style.color = "white";
            document.getElementById("name").style.backgroundColor = "#ffffc6";
        } else if (location.search.substr(1, 9) == "item=parent") {
            // document.getElementById("name").style.color = "white";
            document.getElementById("parent").style.backgroundColor = "#ffffc6";
        } else if (location.search.substr(1, 9) == "item=date") {
            // document.getElementById("date").style.color = "white";
            document.getElementById("date").style.backgroundColor = "#ffffc6";
        } else if (location.search.substr(1, 11) == "item=in_pcs") {
            // document.getElementById("in_pcs").style.color = "white";
            document.getElementById("in_pcs").style.backgroundColor = "#ffffc6";
        } else if (location.search.substr(1, 10) == "item=stock") {
            // document.getElementById("stock").style.color = "white";
            document.getElementById("stock").style.backgroundColor = "#ffffc6";
        } else if (location.search.substr(1, 10) == "item=tanka") {
            // document.getElementById("tanka").style.color = "white";
            document.getElementById("tanka").style.backgroundColor = "#ffffc6";
        } else if (location.search.substr(1, 10) == "item=price") {
            // document.getElementById("price").style.color = "white";
            document.getElementById("price").style.backgroundColor = "#ffffc6";
        }
    }
    
    return this;    // Object Return
    
}   /* class long_holding_parts END  */


///// ���󥹥��󥹤�����
var LongHoldingParts = new long_holding_parts();

