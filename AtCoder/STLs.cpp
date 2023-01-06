#include <iostream>
#include <numeric>
using namespace std;

int main() {
    return 0;
}

/*
*   総和を求める
*/
HRESULT Accumulate(
){
    int list[] = {1, 2, 3, 4, 5, 6, 7, 8, 9, 10};
    const size_t size = sizeof(list) / sizeof(list[0]);

    int sum = std::accumulate(list, list + size, 0);    // ans = 55

    return S_OK;
}

/*
*   ABC223 StringShifting参考
*   文字列の順番をひとつづつずらした時の辞書順で最小と最大を出力
*/
HRESULT StringShifting(
){
    string s;
    cin >> s;
    int n = size(s);
    vector<string> v(n);
    for (int i = 0; i < n; ++i) {
        v[i] = s.substr(i, n - i) + s.substr(0, i);
    }
    cout << *min_element(begin(v), end(v)) << '\n';//最小を出力
    cout << *max_element(begin(v), end(v)) << '\n';//最大を出力

    return S_OK;
}