/*
#include <iostream>
#include <vector>
using namespace std;

int main() {
    long long N;
    cin >> N;
    vector<long long>A, B;
    A.resize(N, 0);
    B.resize(N, 0);
    long long max = 0;
    for(long long i = 0; i < N; i++){
        long long Ai, Bi;
        cin >> Ai >> Bi;
        A[i] = Ai;
        B[i] = Bi;
        if((Ai + Bi) > max){
            max = (Ai + Bi);
        }
    }

    vector<long long> login;
    login.resize(max, 0);
    for(long long i = 0; i < N; i++){
        for(long long idx = A[i]; idx < A[i] + B[i]; idx++){
            login[idx] += 1;
        }
    }

    vector<long long> result;
    result.resize(N + 1, 0);
    for(long long idx = 0; idx < login.size(); idx++){
        result[login[idx]] += 1;
    }

    // vector<long long> login;
    // login.resize(N + 1, 0);
    // for(long long i = 0; i < N; i++){
    //     long long A, B;
    //     cin >> A >> B;
    //     for(long long idx = A; idx < A + B; idx++){
    //         login[idx] += 1;
    //     }
    // }

    // vector<long long> result;

    cout << result[1];
    for(int idx = 2; idx < N + 1; idx++){
        cout << " " << result[idx];
    }
    cout << endl;
    return 0;
}
*/

#include <iostream>
#include <vector>
#include <algorithm>
using namespace std;
#define N 200010
#define rep(i, n) for(int i = 0; i < n; ++i)

/*
    ログインした日を+1、ログインしなくなった日を-1として
    昇順で人数をカウントしていくことで割り出す。
*/
int main(void) {
	int n;
	int a, b;
	vector<pair<int, int> >x;
	int cnt;
	int ans[N];
	rep(i, N)ans[i] = 0;
	cin >> n;
	rep(i, n) {
		cin >> a >> b;
		x.push_back({ a,1 });
		x.push_back({ a + b,-1 });
	}
	sort(x.begin(), x.end());
	cnt = 0;
	rep(i, (x.size())-1) {
		cnt += x[i].second;
		ans[cnt] += ((x[i + 1].first) - (x[i].first));
	}
	rep(i, n - 1)cout << ans[i + 1] << " ";
	cout << ans[n] << endl;
	return 0;
}