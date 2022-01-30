install:
	composer install

test:
	composer exec phpunit tests

lint:
	composer exec phpcs -- --standard=PSR12 src tests

test-coverage:
	composer exec --verbose phpunit tests -- --coverage-clover build/logs/clover.xml

docker-start: 
	docker-compose up -d && make docker-install

docker-stop: 
	docker-compose down

docker-install:
	docker exec -it application composer install

docker-test:
	docker exec -it application make test

docker-bash:
	docker exec -it application bash

env-prepare:
	cp -n .env.example .env || true