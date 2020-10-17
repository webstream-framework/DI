<?php

namespace WebStream\DI;

use PhpDocReader\PhpDocReader;
use WebStream\Container\Container;
use WebStream\Exception\Extend\AnnotationException;

/**
 * Injector
 * @author Ryuichi TANAKA.
 * @since 2015/12/26
 * @version 0.7
 */
trait Injector
{
    /**
     * @var Container プロパティコンテナ
     */
    private Container $propertyContainer;

    /**
     * オブジェクトを注入する
     * @param string プロパティ名
     * @param mixed オブジェクト
     * @return Injector
     */
    public function inject(string $name, $object)
    {
        $this->{$name} = $object;

        return $this;
    }

    /**
     * 型指定されたオブジェクトを注入する
     * @param string プロパティ名
     * @param mixed オブジェクト
     * @return Injector
     * @throws WebStream\Exception\Extend\AnnotationException
     */
    public function strictInject(string $name, $object)
    {
        $reader = new PhpDocReader();
        try {
            $refClass = new \ReflectionClass($this);
            while ($refClass !== false) {
                if ($refClass->hasProperty($name)) {
                    $refProperty = $refClass->getProperty($name);
                    $classpath = $reader->getPropertyClass($refProperty);
                    if ($object instanceof $classpath) {
                        $this->inject($name, $object);
                    } else {
                        throw new AnnotationException("The type of injected property must be instance of ${classpath}");
                    }
                }
                $refClass = $refClass->getParentClass();
            }
        } catch (\ReflectionException $e) {
            throw new AnnotationException($e);
        }

        return $this;
    }

    /**
     * overload setter
     * @param mixed $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        if (!isset($this->propertyContainer)) {
            $this->propertyContainer = new Container(false);
        }
        $this->propertyContainer->{$name} = $value;
    }

    /**
     * overload setter
     * @param mixed $name
     * @return mixed|null
     */
    public function __get($name)
    {
        return $this->propertyContainer !== null ? $this->propertyContainer->{$name} : null;
    }

    /**
     * overload isset
     * @param mixed $name
     * @return bool
     */
    public function __isset($name)
    {
        return $this->propertyContainer === null || $this->propertyContainer->{$name} === null;
    }

    /**
     * overload unset
     * @param mixed $name
     */
    public function __unset($name)
    {
        $this->propertyContainer->remove($name);
    }

    /**
     * コンテナクリア
     */
    public function __clear()
    {
        $this->propertyContainer = null;
    }
}
