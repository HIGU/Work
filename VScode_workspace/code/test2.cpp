#include "bits/stdc++.h"
using namespace std;

struct edge {
    int to;
    int color;
    int dist;
};

vector<vector<edge>> g;
vector<long long> d;
vector<pair<int, long long>> cnt;
vector<vector<tuple<int, long long, int, bool>>> ppp;
vector<long long> ans;

void dfs(int v, int p = -1) {
    for (edge e : g[v]) {
        if (e.to == p) continue;
        d[e.to] = d[v] + e.dist;
        cnt[e.color].first++;
        cnt[e.color].second += e.dist;
        for (auto t : ppp[e.to]) {
            int c = get<0>(t);
            long long dis = get<1>(t);
            int ban = get<2>(t);
            bool isLca = get<3>(t);
            if (isLca) {
                ans[ban] -= 2 * (d[e.to] - cnt[c].second + dis * cnt[c].first);
            }
            else {
                ans[ban] += d[e.to] - cnt[c].second + dis * cnt[c].first;
            }
        }
        dfs(e.to, v);
        cnt[e.color].first--;
        cnt[e.color].second -= e.dist;
    }
}

struct LowestCommonAncestor {
private:
    int n;
    int log;
    vector<vector<int>> parent;
    vector<int> dep;
    vector<vector<int>> G;
    void dfs(const vector<vector<int>>& G, int v, int p, int d){
        parent[0][v] = p;
        dep[v] = d;
        for(int to : G[v]){
            if(to != p) dfs(G, to, v, d + 1);
        }
    }
public:
    LowestCommonAncestor(int n) : n(n){
        G.resize(n);
    }

    void add_edge(int from, int to) {
        G[from].push_back(to);
        G[to].push_back(from);
    }

    void build(int root = 0){
        log = log2(n) + 1;
        parent.resize(log, vector<int>(n));
        dep.resize(n);
        LowestCommonAncestor::dfs(G, root, -1, 0);
        for(int k = 0; k + 1 < log; ++k){
            for(int v = 0; v < (int)G.size(); ++v){
                if(parent[k][v] < 0){
                    parent[k+1][v] = -1;
                }
                else {
                    parent[k+1][v] = parent[k][parent[k][v]];
                }
            }
        }
    }

    int depth(int v){
        return dep[v];
    }

    int lca(int u, int v) {
        if(dep[u] > dep[v]) swap(u, v);
        for(int k = 0; k < log; ++k) {
            if((dep[v] - dep[u]) >> k & 1) v = parent[k][v];
        }
        if(u == v) return u;
        for(int k = log - 1; k >= 0; --k) {
            if(parent[k][u] != parent[k][v]) {
                u = parent[k][u];
                v = parent[k][v];
            }
        }
        return parent[0][u];
    }

    int dist(int u, int v) {
        return dep[u] + dep[v] - 2 * dep[lca(u, v)];
    }
};

int main() {
    int N, Q;
    cin >> N >> Q;

    g.resize(N);
    d.resize(N);
    cnt.resize(N, {0, 0});
    ppp.resize(N);
    LowestCommonAncestor lca(N);
    ans.resize(Q);
    for(int i = 0; i < N - 1; ++i) {
        int a, b, c, d;
        cin >> a >> b >> c >> d;
        a--;
        b--;
        c--;
        g[a].push_back({b, c, d});
        g[b].push_back({a, c, d});
        lca.add_edge(a, b);
    }
    lca.build();
    for(int i = 0; i < Q; ++i) {
        int c, d, a, b;
        cin >> c >> d >> a >> b;
        a--;
        b--;
        c--;
        ppp[a].push_back(make_tuple(c, (long long)d, i, false));
        ppp[b].push_back(make_tuple(c, (long long)d, i, false));
        ppp[lca.lca(a, b)].push_back(make_tuple(c, (long long)d, i, true));
    }
    d[0] = 0;
    dfs(0);
    for(int i = 0; i < Q; ++i) {
        cout << ans[i] << endl;
    }
    return 0;
}