#include <iostream>
#include <vector>
#include <algorithm>
using namespace std;

/*
    Nこの品物があって、i番目の品物の重さはwi,価値はviで与えられている。
    このN個の品物から「重さの総和がWを超えないように」いくつか選びます。
    このとき選んだ品物の価値の最大値を求めよ。
*/
int main(){
    int N;
    cin >> N;

    int W;
    cin >> W;

    long long goods[110][2];
    for(int idx = 0; idx < N; idx++){
        cin >> goods[idx][0] >> goods[idx][1];
    }

    long long dp[110][10010];
    for(int i = 0; i < N; i++){
        for(int j = 0; j < W; j++){
            if(j - goods[i][0] >= 0){
                dp[i + 1][j] = max(dp[i+1][j], dp[i][j-goods[i][0]] + goods[i][1]);
            }
            dp[i+1][j] = max(dp[i+1][j], dp[i][j]);
        }
    }

    cout << dp[N][W] << endl;
    return 0;
}