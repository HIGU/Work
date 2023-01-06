#include <iostream>
#include <cmath>
using namespace std;

//1以上N以下の整数のうち、10進数で各桁の和がA以上B以下であるものについての総和を求める
int main(){
    int n, a, b;
    cin >> n >> a >> b;

    if(n >= 36) n = 36;

    int sum = 0;
    for(int i = 1; i <= n; i++){
        int x[4] = {0, 0, 0, 0};
        for(int j = 0; j < 4; j++){
            int k = i;
            x[j] = k / pow(10, j);
        }

        int plus = 0;
        for(int plusIdx = 0; plusIdx < 4; plusIdx++){
            plus += x[plusIdx];
        }

        if(plus >= a && plus <= b) sum += i; 
    }

    cout << sum << endl;

    return 0;
}

//解法からの参考
//各桁の和を計算する関数
int FindSumOfDigitals(int i){
    int sum = 0;
    while(i > 0){
        sum += i % 10;
        i /= 10;
    }

    return sum;
}