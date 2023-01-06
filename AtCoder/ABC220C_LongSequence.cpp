#include <iostream>
#include <vector>
using namespace std;

int main(){
    int N;
    cin >> N;
    vector<long long> A;
    long long sum = 0;
    A.resize(N, 0);
    for(int i = 0; i < N; i++){
        long long Ai;
        cin >> Ai;
        A[i] = Ai;
        sum += Ai;
    }
    long long X;
    cin >> X;

    long long a, b;
    a = X / sum;
    b = X % sum;

    long long pos = 0;
    long long c = 0;
    for(int i = 0; i < N; i++){
        c += A[i];
        pos ++;
        if(b < c){
            break;
        }
    }

    cout << a * N + pos <<endl;
    
    return 0;
}