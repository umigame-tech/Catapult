# Catapult

## 実行方法

```sh
docker compose run php php /app/src/main.php /sample/001.json
```

Laravel の install をスキップしたい場合

```sh
docker compose run php php /app/src/main.php /sample/001.json --skip-installation
```

## 生成されたページを確認

```sh
cd dist/my_great_project
```

```sh
# ホスト側にも PHP がインストールされている前提
php artisan serve --host 0.0.0.0 --port 8080
```

ブラウザで http://localhost:8080/admin/my_great_entity を開く
