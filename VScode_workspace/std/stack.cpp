#include <iostream>
#include <stack>

int main(void){
    std::stack<int> st;
    //データの追加
    st.push(1);
    st.push(2);
    st.push(3);
    //3 2 1の順で出力
    while(!st.empty()){
        //先頭データの出力
        std::cout << st.top() << std::endl;
        //先頭データの削除
        st.pop();
    }
    return 0;
}