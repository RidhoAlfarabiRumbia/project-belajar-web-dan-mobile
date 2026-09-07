<?php

$base = "/portal_magang/";

function head($title)
{
    global $base;
    ?>

    <!doctype html>
    <html lang="id">

    <head>

        <meta charset="utf-8">

        <meta
            name="viewport"
            content="width=device-width, initial-scale=1"
        >

        <title><?= htmlspecialchars($title) ?></title>

        <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
            rel="stylesheet"
        >

        <link
            href="<?= $base ?>assets/style.css"
            rel="stylesheet"
        >

    </head>

    <body>

    <div class="container-fluid">

        <div class="row">

            <div class="col-md-2 sidebar p-0">

                <h5 class="text-white p-3">
                    Portal Magang
                </h5>

                <a href="<?= $base ?>dashboard.php">
                    Dashboard
                </a>

                <a href="<?= $base ?>materi/index.php">
                    Materi
                </a>

                <a href="<?= $base ?>kelas/index.php">
                    Kelas
                </a>

                <a href="<?= $base ?>siswa/index.php">
                    Daftar Siswa
                </a>

                <a href="<?= $base ?>logbook/index.php">
                    Logbook
                </a>

                <a href="<?= $base ?>logout.php">
                    Logout
                </a>

            </div>

            <main class="col-md-10 p-4">

    <?php
}


function foot()
{
    ?>

            </main>

        </div>

    </div>

    </body>

    </html>

    <?php
}
?>