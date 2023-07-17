<?php

namespace WPGraphQL\Upload\Request;

use GraphQL\Server\RequestError;

/**
 * Class BodyParser
 *
 * Request body parser for Upload scalar type.
 *
 * @package WPGraphQL\Upload
 */
class BodyParser
{
    /**
     * Register parser into the WPGraphQL request pipeline.
     */
    public static function init()
    {
        add_filter('graphql_request_data', [static::class, 'processRequest'], 10, 2);
    }

    /**
     * Maps files to their respective variables for input.
     * Based on the GraphQL multipart request specification.
     *
     * @see https://github.com/jaydenseric/graphql-multipart-request-spec
     * @see https://github.com/Ecodev/graphql-upload/blob/73a63038455e5fb5aa6c3f0e41a06208c45e8539/src/UploadMiddleware.php#L27
     *
     * @param array $bodyParams     The parsed body parameters.
     * @param array $requestContext The GraphQL request context.
     *
     * @return array
     */
    public static function processRequest(array $bodyParams, array $requestContext)
    {
        $contentType = isset($_SERVER['CONTENT_TYPE']) ? sanitize_text_field(wp_unslash($_SERVER['CONTENT_TYPE'])) : '';

        if ('POST' === $requestContext['method'] && stripos($contentType, 'multipart/form-data') !== false) {
            if (empty($bodyParams['map'])) {
                throw new RequestError(__('The request must define a `map`', 'wp-graphql-upload'));
            }

            $decodeJson = static function ($json) {
                if (!is_string($json)) {
                    return $json;
                }

                return json_decode(stripslashes($json), true);
            };

            $map    = $decodeJson($bodyParams['map']);
            $result = $decodeJson($bodyParams['operations']);

            foreach ($map as $fileKey => $locations) {
                $items = &$result;

                foreach ($locations as $location) {
                    foreach (explode('.', $location) as $key) {
                        if (!isset($items[$key]) || !is_array($items[$key])) {
                            $items[$key] = [];
                        }

                        $items = &$items[$key];
                    }

                    // Throw an error if the file wasnt found in the location specified in the map
                    if (! array_key_exists($fileKey, $_FILES)) {
                        throw new RequestError(
                            sprintf(
                                // translators: %s is the location of the file
                                __('The uploaded file was not found in the specified location `%s`', 'wp-graphql-upload'),
                                $location
                            )
                        );
                    }

                    $items = $_FILES[$fileKey];
                }
            }

            return $result;
        }

        return $bodyParams;
    }
}
