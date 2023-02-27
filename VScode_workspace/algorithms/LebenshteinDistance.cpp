#include <iostream>
#include <vector>
#include <string>
#include <algorithm>

//2つの文字列のレーベンシュタイン距離を求める
//編集は、追加・削除・置換の3種
//文字列の長さをそれぞれS,Tとしたとき、O(ST)

int main(void){
    std::string S, T;
    std::cin >> S >> T;
    const int s_size = (int)S.length();
    const int t_size = (int)T.length();
    int max_ans = s_size;
    if(max_ans < t_size) max_ans = t_size;
    std::vector<std::vector<int>> dp(s_size+1, std::vector<int>(t_size+1, max_ans));
    for(int i = 0; i <= s_size; ++i) dp[i][0] = i;
    for(int i = 0; i <= t_size; ++i) dp[0][i] = i;
    for(int i = 1; i <= s_size; ++i){
        for(int j = 1; j <= t_size; ++j){
            dp[i][j] = (std::min)(dp[i][j], dp[i-1][j]+1);
            dp[i][j] = (std::min)(dp[i][j], dp[i][j-1]+1);
            if(S[i-1] == T[j-1]) dp[i][j] = (std::min)(dp[i][j], dp[i-1][j-1]);
            else dp[i][j] = (dp[i][j], dp[i-1][j-1]+1);
        }
    }
    std::cout << dp[s_size][t_size] << std::endl;
    return 0;
}