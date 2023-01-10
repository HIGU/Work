#include <iostream>
#include <iomanip>
using namespace std;

//3点a, b, cが与えられたとき、線分bcとaとの最短距離

int main() {
    pair<long long, long long> a, b, c;
    cin >> a.first >> a.second;
    cin >> b.first >> b.second;
    cin >> c.first >> c.second;

    double x = c.first - b.first;
    double y = c.second - b.second;
    double x2 = x * x;
    double y2 = y * y;
    //bcの距離の2乗
    double r2 = x2 + y2;
    //bcとabの内積
    double tt = -(x * (b.first - a.first) + y * (b.second- a.second));
    if(tt < 0){
        //負の場合は角度abcが90度以上、つまり線分の外側なので最短はabになる
        cout << fixed << setprecision(12) << sqrt((b.first - a.first) * (b.first - a.first) + (b.second - a.second) * (b.second - a.second));
    }
    else if(tt > r2){
        //bcよりttが長いので、aは線分の外側でcのほうにある
        cout << fixed << setprecision(12) << sqrt((c.first - a.first) * (c.first - a.first) + (c.second - a.second) * (c.second - a.second));
    }
    else{
        //bcに垂直に落とした線が最短距離
        double f1 = x * (b.second - a.second) - y * (b.first - a.first);
        cout << fixed << setprecision(12) << sqrt((f1 * f1) / r2) << endl;
    }
    return 0;
}