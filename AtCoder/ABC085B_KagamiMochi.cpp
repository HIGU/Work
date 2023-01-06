#include <iostream>
#include <vector>
using namespace std;

//自作
int main(){
    int N = 0;
    int d[109];

    cin >> N;
    for(int i = 0; i < N; i++){
        cin >> d[i];
    }

    vector<int> v;
    v.resize(0);
    for(int i = 0; i < N; i++){
        if(i == 0){
            v.emplace_back(d[i]);
        }
        else{
            bool have = false;
            for(int idx = 0; idx < (int)v.size(); idx++){
                if(v[idx] == d[i]){
                    have = true;
                }
            }
            if(!have){
                v.emplace_back(d[i]);
            }
        }
    }

    cout << (int)v.size() << endl;

    return 0;
}

//別解1
int main_2(){
        int N;
    int d[110];
    cin >> N;
    for (int i = 0; i < N; ++i) cin >> d[i];

    int num[110] = {0};  // バケット
    for (int i = 0; i < N; ++i) {
        num[d[i]]++;  // d[i] が 1 個増える
    }

    int res = 0;  // 答えを格納
    for (int i = 1; i <= 100; ++i) {  // 1 <= d[i] <= 100 なので 1 から 100 まで探索
        if (num[i]) {  // 0 より大きかったら
            ++res;
        }
    }
    cout << res << endl;
}

//別解2
#include <set>
int main_3(){
        int N;
    int d[110];
    cin >> N;
    for (int i = 0; i < N; ++i) cin >> d[i];

    set<int> values; // insert するときに重複を取り除いてくれます
    for (int i = 0; i < N; ++i) {
        values.insert(d[i]); // 挿入します
    }

    // set のサイズを出力します
    cout << values.size() << endl;
}