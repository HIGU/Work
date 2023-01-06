#include <iostream>
#include <string>
using namespace std;

int main() {
    std::string str;
    cin >> str;

    int aaa = 3;
    if(str[0] == str[1]){
        aaa -= 1;
        if(str[1] == str[2]){
            aaa -= 1;
        }
    }
    else if(str[0] == str[2]){
        aaa -= 1;
        if(str[1] == str[2]){
            aaa -= 1;
        }
    }
    else if(str[1] == str[2]){
        aaa -= 1;
    }
    if(aaa == 1){
        cout << "1" << std::endl;
    }
    else if(aaa == 2){
        cout << "3" << endl;
    }
    else if(aaa == 3){
        cout << "6" << endl;
    }
    return 0;
}

/*
int main(){
    string S; cin >> S;
    sort(S.begin(),S.end());
    set<string> s;
    do{
        s.insert(S);
    }while(next_permutation(S.begin(),S.end()));
    cout << s.size() << endl;
}
*/