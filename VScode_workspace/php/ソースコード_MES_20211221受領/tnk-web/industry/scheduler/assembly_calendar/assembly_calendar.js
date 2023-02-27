//////////////////////////////////////////////////////////////////////////////
// ��Ω�饤��Υ������� ���ƥʥ�         MVC View��(JavaScript���饹) //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/07/11 Created   assembly_calendar.js                                //
// 2006/12/05 win_open()�᥽�åɤ��ѿ�̾�Υߥ����� name �� winName        //
//////////////////////////////////////////////////////////////////////////////

///// �����Х��ѿ� _GDEBUG �ν���ͤ򥻥å�(��꡼������false�˥��åȤ���)
var _GDEBUG = false;

/****************************************************************************
/*          assembly_calendar class base_class �γ�ĥ���饹�����             *
/****************************************************************************
class assembly_calendar extends base_class
*/
///// �����ѡ����饹�ηѾ�
assembly_calendar.prototype = new base_class();    // base_class �ηѾ�
///// Constructer �����
function assembly_calendar()
{
    /***********************************************************************
    *                           Private properties                         *
    ***********************************************************************/
    // this.properties = false;                         // �ץ�ѥƥ����ν����
    this.blink_flag = 1;                                // blink_disp()�᥽�å���ǻ��Ѥ���
    this.blink_msg  = "";                               //     ��      , checkANDexecute(), viewClear()
    this.intervalID;                                    // �����Ѥ�intervalID
    this.blink_id_name;                                 // �����оݤ� ID̾ ID='???'
    this.parameter  = "";                               // Ajax�̿����Υѥ�᡼����
    this.maxYear;                                       // �����ϰϤκ�����
    
    var dateObj = new Date();
    this.maxYear = (dateObj.getFullYear() + 1);         // �ץ�ѥƥ������ͤ򥻥å�
    
    /************************************************************************
    *                           Public methods                              *
    ************************************************************************/
    /***** �ѥ�᡼�����ǻ��ꤵ�줿���֥������ȤΥ�����Ȥ˥ե������������� *****/
    assembly_calendar.prototype.set_focus = function (obj, status)
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
    assembly_calendar.prototype.blink_disp = function (id_name)
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
    assembly_calendar.prototype.stop_blink = function ()
    {
        document.getElementById(this.blink_id_name).innerHTML = this.blink_msg;
        clearInterval(this.intervalID);
    }
    
    /***** ���֥������Ȥ��ͤ���ʸ���Ѵ����� *****/
    assembly_calendar.prototype.obj_upper = function (obj)
    {
        obj.value = obj.value.toUpperCase();
        return true;
    }
    
    /***** ������礭���Υ��֥�����ɥ��������ɽ������ *****/
    /***** Windows XP SP2 �Ǥϥ������ƥ��ηٹ𤬽Ф�  *****/
    assembly_calendar.prototype.win_open = function (url, w, h, winName)
    {
        if (!winName) winName = "";
        if (!w) w = 800;     // �����
        if (!h) h = 600;     // �����
        var left = (screen.availWidth  - w) / 2;
        var top  = (screen.availHeight - h) / 2;
        w -= 10; h -= 30;   // ��Ĵ����ɬ��
        window.open(url, winName, 'width='+w+',height='+h+',resizable=yes,scrollbars=yes,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
    }
    
    /***** ���֥�����ɥ�¦��Window��Active�����å���Ԥ� *****/
    /***** <body onLoad="setInterval('templ.winActiveChk()',100)">*****/
    assembly_calendar.prototype.winActiveChk = function ()
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
    assembly_calendar.prototype.win_show = function (url, w, h)
    {
        if (!w) w = 800;     // �����
        if (!h) h = 600;     // �����
        showModalDialog(url, 'show_win', "dialogWidth:" + w + "px;dialogHeight:" + h + "px");
    }
    
    /***** �ꥹ�ȥܥå��������򤷤����դγ��ϡ���λ���դ򥻥åȤ��� *****/
    assembly_calendar.prototype.dateCreate = function (obj, flg, offset)
    {
        if (!obj) return;     // parameter�Υ����å�
        // �ܥ���̾�����������ؤˤʤäƤ�����˾ܺ��Խ����᤹
        if (document.ConditionForm.SetTime) {
            document.ConditionForm.SetTime.value = "�ܺ��Խ���";
        }
        if (offset < 0) {
            if (obj.targetDateY.value <= 2000) return;
        } else {
            if (obj.targetDateY.value >= this.maxYear) return;
        }
        var temp = parseInt(obj.targetDateY.value);     // ���ͤ��Ѵ�(���줬�ݥ����)
        temp += offset;
        temp += "";                                     // ʸ�����Ѵ�
        obj.targetDateY.value = temp;
        this.checkANDexecute(obj, flg);
        return;
    }
    
    /***** ���������Υ�����������إ᥽�å� *****/
    assembly_calendar.prototype.setTargetCalendar = function (obj, flg, status)
    {
        if (!obj) return;     // parameter�Υ����å�
        switch (status) {
        case 'BDSwitch':
            obj.BDSwitch.style.color = "blue";
            obj.Comment.style.color  = "";
            obj.SetTime.style.color  = "";
            obj.SetTime.value = "�ܺ��Խ���";
            break;
        case 'Comment':
            obj.BDSwitch.style.color = "";
            obj.Comment.style.color  = "blue";
            obj.SetTime.style.color  = "";
            obj.SetTime.value = "�ܺ��Խ���";
            break;
        case 'SetTime':
            obj.BDSwitch.style.color = "";
            obj.Comment.style.color  = "";
            obj.SetTime.style.color  = "blue";
            obj.SetTime.value = "�ܺ��Խ���";
            break;
        }
        obj.targetCalendar.value = status;
        this.checkANDexecute(obj, flg);
        return;
    }
    
    /***** ���������Υ��������̾ ���إ᥽�å� *****/
    assembly_calendar.prototype.actionNameSwitch = function ()
    {
        if (document.ConditionForm.SetTime) {
            document.ConditionForm.SetTime.value = "����������";
        }
    }
    
    /***** �оݴ��Υ����������������ޤ� *****/
    assembly_calendar.prototype.initFormat = function (obj, flg)
    {
        if (!obj) return;     // parameter�Υ����å�
        if (confirm("���������������֤��ᤷ�ޤ���\n\n�������Ǥ�����")) {
            // �ܥ���̾�����������ؤˤʤäƤ�����˾ܺ��Խ����᤹
            if (document.ConditionForm.SetTime) {
                document.ConditionForm.SetTime.value = "�ܺ��Խ���";
            }
            obj.targetFormat.value = "Execute";
            this.checkANDexecute(obj, flg);
            obj.targetFormat.value = "";    // ���� 2006/07/07
        }
        return;
    }
    
    /***** ���ϻ��֤Ƚ�λ���֤ǻ���(ʬ)�򥻥åȤ���Ǿ��ͤȺ����ͤΥ����å���Ԥ� *****/
    assembly_calendar.prototype.setTimeValue = function (formObj, targetObj)
    {
        if (!formObj.str_hour.options)   return;     // parameter�Υ����å�
        if (!formObj.str_minute.options) return;     // parameter�Υ����å�
        if (!formObj.end_hour.options)   return;     // parameter�Υ����å�
        if (!formObj.end_minute.options) return;     // parameter�Υ����å�
        if (!targetObj) return;     // parameter�Υ����å�
        var str_hour   = formObj.str_hour.options[formObj.str_hour.selectedIndex].value;
        var str_minute = formObj.str_minute.options[formObj.str_minute.selectedIndex].value;
        var end_hour   = formObj.end_hour.options[formObj.end_hour.selectedIndex].value;
        var end_minute = formObj.end_minute.options[formObj.end_minute.selectedIndex].value;
        if (str_hour < 24) {
            var str_date = "1970/01/01 ";   // ʸ���Υ��ڡ��������
        } else {
            var str_date = "1970/01/02 ";   // ʸ���Υ��ڡ��������
            str_hour = "00";
        }
        if (end_hour < 24) {
            var end_date = "1970/01/01 ";   // ʸ���Υ��ڡ��������
        } else {
            var end_date = "1970/01/02 ";   // ʸ���Υ��ڡ��������
            end_hour = "00";
        }
        targetObj.value = (Date.parse(end_date+end_hour+":"+end_minute+":00") - Date.parse(str_date+str_hour+":"+str_minute+":00")) / 1000 / 60;
        return;
        // ���߰ʲ��ϻ��Ѥ��ʤ�
        // targetObj.value = (Date.parse("1970/01/01 "+end_hour+":"+end_minute+":00") - Date.parse("1970/01/01 "+str_hour+":"+str_minute+":00")) / 1000 / 60;
        for (var i in formObj.str_hour.options) {
            formObj.str_hour.options[i]
        }
    }
    
    /***** ���åȤ��줿����(ʬ)�κǾ��ͤȺ����ͤΥ����å� *****/
    assembly_calendar.prototype.checkTimeValue = function (timeValue, formObj)
    {
        if (timeValue <= 0) {
            if (timeValue == "") timeValue = 0;
            alert(timeValue + " ʬ�Ǥ���Ͽ�Ǥ��ޤ���");
            return false;
        }
        if (timeValue > 1440) {
            alert(timeValue + " ʬ�� 24���� (1440ʬ) ��Ķ���Ƥ��ޤ���");
            return false;
        }
        if ((formObj.str_hour.value+formObj.str_minute.value) > "2400") {
            alert("���ϻ��֤� 24:00 ��Ķ���Ƥ��ޤ���");
            return false;
        }
        if ((formObj.end_hour.value+formObj.end_minute.value) > "2400") {
            alert("��λ���֤� 24:00 ��Ķ���Ƥ��ޤ���");
            return false;
        }
        return true;
    }
    
    /***** ConditionForm �����ϥ����å��᥽�å� *****/
    assembly_calendar.prototype.checkConditionForm = function (obj)
    {
        // obj.targetPartsNo.value = obj.targetPartsNo.value.toUpperCase();
        if (obj.targetDateY.value < 2000 || obj.targetDateY.value > this.maxYear) {
            alert("�оݴ����꤬�����Ǥ���");
            obj.targetDateY.focus();
            // obj.targetDateY.select();
            return false;
        }
        obj.targetDateStr.value = obj.targetDateY.value + "04";
        obj.targetDateEnd.value = parseInt(obj.targetDateY.value) + 1;  // parseInt()���ݥ����
        obj.targetDateEnd.value = obj.targetDateEnd.value + "03";
        /************
        if (!obj.targetPartsNo.value.match(/^[A-Z]{2}[0-9]{5}[-#]{1}[A-Z0-9]{1}$/)) {
            alert("�����ֹ椬�ְ�äƤ��ޤ���");
            obj.targetPartsNo.focus();
            obj.targetPartsNo.select();
            return false;
        }
        ************/
        this.parameter  = "&targetLine="   + obj.targetLine.value;
        this.parameter += "&targetDateY="   + obj.targetDateY.value;
        this.parameter += "&targetDateStr=" + obj.targetDateStr.value;
        this.parameter += "&targetDateEnd=" + obj.targetDateEnd.value;
        this.parameter += "&targetCalendar=" + obj.targetCalendar.value;
        if (obj.targetFormat.value) {
            this.parameter += "&targetFormat=" + obj.targetFormat.value;
        }
        return true;
    }
    
    /***** ConditionForm �����ϥ����å��򤷤�Ajax�¹� *****/
    assembly_calendar.prototype.checkANDexecute = function (obj, flg)
    {
        if (this.checkConditionForm(obj)) {
            // obj.submit();
            // return false;
            if (flg == 1) {
                this.AjaxLoadTable("Calendar", "showAjax");
            } else {
                this.AjaxLoadTable("List", "showAjax");
                // this.AjaxLoadTable("ListWin", "showAjax");
            }
            // ���ǤΥ�å��������ѹ�����
            // this.blink_msg = "�����ֹ�";
            // this.stop_blink();
        }
        return false;   // �ºݤ�submit�Ϥ����ʤ�
    }
    
    /***** ���̹�����桼�����˰��´�̵��ɽ��������Ajax�ѥ���ɥ᥽�å� *****/
    // onReadyStateChange���٥�Ȥ�Ȥäƽ�������λ���Ƥ��ʤ�����WaitMessage����ϡ�
    // parameter : ListTable=���ɽ��, WaitMsg=������Ǥ������Ԥ���������
    assembly_calendar.prototype.AjaxLoadTable = function (showMenu, location)
    {
        if (!location) location = "showAjax";   // Default�ͤ�����
        var parm = "?";
        parm += "showMenu=" + showMenu  // iframe�Τ����
        parm += this.parameter;
        if (showMenu == "ListWin") {    // �̥�����ɥ���ɽ��
            this.win_open("assembly_calendar_Main.php"+parm, 700, 350);
            return;
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
        var url = "assembly_calendar_Main.php" + parm;
        try {
            xmlhttp.open("GET", url);
            xmlhttp.send(null);
        } catch (e) {
            alert(url + "\n\n�򥪡��ץ����ޤ���\n\n" + e);
        }
    }
    
    /***** URL���� Ajax�ѥ��ɥ᥽�å� *****/
    assembly_calendar.prototype.AjaxLoadUrl = function (url)
    {
        if (!url) return;   // URL�����ꤵ��Ƥ��ʤ���н�λ
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
                document.getElementById("showAjax").innerHTML = xmlhttp.responseText;
            } else {
                // onReadyStateChange���٥�Ȥ�Ȥäƽ�������λ���Ƥ��ʤ�����WaitMessage����ϡ�
                document.getElementById("showAjax").innerHTML = "<br><table width='100%' border='0'><tr><td align='center' style='font-size:20pt; font-weight:bold;'>������Ǥ������Ԥ���������<br><img src='/img/tnk-turbine.gif' width='68' height='72'></td></tr></table>";
            }
        }
        try {
            xmlhttp.open("GET", url);
            xmlhttp.send(null);
        } catch (e) {
            alert(url + "\n\n�򥪡��ץ����ޤ���\n\n" + e);
        }
    }
    
    /***** ���ɽ���ΰ�Υ��ꥢ���᥽�å� *****/
    assembly_calendar.prototype.viewClear = function ()
    {
        document.getElementById("showAjax").innerHTML = "";
        // ���ǤΥ�å������������֤��᤹
        // this.blink_msg = "�����ֹ�";
        // document.getElementById(this.blink_id_name).innerHTML = this.blink_msg;
    }
    
    /***** �᥽�åɼ����ˤ��WaitMessageɽ�� *****/
    assembly_calendar.prototype.WaitMessage = function ()
    {
        var WaitMsg = "<br><table width='100%' border='0'><tr><td align='center' style='font-size:20pt; font-weight:bold;'>������Ǥ������Ԥ���������<br><img src='/img/tnk-turbine.gif' width='68' height='72'></td></tr></table>";
        document.getElementById("showAjax").innerHTML = WaitMsg;
    }
    
    return this;    // Object Return
    
}   /* class assembly_calendar END  */


///// ���󥹥��󥹤�����
var AssemblyCalendar = new assembly_calendar();

