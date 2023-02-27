import numpy as np
from mpl_toolkits.mplot3d import Axes3D
import matplotlib.pyplot as plt

x = [1, 2, 3, 4, 5]
y = [2, 4, 6, 8, 10]
z = np.linspace(0, 100, 11)
Y, Z = np.meshgrid(y, z)
X = np.array([x] * Y.shape[0])

X2 = X
Y2 = Y
Z2 = Z

fig = plt.figure()
ax = Axes3D(fig)
ax.set_xlabel("X")
ax.set_ylabel("Y")
ax.set_zlabel("Z")
ax.plot_surface(X, Y, Z, alpha=0.3) 
test_data = [1, 2, 3, 4, 5]
ax.plot(X2, Y2, Z2, color='red')

plt.show()