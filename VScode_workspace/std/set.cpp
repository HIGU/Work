#include <iostream>
#include <set>
#include <string>

int main(void){
    //要素の挿入
    std::set<int> a;
    a.insert(0);
    a.insert(1);
    a.insert(2);
    //a.size();

    // 初期化
    // 初期化後、自動的にソートされる
    std::set<std::string> b{"aiueo", "abc", "bbb"};

    // コピーコンストラクタ
    std::set<std::string> c(b);

    //検索
    //あるかどうかだけならcountで代用可能
    auto itr = a.find(0);
    if(itr != a.end()){
        std::cout << *itr << std::endl;
    }

    //全要素
    for(auto it = a.begin(); it != a.end(); ++it){
        std::cout << *it << std::endl;
    }

    // 1以上5以下の要素を検索
    auto one_it = a.lower_bound(1);
    auto five_it = a.upper_bound(5);
    while(one_it != five_it){
        std::cout << *one_it << std::endl;
        one_it++;
    }
    return 0;
}