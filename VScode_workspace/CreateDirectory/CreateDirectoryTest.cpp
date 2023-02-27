#include <iostream>
//#include <windows.h>
#include <shlobj.h>
#include <atlbase.h>

int main(){

    int err = ::SHCreateDirectoryEx(NULL, L"./Test", NULL);
    std::cout << err << std::endl;

    return 0;
}