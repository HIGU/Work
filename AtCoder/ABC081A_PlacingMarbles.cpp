#include <iostream>
#include <string>
using namespace std;

int main(){
    int count = 0;
    string s;
    cin >> s;

    if(s.size() != 3){
        cout << "Input Error" << endl;
        return -1;
    }

    for(int i = 0; i < 3; i++){
        if(s[i] == '1') count++;
    }

    cout << count << std::endl;

    return 0;
}