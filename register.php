<?php

declare(strict_types=1);

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require __DIR__ . '/vendor/autoload.php';
    Dotenv\Dotenv::createImmutable(__DIR__)->load();

    $db = new TasksApp\Database(
        user: $_ENV['DB_USER'],
        password: $_ENV['DB_PASS'],
        host: $_ENV['DB_HOST'],
        name: $_ENV['DB_NAME']
    );

    $userGateway = new TasksApp\UserGateway($db);
    $userController = new TasksApp\UserController($userGateway);
    $userController->processCreatingRequest($_POST);

    if ($userController->apiKey) {
        $_SESSION['apiKey'] = $userController->apiKey;
    }
}
?>

<!DOCTYPE html>
<html data-theme="light" lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API key registration</title>
    <link rel="stylesheet" href="https://unpkg.com/@picocss/pico@latest/css/pico.min.css">
    <style>
        body>main {
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: calc(100vh - 7rem);
            padding: 1rem 0;
        }

        article {
            padding: 0;
            overflow: hidden;
        }

        article div {
            padding: 1rem;
        }

        @media (min-width: 576px) {
            body>main {
                padding: 1.25rem 0;
            }

            article div {
                padding: 1.25rem;
            }
        }

        @media (min-width: 768px) {
            body>main {
                padding: 1.5rem 0;
            }

            article div {
                padding: 1.5rem;
            }
        }

        @media (min-width: 992px) {
            body>main {
                padding: 1.75rem 0;
            }

            article div {
                padding: 1.75rem;
            }
        }

        @media (min-width: 1200px) {
            body>main {
                padding: 2rem 0;
            }

            article div {
                padding: 2rem;
            }
        }

        /* Hero Image */
        article div:nth-of-type(2) {
            display: none;
            background-color: #374956;
            background-image: url("https://www.xelent.ru/upload/medialibrary/647/rest_1.jpg");
            background-position: center;
            background-size: cover;
        }

        @media (min-width: 992px) {
            .grid>div:nth-of-type(2) {
                display: block;
            }
        }

        /* Footer */
        body>footer {
            padding: 1rem 0;
        }
    </style>
</head>

<body>
    <main class="container">
        <article class="grid">
            <div>
                <?php if (!isset($_SESSION['apiKey'])) : ?>
                    <hgroup>
                        <h1>Registration</h1>
                        <h2>Generate your API key</h2>
                    </hgroup>
                    <?php if (!empty($userController->errors)) : ?>
                        <ul>
                            <?php foreach ($userController->errors as $error) : ?>
                                <li> <?= $error ?> </li>
                            <?php endforeach ?>
                        </ul>
                    <?php endif ?>

                    <form method="post">
                        <input value="<?= $userController->name ?? '' ?>" type="text" name="name" aria-invalid="<?= isset($userController->errors['name']) ? 'true' : '' ?>" placeholder="Name" aria-label="name">

                        <input value="<?= $userController->username ?? '' ?>" type="text" name="username" aria-invalid="<?= isset($userController->errors['username']) ? 'true' : '' ?>" placeholder="Username" aria-label="username">

                        <input value="<?= $userController->password ?? '' ?>" type="password" name="password" aria-invalid="<?= isset($userController->errors['password']) ? 'true' : '' ?>" placeholder="Password" aria-label="Password">

                        <button type="submit" class="contrast">Register</button>
                    </form>
                <?php else : ?>
                    <hgroup>
                        <h1>Successfully!</h1>
                        <h2>Save your key somewhere</h2>
                    </hgroup>
                    Your API key: <strong><?= $_SESSION['apiKey'] ?></strong>
                <?php endif ?>
            </div>
            <div></div>
        </article>
    </main>
</body>

</html>