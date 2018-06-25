# Introduce
an api practice of yii2-app-basic framework.

## Getting started
- clone this project to your server `web_root` or any other directory as you like.
- modify `config/db.php` to set a db connection.
- make sure `php` and `composer` is installed already, open a console and switch to your project directory, then excute `composer update` to download dependencies (skip this step).
- keep in the app root directory, execute `php ./yii create-table/create` to init tables.
- add a apache or nginx site config ([see this for detail][add_config]), after that you need restart server to make changes take effect.
- visit `http://{domain}/api/account/open?user_id=101` to test.

## Api document
for convenience all api accept `GET` request so you can visit it in browser directly.  

1. Open account  
url: <http://{domain}/api/account/open?user_id=101>  
method: GET  
params:  
    `user_id` int, choose one as you like  

2. Close account  
url: <http://{domain}/api/account/close?user_id=101&card_id=6330000000000001>  
method: GET  
params:  
    `user_id` int, the one you used to open account.  
    `card_id` string, your card_id.  

3. Query balance  
url: <http://{domain}/api/account/balance?card_id=6330000000000001>  
method: GET  
params:  
    `card_id` string, your card_id.  

4. Withdraw money  
url: <http://{domain}/api/account/withdraw?card_id=6330000000000001&amount=100>  
method: GET  
params:  
    `card_id` string, your card_id.  
    `amount` int/float, money amount.  

5. Deposit money  
url: <http://{domain}/api/account/deposit?card_id=6330000000000001&amount=100>  
method: GET  
params:   
    `card_id` string, your card_id.  
    `amount` int/float, money amount.  

6. Transfer money  
url: <http://{domain}/api/account/transfer?from_card_id=6330000000000001&to_card_id=6330000000000003&amount=100>  
method: GET  
params:   
    `from_card_id` string, your card_id.  
    `to_card_id` string, your other card_id (free charge) or other's card_id (need charge fee).  
    `amount` int/float, money amount.  

[add_config]: https://www.yiiframework.com/doc/guide/1.1/en/quickstart.apache-nginx-config
