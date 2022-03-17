<!DOCTYPE html>

<head>
    <title>Hello Ian!</title>
</head>

<body>
    <h1>Hello Ian!</h1>
    <?php
    echo '正在運行 PHP , version：' . phpversion();

    $con = mysqli_connect("172.18.0.2", "myuser", "password");

    if (!$con) {
        echo "錯誤! 無法連線 MySQL，錯誤代碼：" . mysqli_connect_errno();
        exit;
    };
    echo "<br>成功連線 MySQL , version：" . mysqli_get_server_info($con);
    echo " , 連線主機：" . mysqli_get_host_info($con);

    mysqli_close($con);
    ?>
</body>

</html>