#pragma GCC target ("avx2")
#pragma GCC optimization ("O3")
#pragma GCC optimization ("unroll-loops")
#include <algorithm>
#include <assert.h>
#include <bitset>
#include <cfloat>
#include <complex>
#include <deque>
#include <fstream>
#include <functional>
#include <iomanip>
#include <iostream>
#include <limits.h>
#include <list>
#include <map>
#include <math.h>
#include <queue>
#include <random>
#include <set>
#include <stack>
#include <string>
#include <string.h>
#include <time.h>
#include <unordered_map>
#include <unordered_set>
#include <vector>
#define rep(i,n) for(int i=0;i<n;i++)
#define REP(i,n) for(int i=1;i<=n;i++)
#define ll long long
#define eps LDBL_EPSILON
#define mod (int)1000000007
#define INF INT_MAX/10
#define P pair<int,int>
#define prique(T) priority_queue<T,vector<T>,greater<T>>
#define all(V) V.begin(),V.end()
using namespace std;
 
class ConvexHullTrick {
	bool minOrMax, lineMonotone;
	class Line {
	public:
		int a, b;
		bool isquery;
		mutable std::function<const Line * ()> getSuc;
		bool operator<(const Line& x) const {
			if (isquery) {
				const Line* suc = next(this);
				if (suc == nullptr) return true;
				return (suc->a - x.a) * a + suc->b - x.b > 0;
			}
			if (x.isquery) {
				const Line* suc = next(this);
				if (suc == nullptr) return false;
				return (suc->a - a) * x.a + suc->b - b < 0;
			}
			return a < x.a;
		}
	};
	bool isbad(const set<Line>::iterator x) {
		if (x == st.begin() || next(x) == st.end())return false;
		auto pre = prev(x), nex = next(x);
		if (((*x).b - (*pre).b) * ((*nex).a - (*x).a) >= ((*nex).b - (*x).b) * ((*x).a - (*pre).a))return true;
		return false;
	}
	bool isbad(const vector<Line>::iterator x) {
		if (x == vec.begin() || next(x) == vec.end())return false;
		auto pre = prev(x), nex = next(x);
		if (((*x).b - (*pre).b) * ((*nex).a - (*x).a) >= ((*nex).b - (*x).b) * ((*x).a - (*pre).a))return true;
		return false;
	}
	set<Line> st;
	vector<Line> vec;
public:
	ConvexHullTrick(bool minormax = false, bool lineMonotone = false) :minOrMax(minormax), lineMonotone(lineMonotone) {}
	void addLine(int a, int b) {
		if (minOrMax) {
			a = -a; b = -b;
		}
		if (!lineMonotone) {
			auto pos = st.lower_bound({ a,-INF,false });
			if (pos != st.end()) {
				if ((*pos).a == a) {
					if ((*pos).b <= b)return;
					st.erase(pos);
				}
			}
			auto ite = st.insert({ a,b,false }).first;
			ite->getSuc = [=] {return next(ite) == st.end() ? nullptr : &*next(ite); };
			if (isbad(ite)) {
				st.erase(ite);
				return;
			}
			while (next(ite) != st.end() && isbad(next(ite)))st.erase(next(ite));
			while (ite != st.begin() && isbad(prev(ite)))st.erase(prev(ite));
		}
		else {
			if (!vec.empty()) {
				if (vec.back().a > a) {
					cerr << "Line additions are not monotone" << endl;
					exit(1);
				}
				if (vec.back().a == a) {
					if (vec.back().b <= b)return;
					vec.pop_back();
				}
			}
			vec.push_back({ a,b,false });
			auto ite = --vec.end();
			int index = vec.size() - 1;
			ite->getSuc = [this, index] {cout << vec.size() << endl; return index == vec.size() - 1 ? nullptr : &*(vec.begin() + index + 1); };
			while (ite != vec.begin() && isbad(prev(ite))) {
				*prev(ite) = vec.back();
				vec.pop_back();
				ite = --vec.end();
			}
		}
	}
	int query(int x) {
		if (!lineMonotone) {
			auto l = *st.lower_bound(Line{ x, 0,true });
			if (!minOrMax)return l.a * x + l.b;
			else return -l.a * x - l.b;
		}
		else {
			auto l = *lower_bound(vec.begin(), vec.end() - 1, Line({ x,0,true }));
			if (!minOrMax)return l.a * x + l.b;
			else return -l.a * x - l.b;
		}
	}
};
 
//COLOCON2018Final-C
int n, a[200010];
signed main() {
	ConvexHullTrick cht(false, true);
	cin >> n;
	rep(i, n)cin >> a[i];
	for (int i = n - 1; i >= 0; i--)cht.addLine(-2 * i, a[i] + i * i);
	rep(i, n) {
		cout << cht.query(i) + i * i << endl;
	}
	return 0;
}