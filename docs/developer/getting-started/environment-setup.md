---
---

# Environment setup


## Development OS
We strongly recommend that you develop and deploy the dashboard on a computer running a linux operating system. Preferably, the latest LTS version of [Ubuntu](https://ubuntu.com/about/release-cycle).

And as a web server, Nginx with php-fpm.

## Dependencies
- Php 8.2 (or greater, including 8.5)
- [Composer](https://getcomposer.org/)
- Various php extensions such as,
    - BCMath PHP Extension
    - Ctype PHP Extension
    - cURL PHP Extension
    - DOM PHP Extension
    - Fileinfo PHP Extension
    - JSON PHP Extension
    - Mbstring PHP Extension
    - OpenSSL PHP Extension
    - PCRE PHP Extension
    - PDO PHP Extension
    - Redis PHP Extension
    - Tokenizer PHP Extension
    - XML PHP Extension
    - Internationalization extension (Intl) 
- On Ubuntu, the following command will install php and all required extensions

  ```sudo apt install -y php8.3 php8.3-fpm php8.3-cli php8.3-pgsql php8.3-mysql php8.3-zip php8.3-curl php8.3-xml php8.3-mbstring php8.3-intl php8.3-redis php8.3-sqlite3```
- [PostgreSQL](https://www.postgresql.org/)
