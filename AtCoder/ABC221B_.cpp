#include <iostream>
#include <string>
using namespace std;

int main() {
    string S, T;
    cin >> S >> T;

if(S == T){
    cout << "Yes" << endl;
    return 0;
}
else{
    int length = S.length();
    if(length != (int)T.length()){
        cout << "No" << endl;
        return 0;
    }
    //int index = -1;
    for(int i = 0; i < length - 1; i++){
        if(S[i] != T[i]){
            //if(index >= 0){
                char s1 = S[i + 1];
                S[i + 1] = S[i];
                S[i] = s1;
                // char t1 = T[index];
                // T[index] = T[i];
                // T[i] = t1;
                if(S == T){
                    cout << "Yes" << endl;
                }
                else{
                    cout << "No" << endl;
                }
                return 0;
            //}
            // else{
            //     index = i;
            // }
        }
    }
}

cout << "No" << endl;

    return 0;
}