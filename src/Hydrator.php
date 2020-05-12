<?php

namespace YunaSoft\Hydrator;

class Hydrator
{
    private $object;
    private array $map;

    public function __construct($object)
    {
        if (!isset($this->map)) {
            $this->createMap($object);
        }

        $this->object = $object;
    }

    /**
     * Returns instance of reflection class for class name passed
     *
     * @param string $className
     * @return \ReflectionClass
     * @throws \ReflectionException
     */
    protected function getReflectionClass($className): \ReflectionClass
    {
        if (!isset($this->reflectionClassMap[$className])) {
            $this->reflectionClassMap[$className] = new \ReflectionClass($className);
        }

        return $this->reflectionClassMap[$className];
    }

    protected function getReflection(Object $object)
    {
        $className = get_class($object);
        return $this->getReflectionClass($className);
    }

    public function map(?array $map, $replace = false): Hydrator
    {
        if (!isset($map)) {
            return $this;
        }

        if (!$this->is_array($map)) {
            throw new \Exception('Map must be array');
        }

        if ($replace) {
            $this->map = $map;
        } else {
            foreach ($map as $dataKey => $propertyName) {
                $this->map[$dataKey] = $propertyName;
            }
        }

        return $this;
    }

    private function is_array($object)
    {
        if (is_object($object)) {
            return false;
        }

        if (is_array($object)) {
            return true;
        }

        throw new \Exception('Incorrect object type');
    }

    private function objectPropertyMap(Object $object)
    {
        $reflection = $this->getReflection($object);

        $map = [];

        foreach ($reflection->getProperties() as $property) {
            $map[] = $property->name;
        }

        return $map;
    }

    public function extract($strict = false): array
    {
        if (!$is_array = $this->is_array($this->object)) {
            $reflection = $this->getReflection($this->object);
        }

        $data = [];
        foreach ($this->map as $dataKey => $propertyName) {
            if ($is_array) {
                $data[$dataKey] = $this->object[$dataKey];
            } else {
                if ($reflection->hasProperty($propertyName)) {
                    $property = $reflection->getProperty($propertyName);
                    $property->setAccessible(true);
                    $data[$dataKey] = $property->getValue($this->object);
                } elseif ($strict) {
                    throw new \InvalidArgumentException("There's no $propertyName property in $className.");
                }
            }
        }

        return $data;
    }

    public function hydrate(Object $object, $strict = false)
    {
        if (!$is_array = $this->is_array($this->object)) {
            $data = (new self($this->object))->extract($strict);
        } else {
            $data = $this->object;
        }

        $className = get_class($object);
        $reflection = $this->getReflectionClass($className);

        foreach ($this->map as $dataKey => $propertyName) {
            if (!$reflection->hasProperty($propertyName)) {
                if ($strict) {
                    throw new \InvalidArgumentException("There's no $propertyName property in $className.");
                } else {
                    continue;
                }
            }

            if (isset($data[$dataKey])) {
                $property = $reflection->getProperty($propertyName);
                $property->setAccessible(true);
                $property->setValue($object, $data[$dataKey]);
            }
        }

        return $object;
    }

    private function createMap($object)
    {
        if ($this->is_array($object)) {
            $properties = array_keys($object);
        } else {
            $properties = $this->objectPropertyMap($object);
        }

        foreach ($properties as $property) {
            $this->map[$property] = $property;
        }
    }
}