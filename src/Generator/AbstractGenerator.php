<?php

namespace Kodmit\MessengerCqrsGeneratorBundle\Generator;

abstract class AbstractGenerator
{
    protected const APP_ROOT = __DIR__ . '/../../../../../src';
    protected const SKIPPED_PROPERTIES = ['createdAt', 'updatedAt'];

    protected string $namespace;
    protected \ReflectionClass $reflection;
    protected string $dirPath;
    protected string $className;

    public function __construct(string $namespace)
    {
        $this->namespace = $namespace;
        $this->reflection = new \ReflectionClass($namespace);
        $this->className = self::getClassNameFromNamespace($namespace);
        $this->dirPath = sprintf('%1$s/Action/%2$s', self::APP_ROOT, $this->className);

        if (false === file_exists($this->dirPath)) {
            mkdir($this->dirPath,0777, true);
        }
    }

    protected static function getClassNameFromNamespace(string $namespace): string
    {
        $str = explode('\\', $namespace);
        return end($str);
    }

    protected function generateConstruct(bool $ignoreId = false, bool $onlyId = false): string
    {
        $parameters = '';
        $assignProperties = '';

        foreach ($this->reflection->getProperties() as $property) {
            if (
                true === in_array($property->getName(), self::SKIPPED_PROPERTIES) ||
                (true === $ignoreId && 'id' === $property->getName())
            ) {
                continue;
            }

            if (false === empty($assignProperties)) {
                $assignProperties .= '        ';
            }

            if ('id' === $property->getName() && false === $ignoreId) {
                $assignProperties .= sprintf('$this->%1$sId = $%1$sId;' . "\n", strtolower(self::getClassNameFromNamespace($this->namespace)));
                $parameters .= sprintf("%s $%sId, ", $property->getType(), strtolower(self::getClassNameFromNamespace($this->namespace)));

                if (true === $onlyId) {
                    break;
                }

                continue;
            }

            $parameters .= sprintf("%s $%s, ", $property->getType(), $property->getName());
            $assignProperties .= sprintf('$this->%1$s = $%1$s;' . "\n", $property->getName());
        }

        $assignProperties = rtrim($assignProperties, "\n");
        $parameters = rtrim($parameters, ', ');

        return sprintf('public function __construct(%s) 
    {
        %s
    }', $parameters, $assignProperties);

    }

    protected function generateGetters(bool $ignoreId = false, bool $onlyId = false): string
    {
        $getters = '';

        foreach ($this->reflection->getProperties() as $property) {

            if (
                true === in_array($property->getName(), self::SKIPPED_PROPERTIES) ||
                (true === $ignoreId && 'id' === $property->getName())
            ) {
                continue;
            }

            if (false === empty($getters)) {
                $getters .= '    ';
            }

            if ('id' === $property->getName() && false === $ignoreId) {
                $propertyType = null === $property->getType() ? '' : sprintf(': %s', $property->getType());
                $className = self::getClassNameFromNamespace($this->namespace);
                $getters .= sprintf('public function get%sId()%s
    {
        return $this->%sId;
    }
            ' . "\n",
                    $className, $propertyType, strtolower($className)
                );

                if (true === $onlyId) {
                    break;
                }

                continue;
            }

            $propertyType = null === $property->getType() ? '' : sprintf(': %s', $property->getType());

            $getters .= sprintf('public function get%s()%s
    {
        return $this->%s;
    }
            ' . "\n",
                ucfirst($property->getName()), $propertyType, $property->getName()
            );
        }

        return $getters;
    }
}