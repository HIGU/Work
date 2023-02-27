//////////////////////////////////////////////////////////////////////////////
// ���Ƚ���ν��� ��� �Ȳ�                    MVC View��(JavaScript���饹) //
// Copyright (C) 2008 - 2017 Norihisa.Ohya usoumu@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2008/09/22 Created    working_hours_report.js                            //
// 2017/06/02 ����Ĺ���� �ܳʲ�ư                                           //
// 2017/06/15 ���ϡ���λ���դ�20160331�����ξ�票�顼��ɽ��                //
//            �ºݤ�20140401����ǡ�����¸�ߤ��Ƥ���                        //
//            ��ʬ�Τߥ��顼�оݳ�                                          //
// 2017/06/28 �������ΤߤǤ�ñ��������ǽ                                    //
// 2017/06/29 �����̾Ȳ���б��ʹ���Ĺ�����                                //
//////////////////////////////////////////////////////////////////////////////

///// �����Х��ѿ� _GDEBUG �ν���ͤ򥻥å�(��꡼������false�˥��åȤ���)
var _GDEBUG = false;

/****************************************************************************
/*     working_hours_report class base_class �γ�ĥ���饹�����             *
/****************************************************************************
class working_hours_report extends base_class
*/
///// �����ѡ����饹�ηѾ�
working_hours_report.prototype = new base_class();    // base_class �ηѾ�
///// Constructer �����
function working_hours_report()
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
    working_hours_report.prototype.set_focus = function (obj, status)
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
    working_hours_report.prototype.blink_disp = function (id_name)
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
    working_hours_report.prototype.stop_blink = function ()
    {
        document.getElementById(this.blink_id_name).innerHTML = this.blink_msg;
        clearInterval(this.intervalID);
    }
    
    /***** ���֥������Ȥ��ͤ���ʸ���Ѵ����� *****/
    working_hours_report.prototype.obj_upper = function (obj)
    {
        obj.value = obj.value.toUpperCase();
        return true;
    }
    
    /***** ������礭���Υ��֥�����ɥ��������ɽ������ *****/
    /***** Windows XP SP2 �Ǥϥ������ƥ��ηٹ𤬽Ф�  *****/
    working_hours_report.prototype.win_open = function (url, w, h)
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
    working_hours_report.prototype.winActiveChk = function ()
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
    working_hours_report.prototype.win_show = function (url, w, h)
    {
        if (!w) w = 800;     // �����
        if (!h) h = 600;     // �����
        showModalDialog(url, 'show_win', "dialogWidth:" + w + "px;dialogHeight:" + h + "px");
    }
    
    /***** �ꥹ�ȥܥå��������򤷤����դγ��ϡ���λ���դ򥻥åȤ��� *****/
    working_hours_report.prototype.dateCreate = function (obj)
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
    working_hours_report.prototype.checkConditionForm = function (obj)
    {
        // obj.targetPartsNo.value = obj.targetPartsNo.value.toUpperCase();
        if (!obj.targetDateStr.value) {
            alert("�������դ����Ϥ���Ƥ��ޤ���");
            obj.targetDateStr.focus();
            obj.targetDateStr.select();
            return false;
        }
        if (obj.targetDateStr.value.length != 8) {
            alert("�������դη���ϣ���Ǥ���");
            obj.targetDateStr.focus();
            obj.targetDateStr.select();
            return false;
        }
        if (!this.isDigit(obj.targetDateStr.value)) {
            alert("�������դϿ��������Ϥ��Ʋ�������");
            obj.targetDateStr.focus();
            obj.targetDateStr.select();
            return false;
        }
        if (obj.use_uid.value != 300144) {
            if (obj.targetDateStr.value < 20160401) {
                alert("�������դ�2016ǯ4��1���ʹߤˤ��Ʋ�������");
                obj.targetDateStr.focus();
                obj.targetDateStr.select();
                return false;
            }
        }
        if (obj.targetDateStr.value) {
            if (obj.targetDateEnd.value) {
                if (!obj.targetDateEnd.value) {
                    alert("��λ���դ����Ϥ���Ƥ��ޤ���");
                    obj.targetDateEnd.focus();
                    obj.targetDateEnd.select();
                    return false;
                }
                if (obj.targetDateEnd.value.length != 8) {
                    alert("��λ���դη���ϣ���Ǥ���");
                    obj.targetDateEnd.focus();
                    obj.targetDateEnd.select();
                    return false;
                }
                if (!this.isDigit(obj.targetDateEnd.value)) {
                    alert("��λ���դϿ��������Ϥ��Ʋ�������");
                    obj.targetDateEnd.focus();
                    obj.targetDateEnd.select();
                    return false;
                }
                if (obj.use_uid.value != 300144) {
                    if (obj.targetDateEnd.value < 20160401) {
                        alert("��λ���դ�2016ǯ4��1���ʹߤˤ��Ʋ�������");
                        obj.targetDateEnd.focus();
                        obj.targetDateEnd.select();
                        return false;
                    }
                }
            } else {
                obj.targetDateEnd.value = obj.targetDateStr.value
            }
        }
        if (!obj.targetSection.value) {
            //if (!obj.uid.value) {
                alert("��������򤷤Ƥ���������");
                return false;
            //}
            //if (obj.uid.value.length != 6) {
            //alert("�Ұ��ֹ�η���ϣ���Ǥ���");
            //obj.uid.focus();
            //obj.uid.select();
            //return false;
            //}
            //if (!this.isDigit(obj.uid.value)) {
            //    alert("�Ұ��ֹ�Ͽ��������Ϥ��Ʋ�������");
            //    obj.uid.focus();
            //    obj.uid.select();
            //    return false;
            //}
        }
        // return false;   // �ǥХå���
        /************
        if (!obj.targetPartsNo.value.match(/^[A-Z0-9]{7}[-#]{1}[A-Z0-9]{1}$/)) {
            alert("�����ֹ椬�ְ�äƤ��ޤ���");
            obj.targetPartsNo.focus();
            obj.targetPartsNo.select();
            return false;
        }
        ************/
        this.parameter  = "&targetDateYM=" + obj.targetDateYM.value;
        this.parameter += "&targetDateStr=" + obj.targetDateStr.value;
        this.parameter += "&targetDateEnd=" + obj.targetDateEnd.value;
        this.parameter += "&targetSection=" + obj.targetSection.value;
        this.parameter += "&targetPosition=" + obj.targetPosition.value;
        var i;
        for (i = 0; i < obj.formal.length; i++) {
            if (obj.formal[i].checked) {
                this.parameter += "&formal=" + obj.formal[i].value;
            }
        }
        return true;
    }
    
    /***** ConditionForm �����ϥ����å��᥽�å� �����ǧ��*****/
    working_hours_report.prototype.checkConditionFormConfirm = function (obj)
    {
        if (!obj.targetDateStr.value) {
            alert("�������դ����Ϥ���Ƥ��ޤ���");
            obj.targetDateStr.focus();
            obj.targetDateStr.select();
            return false;
        }
        if (obj.targetDateStr.value.length != 8) {
            alert("�������դη���ϣ���Ǥ���");
            obj.targetDateStr.focus();
            obj.targetDateStr.select();
            return false;
        }
        if (!this.isDigit(obj.targetDateStr.value)) {
            alert("�������դϿ��������Ϥ��Ʋ�������");
            obj.targetDateStr.focus();
            obj.targetDateStr.select();
            return false;
        }
        if (obj.targetDateStr.value) {
            if (obj.targetDateEnd.value) {
                if (!obj.targetDateEnd.value) {
                    alert("��λ���դ����Ϥ���Ƥ��ޤ���");
                    obj.targetDateEnd.focus();
                    obj.targetDateEnd.select();
                    return false;
                }
                if (obj.targetDateEnd.value.length != 8) {
                    alert("��λ���դη���ϣ���Ǥ���");
                    obj.targetDateEnd.focus();
                    obj.targetDateEnd.select();
                    return false;
                }
                if (!this.isDigit(obj.targetDateEnd.value)) {
                    alert("��λ���դϿ��������Ϥ��Ʋ�������");
                    obj.targetDateEnd.focus();
                    obj.targetDateEnd.select();
                    return false;
                }
            }
        } else {
            obj.targetDateEnd.value = obj.targetDateStr.value
        }
        this.parameter  = "&targetDateYM=" + obj.targetDateYM.value;
        this.parameter += "&targetDateStr=" + obj.targetDateStr.value;
        this.parameter += "&targetDateEnd=" + obj.targetDateEnd.value;
        return true;
    }
    
    /***** ConditionForm ��Ajax�¹� �����Ѥ߹���*****/
    working_hours_report.prototype.Confirmexecute = function (sid, str_date, end_date, section)
    {
        if (confirm("������ǧ�Ѥˤ��Ƥ�����Ǥ�����\n ��ǧ�Ѥˤ���ȸ��ˤ��᤻�ޤ����������Ƥ���Ͽ�Ͻ���äƤޤ�����")) {
                var parm = "&";
                parm += "section_id=" + sid;
                parm += "&str_date=" + str_date;
                parm += "&end_date=" + end_date;
                parm += "&targetSection=" + section;
                document.CorrectForm.action="../working_hours_report_Main.php?showMenu=CondForm&ConfirmFlg=y" + parm;
                document.CorrectForm.submit();
        }
    }
    /***** ConditionForm ��Ajax�¹� �����Ѥ߹���*****/
    working_hours_report.prototype.Confirmoneexecute = function (tnkuid, str_date, end_date, sid, section)
    {
        if (confirm("������ǧ�Ѥˤ��Ƥ�����Ǥ�����\n ��ǧ����������ϴְ�äƤ��ޤ��󤫡���")) {
                var uid              = tnkuid.slice(3);
                var form_name        = "CorrectForm" + uid;
                var select_name      = "ConfirmFlg" + uid;
                var $elementReference = document.getElementById( select_name );
                var $selectedIndex    = $elementReference.selectedIndex;
                var confirm_flg       = $elementReference.options[$selectedIndex].value;
                var parm             = "&";
                parm += "uid=" + uid;
                parm += "&str_date=" + str_date;
                parm += "&end_date=" + end_date;
                parm += "&confirm_flg=" + confirm_flg;
                parm += "&section_id=" + sid;
                parm += "&targetSection=" + section;
                document.CorrectForm.action="../working_hours_report_Main.php?showMenu=CondForm&ConfirmOneFlg=y" + parm;
                document.CorrectForm.submit();
        }
    }
    /***** ConditionForm ��select�����*****/
    working_hours_report.prototype.getSelected = function (select_name)
    {
        var $elementReference = document.getElementById( "ConfirmFlg005789" );
        var $selectedIndex = $elementReference.selectedIndex;
        var $value = $elementReference.options[$selectedIndex].value;
        document.getElementById( "selectOutputIndex" ).innerHTML = $selectedIndex;
        document.getElementById( "selectOutputValue" ).innerHTML = $value;
    }
    
    /***** ConditionForm ��Ajax�¹� �����Ѥ߹���*****/
    working_hours_report.prototype.Correctexecute = function (uid, date, flg)
    {
        if (flg == 2) {
            if (confirm("���������������Ѥˤ��Ƥ�����Ǥ�����")) {
                var parm = "&";
                parm += "user_id=" + uid;
                parm += "&date=" + date;
                document.CorrectForm.action="../working_hours_report_Main.php?showMenu=CondForm&CorrectFlg=y&CancelFlg=n" + parm;
                document.CorrectForm.submit();
            }
        } else {
            if (confirm("�����Ѥ���ä��Ƥ�����Ǥ�����")) {
                var parm = "&";
                parm += "user_id=" + uid;
                parm += "&date=" + date;
                document.CorrectForm.action="../working_hours_report_Main.php?showMenu=CondForm&CorrectFlg=y&CancelFlg=y" + parm;
                document.CorrectForm.submit();
            }
        }
    }
    
    /***** ConditionForm �����ϥ����å��򤷤�Ajax�¹� *****/
    working_hours_report.prototype.checkANDexecute = function (obj, flg)
    {
        if (flg == 3) {
            this.AjaxLoadTable("Correct", "showAjax");
        } else if (flg == 4) {
            this.AjaxLoadTable("CorrectList", "showAjax");
        } else if (flg == 5) {
            this.AjaxLoadTable("CorrectEndList", "showAjax");
        } else if (flg == 6) {
            this.AjaxLoadTable("List", "showAjax");
        } else if (flg == 7) {
            if (this.checkConditionFormConfirm(obj)) {
                this.AjaxLoadTable("ConfirmList", "showAjax");
            }
        } else if (flg == 8) {
            this.AjaxLoadTable("ConfirmList", "showAjax");
        } else if (flg == 9) {
            this.AjaxLoadTable("CorrectList", "showAjax");
        } else if (flg == 11) {
            this.AjaxLoadTable("MailList", "showAjax");
        } else if (flg == 2) {
            
        } else if (this.checkConditionForm(obj)) {
            if (flg == 1) {
                this.AjaxLoadTable("List", "showAjax");
            } else if (flg == 10) {
                this.AjaxLoadTable("ListCo", "showAjax");
            } else {
                this.AjaxLoadTable("ListWin", "showAjax");
            }
            // ���ǤΥ�å��������ѹ�����
            // this.blink_msg = "�����ֹ�";
            // this.stop_blink();
        }
        return false;   // �ºݤ�submit�Ϥ����ʤ�
    }
    /***** ConditionForm �γ�ǧ�ե饰 *****/
    working_hours_report.prototype.ConfirmValue = function (flg)
    {
        
        var index  = document.CorrectForm.ConfirmFlg.selectedIndex; 
        var ConFlg = document.CorrectForm.ConfirmFlgt.options[index].value; 
        
        alert('��������');
        return ConFlg;   // �ºݤ�submit�Ϥ����ʤ�
        return false;   // �ºݤ�submit�Ϥ����ʤ�
    }
    
    /***** ConfirmFlg�λ���Ajax�¹� *****/
    working_hours_report.prototype.ConfirmFlgexecute = function (obj, flg, section)
    {
        this.AjaxLoadTableConfirm("List", "showAjax", section);
        return false;   // �ºݤ�submit�Ϥ����ʤ�
    }
    
    /***** ���̹�����桼�����˰��´�̵��ɽ��������ConfirmFlg�λ���Ajax�ѥ���ɥ᥽�å� *****/
    working_hours_report.prototype.AjaxLoadTableConfirm = function (showMenu, location, section)
    {
        if (!location) location = "showAjax";   // Default�ͤ�����
        var parm = "?";
        parm += "showMenu=" + showMenu  // iframe�Τ����
        parm += "&targetSection=" + section
        parm += "&formal=details"
        parm += this.parameter;
        if (showMenu == "ListWin") {    // �̥�����ɥ���ɽ��
            this.win_open("working_hours_report_Main.php"+parm, 700, 350);
            return;
        }
        if (showMenu == "Correct") {    // �̥�����ɥ���ɽ��
            this.win_open("../working_hours_report_Main.php"+parm, 1000, 800);
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
        try {
            xmlhttp.open("GET", "working_hours_report_Main.php"+parm);
            xmlhttp.send(null);
        } catch (e) {
            alert(url + "\n\n�򥪡��ץ����ޤ���\n\n" + e);
        }
    }
    
    /***** ���̹�����桼�����˰��´�̵��ɽ��������Ajax�ѥ���ɥ᥽�å� *****/
    // onReadyStateChange���٥�Ȥ�Ȥäƽ�������λ���Ƥ��ʤ�����WaitMessage����ϡ�
    // parameter : ListTable=���ɽ��, WaitMsg=������Ǥ������Ԥ���������
    working_hours_report.prototype.AjaxLoadTable = function (showMenu, location)
    {
        if (!location) location = "showAjax";   // Default�ͤ�����
        var parm = "?";
        parm += "showMenu=" + showMenu  // iframe�Τ����
        parm += this.parameter;
        if (showMenu == "ListWin") {    // �̥�����ɥ���ɽ��
            this.win_open("working_hours_report_Main.php"+parm, 700, 350);
            return;
        }
        if (showMenu == "Correct") {    // �̥�����ɥ���ɽ��
            this.win_open("../working_hours_report_Main.php"+parm, 1000, 800);
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
        try {
            xmlhttp.open("GET", "working_hours_report_Main.php"+parm);
            xmlhttp.send(null);
        } catch (e) {
            alert(url + "\n\n�򥪡��ץ����ޤ���\n\n" + e);
        }
    }
    
    /***** ���ɽ���ΰ�Υ��ꥢ���᥽�å� *****/
    working_hours_report.prototype.viewClear = function ()
    {
        document.getElementById("showAjax").innerHTML = "";
        // ���ǤΥ�å������������֤��᤹
        // this.blink_msg = "�����ֹ�";
        // document.getElementById(this.blink_id_name).innerHTML = this.blink_msg;
    }
    
    /***** �᥽�åɼ����ˤ��WaitMessageɽ�� *****/
    working_hours_report.prototype.WaitMessage = function ()
    {
        var WaitMsg = "<br><table width='100%' border='0'><tr><td align='center' style='font-size:20pt; font-weight:bold;'>������Ǥ������Ԥ���������<br><img src='/img/tnk-turbine.gif' width='68' height='72'></td></tr></table>";
        document.getElementById("showAjax").innerHTML = WaitMsg;
    }
    
    return this;    // Object Return
    
}   /* class working_hours_report END  */


///// ���󥹥��󥹤�����
var WorkingHoursReport = new working_hours_report();

