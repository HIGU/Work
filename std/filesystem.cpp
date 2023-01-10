#include <iostream>
#include <filesystem>

int main(){
    std::wstring path = L"C:\\Users\\PC1901-24\\Desktop\\VScode_workspace\\std";
    bool is_exist = std::filesystem::exists(path);
    if(!is_exist){
        //try/catchは必須
        try{
            //複数階層一気に作れる
            std::filesystem::create_directories(path);
            //上記例では、「VScode_workspace」までは存在する必要がある
            std::filesystem::create_directory(path);
        }
        catch(std::exception){
            return 1;
        }
    }

    std::wstring src = L"C:\\test.txt";
    std::wstring dist = L"C:\\Users\\PC1901-24\\Desktop\\test_2.txt";
    //distに同一名のファイルがある場合はエラーがでるため
    //try/catchで挟むべき
    std::filesystem::copy(src, dist);
    std::filesystem::remove(L"C:\\Test.txt");

    //全部削除
    //存在しない場合は0が帰る
    //指定したフォルダとその下を削除
    std::filesystem::remove_all(L"C:\\Test\\Test");

    //指定フォルダ以下全探索
    std::wstring folder_path = L"C:\\Users\\PC1901-24\\Desktop\\VScode_workspace\\code";
    for(const std::filesystem::directory_entry &i : std::filesystem::recursive_directory_iterator(folder_path)){
        if(i.is_directory()) continue;
        std::cout << "file_path: " << i.path().c_str() << std::endl;
    }
    
    return 0;
}