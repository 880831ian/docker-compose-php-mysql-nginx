# Docker-compose 整合 PHP MySQL Nginx

實作各版本說明：

本機

* 作業系統：macOS 11.6
* Docker：Docker version 20.10.12, build e91ed57

Docker 

*  PHP：7.4-fpm
*  MySQL：8.0.28
*  Nginx：1.20

## 檔案說明

檔案目錄

```sh
.
├── Docker-compose.yml
├── README.md
├── html
│   ├── index.php
│   └── info.php
├── nginx
│   ├── Dockerfile
│   └── default.conf
└── php
    └── Dockerfile
``` 

<br>

### Docker-compose.yml

此為 Docker-compose 設定檔案，以下簡單說明各段程式用途，詳細請參考 [個人Blog/docker](https://pin-yi.me/docker/) 。

services 裡面分別放置我們三個服務，nginx、php、mysql 以及 nginx 跟 php 共同掛載的 app-data。

<br>

#### nginx

nginx 會去找 nginx 資料夾裡面的 Dockerfile 來建立映像檔，我們將容器80 Port 指向本機 7777 Port，網路使用 mynetwork，將路徑共同掛載到 app-data。

<br>

#### php

php 會去找 php 資料夾裡面的 Dockerfile 來建立映像檔，我們設定 php 容器會開放 9000 Port，網路使用 mynetwork，，將路徑共同掛載到 app-data。

<br>

#### app-data 

app-data 我們將 nginx 的 /var/www/html 網頁目錄映射到 Desktop/docker-volume/html 資料夾，以及將 nginx 的 /var/log/nginx 的紀錄檔映射到 Desktop/docker-volume/log 資料夾。

<br>

#### mysql

mysql 使用 docker hub 映像檔，版本是 8.0.28，網路使用 mynetwork 且設定固定 IPV4 172.18.0.2，將 mysql /var/lib/mysql 路徑映射到 Desktop/docker-volume/mysql 資料夾，設定環境變數(root 密碼、資料庫、帳號、密碼)。

<br>

#### networks

使用橋接 bridge 網路模式，子網段設定 172.18.0.0/24。

<br>

### html

這個資料夾是為了代表我們映射在桌面的 docker-volume/html 範例檔案，裡面有 index.php ，會顯示 php 版本、mysql 是否連接成功 ; info.php 則是可以查看 phpinfo() 。

![圖片]()

<br>

### nginx

裡面放置 default.conf 是 nginx 設定檔案，另一個是 Dockerfile，內容包含要使用 nginx ，版本 1.20 ，以及要把 default.conf 複製到容器的 /etc/nginx/conf.d/default.conf。

### php

裡面只放了 php 的 Dockerfile，內容包含要使用 php ，版本 7.4-fpm，以及映像檔要使用的 docker-php-ext-install mysqli。