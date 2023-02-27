#include <iostream>
#include <vector>
using namespace std;

//N個のノードを持つ木の各辺間の距離の合計値を求める
//各辺を通る回数から求める。各辺を通る回数は、(自身と子の数)*(それ以外のノード数)で求める

vector<vector<int>> tree;
vector<int> dist;//自身+子の数

void Dfs(const int n){
	dist[n] = 1;
	for(int node : tree[n]){
		if(dist[node] == 0){
			Dfs(node);
			dist[n] += dist[node];
		}
	}
}

int main(){
	int N;
	cin >> N;
	tree.resize(N);
	for(int i = 0; i < N-1; ++i){
		int a, b;
		cin >> a >> b;
		a--;
		b--;
		tree[a].push_back(b);
		tree[b].push_back(a);
	}
	dist.resize(N, 0);
	Dfs(0);

	long long ans = 0;
	for(int i = 1; i < N; ++i){
		ans += (long long)dist[i] * (long long)(N - dist[i]);
	}
	cout << ans << endl;
    return 0;
}