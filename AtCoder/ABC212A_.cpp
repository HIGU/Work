#include <iostream>
using namespace std;

int main(){
    int A, B;
    cin >> A >> B;

    if(A > 0 && B > 0){
        cout << "Alloy" << endl;
    }
    else if(A  > 0){
        cout << "Gold" << endl;
    }
    else{
        cout << "Silver" << endl;
    }
    return 0;
}