#include <iostream>
#include <fstream>
#include <vector>
#include <string>
using namespace std;

static void SplitMessage(const std::string message, std::vector<std::string>& split_message){
	std::string mes = message;
	split_message.clear();
	while(1){
		auto pos = mes.find(":");
		if(pos == std::string::npos) break;
		split_message.push_back(mes.substr(0, pos));
		mes.erase(0, pos+1);
	}
	split_message.push_back(mes);
}

int main(){
    std::string aaa;
    cin >> aaa;
    std::vector<std::string> splits;
    SplitMessage(aaa, splits);
    for(int i = 0; i < (int)splits.size(); ++i){
        std::cout << splits[i] << std::endl;
    }
    return 0;
}