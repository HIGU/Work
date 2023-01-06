#include <iostream>
#include <vector>
using namespace std;

int main(){
    int N, X, Y;
    cin >> N >> X >> Y;

    vector<int> A, B;
    A.resize(N);
    B.resize(N);
    for(int i = 0; i < N; i++){
        int A_, B_;
        cin >> A_ >> B_;
        A.emplace_back(A_);
        B.emplace_back(B_);
    }

    int dp[301][301][301];
    for(int idx = 0; idx < 301; ++idx){
        if(idx == 0){
            dp[idx];
        }
    }
    return 0;
}