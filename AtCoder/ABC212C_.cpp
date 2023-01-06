#include <iostream>
#include <vector>
#include <algorithm>
using namespace std;

int main(){
    int N, M;
    cin >> N >> M;

    vector<int> A;
    for(int idx = 0;idx < N; idx++){
        int a;
        cin >> a;
        A.emplace_back(a);
    }

    vector<int> B;
    for(int idx = 0;idx < M; idx++){
        int b;
        cin >> b;
        B.emplace_back(b);
    }

    sort(A.begin(), A.end());
    A.erase(unique(A.begin(), A.end()), A.end());
    sort(B.begin(), B.end());
    B.erase(unique(B.begin(), B.end()), B.end());

    int min_sub = INT_MAX;
    int last_index = 0;
    for(int i = 0; i < N; i++){
        int min_sub_ = INT_MAX;
        for(int j = last_index; j < M; j++){
            if(std::abs(A[i] - B[j]) <= min_sub_){
                min_sub_  = std::abs(A[i] - B[j]);
            }
            else{
                last_index = j - 1;
                break;
            }
        }

        if(min_sub_ < min_sub){
            min_sub = min_sub_;
        }
    }

    cout << min_sub << endl;

    return 0;
}