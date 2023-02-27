#include <iostream>
#include <queue>

int main(void){
    std::queue<int> q1;
    //追加
    q1.push(1);
    q1.push(2);
    //先頭データの取得
    int a = q1.front();
    //先頭データの削除
    q1.pop();

    //クリア、空のqueueと中身を入れ替える
    std::queue<int>().swap(q1);

    //中身の移動、q1は空になる
    std::queue<int> q2(std::move(q1));
    
    return 0;
}