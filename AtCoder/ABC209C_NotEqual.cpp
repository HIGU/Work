#include <iostream>
#include <vector>
#include <algorithm>

int main(){
  int N;
  std::cin >> N;
  
  std::vector<int> C;
  for(int idx = 0; idx < N; idx++){
    int in;
    std::cin >> in;
    C.emplace_back(in);
  }

  std::sort(C.begin(), C.end());

  long long answer = 1;
  for(int idx = 0; idx < N; idx++){
    answer = answer * std::max(0, C[idx] - idx) % 1000000007;
  }

  std::cout << answer << std::endl;

  return 0;
}