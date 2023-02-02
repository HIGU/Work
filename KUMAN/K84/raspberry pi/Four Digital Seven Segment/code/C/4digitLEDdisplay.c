#include <wiringPi.h>
#include <stdio.h>
//display 1234
//Set cathode interface
int a = 8;
int b = 9;
int c = 13;
int d = 15;
int e = 16;
int f = 0;
int g = 1;
int dp = 2;
//Set anode interface
int d4 = 12;
int d3 = 5;
int d2 = 4;
int d1 = 3;
//Set variable
long n = 1230;
int x = 100;
int del = 55;  //Here to fine tune the clock	 
void init()
{
	pinMode(d1, OUTPUT);
	pinMode(d2, OUTPUT);
	pinMode(d3, OUTPUT);
	pinMode(d4, OUTPUT);
	pinMode(a, OUTPUT);
	pinMode(b, OUTPUT);
	pinMode(c, OUTPUT);
	pinMode(d, OUTPUT);
	pinMode(e, OUTPUT);
	pinMode(f, OUTPUT);
	pinMode(g, OUTPUT);
	pinMode(dp, OUTPUT);
}
/////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////
void bitSelect(unsigned char n)//
{
    switch(n)
     {
	case 1: 
	  digitalWrite(d1,HIGH);
 	  digitalWrite(d2, LOW);
	  digitalWrite(d3, LOW);
	  digitalWrite(d4, LOW);   
	 break;
	 case 2: 
	  digitalWrite(d1, LOW);
 	  digitalWrite(d2, HIGH);
	  digitalWrite(d3, LOW);
	  digitalWrite(d4, LOW); 
	    break;
	  case 3: 
	    digitalWrite(d1,LOW);
 	   digitalWrite(d2, LOW);
	   digitalWrite(d3, HIGH);
	   digitalWrite(d4, LOW); 
	    break;
	  case 4: 
	   digitalWrite(d1, LOW);
 	   digitalWrite(d2, LOW);
	   digitalWrite(d3, LOW);
	   digitalWrite(d4, HIGH); 
	    break;
        default :
           digitalWrite(d1, LOW);
	   digitalWrite(d2, LOW);
	   digitalWrite(d3, LOW);
	   digitalWrite(d4, LOW);
        break;
	  }
}
void Num_0()
{
  digitalWrite(a, LOW);
  digitalWrite(b, LOW);
  digitalWrite(c, LOW);
  digitalWrite(d, LOW);
  digitalWrite(e, LOW);
  digitalWrite(f, LOW);
  digitalWrite(g, HIGH);
  digitalWrite(dp,HIGH);
}
void Num_1()
{
  digitalWrite(a, HIGH);
  digitalWrite(b, LOW);
  digitalWrite(c, LOW);
  digitalWrite(d, HIGH);
  digitalWrite(e, HIGH);
  digitalWrite(f, HIGH);
  digitalWrite(g, HIGH);
  digitalWrite(dp,HIGH);
}
void Num_2()
{
  digitalWrite(a, LOW);
  digitalWrite(b, LOW);
  digitalWrite(c, HIGH);
  digitalWrite(d, LOW);
  digitalWrite(e, LOW);
  digitalWrite(f, HIGH);
  digitalWrite(g, LOW);
  digitalWrite(dp,HIGH);
}
void Num_3()
{
  digitalWrite(a, LOW);
  digitalWrite(b, LOW);
  digitalWrite(c, LOW);
  digitalWrite(d, LOW);
  digitalWrite(e, HIGH);
  digitalWrite(f, HIGH);
  digitalWrite(g, LOW);
  digitalWrite(dp,HIGH);
}
void Num_4()
{
  digitalWrite(a, HIGH);
  digitalWrite(b, LOW);
  digitalWrite(c, LOW);
  digitalWrite(d, HIGH);
  digitalWrite(e, HIGH);
  digitalWrite(f, LOW);
  digitalWrite(g, LOW);
  digitalWrite(dp,HIGH);
}
void Num_5()
{
  digitalWrite(a, LOW);
  digitalWrite(b, HIGH);
  digitalWrite(c, LOW);
  digitalWrite(d, LOW);
  digitalWrite(e, HIGH);
  digitalWrite(f, LOW);
  digitalWrite(g, LOW);
  digitalWrite(dp,HIGH);
}
void Num_6()
{
  digitalWrite(a, LOW);
  digitalWrite(b, HIGH);
  digitalWrite(c, LOW);
  digitalWrite(d, LOW);
  digitalWrite(e, LOW);
  digitalWrite(f, LOW);
  digitalWrite(g, LOW);
  digitalWrite(dp,HIGH);
}
void Num_7()
{
  digitalWrite(a, LOW);
  digitalWrite(b, LOW);
  digitalWrite(c, LOW);
  digitalWrite(d, HIGH);
  digitalWrite(e, HIGH);
  digitalWrite(f, HIGH);
  digitalWrite(g, HIGH);
  digitalWrite(dp,HIGH);
}
void Num_8()
{
  digitalWrite(a, LOW);
  digitalWrite(b, LOW);
  digitalWrite(c, LOW);
  digitalWrite(d, LOW);
  digitalWrite(e, LOW);
  digitalWrite(f, LOW);
  digitalWrite(g, LOW);
  digitalWrite(dp,HIGH);
}
void Num_9()
{
  digitalWrite(a, LOW);
  digitalWrite(b, LOW);
  digitalWrite(c, LOW);
  digitalWrite(d, LOW);
  digitalWrite(e, HIGH);
  digitalWrite(f, LOW);
  digitalWrite(g, LOW);
  digitalWrite(dp,HIGH);
}
void Clear()  // Clear the screen
{
  digitalWrite(a, HIGH);
  digitalWrite(b, HIGH);
  digitalWrite(c, HIGH);
  digitalWrite(d, HIGH);
  digitalWrite(e, HIGH);
  digitalWrite(f, HIGH);
  digitalWrite(g, HIGH);
  digitalWrite(dp,HIGH);
}
void pickNumber(unsigned char n)//Choose the number of
{
  switch(n)
  {
   case 0:Num_0();
   break;
   case 1:Num_1();
   break;
   case 2:Num_2();
   break;
   case 3:Num_3();
   break;
   case 4:Num_4();
   break;
   case 5:Num_5();
   break;
   case 6:Num_6();
   break;
   case 7:Num_7();
   break;
   case 8:Num_8();
   break;
   case 9:Num_9();
   break;
   default:Clear();
   break; 
  }
}
void Display(unsigned char x, unsigned char Number)//Show that x is the coordinate, Number is the number
{
	bitSelect(x);
	pickNumber(Number);
	delay(1);
	//Clear() ; //Vanishing
}

int main(void)
{
	if(wiringPiSetup() == -1)
	{
		printf("wiringPi setup failed!\n");
		return -1;
	}
	init();
	while(1)
	{
		Display(1, 1);
		delay(1000);
		Display(2, 2);
		delay(1000);
		Display(3, 3);
		delay(1000);
		Display(4, 4);
		delay(1000);
	}
	
 

}
