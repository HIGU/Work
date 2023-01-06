#include <iostream>
#include <string>
using namespace std;

int main(){
    string S[4];
    cin >> S[0] >> S[1] >> S[2] >> S[3];

    bool has_hr = false, has_2b = false, has_3b = false, has_h = false;
    for(int idx = 0; idx < 4; idx++){
        if(S[idx] == "H"){
            has_h = true;
        }
        else if(S[idx] == "2B"){
            has_2b = true;
        }
        else if(S[idx] == "HR"){
            has_hr = true;
        }
        else if(S[idx] == "3B"){
            has_3b = true;
        }
    }

    if(has_h && has_2b && has_3b && has_hr){
        cout << "Yes" << std::endl;
    }
    else{
        cout << "No" << std::endl;
    }

    return 0;
}