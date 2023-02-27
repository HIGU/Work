#include <stdio.h>
#include <string.h>
#include <stdlib.h>

int main(void)
{
    int i;
    FILE *fp, *fp2;
    char *filename = "sine_wave.bin";
    char *filename_2 = "test.csv";
    int p[1536];
    char text = ',';

    /* ファイルのオープン */
    if ((fp = fopen(filename, "rb")) == NULL) {
        fprintf(stderr, "%sのオープンに失敗\n",
                filename);
        return 1;
    }

        for ( i = 0; i < 1536; i++ ) {
        fread(&p[i], 1 ,1 ,fp);
        //p[i] = '\0';
    }

        /* ファイルのオープン */
    if ((fp2 = fopen(filename_2, "Wb")) == NULL) {
        fprintf(stderr, "%sのオープンに失敗\n",
                filename);
        return 1;
    }

    for(i = 0; i < 1536; ++i){
        fwrite(&p[i], 1, 1, fp2);
        fwrite(&text, 1, 1, fp2);
    }

    fclose(fp2);
    fclose(fp);
}