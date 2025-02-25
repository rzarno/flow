# Flow PHP - Web

## Prerequisite
To run web page locally, you need to have Symfony CLI installed locally.
Instruction on how to install it can be found here: [https://symfony.com/download](https://symfony.com/download).

## Setup
Install all dependencies:
```shell
composer install
```

Create a local certificate authority for the server:
```shell
symfony server:ca:install
```

Run symfony web server:
```shell
symfony server:start --dir=public -d
```

Build assets:
```shell
bin/console tailwind:build
```

Page will be accessible from [https://127.0.0.1:8000](https://127.0.0.1:8000) url.

To stop symfony web server, execute:
```shell
symfony local:server:stop --dir=public
```

## Working with CSS using Tailwind
We strive to eliminate usage of Node and NPM in Flow PHP, so we decided to use standalone CLI executable here.
To generated CSS using Tailwind, execute:
```shell
bin/console tailwind:build
```

It'll fetch executable file and create CSS that will be served from web server.
Or if you want to keep it running in the watch mode, execute:
```shell
bin/console tailwind:build --watch
```