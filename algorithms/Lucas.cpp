#include <iostream>

//nCrのリュカの定理、素数pは3とする

int comb(int n, int r) {
  if (n < 3 && r < 3) {
    if (n < r) return 0;
    if (n == 2 && r == 1) return 2;
    return 1;
  }
  return comb(n / 3, r / 3) * comb(n % 3, r % 3) % 3;
}

int main(void){
    return 0;
}