import numpy as np
import pathlib as pl
import matplotlib.pyplot as plt

file = open("sine_wave.bin", "rb")

data: short = file.read()

for i in range(190):
    y_value[i] = data[2*i + 1]
x_value = range(1, len(data))

plt.plot(x_value, y_value)

plt.show()

file.close()