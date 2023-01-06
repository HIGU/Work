#include <iostream>
#include <vector>
#include <algorithm>
using namespace std;

int main(){
    long long L, Q;
    cin >> L >> Q;

    vector<pair<int, long long>> query;
    for(long long i = 0; i < Q; i++){
        long long c, x;
        cin >> c >> x;
        query.emplace_back(make_pair(c, x));
    }

    vector<bool> wood;
    wood.assign(L + 1, false);
    wood[0] = true; wood[L] = true;
    for(long long idx = 0; idx < Q; idx++){
        if(query[idx].first == 1){
            wood[query[idx].second] = true;
        }
        else if(query[idx].first == 2){
            long long low, up;
            // for(long long i = query[idx].second; i >= 0; i--){
            //     if(wood[i] == true){
            //         low = i;
            //         break;
            //     }
            // }
            // for(long long i = query[idx].second; i < L + 1; i++){
            //     if(wood[i] == true){
            //         up = i;
            //         break;
            //     }
            // }
            bool is_low = false, is_up = false;
            for(int i = 1; i < L; i++){
                if(is_low == false && wood[query[idx].second - i] == true){
                    low = query[idx].second - i;
                    is_low = true;
                }
                if(is_up == false && wood[query[idx].second + i] == true){
                    up = query[idx].second + i;
                    is_up = true;
                }
                if(is_low && is_up) break;
            }
            cout << up - low << endl;
        }
    }

    return 0;
}