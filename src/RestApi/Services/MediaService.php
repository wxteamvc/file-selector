<?php

namespace Encore\FileSelector\RestApi\Services;

use Encore\Admin\Form\Field\File;
use Encore\FileSelector\Models\FileMedia;
use Encore\FileSelector\RestApi\Helpers\FileUtil;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Nwidart\Modules\Facades\Module;

class MediaService
{

    public $err_message;

    public function getMediaList($userId, $keyword, $order, $orderName, $pageSize, $type, $modules)
    {
        $query = FileMedia::query()->where(function ($q) use ($keyword, $type, $modules) {

            if (!empty($keyword))
                $q->where('file_ext', 'like', '%' . $keyword . '%');

            if (!empty($type))
                $q->where('type', $type);

            // 这边要加个判断,是否开启模块隔离
            $open_modules = config('file_selector.open_modules', false);
            if ($open_modules){
                $q->whereIn('modules', array_merge(['default'], [$modules]));
            }


        });

        $list = $query->orderBy($orderName, $order)->paginate($pageSize);

        $dataList = [];

        foreach ($list as $value) {

            $dataList[] = array(
                'id' => $value->id,
                'modules' => $value->modules == 'default' ? "公共" : $value->modules,
                'media_type' => $value->type,
                'path' => $value->path,
                'size' => FileUtil::getFormatBytes($value->size),
                'file_ext' => $value->file_ext,
                'name' => $value->file_name,
                'created_at' => $value->created_at,
            );
        }

        return json_encode(["total" => $list->total(), "data" => $dataList], JSON_UNESCAPED_UNICODE);
    }

    public function upload($userId, UploadedFile $file, $type, $move, $modules = 'default', $category_id = 0, $visibility_level = 'public')
    {
        $mime_type = $file->getMimeType();
        $type_info = $this->_getTypeInfoByMimeType($mime_type);

        // 检测文件格式
        $check_suffix = config('file_selector.check_suffix', false);
        // 如果是数组并且不等于空则做后缀验证
        if (is_array($check_suffix) && !empty($check_suffix)){
            if (!in_array($type_info['suffix'], $check_suffix)){
                $this->err_message = '只能上传(' . implode('、', $check_suffix) . ')格式的文件';
                return false;
            }
        }

        // 哈希值
        $hash = sha1_file($file->getRealPath());

        // 获取是否验证hash配置
        $check_hash = (boolean)config('file_selector.check_hash', true);

        // 如果不验证hash,直接创建
        if ($check_hash === true){
            $has = FileMedia::where([
                'category_id' => $category_id,
                'modules' => $modules,
                'visibility_level' => $visibility_level,
                'hash' => $hash
            ])->first();
            if ($has) return $has;
        }

        //配置上传信息
        config([
            'filesystems.default' => config('union.disk', 'admin')
        ]);

        $disk = config('filesystems.default');

        $bucket = $disk == 'qiniu' ? config('filesystems.disks.qiniu.bucket') : null;

        // 获取是否分模块
        $open_modules = config('file_selector.open_modules', false);

        $folder = $open_modules ?  $modules. "/" . $move->dir : $move->dir; //保存文件夹

        $file_name = $this->_getFileName($move, $file);

        $path = $file->storeAs($folder, $file_name);

        $getFileType = FileUtil::getFileType(Storage::disk(config('admin.upload.disk'))->url($path));

        $meta = $this->_getMeta($file, $getFileType, $type_info['suffix']);


        $data = [
            'category_id' => $category_id,
            'modules' => $modules,
            'visibility_level' => $visibility_level,
            'user_id' => $userId,
            'path' => $path,
            'file_name' => $file_name,
            'size' => $file->getSize(),
            'type' => $getFileType,
            'file_ext' => $file->getClientOriginalExtension(),
            'disk' => $disk,
            'bucket' => $bucket,
            'meta' => $meta,
            'hash' => $hash
        ];

        return FileMedia::query()->Create($data);
    }

    private function _getMeta($file, $getFileType, $format)
    {
        switch ($getFileType) {
            case 'image':
                $manager = new ImageManager();
                $image = $manager->make($file);
                $meta = [
                    'format' => $format,
                    'suffix' => $file->getClientOriginalExtension(),
                    'size' => $file->getSize(),
                    'width' => $image->getWidth(),
                    'height' => $image->getHeight()
                ];
                break;
            case 'video':
            case 'audio':
            case 'powerpoint':
            case 'code':
            case 'zip':
            case 'text':
                $meta = [
                    'format' => $format,
                    'suffix' => $file->getClientOriginalExtension(),
                    'size' => $file->getSize(),
                    'width' => 0,
                    'height' => 0
                ];
                break;
            default :
                $meta = [
                    'format' => $format,
                    'suffix' => $file->getClientOriginalExtension(),
                    'size' => $file->getSize(),
                    'width' => 0,
                    'height' => 0
                ];;
        }
        return $meta;
    }

    private function _getTypeInfoByMimeType($mt)
    {
        $arr = explode('/', $mt);
        return [
            'type' => $arr[0],
            'suffix' => $arr[1]
        ];
    }

    private function _getFileName($move, $file)
    {
        $fileName = $file->getClientOriginalName();
        if ($move->fileNameIsEncrypt)
            $fileName = md5(rand(1, 99999) . $file->getClientOriginalName()) . "." . $file->getClientOriginalExtension();

        return $fileName;
    }
}
