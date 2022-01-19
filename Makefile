install:
	composer install
test:
	composer exec phpunit tests
lint:
	composer exec phpcs -- --standard=PSR12 src
gh-test:
	composer exec --verbose phpunit -- --testsuite gh-actions
test-coverage:
	composer exec --verbose phpunit tests -- --coverage-html build/coverage