<?php

namespace WPGraphQL;

use WPGraphQL\Upload\Request\BodyParser;
use WPGraphQL\Upload\Type\Upload;

add_action('graphql_init', function () {
    Upload::registerType();
    BodyParser::init();
});
