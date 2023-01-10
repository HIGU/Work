#include <iostream>
#include <vector>
#include <map>

//整数Nが入力されされたときの、Σ(1<=K<=N)K*f(K)を求める
//ただしf(N)はNの約数の個数
//1 <= N <= 10^7

std::vector<int> spf;

void SpfBuild(int n){
    spf.resize(n+1);
    for(int i = 0; i <= n; ++i) spf[i] = i;
    for(int i = 2; i * i <= n; ++i){
        if(spf[i] != i) continue;
        for(int j = i * i; j <= n; j += i){
            if(spf[j] == j) spf[j] = i;
        }
    }
}

long long PrimeNum(int x){
    //mp: どの整数が何個あるか
    std::map<int, int> mp;
    while(x != 1){
        mp[spf[x]]++;
        x /= spf[x];
    }
    long long num = 1;
    for(auto it = mp.begin(); it != mp.end(); ++it) num *= (long long)(it->second + 1);
    return num;
}

int main() {
    int N;
    std::cin >> N;
    SpfBuild(N);
    long long ans = 0;
    for(int i = 1; i <= N; ++i){
        long long num = PrimeNum(i);
        ans += num * i;
    }
    std::cout << ans << std::endl;
    return 0;
}