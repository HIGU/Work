#include <iostream>
#include <vector>
#include <stack>

//N頂点M本のグラフにおいて、頂点0から各頂点への距離を深さ優先探索で求める

int main(void){
    int N, M;
    std::cin >> N >> M;
    std::vector<std::vector<std::pair<int, int>>> G(N, std::vector<std::pair<int, int>>());
    for(int i = 0; i < M; ++i){
        int a, b, c;
        std::cin >> a >> b >> c;
        a--; b--;
        G[a].push_back({b, c});
        G[b].push_back({a, c});
    }

    std::stack<int> s;
    std::vector<int> distance(G.size(), INT_MAX);
    s.push(0);
    distance[0] = 0;
    while(!s.empty()){
        int top = s.top();
        s.pop();
        for(int i = 0; i < (int)G[top].size(); ++i){
            if(distance[G[top][i].first] == INT_MAX ||
            distance[G[top][i].first] > distance[top] + G[top][i].second){
                distance[G[top][i].first] = distance[top] + G[top][i].second;
                s.push(G[top][i].first);
            }
        }
    }
    for(int i = 0; i < (int)distance.size(); ++i){
        std::cout << i+1 << ": " << distance[i] << std::endl;
    }
    return 0;
}