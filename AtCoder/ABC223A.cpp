#include <iostream>
using namespace std;

int main() {
    int X;
    cin >> X;

    for(int i = 1; i <= 10; i++){
        if(i * 100 == X){
            cout << "Yes" << endl;
            return 0;
        }
    }

    cout << "No" << endl;

    return 0;
}