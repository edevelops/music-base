<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
        <title>Music Base</title>
        <link rel="shortcut icon" href="/favicon.ico"  type="image/x-icon"/>
        <link rel="stylesheet" href="./assets/style.css" type="text/css"/>
    </head>
    <body>
        <div id="app"></div>
        
        
        <div style="display: none">
            <?php require './app/components/app/app.component.html'; ?>
            <?php require './app/components/popup/popup.component.html'; ?>
            <?php require './app/components/search-control/search-control.component.html'; ?>
        </div>
        
        <script type="text/javascript" src="./app/vendor/underscore/underscore-min.js"></script>
        <script type="text/javascript" src="./app/vendor/axios/dist/axios.min.js"></script>
        <script type="module" src="./app/main.js"></script>
    </body>
</html>
