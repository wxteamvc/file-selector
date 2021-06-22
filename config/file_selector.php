<?php

return [

    /**
     * 只有安装了laravel-module了才可以开启使文件资源区分模块,资源隔离
     */
    'open_modules' => false,

    /**
     * 验证文件hash值,如果hash值相同则不存储文件
     * 验证时会同时对 分类id,可见等级,模块名称,所属用户 进行区分.
     */
    'check_hash' => true,

    /**
     * 检测文件后缀,false或者空数组则不做验证
     */
    'check_suffix' => [
        'jpg',
        'jpeg',
        'png',
    ],


];
