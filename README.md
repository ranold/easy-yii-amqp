# EasyYiiAmqp #

EasyYiiAmqp is a basic helper class for using [PhpAmqpLib](https://github.com/php-amqplib/php-amqplib) with Yii 1.1.x
It uses only one exchange pipe for simplicity and optional multiple queues

## Setup ##

Ensure you have [composer](http://getcomposer.org) installed, then run the following command:

```bash
$ composer require ranold/easy-yii-amqp
```

That will fetch the library and its dependencies inside your vendor folder. Then you can add the following to your
.php files in order to use the library

```php
require_once __DIR__.'/vendor/autoload.php';
```

Define component within Yii config `protected/config/main.php`

```php
return [
   ...
   'components' => [
     'easymq' => [
         'class' => 'ranold\EasyYiiAmqp\Client',
         'connectionParams' => [
             'host' => 'localhost',
             'port' => '5672',
             'vhost' => '/',
             'user' => 'guest',
             'password' => 'guest'
         ]
     ],
       ...
   ]
];
```

## Usage ##

EasyYiiAmqp provides a very basic interface for sending messages and running callbacks on recieved messages. 

```php
public function actionSend()
{
    Yii::app()->easymq->send('Hello world!', 'QueueName');
    echo "// Sent 'Hello World!'" . PHP_EOL;
}
```

```php
public function actionRecieve()
{
    Yii::app()->easymq->consume(function ($message) {
      echo "// Recieved '{$message->body}'" . PHP_EOL;
    }, 'QueueName');
    
    // continiously wait for new messages
    Yii::app()->easymq->wait();
}
```
 