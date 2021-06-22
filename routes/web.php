<?php

use Encore\FileSelector\Http\Controllers\FileSelectorController;

Route::post('file-selector/file-list', FileSelectorController::class . '@getFileList');

Route::post('file-selector/file-upload', FileSelectorController::class . '@upload');
