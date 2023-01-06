#include <iostream>
#include <vector>
using namespace std;

int main(){
    long long N;
    cin >> N;
    long long p[N];
    for(long long i = 0; i < N; i++){
        long long p_;
        cin >> p_;
        p[i] = p_;
    }

    long long q[N + 1];
    for(long long i = 0; i < N; i++){
        q[p[i]] = i + 1;
    }

    cout << q[1];
    for(long long i = 2; i < N + 1; i++){
        cout << " " << q[i];
    }
    cout << endl;

    return 0;
}