import numpy as np
import matplotlib.pyplot as plt
import matplotlib.cm as cm
import tensorflow as tf
import tflearn
#tf.logging.set_verbosity(tf.logging.ERROR)

from tflearn.layers.core import input_data, dropout, fully_connected
from tflearn.layers.conv import conv_2d, max_pool_2d
from tflearn.layers.normalization import local_response_normalization
from tflearn.layers.estimator import regression

import tflearn.datasets.mnist as mnist

train_image, train_label, test_image, test_label = \
mnist.load_data('C:\\Users\\PC1901-24\\anaconda3\\Lib\\site-packages\\tflearn\\datasets\\mnist', one_hot=True)

train_image = train_image.reshape([-1, 28, 28, 1])
test_image = test_image.reshape([-1, 28, 28, 1])

#tf.reset_default_graph()

net = tflearn.input_data(shape=[None, 28, 28, 1])
net = tflearn.conv_2d(net, 32, 5, activation='relu')
net = tflearn.max_pool_2d(net, 2)
net = tflearn.conv_2d(net, 64, 5, activation = 'relu')
net = tflearn.max_pool_2d(net, 2)
net = tflearn.fully_connected(net, 128, activation='relu')
net = tflearn.dropout(net, 0.5)

net = tflearn.fully_connected(net, 10, activation='softmax')
net = tflearn.regression(net, optimizer='sgd', learning_rate=0.5, loss='categorical_crossentropy')

model = tflearn.DNN(net)
model.fit(train_image, train_label, n_epoch=20, batch_size=100, validation_set=0.1, show_metric=True)
pred=np.array(model.predict(test_image)).argmax(axis=1)
print(pred)
label = test_label.argmax(axis=1)
print(label)
accuracy=np.mean(pred == label, axis=0)
print(accuracy)
