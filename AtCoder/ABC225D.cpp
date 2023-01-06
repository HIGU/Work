#include <iostream>
#include <vector>
using namespace std;

int main(){
    ll n,q;cin>>n>>q;
    ll nil=-1;
    vector<ll>front(n+1,nil); // front[i] := 電車iの前部に連結している電車。ないならnil。
    vector<ll>back(n+1,nil);  // back[i]  := 電車iの後部に連結している電車。ないならnil。
    while(q--){
        ll c;cin>>c;
        if(c==1){
            ll x,y;cin>>x>>y;
            back[x] = y;
            front[y] = x;
        }else if(c==2){
            ll x,y;cin>>x>>y;
            back[x] = nil;
            front[y] = nil;
        }else{
            ll x;cin>>x;
            while(front[x] != nil){
                x = front[x]; // xの連結成分の、先頭に移動
            }
            vector<ll>ans;
            while(x != nil){
                ans.push_back(x);
                x = back[x];
            }
            cout<<ans.size()<<" ";
            for(ll i=0;i<=(int)ans.size()-1;i++){
                cout<<ans[i];
                if(i!=(int)ans.size()-1)cout<<" ";
                else cout<<endl;
            }
        }
    }
    return 0;
}