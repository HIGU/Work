//////////////////////////////////////////////////////////////////////////////
// menu_frame.php �� JavaScript���饹                                       //
// Copyright (C) 2005-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/08/31 Created    menu_frame.js                                      //
// 2005/09/02 SavaLocate()��NN�ξ��Τ���¸����褦���ѹ�IE��logout.js��   //
// 2005/09/03 IE���� Window���֤ν���ͥǡ�������¸���Ƥ����Τ����         //
// 2005/09/05 setWinOpen()�᥽�åɤ��ɲ� menuOn/Off�����å��ˤ��Unload�б� //
// 2005/09/07 ��λ���ν�����JavaScript�ǥ��饤����Ȥβ��̥���������¸����  //
//            Cookie��winW/winH �����פˤʤä�                              //
// 2005/09/09 setCookie()/getCookie��setArrayCookie()/getArrayCookie()���ѹ�//
// 2005/09/11 siteMenuView()�᥽�åɤ��ɲ� menu_frame.php��onLoad=''�ǻ���  //
//            win_close()�ǥ��֥�����ɥ����ƥ�����ɥ��ξ�硢��˽�λ���� //
//            ����ȥ��顼�ˤʤ뤿�� try{}catch(){}���ɲ� e=[object Error]  //
// 2005/09/13 siteMenuView()�᥽�åɤν���ͤ�ɽ��ON������ɽ�����ѹ�        //
// 2005/10/26 siteMenuView()��this.Ajax("/setMenuOnOff.php?site=off");���ɲ�//
// 2006/02/24 ���饹�᥽�åɤε��ҥ�������򵼻�(̾���ʤ�)function()�����ѹ�//
// 2021/12/10 �ֿƥ�����ɥ��ϴ���...�פ�ʸ�������ΰ١��Ѹ�ɽ�����ѹ�  ���� //
//////////////////////////////////////////////////////////////////////////////

///// �����Х��ѿ� _GDEBUG �ν���ͤ򥻥å�(��꡼������false�˥��åȤ���)
var _GDEBUG = false;

/****************************************************************************
/*            menu_frame class   base_class�γ�ĥ���饹���                 *
/****************************************************************************
class menu_frame extends base_class
*/
///// �����ѡ����饹�ηѾ�
menu_frame.prototype = new base_class();   // base_class �ηѾ�
///// Class & Constructer �����
function menu_frame()
{
    /***********************************************************************
    *                           Private properties                         *
    ***********************************************************************/
    this.w  = screen.availWidth;        // ���饤����Ȥβ�����
    this.h  = screen.availHeight;       // ���饤����Ȥβ��̹⤵
    this.win_name = "win";              // ���֥�����ɥ���window̾
    this.winX     = "";                 // X����Window���� ����� window.screenLeft;�ϻȤ��ʤ�
    this.winY     = "";                 // Y����Window���� ����� window.screenTop; �ϻȤ��ʤ�
    this.offX     = 0;                  // Window��ScreenOffset�� X��
    this.offY     = 0;                  // Window��ScreenOffset�� Y��
    this.winW     = 0;                  // Window����
    this.winH     = 0;                  // Window�ι⤵
    
    /************************************************************************
    *                           Public methods                              *
    ************************************************************************/
    /***** ���֥�����ɥ�̾��������� *****/
    menu_frame.prototype.getWinName = function ()
    {
        this.win_name = window.name;
        window.defaultStatus = "TNK Web System " + this.win_name;
        document.title = "TNK Web System " + this.win_name;
    }
    
    /***** ������ɥ����֤�Offset�ͤ�������� *****/
    menu_frame.prototype.getWinLocate = function ()
    {
        this.winX = this.getArrayCookie(this.win_name, "winX");
        if (this.winX != "") this.winX -= 0;
        this.winY = this.getArrayCookie(this.win_name, "winY");
        if (this.winY != "") this.winY -= 0;
        ///// �׻��ǻ��Ѥ��뤿����ͷ����Ѵ�
        this.offX = (this.getArrayCookie(this.win_name, "offX") - 0);
        this.offY = (this.getArrayCookie(this.win_name, "offY") - 0);
        this.winW = (this.getArrayCookie(this.win_name, "winW") - 0);
        this.winH = (this.getArrayCookie(this.win_name, "winH") - 0);
    }
    
    /***** Window ���֤��������� *****/
    menu_frame.prototype.setWinLocate = function ()
    {
        ///// X,Y���κ����ͤλ���
            // �����ͤ˷׻��ǻ��Ѥ����������äƿ��ͷ����Ѵ������
            /***** logout.js �Ǥ���¸���˰ʲ���Ʊ�������å��򤷤Ƥ���(Double check) *****/
        var maxX = this.w - this.winW - (this.offX * 2);
        var maxY = this.h - this.winH - this.offY;
            // alert("maxX=" + maxX + " w=" + this.w + " winW=" + this.winW + " offX=" + this.offX);
        ///// �����ϰ���ʤ�������֤��᤹
        if (this.winX == '' || this.winY == '') return;     // ���ξ��ϰ�ư���ʤ�
        if (this.winX >= 0 && this.winY >= 0 && this.winX <= maxX && this.winY <= maxY) {
            window.moveTo(this.winX, this.winY);   // ����ΰ��֤��᤹
        }
    }
    
    /***** site menu ��ɽ������ɽ������������(IE����) *****/
    menu_frame.prototype.siteMenuView = function ()
    {
        if (document.all) {                         // IE4-
            try {
                // IE5 �ʾ������ ����ʳ��ϲ��⤷�ʤ�
                // Cookie�������ͤ����
                var site = this.getArrayCookie(this.win_name, "site");
                if (site == '1') {
                    top.topFrame.cols = "10%,*";
                    // �嵭�����ꤹ��ȥܥ���̾�ȹ��ʤ��ʤ�� base_class.js��menuStatusCheck()������
                    this.Ajax("/setMenuOnOff.php?site=on");     // �����С�¦���碌��
                } else {            // Cookie�ǡ������ʤ����ν���ͤˤ�ʤ�1
                    top.topFrame.cols = "0%,*";
                    // �嵭�����ꤹ��ȥܥ���̾�ȹ��ʤ��ʤ�� base_class.js��menuStatusCheck()������
                    this.Ajax("/setMenuOnOff.php?site=off");    // �����С�¦���碌��
                }
            } catch (e) {
                this.Debug(e, "menu_frame.js -> siteMenuView() -> top.topFrame.cols", 114);
            }
        }
    }
    
    /***** Window Offset�ͤ���¸���� *****/
    menu_frame.prototype.SaveOffset = function ()
    {
        if (document.all) {                         // IE4-
                // IE�ξ���ɬ��offset�ͤ�ɬ��
            var winX = window.screenLeft;           // Offsetʬ�û����줿���߰��֤����
            var winY = window.screenTop;
            // IE��frame���ڤ�������¸���뤷�����ߤνꡢ��ˡ�Ϥʤ� �� logout.js ���б�����
            // this.setArrayCookie(this.win_name, "winX", winX);    // X����Window���֤���¸
            // this.setArrayCookie(this.win_name, "winY", winY);    // Y����Window���֤���¸
        } else if (document.getElementById) {          // NN6-
            // NN�ξ��Ϻ��ν�Offset�ͤ�ɬ�פʤ�������Τ���
            var winX = window.screenX;              // ���߰��֤���¸
            var winY = window.screenY;
        } else {
            return;     // ����¾��̤�б��֥饦�����ϲ��⤷�ʤ�
        }
            //alert("�֥쥤��\n\n winX=" + winX + "\n\n winY=" + winY);
        ///// �����Window���֤μ����������å�
        var xData = this.getArrayCookie(this.win_name, "winX");
        var yData = this.getArrayCookie(this.win_name, "winY");
        if (xData != '' && yData != '') {
            window.moveTo(winX, winY);          // Offset�� �����Τ�����Ū�˰�ư(������֤���Offsetʬ����)
            this.offX = (winX - xData);
            this.offY = (winY - yData);
        } else {
            ///// ���ư��Window�Ͻ���ͤ��ʲ����ͤ�window_ctl.js�����ꤵ��Ƥ���
            var x = (this.w - this.winW) / 2;
            var y = (this.h - this.winH) / 2;
            window.moveTo(winX, winY);          // Offset�� �����Τ�����Ū�˰�ư(�������Offsetʬ����)
            this.offX = (winX - x);
            this.offY = (winY - y);
        }
            //alert("�֥쥤��\n\n this.offX=" + this.offX + "\n\n this.offY=" + this.offY);
        winX -= this.offX;
        winY -= this.offY;
        window.moveTo(winX, winY);              // ���ΰ��֤��᤹
            //alert("�֥쥤��\n\n winX=" + winX + "\n\n winY=" + winY);
        this.setArrayCookie(this.win_name, "offX", this.offX);          // X����Offset�ͤ���¸
        this.setArrayCookie(this.win_name, "offY", this.offY);          // Y����Offset�ͤ���¸
    }
    
    /***** Window ���֤���¸���� *****/
    menu_frame.prototype.SaveLocate = function ()
    {
        if (document.all) {                         // IE4-
            // IE��frame(iframe)�ˤ��Ƥ����10000���ͤ��֤����ᡢ���λ����ǤϤ���¸���ʤ�
            // logout.js ����¸����
            // this.winX = window.screenLeft;          // ���߰��֤���¸
            // this.winY = window.screenTop;
            // �ʲ�(top.frames)����Ѥ��Ƥ�NG�Ǥ��롣
            // �����ȤΤޤޤˤ��롣X�ǽ�λ�������ϰ��֤���¸���ʤ����ͤˤ�����
            this.winX = top.frames.screenLeft;      // ���߰��֤���¸
            this.winY = top.frames.screenTop;
                // var msg = '';
                // for (var i in top.frames) {
                //     msg += i + " => " + top.frames[i] + "\n";
                // }
                // alert(msg);
                // alert("�֥쥤��\n\n this.winX=" + this.winX + "\n\n this.winY=" + this.winY);
            // IE�ξ���ɬ��offset�ͤ�ɬ��
            this.winX -= this.offX;
            this.winY -= this.offY;
                // alert("�֥쥤��\n\n this.winX=" + this.winX + "\n\n this.winY=" + this.winY);
        } else if (document.getElementById) {       // NN6-
            this.winX = window.screenX;             // ���߰��֤���¸
            this.winY = window.screenY;
            // NN�ξ��Ϻ��ν�Offset�ͤ�ɬ�פʤ�������Τ���
            this.winX -= this.offX;
            this.winY -= this.offY;
            // ���ߤ�NN�ˤ����б����Ƥ��ʤ�
            //this.setArrayCookie(this.win_name, "winX", this.winX);    // X����Window���֤���¸
            //this.setArrayCookie(this.win_name, "winY", this.winY);    // Y����Window���֤���¸
        }
        ///// X,Y���Υ��顼�����å�������
        var w = screen.availWidth;        // ���饤����Ȥβ�����
        var h = screen.availHeight;       // ���饤����Ȥβ��̹⤵
            // �����ͤ˷׻��ǻ��Ѥ����������äƿ��ͷ����Ѵ������
            /***** menu_frame.js(menu_window.js)�Ǥ��������˰ʲ���Ʊ�������å��򤷤Ƥ���(Double check) *****/
        var maxX = w - this.winW - (this.offX * 2);
        var maxY = h - this.winH - this.offY;
            // alert("maxX=" + maxX + " w=" + w + " winW=" + this.winW + " offX=" + this.offX);
            // alert("maxY=" + maxY + " h=" + h + " winH=" + this.winH + " offY=" + this.offY);
        if (this.winX > maxX) this.winX = maxX;
        if (this.winY > maxY) this.winY = maxY;
        if (this.winX < 0) this.winX = 0;
        if (this.winY < 0) this.winY = 0;
        ///// X,Y���ΰ��� ��¸
        this.setArrayCookie(this.win_name, "winX", this.winX);    // X����Window���֤���¸
        this.setArrayCookie(this.win_name, "winY", this.winY);    // Y����Window���֤���¸
    }
    
    /***** Window �ν�λ���� *****/
    menu_frame.prototype.win_close = function ()
    {
        // this.SaveLocate();   // X�ǽ�λ���ϰ��֤���¸���ʤ�
        this.setArrayCookie(this.win_name, this.win_name, "0");
        try {
            parent.opener.focus();
        } catch (e) {
            //this.Debug(e + " �ƥ�����ɥ��ϴ��˽�λ���Ƥ��ޤ���", "menu_frame.js -> win_close() -> parent.opener.focus()", 224);
            //this.Debug(e + " The parent window has already ended.", "menu_frame.js -> win_close() -> parent.opener.focus()", 224);
        }
    }
    
    /***** Window �Υ���ɻ��ν��� *****/
    menu_frame.prototype.setWinOpen = function ()
    {
        this.setArrayCookie(this.win_name, this.win_name, "1");
        window.self.focus();
    }
    
    // Constructer �μ¹���
    /***** ����� *****/
    this.getWinName();
    this.getWinLocate();
    this.SaveOffset();      // ��ư����1�����Offset�ͤ򥻥åȤ���
    this.setWinLocate();
    
    return this;    // Object Return
}   // class menu_frame END


///// ���󥹥��󥹤�����
var menu = new menu_frame();

///// Window�ΰ�ư�򥤥٥�ȤǸ���
// onMove = menu.SaveLocate;
///// Window�Τꥵ�����򥤥٥�ȤǸ���
// onResize = menu.SaveSize;
///// ��λ���ν���
// onUnload = menu.win_close;


