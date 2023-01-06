#include <iostream>
#include <cstring>
#include <string>
using namespace std;

int main(){
    int N;
    string S;
    cin >> N >> S;

    if(S.at(N-1) == 'o'){
            cout << "Yes" << endl;
    }
    else{
        cout << "No" << endl;
    }

    return 0;
}