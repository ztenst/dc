<?php
namespace app\behaviors\redis;

use Yii;
use yii\behaviors\Behavior;
use \Exception;

abstract class RedisEntity extends Behavior {
	/**
	 * The name of the redis entity (key)
	 * @var string
	 */
	public $name;

	/**
	 * Holds the redis connection
	 * @var ARedisConnection
	 */
	protected $_connection;

	/**
	 * The old name of this entity
	 * @var string
	 */
	protected $_oldName;
	/**
	 * Constructor
	 * @param string $name the name of the entity
	 * @param ARedisConnection|string $connection the redis connection to use with this entity
	 */
	public function __construct($name = null, $connection = null) {
		if ($name !== null) {
			$this->name = $name;
		}
		if ($connection !== null) {
			$this->setConnection($connection);
		}
	}

	/**
	 * Sets the redis connection to use for this entity
	 * @param ARedisConnection|string $connection the redis connection, if a string is provided, it is presumed to be a the name of an applciation component
	 */
	public function setConnection($connection)
	{
		if (is_string($connection)) {
			$connection = Yii::$app->{$connection};
		}
		$this->_connection = $connection;
	}

	/**
	 * Gets the redis connection to use for this entity
	 * @return ARedisConnection
	 */
	public function getConnection()
	{
		if ($this->_connection === null) {
			if (!isset(Yii::$app->redis)) {
				throw new CException(get_class($this)." expects a 'redis' application component");
			}
			$this->_connection = Yii::$app->redis;
		}
		return $this->_connection;
	}

	/**
	 * Sets the expiration time in seconds to this entity
	 *  @param integer number of expiration for this entity in seconds
	 */
	public function expire($seconds)
	{
		return $this->getConnection()->expire($this->name, $seconds);
	}

}
