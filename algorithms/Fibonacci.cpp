#include <iostream>
#include <vector>

//フィボナッチ数列の第N項を求め、10^9で割った余りを出力する。
//1 <= N <= 10^18
//pは|n項目、n-1項目　|の行列、ただしnは2の累乗
//   |n-1項目、n-2項目|

const long long MOD = 1000000000LL;

std::vector<std::vector<long long>> Mult(
    std::vector<std::vector<long long>> a,
    std::vector<std::vector<long long>> b
){
    std::vector<std::vector<long long>> c(2, std::vector<long long>(2, 0));
    for(int i = 0; i < 2; ++i){
        for(int j = 0; j < 2; ++j){
            for(int k = 0; k < 2; ++k){
                c[i][k] += a[i][j] * b[j][k];
                c[i][k] %= MOD;
            }
        }
    }

    return c;
}

std::vector<std::vector<long long>> Power(
    std::vector<std::vector<long long>> a,
    long long n
){
    std::vector<std::vector<long long>> p = a;
    std::vector<std::vector<long long>> res(2, std::vector<long long>(2, 0));
    res[0][0] = 1;
    res[1][1] = 1;
    for(int i = 0; i < 60; ++i){
        std::cout << "-----" << std::endl;
        std::cout << p[0][0] << ", " << p[0][1] << std::endl;
        std::cout << p[1][0] << ", " << p[1][1] << std::endl;
        if((n & (1LL << i)) != 0LL) res = Mult(res, p);
        p = Mult(p, p);
    }

    return res;
}

int main() {
    long long N;
    std::cin >> N;

    std::vector<std::vector<long long>> a(2, std::vector<long long>(2, 0));
    a[0][0] = 1;
    a[0][1] = 1;
    a[1][0] = 1;
    a[1][1] = 0;
    std::vector<std::vector<long long>> b = Power(a, N-1LL);

    std::cout << (b[1][0] + b[1][1]) % MOD << std::endl;

    return 0;
}