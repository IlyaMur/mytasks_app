# MyTasks

![CodeSniffer PSR-12](https://github.com/IlyaMur/my_tasks/workflows/CodeSniffer-PSR-12/badge.svg)
![PHPUnit-Tests](https://github.com/IlyaMur/my_tasks/workflows/PHPUnit-Tests/badge.svg)
[![Maintainability](https://api.codeclimate.com/v1/badges/1fe9e35cd954bd20623c/maintainability)](https://codeclimate.com/github/IlyaMur/myTasks/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/1fe9e35cd954bd20623c/test_coverage)](https://codeclimate.com/github/IlyaMur/myTasks/test_coverage)

**[🇬🇧 English readme](https://github.com/IlyaMur/mytasks_app/blob/master/README_en.md)**

**Содержание**
  - [О приложении](#о-приложении)
  - [Установка](#установка)
    - [Сборка в Docker](#сборка-в-docker)
    - [Установка локально](#установка-локально)
    - [Конфигурация](#конфигурация)
  - [Авторизация по JWT](#авторизация-по-jwt)
    - [Регистрация](#регистрация)
    - [Аутентификация](#аутентификация)
    - [Обновление токена](#обновление-access-token)
    - [Выход](#выход)
  - [Авторизация по ключу](#авторизация-по-стандартному-ключу)
  - [RESTful API](#restful-api)
  - [Работа с ошибками](#работа-с-ошибками)

## О Приложении  

**MyTasks** - API-приложение созданное на чистом PHP, с поддержкой двух типов авторизации: JWT и стандартным ключом в заголовке.  
Приложение представляет собой CRUD с полным доступом к ресурсу `tasks` через REST API.

Во время написания приложения целью стояло избегание любых зависимостей и написания всего API-функционала с нуля, особое внимание уделялось безопасности JWT-авторизации.  

Деплой приложения осуществлен на сервис Heroku.   
API **MyTasks** доступен по адресу - https://rest-todoapp.herokuapp.com/api/signup   

Для демонстрации работы API написан клиент на React - https://github.com/IlyaMur/mytasks_api_client

В приложении реализованы:
- Системы аутентификации и авторизации.
- Возможность переключения режимов авторизации между JWT и стандартным ключом в заголовке.
- Для JWT реализована система Access и Refresh токенов.
- RESTful-эндпоинт с полным доступом к CRUD-операциям с ресурсом.
- Валидация поступающих от клиента данных.
- Семантика HTTP-ответов.

## Установка  

Необходимо склонировать репозиторий

    $ git clone https://github.com/IlyaMur/mytasks_app.git  
    $ cd mytasks_app

И подготовить файл `.env`

    $ make env-prepare


Изменить (опционально, будет работать и с настройками по умолчанию) параметры подключения в файле `.env`

```dotenv
MYSQL_USER='user'
MYSQL_HOST='mariadb'
APACHE_DEFAULT_PORT='80'
MYSQL_PASSWORD='testpassword'
...
```

### Сборка в Docker

Приложение доступно для сборки в Docker.   

Собрать и запустить приложение

    $ make docker-start  

Остановить и удалить контейнеры

    $ make docker-stop  

Так же доступны:

    $ make docker-bash  # запустить сессию bash в контейнере  
    $ make docker-test  # запустить тесты в контейнере 

По умолчанию приложение будет доступно: `http://localhost/api/tasks`

### Установка локально

`PHP >= 8.0`

Для установки зависимостей:  

    $ make install   

В настройках веб-сервера установить root в директории `public/`  

В выбранную СУБД импортировать SQL из файла `database/mytasks_db.sql`    

### Конфигурация  

Настройки конфигурации доступны в файле [config.php](config/config.php)

Настройки по умолчанию включают в себя:
- Данные подключения к серверу БД. 
- Настройка секретного ключа для хэширования токенов.
- Регулировка продолжительности жизни Access Token и Refresh Token.
- Переключение режима авторизации: JWT или стандартный API-ключ.
- Установки для вывода/скрытия детализации ошибок.
- Настройка логирования ошибок.
- Логирование ошибок.
- Настройка CORS.

Для переопределения настроек в конфигурационном файле доступны соответствующие константы.

## Авторизация по JWT

В качестве основного варианта авторизации доступна авторизация по JWT (задана по умолчанию).

Срок жизни токенов (регулируется в [config.php](config/config.php)):  
**Access Token** - 5 минут.  
**Refresh Token** - 5 дней.

### Регистрация

Для регистрации необходим `POST-запрос` на эндпоинт https://rest-todoapp.herokuapp.com/api/signup   
В теле запроса указать в формате JSON:

```
{
  "username": "...",
  "email": "...",
  "password": "..."
}
```
При успехе в ответе будет набор токенов:
```
{
  "accessToken": "...",
  "refreshToken": "..."
}
```
При неудаче вернется JSON с информацией об ошибках.

Для последующих запросов **Access Token** необходимо вставить в заголовок **Authorization**. 

### Обновление Access Token

Для обновления **Access Token** небходим `POST-запрос` на https://rest-todoapp.herokuapp.com/api/refresh  
В теле должен быть полученный вместе с ним **Refresh Token**:

```
{
  "refreshToken": "полученный ранее refreshToken"
}
```
При успехе (токен корректен и его срок не истёк) в ответе будет новая пара токенов.

### Аутентификация

https://rest-todoapp.herokuapp.com/api/login обеспечит новым набором JWT.  
В тело `POST-запроса` необходимо включить JSON со своими логином и паролем.

### Выход

https://rest-todoapp.herokuapp.com/api/logout служит выходом из системы.    
При `DELETE-запросе` с включенным в тело **Refresh Token** - будет совершено удаление **Refresh Token** из белого списка, дальнейшее его обновление станет невозможным.

## Авторизация по ключу.

Процедура регистрации схожа с описанной [выше](#регистрация).  
В `POST-запрос` на эндпоинт https://rest-todoapp.herokuapp.com/api/signup нужно включить JSON с желаемыми логином, паролем и почтой.

При успехе в ответе будет получен токен доступа.
 ```
 {
   "accessToken": "токен доступа"
 }
 ```
Для последующих запросов **Access Token** необходимо вставить в заголовок **X-Api-Key**.

## RESTful API

Для работы с REST-ресурсом создан эндпоинт - https://rest-todoapp.herokuapp.com/api/tasks 

`Tasks` - типичный To-Do-list. Ресурс, доступный для всех CRUD-операций.

`GET-запрос` на `/tasks` даст список всех задач конкретного пользователя:
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

`POST-запрос` на `/tasks` с включенном в тело JSON с данными задачи cоздаст необходимую задачу:
```
{
  "title": "Feed my cat",
  "body": "Fish and milk",
}
```
Запросы на экземпляр ресурса:  

`GET` на `/tasks/:id`, в ответе вернет конкретную задачу  
`PATCH` на `/tasks/:id`, с включенными в тело данными изменит конкретную задачу.  
`DELETE` на `/tasks/:id`, удалит конкретную задачу.  

Все данные поступающие на сервер валидируются. Ошибки возвращаются клиенту.

## Работа с ошибками

Ошибки преобразуются в исключения. Обработчиками обозначены:
```
set_error_handler('Ilyamur\TasksApp\Exceptions\ErrorHandler::handleError');
set_exception_handler('Ilyamur\TasksApp\Exceptions\ErrorHandler::handleException');
```

При константе `SHOW_ERRORS` (настраивается в [config.php](config/config.php)) равной `true`, в случае исключения или ошибки клиенту будет выведена полная детализация ошибки.   
Если `SHOW_ERRORS` присвоено значение `false` будет показано лишь общее сообщение.
Детализированная информация в данном случае будет логироваться в директории `logs/`.
