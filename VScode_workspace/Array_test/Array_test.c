#include <stdio.h>

int main(void)
{
    int Array[10] = {0,1,2,3,4,5,6,7,8,9};
    int array[3] = {1,2,3};

    memcpy(&Array[5], array, sizeof(array));

    for(int i; i < 10; ++i){
        printf("Array = %d\n", Array[i]);
    }
}