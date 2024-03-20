<?php

/**
 * 上传管理
 * 上传文件大小将由 php.ini 文件配置决定
 * nginx应配置好client_max_body_size
 * php.ini应配置好upload_max_filesize,post_max_size
 * 
 * @since 2024.3.16
 * @link https://github.com/symfony/symfony/blob/4.2/src/Symfony/Component/HttpFoundation/File/UploadedFile.php
 * */

namespace App\Http\Controllers\Commons;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class UploadController extends Controller
{
    /**
     * 单图上传
     * 
     * @method POST
     * */
    public function image(): object
    {
        // 基础验证
        if (! $this->request->hasFile('photo') || ! $this->request->file('photo')->isValid()) {
            return response()->json($this->fail('未检测到可上传的图片'));
        }

        // 允许上传的扩展类型
        $allowExtension = ['jpg', 'png', 'gif', 'webp'];
        if (! in_array($this->request->file('photo')->extension(), $allowExtension)) {
            return response()->json($this->fail('仅支持'.implode(',', $allowExtension).'格式'));
        }

        return response()->json($this->success($this->_doUpload('photo', 1, 'images'), '图片上传成功'));
    }

    /**
     * 单视频上传
     * 
     * @method POST
     * */
    public function video(): object
    {
        // 共计60s
        set_time_limit(30);

        // 基础验证
        if (! $this->request->hasFile('video') || ! $this->request->file('video')->isValid()) {
            return response()->json($this->fail('未检测到可上传的视频'));
        }

        // 允许上传的扩展类型
        $allowExtension = ['mp4'];
        if (! in_array($this->request->file('video')->extension(), $allowExtension)) {
            return response()->json($this->fail('仅支持'.implode(',', $allowExtension).'格式'));
        }

        return response()->json($this->success($this->_doUpload('video', 2, 'videos'), '视频上传成功'));
    }

    /**
     * 单附件(文档)上传
     * 
     * @method POST
     * */
    public function attachment(): object
    {
        // 基础验证
        if (! $this->request->hasFile('attachment') || ! $this->request->file('attachment')->isValid()) {
            return response()->json($this->fail('未检测到可上传的附件(文档)'));
        }

        // 允许上传的扩展类型
        $allowExtension = ['xls', 'xlsx', 'doc', 'docx', 'pdf', 'zip'];
        if (! in_array($this->request->file('attachment')->extension(), $allowExtension)) {
            return response()->json($this->fail('仅支持'.implode(',', $allowExtension).'格式'));
        }

        return response()->json($this->success($this->_doUpload('attachment', 3, 'attachments'), '附件上传成功'));
    }

    /**
     * 生成随机文件名
     * */
    private function _generateFileName(string $uploadKey = ''): string {
        return uniqid(date('d')).'.'.$this->request->file($uploadKey)->extension();
    }

    /**
     * 执行上传
     * */
    private function _doUpload(string $ext, int $type, string $dir): array
    {
        $uploadDir = $dir.'/' .date('Y').'/' .date('m');
        $filename = $this->_generateFileName($ext);
        $url = $uploadDir.'/' .$filename;
        $size = $this->request->file($ext)->getSize();
        $this->request->file($ext)->move(env('FILE_UPLOAD_DIRECTORY').$uploadDir, $filename);
        DB::table('sys_file_uploads')->insert([
            'admin_id'=>$this->request->adminInfo['adminId'],
            'original_name'=>$this->request->file($ext)->getClientOriginalName(),
            'url'=>$url,
            'size'=>$size,
            'type'=>$type,
            'created_at'=>$this->getDatetime()
        ]);
        $res = ['url'=>env('RESOURCE_URL').$url];
        if ($type === 1) {
            $res['alt'] = $filename;
        }
        return $res;
    }
}