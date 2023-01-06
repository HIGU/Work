#include <iostream>
#include <vector>
using namespace std;

int main() {
    int N;
    cin >> N;

    vector<pair<long long, long long>> points;
    for(int idx = 0; idx < N; idx++){
        long long a, b;
        cin >> a >> b;
        points.emplace_back(make_pair(a, b));
    }

    long long ans = 0;
    for(int i = 0; i < (int)points.size(); i++){
        for(int idx = i + 1; idx < (int)points.size(); idx++){
            for(int index = idx + 1; index < (int)points.size(); index++){
                long long x1 = (points[i].first - points[idx].first);
                long long x2 = (points[i].first - points[index].first);
                long long y1 = (points[i].second - points[idx].second);
                long long y2 = (points[i].second - points[index].second);
                if(x2 * y1 - x1 * y2 !=0){
                    ans += 1;
                }
            }
        }
    }

    cout << ans << endl;

    return 0;
}