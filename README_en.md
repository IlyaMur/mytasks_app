# MyTasks

![CodeSniffer PSR-12](https://github.com/IlyaMur/my_tasks/workflows/CodeSniffer-PSR-12/badge.svg)
![PHPUnit-Tests](https://github.com/IlyaMur/my_tasks/workflows/PHPUnit-Tests/badge.svg)
[![Maintainability](https://api.codeclimate.com/v1/badges/1fe9e35cd954bd20623c/maintainability)](https://codeclimate.com/github/IlyaMur/myTasks/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/1fe9e35cd954bd20623c/test_coverage)](https://codeclimate.com/github/IlyaMur/myTasks/test_coverage)

**[🇷🇺 Russian readme](https://github.com/IlyaMur/mytasks_app/blob/master/README.md)**

**Table of contents**
  - [Overview](#overview)
  - [Install](#install)
    - [Configuration](#configuration)
  - [Authorization by JWT](#authorization-by-jwt)
    - [Registration](#registration)
    - [Authentication](#authentication)
    - [Refresh Access Token](#refresh-access-token)
    - [Logout](#logout)
  - [Authorization by key](#authorization-by-key)
  - [RESTful API](#restful-api)
  - [Errors](#errors)

## Overview 

**MyTasks** is an API application written in plain PHP that supports two types of authorization: JWT and a standard header key.  

The application is a CRUD with full access to the `tasks` resource via the REST API.  

One of the goal of writing this app was to avoid any dependencies and write all the API functionality from scratch. Special attention paid to the security of JWT authorization.  

To improve the quality of the code, the main functionality of the app was covered by unit tests.

The application was deployed to the Heroku service.  
The **MyTasks** API is available at - https://rest-todoapp.herokuapp.com/api/signup   

To demonstrate how the API-app works, I built the React client - https://github.com/IlyaMur/mytasks_api_client

The application has:
- Systems of authentication and authorization.
- Ability to switch authorization modes between JWT and the standard key in the header.
- The system of Access and Refresh tokens is implemented.
- RESTful endpoint with full access to resource CRUD operations.
- Validation of data received from the client.
- Semantics of HTTP responses.

## Install  

`PHP >= 8.0` (application uses named arguments and other new PHP features)  

You need to clone the repository:
    $ git clone https://github.com/IlyaMur/mytasks_app.git

To install dependencies:  

    $ make install   

Configure your web server to have the `public/` folder as the web root.

Import SQL from the `mytasks.sql` file into the selected DBMS 
In `config/config.php` set the data for accessing the database, storage and hashing settings.

### Configuration  

Configuration settings are available in the file [config.php](config/config.php)

Default settings include:
- Database connection data.
- Error logging settings.
- A secret key for hashing tokens.
- Adjusting the lifetime of Access Token and Refresh Token..
- Switch authorization mode: JWT or standard API key.
- Settings for showing/hiding error details.
- Configuring CORS.

The corresponding constants are available to override settings in the configuration file.

## Authorization by JWT

JWT authorization is available as the main authorization option (set by default).

Token lifetime (adjustable in [config.php](config/config.php)):
**Access Token** - 5 minutes.
**Refresh Token** - 5 days.

### Registration

Registration requires a `POST` request to the endpoint https://rest-todoapp.herokuapp.com/api/signup

```
{
  "username": "...",
  "email": "...",
  "password": "..."
}
```
If successful, the response will be a set of tokens:
```
{
  "accessToken": "...",
  "refreshToken": "..."
}
```
On failure, JSON will be returned with errors.

For subsequent requests, the **Access Token** must be in the **Authorization** header.

### Refresh Access Token

For refreshing **Access Token** you need a `POST` request to https://rest-todoapp.herokuapp.com/api/refresh  
The body must contain the **Refresh Token** received with it:

```
{
  "refreshToken": "previously received refreshToken"
}
```
If successful (the token is valid and not expired), the response will be a new pair of tokens.

### Authentication

https://rest-todoapp.herokuapp.com/api/login will provide a new set of JWTs. 
In the body of the `POST` request you need to include JSON with your username and password.

### Logout

https://rest-todoapp.herokuapp.com/api/logout is a logout.    
With a `DELETE-request` with **Refresh Token** included in its body, **Refresh Token** will be removed from the white list, and its further updating will become impossible.

## Authorization by key.

The registration is similar to [above](#endpoint-for-registration).
In the `POST` request to the https://rest-todoapp.herokuapp.com/api/signup endpoint, you need to include JSON with the desired login, password, and mail.

If successful, the response will receive an access token.
 ```
 {
   "accessToken": "access token"
 }
 ```
For subsequent requests, the **Access Token** must be inserted in the **X-Api-Key** header.

## RESTful API

`/tasks` endpoint has been created to work with the REST resource.
`Tasks` is To-Do-list. The resource available for all CRUD operations.

A `GET` request to https://rest-todoapp.herokuapp.com/api/tasks will give a list of all tasks for a particular user:
```
[
  {
    ...
    "title": "Paint a wall",
    "body": "In green paint",
    ...
  },
  {
    ...
    "title": "Go for a walk",
    "body": "In the park",
    ...
  }
]
```

A `POST` request to `/tasks` with the task data JSON in the body will create the required task:
```
{
  "title": "Feed my cat",
  "body": "Fish and milk",
}
```
Requests to the specific instance of the resource:

`GET` on `/tasks/:id`, will return the specific task in response
`PATCH` to `/tasks/:id`, with the data included in the body, will update the specific task.
`DELETE` on `/tasks/:id` will delete the specific task.

All data received on the server is validated. Errors are returned to the client.

## Errors

Errors are converted to exceptions. The handlers are:
```
set_error_handler('Ilyamur\TasksApp\Exceptions\ErrorHandler::handleError');
set_exception_handler('Ilyamur\TasksApp\Exceptions\ErrorHandler::handleException');
```

If the `SHOW_ERRORS` constant (configurable in [config.php](config/config.php)) is set to `true`, full error's details will be displayed in the browser in case of an exception or error.   
If `SHOW_ERRORS` is set to `false` only the generic message will be printed.
Detailed information in this case will be logged in the `logs/` directory.  
