#include <iostream>
using namespace std;

#if 0
int main()
{
    int n;
    cin >> n;
    float area[2010][3];
    for(int i = 0; i < n; i++){
        cin >> area[i][0] >> area[i][1] >> area[i][2];
    }

    for(int i=0; i < n; i++){
        switch((int)area[i][0]){
        case 1:
            break;
        case 2:
            area[i][2] -= 0.5f;
            break;
        case 3:
            area[i][1] += 0.5f;
            break;
        case 4:
            area[i][1] += 0.5f;
            area[i][2] -= 0.5f;
            break;
        default:
            return -1;
        }

    }

    int count = 0;
    for(int i = 0; i < n; i++){
        for(int j = i+1; j < n; j++){
            count += (max(area[i][1], area[j][1]) <= min(area[i][2], area[j][2]));
        }
    }

    cout << count << endl;

    return 0;
}
#endif

#include <vector>

int main(){
    int N; cin >> N;
    vector<double> l(N),r(N);
    for(int i=0; i<N; i++){
        int t; cin >> t >> l[i] >> r[i];
        t--;
        if(t&1) r[i] -= 0.5;
        if(t&2) l[i] += 0.5;
    }
    int ans = 0;
    for(int i=0; i<N; i++){
        for(int j=i+1; j<N; j++){
            ans += (max(l[i],l[j]) <= min(r[i],r[j]));
        }
    }
    cout << ans << endl;
}