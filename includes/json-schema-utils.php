<?php

/**
 * Applies validation and sanitization to data based on json schemas.
 *
 * @param array $data Target data.
 * @param array $schema JSON schema.
 *
 * @return array|WP_Error Validation result.
 */
function forms_bridge_validate_with_schema($data, $schema)
{
    $is_valid = rest_validate_value_from_schema($data, $schema);
    if (is_wp_error($is_valid)) {
        return $is_valid;
    }

    return rest_sanitize_value_from_schema($data, $schema);
}

/**
 * Merge numeric arrays with default values and returns the union of
 * the two arrays without repetitions.
 *
 * @param array $list Numeric array with values.
 * @param array $default Default values for the list.
 *
 * @return array
 */
function forms_bridge_merge_array($list, $default)
{
    return array_values(array_unique(array_merge($list, $default)));
}

/**
 * Merge collection of arrays with its defaults, apply defaults to
 * each item of the collection and return the collection without
 * repetitions.
 *
 * @param array $collection Input collection of arrays.
 * @param array $default Default values for the collection.
 * @param array $schema JSON schema of the collection.
 *
 * @return array
 */
function forms_bridge_merge_collection($collection, $default, $schema = [])
{
    if (!isset($schema['type'])) {
        $schema['type'] = forms_bridge_get_json_schema_type($default[0]);
    }

    if (!in_array($schema['type'], ['array', 'object'])) {
        return forms_bridge_merge_array($collection, $default);
    }

    if ($schema['type'] === 'object') {
        foreach ($default as $default_item) {
            $col_item = null;
            for ($i = 0; $i < count($collection); $i++) {
                $col_item = $collection[$i];

                if (!isset($col_item['name'])) {
                    continue;
                }

                if (
                    $col_item['name'] === $default_item['name'] &&
                    ($col_item['ref'] ?? false) ===
                        ($default_item['ref'] ?? false)
                ) {
                    break;
                }
            }

            if ($i === count($collection)) {
                $collection[] = $default_item;
            } else {
                $collection[$i] = forms_bridge_merge_object(
                    $col_item,
                    $default_item,
                    $schema
                );
            }
        }
    } elseif ($schema['type'] === 'array') {
        $a = 1;
        // TODO: Handle matrix case
    }

    return $collection;
}

/**
 * Generic array default values merger. Switches between merge_collection and merge_list
 * based on the list items' data type.
 *
 * @param array $array Input array.
 * @param array $default Default array values.
 * @param array $schema JSON schema of the array values.
 *
 * @return array Array fullfilled with defaults.
 */
function forms_bridge_merge_object($array, $default, $schema = [])
{
    foreach ($default as $key => $default_value) {
        if (empty($array[$key])) {
            $array[$key] = $default_value;
        } else {
            $value = $array[$key];
            $type =
                $schema['properties'][$key]['type'] ??
                forms_bridge_get_json_schema_type($default_value);

            if ($type === 'object') {
                if (!is_array($value) || wp_is_numeric_array($value)) {
                    $array[$key] = $default_value;
                } else {
                    $array[$key] = forms_bridge_merge_object(
                        $value,
                        $default_value,
                        $schema['properties'][$key] ?? []
                    );
                }
            } elseif ($type === 'array') {
                if (!wp_is_numeric_array($value)) {
                    $array[$key] = $default_value;
                } else {
                    $array[$key] = forms_bridge_merge_collection(
                        $value,
                        $default_value,
                        $schema['properties'][$key]['items'] ?? []
                    );
                }
            }
        }
    }

    if (isset($schema['properties'])) {
        foreach ($array as $key => $value) {
            if (!isset($schema['properties'][$key])) {
                unset($array[$key]);
            }
        }
    }

    return $array;
}

/**
 * Gets the corresponding JSON schema type from a given value.
 *
 * @param mixed $value
 *
 * @return string JSON schema value type.
 */
function forms_bridge_get_json_schema_type($value)
{
    if (wp_is_numeric_array($value)) {
        return 'array';
    } elseif (is_array($value)) {
        return 'object';
    } else {
        $type = gettype($value);
        switch ($type) {
            case 'double':
                return 'number';
            default:
                return strtolower($type);
        }
    }
}
