#include <iostream>
#include <vector>
using namespace std;

//エラトステネスの区間篩
 
int main(){
	long long L, R;
	cin >> L >> R;
	vector<bool> is_primes(R - L + 10LL, true);
	if(L == 1LL) is_primes[0] = false;
	
	for(long long i = 2; i * i <= R; ++i){
		long long mini = ((L + i - 1LL) / i) * i;
		for(long long j = mini; j <= R; j += i){
			if(j == i) continue;
			is_primes[j - L] = false;
		}
	}
 
	long long ans = 0;
	for(long long i = 0; i <= R - L; ++i){
		if(is_primes[i] == true) ans++;
	}
	cout << ans << endl;
    return 0;
}