#include "stdio.h"

int main(void)
{
    int array_one[8];
    int array_two[8][8];
    int *middle;
    int i, j;

    for(i = 0; i < 8; ++i){
        for(j = 0; j < 8; ++j){
            array_two[i][j] = j;
        }
        array_one[i] = i+8;
    }

    middle = &array_one[0];

    for(i = 0; i < 8; ++i){
        *middle++ = array_two[0][0]++;
        printf("%d\n", array_one[i]);
    }

    return 0;
}