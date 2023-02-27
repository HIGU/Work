#include <iostream>
#include <set>

// エラトステネスの篩
int main(void){
    int N;
    std::cin >> N;
    std::set<int> group;
    for(int i = 2; i <= N; ++i){
        group.insert(i);
    }
    int index = 2;
    while(index < N){
        auto itr = group.lower_bound(index);
        if(itr == group.end()) break;
        int num = N / *itr;
        for(int i = 2; i <= num; ++i){
            auto it = group.find(*itr * i);
            if(it != group.end()) group.erase(*it);
        }
        index = *itr + 1;
    }
    // 最終的なgroupが素数の集合
    // for(auto itr = group.begin(); itr != group.end(); ++itr){
    //     std::cout << *itr << std::endl;
    // }
    return 0;
}