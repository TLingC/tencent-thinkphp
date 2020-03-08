<?php

define('TEXT_REG', '#\.html.*|\.js.*|\.css.*|\.html.*#');
define('BINARY_REG', '#\.gif.*|\.jpg.*|\.png.*|\.jepg.*|\.swf.*|\.bmp.*|\.ico.*#');

/**
 * handler static files
 */
function handlerStatic($path)
{
    $filename = __DIR__ . "/public" . $path;
    $handle   = fopen($filename, "r");
    $contents = fread($handle, filesize($filename));
    fclose($handle);

    $base64Encode = false;
    $headers = [
        'Content-Type'  => '',
        'Cache-Control' => "max-age=8640000",
        'Accept-Ranges' => 'bytes',
    ];
    $body = $contents;
    if (preg_match(BINARY_REG, $path)) {
        $base64Encode = true;
        $headers = [
            'Content-Type'  => '',
            'Cache-Control' => "max-age=86400",
        ];
        $body = base64_encode($contents);
    }
    return [
        "isBase64Encoded" => $base64Encode,
        "statusCode" => 200,
        "headers" => $headers,
        "body" => $body,
    ];
}

function handler($event, $context)
{
    require __DIR__ . '/vendor/autoload.php';

    // init path
    $path = str_replace("//", "/", $event->path);

    if (preg_match(TEXT_REG, $path) || preg_match(BINARY_REG, $path)) {
        return handlerStatic($path);
    }

    // init body
    $req = $event->body ?? '';

    // init headers
    $headers = $event->headers ?? [];
    $headers = json_decode(json_encode($headers), true);

    // init data
    $data = !empty($req) ? json_decode($req, true) : [];

    // execute thinkphp app request, get response
    $app = new \think\App();
    $http = $app->http;
    
    $request = new \think\Request($app);

    $request->setPathinfo($path);
    $request->setMethod($event->httpMethod);
    $request->withInput($event->body);
    $request->withHeader($headers);

    $response = $http->run();

    $http->end($response);

    // init content
    $body = $response->getContent();
    $contentType = $response->getHeader('Content-Type');

    return [
        'isBase64Encoded' => false,
        'statusCode' => 200,
        'headers' => [
            'Content-Type' => $contentType
        ],
        'body' => $body
    ];
}
