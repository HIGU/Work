#include <iostream>
#include <string>
#include <map>
using namespace std;

int main(){
    string S1, S2, S3;
    cin >> S1 >> S2 >> S3;
    
    map<string, bool> contests;
    contests.emplace("ABC", false);
    contests.emplace("ARC", false);
    contests.emplace("AGC", false);
    contests.emplace("AHC", false);


    for(auto itr = contests.begin(); itr != contests.end(); itr++){
        if(itr->first == S1){
            itr->second = true;
        }
        else if(itr->first == S2){
            itr->second = true;
        }
        else if(itr->first == S3){
            itr->second = true;
        }
    }

    for(auto itr = contests.begin(); itr != contests.end(); itr++){
        if(itr->second == false){
            cout << itr->first << endl;
            break;
        }
    }

    return 0;
}