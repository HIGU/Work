<?php
//////////////////////////////////////////////////////////////////////////////
// ���⵬����˥塼   company regulation                                 �� //
// Copyright (C) 2005-2020 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/07/25 Created  regulation_menu.php                                  //
// 2006/02/21 �Ұ������˾�ˤ�����ȵĵ����ʲ�10���ܤ��ɲá����֤��ηϽ��//
// 2006/03/08 �������յ���������ͻ���������������յ�����06/02/01�Ǥغ���//
// 2006/06/01 �������ñ�����ɽ���ɲ�                                      //
// 2006/07/01 �̶и�����ٵ��§��06.06.01���ѹ�                            //
// 2007/03/27 ����ȵĵ��ꢪ����ȵĵ�����ѹ� ��ë                         //
//            ���������Ѱ����§�����������Ѱ����§���ѹ� ��ë             //
// 2007/04/17 ��̳ʬ������������ȵĵ����������¤Ӥ˷򹯴������§��        //
//            07.04.01���ѹ�                                                //
// 2008/04/08 �¶⵬�ꡦ��̳ʬ�����ꡦ���Ȱ��ޥ��������������              //
//            08.04.01���ѹ�                                           ��ë //
// 2008/09/01 �������յ�����08.09.01���ѹ�                             ��ë //
// 2009/01/13 ���ȵ�§��09.01.08���ѹ������ȵ�§���̻���ɲ�           ��ë //
// 2009/04/07 �¶⵬�����������¤Ӥ˷򹯴������§��09.04.01���ѹ���     �� //
// 2009/07/07 ��̳ʬ�����������ȵ�����09.07.01���ѹ���                   �� //
// 2009/07/07 ��̳ʬ�����������ȵ�����09.10.01���ѹ���                   �� //
// 2010/03/31 ������鵬����10.04.01���ѹ���                             �� //
// 2010/04/27 ��̳ʬ�����������ȵ������¶⵬����10.04.01���ѹ���         �� //
//            �Ұ���ͭ�֤ζ�̳���Ѥ˴ؤ��뵬����10.04.01���ѹ���         �� //
// 2010/06/15 ����������Ϣ�ε�����ɲ�                                 ��ë //
// 2010/07/16 ����ٶȵ��ꡦ���ٶȵ����10.07.01���ѹ�               ��ë //
// 2010/08/19 ��������10.08.06���ɲ�                                   ��ë //
// 2010/08/20 ����ȵĵ����10.08.06���ѹ�                             ��ë //
// 2010/09/22 �Ұ���ͭ�֤ζ�̳���Ѥ˴ؤ��뵬����                            //
//            �ޥ������ζ�̳���Ѥ˴ؤ��뵬����ѹ�                     ��ë //
// 2010/10/11 ���ȵ�§��10.10.11���ѹ����ѡ��Ƚ��ȵ�§10.10.11���ɲ�   ��ë //
// 2010/12/07 �ѡ��ȷ�Ĥ����06.08.29���ɲ�                             ��ë //
// 2011/01/06 ��ǯ��Ƹ��ѷ���Ұ�������11.01.01���ѹ���                    //
//            ������������ڤӺ�§10.12.01�򿷵��ɲ�                   ��ë //
// 2011/03/08 ����ٶȵ��ꡦ���ٶȵ����11.03.01���ѹ�               ��ë //
// 2011/04/02 �ѡ��Ƚ��ȵ�§���¶⵬������ǯ��Ƹ��ѷ���Ұ�������          //
//            11.04.01���ѹ�                                           ��ë //
// 2011/04/07 ��̳ʬ��������11.04.01���ѹ�                             ��ë //
// 2011/08/22 ����ٶȵ����11.09.01�ز��ٶȵ����11.03.01���ѹ�     ��ë //
// 2011/09/14 �����̳���Ѻ�§10.04.01���ɲ�                           ��ë //
// 2012/03/27 ��������ʲ��ε����ɽ������褦�˻ųݤ����ɲ�            //
//              ���Ȱ��ޥ�������������          12/4/1 ����                 //
//              �ޥ������ζ�̳���Ѥ˴ؤ��뵬��  12/4/1 ����                 //
//              ���������յ���                12/4/1 ����                 //
//              ����ι����                    12/4/1 ����                 //
//              �ʎߎ��Ľ��ȵ�§                    12/4/1 ����                 //
//              �¶⵬��                        12/4/1 ����                 //
//              �������¤Ӥ˷򹯴������§      12/4/1 ����                 //
//              ����ٶȵ���                    12/4/1 ����                 //
//              ���ٶȵ���                    12/4/1 ����                 //
//              ��־������§                  12/4/1 �ѻ�                 //
//              �̶и�����ٵ��§              12/4/1 �ѻ�            ��ë //
// 2012/04/03 ��������ʸ�������äƤ����Τ���                         ��ë //
// 2012/10/03 �������뤬����Ƥ����Τ�ɽ������褦���ѹ�             ��ë //
// 2012/11/27 ���ȵ�§��2012/12/01���12.12.01���ѹ�                   ��ë //
// 2013/03/21 13.04.01��꽢�ȵ�§�ʥѡ��Ȥ�ˤ�13.04.01���ѹ�         ��ë //
// 2013/10/25 ��������դ��ΰʲ��ε������ѹ�                              //
//              ���ȵ�§                                                    //
//              �ѡ��Ƚ��ȵ�§                                              //
//              ��Ĥ����                                                    //
//              ����ι����                                                //
//              �����̳���Ѻ�§                                            //
//              �¶⵬��                                                    //
//              ����ٶȵ���                                                //
//              ���ٶȵ���                                                //
//              ������Ƚ��ٶȵ���                                          //
//              ��ǯ��³��ɽ������                                          //
//            â�����ʲ��ε����ϸ�����������Ƥ��ʤ��ä���ΤʤΤǥ����Ȳ�//
//              �����������١��ʎ׎��Ҏݎ��ɻߵ����ʿ�����                              //
//              �ʎߎ܎����ʎ׎��Ҏݎ��ɻߵ����ʿ�����                                //
//              ��̳��ҳ�ˡ��������                                    //
//              �����򹯴��������֤˴ؤ��뵬��                              //
//              �и�����                                                    //
//            �ޤ����������˹�碌�Ƶ������ѹ��������פʻųݤ�����   ��ë //
// 2013/11/25 �����ɲä�ȼ���礭���쥤�����Ȥ��ѹ�                          //
//            �������������ε����ηϿޤ˹�碌�ơ��夫����ɽ��            //
//            �ʲ��ε������ɲ�                                              //
//              �괾                                    12/11/07            //
//              ���������                            11/06/22            //
//              ��̳���µ���                            08/04/01            //
//              ������������                            10/08/06            //
//              �����ηϿ�                              12/04/01            //
//              ��̳���ѵ���                            00/09/07            //
//              ���ϵ���                                00/08/03            //
//              ���ϼ谷��§                            00/09/07            //
//              ����ž�С���Ǥ�Ԥμ谷���˴ؤ��뵬��    00/08/23            //
//              ��̳��ҳ�ˡ�곰�������                13/10/01            //
//              �����򹯴��������֤˴ؤ��뵬��          13/10/01            //
//              ��������                                00/09/04            //
//              �и�����                                13/10/01            //
//              ������߰�����                          00/09/05            //
//              ����谷����                            10/04/01            //
//              ������ĥ���ˤ�����ҳ��������          00/09/04            //
//              �����������١��ʎ׎��Ҏݎ��ɻߵ���                  13/10/01            //
//              �ʎߎ܎����ʎ׎��Ҏݎ��ɻߵ���                    13/10/01            //
//              ��Ǽ����                                02/04/01            //
//              �����ͭ���ڷ���������                  00/08/24            //
//              �����׻�����                            10/12/01            //
//              ê���񻺴�������                        10/10/01            //
//              ����񻺴�������                        10/10/01            //
//              ������������                            00/08/22            //
//              �����������                            00/08/22       ��ë //
// 2014/01/20 ������դ��ǰʲ��ε�����ѹ�                                //
//              ��ۡ��ȵĵ���(����ȵĵ������̾���ѹ�) 14/1/1 ����        //
//              �ѡ��ȥ����ޡ����ȵ�§(����)                                //
//                          (�ѡ��Ƚ��ȵ�§���̾���ѹ�) 14/1/1 ����        //
//              ���Ȱ��η�Ĥ�����˴ؤ��뵬��                              //
//                                (��Ĥ�������̾���ѹ�) 14/1/1 ����        //
//              �ѡ��ȥ����ޡ��η�Ĥ�����˴ؤ��뵬��                      //
//                          (�ѡ��ȷ�Ĥ�������̾���ѹ�) 14/1/1 ����        //
//              ���ɷײ赬��(���ɷײ���̾���ѹ�) 14/1/1 �������ɲ�   ��ë //
// 2014/01/24 ������դ��ǰʲ��ε�����ѹ����ɲá���碌�ƥ쥤�������ѹ�  //
//              �� ���롼�׶�ͭ����(����)                                   //
//                  ���������������ηϿ�                 14/1/1 ����        //
//                  ͽ����������                         14/1/1 ����        //
//                  �����������                         14/1/1 ����        //
//                  �������������§                     14/1/1 ����        //
//                  �����������١��ʎ׎��Ҏݎ��ɻߵ���               14/1/1 ����        //
//                  �ʎߎ܎����ʎ׎��Ҏݎ��ɻߵ���                 14/1/1 ����        //
//              �� ���롼�׶�ͭ����(����)                                   //
//                  �����ƺ�����                         14/1/1 ����        //
//                  ����ץ饤���󥹵���                 14/1/1 ����        //
//                  ����������                         14/1/1 ����        //
//                  �Ŀ;����ݸ��                     14/1/1 ����        //
//                  ���칩��ގَ��̎������Լ����������     14/1/1 ����        //
//                  ���꡼��Ĵã�Ѱ���§(��§)         14/1/1 ����        //
//                  ���칩�ｾ�Ȱ���������             08/10/06 ����      //
//                  ���칩�ｾ�Ȱ������񱿱ĺ�§         08/10/06 ����      //
//                  ���󷿳�����մ��ǯ�⵬��           10/12/07 ����      //
//              �� ��������                                                 //
//                  ���ϵ���                             14/1/1 ����   ��ë //
// 2014/01/31 ��������������������������ˤʤäƤ����Τ�����           ��ë //
// 2014/02/14 ������������ϥ��롼�׶�ͭ�ǤϤʤ��Τ�����               ��ë //
// 2014/03/11 ��̳ʬ��������13.08.11�դ��ѹ����ѹ�ϳ���               ��ë //
// 2014/04/01 ��ǯ��Ƹ��Ѥη���Ұ�������14.04.01�դ����ѹ�           ��ë //
// 2014/04/04 �ʲ��ε�����14.04.01�դ����ѹ�                                //
//            ����ٶȵ��������ٶȵ�������̳ʬ����������������       ��ë //
// 2014/04/11 ���ȵ�§�θ������                                       ��ë //
//            ����������ٵ�����13.04.01�դ��ѹ����ѹ�ϳ���           ��ë //
// 2014/06/25 ������������14.06.23�դ����ѹ�                         ��ë //
// 2014/09/22 �ʲ��ε�����14.09.01�դ����ѹ�                                //
//            ����ž�С���Ǥ�Ԥμ谷���˴ؤ��뵬��                          //
//            �и�����������谷����                                   ��ë //
// 2014/10/10 ����ι��������ɽ������ɽ�����ѹ�                       ��ë //
// 2015/03/31 ������������15.04.01�դ��ѹ���4/1��������                 //
//            ����������15.04.01�դ��ɲá�4/1��������              ��ë //
//            ���칩����ηϿޤ˹�碌�ƿ������ؤ�                     ��ë //
// 2015/09/18 �ʲ��ε�����15.09.01�դ����ѹ�                                //
//            ���Ȱ��η�Ĥ�����˴ؤ��뵬��                                //
//            �ѡ��ȥ����ޡ��η�Ĥ�����˴ؤ��뵬��                   ��ë //
// 2015/11/13 ���⡦����ι������15.11.04���ѹ�                       ��ë //
// 2015/12/10 ʸ������ɽ��ʸ����¸���ְ���ɽ�򿷵��Ǻ�               ��ë //
// 2016/01/05 ���ȵ�§���ѡ��Ƚ��ȵ�§�������������(��)��16.01.01�դ��ѹ�  //
//            ����Ŀ;���谷��������                               ��ë //
// 2016/04/13 ����谷��������̳ʬ��������16.04.01�դ��ѹ�             ��ë //
// 2016/05/20 �ѡ��ȥ����ޡ����ȵ�§��16.04.01�դ��ѹ�                 ��ë //
// 2016/08/05 ȯ���谷��������§�򿷵��ɲá������ηϿޤ򹹿�           ��ë //
// 2016/10/31 ��ǯ��Ƹ��Ѥη���Ұ��������ѡ��ȥ����ޡ����ȵ�§            //
//            ���ȵ�§��16.11.01�դ��ѹ�(11.01����ѹ��Ȥʤ�褦�ų�)  ��ë //
// 2016/11/21 �ޥ�������̳���ѻ��Υ������ñ����Ǻ�(2016.03.01�ա�)   ��ë //
// 2016/12/06 ����Ŀ;���谷������16.11.01�դع���                   ��ë //
// 2017/03/22 ��ۡ��ȵĵ����������̳�ȹ���������ҷ�۴����ɲ�     ��ë //
// 2017/03/30 ��ۡ��ȵĵ����ι���������ҷ�۴��򣴷�����ɽ���ѹ�    //
//            ����褦�ųݤ����ɲá�¾�Τλųݤ�����                 ��ë //
// 2017/05/18 ����ž�С���Ǥ�Ԥμ谷���˴ؤ��뵬����17.04.11�դع���   ��ë //
// 2017/06/05 ����Ŀ;���谷������17.05.11�դع���                   ��ë //
// 2017/07/14 ��ۡ��ȵĵ�����17.07.01�դع���                         ��ë //
// 2017/09/29 �������¤Ӥ˷򹯴������§��17.10.01�դع���             ��ë //
// 2017/11/22 �ʲ��ε�����17.10.01�դ����ѹ�                                //
//            ����ٶȵ��������ٶȵ���                               ��ë //
// 2017/12/22 ȯ���谷������17.09.01�դ����ѹ�                         ��ë //
// 2018/02/16 �¶⵬�ꡢ�ѡ��Ƚ��ȵ�§��18.02.06�դ����ѹ�             ��ë //
// 2018/03/22 ����ι��������ǯ��Ƹ��Ѥη���Ұ����������¤Ӥˡ�          //
//            ���ٶȵ�����18.04.01����ե������ѹ�����褦                //
//            �ץ�������                                         ��ë //
// 2018/04/02 ����ι��������ɽ��18.04.01�դ��ѹ�                     ��ë //
// 2018/04/06 �ƥ��롼�׶�ͭ������ʲ����̤��ѹ�                       ��ë //
//                  �Ŀ;����ݸ��                     17/10/23��         //
//                  ���칩�殺�롼�������Լ����������   17/03/07��         //
//                  ����������                         18/04/01��         //
//                  ���󷿳�����մ��ǯ�⵬��           16/10/01��         //
// 2018/04/19 ����������ٵ�����18.04.01�դ��ѹ�                       ��ë //
// 2018/05/24 ����бķײ赬�����ɲ�(����) �������ηϿޤ��Ѥ��        ��ë //
//            ���ʬ�������Ƥ����Τ�������acrobat��ʸ������               //
// 2018/06/06 ��������������18.05.22�դ��ѹ����ηϿޤ��               ��ë //
//            ����бķײ赬���ϸ���̵���Τ��׳�ǧ                          //
// 2018/06/11 ����бķײ赬����18.05.22�դ��Ǹ���                     ��ë //
// 2018/06/22 �괾��18.06.18�դ��ѹ�                                   ��ë //
// 2018/11/21 ���ȵ�§��18.10.16���ѹ����ѡ��Ƚ��ȵ�§��18.10.16���ѹ� ��ë //
// 2019/01/17 ����ȵĵ�����19.01.01�դ��ѹ�(���⡦����ɽ��)           ��ë //
// 2019/04/18 �������¤Ӥ˷򹯴������§��19.04.01���ѹ�               ��ë //
// 2019/09/30 �Ƶ�����19/10/01�դ��ǹ���(ʬ��)                         ��ë //
//            �ַ�Ĥ����⵬���סġֽ��Ȱ��η�Ĥ�����˴ؤ��뵬���פ�      //
//                                �֥ѡ��ȥ����ޡ��η�Ĥ�����˴ؤ��뵬����//
//                                 ��������                                 //
//            �ֹ���ž�С���Ǥ�Ԥμ谷�˴ؤ��뵬���סĽ����佤�����θ�ľ��  //
//            �ֹ���ι�����סĽ�����ε��ܤ�ۣ��ʤ���ʸ���ν���          //
//            �ֽ������¤Ӥ˷򹯴������§�סġֽ�������§�פ�̾���ѹ�      //
//            �ֹ�����������סļ�̳�˹�碌��ʸ���ν���                    //
//            �֥ѡ��ȥ����ޡ��η�Ĥ�����˴ؤ��뵬���ס�����ˤ���ѻ�    //
//            �ֵ����ηϿޡסľ嵭���ѹ�ˤ���ѹ�                          //
// 2019/10/24 ���칩�ｾ�Ȱ������������칩�ｾ�Ȱ������񱿱ĺ�§��      //
//            19.09.13�դ����ѹ�                                       ���� //
// 2020/04/21 �ƥ��롼�׶�ͭ������ʲ����̤��ѹ�                       ��ë //
//                  ���칩�殺�롼�������Լ����������   20/03/10��         //
//                  ȯ���谷������20.02.07�դ����ѹ�                   ��ë //
//                  ������������20.04.01�դ����ѹ�                   ��ë //
//                  �����ϥ顦�ѥ�ϥ�����礷��                            //
//                  �ϥ饹�����ɻߵ�����20.04.01�դ��Ȥ���ɽ��       ��ë //
// 2020/05/25 ��������������20.04.01���ѹ�                             ��ë //
// 2020/09/24 �����ɲä�ȼ���쥤�����Ȥ��ѹ�(10/01��ư)                ��ë //
//            �ʲ��ε������ɲá�2020/10/01��)                               //
//              ������������ʵ����ηϿޡ�                                  //
//              ����ȵĵ���                                                //
//              ��̳���ѵ���                                                //
//              ʸ����-ʸ�������̳�ޥ˥奢��                             //
//              ���ϵ���                                                    //
//              ����ι����                                                //
//              ���⸦��ι����                                            //
//              ����ι����                                                //
//              �ޥ������ζ�̳���Ѥ˴ؤ��뵬��                              //
//              ���Ȱ��ޥ�������������                                      //
//              ����������������                                            //
//              ���������Ѱ����§                                          //
//              ���ɷײ赬��                                                //
//              ��������                                                    //
//              �и�����                                                    //
//              ����ž�С���Ǥ�Ԥμ谷���˴ؤ��뵬��                        //
//              ��������§                                                  //
//              ����ͻ����                                                //
//              �������յ���                                                //
//              ���������յ���                                            //
//              ����谷����                                                //
//              ��̳��ҳ�ˡ�곰�������                                    //
//              ����ٶȵ���                                                //
//              ������ĥ���ˤ�����ҳ��������                              //
//              ���ٶȵ���                                                //
//              ������Ƚ�����                                              //
//              �����򹯴��������֤˴ؤ��뵬��                              //
//              ��������                                                    //
//              ��Ǽ����                                                    //
//              �����ͭ���ڷ���������                                      //
//              �����׻�����                                                //
//              ê���񻺴�������                                            //
//              ����񻺴�������                                            //
//              ������������                                                //
//              �����������                                                //
//              ��̳���µ���                                                //
//              ��̳ʬ������                                                //
//              �����̳���Ѻ�§                                            //
//            �ʲ��ε���������2020/10/01��)                               //
//              ������߰�����                                              //
// 2021/01/12 ��������������20.12.01���ѹ�                             ��ë //
// 2021/02/17 ��������ٶȵ���������21.01.01���ѹ�                   ��ë //
// 2021/05/20 �����ƺ�����,����������,��������������                      //
//            21.04.01���ѹ�                                           ��ë //
// 2021/10/28 ��ۡ��ȵĵ�����21.12.01���ѹ�����褦�˥��å�           ��ë //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../function.php');           // define.php �� pgsql.php �� require_once ���Ƥ���
// require_once ('../tnk_func.php');           // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../MenuHeader.php');         // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
if (isset($_SESSION['REGU_Auth'])) {
    $menu = new MenuHeader(-1);             // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
} else {
    $menu = new MenuHeader(0);              // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
}

////////////// ����������
$menu->set_site(INDEX_REGU, 0);            // site_index=INDEX_REGU(���⵬����˥塼) site_id=0(�ʤ�)
////////////// �꥿���󥢥ɥ쥹����(���л��ꤹ����)
// $menu->set_RetUrl(SYS_MENU);                // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('���⵬�� �Ȳ� ��˥塼');
//////////// ɽ�������
$menu->set_caption('�ʲ��ε������ Acrobat Reader 5 �ʾ�Ǳ�������ޤ���');
$uniq = 'ID=' . uniqid('regu');

if ($_SESSION['User_ID'] == '300144') {
    //$today = 20201001;    // �ƥ�����
    $today = date('Ymd');
} else {
    $today = date('Ymd');
}
///// ���եƥ����ѡʥƥ��ȴ�λ���ϥ����Ȳ���
//$today = 20130401;

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>
<script type='text/javascript' language='JavaScript' src='regulation.js?id=<?= $uniq ?>'></script>
<link rel='stylesheet' href='regulation.css?id=<?= $uniq ?>' type='text/css' media='screen'>
</head>
<body onLoad='Regu.set_focus(document.getElementById("start", ""))'>
    <center>
<?= $menu->out_title_border() ?>
    <!--
    <div class='pt12b'><?php echo $menu->out_caption()?></div>
    -->
    <div class='pt12b'>&nbsp;</div>
    <B>
    ������������������������������������������������������������������������������������������
    (��)�ϥ��롼�׶�ͭ����
    </B>
    <!--
    2020/09/24 2020/10/01�ϵ������꤬¿��������Ǿ��ʬ��
    -->
    <table class='layout'>
    <tr class='layout'>
        <td class='layout' id='start' colspan='2'>
        �����ܵ���
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout' id='start'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("teikan18.06.18.pdf", "")'
                onMouseover="status='�괾��ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='�괾��ɽ�����ޤ���'
            >�괾</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("torisimariyaku-kai21.04.01.pdf", "")'
                onMouseover="status='�����������ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='�����������ɽ�����ޤ���'
            >���������</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout' id='start'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("internal_control_kitei21.04.01.pdf", "")'
                onMouseover="status='��������������ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='��������������ɽ�����ޤ���'
            >������������(��)</a>
        </td>
        <td class='layout'>
            ��
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout' id='start' colspan='2'>
        �������������ȿ�����
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout' id='start'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("shokumu-kengen20.10.01.pdf", "")'
                onMouseover="status='��̳���µ�����ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='��̳���µ�����ɽ�����ޤ���'
            >��̳���µ���</a>
        </td>
        <td class='layout' id='start'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("gyoumu-bunshou21.04.01.pdf", "")'
                onMouseover="status='��̳ʬ��������ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='��̳ʬ��������ɽ�����ޤ���'
            >��̳ʬ������</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout' id='start' colspan='2'>
        �����������ݶ�̳���������̴����ε���
        </td>
    </tr>
    <tr class='layout'>
        <?php
        //if ($_SESSION['User_ID'] == '300144') {
        if ($today >= 20211201) {
        ?>
        <td class='layout' id='start'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("kessai-ringi21.12.01.pdf", "")'
                onMouseover="status='��ۡ��ȵĵ�����ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='��ۡ��ȵĵ�����ɽ�����ޤ���'
            >��ۡ��ȵĵ���</a>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("kessai-kijyun-nitijyo19.01.01.pdf", "")'
                onMouseover="status='�����̳��۴���ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='�����̳��۴���ɽ�����ޤ���'
            >�����̳</a>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("kessai-kijyun-kokunai21.12.01.pdf", "")'
                onMouseover="status='����������ҷ�۴���ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='����������ҷ�۴���ɽ�����ޤ���'
            >�����������</a>
        </td>
        <?php
        } else {
        ?>
        <td class='layout' id='start'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("kessai-ringi21.04.01.pdf", "")'
                onMouseover="status='��ۡ��ȵĵ�����ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='��ۡ��ȵĵ�����ɽ�����ޤ���'
            >��ۡ��ȵĵ���</a>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("kessai-kijyun-nitijyo19.01.01.pdf", "")'
                onMouseover="status='�����̳��۴���ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='�����̳��۴���ɽ�����ޤ���'
            >�����̳</a>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("kessai-kijyun-kokunai21.04.01.pdf", "")'
                onMouseover="status='����������ҷ�۴���ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='����������ҷ�۴���ɽ�����ޤ���'
            >�����������</a>
        </td>
        <?php
        }
        ?>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("kitei-kanri21.04.01.pdf", "")'
                onMouseover="status='�������������ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='�������������ɽ�����ޤ���'
            >�����������</a>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("kitei-taikeizu21.04.01.pdf", "")'
                onMouseover="status='�����ηϿޤ�ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='�����ηϿޤ�ɽ�����ޤ���'
            >�����ηϿ�</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout' id='start'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("gyoumu-hikitsugi20.10.01.pdf", "")'
                onMouseover="status='��̳���ѵ�����ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='��̳���ѵ�����ɽ�����ޤ���'
            >��̳���ѵ���</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("naibu-kansa21.04.01.pdf", "")'
                onMouseover="status='�����ƺ�������ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='�����ƺ�������ɽ�����ޤ���'
            >�����ƺ�����(��)</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout' id='start'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("chuki-keikaku18.05.22.pdf", "")'
                onMouseover="status='����бķײ赬����ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='����бķײ赬����ɽ�����ޤ���'
            >����бķײ赬��(��)</a>
        </td>
        <td class='layout' id='start'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("yosan-kanri14.01.01.pdf", "")'
                onMouseover="status='ͽ������������ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='ͽ������������ɽ�����ޤ���'
            >ͽ����������(��)</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("compliance-kitei14.01.01.pdf", "")'
                onMouseover="status='����ץ饤���󥹵�����ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='����ץ饤���󥹵�����ɽ�����ޤ���'
            >����ץ饤���󥹵���(��)</a>
        </td>
        <td class='layout' id='start'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("naibu-tsuho21.04.01.pdf", "")'
                onMouseover="status='������������ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='������������ɽ�����ޤ���'
            >����������(��)</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout' id='start' colspan='2'>
        �����������ݶ�̳��������̳�ط��ε���
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("inshou20.10.01.pdf", "")'
                onMouseover="status='���ϵ�����ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='���ϵ�����ɽ�����ޤ���'
            >���ϵ���</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("keicho-mimai21.04.01.pdf", "")'
                onMouseover="status='���Ȱ��η�Ĥ�����˴ؤ��뵬����ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='���Ȱ��η�Ĥ�����˴ؤ��뵬����ɽ�����ޤ���'
            >��Ĥ����⵬��</a>
        </td>
        
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("kokunai-ryohi20.10.01.pdf", "")'
                onMouseover="status='����ι������ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='����ι������ɽ�����ޤ���'
            >����ι����</a>
            &nbsp;
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("kokunai-beppyou1.18.04.01.pdf", "")'
                onMouseover="status='����ι��������ɽ����ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='����ι��������ɽ����ɽ�����ޤ���'
            >��ɽ��</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("kaigai-ryohi20.10.01.pdf", "")'
                onMouseover="status='����ι������ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='����ι������ɽ�����ޤ���'
            >����ι����</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("kokunaikensyu-ryohi20.10.01.pdf", "")'
                onMouseover="status='���⸦��ι������ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='���⸦��ι������ɽ�����ޤ���'
            >���⸦��ι����</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("anzeneisei-kanri20.10.01.pdf", "")'
                onMouseover="status='������������������ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='������������������ɽ�����ޤ���'
            >����������������</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("jyugyouin-maika21.04.01.pdf", "")'
                onMouseover="status='���Ȱ��ޥ���������������ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='���Ȱ��ޥ���������������ɽ�����ޤ���'
            >���Ȱ��ޥ�������������</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("mycar-gyoumu20.10.01.pdf", "")'
                onMouseover="status='�ޥ������ζ�̳���Ѥ˴ؤ��뵬����ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='�ޥ������ζ�̳���Ѥ˴ؤ��뵬����ɽ�����ޤ���'
            >�ޥ������ζ�̳���Ѥ˴ؤ��뵬��</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("anzeneisei-iinkai20.10.01.pdf", "")'
                onMouseover="status='���������Ѱ����§��ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='���������Ѱ����§��ɽ�����ޤ���'
            >���������Ѱ����§</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("bunsho-kitei20.10.01.pdf", "")'
                onMouseover="status='ʸ�����ڤ�ʸ�������̳�ޥ˥奢���ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='ʸ�����ڤ�ʸ�������̳�ޥ˥奢���ɽ�����ޤ���'
            >ʸ������ʸ�������̳�ޥ˥奢��</a>
            <BR>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("bunsho-beppyou1.pdf", "")'
                onMouseover="status='��ɽ����ʸ����¸���ְ���ɽ��ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='��ɽ����ʸ����¸���ְ���ɽ��ɽ�����ޤ���'
            >��ɽ����ʸ����¸���ְ���ɽ</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("syobo20.10.01.pdf", "")'
                onMouseover="status='���ɷײ赬����ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='���ɷײ赬����ɽ�����ޤ���'
            >���ɷײ赬��</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("jyoho-kanri14.01.01.pdf", "")'
                onMouseover="status='�������������ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='�������������ɽ�����ޤ���'
            >�����������(��)</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("jyoho-kanri-saisoku14.01.01.pdf", "")'
                onMouseover="status='�������������§��ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='�������������§��ɽ�����ޤ���'
            >�������������§(��)</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("kiki-kanri15.04.01.pdf", "")'
                onMouseover="status='������������ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='������������ɽ�����ޤ���'
            >����������(��)</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("nitto-naibu-torihiki20.03.10.pdf", "")'
                onMouseover="status='���칩�殺�롼�������Լ������������ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='���칩�殺�롼�������Լ������������ɽ�����ޤ���'
            >���칩�殺�롼�������Լ����������(��)</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("kojin-jyoho-hogo17.10.23.pdf", "")'
                onMouseover="status='�Ŀ;����ݸ����ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='�Ŀ;����ݸ����ɽ�����ޤ���'
            >�Ŀ;����ݸ��(��)</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("tokutei-kojinjyoho-tori17.05.11.pdf", "")'
                onMouseover="status='����Ŀ;���谷������ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='����Ŀ;���谷������ɽ�����ޤ���'
            >����Ŀ;���谷����(��)</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("kenko-jyoho-tori21.04.01.pdf", "")'
                onMouseover="status='�򹯾������μ谷������ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='�򹯾������μ谷������ɽ�����ޤ���'
            >�򹯾������μ谷����</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout' id='start' colspan='2'>
        �����������ݶ�̳�����ݿͻ�ϫ̳�ط��ε���
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("syuugyou21.04.01.pdf", "")'
                onMouseover="status='���ȵ�§��ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='���ȵ�§��ɽ�����ޤ���'
            >���ȵ�§</a>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("syuugyou-part21.04.01.pdf", "")'
                onMouseover="status='�ѡ��ȥ����ޡ����ȵ�§(����)��ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='�ѡ��ȥ����ޡ����ȵ�§(����)��ɽ�����ޤ���'
            >�ѡ��ȥ����ޡ����ȵ�§(����)</a>
            &nbsp;
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("syuugyou-besshi.pdf", "")'
                onMouseover="status='���ȵ�§���̻��ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='���ȵ�§���̻��ɽ�����ޤ���'
            >�̻�</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("koutaikinmu-unyo-saisoku20.10.01.pdf", "")'
                onMouseover="status='�����̳���Ѻ�§��ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='�����̳���Ѻ�§��ɽ�����ޤ���'
            >�����̳���Ѻ�§</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("chingin21.04.01.pdf", "")'
                onMouseover="status='�¶⵬����ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='�¶⵬����ɽ�����ޤ���'
            >�¶⵬��</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("shoteate-saisoku21.04.01.pdf", "")'
                onMouseover="status='��������§��ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='��������§��ɽ�����ޤ���'
            >��������§</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("kakutei-nenkin16.10.01.pdf", "")'
                onMouseover="status='���󷿳�����մ��ǯ�⵬���ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='���󷿳�����մ��ǯ�⵬���ɽ�����ޤ���'
            >���󷿳�����մ��ǯ�⵬��</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("shikaku-toukyu21.04.01.pdf", "")'
                onMouseover="status='����������ٵ�����ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='����������ٵ�����ɽ�����ޤ���'
            >����������ٵ���</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("kokunaitenkin-funinsha20.10.01.pdf", "")'
                onMouseover="status='����ž�С���Ǥ�Ԥμ谷���˴ؤ��뵬����ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='����ž�С���Ǥ�Ԥμ谷���˴ؤ��뵬����ɽ�����ޤ���'
            >����ž�С���Ǥ�Ԥμ谷���˴ؤ��뵬��</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("jyuutaku-yuushi20.10.01.pdf", "")'
                onMouseover="status='����ͻ������ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='����ͻ������ɽ�����ޤ���'
            >����ͻ����</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("ippan-kashituke20.10.01.pdf", "")'
                onMouseover="status='�������յ�����ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='�������յ�����ɽ�����ޤ���'
            >�������յ���</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("kyouiku-shikin20.10.01.pdf", "")'
                onMouseover="status='���������յ�����ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='���������յ�����ɽ�����ޤ���'
            >���������յ���</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("ikuji-kyuugyou21.04.01.pdf", "")'
                onMouseover="status='����ٶȵ�����ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='����ٶȵ�����ɽ�����ޤ���'
            >����ٶȵ���</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("kaigo-kyuugyou21.04.01.pdf", "")'
                onMouseover="status='���ٶȵ�����ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='���ٶȵ�����ɽ�����ޤ���'
            >���ٶȵ���</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("sinnya-menjyo20.10.01.pdf", "")'
                onMouseover="status='������Ƚ�������ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='������Ƚ�������ɽ�����ޤ���'
            >������Ƚ�����</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("gyomujyosaigai-hosyo20.10.01.pdf", "")'
                onMouseover="status='��̳��ҳ�ˡ�곰���������ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='��̳��ҳ�ˡ�곰���������ɽ�����ޤ���'
            >��̳��ҳ�ˡ�곰�������</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("bosei-kenkokanri21.04.01.pdf", "")'
                onMouseover="status='�����򹯴��������֤˴ؤ��뵬���ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='�����򹯴��������֤˴ؤ��뵬���ɽ�����ޤ���'
            >�����򹯴��������֤˴ؤ��뵬��</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("einen-kinzoku21.04.01.pdf", "")'
                onMouseover="status='��ǯ��³��ɽ��������ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='��ǯ��³��ɽ��������ɽ�����ޤ���'
            >��ǯ��³��ɽ������</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("shokutaku21.04.01.pdf", "")'
                onMouseover="status='����������ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='����������ɽ�����ޤ���'
            >��������</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("shukkou21.04.01.pdf", "")'
                onMouseover="status='�и�������ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='�и�������ɽ�����ޤ���'
            >�и�����</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("shataku-toriatsukai20.10.01.pdf", "")'
                onMouseover="status='����谷������ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='����谷������ɽ�����ޤ���'
            >����谷����</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("kaigaishuchou-saigaihosyou20.10.01.pdf", "")'
                onMouseover="status='������ĥ���ˤ�����ҳ����������ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='������ĥ���ˤ�����ҳ����������ɽ�����ޤ���'
            >������ĥ���ˤ�����ҳ��������</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("teinengo-saikoyou21.04.01.pdf", "")'
                onMouseover="status='��ǯ��Ƹ��Ѥη���Ұ�������ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='��ǯ��Ƹ��Ѥη���Ұ�������ɽ�����ޤ���'
            >��ǯ��Ƹ��Ѥη���Ұ�����</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("harasu-boushi20.04.01.pdf", "")'
                onMouseover="status='�ϥ饹�����ɻߵ�����ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='�ϥ饹�����ɻߵ�����ɽ�����ޤ���'
            >�ϥ饹�����ɻߵ���(��)</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout' id='start' colspan='2'>
        �����������ݶ�̳�����ݷ����ط��ε���
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("keiri20.10.01.pdf", "")'
                onMouseover="status='����������ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='����������ɽ�����ޤ���'
            >��������</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("suitou20.10.01.pdf", "")'
                onMouseover="status='��Ǽ������ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='��Ǽ������ɽ�����ޤ���'
            >��Ǽ����</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("tegata-yukashoken20.10.01.pdf", "")'
                onMouseover="status='�����ͭ���ڷ�����������ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='�����ͭ���ڷ�����������ɽ�����ޤ���'
            >�����ͭ���ڷ���������</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("genka-keisan20.10.01.pdf", "")'
                onMouseover="status='�����׻�������ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='�����׻�������ɽ�����ޤ���'
            >�����׻�����</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("tanaoroshishisan-kannri20.10.01.pdf", "")'
                onMouseover="status='ê���񻺴���������ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='ê���񻺴���������ɽ�����ޤ���'
            >ê���񻺴�������</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("koteishisan-kanri20.10.01.pdf", "")'
                onMouseover="status='����񻺴���������ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='����񻺴���������ɽ�����ޤ���'
            >����񻺴�������</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout' id='start' colspan='2'>
        �����������ݶ�̳�����ݻ��ط��ε���
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("seisan-kanri20.10.01.pdf", "")'
                onMouseover="status='��������������ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='��������������ɽ�����ޤ���'
            >������������</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("koubai-kanri20.10.01.pdf", "")'
                onMouseover="status='�������������ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='�������������ɽ�����ޤ���'
            >�����������</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("green-chotatsu-iinkai14.01.01.pdf", "")'
                onMouseover="status='���꡼��Ĵã�Ѱ���§(��§)��ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='���꡼��Ĵã�Ѱ���§(��§)��ɽ�����ޤ���'
            >���꡼��Ĵã�Ѱ���§(��§)(��)</a>
        </td>
        <td class='layout'>
            ��
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout' id='start' colspan='2'>
        �����������ݶ�̳�����ݳ�ȯ�ط��ε���
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("hatsumei-toriatukai20.02.07.pdf", "")'
                onMouseover="status='ȯ���谷������ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='ȯ���谷������ɽ�����ޤ���'
            >ȯ���谷����(��)</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("hatsumei-toriatukai-saisoku16.07.19.pdf", "")'
                onMouseover="status='ȯ���谷������§��ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='ȯ���谷������§��ɽ�����ޤ���'
            >ȯ���谷������§(��)</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout' id='start' colspan='2'>
        �����ܵ���
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("mochikabu-kiyaku19.09.13.pdf", "")'
                onMouseover="status='���칩�ｾ�Ȱ����������ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='���칩�ｾ�Ȱ����������ɽ�����ޤ���'
            >���칩�ｾ�Ȱ���������(��)</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("mochikabu-unei19.09.13.pdf", "")'
                onMouseover="status='���칩�ｾ�Ȱ������񱿱ĺ�§��ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='���칩�ｾ�Ȱ������񱿱ĺ�§��ɽ�����ޤ���'
            >���칩�ｾ�Ȱ������񱿱ĺ�§(��)</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout' id='start' colspan='2'>
        ������¾
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("gasoline-suii.pdf", "")'
                onMouseover="status='�������ñ�����ɽ��ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='�������ñ�����ɽ��ɽ�����ޤ���'
            >�������ñ�����ɽ</a>
         <td class='layout'>
            <a href='internal_control/regulation_inter_menu.php'>����������Ϣ</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("mycar-gasoline-suii.pdf", "")'
                onMouseover="status='�ޥ�������̳���ѻ��Υ������ñ�����ɽ��ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='�ޥ�������̳���ѻ��Υ������ñ�����ɽ��ɽ�����ޤ���'
            >�ޥ�������̳���ѻ��������ñ�����ɽ</a>
        </td>
         <td class='layout'>
            ��
        </td>
    </tr>
    </table>
    </center>
</body>
<?=$menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
