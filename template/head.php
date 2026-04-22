<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DarQuest</title>
    <link rel="icon" type="image/x-icon" href="<?= URL_ROOT ?>favicon.ico">
    <link href="<?= URL_ROOT ?>public/assets/bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="<?= URL_ROOT ?>public/assets/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js" defer></script>
    
    <?php 
    if (!empty($jsAdd)) {

        foreach ($jsAdd as $path) {
            $normalizedPath = (str_starts_with($path, '/'))
                ? rtrim(URL_ROOT, '/') . $path
                : $path;
            echo '<script src="' . $normalizedPath . '" defer></script>';
        }

    }    

    if (!empty($cssAdd)) {

        foreach ($cssAdd as $path) {
            $normalizedPath = (str_starts_with($path, '/'))
                ? rtrim(URL_ROOT, '/') . $path
                : $path;
            echo '<link rel="stylesheet" href="' . $normalizedPath . '" />';
        }

    }  
    ?>
</head>