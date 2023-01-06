#include <iostream>
#include <vector>
#include <algorithm>
using namespace std;

int main(){
    int N;
    cin >> N;

    vector<int> h;
    for(int idx = 0; idx < N; idx++){
        int a;
        cin >> a;
        h.emplace_back(a);
    }

    std::vector<int> answer;
    answer.resize(N);
    for(int idx = 0; idx < N; idx++){
        if(idx == 0){
            answer[idx] = 0;
        }
        else if(idx == 1){
            answer[idx] = answer[idx-1] + abs(h[idx] - h[idx-1]);
        }
        else{
            answer[idx] = min(answer[idx-1] + abs(h[idx] - h[idx-1]), answer[idx-2] + abs(h[idx] - h[idx-2]));
        }
    }

    cout << answer[N] << endl;
    
    return 0;
}