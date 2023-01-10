#include <iostream>
#include <vector>
#include <algorithm>

//座標圧縮
// {100, 120, 130, 100, 250, 10, 130} -> {1, 2, 3, 1, 4, 0, 3}
void CoordinateCompression(const std::vector<int>& vec, std::vector<int>& out){
    std::vector<int> temp_vec;
    for(int i = 0; i < (int)vec.size(); ++i) temp_vec.push_back(vec[i]);
    std::sort(temp_vec.begin(), temp_vec.end());
    temp_vec.erase(std::unique(temp_vec.begin(), temp_vec.end()), temp_vec.end());
    for(int i = 0; i < (int)vec.size(); ++i){
        int h = std::lower_bound(temp_vec.begin(), temp_vec.end(), vec[i]) - temp_vec.begin();
        out.push_back(h);
    }
}

int main(void){
    std::vector<int> vec = {100, 120, 130, 100, 250, 10, 130};
    std::vector<int> out_vec;
    CoordinateCompression(vec, out_vec);
    for(int i = 0; i < (int)out_vec.size(); ++i){
        if(i != 0) std::cout << " ";
        std::cout << out_vec[i];
    }
    std::cout << std::endl;
    return 0;
}