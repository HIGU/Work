#include <iostream>
#include <vector>

//N頂点M本の辺のグラフが2部グラフかどうかを判定する
//DFSで解いているがBFSやUnionFindでもできるらしい
//1つの頂点から始めて、矛盾しないか確かめていく

bool dfs(const std::vector<std::vector<int>>& G, std::vector<int>& color, int v, int cur = 0){
    color[v] = cur;
    for(auto next_v : G[v]){
        if(color[next_v] != -1){
            if(color[next_v] == cur) return false;
            continue;
        }
 
        if(!dfs(G, color, next_v, 1-cur)) return false;
    }
    return true;
}
 
int main() {
    int N, M;
    std::cin >> N >> M;
    std::vector<std::vector<int>> graph(N, std::vector<int>());
    for(int i = 0; i < M; ++i){
        int A, B;
        std::cin >> A >> B;
        A--;
        B--;
        graph[A].push_back(B);
        graph[B].push_back(A);
    }
 
    std::vector<int> color(N, -1);
    bool is_bipartite = true;
    for(int i = 0; i < N; ++i){
        if(color[i] != -1) continue;
        if(!dfs(graph, color, i)) is_bipartite = false;
    }
    if(is_bipartite) std::cout << "Yes" << std::endl;
    else std::cout << "No" << std::endl;
 
    return 0;
}