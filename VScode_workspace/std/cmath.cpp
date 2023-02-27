#include <iostream>
#include <cmath>

int main(void){
    float num1 = 0.5f;
    float rounded_num1 = std::round(num1);//小数点第1位を四捨五入する、1.f
    num1 = -0.95f;
    rounded_num1 = std::round(num1);//-1.f

    float num2 = 0.5f;
    float floored_num2 = std::floor(num2);//小数点第1位を切り捨て、0.f
    
    // √xの値を返す
    double a = std::sqrt(5.0);
    // 3√xの値を返す, 8 -> 2^3 -> 3
    double b = std::cbrt(8.0);

    // べき乗を計算する
    // 2の3乗
    double num3 = std::pow(2, 3);
    return 0;
}