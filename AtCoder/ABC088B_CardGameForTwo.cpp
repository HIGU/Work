#include <iostream>
#include <algorithm>
using namespace std;

int main(){
    int N;
    int a[109];

    cin >> N;
    for(int i = 0; i < N; i++){
        cin >> a[i];
    }

    sort(a, a + N, greater<int>());

    int sum_alice = 0, sum_bob = 0;
    for(int i = 0; i < N; i++){
        if(i%2){
            sum_bob += a[i];
        }
        else{
            sum_alice += a[i];
        }
    }

    int dif = sum_alice - sum_bob;

    cout << dif << endl;

    return 0;
}