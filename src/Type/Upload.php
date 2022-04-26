<?php

namespace WPGraphQL\Upload\Type;

use GraphQL\Error\Error;
use GraphQL\Error\InvariantViolation;
use GraphQL\Utils\Utils;
use UnexpectedValueException;

/**
 * Class Upload
 *
 * Registers Upload scalar type to WPGraphQL schema.
 *
 * @package WPGraphQL\Upload\Type
 */
class Upload
{
    /**
     * Keys found for a file in the $_FILES array to validate against.
     *
     * @var array
     */
    public static $validationFileKeys = ['name', 'type', 'size'];

    /**
     * Register the scalar Upload type.
     */
    public static function registerType()
    {
        add_action('graphql_register_types', function ($typeRegistry) {
            $typeRegistry->register_scalar('Upload', [
                'description' => 'The `Upload` special type represents a file to be uploaded in the same HTTP request as specified by [graphql-multipart-request-spec](https://github.com/jaydenseric/graphql-multipart-request-spec).',
                'serialize' => function ($value) {
                    return static::serialize($value);
                },
                'parseValue' => function ($value) {
                    return static::parseValue($value);
                },
                'parseLiteral' => function ($value, array $variables = null) {
                    return static::parseLiteral($value);
                },
            ]);
        });
    }

    /**
     * Serializes an internal value to include in a response.
     *
     * @param string $value
     * @return string
     */
    public static function serialize($value)
    {
        throw new InvariantViolation('`Upload` cannot be serialized');
    }

    /**
     * Parses an externally provided value (query variable) to use as an input
     *
     * @param mixed $value
     * @return mixed
     */
    public static function parseValue($value)
    {
        if (!static::arrayKeysExist($value, static::$validationFileKeys)) {
            throw new UnexpectedValueException('Could not get uploaded file, be sure to conform to GraphQL multipart request specification. Instead got: ' . Utils::printSafe($value));
        }

        // If not supplied, use the server's temp directory.
        if (empty($value['tmp_name'])) {
          $tmp_dir = get_temp_dir();
          $value['tmp_name'] = $tmp_dir . wp_unique_filename($tmp_dir, $value['name']);
        }

        return $value;
    }

    /**
     * Parses an externally provided literal value (hardcoded in GraphQL query) to use as an input.
     *
     * E.g.
     * {
     *   upload(file: ".......")
     * }
     *
     * @param GraphQLLanguageASTNode $valueNode
     * @param array|null $variables
     * @return string
     * @throws Error
     */
    public static function parseLiteral($value, array $variables = null)
    {
        throw new Error('`Upload` cannot be hardcoded in query, be sure to conform to GraphQL multipart request specification. Instead got: ' . $value->kind, $value);
    }

    /**
     * Check if an array of keys exist in the given array.
     *
     * @param array $array
     * @param array $keys
     * @return boolean
     */
    private static function arrayKeysExist($array, $keys)
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $array)) {
                return false;
            }
        }

        return true;
    }
}
