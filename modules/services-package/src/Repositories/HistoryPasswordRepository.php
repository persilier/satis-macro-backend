<?php

namespace Satis2020\ServicePackage\Repositories;

use Satis2020\ServicePackage\Models\HistoryPassword;

/**
 * Class HistoryPasswordRepository
 * @package Satis2020\ServicePackage\Repositories
 */
class HistoryPasswordRepository
{
    /**
     * @var HistoryPassword
     */
    private $historyPassword;

    /**
     * HistoryPasswordRepository constructor.
     * @param HistoryPassword $historyPassword
     */
    public function __construct(HistoryPassword $historyPassword)
    {
        $this->historyPassword = $historyPassword;
    }


    public function getPasswordForHistoryManagement($userId, $limit)
    {
        return $this->historyPassword->where('user_id', $userId)
                                    ->orderBy('created_at', 'desc')
                                    ->limit($limit)
                                    ->get();
    }

    public function create($data)
    {
        return $this->historyPassword->create($data);
    }

    public function delete($ids)
    {
        return $this->historyPassword->destroy($ids);
    }

}