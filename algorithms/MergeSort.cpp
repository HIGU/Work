#include <iostream>
#include <vector>
using namespace std;

void Merge(vector<long long>& a, vector<long long>& b, int mid, int left, int right){
    int i = left;
    int j = mid;
    int k = 0;
    while(i < mid && j < right){
        if(a[i] <= a[j]){
            b[k++] = a[i++];
        }
        else{
            b[k++] = a[j++];
        }
    }
    if(i == mid){
        while(j < right){
            b[k++] = a[j++];
        }
    }
    else{
        while(i < mid){
            b[k++] = a[i++];
        }
    }
    for(int l = 0; l < k; ++l){
        a[left+l] = b[l];
    }
}

void MergeSort(vector<long long>& a, vector<long long>& b, int left, int right){
    if(left == right || left == right - 1) return;
    int mid = (left + right) / 2;
    MergeSort(a, b, left, mid);
    MergeSort(a, b, mid, right);
    Merge(a, b, mid, left, right);
}

int main() {
    int N;
    cin >> N;
    vector<long long> A(N), B(N, 0);
    for(int i = 0; i < N; ++i) cin >> A[i];
    MergeSort(A, B, 0, N);
    for(int i = 0; i < N; ++i) {
        if(i != 0) cout << " ";
        cout << A[i];
    }
    cout << endl;
    return 0;
}