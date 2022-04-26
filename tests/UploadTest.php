<?php

namespace WPGraphQLTests\Upload;

use GraphQL\Error\Error;
use GraphQL\Error\InvariantViolation;
use GraphQL\Language\AST\StringValueNode;
use UnexpectedValueException;
use WPGraphQL\Upload\Type\Upload;

class UploadTest extends \WP_UnitTestCase
{
    public function testCanParseUploadedFileInstance(): void
    {
        $file = ['name' => 'image.jpg', 'type' => 'image/jpeg', 'size' => 1455000, 'tmp_name' => '/tmp/random'];
        $actual = Upload::parseValue($file);
        $this->assertSame($file, $actual);
    }

    public function testCanParseUploadedFileInstanceWithoutTmpName(): void
    {
        $file = ['name' => 'image.jpg', 'type' => 'image/jpeg', 'size' => 1455000];
        $actual = Upload::parseValue($file);
        $this->assertEquals($file['name'], $actual['name']);
        $this->assertEquals($file['type'], $actual['type']);
        $this->assertEquals($file['size'], $actual['size']);
        $this->assertStringContainsString(get_temp_dir(),$actual['tmp_name']);
    }

    public function testCannotParseNonUploadedFileInstance(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Could not get uploaded file, be sure to conform to GraphQL multipart request specification. Instead got: ["foo"]');

        Upload::parseValue(['foo']);
    }

    public function testCanNeverBeSerialized(): void
    {
        $this->expectException(InvariantViolation::class);
        $this->expectExceptionMessage('`Upload` cannot be serialized');

        Upload::serialize('foo');
    }

    public function testCanNeverParseLiteral(): void
    {
        $node = new StringValueNode(['value' => 'foo']);

        $this->expectException(Error::class);
        $this->expectExceptionMessage('`Upload` cannot be hardcoded in query, be sure to conform to GraphQL multipart request specification. Instead got: StringValue');

        Upload::parseLiteral($node);
    }
}
