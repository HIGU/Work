#!/usr/bin/env python    
  
  
import RPi.GPIO as GPIO  
import time  
import signal  
import atexit  
  
atexit.register(GPIO.cleanup)    
  
servopin = 18  
GPIO.setmode(GPIO.BCM)  
GPIO.setup(servopin, GPIO.OUT, initial=False)  
p = GPIO.PWM(servopin,50) #50HZ  
p.start(2.5)  
time.sleep(1)  
  
while(True):  
  for i in range(0,181,10):  
    p.ChangeDutyCycle(2.5 + 10 * i / 180)  
    time.sleep(0.02)                        
    p.ChangeDutyCycle(0)                  
    time.sleep(0.2)  
    
  for i in range(181,0,-10):  
    p.ChangeDutyCycle(2.5 + 10 * i / 180)  
    time.sleep(0.02)  
    p.ChangeDutyCycle(0)  
    time.sleep(0.2) 
