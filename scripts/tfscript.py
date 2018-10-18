# Python
import os
from os.path import dirname
from pprint import pprint

import sys
from sys import argv

import numpy as np
import tensorflow as tf
from numpy import loadtxt
from tensorflow import keras

args = sys.argv
num_args = len(sys.argv)

dir_path = os.path.dirname(os.path.realpath(__file__))
filename = dir_path + "/../../" + args[1]

percentTesting = 0.5 # number between 0 and 1 for what % of dataset should be used for testing
x = loadtxt(filename, ndmin=2, delimiter=',', skiprows=2, usecols=tuple(map(int, args[2:num_args-1])))
y = loadtxt(filename, delimiter=',', skiprows=2, usecols=int(args[num_args-1]))

num_rows = len(x)
num_testing_rows = int(num_rows * percentTesting)
x_train = x[:num_rows-num_testing_rows]
y_train = y[:num_rows-num_testing_rows]

x_test = x[-num_testing_rows:]
y_test = y[-num_testing_rows:]


if "numLevel" not in args[1]:
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
    print test_acc
else:
    mean = x_train.mean(axis=0)
    std = x_train.std(axis=0)
    x_train = (x_train - mean) / std
    x_test = (x_test - mean) / std
    model = keras.Sequential([
        keras.layers.Dense(64, activation=tf.nn.relu, input_shape=(x_train.shape[1],)),
        keras.layers.Dense(64, activation=tf.nn.relu),
        keras.layers.Dense(1)
    ])

    optimizer = tf.train.RMSPropOptimizer(0.001)

    model.compile(loss='mse',
                  optimizer=optimizer,
                  metrics=['mae'])
    early_stop = keras.callbacks.EarlyStopping(monitor='val_loss', patience=20)
    model.fit(x_train, y_train, epochs=500,
                  validation_split=0.2, verbose=0,
                  callbacks=[early_stop])
    [loss, mae] = model.evaluate(x_test, y_test, verbose=0)
    print mae