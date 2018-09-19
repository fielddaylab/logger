# Python
import tensorflow as tf
from tensorflow import keras
import os
from os.path import dirname
import numpy as np
from numpy import loadtxt
from pprint import pprint

dir_path = os.path.dirname(os.path.realpath(__file__))
filename = dirname(dir_path) + "/questions/questionsDataForR_q00.txt"
filename_queue = tf.train.string_input_producer([dirname(dir_path) + "/questions/questionsDataForR_q00.txt"])

percentTesting = 0.2 # number between 0 and 1 for what % of dataset should be used for testing

x = loadtxt(filename, ndmin=2, delimiter=',', skiprows=2, usecols=(1, 2, 3, 4, 5, 6))
y = loadtxt(filename, delimiter=',', skiprows=2, usecols=(7))

num_rows = len(x)
num_testing_rows = int(num_rows * percentTesting)
x_train = x[:num_rows-num_testing_rows]
y_train = y[:num_rows-num_testing_rows]

x_test = x[-num_testing_rows:]
y_test = y[-num_testing_rows:]
# Scale inputs 0 to 1
for i in range(len(x_train[0])):
   maxX = max(max(x_train[:,i]), max(x_test[:,i]))
   x_train[:,i] = x_train[:,i] / maxX
   x_test[:,i] = x_test[:,i] / maxX

class_names = [0, 1]

model = keras.Sequential([
    keras.layers.Dense(128, activation=tf.nn.relu),
    keras.layers.Dense(2, activation=tf.nn.softmax)
])
model.compile(optimizer=tf.train.AdamOptimizer(),
            loss='sparse_categorical_crossentropy',
            metrics=['accuracy'])
model.fit(x_train, y_train, epochs=5)
test_loss, test_acc = model.evaluate(x_test, y_test)
print('Test accuracy:', test_acc)