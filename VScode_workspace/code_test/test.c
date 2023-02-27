#include <stdio.h>
//#include <iostream>
// #include <windows.h>
// #include <Shlobj.h>

//#define INT_MIN         (-INT_MAX-1)     /* MIN VALUE FOR INT */
//#define INT_MAX         2147483647       /* MAX VALUE FOR INT */

// static int saturateInt40toInt32(__int64_t input) {
//     if (input < INT_MIN) {
//         printf("exchanged to INT_MIN\n");
//         return INT_MIN;
//     } else if (input > INT_MAX) {
//         printf("exchanged to INT_MAX\n");
//         return INT_MAX;
//     } else {
//         return (int)input;
//     }
// }

int main(void)
{
    char character = (char)-1;
    int integer = -1024;
    short s = -1024;
    printf("%x\n", character);
    printf("%x\n", integer);
    printf("%x\n", s);
    //__int64_t a = 21474836470;
    //int b = 0xFFFF;
    //int c;

    //c = saturateInt40toInt32(a) >> 16;

    //SHCreateDirectory(NULL, "c:\\User\\Test");

    //printf("c = 0x%x\n", c);
    return 0;
}