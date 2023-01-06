#include<bits/stdc++.h>
using namespace std;

int X, Y, Z, K;
vector<vector<long long>> A;
vector<long long> B;
int check(long long x){
	long long cnt = 0;
	for(int i = 0; i < X; ++i){
		int id = lower_bound(B.begin(), B.end(), x - A[0][i]) - B.begin();
		cnt += (int)B.size() - id;
	}
	return K <= cnt;
}

int main(void) {
	cin >> X >> Y >> Z >> K;
	A.resize(3);
	A[0].resize(X);
	A[1].resize(Y);
	A[2].resize(Z);
	for(int i = 0; i < X; ++i) cin >> A[0][i];
	for(int i = 0; i < Y; ++i) cin >> A[1][i];
	for(int i = 0; i < Z; ++i) cin >> A[2][i];
	for(int i = 0; i < Y; ++i){
		for(int j = 0; j < Z; ++j){
			B.push_back(A[1][i] + A[2][j]);
		}
	}
	sort(B.begin(), B.end());

	long long ok = 0, ng = 10000000000000000LL;
	while(ok+1LL != ng){
		long long md = (ng + ok) / 2LL;
		if(check(md)) ok = md;
		else ng = md;
	}

	sort(B.begin(), B.end(), greater<long long>());
	vector<long long> ans;
	for(int i = 0; i < X; ++i){
		for(auto& b : B){
			if(ok < A[0][i] + b) ans.push_back(A[0][i] + b);
			else break;
		}
	}
	while((int)ans.size() < K) ans.push_back(ok);

	sort(ans.begin(), ans.end(), greater<long long>());
	for(int i = 0; i < K; ++i) cout << ans[i] << endl;
    return 0;
}