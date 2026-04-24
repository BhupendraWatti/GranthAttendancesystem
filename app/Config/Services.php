<?php

namespace Config;

use CodeIgniter\Config\BaseService;
use App\Services\ApiService;
use App\Services\AttendanceService;

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 */
class Services extends BaseService
{
    /**
     * ApiService - eTimeOffice API Client
     * 
     * @param bool $getShared Return singleton instance (default: true)
     * @return ApiService
     */
    public static function apiservice($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('apiservice');
        }

        return new ApiService();
    }

    /**
     * AttendanceService - Core Business Logic
     * 
     * @param bool $getShared Return singleton instance (default: true)
     * @return AttendanceService
     */
    public static function attendanceservice($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('attendanceservice');
        }

        return new AttendanceService();
    }

    /*
     * public static function example($getShared = true)
     * {
     *     if ($getShared) {
     *         return static::getSharedInstance('example');
     *     }
     *
     *     return new \CodeIgniter\Example();
     * }
     */
}
