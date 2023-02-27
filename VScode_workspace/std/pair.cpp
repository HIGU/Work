#include <iostream>
#include <vector>
#include <algorithm>

int main(void){
    // pairオブジェクトの構築
    //make_pairはあまり使わないらしい
    std::pair<int, std::string> p1 = std::make_pair(1, "hello");

    //{}で初期化できる
    std::pair<int, int> p2{1, 2};

    //分解、C++が古いとコンパイルが通らない
    auto [a, b] = p2;
    std::cout << a << " " << b << std::endl;

    // pairのvectorのsortは第一引数の昇順->第二引数の昇順で行う
    std::vector<std::pair<int, int>> vp1;
    vp1.emplace_back(1, 0);
    vp1.emplace_back(10, 2);
    vp1.emplace_back(-1, 1);
    vp1.emplace_back(1, 10);
    vp1.emplace_back(1, 5);
    std::sort(vp1.begin(), vp1.end());
    
    return 0;
}