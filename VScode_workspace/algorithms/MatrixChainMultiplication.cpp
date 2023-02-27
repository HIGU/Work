#include <iostream>
#include <vector>
#include <algorithm>

//n個の行列が与えられたときの連鎖行列積問題
//dp[j][i]: jからi個めまでの最小の連鎖行列積
//[j~kまでの乗数]+[k+1~i+jまでの乗数]+[j~kとj+1~i+jの計算でできた行列の乗数]がdpの値

int main(void){
    int n;
    std::cin >> n;
    std::vector<std::pair<int, int>> M(n);
    for(int i = 0; i < n; ++i) std::cin >> M[i].first >> M[i].second;

    std::vector<std::vector<int>> dp(n, std::vector<int>(n, INT_MAX));
    for(int i = 0; i < n; ++i) dp[i][0] = 0;
    for(int i = 1; i < n; ++i){
        for(int j = 0; j < n - i; ++j){
            int rc = M[j].first * M[j+i].second;
            for(int k = 1; k <= i; ++k){
                int tmp = dp[j][k-1] + dp[j+k][i-k];
                tmp += rc * M[j+k].first;
                if(dp[j][i] > tmp){
                    dp[j][i] = tmp;
                }
            }
        }
    }
    std::cout << dp[0][n-1] << std::endl;
    return 0;
}