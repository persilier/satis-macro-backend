<?php

namespace Satis2020\ServicePackage\Repositories;

use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\File;
/**
 * Class FileRepository
 * @package Satis2020\ServicePackage\Repositories
 */
class FileRepository
{
    /**
     * @var File
     */
    private $file;

    /**
     * FileRepository constructor.
     * @param File $file
     */
    public function __construct(File $file)
    {
        $this->file = $file;
    }

    /***
     * @param $id
     * @return mixed
     */
    public function getById($id) {
        return $this->file->find($id);
    }


    /***
     * @param $data
     * @param $id
     * @return mixed
     */
    public function update($data, $id) {
        $file = $this->getById($id);
        $file->update($data);
        return $file->refresh();
    }


    /**
     * @param $title
     * @param $attachmentable_id
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getFileByTitleAndAttachId($title, $attachmentable_id)
    {
        return File::query()
            ->where('title',$title)
            ->where('attachmentable_id',$attachmentable_id)
            ->first();
    }


}