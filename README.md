# Catapult

## 実行方法

```sh
docker compose up
```

```sh
docker compose exec php bash
```

```sh
php src/main.php /sample/001.json
```

Laravel の install をスキップしたい場合

```sh
php src/main.php /sample/001.json --skip-installation
```

## 生成されたページを確認

```sh
cd /dist/my_great_project
```

TailwindCSSのクラスを継続的にビルド

```sh
npm run build -- --watch
```

Laravel の開発サーバーを起動

```sh
php artisan serve --host 0.0.0.0
```

ブラウザで http://localhost:8000/admin/my_great_entity を開く
