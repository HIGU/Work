#include <bits/stdc++.h>

struct Test{
    int aaa;
    int bbb;
};

void TestFunction(Test &a){
    if(&a == nullptr){
        std::cout << "Error" << std::endl;
    }
}

int main(){
    Test aaa;
    TestFunction(aaa);
    std::cout << "Hello World" << std::endl;
    return 0;
}