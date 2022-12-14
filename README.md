
# Task Reminders in Code are marked as follows:
*PENDING*
*REFACTOR*
*REVISIT* nothing urgent


User seeder: `users` table will always be seeded with an entry username:'ryan' password 'password'
Allowing us to set authentication in Postman.


# Models

## Project

Every Project consists of a minimum of 2 jobs and 2 team members.

A Project should tell the public:

## Job
When a Job is unoccupied, the project manager can post a listing for the vacancy by creating a JobVacancy.

## JobVacancy
A listing for a job which is unoccupied.
For mere convenience and consistency, throughout the project we typically assign a JobVacancy instance to a variable '$listing' rather than '$jobVacancy' e.g.:
	$listing = JobVacancy::find($id)

# Tables (without a model)

## `genres`
Every project has exactly 1 genre.

What is a genre?

Examples

* **music** funk, rock, pop
* **film** horror, thriller, romance
* **app** finance, social, utilities, entertainment


## `project_types`

What is a project type?

Examples

* **music** song, album, jam session, live performance
* **app** web app, desktop app, mobile app


# Eloquent

## get 5 users without sensitive data
`User::all()->take(5)->toArray()`

# Routes

## Public Routes
```
POST    /api/login                  email, password
POST    /api/register               username, email, password, password_confirmation

GET     /api/projects
GET     /api/projects/:id


```

## Protected Routes
```
POST    /api/logout

POST    /api/projects               title, description, type
PUT     /api/projects/:id           title, description, type
DELETE  /api/projects/:id


```



# Postman

Testing the API using Postman.

For POST, PUT requests you need the header `Accept: application/json`

To access protected routes you must first send a POST request to `/api/register` or `/api/login` to obtain a token. This token must then be included in the Authorization header as a Bearer token.


# Installation

$ php artisan migrate
$ php artisan db:seed



# Testing

$ touch .env.testing
$ touch test.sqlite
$ php artisan key:generate --env=testing
$ php artisan migrate --env=testing
$ php artisan test

Read notes: Tech\Laravel\testing.txt

Note that running `vendor\bin\phpunit` instead of
`php artisan test` gives you syntax highlighting.

## Testing a Single Class
$ vendor\bin\phpunit --filter 'ListingTest'

## Testing a Single Method
$ vendor\bin\phpunit --filter 'Tests\\Feature\\ListingTest::test_users_can_create_listing'

## Buggy Test Functions (SKIP_*)
Some test functions have been prexised with `SKIP_` instead of `test_`. These are, for the most part, non-essential tests which need debugging.









## Eloquent Relationships



`$user->skills->pluck('title')`

**JSON Nested Object**

`User::find($id)->with('profile')->with('skills')->get() `


