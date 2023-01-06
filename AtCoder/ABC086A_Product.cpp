#include <iostream>

//整数の積が奇数か偶数か判断するプログラム
int main(){
    int a, b;
    std::cin >> a >> b ;
    int result = a * b;

    if(result % 2) std::cout << "Even" << std::endl;
    else std::cout << "Odd" << std::endl;
}