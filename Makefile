
build:
	docker-compose build
	docker-compose up -d
	docker-compose down

start:
	docker-compose up -d

stop:
	docker-compose down

database:
	docker-compose run laminas composer create-test-db

test:
	docker-compose exec laminas composer test

bash-server:
	docker-compose exec -e COMPOSER_MEMORY_LIMIT=-1 server bash

logs:
	docker-compose logs -f