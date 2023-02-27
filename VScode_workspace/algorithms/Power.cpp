#include <iostream>
#include <vector>

//aのb乗を100000007で割った余りを表示
//2 <= a <= 100, 1 <= b <= 10^9
//2の累乗の乗算でa^bを求める（2^7 -> 2^4 * 2^2 * 2^1）、繰り返し2乗法

int main() {
    const long long mod = 1000000007LL;
    long long a, b;
    std::cin >> a >> b;

    long long mult = a;
    long long ans = 1LL;
    for(int i = 0; i < 60; ++i){
        if((b & (1LL << i)) != 0) {
            ans *= mult;
            ans %= mod;
        }
        mult *= mult;
        mult %= mod;
    }

    std::cout << ans << std::endl;

    return 0;
}