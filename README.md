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
├── docker-volume
│   └── html
│       ├── index.php
│       └── info.php
├── nginx
│   ├── Dockerfile
│   └── default.conf
└── php
    └── Dockerfile
``` 

<br>

### Docker-compose.yml

此為 Docker-compose 設定檔案，以下簡單說明各段程式用途，詳細請參考 [個人Blog/docker](https://pin-yi.me/docker/) 。

我們在執行多個容器時，需要重複的下 `run` 指令來執行，以及容器與容器之間要做關聯也要記得每一個之間要怎麼連結，會變得很麻煩且不易管理，所以有了 `docker-compose` 可以將多個容器組合起來，變成一個強大的功能。

只要寫一個 `docker-compose.yml` 把所有會使用到的 Docker image 以及每一個容器之間的關連或是網路的設定都寫上去，最後再使用 `docker-compose up` 指令，就可以把所有的容器都執行起來囉！

<br>

我們就直接來實作我們這次的標題，要使用 docker-compose 來整合 PHP MySQL Nginx 環境。

1. 我們先開啟一個資料夾，取名叫 `docker-compose` ，來放置我們的 docker-compose 檔案
2. 接著新增 `docker-compose.yml` 檔案，要準備來撰寫我們的設定檔囉！ 由於內容有點長，所以我分段說明，([這邊有放已經寫好的檔案歐](https://github.com/880831ian/docker-compose-php-mysql-nginx))

<br>

```yml
version: "3.8"

services:
... 省略 ....
```
可以看到一開頭，會先寫版本，這邊代表的是會使用 3.8 版本的設定檔，詳細版本對照可以參考 [Compose file versions and upgrading](https://docs.docker.com/compose/compose-file/compose-versioning/) 

services 可以設定用來啟動多的容器，裡面我們總共放了三個容器，分別是 nginx、php、mysql 。

那我們來看看 nginx 裡面放了什麼吧！我會依照程式碼往下說明，有不清楚的可以底下留言！

<br>

#### nginx

```yml
  nginx:
    build: ./nginx/
    container_name: nginx
    ports:
      - 7777:80
    volumes:
      - ./docker-volume/log/:/var/log/nginx/
```


nginx 的 `build` 就是要執行這個 nginx 容器的映像檔，還記得我們也可以使用 Dockerfile 來撰寫映像檔案嗎！?
 
由於我們還要設定其他內容，所以特別另外拉一個 nginx 資料夾來放置，裡面放了兩個檔案，分別是 Dockerfile、default.conf。

Dockerfile 檔案裡面會使用 nginx 版本 1.20 ，並將 default.conf 複製到容器的 /etc/nginx/conf.d/default.conf 來取代設定。

以及我們使用 `ports` 將容器80 Port 指向本機 7777 Port ，格式是 `本機 Port : 容器 Port`，

再使用 `volumes` 來設定我們 nginx 容器 `log` 資料夾映射到本機的 `./docker-volume/log/` 資料夾。

<br>

#### php

```yml
  php:
    build: ./php/
    container_name: php
    expose:
      - 9000
    volumes:
      - ./docker-volume/html/:/var/www/html/
```

php 的 `build` 是要執行這個 php 容器的映像檔，由於我們還要設定其他內容，所以特別另外拉一個 php 資料夾來放置 Dokcerfile。

Dockerfile 檔案裡面會使用 php 版本 7.4-fpm，並且在容器執行 `docker-php-ext-install`、`mysqli`。

並將 Port 9000 發佈於本機，再使用 `volumes` 來設定 `/var/www/html` 網站根目錄映射到本機的 `./docker-volume/html/` 資料夾。

<br>

#### mysql

```yml
  mysql:
    image: mysql:8.0.28
    container_name: mysql
    volumes:
      - ./docker-volume/mysql/:/var/lib/mysql
    environment:
      MYSQL_ROOT_PASSWORD: secret
      MYSQL_DATABASE: mydb
      MYSQL_USER: myuser
      MYSQL_PASSWORD: password
```

mysql 使用的映像檔是 mysql 版本是 8.0.28，我們為了要保留資料庫的資料，所以將容器的 `/var/lib/mysql` 映射到本地端 `./docker-volume/mysql` 資料夾。

最後的環境變數，設定 root 帳號的登入密碼，以及要使用的資料庫、使用者的帳號、使用者的密碼。

<br>

最後在上面的 ([這邊有放已經寫好的檔案歐](https://github.com/880831ian/docker-compose-php-mysql-nginx)) 裡面還有多一個 docker-volume/html 的資料夾，就是我們剛剛映射到本地端的資料夾，資料夾內已經放有連線測試的檔案，輸入網址 `http://127.0.0.1:7777/index.php`，如果開啟後有顯示下方畫面，就代表我們成功用 docker-compose 將 PHP MySQL Nginx 整合再一起囉！


![圖片](https://raw.githubusercontent.com/880831ian/docker-compose-php-mysql-nginx/master/images/localhost-7777.png)
