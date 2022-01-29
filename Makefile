install:
	composer install

test:
	composer exec phpunit tests

lint:
	composer exec phpcs -- --standard=PSR12 src tests

test-coverage:
	composer exec --verbose phpunit tests -- --coverage-clover build/logs/clover.xml

docker-start: 
	docker-compose up -d && make docker-install && sleep 5 && make install-dump

install-dump:
	docker exec -it php-apache php ./database/import.php

docker-stop: 
	docker-compose down

docker-install:
	docker exec -it php-apache composer install --no-dev

env-prepare:
	cp -n .env.example .env || true