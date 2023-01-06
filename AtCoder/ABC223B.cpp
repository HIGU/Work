#include <iostream>
#include <vector>
using namespace std;

int main() {
    string s;
    cin >> s;

    int min = 1000, max = -1;
    vector<int> min_num, max_num;
    for(int i = 0; i < s.length(); i++){
        if(s[i] - '0' < min){
            min = s[i] - '0';
            min_num.clear();
        }
        if(s[i] - '0' == min){
            min_num.emplace_back(i);
        }
        if(s[i] - '0' > max){
            max = s[i] - '0';
            max_num.clear();
        }
        if(s[i] - '0' == max){
            max_num.emplace_back(i);
        }
    }

    int ans_min = min_num[0], ans_max = max_num[0];
    for(int i = 1; i < min_num.size(); i++){
        int a = ans_min + 1;
        int b = min_num[i] + 1;
        int index = 0;
        while(index++ < s.length()){
            if(a >= s.length()){
                a -= s.length();
            }
            if(b >= s.length()){
                b -= s.length();
            }
            bool flag = false;
            if(s[a] > s[b]){
                ans_min = min_num[i];
                flag = true;
            }
            else if(s[a] < s[b]){
                flag = true;
            }
            if(flag){
                break;
            }
            a++;
            b++;
        }
    }

    if(max_num.size() >= 2){
    for(int i = 1; i < max_num.size(); i++){
            int a = ans_max + 1;
            int b = max_num[i] + 1;
            int index = 0;
        while(index++ < s.length()){
            if(a >= s.length()){
                a -= s.length();
            }
            if(b >= s.length()){
                b -= s.length();
            }
            bool flag = false;
            if(s[a] < s[b]){
                ans_max = max_num[i];
                flag = true;
            }
            else if(s[a] > s[b]){
                flag = true;
            }
            if(flag){
                break;
            }
            a++;
            b++;
        }
    }
    }

    for(int i = 0; i < s.length(); i++){
        int index = ans_min + i;
        if(index >= s.length()){
            index -= s.length();
        }
        cout << s[index];
    }
    cout << endl;

    for(int i = 0; i < s.length(); i++){
        int index = ans_max + i;
        if(index >= s.length()){
            index -= s.length();
        }
        cout << s[index];
    }
    cout << endl;

    return 0;
}