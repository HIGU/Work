#include <iostream>
#include <deque>

int main(){
    std::deque<int> dq1;
    dq1.push_front(0);//0
    dq1.push_back(1);//0 1
    dq1.push_front(2);//2 0 1

    dq1.pop_back();//2 0
    dq1.pop_front();//0

    dq1.push_back(1);//0 1
    dq1.push_back(2);//0 1 2

    dq1.front() = 100;//100 1 2
    dq1.back() = 10;//100 1 10

    for(auto value : dq1)
    {
        std::cout << value << " ";
    }
    std::cout << std::endl;

    return 0;
}