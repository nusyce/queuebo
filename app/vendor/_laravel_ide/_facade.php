<?php

namespace Illuminate\Support\Facades;

interface Auth
{
    /**
     * @return \Modules\AdminUser\Models\User|false
     */
    public static function loginUsingId(mixed $id, bool $remember = false);

    /**
     * @return \Modules\AdminUser\Models\User|false
     */
    public static function onceUsingId(mixed $id);

    /**
     * @return \Modules\AdminUser\Models\User|null
     */
    public static function getUser();

    /**
     * @return \Modules\AdminUser\Models\User
     */
    public static function authenticate();

    /**
     * @return \Modules\AdminUser\Models\User|null
     */
    public static function user();

    /**
     * @return \Modules\AdminUser\Models\User|null
     */
    public static function logoutOtherDevices(string $password);

    /**
     * @return \Modules\AdminUser\Models\User
     */
    public static function getLastAttempted();
}