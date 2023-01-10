#include <iostream>
#include <functional>
#include <queue>

int main(){
    // int 型の要素を持ち、最も小さい値を取り出す形の priority_queue を定義する場合
    // 第二要素(Container)はvectorやdequeなどが指定可能
    std::priority_queue<int, std::vector<int>, std::greater<int>> pq1;

    // int 型の要素を持ち、最も大きい値を取り出す形の priority_queue を定義する場合
    std::priority_queue<int, std::vector<int>, std::less<int>> pq2;
    // pq2と同じ
    std::priority_queue<int> pq3;
    
    // データを追加する
    pq3.push(3);
    pq3.push(1);
    pq3.push(4);
    
    // 処理順に出力する
    // 4 3 1の順で出力される
    while (!pq3.empty()) {
        std::cout << pq3.top() << std::endl;
        pq3.pop();
    }

    {
        auto compare = [](int a, int b){
            return a < b;
        };

        // 比較関数オブジェクトを指定
        // 降順で出力する
        std::priority_queue<int, std::vector<int>, decltype(compare)> pq4{compare};
    }
}