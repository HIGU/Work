#include <iostream>
#include <vector>

// Union-Find
struct UnionFind {
    std::vector<int> par;

    UnionFind() { }
    UnionFind(int n) : par(n, -1) { }
    void init(int n) { par.assign(n, -1); }
    
    int root(int x) {
        if (par[x] < 0) return x;
        else return par[x] = root(par[x]);
    }
    
    bool issame(int x, int y) {
        return root(x) == root(y);
    }
    
    bool merge(int x, int y) {
        x = root(x); y = root(y);
        if (x == y) return false;
        if (par[x] > par[y]) std::swap(x, y); // merge technique
        par[x] += par[y];
        par[y] = x;
        return true;
    }
    
    int size(int x) {
        return -par[root(x)];
    }
};

int main(void){
    // 入力と UnionFind
    int N, K, L;
    // N: 都市数、K:道路数
    std::cin >> N >> K;
    UnionFind road(N);
    for (int i = 0; i < K; ++i) {
        // p:From、q:To
        int p, q;
        std::cin >> p >> q;
        --p, --q;
        road.merge(p, q);
    }

    // 各都市の根を表示
    for(int i = 0; i < N; i++)
    {
        std::cout << road.root(i) << std::endl;
    }

    // つながっている都市の数
    std::cout << road.size(0) << std::endl;

    return 0;
}