<?php
/**
 * @author Mohamed Marzouk <mohamedmarzouk1600@gmail.com>
 * @Copyright Maximum Develop
 * @FileCreated 10/18/19 10:03 AM
 * @Contact https://www.linkedin.com/in/mohamed-marzouk-138158125
 */

namespace App\Enums;

use ReflectionClass;
use UnexpectedValueException;

class Enum
{
    // phpcs:disable
    /**
     * Default enum value.
     *
     * @constant(__default)
     */
    public const __default = null;
    // phpcs:enable

    /**
     * Enum value.
     *
     * @var mixed
     */
    protected $value;

    /**
     * Enum constructor.
     *
     * @param mixed $value enum value
     *
     * @throws \ReflectionException
     */
    public function __construct($value = null)
    {
        $reflected = new ReflectionClass($this);

        if (!in_array($value, $reflected->getConstants())) {
            throw new UnexpectedValueException("Value '$value' is not part of the enum ".get_called_class());
        }

        $this->value = $value;
    }

    /**
     * Get an array of all enum constants.
     *
     * @param bool $include_default include default value or not
     *
     * @return array
     *
     * @throws \ReflectionException
     */
    public static function getConstList($include_default = false)
    {
        $reflected = new ReflectionClass(new static(null));

        $constants = $reflected->getConstants();

        if (!$include_default) {
            unset($constants['__default']);

            return $constants;
        }

        return $constants;
    }

    /**
     * String representation of the enum.
     *
     * @return string
     */
    final public function __toString()
    {
        return strval($this->value);
    }

    /**
     * Get an array of all enum constants.
     *
     * @param bool $include_default include default value or not
     *
     * @return array
     *
     * @throws \ReflectionException
     */
    public static function getValues($include_default = false)
    {
        $reflected = new ReflectionClass(new static(null));
        $constants = $reflected->getConstants();
        if (!$include_default) {
            unset($constants['__default']);
            return array_values($constants);
        }
        return array_values($constants);
    }

    /**
     * Get an array of all enum constants.
     *
     * @param bool $include_default include default value or not
     *
     * @return array
     *
     * @throws \ReflectionException
     */
    public static function getKeys($include_default = false)
    {
        $reflected = new ReflectionClass(new static(null));
        $constants = $reflected->getConstants();
        if (!$include_default) {
            unset($constants['__default']);
            return array_keys($constants);
        }
        return array_keys($constants);
    }

    /**
     * @param $value
     * @return mixed
     * @throws \ReflectionException
     */
    public static function getName($value)
    {
        $constants = array_flip(self::getConstList());
        return $constants[$value];
    }

    /**
     * get Value of the constant by Key
     * @param $key
     * @return mixed
     * @throws \ReflectionException
     */
    public static function getValue(string $key)
    {
        $constants = array_change_key_case(self::getConstList(), CASE_LOWER);
        return $constants[$key];
    }
}
