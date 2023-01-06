#include <iostream>
using namespace std;

int main(){
    int N = 0, Y = 0;
    cin >> N >> Y;

    int a = 0, b = 0, c = 0;
    bool find = false;
    for(a = 0; a <= N; a++){
        for(b = 0; b <= N - a; b++){
            c = N - a - b;
            if((a * 1000 + b * 5000 + c * 10000) == Y){
                find = true;
                break;
            }
        }
        if(find){
            break;
        }
    }

    if(!find){
        a = b = c = -1;
    }

    cout << c << ", " << b << ", " << a << endl;

    return 0;
}