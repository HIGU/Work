//////////////////////////////////////////////////////////////////////////////
// ���Ҷ�ͭ �ǹ礻(���)�������塼��ɽ�ξȲ񡦥��ƥʥ�                  //
//                                           MVC View �� (JavaScript���饹) //
// Copyright (C) 2005-2020 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/11/02 Created    meeting_schedule.js                                //
// 2005/11/21 groupMemberCopy()�᥽�åɤ��ɲ�                               //
// 2005/11/22 ControlFormSubmit()�᥽�å� ���Submit�к����ɲ�              //
// 2005/11/24 addFavoriteIcon(url,uid)����������˥��������ɲå᥽�åɤ��ɲ�//
// 2006/01/25 apend_formCheck()�᥽�åɤ˵����¸ʸ���Υ����å����ɲ�       //
// 2006/04/17 �������ε����¸ʸ���б�                                      //
// 2009/12/17 �Ȳ񡦰��������ϥ����å�print_formCheck���ɲ�            ��ë //
// 2019/03/15 �Ժ߼Ԥ򥦥���ɥ���ɽ������١��ɲ�                     ��ë //
// 2020/09/11 ��ãȯ�������Ȳ񥦥���ɥ�ɽ�����ɲ�                     ��ë //
// 2020/11/27 ���������Ȳ񥦥���ɥ�ɽ�����ɲ�                         ��ë //
// 2021/11/17 ����Ͼ�ǧ�Ԥ������ɽ����Ϣ���ɲ�                       ���� //
//////////////////////////////////////////////////////////////////////////////

/****************************************************************************
/*     meeting_schedule class �ƥ�ץ졼�Ȥγ�ĥ���饹�����           *
/****************************************************************************
class meeting_schedule extends base_class
{   */
    ///// �����ѡ����饹�ηѾ�
    meeting_schedule.prototype = new base_class();   // base_class �ηѾ�
    ///// �����Х��ѿ� _GDEBUG �ν���ͤ򥻥å�(��꡼������false�˥��åȤ���)
    var _GDEBUG = false;
    
    ///// Constructer �����
    function meeting_schedule()
    {
        /***********************************************************************
        *                           Private properties                         *
        ***********************************************************************/
        // this.properties = false;                         // �ץ�ѥƥ����ν����
        
        /************************************************************************
        *                           Public methods                              *
        ************************************************************************/
        meeting_schedule.prototype.set_focus        = set_focus;        // ��������ϥ�����Ȥ˥ե�������
        meeting_schedule.prototype.blink_disp       = blink_disp;       // ����ɽ���᥽�å�
        meeting_schedule.prototype.obj_upper        = obj_upper;        // ���֥������ͤ���ʸ���Ѵ�
        meeting_schedule.prototype.win_open         = win_open;         // ���֥�����ɥ��������ɽ��
        meeting_schedule.prototype.win_open2        = win_open2;        // ���֥�����ɥ��������ɽ��2
        meeting_schedule.prototype.setAdmitCnt      = setAdmitCnt;      // ����Ͼ�ǧ�Ԥ����
        meeting_schedule.prototype.winActiveChk     = winActiveChk;     // ���֥�����ɥ���Active�����å�
        meeting_schedule.prototype.win_show         = win_show;         // �⡼�������������ɽ��(IE����)
        meeting_schedule.prototype.strTimeCopy      = strTimeCopy;      // ���ϻ��֤Υ��ԡ��᥽�å�
        meeting_schedule.prototype.endTimeCopy      = endTimeCopy;      // ��λ���֤Υ��ԡ��᥽�å�
        meeting_schedule.prototype.sponsorNameCopy  = sponsorNameCopy;  // ��żԤ�̾���Υ��ԡ��᥽�å�
        meeting_schedule.prototype.attenCopy        = attenCopy;        // ���üԤ�texterea�ؤ�ɽ���᥽�å�
        meeting_schedule.prototype.attenCopy2       = attenCopy2;       // ���üԤ�texterea�ؤ�ɽ���᥽�å� ���롼����Ͽ��
        meeting_schedule.prototype.roomCopy         = roomCopy;         // ��ľ��Υ��ԡ��᥽�å�
        meeting_schedule.prototype.carCopy          = carCopy;          // ���Ѽ֤Υ��ԡ��᥽�å�
        meeting_schedule.prototype.apend_formCheck  = apend_formCheck;  // apend_form �����ϥ����å��᥽�å�
        meeting_schedule.prototype.room_formCheck   = room_formCheck;   // room_form �����ϥ����å��᥽�å�
        meeting_schedule.prototype.car_formCheck    = car_formCheck;    // car_form �����ϥ����å��᥽�å�
        meeting_schedule.prototype.group_formCheck  = group_formCheck;  // group_form �����ϥ����å��᥽�å�
        meeting_schedule.prototype.print_formCheck  = print_formCheck;  // print_form �����ϥ����å��᥽�å�
        meeting_schedule.prototype.groupMemberCopy  = groupMemberCopy;  // ���üԥ��롼�פ�atten[]�ؤΥ��ԡ��᥽�å�(������attenCopy��ƽФ�)
        meeting_schedule.prototype.ControlFormSubmit= ControlFormSubmit;// ControlForm �Υ��֥ߥåȥ᥽�å�
        meeting_schedule.prototype.addFavoriteIcon  = addFavoriteIcon;  // ����������˥���������ɲä���
        meeting_schedule.prototype.checkANDexecute  = checkANDexecute;  // �Ժ߼ԤΥ�����ɥ�ɽ��
        meeting_schedule.prototype.AjaxLoadTable    = AjaxLoadTable;    // �Ժ߼ԤΥ�����ɥ�ɽ��2
        meeting_schedule.prototype.AjaxLoadPITable  = AjaxLoadPITable;  // PI���������Υ�����ɥ�ɽ��
        
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
    

    var subWinObj;     // ���֥�����ɥ����֥�������
    
    /***** ������礭���Υ��֥�����ɥ��������ɽ������ *****/
    /***** Windows XP SP2 �Ǥϥ������ƥ��ηٹ𤬽Ф�  *****/
    function win_open(url, w, h) {
        if (!w) w = 800;     // �����
        if (!h) h = 600;     // �����
        var left = (screen.availWidth  - w) / 2;
        var top  = (screen.availHeight - h) / 2;
        w -= 10; h -= 30;   // ��Ĵ����ɬ��

        if( (subWinObj) && (!subWinObj.closed) ){   // ���֥�����ɥ���������Ƥ��뤫��
            subWinObj.close();                      // ���֥�����ɥ����Ĥ���
        }

        subWinObj = window.open(url, 'view_win', 'width='+w+',height='+h+',resizable=yes,scrollbars=yes,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
//        subWinObj.blur();      // ���֥�����ɥ��˥ե������������ꤹ��
//        window.focus();        // �����̤���ե������������
//        window.blur();         // �����̤���ե�������������
//        subWinObj.focus();     // ���֥�����ɥ��˥ե������������ꤹ��
    }
    
    var subWinObj2;         // ���֥�����ɥ����֥�������
    var timerFlag = false;  // ���ֻ���ե饰
    /***** ������礭���Υ��֥�����ɥ��������ɽ������ *****/
    /***** Windows XP SP2 �Ǥϥ������ƥ��ηٹ𤬽Ф�  *****/
    function win_open2(url, w, h) {
        if (!w) w = 800;     // �����
        if (!h) h = 600;     // �����
        var left = (screen.availWidth  - w) / 2;
        var top  = (screen.availHeight - h) / 2;
        w -= 10; h -= 30;   // ��Ĵ����ɬ��
        
        if( (subWinObj2) && (!subWinObj2.closed) ){ // ���֥�����ɥ���������Ƥ��뤫��
            //subWinObj2.close();                     // ���֥�����ɥ����Ĥ���
        } else {
            var openFlag = true;   // ����͡�true ɽ����
            
            // ���ֻ��ꤢ��ʤ���֤�����å�����
            if( timerFlag ) {
                var now = new Date();
                if( now.getHours()==10 && now.getMinutes()==30 && now.getSeconds()>=0 && now.getSeconds()<=10
                ||  now.getHours()==12 && now.getMinutes()==0 && now.getSeconds()>=0 && now.getSeconds()<=10
                ||  now.getHours()==15 && now.getMinutes()==0 && now.getSeconds()>=0 && now.getSeconds()<=10 ) {
                    // ɽ��������
                    // alert( '���߻��' + now.toLocaleTimeString() );
                } else {
                    openFlag = false;   // false ��ɽ��
                }
            }
            
            if( souAdmiCnt != 0 || (timerFlag && openFlag) ) {
                subWinObj2 = window.open(url, 'test_win', 'width='+w+',height='+h+',resizable=yes,scrollbars=yes,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
            }
        }
    }
    
    /* �����С�¦�ȥǡ������̿����뤿��ε�ǽ�����API��������ޤ��� */
    function createXmlHttpRequest()
    {
        var xmlhttp=null;
        if(window.ActiveXObject) {
            try {
                xmlhttp=new ActiveXObject("Msxml2.XMLHTTP");
            }
            catch(e) {
                try {
                    xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
                }
                catch (e2) {
                    ;
                }
            }
        } else if(window.XMLHttpRequest) {
            xmlhttp = new XMLHttpRequest();
        }
        return xmlhttp;
    }
    
    /* ����Ͼ�ǧ�Ԥ���� */
    var souAdmiCnt = 0;     // ����Ͼ�ǧ�Ԥ����
    function setAdmitCnt() {
        var xmlhttp=createXmlHttpRequest();
        if(xmlhttp!=null) {
            xmlhttp.open("POST", "./meeting_schedule_sougou_admit_output.php", false);
            xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xmlhttp.send();
            souAdmiCnt = xmlhttp.responseText;
//alert("TEST " + souAdmiCnt);
        } else {
//alert("TEST xmlhttp = NULL");
        }
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
    
    /***** ���ϻ��֤Υ��ԡ��᥽�å� *****/
    function strTimeCopy()
    {
        // �ե�ǥ��֥������Ȥλ���
        document.apend_form.str_time.value = document.apend_form.str_hour.value + ':' + document.apend_form.str_minute.value;
    }
    
    /***** ��λ���֤Υ��ԡ��᥽�å� *****/
    function endTimeCopy()
    {
        // �ե�ǥ��֥������Ȥλ���
        document.apend_form.end_time.value = document.apend_form.end_hour.value + ':' + document.apend_form.end_minute.value;
    }
    
    /***** ��żԤ�̾���Υ��ԡ��᥽�å� *****/
    function sponsorNameCopy()
    {
        // �ե�ǥ��֥������Ȥλ���
        document.apend_form.sponsor.value = document.apend_form.userID_name.value;
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
    
    /***** ��ľ��Υ��ԡ��᥽�å� *****/
    function roomCopy()
    {
        // �ե�ǥ��֥������Ȥλ���   2005/11/12 �ʹߤϻ��Ѥ��ʤ�
        // document.apend_form.note.value = document.apend_form.room.value;
    }
    
    /***** ���Ѽ֤Υ��ԡ��᥽�å� *****/
    function carCopy()
    {
        // �ե�ǥ��֥������Ȥλ���   2005/11/12 �ʹߤϻ��Ѥ��ʤ�
        // document.apend_form.note.value = document.apend_form.car.value;
    }
    
    /***** apend_form �����ϥ����å��᥽�å� *****/
    function apend_formCheck(obj) {
        if (obj.subject.value.length == 0) {
            alert("���(�ǹ礻)�η�̾�����Ϥ���Ƥ��ޤ���");
            obj.subject.focus();
            // obj.subject.select();
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
        if (obj.str_time.value.length == 0) {
            alert("���ϻ��֤����Ϥ���Ƥ��ޤ���");
            obj.str_hour.focus();
            // obj.str_hour.select();
            return false;
        }
        if (obj.end_time.value.length == 0) {
            alert("��λ���֤����Ϥ���Ƥ��ޤ���");
            obj.end_hour.focus();
            // obj.end_hour.select();
            return false;
        }
        if (obj.str_time.value > obj.end_time.value) {
            alert("���ϻ��֤Ƚ�λ���֤���ž���Ƥ��ޤ���");
            obj.str_hour.focus();
            return false;
        } else if (obj.str_time.value == obj.end_time.value) {
            alert("���ϻ��֤Ƚ�λ���֤�Ʊ���Ǥ���");
            obj.str_hour.focus();
            return false;
        }
        if (obj.sponsor.value.length == 0) {
            alert("��żԤ����ꤵ��Ƥ��ޤ���");
            obj.userID_name.focus();
            // obj.userID_name.select();
            return false;
        }
        if (obj.attenView.value.length == 0) {
            alert("���ʼԤ����ꤵ��Ƥ��ޤ���");
            obj.elements['atten[]'].focus();
            return false;
        }
        if (obj.room_no.value.length == 0) {
            alert("��꤬���ꤵ��Ƥ��ޤ���");
            obj.room.focus();
            return false;
        }
        return true;
    }
    
    /***** room_form �����ϥ����å��᥽�å�(��ļ����ֹ�, ��ļ�̾, ��ʣ�����å�) *****/
    function room_formCheck(obj) {
        // obj.room_no.value = obj.room_no.value.toUpperCase();
        if (obj.room_no.value.length == 0) {
            alert("��ļ����ֹ椬�֥�󥯤Ǥ���");
            obj.room_no.focus();
            obj.room_no.select();
            return false;
        }
        if (!this.isDigit(obj.room_no.value)) {
            alert("��ļ����ֹ�Ͽ��������Ϥ��Ʋ�������");
            obj.room_no.focus();
            obj.room_no.select();
            return false;
        }
        if (obj.room_no.value < 1 || obj.room_no.value > 32000) {
            alert("��ļ����ֹ�ϣ����飳���������ޤǤǤ���");
            obj.room_no.focus();
            obj.room_no.select();
            return false;
        }
        if (obj.room_name.value.length == 0) {
            alert("��ļ�̾���֥�󥯤Ǥ���");
            obj.room_name.focus();
            obj.room_name.select();
            return false;
        }
        if (!obj.duplicate[0].checked && !obj.duplicate[1].checked) {
            alert("��ʣ�����å��� ���롿���ʤ� �Τɤ��餫������å����Ʋ�������");
            obj.duplicate[0].focus();
            return false;
        }
        return true;
    }
    
    /***** car_form �����ϥ����å��᥽�å�(���Ѽ֤��ֹ�, ���Ѽ�̾, ��ʣ�����å�) *****/
    function car_formCheck(obj) {
        // obj.car_no.value = obj.car_no.value.toUpperCase();
        if (obj.car_no.value.length == 0) {
            alert("���Ѽ֤��ֹ椬�֥�󥯤Ǥ���");
            obj.car_no.focus();
            obj.car_no.select();
            return false;
        }
        if (!this.isDigit(obj.car_no.value)) {
            alert("���Ѽ֤��ֹ�Ͽ��������Ϥ��Ʋ�������");
            obj.car_no.focus();
            obj.car_no.select();
            return false;
        }
        if (obj.car_no.value < 1 || obj.car_no.value > 32000) {
            alert("���Ѽ֤��ֹ�ϣ����飳���������ޤǤǤ���");
            obj.car_no.focus();
            obj.car_no.select();
            return false;
        }
        if (obj.car_name.value.length == 0) {
            alert("���Ѽ�̾���֥�󥯤Ǥ���");
            obj.car_name.focus();
            obj.car_name.select();
            return false;
        }
        if (!obj.car_dup[0].checked && !obj.car_dup[1].checked) {
            alert("��ʣ�����å��� ���롿���ʤ� �Τɤ��餫������å����Ʋ�������");
            obj.car_dup[0].focus();
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
            alert("���ʼԤ����ꤵ��Ƥ��ޤ���");
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
    
    /***** print_form �����ϥ����å��᥽�å�(����) *****/
    function print_formCheck(obj) {
        // obj.group_no2.value = obj.group_no2.value.toUpperCase();
        if (!obj.str_date.value.length) {
        	alert("���դ����򳫻��������Ϥ���Ƥ��ޤ���");
        	obj.str_date.focus();
        	return false;
    	}
    	if (!this.isDigit(obj.str_date.value)) {
        	alert("�������դ˿����ʳ��Υǡ���������ޤ���");
        	obj.str_date.focus();
        	obj.str_date.select();
        	return false;
    	}
    	if (obj.str_date.value.length != 8) {
        	alert("���դγ�����������Ǥ���ޤ���");
        	obj.str_date.focus();
        	return false;
    	}
    	if (!obj.end_date.value.length) {
        	alert("���դ�����λ�������򤵤�Ƥ��ޤ���");
        	obj.end_date.focus();
        	return false;
    	}
    	if (!this.isDigit(obj.end_date.value)) {
        	alert("��λ���դ˿����ʳ��Υǡ���������ޤ���");
        	obj.end_date.focus();
        	obj.end_date.select();
        	return false;
    	}
    	if (obj.end_date.value.length != 8) {
        	alert("���դν�λ��������Ǥ���ޤ���");
        	obj.end_date.focus();
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
                window.external.AddFavorite(url + "?calUid=" + uid, "���(�ǹ礻)�������塼��");
            } else {
                window.external.AddFavorite(url, "���(�ǹ礻)�������塼��");
            }
        }
        return false;       // ���������� �¹Ԥ��к�
    }
    
    /***** ControlForm �����ϥ����å��򤷤�Ajax�¹� *****/
    function checkANDexecute(flg)
    {
        // confirm("����������˥���������ɲä��ޤ���\n\n�������Ǥ�����");
            if (flg == 1) {
                this.AjaxLoadTable("List", "showAjax");
            } else if (flg == 2){
                this.parameter = "&noMenu=yes";
                this.AjaxLoadTable("ListWin", "showAjax");
            } else if (flg == 3) {
                this.parameter = "&requireDate=yes"
                this.AjaxLoadTable("List", "showAjax");
            } else if (flg == 4){
                this.parameter = "&requireDate=yes"
                this.parameter += "&noMenu=yes";
                this.AjaxLoadTable("ListWin", "showAjax");
            } else if (flg == 5){
                this.parameter = "&noMenu=yes";
                this.AjaxLoadPITable("ListWin", "showAjax");
            } else if (flg == 6){       // ��ãȯ�������Ȳ���
                this.parameter = "&noMenu=yes";
                this.AjaxLoadTable("NotiWin", "showAjax");
            } else if (flg == 7){       // ���������Ȳ���
                this.AjaxLoadTable("EizWin", "showAjax");
            } else if (flg == 8){       // ��ļ�ͽ��ɽ��
                this.AjaxLoadTable("RooWin", "showAjax");
            } else if (flg == 9){       // ����ϡʾ�ǧ�Ԥ���
                this.AjaxLoadTable("SadWin", "showAjax");
            } else {
                this.AjaxLoadTable("List", "showAjax");
            }
            // ���ǤΥ�å��������ѹ�����
            // this.blink_msg = "�����ֹ�";
            // this.stop_blink();
        return false;   // �ºݤ�submit�Ϥ����ʤ�
    }
    /***** ���̹�����桼�����˰��´�̵��ɽ��������Ajax�ѥ���ɥ᥽�å� *****/
    // onReadyStateChange���٥�Ȥ�Ȥäƽ�������λ���Ƥ��ʤ�����WaitMessage����ϡ�
    // parameter : ListTable=���ɽ��, WaitMsg=������Ǥ������Ԥ���������
    function AjaxLoadTable(showMenu, location)
    {
        if (!location) location = "showAjax";   // Default�ͤ�����
        var parm = "?";
        parm += "showMenu=" + showMenu  // iframe�Τ����
        parm += this.parameter;
        if (showMenu == "ListWin") {    // �̥�����ɥ���ɽ��
            this.win_open("meeting_schedule_absence_Main.php"+parm, 500, 400);
            return;
        }
        // ��ãȯ�������Ȳ���
        if (showMenu == "NotiWin") {    // �̥�����ɥ���ɽ��
            this.win_open("notification.php"+parm, 1100, 600);
            return;
        }
        // ���������Ȳ���
        if (showMenu == "EizWin") {    // �̥�����ɥ���ɽ��
            this.win_open("notification_eizen.php"+parm, 1200, 600);
            return;
        }
        // ��ļ�ͽ��ɽ��
        if (showMenu == "RooWin") {    // �̥�����ɥ���ɽ��
            this.win_open("meeting_schedule_room.php"+parm+"&year="+year+"&month="+month+"&day="+day, 1000, 650);
            return;
        }
        // ����ϡʾ�ǧ�Ԥ���
        if (showMenu == "SadWin") {    // �̥�����ɥ���ɽ��
            this.win_open2("meeting_schedule_sougou_admit_list.php"+parm, 270, 250);
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
            xmlhttp.open("GET", "meeting_schedule_absence_Main.php"+parm);
            xmlhttp.send(null);
        } catch (e) {
            alert(url + "\n\n�򥪡��ץ����ޤ���\n\n" + e);
        }
    }
    /***** ���̹�����桼�����˰��´�̵��ɽ��������Ajax�ѥ���ɥ᥽�å� *****/
    // onReadyStateChange���٥�Ȥ�Ȥäƽ�������λ���Ƥ��ʤ�����WaitMessage����ϡ�
    // parameter : ListTable=���ɽ��, WaitMsg=������Ǥ������Ԥ���������
    function AjaxLoadPITable(showMenu, location)
    {
        if (!location) location = "showAjax";   // Default�ͤ�����
        var parm = "?";
        parm += "showMenu=" + showMenu  // iframe�Τ����
        parm += this.parameter;
        if (showMenu == "ListWin") {    // �̥�����ɥ���ɽ��
            this.win_open("meeting_schedule_pi_Main.php"+parm, 1000, 600);
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
            xmlhttp.open("GET", "meeting_schedule_pi_Main.php"+parm);
            xmlhttp.send(null);
        } catch (e) {
            alert(url + "\n\n�򥪡��ץ����ޤ���\n\n" + e);
        }
    }
    
    // ��ļ�ͽ��ɽ���Ϥ�ǯ�����򥻥å�
    var year = month = day = 0; 
    function setSelectDate(y,m,d)
    {
        year = y; month = m; day = d;
    }

    // �ɥ�åץ�����ꥹ�Ȥ����򤷤�ǯ�����򥻥å�
    function setDdlistDate()
    {
        year = document.getElementById('id_year').value;
        month = document.getElementById('id_month').value;
        day = document.getElementById('id_day').value;

        if( ! isDate(year+month+day) ) { // ¸�ߤ��ʤ����դξ�硢����κǽ����򥻥å�
            if( month == 2 ) { // 2��Τ����դ������١��տ�Ū�� 3/1 �򥻥å�
                month = 3;
                day = 1;
            }
            setBeforDate();
            document.getElementById('id_year').value = ('0'+year).slice(-2);
            document.getElementById('id_month').value = ('0'+month).slice(-2);
            document.getElementById('id_day').value = ('0'+day).slice(-2);
        }
        viewWeek(); // ������ɽ��
    }

    // ¸�ߤ���ǯ�����Ǥ�����
    function isDate( str )
    {
        var arr = (str.substr(0, 4) + '/' + str.substr(4, 2) + '/' + str.substr(6, 2)).split('/');
        
        if (arr.length !== 3) return false;
        var date = new Date(arr[0], arr[1] - 1, arr[2]);
        if (arr[0] !== String(date.getFullYear()) || arr[1] !== ('0' + (date.getMonth() + 1)).slice(-2) || arr[2] !== ('0' + date.getDate()).slice(-2)) {
            return false;
        } else {
            return true;
        }
    }

    // �������򥻥å�
    function setBeforDate()
    {
        var dt = new Date(year, month-1, day);
        dt.setDate(dt.getDate() - 1);

        year = dt.getFullYear();
        month = dt.getMonth()+1;
        day = dt.getDate();
    }

    // �������򥻥å�
    function setNextDate()
    {
        var dt = new Date(year, month-1, day);
        dt.setDate(dt.getDate() + 1);

        year = dt.getFullYear();
        month = dt.getMonth()+1;
        day = dt.getDate();
    }

    // ������ɽ��
    function viewWeek()
    {
        var hiduke = new Date(year, month-1, day);
        var week = hiduke.getDay();
        var yobi = new Array(" (��)"," (��)"," (��)"," (��)"," (��)"," (��)"," (��)");
        var obj = document.getElementById('id_week');
        if( week == 0 ) {
            obj.innerHTML = "<span style='color: red;'>" + yobi[week] + "</span>";
        } else if( week == 6 ) {
            obj.innerHTML = "<span style='color: blue;'>" + yobi[week] + "</span>";
        } else if( isHoliday(year, month, day) ) {
            obj.innerHTML = "<span style='color: red;'>" + yobi[week] + "</span>";
        } else {
            obj.innerHTML = "<span style='color: black;'>" + yobi[week] + "</span>";
        }
    }

    // ��Ҥε�������򥻥åȤ��Ƥ�����
    var holiday = "";
    function setHoliday(day)
    {
        holiday = day;
    }
    
    // �����Ǥ�����
    function isHoliday(y,m,d)
    {
        var date = ('0'+y).slice(-4)+'-'+('0'+m).slice(-2)+'-'+('0'+d).slice(-2);

        if( holiday.search(date) != -1 ) {
            return true;
        } else {
            return false;
        }
    }

    // ������Ϥ��Ϥ��ѿ��򥻥å�
//    function setApendData(hu, mi, room_no)
    function setApendData(s_hour,s_minute,e_hour,e_minute,room_no)
    {
/*
        var s_time = s_hour = s_minute = e_time = e_hour = e_minute = 0;

        if( mi < 30 ) {
            s_hour = ('0'+hu).slice(-2); s_minute = "00";
            e_hour = ('0'+hu).slice(-2); e_minute = "30";
        } else {
            s_hour = ('0'+hu).slice(-2); s_minute = "30";
            hu++;
            e_hour = ('0'+hu).slice(-2); e_minute = "00";
        }
*/
        s_hour = ('0'+s_hour).slice(-2); s_minute = ('0'+s_minute).slice(-2);
        e_hour = ('0'+e_hour).slice(-2); e_minute = ('0'+e_minute).slice(-2);
/**
        s_time = s_hour + ":" + s_minute;
        e_time = e_hour + ":" + e_minute;
alert("\n\nTEST\n\n" + s_time + '-' + e_time + '/' + room_no);
/**/

//        document.getElementById('id_str_time').value = s_time;
        document.getElementById('id_str_hour').value = s_hour;
        document.getElementById('id_str_minute').value = s_minute;
//        document.getElementById('id_end_time').value = e_time;
        document.getElementById('id_end_hour').value = e_hour;
        document.getElementById('id_end_minute').value = e_minute;
        document.getElementById('id_room_no').value = room_no;
    }

    // ��ư�����ʥ���ɡ�����
    function reload() 
    {
        setInterval('document.reload_form.submit()', 300000);   // ��ʬ (1000 = 1��)
    }
    
/*
}   // class meeting_schedule END  */


///// ���󥹥��󥹤�����
var MeetingSchedule = new meeting_schedule();
// blink_disp()�᥽�å���ǻ��Ѥ��륰���Х��ѿ��Υ��å�
var blink_flag = 1;


