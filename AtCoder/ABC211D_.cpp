#include <iostream>
#include <string>
#include <vector>
#include <pair>
using namespace std;

int main(){
    int N, M;
    cin >> N >> M;

    std::vector<std::pair<int, int>> node;
    for(int idx = 0; idx < M; idx++){
        int A, B;
        cin >> A >> B;
        node.emplace_back(std::make_pair(A, B));
    }

    return 0;
}