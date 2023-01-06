#include <iostream>
#include <map>
#include <algorithm>
using namespace std;

bool canTravel(
    int t1,
    int x1,
    int y1,
    int t2,
    int x2,
    int y2
){
    if((t2 - t1) >= (abs(x1-x2) + abs(y1-y2))){
        int last = abs((t2 - t1) - abs(x1-x2) - abs(y1-y2));
        if(!last%2){
            return true;
        }
    }
    return false;
}

int main_1(){
    int N;
    map<int, pair<int, int>> pos;
    pos.emplace(0, make_pair(0, 0));

    cin >> N;
    for(int i = 0; i < N; i++){
        int t, x, y;
        cin >> t >> x >> y;
        pos.emplace(t, make_pair(x, y));
    }

    bool can_travel = true;
    int t, x, y;
    for(auto itr = pos.begin(); itr != pos.end(); itr++){
        if(itr == pos.begin()){
            t = itr->first;
            x = itr->second.first;
            y = itr->second.second;
            continue;
        }
        bool flag = canTravel(t, x, y, itr->first, itr->second.first, itr->second.second);
        if(!flag){
            can_travel = false;
            break;
        }
        t = itr->first;
        x = itr->second.first;
        y = itr->second.second;
    }

    if(can_travel){
        cout << "Yes" << endl;
    }
    else{
        cout << "No" << endl;
    }

    return 0;
}

//解答
int main() {
    int N;
    int t[110000], x[110000], y[110000];
    cin >> N;
    t[0] = x[0] = y[0] = 0;  // 初期状態
    for (int i = 0; i < N; ++i) cin >> t[i+1] >> x[i+1] >> y[i+1];  // 1-index にしておく

    bool can = true;
    for (int i = 0; i < N; ++i) {
        int dt = t[i+1] - t[i];
        int dist = abs(x[i+1] - x[i]) + abs(y[i+1] - y[i]);
        if (dt < dist) can = false;
        if (dist % 2 != dt % 2) can = false;  // dist と dt の偶奇は一致する必要あり！
    }

    if (can) cout << "Yes" << endl;
    else cout << "No" << endl;
}