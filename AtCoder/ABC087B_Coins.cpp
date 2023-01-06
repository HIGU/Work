#include <iostream>
using namespace std;

int main(){
    int a = 0, b = 0, c = 0;
    cin >> a >> b >> c;

    int x;
    cin >> x;

    int count = 0;
    for(int aIdx = 0; aIdx <= a; aIdx++){
        for(int bIdx = 0; bIdx <= b; bIdx++){
            for(int cIdx = 0; cIdx <= c; cIdx++){
                if(500*aIdx+100*bIdx+50*cIdx == x) count++;
            }
        }
    }

    cout << count << endl;

    return 0;
}