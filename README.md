Required software:
- macOS 10.12.6 or later
- MAMP 4.5 (3208)
- PHP 7.2.1
- Apache
- MySQL 5.6.38
- R 3.5.1
	- Install "caret" package with dependencies=TRUE
- Chrome 68
- Python 2.7

Installing TensorFlow:
1. Create logger/tensorflow folder
2. Open terminal
3. sudo easy_install pip
4. pip install --upgrade virtualenv
	This command may need to be run with sudo 
5. virtualenv --system-site-packages <tensorflow directory>
6. cd <tensorflow directory>
7. source ./bin/activate
8. pip install --upgrade tensorflow
9. Verify installation by issuing "python" command and paste in
	import tensorflow as tf
	hello = tf.constant('Hello, TensorFlow!')
	sess = tf.Session()
	print(sess.run(hello))
10. pip install -U scikit-learn
