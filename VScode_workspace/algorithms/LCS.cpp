#include <iostream>
#include <string>
#include <vector>
#include <algorithm>

//2つの文字列が渡されたときの最長共通列の長さを求める
//文字列の長さをそれぞれN, Mとしたとき、O(NM)かかる

int main(void){
    std::string X, Y;
    std::cin >> X >> Y;
    int N = X.length();
    int M = Y.length();
    std::vector<std::vector<int>> dp(N+1, std::vector<int>(M+1, 0));
    for(int i = 1; i <= N; ++i){
        for(int j = 1; j <= M; ++j){
            if(X[i-1] == Y[j-1]){
                dp[i][j] += dp[i-1][j-1] + 1;
            }
            else{
                dp[i][j] += (std::max)(dp[i][j-1], dp[i-1][j]);
            }
        }
    }
    std::cout << dp[N][M] << std::endl;

    return 0;
}