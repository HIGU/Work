#include <iostream>
#include <vector>
using namespace std;

int main() {
    int N, M;
    cin >> N >> M;
    vector<vector<long long>> B_list;
    bool is_ok = true;
    long long first = 0;
    int left = 0;
    for(int idx = 0; idx < N; idx++){
        for(int i = 0; i < M;i++){
            long long b;
            cin >> b;
            if(idx == 0 && i == 0){
                first = b;
                left = (first - 1) % 7 + 1;
            }
            else{
                if(left + i >= 8){
                    is_ok = false;
                }
                else if(b != first + i + idx * 7){
                    is_ok = false;
                }
            }
        }
    }

    if(is_ok){
        cout << "Yes" << endl;
    }
    else{
        cout << "No" << endl;
    }
    return 0;
}

/*
int main(){
    int N,M; cin >> N >> M;
    vector<vector<int>> B(N,vector<int>(M));
    for(int i=0; i<N; i++){
        for(int j=0; j<M; j++) cin >> B[i][j];
    }
    vector<vector<int>> x(N,vector<int>(M)),y(N,vector<int>(M));
    for(int i=0; i<N; i++){
        for(int j=0; j<M; j++){
            x[i][j] = (B[i][j]+6)/7;
            y[i][j] = (B[i][j]-1)%7+1;
        }
    }
    string ans = "Yes";
    for(int i=0; i<N; i++){
        for(int j=0; j<M; j++){
            if(0 < i && x[i][j] != x[i-1][j]+1) ans = "No";
            if(0 < j && y[i][j] != y[i][j-1]+1) ans = "No";
            if(0 < j && x[i][j] != x[i][j-1]) ans = "No";
            if(0 < i && y[i][j] != y[i-1][j]) ans = "No";
        }
    }
    cout << ans << endl;
}
*/

