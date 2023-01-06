#include <iostream>

int main(){
    double A, B;
    std::cin >> A >> B;

    std::cout << double((A - B)/3.f + B) << std::endl;

    return 0;
}