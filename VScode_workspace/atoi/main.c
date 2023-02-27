/****************************************************************************/
/*																			*/
/*		AP-SH2A-4A サンプルプログラム										*/
/*			メイン処理														*/
/*																			*/
/*			Copyright   :: ㈱アルファプロジェクト							*/
/*			Cpu         :: SH7216											*/
/*			Language    :: SH-C,GCC											*/
/*			File Name   :: main.c											*/
/*																			*/
/****************************************************************************/
#include <machine.h>
#include  "7216.h"
#include "common.h"
#include <stdio.h>
#include <stdlib.h>

void console_out(char * buf, int len);
/************************************/
/*	メイン関数						*/
/************************************/
int main(void)
{
	short data;
	char can_data;
	char	buf[256];

	sci_init(38400, 8, 0, 1);							/* SCI(SCIF)初期化 */
	cmt0_init(5);										/* CMT0初期化 */
	cmt1_init(10);										/* CMT1初期化 */
	rcan_init();										/* CAN初期化 */
#ifdef ETHER_SAMPLE
	ether_init();										/*	イーサネット初期化	*/
#endif
#ifdef USBF_SAMPLE
	Usbf_init();										/*	USBファンクション初期化		*/
#endif
	set_imask(0);										/* 割り込みマスクレベル(0)の指定 */

	while(1)
	{
		/* シリアルエコーバックの処理 */
		data = sci_getc();								/*１文字受信 */
		if (0 <= data)
		{
			sci_putc((unsigned char)data);				/*１文字送信 */
			sprintf(buf, " %c が入力されました\r\n", data);
			console_out(buf, strlen(buf));
			
		}

		/* CANエコーバックの処理 */
		if(get_can_get_count() > 0) {
			can_data = get_can_data();					/* CAN受信データ取得 */
			dataf_tx(can_data);							/* データ送信 */
		}

#ifdef ETHER_SAMPLE
		/*	イーサネット処理	*/
		ether_main();
#endif

#ifdef USBF_SAMPLE
		/*	USBファンクション（USBシリアル）処理	*/
		Usbf_main();
#endif

	}

	return 0;
}

void console_out(char * buf, int len)
{
	int	i;
	for(i = 0; i < len; i++){
		sci_putc((unsigned char)buf[i]);				/*１文字送信 */
	}
}