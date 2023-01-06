#include <iostream>
#include <string>
#include <vector>
using namespace std;

string X;
int N;
vector<string> S;

void my_sort(vector<string> &S_, int index){
    vector<vector<string>> a;
    a.resize(26);

    for(int i = 0; i < S_.size(); i++){
        if(S_[i].length() > index){
            for(int idx = 0; idx < 26; idx++){
                if(S_[i][index] == X[idx]){
                    a[idx].emplace_back(S_[i]);
                }
            }
        }
        else{
            cout << S_[i] << endl;
        }
    }

    for(int idx = 0; idx < 26; idx++){
        if(a[idx].size() == 1){
            cout << a[idx][0] << endl;
        }
        else if(a[idx].size() >= 2){
            my_sort(a[idx], index + 1);
        }
    }
}

int main(){
    cin >> X >> N;
    for(int i = 0; i < N; i++){
        string Sx;
        cin >> Sx;
        S.emplace_back(Sx);
    }

    my_sort(S, 0);

    return 0;
}