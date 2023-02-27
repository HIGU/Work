#include <iostream>
#include <random>

int main(void){
    // メルセンヌ・ツイスター法による疑似乱数生成器を、
    // ハードウェア乱数をシード値にして初期化
    std::random_device seed_gen;
    std::mt19937 engine(seed_gen());

    // 一様実数分布
    // std::uniform_~で種類があるみたいだが未調査
    std::uniform_int_distribution<> dist(1, 10);
    //std::uniform_real_distribution<> dist(-1.0, 1.0);

    std::vector<int> vec;
    for(int i = 0; i < 10; ++i){
        // ランダムな値を生成
        vec.emplace_back(dist(engine));
    }
    return 0;
}