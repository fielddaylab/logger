.PHONY: build deploy

build:
	

deploy:
	rsync -vrc * mli-field@fielddaylab.wisc.edu:/httpdocs/logger
