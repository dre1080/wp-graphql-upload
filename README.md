# WPGraphQL Upload

This plugin adds Upload support to the [WPGraphQL plugin](https://github.com/wp-graphql/wp-graphql) as specified by [graphql-multipart-request-spec](https://github.com/jaydenseric/graphql-multipart-request-spec).

## Requirements

Using this plugin requires having the [WPGraphQL plugin](https://github.com/wp-graphql/wp-graphql) installed and activated.

## Activating / Using

Activate the plugin like you would any other WordPress plugin.

Once the plugin is active, the `Upload` scalar type will be available to your mutation input fields.

If you're using composer:

```
composer require dre1080/wp-graphql-upload
```

## Usage

Then you can start using in your mutations like so:

```php
register_graphql_mutation(
  'upload', [
      'inputFields' => [
          'file' => [
              'type' => ['non_null' => 'Upload'],
          ],
      ],
      'outputFields' => [
          'text' => [
              'type'    => 'String',
              'resolve' => function ($payload) {
                  return $payload['text'];
              },
          ],
      ],
      'mutateAndGetPayload' => function ($input) {
          if (!function_exists('wp_handle_sideload')) {
              require_once(ABSPATH . 'wp-admin/includes/file.php');
          }

          wp_handle_sideload($input['file'], [
              'test_form' => false,
              'test_type' => false,
          ]);

          return [
              'text' => 'Uploaded file was "' . $input['file']['name'] . '" (' . $input['file']['type'] . ').',
          ];
      }
  ]
);
```
