<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <title>Chat App Powered by: Codeigniter4!</title>
    <meta name="description" content="Simple chat application">
    <meta name="author" content="Mario Zahov">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="apple-touch-icon" href="icon.png">

    <?= vite(['resources/js/main.js', 'resources/css/app.scss']) ?>
</head>

<body>
    <div class="container-fluid bg-mini-boxes px-lg-5">
        <header>
            <h1 class="py-2">Chat App</h1>
        </header>
        <?php $this->renderSection('content'); ?>
    </div>
</body>

</html>