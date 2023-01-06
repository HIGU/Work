#include <iostream>
#include <vector>
using namespace std;

//N個の整数を何回2で割ることができるか
int main(){
    int num = 0;
    int num_list[200];

    cin >> num;
    for(int i = 0; i < num; i++){
        cin >> num_list[i];
    }

    int a = 0;
    while(1){
        bool is_break = false;
        for(int i = 0; i < num; i++){
            if(num_list[i] % 2){
                is_break = true;
            }
            else{
                num_list[i] = num_list[i] / 2;
            }
        }

        if(is_break) break;
        a++;
    }

    cout << a << endl;

    return 0;
}