# wei-admin-plus后端源码

基于`Lumen (10.0.1) (Laravel Components ^10.0)`构建

## 一、环境要求

- PHP >= 8.1
- OpenSSL PHP Extension
- Mbstring PHP Extension
- MySQL PDO Extension
- Redis PHP Extension
- GD Extension
- fileinfo Extension


## 二、项目安装

```shell
composer install
```



## 三、项目配置

- 参考`.env.example`创建`.env`配置文件，若redis服务器不在本地甚至需要密码，还需增加redis配置项。

  ```shell
  REDIS_CLIENT=phpredis
  REDIS_HOST=127.0.0.1
  REDIS_PASSWORD=
  REDIS_PORT=6379
  REDIS_DB=0
  ```

- 配置php.ini文件`upload_max_filesize,post_max_size`的值，若有必要，还需再配置nginx的`client_max_body_size`

- 切换到根目录`api`，创建队列失败任务表迁移

  ```shell
  php artisan queue:failed-table
  php artisan migrate
  ```

- Windows环境下在根目录下启动队列进程处理器

  ```shell
  php artisan queue:listen
  ```
  
- Linux环境下您需要安装Supervisor来一直保持`queue:work`进程运行，安装和使用请参考：[Supervisor配置](https://learnku.com/docs/laravel/10.x/queues/14873#e45763)。

  

## 四、Nginx配置参考

```nginx
server {
    listen 80;
    #listen [::]:80;
    server_name api.weiadminplus.xyz;
    root /home/web/wei-admin-plus/api/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        #fastcgi_pass 127.0.0.1:9000;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```
