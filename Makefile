install:
	composer install
test:
	composer exec phpunit tests
lint:
	composer exec phpcs -- --standard=PSR12 src tests
test-coverage:
	composer exec --verbose phpunit tests -- --coverage-clover build/logs/clover.xml
