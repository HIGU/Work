#include <iostream>
#include <fstream>
#include <vector>
#include <string>
#include <sstream>
#include <iomanip>
#include <cmath>
using namespace std;

int main(){
	std::fstream fs;
	fs.open("20230220170714_Log.txt");
	if(!fs.is_open()){
		std::cout << "Failed to  open fstream" << std::endl;
		return 0;
	}

	//','区切りでファイルを読む
	char delimeter = ':';
	std::vector<std::vector<std::string>> lines;
	std::string line;
	while(std::getline(fs, line)){
		std::vector<std::string> strs;
		std::istringstream buffer(line);
		std::string val;
		while(std::getline(buffer, val, delimeter)){
			strs.push_back(val);
		}
		lines.push_back(strs);
	}
	fs.close();

	std::ofstream ofs;
    ofs.open("temp.csv", std::ios::out);
    if(!ofs.is_open()){
        std::cout << "Failed to open ofstream" << std::endl;
        return 0;
    }
	
	for(int i = 0; i < lines.size(); ++i){
		if(lines[i].size() != 6) continue;
		ofs << lines[i][1] << "," << lines[i][2] << std::endl;
	}
	ofs.close();

    return 0;
}