#include <stdio.h>
#include <stdlib.h>
#include <string.h>

typedef struct{
    int test1;
    int test2;
}tests;

tests template;

void c_out(char *buf, int size)
{
    int i;
    short ret;

    for(i=0; i < size; ++i){
        printf("%c", buf[i]);
        if(buf[i] == '\0'){
            break;
        }
    }
    printf("\n");

}

void check_start(char data, int flag, char *start)
{
	//static char start[5];
	char check[5] = {'s', 't', 'a', 'r', 't'};
	int i;
	
	if(data == 's'){
		start[0] = 's';
        printf("s is inputted\n");
	}
    else if((start[0] == 's') && (start[1] == 't') && (start[2] == 'a') && (start[3] == 'r') && (data == 't')){
		start[4] = 't';
        printf("t2 is inputted\n");
	}
	else if((start[0] == 's') && (data == 't')){
		start[1] = 't';
        printf("t is inputted\n");
	}
	else if((start[0] == 's') && (start[1] == 't') && (data == 'a')){
		start[2] = 'a';
        printf("a is inputted\n");
	}
	else if((start[0] == 's') && (start[1] == 't') && (start[2] == 'a') && (data == 'r')){
		start[3] = 'r';
        printf("r is inputted\n");
	}
	else{
		for(i = 0; i < sizeof(start); ++i){
			start[i] = '\0';
		}
	}
	
	if(memcmp(start, check, sizeof(check)) == 0){
		flag = 0;
        printf("mecmp is OK\n");
	}
	else{
		flag = 1;
        printf("mecmp is NG\n");
	}
}

int main(int argc, char const *argv[])
{
    char data;
    char buf[256];
    char text;
    char start[5];
    int flag;
    while(1){
        scanf("%c", &text);
        check_start(text, flag, start);
    }
    //check_start(start, flag);

    sprintf(buf, "%Cが入力されました\n", data);
    c_out(buf, sizeof(buf));

    return 0;
}

