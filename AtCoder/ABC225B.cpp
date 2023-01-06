#include <iostream>
#include <vector>
#include <set>
using namespace std;

int main() {
    int N;
    cin >> N;
    int my_set[100005][100005];
    for(int idx = 0; idx < N - 1; idx++){
        int a, b;
        cin >> a >> b;
        my_set[a - 1][b - 1] = 1;
        my_set[b - 1][a - 1] = 1;
        // auto a_itr = my_set[a - 1].find(b);
        // if(a_itr != my_set[a - 1].end()){
        //     my_set[a - 1].erase(a_itr);
        // }
        // auto b_itr = my_set[b - 1].find(a);
        // if(b_itr != my_set[b - 1].end()){
        //     my_set[b - 1].erase(b_itr);
        // }
    }
    for(int idx = 0; idx < N; idx++){
                my_set[idx][idx] = 1;
    }

    bool is_empty = false;
    for(int idx = 0; idx < N; idx++){
        // if(my_set[idx].empty()){
        //     is_empty = true;
        //     cout << "Yes" << endl;
        //     break;
        // }
        bool not_found = false;
        for(int i = 0; i < N; i++){
            if(my_set[idx][i] != 1){
                not_found = true;
                break;
            }
        }
        if(!not_found){
            is_empty = true;
            cout << "Yes" << endl;
            break;
        }
    }
    if(!is_empty){
        cout << "No" << endl;
    }
    return 0;
}