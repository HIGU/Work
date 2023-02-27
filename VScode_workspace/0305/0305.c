#include <stdio.h>

int main(void)
{
    FILE *s_fp, *d_fp;
    char *source_filename = "rx1_2_demodulated.bin";
    char *destination_filename = "rx1_2_demodulated_out.txt";
    int i;
    unsigned int out;
    unsigned short x, y;
    char z = '\n';

    /* 出力元ファイルのopen */
    if ((s_fp = fopen(source_filename, "rb")) == NULL) {
        //fprintf(stderr, "%sのオープンに失敗しました.\n", source_filename);
        printf("ファイルのオープンに失敗しました\n");
        return 0;
    }

    /* 出力先ファイルのopen */
    if ((d_fp = fopen(destination_filename, "w+")) == NULL) {
        ///fprintf(stderr, "%sのオープンに失敗しました.\n", destination_filename);
        printf("ファイルのオープンに失敗しました\n");
        return 0;
    }

    for ( i = 0; i < 47; i++ ) {//サイズは決め打ち
        fread(&out, 2, 1, s_fp);
        //x = (out & 0x8000) >> 15;
        x = (out>>15) & 1;
        //y = (out & 0x0800) >> 11;
        y = (out>>11) & 1;
        fprintf(d_fp, "%d%d\n", x, y);
    }

    /* ファイルのクローズ */
    fclose(s_fp);
    fclose(d_fp);

    return 0;
}