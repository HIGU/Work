#include <iostream>
#include <algorithm>
#include <vector>
#include <numeric>
#include <map>
using namespace std;

int main(){
    int N;
    multimap<int, int> x_map;
    multimap<int, int> y_map;
    cin >> N;

    long long answer = 0;
    for(int i = 0; i < N; i++){
        int x, y;
        cin >> x >> y;
        std::vector<int> y_found;
        auto a = x_map.equal_range(x);
        for(auto itr = a.first; itr != a.second; ++itr){
            y_found.emplace_back(itr->second);
        }

        auto b = y_map.equal_range(y);
        for(auto itr = b.first; itr != b.second; ++itr){
            auto c = x_map.equal_range(itr->second);
            for(auto it = c.first; it != c.second; ++it){
                for(int idx = 0; idx < y_found.size(); idx++){
                    if(it->second == y_found[idx]){
                        answer += 1;
                    }
                }
            }
        }

        x_map.emplace(x, y);
        y_map.emplace(y, x);
    } 

    cout << answer <<endl;
    
    return 0;
}