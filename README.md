# Catapult

Catapult is a tool to generate Laravel project with simple admin pages and CRUD APIs from a json file.

It automatically creates classes below.

- web.php and api.php (routes)
- Models
- Controllers
- Requests
- Resources
- Migrations
- Factories
- Seeders
- Views
- CSS setup with TailwindCSS

This tool is under development.

## How to use

First, start docker containers.

```sh
docker compose up
```

Login to php container.

```sh
docker compose exec php bash
```

Install PHP dependencies.

```sh
composer install
```

main.php takes a json file path as an argument.

```sh
php src/main.php /sample/001.json
```

If you want to skip Laravel installation, use `--skip-installation` option.  
This options is useful when you already created a Laravel project with Catapult.

```sh
php src/main.php /sample/001.json --skip-installation
```

## JSON example

```json
{
  "project_name": "my_project",
  "entities": [
    {
      "name": "person",
      "authenticatable": true,
      "allowedFor": [
        "person"
      ],
      "attributes": [
        {
          "name": "name",
          "type": "username"
        },
        {
          "name": "email",
          "type": "email",
          "loginKey": true
        },
        {
          "name": "password",
          "type": "password"
        },
        {
          "name": "tel",
          "type": "tel",
          "rules": {
            "nullable": true
          }
        },
        {
          "name": "age",
          "type": "integer",
          "rules": {
            "min": 10,
            "max": 80
          }
        },
        {
          "name": "is_admin",
          "type": "boolean"
        },
        {
          "name": "birthday",
          "type": "date"
        },
        {
          "name": "registered_at",
          "type": "datetime"
        },
        {
          "name": "start_time",
          "type": "time"
        },
        {
          "name": "weight",
          "type": "decimal"
        },
        {
          "name": "description",
          "type": "text"
        }
      ]
    },
    {
      "name": "author",
      "allowedFor": [
        "everyone"
      ],
      "attributes": [
        {
          "name": "name",
          "type": "string"
        },
        {
          "name": "description",
          "type": "text"
        }
      ]
    },
    {
      "name": "book",
      "allowedFor": [
        "person",
        "everyone"
      ],
      "attributes": [
        {
          "name": "title",
          "type": "string",
          "rules": {
            "max": 60
          }
        },
        {
          "name": "description",
          "type": "text"
        }
      ],
      "belongsTo": [
        {
          "name": "author",
          "type": "select"
        }
      ]
    },
    {
      "name": "chapter",
      "allowedFor": [ "everyone" ],
      "attributes": [
        {
          "name": "title",
          "type": "string"
        },
        {
          "name": "description",
          "type": "text"
        }
      ],
      "belongsTo": [
        {
          "name": "book",
          "type": "radio"
        }
      ]
    },
    {
      "name": "role",
      "allowedFor": [
        "person"
      ],
      "attributes": [
        {
          "name": "level",
          "type": "integer"
        },
        {
          "name": "name",
          "type": "string"
        }
      ],
      "dataPath": "002/roles.json"
    }
  ]
}
```

For more detail, see `app/src/JsonSchemas/schema.json`.

## Preview the generated Laravel project

If you defined `my_great_project` as `project_name` in your json file, you can preview the generated Laravel project like this.

```sh
cd /dist/my_great_project
```

Watch TailwindCSS classes.

```sh
npm run build -- --watch
```

In another shell, start Laravel dev server.

```sh
php artisan serve --host 0.0.0.0
```

Open `http://localhost:8000/{plural_entity_name}` in your browser.

e.g. http://localhost:8000/books or http://localhost:8000/people or something like that.
