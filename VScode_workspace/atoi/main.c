/****************************************************************************/
/*																			*/
/*		AP-SH2A-4A �T���v���v���O����										*/
/*			���C������														*/
/*																			*/
/*			Copyright   :: ���A���t�@�v���W�F�N�g							*/
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
/*	���C���֐�						*/
/************************************/
int main(void)
{
	short data;
	char can_data;
	char	buf[256];

	sci_init(38400, 8, 0, 1);							/* SCI(SCIF)������ */
	cmt0_init(5);										/* CMT0������ */
	cmt1_init(10);										/* CMT1������ */
	rcan_init();										/* CAN������ */
#ifdef ETHER_SAMPLE
	ether_init();										/*	�C�[�T�l�b�g������	*/
#endif
#ifdef USBF_SAMPLE
	Usbf_init();										/*	USB�t�@���N�V����������		*/
#endif
	set_imask(0);										/* ���荞�݃}�X�N���x��(0)�̎w�� */

	while(1)
	{
		/* �V���A���G�R�[�o�b�N�̏��� */
		data = sci_getc();								/*�P������M */
		if (0 <= data)
		{
			sci_putc((unsigned char)data);				/*�P�������M */
			sprintf(buf, " %c �����͂���܂���\r\n", data);
			console_out(buf, strlen(buf));
			
		}

		/* CAN�G�R�[�o�b�N�̏��� */
		if(get_can_get_count() > 0) {
			can_data = get_can_data();					/* CAN��M�f�[�^�擾 */
			dataf_tx(can_data);							/* �f�[�^���M */
		}

#ifdef ETHER_SAMPLE
		/*	�C�[�T�l�b�g����	*/
		ether_main();
#endif

#ifdef USBF_SAMPLE
		/*	USB�t�@���N�V�����iUSB�V���A���j����	*/
		Usbf_main();
#endif

	}

	return 0;
}

void console_out(char * buf, int len)
{
	int	i;
	for(i = 0; i < len; i++){
		sci_putc((unsigned char)buf[i]);				/*�P�������M */
	}
}