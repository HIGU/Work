#include <iostream>
#include <bitset>

int main(void){
    // 例 1: 長さ 250000 の bitset（250000 桁の 2 進数だと思ってよい）を定義する。
    std::bitset<250000> bs1;

    // 例 2: 長さ 8 の bitset を定義する。整数から初期化を行う。
    std::bitset<8> bs2(131); // 7 ビット目から 0 ビット目への順番で、10000011 となる。

    // 例 3: 長さ 8 の bitset を定義する。2 進数から初期化を行う。
    std::bitset<8> bs3("10000011"); // 7 ビット目から 0 ビット目への順番で、10000011 となる。

    // 例 4: 例 3 とやってることは変わらない。ただ bitset の長さが増えただけ。
    std::bitset<2000> bs4("10000011"); // 1999 ビット目から 0 ビット目の順番で、0...010000011 となる。

    std::cout << bs2.count() << std::endl;//bitが立っている数を数える、3
    bs2.set(2);//10000011 -> 10000111、3桁目を1にする
    bs2.reset(0);//10000111 -> 10000110、1桁目を0にする

    for(int i = 7; i >= 0; i++)
    {
        if(bs2[i] == 1)
        {
            std::cout << "1";
        }
        else if(bs2[i] == 0)
        {
            std::cout << "0";
        }
    }
    std::cout << std::endl;

    return 0;
}