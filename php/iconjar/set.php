<?php

namespace iconjar;

class Set
{

    /**
     * @var Icon[]
     */
    public $icons = [];

    /**
     * @var null|string
     */
    public $name = null;

    /**
     * @var null|string
     */
    public $identifier = null;

    /**
     * @var null|string
     */
    public $description = null;

    /**
     * @var null|License
     */
    public $license = null;

    /**
     * @var null|\DateTime
     */
    public $date = null;

    /**
     * @var null|Group
     */
    public $group = null;

    /**
     * @var int
     */
    public $sort = 0;

    /**
     * Set constructor.
     *
     * @param string $name
     * @param Icon[]
     */
    public function __construct($name = 'Untitled Set', array $icons = [])
    {
        $this->name = $name;
        $this->icons = $icons;
        $this->identifier = Utils::globally_unique_identifier();
    }

    /**
     * @param Icon $icon
     *
     * @return $this
     */
    public function add_icon(Icon $icon)
    {
        $icon->set = $this;
        $this->icons[] = $icon;
        return $this;
    }

}