#include <iostream>
using namespace std;

//参考：https://qiita.com/drken/items/3b4fdf0a78e7a138cd9a

//-------------------------------------------------------------
//二項係数の場合(nCrの計算)

const int MAX = 510000;
const int MOD = 1000000007;

long long fac[MAX], finv[MAX], inv[MAX];

// テーブルを作る前処理
void COMinit() {
    fac[0] = fac[1] = 1;
    finv[0] = finv[1] = 1;
    inv[1] = 1;
    for (int i = 2; i < MAX; i++){
        fac[i] = fac[i - 1] * i % MOD;
        inv[i] = MOD - inv[MOD%i] * (MOD / i) % MOD;
        finv[i] = finv[i - 1] * inv[i] % MOD;
    }
}

// 二項係数計算
long long COM(int n, int k){
    if (n < k) return 0;
    if (n < 0 || k < 0) return 0;
    return fac[n] * (finv[k] * finv[n - k] % MOD) % MOD;
}

//-------------------------------------------------------------
//拡張Euclidの互除法による逆元計算

long long modinv(long long a, long long m) {
    long long b = m, u = 1, v = 0;
    while (b) {
        long long t = a / b;
        a -= t * b; swap(a, b);
        u -= t * v; swap(u, v);
    }
    u %= m; 
    if (u < 0) u += m;
    return u;
}

//-------------------------------------------------------------
//modint構造体
using mint = Fp<MOD>;

template<int MOD> struct Fp {
    long long val;
    constexpr Fp(long long v = 0) noexcept : val(v % MOD) {
        if (val < 0) val += MOD;
    }
    constexpr int getmod() { return MOD; }
    constexpr Fp operator - () const noexcept {
        return val ? MOD - val : 0;
    }
    constexpr Fp operator + (const Fp& r) const noexcept { return Fp(*this) += r; }
    constexpr Fp operator - (const Fp& r) const noexcept { return Fp(*this) -= r; }
    constexpr Fp operator * (const Fp& r) const noexcept { return Fp(*this) *= r; }
    constexpr Fp operator / (const Fp& r) const noexcept { return Fp(*this) /= r; }
    constexpr Fp& operator += (const Fp& r) noexcept {
        val += r.val;
        if (val >= MOD) val -= MOD;
        return *this;
    }
    constexpr Fp& operator -= (const Fp& r) noexcept {
        val -= r.val;
        if (val < 0) val += MOD;
        return *this;
    }
    constexpr Fp& operator *= (const Fp& r) noexcept {
        val = val * r.val % MOD;
        return *this;
    }
    constexpr Fp& operator /= (const Fp& r) noexcept {
        long long a = r.val, b = MOD, u = 1, v = 0;
        while (b) {
            long long t = a / b;
            a -= t * b; swap(a, b);
            u -= t * v; swap(u, v);
        }
        val = val * u % MOD;
        if (val < 0) val += MOD;
        return *this;
    }
    constexpr bool operator == (const Fp& r) const noexcept {
        return this->val == r.val;
    }
    constexpr bool operator != (const Fp& r) const noexcept {
        return this->val != r.val;
    }
    friend constexpr ostream& operator << (ostream &os, const Fp<MOD>& x) noexcept {
        return os << x.val;
    }
    friend constexpr Fp<MOD> modpow(const Fp<MOD> &a, long long n) noexcept {
        if (n == 0) return 1;
        auto t = modpow(a, n / 2);
        t = t * t;
        if (n & 1) t = t * a;
        return t;
    }
};

//-------------------------------------------------------------

// パスカルの三角形
//C[n][k] -> nCk
long long C[51][51];

void comb_table(int N){
  for(int i = 0; i <= N; ++i){
    for(int j = 0; j <= i; ++j){
      if(j == 0 || j == i){
        C[i][j] = 1;
      }
      else{
        C[i][j] = (C[i-1][j-1] + C[i-1][j]);
      }
    }
  }
}

//-------------------------------------------------------------

int main(void){
    //二項係数
    {
        // 前処理
        COMinit();

        // 計算例
        cout << COM(100000, 50000) << endl;
    }

    //拡張Euclidの互除法
    {
        // mod. 13 での逆元を求めてみる
        for (int i = 1; i < 13; ++i) {
            cout << i << " 's inv: " << modinv(i, 13) << endl;
        }
    }

    {
        mint a = 423343;
        mint b = 74324;
        mint c = 13231;
        mint d = 8432455;

        cout << (a * b + c) / d << endl;
    }

    // パスカルの三角形
    // 準備にO(N^2)かかるため、Nが少ないとき用
    // 余りは出さないので、値は正確
    {
        // 50までの計算
        comb_table(50);
        // 10C3
        std::cout << C[10][3] << std::endl;
    }
    return 0;
}