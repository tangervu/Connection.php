Connection.php
==============

A PHP class to transfer data using different protocols (sftp, ftp, http, etc). Utilizes PHPs ssh2, ftp and curl functions if available.

Example
-------

    <?php
    $conn = new Connection('ftp://ftp.funet.fi');
    //List directory contents
    print_r($conn->ls());
    //Display contents of the README file
    echo $conn->get('README');

