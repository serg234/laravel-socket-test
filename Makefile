install:
	docker compose build
	docker compose up -d
	cp -n src/.env.example src/.env || true
	docker compose exec app composer install
	docker compose exec app php artisan key:generate
	docker compose exec app npm install
	docker compose exec app npm run build
	sleep 5
	docker compose exec app php artisan migrate
