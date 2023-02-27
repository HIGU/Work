//////////////////////////////////////////////////////////////////////////////
// ���Ҷ�ͭ ��Ŭ������ξȲ񡦥��ƥʥ�                                //
//                                           MVC View �� (JavaScript���饹) //
// Copyright (C) 2008 Norihisa.Ohya usoumu@nitto-kohki.co.jp                //
// Changed history                                                          //
// 2008/05/30 Created    unfit_report.js                                    //
// 2008/08/29 masterst���ܲ�ư����                                          //
//////////////////////////////////////////////////////////////////////////////

/****************************************************************************
/*     unfit_report class �ƥ�ץ졼�Ȥγ�ĥ���饹�����           *
/****************************************************************************
class unfit_report extends base_class
{   */
    ///// �����ѡ����饹�ηѾ�
    unfit_report.prototype = new base_class();   // base_class �ηѾ�
    ///// �����Х��ѿ� _GDEBUG �ν���ͤ򥻥å�(��꡼������false�˥��åȤ���)
    var _GDEBUG = false;
    
    ///// Constructer �����
    function unfit_report()
    {
        /***********************************************************************
        *                           Private properties                         *
        ***********************************************************************/
        // this.properties = false;                         // �ץ�ѥƥ����ν����
        
        /************************************************************************
        *                           Public methods                              *
        ************************************************************************/
        unfit_report.prototype.set_focus        = set_focus;        // ��������ϥ�����Ȥ˥ե�������
        unfit_report.prototype.blink_disp       = blink_disp;       // ����ɽ���᥽�å�
        unfit_report.prototype.obj_upper        = obj_upper;        // ���֥������ͤ���ʸ���Ѵ�
        unfit_report.prototype.win_open         = win_open;         // ���֥�����ɥ��������ɽ��
        unfit_report.prototype.winActiveChk     = winActiveChk;     // ���֥�����ɥ���Active�����å�
        unfit_report.prototype.win_show         = win_show;         // �⡼�������������ɽ��(IE����)
        unfit_report.prototype.sponsorNameCopy  = sponsorNameCopy;  // �����Ԥ�̾���Υ��ԡ��᥽�å�
        unfit_report.prototype.attenCopy        = attenCopy;        // ������texterea�ؤ�ɽ���᥽�å�
        unfit_report.prototype.attenCopy2       = attenCopy2;       // ������texterea�ؤ�ɽ���᥽�å� ���롼����Ͽ��
        unfit_report.prototype.apend_formCheck  = apend_formCheck;  // apend_form �����ϥ����å��᥽�å�
        unfit_report.prototype.follow_formCheck = follow_formCheck; // follow_form �����ϥ����å��᥽�å�
        unfit_report.prototype.group_formCheck  = group_formCheck;  // group_form �����ϥ����å��᥽�å�
        unfit_report.prototype.groupMemberCopy  = groupMemberCopy;  // ����襰�롼�פ�atten[]�ؤΥ��ԡ��᥽�å�(������attenCopy��ƽФ�)
        unfit_report.prototype.ControlFormSubmit= ControlFormSubmit;// ControlForm �Υ��֥ߥåȥ᥽�å�
        unfit_report.prototype.addFavoriteIcon  = addFavoriteIcon;  // ����������˥���������ɲä���
        
        return this;    // Object Return
    }
    
    /***** �ѥ�᡼�����ǻ��ꤵ�줿���֥������ȤΥ�����Ȥ˥ե������������� *****/
    function set_focus(obj, status)
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
    
    /***** ���֥������Ȥ��ͤ���ʸ���Ѵ����� *****/
    function obj_upper(obj) {
        obj.value = obj.value.toUpperCase();
        return true;
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
    
    /***** �����Ԥ�̾���Υ��ԡ��᥽�å� *****/
    function sponsorNameCopy()
    {
        // �ե�ǥ��֥������Ȥλ���
        document.apend_form.sponsor.value = document.apend_form.userID_name.value;
    }
    
    /***** �����Ԥ�̾���Υ��ԡ��᥽�å� *****/
    function sponsorNameCopyFollow()
    {
        // �ե�ǥ��֥������Ȥλ���
        document.follow_form.sponsor.value = document.follow_form.userID_name.value;
    }
    
    /***** attenView ��ɽ���ѥ��ԡ��᥽�å� *****/
    function attenCopy(obj)
    {
        document.apend_form.attenView.value = "";
        for (var i=0; i<obj.options.length; i++) {
            if (obj.options[i].selected) {
                if (document.apend_form.attenView.value == "") {
                    document.apend_form.attenView.value += obj.options[i].text;
                } else {
                    document.apend_form.attenView.value += (", " + obj.options[i].text);
                }
            }
        }
        /*****  �������̾��ޤ����ϰʲ����ͤˤ���
        for (var i=0; i<obj.elements['atten[]'].options.length; i++) {
            if (obj.elements['atten[]'].options[i].selected) {
                obj.attenView.value += obj.elements['atten[]'].options[i].text;
            }
        }
        *****/
    }
    
    /***** attenView ��ɽ���ѥ��ԡ��᥽�å� �ե������å���*****/
    function attenCopyFollow(obj)
    {
        document.follow_form.attenView.value = "";
        for (var i=0; i<obj.options.length; i++) {
            if (obj.options[i].selected) {
                if (document.follow_form.attenView.value == "") {
                    document.follow_form.attenView.value += obj.options[i].text;
                } else {
                    document.follow_form.attenView.value += (", " + obj.options[i].text);
                }
            }
        }
        /*****  �������̾��ޤ����ϰʲ����ͤˤ���
        for (var i=0; i<obj.elements['atten[]'].options.length; i++) {
            if (obj.elements['atten[]'].options[i].selected) {
                obj.attenView.value += obj.elements['atten[]'].options[i].text;
            }
        }
        *****/
    }
    
    /***** attenView ��ɽ���ѥ��ԡ��᥽�å� ���롼���Խ��� *****/
    function attenCopy2(obj, obj2)
    {
        obj2.value = "";
        for (var i=0; i<obj.options.length; i++) {
            if (obj.options[i].selected) {
                if (obj2.value == "") {
                    obj2.value += obj.options[i].text;
                } else {
                    obj2.value += (", " + obj.options[i].text);
                }
            }
        }
    }
    
    /***** apend_form �����ϥ����å��᥽�å� *****/
    function apend_formCheck(obj) {
        if (obj.subject.value.length == 0) {
            alert("��Ŭ�����Ƥ����Ϥ���Ƥ��ޤ���");
            obj.subject.focus();
            // obj.subject.select();
            return false;
        }
        if (obj.place.value.length == 0) {
            alert("ȯ����꤬���Ϥ���Ƥ��ޤ���");
            obj.place.focus();
            // obj.place.select();
            return false;
        }
        if (obj.section.value.length == 0) {
            alert("��Ǥ���礬���Ϥ���Ƥ��ޤ���");
            obj.section.focus();
            // obj.section.select();
            return false;
        }
        if (obj.assy_no.value.length == 0) {
            if (obj.parts_no.value.length == 0) {
                alert("�����ֹ椫�����ֹ�Τɤ��餫��ɬ�����Ϥ��Ʋ�������");
                obj.assy_no.focus();
                // obj.parts_no.select();
                return false;
            }
        }
        if (obj.occur_cause.value.length == 0) {
            alert("ȯ�����������Ϥ���Ƥ��ޤ���(�����ξ��ϡ�Ĵ�����)");
            obj.occur_cause.focus();
            // obj.occur_cause.select();
            return false;
        }
        if (obj.occur_cause.value.length == 0) {
            alert("ή�Ф����Ϥ���Ƥ��ޤ���(�����ξ��ϡ�Ĵ����١�ή�Фʤ��ξ��ϡ�ή��̵����)");
            obj.occur_cause.focus();
            // obj.occur_cause.select();
            return false;
        }
        if (obj.unfit_num.value.length == 0) {
            alert("��Ŭ����̤����Ϥ���Ƥ��ޤ���(Ĵ����ξ��ϣ�)");
            obj.unfit_num.focus();
            // obj.unfit_num.select();
            return false;
        }
        if (obj.issue_num.value.length == 0) {
            alert("ή�п��̤����Ϥ���Ƥ��ޤ���(Ĵ���桦ή�Фʤ��ξ��ϣ�)");
            obj.issue_num.focus();
            // obj.issue_num.select();
            return false;
        }
        if (obj.subject.value.match(/��/)) {
            // /m(���ץ����)��ʣ���ԤΥޥå��󥰤�Ԥ����嵭�Ǥ�OK
            alert("��̾�˵����¸ʸ���� �� (1ʸ���Υ���)�����äƤ��ޤ���\n\n(��) (Ⱦ�ѥ��å��ȥ���)�����ִ����Ʋ�������");
            obj.subject.focus();
            return false;
        }
        if (obj.subject.value.match(/[��-��]/)) {
            alert("��̾�˵����¸ʸ���� �� �������äƤ��ޤ���\n\n�ʣ��� (Ⱦ���������ѥ��å��ȣ�)�����ִ����Ʋ�������");
            obj.subject.focus();
            return false;
        }
        if (obj.sponsor.value.length == 0) {
            alert("�����Ԥ����ꤵ��Ƥ��ޤ���");
            obj.userID_name.focus();
            // obj.userID_name.select();
            return false;
        }
        if (obj.attenView.value.length == 0) {
            alert("����褬���ꤵ��Ƥ��ޤ���");
            obj.elements['atten[]'].focus();
            return false;
        }
        if (obj.measure[0].checked) {
            if (obj.receipt_no.value.length == 0) {
                alert("�����λ����ݤϼ���No.�����Ϥ��Ƥ���������");
                obj.receipt_no.focus();
                return false;
            }
        }
        return true;
    }
    /***** follow_form �����ϥ����å��᥽�å� *****/
    function follow_formCheck(obj) {
        if (obj.sponsor.value.length == 0) {
            alert("�����Ԥ����ꤵ��Ƥ��ޤ���");
            obj.userID_name.focus();
            // obj.userID_name.select();
            return false;
        }
        if (obj.attenView.value.length == 0) {
            alert("����褬���ꤵ��Ƥ��ޤ���");
            obj.elements['atten[]'].focus();
            return false;
        }
        return true;
    }
    /***** group_form �����ϥ����å��᥽�å�(���롼���ֹ�, ���롼��̾, ���ʼ�, �Ŀ�/��ͭ��) *****/
    function group_formCheck(obj) {
        // obj.group_no2.value = obj.group_no2.value.toUpperCase();
        if (obj.group_no2.value.length == 0) {
            alert("���롼���ֹ椬�֥�󥯤Ǥ���");
            obj.group_no2.focus();
            obj.group_no2.select();
            return false;
        }
        if (!this.isDigit(obj.group_no2.value)) {
            alert("���롼���ֹ�Ͽ��������Ϥ��Ʋ�������");
            obj.group_no2.focus();
            obj.group_no2.select();
            return false;
        }
        if (obj.group_no2.value < 1 || obj.group_no2.value > 999) {
            alert("���롼���ֹ�ϣ����飹�����ޤǤǤ���");
            obj.group_no2.focus();
            obj.group_no2.select();
            return false;
        }
        if (obj.group_name.value.length == 0) {
            alert("���롼��̾���֥�󥯤Ǥ���");
            obj.group_name.focus();
            obj.group_name.select();
            return false;
        }
        if (obj.attenView.value.length == 0) {
            alert("����褬���ꤵ��Ƥ��ޤ���");
            obj.elements['atten[]'].focus();
            return false;
        }
        if (!obj.owner[0].checked && !obj.owner[1].checked) {
            alert("�Ŀ��� �� ��ͭ�� �Τɤ��餫������å����Ʋ�������");
            obj.owner[0].focus();
            return false;
        }
        return true;
    }
    
    /***** group_name�����򥪥ץ����� atten[] �إ��ԡ��� attenView��ɽ�������� *****/
    /***** �����Х��ѿ��� Ggroup_member ����Ѥ��� *****/
    function groupMemberCopy(groupObj, attenObj)
    {
        // �����
        for (var r=0; r<attenObj.options.length; r++) {
            attenObj.options[r].selected = false;
        }
        // ���ԡ�
        for (var i=1; i<groupObj.options.length; i++) {
            if (groupObj.options[i].selected) {
                for (var j=0; j<Ggroup_member[i-1].length; j++) {
                    for (var k=0; k<attenObj.options.length; k++) {
                        if (attenObj.options[k].value == Ggroup_member[i-1][j]) {
                            attenObj.options[k].selected = true;
                        } else {
                            // attenObj.options[k].selected = false;
                        }
                    }
                }
            }
        }
        // attenView �إ��ԡ�
        this.attenCopy(attenObj);
    }
    
    /***** ControlForm �� Submit �᥽�å� ��������к� *****/
    function ControlFormSubmit(radioObj, formObj)
    {
        radioObj.checked = true;
        formObj.submit();
        return false;       // �����줬���Submit���к�
    }
    
    /***** ����������˥���������ɲä��� ��Ū�ϥǥ����ȥåפ˥��������Ž���դ���� *****/
    function addFavoriteIcon(url, uid)
    {
        if (!confirm("����������˥���������ɲä��ޤ���\n\n�������Ǥ�����")) return false;
        if (document.all && !window.opera) {
            if (uid >= 100 && uid <= 999999) {
                window.external.AddFavorite(url + "?calUid=" + uid, "��Ŭ������ �Ȳ񡦺���");
            } else {
                window.external.AddFavorite(url, "��Ŭ������ �Ȳ񡦺���");
            }
        }
        return false;       // ���������� �¹Ԥ��к�
    }
    
    /***** ����̾�����Τ��������ֹ��Submit���� *****/
    function PartsNoSubmit(no)
    {
        var parts_no = no;
        if (parts_no.value.length == 9) {
    	    document.apend_form.action="unfit_report_Main.php";
            document.ControlForm.showMenu.value = "Apend";
            document.apend_form.partsflg.value = "TRUE";
            document.apend_form.submit();
        }
    }
    /***** ����̾�����Τ��������ֹ��Submit���� *****/
    function AssyNoSubmit(no)
    {
        var assy_no = no;
        if (assy_no.value.length == 9) {
    	    document.apend_form.action="unfit_report_Main.php";
            document.ControlForm.showMenu.value = "Apend";
            document.apend_form.assyflg.value = "TRUE";
            document.apend_form.submit();
        }
    }
    function chkCode(id) {
      work='';
      for (lp=0;lp<id.value.length;lp++) {
        unicode=id.value.charCodeAt(lp);
        if ((0xff0f<unicode) && (unicode<0xff1a)) {
          work+=String.fromCharCode(unicode-0xfee0);
        } else if ((0xff20<unicode) && (unicode<0xff3b)) {
          work+=String.fromCharCode(unicode-0xfee0);
        } else if ((0xff40<unicode) && (unicode<0xff5b)) {
          work+=String.fromCharCode(unicode-0xfee0);
        } else {
          work+=String.fromCharCode(unicode);
        }
      }
      id.value=work; /* Ⱦ�ѽ����Τ� */
      //id.value=work.toUpperCase(); /* ��ʸ�������줹����˻��� */
      //id.value=work.toLowerCase(); /* ��ʸ�������줹����˻��� */
    }
    function limitChars(target,maxlength,maxrow) {
        Str = target.value;
        lines = Str.split("\n").length;
        StrAry = Str.split("\n");
        if( lines > maxrow ){
            alert(maxrow+"�԰�������Ϥ��Ʋ�������");
            target.value = Str.replace(/(.|\r\n|\r|\n)$/,"");
        }

        if ( target.value.length > maxlength ) {
        alert(maxlength + "����������Ϥ��Ƥ�������");
        target.value = target.value.substr(0,maxlength);
        }
        target.focus();
    }

/*
}   // class unfit_report END  */


///// ���󥹥��󥹤�����
var UnfitReport = new unfit_report();
// blink_disp()�᥽�å���ǻ��Ѥ��륰���Х��ѿ��Υ��å�
var blink_flag = 1;


