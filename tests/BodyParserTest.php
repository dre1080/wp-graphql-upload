<?php

namespace WPGraphQLTests\Upload;

use GraphQL\Server\RequestError;
use WPGraphQL\Upload\Request\BodyParser;

class BodyParserTest extends \WP_UnitTestCase
{
    public function tearDown() : void
    {
        unset($_SERVER['CONTENT_TYPE'], $_FILES);
    }

    public function testParsesMultipartRequest(): void
    {
        $query = '{my query}';
        $variables = [
            'test' => 1,
            'test2' => 2,
            'uploads' => [
                0 => null,
                1 => null,
            ],
        ];
        $map = [
            1 => ['variables.uploads.0'],
            2 => ['variables.uploads.1'],
        ];

        $file1 = ['name' => 'image.jpg', 'type' => 'image/jpeg', 'size' => 1455000, 'tmp_name' => '/tmp/random'];
        $file2 = ['name' => 'foo.txt', 'type' => 'text/plain', 'size' => 945000]; // test without tmp_name.
        $_FILES = [
            1 => $file1,
            2 => $file2,
        ];

        $params = [
            'operations' => json_encode([
                'query' => $query,
                'variables' => $variables,
                'operation_name' => 'testUpload',
            ]),
            'map' => json_encode($map),
        ];

        $_SERVER['CONTENT_TYPE'] = 'multipart/form-data';

        $processedRequest = BodyParser::processRequest($params, ['method' => 'POST']);

        $variables['uploads'] = [
            0 => $file1,
            1 => $file2,
        ];

        $this->assertSame($variables, $processedRequest['variables'], 'uploaded files should have been injected into variables');
    }

    public function testNonMultipartRequestAreNotTouched(): void
    {
        $params = [
            'operations' => json_encode([
                'query' => '{my query}',
                'variables' => [],
                'operation_name' => 'op',
            ]),
        ];

        $processedRequest = BodyParser::processRequest($params, ['method' => 'POST']);

        $this->assertSame($params, $processedRequest);
    }

    public function testEmptyRequestShouldThrows(): void
    {
        $_SERVER['CONTENT_TYPE'] = 'multipart/form-data';

        $this->expectException(RequestError::class);
        $this->expectExceptionMessage('The request must define a `map`');

        BodyParser::processRequest([], ['method' => 'POST']);
    }

    public function testOtherContentTypeShouldNotBeTouched(): void
    {
        $_SERVER['CONTENT_TYPE'] = 'application/json';

        $processedRequest = BodyParser::processRequest([], ['method' => 'POST']);

        $this->assertSame([], $processedRequest);
    }
}
