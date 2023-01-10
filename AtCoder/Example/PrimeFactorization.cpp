#include <iostream>
#include <map>

int main(void){
    int N;
    std::cin >> N;
    std::map<int, int> primes;
    int num = N;
    for(int i = 2; i * i <= N; ++i){
        while(num % i == 0){
            num /= i;
            primes[i] += 1;
        }
    }
    if(num > 1) primes[num] += 1;
    for(auto it = primes.begin(); it != primes.end(); ++it){
        std::cout << it->first << " " << it->second << std::endl;
    }
    return 0;
}