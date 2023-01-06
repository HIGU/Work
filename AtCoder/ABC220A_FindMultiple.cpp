#include <iostream>
using namespace std;

int main(){
    int A, B, C;
    cin >> A >> B >> C;
    bool find = false;
    int check = C;
    while(check <= B){
        if(A <= check && B >= check){
            find =true;
            break;
        }
        check += C;
    }

    if(find){
        cout << check << endl;
    }
    else{
        cout << -1 << endl;
    }
    

    return 0;
}