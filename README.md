# artisan route:list... for Lumen

artisan route:list for Lumen projects

# Installation

1. `composer require mlntn/lumen-artisan-route-list "~1"`

2. Add the following line to the $commands array in app/Console/Kernel.php:

    `\Mlntn\Console\Commands\RouteList::class,`

3. artisan route:list

# Disclaimer

I didn't write most of this code. It comes straight from [laravel/framework](https://github.com/laravel/framework) by [@taylorotwell](https://github.com/taylorotwell). I did have to tweak a bunch of things to get it going for Lumen. Enjoy!