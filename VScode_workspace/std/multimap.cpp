#include <iostream>
#include <map>
#include <string>

int main(void){
    std::multimap<std::string, int> mm1;
    mm1.emplace("a", 2);
    mm1.emplace("b", 10);
    mm1.emplace("a", 1);
    mm1.emplace("c", 20);
    mm1.emplace("c", 5);

    //["a", 2]["a", 1]["b", 10]["c", 20]["c", 5]
    //第二要素ではソートされない
    for(auto itr = mm1.begin(); itr != mm1.end(); itr++)
    {
        std::cout << itr->first << " " << itr->second << std::endl;
    }

    //first:["a", 2], second:["b", 10]
    //secondは範囲の最後+1の要素を返す
    auto itr_pair = mm1.equal_range("a");
    std::cout << itr_pair.first->first << " " << itr_pair.first->second << std::endl;
    std::cout << itr_pair.second->first << " " << itr_pair.second->second << std::endl;
    return 0;
}