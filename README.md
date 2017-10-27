# yii2-mailerqueue
async mailer
使用队列异步发送邮件

###  环境

* PHP >= 5.4<br>
* yiisoft/yii2-redis >= 2.0.0<br>
* composer<br>

### 安装<br>
```php
composer require zangsilu/yii2-mailer-queue
```
### 使用<br>

#### 1.配置文件<br>

```php
<?php
    'redis' => [
           'class' => 'yii\redis\Connection',
        'hostname' => 'localhost',
        'port' => 6379,
        'database' => 0,//默认16个库 0-15
    ],
    'mailer' => [
        //'class' => 'yii\swiftmailer\Mailer', //服务器类
        'class' => 'baidumes\mailerqueue\MailerQueue', 
        'db' => '1', //将邮件信息存储到redis 库 1中
        'key' => 'mails',//存到reis中的键
        'viewPath' => '@common/mail',
        'useFileTransport' => false, //这句一定有，false发送邮件，true只是生成邮件在runtime文件夹下，不发邮件
        'transport' => [
        'class' => 'Swift_SmtpTransport', //使用的类
            'host' => 'smtp.163.com', //邮箱服务一地址
            'username' => 'baidumes@163.com',
            'password' => 'gp806421831',
            'port' => '25', //服务器端口
            'encryption' => '',//加密方式
        ],
    ],
?>
```


#### 2：创建控制台指令

```php
<?php
namespace console\controllers;

use yii\console\Controller;
use yii;

class MailerController extends Controller {
    public function actionSend() {
        Yii::$app->mailer->process();
        echo '发送完毕!';
    }
}
```
#### 3：将指令加入lunux定时任务(每分钟检测一次)
crontab -e
```php
*/1 * * * * php yii mailer-queue/send > ./log/mailer-send.log
```
