#include <iostream>
#include <vector>
#include <iomanip>
using namespace std;

int main() {
    long long N;
    cin >> N;
    std::vector<pair<int, int>> list;
    long long back_pos = 0;
    std::vector<double> minutes;
    for(long long i = 0; i < N; i++){
        double a, b;
        cin >> a >> b;
        back_pos += a;
        list.emplace_back(make_pair(a, b));
        minutes.emplace_back(a / b);
    }

    std::cout << std::setprecision(15);

    double ans = list[0].first;
    int front_index = 0, back_index = list.size() - 1;
    double a = minutes[0], b = minutes[minutes.size() - 1];
    while(front_index != back_index){
        if(a <= b){
            front_index++;
            a += minutes[front_index];
            ans += list[front_index].first;
        }
        else if(a > b){
            back_index--;
            b += minutes[back_index];

        }
    }

    a -= minutes[front_index];
    ans -= list[front_index].first;
    b -= minutes[back_index];
    double aaa = a - b;
    if(aaa < 0){
        ans += (b - a) * list[front_index].second;
        aaa *= -1;
    }
    double sub = (list[front_index].first - (aaa) * list[front_index].second) / 2;
    ans += sub;

    std::cout << std::setprecision(15) << endl;
    cout << ans << endl;

    return 0;
}