#include <iostream>
#include <cmath>
using namespace std;

int main() {
    long long A, B;
    cin >> A >> B;

    long long result = pow((long long)32, (A - B));
    cout << result << endl;

    return 0;
}