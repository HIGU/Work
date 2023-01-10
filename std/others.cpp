#include <iostream>
#include <iomanip>

int main(void){
    float f = 3.14159265;
    // 整数部も含めた3桁表示
    std::cout << std::setprecision(3) << f << std::endl;
    // 小数部が3桁表示
    std::cout << std::fixed << std::setprecision(3) << f << std::endl;

    return 0;
}