<?php

namespace YunaSoft\Hydrator;

class Hydrator
{
    private array $data;
    private array $map;
    private array $reflectionClassMap = [];

    /**
     * Hydrator constructor.
     * @param Object|array $data
     */
    public function __construct($data)
    {
        if (is_array($data)) {
            $this->data = $data;
        }

        if (is_object($data)) {
            $this->data = $this->extractProperties($data);
        }

        $this->map = array_keys($data);
    }


    private function extractProperties(Object $object): array
    {
        $reflection = $this->getReflectionClass($object);
        $data = [];
        foreach ($reflection->getProperties() as $property) {
            $property->setAccessible(true);
            $data[$property->name] = $property->getValue($object);
        }

        return $data;
    }

    public function map(array $map): Hydrator
    {
        foreach ($map as $dataKey => $propertyName) {
            $this->map[$dataKey] = $propertyName;
        }

        return $this;
    }

    /**
     * Creates an instance of a class filled with data according to map
     *
     * @param array $data
     * @param string $className
     * @return object
     * @throws \ReflectionException
     */
    public function hydrate($data, $className): Object
    {
        $reflection = $this->getReflectionClass($className);
        $object = $reflection->newInstanceWithoutConstructor();

        foreach ($this->map as $dataKey => $propertyName) {
            if (!$reflection->hasProperty($propertyName)) {
                throw new \InvalidArgumentException("There's no $propertyName property in $className.");
            }

            if (isset($data[$dataKey])) {
                $property = $reflection->getProperty($propertyName);
                $property->setAccessible(true);
                $property->setValue($object, $data[$dataKey]);
            }
        }

        return $object;
    }

    /**
     * Fills an object passed with data according to map
     *
     * @param array $data
     * @param object $object
     * @return object
     * @throws \ReflectionException
     */
    public function hydrateInto($data, $object): Object
    {
        $className = get_class($object);
        $reflection = $this->getReflectionClass($className);

        foreach ($this->map as $dataKey => $propertyName) {
            if (!$reflection->hasProperty($propertyName)) {
                throw new \InvalidArgumentException("There's no $propertyName property in $className.");
            }

            if (isset($data[$dataKey])) {
                $property = $reflection->getProperty($propertyName);
                $property->setAccessible(true);
                $property->setValue($object, $data[$dataKey]);
            }
        }

        return $object;
    }

    /**
     * Extracts data from an object according to map
     *
     * @param object $object
     * @return array
     * @throws \ReflectionException
     */
    public function extract($object): array
    {
        $data = [];

        $className = get_class($object);
        $reflection = $this->getReflectionClass($className);

        foreach ($this->map as $dataKey => $propertyName) {
            if ($reflection->hasProperty($propertyName)) {
                $property = $reflection->getProperty($propertyName);
                $property->setAccessible(true);
                $data[$dataKey] = $property->getValue($object);
            }
        }

        return $data;
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
}