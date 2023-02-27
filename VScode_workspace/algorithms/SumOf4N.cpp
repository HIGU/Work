#include <iostream>

//4^0 + 4^1 + ... + 4^Nを1000000007で割った答えを出力
//繰り返し2乗法を使う。
//1 <= N <= 10^18

const long long MOD = 1000000007LL;

long long ModPow(long long a, long long b){
    long long p = a;
    long long ans = 1LL;
    //2^60 > 10^18
    for(int i = 0; i < 61; ++i){
        if((b & (1LL << i)) != 0){
            ans *= p;
            ans %= MOD;
        }
        p *= p;
        p %= MOD;
    }
    return ans;
}

int main() {
    long long N;
    std::cin >> N;

    long long ans = ModPow(4LL, N+1LL) - 1LL;
    long long ans_ = (ans * ModPow(3LL, MOD-2LL)) % MOD;
    std::cout << ans_ << std::endl;

    return 0;
}