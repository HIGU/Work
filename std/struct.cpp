#include <iostream>

struct A{
    int a;
    int b;
    int c;

    // コンストラクタ
    // 作成時に初期化される
    // 変数がconstの場合はこの方法である必要がある
    A(        
    ) :
    a(10),
    b(100),
    c(-10)
    {
    }
};

struct B{
    int a;
    int b;
    int c;

    // コンストラクタ
    // Aの方法とは違い、作成後に代入を行う
    B()
    {
        a = 10;
        b = 20;
        c = 30;
    }
};

int main(void){
    return 0;
}