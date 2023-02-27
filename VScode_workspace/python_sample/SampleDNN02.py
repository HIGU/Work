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

for i in range(0, 10):
    if(train_label[0][i] == 1):
        print(f'label = {i}')
        break

#tf.reset_default_graph()

net = tflearn.input_data(shape=[None, 784])
net = tflearn.fully_connected(net, 128, activation='relu')
net = tflearn.dropout(net, 0.5)
net = tflearn.fully_connected(net, 10, activation = 'softmax')
net = tflearn.regression(net, optimizer='sgd', learning_rate=0.5, loss='categorical_crossentropy')

model = tflearn.DNN(net)
model.fit(train_image, train_label, n_epoch=20, batch_size=100, validation_set=0.1, show_metric=True)
pred=np.array(model.predict(test_image)).argmax(axis=1)
print(pred)
label = test_label.argmax(axis=1)
print(label)
accuracy=np.mean(pred == label, axis=0)
print(accuracy)
