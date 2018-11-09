# Python
import os
from os.path import dirname
from pprint import pprint

import numpy as np
from numpy import loadtxt

from sklearn.model_selection import train_test_split
from sklearn.preprocessing import StandardScaler
from sklearn.neural_network import MLPClassifier
from sklearn.neighbors import KNeighborsClassifier
from sklearn.svm import SVC
from sklearn.gaussian_process import GaussianProcessClassifier
from sklearn.gaussian_process.kernels import RBF
from sklearn.tree import DecisionTreeClassifier
from sklearn.ensemble import RandomForestClassifier, AdaBoostClassifier
from sklearn.naive_bayes import GaussianNB
from sklearn.discriminant_analysis import QuadraticDiscriminantAnalysis
from sklearn import metrics

import sys
from sys import argv
sys.tracebacklimit = 0
sys.stderr = open(os.devnull,'wb')
args = sys.argv
num_args = len(sys.argv)

dir_path = os.path.dirname(os.path.realpath(__file__))
filename = dir_path + "/../" + args[1]

percentTesting = 0.5 # number between 0 and 1 for what % of dataset should be used for testing
X = loadtxt(filename, ndmin=2, delimiter=',', skiprows=2, usecols=tuple(map(int, args[2:num_args-1])))
y = loadtxt(filename, delimiter=',', skiprows=2, usecols=int(args[num_args-1]))


names = ["Nearest Neighbors", "Linear SVM", "RBF SVM", "Gaussian Process",
         "Decision Tree", "Random Forest", "Neural Net", "AdaBoost",
         "Naive Bayes", "QDA"]

classifiers = [
    KNeighborsClassifier(3),
    SVC(kernel="linear", C=0.025, probability=True),
    SVC(gamma=2, C=1, probability=True),
    GaussianProcessClassifier(1.0 * RBF(1.0)),
    DecisionTreeClassifier(max_depth=5),
    RandomForestClassifier(max_depth=5, n_estimators=10, max_features=1),
    MLPClassifier(alpha=1, max_iter=1000),
    AdaBoostClassifier(),
    GaussianNB(),
    QuadraticDiscriminantAnalysis()]

# preprocess dataset, split into training and test part
X = StandardScaler().fit_transform(X)
X_train, X_test, y_train, y_test = \
    train_test_split(X, y, test_size=.5, random_state=123)

# iterate over classifiers
for name, clf in zip(names, classifiers):
    clf.fit(X_train, y_train)
    score = clf.score(X_test, y_test)
    predictions = clf.predict(X_test)
    probabilities = (clf.predict_proba(X_test))[:,1]

    f1 = metrics.f1_score(y_test, predictions, average='weighted')
    #auc = metrics.roc_auc_score(y_test, probabilities, average='weighted')
    #kappa = metrics.cohen_kappa_score(y_test, predictions)
    #ari = metrics.adjusted_rand_score(y_test, predictions)

    print name, score, f1