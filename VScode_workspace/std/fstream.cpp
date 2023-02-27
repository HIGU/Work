#include <iostream>
#include <fstream>
#include <sstream>
#include <string>
#include <vector>

int main(void){
    //入力
    std::ifstream ifs;
    ifs.open("test.raw", std::ios::in | std::ios::binary);
    if(!ifs.is_open()){
        std::cout << "Failed to open ifstream" << std::endl;
        return 0;
    }
    //バイナリデータの読み出し
    int file_size = 512*512*256;
    std::vector<char> data_buffer(file_size);
    ifs.read(&data_buffer[0], file_size);
    //スコープが外れると勝手にcloseされるが、念のためcloseする
    ifs.close();

    //出力
    std::ofstream ofs;
    //std::ios::app -> 追加書き込み（書き込みの際にポインタがファイル末尾に移動する）
    //std::ios::end -> 書き込み位置を末尾に移動する
    ofs.open("text2.raw", std::ios::out | std::ios::binary);
    if(!ofs.is_open()){
        std::cout << "Failed to open ofstream" << std::endl;
        return 0;
    }
    //バイナリデータの書き出し
    ofs.write((char*)&data_buffer[0], file_size);
    //バイナリでない場合は、以下のようにも出力できる
    //ofs << "aiueo" << std::endl;
    ofs.close();

    //入出力
    {
        std::fstream fs;
        fs.open("test.txt");
        if(!fs.is_open()){
            std::cout << "Failed to  open fstream" << std::endl;
            return 0;
        }

        //','区切りでファイルを読む
        char delimeter = ',';
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
    }

    return 0;
}