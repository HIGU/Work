#include <iostream>
#include <algorithm>
#include <vector>

int main(void){
    std::vector<int> a = {1, 4, 4, 7, 7, 8, 8, 11, 13, 19};

    auto itr1 = std::lower_bound(a.begin(), a.end(), 1);// *itr1 -> 1
    auto itr2 = std::lower_bound(a.begin(), a.end(), 7);// *itr2 -> 7
    auto itr3 = std::lower_bound(a.begin(), a.end(), 20);// itr3 -> a.end()

    std::cout << *itr1 << std::endl;
    std::cout << *itr2 << std::endl;
    if(itr3 != a.end()){
        std::cout << *itr3 << std::endl;
    }
    else if(itr3 == a.end()){
        std::cout << "is end" << std::endl;
    }

    std::cout << itr2 - itr1 << std::endl;//itr1からitr2までの個数を表す、3
    std::cout << a.end() - itr2 << std::endl;//itr2よりも後ろの要素の個数(itr2を含む)、7
    std::cout << itr2 - a.begin() << std::endl;//itr2よりも前の要素の個数(itr2は含まない)、3

    //itr2のaでのindexを取得、endの場合はindexが配列のサイズになるので注意
    int index = std::distance(a.begin(), itr2);

    int count = __builtin_popcount(10);//bitが立っている数を数える

    // VisualStudioではminとmaxを使うときは()で囲むかNOMINMAXのDefineが必要
    // windows.hでminとmaxが使われているため
    int max = (std::max)(0, 1);
    int min = (std::min)(0, 1);

    // 昇順sort
    auto mySortFunction = [](int a, int b){
        return a < b;
    };
    std::sort(a.begin(), a.end(), mySortFunction);

    // 降順sort
    std::sort(a.begin(), a.end(), std::greater<int>());

    return 0;
}