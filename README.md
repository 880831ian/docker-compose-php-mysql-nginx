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

services 可以設定用來啟動多的容器，裡面我們總共放了四個容器，分別是 nginx、php、app-data、mysql ，至於為什麼會多一個 app-data ，後面會講到。

那我們來看看 nginx 裡面放了什麼吧！我會依照程式碼往下說明，有不清楚的可以底下留言！

<br>

#### nginx

```yml
  nginx:
    build: ./nginx/
    container_name: nginx
    ports:
      - 7777:80
    networks:
      mynetwork:
    volumes_from:
      - app-data
```


nginx 的 `build` 就是要執行這個 nginx 容器的映像檔，還記得我們也可以使用 Dockerfile 來撰寫映像檔案嗎！?
 
由於我們還要設定其他內容，所以特別另外拉一個 nginx 資料夾來放置，裡面放了兩個檔案，分別是 Dockerfile、default.conf。

Dockerfile 檔案裡面會使用 nginx 版本 1.20 ，並將 default.conf 複製到容器的 /etc/nginx/conf.d/default.conf 來取代設定。

以及我們使用 `ports` 將容器80 Port 指向本機 7777 Port ，格式是 `本機 Port : 容器 Port`，

再使用 `network` 設定我們 nginx 容器的網路要使用 mynetwork，最後將路徑共同掛載到 app-data。

<br>

#### php

```yml
  php:
    build: ./php/
    container_name: php
    networks:
      mynetwork:    
    expose:
      - 9000
    volumes_from:
      - app-data
```

php 的 `build` 是要執行這個 php 容器的映像檔，由於我們還要設定其他內容，所以特別另外拉一個 php 資料夾來放置 Dokcerfile。

Dockerfile 檔案裡面會使用 php 版本 7.4-fpm，並且在容器執行 `docker-php-ext-install`、`mysqli`。

並將 Port 9000 發佈於本機，再使用 `network` 設定我們 php 容器的網路要使用 mynetwork，最後將路徑共同掛載到 app-data。

<br>

#### app-data

```yml
  app-data:
    image: php:7.4-fpm
    container_name: app-data
    volumes:
      - ~/Desktop/docker-volume/html/:/var/www/html/
      - ~/Desktop/docker-volume/log/:/var/log/nginx/
    command: "true"
```

app-data 使用的映像檔是 php 版本是 7.4-fpm，我們為了要本機端可以修改 nginx 根目錄的內容，所以將容器的 /var/www/html 映射到本地端 Desktop/docker-volume/html 資料夾，

以及想要查看 log ，所以將容器的 /var/log/nginx 映射到本地端 Desktop/docker-volume/log 資料夾。

<br>

#### mysql

```yml
  mysql:
    image: mysql:8.0.28
    container_name: mysql
    networks:
      mynetwork:
        ipv4_address: 172.18.0.2
    volumes:
      - ~/Desktop/docker-volume/mysql/:/var/lib/mysql
    environment:
      MYSQL_ROOT_PASSWORD: secret
      MYSQL_DATABASE: mydb
      MYSQL_USER: myuser
      MYSQL_PASSWORD: password
```

mysql 使用的映像檔是 mysql 版本是 8.0.28，我們一樣使用 mynetwork 來當我們的網路，但比較特別的是，我們用 ipv4 將他設定固定 IP，方便我們後續連接資料庫使用。

我們為了要保留資料庫的資料，所以將容器的 /var/lib/mysql 映射到本地端  Desktop/docker-volume/mysql 資料夾。

最後的環境變數，設定 root 帳號的登入密碼，以及要使用的資料庫、使用者的帳號、使用者的密碼。

<br>

#### network

```yml
networks:
  mynetwork:
    driver: bridge
    ipam:
      config:
        - subnet: 172.18.0.0/24
```

我們剛剛有設定一個叫 mynetwork 的網路設定，我們在最後要來定義一下他的模式以及內容，我們將他的模式定義成 橋接 (bridge) ，也是 Docker 預設的模式，再設定子網路 172.18.0.0/24。

<br>

最後在上面的 ([這邊有放已經寫好的檔案歐](https://github.com/880831ian/docker-compose-php-mysql-nginx)) 裡面還有多一個 html 的資料夾，裡面放的檔案是可以放在 Desktop/docker-volume/html 資料夾中，

index.php 的內容會顯示 php 的版本、以及使用我們所設定的 mysql ip、使用者帳號、密碼 來做對 MySQL 做測試，如果開啟後可以顯示下方畫面，就代表我們成功用 docker-compose 將 PHP MySQL Nginx 整合再一起囉！


![圖片](https://raw.githubusercontent.com/880831ian/docker-compose-php-mysql-nginx/master/images/localhost-7777.png)
