<?php
declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

use PhpCfdi\CfdiSatScraper\Validators\CiecValidator;
use PhpCfdi\CfdiSatScraper\Validators\RfcValidator;
use PhpCfdi\CfdiSatScraper\Validators\Contracts\ValidatorInterface;

class Options
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var array
     */
    protected $validators;

    /**
     * Options constructor.
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->validators = [
            RfcValidator::class,
            CiecValidator::class
        ];

        foreach ($options as $option => $value) {
            $this->{$option} = $value;
        }
    }

    /**
     * @return array
     */
    protected function getAvailableKeys(): array
    {
        return [
            'rfc',
            'ciec',
            'loginUrl'
        ];
    }

    /**
     * @param $name
     *
     * @return mixed|null
     */
    public function __get($name)
    {
        return $this->options[$name] ?? null;
    }

    /**
     * @param $name
     * @param $value
     * @throws \InvalidArgumentException
     */
    public function __set($name, $value)
    {
        if (empty($value)) {
            throw new \InvalidArgumentException("The field {$value} is empty");
        }

        if (!in_array($name, $this->getAvailableKeys())) {
            throw new \InvalidArgumentException("the option {$name} not exists");
        }

        foreach ($this->validators as $validator) {
            /**
             * @var $validatorInstance ValidatorInterface
             */
            $validatorInstance = new $validator;
            if ($validatorInstance->can($name) && !$validatorInstance->isValid($value)) {
                throw new \InvalidArgumentException("The value for option {$name} is invalid");
            }
        }

        $this->options[$name] = $value;
    }

    /**
     * @param string $name
     * @param null $default
     *
     * @return mixed|null
     */
    public function getOption(string $name, $default = null)
    {
        return $this->options[$name] ?? $default;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->options;
    }
}
