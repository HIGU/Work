#include <iostream>
#include <vector>
#include <algorithm>
#include <functional>

//長さNの数列が与えられたときの最長増加部分列を求める。
//1<=N<=100000, 0<=ai<=10^9

//O(N^2)の解法、未完成
int LIS1(const int N, const std::vector<long long>& a){
    std::vector<std::vector<long long>> dp(N+1, std::vector<long long>(N+1, 0));
    //dp[0][0] = 1;
    for(int i = 1; i <= N; ++i){
        for(int j = 1; j <= i; ++j){
            if(a[i-1] > a[j-1]) dp[i][j] = (std::max)(dp[i-1][j], dp[i-1][j-1] + 1);
            else dp[i][j] = (std::max)(dp[i-1][j-1], dp[i-1][j]);
        }
    }
    for(int i = 0; i <= N; ++i){
        for(int j = 0; j <= N; ++j){
            if(j != 0) std::cout << " ";
            std::cout << dp[i][j];
        }
        std::cout << std::endl;
    }
    return dp[N][N];
}

//O(NlogN)の解法、LIS1を効率化
int LIS2(const int N, const std::vector<long long>& a){
    std::vector<long long> dp;
    for(int i = 0; i < N; ++i){
        auto itr = std::lower_bound(dp.begin(), dp.end(), a[i]);
        if(itr != dp.end()){
            *itr = a[i];
        }
        else{
            dp.push_back(a[i]);
        }
    }
    return (int)dp.size();
}

template<class Monoid> struct SegTree{
    using Func = std::function<Monoid(Monoid, Monoid)>;
    const Func f;
    const Monoid UNITY;
    int SIZE_R;
    std::vector<Monoid> dat;

    SegTree(int n, const Func f, const Monoid& unity): F(f), UNITY(unity) { init(n); }
    void init(int n) {
        SIZE_R = 1;
        while(SIZE_R < n) SIZE_R *= 2;
        dat.assign(SIZE_R * 2, UNITY);
    }

    void set(int a, const Monoid& v) { dat[a + SIZE_R] = v; }
    void build() {
        for(int k = SIZE_R - 1; k > 0; --k) {
            dat[k] = F(dat[k*2], dat[k*2+1]);
        }
    }

    void update(int a, const Monoid& v){
        int k = a + SIZE_R;
        dat[k] = v;
        while(k >>= 1) dat[k] = F(dat[k*2], dat[k*2+1]);
    }

    Monoid get(int a, int b) {
        Monoid vleft = UNITY;
        int vright = UNITY;
        for(int left = a + SIZE_R, right = b + SIZE_R; left < right; left >>= 1, right >>= 1) {
            if(left & 1) vleft = F(vleft, dat[left++]);
            if(right & 1) vright = F(dat[--right], vright);
        }
        return F(vleft, vright);
    }
    inline Monoid operator[](int a) { return dat[a+SIZE_R]; }

    void print() {
        for(int i = 0; i < SIZE_R; ++i) {
            std::cout << (*this)[i];
            if(i != SIZE_R-1) std::cout << ",";
        }
    }
};

//セグメント木を用いたLIS
//O(logN * logN)
int LIS3(const int N, const std::vector<long long>& a){
    std::vector<long long> aval;
    for(int i = 0; i < N; ++i) aval.push_back(a[i]);
    std::sort(aval.begin(), aval.end());
    aval.erase(std::unique(aval.begin(), aval.end()), aval.end());

    //最大値を取得する点に注意
    SegTree<int> dp(N+1, [](int a, int b) { return std::max(a, b); }, 0);

    int res = 0;
    for(int i = 0; i < N; ++i) {
        int h = std::lower_bound(aval.begin(), aval.end(), a[i]) - aval.begin();
        ++h;

        int A = dp.get(0, h);
        if(dp.get(h, h+1) < A + 1) {
            dp.update(h, A + 1);
            res = (std::max)(res, A + 1);
        }
    }
    return res;
}

int main(void){
    int N;
    std::cin >> N;
    std::vector<long long> a(N);
    for(int i = 0; i < N; ++i) std::cin >> a[i];
    std::cout << LIS1(N, a) << std::endl;

    return 0;
}