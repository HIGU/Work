#include <iostream>
#include <vector>
#include <algorithm>
#include <array>

int main(){
    //初期化の方法としてはよくない
    //std::vector<int> vec = {0, 1, 2}で初期化すべき
    std::vector<int> vec;
    vec.emplace_back(0);
    vec.emplace_back(1);
    vec.emplace_back(2);

    std::sort(vec.begin(), vec.end());

    //初期化例
    //配列の数:5, 値をappleで初期化
    std::vector<std::string> const_vec1(5, "apple");
    //配列を使った初期化
    std::vector<int> const_vec2 = {0, 1, 2, 3};
    //構造体を使った場合の初期化
    struct Point{int x, y;};
    std::vector<Point> const_vec1(5, {0, 1});
    std::vector<int> const_vec3;
    //同じデータで埋める場合やデータ数が多い場合
    std::fill(const_vec3.begin(), const_vec3.end(), 10);

    //std::vector<std::vector<std::array<int, 2>>>
    //C++が古いとコンパイルが通らない
    std::vector dp(10, std::vector(5, std::array<int, 2>{0, 0}));
    for(int i = 0; i < dp.size(); i++)
    {
        //size => 5
        for(int j = 0; j < dp[i].size(); j++)
        {
            //size => 2
            for(int k = 0; k < dp[i][j].size(); k++)
            {
                std::cout << dp[i][j][k] << std::endl;
            }
        }
    }

    //for分の書き方例
    std::vector<int> auto_vec;
    auto_vec.resize(10, 0);
    for(const auto& itr : auto_vec)
    {
        //処理
        std::cout << itr << std::endl;
    }

    //こんな書き方もできるらしい、ABC244:E
    //C++が古いとコンパイルが通らない
    std::vector<std::pair<int, int>> edge(10);
    int edge_index = 0;
    for(auto& [U, V] : edge){
        U = edge_index;
        V = edge_index * 2;
    }

    //初期化を宣言しない場合、型に応じた初期値になる
    //intの場合は0
    std::vector<int> aaa(2);

    return 0;
}