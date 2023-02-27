#include <iostream>
#include <iomanip>
#include <sstream>

int main(void){
    float f = 3.14159265;
    // 整数部も含めた3桁表示, 3.14
    std::cout << std::setprecision(3) << f << std::endl;
    // 小数部が3桁表示, 3.142
    std::cout << std::fixed << std::setprecision(3) << f << std::endl;

    //int -> 16進数の文字列
    std::stringstream ss;
    //12を16進数で2桁表示、足りない部分は0で埋める、
    ss << std::setfill('0') << std::setw(2) << std::hex << 12;
    // 0c
    std::cout << ss.str() << std::endl;

    return 0;
}