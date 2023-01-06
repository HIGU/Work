#include <iostream>
#include <string>
#include <vector>
using namespace std;

#if 0
int main(){
    string S;
    cin >> S;

    string chokudai = "chokudai";

    long long answer = 0;
    while(1){
        int index = 0;
        auto itr1 = S.find(chokudai[0], index);
        if(itr1 == string::npos){
            break;
        }
        index = itr1;
        while(1){
            auto itr2 = S.find(chokudai[1], index);
            if(itr2 == string::npos){
                break;
            }
            index = itr2;
            while(1){
                auto itr3 = S.find(chokudai[2], index);
                if(itr3 == string::npos){
                    break;
                }
                index = itr3;
                while(1){
                    auto itr4 = S.find(chokudai[3], index);
                    if(itr4 == string::npos){
                        break;
                    }
                    index = itr4;
                    while(1){
                        auto itr5 = S.find(chokudai[4], index);
                        if(itr5 == string::npos){
                            break;
                        }
                        index = itr5;
                        while(1){
                            auto itr6 = S.find(chokudai[5], index);
                            if(itr6 == string::npos){
                                break;
                            }
                            index = itr6;
                            while(1){
                                auto itr7 = S.find(chokudai[6], index);
                                if(itr7 == string::npos){
                                    break;
                                }
                                index = itr7;
                                while(1){
                                    auto itr8 = S.find(chokudai[7], index);
                                    if(itr8 == string::npos){
                                        break;
                                    }
                                    index = itr8;
                                    answer += 1;
                                }
                                index = itr7;
                            }
                            index = itr6;
                        }
                        index = itr5;
                    }
                    index = itr4;
                }
                index = itr3;
            }
            index = itr2;
        }
        index = itr1;
    }

    std::cout << answer % 1000000007 << endl;

    return 0;
}
#endif

#define rep(i,n) for (int i = 0; i < (n); ++i)
 
int main() {
  string s;
  cin >> s;
  int n = s.size();
  vector dp(n+1, vector<int>(9));
  rep(i,n+1) dp[i][0] = 1;
  const int mod = 1000000007;
  string t = "chokudai";
  rep(i,n)rep(j,8) {
    if (s[i] != t[j]) {
      dp[i+1][j+1] = dp[i][j+1];
    } else {
      dp[i+1][j+1] = (dp[i][j+1] + dp[i][j]) % mod;
    }
  }
  cout << dp[n][8] << endl;
  return 0;
}