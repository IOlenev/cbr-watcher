# cbr-watcher
Collecting exchange rates service

Stack: Nginx + Php (Symfony) + Redis (cache & storage & queue brokers) 

### Requirements
Linux, MacOS with Docker and Git installed

### Installation
- clone repository
> git clone https://github.com/IOlenev/cbr-watcher.git
- execute project's builder script
> sh build.sh
- execute project's functional tests
> docker exec cbrw-php php vendor/bin/phpunit
- open welcome [link &raquo;](http://localhost:8800) 

### Usage
1. Web
   - get exchanges rates by date via http request `http://localhost:8800/[ticker]/[date]/[base-currency]`
     > examples:
     > 
     > http://localhost:8800/USD
     > 
     > http://localhost:8800/USD/20240812
     > 
     > http://localhost:8800/USD/20240812/AUD

2. Cli
   - warm up command (getting rates for a 180 days period) `docker exec cbrw-php php bin/console app:warmup [ticker] [base-currency]`
      > examples:
      > 
      > docker exec cbrw-php php bin/console app:warmup USD
      > 
      > docker exec cbrw-php php bin/console app:warmup USD AUD
   - get exchange rates by date command `docker exec cbrw-php php bin/console app:get-ticker [ticker] [date] [base-currency]`
     > examples:
     > 
     > docker exec cbrw-php php bin/console app:get-ticker USD
     > 
     > docker exec cbrw-php php bin/console app:get-ticker USD 20240812
     > 
     > docker exec cbrw-php php bin/console app:get-ticker USD 20240812 AUD

### Service
- get info about the rates_preload queue
> docker exec cbrw-redis redis-cli xinfo groups rates_preload- get info about the rates_preload queue
- get info about the building rur index queue
> docker exec cbrw-redis redis-cli xinfo groups index_rur
- get info about the building base currency index queue
> docker exec cbrw-redis redis-cli xinfo groups index_base
