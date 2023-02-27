//#include <boost/foreach.hpp>
//#include <vector>
//#include <algorithm>
#include <iostream>
#include <map>

int main ()//int argc, char *argv[]
{
        std::map<int,char> my_map = {{1, 'a'}};
        auto search = my_map.find(1);
        if(search == my_map.end()){
            std::cout << "not find";
        }
        else{
            std::cout << "find";
        }

        return 0;
}