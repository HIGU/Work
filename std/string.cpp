#include <iostream>
#include <string>
#include <algorithm>

int main(void){
    std::string s1 = "abcdef";
    // 1bcdef
    s1[0] = '1';

    size_t pos = s1.find('c');
    if(pos != std::string::npos)
    {
        // 1b345def
        s1.replace(pos, 1, "345");
    }

    // 速度的には遅い？
    std::string s2 = s1.substr(2, 3);
    // 345
    std::cout << s2 << std::endl;

    std::string s3 = "abcdef";
    // abdef
    s3.erase(2, 1);

    //axy -> yxa
    std::string t = "axy";
    std::sort(t.begin(), t.end(), std::greater<char>());
    
    return 0;
}