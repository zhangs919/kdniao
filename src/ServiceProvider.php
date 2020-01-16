<?php


namespace Laravelvip\Kdniao;


class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    protected $defer = true;

    public function register()
    {
        $this->app->singleton(Kdniao::class, function () {
            return new Kdniao(config('kdniao.app_id'), config('kdniao.app_key'));
        });

        $this->app->alias(Kdniao::class, 'kdniao');
    }

    public function provides()
    {
        return [Kdniao::class, 'kdniao'];
    }
}