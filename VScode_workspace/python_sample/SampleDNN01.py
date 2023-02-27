import numpy as np
import matplotlib.pyplot as plt
import matplotlib.cm as cm
import tensorflow as tf
import tflearn
#tf.logging.set_verbosity(tf.logging.ERROR)

import tflearn.datasets.mnist as mnist

train_image, train_label, test_image, test_label = \
mnist.load_data('C:\\Users\\PC1901-24\\anaconda3\\Lib\\site-packages\\tflearn\\datasets\\mnist', one_hot=True)

print(train_image[0])
image0 = train_image[0].reshape(28, 28)
plt.imshow(image0, cmap=cm.gray_r, interpolation='nearest')

print(train_label)
print(train_label[0])
for i in range(0, 10):
    if(train_label[0][i] == 1):
        print(f'label = {i}')
        break