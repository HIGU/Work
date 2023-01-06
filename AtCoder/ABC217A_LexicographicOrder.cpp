#include <iostream>
#include <string>
#include <algorithm>
using namespace std;

int main(){
    string S, T;
    cin >> S >> T;

    int a = S.length();
    int b = T.length();
    int L = a;
    if(a > b){
        L = b;
    }

    bool is_yes = true;
    for(int i = 0; i < L; i++){
        int s = int(S[i]);
        int t = int(T[i]);
        if(s > t){
            is_yes = false;
            break;
        }
        else if(s < t){
            is_yes = true;
            break;
        }
        else{
            if(L == a){
                is_yes = true;
            }
            else{
                is_yes = false;
            }
        }
    }

    if(is_yes){
        cout << "Yes" << endl;
    }
    else{
        cout << "No" << endl;
    }

    return 0;
}