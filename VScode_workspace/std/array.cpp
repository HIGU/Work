#include <iostream>
#include <array>
#include <algorithm>

int main(void){
    // 要素数5の配列
    std::array<int, 5> ar1;
    // 全部の要素を10で埋める
    std::fill(ar1.begin(), ar1.end(), 10);

    return 0;
}