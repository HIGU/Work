//////////////////////////////////////////////////////////////////////////////
// ���������Τζ�ͭ���� JavaScript���饹                                    //
// Copyright (C) 2005-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/08/25 Created  base_class.js (cookie_class, base_class)             //
// 2005/09/05 site_menu On/Off��ǽ�� JavaScript�Ǽ��� (������NN�б��ʤ�)    //
// 2005/09/07 Windown��2�İʾ峫�������֤�������MenuOff���������б���     //
//            menuStatusCheck()�᥽�åɤ��ɲ� MenuHeader.php����θƽ�      //
// �ƥ�����ɥ����̻��ͤ���˾��menuStatusCheck()/menuOnOff()Ajax()�򥳥���//
// 2005/09/09 setArrayCookie()/getArrayCookie() �᥽�åɤ��ɲ� �ͤ������Ǽ //
// 2005/09/10 delArrayCookie()���ɲ�   base_class�Υץ�ѥƥ��� maxWin �ɲ� //
// 2005/09/11 menuStatusCookieSave(status)�᥽�åɤ��ɲä�site��ON/OFF����¸//
//       �ƥ�����ɥ����siteMenu��ɽ������ɽ����ǽ���ɲ� menu_fram.js��Ϣ��//
// 2005/09/12 Window�δĶ������ꥻ�åȤ���EnvInfoReset()�᥽�åɤ��ɲ�    //
// 2005/09/15 setCookie(key, val, tmp) �� setCookie(key, val)���ѹ�         //
// 2005/10/26 this.Ajax("/setMenuOnOff.php?site=???&id=1/2") ��menuOnOff()��//
//                                                 menuStatusCheck()���ɲ�  //
// 2006/02/23 ���饹�᥽�åɤε��ҥ�������򵼻�(̾���ʤ�)function()�����ѹ�//
// 2006/02/25 evt_key_chk(evt)�᥽�åɤϵ���function()����������          //
//                      ¾�Υ�����ץȤ���ؿ��ξ�񤬽���ʤ����ḵ���᤹  //
// 2006/02/27 ���顼�ϥ�ɥ顼�ѤΥ᥽�åɤ�ؿ��ˤ��ϥ�ɥ�󥰤ʤΤ��᤹//
// 2006/04/04 menuStatusCheck�᥽�åɤ�top.window.name��NULL���ä������б�//
//            �嵭���ɲä�ById("switch_name").disabled = true; ���ɲ�       //
//            menuOnOff()�᥽�åɤǤ� if (top.topFrame)�Ǿ嵭��Ʊ�ͤ��б�   //
// 2006/06/02 ��ʸ���Ѵ��ѤΥ����Х��ѿ�_KEYUPPERFLG���ɲ� evt_key_chk()��//
//            a��z�ޤǤ����ϥ����å��ɲá�keyInUpper()�᥽�å��ɲ�          //
// 2007/08/05 base_class�˥᥽�å�clipCopy(obj),clipCopyValue(data)���ɲ�   //
//////////////////////////////////////////////////////////////////////////////

/****************************************************************************
/*                      Cookie Class ���쥯�饹�����                       *
/****************************************************************************
class cookie_class
*/
///// Class & Constructer �����
function cookie_class()
{
    ///// Private properties
    // no properties
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    /***** ���å����ǡ����Խ��� ���ܥ᥽�å� *****/
    /***** Cookie data get *****/
    cookie_class.prototype.getCookie = function (key)
    {
        var tmp1 = " " + document.cookie + ";";
        var tmp2 = "";
        var xx1  = 0;
        var xx2  = 0;
        var xx3  = 0;
        var len  = tmp1.length;
        while (xx1 < len) {
            xx2 = tmp1.indexOf(";", xx1);
            tmp2 = tmp1.substring(xx1 + 1, xx2);
            xx3 = tmp2.indexOf("=");
            if (tmp2.substring(0, xx3) == key) {
                return unescape(tmp2.substring(xx3 + 1, xx2 - xx1 - 1));
            }
            xx1 = xx2 + 1;
        }
        return "";
    }
    
    /***** Cookie data insert update *****/
    cookie_class.prototype.setCookie = function (key, val)
    {
        var tmp = key + "=" + escape(val) + "; ";
        tmp += "path=/; ";
        tmp += "expires=Fri, 31-Dec-2033 23:59:59; ";   // domain= path= ������ꤹ���Cookie�ˤ��ޤ������ʤ�loca�Τ��ᤫ��
        document.cookie = tmp;
    }
    
    /***** Cookie data drop *****/
    cookie_class.prototype.delCookie = function (key)
    {
        document.cookie = key + "=" + "; expires=1-Jan-1997 00:00:00;";
    }
    
    /***** Array Cookie insert update *****/
    cookie_class.prototype.setArrayCookie = function (parentKey, key, val)
    {
        if (!parentKey) {
            alert("�ƥ��������ꤵ��Ƥ��ʤ�������Ͽ����ޤ���");
            return "";
        }
        var arrayKey = new Array();
        var arrayVal = new Array();
        var old_val = this.getCookie(parentKey);
        // var Reg = new RegExp(' ', 'g');         // IE�Ǥ��ޤ������ʤ��Τ�����ɽ�����֥������Ȥ�����
        // old_val = old_val.replace(Reg, "");     // ���ڡ�����ͤ��(/ /g, "") g=�����о�
        if (old_val == "") {
            // new parentKey ADD
            arrayKey[0] = key;
            arrayVal[0] = val;
        } else {
            // parentKey Update
            var KeyVal = old_val.split(';');    // key=valñ�̤�ʬ��
            var data = new Array();
            var flag = 0;   // ���Ĥ��ä������Υե饰
            var i;
            for (i=0; i<KeyVal.length; i++) {
                ///// data[i] = new Array(2);
                data[i] = KeyVal[i].split('=');    // key��val��ʬ��
                if (data[i][0] == key) {
                    arrayKey[i] = key;
                    arrayVal[i] = val;
                    flag = 1;
                } else {
                    arrayKey[i] = data[i][0];
                    arrayVal[i] = data[i][1];
                }
            }
            if (flag == 0) {    // ���Ĥ���ʤ��ä����ϺǸ���ɲ�
                arrayKey[i] = key;
                arrayVal[i] = val;
            }
        }
        return this.writeArrayCookie(parentKey, arrayKey, arrayVal);
    }
    
    /***** Array Cookie Write Execute *****/
    cookie_class.prototype.writeArrayCookie = function (parentKey, arrayKey, arrayVal)
    {
        var tmp = "";
        for (var i=0; i<arrayKey.length; i++) {
            // if (!arrayKey[i] || !arrayVal[i]) continue;  // key && value undefined�Υ����å�(value=0�����뤿��NG)
            if (!arrayKey[i]) continue;     // key undefined�Υ����å�(key=0�ϻȤ��ʤ�)
            tmp += (arrayKey[i] + "=" + arrayVal[i] + ";");     // ���ڤ�� ';'
        }
        // alert(parentKey + " => " + tmp + "\n\n" + "Cookie����Ͽ���ޤ�����");
        this.setCookie(parentKey, tmp);
        return tmp;
    }
    
    /***** Array Cookie get *****/
    cookie_class.prototype.getArrayCookie = function (parentKey, key)
    {
        if (!parentKey) {
            alert("�ƥ��������ꤵ��Ƥ��ʤ������������ޤ���");
            return "";
        }
        var val = this.getCookie(parentKey);
        if (val == "") {
            // alert("�ƥ��������Ĥ���ʤ������������ޤ���");
            return "";
        }
        var ichi;   // key�θ��Ĥ��ä�����
        var ichi2;  // key=�� '=' �ΰ���
        var owari;  // ';' �ν����ΰ���
        var Reg = new RegExp(key, 'i'); // ưŪ�˥ѥ�����ޥå��󥰤�Ԥ����������ɽ�����֥������Ȥ�����
        if ( (ichi = val.search(Reg)) != -1) {
            if ( (ichi2 = val.indexOf('=', ichi)) != -1) {
                if ( (owari = val.indexOf(';', ichi2)) != -1) {
                    return val.substring(ichi2+1, owari);
                }
                // alert("����꤬���Ĥ���ʤ������������ޤ���");
                // return "";
            }
            // alert("�᤬���Ĥ���ʤ������������ޤ���");
            // return "";
        }
        // alert("�ҥ��������Ĥ���ʤ������������ޤ���");
        return "";
    }
    
    /***** Array Cookie drop *****/
    cookie_class.prototype.delArrayCookie = function (parentKey, key)
    {
        if (!parentKey) {
            alert("�ƥ��������ꤵ��Ƥ��ʤ�����������ޤ���");
            return "";
        }
        var old_val = this.getCookie(parentKey);
        if (old_val != "") {
            var arrayKey = new Array();
            var arrayVal = new Array();
            var KeyVal = old_val.split(';');    // key=valñ�̤�ʬ��
            var data = new Array();
            var flag = 0;   // ���Ĥ��ä������Υե饰
            for (var i=0; i<KeyVal.length; i++) {
                ///// data[i] = new Array(2);
                data[i] = KeyVal[i].split('=');    // key��val��ʬ��
                if (data[i][0] == key) {
                    arrayKey[i] = '';   // ���Ĥ��ä��Τǥ֥�󥯤ˤ���
                    arrayVal[i] = '';
                    flag = 1;
                } else {
                    arrayKey[i] = data[i][0];
                    arrayVal[i] = data[i][1];
                }
            }
            if (flag == 1) {    // ���Ĥ��ä����Ͻ����
                return this.writeArrayCookie(parentKey, arrayKey, arrayVal);
            }
        }
        // ����оݤʤ�
        return "";
    }
    
    return this;
}   // class cookie_class END



///// �����Х��ѿ� _GDEBUG �ν���ͤ򥻥å�
var _GDEBUG = false;
var _KEYUPPERFLG = false;   // ��ʸ���Ѵ������ե饰

/****************************************************************************
/*      base class ���������Τδ��ܥ��饹����� (cookie_class���ĥ)        *
/****************************************************************************
class base_class extends cookie_class
*/
///// �����ѡ����饹�ηѾ�
base_class.prototype = new cookie_class;   // cookie_class �ηѾ�
///// Class & Constructer �����
function base_class()
{
    /***********************************************************************
    *                           Private properties                         *
    ***********************************************************************/
    // this.properties = "none";            // �ץ�ѥƥ����ν����
    this.maxWin = 15;                       // Window�κ��祪���ץ��(20��15)Cookie�����¤Ǻ����20
    // this._GDEBUG = false; // ���顼�ϥ�ɥ���ǻ��Ѥ��뤿�ᥰ���Х��ѿ��򥯥饹���С����ѹ��ϤǤ��ʤ���
    
    /************************************************************************
    *                           Public methods                              *
    ************************************************************************/
    /***** ���̥��������� *****/
    /***** 1.���ܥ����� F12=123, F2=113 �ɤ���Ǥ�Ȥ���褦��  *****/
    base_class.prototype.evt_key_chk    = evt_key_chk;  // �ؿ���᥽�åɤȤ�����Ͽ����
    /***** �ǥХå��⡼�ɤˤ�륨�顼�������� *****/
    base_class.prototype.Debug          = Debug;        // �ؿ���᥽�åɤȤ�����Ͽ����
    /***** Ajax �ν����� private methods *****/
    base_class.prototype.Ajax           = Ajax;         // �ؿ���᥽�åɤȤ�����Ͽ����
    
    // Constructer �Υ᥽�å���
    /***** ���顼�ϥ�ɥ�ݤ����� *****/
    window.onerror = this.Debug;
    
    /***** ����ʸ�����������ɤ��������å�(ASCII code check) *****/
    base_class.prototype.isDigit = function (str)
    {
        var len = str.length;
        var c;
        for (i=0; i<len; i++) {
            c = str.charAt(i);
            if ((c < '0') || (c > '9')) {
                return false;
            }
        }
        return true;
    }
    
    /***** ����ʸ��������ե��٥åȤ��ɤ��������å� isDigit()�ε� *****/
    base_class.prototype.isABC = function isABC(str)
    {
        // var str = str.toUpperCase();    // ɬ�פ˱�������ʸ�����Ѵ�
        var len = str.length;
        var c;
        for (i=0; i<len; i++) {
            c = str.charAt(i);
            if ((c < 'A') || (c > 'Z')) {
                if (c == ' ') continue; // ���ڡ�����OK
                return false;
            }
        }
        return true;
    }
    
    /***** ����ʸ�����������ɤ��������å� �������б� *****/
    base_class.prototype.isDigitDot = function (str)
    {
        var len = str.length;
        var c;
        var cnt_dot = 0;
        for (i=0; i<len; i++) {
            c = str.charAt(i);
            if (c == '.') {
                if (cnt_dot == 0) {     // 1���ܤ������å�
                    cnt_dot++;
                } else {
                    return false;       // 2���ܤ� false
                }
            } else {
                if (('0' > c) || (c > '9')) {
                    return false;
                }
            }
        }
        return true;
    }
    
    /***** �ꥢ�륿���९��å�ɽ���ѥ᥽�å� obj=���֤ν������ *****/
    base_class.prototype.disp_clock = function (mSec, obj)
    {
        DateTime.setTime(DateTime.getTime() + mSec);
        var yy = DateTime.getYear();
        var mm = DateTime.getMonth() + 1;
        var dd = DateTime.getDate();
        var hh = DateTime.getHours();
        var ii = DateTime.getMinutes();
        var ss = DateTime.getSeconds();
        if (yy < 2000) { yy += 1900; }
        if (mm < 10) { mm = '0' + mm; }
        if (dd < 10) { dd = '0' + dd; }
        if (hh < 10) { hh = '0' + hh; }
        if (ii < 10) { ii = '0' + ii; }
        if (ss < 10) { ss = '0' + ss; }
        obj.value = yy + '/' + mm + '/' + dd + ' ' + hh + ':' + ii + ':' + ss;
    }
    
    /***** site_menu On/Off  *****/
    base_class.prototype.menuOnOff = function (id, address, reload, ajax)
    {
        if (document.all) {                         // IE4-
            try {
                // ���ߤ������ͤ����  IE5 �ʾ������ ����ʳ��ϲ��⤷�ʤ�
                if (top.topFrame) {     // �Ρ�������ñ�ȾȲ�����б�
                    var cols = top.topFrame.cols;
                } else {
                    document.getElementById("switch_name").disabled = true;
                    return;
                }
                if (cols == "10%,*") {
                    top.topFrame.cols = "0%,*";
                    document.getElementById(id).value = "MenuON";   //�ܥ���̾�ϵդ����
                    this.menuStatusCookieSave("off");
                    this.Ajax("/setMenuOnOff.php?site=off&id=1");   // �����С�¦���碌��
                } else {
                    top.topFrame.cols = "10%,*";
                    document.getElementById(id).value = "MenuOFF";  //�ܥ���̾�ϵդ����
                    this.menuStatusCookieSave("on");
                    this.Ajax("/setMenuOnOff.php?site=on&id=1");    // �����С�¦���碌��
                }
                ///// Client side script �Τ��ᡢ������Ajax��Ȥäƾ��֤򥵡��С��˽����
                // if (ajax != "no") this.Ajax("/setMenuOnOff.php");
                ///// �ƥ�����ɥ����̻��ͤ���˾�ǥ����С�����ߤ򥳥���(�嵭��Ajax)
                // �ʲ��ϸ������ꡦ�����ųݤ����Υ�˥塼�Τ���Υ���ɤǤ���â�����㤤���Ф�Τ�Ajax()�Ƕ������Ƥ���
                if (reload == 1) {
                    // �����������
                    try {
                        cols = top.topFrame.cols;
                        // Ajax��Client¦��Server¦���碌���ͤ�����äƵդ����ꤷ�Ƥ���
                        if (cols == "0%,*") this.Ajax("/setMenuOnOff.php?site=on");
                        if (cols == "10%,*") this.Ajax("/setMenuOnOff.php?site=off");
                        // �����
                        top.location.href = address;
                    } catch (e) {
                        this.Debug(e.message, "base_class.js->menuOnOff()->top.topFrame.cols", 341);
                    }
                }
            } catch (e) {
                this.Debug(e.message, "base_class.js->menuOnOff()->top.topFrame.cols", 345);
            }
        } else {                                    // NN6.1-
            try {
                // ���ߤ������ͤ����  NN6.1 �ʾ������
                top.location.href = address;
            } catch (e) {
                this.Debug(e.message, "base_class.js->menuOnOff()->top.location.href", 352);
            }
        }
        return;
    }
    
    /***** site_menu On/Off Status Cookie Save  *****/
    base_class.prototype.menuStatusCookieSave = function (status)
    {
        // ������ɥ�̾�����
        var win_name = top.window.name;     // top.����ꤹ�����˺��ʤ���
        // �ѥ�᡼���������ꤵ��Ƥ����
        if (status == "on") {
            this.setArrayCookie(win_name, "site", '1');
            return;
        } else if(status == "off") {
            this.setArrayCookie(win_name, "site", '0');
            return;
        }
        // �ѥ�᡼���������ꤵ��Ƥ��ʤ���Хȥ��륹���å���ư��
        // Cookie�μ���
        var site = this.getArrayCookie(win_name, "site");
        if (site == '0') {
            // �����
            this.setArrayCookie(win_name, "site", '1');
        } else {            // �ǡ������ʤ����֤Ͻ���ͤ�'1'(����)��Ƚ��
            // �����
            this.setArrayCookie(win_name, "site", '0');
        }
        return;
    }
    
    /***** site_menu On/Off Status check  *****/
    base_class.prototype.menuStatusCheck = function (id, address, reload)
    {
        ///// �ܥ���̾�Τߥ����å�����äƤ���н���(IE5-����)
            // menu_frame.js��onLoad='siteMenuView()'���˳ƥ�����ɥ���˽�������줿ʪ�򤳤�������
        if (document.all) {                     // IE4-
            // ������ɥ�̾�����
            if (top.window.name) {
                var win_name = top.window.name;     // top.����ꤹ�����˺��ʤ���
            } else {
                var win_name = "default_win";
                document.getElementById("switch_name").disabled = true;
            }
            try {
                // ���ߤ������ͤ����  IE5 �ʾ������ ����ʳ��ϲ��⤷�ʤ�
                var buttonName = document.getElementById(id).value;
                var site = this.getArrayCookie(win_name, "site");
                if ( (buttonName == "MenuON") && (site == '1') ) {
                    document.getElementById(id).value = "MenuOFF";  //�ܥ���̾�ϵդ����
                    this.Ajax("/setMenuOnOff.php?site=on&id=2");    // �����С�¦���碌��
                } else if ( (buttonName == "MenuOFF") && (site == '0') ) {
                    document.getElementById(id).value = "MenuON";   //�ܥ���̾�ϵդ����
                    this.Ajax("/setMenuOnOff.php?site=off&id=2");   // �����С�¦���碌��
                }
            } catch (e) {
                this.Debug(e.message, "base_class.js->menuStatusCheck()->document.getElementById()", 409);
            }
        }
        ///// �ƥ�����ɥ����̻��ͤ���˾�ǡ��ʲ��ν����ϲ��⤷�ʤ�
        return;
        ///// ���ߤ������ͤ����  IE5 NN6.1 �ʾ������ ����ʳ��ϲ��⤷�ʤ�
        try {
            var buttonName = document.getElementById(id).value;
            var cols = "";
            if (buttonName == "MenuON")  cols = "0%,*";
            if (buttonName == "MenuOFF") cols = "10%,*";
            if (top.topFrame.cols != cols) {
                this.menuOnOff(id, address+"&noSwitch", reload, "no");
            }
        } catch (e) {
            try {       // NN6.1 �ʾ������ ����ʳ��ϲ��⤷�ʤ�
                var buttonName = document.getElementById(id).value;
                if (buttonName == "MenuON") {
                    if (top.menu_site.innerWidth > 0) this.menuOnOff(id, address+"&noSwitch", reload, "no");
                }
                if (buttonName == "MenuOFF") {
                    if (top.menu_site.innerWidth <= 0) this.menuOnOff(id, address+"&noSwitch", reload, "no");
                }
            } catch (e) {
                this.Debug(e.message, "base_class.js->menuStatusCheck()->document.getElementById()", 433);
            }
        }
    }
    
    /***** Window �δĶ������ꥻ�åȤ���  *****/
    base_class.prototype.EnvInfoReset = function ()
    {
        if (!confirm("Window�ΰ��֤��礭�� �ڤ� �����Ƥ������ξ����ꥻ�åȤ��ޤ���\n\n�������Ǥ�����\n\n")) return;
        var win_name;
        for (var i=1; i<=this.maxWin; i++) {
            win_name = "win" + i;
            if (this.getCookie(win_name) == "") continue;
            this.delCookie(win_name);
        }
        alert("Window�δĶ������ꥻ�åȤ��ޤ�����\n\n�������Ƥ�Window�򱦾�Σؤǽ�λ���Ƥ���\n\n�����󤷤Ʋ�������");
    }
    
    /***** ��ʸ���Ѵ��᥽�å�  *****/
    /***** ������ˡ <input type='text' name='???' onKeyUp='OBJ.keyInUpper(this);'> *****/
    base_class.prototype.keyInUpper = function (obj)
    {
        // http://msdn.microsoft.com/library/default.asp?url=/workshop/author/dhtml/reference/methods/findtext.asp
        if (_KEYUPPERFLG) obj.value = obj.value.toUpperCase();
        return true;
            var rangeObj = obj.createTextRange();
            rangeObj.collapse(true);
            rangeObj.text = obj.value.toUpperCase();
            // rangeObj.moveToPoint(0, 0);
            // rangeObj.select();
    }
    
    /***** Windows��IE�ξ������ǻ��ꤷ�����֥������Ȥ�value�򥯥�åץܡ��ɤإ��ԡ�  *****/
    base_class.prototype.clipCopy = function (obj)
    {
        if (document.all && navigator.userAgent.match(/windows/i) && obj.value) {
            var copy_obj = obj.createTextRange()
            copy_obj.execCommand("Copy")
            // alert(obj.value + " �򥯥�åץܡ��ɤ˥��ԡ����ޤ�����");
            window.status = obj.value + " �򥯥�åץܡ��ɤ˥��ԡ����ޤ�����";
        }
    }
    
    /***** Windows��IE�ξ������ǻ��ꤷ��value�򥯥�åץܡ��ɤإ��ԡ�  *****/
    base_class.prototype.clipCopyValue = function (data)
    {
        if (document.all && navigator.userAgent.match(/windows/i) && data) {
            window.clipboardData.setData("text", data);
            // alert(data + " �򥯥�åץܡ��ɤ˥��ԡ����ޤ�����");
            window.status = data + " �򥯥�åץܡ��ɤ˥��ԡ����ޤ�����";
        }
    }
    
    return this;    // Object Return
}   // class base_class END

///// ���������Τδ��ܥ��饹�Υ��֥�����������
var baseJS = new base_class();


/***** �ǥХå��⡼�ɤˤ�륨�顼�������� *****/
/***** ���顼�ϥ�ɥ顼�Τ���ؿ��Ȥ��������prototype�Ǽ�� *****/
function Debug(error, file, line)
{
    // ���ѥ֥饦����̾�μ���
    var browser = navigator.userAgent;
    // IE�ξ��ϳ�ΨŪ�ˣ�������¿���Τǥޥ��ʥ����Ƥ��롣
    var str = navigator.appName.toUpperCase();
    if (str.indexOf("EXPLORER") >= 0) line -= 1;
    // �����Х��ѿ���_GDEBUG=true�λ��ϥ�å�������Ф���
    if (_GDEBUG) {
        var msg = "";
        msg += "Error Infomation     : " + error + "\n\n";
        msg += "Error File Name     : " + file + "\n\n";
        msg += "Error Line Number  : " + line + "\n\n";
        msg += "Use Browser Name : " + browser + "\n\n";
        alert(msg);
    } else {
        // alert("_GDEBUG ����" + _GDEBUG + "�Ǥ���");
        Ajax("/error/ErrorScriptLog.php?error="+error+"&file="+file+"&line="+line+"&browser="+browser);
    }
}

/***** Ajax �ν����� private methods *****/
/***** ���顼�ϥ�ɥ顼����ƤФ�뤿��ؿ��Ȥ��������prototype�Ǽ�� *****/
function Ajax(url)
{
    if (url) {
        try {
            var xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        } catch (e) {
            try {
                var xmlhttp = new XMLHttpRequest();
            } catch (e) {
                // this.Debug(e.message, "base_class.js->Ajax()->new XMLHttpRequest()", 492);
                // alert("�����ѤΥ֥饦������̤�б��Ǥ���\n\n" + e);
            }
        }
        try {
            // alert("xmlhttp.open ��¹Ԥ��ޤ���");
            xmlhttp.open("GET", url);
            xmlhttp.send(null);
        } catch (e) {
            // this.Debug(e.message, "base_class.js->Ajax()->xmlhttp.open()", 501);
            // alert(url + "\n\n�򥪡��ץ����ޤ���\n\n" + e);
        }
    }
}

/***** �����ܡ������ϥ��٥�Ƚ��� ���̥��������� *****/
/***** �ؿ��Ȥ��������prototype�Ǽ����¾�Υ��饹����ؿ�̾��������褦�ˤ��� *****/
/***** 1.���ܥ����� F12=123, F2=113 �ɤ���Ǥ�Ȥ���褦��  *****/
function evt_key_chk(evt)
{
    // �����Х��ѿ��� backward_obj �������Υ���ȥ��륪�֥�������
    var browser = navigator.appName;
    if (browser.charAt(0) == 'M') {         // IE�ξ��
        var chk_key = event.keyCode;        // IE�Ǥϥ��������ɤ�Ĵ�٤�ˤ� event.keyCode ��Ȥ���
    } else {                                // NN�ξ�������
        var chk_key = evt.which;            // NN�Ǥ� evt.which ��Ȥ���(evt�ϥ��٥�Ȥˤ�äƸƤӽФ����ؿ��Υ��å���������)
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
        if (chk_key >= 65 && chk_key <= 90) {   // A(a) �� Z(z)�ޤǡ���ʸ����ʸ���ζ��̤�����ʤ�
            _KEYUPPERFLG = true;
        } else {
            _KEYUPPERFLG = false;
        }
    }
    return true;
}

