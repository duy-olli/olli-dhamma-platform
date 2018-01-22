<?php
namespace Engine;

use League\Fractal\Serializer\ArraySerializer;

/**
 * Custom fractal array serializer.
 *
 * @category  OLLI Music Platform
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2014-2015
 * @license   New BSD License
 * @link      http://olli.vn/
 */
class FractalSerializer extends ArraySerializer
{
    public function collection($resourceKey, array $data)
    {
        if ($resourceKey == 'parent') {
            return $data;
        }

        return array($resourceKey ?: 'data' => $data);
    }

    public function item($resourceKey, array $data)
    {
        if ($resourceKey == 'parent') {
            return $data;
        }
        return array($resourceKey ?: 'data' => $data);
    }
}
