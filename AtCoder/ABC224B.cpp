#include <iostream>
using namespace std;

int main() {
    long long h, w;
    cin >> h >> w;
    long long A[55][55];

    for(long long idx = 0; idx < h; idx++){
        for(long long i = 0; i < w; i++){
            long long aaa;
            cin >> aaa;
            A[idx][i] = aaa;
            //cin >> A[idx][i];
        }
    }

    for(int idx = 0; idx < h - 1; idx++){
        for(int i = 0; i < w - 1; i++){
            if((A[idx][i] + A[idx + 1][i + 1]) > (A[idx][i + 1] + A[idx + 1][i])){
                cout << "No" << endl;
                return 0;
            }
        }
    }

    cout << "Yes" << endl;

    return 0;
}