# Ship ERP Take Home

## How to run the project
- clone the repo with `https://github.com/jcdampil23/shipErp.git`
- run the following commands in the terminal to install dependencies and setup the project
```
cp .env.example .env
# Open .env and add your OPEN_WEATHER_API_KEY
composer install
php artisan key:generate
touch database/database.sqlite # Optional: ensures the file exists
php artisan migrate
php artisan serve
```
- once dependencies are installed and the project is setup, run `php artisan serve` and it should be good to go
- The routes available to you will be
- `{baseUrl}/weather/{city}`
- `{baseUrl}/weather/{city}/cached`
- They should return a DTO in this format 
``` 
{
  "city": string,
  "temperature": float,
  "description": string,
  "timestamp": int,
  "source": "external" or 'cache'
}
```

## How to run the tests
- run the command `composer test` in the terminal
- If you encounter one of the tests failing specifically `ExampleTest`, you might not have ran `php artisan key:generate`

## Approach

When interacting with external APIs you want to be able to swap them out at any time for alternatives or fallback API's to ensure downtime remains low.

So in adherence to the maintainable code structure and also personal preference, I decided to go with Domain Driven Development or DDD with a dash of Service Oriented Architecture, (although I'm a bit old school so I still called the folder modules in the project).

The base idea is to have multiple domains based on business rules then creating interfaces for them so that major changes or swapping 3rd party software doesn't affect the core of the project. The interfaces enforce a rigid structure of what each service should have functionally and what those functions should return.

