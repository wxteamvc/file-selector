<?php

namespace Encore\FileSelector;

use Encore\Admin\Admin;
use Encore\Admin\Form;
use Encore\FileSelector\Commands\InstallCommand;
use Illuminate\Support\ServiceProvider;

class FileSelectorServiceProvider extends ServiceProvider
{

    protected $commands = [InstallCommand::class];

    /**
     * {@inheritdoc}
     */
    public function boot(FileSelector $extension)
    {
        if (! FileSelector::boot()) {
            return ;
        }

        if ($views = $extension->views()) {
            $this->loadViewsFrom($views, 'file-selector');
        }

        $this->registerPublishing();

        if ($this->app->runningInConsole() && $assets = $extension->assets()) {
            $this->publishes(
                [$assets => public_path('vendor/fengwuyan/file-selector')],
                'file-selector'
            );
        }

        // 加载插件
        Admin::booting(function () {
            Form::extend('fileSelector', FormFileSelector::class);
        });

        $this->app->booted(function () {
            FileSelector::routes(__DIR__.'/../routes/web.php');
        });
    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    protected function registerPublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__.'/../database/migrations' => database_path('migrations')], 'file-selector-migrations');
            $this->publishes([__DIR__.'/../resources/assets' => public_path('vendor/fengwuyan/file-selector')], 'file-selector-assets');
            $this->publishes([__DIR__.'/../config/file_selector.php' => config_path('file_selector.php')], 'file-selector-config');
        }
    }

    public function register()
    {
        $this->commands($this->commands);
    }
}
