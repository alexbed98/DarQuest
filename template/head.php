<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DarQuest</title>
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link href="/public/assets/bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="/public/assets/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js" defer></script>
    
    <?php 
    if (!empty($jsAdd)) {

        foreach ($jsAdd as $path) {
            echo '<script src="' . $path . '" defer></script>';
        }

    }    

    if (!empty($cssAdd)) {

        foreach ($cssAdd as $path) {
            echo '<link rel="stylesheet" href="' . $path . '" />';
        }

    }  
    ?>
</head>