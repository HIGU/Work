#include <iostream>
#include <string>
using namespace std;

int main() {
    string s;
    cin >> s;

    if(s[s.length()-2] == 'e' && s[s.length() - 1] == 'r'){
        cout << "er" << endl;
    }
    else{
        cout << "ist" << endl;
    }
    return 0;
}