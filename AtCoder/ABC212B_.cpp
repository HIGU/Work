#include <iostream>
#include <string>
using namespace std;

int main(){
    string x[4];
    int aaa;
    cin >> aaa;
    int a = (aaa/1000);
    x[0] = to_string(a);
    int b = (aaa/100) - a*10;
    x[1] = to_string(b);
    int c = (aaa/10) - a*100 - b*10;
    x[2] = to_string(c);
    int d = aaa - a*1000 - b*100 - c*10;
    x[3] = to_string(d);

    bool is_weak = true;
    bool is_same = true;
    string check = x[0];
    for(int idx = 1; idx < 4; idx++){
        if(check == x[idx]){
            is_same = true;
            continue;
        }
        is_same = false;
        break;
    }
    
    check = x[0];
    for(int idx = 1; idx < 4; idx++){
        if(std::to_string(std::stoi(check) + 1) == x[idx]){
            check = x[idx];
            is_weak = true;
            continue;
        }
        else if(check == "9" && x[idx] == "0"){
            check = x[idx];
            is_weak = true;
            continue;
        }
        is_weak = false;
        break;
    }

    if(!is_same && !is_weak){
        cout << "Strong" << endl;
    }
    else{
        cout << "Weak" << endl;
    }

    return 0;
}