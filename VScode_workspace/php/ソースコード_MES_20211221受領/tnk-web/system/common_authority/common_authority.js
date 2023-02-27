//////////////////////////////////////////////////////////////////////////////
// ���� ���� �ط��ơ��֥� ���ƥʥ�         MVC View��(JavaScript���饹) //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/07/26 Created   common_authority.js                                 //
// 2006/08/02 document.getElementById(location)�� IE�Τ�NULL���顼�ˤʤ����//
//            ���� try {} catch (e) {} ���ɲ�  NN7.1�Ǥ�OK                  //
// 2006/09/06 ����̾�ν�����ǽ�ɲä�ȼ�� edit/updateDivision  �ط����ɲ�    //
// 2006/12/05 win_open()�᥽�åɤ��ѿ�̾�Υߥ����� name �� winName        //
//////////////////////////////////////////////////////////////////////////////

///// �����Х��ѿ� _GDEBUG �ν���ͤ򥻥å�(��꡼������false�˥��åȤ���)
var _GDEBUG = false;

/****************************************************************************
/*          common_authority class base_class �γ�ĥ���饹�����            *
/****************************************************************************
class common_authority extends base_class
*/
///// �����ѡ����饹�ηѾ�
common_authority.prototype = new base_class();    // base_class �ηѾ�
///// Constructer �����
function common_authority()
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
    
    /************************************************************************
    *                           Public methods                              *
    ************************************************************************/
    /***** �ѥ�᡼�����ǻ��ꤵ�줿���֥������ȤΥ�����Ȥ˥ե������������� *****/
    common_authority.prototype.set_focus = function (obj, status)
    {
        if (obj) {
            obj.focus();
            if (status == "select") obj.select();
        }
    }
    
    /***** ����ɽ���᥽�å� *****/
    /***** blink_flg Private property �������0.5��������� *****/
    /***** <body onLoad='setInterval("obj.blink_disp(\"caption\")", 500)'> *****/
    common_authority.prototype.blink_disp = function (id_name)
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
    common_authority.prototype.stop_blink = function ()
    {
        document.getElementById(this.blink_id_name).innerHTML = this.blink_msg;
        clearInterval(this.intervalID);
    }
    
    /***** ���֥������Ȥ��ͤ���ʸ���Ѵ����� *****/
    common_authority.prototype.obj_upper = function (obj)
    {
        obj.value = obj.value.toUpperCase();
        return true;
    }
    
    /***** ������礭���Υ��֥�����ɥ��������ɽ������ *****/
    /***** Windows XP SP2 �Ǥϥ������ƥ��ηٹ𤬽Ф�  *****/
    common_authority.prototype.win_open = function (url, w, h, winName)
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
    common_authority.prototype.winActiveChk = function ()
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
    common_authority.prototype.win_show = function (url, w, h)
    {
        if (!w) w = 800;     // �����
        if (!h) h = 600;     // �����
        showModalDialog(url, 'show_win', "dialogWidth:" + w + "px;dialogHeight:" + h + "px");
    }
    
    /***** DOM ����Ѥ������٥����Ͽ�᥽�å� *****/
    common_authority.prototype.addListener = function (obj, eventType, func, cap)
    {
        if (obj.attachEvent) {                          // IE �ξ��
            obj.attachEvent("on" + eventType, func);
        } else if (obj.addEventListener) {              // IE �ʳ�
            obj.addEventListener(eventType, func, cap);
        } else {
            alert("�����ѤΥ֥饦�����ϡ����Υץ�����̤�б��Ǥ���");
            return false;
        }
    }
    
    /***** DOM ����Ѥ������٥������(ʣ����)�᥽�å� *****/
    common_authority.prototype.setEventListeners = function (eventType, ID)
    {
        var eSource = document.getElementById(ID);
        this.addListener(eSource, eventType, catchEventListener, false);  // catchEventListener()��ñ�ȴؿ�
        ///// �嵭�� eventType = "click" �� "Click" �Τ褦����ʸ������Ѥ��ƤϤ����ʤ�
    }
    
    /***** ���¥ޥ������κ�� ��ǧ �� �¹� �᥽�å� *****/
    common_authority.prototype.deleteDivision = function (division, div_name)
    {
        if (confirm(div_name + "\n\n�������ޤ����������Ǥ�����")) {
            // ���С���ɽ���򥯥ꥢ����
            this.viewClear("showAjax2");
            // Ajax�ѥѥ�᡼�����򥻥å�
            this.parameter  = "&targetDivision="   + division;
            // Ajax�̿�
            this.AjaxLoadTable("DeleteDivision", "ListDivision", "showAjax1");
        }
    }
    
    /***** ���¥ޥ������θ���̾�ν��� �� �¹� �᥽�å� *****/
    common_authority.prototype.editDivision = function (division, div_name)
    {
        // if (confirm(div_name + "\n\n�������ޤ����������Ǥ�����")) {
            // ���С���ɽ���򥯥ꥢ����
            this.viewClear("showAjax2");
            // Ajax�ѥѥ�᡼�����򥻥å�
            this.parameter  = "&targetDivision="   + division;
            // Ajax�̿�
            this.AjaxLoadTable("EditDivision", "ListDivision", "showAjax1");
        // }
    }
    
    /***** ���¥ޥ������θ���̾�ν�����Ͽ �� �¹� �᥽�å� *****/
    common_authority.prototype.updateDivision = function (division, div_name)
    {
        if (confirm(div_name + "\n\n����Ͽ���ޤ����������Ǥ�����")) {
            // ���С���ɽ���򥯥ꥢ����
            this.viewClear("showAjax2");
            // Ajax�ѥѥ�᡼�����򥻥å�
            this.parameter  = "&targetDivision="   + division;
            this.parameter += "&targetAuthName="   + div_name;
            // Ajax�̿�
            this.AjaxLoadTable("UpdateDivision", "ListDivision", "showAjax1");
        }
    }
    
    /***** ���¥��С��Υꥹ�Ȼؼ� �᥽�å� *****/
    common_authority.prototype.listID = function (division)
    {
        // Ajax�ѥѥ�᡼�����򥻥å�
        this.parameter  = "&targetDivision="   + division;
        // Ajax�̿�
        this.AjaxLoadTable("ListID", "ListID", "showAjax2");
    }
    
    /***** ���¥��С��κ�� ��ǧ �� �¹� �᥽�å� *****/
    common_authority.prototype.deleteID = function (id, division)
    {
        if (confirm(id + "\n\n�������ޤ����������Ǥ�����")) {
            // Ajax�ѥѥ�᡼�����򥻥å�
            this.parameter  = "&targetID="   + id;
            this.parameter  += "&targetDivision="   + division;
            // Ajax�̿�
            this.AjaxLoadTable("DeleteID", "ListID", "showAjax2");
        }
    }
    
    /***** ���̹�����桼�����˰��´�̵��ɽ��������Ajax�ѥ���ɥ᥽�å� *****/
    // onReadyStateChange���٥�Ȥ�Ȥäƽ�������λ���Ƥ��ʤ�����WaitMessage����ϡ�
    // parameter : ListTable=���ɽ��, WaitMsg=������Ǥ������Ԥ���������
    common_authority.prototype.AjaxLoadTable = function (Action, showMenu, location)
    {
        if (!location) location = "showAjax1";   // Default�ͤ�����
        var parm = "?";
        parm += "Action=" + Action;
        parm += "&showMenu=" + showMenu;
        parm += this.parameter;
        if (showMenu == "ListWin") {    // �̥�����ɥ���ɽ��
            this.win_open("common_authority_Main.php"+parm, 700, 350);
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
                try {
                document.getElementById(location).innerHTML = xmlhttp.responseText;
                } catch (e) {}
                if (location == "showCateList") {
                    CommonAuthority.setEventListeners("change", "targetCategory");
                    CommonAuthority.setEventListeners("focus", "targetCategory");
                    document.getElementById("targetCategory").focus();
                }
            } else {
                // onReadyStateChange���٥�Ȥ�Ȥäƽ�������λ���Ƥ��ʤ�����WaitMessage����ϡ�
                if (location != "showCateList" && location != "showIDName") {
                    try {
                    document.getElementById(location).innerHTML = "<br><table width='100%' border='0'><tr><td align='center' style='font-size:20pt; font-weight:bold;'>������Ǥ������Ԥ���������<br><img src='/img/tnk-turbine.gif' width='68' height='72'></td></tr></table>";
                    } catch (e) {}
                }
            }
        }
        var url = "common_authority_Main.php" + parm;
        try {
            xmlhttp.open("GET", url);
            xmlhttp.send(null);
        } catch (e) {
            alert(url + "\n\n�򥪡��ץ����ޤ���\n\n" + e);
        }
    }
    
    /***** URL���� Ajax�ѥ��ɥ᥽�å� *****/
    common_authority.prototype.AjaxLoadUrl = function (url, location)
    {
        if (!location) location = "showAjax1";   // Default�ͤ�����
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
                document.getElementById(location).innerHTML = xmlhttp.responseText;
            } else {
                // onReadyStateChange���٥�Ȥ�Ȥäƽ�������λ���Ƥ��ʤ�����WaitMessage����ϡ�
                document.getElementById(location).innerHTML = "<br><table width='100%' border='0'><tr><td align='center' style='font-size:20pt; font-weight:bold;'>������Ǥ������Ԥ���������<br><img src='/img/tnk-turbine.gif' width='68' height='72'></td></tr></table>";
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
    common_authority.prototype.viewClear = function (showAjax)
    {
        document.getElementById(showAjax).innerHTML = "";
        // ���ǤΥ�å������������֤��᤹
        // this.blink_msg = "�����ֹ�";
        // document.getElementById(this.blink_id_name).innerHTML = this.blink_msg;
    }
    
    /***** �᥽�åɼ����ˤ��WaitMessageɽ�� *****/
    common_authority.prototype.WaitMessage = function ()
    {
        var WaitMsg = "<br><table width='100%' border='0'><tr><td align='center' style='font-size:20pt; font-weight:bold;'>������Ǥ������Ԥ���������<br><img src='/img/tnk-turbine.gif' width='68' height='72'></td></tr></table>";
        document.getElementById("showAjax").innerHTML = WaitMsg;
    }
    
    return this;    // Object Return
    
}   /* class common_authority END  */


///// ���󥹥��󥹤�����
var CommonAuthority = new common_authority();


function catchEventListener(evtObj)
{
    // �֥饦������Ƚ��
    if (evtObj.srcElement) {        // IE
        var id = evtObj.srcElement.id;
    } else if (evtObj.target) {     // IE �ʳ�
        var id = evtObj.target.id;
    } else {
        alert("�����ѤΥ֥饦������̤�б��Ǥ���");
        return false;
    }
    // id �ˤ������ο�ʬ
    switch (id) {
    case "addDivisionForm":
    case "addDivision":
        // ���Ϲ��ܤΥ����å�
        var targetAuthName = document.getElementById("targetAuthName");
        if (!targetAuthName.value) {
            alert("����̾�����Ϥ���Ƥ��ޤ�������ʸ��ޤ�����Ϥ��Ʋ�������");
            targetAuthName.focus();
            targetAuthName.select();
            return false;
        }
        // Ajax�ѥѥ�᡼�����򥻥å�
        CommonAuthority.parameter  = "&targetAuthName="   + targetAuthName.value;
        // Ajax�̿�
        // var url = "common_authority_Main.php?Action=AddDivision&showMenu=ListDivision&targetAuthName=" + targetAuthName.value;
        // CommonAuthority.AjaxLoadUrl(url, "showAjax1");
        CommonAuthority.AjaxLoadTable("AddDivision", "ListDivision", "showAjax1");
        return false;
    case "addIDForm":
    case "addID":
        // ���Ϲ��ܤΥ����å�
        var targetID = document.getElementById("targetID");
        if (!targetID.value) {
            alert("���С������Ϥ���Ƥ��ޤ���\n\n�Ұ��ֹ桦IP���ɥ쥹��MAC���ɥ쥹������¾�������Ϥ��Ʋ�������");
            targetID.focus();
            targetID.select();
            return false;
        }
        var targetCategory = document.getElementById("targetCategory")[document.getElementById("targetCategory").selectedIndex];
        if (!targetCategory.value) {
            alert("���ब���򤵤�Ƥ��ޤ���\n\n�Ұ��ֹ桦IP���ɥ쥹��MAC���ɥ쥹������¾�����򤷤Ʋ�������");
            document.getElementById("targetCategory").focus();
            return false;
        }
        // Ajax�ѥѥ�᡼�����򥻥å�
        CommonAuthority.parameter  = "&targetID="   + targetID.value;
        CommonAuthority.parameter  += "&targetDivision="   + document.getElementById("targetDivision").value;
        CommonAuthority.parameter  += "&targetCategory="   + targetCategory.value;
        // Ajax�̿�
        CommonAuthority.AjaxLoadTable("AddID", "ListID", "showAjax2");
        return false;
    case "targetID":
        // ���Ϲ��ܤΥ����å�
        if (!document.getElementById("targetID").value) {
            return false;
        }
        // Ajax�ѥѥ�᡼�����򥻥å�
        CommonAuthority.parameter  = "&targetID="   + document.getElementById("targetID").value;
        // Ajax�̿�
        CommonAuthority.AjaxLoadTable("ConfirmID", "ListCategory", "showCateList");
        return false;
    case "targetCategory":
        // ���Ϲ��ܤΥ����å�
        if (!document.getElementById("targetID").value) {
            return false;
        }
        // Ajax�ѥѥ�᡼�����򥻥å�
        CommonAuthority.parameter  = "&targetID="   + document.getElementById("targetID").value;
        CommonAuthority.parameter  += "&targetCategory="   + document.getElementById("targetCategory")[document.getElementById("targetCategory").selectedIndex].value;
        // Ajax�̿�
        CommonAuthority.AjaxLoadTable("ConfirmID", "GetIDName", "showIDName");
        return false;
    default:
        alert("�֥饦������NN�ξ�硢ͽ�۳��Υ��٥�Ȥ�ȯ�����ޤ���");
        return false;
    }
}

