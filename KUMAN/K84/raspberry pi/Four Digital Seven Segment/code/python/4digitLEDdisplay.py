#!/usr/bin/env python
import RPi.GPIO as GPIO
import time


pins = {'pinA':3, 'pinB':5, 'pinC':21, 'pinD':8, 'pinE':10, 'pinF':11, 'pinG':12, 'pinDP':13, 'pin_1':15, 'pin_2':16, 'pin_3':18, 'pin_4':19}


def init():
	GPIO.setmode(GPIO.BOARD)
	for i in pins:
		GPIO.setup(pins[i], GPIO.OUT)
	print 'gpio init completed!'
	
def bitSelect(bitNum):
	if(bitNum == 1):
		GPIO.output(pins['pin_1'], GPIO.HIGH)
		GPIO.output(pins['pin_2'], GPIO.LOW)
		GPIO.output(pins['pin_3'], GPIO.LOW)
		GPIO.output(pins['pin_4'], GPIO.LOW)
	elif(bitNum == 2):
		GPIO.output(pins['pin_1'], GPIO.LOW)
		GPIO.output(pins['pin_2'], GPIO.HIGH)
		GPIO.output(pins['pin_3'], GPIO.LOW)
		GPIO.output(pins['pin_4'], GPIO.LOW)
	elif(bitNum == 3):
		GPIO.output(pins['pin_1'], GPIO.LOW)
		GPIO.output(pins['pin_2'], GPIO.LOW)
		GPIO.output(pins['pin_3'], GPIO.HIGH)
		GPIO.output(pins['pin_4'], GPIO.LOW)
	elif(bitNum == 4):
		GPIO.output(pins['pin_1'], GPIO.LOW)
		GPIO.output(pins['pin_2'], GPIO.LOW)
		GPIO.output(pins['pin_3'], GPIO.LOW)
		GPIO.output(pins['pin_4'], GPIO.HIGH)
	else:
		GPIO.output(pins['pin_1'], GPIO.LOW)
		GPIO.output(pins['pin_2'], GPIO.LOW)
		GPIO.output(pins['pin_3'], GPIO.LOW)
		GPIO.output(pins['pin_4'], GPIO.LOW)
	print 'bitSelect completed!'
	
	
def display_0():
	GPIO.output(pins['pinA'], GPIO.LOW)
	GPIO.output(pins['pinB'], GPIO.LOW)
	GPIO.output(pins['pinC'], GPIO.LOW)
	GPIO.output(pins['pinD'], GPIO.LOW)
	GPIO.output(pins['pinE'], GPIO.LOW)
	GPIO.output(pins['pinF'], GPIO.LOW)
	GPIO.output(pins['pinG'], GPIO.HIGH)
	GPIO.output(pins['pinDP'], GPIO.HIGH)
	print 'display number 0'

def display_1():
	GPIO.output(pins['pinA'], GPIO.HIGH)
	GPIO.output(pins['pinB'], GPIO.LOW)
	GPIO.output(pins['pinC'], GPIO.LOW)
	GPIO.output(pins['pinD'], GPIO.HIGH)
	GPIO.output(pins['pinE'], GPIO.HIGH)
	GPIO.output(pins['pinF'], GPIO.HIGH)
	GPIO.output(pins['pinG'], GPIO.HIGH)
	GPIO.output(pins['pinDP'], GPIO.HIGH)
	print 'display number 1'
	
	
def display_2():
	GPIO.output(pins['pinA'], GPIO.LOW)
	GPIO.output(pins['pinB'], GPIO.LOW)
	GPIO.output(pins['pinC'], GPIO.HIGH)
	GPIO.output(pins['pinD'], GPIO.LOW)
	GPIO.output(pins['pinE'], GPIO.LOW)
	GPIO.output(pins['pinF'], GPIO.HIGH)
	GPIO.output(pins['pinG'], GPIO.LOW)
	GPIO.output(pins['pinDP'], GPIO.HIGH)
	print 'display number 2'
	
def display_3():
	GPIO.output(pins['pinA'], GPIO.LOW)
	GPIO.output(pins['pinB'], GPIO.LOW)
	GPIO.output(pins['pinC'], GPIO.LOW)
	GPIO.output(pins['pinD'], GPIO.LOW)
	GPIO.output(pins['pinE'], GPIO.HIGH)
	GPIO.output(pins['pinF'], GPIO.HIGH)
	GPIO.output(pins['pinG'], GPIO.LOW)
	GPIO.output(pins['pinDP'], GPIO.HIGH)
	print 'display number 3'	
	
def display_4():
	GPIO.output(pins['pinA'], GPIO.HIGH)
	GPIO.output(pins['pinB'], GPIO.LOW)
	GPIO.output(pins['pinC'], GPIO.LOW)
	GPIO.output(pins['pinD'], GPIO.HIGH)
	GPIO.output(pins['pinE'], GPIO.HIGH)
	GPIO.output(pins['pinF'], GPIO.LOW)
	GPIO.output(pins['pinG'], GPIO.LOW)
	GPIO.output(pins['pinDP'], GPIO.HIGH)
	print 'display number 4'	
	
def display_5():
	GPIO.output(pins['pinA'], GPIO.LOW)
	GPIO.output(pins['pinB'], GPIO.HIGH)
	GPIO.output(pins['pinC'], GPIO.LOW)
	GPIO.output(pins['pinD'], GPIO.LOW)
	GPIO.output(pins['pinE'], GPIO.HIGH)
	GPIO.output(pins['pinF'], GPIO.LOW)
	GPIO.output(pins['pinG'], GPIO.LOW)
	GPIO.output(pins['pinDP'], GPIO.HIGH)
	print 'display number 5'	
	
def display_6():
	GPIO.output(pins['pinA'], GPIO.LOW)
	GPIO.output(pins['pinB'], GPIO.HIGH)
	GPIO.output(pins['pinC'], GPIO.LOW)
	GPIO.output(pins['pinD'], GPIO.LOW)
	GPIO.output(pins['pinE'], GPIO.LOW)
	GPIO.output(pins['pinF'], GPIO.LOW)
	GPIO.output(pins['pinG'], GPIO.LOW)
	GPIO.output(pins['pinDP'], GPIO.HIGH)
	print 'display number 6'


def display_7():
	GPIO.output(pins['pinA'], GPIO.LOW)
	GPIO.output(pins['pinB'], GPIO.LOW)
	GPIO.output(pins['pinC'], GPIO.LOW)
	GPIO.output(pins['pinD'], GPIO.HIGH)
	GPIO.output(pins['pinE'], GPIO.HIGH)
	GPIO.output(pins['pinF'], GPIO.HIGH)
	GPIO.output(pins['pinG'], GPIO.HIGH)
	GPIO.output(pins['pinDP'], GPIO.HIGH)
	print 'display number 7'	
	
def display_8():
	GPIO.output(pins['pinA'], GPIO.LOW)
	GPIO.output(pins['pinB'], GPIO.LOW)
	GPIO.output(pins['pinC'], GPIO.LOW)
	GPIO.output(pins['pinD'], GPIO.LOW)
	GPIO.output(pins['pinE'], GPIO.LOW)
	GPIO.output(pins['pinF'], GPIO.LOW)
	GPIO.output(pins['pinG'], GPIO.LOW)
	GPIO.output(pins['pinDP'], GPIO.HIGH)
	print 'display number 8'	
	
	
def display_9():
	GPIO.output(pins['pinA'], GPIO.LOW)
	GPIO.output(pins['pinB'], GPIO.LOW)
	GPIO.output(pins['pinC'], GPIO.LOW)
	GPIO.output(pins['pinD'], GPIO.LOW)
	GPIO.output(pins['pinE'], GPIO.HIGH)
	GPIO.output(pins['pinF'], GPIO.LOW)
	GPIO.output(pins['pinG'], GPIO.LOW)
	GPIO.output(pins['pinDP'], GPIO.HIGH)
	print 'display number 9'
	
def display_dp():
	GPIO.output(pins['pinA'], GPIO.HIGH)
	GPIO.output(pins['pinB'], GPIO.HIGH)
	GPIO.output(pins['pinC'], GPIO.HIGH)
	GPIO.output(pins['pinD'], GPIO.HIGH)
	GPIO.output(pins['pinE'], GPIO.HIGH)
	GPIO.output(pins['pinF'], GPIO.HIGH)
	GPIO.output(pins['pinG'], GPIO.HIGH)
	GPIO.output(pins['pinDP'], GPIO.LOW)
	print 'display DP'	


def clear():	#clear the screen
	GPIO.output(pins['pinA'], GPIO.HIGH)
	GPIO.output(pins['pinB'], GPIO.HIGH)
	GPIO.output(pins['pinC'], GPIO.HIGH)
	GPIO.output(pins['pinD'], GPIO.HIGH)
	GPIO.output(pins['pinE'], GPIO.HIGH)
	GPIO.output(pins['pinF'], GPIO.HIGH)
	GPIO.output(pins['pinG'], GPIO.HIGH)
	GPIO.output(pins['pinDP'], GPIO.HIGH)
	print 'clear the screen!'
	
def pickNum(number):
	if(number == 0):
		display_0()
	elif(number == 1):
		display_1()
	elif(number == 2):
		display_2()
	elif(number == 3):
		display_3()
	elif(number == 4):
		display_4()
	elif(number == 5):
		display_5()
	elif(number == 6):
		display_6()
	elif(number == 7):
		display_7()
	elif(number == 8):
		display_8()
	elif(number == 9):
		display_9()
	else:
		clear()

		
def Display(Bit, Number):
	bitSelect(Bit)
	pickNum(Number)
	time.sleep(0.001)

def loop():
    while True:
        Display(1,1)
        time.sleep(1)
        Display(2,2)
        time.sleep(1)
        Display(3,3)
        time.sleep(1)
        Display(4,4)
        time.sleep(1)
	
if __name__ == '__main__':
	try:
		init()
                loop()
	except KeyboardInterrupt:
		GPIO.cleanup()
		print 'Key Board Interrupt!'































