.PHONY: build deploy

build:
	

deploy:
	rsync -vrc --exclude-from 'rsync-exclude' * mli-field@fielddaylab.wisc.edu:/httpdocs/logger 