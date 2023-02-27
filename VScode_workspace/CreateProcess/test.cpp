#include <iostream>
#include <windows.h>
#include <thread>
using namespace std;

int main() {
    // auto aaa = system("cd C:\\Users\\PC1901-24\\Desktop\\VScode_workspace & Exe.exe");
    // cout << aaa << endl;

    HANDLE hevent_cancel = ::CreateEvent(NULL, true, false, NULL);
    HANDLE hjob = ::CreateJobObject(NULL, NULL);
    if(hjob == NULL){
        DWORD error_code = ::GetLastError();
        cout << error_code << endl;
        return 1;
    }

    //wstring work_path = L"C:\\Users\\PC1901-24\\Desktop\\VScode_workspace\\std";
    // int size = GetCurrentDirectory(0, 0);
    // if(size > 0){
    //     unique_ptr<wchar_t> path(new wchar_t[size]);
    //     if(!path.get()) return 1;
    //     GetCurrentDirectoryW(size, path.get());
    //     work_path = path.get();
    // }
    //cout << "work_path: " << work_path.c_str() << endl;

    SECURITY_ATTRIBUTES sec_attr;
    ZeroMemory(&sec_attr, sizeof(sec_attr));
    sec_attr.bInheritHandle = TRUE;
    // wstring log_path = L"C:\\Users\\PC1901-24\\Desktop\\VScode_workspace";
    // HANDLE hOutput = CreateFileW((LPCWSTR)log_path.c_str(), GENERIC_WRITE, FILE_SHARE_WRITE, &sec_attr, CREATE_ALWAYS, FILE_ATTRIBUTE_NORMAL, NULL);

    //LPSTARTUPINFOW si;
    STARTUPINFOW si;
    PROCESS_INFORMATION pi;
    SecureZeroMemory(&si, sizeof(si));
    SecureZeroMemory(&pi, sizeof(pi));
    si.cb = sizeof(si);
    si.dwFlags = STARTF_USESTDHANDLES;
    //si.hStdOutput = hOutput;
    //si = &si_;

    DWORD creation_flags = CREATE_SUSPENDED | NORMAL_PRIORITY_CLASS;
    OSVERSIONINFO os_ver_info;
    os_ver_info.dwOSVersionInfoSize = sizeof(OSVERSIONINFO);
    if((os_ver_info.dwMajorVersion < 6) ||
    (os_ver_info.dwMajorVersion == 6 && os_ver_info.dwMinorVersion < 2)){
        creation_flags |= CREATE_BREAKAWAY_FROM_JOB;
    }
    //wstring command = L"Exe.exe";
    if(!::CreateProcessW(L"Exe.exe", NULL, &sec_attr, &sec_attr, TRUE, creation_flags, NULL, nullptr, &si, &pi)){//LPWSTR(work_path.c_str())
        cout << "Error" << endl;
        DWORD lasterr = ::GetLastError();
        cout << lasterr << endl;
        return 1;
    }

    if(!::AssignProcessToJobObject(hjob, pi.hProcess)){
        DWORD lasterr = ::GetLastError();
        cout << lasterr << endl;
        ::CloseHandle(pi.hThread);
        ::CloseHandle(pi.hProcess);
        return 1;
    }
    DWORD dRet = ::ResumeThread(pi.hThread);
    if(dRet == -1){
        ::CloseHandle(pi.hThread);
        ::CloseHandle(pi.hProcess);
        return 1;
    }
    ::CloseHandle(pi.hThread);

    auto EngineWaitThread = [&](HANDLE hjob, HANDLE hproc, HANDLE hevent_cancel, HANDLE hOutput) ->void{
        HANDLE wait_handles[] = {
            hproc,
            hevent_cancel
        };
        DWORD nwait_handles = sizeof(wait_handles) / sizeof(wait_handles[0]);

        bool is_error = true;
        DWORD timeout = 30 * 60 * 60;
        DWORD wait_result = ::WaitForMultipleObjects(nwait_handles, wait_handles, false, timeout);
        if(wait_result == WAIT_FAILED){
            cout << "Process Wait Failed." << endl;
        }
        else if(wait_result == WAIT_TIMEOUT){
            cout << "Process Timeout." << endl;
            ::TerminateJobObject(hjob, 0);
        }
        else if(wait_result >= WAIT_ABANDONED_0 && wait_result < (WAIT_ABANDONED_0 + nwait_handles)){
            cout << "Process Abandoned." << endl;
        }
        else if(wait_result >= WAIT_OBJECT_0 && wait_result < (WAIT_OBJECT_0 + nwait_handles)){
            int handle_index = wait_result - WAIT_OBJECT_0;
            if(wait_handles[handle_index] == hproc){
                cout << "Process Terminated." << endl;

                DWORD exit_code;
                ::GetExitCodeProcess(hproc, &exit_code);
                cout << "exit code is [ " << exit_code << " ]" << endl;
                if(exit_code == 0){
                    is_error = false;
                }
            }
        }
        else{
            cout << "Unknown" << endl;
        }
        ::CloseHandle(hproc);
        ::CloseHandle(hOutput);
    };

    cout << "Start" << endl;

    thread engine_thread(EngineWaitThread, hjob, pi.hProcess, hevent_cancel, si.hStdOutput);
    engine_thread.detach();
    Sleep(5000);
    cout << "End" << endl;

    return 0;
}