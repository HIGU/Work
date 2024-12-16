#include <iostream>
#include <stdlib.h>
#include <conio.h>
#include <time.h>

static const int FIELD_WIDTH = 9;
static const int FIELD_HEIGHT = 9;

static const int BOMB_COUNT = 10;

int cursorX;
int cursorY;

struct Cell
{
    bool isExistBomb;
    bool isHiddenByMine;
    bool isOnFlag;
};

Cell cells[FIELD_WIDTH][FIELD_HEIGHT];

int getAdjacentBombsCount(int x, int y);
void autoRemoveMines(int argX, int argY);

int main()
{
    // initialize. 
    srand(static_cast<unsigned int>(time(NULL)));
    {
        int count = 0;
        while(count < BOMB_COUNT)
        {
            int x = rand() % FIELD_WIDTH;
            int y = rand() % FIELD_HEIGHT;
            if (false == cells[x][y].isExistBomb)
            {
                cells[x][y].isExistBomb = true;
                ++count;
            }
        }
    }

    for (int y = 0; y < FIELD_HEIGHT; ++y)
    {
        for (int x = 0; x < FIELD_WIDTH; ++x)
        {
            cells[x][y].isHiddenByMine = true;
        }
    }

    bool isExplosion = false;
    bool isClear = false;

    while (1)
    {
        system("cls");
        for (int y = 0; y < FIELD_HEIGHT; ++y)
        {
            for (int x = 0; x < FIELD_WIDTH; ++x)
            {
                if ((cursorX == x) && (cursorY == y))
                {
                    if (true == isExplosion)
                    {
                        std::cout << "Å¶";
                    }
                    else
                    {
                        std::cout << "Åù";
                    }
                }
                else if (true == cells[x][y].isOnFlag)
                {
                    std::cout << "Å£";
                }
                else if (true == cells[x][y].isHiddenByMine)
                {
                    std::cout << "Å°";
                }
                else if (true == cells[x][y].isExistBomb)
                {
                    std::cout << "Åú";
                }
                else
                {
                    int AdjacentBombs = getAdjacentBombsCount(x, y);
                    if (AdjacentBombs > 0)
                    {
                        char str[] = "ÅZ";
                        str[1] += AdjacentBombs;
                        std::cout << str;
                    }
                    else
                    {
                        std::cout << "ÅE";
                    }
                }
            }
            std::cout << std::endl;
        }

        // Game Over. 
        if (true == isExplosion)
        {
            std::cout << "Game Over...";
            std::cout << "\a";
            _getch();
            break;
        }

        // Game Clear. 
        if (true == isClear)
        {
            std::cout << "Game Clear!";
            std::cout << "\a";
            _getch();
            break;
        }

        switch (_getch())
        {
        case 'w':
            --cursorY;
            break;
        case 's':
            ++cursorY;
            break;
        case 'a':
            --cursorX;
            break;
        case 'd':
            ++cursorX;
            break;
        /* DEBUG
        case 'b':
            cells[cursorX][cursorY].isExistBomb = !cells[cursorX][cursorY].isExistBomb;
            break;
        case 'm':
            cells[cursorX][cursorY].isHiddenByMine = !cells[cursorX][cursorY].isHiddenByMine;
            break;
        */
        case 'f':
            cells[cursorX][cursorY].isOnFlag = !cells[cursorX][cursorY].isOnFlag;
            break;
        default:
            // disable to remove mine. 
            if (true == cells[cursorX][cursorY].isOnFlag)
            {
                break;
            }
            cells[cursorX][cursorY].isHiddenByMine = false;

            // Judge Game Over. 
            if (true == cells[cursorX][cursorY].isExistBomb)
            {
                isExplosion = true;
                for (int y = 0; y < FIELD_HEIGHT; ++y)
                {
                    for (int x = 0; x < FIELD_WIDTH; ++x)
                    {
                        cells[x][y].isHiddenByMine = false;
                        cells[x][y].isOnFlag = false;
                    }
                }
                break;
            }

            // Judge Game Clear. 
            {
                isClear = true;
                for (int y = 0; y < FIELD_HEIGHT; ++y)
                {
                    for (int x = 0; x < FIELD_WIDTH; ++x)
                    {
                        if ((false == cells[x][y].isExistBomb) && (true == cells[x][y].isHiddenByMine))
                        {
                            isClear = false;
                        }
                    }
                }
            }

            // Remove Adjacent Mines Automatically. 
            for (int y = -1; y <= 1; ++y)
            {
                for (int x = -1; x <= 1; ++x)
                {
                    if ((0 == x) && (0 == y))
                    {
                        continue;
                    }

                    autoRemoveMines(cursorX + x, cursorY + y);
                }
            }
            break;
        }
    }
    return 0;
}

int getAdjacentBombsCount(int argX, int argY)
{
    int count = 0;

    for (int y = -1; y <= 1; ++y)
    {
        for (int x = -1; x <= 1; ++x)
        {
            if ((0 == x) && (0 == y))
            {
                continue;
            }
            if ((argX + x < 0) || (argX + x >= FIELD_WIDTH) || (argY + y < 0) || (argY + y >= FIELD_HEIGHT))
            {
                continue;
            }
            if (true == cells[argX + x][argY + y].isExistBomb)
            {
                ++count;
            }
        }
    }
    return count;
}

void autoRemoveMines(int argX, int argY)
{
    if ((true == cells[argX][argY].isExistBomb) 
        || (false == cells[argX][argY].isHiddenByMine)
        || (argX < 0) || (argX >= FIELD_WIDTH) || (argY < 0) || (argY >= FIELD_HEIGHT)
        || (getAdjacentBombsCount(argX, argY) > 0))
    {
        return;
    }

    cells[argX][argY].isHiddenByMine = false;

    for (int y = -1; y <= 1; ++y)
    {
        for (int x = -1; x <= 1; ++x)
        {
            if ((0 == x) && (0 == y))
            {
                continue;
            }
            autoRemoveMines(argX + x, argY + y);
        }
    }
}