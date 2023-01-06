#include <iostream>
using namespace std;

#if 0
int main()
{
    int a[3];
    cin >> a[0] >> a[1] >> a[2];

    int max = a[0] + a[2];
    for(int i = 0; i < 2; i++){
        int compare = a[i] + a[i+1];
        if(max < compare){
            max = compare;
        }
    }

    cout << max << endl;

    return 0;
}
#endif

#include <algorithm>

int main()
{
    int a[3];
    cin >> a[0] >> a[1] >> a[2];

    sort(a, a+2);

    cout << a[1] + a[2] << endl;

    return 0;
}