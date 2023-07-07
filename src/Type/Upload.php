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
    public static $validationFileKeys = ['name', 'type', 'size', 'tmp_name'];

    /**
     * Register the scalar Upload type.
     */
    public static function registerType()
    {
        add_action('graphql_register_types', static function ($typeRegistry) {
            $typeRegistry->register_scalar('Upload', [
                'description'  => sprintf(
                    // translators: %s is a link to the graphql-multipart-request-spec repo
                    __( 'The `Upload` special type represents a file to be uploaded in the same HTTP request as specified by [graphql-multipart-request-spec](%s).', 'wp-graphql-upload' ),
                    'https://github.com/jaydenseric/graphql-multipart-request-spec'
                ),
                'serialize'    => static function ($value) {
                    return static::serialize($value);
                },
                'parseValue'   => static function ($value) {
                    return static::parseValue($value);
                },
                'parseLiteral' => static function ($value, array $variables = null) {
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
     * @param \WPGraphQL\Upload\Type\GraphQLLanguageASTNode $valueNode
     * @param array|null $variables
     * @return string
     * @throws \GraphQL\Error\Error
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
