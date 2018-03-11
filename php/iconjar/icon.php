<?php

namespace iconjar;

use function array_unique;
use iconjar\exceptions\exports\iconjar\DimensionsException;
use iconjar\exceptions\exports\iconjar\InvalidType;
use function pathinfo;
use const PATHINFO_BASENAME;
use const PATHINFO_EXTENSION;
use const PATHINFO_FILENAME;
use function strtolower;

class Icon
{

    const TYPE_UNKNOWN = -1;
    const TYPE_SVG     = 0;
    const TYPE_PNG     = 1;
    const TYPE_GIF     = 2;
    const TYPE_PDF     = 3;
    const TYPE_ICNS    = 4;
    const TYPE_WEBP    = 5;
    const TYPE_ICO     = 6;

    /**
     * @var null|string
     */
    public $file_path = null;

    /**
     * @var null|string
     */
    public $file = null;

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
     * @var int
     */
    public $type = self::TYPE_UNKNOWN;

    /**
     * @var array
     */
    public $tags = [];

    /**
     * @var int
     */
    public $width = 0;

    /**
     * @var int
     */
    public $height = 0;

    /**
     * @var null|\DateTime
     */
    public $date = null;

    /**
     * @var null|string
     */
    public $unicode = null;

    /**
     * @var null|Set
     */
    public $set = null;

    /**
     * Icon constructor.
     *
     * @param string $name
     * @param string $file_on_disk
     * @param int    $type
     */
    public function __construct($name = 'Untitled Icon', $file_on_disk, $type = null)
    {
        $this->name = $name;
        $this->file_path = $file_on_disk;
        $this->file = pathinfo($this->file_path, PATHINFO_BASENAME);
        $this->type = $type ?: static::type($file_on_disk);
        $this->identifier = Utils::globally_unique_identifier();
    }

    /**
     * @param $file
     *
     * @return int
     */
    public static function type($file)
    {
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        if($ext === null) {
            return static::TYPE_UNKNOWN;
        }
        switch(strtolower($ext)) {
            case 'svg':
                return static::TYPE_SVG;
            case 'png':
                return static::TYPE_PNG;
            case 'gif':
                return static::TYPE_GIF;
            case 'pdf':
                return static::TYPE_PDF;
            case 'icns':
                return static::TYPE_ICNS;
            case 'webp':
                return static::TYPE_WEBP;
            case 'ico':
                return static::TYPE_ICO;
            default:
                return static::TYPE_UNKNOWN;
        }
    }

    /**
     * @param string $tag
     *
     * @return $this
     */
    public function add_tag($tag)
    {
        $this->tags[] = $tag;
        return $this;
    }

    /**
     * @param string[] $tags
     *
     * @return $this
     */
    public function add_tags(array $tags)
    {
        $this->tags = array_merge($this->tags, $tags);
        return $this;
    }

    /**
     * @return string
     */
    public function tags_string()
    {
        return implode(',', array_unique($this->tags));
    }

    /**
     * @throws DimensionsException
     */
    public function validate()
    {
        if($this->type === static::TYPE_UNKNOWN) {
            throw new InvalidTypeException();
        }
        if($this->type !== static::TYPE_SVG) {
            if($this->width == 0 || $this->height == 0) {
                throw new DimensionsException();
            }
        }
    }
}