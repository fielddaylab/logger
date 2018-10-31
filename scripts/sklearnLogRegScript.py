# Python
import os
from os.path import dirname
from pprint import pprint

import numpy as np
from numpy import loadtxt

from sklearn import preprocessing
from sklearn.model_selection import train_test_split
from sklearn.preprocessing import StandardScaler
from sklearn.linear_model import LogisticRegression

import sys
from sys import argv
#sys.tracebacklimit = 0
#sys.stderr = open(os.devnull,'wb')
args = sys.argv
num_args = len(sys.argv)

dir_path = os.path.dirname(os.path.realpath(__file__))
filename = dir_path + "/../" + args[1]

X = loadtxt(filename, ndmin=2, delimiter=',', skiprows=2, usecols=tuple(map(int, args[2:num_args-1])))
y = loadtxt(filename, delimiter=',', skiprows=2, usecols=int(args[num_args-1]))

# preprocess dataset, split into training and test part
X = StandardScaler().fit_transform(X)
lab_enc = preprocessing.LabelEncoder()
encoded = lab_enc.fit_transform(y)
X_train, X_test, y_train, y_test = \
    train_test_split(X, y, test_size=.5)

clf = LogisticRegression(multi_class='multinomial', solver='saga', max_iter=5000).fit(X_train, y_train)
score = clf.score(X_test, y_test)

print score