<h1>フリマアプリ</h1>
<h2>環境構築</h2>
Dockerビルド</br>
1 git clone リンク</br>
2 docker-compose up -d -build</br>

MySQLは、OSによって起動しない場合があるのでそれぞれのPCに合わせてdocker-compose.ymlファイルを編集してください。</br>
<h2>Laravel開発環境</h2>
　
1 docker-compose exec php bash</br>
2 composer install</br>
3 .env.exampleファイルから.envを作成し、環境変数を変更</br>
4 php artisan key:generate</br>
5 php artisan migrate</br>
6 php artisan db:seed</br>
<h2>使用技術</h2>

PHP 8.4.4</br>
Laravel 8.83.8</br>
MySQL 8.02.6</br>
URL</br>
開発環境: http://localhost/ phpMyAdmin: http://localhost:8080/
