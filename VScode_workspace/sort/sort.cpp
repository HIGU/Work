#include <iostream>
//#include <boost/foreach.hpp>
#include <vector>
#include <algorithm>

int main (int argc, char *argv[])
{
        std::vector<std::string> v;
 
        v.push_back ("Vessel 1");
        v.push_back ("Vessel 3");
        v.push_back ("Vessel 10");
        v.push_back ("Vessel 2");
        v.push_back ("Vessel 24");
 
        std::cout << "Before sort" << std::endl;
        // std::cout << v[0].c_str() << std::endl;
        // std::cout << v[1].c_str() << std::endl;
        // std::cout << v[2].c_str() << std::endl;
        // std::cout << v[3].c_str() << std::endl;
        //dump (v);

        for(const auto& itr : v){
                std::cout << itr.c_str() << std::endl;
        }
 
        std::sort(v.begin(), v.end() );
 
        std::cout << "After sort" << std::endl;
        // std::cout << v[0].c_str() << std::endl;
        // std::cout << v[1].c_str() << std::endl;
        // std::cout << v[2].c_str() << std::endl;
        // std::cout << v[3].c_str() << std::endl;
        //dump (v);

        for(const auto& itr : v){
                std::cout << itr.c_str() << std::endl;
        }

        return 0;
}