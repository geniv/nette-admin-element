<?php declare(strict_types=1);

namespace AdminElement\Bridges\Nette;

use AdminElement\AdminElement;
use AdminElement\WrapperSection;
use Nette\DI\CompilerExtension;


/**
 * Class Extension
 *
 * @author  geniv
 * @package AdminElement\Bridges\Nette
 */
class Extension extends CompilerExtension
{
    /** @var array default values */
    private $defaults = [
        'elements' => [],
    ];


    /**
     * Load configuration.
     */
    public function loadConfiguration()
    {
        $builder = $this->getContainerBuilder();
        $config = $this->validateConfig($this->defaults);

        // load elements
        $elements = [];
        foreach ($config['elements'] as $name => $element) {
            $elements[$name] = $builder->addDefinition($this->prefix($name))
                ->setFactory($element)
                ->setAutowired(true);
        }

        $builder->addDefinition($this->prefix('default'))
            ->setFactory(AdminElement::class, [$elements])
            ->setAutowired(true);

        $builder->addDefinition($this->prefix('wrapper'))
            ->setFactory(WrapperSection::class)
            ->setAutowired(true);
    }
}
