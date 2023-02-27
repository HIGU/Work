#include <bits/stdc++.h>
using namespace std;

int main() {
  const long long mod = 1000000007LL;
  string S;
  cin >> S;
  vector<vector<long long>> dp(S.length(), vector<long long>(13, 0));
  dp[0][0] = 1;
  for(int i = 0; i < S.length(); ++i){
    for(int j = 0; j < 13; ++j){
      if(S[i] == '?'){
        for(int k = 0; k < 10; ++k){
          //
        }
      }
      else{
        int num = S[i] - '0';
        //num *= pow(10, i);
        int rest = ;
        dp[i][j];
      }
    }
  }
  return 0;
}