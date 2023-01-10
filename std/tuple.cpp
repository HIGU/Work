#include <iostream>
#include <tuple>
#include <string>

int main(void){
    // 初期化
    std::tuple<int, char, std::string> t = std::make_tuple(1, 'a', "aiueo");
    std::tuple<int, double, std::string> t2{100, 1.1, "aaa"};
    // 1つ目の要素へのアクセス
    int& i = std::get<0>(t);

    return 0;
}