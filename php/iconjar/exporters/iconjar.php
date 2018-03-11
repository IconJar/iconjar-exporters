<?php

namespace iconjar\exporters;

use const DIRECTORY_SEPARATOR;
use function error_get_last;
use function file_put_contents;
use function gzencode;
use iconjar\exceptions\exports\iconjar\CopyFileException;
use iconjar\exceptions\exports\iconjar\CreationException;
use iconjar\exceptions\exports\iconjar\DimensionsException;
use iconjar\Icon;
use iconjar\License;
use iconjar\Set;
use iconjar\Group;
use iconjar\Utils;
use function json_encode;
use function mkdir;

class IconJar
{

    const EXT     = 'iconjar';
    const VERSION = 2.0;

    const GZ_COMPRESSION_LEVEL = 1;

    /**
     * @var null|string
     */
    public $name = null;

    /**
     * @var Set[]|Group[]
     */
    protected $children = [];

    /**
     * @var array
     */
    protected $groups = [];

    /**
     * @var array
     */
    protected $sets = [];

    /**
     * @var array
     */
    protected $icons = [];

    /**
     * @var array
     */
    protected $licenses = [];

    /**
     * @var null|string
     */
    protected $save_location = null;

    /**
     * IconJar constructor.
     *
     * @param               $name
     * @param Set[]|Group[] $children
     */
    public function __construct($name, array $children = [])
    {
        $this->name = $name;
        $this->children = $children;
    }

    /**
     * @param Set $set
     *
     * @return $this
     */
    public function add_set(Set $set)
    {
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
        $this->children[] = $group;
        return $this;
    }

    /**
     * @param array $children
     */
    protected function compile_array(array $children)
    {
        foreach($children as $child) {
            if($child instanceof Set) {
                $this->compile_set($child);
            } else if($child instanceof Group) {
                $this->compile_group($child);
            }
        }
    }

    protected function compile_set(Set $set)
    {
        $dict = [
            'name' => $set->name,
            'identifier' => $set->identifier,
            'sort' => $set->sort,
            'description' => $set->description ?: '', // cannot be null,
            'date' => Utils::formatted_date_string($set->date)
        ];
        if(($group = $set->group) instanceof Group) {
            $dict['parent'] = $group->identifier;
        }
        if(($license = $set->license) instanceof License) {
            $dict['license'] = $this->compile_license($license);
        }
        $this->sets[$set->identifier] = $dict;

        foreach($set->icons as $icon) {
            $this->compile_icon($icon);
        }
    }

    /**
     * @param Icon $icon
     *
     * @return string
     * @throws CopyFile
     * @throws CopyFileException
     * @throws DimensionsException
     */
    protected function compile_icon(Icon $icon)
    {
        $icon->validate();

        $save_location = $this->save_location.DIRECTORY_SEPARATOR.'icons';
        $filename = Utils::unique_filename($icon->file, $save_location);

        $dict = [
            'name' => $icon->name,
            'width' => $icon->width,
            'height' => $icon->height,
            'type' => $icon->type,
            'file' => $filename,
            'date' => Utils::formatted_date_string($icon->date),
            'tags' => $icon->tags_string() ?: '', // cannot be null,
            'identifier' => $icon->identifier,
            'parent' => $icon->set->identifier,
            'unicode' => $icon->unicode ?: '', // cannot be null
            'description' => $icon->description ?: '' // cannot be null
        ];

        if(($license = $icon->license) instanceof License) {
            $dict['licence'] = $this->compile_license($license);
        }
        $this->icons[$icon->identifier] = $dict;
        if(copy($icon->file_path, $save_location.DIRECTORY_SEPARATOR.$filename) === false) {
            $err = error_get_last();
            throw new CopyFileException($err['message']);
        }
        return $icon->identifier;
    }

    /**
     * @param License $license
     *
     * @return null|string
     */
    protected function compile_license(License $license)
    {
        if(isset($this->licenses[$license->identifier]) === false) {
            $this->licenses[$license->identifier] = [
                'name' => $license->name,
                'identifier' => $license->identifier,
                'url' => $license->url,
                'text' => $license->description ?: '' // cannot be null
            ];
        }
        return $license->identifier;
    }

    /**
     * @param Group $group
     *
     * @return null|string
     */
    protected function compile_group(Group $group)
    {
        $dict = [
            'name' => $group->name,
            'identifier' => $group->identifier,
            'sort' => $group->sort,
            'description' => $group->description ?: ''
        ];
        if(($parent_group = $group->group) instanceof Group) {
            $dict['parent'] = $parent_group->identifier;
        }
        $this->groups[$group->identifier] = $dict;
        $this->compile_array($group->children());
        return $group->identifier;
    }

    /**
     * @param $path_to_save
     *
     * @return string
     * @throws CreationException
     */
    public function save($path_to_save)
    {
        $save_dir = $path_to_save.DIRECTORY_SEPARATOR.$this->name.'.'.static::EXT;
        $this->save_location = $save_dir;
        if(mkdir($save_dir) === false) {
            $err = error_get_last();
            throw new CreationException($err['message']);
        }

        $icon_dir = $save_dir.DIRECTORY_SEPARATOR.'icons';
        if(mkdir($icon_dir) === false) {
            $err = error_get_last();
            throw new CreationException($err['message']);
        }

        $this->compile_array($this->children);
        $dict = [
            'meta' => [
                'version' => static::VERSION,
                'date' => Utils::formatted_date_string()
            ],
            'groups' => $this->groups,
            'sets' => $this->sets,
            'licences' => $this->licenses,
            'items' => $this->icons
        ];

        $json = json_encode($dict);
        $json_data = gzencode($json, static::GZ_COMPRESSION_LEVEL);

        $meta_file = $save_dir.DIRECTORY_SEPARATOR.'META';
        if(file_put_contents($meta_file, $json_data) === false) {
            $err = error_get_last();
            throw new CreationException($err['message']);
        }
        return $save_dir;
    }

}