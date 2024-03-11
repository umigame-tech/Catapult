# Catapult

Catapult is a tool that generates a Laravel project with simple admin pages and CRUD APIs from a JSON file.

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

## How to Use

First, start docker containers.

```sh
docker compose up
```

Log in to php container.

```sh
docker compose exec php bash
```

Install PHP dependencies.

```sh
composer install
```

`src/Main.php` takes a JSON file path as an argument.

```sh
php src/Main.php /sample/001.json
```

If you want to skip Laravel installation, use `--skip-installation` option.  
These options are useful when you already created a Laravel project with Catapult.

```sh
php src/Main.php /sample/001.json --skip-installation
```

## Example JSON configuration for project setup

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

For more detail, see [app/src/JsonSchemas/schema.json](app/src/JsonSchemas/schema.json).

## Previewing generated Laravel projects

If you defined `my_great_project` as `project_name` in your json file, you can preview generated Laravel projects like this.

```sh
cd /dist/my_great_project
```

Watch TailwindCSS classes. `npm run build -- --watch` command watches the project's front-end source code and build classes of TailwindCSS.

```sh
npm run build -- --watch
```

In another shell, start Laravel dev server.

```sh
php artisan serve --host 0.0.0.0
```

Open `http://localhost:8000/{plural_entity_name}` in your browser.

e.g. http://localhost:8000/books , http://localhost:8000/people or something like that.
