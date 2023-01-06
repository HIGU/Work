#include <iostream>
#include <string>
#include <cmath>
using namespace std;

int main(){
    int K;
    cin >> K;
    string A, B;
    cin >> A >> B;
    long long A_int = 0;
    for(int i = A.length() - 1; i >= 0; i--){
        int num = int(A[i] - '0');
        A_int += num * (pow(K, A.length() - 1 - i));
    }

    long long B_int = 0;
    for(int i = B.length() - 1; i >= 0; i--){
        int num = int(B[i] - '0');
        B_int += num * pow(K, B.length() - 1 - i);
    }
    cout << A_int * B_int << endl;

    return 0;
}