#include <iostream>
#include <string>
#include <vector>

/*
0以上N以下の整数で、いずれかの桁に3を含むものの個数を求めよ
*/

int main(void){
    //桁数が大きい場合があるため、文字列にする
    std::string N;
    std::cin >> N;

    std::vector<int> n;
    for(auto a : N){
        n.push_back(a-'0');
    }
    int l = N.size();

    std::vector<std::vector<std::vector<int>>> dp(100, std::vector<std::vector<int>>(2, std::vector<int>(2)));
    dp[0][0][0] = 1;
    for(int i = 0; i < l; ++i){
        for(int smaller = 0; smaller < 2; ++smaller){
            for(int j = 0; j < 2; ++j){
                for(int x = 0; x <= (smaller ? 9 : n[i]); ++x){
                    dp[i+1][smaller || x < n[i]][j || x == 3] += dp[i][smaller][j];
                }
            }
        }
    }
    std::cout << dp[l][0][1] + dp[l][1][1] << std::endl;

    return 0;
}