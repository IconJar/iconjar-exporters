<?php

namespace iconjar;

use DateTime;
use const DIRECTORY_SEPARATOR;
use function file_exists;
use function ltrim;
use function pathinfo;
use function preg_replace;

class Utils
{

    const DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * @return string
     */
    public static function globally_unique_identifier()
    {
        // this should produce an exact same format as
        // what NSProcessInfo globallyUniqueIdentifier produces.
        $data = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return strtoupper(vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4)));
    }

    /**
     * @param \DateTime $time
     *
     * @return string
     */
    public static function formatted_date_string(\DateTime $time = null)
    {
        $time = $time ?: new DateTime();
        return $time->format(static::DATE_FORMAT);
    }

    /**
     * @param $filename
     * @param $base_directory
     *
     * @return string
     */
    public static function unique_filename($filename, $base_directory)
    {
        $filename = static::clean_string(ltrim($filename, '.'));
        $first_check_path = $base_directory.DIRECTORY_SEPARATOR.$filename;
        if(file_exists($first_check_path) === false) {
            return $filename;
        }

        $info = pathinfo($filename);
        $ext = $info['extension'];
        $component = $info['filename'];
        $counter = 1;

        while(true) {
            $new_filename = $component.'.'.$counter.'.'.$ext;
            $new_path = $base_directory.DIRECTORY_SEPARATOR.$new_filename;
            if(file_exists($new_path) === false) {
                $filename = $new_filename;
                break;
            }
            $counter++;
        }
        return $filename;
    }

    /**
     * @param $string
     *
     * @return null|string
     */
    public static function clean_string($string)
    {
        return strtolower(preg_replace('#[^a-z0-9@\\.]+#', '-', $string));
    }

}