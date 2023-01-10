#include <iostream>
#include <iomanip>

int main(void){
    double aaa = 1.0;

    //小数点以下の桁数を設定できるようにする
    std::cout << std::fixed;
    // 小数点以下を10桁表示
    std::cout << std::setprecision(10) << aaa << std::endl;
    return 0;
}