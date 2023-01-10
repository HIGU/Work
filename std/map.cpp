#include <iostream>
#include <map>


int main(void){
    std::map<int, int> vec;
    vec.emplace(0, 0);
    vec.emplace(1, 0);
    vec.emplace(2, 0);
    vec[3] = 4;

    //内部的にはすでにソートはかかっているはず
    //std::sort(vec.begin(), vec.end());

    //要素が存在するか
    auto itr = vec.find(0);
    if(itr == vec.end()){
        std::cout << "Not Find" << std::endl;
    }

    //Mapの場合はカウントでも要素の有無のチェックができる
    if(vec.count(0) == 0){
        std::cout << "Not Found" << std::endl;
    }

    // for分の書き方例
    for(auto& itr : vec)
    {
        std::cout << itr.first << ", " << itr.second << std::endl;
    }

    // 任意の番号へのアクセス
    // 例は3番目の要素へのアクセス
    auto itr1 = vec.begin();
    std::next(itr1, 2);

    return 0;
}