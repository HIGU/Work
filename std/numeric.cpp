#include <iostream>
#include <numeric>
#include <vector>

//コンパイラのバージョンの問題でgcdとlcmは動かないかも
//C++17以上らしい

int main(void){
    // 最小公倍数を求める
    // 数が大きいとオーバーフローする場合がある
    long long a = std::lcm(3LL, 5LL);

    //最大公約数
    //ユークリッドの互除法
    //計算量は引数の小さいほうをnとしたときO(log n)
    int max_num = std::gcd(12, 15);

    // 3個以上の数で最大公約数を求める場合
    std::vector<int> vec = {120, 84, 42, 27};
    int last = vec[0];
    for(int i = 1; i < (int)vec.size(); ++i){
        last = std::gcd(last, vec[i]);
    }
    
    return 0;
}