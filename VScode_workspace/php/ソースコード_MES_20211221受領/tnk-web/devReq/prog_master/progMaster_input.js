//////////////////////////////////////////////////////////////////////////////
// �ץ������� �ץ�������Ͽ MVC View �� (JavaScript���饹)           //
// Copyright (C) 2010 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2010/01/26 Created    progMaster_input.js                                //
// 2010/03/05 �ǥ��쥯�ȥ�����ϥ����å����ɲ�                              //
//////////////////////////////////////////////////////////////////////////////

/****************************************************************************
/*              progMaster_input class �ƥ�ץ졼�Ȥγ�ĥ���饹�����       *
/****************************************************************************
class progMaster_input extends base_class
{   */
    ///// �����ѡ����饹�ηѾ�
    progMaster_input.prototype = new base_class();   // base_class �ηѾ�
    ///// �����Х��ѿ� _GDEBUG �ν���ͤ򥻥å�(��꡼������false�˥��åȤ���)
    var _GDEBUG = false;
    
    ///// Constructer �����
    function progMaster_input()
    {
        /***********************************************************************
        *                           Private properties                         *
        ***********************************************************************/
        // this.properties = false;                         // �ץ�ѥƥ����ν����
        this.Gid = false;                               // setTimeout()������� clearTimeout()�ǻ��Ѥ���
        this.GpidKey;                                 // HTML���document.ControlForm.pidKey.value�ǽ����
        // this.incrementalSearch = false;     // ���󥯥��󥿥륵�����μ¹ԥե饰    ���٥�Ȥ���ƽФ����ؿ���Ǥϻ��ѤǤ��ʤ�����
        //this.UpperSwitch;                   // ��ư��ʸ���Ѵ������оݤ򥹥��å�����  �����Х��ѿ����ѹ�
        
        /************************************************************************
        *                           Public methods                              *
        ************************************************************************/
        progMaster_input.prototype.blink_disp     = blink_disp;       // ����ɽ���᥽�å�
        progMaster_input.prototype.setFocus       = setFocus;         // ����ե�����������
        progMaster_input.prototype.win_open       = win_open;         // ���֥�����ɥ��������ɽ��
        progMaster_input.prototype.winActiveChk   = winActiveChk;     // ���֥�����ɥ���Active�����å�
        progMaster_input.prototype.win_show       = win_show;         // �⡼�������������ɽ��(IE����)
        progMaster_input.prototype.evt_key_chk    = evt_key_chk;      // ���٥�ȥ���(�����С��饤��)��ư��ʸ���Ѵ���ǽ�ȵ������󥯥���ȥ�������ǽ���ɲ�
        progMaster_input.prototype.incExecChk     = incExecChk;       // ���󥯥���ȥ������¹���
        progMaster_input.prototype.CheckItemMaster= CheckItemMaster;  // �Խ��ե���������ϥ����å���
        
        return this;    // Object Return
    }
    
    /***** ����ɽ����HTML�ɥ������ *****/
    /***** blink_flg �ϥ����Х��ѿ������ �������0.5��������� *****/
    /***** <body onLoad='setInterval("templ.blink_disp(\"caption\")", 500)'> *****/
    function blink_disp(id_name)
    {
        if (blink_flag == 1) {
            document.getElementById(id_name).innerHTML = "";
            blink_flag = 2;
        } else {
            document.getElementById(id_name).innerHTML = "����ץ�ǥ����ƥ�ޥ�������ɽ�����Ƥ��ޤ�";
            blink_flag = 1;
        }
    }
    
    /***** �ѥ�᡼�����ǻ��ꤵ�줿���֥������ȤΥ�����Ȥ˥ե������������� *****/
    function setFocus(obj)
    {
        if (obj) {
            obj.focus();
        }
        // document.body.focus();   // F2/F12������ͭ���������б�
        // document.mhForm.backwardStack.focus();  // �嵭��IE�ΤߤΤ��ᡢ��������ѹ���NN�б�
        // document.form_name.element_name.focus();      // ������ϥե����ब������ϥ����Ȥ򳰤�
        // document.form_name.element_name.select();
    }
    
    /***** ������礭���Υ��֥�����ɥ��������ɽ������ *****/
    /***** Windows XP SP2 �Ǥϥ������ƥ��ηٹ𤬽Ф�  *****/
    function win_open(url, w, h) {
        if (!w) w = 800;     // �����
        if (!h) h = 600;     // �����
        var left = (screen.availWidth  - w) / 2;
        var top  = (screen.availHeight - h) / 2;
        w -= 10; h -= 30;   // ��Ĵ����ɬ��
        window.open(url, 'view_win', 'width='+w+',height='+h+',scrollbars=yes,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
    }
    
    /***** ���֥�����ɥ�¦��Window��Active�����å���Ԥ� *****/
    /***** <body onLoad="setInterval('templ.winActiveChk()',100)">*****/
    function winActiveChk() {
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
    function win_show(url, w, h) {
        if (!w) w = 800;     // �����
        if (!h) h = 600;     // �����
        showModalDialog(url, 'show_win', "dialogWidth:" + w + "px;dialogHeight:" + h + "px");
    }
    
    /***** ���̥��������� base_class �Υ᥽�åɤ򥪡��С��饤�� *****/
    /***** 1.���ܥ����� F12=123, F2=113 �ɤ���Ǥ�Ȥ���褦��  *****/
    /***** �����ֹ�μ�ư��ʸ���Ѵ���ǽ�ȵ������󥯥���ȥ�������ǽ���ɲ� *****/
    function evt_key_chk(evt)
    {
        // �����Х��ѿ��� backward_obj �������Υ���ȥ��륪�֥�������
        var browser = navigator.appName;
        if (browser.charAt(0) == 'M') {         // IE�ξ��
            var chk_key = event.keyCode;        // IE�Ǥϥ��������ɤ�Ĵ�٤�ˤ� event.keyCode ��Ȥ���
        } else {                                // NN�ξ�������
            var chk_key = evt.which;            // NN�Ǥ� evt.which ��Ȥ���(evt�ϥ��٥�Ȥˤ�äƸƤӽФ����ؿ��Υ��å��������륪�֥��������ѿ�̾)
            if (chk_key == 13) {                    // NN��<input>��ʣ��������˲��Ԥ�submit���ʤ�������б�
                var work = evt.target + "";             // ʸ�����Ѵ�
                /***** debug 
                alert(work);
                *****/
                if (work.match("Input") == "Input") {   // target��Input������Ȥλ�����submit����
                    window.document.ControlForm.submit();
                }
            }
        }
        switch (chk_key) {
        case 113:   // F2
        case 123:   // F12
            backward_obj.submit();
            return true;
        case 112:   // F1   �� �����̵���ˤ���ˤ�(onHelp='return false')IE�Τ�
        case 114:   // F3   ����
        case 116:   // F5   �����ܥ���
        case 117:   // F6   google
            if (browser.charAt(0) == 'M') {         // IE�ξ��
                event.keyCode = null;
            } else {                                // NN�ξ�������
                evt.which = null;
            }
            return false;
        default:
            ///// �ʲ���G_UpperSwitch��G_incrementalSearch�ϥץ�ѥƥ��Ǥ�ư��ʤ����ᥰ���Х��ѿ����ѹ�
            if (!G_incrementalSearch) return;    // ���󥯥��󥿥륵�����μ¹�Ƚ��
            if (this.Gid) {
                clearTimeout(this.Gid);
                this.Gid = false;
            }
            this.Gid = setTimeout("this.incExecChk()", 200);     // ���Ϥ��٤��ͤ�500���餤?
        }
    }
    
    /***** �������󥯥���ȥ������Τ���μ¹ԥ᥽�åɤ��ɲ� *****/
    /***** GpidKey ��HTML���document.ControlForm.pidKey.value�ǽ���� *****/
    function incExecChk()
    {
        if (document.ControlForm.pidKey.value != this.GpidKey) {
            /*****
            document.ControlForm.submit();  // SUBMIT ��
            *****/
            this.GpidKey = document.ControlForm.pidKey.value;
            var parm = "?";
            parm += "pidKey="             + document.ControlForm.pidKey.value;
            parm += "&current_menu=table"   // table�Τ����
            parm += "&CTM_selectPage="      + document.ControlForm.CTM_selectPage.value;
            parm += "&CTM_prePage="         + document.ControlForm.CTM_prePage.value;
            parm += "&CTM_pageRec="         + document.ControlForm.CTM_pageRec.value;
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
                }
            }
            try {
                xmlhttp.open("GET", "progMaster_input_Main.php"+parm);
                xmlhttp.send(null);
            } catch (e) {
                alert(url + "\n\n�򥪡��ץ����ޤ���\n\n" + e);
            }
        }
    }
    
    function CheckItemMaster(obj) {
        if (obj.pid.value.length == 0) {
            alert("�ץ����ID���֥�󥯤Ǥ���");
            obj.pid.focus();
            obj.pid.select();
            return false;
        }
        if (obj.pdir.value.length == 0) {
            alert("�ǥ��쥯�ȥ꤬�֥�󥯤Ǥ���");
            obj.pdir.focus();
            obj.pdir.select();
            return false;
        }
        return true;
    }
/*
}   // class progMaster_input END  */


///// ���󥹥��󥹤�����
var ProgMaster = new progMaster_input();
// blink_disp()�᥽�å���ǻ��Ѥ��륰���Х��ѿ��Υ��å�
var blink_flag = 1;


