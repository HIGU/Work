#include <iostream>
#include <string>
#include<vector>
using namespace std;

int main(){
    int P[26];
    for(int i = 0; i < 26; i++){
        // char S;
        // cin >> S;
        // P.emplace_back(S);
        cin >> P[i];
        P[i] -= 1;
    }

    char plus = 39;
    for(int i = 0; i < 26 ; i++){
        cout << (char)('a' + P[i]);
    }
    cout << endl;

    return 0;
}