<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>API Documentation</title>
    </head>
    <body>
        <div id="swagger-api"></div>
        <script src="{{ route('resource_route', ['filename' => 'swagger.js']) }}"></script>
        @vite("resources/js/swagger.js")
    </body>
</html>
