<?php
/**
 * Created by PhpStorm.
 * User: eav
 * Date: 12.07.15
 * Time: 20:38
 */

namespace common\traits;

trait JsonTrait
{
    public function decodeJsonField($name)
    {
        return explode(',', str_replace(['{', '}', '"', ', '], ['', '', '', ','], $this->$name));
    }

    public function decodeJsonFields($name_fields)
    {
        if (is_array($name_fields) && !empty($name_fields)) {
            foreach ($name_fields as $name) {
                $this->{$name} = json_decode($this->{$name}, true);
            }
        }
    }

    public function encodeJsonFields($name_fields)
    {
        if (is_array($name_fields) && !empty($name_fields)) {
            foreach ($name_fields as $name) {
                if (isset($this->{$name}) && !is_scalar($this->{$name})) {
                    $this->{$name} = json_encode($this->{$name});
                }
            }
        }
    }
}