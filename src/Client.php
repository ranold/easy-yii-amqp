<?php
/**
 * EasyYiiAmqp class file
 *
 * @author Dmitry Antonov <mail@ranold.ru>
 * @date: 26.08.2018
 */

/**
 * EasyYiiAmqp is a basic helper class for using PhpAmqpLib with Yii 1.1.x
 * It uses only one exchange pipe for simplicity and optional multiple queues
 *
 * You may configure it as below.
 * <pre>
 * return [
 *    ...
 *    'components' => [
 *      'easymq' => [
 *          'class' => 'ranold\EasyYiiAmqp\Client',
 *          'connectionParams' => [
 *              'host' => 'localhost',
 *              'port' => '5672',
 *              'vhost' => '/',
 *              'user' => 'guest',
 *              'password' => 'guest'
 *          ]
 *      ],
 *        ...
 *    ]
 * ];
 * </pre>
 *
 * Example usage:
 * <pre>
 * public function actionSend()
 * {
 *     Yii::app()->easymq->send('Hello world!', 'QueueName');
 *     echo "// Sent 'Hello World!'" . PHP_EOL;
 * }
 *
 * public function actionRecieve()
 * {
 *     Yii::app()->easymq->consume(function ($message) {
 *       echo "// Recieved '{$message->body}'" . PHP_EOL;
 *     }, 'QueueName');
 *     Yii::app()->easymq->wait();
 * }
 * </pre>
 */

namespace ranold\EasyYiiAmqp;

use \PhpAmqpLib\Connection\AMQPStreamConnection;
use \PhpAmqpLib\Message\AMQPMessage;
use \PhpAmqpLib\Channel\AMQPChannel;

class Client extends \CApplicationComponent
{
    const DEFAULT_QUEUE_NAME = 'main';

    /** @var array Connection params for connecting to AMQP-server
     *  Required options are:
     *  <ul>
     *    <li>host</li>
     *    <li>port</li>
     *    <li>vhost</li>
     *    <li>user</li>
     *    <li>password</li>
     *  </ul>
     */
    protected $_connectionParams;

    /** @var AMQPStreamConnection */
    protected $_connection;

    /** @var AMQPChannel */
    protected $_channel;

    public function init()
    {
        $this->setConnection(new AMQPStreamConnection($this->_connectionParams['host'], $this->_connectionParams['port'], $this->_connectionParams['user'], $this->_connectionParams['password']));

        $this->setChannel($this->getConnection()->channel());

        parent::init();
    }

    /**
     * Sends message to the specified or default queue
     *
     * @param mixed $message
     * @param string $queueName
     */
    public function send($message, $queueName = self::DEFAULT_QUEUE_NAME)
    {
        $amqpMessage = new AMQPMessage($message);

        $this->getChannel()->basic_publish($amqpMessage, '', $queueName);
    }

    /**
     * Subscribe callback function to
     */
    public function consume($callback, $queueName = self::DEFAULT_QUEUE_NAME)
    {
        $this->getChannel()->queue_declare($queueName, false, true, false, false);

        $this->getChannel()->basic_consume($queueName, '', false, true, false, false, $callback);
    }

    /**
     * This method should be run to continiously wait for new messages
     */
    public function wait()
    {
        while (count($this->getChannel()->callbacks)) {
            $this->getChannel()->wait();
        }
    }

    /**
     * @return mixed
     */
    public function getConnection()
    {
        return $this->_connection;
    }

    /**
     * @param mixed $connection
     */
    public function setConnection($connection)
    {
        $this->_connection = $connection;
    }

    /**
     * @return mixed
     */
    public function getChannel()
    {
        return $this->_channel;
    }

    /*** @param mixed $channel
     */
    public function setChannel($channel)
    {
        $this->_channel = $channel;
    }

    /**
     * @param mixed $connectionParams
     */
    public function setConnectionParams($connectionParams)
    {
        $this->_connectionParams = $connectionParams;
    }
}
