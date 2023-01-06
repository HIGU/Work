#include <iostream>
#include <vector>
#include <algorithm>
using namespace std;

/*
    N日間の夏休みで
    A:幸福度aiを追加
    B:幸福度biを追加
    C:幸福度ciを追加
    の三択から選ぶことができる。
    ただし、同じ行動を続けて取ることはできない。
    幸福度を最大にする。
*/
int main(){
    int N;
    cin >> N;

    long long happy_list[10010][3];
    for(int idx = 0; idx < N; idx++){
        cin >> happy_list[idx][0] >> happy_list[idx][1] >> happy_list[idx][2];
    }

    long long answer[10010][3];

    for(int idx = 1; idx < N+1; idx++){
        for(int index = 0; index < 3; index++){
            int act1 = index + 1, act2 = index + 2;
            if(act1 >= 3){
                act1 -= 3;
            }
            if(act2 >= 3){
                act2 -= 3;
            }
            answer[idx][index] = max(answer[idx-1][act1] + happy_list[idx-1][index], answer[idx-1][act2] + happy_list[idx-1][index]);
        }
    }

    long long res = 0;
    for(int idx = 0; idx < 3; idx++){
        res = max(res, answer[N][idx]);
    }
    cout << res << endl;

    return 0;
}