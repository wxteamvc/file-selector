<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFileMediaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('file_media', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('category_id')->comment('分类id');
            $table->string('modules', 50)->comment('所属模块');
            $table->string('visibility_level', 20)->comment('资源是否公开：public=公开，private=私有');
            $table->integer('user_id')->comment("用户ID");
            $table->enum('type', ['image', 'video', 'audio', 'powerpoint', 'code', 'zip', 'text', 'other'])->comment('类型');
            $table->string('bucket')->nullable()->comment("bucket");
            $table->string('disk')->comment("磁盘");
            $table->string('path')->comment('文件路径');
            $table->Integer('size')->comment('文件大小(K)');
            $table->string('file_ext')->comment("文件后缀");
            $table->string('file_name')->comment("文件名称");
            $table->string('meta')->comment("属性");
            $table->string('hash')->comment("文件哈希值");
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->softDeletes();
            $table->index(['user_id', 'type']);
            $table->index(['file_name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('file_media', function (Blueprint $table) {
            //
        });
    }
}
