<?php

namespace Kod\MessengerCqrsGeneratorBundle\Generator;

use function Symfony\Component\String\lower;

class DTO extends AbstractGenerator implements GeneratorInterface
{
    public function generate(): void
    {
        $this->generateCreate();
        $this->generateUpdate();
        $this->generateDelete();
    }

    public function generateCreate(): void
    {
        $privateProperties = '';

        foreach ($this->reflection->getProperties() as $property) {
            if (true === in_array($property->getName(), self::SKIPPED_PROPERTIES) || 'id' === $property->getName()) {
                continue;
            }

            $privateProperties .= sprintf("    private %s $%s;\n", $property->getType(), $property->getName());
        }

        $filePath = sprintf('%s/Create%s.php', $this->dirPath, $this->className);
        touch($filePath);

        $data = sprintf('<?php
        
namespace App\\Action\\%1$s;
          
class Create%1$s
{
%2$s
    %3$s
    
    %4$s
}
',
            $this->className,
        $privateProperties,
        $this->generateConstruct(true),
        $this->generateGetters(true)
        );

        file_put_contents($filePath, $data);
    }

    public function generateUpdate(): void
    {
        $privateProperties = '';

        foreach ($this->reflection->getProperties() as $property) {
            if (true === in_array($property->getName(), self::SKIPPED_PROPERTIES)) {
                continue;
            }

            if ('id' === $property->getName()) {
                $privateProperties .= sprintf("    private %s $%sId;\n", $property->getType(), strtolower($this->className));
                continue;
            }

            $privateProperties .= sprintf("    private %s $%s;\n", $property->getType(), $property->getName());
        }

        $filePath = sprintf('%s/Update%s.php', $this->dirPath, $this->className);
        touch($filePath);

        $data = sprintf('<?php
        
namespace App\\Action\\%1$s;
          
class Update%1$s
{
%2$s
    %3$s
    
    %4$s
}
',
            $this->className,
            $privateProperties,
            $this->generateConstruct(),
            $this->generateGetters(),
        );

        file_put_contents($filePath, $data);
    }

    public function generateDelete(): void
    {
        try {
            $id = $this->reflection->getProperty('id');
        } catch (\ReflectionException $e) {
            throw new \DomainException('Id not found : ' . $e->getMessage());
        }

        $filePath = sprintf('%s/Delete%s.php', $this->dirPath, $this->className);
        touch($filePath);

        $data = sprintf('<?php
        
namespace App\\Action\\%1$s;
          
class Delete%1$s
{
    private %2$s $%3$sId;
    
    %4$s
    
    %5$s 
}
',
            $this->className,
            $id->getType(),
        strtolower($this->className),
        $this->generateConstruct(false, true),
        $this->generateGetters(false, true)
        );

        file_put_contents($filePath, $data);
    }

}