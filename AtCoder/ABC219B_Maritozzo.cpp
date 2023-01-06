#include <iostream>
#include <string>
using namespace std;

int main(){
    string S1, S2, S3, T;
    cin >> S1 >> S2 >> S3 >> T;

    string answer = "";
    for(int  idx = 0; idx < T.length(); idx++){
        if(T[idx] == '1'){
            answer += S1;
        }
        else if(T[idx] == '2'){
            answer += S2;
        }
        else if(T[idx] == '3'){
            answer += S3;
        }
    }

    cout << answer << endl;

    return 0;
}