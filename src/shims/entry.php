<?php

define('DISALLOW_REG', '#\.htaccess.*|\.php.*#');
/**
 * handler static files
 */
function handlerStatic($filename)
{
    $handle = fopen($filename, "r");

    $contents = '';
    if(filesize($filename) > 0) {
        $contents = fread($handle, filesize($filename));
    }

    fclose($handle);

    $headers = [];

    switch(pathinfo($filename, PATHINFO_EXTENSION)) {
        case 'css':
            $headers['Content-Type'] = 'text/css';
            break;
        case 'svg':
            $headers['Content-Type'] = 'image/svg+xml';
            break;
        default:
            $headers['Content-Type'] = mime_content_type($filename);
            break;
    }

    if (substr($headers['Content-Type'], 0, 4) === 'text') {
        $base64Encode = false;
        $body = $contents;
    } else {
        $base64Encode = true;
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

    if(substr($event->path, 0, 1) === '/') $path = substr($event->path, 1);

    $filename = __DIR__ . "/public/" . $path;

    if (!empty($path) && file_exists($filename) && !preg_match(DISALLOW_REG, $path)) {
        return handlerStatic($filename);
    }

    $headers = $event->headers ?? [];
    $headers = json_decode(json_encode($headers), true);

    $_GET = $event->queryString ?? [];
    $_GET = json_decode(json_encode($_GET), true);

    if(!empty($event->body)) {
        $_POSTbody = explode("&", $event->body);
        foreach ($_POSTbody as $postvalues) {
            $tmp=explode("=", $postvalues);
            $_POST[$tmp[0]] = $tmp[1];
        }
    }

    $app = new \think\App();
    $app->setRuntimePath(DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR);

    $http = $app->http;
    
    $request = $app->make('request', [], true);

    $request->setPathinfo($path);
    $request->setMethod($event->httpMethod);
    $request->withHeader($headers);

    $response = $http->run($request);

    $http->end($response);


    $body = $response->getContent();
    $contentType = $response->getHeader('Content-Type');

    return [
        'isBase64Encoded' => false,
        'statusCode' => $response->getCode(),
        'headers' => [
            'Content-Type' => $contentType
        ],
        'body' => $body
    ];
}
