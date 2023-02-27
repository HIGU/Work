#include <iostream>
#include <vector>
#include <algorithm>

struct SegmentTree{
private:
    int n;
    std::vector<int> node;

public:
    SegmentTree(std::vector<int> v){
        int sz = (int)v.size();
        n = 1;
        while(n < sz) n *= 2;
        node.resize(2*n-1, INT_MAX);

        for(int i = 0; i < sz; ++i) node[i+n-1] = v[i];
        for(int i = n-2; i >= 0; --i) node[i] = (std::min)(node[2*i+1], node[2*i+2]);
    }

    void update(int x, int val){
        x += (n-1);

        node[x] = val;
        while(x > 0){
            x = (x-1)/2;
            node[x] = (std::min)(node[2*x+1], node[2*x+2]);
        }
    }

    int getmin(int a, int b, int k=0, int l=0, int r=-1){
        if(r < 0) r = n;
        if(r <= a || b <= l) return INT_MAX;
        if(a <= l && r <= b) return node[k];

        int vl = getmin(a, b, 2*k+1, l, (l+r)/2);
        int vr = getmin(a, b, 2*k+2, (l+r)/2, r);
        return (std::min)(vl, vr);
    }
};

int main(void){
    return 0;
}