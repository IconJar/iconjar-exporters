<?php

namespace iconjar;

class Group
{

    /**
     * @var array
     */
    protected $children = [];

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
     * @var null|Group
     */
    public $group = null;

    /**
     * @var int
     */
    public $sort = 0;

    /**
     * Group constructor.
     *
     * @param string        $name
     * @param Set[]|Group[] $children
     */
    public function __construct($name = 'Untitled', array $children = [])
    {
        $this->name = $name;
        $this->children = $children;
        $this->identifier = Utils::globally_unique_identifier();
    }

    /**
     * @param Set $set
     *
     * @return $this
     */
    public function add_set(Set $set)
    {
        $set->group = $this;
        $this->children[] = $set;
        return $this;
    }

    /**
     * @param Group $group
     *
     * @return $this
     */
    public function add_group(Group $group)
    {
        $group->group = $this;
        $this->children[] = $group;
        return $this;
    }

    /**
     * @return array|Group[]|Set[]
     */
    public function children()
    {
        return $this->children;
    }

}