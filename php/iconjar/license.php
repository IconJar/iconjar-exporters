<?php

namespace iconjar;

class License
{

    /**
     * @var null|string
     */
    public $name = null;

    /**
     * @var null|string
     */
    public $url = null;

    /**
     * @var null|string
     */
    public $description = null;

    /**
     * @var null|string
     */
    public $identifier = null;

    /**
     * License constructor.
     *
     * @param string $name
     */
    public function __construct($name = 'Untitled License')
    {
        $this->name = $name;
        $this->identifier = Utils::globally_unique_identifier();
    }

}