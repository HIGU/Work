#ifndef TI_OPT_AES_H_
#define TI_OPT_AES_H_

void aes_enc_dec(unsigned char *state, unsigned char *key, unsigned char dir);
#define DIR_ENCODE	0
#define DIR_DECODE	1
#endif /* TI_OPT_AES_H_ */
