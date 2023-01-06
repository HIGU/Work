#include <iostream>
#include <vector>
using namespace std;

int main(){
    long long N;
    cin >> N;
    vector<int> A;
    A.resize(N, 0);
    for(int i = 0; i < N; i++){
        int n;
        cin >> n;
        A[i] = n;
    }

    long long dp[100005][10];
    for(int i = 0; i < 10;i++){
        dp[0][i] = 0;
    }
    for(int idx = 1; idx < N; idx++){
        for(int i = 0; i < 10; i++){
            if(i == (dp[idx - 1][i] + A[idx])%10){
                dp[idx][i] += dp[idx - 1][i] + 1;
            }
            if(i == (dp[idx - 1][i] * A[idx])%10){
                dp[idx][i] += dp[idx - 1][i] + 1;
            }
        }
    }

    for(int idx = 0; idx < 10; idx++){
        cout << dp[N - 1][idx] % 998244353 << endl;
    }

    return 0;
}